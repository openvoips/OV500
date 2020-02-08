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

class Sysconfig extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('pagination');
        $this->load->model('Utils_model');
        $this->load->model('Sysconfig_mod');
        $this->load->model('Route_mod');
    }

    function ExcRate() {
        $data['page_name'] = "currency_add";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('currency', 'Currency', 'trim|required');
            $this->form_validation->set_rules('exc_rate', 'Exchange Rate', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $currency_id = $_POST['currency'];
                $result = $this->Sysconfig_mod->add($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Exchange Rate Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'currency/index/' . param_encrypt($result['id']), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'currency', 'location', '301');
                    } else {
                        redirect(base_url() . 'currency', 'location', '301');
                    }
                    redirect(base_url() . 'currency/index/' . param_encrypt($currency_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }

        $search_data = Array();
        $data['currency_dropdown'] = $this->Sysconfig_mod->get_currency(array('currency_id' => 'ASC'), $search_data);
        $this->load->view('basic/header', $data);
        $this->load->view('services/ExcRate', $data);
        $this->load->view('basic/footer', $data);
    }

    public function inConfig($arg1 = '', $format = '') {
        $data['page_name'] = "inConfig";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('pGConfig', 'add'))
            show_404('403');
        $logged_account_id = get_logged_account_id();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('data[company_name]', 'Business name / Company name', 'trim|required|min_length[5]|max_length[100]');
            $this->form_validation->set_rules('data[address]', 'Business / Company Address', 'trim|required|min_length[10]|max_length[1000]');
            $this->form_validation->set_rules('data[bank_detail]', 'Business / Company Bank Account Detail where want to recive Payment', 'trim|required|min_length[10]|max_length[1000]');
            $this->form_validation->set_rules('data[support_text]', 'Customer / Billing Support Detail In invoice', 'trim|required|min_length[10]|max_length[1000]');

           
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
               
			   	///////upload logo if exists///////////////
				if(is_uploaded_file($_FILES['invoicelogo']['tmp_name'])) 
				{
					$upload_path = 'uploads/invoicelogo';
					if(!file_exists($upload_path)) 
						mkdir($upload_path);
						
					chmod($upload_path, 0777);	
					
					$file_name = $logged_account_id.'_'.time();	
				
					$config['upload_path']          = $upload_path;
					$config['allowed_types']        = 'gif|jpg|png|jpeg';
					$config['file_name']     		= $file_name;
					$config['file_ext_tolower']     = TRUE;
					$config['max_size'] = 0;
					
					$this->load->library('upload', $config);
					
					if($this->upload->do_upload('invoicelogo'))
					{					
						$uploaded_data_array = $this->upload->data();						
						$client_name =  $uploaded_data_array['client_name'];
						$file_name	= $uploaded_data_array['file_name'];
						
						/////////resize image//////////
						$width = 300;
						$height = 300;
						$config = array();
						$this->load->library('image_lib');
						$config['image_library']  = 'gd2';
						$config['source_image']   = $upload_path.'/'. $file_name;       
						$config['create_thumb']   = false;
						$config['maintain_ratio'] = TRUE;
						$config['width']          = $width;
						$config['height']         = $height;
						//$config['new_image']      = $upload_path.'/'. 'thumb_'.$file_name;               
						$this->image_lib->initialize($config);
						$this->image_lib->resize();							
						$_POST['data']['logo'] = $file_name;
					}
					else
					{
						$error =  $this->upload->display_errors();
						$data['err_msgs'] = $error;
					}
				}
				

                if(!isset($data['err_msgs']) || $data['err_msgs']=='')
				{
				$result = $this->Sysconfig_mod->inConfig_update($_POST['data']);
                if ($result['status']) 
				{				
					////////delete previous logo if uploaded new//////
					if(isset($_POST['data']['logo']) && $_POST['data']['logo']!='' && isset($_POST['existing_invoicelogo']) && $_POST['existing_invoicelogo']!='')
					{
						$existing_invoicelogo = trim($_POST['existing_invoicelogo']);						
						$upload_path = 'uploads/invoicelogo';
						$file_path=$upload_path.'/'.$existing_invoicelogo;
						if(file_exists($file_path))
						{
							unlink($file_path);
						}			
					}		
				
                    $this->session->set_flashdata('suc_msgs', 'Invoice Setup Updated Successfully');
					redirect(base_url().'sysconfig/inconfig', 'location', '301'); // 301 redirected	
					exit();
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
				}
            }
        }
      
        $data['data'] = $this->Sysconfig_mod->inConfig_data($logged_account_id);

        $this->load->view('basic/header', $data);
        $this->load->view('services/inConfig', $data);
        $this->load->view('basic/footer', $data);
    }

    public function pGConfig($arg1 = '', $format = '') {
        $data['page_name'] = "pGConfig";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('pGConfig', 'add'))
            show_404('403');
        $_POST['logged_user_type'] = get_logged_account_type();
        $_POST['logged_current_customer_id'] = get_logged_account_id();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('business', 'Business name or Business Email', 'trim|required|min_length[5]|max_length[50]');
            $this->form_validation->set_rules('pdt_identity_token', 'Payment SDK Token Key', 'trim|required|min_length[10]|max_length[300]');
            $_POST['logged_user_type'] = get_logged_account_type();
            $_POST['logged_current_customer_id'] = get_logged_account_id();
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->Sysconfig_mod->pGConfig($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Added Successfully');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }

        $_POST['logged_user_type'] = get_logged_account_type();
        $_POST['logged_current_customer_id'] = get_logged_account_id();
        $_POST['action'] = 'search';
        $result = $this->Sysconfig_mod->pGConfig($_POST);
        $data['data'] = $result['result'][0];
        $this->load->view('basic/header', $data);
        $this->load->view('services/pGConfig', $data);
        $this->load->view('basic/footer', $data);
    }

    function signupConfig() {
        $page_name = "signup";
        if (!check_account_permission('signup', 'view'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		
		$logged_account_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
		$logged_account_level = get_logged_account_level();

        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('signup', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'sysconfig/signupConfig', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->Sysconfig_mod->signupConfig_delete($delete_param_array);
                if ($result['status'] === true) {
                    $suc_msgs = count($delete_id_array) . ' Singup Config';
                    if (count($delete_id_array) > 1)
                        $suc_msgs .= 's';
                    $suc_msgs .= ' Deleted Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    redirect(current_url(), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } else {
                $err_msgs = 'Select row to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
            redirect(base_url() . 'sysconfig/signupConfig', 'location', '301');
        }
        //$data['currency_data'] = $this->Utils_model->get_currencies();
        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_signup_data'] = array(
                's_signupkey' => $_POST['signupkey'],
                's_signup_plan' => $_POST['signup_plan'],
                's_status_id' => $_POST['status_id'],
                's_no_of_rows' => $_POST['no_of_rows'],
            );
        } else {
            $r = $this->uri->segment(2);               
                $_SESSION['search_signup_data']['s_signupkey'] = isset($_SESSION['search_signup_data']['s_signupkey']) ? $_SESSION['search_signup_data']['s_signupkey'] : '';
                $_SESSION['search_signup_data']['s_signup_plan'] = isset($_SESSION['search_signup_data']['s_signup_plan']) ? $_SESSION['search_signup_data']['s_signup_plan'] : '';
				$_SESSION['search_signup_data']['s_status_id'] = isset($_SESSION['search_signup_data']['s_status_id']) ? $_SESSION['search_signup_data']['s_status_id'] : '';   			
                $_SESSION['search_signup_data']['s_no_of_rows'] = isset($_SESSION['search_signup_data']['s_no_of_rows']) ? $_SESSION['search_signup_data']['s_no_of_rows'] : RECORDS_PER_PAGE;
        }

        $search_data = array(
            'signupkey' => $_SESSION['search_signup_data']['s_signupkey'],
            'signup_plan' => $_SESSION['search_signup_data']['s_signup_plan'],
            'status_id' => $_SESSION['search_signup_data']['s_status_id'],            
	        );
		
		if(strtolower($logged_account_type) !='admin')
		{
			$search_data['business_holder_account_id'] = $logged_account_id;
			$search_data['business_holder'] = $logged_account_type.$logged_account_level;
		}
		else
		{
			$search_data['business_holder'] = $logged_account_type;
		}
		
        $order_by = '';
        $pagination_uri_segment = $this->uri->segment(3, 0);
        if (isset($_SESSION['search_signup_data']['s_no_of_rows']) && $_SESSION['search_signup_data']['s_no_of_rows'] != '')
            $per_page = $_SESSION['search_signup_data']['s_no_of_rows'];
        else
            $per_page = RECORDS_PER_PAGE;

        $response = $this->Sysconfig_mod->signupConfig_data($order_by, $pagination_uri_segment, $per_page, $search_data);
        $config = array();
        $config = $this->utils_model->setup_pagination_option($response['total'], 'sysconfig/signupConfig', $per_page, 3);
        $this->pagination->initialize($config);
        $data['page_name'] = $page_name;
        $data['pagination'] = $this->pagination->create_links();
        $data['listing_data'] = $response['result'];
        $data['total_records'] = $response['total'];
        $this->load->view('basic/header', $data);
        $this->load->view('services/signup', $data);
        $this->load->view('basic/footer', $data);
    }

    function AddSignup() {        
        $page_name = "esignup";
        $logged_account_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
		$logged_account_level = get_logged_account_level();
        
         if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('signup_plan', 'Plan', 'trim|required');
			$this->form_validation->set_rules('tariff_id', 'Tariff', 'trim');
			$this->form_validation->set_rules('dialplan_id', 'Dialplan', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
			
			$_POST['business_holder'] = $logged_account_type.$logged_account_level;
			$_POST['business_holder_account_id'] = $logged_account_id;
			$_POST['status_id'] = '1';
			//echo '<pre>';print_r($_POST);die;////
                $result = $this->Sysconfig_mod->signupConfig_add($_POST);
				//var_dump($result);die;
				
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
						$signupkey = $result['signupkey'];
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'sysconfig/eSignupConfig/' . param_encrypt($signupkey), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'sysconfig/signupConfig', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'sysconfig/signupConfig', 'location', '301');
                    }
                    redirect(base_url() . 'sysconfig/eSignupConfig' . param_encrypt($signupkey), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        
       
       
        if (check_logged_account_type(array('ADMIN', 'SUBADMIN'))) {
            $data['tariff_options'] = $this->utils_model->get_tariffs($logged_account_type, 'CUSTOMER');
        } else {
            $data['tariff_options'] = $this->utils_model->get_tariffs($logged_account_type, 'CUSTOMER', $logged_account_id);
        }

        $order_by = '';
        $limit_to = 0;
        $limit_from = 1000;
        $filter_data = Array();
        $diaplan = $this->Route_mod->get_data($order_by, $limit_to, $limit_from, $filter_data);
        $data['dialplan_options'] = $diaplan['result'];

        $data['page_name'] = $page_name;
        $data['pagination'] = $this->pagination->create_links();
        $data['listing_data'] = $response['result'][0];
        $data['total_records'] = $response['total'];
        $data['listing_count'] = $response['total'];

        $this->load->view('basic/header', $data);
        $this->load->view('services/addesignup', $data);
        $this->load->view('basic/footer', $data);
    }
	function eSignupConfig($signupkey = -1) {        
        $page_name = "esignup";
        if ($signupkey == -1)
            redirect(base_url() . 'sysconfig/signupConfig', 'location', '301');
        $signupkey = param_decrypt($signupkey);
		
		$logged_account_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
		$logged_account_level = get_logged_account_level();
        
         if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('signupkey', 'Signup Key', 'trim|required');			
			$this->form_validation->set_rules('tariff_id', 'Tariff', 'trim');
			$this->form_validation->set_rules('dialplan_id', 'Dialplan', 'trim');
			$this->form_validation->set_rules('status_id', 'Plan', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->Sysconfig_mod->signupConfig_update($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'sysconfig/eSignupConfig/' . param_encrypt($signupkey), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'sysconfig/signupConfig', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'rates', 'location', '301');
                    }
                    redirect(base_url() . 'sysconfig/eSignupConfig' . param_encrypt($signupkey), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        
        $order_by = '';
        $pagination_uri_segment = 0;
        $per_page = 1;
        $search_data = array(
            'signupkey' => $signupkey,
			'business_holder' => $logged_account_type,           
        );
		
		if(strtolower($logged_account_type) !='admin')
		{
			$search_data['business_holder_account_id'] = $logged_account_id;
			$search_data['business_holder'] = $logged_account_type.$logged_account_level;
		}
		else
		{
			$search_data['business_holder'] = $logged_account_type;
		}


        $response = $this->Sysconfig_mod->signupConfig_data($order_by, $pagination_uri_segment, $per_page, $search_data);

        $logged_account_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        if (check_logged_account_type(array('ADMIN', 'SUBADMIN'))) {
            $data['tariff_options'] = $this->utils_model->get_tariffs($logged_account_type, 'CUSTOMER');
        } else {
            $data['tariff_options'] = $this->utils_model->get_tariffs($logged_account_type, 'CUSTOMER', $logged_account_id);
        }

        $order_by = '';
        $limit_to = 0;
        $limit_from = 1000;
        $filter_data = Array();
        $diaplan = $this->Route_mod->get_data($order_by, $limit_to, $limit_from, $filter_data);
        $data['dialplan_options'] = $diaplan['result'];

        $data['page_name'] = $page_name;
        $data['pagination'] = $this->pagination->create_links();
        $data['listing_data'] = $response['result'][0];
        $data['total_records'] = $response['total'];
        $data['listing_count'] = $response['total'];

        $this->load->view('basic/header', $data);
        $this->load->view('services/esignup', $data);
        $this->load->view('basic/footer', $data);
    }

    public function addRC() {
        $data['page_name'] = "ratecard_add";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('ratecard', 'add'))
            show_404('403');

        $data['currency_data'] = $this->Utils_model->get_currencies();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_name', 'Name', 'trim|required|min_length[5]|max_length[30]');
            $this->form_validation->set_rules('frm_currency', 'Currency', 'trim|min_length[0]|max_length[10]');
            $this->form_validation->set_rules('ratecard_for', 'Ratecard For', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->ratecard_mod->add($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Ratecard Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'ratecard/editRC/' . param_encrypt($result['id']), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'ratecard', 'location', '301');
                    } else {
                        redirect(base_url() . 'ratecard', 'location', '301');
                    }
                    redirect(base_url() . 'ratecard/editRC/' . param_encrypt($ratecard_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        $this->load->view('basic/header', $data);
        $this->load->view('rates/ratecard_add', $data);
        $this->load->view('basic/footer', $data);
    }

}
