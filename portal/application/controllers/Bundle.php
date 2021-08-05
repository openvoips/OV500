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

class Bundle extends MY_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('Bundle_mod');
        $this->load->model('Utils_model');
    }

    public function index($arg1 = '', $format = '') {
        $page_name = "bundle_index";
        $file_name = 'Bundle_' . date('Ymd');
        $is_file_downloaded = false;
        if (!check_account_permission('bundle', 'view')) {
            show_404('403');
        }
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('bundle', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'bundle', 'location', '301');
            }
            $param = '';
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_parameter_two']))
                $param = param_decrypt($_POST['delete_parameter_two']);
            if ($param == '') {
                if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->Bundle_mod->delete($delete_param_array);
                    if ($result['status'] === true) {
                        $suc_msgs = count($delete_id_array) . ' Bundle';
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
                    $result = $this->Bundle_mod->delete_mapping($delete_param_array);

                    if ($result['status'] === true) {
                        $suc_msgs = count($delete_id_array) . ' Bundle Map';
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
            $_SESSION['search_bundle_data'] = array(
                's_bundle_package_name' => $_POST['name'],
                's_bundle_package_currency' => $_POST['currency'],
                's_bundle_package_id' => $_POST['bundle_package_id'],
                's_bundle_package_status' => $_POST['status'],
                's_no_of_records' => $_POST['no_of_rows']
            );
        } else {
            $r = $this->uri->segment(2);
            if (strlen($r) == 0) {
                $_SESSION['search_bundle_data']['s_bundle_package_name'] = isset($_SESSION['search_bundle_data']['s_bundle_package_name']) ? $_SESSION['search_bundle_data']['s_bundle_package_name'] : '';
                $_SESSION['search_bundle_data']['s_bundle_package_id'] = isset($_SESSION['search_bundle_data']['s_bundle_package_id']) ? $_SESSION['search_bundle_data']['s_bundle_package_id'] : '';
                $_SESSION['search_bundle_data']['s_bundle_package_currency'] = isset($_SESSION['search_bundle_data']['s_bundle_package_currency']) ? $_SESSION['search_bundle_data']['s_bundle_package_currency'] : '';
                $_SESSION['search_bundle_data']['s_bundle_package_status'] = isset($_SESSION['search_bundle_data']['s_bundle_package_status']) ? $_SESSION['search_bundle_data']['s_bundle_package_status'] : '';
                $_SESSION['search_bundle_data']['s_no_of_records'] = isset($_SESSION['search_bundle_data']['s_no_of_records']) ? $_SESSION['search_bundle_data']['s_no_of_records'] : '';
            }
        }
        $search_data = array(
            'bundle_package_name' => $_SESSION['search_bundle_data']['s_bundle_package_name'],
            'bundle_package_currency_id' => $_SESSION['search_bundle_data']['s_bundle_package_currency'],
            'bundle_package_id' => $_SESSION['search_bundle_data']['s_bundle_package_id'],
            'bundle_package_status' => $_SESSION['search_bundle_data']['s_bundle_package_status'],
        );


        $search_data['created_by'] = get_logged_account_id();

        $order_by = '';
        if ($arg1 == 'export' && $format != '') {
            $this->load->library('Export');
            $format = param_decrypt($format);
            $option_param = array('bundle' => true);
            $response_data = $this->Bundle_mod->get_data($order_by, '', '', $search_data, $option_param);
            $export_header = array('Name', 'Bundle Code', 'Currency', 'Description', 'Status');
            if ($response_data['total'] > 0) {
                foreach ($response_data['result'] as $row) {
                    $ex_status = $row['bundle_package_status'] == 1 ? 'Active' : 'Inactive';
                    $export_data[] = array($row['bundle_package_name'], $row['bundle_package_id'], $row['currency_name'], $row['bundle_package_description'], $ex_status);
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
            if (isset($_SESSION['search_bundle_data']['s_no_of_records']) && $_SESSION['search_bundle_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_bundle_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            $response = $this->Bundle_mod->get_data($order_by, $pagination_uri_segment, $per_page, $search_data);
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['total'], 'bundle/index', $per_page, 3);
            $this->pagination->initialize($config);

            $data['page_name'] = $page_name;
            $data['pagination'] = $this->pagination->create_links();
            $data['listing_data'] = $response['result'];
            $data['total_records'] = $data['listing_count'] = $response['total'];

            $this->load->view('basic/header', $data);
            $this->load->view('rates/bundles', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function addBP() {
        $data['page_name'] = "bundle_add";

        if (!check_account_permission('bundle', 'add'))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();



        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            //$this->form_validation->set_rules('bundle_package_id', 'BUNDLE ID', 'trim|required');
            $this->form_validation->set_rules('bundle_package_name', 'Name', 'trim|required|min_length[5]|max_length[30]');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->Bundle_mod->add($_POST);
                //echo '<pre>';print_r($_REQUEST);  var_dump($result);echo $this->Bundle_mod->bundle_package_id;die;
                if ($result === true) {
                    $bundle_package_id = $this->Bundle_mod->bundle_package_id;
                    $this->session->set_flashdata('suc_msgs', 'Bundle & Package Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'bundle/editBP/' . param_encrypt($bundle_package_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'bundle', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'bundle', 'location', '301');
                    }
                } else {
                    $data['err_msgs'] = $result;
                }
            }
        }
        $data['currency_data'] = $this->Utils_model->get_currencies();

        $this->load->view('basic/header', $data);
        $this->load->view('rates/bundle_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editBP($bundle_id = -1) {
        $data['page_name'] = "bundle_edit";
        if (!check_account_permission('bundle', 'edit'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if ($bundle_id == -1)
            show_404();
        $bundle_id = param_decrypt($bundle_id);

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {

            $this->form_validation->set_rules('bundle_package_id', 'BUNDLE ID', 'trim|required');
            $this->form_validation->set_rules('bundle_package_name', 'Name', 'trim|required|min_length[5]|max_length[30]');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {//echo '<pre>';print_r($_REQUEST); echo '</pre>';
                $result = $this->Bundle_mod->update($_POST);
                //echo '<pre>';print_r($_REQUEST);  var_dump($result);die;
                if ($result) {
                    $this->session->set_flashdata('suc_msgs', 'Bundle & Package Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'bundle/editBP/' . param_encrypt($bundle_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'bundle', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'bundle', 'location', '301');
                    }
                } else {
                    $data['err_msgs'] = $result;
                }
            }
        }

        $search_data = array('bundle_package_id' => $bundle_id);
        $response_data = $this->Bundle_mod->get_data('', 0, RECORDS_PER_PAGE, $search_data, array());

        if ($response_data['total'] > 0) {
            $data['data'] = $response_data['result'][0];
        } else {
            show_404();
        }


        $data['currency_data'] = $this->Utils_model->get_currencies();

        $this->load->view('basic/header', $data);
        $this->load->view('rates/bundle_edit', $data);
        $this->load->view('basic/footer', $data);
    }

}
