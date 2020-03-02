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

class Member_mod extends CI_Model {

    public $id;
    public $user_access_id = '';
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {
            $user_type_array = get_account_types(1);
            $user_types = array_keys($user_type_array);
            $user_type_str = implode("','", $user_types);

            $sql = "SELECT SQL_CALC_FOUND_ROWS account_status, ua.customer_id, ua.account_id, ua.name, ua.address, ua.country_id, ua.phone, ua.emailaddress, web_access.username, web_access.secret, ua.account_type FROM customers ua  INNER JOIN web_access on web_access.customer_id = ua.customer_id  WHERE ua.account_type IN ('" . $user_type_str . "') ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'customer_id' || $key == 'account_id')
                            $sql .= " AND $key ='" . $value . "' ";

                        elseif ($key == 'account_type') {
                            if (is_array($filter_data[$key])) {
                                if (count($filter_data[$key]) > 0) {
                                    $user_type_str = implode("','", $filter_data[$key]);
                                    $sql .= " AND $key IN ('" . $user_type_str . "') ";
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
                $access_id = $row['account_id'];
                $final_return_array['result'][$access_id] = $row;
            }
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Users fetched successfully';
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
            if (isset($data['account_type']))
                $account_type = strtoupper($data['account_type']);
            else
                return 'Account type missing';
            $account_types_array = get_account_types();
            if (isset($account_types_array[1][$account_type])) {
                $account_group_key = 1;
            } elseif (isset($account_types_array[2][$account_type])) {
                $account_group_key = 2;
            } else
                return 'unsupported account type';

            if ($account_group_key == 1) {
                $sql = "SELECT username FROM web_access WHERE  username ='" . $data['username'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    if ($row->username == $data['username'])
                        return 'Username already exists';
                }
                $key = $this->generate_key($account_type);
                $account_acces_access_array = $account_access_array = array();
                $account_access_array['account_id'] = $key;
                $account_access_array['account_type'] = $account_type;
                if (isset($data['name']))
                    $account_access_array['name'] = $data['name'];
                if (isset($data['address']))
                    $account_access_array['address'] = $data['address'];
                if (isset($data['country_id']))
                    $account_access_array['country_id'] = $data['country_id'];
                if (isset($data['phone']))
                    $account_access_array['phone'] = $data['phone'];
                if (isset($data['emailaddress']))
                    $account_access_array['emailaddress'] = $data['emailaddress'];

                $account_acces_access_array['username'] = $data['username'];
                $account_acces_access_array['secret'] = $data['secret'];
                $this->db->trans_begin();
                if (count($account_access_array) > 0) {
                    $str = $this->db->insert_string('customers', $account_access_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customers', 'sql_key' => '', 'sql_query' => $str);
                }
                if (count($account_acces_access_array) > 0) {
                    $sql = "SELECT customer_id FROM customers  WHERE account_id ='" . $key . "' limit 1;";
                    $query = $this->db->query($sql);
                    foreach ($query->result_array() as $row) {
                        $customer_id = $row['customer_id'];
                    }
                    $account_acces_access_array['customer_id'] = $customer_id;

                    $str = $this->db->insert_string('web_access', $account_acces_access_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'customers', 'sql_key' => '', 'sql_query' => $str);
                }
                if ($this->db->trans_status() === FALSE) {
                    $error_array = $this->db->error();
                    $this->db->trans_rollback();
                    return $error_array['message'];
                } else {
                    $this->db->trans_commit();
                    set_activity_log($log_data_array);
                }
            }

            $this->account_id = $key;
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function update($data) {
        try {
            $log_data_array = array();
            if (isset($data['existing_user_type']))
                $user_type = strtoupper($data['existing_user_type']);
            else
                return 'type missing';
            $user_types_array = get_account_types();
            if (isset($user_types_array[1][$user_type])) {
                $user_group_key = 1;
                if (!isset($data['key']))
                    return 'key missing';
            }
            elseif (isset($user_types_array[2][$user_type])) {
                $user_group_key = 2;
            } else
                return 'unsupported user type';
            if ($user_group_key == 1) {
                $key = $data['key'];
                if ($data['customer_id'] != '') {
                    $sql = "SELECT customer_id FROM customers WHERE account_id !='" . $key . "'";
                    $query = $this->db->query($sql);
                    foreach ($query->result_array() as $row) {
                        $customer_id = $row['customer_id'];
                    }
                    if ($customer_id > 0) {
                        
                    } else {
                        return 'Account Code is not exists';
                    }
                }

                $web_access_data_array = $account_access_array = array();

                if (isset($data['name']))
                    $account_access_array['name'] = $data['name'];
                if (isset($data['address']))
                    $account_access_array['address'] = $data['address'];
                if (isset($data['country_id']))
                    $account_access_array['country_id'] = $data['country_id'];
                if (isset($data['phone']))
                    $account_access_array['phone'] = $data['phone'];
                if (isset($data['emailaddress']))
                    $account_access_array['emailaddress'] = $data['emailaddress'];
                if (isset($data['account_type']))
                    $account_access_array['account_type'] = $data['account_type'];
                if (isset($data['username']))
                    $web_access_data_array['username'] = $data['username'];

                if (isset($data['secret']) && $data['secret'] != '')
                    $web_access_data_array['secret'] = $data['secret'];

                if (isset($data['account_status']) && $data['account_status'] != '') {
                    $account_access_array['account_status'] = $data['account_status'];
                }

                $this->db->trans_begin();
                if (count($account_access_array) > 0) {
                    $where = "account_id='" . $key . "' AND account_type='" . $user_type . "'";
                    $str = $this->db->update_string('customers', $account_access_array, $where);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customers', 'sql_key' => $where, 'sql_query' => $str);
                }
                if (count($web_access_data_array) > 0) {
                    $sql = "SELECT customer_id FROM customers WHERE account_id ='" . $key . "'";
                    $query = $this->db->query($sql);
                    foreach ($query->result_array() as $row) {
                        $customer_id = $row['customer_id'];
                    }
                    $where = " customer_id='" . $customer_id . "'";
                    $str = $this->db->update_string('web_access', $web_access_data_array, $where);
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
                    $this->db->trans_commit();
                    set_activity_log($log_data_array);
                }
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

    function get_account_by_key($field, $value, $option_param = array()) {
        $sql = "SELECT	ua.customer_id, ua.account_id, ua.name, ua.account_type, ua.emailaddress FROM customers ua WHERE  `" . $field . "` ='" . $value . "' LIMIT 0,1";
        $query = $this->db->query($sql);
        if ($query == null)
            return false;
        $num_rows = $query->num_rows();

        if ($num_rows < 1) {
            return false;
        }
        $row = $query->row();
        $account_type = $row->account_type;
        $account_id = $row->account_id;
        $final_array = array();
        $final_array['account_type'] = $account_type;
        $final_array['account_id'] = $account_id;
        $order_by = '';
        $per_page = 1;
        $segment = 0;
        if (in_array($account_type, array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
            $search_data = array('account_id' => $account_id);
            $data_array = $this->get_data($order_by, $per_page, $segment, $search_data);
            if (isset($data_array['result']))
                $final_array = current($data_array['result']);
        }
        elseif (in_array($account_type, array('CUSTOMER'))) {
            $this->load->model('Customer_mod', 'customer_mod');

            $search_data = array('account_id' => $account_id);
            $data_array = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($data_array['result']))
                $final_array = current($data_array['result']);
        } elseif (in_array($account_type, array('RESELLER'))) {
            $this->load->model('Reseller_mod', 'reseller_mod');
            $search_data = array('account_id' => $account_id);
            $data_array = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($data_array['result']))
                $final_array = current($data_array['result']);
        }

        if (isset($option_param['payment_history']) && $option_param['payment_history'] == true) {
            $this->load->model('Payment_mod', 'payment_mod');
            $final_array['payment_history'] = array();
            $search_data = array('account_id' => $account_id);
            $data_array = $this->payment_mod->get_data($order_by, '', $segment, $search_data);
            if (isset($data_array['result']))
                $final_array['payment_history'] = $data_array['result'];
        }
        if (isset($option_param['balance']) && $option_param['balance'] == true) {
            $this->load->model('Payment_mod', 'payment_mod');
            $final_array['balance'] = array();
            $data_array = $this->payment_mod->get_balance($account_id);
            if (isset($data_array['result']))
                $final_array['balance'] = $data_array['result'];
        }

        if (isset($option_param['permission']) && $option_param['permission'] == true) {

            $this->load->model('Login_mod', 'login_mod');
            $final_array['permission'] = array();
            $permission_str = $this->login_mod->get_account_permission($account_id, $account_type);
            if ($permission_str != '')
                $final_array['permission'] = unserialize($permission_str);
        }


        return $final_array;
    }

    function update_profile($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id']))
                $account_id = strtoupper($data['account_id']);
            else
                return 'account id missing';
            $sql = "SELECT ua.customer_id, ua.account_id, ua.name, ua.account_type user_type, ua.emailaddress FROM  customers ua  WHERE  account_id ='" . $account_id . "' LIMIT 0,1";
            $query = $this->db->query($sql);
            if ($query == null)
                return false;
            $num_rows = $query->num_rows();

            if ($num_rows < 1) {
                return false;
            }

            $row = $query->row();
            $account_type = $row->account_type;
            $account_id = $row->account_id;
            $final_array = array();

            if ($data['emailaddress'] != '') {
                $sql = "SELECT emailaddress, customer_id FROM  customers WHERE account_id !='" . $account_id . "' AND emailaddress ='" . $data['emailaddress'] . "';";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    if (strlen($row['emailaddress']) > 0)
                        return 'Email address already exists';
                }
            }
            $account_detail_array = array();
            if (isset($data['name']) && $data['name'] != '')
                $account_detail_array['name'] = $data['name'];
            if (isset($data['address']))
                $account_detail_array['address'] = $data['address'];
            if (isset($data['country_id']))
                $account_detail_array['country_id'] = $data['country_id'];
            if (isset($data['phone']))
                $account_detail_array['phone'] = $data['phone'];
            if (isset($data['emailaddress']) && $data['emailaddress'] != '')
                $account_detail_array['emailaddress'] = $data['emailaddress'];
            if (isset($data['secret']) && $data['secret'] != '')
                $account_access_array['secret'] = $data['secret'];
            $this->db->trans_begin();
            if (count($account_detail_array) > 0) {
                $where = "account_id='" . $account_id . "'";
                $str = $this->db->update_string('customers', $account_detail_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'customers', 'sql_key' => $where, 'sql_query' => $str);
            }
            if (count($account_access_array) > 0) {
                $where = "customer_id = (select customer_id from customers where account_id='" . $account_id . "')";
                $str = $this->db->update_string('web_access', $account_access_array, $where);
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
                $this->db->trans_commit();
                set_activity_log($log_data_array);
            }

            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function generate_key($account_type = '') {
        $prefix1 = 'OS';
        $prefix2 = '';
        if ($account_type != '')
            $prefix2 = $account_type[0];
        $sql = "SELECT MAX(customer_id) as table_key FROM customers ";
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)) {
            $max_key = $row->table_key;
            $new_key_int = $max_key;
            while (1) {
                $new_key_int = $new_key_int + 1;
                $new_key_int_zero_fill = sprintf('%06d', $new_key_int);

                $new_key = $prefix1 . $prefix2 . $new_key_int_zero_fill . rand(100, 999);

                $sql = "SELECT customer_id FROM customers WHERE  account_id ='" . $new_key . "'";
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

    function delete($id_array) {
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();
                $user_type = 'ADMIN';

                $sql = "SELECT * FROM customers WHERE  account_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $user_type = $row['account_type'];
                    $customer_id = $row['customer_id'];
                    $result = $this->db->delete('web_access', array('customer_id' => $customer_id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $data_dump = serialize($row);
                    $result = $this->db->delete('customers', array('account_id' => $id));
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
                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => $user_type, 'sql_key' => $id, 'sql_query' => '');
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

    function check_permission($module) {
        $session_current_user_id = $this->session->userdata('session_current_customer_id');
        if ($session_current_user_id == '') {
            redirect(base_url() . 'login', 'refresh');
        }

        $session_user_type = $_SESSION['customer'][$session_current_user_id]['session_account_type'];
        if ($session_user_type == '') {
            redirect(base_url() . 'login', 'refresh');
        }
    }

}
