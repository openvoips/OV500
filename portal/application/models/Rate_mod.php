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

class Rate_mod extends CI_Model {

    public $total_count;

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data_total_count() {
        return $this->total_count;
    }

    /* Add */

    public function add($data) {
        $log_data_array = array();

        // grab post data
        $data_array = array();
        if (isset($data['frm_card']))
            $data_array['ratecard_id'] = $data['frm_card'];
        if (isset($data['frm_prefix']))
            $data_array['prefix'] = $data['frm_prefix'];
        if (isset($data['frm_dest']))
            $data_array['destination'] = $data['frm_dest'];
        if (isset($data['frm_ppm']))
            $data_array['rate'] = $data['frm_ppm'];
        if (isset($data['frm_ppc']))
            $data_array['connection_charge'] = $data['frm_ppc'];
        if (isset($data['frm_min']))
            $data_array['minimal_time'] = $data['frm_min'];
        if (isset($data['frm_res']))
            $data_array['resolution_time'] = $data['frm_res'];
        if (isset($data['frm_grace']))
            $data_array['grace_period'] = $data['frm_grace'];
        if (isset($data['frm_mul']))
            $data_array['rate_multiplier'] = $data['frm_mul'];
        if (isset($data['frm_add']))
            $data_array['rate_addition'] = $data['frm_add'];
        if (isset($data['frm_status']))
            $data_array['rates_status'] = $data['frm_status'];
        if (isset($data['frm_rental']))
            $data_array['rental'] = $data['frm_rental'];
        if (isset($data['frm_setup_charge']))
            $data_array['setup_charge'] = $data['frm_setup_charge'];


        if (isset($data['frm_inclusive_channel']))
            $data_array['inclusive_channel'] = $data['frm_inclusive_channel'];
        if (isset($data['frm_exclusive_per_channel_rental']))
            $data_array['exclusive_per_channel_rental'] = $data['frm_exclusive_per_channel_rental'];

        $data_array['update_dt'] = $data_array['create_dt'] = date('Y-m-d H:i:s');

        // check rate card is from carrier or user		
        $rate_type = $data['ratecard_type'];
        $ratecard_for = $data['ratecard_for'];

        if ($rate_type == 'CARRIER')
            $rate_table_name = 'carrier_rates';
        elseif ($rate_type == 'CUSTOMER')
            $rate_table_name = 'customer_rates';
        else
            return array('status' => false, 'msg' => 'Data Mismatch');

        $str = $this->db->insert_string($this->db->dbprefix($rate_table_name), $data_array);

        $result = $this->db->query($str);
        if ($result) {
            $insert_id = $this->db->insert_id();
            if ($data['ratecard_type'] == 'CARRIER')
                $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => $this->db->dbprefix($rate_table_name), 'sql_key' => '', 'sql_query' => $str);
            else
                $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => $this->db->dbprefix($rate_table_name), 'sql_key' => '', 'sql_query' => $str);

