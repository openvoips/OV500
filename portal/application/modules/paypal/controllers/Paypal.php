<?php

// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0.1
// License https://www.gnu.org/licenses/agpl-3.0.html
//
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
defined('BASEPATH') OR exit('No direct script access allowed');

class Paypal extends MY_Controller {

    /** @var string Test / sandbox mode */
    private $testingMode;

    /** @var array Error messages */
    public $errorMsg = array();

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('payment_mod');
        $this->load->model('paypal_mod');
        $this->load->helper('paypal_helper');
        $this->$testingMode = false;
    }

    function get_paypal_url() {
        if ($this->$testingMode) {
            $pfHost = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $pfHost = 'https://www.paypal.com/cgi-bin/webscr';
        }
        return $pfHost;
    }

    public function index() {
        $page_name = "paypal_index";
        $search_session_key = 'search_' . $page_name;

        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $view_page = "";
       
        if (!check_is_loggedin())
            redirect(site_url(), 'refresh');
		      

        $account_id = get_logged_account_id();
        $option_param = array('currency' => true, 'balance' => true);
        $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
        
        if ($account_result['account_type'] == 'DEMO') {
            show_404('404_demo_account');
        }
        $account_type = $account_result['account_type'];
        $payment_gateways_result = array();

		
		if (check_logged_user_group(array(ADMIN_ACCOUNT_ID))) {
            redirect(site_url('paypal/config'), 'location', '301');
        }
        elseif (!check_logged_user_group(array('reseller', 'customer'))) {
            //not allowed
			show_404('403');
        } elseif ($account_result['parent_account_id'] == '') {//parent is admin
            $payment_gateways_result = $this->payment_mod->get_payment_gateways(ADMIN_ACCOUNT_ID);
        } else {
            $payment_gateways_result = $this->payment_mod->get_payment_gateways($account_result['parent_account_id']);
        }
        
        if (!isset($payment_gateways_result['result']['paypal'])) {
            show_404();
        }

        $data['account_result'] = $account_result;
        $data['payment_gateways_result'] = $payment_gateways_result['result']['paypal'];

        if (isset($_GET['tx']) && $_GET['tx'] != '') {
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkPay') {
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
            $this->form_validation->set_rules('method', 'Method', 'trim|required|in_list[paypal]', array('in_list' => 'You must provide a %s.')
            );

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $amount = $_POST['amount'];
                $method = $_POST['method'];
                
				$post_data = array();

                $payment_gateway_data = $payment_gateways_result['result'][$method];
                $this->initiate_payment($account_id, $amount, $method, $account_result, $payment_gateway_data,$post_data);
                $view_page = "loading";
            }
        }
		// ddd($data);
        if ($view_page != "loading") {
            $this->load->view('basic/header', $data);
            $this->load->view('make_payment_paypal', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    /* keep record before payment initiate */

    function initiate_payment($account_id, $amount, $method, $account_result, $payment_gateway_data, $post_data) {

        $item_name = 'Payment';
        $credentials = json_decode($payment_gateway_data['credentials'], true);

        $order_id = str_replace(' ', '', $account_id);
        $order_id = str_replace('-', '', $order_id);
        $order_id = preg_replace('/[^A-Za-z0-9\-]/', '', $order_id);
        $order_id = strtoupper('ORD' . $order_id . generateRandom(3)); 
		{
            //////
            $return_url = site_url() . 'paypal/success/' . param_encrypt($account_id) . '/' . $order_id;
            $cancel_url = site_url() . 'paypal/index';
            $notify_url = site_url() . 'paypal/paypal_notify/' . $order_id;
         
		 
            $pay_data_array = array();
			$pay_data_array['business'] = $credentials['business']; 
            $pay_data_array['amount'] = $amount;

            /////			
            $pay_data_array['return'] = $return_url;
            $pay_data_array['cancel_return'] = $cancel_url;
            $pay_data_array['notify_url'] = $notify_url;
            $pay_data_array['order_id'] = $order_id;
            $pay_data_array['currency_code'] = $account_result['currency']['name'];

            $first_name = $account_result['contact_name'];
            $last_name = '';
            $pos = strpos($account_result['contact_name'], ' ');
            if ($pos !== false) {
                $first_name = substr($account_result['contact_name'], 0, $pos);
                $last_name = substr($account_result['contact_name'], $pos);
            }

            $pay_data_array['payer_first_name'] = $first_name;
            $pay_data_array['payer_last_name'] = $last_name;
            $pay_data_array['payer_email'] = $account_result['emailaddress'];
			$pay_data_array['custom'] = $account_id;
			

            $pay_data_array['item_name'] = "Payment Item";
		    $pay_data_array['item_number'] = "1";
            $pay_data_array['rm'] = "2";
            $pay_data_array['cmd'] = "_xclick";
            $pay_data_array['no_shipping'] = "0";
            $pay_data_array['no_note'] = "1";
			/////////////
				
            $data['pay_data_array'] = $pay_data_array;
            
            $pay_data_array['order_id'] = $order_id;
            $this->payment_mod->initiate_payment($account_id, $amount, $pay_data_array, 'paypal');

			$pf_host = $this->get_paypal_url();
			$data['submit_url'] = $pf_host;
			
            $this->load->view('basic/header', $data);
            $this->load->view('paypal_loading', $data);
        }
    }

    function success($account_id_encode, $order_id) {
        $account_id = param_decrypt($account_id_encode);
        $sql = "SELECT * FROM payment_tracking WHERE account_id='" . $account_id . "' AND order_id='" . $order_id . "' LIMIT 0,1";
        $query = $this->db->query($sql);

        $num_rows = $query->num_rows();
        if ($num_rows < 1) {
            redirect(site_url('paypal'), 'location', '301');
        }
        $order_row = $query->row_array();
		 
		//http://portal.openvoips.org/portal/paypal/success/U1RDMzAwMDAw/ORDSTC300000W8R
        if ($order_row['order_status'] == 'success') {
            $this->session->set_flashdata('suc_msgs', 'Thank You For Your payment');
            redirect(site_url('paypal'), 'location', '301');
        } elseif ($order_row['pending'] == 'pending') {
            $this->session->set_flashdata('err_msgs', 'Your Payment Status is Pending');
            redirect(site_url('paypal'), 'location', '301');
        } elseif ($order_row['order_status'] == 'failed') {
            $this->session->set_flashdata('err_msgs', 'Your Payment is Failed');
            redirect(site_url('paypal'), 'location', '301');
        }  elseif ($order_row['order_status'] == 'initiated') {
            $this->session->set_flashdata('suc_msgs', 'Your Payment is Under Process');
            redirect(site_url('paypal'), 'location', '301');
        } else {////
            $this->session->set_flashdata('err_msgs', 'Your Payment is Declined');
            redirect(site_url('paypal'), 'location', '301');
        }
    }
	
	


    function paypal_notify($order_id = '') {
        try {
            $this->db = $this->load->database('default', true);
            $this->errorMsg = array();
            $raw_post_data = file_get_contents('php://input'); //$_POST;
			
			
			$raw_post_array = explode('&', $raw_post_data); 
			$post_data = $myPost = array(); 
			foreach ($raw_post_array as $keyval) { 
				$keyval = explode ('=', $keyval); 
				if (count($keyval) == 2) 
				{
					$myPost[$keyval[0]] = urldecode($keyval[1]); 
				}
			} 
			$post_data = $myPost;
			
			////////////////
		
			
			/*$post_data_str = print_r($post_data,true);
			$data_array = array();
			$data_array['field1'] = $post_data_str;
			$str = $this->db->insert_string('temp_data_dump', $data_array);
			$result = $this->db->query($str);*/

            ///////fetch order details/////////
            $account_id = $post_data['custom'];
            $sql = "SELECT * FROM payment_tracking WHERE account_id='" . $account_id . "' AND order_id='" . $order_id . "' LIMIT 0,1";
            $query = $this->db->query($sql);
            if ($query == null)
                return false;
            $num_rows = $query->num_rows();
            if ($num_rows < 1) {
                $this->errorMsg[] = "Order Not Found";
            }
            $order_row = $query->row_array();
            if ($order_row['order_status'] == 'initiated') {
                //ok proceed
            } else {
                $this->errorMsg[] = "Order Already Processed";
            }
            $checks = array('amount_gross' => $order_row['amount']);
            if (count($this->errorMsg) > 0) {
                throw new Exception('Error');
            }
			

            //////////////////////////////////////////////////

           
            //varify from paypal
            // if( function_exists( 'curl_init' ) )
            {				 
				// Read the post from PayPal system and add 'cmd' 
				$req = 'cmd=_notify-validate'; 
				if(function_exists('get_magic_quotes_gpc')) { 
					$get_magic_quotes_exists = true; 
				} 
				foreach ($myPost as $key => $value) { 
					if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
						$value = urlencode(stripslashes($value)); 
					} else { 
						$value = urlencode($value); 
					} 
					$req .= "&$key=$value"; 
				} 
			   //////
			   $pf_host = $this->get_paypal_url();
			   $ch = curl_init($pf_host); 
				if ($ch == FALSE) { 
                    throw new Exception('Curl Initialization failed');
				} 
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); 
				curl_setopt($ch, CURLOPT_POST, 1); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, $req); 
				curl_setopt($ch, CURLOPT_SSLVERSION, 6); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
				curl_setopt($ch, CURLOPT_FORBID_REUSE, 1); 
				 
				// Set TCP timeout to 30 seconds 
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: company-name')); 
				$response = curl_exec($ch); 
				 
				/* 
				 * Inspect IPN validation result and act accordingly 
				 * Split response headers and payload, a better way for strcmp 
				 */  
				$tokens = explode("\r\n\r\n", trim($response)); 
				$res = trim(end($tokens)); 
				////
			   if (strcmp($res, "VERIFIED") == 0 || strcasecmp($res, "VERIFIED") == 0) 
			   { 
			   		// Retrieve transaction info from PayPal 
					$item_number		= $post_data['item_number']; 
					$transaction_id		= $post_data['txn_id']; 
					$payment_gross     	= $post_data['mc_gross']; 
					$currency_code     	= $post_data['mc_currency']; 
					$payment_status 	= $post_data['payment_status']; 					
					
					$response_formatted = $post_data;
                    $response_formatted['amount_to_update'] = $payment_gross;

                    $payment_response_data = array('response_string' => $response_formatted, 'tracking_id' => $transaction_id,);

					if ($payment_status == 'Completed') {
						 $payment_response_data['order_status'] = 'success';
					} elseif ($payment_status == 'Pending') {
						$payment_response_data['order_status'] = 'pending';
					} else{
						$payment_response_data['order_status'] = 'failed';
					}
/*					
Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
Completed: The payment has been completed, and the funds have been added successfully to your account balance.
Created: A German ELV payment is made through Express Checkout.
Denied: The payment was denied. This happens only if the payment was previously pending because of one of the reasons listed for the pending_reason variable or the Fraud_Management_Filters_x variable.
Expired: This authorization has expired and cannot be captured.
Failed: The payment has failed. This happens only if the payment was made from your customer's bank account.
Pending: The payment is pending. See pending_reason for more information.
Refunded: You refunded the payment.
Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the reason_code element.
Processed: A payment has been accepted.
Voided: This authorization has been voided.
*/				
					
					 
					 //update payment status
                    $response_data = $this->payment_mod->update_payment($account_id, $order_id, $payment_response_data, 'paypal');		
					
					////
					if($payment_response_data['order_status'] == 'success')
					{
						$payment_method = 'paypal';
						$amount = $payment_gross; //$response_formatted['payment_gross'];
						$this->send_confirmation_mail($account_id, $payment_method, $amount, $transaction_id);	
					}
					/////			
					
			   }
			   else
			   {
				    $this->errorMsg[] = 'Data Not valid';
                    throw new Exception('Error');
			   }
			   
			   
			 /////////////// 
                
            }
            if (count($this->errorMsg) > 0) {
                //update message
                throw new Exception('Error');
                die;
            }
        } catch (Exception $e) {
            if (count($this->errorMsg) > 0) {
                $message = implode('<br>', $this->errorMsg);
                $response_data = $this->payment_mod->update_payment($account_id, $order_id, $post_data, 'paypal');
            }
            //$message = $order_id.'<br>'.$post_mail_data;		
            //mail($to, $subject, $message);
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
		<td width="36%" align="left" valign="middle">Account type:</td>
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
       
		$path = FCPATH.'email_templates/blue.html';
		$body = file_get_contents($path);
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
		$attachment_array = array();
 


        send_mail($body, $subject, $mail_to, $mail_from, $mail_from_name, '', '', '', 'PaymentConfirmation', $attachment_array);

        /////////send mail to user
        $heading = 'Payment Confirmation';
        $message = 'Dear ' . $account_result['name'] . ', <br><br>
		Thank you for your payment.<br><br>';

        $message .= 'Your account (' . $account_id . ') payment transaction ID is <strong>' . $transaction_id . '</strong>';

        if (isset($account_result['balance']['balance']))
            $message .= ' and after payment, the current balance is ' . (-$account_result['balance']['balance']) . ' ' . $account_result['currency']['name'] . '.';

        $path = FCPATH.'email_templates/yellow.html';
		$body = file_get_contents($path);
        $body = str_replace("#SITE_URL#", base_url(), $body);
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

	/*payment configuration*/
    public function config() {
        $data['page_name'] = "paypal_config";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		//$logged_account_id = get_logged_account_id();
		
		if (check_logged_user_group(array('reseller')))
			$logged_account_id = get_logged_account_id();
		elseif (check_logged_user_group(ADMIN_ACCOUNT_ID))
			$logged_account_id =ADMIN_ACCOUNT_ID;
		
		if (!check_is_loggedin())
            redirect(site_url(), 'refresh');
        if (!check_logged_user_group(array('reseller', ADMIN_ACCOUNT_ID)))
            show_404('403');

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('business', 'Email ID', 'trim|required|min_length[5]');
           
           
		
			$_POST['account_id'] =$logged_account_id ;
				
				
				
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->paypal_mod->set_paypal_data($_POST);
                if ($result) {
                    $this->session->set_flashdata('suc_msgs', 'Paypal Configuration Updated Successfully');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
		
        $result = $this->paypal_mod->get_paypal_data($logged_account_id);
        $data['data'] = $result['result'];
		
		$data['testingMode'] = $this->$testingMode;
		
        $this->load->view('basic/header', $data);
        $this->load->view('paypal_config', $data);
        $this->load->view('basic/footer', $data);
    }
	
	
 
}
