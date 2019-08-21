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

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sitesetup extends CI_Controller {

    function __construct() {
        parent::__construct();
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->output->enable_profiler(ENABLE_PROFILE);
    }

    public function index() {
        $page_name = "sitesetup_index";
        $data['page_name'] = $page_name;

        if (!check_logged_account_type(array('ADMIN', 'SUBADMIN')))
            show_404('403');

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('data[site_name]', 'Site Name', 'trim|required');
            $this->form_validation->set_rules('data[mail_sent_from]', 'Mail Sent From', 'trim');
            $this->form_validation->set_rules('data[mail_sent_to]', 'Mail Sent To', 'trim');

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->sitesetup_mod->update_sitesetup($_POST['data']);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Site Settings Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'sitesetup', 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'dashboard', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'dashboard', 'location', '301'); 	
                    }

                    redirect(base_url() . 'sitesetup', 'location', '301'); 					
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        //log_message('error', 'log message.');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $data['data'] = $data['sitesetup_data'];


        $this->load->view('basic/header', $data);
        $this->load->view('sitesetup', $data);
        $this->load->view('basic/footer', $data);
    }

}