            set_activity_log($log_data_array);
            return array('status' => true, 'id' => $insert_id, 'msg' => 'Successfully added');
        }
        else {
            $error_array = $this->db->error();
            return array('status' => false, 'msg' => $error_array['message']);
        }
    }

    /* Update */

    public function update($data) {
        $log_data_array = array();

        // grab post data
        $data_array = array();
        if (isset($data['frm_prefix']))
            $data_array['prefix'] = $data['frm_prefix'];
        if (isset($data['frm_dest']))
            $data_array['destination'] = $data['frm_dest'];
        if (isset($data['frm_ppm']))
            $data_array['rate'] = $data['frm_ppm'];
        if (isset($data['frm_ppc']))
            $data_array['connection_charge'] = $data['frm_ppc'];
        if (isset($data['frm_min']))
            $data_array['minimal_time'] = $data['frm_min'];
        if (isset($data['frm_res']))
            $data_array['resolution_time'] = $data['frm_res'];
        if (isset($data['frm_grace']))
            $data_array['grace_period'] = $data['frm_grace'];
        if (isset($data['frm_mul']))
            $data_array['rate_multiplier'] = $data['frm_mul'];
        if (isset($data['frm_add']))
            $data_array['rate_addition'] = $data['frm_add'];
        if (isset($data['frm_status']))
            $data_array['rates_status'] = $data['frm_status'];

        if (isset($data['frm_rental']))
            $data_array['rental'] = $data['frm_rental'];
        if (isset($data['frm_setup_charge']))
            $data_array['setup_charge'] = $data['frm_setup_charge'];

        if (isset($data['frm_inclusive_channel']))
            $data_array['inclusive_channel'] = $data['frm_inclusive_channel'];
        if (isset($data['frm_exclusive_per_channel_rental']))
            $data_array['exclusive_per_channel_rental'] = $data['frm_exclusive_per_channel_rental'];

        $data_array['update_dt'] = date('Y-m-d H:i:s');

        if (isset($data['frm_key'])) {
            $ratecard_id = $data['frm_key'];
            $this->load->model('ratecard_mod');
            $response = $this->ratecard_mod->get_data('', 0, '', array('ratecard_id' => $ratecard_id));
            $ratecard_type = $response['result'][0]['ratecard_type'];
            $ratecard_for = $response['result'][0]['ratecard_for'];

            if ($ratecard_type == 'CARRIER')
                $rate_table_name = 'carrier_rates';
            elseif ($ratecard_type == 'CUSTOMER')
                $rate_table_name = 'customer_rates';
            else
                return array('status' => false, 'msg' => 'Data Mismatch');

            /* if($ratecard_type == 'CARRIER' && $ratecard_for=='INCOMING')
              $rate_table_name='carrier_rates_incoming';
              elseif($ratecard_type == 'CARRIER' && $ratecard_for=='OUTGOING')
              $rate_table_name='carrier_rates';
              elseif($ratecard_type == 'CUSTOMER' && $ratecard_for=='INCOMING')
              $rate_table_name='rates_incoming';
              else
              $rate_table_name='rates'; */


            $this->db->trans_begin();
            if (count($data_array) > 0) {
                $where = "rate_id='" . $data['frm_id'] . "'";

                $str = $this->db->update_string($this->db->dbprefix($rate_table_name), $data_array, $where);
                $result = $this->db->query($str);
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => $this->db->dbprefix($rate_table_name), 'sql_key' => $where, 'sql_query' => $str);
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

    /* Delete */

    public function delete($data) {
        try {
            $this->db->trans_begin();

            foreach ($data['delete_id'] as $id) {
                $info = explode('@', param_decrypt($id));
                $rate_id = $info[0];
                $ratecard_id = $info[1];

                $log_data_array = array();
                $q = $this->db->where('ratecard_id', $ratecard_id)->get($this->db->dbprefix('ratecard'));
                $row = $q->result_array();
                if (count($row) > 0) {
                    $ratecard_type = $row[0]['ratecard_type'];
                    $ratecard_for = $row[0]['ratecard_for'];

                    if ($ratecard_type == 'CARRIER' && $ratecard_for == 'INCOMING') {
                        $table = 'carrier_rates';
                        $table_type = 'RATE_CARRIER';
                    } elseif ($ratecard_type == 'CARRIER' && $ratecard_for == 'OUTGOING') {
                        $table = 'carrier_rates';
                        $table_type = 'RATE_CARRIER';
                    } elseif ($ratecard_type == 'CUSTOMER' && $ratecard_for == 'INCOMING') {
                        $table = 'customer_rates';
                        $table_type = 'RATE_USER';
                    } elseif ($ratecard_type == 'CUSTOMER' && $ratecard_for == 'OUTGOING') {
                        $table = 'customer_rates';
                        $table_type = 'RATE_USER';
                    } else {
                        throw new Exception('type & ratecard for mismatch');
                    }

                    /* if($ratecard_type == 'CUSTOMER') {
                      $table = 'rates';
                      $table_type = 'RATE_USER';
                      }else {
                      $table = 'carrier_rates';
                      $table_type = 'RATE_CARRIER';
                      } */

                    $q1 = $this->db->where('rate_id', $rate_id)->get($this->db->dbprefix($table));
                    $row1 = $q1->result_array();
                    $data_dump1 = serialize($row1);

                    $str = $this->db->where('rate_id', $rate_id)->get_compiled_delete($this->db->dbprefix($table));
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }

                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => $this->db->dbprefix($table), 'sql_key' => $rate_id, 'sql_query' => $data_dump1);
                }

                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => $table_type, 'sql_key' => $rate_id, 'sql_query' => $str);
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


        /* $log_data_array=array();
          $where = '';
          $str = $this->db->where('rate_id', $data)->get_compiled_delete($this->db->dbprefix('carrier_rates'));
          $result = $this->db->query($str);
          if($result){
          $log_data_array[] =array('activity_type'=>'delete','sql_table'=>$this->db->dbprefix('carrier_rates'),'sql_key'=>$where, 'sql_query'=>$str);
          set_activity_log($log_data_array);
          return array('status'=>true,'msg'=>'Successfully deleted');
          }else{
          return array('status'=>false,'msg'=>'failed deletion');
          } */
    }

    /* List */

    public function get_MyRates($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        try {
            $ratecard_type = '';
            if (isset($filter_data['tariff_id']) && $filter_data['tariff_id'] == '') {
                $final_return_array['result'] = array();
                $final_return_array["total"] = 0;
                $final_return_array['status'] = 'success';
                $final_return_array['message'] = 'Rate List fetched successfully';
                return $final_return_array;
            }

            if (isset($filter_data['tariff_id'])) {
                $tariff_id = $filter_data['tariff_id'];
                $this->load->model('tariff_mod');
                $response = $this->tariff_mod->get_data('', 0, '', array('tariff_id' => $tariff_id));
                $rate_type = $response['result'][0]['tariff_type'];
            }
            $rate_table_name = 'customer_rates';

            $this->db->select("SQL_CALC_FOUND_ROWS *, '$rate_table_name' as rate_table_name ", FALSE);
            $sub = $this->subquery->start_subquery('select');
            $sub->select('ratecard_for')->from('ratecard');
            $sub->where('ratecard.ratecard_id = ' . $rate_table_name . '.ratecard_id');
            $this->subquery->end_subquery('ratecard_for');
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'rate_id' || $key == 'rates_status') {
                            $this->db->where($key, $value);
                        } elseif ($key == 'tariff_id' && $filter_data['tariff_id'] != '') {
                            $subwhere = $this->subquery->start_subquery('where_in');
                            $subwhere->select('ratecard_id')->from('tariff_ratecard_map')->where('tariff_id', $filter_data['tariff_id']);
                            if ($filter_data['ratecard_for'] == 'OUTGOING' || $filter_data['ratecard_for'] == 'INCOMING')
                                $subwhere->where('ratecard_for', $filter_data['ratecard_for']);
                            $tariff_id = $filter_data['tariff_id'];
                            $this->subquery->end_subquery('ratecard_id', TRUE);
                        } elseif ($key == 'destination') {
                            $this->db->like($key, rtrim($value, '%'), 'after');
                        } elseif ($key == 'prefix') {
                            if (strpos($value, '%') !== false)
                                $this->db->like($key, rtrim($value, '%'), 'after');
                            else
                                $this->db->where($key, $value);
                        }
                    }
                }
            }
            $this->db->order_by('prefix', 'ASC');
            $this->db->limit(intval($limit_from), intval($limit_to));
            $q = $this->db->get($rate_table_name);

            if (!$q) {
                $error_array = $this->db->error();
            }
            $final_return_array['result'] = $q->result_array();
            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
            $final_return_array["total"] = $query->row()->Count;
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Rate List fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    public function get_data($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        try {
            $ratecard_type = '';
            //var_dump($filter_data);
            if (isset($filter_data['ratecard_id']) && $filter_data['ratecard_id'] == '' && isset($filter_data['tariff_id']) && $filter_data['tariff_id'] == '') {
                $final_return_array['result'] = array();
                $final_return_array["total"] = 0;

                $final_return_array['status'] = 'success';
                $final_return_array['message'] = 'Rate List fetched successfully';
                return $final_return_array;
            }

            if (isset($filter_data['ratecard_id']) && $filter_data['ratecard_id'] != '') {
                $ratecard_id = $filter_data['ratecard_id'];
                $this->load->model('ratecard_mod');
                $response = $this->ratecard_mod->get_data('', 0, '', array('ratecard_id' => $ratecard_id));
                $rate_type = $response['result'][0]['ratecard_type'];
                $ratecard_for = $response['result'][0]['ratecard_for'];
            } elseif (isset($filter_data['tariff_id'])) {
                $tariff_id = $filter_data['tariff_id'];
                $this->load->model('tariff_mod');
                $response = $this->tariff_mod->get_data('', 0, '', array('tariff_id' => $tariff_id));
                $rate_type = $response['result'][0]['tariff_type'];
                $ratecard_for = 'OUTGOING';
            } else {
                $rate_type = 'CUSTOMER';
                $ratecard_for = 'OUTGOING';
            }


            if ($rate_type == 'CARRIER')
                $rate_table_name = 'carrier_rates';
            elseif ($rate_type == 'CUSTOMER')
                $rate_table_name = 'customer_rates';
            else
                throw new Exception('type & ratecard mismatch');

            $this->db->select("SQL_CALC_FOUND_ROWS *, '$rate_table_name' as rate_table_name, '$ratecard_for' AS ratecard_for", FALSE);
            $sub = $this->subquery->start_subquery('select');
            $sub->select('ratecard_name')->from('ratecard');

            $sub->where('ratecard.ratecard_id = ' . $rate_table_name . '.ratecard_id');


            $this->subquery->end_subquery('card_name');

            //var_dump($filter_data);
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'rate_id' || $key == 'rates_status')
                            $this->db->where($key, $value);
                        /* elseif($key=='ratecard_id' && $filter_data['tariff_id_name']=='') $this->db->where($key, $value);
                          elseif($key=='ratecard_id' && $filter_data['tariff_id_name']!='') */
                        //elseif($key=='ratecard_id' && isset($filter_data['tariff_id_name']) && $filter_data['tariff_id_name']=='') $this->db->where($key, $filter_data['ratecard_id']);
                        elseif ($key == 'ratecard_id')
                            $this->db->where($key, $filter_data['ratecard_id']);
                        elseif ($key == 'tariff_id' && $filter_data['tariff_id'] != '') {
                            $subwhere = $this->subquery->start_subquery('where_in');
                            $subwhere->select('ratecard_id')->from('tariff_ratecard_map')->where('tariff_id', $filter_data['tariff_id']);
                            $this->subquery->end_subquery('ratecard_id', TRUE);
                        } elseif ($key == 'destination') {
                            $this->db->like($key, rtrim($value, '%'), 'after');
                        } elseif ($key == 'prefix') {
                            if (strpos($value, '%') !== false)
                                $this->db->like($key, rtrim($value, '%'), 'after');
                            else
                                $this->db->where($key, $value);
                        }
                    }
                }
            }

            /// added to filter search
            /* if($filter_data['created_by']!='admin')
              {
              $subwhere = $this->subquery->start_subquery('where_in');
              $subwhere->select('ratecard_id')->from('switch_ratecard')->where('created_by', $filter_data['created_by']);
              $this->subquery->end_subquery('ratecard_id', TRUE);
              } */

            $this->db->order_by('rate_id', 'ASC');
            $this->db->limit(intval($limit_from), intval($limit_to));
            //echo $this->db->get_compiled_select($rate_table_name); //die();
            /* if($rate_type == 'CUSTOMER') $q = $this->db->get('rates');
              else $q = $this->db->get('carrier_rates'); */
            //echo $rate_table_name;
            $q = $this->db->get($rate_table_name);

            if (!$q) {
                $error_array = $this->db->error();
                //throw new Exception($error_array['message']);
                //echo $this->db->last_query().'<br>';
                //echo $error_array['message'];
            }
