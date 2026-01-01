<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
class Report_mod extends CI_Model {

    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function ConnectedCalls($search_data, $limit_to = '', $limit_from = '') {
        try {
            $table = date('Ym') . "_ratedcdr";
            $DB1 = $this->load->database('cdrdb', true);
            $range = explode(' - ', $search_data['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];

            $table_from = date('Ym', strtotime($start_dt)) . "_ratedcdr";
            $table_to = date('Ym', strtotime($end_dt)) . "_ratedcdr";

            //start_time 'Start Time', answer_time 'Answer Time', digits_dialed,	end_time 'End time', pdd,
            $sql_fields = " src_extension_id ,pdd, digits_dialed,
  src_extension_no,
  src_extension_name,
  dst_extension_id,
  dst_app,
  dst_app_number,
  dst_app_name,
  endpoint_app,
  endpoint_name,
  endpoint_number,
  endpoint_extension_no,
  endpoint_uuid,
  endpoint_extension_id, id, disposition_cause, customer_account_id Account,customer_company_name, customer_src_caller 'SRC-CLI', customer_src_callee 'SRC-DST', customer_src_ip 'SRC-IP',customer_incodecs 'Incoming-Codecs',carrier_outcodecs 'Outgoing-Codecs',call_codecs 'Call\'s-Codec',
						customer_tariff_id 'User-Tariff',	customer_prefix 'Prefix', customer_destination 'Destination', customer_duration 'Duration',
						customer_callcost_total 'Cost',	reseller1_account_id 'R1-Account',	reseller1_tariff_id 'R1-Tariff',reseller1_duration 'R1-Duration',
						reseller1_callcost_total 'R1-Cost',	reseller2_account_id 'R2-Account',	reseller2_tariff_id 'R2-Tariff', reseller2_duration 'R2-Duration',
						reseller2_callcost_total 'R2-Cost',	reseller3_account_id 'R3-Account',	reseller3_tariff_id 'R3-Tariff', reseller3_duration 'R3-Duration',
						reseller3_callcost_total 'R3-Cost', carrier_dialplan_id 'Routing',	carrier_id 'Carrier', carrier_ipaddress 'C-IP',
						carrier_src_caller 'USER-CLI',	carrier_src_callee 'User-DST', carrier_dst_caller 'C-CLI', carrier_dst_callee 'C-DST', carrier_tariff_id 'C-Tariff',
						carrier_prefix 'C-Prefix',	carrier_destination 'C-Destination', carrier_duration 'C-Duration',	carrier_callcost_total 'C-Cost',
						billsec 'Org-Duration',	 Q850CODE, SIPCODE , hangupby,						
						IF(customer_duration > 0, answer_time, start_time ) AS 'Start Time',
						IF(customer_duration > 0, end_time, start_time ) AS 'End Time' , cdr_type, recording_file, disposition ";

            $sql_where = '';
            if (trim($search_data['s_cdr_customer_account']) != '') {
                if ($search_data['s_cdr_customer_type'] == 'U')
                    $sql_where .= " AND customer_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R1')
                    $sql_where .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R2')
                    $sql_where .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R3')
                    $sql_where .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
            }

            if (trim($search_data['s_cdr_dialed_no']) != '')
                $sql_where .= " AND customer_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
            if (trim($search_data['s_cdr_carrier_dst_no']) != '')
                $sql_where .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
            if (trim($search_data['s_cdr_customer_cli']) != '')
                $sql_where .= " AND customer_src_caller like '" . trim($search_data['s_cdr_customer_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier_cli']) != '')
                $sql_where .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier']) != '')
                $sql_where .= " AND carrier_id like '" . trim($search_data['s_cdr_carrier']) . "%' ";
            if (trim($search_data['s_cdr_carrier_ip']) != '')
                $sql_where .= " AND carrier_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
            if (trim($search_data['s_cdr_customer_ip']) != '')
                $sql_where .= " AND customer_src_ip = '" . trim($search_data['s_cdr_customer_ip']) . "' ";
            if (trim($search_data['s_cdr_cdr_type']) != '')
                $sql_where .= " AND cdr_type = '" . trim($search_data['s_cdr_cdr_type']) . "' ";

            if (trim($search_data['disposition']) != '') {
                if ($search_data['disposition'] == 'ANSWRED')
                    $sql_where .= " AND disposition = 'ANSWRED' ";
                else
                    $sql_where .= " AND disposition != 'ANSWRED' ";
            }

            if (trim($search_data['s_cdr_call_duration']) != '') {

                if (trim($search_data['s_cdr_call_duration_range']) == 'gt') {

                    $sql_where .= " AND carrier_duration > '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'ls') {

                    $sql_where .= " AND carrier_duration < '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'gteq') {

                    $sql_where .= " AND carrier_duration >= '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'lseq') {

                    $sql_where .= " AND carrier_duration <= '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'eq') {

                    $sql_where .= " AND carrier_duration = '" . trim($search_data['s_cdr_call_duration']) . "' ";
                }
            }

            if (trim($search_data['s_time_range']) != '') {
                //$sql_where .= " AND start_time >= '".$start_dt."' AND end_time <= '".$end_dt."' ";
                $sql_where .= " AND end_time BETWEEN '" . $start_dt . "' AND '" . $end_dt . "' ";
            }

            if (trim($search_data['s_cdr_customer_company_name']) != '')
                $sql_where .= " AND customer_company_name LIKE '%" . trim($search_data['s_cdr_customer_company_name']) . "%' ";

            if (isset($search_data['s_cdr_customer_type_login']) and $search_data['s_cdr_customer_type_login'] == 'CUSTOMER') {

                $account_id_str = $search_data['s_cdr_customer_account'];
                $sql_where .= " AND customer_account_id IN('" . $account_id_str . "')";
            } elseif (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM account WHERE parent_account_id='" . $search_data['s_parent_account_id'] . "'";
                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql_where .= " AND customer_account_id IN(" . $account_id_str . ")";
            }


            ////////////////////

            $group_by = '';
            $orderby = ' order by id desc ';

            if ($table_from == $table_to) {
                $sql = "SELECT SQL_CALC_FOUND_ROWS " . $sql_fields . "	FROM  $table_from WHERE 1 ";
                $query = $sql . $sql_where . $group_by . $orderby;
            } else {
                $sql_from = "SELECT " . $sql_fields . " FROM  $table_from WHERE 1 " . $sql_where;
                $sql_to = "SELECT " . $sql_fields . " FROM  $table_to WHERE 1 " . $sql_where;

                $query = "SELECT SQL_CALC_FOUND_ROWS * FROM (" . $sql_from . " UNION ALL " . $sql_to . ") as table1 " . $group_by . $orderby;
            }

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $query .= " LIMIT $limit_from, $limit_to";
            else
                $query .= "  ";

            $return['query'] = $query;
          //   echo $query;
            $result = $DB1->query($query);

            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }


            ///find total
            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $DB1->query($sql);
            $row_count = $query_count->row();

            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $return['all_total'] = $row_count->total;
            //////////////

            $return['total'] = $result->num_rows();
            $return['result'] = $result->result_array();
            $return['status'] = 'success';

            //var_dump($return);die;
            return $return;
        } catch (Exception $e) {
            $return['all_total'] = 0;
            $return['total'] = 0;
            $return['result'] = Array();
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function callusage_mod($search_data, $limit_to = '', $limit_from = '') {
        try {
            $return = [];
            $logged_account_id = get_logged_account_id();
            $table = date('Ym') . "_ratedcdr";
            $DB1 = $this->load->database('cdrdb', true);
            //ddd($search_data);
            $range = explode(' - ', $search_data['time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];

            $table = date('Ym', strtotime($start_dt)) . "_ratedcdr";

            $sql = "SELECT SQL_CALC_FOUND_ROWS customer_account_id, customer_company_name, 
            if(customer_callcost_total = 0, 'BUNDLE','CHARGED') as 'plan_type' , 
            SUM(customer_duration) as used , 
            customer_rate 'plan_rate',
            sum(customer_callcost_total) as 'plan_charge',
            SUM(if (customer_tax_type = 'exclusive', (customer_callcost + customer_tax1_cost + customer_tax2_cost  + customer_tax3_cost) , customer_callcost))  as 'total_cost',                        
            cdr_type
            from  $table where ";
            $where = " cdr_type in ( 'IN', 'OUT') AND end_time BETWEEN '" . $start_dt . "' AND '" . $end_dt . "' ";

            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    $value = trim($value);
                    if ($value != '') {
                        if (in_array($key, array('faq_id', 'status_id'))) {
                            $where .= " AND $key ='" . $value . "' ";
                        } elseif (in_array($key, array('time_range', 'logged_customer_account_id', 'logged_customer_level'))) {
                            // $where .=" AND $key ='".$value."' "; 
                        } elseif ($value != '') {
                            $where .= " AND $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
                if (check_logged_account_type(array('RESELLER'))) {
                    //$logged_account_id
                    $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM account WHERE parent_account_id='" . $logged_account_id . "'";
                    /////////////
                    $query = $this->db->query($sub_sql);
                    if (!$query) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $row = $query->row();
                    $account_id_str = $row->account_ids;
                    /////////////
                    $where .= " AND customer_account_id IN(" . $account_id_str . ")";
                } elseif (check_logged_account_type(array('CUSTOMER'))) {
                    $where .= " AND customer_account_id='$logged_account_id'";
                } else {//admin
                    //??
                }
            }
            $sql .= $where;
            $sql .= " GROUP BY customer_account_id, plan_type, plan_rate, cdr_type";

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            $return['sql'] = $sql;
            $result = $DB1->query($sql);

//echo   $sql;


            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }


            ///find total
            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $DB1->query($sql);
            $row_count = $query_count->row();
            $return['total'] = $row_count->total;
            $return['result'] = $result->result_array();
            $return['status'] = 'success';

            //////sum data
            $sql = "SELECT 
            cdr_type,
            COUNT(cdr_type) call_count,
            SUM(customer_callcost_total) as 'plan_charge',
            SUM(if (customer_tax_type = 'exclusive', (customer_callcost + customer_tax1_cost + customer_tax2_cost  + customer_tax3_cost) , customer_callcost)) as  total_cost,
            customer_currency_id
            FROM  $table
            WHERE  ";
            $sql .= $where;
            $sql .= " GROUP BY cdr_type, customer_currency_id";
            $return['sum_sql'] = $sql;
            $result = $DB1->query($sql);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $sum_call_data = [];
            foreach ($result->result_array() as $row) {
                $cdr_type = $row['cdr_type'];
                $customer_currency_id = $row['customer_currency_id'];
                $sum_call_data[$cdr_type][$customer_currency_id] = $row;
            }
            $return['sum_result'] = $sum_call_data;

            return $return;
        } catch (Exception $e) {
            $return['total'] = 0;
            $return['result'] = Array();
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function reseller_summery($logged_account_id) {

        $DB1 = $this->load->database('cdrdb', true);
        $table = date('Ym') . "_ratedcdr";
        //today call data

        $logindata = get_logged_data();

        $sql = "SELECT   SUM(if (customer_tax_type = 'exclusive', (customer_callcost + customer_tax1_cost + customer_tax2_cost  + customer_tax3_cost) , customer_callcost)) as  total_cost, SUM(customer_callcost_total) customer_callcost_total,  cdr_type, COUNT(cdr_type) calls       FROM  $table  WHERE  customer_account_id='$logged_account_id' AND DATE(end_time) = CURDATE() GROUP BY cdr_type  and billsec > 0";

        if ($logindata['account_type'] == 'RESELLER') {
            if ($logindata['account_level'] == '1') {
                $sql = "SELECT   SUM(if (reseller1_tax_type = 'exclusive', (reseller1_callcost + reseller1_tax1_cost + reseller1_tax2_cost  + reseller1_tax3_cost) , reseller1_callcost)) as  total_cost, SUM(reseller1_callcost_total)  callcost_total,  cdr_type, COUNT(cdr_type) calls       FROM  $table  WHERE  reseller1_account_id='$logged_account_id' AND DATE(end_time) = CURDATE() GROUP BY cdr_type  and billsec > 0";
            } elseif ($logindata['account_level'] == '2') {
                $sql = "SELECT   SUM(if (reseller2_tax_type = 'exclusive', (reseller2_callcost + reseller2_tax1_cost + reseller2_tax2_cost  + reseller2_tax3_cost) , reseller2_callcost)) as  total_cost, SUM(reseller2_callcost_total) callcost_total,  cdr_type, COUNT(cdr_type) calls       FROM  $table  WHERE  reseller2_account_id='$logged_account_id' AND DATE(end_time) = CURDATE() GROUP BY cdr_type  and billsec > 0";
            } elseif ($logindata['account_level'] == '3') {
                $sql = "SELECT   SUM(if (reseller3_tax_type = 'exclusive', (reseller3_callcost + reseller3_tax1_cost + reseller3_tax3_cost  + reseller3_tax3_cost) , reseller3_callcost)) as  total_cost, SUM(reseller3_callcost_total) callcost_total,  cdr_type, COUNT(cdr_type) calls       FROM  $table  WHERE  reseller3_account_id='$logged_account_id' AND DATE(end_time) = CURDATE() GROUP BY cdr_type  and billsec > 0";
            }
        }

        $sql_array['today call data'] = $sql;

        $result = $DB1->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }
        $today_call_data['IN'] = Array('calls' => 0, 'total_cost' => 0, 'callcost_total' => 0);
        $today_call_data['OUT'] = Array('calls' => 0, 'total_cost' => 0, 'callcost_total' => 0);

        foreach ($result->result_array() as $row) {
            $cdr_type = $row['cdr_type'];
            $today_call_data[$cdr_type] = $row;
        }
        $data['today_callcost'] = $today_call_data;

        //month call data
        if ($logindata['account_type'] == 'RESELLER') {
            if ($logindata['account_level'] == '1') {
                $sql = "SELECT   SUM(if (reseller1_tax_type = 'exclusive', (reseller1_callcost + reseller1_tax1_cost + reseller1_tax2_cost  + reseller1_tax3_cost) , reseller1_callcost)) as  total_cost, SUM(reseller1_callcost_total)  callcost_total,  cdr_type, COUNT(cdr_type) calls       FROM  $table  WHERE  reseller1_account_id='$logged_account_id' AND DATE(end_time) = CURDATE() GROUP BY cdr_type  and billsec = 0";
            } elseif ($logindata['account_level'] == '2') {
                $sql = "SELECT   SUM(if (reseller2_tax_type = 'exclusive', (reseller2_callcost + reseller2_tax1_cost + reseller2_tax2_cost  + reseller2_tax3_cost) , reseller2_callcost)) as  total_cost, SUM(reseller2_callcost_total) callcost_total,  cdr_type, COUNT(cdr_type) calls       FROM  $table  WHERE  reseller2_account_id='$logged_account_id' AND DATE(end_time) = CURDATE() GROUP BY cdr_type  and billsec = 0";
            } elseif ($logindata['account_level'] == '3') {
                $sql = "SELECT   SUM(if (reseller3_tax_type = 'exclusive', (reseller3_callcost + reseller3_tax1_cost + reseller3_tax3_cost  + reseller3_tax3_cost) , reseller3_callcost)) as  total_cost, SUM(reseller3_callcost_total) callcost_total,  cdr_type, COUNT(cdr_type) calls       FROM  $table  WHERE  reseller3_account_id='$logged_account_id' AND DATE(end_time) = CURDATE() GROUP BY cdr_type  and billsec = 0";
            }
        }





        $sql_array['month call data'] = $sql;
        $result = $DB1->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }


        $month_call_data['IN'] = Array('calls' => 0, 'total_cost' => 0, 'callcost_total' => 0);
        $month_call_data['OUT'] = Array('calls' => 0, 'total_cost' => 0, 'callcost_total' => 0);

        foreach ($result->result_array() as $row) {
            $cdr_type = $row['cdr_type'];
            $month_call_data[$cdr_type] = $row;
        }






        $data['month_callcost'] = $month_call_data;

        $sql = "SELECT 		
        SUM(bill_account_sdr.totalcost) total_cost,      
        
        sys_sdr_terms.term_group,
        sys_sdr_terms.cost_calculation_formula                  
      
        FROM `bill_account_sdr` INNER JOIN sys_sdr_terms ON bill_account_sdr.rule_type=sys_sdr_terms.term
        WHERE account_id='$logged_account_id'
        AND   DATE(billing_date) = CURDATE()
        AND cost_calculation_formula!='' AND rule_type NOT IN('OPENINGBALANCE')
        GROUP BY term_group, cost_calculation_formula";
        $sql_array['today spent data'] = $sql;

//        echo $sql;
        $result = $this->db->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }


        $today_spent_data['balance'] = 0;
        $today_spent_data['usage'] = 0;
        foreach ($result->result_array() as $row) {
            if ($row) {
                $term_group = $row['term_group'];
                if (!isset($month_call_data[$term_group]))
                    $today_spent_data[$term_group] = 0;

                if ($row['cost_calculation_formula'] == '+') {
                    $today_spent_data[$term_group] += $row['total_cost'];
                } else {
                    $today_spent_data[$term_group] -= $row['total_cost'];
                }
            }
        }


        $data['today_spent_data'] = $today_spent_data;

        /////month spent data
        $sql = "SELECT 		
        SUM(bill_account_sdr.totalcost) total_cost,      

        sys_sdr_terms.term_group,
        sys_sdr_terms.cost_calculation_formula                  
      
        FROM `bill_account_sdr` INNER JOIN sys_sdr_terms ON bill_account_sdr.rule_type=sys_sdr_terms.term
        WHERE account_id='$logged_account_id'
         
        AND cost_calculation_formula!='' AND rule_type NOT IN('OPENINGBALANCE')
        GROUP BY term_group, cost_calculation_formula";
//        echo $sql;
//        die;
        $sql_array['month spent data'] = $sql;
        $result = $this->db->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }

        $month_spent_data['balance'] = 0;
        $month_spent_data['usage'] = 0;
        foreach ($result->result_array() as $row) {
            if ($row) {
                $term_group = $row['term_group'];
                if (!isset($month_spent_data[$term_group]))
                    $month_spent_data[$term_group] = 0;

                if ($row['cost_calculation_formula'] == '+') {
                    $month_spent_data[$term_group] += $row['total_cost'];
                } else {
                    $month_spent_data[$term_group] -= $row['total_cost'];
                }
            }
        }


//print_r($month_spent_data);
        $data['month_spent_data'] = $month_spent_data;

        $sql = "SELECT balance from  customer_balance where account_id ='$logged_account_id'";
        $sql_array['month call data'] = $sql;
        $result = $this->db->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }



        $currentBalance['balance'] = 0;
        foreach ($result->result_array() as $key => $value) {

            $currentBalance['balance'] = $value;
        }



        $data['currentBalance'] = $currentBalance['balance'];

        $sql = "SELECT
sys_currencies.symbol
from account 
INNER JOIN sys_currencies on sys_currencies.currency_id = account.currency_id
where account_id = '$logged_account_id'";
        $sql_array['month call data'] = $sql;
        $result = $this->db->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }

        $currency['symbol'] = 0;
        foreach ($result->result_array() as $key => $value) {

            $currency['symbol'] = $value;
        }
        $data['currency'] = $currency['symbol'];

        $sql = "SELECT 
account_id,
account_bundle_key,
bundle_package_id,
bundle_package_name,
total_allowed,
monthly_charges,
bundle_type,
bundle_for,
round(sdr_consumption / 60) as sdr_consumption,
action_date,
(SELECT GROUP_CONCAT(prefix) AS prefix from bundle_package_prefixes where bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id   ) as prefix1
from customer_bundle_sdr
where account_id = '" . $logged_account_id . "';";

        $data_plan = Array();
        $result = $this->db->query($sql);
        $data_plan = $result->result_array();
        $data['plan_data'] = $data_plan;
        $data['sql_array'] = $sql_array;
        return $data;
    }

