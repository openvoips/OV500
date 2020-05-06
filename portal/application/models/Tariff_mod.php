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

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tariff_mod extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function add($data) {
        $log_data_array = array();
        $data_array = array();
        if (isset($data['frm_name']))
            $data_array['tariff_name'] = $data['frm_name'];
        if (isset($data['frm_type']))
            $data_array['tariff_type'] = $data['frm_type'];
        if (isset($data['frm_currency']))
            $data_array['tariff_currency_id'] = $data['frm_currency'];
        if (isset($data['frm_desc']))
            $data_array['tariff_description'] = $data['frm_desc'];
        if (isset($data['frm_status']))
            $data_array['tariff_status'] = $data['frm_status'];
        $data_array['tariff_id'] = generate_key($data['frm_name'], '');
        $data_array['created_by'] = get_logged_account_id();
        $data_array['update_dt'] = $data_array['create_dt'] = date('Y-m-d H:i:s');
        $str = $this->db->insert_string('tariff', $data_array);
        $result = $this->db->query($str);
        if ($result) {
            $insert_id = $data_array['tariff_id'];
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'tariff', 'sql_key' => '', 'sql_query' => $str);
            set_activity_log($log_data_array);
            return array('status' => true, 'id' => $insert_id, 'msg' => 'Successfully added');
        } else {
            $error_array = $this->db->error();
            return array('status' => false, 'msg' => $error_array['message']);
        }
    }

    public function update($data) {
        $log_data_array = array();
        $data_array = array();
        if (isset($data['frm_name']))
            $data_array['tariff_name'] = $data['frm_name'];
        if (isset($data['frm_type']))
            $data_array['tariff_type'] = $data['frm_type'];
        if (isset($data['frm_desc']))
            $data_array['tariff_description'] = $data['frm_desc'];
        if (isset($data['frm_status']))
            $data_array['tariff_status'] = $data['frm_status'];
        $data_array['update_dt'] = date('Y-m-d H:i:s');
        if (isset($data['frm_key'])) {
            $this->db->trans_begin();
            if (count($data_array) > 0) {
                $where = "tariff_id='" . $data['frm_key'] . "'";
                $str = $this->db->update_string('tariff', $data_array, $where);
                $result = $this->db->query($str);
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'tariff', 'sql_key' => $where, 'sql_query' => $str);
            }

            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return array('status' => false, 'msg' => $error_array['message']);
            } else {
                $this->db->trans_commit();
                set_activity_log($log_data_array);
                return array('status' => true, 'msg' => 'Successfully updated');
            }
        } else {
            return array('status' => false, 'msg' => 'KEY not found');
        }
    }

    public function updateBundle($data) {
        $log_data_array = array();
        $data_array = array();
        $data_array_incoming = array();
        $data_array['package_option'] = '0';
        $data_array['monthly_charges'] = 0;
        $data_array['bundle_option'] = '0';
        $data_array['bundle1_type'] = $data_array['bundle2_type'] = $data_array['bundle3_type'] = 'MINUTE';
        $data_array['bundle1_value'] = 0;
        $data_array['bundle2_value'] = $data_array['bundle3_value'] = '';
        $data_array_incoming['bundle1_prefix'] = $data_array_incoming['bundle2_prefix'] = $data_array_incoming['bundle3_prefix'] = '';
        if ($data['frm_plan'] == 1) {
            $data_array['package_option'] = '1';
            $data_array['monthly_charges'] = $data['frm_monthly_charge'];
            if ($data['frm_bundle'] == 1) {
                $data_array['bundle_option'] = '1';
                $data_array['bundle1_type'] = $data['bundle1_type'];
                $data_array['bundle1_value'] = $data['bundle1_value'];
                $data_array_incoming['bundle1_prefix'] = $data['bundle1_prefix'];
                $data_array['bundle2_type'] = $data['bundle2_type'];
                $data_array['bundle2_value'] = $data['bundle2_value'];
                $data_array_incoming['bundle2_prefix'] = $data['bundle2_prefix'];
                $data_array['bundle3_type'] = $data['bundle3_type'];
                $data_array['bundle3_value'] = $data['bundle3_value'];
                $data_array_incoming['bundle3_prefix'] = $data['bundle3_prefix'];
            }
        }
        $data_array['update_dt'] = date('Y-m-d H:i:s');
        if (isset($data['frm_key'])) {
            $this->db->trans_begin();
            if (count($data_array) > 0) {
                if ($data_array['bundle2_value'] == '')
                    $data_array['bundle2_value'] = NULL;
                if ($data_array['bundle3_value'] == '')
                    $data_array['bundle3_value'] = NULL;
                $where = "tariff_id='" . $data['frm_key'] . "'";
                $str = $this->db->update_string('tariff', $data_array, $where);
                $result = $this->db->query($str);
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'tariff', 'sql_key' => $where, 'sql_query' => $str);
                $str = $this->db->where('tariff_id', $data['frm_key'])->get_compiled_delete('tariff_bundle_prefixes');
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $data_in = array();
                $prefix1 = explode(',', $data_array_incoming['bundle1_prefix']);
                if (count($prefix1) > 0) {
                    for ($x = 0; $x < count($prefix1); $x++) {
                        if ($prefix1[$x] != '')
                            $data_in[] = array('tariff_id' => $data['frm_key'], 'bundle_id' => 1, 'prefix' => $prefix1[$x]);
                    }
                }
                $prefix2 = explode(',', $data_array_incoming['bundle2_prefix']);
                if (count($prefix2) > 0) {
                    for ($x = 0; $x < count($prefix2); $x++) {
                        if ($prefix2[$x] != '')
                            $data_in[] = array('tariff_id' => $data['frm_key'], 'bundle_id' => 2, 'prefix' => $prefix2[$x]);
                    }
                }
                $prefix3 = explode(',', $data_array_incoming['bundle3_prefix']);
                if (count($prefix3) > 0) {
                    for ($x = 0; $x < count($prefix3); $x++) {
                        if ($prefix3[$x] != '')
                            $data_in[] = array('tariff_id' => $data['frm_key'], 'bundle_id' => 3, 'prefix' => $prefix3[$x]);
                    }
                }
                if (count($data_in) != 0) {
                    $result = $this->db->insert_batch('tariff_bundle_prefixes', $data_in);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                }
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return array('status' => false, 'msg' => $error_array['message']);
            } else {
                $this->db->trans_commit();
                set_activity_log($log_data_array);
                return array('status' => true, 'msg' => 'Successfully updated');
            }
        } else {
            return array('status' => false, 'msg' => 'KEY not found');
        }
    }

    public function delete($data) {
        try {
            $this->db->trans_begin();
            foreach ($data['delete_id'] as $id) {
                $log_data_array = array();
                $q = $this->db->where('tariff_id', param_decrypt($id))->get('tariff');
                $row = $q->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $str = $this->db->where('tariff_id', param_decrypt($id))->get_compiled_delete('tariff');
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }

                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'tariff', 'sql_key' => param_decrypt($id), 'sql_query' => $data_dump);
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

    public function get_data($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        try {
            $am_ratecard_id_name_array = array();
            if (isset($filter_data['logged_account_type']) && isset($filter_data['logged_current_customer_id']) && $filter_data['logged_account_type'] == 'agent') {
                $sub_sql = "SELECT DISTINCT u.tariff_id FROM customers ua, account u WHERE ua.account_id=u.account_id AND  ua.account_manager='" . $filter_data['logged_current_customer_id'] . "' AND u.tariff_id IS NOT NULL";
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $am_ratecard_id_name_array[] = $row['tariff_id'];
                    }
                }
            }

            $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
            $sub = $this->subquery->start_subquery('select');
            $sub->select('name')->from('sys_currencies');
            $sub->where('sys_currencies.currency_id = tariff.tariff_currency_id');
            $this->subquery->end_subquery('currency_name');
            $sub = $this->subquery->start_subquery('select');
            $sub->select('symbol')->from('sys_currencies');
            $sub->where('sys_currencies.currency_id = tariff.tariff_currency_id');
            $this->subquery->end_subquery('currency_symbol');
            $sub = $this->subquery->start_subquery('select');
            $sub->select('count(*)')->from('tariff_ratecard_map');
            $sub->where('tariff.tariff_id = tariff_ratecard_map.tariff_id and ratecard_for = "OUTGOING"');
            $this->subquery->end_subquery('ratecard_count');
            $sub = $this->subquery->start_subquery('select');
            $sub->select('count(*)')->from('tariff_ratecard_map');
            $sub->where('tariff.tariff_id = tariff_ratecard_map.tariff_id and ratecard_for = "INCOMING"');
            $this->subquery->end_subquery('ratecard_in_count');
            $sub = $this->subquery->start_subquery('select');
            $sub->select('count(*)')->from('carrier');
            $sub->where('tariff.tariff_id = carrier.tariff_id');
            $this->subquery->end_subquery('carrier_count');
            $sub = $this->subquery->start_subquery('select');
            $sub->select('count(*)')->from('account');
            $sub->where('tariff.tariff_id = account.tariff_id');
            $this->subquery->end_subquery('user_count');

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'tariff_currency_id' || $key == 'tariff_id')
                            $this->db->where($key, $value);
                        elseif (in_array($key, array('logged_account_type', 'logged_current_customer_id', 'logged_account_level'))) {
                            
                        } elseif ($key == 'created_by') {
                            if ($value == 'admin') {
                                $sub = $this->subquery->start_subquery('where_in');
                                $sub->select('account_id')->from('customers');
                                //     $sub->where("account_type in ('ADMIN','SUBADMIN'') ");
                                $this->subquery->end_subquery('created_by', TRUE);
                            } else {
                                $this->db->where('created_by', $value);
                            }
                        } else
                            $this->db->like($key, $value, 'after');
                    }
                }
            }
            if (count($am_ratecard_id_name_array) > 0) {
                $this->db->where_in('tariff_id', $am_ratecard_id_name_array);
            }
            if (is_string($order_by) && $order_by == '') {
                $this->db->order_by('id', 'DESC');
            } else {
                foreach ($order_by as $k => $v)
                    $this->db->order_by($k, $v);
            }
            $this->db->limit(intval($limit_from), intval($limit_to));
            $q = $this->db->get('tariff');
            if (!$q) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = $q->result_array();

            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
            $final_return_array["total"] = $query->row()->Count;

            if ($final_return_array['result']) {
                if (strlen($final_return_array['result'][0]['tariff_id']) > 0) {
                    $q = $this->db->query("SELECT bundle_id, group_concat(prefix) as prefixes FROM tariff_bundle_prefixes WHERE tariff_id = '" . $final_return_array['result'][0]['tariff_id'] . "' group by bundle_id order by bundle_id");
                    $final_return_array['bundle'] = $q->result_array();
                }
            } else {
                $final_return_array['bundle'] = '';
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Tariff List fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    public function addTMP($data) {
        $log_data_array = array();
        $data_array = array();
        if (isset($data['frm_card']))
            $data_array['ratecard_id'] = $data['frm_card'];
        if (isset($data['frm_key']))
            $data_array['tariff_id'] = $data['frm_key'];
        if (isset($data['frm_start_day']))
            $data_array['start_day'] = $data['frm_start_day'];
        if (isset($data['frm_start_time']))
            $data_array['start_time'] = $data['frm_start_time'];
        if (isset($data['frm_end_day']))
            $data_array['end_day'] = $data['frm_end_day'];
        if (isset($data['frm_end_time']))
            $data_array['end_time'] = $data['frm_end_time'];
        if (isset($data['frm_priority']))
            $data_array['priority'] = $data['frm_priority'];
        if (isset($data['frm_status']))
            $data_array['status'] = $data['frm_status'];
        if (isset($data['ratecard_for']))
            $data_array['ratecard_for'] = $data['ratecard_for'];

        $str = $this->db->insert_string('tariff_ratecard_map', $data_array);
        $result = $this->db->query($str);
        if ($result) {
            $insert_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'tariff_ratecard_map', 'sql_key' => '', 'sql_query' => $str);
            set_activity_log($log_data_array);
            return array('status' => true, 'id' => $insert_id, 'msg' => 'Successfully added');
        } else {
            $error_array = $this->db->error();
            return array('status' => false, 'msg' => $error_array['message']);
        }
    }

    public function editTMP($data) {
        $log_data_array = array();
        $data_array = array();
        if (isset($data['frm_start_day']))
            $data_array['start_day'] = $data['frm_start_day'];
        if (isset($data['frm_start_time']))
            $data_array['start_time'] = $data['frm_start_time'];
        if (isset($data['frm_end_day']))
            $data_array['end_day'] = $data['frm_end_day'];
        if (isset($data['frm_end_time']))
            $data_array['end_time'] = $data['frm_end_time'];
        if (isset($data['frm_priority']))
            $data_array['priority'] = $data['frm_priority'];
        if (isset($data['frm_status']))
            $data_array['status'] = $data['frm_status'];

        if (isset($data['frm_id'])) {
            $where = "id='" . $data['frm_id'] . "'";
            $str = $this->db->update_string('tariff_ratecard_map', $data_array, $where);
            $result = $this->db->query($str);
            if ($result) {
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'tariff_ratecard_map', 'sql_key' => '', 'sql_query' => $str);
                set_activity_log($log_data_array);
                return array('status' => true, 'id' => $data['frm_key'], 'msg' => 'Successfully updated');
            } else {
                $error_array = $this->db->error();
                return array('status' => false, 'id' => $data['frm_key'], 'msg' => $error_array['message']);
            }
        } else {
            return array('status' => false, 'id' => $data['frm_key'], 'msg' => 'KEY not found');
        }
    }

    public function get_mapping($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        try {
            $this->db->select("SQL_CALC_FOUND_ROWS 
				tariff_ratecard_map.id,
				tariff_ratecard_map.ratecard_id,
				tariff_ratecard_map.tariff_id,
				tariff_ratecard_map.start_day,
				tariff_ratecard_map.start_time,
				tariff_ratecard_map.end_day,
				tariff_ratecard_map.end_time,
				tariff_ratecard_map.priority,
				tariff_ratecard_map.status,
				ratecard.ratecard_name,
				ratecard.ratecard_type,
                                ratecard.ratecard_for,
				sys_currencies.name as symbol,
                                sys_currencies.name as currency_name ", FALSE);
            $this->db->from('tariff_ratecard_map');
            $this->db->join('ratecard', 'ratecard.ratecard_id = tariff_ratecard_map.ratecard_id');
            $this->db->join('sys_currencies', 'sys_currencies.currency_id = ratecard.ratecard_currency_id');
            $sub = $this->subquery->start_subquery('select');
            $sub->select('tariff_name')->from('tariff');
            $sub->where('tariff.tariff_id = tariff_ratecard_map.tariff_id');
            $this->subquery->end_subquery('tariff_name');
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'id' || $key == 'tariff_id')
                            $this->db->where($key, $value);
                        elseif ($key == 'tariff_ratecard_map_id')
                            $this->db->where('tariff_ratecard_map.id', $value);
                        else
                            $this->db->like('tariff_ratecard_map.' . $key, $value, 'after');
                    }
                }
            }
            $this->db->order_by('id', 'ASC');
            $this->db->limit(intval($limit_from), intval($limit_to));
            $q = $this->db->get();
            $final_return_array['result'] = $q->result_array();
            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
            $final_return_array["total"] = $query->row()->Count;
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Tariff ratecard Mapping fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    public function delete_mapping($data) {
        try {
            $this->db->trans_begin();
            foreach ($data['delete_id'] as $id) {
                $log_data_array = array();
                $q = $this->db->where('id', param_decrypt($id))->get('tariff_ratecard_map');
                $row = $q->result_array();
                if (count($row) > 0) {
                    $data_dump = serialize($row);
                    $str = $this->db->where('id', param_decrypt($id))->get_compiled_delete('tariff_ratecard_map');
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'tariff_ratecard_map', 'sql_key' => param_decrypt($id), 'sql_query' => $data_dump);
                }
                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'TARIFFMAP', 'sql_key' => param_decrypt($id), 'sql_query' => $str);
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

    public function get_tariff_name($tariff_id) {
        $sql = "SELECT tariff_name FROM tariff WHERE  tariff_id ='" . $tariff_id . "' LIMIT 0,1";
        $query = $this->db->query($sql);
        $row = $query->row();
        $tariffname = $row->tariff_name;
        return $tariffname;
    }

}
