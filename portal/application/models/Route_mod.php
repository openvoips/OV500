<?php

// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
// OV500 Version 2.0.0
// Copyright (C) 2019-2021 Openvoips Technologies   
// http://www.openvoips.com  http://www.openvoips.org
// 
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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

class Route_mod extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function add($data) {
        $log_data_array = array();
        $data_array = array();
        $data_array['dialplan_id'] = strtoupper(generate_key($data['frm_name'], ''));
        if (isset($data['frm_name']))
            $data_array['dialplan_name'] = $data['frm_name'];
        if (isset($data['frm_desc']))
            $data_array['dialplan_description'] = $data['frm_desc'];
        if (isset($data['frm_failover']))
            $data_array['failover_sipcause_list'] = $data['frm_failover'];
        if (isset($data['frm_status']))
            $data_array['dialplan_status'] = $data['frm_status'];
        $data_array['create_dt'] = $data_array['update_dt'] = date('Y-m-d H:i:s');
        $str = $this->db->insert_string('dialplan', $data_array);
        $result = $this->db->query($str);
        if ($result) {
            $insert_id = $data_array['dialplan_id'];
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'dialplan', 'sql_key' => '', 'sql_query' => $str);
            set_activity_log($log_data_array);
            return array('status' => true, 'id' => $insert_id, 'msg' => 'Successfully added');
        } else {
            $error_array = $this->db->error();
            return array('status' => false, 'msg' => $error_array['message']);
        }
    }

    function update($data) {
        $log_data_array = array();
        $data_array = array();
        if (isset($data['frm_id']))
            $data_array['dialplan_id'] = $data['frm_id'];
        if (isset($data['frm_name']))
            $data_array['dialplan_name'] = $data['frm_name'];
        if (isset($data['frm_desc']))
            $data_array['dialplan_description'] = $data['frm_desc'];
        if (isset($data['frm_failover']))
            $data_array['failover_sipcause_list'] = $data['frm_failover'];
        if (isset($data['frm_status']))
            $data_array['dialplan_status'] = $data['frm_status'];
        if (isset($data['frm_key'])) {
            $this->db->trans_begin();
            if (count($data_array) > 0) {
                $where = "dialplan_id ='" . $data['frm_key'] . "'";
                $str = $this->db->update_string('dialplan', $data_array, $where);
                $result = $this->db->query($str);
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'dialplan', 'sql_key' => $where, 'sql_query' => $str);
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

    function delete($data) {
        try {
            $this->db->trans_begin();
            foreach ($data['delete_id'] as $id) {
                $log_data_array = array();
                $sql = "SELECT * FROM dialplan WHERE dialplan_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('dialplan', array('dialplan_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'dialplan', 'sql_key' => $id, 'sql_query' => $data_dump);

                    $result = $this->db->delete('dialplan_prefix_list', array('dialplan_id' => $id));

                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'dialplan_prefix_list', 'sql_key' => $id, 'sql_query' => '');
                }
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

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        // function get_data($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        try {
            $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
            $sub = $this->subquery->start_subquery('select');
            $sub->select('count(*)')->from('customer_dialplan');
            $sub->where('customer_dialplan.dialplan_id = dialplan.dialplan_id');
            $this->subquery->end_subquery('customer_count');
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'dialplan_id')
                            $this->db->where($key, $value);
                        else
                            $this->db->like($key, $value);
                    }
                }
            }
            
            if ($order_by != '') {
                $this->db->order_by($order_by, 'ASC');
            } else {
                $this->db->order_by('id', 'DESC');
            }
            $this->db->limit(intval($limit_from), intval($limit_to));
            $q = $this->db->get('dialplan');

            $final_return_array['result'] = $q->result_array();
//           echo $this->db->last_query();
            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
            $final_return_array["total"] = $query->row()->Count;
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Dialplan fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

}
