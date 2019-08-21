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
        $_POST['logged_user_type'] = get_logged_account_type();
        $_POST['logged_current_customer_id'] = get_logged_account_id();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('company_name', 'Business name / Company name', 'trim|required|min_length[5]|max_length[100]');
            $this->form_validation->set_rules('address', 'Business / Company Address', 'trim|required|min_length[10]|max_length[1000]');
            $this->form_validation->set_rules('bank_detail', 'Business / Company Bank Account Detail where want to recive Payment', 'trim|required|min_length[10]|max_length[1000]');

            $this->form_validation->set_rules('support_text', 'Customer / Billing Support Detail In invoice', 'trim|required|min_length[10]|max_length[1000]');

            $_POST['logged_user_type'] = get_logged_account_type();
            $_POST['logged_current_customer_id'] = get_logged_account_id();
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $config['upload_path'] = './upload/invoicelogo/';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size'] = 2000;
                $config['max_width'] = 1500;
                $config['max_height'] = 1500;
                $config['file_name'] = $_POST['logged_current_customer_id'];
                $config['file_ext_tolower'] = TRUE;
                $config['max_size'] = 0;
                $config['overwrite'] = TRUE;
                $this->load->library('upload', $config);
                if ($this->upload->do_upload('invoicelogo')) {
                    $uploaded_data_array = $this->upload->data();
                    $client_name = $uploaded_data_array['client_name'];
                    $_POST['logo'] = $uploaded_data_array['file_name'];
                } else {
                    $error = array('error' => $this->upload->display_errors());
                    $_POST['logo'] = '';
                }

                $result = $this->Sysconfig_mod->inConfig($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Ratecard Added Successfully');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        $logopath = 'upload/invoicelogo/';
        $_POST['logged_user_type'] = get_logged_account_type();
        $_POST['logged_current_customer_id'] = get_logged_account_id();
        $_POST['action'] = 'search';
        $result = $this->Sysconfig_mod->inConfig($_POST);

        $data['data'] = $result['result'][0];
        $data['logopath'] = $logopath;
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
                    $this->session->set_flashdata('suc_msgs', 'Ratecard Added Successfully');
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

}
