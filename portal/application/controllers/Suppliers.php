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

if (!defined('BASEPATH')) exit('No direct script access allowed'); 
class Suppliers extends CI_Controller { 
    
	function __construct()
	{
		parent::__construct();
		
		$this->load->library('pagination'); // pagination class			
		$this->form_validation->set_error_delimiters('','');		
		$this->load->model('supplier_mod');
		$this->load->model('server_mod');
		//permission check		
		if(!check_is_loggedin())
			redirect(base_url(), 'refresh');
		
		if(!check_logged_account_type(array('ADMIN','SUBADMIN','NOC')))		
			redirect(base_url(), 'refresh');
		//$this->output->enable_profiler(ENABLE_PROFILE);		
	}  	
		
	
	function index($arg1='',$format='')
	{		
		$page_name = "supplier_index";
		$data['page_name']=$page_name;		
										
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();	
		////////////////////////////////////////////////
		
		if(isset($_POST['action']) && $_POST['action']=='OkDeleteData')
		{
			if(!check_logged_account_type(array('ADMIN','SUBADMIN')))
			{
				$this->session->set_flashdata('err_msgs', 'Dont have enough permission');	
				redirect(base_url().'suppliers', 'location', '301');
			}
						
			$delete_id_array = json_decode($_POST['delete_id']);
			if(isset($_POST['delete_id']) && count($delete_id_array)>0)
			{
				$delete_param_array=array('delete_id'=>$delete_id_array);
				$result = $this->supplier_mod->delete($delete_param_array); 

				if($result === true)
				{
					$suc_msgs = count($delete_id_array).' Supplier';
					if(count($delete_id_array)>1)
						$suc_msgs .= 's';
					$suc_msgs .= ' Deleted Successfully';
					$this->session->set_flashdata('suc_msgs',$suc_msgs );
					redirect(current_url(), 'location', '301'); 
				}
				else
				{
					$err_msgs = $result;
					$this->session->set_flashdata('err_msgs', $err_msgs);
					redirect(current_url(), 'location', '301'); 
				}
			}
			else
			{
				$err_msgs='Select Supplier to delete';
				$this->session->set_flashdata('err_msgs', $err_msgs);
				redirect(current_url(), 'location', '301'); 
			}		
			
			redirect(base_url().'suppliers', 'location', '301'); 
		}	
		
		if(isset($_POST['search_action']))
		{// coming from search button	
			$_SESSION['search_supplier_data'] = array('s_supplier_id_name'=> $_POST['supplier_id_name'], 's_supplier_name'=>$_POST['supplier_name'], 's_supplier_type'=>$_POST['supplier_type'],'s_currency_id'=>$_POST['currency_id']);	
		}
		else
		{
			$_SESSION['search_supplier_data']['s_supplier_id_name']	= isset($_SESSION['search_supplier_data']['s_supplier_id_name'])? $_SESSION['search_supplier_data']['s_supplier_id_name'] : '';
			$_SESSION['search_supplier_data']['s_supplier_name'] 		= isset($_SESSION['search_supplier_data']['s_supplier_name'])? $_SESSION['search_supplier_data']['s_supplier_name'] : '';
			$_SESSION['search_supplier_data']['s_supplier_type'] 		= isset($_SESSION['search_supplier_data']['s_supplier_type'])? $_SESSION['search_supplier_data']['s_supplier_type'] : '';
			$_SESSION['search_supplier_data']['s_currency_id'] 		= isset($_SESSION['search_supplier_data']['s_currency_id'])? $_SESSION['search_supplier_data']['s_currency_id'] : '';
		}
		$search_data = array(	
				'supplier_id_name'=>$_SESSION['search_supplier_data']['s_supplier_id_name'],	
				'supplier_name'=>$_SESSION['search_supplier_data']['s_supplier_name'],					
				'supplier_type'=>$_SESSION['search_supplier_data']['s_supplier_type'],	
				's.currency_id'=>$_SESSION['search_supplier_data']['s_currency_id'],					
				
		);		
		$order_by = '';
		
				
		
		$is_file_downloaded = false;
		$currency_data = $this->utils_model->get_currencies();
		
	if ($arg1 == 'export' && $format != '') 
		{
			ini_set('memory_limit', '2048M');
			$format = param_decrypt($format);			
			$export_data=array();
			$per_page = 60000;
			$segment = 0;
			$option_param=array();
			$order_by='';	
			$suppliers_data = $this->supplier_mod->get_data($order_by,$per_page, $segment, $search_data,$option_param);	
						
			// column titles
			$export_header = array(
			'Supplier ID',
			'Supplier Name',
			'Currency',
			'Status'			 
			); 
			
			
			$currency_array=array();
			for($i=0; $i<count($currency_data); $i++)
			{									
				$currency_id = $currency_data[$i]['currency_id'];
				$currency_array[$currency_id] = $currency_data[$i]['name'];
			}
			
			$status_name_array = array(
				'1'=>array('name'=>'Active'),
				'-2'=>array('name'=>'Suspended'),
				'-3'=>array('name'=>'Deleted'),		 
				'-4'=>array('name'=>'Closed'),
			);
			
			if(count($suppliers_data['result']) > 0)
			{ 
				foreach($suppliers_data['result'] as $supplier_data)
				{ 
					$status_id = $supplier_data['status_id'];
						if(isset($status_name_array[$status_id]))
						{
							$status_name = $status_name_array[$status_id]['name'];
							
						}	
						else
						{
							$status_name = '';	
							
						}	
							
					$export_data[] = array(
						$supplier_data['supplier_id_name'],
						$supplier_data['supplier_name'],
						$supplier_data['currency_name'],
						$status_name		
					);
				} 
			} 
			else
				$export_data = array('');
			
			//prepare search data
			$search_array = array();
			
			if($_SESSION['search_supplier_data']['s_supplier_id_name'] != '')
				$search_array['Supplier ID'] = $_SESSION['search_supplier_data']['s_supplier_id_name'];
			if($_SESSION['search_supplier_data']['s_supplier_name'] != '')
				$search_array['Supplier Name'] = $_SESSION['search_supplier_data']['s_supplier_name'];
			if($_SESSION['search_supplier_data']['s_supplier_type'] != '')
				$search_array['Supplier Type'] = $_SESSION['search_c_summary']['s_supplier_type'];	
			if(isset($_SESSION['search_supplier_data']['s_currency_id']) && $_SESSION['search_supplier_data']['s_currency_id'] != '')
			{
				$s_currency_id = $_SESSION['search_supplier_data']['s_currency_id'];
				$currency_name = '';
				if(isset($currency_array[$s_currency_id]))
					$currency_name = $currency_array[$s_currency_id];
				
				if($currency_name !='')	
				$search_array['Currency'] = $currency_name;
				
			}	
						
					
			$file_name = 'suppliers';
			
			$this->load->library('Export');
			$downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);
			
			if (gettype($downloaded_message) == 'string')
				$data['err_msgs'] = $downloaded_message;
			else
				$is_file_downloaded = true;
		
		}
		
