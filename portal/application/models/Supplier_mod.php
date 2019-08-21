<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0
// License https://www.gnu.org/licenses/agpl-3.0.html
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################

class Supplier_mod extends CI_Model {

	public $did_id;
	public $total_count;
	function __construct()
	{
		parent::__construct();
		$this->load->database();	
	}
	
	/*Supplier List*/
	function get_data($order_by='', $limit_to='', $limit_from='', $filter_data=array(), $option_param=array()) 
	{		
		$final_return_array= array();	
		try
		{		
			$sql = "SELECT SQL_CALC_FOUND_ROWS		
			s.*,
			c.name currency_name		
			 FROM ".$this->db->dbprefix('suppliers')." s
			 LEFT JOIN ".$this->db->dbprefix('currencies')." c ON s.currency_id=c.currency_id
			 WHERE status_id!='-4' ";
					
			if(count($filter_data) > 0 )
			{
				foreach($filter_data as $key=>$value)
				{	
					if( $value!='')
					{
						if($key=='supplier_id')
							$sql .=" AND $key ='".$value."' "; 
						elseif($key=='currency_id')
							$sql .=" AND s.$key ='".$value."' "; 								
						else
							$sql .=" AND $key LIKE '%".$value."%' "; 
					}
				}			
			}									
			
			if($order_by!='')
			{
				$sql .=" ORDER BY $order_by ";
			}
			else
			{
				$sql .=" ORDER BY  `supplier_name` "; 	
			}
			
			$limit_from = intval($limit_from);
			
			if($limit_to !='')
				$sql .=" LIMIT $limit_from, $limit_to";	
			//echo $sql;
			$query = $this->db->query($sql);
		
			if(!$query)
			{	
				$error_array = $this->db->error();
				throw new Exception($error_array['message']);
			}	
			
			$sql = "SELECT FOUND_ROWS() as total";
			$query_count = $this->db->query($sql);			
			$row_count = $query_count->row();
			$this->total_count = $row_count->total;		
										
			foreach($query->result_array() as $row)
			{	
				$supplier_id_name = $row['supplier_id_name'];							
				$final_return_array['result'][$supplier_id_name]=$row;
				
				if(isset($option_param['netoff_mapping']) && $option_param['netoff_mapping']==true)
				{
					$row['netoff_mapping']=array();
				}
				$supplier_id_name_array[] = $supplier_id_name;
			}			
			
			
			if(isset($option_param['netoff_mapping']) && $option_param['netoff_mapping']==true && count($final_return_array['result'])>0)
			{
				$supplier_id_name_str = implode("','", $supplier_id_name_array);	
				$supplier_id_name_str ="'".$supplier_id_name_str."'";			
				$sql = "SELECT sam.*, ua.company_name FROM ".$this->db->dbprefix('suuplier_account_mapping')." sam LEFT JOIN ".$this->db->dbprefix('user_access')." ua ON sam.account_id = ua.user_access_id_name
				WHERE supplier_id_name IN($supplier_id_name_str) ";
				
				$query = $this->db->query($sql);		
				if(!$query)
				{	
					$error_array = $this->db->error();
					throw new Exception($error_array['message']);
				}	
				
				foreach($query->result_array() as $row)
				{
					$supplier_id_name = $row['supplier_id_name'];					
					$final_return_array['result'][$supplier_id_name]['netoff_mapping'][]=$row;					
				}	
			}
			
			
			$final_return_array['status'] = 'success';
			$final_return_array['message']='Suppliers fetched successfully';
			
			return $final_return_array;
		}
		catch(Exception $e)
		{
		
			$final_return_array['status'] = 'failed';
			$final_return_array['message']= $e->getMessage();
			return $final_return_array;
		}
		
		
	}
	
		
	/*Add Supplier*/
	function add($data) 
	{
		try
		{		
			$log_data_array=array();//reset array
			
			$supplier_data_array=array();			
			
			$supplier_data_array['supplier_name'] = $data['supplier_name'];	
			$supplier_data_array['currency_id'] = $data['currency_id'];		
			$supplier_data_array['supplier_address'] = $data['supplier_address'];		
			$supplier_data_array['supplier_emailid'] = $data['supplier_emailid'];		
			$supplier_data_array['created_by'] = $data['created_by'];		
			$supplier_data_array['create_date'] = date('Y-m-d H:i:s');
			
			
			///check supplier name
			$sql = "SELECT supplier_name FROM ".$this->db->dbprefix('suppliers')." WHERE supplier_name='".$supplier_data_array['supplier_name']."'"; 
			$query = $this->db->query($sql);
			$row = $query->row_array();
			if(isset($row))
			{	
				return 'Supplier Name Already Exists';	
			}
			
			////generate supplier_id_name
			while(1)
			{
				$key=generate_key($supplier_data_array['supplier_name'],$supplier_data_array['currency_id']);								
				$sql_check = "SELECT supplier_id
					 FROM ".$this->db->dbprefix('suppliers')." 
					 WHERE supplier_id_name ='".$key."'";								
				$query_check = $this->db->query($sql_check);	
				$num_rows = $query_check->num_rows();	
				if($num_rows > 0)
				{
					
				}
				else
				{
					$supplier_data_array['supplier_id_name'] =$key;
					break;
				}									
			}
						
							
			$this->db->trans_begin();
			if(count($supplier_data_array)>0)			
			{
				$str = $this->db->insert_string($this->db->dbprefix('suppliers'), $supplier_data_array); 
				$result = $this->db->query($str);	
				if(!$result) 
				{
					$error_array = $this->db->error();
					throw new Exception($error_array['message']);
				}
				$this->supplier_id = $this->db->insert_id();						
				$log_data_array[] =array('activity_type'=>'add','sql_table'=>$this->db->dbprefix('suppliers'),'sql_key'=>$this->supplier_id,'sql_query'=>$str);	
			}
				
			
			/////////////////
			
			
			if ($this->db->trans_status() === FALSE)
			{
				$error_array = $this->db->error();
				$this->db->trans_rollback();
				return $error_array['message'];					
			}
			else
			{
				$this->db->trans_commit();
				set_activity_log($log_data_array);
			}
							
			return true;
		}
		catch(Exception $e)
		{
			$this->db->trans_rollback();
			return $e->getMessage();			
		}
	}
	