    function account_summery($logged_account_id) {

        $DB1 = $this->load->database('cdrdb', true);
        $table = date('Ym') . "_ratedcdr";
        //today call data
        $sql = "SELECT   SUM(if (customer_tax_type = 'exclusive', (customer_callcost + customer_tax1_cost + customer_tax2_cost  + customer_tax3_cost) , customer_callcost)) as  total_cost, SUM(customer_callcost_total) customer_callcost_total,  cdr_type, COUNT(cdr_type) calls       FROM  $table
        WHERE  customer_account_id='$logged_account_id' AND DATE(end_time) = CURDATE()
        GROUP BY cdr_type and billsec > 0";
        $sql_array['today call data'] = $sql;

        $result = $DB1->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }
        $today_call_data['IN'] = Array('calls' => 0, 'total_cost' => 0, 'customer_callcost_total' => 0);
        $today_call_data['OUT'] = Array('calls' => 0, 'total_cost' => 0, 'customer_callcost_total' => 0);

        foreach ($result->result_array() as $row) {
            $cdr_type = $row['cdr_type'];
            $today_call_data[$cdr_type] = $row;
        }
        $data['today_callcost'] = $today_call_data;

        //month call data
        $sql = "SELECT   SUM(if (customer_tax_type = 'exclusive', (customer_callcost + customer_tax1_cost + customer_tax2_cost  + customer_tax3_cost) , customer_callcost)) as  total_cost, SUM(customer_callcost_total) customer_callcost_total,  cdr_type, COUNT(cdr_type) calls       FROM  $table
        WHERE  customer_account_id='$logged_account_id' AND DATE(end_time) = CURDATE()
        GROUP BY cdr_type and billsec = 0";

