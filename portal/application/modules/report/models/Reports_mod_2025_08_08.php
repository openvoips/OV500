<?php
/* 
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2023 
 * http://www.openvoips.com 
 */
class Reports_mod extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_client_profitloss_data($filter_data) {
        $final_return_array = array();
        try {
			$range = explode(' - ', $filter_data['clienttime']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);
            $start_dt = $range_from[0]." 00:00:00";
            $end_dt = $range_to[0]." 23:59:59";
			
			 
			 $sql = "SELECT account.currency_id, 
			 COUNT(DISTINCT bill_account_sdr.account_id) AS total_customer, sys_currencies.`name` as cname, SUM(bill_account_sdr.totalcost- bill_account_sdr.totalsallercost) as profit,  sys_currencies.symbol
			FROM bill_account_sdr INNER JOIN  account on bill_account_sdr.account_id = account.account_id
			INNER JOIN sys_currencies on sys_currencies.currency_id = account.currency_id
			WHERE account_type='CUSTOMER' 
			AND bill_account_sdr.rule_type in (SELECT term FROM `sys_sdr_terms` where cost_calculation_formula = '-' and term_group = 'usage') 
AND (billing_date BETWEEN '$start_dt' AND '$end_dt') ";
	
			if ($filter_data['logged_customer_type'] == 'RESELLER') {					
				$sql .= " AND account.parent_account_id ='" . $filter_data['logged_customer_account_id'] . "' ";
			} else {
				$sql .= " AND account.parent_account_id ='' ";
			}
			
			
			 $sql .= " GROUP BY currency_id";

			
//echo $sql;

			$this->sql=$sql;
			$query = $this->db->query($sql);
			$final_return_array['result'] = $query->result_array();
			return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }
	
	
	


	function get_reseller_profitloss_data($filter_data) {
        $final_return_array = array();
        try {
			$range = explode(' - ', $filter_data['resellertime']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);
            $start_dt = $range_from[0]." 00:00:00";
            $end_dt = $range_to[0]." 23:59:59";
			
			
			 $sql = "SELECT  account.currency_id, 
			 COUNT(DISTINCT account.account_id) AS total_customer,
			 SUM(bill_account_sdr.totalcost- bill_account_sdr.totalsallercost) as profit, 
			 sys_currencies.`name` as cname
			FROM bill_account_sdr INNER JOIN  account on bill_account_sdr.account_id = account.account_id
INNER JOIN sys_currencies on sys_currencies.currency_id = account.currency_id
WHERE account_type='RESELLER' 
AND bill_account_sdr.rule_type in (SELECT term FROM `sys_sdr_terms` where cost_calculation_formula = '-' and term_group = 'usage') 
AND (billing_date BETWEEN '$start_dt' AND '$end_dt')";
				
		///////////	
			if ($filter_data['logged_customer_type'] == 'RESELLER') {					
				$sql .= " AND account.parent_account_id ='" . $filter_data['logged_customer_account_id'] . "' ";
			} else {
				$sql .= " AND account.parent_account_id ='' ";
			}
			
			
			 $sql .= " GROUP BY currency_id";

			$this->reseller_sql=$sql;
			$query = $this->db->query($sql);
			$final_return_array['result'] = $query->result_array();
			return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }


    function get_vendor_profitloss_data($search_data) {
        try {
            $range = explode(' - ', $search_data['vendortime']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);
            $start_dt = $range_from[0]." 00:00:00";
            $end_dt = $range_to[0]." 23:59:59";
			
			$sql="SELECT carrier.carrier_currency_id as currency_id, carrier.carrier_id, carrier.carrier_name, sys_currencies.name as cname, sys_currencies.symbol, 
			sum(carriercost) as total, 
			 COUNT(DISTINCT carrier.carrier_id) AS total_customer
			FROM bill_carrier_sdr INNER JOIN carrier ON bill_carrier_sdr.carrier_id= carrier.carrier_id 
				INNER JOIN sys_currencies on sys_currencies.currency_id = carrier.carrier_currency_id	
			WHERE  bill_carrier_sdr.billing_date BETWEEN '$start_dt' AND '$end_dt'
			AND  bill_carrier_sdr.rule_type in (SELECT term FROM `sys_sdr_terms` where cost_calculation_formula = '-' and term_group = 'usage')
			GROUP BY currency_id";
			
			$this->vendor_sql=$sql;
			$query = $this->db->query($sql);
			$final_return_array['result'] = $query->result_array();
		
            return $final_return_array;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    

}
