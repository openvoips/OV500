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
//OV500 Version 1.0.3
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

class Failedcdr_mod extends CI_Model {

    public $cdr_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_data($order_by, $limit_to, $limit_from, $filter_data, $option_param = array()) {
        $db2 = $this->load->database('cdrdb', TRUE);
        try {
            $sql = "SELECT cdr_id,
					user_account_id 'Account',user_src_ip 'SRC-IP',user_src_caller 'SRC-CLI',user_src_callee 'SRC-DST',	
					user_tariff_id_name 'User-Tariff',user_prefix 'Prefix',user_destination 'Destination',
					reseller1_account_id 'R1-Account',reseller1_tariff_id_name 'R1-Tariff',	reseller1_prefix 'R1-Prefix',reseller1_destination 'R1-DST',	
					reseller2_account_id 'R2-Account',reseller2_tariff_id_name 'R2-Tariff', reseller2_prefix 'R2-Prefix',reseller2_destination 'R2-DST',
					reseller3_account_id 'R3-Account',reseller3_tariff_id_name 'R3-Tariff',	reseller3_prefix 'R3-Prefix',reseller3_destination 'R3-DST',
					carrier_dialplan_id_name 'Routing',
					carrier_id_name 'Carrier',carrier_tariff_id_name 'C-Tariff', carrier_prefix 'C-Prefix',carrier_destination 'C-Destination',
					carrier_gateway_ipaddress 'C-IP',carrier_src_caller 'USER-CLI',carrier_src_callee 'User-DST',
					carrier_dst_caller 'C-CLI',carrier_dst_callee 'C-DST',start_stamp 'Start Time',duration 'Duration',billsec 'Org-Duration',Q850CODE,SIPCODE,concat(fscause,'<br>',fs_errorcode) 'FS-Cause',hangupby
					FROM " . $db2->dbprefix('cdrs') . " ";

            $where = 'where billsec = 0';

            if (count($filter_data) > 0) {
                $search_data = array($user => $_SESSION['search_data']['s_cdr_user_account'],
                    'user_src_callee' => $_SESSION['search_data']['s_cdr_dialed_no'],
                    'carrier_dst_callee' => $_SESSION['search_data']['s_cdr_carrier_dst_no'],
                    'user_src_caller' => $_SESSION['search_data']['s_cdr_user_cli'],
                    'carrier_dst_caller' => $_SESSION['search_data']['s_cdr_carrier_cli'],
                    'carrier_carrier_id_name' => $_SESSION['search_data']['s_cdr_carrier'],
                    'carrier_gateway_ipaddress' => $_SESSION['search_data']['s_cdr_carrier_ip'],
                    'user_src_ip' => $_SESSION['search_data']['s_cdr_user_ip'],
                    'carrier_duration' => $_SESSION['search_data']['s_cdr_call_duration'],
                    'start_stamp' => $_SESSION['search_data']['s_cdr_start_dt'],
                    'end_stamp' => $_SESSION['search_data']['s_cdr_end_dt']
                );

                foreach ($filter_data as $key => $value) {

                    if (in_array($key, array('logged_user_type', 'logged_user_account_id', 'logged_user_level'))) {
                        
                    } elseif ($value != '') {
                        if ($key == 'start_stamp' && $value != '')
                            $where .= " AND $key >='" . $value . "' ";
                        else if ($key == 'end_stamp' && $value != '')
                            $where .= " AND $key <='" . $value . "' ";
                        else if ($key == 'user_src_callee' && $value != '')
                            $where .= " AND $key LIKE '%" . $value . "%' ";
                        else if ($key == 'carrier_dst_callee' && $value != '')
                            $where .= " AND $key LIKE '%" . $value . "%' ";
                        else if ($key == 'user_src_caller' && $value != '')
                            $where .= " AND $key LIKE '%" . $value . "%' ";
                        else if ($key == 'carrier_dst_caller' && $value != '')
                            $where .= " AND $key LIKE '%" . $value . "%' ";
                        elseif ($key == 'carrier_carrier_id_name' && $value != '')
                            $where .= " AND $key LIKE '%" . $value . "%' ";
                        else
                            $where .= " AND $key ='" . $value . "' ";
                    }
                }

                if (isset($filter_data['logged_user_type']) && isset($filter_data['logged_user_account_id']) && isset($filter_data['logged_user_level']) && $filter_data['logged_user_type'] == 'RESELLER' && in_array($filter_data['logged_user_level'], array(1, 2, 3))) {
                    $level = $filter_data['logged_user_level'];
                    $field_name = 'reseller' . $level . '_account_id';

                    $where .= " AND `" . $field_name . "` = '" . $filter_data['logged_user_account_id'] . "'";
                } elseif (isset($filter_data['logged_user_type']) && isset($filter_data['logged_user_account_id']) && $filter_data['logged_user_type'] == 'CUSTOMER')
                    $where .= " AND user_account_id='" . $filter_data['logged_user_account_id'] . "'";
            }

            $sql = $sql . $where . " ORDER BY `cdr_id` Desc ";

            // for export. $limit_to and $limit_from coming blank
            if ($limit_to == '' && $limit_from == '')
                $sql .= " LIMIT 4000";
            else
                $sql .= " LIMIT $limit_to, $limit_from";

            // echo $sql;

            $query = $db2->query($sql);

            if (!$query) {
                $error_array = $db2->error();
                throw new Exception($error_array['message']);
            }

            $sql = "select cdr_id from " . $db2->dbprefix('cdrs') . " " . $where . " LIMIT 4000";  //echo $sql;
            $query1 = $db2->query($sql);
            $rows = $query1->result_array();
            //print_r($rows);		
            $final_return_array['total'] = count($rows);
            $final_return_array['result'] = $query->result_array();
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'CDRs fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_data_total_count() {
        return $this->total_count;
    }

}

?>