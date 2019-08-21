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

class Dialplans extends CI_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('dialplan_mod');
        $this->load->model('route_mod');
        $this->load->model('carrier_mod');
    }

    public function index($arg1 = '', $format = '') {
        $page_name = "dialplan_index";
        $file_name = 'Dialplan_' . date('Ymd');
        $is_file_downloaded = false;
        $searching = true;
        if (!check_account_permission('dialplan', 'view'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('dialplan', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'dialplans', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->dialplan_mod->delete($delete_param_array);
                if ($result['status'] === true) {
                    $suc_msgs = count($delete_id_array) . ' Dialplan';
                    if (count($delete_id_array) > 1)
                        $suc_msgs .= 's';
                    $suc_msgs .= ' Deleted Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    redirect(current_url() . '/index/', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } else {
                $err_msgs = 'Select row to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
            redirect(base_url() . 'dialplans', 'location', '301');
        }
        $route_data = $this->route_mod->get_data('dialplan_name', 0, 10000, array(), array());
        $data['route_data'] = $route_data['result'];
        $carrier_data = $this->carrier_mod->get_data('', 0, 20000, array(), array());
        $data['carrier_data'] = $carrier_data['result'];
        $search_data = array();
        if (isset($_POST['search_action']))
            $_SESSION['search_dialplan_data'] = array(
                's_prefix' => $_POST['prefix'],
                's_route_abbr' => $_POST['abbr'],
                's_status' => $_POST['status'],
                's_carrier' => $_POST['carrier'],
                's_no_of_records' => $_POST['no_of_rows'],
            );
        else {
            $r = $this->uri->segment(2);
            if ($r == '') {
                $searching = false;
                $_SESSION['search_dialplan_data']['s_prefix'] = isset($_SESSION['search_dialplan_data']['s_prefix']) ? $_SESSION['search_dialplan_data']['s_prefix'] : '';
                $_SESSION['search_dialplan_data']['s_route_abbr'] = isset($_SESSION['search_dialplan_data']['s_route_abbr']) ? $_SESSION['search_dialplan_data']['s_route_abbr'] : '';
                $_SESSION['search_dialplan_data']['s_status'] = isset($_SESSION['search_dialplan_data']['s_status']) ? $_SESSION['search_dialplan_data']['s_status'] : '';
                $_SESSION['search_dialplan_data']['s_carrier'] = isset($_SESSION['search_dialplan_data']['s_carrier']) ? $_SESSION['search_dialplan_data']['s_carrier'] : '';
                $_SESSION['search_dialplan_data']['s_no_of_records'] = isset($_SESSION['search_dialplan_data']['s_no_of_records']) ? $_SESSION['search_dialplan_data']['s_no_of_records'] : '';
            }
        }

        $search_data = array(
            'dial_prefix' => $_SESSION['search_dialplan_data']['s_prefix'],
            'dialplan_id' => $_SESSION['search_dialplan_data']['s_route_abbr'],
            'route_status' => $_SESSION['search_dialplan_data']['s_status'],
            'carrier_id' => $_SESSION['search_dialplan_data']['s_carrier']
        );
        $order_by = '';
        if ($arg1 == 'export' && $format != '') {
            $this->load->library('Export');
            $format = param_decrypt($format);
            $option_param = array('tariff' => true);
            $response_data = $this->dialplan_mod->get_data($order_by, '', '', $search_data, $option_param);
            $export_header = array('Route', 'Prefix', 'Carrier', 'Priority', 'Start Day', 'Start Time', 'End Day', 'End Time', 'Load', 'Status');
            if ($response_data['total'] > 0) {
                foreach ($response_data['result'] as $row) {
                    $ex_status = $row['route_status'] == 1 ? 'Active' : 'Inactive';
                    $export_data[] = array($row['dialplan_id'], $row['dial_prefix'], $row['carrier_id'], $row['priority'], $row['start_day'], $row['start_time'], $row['end_day'], $row['end_time'], $row['load_share'], $ex_status);
                }
            } else
                $export_data = array('');

            $downloaded_message = $this->export->download($file_name, $format, $search_data, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = $this->uri->segment(3, 0);
            if (isset($_SESSION['search_dialplan_data']['s_no_of_records']) && $_SESSION['search_dialplan_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_dialplan_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            $response = $this->dialplan_mod->get_data($order_by, $pagination_uri_segment, $per_page, $search_data);
            $config = $this->utils_model->setup_pagination_option($response['total'], 'dialplans/index', $per_page, 3);
            $this->pagination->initialize($config);
            $data['page_name'] = $page_name;
            $data['searching'] = 1;
            $data['pagination'] = $this->pagination->create_links();
            $data['listing_data'] = $response['result'];
            $data['total_records'] = $data['listing_count'] = $response['total'];

            $this->load->view('basic/header', $data);
            $this->load->view('carrier/dialplans', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function addD() {
        $data['page_name'] = "dialplan_add";
        if (!check_account_permission('dialplan', 'add'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_prefix', 'Prefix', 'trim|required|min_length[1]|max_length[10]|numeric');
            $this->form_validation->set_rules('frm_route', 'Route', 'trim|required|alpha_numeric');
            $this->form_validation->set_rules('frm_carrier', 'Carrier', 'trim|required|alpha_numeric');
            $this->form_validation->set_rules('frm_priority', 'Priority', 'trim|required|min_length[1]|max_length[5]|is_natural_no_zero');
            $this->form_validation->set_rules('frm_start_day', 'Start Day', 'trim|required|min_length[0]|max_length[6]|is_natural');
            $this->form_validation->set_rules('frm_end_day', 'End Day', 'trim|required|min_length[0]|max_length[6]|is_natural');
            $this->form_validation->set_rules('frm_load', 'Load Share', 'trim|required|min_length[0]|max_length[100]|is_natural');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('frm_start_time', 'Start Time', 'trim');
            $this->form_validation->set_rules('frm_end_time', 'End Time', 'trim');


            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->dialplan_mod->add($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Dialplan Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(base_url() . 'dialplans/editD/' . param_encrypt($result['id']), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(base_url() . 'dialplans/index/', 'location', '301');
                        }
                    } else {
                        redirect(base_url() . 'routes/index/', 'location', '301');
                    }
                    redirect(base_url() . 'dialplans/editD/' . param_encrypt($route_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        $route_data = $this->route_mod->get_data('dialplan_name', 0, 10000, array('dialplan_status' => 1), array());
        $response = $this->carrier_mod->get_data('', 0, 10000, array('carrier_status' => 1), array());
        $data['route_data'] = $route_data['result'];
        $data['carrier_data'] = $response['result'];
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/dialplan_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editD() {
        $data['page_name'] = "dialplan_edit";
        if (!check_account_permission('dialplan', 'edit'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $dialplan_id = param_decrypt($this->uri->segment(3));
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_id', 'Dialplan ID', 'trim|required');
            $this->form_validation->set_rules('frm_carrier', 'Carrier', 'trim|required|alpha_numeric');
            $this->form_validation->set_rules('frm_priority', 'Priority', 'trim|required|min_length[1]|max_length[5]|is_natural_no_zero');
            $this->form_validation->set_rules('frm_start_day', 'Start Day', 'trim|required|min_length[0]|max_length[6]|is_natural');
            $this->form_validation->set_rules('frm_end_day', 'End Day', 'trim|required|min_length[0]|max_length[6]|is_natural');
            $this->form_validation->set_rules('frm_load', 'Load Share', 'trim|required|min_length[0]|max_length[100]|is_natural');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('frm_start_time', 'Start Time', 'trim');
            $this->form_validation->set_rules('frm_end_time', 'End Time', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->dialplan_mod->update($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Dialplan Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(base_url() . 'dialplans/editD/' . param_encrypt($dialplan_id), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(base_url() . 'dialplans/index/', 'location', '301');
                        }
                    } else {
                        redirect(base_url() . 'dialplans', 'location', '301');
                    }
                    redirect(base_url() . 'dialplans/editD/' . param_encrypt($dialplan_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        $show_404 = false;
        if (!empty($dialplan_id) && strlen($dialplan_id) > 0) {
            $search_data = array('id' => $dialplan_id);
            $response_data = $this->dialplan_mod->get_data('', 0, RECORDS_PER_PAGE, $search_data, array());
            if ($response_data['total'] > 0) {
                $data['data'] = $response_data['result'][0];
                $route_data = $this->route_mod->get_data('', 0, 100, array('dialplan_status' => 1), array());
                $data['route_data'] = $route_data['result'];
                $carrier_data = $this->carrier_mod->get_data('', 0, 100, array('carrier_status' => 1), array());
                $data['carrier_data'] = $carrier_data['result'];

//                print_r($data);
            } else {
                $show_404 = true;
            }
        } else {
            $show_404 = true;
        }
        $data['dialplan_id'] = $dialplan_id;
        $this->load->view('basic/header', $data);
        if ($show_404) {
            $this->load->view('basic/404', $data);
        } else {
            $this->load->view('carrier/dialplan_edit', $data);
        }
        $this->load->view('basic/footer', $data);
    }

}
