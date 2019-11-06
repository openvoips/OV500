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

class Payment_mod extends CI_Model {

    public $account_id;
    public $max_balance_limit = 999999.999999;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function check_payment($account_id, $order_id) {
        $final_return_array = array();
        try {
            $sql = "SELECT * FROM payment_tracking WHERE account_id='" . $account_id . "' AND order_id='" . $order_id . "' LIMIT 0,1";
            //echo $sql;die;
            $query = $this->db->query($sql);
            if ($query == null)
                return false;
            $num_rows = $query->num_rows();
            if ($num_rows < 1) {
                //return false;
                throw new Exception('Invalid Order');
            }
            $row = $query->row_array();
            $final_return_array['result'] = $row;
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Payment details fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function update_payment($account_id, $order_id, $data, $payment_method = '') {
        $log_data_array = array();
        $api_log_data_array = array();
        try {
            $tracking_data_array = array();
            if (isset($data['tracking_id']))
                $tracking_data_array['tracking_id'] = $data['tracking_id'];
            if (isset($data['order_status']))
                $tracking_data_array['order_status'] = $data['order_status'];
            if (isset($data['response_string'])) {
                $tracking_data_array['response_string'] = print_r($data['response_string'], true);
            }
            $this->db->trans_begin();
            $row_data = $this->check_payment($account_id, $order_id);
            if (isset($row_data['result']['amount']) && $row_data['result']['amount'] > 0) {
                if ($row_data['result']['payment_method'] == 'paypal')
                    $amount = $data['response_string']['mc_gross'] - $data['response_string']['mc_fee'];
                else
                    $amount = $row_data['result']['amount'];
            } else
                throw new Exception('Amount not found');
            //if(isset($row_data['result']) && $row_data['result']['order_status']=='initiated')



            if (count($tracking_data_array) > 0) {
                $where = "account_id='" . $account_id . "' AND order_id='" . $order_id . "'";
                $str = $this->db->update_string('payment_tracking', $tracking_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'payment_tracking', 'sql_key' => $where, 'sql_query' => $str);
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
                if (isset($data['order_status']) && $data['order_status'] == 'success') {
                    if ($payment_method == 'secure-trading') {
                        $payment_method_display = 'Debit / Credit Card';
                    } else {
                        $payment_method_display = $payment_method;
                    }
                    $api_request['account_id'] = $account_id;
                    $api_request['user_type'] = $data['user_type'];
                    $api_request['service_number'] = 'Bank Transfer Payment';
                    $api_request['amount'] = $amount;
                    $api_request['paid_on'] = date('Y-m-d H:i:s');
                    $api_request['notes'] = $payment_method_display . ' payment. Trans ID: (' . $data['tracking_id'] . ')';
                    $api_request['created_by'] = get_logged_account_id();
                    $api_request['request'] = 'ADDBALANCE';
                    $api_request['making_own_payment'] = 'Y';
                    if (isset($data['tracking_id'])) {
                        $api_request['transaction_id'] = $data['tracking_id'];
                    }
                    $api_response = callSdrAPI($api_request);
                    $api_result = json_decode($api_response, true);
                    //echo '<pre>';print_r($api_request);print_r($api_result);echo '</pre>';die;
                }
                set_activity_log($log_data_array);
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            set_activity_log($api_log_data_array);
            return $e->getMessage();
        }
    }

    function initiate_payment($account_id, $amount, $pay_data_array, $payment_method = '') {
        $log_data_array = array();
        try {
            $payment_tracking_array = array();

            $payment_tracking_array['account_id'] = $account_id;
            $payment_tracking_array['amount'] = $amount;
            $payment_tracking_array['payment_method'] = $payment_method;
            $payment_tracking_array['order_id'] = $pay_data_array['order_id'];
            $payment_tracking_array['order_status'] = 'initiated';
            $payment_tracking_array['send_string'] = print_r($pay_data_array, true);
            //	$user_data_array['order_date'] = $data['tariff_id_name'];	

            $this->db->trans_begin();
            if (count($payment_tracking_array) > 0) {
                $str = $this->db->insert_string($this->db->dbprefix('payment_tracking'), $payment_tracking_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $payment_id = $this->db->insert_id();

                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => $this->db->dbprefix('payment_tracking'), 'sql_key' => $this->payment_id, 'sql_query' => $str);
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                throw new Exception($error_array['message']);
            } else {
                $this->db->trans_commit();
                set_activity_log($log_data_array);
            }

            return true;
        } catch (Exception $e) {
            set_activity_log($log_data_array); //api log			
            return $e->getMessage();
        }
    }

    function add($data) {
        $api_log_data_array = array();
        try {
            $api_request = array();
            $api_request['account_id'] = $data['account_id'];
            $api_request['user_type'] = $data['user_type'];
            $api_request['service_number'] = $data['collection_option'];
            $api_request['amount'] = $data['amount'];
            $api_request['paid_on'] = $data['paid_on'];
            $api_request['notes'] = $data['notes'];
            $api_request['created_by'] = get_logged_account_id();
            $api_request['request'] = $data['payment_option'];
            $approvedby = $data['approvedby'];
            $transactiondetails = $data['transactiondetails'];
            if (isset($transactiondetails) && $transactiondetails != '') {
                $api_request['service_number'] = $api_request['service_number'] . ' - ' . ucwords($transactiondetails) . '';
            }

            if (isset($approvedby) && $approvedby != '') {
                $api_request['notes'] = $api_request['notes'] . ' ' . '(approved by ' . ucwords($approvedby) . ')';
            }

            if ($data['payment_option'] == 'ADDCREDIT' && isset($data['credit_scheduler_hour']) && $data['credit_scheduler_hour'] != '') {
                $api_request['schedulertime'] = $data['credit_scheduler_hour'];
            }
            //   echo '<pre>';
            //  print_r($api_request);
            // die;
            $api_response = callSdrAPI($api_request);

            // var_dump($api_response);
            //die;
            $api_result = json_decode($api_response, true);
            $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));
            if (!isset($api_result['error']) || $api_result['error'] == '1') {
                $this->db->trans_rollback();
                throw new Exception($api_result['message']);
            }

            ////set credit revert scheduler
            if (isset($data['credit_scheduler_execution_date']) && $data['credit_scheduler_execution_date'] != '') {
                $scheduler_data_array = array(
                    'account_id' => $data['account_id'],
                    'credit_amount' => $data['amount'],
                    'execution_date' => $data['credit_scheduler_execution_date'],
                    'status_id' => '0',
                    'created_by' => get_logged_account_id(),
                    'create_date' => date('Y-m-d H:i:s')
                );

                //  print_r($_POST);
                if (strpos($data['collection_option'], 'Emergency') !== false)
                    $scheduler_data_array['is_emergency_credit'] = 'Y';
                else
                    $scheduler_data_array['is_emergency_credit'] = 'N';
                // print_r($scheduler_data_array);
                // die;
                $str = $this->db->insert_string($this->db->dbprefix('credit_scheduler'), $scheduler_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->id = $this->db->insert_id();

                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => $this->db->dbprefix('credit_scheduler'), 'sql_key' => $this->id, 'sql_query' => $str);
            }



            set_activity_log($api_log_data_array); //api log



            return true;
        } catch (Exception $e) {
            set_activity_log($api_log_data_array); //api log			
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

    /* Add payment */

    function add_balance($data) {
        $log_data_array = array(); //reset array					

        try {
            $payment_data_array = $balance_data_array = array();

            $payment_data_array['account_id'] = $data['account_id'];
            $payment_data_array['payment_option_id'] = $data['payment_option'];
            $payment_data_array['amount'] = $data['amount'];
            $payment_data_array['paid_on'] = $data['paid_on'];
            $payment_data_array['notes'] = $data['notes'];

            $payment_data_array['created_by'] = $data['created_by']; //get_logged_account_id();		

            $notes = 'Current Credit: {CREDIT}, Current Outstanding Balance: {BALANCE}, Updated Credit: {UPDATED CREDIT}, Updated Outstanding Balance: {UPDATED BALANCE}';


            $this->db->trans_begin();


            $sql = "SELECT * FROM " . $this->db->dbprefix('balance') . " WHERE account_id='" . $data['account_id'] . "'";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $row = $query->row();
            if (isset($row)) {
                $id = $row->id;
                $credit_limit = $row->credit_limit;
                $balance = $row->balance;

                $notes = str_replace('{CREDIT}', $credit_limit, $notes);
                $notes = str_replace('{BALANCE}', $balance, $notes);


                $this->db->where('id', $id);

                $amount = $payment_data_array['amount'];

                if ($payment_data_array['payment_option_id'] == 'ADDBALANCE') {
                    $this->db->set('balance', 'balance-' . $amount, FALSE);
                    $notes = str_replace('{UPDATED CREDIT}', $credit_limit, $notes);
                    $notes = str_replace('{UPDATED BALANCE}', $balance - $amount, $notes);

                    $updated_balance = -($balance - $amount);
                    if ($updated_balance > $this->max_balance_limit) {
                        $error_message = 'Balance exceeds maximum amount';
                        throw new Exception($error_message);
                    }
                } elseif ($payment_data_array['payment_option_id'] == 'ADDCREDIT') {
                    $this->db->set('credit_limit', 'credit_limit+' . $amount, FALSE);
                    $notes = str_replace('{UPDATED CREDIT}', $credit_limit + $amount, $notes);
                    $notes = str_replace('{UPDATED BALANCE}', $balance, $notes);

                    $updated_limit = $credit_limit + $amount;
                    if ($updated_limit > $this->max_balance_limit) {
                        $error_message = 'Credit limit exceeds maximum amount';
                        throw new Exception($error_message);
                    }
                } elseif ($payment_data_array['payment_option_id'] == 'REMOVEBALANCE') {
                    $this->db->set('balance', 'balance+' . $amount, FALSE);
                    $notes = str_replace('{UPDATED CREDIT}', $credit_limit, $notes);
                    $notes = str_replace('{UPDATED BALANCE}', $balance + $amount, $notes);
                } elseif ($payment_data_array['payment_option_id'] == 'REMOVECREDIT') {
                    $this->db->set('credit_limit', 'credit_limit-' . $amount, FALSE);
                    $notes = str_replace('{UPDATED CREDIT}', $credit_limit - $amount, $notes);
                    $notes = str_replace('{UPDATED BALANCE}', $balance, $notes);
                }

                if (isset($data['maxcredit_limit'])) {
                    $this->db->set('maxcredit_limit', $data['maxcredit_limit'], FALSE);
                }

                $this->db->update('balance');

                $str = $this->db->last_query();
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => $this->db->dbprefix('balance'), 'sql_key' => $id, 'sql_query' => $str);
            } else {//add
                $balance_data_array['credit_limit'] = 0;
                $balance_data_array['balance'] = 0;
                $balance_data_array['account_id'] = $data['account_id'];
                if (isset($data['maxcredit_limit']))
                    $balance_data_array['maxcredit_limit'] = $data['maxcredit_limit'];

                $balance_data_array['maxcredit_limit'] = 99;

                if ($payment_data_array['payment_option_id'] == 'ADDBALANCE')
                    $balance_data_array['balance'] = -$payment_data_array['amount'];
                elseif ($payment_data_array['payment_option_id'] == 'ADDCREDIT')
                    $balance_data_array['credit_limit'] = $payment_data_array['amount'];
                elseif ($payment_data_array['payment_option_id'] == 'REMOVEBALANCE')
                    $balance_data_array['balance'] = $payment_data_array['amount'];
                elseif ($payment_data_array['payment_option_id'] == 'REMOVECREDIT')
                    $balance_data_array['credit_limit'] = -$payment_data_array['amount'];

                $notes = str_replace('{CREDIT}', 0, $notes);
                $notes = str_replace('{BALANCE}', 0, $notes);
                $notes = str_replace('{UPDATED CREDIT}', $balance_data_array['credit_limit'], $notes);
                $notes = str_replace('{UPDATED BALANCE}', $balance_data_array['balance'], $notes);

                $str = $this->db->insert_string($this->db->dbprefix('balance'), $balance_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->id = $this->db->insert_id();

                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => $this->db->dbprefix('balance'), 'sql_key' => $this->id, 'sql_query' => $str);
            }


            if (count($payment_data_array) > 0) {
                if ($payment_data_array['notes'] != '')
                    $payment_data_array['notes'] = $payment_data_array['notes'] . '<br>';
                $payment_data_array['notes'] = $payment_data_array['notes'] . $notes;
                $create_dt = date('Y-m-d H:i:s');
                $payment_data_array['create_dt'] = $create_dt;

                $str = $this->db->insert_string($this->db->dbprefix('payment_history'), $payment_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->payment_id = $this->db->insert_id();

                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => $this->db->dbprefix('payment_history'), 'sql_key' => $this->payment_id, 'sql_query' => $str);
            }

            if ($this->db->trans_status() === FALSE) {
                $error_message = 'transaction failed';
                $this->db->trans_rollback();
                return $error_message;
            } else {

                $this->db->trans_commit();
                set_activity_log($log_data_array);
            }




            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function update_max_credit_limit($data) {
        $log_data_array = array(); //reset array					

        try {
            $payment_data_array = $balance_data_array = array();


            $this->db->trans_begin();


            $sql = "SELECT * FROM " . $this->db->dbprefix('balance') . " WHERE account_id='" . $data['account_id'] . "'";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $row = $query->row();
            if (isset($row)) {//edit
                $id = $row->id;


                $this->db->where('id', $id);
                $this->db->set('maxcredit_limit', $data['maxcredit_limit'], FALSE);
                $this->db->update('balance');

                $str = $this->db->last_query();
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => $this->db->dbprefix('balance'), 'sql_key' => $id, 'sql_query' => $str);
            } else {
                return 'No entry at balance found';
            }




            if ($this->db->trans_status() === FALSE) {
                $error_message = 'transaction failed';
                $this->db->trans_rollback();
                return $error_message;
            } else {

                $this->db->trans_commit();
                set_activity_log($log_data_array);
            }




            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function get_balance($account_id) {
        try {
            $final_return_array = array();
            $sql = "SELECT id, account_id, credit_limit, balance, credit_limit - balance usable_balance, maxcredit_limit FROM customer_balance WHERE account_id='" . $account_id . "' LIMIT 0,1";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            //print_r($query->row_array());die;
            $final_return_array['result'] = $query->row_array();

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Balance fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function get_payment_gateways($account_id) {
        $final_return_array = array();
        try {
            $sql = "SELECT * FROM sys_payment_credentials WHERE account_id ='" . $account_id . "'  AND status='Y' ORDER BY `payment_method` ASC ";
            //echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $num_rows = $query->num_rows();
            if ($num_rows < 1) {
                throw new Exception('No payment gateways found');
            }
            foreach ($query->result_array() as $row) {
                $payment_method = $row['payment_method'];
                $final_return_array['result'][$payment_method] = $row;
            }
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Payment gateways fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_payment_options() {
        $final_return_array = array();
        try {
            $sql = "SELECT option_id, option_name FROM sys_rule_options WHERE status_id='1' AND option_group='payment'";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $final_return_array['result'] = array();
            foreach ($query->result_array() as $row) {
                $option_id = $row['option_id'];
                $final_return_array['result'][$option_id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Payment Options fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    /* Payment List */

    function get_data($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        $final_return_array = array();
        try {
            /* $sql = "SELECT SQL_CALC_FOUND_ROWS 				
              ph.payment_id, ph.account_id, ph.payment_option_id, ph.amount, ph.paid_on, ph.notes, ph.created_by, ph.create_dt, po.option_name payment_option, po.option_id_name
              FROM ".$this->db->dbprefix('payment_history')." ph LEFT JOIN ".$this->db->dbprefix('rule_options')." po ON ph.payment_option_id=po.option_id_name WHERE 1 "; */


            $sql = "SELECT SQL_CALC_FOUND_ROWS 				
				ph.payment_id, ph.account_id, ph.payment_option_id, ph.amount, ph.paid_on, ph.notes, ph.created_by, ph.create_dt, 
				po.display_text payment_option, po.term option_id_name
					FROM payment_history ph LEFT JOIN sys_sdr_terms po ON ph.payment_option_id=po.term WHERE 1 ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id' || $key == 'payment_option_id')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'date_range') {
                            $range = explode(' - ', $filter_data['date_range']);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);

                            $start_dt = $range[0];
                            $end_dt = $range[1];

                            $sql .= " AND ph.paid_on BETWEEN '" . $start_dt . "' AND '" . $end_dt . "' ";
                        } elseif (in_array($key, array('s_account_manager', 's_parent_account_id', 's_superagent'))) {
                            continue;
                        } else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }


            /////////////
            if (isset($filter_data['s_superagent']) && $filter_data['s_superagent'] != '') {
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $filter_data['s_superagent'] . "'";
                if (isset($filter_data['am_under_sm']) && $filter_data['am_under_sm'] != '') {
                    $sub_sub_sql .= " AND user_access_id_name='" . $filter_data['am_under_sm'] . "'";
                }
                $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

                $sub_query = $this->db->query($sub_sql);
                if (!$sub_query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $user_access_id_name_array = array();
                if ($sub_query->row() > 0) {
                    foreach ($sub_query->result_array() as $row) {
                        $user_access_id_name_array[] = $row['user_access_id_name'];
                    }
                }
                $account_id_str = implode("','", $user_access_id_name_array);
                $account_id_str = "'" . $account_id_str . "'";

                $sql .= " AND account_id IN(" . $account_id_str . ")";
            } elseif (isset($filter_data['s_account_manager']) && $filter_data['s_account_manager'] != '') {
                $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $filter_data['s_account_manager'] . "'";
                $sql .= " AND account_id IN(" . $sub_sql . ")";
            } elseif (isset($filter_data['s_parent_account_id'])) {   //&& $filter_data['s_parent_account_id']!=''
                $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $filter_data['s_parent_account_id'] . "' ";
                $sql .= " AND account_id IN(" . $sub_sql . ")";
            }
            ////////////			



            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY `create_dt` DESC ";
            }

            $limit_from = intval($limit_from);

            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            //	echo $sql;
            $final_return_array['sql'] = $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;

            $final_return_array['result'] = array();
            foreach ($query->result_array() as $row) {
                $payment_id = $row['payment_id'];
                $final_return_array['result'][$payment_id] = $row;
            }


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Payment History fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function payment_confirm($account_id, $user_type, $method, $data) {
        //$api_log_data_array = array();
        $log_data_array = array(); //reset array	

        try {
            $payment_data_array = json_decode($data, true);
            //$this->db->trans_begin();
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'pay_log', 'sql_key' => $account_id, 'sql_query' => json_encode($data));

            /* if ($this->db->trans_status() === FALSE)
              {
              $error_array = $this->db->error();
              $this->db->trans_rollback();
              return $error_array['message'];
              }
              else
              {
              $this->db->trans_commit();
              //set_activity_log($log_data_array);
              } */
            $transaction_id = '';
            if (isset($payment_data_array['transactions'][0]['related_resources']['0']['sale']['id']))
                $transaction_id = $payment_data_array['transactions'][0]['related_resources']['0']['sale']['id'];
            /////////////
            $api_request['account_id'] = $account_id;
            $api_request['user_type'] = $user_type; //??
            $api_request['service_number'] = 'Bank Transfer Payment';
            $api_request['amount'] = $payment_data_array['transactions'][0]['amount']['total'];
            $api_request['paid_on'] = date('Y-m-d H:i:s');
            $api_request['notes'] = 'Paypal payment. Trans ID: (' . $transaction_id . ')';
            $api_request['created_by'] = get_logged_account_id();
            $api_request['request'] = 'ADDBALANCE';


            $api_response = callSdrAPI($api_request);
            $api_result = json_decode($api_response, true);

            $log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));
            if (!isset($api_result['error']) || $api_result['error'] == '1') {
                $this->db->trans_rollback();
                throw new Exception($api_result['message']);
            }

            set_activity_log($log_data_array); //api log
            //////////////////	
        } catch (Exception $e) {
            set_activity_log($log_data_array); //api log
            //$this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function trace_payment_search($order_by = '', $limit_to = '', $limit_from = '', $search_data = array()) {
        try {
            $final_return_array = array();

            //$sql = "SELECT SQL_CALC_FOUND_ROWS payment_id,order_id,amount,tracking_id,order_status,payment_method,account_id,response_string,order_date FROM ".$this->db->dbprefix('payment_tracking')." WHERE 1";	

            $sql = "SELECT SQL_CALC_FOUND_ROWS payment_id,order_id,amount,tracking_id,order_status,payment_method,account_id,send_string,response_string,order_date,
			ua.company_name 
			FROM " . $this->db->dbprefix('payment_tracking') . " pt LEFT JOIN " . $this->db->dbprefix('user_access') . " ua ON pt.account_id=ua.user_access_id_name  WHERE 1";

            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'order_date') {
                            $range = explode(' - ', $search_data['order_date']);
                            $start_dt = $range[0];
                            $end_dt = $range[1];
                            $sql .= " AND order_date BETWEEN '" . $start_dt . "' AND '" . $end_dt . "' ";
                        } elseif ($key == 'card_number') {
                            $sql .= " AND (send_string like '%" . $value . "%' OR response_string like '%" . $value . "%')";
                        } elseif (in_array($key, array('company_name'))) {
                            $sql .= " AND $key  like '%" . $value . "%' ";
                        } else
                            $sql .= " AND $key  ='" . $value . "' ";
                    }
                }
            }


            $orderby = ' ORDER BY payment_id DESC ';


            $query_str = $sql . $orderby;

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $query_str .= " LIMIT $limit_from, $limit_to";
            //echo $query_str;
            $query = $this->db->query($query_str);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }


            $result = $query->result_array();

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $final_return_array['total_row'] = $row_count->total;

            $final_return_array['result'] = $result;
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Record fetched successfully';


            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
        }
    }

    function get_credit_scheduler($filter_data = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT *			
			 FROM " . $this->db->dbprefix('credit_scheduler') . " 
			 WHERE 1 ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'account_id' || $key == 'status_id')
                            $sql .= " AND $key ='" . $value . "' ";
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            $sql .= " ORDER BY status_id";

            //echo $sql;
            $query = $this->db->query($sql);

            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = Array();
            foreach ($query->result_array() as $row) {
                $id = $row['id'];
                $final_return_array['result'][$id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Credit Scheduler fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function cancel_scheduler($account_id, $id_array) {
        try {
            $this->db->trans_begin();

            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();
                $scheduler_data_array = array();
                ////delete user ///////
                $sql = "SELECT * FROM " . $this->db->dbprefix('credit_scheduler') . " WHERE account_id='" . $account_id . "'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $scheduler_data_array = array('status_id' => '2');
                    $where = "account_id='" . $account_id . "' AND id='" . $id . "'";
                    $str = $this->db->update_string($this->db->dbprefix('credit_scheduler'), $scheduler_data_array, $where);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'update', 'sql_table' => $this->db->dbprefix('credit_scheduler'), 'sql_key' => $where, 'sql_query' => $str);
                }

                set_activity_log($log_data_array);
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function get_card_details($account_id) {
        try {
            $final_return_array = array();

            $sql = "SELECT id, account_id,card_name,card_data 
			FROM " . $this->db->dbprefix('account_card_details') . " WHERE account_id='" . $account_id . "'";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            //$result=$query->result_array();	
            //$final_return_array['result']=$result;	

            foreach ($query->result_array() as $row) {
                $card_data_en = $row['card_data'];
                $card_data_de = base64_decode($card_data_en);
                $card_data_array = json_decode($card_data_de, true);

                $row = array_merge($row, $card_data_array);
                $final_return_array['result'][] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Record fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
        }
    }

    function save_card_details($data) {
        try {
            $log_data_array = array(); //reset array

            $card_data_array = array();
            $update_or_insert = '';

            $account_id = $data['account_id'];
            $card_number = $data['card_number'];
            $expirymonth = $data['card_expirymonth'];
            $expiryyear = $data['card_expiryyear'];
            $securitycode = $data['card_securitycode'];

            $card_name = '';
            $card_number_length = strlen($card_number);
            for ($i = 0; $i < $card_number_length; $i++) {
                if ($i < $card_number_length - 4) {
                    $card_name .= 'x';
                } else {
                    $card_name .= $card_number[$i];
                }
            }


            ///check card exists or not
            $sql = "SELECT id FROM " . $this->db->dbprefix('account_card_details') . " WHERE account_id='" . $data['account_id'] . "' AND card_name='" . $card_name . "'";
            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows > 0)
                $update_or_insert = 'update';
            else
                $update_or_insert = 'insert';

            if ($update_or_insert == 'update') {


                $card_data_en_array = array(
                    'card_number' => $card_number,
                    'expirymonth' => $expirymonth,
                    'expiryyear' => $expiryyear,
                    'securitycode' => $securitycode,
                );
                $card_data_en = json_encode($card_data_en_array);
                $card_data_array['card_data'] = base64_encode($card_data_en);
                $card_data_array['card_name'] = $card_name;

                $where = "account_id='" . $data['account_id'] . "' AND card_name='" . $card_name . "'";
                $str = $this->db->update_string($this->db->dbprefix('account_card_details'), $card_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            } elseif ($update_or_insert == 'insert') {
                $card_data_array['account_id'] = $data['account_id'];
                $card_data_array['card_name'] = $card_name;


                $card_data_en_array = array(
                    'card_number' => $card_number,
                    'expirymonth' => $expirymonth,
                    'expiryyear' => $expiryyear,
                    'securitycode' => $securitycode,
                );
                $card_data_en = json_encode($card_data_en_array);
                $card_data_array['card_data'] = base64_encode($card_data_en);

                if (count($card_data_array) > 0) {
                    $str = $this->db->insert_string($this->db->dbprefix('account_card_details'), $card_data_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                }
            }



            /////////////////




            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function delete_card_details($account_id, $id_array) {
        try {
            //check status

            $this->db->trans_begin();

            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();

                ////delete card ///////
                $sql = "SELECT * FROM " . $this->db->dbprefix('account_card_details') . " WHERE id='" . $id . "' AND account_id='" . $account_id . "' ";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete($this->db->dbprefix('account_card_details'), array('id' => $id, 'account_id' => $account_id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => $this->db->dbprefix('account_card_details'), 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'Service', 'sql_key' => $id, 'sql_query' => '');
                set_activity_log($log_data_array);
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

}
