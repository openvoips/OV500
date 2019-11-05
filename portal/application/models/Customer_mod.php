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

class Customer_mod extends CI_Model {

    public $account_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = $account_access_id_name_array = $tariff_id_name_array = array();
        $tariff_id_account_access_id_mapping_array = $currency_id_account_access_id_mapping_array = array();
        $service_id_name_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS u.id, u.account_id, u.account_id, u.account_status,u.account_type, u.parent_account_id, u.dp, u.tariff_id, u.account_cc, u.account_cps, u.tax_number, u.tax1, u.tax2, u.tax3, u.tax_type, u.currency_id, u.cli_check, u.dialpattern_check, u.llr_check, u.account_codecs, u.media_transcoding, u.media_rtpproxy, u.account_level,u.vat_flag, u.force_dst_src_cli_prefix,u.codecs_force, u.max_callduration, ua.customer_id, ua.account_id, username, secret, ua.account_type, ua.billing_type, ua.billing_cycle, ua.payment_terms, ua.next_billing_date, ua.name, ua.company_name, ua.emailaddress, ua.phone, ua.address, ua.country_id, ua.state_code_id, ua.pincode FROM customers ua INNER JOIN web_access on web_access.customer_id = ua.customer_id INNER JOIN account u ON u.account_id=ua.account_id  WHERE ua.account_type ='CUSTOMER' ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'parent_account_id') {
                        $sql .= " AND $key ='" . $value . "' ";
                    } elseif ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND u.account_id ='" . $value . "' ";
                        } elseif ($key == 'id' || $key == 'tariff_id'){
                            $sql .= " AND $key ='" . $value . "' ";
                        } elseif ( $key == 'account_status' ){
                            $sql .= " AND u.$key ='" . $value . "' ";
                        
                        } elseif ($key == 'ipaddress' and strlen($value)> 0) {
                            $sql .= " AND ua.account_id IN( SELECT account_id FROM customer_ips WHERE $key LIKE '%" . $value . "%' )";
                        } elseif ($key == 'sip_username' and strlen($value)> 0) {
                            $sql .= " AND ua.account_id IN( SELECT account_id FROM customer_sip_account WHERE username LIKE '%" . $value . "%' )";
                        } elseif ($key == 'account_type') {
                            if (is_array($filter_data[$key])) {
                                if (count($filter_data[$key]) > 0) {
                                    $account_type_str = implode("','", $filter_data[$key]);
                                    $sql .= " AND $key IN ('" . $account_type_str . "') ";
                                }
                            } else
                                $sql .= " AND $key ='" . $value . "' ";
                        } else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }
  

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY `name` ASC ";
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
                $account_id = $row['account_id'];
                $tariff_id = $row['tariff_id'];
                $currency_id = $row['currency_id'];

                if (isset($option_param['tariff']) && $option_param['tariff'] == true) {
                    $row['tariff'] = array();
                }
                if (isset($option_param['user']) && $option_param['user'] == true) {
                    $row['user'] = array();
                }
                if (isset($option_param['ip']) && $option_param['ip'] == true) {
                    $row['ip'] = array();
                }
                if (isset($option_param['callerid']) && $option_param['callerid'] == true) {
                    $row['callerid'] = $row['dst_src_cli'] = array();
                }
                if (isset($option_param['prefix']) && $option_param['prefix'] == true) {
                    $row['prefix'] = array();
                }
                if (isset($option_param['sipuser']) && $option_param['sipuser'] == true) {
                    $row['sipuser'] = array();
                }
                if (isset($option_param['dialplan']) && $option_param['dialplan'] == true) {
                    $row['dialplan'] = array();
                }
                if (isset($option_param['translation_rules']) && $option_param['translation_rules'] == true) {
                    $row['translation_rules'] = array();
                }
                if (isset($option_param['callerid_incoming']) && $option_param['callerid_incoming'] == true) {
                    $row['callerid_incoming'] = array();
                }
                if (isset($option_param['translation_rules_incoming']) && $option_param['translation_rules_incoming'] == true) {
                    $row['translation_rules_incoming'] = array();
                }
                if (isset($option_param['currency']) && $option_param['currency'] == true) {
                    $row['currency'] = array();
                }
                if (isset($option_param['notification']) && $option_param['notification'] == true) {
                    $row['notification'] = array();
                }
                if (isset($option_param['balance']) && $option_param['balance'] == true) {
                    $row['balance'] = array();
                }

                $final_return_array['result'][$account_id] = $row;
                $account_id_array[] = $account_id;
                $tariff_id_array[] = $tariff_id;
                $tariff_id_account_id_mapping_array[$tariff_id][] = $account_id;
                $currency_id_account_id_mapping_array[$currency_id][] = $account_id;
            }
            $tariff_id_array = array_unique($tariff_id_array);

            if (isset($option_param['currency']) && $option_param['currency'] == true && count($final_return_array['result']) > 0) {
                $sql = "SELECT * FROM  sys_currencies  WHERE 1";
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $currency_id = $row['currency_id'];
                    if (isset($currency_id_account_id_mapping_array[$currency_id])) {
                        foreach ($currency_id_account_id_mapping_array[$currency_id] as $account_id) {
                            $final_return_array['result'][$account_id]['currency'] = $row;
                        }
                    }
                }
            }

            if (isset($option_param['tariff']) && $option_param['tariff'] == true && count($final_return_array['result']) > 0) {
                $tariff_id_str = implode("','", $tariff_id_array);
                $tariff_id_str = "'" . $tariff_id_str . "'";
                $sql = "SELECT * , ( select GROUP_CONCAT(tariff_bundle_prefixes.prefix) from tariff_bundle_prefixes where tariff_bundle_prefixes.tariff_id = tariff.tariff_id and bundle_id = '1' group by bundle_id) bp1, ( select GROUP_CONCAT(tariff_bundle_prefixes.prefix) from tariff_bundle_prefixes where tariff_bundle_prefixes.tariff_id = tariff.tariff_id and bundle_id = '2' group by bundle_id ) bp2,( select GROUP_CONCAT(tariff_bundle_prefixes.prefix)from tariff_bundle_prefixes  where tariff_bundle_prefixes.tariff_id = tariff.tariff_id and bundle_id = '3' group by bundle_id)  bp3  FROM tariff  where  tariff_id in ($tariff_id_str)";
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $tariff_id = $row['tariff_id'];
                    if (isset($tariff_id_account_id_mapping_array[$tariff_id])) {
                        foreach ($tariff_id_account_id_mapping_array[$tariff_id] as $account_id) {
                            $final_return_array['result'][$account_id]['tariff'] = $row;
                        }
                    }
                }
            }

            if (isset($option_param['balance']) && $option_param['balance'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT id, account_id, credit_limit, balance, credit_limit - balance usable_balance, maxcredit_limit FROM customer_balance WHERE account_id IN($account_id_str)";
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $final_return_array['result'][$account_id]['balance'] = $row;
                }
            }
            if (isset($option_param['ip']) && $option_param['ip'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_ips WHERE account_id IN($account_id_str) ";
                if (isset($option_param['account_ip_id'])) {
                    $sql .= " AND id ='" . $option_param['account_ip_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];
                    $final_return_array['result'][$account_id]['ip'][$id] = $row;
                }
            }

            if (isset($option_param['sipuser']) && $option_param['sipuser'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_sip_account WHERE account_id IN($account_id_str) ";
                if (isset($option_param['customer_sip_id'])) {
                    $sql .= " AND id ='" . $option_param['customer_sip_id'] . "'";
                }

                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];

                    $final_return_array['result'][$account_id]['sipuser'][$id] = $row;
                }
            }

            if (isset($option_param['callerid']) && $option_param['callerid'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_callerid WHERE account_id IN($account_id_str)   and route= 'OUTBOUND'";
                if (isset($option_param['customer_callerid_id'])) {
                    $sql .= " AND id ='" . $option_param['customer_callerid_id'] . "'";
                }

                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];
                    $final_return_array['result'][$account_id]['callerid'][$id] = $row;
                }


                $sql = "SELECT * FROM customer_callerid WHERE account_id IN($account_id_str)  and route= 'DTSBASEDCLI' ";
                if (isset($option_param['dst_src_cli_callerid_id'])) {
                    $sql .= " AND id ='" . $option_param['dst_src_cli_callerid_id'] . "'";
                }

                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $dst_src_cli_callerid_id = $row['id'];

                    $final_return_array['result'][$account_id]['dst_src_cli'][$dst_src_cli_callerid_id] = $row;
                }
            }

            if (isset($option_param['callerid_incoming']) && $option_param['callerid_incoming'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_callerid WHERE account_id IN($account_id_str)  and route= 'INBOUND'";
                if (isset($option_param['customer_callerid_id'])) {
                    $sql .= " AND id  ='" . $option_param['customer_callerid_id'] . "'";
                }

                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];
                    $final_return_array['result'][$account_id]['callerid_incoming'][$id] = $row;
                }
            }

            if (isset($option_param['translation_rules']) && $option_param['translation_rules'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_dialpattern WHERE account_id IN($account_id_str)   and route= 'OUTBOUND'";
                if (isset($option_param['account_dialplan_id'])) {
                    $sql .= " AND id ='" . $option_param['account_dialplan_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];
                    $final_return_array['result'][$account_id]['translation_rules'][$id] = $row;
                }
            }

            if (isset($option_param['translation_rules_incoming']) && $option_param['translation_rules_incoming'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_dialpattern  WHERE account_id IN($account_id_str)     and route= 'INBOUND' ";
                if (isset($option_param['account_dialplan_incoming_id'])) {
                    $sql .= " AND id ='" . $option_param['account_dialplan_incoming_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];
                    $final_return_array['result'][$account_id]['translation_rules_incoming'][$id] = $row;
                }
            }

            if (isset($option_param['dialplan']) && $option_param['dialplan'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT ucd.*, d.dialplan_name FROM customer_dialplan ucd LEFT JOIN dialplan d ON ucd.dialplan_id=d.dialplan_id  WHERE ucd.account_id IN($account_id_str) ";
                if (isset($option_param['account_carrier_dialplan_id'])) {
                    $sql .= " AND ucd.id='" . $option_param['account_carrier_dialplan_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];
                    $final_return_array['result'][$account_id]['dialplan'][$id] = $row;
                }
            }

            if (isset($option_param['notification']) && $option_param['notification'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_notification  WHERE account_id IN($account_id_str) ";
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $notify_name = $row['notify_name'];
                    $final_return_array['result'][$account_id]['notification'][$notify_name] = $row;
                }
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'End users fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function add($data) {
        $api_log_data_array = array();
        $log_data_array = array();
       $data['account_type'] = $account_type = 'CUSTOMER';
        try {
            $sql = "SELECT  username  FROM web_access WHERE username ='" . $data['username'] . "' ";

            if (isset($data['input_account_id'])) {
                $key = $data['input_account_id'];
                $sql .= " OR account_id='" . $key . "'";
            } else {
                $key = $this->generate_key('customers');
            }

            $query = $this->db->query($sql);
            $row = $query->row();
            if (isset($row)) {
                if ($row->username == $data['username'])
                    return 'Username already exists';
                elseif (isset($data['input_account_id']) && $row->account_id == $key)
                    return 'Account Code already exists';
            }
            $account_access_data_array = $account_data_array = $account_access_array = $billing_cust_data_array = array();
            $account_data_array['account_id'] = $key;
            $account_data_array['account_type'] = $account_type;
            $account_data_array['dp'] = $data['dp'];
            $account_data_array['tariff_id'] = $data['tariff_id'];
            $account_data_array['account_cc'] = $data['account_cc'];
            $account_data_array['account_cps'] = $data['account_cps'];
            $account_data_array['tax_type'] = $data['tax_type'];
            $account_data_array['tax1'] = $data['tax1'];
            $account_data_array['tax2'] = $data['tax2'];
            $account_data_array['tax3'] = $data['tax3'];
            $account_data_array['currency_id'] = $data['currency_id'];

            $account_data_array['account_status'] = $data['account_status'];
            if (isset($data['account_type'])) {
				if(strlen(trim($data['account_type']))> 0){
					$account_data_array['account_type'] = $data['account_type'];
				}
                if ($data['account_type'] == 'DEMO') {
                    $account_data_array['account_status'] = '1';
                }
            }
            if (isset($data['force_dst_src_cli_prefix']))
                $account_data_array['force_dst_src_cli_prefix'] = $data['force_dst_src_cli_prefix'];
            if (isset($data['codecs_force']))
                $account_data_array['codecs_force'] = $data['codecs_force'];
            if (isset($data['media_transcoding']))
                $account_data_array['media_transcoding'] = $data['media_transcoding'];
            if (isset($data['media_rtpproxy']))
                $account_data_array['media_rtpproxy'] = $data['media_rtpproxy'];
            if (isset($data['account_codecs']))
                $account_data_array['account_codecs'] = $data['account_codecs'];
            if (isset($data['tax_number']))
                $account_data_array['tax_number'] = $data['tax_number'];
            if (isset($data['vat_flag']))
                $account_data_array['vat_flag'] = $data['vat_flag'];
            if (isset($data['parent_account_id'])) {
                $account_data_array['parent_account_id'] = $data['parent_account_id'];
                if ($data['parent_account_id'] != '') {
                    $account_data_array['account_status'] = '1';
                }
            }

            if (isset($data['max_callduration']))
                $account_data_array['max_callduration'] = $data['max_callduration'];
            $account_access_array['account_id'] = $key;
            $account_access_array['name'] = $data['name'];
            $account_access_array['address'] = $data['address'];
            $account_access_array['country_id'] = $data['country_id'];
            $account_access_array['phone'] = $data['phone'];
            $account_access_array['emailaddress'] = $data['emailaddress'];
            $account_access_data_array['username'] = $data['username'];
            $account_access_data_array['secret'] = $data['secret'];
            $account_access_array['account_type'] = $account_type;
            if (isset($data['company_name']))
                $account_access_array['company_name'] = $data['company_name'];
            if (isset($data['billing_type']))
                $account_access_array['billing_type'] = $data['billing_type'];
            $account_access_array['billing_cycle'] = $data['billing_cycle'];
            $account_access_array['payment_terms'] = $data['payment_terms'];
            if ($account_data_array['billing_cycle'] == 'weekly') {
                $next_billing_date_timestamp = strtotime('next monday');
            } else {
                $next_billing_date_timestamp = strtotime('first day of next month');
            }
            $account_access_array['next_billing_date'] = date('Y-m-d', $next_billing_date_timestamp);


            if (isset($data['pincode']))
                $account_access_array['pincode'] = $data['pincode'];


            if (isset($data['state_code_id']))
                $account_access_array['state_code_id'] = $data['state_code_id'];

            if (isset($data['created_by']))
                $account_access_array['created_by'] = $data['created_by'];
            $account_access_array['create_dt'] = date('Y-m-d H:i:s');


            $this->db->trans_begin();
            if (count($account_data_array) > 0) {
                $str = $this->db->insert_string('account', $account_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                $this->account_id = $key;
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'account', 'sql_key' => $this->account_id, 'sql_query' => $str);
            }

            if (count($account_access_array) > 0) {
                $str = $this->db->insert_string('customers', $account_access_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customers', 'sql_key' => '', 'sql_query' => $str);
            }



            if (count($account_access_data_array) > 0) {
                $sql = "SELECT customer_id FROM customers WHERE account_id ='" . $key . "'";
                $query = $this->db->query($sql);
                foreach ($query->result_array() as $row) {
                    $customer_id = $row['customer_id'];
                }
                $account_access_data_array['customer_id'] = $customer_id;
                $str = $this->db->insert_string('web_access', $account_access_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'web_access', 'sql_key' => '', 'sql_query' => $str);
            }
            $maching_string = '%';
            $remove_string = '';
            $add_string = '%';
            $display_string = '%=>%';
            $callerid_array_temp = array(
                'account_id' => $key,
                'display_string' => $display_string,
                'maching_string' => $maching_string,
                'remove_string' => $remove_string,
                'add_string' => $add_string,
                'action_type' => '1',
                'route' => 'INBOUND'
            );
            $str = $this->db->insert_string('customer_callerid', $callerid_array_temp);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_callerid', 'sql_key' => $key, 'sql_query' => $str);

            $callerid_array_temp = array(
                'account_id' => $key,
                'display_string' => $display_string,
                'maching_string' => $maching_string,
                'remove_string' => $remove_string,
                'add_string' => $add_string,
                'action_type' => '1',
                'route' => 'OUTBOUND'
            );
            $str = $this->db->insert_string('customer_callerid', $callerid_array_temp);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_callerid', 'sql_key' => $key, 'sql_query' => $str);


            $prefix_array_temp = array(
                'account_id' => $key,
                'display_string' => $display_string,
                'maching_string' => $maching_string,
                'remove_string' => $remove_string,
                'add_string' => $add_string,
                'route' => 'OUTBOUND'
            );

            $str = $this->db->insert_string('customer_dialpattern', $prefix_array_temp);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_dialpattern', 'sql_key' => $key, 'sql_query' => $str);


            $prefix_array_temp = array(
                'account_id' => $key,
                'display_string' => $display_string,
                'maching_string' => $maching_string,
                'remove_string' => $remove_string,
                'add_string' => $add_string,
                'route' => 'INBOUND'
            );

            $str = $this->db->insert_string('customer_dialpattern', $prefix_array_temp);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_dialpattern', 'sql_key' => $key, 'sql_query' => $str);

            $balance_data_array['credit_limit'] = 0;
            $balance_data_array['balance'] = 0;
            $balance_data_array['account_id'] = $key;
            $balance_data_array['maxcredit_limit'] = $data['maxcredit_limit'];
            $str = $this->db->insert_string('customer_balance', $balance_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_balance', 'sql_key' => $key, 'sql_query' => $str);

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
                set_activity_log($log_data_array);

                /*
                  /////////SDR API////
                  $api_request['account_id'] = $account_access_array['account_id'];
                  $api_request['account_type'] = 'CUSTOMER';
                  $api_request['account_level'] = '';
                  $api_request['is_new_account'] = 'Y';
                  $api_request['service_number'] = $account_data_array['tariff_id']; //tariff
                  $api_request['request'] = 'TARIFFCHARGES';

                  $api_response = callSdrAPI($api_request);
                  $api_result = json_decode($api_response, true);
                  $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));

                  if (!isset($api_result['error']) || $api_result['error'] == '1') {
                  //echo '<pre>';print_r($api_result);die;
                  //$this->db->trans_rollback();
                  //throw new Exception('SDR Problem:('.$api_request['account_id'].')'.$api_result['message']);
                  }
                  ///////////////
                  //////////////sdr api ADDCREDIT///////
                  if ($data['credit_limit'] > 0) {
                  $api_request = array();
                  $api_request['account_id'] = $account_access_array['account_id'];
                  $api_request['account_type'] = 'CUSTOMER';
                  $api_request['service_number'] = 'Increase Credit';
                  $api_request['amount'] = $data['credit_limit'];
                  $api_request['paid_on'] = date('Y-m-d h:i:s');
                  $api_request['notes'] = 'Initial Credit';
                  $api_request['created_by'] = get_logged_account_id();
                  $api_request['request'] = 'ADDCREDIT';

                  $api_response = callSdrAPI($api_request);
                  $api_result = json_decode($api_response, true);
                  $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));
                  set_activity_log($api_log_data_array); //api log
                  if (!isset($api_result['error']) || $api_result['error'] == '1') {

                  }
                  }
                 */


                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            set_activity_log($api_log_data_array); //api log
            return $e->getMessage();
        }

        return true;
    }

    function update($data) {
        $log_data_array = array();
        $api_log_data_array = array();
        $account_type = 'CUSTOMER';
        $key = $data['key'];
        try {
            $this->db->trans_begin();
            $account_access_data_array = $account_data_array = $account_access_array = $billing_cust_data_array = array();
            if (isset($data['username']))
                $account_access_data_array['username'] = $data['username'];
            if (isset($data['secret']) && $data['secret'] != '')
                $account_access_data_array['secret'] = $data['secret'];
           
            if (isset($data['account_status']))
                $account_data_array['account_status'] = $data['account_status'];
            if (isset($data['account_cc']))
                $account_data_array['account_cc'] = $data['account_cc'];
            if (isset($data['account_cps']))
                $account_data_array['account_cps'] = $data['account_cps'];
            if (isset($data['dp']))
                $account_data_array['dp'] = $data['dp'];
            if (isset($data['account_currency_id']))
                $account_data_array['currency_id'] = $data['account_currency_id'];
            if (isset($data['tariff_id']))
                $account_data_array['tariff_id'] = $data['tariff_id'];
            if (isset($data['tax_type']))
                $account_data_array['tax_type'] = $data['tax_type'];
            if (isset($data['tax1']))
                $account_data_array['tax1'] = $data['tax1'];
            if (isset($data['tax2']))
                $account_data_array['tax2'] = $data['tax2'];
            if (isset($data['tax3']))
                $account_data_array['tax3'] = $data['tax3'];
            if (isset($data['account_codecs']))
                $account_data_array['account_codecs'] = $data['account_codecs'];
            if (isset($data['tax_number']))
                $account_data_array['tax_number'] = $data['tax_number'];
            if (isset($data['vat_flag']))
                $account_data_array['vat_flag'] = $data['vat_flag'];

            if (isset($data['billing_type']))
                $account_access_array['billing_type'] = $data['billing_type'];
            if (isset($data['billing_cycle']))
                $account_access_array['billing_cycle'] = $data['billing_cycle'];
            if (isset($data['payment_terms']))
                $account_access_array['payment_terms'] = $data['payment_terms'];

            if (isset($data['media_transcoding']))
                $account_data_array['media_transcoding'] = $data['media_transcoding'];
            if (isset($data['media_rtpproxy']))
                $account_data_array['media_rtpproxy'] = $data['media_rtpproxy'];

            if (isset($data['force_dst_src_cli_prefix']))
                $account_data_array['force_dst_src_cli_prefix'] = $data['force_dst_src_cli_prefix'];
            if (isset($data['codecs_force']))
                $account_data_array['codecs_force'] = $data['codecs_force'];

            if (isset($data['max_callduration']))
                $account_data_array['max_callduration'] = $data['max_callduration'];
            if (isset($data['name']))
                $account_access_array['name'] = $data['name'];
            if (isset($data['company_name']))
                $account_access_array['company_name'] = $data['company_name'];
            if (isset($data['emailaddress']))
                $account_access_array['emailaddress'] = $data['emailaddress'];
            if (isset($data['address']))
                $account_access_array['address'] = $data['address'];
            if (isset($data['phone']))
                $account_access_array['phone'] = $data['phone'];
            if (isset($data['country_id']))
                $account_access_array['country_id'] = $data['country_id'];
            if (isset($data['state_code_id']))
                $account_access_array['state_code_id'] = $data['state_code_id'];
            if (isset($data['pincode']))
                $account_access_array['pincode'] = $data['pincode'];

            if (isset($data['billing_name']))
                $account_access_array['billing_name'] = $data['billing_name'];
            if (isset($data['billing_company_name']))
                $account_access_array['billing_company_name'] = $data['billing_company_name'];
            if (isset($data['billing_emailaddress']))
                $account_access_array['billing_emailaddress'] = $data['billing_emailaddress'];
            if (isset($data['billing_address']))
                $account_access_array['billing_address'] = $data['billing_address'];
            if (isset($data['billing_phone']))
                $account_access_array['billing_phone'] = $data['billing_phone'];
            if (isset($data['billing_country_id']))
                $account_access_array['billing_country_id'] = $data['billing_country_id'];
            if (isset($data['billing_state_code_id']))
                $account_access_array['billing_state_code_id'] = $data['billing_state_code_id'];
            if (isset($data['billing_pincode']))
                $account_access_array['billing_pincode'] = $data['billing_pincode'];

            $sql = "SELECT * FROM " . $this->db->dbprefix('account') . " WHERE account_id ='" . $key . "'";
            $query = $this->db->query($sql);
            $existing_account_row = $query->row_array();
            if (!isset($existing_account_row)) {
                throw new Exception('User Not Found');
            }

            if (isset($data['account_type'])) {
                $account_data_array['account_type'] = $data['account_type'];
                if ($data['account_type'] == 'REAL' && $existing_account_row['account_type'] != 'REAL') {
                    $account_data_array['account_status'] = '-1';
                }
            }
            if (count($account_data_array) > 0) {
                $where = "account_id='" . $key . "'";
                $str = $this->db->update_string('account', $account_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'account', 'sql_key' => $where, 'sql_query' => $str);
            }

            if (count($account_access_array) > 0) {
                $where = " account_id='" . $key . "' AND account_type='" . $account_type . "'";
                $str = $this->db->update_string('customers', $account_access_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customers', 'sql_key' => $where, 'sql_query' => $str);
            }


            if (count($account_access_data_array) > 0) {
                $sql = "SELECT customer_id FROM customers WHERE account_id ='" . $key . "'";
                echo $sql;
                $query = $this->db->query($sql);
                foreach ($query->result_array() as $row) {
                    $customer_id = $row['customer_id'];
                }

                $where = "customer_id='" . $customer_id . "'";
                $str = $this->db->update_string('web_access', $account_access_data_array, $where);

                $result = $this->db->query($str);
                //    echo $this->db->last_query();
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'web_access', 'sql_key' => '', 'sql_query' => $str);
            }
            if (isset($account_data_array['account_status']) && $account_data_array['account_status'] == 0 && $existing_account_row['account_status'] != 0) {
                $delete_data_array = array(
                    'delete_type' => 'account',
                    'delete_status' => '0',
                    'delete_code' => $key,
                    'deleted_by' => $data['logged_account_id']
                );
                $str = $this->db->insert_string('delete_history', $delete_data_array);
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
                if (isset($data['tariff_id']) && $existing_account_row['account_status'] == '1' && $data['tariff_id'] != $existing_account_row['tariff_id']) {
                    $api_request['account_id'] = $key;
                    $api_request['account_type'] = 'CUSTOMER';
                    $api_request['account_level'] = '';
                    $api_request['service_number'] = $data['tariff_id'];
                    $api_request['request'] = 'TARIFFCHARGES';


                    $api_response = callSdrAPI($api_request);
                    $api_result = json_decode($api_response, true);
                    $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));

                    if (!isset($api_result['error']) || $api_result['error'] == '1') {
                        throw new Exception('SDR Problem:(' . $api_request['account_id'] . ')' . $api_result['message']);
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

        return true;
    }

    function delete($id_array) {
        try {
            $this->db->trans_begin();

            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();

                $sql = "SELECT * FROM account WHERE account_id='" . $id . "' AND account_type='CUSTOMER'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('account', array('account_id' => $id, 'account_type' => 'CUSTOMER'));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'account', 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $sql = "SELECT * FROM account WHERE  account_id='" . $id . "' AND account_type='CUSTOMER'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customers', array('account_id' => $id, 'account_type' => 'CUSTOMER'));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customers', 'sql_key' => $id, 'sql_query' => $data_dump);
                }
                $sql = "SELECT * FROM customer_permissions WHERE  account_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customer_permissions', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_permissions', 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $sql = "SELECT * FROM customer_ips WHERE  account_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customer_ips', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_ips', 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $sql = "SELECT * FROM customer_ips WHERE  account_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customer_ips', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_ips', 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $sql = "SELECT * FROM customer_callerid WHERE  account_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customer_callerid', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_callerid', 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $sql = "SELECT * FROM customer_callerid WHERE  account_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customer_callerid', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_callerid', 'sql_key' => $id, 'sql_query' => $data_dump);
                }
                $sql = "SELECT * FROM customer_dialplan WHERE  account_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customer_dialplan', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_dialplan', 'sql_key' => $id, 'sql_query' => $data_dump);
                }
                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'ENDUSER', 'sql_key' => $id, 'sql_query' => '');
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

    function carringcard_pin_key($table = 'customer_sip_account') {
        while (1) {
            $new_key = rand(100000, 999999);
            $sql = "SELECT callingcard_pin FROM customer_sip_account WHERE  account_id ='" . $new_key . "'";
            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows > 0) {
                
            } else {
                break;
            }
        }

        return $new_key;
    }

    function generate_key($table = 'account') {
        $table = 'customers';
        $prefix1 = 'IS';
        $prefix2 = '';
        $sql = "SELECT MAX(customer_id) as table_key FROM " . $table . ";";
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)) {
            $max_key = $row->table_key;
            $new_key_int = $max_key;
            while (1) {
                $new_key_int = $new_key_int + 1;
                $new_key_int_zero_fill = sprintf('%06d', $new_key_int);

                $new_key = $prefix1 . $prefix2 . $new_key_int_zero_fill . rand(100, 999);

                $sql = "SELECT customer_id FROM " . $table . " WHERE  account_id ='" . $new_key . "'";
                $query = $this->db->query($sql);
                $num_rows = $query->num_rows();
                if ($num_rows > 0) {
                    
                } else {
                    break;
                }
            }
        } else {
            $new_key_int = 1;
            $new_key_int_zero_fill = sprintf('%06d', $new_key_int);
            $new_key = $prefix1 . $prefix2 . $new_key_int_zero_fill;
        }
        return $new_key;
    }

    function add_ip($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'Account missing';
            }
            $account_type = 'CUSTOMER';
            if (strpos($data['dialprefix'], '%') === false) {
                $data['dialprefix'] = $data['dialprefix'] . '%';
            }
            if (isset($data['ipaddress'])) {
                $sql = "SELECT account_id FROM customer_ips  WHERE ipaddress='" . $data['ipaddress'] . "' AND dialprefix='" . $data['dialprefix'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return 'This IP & Dial Prefix already exists in system';
                }
            } else {
                return 'This IP in missing in IP add request.';
            }

            $ip_data_array = array();
            $ip_data_array['account_id'] = $data['account_id'];
            $ip_data_array['ipaddress'] = $data['ipaddress'];
            $ip_data_array['description'] = $data['description'];
            $ip_data_array['dialprefix'] = $data['dialprefix'];
            $ip_data_array['ip_cc'] = $data['ip_cc'];
            $ip_data_array['ip_cps'] = $data['ip_cps'];
            $ip_data_array['ip_status'] = $data['ip_status'];

            $this->db->trans_begin();
            $str = $this->db->insert_string('customer_ips', $ip_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->last_customer_ip_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'customer_ips', 'sql_key' => '', 'sql_query' => $str);

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

    function update_ip($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'User missing';
            }
            if (isset($data['id'])) {
                $id = $data['id'];
            } else {
                return 'ID missing';
            }
            $account_type = 'CUSTOMER';

            if (isset($data['ipaddress'])) {
                $sql = "SELECT account_id FROM customer_ips  WHERE ipaddress='" . $data['ipaddress'] . "'  AND dialprefix='" . $data['dialprefix'] . "' AND  id !='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return 'This IP & Dial Prefix already exists in system';
                }
            }
            $ip_data_array = array();
            if (isset($data['ipaddress']))
                $ip_data_array['ipaddress'] = $data['ipaddress'];
            if (isset($data['description']))
                $ip_data_array['description'] = $data['description'];
            if (isset($data['dialprefix'])) {
                if (strpos($data['dialprefix'], '%') === false) {
                    $data['dialprefix'] = $data['dialprefix'] . '%';
                }
                $ip_data_array['dialprefix'] = $data['dialprefix'];
            }
            if (isset($data['ip_cc']))
                $ip_data_array['ip_cc'] = $data['ip_cc'];
            if (isset($data['ip_cps']))
                $ip_data_array['ip_cps'] = $data['ip_cps'];
            if (isset($data['ip_status']))
                $ip_data_array['ip_status'] = $data['ip_status'];
            $this->db->trans_begin();
            if (count($ip_data_array) > 0) {
                $where = " id='" . $id . "' AND account_id='" . $account_id . "' ";
                $str = $this->db->update_string('customer_ips', $ip_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_ips', 'sql_key' => $where, 'sql_query' => $str);
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

    function add_sip($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'User missing';
            }
            $account_type = 'CUSTOMER';

            if (isset($data['username'])) {
                $sql = "SELECT username FROM customer_sip_account  WHERE username='" . $data['username'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return "Username " . $data['username'] . " already exist. Please use another username; the value is reset with original value.";
                }
            }
            if (isset($data['extension_no'])) {
                $sql = "SELECT extension_no FROM customer_sip_account  WHERE extension_no='" . $data['extension_no'] . "' AND account_id ='" . $account_id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return "Extension number  " . $data['extension_no'] . " already exist. Please use another extension number and value reset with original value.";
                }
            }
            $sip_data_array = array();
            $sip_data_array['account_id'] = $data['account_id'];
            $sip_data_array['username'] = $data['username'];
            $sip_data_array['secret'] = $data['secret'];
            $sip_data_array['ipaddress'] = $data['ipaddress'];
            $sip_data_array['sip_cc'] = $data['sip_cc'];
            $sip_data_array['sip_cps'] = $data['sip_cps'];
            $sip_data_array['status'] = $data['status'];
            if (isset($data['extension_no']))
                $sip_data_array['extension_no'] = $data['extension_no'];
            if (isset($data['voicemail']))
                $sip_data_array['voicemail'] = $data['voicemail'];
            if (isset($data['voicemail_email']))
                $sip_data_array['voicemail_email'] = $data['voicemail_email'];
            $carringcard_pin = $this->carringcard_pin_key();
            $sip_data_array['callingcard_pin'] = $carringcard_pin;
            $this->db->trans_begin();
            $str = $this->db->insert_string('customer_sip_account', $sip_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->last_customer_sip_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'customer_sip_account', 'sql_key' => '', 'sql_query' => $str);

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

    function update_sip($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'User missing';
            }
            if (isset($data['id'])) {
                $id = $data['id'];
            } else {
                return 'ID missing';
            }
            $account_type = 'CUSTOMER';
            if (isset($data['username'])) {
                $sql = "SELECT username FROM customer_sip_account  WHERE username='" . $data['username'] . "' AND id !='" . $id . "'";
                $query = $this->db->query($sql);
                echo $this->db->last_query();
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return "Username " . $data['username'] . " already exist. Please use another username; the value is reset with original value.";
                }
            }
            if (isset($data['extension_no'])) {
                $sql = "SELECT extension_no FROM customer_sip_account  WHERE extension_no='" . $data['extension_no'] . "' AND account_id ='" . $account_id . "' AND id !='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return "Extension number  " . $data['extension_no'] . " already exist. Please use another extension number and value reset with original value.";
                }
            }

            $sip_data_array = array();
            if (isset($data['username']))
                $sip_data_array['username'] = $data['username'];
            if (isset($data['secret']) && $data['secret'] != '')
                $sip_data_array['secret'] = $data['secret'];
            if (isset($data['ipaddress'])) {
                $sip_data_array['ipaddress'] = $data['ipaddress'];
                if ($sip_data_array['ipaddress'] == '')
                    $sip_data_array['ipauthfrom'] = 'NO';
                else
                    $sip_data_array['ipauthfrom'] = 'SRC';
            }
            if (isset($data['sip_cc']))
                $sip_data_array['sip_cc'] = $data['sip_cc'];
            if (isset($data['sip_cps']))
                $sip_data_array['sip_cps'] = $data['sip_cps'];
            if (isset($data['status']))
                $sip_data_array['status'] = $data['status'];
            if (isset($data['extension_no']))
                $sip_data_array['extension_no'] = $data['extension_no'];
            if (isset($data['voicemail']))
                $sip_data_array['voicemail'] = $data['voicemail'];
            if (isset($data['voicemail_email']))
                $sip_data_array['voicemail_email'] = $data['voicemail_email'];

            $this->db->trans_begin();
            if (count($sip_data_array) > 0) {
                $where = " id ='" . $id . "' AND account_id='" . $account_id . "' ";
                $str = $this->db->update_string('customer_sip_account', $sip_data_array, $where);

                echo $this->db->last_query();
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_sip_account', 'sql_key' => $where, 'sql_query' => $str);
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
            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'User missing';
            $callerid_array = $maching_string_array = $dst_src_cli_maching_string_array = array();
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
                        $match_length = $rules_return['match_length'];
                    }
                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice in Allowed Rules ';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $allowed_rule_temp,
                        'maching_string' => $maching_string,
                        'match_length' => $match_length,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'OUTBOUND',
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
                        $match_length = $rules_return['match_length'];
                    }


                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice in Disallowed Rules';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $disallowed_rule_temp,
                        'maching_string' => $maching_string,
                        'match_length' => $match_length,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'OUTBOUND',
                        'action_type' => '0'
                    );
                }
            }
            $sql = "SELECT id , maching_string FROM customer_callerid WHERE account_id='" . $account_id . "' and route='OUTBOUND'";

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
                    $this->db->delete('', array('account_id' => $account_id, 'route' => 'OUTBOUND'));
                }
            } else {
                foreach ($callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];
                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {
                        $customer_callerid_id = $existing_callerid_array[$maching_string_temp];
                        $where = " id='" . $customer_callerid_id . "' and route ='OUTBOUND';";
                        $str = $this->db->update_string('customer_callerid', $callerid_array_temp, $where);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }

                        $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_callerid', 'sql_key' => $where, 'sql_query' => $str);
                        unset($existing_callerid_array[$maching_string_temp]);
                    } else {
                        $callerid_array_temp['account_id'] = $account_id;
                        $str = $this->db->insert_string('customer_callerid', $callerid_array_temp);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_callerid', 'sql_key' => $where, 'sql_query' => $str);
                    }
                }
                if (count($existing_callerid_array) > 0) {
                    foreach ($existing_callerid_array as $existing_callerid_id) {
                        $this->db->delete('customer_callerid', array('account_id' => $account_id, 'id' => $existing_callerid_id));
                    }
                }
            }
            if (isset($data['dst_src_cli_rules_array'])) {
                foreach ($data['dst_src_cli_rules_array'] as $dst_src_cli_rule_temp) {
                    if (trim($dst_src_cli_rule_temp) == '')
                        continue;
                    $maching_string = $remove_string = $add_string = '';

                    $rules_return = generate_rule_fields($dst_src_cli_rule_temp, 'dst_src_cli');
                    if ($rules_return['status'] === false) {
                        return $rules_return['message'];
                    } else {
                        $maching_string = $rules_return['maching_string'];
                        $remove_string = $rules_return['remove_string'];
                        $add_string = $rules_return['add_string'];
                        $match_length = $rules_return['match_length'];
                    }


                    if (in_array($maching_string, $dst_src_cli_maching_string_array))
                        return $maching_string . ' prefix cannot occur twice in DST Prefix Based CLI Rules ';
                    $dst_src_cli_maching_string_array[] = $maching_string;

                    $dst_src_cli_callerid_array[] = array(
                        'display_string' => $dst_src_cli_rule_temp,
                        'maching_string' => $maching_string,
                        'match_length' => $match_length,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'action_type' => '1',
                        'route' => 'DTSBASEDCLI'
                    );
                }
            }

            $sql = "SELECT id, maching_string FROM customer_callerid WHERE account_id='" . $account_id . "' and route = 'DTSBASEDCLI' ";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $customer_callerid_id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_callerid_array[$maching_string] = $customer_callerid_id;
            }
            if (count($dst_src_cli_callerid_array) == 0) {
                if (count($existing_callerid_array) > 0) {
                    $this->db->delete('customer_callerid', array('account_id' => $account_id, 'route' => 'DTSBASEDCLI'));
                }
            } else {
                foreach ($dst_src_cli_callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];
                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {
                        $customer_callerid_id = $existing_callerid_array[$maching_string_temp];
                        $where = " id ='" . $customer_callerid_id . "' ";
                        $str = $this->db->update_string('customer_callerid', $callerid_array_temp, $where);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_callerid', 'sql_key' => $where, 'sql_query' => $str);
                        unset($existing_callerid_array[$maching_string_temp]);
                    } else {
                        $callerid_array_temp['account_id'] = $account_id;
                        $str = $this->db->insert_string('customer_callerid', $callerid_array_temp);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_callerid', 'sql_key' => $where, 'sql_query' => $str);
                    }
                }
                if (count($existing_callerid_array) > 0) {
                    foreach ($existing_callerid_array as $existing_callerid_id) {
                        $this->db->delete('customer_callerid', array('account_id' => $account_id, 'route' => 'DTSBASEDCLI', 'id' => $existing_callerid_id));
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
            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'User missing';
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
                        $match_length = $rules_return['match_length'];
                    }
                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $allowed_rule_temp,
                        'maching_string' => $maching_string,
                        'match_length' => $match_length,
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
                        $match_length = $rules_return['match_length'];
                    }

                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $disallowed_rule_temp,
                        'maching_string' => $maching_string,
                        'match_length' => $match_length,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'INBOUND',
                        'action_type' => '0'
                    );
                }
            }
            $sql = "SELECT id, maching_string FROM customer_callerid WHERE account_id='" . $account_id . "' and  route = 'INBOUND'";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $customer_callerid_incoming_id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_callerid_array[$maching_string] = $customer_callerid_incoming_id;
            }
            $this->db->trans_begin();
            if (count($callerid_array) == 0) {
                if (count($existing_callerid_array) > 0) {
                    $this->db->delete('customer_callerid', array('account_id' => $account_id));
                }
            } else {
                foreach ($callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];
                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {
                        $customer_callerid_incoming_id = $existing_callerid_array[$maching_string_temp];
                        $where = " id ='" . $customer_callerid_incoming_id . "'   and  route = 'INBOUND' ";
                        $str = $this->db->update_string('customer_callerid', $callerid_array_temp, $where);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }

                        $log_data_array[] = array('activity_type' => 'update', 'sql_table' => $this->db->dbprefix('customer_callerid'), 'sql_key' => $where, 'sql_query' => $str);
                        unset($existing_callerid_array[$maching_string_temp]);
                    } else {
                        $callerid_array_temp['account_id'] = $account_id;
                        $str = $this->db->insert_string('customer_callerid', $callerid_array_temp);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }

                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_callerid', 'sql_key' => $where, 'sql_query' => $str);
                    }
                }

                if (count($existing_callerid_array) > 0) {
                    foreach ($existing_callerid_array as $existing_callerid_id) {
                        $this->db->delete('customer_callerid', array('account_id' => $account_id, 'route' => 'INBOUND', 'id' => $existing_callerid_id));
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

    function update_translation_rules($data) {
        try {
            $log_data_array = array();

            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'User missing';

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
                        $match_length = $rules_return['match_length'];
                    }
                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $allowed_rule_temp,
                        'maching_string' => $maching_string,
                        'match_length' => $match_length,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'OUTBOUND',
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
                        $match_length = $rules_return['match_length'];
                    }


                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $disallowed_rule_temp,
                        'maching_string' => $maching_string,
                        'match_length' => $match_length,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'OUTBOUND',
                        'action_type' => '0'
                    );
                }
            }

            $sql = "SELECT id, maching_string FROM  customer_dialpattern WHERE account_id='" . $account_id . "'";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $account_dialplan_id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_callerid_array[$maching_string] = $account_dialplan_id;
            }

            $this->db->trans_begin();
            if (count($callerid_array) == 0) {
                if (count($existing_callerid_array) > 0) {
                    $this->db->delete('customer_dialpattern', array('account_id' => $account_id, 'route' => 'OUTBOUND'));
                }
            } else {
                foreach ($callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];
                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {//update
                        $account_dialplan_id = $existing_callerid_array[$maching_string_temp];
                        $where = " id ='" . $account_dialplan_id . "' and route = 'OUTBOUND'";
                        $str = $this->db->update_string('customer_dialpattern', $callerid_array_temp, $where);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_dialpattern', 'sql_key' => $where, 'sql_query' => $str);
                        unset($existing_callerid_array[$maching_string_temp]);
                    } else {
                        $callerid_array_temp['account_id'] = $account_id;
                        $str = $this->db->insert_string('customer_dialpattern', $callerid_array_temp);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_dialpattern', 'sql_key' => $where, 'sql_query' => $str);
                    }
                }
                if (count($existing_callerid_array) > 0) {
                    foreach ($existing_callerid_array as $existing_callerid_id) {
                        $this->db->delete('customer_dialpattern', array('account_id' => $account_id, 'route' => 'OUTBOUND', 'id' => $existing_callerid_id));
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

    function update_translation_rules_incoming($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'User missing';

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
                        $match_length = $rules_return['match_length'];
                    }


                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $allowed_rule_temp,
                        'maching_string' => $maching_string,
                        'match_length' => $match_length,
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
                        $match_length = $rules_return['match_length'];
                    }


                    if (in_array($maching_string, $maching_string_array))
                        return $maching_string . ' prefix cannot occur twice';
                    $maching_string_array[] = $maching_string;

                    $callerid_array[] = array(
                        'display_string' => $disallowed_rule_temp,
                        'maching_string' => $maching_string,
                        'match_length' => $match_length,
                        'remove_string' => $remove_string,
                        'add_string' => $add_string,
                        'route' => 'INBOUND',
                        'action_type' => '0'
                    );
                }
            }
            $sql = "SELECT id , maching_string FROM customer_dialpattern WHERE account_id='" . $account_id . "' and route = 'INBOUND'";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $account_dialplan_incoming_id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_callerid_array[$maching_string] = $account_dialplan_incoming_id;
            }

            $this->db->trans_begin();
            if (count($callerid_array) == 0) {
                if (count($existing_callerid_array) > 0) {
                    $this->db->delete('customer_dialpattern', array('account_id' => $account_id, 'route' => 'INBOUND'));
                }
            } else {
                foreach ($callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];
                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {
                        $account_dialplan_incoming_id = $existing_callerid_array[$maching_string_temp];
                        $where = " id='" . $account_dialplan_incoming_id . "' and route = 'INBOUND'";
                        $str = $this->db->update_string('customer_dialpattern', $callerid_array_temp, $where);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_dialpattern', 'sql_key' => $where, 'sql_query' => $str);
                        unset($existing_callerid_array[$maching_string_temp]);
                    } else {
                        $callerid_array_temp['account_id'] = $account_id;
                        $str = $this->db->insert_string('customer_dialpattern', $callerid_array_temp);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_dialpattern', 'sql_key' => $where, 'sql_query' => $str);
                    }
                }

                if (count($existing_callerid_array) > 0) {
                    foreach ($existing_callerid_array as $existing_callerid_id) {
                        $this->db->delete('customer_dialpattern', array('account_id' => $account_id, 'route' => 'INBOUND', 'account_dialplan' => $existing_callerid_id));
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

    function update_dialplan($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'User missing';
            if (isset($data['id']))
                $id = $data['id'];
            else
                return 'ID missing';
            $account_type = 'CUSTOMER';
            if (strpos($data['maching_string'], '%') === false) {
                $data['maching_string'] = $data['maching_string'] . '%';
            }

            if (isset($data['maching_string'])) {
                $sql = "SELECT account_id FROM customer_dialplan WHERE maching_string='" . $data['maching_string'] . "' AND account_id='" . $account_id . "' AND id  !='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return "The Dialing Pattern " . $data['maching_string'] . " already exist in the system. Please use another Dialing Pattern; the value is reset with original value.";
                }
            }

            $display_string = $data['maching_string'] . '=>' . $data['dialplan_id'] . '%';
            $dialplan_data_array = array();
            $dialplan_data_array['dialplan_id'] = $data['dialplan_id'];
            $dialplan_data_array['display_string'] = $display_string;
            $pos = strpos($data['maching_string'], '|');
            if ($pos !== false) {
                $remove_string = substr($data['maching_string'], 0, $pos);

                $data['maching_string'] = str_replace('|', '', $data['maching_string']);
            } else {
                $remove_string = '';
            }
            $dialplan_data_array['remove_string'] = $remove_string;
            $dialplan_data_array['maching_string'] = $data['maching_string'];

            $this->db->trans_begin();
            if (count($dialplan_data_array) > 0) {
                $where = " id ='" . $id . "' AND account_id='" . $account_id . "' ";
                $str = $this->db->update_string('customer_dialplan', $dialplan_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_dialplan', 'sql_key' => $where, 'sql_query' => $str);
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

    function add_dialplan($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'User missing';
            $account_type = 'CUSTOMER';
            if (strpos($data['maching_string'], '%') === false) {
                $data['maching_string'] = $data['maching_string'] . '%';
            }
            if (isset($data['maching_string'])) {
                $sql = "SELECT account_id FROM customer_dialplan WHERE maching_string='" . $data['maching_string'] . "' AND account_id='" . $account_id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return 'Route already exists';
                }
            }
            $display_string = $data['maching_string'] . '=>' . $data['dialplan_id'] . '%';
            $dialplan_data_array = array();
            $dialplan_data_array['account_id'] = $account_id;
            $dialplan_data_array['dialplan_id'] = $data['dialplan_id'];
            $dialplan_data_array['display_string'] = $display_string;
            $pos = strpos($data['maching_string'], '|');
            if ($pos !== false) {
                $remove_string = substr($data['maching_string'], 0, $pos);
                $data['maching_string'] = str_replace('|', '', $data['maching_string']);
            } else {
                $remove_string = '';
            }
            $dialplan_data_array['remove_string'] = $remove_string;
            $dialplan_data_array['maching_string'] = $data['maching_string'];

            $this->db->trans_begin();

            $str = $this->db->insert_string('customer_dialplan', $dialplan_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->last_account_dialplan_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'customer_dialplan', 'sql_key' => '', 'sql_query' => $str);

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
        $sql = "SELECT * FROM " . $this->db->dbprefix('user') . "  WHERE $key ='" . $value . "'";
        $query = $this->db->query($sql);
        $row = $query->row();
        return $row;
    }

    function delete_ip($account_id, $id_array) {
        try {
            $log_data_array = array();
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('customer_ips', array('account_id' => $account_id, 'id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_ips', 'sql_key' => $id, 'sql_query' => $this->db->last_query());
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

    function delete_sip($account_id, $id_array) {
        try {
            $log_data_array = array();
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('customer_sip_account', array('account_id' => $account_id, 'id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => $this->db->dbprefix('customer_sip_account'), 'sql_key' => $id, 'sql_query' => $this->db->last_query());
                if ($this->db->affected_rows() == 0)
                    throw new Exception('SIP not found');
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

    function delete_dialplan($account_id, $id_array) {
        try {
            $log_data_array = array();
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('customer_dialplan', array('account_id' => $account_id, 'id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_dialplan', 'sql_key' => $id, 'sql_query' => $this->db->last_query());
                if ($this->db->affected_rows() == 0)
                    throw new Exception('Dialplan not found');
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

    function update_notification($data) {
        try {
            $log_data_array = array();

            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'User missing';

            if (isset($data['notifications'])) {
                foreach ($data['notifications'] as $notify_name => $notify_array) {
                    $status = $notify_array['status'];
                    $notify_emails = $notify_array['notify_emails'];
                    $balance = $notify_array['balance'];
                    $sql = "SELECT status FROM customer_notification WHERE account_id='" . $account_id . "' AND notify_name='" . $notify_name . "'";
                    $query = $this->db->query($sql);
                    $row = $query->row();
                    if (isset($row)) {
                        if ($row->status != $status || $row->notify_emails != $notify_emails) {
                            $where = "account_id='" . $account_id . "' AND notify_name='" . $notify_name . "'";
                            $data_array = array('status' => $status, 'notify_emails' => $notify_emails, 'notify_amount' => $balance);
                            $str = $this->db->update_string('customer_notification', $data_array, $where);
                            $result = $this->db->query($str);
                            if (!$result) {
                                $error_array = $this->db->error();
                                throw new Exception($error_array['message']);
                            }
                            $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_notification', 'sql_key' => $where, 'sql_query' => $str);
                        }
                    } else {
                        $data_array = array('account_id' => $account_id, 'notify_name' => $notify_name, 'notify_emails' => $notify_emails, 'status' => $status);
                        $str = $this->db->insert_string('customer_notification', $data_array);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_notification', 'sql_key' => '', 'sql_query' => $str);
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

}
