<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
class Blocknumbers_mod  extends CI_Model{

	function __construct()
	{
		parent::__construct();
		$this->load->database();	
	}

	function get_data($order_by='', $limit_to='', $limit_from='', $filter_data=array(), $option_param=array()) 
	{		
		$final_return_array=array();	
		try
		{	
			$sql = "SELECT SQL_CALC_FOUND_ROWS 						
					cli.id, cli.dstnumber, cli.account_id, customers.company_name 
					FROM ".$this->db->dbprefix('block_dst')." cli
					LEFT JOIN customers ON cli.account_id =customers.account_id
					WHERE 1 ";
					
			if(count($filter_data)> 0 )
			{
				foreach($filter_data as $key=>$value)
				{
					if ($value != '') {						
						if (in_array($key,['account_id','id']))
							  $sql .= " AND cli.$key ='" . $value . "' ";
						elseif($key=='dstnumber')
							$sql .=" AND cli.$key LIKE '".$value."%' "; 						
					}
					
				}
			}	
			
			if($order_by!='')
			{
				$sql .=" ORDER BY $order_by ";
			}
			else
			{
				$sql .=" ORDER BY id desc "; 	
			}
			
			$limit_from = intval( $limit_from);
			
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
				$dnd_id = $row['id'];			
				$final_return_array['result'][$dnd_id]=$row;
			}			
			/*
			echo '<pre>';
			print_r($final_return_array['result']);
			echo '<pre>'; 
			die;
			*/
			
		
			$final_return_array['status'] = 'success';
			$final_return_array['message']='Numbers Fetched Successfully';
			
			return $final_return_array;
		
		}
		catch(Exception $e)
		{
		
			$final_return_array['status'] = 'failed';
			$final_return_array['message']= $e->getMessage();
			return $final_return_array;
		}
	
	}

	function add($param){
	
	
	try{
			
			$log_data_array=array();//reset array			
			$dnd_data_array=array();			
			$this->db->trans_begin();
			
			
			$sql = "SELECT dstnumber FROM ".$this->db->dbprefix('block_dst')." WHERE dstnumber='".$param['dstnumber']."'"; 
			$query = $this->db->query($sql);
			$row = $query->row_array();
			if(isset($row))
			{	
				throw new Exception('CLI already exists.');	
			}
			
			$dnd_data_array['dstnumber']=	$param['dstnumber'];
			$dnd_data_array['account_id']=	$param['account_id'];
			
			
			if(count($dnd_data_array)>0)			
			{							
			
				$str = $this->db->insert_string($this->db->dbprefix('block_dst'), $dnd_data_array); 
				//echo $str;die;
				$result = $this->db->query($str);	
				if(!$result) 
				{
					$error_array = $this->db->error();
					throw new Exception($error_array['message']);
				}
				$this->id = $this->db->insert_id();		
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
				//set_activity_log($log_data_array);
			}
							
			return true;
			
	}
	catch(Exception $e)
		{
			$this->db->trans_rollback();
			return $e->getMessage();			
		}
	
}

	//delete 
	function delete($id_array)
	{
		try
		{
			$this->db->trans_begin();
			
			foreach($id_array['delete_id'] as $id)
			{
				$log_data_array = array();
				////delete user ///////
				$sql = "SELECT * FROM ".$this->db->dbprefix('block_dst')." WHERE id='".$id."' ";
				$query = $this->db->query($sql);
				$row = $query->row_array();								
				if(isset($row))
				{
					$data_dump = serialize($row);					
					$result = $this->db->delete($this->db->dbprefix('block_dst'), array('id' => $id));
					if(!$result) 
					{
						$error_array = $this->db->error();
						throw new Exception($error_array['message']);
					}
					
				}
		
							
				
				
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
	//
	function add_blocknumbers_number($account_id, $filename, $csv_data)
	{
		try
		{
			$postcode_data_array=$log_data_array=array();
			$error_message_array = array();
			$final_dnd_data_array=array();
			for($i=0;$i<count($csv_data);$i++)
			{
				$data = $csv_data[$i][0];
				
				$dnd_number =$val= trim($data);	
				///////////
				$dnd_data_array = array();
				$dnd_data_array['account_id'] =$account_id;		
				$dnd_data_array['dstnumber'] = $dnd_number;
				
				$final_dnd_data_array[]=$dnd_data_array;
			}
			
			//echo '<pre>';print_r($final_dnd_data_array);echo '<pre>';die;	
		
			$this->db->trans_begin();
			
			
			if(count($dnd_data_array)>0)			
			{
				$result = $this->db->insert_batch($this->db->dbprefix('block_dst'), $final_dnd_data_array); 				
				if(!$result) 
				{
					$error_array = $this->db->error();
					throw new Exception($error_array['message']);
				}		
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
			}
							
			return true;
		}
		catch(Exception $e)
		{		
			$this->db->trans_rollback();
			return $e->getMessage();	
			//set_activity_log($log_data_array);		
		}	
	
	}
	


}
?>
