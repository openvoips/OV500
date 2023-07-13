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

class customerinvoice_mod extends CI_Model {

    public $account_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {

            $sql = "SELECT SQL_CALC_FOUND_ROWS 
bill_invoice.id ,
bill_invoice.invoice_id ,
bill_invoice.account_id,
bill_invoice.contact_name ,
bill_invoice.company_name ,
bill_invoice.company_address ,
bill_invoice.email_address,
bill_invoice.phone_number ,
bill_invoice.tax_number ,
bill_invoice.tax1 ,
bill_invoice.tax2 ,
bill_invoice.tax3 ,	
bill_invoice.bill_date ,
bill_invoice.billing_cycle ,
bill_invoice.payment_terms ,
bill_invoice.itemised_billing ,
bill_invoice.billing_date_from ,
bill_invoice.billing_date_to ,
bill_invoice.currency_symbol ,
bill_invoice.currency_name ,
bill_invoice.status_id ,
bill_invoice.status_message ,
bill_invoice.account_manager ,
	
bill_invoice.create_dt ,
bill_invoice.update_dt, 		
		
account_type,
                
		if( account_type = 'CUSTOMER',( select company_name from customers where customers.account_id =  account.account_id ), (select company_name from resellers where resellers.account_id =  account.account_id ) )  as company_name,
bill_invoice.due_status 
		FROM bill_invoice INNER JOIN account ON bill_invoice.account_id=account.account_id	
		WHERE  account.account_type IN('RESELLER','CUSTOMER')";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND account.account_id ='" . $value . "' ";
                        } elseif ($key == 'invoice_id') {
                            $sql .= " AND bill_invoice.invoice_id ='" . $value . "' ";
                        } elseif ($key == 'bill_date') {
							$range = explode(' - ', $value);
							$range_from = $range[0]." 00:00:00";
							$range_to = $range[1]." 23:59:59";						
							
                            $sql .= " AND bill_invoice.bill_date BETWEEN '$range_from' AND '$range_to' ";
                        }else {
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
            }

            if (check_logged_user_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                $sql .= " AND account.account_id IN (SELECT account_id FROM account WHERE parent_account_id  is null or  parent_account_id = '')";
            } elseif (check_logged_user_type(array('RESELLER'))) {
                $parent_account_id = get_logged_account_id();
                $sql .= " AND account.account_id IN (SELECT account_id FROM account WHERE parent_account_id ='" . $parent_account_id . "')";
            }
            else{
                $account_id = get_logged_account_id();
                $sql .= " AND account.account_id ='" . $account_id . "' ";
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY bill_invoice.id desc ";
            }
            //echo $sql;
            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;
			
			
			
			$invoice_id_array = array();
			$final_return_array['result'] = array();
            $tariff_id_array = array();
            foreach ($query->result_array() as $row) {
                $invoice_id = $row['invoice_id'];

                if (isset($option_param['tariff']) && $option_param['tariff'] == true) {
                    $row['tariff'] = array();
                }
				$invoice_id_array[] = $invoice_id;
				
				if (isset($option_param['sum']) && $option_param['sum'] == true) {
                    $row['sum'] = array('openingbalance'=>0,'paymentadd'=>0,'paymentrefund'=>0,'usage'=>0,);
                }
				$final_return_array['result'][$invoice_id] = $row;
            }
			
			if(isset($option_param['sum']) && $option_param['sum'] == true && count($final_return_array['result']) > 0) {
                $invoice_id_str = implode("','", $invoice_id_array);
				$invoice_id_str = "'" . $invoice_id_str . "'";				
				
				$sql="SELECT 
				invoice_id,
				SUM(totalcost) totalcost,
				(CASE 
					WHEN rule_type='OPENINGBALANCE' THEN 'openingbalance'
					WHEN rule_type='ADDBALANCE' THEN 'paymentadd'
					WHEN rule_type='REMOVEBALANCE' THEN 'paymentrefund'
					ELSE 'usage'
				 END) AS rule_group 
				FROM `bill_account_sdr`
							INNER JOIN sys_sdr_terms on bill_account_sdr.rule_type = sys_sdr_terms.term				
				WHERE invoice_id IN($invoice_id_str)      
				AND rule_type NOT IN('ADDCREDIT', 'REMOVECREDIT', 'ADDTESTBALANCE', 'REMOVETESTBALANCE', 'ADDNETOFFBALANCE', 'REMOVENETOFFBALANCE') 
				GROUP BY invoice_id, rule_group ";
				
				//echo $sql;
				$query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $invoice_id = $row['invoice_id'];
					$rule_group = $row['rule_group'];
					$totalcost = $row['totalcost'];
                    $final_return_array['result'][$invoice_id]['sum'][$rule_group] = $totalcost;
                   
                }	
			}
			
          //  $final_return_array['result'] = $query->result_array();
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Invoices fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_data_total_count() {
        return $this->total_count;
    }

    function get_sdr_data($invoice_id) {
        $final_return_array = array();
        try {
            $sql = "SELECT * FROM bill_sdr WHERE invoice_id='" . $invoice_id . "'";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = $query->result_array();
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Invoice SDR fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }
	
	function sdr_statement($account_id, $filter_data = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT *
from bill_account_sdr
where account_id ='" . $account_id . "' ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'invoice_id') {
                        if (strlen(trim($value)) > 0)
                            $sql .= " AND $key ='" . $value . "' ";
                        else
                            $sql .= " AND (  $key is null or $key = '')  ";
                    } elseif ($value !='') {
                        $sql .= " AND $key ='" . $value . "' ";
                    }
                }
            }

            $sql .= "  ORDER BY billing_date ASC ";
            
            // echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = $query->result_array();

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'SDR statement fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

}
