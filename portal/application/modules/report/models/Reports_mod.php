<?php

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


    function get_provider_profitloss_data($search_data) {
        try {
            $range = explode(' - ', $search_data['providertime']);
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
			
			$this->provider_sql=$sql;
			$query = $this->db->query($sql);
			$final_return_array['result'] = $query->result_array();
		
            return $final_return_array;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function get_active_services_data($filter_data) {
        try {
           
		   $sql = "SELECT SUM(bill_billing_event.quantity) total_item  
		   
		   FROM bill_billing_event  
		INNER JOIN bill_itemlist  ON bill_billing_event.item_id=bill_itemlist.item_id  
		INNER JOIN bill_pricelist on bill_pricelist.price_id = bill_billing_event.price_id 
		INNER JOIN account on account.account_id = bill_billing_event.account_id
		 WHERE bill_billing_event.status_id='1' ";	

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                        if(in_array($key,array('logged_customer_account_id','logged_customer_level')))
					   {
							continue;   
					   } 
						elseif ($key == 'logged_customer_type') 
						{
                            if ($value == 'RESELLER') {					
								$sql .= " AND account.parent_account_id ='" . $filter_data['logged_customer_account_id'] . "' ";
							} else {
								$sql .= " AND account.parent_account_id ='' ";
							}
                        } 
						
                    }
                }
            }
            //$sql .=" ";
			$this->service_sql=$sql;
            $query = $this->db->query($sql);
            $serices_count = $query->row_array();
           
            $final_array['serices_count'] = $serices_count['total_item'];
            return $final_array;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

}
