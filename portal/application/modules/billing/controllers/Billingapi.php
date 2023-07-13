<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Billingapi extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Billingapi_mod');
    }

    public function api() {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $REQUEST = $_POST;
        } elseif ($_SERVER['REQUEST_METHOD'] == "GET") {
            $REQUEST = $_GET;
        } else {
            $REQUEST = $_REQUEST;
        }

        $result = $this->Billingapi_mod->billing($REQUEST);
        echo json_encode($result);
    }

    public function cron() {
        $date = date('Y-m-d');

        $this->Billingapi_mod->carrier_usage_data($date);
        $account = '';
        $account_type = 'CUSTOMER';
        $this->Billingapi_mod->cdr_service($account, $date, $account_type);
        $this->Billingapi_mod->monthlycharges($date);
        $this->Billingapi_mod->generateinvoice($date);
        $this->ManageBalance();
    }

    public function quickservice() {
        $this->Billingapi_mod->creditmanagement();
    }

    function sendinvoice() {

        $DB1 = $this->load->database('default', true);
        $sql = "SELECT bi.*, a.account_type, a.parent_account_id  FROM 
			bill_invoice bi INNER JOIN bill_customer_priceplan bcp ON bi.account_id=bcp.account_id 
			INNER JOIN account a ON bi.account_id=a.account_id 
			WHERE bi.status_id='generated' AND bcp.invoice_via_email='1' AND DATEDIFF(NOW(),bi.create_dt)<3
			ORDER BY parent_account_id, bi.id LIMIT 5";
        $query = $DB1->query($sql);
        if (!$query) {
            $error_array = $DB1->db->error();
//throw new Exception($error_array['message']);
        }
        echo $sql;
        foreach ($query->result_array() as $row) {
            try {
                $account_id = $row['account_id'];
                $account_type = $row['account_type'];
                $parent_account_id = $row['parent_account_id'];
                $invoice_id = $row['invoice_id'];
                if ($parent_account_id == '')
                    $parent_account_id = ADMIN_ACCOUNT_ID;

                echo '<br>' . $account_id . '--' . $invoice_id;

                if ($account_type == 'CUSTOMER') {
                    $sql = "SELECT contact_name, company_name, address, country_id, state_code_id, phone, emailaddress, pincode FROM customers WHERE account_id ='$account_id'";
                } else {
                    $sql = "SELECT contact_name, company_name, address, country_id, state_code_id, phone, emailaddress, pincode FROM resellers WHERE account_id ='$account_id'";
                }
                $query = $DB1->query($sql);
                if (!$query) {
                    $error_array = $DB1->db->error();
                    throw new Exception($error_array['message']);
                }
                $account_details = $query->row_array();
                if ($account_details['emailaddress'] == '') {
                    throw new Exception('Customer Email ID missing');
                }
/////////Fetch Parent Settings/////////////	
//ajax_get_mail_content
                $sql = "SELECT et.email_subject, et.email_body, et.email_bcc, et.email_cc, et.email_daemon, et.smtp_id,
				 sc.smtp_auth, sc.smtp_secure, sc.smtp_host, sc.smtp_port, sc.smtp_username, sc.smtp_password, sc.smtp_from, sc.smtp_from_name, sc.smtp_xmailer, sc.smtp_host_name
				FROM bill_email_templates et LEFT JOIN bill_smtp_config sc ON et.smtp_id=sc.smtp_config_id
				WHERE et.account_id ='$parent_account_id' AND et.template_for='INVOICEEMAIL'";
                $query = $DB1->query($sql);
                if (!$query) {
                    $error_array = $DB1->db->error();
                    throw new Exception($error_array['message']);
                }
                $num_rows = $query->num_rows();
                if ($num_rows == 0) {
                    throw new Exception('Invoice Template Not Found');
                }
                $mail_template = $query->row_array();
//-------------//
                $invoice_config = $this->customerinvoiceconfig_mod->inConfig_data($parent_account_id);
                if (!$invoice_config) {
                    throw new Exception('Invoice Configuration Not Found');
                }
/////////////////

                $search_data = array();
                $search_data['invoice_id'] = $invoice_id;
// below data function need to move here 
// Feedback -1 
// It is providing bill invoice last invoice data (single  row)

                $customerinvoice_data = $this->customerinvoice_mod->get_data('', 1, 0, $search_data);

                if (isset($customerinvoice_data['result']) && count($customerinvoice_data['result']) > 0)
                    $customerinvoice_data = current($customerinvoice_data['result']);
                else
                    throw new Exception('Invoice Not Found');

//$sdr_data = $this->customerinvoice_mod->get_sdr_data($invoice_id);

                $sql = "SELECT
bill_services.service_name,
sys_sdr_terms.service_id,
bill_sdr.account_id,
bill_sdr.invoice_id,
bill_sdr.item_id,
if(bill_itemlist.item_name is null, bill_sdr.item_name, bill_itemlist.item_name ) dst,
bill_sdr.rate,
sum(bill_sdr.quantity) quantity,
sum(bill_sdr.tax1_amount) tax1_amount,
sum(bill_sdr.tax2_amount) tax2_amount,
sum(bill_sdr.tax3_amount) tax3_amount,
sum(bill_sdr.charges) charges,
sum(bill_sdr.total_charges) total_charges
FROM `bill_sdr`
INNER JOIN sys_sdr_terms on bill_sdr.item_id = sys_sdr_terms.term
INNER JOIN bill_services on bill_services.service_id = sys_sdr_terms.service_id
left JOIN bill_itemlist on bill_itemlist.item_id = bill_sdr.item_id where invoice_id = '" . $invoice_id . "'
GROUP BY service_id, rate, bill_sdr.item_name";
                $query = $DB1->query($sql);
                $sdr_data = $query->result_array();

//////////////
                $account_manager_details = array();
                if ($customerinvoice_data['account_manager'] != '') {
                    $sql = "SELECT name,emailaddress,phone FROM users WHERE user_id ='" . $customerinvoice_data['account_manager'] . "'";
                    $query = $DB1->query($sql);
                    $account_manager_details = $query->row_array();
                }
////////////
/////
                $message = $mail_template['email_body'];
                $replace_array = array(
                    '{{CUSTOMER_NAME}}' => $account_details['contact_name'],
                    '{{AMOUNT}}' => number_format($customerinvoice_data['bill_amount'], 2),
                    '{{COMPANY_NAME}}' => $account_details['company_name'],
                    '{{SITE_URL}}' => site_url()
                );
//$message = $temp_data['email_body'];
                $message = replace_mail_variables($message, $replace_array);

                $heading = '';
                $body = file_get_contents(FCPATH . 'email_templates/blank.html');
                $body = str_replace("#HEADING#", $heading, $body);
                $body = str_replace("#BODY#", $message, $body);
//var_dump($invoice_config);
//echo $body;
//die;
////


                $file_save_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
                $pdf_file_name = 'invoice' . time();
                $r = $this->customerinvoicepdf_custom($customerinvoice_data, $sdr_data, $invoice_config, $account_manager_details, $file_save_path, $pdf_file_name);

                $file_full_path = $file_save_path . $pdf_file_name . '.pdf';
                if (file_exists($file_full_path)) {
                    $mail_to = $account_details['emailaddress'];
                    $subject = $mail_template['email_subject'];
                    $mail_from = SITE_MAIL_FROM;
                    $mail_from_name = SITE_FULL_NAME;
                    $cc = $mail_template['email_cc'];
                    $bcc = $mail_template['email_bcc'];
                    $actionfrom = 'SEND-INVOICE';
                    $attachment_array = array();
                    $attachment_array[] = array($file_full_path, 'invoice.pdf');
//$attachment_array[] = array($file_full_path, 'paymentreceipt.pdf');
                    $smpt_details = array();
                    if ($mail_template['email_daemon'] == 'SMTP') {
                        $smpt_details = $mail_template;
// sc.smtp_auth, sc.smtp_secure, sc.smtp_host, sc.smtp_port, sc.smtp_username, sc.smtp_password, sc.smtp_from, sc.smtp_from_name, sc.smtp_xmailer, sc.smtp_host_name						
                    }
//  echo '<br>'.$cc.'<br>'.$bcc.'<br>'.$mail_to;//die;

                    $mail_to = 'kanand81@gmail.com';
                    send_mail($body, $subject, $mail_to, $mail_from, $mail_from_name, $cc, $bcc, $account_id, $actionfrom, $attachment_array, $smpt_details);
                } else {
                    throw new Exception('Invoice File Not Found ->' . $file_full_path);
                }
//////////////////////////

                $sql = "UPDATE bill_invoice SET status_id='mail-sent', status_message='' WHERE account_id ='$account_id' AND invoice_id='$invoice_id'";
                echo '<br>' . $sql;
                $query = $DB1->query($sql);
//enum('no-mail','mail-sent','failed','generated')
            } catch (Exception $e) {
                $message = 'Exception: ' . $e->getMessage();

                $sql = "UPDATE bill_invoice SET status_id='failed', status_message='$message' WHERE account_id ='$account_id' AND invoice_id='$invoice_id'";
                $query = $DB1->query($sql);
            }
        }
    }

}
