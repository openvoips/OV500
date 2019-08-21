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

class Role_mod extends CI_Model {

    public $account_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_role_permission($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT id,account_type,permissions FROM customer_default_permissions utp WHERE 1";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'account_type')
                            $sql .= " AND $key ='" . $value . "' ";
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }
            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY `account_type` ASC ";
            }
            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = array();
            foreach ($query->result_array() as $row) {
                $row['permissions'] = unserialize($row['permissions']);
                $id = $row['id'];
                $account_type = $row['account_type'];
                $final_return_array['result'][$account_type] = $row;
            }
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Roles fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_account_permission($account_id) {
        $final_return_array = array();
        try {
            $sql = "SELECT id,account_id,permissions FROM customer_permissions WHERE account_id='" . $account_id . "'";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = array();
            foreach ($query->result_array() as $row) {
                $row['permissions'] = unserialize($row['permissions']);
                $id = $row['id'];
                $account_id = $row['account_id'];
                $final_return_array['result'] = $row;
            }
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Permissions fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function role_permission_update($data) {
        $log_data_array = array();
        if (isset($data['account_type']))
            $account_type = strtoupper($data['account_type']);
        else
            return 'type missing';
        $account_default_permissions_array = array();
        $permission_array = get_permission_options();
        foreach ($permission_array as $item_name => $permission_array_single) {
            if (isset($data[$item_name])) {
                $account_default_permissions_array[$item_name] = $data[$item_name];
            }
        }
        $account_default_permissions = $account_type_permissions['permissions'] = serialize($account_default_permissions_array);
        $this->db->trans_begin();
        if (count($account_type_permissions) > 0) {
            $sql = "SELECT account_type FROM customer_default_permissions  WHERE  account_type ='" . $account_type . "' LIMIT 0,1";
            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows > 0) {
                $where = "account_type='" . $account_type . "'";
                $str = $this->db->update_string('customer_default_permissions', $account_type_permissions, $where);
                $result = $this->db->query($str);
                $this->db->last_query();
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_default_permissions', 'sql_key' => $where, 'sql_query' => $str);
            } else {
                $account_type_permissions['account_type'] = $account_type;
                $str = $this->db->insert_string('customer_default_permissions', $account_type_permissions);
                $result = $this->db->query($str);
                $this->id = $this->db->insert_id();
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_default_permissions', 'sql_key' => $this->id, 'sql_query' => $str);
            }
        }
        if ($this->db->trans_status() === FALSE) {
            $error_array = $this->db->error();
            $this->db->trans_rollback();
            return $error_array['message'];
        } else {
            $this->db->trans_commit();
            set_activity_log($log_data_array);
        }
        return true;
    }

    function account_permission_update($data) {
        $log_data_array = array();
        if (isset($data['account_id']))
            $account_id = strtoupper($data['account_id']);
        else
            return 'User missing';
        $account_permissions_array = array();
        $permission_array = get_permission_options();
        foreach ($permission_array as $item_name => $permission_array_single) {
            if (isset($data[$item_name])) {
                $account_permissions_array[$item_name] = $data[$item_name];
            }
        }
        $account_permissions['permissions'] = serialize($account_permissions_array);
        $this->db->trans_begin();
        if (count($account_permissions) > 0) {
            $sql = "SELECT	account_id FROM customer_permissions WHERE  account_id ='" . $account_id . "' LIMIT 0,1";
            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows > 0) {
                $where = "account_id='" . $account_id . "'";
                $str = $this->db->update_string('customer_permissions', $account_permissions, $where);
                $result = $this->db->query($str);

                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_permissions', 'sql_key' => $where, 'sql_query' => $str);
            } else {
                $account_permissions['account_id'] = $account_id;
                $str = $this->db->insert_string('customer_permissions', $account_permissions);
                $result = $this->db->query($str);
                $this->id = $this->db->insert_id();
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_permissions', 'sql_key' => $this->id, 'sql_query' => $str);
            }
        }
        if ($this->db->trans_status() === FALSE) {
            $error_array = $this->db->error();
            $this->db->trans_rollback();
            return $error_array['message'];
        } else {
            $this->db->trans_commit();
            set_activity_log($log_data_array);
        }
        return true;
    }

}
