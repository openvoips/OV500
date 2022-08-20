<?php

/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

class Crsvoip_mod extends CI_Model {

    public $customer_voipminute_id;
    public $total_count;
    public $select_sql;
    public $total_count_sql;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_voip_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $logged_account_id = get_logged_account_id();
        $final_return_array = array();
        try {
            $sql = "SELECT * FROM (SELECT account.account_id, account.parent_account_id, account.account_type , (select tariff_id from customer_voipminuts  where customer_voipminuts.account_id = account.account_id limit 1) tariff_id, (select  tariff_name from tariff where tariff_id = (select tariff_id from customer_voipminuts  where customer_voipminuts.account_id = account.account_id limit 1)) as tariff_name , account.status_id as status,
 if( account.account_type = 'CUSTOMER',( select company_name from customers where customers.account_id =  account.account_id ), 
(select company_name from resellers where resellers.account_id =  account.account_id ) ) 
 as company_name , customer_balance.balance, customer_balance.credit_limit,
sys_currencies.symbol, sys_currencies.name currency_name, account.dp  , users.username as web_username ,

(select username  from customer_sip_account where customer_sip_account.account_id =  account.account_id limit 1) as  sip_user

FROM account 
INNER JOIN sys_currencies on sys_currencies.currency_id = account.currency_id
left JOIN users on users.account_id = account.account_id 
left JOIN customer_balance on customer_balance.account_id = account.account_id
 ) abcd WHERE 1";
            if (check_logged_user_type(array('RESELLERADMIN', 'RESELLER'))) {
                $sql .= " AND parent_account_id = '$logged_account_id' ";
            } else {
                $sql .= " AND parent_account_id=''  ";
            }
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                        if ($key == 'account_id')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'account_type')
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                        elseif ($key == 'company_name')
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                        elseif ($key == 'web_username')
                            $sql .= " AND web_username LIKE '%" . $value . "%' ";
                        elseif ($key == 'sip_user')
                            $sql .= " AND sip_user LIKE '%" . $value . "%' ";
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY account_id desc ";
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
            $final_return_array['result'] = $query->result_array();
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Voip Minuts fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_data_total_count($sql_exists = false) {
        try {

            if ($sql_exists && isset($this->total_count_sql) && $this->total_count_sql != '') {
                $count_sql = trim($this->total_count_sql);
            } else {

                $count_sql = generate_count_total_sql($this->select_sql);
                if (substr($count_sql, 0, 5) == 'error') {
                    throw new \Exception($count_sql);
                }
            }
            $this->total_count_sql = $count_sql;
            $query_count = $this->db->query($count_sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;
            return $this->total_count;
        } catch (\Exception $e) {
            return 0;
        }

        return 0;
    }

    function get_asignvoip_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {

        $logged_account_id = get_logged_account_id();
        $final_return_array = array();
        try {
            $sql = " SELECT * FROM (SELECT account_id, parent_account_id, account_type , 
if( account_type = 'CUSTOMER',( select company_name from customers where customers.account_id =  account.account_id ), 
(select company_name from resellers where resellers.account_id =  account.account_id ) )
 as company_name FROM account WHERE  account_id NOT IN(SELECT account_id FROM customer_voipminuts) ) abcd WHERE 1 ";

            if (check_logged_user_type(array('RESELLERADMIN', 'RESELLER'))) {
                $sql .= " AND parent_account_id = '$logged_account_id' ";
            } else {
                $sql .= " AND parent_account_id=''  ";
            }

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($value != '') {
                        if ($key == 'account_id')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'company_name')
                            $sql .= " AND   $key LIKE '%" . $value . "%'";
                        elseif ($key == 'account_type')
                            $sql .= " AND   $key LIKE '%" . $value . "%'";
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY account_id desc ";
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

            $final_return_array['result'] = $query->result_array();


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Customer List fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
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

    public function add($data) {
        $api_log_data_array = array();
        $logged_account_id = get_logged_account_id();
        $log_data_array = array();
        $data_array = array();

        try {
            if (isset($data['account_id']))
                $data_array['account_id'] = $data['account_id'];
            if (isset($data['tariff_id']))
                $data_array['tariff_id'] = $data['tariff_id'];
            if (isset($data['account_type']))
                $data_array['account_type'] = $data['account_type'];

            $data_array['billingcode'] = $data['billingcode'];

            $data_array['customer_voipminute_id'] = $this->generate_voip_key();
            $data_array['created_by'] = get_logged_user_id();
            $data_array['created_dt'] = date('Y-m-d H:i:s');
            $key = $data_array['account_id'];
            $this->db->trans_begin();
            if (count($data_array) > 0) {
                //ddd($data_array);die;
                $str = $this->db->insert_string('customer_voipminuts', $data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }


                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_voipminuts', 'sql_key' => $this->customer_voipminute_id, 'sql_query' => $str);
            }
            if ($data['account_type'] == 'CUSTOMER') {
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
                    'route' => 'INBOUND',
                    'created_by' => get_logged_user_id(),
                    'created_dt' => date('Y-m-d H:i:s')
                );
                $str = $this->db->insert_string('customer_callerid', $callerid_array_temp) . ' ON DUPLICATE KEY UPDATE maching_string=values(maching_string)';
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
                    'route' => 'OUTBOUND',
                    'created_by' => get_logged_user_id(),
                    'created_dt' => date('Y-m-d H:i:s')
                );
                $str = $this->db->insert_string('customer_callerid', $callerid_array_temp) . ' ON DUPLICATE KEY UPDATE maching_string=values(maching_string)';
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
                    'route' => 'OUTBOUND',
                    'created_by' => get_logged_user_id(),
                    'created_dt' => date('Y-m-d H:i:s')
                );

                $str = $this->db->insert_string('customer_dialpattern', $prefix_array_temp) . ' ON DUPLICATE KEY UPDATE maching_string=values(maching_string)';
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
                    'route' => 'INBOUND',
                    'created_by' => get_logged_user_id(),
                    'created_dt' => date('Y-m-d H:i:s')
                );

                $str = $this->db->insert_string('customer_dialpattern', $prefix_array_temp) . ' ON DUPLICATE KEY UPDATE maching_string=values(maching_string)';
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_dialpattern', 'sql_key' => $key, 'sql_query' => $str);

                $balance_data_array['credit_limit'] = 0;
                $balance_data_array['balance'] = 0;
                $balance_data_array['account_id'] = $key;
                $balance_data_array['maxcredit_limit'] = '0.000000';

                $str = $this->db->insert_string('customer_balance', $balance_data_array) . ' ON DUPLICATE KEY UPDATE account_id=values(account_id)';
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_balance', 'sql_key' => $key, 'sql_query' => $str);
                $api_data['RULETYPE'] = 'OPENINGBALANCE';
                $api_data['ACCOUNTID'] = $key;
                $api_data['QUANTITY'] = 1;
                $api_data['SERVICENUMBER'] = $data['tariff_id'];
                $api_data['SERVICEKEY'] = $bundle_data_array['account_bundle_key'];
                $api_data['REQUEST'] = 'TARIFFCHARGES';
                $api_response = '';

                $api_response = call_billing_api($api_data);
                $api_result = json_decode($api_response, true);
            } elseif ($data['account_type'] == 'RESELLER') {
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
                    'route' => 'INBOUND',
                    'created_by' => get_logged_user_id(),
                    'created_dt' => date('Y-m-d H:i:s')
                );
                $str = $this->db->insert_string('customer_callerid', $callerid_array_temp) . ' ON DUPLICATE KEY UPDATE maching_string=values(maching_string)';
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
                    'route' => 'OUTBOUND',
                    'created_by' => get_logged_user_id(),
                    'created_dt' => date('Y-m-d H:i:s')
                );
                $str = $this->db->insert_string('customer_callerid', $callerid_array_temp) . ' ON DUPLICATE KEY UPDATE maching_string=values(maching_string)';
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

                $str = $this->db->insert_string('customer_dialpattern', $prefix_array_temp) . ' ON DUPLICATE KEY UPDATE maching_string=values(maching_string)';
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
                    'route' => 'INBOUND',
                    'created_by' => get_logged_user_id(),
                    'created_dt' => date('Y-m-d H:i:s')
                );

                $str = $this->db->insert_string('customer_dialpattern', $prefix_array_temp) . ' ON DUPLICATE KEY UPDATE maching_string=values(maching_string)';
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
                $str = $this->db->insert_string('customer_balance', $balance_data_array) . ' ON DUPLICATE KEY UPDATE account_id=values(account_id)';
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customer_balance', 'sql_key' => $key, 'sql_query' => $str);
                $api_data['RULETYPE'] = 'OPENINGBALANCE';
                $api_data['ACCOUNTID'] = $key;
                $api_data['QUANTITY'] = 1;
                $api_data['SERVICENUMBER'] = $data['tariff_id'];
                $api_data['SERVICEKEY'] = $bundle_data_array['account_bundle_key'];
                $api_data['REQUEST'] = 'TARIFFCHARGES';
                $api_response = '';
                $api_response = call_billing_api($api_data);
                $api_result = json_decode($api_response, true);
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
                set_activity_log($log_data_array);

                return array('status' => true, 'id' => $key, 'msg' => 'VOIP Services Added  Successfully in the system.');
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            set_activity_log($api_log_data_array);
            return $e->getMessage();
        }
    }

    function get_tariffs($currency, $user_type, $tariff_type = '', $created_by = '') {
        $sql = "SELECT id, tariff_id, tariff_name, tariff_currency_id, tariff_type FROM tariff t WHERE t.tariff_status='1' and tariff_currency_id = '$currency' ";
        $logged_account_id = get_logged_account_id();
        $sql .= " AND t.account_id= '$logged_account_id'";
        if ($tariff_type != '')
            $sql .= " AND t.tariff_type='" . $tariff_type . "'";

        $sql .= " ORDER BY t.tariff_type, t.tariff_name";

        $query = $this->db->query($sql);
        $rows = $query->result_array();
        return $rows;
    }

    public function edit_voip_data($data) {
        try {

            $log_data_array = array();

            $data_array = array();
            if (isset($data['tariff_id']))
                $data_array['tariff_id'] = $data['tariff_id'];
            if (isset($data['status']))
                $data_array['status'] = $data['status'];
            if (isset($data['billingcode']))
                $data_array['billingcode'] = $data['billingcode'];

            $this->db->trans_begin();
            if (count($data_array) > 0) {
                $where = " account_id='" . $data['account_id'] . "' ";
                $str = $this->db->update_string('customer_voipminuts', $data_array, $where);
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
                if (isset($data['tariff_id'])) {
                    $sdr_data_array = array();
                    $sdr_data_array['ACCOUNTID'] = $data['account_id'];
                    $sdr_data_array['REQUEST'] = 'TARIFFCHARGES';
                    $sdr_data_array['SERVICENUMBER'] = $data['tariff_id'];
                    $sdr_data_array['CREATEDBY'] = $data['account_id'];
                    $api_response = call_billing_api($sdr_data_array);
                    $api_result = json_decode($api_response, true);                   
                }
            }
            return true;
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

            $bundle_data_array = array();
            $bundle_data_array['account_id'] = $data['account_id'];
            $bundle_data_array['bundle_package_id'] = $data['bundle_package_id'];
            $bundle_data_array['assign_dt'] = date('Y-m-d H:i:s');
            $bundle_data_array['bundle_package_desc'] = $data['bundle_package_desc'];

            while (1) {
                $bundle_data_array['account_bundle_key'] = strtoupper('AB' . generateRandom(8));
                $sql = "SELECT  account_bundle_key FROM bundle_account WHERE account_bundle_key ='" . $bundle_data_array['account_bundle_key'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    
                } else {
                    break;
                }
            }
            $str = $this->db->insert_string('bundle_account', $bundle_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $api_data['RULETYPE'] = 'BUNDLECHARGES';
            $api_data['ACCOUNTID'] = $account_id;
            $api_data['QUANTITY'] = 1;
            $api_data['SERVICENUMBER'] = $bundle_data_array['bundle_package_id'];
            $api_data['SERVICEKEY'] = $bundle_data_array['account_bundle_key'];
            $api_data['REQUEST'] = 'BUNDLECHARGES';
            $api_response = '';
            $api_response = call_billing_api($api_data);

            $api_result = json_decode($api_response, true);
            $api_log_data_array[] = array('activity_type' => 'SDRAPI', 'sql_table' => $api_data['REQUEST'], 'sql_key' => $api_data['ACCOUNTID'], 'sql_query' => print_r($api_result, true));

            if (!isset($api_result['error']) || $api_result['error'] == '1') {
                throw new Exception('SDR Problem:(' . $api_data['ACCOUNTID'] . ')' . $api_result['message']);
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            } else {
                $this->message = $this->data['message'];
                $this->db->trans_commit();
                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function get_account_details($account_id, $search_data, $option_param = array()) {
        $sql = "SELECT account_id, account_type FROM account 
			WHERE account_id ='" . $account_id . "' LIMIT 0,1 ";
        $query = $this->db->query($sql);
        if ($query == null)
            return false;

        $row = $query->row_array();
        if (!isset($row))
            return false;

        $final_array = array();
        $account_type = $row['account_type'];

        if (strtolower($account_type) == 'reseller') {
            $this->load->model('reseller_mod', 'reseller_mod');

            $search_data['account_id'] = $account_id;
            $data_array = $this->reseller_mod->get_data('', 1, 0, $search_data, $option_param);
            if (isset($data_array['result']))
                $final_array = current($data_array['result']);
        } elseif (strtolower($account_type) == 'customer') {
            $this->load->model('customer_mod', 'customer_mod');

            $search_data['account_id'] = $account_id;
            $data_array = $this->customer_mod->get_data('', 1, 0, $search_data, $option_param);
            if (isset($data_array['result']))
                $final_array = current($data_array['result']);
        }

        if (isset($option_param['payment_history']) && $option_param['payment_history'] == true) {
            $this->load->model('crspayment_mod', 'crspayment_mod');
            $final_array['payment_history'] = array();
            $search_data = array('account_id' => $account_id);
            $data_array = $this->crspayment_mod->get_data($order_by, '', $segment, $search_data);
            if (isset($data_array['result']))
                $final_array['payment_history'] = $data_array['result'];
        }
        if (isset($option_param['balance']) && $option_param['balance'] == true) {
            $this->load->model('crspayment_mod', 'crspayment_mod');
            $final_array['balance'] = array();
            $data_array = $this->crspayment_mod->get_balance($account_id);
            if (isset($data_array['result']))
                $final_array['balance'] = $data_array['result'];
        }


        if (isset($option_param['voipminuts']) && $option_param['voipminuts'] == true) {
            $sql = "SELECT * FROM customer_voipminuts WHERE account_id='$account_id' ORDER BY id DESC LIMIT 1";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_array['voipminuts'] = $query->row_array();
        }
        if ((isset($option_param['bundle_package']) || isset($option_param['bundle_package_group_by'])) && count($final_array) > 0) {

            $sql = "SELECT bundle_package.bundle_package_name, bundle_account.*, (select GROUP_CONCAT(prefix) from bundle_package_prefixes where  bundle_package_prefixes.bundle_package_id = bundle_account.bundle_package_id  and prefix <> '' ) prefix, bundle_account.id bundle_account_id, count(bundle_account.bundle_package_id) bundle_count FROM bundle_account INNER JOIN bundle_package ON bundle_account.bundle_package_id = bundle_package.bundle_package_id WHERE bundle_account.account_id='$account_id' ";
            if (isset($option_param['bundle_package_id'])) {
                $sql .= " AND id  ='" . $option_param['bundle_package_id'] . "'";
            }

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
                $final_array['bundle_package'][] = $row;
            }
        }     

        return $final_array;
    }

    function generate_voip_key() {
        $table = 'customer_voipminuts';
        $prefix1 = 'CVM';
        $prefix2 = '';
        $sql = "SELECT MAX(id) as table_key FROM " . $table . ";";
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)) {
            $max_key = $row->table_key;
            $new_key_int = $max_key;
            while (1) {
                $new_key_int = $new_key_int + 1;
                $new_key_int_zero_fill = sprintf('%06d', $new_key_int);

                $new_key = $prefix1 . $prefix2 . $new_key_int_zero_fill . rand(100, 999);

                $sql = "SELECT customer_voipminute_id FROM " . $table . " WHERE  customer_voipminute_id ='" . $new_key . "'";
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

    function add_reseller_dialplan($data) {
        try {
            $this->db->trans_begin();
            $log_data_array = array();

            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                throw new Exception('User missing');

            if (isset($data['dialplan_id'])) {
                $sql = "SELECT account_id FROM reseller_dialplan  WHERE dialplan_id='" . $data['dialplan_id'] . "' AND account_id='" . $account_id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    throw new Exception('Dialplan already exists');
                }
            }
            $dialplan_data_array = array();
            $dialplan_data_array['account_id'] = $account_id;
            $dialplan_data_array['dialplan_id'] = $data['dialplan_id'];
            $dialplan_data_array['create_dt'] = date('Y-m-d h:i:s');

            $str = $this->db->insert_string('reseller_dialplan', $dialplan_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
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

    function delete_reseller_dialplan($account_id, $id_array) {
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('reseller_dialplan', array('account_id' => $account_id, 'id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                if ($this->db->affected_rows() == 0)
                    throw new Exception('Dialplan not found');
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

    function add_customer_dialplan($data) {
        try {
            $this->db->trans_begin();
            $log_data_array = array();
            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                throw new Exception('Account ID missing');
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
                    throw new Exception('Route already exists');
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

            $str = $this->db->insert_string('customer_dialplan', $dialplan_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->last_account_dialplan_id = $this->db->insert_id();

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

    function delete_customer_dialplan($account_id, $id_array) {
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('customer_dialplan', array('account_id' => $account_id, 'id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                if ($this->db->affected_rows() == 0)
                    throw new Exception('Dialplan not found');
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

    function delete_bundle($account_id, $id_array) {
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('bundle_account', array('account_id' => $account_id, 'id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                if ($this->db->affected_rows() == 0)
                    throw new Exception('Bundle not found');
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
                $sql = "SELECT account_id FROM customer_ips  WHERE ipaddress='" . $data['ipaddress'] . "' AND dialprefix='" . $data['dialprefix'] . "' AND billingcode='" . $data['billingcode'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return 'This Billing Code, IP & Dial Prefix already exists in system';
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
            $ip_data_array['billingcode'] = $data['billingcode'];

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
            return TRUE;
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
                $sql = "SELECT account_id FROM customer_ips  WHERE ipaddress='" . $data['ipaddress'] . "'  AND dialprefix='" . $data['dialprefix'] . "' AND billingcode='" . $data['billingcode'] . "' AND  id !='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                    
                } else {
                    return 'This Billing Code, IP & Dial Prefix already exists in system';
                }
            }
            $ip_data_array = array();
            if (isset($data['ipaddress']))
                $ip_data_array['ipaddress'] = $data['ipaddress'];
            if (isset($data['description']))
                $ip_data_array['description'] = $data['description'];
            if (isset($data['billingcode']))
                $ip_data_array['billingcode'] = $data['billingcode'];
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
            $updated_id = 0;
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
                $sql = "SELECT extension_no FROM customer_sip_account  WHERE extension_no='" . $data['extension_no'] . "' ";
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
            $sip_data_array['extension_id'] = $this->generate_ext_key();

            if (isset($data['extension_no']))
                $sip_data_array['extension_no'] = $data['extension_no'];
            if (isset($data['voicemail_enabled']))
                $sip_data_array['voicemail_enabled'] = $data['voicemail_enabled'];
            $sip_data_array['voicemail'] = $sip_data_array['extension_id'];
            $this->db->trans_begin();
            $str = $this->db->insert_string('customer_sip_account', $sip_data_array);
            $result = $this->db->query($str);
            $insert_id = $this->db->insert_id();
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'customer_sip_account', 'sql_key' => $extension_id, 'sql_query' => $str);

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
                set_activity_log($log_data_array);
                return array('status' => true, 'id' => $insert_id, 'account_id' => $sip_data_array['account_id'], 'msg' => 'SIP Added  Successfully in the system.');
            }
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
            if (isset($data['voicemail_enabled']))
                $sip_data_array['voicemail_enabled'] = $data['voicemail_enabled'];

            $sip_data_array['voicemail'] = $data['extension_id'];

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
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_sip_account', 'sql_key' => $id, 'sql_query' => $this->db->last_query());
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

    function generate_ext_key() {
        $table = 'customer_sip_account';
        $prefix1 = 'EXT';
        $prefix2 = '';
        $sql = "SELECT MAX(id) as table_key FROM " . $table . ";";
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)) {
            $max_key = $row->table_key;
            $new_key_int = $max_key;
            while (1) {
                $new_key_int = $new_key_int + 1;
                $new_key_int_zero_fill = sprintf('%06d', $new_key_int);

                $new_key = $prefix1 . $prefix2 . $new_key_int_zero_fill . rand(100, 999);

                $sql = "SELECT extension_id FROM " . $table . " WHERE  extension_id ='" . $new_key . "'";
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

    function account_delete($id) {
        try {
            $this->db->trans_begin(); {
                $log_data_array = array();

                $sql = "SELECT * FROM account WHERE account_id='" . $id . "' AND account_type='CUSTOMER'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $result = $this->db->delete('account', array('account_id' => $id, 'account_type' => 'CUSTOMER'));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                } else {
                    throw new Exception('Customer Not Found');
                }
                $result = $this->db->delete('customers', array('account_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $result = $this->db->delete('users', array('account_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $result = $this->db->delete('customer_voipminuts', array('account_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                $result = $this->db->delete('customer_dialpattern', array('account_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                $result = $this->db->delete('customer_callerid', array('account_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                $result = $this->db->delete('customer_dialplan', array('account_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                $result = $this->db->delete('customer_balance', array('account_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                $result = $this->db->delete('customer_sip_account', array('account_id' => $id));
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
                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

}
