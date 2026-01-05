<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
class Dashboard_mod extends CI_Model {

    public $id;
    public $user_access_id = '';
    public $select_sql;
    public $total_count_sql;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_reseller_dashboard() {

 
        $final_return_array = array('result' => array());
        try {


            $sql = " SELECT
customer_balance.balance
from customer_balance
where account_id = '" . get_logged_account_id() . "';";

            $query = $this->db->query($sql);
            $data = $query->result_array();
            //echo  $sql;ddd($data);die;
            $final_return_array['result']['balance'] = $data[0];

            $DB1 = $this->load->database('cdrdb', true);

            $logindata = get_logged_data();
            $filter_field = 'reseller1_account_id';
            $callcost_field = 'reseller1_callcost_total';
            if ($logindata['account_type'] == 'RESELLER') {
                $filter_field = "reseller" . $logindata['account_level'] . "_account_id";
                $callcost_field = "reseller" . $logindata['account_level'] . "_callcost_total";
            }

            $sql = "SELECT  if(cdr_type = 'DID',1,0) did, if(cdr_type = 'PSTN',1,0) pstn , sum($callcost_field) cost  FROM " . date('Ym') . "_ratedcdr where $filter_field = '" . get_logged_account_id() . "' and date(end_time) = CURRENT_DATE() and billsec > 0;";

            $result = $DB1->query($sql);
            if (!$result) {
                $data[0] = 0;
            } else {
                $data = $result->result_array();
            }
            $final_return_array['result']['total'] = $data[0];

            $sql = "SELECT carrier_dst_callee, carrier_dst_caller FROM " . date('Ym') . "_ratedcdr where $filter_field =   '" . get_logged_account_id() . "' and date(start_stamp) = CURRENT_DATE() and billsec = 0;";
            $result = $DB1->query($sql);

            if (!$result) {
                $data[0] = 0;
            } else {
                $data = $result->result_array();
            }
            $final_return_array['result']['misccalls'] = $data[0];

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Users fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_customer_dashboard() {
        $final_return_array = array('result' => array());
        try {          

            $sql = " SELECT
customer_balance.balance
from customer_balance
where account_id = '" . get_logged_account_id() . "';";

            $query = $this->db->query($sql);
            $data = $query->result_array();
            //echo  $sql;ddd($data);die;
            $final_return_array['result']['balance'] = $data[0];

            $DB1 = $this->load->database('cdrdb', true);

            $sql = "SELECT  if(cdr_type = 'DID',1,0) did, if(cdr_type = 'PSTN',1,0) pstn , sum(customer_callcost_total) cost  FROM " . date('Ym') . "_ratedcdr where customer_account_id = '" . get_logged_account_id() . "' and date(end_time) = CURRENT_DATE();";

            $result = $DB1->query($sql);
            if (!$result) {
                $data[0] = 0;
            } else {
                $data = $result->result_array();
            }
            $final_return_array['result']['total'] = $data[0];

            $sql = "SELECT carrier_dst_callee, carrier_dst_caller FROM " . date('Ym') . "_cdr where customer_account_id =   '" . get_logged_account_id() . "' and date(start_stamp) = CURRENT_DATE();";
            $result = $DB1->query($sql);

            if (!$result) {
                $data[0] = 0;
            } else {
                $data = $result->result_array();
            }
            $final_return_array['result']['misccalls'] = $data[0];

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Users fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

}
