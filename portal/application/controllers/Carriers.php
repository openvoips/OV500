<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Carriers extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('carrier_mod');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->output->enable_profiler(ENABLE_PROFILE);
    }

    function index($arg1 = '', $format = '') {

        $page_name = "carrier_index";
        $data['page_name'] = $page_name;
        if (!check_account_permission('carrier', 'view'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('carrier', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'endusers', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->carrier_mod->delete($delete_param_array);
                if ($result === true) {
                    $suc_msgs = count($delete_id_array) . ' Carrier';
                    if (count($delete_id_array) > 1)
                        $suc_msgs .= 's';
                    $suc_msgs .= ' Deleted Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    redirect(current_url(), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } else {
                $err_msgs = 'Select user to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }

            redirect(base_url() . 'carriers', 'location', '301');
        }

        if (isset($_POST['search_action'])) {
            $_SESSION['search_carrier_data'] = array('s_carrier_name' => $_POST['carrier_name'], 's_status' => $_POST['status'], 's_carrier_id' => $_POST['carrier_id'], 's_gateway_username' => $_POST['gateway_username'], 's_gateway_ipaddress' => $_POST['gateway_ipaddress'], 's_no_of_records' => $_POST['no_of_rows']);
        } else {
            $_SESSION['search_carrier_data']['s_carrier_name'] = isset($_SESSION['search_carrier_data']['s_carrier_name']) ? $_SESSION['search_carrier_data']['s_carrier_name'] : '';
            $_SESSION['search_carrier_data']['s_status'] = isset($_SESSION['search_carrier_data']['s_status']) ? $_SESSION['search_carrier_data']['s_status'] : '';
            $_SESSION['search_carrier_data']['s_carrier_id'] = isset($_SESSION['search_carrier_data']['s_carrier_id']) ? $_SESSION['search_carrier_data']['s_carrier_id'] : '';
            $_SESSION['search_carrier_data']['s_gateway_username'] = isset($_SESSION['search_carrier_data']['s_gateway_username']) ? $_SESSION['search_carrier_data']['s_gateway_username'] : '';
            $_SESSION['search_carrier_data']['s_gateway_ipaddress'] = isset($_SESSION['search_carrier_data']['s_gateway_ipaddress']) ? $_SESSION['search_carrier_data']['s_gateway_ipaddress'] : '';
            $_SESSION['search_carrier_data']['s_no_of_records'] = isset($_SESSION['search_carrier_data']['s_no_of_records']) ? $_SESSION['search_carrier_data']['s_no_of_records'] : '';
        }
        $search_data = array('carrier_name' => $_SESSION['search_carrier_data']['s_carrier_name'], 'carrier_status' => $_SESSION['search_carrier_data']['s_status'], 'carrier_id' => $_SESSION['search_carrier_data']['s_carrier_id'], 'username' => $_SESSION['search_carrier_data']['s_gateway_username'], 'ipaddress' => $_SESSION['search_carrier_data']['s_gateway_ipaddress']);
        $order_by = 'carrier_id DESC';
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            $format = param_decrypt($format);
            $option_param = array('tariff' => true);
            $carriers_data = $this->carrier_mod->get_data($order_by, '', '', $search_data, $option_param);
            $search_array = array();
            if ($_SESSION['search_carrier_data']['s_carrier_id_name'] != '')
                $search_array['Account ID'] = $_SESSION['search_carrier_data']['s_carrier_id_name'];
            if ($_SESSION['search_carrier_data']['s_carrier_name'] != '')
                $search_array['Carrier'] = $_SESSION['search_carrier_data']['s_carrier_name'];
            if ($_SESSION['search_carrier_data']['s_status'] != '')
                $search_array['Status'] = $_SESSION['search_carrier_data']['s_status'] == 1 ? 'Active' : 'Inactive';
            if ($_SESSION['search_carrier_data']['s_gateway_username'] != '')
                $search_array['SIP Username'] = $_SESSION['search_carrier_data']['s_gateway_username'];
            if ($_SESSION['search_carrier_data']['s_gateway_ipaddress'] != '')
                $search_array['IP address'] = $_SESSION['search_carrier_data']['s_gateway_ipaddress'];
            $export_header = array('Account ID', 'Name', 'Tariff-plan', 'CC', 'CPS', 'Status');
            if (count($carriers_data['result']) > 0) {
                foreach ($carriers_data['result'] as $carrier_data_temp) {
                    $ex_status = $carrier_data_temp['carrier_status'] == 1 ? 'Active' : 'Inactive';
                    $ex_tariff_name = isset($carrier_data_temp['tariff']['tariff_name']) ? $carrier_data_temp['tariff']['tariff_name'] : '';
                    $export_data[] = array($carrier_data_temp['carrier_id_name'], $carrier_data_temp['carrier_name'], $ex_tariff_name, $carrier_data_temp['carrier_cc'], $carrier_data_temp['carrier_cps'], $ex_status);
                }
            } else
                $export_data = array('');
            $file_name = 'Carriers';
            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;

            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }


            if (isset($_SESSION['search_carrier_data']['s_no_of_records']) && $_SESSION['search_carrier_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_carrier_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            $option_param = array('tariff' => true);
            $carriers_data = $this->carrier_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            $data['total_records'] = $total = $this->carrier_mod->get_data_total_count();
            $config = array();
            $config = $this->utils_model->setup_pagination_option($total, 'carriers/index', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();
            $data['data'] = $carriers_data;
            $this->load->view('basic/header', $data);
            $this->load->view('carrier/carriers', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function addC() {
        $page_name = "carrier_add";
        $data['page_name'] = $page_name;
        if (!check_account_permission('carrier', 'add'))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('carrier_name', 'Name', 'trim|required');
            $this->form_validation->set_rules('carrier_cc', 'Maximum Call Sessions', 'trim|required');
            $this->form_validation->set_rules('carrier_cps', 'Call Sessions per Second', 'trim|required');
            $this->form_validation->set_rules('dp', 'Billing in Decimal', 'trim|required');
            $this->form_validation->set_rules('carrier_currency_id', 'Currency', 'trim|required');
            $this->form_validation->set_rules('tariff_id', 'Tariff Name', 'trim|required');
            $this->form_validation->set_rules('carrier_progress_timeout', 'Progress Timeout', 'trim|required');
            $this->form_validation->set_rules('carrier_ring_timeout', 'Ring Timeout', 'trim|required');
            $this->form_validation->set_rules('carrier_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('cli_prefer', 'CLI Prefer', 'trim|required');

            if ($_POST['diversion_header_option'] == 1) {
                $this->form_validation->set_rules('diversion_header_format', 'Diversion Header Format', 'trim|required');
            }
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['carrier_codecs'] = implode(',', $_POST['codecs']);
                $result = $this->carrier_mod->add($_POST);
                if ($result === true) {
                    $carrier_id = $this->carrier_mod->carrier_id;
                    $this->session->set_flashdata('suc_msgs', 'Carrier Added Successfully');

                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'carriers', 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers', 'location', '301');
                    }

                    redirect(base_url() . 'carriers/add', 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $logged_user_type = get_logged_user_group();
        $data['currency_options'] = $this->utils_model->get_currencies();

        $this->load->view('basic/header', $data);
        $this->load->view('carrier/carrier_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function edit($id = -1, $active_tab = 1) {
        if ($id == -1)
            show_404();
        if (!check_account_permission('carrier', 'edit'))
            show_404('403');
        $data['active_tab'] = $active_tab;

        $page_name = "carrier_edit";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('carrier', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(current_url(), 'location', '301');
            }
            if (!isset($_POST['delete_parameter_two'])) {
                $this->session->set_flashdata('err_msgs', 'Insufficient Parameters');
                redirect(current_url(), 'location', '301');
            }
            if (!isset($_POST['delete_id'])) {
                $err_msgs = 'Select to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
            $account_id = param_decrypt($id);
            switch ($_POST['delete_parameter_two']) {
                case 'carrier_ip_delete':

                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->carrier_mod->delete_ip($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'IP Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }

                    redirect(current_url(), 'location', '301');
                    break;

                case 'RandomCLI_delete':

                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->carrier_mod->delete_RandomCLI($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'IP Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }

                    redirect(current_url(), 'location', '301');
                    break;

                default:

                    $this->session->set_flashdata('err_msgs', 'Parameter mismatch');
                    redirect(current_url(), 'location', '301');
            }
        }

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $carrier_id = $_POST['carrier_id'];
            $data['carrier_id'] = $carrier_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('carrier_id', 'Carrier ID', 'trim|required');
            $this->form_validation->set_rules('carrier_name', 'Name', 'trim|required');
            $this->form_validation->set_rules('carrier_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('carrier_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('dp', 'DP', 'trim|required');
            $this->form_validation->set_rules('carrier_currency_id', 'Currency', 'trim|required');
            $this->form_validation->set_rules('tariff_id', 'Tariff Plan', 'trim|required');
            $this->form_validation->set_rules('carrier_progress_timeout', 'Progress Timeout', 'trim|required');
            $this->form_validation->set_rules('carrier_ring_timeout', 'Ring Timeout', 'trim|required');
            $this->form_validation->set_rules('carrier_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('cli_prefer', 'CLI Prefer', 'trim|required');
            $this->form_validation->set_rules('incoming_cdr_billing', 'Billing', 'trim');

            if ($_POST['diversion_header_option'] == 1) {
                $this->form_validation->set_rules('diversion_header_format', 'Diversion Header Format', 'trim|required');
            }
            if (trim($_POST['secret']) != '') {
                $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
            }
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['carrier_codecs'] = implode(',', $_POST['codecs']);
                $result = $this->carrier_mod->update($_POST);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Carrier Updated Successfully');
                    redirect(site_url('carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab']), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (!empty($id)) {
            $carrier_id = param_decrypt($id);
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            if (strlen($carrier_id) > 0)
                $search_data = array('carrier_id' => $carrier_id);
            $option_param = array('tariff' => true, 'customers' => true, 'ip' => true, 'callerid' => true, 'prefix' => true, 'callerid_incoming' => true, 'prefix_incoming' => true, 'randomcli' => true);
            $carriers_data_temp = $this->carrier_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($carriers_data_temp['result']))
                $carriers_data = current($carriers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $carriers_data;
        $data['carrier_id'] = $carrier_id;
        $logged_user_type = get_logged_user_group();
        $data['currency_options'] = $this->utils_model->get_currencies();

        $this->load->view('basic/header', $data);
        $this->load->view('carrier/carrier_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function addRandomCLI($id1 = -1, $active_tab = 1) {
        $carrier_id = param_decrypt($id1);
        if (strlen($carrier_id) < 1)
            show_404();
        $data['active_tab'] = $active_tab;
        $page_name = "carrier_addRandomCLI";
        $data['page_name'] = $page_name;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $carrier_id = $_POST['carrier_id'];
            $id = $_POST['id'];
            $data['carrier_id'] = $carrier_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('carrier_id', 'Carrier ID', 'trim|required');
            $this->form_validation->set_rules('clirule_name', 'CLI Rule Name', 'trim|required');
            $this->form_validation->set_rules('destination_prefix', 'DST Prefix', 'trim|required');
            $this->form_validation->set_rules('cli_fixprefix', 'Fix Prefix', 'trim|required');
            $this->form_validation->set_rules('cli_length', 'Max length Prefix', 'trim');
            $this->form_validation->set_rules('cli_status', 'Status', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {

                $result = $this->carrier_mod->add_RandomCLI($_POST);
                if ($result['status'] == '1') {
                    $id = $result['result']['id'];
                    $this->session->set_flashdata('suc_msgs', 'Random CLI Rule Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        $id = $result['result']['id'];
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editRandomCLI/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url('carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab']), 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers', 'location', '301');
                    }
                    redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result['result']['id'];
                    $data['err_msgs'] = "Duplicate Random CLI rule"; //$err_msgs;
                }
            }
        }
        if (strlen($carrier_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('carrier_id' => $carrier_id);
            $option_param = array();
            $carriers_data_temp = $this->carrier_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($carriers_data_temp['result']))
                $carriers_data = current($carriers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }

        $data['data'] = $carriers_data;
        $data['carrier_id'] = $carrier_id;
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/addRandomCLI', $data);
        $this->load->view('basic/footer', $data);
    }

    function editRandomCLI($id1 = -1, $id2 = -1, $active_tab = 1) {
        $carrier_id = param_decrypt($id1);
        $id = param_decrypt($id2);
        if (strlen($carrier_id) < 1 || $id2 == -1) {
            show_404();
        }
        $page_name = "carrier_editRandomCLI";
        $data['page_name'] = $page_name;
        $data['active_tab'] = $active_tab;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $carrier_id = $_POST['carrier_id'];
            $id = $_POST['id'];
            $data['carrier_id'] = $carrier_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('carrier_id', 'Carrier ID', 'trim|required');
            $this->form_validation->set_rules('clirule_name', 'CLI Rule Name', 'trim|required');
            $this->form_validation->set_rules('destination_prefix', 'DST Prefix', 'trim|required');
            $this->form_validation->set_rules('cli_fixprefix', 'Fix Prefix', 'trim|required');
            $this->form_validation->set_rules('cli_length', 'Max length Prefix', 'trim');
            $this->form_validation->set_rules('cli_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('id', 'RandomCLI ID', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->carrier_mod->update_RandomCLI($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'RandomCLI Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editRandomCLI/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url('carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab']), 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers', 'location', '301');
                    }
                    redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (strlen($carrier_id) > 0 && !empty($id)) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('carrier_id' => $carrier_id, 'id' => $id);
            // print_r($search_data);
            $carriers_data_temp = $this->carrier_mod->RandomCLI($search_data);
            if (isset($carriers_data_temp['result']))
                $carriers_data = current($carriers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $carriers_data_temp['result'];
        $data['carrier_id'] = $carrier_id;
        $data['id'] = $id;

        //  print_r($data);
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/editRandomCLI', $data);
        $this->load->view('basic/footer', $data);
    }

    public function addG($id1 = -1, $active_tab = 1) {
        $carrier_id = param_decrypt($id1);
        if (strlen($carrier_id) < 1)
            show_404();
        $page_name = "carrier_addG";
        $data['page_name'] = $page_name;
        $data['active_tab'] = $active_tab;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $carrier_id = $_POST['carrier_id'];
            $id = $_POST['id'];
            $data['carrier_id'] = $carrier_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('carrier_id', 'Carrier ID', 'trim|required');
            $this->form_validation->set_rules('ipaddress_name', 'Gateway Name', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'Gateway IP', 'trim|required');
            $this->form_validation->set_rules('auth_type', 'Gateway Type', 'trim|required');
            $this->form_validation->set_rules('load_share', 'Load sharing', 'trim');
            $this->form_validation->set_rules('ip_status', 'Status', 'trim|required');
            if (trim($_POST['gateway_type']) == "USER") {
                $this->form_validation->set_rules('username', 'SIP Username', 'trim|required|min_length[2]');
                $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[2]');
            }

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->carrier_mod->add_ip($_POST);
                if ($result['status'] == '1') {
                    $id = $result['result']['id'];
                    $this->session->set_flashdata('suc_msgs', 'Carrier Gateway Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        $id = $result['result']['id'];
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editG/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url('carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab']), 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers', 'location', '301');
                    }
                    redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result['result']['id'];
                    $data['err_msgs'] = "Duplicate gateway"; //$err_msgs;
                }
            }
        }
        if (strlen($carrier_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('carrier_id' => $carrier_id);
            $option_param = array();
            $carriers_data_temp = $this->carrier_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($carriers_data_temp['result']))
                $carriers_data = current($carriers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }

        $data['data'] = $carriers_data;
        $data['carrier_id'] = $carrier_id;
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/addG', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editG($id1 = -1, $id2 = -1, $active_tab = 1) {
        $carrier_id = param_decrypt($id1);
        $id = param_decrypt($id2);
        if (strlen($carrier_id) < 1 || $id2 == -1)
            show_404();
        $data['active_tab'] = $active_tab;
        $page_name = "carrier_editG";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $carrier_id = $_POST['carrier_id'];
            $id = $_POST['id'];
            $data['carrier_id'] = $carrier_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('carrier_id', 'Carrier ID', 'trim|required');
            $this->form_validation->set_rules('id', 'Gateway ID', 'trim|required');
            $this->form_validation->set_rules('ipaddress_name', 'Gateway Name', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'Gateway IP', 'trim|required');
            $this->form_validation->set_rules('auth_type', 'Gateway Type', 'trim|required');
            $this->form_validation->set_rules('ip_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('load_share', 'Load sharing', 'trim');
            if (trim($_POST['auth_type']) == "USER") {
                $this->form_validation->set_rules('username', 'SIP Username', 'trim|required|min_length[2]');
                if (trim($_POST['secret']) != '') {
                    $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[2]');
                }
            }
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->carrier_mod->update_ip($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Carrier Gateway Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editG/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers', 'location', '301');
                    }
                    redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (strlen($carrier_id) > 0 && !empty($id)) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('carrier_id' => $carrier_id, 'id' => $id);
            $carriers_data_temp = $this->carrier_mod->gateway_ipaddress($search_data);
            if (isset($carriers_data_temp['result']))
                $carriers_data = current($carriers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $carriers_data_temp['result'];
        $data['carrier_id'] = $carrier_id;
        $data['id'] = $id;
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/editG', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editSRCNo($id1 = -1, $active_tab = 1) {
        if ($id1 == -1)
            show_404();
        $page_name = "carrier_editSRCNo";
        $data['page_name'] = $page_name;
        $data['active_tab'] = $active_tab;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $carrier_id = $_POST['carrier_id'];
            $data['carrier_id'] = $carrier_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('carrier_id', 'Carrier ID', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['carrier_id'] = $_POST['carrier_id'];
                $post_array['carrier_key'] = $_POST['carrier_key'];

                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }
                $result = $this->carrier_mod->update_callerid($post_array);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Carrier Caller ID Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editSRCNo/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers', 'location', '301');
                    }
                    redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (!empty($id1)) {
            $carrier_id = param_decrypt($id1);
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('carrier_id' => $carrier_id);
            $option_param = array('callerid' => true);
            $carriers_data_temp = $this->carrier_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($carriers_data_temp['result'])) {
                $carriers_data = current($carriers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }

        $data['data'] = $carriers_data;
        $data['carrier_id'] = $carrier_id;
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/editSRCNo', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editDSTNo($id1 = -1, $active_tab = 1) {
        if ($id1 == -1)
            show_404();
        $page_name = "carrier_editDSTNo";
        $data['page_name'] = $page_name;
        $data['active_tab'] = $active_tab;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $carrier_id = $_POST['carrier_id'];
            $id = $_POST['id'];
            $data['active_tab'] = $_POST['tab'];
            $data['carrier_id'] = $carrier_id;
            $this->form_validation->set_rules('carrier_id', 'Carrier ID', 'trim|required');
            $this->form_validation->set_rules('rules', 'Rules', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['id'] = $_POST['id'];
                $post_array['carrier_id'] = $_POST['carrier_id'];
                $post_array['carrier_key'] = $_POST['carrier_key'];
                $post_array['rules'] = array();
                if ($_POST['rules'] != '') {
                    $post_array['rules'] = preg_split('/\r\n|\r|\n|,/', $_POST['rules']);
                }
                $result = $this->carrier_mod->update_prefix($post_array);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Carrier Termination Prefix Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editDSTNo/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers', 'location', '301');
                    }
                    redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (!empty($id1)) {
            $carrier_id = param_decrypt($id1);
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('carrier_id' => $carrier_id);
            $option_param = array('prefix' => true);
            $carriers_data_temp = $this->carrier_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($carriers_data_temp['result'])) {
                $carriers_data = current($carriers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $carriers_data;
        $data['carrier_id'] = $carrier_id;
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/editDSTNo', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editINSRCNo($id1 = -1, $active_tab = 1) {
        if ($id1 == -1)
            show_404();
        $page_name = "carrier_editINSRCNo";
        $data['page_name'] = $page_name;
        $data['active_tab'] = $active_tab;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $carrier_id = $_POST['carrier_id'];
            $id = $_POST['id'];
            $data['carrier_id'] = $carrier_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('carrier_id', 'Carrier ID', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['carrier_id'] = $_POST['carrier_id'];
                $post_array['carrier_key'] = $_POST['carrier_key'];

                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }
                $result = $this->carrier_mod->update_callerid_incoming($post_array);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Incoming Carrier Caller ID Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editINSRCNo/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers', 'location', '301');
                    }
                    redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (!empty($id1)) {
            $carrier_id = param_decrypt($id1);
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('carrier_id' => $carrier_id);
            $option_param = array('callerid_incoming' => true);
            $carriers_data_temp = $this->carrier_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($carriers_data_temp['result'])) {
                $carriers_data = current($carriers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $carriers_data;
        $data['carrier_id'] = $carrier_id;
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/editINSRCNo', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editINDSTNo($id1 = -1, $active_tab = 1) {
        if ($id1 == -1)
            show_404();
        $data['active_tab'] = $active_tab;
        $page_name = "carrier_editINDSTNo";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $carrier_id = $_POST['carrier_id'];
            $id = $_POST['id'];
            $data['carrier_id'] = $carrier_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('carrier_id', 'Carrier ID', 'trim|required');
            $this->form_validation->set_rules('rules', 'Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['carrier_id'] = $_POST['carrier_id'];
                $post_array['carrier_key'] = $_POST['carrier_key'];
                $post_array['rules'] = array();
                if ($_POST['rules'] != '') {
                    $post_array['rules'] = preg_split('/\r\n|\r|\n|,/', $_POST['rules']);
                }
                $result = $this->carrier_mod->update_prefix_incoming($post_array);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Carrier Incoming Termination Prefix Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editINDSTNo/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers', 'location', '301');
                    }

                    redirect(base_url() . 'carriers/edit/' . param_encrypt($carrier_id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (!empty($id1)) {
            $carrier_id = param_decrypt($id1);
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('carrier_id' => $carrier_id);
            $option_param = array('prefix_incoming' => true);
            $carriers_data_temp = $this->carrier_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);

            if (isset($carriers_data_temp['result'])) {
                $carriers_data = current($carriers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $carriers_data;
        $data['carrier_id'] = $carrier_id;
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/editINDSTNo', $data);
        $this->load->view('basic/footer', $data);
    }

    public function diversion($id = -1) {
        if ($id == -1)
            show_404();
        $carrier_id = param_decrypt($id);
        $data = Array();
        $page_name = "diversion";
        $search_session_key = $page_name;
        $data['page_name'] = $page_name;
        if (!check_account_permission('carrier', 'view'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->carrier_mod->delete_diversion($delete_param_array);
                if ($result === true) {
                    $suc_msgs = count($delete_id_array) . ' Diversion';
                    $suc_msgs .= ' Deleted Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    redirect(current_url(), 'location', '301');
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
            redirect(base_url() . 'carriers/diversion' . param_encrypt($carrier_id), 'location', '301');
        }
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteDataBulk') {

            $cancel_diversion_number = explode(",", $_POST['cancel_diversion_number']);
            $diversion_number = array();
            foreach ($cancel_diversion_number as $key => $value) {
                $diversion_number['delete_id'][] = $value;
            }
            $count = count($diversion_number['delete_id']);
            $result = $this->carrier_mod->delete_bulk_diversion($diversion_number, $carrier_id);
            $suc_msgs = $count . ' Diversion Cancelled Successfully';

            if ($result === true) {
                $this->session->set_flashdata('suc_msgs', $suc_msgs);
                redirect(current_url(), 'location', '301');
            } else {
                $err_msgs = $result;
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
        }
        $search_parameters = array('diversion_number', 'account_id', 'name', 'number_status', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        $search_data = array(
            'carrier_id' => $carrier_id,
            'diversion_number' => $_SESSION[$search_session_key]['diversion_number'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'name' => $_SESSION[$search_session_key]['name'],
            'number_status' => $_SESSION[$search_session_key]['number_status'],
        );

        $is_file_downloaded = false;
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $diversion_data = $this->carrier_mod->get_diversion_data('', $per_page, $segment, $search_data);

            $total_count = $this->carrier_mod->get_data_total_count_der();
            $data['pagination'] = setup_pagination_option($total_count, 'carriers/diversion' . param_encrypt($carrier_id), $per_page, $pagination_uri_segment, $this->pagination);

            $data['diversion_data'] = $diversion_data;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;
            $data['carrier_id'] = $carrier_id;
            $this->load->view('basic/header', $data);
            $this->load->view('carrier/diversion', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function adddiversion($id = '') {
        if ($id == -1)
            show_404();
        $carrier_id = param_decrypt($id);
        $page_name = "diversion_add";
        $data['page_name'] = $page_name;
        $data['carrier_id'] = $carrier_id;
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('diversion_number', 'Diversion Number', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {

                $result = $this->carrier_mod->add_diversion($_POST);
                if ($result['status'] === true) {
                    $this->session->set_flashdata('suc_msgs', 'Diversion Number Added Successfully');

                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editdiversion/' . param_encrypt($carrier_id) . '/' . param_encrypt($result['id']), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'carriers/diversion/' . param_encrypt($carrier_id), 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers/diversion/' . param_encrypt($carrier_id), 'location', '301');
                    }

                    redirect(base_url() . 'carriers/adddiversion/' . param_encrypt($carrier_id), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/add_diversion', $data);
        $this->load->view('basic/footer', $data);
    }

    function editdiversion($ids = '', $id2 = '') {
        if ($ids == -1)
            show_404();
        if ($id2 == -1)
            show_404();
        $carrier_id = param_decrypt($ids);
        $id = param_decrypt($id2);
        $data['id'] = $id;
        $data['carrier_id'] = $carrier_id;
        $page_name = "diversion_edit";
        $data['page_name'] = $page_name;
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('diversion_number', 'Diversion Number', 'trim|required');
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('number_status', 'Status', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {

                $result = $this->carrier_mod->edit_diversion($_POST);
                if ($result['status'] === true) {
                    $this->session->set_flashdata('suc_msgs', 'Diversion Number Updated Successfully');

                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'carriers/editdiversion/' . param_encrypt($carrier_id) . '/' . param_encrypt($id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'carriers/diversion/' . param_encrypt($carrier_id), 'location', '301');
                    } else {
                        redirect(base_url() . 'carriers/diversion/' . param_encrypt($carrier_id), 'location', '301');
                    }

                    redirect(base_url() . 'carriers/adddiversion/' . param_encrypt($carrier_id), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $search_data = array('carrier_id' => $carrier_id, 'id' => $id);
        $result = $this->carrier_mod->get_diversion_data('', '', '', $search_data);
        $data['diversion_data'] = $result['result'][0];
//         echo '<pre>';
//         print_r($diversion_data);die;
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/edit_diversion', $data);
        $this->load->view('basic/footer', $data);
    }

    function addbulkdiversion($id = '') {
        if ($id == -1)
            show_404();
        $carrier_id = param_decrypt($id);
        $page_name = "diversion_bulkadd";
        $data['page_name'] = $page_name;
        $dir = 'uploads/diversion/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $account_id = get_logged_account_id();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {


            $config['upload_path'] = $dir;  //'uploads/dnd/'.SITE_SUBDOMAIN.'/';
            $config['allowed_types'] = 'csv';
            $config['file_name'] = strtolower($account_id) . '_dnd_' . date('YmdHis');
            $config['file_ext_tolower'] = TRUE;
            $config['max_size'] = 0;
            //   $config['encrypt_name'] = TRUE;
            $config['remove_spaces'] = TRUE;
// $this->upload->initialize($config);
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('file_diversion_number')) {
//                echo $this->upload->display_errors();
//                die;
                $this->session->set_flashdata('err_msgs', $this->upload->display_errors());
                //redirect(base_url().'dnd', 'location', '301');
            } else {

                ini_set('memory_limit', '2048M');
                $data = array('upload_data' => $this->upload->data());
                $file_with_path = $dir . $data['upload_data']['file_name'];
                $csv = array_map('str_getcsv', file($file_with_path));
                //  print_r(str_getcsv($file_with_path))   ;
                //var_dump($csv);die;


                $filename = $config['file_name'] . '.csv';
                $result = $this->carrier_mod->add_diversion_bulk($carrier_id, $filename, $csv);
                if ($result === true) {//success						
                    $this->session->set_flashdata('suc_msgs', 'Diversion Numbers Added Successfully');
                    redirect(base_url('carriers/diversion/') . param_encrypt($carrier_id), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $data['carrier_id'] = $carrier_id;
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/add_bulk_diversion', $data);
        $this->load->view('basic/footer', $data);
    }

}
