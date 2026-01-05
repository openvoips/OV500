<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Clifilter extends MY_Controller {

    function __construct() {
        parent::__construct();

        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('clifilter_mod');
        if (!check_is_loggedin())
            redirect(site_url(), 'refresh');
        $this->output->enable_profiler(ENABLE_PROFILE);

        $this->logged_user_type = get_logged_user_type();
        $this->logged_user_id = get_logged_user_id();
        $this->logged_account_id = get_logged_account_id();
    }



    
	public function index($account_id_temp=-1,$type='did') 
	{
		if($account_id_temp==-1)
			show_404();
		$page_name = "customer_edit_randomcli";
		$data['page_name']=$page_name;
		
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
			
		if($type!='did')
		$type = 'pstn';
		

		if(isset($_POST['action']) && $_POST['action']=='OkDeleteData')
		{	//echo '<pre>';print_r($_POST);die;
			
			if(!isset($_POST['delete_parameter_two']))
			{
				$this->session->set_flashdata('err_msgs', 'Insufficient Parameters');	
				redirect(current_url(), 'location', '301'); 
			}	
			if(!isset($_POST['delete_id']))
			{
				$err_msgs='Select to delete';
				$this->session->set_flashdata('err_msgs', $err_msgs);
				redirect(current_url(), 'location', '301'); 			
			}
			$account_id = param_decrypt($account_id_temp);
           // print_r($_POST);die;
			switch($_POST['delete_parameter_two'])
			{				
				
					
				case 'didclifilter_delete':			
				
					$delete_id_array = json_decode($_POST['delete_id']);
					//print_r($delete_id_array);
					
					$delete_param_array=array('delete_id'=>$delete_id_array);
				
					$result = $this->clifilter_mod->delete_cli($account_id,$delete_param_array,'did'); 
                 

					if($result === true)
					{						
						$suc_msgs = 'CLI Deleted Successfully';
						$this->session->set_flashdata('suc_msgs',$suc_msgs );
					}
					else
					{
						$err_msgs = $result;
						$this->session->set_flashdata('err_msgs', $err_msgs);
					}
					
					redirect(current_url(), 'location', '301'); 
					break;
                case 'pstnclifilter_delete':			
            
                    $delete_id_array = json_decode($_POST['delete_id']);
                    //print_r($delete_id_array);
                    
                    $delete_param_array=array('delete_id'=>$delete_id_array);
                    
                    $result = $this->clifilter_mod->delete_cli($account_id,$delete_param_array,'pstn'); 

                    //var_dump($result);echo $account_id; ddd($delete_param_array);die;
                    if($result === true)
                    {						
                        $suc_msgs = 'CLI Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs',$suc_msgs );
                    }
                    else
                    {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    
                    redirect(current_url(), 'location', '301'); 
                    break;
				default:
				
					$this->session->set_flashdata('err_msgs', 'Parameter mismatch');
					redirect(current_url(), 'location', '301'); 
			}
			
			
		}

        if($type=='did')
            $param = 'clifilterdid';
        else
            $param = 'clifilterpstn';
	
		{
			$account_id = param_decrypt($account_id_temp);
			$search_data = array('account_id' => $account_id);
            if (check_logged_user_group(array('RESELLER'))) {
                $search_data['parent_account_id'] = $this->logged_account_id;
            } else {
                $search_data['parent_account_id'] = '';
            }
			$option_param=array($param=>true);
            $customers_data_temp = $this->clifilter_mod->get_data('', 1, 0, $search_data, $option_param);

            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else 
                show_404();
            
			 		
		}		
		
		$data['data'] = $customers_data;	


        $data['active_tab']=$type ;
										
		$this->load->view('basic/header',$data);
		$this->load->view('clifilter/clifilters', $data);
		$this->load->view('basic/footer', $data);		
	}	
	
	public function edit($account_id_temp=-1, $id1=-1, $type='') 
	{
		if($account_id_temp==-1 || $id1==-1)
			show_404();
		$page_name = "customer_edit_randomcli";
		$data['page_name']=$page_name;

        if($type!='did')
		$type = 'pstn';
		
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
			
		if (isset($_POST['action']) && $_POST['action']=='OkSaveData')
		{			 // print_r($_POST);
			$account_id= $_POST['account_id'];			
			$customer_ip_id= $_POST['customer_ip_id'];			
            $rule_type= $_POST['rule_type'];							
			$data['account_id'] = $account_id;		

            
         
            $this->form_validation->set_rules('account_id', 'customer ID', 'trim|required');
            $this->form_validation->set_rules('callerid', 'Caller ID', 'trim|required');
            $this->form_validation->set_rules('cli_status', 'Status', 'trim|required');	
            $this->form_validation->set_rules('type', 'Type', 'trim|required');	
            $this->form_validation->set_rules('clifilterdid_id', 'CLI ID', 'trim|required');
									
			if ($this->form_validation->run() == FALSE)
			{// error
             
				$data['err_msgs'] =validation_errors();
			}
			else
			{					
				$result =$this->clifilter_mod->update_didfiltercli($_POST);
				//ddd($_POST); ddd($result);die;
				if($result === true)
				{//success													
					$this->session->set_flashdata('suc_msgs', 'CLI Updated Successfully');					
				
					redirect(base_url().'crs/clifilter/edit/'.$account_id_temp.'/'.$id1.'/'.$type, 'location', '301');
							
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}
					
			}//if
		
		} //if(isset($_POST['OkSaveData']))
		///////////////////////////		
        $customers_data =$cli_data=[];
		if(!empty($id1) )	
		{
			$account_id = param_decrypt($account_id_temp);
			$clifilterdid_id = param_decrypt($id1);
			
            
			
			$search_data=array('account_id'=>$account_id);
            if($type=='did')
			    $option_param=array('clifilterdid'=>true,'clifilterdid_id'=>$clifilterdid_id);
            else
                $option_param=array('clifilterpstn'=>true,'clifilterpstn_id'=>$clifilterdid_id);
			$customers_data_temp = $this->clifilter_mod->get_data('',1,0,$search_data,$option_param);		
			//ddd($customers_data_temp);die;
			if(isset($customers_data_temp['result']))
				$customers_data = current($customers_data_temp['result']);
			else
			{
				show_404();
			}	

            if(isset($customers_data['clifilterdid']) && count($customers_data['clifilterdid'])>0)
				$cli_data = current($customers_data['clifilterdid']);
            elseif(isset($customers_data['clifilterpstn']) && count($customers_data['clifilterpstn'])>0)
                $cli_data = current($customers_data['clifilterpstn']);
			else
			{
				show_404();
			}	
 		
		}		
		else
		{
			show_404();
		}
		
		$data['data'] = $customers_data;
        $data['cli_data'] = $cli_data;		
        $data['active_tab']=$type ;

										
		$this->load->view('basic/header',$data);
		$this->load->view('clifilter/clifilter_edit', $data);
		$this->load->view('basic/footer', $data);		
	}	
	

	public function add($id1=-1,$type='') 
	{
		if($id1==-1)
			show_404();
		$page_name = "customer_add_randomcli";
		$data['page_name']=$page_name;
		if($type!='did')
		$type = 'pstn';
		
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
			
		if (isset($_POST['action']) && $_POST['action']=='OkSaveDataType2')
		{
			$account_id= $_POST['account_id'];								
			$data['account_id'] = $account_id;		
			
			$this->form_validation->set_rules('account_id', 'customer ID', 'trim|required');
            $this->form_validation->set_rules('callerid', 'Caller ID', 'trim|required');
            $this->form_validation->set_rules('cli_status', 'Status', 'trim|required');	
            $this->form_validation->set_rules('type', 'Type', 'trim|required');	
						
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{		
				$result =$this->clifilter_mod->add_didfiltercli($_POST);
				//ddd($_POST); ddd($result);die;
				if($result === true)
				{//success													
					$this->session->set_flashdata('suc_msgs', 'CLI Added Successfully');	
					redirect(site_url('crs/clifilter/add/'.param_encrypt($account_id).'/'.$type), 'location', '301');
						
				}
				else
				{
					$err_msgs = $result;
					$data['err_msgs'] = $err_msgs;
				}
					
			}//if
		
		} //if(isset($_POST['OkSaveData']))
		
		elseif (isset($_POST['action']) && $_POST['action']=='OkSaveDataFile')
		{
			$account_id= $_POST['account_id'];								
			$data['account_id'] = $account_id;		
			
			$this->form_validation->set_rules('account_id', 'customer ID', 'trim|required');
            
						
			if ($this->form_validation->run() == FALSE)
			{// error
				$data['err_msgs'] =validation_errors();
			}
			else
			{		

				$config['upload_path']          = './uploads/';
				$config['allowed_types']        = 'csv'; //xlsx|xls|csv|txt
				$config['file_name']     		= 'clifilter_'.date('YmdHis');
				$config['file_ext_tolower']     = TRUE;
				$config['max_size']             = 0;
				$this->load->library('upload', $config);
				if(!$this->upload->do_upload('file'))
				{
					$data['err_msgs'] =$this->upload->display_errors();	
				}
				else
				{
					$data = $this->upload->data();
					$file_with_path = './uploads/'.$data['file_name'];
					$file = fopen($file_with_path,"r");
					$cnt = 0;
					$error = 0; $error_msg = '';
					$csv_data = array();
					while(! feof($file)){
						$d = fgetcsv($file);
						$csv_data[] = $d;						
						if($cnt>0 && is_array($d)){

							$error_type = '';
							$pref = trim($d[0]); if(!preg_match('/^\d{1,15}$/', $pref)) {$error++; $error_type.='Prefix ('. $pref.')';}
							//$dest = trim($d[1]); if(!preg_match('/^[a-z0-9 \/ \-()&\.]+$/i', $dest)) {$error++; $error_type.='Destination ('.$dest.')';}
								
							if($error) {$lineno = $cnt+1; $error_msg = 'Error in Line no. '.$lineno.' - column ['.$error_type.']'; /*echo 'Error in '.$cnt.' ['.$error_type.']';*/ break;}
							else
							{
								//echo $pref.'|'.$dest.'|'.$ppm.'|'.$ppc.'|'.$min.'|'.$res.'|'.$grace.'|'.$mul.'|'.$add.'|'.$stat.'<br>';
							}
						}
						++$cnt;
					}
					fclose($file);
					unlink($file_with_path);

					//ddd($csv_data);die;
					if($error)
					{
						$this->session->set_flashdata('err_msgs', $error_msg);
						redirect(site_url('crs/clifilter/add/'.param_encrypt($account_id).'/'.$type), 'location', '301');
					}
					else
					{
						unset($csv_data[0]);
						$result =$this->clifilter_mod->add_didfiltercli_bulk($_POST,$csv_data);
						//ddd($_POST); ddd($result);die;
						if($result === true)
						{//success													
							$this->session->set_flashdata('suc_msgs', 'CLI Added Successfully');	
							redirect(site_url('crs/clifilter/index/'.param_encrypt($account_id).'/'.$type), 'location', '301');// 301 redirected	
						}
						else
						{
							$err_msgs = $result;
							$data['err_msgs'] = $err_msgs;
						}
					}
				}


				
					
			}//if
		
		} 
		///////////////////////////		
		if(!empty($id1))	
		{
			$account_id = param_decrypt($id1);
			$order_by='';
			$per_page=1;
			$segment=0;
			$search_data=array('account_id'=>$account_id);;
			$option_param=array();
			$customers_data_temp = $this->clifilter_mod->get_data($order_by,$per_page,$segment,$search_data,$option_param);		
			
			if(isset($customers_data_temp['result']))
				$customers_data = current($customers_data_temp['result']);
			else
			{
				show_404();
			}	
		}		
		else
		{
			show_404();
		}
	
		$data['data'] = $customers_data;		
		$data['account_id'] = $account_id;
        $data['active_tab']=$type ;
												
		$this->load->view('basic/header',$data);
		$this->load->view('clifilter/clifilter_add', $data);
		$this->load->view('basic/footer', $data);		
	}

    public function download($file) {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');

        $file = param_decrypt($file);
        switch ($file) {
           
            case 'abcd':
                $filename = 'abcd.csv';

                $fullPath = 'uploads/sample/' . $filename;

                break;

            default:

                $filename = $file;
                $fullPath = 'uploads/sample/' . $filename;

                if (file_exists($fullPath)) {
                    
                } else
                    show_404();
        }

        $this->load->helper('download');
        force_download($fullPath, NULL, true);
        exit;
    }

}
