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

class Sysconfig_mod extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_currency($order_by, $filter_data) {

        $currencies_table = 'sys_currencies';
        try {
            $this->db->select("SQL_CALC_FOUND_ROWS *, '$currencies_table' as table_name", FALSE);
            $this->db->order_by('currency_id', 'DESC');
            $q = $this->db->get($currencies_table);
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'currency_id') {
                            $this->db->where($key, $value);
                        } else {
                            $this->db->where($key, $value);
                        }
                    }
                }
            }

            $final_return_array['result'] = $q->result_array();
            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
            $final_return_array["total"] = $query->row()->Count;
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Currency List fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_exchange_rate($order_by, $filter_data, $limit_to = 1000, $limit_from = 0, $option_param = array()) {


        print_r($filter_data);
        $table_name = 'sys_currencies_conversions';
        try {

            $this->db->select("SQL_CALC_FOUND_ROWS *, '$table_name' as table_name", FALSE);
            $sub = $this->subquery->start_subquery('select');
            $sub->select('name')->from('sys_currencies');
            $sub->where('sys_currencies.currency_id = ' . $table_name . '.currency_id');
            $this->subquery->end_subquery('currency_name');
            $sub = $this->subquery->start_subquery('select');
            $sub->select('symbol')->from('sys_currencies');
            $sub->where('sys_currencies.currency_id = ' . $table_name . '.currency_id');
            $this->subquery->end_subquery('currency_symbol');
            $sub = $this->subquery->start_subquery('select');
            $sub->select('detail_name')->from('sys_currencies');
            $sub->where('sys_currencies.currency_id = ' . $table_name . '.currency_id');
            $this->subquery->end_subquery('detail_name');
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'currency_id') {
                            $this->db->where($key, $value);
                        } else {
                            $this->db->where($key, $value);
                        }
                    }
                }
            }

            $this->db->order_by('date', 'DESC');
            $this->db->limit(intval($limit_to), intval($limit_from));

            $q = $this->db->get($table_name);

            if (!$q) {
                $error_array = $this->db->error();
            }

//            $sql = $this->db->last_query();
//            echo $sql;
            $final_return_array['result'] = $q->result_array();

            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
            $final_return_array["total"] = $query->row()->Count;
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Currency exchange Rates List fetched successfully';

            //var_dump($final_return_array);
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function add($data) {
        $log_data_array = array();
        $data_array = array();
        if (isset($data['currency']))
            $data_array['currency_id'] = $data['currency'];
        if (isset($data['exc_rate']))
            $data_array['ratio'] = $data['exc_rate'];
        $date = date('Y-m-d h:i:s');
        $data_array['date'] = $date;
        $str = $this->db->insert_string('sys_currencies_conversions', $data_array);
        $result = $this->db->query($str);
        if ($result) {
            $currency_id = $data_array['currency_id'];
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'sys_currencies_conversions', 'sql_key' => '', 'sql_query' => $str);
            set_activity_log($log_data_array);
            return array('status' => true, 'id' => $currency_id, 'msg' => 'Ratecard Added Successfully in the system.');
        } else {
            $error_array = $this->db->error();
            return array('status' => false, 'msg' => $error_array['message']);
        }
    }

    function inConfig($data) {
//        print_r($data);
//        die;
           $table_name = 'sys_invoice_config';          
        try {            
            if ($data['action'] == 'search') {
                if ($data['logged_user_type'] == 'ADMIN' or $data['logged_user_type'] == 'SUBADMIN' or $data['logged_user_type'] == 'ACCOUNTS') {
                    $account_id = 'ADMIN';
                } else {
                    $account_id = $data['logged_current_customer_id'];
                }
                $this->db->select("SQL_CALC_FOUND_ROWS *, '$table_name' as table_name", FALSE);
                $this->db->where('account_id', $account_id);               
                $q = $this->db->get($table_name);
                $final_return_array['result'] = $q->result_array();
                $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
                $final_return_array["total"] = $query->row()->Count;
                $final_return_array['status'] = 'success';
                $final_return_array['message'] = 'Payment List fetched successfully';
                return $final_return_array;
            } else if ($data['action'] == 'OkSaveData') {
                if ($data['logged_user_type'] == 'ADMIN' or $data['logged_user_type'] == 'SUBADMIN' or $data['logged_user_type'] == 'ACCOUNTS') {
                    $account_id = 'ADMIN';
                } else {
                    $account_id = $data['logged_current_customer_id'];
                }
                
                $subquery = '';
                $subquery2 = '';
                if(strlen($data['logo'])> 0){
                    $subquery = ", logo ='" . $data['logo'] ."'";
                    $subquery2 = ", logo = values(logo)";
                }
                $SQl = "insert into " . $table_name . " set  account_id ='" . $account_id . "', company_name = '".$data['company_name']."', address= '" . $data['address'] . "', bank_detail='" . $data['bank_detail'] . "', support_text='" . $data['support_text'] . "', footer_text='" . $data['footer_text'] . "' $subquery ON DUPLICATE KEY UPDATE  company_name=values(company_name), address=values(address), bank_detail=values(bank_detail), support_text=values(support_text), footer_text=values(footer_text) $subquery2";
                $query = $this->db->query($SQl);
                
               // echo $SQl;
                return true;
            }
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
        
        
    }

    function pGConfig($data) {
        $table_name = 'sys_payment_credentials';
        try {            
            if ($data['action'] == 'search') {
                if ($data['logged_user_type'] == 'ADMIN' or $data['logged_user_type'] == 'SUBADMIN' or $data['logged_user_type'] == 'ACCOUNTS') {
                    $account_id = 'ADMIN';
                } else {
                    $account_id = $data['logged_current_customer_id'];
                }
                $this->db->select("SQL_CALC_FOUND_ROWS *, '$table_name' as table_name", FALSE);
                $this->db->where('account_id', $account_id);
                $this->db->where('payment_method', 'paypal-sdk');
                $this->db->where('status', 'Y');
                $q = $this->db->get($table_name);
                $final_return_array['result'] = $q->result_array();
                $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
                $final_return_array["total"] = $query->row()->Count;
                $final_return_array['status'] = 'success';
                $final_return_array['message'] = 'Payment List fetched successfully';
                return $final_return_array;
            } else if ($data['action'] == 'OkSaveData') {
                if ($data['logged_user_type'] == 'ADMIN' or $data['logged_user_type'] == 'SUBADMIN' or $data['logged_user_type'] == 'ACCOUNTS') {
                    $account_id = 'ADMIN';
                } else {
                    $account_id = $data['logged_current_customer_id'];
                }

                if (strlen($data['business']) == 0) {
                    return FALSE;
                }
                if (strlen($data['pdt_identity_token']) == 0) {
                    return FALSE;
                }
                if (strlen($data['logged_current_customer_id']) == 0) {
                    return FALSE;
                }

                $credentials = array('business' => $data['business'], 'pdt_identity_token' => $data['pdt_identity_token']);
                $SQl = "insert into " . $table_name . " set  account_id ='" . $account_id . "', payment_method = 'paypal-sdk', credentials= '" . json_encode($credentials) . "', status='Y' ON DUPLICATE KEY UPDATE credentials=values(credentials), account_id = values(account_id), payment_method=values(payment_method), status=values(status)";
                $query = $this->db->query($SQl);
                return true;
            }
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

}
