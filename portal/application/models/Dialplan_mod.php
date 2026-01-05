<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Dialplan_mod extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function add($data) {
        $log_data_array = array();
        $data_array = array();
        if (isset($data['frm_prefix']))
            $data_array['dial_prefix'] = $data['frm_prefix'];
        if (isset($data['frm_route']))
            $data_array['dialplan_id'] = $data['frm_route'];
        if (isset($data['frm_carrier']))
            $data_array['carrier_id'] = $data['frm_carrier'];
        if (isset($data['frm_priority']))
            $data_array['priority'] = $data['frm_priority'];
        if (isset($data['frm_start_day']))
            $data_array['start_day'] = $data['frm_start_day'];
        if (isset($data['frm_start_time']))
            $data_array['start_time'] = $data['frm_start_time'];
        if (isset($data['frm_end_day']))
            $data_array['end_day'] = $data['frm_end_day'];
        if (isset($data['frm_end_time']))
            $data_array['end_time'] = $data['frm_end_time'];
        if (isset($data['frm_load']))
            $data_array['load_share'] = $data['frm_load'];
        if (isset($data['frm_status']))
            $data_array['route_status'] = $data['frm_status'];
        $data_array['update_dt'] = $data_array['create_dt'] = date('Y-m-d H:i:s');

        $table = 'dialplan_prefix_list';
        $data = $data_array;
        if (empty($table) || empty($data))
            return false;
        $duplicate_data = array();
        foreach ($data AS $key => $value) {
            $duplicate_data[] = sprintf("%s='%s'", $key, $value);
        }

        $sql = sprintf("%s ON DUPLICATE KEY UPDATE %s", $this->db->insert_string($table, $data), implode(',', $duplicate_data));
        $result = $this->db->query($sql);
        //return $this->db->insert_id();
        //  $str = $this->db->insert_string('dialplan_prefix_list', $data_array);
        //  $result = $this->db->query($str);
        if ($result) {
            $insert_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'dialplan_prefix_list', 'sql_key' => '', 'sql_query' => $sql);
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
            $data_array['id'] = $data['frm_id'];
        if (isset($data['frm_carrier']))
            $data_array['carrier_id'] = $data['frm_carrier'];
        if (isset($data['frm_priority']))
            $data_array['priority'] = $data['frm_priority'];
        if (isset($data['frm_start_day']))
            $data_array['start_day'] = $data['frm_start_day'];
        if (isset($data['frm_start_time']))
            $data_array['start_time'] = $data['frm_start_time'];
        if (isset($data['frm_end_day']))
            $data_array['end_day'] = $data['frm_end_day'];
        if (isset($data['frm_end_time']))
            $data_array['end_time'] = $data['frm_end_time'];
        if (isset($data['frm_load']))
            $data_array['load_share'] = $data['frm_load'];
        if (isset($data['frm_status']))
            $data_array['route_status'] = $data['frm_status'];
        $data_array['update_dt'] = date('Y-m-d H:i:s');
        if (isset($data['frm_key'])) {
            $this->db->trans_begin();
            if (count($data_array) > 0) {
                $where = "id='" . $data['frm_id'] . "'";
                $str = $this->db->update_string('dialplan_prefix_list', $data_array, $where);
                $result = $this->db->query($str);
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'dialplan_prefix_list', 'sql_key' => $where, 'sql_query' => $str);
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
                $str = $this->db->where('id', param_decrypt($id))->get_compiled_delete('dialplan_prefix_list');
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'dialplan_prefix_list', 'sql_key' => param_decrypt($id), 'sql_query' => $str);
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

    function get_data($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        try {
            $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'dialplan_id' || ($key == 'dial_prefix' && strpos($value, '%') === false))
                            $this->db->where($key, $value);
                        else
                            $this->db->like($key, rtrim($value, '%'), 'after');
                    }
                }
            }
            $this->db->order_by('dial_prefix', 'ASC');
            $this->db->order_by('priority', 'ASC');
            $this->db->limit(intval($limit_from), intval($limit_to));

            $this->db->from('dialplan_prefix_list');
          
             
            $q = $this->db->get();

            $final_return_array['result'] = $q->result_array();
            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
            $final_return_array["total"] = $query->row()->Count;
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Dialplan List fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

}