        $sql_array['month call data'] = $sql;
        $result = $DB1->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }


        $month_call_data['IN'] = Array('calls' => 0, 'total_cost' => 0, 'customer_callcost_total' => 0);
        $month_call_data['OUT'] = Array('calls' => 0, 'total_cost' => 0, 'customer_callcost_total' => 0);

        foreach ($result->result_array() as $row) {
            $cdr_type = $row['cdr_type'];
            $month_call_data[$cdr_type] = $row;
        }
        $data['month_callcost'] = $month_call_data;

        $sql = "SELECT 		
        SUM(bill_account_sdr.totalcost) total_cost,      
        
        sys_sdr_terms.term_group,
        sys_sdr_terms.cost_calculation_formula                  
      
        FROM `bill_account_sdr` INNER JOIN sys_sdr_terms ON bill_account_sdr.rule_type=sys_sdr_terms.term
        WHERE account_id='$logged_account_id'
        AND   DATE(billing_date) = CURDATE()
        AND cost_calculation_formula!='' AND rule_type NOT IN('OPENINGBALANCE')
        GROUP BY term_group, cost_calculation_formula";
        $sql_array['today spent data'] = $sql;

//        echo $sql;
        $result = $this->db->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }


        $today_spent_data['balance'] = 0;
        $today_spent_data['usage'] = 0;
        foreach ($result->result_array() as $row) {
            if ($row) {
                $term_group = $row['term_group'];
                if (!isset($month_call_data[$term_group]))
                    $today_spent_data[$term_group] = 0;

                if ($row['cost_calculation_formula'] == '+') {
                    $today_spent_data[$term_group] += $row['total_cost'];
                } else {
                    $today_spent_data[$term_group] -= $row['total_cost'];
                }
            }
        }


        $data['today_spent_data'] = $today_spent_data;

        /////month spent data
        $sql = "SELECT 		
        SUM(bill_account_sdr.totalcost) total_cost,      

        sys_sdr_terms.term_group,
        sys_sdr_terms.cost_calculation_formula                  
      
        FROM `bill_account_sdr` INNER JOIN sys_sdr_terms ON bill_account_sdr.rule_type=sys_sdr_terms.term
        WHERE account_id='$logged_account_id'
         
        AND cost_calculation_formula!='' AND rule_type NOT IN('OPENINGBALANCE')
        GROUP BY term_group, cost_calculation_formula";
//        echo $sql;
//        die;
        $sql_array['month spent data'] = $sql;
        $result = $this->db->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }

        $month_spent_data['balance'] = 0;
        $month_spent_data['usage'] = 0;
        foreach ($result->result_array() as $row) {
            if ($row) {
                $term_group = $row['term_group'];
                if (!isset($month_spent_data[$term_group]))
                    $month_spent_data[$term_group] = 0;

                if ($row['cost_calculation_formula'] == '+') {
                    $month_spent_data[$term_group] += $row['total_cost'];
                } else {
                    $month_spent_data[$term_group] -= $row['total_cost'];
                }
            }
        }


//print_r($month_spent_data);
        $data['month_spent_data'] = $month_spent_data;

        $sql = "SELECT balance from  customer_balance where account_id ='$logged_account_id'";
        $sql_array['month call data'] = $sql;
        $result = $this->db->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }



        $currentBalance['balance'] = 0;
        foreach ($result->result_array() as $key => $value) {

            $currentBalance['balance'] = $value;
        }



        $data['currentBalance'] = $currentBalance['balance'];

        $sql = "SELECT
sys_currencies.symbol
from account 
INNER JOIN sys_currencies on sys_currencies.currency_id = account.currency_id
where account_id = '$logged_account_id'";
        $sql_array['month call data'] = $sql;
        $result = $this->db->query($sql);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }

        $currency['symbol'] = 0;
        foreach ($result->result_array() as $key => $value) {

            $currency['symbol'] = $value;
        }
        $data['currency'] = $currency['symbol'];

        $sql = "SELECT 
