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

class Api_mod extends CI_Model {

    public $account_id;
    public $max_balance_limit = 999999.999999;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function didsetupcharge($data, $date) {
        // $date =date('%Y-%m-%d');
        try {
            $didcharge_data_array = array();
            $this->db->trans_begin();
            $didcharge_data_array['account_id'] = $data['account_id'];
            $didcharge_data_array['payment_option_id'] = $data['request'];
            $didcharge_data_array['amount'] = $data['amount'];
            $didcharge_data_array['paid_on'] = $data['paid_on'];
            $didcharge_data_array['created_by'] = $data['created_by'];
            $didcharge_data_array['create_dt'] = date('Y-m-d H:s:i');
            $didcharge_data_array['notes'] = $data['service_number'];

            $yearmonth = date('Ym');
            $total_cost = $data['amount'];
            $service_startdate = date('Y-m-d h:s:i');
            $service_stopdate = date('Y-m-d h:s:i');
            $date = date('Y-m-d h:s:i');
            $query = sprintf("INSERT INTO customer_sdr( account_id, rule_type, yearmonth, service_number, service_charges, detail, otherdata, action_date, tax1_cost, tax2_cost, tax3_cost, cost, total_cost, total_tax, service_startdate, service_stopdate,tax1,tax2,tax3,actiondate) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', Now(), '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s');", $account_id, $data['request'], $yearmonth, $didcharge_data_array['notes'], 0, 0, 0, 0, 0, 0, 0, $total_cost, 0, $service_startdate, $service_stopdate, 0, 0, 0, $date);

            $this->db->query($query);
            //  echo $this->db->last_query();

            if ($this->db->trans_status() === FALSE) {
                $error_message = 'transaction failed';
                $this->db->trans_rollback();
                return $error_message;
            } else {
                $this->db->trans_commit();
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function save_payment($data) {
        try {
            $payment_data_array = array();
            $this->db->trans_begin();
            $payment_data_array['account_id'] = $data['account_id'];
            $payment_data_array['payment_option_id'] = $data['request'];
            $payment_data_array['amount'] = $data['amount'];
            $payment_data_array['paid_on'] = $data['paid_on'];
            $payment_data_array['created_by'] = $data['created_by'];
            $payment_data_array['create_dt'] = date('Y-m-d H:s:i');
            $payment_data_array['notes'] = $data['service_number'];

            $str = $this->db->insert_string('payment_history', $payment_data_array);
            $result = $this->db->query($str);
            $account_id = $data['account_id'];
            $amount = $data['amount'];

            if ($data['request'] == 'REMOVEBALANCE') {
                $sql = "update customer_balance set balance = balance + $amount where account_id = '$account_id'";
            } if ($data['request'] == 'ADDBALANCE') {
                $sql = "update customer_balance set balance = balance - $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'ADDCREDIT') {
                $sql = "update customer_balance set credit_limit = credit_limit + $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'REMOVECREDIT') {
                $sql = "update customer_balance set credit_limit = credit_limit - $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'ADDTESTBALANCE') {
                $sql = "update customer_balance set balance = balance - $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'REMOVETESTBALANCE') {
                $sql = "update customer_balance set balance = balance + $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'BALANCETRANSFERADD') {
                $sql = "update customer_balance set balance = balance - $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'BALANCETRANSFERREMOVE') {
                $sql = "update customer_balance set balance = balance + $amount where account_id = '$account_id'";
            }

            $this->db->query($sql);
            //  echo $this->db->last_query();

            $yearmonth = date('Ym');
            $total_cost = $data['amount'];
            $service_startdate = date('Y-m-d h:s:i');
            $service_stopdate = date('Y-m-d h:s:i');
            $date = date('Y-m-d h:s:i');
            $query = sprintf("INSERT INTO customer_sdr( account_id, rule_type, yearmonth, service_number, service_charges, detail, otherdata, action_date, tax1_cost, tax2_cost, tax3_cost, cost, total_cost, total_tax, service_startdate, service_stopdate,tax1,tax2,tax3,actiondate) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', Now(), '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s');", $account_id, $data['request'], $yearmonth, $payment_data_array['notes'], 0, 0, 0, 0, 0, 0, 0, $total_cost, 0, $service_startdate, $service_stopdate, 0, 0, 0, $date);

            $this->db->query($query);
            //  echo $this->db->last_query();

            if ($this->db->trans_status() === FALSE) {
                $error_message = 'transaction failed';
                $this->db->trans_rollback();
                return $error_message;
            } else {
                $this->db->trans_commit();
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

}
