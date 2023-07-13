<?php

class Detail_mod extends CI_Model {

    public $total_count;
    public $select_sql;
    public $total_count_sql;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_client_profitloss_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {///ddd($filter_data);
            $sql = "SELECT account.account_id, account.account_type, account.currency_id, sys_currencies.`name` as cname, sys_currencies.symbol, ";
			$sql .= "SUM(bill_account_sdr.totalcost) total_cost,
 SUM(bill_account_sdr.totalsallercost) buy_cost,
 SUM(bill_account_sdr.totalcost - bill_account_sdr.totalsallercost) AS profit, ";          

            $sql .= " ( SELECT company_name FROM customers WHERE customers.account_id = account.account_id ) as company_name
			FROM bill_account_sdr INNER JOIN  account on bill_account_sdr.account_id = account.account_id
INNER JOIN sys_currencies on sys_currencies.currency_id = account.currency_id
WHERE account_type='CUSTOMER' ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                       if(in_array($key,array('logged_customer_account_id','logged_customer_level')))
					   {
							continue;   
					   }  elseif ($key == 'clienttime') {
                            $range = explode(' - ', $value);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $start_dt = $range_from[0]." 00:00:00";
                            $end_dt = $range_to[0]." 23:59:59";
                            $sql .= "  and (bill_account_sdr.billing_date BETWEEN '$start_dt' AND '$end_dt') ";
                        } elseif ($key == 'logged_customer_type') {
							if ($value == 'RESELLER') {
								$sql .= " AND account.parent_account_id ='" . $filter_data['logged_customer_account_id'] . "' ";
								//$sql .= " AND account.account_level ='" . $filter_data['logged_customer_level'] . "' ";
							} else {
								$sql .= " AND account.parent_account_id ='' ";
							}	
						}
						elseif ($key == 'account_id')
                            $sql .= "  and bill_account_sdr.$key='$value' ";
                        elseif ($key == 'company_name')
                            $sql .= "  and bill_account_sdr.account_id IN( SELECT account_id FROM customers WHERE company_name LIKE '%" . $value . "%' )";
                    }
                }
            }
			$sql.=" and  bill_account_sdr.rule_type in (SELECT term FROM `sys_sdr_terms` where cost_calculation_formula = '-' and term_group = 'usage') ";
            $sql.=" GROUP BY account_id";
            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY company_name  ";
            }
            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
			  
            $this->select_sql = $sql;
            $final_return_array['result'] = $query->result_array();


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_reseller_profitloss_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) 
	{
        $final_return_array = array();
        try {///ddd($filter_data);
             $sql = "SELECT account.account_id, account.account_type, account.currency_id, sys_currencies.`name` as cname, sys_currencies.symbol, ";
			 
			 $sql .= "SUM(bill_account_sdr.totalcost) total_cost,
 SUM(bill_account_sdr.totalsallercost) buy_cost,
 SUM(bill_account_sdr.totalcost - bill_account_sdr.totalsallercost) AS profit, "; 
 			
			 $sql .= " ( SELECT company_name FROM resellers WHERE resellers.account_id = account.account_id ) as company_name
			FROM bill_account_sdr INNER JOIN  account on bill_account_sdr.account_id = account.account_id
INNER JOIN sys_currencies on sys_currencies.currency_id = account.currency_id
WHERE account_type='RESELLER' ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                       if(in_array($key,array('logged_customer_account_id','logged_customer_level')))
					   {
							continue;   
					   }  elseif ($key == 'resellertime') {
                            $range = explode(' - ', $value);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $start_dt = $range_from[0]." 00:00:00";
                            $end_dt = $range_to[0]." 23:59:59";
                            $sql .= "  and (bill_account_sdr.billing_date BETWEEN '$start_dt' AND '$end_dt') ";
                        } elseif ($key == 'logged_customer_type') {
							if ($value == 'RESELLER') {
								$sql .= " AND account.parent_account_id ='" . $filter_data['logged_customer_account_id'] . "' ";
							} else {
								$sql .= " AND account.parent_account_id ='' ";
							}	
						}
						elseif ($key == 'account_id')
                            $sql .= "  AND bill_account_sdr.$key='$value' ";
                        elseif ($key == 'company_name')
                            $sql .= "  AND bill_account_sdr.account_id IN( SELECT account_id FROM resellers WHERE company_name LIKE '%" . $value . "%' )";
                    }
                }
            }
			$sql.=" and  bill_account_sdr.rule_type in (SELECT term FROM `sys_sdr_terms` where cost_calculation_formula = '-' and term_group = 'usage') ";
		    $sql.=" GROUP BY account_id";          
            $sql .= " ORDER BY company_name  ";

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = $sql;
            $final_return_array['result'] = $query->result_array();


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }
    
     function get_carrierreport($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT carrier.carrier_currency_id as  currency_id,  sum(carriercost) as c_total_cost_sum, sum(carriercost) as c_cost_sum,  carrier.carrier_id, carrier.carrier_name ,
sys_currencies.name as cname,
sys_currencies.symbol
FROM bill_carrier_sdr 
INNER JOIN carrier ON bill_carrier_sdr.carrier_id= carrier.carrier_id 
INNER JOIN sys_currencies on sys_currencies.currency_id = carrier.carrier_currency_id
WHERE 1 ";
            $sql .= " and  bill_carrier_sdr.rule_type in (SELECT term FROM `sys_sdr_terms` where cost_calculation_formula = '-' and term_group = 'usage') ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                        if ($key == 'providertime') {
                            $range = explode(' - ', $value);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $start_dt = $range_from[0] . " 00:00:00";
                            $end_dt = $range_to[0] . " 23:59:59";
                            $sql .= "  and bill_carrier_sdr.billing_date BETWEEN '$start_dt' AND '$end_dt' ";
                        } elseif ($key == 'carrier_id')
                            $sql .= "  and  carrier.$key='$value' ";
                        elseif ($key == 'carrier_name')
                            $sql .= "  and  carrier.$key  LIKE '%" . $value . "%' ";
                    }
                }
            }
            $sql .= " GROUP BY carrier_id ";
            $sql .= " ORDER BY carrier_name ";

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";

            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = $sql;
            $final_return_array['result'] = $query->result_array();


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'carrier data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }
    

    function get_provider_profitloss_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT carrier.carrier_currency_id as  currency_id,  sum(c_total_cost) as total, carrier.carrier_id, carrier.carrier_name ,
