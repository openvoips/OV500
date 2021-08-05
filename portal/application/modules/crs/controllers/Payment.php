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

class Payment extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('crspayment_mod');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
    }

   public function index($id ) {
		
		 if ($id == -1)
            show_404('403');
		$account_id = param_decrypt($id);
      //  $this->load->model('detail_mod');
        $page_name = "crs_paymenthistory";
        $search_session_key = 'search_' . $page_name;

        $this->load->model('crspayment_mod');
        $this->load->library('pagination'); // pagination class		
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            show_404('403');
        }

        $logged_account_id = get_logged_account_id();

        $search_parameters = array('pay_date', 'payment_type', 'no_of_rows');

        if (isset($_GET['search_action'])) {
			$_POST = $_GET;
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
            'account_id' => $account_id,
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
        } 
		{
            $pagination_uri_segment = 4;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $response = $this->crspayment_mod->paymenthistory('', $per_page, $segment, $search_data);

            $total_count = $this->crspayment_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'crs/payment/index/'.$id, $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;
			$data['account_id']=$account_id;
			
			$data['payment_options'] = $this->crspayment_mod->get_payment_options();

            $this->load->view('basic/header', $data);
            $this->load->view('payment/paymenthistory', $data);
            $this->load->view('basic/footer', $data);
        }
    
		
	}
	function addpayment($id = -1) {
        $page_name = "crs_balance_edit";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if ($id == -1)
            show_404('403');
        if (isset($_POST['action']) && $_POST['action'] == 'OkSearchData') {
            if (isset($_POST['search_account_id']))
                $id = param_encrypt($_POST['search_account_id']);
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('payment_option', 'Payment Option', 'trim|required');
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required|greater_than[0]');
            $this->form_validation->set_rules('paid_on', 'Date-Time', 'trim|required');
            $this->form_validation->set_rules('notes', 'Notes', 'trim');
            $this->form_validation->set_rules('collection_option', 'Collection Method', 'trim');
            $this->form_validation->set_rules('credit_scheduler', 'Credit Revert Scheduler', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $paid_on_timestamp = strtotime($_POST['paid_on']);
                $paid_on = date('Y-m-d H:i:s', $paid_on_timestamp);
                $_POST['paid_on'] = $paid_on;
                if ($_POST['payment_option'] == 'ADDCREDIT' && isset($_POST['credit_scheduler_hour']) && $_POST['credit_scheduler_hour'] != '') {
                    $credit_scheduler_timestamp = strtotime('+' . $_POST['credit_scheduler_hour'] . ' hours');
                    $credit_scheduler = date('Y-m-d H:i:s', $credit_scheduler_timestamp);
                    $_POST['credit_scheduler_execution_date'] = $credit_scheduler;
                    $send_credit_mail = 'yes';
                }

                $result = $this->crspayment_mod->add($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Payment Added Successfully');
                    if (isset($send_credit_mail) && $send_credit_mail == 'yes') {
                        $amount = $_POST['amount'];
                        $removal_hour = $_POST['credit_scheduler_hour'];
                        $account_id = $_POST['account_id'];
                        $option_param = array('currency' => true);
                        $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
                        $mail_to_cc = $mail_to_bcc = '';
                        $heading = 'Temporary Credit';
                        $message = '<p>Dear ' . ucwords(strtolower($account_result['name'])) . ',</p>
				<p>Temporary credit allocated in your account ' . $account_result['currency']['name'] . ' ' . $amount . '. This will automatically be removed from your account in ' . $removal_hour . ' hours.</p>
				<p>Would request to top-up your account asap using portal <a href="' . base_url() . '">' . base_url() . '</a> url by online.</p>';
                        $body = file_get_contents(base_url() . 'email_templates/normal.html');
                        $body = str_replace("#SITE_URL#", base_url(), $body);
                        $body = str_replace("#SITE_LOGO#", SITE_FULL_NAME, $body);
                        $body = str_replace("#HEADING#", $heading, $body);
                        $body = str_replace("#BODY#", $message, $body);
                        $body = str_replace("#SITE_NAME#", 'Kind Regards,<br><strong>' . SITE_FULL_NAME . '</strong>', $body);
                        $subject = 'Added temporary credit in ' . $account_id . ' account';
                        $mail_to = $account_result['emailaddress'];
                        $mail_from = SITE_MAIL_FROM;
                        $mail_from_name = SITE_FULL_NAME;
                        if ($mail_to != '') {
                            send_mail($body, $subject, $mail_to, $mail_from, $mail_from_name, $mail_to_cc, $mail_to_bcc, $account_id, 'TemporaryCredit');
                        }
                    }

                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save') 
                            redirect(base_url() . 'crs/payment/addpayment/' . param_encrypt($account_id), 'location', '301');
                        else
                            redirect(base_url() . 'crs/payment/index/' . param_encrypt($account_id), 'location', '301');
                       
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (!empty($id)) {
            $account_id = param_decrypt($id);
            $option_param = array('payment_history' => true, 'balance' => true, 'currency' => true);
            $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
            if (!$account_result) {
                $data['err_msgs'] = 'Account Not Found';
            } else {
                $is_permitted = true;
                if ($account_result['account_id'] == get_logged_account_id()) {
                    
                } elseif (check_logged_user_group(array(ADMIN_ACCOUNT_ID))) {
                    
                } elseif (check_logged_user_group(array('RESELLER'))) {
                    if ($account_result['parent_account_id'] != get_logged_account_id()) {
                        $data['err_msgs'] = 'Not Permitted1';
                        $is_permitted = false;
                    }
                } else {
                    $data['err_msgs'] = 'Not Permitted2';
                    $is_permitted = false;
                }

                if ($is_permitted) {
                    $data['account_result'] = $account_result;
                    $data['credit_scheduler_result'] = $this->crspayment_mod->get_credit_scheduler(array('account_id' => $account_id, 'status_id' => '0'));
                }

                if (isset($account_result['account_id']) && $account_result['account_id'] == get_logged_account_id()) {
                    $data['page_name'] = 'my_balance';
                } else {
					$data['page_name'] = $page_name;
                    //$data['page_name'] = strtolower($account_result['account_type']) . '_payment_history';
                }
            }
        } else {
            
        }
        $data['payment_options'] = $this->crspayment_mod->get_payment_options();
        $this->load->view('basic/header', $data);
        $this->load->view('payment/add_payment', $data);
        $this->load->view('basic/footer', $data);
    }

  
    function trace() {
        $data['page_name'] = "payment_trace";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		 
		if (check_logged_user_group(array(ADMIN_ACCOUNT_ID))) {
		}else if (!check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {
            show_404('403');
        }
		
        $this->load->library('pagination');

        if (isset($_POST['search_action'])) {// coming from search button
            $_SESSION['search_tracing_data'] = array(
                's_account_id' => $_POST['account_id'],
                's_transact_id' => $_POST['transact_id'],
                's_order_id' => $_POST['order_id'],
                's_order_status' => $_POST['order_status'],
                's_payment_method' => $_POST['payment_method'],
                's_order_date' => $_POST['order_date'],
                's_company_name' => $_POST['company_name'],
                's_card_number' => $_POST['card_number'],
				'no_of_rows' => $_POST['no_of_records'],
            );
        } else {
            $_SESSION['search_tracing_data']['s_account_id'] = isset($_SESSION['search_tracing_data']['s_account_id']) ? $_SESSION['search_tracing_data']['s_account_id'] : '';
            $_SESSION['search_tracing_data']['s_transact_id'] = isset($_SESSION['search_tracing_data']['s_transact_id']) ? $_SESSION['search_tracing_data']['s_transact_id'] : '';
            $_SESSION['search_tracing_data']['s_order_id'] = isset($_SESSION['search_tracing_data']['s_order_id']) ? $_SESSION['search_tracing_data']['s_order_id'] : '';
            $_SESSION['search_tracing_data']['s_order_status'] = isset($_SESSION['search_tracing_data']['s_order_status']) ? $_SESSION['search_tracing_data']['s_order_status'] : '';
            $_SESSION['search_tracing_data']['s_payment_method'] = isset($_SESSION['search_tracing_data']['s_payment_method']) ? $_SESSION['search_tracing_data']['s_payment_method'] : '';
            $_SESSION['search_tracing_data']['s_order_date'] = isset($_SESSION['search_tracing_data']['s_order_date']) ? $_SESSION['search_tracing_data']['s_order_date'] : '';
            $_SESSION['search_tracing_data']['s_company_name'] = isset($_SESSION['search_tracing_data']['s_company_name']) ? $_SESSION['search_tracing_data']['s_company_name'] : '';
            $_SESSION['search_tracing_data']['s_card_number'] = isset($_SESSION['search_tracing_data']['s_card_number']) ? $_SESSION['search_tracing_data']['s_card_number'] : '';
			$_SESSION['search_tracing_data']['no_of_rows'] = isset($_SESSION['search_tracing_data']['no_of_rows']) ? $_SESSION['search_tracing_data']['no_of_rows'] : RECORDS_PER_PAGE;
        }

        if ($_SESSION['search_tracing_data']['s_order_date'] == '') {
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION['search_tracing_data']['s_order_date'] = $time_range;
        }
        $search_data = array(
            'account_id' => $_SESSION['search_tracing_data']['s_account_id'],
            'tracking_id' => $_SESSION['search_tracing_data']['s_transact_id'],
            'order_id' => $_SESSION['search_tracing_data']['s_order_id'],
            'order_status' => $_SESSION['search_tracing_data']['s_order_status'],
            'payment_method' => $_SESSION['search_tracing_data']['s_payment_method'],
            'order_date' => $_SESSION['search_tracing_data']['s_order_date'],
            'company_name' => $_SESSION['search_tracing_data']['s_company_name'],
            'card_number' => $_SESSION['search_tracing_data']['s_card_number'],
        );

		$pagination_uri_segment = 4;
        list($per_page, $segment) = get_pagination_param($pagination_uri_segment, 'search_tracing_data');
			
		
       
        $order_by = '';

        $data['trace_data'] = $this->crspayment_mod->trace_payment_search($order_by, $per_page, $segment, $search_data);

        $total_row = $data['trace_data']['total_row'];


        $config = array();
        $config = $this->utils_model->setup_pagination_option($total_row, 'crs/payment/trace', $per_page, $pagination_uri_segment);
        $this->pagination->initialize($config);

        /*         * **** pagination code ends  here ********* */
        $data['pagination'] = $this->pagination->create_links();
		$data['total_records'] = $total_row;

        $this->load->view('basic/header', $data);
        $this->load->view('payment/trace_listing', $data);
        $this->load->view('basic/footer', $data);
    }

    function trace_details($key = -1) {
        if ($key == -1)
            show_404();
        if (!check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {
            show_404('403');
        }
        $data['page_name'] = "payment_trace";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $order_id = param_decrypt($key);

        $search_data = array('order_id' => $order_id);
        $trace_data = $this->crspayment_mod->trace_payment_search('', 1, '', $search_data);

        if (isset($trace_data['result']) && count($trace_data['result']) > 0)
            $data['trace_data'] = current($trace_data['result']);
        else {
            show_404();
        }
        //	echo '<pre>';print_r($trace_data);echo '</pre>';


        $this->load->view('basic/header', $data);
        $this->load->view('payment/trace_details', $data);
        $this->load->view('basic/footer', $data);
    }


    function delete_scheduler() {
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            $delete_id_array = json_decode($_POST['delete_id']);
            $account_id = $_POST['delete_parameter_two'];
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->crspayment_mod->cancel_scheduler($account_id, $delete_param_array);
                if ($result === true) {
                    $suc_msgs = count($delete_id_array) . ' Credit Scheduler';
                    if (count($delete_id_array) > 1)
                        $suc_msgs .= 's';
                    $suc_msgs .= ' Cancelled Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                }else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } else {
                $err_msgs = 'Select scheduler to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
            }
            redirect(base_url() . 'crs/payment/index/' . param_encrypt($account_id), 'location', '301');
        }
        show_404();
    }

}