// echo $this->db->last_query();

            $final_return_array['result'] = $q->result_array();
           

            $query = $this->db->query('SELECT FOUND_ROWS() AS Count');
             $row_count = $q->row();
           // $this->total_count = $row_count->total;
            
           $this->total_count =  $final_return_array["total"] = $query->row()->Count;

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Rate List fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    public function copyRates($data) {
        $log_data_array = array();
        if (isset($data['frm_key'])) {
            $this->load->model('ratecard_mod');
            $response = $this->ratecard_mod->get_data('', 0, '', array('ratecard_id' => $data['frm_key']));
            $ratecard_type_to = $response['result'][0]['ratecard_type'];
            $ratecard_for_to = $response['result'][0]['ratecard_for'];
            $response = $this->ratecard_mod->get_data('', 0, '', array('ratecard_id' => $data['frm_card']));
            $ratecard_type_from = $response['result'][0]['ratecard_type'];
            $ratecard_for_from = $response['result'][0]['ratecard_for'];
            $where = '';
            if ($ratecard_type_from == 'CARRIER') {
                $rate_table_name_from = 'carrier_rates';
            } elseif ($ratecard_type_from == 'CUSTOMER') {
                $rate_table_name_from = 'customer_rates';
            } else {
                return array('status' => false, 'msg' => 'type & ratecard for mismatch');
            }
            if ($ratecard_type_to == 'CARRIER') {
                $rate_table_name_to = 'carrier_rates';
            } elseif ($ratecard_type_to == 'CUSTOMER') {
                $rate_table_name_to = 'customer_rates';
            } else {
                return array('status' => false, 'msg' => 'type & ratecard for mismatch');
            }
            if (isset($data['frm_del'])) {
                $str = $this->db->where('ratecard_id', $data['frm_key'])->get_compiled_delete($rate_table_name_to);
                $result = $this->db->query($str);

                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => $this->db->dbprefix($rate_table_name_to), 'sql_key' => $where, 'sql_query' => $str);
                set_activity_log($log_data_array);
            }

            $sql = "insert into $rate_table_name_to ";
            $sql .= " (ratecard_id, prefix, destination, rate, connection_charge, minimal_time, resolution_time, grace_period, rate_multiplier, rate_addition, rates_status,inclusive_channel, exclusive_per_channel_rental, create_dt, update_dt) select '" . $data['frm_key'] . "', prefix,destination,";


            if ($data['frm_action_rate'] == 1)
                $sql .= "rate,";
            elseif ($data['frm_action_rate'] == 2)
                $sql .= "rate * " . $data['frm_val_rate'] . ",";
            elseif ($data['frm_action_rate'] == 3)
                $sql .= "rate + " . $data['frm_val_rate'] . ",";
            elseif ($data['frm_action_rate'] == 4)
                $sql .= "'" . $data['frm_val_rate'] . "',";

            if ($data['frm_action_connect'] == 1)
                $sql .= "connection_charge,";
            elseif ($data['frm_action_connect'] == 2)
                $sql .= "connection_charge * " . $data['frm_val_connect'] . ",";
            elseif ($data['frm_action_connect'] == 3)
                $sql .= "connection_charge + " . $data['frm_val_connect'] . ",";
            elseif ($data['frm_action_connect'] == 4)
                $sql .= "'" . $data['frm_val_connect'] . "',";

            if ($data['frm_action_min'] == 1)
                $sql .= "minimal_time,";
            elseif ($data['frm_action_min'] == 2)
                $sql .= "minimal_time * " . $data['frm_val_min'] . ",";
            elseif ($data['frm_action_min'] == 3)
                $sql .= "minimal_time + " . $data['frm_val_min'] . ",";
            elseif ($data['frm_action_min'] == 4)
                $sql .= "'" . $data['frm_val_min'] . "',";
            if ($data['frm_action_res'] == 1)
                $sql .= "resolution_time,";
            elseif ($data['frm_action_res'] == 2)
                $sql .= "resolution_time * " . $data['frm_val_res'] . ",";
            elseif ($data['frm_action_res'] == 3)
                $sql .= "resolution_time + " . $data['frm_val_res'] . ",";
            elseif ($data['frm_action_res'] == 4)
                $sql .= "'" . $data['frm_val_res'] . "',";

            $sql .= " grace_period, rate_multiplier, rate_addition, rates_status, inclusive_channel, exclusive_per_channel_rental, '" . date('Y-m-d H:i:s') . "','" . date('Y-m-d H:i:s') . "' ";
            $sql .= " from $rate_table_name_from ";
            $sql .= " where ratecard_id = '" . $data['frm_card'] . "'";
            $sql .= "  ON DUPLICATE KEY UPDATE destination = values(destination), rate= values(rate), connection_charge = values(connection_charge) , minimal_time= values(minimal_time), resolution_time= values(resolution_time), grace_period= values(grace_period), rate_multiplier= values(rate_multiplier), rate_addition= values(rate_addition), rates_status= values(rates_status),inclusive_channel= values(inclusive_channel), exclusive_per_channel_rental= values(exclusive_per_channel_rental) ;";
            $result = $this->db->query($sql);
            if ($result) {
                $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => $rate_table_name_to, 'sql_key' => $where, 'sql_query' => $sql);
                set_activity_log($log_data_array);
                return array('status' => true, 'msg' => 'Successfully Uploaded');
            } else {
                return array('status' => false, 'msg' => 'failed upload process');
            }
        }
    }

    public function bulkRates($data, $csv_data) {
        $where = $error_msg = '';
        $success = true;
        $log_data_array = array();
        //var_dump($data);

        if (isset($data['frm_key'])) {
            $ratecard_id = $data['frm_key'];

            $this->load->model('ratecard_mod');
            $response = $this->ratecard_mod->get_data('', 0, '', array('ratecard_id' => $ratecard_id));
            $ratecard_type = $response['result'][0]['ratecard_type'];
            $ratecard_for = $response['result'][0]['ratecard_for'];


            // delete rates from ratecard
            if (isset($data['frm_del'])) {

                if ($ratecard_type == 'CUSTOMER') {
                    $str = $this->db->where('ratecard_id', $ratecard_id)->get_compiled_delete('customer_rates');
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'customer_rates', 'sql_key' => $where, 'sql_query' => $str);
                } else {
                    $str = $this->db->where('ratecard_id', $ratecard_id)->get_compiled_delete($this->db->dbprefix('carrier_rates'));
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => $this->db->dbprefix('carrier_rates'), 'sql_key' => $where, 'sql_query' => $str);
                }

                $result = $this->db->query($str);
                set_activity_log($log_data_array);
            }



            if ($ratecard_type == 'CARRIER')
                $rate_table_name = 'carrier_rates';
            elseif ($ratecard_type == 'CUSTOMER')
                $rate_table_name = 'customer_rates';
            else
                $rate_table_name = 'customer_rates';


            $sql_insert = 'INSERT INTO ' . $rate_table_name . ' (ratecard_id, prefix, destination, rate, connection_charge, minimal_time, resolution_time, grace_period, rate_multiplier, rate_addition, 
			rates_status, 
			inclusive_channel, exclusive_per_channel_rental, rental, setup_charge,
			 create_dt, update_dt) VALUES ';
            /////////////	



            $sql_values = '';

            for ($i = 1; $i < count($csv_data); $i++) {

                if ($ratecard_for == 'INCOMING') {
                    $inclusive_channel = $csv_data[$i][10];
                    $exclusive_per_channel_rental = $csv_data[$i][11];
                    $rental = $csv_data[$i][12];
                    $setup_charge = $csv_data[$i][13];
                } else {
                    $inclusive_channel = 0;
                    $exclusive_per_channel_rental = 0;
                    $rental = 0;
                    $setup_charge = 0;
                }

                $sql_values .= "('" . $ratecard_id . "', '" . $csv_data[$i][0] . "', '" . $csv_data[$i][1] . "', " . $csv_data[$i][2] . ", " . $csv_data[$i][3] . ", " . $csv_data[$i][4] . ", " . $csv_data[$i][5] . ", " . $csv_data[$i][6] . ", '" . $csv_data[$i][7] . "', '" . $csv_data[$i][8] . "',
				 '" . $csv_data[$i][9] . "', 
		'" . $inclusive_channel . "',	'" . $exclusive_per_channel_rental . "', '" . $rental . "', '" . $setup_charge . "', NOW(), NOW()),";

                if (($i % 400) == 399 || $i == count($csv_data) - 1) {
                    $sql = $sql_insert . rtrim($sql_values, ',');
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


            if ($success) {

                $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => $rate_table_name, 'sql_key' => '', 'sql_query' => $sql);

                set_activity_log($log_data_array);
                return array('status' => true, 'msg' => 'Successfully Uploaded');
            } else {
                return array('status' => false, 'msg' => $error_msg);
            }
        }
    }

}
