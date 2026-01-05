<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
class Blocknumbers extends MY_Controller {
    
	function __construct()
	{
		parent::__construct();
		
		$this->load->library('pagination'); // pagination class			
		$this->form_validation->set_error_delimiters('','');
		$this->load->model('blocknumbers_mod');		
		//permission check		
		if(!check_is_loggedin())
			redirect(base_url(), 'refresh');
				
		//$this->output->enable_profiler(ENABLE_PROFILE);	
			
	}	
	
	public function index($arg1='',$format='')
	{
		$page_name = "blocknumbers_number";
		$data['page_name']=$page_name;
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		$account_id=get_logged_account_id();
		
			
		 
		
		
		if(isset($_POST['action']) && $_POST['action']=='OkDeleteData')
		{	
						
			$delete_id_array = json_decode($_POST['delete_id']);
			
					
			if(isset($_POST['delete_id']) && count($delete_id_array)>0)
			{
				$delete_param_array=array('delete_id'=>$delete_id_array);
				$result = $this->blocknumbers_mod->delete($delete_param_array); 

				if($result === true)
				{
					$suc_msgs = count($delete_id_array).' Number';
					if(count($delete_id_array)>1)
						$suc_msgs .= 's';
					$suc_msgs .= ' Deleted Successfully';
					$this->session->set_flashdata('suc_msgs',$suc_msgs );
					redirect(base_url().'blocknumbers', 'location', '301');
				}
				else
				{
					$err_msgs = $result;
					$this->session->set_flashdata('err_msgs', $err_msgs);
					redirect(base_url().'blocknumbers', 'location', '301');
				}
			}
			else
			{
				$err_msgs='Select Number(s) to delete';
				$this->session->set_flashdata('err_msgs', $err_msgs);
				redirect(base_url().'blocknumbers', 'location', '301');
			}		
			
			redirect(base_url().'blocknumbers', 'location', '301');
		}// end of delete section	
		
		if(isset($_POST['search_action']))
		{// coming from search button
			$_SESSION['search_blocknumbers_data'] = array(
				'dstnumber'=> $_POST['dstnumber'],
's_no_of_records'=> $_POST['no_of_records']

				
			);			
		}
		else
		{
			$_SESSION['search_blocknumbers_data']['dstnumber'] = isset($_SESSION['search_blocknumbers_data']['dstnumber'])? $_SESSION['search_blocknumbers_data']['dstnumber'] : '';
			$_SESSION['search_blocknumbers_data']['s_no_of_records'] = isset($_SESSION['search_blocknumbers_data']['s_no_of_records']) ? $_SESSION['search_blocknumbers_data']['s_no_of_records'] : RECORDS_PER_PAGE;
 
		}
		
		
		
		$search_data = array(
			'dstnumber'=> $_SESSION['search_blocknumbers_data']['dstnumber']
		);
		if (check_logged_user_group(ADMIN_ACCOUNT_ID)) {
		
		} elseif (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
			$search_data['account_id']=get_logged_account_id();
		} 
		
		$order_by = 'id DESC';
		$is_file_downloaded = false;
		
		if($arg1=='export' && $format!='')
		{
			$format = param_decrypt($format);
			
			$option_param=array();
			$blocknumbers_data = $this->blocknumbers_mod->get_data($order_by,'', '', $search_data,$option_param);
			$search_array=array();

			// column titles
			$export_header = array('Blocked Destination Numbers');

				
			
			if(count($blocknumbers_data['result']) > 0)
			{		
									
				foreach($blocknumbers_data['result'] as $blocknumbers_data_temp)
				{	
					$export_data[]	= array($blocknumbers_data_temp['dstnumber']);
				}
				
			}
			else
				
				$export_data = array('');
			
			$file_name='blocknumbers_numbers';
			
			
			
			$this->load->library('Export');	
			$downloaded_message = $this->export->download($file_name,$format,$search_array,$export_header,$export_data); 
			
			
			if(gettype($downloaded_message)=='string')
				$data['err_msgs'] = $downloaded_message;
			else
				$is_file_downloaded = true;			
		} // end of export 
		
		if($is_file_downloaded ===false)
		{		
			/****** pagination code start here **********/
			//$pagination_uri_segment = 3;
			///$per_page = RECORDS_PER_PAGE;

			       $pagination_uri_segment = $this->uri->segment(3, 0);


  if (isset($_SESSION['search_blocknumbers_data']['s_no_of_records']) && $_SESSION['search_blocknumbers_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_blocknumbers_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;




			if($this->uri->segment($pagination_uri_segment)==''){ $segment= 0; }
			else{ $segment= $this->uri->segment($pagination_uri_segment); }
			
			$option_param=array();
			
			$blocknumbers_data = $this->blocknumbers_mod->get_data($order_by,$per_page, $segment, $search_data,$option_param);	
			$total = $this->blocknumbers_mod->total_count;	
			//echo '<pre>';print_r($blocknumbers_data); echo '</pre>';	die;
			
			$config = array();
			$config = $this->utils_model->setup_pagination_option($total, 'blocknumbers/index', $per_page, $pagination_uri_segment );		
			$this->pagination->initialize($config);			
			
			/****** pagination code ends  here **********/
			$data['pagination']=$this->pagination->create_links();	
			$data['blocknumbers_data'] = $blocknumbers_data;
			
			//echo '<pre>';print_r($_POST); echo '</pre>';
			
			if (check_logged_account_type(array('RESELLER', 'CUSTOMER')))
				$parent_account_id = get_logged_account_id();
			else
				$parent_account_id = '';
			$data['customer_options'] =	$this->utils_model->get_customers($parent_account_id);
			
			$this->load->view('basic/header',$data);
			$this->load->view('blocknumbers/blocknumbers_list', $data);
			$this->load->view('basic/footer', $data);
		}		
		
		
		
			
	}


	public function add(){
		$page_name = "add_blocknumbers_number";
		$data['page_name']=$page_name;
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		$account_id=get_logged_account_id();
	
		
		if (isset($_POST['action']) && $_POST['action']=='OkSaveData')
		{			
			$this->form_validation->set_rules('dstnumber', 'Number', 'trim|required|min_length[4]');	
			$this->form_validation->set_rules('account_id', 'Customer', 'trim|required');	
		
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{	
				$result = $this->blocknumbers_mod->add($_POST);
				
				if($result === true)
				{//success
					$id = $this->blocknumbers_mod->id;					
					$this->session->set_flashdata('suc_msgs', 'Blocked Number Added Successfully.');	
					
					if(isset($_POST['button_action']) && trim($_POST['button_action'])=='save')
						redirect(base_url().'blocknumbers', 'location', '301');
					else
						redirect(base_url().'blocknumbers', 'location', '301'); // 301 redirected	
					
					exit();	
					
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}		
			}	
		}
		if (isset($_POST['action']) && $_POST['action']=='OkSaveFile')
		{
					
				$config['upload_path']          = 'uploads/blocknumbers/';  //'uploads/dnd/'.SITE_SUBDOMAIN.'/';
				$config['allowed_types']        = 'csv';
				$config['file_name']     		= strtolower($account_id).'_blocknumbers_'.date('YmdHis');
				$config['file_ext_tolower']     = TRUE;
				$config['max_size']             = 0;
				$config['encrypt_name'] = TRUE;
			
						
				$this->load->library('upload', $config);				
				if(!$this->upload->do_upload('file_blocknumbers_number'))
				{					
					$this->session->set_flashdata('err_msgs', $this->upload->display_errors());	
					//redirect(base_url().'blocknumbers', 'location', '301');
				}
				else
				{
					
					ini_set('memory_limit', '2048M');
					$data = array('upload_data' => $this->upload->data());
					$file_with_path = 'uploads/blocknumbers/'.$data['upload_data']['file_name'];
					$csv = array_map('str_getcsv', file($file_with_path));
					//var_dump($csv);die;
								
					
					$filename = $config['file_name'].'.csv';
					
					//echo '<pre>';print_r($csv );echo '<pre>';die;
					$account_id=trim($_POST['account_id']);
					$result = $this->blocknumbers_mod->add_blocknumbers_number($account_id, $filename, $csv);				
					if($result === true)
					{//success						
						$this->session->set_flashdata('suc_msgs', 'Blocked Number Added Successfully');							
						redirect(base_url().'blocknumbers', 'location', '301'); 
						exit();
					}
					else
					{
						$err_msgs = $result;
						$data['err_msgs'] = $err_msgs;
					}
					
				}
			
		}

		if (check_logged_account_type(array('RESELLER', 'CUSTOMER')))
			$parent_account_id = get_logged_account_id();
		else
			$parent_account_id = '';
		$data['customer_options'] =	$this->utils_model->get_customers($parent_account_id);
		
		$this->load->view('basic/header',$data);
		$this->load->view('blocknumbers/blocknumbers_add', $data);
		$this->load->view('basic/footer', $data);
		
	}
	


}