	/*Update Supplier*/
	function update($data) 
	{
		try
		{
			$log_data_array=array();//reset array
			$supplier_data_array=array();		
			
			if(isset($data['supplier_id']))
				$supplier_id =$data['supplier_id'];
			else
				return 'ID missing';				
									
			if(isset($data['supplier_name']))
				$supplier_data_array['supplier_name'] = $data['supplier_name'];				
			if(isset($data['currency_id']))
				$supplier_data_array['currency_id'] = $data['currency_id'];		
			if(isset($data['supplier_address']))
				$supplier_data_array['supplier_address'] = $data['supplier_address'];			
			if(isset($data['supplier_emailid']))
				$supplier_data_array['supplier_emailid'] = $data['supplier_emailid'];	
			if(isset($data['status_id']))
				$supplier_data_array['status_id'] = $data['status_id'];		
			if(isset($data['modify_by']))
				$supplier_data_array['modify_by'] = $data['modify_by'];	
				
				
			///check supplier name	
			if(isset($supplier_data_array['supplier_name']))
			{
				$sql = "SELECT supplier_name FROM ".$this->db->dbprefix('suppliers')." WHERE supplier_name='".$supplier_data_array['supplier_name']."' AND supplier_id !='".$data['supplier_id']."'"; 
				$query = $this->db->query($sql);
				$row = $query->row_array();
				if(isset($row))
				{	
					return 'Supplier Name Already Exists';	
				}
			}	
			
				
			$this->db->trans_begin();
			if(count($supplier_data_array)>0)			
			{
				$where = "supplier_id='".$data['supplier_id']."'";
				$str = $this->db->update_string($this->db->dbprefix('suppliers'), $supplier_data_array, $where); 
				$result = $this->db->query($str);	
				if(!$result) 
				{
					$error_array = $this->db->error();
					throw new Exception($error_array['message']);
				}
				$log_data_array[] =array('activity_type'=>'update','sql_table'=>$this->db->dbprefix('suppliers'),'sql_key'=>$where, 'sql_query'=>$str);						
			}
			
			if($this->db->trans_status() === FALSE)
			{
				$error_array = $this->db->error();
				$this->db->trans_rollback();
				return $error_array['message'];
					
			}
			else
			{
				$this->db->trans_commit();
				set_activity_log($log_data_array);
			}
				
			return true;
		}
		catch(Exception $e)
		{
			$this->db->trans_rollback();
			return $e->getMessage();			
		}
	}
	
