<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
class Customer_mod extends CI_Model {

    public $account_id;
    public $select_sql;
    public $total_count_sql;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array('result' => array());
        $account_access_id_name_array = $tariff_id_name_array = array();
        $tariff_id_account_access_id_mapping_array = $currency_id_account_access_id_mapping_array = array();
        $service_id_name_array = array();
        try {
            $sql = "SELECT a.*, 
			c.contact_name, c.company_name, c.address, c.country_id, c.state_code_id, c.phone, c.emailaddress, c.pincode,
			 view_ipdevices, view_sipdevice, view_src_out, view_dst_out, view_src_did, view_dst_did,
			 '' tariff_id
			FROM account a INNER JOIN customers c ON a.account_id = c.account_id 
			WHERE a.account_type='CUSTOMER' ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'parent_account_id')
                        $sql .= " AND a.$key ='" . $value . "' ";
                    elseif ($value != '') {
                        if (in_array($key, array('id', 'account_id', 'status_id', 'account_type', 'account_level', 'currency_id'))) {
                            $sql .= " AND a.$key ='" . $value . "' ";
                        } elseif ($key == 'ipaddress' and strlen($value) > 0) {
                            $sql .= " AND c.account_id IN( SELECT account_id FROM customer_ips WHERE $key LIKE '%" . $value . "%' )";
                        } elseif ($key == 'sip_username' and strlen($value) > 0) {
                            $sql .= " AND c.account_id IN( SELECT account_id FROM customer_devices WHERE username LIKE '%" . $value . "%' )";
                        } else {
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY a.create_dt DESC";
            }
            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";

            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = $sql;

            $final_return_array['result'] = Array();
            $tariff_id_array = Array();
            foreach ($query->result_array() as $row) {
                $account_id = $row['account_id'];
                //$tariff_id = $row['tariff_id'];
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
                if (isset($option_param['bundle_package']) || isset($option_param['bundle_package_group_by'])) {
                    $row['bundle_package'] = array();
                }
                if (isset ($option_param['randomcli']) && $option_param['randomcli'] == true) {//set default value
                    $row['randomcli'] = array();
                }
                if (isset ($option_param['randomcli_type1']) && $option_param['randomcli_type1'] == true) {//set default value
                    $row['randomcli_type1'] = array();
                }
                if (isset ($option_param['randomcli_type2']) && $option_param['randomcli_type2'] == true) {//set default value
                    $row['randomcli_type2'] = array();
                }
                if (isset ($option_param['cli_dst_rules']) && $option_param['cli_dst_rules'] == true) {//set default value
                    $row['cli_dst_rules'] = array();
                }

                $final_return_array['result'][$account_id] = $row;
                $account_id_array[] = $account_id;
                //$tariff_id_array[] = $tariff_id;
                // $tariff_id_account_id_mapping_array[$tariff_id][] = $account_id;
                $currency_id_account_id_mapping_array[$currency_id][] = $account_id;
            }
            //$tariff_id_array = array_unique($tariff_id_array);

            if (isset($option_param['cli_dst_rules']) && $option_param['cli_dst_rules'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * 
                FROM account_cli_dst_rules WHERE account_id IN($account_id_str) ";
               
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }                
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    //$cli_dst_rules = $row['id'];
                    $final_return_array['result'][$account_id]['cli_dst_rules'] = $row;
                }
            }
            
