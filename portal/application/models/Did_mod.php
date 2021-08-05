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


class Did_mod extends CI_Model {

    public $did_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = $carrier_id_array = $did_id_carrier_id_mapping_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM did WHERE 1 ";
            $carrier_ip_sql = '';
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if (in_array($key, array('logged_account_type', 'logged_current_customer_id', 'logged_account_level', 'assigned_to'))) {
                        
                    } elseif ($value != '') {
                        if ($key == 'did_id')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'prefix')
                            $sql .= " AND $key ='%" . $value . "' ";
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }
            if (isset($filter_data['logged_account_type']) && isset($filter_data['logged_current_customer_id']) && isset($filter_data['logged_account_level']) && $filter_data['logged_account_type'] == 'RESELLER' && in_array($filter_data['logged_account_level'], array(1, 2, 3))) {
                $level = $filter_data['logged_account_level'];
                $field_name = 'reseller' . $level . '_account_id';
                $sql .= " AND `" . $field_name . "` = '" . $filter_data['logged_current_customer_id'] . "'";
                if (isset($filter_data['assigned_to']) && $filter_data['assigned_to'] != '') {
                    if (in_array($filter_data['logged_account_level'], array(1, 2))) {
                        $field_name = 'reseller' . ($level + 1) . '_account_id';
                        $sql .= " AND `" . $field_name . "` = '" . $filter_data['assigned_to'] . "'";
                    } else {
                        $sql .= "  AND account_id='" . $filter_data['assigned_to'] . "'";
                    }
                }
            } elseif (isset($filter_data['logged_account_type']) && isset($filter_data['logged_current_customer_id']) && $filter_data['logged_account_type'] == 'CUSTOMER') {
                $sql .= "  AND account_id='" . $filter_data['logged_current_customer_id'] . "'";
            } elseif (isset($filter_data['logged_account_type']) && isset($filter_data['logged_current_customer_id']) && in_array($filter_data['logged_account_type'], array('ADMIN', 'SUBADMIN'))) {
                if (isset($filter_data['assigned_to']) && $filter_data['assigned_to'] != '') {
                    $sql .= "  AND ( account_id='" . $filter_data['assigned_to'] . "' || reseller1_account_id='" . $filter_data['assigned_to'] . "' || reseller2_account_id='" . $filter_data['assigned_to'] . "' || reseller3_account_id='" . $filter_data['assigned_to'] . "' )";
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY `did_status` ASC, `did_number` ";
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
                $did_id = $row['did_id'];
                $carrier_id = $row['carrier_id'];

                if (isset($option_param['carrier_rates']) && $option_param['carrier_rates'] == true) {
                    $row['carrier_rates'] = array();
                    $row['carrier'] = array();
                }
                if (isset($option_param['did_dst']) && $option_param['did_dst'] == true) {
                    $row['did_dst'] = array();
                }

                $final_return_array['result'][$did_id] = $row;
                $carrier_id_array[] = $carrier_id;
                $did_id_carrier_id_mapping_array[$carrier_id][] = $did_id;

                $did_id_prefix_mapping_array[$did_id] = $row['did_number'];
            }


            $carrier_id_array = array_unique($carrier_id_array);
            if (isset($option_param['carrier_rates']) && $option_param['carrier_rates'] == true && count($final_return_array['result']) > 0) {
                $carrier_id_str = implode("','", $carrier_id_array);
                $carrier_id_str = "'" . $carrier_id_str . "'";
                $sql = "
select carrier_id, carrier.tariff_id, ratecard_id, carrier_currency_id  from carrier
INNER JOIN tariff_ratecard_map on tariff_ratecard_map.tariff_id = carrier.tariff_id
 where carrier_id IN($carrier_id_str) and ratecard_for = 'INCOMING'";

                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $carrier_id = $row['carrier_id'];
                    $tariff_id = $row['tariff_id'];
                    $incoming_ratecard_id = $row['ratecard_id'];
                    $sql = "SELECT * FROM carrier_rates WHERE ratecard_id ='" . $incoming_ratecard_id . "'";
                    $query = $this->db->query($sql);
                    $row_sub = $query->result_array();
                    if (isset($did_id_carrier_id_mapping_array[$carrier_id])) {
                        foreach ($did_id_carrier_id_mapping_array[$carrier_id] as $did_id) {
                            $final_return_array['result'][$did_id]['carrier'] = $row;
                            $did_number = $did_id_prefix_mapping_array[$did_id];

                            if (count($row_sub) > 0) {
                                foreach ($row_sub as $row_sub_single) {
                                    $prefix = $row_sub_single['prefix'];
                                    if ($did_number == $prefix)
                                        $final_return_array['result'][$did_id]['carrier_rates'] = $row_sub_single;
                                }
                            }
                        }
                    }
                }
            }

            if (isset($option_param['did_dst']) && $option_param['did_dst'] == true && count($final_return_array['result']) > 0) {
                $prefix_did_id_mapping_array = array_flip($did_id_prefix_mapping_array);

                $prefix_str = implode("','", $did_id_prefix_mapping_array);
                $prefix_str = "'" . $prefix_str . "'";
                $sql = "SELECT * FROM did_dst WHERE did_number IN($prefix_str) ";
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $did_number = $row['did_number'];
                    if (isset($prefix_did_id_mapping_array[$did_number])) {
                        $did_id = $prefix_did_id_mapping_array[$did_number];
                        $final_return_array['result'][$did_id]['did_dst'] = $row;
                    }
                }
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Incoming Numbers fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function add_bulk($carrier_id, $csv_data) {
        try {
            $did_data_array = $log_data_array = array();

            $sql = "SELECT  tariff_id FROM carrier  WHERE  carrier_id ='" . $carrier_id . "'";
            $query = $this->db->query($sql);
            $row = $query->row_array();
            if (!isset($row)) {
                return 'Carrier Not Found';
            }
            $tariff_id = $row['tariff_id'];

            $sql = "select tariff_ratecard_map.ratecard_id incoming_ratecard_id from tariff_ratecard_map INNER JOIN ratecard on ratecard.ratecard_id = tariff_ratecard_map.ratecard_id  where tariff_id = '" . $tariff_id . "' and  tariff_ratecard_map.ratecard_for = 'INCOMING' and ratecard.ratecard_for ='INCOMING' and ratecard_type = 'CARRIER' and tariff_ratecard_map.status='1' limit 1;";
            $query = $this->db->query($sql);
            $row = $query->row_array();
            if (!isset($row)) {
                return 'Carrier Tariff Not Found';
            }
            $incoming_ratecard_id = $row['incoming_ratecard_id'];
            $error_message_array = array();
			$rate_values='';
            for ($i = 1; $i < count($csv_data); $i++) {
                $data = $csv_data[$i];
                $lineno = $i + 1;
                $did = $val = trim($data[0]);
                if (!preg_match('/^[0-9]+$/', $val)) {
                    $error_message_array[] = 'Error: line [' . $lineno . '] - column [DID]' . ' - value[' . $val . ']';
                }
                $did_name = $val = trim($data[1]);
                if (!preg_match('/^[\w -\/]+$/', $val)) {
                    $error_message_array[] = 'Error: line [' . $lineno . '] - column [DID Name]' . ' - value[' . $val . ']';
                }
                $setup_charge = $val = trim($data[2]);
                if (!is_numeric($val)) {
                    $error_message_array[] = 'Error: line [' . $lineno . '] - column [Setup Charge]' . ' - value[' . $val . ']';
                }
                $rental = $val = trim($data[3]);
                if (!is_numeric($val)) {
                    $error_message_array[] = 'Error: line [' . $lineno . '] - column [Rental]' . ' - value[' . $val . ']';
                }
                $rate = $val = trim($data[4]);
                if (!is_numeric($val)) {
                    $error_message_array[] = 'Error: line [' . $lineno . '] - column [Rate]' . ' - value[' . $val . ']';
                }
                $connection_charge = $val = trim($data[5]);
                if (!is_numeric($val)) {
                    $error_message_array[] = 'Error: line [' . $lineno . '] - column [Connection Charge]' . ' - value[' . $val . ']';
                }
                $minimal_time = $val = trim($data[6]);
                if (!preg_match('/^[0-9]+$/', $val)) {
                    $error_message_array[] = 'Error: line [' . $lineno . '] - column [Minimum Time]' . ' - value[' . $val . ']';
                }
                $resolution_time = $val = trim($data[7]);
                if (!preg_match('/^[0-9]+$/', $val)) {
                    $error_message_array[] = 'Error: line [' . $lineno . '] - column [Resolution Time]' . ' - value[' . $val . ']';
                }
                $channels = $val = trim($data[8]);
                if (!preg_match('/^[0-9]+$/', $val)) {
                    $error_message_array[] = 'Error: line [' . $lineno . '] - column [Channels]' . ' - value[' . $val . ']';
                }

                if (strlen($did_name) == 0)
                    $did_name = $did;
                $did_data_array_temp = array();
                $did_data_array_temp['carrier_id'] = $carrier_id;
                $did_data_array_temp['did_number'] = $did;
                $did_data_array_temp['channels'] = $channels;
                $did_data_array_temp['did_name'] = $did_name;
                $did_data_array_temp['create_date'] = date('Y-m-d H:i:s');
                $did_data_array_temp['did_status'] = 'NEW';

                $rate_data_array_temp = array();
                $rate_data_array_temp['ratecard_id'] = $incoming_ratecard_id;
                $rate_data_array_temp['prefix'] = $did;
                $rate_data_array_temp['destination'] = $did_name;
                $rate_data_array_temp['rate'] = $rate;
                $rate_data_array_temp['connection_charge'] = $connection_charge;
                $rate_data_array_temp['setup_charge'] = $setup_charge;
                $rate_data_array_temp['rental'] = $rental;
                $rate_data_array_temp['minimal_time'] = $minimal_time;
                $rate_data_array_temp['resolution_time'] = $resolution_time;
                $rate_data_array_temp['grace_period'] = 0;
                $rate_data_array_temp['rate_multiplier'] = 1;
                $rate_data_array_temp['rate_addition'] = 0;
                $rate_data_array_temp['rates_status'] = 1;
                $rate_data_array_temp['create_dt'] = date('Y-m-d H:i:s');
                $did_array[] = "'" . $did . "'";
                $did_data_array[] = $did_data_array_temp;
                $rate_data_array[] = $rate_data_array_temp;


                $rate_data_array_temp_str = "'" . implode("','", $rate_data_array_temp) . "'";
                if ($rate_values != '')
                    $rate_values .= ",";
                $rate_values .= '(' . $rate_data_array_temp_str . ')';
            }
            if (count($error_message_array) > 0) {
                $msg = implode('<br>', $error_message_array);
                throw new Exception($msg);
            }
            $did_str = implode(', ', $did_array);
            $sql = "SELECT did_number FROM did WHERE did_number IN(" . $did_str . ")";
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $did_number = $row['did_number'];
                $error_message_array[] = 'Error: DID [' . $did_number . '] already exists in system';
            }

            if (count($error_message_array) > 0) {
                $msg = implode('<br>', $error_message_array);
                throw new Exception($msg);
            }

            $this->db->trans_begin();
            if (count($did_data_array) > 0) {
                $result = $this->db->insert_batch('did', $did_data_array);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }


                $rate_fields_array = array_keys($rate_data_array_temp);
                $rate_fields = implode(", ", $rate_fields_array);
                $sql = "INSERT INTO carrier_rates ($rate_fields) VALUES $rate_values
  ON DUPLICATE KEY UPDATE 
 
  destination=VALUES(destination), 
  rate=VALUES(rate), connection_charge=VALUES(connection_charge), setup_charge=VALUES(setup_charge), rental=VALUES(rental), minimal_time=VALUES(minimal_time), resolution_time=VALUES(resolution_time)  ";

                $result = $this->db->query($sql);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();    
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
            set_activity_log($log_data_array);
        }
    }

    function add($data) {
        try {
            $log_data_array = array();
            $did_data_array = $rate_data_array = array();
            $did_data_array['carrier_id'] = $data['carrier_id'];
            $did_data_array['did_number'] = $data['did_number'];
            $did_data_array['did_name'] = $data['destination'];
            if (isset($data['did_status']))
                $did_data_array['did_status'] = $data['did_status'];
            if (isset($data['channels']))
                $did_data_array['channels'] = $data['channels'];
            $did_data_array['create_date'] = date('Y-m-d H:i:s');
            $sql = "SELECT ratecard_id FROM tariff_ratecard_map inner join carrier on tariff_ratecard_map.tariff_id = carrier.tariff_id WHERE  ratecard_for = 'INCOMING' and  carrier.carrier_id = '" . $data['carrier_id'] . "'";
            $query = $this->db->query($sql);
            $row_carrier = $query->row_array();
            if (!isset($row_carrier)) {
                return 'Ratecard is not linked in carrier tariff';
            }

            $incoming_ratecard_id = '';
            foreach ($row_carrier as $key => $value) {
                $incoming_ratecard_id = "'" . $value . "',";
                $incoming_ratecard_id1 = $value;
            }
            $incoming_ratecard_id = rtrim($incoming_ratecard_id, ',');

            $sql = "SELECT did_number FROM did WHERE did_number ='" . $did_data_array['did_number'] . "'";
            $query = $this->db->query($sql);
            $row = $query->row_array();
            if (isset($row)) {
                return 'DID number is already available in the system. Please add the new DID which is not listed in the system';
            }

            $rate_data_array['ratecard_id'] = $incoming_ratecard_id1;
            $rate_data_array['destination'] = $data['destination'];
            $rate_data_array['rate'] = $data['rate'];
            $rate_data_array['connection_charge'] = $data['connection_charge'];
            $rate_data_array['setup_charge'] = $data['setup_charge'];
            $rate_data_array['rental'] = $data['rental'];
            $rate_data_array['minimal_time'] = $data['minimal_time'];
            $rate_data_array['resolution_time'] = $data['resolution_time'];
            $rate_data_array['grace_period'] = 0;
            $rate_data_array['rate_multiplier'] = 1;
            $rate_data_array['rate_addition'] = 0;
            $rate_data_array['rates_status'] = '1';
            $rate_data_array['prefix'] = $did_data_array['did_number'];
            $rate_data_array['create_dt'] = date('Y-m-d H:i:s');

            $this->db->trans_begin();
            if (count($did_data_array) > 0) {
                $str = $this->db->insert_string('did', $did_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->did_id = $this->db->insert_id();
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'did', 'sql_key' => $this->did_id, 'sql_query' => $str);
            }

            $sql = "SELECT prefix, rate_id, ratecard_id FROM carrier_rates WHERE prefix ='" . $rate_data_array['prefix'] . "' AND ratecard_id in (" . $incoming_ratecard_id . ")";


            $query = $this->db->query($sql);
            $row = $query->row_array();
            if (isset($row)) {
                unset($rate_data_array['prefix']);
                unset($rate_data_array['create_dt']);
                $rate_id = $row['rate_id'];
                $rate_data_array['ratecard_id'] = $row['ratecard_id'];
                $where = "rate_id='" . $rate_id . "'";
                $str = $this->db->update_string('carrier_rates', $rate_data_array, $where);
                $result = $this->db->query($str);

                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'carrier_rates', 'sql_key' => $rate_id, 'sql_query' => $str);
            } else {
                if (count($rate_data_array) > 0) {
                    $str = $this->db->insert_string('carrier_rates', $rate_data_array);
                    $result = $this->db->query($str);

                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $this->rate_id = $this->db->insert_id();
                    $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'carrier_rates', 'sql_key' => $this->rate_id, 'sql_query' => $str);
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
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function update($data) {
        try {
            $api_log_data_array = array();
            $log_data_array = array();
            $did_data_array = $rate_data_array = array();
            if (isset($data['did_id'])) {
                $did_id = $data['did_id'];
            } else {
                return 'ID missing';
            }
            if (isset($data['rate_id']))
                $rate_id = $data['rate_id'];
            if (isset($data['did_id']))
                $did_data_array['did_id'] = $data['did_id'];
            if (isset($data['did_number']))
                $did_data_array['did_number'] = $data['did_number'];
            if (isset($data['did_status']))
                $did_data_array['did_status'] = $data['did_status'];
            if (isset($data['channels']))
                $did_data_array['channels'] = $data['channels'];
            if (isset($data['destination']))
                $did_data_array['did_name'] = $data['destination'];
            if (isset($data['rate_id']))
                $rate_data_array['rate_id'] = $data['rate_id'];
            if (isset($data['destination']))
                $rate_data_array['destination'] = $data['destination'];
            if (isset($data['setup_charge']))
                $rate_data_array['setup_charge'] = $data['setup_charge'];
            if (isset($data['rental']))
                $rate_data_array['rental'] = $data['rental'];
            if (isset($data['rate']))
                $rate_data_array['rate'] = $data['rate'];
            if (isset($data['connection_charge']))
                $rate_data_array['connection_charge'] = $data['connection_charge'];
            if (isset($data['minimal_time']))
                $rate_data_array['minimal_time'] = $data['minimal_time'];
            if (isset($data['resolution_time']))
                $rate_data_array['resolution_time'] = $data['resolution_time'];
            if (isset($did_data_array['did_number'])) {
                $sql = "SELECT did_number FROM did WHERE did_number ='" . $did_data_array['did_number'] . "' AND did_id !='" . $data['did_id'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    return 'DID Already Exists';
                }
            }
            $sql = "SELECT ratecard_id FROM tariff_ratecard_map inner join carrier on tariff_ratecard_map.tariff_id = carrier.tariff_id WHERE  ratecard_for = 'INCOMING' and  carrier.carrier_id = '" . $data['carrier_id'] . "'";
            $query = $this->db->query($sql);
            $row_carrier = $query->row_array();
            if (!isset($row_carrier)) {
                return 'Carrier Not Found';
            }

            $incoming_ratecard_id = '';
            foreach ($row_carrier as $key => $value) {
                $incoming_ratecard_id = "'" . $value . "',";
                $incoming_ratecard_id1 = $value;
            }
            $incoming_ratecard_id = rtrim($incoming_ratecard_id, ',');

            //if rate update then check
            if (isset($rate_data_array['rate_id'])) {
                $sql = "SELECT  rate_id FROM  carrier_rates WHERE  ratecard_id ='" . $incoming_ratecard_id1 . "' AND rate_id ='" . $rate_data_array['rate_id'] . "' LIMIT 1";
                $query = $this->db->query($sql);
                $row_old_rate = $query->row_array();
                if (!isset($row_old_rate)) {
                    return 'Carrier Rate Not found For This Prefix';
                }
            }
            $sql = "Select * from carrier_rates where ratecard_id in (SELECT ratecard_id FROM tariff_ratecard_map inner join carrier on tariff_ratecard_map.tariff_id = carrier.tariff_id WHERE  ratecard_for = 'INCOMING' and  carrier.carrier_id = '" . $data['carrier_id'] . "') and carrier_rates.prefix like '%" . $did_data_array['did_number'] . "%' limit 1;";

            $query = $this->db->query($sql);
            $row = $query->row_array();
            if (!isset($row)) {
                return 'Carrier Rate Not found For This ' . $did_data_array['did_number'] . ' DID';
            }

            $this->db->trans_begin();
            if (count($did_data_array) > 0) {
                $where = "did_id='" . $did_id . "'";
                $str = $this->db->update_string('did', $did_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'did', 'sql_key' => $where, 'sql_query' => $str);
            }
            if (isset($rate_data_array['rate_id'])) {
                if (count($rate_data_array) > 0) {
                    $where = "rate_id='" . $rate_data_array['rate_id'] . "'";
                    $str = $this->db->update_string('carrier_rates', $rate_data_array, $where);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'carrier_rates', 'sql_key' => $where, 'sql_query' => $str);
                }
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {

                if (isset($data['channels']) && $existing_did_row['did_status'] == 'USED') {
                    $existing_reseller1_account_id = $existing_did_row['reseller1_account_id'];
                    $existing_reseller2_account_id = $existing_did_row['reseller2_account_id'];
                    $existing_reseller3_account_id = $existing_did_row['reseller3_account_id'];
                    $existing_account_id = $existing_did_row['account_id'];
                    $extra_channel = $data['channels'] - $existing_did_row['channels'];
                    if ($extra_channel > 0) {
                        $api_request['REQUEST'] = 'DIDEXTRACHRENTAL';
                        $api_request['service_number'] = $existing_did_row['did_number'];
                        $api_request['channels'] = $extra_channel;
                        if ($existing_account_id != '') {
                            $api_request['account_id'] = $existing_account_id;
                            $api_request['account_type'] = 'CUSTOMER';
                            $api_request['account_level'] = '';
                            $api_response = call_billing_api($api_request);                         
                            $api_result = json_decode($api_response, true);
                            $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['REQUEST'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));
                            if (!isset($api_result['error']) || $api_result['error'] == '1') {
                                $this->db->trans_rollback();
                                throw new Exception('SDR Problem:(' . $api_request['account_id'] . ')' . $api_result['message']);
                            }
                        }

                        if ($existing_reseller1_account_id != '') {
                            $api_request['account_id'] = $existing_reseller1_account_id;
                            $api_request['account_type'] = 'RESELLER';
                            $api_request['account_level'] = '1';                          
                            $api_response = call_billing_api($api_request);
                            $api_result = json_decode($api_response, true);
                            $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));
                            if (!isset($api_result['error']) || $api_result['error'] == '1') {
                                $this->db->trans_rollback();
                                throw new Exception('SDR Problem:(' . $api_request['account_id'] . ')' . $api_result['message']);
                            }
                        }

                        if ($existing_reseller2_account_id != '') {
                            $api_request['account_id'] = $existing_reseller2_account_id;
                            $api_request['account_type'] = 'RESELLER';
                            $api_request['account_level'] = '2';
                            $api_response = call_billing_api($api_request);
                            $api_result = json_decode($api_response, true);
                            $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));
                            if (!isset($api_result['error']) || $api_result['error'] == '1') {
                                $this->db->trans_rollback();
                                throw new Exception('SDR Problem:(' . $api_request['account_id'] . ')' . $api_result['message']);
                            }
                        }

                        if ($existing_reseller3_account_id != '') {
                            $api_request['account_id'] = $existing_reseller3_account_id;
                            $api_request['account_type'] = 'RESELLER';
                            $api_request['account_level'] = '3';                           
                            $api_response = call_billing_api($api_request);
                            $api_result = json_decode($api_response, true);
                            $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));
                            if (!isset($api_result['error']) || $api_result['error'] == '1') {
                                $this->db->trans_rollback();
                                throw new Exception('SDR Problem:(' . $api_request['account_id'] . ')' . $api_result['message']);
                            }
                        }
                    }
                }

                $this->db->trans_commit();
                set_activity_log($log_data_array);
                set_activity_log($api_log_data_array);
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            set_activity_log($api_log_data_array);
            return $e->getMessage();
        }
    }

    function delete($account_id, $id_array) {
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();
                $sql = "SELECT * FROM did WHERE did_id='" . $id . "' AND did_status IN('NEW','DEAD')";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('did', array('did_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'did', 'sql_key' => $id, 'sql_query' => $data_dump);
                    $result = $this->db->delete('did_dst', array('did_number' => $row['did_number']));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    //$account_id='NEEDTOSET';//you need to pass account_id
                    $this->clean_did_related_data($row['did_number'], $account_id);
                }

                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'DID', 'sql_key' => $id, 'sql_query' => '');
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

    /* get matched rates for DID */

    function getDIDRates($did) {
        $account_type = get_logged_account_type();
        $account_id = get_logged_account_id();
        $reseller_level = get_logged_account_level();

        $sql = "SELECT ratecard_id FROM tariff_ratecard_map inner join customer_voipminuts on tariff_ratecard_map.tariff_id = customer_voipminuts.tariff_id WHERE  ratecard_for = 'INCOMING' and  customer_voipminuts.account_id = '" . $account_id . "'";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        if (!isset($row)) {
            return 'Customer Not Found';
        }


        $incoming_ratecard_id = '';
        foreach ($row as $key => $value) {
            $incoming_ratecard_id = "'" . $value . "',";
            $incoming_ratecard_id1 = $value;
        }
        $incoming_ratecard_id = rtrim($incoming_ratecard_id, ',');



        $sql = "SELECT did_status FROM did where did_number = '" . $did . "' and  account_id = '" . $account_id . "'";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        if (!isset($row)) {
            return 'Customer Not Found';
        }


        $did_status = '';
        foreach ($row as $key => $value) {
            $did_status = $value;
        }



        $query = $this->db->query($sql);
        $rs = $query->row();
        if (isset($rs)) {
            $sql = "SELECT * FROM customer_rates WHERE ratecard_id in (" . $incoming_ratecard_id . ") and ('" . $did . "' like concat(prefix,'%') or prefix like '" . $did . "%')";

            $query = $this->db->query($sql);
            $rs_rates = $query->row();
            if (isset($rs_rates)) {
                $option_param = array();
                $user_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
                $parent_account_id = $user_result['parent_account_id'];


                $didlist = array('did' => $did, 'prefix' => $rs_rates->prefix, 'setup' => number_format($rs_rates->setup_charge, 4, '.', ''), 'rental' => number_format($rs_rates->rental, 4, '.', ''), 'ppm' => number_format($rs_rates->rate, 4, '.', ''), 'ppc' => number_format($rs_rates->connection_charge, 4, '.', ''), 'min' => $rs_rates->minimal_time, 'res' => $rs_rates->resolution_time, 'grace' => $rs_rates->grace_period, 'add' => number_format($rs_rates->rate_addition, 4, '.', ''), 'mul' => number_format($rs_rates->rate_multiplier, 4, '.', ''), 'did_status' => $did_status);
                $return = array('status' => true, 'msg' => 'DID Rates Found', 'dids' => $didlist);
            } else {
                $return = array('status' => false, 'msg' => 'No rate available');
            }
        }
        //echo '<pre>';print_r($return);echo '</pre>';
        return $return;
    }

    function getAvailableDID($did, $area_specific = false) {
        $account_type = get_logged_account_type();
        $account_id = get_logged_account_id();
         $sql = "SELECT ratecard_id FROM tariff_ratecard_map inner join customer_voipminuts on tariff_ratecard_map.tariff_id = customer_voipminuts.tariff_id WHERE  ratecard_for = 'INCOMING' and  customer_voipminuts.account_id = '" . $account_id . "'";

        $query = $this->db->query($sql);
        $rs = $query->row();
        $incoming_ratecard_id = '';
        foreach ($rs as $key => $value) {
            $incoming_ratecard_id = "'" . $value . "',";
        }
        $incoming_ratecard_id = rtrim($incoming_ratecard_id, ',');

        if ($area_specific)
            $did_table = 'new_did';
        else
            $did_table = 'did';

        if (isset($rs)) {
            $sql = "SELECT * FROM customer_rates WHERE ratecard_id in  (" . $incoming_ratecard_id . ") and ('" . $did . "' like concat('%',prefix,'%') or prefix like '%" . $did . "%')";
            $query = $this->db->query($sql);
            $rs_rates = $query->row();
            if (isset($rs_rates)) {
                $option_param = array();
                $user_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
                $parent_account_id = $user_result['parent_account_id'];
                if ($account_type == 'CUSTOMER') {
                    if ($parent_account_id == '') {
                        $sql = "SELECT did_number FROM $did_table where (account_id is NULL OR account_id='') and did_status = 'NEW' ";
                    } else {
                        $reseller_result = $this->member_mod->get_account_by_key('account_id', $parent_account_id, $option_param);
                        $parent_level = $reseller_result['account_level'];
                        $sql = "SELECT did_number FROM $did_table where account_id is null and did_status = 'USED' ";
                        if ($parent_level == 1)
                            $sql .= " and reseller1_account_id = '" . $parent_account_id . "'";
                        elseif ($parent_level == 2)
                            $sql .= " and reseller2_account_id = '" . $parent_account_id . "'";
                        else
                            $sql .= " and reseller3_account_id = '" . $parent_account_id . "'";
                    }
                } else {
                    $reseller_level = get_logged_account_level();
                    $sql = "SELECT did_number FROM $did_table where (account_id is NULL OR account_id='') ";
                    if ($reseller_level == 1) {
                        $sql .= " AND did_status = 'NEW' ";
                    } elseif ($reseller_level == 2) {
                        $sql .= " and did_status = 'USED' and reseller1_account_id = '" . $parent_account_id . "'";
                    } elseif ($reseller_level == 3) {
                        $sql .= " and did_status = 'USED' and reseller2_account_id = '" . $parent_account_id . "'";
                    }
                }
                $sql .= " and did_number like '%" . $did . "%' limit 500 ";
                $query = $this->db->query($sql);
                $rows = $query->result_array();


                if (count($rows) > 0) {
                    $didlist = array();
                    foreach ($rows as $k => $v) {
                        $sql = "SELECT * FROM customer_rates WHERE ratecard_id in  (" . $incoming_ratecard_id . ") and ('" . $v['did_number'] . "' like concat(prefix,'%') or prefix like '" . $v['did_number'] . "%') ORDER BY LENGTH(prefix) DESC , rate DESC LIMIT 1;";

                        // $return = array('status' => false, 'msg' => 'DID available', 'dids' => $sql);
                        // return $return;
                        $query = $this->db->query($sql);
                        $rs_rates = $query->row();

                        $didlist[] = array('did' => $v['did_number'], 'setup' => number_format($rs_rates->setup_charge, 4, '.', ''), 'rental' => number_format($rs_rates->rental, 4, '.', ''), 'ppm' => number_format($rs_rates->rate, 4, '.', ''), 'ppc' => number_format($rs_rates->connection_charge, 4, '.', ''), 'min' => $rs_rates->minimal_time, 'res' => $rs_rates->resolution_time, 'grace' => $rs_rates->grace_period, 'add' => number_format($rs_rates->rate_addition, 4, '.', ''), 'mul' => number_format($rs_rates->rate_multiplier, 4, '.', ''));
                    }
                    $return = array('status' => true, 'msg' => 'DID available', 'dids' => $didlist);
                } else {
                    $return = array('status' => false, 'msg' => 'No DID available');
                }
            } else {
                $return = array('status' => false, 'msg' => 'No rate available');
            }
        } else {
            $return = array('status' => false, 'msg' => 'No ratecard available');
        }
        //echo '<pre>';print_r($return);echo '</pre>';
        return $return;
    }
	
	
	 function destination_bulk($data) {
        try {
            $log_data_array = array();
            $did_data_array = array();

            $this->db->trans_begin();

            $account_id = get_logged_account_id();
            $assign_did_number = $data['assign_did_number'];

            if (!isset($data['assign_did_number']) || $data['assign_did_number'] == '') {
                throw new Exception('DID Numbers Not Found');
            }

            $did_data_array['dst_type'] = $data['dst_type'];

            if ($data['dst_type'] == 'IP')
                $dst_destination = $data['dst_point_ip'];
            elseif ($data['dst_type'] == 'CUSTOMER')
                $dst_destination = $data['dst_point_sip'];
            else
                $dst_destination = $data['dst_point_pstn'];


            $did_data_array['dst_type2'] = $data['dst_type2'];
            if ($data['dst_type2'] == 'IP')
                $dst_destination2 = $data['dst_point2_ip'];
            elseif ($data['dst_type2'] == 'CUSTOMER')
                $dst_destination2 = $data['dst_point2_sip'];
            else
                $dst_destination2 = $data['dst_point2_pstn'];


            $assign_did_number_array = explode(',', $assign_did_number);
            $assign_did_number_str = "'" . implode("','", $assign_did_number_array) . "'";

            $db_array = $add_array = $update_array = array();

            $sql = "SELECT did_number FROM " . $this->db->dbprefix('did_dst') . " WHERE account_id='$account_id' AND did_number IN($assign_did_number_str)";

            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $db_array[] = $row['did_number'];
            }



            $add_array = array_diff($assign_did_number_array, $db_array);
            $update_array = array_intersect($assign_did_number_array, $db_array);

            $did_add_data_array = $did_update_data_array = array();
            $did_update_data_array['dst_type'] = $data['dst_type'];
            $did_update_data_array['dst_destination'] = $dst_destination;
            $did_update_data_array['dst_type2'] = $data['dst_type2'];
            $did_update_data_array['dst_destination2'] = $dst_destination2;

            if (count($add_array) > 0) {
                foreach ($add_array as $did) {
                    if (trim($did) == '')
                        continue;
                    $did_add_data_array[] = array(
                        'account_id' => $account_id,
                        'did_number' => $did,
                        'create_date' => date('Y-m-d'),
                        'dst_type' => $data['dst_type'],
                        'dst_destination' => $dst_destination,
                        'dst_type2' => $data['dst_type2'],
                        'dst_destination2' => $dst_destination2,
                    );
                }
                $this->db->insert_batch('did_dst', $did_add_data_array);
            }

            if (count($update_array) > 0) {
                $update_array_str = "'" . implode("','", $update_array) . "'";
                $where = " account_id='" . $account_id . "' AND did_number IN($update_array_str)";
                $str = $this->db->update_string($this->db->dbprefix('did_dst'), $did_update_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }



            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
                //set_activity_log($log_data_array);
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function assignment_bulk($did_data) {
        try {
            $account_type = get_logged_account_type();
            $account_id = get_logged_account_id();
            $account_level = get_logged_account_level();
            $this->completed_did_purchase = 0;

            $completed_did_array = array();
			
			////find parent
			$sql = "SELECT parent_account_id, account_type FROM account WHERE account_id='$account_id'";
			$query = $this->db->query($sql);
            $parent_account_row = $query->row_array(); 
			$parent_account_id = $parent_account_row['parent_account_id'];
			$parent_account_type = $parent_account_row['account_type'];
			//////
			
            foreach ($did_data['did'] as $did) {
                $this->db->trans_begin();
                if ($account_type == 'CUSTOMER') {
                    $sql = "update did set did_status = 'USED', account_id = '" . $account_id . "', assign_date = now() WHERE did_number = '" . $did . "'";
                } else {
                    if ($account_level == 1)
                        $sql = "update did set did_status = 'USED', reseller1_account_id = '" . $account_id . "',reseller1_assign_date = now() WHERE did_number = '" . $did . "'";
                    elseif ($account_level == 2)
                        $sql = "update did set did_status = 'USED', reseller2_account_id = '" . $account_id . "', reseller2_assign_date = now() WHERE did_number = '" . $did . "'";
                    else
                        $sql = "update did set did_status = 'USED', reseller3_account_id = '" . $account_id . "', reseller3_assign_date = now() WHERE did_number = '" . $did . "'";
                }
                $result = $this->db->query($sql);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                if ($this->db->trans_status() === FALSE) {
                    $error_array = $this->db->error();
                    $this->db->trans_rollback();
                    throw new Exception($error_array['message']);
                } else {                   	
                    
				if($account_type == 'CUSTOMER' || $parent_account_id!='')
				{
					////customer
					$api_request=array();
					$api_request['REQUEST'] = 'NEWDIDSETUP';
                    $api_request['account_id'] = $account_id;
                    $api_request['service_number'] = $did;
                    $api_request['account_type'] = $account_type;
                    $api_request['account_level'] = $account_level;
                    $api_response = call_billing_api($api_request);
					$api_result = json_decode($api_response, true);
					if (!isset($api_result['error']) || $api_result['error'] == '1') {
                        $this->db->trans_rollback();
                        throw new Exception('SDR Problem:' . $api_result['message']);
                    }   
					///////
					//////parent if exists
					if($parent_account_id!='')
					{
						$api_request=array();
						$api_request['REQUEST'] = 'NEWDIDSETUP';
						$api_request['account_id'] = $parent_account_id;
						$api_request['service_number'] = $did;
						$api_request['account_type'] = $parent_account_type;
						$api_request['account_level'] = $account_level-1;
						$api_response = call_billing_api($api_request);
						$api_result = json_decode($api_response, true);
					}
					
					   
				}
					
					/*echo "---------------------------------1 <br>";
					
					echo ( $api_response);
					
                    
						echo "---------------------------------2 <br>";
					print_r( $api_result);
					die;*/
                                     
                }
                $this->db->trans_commit();
                $this->completed_did_purchase = $this->completed_did_purchase + 1;
                $completed_did_array[] = $did;
                $did_key = array_search($did, $_SESSION['cart']['did']);
                if ($did_key !== false)
                    unset($_SESSION['cart']['did'][$did_key]);
            }

            if (isset($did_data['id_checkbox_configure_dest']) && $did_data['id_checkbox_configure_dest'] == 'yes') {
                if (count($completed_did_array) > 0) {
                    if ($did_data['dst_type'] == 'IP')
                        $dst_destination = $did_data['dst_point_ip'];
                    elseif ($did_data['dst_type'] == 'CUSTOMER')
                        $dst_destination = $did_data['dst_point_sip'];
                    else
                        $dst_destination = $did_data['dst_point_pstn'];
                    if ($did_data['dst_type2'] == 'IP')
                        $dst_destination2 = $did_data['dst_point2_ip'];
                    elseif ($did_data['dst_type2'] == 'CUSTOMER')
                        $dst_destination2 = $did_data['dst_point2_sip'];
                    else
                        $dst_destination2 = $did_data['dst_point2_pstn'];
                    $did_add_data_array = array();
                    foreach ($completed_did_array as $did) {
                        if (trim($did) == '')
                            continue;
                        $did_add_data_array[] = array(
                            'account_id' => $account_id,
                            'did_number' => $did,
                            'create_date' => date('Y-m-d'),
                            'dst_type' => $did_data['dst_type'],
                            'dst_destination' => $dst_destination,
                            'dst_type2' => $did_data['dst_type2'],
                            'dst_destination2' => $dst_destination2,
                        );
                    }
                    $this->db->insert_batch('did_dst', $did_add_data_array);
                }
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();

            if (isset($did_data['id_checkbox_configure_dest']) && $did_data['id_checkbox_configure_dest'] == 'yes') {
                if (count($completed_did_array) > 0) {
                    if ($data['dst_type'] == 'IP')
                        $dst_destination = $did_data['dst_point_ip'];
                    elseif ($data['dst_type'] == 'CUSTOMER')
                        $dst_destination = $did_data['dst_point_sip'];
                    else
                        $dst_destination = $did_data['dst_point_pstn'];

                    if ($data['dst_type2'] == 'IP')
                        $dst_destination2 = $did_data['dst_point2_ip'];
                    elseif ($data['dst_type2'] == 'CUSTOMER')
                        $dst_destination2 = $did_data['dst_point2_sip'];
                    else
                        $dst_destination2 = $did_data['dst_point2_pstn'];
                    $did_add_data_array = array();
                    foreach ($completed_did_array as $did) {
                        if (trim($did) == '')
                            continue;
                        $did_add_data_array[] = array(
                            'account_id' => $account_id,
                            'did_number' => $did,
                            'create_date' => date('Y-m-d'),
                            'dst_type' => $did_data['dst_type'],
                            'dst_destination' => $dst_destination,
                            'dst_type2' => $did_data['dst_type2'],
                            'dst_destination2' => $dst_destination2,
                        );
                    }
                    $this->db->insert_batch('did_dst', $did_add_data_array);
                }
            }

            return $e->getMessage();
        }
    }


    function assignment($did, $setup, $rental) {

        try {
            $this->db->trans_begin();
            $account_type = get_logged_account_type();
            $account_id = get_logged_account_id();
            $account_level = get_logged_account_level();
            if ($account_type == 'CUSTOMER') {
                $sql = "update did set did_status = 'USED', account_id = '" . $account_id . "', assign_date = now() WHERE did_number = '" . $did . "'";
            } else {
                if ($account_level == 1)
                    $sql = "update did set did_status = 'USED', reseller1_account_id = '" . $account_id . "',reseller1_assign_date = now() WHERE did_number = '" . $did . "'";
                elseif ($account_level == 2)
                    $sql = "update did set did_status = 'USED', reseller2_account_id = '" . $account_id . "', reseller2_assign_date = now() WHERE did_number = '" . $did . "'";
                else
                    $sql = "update did set did_status = 'USED', reseller3_account_id = '" . $account_id . "', reseller3_assign_date = now() WHERE did_number = '" . $did . "'";
            }
            $result = $this->db->query($sql);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                throw new Exception($error_array['message']);
            } else {	
                $api_request['REQUEST'] = 'NEWDIDSETUP';
                $api_request['account_id'] = $account_id;
                $api_request['service_number'] = $did;
                $api_request['account_type'] = $account_type;
                $api_request['account_level'] = $account_level;
                $api_response = call_billing_api($api_request);
                $api_result = json_decode($api_response, true);
                
                
                if (!isset($api_result['error']) || $api_result['error'] == '1') {
                    $this->db->trans_rollback();
                    throw new Exception('SDR Problem:' . $api_result['message']);
                }
                $this->db->trans_commit();
                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function destination($data) {
        try {
            $log_data_array = array();
            $did_data_array = array();

            $did_data_array['dst_type'] = $data['dst_type'];

            if ($data['dst_type'] == 'IP')
                $did_data_array['dst_destination'] = $data['dst_point_ip'];
            elseif ($data['dst_type'] == 'CUSTOMER')
                $did_data_array['dst_destination'] = $data['dst_point_sip'];
            else
                $did_data_array['dst_destination'] = $data['dst_point_pstn'];


            $did_data_array['dst_type2'] = $data['dst_type2'];
            if ($data['dst_type2'] == 'IP')
                $did_data_array['dst_destination2'] = $data['dst_point2_ip'];
            elseif ($data['dst_type2'] == 'CUSTOMER')
                $did_data_array['dst_destination2'] = $data['dst_point2_sip'];
            else
                $did_data_array['dst_destination2'] = $data['dst_point2_pstn'];;

            $this->db->trans_begin();

            if ($data['dst_id'] == '') {
                $did_data_array['account_id'] = get_logged_account_id();
                $did_data_array['did_number'] = $data['did_number'];
                $did_data_array['create_date'] = date('Y-m-d');

                $str = $this->db->insert_string('did_dst', $did_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $did_id = $this->db->insert_id();
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'did_dst', 'sql_key' => $did_id, 'sql_query' => $str);
            } else {
                $where = "did_dst_id = '" . $data['dst_id'] . "'";
                $str = $this->db->update_string('did_dst', $did_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'did_dst', 'sql_key' => $where, 'sql_query' => $str);
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

    /* Delete Release */

    function clean_did_related_data($did_number, $account_id) {
        return 1;
    }

    function release($did_id, $account_id) {
        try {
            $this->db->trans_begin();
            $sql = "SELECT account_type,account_level,parent_account_id FROM account WHERE account_id ='" . $account_id . "'";
            $query = $this->db->query($sql);
            $user_row = $query->row_array();
            if (!isset($user_row)) {
                return 'User Not Found';
            }
            $sql = "SELECT * FROM did WHERE did_id ='" . $did_id . "'";
            $query = $this->db->query($sql);
            $did_row = $query->row_array();
            if (!isset($did_row)) {
                return 'User Not Found';
            }
            $log_data_array = array();
            $new_status = '';
            if ($user_row['account_type'] == 'CUSTOMER') {
                $new_status = 'NEW';
                $sql = "update did set account_id = NULL, assign_date = NULL ";
                if ($user_row['parent_account_id'] == '')
                    $sql .= ", did_status='" . $new_status . "' ";
                $sql .= " WHERE did_id='" . $did_id . "' AND account_id = '" . $account_id . "'";
                $result = $this->db->query($sql);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                $result = $this->db->delete('did_dst', array('account_id' => $account_id, 'did_number' => $did_row['did_number']));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->clean_did_related_data($did_row['did_number'], $account_id);
            } elseif ($user_row['account_type'] == 'RESELLER') {
                $sql = "update did set ";
                $where = " WHERE did_id='" . $did_id . "' ";
                if ($user_row['account_level'] == 1) {
                    $new_status = 'NEW';
                    $sql .= " reseller1_account_id =NULL, reseller1_assign_date = NULL, did_status='" . $new_status . "' ";
                    $where .= " AND reseller1_account_id = '" . $account_id . "' ";
                } elseif ($user_row['account_level'] == 2) {
                    $sql .= "reseller2_account_id =NULL, reseller2_assign_date = NULL";
                    $where .= " AND reseller2_account_id = '" . $account_id . "' ";
                } else {
                    $sql .= "reseller3_account_id =NULL, reseller3_assign_date = NULL";
                    $where .= " AND reseller3_account_id = '" . $account_id . "' ";
                }

                $sql .= $where;

                $result = $this->db->query($sql);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $api_request['REQUEST'] = 'DIDCANCEL';
                $api_request['account_id'] = $account_id;
                $api_request['service_number'] = $did_row['did_number'];
                $api_request['account_type'] = $user_row['account_type'];
                $api_request['account_level'] = $user_row['account_level'];
                              $api_response = call_billing_api($api_request);
                $api_result = json_decode($api_response, true);
                if (!isset($api_result['error']) || $api_result['error'] == '1') {
                    $this->db->trans_rollback();
                    throw new Exception('SDR Problem:' . $api_result['message']);
                }

                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

}
