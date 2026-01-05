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

class Users extends MY_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->output->enable_profiler(ENABLE_PROFILE);

        $this->logged_user_type = get_logged_user_type();
        $this->logged_user_id = get_logged_user_id();
        $this->logged_account_id = get_logged_account_id();
    }

    function index($arg1 = '', $format = '') {
        $page_name = "users_index";
        $search_session_key = 'search_' . $page_name;
        if (!check_account_permission('user', 'view'))
            show_404('403');

        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('user', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'users', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->member_mod->delete($delete_param_array);
                if ($result === true) {
                    $suc_msgs = 'User Deleted Successfully';
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

            redirect(base_url('users'), 'location', '301');
        }

        $search_parameters = array('name', 'username', 'status', 'user_type', 'user_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        $search_data = array(
            'name' => $_SESSION[$search_session_key]['name'],
            'username' => $_SESSION[$search_session_key]['username'],
            'status_id' => $_SESSION[$search_session_key]['status'],
            'user_type' => $_SESSION[$search_session_key]['user_type'],
            'user_id' => $_SESSION[$search_session_key]['user_id'],
        );

        $data['user_type_array'] = get_user_types($this->logged_user_type);
        $search_data['user_type_group'] = array_keys($data['user_type_array']);

        if (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
            $search_data['account_id'] = $this->logged_account_id;
        }

        $order_by = '';

        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            $format = param_decrypt($format);
            $account_data = $this->member_mod->get_data($order_by, $per_page, $segment, $search_data);
            $total = $this->member_mod->get_data_total_count();
            $search_array = array();
            if ($_SESSION[$search_session_key]['user_id'] != '')
                $search_array['User ID'] = $_SESSION[$search_session_key]['user_id'];
            if ($_SESSION[$search_session_key]['name'] != '')
                $search_array['Name'] = $_SESSION[$search_session_key]['name'];
            if ($_SESSION[$search_session_key]['username'] != '')
                $search_array['Username'] = $_SESSION[$search_session_key]['username'];
            if ($_SESSION[$search_session_key]['user_type'] != '')
                $search_array['User Type'] = $_SESSION[$search_session_key]['user_type'];
            if ($_SESSION[$search_session_key]['status'] != '')
                $search_array['Status'] = $_SESSION[$search_session_key]['status'] == 1 ? 'Active' : 'Inactive';

            $export_header = array('User ID', 'Name', 'Username', 'User Type', 'Status');

            if (count($account_data['result']) > 0) {
                foreach ($account_data['result'] as $account_data) {
                    if ($account_data['account_status'] == '1')
                        $status = 'Active';
                    else
                        $status = 'Inactive';

                    $account_type = $account_data['user_type'];
                    $account_type = $user_type_array[$account_type];

                    $export_data[] = array($account_data['user_id'], $account_data['name'], $account_data['username'], $account_type, $status);
                }
            } else
                $export_data = array('');

            $file_name = 'System users';

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);

            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        if ($is_file_downloaded === false) {

            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $account_data = $this->member_mod->get_data($order_by, $per_page, $segment, $search_data);
            $total_count = $this->utils_model->get_data_total_count($this->member_mod->select_sql);
            $data['pagination'] = setup_pagination_option($total_count, 'users/index', $per_page, $pagination_uri_segment, $this->pagination);

            $data['data'] = $account_data;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('user/users', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function addA() {
        $page_name = "users_add";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (!check_account_permission('user', 'add'))
            show_404('403');

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('user_fullname', 'Name', 'trim|required');
            $this->form_validation->set_rules('user_emailaddress', 'Email Address', 'trim|required');
            $this->form_validation->set_rules('user_type', 'User Type', 'trim|required');
            $this->form_validation->set_rules('status_id', 'Status', 'trim|required');
            $this->form_validation->set_rules('user_address', 'address', 'trim');
            $this->form_validation->set_rules('user_phone', 'phone', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['account_id'] = $this->logged_account_id;
                $result = $this->member_mod->add($_POST);
                if ($result === true) {
                    $user_id_name = $this->member_mod->user_id_name;
                    $this->session->set_flashdata('suc_msgs', 'User Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save')
                        redirect(base_url() . 'users/editA/' . param_encrypt($user_id_name), 'location', '301');
                    else
                        redirect(base_url() . 'users', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $data['country_options'] = $this->utils_model->get_countries();
        $data['user_type_array'] = get_user_types($this->logged_user_type);

        $this->load->view('basic/header', $data);
        $this->load->view('user/addA', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editA($id = -1) {
        $page_name = "users_edit";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('user', 'edit'))
            show_404('403');

        if ($id == -1)
            show_404('403');
        $users_id_p = $id;
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $this->form_validation->set_rules('user_fullname', 'Name', 'trim|required');
            $this->form_validation->set_rules('user_emailaddress', 'Email Address', 'trim|required');
            $this->form_validation->set_rules('user_type', 'User Type', 'trim|required');
            $this->form_validation->set_rules('status_id', 'Status', 'trim|required');
            $this->form_validation->set_rules('user_address', 'address', 'trim');
            $this->form_validation->set_rules('user_phone', 'phone', 'trim');
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
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save')
                        redirect(base_url() . 'users/editA/' . $id, 'location', '301');
                    else
                        redirect(base_url() . 'users', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }


        $user_id_name = param_decrypt($users_id_p);
        $search_data = array('user_id' => $user_id_name);
        $account_data_temp = $this->member_mod->get_data('', 1, 0, $search_data);
        if (isset($account_data_temp['result']))
            $account_data = current($account_data_temp['result']);
        else {
            show_404();
        }

        $data['data'] = $account_data;
        $data['account_id'] = $users_id_p;

        $data['country_options'] = $this->utils_model->get_countries();
        $data['user_type_array'] = get_user_types($this->logged_user_type);

        $this->load->view('basic/header', $data);
        $this->load->view('user/editA', $data);
        $this->load->view('basic/footer', $data);
    }

    public function profile() {
        $page_name = "account_profile";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $user_id_name = get_logged_user_id();
        {
            if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
                $this->form_validation->set_rules('user_fullname', 'Name', 'trim|required');
                $this->form_validation->set_rules('user_emailaddress', 'Email Address', 'trim|required');
                /* non mandatory fields */
                $this->form_validation->set_rules('user_address', 'Address', 'trim');
                $this->form_validation->set_rules('user_phone', 'Phone', 'trim');
                $this->form_validation->set_rules('user_country_id', 'Country', 'trim');

                if (trim($_POST['secret']) != '') {
                    $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
                }

                if ($this->form_validation->run() == FALSE) {
                    $data['err_msgs'] = validation_errors();
                } else {
                    $_POST['user_id_name'] = $user_id_name;
                    $result = $this->member_mod->update($_POST);
                    if ($result === true) {

                        //   $session_current_customer_id = $this->session->userdata('session_current_customer_id');
                        $session_current_customer_id = $this->session->userdata('session_current_user_id');
                        $_SESSION['customer'][$session_current_customer_id]['session_fullname'] = $_POST['name'];

                        $this->session->set_flashdata('suc_msgs', 'Profile Updated Successfully');
                        redirect(base_url() . 'profile', 'location', '301');
                    } else {
                        $err_msgs = $result;
                        $data['err_msgs'] = $err_msgs;
                    }
                }
            }
        }

        $search_data = array('user_id' => $user_id_name);
        $result = $this->member_mod->get_data('', '', '', $search_data);

        $data['data'] = current($result['result']);

        $data['currency_options'] = $this->utils_model->get_currencies();
        $data['country_options'] = $this->utils_model->get_countries();
        

        if (check_logged_user_group(array('CUSTOMER'))) {
            $this->load->model('customer_mod');
            $logged_account_id = get_logged_account_id();
            $search_data = array('account_id' => $logged_account_id);
            $customers_data_temp = $this->customer_mod->get_data('', 1, 0, $search_data, []);
            $data['customers_data'] = current($customers_data_temp['result']);
            //print_r($data['customers_data'] );
        }


        /////

        $this->load->view('basic/header', $data);
        $this->load->view('basic/profile', $data);
        $this->load->view('basic/footer', $data);
    }

    public function switch_user($id) {
        $account_id = param_decrypt($id);
        $userdata = array('session_current_user_id' => $account_id);
        $this->session->set_userdata($userdata);
        $this->session->set_flashdata('suc_msgs', 'You are switched to ' . get_logged_user_name());
        redirect('dashboard', 'refresh');
    }

    public function unswitch_user($id) {
        $account_id = param_decrypt($id);
        $userdata = array('session_current_user_id' => $account_id);
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

        $account_id = param_decrypt($id);
        $result = $this->member_mod->get_user_by_key('user_id', $account_id);

        $child_account_type = $result['user_type'];
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
        } elseif ($row['user_status'] == 0) {
            $err_msgs = '<p>User is not Active.</p>';
            $this->session->set_flashdata('err_msgs', $err_msgs);

            redirect($refer_url, 'refresh');
        } else {

            $userdata = array();
            $userdata = array('session_current_user_id' => $row['user_id']);

            $userdata_details = array(
                'session_logged_in' => true,
                'session_user_id' => $row['user_id'],
                'session_user_type' => $row['user_type'],
                'session_user_name' => $row['name'],
                'session_account_id' => $row['account_id'],
                'session_account_name' => $row['account_name'],
                'session_account_type' => $row['account_type'],
                'session_account_level' => $row['account_level'],
                'session_account_status' => $row['account_status'],
                'session_currency_id' => $row['currency_id'],
                'session_permissions' => $row['permissions'],
            );
            $user_id = $row['user_id'];

            $this->session->set_userdata($userdata);
            $_SESSION['customer'][$user_id] = $userdata_details;

            $this->session->set_flashdata('suc_msgs', 'You are switched to ' . get_logged_user_name());

            if (isset($child_account_type)) {
                if (in_array($child_account_type, array('CUSTOMER'))) {
                    $redirect_to = 'dashboard';
                    unset($_SESSION['search_customers_data']);
                } elseif (in_array($child_account_type, array('RESELLER'))) {

                    $redirect_to = 'dashboard';
                    unset($_SESSION['search_resellers_data']);
                }
            }
        }

        redirect($redirect_to, 'refresh');
    }

    public function reautologin($id, $login_as = 'self') {
        $redirect_to = 'dashboard';
        $refer_url = $_SERVER['HTTP_REFERER'];
        $this->load->model('login_mod');
        $page_name = "account_autologin";
        $data['page_name'] = $page_name;
        if (!check_account_permission('reseller', 'login')) {
            show_404('403');
        }
        $account_id = param_decrypt($id);

        $user_search_data['account_id'] = $account_id;
        $user_search_data['user_type'] = 'RESELLERADMIN';

        $users_data_temp = $this->member_mod->get_data('', 1, 0, $user_search_data);

        if (isset($users_data_temp['result']))
            $users_data = current($users_data_temp['result']);
        else {
            $err_msgs = '<p>User not found.</p>';
            $this->session->setFlashdata('err_msgs', $err_msgs);
            return redirect()->to($refer_url);
        }

        $this->autologin(param_encrypt($users_data['user_id']));
    }

    public function cuautologin($id, $login_as = 'self') {
        $redirect_to = 'dashboard';
        $refer_url = $_SERVER['HTTP_REFERER'];
        $this->load->model('login_mod');
        $page_name = "account_autologin";
        $data['page_name'] = $page_name;
        if (!check_account_permission('reseller', 'login')) {
            show_404('403');
        }
        $account_id = param_decrypt($id);

        $user_search_data['account_id'] = $account_id;
        $user_search_data['user_type'] = 'CUSTOMERADMIN';

        $users_data_temp = $this->member_mod->get_data('', 1, 0, $user_search_data);

        if (isset($users_data_temp['result']))
            $users_data = current($users_data_temp['result']);
        else {
            $err_msgs = '<p>User not found.</p>';
            $this->session->setFlashdata('err_msgs', $err_msgs);
            return redirect()->to($refer_url);
        }

        $this->autologin(param_encrypt($users_data['user_id']));
    }

    public function users_email_check($email) {
        return true;
    }

    function usersearch($id = '') {
        $page_name = "users_usersearch";
        $data['page_name'] = $page_name;

        if (!check_logged_user_type(array('ADMIN', 'SUBADMIN')))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSearchData') {
            if (isset($_POST['search_account_id'])) {
                $_SESSION['search_account_search']['s_account_id'] = $_POST['search_account_id'];
                $id = param_encrypt($_POST['search_account_id']);
            }
        }

        $is_id_exists = false;
        $is_account_data_exists = $is_account_parent_data_exists = false;
        if (!empty($id)) {
            $is_id_exists = true;
            $account_id = param_decrypt($id);

            $option_param = array('user' => true);
            $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);

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
