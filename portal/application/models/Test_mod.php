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

class Test_mod extends CI_Model {

    public $account_id;
    public $max_balance_limit = 999999.999999;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function fetch_payment_history($order_by, $limit_to, $limit_from, $filter_data) {
        try {

            $final_return_array = array();
            $where = '';
            if (isset($filter_data['s_superagent']) && $filter_data['s_superagent'] != '') {
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $filter_data['s_superagent'] . "'";
                if (isset($filter_data['am_under_sm']) && $filter_data['am_under_sm'] != '') {
                    $sub_sub_sql .= " AND user_access_id_name='" . $filter_data['am_under_sm'] . "'";
                }
                $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

                $sub_query = $this->db->query($sub_sql);

                if (!$sub_query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $user_access_id_name_array = array();
                if ($sub_query->row() > 0) {
                    foreach ($sub_query->result_array() as $row) {
                        $user_access_id_name_array[] = $row['user_access_id_name'];
                    }
                }
                $account_id_str = implode("','", $user_access_id_name_array);
                $account_id_str = "'" . $account_id_str . "'";

                if ($where != '')
                    $where .= ' AND ';

                if (isset($_SESSION['search_payment_data']['search_account_id']) && $_SESSION['search_payment_data']['search_account_id'] != '') {

                    $where .= " ph.account_id IN('" . $_SESSION['search_payment_data']['search_account_id'] . "')";
                } else {
                    $where .= " ph.account_id IN(" . $account_id_str . ")";
                }
            } elseif (isset($filter_data['s_account_manager']) && $filter_data['s_account_manager'] != '') {

                $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $filter_data['s_account_manager'] . "'";
                if ($where != '')
                    $where .= ' AND ';

                if (isset($_SESSION['search_payment_data']['search_account_id']) && $_SESSION['search_payment_data']['search_account_id'] != '') {

                    $where .= " ph.account_id IN('" . $_SESSION['search_payment_data']['search_account_id'] . "')";
                } else {
                    $where .= " ph.account_id IN(" . $sub_sql . ")";
                }
            } elseif (isset($filter_data['s_parent_account_id'])) {
                $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $filter_data['s_parent_account_id'] . "' ";
                if ($where != '')
                    $where .= ' AND ';

                if (isset($_SESSION['search_payment_data']['search_account_id']) && $_SESSION['search_payment_data']['search_account_id'] != '') {

                    $where .= " ph.account_id IN('" . $_SESSION['search_payment_data']['search_account_id'] . "')";
                } else {
                    $where .= " ph.account_id IN(" . $sub_sql . ")";
                }
            }



            //-------------------------------------------------------------------------	//


            $sql = "SELECT SQL_CALC_FOUND_ROWS 				
				ph.payment_id, ph.account_id, ph.payment_option_id, ph.amount, ph.paid_on, ph.notes, ph.created_by, ph.create_dt, 
				po.display_text payment_option, po.term option_id_name
					FROM " . $this->db->dbprefix('payment_history') . " ph LEFT JOIN " . $this->db->dbprefix('sdr_terms') . " po ON ph.payment_option_id=po.term WHERE $where";



            $range = explode(' - ', $filter_data['date_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];

            if (trim($filter_data['date_range']) != '')
                $sql .= " AND ph.paid_on >= '" . $start_dt . "' AND ph.paid_on <= '" . $end_dt . "' ";

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY `paid_on` DESC ";
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

            $final_return_array['result'] = array();
            foreach ($query->result_array() as $row) {
                $payment_id = $row['payment_id'];
                $final_return_array['result'][$payment_id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Payment History fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

}