	/*Delete supplier*/
	function delete($id_array)
	{
		try
		{		
			//check status
		
			$this->db->trans_begin();
			
			foreach($id_array['delete_id'] as $id)
			{
				$log_data_array = array();				
				
				$sql = "SELECT group_concat(s.server_name,'') AS server_names
				FROM ".$this->db->dbprefix('servers')." s 
				WHERE s.supplier_id_name='".$id."'";								
				$query = $this->db->query($sql);		
				$assigned_row = $query->row();
				if(isset($assigned_row) && $assigned_row->server_names!='')
				{	
					$server_names = $assigned_row->server_names;
					throw new Exception('Supplier assigned to these servers('.$server_names.')');
				}
				
				$sql = "SELECT group_concat(s.carrier_name,'') AS carrier_names
				FROM ".$this->db->dbprefix('carrier')." s 
				WHERE s.supplier_id_name='".$id."'";								
				$query = $this->db->query($sql);		
				$assigned_row = $query->row();
				if(isset($assigned_row) && $assigned_row->carrier_names!='')
				{	
					$server_names = $assigned_row->carrier_names;
					throw new Exception('Supplier assigned to these carriers('.$server_names.')');
				}
				
				
				////delete supplier ///////
				$sql = "SELECT * FROM ".$this->db->dbprefix('suppliers')." WHERE supplier_id_name='".$id."' ";
				$query = $this->db->query($sql);
				$row = $query->row_array();								
				if(isset($row))
				{
					$data_dump = serialize($row);					
					$result = $this->db->delete($this->db->dbprefix('suppliers'), array('supplier_id_name' => $id));
					if(!$result) 
					{
						$error_array = $this->db->error();
						throw new Exception($error_array['message']);
					}
					$log_data_array[] =array('activity_type'=>'delete','sql_table'=>$this->db->dbprefix('suppliers'),'sql_key'=>$id, 'sql_query'=>$data_dump );					
				}
				
				$log_data_array[] =array('activity_type'=>'delete_recovery','sql_table'=>'Supplier','sql_key'=>$id, 'sql_query'=>'' );				
				set_activity_log($log_data_array);
			}
			
			if ($this->db->trans_status() === FALSE)
			{
				$error_array = $this->db->error();
				$this->db->trans_rollback();
				return $error_array['message'];					
			}
			else
			{
				$this->db->trans_commit();
				return true;
			}
			
		}
		catch(Exception $e)
		{
			$this->db->trans_rollback();
			return $e->getMessage();			
		}
		
		
		
	}
	
	function get_data_total_count() 
	{	
		return $this->total_count;
	}
	