		if($is_file_downloaded ===false)
		{		
			/****** pagination code start here **********/
			$pagination_uri_segment = 3;
			$per_page = RECORDS_PER_PAGE;
			
			if($this->uri->segment($pagination_uri_segment)==''){ $segment= 0; }
			else{ $segment= $this->uri->segment($pagination_uri_segment); }
			
			//echo '<pre>';print_r($search_data);echo '</pre>';
			
			$option_param=array();
			$suppliers_data = $this->supplier_mod->get_data($order_by,$per_page, $segment, $search_data,$option_param);		
			$total = $this->supplier_mod->get_data_total_count();	
			
			$config = array();
			$config = $this->utils_model->setup_pagination_option($total, 'suppliers/index', $per_page, $pagination_uri_segment );		
			$this->pagination->initialize($config);			
			
			/****** pagination code ends  here **********/
			$data['pagination']=$this->pagination->create_links();	
			$data['suppliers_data'] = $suppliers_data;	
			$data['currency_options'] = $this->utils_model->get_currencies();
			$data['sdr_terms'] = $this->utils_model->get_sdr_terms('suppliers');
			
			$this->load->view('basic/header',$data);
			$this->load->view('supplier/suppliers', $data);
			$this->load->view('basic/footer', $data); 	
		}
	
	}
	
	
	
	public function add() /*Add New Supplier*/
	{
		$page_name = "supplier_add";
		$data['page_name']=$page_name;
		
				
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
				
		if(isset($_POST['action']) && $_POST['action']=='OkSaveData')
		{
			
			$this->form_validation->set_rules('supplier_name', 'Supplier Name', 'trim|required');	
			$this->form_validation->set_rules('currency_id', 'Currency', 'trim|required');	
			$this->form_validation->set_rules('supplier_address', 'Address', 'trim');
			$this->form_validation->set_rules('supplier_emailid', 'Email Address', 'trim');
								
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{	
				$_POST['created_by']=get_logged_account_id();						
				
				$result = $this->supplier_mod->add($_POST);
				//echo '<pre>';print_r($post_array);var_dump($result);die;
				if($result === true)
				{		
					$supplier_id = $this->supplier_mod->supplier_id;							
					$this->session->set_flashdata('suc_msgs', 'Supplier Added Successfully');	
					
					if(isset($_POST['button_action']) && trim($_POST['button_action'])!='')
					{
						 $action =  trim($_POST['button_action']);
						 if($action=='save')
							redirect(base_url().'suppliers/edit/'.param_encrypt($supplier_id), 'location', '301');
						 elseif($action=='save_close')
							redirect(base_url().'suppliers', 'location', '301');
					}
					else
					{
						redirect(base_url().'suppliers', 'location', '301'); 	
					}
											
					redirect(base_url().'suppliers/add', 'location', '301'); 	
					exit();
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}
				
			}//if
			
		} 
		///////////////////////////		
		
		$data['currency_options'] = $this->utils_model->get_currencies();	
		
	
				
		$this->load->view('basic/header',$data);
		$this->load->view('supplier/supplier_add', $data);
		$this->load->view('basic/footer', $data);		
	}
	
	public function edit($id=-1) /*Edit Supplier*/
	{
		if($id==-1)
			show_404();
						
		$page_name = "supplier_edit";
		$data['page_name']=$page_name;		
		
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
			
		if(isset($_POST['action']) && $_POST['action']=='OkSaveData')
		{		
			$supplier_id= $_POST['supplier_id'];
			$data['supplier_id'] = $supplier_id;		
		
			$this->form_validation->set_rules('supplier_id', 'Supplier', 'trim|required');			
			$this->form_validation->set_rules('supplier_name', 'Supplier Name', 'trim|required');	
			$this->form_validation->set_rules('status_id', 'Status', 'trim|required');	
			$this->form_validation->set_rules('supplier_address', 'Address', 'trim');
			$this->form_validation->set_rules('supplier_emailid', 'Email Address', 'trim');
			
			/*non mandatory fields*/			
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{				
					
				$_POST['modify_by']=get_logged_account_id();								
			
				$result =$this->supplier_mod->update($_POST);
				//echo '<pre>';	print_r($_POST); print_r($result);die;
				if($result === true)
				{													
					$this->session->set_flashdata('suc_msgs', 'Supplier Updated Successfully');					
					if(isset($_POST['button_action']) && trim($_POST['button_action'])!='')
					{
						 $action =  trim($_POST['button_action']);
						 if($action=='save')
							redirect(base_url().'suppliers/edit/'.param_encrypt($supplier_id), 'location', '301');
						 elseif($action=='save_close')
							redirect(base_url().'suppliers', 'location', '301');
					}
					else
					{
						redirect(base_url().'suppliers', 'location', '301'); 	
					}
											
					redirect(base_url().'suppliers/edit/'.param_encrypt($supplier_id), 'location', '301'); 				
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}
					
			}//if
		
		} 
		elseif(isset($_POST['action']) && $_POST['action']=='OkSaveMapping')
		{		
			$supplier_id_name= $_POST['supplier_id_name'];
			$supplier_id= $_POST['supplier_id'];
			$data['supplier_id_name'] = $supplier_id_name;		
		
			$this->form_validation->set_rules('supplier_id_name', 'Supplier', 'trim|required');			
			
			//echo '<pre>';	print_r($_POST); die;
			/*non mandatory fields*/			
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{				
					
				$_POST['created_by']=get_logged_account_id();								
			
				$result =$this->supplier_mod->update_netoff_accounts($_POST);
				//echo '<pre>';	print_r($_POST); print_r($result);die;
				if($result === true)
				{													
					$this->session->set_flashdata('suc_msgs', 'Supplier NetOff Accounts Updated Successfully');					
					if(isset($_POST['button_action_mapping']) && trim($_POST['button_action_mapping'])!='')
					{
						 $action =  trim($_POST['button_action_mapping']);
						 if($action=='save')
							redirect(base_url().'suppliers/edit/'.param_encrypt($supplier_id), 'location', '301');
						 elseif($action=='save_close')
							redirect(base_url().'suppliers', 'location', '301');
					}
					else
					{
						redirect(base_url().'suppliers', 'location', '301'); 	
					}
											
					redirect(base_url().'suppliers/edit/'.param_encrypt($supplier_id), 'location', '301'); 				
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}
					
			}//if
		
		} 
		///////////////////////////		
			
		if(!empty($id))	
		{
			$supplier_id = param_decrypt($id);			
			$order_by='';
			$per_page=1;
			$segment=0;

			$search_data = array(	
				'supplier_id'=>$supplier_id,
			);	
			
			
			$supplier_data = $this->supplier_mod->get_data($order_by,$per_page, $segment, $search_data, array('netoff_mapping'=>true));		
					
			if(isset($supplier_data['result']))
				$supplier_data = current($supplier_data['result']);
			else
			{
				show_404();
			}				 		
		}		
		else
		{
			show_404();
		}
		/****** pagination code ends  here **********/
		$data['supplier_data'] = $supplier_data;		
		$data['supplier_id'] = $supplier_id;
		
		//echo '<pre>';print_r($supplier_data);echo '</pre>';die;
		
		$data['currency_options'] = $this->utils_model->get_currencies();	
		
		$data['netoff_accounts'] = $this->supplier_mod->get_netoff_accounts($supplier_data['currency_id']);
				
		$this->load->view('basic/header',$data);
		$this->load->view('supplier/supplier_edit', $data);
		$this->load->view('basic/footer', $data);	
		
	}
	
	
	function servers($arg1='',$format='')
	{		
		$page_name = "server_index";
		$data['page_name']=$page_name;
					
							
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();	
		////////////////////////////////////////////////
		
		if(isset($_POST['action']) && $_POST['action']=='OkDeleteData')
		{
			if(!check_logged_account_type(array('ADMIN','SUBADMIN','NOC')))
			{
				$this->session->set_flashdata('err_msgs', 'Dont have enough permission');	
				redirect(base_url().'servers', 'location', '301');
			}
						
			$delete_id_array = json_decode($_POST['delete_id']);
			if(isset($_POST['delete_id']) && count($delete_id_array)>0)
			{
				$delete_param_array=array('delete_id'=>$delete_id_array);
				$result = $this->server_mod->delete($delete_param_array); 

				if($result === true)
				{
					$suc_msgs = count($delete_id_array).' Suppliers';
					if(count($delete_id_array)>1)
						$suc_msgs .= 's';
					$suc_msgs .= ' Deleted Successfully';
					$this->session->set_flashdata('suc_msgs',$suc_msgs );
					redirect(current_url(), 'location', '301'); 
				}
				else
				{
					$err_msgs = $result;
					$this->session->set_flashdata('err_msgs', $err_msgs);
					redirect(current_url(), 'location', '301'); 
				}
			}
			else
			{
				$err_msgs='Select Supplier to delete';
				$this->session->set_flashdata('err_msgs', $err_msgs);
				redirect(current_url(), 'location', '301'); 
			}		
			
			redirect(base_url().'servers', 'location', '301'); 
		}	
		
		if(isset($_POST['search_action']))
		{// coming from search button	
			$_SESSION['search_server_data'] = array(
				's_server_id_name'=> $_POST['server_id_name'], 
				's_server_name'=>$_POST['server_name'], 
				's_server_type'=>$_POST['server_type'],
				's_currency_id'=>$_POST['currency_id'],
				's_no_of_records' => $_POST['no_of_rows']
				);	
		}
		else
		{
			$_SESSION['search_server_data']['s_server_id_name']	= isset($_SESSION['search_server_data']['s_server_id_name'])? $_SESSION['search_server_data']['s_server_id_name'] : '';
			$_SESSION['search_server_data']['s_server_name'] 		= isset($_SESSION['search_server_data']['s_server_name'])? $_SESSION['search_server_data']['s_server_name'] : '';
			$_SESSION['search_server_data']['s_server_type'] 		= isset($_SESSION['search_server_data']['s_server_type'])? $_SESSION['search_server_data']['s_server_type'] : '';
			$_SESSION['search_server_data']['s_currency_id'] 		= isset($_SESSION['search_server_data']['s_currency_id'])? $_SESSION['search_server_data']['s_currency_id'] : '';
			
		}
		$search_data = array(	
				'server_id_name'=>$_SESSION['search_server_data']['s_server_id_name'],	
				'server_name'=>$_SESSION['search_server_data']['s_server_name'],					
				'server_type'=>$_SESSION['search_server_data']['s_server_type'],	
				's.currency_id'=>$_SESSION['search_server_data']['s_currency_id'],					
				
		);		
			
		
		$is_file_downloaded = false;
		$currency_data = $this->utils_model->get_currencies();
		
		
		if ($arg1 == 'export' && $format != '') 
		{
			ini_set('memory_limit', '2048M');
			$format = param_decrypt($format);			
			$export_data=array();
			$per_page = 60000;
			$segment = 0;
			$option_param=array();
			$order_by='';	
			$servers_data = $this->server_mod->get_data($order_by,$per_page, $segment, $search_data,$option_param);	
						
			// column titles
			$export_header = array(
				'Supplier Name',
				'Supplier IP',
				'Server ID',
				'Server Name',
				'Server Type',
				'Setup Charges',
				'Monthly Charges',
				'Currency',
				'Status'
			); 
			
			  
			
			$currency_array=array();
			for($i=0; $i<count($currency_data); $i++)
			{									
				$currency_id = $currency_data[$i]['currency_id'];
				$currency_array[$currency_id] = $currency_data[$i]['name'];
			}
			
			$status_name_array = array(
				'1'=>array('name'=>'Active'),
				'-2'=>array('name'=>'Suspended'),
				'-3'=>array('name'=>'Deleted'),		 
				'-4'=>array('name'=>'Closed'),
			);
			
			if(count($servers_data['result']) > 0)
			{ 
				foreach($servers_data['result'] as $server_data)
				{ 
					$status_id = $server_data['status_id'];
					if(isset($status_name_array[$status_id]))
					{
						$status_name = $status_name_array[$status_id]['name'];						
					}	
					else
					{
						$status_name = '';						
					}
					
				$setup_charges = round($server_data['setup_charges'], 2);
				$monthly_charges = round($server_data['monthly_charges'], 2);
				
					$export_data[] = array(
						$server_data['supplier_name'],
						$server_data['server_ip'],
						$server_data['server_id_name'],
						$server_data['server_name'],
						$server_data['server_type'],
						$setup_charges,
						$monthly_charges,
						$server_data['currency_name'],
						$status_name		
					);
				} 
			} 
			else
				$export_data = array('');
			
			//prepare search data
			$search_array = array();
		
			
			if($_SESSION['search_server_data']['s_server_id_name'] != '')
				$search_array['Server ID'] = $_SESSION['search_server_data']['s_server_id_name'];
			if($_SESSION['search_server_data']['s_server_name'] != '')
				$search_array['Server Name'] = $_SESSION['search_server_data']['s_server_name'];
			if($_SESSION['search_server_data']['s_server_type'] != '')
				$search_array['Server Type'] = $_SESSION['search_server_data']['s_server_type'];	
			if(isset($_SESSION['search_server_data']['s_currency_id']) && $_SESSION['search_server_data']['s_currency_id'] != '')
			{
				$s_currency_id = $_SESSION['search_server_data']['s_currency_id'];
				$currency_name = '';
				if(isset($currency_array[$s_currency_id]))
					$currency_name = $currency_array[$s_currency_id];
				
				if($currency_name !='')	
				$search_array['Currency'] = $currency_name;
				
			}	
						
					
			$file_name = 'servers';
			
			$this->load->library('Export');
			$downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);
			
			if (gettype($downloaded_message) == 'string')
				$data['err_msgs'] = $downloaded_message;
			else
				$is_file_downloaded = true;
		
		}
		
		if($is_file_downloaded ===false)
		{		
			/****** pagination code start here **********/
			
			$pagination_uri_segment = 3;
			 if (isset($_SESSION['search_server_data']['s_no_of_records']) && $_SESSION['search_server_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_server_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;
			
			if($this->uri->segment($pagination_uri_segment)==''){ $segment= 0; }
			else{ $segment= $this->uri->segment($pagination_uri_segment); }
						
			$option_param=array();
			$servers_data = $this->server_mod->get_data($order_by,$per_page, $segment, $search_data,$option_param);		
			$total = $this->server_mod->get_data_total_count();	
			
			$config = array();
			$config = $this->utils_model->setup_pagination_option($total, 'suppliers/servers', $per_page, $pagination_uri_segment );		
			$this->pagination->initialize($config);			
			
			/****** pagination code ends  here **********/
			$data['pagination']=$this->pagination->create_links();
			
			$data['servers_data'] = $servers_data;	
			$data['currency_options'] = $this->utils_model->get_currencies();
			$data['sdr_terms'] = $this->utils_model->get_sdr_terms('servers');
			$data['total_records']=$total;
			
			$this->load->view('basic/header',$data);
			$this->load->view('supplier/servers', $data);
			$this->load->view('basic/footer', $data); 	
		}
	
	}
	
	
	
	public function server_add() /*Add New Supplier*/
	{
		$page_name = "server_add";
		$data['page_name']=$page_name;
		
				
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
				
		if(isset($_POST['action']) && $_POST['action']=='OkSaveData')
		{				
			$this->form_validation->set_rules('server_name', 'Server Name', 'trim|required');	
			$this->form_validation->set_rules('server_type', 'Server Type', 'trim|required');
			$this->form_validation->set_rules('server_reference', 'Server Reference', 'trim|required');
			$this->form_validation->set_rules('server_ip', 'Server IP', 'trim|required');			
			$this->form_validation->set_rules('setup_charges', 'Setup Cost', 'trim|required|greater_than_equal_to[0]');
			$this->form_validation->set_rules('monthly_charges', 'Monthly Cost', 'trim|required|greater_than_equal_to[0]');			
			$this->form_validation->set_rules('minimum_contract_days', 'Minimum Contract Time', 'trim|required|is_natural');			
			$this->form_validation->set_rules('supplier_id_name', 'Supplier Name', 'trim|required');
			
			$this->form_validation->set_rules('currency_id', 'Currency', 'trim|required');
								
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{	
				$_POST['created_by']=get_logged_account_id();						
					
				$result = $this->server_mod->add($_POST);
				//echo '<pre>';print_r($post_array);var_dump($result);die;
				if($result === true)
				{		
					$server_id = $this->server_mod->server_id;							
					$this->session->set_flashdata('suc_msgs', 'Server Added Successfully');	
					
					if(isset($_POST['button_action']) && trim($_POST['button_action'])!='')
					{
						 $action =  trim($_POST['button_action']);
						 if($action=='save')
							redirect(base_url().'suppliers/server_edit/'.param_encrypt($server_id), 'location', '301');
						 elseif($action=='save_close')
							redirect(base_url().'suppliers/servers', 'location', '301');
					}
					else
					{
						redirect(base_url().'suppliers/servers', 'location', '301'); 	
					}
											
					redirect(base_url().'suppliers/server_add', 'location', '301'); 	
					exit();
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}
				
			}//if
			
		} 
		///////////////////////////		
		
		$data['currency_options'] = $this->utils_model->get_currencies();	
		
		$data['suppliers_data'] = $this->supplier_mod->get_data('','', '', array('status_id'=>1),array());	
				
		$this->load->view('basic/header',$data);
		$this->load->view('supplier/server_add', $data);
		$this->load->view('basic/footer', $data);		
	}
	
	public function server_edit($id=-1) /*Edit Supplier*/
	{
		if($id==-1)
			show_404();
						
		$page_name = "server_edit";
		$data['page_name']=$page_name;		
		
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
			
		if(isset($_POST['action']) && $_POST['action']=='OkSaveData')
		{		
			$server_id= $_POST['server_id'];
			$data['server_id'] = $server_id;		
		
			$this->form_validation->set_rules('server_id', 'Server id', 'trim|required');			
			$this->form_validation->set_rules('server_name', 'Server Name', 'trim|required');	
			$this->form_validation->set_rules('status_id', 'Status', 'trim|required');	
			$this->form_validation->set_rules('server_type', 'Server Type', 'trim|required');
			$this->form_validation->set_rules('server_reference', 'Server Reference', 'trim|required');
			$this->form_validation->set_rules('server_ip', 'Server IP', 'trim|required');			
			$this->form_validation->set_rules('setup_charges', 'Setup Cost', 'trim|required|greater_than_equal_to[0]');
			$this->form_validation->set_rules('monthly_charges', 'Monthly Cost', 'trim|required|greater_than_equal_to[0]');			
			$this->form_validation->set_rules('minimum_contract_days', 'Minimum Contract Time', 'trim|required|is_natural');			
			$this->form_validation->set_rules('supplier_id_name', 'Supplier Name', 'trim|required');
				
			
			/*non mandatory fields*/			
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{				
					
				$_POST['modify_by']=get_logged_account_id();								
			
				$result =$this->server_mod->update($_POST);
				//echo '<pre>';	print_r($_POST); print_r($result);die;
				if($result === true)
				{													
					$this->session->set_flashdata('suc_msgs', 'Server Updated Successfully');					
					if(isset($_POST['button_action']) && trim($_POST['button_action'])!='')
					{
						 $action =  trim($_POST['button_action']);
						 if($action=='save')
							redirect(base_url().'suppliers/server_edit/'.param_encrypt($server_id), 'location', '301');
						 elseif($action=='save_close')
							redirect(base_url().'suppliers/servers', 'location', '301');
					}
					else
					{
						redirect(base_url().'suppliers/servers', 'location', '301'); 	
					}
											
					redirect(base_url().'suppliers/server_edit/'.param_encrypt($server_id), 'location', '301'); 				
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}
					
			}//if
		
		} 
		///////////////////////////		
			
		if(!empty($id))	
		{
			$server_id = param_decrypt($id);			
			$order_by='';
			$per_page=1;
			$segment=0;

			$search_data = array(	
				'server_id'=>$server_id,
			);	
			
			
			$server_data = $this->server_mod->get_data($order_by,$per_page, $segment, $search_data, array());
		
					
			if(isset($server_data['result']))
				$server_data = current($server_data['result']);
			else
			{
				show_404();
			}				 		
		}		
		else
		{
			show_404();
		}
		/****** pagination code ends  here **********/
		$data['server_data'] = $server_data;		
		$data['server_id'] = $server_id;
		
				
		$search_data=array('currency_id'=>$server_data['currency_id']);
		$data['suppliers_data'] = $this->supplier_mod->get_data('','', '', $search_data,array());	
				
		$this->load->view('basic/header',$data);
		$this->load->view('supplier/server_edit', $data);
		$this->load->view('basic/footer', $data);	
		
		
										
		
	}
	
	
	function invoice($arg1='',$format='') {
        $page_name = "supplier_invoice";
        $data['page_name'] = $page_name;
       	
		$this->load->model('supplier_invoice_mod');	

        $account_id = get_logged_account_id();
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ////////////////////////////////////////////////

		if(isset($_POST['action']) && $_POST['action']=='OkDeleteData')
		{	
			
			$delete_id_array = json_decode($_POST['delete_id']);
			if(isset($_POST['delete_id']) && count($delete_id_array)>0)
			{
				$delete_param_array=array('delete_id'=>$delete_id_array);
				$result = $this->supplier_invoice_mod->delete($delete_param_array); 

				if($result === true)
				{
					$suc_msgs = count($delete_id_array).' Supplier Invoice';
					if(count($delete_id_array)>1)
						$suc_msgs .= 's';
					$suc_msgs .= ' Deleted Successfully';
					$this->session->set_flashdata('suc_msgs',$suc_msgs );
					redirect(current_url(), 'location', '301'); 
				}
				else
				{
					$err_msgs = $result;
					$this->session->set_flashdata('err_msgs', $err_msgs);
					redirect(current_url(), 'location', '301'); 
				}
			}
			else
			{
				$err_msgs='Select Supplier Invoice to delete';
				$this->session->set_flashdata('err_msgs', $err_msgs);
				redirect(current_url(), 'location', '301'); 
			}		
			
			redirect(base_url().'suppliers/invoice', 'location', '301'); 
		}
		
       
		if(isset($_POST['OkFilter']))
		{// coming from search button	
			$_SESSION['search_supplier_invoice_data'] = array(
				's_invoice_date'=> $_POST['invoice_date'], 
				's_invoice_number'=>$_POST['invoice_number'],
				's_from_date'=>$_POST['from_date'],
				's_to_date'=>$_POST['to_date'],
				's_no_of_records'=>$_POST['no_of_rows']		
				);								
		}
		else
		{			
			$_SESSION['search_supplier_invoice_data']['s_invoice_date']	= '';
			$_SESSION['search_supplier_invoice_data']['s_invoice_number'] = '';
			$_SESSION['search_supplier_invoice_data']['s_supplier_id_name']='';
			$_SESSION['search_supplier_invoice_data']['s_from_date']='';
			$_SESSION['search_supplier_invoice_data']['s_to_date']='';
			
			if (isset($_SESSION['search_supplier_invoice_data']['s_no_of_records']) && $_SESSION['search_supplier_invoice_data']['s_no_of_records'] != '')
				$per_page = $_SESSION['search_supplier_invoice_data']['s_no_of_records'];
			else
				$per_page = RECORDS_PER_PAGE;
			
			$_SESSION['search_supplier_invoice_data']['s_no_of_records']=$per_page;			
		}
		
					
		$search_data = array(
			'invoice_date'=> $_SESSION['search_supplier_invoice_data']['s_invoice_date'], 
			'invoice_number'=>$_SESSION['search_supplier_invoice_data']['s_invoice_number'],			
			'from_date'=>$_SESSION['search_supplier_invoice_data']['s_from_date'],
			'to_date'=>$_SESSION['search_supplier_invoice_data']['s_to_date']			
			);	

		$order_by = 'supplier_invoice_id DESC';
		
		////////////export////////////
		$is_file_downloaded = false;
   
		
		/////////////view report/////////////
        if ($is_file_downloaded === false) 
		{				
			$pagination_uri_segment = 3;
			if (isset($_SESSION['search_supplier_invoice_data']['s_no_of_records']) && $_SESSION['search_supplier_invoice_data']['s_no_of_records'] != '')
				$per_page = $_SESSION['search_supplier_invoice_data']['s_no_of_records'];
			else
				$per_page = RECORDS_PER_PAGE;
				
			if ($this->uri->segment($pagination_uri_segment) == '') 
				$segment = 0;
			else 
				$segment = $this->uri->segment($pagination_uri_segment);
			$option_param=array();
			$supplier_invoice_data = $this->supplier_invoice_mod->get_data($order_by,$per_page,$segment,$search_data, $per_page,$option_param);
			
			$all_total = $this->supplier_invoice_mod->total_count;
			$this->load->library('pagination'); // pagination class		
			$config = array();
			$config = $this->utils_model->setup_pagination_option($all_total, 'Supplier_invoice/index', $per_page, $pagination_uri_segment);
			$this->pagination->initialize($config);
			$data['pagination'] = $this->pagination->create_links();
			
			$data['total_records'] = $all_total;
			$data['supplier_invoice_data'] = $supplier_invoice_data;
	
						
			$this->load->view('basic/header', $data);
			$this->load->view('supplier/supplier_invoice', $data);
			$this->load->view('basic/footer', $data);
		}	
    }
	
	public function invoiceadd() /*Add New Supplier Invoice*/
	{
		$page_name = "supplier_invoice_add";
		$data['page_name']=$page_name;
		$this->load->model('supplier_invoice_mod');	
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		if (isset($_POST['action']) && $_POST['action']=='OkSaveData')
		{		
			
			$this->form_validation->set_rules('invoice_type', 'Invoice Type', 'trim|required|in_list[IN,OUT]');
			$this->form_validation->set_rules('supplier_id_name', 'Supplier', 'trim|required');
			$this->form_validation->set_rules('invoice_number', 'Invoice Number', 'trim|required');
			$this->form_validation->set_rules('invoice_date', 'Invoice Date', 'trim|required');
			$this->form_validation->set_rules('from_date', 'From Date', 'trim|required');
			$this->form_validation->set_rules('to_date', 'To Date', 'trim|required');			
			$this->form_validation->set_rules('description', 'Description', 'trim|required');
			$this->form_validation->set_rules('bill_amount', 'Billed Amount', 'trim|required');
			$this->form_validation->set_rules('ttl_inv_amount', 'Total Invoice Amount', 'trim|required');
			$this->form_validation->set_rules('tax_amount', 'Tax Amount', 'trim|required');
			$this->form_validation->set_rules('tax1', 'Tax1', 'trim|required');
			$this->form_validation->set_rules('tax2', 'Tax2', 'trim|required');
			$this->form_validation->set_rules('tax3', 'Tax3', 'trim|required');
			$this->form_validation->set_rules('tax1_amount', 'Tax1 Amount', 'trim|required');
			$this->form_validation->set_rules('tax2_amount', 'Tax2 Amount', 'trim|required');
			$this->form_validation->set_rules('tax3_amount', 'Tax3 Amount', 'trim|required');
			
			
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{
				
				$result = $this->supplier_invoice_mod->add($_POST);
			
				if($result === true)
				{
					$supplier_invoice_id = $this->supplier_invoice_mod->supplier_invoice_id;
					
					$this->session->set_flashdata('suc_msgs', 'Supplier Invoice Added Successfully.');	
					if(isset($_POST['button_action']) && trim($_POST['button_action'])!='')
					{
						 $action =  trim($_POST['button_action']);
						 if($action=='save')
							redirect(base_url().'suppliers/invoiceedit/'.param_encrypt($supplier_invoice_id).'/'.param_encrypt($_POST['invoice_type']), 'location', '301');
						 elseif($action=='save_close')
							redirect(base_url().'suppliers/invoice', 'location', '301');
					}
					else
					{
						redirect(base_url().'suppliers/invoiceadd', 'location', '301'); 	
					}
												
					redirect(base_url().'suppliers/invoiceadd', 'location', '301'); 	
					exit();				
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}
		
			}
		}
		
		$data['suppliers_data'] = $this->supplier_mod->get_data('','', '', array('status_id'=>1),array());	
		
		$this->load->view('basic/header',$data);
		$this->load->view('supplier/supplier_invoice_add', $data);
		$this->load->view('basic/footer', $data);		

	}
	
	public function invoiceedit($id=-1,$invoice_type_t=-1) /*Edit Supplier Invoice*/
	{
		$this->load->model('supplier_invoice_mod');	
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		if($id==-1 || $invoice_type_t==-1)
			show_404();
		
		$supplier_invoice_id = param_decrypt($id);
		$invoice_type = param_decrypt($invoice_type_t);
		if(!empty($id))	
		{					
			$order_by='';
			$per_page=1;
			$segment=0;			
			
			$search_data=array('supplier_invoice_id'=>$supplier_invoice_id,'invoice_type'=>$invoice_type);
			//print_r($search_data);
			$option_param=array();
			$supplier_invoice_data_temp = $this->supplier_invoice_mod->get_data($order_by,$per_page, $segment, $search_data,$option_param);		
			
			if(isset($supplier_invoice_data_temp['result']))
				$supplier_invoice_data = current($supplier_invoice_data_temp['result']);
			else
			{
				show_404();
			}				 		
		}		
		else
		{
			show_404();
		}
		
		///////////////// Update /////////////////
		if(isset($_POST['action']) && $_POST['action']=='OkSaveData')
		{	
			$this->form_validation->set_rules('invoice_type', 'Invoice Type', 'trim|required|in_list[IN,OUT]');
			$this->form_validation->set_rules('supplier_id_name', 'Supplier', 'trim|required');
			$this->form_validation->set_rules('invoice_number', 'Invoice Number', 'trim|required');
			$this->form_validation->set_rules('invoice_date', 'Invoice Date', 'trim|required');
			$this->form_validation->set_rules('from_date', 'From Date', 'trim|required');
			$this->form_validation->set_rules('to_date', 'T Date', 'trim|required');			
			$this->form_validation->set_rules('description', 'Description', 'trim|required');
			$this->form_validation->set_rules('bill_amount', 'Billed Amount', 'trim|required');
			$this->form_validation->set_rules('ttl_inv_amount', 'Total Invoice Amount', 'trim|required');
			$this->form_validation->set_rules('tax_amount', 'Tax Amount', 'trim|required');
			$this->form_validation->set_rules('tax1', 'Tax1', 'trim|required');
			$this->form_validation->set_rules('tax2', 'Tax2', 'trim|required');
			$this->form_validation->set_rules('tax3', 'Tax3', 'trim|required');
			$this->form_validation->set_rules('tax1_amount', 'Tax1 Amount', 'trim|required');
			$this->form_validation->set_rules('tax2_amount', 'Tax2 Amount', 'trim|required');
			$this->form_validation->set_rules('tax3_amount', 'Tax3 Amount', 'trim|required');
			
			if ($this->form_validation->run() == FALSE)
			{
				$data['err_msgs'] =validation_errors();
			}
			else
			{
				$_POST['supplier_invoice_id']=$supplier_invoice_id;	
				$result =$this->supplier_invoice_mod->update($_POST);
				
				//echo '<pre>';print_r($result); echo '</pre>';die;
				if($result === true)
				{					
					$this->session->set_flashdata('suc_msgs', 'Supplier Invoice Updated Successfully');					
					if(isset($_POST['button_action']) && trim($_POST['button_action'])!='')
					{
						 $action =  trim($_POST['button_action']);
						 if($action=='save') redirect(base_url().'suppliers/invoiceedit/'.$id.'/'.$invoice_type_t, 'location', '301');
						 elseif($action=='save_close') redirect(base_url().'suppliers/invoice', 'location', '301');
					}
					else
					{
						redirect(base_url().'suppliers/invoice', 'location', '301'); 	
					}											
					redirect(base_url().'suppliers/invoiceedit/'.$id.'/'.$invoice_type_t, 'location', '301'); 				
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}
			}		
		}// -----------Update supplier invoice ------------	
				
		
		$page_name = "supplier_invoice_edit";
		$data['page_name']=$page_name;
		
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		
		$data['supplier_invoice_data'] = $supplier_invoice_data;		
		$data['supplier_invoice_id'] = $supplier_invoice_id;
		
		//$data['suppliers_data'] = $this->supplier_mod->get_data('','', '', array('status_id'=>1),array());
		
		//echo '<pre>';print_r($data);echo '<pre>'; die;
		$this->load->view('basic/header', $data);
		$this->load->view('supplier/supplier_invoice_edit', $data);
		$this->load->view('basic/footer', $data);
		
	}
	
}	