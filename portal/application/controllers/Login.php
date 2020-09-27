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
//OV500 Version 1.0.3
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

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('sitesetup_mod');
        $this->load->model('login_mod');
    }

    public function index() {
        $page_code = 'login';
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();     
        $session_account_id = $this->session->userdata('session_account_id');
        if ($session_account_id != '') {
            redirect('dashboard', 'refresh');
        }
        if (isset($_POST['action']) && $_POST['action'] == 'login') {            
            $this->form_validation->set_rules('login', 'Username', 'trim|required');
            $this->form_validation->set_rules('pass', 'Password', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $username = $this->input->post('login');
                $password = $this->input->post('pass');
                $result = $this->login_mod->get_user($username, $password);
                if (!$result) {
                    $data['err_msgs'] = '<p>Invalid Username or Password.</p>';
                } elseif ($result->account_status == 0) {
                    $data['err_msgs'] = '<p>Your Account is Closed.</p>';
                } elseif ($result->account_status == 2) {
                    $data['err_msgs'] = '<p>Your Account is Suspended.</p>';
                } elseif ($result->account_status == 3) {
                    $data['err_msgs'] = '<p>Your Account is Blocked.</p>';
                } else {
                    $userdata = array('session_current_customer_id' => $result->account_id);
                    $userdata_details = array(
                        'session_logged_in' => true,
                        'session_customer_id' => $result->customer_id,
                        'session_account_id' => $result->account_id,
                        'session_fullname' => $result->name,
                        'session_account_type' => $result->account_type,
                        'session_currency_id' => $result->currency_id,
                        'session_account_level' => $result->account_level,
                        'session_account_status' => $result->account_status,
                        'session_permissions' => $result->permissions,
                        'session_username' => $result->username,
                    );

                    $this->session->set_userdata($userdata);
                    $_SESSION['customer'][$result->account_id] = $userdata_details;
                    redirect(base_url() . 'dashboard', 'refresh');
                }
            }
        }

        if (!check_is_loggedin()) {
            $this->load->view('login', $data);
        } else {
            redirect(base_url('dashboard'), 'location', '301');
        }
    }

}
