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

class Cli_audit_mod extends CI_Model {

    public $did_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /* DID List */

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS 		
			pca.*, ua.company_name	
			
			 FROM " . $this->db->dbprefix('account_presentation_cli_audit') . " pca
			 LEFT JOIN " . $this->db->dbprefix('user_access') . " ua ON pca.account_id=ua.user_access_id_name
			 WHERE 1 ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if (in_array($key, array('logged_user_type', 'logged_user_account_id')))
                        continue;
                    if ($value != '') {
                        if ($key == 'account_presentation_cli_audit_id' || $key == 'status_id')
                            $sql .= " AND $key ='" . $value . "' ";
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY FIELD(status_id, '-1','1','0','-2') ";
            }

            $limit_from = intval($limit_from);

            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            //echo $sql;
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
                $account_presentation_cli_audit_id = $row['account_presentation_cli_audit_id'];
                $final_return_array['result'][$account_presentation_cli_audit_id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'CLI fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    /* Add CLI */

    function add($data) {
        try {
            $log_data_array = array(); //reset array

            $cli_data_array = array();

            $cli_data_array['account_id'] = $data['account_id'];
            $cli_data_array['caller_id'] = $data['caller_id'];
            $cli_data_array['status_id'] = $data['status_id'];

            if ($cli_data_array['status_id'] == 1) {
                $cli_data_array['approved_by'] = $data['created_by'];
                $cli_data_array['dt_approved'] = date('Y-m-d H:i:s');
            }

            if (isset($data['created_by']))
                $cli_data_array['created_by'] = $data['created_by'];
            if (isset($data['comments']))
                $cli_data_array['comments'] = $data['comments'];

            $cli_data_array['dt_created'] = date('Y-m-d H:i:s');


            ///check account
            $sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_access_id_name='" . $cli_data_array['account_id'] . "'";
            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows == 0) {
                return 'Account Not Found';
            }

            $sql = "SELECT account_id FROM " . $this->db->dbprefix('account_presentation_cli_audit') . " WHERE account_id='" . $cli_data_array['account_id'] . "' AND caller_id='" . $cli_data_array['caller_id'] . "'";
            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows > 0) {
                return 'CLI already exists';
            }


            $this->db->trans_begin();
            if (count($cli_data_array) > 0) {
                $str = $this->db->insert_string($this->db->dbprefix('account_presentation_cli_audit'), $cli_data_array);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->account_presentation_cli_audit_id = $this->db->insert_id();
                $log_data_array[] = array('activity_type' => 'add', 'sql_table' => $this->db->dbprefix('account_presentation_cli_audit'), 'sql_key' => $this->account_presentation_cli_audit_id, 'sql_query' => $str);
            }


            /////////////////


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

    /* Update CLI */

    function update($data) {
        try {
            $log_data_array = array(); //reset array
            $cli_data_array = array();

            if (isset($data['account_presentation_cli_audit_id']))
                $account_presentation_cli_audit_id = $data['account_presentation_cli_audit_id'];
            else
                return 'ID missing';


            $sql = "SELECT * FROM " . $this->db->dbprefix('account_presentation_cli_audit') . " WHERE account_presentation_cli_audit_id='" . $account_presentation_cli_audit_id . "' ";
            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows == 0) {
                return 'Record Not Found';
            }
            $existing_cli_data_array = $query->row_array();

            if (isset($data['caller_id'])) {
                $sql = "SELECT account_id FROM " . $this->db->dbprefix('account_presentation_cli_audit') . " WHERE account_id='" . $existing_cli_data_array['account_id'] . "' AND caller_id='" . $data['caller_id'] . "' AND account_presentation_cli_audit_id!='" . $account_presentation_cli_audit_id . "'";
                //echo $sql;die;
                $query = $this->db->query($sql);
                $num_rows = $query->num_rows();
                if ($num_rows > 0) {
                    return 'CLI already exists';
                }
            }
            ////////////////////
            if (isset($data['account_presentation_cli_audit_id']))
                $cli_data_array['account_presentation_cli_audit_id'] = $data['account_presentation_cli_audit_id'];
            if (isset($data['caller_id']))
                $cli_data_array['caller_id'] = $data['caller_id'];
            if (isset($data['status_id']))
                $cli_data_array['status_id'] = $data['status_id'];
            if (isset($data['account_id']))
                $cli_data_array['account_id'] = $data['account_id'];

            if (isset($data['comments']))
                $cli_data_array['comments'] = $data['comments'];

            if ($existing_cli_data_array['status_id'] != 1 && $cli_data_array['status_id'] == 1) {
                $cli_data_array['approved_by'] = $data['modify_by'];
                $cli_data_array['dt_approved'] = date('Y-m-d H:i:s');
            }
            $this->db->trans_begin();
            if (count($cli_data_array) > 0) {
                $where = "account_presentation_cli_audit_id='" . $data['account_presentation_cli_audit_id'] . "'";
                $str = $this->db->update_string($this->db->dbprefix('account_presentation_cli_audit'), $cli_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => $this->db->dbprefix('account_presentation_cli_audit'), 'sql_key' => $where, 'sql_query' => $str);
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

    /* Delete service */

    function delete($id_array) {
        try {
            //check status

            $this->db->trans_begin();

            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();

                ////delete CLI ///////
                $sql = "SELECT * FROM " . $this->db->dbprefix('account_presentation_cli_audit') . " WHERE account_presentation_cli_audit_id='" . $id . "' ";
                $query = $this->db->query($sql);
                $row = $query->row_array();
                if (isset($row)) {
                    $data_dump = serialize($row);
                    $result = $this->db->delete($this->db->dbprefix('account_presentation_cli_audit'), array('account_presentation_cli_audit_id' => $id));
                    if (!$result) {
                        $error_array = $this->db->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => $this->db->dbprefix('account_presentation_cli_audit'), 'sql_key' => $id, 'sql_query' => $data_dump);
                }

                $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'Presentation CLI', 'sql_key' => $id, 'sql_query' => '');
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

    function get_data_total_count() {
        return $this->total_count;
    }

}
