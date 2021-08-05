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

class Resellers extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('reseller_mod');
        if (!check_is_loggedin())
            redirect(site_url(), 'refresh');
        if (get_logged_account_level() > 2)
            show_404('403');

        $this->logged_user_type = get_logged_user_type();
        $this->logged_user_id = get_logged_user_id();
        $this->logged_account_id = get_logged_account_id();
    }

    public function index($arg1 = '', $format = '') {
        redirect(site_url('crs'), 'refresh');

        $reseller_type = 'reseller';
        $page_name = "reseller_index";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('reseller', 'view'))
            show_404('404');

        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('reseller', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(site_url() . 'crs/resellers', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->reseller_mod->delete($delete_param_array);
                if ($result === true) {
                    $suc_msgs = count($delete_id_array) . 'Reseller ';
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
            redirect(site_url('crs/resellers'), 'location', '301');
        }


        $search_parameters = array('company_name', 'account_id', 'status_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }


        $search_data = array(
            'company_name' => $_SESSION[$search_session_key]['company_name'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'status_id' => $_SESSION[$search_session_key]['status_id'],
        );
        if (check_logged_user_group(array('RESELLER'))) {
            $search_data['parent_account_id'] = $this->logged_account_id;
        } else {
            $search_data['parent_account_id'] = '';
        }


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
            $file_name = ucfirst('resellers');
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
            $option_param = array('balance' => true, 'currency' => true);
            $resellers_data = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            $total_count = $this->utils_model->get_data_total_count($this->reseller_mod->select_sql);
            $data['pagination'] = setup_pagination_option($total_count, 'resellers/index', $per_page, $pagination_uri_segment, $this->pagination);

            $data['data'] = $resellers_data;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;
            $_SESSION['previous_url'] = current_url();
            $this->load->view('basic/header', $data);
            $this->load->view('reseller/resellers', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function add() {
        $page_name = "reseller_add";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('reseller', 'add'))
            show_404('403');


        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            if ($_POST['vat_flag'] == 'NONE')
                $_POST['tax_type'] = 'exclusive';
            $this->form_validation->set_rules('account_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('account_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('dp', 'DP', 'trim|required');
            $this->form_validation->set_rules('currency_id', 'Currency', 'trim|required');
            // $this->form_validation->set_rules('tariff_id', 'Tariff Plan', 'trim|required');
            $this->form_validation->set_rules('tax_type', 'Tax Type', 'trim|required');
            $this->form_validation->set_rules('tax1', 'Tax 1', 'trim|required');
            $this->form_validation->set_rules('tax2', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('tax3', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('status_id', 'Status', 'trim|required');

            $this->form_validation->set_rules('llr_check', 'LLR Check', 'trim|required');
            $this->form_validation->set_rules('media_transcoding', 'Transcoding', 'trim|required');
            $this->form_validation->set_rules('media_rtpproxy', 'With-media', 'trim|required');
//            $this->form_validation->set_rules('billing_type', 'Billing Type', 'trim|required');
            $this->form_validation->set_rules('agent', 'Account Manager', 'trim');
            $this->form_validation->set_rules('tax_number', 'Tax Number', 'trim');
            $this->form_validation->set_rules('vat_flag', 'VAT Flag', 'trim');
            // $this->form_validation->set_rules('name', 'Name', 'trim|required');
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
                if (check_logged_user_group(array('RESELLER'))) {
                    $_POST['parent_account_id'] = $this->logged_account_id;
                } else {
                    $_POST['parent_account_id'] = '';
                }
                if ($_POST['media_rtpproxy'] == 0) {
                    $_POST['media_transcoding'] = '0';
                }
                $result = $this->reseller_mod->add($_POST);
                //echo '<pre>';	print_r($_POST); 	var_dump($result);die;
                if ($result === true) {
                    $account_id = $this->reseller_mod->account_id;
                    $this->session->set_flashdata('suc_msgs', 'Resellers Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save')
                        redirect(site_url('crs/resellers') . '/edit/' . param_encrypt($account_id), 'location', '301');
                    else
                        redirect(site_url('crs/resellers'), 'location', '301');


                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        //$logged_user_type = get_logged_account_type();
        //  $logged_account_id = get_logged_account_id();
        $data['country_options'] = $this->utils_model->get_countries();
        $data['currency_options'] = $this->utils_model->get_currencies();
        $data['state_options'] = $this->utils_model->get_states();


        /* if(check_logged_user_group(array('RESELLER')))
          {
          $data['tariff_options'] = $this->utils_model->get_tariffs($logged_user_type, 'CUSTOMER', $logged_account_id);
          $option_param = array('tariff' => true);
          $data['logged_account_result'] = $this->member_mod->get_account_by_key('account_id', $logged_account_id, $option_param);
          }
          else{
          $data['tariff_options'] = $this->utils_model->get_tariffs($logged_user_type, 'CUSTOMER');
          $data['ac_mngrs_data'] = $this->member_mod->get_data('', '', '', array('account_type' => 'AGENT'));
          }
         */
        $this->load->view('basic/header', $data);
        $this->load->view('reseller/reseller_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function edit($id = -1, $active_tab = 1) {
        $reseller_type = 'reseller';
        $page_name = "reseller_edit";
        $data['page_name'] = $page_name;
        $data['active_tab'] = $active_tab;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('reseller', 'view') && !check_account_permission('reseller', 'edit'))
            show_404('403');



        $account_id = param_decrypt($id);
        if (strlen($account_id) == 0)
            show_404();
        if (!check_account_permission('reseller', 'view') && !check_account_permission('reseller', 'edit'))
            show_404('403');


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
                case 'account_bundle_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    // $logged_account_type = get_logged_account_type();
                    // $logged_account_id = get_logged_account_id();
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->reseller_mod->delete_bundle($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'Bundle Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    redirect(current_url(), 'location', '301');
                    //die("aaa");
                    break;
                default:
                    $this->session->set_flashdata('err_msgs', 'Parameter mismatch');
                    redirect(current_url(), 'location', '301');
            }
        }

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveLoginData') {
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('user_id', 'User ID', 'trim');
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required');
            $this->form_validation->set_rules('secret', 'Password', 'trim|required|required');
            $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post = $_POST;
                $post['user_type'] = 'RESELLERADMIN';
                $post['created_by'] = $this->logged_user_id;
                $result = $this->member_mod->modify_account_login($post);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Login Details Updated Successfully');
                    redirect(site_url('crs/resellers/edit/' . param_encrypt($account_id) . '/' . $data['active_tab']), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            if ($_POST['vat_flag'] == 'NONE')
                $_POST['tax_type'] = 'exclusive';
            $data['active_tab'] = $_POST['tab'];
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('account_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('dp', 'DP', 'trim|required');
            // $this->form_validation->set_rules('tariff_id', 'Tariff Plan', 'trim|required');
            $this->form_validation->set_rules('tax_type', 'Tax Type', 'trim|required');
            $this->form_validation->set_rules('tax1', 'Tax 1', 'trim|required');
            $this->form_validation->set_rules('tax2', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('tax3', 'Tax 3', 'trim|required');
            $this->form_validation->set_rules('vat_flag', 'VAT Flag', 'trim');
            $this->form_validation->set_rules('status_id', 'Status', 'trim|required');
            $this->form_validation->set_rules('llr_check', 'LLR Check', 'trim|required');
            $this->form_validation->set_rules('media_transcoding', 'Transcoding', 'trim|required');
            $this->form_validation->set_rules('media_rtpproxy', 'With-media', 'trim|required');
            //$this->form_validation->set_rules('billing_type', 'Billing Type', 'trim|required');
            // $this->form_validation->set_rules('agent', 'Account Manager', 'trim');
            $this->form_validation->set_rules('tax_number', 'Tax Number', 'trim');
            // $this->form_validation->set_rules('name', 'Name', 'trim|required');
            // $this->form_validation->set_rules('company_name', 'Company', 'trim');
            // $this->form_validation->set_rules('emailaddress', 'Email Address', 'trim|required');
            // $this->form_validation->set_rules('address', 'address', 'trim');
            // $this->form_validation->set_rules('phone', 'phone', 'trim');
            // $this->form_validation->set_rules('country_id', 'Country', 'trim');
            // $this->form_validation->set_rules('state_code_id', 'State', 'trim');
            //$this->form_validation->set_rules('pincode', 'Pin-Code', 'trim');

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
                //echo '<pre>';	print_r($_POST); var_dump($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Reseller Updated Successfully');
                    redirect(site_url('crs/resellers/edit/' . param_encrypt($account_id) . '/' . $data['active_tab']), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveAddressData') {

            $data['active_tab'] = $_POST['tab'];
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
                $result = $this->reseller_mod->update($_POST);
                //var_dump($result );ddd($_POST);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Address Details Updated Successfully');
                    redirect(site_url('crs/resellers/edit/' . param_encrypt($account_id) . '/' . $data['active_tab']), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        $search_data = array('account_id' => $account_id);
        if (check_logged_user_group(array('RESELLER'))) {
            $search_data['parent_account_id'] = $this->logged_account_id;
        } else {
            $search_data['parent_account_id'] = '';
        }



        $option_param = array('callerid' => true, 'tariff' => true, 'account' => true, 'dialplan' => true, 'translation_rules' => true, 'callerid_incoming' => true, 'translation_rules_incoming' => true, 'bundle_package_group_by' => true);
        $resellers_data_temp = $this->reseller_mod->get_data('', 1, 0, $search_data, $option_param);
        if (isset($resellers_data_temp['result']) && count($resellers_data_temp['result']) > 0)
            $resellers_data = current($resellers_data_temp['result']);
        else {
            show_404();
        }


        $data['data'] = $resellers_data;

        //  $logged_user_type = get_logged_account_type();
        //   $logged_account_id = get_logged_account_id();
        $data['country_options'] = $this->utils_model->get_countries();
        $data['currency_options'] = $this->utils_model->get_currencies();
        $data['state_options'] = $this->utils_model->get_states();
        /*
          if (check_logged_user_type(array('ADMIN', 'SUBADMIN', 'NOC'))) {
          $data['tariff_options'] = $this->utils_model->get_tariffs($logged_user_type, 'CUSTOMER');
          //$data['ac_mngrs_data'] = $this->member_mod->get_data('', '', '', array('account_type' => 'ACCOUNTMANAGER'));
          } else {
          $data['tariff_options'] = $this->utils_model->get_tariffs($logged_user_type, 'CUSTOMER', $logged_account_id);
          $option_param = array('tariff' => true);
          $data['logged_account_result'] = $this->member_mod->get_account_by_key('account_id', $logged_account_id, $option_param);
          } */
        //////////
        $user_search_data['account_id'] = $account_id;
        $user_search_data['user_type'] = 'RESELLERADMIN';

        $users_data_temp = $this->member_mod->get_data('', 1, 0, $user_search_data);
        if (isset($users_data_temp['result']))
            $users_data = current($users_data_temp['result']);
        else
            $users_data = array();
//ddd($users_data);die;
        $data['user_data'] = $users_data;
        //////////////
        $data['account_manager_options'] = $this->reseller_mod->get_user_by_account_manager();
        $data['account_manager_data'] = $this->reseller_mod->get_account_manager($account_id);
        $this->load->view('basic/header', $data);
        $this->load->view('reseller/reseller_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    function rState($id = -1) {
        $page_name = "rState";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');
        $this->load->model('reseller_mod');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (check_logged_user_group(array('CUSTOMER'))) {
            $account_id = get_logged_account_id();
        } elseif ($id != -1) {
            $id_decrypted = param_decrypt($id);
            $search_data = array();
            if (check_logged_user_group(array('RESELLER')))
                $search_data['parent_account_id'] = get_logged_account_id();
            elseif (check_logged_user_group(array(ADMIN_ACCOUNT_ID))) {
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

    function statement($id = -1, $arg1 = '', $format = '') {
        $page_name = "r_statement";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');
        $this->load->model('crspayment_mod');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['search_action']) && isset($_POST['account_id']) && $_POST['account_id'] != '') {
            $account_id = trim($_POST['account_id']);
        } elseif ($id != -1) {
            $account_id = param_decrypt($id);
        } elseif (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
            $account_id = get_logged_account_id();
        }

        $search_parameters = array('yearmonth', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }
        if ($_SESSION[$search_session_key]['yearmonth'] == '')
            $_SESSION[$search_session_key]['yearmonth'] = date("Ym");

        $search_data = array(
            'yearmonth' => $_SESSION[$search_session_key]['yearmonth'],
        );

        $customer_result = $this->member_mod->get_account_by_key('account_id', $account_id);
        if (!$customer_result) {
            $data['statement_error_message'] = 'Account Not Found';
        }

//==========export pdf start==========================			
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            $format = param_decrypt($format);

            $report_data = $this->report_mod->sdr_statement($account_id, $search_data);

            $yearmonth = $_SESSION[$search_session_key]['s_yearmonth'];
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
                $report_data = $this->report_mod->sdr_statement($account_id, $search_data);
                $data['customer_data'] = $customer_result;
                $data['sdr_terms'] = $this->utils_model->get_sdr_terms();
                $data['searched_account_id'] = $account_id;
                $data['data'] = $report_data;
            }
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('sdr_statement', $data);
            $this->load->view('basic/footer', $data);
        }
    }

}
