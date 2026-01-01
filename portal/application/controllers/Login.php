<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('sitesetup_mod');
        $this->load->model('login_mod');
    }

    public function index() {
        $page_code = 'login';
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (check_is_loggedin()) {
            redirect(base_url('dashboard'), 'location', '301');
        }

        if (isset($_POST['action']) && $_POST['action'] == 'login') {
            $this->form_validation->set_rules('login', 'Username', 'trim|required');
            $this->form_validation->set_rules('pass', 'Password', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $username = $this->input->post('login');
                $password = $this->input->post('pass');
                $row = $this->login_mod->get_user($username, $password);
                if (!$row) {
                    $data['err_msgs'] = '<p>Invalid Username or Password.</p>';
                } elseif ($row['user_status'] == 0) {
                    $data['err_msgs'] = '<p>User is not Active.</p>';
                } elseif ($row['user_status'] == -1) {
                    $data['err_msgs'] = '<p>User is waiting for Approval.</p>';
                } elseif ($row['user_status'] == -3) {
                    $data['err_msgs'] = '<p>User is Blocked.</p>';
                } elseif ($row['account_status'] != '' && $row['account_status'] != 1) {
                    if ($row['account_status'] == 0) {
                        $data['err_msgs'] = '<p>Account is Closed.</p>';
                    } elseif ($row['account_status'] == -1) {
                        $data['err_msgs'] = '<p>Account is waiting for Approval.</p>';
                    } elseif ($row['account_status'] == -3) {
                        $data['err_msgs'] = '<p>Account is Blocked.</p>';
                    }
                } else {
                    $user_id = $row['user_id'];
                    $userdata = array('session_current_user_id' => $user_id);

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
                        'session_timezone' => isset($row['timezone']) ? $row['timezone'] : '',
                    );
                    if($row['user_type']=='EXTENSION')
                    {
                        $userdata_details['session_extension_id']=$row['extension_id'];
                    }
                    //ddd($row);
                    //ddd($userdata_details);die;
                    $this->session->set_userdata($userdata);
                    $_SESSION['customer'][$user_id] = $userdata_details;

                    redirect(base_url() . 'dashboard', 'refresh');
                }
            }
        }


        $view_file = 'login';
        //$view_file='login1';
        $this->load->view($view_file, $data);
    }

}
