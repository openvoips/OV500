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

class Sitesetup extends MY_Controller {

    function __construct() {
        parent::__construct();
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->output->enable_profiler(ENABLE_PROFILE);
    }

    public function index() {
        $page_name = "sitesetup_index";
        $data['page_name'] = $page_name;

        if (!check_logged_user_type(array('ADMIN', 'SUBADMIN')))
            show_404('403');

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
           $this->form_validation->set_rules('action', 'Site Name', 'trim|required');

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->sitesetup_mod->update_sitesetup($_POST['data']);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Site Settings Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save') 
						redirect(base_url() . 'sitesetup', 'location', '301');
					else
						redirect(base_url() . 'dashboard', 'location', '301');
                    
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
