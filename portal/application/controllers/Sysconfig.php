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


class Sysconfig extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('pagination');
        $this->load->model('Utils_model');
        $this->load->model('Sysconfig_mod');
        $this->load->model('Route_mod');

        $this->logged_user_type = get_logged_user_type();
        $this->logged_user_id = get_logged_user_id();
        $this->logged_account_id = get_logged_account_id();
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
