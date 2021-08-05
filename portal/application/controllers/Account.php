<?php

// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
// OV500 Version 2.0.0
// Copyright (C) 2019-2021 Openvoips Technologies   
// http://www.openvoips.com  http://www.openvoips.org
// 
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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

class Account extends MY_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        // $this->output->enable_profiler(ENABLE_PROFILE);
    }

    public function index() {
        $this->account();
    }

    public function account() /* user profile */ {
        $page_name = "account_profile";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_id = get_logged_account_id();
        $this->load->model('tariff_mod');
        if (!check_logged_user_group(array('reseller', 'customer'))) {
            show_404('403');
        }
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
                $_POST['account_access_id_name'] = $account_id;
                $result = $this->member_mod->update_profile($_POST);
                if ($result === true) {

                    $session_current_account_id = $this->session->userdata('session_current_account_id');
                    $_SESSION['users'][$session_current_account_id]['session_fullname'] = $_POST['name'];

                    $this->session->set_flashdata('suc_msgs', 'Password Updated Successfully');
                    redirect(base_url() . 'profile', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }//if
        }
        ///////////////////////////		



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
                $_POST['account_access_id_name'] = $account_id;
                $result = $this->member_mod->update_profile($_POST);
                if ($result === true) {

                    $session_current_account_id = $this->session->userdata('session_current_account_id');
                    $_SESSION['users'][$session_current_account_id]['session_fullname'] = $_POST['name'];

                    $this->session->set_flashdata('suc_msgs', 'Password Updated Successfully');
                    redirect(base_url() . 'profile', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }//if
        }


        $option_param = array('ip' => true, 'callerid' => true, 'sipuser' => true, 'tariff' => false, 'user' => false, 'prefix' => true, 'dialplan' => true, 'translation_rules' => true, 'callerid_incoming' => true, 'translation_rules_incoming' => true, 'notification' => true, 'service' => true);

        $data['notification_options'] = $this->utils_model->get_rule_options('notification');




        $result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);


        /*         * **** pagination code ends  here ********* */
        $data['data'] = $result;

        $data['tariff_name'] = $this->tariff_mod->get_tariff_name($result['tariff_id']);

        $data['currency_options'] = $this->utils_model->get_currencies();
        $data['country_options'] = $this->utils_model->get_countries();
        $data['state_options'] = $this->utils_model->get_states();


        $this->load->view('basic/header', $data);
        $this->load->view('basic/account_profile', $data);
        $this->load->view('basic/footer', $data);
    }

}
