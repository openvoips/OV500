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

class Carrier_mod extends CI_Model {

    public $id;
    public $carrier_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function gateway_ipaddress($filter_data = array()) {
        try {
            $carrier_id = $filter_data['carrier_id'];
            $id = $filter_data['id'];
            $sql = "SELECT carrier.carrier_name, carrier.tariff_id, carrier.carrier_status, carrier.carrier_cps, carrier.carrier_cc, carrier.carrier_currency_id, carrier.provider_id, carrier.carrier_progress_timeout, carrier.carrier_ring_timeout, carrier.cli_prefer, carrier.carrier_codecs, carrier.gateway_withmedia, carrier.tax1, carrier.tax2, carrier.tax3, carrier.tax_type, carrier.dp, carrier.carrier_type, carrier.vat_flag, carrier.tax_number, carrier_ips.id, carrier_ips.carrier_ip_id, carrier_ips.carrier_id, carrier_ips.ipaddress_name, carrier_ips.ipaddress, carrier_ips.load_share, carrier_ips.priority, carrier_ips.ip_status, carrier_ips.auth_type, carrier_ips.username, carrier_ips.passwd FROM carrier_ips inner join carrier on carrier.carrier_id = carrier_ips.carrier_id  WHERE carrier_ips.carrier_id ='$carrier_id' ";
            if ($id > 0) {
                $sql .= " AND carrier_ips.id ='" . $id . "'";
            }
        
            $query = $this->db->query($sql);
            
         //   echo $this->db->last_query();
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            foreach ($query->result_array() as $row) {

                $final_return_array['result'] = $row;
            }
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Carriers IP fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = $carrier_id_array = $tariff_id_array = array();
        $tariff_id_carrier_id_mapping_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS id, carrier_type, carrier_id, carrier_name, tariff_id, carrier_status, carrier_cps, carrier_cc, carrier_currency_id, carrier_progress_timeout, carrier_ring_timeout, cli_prefer, carrier_codecs, dp, cli_prefer, tax1,tax2,tax3,tax_type,tax_number,vat_flag, provider_id, sys_currencies.name cname, sys_currencies.symbol FROM carrier  INNER JOIN sys_currencies on sys_currencies.currency_id = carrier.carrier_currency_id  WHERE 1 ";
            $carrier_ip_sql = '';
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'carrier_id')
                            $sql .= " AND $key ='" . $value . "' ";
                        if ($key == 'id')
                            $sql .= " AND carrier.id ='" . $value . "' ";
                        if ($key == 'gateway_id')
                            $sql .= " AND carrier_ips.id ='" . $value . "' ";
                        if ($key == 'username' || $key == 'ipaddress') {
                            if ($carrier_ip_sql != '')
                                $carrier_ip_sql .= ' OR ';
                            $carrier_ip_sql .= " $key LIKE '%" . $value . "%' ";
                        } else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            if ($carrier_ip_sql != '') {
                $sql .= " AND carrier_id IN( SELECT carrier_id FROM carrier_ips WHERE " . $carrier_ip_sql . ")";
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY `carrier_name` ASC ";
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
                $carrier_id = $row['carrier_id'];
                $tariff_id = $row['tariff_id'];

                if (isset($option_param['tariff']) && $option_param['tariff'] == true) {
                    $row['tariff'] = array();
                }
                if (isset($option_param['ip']) && $option_param['ip'] == true) {
                    $row['ip'] = array();
                }
                if (isset($option_param['callerid']) && $option_param['callerid'] == true) {
                    $row['callerid'] = array();
                }
                if (isset($option_param['prefix']) && $option_param['prefix'] == true) {
                    $row['prefix'] = array();
                }
                if (isset($option_param['callerid_incoming']) && $option_param['callerid_incoming'] == true) {
                    $row['callerid_incoming'] = array();
                }
                if (isset($option_param['prefix_incoming']) && $option_param['prefix_incoming'] == true) {
                    $row['prefix_incoming'] = array();
                }
                $final_return_array['result'][$carrier_id] = $row;
                $carrier_id_array[] = $carrier_id;
                $tariff_id_array[] = $tariff_id;
                $tariff_id_carrier_id_mapping_array[$tariff_id][] = $carrier_id;
            }
            $tariff_id_array = array_unique($tariff_id_array);

            if (isset($option_param['tariff']) && $option_param['tariff'] == true && count($final_return_array['result']) > 0) {
                $tariff_id_str = implode("','", $tariff_id_array);
                $tariff_id_str = "'" . $tariff_id_str . "'";
                $sql = "SELECT * FROM tariff WHERE tariff_id IN($tariff_id_str) ";
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $tariff_id = $row['tariff_id'];
                    if (isset($tariff_id_carrier_id_mapping_array[$tariff_id])) {
                        foreach ($tariff_id_carrier_id_mapping_array[$tariff_id] as $carrier_id) {
                            $final_return_array['result'][$carrier_id]['tariff'] = $row;
                        }
                    }
                }
            }


