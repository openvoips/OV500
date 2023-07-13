<?php

/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Crs extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('crsvoip_mod');
        $this->load->model('bundle_mod');
        $this->load->model('route_mod');
        $this->load->helper('crs_helper');


        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
    }

    public function index() {
        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            show_404('403');
        }

        $data = array();
        $page_name = "crs_index";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();


        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('customer', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(site_url() . 'customers', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                echo $delete_id_array[0];
                $result = $this->crsvoip_mod->account_delete($delete_id_array[0]);
                var_dump($result);
                if ($result === true) {
                    $suc_msgs = 'Customer Deleted Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    redirect(current_url(), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } else {
                $err_msgs = 'Select Customer to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
            redirect(site_url() . 'customers', 'location', '301');
        }

        $search_parameters = array('account_id', 'company_name', 'account_type', 'web_username', 'sip_user', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        $search_data = array(
            'customer_voipminute_id' => $_SESSION[$search_session_key]['customer_voipminute_id'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'company_name' => $_SESSION[$search_session_key]['company_name'],
            'account_type' => $_SESSION[$search_session_key]['account_type'],
            'web_username' => $_SESSION[$search_session_key]['web_username'],
            'sip_user' => $_SESSION[$search_session_key]['sip_user'],
        );
        $is_file_downloaded = false;
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $voip_data = $this->crsvoip_mod->get_voip_data('', $per_page, $segment, $search_data);

            $total_count = $this->crsvoip_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'crs/index', $per_page, $pagination_uri_segment, $this->pagination);
            $data['voip_data'] = $voip_data;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $_SESSION['previous_url'] = current_url();
            $this->load->view('basic/header', $data);
            $this->load->view('listing', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function paymentdetail($id) {
        $page_name = "crs_paymentdetail";
        $search_session_key = 'search_' . $page_name;
        $this->load->library('pagination');
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            show_404('403');
        }

        $logged_account_id = get_logged_account_id();
        $search_parameters = array('pay_date', 'company_name', 'account_id', 'payment_type', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        if ($_SESSION[$search_session_key]['pay_date'] == '') {
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION[$search_session_key]['pay_date'] = $time_range;
        }

        $search_data = array(
            'time_range' => $_SESSION[$search_session_key]['pay_date'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'company_name' => $_SESSION[$search_session_key]['company_name'],
            'payment_type' => $_SESSION[$search_session_key]['payment_type'],
            'logged_customer_type' => get_logged_account_type(),
            'logged_customer_level' => get_logged_account_level(),
            'logged_customer_account_id' => get_logged_account_id(),);

        if (check_logged_user_type(array('ACCOUNTMANAGER')))
            $search_data['account_manager'] = $logged_account_id;
        elseif (check_logged_user_type(array('SALESMANAGER')))
            $search_data['sales_manager'] = $logged_account_id;
        elseif (check_logged_user_type(array('RESELLER'))) {
            // $report_search_data['parent_account_id'] = $logged_account_id;    
        } {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $response = $this->crs_mod->paymentdetail('', $per_page, $segment, $search_data);

            $total_count = $this->crs_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'report/paymentdetail', $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('paymentdetail', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function assignvoip() {}

    public function addvoip($id = '-1') {
        $page_name = "crs_addvoip";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if ($id == '-1')
            show_404();

        $logged_account_id = get_logged_account_id();
        $logged_user_group = get_logged_user_type();

        $account_id = param_decrypt($id);
        $accountinfo = $this->member_mod->get_account_by_key('account_id', $account_id, array());
        if (!isset($accountinfo) || count($accountinfo) == 0)
            show_404();
        $account_type = $accountinfo['account_type'];
        $currency_id = $accountinfo['currency_id'];
        ///////////////////////

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveTariff') {
            $this->form_validation->set_rules('tariff_id', 'Tarrif', 'trim|required');
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('account_type', 'Account Type', 'trim|required');
            $this->form_validation->set_rules('billingcode', 'Billing Code', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->crsvoip_mod->add($_POST);
                if ($result['status'] === true) {

                    $this->session->set_flashdata('suc_msgs', 'Tariff Assigned Successfully.');
                    redirect(site_url('crs/editvoip/' . $id . '/2'), 'location', '301');
                    exit();
                } else {
                    $this->session->set_flashdata('err_msgs', $result['msg']);
                }
            }
        }

        $data['accountinfo'] = $accountinfo;
        if ($logged_user_group == 'reseller') {
            $data['tariff_options'] = $this->crsvoip_mod->get_tariffs($currency_id, $logged_user_group, 'CUSTOMER', $logged_account_id);
        } else {
            $data['tariff_options'] = $this->crsvoip_mod->get_tariffs($currency_id, $logged_user_group, 'CUSTOMER');
        }

        $data['active_tab'] = 1;
        $this->load->view('basic/header', $data);
        $this->load->view('service_add', $data);
        $this->load->view('basic/footer', $data);
    }

    function editvoip($id = '-1', $active_tab = 1) {
        $page_name = "crs_editvoip";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if ($id == '-1')
            show_404();

        $logged_account_id = get_logged_account_id();
        $logged_user_group = get_logged_user_type();

        $account_id = param_decrypt($id);
        $data['active_tab'] = $active_tab;   
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveTariff') {
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('tariff_id', 'Tarrif', 'trim|required');
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('account_type', 'Account Type', 'trim|required');
            $this->form_validation->set_rules('billingcode', 'Billing Code', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->crsvoip_mod->edit_voip_data($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Tariff Assigned Successfully.');
                    redirect(site_url('crs/editvoip/' . $id . '/' . $_POST['active_tab']), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveBundle') {
            $data['active_tab'] = $_POST['tab'];

            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('bundle_package_id', 'Bundle', 'trim|required');
            $this->form_validation->set_rules('bundle_package_desc', 'Description', 'trim');
            $this->form_validation->set_rules('no_of_package', 'Number of Package', 'trim|required|numeric|greater_than[0]|less_than[1000]');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $time = 1;
                while ($time <= $_POST['no_of_package']) {
                    $result = $this->crsvoip_mod->add_bundle($_POST);
                    $time += 1;
                }

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Bundle & Package Added Successfully');
                    redirect(site_url('crs/editvoip/' . $id . '/' . $data['active_tab']), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSavesrcno') {
            $data['active_tab'] = $_POST['tab'];
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

                $result = $this->crsvoip_mod->update_callerid($post_array);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Caller ID Translation Rules Updated Successfully');
                    redirect(site_url('crs/editvoip/' . $id . '/' . $data['active_tab']), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveDstno') {
            $data['active_tab'] = $_POST['tab'];
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

                $result = $this->crsvoip_mod->update_translation_rules($post_array);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Incoming Caller ID Translation Rules Updated Successfully');
                    redirect(site_url('crs/editvoip/' . $id . '/' . $data['active_tab']), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveDidsrcNo') {
            $data['active_tab'] = $_POST['tab'];
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

                $result = $this->crsvoip_mod->update_callerid_incoming($post_array);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Incoming Caller ID Translation Rules Updated Successfully');
                    redirect(site_url('crs/editvoip/' . $id . '/' . $data['active_tab']), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveDidDSTrule') {
            $data['active_tab'] = $_POST['tab'];
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

                $result = $this->crsvoip_mod->update_translation_rules_incoming($post_array);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Incoming Caller ID Translation Rules Updated Successfully');
                    redirect(site_url('crs/editvoip/' . $id . '/' . $data['active_tab']), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveDialplanReseller') {
            $data['active_tab'] = $_POST['tab'];

            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('dialplan_id', 'Dialplan', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->crsvoip_mod->add_reseller_dialplan($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Dialing Plan Assigned Successfully.');
                    redirect(site_url('crs/editvoip/' . $id . '/' . $data['active_tab']), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveDialplanCustomer') {
            $data['active_tab'] = $_POST['tab'];

            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('dialplan_id', 'Dialplan', 'trim|required');
            $this->form_validation->set_rules('maching_string', 'Routing Pattern', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->crsvoip_mod->add_customer_dialplan($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Dialing Plan Assigned Successfully.');
                    redirect(site_url('crs/editvoip/' . $id . '/' . $data['active_tab']), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveInvoice') {
            $data['active_tab'] = $_POST['tab'];

            $this->form_validation->set_rules('account_id', 'Customer ID', 'trim|required');
            $this->form_validation->set_rules('billing_cycle', 'Billing Cycle', 'trim|required');
            $this->form_validation->set_rules('payment_terms', 'Payment Terms', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                //$result = $this->crsvoip_mod->edit_voip_data($_POST);
                $_POST['created_by'] = get_logged_user_id();
                $result = $this->crsvoip_mod->priceplan_update($_POST);


                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Invoice Congiguration Updated Successfully.');
                    redirect(site_url('crs/editvoip/' . $id . '/' . $data['active_tab']), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }  elseif (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
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
                    $result = $this->crsvoip_mod->delete_reseller_dialplan($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'Dialplan Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    redirect(current_url(), 'location', '301');
                    break;

                case 'customer_dialplan_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->crsvoip_mod->delete_customer_dialplan($account_id, $delete_param_array);
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
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->crsvoip_mod->delete_bundle($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'Bundle Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    redirect(current_url(), 'location', '301');
                    break;

                
                case 'account_ips_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->crsvoip_mod->delete_ip($account_id, $delete_param_array);

                    if ($result === true) {
                        $suc_msgs = 'IP Deleted Successfully';
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
                    $result = $this->crsvoip_mod->delete_sip($account_id, $delete_param_array);

                    if ($result === true) {
                        $suc_msgs = 'SIP Deleted Successfully';
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





        ////////////
        $search_data = array(); //'account_id' => $account_id);
        if (check_logged_user_group('reseller'))
            $search_data['parent_account_id'] = get_logged_account_id();

        /* $option_param = array('ip' => true, 'callerid' => true, 'sipuser' => true, 'tariff' => true, 'user' => false, 'prefix' => false, 'dialplan' => true, 'translation_rules' => true, 'callerid_incoming' => true, 'translation_rules_incoming' => true, 'bundle_package_group_by' => true); */
        $option_param = array('voipminuts' => true, 'bundle_package_group_by' => true, 'customer_priceplan' => true, 'dialplan' => true, 'customer_pricelist' => true, 'callerid' => TRUE, 'translation_rules' => true, 'callerid_incoming' => true, 'translation_rules_incoming' => true, 'ip' => true, 'sipuser' => true);
        $customers_data_temp = $this->crsvoip_mod->get_account_details($account_id, $search_data, $option_param);
 
 
        if (is_array($customers_data_temp) && count($customers_data_temp) > 0)
            $customers_data = $customers_data_temp;
        else {
            show_404();
        }
        $currency_id = $customers_data['currency_id'];
        /////

        if (check_logged_user_group(array('RESELLER'))) {
            $data['tariff_options'] = $this->crsvoip_mod->get_tariffs($currency_id, $logged_user_group, 'CUSTOMER', $logged_account_id);
        } else {
            $data['tariff_options'] = $this->crsvoip_mod->get_tariffs($currency_id, $logged_user_group, 'CUSTOMER');
        }
        /////
        $data['accountinfo'] = $customers_data;

        /////bundle data/
        $search_data = array('bundle_package_currency_id' => $currency_id);
        if (check_logged_user_group(array('RESELLER'))) {
            $created_by = get_logged_account_id();
        } else {
            $created_by = 'ADMIN';
        }
        $response = $this->bundle_mod->get_unassigned_data($account_id, $created_by, $search_data);
        $data['bundle_data'] = $response;
      
        if (check_logged_user_group(array('RESELLER'))) {
            $option_param = array('dialplan' => true);
            $logged_account_result = $this->member_mod->get_account_by_key('account_id', $logged_account_id, $option_param);
            if (count($logged_account_result['dialplan']) > 0) {
                $data['route_data'] = $logged_account_result['dialplan'];
            } else
                $data['route_data'] = array();
        }
        else {
            $route_data = $this->route_mod->get_data('dialplan_name', '', '', array());
            $data['route_data'] = $route_data['result'];
        }

        ////////


        $this->load->view('basic/header', $data);
        $this->load->view('service_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function ipAdd($id1 = -1, $active_tab = 1) {
        $account_id = param_decrypt($id1);
//        echo $account_id;die;
        if (isset($id2))
            $id = param_decrypt($id2);

        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "customer_voip_ipadd";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;
        $data['active_tab'] = $active_tab;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $data['active_tab'] = $_POST['tab'];
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
                $result = $this->crsvoip_mod->add_ip($_POST);
                $id = $this->crsvoip_mod->last_customer_ip_id;
//                echo '<pre>';
//                print_r($_POST);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User IP Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url('crs') . '/ipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url('crs') . '/editvoip/' . param_encrypt($account_id) . '/' . $data['active_tab'], 'location', '301');
                    }

                    redirect(base_url('crs') . '/editvoip/' . param_encrypt($account_id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $search_data = array();
        $customers_data_temp = $this->crsvoip_mod->get_account_details($account_id, $search_data, $option_param);

        if (is_array($customers_data_temp) && count($customers_data_temp) > 0)
            $customers_data = $customers_data_temp;
        else {
            show_404();
        }
        $data['data'] = $customers_data;
//        echo '<pre>';
//         print_r($customers_data);die;
        $data['account_id'] = $account_id;
        $this->load->view('basic/header', $data);
        $this->load->view('ipAdd', $data);
        $this->load->view('basic/footer', $data);
    }

    public function ipEdit($id1 = -1, $id2 = -1, $active_tab = 1) {
        $account_id = param_decrypt($id1);
        $id = param_decrypt($id2);
        if (strlen($account_id) < 1 and $id < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "customer_voip_ipedit";
        $data['active_tab'] = $active_tab;
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;


        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $id = $_POST['id'];
            $data['account_id'] = $account_id;
            $data['active_tab'] = $_POST['tab'];

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
                $result = $this->crsvoip_mod->update_ip($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User IP Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url('crs') . '/ipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url('crs') . '/editvoip/' . param_encrypt($account_id) . '/' . $data['active_tab'], 'location', '301');
                    }

                    redirect(base_url('crs') . '/editvoip/' . param_encrypt($account_id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $customers_data = array();

        $search_data = array('account_id' => $account_id);
        $option_param = array('ip' => true, 'account_ip_id' => $id);
        $customers_data_temp = $this->crsvoip_mod->get_account_details($account_id, $search_data, $option_param);

        if (is_array($customers_data_temp) && count($customers_data_temp) > 0)
            $customers_data = $customers_data_temp;
        else {
            show_404();
        }
        $data['data'] = $customers_data;

        $this->load->view('basic/header', $data);
        $this->load->view('ipedit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function sipAdd($id1 = -1, $active_tab = 1) {
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
        $data['active_tab'] = $active_tab;
        $page_name = "customer_voip_sipAdd";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim');
            $this->form_validation->set_rules('sip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('sip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('status', 'Status', 'trim|required');
            $this->form_validation->set_rules('voicemail_enabled', 'Voicemail Option', 'trim|required');
            //     $this->form_validation->set_rules('voicemail_email', 'Voicemail EmailList', 'trim');
            $this->form_validation->set_rules('extension_no', 'Extension', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->crsvoip_mod->add_sip($_POST);
                // echo '<pre>';    print_r($result);die;
                if ($result['status'] === true) {
                    $this->session->set_flashdata('suc_msgs', 'User SIP Added Successfully');
                    $action = trim($_POST['button_action']);
                    if ($action == 'save') {
                        redirect(base_url('crs') . '/sipEdit/' . param_encrypt($result['account_id']) . '/' . param_encrypt($result['id']) . '/' . $data['active_tab'], 'location', '301');
                    } elseif ($action == 'save_close') {
                        redirect(base_url('crs') . '/editvoip/' . param_encrypt($result['account_id']) . '/' . $data['active_tab'], 'location', '301');
                    }

                    redirect(base_url('crs') . '/editvoip/' . param_encrypt($result['account_id']) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $search_data = array();
        $customers_data_temp = $this->crsvoip_mod->get_account_details($account_id, $search_data, $option_param);

        if (is_array($customers_data_temp) && count($customers_data_temp) > 0)
            $customers_data = $customers_data_temp;
        else {
            show_404();
        }

        $data['data'] = $customers_data;
        $data['account_id'] = $account_id;
        $this->load->view('basic/header', $data);
        $this->load->view('sipadd', $data);
        $this->load->view('basic/footer', $data);
    }

    public function sipEdit($id1 = -1, $id2 = -1, $active_tab = 1) {
        $account_id = param_decrypt($id1);
        $id = param_decrypt($id2);
        if (strlen($account_id) < 1 and $id < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');
        $data['active_tab'] = $active_tab;
        $page_name = "customer_voip_sipEdit";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $id = $_POST['id'];
            $data['account_id'] = $account_id;
            $data['active_tab'] = $_POST['tab'];
            $this->form_validation->set_rules('account_id', 'User ID', 'trim|required');
            $this->form_validation->set_rules('id', 'SIP ID', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim');
            $this->form_validation->set_rules('voicemail_enabled', 'Voicemail Option', 'trim|required');
            // $this->form_validation->set_rules('voicemail_email', 'Voicemail', 'trim');
            $this->form_validation->set_rules('extension_no', 'Extension', 'trim|required');
            $this->form_validation->set_rules('sip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('sip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('status', 'Status', 'trim|required');


            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
                print_r($data);
                die;
            } else {
//                  echo '<pre>';
//             print_r($_POST);die;
                $result = $this->crsvoip_mod->update_sip($_POST);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User SIP Added Successfully');
                    $action = trim($_POST['button_action']);
                    if ($action == 'save') {
                        redirect(base_url('crs') . '/sipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id) . '/' . $data['active_tab'], 'location', '301');
                    } elseif ($action == 'save_close') {
                        redirect(base_url('crs') . '/editvoip/' . param_encrypt($account_id) . '/' . $data['active_tab'], 'location', '301');
                    }

                    redirect(base_url('crs') . '/editvoip/' . param_encrypt($account_id) . '/' . $data['active_tab'], 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }


        $search_data = array('account_id' => $account_id);
        $option_param = array('sipuser' => true, 'customer_sip_id' => $id);
//        print_r($option_param);die;
        $customers_data_temp = $this->crsvoip_mod->get_account_details($account_id, $search_data, $option_param);

        if (is_array($customers_data_temp) && count($customers_data_temp) > 0)
            $customers_data = $customers_data_temp;
        else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('sipedit', $data);
        $this->load->view('basic/footer', $data);
    }

}
