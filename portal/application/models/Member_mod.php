<?php
/* 
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026
 * http://www.openvoips.com 
 */
class Member_mod extends CI_Model {

    public $id;
    public $user_access_id = '';
    public $select_sql;
    public $total_count_sql;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array('result' => array());
        try {
            $sql = "SELECT  users.* FROM users WHERE 1 ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if (in_array($key, array('user_id', 'id', 'account_id', 'status_id', 'user_type')))
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'user_type') {
                            if (is_array($filter_data[$key])) {
                                if (count($filter_data[$key]) > 0) {
                                    $user_type_str = implode("','", $filter_data[$key]);
                                    $sql .= " AND $key IN ('" . $user_type_str . "') ";
                                }
                            } else
                                $sql .= " AND $key ='" . $value . "' ";
                        } elseif ($key == 'user_type_group') {
                            if (is_array($filter_data[$key])) {
                                if (count($filter_data[$key]) > 0) {
                                    $user_type_str = implode("','", $filter_data[$key]);
                                    $sql .= " AND user_type IN ('" . $user_type_str . "') ";
                                }
                            } else
                                $sql .= " AND user_type ='" . $value . "' ";
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
            $this->select_sql = $sql;
            foreach ($query->result_array() as $row) {
                $user_id_name = $row['user_id'];
                $final_return_array['result'][$user_id_name] = $row;
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
            if (!isset($data['user_type']))
                return 'type missing';
            if (!isset($data['username']))
                return 'username missing';
            if (!isset($data['secret']))
                return 'password missing';
            if (!isset($data['status_id']))
                return 'status missing';
            if (!isset($data['account_id']))
                return 'account_id missing';

            $account_id = $data['account_id'];
            $user_type = strtoupper($data['user_type']);
            $user_types_array = get_user_types();

            if (isset($user_types_array[1][$user_type])) {
                $user_group_key = 1;
            } elseif (isset($user_types_array[2][$user_type])) {
                $user_group_key = 2;
            } elseif (isset($user_types_array[3][$user_type])) {
                $user_group_key = 3;
                return 'Unsupported user type';
            } else
                return 'Unsupported user type'; {

                $key = $this->generate_key($user_type);
                $web_access_data_array = array();
                $web_access_data_array['user_id'] = $key;
                $web_access_data_array['account_id'] = $account_id;
                $web_access_data_array['username'] = $data['username'];
                $web_access_data_array['secret'] = $data['secret'];
                $web_access_data_array['user_type'] = $data['user_type'];
                $web_access_data_array['name'] = $data['user_fullname'];
                $web_access_data_array['emailaddress'] = $data['user_emailaddress'];
                $web_access_data_array['address'] = $data['user_address'];
                $web_access_data_array['phone'] = $data['user_phone'];
                $web_access_data_array['country_id'] = $data['user_country_id'];
                $web_access_data_array['status_id'] = $data['status_id'];
                $web_access_data_array['create_by'] = get_logged_user_id();
                $sql = "SELECT  username, emailaddress  FROM users WHERE  emailaddress='" . $web_access_data_array['emailaddress'] . "' OR  username='" . $web_access_data_array['username'] . "' ";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    if ($row->username == $web_access_data_array['username'])
                        return 'Username already exists';
                    elseif ($row->emailaddress == $web_access_data_array['emailaddress'])
                        return 'Email ID already exists';
                }

                $this->db->trans_begin();
                if (count($web_access_data_array) > 0) {

                    $str = $this->db->insert_string('users', $web_access_data_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $this->user_id_name = $key;
                }
                if ($this->db->trans_status() === FALSE) {
                    $error_array = $this->db->error();
                    $this->db->trans_rollback();
                    return $error_array['message'];
                } else {
                    $this->db->trans_commit();
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
            if (isset($data['user_id_name']))
                $user_id_name = strtoupper($data['user_id_name']);
            else
                return 'ID missing'; {
                $web_access_data_array = array();
                if (isset($data['username']))
                    $web_access_data_array['username'] = $data['username'];
                if (isset($data['secret']) && $data['secret'] != '')
                    $web_access_data_array['secret'] = $data['secret'];
                if (isset($data['user_type']) && $data['user_type'] != '')
                    $web_access_data_array['user_type'] = $data['user_type'];
                if (isset($data['user_fullname']) && $data['user_fullname'] != '')
                    $web_access_data_array['name'] = $data['user_fullname'];
                if (isset($data['user_emailaddress']) && $data['user_emailaddress'] != '')
                    $web_access_data_array['emailaddress'] = $data['user_emailaddress'];
                if (isset($data['user_address']) && $data['user_address'] != '')
                    $web_access_data_array['address'] = $data['user_address'];
                if (isset($data['user_phone']) && $data['user_phone'] != '')
                    $web_access_data_array['phone'] = $data['user_phone'];
                if (isset($data['user_country_id']) && $data['user_country_id'] != '')
                    $web_access_data_array['country_id'] = $data['user_country_id'];
                if (isset($data['status_id']) && $data['status_id'] != '')
                    $web_access_data_array['status_id'] = $data['status_id'];
                $web_access_data_array['update_dt'] = date('');
                $web_access_data_array['update_by'] = get_logged_user_id();
                if (isset($web_access_data_array['username']) || isset($web_access_data_array['emailaddress'])) {
                    $sql = "SELECT  username, emailaddress  FROM users WHERE 1 ";
                    if (isset($web_access_data_array['username'])) {
                        $sql .= " username='" . $web_access_data_array['username'] . "'";
                    }
                    $sql .= " AND user_id !='" . $user_id_name . "'";
                    $query = $this->db->query($sql);
                    $row = $query->row();
                    if (isset($row)) {
                        if ($row->username == $web_access_data_array['username'])
                            return 'Username already exists';
                        elseif ($row->emailaddress == $web_access_data_array['emailaddress'])
                            return 'Email ID already exists';
                    }
                }
				
				
				$sql = "SELECT  account_id  FROM users WHERE  user_id='" . $user_id_name. "' ";
                $query = $this->db->query($sql);
                $existing_row = $query->row_array();
                if (!isset($row)) {
					return 'User Not Found';
				}
				
				$customer_data_array=[];
				if (isset($data['timezone']))
				{
					$customer_data_array['timezone'] = $data['timezone'];
				}

                $this->db->trans_begin();
                if (count($web_access_data_array) > 0) {
                    $where = " user_id='" . $user_id_name . "'";
                    $str = $this->db->update_string('users', $web_access_data_array, $where);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'users', 'sql_key' => $where, 'sql_query' => $str);
                }
				
				if (count($customer_data_array) > 0) {
					$where = "account_id='" . $existing_row['account_id'] . "'";
					$str = $this->db->update_string('customers', $customer_data_array, $where);
					
					//echo $str;die;
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

    function modify_account_login($data) {
        try {
            $this->db->trans_begin();
            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                throw new Exception('Account Id Missing');

            if (isset($data['user_type']))
                $user_type = $data['user_type'];
            else
                throw new Exception('User Type Missing');

            if (!in_array($user_type, array('RESELLERADMIN', 'CUSTOMERADMIN')))
                throw new Exception('User Type Mismatch');
            $web_access_data_array = array();
            if (isset($data['username']))
                $web_access_data_array['username'] = $data['username'];
            if (isset($data['secret']) && $data['secret'] != '')
                $web_access_data_array['secret'] = $data['secret'];
            if (isset($data['name']) && $data['name'] != '')
                $web_access_data_array['name'] = $data['name'];
            if (isset($data['emailaddress']) && $data['emailaddress'] != '')
                $web_access_data_array['emailaddress'] = $data['emailaddress'];
            if (isset($data['address']) && $data['address'] != '')
                $web_access_data_array['address'] = $data['address'];
            if (isset($data['phone']) && $data['phone'] != '')
                $web_access_data_array['phone'] = $data['phone'];
            if (isset($data['country_id']) && $data['country_id'] != '')
                $web_access_data_array['country_id'] = $data['country_id'];
            if (isset($data['status_id']) && $data['status_id'] != '')
                $web_access_data_array['status_id'] = $data['status_id'];
            if (isset($web_access_data_array['username']) || isset($web_access_data_array['emailaddress'])) {
                $sql = "SELECT  username, emailaddress  FROM users WHERE (";
                $where = '';
                if (isset($web_access_data_array['emailaddress'])) {
                    $where .= " emailaddress='" . $web_access_data_array['emailaddress'] . "'";
                }
                if (isset($web_access_data_array['username'])) {
                    if ($where != '')
                        $where .= " OR ";
                    $where .= " username='" . $web_access_data_array['username'] . "'";
                }
                $sql .= $where . ")";
                $sql .= " AND account_id !='" . $account_id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    if ($row->username == $web_access_data_array['username'])
                        throw new Exception('Username already exists');
                    elseif ($row->emailaddress == $web_access_data_array['emailaddress'])
                        throw new Exception('Email ID already exists');
                }
            }

            if (count($web_access_data_array) > 0) {
                $where = "user_type='" . $user_type . "' AND account_id = '" . $account_id . "'";

                $sql = "SELECT user_id
				 FROM users
				 WHERE  $where";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    $user_id = $row->user_id;
                    $where = " user_id='" . $user_id . "'";
                    $str = $this->db->update_string('users', $web_access_data_array, $where);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                } else {
                    $key = $this->generate_key($user_type);
                    $web_access_data_array['user_id'] = $key;
                    $web_access_data_array['user_type'] = $user_type;
                    $web_access_data_array['account_id'] = $account_id;

                    if (!isset($user_data_array['status_id']))
                        $web_access_data_array['status_id'] = 1;

                    $str = $this->db->insert_string('users', $web_access_data_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
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
    }

    function get_user_by_key($field, $value) {

        $sql = "SELECT  users.* FROM users 
			WHERE `" . $field . "` ='" . $value . "' LIMIT 0,1 ";
        $query = $this->db->query($sql);
        if ($query == null)
            return false;

        $row = $query->row_array();
        if (!isset($row))
            return false;

        $final_array = array();
        $final_array = $row;

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
                $str = $this->db->update_string('users', $account_access_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'users', 'sql_key' => $where, 'sql_query' => $str);
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
        $prefix1 = 'U';
        $prefix2 = '';
        if ($account_type != '')
            $prefix2 = $account_type[0];
        $sql = "SELECT MAX(id) as table_key FROM users ";
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)) {
            $max_key = $row->table_key;
            $new_key_int = $max_key;
            while (1) {
                $new_key_int = $new_key_int + 1;
                $new_key_int_zero_fill = sprintf('%06d', $new_key_int);

                $new_key = $prefix1 . $prefix2 . $new_key_int_zero_fill . rand(100, 999);

                $sql = "SELECT id FROM users WHERE  user_id ='" . $new_key . "'";
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

                $sql = "SELECT * FROM users WHERE user_id='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $user_type = $row['account_type'];
                    $customer_id = $row['customer_id'];
                    $result = $this->db->delete('web_access', array('user_id_name' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
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

    
    function get_account_by_key($field, $value, $option_param = array()) {
        $sql = "SELECT account_id, user_id, user_type FROM users 
			WHERE `" . $field . "` ='" . $value . "' LIMIT 0,1 ";
        $query = $this->db->query($sql);
        if ($query == null)
            return false;

        $row = $query->row_array();
        if (!isset($row))
            return false;

        $final_array = array();
        $user_type = $row['user_type'];
        $user_id = $row['user_id'];
        $account_id = $row['account_id'];
        $user_types_group = get_user_types();
        if (isset($user_types_group[1][$user_type])) {
            
        } elseif (isset($user_types_group[2][$user_type])) {
            $this->load->model('reseller_mod', 'reseller_mod');

            $search_data = array('account_id' => $account_id);
            $data_array = $this->reseller_mod->get_data('', 1, 0, $search_data, $option_param);
            if (isset($data_array['result']))
                $final_array = current($data_array['result']);
        } elseif (isset($user_types_group[3][$user_type])) {
            $this->load->model('customer_mod', 'customer_mod');

            $search_data = array('account_id' => $account_id);
            $data_array = $this->customer_mod->get_data('', 1, 0, $search_data, $option_param);
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

        return $final_array;
    }
    
    
   
}
