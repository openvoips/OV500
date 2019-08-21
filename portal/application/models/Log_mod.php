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

class Log_mod extends CI_Model {

    public $primary_id;
    public $account_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /* List */

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS 	*
					FROM " . $this->db->dbprefix('activity_log') . "  
					  WHERE 1 ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {

                        if ($key == 'activity_id' || $key == 'activity_type')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'start_dt')
                            $sql .= " AND dt_created >='" . $value . "' ";
                        elseif ($key == 'end_dt')
                            $sql .= " AND dt_created <='" . $value . "' ";
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY `activity_id` DESC ";
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
                $id = $row['id'];
                $final_return_array['result'][$id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Deleted log fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function rollback($activity_id) {
        $log_data_array = array(); //reset array	
        try {
            $this->db->trans_begin();

            $search_data = array('activity_id' => $activity_id, 'activity_type' => 'delete');
            $log_details_result = $this->get_data('', '', '', $search_data);
            if (!isset($log_details_result['result']))
                throw new Exception('No log found');

            $activity_data = $log_details_result['result'];

            if (count($activity_data) == 0)
                throw new Exception('No log found');


            foreach ($activity_data as $log_data) {
                $table_name = $log_data['sql_table'];
                $table_data = unserialize($log_data['sql_query']);

                if (count($table_data) == 0)
                    continue;

                //echo '<br><br>';	echo $table_name;	
                if (gettype(current($table_data)) == 'array') {
                    foreach ($table_data as $log_data_single) {
                        $str = $this->db->insert_string($table_name, $log_data_single);
                        $result = $this->db->query($str);
                        if (!$result) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $this->primary_id = $this->db->insert_id();
                        $log_data_array[] = array('activity_type' => 'rollback', 'sql_table' => $table_name, 'sql_key' => $this->primary_id, 'sql_query' => $str);
                        //	echo $str; 
                        //	echo  '<pre>';print_r($log_data_single);echo  '</pre>';
                    }
                } else {//string
                    $str = $this->db->insert_string($table_name, $table_data);
                    $result = $this->db->query($str);
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $this->primary_id = $this->db->insert_id();
                    $log_data_array[] = array('activity_type' => 'rollback', 'sql_table' => $table_name, 'sql_key' => $this->primary_id, 'sql_query' => $str);

                    //echo $str;  //echo  '<pre>';print_r($table_data);echo  '</pre>';			
                }
            }//foreach
            //no query executed
            if (count($log_data_array) == 0)
                throw new Exception('No query executed');


            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                //delete all entry from log for this activity
                //or status change ???????
                $result = $this->db->delete($this->db->dbprefix('activity_log'), array('activity_id' => $activity_id));

                $this->db->trans_commit();
                set_activity_log($log_data_array);
                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function get_delete_types() {
        $sql = "SELECT DISTINCT sql_table FROM " . $this->db->dbprefix('activity_log') . " WHERE activity_type='delete_recovery' ORDER BY sql_table";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        return $rows;
    }

    function email_get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS 	el.*, ua.company_name
					FROM " . $this->db->dbprefix('emaillog') . " el LEFT JOIN " . $this->db->dbprefix('user_access') . " ua ON el.account_id = ua.user_access_id_name
					  WHERE 1 ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {

                        if ($key == 'email_log_id' || $key == 'actionfrom')
                            $sql .= " AND $key ='" . $value . "' ";
                        elseif ($key == 'account_id') {
                            $sql .= " AND (el.account_id like '%" . $value . "%' OR el.subject like '%" . $value . "%' OR ua.company_name like '%" . $value . "%') ";
                        } elseif ($key == 'time_range') {
                            $range = explode(' - ', $filter_data['time_range']);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);

                            $start_dt = $range[0];
                            $end_dt = $range[1];

                            $sql .= " AND el.action_date BETWEEN '" . $start_dt . "' AND '" . $end_dt . "' ";
                        } else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY `email_log_id` DESC ";
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
                $id = $row['email_log_id'];
                $final_return_array['result'][$id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Deleted log fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_email_get_types() {
        $sql = "SELECT DISTINCT actionfrom FROM " . $this->db->dbprefix('emaillog') . " ORDER BY actionfrom";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        return $rows;
    }

}
