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
//OV500 Version 1.0.3
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

class Provider_mod extends CI_Model {

    public $did_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS	s.*, c.name currency_name, c.symbol currency_symbol FROM providers s LEFT JOIN sys_currencies c ON s.currency_id=c.currency_id where 1 ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'provider_id')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'currency_id')
                            $sql .= " AND s.$key ='" . $value . "' ";
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY s.id desc ";
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
                $provider_id = $row['provider_id'];
                $final_return_array['result'][$provider_id] = $row;
                $provider_id_array[] = $provider_id;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Providers fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function add($data) {
        try {
            $log_data_array = array();
            $provider_data_array = array();
            $provider_data_array['provider_name'] = $data['provider_name'];
            $provider_data_array['currency_id'] = $data['currency_id'];
            $provider_data_array['provider_address'] = $data['provider_address'];
            $provider_data_array['provider_emailid'] = $data['provider_emailid'];
            $provider_data_array['created_by'] = $data['created_by'];
            $provider_data_array['create_date'] = date('Y-m-d H:i:s');
            $sql = "SELECT provider_name FROM providers WHERE provider_name='" . $provider_data_array['provider_name'] . "'";
            $query = $this->db->query($sql);
            $row = $query->row_array();
            if (isset($row)) {
                return 'Provider Name Already Exists';
            }
            while (1) {
                $key = generate_key($provider_data_array['provider_name'], $provider_data_array['currency_id']);
                $sql_check = "SELECT provider_id FROM providers WHERE provider_id ='" . $key . "'";
                $query_check = $this->db->query($sql_check);
                $num_rows = $query_check->num_rows();
                if ($num_rows > 0) {
                    
                } else {
                    $provider_data_array['provider_id'] = $key;
                    break;
                }
            }
            $this->db->trans_begin();
            if (count($provider_data_array) > 0) {
                $str = $this->db->insert_string('providers', $provider_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->provider_id = $key;
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'providers', 'sql_key' => $this->provider_id, 'sql_query' => $str);
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
            $log_data_array = array();
            $provider_data_array = array();
            if (isset($data['provider_id'])) {
                $provider_id = $data['provider_id'];
            } else {
                return ' Provider ID missing in the system';
            }
            if (isset($data['provider_name']))
                $provider_data_array['provider_name'] = $data['provider_name'];
            if (isset($data['currency_id']))
                $provider_data_array['currency_id'] = $data['currency_id'];
            if (isset($data['provider_address']))
                $provider_data_array['provider_address'] = $data['provider_address'];
            if (isset($data['provider_emailid']))
                $provider_data_array['provider_emailid'] = $data['provider_emailid'];
            if (isset($data['status_id']))
                $provider_data_array['status_id'] = $data['status_id'];
            if (isset($data['modify_by']))
                $provider_data_array['modify_by'] = $data['modify_by'];
            if (isset($provider_data_array['provider_name'])) {
                $sql = "SELECT provider_name FROM providers WHERE provider_name='" . $provider_data_array['provider_name'] . "' AND provider_id !='" . $data['provider_id'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    return 'Provider Name Already Exists in the system';
                }
            }


            $this->db->trans_begin();
            if (count($provider_data_array) > 0) {
                $where = "provider_id='" . $data['provider_id'] . "'";
                $str = $this->db->update_string('providers', $provider_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'providers', 'sql_key' => $where, 'sql_query' => $str);
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

    function delete($id_array) {
        print_r($id_array);
       
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();

                $sql = "SELECT group_concat(carrier_name,'') AS carrier_names	FROM carrier WHERE provider_id ='" . $id . "'";
             
                $query = $this->db->query($sql);
                $assigned_row = $query->row();
                if (isset($assigned_row) && $assigned_row->carrier_names != '') {
                    $carrier_names = $assigned_row->carrier_names;
                    throw new Exception('Provider assigned to these carriers(' . $carrier_names . ')');
                }

                $sql = "SELECT * FROM providers WHERE provider_id ='" . $id . "' ";
                  
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('providers', array('provider_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'providers', 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'providers', 'sql_key' => $id, 'sql_query' => '');
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

    function get_data_total_count() {
        return $this->total_count;
    }

}