sys_currencies.name as cname,
sys_currencies.symbol
FROM bill_sdrdata 
INNER JOIN carrier ON bill_sdrdata.c_carrier_id= carrier.carrier_id 
INNER JOIN sys_currencies on sys_currencies.currency_id = carrier.carrier_currency_id
WHERE 1 ";

            $sql .= " and  bill_sdrdata.rule_type in (SELECT term FROM `sys_sdr_terms` where cost_calculation_formula = '-' and term_group = 'usage') ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                        if ($key == 'providertime') {
                            $range = explode(' - ', $value);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $start_dt = $range_from[0] . " 00:00:00";
                            $end_dt = $range_to[0] . " 23:59:59";
                            $sql .= "  and bill_sdrdata.action_date BETWEEN '$start_dt' AND '$end_dt' ";
                        } elseif ($key == 'carrier_id')
                            $sql .= "  and  carrier.$key='$value' ";
                        elseif ($key == 'carrier_name')
                            $sql .= "  and  carrier.$key  LIKE '%" . $value . "%' ";
                    }
                }
            }
            $sql .= " GROUP BY carrier_id ";
            $sql .= " ORDER BY carrier_name ";

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";

            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = $sql;
            $final_return_array['result'] = $query->result_array();


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'carrier data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_service_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array()) {
        $final_return_array = array();
        try {//ddd($filter_data);
            $sql = "SELECT account.account_type,
		IF(CONCAT(( SELECT company_name FROM customers WHERE customers.account_id =bill_billing_event.account_id ), ' (',bill_billing_event.account_id,')') IS NOT NULL, 
		CONCAT(( SELECT company_name FROM customers WHERE customers.account_id =bill_billing_event.account_id ), ' (',bill_billing_event.account_id,')'),    CONCAT(( SELECT company_name FROM resellers WHERE resellers.account_id =bill_billing_event.account_id ), ' (',bill_billing_event.account_id,')')  )  account_id,
		CONCAT(bill_pricelist.description , ' (', bill_billing_event.item_id,')') item,
		bill_itemlist.item_name,
		bill_billing_event.item_id,
		SUM(bill_billing_event.quantity  ) AS quantity
		FROM bill_billing_event  
		INNER JOIN bill_itemlist  ON bill_billing_event.item_id=bill_itemlist.item_id  
		INNER JOIN bill_pricelist on bill_pricelist.price_id = bill_billing_event.price_id 
		INNER JOIN account on account.account_id = bill_billing_event.account_id
		WHERE bill_billing_event.status_id='1' ";




            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                        if (in_array($key, array('logged_customer_account_id', 'logged_customer_level'))) {
                            continue;
                        } elseif ($key == 'logged_customer_type') {
                            if ($value == 'RESELLER') {
                                $sql .= " AND account.parent_account_id ='" . $filter_data['logged_customer_account_id'] . "' ";
                            } else {
                                $sql .= " AND account.parent_account_id ='' ";
                            }
                        } elseif ($key == 'account_id')
                            $sql .= "  and  account.account_id='$value' ";
                        elseif ($key == 'service_name')
                            $sql .= "  AND (bill_pricelist.description  LIKE '%" . $value . "%' OR bill_billing_event.item_id  LIKE '%" . $value . "%') ";
                    }
                }
            }
            $sql .= " GROUP BY bill_billing_event.account_id, bill_billing_event.item_id";

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";

            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = $sql;
            $final_return_array['result'] = $query->result_array();


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Services fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_data_total_count($sql_exists = false) {
        try {

            if ($sql_exists && isset($this->total_count_sql) && $this->total_count_sql != '') {
                $count_sql = trim($this->total_count_sql);
            } else {

                $count_sql = generate_count_total_sql($this->select_sql);
                if (substr($count_sql, 0, 5) == 'error') {
                    throw new \Exception($count_sql);
                }
            }
            //echo $this->select_sql.'<br>'. $count_sql;
            $this->total_count_sql = $count_sql;
            $query_count = $this->db->query($count_sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;
            return $this->total_count;
        } catch (\Exception $e) {
            //echo $e->getMessage();
            return 0;
        }

        return 0; //$this->total_count;
    }

    function paymenthistory($order_by = '', $limit_to = '', $limit_from = '', $search_data = array()) {
        $final_return_array = array('result' => array());
        $final_return_array['result'] = array();
        try {
            if (!isset($search_data['time_range'])) {
                throw new Exception('time range missing');
            }
            $range = explode(' - ', $search_data['time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];

            $sql_select = " SELECT   			
			ph.payment_id, ph.account_id, ph.amount, ph.paid_on, ph.notes, ph.transaction_id, ph.created_by, ph.create_dt,	
			account.account_type,					
			(SELECT name FROM users WHERE user_id=ph.created_by)  created_by_name,
			IF(customers.company_name IS NULL, resellers.company_name, customers.company_name) company_name";

            $sql_from = "			
			FROM payment_history ph INNER JOIN account ON ph.account_id=account.account_id
			LEFT JOIN customers ON ph.account_id=customers.account_id
			LEFT JOIN resellers ON ph.account_id=resellers.account_id
			
			WHERE ";
            $sql_where = " payment_option_id IN('ADDBALANCE','ADDNETOFFBALANCE','REMOVEBALANCE','REMOVENETOFFBALANCE') AND paid_on BETWEEN '$start_dt' AND '$end_dt'";


            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if (in_array($key, array('logged_customer_account_id', 'logged_customer_level', 'time_range', 'account_manager', 'parent_account_id', 'sales_manager'))) {
                            continue;
                        } elseif ($key == 'logged_customer_type') {
                            if ($value == 'RESELLER') {
                                $sql_where .= " AND account.parent_account_id ='" . $search_data['logged_customer_account_id'] . "' ";
                                //$sql .= " AND account.account_level ='" . $filter_data['logged_customer_level'] . "' ";
                            } else {
                                $sql_where .= " AND account.parent_account_id ='' ";
                            }
                        } elseif ($key == 'account_id') {
                            $sql_where .= " AND ph.account_id ='" . $value . "'";
                        } else if ($key == 'company_name') {
                            $sql_where .= " AND (customers.company_name LIKE '%" . $value . "%' OR resellers.company_name LIKE '%" . $value . "%')";
                        } else if ($key == 'payment_type') {
                            if ($value == 'manual')
                                $sql_where .= " AND ph.account_id!=ph.created_by";
                            elseif ($value == 'customer')
                                $sql_where .= " AND ph.account_id=ph.created_by";
                        }
                        else {
                            $sql_where .= " AND $key  LIKE '%" . $value . "%'";
                        }
                    }
                }
            }





            $sql = $sql_select . $sql_from . $sql_where;
            $sql .= " ORDER BY payment_id DESC, ph.account_id";

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";

            //    echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = "SELECT account_id " . $sql_from . $sql_where;


            $final_return_array['result'] = $query->result_array();

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Payment data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    ////////////
    function get_sales_summary_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {///ddd($filter_data);
            $select_sql = "SELECT account.account_id, account.account_type, account.currency_id, 
		sys_currencies.`name` as cname, sys_currencies.symbol, (SUM(bill_account_sdr.totalcost) /  ((100 + account.tax1 + account.tax2 + account.tax3)/ 100) ) total_cost,
		SUM(IF(totalsallercost is null , 0 , totalsallercost)  /  ((100 + account.tax1 + account.tax2 + account.tax3)/ 100) ) buy_cost,
		SUM((bill_account_sdr.totalcost- (IF(totalsallercost is null , 0 , totalsallercost)) ) /  ((100 + account.tax1 + account.tax2 + account.tax3)/ 100) ) as profit, 
		IF(rule_type IN ('IN','OUT'), SUM(unit)/60, SUM(if(unit = 0,1,unit) )) AS quantity,
		IF(account.account_type='CUSTOMER', (SELECT company_name FROM customers WHERE customers.account_id = account.account_id) , (SELECT company_name FROM resellers WHERE resellers.account_id = account.account_id)  ) as company_name ";

            $from_sql = " 
			FROM 
			bill_account_sdr INNER JOIN sys_sdr_terms ON bill_account_sdr.rule_type = sys_sdr_terms.term
			INNER JOIN account on bill_account_sdr.account_id = account.account_id 
			INNER JOIN sys_currencies on sys_currencies.currency_id = account.currency_id";

            $where_sql = " WHERE  rule_type NOT IN ('OPENINGBALANCE','ADDCREDIT', 'REMOVECREDIT')
			AND  bill_account_sdr.rule_type IN (SELECT term FROM `sys_sdr_terms` WHERE cost_calculation_formula = '-' and term_group = 'usage')";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                        if (in_array($key, array('logged_customer_account_id', 'logged_customer_level'))) {
                            continue;
                        } elseif ($key == 'clienttime') {
                            $range = explode(' - ', $value);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $start_dt = $range_from[0] . " 00:00:00";
                            $end_dt = $range_to[0] . " 23:59:59";
                            $where_sql .= "  and (billing_date BETWEEN '$start_dt' AND '$end_dt') ";
                        } elseif ($key == 'logged_customer_type') {
                            if ($value == 'RESELLER') {
                                $where_sql .= " AND account.parent_account_id ='" . $filter_data['logged_customer_account_id'] . "' ";
                                //$cust_sql .= " AND account.account_level ='" . $filter_data['logged_customer_level'] . "' ";
                            } else {
                                $where_sql .= " AND account.parent_account_id ='' ";
                            }
                        } elseif ($key == 'account_id')
                            $where_sql .= "  AND bill_account_sdr.$key='$value' ";
                        elseif ($key == 'company_name')
                            $where_sql .= "  AND bill_account_sdr.account_id IN( SELECT account_id FROM customers WHERE company_name LIKE '%" . $value . "%' UNION SELECT account_id FROM resellers WHERE company_name LIKE '%" . $value . "%' )";
                    }
                }
            }

            $group_sql .= " GROUP BY account_id";

            $sql = $select_sql . $from_sql . $where_sql . $group_sql;



            //$cust_sql;	
            //$sql ="SELECT * FROM ($cust_sql UNION $res_sql) union_table";
            //	$sql ="$cust_sql UNION $res_sql";

            /* $limit_from = intval($limit_from);
              if ($limit_to != '')
              $sql .= " LIMIT $limit_from, $limit_to"; */


            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = $sql;
            $final_return_array['result'] = $query->result_array();


            ///////////////
            $sum_sql = "SELECT account.currency_id, sys_currencies.`name` as cname, sys_currencies.symbol, 
			(SUM(bill_account_sdr.totalcost) /  ((100 + account.tax1 + account.tax2 + account.tax3)/ 100)) total_cost,
			(SUM(IF(totalsallercost is null , 0 , totalsallercost)) /  ((100 + account.tax1 + account.tax2 + account.tax3)/ 100) ) buy_cost,
			(SUM(bill_account_sdr.totalcost- (IF(totalsallercost is null , 0 , totalsallercost)))/  ((100 + account.tax1 + account.tax2 + account.tax3)/ 100)) as profit	"
                    . $from_sql . $where_sql
                    . " GROUP BY account.currency_id";
					
					
					

            $query = $this->db->query($sum_sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['sum_result'] = $query->result_array();
            /////////////


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_sales_details_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {///ddd($filter_data);
            $select_sql = "SELECT sys_sdr_terms.term, sys_sdr_terms.display_text, 
			account.account_id, account.account_type, account.currency_id, 
		sys_currencies.`name` as cname, sys_currencies.symbol, 
		(SUM(bill_account_sdr.totalcost) / ((100 + account.tax1 + account.tax2 + account.tax3)/ 100) ) total_cost,
		(SUM(IF(totalsallercost is null , 0 , totalsallercost)) / ((100 + account.tax1 + account.tax2 + account.tax3)/ 100) ) buy_cost,
		(SUM(bill_account_sdr.totalcost- (IF(totalsallercost is null , 0 , totalsallercost))) / ((100 + account.tax1 + account.tax2 + account.tax3)/ 100) ) as profit, 
		IF(rule_type IN ('IN','OUT'), SUM(unit)/60, SUM(if(unit = 0,1,unit) )) AS quantity,
		IF(account.account_type='CUSTOMER', (SELECT company_name FROM customers WHERE customers.account_id = account.account_id) , (SELECT company_name FROM resellers WHERE resellers.account_id = account.account_id)  ) as company_name ";

            $from_sql = " 
			FROM 
			bill_account_sdr INNER JOIN sys_sdr_terms ON bill_account_sdr.rule_type = sys_sdr_terms.term
			INNER JOIN account on bill_account_sdr.account_id = account.account_id 
			INNER JOIN sys_currencies on sys_currencies.currency_id = account.currency_id";

            $where_sql = " WHERE  rule_type NOT IN ('OPENINGBALANCE','ADDCREDIT', 'REMOVECREDIT')
			AND  bill_account_sdr.rule_type IN (SELECT term FROM `sys_sdr_terms` WHERE cost_calculation_formula = '-' and term_group = 'usage')";

            /////




            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                        if (in_array($key, array('logged_customer_account_id', 'logged_customer_level'))) {
                            continue;
                        } elseif ($key == 'clienttime') {
                            $range = explode(' - ', $value);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $start_dt = $range_from[0] . " 00:00:00";
                            $end_dt = $range_to[0] . " 23:59:59";
                            $where_sql .= "  and (billing_date BETWEEN '$start_dt' AND '$end_dt') ";
                        } elseif ($key == 'logged_customer_type') {
                            if ($value == 'RESELLER') {
                                $where_sql .= " AND account.parent_account_id ='" . $filter_data['logged_customer_account_id'] . "' ";
                                //$cust_sql .= " AND account.account_level ='" . $filter_data['logged_customer_level'] . "' ";
                            } else {
                                $where_sql .= " AND account.parent_account_id ='' ";
                            }
                        } elseif ($key == 'account_id')
                            $where_sql .= "  AND bill_account_sdr.$key='$value' ";
                        elseif ($key == 'company_name')
                            $where_sql .= "  AND bill_account_sdr.account_id IN( SELECT account_id FROM customers WHERE company_name LIKE '%" . $value . "%' UNION SELECT account_id FROM resellers WHERE company_name LIKE '%" . $value . "%' )";
                    }
                }
            }



            $group_sql .= " GROUP BY account_id, rule_type";
            $sql = $select_sql . $from_sql . $where_sql . $group_sql;

            /* $limit_from = intval($limit_from);
              if ($limit_to != '')
              $sql .= " LIMIT $limit_from, $limit_to"; */
            //echo $sql;

            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = $sql;
            $final_return_array['result'] = $query->result_array();


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

}
