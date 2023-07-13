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
include_once (dirname(__FILE__) . "/Billingapi.php");

class Billing extends Billingapi {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
         $this->load->model('customerinvoice_mod');
        $this->load->model('customerinvoiceconfig_mod');
        $this->load->model('Smtpconfig_mod');
        $this->load->model('EmailTemplate_mod');

        $this->load->helper('Billing_helper');
    }

    public function index() {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->load->view('basic/header');
        $this->load->view('billing');
        $this->load->view('basic/footer');
    }



    
    public function inconfig($arg1 = '', $format = '') {

        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', ADMIN_ACCOUNT_ID))) {
            show_404('403');
        }
        $data['page_name'] = "inConfig";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
//        if (!check_account_permission('pGConfig', 'add'))
//            show_404('403');
        $logged_account_id = get_logged_account_id();
        if (check_logged_user_type(array(ADMIN_ACCOUNT_ID))) {
            $_POST['data']['account_id'] = ADMIN_ACCOUNT_ID;
            $logged_account_id = ADMIN_ACCOUNT_ID;
        }
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('data[company_name]', 'Business name / Company name', 'trim|required|min_length[5]|max_length[100]');
            $this->form_validation->set_rules('data[address]', 'Business / Company Address', 'trim|required|min_length[10]|max_length[1000]');
            $this->form_validation->set_rules('data[bank_detail]', 'Business / Company Bank Account Detail where want to recive Payment', 'trim|required|min_length[10]|max_length[1000]');
            $this->form_validation->set_rules('data[support_text]', 'Customer / Billing Support Detail In invoice', 'trim|required|min_length[10]|max_length[1000]');


            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {

                ///////upload logo if exists///////////////
                if (is_uploaded_file($_FILES['invoicelogo']['tmp_name'])) {
                    $upload_path = 'uploads/invoicelogo';
                    if (!file_exists($upload_path))
                        mkdir($upload_path);

                    chmod($upload_path, 0777);

                    $file_name = $logged_account_id . '_' . time();

                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'gif|jpg|png|jpeg';
                    $config['file_name'] = $file_name;
                    $config['file_ext_tolower'] = TRUE;
                    $config['max_size'] = 0;

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('invoicelogo')) {//die("SSS");
                        $uploaded_data_array = $this->upload->data();
                        $client_name = $uploaded_data_array['client_name'];
                        $file_name = $uploaded_data_array['file_name'];

                        /////////resize image//////////
                        $width = 300;
                        $height = 300;
                        $config = array();
                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $upload_path . '/' . $file_name;
                        $config['create_thumb'] = false;
                        $config['maintain_ratio'] = TRUE;
                        $config['width'] = $width;
                        $config['height'] = $height;
                        //$config['new_image']      = $upload_path.'/'. 'thumb_'.$file_name;               
                        $this->image_lib->initialize($config);
                        $this->image_lib->resize();
                        $_POST['data']['logo'] = $file_name;
                    } else {
                        $error = $this->upload->display_errors();
                        $data['err_msgs'] = $error;
                    }
                }


                if (!isset($data['err_msgs']) || $data['err_msgs'] == '') {
                    $result = $this->customerinvoiceconfig_mod->inConfig_update($_POST['data']);
                    if ($result['status']) {
                        ////////delete previous logo if uploaded new//////
                        if (isset($_POST['data']['logo']) && $_POST['data']['logo'] != '' && isset($_POST['existing_invoicelogo']) && $_POST['existing_invoicelogo'] != '') {//die("how?");
                            $existing_invoicelogo = trim($_POST['existing_invoicelogo']);
                            $upload_path = 'uploads/invoicelogo';
                            $file_path = $upload_path . '/' . $existing_invoicelogo;
                            if (file_exists($file_path)) {
                                unlink($file_path);
                            }
                        }

                        $this->session->set_flashdata('suc_msgs', 'Invoice Setup Updated Successfully');
                        redirect(base_url() . 'Billing/inconfig', 'location', '301'); // 301 redirected	
                        exit();
                    } else {
                        $data['err_msgs'] = $result['msg'];
                    }
                }
            }
        }


        $data['data'] = $this->customerinvoiceconfig_mod->inConfig_data($logged_account_id);

        $this->load->view('basic/header', $data);
        $this->load->view('inconfig', $data);
        $this->load->view('basic/footer', $data);
    }

    public function customerinvoice($arg1 = '', $format = '') {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', ADMIN_ACCOUNT_ID))) {
            show_404('403');
        }
        $page_name = "customerinvoice_index";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();



        if (isset($_POST['search_action'])) {
            $_SESSION['search_customerinvoice_data2'] = array('s_account_id' => $_POST['account_id'], 's_invoice_id' => $_POST['invoice_id'], 's_billing_date' => $_POST['billing_date'], 's_no_of_records' => $_POST['no_of_rows']);
        } else {
            $_SESSION['search_customerinvoice_data2']['s_account_id'] = isset($_SESSION['search_customerinvoice_data2']['s_account_id']) ? $_SESSION['search_customerinvoice_data']['s_account_id'] : '';
            $_SESSION['search_customerinvoice_data2']['s_invoice_id'] = isset($_SESSION['search_customerinvoice_data2']['s_invoice_id']) ? $_SESSION['search_customerinvoice_data']['s_invoice_id'] : '';
            $_SESSION['search_customerinvoice_data2']['s_billing_date'] = isset($_SESSION['search_customerinvoice_data2']['s_billing_date']) ? $_SESSION['search_customerinvoice_data']['s_billing_date'] : '';
            $_SESSION['search_customerinvoice_data2']['s_no_of_records'] = isset($_SESSION['search_customerinvoice_data2']['s_no_of_records']) ? $_SESSION['search_customerinvoice_data']['s_no_of_records'] : '';
        }
        if ($_SESSION['search_customerinvoice_data']['s_billing_date'] == '') {
            $yesterday_timestamp = strtotime("yesterday");
            $yesterday = date('Y-m-d', $yesterday_timestamp);
            $time_range = $yesterday . ' 00:00:00 - ' . $yesterday . ' 23:59:59';
           // $_SESSION['search_customerinvoice_data']['s_billing_date'] = $time_range;
        }
        $search_data = array(
            'account_id' => $_SESSION['search_customerinvoice_data2']['s_account_id'],
            'invoice_id' => $_SESSION['search_customerinvoice_data2']['s_invoice_id'],
			'bill_date' => $_SESSION['search_customerinvoice_data2']['s_billing_date'],
        );
        $order_by = '';

        $pagination_uri_segment = 3;
        if ($this->uri->segment($pagination_uri_segment) == '') {
            $segment = 0;
        } else {
            $segment = $this->uri->segment($pagination_uri_segment);
        }

        if (isset($_SESSION['search_customerinvoice_data2']['s_no_of_records']) && $_SESSION['search_customerinvoice_data2']['s_no_of_records'] != '')
            $per_page = $_SESSION['search_customerinvoice_data2']['s_no_of_records'];
        else
            $per_page = RECORDS_PER_PAGE;


        $option_param = array('sum' => true);
        $customerinvoice_data = $this->customerinvoice_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
        $data['total_records'] = $total = $this->customerinvoice_mod->get_data_total_count();
        $config = array();
        $config = $this->utils_model->setup_pagination_option($total, 'Billing/customerinvoice', $per_page, $pagination_uri_segment);
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['customerinvoice_data'] = $customerinvoice_data;

        $this->load->view('basic/header', $data);
        $this->load->view('customerinvoice/list', $data);
        $this->load->view('basic/footer', $data);
    }

    
    public function smtpconfig($arg1 = '', $format = '') {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', 'SYSTEM'))) {
            show_404('403');
        }
        $page_name = "smtp_config_list";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {

            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->Smtpconfig_mod->delete($delete_param_array);
                if ($result === true) {
                    $suc_msgs = 'SMTP Configuration Deleted Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    redirect(current_url(), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } else {
                $err_msgs = 'Select SMTP Config to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
        }

        if (isset($_POST['search_action'])) {
            $_SESSION['search_smtpconfig_data'] = array('s_account_id' => $_POST['account_id'], 's_smtp_host' => $_POST['smtp_host'], 's_smtp_from' => $_POST['smtp_from'], 's_smtp_port' => $_POST['smtp_port'], 's_no_of_records' => $_POST['no_of_rows']);
        } else {
            $_SESSION['search_smtpconfig_data']['s_account_id'] = isset($_SESSION['search_smtpconfig_data']['s_account_id']) ? $_SESSION['search_smtpconfig_data']['s_account_id'] : '';
            $_SESSION['search_smtpconfig_data']['s_smtp_host'] = isset($_SESSION['search_smtpconfig_data']['s_smtp_host']) ? $_SESSION['search_smtpconfig_data']['s_smtp_host'] : '';
            $_SESSION['search_smtpconfig_data']['s_smtp_from'] = isset($_SESSION['search_smtpconfig_data']['s_smtp_from']) ? $_SESSION['search_smtpconfig_data']['s_smtp_from'] : '';
            $_SESSION['search_smtpconfig_data']['s_smtp_port'] = isset($_SESSION['search_smtpconfig_data']['s_smtp_port']) ? $_SESSION['search_smtpconfig_data']['s_smtp_port'] : '';
            $_SESSION['search_smtpconfig_data']['s_no_of_records'] = isset($_SESSION['search_smtpconfig_data']['s_no_of_records']) ? $_SESSION['search_smtpconfig_data']['s_no_of_records'] : '';
        }
        $search_data = array(
            'account_id' => $_SESSION['search_smtpconfig_data']['s_account_id'],
            'smtp_host' => $_SESSION['search_smtpconfig_data']['s_smtp_host'],
            'smtp_from' => $_SESSION['search_smtpconfig_data']['s_smtp_from'],
            'smtp_port' => $_SESSION['search_smtpconfig_data']['s_smtp_port'],
        );
        $order_by = '';


        $search_array = array();
        if ($_SESSION['search_smtpconfig_data']['s_account_id'] != '')
            $search_array['Account ID'] = $_SESSION['search_smtpconfig_data']['s_account_id'];
        if ($_SESSION['search_smtpconfig_data']['s_smtp_host'] != '')
            $search_array['SMTP Host'] = $_SESSION['search_smtpconfig_data']['s_smtp_host'];

        if (isset($_SESSION['search_smtpconfig_data']['s_smtp_from']) && $_SESSION['search_smtpconfig_data']['s_smtp_from'] != '') {
            $search_array['SMTP From Email'] = $_SESSION['search_smtpconfig_data']['s_smtp_from'];
        }
        if (isset($_SESSION['search_smtpconfig_data']['s_smtp_port']) && $_SESSION['search_smtpconfig_data']['s_smtp_port'] != '') {
            $search_array['SMTP Port'] = $_SESSION['search_smtpconfig_data']['s_smtp_port'];
        }
        $pagination_uri_segment = 3;
        if ($this->uri->segment($pagination_uri_segment) == '') {
            $segment = 0;
        } else {
            $segment = $this->uri->segment($pagination_uri_segment);
        }

        if (isset($_SESSION['search_smtpconfig_data']['s_no_of_records']) && $_SESSION['search_smtpconfig_data']['s_no_of_records'] != '')
            $per_page = $_SESSION['search_smtpconfig_data']['s_no_of_records'];
        else
            $per_page = RECORDS_PER_PAGE;


        $option_param = array();
        $smtp_data = $this->Smtpconfig_mod->get_smtp_data($order_by, $per_page, $segment, $search_data, $option_param);
        $data['total_records'] = $total = $this->Smtpconfig_mod->get_smtp_total_count();
        $config = array();
        $config = $this->utils_model->setup_pagination_option($total, 'Smtpconfig', $per_page, $pagination_uri_segment);
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['smtp_data'] = $smtp_data;
        $this->load->view('basic/header', $data);
        $this->load->view('smtpconfig/list', $data);
        $this->load->view('basic/footer', $data);
    }

    public function smtpconfigadd() {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', 'SYSTEM'))) {
            show_404('403');
        }
        $page_name = "smtp_config_add";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('smtp_auth', 'SMTP Auth', 'trim|required');
            $this->form_validation->set_rules('smtp_secure', 'SMTP SECURE', 'trim|required');
            $this->form_validation->set_rules('smtp_host', 'SMTP Host', 'trim|required');
            $this->form_validation->set_rules('smtp_port', 'SMTP Port', 'trim');
            $this->form_validation->set_rules('smtp_username', 'SMTP Username', 'trim');
            $this->form_validation->set_rules('smtp_password', 'SMTP Password', 'trim');
            $this->form_validation->set_rules('smtp_from', 'SMTP From Email', 'trim');
            $this->form_validation->set_rules('smtp_from_name', 'SMTP From Name', 'trim');
            $this->form_validation->set_rules('smtp_xmailer', 'SMTP Xmailer', 'trim');
            $this->form_validation->set_rules('smtp_host_name', 'SMTP Host Name', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['created_by'] = get_logged_account_id();
                $result = $this->Smtpconfig_mod->add($_POST);
                if ($result === true) {
                    $id = $this->Smtpconfig_mod->smtp_config_id;
                    $this->session->set_flashdata('suc_msgs', 'SMTP Config Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save')
                        redirect(site_url('Billing/smtpconfigedit/' . param_encrypt($id)), 'location', '301');
                    else
                        redirect(site_url('Billing/smtpconfig'), 'location', '301');

                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        $this->load->view('basic/header', $data);
        $this->load->view('smtpconfig/add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function smtpconfigedit($id = -1) {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', 'SYSTEM'))) {
            show_404('403');
        }
        $page_name = "smtp_config_edit";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if ($id == -1)
            show_404();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('smtp_auth', 'SMTP Auth', 'trim|required');
            $this->form_validation->set_rules('smtp_secure', 'SMTP SECURE', 'trim|required');
            $this->form_validation->set_rules('smtp_host', 'SMTP Host', 'trim|required');
            $this->form_validation->set_rules('smtp_port', 'SMTP Port', 'trim');
            $this->form_validation->set_rules('smtp_username', 'SMTP Username', 'trim');
            $this->form_validation->set_rules('smtp_password', 'SMTP Password', 'trim');
            $this->form_validation->set_rules('smtp_from', 'SMTP From Email', 'trim');
            $this->form_validation->set_rules('smtp_from_name', 'SMTP From Name', 'trim');
            $this->form_validation->set_rules('smtp_xmailer', 'SMTP Xmailer', 'trim');
            $this->form_validation->set_rules('smtp_host_name', 'SMTP Host Name', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['created_by'] = get_logged_account_id();
                $result = $this->Smtpconfig_mod->update($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Smtp Config Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save')
                        redirect(site_url('Billing/smtpconfigedit/' . $id), 'location', '301');
                    else
                        redirect(site_url('Billing/smtpconfig'), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $smtp_id = param_decrypt($id);
        $smtp_data = $this->Smtpconfig_mod->get_smtp_data_by_id($smtp_id);
        $data['smtp_data'] = $smtp_data[0];
        $this->load->view('basic/header', $data);
        $this->load->view('smtpconfig/edit', $data);
        $this->load->view('basic/footer', $data);
    }

////////////////////
    public function emailtemplate($arg1 = '', $format = '') {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', 'SYSTEM'))) {
            show_404('403');
        }
        $page_name = "email_template_list";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'Billing/emailtemplate/', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->EmailTemplate_mod->delete($delete_param_array);
                if ($result === true) {
                    $suc_msgs = count($delete_id_array) . ' CLI';
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
                $err_msgs = 'Select Email Config to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
        }

        if (isset($_POST['search_action'])) {
            $_SESSION['search_EmailTemplate_data'] = array('s_account_id' => $_POST['account_id'], 's_email_name' => $_POST['email_name'], 's_email_subject' => $_POST['email_subject'], 's_template_for' => $_POST['template_for'], 's_no_of_records' => $_POST['no_of_rows']);
        } else {
            $_SESSION['search_EmailTemplate_data']['s_account_id'] = isset($_SESSION['search_EmailTemplate_data']['s_account_id']) ? $_SESSION['search_EmailTemplate_data']['s_account_id'] : '';
            $_SESSION['search_EmailTemplate_data']['s_email_name'] = isset($_SESSION['search_EmailTemplate_data']['s_email_name']) ? $_SESSION['search_EmailTemplate_data']['s_email_name'] : '';
            $_SESSION['search_EmailTemplate_data']['s_email_subject'] = isset($_SESSION['search_EmailTemplate_data']['s_email_subject']) ? $_SESSION['search_EmailTemplate_data']['s_email_subject'] : '';
            $_SESSION['search_EmailTemplate_data']['s_template_for'] = isset($_SESSION['search_EmailTemplate_data']['s_template_for']) ? $_SESSION['search_EmailTemplate_data']['s_template_for'] : '';
            $_SESSION['search_EmailTemplate_data']['s_no_of_records'] = isset($_SESSION['search_EmailTemplate_data']['s_no_of_records']) ? $_SESSION['search_EmailTemplate_data']['s_no_of_records'] : '';
        }
        $search_data = array(
            'account_id' => $_SESSION['search_EmailTemplate_data']['s_account_id'],
            'email_name' => $_SESSION['search_EmailTemplate_data']['s_email_name'],
            'email_subject' => $_SESSION['search_EmailTemplate_data']['s_email_subject'],
            'template_for' => $_SESSION['search_EmailTemplate_data']['s_template_for'],
        );
        $order_by = '';


        $search_array = array();
        if ($_SESSION['search_EmailTemplate_data']['s_account_id'] != '')
            $search_array['Account ID'] = $_SESSION['search_EmailTemplate_data']['s_account_id'];
        if ($_SESSION['search_EmailTemplate_data']['s_email_name'] != '')
            $search_array['Email Name'] = $_SESSION['search_EmailTemplate_data']['s_email_name'];

        if (isset($_SESSION['search_EmailTemplate_data']['s_email_subject']) && $_SESSION['search_EmailTemplate_data']['s_email_subject'] != '') {
            $search_array['Email Subject'] = $_SESSION['search_EmailTemplate_data']['s_email_subject'];
        }
        if (isset($_SESSION['search_EmailTemplate_data']['s_template_for']) && $_SESSION['search_EmailTemplate_data']['s_template_for'] != '') {
            $search_array['Template'] = $_SESSION['search_EmailTemplate_data']['s_template_for'];
        }
        $pagination_uri_segment = 3;
        if ($this->uri->segment($pagination_uri_segment) == '') {
            $segment = 0;
        } else {
            $segment = $this->uri->segment($pagination_uri_segment);
        }

        if (isset($_SESSION['search_EmailTemplate_data']['s_no_of_records']) && $_SESSION['search_EmailTemplate_data']['s_no_of_records'] != '')
            $per_page = $_SESSION['search_EmailTemplate_data']['s_no_of_records'];
        else
            $per_page = RECORDS_PER_PAGE;


        $option_param = array();
        $template_data = $this->EmailTemplate_mod->get_template_data($order_by, $per_page, $segment, $search_data, $option_param);
        $data['total_records'] = $total = $this->EmailTemplate_mod->get_smtp_total_count();
        $config = array();
        $config = $this->utils_model->setup_pagination_option($total, 'Billing/emailtemplate/index', $per_page, $pagination_uri_segment);
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['template_data'] = $template_data;
        $this->load->view('basic/header', $data);
        $this->load->view('emailtemplate/list', $data);
        $this->load->view('basic/footer', $data);
    }

    public function emailtemplateadd() {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', 'ADMIN', 'SYSTEM'))) {
            show_404('403');
        }
        $page_name = "email_template_add";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $logged_account_id = get_logged_account_id();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('template_name', 'Template Name', 'trim|required');
            $this->form_validation->set_rules('template_for', 'Template For', 'trim|required');
            $this->form_validation->set_rules('template_subject', 'Template Subject', 'trim|required');
            $this->form_validation->set_rules('template_body', 'Template Body', 'trim');
            $this->form_validation->set_rules('template_bcc', 'Template Bcc', 'trim');
            $this->form_validation->set_rules('template_cc', 'Template CC', 'trim');
            $this->form_validation->set_rules('template_email_daemon', 'Template Email Daemon', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['created_by'] = get_logged_account_id();
                $result = $this->EmailTemplate_mod->add($_POST);
                if ($result === true) {
                    $id = $this->EmailTemplate_mod->id;
                    $this->session->set_flashdata('suc_msgs', 'Email Template Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save')
                        redirect(site_url('Billing/emailtemplateedit/' . param_encrypt($id)), 'location', '301');
                    else
                        redirect(site_url('Billing/emailtemplate'), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $data['smtp_data'] = $this->EmailTemplate_mod->get_smtp_data($logged_account_id);
        $this->load->view('basic/header', $data);
        $this->load->view('emailtemplate/add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function emailtemplateedit($id = -1) {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', 'SYSTEM'))) {
            show_404('403');
        }
        $page_name = "email_template_edit";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $logged_account_id = get_logged_account_id();

        if ($id == -1)
            show_404();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('id', 'ID', 'trim|required');
            $this->form_validation->set_rules('template_name', 'Template Name', 'trim|required');
            $this->form_validation->set_rules('template_for', 'Template For', 'trim|required');
            $this->form_validation->set_rules('template_subject', 'Template Subject', 'trim|required');
            $this->form_validation->set_rules('template_body', 'Template Body', 'trim');
            $this->form_validation->set_rules('template_bcc', 'Template Bcc', 'trim');
            $this->form_validation->set_rules('template_cc', 'Template CC', 'trim');
            $this->form_validation->set_rules('template_email_daemon', 'Template Email Daemon', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {

                $result = $this->EmailTemplate_mod->update($_POST);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Email Template Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save')
                        redirect(site_url('Billing/emailtemplateedit/' . $id), 'location', '301');
                    else
                        redirect(site_url('Billing/emailtemplate'), 'location', '301');

                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if ($id != -1) {
            $id = param_decrypt($id);
        }
        $temp_data = $this->EmailTemplate_mod->get_temp_data_by_id($id);
        $data['temp_data'] = $temp_data[0];
        $data['smtp_data'] = $this->EmailTemplate_mod->get_smtp_data($logged_account_id);
        $this->load->view('basic/header', $data);
        $this->load->view('emailtemplate/edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function ajax_get_mail_content($id) {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', 'ADMIN', 'SYSTEM'))) {
            show_404('403');
        }
        ///////////////////	
        $temp_data = $this->EmailTemplate_mod->get_temp_data_by_id($id);
        $temp_data = $temp_data[0];
        $message = $temp_data['email_body'];
        //////////////////////
        /////////////////
        $logged_account_id = get_logged_account_id();
        if (check_logged_user_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
            $logged_account_id = 'ADMIN';
        }
        $config_data = $this->customerinvoiceconfig_mod->inConfig_data($logged_account_id);
        ///////////////////
        //$message = replace_mail_variables($message);

        $replace_array = array(
            '{{CUSTOMER_NAME}}' => 'John Doe',
            '{{AMOUNT}}' => 100,
            '{{COMPANY_NAME}}' => $config_data['company_name'],
            '{{SITE_URL}}' => site_url()
        );
        //$message = $temp_data['email_body'];
        $message = replace_mail_variables($message, $replace_array);



        $heading = '';
        $body = file_get_contents(FCPATH . 'email_templates/blank.html');
        //$body		= str_replace("#SITE_URL#", base_url(), $body);
        //$body		= str_replace("#SITE_LOGO#", SITE_FULL_NAME, $body);
        $body = str_replace("#HEADING#", $heading, $body);
        $body = str_replace("#BODY#", $message, $body);
        //$body		= str_replace("#SITE_NAME#", 'Kind regards,<br><strong>'.SITE_FULL_NAME.'</strong>', $body);

        echo $body;
    }


     public function customerinvoicedetails($id = -1) {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group(array('RESELLER', ADMIN_ACCOUNT_ID))) {
            show_404('403');
        }
        $page_name = "customerinvoice_edit";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if ($id == -1)
            show_404();
        $DB1 = $this->load->database('default', true);

        $invoice_id = param_decrypt($id);
        $search_data['invoice_id'] = $invoice_id;
        $customerinvoice_data = $this->customerinvoice_mod->get_data('', 1, 0, $search_data);

        if (isset($customerinvoice_data['result']) && count($customerinvoice_data['result']) > 0)
            $data['customerinvoice_data'] = current($customerinvoice_data['result']);
        else
            show_404();

        $logged_account_id = get_logged_account_id();

        $data['invoice_config'] = $this->customerinvoiceconfig_mod->inConfig_data($logged_account_id);


        /////////////////////////////		


        if ($data['customerinvoice_data']['account_manager'] != '') {
            $sql = "SELECT name,emailaddress,phone FROM users WHERE user_id ='" . $data['customerinvoice_data']['account_manager'] . "'";
            $query = $DB1->query($sql);
            $data['account_manager_details'] = $query->row_array();
        }



        $sql = "SELECT	
				sys_sdr_terms.service_id,
				if(bill_itemlist.item_name is null, bill_account_sdr.service_number, bill_itemlist.item_name ) dst,
				
				bill_account_sdr.account_id,
				bill_account_sdr.invoice_id,
				bill_account_sdr.rule_type item_id,				
				bill_account_sdr.rate,
				sum(bill_account_sdr.unit) quantity,
				
				sum(bill_account_sdr.totalcost) total_charges,
				
				bill_account_sdr.startdate, bill_account_sdr.enddate, CONCAT(bill_account_sdr.startdate, bill_account_sdr.enddate) date_start_end,
				(SELECT service_name FROM bill_services WHERE bill_services.service_id = sys_sdr_terms.service_id) AS service_name
				
			FROM `bill_account_sdr`
			INNER JOIN sys_sdr_terms on bill_account_sdr.rule_type = sys_sdr_terms.term and bill_account_sdr.rule_type not in ('ADDCREDIT', 'REMOVECREDIT', 'IN','OUT')
			
			LEFT JOIN bill_itemlist on bill_itemlist.item_id = bill_account_sdr.rule_type 
			WHERE invoice_id = '" . $invoice_id . "' 
			GROUP BY service_id, dst, rate, billing_date 
			
			union
			SELECT	
				sys_sdr_terms.service_id,
				if(bill_itemlist.item_name is null, bill_account_sdr.service_number, bill_itemlist.item_name ) dst,
				
				bill_account_sdr.account_id,
				bill_account_sdr.invoice_id,
				bill_account_sdr.rule_type item_id,				
				bill_account_sdr.rate,
				sum(bill_account_sdr.unit) quantity,
				
				sum(bill_account_sdr.totalcost) total_charges,
				
				bill_account_sdr.startdate, bill_account_sdr.enddate, CONCAT(bill_account_sdr.startdate, bill_account_sdr.enddate) date_start_end,
				(SELECT service_name FROM bill_services WHERE bill_services.service_id = sys_sdr_terms.service_id) AS service_name
				
			FROM `bill_account_sdr`
			INNER JOIN sys_sdr_terms on bill_account_sdr.rule_type = sys_sdr_terms.term and bill_account_sdr.rule_type   in ('IN','OUT')
			
			LEFT JOIN bill_itemlist on bill_itemlist.item_id = bill_account_sdr.rule_type 
			WHERE invoice_id = '" . $invoice_id . "' 
			GROUP BY service_id, dst, rate 
			 ";


			
			  $sql = "SELECT	
				sys_sdr_terms.service_id,
				if(bill_itemlist.item_name is null, bill_account_sdr.service_number, bill_itemlist.item_name ) dst,
				
				bill_account_sdr.account_id,
				bill_account_sdr.invoice_id,
				bill_account_sdr.rule_type item_id,				
				bill_account_sdr.rate,
				sum(bill_account_sdr.unit) quantity,
				
				sum(bill_account_sdr.totalcost) total_charges,
				
				bill_account_sdr.startdate, bill_account_sdr.enddate, 
								
				(CASE 
					WHEN rule_type='IN' OR rule_type='OUT' 
						THEN bill_account_sdr.service_number
					
					ELSE 
						CONCAT(bill_account_sdr.startdate, bill_account_sdr.enddate)
				 END) AS date_start_end,			
				
				
				(SELECT service_name FROM bill_services WHERE bill_services.service_id = sys_sdr_terms.service_id) AS service_name
				
			FROM `bill_account_sdr`
			INNER JOIN sys_sdr_terms on bill_account_sdr.rule_type = sys_sdr_terms.term
			
			LEFT JOIN bill_itemlist on bill_itemlist.item_id = bill_account_sdr.rule_type 
			WHERE invoice_id = '" . $invoice_id . "' 
			
			AND rule_type NOT IN('ADDCREDIT', 'REMOVECREDIT', 'ADDTESTBALANCE', 'REMOVETESTBALANCE', 'ADDNETOFFBALANCE', 'REMOVENETOFFBALANCE')
			GROUP BY service_id, rate, date_start_end";
			
			

        $query = $DB1->query($sql);
        $data['sdr_data'] = $query->result_array();



        $this->load->view('basic/header', $data);
        $this->load->view('customerinvoice/details', $data);
        $this->load->view('basic/footer', $data);
    }

    function sql()
    {
        $sql="CREATE TABLE IF NOT EXISTS bill_smtp_config (
  id bigint(11) NOT NULL AUTO_INCREMENT,
  account_id varchar(30) DEFAULT '',
  smtp_config_id varchar(200) DEFAULT NULL,
  smtp_auth enum('0','1') DEFAULT NULL,
  smtp_secure enum('SSL','TSL') DEFAULT NULL,
  smtp_host varchar(100) DEFAULT NULL,
  smtp_port varchar(30) DEFAULT NULL,
  smtp_username varchar(30) DEFAULT NULL,
  smtp_password varchar(30) DEFAULT NULL,
  smtp_from varchar(100) DEFAULT NULL,
  smtp_from_name varchar(30) DEFAULT NULL,
  smtp_xmailer varchar(100) DEFAULT NULL,
  smtp_host_name varchar(100) DEFAULT NULL,
  created_by varchar(30) NOT NULL,
  updated_by varchar(30) NOT NULL,
  created_dt datetime NOT NULL,
  updated_dt timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY smtp_config_id (smtp_config_id) USING BTREE,
  UNIQUE KEY smtp_config (smtp_config_id,account_id) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;";
        $query = $this->db->query($sql);
    }
}