            if (isset($option_param['randomcli']) && $option_param['randomcli'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT id, rule_id, rule_name, account_id, destination_prefix, cli_fixprefix, cli_length, cli_status, rule_type 
                FROM randomcli_cust WHERE account_id IN($account_id_str) ";
               
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }                
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $randomcliid = $row['id'];
                    $final_return_array['result'][$account_id]['randomcli'][$randomcliid] = $row;
                }
            }
            if (isset($option_param['randomclisingle']) && $option_param['randomclisingle'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT id, rule_id, rule_name, account_id, destination_prefix, cli_fixprefix, cli_length, cli_status, rule_type 
                FROM randomcli_cust WHERE account_id IN($account_id_str) ";
                if (isset ($option_param['randomcli_id'])) {
                    $sql .= " AND id='" . $option_param['randomcli_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }                
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $randomcliid = $row['id'];
                    $final_return_array['result'][$account_id]['randomclisingle'][$randomcliid] = $row;
                }
            }

            /*
            if (isset($option_param['randomcli_type1']) && $option_param['randomcli_type1'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                            $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT id, rule_id, rule_name, account_id, destination_prefix, cli_fixprefix, cli_length, cli_status 
                FROM randomcli_cust WHERE account_id IN($account_id_str) AND rule_type='1'";
                if (isset ($option_param['randomcli_id'])) {
                    $sql .= " AND id='" . $option_param['randomcli_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $randomcliid = $row['id'];
                    $final_return_array['result'][$account_id]['randomcli_type1'][$randomcliid] = $row;
                }
            }
            if (isset($option_param['randomcli_type2']) && $option_param['randomcli_type2'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                            $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT id, rule_id, rule_name, account_id, destination_prefix, cli_fixprefix, cli_length, cli_status 
                FROM randomcli_cust WHERE account_id IN($account_id_str) AND rule_type='2'";
                if (isset ($option_param['randomcli_id'])) {
                    $sql .= " AND id='" . $option_param['randomcli_id'] . "'";
                }
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $randomcliid = $row['id'];
                    $final_return_array['result'][$account_id]['randomcli_type2'][$randomcliid] = $row;
                }
            }
            */

            if (count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT tariff_id, account_id FROM customer_voipminuts WHERE account_id IN($account_id_str) order by id desc limit 1";
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $tariff_id = $row['tariff_id'];
                    $final_return_array['result'][$account_id]['tariff_id'] = $tariff_id;
                    /////////////////////
                    $tariff_id_array[] = $tariff_id;
                    $tariff_id_account_id_mapping_array[$tariff_id][] = $account_id;
                }
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
                $sql = "SELECT *  FROM tariff  where  tariff_id in ($tariff_id_str)";
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
                $sql = "SELECT * FROM customer_devices WHERE account_id IN($account_id_str) ";
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
        try {
            $this->db->trans_begin();
            $log_data_array = array();
            $account_type = 'CUSTOMER';

            $sql = "SELECT  username FROM users  WHERE  username ='" . $data['username'] . "'";
            if ($data['user_emailaddress'] != '') {
                $sql .= " OR emailaddress ='" . $data['user_emailaddress'] . "'";
            }
            $query = $this->db->query($sql);
            $row = $query->row();
            if (isset($row)) {
                if ($row->username == $data['username'])
                    throw new Exception('Username already exists');
                elseif ($row->emailaddress == $data['user_emailaddress'])
                    throw new Exception('Email ID already exists');
            }

            $sql = "SELECT  company_name FROM customers  WHERE  company_name ='" . $data['company_name'] . "'";
            $query = $this->db->query($sql);
            $row = $query->row();
            if (isset($row)) {
                throw new Exception('Company already exists');
            }

            if (isset($data['account_id']) && $data['account_id'] != '') {
                $key = $data['account_id'];

                $sql = "SELECT account_id FROM account WHERE account_id ='" . $key . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    throw new Exception('Account Code already exists');
                }
            } else
                $key = $this->generate_key($data['company_name'], CUSTOMERCODEPREFIX, 'customers', 'account_id');
            $user_key = $this->member_mod->generate_key('CUSTOMERADMIN');

            $user_data_array = $account_data_array = $customer_data_array = array();
            $account_data_array['account_id'] = $key;
            $account_data_array['status_id'] = '1';
            $account_data_array['account_type'] = $account_type;
            $account_data_array['account_level'] = get_logged_account_level() + 1;
            $account_data_array['dp'] = $data['dp'];
            //   $account_data_array['tariff_id'] = $data['tariff_id'];
            $account_data_array['account_cc'] = $data['account_cc'];
            $account_data_array['account_cps'] = $data['account_cps'];
            $account_data_array['tax_type'] = $data['tax_type'];
            $account_data_array['tax1'] = $data['tax1'];
            $account_data_array['tax2'] = $data['tax2'];
            $account_data_array['tax3'] = $data['tax3'];
            $account_data_array['currency_id'] = $data['currency_id'];

            if (isset($data['vat_flag']))
                $account_data_array['vat_flag'] = $data['vat_flag'];
            if (isset($data['parent_account_id']))
                $account_data_array['parent_account_id'] = $data['parent_account_id'];
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

            if (isset($data['billing_type']))
                $account_access_array['billing_type'] = $data['billing_type'];
            $account_access_array['billing_cycle'] = $data['billing_cycle'];
            $account_access_array['payment_terms'] = $data['payment_terms'];
            if ($data['billing_cycle'] == 'weekly') {
                $next_billing_date_timestamp = strtotime('next monday');
            } else {
                $next_billing_date_timestamp = strtotime('first day of next month');
            }
            //  $account_access_array['next_billing_date'] = date('Y-m-d', $next_billing_date_timestamp);
            /////////////////////	
            $customer_data_array['account_id'] = $key;
            $customer_data_array['contact_name'] = $data['contact_name'];
            $customer_data_array['company_name'] = $data['company_name'];
            $customer_data_array['address'] = $data['address'];
            $customer_data_array['country_id'] = $data['country_id'];
            $customer_data_array['phone'] = $data['phone'];
            $customer_data_array['emailaddress'] = $data['emailaddress'];
            $customer_data_array['state_code_id'] = $data['state_code_id'];
            $customer_data_array['pincode'] = $data['pincode'];

            if (isset($data['view_ipdevices']))
                $customer_data_array['view_ipdevices'] = $data['view_ipdevices'];
            if (isset($data['view_sipdevice']))
                $customer_data_array['view_sipdevice'] = $data['view_sipdevice'];
            if (isset($data['view_src_out']))
                $customer_data_array['view_src_out'] = $data['view_src_out'];
            if (isset($data['view_dst_out']))
                $customer_data_array['view_dst_out'] = $data['view_dst_out'];
            if (isset($data['view_src_did']))
                $customer_data_array['view_src_did'] = $data['view_src_did'];
            if (isset($data['view_dst_did']))
                $customer_data_array['view_dst_did'] = $data['view_dst_did'];
            ///////////
            $user_data_array['account_id'] = $key;
            $user_data_array['user_id'] = $user_key;
            $user_data_array['user_type'] = 'CUSTOMERADMIN';
            $user_data_array['username'] = $data['username'];
            $user_data_array['name'] = $data['company_name'];
            $user_data_array['secret'] = $data['secret'];
            $user_data_array['emailaddress'] = $data['user_emailaddress'];
            $user_data_array['status_id'] = '1';

            $sdr_data_array = array();
            $sdr_data_array['ACCOUNTID'] = $key;
            $sdr_data_array['REQUEST'] = 'OPENINGBALANCE';
            $sdr_data_array['SERVICENUMBER'] = '';
            $sdr_data_array['CREATEDBY'] = $key;
            $api_response = call_billing_api($sdr_data_array);
            $api_result = json_decode($api_response, true);

            if (count($account_data_array) > 0) {
                $account_data_array['create_dt'] = date('Y-m-d');
                $str = $this->db->insert_string('account', $account_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->account_id = $key;
            }

            if (count($customer_data_array) > 0) {
                $str = $this->db->insert_string('customers', $customer_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }

            if (count($user_data_array) > 0) {
                $str = $this->db->insert_string('users', $user_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }
/*
            $tariff_data_array['customer_voipminute_id'] = $key . rand(10000, 99999);
            $tariff_data_array['account_id'] = $key;
            $tariff_data_array['account_type'] = 'CUSTOMER';
            $tariff_data_array['tariff_id'] = $data['tariff_id'];
            $tariff_data_array['status'] = '1';
            $tariff_data_array['created_by'] = get_logged_account_id();
            $tariff_data_array['created_dt'] = date('Y-m-d');

            if (count($tariff_data_array) > 0) {
                $str = $this->db->insert_string('customer_voipminuts', $tariff_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }
*/

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
            }
 $strQSL = "INSERT INTO `bill_customer_priceplan` ( `account_id`, `billing_cycle`, `payment_terms`, `itemised_billing`, `billing_day`) VALUES ( '".$key."', 'MONTHLY', 1, '1', 1);";


$this->db->query($strQSL);

            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            set_activity_log($api_log_data_array); //api log
            return $e->getMessage();
        }
    }

    function update($data) {
        $log_data_array = array();
        $api_log_data_array = array();

        try {
            $this->db->trans_begin();

            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                throw new Exception('id missing');
            $account_type = 'CUSTOMER';

            $account_data_array = $customer_data_array = array();
            if (isset($data['status_id']))
                $account_data_array['status_id'] = $data['status_id'];
            if (isset($data['account_cc']))
                $account_data_array['account_cc'] = $data['account_cc'];
            if (isset($data['account_cps']))
                $account_data_array['account_cps'] = $data['account_cps'];
            if (isset($data['dp']))
                $account_data_array['dp'] = $data['dp'];
            if (isset($data['currency_id']))
                $account_data_array['currency_id'] = $data['currency_id'];
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
            if (isset($data['billing_type']))
                $account_data_array['billing_type'] = $data['billing_type'];
            //

            if (isset($data['force_dst_src_cli_prefix']))
                $account_data_array['force_dst_src_cli_prefix'] = $data['force_dst_src_cli_prefix'];
            if (isset($data['codecs_force']))
                $account_data_array['codecs_force'] = $data['codecs_force'];
            if (isset($data['max_callduration']))
                $account_data_array['max_callduration'] = $data['max_callduration'];
            if (isset($data['account_codecs']))
                $account_data_array['account_codecs'] = $data['account_codecs'];


            ////
            if (isset($data['contact_name']))
                $customer_data_array['contact_name'] = $data['contact_name'];
            if (isset($data['company_name']))
                $customer_data_array['company_name'] = $data['company_name'];
            if (isset($data['address']))
                $customer_data_array['address'] = $data['address'];
            if (isset($data['country_id']))
                $customer_data_array['country_id'] = $data['country_id'];
            if (isset($data['phone']))
                $customer_data_array['phone'] = $data['phone'];
            if (isset($data['emailaddress']))
                $customer_data_array['emailaddress'] = $data['emailaddress'];
            if (isset($data['state_code_id']))
                $customer_data_array['state_code_id'] = $data['state_code_id'];
            if (isset($data['pincode']))
                $customer_data_array['pincode'] = $data['pincode'];


            if (isset($data['view_ipdevices']))
                $customer_data_array['view_ipdevices'] = $data['view_ipdevices'];
            if (isset($data['view_sipdevice']))
                $customer_data_array['view_sipdevice'] = $data['view_sipdevice'];
            if (isset($data['view_src_out']))
                $customer_data_array['view_src_out'] = $data['view_src_out'];
            if (isset($data['view_dst_out']))
                $customer_data_array['view_dst_out'] = $data['view_dst_out'];
            if (isset($data['view_src_did']))
                $customer_data_array['view_src_did'] = $data['view_src_did'];
            if (isset($data['view_dst_did']))
                $customer_data_array['view_dst_did'] = $data['view_dst_did'];



            //	ddd($data);die;

            $sql = "SELECT * FROM account WHERE account_id ='" . $account_id . "' AND account_type='" . $account_type . "'";
            $query = $this->db->query($sql);
            $existing_account_row = $query->row_array();
            if (!isset($existing_account_row)) {
                throw new Exception('Account Not Found-' . $sql);
            }
            $logged_account_id = get_logged_account_id();
            if (isset($data['account_manager']) && $data['account_manager'] != '')
                $account_manager_data_array['account_manager'] = $data['account_manager'];
            $account_manager_data_array['customer_account_id'] = $account_id;
            $account_manager_data_array['account_id'] = $logged_account_id;
            $account_manager_data_array['created_dt'] = date('Y-m-d H:i:s');
            if (count($account_manager_data_array) > 0) {
                $str1 = $this->db->insert_string('account_am', $account_manager_data_array) . ' ON DUPLICATE KEY UPDATE account_manager=values(account_manager)';
                $result1 = $this->db->query($str1);
                if (!$result1) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }
            if (isset($data['account_type'])) {
                $account_data_array['account_type'] = $data['account_type'];
                if ($data['account_type'] == 'REAL' && $existing_account_row['account_type'] != 'REAL') {
                    $account_data_array['account_status'] = '-1';
                }
            }
            if (count($account_data_array) > 0) {//ddd($account_data_array);die;
                $where = "account_id='" . $account_id . "' AND account_type='" . $account_type . "'";
                $str = $this->db->update_string('account', $account_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }

            if (count($customer_data_array) > 0) {
                $where = "account_id='" . $account_id . "'";
                $str = $this->db->update_string('customers', $customer_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }



            if (isset($account_data_array['account_status']) && $account_data_array['account_status'] == 0 && $existing_account_row['account_status'] != 0) {
                $delete_data_array = array(
                    'delete_type' => 'account',
                    'delete_status' => '0',
                    'delete_code' => $account_id,
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
                $this->db->trans_commit();
            }

            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
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

                $sql = "SELECT * FROM customers WHERE  account_id='" . $id . "' ";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customers', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customers', 'sql_key' => $id, 'sql_query' => $data_dump);
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

    function carringcard_pin_key($table = 'customer_devices') {
        while (1) {
            $new_key = rand(100000, 999999);
            $sql = "SELECT callingcard_pin FROM customer_devices WHERE  account_id ='" . $new_key . "'";
            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows > 0) {
                
            } else {
                break;
            }
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
            $ip_data_array['ipauthfrom'] = 'SRC';

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
                $sql = "SELECT username FROM customer_devices  WHERE username='" . $data['username'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return "Username " . $data['username'] . " already exist. Please use another username; the value is reset with original value.";
                }
            }
            if (isset($data['extension_no'])) {
                $sql = "SELECT extension_no FROM customer_devices  WHERE extension_no='" . $data['extension_no'] . "' AND account_id ='" . $account_id . "'";

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
                $sip_data_array['email_address'] = $data['voicemail_email'];

            $this->db->trans_begin();
            $str = $this->db->insert_string('customer_devices', $sip_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->last_customer_sip_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'customer_devices', 'sql_key' => '', 'sql_query' => $str);

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
                $sql = "SELECT username FROM customer_devices  WHERE username='" . $data['username'] . "' AND id !='" . $id . "'";
                $query = $this->db->query($sql);

                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return "Username " . $data['username'] . " already exist. Please use another username; the value is reset with original value.";
                }
            }
            if (isset($data['extension_no'])) {
                $sql = "SELECT extension_no FROM customer_devices  WHERE extension_no='" . $data['extension_no'] . "' AND account_id ='" . $account_id . "' AND id !='" . $id . "'";

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
                $sip_data_array['email_address'] = $data['voicemail_email'];

            $this->db->trans_begin();
            if (count($sip_data_array) > 0) {
                $where = " id ='" . $id . "' AND account_id='" . $account_id . "' ";
                $str = $this->db->update_string('customer_devices', $sip_data_array, $where);

                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customer_devices', 'sql_key' => $where, 'sql_query' => $str);
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

            $sql = "SELECT id, maching_string FROM  customer_dialpattern WHERE account_id='" . $account_id . "' and route = 'OUTBOUND'";
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
        $sql = "SELECT * FROM account  WHERE $key ='" . $value . "'";
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
                $result = $this->db->delete('customer_devices', array('account_id' => $account_id, 'id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_devices', 'sql_key' => $id, 'sql_query' => $this->db->last_query());
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

   
    function generate_key_del($table = 'account') {
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

    /* generate unique key */

    function generate_key($name, $prefix1, $table, $unique_field_name) {
        $prefix2 = '';
        $key = '';
        //generate_key($name, ''); //generate unique key

        $sql = "SELECT MAX(customer_id) as table_key FROM " . $table . " ";
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)) {
            $max_key = $row->table_key;
            $new_key_int = $max_key + 1;
            $rand = '';
            while (1) {
                $new_key = $prefix1 . $prefix2 . $key . $new_key_int;
                //$key.rand(100,999);				
                $new_key = sprintf('%-09s', $new_key);

                $sql = "SELECT $unique_field_name
				 FROM " . $table . " 
				 WHERE  $unique_field_name ='" . $new_key . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    $key = generate_key($name, '');
                    $new_key_int = '';
                    $rand = rand(100, 999);
                } else {
                    break;
                }
            }
        } else {
            $new_key = $prefix1 . $prefix2 . $key . rand(100, 999);
            $new_key = sprintf('%-015s', $new_key);
        }
        //echo $new_key.'--'.strlen($new_key);die;
        return $new_key;
    }

    public function get_plugin_data($plugin_name) {
        $sql = "select plugin_system_name from plugins where plugin_system_name='" . $plugin_name . "'";
        $query = $this->db->query($sql);
        return $query->row_array();
    }

    public function get_voip_data($account_id) {
        $sql = "select customer_voipminuts.*,tariff.tariff_name  from customer_voipminuts 
            INNER JOIN tariff ON customer_voipminuts.tariff_id=tariff.tariff_id 
            where 1 and  status = '1' and  customer_voipminuts.account_id='" . $account_id . "' order by id desc limit 1";
        $query = $this->db->query($sql);
        $result = $query->result_array();

        return $result;
    }

  
   

    function get_user_by_account_manager() {
        $logged_account_id = get_logged_account_id();
        $final_return_array = array();
        try {
            $sql = "SELECT user_id,name,account_id FROM users WHERE user_type='ACCOUNTMANAGER' and account_id='$logged_account_id'";
            $query = $this->db->query($sql);
            $final_return_array = $query->result_array();
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_account_manager($customer_account_id) {
        $logged_account_id = get_logged_account_id();
        $final_return_array = array();
        try {
            $sql = "SELECT account_manager FROM account_am WHERE account_id='$logged_account_id' AND customer_account_id='$customer_account_id'
                     ORDER BY created_dt DESC LIMIT 1";
            $query = $this->db->query($sql);
            $final_return_array = $query->row_array();
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    ///////////////////////////////////
    function delete_randomcli($account_id, $id_array) {
        try {
            $log_data_array = array();
           // echo $account_id;          print_r($id_array);die;
          
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('randomcli_cust', array('id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'randomcli', 'sql_key' => $id, 'sql_query' => $this->db->last_query());
                if ($this->db->affected_rows() == 0)
                    throw new Exception('RandomCLI not found');
            }

                //set_activity_log($log_data_array);
                return true;
            
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    function update_randomcli($data) {
        try {
            $log_data_array = array();

            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'Account missing';
            if (isset($data['randomcli_id']))
                $id = $data['randomcli_id'];
            else
                return 'CLI ID missing';

            $ip_data_array = array();
            if (isset($data['cli_status']))
                $ip_data_array['cli_status'] = $data['cli_status'];
            if (isset($data['cli_length']))
                $ip_data_array['cli_length'] = $data['cli_length'];
            if (isset($data['cli_fixprefix']))
                $ip_data_array['cli_fixprefix'] = $data['cli_fixprefix'];
            if (isset($data['destination_prefix']))
                $ip_data_array['destination_prefix'] = $data['destination_prefix'];
            if (isset($data['clirule_name']))
                $ip_data_array['rule_name'] = $data['clirule_name'];

          
            if (count($ip_data_array) > 0) {
                $ip_data_array['updated_by'] = get_logged_account_id();
                $ip_data_array['update_dt'] = date('Y-m-d h:i:s');


                $where = " id='" . $id . "' AND account_id='" . $account_id . "' ";
               // print_r($ip_data_array);die;
                $str = $this->db->update_string('randomcli_cust', $ip_data_array, $where);
                $result = $this->db->query($str);

                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'randomcli', 'sql_key' => $where, 'sql_query' => $str);
            }

            
               // set_activity_log($log_data_array);
            
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function add_randomcli($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'Account missing';
            }
            $rule_type = $data['rule_type'];
            $randomcli_data_array = array();
            $randomcli_data_array['account_id'] = $data['account_id'];
            
            $randomcli_data_array['cli_status'] = $data['cli_status'];
            if($rule_type==1)
            {            
                $randomcli_data_array['destination_prefix'] = $data['destination_prefix'];
                $randomcli_data_array['cli_fixprefix'] = $data['cli_fixprefix'];
                $randomcli_data_array['cli_length'] = $data['cli_length'];       
                $randomcli_data_array['rule_type'] = 1;      
            }
            else
            {                
                $randomcli_data_array['destination_prefix'] = $data['destination_prefix'];
                $randomcli_data_array['cli_fixprefix'] = $data['cli_fixprefix'];
                $randomcli_data_array['cli_length'] = 0;
                $randomcli_data_array['rule_type'] = 2;
            }
            $timestamp = time();

            if(isset($data['clirule_name']))
            $randomcli_data_array['rule_name'] = $data['clirule_name'];
            else
            $randomcli_data_array['rule_name'] =$timestamp;

            $randomcli_data_array['created_by'] = get_logged_account_id();
            $randomcli_data_array['created_dt'] = date('Y-m-d h:i:s');
           
            
            $randomcli_id = $randomcli_data_array['clirule_name'];
            $randomcli_id = preg_replace('/[^a-z\d]/i', '', $randomcli_id);
            $randomcli_id = substr($randomcli_id, 0, 19);
            $randomcli_id = strtoupper($randomcli_id);
            $randomcli_id = $randomcli_id . $timestamp;
            $randomcli_data_array['rule_id'] = $randomcli_id;
            //print_r($randomcli_data_array);die;
          
            $str = $this->db->insert_string('randomcli_cust', $randomcli_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $insert_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'randomcli', 'sql_key' => '', 'sql_query' => $str);
            
                $this->randomcli_id= $insert_id;
               
                //set_activity_log($log_data_array);
            

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    function add_randomcli_bulk($data, $csv_data)
    {

        try 
        {

            $account_id = $data['account_id'];
            $rule_name = $data['clirule_name'];
            $cli_status = 1;
            $rule_type = 2;
            $timestamp = time();
            $rule_id = $timestamp;
            $created_by = get_logged_account_id();
            $created_dt = date('Y-m-d h:i:s');

         
            if(isset($data['delete_existing']) && $data['delete_existing']=='1')
            {
                $sql ="DELETE FROM randomcli_cust WHERE account_id='$account_id'";
                //die($sql);
                $result = $this->db->query($sql);
            }
            


            $error_msg='';
            $sql_insert = 'INSERT INTO randomcli_cust (rule_id, rule_name, account_id, destination_prefix, cli_fixprefix, cli_length, cli_status, rule_type, 
            created_by,created_dt) VALUES ';
            $sql_values = '';

            for ($i = 1; $i < count($csv_data); $i++) {
                $inclusive_channel = $csv_data[$i][0];
                $exclusive_per_channel_rental = $csv_data[$i][1];
                $rule_id = $timestamp.$i;

                $sql_values .= "('" . $rule_id . "', '" . $rule_name . "', '" . $account_id . "', " . $csv_data[$i][0] . ", " . $csv_data[$i][1] . ", '0', '1', '2', 
                '" . $created_by . "', '" .$created_dt . "'),";

                if (($i % 400) == 399 || $i == count($csv_data) - 1) {
                    $sql = $sql_insert . rtrim($sql_values, ',');
                    //echo $sql;die;
                    $result = $this->db->query($sql);
                    
                    //var_dump($result);die();
                    $sql_values = '';
                    if ($result) {
                        
                    } else {
                        $success = false;
                        $e = $this->db->error();
                        $error_msg .= $e['message'];
                    }
                }

            }

            if ($error_msg=='') {
                return true;
            }
            else
            {
                return $error_msg;
            }

        
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    
    function save_cli_dst_rule($data) 
    {
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

            $sql = "SELECT account_id FROM account_cli_dst_rules  WHERE account_id='" . $account_id. "'";
            $query = $this->db->query($sql);
            $row = $query->row();
            if ($row == NULL) {
            $action='add';                
            } else {
                $action='update';
            }

            $ip_data_array = array();
            
            $ip_data_array['pstn_cli_usage_option'] = $data['pstn_cli_usage_option'];
            $ip_data_array['pstn_max_calls_per_cli_in_aday'] = $data['pstn_max_calls_per_cli_in_aday'];
            $ip_data_array['pstn_max_call_per_cli_live'] = $data['pstn_max_call_per_cli_live'];
         
            $ip_data_array['pstn_max_cli_length'] = $data['pstn_max_cli_length'];
            $ip_data_array['pstn_min_cli_length'] = $data['pstn_min_cli_length'];

            $ip_data_array['pstn_cli_malfunction'] = $data['pstn_cli_malfunction'];
            $ip_data_array['pstn_min_dst_number_length_option'] = $data['pstn_min_dst_number_length_option'];
            $ip_data_array['pstn_min_dst_number_length'] = $data['pstn_min_dst_number_length'];
            $ip_data_array['pstn_max_dst_number_length'] = $data['pstn_max_dst_number_length'];
            $ip_data_array['did_cli_usage_option'] = $data['did_cli_usage_option'];
            $ip_data_array['did_max_calls_per_cli_in_aday'] = $data['did_max_calls_per_cli_in_aday'];
            $ip_data_array['did_max_call_per_cli_live'] = $data['did_max_call_per_cli_live'];
           
            $ip_data_array['did_max_cli_length'] = $data['did_max_cli_length'];
            $ip_data_array['did_min_cli_length'] = $data['did_min_cli_length'];


            if($action=='add')
            {
                $ip_data_array['account_id'] = $data['account_id'];
                $ip_data_array['created_by'] = $data['created_by'];
                $ip_data_array['created_dt'] = date('Y-m-d h:i:s');

                $str = $this->db->insert_string('account_cli_dst_rules', $ip_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'account_cli_dst_rules', 'sql_key' => '', 'sql_query' => $str);
            }
            else
            {
                $where = " account_id='" . $account_id . "' ";
                $ip_data_array['update_by'] = $data['created_by'];
                $str = $this->db->update_string('account_cli_dst_rules', $ip_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'account_cli_dst_rules', 'sql_key' => $where, 'sql_query' => $str);
            }
        

            set_activity_log($log_data_array);
            

            return true;
        } catch (Exception $e) {

            return $e->getMessage();
        }
    }


}
