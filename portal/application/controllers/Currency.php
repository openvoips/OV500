<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */

class Currency extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('pagination');
        $this->load->model('Utils_model');
        $this->load->model('Currency_mod');
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
                $result = $this->Currency_mod->add($_POST);
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
        $data['currency_dropdown'] = $this->Currency_mod->get_currency(array('currency_id' => 'ASC'), $search_data);
        $this->load->view('basic/header', $data);
        $this->load->view('services/ExcRate', $data);
        $this->load->view('basic/footer', $data);
    }

    public function Exc($arg1 = '', $format = '') {

        $data = array();
        $page_name = "Currencyexc";
        $file_name = 'Currency_' . date('Ymd');
        $is_file_downloaded = false;
        $searching = true;

        //var_dump($_POST);
        ///////////////// Deletion /////////////////	
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('currency', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'currency', 'location', '301');
            }

            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->Currency_mod->delete($delete_param_array);
                if ($result['status'] === true) {
                    $suc_msgs = count($delete_id_array) . ' currency';
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
                $err_msgs = 'Select row to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
            redirect(base_url() . 'currency', 'location', '301');
        }
        $search_data = array();
        $_SESSION['search_currency_data'] = Array();
        $_SESSION['search_currency_data']['s_currency_id']='';
        if (isset($_POST['search_action'])) {
            $this->form_validation->set_rules('currency', 'currency', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_SESSION['search_currency_data'] = array(
                    's_currency_id' => (isset($_POST['currency']) ? trim($_POST['currency']) : ''),
                    'no_of_records' => (isset($_POST['no_of_rows']) ? trim($_POST['no_of_rows']) : ''),
                );
            }
        } else {
            $r = $this->uri->segment(2);
            if ($r == '') {
                $_SESSION['search_currency_data']['s_currency_id'] = isset($_SESSION['search_currency_data']['s_currency_id']) ? $_SESSION['search_currency_data']['s_currency_id'] : '';
            }
            $_SESSION['search_currency_data']['no_of_records'] = isset($_SESSION[$page_name]['no_of_records']) ? $_SESSION[$page_name]['no_of_records'] : RECORDS_PER_PAGE;
        }


        $search_data = array(
            'currency_id' => $_SESSION['search_currency_data']['s_currency_id'],
        );

        $data['currency_dropdown'] = $this->Currency_mod->get_currency(array('currency_id' => 'ASC'), $search_data);

        if ($is_file_downloaded === false) {
            $data['page_name'] = $page_name;
            if ($searching) {
                $pagination_uri_segment = $this->uri->segment(3, 0);
                $order_by = array('id' => 'ASC');

                $response = $this->Currency_mod->get_exchange_rate($order_by, $search_data, 10, $pagination_uri_segment);
                $config = array();
                $config = $this->utils_model->setup_pagination_option($response['total'], 'currency/exc', 10, 3);
                $this->pagination->initialize($config);
                $data['searching'] = 1;
                $data['pagination'] = $this->pagination->create_links();
                $data['listing_data'] = $response['result'];
                $data['total_records']=$data['listing_count'] = $response['total'];
            } else {
                $data['searching'] = 0;
                $data['listing_data'] = array();
                $data['total_records']=$data['listing_count'] = 0;
                $data['currency_dropdown'] = Array();
            }
            $data['search_session_key'] = 'search_currency_data';
            $this->load->view('basic/header', $data);
            $this->load->view('services/exccurrency', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function index($arg1 = '', $format = '') {
        redirect(base_url() . 'currency/exc', 'location', '301');
    }

}
