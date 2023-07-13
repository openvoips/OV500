<?php

/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */


class Reseller_mod extends CI_Model {

    public $customers_id;
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
        $account_id_array = $tariff_id_name_array = $tariff_id_account_id_mapping_array = $currency_id_account_access_id_name_mapping_array = array();

        try {
            $sql = "SELECT a.*, 
			 r.contact_name, r.company_name, r.address, r.country_id, r.state_code_id, r.phone, r.emailaddress, r.pincode, '' tariff_id			
			FROM account a INNER JOIN resellers r ON a.account_id = r.account_id 
			WHERE a.account_type='RESELLER' ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'parent_account_id')
                        $sql .= " AND a.$key ='" . $value . "' ";
                    elseif ($value != '') {
                        if (in_array($key, array('id', 'account_id', 'parent_account_id', 'status_id', 'account_type', 'account_level', 'currency_id'))) {
                            $sql .= " AND a.$key ='" . $value . "' ";
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
            $tariff_id_array = array();
            foreach ($query->result_array() as $row) {
                $account_id = $row['account_id'];
                // $tariff_id = $row['tariff_id'];
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
                if (isset($option_param['bundle_package']) || isset($option_param['bundle_package_group_by'])) {
                    $row['bundle_package'] = array();
                }
                $final_return_array['result'][$account_id] = $row;
                $account_id_array[] = $account_id;
                //   $tariff_id_array[] = $tariff_id;
                // $tariff_id_account_id_mapping_array[$tariff_id][] = $account_id;
                $currency_id_account_id_mapping_array[$currency_id][] = $account_id;
            }



            if (count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT tariff_id, account_id FROM customer_voipminuts WHERE account_id IN($account_id_str)";
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $tariff_id = $row['tariff_id'];
                    $final_return_array['result'][$account_id]['tariff_id'] = $tariff_id;
                    ////////////
                    $tariff_id_array[] = $tariff_id;
                    $tariff_id_account_id_mapping_array[$tariff_id][] = $account_id;
                }
            }
            $tariff_id_array = array_unique($tariff_id_array);


            if ((isset($option_param['bundle_package']) || isset($option_param['bundle_package_group_by'])) && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";


                $sql = "SELECT *, (select GROUP_CONCAT(prefix) from bundle_package_prefixes where  bundle_package_prefixes.bundle_package_id = bundle_account.bundle_package_id   and prefix <> '') prefix, bundle_account.id bundle_account_id, count(bundle_account.bundle_package_id) bundle_count FROM bundle_account INNER JOIN bundle_package ON bundle_account.bundle_package_id = bundle_package.bundle_package_id WHERE bundle_account.account_id IN($account_id_str)";
                if (isset($option_param['bundle_package_id'])) {
                    $sql .= " AND id  ='" . $option_param['bundle_package_id'] . "'";
                }//echo $sql;

                if (isset($option_param['bundle_package_group_by'])) {
                    $sql .= " GROUP BY bundle_account.bundle_package_id";
                }

                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];
                    $final_return_array['result'][$account_id]['bundle_package'][] = $row;
                }
            }

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
                $sql = "SELECT * FROM tariff  where  tariff_id in ($tariff_id_str)";

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
            $this->db->trans_begin();
            $log_data_array = array();
            $account_type = 'RESELLER';

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

            $sql = "SELECT  company_name FROM resellers  WHERE  company_name ='" . $data['company_name'] . "'";
            $query = $this->db->query($sql);
            $row = $query->row();
            if (isset($row)) {
                throw new Exception('Company already exists');
            }

            $key = $this->generate_key($data['company_name'], RESELLERCODEPREFIX, 'resellers', 'account_id');
            $user_key = $this->member_mod->generate_key('RESELLERADMIN');


            $user_data_array = $account_data_array = $reseller_data_array = array();
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
//            if (isset($data['cli_check']))
//                $account_data_array['cli_check'] = $data['cli_check'];
//            if (isset($data['dialpattern_check']))
//                $account_data_array['dialpattern_check'] = $data['dialpattern_check'];
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
            $reseller_data_array['account_id'] = $key;
            $reseller_data_array['contact_name'] = $data['contact_name'];
            $reseller_data_array['company_name'] = $data['company_name'];
            $reseller_data_array['address'] = $data['address'];
            $reseller_data_array['country_id'] = $data['country_id'];
            $reseller_data_array['phone'] = $data['phone'];
            $reseller_data_array['emailaddress'] = $data['emailaddress'];
            $reseller_data_array['state_code_id'] = $data['state_code_id'];
            $reseller_data_array['pincode'] = $data['pincode'];
            ///////////
            $user_data_array['account_id'] = $key;
            $user_data_array['user_id'] = $user_key;
            $user_data_array['user_type'] = 'RESELLERADMIN';
            $user_data_array['username'] = $data['username'];
            $user_data_array['name'] = $data['company_name'];
            $user_data_array['secret'] = $data['secret'];
            $user_data_array['emailaddress'] = $data['user_emailaddress'];
            $user_data_array['status_id'] = '1';


            if (count($account_data_array) > 0) {
                $str = $this->db->insert_string('account', $account_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->account_id = $key;
            }

            if (count($reseller_data_array) > 0) {
                $str = $this->db->insert_string('resellers', $reseller_data_array);
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



            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
				
				////////
				$sdr_data_array=array();
				$sdr_data_array['ACCOUNTID'] = $key;
				$sdr_data_array['REQUEST'] = 'OPENINGBALANCE';
				$sdr_data_array['SERVICENUMBER'] = '';
				$sdr_data_array['CREATEDBY'] = $key;
				$api_response = call_billing_api($sdr_data_array);
				$api_result = json_decode($api_response, true);			
				///////
				
				
                set_activity_log($log_data_array);
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
        try {
            $this->db->trans_begin();
            $log_data_array = array();
            $api_log_data_array = array();

            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                throw new Exception('id missing');
            $account_type = 'RESELLER';

            $account_data_array = $reseller_data_array = array();
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

//            if (isset($data['cli_check']))
//                $account_data_array['cli_check'] = $data['cli_check'];
//            if (isset($data['dialpattern_check']))
//                $account_data_array['dialpattern_check'] = $data['dialpattern_check'];
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
            /* if (isset($data['billing_cycle']))
              $account_data_array['billing_cycle'] = $data['billing_cycle']; */
            /* if (isset($data['payment_terms']))
              $account_data_array['payment_terms'] = $data['payment_terms']; */
            ////////////////
            if (isset($data['contact_name']))
                $reseller_data_array['contact_name'] = $data['contact_name'];
            if (isset($data['company_name']))
                $reseller_data_array['company_name'] = $data['company_name'];
            if (isset($data['address']))
                $reseller_data_array['address'] = $data['address'];
            if (isset($data['country_id']))
                $reseller_data_array['country_id'] = $data['country_id'];
            if (isset($data['phone']))
                $reseller_data_array['phone'] = $data['phone'];
            if (isset($data['emailaddress']))
                $reseller_data_array['emailaddress'] = $data['emailaddress'];
            if (isset($data['state_code_id']))
                $reseller_data_array['state_code_id'] = $data['state_code_id'];
            if (isset($data['pincode']))
                $reseller_data_array['pincode'] = $data['pincode'];


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
                $str1 = $this->db->insert_string('account_am', $account_manager_data_array). ' ON DUPLICATE KEY UPDATE account_manager=values(account_manager)';
                $result1 = $this->db->query($str1);
                if (!$result1) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }

            if (count($account_data_array) > 0) {
                $where = "account_id='" . $account_id . "' AND account_type='" . $account_type . "'";
                $str = $this->db->update_string('account', $account_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'account', 'sql_key' => $where, 'sql_query' => $str);
            }

            if (count($reseller_data_array) > 0) {
                $where = "account_id='" . $account_id . "'";
                $str = $this->db->update_string('resellers', $reseller_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                // $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customers', 'sql_key' => $where, 'sql_query' => $str);
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

                $sql = "SELECT * FROM resellers WHERE  account_id='" . $id . "' ";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete('resellers', array('account_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customers', 'sql_key' => $id, 'sql_query' => $data_dump);
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

    /* generate unique key */

    function generate_key($name, $prefix1, $table, $unique_field_name) {
        $prefix2 = '';
        $key = '';
        //generate_key($name, ''); //generate unique key

        $sql = "SELECT MAX(id) as table_key FROM " . $table . " ";
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

    function add_bundle($data) {
        try {
            $this->db->trans_begin();
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                throw new Exception('User missing');
            }

            $sip_data_array = array();
            $sip_data_array['account_id'] = $data['account_id'];
            $sip_data_array['bundle_package_id'] = $data['bundle_package_id'];
            $sip_data_array['assign_dt'] = date('Y-m-d H:i:s');
            $sip_data_array['bundle_package_desc'] = $data['bundle_package_desc'];

            while (1) {
                $sip_data_array['account_bundle_key'] = strtoupper('RB' . generateRandom(8));
                $sql = "SELECT  account_bundle_key FROM bundle_account WHERE account_bundle_key ='" . $sip_data_array['account_bundle_key'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    
                } else {
                    break;
                }
            }



            $str = $this->db->insert_string('bundle_account', $sip_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            ////////////////////
            $api_request['account_id'] = $account_id;
            $api_request['account_type'] = 'RESELLER';
            $api_request['service_number'] = $sip_data_array['bundle_package_id'];
            $api_request['request'] = 'BUNDLECHARGES';

            $api_response = callSdrAPI($api_request);
            $api_result = json_decode($api_response, true);

            $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_request['request'], 'sql_key' => $api_request['account_id'], 'sql_query' => print_r($api_request, true));

            if (!isset($api_result['error']) || $api_result['error'] == '1') {
                throw new Exception('SDR Problem:(' . $api_request['account_id'] . ')' . $api_result['message']);
            }

            // $this->last_customer_sip_id = $this->db->insert_id();
            // $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'customer_sip_account', 'sql_key' => '', 'sql_query' => $str);

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            } else {
                $this->db->trans_commit();
                //set_activity_log($log_data_array);
                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function delete_bundle($account_id, $id_array) {
        try {
            $log_data_array = array();
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('bundle_account', array('account_id' => $account_id, 'id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'bundle_account', 'sql_key' => $id, 'sql_query' => $this->db->last_query());
                if ($this->db->affected_rows() == 0)
                    throw new Exception('Bundle not found');
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

}
