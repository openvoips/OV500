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

class Resellers extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('reseller_mod');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (get_logged_account_level() > 2)
            show_404('403');
    }

      function rState($id = -1) {
        $page_name = "rState";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');
        $this->load->model('reseller_mod');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (check_logged_account_type(array('CUSTOMER'))) {
            $account_id = get_logged_account_id();
        } elseif ($id != -1) {
            $id_decrypted = param_decrypt($id);
            $search_data = array();
            if (check_logged_account_type(array('RESELLER')))
                $search_data['parent_account_id'] = get_logged_account_id();
            elseif (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                //$search_data['parent_account_id']='';
            }

            if (is_numeric($id_decrypted))
                $search_data['customer_id'] = $id_decrypted;
            else
                $search_data['account_id'] = $id_decrypted;

            $endusers_data_temp = $this->reseller_mod->get_data('', 1, 0, $search_data);
            
         //   print_r($endusers_data_temp);
            if (isset($endusers_data_temp['result'])) {
                $endusers_data = current($endusers_data_temp['result']);

                $account_id = $endusers_data['account_id'];
            } else {
                show_404();
            }
        }
        if (isset($_POST['report_time'])) {
            $report_time = $_POST['report_time'];
            $range = explode(' - ', $report_time);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);
            $date_from = $range[0];
            $date_to = $range[1];
            $search_account_id = trim($_POST['account_id']);

            $src_ipaddress = trim($_POST['src_ipaddress']);
            $prefix_name = trim($_POST['prefix_name']);
        } else {
            $date_from = date('Y-m-d') . ' 00:00:00';
            $date_to = date('Y-m-d') . ' 23:59:59';

            $src_ipaddress = '';
            $prefix_name = '';
        }
        $time_range = $date_from . ' - ' . $date_to;

        if (isset($search_account_id) && $search_account_id != '')
            $account_id = $search_account_id;      
        
        $account_level = $endusers_data['account_level'];        
        
        $report_data = $this->report_mod->reseller_call_sipcode_review($account_id, $date_from, $date_to, $src_ipaddress, $prefix_name, $account_level);
        $data['report_data'] = $report_data;
        $data['account_id'] = $account_id;
        $data['time_range'] = $time_range;

        $data['src_ipaddress'] = $src_ipaddress;
        $data['prefix_name'] = $prefix_name;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/cState', $data);
        $this->load->view('basic/footer', $data);
    }
    
    function myplan($account_id = '') {
        $account_id = param_decrypt($account_id);
        if (strlen($account_id) > 0) {
            $order_by = '';
            $pagination_uri_segment = 3;
            $per_page = RECORDS_PER_PAGE;
            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }
            $search_data = Array('account_id' => $account_id);
            $option_param = array('tariff' => true, 'balance' => true, 'currency' => true, 'pbx' => true);
            $customers_data = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            $total = $this->reseller_mod->get_data_total_count();
            $config = array();
            $config = $this->utils_model->setup_pagination_option($total, 'customers/index', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();
            $data['data'] = $customers_data;

            $this->load->view('basic/header', $data);
            $this->load->view('customer/myplan', $data);
            $this->load->view('basic/footer', $data);
        } else {
            redirect(base_url() . 'customers', 'location', '301');
        }
    }

    
    function statement($id = -1, $arg1 = '', $format = '') {
    $page_name = "r_statement";
    $data['page_name'] = $page_name;
    $this->load->model('report_mod');
    $this->load->model('payment_mod');

    $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();


//==========export pdf start==========================			
    if ($arg1 == 'export' && $format != '') {

        if ($id != -1) {
            $account_id = param_decrypt($id);
        }

        $format = param_decrypt($format);

        $customer_result = $this->member_mod->get_account_by_key('account_id', $account_id);
        if (!$customer_result) {
            $data['statement_error_message'] = 'Account Not Found';
        }

        $search_data = array('yearmonth' => $_SESSION['search_sdr_summary_data']['s_yearmonth']);

        $report_data = $this->report_mod->sdr_statement($account_id, $search_data);

        $yearmonth = $_SESSION['search_sdr_summary_data']['s_yearmonth'];
        $year = substr($yearmonth, 0, 4);
        $month = substr($yearmonth, 4);

        $date = $year . '-' . $month . '-01';
        $month_year = date('F-Y', strtotime($date));
        $customer_dp = $customer_result['dp'];
        if (!$customer_dp || $customer_dp == '')
            $customer_dp = 2;
        $sdr_terms = $this->utils_model->get_sdr_terms();

        $file_name = "account_statements";
        $this->load->library('Export');

        if (count($report_data['result']) > 0) {
            if ($format == 'pdf') {
                $downloaded_message = $this->export->download_pdf($file_name, $report_data, $sdr_terms, $customer_dp, $month_year, $account_id);
            } elseif ($format == 'xlsx') {
                $downloaded_message = $this->export->download_excel($file_name, $report_data, $sdr_terms, $customer_dp, $month_year, $account_id, $format);
            }
        } else {
            
        }


        if (gettype($downloaded_message) == 'string')
            $data['err_msgs'] = $downloaded_message;
    }

//================================export pdf end===========================


    if (check_logged_account_type(array('RESELLER', 'CUSTOMER'))) {
        $account_id = get_logged_account_id();
    } elseif (isset($_POST['search_action']) && isset($_POST['account_id']) && $_POST['account_id'] != '') {
        $account_id = trim($_POST['account_id']);
    } elseif (isset($_POST['invoice_search_action']) && isset($_POST['account_id']) && $_POST['account_id'] != '') {
        $account_id = trim($_POST['account_id']);
    } elseif ($id != -1) {
        $account_id = param_decrypt($id);
    }



    if (isset($account_id) && $account_id != '') {
        $customer_result = $this->member_mod->get_account_by_key('account_id', $account_id);
        //print_r($customer_result);
        //echo $customer_result['billing_type'];
        if (!$customer_result) {
            $data['statement_error_message'] = 'Account Not Found';
        }

        $active_tab = 'tab_statement'; //default active tab

        if (isset($_POST['search_action'])) {// coming from search button account statement
            $_SESSION['search_sdr_summary_data'] = array('s_yearmonth' => $_POST['yearmonth'], 's_yearmonth_invoice' => $_POST['yearmonth']);
        } elseif (isset($_POST['invoice_search_action'])) {// coming from search button invoice
            $_SESSION['search_sdr_summary_data'] = array('s_yearmonth' => $_POST['yearmonth'], 's_yearmonth_invoice' => $_POST['yearmonth']);
            $active_tab = 'tab_invoice';
        } else {
            $_SESSION['search_sdr_summary_data']['s_yearmonth'] = isset($_SESSION['search_sdr_summary_data']['s_yearmonth']) ? $_SESSION['search_sdr_summary_data']['s_yearmonth'] : date("Ym");

            $_SESSION['search_sdr_summary_data']['s_yearmonth_invoice'] = isset($_SESSION['search_sdr_summary_data']['s_yearmonth_invoice']) ? $_SESSION['search_sdr_summary_data']['s_yearmonth_invoice'] : date("Ym");
        }

        $search_data = array('yearmonth' => $_SESSION['search_sdr_summary_data']['s_yearmonth']);

        $report_data = $this->report_mod->sdr_statement($account_id, $search_data);

        $data['active_tab'] = $active_tab;
        $data['customer_dp'] = $customer_result['dp'];
        $data['sdr_terms'] = $this->utils_model->get_sdr_terms();
        $data['searched_account_id'] = $account_id;
        $data['data'] = $report_data;
        $data['billing_type'] = $customer_result['billing_type'];
        ////invoice data//////
        if ($customer_result['billing_type'] == 'prepaid') {
            $yearmonth = $_SESSION['search_sdr_summary_data']['s_yearmonth_invoice'];
            $year = substr($yearmonth, 0, 4);
            $month = substr($yearmonth, -2);
            if (intval($year) < 2019 || (intval($year) == 2019 && intval($month) < 4)) {//no invoice before April 2019
                $year = '2019';
                $month = '04';
                $_SESSION['search_sdr_summary_data']['s_yearmonth_invoice'] = $year . $month;
            }
            $from = $year . '-' . $month . '-01 00:00:00';
            $to = date('Y-m-d 23:59:59', strtotime('last day of ' . $from));
            $date_range = $from . ' - ' . $to;

            $search_data = array('account_id' => $account_id, 'payment_option_id' => 'ADDBALANCE', 'date_range' => $date_range);
            $order_by = '';
            $data_array = $this->payment_mod->get_data($order_by, '', '', $search_data);
            if (isset($data_array['result']))
                $data['payment_history'] = $data_array['result'];
        }
    }

    $this->load->view('basic/header', $data);
    $this->load->view('reports/sdr_statement', $data);
    $this->load->view('basic/footer', $data);
}


    function index($arg1 = '', $format = '', $reseller_type = 'reseller') {
        $page_name = "{$reseller_type}_index";
        $data['page_name'] = $page_name;
        if (!check_account_permission('reseller', 'view'))
            show_404('404');

        $data['reseller_type'] = $reseller_type;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('reseller', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'resellers', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->reseller_mod->delete($delete_param_array);
                if ($result === true) {
                    $suc_msgs = count($delete_id_array) . ' ' . ucfirst($reseller_type);
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
            redirect(base_url($reseller_type . 's'), 'location', '301');
        }
        if (isset($_POST['search_action'])) {
            $_SESSION['search_resellers_data'] = array('s_name' => $_POST['name'], 's_status' => $_POST['status'], 's_account_id' => $_POST['account_id'], 's_no_of_records' => $_POST['no_of_rows'],);
        } else {
            $_SESSION['search_resellers_data']['s_name'] = isset($_SESSION['search_resellers_data']['s_name']) ? $_SESSION['search_resellers_data']['s_name'] : '';
            $_SESSION['search_resellers_data']['s_status'] = isset($_SESSION['search_resellers_data']['s_status']) ? $_SESSION['search_resellers_data']['s_status'] : '';
            $_SESSION['search_resellers_data']['s_account_id'] = isset($_SESSION['search_resellers_data']['s_account_id']) ? $_SESSION['search_resellers_data']['s_account_id'] : '';
            $_SESSION['search_resellers_data']['s_no_of_records'] = isset($_SESSION['search_resellers_data']['s_no_of_records']) ? $_SESSION['search_resellers_data']['s_no_of_records'] : '';
            
        }
        $search_data = array('name' => $_SESSION['search_resellers_data']['s_name'], 'account_status' => $_SESSION['search_resellers_data']['s_status'], 'account_id' => $_SESSION['search_resellers_data']['s_account_id']);
        if (check_logged_account_type(array('agent')))
            $search_data['agent'] = get_logged_account_id();
        elseif (check_logged_account_type(array('RESELLER')))
            $search_data['parent_account_id'] = get_logged_account_id();
        elseif (check_logged_account_type(array('ADMIN', 'SUBADMIN')))
            $search_data['account_level'] = '1';

        $order_by = '';
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            $format = param_decrypt($format);
            $option_param = array('tariff' => true);
            $resellers_data = $this->reseller_mod->get_data($order_by, '', '', $search_data, $option_param);
            $search_array = array();
            if ($_SESSION['search_resellers_data']['s_account_id'] != '')
                $search_array['Account ID'] = $_SESSION['search_resellers_data']['s_account_id'];
            if ($_SESSION['search_resellers_data']['s_name'] != '')
                $search_array['Reseller'] = $_SESSION['search_resellers_data']['s_name'];
            if ($_SESSION['search_resellers_data']['s_status'] != '')
                $search_array['Status'] = $_SESSION['search_resellers_data']['s_status'] == 1 ? 'Active' : 'Inactive';

            $export_header = array('Account ID', 'Name', 'Tariff-plan', 'CC', 'Status');
            if (count($resellers_data['result']) > 0) {
                foreach ($resellers_data['result'] as $resellers_data_temp) {
                    $ex_status = $resellers_data_temp['account_status'] == 1 ? 'Active' : 'Inactive';
                    $ex_tariff_name = isset($resellers_data_temp['tariff']['tariff_name']) ? $resellers_data_temp['tariff']['tariff_name'] : '';
                    $export_data[] = array($resellers_data_temp['account_id'], $resellers_data_temp['name'], $ex_tariff_name, $resellers_data_temp['account_cc'], $ex_status);
                }
            } else
                $export_data = array('');
            $file_name = ucfirst($reseller_type) . 's';
            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        if ($is_file_downloaded === false) {

            $pagination_uri_segment = 3;
            $per_page = RECORDS_PER_PAGE;
                       
             if (isset($_SESSION['search_resellers_data']['s_no_of_records']) && $_SESSION['search_resellers_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_resellers_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;
           
            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }
            
            $option_param = array('tariff' => true, 'balance' => true, 'currency' => true);
            $resellers_data = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
           $data['total_records'] =    $total = $this->reseller_mod->get_data_total_count();
            $config = array();
            $config = $this->utils_model->setup_pagination_option($total, $reseller_type . 's/index', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();
            $data['data'] = $resellers_data;
            $this->load->view('basic/header', $data);
            $this->load->view('reseller/resellers', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function add($reseller_type = 'reseller') {
        $page_name = "{$reseller_type}_add";
        $data['page_name'] = $page_name;
        if (!check_account_permission('reseller', 'add'))
            show_404('403');

        $data['reseller_type'] = $reseller_type;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
             if($_POST['vat_flag']== 'NONE')
                $_POST['tax_type']='exclusive';
            $this->form_validation->set_rules('account_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('account_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('dp', 'DP', 'trim|required');
            $this->form_validation->set_rules('currency_id', 'Currency', 'trim|required');
            $this->form_validation->set_rules('tariff_id', 'Tariff Plan', 'trim|required');
            $this->form_validation->set_rules('tax_type', 'Tax Type', 'trim|required');
            $this->form_validation->set_rules('tax1', 'Tax 1', 'trim|required');
            $this->form_validation->set_rules('tax2', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('tax3', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('account_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[6]');
            $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
            $this->form_validation->set_rules('cli_check', 'Caller ID Check', 'trim|required');
            $this->form_validation->set_rules('dialpattern_check', 'Number Check', 'trim|required');
            $this->form_validation->set_rules('llr_check', 'LLR Check', 'trim|required');
            $this->form_validation->set_rules('media_transcoding', 'Transcoding', 'trim|required');
            $this->form_validation->set_rules('media_rtpproxy', 'With-media', 'trim|required');
            $this->form_validation->set_rules('billing_type', 'Billing Type', 'trim|required');
            $this->form_validation->set_rules('agent', 'Account Manager', 'trim');
            $this->form_validation->set_rules('tax_number', 'Tax Number', 'trim');
            $this->form_validation->set_rules('vat_flag', 'VAT Flag', 'trim');
            $this->form_validation->set_rules('name', 'Name', 'trim|required');
            $this->form_validation->set_rules('company_name', 'Company', 'trim');
            $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim|required');
            $this->form_validation->set_rules('address', 'address', 'trim');
            $this->form_validation->set_rules('phone', 'phone', 'trim');
            $this->form_validation->set_rules('country_id', 'Country', 'trim');
            $this->form_validation->set_rules('state_code_id', 'State', 'trim');
            $this->form_validation->set_rules('pincode', 'Pin-Code', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $logged_user_type = get_logged_account_type();
                if (in_array($logged_user_type, array('RESELLER', 'CARRIER')))
                    $_POST['parent_account_id'] = get_logged_account_id();
                else
                    $_POST['parent_account_id'] = '';
                if ($_POST['media_rtpproxy'] == 0) {
                    $_POST['media_transcoding'] = '0';
                }               
                $result = $this->reseller_mod->add($_POST);            
                if ($result === true) {
                    $account_id = $this->reseller_mod->account_id;
                    $this->session->set_flashdata('suc_msgs', 'Resellers Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url($reseller_type . 's'), 'location', '301');
                    }
                    else {
                        redirect(base_url($reseller_type . 's'), 'location', '301');
                    }

                    redirect(base_url($reseller_type . 's') . '/add', 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $logged_user_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $data['country_options'] = $this->utils_model->get_countries();
        $data['currency_options'] = $this->utils_model->get_currencies();
        $data['state_options'] = $this->utils_model->get_states();

        if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC'))) {
            $data['tariff_options'] = $this->utils_model->get_tariffs($logged_user_type, 'CUSTOMER');
            $data['ac_mngrs_data'] = $this->member_mod->get_data('', '', '', array('account_type' => 'AGENT'));
        } else {
            $data['tariff_options'] = $this->utils_model->get_tariffs($logged_user_type, 'CUSTOMER', $logged_account_id);
            $option_param = array('tariff' => true);
            $data['logged_account_result'] = $this->member_mod->get_account_by_key('account_id', $logged_account_id, $option_param);
        }

        $this->load->view('basic/header', $data);
        $this->load->view('reseller/reseller_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function edit($account_id = -1, $reseller_type = 'reseller') {
        $account_id = param_decrypt($account_id);
        if (strlen($account_id) == 0)
            show_404();
        if (!check_account_permission('reseller', 'view') && !check_account_permission('reseller', 'edit'))
            show_404('403');
        $page_name = "{$reseller_type}_edit";
        $data['page_name'] = $page_name;
        $data['reseller_type'] = $reseller_type;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('reseller', 'delete')) {
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
            switch ($_POST['delete_parameter_two']) {
                case 'reseller_dialplan_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->reseller_mod->delete_dialplan($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'Dialplan Deleted Successfully';
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
             if($_POST['vat_flag']== 'NONE')
                $_POST['tax_type']='exclusive';
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('account_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('dp', 'DP', 'trim|required');
            $this->form_validation->set_rules('account_currency_id', 'Currency', 'trim|required');
            $this->form_validation->set_rules('tariff_id', 'Tariff Plan', 'trim|required');
            $this->form_validation->set_rules('tax_type', 'Tax Type', 'trim|required');
            $this->form_validation->set_rules('tax1', 'Tax 1', 'trim|required');
            $this->form_validation->set_rules('tax2', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('tax3', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('vat_flag', 'VAT Flag', 'trim');
            $this->form_validation->set_rules('account_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('cli_check', 'Caller ID Check', 'trim|required');
            $this->form_validation->set_rules('dialpattern_check', 'Number Check', 'trim|required');
            $this->form_validation->set_rules('llr_check', 'LLR Check', 'trim|required');
            $this->form_validation->set_rules('media_transcoding', 'Transcoding', 'trim|required');
            $this->form_validation->set_rules('media_rtpproxy', 'With-media', 'trim|required');
            $this->form_validation->set_rules('billing_type', 'Billing Type', 'trim|required');
            $this->form_validation->set_rules('agent', 'Account Manager', 'trim');
            $this->form_validation->set_rules('tax_number', 'Tax Number', 'trim');
            $this->form_validation->set_rules('name', 'Name', 'trim|required');
            $this->form_validation->set_rules('company_name', 'Company', 'trim');
            $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim|required');
            $this->form_validation->set_rules('address', 'address', 'trim');
            $this->form_validation->set_rules('phone', 'phone', 'trim');
            $this->form_validation->set_rules('country_id', 'Country', 'trim');
            $this->form_validation->set_rules('state_code_id', 'State', 'trim');
            $this->form_validation->set_rules('pincode', 'Pin-Code', 'trim');

            if (!isset($_POST['state_code_id']) || $_POST['country_id'] != 100)
                $_POST['state_code_id'] = '0';

            if (trim($_POST['secret']) != '') {
                $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
            }

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                if ($_POST['media_rtpproxy'] == 0) {
                    $_POST['media_transcoding'] = '0';
                }
                $result = $this->reseller_mod->update($_POST);
//                echo '<pre>';	print_r($_POST); print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', ucfirst($reseller_type) . ' Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url($reseller_type . 's'), 'location', '301');
                    }
                    else {
                        redirect(base_url($reseller_type . 's'), 'location', '301');
                    }
                    redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;

            $search_data = array('account_id' => $account_id);
            if (check_logged_account_type(array('agent')))
                $search_data['agent'] = get_logged_account_id();
            elseif (check_logged_account_type(array('RESELLER')))
                $search_data['parent_account_id'] = get_logged_account_id();
            elseif (check_logged_account_type(array('ADMIN', 'SUBADMIN')))
                $search_data['account_level'] = '1';


            $option_param = array('callerid' => true, 'tariff' => true, 'account' => true, 'dialplan' => true, 'translation_rules' => true, 'callerid_incoming' => true, 'translation_rules_incoming' => true);
            $resellers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($resellers_data_temp['result']))
                $resellers_data = current($resellers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }

        $data['data'] = $resellers_data;
        $data['account_id'] = $account_id;
        $logged_user_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $data['country_options'] = $this->utils_model->get_countries();
        $data['currency_options'] = $this->utils_model->get_currencies();
        $data['state_options'] = $this->utils_model->get_states();

        if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC'))) {
            $data['tariff_options'] = $this->utils_model->get_tariffs($logged_user_type, 'CUSTOMER');
            $data['ac_mngrs_data'] = $this->member_mod->get_data('', '', '', array('account_type' => 'ACCOUNTMANAGER'));
        } else {
            $data['tariff_options'] = $this->utils_model->get_tariffs($logged_user_type, 'CUSTOMER', $logged_account_id);
            $option_param = array('tariff' => true);
            $data['logged_account_result'] = $this->member_mod->get_account_by_key('account_id', $logged_account_id, $option_param);
        }

        $this->load->view('basic/header', $data);
        $this->load->view('reseller/reseller_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function srcNu($account_id = '', $reseller_type = 'reseller') {
        $account_id = param_decrypt($account_id);
        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('reseller', 'view') && !check_account_permission('reseller', 'edit'))
            show_404('403');
        $page_name = "{$reseller_type}_srcNu";
        $data['page_name'] = $page_name;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $data['reseller_type'] = $reseller_type;

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
//                $post_array['id'] = $_POST['id'];
                $post_array['account_id'] = $_POST['account_id'];

                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }

                $result = $this->reseller_mod->update_callerid($post_array);
                //print_r($post_array); print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Source Number Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url($reseller_type . 's') . '/srcNu/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    }
                    else {
                        redirect(base_url($reseller_type . 's'), 'location', '301');
                    }

                    redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('callerid' => true);
            $resellers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);

            if (isset($resellers_data_temp['result']))
                $resellers_data = current($resellers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $resellers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('reseller/srcNu', $data);
        $this->load->view('basic/footer', $data);
    }

    public function srcNuIN($account_id = '', $reseller_type = 'reseller') {
        $account_id = param_decrypt($account_id);
        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('reseller', 'view') && !check_account_permission('reseller', 'edit'))
            show_404('403');
        $page_name = "{$reseller_type}_incoming_editSRCNo";
        $data['page_name'] = $page_name;
        $data['reseller_type'] = $reseller_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['account_id'] = $_POST['account_id'];
                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }

                $result = $this->reseller_mod->update_incoming_callerid($post_array);
//                print_r($post_array); print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'DID Calls Source Number Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url($reseller_type . 's') . '/srcNuIN/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    }
                    else {
                        redirect(base_url($reseller_type . 's'), 'location', '301');
                    }

                    redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('callerid_incoming' => true);
            $resellers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);

            if (isset($resellers_data_temp['result']))
                $resellers_data = current($resellers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $resellers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('reseller/srcNuIN', $data);
        $this->load->view('basic/footer', $data);
    }

    public function dstRules($account_id = '', $reseller_type = 'reseller') {
        $account_id = param_decrypt($account_id);
        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('reseller', 'view') && !check_account_permission('reseller', 'edit'))
            show_404('403');
        $page_name = "{$reseller_type}_dstRules";
        $data['page_name'] = $page_name;
        $data['reseller_type'] = $reseller_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['account_id'] = $_POST['account_id'];
                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }
                $result = $this->reseller_mod->update_translation_rules($post_array);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Destination Number Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(base_url($reseller_type . 's') . '/dstRules/' . param_encrypt($account_id), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                        }
                    } else {
                        redirect(base_url($reseller_type . 's'), 'location', '301');
                    }
                    redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (strlen($account_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('translation_rules' => true);
            $resellers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);

            if (isset($resellers_data_temp['result']))
                $resellers_data = current($resellers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $resellers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('reseller/dstRules', $data);
        $this->load->view('basic/footer', $data);
    }

    public function dstRulesIN($account_id = '', $reseller_type = 'reseller') {
        $account_id = param_decrypt($account_id);
        if (strlen($account_id) < 1) {
            show_404();
        }
        if (!check_account_permission('reseller', 'view') && !check_account_permission('reseller', 'edit')) {
            show_404('403');
        }
        $page_name = "{$reseller_type}_dstRulesIN";
        $data['page_name'] = $page_name;
        $data['reseller_type'] = $reseller_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['account_id'] = $_POST['account_id'];

                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }

                $result = $this->reseller_mod->update_translation_rules_incoming($post_array);
                //print_r($post_array); print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Incoming Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url($reseller_type . 's') . '/dstRulesIN/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    }
                    else {
                        redirect(base_url($reseller_type . 's'), 'location', '301');
                    }

                    redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (strlen($account_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            ;
            $option_param = array('translation_rules_incoming' => true);
            $resellers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);

            if (isset($resellers_data_temp['result']))
                $resellers_data = current($resellers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        /*         * **** pagination code ends  here ********* */
        $data['data'] = $resellers_data;


        $this->load->view('basic/header', $data);
        $this->load->view('reseller/dstRulesIN', $data);
        $this->load->view('basic/footer', $data);
    }

    public function addDP($account_id = '', $reseller_type = 'reseller') {

        $account_id = param_decrypt($account_id);
        if (strlen($account_id) < 1) {
            show_404();
        }
        if (!check_account_permission('reseller', 'edit')) {
            show_404('403');
        }

        $page_name = "{$reseller_type}_addDP";
        $data['page_name'] = $page_name;
        $data['reseller_type'] = $reseller_type;

        $this->load->model('route_mod');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];

            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('dialplan_id', 'Dialplan', 'trim|required');
            $this->form_validation->set_rules('maching_string', 'Routing Pattern', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->reseller_mod->add_dialplan($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Dialing Plan Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url($reseller_type . 's') . '/addDP/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    }
                    else {
                        redirect(base_url($reseller_type . 's'), 'location', '301');
                    }

                    redirect(base_url($reseller_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (strlen($account_id) > 0) {
            $account_id = $account_id;
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array();
            $resellers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($resellers_data_temp['result'])) {
                $resellers_data = current($resellers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        /*         * **** pagination code ends  here ********* */
        $data['data'] = $resellers_data;

        if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC'))) {

            $route_data = $this->route_mod->get_data('dialplan_name', '', '', array());
            $data['route_data'] = $route_data['result'];
        } else {
            $logged_account_id = get_logged_account_id();
            $option_param = array('dialplan' => true);
            $logged_account_result = $this->member_mod->get_account_by_key('account_id', $logged_account_id, $option_param);
            if (count($logged_account_result['dialplan']) > 0) {
                $data['route_data'] = $logged_account_result['dialplan'];
            } else
                $data['route_data'] = array();
        }

        $this->load->view('basic/header', $data);
        $this->load->view('reseller/addDP', $data);
        $this->load->view('basic/footer', $data);
    }

}
