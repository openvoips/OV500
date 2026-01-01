<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
class Blockcli extends MY_Controller {
    
	function __construct()
	{
		parent::__construct();
		
		$this->load->library('pagination'); // pagination class			
		$this->form_validation->set_error_delimiters('','');
		$this->load->model('blockcli_mod');		
		//permission check		
		if(!check_is_loggedin())
			redirect(base_url(), 'refresh');
				
		//$this->output->enable_profiler(ENABLE_PROFILE);	
		
			
	}	
	
	public function index($arg1='',$format='')
	{
		$page_name = "blockcli_number";
		$data['page_name']=$page_name;
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		$account_id=get_logged_account_id();
		
			
		//if (!check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) 
		//show_404('403');
	//	echo $account_id.'---'.get_logged_account_type();
		
		//
		
		if(isset($_POST['action']) && $_POST['action']=='OkDeleteData')
		{	
						
			$delete_id_array = json_decode($_POST['delete_id']);
			
					
			if(isset($_POST['delete_id']) && count($delete_id_array)>0)
			{
				$delete_param_array=array('delete_id'=>$delete_id_array);
				$result = $this->blockcli_mod->delete($delete_param_array); 

				if($result === true)
				{
					$suc_msgs = count($delete_id_array).' CLI Number';
					if(count($delete_id_array)>1)
						$suc_msgs .= 's';
					$suc_msgs .= ' Deleted Successfully';
					$this->session->set_flashdata('suc_msgs',$suc_msgs );
					redirect(base_url().'blockcli', 'location', '301');
				}
				else
				{
					$err_msgs = $result;
					$this->session->set_flashdata('err_msgs', $err_msgs);
					redirect(base_url().'blockcli', 'location', '301');
				}
			}
			else
			{
				$err_msgs='Select CLI Number(s) to delete';
				$this->session->set_flashdata('err_msgs', $err_msgs);
				redirect(base_url().'blockcli', 'location', '301');
			}		
			
			redirect(base_url().'blockcli', 'location', '301');
		}// end of delete section	
		
		if(isset($_POST['search_action']))
		{// coming from search button
			$_SESSION['search_blockcli_data'] = array(
				'cli'=> $_POST['cli']				
			);			
		}
		else
		{
			$_SESSION['search_blockcli_data']['cli'] = isset($_SESSION['search_blockcli_data']['cli'])? $_SESSION['search_blockcli_data']['cli'] : '';
			 
		}
		
		
		
		$search_data = array(
			'cli'=> $_SESSION['search_blockcli_data']['cli']
		);
		
		$order_by = 'id DESC';
		$is_file_downloaded = false;
		
		if($arg1=='export' && $format!='')
		{
			$format = param_decrypt($format);
			
			$option_param=array();
			$blockcli_data = $this->blockcli_mod->get_data($order_by,'', '', $search_data,$option_param);
			$search_array=array();
			//if($_SESSION['search_blockcli_data']['s_blockcli_number']!='')
				//$search_array['DND Number']=$_SESSION['search_blockcli_data']['s_blockcli_number'];					
			
			// column titles
			$export_header = array('Blocked CLI');

				
			
			if(count($blockcli_data['result']) > 0)
			{		
									
				foreach($blockcli_data['result'] as $blockcli_data_temp)
				{	
					$export_data[]	= array($blockcli_data_temp['cli']);
				}
				
			}
			else
				
				$export_data = array('');
			
			$file_name='blockcli_numbers';
			
			
			
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
			$pagination_uri_segment = 3;
			$per_page = RECORDS_PER_PAGE;
			
			if($this->uri->segment($pagination_uri_segment)==''){ $segment= 0; }
			else{ $segment= $this->uri->segment($pagination_uri_segment); }
			
			$option_param=array();
			
			$blockcli_data = $this->blockcli_mod->get_data($order_by,$per_page, $segment, $search_data,$option_param);	
			$total = $this->blockcli_mod->total_count;	
			//echo '<pre>';print_r($blockcli_data); echo '</pre>';	die;
			
			$config = array();
			$config = $this->utils_model->setup_pagination_option($total, 'blockcli/index', $per_page, $pagination_uri_segment );		
			$this->pagination->initialize($config);			
			
			/****** pagination code ends  here **********/
			$data['pagination']=$this->pagination->create_links();	
			$data['blockcli_data'] = $blockcli_data;
			
			//echo '<pre>';print_r($_POST); echo '</pre>';	
		
			if (check_logged_account_type(array('RESELLER', 'CUSTOMER')))
				$parent_account_id = get_logged_account_id();
			else
				$parent_account_id = '';
			$data['customer_options'] =	$this->utils_model->get_customers($parent_account_id);
			
			$this->load->view('basic/header',$data);
			$this->load->view('blockcli/blockcli_list', $data);
			$this->load->view('basic/footer', $data);
		}		
		
		
		
			
	}
	
	public function add(){
		$page_name = "add_blockcli_number";
		$data['page_name']=$page_name;
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		$account_id=get_logged_account_id();
	
		
		if (isset($_POST['action']) && $_POST['action']=='OkSaveData')
		{			
			$this->form_validation->set_rules('cli', 'CLI', 'trim|required|min_length[4]');	
			$this->form_validation->set_rules('account_id', 'Customer', 'trim|required');	
		
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{	
				$result = $this->blockcli_mod->add($_POST);
				
				if($result === true)
				{//success
					$id = $this->blockcli_mod->id;					
					$this->session->set_flashdata('suc_msgs', 'Blocked CLI Added Successfully.');	
					
					if(isset($_POST['button_action']) && trim($_POST['button_action'])=='save')
						redirect(base_url().'blockcli', 'location', '301');
					else
						redirect(base_url().'blockcli', 'location', '301'); // 301 redirected	
					
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
					
				$config['upload_path']          = 'uploads/blockcli/';  //'uploads/dnd/'.SITE_SUBDOMAIN.'/';
				$config['allowed_types']        = 'csv';
				$config['file_name']     		= strtolower($account_id).'_blockcli_'.date('YmdHis');
				$config['file_ext_tolower']     = TRUE;
				$config['max_size']             = 0;
				$config['encrypt_name'] = TRUE;
			
						
				$this->load->library('upload', $config);				
				if(!$this->upload->do_upload('file_blockcli_number'))
				{					
					$this->session->set_flashdata('err_msgs', $this->upload->display_errors());	
					//redirect(base_url().'blockcli', 'location', '301');
				}
				else
				{
					
					ini_set('memory_limit', '2048M');
					$data = array('upload_data' => $this->upload->data());
					$file_with_path = 'uploads/blockcli/'.$data['upload_data']['file_name'];
					$csv = array_map('str_getcsv', file($file_with_path));
					//var_dump($csv);die;
								
					
					$filename = $config['file_name'].'.csv';
					
					//echo '<pre>';print_r($csv );echo '<pre>';die;
					$account_id=trim($_POST['account_id']);
					
					$result = $this->blockcli_mod->add_blockcli_number($account_id, $filename, $csv);				
					if($result === true)
					{//success						
						$this->session->set_flashdata('suc_msgs', 'Blocked CLI Added Successfully');							
						redirect(base_url().'blockcli', 'location', '301'); 
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
		$this->load->view('blockcli/blockcli_add', $data);
		$this->load->view('basic/footer', $data);
		
	}

}
