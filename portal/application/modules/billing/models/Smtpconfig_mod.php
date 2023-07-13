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

class Smtpconfig_mod extends CI_Model {

    public $smtp_config_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function add($data) {
        try {           
            $insert_data['account_id'] = get_logged_account_id();
            
            $insert_data['smtp_auth'] = $data['smtp_auth'];
            $insert_data['smtp_secure'] = $data['smtp_secure'];
            $insert_data['smtp_host'] = $data['smtp_host'];
            $insert_data['smtp_port'] = $data['smtp_port'];
            $insert_data['smtp_username'] = $data['smtp_username'];
            $insert_data['smtp_password'] = $data['smtp_password'];
            $insert_data['smtp_from'] = $data['smtp_from'];
            $insert_data['smtp_from_name'] = $data['smtp_from_name'];
            $insert_data['smtp_xmailer'] = $data['smtp_xmailer'];
            $insert_data['smtp_host_name'] = $data['smtp_host_name'];
            $insert_data['created_by'] = $data['created_by'];
            $insert_data['smtp_config_id'] = generate_key($insert_data['smtp_username'], $insert_data['account_id']);
            $this->db->trans_begin();
            if (count($insert_data) > 0) {
                $str = $this->db->insert_string('bill_smtp_config', $insert_data);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->smtp_config_id = $insert_data['smtp_config_id'];
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'bill_smtp_config', 'sql_key' => $this->provider_id, 'sql_query' => $str);
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
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function update($data) {

        try {
            if (isset($data['account_id']))
                $insert_data['account_id'] = $data['account_id'];
            if (isset($data['smtp_auth']))
                $insert_data['smtp_auth'] = $data['smtp_auth'];
            if (isset($data['smtp_secure']))
                $insert_data['smtp_secure'] = $data['smtp_secure'];
            if (isset($data['smtp_host']))
                $insert_data['smtp_host'] = $data['smtp_host'];
            if (isset($data['smtp_port']))
                $insert_data['smtp_port'] = $data['smtp_port'];
            if (isset($data['smtp_username']))
                $insert_data['smtp_username'] = $data['smtp_username'];

            if (isset($data['smtp_password']))
                $insert_data['smtp_password'] = $data['smtp_password'];
            if (isset($data['smtp_from']))
                $insert_data['smtp_from'] = $data['smtp_from'];
            if (isset($data['smtp_from_name']))
                $insert_data['smtp_from_name'] = $data['smtp_from_name'];
            if (isset($data['smtp_xmailer']))
                $insert_data['smtp_xmailer'] = $data['smtp_xmailer'];
            if (isset($data['smtp_host_name']))
                $insert_data['smtp_host_name'] = $data['smtp_host_name'];
            if (isset($data['smtp_config_id']))
                $insert_data['smtp_config_id'] = $data['smtp_config_id'];
            if (count($insert_data) > 0) {
                $where = "smtp_config_id='" . $data['smtp_config_id'] . "'";
                $str = $this->db->update_string('bill_smtp_config', $insert_data, $where);

                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'bill_smtp_config', 'sql_key' => $where, 'sql_query' => $str);
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
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function get_smtp_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS * from bill_smtp_config where 1 ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'smtp_host')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'smtp_from')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'smtp_port')
                            $sql .= " AND $key ='" . $value . "' ";
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }
           
            $sql .= " AND  account_id = '" . get_logged_account_id() . "'";
            
            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY id desc ";
            }

            $limit_from = intval($limit_from);

            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;

            foreach ($query->result_array() as $row) {
                $account_id = $row['smtp_config_id'];
                $final_return_array['result'][$account_id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'SMTP Config fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function delete($id_array) {
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();

                $result = $this->db->delete('bill_smtp_config', array('smtp_config_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'bill_smtp_config', 'sql_key' => $id, 'sql_query' => $data_dump);
            }

            $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'bill_smtp_config', 'sql_key' => $id, 'sql_query' => '');
            set_activity_log($log_data_array);


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

    function get_smtp_total_count() {
        return $this->total_count;
    }

    function get_smtp_data_by_id($id) {
        $sql = "select * from bill_smtp_config where smtp_config_id='$id'";
        $query = $this->db->query($sql);

        return $query->result_array();
    }

}