account_id,
account_bundle_key,
bundle_package_id,
bundle_package_name,
total_allowed,
monthly_charges,
bundle_type,
bundle_for,
round(sdr_consumption / 60) as sdr_consumption,
action_date,
(SELECT GROUP_CONCAT(prefix) AS prefix from bundle_package_prefixes where bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id   ) as prefix1
from customer_bundle_sdr
where account_id = '" . $logged_account_id . "';";

        $data_plan = Array();
        $result = $this->db->query($sql);
        $data_plan = $result->result_array();
        $data['plan_data'] = $data_plan;
        $data['sql_array'] = $sql_array;
        return $data;
    }

    function CarrQOSR($search_data) {
        try {
            $DB1 = $this->load->database('cdrdb', true);
            $range = explode(' - ', $search_data['timerange']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);
            $start_dt = $range_from[0];
            $date = $start_dt . " 00:00:00";
            $table = date('Ym', strtotime($date)) . "_carrierstate";
            $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
            $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
            $end_dt = $range_to[0];
            $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
            $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
            $str = "select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls , round((sum(answeredcalls)/sum(totalcalls))*100,2) as asr, ifnull(round((sum(carrier_duration)/sum(answeredcalls))/60,2),0.00) as acd, ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd, ";
            $str .= " round(sum(carrier_duration)/60,2) as total_duration ";
            if ($search_data['group_by_ip'] == 'Y')
                $str .= " ,carrier_ipaddress  as ip_address ";
            if ($search_data['group_by_carrier'] == 'Y')
                $str .= " ,concat(carrier_name,'(',carrier_id,')') carrier_id";
            if ($search_data['group_by_date'] == 'Y')
                $str .= " ,call_date ";
            if ($search_data['group_by_hour'] == 'Y')
                $str .= " ,calltime_h ";
            if ($search_data['group_by_minute'] == 'Y')
                $str .= " ,calltime_m ";
            if ($search_data['group_by_prefix'] == 'Y')
                $str .= " ,carrier_prefix as prefix";
            if ($search_data['group_by_destination'] == 'Y')
                $str .= " ,carrier_prefix_name as prefix_name";
            if ($search_data['group_by_sip'] == 'Y')
                $str .= " ,SIPCODE ";
            if ($search_data['group_by_q850'] == 'Y')
                $str .= " ,Q850CODE ";

            //////////////////////////////
            /// showing account's cost///	
            if ($search_data['group_by_carrier'] == 'Y' || $search_data['carrier_id'] != '') {
                $str .= " ,round(sum(carrier_cost)*1.0000000000000000,2)  as cost ,carrier_currency_id as currency_id";
            }
            /// showing account's cost///	
            //////////////////////////////
            $str .= " from $table where date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "'
and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";
            if ($search_data['ip'] != '')
                $str .= " and carrier_ipaddress = '" . $search_data['ip'] . "'";
            if ($search_data['carrier_id'] != '')
                $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
            if ($search_data['prefix'] != '')
                $str .= " and carrier_prefix like '" . $search_data['prefix'] . "'";
            if ($search_data['destination'] != '')
                $str .= " and carrier_prefix_name like '" . $search_data['destination'] . "'";
            if ($search_data['sip'] != '')
                $str .= " and SIPCODE = '" . $search_data['sip'] . "'";
            if ($search_data['q850'] != '')
                $str .= " and Q850CODE = '" . $search_data['q850'] . "'";

            $group_by = "";

            if ($search_data['group_by_ip'] == 'Y')
                $group_by .= " carrier_ipaddress ,";
            if ($search_data['group_by_carrier'] == 'Y')
                $group_by .= " carrier_id ,";
            if ($search_data['group_by_date'] == 'Y')
                $group_by .= " call_date ,";
            if ($search_data['group_by_hour'] == 'Y')
                $group_by .= " calltime_h ,";
            if ($search_data['group_by_minute'] == 'Y')
                $group_by .= " calltime_m ,";
            if ($search_data['group_by_prefix'] == 'Y')
                $group_by .= " carrier_prefix ,";
            if ($search_data['group_by_destination'] == 'Y')
                $group_by .= " carrier_prefix_name ,";
            if ($search_data['group_by_sip'] == 'Y')
                $group_by .= " SIPCODE ,";
            if ($search_data['group_by_q850'] == 'Y')
                $group_by .= " Q850CODE ,";


            if ($group_by != '')
                $group_by = " group by " . rtrim($group_by, ',');

            $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
            $query = $str . $group_by . $orderby;

            $result = $DB1->query($query);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $return['total'] = $result->num_rows();
            $return['result'] = $result->result_array();
            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    public function monin_data($incoming_calls = 'Y', $outgoing_calls = 'Y', $incoming_duration = 'Y', $outgoing_duration = 'Y', $gateway_calls = 'Y', $customer_calls = 'Y', $show_usage = 'Y', $customer_call_stat = 'N', $carrier_call_stat = 'N', $livecalls_destination = 'N') {
        $logged_customer_type = get_logged_account_type();
        $logged_customer_account_id = get_logged_account_id();
        $logged_customer_level = get_logged_account_level();
        $DB1 = $this->load->database('cdrdb', true);

        /*
          if (mt_rand(1, 50) = 10) {
          $sql = "delete from  livecalls where  start_time < DATE_SUB( CURRENT_TIME(), INTERVAL 2 MINUTE) and callstatus <>  'answer'";
          $this->db->query($sql);
          }
         */
        if ($livecalls_destination == 'Y') {
            $str = "SELECT concat( call_flow, '::',customer_destination) as customer_destination, count(id) total_calls, sum( if(callstatus = 'answer',1,0)) answering, sum( if(callstatus <> 'answer',1,0)) ringing FROM livecalls GROUP BY call_flow, customer_destination ORDER BY total_calls DESC";
            $result = $this->db->query($str);
            $return['livecalls_destination'] = $result->result_array();
        }

        if ($customer_call_stat == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $str = "SELECT concat(customer_company_name,' (',account_id,')') account_id, IFNULL(SUM(totalcalls),0) tot_calls, IFNULL(SUM(answeredcalls),0) tot_answered, IFNULL(round((SUM(answeredcalls)/SUM(totalcalls))*100),0) asr,  IFNULL(round((SUM(customer_duration)/SUM(answeredcalls))),0) acd, round(SUM(customer_cost),2) tot_cost FROM $tablecustomerstate  WHERE call_date =  '" . date('Y-m-d') . "'  AND calltime_h >= HOUR(NOW())-1 AND calltime_m >= MINUTE(NOW())-1 GROUP BY account_id ORDER BY asr, tot_calls DESC;";
            $result = $DB1->query($str);
            $return['customer_call_stat'] = $result->result_array();
        }

        if ($carrier_call_stat == 'Y') {
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "SELECT  concat(cdr_type,'::', carrier_name ,' (',carrier_id,')') carrier_id,
                        IFNULL(SUM(totalcalls),0) tot_calls,
						IFNULL(SUM(answeredcalls),0) tot_answered,
						IFNULL(round((SUM(answeredcalls)/SUM(totalcalls))*100),0) asr, 
						IFNULL(round((SUM(carrier_duration)/SUM(answeredcalls))),0) acd,
						round(SUM(carrier_cost),2) tot_cost	
					FROM $tablecarrierstate 
					WHERE call_date =  '" . date('Y-m-d') . "'
					  AND calltime_h >= HOUR(NOW())-1
					  AND calltime_m >= MINUTE(NOW())-1
					GROUP BY carrier_id, cdr_type
                    ORDER BY asr, tot_calls DESC
				";
            $result = $DB1->query($str);
            $return['carrier_call_stat'] = $result->result_array();
        }
        /////////////////////////

        if ($show_usage == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $currency_data = $this->utils_model->get_currencies();
            $currency_array = array();
            for ($i = 0; $i < count($currency_data); $i++) {
                $currency_id = $currency_data[$i]['currency_id'];
                $currency_array[$currency_id] = $currency_data[$i]['symbol'];
            }

            $str = "SELECT round(sum(customer_cost),2) customer_cost_total,
							customer_currency_id currency_id, 
							'customer' customer_type 
					FROM $tablecustomerstate 
					WHERE call_date =  '" . date('Y-m-d') . "'
					GROUP BY customer_currency_id
				UNION
				SELECT round(sum(carrier_cost),2) customer_cost_total,
						carrier_currency_id currency_id ,
						'carrier' customer_type 
				FROM $tablecarrierstate 
				where call_date =  '" . date('Y-m-d') . "'
				GROUP BY carrier_currency_id";
            $result = $DB1->query($str);
            //$current_data = $result->result_array();
            //$return['usage_data'] = $result->result_array();
            $return['usage_data'] = array();
            foreach ($result->result_array() as $row) {
                $currency_id = $row['currency_id'];
                if (isset($currency_array[$currency_id]))
                    $currency_name = $currency_array[$currency_id];
                else
                    $currency_name = '--';

                $row['currency_name'] = $currency_name;
                $return['usage_data'][] = $row;
            }
        }

        if ($incoming_calls == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY 
						ifnull(sum(totalcalls),0) as tot_calls,
						ifnull(sum(answeredcalls),0)  as tot_answered,
						ifnull(round(sum(customer_duration)/60,2),0) as tot_duration,
						ifnull(round((sum(answeredcalls)/sum(totalcalls))*100,2),0) as asr, 
						ifnull(round((sum(customer_duration)/sum(answeredcalls)),2),0.00) as acd, 
						ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd
					from $tablecustomerstate where call_date = '" . date('Y-m-d') . "'";
            $result = $DB1->query($str);
            $current_data = $result->result_array();
            $return['incoming_calls'] = $current_data[0];
        }

        if ($incoming_duration == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY calltime_h, round(sum(customer_duration)/60,2) as hour_duration from $tablecustomerstate where call_date = '" . date('Y-m-d') . "' group by calltime_h";
            $result = $DB1->query($str);
            $return['incoming_duration'] = $result->result_array();
        }

        if ($outgoing_calls == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY 
	ifnull(sum(totalcalls),0) as tot_calls,
	ifnull(sum(answeredcalls),0)  as tot_answered,
	ifnull(round(sum(carrier_duration)/60,2),0) as tot_duration,
	ifnull(round((sum(answeredcalls)/sum(totalcalls))*100,2),0) as asr, 
	ifnull(round((sum(carrier_duration)/sum(answeredcalls)),2),0.00) as acd, 
	ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd
	from $tablecarrierstate where call_date = '" . date('Y-m-d') . "'";
            $result = $DB1->query($str);
            $current_data = $result->result_array();
            $return['outgoing_calls'] = $current_data[0];
        }

        if ($outgoing_duration == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY calltime_h, round(sum(carrier_duration)/60,2) as hour_duration from $tablecarrierstate where call_date = '" . date('Y-m-d') . "' group by calltime_h";
            $result = $DB1->query($str);
            $return['outgoing_duration'] = $result->result_array();
        }

        if ($gateway_calls == 'Y') {
            $str = "select HIGH_PRIORITY concat(call_flow,'::',carrier_id)  as name, carrier_ipaddress as ip,count(*) as total_calls ,
	sum(if(callstatus = 'answer',1,0)) as 'answer',
	sum(if(callstatus = 'ring',1,0)) as 'ringing',
	sum(if(callstatus = 'progress',1,0)) as 'progress'
	from livecalls where 1 
	group by call_flow, carrier_name, carrier_ipaddress order by carrier_name asc";

            //$result = $DB1->query($str);
            $result = $this->db->query($str);
            $return['gateway_calls'] = $result->result_array();
        }

        if ($customer_calls == 'Y') {
            $str = "select HIGH_PRIORITY concat(call_flow,'::',customer_company) as name, 'enduser' as type ,customer_ipaddress as ip,count(*) as total_calls ,
sum(if(callstatus = 'answer',1,0)) as 'answer',
sum(if(callstatus = 'ring',1,0)) as 'ringing',
sum(if(callstatus = 'progress',1,0)) as 'progress'
from livecalls where reseller1_account_id is NULL 
group by call_flow, customer_account_id ,customer_ipaddress  order by customer_company ,total_calls desc ";
// customer_ipaddress
            // $result = $DB1->query($str);
            $result = $this->db->query($str);
            $customer_calls = $result->result_array();

            $str = "select HIGH_PRIORITY concat(call_flow,'::',reseller1_account_id) as name, 'reseller' as type ,customer_ipaddress as ip,count(*) as total_calls ,
sum(if(callstatus = 'answer',1,0)) as 'answer',
sum(if(callstatus = 'ring',1,0)) as 'ringing',
sum(if(callstatus = 'progress',1,0)) as 'progress'
from livecalls where reseller1_account_id is not NULL 
group by call_flow, reseller1_account_id order by total_calls desc ";

            //  $result = $DB1->query($str);
            $result = $this->db->query($str);
            $reseller_calls = $result->result_array();

            $return['customer_calls'] = array_merge($customer_calls, $reseller_calls);
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

    function ProfitLoss($search_data) {

        if ($search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
            if ($search_data['logged_customer_level'] == '1')
                return $this->ProfitLossR1($search_data);
            if ($search_data['logged_customer_level'] == '2')
                return $this->ProfitLossR2($search_data);
            if ($search_data['logged_customer_level'] == '3')
                return $this->ProfitLossR3($search_data);
        } else {
            return $this->ProfitLossAdmin($search_data);
        }
    }

    function ProfitLossAdmin($search_data) {
        try {
            $range = explode(' - ', $search_data['call_date']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);
            $start_dt = $range_from[0];
            $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
            $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
            $end_dt = $range_to[0];
            $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
            $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
            $table = date('Ym', strtotime($start_dt)) . "_customerstate";

            $str = "   select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls ,if(r1_account_id is null or r1_account_id = '' , round(sum(customer_duration)/60,2),  round(sum(r1_duration)/60,2)) as total_duration,  if(r1_account_id is null or r1_account_id = '' , concat(if(customer_company_name = '',account_id, customer_company_name ),' (',account_id,')'), r1_account_id)  as account_code , sum(carrier_callcost_total_usercurrency) carrier_cost, if(r1_account_id is null or r1_account_id = '', round(sum(customer_cost)*1.0000000000000000,4),round(sum(r1_cost)*1.0000000000000000,4)) as cost, customer_currency_id as currency_id ";

            if ($search_data['group_by_carrier'] == 'Y')
                $str .= " ,carrier_id ";
            if ($search_data['group_by_date'] == 'Y')
                $str .= " ,call_date ";
            if ($search_data['group_by_hour'] == 'Y')
                $str .= " ,calltime_h ";
            if ($search_data['group_by_minute'] == 'Y')
                $str .= " ,calltime_m ";
            if ($search_data['group_by_prefix'] == 'Y')
                $str .= " ,prefix ";
            if ($search_data['group_by_destination'] == 'Y')
                $str .= " ,prefix_name ";

            //  $str .= " from $table WHERE 1 and ((r2_account_id = '' or r2_account_id is null ) and (r3_account_id = '' or r3_account_id is null)) and answeredcalls > 0 ";

            $str .= " from $table WHERE 1  and answeredcalls > 0 ";

            if ($search_data['account_type'] == 'U') {
                if ($search_data['account_id'] != '') {
                    $str .= " and account_id = '" . $search_data['account_id'] . "'";
                }
            } else if ($search_data['account_type'] == 'R') {
                if ($search_data['account_id'] != '') {
                    $str .= " and r1_account_id = '" . $search_data['account_id'] . "'";
                }
            }

            $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "' and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";

            if (trim($search_data['company_name']) != '') {
                $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";
            }

            if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                $level = $search_data['logged_customer_level'];
                $field_name = 'r' . $level . '_account_id';
                $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
            }
            if ($search_data['carrier_id'] != '')
                $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
            if ($search_data['prefix'] != '')
                $str .= " and prefix like '" . $search_data['prefix'] . "'";
            if ($search_data['destination'] != '')
                $str .= " and prefix_name like '" . $search_data['destination'] . "'";

            $group_by = "";

            if ($search_data['group_by_user'] == 'Y') {
                if ($search_data['account_type'] == 'U')
                    $group_by .= " account_code ,";
                else {
                    $group_by .= " account_code ,";
                }
            }
            if ($search_data['group_by_carrier'] == 'Y')
                $group_by .= " carrier_id ,";
            if ($search_data['group_by_date'] == 'Y')
                $group_by .= " call_date ,";
            if ($search_data['group_by_hour'] == 'Y')
                $group_by .= " calltime_h ,";
            if ($search_data['group_by_minute'] == 'Y')
                $group_by .= " calltime_m ,";
            if ($search_data['group_by_prefix'] == 'Y')
                $group_by .= " prefix ,";
            if ($search_data['group_by_destination'] == 'Y')
                $group_by .= " prefix_name ,";
            if ($group_by != '')
                $group_by = " group by customer_currency_id," . rtrim($group_by, ',');

            $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
            $query = $str . $group_by . $orderby;
            $DB1 = $this->load->database('cdrdb', true);
            $result = $DB1->query($query);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            // echo $DB1->last_query();
            $return['total'] = $result->num_rows();
            if ($return['total'] == '')
                $return['total'] = 0;
            $return['result'] = $result->result_array();

            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function ProfitLossR1($search_data) {
        try {
            $range = explode(' - ', $search_data['call_date']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);
            $start_dt = $range_from[0];
            $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
            $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
            $end_dt = $range_to[0];
            $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
            $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
            $table = date('Ym', strtotime($start_dt)) . "_customerstate";
            $str = "   select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls ,if(r2_account_id is null or r2_account_id = '' , round(sum(customer_duration)/60,2),  round(sum(r2_duration)/60,2)) as total_duration,  if(r2_account_id is null or r2_account_id = '' , concat(if(customer_company_name = '',account_id, customer_company_name ),' (',account_id,')'), r2_account_id)  as account_code , sum(r1_cost) carrier_cost, if(r2_account_id is null or r2_account_id = '', round(sum(customer_cost)*1.0000000000000000,4),round(sum(r2_cost)*1.0000000000000000,4)) as cost, customer_currency_id as currency_id ";

            if ($search_data['group_by_carrier'] == 'Y')
                $str .= " ,carrier_id ";
            if ($search_data['group_by_date'] == 'Y')
                $str .= " ,call_date ";
            if ($search_data['group_by_hour'] == 'Y')
                $str .= " ,calltime_h ";
            if ($search_data['group_by_minute'] == 'Y')
                $str .= " ,calltime_m ";
            if ($search_data['group_by_prefix'] == 'Y')
                $str .= " ,prefix ";
            if ($search_data['group_by_destination'] == 'Y')
                $str .= " ,prefix_name ";

            $str .= " from $table WHERE 1 and answeredcalls > 0 ";
            if ($search_data['account_type'] == 'U') {
                if ($search_data['account_id'] != '') {
                    $str .= " and account_id = '" . $search_data['account_id'] . "'";
                }
            } else {
                if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                    $level = $search_data['logged_customer_level'] + 1;
                    $field_name = 'r' . $level . '_account_id';
                    $str .= " AND `" . $field_name . "` = '" . $search_data['account_id'] . "'";
                } else {
                    $str .= " and r2_account_id = '" . $search_data['account_id'] . "'";
                }
            }
            $str .= " and r1_account_id = '" . $search_data['logged_customer_account_id'] . "'";

            $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "' and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";

            if (trim($search_data['company_name']) != '') {
                $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";
            }

            if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                $level = $search_data['logged_customer_level'];
                $field_name = 'r' . $level . '_account_id';
                $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
            }
            if ($search_data['carrier_id'] != '')
                $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
            if ($search_data['prefix'] != '')
                $str .= " and prefix like '" . $search_data['prefix'] . "'";
            if ($search_data['destination'] != '')
                $str .= " and prefix_name like '" . $search_data['destination'] . "'";

            $group_by = "";

            if ($search_data['group_by_user'] == 'Y') {
                if ($search_data['account_type'] == 'U')
                    $group_by .= " account_code ,";
                else {
                    $group_by .= " account_code ,";
                }
            }
            if ($search_data['group_by_carrier'] == 'Y')
                $group_by .= " carrier_id ,";
            if ($search_data['group_by_date'] == 'Y')
                $group_by .= " call_date ,";
            if ($search_data['group_by_hour'] == 'Y')
                $group_by .= " calltime_h ,";
            if ($search_data['group_by_minute'] == 'Y')
                $group_by .= " calltime_m ,";
            if ($search_data['group_by_prefix'] == 'Y')
                $group_by .= " prefix ,";
            if ($search_data['group_by_destination'] == 'Y')
                $group_by .= " prefix_name ,";
            if ($group_by != '')
                $group_by = " group by customer_currency_id," . rtrim($group_by, ',');

            $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
            $query = $str . $group_by . $orderby;
            $DB1 = $this->load->database('cdrdb', true);
            $result = $DB1->query($query);

            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $return['total'] = $result->num_rows();
            if ($return['total'] == '')
                $return['total'] = 0;
            $return['result'] = $result->result_array();
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function ProfitLossR2($search_data) {
        try {
            $range = explode(' - ', $search_data['call_date']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);
            $start_dt = $range_from[0];
            $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
            $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
            $end_dt = $range_to[0];
            $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
            $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
            $table = date('Ym', strtotime($start_dt)) . "_customerstate";
            $str = "   select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls ,if(r3_account_id is null or r3_account_id = '' , round(sum(customer_duration)/60,2),  round(sum(r3_duration)/60,2)) as total_duration,  if(r3_account_id is null or r3_account_id = '' , concat(if(customer_company_name = '',account_id, customer_company_name ),' (',account_id,')'), r3_account_id)  as account_code , sum(r2_cost) carrier_cost, if(r3_account_id is null or r3_account_id = '', round(sum(customer_cost)*1.0000000000000000,4),round(sum(r3_cost)*1.0000000000000000,4)) as cost, customer_currency_id as currency_id ";

            if ($search_data['group_by_carrier'] == 'Y')
                $str .= " ,carrier_id ";
            if ($search_data['group_by_date'] == 'Y')
                $str .= " ,call_date ";
            if ($search_data['group_by_hour'] == 'Y')
                $str .= " ,calltime_h ";
            if ($search_data['group_by_minute'] == 'Y')
                $str .= " ,calltime_m ";
            if ($search_data['group_by_prefix'] == 'Y')
                $str .= " ,prefix ";
            if ($search_data['group_by_destination'] == 'Y')
                $str .= " ,prefix_name ";

            $str .= " from $table WHERE 1 and answeredcalls > 0 ";
            if ($search_data['account_type'] == 'U') {
                if ($search_data['account_id'] != '') {
                    $str .= " and account_id = '" . $search_data['account_id'] . "'";
                }
            } else {
                if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                    $level = $search_data['logged_customer_level'] + 1;
                    $field_name = 'r' . $level . '_account_id';
                    $str .= " AND `" . $field_name . "` = '" . $search_data['account_id'] . "'";
                } else {
                    $str .= " and r3_account_id = '" . $search_data['account_id'] . "'";
                }
            }
            $str .= " and r2_account_id = '" . $search_data['logged_customer_account_id'] . "'";
            $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "' and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";
            if (trim($search_data['company_name']) != '') {
                $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";
            }

            if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                $level = $search_data['logged_customer_level'];
                $field_name = 'r' . $level . '_account_id';
                $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
            }
            if ($search_data['carrier_id'] != '')
                $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
            if ($search_data['prefix'] != '')
                $str .= " and prefix like '" . $search_data['prefix'] . "'";
            if ($search_data['destination'] != '')
                $str .= " and prefix_name like '" . $search_data['destination'] . "'";

            $group_by = "";

            if ($search_data['group_by_user'] == 'Y') {
                if ($search_data['account_type'] == 'U')
                    $group_by .= " account_code ,";
                else {
                    $group_by .= " account_code ,";
                }
            }
            if ($search_data['group_by_carrier'] == 'Y')
                $group_by .= " carrier_id ,";
            if ($search_data['group_by_date'] == 'Y')
                $group_by .= " call_date ,";
            if ($search_data['group_by_hour'] == 'Y')
                $group_by .= " calltime_h ,";
            if ($search_data['group_by_minute'] == 'Y')
                $group_by .= " calltime_m ,";
            if ($search_data['group_by_prefix'] == 'Y')
                $group_by .= " prefix ,";
            if ($search_data['group_by_destination'] == 'Y')
                $group_by .= " prefix_name ,";
            if ($group_by != '')
                $group_by = " group by customer_currency_id," . rtrim($group_by, ',');

            $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
            $query = $str . $group_by . $orderby;
            $DB1 = $this->load->database('cdrdb', true);
            $result = $DB1->query($query);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $return['total'] = $result->num_rows();
            if ($return['total'] == '')
                $return['total'] = 0;
            $return['result'] = $result->result_array();
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function ProfitLossR3($search_data) {
        $range = explode(' - ', $search_data['call_date']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);
        $start_dt = $range_from[0];
        $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
        $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
        $end_dt = $range_to[0];
        $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
        $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
        $table = date('Ym', strtotime($start_dt)) . "_customerstate";
        $str = "   select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls ,round(sum(customer_duration)/60,2) as total_duration,  concat(if(customer_company_name = '',account_id, customer_company_name ),' (',account_id,')')  as account_code , sum(r3_cost) carrier_cost, round(sum(customer_cost)*1.0000000000000000,4) as cost, customer_currency_id as currency_id ";

        if ($search_data['group_by_carrier'] == 'Y')
            $str .= " ,carrier_id ";
        if ($search_data['group_by_date'] == 'Y')
            $str .= " ,call_date ";
        if ($search_data['group_by_hour'] == 'Y')
            $str .= " ,calltime_h ";
        if ($search_data['group_by_minute'] == 'Y')
            $str .= " ,calltime_m ";
        if ($search_data['group_by_prefix'] == 'Y')
            $str .= " ,prefix ";
        if ($search_data['group_by_destination'] == 'Y')
            $str .= " ,prefix_name ";

        $str .= " from $table WHERE 1 and answeredcalls > 0 ";
        if ($search_data['account_type'] == 'U') {
            if ($search_data['account_id'] != '') {
                $str .= " and account_id = '" . $search_data['account_id'] . "'";
            }
        } else {
            if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                $level = $search_data['logged_customer_level'] + 1;
                $field_name = 'r' . $level . '_account_id';
                $str .= " AND " . $field_name . " = '" . $search_data['account_id'] . "'";
            }
        }

        $str .= " and r3_account_id = '" . $search_data['logged_customer_account_id'] . "'";

        $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "' and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";
        if (trim($search_data['company_name']) != '') {
            $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";
        }

        if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
            $level = $search_data['logged_customer_level'];
            $field_name = 'r' . $level . '_account_id';
            $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
        }

        if ($search_data['carrier_id'] != '')
            $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
        if ($search_data['prefix'] != '')
            $str .= " and prefix like '" . $search_data['prefix'] . "'";
        if ($search_data['destination'] != '')
            $str .= " and prefix_name like '" . $search_data['destination'] . "'";

        $group_by = "";

        if ($search_data['group_by_user'] == 'Y') {
            if ($search_data['account_type'] == 'U')
                $group_by .= " account_code ,";
            else {
                $group_by .= " account_code ,";
            }
        }
        if ($search_data['group_by_carrier'] == 'Y')
            $group_by .= " carrier_id ,";
        if ($search_data['group_by_date'] == 'Y')
            $group_by .= " call_date ,";
        if ($search_data['group_by_hour'] == 'Y')
            $group_by .= " calltime_h ,";
        if ($search_data['group_by_minute'] == 'Y')
            $group_by .= " calltime_m ,";
        if ($search_data['group_by_prefix'] == 'Y')
            $group_by .= " prefix ,";
        if ($search_data['group_by_destination'] == 'Y')
            $group_by .= " prefix_name ,";
        if ($group_by != '')
            $group_by = " group by customer_currency_id," . rtrim($group_by, ',');

        $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
        $query = $str . $group_by . $orderby;
        $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($query);
        if (!$result) {
            $error_array = $this->db->error();
            throw new Exception($error_array['message']);
        }
        $return['total'] = $result->num_rows();
        if ($return['total'] == '')
            $return['total'] = 0;
        $return['result'] = $result->result_array();
        return $return;
    }

    function CustQOSR($search_data) {
        try {

            $range = explode(' - ', $search_data['call_date']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);
            $start_dt = $range_from[0];
            $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
            $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
            $end_dt = $range_to[0];
            $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
            $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);

            // $date = $search_data['call_date'] . " 00:00:00";
            $table = date('Ym', strtotime($start_dt)) . "_customerstate";
            $str = "select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls , round((sum(answeredcalls)/sum(totalcalls))*100,2) as asr, ifnull(round((sum(customer_duration)/sum(answeredcalls))/60,2),0.00) as acd, ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd, ";

            if ($search_data['account_type'] == 'U')
                $str .= " round(sum(customer_duration)/60,2) as total_duration ";
            else {

                $str .= " round(sum(r1_duration)/60,2) as total_duration ";
            }

            if ($search_data['group_by_user'] == 'Y') {
                if ($search_data['account_type'] == 'U')
                    $str .= " ,concat(if(customer_company_name = '',account_id,customer_company_name  ),' (',account_id,')')  as account_code ";
                else {
                    $str .= " ,r1_account_id  as account_code ";
                }
            }

            if ($search_data['group_by_carrier'] == 'Y')
                $str .= " ,carrier_id ";
            if ($search_data['group_by_date'] == 'Y')
                $str .= " ,call_date ";
            if ($search_data['group_by_hour'] == 'Y')
                $str .= " ,calltime_h ";
            if ($search_data['group_by_minute'] == 'Y')
                $str .= " ,calltime_m ";
            if ($search_data['group_by_prefix'] == 'Y')
                $str .= " ,prefix ";
            if ($search_data['group_by_destination'] == 'Y')
                $str .= " ,prefix_name ";
            if ($search_data['group_by_sip'] == 'Y')
                $str .= " ,SIPCODE ";
            if ($search_data['group_by_q850'] == 'Y')
                $str .= " ,Q850CODE ";

            //////////////////////////////
            /// showing account's cost///	
            if ($search_data['group_by_user'] == 'Y' || $search_data['account_id'] != '') {
                if ($search_data['account_type'] == 'U')
                    $str .= " ,round(sum(customer_cost)*1.0000000000000000,2)  as cost ";
                else
                    $str .= " ,round(sum(r1_cost)*1.0000000000000000,2)  as cost ";

                $str .= " ,customer_currency_id as currency_id";
            }
            /// showing account's cost///	
            //////////////////////////////

            $str .= " from $table WHERE 1 ";

            if ($search_data['account_id'] != '') {
                if ($search_data['account_type'] == 'U')
                    $str .= " and account_id = '" . $search_data['account_id'] . "'";
                else {

                    if (isset($search_data['logged_account_type']) && isset($search_data['logged_account_level']) && $search_data['logged_account_type'] == 'RESELLER' && in_array($search_data['logged_account_level'], array(1, 2, 3))) {
                        $level = $search_data['logged_account_level'] + 1;
                        $field_name = 'r' . $level . '_account_id';

                        $str .= " AND `" . $field_name . "` = '" . $search_data['account_id'] . "'";
                    } else {
                        $str .= " and r1_account_id = '" . $search_data['account_id'] . "'";
                    }
                }
            }




            $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "'
and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";

            /* ------------------------------ */
            if (trim($search_data['company_name']) != '')
                $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";

            if (isset($search_data['logged_account_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_account_level']) && $search_data['logged_account_type'] == 'RESELLER' && in_array($search_data['logged_account_level'], array(1, 2, 3))) {
                $level = $search_data['logged_account_level'];
                $field_name = 'r' . $level . '_account_id';

                $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
            }




            if ($search_data['carrier_id'] != '')
                $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
            if ($search_data['prefix'] != '')
                $str .= " and prefix like '" . $search_data['prefix'] . "'";
            if ($search_data['destination'] != '')
                $str .= " and prefix_name like '" . $search_data['destination'] . "'";
            if ($search_data['sip'] != '')
                $str .= " and SIPCODE = '" . $search_data['sip'] . "'";
            if ($search_data['q850'] != '')
                $str .= " and Q850CODE = '" . $search_data['q850'] . "'";

            $group_by = "";

            if ($search_data['group_by_user'] == 'Y') {
                if ($search_data['account_type'] == 'U')
                    $group_by .= " account_id ,";
                else {
                    $group_by .= " r1_account_id ,";
                }
            }
            if ($search_data['group_by_carrier'] == 'Y')
                $group_by .= " carrier_id ,";
            if ($search_data['group_by_date'] == 'Y')
                $group_by .= " call_date ,";
            if ($search_data['group_by_hour'] == 'Y')
                $group_by .= " calltime_h ,";
            if ($search_data['group_by_minute'] == 'Y')
                $group_by .= " calltime_m ,";
            if ($search_data['group_by_prefix'] == 'Y')
                $group_by .= " prefix ,";
            if ($search_data['group_by_destination'] == 'Y')
                $group_by .= " prefix_name ,";
            if ($search_data['group_by_sip'] == 'Y')
                $group_by .= " SIPCODE ,";
            if ($search_data['group_by_q850'] == 'Y')
                $group_by .= " Q850CODE ,";


            if ($group_by != '')
                $group_by = " group by " . rtrim($group_by, ',');

            $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
            $query = $str . $group_by . $orderby;
            $DB1 = $this->load->database('cdrdb', true);
            $result = $DB1->query($query);
//echo $DB1->last_query();


            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $return['total'] = $result->num_rows();
            if ($return['total'] == '')
                $return['total'] = 0;
            $return['result'] = $result->result_array();
            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function invoice_list($account_id) {
        $final_return_array = array();
        try {
            $sql = " select invoice_id from bill_invoice where account_id ='" . $account_id . "' order by invoice_id desc ";
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

    function sdr_statement($account_id, $filter_data = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT account_id, account_type, account_level FROM account WHERE account_id ='" . $account_id . "' LIMIT 0,1 ";
            $query = $this->db->query($sql);
            $row = $query->row_array();
            $account_level = $row['account_level'];
            $account_type = $row['account_type'];

            if ($account_type == 'CUSTOMER') {
                $account_id_field = 'account_id';
                $total_cost_field = 'total_cost';
            } else {
                $account_id_field = 'r' . $account_level . '_account_id';
                $total_cost_field = 'r' . $account_level . '_total_cost';
            }




            $sql = "    SELECT
id,
rule_type,
billing_date as action_date,
group_concat(DISTINCT  service_number ORDER BY service_number ASC SEPARATOR ', ')  notes,
startdate service_startdate,
enddate service_stopdate,
account_id,
sum(totalcost) total_cost
 
from bill_account_sdr
where account_id ='" . $account_id . "' ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'invoice_id') {
                        if (strlen(trim($value)) > 0)
                            $sql .= " AND $key ='" . $value . "' ";
                        else
                            $sql .= " AND (  $key is null or $key = '')  ";
                    } elseif ($key == 'action_date') {
                        $sql .= " AND DATE_FORMAT(billing_date, '%Y-%m-%d')='" . $value . "' ";
                    }
                }
            }

            $sql .= " group by rule_type, date(billing_date) ORDER BY billing_date ASC ";

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

    function call_statistics($filter_data = array()) {

        $final_return_array = array();
        try {
            $sql = "SELECT customer_account_id,customer_company_name, customer_destination 'destination', COUNT(cdr_id) 'connected_calls', SUM(customer_duration) 'duration', SUM(customer_callcost_total) 'cost' FROM bill_cdrs  WHERE 1 ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($key == 'parent_account_id' && $value != '') {
                        $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM account WHERE parent_account_id='" . $value . "'";
                        /////////////
                        $query = $this->db->query($sub_sql);
                        if (!$query) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $row = $query->row();
                        $account_id_str = $row->account_ids;
                        /////////////
                        $sql .= " AND customer_account_id IN(" . $account_id_str . ")";
                    } elseif ($key == 'action_month' && $value != '') {
                        $sql .= " AND DATE_FORMAT(end_time, '%Y-%m')='" . $value . "' ";
                    } elseif ($key == 'action_date' && $value != '') {
                        $sql .= " AND DATE_FORMAT(end_time, '%Y-%m-%d')='" . $value . "' ";
                    } elseif ($key == 'customer_account_id' && $value != '') {
                        $sql .= " AND customer_account_id='" . $value . "' ";
                    } else if ($key == 'customer_company_name' && $value != '') {

                        $sql .= " AND customer_company_name LIKE '%" . $value . "%' ";
                    }
                }
            }

            if (isset($filter_data['groupby_account']) && $filter_data['groupby_account'] == 'Y')
                $sql .= " GROUP BY customer_account_id ORDER BY Cost desc";
            else
                $sql .= " GROUP BY customer_account_id, Destination ORDER BY Cost desc";
            //echo $sql;
            $DB1 = $this->load->database('cdrdb', true);
            $query = $DB1->query($sql);
            if (!$query) {
                $error_array = $DB1->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = $query->result_array();

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Call statistics fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    public function monin_data_reseller($incoming_calls = 'Y', $outgoing_calls = 'Y', $incoming_duration = 'Y', $outgoing_duration = 'Y', $gateway_calls = 'Y', $customer_calls = 'Y', $show_usage = 'Y', $customer_call_stat = 'N', $carrier_call_stat = 'N', $livecalls_destination = 'N') {
        $logged_customer_type = get_logged_account_type();
        $logged_customer_account_id = get_logged_account_id();
        $logged_customer_level = get_logged_account_level();
        $DB1 = $this->load->database('cdrdb', true);

        if ($livecalls_destination == 'Y') {
            $str = "SELECT concat( call_flow, '::',customer_destination) as customer_destination, count(id) total_calls, sum( if(callstatus = 'answer',1,0)) answering, sum( if(callstatus <> 'answer',1,0)) ringing FROM livecalls GROUP BY call_flow, customer_destination ORDER BY total_calls DESC";
            $result = $this->db->query($str);
            $return['livecalls_destination'] = $result->result_array();
        }

        if ($customer_call_stat == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $str = "SELECT concat(customer_company_name,' (',account_id,')') account_id, IFNULL(SUM(totalcalls),0) tot_calls, IFNULL(SUM(answeredcalls),0) tot_answered, IFNULL(round((SUM(answeredcalls)/SUM(totalcalls))*100),0) asr,  IFNULL(round((SUM(customer_duration)/SUM(answeredcalls))),0) acd, round(SUM(customer_cost),2) tot_cost FROM $tablecustomerstate  WHERE call_date =  '" . date('Y-m-d') . "'  AND calltime_h >= HOUR(NOW())-1 AND calltime_m >= MINUTE(NOW())-1 GROUP BY account_id ORDER BY asr, tot_calls DESC;";
            $result = $DB1->query($str);
            $return['customer_call_stat'] = $result->result_array();
        }

        if ($carrier_call_stat == 'Y') {
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "SELECT  concat(cdr_type,'::', carrier_name ,' (',carrier_id,')') carrier_id,
                        IFNULL(SUM(totalcalls),0) tot_calls,
						IFNULL(SUM(answeredcalls),0) tot_answered,
						IFNULL(round((SUM(answeredcalls)/SUM(totalcalls))*100),0) asr, 
						IFNULL(round((SUM(carrier_duration)/SUM(answeredcalls))),0) acd,
						round(SUM(carrier_cost),2) tot_cost	
					FROM $tablecarrierstate 
					WHERE call_date =  '" . date('Y-m-d') . "'
					  AND calltime_h >= HOUR(NOW())-1
					  AND calltime_m >= MINUTE(NOW())-1
					GROUP BY carrier_id, cdr_type
                    ORDER BY asr, tot_calls DESC
				";
            $result = $DB1->query($str);
            $return['carrier_call_stat'] = $result->result_array();
        }
        /////////////////////////

        if ($show_usage == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $currency_data = $this->utils_model->get_currencies();
            $currency_array = array();
            for ($i = 0; $i < count($currency_data); $i++) {
                $currency_id = $currency_data[$i]['currency_id'];
                $currency_array[$currency_id] = $currency_data[$i]['symbol'];
            }

            $str = "SELECT round(sum(customer_cost),2) customer_cost_total,
							customer_currency_id currency_id, 
							'customer' customer_type 
					FROM $tablecustomerstate 
					WHERE call_date =  '" . date('Y-m-d') . "'
					GROUP BY customer_currency_id
				UNION
				SELECT round(sum(carrier_cost),2) customer_cost_total,
						carrier_currency_id currency_id ,
						'carrier' customer_type 
				FROM $tablecarrierstate 
				where call_date =  '" . date('Y-m-d') . "'
				GROUP BY carrier_currency_id";
            $result = $DB1->query($str);
            //$current_data = $result->result_array();
            //$return['usage_data'] = $result->result_array();
            $return['usage_data'] = array();
            foreach ($result->result_array() as $row) {
                $currency_id = $row['currency_id'];
                if (isset($currency_array[$currency_id]))
                    $currency_name = $currency_array[$currency_id];
                else
                    $currency_name = '--';

                $row['currency_name'] = $currency_name;
                $return['usage_data'][] = $row;
            }
        }

        if ($incoming_calls == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY 
						ifnull(sum(totalcalls),0) as tot_calls,
						ifnull(sum(answeredcalls),0)  as tot_answered,
						ifnull(round(sum(customer_duration)/60,2),0) as tot_duration,
						ifnull(round((sum(answeredcalls)/sum(totalcalls))*100,2),0) as asr, 
						ifnull(round((sum(customer_duration)/sum(answeredcalls)),2),0.00) as acd, 
						ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd
					from $tablecustomerstate where call_date = '" . date('Y-m-d') . "'";
            $result = $DB1->query($str);
            $current_data = $result->result_array();
            $return['incoming_calls'] = $current_data[0];
        }

        if ($incoming_duration == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY calltime_h, round(sum(customer_duration)/60,2) as hour_duration from $tablecustomerstate where call_date = '" . date('Y-m-d') . "' group by calltime_h";
            $result = $DB1->query($str);
            $return['incoming_duration'] = $result->result_array();
        }

        if ($outgoing_calls == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY 
	ifnull(sum(totalcalls),0) as tot_calls,
	ifnull(sum(answeredcalls),0)  as tot_answered,
	ifnull(round(sum(carrier_duration)/60,2),0) as tot_duration,
	ifnull(round((sum(answeredcalls)/sum(totalcalls))*100,2),0) as asr, 
	ifnull(round((sum(carrier_duration)/sum(answeredcalls)),2),0.00) as acd, 
	ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd
	from $tablecarrierstate where call_date = '" . date('Y-m-d') . "'";
            $result = $DB1->query($str);
            $current_data = $result->result_array();
            $return['outgoing_calls'] = $current_data[0];
        }

        if ($outgoing_duration == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY calltime_h, round(sum(carrier_duration)/60,2) as hour_duration from $tablecarrierstate where call_date = '" . date('Y-m-d') . "' group by calltime_h";
            $result = $DB1->query($str);
            $return['outgoing_duration'] = $result->result_array();
        }

        if ($gateway_calls == 'Y') {
            $str = "select HIGH_PRIORITY concat(call_flow,'::',carrier_id)  as name, carrier_ipaddress as ip,count(*) as total_calls ,
	sum(if(callstatus = 'answer',1,0)) as 'answer',
	sum(if(callstatus = 'ring',1,0)) as 'ringing',
	sum(if(callstatus = 'progress',1,0)) as 'progress'
	from livecalls where 1 
	group by call_flow, carrier_name, carrier_ipaddress order by carrier_name asc";

            //$result = $DB1->query($str);
            $result = $this->db->query($str);
            $return['gateway_calls'] = $result->result_array();
        }

        if ($customer_calls == 'Y') {
            $str = "select HIGH_PRIORITY concat(call_flow,'::',customer_company) as name, 'enduser' as type ,customer_ipaddress as ip,count(*) as total_calls ,
sum(if(callstatus = 'answer',1,0)) as 'answer',
sum(if(callstatus = 'ring',1,0)) as 'ringing',
sum(if(callstatus = 'progress',1,0)) as 'progress'
from livecalls where reseller1_account_id is NULL 
group by call_flow, customer_account_id ,customer_ipaddress  order by customer_company ,total_calls desc ";
// customer_ipaddress
            // $result = $DB1->query($str);
            $result = $this->db->query($str);
            $customer_calls = $result->result_array();

            $str = "select HIGH_PRIORITY concat(call_flow,'::',reseller1_account_id) as name, 'reseller' as type ,customer_ipaddress as ip,count(*) as total_calls ,
sum(if(callstatus = 'answer',1,0)) as 'answer',
sum(if(callstatus = 'ring',1,0)) as 'ringing',
sum(if(callstatus = 'progress',1,0)) as 'progress'
from livecalls where reseller1_account_id is not NULL 
group by call_flow, reseller1_account_id order by total_calls desc ";

            //  $result = $DB1->query($str);
            $result = $this->db->query($str);
            $reseller_calls = $result->result_array();

            $return['customer_calls'] = array_merge($customer_calls, $reseller_calls);
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

}
