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


if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Bundle_mod extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_data($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        try {
            $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
            $sub = $this->subquery->start_subquery('select');
            $sub->select('name')->from('sys_currencies');
            $sub->where('sys_currencies.currency_id = bundle_package.bundle_package_currency_id');
            $this->subquery->end_subquery('currency_name');

            $sub = $this->subquery->start_subquery('select');
            $sub->select('symbol')->from('sys_currencies');
            $sub->where('sys_currencies.currency_id = bundle_package.bundle_package_currency_id');
            $this->subquery->end_subquery('currency_symbol');

            $sub = $this->subquery->start_subquery('select');
            $sub->select('count(*)')->from('bundle_account');
            $sub->where('bundle_package.bundle_package_id = bundle_account.bundle_package_id');
            $this->subquery->end_subquery('user_count');


            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'bundle_package_currency_id' || $key == 'bundle_package_id')
                            $this->db->where($key, $value);
                        elseif (in_array($key, array('logged_account_type', 'logged_current_customer_id', 'logged_account_level'))) {
                            
                        } elseif ($key == 'created_by') {
                            $this->db->where('account_id', get_logged_account_id());
                        } else
                            $this->db->like($key, trim($value), 'after');
                    }
                }
            }


            if (is_string($order_by) && $order_by == '') {
                $this->db->order_by('id', 'DESC');
            } else {
                foreach ($order_by as $k => $v)
                    $this->db->order_by($k, $v);
            }
            $this->db->limit(intval($limit_from), intval($limit_to));
            $q = $this->db->get('bundle_package');

            // echo $this->db->last_query();
            if (!$q) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = $q->result_array();

            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
            $final_return_array["total"] = $query->row()->Count;

            if ($final_return_array['result']) {
                if (strlen($final_return_array['result'][0]['bundle_package_id']) > 0) {
                    $q = $this->db->query("SELECT bundle_package_id, group_concat(prefix) as prefixes,bundle_id FROM bundle_package_prefixes WHERE bundle_package_id = '" . $final_return_array['result'][0]['bundle_package_id'] . "' group by bundle_package_id, bundle_id order by bundle_package_id");
                    //$final_return_array['result'][0]['bundle'] = $q->result_array();
                    foreach ($q->result_array() as $row) {
                        $bundle_id = $row['bundle_id'];
                        $final_return_array['result'][0]['bundle'][$bundle_id] = $row;
                    }
                }
            } else {
                $final_return_array['bundle'] = '';
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Bundle List fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    public function add($data) {
        $log_data_array = array();
        $data_array = array();
        try {
            $this->db->trans_begin();
            $data_array['bundle_package_name'] = $data['bundle_package_name'];
            $data_array['bundle_package_currency_id'] = $data['bundle_package_currency_id'];
            $data_array['bundle_package_description'] = $data['bundle_package_description'];
            $data_array['monthly_charges'] = $data['monthly_charges'];
            $data_array['bundle_package_status'] = $data['bundle_package_status'];
            $data_array['bundle_option'] = $data['bundle_option'];

            $data_array['bundle1_type'] = $data['bundle1_type'];
            $data_array['bundle1_value'] = $data['bundle1_value'];

            $data_array['bundle2_type'] = $data['bundle2_type'];
            $data_array['bundle2_value'] = $data['bundle2_value'];

            $data_array['bundle3_type'] = $data['bundle3_type'];
            $data_array['bundle3_value'] = $data['bundle3_value'];



            if ($data['bundle_option'] == 0) {
                $data['bundle1_prefix'] = $data['bundle2_prefix'] = $data['bundle3_prefix'] = '';
                $data['bundle1_value'] = $data['bundle2_value'] = $data['bundle3_value'] = 0;
                $data['bundle1_type'] = $data['bundle2_type'] = $data['bundle3_type'] = 'MINUTE';
            }


            $sql = "SELECT  bundle_package_id FROM bundle_package WHERE bundle_package_name ='" . $data_array['bundle_package_name'] . "' ";
            $query = $this->db->query($sql);
            $row = $query->row();
            if (isset($row)) {
                throw new Exception('Package Name Already Exists');
            }
            while (1) {
                $data_array['bundle_package_id'] = generate_key($data_array['bundle_package_name'], 'P');
                $sql = "SELECT  bundle_package_id FROM bundle_package WHERE bundle_package_id ='" . $data_array['bundle_package_id'] . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    
                } else {
                    break;
                }
            }

            $data_array['created_by'] = get_logged_user_id();
            $data_array['account_id'] = get_logged_account_id();

            $data_array['update_dt'] = $data_array['create_dt'] = date('Y-m-d H:i:s');
            $str = $this->db->insert_string('bundle_package', $data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            if (isset($data['bundle1_prefix']) && $data['bundle1_prefix'] != '') {
                $bundle1_prefix_array = explode(',', $data['bundle1_prefix']);
                foreach ($bundle1_prefix_array as $prefix) {
                    $maching_string_temp = $prefix;
                    $add_array = array('bundle_package_id' => $data_array['bundle_package_id'], 'bundle_id' => 1, 'prefix' => $maching_string_temp);

                    $str = $this->db->insert_string('bundle_package_prefixes', $add_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                }
            }
            if (isset($data['bundle2_prefix']) && $data['bundle2_prefix'] != '') {
                $bundle2_prefix_array = explode(',', $data['bundle2_prefix']);
                foreach ($bundle2_prefix_array as $prefix) {
                    $maching_string_temp = $prefix;
                    $add_array = array('bundle_package_id' => $data_array['bundle_package_id'], 'bundle_id' => 2, 'prefix' => $maching_string_temp);

                    $str = $this->db->insert_string('bundle_package_prefixes', $add_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                }
            }
            if (isset($data['bundle3_prefix']) && $data['bundle3_prefix'] != '') {
                $bundle3_prefix_array = explode(',', $data['bundle3_prefix']);
                foreach ($bundle3_prefix_array as $prefix) {
                    $maching_string_temp = $prefix;
                    $add_array = array('bundle_package_id' => $data_array['bundle_package_id'], 'bundle_id' => 3, 'prefix' => $maching_string_temp);

                    $str = $this->db->insert_string('bundle_package_prefixes', $add_array);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                }
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            } else {
                $this->db->trans_commit();
                // set_activity_log($log_data_array);
            }

            $this->bundle_package_id = $data_array['bundle_package_id'];
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            // set_activity_log($api_log_data_array); //api log
            return $e->getMessage();
        }
    }

    public function update($data) {
        $log_data_array = array();
        $data_array = array();
        try {
            $this->db->trans_begin();

            if (!isset($data['bundle_package_id']) || $data['bundle_package_id'] == '')
                throw new Exception('ID missing');
            $bundle_package_id = $data['bundle_package_id'];

            if (isset($data['bundle_package_name'])) {
                $data_array['bundle_package_name'] = $data['bundle_package_name'];

                $sql = "SELECT  bundle_package_id FROM bundle_package WHERE bundle_package_name ='" . $data_array['bundle_package_name'] . "' AND bundle_package_id !='" . $bundle_package_id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    throw new Exception('Package Name Already Exists');
                }
            }
            if (isset($data['bundle_package_currency_id']))
                $data_array['bundle_package_currency_id'] = $data['bundle_package_currency_id'];
            if (isset($data['bundle_package_description']))
                $data_array['bundle_package_description'] = $data['bundle_package_description'];
            if (isset($data['monthly_charges']))
                $data_array['monthly_charges'] = $data['monthly_charges'];
            if (isset($data['bundle_package_status']))
                $data_array['bundle_package_status'] = $data['bundle_package_status'];
            if (isset($data['bundle_option'])) {
                $data_array['bundle_option'] = $data['bundle_option'];
                if ($data_array['bundle_option'] == 0) {
                    $data['bundle1_prefix'] = $data['bundle2_prefix'] = $data['bundle3_prefix'] = '';
                    $data['bundle1_value'] = $data['bundle2_value'] = $data['bundle3_value'] = 0;
                    $data['bundle1_type'] = $data['bundle2_type'] = $data['bundle3_type'] = 'MINUTE';
                }
            }
            if (isset($data['bundle1_type']))
                $data_array['bundle1_type'] = $data['bundle1_type'];
            if (isset($data['bundle1_value']))
                $data_array['bundle1_value'] = $data['bundle1_value'];

            if (isset($data['bundle2_type']))
                $data_array['bundle2_type'] = $data['bundle2_type'];
            if (isset($data['bundle2_value']))
                $data_array['bundle2_value'] = $data['bundle2_value'];

            if (isset($data['bundle3_type']))
                $data_array['bundle3_type'] = $data['bundle3_type'];
            if (isset($data['bundle3_value']))
                $data_array['bundle3_value'] = $data['bundle3_value'];
            if (count($data_array) > 0) {
                $data_array['update_dt'] = date('Y-m-d H:i:s');

                $data_array['updated_by'] = get_logged_user_id();

                $where = "bundle_package_id='" . $bundle_package_id . "'";
                $str = $this->db->update_string('bundle_package', $data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'bundle', 'sql_key' => $where, 'sql_query' => $str);
            }

            /////////////////////prefixes/////////////////
            $sql = "SELECT * FROM bundle_package_prefixes WHERE bundle_package_id='" . $bundle_package_id . "' ";

            $existing_prefix_array = array();
            $existing_prefix_array[1] = $existing_prefix_array[2] = $existing_prefix_array[3] = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $id = $row['id'];
                $bundle_id = $row['bundle_id'];
                $prefix = $row['prefix'];
                $existing_prefix_array[$bundle_id][$prefix] = $id;
            }

            if (isset($data['bundle1_prefix'])) {
                $bundle1_prefix_array = array_unique(explode(',', $data['bundle1_prefix']));
                if (count($bundle1_prefix_array) == 0) {
                    if (count($existing_prefix_array[1]) > 0) {
                        $this->db->delete('bundle_package_prefixes', array('bundle_package_id' => $bundle_package_id, 'bundle_id' => '1'));
                    }
                } else {
                    foreach ($bundle1_prefix_array as $prefix) {
                        $maching_string_temp = $prefix; //$callerid_array_temp['maching_string'];
                        if (count($existing_prefix_array[1]) > 0 && isset($existing_prefix_array[1][$maching_string_temp])) {
                            unset($existing_prefix_array[1][$maching_string_temp]);
                        } else {
                            $add_array = array('bundle_package_id' => $bundle_package_id, 'bundle_id' => 1, 'prefix' => $maching_string_temp);

                            $str = $this->db->insert_string('bundle_package_prefixes', $add_array);
                            $result = $this->db->query($str);
                            if (!$result) {
                                $error_array = $this->db->error();
                                throw new Exception($error_array['message']);
                            }
                            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'bundle_package_prefixes', 'sql_key' => $where, 'sql_query' => $str);
                        }
                    }
                    if (count($existing_prefix_array[1]) > 0) {
                        foreach ($existing_prefix_array[1] as $existing_id) {
                            $this->db->delete('bundle_package_prefixes', array('id' => $existing_id));
                        }
                    }
                }
            }

            if (isset($data['bundle2_prefix'])) {
                $bundle2_prefix_array = array_unique(explode(',', $data['bundle2_prefix']));
                if (count($bundle2_prefix_array) == 0) {
                    if (count($existing_prefix_array[2]) > 0) {
                        $this->db->delete('bundle_package_prefixes', array('bundle_package_id' => $bundle_package_id, 'bundle_id' => '2'));
                    }
                } else {
                    foreach ($bundle2_prefix_array as $prefix) {
                        $maching_string_temp = $prefix; //$callerid_array_temp['maching_string'];
                        if (count($existing_prefix_array[2]) > 0 && isset($existing_prefix_array[2][$maching_string_temp])) {
                            unset($existing_prefix_array[2][$maching_string_temp]);
                        } else {
                            $add_array = array('bundle_package_id' => $bundle_package_id, 'bundle_id' => 2, 'prefix' => $maching_string_temp);

                            $str = $this->db->insert_string('bundle_package_prefixes', $add_array);
                            $result = $this->db->query($str);
                            if (!$result) {
                                $error_array = $this->db->error();
                                throw new Exception($error_array['message']);
                            }
                            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'bundle_package_prefixes', 'sql_key' => $where, 'sql_query' => $str);
                        }
                    }
                    if (count($existing_prefix_array[2]) > 0) {
                        foreach ($existing_prefix_array[2] as $existing_id) {
                            $this->db->delete('bundle_package_prefixes', array('id' => $existing_id));
                        }
                    }
                }
            }

            if (isset($data['bundle3_prefix'])) {
                $bundle3_prefix_array = array_unique(explode(',', $data['bundle3_prefix']));
                if (count($bundle3_prefix_array) == 0) {
                    if (count($existing_prefix_array[3]) > 0) {
                        $this->db->delete('bundle_package_prefixes', array('bundle_package_id' => $bundle_package_id, 'bundle_id' => '3'));
                    }
                } else {
                    foreach ($bundle3_prefix_array as $prefix) {
                        $maching_string_temp = $prefix; //$callerid_array_temp['maching_string'];
                        if (count($existing_prefix_array[3]) > 0 && isset($existing_prefix_array[3][$maching_string_temp])) {
                            unset($existing_prefix_array[3][$maching_string_temp]);
                        } else {
                            $add_array = array('bundle_package_id' => $bundle_package_id, 'bundle_id' => 3, 'prefix' => $maching_string_temp);

                            $str = $this->db->insert_string('bundle_package_prefixes', $add_array);
                            $result = $this->db->query($str);
                            if (!$result) {
                                $error_array = $this->db->error();
                                throw new Exception($error_array['message']);
                            }
                            $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'bundle_package_prefixes', 'sql_key' => $where, 'sql_query' => $str);
                        }
                    }
                    if (count($existing_prefix_array[3]) > 0) {
                        foreach ($existing_prefix_array[3] as $existing_id) {
                            $this->db->delete('bundle_package_prefixes', array('id' => $existing_id));
                        }
                    }
                }
            }


            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            } else {
                $this->db->trans_commit();
                // set_activity_log($log_data_array);
            }




            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            // set_activity_log($api_log_data_array); //api log
            return $e->getMessage();
        }
    }

    public function get_unassigned_data($account_id, $created_by, $filter_data) {
        $final_return_array = array();
        try {
            $sql = "SELECT bundle_package_id, bundle_package_name FROM bundle_package WHERE 1 ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    $sql .= " AND $key ='" . $value . "' ";
                }
            }

            $sql .= " AND account_id ='" . get_logged_account_id() . "'";


            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            return $query->result_array();
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    public function delete($data) {
        try {
            $this->db->trans_begin();
            foreach ($data['delete_id'] as $id) {
                $log_data_array = array();
                $q = $this->db->where('bundle_id', param_decrypt($id))->get('bundle');
                $row = $q->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $str = $this->db->where('bundle_id', param_decrypt($id))->get_compiled_delete('bundle');
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }

                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'bundle', 'sql_key' => param_decrypt($id), 'sql_query' => $data_dump);
                }

                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'TARIFF', 'sql_key' => param_decrypt($id), 'sql_query' => $str);
                set_activity_log($log_data_array);
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return array('status' => false, 'msg' => 'failed deletion :: ' . $error_array['message']);
            } else {
                $this->db->trans_commit();
                return array('status' => true, 'msg' => 'Successfully deleted');
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return array('status' => false, 'msg' => 'failed deletion :: ' . $e->getMessage());
        }
    }

}
