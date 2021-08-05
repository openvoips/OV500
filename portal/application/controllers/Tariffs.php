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

class Tariffs extends MY_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('tariff_mod');
        $this->load->model('Utils_model');
    }

    public function index($arg1 = '', $format = '') {
        $page_name = "tariff_index";
        $file_name = 'Tariff_' . date('Ymd');
        $is_file_downloaded = false;
        if (!check_account_permission('tariff', 'view')) {
            show_404('403');
        }
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('tariff', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'tariff', 'location', '301');
            }
            $param = '';
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_parameter_two']))
                $param = param_decrypt($_POST['delete_parameter_two']);
            if ($param == '') {
                if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->tariff_mod->delete($delete_param_array);
                    if ($result['status'] === true) {
                        $suc_msgs = count($delete_id_array) . ' Tariff';
                        if (count($delete_id_array) > 1)
                            $suc_msgs .= 's';
                        $suc_msgs .= ' Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                        redirect(current_url(), 'location', '301');
                    }
                    else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                        redirect(current_url(), 'location', '301');
                    }
                } else {
                    $err_msgs = 'Select row to delete';
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } else {
                if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->tariff_mod->delete_mapping($delete_param_array);

                    if ($result['status'] === true) {
                        $suc_msgs = count($delete_id_array) . ' Tariff Map';
                        if (count($delete_id_array) > 1)
                            $suc_msgs .= 's';
                        $suc_msgs .= ' Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                        redirect($_SERVER['HTTP_REFERER'], 'location', '301');
                    }
                    else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                        redirect($_SERVER['HTTP_REFERER'], 'location', '301');
                    }
                }
            }
        }
        $data['currency_data'] = $this->Utils_model->get_currencies();
        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_tariff_data'] = array(
                's_tariff_name' => $_POST['name'],
                's_tariff_currency' => $_POST['currency'],
                's_tariff_id' => $_POST['tariff_id'],
                's_tariff_status' => $_POST['status'],
                's_tariff_type' => $_POST['type'],
                's_no_of_records' => $_POST['no_of_rows']
            );
        } else {
            $r = $this->uri->segment(2);
            if (strlen($r) == 0) {
                $_SESSION['search_tariff_data']['s_tariff_name'] = isset($_SESSION['search_tariff_data']['s_tariff_name']) ? $_SESSION['search_tariff_data']['s_tariff_name'] : '';
                $_SESSION['search_tariff_data']['s_tariff_id'] = isset($_SESSION['search_tariff_data']['s_tariff_id']) ? $_SESSION['search_tariff_data']['s_tariff_id'] : '';
                $_SESSION['search_tariff_data']['s_tariff_currency'] = isset($_SESSION['search_tariff_data']['s_tariff_currency']) ? $_SESSION['search_tariff_data']['s_tariff_currency'] : '';
                $_SESSION['search_tariff_data']['s_tariff_type'] = isset($_SESSION['search_tariff_data']['s_tariff_type']) ? $_SESSION['search_tariff_data']['s_tariff_type'] : '';
                $_SESSION['search_tariff_data']['s_tariff_status'] = isset($_SESSION['search_tariff_data']['s_tariff_status']) ? $_SESSION['search_tariff_data']['s_tariff_status'] : '';
                $_SESSION['search_tariff_data']['s_no_of_records'] = isset($_SESSION['search_tariff_data']['s_no_of_records']) ? $_SESSION['search_tariff_data']['s_no_of_records'] : '';
            }
        }
        $search_data = array(
            'tariff_name' => $_SESSION['search_tariff_data']['s_tariff_name'],
            'tariff_currency_id' => $_SESSION['search_tariff_data']['s_tariff_currency'],
            'tariff_id' => $_SESSION['search_tariff_data']['s_tariff_id'],
            'tariff_status' => $_SESSION['search_tariff_data']['s_tariff_status'],
            'tariff_type' => $_SESSION['search_tariff_data']['s_tariff_type']
        );




        $search_data['account_id'] = get_logged_account_id();

        $order_by = '';
        if ($arg1 == 'export' && $format != '') {
            $this->load->library('Export');
            $format = param_decrypt($format);
            $option_param = array('tariff' => true);
            $response_data = $this->tariff_mod->get_data($order_by, '', '', $search_data, $option_param);
            $export_header = array('Name', 'Tariff Code', 'Currency', 'Description', 'Status');
            if ($response_data['total'] > 0) {
                foreach ($response_data['result'] as $row) {
                    $ex_status = $row['tariff_status'] == 1 ? 'Active' : 'Inactive';
                    $export_data[] = array($row['tariff_name'], $row['tariff_id'], $row['currency_name'], $row['tariff_description'], $ex_status);
                }
            } else {
                $export_data = array('');
            }
            $downloaded_message = $this->export->download($file_name, $format, $search_data, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string') {
                $data['err_msgs'] = $downloaded_message;
            } else {
                $is_file_downloaded = true;
            }
        }

        if ($is_file_downloaded === false) {

            $pagination_uri_segment = $this->uri->segment(3, 0);
            if (isset($_SESSION['search_tariff_data']['s_no_of_records']) && $_SESSION['search_tariff_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_tariff_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            $response = $this->tariff_mod->get_data($order_by, $pagination_uri_segment, $per_page, $search_data);
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['total'], 'tariffs/index', $per_page, 3);
            $this->pagination->initialize($config);

            $data['page_name'] = $page_name;
            $data['pagination'] = $this->pagination->create_links();
            $data['listing_data'] = $response['result'];
            $data['total_records'] = $data['listing_count'] = $response['total'];

            $this->load->view('basic/header', $data);
            $this->load->view('rates/tariffs', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function addTP() {
        $data['page_name'] = "tariff_add";

        if (!check_account_permission('tariff', 'add'))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $data['currency_data'] = $this->Utils_model->get_currencies();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_name', 'Name', 'trim|required|min_length[5]|max_length[30]');
            $this->form_validation->set_rules('frm_currency', 'Currency', 'trim|min_length[0]|max_length[10]');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('frm_desc', 'Description', 'trim|min_length[0]|max_length[50]');
            if (check_logged_user_group(array('RESELLER'))) {
                $_POST['frm_type'] = 'CUSTOMER';
            }
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->tariff_mod->add($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Tariff Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'tariffs/editTP/' . param_encrypt($result['id']), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'tariffs', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'tariffs', 'location', '301');
                    }
                    redirect(base_url() . 'tariffs/editTP/' . param_encrypt($route_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }

        $this->load->view('basic/header', $data);
        $this->load->view('rates/tariff_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editTP() {
        $data['page_name'] = "tariff_edit";
        if (!check_account_permission('tariff', 'edit'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $data['currency_data'] = $this->Utils_model->get_currencies();
        $tariff_id = param_decrypt($this->uri->segment(3));
        //print_r($_REQUEST);  die;
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_id', 'Route ID', 'trim|required');
            $this->form_validation->set_rules('frm_name', 'Name', 'trim|required|min_length[5]|max_length[30]');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('frm_desc', 'Description', 'trim|min_length[0]|max_length[50]');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->tariff_mod->update($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Tariff Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'tariffs/editTP/' . param_encrypt($tariff_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'tariffs', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'tariffs', 'location', '301');
                    }
                    redirect(base_url() . 'tariffs/editTP/' . param_encrypt($tariff_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkUpdateData') {
            
        }
        $show_404 = false;
        if (!empty($tariff_id) && strlen($tariff_id) > 0) {
            $search_data = array('tariff_id' => $tariff_id);
            $response_data = $this->tariff_mod->get_data('', 0, RECORDS_PER_PAGE, $search_data, array());
            if ($response_data['total'] > 0) {
                $data['data'] = $response_data['result'][0];
                $ratecard_response_data = $this->tariff_mod->get_mapping('', 0, RECORDS_PER_PAGE, array('tariff_id' => $response_data['result'][0]['tariff_id'], 'ratecard_for' => 'OUTGOING'), array());
                $data['data_ratecard'] = $ratecard_response_data['result'];

                $ratecard_response_data_in = $this->tariff_mod->get_mapping('', 0, RECORDS_PER_PAGE, array('tariff_id' => $response_data['result'][0]['tariff_id'], 'ratecard_for' => 'INCOMING'), array());
                $data['data_ratecard_in'] = $ratecard_response_data_in['result'];

                $this->load->model('carrier_mod');
                $carrier_response_data = $this->carrier_mod->get_data('', 0, RECORDS_PER_PAGE, array('tariff_id' => $response_data['result'][0]['tariff_id']), array());
                if (isset($carrier_response_data['result']))
                    $data['data_carrier'] = $carrier_response_data['result'];
                else
                    $data['data_carrier'] = array();
            } else {
                $show_404 = true;
            }
        } else {
            $show_404 = true;
        }
        $data['tariff_id'] = $tariff_id;
        $this->load->view('basic/header', $data);
        if ($show_404)
            $this->load->view('basic/404', $data);
        else
            $this->load->view('rates/tariff_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editTMP() {
        $data['page_name'] = "mapping_edit";


        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $mapping_id = param_decrypt($this->uri->segment(3));
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_id', 'Mapping ID', 'trim|required');
            $this->form_validation->set_rules('frm_priority', 'Priority', 'trim|required|min_length[1]|max_length[5]|is_natural_no_zero');
            $this->form_validation->set_rules('frm_start_day', 'Start Day', 'trim|required|min_length[0]|max_length[6]|is_natural');
            $this->form_validation->set_rules('frm_end_day', 'End Day', 'trim|required|min_length[0]|max_length[6]|is_natural');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->tariff_mod->editTMP($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Tariff Ratecard Mapping Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'tariffs/editTMP/' . param_encrypt($mapping_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'tariffs/editTP/' . $result['id'], 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'tariffs', 'location', '301');
                    }
                    redirect(base_url() . 'tariffs/editTP/' . $result['id'], 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }

        $show_404 = false;
        if (!empty($mapping_id) && strlen($mapping_id) > 0) {

            $search_data = array('tariff_ratecard_map_id' => $mapping_id);
            $response_data = $this->tariff_mod->get_mapping('', 0, RECORDS_PER_PAGE, $search_data, array());

            if ($response_data['total'] > 0) {
                $data['data'] = $response_data['result'][0];
                $this->load->model('ratecard_mod');
                $data['ratecard_data'] = $this->ratecard_mod->get_data('', 0, '', array(), array());
                $data['tariff_data'] = $this->tariff_mod->get_data('', '', '', array(), array());
            } else
                $show_404 = true;
        } else
            $show_404 = true;

        $data['mapping_id'] = $mapping_id;
        $this->load->view('basic/header', $data);
        if ($show_404)
            $this->load->view('basic/404', $data);
        else
            $this->load->view('rates/mapping_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function addTMP() {
        $data['page_name'] = "mapping_add";
        //$this->member_mod->check_permission('');		
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $tariff_data = param_decrypt($this->uri->segment(3));
        $tariff_data = explode('@', $tariff_data);
        $tariff_id = $tariff_data['0'];
        $ratecard_for = $tariff_data['1'];
        if (strlen($ratecard_for) > 0) {
            $_POST['ratecard_for'] = $ratecard_for;
            $ratecard_for = $_POST['ratecard_for'];
        }
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_id', 'Tariff ID', 'trim|required');
            $this->form_validation->set_rules('frm_key', 'Tariff Key', 'trim|required');
            $this->form_validation->set_rules('frm_card', 'Ratecard', 'trim|required');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('frm_start_day', 'Start Day', 'trim|required|min_length[0]|max_length[6]|is_natural');
            $this->form_validation->set_rules('frm_end_day', 'End Day', 'trim|required|min_length[0]|max_length[6]|is_natural');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->tariff_mod->addTMP($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Tariff Ratecard Mapping Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'tariffs/editTMP/' . param_encrypt($result['id']), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'tariffs/editTP/' . param_encrypt($tariff_id), 'location', '301');
                    } else {
                        redirect(base_url() . 'tariffs', 'location', '301');
                    }
                    redirect(base_url() . 'tariffs/editTP/' . param_encrypt($tariff_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }

        $show_404 = false;
        if (!empty($tariff_id) && strlen($tariff_id) > 0) {
            $search_data = array('tariff_id' => $tariff_id);
            $response_data = $this->tariff_mod->get_data('', 0, RECORDS_PER_PAGE, $search_data, array());
            if ($response_data['total'] > 0) {
                $tariff_data = $response_data['result'][0];
                $data['data'] = $tariff_data;
                $this->load->model('ratecard_mod');
                $data['ratecard_data'] = $this->ratecard_mod->get_data('', 0, '', array('ratecard_for' => $ratecard_for, 'ratecard_currency_id' => $tariff_data['tariff_currency_id'], 'ratecard_type' => $tariff_data['tariff_type'], 'account_id' => get_logged_account_id()), array());
            } else
                $show_404 = true;
        } else
            $show_404 = true;

        $data['tariff_id'] = $tariff_id;
        $this->load->view('basic/header', $data);
        if ($show_404)
            $this->load->view('basic/404', $data);
        else
            $this->load->view('rates/mapping_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function apiTM($tariff) {

        $search_data = array('tariff_id' => $tariff);
        $response = $this->tariff_mod->get_mapping('', 0, RECORDS_PER_PAGE, $search_data, array());
        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }

}
