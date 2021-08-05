<?php


/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Customers extends MY_Controller {

    function __construct() {
        parent::__construct();

        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('customer_mod');
        if (!check_is_loggedin())
            redirect(site_url(), 'refresh');
        $this->output->enable_profiler(ENABLE_PROFILE);

        $this->logged_user_type = get_logged_user_type();
        $this->logged_user_id = get_logged_user_id();
        $this->logged_account_id = get_logged_account_id();
    }

    /* for enduser */

    public function index($arg1 = '', $format = '') {
        $page_name = "customer_index";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('customer', 'view'))
            show_404('403');
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('customer', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(site_url() . 'customers', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->customer_mod->delete($delete_param_array);
                if ($result === true) {
                    $suc_msgs = count($delete_id_array) . ' User';
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
                $err_msgs = 'Select customers to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
            redirect(site_url() . 'customers', 'location', '301');
        }


        $search_parameters = array('company_name', 'account_id', 'status_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {// coming from search button
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }


        $search_data = array(
            'company_name' => $_SESSION[$search_session_key]['company_name'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'status_id' => $_SESSION[$search_session_key]['status_id'],
            'ipaddress' => $_SESSION[$search_session_key]['ipaddress'],
            'sip_username' => $_SESSION[$search_session_key]['sip_username'],
        );
        if (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
            $search_data['parent_account_id'] = $this->logged_account_id;
        } else {
            $search_data['parent_account_id'] = '';
        }
        ///

        $order_by = '';
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            $format = param_decrypt($format);
            $option_param = array('tariff' => true);
            $customers_data = $this->customer_mod->get_data($order_by, '', '', $search_data, $option_param);
            $search_array = array();
            if ($_SESSION['search_customers_data']['s_account_id'] != '')
                $search_array['Account ID'] = $_SESSION['search_customers_data']['s_account_id'];
            if ($_SESSION['search_customers_data']['s_name'] != '')
                $search_array['Name'] = $_SESSION['search_customers_data']['s_name'];
            if ($_SESSION['search_customers_data']['s_company_name'] != '')
                $search_array['Company Name'] = $_SESSION['search_customers_data']['s_company_name'];
            if ($_SESSION['search_customers_data']['s_username'] != '')
                $search_array['Username'] = $_SESSION['search_customers_data']['s_username'];
            if ($_SESSION['search_customers_data']['s_status'] != '')
                $search_array['Status'] = $_SESSION['search_customers_data']['s_status'] == 1 ? 'Active' : 'Inactive';
            if ($_SESSION['search_customers_data']['s_ipaddress'] != '')
                $search_array['IP address'] = $_SESSION['search_customers_data']['s_ipaddress'];
            if ($_SESSION['search_customers_data']['s_sip_username'] != '')
                $search_array['SIP Username'] = $_SESSION['search_customers_data']['s_sip_username'];
            $export_header = array('Account ID', 'Name', 'Tariff-plan', 'CC', 'CPS', 'Status');
            if (count($customers_data['result']) > 0) {
                foreach ($customers_data['result'] as $customers_data_temp) {
                    $ex_status = $customers_data_temp['account_status'] == 1 ? 'Active' : 'Inactive';
                    $ex_tariff_name = isset($customers_data_temp['tariff']['tariff_name']) ? $customers_data_temp['tariff']['tariff_name'] : '';
                    $export_data[] = array($customers_data_temp['account_id'], $customers_data_temp['name'], $ex_tariff_name, $customers_data_temp['account_cc'], $customers_data_temp['account_cps'], $ex_status);
                }
            } else {
                $export_data = array('');
            }
            $file_name = 'Endusers';

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        if ($is_file_downloaded === false) {

            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $option_param = array('tariff' => true, 'balance' => true, 'currency' => true);
            $customers_data = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            $total_count = $this->utils_model->get_data_total_count($this->customer_mod->select_sql);
            $data['pagination'] = setup_pagination_option($total_count, 'customers/index', $per_page, $pagination_uri_segment, $this->pagination);

            $data['data'] = $customers_data;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;
            $_SESSION['previous_url'] = current_url();
            $this->load->view('basic/header', $data);
            $this->load->view('customer/customers', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function add() {
        $page_name = "customer_add";
        $data['page_name'] = $page_name;
        if (!check_account_permission('customer', 'add'))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            if ($_POST['vat_flag'] == 'NONE')
                $_POST['tax_type'] = 'exclusive';
            $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[6]');
            $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');
            $this->form_validation->set_rules('currency_id', 'Currency', 'trim|required');
            $this->form_validation->set_rules('account_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('dp', 'DP', 'trim|required');
            $this->form_validation->set_rules('credit_limit', 'Initial Credit Limit', 'trim|required');
            $this->form_validation->set_rules('tax_type', 'Tax Type', 'trim|required');
            $this->form_validation->set_rules('tax1', 'Tax 1', 'trim|required');
            $this->form_validation->set_rules('tax2', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('tax3', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('billing_cycle', 'Billing Cycle', 'trim|required');
            $this->form_validation->set_rules('tax_number', 'Tax Number', 'trim');
            $this->form_validation->set_rules('vat_flag', 'VAT Flag', 'trim');
            $this->form_validation->set_rules('account_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('account_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('media_transcoding', 'Transcoding', 'trim|required');
            $this->form_validation->set_rules('media_rtpproxy', 'With-media', 'trim|required');
            $this->form_validation->set_rules('codecs[]', 'Codecs', 'trim|required');

            $this->form_validation->set_rules('force_dst_src_cli_prefix', 'Change CLI Based On DST Prefix', 'trim|required');
            $this->form_validation->set_rules('codecs_force', 'Codec Checking', 'trim|required');
            $this->form_validation->set_rules('max_callduration', 'Max Call Duration', 'trim|required');

            $this->form_validation->set_rules('company_name', 'Company', 'trim');
            $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim|required');
            $this->form_validation->set_rules('address', 'address', 'trim');
            $this->form_validation->set_rules('phone', 'phone', 'trim');
            $this->form_validation->set_rules('country_id', 'Country', 'trim');
            $this->form_validation->set_rules('state_code_id', 'State', 'trim');
            $this->form_validation->set_rules('pincode', 'Pin-Code', 'trim');

            $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[6]');
            $this->form_validation->set_rules('secret', 'Password', 'trim|required|min_length[8]');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['account_codecs'] = implode(',', $_POST['codecs']);

                if (check_logged_user_group(array('RESELLER'))) {
                    $_POST['parent_account_id'] = $this->logged_account_id;
                } else {
                    $_POST['parent_account_id'] = '';
                }

                if ($_POST['media_rtpproxy'] == 0) {
                    $_POST['media_transcoding'] = '0';
                }
                $_POST['created_by'] = get_logged_account_id();
                $result = $this->customer_mod->add($_POST);
                //echo '<pre>';print_r($_POST);var_dump($result);die;
                if ($result === true) {
                    $account_id = $this->customer_mod->account_id;
                    $this->session->set_flashdata('suc_msgs', 'Customer Added Successfully.');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save')
                        redirect(site_url() . 'customers/edit/' . param_encrypt($account_id), 'location', '301');
                    else {
                        if (isset($_SESSION['return_url']) && $_SESSION['return_url'] == 'crs')
                            $return_url = 'crs';
                        else
                            $return_url = 'customers';
                        redirect(site_url($return_url), 'location', '301');
                    }

                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        $data['country_options'] = $this->utils_model->get_countries();
        $data['currency_options'] = $this->utils_model->get_currencies();
        $data['state_options'] = $this->utils_model->get_states();

        if (strpos($_SERVER['HTTP_REFERER'], 'customers/add') === false) {
            if (strpos($_SERVER['HTTP_REFERER'], 'crs') !== false)
                $_SESSION['return_url'] = 'crs';
            else
                $_SESSION['return_url'] = 'customers';
        }


        $this->load->view('basic/header', $data);
        $this->load->view('customer/customer_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function edit($id = -1,$active_tab= 1) {
        $customer_type = 'customer';
        $page_name = "customers_edit";
        $data['page_name'] = $page_name;
        $data['active_tab']	= $active_tab;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $account_id = param_decrypt($id);
        if (strlen($account_id) == 0)
            show_404();

        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('customer', 'delete')) {
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
                case 'account_ips_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->customer_mod->delete_ip($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'IP User Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    redirect(current_url(), 'location', '301');
                    break;
                case 'account_sip_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->customer_mod->delete_sip($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'SIP User Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    redirect(current_url(), 'location', '301');
                    break;
                case 'account_dialplan_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->customer_mod->delete_dialplan($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'Dialplan Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    redirect(current_url(), 'location', '301');
                    break;
                case 'account_service_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $logged_account_type = get_logged_account_type();
                    $logged_account_id = get_logged_account_id();
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->customer_mod->delete_service($account_id, $delete_param_array, $logged_account_type, $logged_account_id);
                    if ($result === true) {
                        $suc_msgs = $this->customer_mod->message;
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    redirect(current_url(), 'location', '301');
                    break;
                case 'account_bundle_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->customer_mod->delete_bundle($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'Bundle Deleted Successfully';
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

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveLoginData') {
$data['active_tab']	= $_POST['tab'];
            $this->form_validation->set_rules('user_id', 'User ID', 'trim');
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required');
            $this->form_validation->set_rules('secret', 'Password', 'trim|required|required');
            $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post = $_POST;
                $post['user_type'] = 'CUSTOMERADMIN';
                $post['created_by'] = $this->logged_user_id;
                $result = $this->member_mod->modify_account_login($post);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Login Details Updated Successfully');
                      redirect(site_url('customers/edit/'.param_encrypt($account_id).'/'.$data['active_tab']), 'location', '301');
                 exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $data['active_tab']	= $_POST['tab'];
            $account_id = $_POST['key'] = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('account_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('dp', 'DP', 'trim|required');
            $this->form_validation->set_rules('tax_type', 'Tax Type', 'trim|required');
            $this->form_validation->set_rules('tax1', 'Tax 1', 'trim|required');
            $this->form_validation->set_rules('tax2', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('tax3', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('vat_flag', 'VAT Flag', 'trim');
            $this->form_validation->set_rules('status_id', 'Status', 'trim|required');
            $this->form_validation->set_rules('media_transcoding', 'Transcoding', 'trim|required');
            $this->form_validation->set_rules('media_rtpproxy', 'With-media', 'trim|required');
            $this->form_validation->set_rules('tax_number', 'Tax Number', 'trim');
            $this->form_validation->set_rules('force_dst_src_cli_prefix', 'Change CLI Based On DST Prefix', 'trim|required');
            $this->form_validation->set_rules('codecs_force', 'Codec Checking', 'trim|required');

            $this->form_validation->set_rules('max_callduration', 'Max Call Duration', 'trim|required');
            ///
            if (trim($_POST['status_id']) == 0) {
                $this->form_validation->set_rules('status_id', 'Status', 'trim|required');
            } else {
                $this->form_validation->set_rules('status_id', 'Status', 'trim|required');
            }

            if (trim($_POST['view_ipdevices']) == 0) {
                $this->form_validation->set_rules('view_ipdevices', 'Status', 'trim|required');
            } else {
                $this->form_validation->set_rules('view_ipdevices', 'Status', 'trim|required');
            }

            if (trim($_POST['view_sipdevice']) == 0) {
                $this->form_validation->set_rules('view_sipdevice', 'Status', 'trim|required');
            } else {
                $this->form_validation->set_rules('view_sipdevice', 'Status', 'trim|required');
            }

            if (trim($_POST['view_src_out']) == 0) {
                $this->form_validation->set_rules('view_src_out', 'Status', 'trim|required');
            } else {
                $this->form_validation->set_rules('view_src_out', 'Status', 'trim|required');
            }

            if (trim($_POST['view_dst_out']) == 0) {
                $this->form_validation->set_rules('view_dst_out', 'Status', 'trim|required');
            } else {
                $this->form_validation->set_rules('view_dst_out', 'Status', 'trim|required');
            }

            if (trim($_POST['view_src_did']) == 0) {
                $this->form_validation->set_rules('view_src_did', 'Status', 'trim|required');
            } else {
                $this->form_validation->set_rules('view_src_did', 'Status', 'trim|required');
            }

            if (trim($_POST['view_dst_did']) == 0) {
                $this->form_validation->set_rules('view_dst_did', 'Status', 'trim|required');
            } else {
                $this->form_validation->set_rules('view_dst_did', 'Status', 'trim|required');
            }
//

            if (!isset($_POST['state_code_id']) || $_POST['country_id'] != 100)
                $_POST['state_code_id'] = '0';


            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['account_codecs'] = implode(',', $_POST['codecs']);
                if ($_POST['media_rtpproxy'] == 0) {
                    $_POST['media_transcoding'] = '0';
                }
                $_POST['logged_account_id'] = get_logged_account_id();
                $result = $this->customer_mod->update($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Customer Account Updated Successfully');
                      redirect(site_url('customers/edit/'.param_encrypt($account_id).'/'.$data['active_tab']), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveAddressData') {
$data['active_tab']	= $_POST['tab'];
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('company_name', 'Company Name', 'trim|required');
            $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim');
            $this->form_validation->set_rules('address', 'Address', 'trim');
            $this->form_validation->set_rules('state_code_id', 'State', 'trim');
            $this->form_validation->set_rules('country_id', 'Country', 'trim');
            $this->form_validation->set_rules('pincode', 'Pincode', 'trim');
            $this->form_validation->set_rules('account_manager', 'Account Manager', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->update($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Address Details Updated Successfully');
                        redirect(site_url('customers/edit/'.param_encrypt($account_id).'/'.$data['active_tab']), 'location', '301');
                   exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } {
            $search_data = array('account_id' => $account_id);
            if (check_logged_user_group(array('RESELLER'))) {
                $search_data['parent_account_id'] = $this->logged_account_id;
            } else {
                $search_data['parent_account_id'] = '';
            }

            $option_param = array();
            /* array('ip' => true, 'callerid' => true, 'sipuser' => true, 'tariff' => true, 'user' => false, 'prefix' => false, 'dialplan' => true, 'translation_rules' => true, 'callerid_incoming' => true, 'translation_rules_incoming' => true, 'bundle_package_group_by' => true); */
            $customers_data_temp = $this->customer_mod->get_data('', 1, 0, $search_data, $option_param);

            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        }

        $data['data'] = $customers_data;


        $data['country_options'] = $this->utils_model->get_countries();
        $data['currency_options'] = $this->utils_model->get_currencies();
        $data['state_options'] = $this->utils_model->get_states();
		$data['language_options'] = $this->utils_model->get_languages();

        $data['account_manager_options'] = $this->customer_mod->get_user_by_account_manager();
        $data['account_manager_data'] = $this->customer_mod->get_account_manager($account_id);

        //////////
        $user_search_data['account_id'] = $account_id;
        $user_search_data['user_type'] = 'CUSTOMERADMIN';

        $users_data_temp = $this->member_mod->get_data('', 1, 0, $user_search_data);
        if (isset($users_data_temp['result']))
            $users_data = current($users_data_temp['result']);
        else
            $users_data = array();
        $data['user_data'] = $users_data;
        //////////////

        $this->load->view('basic/header', $data);
        $this->load->view('customer/customer_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function balance_check($str) {//die("DDDD");
        $this->load->model('payment_mod');
        $payment_result = $this->payment_mod->get_balance($_POST['key']);
        if ($payment_result['result']['outstanding_balance'] != 0) {
            $this->form_validation->set_message('balance_check', 'To be closed, account balance must be 0');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function sipAdd($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        if (isset($id2)) {
            $id = param_decrypt($id2);
        }
        if (strlen($account_id) < 1) {
            show_404();
        }
        if (!check_account_permission('customer', 'edit')) {
            show_404('403');
        }

        $page_name = "{$customer_type}_sipAdd";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim');
            $this->form_validation->set_rules('sip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('sip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('status', 'Status', 'trim|required');
            $this->form_validation->set_rules('voicemail', 'Voicemail Option', 'trim|required');
            $this->form_validation->set_rules('voicemail_email', 'Voicemail EmailList', 'trim');
            $this->form_validation->set_rules('extension_no', 'Extension', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->add_sip($_POST);
                $id = $this->customer_mod->last_customer_sip_id;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User SIP Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(site_url($customer_type . 's') . '/sipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                        }
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }
                    redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
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
            $option_param = array();
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result'])) {
                $customers_data = current($customers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }

        $data['data'] = $customers_data;
        $data['account_id'] = $account_id;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/sipAdd', $data);
        $this->load->view('basic/footer', $data);
    }

    public function sipEdit($id1 = -1, $id2 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        $id = param_decrypt($id2);
        if (strlen($account_id) < 1 and $id < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_sipEdit";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $id = $_POST['id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'User ID', 'trim|required');
            $this->form_validation->set_rules('id', 'SIP ID', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim');
            $this->form_validation->set_rules('voicemail', 'Voicemail Option', 'trim|required');
            $this->form_validation->set_rules('voicemail_email', 'Voicemail', 'trim');
            $this->form_validation->set_rules('extension_no', 'Extension', 'trim|required');
            $this->form_validation->set_rules('sip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('sip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('status', 'Status', 'trim|required');


            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->update_sip($_POST);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User SIP Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(site_url($customer_type . 's') . '/sipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }
                    redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id) . '/' . param_encrypt($account_sip_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (strlen($account_id) > 1 and $id > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('sipuser' => true, 'customer_sip_id' => $id);
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/sipEdit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function ipAdd($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        if (isset($id2))
            $id = param_decrypt($id2);

        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_ipAdd";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim|required');
            $this->form_validation->set_rules('dialprefix', 'Dial Prefix', 'trim|required');
            $this->form_validation->set_rules('ip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('ip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('ip_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('description', 'description', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->add_ip($_POST);
                $id = $this->customer_mod->last_customer_ip_id;

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User IP Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(site_url($customer_type . 's') . '/ipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                        }
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }
                    redirect(site_url($customer_type . 's'), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 1) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array();
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result'])) {
                $customers_data = current($customers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $data['account_id'] = $account_id;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/ipAdd', $data);
        $this->load->view('basic/footer', $data);
    }

    public function ipEdit($id1 = -1, $id2 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        $id = param_decrypt($id2);
        if (strlen($account_id) < 1 and $id < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_ipEdit";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;


        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $id = $_POST['id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('id', 'IP ID', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim|required');
            $this->form_validation->set_rules('dialprefix', 'Dial Prefix', 'trim|required');
            $this->form_validation->set_rules('ip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('ip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('ip_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('description', 'description', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->update_ip($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User IP Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(site_url($customer_type . 's') . '/ipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }

                    redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (strlen($account_id) > 1 and $id > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('ip' => true, 'account_ip_id' => $id);
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/ipEdit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editSRCNo($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_editSRCNo";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');
            $this->form_validation->set_rules('dst_src_cli_rules', 'Destination prefix based CLI transalation Rules', 'trim');
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
                if ($_POST['dst_src_cli_rules'] != '') {
                    $post_array['dst_src_cli_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['dst_src_cli_rules']);
                }

                $result = $this->customer_mod->update_callerid($post_array);
//                print_r($post_array); print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Caller ID Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(site_url($customer_type . 's') . '/editSRCNo/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }

                    redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 1) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('callerid' => true);
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/editSRCNo', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editINSRCNo($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        if (strlen($account_id) < 1)
            show_404();
        // if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'cliedit'))
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'cliedit'))
            show_404('403');

        $page_name = "{$customer_type}_editINSRCNo";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'User ID', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['account_id'] = $_POST['account_id'];
                $post_array['account_id'] = $_POST['account_id'];

                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }

                $result = $this->customer_mod->update_callerid_incoming($post_array);
                //print_r($post_array); print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Incoming Caller ID Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(site_url($customer_type . 's') . '/editINSRCNo/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }

                    redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 1) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('callerid_incoming' => true);
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);

            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/editINSRCNo', $data);
        $this->load->view('basic/footer', $data);
    }

    public function DSTRule($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_DSTRule";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

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

                $result = $this->customer_mod->update_translation_rules($post_array);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(site_url($customer_type . 's') . '/DSTRule/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }

                    redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
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
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/DSTRule', $data);
        $this->load->view('basic/footer', $data);
    }

    public function DIDNumberTRule($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_DIDNumberTRule";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'User ID', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['account_id'] = $_POST['account_id'];
                $post_array['account_id'] = $_POST['account_id'];

                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }

                $result = $this->customer_mod->update_translation_rules_incoming($post_array);
                //print_r($post_array); print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Incoming Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(site_url($customer_type . 's') . '/DIDNumberTRule/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }

                    redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 1) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('translation_rules_incoming' => true);
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/DIDNumberTRule', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editD($id1 = -1, $id2 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        $id = param_decrypt($id2);
        if (strlen($account_id) < 1 and $id < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_editD";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;
        $this->load->model('route_mod');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $id = $_POST['id'];
            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('id', 'Dialplan ID', 'trim|required');
            $this->form_validation->set_rules('dialplan_id', 'Dialplan Code', 'trim|required');
            $this->form_validation->set_rules('maching_string', 'Routing Pattern', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->update_dialplan($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Dialing Plan Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(site_url($customer_type . 's') . '/editD/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                        }
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }
                    redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 0 and $id > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('dialplan' => true, 'id' => $id);
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);

            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        /*         * **** pagination code ends  here ********* */
        $data['data'] = $customers_data;

        if (check_logged_user_group(array(ADMIN_ACCOUNT_ID))) {

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
        $this->load->view('customer/editD', $data);
        $this->load->view('basic/footer', $data);
    }

    public function addD($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);

        if (strlen($account_id) < 1) {
            show_404();
        }
        if (!check_account_permission('customer', 'edit')) {
            show_404('403');
        }
        $page_name = "{$customer_type}_addD";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $this->load->model('route_mod');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];

            $this->form_validation->set_rules('account_id', 'User ID', 'trim|required');
            $this->form_validation->set_rules('dialplan_id', 'Dialplan Code', 'trim|required');
            $this->form_validation->set_rules('maching_string', 'Routing Pattern', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->add_dialplan($_POST);
                $id = $this->customer_mod->last_account_dialplan_id;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Dialing Plan Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(site_url($customer_type . 's') . '/editD/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(site_url($customer_type . 's'), 'location', '301');
                    }

                    redirect(site_url($customer_type . 's') . '/edit/' . param_encrypt($account_id), 'location', '301');
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
            $option_param = array();
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        if (check_logged_user_group(array(ADMIN_ACCOUNT_ID))) {
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
        $this->load->view('customer/addD', $data);
        $this->load->view('basic/footer', $data);
    }

    public function my_cli_lookup_edit($lookup_id = -1) /* Edit End User cli lookup */ {
        $this->load->model('did_mod');
        $this->load->model('customer_mod');
        if ($lookup_id == -1)
            show_404();

        $page_name = "my_cli_lookup_edit";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_id = get_logged_account_id();

        if (isset($_POST['action']) && $_POST['action'] == 'OkUpdateCli') {
            $this->form_validation->set_rules('group_name', 'Group name', 'trim|required');
            $this->form_validation->set_rules('prefixes', 'Prefixes', 'trim|required');
            $this->form_validation->set_rules('cli[]', 'CLI', 'trim|required');

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $prefixes_array = preg_split('/\r\n|\r|\n|,/', $_POST['prefixes']);
                foreach ($prefixes_array as $key => $prefix) {
                    if ($prefix == '')
                        unset($prefixes_array[$key]);
                }

                $data_array = array(
                    'account_id' => $account_id,
                    'lookup_id' => $_POST['lookup_id'],
                    'group_name' => $_POST['group_name'],
                    'prefixes' => $prefixes_array,
                    'cli' => $_POST['cli']
                );

                $result = $this->did_mod->update_cli($data_array);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'CLI Updated Successfully');
                    $action = trim($_POST['button_action']);
                    if ($action == 'save')
                        redirect(site_url() . 'my_cli_lookup/edit/' . param_encrypt($_POST['lookup_id']), 'location', '301');
                    else
                        redirect(site_url() . 'my_cli_lookup', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        $lookup_id = param_decrypt($lookup_id);

        $search_data = array('lookup_id' => $lookup_id);
        $assigned_cli_data = $this->did_mod->get_cli_data('', '', '', $search_data);

        if (isset($assigned_cli_data['result']))
            $assigned_cli_data = current($assigned_cli_data['result']);
        else {
            show_404();
        }

        $data['account_id'] = $account_id;
        $data['assigned_cli_data'] = $assigned_cli_data;

        $data['country_options'] = $this->did_mod->get_cli_countries();
        $data['cli_group_options'] = $this->did_mod->get_cli_groups();
        $data['available_cli_data'] = $this->did_mod->get_available_cli($account_id);


        $this->load->view('basic/header', $data);
        $this->load->view('customer/my_cli_lookup_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function test($id = -1, $customer_type = 'customer') /* Edit End User cli lookup */ {
        $this->load->model('did_mod');
        if ($id == -1)
            show_404();

        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_edit";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_id = param_decrypt($id);

        $order_by = '';
        $per_page = 1;
        $segment = 0;
        $search_data = array('account_id' => $account_id);

        $option_param = array();
        $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);

        if (isset($customers_data_temp['result']))
            $customers_data = current($customers_data_temp['result']);
        else {
            show_404();
        }

        $data['data'] = $customers_data;
        $data['account_id'] = $account_id;
        $data['country_options'] = $this->did_mod->get_cli_countries($account_id);
        $data['cli_group_options'] = $this->did_mod->test_get_cli_groups();
        $data['available_cli_data'] = $this->did_mod->get_available_cli($account_id);
        $this->load->view('basic/header', $data);
        $this->load->view('customer/test', $data);
        $this->load->view('basic/footer', $data);
    }

    function get_prefixes_by_group() {
        $this->load->model('did_mod');


        $country_abbr = trim($_POST['country']);
        $group_name = trim($_POST['group_name']);


        $result = $this->did_mod->get_prefixes_by_group($country_abbr, $group_name);

        if ($result['status'] = 'success')
            $return = $result['result'];
        else
            $return = 'error';

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

    public function addBundle($id1 = -1) {
        $account_id = param_decrypt($id1);

        if (strlen($account_id) < 1) {
            show_404();
        }
        if (!check_account_permission('customer', 'edit')) {
            show_404('403');
        }
        $page_name = "customer_addBundle";
        $data['page_name'] = $page_name;
        //$data['customer_type'] = $customer_type;

        $this->load->model('Bundle_mod');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];

            $this->form_validation->set_rules('account_id', 'Customer ID', 'trim|required');
            $this->form_validation->set_rules('bundle_package_id', 'Bundle', 'trim|required');
            $this->form_validation->set_rules('bundle_package_desc', 'Description', 'trim');
            $this->form_validation->set_rules('no_of_package', 'Number of Packahe', 'trim|required|numeric|greater_than[0]|less_than[1000]');


            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $time = 1;
                while ($time <= $_POST['no_of_package']) {
                    $result = $this->customer_mod->add_bundle($_POST);
                    $time+=1;
                }
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Bundle & Package Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(site_url('customers') . '/addBundle/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(site_url('customers') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(site_url('customers') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    }
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }


        $order_by = '';
        $per_page = 1;
        $segment = 0;
        $search_data = array('account_id' => $account_id);
        $option_param = array();
        $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
        if (isset($customers_data_temp['result']))
            $customers_data = current($customers_data_temp['result']);
        else {
            show_404();
        }


        $data['data'] = $customers_data;


        $search_data = array('bundle_package_currency_id' => $customers_data['currency_id']);
        $created_by = get_logged_account_id();

        $response = $this->Bundle_mod->get_unassigned_data($account_id, $created_by, $search_data);
        $data['bundle_data'] = $response;

        $this->load->view('basic/header', $data);
        $this->load->view('customer/bundleAdd', $data);
        $this->load->view('basic/footer', $data);
    }

    function cState($id = -1) {
        $page_name = "cState";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');
        $this->load->model('customer_mod');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (check_logged_user_group(array('CUSTOMER'))) {
            $account_id = get_logged_account_id();
        } elseif ($id != -1) {
            $id_decrypted = param_decrypt($id);
            $search_data = array();
            if (check_logged_user_group(array('RESELLER')))
                $search_data['parent_account_id'] = get_logged_account_id();
            elseif (check_logged_user_group(array(ADMIN_ACCOUNT_ID))) {
                $search_data['parent_account_id'] = '';
            }

            if (is_numeric($id_decrypted))
                $search_data['customer_id'] = $id_decrypted;
            else
                $search_data['account_id'] = $id_decrypted;

            $endusers_data_temp = $this->customer_mod->get_data('', 1, 0, $search_data);
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

        $report_data = $this->report_mod->customer_call_sipcode_review($account_id, $date_from, $date_to, $src_ipaddress, $prefix_name);
        $data['report_data'] = $report_data;
        $data['account_id'] = $account_id;
        $data['time_range'] = $time_range;

        $data['src_ipaddress'] = $src_ipaddress;
        $data['prefix_name'] = $prefix_name;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/cState', $data);
        $this->load->view('basic/footer', $data);
    }

    function statement($id = -1, $arg1 = '', $format = '') {
        $page_name = "statement";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');
        $this->load->model('payment_mod');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();


        if (isset($_POST['search_action']) && isset($_POST['account_id']) && $_POST['account_id'] != '') {
            $account_id = trim($_POST['account_id']);
        } elseif ($id != -1) {
            $account_id = param_decrypt($id);
        } elseif (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
            $account_id = get_logged_account_id();
        }


        $search_parameters = array('invoice_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }
        if ($_SESSION[$search_session_key]['invoice_id'] == '')
            $_SESSION[$search_session_key]['invoice_id'] = '';

        $search_data = array(
            'invoice_id' => $_SESSION[$search_session_key]['invoice_id'],
        );

        $customer_result = $this->member_mod->get_account_by_key('account_id', $account_id);
        if (!$customer_result) {
            $data['statement_error_message'] = 'Account Not Found';
        }


        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            $invoice_data = $this->report_mod->invoice_list($account_id);
            $data['invoice_list'] = $invoice_data['result'];

            $format = param_decrypt($format);
            $report_data = $this->report_mod->sdr_statement($account_id, $search_data);

            $yearmonth = date('Ym');
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
            else
                $is_file_downloaded = true;
        }

        //================================export pdf end===========================      
        if ($is_file_downloaded === false) {
            if (isset($account_id) && $account_id != '') {
                $invoice_data = $this->report_mod->invoice_list($account_id);
                $data['invoice_list'] = $invoice_data['result'];

                $report_data = $this->report_mod->sdr_statement($account_id, $search_data);

                $data['customer_data'] = $customer_result;
                $data['sdr_terms'] = $this->utils_model->get_sdr_terms();
                $data['searched_account_id'] = $account_id;
                $data['data'] = $report_data;
            }
            $data['search_session_key'] = $search_session_key;
            $this->load->view('basic/header', $data);
            $this->load->view('reports/sdr_statement', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function myplan($account_id = '') {
        if (!check_logged_user_group(array('CUSTOMER'))) {
            show_404('403');
        }
        $account_id = param_decrypt($account_id);
        if (strlen($account_id) == 0) {
            $account_id = get_logged_account_id();
        }

        if (strlen($account_id) > 0) {
            $plugin_name = 'voip';
            $plugin_data = $this->customer_mod->get_plugin_data($plugin_name);
            if (isset($plugin_data)) {
                $order_by = '';
                $per_page = 1;
                $segment = 0;
                $search_data = array('account_id' => $account_id);
                if (check_logged_user_group('reseller'))
                    $search_data['parent_account_id'] = get_logged_account_id();

                $option_param = array('tariff' => true, 'user' => false, 'prefix' => false, 'bundle_package_group_by' => true, 'balance' => true, 'currency' => true, 'pbx' => true);
                $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);

                if (isset($customers_data_temp['result'])) {
                    $customers_data = current($customers_data_temp['result']);
                    $data['data'] = $customers_data;
                }
            }


            $plugin_name = 'billing';
            $plugin_data = $this->customer_mod->get_plugin_data($plugin_name);
            if (isset($plugin_data)) {
                $plan_data = $this->customer_mod->get_plan_data($account_id);
                if (isset($plan_data)) {
                    $data['plan_data'] = $plan_data;
                }
            }
        } else {
            show_404('403');
        }



        $this->load->view('basic/header', $data);
        $this->load->view('basic/myplan', $data);
        $this->load->view('basic/footer', $data);
    }

}