            if (isset($option_param['ip']) && $option_param['ip'] == true && count($final_return_array['result']) > 0) {
                $carrier_id_str = implode("','", $carrier_id_array);
                $carrier_id_str = "'" . $carrier_id_str . "'";
                $sql = "SELECT * FROM carrier_ips WHERE carrier_id IN ($carrier_id_str) ";
                if (isset($option_param['carrier_ip_id'])) {
                    $sql .= " AND carrier_ip_id='" . $option_param['carrier_ip_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $carrier_id = $row['carrier_id'];
                    $carrier_ip_id = $row['carrier_ip_id'];
                    $final_return_array['result'][$carrier_id]['ip'][$carrier_ip_id] = $row;
                }
            }

            if (isset($option_param['prefix']) && $option_param['prefix'] == true && count($final_return_array['result']) > 0) {
                $carrier_id_str = implode("','", $carrier_id_array);
                $carrier_id_str = "'" . $carrier_id_str . "'";
                $sql = "SELECT * FROM carrier_prefix WHERE  route = 'OUTBOUND' and  carrier_id IN ($carrier_id_str) ";
                if (isset($option_param['carrier_prefix_id'])) {
                    $sql .= " AND id	='" . $option_param['carrier_prefix_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $carrier_id = $row['carrier_id'];
                    $carrier_prefix_id = $row['id'];
                    $final_return_array['result'][$carrier_id]['prefix'][$carrier_prefix_id] = $row;
                }
            }
            if (isset($option_param['callerid']) && $option_param['callerid'] == true && count($final_return_array['result']) > 0) {
                $carrier_id_str = implode("','", $carrier_id_array);
                $carrier_id_str = "'" . $carrier_id_str . "'";
                $sql = "SELECT * FROM carrier_callerid WHERE route = 'OUTBOUND' and carrier_id IN($carrier_id_str) ";
                if (isset($option_param['carrier_callerid_id'])) {
                    $sql .= " AND carrier_callerid_id ='" . $option_param['carrier_callerid_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $carrier_id = $row['carrier_id'];
                    $carrier_callerid_id = $row['id'];
                    $final_return_array['result'][$carrier_id]['callerid'][$carrier_callerid_id] = $row;
                }
            }

            if (isset($option_param['prefix_incoming']) && $option_param['prefix_incoming'] == true && count($final_return_array['result']) > 0) {
                $carrier_id_str = implode("','", $carrier_id_array);
                $carrier_id_str = "'" . $carrier_id_str . "'";
                $sql = "SELECT * FROM carrier_prefix WHERE  route = 'INBOUND' and carrier_id IN($carrier_id_str) ";
                if (isset($option_param['carrier_prefix_incoming_id'])) {
                    $sql .= " AND id	='" . $option_param['carrier_prefix_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $carrier_id = $row['carrier_id'];
                    $carrier_prefix_incoming_id = $row['id'];
                    $final_return_array['result'][$carrier_id]['prefix_incoming'][$carrier_prefix_incoming_id] = $row;
                }
            }

            if (isset($option_param['callerid_incoming']) && $option_param['callerid_incoming'] == true && count($final_return_array['result']) > 0) {
                $carrier_id_str = implode("','", $carrier_id_array);
                $carrier_id_str = "'" . $carrier_id_str . "'";
                $sql = "SELECT * FROM carrier_callerid WHERE  route = 'INBOUND' and  carrier_id IN($carrier_id_str) ";
                if (isset($option_param['carrier_callerid_incoming_id'])) {
                    $sql .= " AND id ='" . $option_param['carrier_callerid_incoming_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $carrier_id = $row['carrier_id'];
                    $carrier_callerid_incoming_id = $row['id'];

                    $final_return_array['result'][$carrier_id]['callerid_incoming'][$carrier_callerid_incoming_id] = $row;
                }
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Carriers fetched successfully';

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
            $key = generate_key($data['carrier_name'], '');
            $carrier_data_array = array();
            $carrier_data_array['carrier_id'] = $key;
            $carrier_data_array['carrier_name'] = $data['carrier_name'];
            $carrier_data_array['carrier_cc'] = $data['carrier_cc'];
            $carrier_data_array['carrier_cps'] = $data['carrier_cps'];
            $carrier_data_array['dp'] = $data['dp'];
            $carrier_data_array['carrier_currency_id'] = $data['carrier_currency_id'];
            $carrier_data_array['tariff_id'] = $data['tariff_id'];
            $carrier_data_array['carrier_progress_timeout'] = $data['carrier_progress_timeout'];
            $carrier_data_array['carrier_ring_timeout'] = $data['carrier_ring_timeout'];
            $carrier_data_array['carrier_status'] = $data['carrier_status'];
            $carrier_data_array['cli_prefer'] = $data['cli_prefer'];
            if (isset($data['provider_id']))
                $carrier_data_array['provider_id'] = $data['provider_id'];
            if (isset($data['carrier_type']))
                $carrier_data_array['carrier_type'] = $data['carrier_type'];
            if (isset($data['carrier_codecs']))
                $carrier_data_array['carrier_codecs'] = $data['carrier_codecs'];
            
            if (isset($data['vat_flag']))
                $carrier_data_array['vat_flag'] = $data['vat_flag'];
            if (isset($data['tax_type']))
                $carrier_data_array['tax_type'] = $data['tax_type'];
            if (isset($data['tax_number']))
                $carrier_data_array['tax_number'] = $data['tax_number'];
            if (isset($data['tax1']))
                $carrier_data_array['tax1'] = $data['tax1'];
            if (isset($data['tax2']))
                $carrier_data_array['tax2'] = $data['tax2'];
            if (isset($data['tax3']))
                $carrier_data_array['tax3'] = $data['tax3'];

            
            
            
            $this->db->trans_begin();
            if (count($carrier_data_array) > 0) {
                $str = $this->db->insert_string('carrier', $carrier_data_array);
                $result = $this->db->query($str);
                echo $this->db->last_query();
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->carrier_id = $key;
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'carrier', 'sql_key' => $this->carrier_id, 'sql_query' => $str);
            }

            /* OUTBOUND CLI DEFAULT DATA */
            $maching_string = '%';
            $remove_string = '';
            $add_string = '%';
            $display_string = '%=>%';

            $callerid_array_temp = array(
                'carrier_id' => $key,
                'display_string' => $display_string,
                'maching_string' => $maching_string,
                'remove_string' => $remove_string,
                'add_string' => $add_string,
                'route' => 'OUTBOUND',
                'action_type' => '1'
            );

            $str = $this->db->insert_string($this->db->dbprefix('carrier_callerid'), $callerid_array_temp);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => $this->db->dbprefix('carrier_callerid'), 'sql_key' => $key, 'sql_query' => $str);

            /* INBOUND CLI DEFAULT DATA */
            $callerid_array_temp = array(
                'carrier_id' => $key,
                'display_string' => $display_string,
                'maching_string' => $maching_string,
                'remove_string' => $remove_string,
                'add_string' => $add_string,
                'route' => 'INBOUND',
                'action_type' => '1'
            );

            $str = $this->db->insert_string('carrier_callerid', $callerid_array_temp);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'carrier_callerid', 'sql_key' => $key, 'sql_query' => $str);
            /*  OUTBOUND DIALED NUMBER DEFAULT DATA */

            $prefix_array_temp = array(
                'carrier_id' => $key,
                'display_string' => $display_string,
                'maching_string' => $maching_string,
                'remove_string' => $remove_string,
                'route' => 'OUTBOUND',
                'add_string' => $add_string
            );

            $str = $this->db->insert_string('carrier_prefix', $prefix_array_temp);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'carrier_prefix', 'sql_key' => $key, 'sql_query' => $str);

            /*  INBOUND DIALED NUMBER DEFAULT DATA */
            $prefix_array_temp = array(
                'carrier_id' => $key,
                'display_string' => $display_string,
                'maching_string' => $maching_string,
                'remove_string' => $remove_string,
                'route' => 'INBOUND',
                'add_string' => $add_string
            );

            $str = $this->db->insert_string('carrier_prefix', $prefix_array_temp);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'carrier_prefix', 'sql_key' => $key, 'sql_query' => $str);
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
            if (isset($data['carrier_id']))
                $carrier_id = $data['carrier_id'];
            else
                return 'type missing';
            $key = $data['key'];

            $carrier_data_array = array();
            if (isset($data['carrier_status']))
                $carrier_data_array['carrier_status'] = $data['carrier_status'];
            if (isset($data['carrier_name'])) {
                $carrier_data_array['carrier_name'] = $data['carrier_name'];
            }
            if (isset($data['carrier_cc']))
                $carrier_data_array['carrier_cc'] = $data['carrier_cc'];
            if (isset($data['carrier_cps']))
                $carrier_data_array['carrier_cps'] = $data['carrier_cps'];
            if (isset($data['dp']))
                $carrier_data_array['dp'] = $data['dp'];
            if (isset($data['cli_prefer']))
                $carrier_data_array['cli_prefer'] = $data['cli_prefer'];
            if (isset($data['carrier_currency_id']))
                $carrier_data_array['carrier_currency_id'] = $data['carrier_currency_id'];
            if (isset($data['tariff_id']))
                $carrier_data_array['tariff_id'] = $data['tariff_id'];
            if (isset($data['carrier_progress_timeout']))
                $carrier_data_array['carrier_progress_timeout'] = $data['carrier_progress_timeout'];
            if (isset($data['carrier_ring_timeout']))
                $carrier_data_array['carrier_ring_timeout'] = $data['carrier_ring_timeout'];
            if (isset($data['carrier_type']))
                $carrier_data_array['carrier_type'] = $data['carrier_type'];
            if (isset($data['incoming_cdr_billing']))
                $carrier_data_array['incoming_cdr_billing'] = $data['incoming_cdr_billing'];
            if (isset($data['carrier_codecs']))
                $carrier_data_array['carrier_codecs'] = $data['carrier_codecs'];
            if (isset($data['provider_id']))
                $carrier_data_array['provider_id'] = $data['provider_id'];



            if (isset($data['vat_flag']))
                $carrier_data_array['vat_flag'] = $data['vat_flag'];
            if (isset($data['tax_type']))
                $carrier_data_array['tax_type'] = $data['tax_type'];
            if (isset($data['tax_number']))
                $carrier_data_array['tax_number'] = $data['tax_number'];
            if (isset($data['tax1']))
                $carrier_data_array['tax1'] = $data['tax1'];
            if (isset($data['tax2']))
                $carrier_data_array['tax2'] = $data['tax2'];
            if (isset($data['tax3']))
                $carrier_data_array['tax3'] = $data['tax3'];

            $this->db->trans_begin();
            if (count($carrier_data_array) > 0) {
                $where = "carrier_id='" . $key . "'";
                $str = $this->db->update_string($this->db->dbprefix('carrier'), $carrier_data_array, $where);
                $result = $this->db->query($str);               
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'carrier', 'sql_key' => $where, 'sql_query' => $str);
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

    function generate_key($name) {
        $prefix1 = '';
        $timestamp = time();

        $name = preg_replace('/[^a-z\d]/i', '', $name);
        $name = substr($name, 0, 19);
        $key = strtoupper($name);

        $new_key = $key . $timestamp . $prefix1;

        return $new_key;
    }

    function add_ip($data) {
        try {
            $log_data_array = array();
            if (isset($data['carrier_key'])) {
                $carrier_key = $data['carrier_key'];
            } else {
                return 'Carrier missing';
            }
            $ip_data_array = array();
            $ip_data_array['carrier_id'] = $data['carrier_key'];
            $ip_data_array['ip_status'] = $data['ip_status'];
            $ip_data_array['ipaddress_name'] = $data['ipaddress_name'];
            $ip_data_array['ipaddress'] = $data['ipaddress'];
            $ip_data_array['auth_type'] = $data['auth_type'];
            $ip_data_array['load_share'] = $data['load_share'];
            if (isset($ip_data_array['auth_type']) && $ip_data_array['auth_type'] == "IP") {
                $ip_data_array['username'] = $ip_data_array['passwd'] = '';
            } elseif (isset($ip_data_array['auth_type']) && $ip_data_array['auth_type'] == "CUSTOMER") {
                $ip_data_array['username'] = $data['username'];
                $ip_data_array['passwd'] = $data['secret'];
            } elseif (isset($ip_data_array['auth_type'])) {
                return 'Wrong gateway Auth type';
            }

            $prefix = '';
            $timestamp = time();
            $carrier_ip_id = $ip_data_array['ipaddress_name'];
            $carrier_ip_id = preg_replace('/[^a-z\d]/i', '', $carrier_ip_id);
            $carrier_ip_id = substr($carrier_ip_id, 0, 19);
            $carrier_ip_id = strtoupper($carrier_ip_id);
            $carrier_ip_id = $carrier_ip_id . $timestamp . $prefix;
            $ip_data_array['carrier_ip_id'] = $carrier_ip_id;
            $this->db->trans_begin();
            $str = $this->db->insert_string('carrier_ips', $ip_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $insert_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'carrier_ips', 'sql_key' => '', 'sql_query' => $str);
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                $data['result'] = Array('id' => $error_array['message']);
                $data['status'] = '0';
                return $data;
            } else {
                $this->db->trans_commit();
                $data['result'] = Array('id' => $insert_id);
                $data['status'] = '1';
                set_activity_log($log_data_array);
            }

            return $data;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function update_ip($data) {
        try {
            $log_data_array = array();

            if (isset($data['carrier_key']))
                $carrier_key = $data['carrier_key'];
            else
                return 'Carrier missing';
            if (isset($data['id']))
                $id = $data['id'];
            else
                return 'ID missing';

            $ip_data_array = array();
            if (isset($data['ip_status']))
                $ip_data_array['ip_status'] = $data['ip_status'];
            if (isset($data['ipaddress_name']))
                $ip_data_array['ipaddress_name'] = $data['ipaddress_name'];
            if (isset($data['ipaddress']))
                $ip_data_array['ipaddress'] = $data['ipaddress'];
            if (isset($data['auth_type']))
                $ip_data_array['auth_type'] = $data['auth_type'];
            if (isset($data['load_share']))
                $ip_data_array['load_share'] = $data['load_share'];

            if (isset($ip_data_array['auth_type']) && $ip_data_array['auth_type'] == "IP") {
                $ip_data_array['username'] = $ip_data_array['passwd'] = '';
            } elseif (isset($ip_data_array['auth_type']) && $ip_data_array['auth_type'] == "CUSTOMER") {
                $ip_data_array['username'] = $data['username'];
                $ip_data_array['passwd'] = $data['secret'];
            } elseif (!isset($ip_data_array['auth_type'])) {
                return 'Wrong gateway type';
            }

            $this->db->trans_begin();
            if (count($ip_data_array) > 0) {
                $where = " id='" . $id . "' AND carrier_id='" . $carrier_key . "' ";
                $str = $this->db->update_string('carrier_ips', $ip_data_array, $where);
                $result = $this->db->query($str);

                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'carrier_ips', 'sql_key' => $where, 'sql_query' => $str);
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

    function update_callerid($data) {
        try {
            $log_data_array = array();

            if (isset($data['carrier_key'])) {
                $carrier_key = $data['carrier_key'];
            } else {
                return 'Carrier missing';
            }
            $callerid_array = $maching_string_array = array();
            if (isset($data['allowed_rules_array'])) {
                foreach ($data['allowed_rules_array'] as $allowed_rule_temp) {
                    if (trim($allowed_rule_temp) == '')
                        continue;
                    $maching_string = $remove_string = $add_string = '';
                    $rules_return = generate_rule_fields($allowed_rule_temp);
                    if ($rules_return['status'] === false) {
                        return $rules_return['message'];
                    } else {
                        $maching_string = $rules_return['maching_string'];
                        $remove_string = $rules_return['remove_string'];
                        $add_string = $rules_return['add_string'];
                    }
                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $allowed_rule_temp,
                        'maching_string' => $maching_string,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'action_type' => '1',
                        'route' => 'OUTBOUND',
                    );
                }
            }

            if (isset($data['disallowed_rules_array'])) {
                foreach ($data['disallowed_rules_array'] as $disallowed_rule_temp) {
                    if (trim($disallowed_rule_temp) == '')
                        continue;
                    $maching_string = $remove_string = $add_string = '';
                    $rules_return = generate_rule_fields($disallowed_rule_temp, '1_way');
                    if ($rules_return['status'] === false) {
                        return $rules_return['message'];
                    } else {
                        $maching_string = $rules_return['maching_string'];
                        $remove_string = $rules_return['remove_string'];
                        $add_string = $rules_return['add_string'];
                    }
                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;
                    $callerid_array[] = array(
                        'display_string' => $disallowed_rule_temp,
                        'maching_string' => $maching_string,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'OUTBOUND',
                        'action_type' => '0',
                    );
                }
            }
            $sql = "SELECT id, maching_string FROM carrier_callerid WHERE carrier_id ='" . $carrier_key . "'";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_callerid_array[$maching_string] = $id;
            }
            $this->db->trans_begin();
            if (count($callerid_array) == 0) {
                if (count($existing_callerid_array) > 0) {
                    $this->db->delete('carrier_callerid', array('carrier_id' => $carrier_key, 'route' => 'OUTBOUND'));
                }
            } else {
                foreach ($callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];
                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {
                        $id = $existing_callerid_array[$maching_string_temp];
                        $where = " id='" . $id . "' ";
                        $str = $this->db->update_string('carrier_callerid', $callerid_array_temp, $where);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'carrier_callerid', 'sql_key' => $where, 'sql_query' => $str);
                        unset($existing_callerid_array[$maching_string_temp]);
                    } else {
                        $callerid_array_temp['carrier_id'] = $carrier_key;
                        $str = $this->db->insert_string('carrier_callerid', $callerid_array_temp);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'carrier_callerid', 'sql_key' => $where, 'sql_query' => $str);
                    }
                }
                if (count($existing_callerid_array) > 0) {
                    foreach ($existing_callerid_array as $existing_callerid_id) {
                        $this->db->delete('carrier_callerid', array('route' => 'OUTBOUND', 'carrier_id' => $carrier_key, 'id' => $existing_callerid_id));
                    }
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

    function update_prefix($data) {
        try {
            $log_data_array = array();
            if (isset($data['carrier_key'])) {
                $carrier_key = $data['carrier_key'];
            } else {
                return 'Carrier missing';
            }
            $prefix_array = $maching_string_array = array();
            if (isset($data['rules'])) {
                foreach ($data['rules'] as $allowed_rule_temp) {
                    if (trim($allowed_rule_temp) == '')
                        continue;
                    $maching_string = $remove_string = $add_string = '';
                    $rules_return = generate_rule_fields($allowed_rule_temp);
                    if ($rules_return['status'] === false) {
                        return $rules_return['message'];
                    } else {
                        $maching_string = $rules_return['maching_string'];
                        $remove_string = $rules_return['remove_string'];
                        $add_string = $rules_return['add_string'];
                    }
                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;
                    $prefix_array[] = array(
                        'display_string' => $allowed_rule_temp,
                        'maching_string' => $maching_string,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'OUTBOUND'
                    );
                }
            }
            $sql = "SELECT id, maching_string FROM carrier_prefix WHERE route = 'OUTBOUND' and  carrier_id='" . $carrier_key . "'";
            $existing_prefix_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_prefix_array[$maching_string] = $id;
            }
            $this->db->trans_begin();
            if (count($prefix_array) == 0) {
                if (count($existing_prefix_array) > 0) {
                    $this->db->delete('carrier_prefix', array('carrier_id' => $carrier_key, 'route' => 'OUTBOUND'));
                }
            } else {
                foreach ($prefix_array as $prefix_array_temp) {
                    $maching_string_temp = $prefix_array_temp['maching_string'];
                    if (count($existing_prefix_array) > 0 && isset($existing_prefix_array[$maching_string_temp])) {
                        $carrier_prefix_id = $existing_prefix_array[$maching_string_temp];
                        $where = " id='" . $carrier_prefix_id . "' ";
                        $str = $this->db->update_string('carrier_prefix', $prefix_array_temp, $where);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'carrier_prefix', 'sql_key' => $where, 'sql_query' => $str);
                        unset($existing_prefix_array[$maching_string_temp]);
                    } else {
                        $prefix_array_temp['carrier_id'] = $carrier_key;
                        $str = $this->db->insert_string('carrier_prefix', $prefix_array_temp);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'carrier_prefix', 'sql_key' => $where, 'sql_query' => $str);
                    }
                }
                if (count($existing_prefix_array) > 0) {
                    foreach ($existing_prefix_array as $existing_callerid_id) {
                        $this->db->delete('carrier_prefix', array('carrier_id' => $carrier_key, 'id' => $existing_callerid_id));
                    }
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

    function update_callerid_incoming($data) {
        try {
            $log_data_array = array();
            if (isset($data['carrier_key'])) {
                $carrier_key = $data['carrier_key'];
            } else {
                return 'Carrier missing';
            }
            $callerid_array = $maching_string_array = array();
            if (isset($data['allowed_rules_array'])) {
                foreach ($data['allowed_rules_array'] as $allowed_rule_temp) {
                    if (trim($allowed_rule_temp) == '')
                        continue;
                    $maching_string = $remove_string = $add_string = '';
                    $rules_return = generate_rule_fields($allowed_rule_temp);
                    if ($rules_return['status'] === false) {
                        return $rules_return['message'];
                    } else {
                        $maching_string = $rules_return['maching_string'];
                        $remove_string = $rules_return['remove_string'];
                        $add_string = $rules_return['add_string'];
                    }
                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;
                    $callerid_array[] = array(
                        'display_string' => $allowed_rule_temp,
                        'maching_string' => $maching_string,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'INBOUND',
                        'action_type' => '1'
                    );
                }
            }

            if (isset($data['disallowed_rules_array'])) {
                foreach ($data['disallowed_rules_array'] as $disallowed_rule_temp) {
                    if (trim($disallowed_rule_temp) == '')
                        continue;
                    $maching_string = $remove_string = $add_string = '';

                    $rules_return = generate_rule_fields($disallowed_rule_temp, '1_way');
                    if ($rules_return['status'] === false) {
                        return $rules_return['message'];
                    } else {
                        $maching_string = $rules_return['maching_string'];
                        $remove_string = $rules_return['remove_string'];
                        $add_string = $rules_return['add_string'];
                    }
                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $disallowed_rule_temp,
                        'maching_string' => $maching_string,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'INBOUND',
                        'action_type' => '0'
                    );
                }
            }
            $sql = "SELECT id, maching_string FROM carrier_callerid  WHERE route = 'INBOUND' and  carrier_id='" . $carrier_key . "'";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $carrier_callerid_incoming_id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_callerid_array[$maching_string] = $carrier_callerid_incoming_id;
            }


            $this->db->trans_begin();
            if (count($callerid_array) == 0) {
                if (count($existing_callerid_array) > 0) {
                    $this->db->delete('carrier_callerid', array('carrier_id' => $carrier_key, 'route' => 'INBOUND'));
                }
            } else {
                foreach ($callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];
                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {
                        $carrier_callerid_incoming_id = $existing_callerid_array[$maching_string_temp];
                        $where = " id='" . $carrier_callerid_incoming_id . "' ";
                        $str = $this->db->update_string('carrier_callerid', $callerid_array_temp, $where);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'carrier_callerid', 'sql_key' => $where, 'sql_query' => $str);
                        unset($existing_callerid_array[$maching_string_temp]);
                    } else {
                        $callerid_array_temp['carrier_id'] = $carrier_key;
                        $str = $this->db->insert_string('carrier_callerid', $callerid_array_temp);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'carrier_callerid', 'sql_key' => $where, 'sql_query' => $str);
                    }
                }

                if (count($existing_callerid_array) > 0) {
                    foreach ($existing_callerid_array as $existing_callerid_id) {
                        $this->db->delete('carrier_callerid', array('carrier_id' => $carrier_key, 'id' => $existing_callerid_id));
                    }
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

    function update_prefix_incoming($data) {
        try {
            $log_data_array = array();
            if (isset($data['carrier_key'])) {
                $carrier_key = $data['carrier_key'];
            } else {
                return 'Carrier missing';
            }
            $prefix_array = $maching_string_array = array();
            if (isset($data['rules'])) {
                foreach ($data['rules'] as $allowed_rule_temp) {
                    if (trim($allowed_rule_temp) == '')
                        continue;
                    $maching_string = $remove_string = $add_string = '';
                    $rules_return = generate_rule_fields($allowed_rule_temp);
                    if ($rules_return['status'] === false) {
                        return $rules_return['message'];
                    } else {
                        $maching_string = $rules_return['maching_string'];
                        $remove_string = $rules_return['remove_string'];
                        $add_string = $rules_return['add_string'];
                    }
                    if (in_array($maching_string, $maching_string_array)) {
                        return $maching_string . ' prefix cannot occur twice';
                    }
                    $maching_string_array[] = $maching_string;
                    $prefix_array[] = array(
                        'display_string' => $allowed_rule_temp,
                        'maching_string' => $maching_string,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'INBOUND'
                    );
                }
            }

            $sql = "SELECT id, maching_string FROM carrier_prefix  WHERE  route = 'INBOUND' and carrier_id='" . $carrier_key . "'";
            $existing_prefix_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_prefix_array[$maching_string] = $id;
            }

            $this->db->trans_begin();
            if (count($prefix_array) == 0) {
                if (count($existing_prefix_array) > 0) {
                    $this->db->delete('carrier_prefix', array('carrier_id' => $carrier_key, 'route' => 'INBOUND'));
                }
            } else {
                foreach ($prefix_array as $prefix_array_temp) {
                    $maching_string_temp = $prefix_array_temp['maching_string'];
                    if (count($existing_prefix_array) > 0 && isset($existing_prefix_array[$maching_string_temp])) {
                        $carrier_prefix_id = $existing_prefix_array[$maching_string_temp];
                        $where = " id='" . $carrier_prefix_id . "' ";
                        $str = $this->db->update_string('carrier_prefix', $prefix_array_temp, $where);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'carrier_prefix', 'sql_key' => $where, 'sql_query' => $str);
                        unset($existing_prefix_array[$maching_string_temp]);
                    } else {
                        $prefix_array_temp['carrier_id'] = $carrier_key;
                        $str = $this->db->insert_string('carrier_prefix', $prefix_array_temp);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'carrier_prefix', 'sql_key' => $where, 'sql_query' => $str);
                    }
                }
                if (count($existing_prefix_array) > 0) {
                    foreach ($existing_prefix_array as $existing_callerid_id) {
                        $this->db->delete('carrier_prefix', array('carrier_id' => $carrier_key, 'id' => $existing_callerid_id));
                    }
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

    function get_data_total_count() {
        return $this->total_count;
    }

    function get_data_single($key, $value) {
        $sql = "SELECT * FROM carrier  WHERE $key ='" . $value . "'";
        $query = $this->db->query($sql);
        $row = $query->row();
        return $row;
    }

    function delete($id_array) {
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();
                $sql = "SELECT * FROM carrier WHERE carrier_id='" . $id . "' ";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('carrier', array('carrier_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'carrier', 'sql_key' => $id, 'sql_query' => $data_dump);
                }
                $sql = "SELECT * FROM carrier_ips WHERE  carrier_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('carrier_ips', array('carrier_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'carrier_ips', 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $sql = "SELECT * FROM carrier_prefix WHERE  carrier_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('carrier_prefix', array('carrier_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'carrier_prefix', 'sql_key' => $id, 'sql_query' => $data_dump);
                }
                $sql = "SELECT * FROM carrier_callerid WHERE  carrier_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('carrier_callerid', array('carrier_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'carrier_callerid', 'sql_key' => $id, 'sql_query' => $data_dump);
                }
                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'CARRIER', 'sql_key' => $id, 'sql_query' => '');
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

    function delete_ip($carrier_id, $id_array) {
        try {
            $log_data_array = array();
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete($this->db->dbprefix('carrier_ips'), array('carrier_id' => $carrier_id, 'carrier_ip_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => $this->db->dbprefix('carrier_ips'), 'sql_key' => $id, 'sql_query' => $this->db->last_query());
                if ($this->db->affected_rows() == 0)
                    throw new Exception('IP not found');
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
                set_activity_log($log_data_array);
                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

}
