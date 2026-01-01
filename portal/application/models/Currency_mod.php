<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */

class Currency_mod extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_currency($order_by, $filter_data) {

        $currencies_table = 'sys_currencies';
        try {



            $sql = "SELECT currency_id, symbol,  name FROM sys_currencies where status_id = '1' ORDER BY display_sequence,  name";
            $q = $this->db->query($sql);
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

            foreach ($filter_data as $key => $value) {
                if (strlen(trim($value)) > 0) {
                    if ($key == 'currency' || $key == 'currency_id') {
                        $this->db->where($key, $value);
                    }
                }
            }


            $this->db->order_by('date', 'DESC');
            $this->db->limit(intval($limit_to), intval($limit_from));

            $q = $this->db->get($table_name);
             if (!$q) {
                $error_array = $this->db->error();
            }

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

            $str = "update sys_currencies  set symbol = '&#x20b9;'  where name='INR'";
            $this->db->query($str);
            $str = "update sys_currencies  set symbol = '&euro;'  where name='EURO'";
            $this->db->query($str);
            $str = "update sys_currencies  set symbol = '&#163;'  where name='GBP'";
            $this->db->query($str);
        } else {
            $error_array = $this->db->error();
            return array('status' => false, 'msg' => $error_array['message']);
        }
    }

}