	function get_netoff_accounts($currency_id='')
	{
				
		$final_return_array=$user_access_id_name_array=$tariff_id_name_array=array();	
		$tariff_id_name_user_access_id_name_mapping_array=$currency_id_user_access_id_name_mapping_array=array();
		$service_id_name_array=array();
		try
		{	
			if($currency_id!=='')
			{
				$sql = "SELECT 		
					ua.user_access_id_name account_id,	ua.name, ua.company_name
					FROM ".$this->db->dbprefix('user_access')." ua INNER JOIN ".$this->db->dbprefix('user')." u ON ua.user_access_id_name=u.account_id
					WHERE 
					ua.user_type ='CUSTOMER' 
					AND ua.billing_type='netoff'
					AND user_access_id_name NOT IN(SELECT account_id FROM ".$this->db->dbprefix('suuplier_account_mapping').")					
					AND u.user_currency_id='".$currency_id."'
					ORDER BY `name` ASC";
			
			}
			else
			{
			$sql = "SELECT 		
					ua.user_access_id_name account_id,	ua.name, ua.company_name
					FROM ".$this->db->dbprefix('user_access')." ua 
					WHERE 
					ua.user_type ='CUSTOMER' 
					AND ua.billing_type='netoff'
					AND user_access_id_name NOT IN(SELECT account_id FROM ".$this->db->dbprefix('suuplier_account_mapping').")					
					ORDER BY `name` ASC";			
			}			
			
		//	echo $sql;
			$query = $this->db->query($sql);		
			if(!$query)
			{	
				$error_array = $this->db->error();
				throw new Exception($error_array['message']);
			}	
			
										
			foreach($query->result_array() as $row)
			{	
				$account_id = $row['account_id'];			
				$final_return_array['result'][$account_id]=$row;
			}			
			
					
			
			$final_return_array['status'] = 'success';
			$final_return_array['message']='Net-Off accounts fetched successfully';
			
			return $final_return_array;
		}
		catch(Exception $e)
		{
		
			$final_return_array['status'] = 'failed';
			$final_return_array['message']= $e->getMessage();
			return $final_return_array;
		}
		
		
	
	
	}	
	
	//
	function update_netoff_accounts($data) 
	{//print_r($data);
		try
		{
			$log_data_array=array();//reset array
					
			
			if(isset($data['supplier_id_name']))
				$supplier_id_name =$data['supplier_id_name'];
			else
				return 'Supplier missing';				
			
			//check duplicate
			if(isset($data['netoff_id']))
			{
			
			}
			
			$this->db->trans_begin();						
			if(isset($data['netoff_id']))
			{
				
				foreach($data['netoff_id'] as $key=> $account_id)
				{
					$account_id = trim($account_id);
					if($account_id=='')
						continue;
						
					$sql = "SELECT mapping_id FROM ".$this->db->dbprefix('suuplier_account_mapping')." WHERE account_id='".$account_id."' AND supplier_id_name ='".$supplier_id_name."'"; 
					$query = $this->db->query($sql);
					$row = $query->row_array();
					if(isset($row))
					{	//already exists
						
					}
					else
					{	
						$mapping_data_array=array();
						$mapping_data_array['supplier_id_name'] = $supplier_id_name;	
						$mapping_data_array['account_id'] =  $account_id;			
						$mapping_data_array['created_by'] = $data['created_by'];		
						
											
						$str = $this->db->insert_string($this->db->dbprefix('suuplier_account_mapping'), $mapping_data_array); 
						$result = $this->db->query($str);	
						if(!$result) 
						{
							$error_array = $this->db->error();
							throw new Exception($error_array['message']);
						}
											
						
					}
				}//foreach
				
				
				$netoff_id_str = implode("','", $data['netoff_id']);	
				$netoff_id_str ="'".$netoff_id_str."'";			
				$sql = "DELETE FROM ".$this->db->dbprefix('suuplier_account_mapping')." WHERE account_id NOT IN($netoff_id_str) ";
				$result = $this->db->query($sql);	
				if(!$result) 
				{
					$error_array = $this->db->error();
					throw new Exception($error_array['message']);
				}
				//remove the remaining
			}
			else
			{
				//remove all mapping from switch_suuplier_account_mapping
				$sql = "DELETE FROM ".$this->db->dbprefix('suuplier_account_mapping')." WHERE 1";
				$result = $this->db->query($sql);	
				if(!$result) 
				{
					$error_array = $this->db->error();
					throw new Exception($error_array['message']);
				}
			}
				
				
			
			if($this->db->trans_status() === FALSE)
			{
				$error_array = $this->db->error();
				$this->db->trans_rollback();
				return $error_array['message'];
					
			}
			else
			{
				$this->db->trans_commit();
				
			}
				
			return true;
		}
		catch(Exception $e)
		{
			$this->db->trans_rollback();
			return $e->getMessage();			
		}
	}
}