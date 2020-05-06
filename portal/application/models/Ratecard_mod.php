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

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ratecard_mod extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function add($data) {
        $log_data_array = array();
        $data_array = array();
        if (isset($data['frm_name']))
            $data_array['ratecard_name'] = $data['frm_name'];
        if (isset($data['frm_currency']))
            $data_array['ratecard_currency_id'] = $data['frm_currency'];
        if (isset($data['frm_type']))
            $data_array['ratecard_type'] = strtoupper($data['frm_type']);
        $data_array['created_by'] = get_logged_account_id();
        $data_array['ratecard_id'] = generate_key($data['frm_name'], '');
        if (isset($data['ratecard_for']))
            $data_array['ratecard_for'] = $data['ratecard_for'];

        $str = $this->db->insert_string('ratecard', $data_array);
        $result = $this->db->query($str);
        if ($result) {
            $ratecard_id = $data_array['ratecard_id'];
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'ratecard', 'sql_key' => '', 'sql_query' => $str);
            set_activity_log($log_data_array);
            return array('status' => true, 'id' => $ratecard_id, 'msg' => 'Ratecard Added Successfully in the system.');
        } else {
            $error_array = $this->db->error();
            return array('status' => false, 'msg' => $error_array['message']);
        }
    }

    public function update($data) {
        $log_data_array = array();
        $data_array = array();
        if (isset($data['frm_name']))
            $data_array['ratecard_name'] = $data['frm_name'];
        if (isset($data['ratecard_for']))
            $data_array['ratecard_for'] = $data['ratecard_for'];

        if (isset($data['frm_key'])) {
            $this->db->trans_begin();
            if (count($data_array) > 0) {
                $where = "ratecard_id='" . $data['frm_id'] . "'";
                $str = $this->db->update_string('ratecard', $data_array, $where);

                $result = $this->db->query($str);
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'ratecard', 'sql_key' => $where, 'sql_query' => $str);
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return array('status' => false, 'msg' => $error_array['message']);
            } else {
                $this->db->trans_commit();
                set_activity_log($log_data_array);
                return array('status' => true, 'msg' => 'Successfully updated');
            }
        } else {
            return array('status' => false, 'msg' => 'KEY not found');
        }
    }

    public function delete($data) {
        try {
            $this->db->trans_begin();
            foreach ($data['delete_id'] as $id) {
                $log_data_array = array();
                $q = $this->db->where('ratecard_id', param_decrypt($id))->get('ratecard');
                $row = $q->result_array();
                if (count($row) > 0) {
                    $ratecard_type = $row[0]['ratecard_type'];
                    $ratecard_for = $row[0]['ratecard_for'];
                    if ($ratecard_type == 'CARRIER')
                        $rate_table_name = 'carrier_rates';
                    elseif ($ratecard_type == 'CUSTOMER')
                        $rate_table_name = 'customer_rates';
                    else {
                        throw new Exception('type & ratecard for mismatch');
                    }
                    $data_dump = serialize($row);
                    $str = $this->db->where('ratecard_id', param_decrypt($id))->get_compiled_delete('ratecard');
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'ratecard', 'sql_key' => param_decrypt($id), 'sql_query' => $data_dump);
                    $q1 = $this->db->where('ratecard_id', param_decrypt($id))->get($rate_table_name);
                    $row1 = $q1->result_array();
                    if (count($row1) > 0) {
                        $data_dump = serialize($row1);
                        $str1 = $this->db->where('ratecard_id', param_decrypt($id))->get_compiled_delete($rate_table_name);
                        $result = $this->db->query($str1);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => $rate_table_name, 'sql_key' => param_decrypt($id), 'sql_query' => $data_dump);
                    }
                }

                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'RATECARD', 'sql_key' => param_decrypt($id), 'sql_query' => $str);
                set_activity_log($log_data_array);
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return array('status' => false, 'msg' => 'failed deletion :: ' . $error_array['message']);
            } else {
                $this->db->trans_commit();
                return array('status' => true, 'msg' => 'Successfully deleted');
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return array('status' => false, 'msg' => 'failed deletion :: ' . $e->getMessage());
        }
    }

    public function get_data($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        try {
            $am_ratecard_id_array = array();
            if (isset($filter_data['logged_account_type']) && isset($filter_data['logged_current_customer_id']) && $filter_data['logged_account_type'] == 'ACCOUNTS') {
                $sub_sql = "SELECT ratecard_id, ratecard_for FROM customers ua, account u, tariff_ratecard_map trm WHERE  ua.account_id=u.account_id AND  u.tariff_id=trm.tariff_id AND  ua.account_manager='" . $filter_data['logged_current_customer_id'] . "'";
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $am_ratecard_id_array[] = $row['ratecard_id'];
                    }
                }
            }

            $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
            $sub = $this->subquery->start_subquery('SELECT');
            $sub->select('name')->from('sys_currencies');
            $sub->where('ratecard.ratecard_currency_id = sys_currencies.currency_id');
            $this->subquery->end_subquery('currency_name');

            $sub = $this->subquery->start_subquery('SELECT');
            $sub->select('symbol')->from('sys_currencies');
            $sub->where('ratecard.ratecard_currency_id = sys_currencies.currency_id');
            $this->subquery->end_subquery('currency_symbol');

            $sub = $this->subquery->start_subquery('SELECT');
            $sub->select('detail_name')->from('sys_currencies');
            $sub->where('ratecard.ratecard_currency_id = sys_currencies.currency_id');
            $this->subquery->end_subquery('currency_detail');

            $sub = $this->subquery->start_subquery('select');
            $sub->select('count(*)')->from('tariff_ratecard_map');
            $sub->where('ratecard.ratecard_id = tariff_ratecard_map.ratecard_id');
            $this->subquery->end_subquery('tariff_count');


            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'ratecard_currency_id' || $key == 'ratecard_id')
                            $this->db->where($key, $value);
                        elseif (in_array($key, array('logged_account_type', 'logged_current_customer_id', 'logged_account_level'))) {
                            
                        } else {
                            $this->db->like($key, rtrim($value, '%'), 'after');
                        }
                    }
                }
            }


            if (get_logged_account_level() != 0) {
                $this->db->where('created_by', get_logged_account_id());
            } else {
                $sub = $this->subquery->start_subquery('where_in');
                $sub->select('account_id')->from('customers');
                $sub->where("account_type = 'ADMIN' or account_type ='SUBADMIN'");
                $this->subquery->end_subquery('created_by', TRUE);
            }
            if (is_string($order_by) && $order_by == '') {
                $this->db->order_by('id', 'DESC');
            } else {
                foreach ($order_by as $k => $v) {
                    $this->db->order_by($k, $v);
                }
            }
            if (count($am_ratecard_id_array) > 0) {
                $this->db->where_in('ratecard_id', $am_ratecard_id_array);
            }
            $this->db->limit(intval($limit_from), intval($limit_to));
            $q = $this->db->get('ratecard');
            // echo $this->db->last_query();
            if (!$q) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = $q->result_array();
            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
            $final_return_array["total"] = $query->row()->Count;

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Ratecard List fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    public function generate_key() {
        $prefix = 'RC';
        $counter = 0;
        $this->db->like('id', $prefix, 'after')->limit(1)->order_by('id', 'DESC')->select('id');
        $query = $this->db->get('ratecard');
        $result = $query->result_array();
        if (count($result) > 0)
            $counter = strrev((int) strrev($result[0]['id']));

        while (1) {
            $counter++;
            $key = $prefix . sprintf('%06d', $counter);

            $this->db->like('id', $key)->limit(1)->select('id');
            $query = $this->db->get('ratecard');
            $result = $query->result_array();
            if (count($result) > 0) {
                
            } else
                break;
        }

        return $key;
    }

}
