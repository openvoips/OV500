<?php

/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

class Crs_mod extends CI_Model {

    public $total_count;
    public $select_sql;

    function __construct() {
        parent::__construct();
        $this->load->database();
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
            $this->total_count_sql = $count_sql;
            $query_count = $this->db->query($count_sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;
            return $this->total_count;
        } catch (\Exception $e) {          
            return 0;
        }
        return 0; 
    }

    function paymentdetail($order_by = '', $limit_to = '', $limit_from = '', $search_data = array()) {
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
            $sql_where = " payment_option_id IN('ADDBALANCE','REMOVEBALANCE','ADDCREDIT','ADDCREDIT') AND paid_on BETWEEN '$start_dt' AND '$end_dt'";


            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if (in_array($key, array('logged_customer_account_id', 'logged_customer_level', 'time_range', 'account_manager', 'parent_account_id', 'sales_manager'))) {
                            continue;
                        } elseif ($key == 'logged_customer_type') {
                            if ($value == 'RESELLER') {
                                $sql_where .= " AND account.parent_account_id ='" . $search_data['logged_customer_account_id'] . "' ";
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

}
