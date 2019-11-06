<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
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

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admins extends CI_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
       // $this->output->enable_profiler(ENABLE_PROFILE);
    }

    function index($arg1 = '', $format = '') {
        $page_name = "admins_index";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('admin', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'admins', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->member_mod->delete($delete_param_array);
                if ($result === true) {
                    $suc_msgs = count($delete_id_array) . ' Users';
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
                $err_msgs = 'Select user to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }

            redirect(base_url() . 'admin', 'location', '301');
        }
        if (isset($_POST['search_action'])) {
            $_SESSION['search_account_data'] = array('s_name' => $_POST['name'], 's_username' => $_POST['username'], 's_status' => $_POST['status'], 's_account_type' => $_POST['account_type'], 's_account_id' => $_POST['account_id'], 's_no_of_records' => $_POST['no_of_rows'],);
        } else {
            $_SESSION['search_account_data']['s_name'] = isset($_SESSION['search_account_data']['s_name']) ? $_SESSION['search_account_data']['s_name'] : '';
            $_SESSION['search_account_data']['s_username'] = isset($_SESSION['search_account_data']['s_username']) ? $_SESSION['search_account_data']['s_username'] : '';
            $_SESSION['search_account_data']['s_status'] = isset($_SESSION['search_account_data']['s_status']) ? $_SESSION['search_account_data']['s_status'] : '';
            $_SESSION['search_account_data']['s_account_type'] = isset($_SESSION['search_account_data']['s_account_type']) ? $_SESSION['search_account_data']['s_account_type'] : '';
            $_SESSION['search_account_data']['s_account_id'] = isset($_SESSION['search_account_data']['s_account_id']) ? $_SESSION['search_account_data']['s_account_id'] : '';
             $_SESSION['search_account_data']['s_no_of_records'] = isset($_SESSION['search_account_data']['s_no_of_records']) ? $_SESSION['search_account_data']['s_no_of_records'] : '';
             
        }


        $search_data = array('name' => $_SESSION['search_account_data']['s_name'], 'username' => $_SESSION['search_account_data']['s_username'], 'account_status' => $_SESSION['search_account_data']['s_status'], 'account_type' => $_SESSION['search_account_data']['s_account_type'], 'account_id' => $_SESSION['search_account_data']['s_account_id']);

       
        $this->search_serialize = serialize($search_data);
        $order_by = '';

        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            $format = param_decrypt($format);
            $account_type_array = get_account_types(1);
            $account_data = $this->member_mod->get_data($order_by, $per_page, $segment, $search_data);
            $total = $this->member_mod->get_data_total_count();
            $search_array = array();
            if ($_SESSION['search_account_data']['s_account_id'] != '')
                $search_array['Account ID'] = $_SESSION['search_account_data']['s_account_id'];
            if ($_SESSION['search_account_data']['s_name'] != '')
                $search_array['Name'] = $_SESSION['search_account_data']['s_name'];
            if ($_SESSION['search_account_data']['s_username'] != '')
                $search_array['Username'] = $_SESSION['search_account_data']['s_username'];
            if ($_SESSION['search_account_data']['s_account_type'] != '')
                $search_array['User Type'] = $_SESSION['search_account_data']['s_account_type'];
            if ($_SESSION['search_account_data']['s_status'] != '')
                $search_array['Status'] = $_SESSION['search_account_data']['s_status'] == 1 ? 'Active' : 'Inactive';

            $export_header = array('Account ID', 'Name', 'Username', 'User Type', 'Status');

            if (count($account_data['result']) > 0) {
                foreach ($account_data['result'] as $account_data) {
                    if ($account_data['account_status'] == '1')
                        $status = 'Active';
                    else
                        $status = 'Inactive';

                    $account_type = $account_data['account_type'];
                    $account_type = $account_type_array[$account_type];

                    $export_data[] = array($account_data['account_id'], $account_data['name'], $account_data['username'], $account_type, $status);
                }
            } else
                $export_data = array('');

            $file_name = 'Admin users';

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);


            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        if ($is_file_downloaded === false) {

            $account_group_key = 1;
            $data['account_type_array'] = get_account_types($account_group_key);
            $pagination_uri_segment = 3;
            $per_page = RECORDS_PER_PAGE;
                      
           
                       
             if (isset($_SESSION['search_account_data']['s_no_of_records']) && $_SESSION['search_account_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_account_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;
           
            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }

            $account_data = $this->member_mod->get_data($order_by, $per_page, $segment, $search_data);
           $data['total_records'] =  $total = $this->member_mod->get_data_total_count();

            $config = array();
            $config = $this->utils_model->setup_pagination_option($total, 'admins/index', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);

            $data['pagination'] = $this->pagination->create_links();
            $data['data'] = $account_data;


            $this->load->view('basic/header', $data);
            $this->load->view('user/admins', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function addA() {
        $page_name = "addA";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_group_key = 1;
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('name', 'Name', 'trim|required');
            $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[6]');
            $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
            $this->form_validation->set_rules('account_type', 'Account Type', 'trim|required');
            $this->form_validation->set_rules('account_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('address', 'address', 'trim');
            $this->form_validation->set_rules('phone', 'phone', 'trim');
            $this->form_validation->set_rules('country_id', 'country_id', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->member_mod->add($_POST);
                if ($result === true) {
                    $account_id = $this->member_mod->account_id;
                    $this->session->set_flashdata('suc_msgs', 'User Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'admins/editA/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'admins', 'location', '301');
                    } else {
                        redirect(base_url() . 'admins', 'location', '301');
                    }

                    redirect(base_url() . 'admins/addA', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $data['country_options'] = $this->utils_model->get_countries();
        $data['account_type_array'] = get_account_types($account_group_key);                
        $this->load->view('basic/header', $data);
        $this->load->view('user/addA', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editA() {
        $page_name = "editA";
        $data['page_name'] = $page_name;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $this->form_validation->set_rules('name', 'Name', 'trim|required');
            $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim|required');
            $this->form_validation->set_rules('account_type', 'Account Type', 'trim|required');
            $this->form_validation->set_rules('account_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('address', 'address', 'trim');
            $this->form_validation->set_rules('phone', 'phone', 'trim');
            if (trim($_POST['secret']) != '') {
                $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
            }

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {

                $result = $this->member_mod->update($_POST);

                if ($result === true) {

                    if ($this->member_mod->account_access_id != '' && $this->member_mod->account_access_id == $this->session->userdata('session_current_customer_id')) {
                        $session_current_customer_id = $this->session->userdata('session_current_customer_id');
                        $_SESSION['users'][$session_current_customer_id]['session_fullname'] = $_POST['name'];
                    }

                    $this->session->set_flashdata('suc_msgs', 'User Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'admins/editA/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'admins', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'admins', 'location', '301');
                    }

                    redirect(base_url() . 'admins/editA/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $users_id_p = $this->uri->segment(3);
        $account_group_key = 1;
        if (strlen($users_id_p) > 0) {
            $account_id = param_decrypt($users_id_p);
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $account_data_temp = $this->member_mod->get_data($order_by, $per_page, $segment, $search_data);
            if (isset($account_data_temp['result']))
                $account_data = current($account_data_temp['result']);
            else {
                show_404();
            }
        } elseif (isset($_POST['OkSaveData'])) {
            $account_data = array(
            );

            $account_data = arrayToObject($account_data);
        } else {
            show_404();
        }
        $data['data'] = $account_data;
        $data['account_id'] = $users_id_p;

        $data['country_options'] = $this->utils_model->get_countries();
        $data['account_type_array'] = get_account_types($account_group_key);       
        $this->load->view('basic/header', $data);
        $this->load->view('user/editA', $data);
        $this->load->view('basic/footer', $data);
    }

    public function profile() {
        
      
        $page_name = "account_profile";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_id = get_logged_account_id();
        $this->load->model('tariff_mod');
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {//print_r($_POST);// die;
            //	$this->form_validation->set_rules('name', 'Name', 'trim|required');	
            //	$this->form_validation->set_rules('emailaddress', 'Email Address', 'trim|required');//callback_users_email_check			

            /* non mandatory fields */
            //	$this->form_validation->set_rules('address', 'address', 'trim');
            //	$this->form_validation->set_rules('phone', 'phone', 'trim');
            //	$this->form_validation->set_rules('country_id', 'Country_id', 'trim');


            if (trim($_POST['secret']) != '') {
                $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
            }

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['account_id'] = $account_id;
                $result = $this->member_mod->update_profile($_POST);
                if ($result === true) {

                    $session_current_customer_id = $this->session->userdata('session_current_customer_id');
                    $_SESSION['users'][$session_current_customer_id]['session_fullname'] = $_POST['name'];

                    $this->session->set_flashdata('suc_msgs', 'Password Updated Successfully');
                    redirect(base_url() . 'profile', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }//if
        }
        ///////////////////////////		
        if (check_logged_account_type(array('CUSTOMER'))) {
            if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {//print_r($_POST);// die;
                //	$this->form_validation->set_rules('name', 'Name', 'trim|required');	
                //	$this->form_validation->set_rules('emailaddress', 'Email Address', 'trim|required');//callback_users_email_check			

                /* non mandatory fields */
                //	$this->form_validation->set_rules('address', 'address', 'trim');
                //	$this->form_validation->set_rules('phone', 'phone', 'trim');
                //	$this->form_validation->set_rules('country_id', 'Country_id', 'trim');


                if (trim($_POST['secret']) != '') {
                    $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
                }

                if ($this->form_validation->run() == FALSE) {// error
                    $data['err_msgs'] = validation_errors();
                } else {
                    $_POST['account_id'] = $account_id;
                    $result = $this->member_mod->update_profile($_POST);
                    if ($result === true) {

                        $session_current_customer_id = $this->session->userdata('session_current_customer_id');
                        $_SESSION['users'][$session_current_customer_id]['session_fullname'] = $_POST['name'];

                        $this->session->set_flashdata('suc_msgs', 'Password Updated Successfully');
                        redirect(base_url() . 'profile', 'location', '301');
                    } else {
                        $err_msgs = $result;
                        $data['err_msgs'] = $err_msgs;
                    }
                }
            }
            
            $option_param = array('ip' => true, 'callerid' => true, 'sipuser' => true, 'tariff' => false, 'user' => false, 'prefix' => true, 'dialplan' => true, 'translation_rules' => true, 'callerid_incoming' => true, 'translation_rules_incoming' => true, 'notification' => true, 'service' => true);
            $view = 'account_profile';
            $data['notification_options'] = $this->utils_model->get_rule_options('notification');
        } else {
            if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {//print_r($_POST);// die;
                $this->form_validation->set_rules('name', 'Name', 'trim|required');
                $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim|required'); //callback_users_email_check			

                /* non mandatory fields */
                $this->form_validation->set_rules('address', 'address', 'trim');
                $this->form_validation->set_rules('phone', 'phone', 'trim');
                $this->form_validation->set_rules('country_id', 'Country_id', 'trim');


                if (trim($_POST['secret']) != '') {
                    $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
                }

                if ($this->form_validation->run() == FALSE) {// error
                    $data['err_msgs'] = validation_errors();
                } else {
                    $_POST['account_id'] = $account_id;
                    $result = $this->member_mod->update_profile($_POST);
                    if ($result === true) {

                        $session_current_customer_id = $this->session->userdata('session_current_customer_id');
                        $_SESSION['users'][$session_current_customer_id]['session_fullname'] = $_POST['name'];

                        $this->session->set_flashdata('suc_msgs', 'Profile Updated Successfully');
                        redirect(base_url() . 'profile', 'location', '301');
                    } else {
                        $err_msgs = $result;
                        $data['err_msgs'] = $err_msgs;
                    }
                }//if
            }

            $option_param = array();
            $view = 'profile';
        }



        $result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);


        /*         * **** pagination code ends  here ********* */
        $data['data'] = $result;

        $data['tariff_name'] = $this->tariff_mod->get_tariff_name($result['tariff_id_name']);

        $data['currency_options'] = $this->utils_model->get_currencies();
        $data['country_options'] = $this->utils_model->get_countries();
        $data['state_options'] = $this->utils_model->get_states();

        $this->load->view('basic/header', $data);
        $this->load->view('basic/' . $view, $data);
        $this->load->view('basic/footer', $data);
    }

    public function switch_user($id) /**/ {
        $account_id = param_decrypt($id);
        $userdata = array('session_current_customer_id' => $account_id);
        $this->session->set_userdata($userdata);
        $this->session->set_flashdata('suc_msgs', 'You are switched to ' . get_account_full_name());
        redirect('dashboard', 'refresh');
    }

    public function unswitch_user($id) /**/ {
        $account_id = param_decrypt($id);
        $userdata = array('session_current_customer_id' => $account_id);
        $this->session->set_flashdata('suc_msgs', 'User logged out successfully');
        unset($_SESSION['customer'][$account_id]);

        redirect('dashboard', 'refresh');
    }

    public function autologin($id, $login_as = 'self') {
        $redirect_to = 'dashboard';
        $refer_url = $_SERVER['HTTP_REFERER'];
        $this->load->model('login_mod');
        $page_name = "account_autologin";
        $data['page_name'] = $page_name;
        if (!check_logged_account_type(array('ADMIN', 'SUBADMIN', 'RESELLER'))) {
            show_404('403');
        }
        $account_id = param_decrypt($id);
        $result = $this->member_mod->get_account_by_key('account_id', $account_id);

        $child_account_type = $result['account_type'];  
        if ($result === false)
            show_404();
        if ($login_as == 'self') {
            $username = $result['username'];
            $password = $result['secret'];
            $row = $this->login_mod->get_user($username, $password);
        } else {
            if ($result['parent_account_id'] == '') {
                $err_msgs = '<p>Parent Not Exists.</p>';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect($refer_url, 'refresh');
            }
            $parent_result = $this->member_mod->get_account_by_key('account_id', $result['parent_account_id']);
            if ($parent_result === false) {
                $err_msgs = '<p>Parent Account Not Found.</p>';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect($refer_url, 'refresh');
            }

            $username = $parent_result['username'];
            $password = $parent_result['secret'];
            $row = $this->login_mod->get_user($username, $password);


            $child_account_type = $result['account_type'];
        }

        if (!$row) {
            $err_msgs = '<p>User not found.</p>';
            $this->session->set_flashdata('err_msgs', $err_msgs);
            redirect($refer_url, 'refresh');
        } elseif ($row->account_status == 0) {
            $err_msgs = '<p>Account is not Active.</p>';
            $this->session->set_flashdata('err_msgs', $err_msgs);

            redirect($refer_url, 'refresh');
        } else {
            // Add the users id to the session.
            $userdata = array();
            $userdata = array('session_current_customer_id' => $row->account_id);
            $userdata_details = array(
                'session_logged_in' => true,
                'session_account_id' => $row->account_id,
                'session_fullname' => $row->name,
                'session_account_type' => $row->account_type,
                'session_currency_id' => $row->currency_id,
                'session_account_level' => $row->account_level,
                'session_account_status' => $row->account_status,
                'session_permissions' => $row->permissions,
              
            );
            $this->session->set_userdata($userdata);
            $_SESSION['customer'][$row->account_id] = $userdata_details;

            $this->session->set_flashdata('suc_msgs', 'You are switched to ' . get_account_full_name());
            echo $child_account_type;


            if (isset($child_account_type)) {
                if (in_array($child_account_type, array('CUSTOMER'))) {
                    $redirect_to = 'dashboard';
                    unset($_SESSION['search_customers_data']);
                    $_SESSION['search_customers_data']['s_account_id'] = $result['account_id'];
                } elseif (in_array($child_account_type, array('RESELLER'))) {
                   // $redirect_to = 'resellers';
                    $redirect_to = 'dashboard';
                    unset($_SESSION['search_resellers_data']);
                    $_SESSION['search_resellers_data']['s_account_id'] = $result['account_id'];
                } 
            }
        }
//echo '<pre>';print_r($userdata_details);print_r($result);die;
        redirect($redirect_to, 'refresh'); //redirected	
    }

    public function users_email_check($email) {
        return true;
        /* $users_data = $this->member_mod->get_data_single('email_id', $email);		
          if($users_data==NULL)
          {
          return true;
          }
          else
          {
          $this->form_validation->set_message('users_email_check', 'The {field} already exists');
          return false;
          } */
    }

    function usersearch($id = '') {//edit permission for user
        $page_name = "users_usersearch";
        $data['page_name'] = $page_name;

        if (!check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC')))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSearchData') {
            if (isset($_POST['search_account_id'])) {
                $_SESSION['search_account_search']['s_account_id'] = $_POST['search_account_id'];
                $id = param_encrypt($_POST['search_account_id']);
            }
        }

        ///////////////////////////		
        $is_id_exists = false;
        $is_account_data_exists = $is_account_parent_data_exists = false;
        if (!empty($id)) {
            $is_id_exists = true;
            $account_id = param_decrypt($id);

            /* fetch user details and permissions */
            $option_param = array('user' => true);
            $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
            //echo '<pre>';print_r( $account_result);echo '</pre>';
            if ($account_result === false) {
                $data['err_msgs'] = 'User Not Found';
            } else {
                $data['account_result'] = $account_result;
                $is_account_data_exists = true;
                $_SESSION['search_account_search']['s_account_id'] = $account_id;


                if ($account_result['parent_account_id'] != '') {
                    $parent_result = $this->member_mod->get_account_by_key('account_id', $account_result['parent_account_id']);
                    if ($parent_result === false) {
                        $data['err_msgs'] = 'Parent Account Not Found';
                    } else {
                        $data['parent_result'] = $parent_result;
                        $is_account_parent_data_exists = true;
                    }
                }
            }
        }

        $data['is_id_exists'] = $is_id_exists;

        $this->load->view('basic/header', $data);
        $this->load->view('user/account_search', $data);
        $this->load->view('basic/footer', $data);
    }

    function paypal_client_pay_confirm($account_id) {
        $this->load->model('payment_mod', 'payment_mod');
        $post_payment_data = trim($_POST['data']);
        $post_payment_data_array = json_decode($post_payment_data, true);

        if ($post_payment_data_array['state'] == 'approved')
            $this->session->set_flashdata('suc_msgs', 'Payment is successfully processed');
        else
            $this->session->set_flashdata('err_msgs', 'Payment is not successfull');

        $account_type = get_logged_account_type();
        $this->payment_mod->payment_confirm($account_id, $account_type, 'paypal', $post_payment_data);
    }

}
