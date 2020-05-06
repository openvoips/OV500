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

class Reseller_mod extends CI_Model {

    public $customers_id;
    public $account_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = $account_id_array = $tariff_id_name_array = array();
        $tariff_id_account_id_mapping_array = $currency_id_account_access_id_name_mapping_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS u.id, u.account_id, u.account_status, u.parent_account_id, u.dp, u.tariff_id, u.account_cc, u.account_cps, u.tax_number, u.tax1, u.tax2, u.tax3, u.tax_type, u.currency_id, u.cli_check, u.dialpattern_check,  u.llr_check, u.account_codecs, u.media_transcoding, u.media_rtpproxy,u.account_level, u.vat_flag, ua.customer_id, ua.account_id, ua.name, ua.address, ua.country_id, ua.state_code_id, ua.phone, ua.emailaddress, web_access.username, web_access.secret, ua.account_type, ua.company_name, ua.billing_type, ua.billing_cycle, ua.payment_terms, ua.next_billing_date,ua.pincode FROM customers ua INNER JOIN web_access on web_access.customer_id = ua.customer_id INNER JOIN account u ON u.account_id=ua.account_id  WHERE ua.account_type ='RESELLER' ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND ua.account_id ='" . $value . "' ";
                        } elseif ($key == 'id' || $key == 'tariff_id') {
                            $sql .= " AND $key ='" . $value . "' ";
                        } elseif ($key == 'account_status') {
                            $sql .= " AND u.$key ='" . $value . "' ";
                        } elseif ($key == 'id' || $key == 'customer_id' || $key == 'tariff_id') {
                            $sql .= " AND $key ='" . $value . "' ";
                        } elseif ($key == 'account_type') {
                            if (is_array($filter_data[$key])) {
                                if (count($filter_data[$key]) > 0) {
                                    $account_type_str = implode("','", $filter_data[$key]);
                                    $sql .= " AND $key IN ('" . $account_type_str . "') ";
                                }
                            } else
                                $sql .= " AND $key ='" . $value . "' ";
                        } else {
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
            }
            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY customer_id desc ";
            }
            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            
           // echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;
            $tariff_id_array = Array();
            $final_return_array['result'] = Array();
            foreach ($query->result_array() as $row) {
                $account_id = $row['account_id'];
                $tariff_id = $row['tariff_id'];
                $currency_id = $row['currency_id'];
                if (isset($option_param['tariff']) && $option_param['tariff'] == true) {
                    $row['tariff'] = array();
                }
                if (isset($option_param['account']) && $option_param['account'] == true) {
                    $row['account'] = array();
                }
                if (isset($option_param['callerid']) && $option_param['callerid'] == true) {
                    $row['callerid'] = array();
                }
                if (isset($option_param['callerid_incoming']) && $option_param['callerid_incoming'] == true) {
                    $row['callerid_incoming'] = array();
                }

                if (isset($option_param['prefix']) && $option_param['prefix'] == true) {
                    $row['prefix'] = array();
                }
                if (isset($option_param['dialplan']) && $option_param['dialplan'] == true) {
                    $row['dialplan'] = array();
                }
                if (isset($option_param['translation_rules']) && $option_param['translation_rules'] == true) {
                    $row['translation_rules'] = array();
                }
                if (isset($option_param['translation_rules_incoming']) && $option_param['translation_rules_incoming'] == true) {
                    $row['translation_rules_incoming'] = array();
                }

                if (isset($option_param['currency']) && $option_param['currency'] == true) {
                    $row['currency'] = array();
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
                $sql = "SELECT * FROM sys_currencies WHERE 1";
                //    echo $sql;
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


            if (isset($option_param['balance']) && $option_param['balance'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT id, account_id, credit_limit, balance, credit_limit - balance usable_balance, maxcredit_limit FROM customer_balance WHERE account_id IN($account_id_str)";

                //   echo $sql;

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



            if (isset($option_param['callerid']) && $option_param['callerid'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_callerid WHERE account_id IN($account_id_str) and route = 'OUTBOUND' ";
                if (isset($option_param['callerid_id'])) {
                    $sql .= " AND id ='" . $option_param['callerid_id'] . "'";
                }
// echo $sql;
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $account_callerid_id = $row['id'];

                    $final_return_array['result'][$account_id]['callerid'][$account_callerid_id] = $row;
                }
            }
            if (isset($option_param['callerid_incoming']) && $option_param['callerid_incoming'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_callerid WHERE account_id IN($account_id_str)  and route = 'INBOUND' ";
                if (isset($option_param['callerid_incoming_id'])) {
                    $sql .= " AND id ='" . $option_param['callerid_incoming_id'] . "'";
                }
                // echo $sql;
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
                $sql = "SELECT * FROM customer_dialpattern WHERE account_id IN($account_id_str)  and route = 'OUTBOUND' ";
                if (isset($option_param['dialplan_id'])) {
                    $sql .= " AND id ='" . $option_param['dialplan_id'] . "'";
                }
                // echo $sql;
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $dialplan_id = $row['id'];

                    $final_return_array['result'][$account_id]['translation_rules'][$dialplan_id] = $row;
                }
            }
            if (isset($option_param['translation_rules_incoming']) && $option_param['translation_rules_incoming'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM customer_dialpattern WHERE account_id IN($account_id_str) and route = 'INBOUND'  ";
                if (isset($option_param['dialplan_incoming_id'])) {
                    $sql .= " AND id ='" . $option_param['dialplan_incoming_id'] . "'";
                }
                //   echo $sql;
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $dialplan_incoming_id = $row['id'];

                    $final_return_array['result'][$account_id]['translation_rules_incoming'][$dialplan_incoming_id] = $row;
                }
            }
            if (isset($option_param['dialplan']) && $option_param['dialplan'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT urd.*, d.dialplan_name FROM reseller_dialplan urd LEFT JOIN dialplan d ON urd.dialplan_id = d.dialplan_id  WHERE urd.account_id IN($account_id_str) ";
                if (isset($option_param['reseller_dialplan_id'])) {
                    $sql .= " AND urd.id= '" . $option_param['reseller_dialplan_id'] . "'";
                }
                // echo $sql;
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $reseller_dialplan_id = $row['id'];

                    $final_return_array['result'][$account_id]['dialplan'][$reseller_dialplan_id] = $row;
                }
            }

            if (isset($option_param['tariff']) && $option_param['tariff'] == true && count($final_return_array['result']) > 0) {
                $tariff_id_str = implode("','", $tariff_id_array);
                $tariff_id_str = "'" . $tariff_id_str . "'";
                $sql = "SELECT * , ( select GROUP_CONCAT(tariff_bundle_prefixes.prefix) from tariff_bundle_prefixes where tariff_bundle_prefixes.tariff_id = tariff.tariff_id and bundle_id = '1' group by bundle_id) bp1, ( select GROUP_CONCAT(tariff_bundle_prefixes.prefix) from tariff_bundle_prefixes where tariff_bundle_prefixes.tariff_id = tariff.tariff_id and bundle_id = '2' group by bundle_id ) bp2,( select GROUP_CONCAT(tariff_bundle_prefixes.prefix)from tariff_bundle_prefixes  where tariff_bundle_prefixes.tariff_id = tariff.tariff_id and bundle_id = '3' group by bundle_id)  bp3  FROM tariff  where  tariff_id in ($tariff_id_str)";

                //   echo $sql;
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


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Resellers fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function add($data) {
        $api_log_data_array = array();
        try {
            $log_data_array = array();
            $account_type = 'RESELLER';
            $sql = "SELECT  username FROM web_access  WHERE  username ='" . $data['username'] . "'";
            while (1) {
                $key = generate_key($data['name'], '');
                $sql_check = "SELECT account_id FROM customers 	WHERE account_id ='" . $key . "'";
                $query_check = $this->db->query($sql_check);
                $num_rows = $query_check->num_rows();
                if ($num_rows > 0) {
                    
                } else {
                    break;
                }
            }

            $query = $this->db->query($sql);
            $row = $query->row();
            if (isset($row)) {
                if ($row->username == $data['username'])
                    return 'Username already exists';
            }
            $account_access_data_array = $account_data_array = $account_access_array = array();
            $account_data_array['account_id'] = $key;
            $account_data_array['account_status'] = '1';
            $account_data_array['account_type'] = $account_type;
            $account_data_array['account_level'] = get_logged_account_level() + 1;
            $account_data_array['dp'] = $data['dp'];
            $account_data_array['tariff_id'] = $data['tariff_id'];
            $account_data_array['account_cc'] = $data['account_cc'];
            $account_data_array['account_cps'] = $data['account_cps'];
            $account_data_array['tax_type'] = $data['tax_type'];
            $account_data_array['tax1'] = $data['tax1'];
            $account_data_array['tax2'] = $data['tax2'];
            $account_data_array['tax3'] = $data['tax3'];
            $account_data_array['currency_id'] = $data['currency_id'];

            if (isset($data['vat_flag']))
                $account_data_array['vat_flag'] = $data['vat_flag'];
            if (isset($data['parent_account_id'])) {
                $account_data_array['parent_account_id'] = $data['parent_account_id'];
            }

            if (isset($data['cli_check']))
                $account_data_array['cli_check'] = $data['cli_check'];
            if (isset($data['dialpattern_check']))
                $account_data_array['dialpattern_check'] = $data['dialpattern_check'];

            if (isset($data['llr_check']))
                $account_data_array['llr_check'] = $data['llr_check'];
            if (isset($data['media_transcoding']))
                $account_data_array['media_transcoding'] = $data['media_transcoding'];
            if (isset($data['media_rtpproxy']))
                $account_data_array['media_rtpproxy'] = $data['media_rtpproxy'];

            if (isset($data['tax_number']))
                $account_data_array['tax_number'] = $data['tax_number'];
            $account_access_array['account_id'] = $key;
            $account_access_array['name'] = $data['name'];
            $account_access_array['address'] = $data['address'];
            $account_access_array['country_id'] = $data['country_id'];
            $account_access_array['phone'] = $data['phone'];
            $account_access_array['emailaddress'] = $data['emailaddress'];
            $account_access_data_array['username'] = $data['username'];
            $account_access_data_array['secret'] = $data['secret'];
            $account_access_array['account_type'] = $account_type;
            if (isset($data['current_status']))
                $account_access_array['current_status'] = $data['current_status'];
            else
                $account_access_array['current_status'] = 'LIVE';

            if (isset($data['company_name']))
                $account_access_array['company_name'] = $data['company_name'];
            if (isset($data['billing_type']))
                $account_access_array['billing_type'] = $data['billing_type'];
            $account_access_array['billing_cycle'] = $data['billing_cycle'];
            $account_access_array['payment_terms'] = $data['payment_terms'];
            if ($data['billing_cycle'] == 'weekly') {
                $next_billing_date_timestamp = strtotime('next monday');
            } else {
                $next_billing_date_timestamp = strtotime('first day of next month');
            }
            $account_access_array['next_billing_date'] = date('Y-m-d', $next_billing_date_timestamp);
            if (isset($data['pincode']))
                $account_access_array['pincode'] = $data['pincode'];

            if (isset($data['state_code_id']))
                $account_access_array['state_code_id'] = $data['state_code_id'];
           
            if (isset($data['pincode']))
                $account_access_array['pincode'] = $data['pincode'];

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
            if (isset($data['parent_account_id'])) {
                $sql = "SELECT dialplan_id FROM reseller_dialplan WHERE account_id='" . $data['parent_account_id'] . "'";
                $query = $this->db->query($sql);
                foreach ($query->result_array() as $row) {
                    $dialplan_data_array = array();
                    $dialplan_data_array['account_id'] = $key;
                    $dialplan_data_array['dialplan_id'] = $row['dialplan_id'];
                    $str = $this->db->insert_string('reseller_dialplan', $dialplan_data_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customers', 'sql_key' => '', 'sql_query' => $str);
                }
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
                /*
                  /////////SDR API////
                  $api_request['account_id'] = $account_data_array['account_id'];
                  $api_request['account_type'] = $account_data_array['account_type'];
                  $api_request['account_level'] = $account_data_array['account_level'];
                  $api_request['is_new_account'] = 'Y';
                  $api_request['service_number'] = $account_data_array['tariff_id_name']; //tariff
                  $api_request['request'] = 'TARIFFCHARGES';

                  $api_response = callSdrAPI($api_request);
                  $api_result = json_decode($api_response, true);
                  $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));
                  set_activity_log($api_log_data_array); //api log
                  if (!isset($api_result['error']) || $api_result['error'] == '1') {
                  //echo '<pre>';print_r($api_result);die;
                  $this->db->trans_rollback();
                  throw new Exception('SDR Problem:' . $api_result['message']);
                  }
                  ///////////////
                 */
                $this->db->trans_commit();
                set_activity_log($log_data_array);
            }

            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            set_activity_log($api_log_data_array); //api log
            return $e->getMessage();
        }
    }

    function update($data) {
        try {
            $log_data_array = array();
            $api_log_data_array = array();

            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'id missing';
            $account_type = 'RESELLER';
            $key = $data['key'];
            $account_access_data_array = $account_data_array = $account_data_array = $billing_cust_data_array = array();
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

            if (isset($data['cli_check']))
                $account_data_array['cli_check'] = $data['cli_check'];
            if (isset($data['dialpattern_check']))
                $account_data_array['dialpattern_check'] = $data['dialpattern_check'];
            if (isset($data['llr_check']))
                $account_data_array['llr_check'] = $data['llr_check'];
            if (isset($data['media_transcoding']))
                $account_data_array['media_transcoding'] = $data['media_transcoding'];
            if (isset($data['media_rtpproxy']))
                $account_data_array['media_rtpproxy'] = $data['media_rtpproxy'];
            if (isset($data['tax_number']))
                $account_data_array['tax_number'] = $data['tax_number'];
            if (isset($data['vat_flag']))
                $account_data_array['vat_flag'] = $data['vat_flag'];

            if (isset($data['name']))
                $customer_data_array['name'] = $data['name'];
            if (isset($data['address']))
                $customer_data_array['address'] = $data['address'];
            if (isset($data['country_id']))
                $customer_data_array['country_id'] = $data['country_id'];
            if (isset($data['phone']))
                $customer_data_array['phone'] = $data['phone'];
            if (isset($data['emailaddress']))
                $customer_data_array['emailaddress'] = $data['emailaddress'];
            if (isset($data['username']))
                $account_access_data_array['username'] = $data['username'];
            if (isset($data['secret']) && $data['secret'] != '')
                $account_access_data_array['secret'] = $data['secret'];
            
            if (isset($data['company_name']))
                $customer_data_array['company_name'] = $data['company_name'];
            if (isset($data['billing_type']))
                $customer_data_array['billing_type'] = $data['billing_type'];
            if (isset($data['billing_cycle']))
                $customer_data_array['billing_cycle'] = $data['billing_cycle'];
            if (isset($data['payment_terms']))
                $customer_data_array['payment_terms'] = $data['payment_terms'];
            if (isset($data['state_code_id']))
                $customer_data_array['state_code_id'] = $data['state_code_id'];
            if (isset($data['pincode']))
                $customer_data_array['pincode'] = $data['pincode'];


            $sql = "SELECT * FROM account WHERE account_id ='" . $key . "' AND account_type='" . $account_type . "'";
            $query = $this->db->query($sql);
            $existing_account_row = $query->row_array();
            if (!isset($existing_account_row)) {
                return 'User Not Found';
            }

            $this->db->trans_begin();
            if (count($account_data_array) > 0) {
                $where = "account_id='" . $key . "' AND account_type='" . $account_type . "'";
                $str = $this->db->update_string('account', $account_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'account', 'sql_key' => $where, 'sql_query' => $str);
            }

            if (count($customer_data_array) > 0) {
                $where = "account_id='" . $key . "' AND account_type='" . $account_type . "'";
                $str = $this->db->update_string('customers', $customer_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customers', 'sql_key' => $where, 'sql_query' => $str);
            }


            if (count($account_access_data_array) > 0) {
                $sql = "SELECT customer_id FROM customers WHERE account_id ='" . $key . "'";
                $query = $this->db->query($sql);
                foreach ($query->result_array() as $row) {
                    $customer_id = $row['customer_id'];
                }
                $account_access_data_array['customer_id'] = $customer_id;
                $where = " customer_id='" . $customer_id . "'";
                $str = $this->db->update_string('web_access', $account_access_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'web_access', 'sql_key' => $where, 'sql_query' => $str);
            }


            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {

                if (isset($data['tariff_id']) && $existing_account_row['account_status'] == '1' && $data['tariff_id'] != $existing_account_row['tariff_id']) {
//                    $api_request['account_id'] = $key;
//                    $api_request['account_type'] = $existing_account_row['account_type'];
//                    $api_request['account_level'] = $existing_account_row['account_level'];
//                    $api_request['service_number'] = $data['tariff_id']; //tariff
//                    $api_request['request'] = 'TARIFFCHARGES';
//
//                    $api_response = callSdrAPI($api_request);
//                    $api_result = json_decode($api_response, true);
//                    $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));
//
//                    if (!isset($api_result['error']) || $api_result['error'] == '1') {
//                        $this->db->trans_rollback();
//                        throw new Exception('SDR Problem:(' . $api_request['account_id'] . ')' . $api_result['message']);
//                    }
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

    function delete($id_array) {
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();
                $sql = "SELECT * FROM account WHERE account_id='" . $id . "' AND account_type='RESELLER'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('account', array('account_id' => $id, 'account_type' => 'RESELLER'));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'user', 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $sql = "SELECT * FROM customers WHERE  account_id='" . $id . "' AND account_type='RESELLER'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customers', array('account_id' => $id, 'account_type' => 'RESELLER'));
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
                $sql = "SELECT * FROM customer_dialpattern WHERE  account_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customer_dialpattern', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_dialpattern', 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $sql = "SELECT * FROM reseller_dialplan WHERE  account_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('reseller_dialplan', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'reseller_dialplan', 'sql_key' => $id, 'sql_query' => $data_dump);
                }
                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'RESELLER', 'sql_key' => $id, 'sql_query' => '');
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

    function del_generate_key($name) {
        $prefix1 = 'R';
        $timestamp = time();

        $name = preg_replace('/[^a-z\d]/i', '', $name);
        $name = substr($name, 0, 19);
        $key = strtoupper($name);

        $new_key = $key . $timestamp . $prefix1;

        return $new_key;
    }

    function update_callerid($data) {
        try {
            $log_data_array = array();

            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'User missing';
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
                        'action_type' => '1',
                        'route' => 'OUTBOUND'
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
                        'action_type' => '0',
                        'route' => 'OUTBOUND'
                    );
                }
            }
            $sql = "SELECT id, maching_string FROM customer_callerid WHERE account_id='" . $account_id . "' and   route = 'OUTBOUND'";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $account_callerid_id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_callerid_array[$maching_string] = $account_callerid_id;
            }
            $this->db->trans_begin();
            if (count($callerid_array) == 0) {
                if (count($existing_callerid_array) > 0) {
                    $this->db->delete('customer_callerid', array('account_id' => $account_id, 'route' => 'OUTBOUND'));
                }
            } else {
                foreach ($callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];
                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {
                        $account_callerid_id = $existing_callerid_array[$maching_string_temp];
                        $where = " id ='" . $account_callerid_id . "' ";
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
                        $this->db->delete('customer_callerid', array('account_id' => $account_id, 'route' => 'OUTBOUND', 'id' => $existing_callerid_id));
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

    function update_incoming_callerid($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'User missing';
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
                        'action_type' => '1',
                        'route' => 'INBOUND'
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
                        'action_type' => '0',
                        'route' => 'INBOUND'
                    );
                }
            }

            $sql = "SELECT id, maching_string FROM customer_callerid WHERE account_id='" . $account_id . "' and route = 'INBOUND'";
            $existing_callerid_array = array();

            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $account_callerid_incoming_id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_callerid_array[$maching_string] = $account_callerid_incoming_id;
            }

            $this->db->trans_begin();
            if (count($callerid_array) == 0) {
                if (count($existing_callerid_array) > 0) {
                    $this->db->delete('customer_callerid', array('account_id' => $account_id, 'route' => 'INBOUND'));
                }
            } else {
                foreach ($callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];

                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {
                        $account_callerid_id = $existing_callerid_array[$maching_string_temp];
                        $where = " id ='" . $account_callerid_id . "' ";
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
                    foreach ($existing_callerid_array as $existing_callerid_incoming_id) {
                        $this->db->delete('customer_callerid', array('account_id' => $account_id, 'route' => 'INBOUND', 'id' => $existing_callerid_incoming_id));
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
                        'action_type' => '1',
                        'route' => 'OUTBOUND'
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
                        'action_type' => '0',
                        'route' => 'OUTBOUND'
                    );
                }
            }
            $sql = "SELECT id, maching_string FROM customer_dialpattern  WHERE account_id='" . $account_id . "'  and route = 'OUTBOUND'";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $account_dialplan_id = $row['id'];
                $maching_string = $row['maching_string'];
                $existing_callerid_array[$maching_string] = $account_dialplan_id;
            }
            ////////////////
            $this->db->trans_begin();
            if (count($callerid_array) == 0) {
                if (count($existing_callerid_array) > 0) {
                    $this->db->delete('customer_callerid', array('account_id' => $account_id, 'route' => 'OUTBOUND'));
                }
            } else {
                foreach ($callerid_array as $callerid_array_temp) {
                    $maching_string_temp = $callerid_array_temp['maching_string'];
                    if (count($existing_callerid_array) > 0 && isset($existing_callerid_array[$maching_string_temp])) {//update
                        $account_dialplan_id = $existing_callerid_array[$maching_string_temp];
                        $where = " id ='" . $account_dialplan_id . "' ";
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
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'User missing';
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

            $sql = "SELECT id, maching_string FROM customer_dialpattern WHERE account_id='" . $account_id . "' and route = 'INBOUND' ";
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
                        $where = " id ='" . $account_dialplan_incoming_id . "' ";
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
                        $this->db->delete('customer_dialpattern', array('account_id' => $account_id, 'route' => 'INBOUND', 'id' => $existing_callerid_id));
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

    function add_dialplan($data) {
        try {
            $log_data_array = array();

            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'User missing';

            $account_type = 'RESELLER';
            if (isset($data['dialplan_id'])) {
                $sql = "SELECT account_id FROM reseller_dialplan  WHERE dialplan_id='" . $data['dialplan_id'] . "' AND account_id='" . $account_id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return 'Dialplan already exists';
                }
            }
            $dialplan_data_array = array();
            $dialplan_data_array['account_id'] = $account_id;
            $dialplan_data_array['dialplan_id'] = $data['dialplan_id'];
            $dialplan_data_array['create_dt'] = date('Y-m-d h:i:s');

            $this->db->trans_begin();

            $str = $this->db->insert_string('reseller_dialplan', $dialplan_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'reseller_dialplan', 'sql_key' => '', 'sql_query' => $str);

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
        $sql = "SELECT * FROM  account  WHERE $key ='" . $value . "'";
        $query = $this->db->query($sql);
        $row = $query->row();
        return $row;
    }

    function delete_dialplan($account_id, $id_array) {
        try {
            $log_data_array = array();
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('reseller_dialplan', array('account_id' => $account_id, 'id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'reseller_dialplan', 'sql_key' => $id, 'sql_query' => $this->db->last_query());
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

}
