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

class Payment extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('payment_mod');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
    }

    function my_balance() {
        $id = param_encrypt(get_logged_account_id());
        $this->index($id);
    }

    function delete_scheduler() {
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            $delete_id_array = json_decode($_POST['delete_id']);
            $account_id = $_POST['delete_parameter_two'];
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->payment_mod->cancel_scheduler($account_id, $delete_param_array);
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
            redirect(base_url() . 'payment/index/' . param_encrypt($account_id), 'location', '301');
        }
        show_404();
    }

    function index($id = '') {
        $page_name = "balance_edit";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
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

                $result = $this->payment_mod->add($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Payment Added Successfully');
                    if (isset($send_credit_mail) && $send_credit_mail == 'yes') {
                        $amount = $_POST['amount'];
                        $removal_hour = $_POST['credit_scheduler_hour'];
                        $account_id = $_POST['account_id'];
                        $option_param = array('currency' => true);
                        $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
                        $mail_to_cc = $mail_to_bcc = '';
                        /* find account manager email address */
                        if ($account_result['account_manager'] != '') {
                            $account_manager_result = $this->member_mod->get_account_by_key('account_id', $account_result['account_manager']);
                            $mail_to_bcc = $account_manager_result['emailaddress'];
                        }

                        //send mail to user
                        $heading = 'Temporary Credit'; //'Credit Added';
                        $message = '<p>Dear ' . ucwords(strtolower($account_result['name'])) . ',</p>
				<p>We have allocated ' . $account_result['currency']['name'] . ' ' . $amount . ' of temporary credit to your account. This will automatically be removed in ' . $removal_hour . ' hours.</p>
				<p>You can top-up any time on our portal <a href="' . base_url() . '">' . base_url() . '</a> using your card or PayPal.</p>';
                        $body = file_get_contents(base_url() . 'email_templates/yellow.html');
                        $body = str_replace("#SITE_URL#", base_url(), $body);
                        $body = str_replace("#SITE_LOGO#", SITE_FULL_NAME, $body);
                        $body = str_replace("#HEADING#", $heading, $body);
                        $body = str_replace("#BODY#", $message, $body);
                        $body = str_replace("#SITE_NAME#", 'Kind regards,<br><strong>' . SITE_FULL_NAME . '</strong>', $body);
                        $subject = 'Temporary credit in ' . $account_id . ' account';


                        $mail_to = $account_result['emailaddress'];
                        $mail_from = SITE_MAIL_FROM;
                        $mail_from_name = SITE_FULL_NAME;
                        if ($mail_to != '') {
                            send_mail($body, $subject, $mail_to, $mail_from, $mail_from_name, $mail_to_cc, $mail_to_bcc, $account_id, 'TemporaryCredit');
                        }
                    }
                    //	die("die");				
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'payment/index/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close') {
                            ////fetch user type//
                            $account_result = $this->member_mod->get_account_by_key('account_id', $account_id);
                            if ($account_result['account_type'] == 'CUSTOMER')
                                $redirect_page = 'customerss';
                            elseif ($account_result['account_type'] == 'RESELLER')
                                $redirect_page = 'resellers';                            
                            else
                                $redirect_page = 'dashboard';
                            redirect(base_url() . $redirect_page, 'location', '301');
                        }
                    }
                    else {
                        redirect(base_url() . 'payment/index/' . param_encrypt($account_id), 'location', '301');
                    }

                    redirect(base_url() . 'payment/index/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveMaxcredit') {
            $account_id = $_POST['account_id'];
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('maxcredit_limit', 'Max Credit Limit', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->payment_mod->update_max_credit_limit(array('account_id' => trim($_POST['account_id']), 'maxcredit_limit' => trim($_POST['maxcredit_limit'])));
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Max Credit Limit Updated Successfully');
                    redirect(base_url() . 'payment/index/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (!empty($id)) {
            $account_id = param_decrypt($id);

            /* fetch user details and permissions */
            $option_param = array('payment_history' => true, 'balance' => true, 'currency' => true);
            $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
            if (!$account_result) {
                $data['err_msgs'] = 'Account Not Found';
            } else {
                $is_permitted = true;
                if ($account_result['account_id'] == get_logged_account_id()) {
                    
                } elseif (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                    
                } elseif (check_logged_account_type(array('RESELLER'))) {
                    if ($account_result['parent_account_id'] != get_logged_account_id()) {
                        $data['err_msgs'] = 'Not Permitted';
                        $is_permitted = false;
                    }
                } else {
                    $data['err_msgs'] = 'Not Permitted';
                    $is_permitted = false;
                }

                if ($is_permitted) {
                    $data['account_result'] = $account_result;
                    $data['credit_scheduler_result'] = $this->payment_mod->get_credit_scheduler(array('account_id' => $account_id, 'status_id' => '0'));
                }

                if (isset($account_result['account_id']) && $account_result['account_id'] == get_logged_account_id()) {
                    $data['page_name'] = 'my_balance';
                } else {
                    $data['page_name'] = strtolower($account_result['account_type']) . '_payment_history';
                }
            }
        } else {
            
        }

        $data['payment_options'] = $this->payment_mod->get_payment_options();
        $this->load->view('basic/header', $data);
        $this->load->view('payment/payment_add', $data);
        $this->load->view('basic/footer', $data);
    }

    function save_card_details() {
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            $this->form_validation->set_rules('card_number', 'card_number', 'trim|required');
            $this->form_validation->set_rules('card_expirymonth', 'Expiry Month', 'trim|required');
            $this->form_validation->set_rules('card_expiryyear', 'Expiry Year', 'trim|required');
            $this->form_validation->set_rules('card_securitycode', 'Security Code', 'trim|required');

            if ($this->form_validation->run() == FALSE) {// error
                echo validation_errors();
            } else {
                $result = $this->payment_mod->save_card_details($_POST);
                //	var_dump($result);
                if ($result === true) {
                    
                }
            }
        }
    }

    function remove_card_details() {
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            $account_id = $_POST['delete_parameter_two'];
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->payment_mod->delete_card_details($account_id, $delete_param_array);
                if ($result === true) {
                    $suc_msgs = 'Card Details Removed Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                }
                //redirect(base_url().'payment/make_payment', 'location', '301');		
            } else {
                
            }
        }
        redirect(base_url() . 'payment/make_payment', 'location', '301');
    }

    function make_payment($order_id = -1) {
        $page_name = "make_payment";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $view_page = "make_payment";

        $account_id = get_logged_account_id();
        /* if(!in_array($account_id, array('EN000007690','EN000210311')))
          {
          show_404('under_maintaaince');
          } */
        $option_param = array('currency' => true);
        $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);



        if ($account_result['account_type'] == 'DEMO') {
            show_404('404_demo_account');
        }
        $account_type = $account_result['account_type'];
        $payment_gateways_result = array();
        if ($account_result['account_level'] == '1' and in_array($account_type, array('RESELLER'))) {
            $payment_gateways_result = $this->payment_mod->get_payment_gateways('ADMIN');
        } elseif (strlen($account_result['parent_account_id']) == 0 and in_array($account_type, array('CUSTOMER'))) {
            $payment_gateways_result = $this->payment_mod->get_payment_gateways('ADMIN');
        } elseif ($account_result['account_level'] == '2' and in_array($account_type, array('RESELLER'))) {
            $payment_gateways_result = $this->payment_mod->get_payment_gateways($account_result['parent_account_id']);
        } elseif ($account_result['account_level'] == '3' and in_array($account_type, array('RESELLER'))) {
            $payment_gateways_result = $this->payment_mod->get_payment_gateways($account_result['parent_account_id']);
        } elseif (in_array($account_type, array('CUSTOMER'))) {
            $payment_gateways_result = $this->payment_mod->get_payment_gateways($account_result['parent_account_id']);
        }
        $data['account_result'] = $account_result;
        $data['payment_gateways_result'] = $payment_gateways_result;
        if (isset($_POST['encResp']) && $_POST['encResp'] != '') {//response from ccavenue
            $this->load->helper('ccavenue');
            $encResp = trim($_POST['encResp']);
            if ($order_id == -1) {
                show_404();
            }
            $this->process_ccavenue_payment($account_id, $order_id, $payment_gateways_result, $account_type, $encResp, $account_result);
        } elseif (isset($_GET['tx']) && $_GET['tx'] != '') {//response from paypal
            $tx = trim($_GET['tx']);
            if ($order_id == -1) {
                show_404();
            }
            $this->process_paypal_payment($account_id, $order_id, $payment_gateways_result, $account_type, $tx, $account_result);
        } elseif (isset($_POST['cachetoken']) && $_POST['cachetoken'] != '' && $_POST['amount'] != '') {//response from secure trading
            $cachetoken = $_POST['cachetoken'];
            $amount = $_POST['amount'];
            //	die("AAA");
            $this->process_secure_trading_payment($account_id, $cachetoken, $payment_gateways_result, $account_type, $account_result, $amount);
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkPay') {//	payment form submitted
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
            $this->form_validation->set_rules('method', 'Method', 'trim|required|in_list[paypal,ccavenue,paypal-sdk]', array('in_list' => 'You must provide a %s.')
            );

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
                //echo 'errror';
            } else {
                $amount = $_POST['amount'];
                $method = $_POST['method'];

                $payment_gateway_data = $payment_gateways_result['result'][$method];
                $this->initiate_payment($account_id, $amount, $method, $account_result, $payment_gateway_data);
                $view_page = "loading";
            }
        }
        if ($view_page == "make_payment") {
            //fetch saved card details
            $saved_card_result = $this->payment_mod->get_card_details($account_id);
            $data['saved_card_result'] = $saved_card_result['result'];

            $this->load->view('basic/header', $data);
            $this->load->view('payment/make_payment', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    /* keep record before payment initiate */

    function initiate_payment($account_id, $amount, $method, $account_result, $payment_gateway_data) {
        $item_name = 'Microtalk payment';
        $credentials = json_decode($payment_gateway_data['credentials'], true);

        $order_id = str_replace(' ', '', $account_id);
        $order_id = str_replace('-', '', $order_id);
        $order_id = preg_replace('/[^A-Za-z0-9\-]/', '', $order_id);
        $order_id = 'ORD' . $order_id . generateRandom(3);

        if ($method == 'ccavenue') {
            $this->load->helper('ccavenue');

            $merchant_id = $credentials['merchant_id'];
            $working_key = $credentials['working_key'];
            $access_code = $credentials['access_code'];

            $redirect_url = base_url() . 'payment/make_payment/' . $order_id;
            $cancel_url = base_url() . 'payment/make_payment';

            $language = 'EN';

            $currency = $account_result['currency']['name'];

            //////
            $billing_name = $account_result['name'];
            $billing_email = $account_result['emailaddress'];
            $billing_country = 'India';
            $billing_address = '';
            $billing_city = '';
            $billing_state = '';
            $billing_zip = '';
            $billing_tel = '';
            ///////	
            $delivery_name = '';
            $delivery_address = '';
            $delivery_city = '';
            $delivery_state = '';
            $delivery_zip = '';
            $delivery_country = '';
            $delivery_tel = '';
            /////
            $merchant_param1 = $account_id;
            $merchant_param2 = base_url();
            $merchant_param3 = $account_id;
            $merchant_param4 = $merchant_param5 = $promo_code = '';
            $customer_identifier = '';
            /////

            $merchant_data = 'merchant_id=' . $merchant_id . '&order_id=' . $order_id . '&amount=' . $amount . '&currency=' . $currency . '&redirect_url=' . $redirect_url .
                    '&cancel_url=' . $cancel_url . '&language=' . $language . '&billing_name=' . $billing_name . '&billing_address=' . $billing_address .
                    '&billing_city=' . $billing_city . '&billing_state=' . $billing_state . '&billing_zip=' . $billing_zip . '&billing_country=' . $billing_country .
                    '&billing_tel=' . $billing_tel . '&billing_email=' . $billing_email . '&delivery_name=' . $delivery_name . '&delivery_address=' . $delivery_address .
                    '&delivery_city=' . $delivery_city . '&delivery_state=' . $delivery_state . '&delivery_zip=' . $delivery_zip . '&delivery_country=' . $delivery_country .
                    '&delivery_tel=' . $delivery_tel . '&merchant_param1=' . $merchant_param1 . '&merchant_param2=' . $merchant_param2 .
                    '&merchant_param3=' . $merchant_param3 . '&merchant_param4=' . $merchant_param4 . '&merchant_param5=' . $merchant_param5 . '&promo_code=' . $promo_code .
                    '&customer_identifier=' . $customer_identifier;

            $encrypted_data = encrypt($merchant_data, $working_key); // Method for encrypting the data.

            $data['encrypted_data'] = $encrypted_data;
            $data['access_code'] = $access_code;

            $this->payment_mod->initiate_payment($account_id, $amount, array('order_id' => $order_id, 'merchant_data' => $merchant_data), 'ccavenue');

            $this->load->view('basic/header', $data);
            $this->load->view('payment/cc_loading', $data);
        } elseif ($method == 'paypal-sdk') {//paypal standard nvp
            $this->load->helper('paypal');

            //$pay_data_array['paypal_email'] = 'micro@mailinator.com';
            $pay_data_array['business'] = $credentials['business']; //'tonmoytewary-facilitator@gmail.com';//$payment_gateways_result
            $pay_data_array['amount'] = $amount;

            /////			
            $pay_data_array['return'] = base_url() . 'payment/make_payment/' . $order_id;
            $pay_data_array['cancel_return'] = base_url() . 'payment/make_payment';
            $pay_data_array['notify_url'] = base_url() . 'payment/payment_notify';
            $pay_data_array['order_id'] = $order_id;
            $pay_data_array['currency_code'] = $account_result['currency']['name'];

            $first_name = $account_result['name'];
            $last_name = '';
            $pos = strpos($account_result['name'], ' ');
            if ($pos !== false) {
                $first_name = substr($account_result['name'], 0, $pos);
                $last_name = substr($account_result['name'], $pos);
            }

            $pay_data_array['payer_first_name'] = $first_name;
            $pay_data_array['payer_last_name'] = $last_name;
            $pay_data_array['payer_email'] = $account_result['emailaddress'];

            $pay_data_array['item_number'] = "1";
            $pay_data_array['rm'] = "2";
            $pay_data_array['cmd'] = "_xclick";
            $pay_data_array['item_name'] = "Payment";
            $pay_data_array['no_shipping'] = "0";
            $pay_data_array['no_note'] = "1";
            //echo '<pre>';print_r($pay_data_array);print_r($account_result);die;
            ////////////////////			
            $this->payment_mod->initiate_payment($account_id, $amount, $pay_data_array, 'paypal');

            $data['pay_data_array'] = $pay_data_array;
            //echo 'okkkkkkkkkkk 111';
            $this->load->view('basic/header', $data);
            $this->load->view('payment/paypal_loading', $data);
        }
    }

    /* keep record before payment initiate
      ajax submit
     */

    function secure_trading_initiate_payment() {
        $data['encrypted_data'] = $encrypted_data;
        $data['access_code'] = $access_code;
        $ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

        $account_id = $_POST['account_id'];
        $amount = $_POST['amount'];
        $order_id = 'ORD' . md5($_POST['cachetoken']);
        $method = $_POST['method'];


        $card_number = isset($_POST['card_number']) ? $_POST['card_number'] : '';
        $card_expirymonth = isset($_POST['card_expirymonth']) ? $_POST['card_expirymonth'] : '';
        $card_expiryyear = isset($_POST['card_expiryyear']) ? $_POST['card_expiryyear'] : '';




        $pay_data_array = array('order_id' => $order_id, 'card_number' => $card_number, 'card_expirymonth' => $card_expirymonth, 'card_expiryyear' => $card_expiryyear);

        $response = $this->payment_mod->initiate_payment($account_id, $amount, $pay_data_array, 'secure_trading');

        if ($ajax) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: ' . date('r', time() + (86400 * 365)));
            header('Content-type: application/json');

            $valid = false;
            $error = '';

            if ($response === true)
                $valid = true;
            else
                $error = $response;

            echo json_encode(array(
                'valid' => $valid,
                'error' => $error,
            ));
            exit();
        }
    }

    /*
      process paypal response data
     */

    function process_paypal_payment($account_id, $order_id, $payment_gateways_result, $account_type, $tx, $account_result) {
        $sitesetup_data = $this->sitesetup_mod->get_sitesetup_data();
        //check database, if status not initiated, then omit			
        $payment_data = $this->payment_mod->check_payment($account_id, $order_id);
        if (isset($payment_data['result']) && $payment_data['result']['order_status'] == 'initiated') {
            //ok proceed
        } else {
            show_404();
        }

        $credentials = json_decode($payment_gateways_result['result']['paypal-sdk']['credentials'], true);

        //verify token
        $your_pdt_identity_token = $credentials['pdt_identity_token'];

        $request = curl_init();
        curl_setopt_array($request, array
            (
            CURLOPT_URL => PAYPAL_LINK,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => http_build_query(array
                (
                'cmd' => '_notify-synch',
                'tx' => $tx,
                'at' => $your_pdt_identity_token,
            )),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER => FALSE,
                // CURLOPT_SSL_VERIFYPEER => TRUE,
                // CURLOPT_CAINFO => 'cacert.pem',
        ));
        $response = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        if (strpos($response, 'FAIL') === 0) {
            //invalid data
        } else {//format & keep data
            // Remove SUCCESS part (7 characters long)
            $response_formatted = substr($response, 7);
            $response_formatted = urldecode($response_formatted);
            // Turn into associative array
            preg_match_all('/^([^=\s]++)=(.*+)/m', $response_formatted, $m, PREG_PATTERN_ORDER);
            $response_formatted = array_combine($m[1], $m[2]);
            // Fix character encoding if different from UTF-8 (in my case)
            if (isset($response_formatted['charset']) AND strtoupper($response_formatted['charset']) !== 'UTF-8') {
                foreach ($response_formatted as $key => &$value) {
                    $value = mb_convert_encoding($value, 'UTF-8', $response_formatted['charset']);
                }
            }

            // Sort on keys for readability 
            ksort($response_formatted);

            if ($status == 200 AND strpos($response, 'SUCCESS') === 0) {
                $payment_response_data = array('response_string' => $response_formatted, 'tracking_id' => $response_formatted['txn_id'], 'order_status' => 'success', 'account_type' => $account_type);
                //update payment status
                $response_data = $this->payment_mod->update_payment($account_id, $order_id, $payment_response_data, 'paypal');

                $suc_msgs .= 'Payment successfully added';
                $this->session->set_flashdata('suc_msgs', $suc_msgs);
                ////send mail to Admin

                $payment_method = 'Paypal';
                $amount = $response_formatted['mc_gross']; //$response_formatted['payment_gross'];
                $transaction_id = $tx;
                $this->send_confirmation_mail($account_id, $payment_method, $amount, $transaction_id);



                redirect(base_url() . 'payment/my_balance', 'location', '301');
            } else {
                //not successfull, keep response data
                $payment_response_data = array('response_string' => $response_formatted, 'order_status' => 'failed');
                //update payment status
                $response_data = $this->payment_mod->update_payment($account_id, $order_id, $payment_response_data, 'paypal');

                $this->session->set_flashdata('err_msgs', 'Your payment is not successfull');
                redirect(base_url() . 'payment/make_payment', 'location', '301');
            }
        }
    }

    /*
      process ccavenue response data
     */

    function process_ccavenue_payment($account_id, $order_id, $payment_gateways_result, $account_type, $encResponse, $account_result) {
        $sitesetup_data = $this->sitesetup_mod->get_sitesetup_data();
        //check database, if status not initiated, then omit			
        $payment_data = $this->payment_mod->check_payment($account_id, $order_id);
        if (isset($payment_data['result']) && $payment_data['result']['order_status'] == 'initiated') {
            //ok proceed
        } else {
            show_404();
        }

        $credentials = json_decode($payment_gateways_result['result']['ccavenue']['credentials'], true);

        //verify token
        $working_key = $credentials['working_key'];

        //$encResponse=$_POST["encResp"];			//This is the response sent by the CCAvenue Server
        $rcvdString = decrypt($encResponse, $working_key);  //Crypto Decryption used as per the specified working key.
        $order_status = "";
        $decryptValues = explode('&', $rcvdString);
        $dataSize = sizeof($decryptValues);
        $return_values = array();

        $order_status = '';
        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);
            if ($i == 3)
                $order_status = $information[1];
        }


        if ($order_status == "") {
            //invalid data
        } else {//format & keep data
            $response_formatted = array();
            for ($i = 0; $i < $dataSize; $i++) {
                $information = explode('=', $decryptValues[$i]);
                $key = $information[0];
                $response_formatted[$key] = $information[1];
                //echo '<tr><td>'.$information[0].'</td><td>'.$information[1].'</td></tr>';
            }

            // Sort on keys for readability 
            ksort($response_formatted);

            if ($order_status == "Success") {
                $payment_response_data = array('response_string' => $response_formatted, 'tracking_id' => $response_formatted['tracking_id'], 'order_status' => 'success', 'account_type' => $account_type);
                //update payment status
                $response_data = $this->payment_mod->update_payment($account_id, $order_id, $payment_response_data, 'ccavenue');

                $suc_msgs .= 'Payment successfully added';
                $this->session->set_flashdata('suc_msgs', $suc_msgs);


                $amount = $response_formatted['amount'];
                $payment_method = 'CC Avenue';
                $transaction_id = $response_formatted['tracking_id'];

                $this->send_confirmation_mail($account_id, $payment_method, $amount, $transaction_id);
                redirect(base_url() . 'payment/my_balance', 'location', '301');
            } else {
                //not successfull, keep response data
                $payment_response_data = array('response_string' => $response_formatted, 'order_status' => 'failed');
                //$response_formatted['order_status']			
                //update payment status
                $response_data = $this->payment_mod->update_payment($account_id, $order_id, $payment_response_data, 'ccavenue');

                $this->session->set_flashdata('err_msgs', 'Your payment is not successfull');
                redirect(base_url() . 'payment/make_payment', 'location', '301');
            }
        }
    }

    /*
      process secure-trading response data
     */

    function process_secure_trading_payment($account_id, $cachetoken, $payment_gateways_result, $account_type, $account_result, $amount) {
        require_once('theme/vendors/secure-trading/autoload.php');
        $sitesetup_data = $this->sitesetup_mod->get_sitesetup_data();
        //check database, if status not initiated, then omit	
        $order_id = 'ORD' . md5($cachetoken);
        $payment_data = $this->payment_mod->check_payment($account_id, $order_id);
        //echo $order_id;	echo '<pre>';print_r($payment_data);echo '</pre>';

        if (isset($payment_data['result']) && $payment_data['result']['order_status'] == 'initiated') {
            //ok proceed
        } else {
            show_404();
        }
        $credentials = json_decode($payment_gateways_result['result']['secure-trading']['credentials'], true);
        //verify token
        //$working_key = $credentials['working_key'];	



        $configData = array(
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        );
        //Replace the example Web Services username and password above with your own

        $orderreference = 'MT4,' . $account_id;
        $billingfirstname = $billingmiddlename = $billinglastname = '';
        if ($account_result['name'] != '') {
            $name = $account_result['name'];
            $name = preg_replace("/[^[:alnum:][:space:]]/u", '', $name);
            $name_array = explode(' ', $name);
            $name_word_count = count($name_array);
            $billingfirstname = $billingmiddlename = $billinglastname = '';

            $billingfirstname = $name_array[0];
            if ($name_word_count > 1)
                $billinglastname = $name_array[$name_word_count - 1];
            if ($name_word_count > 2)
                $billingmiddlename = $name_array[1];
        }

        $customerip = getUserIP();

        $baseamount = $amount * 100;
        $requestData = array(
            'sitereference' => $credentials['sitereference'],
            'requesttypedescriptions' => array('AUTH'),
            'accounttypedescription' => 'MOTO', // 'ECOM',
            'currencyiso3a' => $account_result['currency']['name'],
            'baseamount' => "$baseamount",
            'orderreference' => $orderreference,
            'cachetoken' => $cachetoken,
            'billingfirstname' => $billingfirstname,
            'billingmiddlename' => $billingmiddlename,
            'billinglastname' => $billinglastname,
            'customerip' => $customerip,
        );
        //echo '<pre>'; print_r($requestData);
        $api = \Securetrading\api($configData);
        $response = $api->process($requestData);
        $response_array = $response->toArray();
        $response_formatted = $response_array;

        //print_r($response_array);		die;

        $errorcode = $response_formatted['responses']['0']['errorcode'];
        $errormessage = $response_formatted['responses']['0']['errormessage'];

        $settlestatus = $response_formatted['responses']['0']['settlestatus'];

        if ($errorcode == 0) {
            //https://docs.securetrading.com/document/api/paymenttypedescription/paypal/settlement/
            $blocked_settlement = array(2 => 'Transaction is suspended ', 3 => 'Transaction is cancelled');
            if (in_array($settlestatus, $blocked_settlement)) {//settlement blocked
                $payment_response_data = array('response_string' => $response_formatted, 'tracking_id' => $response_formatted['responses']['0']['transactionreference'], 'order_status' => 'not_accepted', 'account_type' => $account_type);
                //update payment status
                $response_data = $this->payment_mod->update_payment($account_id, $order_id, $payment_response_data, 'secure-trading');

                $errormessage = $blocked_settlement[$settlestatus];
                $this->session->set_flashdata('err_msgs', $errormessage);
                redirect(base_url() . 'payment/make_payment', 'location', '301');
            } else {
                $payment_response_data = array('response_string' => $response_formatted, 'tracking_id' => $response_formatted['responses']['0']['transactionreference'], 'order_status' => 'success', 'account_type' => $account_type);
                //update payment status
                $response_data = $this->payment_mod->update_payment($account_id, $order_id, $payment_response_data, 'secure-trading');

                $suc_msgs .= 'Payment successfully added';
                $this->session->set_flashdata('suc_msgs', $suc_msgs);

                $payment_method = 'Secure Trading';
                // $amount = $amount;
                $transaction_id = $response_formatted['responses'][0]['transactionreference'];
                $this->send_confirmation_mail($account_id, $payment_method, $amount, $transaction_id);
                redirect(base_url() . 'payment/my_balance', 'location', '301');
            }
        } else {
            //not successfull, keep response data
            $payment_response_data = array('response_string' => $response_formatted, 'order_status' => 'failed');

            //update payment status
            $response_data = $this->payment_mod->update_payment($account_id, $order_id, $payment_response_data, 'secure-trading');

            if ($errormessage == '')
                $errormessage = 'Your payment is not successfull';
            $this->session->set_flashdata('err_msgs', $errormessage);
            redirect(base_url() . 'payment/make_payment', 'location', '301');
        }
    }

    /*
      send mail
     */

    function send_confirmation_mail($account_id, $payment_method, $amount, $transaction_id) {
        $option_param = array('currency' => true, 'balance' => true);
        $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
        $account_type = $account_result['account_type'];
        $company_name = preg_replace("/[^A-Za-z0-9 ]/", '', $account_result['company_name']);
        ;

        /* find account manager email address */
        if ($account_result['account_manager'] != '') {
            $account_manager_result = $this->member_mod->get_account_by_key('account_id', $account_result['account_manager']);
        }

        //echo '<pre>';print_r($account_result );echo '</pre>';
        //$sitesetup_data = $this->sitesetup_mod->get_sitesetup_data();
        //send mail to admin
        $heading = 'Payment Confirmation';
        $message = 'One customer made payment.';

        $message .= '<table  width="100%" cellspacing="2" cellpadding="2" border="0">
		<tr><td align="left" valign="top" colspan="2"><strong style="color: rgb(186, 0, 0);">Details</strong></td></tr>				
		<tr>
		<td width="36%" align="left" valign="middle">Account ID:</td>
		<td width="64%" align="left" valign="middle">' . $account_id . '</td>
		</tr>
		<tr>
		<td width="36%" align="left" valign="middle">User type:</td>
		<td width="64%" align="left" valign="middle">' . $account_type . '</td>
		</tr>
		
		<tr>
		<td align="left" valign="middle">Gateway:</td>
		<td align="left" valign="middle">' . $payment_method . '</td>
		</tr>
		<tr>
		<td align="left" valign="middle">Amount:</td>
		<td align="left" valign="middle">' . $amount . '</td>
		</tr>
		<tr>
		<td align="left" valign="middle">Trans. ID:</td>
		<td align="left" valign="middle">' . $transaction_id . '</td>
		</tr>
		</table>';
        $body = file_get_contents(base_url() . 'email_templates/blue.html');
        $body = str_replace("#SITE_URL#", base_url(), $body);
        $body = str_replace("#SITE_LOGO#", SITE_FULL_NAME, $body);
        $body = str_replace("#HEADING#", $heading, $body);
        $body = str_replace("#BODY#", $message, $body);
        $body = str_replace("#SITE_NAME#", '<strong>' . SITE_FULL_NAME . '</strong>', $body);
        $subject = 'Payment Confirmation( ' . $account_id . ' )( ' . $company_name . ' )';


        $mail_to = SITE_MAIL_TO;
        if (isset($account_manager_result['emailaddress']) && $account_manager_result['emailaddress'] != '') {
            $mail_to .= ', ' . $account_manager_result['emailaddress'];
        }
        $mail_from = SITE_MAIL_FROM;
        $mail_from_name = SITE_FULL_NAME;


        //attachment
        $attachment_array = array();

        $file_path = '/home/telcoportal/webroot/switch/mt/uploads/' . strtolower(SITE_SUBDOMAIN) . '/payment_receipt/' . $account_id . '/';
        $file_full_path = $file_path . $transaction_id . '.pdf';
        if (file_exists($file_full_path)) {
            $attachment_array[] = array($file_full_path, 'paymentreceipt.pdf');
        }


        send_mail($body, $subject, $mail_to, $mail_from, $mail_from_name, '', '', '', 'PaymentConfirmation', $attachment_array);

        /////////send mail to user
        $heading = 'Payment Confirmation';
        $message = 'Dear ' . $account_result['name'] . ', <br><br>
		Thank you for your payment.<br><br>';

        $message .= 'Your account (' . $account_id . ') payment transaction ID is <strong>' . $transaction_id . '</strong>';

        if (isset($account_result['balance']['balance']))
            $message .= ' and after payment, the current balance is ' . (-$account_result['balance']['balance']) . ' ' . $account_result['currency']['name'] . '.';

        $mail_footer = 'Thanks & Regards,<br>'
                . '<strong>' . SITE_FULL_NAME . '</strong><br>'
                . '<strong>Email</strong>: creditcontrol@microtalkgroup.com<br>'
                . '<strong>Website</strong>: ' . base_url();


        $body = file_get_contents(base_url() . 'email_templates/yellow.html');
        $body = str_replace("#SITE_URL#", '', $body);
        $body = str_replace("#SITE_LOGO#", SITE_FULL_NAME, $body);
        $body = str_replace("#HEADING#", $heading, $body);
        $body = str_replace("#BODY#", $message, $body);
        $body = str_replace("#SITE_NAME#", $mail_footer, $body);
        $subject = 'Thank you for your payment';

        $mail_to = $account_result['emailaddress'];
        $mail_from = SITE_MAIL_FROM;
        $mail_from_name = SITE_FULL_NAME;
        if ($mail_to != '') {
            send_mail($body, $subject, $mail_to, $mail_from, $mail_from_name, '', '', $account_id, 'PaymentConfirmation');
        }



        //	echo $body;
    }

    /* display payment listing */

    function trace() {
        $data['page_name'] = "payment_trace";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC', 'CREDITCONTROL', 'SALESMANAGER'))) {
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

        $pagination_uri_segment = 3;
        $per_page = RECORDS_PER_PAGE;

        if ($this->uri->segment($pagination_uri_segment) == '') {
            $segment = 0;
        } else {
            $segment = $this->uri->segment($pagination_uri_segment);
        }
        $order_by = '';

        $data['trace_data'] = $this->payment_mod->trace_payment_search($order_by, $per_page, $segment, $search_data);

        $total_row = $data['trace_data']['total_row'];


        $config = array();
        $config = $this->utils_model->setup_pagination_option($total_row, 'payment/trace', $per_page, $pagination_uri_segment);
        $this->pagination->initialize($config);

        /*         * **** pagination code ends  here ********* */
        $data['pagination'] = $this->pagination->create_links();

        $this->load->view('basic/header', $data);
        $this->load->view('payment/trace_listing', $data);
        $this->load->view('basic/footer', $data);
    }

    /* display payment details by order id */

    function trace_details($key = -1) {
        if ($key == -1)
            show_404();
        if (!check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC', 'CREDITCONTROL', 'SALESMANAGER'))) {
            show_404('403');
        }
        $data['page_name'] = "payment_trace";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $order_id = param_decrypt($key);

        $search_data = array('order_id' => $order_id);
        $trace_data = $this->payment_mod->trace_payment_search('', 1, '', $search_data);

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

    function save_payment_attempt() {
        $final_return_array = array();
        try {
            if (!isset($_POST['account_id']) || !isset($_POST['amount']) || !isset($_POST['method']) || !isset($_POST['card_number']) || !isset($_POST['card_expirymonth']) || !isset($_POST['card_expiryyear']) || !isset($_POST['card_securitycode'])) {
                throw new Exception('Insuffcient data');
            }

            foreach ($_POST as $post_key => $post_value) {
                $_POST[$post_key] = trim($post_value);
            }

            if ($_POST['account_id'] == '' || $_POST['amount'] == '' || $_POST['method'] == '' || $_POST['card_number'] == '' || $_POST['card_expirymonth'] == '' || $_POST['card_expiryyear'] == '' || $_POST['card_securitycode'] == '') {
                throw new Exception('Data empty');
            }

            $account_id = $_POST['account_id'];
            $amount = $_POST['amount'];
            $payment_method = $_POST['method'];
            $order_id = 'ATT_' . DATE('jm') . $account_id . rand(1, 10000);

            $card_number = $_POST['card_number'];
            $card_expirymonth = $_POST['card_expirymonth'];
            $card_expiryyear = $_POST['card_expiryyear'];
            //if save card details checked
            $card_securitycode = isset($_POST['card_securitycode']) ? $_POST['card_securitycode'] : '';
            $is_save_card = isset($_POST['is_save_card']) ? $_POST['is_save_card'] : 'yes';

            /* save card details for tracking start */
            $payment_tracking_array = array();
            $pay_data_array = array('order_id' => $order_id, 'card_number' => $card_number, 'card_expirymonth' => $card_expirymonth, 'card_expiryyear' => $card_expiryyear);
            $payment_tracking_array['account_id'] = $account_id;
            $payment_tracking_array['amount'] = $amount;
            $payment_tracking_array['payment_method'] = $payment_method;
            $payment_tracking_array['order_id'] = $order_id;
            $payment_tracking_array['order_status'] = 'card_attempt';
            $payment_tracking_array['send_string'] = print_r($pay_data_array, true);

            $str = $this->db->insert_string($this->db->dbprefix('payment_tracking'), $payment_tracking_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            //$payment_id = $this->db->insert_id();		
            /* save card details for tracking end */


            /* check card should block or not start */

            //1. check blocking table
            $sql = "SELECT block_text, reason FROM " . $this->db->dbprefix('payment_blocking') . " WHERE block_text ='" . $account_id . "' OR block_text ='" . $card_number . "' LIMIT 1";
            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows > 0) {
                foreach ($query->result_array() as $row) {
                    $block_text = $row['block_text'];
                    if ($block_text == $account_id) {
                        throw new Exception('Your account is blocked for card payment');
                    } elseif ($block_text == $card_number) {
                        throw new Exception('This card is blocked for card payment');
                    }
                }
            }
            //2. check too many attempts
            $sql = "SELECT count(*) total_attempt FROM " . $this->db->dbprefix('payment_tracking') . " WHERE order_status ='card_attempt' AND attempt_check ='Y' AND account_id ='" . $account_id . "' AND order_date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)";
            $query = $this->db->query($sql);
            $row = $query->row_array();
            if ($row['total_attempt'] > 3) {
                throw new Exception('Too many attempts.We cannot process your request now');
            }
            /*
              future development
              if we need to clear one customer from checking just change status attempt_check ='N' */


            /* check card should block or not end */

            /* save card data if checked and not blocked start */
            if ($is_save_card === 'yes') {
                $card_name = '';
                $card_number_length = strlen($card_number);
                for ($i = 0; $i < $card_number_length; $i++) {
                    if ($i < $card_number_length - 4) {
                        $card_name .= 'x';
                    } else {
                        $card_name .= $card_number[$i];
                    }
                }


                /* check card exists or not */
                $sql = "SELECT id FROM " . $this->db->dbprefix('account_card_details') . " WHERE account_id='" . $account_id . "' AND card_name='" . $card_name . "'";
                $query = $this->db->query($sql);
                $num_rows = $query->num_rows();
                if ($num_rows > 0)
                    $update_or_insert = 'update';
                else
                    $update_or_insert = 'insert';

                $card_data_array = array();
                if ($update_or_insert == 'update') {
                    $card_data_en_array = array(
                        'card_number' => $card_number,
                        'expirymonth' => $card_expirymonth,
                        'expiryyear' => $card_expiryyear,
                        'securitycode' => $card_securitycode,
                    );
                    $card_data_en = json_encode($card_data_en_array);
                    $card_data_array['card_data'] = base64_encode($card_data_en);
                    $card_data_array['card_name'] = $card_name;

                    $where = "account_id='" . $account_id . "' AND card_name='" . $card_name . "'";
                    $str = $this->db->update_string($this->db->dbprefix('account_card_details'), $card_data_array, $where);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                } elseif ($update_or_insert == 'insert') {
                    $card_data_array['account_id'] = $account_id;
                    $card_data_array['card_name'] = $card_name;

                    $card_data_en_array = array(
                        'card_number' => $card_number,
                        'expirymonth' => $card_expirymonth,
                        'expiryyear' => $card_expiryyear,
                        'securitycode' => $card_securitycode,
                    );
                    $card_data_en = json_encode($card_data_en_array);
                    $card_data_array['card_data'] = base64_encode($card_data_en);


                    $str = $this->db->insert_string($this->db->dbprefix('account_card_details'), $card_data_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                }
            }
            /* save card data if checked and not blocked end */


            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: ' . date('r', time() + (86400 * 365)));
            header('Content-type: application/json');
            echo json_encode(array(
                'valid' => true,
                'message' => 'Card Saved',
            ));
            exit();
        } catch (Exception $e) {
            /* send one mail */
            $error_message = $e->getMessage();

            $heading = 'Payment is blocked ( ' . $account_id . ')';
            $message = '<p>Payment is blocked for this account</p>';
            $message .= '<table  width="100%" cellspacing="2" cellpadding="2" border="0">
			<tr><td align="left" valign="top" colspan="2"><strong style="color: rgb(186, 0, 0);">Details</strong></td></tr>				
			<tr>
			<td width="36%" align="left" valign="middle">Account ID:</td>
			<td width="64%" align="left" valign="middle">' . $account_id . '</td>
			</tr>
			<tr>
			<td width="36%" align="left" valign="middle">Amount:</td>
			<td width="64%" align="left" valign="middle">' . $amount . '</td>
			</tr>			
			<tr>
			<td align="left" valign="middle">Gateway:</td>
			<td align="left" valign="middle">' . $payment_method . '</td>
			</tr>
			<tr>
			<td align="left" valign="middle">Card Number:</td>
			<td align="left" valign="middle">' . $card_number . '</td>
			</tr>	
			<tr>
			<td align="left" valign="middle">Message:</td>
			<td align="left" valign="middle">' . $error_message . '</td>
			</tr>				
			</table>';

            $body = file_get_contents(base_url() . 'email_templates/blue.html');
            $body = str_replace("#SITE_URL#", base_url(), $body);
            $body = str_replace("#SITE_LOGO#", SITE_FULL_NAME, $body);
            $body = str_replace("#HEADING#", $heading, $body);
            $body = str_replace("#BODY#", $message, $body);
            $body = str_replace("#SITE_NAME#", 'Kind regards,<br><strong>' . SITE_FULL_NAME . '</strong>', $body);
            $subject = $heading;


            $mail_to = 'nocteam@microtalkgroup.com';
            $mail_to_cc = SITE_MAIL_TO;
            $mail_to_bcc = 'anand.kumar@microtalkgroup.com';
            //
            $mail_from = SITE_MAIL_FROM;
            $mail_from_name = SITE_FULL_NAME;
            if ($mail_to != '') {
                send_mail($body, $subject, $mail_to, $mail_from, $mail_from_name, $mail_to_cc, $mail_to_bcc, '', 'PaymentBlocked');
            }



            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: ' . date('r', time() + (86400 * 365)));
            header('Content-type: application/json');
            echo json_encode(array(
                'valid' => false,
                'message' => $error_message,
            ));
            exit();
        }
    }

}
