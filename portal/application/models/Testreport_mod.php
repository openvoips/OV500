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

class Testreport_mod extends CI_Model {

    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_cdr_daily_usage($search_data, $limit_to = '', $limit_from = '') {
        try {


            $group_by = '';
            $where = '';
            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if (in_array($key, array('g_account_id', 'g_rec_date', 'g_rec_month'))) {
                            if ($value == 'Y') {
                                if ($group_by != '')
                                    $group_by .= ', ';
                                if ($key == 'g_account_id')
                                    $group_by .= 'account_id';
                                if ($key == 'g_rec_date')
                                    $group_by .= 'action_date';
                                if ($key == 'g_rec_month') {
                                    $group_by .= " DATE_FORMAT(action_date, '%Y-%m') ";
                                }
                            }

                            continue;
                        }


                        if ($key == 'currency_id') {
                            if ($where != '')
                                $where .= ' AND ';
                            $where .= " user_currency_id ='" . $value . "' ";
                        }
                        elseif ($key == 'account_id') {
                            if ($where != '')
                                $where .= ' AND ';
                            $where .= " $key ='" . $value . "' ";
                        }
                        elseif (in_array($key, array('s_account_manager', 's_parent_account_id', 's_superagent', 'am_under_sm'))) {
                            continue;
                        } elseif ($key == 'record_date') {
                            if ($where != '')
                                $where .= ' AND ';
                            $range = explode(' - ', $search_data['record_date']);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $where .= " action_date BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
                        } else
                            $where .= " $key LIKE '%" . $value . "%' ";
                    }
                }
            }




            if ($group_by != '') {
                $group_by = ' GROUP BY ' . $group_by;

                $sql = "SELECT SQL_CALC_FOUND_ROWS 				
				id,
				account_id, 
				company_name,
				user_currency_id currency_id,
				user_currency_id_name currency,
				
				action_date record_date, 
				DATE_FORMAT(action_date, '%m-%Y') record_date_month,										
				
				SUM(ROUND(asr,2)) 'asr_out', 
				SUM(ROUND(acd,2)) 'acd_out',
				
				ROUND(SUM(answeredcalls)) 'calls_out',
				ROUND(SUM(account_duration)/60) 'mins_out', 
				ROUND(SUM(callcost_net),2) 'customer_cost_out', 
				ROUND(SUM(callcost_net_carrier),2) 'carrier_cost_out', 					
								
				ROUND(SUM(answeredcalls_in)) 'calls_in', 
				ROUND(SUM(account_duration_in)/60) 'mins_in', 
				ROUND(SUM(callcost_net_in),2) 'customer_cost_in', 
				ROUND(SUM(callcost_net_carrier_in),2) 'carrier_cost_in', 				
				
				ROUND(SUM(account_cost),2) usercost_out,
				ROUND(SUM(account_cost_in),2) usercost_in,			
				
ROUND(SUM(did_extra_channel_cost_net + did_rental_cost_net + did_setup_cost_net),2) 'did_setup_rental_customer_cost', 
ROUND(SUM(did_extra_channel_cost_net_carrier + did_rental_cost_net_carrier + did_setup_cost_net_carrier),2) 'did_setup_rental_carrier_cost', 
ROUND(SUM(hosteddialer_cost_net + ukclicost_net + usaclicost_net),2) 'cli_locatization_hosteddialer_customer_cost', 
ROUND(SUM(hosteddialer_cost_net_carrier + usaclicost_net_carrier + ukclicost_net_carrier),2) 'cli_locatization_hosteddialer_supplier_cost', 				
								
				
				ROUND(SUM(callcost_net - callcost_net_carrier), 2) 'profit_out',
				ROUND(
				SUM(
				(callcost_net_in + did_extra_channel_cost_net + did_rental_cost_net + did_setup_cost_net) - (callcost_net_carrier_in + did_extra_channel_cost_net_carrier + did_rental_cost_net_carrier + did_setup_cost_net_carrier)
				),2) 'profit_in' , 
				
				SUM(credit) credit_added,
				SUM(credit_remove) credit_remove,
				SUM(payment) payment
				
				
				  FROM " . $this->db->dbprefix('customer_daily_usages') . " ";

                $orderby = ' ORDER BY record_date_month desc ';
                /*

                  SUM(mins_out) mins_out,SUM(calls_out) calls_out,
                  round((SUM(mins_out)* 60) / SUM(calls_out),0) acd_out,
                  round((SUM(calls_out)/(SUM(calls_out/asr_out)*100))*100,2) asr_out,
                  SUM(usercost_out) usercost_out,SUM(carriercost_out) carriercost_out,SUM(profit_out) profit_out,SUM(calls_in) calls_in,SUM(mins_in) mins_in,
                  SUM(usercost_in) usercost_in,SUM(carriercost_in) carriercost_in,SUM(did_rental_user) did_rental_user,SUM(did_setup_user) did_setup_user,
                  SUM(didrental_carrier) didrental_carrier,	SUM(didsetup_carrier) didsetup_carrier,SUM(other_services) other_services,
                  SUM(profit_in) profit_in,
                  SUM(total_profit) total_profit,

                  SUM(payment) payment,SUM(reimburse) reimburse,SUM(credit_added) credit_added,SUM(credit_remove) credit_remove
                  ,record_date, DATE_FORMAT(record_date, '%m-%Y') record_date_month
                 */
            } else {
                $sql = "SELECT SQL_CALC_FOUND_ROWS 
				id,
				account_id, 
				company_name,
				managername,
				
				user_currency_id currency_id,
				user_currency_id_name currency,
				
				action_date record_date, 
				DATE_FORMAT(action_date, '%m-%Y') record_date_month,
				
				ROUND(account_cost,2) usercost_out,
				ROUND(account_cost_in,2) usercost_in,				
				 
				ROUND(asr,2) 'asr_out', 
				ROUND(acd,2) 'acd_out', 
				
				ROUND(answeredcalls) 'calls_out',
				ROUND(account_duration/60) 'mins_out', 
				ROUND(callcost_net,2) 'customer_cost_out', 
				ROUND(callcost_net_carrier,2) 'carrier_cost_out',				
								
				ROUND(answeredcalls_in) 'calls_in', 
				ROUND(account_duration_in/60) 'mins_in', 
				ROUND(callcost_net_in,2) 'customer_cost_in', 
				ROUND(callcost_net_carrier_in,2) 'carrier_cost_in', 				
				
				ROUND(callcost_net - callcost_net_carrier, 2) 'profit_out',				
				
				ROUND((did_extra_channel_cost_net + did_rental_cost_net + did_setup_cost_net),2) 'did_setup_rental_customer_cost', 
				ROUND((did_extra_channel_cost_net_carrier + did_rental_cost_net_carrier + did_setup_cost_net_carrier),2) 'did_setup_rental_carrier_cost', 
				ROUND((hosteddialer_cost_net + ukclicost_net + usaclicost_net),2) 'cli_locatization_hosteddialer_customer_cost', 
				ROUND((hosteddialer_cost_net_carrier + usaclicost_net_carrier + ukclicost_net_carrier),2) 'cli_locatization_hosteddialer_supplier_cost' , 
				
				ROUND((callcost_net_in + did_extra_channel_cost_net + did_rental_cost_net + did_setup_cost_net) - (callcost_net_carrier_in + did_extra_channel_cost_net_carrier + did_rental_cost_net_carrier + did_setup_cost_net_carrier),2) 'profit_in' , 
				
				credit credit_added,
				credit_remove,
				payment  						
				
				FROM " . $this->db->dbprefix('customer_daily_usages') . " ";

                $orderby = ' ORDER BY id desc ';
            }

            /////////////
            if (isset($search_data['s_superagent']) && $search_data['s_superagent'] != '') {
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['s_superagent'] . "'";
                if (isset($search_data['am_under_sm']) && $search_data['am_under_sm'] != '') {
                    $sub_sub_sql .= " AND user_access_id_name='" . $search_data['am_under_sm'] . "'";
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
                $where .= " account_id IN(" . $account_id_str . ")";
            }
            elseif (isset($search_data['s_account_manager']) && $search_data['s_account_manager'] != '') {

                $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['s_account_manager'] . "'";
                if ($where != '')
                    $where .= ' AND ';
                $where .= " account_id IN(" . $sub_sql . ")";
            }
            elseif (isset($search_data['s_parent_account_id'])) {   //&& $search_data['s_parent_account_id']!=''
                $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $search_data['s_parent_account_id'] . "' ";
                if ($where != '')
                    $where .= ' AND ';
                $where .= " account_id IN(" . $sub_sql . ")";
            }
            ////////////






            if ($where != '') {
                $sql = $sql . ' WHERE ' . $where;
            }


            if ($group_by != '')
                $group_by .= ', currency  ';


            $query = $sql . $group_by . $orderby;

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $query .= " LIMIT $limit_from, $limit_to";
            else
                $query .= "";


            echo $query; //die;
            $result = $this->db->query($query);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;

            $return['result'] = $result->result_array();
            $return['status'] = 'success';
            $return['sql'] = $query;

            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function api_analytics_cdr_in($search_data, $limit_to = '', $limit_from = '') { //print_r($search_data);
        try {
            $DB1 = $this->load->database('cdrdb', true);

            $range = explode(' - ', $search_data['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];

            $sql = "SELECT SQL_CALC_FOUND_ROWS cdr_id, user_account_id Account,user_company_name, user_src_caller 'SRC-CLI', user_src_callee 'SRC-DST', user_src_ip 'SRC-IP',user_incodecs 'Incoming-Codecs',carrier_outcodecs 'Outgoing-Codecs',call_codecs 'Call\'s-Codec',user_tariff_id_name 'User-Tariff',	user_prefix 'Prefix', user_destination 'Destination', user_duration 'Duration',
			user_callcost_total 'Cost',	reseller1_account_id 'R1-Account',	reseller1_tariff_id_name 'R1-Tariff',reseller1_duration 'R1-Duration',
			reseller1_callcost_total 'R1-Cost',	reseller2_account_id 'R2-Account',	reseller2_tariff_id_name 'R2-Tariff', reseller2_duration 'R2-Duration',
			reseller2_callcost_total 'R2-Cost',	reseller3_account_id 'R3-Account',	reseller3_tariff_id_name 'R3-Tariff', reseller3_duration 'R3-Duration',
			reseller3_callcost_total 'R3-Cost', carrier_dialplan_id_name 'Routing',	carrier_carrier_id_name 'Carrier', carrier_gateway_ipaddress 'C-IP',
			carrier_src_caller 'USER-CLI',	carrier_src_callee 'User-DST', carrier_dst_caller 'C-CLI', carrier_dst_callee 'C-DST', carrier_tariff_id_name 'C-Tariff',
			carrier_prefix 'C-Prefix',	carrier_destination 'C-Destination', carrier_duration 'C-Duration',	carrier_callcost_total 'C-Cost',
			billsec 'Org-Duration',	start_time 'Start Time', answer_time 'Answer Time',	end_time 'End time', Q850CODE, SIPCODE , hangupby
			FROM " . $DB1->dbprefix('bill_cdrs_incoming') . " WHERE 1 ";

            if (trim($search_data['s_cdr_user_account']) != '') {
                if ($search_data['s_cdr_user_type'] == 'U')
                    $sql .= " AND user_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R1')
                    $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R2')
                    $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R3')
                    $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
            }

            if (trim($search_data['s_cdr_dialed_no']) != '')
                $sql .= " AND user_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
            if (trim($search_data['s_cdr_carrier_dst_no']) != '')
                $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
            if (trim($search_data['s_cdr_user_cli']) != '')
                $sql .= " AND user_src_caller like '" . trim($search_data['s_cdr_user_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier_cli']) != '')
                $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier']) != '')
                $sql .= " AND carrier_carrier_id_name like '" . trim($search_data['s_cdr_carrier']) . "%' ";
            if (trim($search_data['s_cdr_carrier_ip']) != '')
                $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
            if (trim($search_data['s_cdr_user_ip']) != '')
                $sql .= " AND user_src_ip = '" . trim($search_data['s_cdr_user_ip']) . "' ";


            if (trim($search_data['s_cdr_call_duration']) != '') {

                if (trim($search_data['s_cdr_call_duration_range']) == 'gt') {

                    $sql .= " AND carrier_duration > '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'ls') {

                    $sql .= " AND carrier_duration < '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'gteq') {

                    $sql .= " AND carrier_duration >= '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'lseq') {

                    $sql .= " AND carrier_duration <= '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'eq') {

                    $sql .= " AND carrier_duration = '" . trim($search_data['s_cdr_call_duration']) . "' ";
                }
            }

            if (trim($search_data['s_time_range']) != '')
                $sql .= " AND start_time >= '" . $start_dt . "' AND end_time <= '" . $end_dt . "' ";


            /* ------------------------------ */
            if (trim($search_data['s_cdr_user_company_name']) != '')
                $sql .= " AND user_company_name LIKE '%" . trim($search_data['s_cdr_user_company_name']) . "%' ";

            /* ------------------------------ */


            if (isset($search_data['s_superagent']) && $search_data['s_superagent'] != '') {
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['s_superagent'] . "'";

                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;

                $sql .= " AND user_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_account_manager']) && $search_data['s_account_manager'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['s_account_manager'] . "'";

                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql .= " AND user_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                /* $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM ".$this->db->dbprefix('user')." WHERE parent_account_id='".$search_data['s_account_manager']."'";						
                  /////////////
                  $query = $this->db->query($sub_sql);
                  if(!$query)
                  {
                  $error_array = $this->db->error();
                  throw new Exception($error_array['message']);
                  }
                  $row =  $query->row();
                  $account_id_str = $row->account_ids;
                  /////////////
                  $sql .=" AND user_account_id IN(".$account_id_str.")"; */
            }


            $group_by = '';
            $orderby = ' order by cdr_id desc ';

            $query = $sql . $group_by . $orderby;

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $query .= " LIMIT $limit_from, $limit_to";
            else
                $query .= " LIMIT 2000";
            //echo 	$query;
            $result = $DB1->query($query);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $DB1->query($sql);
            $row_count = $query_count->row();
            $return['all_total'] = $row_count->total;

            $return['total'] = $result->num_rows();
            $return['result'] = $result->result_array();
            $return['status'] = 'success';
            $return['message'] = 'Result fetched successfully';

            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function api_analytics_cdr_failed_in($search_data, $limit_to = '', $limit_from = '') {
        try {

            $DB1 = $this->load->database('cdrdb', true);

            $range = explode(' - ', $search_data['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];


            $sql = "SELECT SQL_CALC_FOUND_ROWS cdr_id, 
					user_account_id 'Account',user_company_name,user_src_ip 'SRC-IP',user_src_caller 'SRC-CLI',user_src_callee 'SRC-DST',
					user_incodecs 'Incoming-Codecs',carrier_outcodecs 'Outgoing-Codecs',call_codecs 'Call\'s-Codec',user_tariff_id_name 'User-Tariff',user_prefix 'Prefix',user_destination 'Destination',
					reseller1_account_id 'R1-Account',reseller1_tariff_id_name 'R1-Tariff',	reseller1_prefix 'R1-Prefix',reseller1_destination 'R1-DST',	
					reseller2_account_id 'R2-Account',reseller2_tariff_id_name 'R2-Tariff', reseller2_prefix 'R2-Prefix',reseller2_destination 'R2-DST',
					reseller3_account_id 'R3-Account',reseller3_tariff_id_name 'R3-Tariff',	reseller3_prefix 'R3-Prefix',reseller3_destination 'R3-DST',
					carrier_dialplan_id_name 'Routing',
					carrier_id_name 'Carrier',carrier_tariff_id_name 'C-Tariff', carrier_prefix 'C-Prefix',carrier_destination 'C-Destination',
					carrier_gateway_ipaddress 'C-IP',carrier_src_caller 'USER-CLI',carrier_src_callee 'User-DST',
					carrier_dst_caller 'C-CLI',carrier_dst_callee 'C-DST',start_stamp 'Start Time',duration 'Duration',billsec 'Org-Duration',Q850CODE,SIPCODE,concat(fscause,'<br>',fs_errorcode) 'FS-Cause',hangupby
					FROM " . $DB1->dbprefix('cdrs_incoming') . " WHERE 1 ";


            if (trim($search_data['s_cdr_user_account']) != '') {
                if ($search_data['s_cdr_user_type'] == 'U')
                    $sql .= " AND user_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R1')
                    $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R2')
                    $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R3')
                    $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
            }

            if (trim($search_data['s_cdr_dialed_no']) != '')
                $sql .= " AND user_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
            if (trim($search_data['s_cdr_carrier_dst_no']) != '')
                $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
            if (trim($search_data['s_cdr_user_cli']) != '')
                $sql .= " AND user_src_caller like '" . trim($search_data['s_cdr_user_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier_cli']) != '')
                $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier']) != '')
                $sql .= " AND carrier_id_name like '" . trim($search_data['s_cdr_carrier']) . "%' ";


            if (trim($search_data['s_cdr_carrier_ip']) != '')
                $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
            if (trim($search_data['s_cdr_user_ip']) != '')
                $sql .= " AND user_src_ip = '" . trim($search_data['s_cdr_user_ip']) . "' ";
            if (trim($search_data['s_cdr_sip_code']) != '')
                $sql .= " AND SIPCODE = '" . trim($search_data['s_cdr_sip_code']) . "' ";
            if (trim($search_data['s_cdr_Q850CODE']) != '')
                $sql .= " AND Q850CODE = '" . trim($search_data['s_cdr_Q850CODE']) . "' ";
            if (trim($search_data['s_time_range']) != '')
                $sql .= " AND start_stamp >= '" . $start_dt . "' AND end_stamp <= '" . $end_dt . "' ";


            /* ------------------------------ */
            if (trim($search_data['s_cdr_user_company_name']) != '')
                $sql .= " AND user_company_name LIKE '%" . trim($search_data['s_cdr_user_company_name']) . "%' ";

            /* ------------------------------ */

            if (isset($search_data['s_superagent']) && $search_data['s_superagent'] != '') {
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['s_superagent'] . "'";

                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;

                $sql .= " AND user_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_account_manager']) && $search_data['s_account_manager'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['s_account_manager'] . "'";

                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql .= " AND user_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                
            }


            $orderby = ' ORDER BY cdr_id DESC ';
            $query = $sql . $orderby;
            //echo $query;
            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $query .= " LIMIT $limit_from, $limit_to";
            else
                $query .= " LIMIT 2000";

            //echo $query;
            $result = $DB1->query($query);

            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $DB1->query($sql);
            $row_count = $query_count->row();
            $return['all_total'] = $row_count->total;


            $return['total'] = $result->num_rows();
            $return['result'] = $result->result_array();
            $return['status'] = 'success';
            $return['message'] = 'Result fetched successfully';

            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function sdr_statement($account_id, $filter_data = array()) {
        $final_return_array = array();
        try {
            $sql = "SELECT
						switch_user_sdr.rule_type,
						switch_user_sdr.action_date,
						switch_user_sdr.service_number,
						switch_user_sdr.total_cost,
						switch_user_sdr.service_startdate,
						switch_user_sdr.service_stopdate
						FROM " . $this->db->dbprefix('user_sdr') . "
						WHERE `account_id`='" . $account_id . "' ";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'yearmonth') {
                        $sql .= " AND $key ='" . $value . "' ";
                    } elseif ($key == 'action_date') {
                        $sql .= " AND DATE_FORMAT(action_date, '%Y-%m-%d')='" . $value . "' ";
                    } elseif ($value != '') {
                        $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            $sql .= " ORDER BY `action_date` ASC ";
            //echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = $query->result_array();

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'SDR statement fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function sdr_summary($filter_data) {
        $final_return_array = array();
        try {
            $sql = "SELECT account_id, rule_type, DATE_FORMAT(`action_date`,'%d/%m/%Y') date_formatted, SUM(total_cost) sum_total_cost, SUM(total_seller_cost) sum_total_seller_cost
			FROM " . $this->db->dbprefix('user_sdr') . "
			WHERE 
			 rule_type IN('DAILYUSAGE', 'DAILYUSAGEIN', 'DIDEXTRACHRENTAL', 'DIDRENTAL', 'DIDSETUP', 'TARIFFCHARGES', 'ADDBALANCE', 'REMOVEBALANCE', 'ADDCREDIT', 'REMOVECREDIT')
			AND (total_cost >0 OR total_seller_cost>0)";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'account_manager') {
                        $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $value . "'";
                        $sql .= " AND account_id IN(" . $sub_sql . ")";
                    } elseif ($key == 'parent_account_id') {
                        $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $value . "'";
                        $sql .= " AND account_id IN(" . $sub_sql . ")";
                    } elseif ($key == 'yearmonth') {
                        $sql .= " AND DATE_FORMAT(action_date, '%Y-%m')='" . $value . "' ";
                    } elseif ($value != '') {
                        $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }

            $sql .= " GROUP BY account_id, DATE_FORMAT(`action_date`,'%d %c %Y'), rule_type";

            $sql .= " ORDER BY `account_id` , date_formatted ";
            //echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $row_count = $query->row();

            $final_return_array['result'] = array();

            if ($row_count > 0) {
                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $rule_type = $row['rule_type'];
                    $date_formatted = $row['date_formatted'];
                    $sum_total_cost = $row['sum_total_cost'];

                    $final_return_array['result'][$account_id][$date_formatted][$rule_type] = $row;
                }
            }



            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'SDR summary fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function call_statistics($filter_data = array()) {

        $final_return_array = array();
        try {
            $sql = "SELECT 
					user_account_id,user_company_name,
					user_destination 'destination', 
					COUNT(cdr_id) 'connected_calls', 
					SUM(user_duration) 'duration', 
					SUM(user_callcost_total) 'cost' 
					FROM " . $this->db->dbprefix('bill_cdrs') . "
					WHERE 1 ";


            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($key == 'account_manager' && $value != '') {
                        $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $value . "'";

                        /////////////
                        $query = $this->db->query($sub_sql);
                        if (!$query) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $row = $query->row();
                        $account_id_str = $row->account_ids;
                        /////////////
                        $sql .= " AND user_account_id IN(" . $account_id_str . ")";
                    } elseif ($key == 'parent_account_id' && $value != '') {
                        $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $value . "'";
                        /////////////
                        $query = $this->db->query($sub_sql);
                        if (!$query) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $row = $query->row();
                        $account_id_str = $row->account_ids;
                        /////////////
                        $sql .= " AND user_account_id IN(" . $account_id_str . ")";
                    } elseif ($key == 'action_month' && $value != '') {
                        $sql .= " AND DATE_FORMAT(end_time, '%Y-%m')='" . $value . "' ";
                    } elseif ($key == 'action_date' && $value != '') {
                        $sql .= " AND DATE_FORMAT(end_time, '%Y-%m-%d')='" . $value . "' ";
                    } elseif ($key == 'user_account_id' && $value != '') {
                        $sql .= " AND user_account_id='" . $value . "' ";
                    } else if ($key == 'user_company_name' && $value != '') {

                        $sql .= " AND user_company_name LIKE '%" . $value . "%' ";
                    }
                }
            }

            if (isset($filter_data['groupby_account']) && $filter_data['groupby_account'] == 'Y')
                $sql .= " GROUP BY user_account_id ORDER BY Cost desc";
            else
                $sql .= " GROUP BY user_account_id, Destination ORDER BY Cost desc";
            //echo $sql;
            $DB1 = $this->load->database('cdrdb', true);
            $query = $DB1->query($sql);
            if (!$query) {
                $error_array = $DB1->error();
                throw new Exception($error_array['message']);
            }
            $final_return_array['result'] = $query->result_array();

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Call statistics fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function api_analytics_cdr_failed($search_data, $limit_to = '', $limit_from = '') {  //print_r($search_data);
        try {
            $DB1 = $this->load->database('cdrdb', true);

            $range = explode(' - ', $search_data['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];

            //
            $sql = "SELECT SQL_CALC_FOUND_ROWS
					cdr_id, 
					user_account_id 'Account',user_company_name,user_src_ip 'SRC-IP',user_src_caller 'SRC-CLI',user_src_callee 'SRC-DST',user_incodecs 'Incoming-Codecs',carrier_outcodecs 'Outgoing-Codecs',call_codecs  'Call\'s-Codec',user_tariff_id_name 'User-Tariff',user_prefix 'Prefix',user_destination 'Destination',
					reseller1_account_id 'R1-Account',reseller1_tariff_id_name 'R1-Tariff',	reseller1_prefix 'R1-Prefix',reseller1_destination 'R1-DST',	
					reseller2_account_id 'R2-Account',reseller2_tariff_id_name 'R2-Tariff', reseller2_prefix 'R2-Prefix',reseller2_destination 'R2-DST',
					reseller3_account_id 'R3-Account',reseller3_tariff_id_name 'R3-Tariff',	reseller3_prefix 'R3-Prefix',reseller3_destination 'R3-DST',
					carrier_dialplan_id_name 'Routing',
					carrier_id_name 'Carrier',carrier_tariff_id_name 'C-Tariff', carrier_prefix 'C-Prefix',carrier_destination 'C-Destination',
					carrier_gateway_ipaddress 'C-IP',carrier_src_caller 'USER-CLI',carrier_src_callee 'User-DST',
					carrier_dst_caller 'C-CLI',carrier_dst_callee 'C-DST',start_stamp 'Start Time', end_stamp 'End Time', duration 'Duration',billsec 'Org-Duration',Q850CODE,SIPCODE,concat(fscause,'<br>',fs_errorcode) 'FS-Cause',hangupby
					FROM " . $DB1->dbprefix('cdrs') . " WHERE fscause != 'NORMAL_CLEARING' ";


            if (trim($search_data['s_cdr_user_account']) != '') {
                if ($search_data['s_cdr_user_type'] == 'U')
                    $sql .= " AND user_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R1')
                    $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R2')
                    $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R3')
                    $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
            }

            if (trim($search_data['s_cdr_dialed_no']) != '')
                $sql .= " AND user_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
            if (trim($search_data['s_cdr_carrier_dst_no']) != '')
                $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
            if (trim($search_data['s_cdr_user_cli']) != '')
                $sql .= " AND user_src_caller like '" . trim($search_data['s_cdr_user_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier_cli']) != '')
                $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier']) != '')
                $sql .= " AND carrier_id_name like '" . trim($search_data['s_cdr_carrier']) . "%' ";
            if (trim($search_data['s_cdr_carrier_ip']) != '')
                $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
            if (trim($search_data['s_cdr_user_ip']) != '')
                $sql .= " AND user_src_ip = '" . trim($search_data['s_cdr_user_ip']) . "' ";
            if (trim($search_data['s_cdr_sip_code']) != '')
                $sql .= " AND SIPCODE = '" . trim($search_data['s_cdr_sip_code']) . "' ";
            if (trim($search_data['s_cdr_Q850CODE']) != '')
                $sql .= " AND Q850CODE = '" . trim($search_data['s_cdr_Q850CODE']) . "' ";
            if (trim($search_data['s_cdr_fserrorcode']) != '')
                $sql .= " AND (fs_errorcode LIKE '%" . trim($search_data['s_cdr_fserrorcode']) . "%' OR fscause LIKE '%" . trim($search_data['s_cdr_fserrorcode']) . "%')";
            //	if(trim($search_data['s_time_range'])!='') $sql .= " AND start_stamp >= '".$start_dt."' AND end_stamp <= '".$end_dt."' ";
            if (trim($search_data['s_time_range']) != '')
                $sql .= " AND end_stamp BETWEEN '" . $start_dt . "' AND '" . $end_dt . "' ";

            /* ------------------------------ */
            if (trim($search_data['s_cdr_user_company_name']) != '')
                $sql .= " AND user_company_name LIKE '%" . trim($search_data['s_cdr_user_company_name']) . "%' ";

            /* ------------------------------ */

            if (isset($search_data['s_superagent']) && $search_data['s_superagent'] != '') {
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['s_superagent'] . "'";

                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;

                $sql .= " AND user_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_account_manager']) && $search_data['s_account_manager'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['s_account_manager'] . "'";

                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql .= " AND user_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                /* $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM ".$this->db->dbprefix('user')." WHERE parent_account_id='".$search_data['s_account_manager']."'";						
                  /////////////
                  $query = $this->db->query($sub_sql);
                  if(!$query)
                  {
                  $error_array = $this->db->error();
                  throw new Exception($error_array['message']);
                  }
                  $row =  $query->row();
                  $account_id_str = $row->account_ids;
                  /////////////
                  $sql .=" AND user_account_id IN(".$account_id_str.")"; */
            }

            $group_by = '';
            $orderby = ' order by cdr_id desc ';
            $query = $sql . $group_by . $orderby;


            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $query .= " LIMIT $limit_from, $limit_to";
            else
                $query .= " LIMIT 2000";

            //echo $query;
            $result = $DB1->query($query);

            ///find total
            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $DB1->query($sql);
            $row_count = $query_count->row();
            $return['all_total'] = $row_count->total;
            //////////////

            $return['total'] = $result->num_rows();
            $return['result'] = $result->result_array();
            $return['status'] = 'success';
            //var_dump($return);die;
            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function api_analytics_cdr($search_data, $limit_to = '', $limit_from = '') {
        try {
            $DB1 = $this->load->database('cdrdb', true);

            $range = explode(' - ', $search_data['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];

            //start_time 'Start Time', answer_time 'Answer Time',	end_time 'End time',
            $sql = "SELECT SQL_CALC_FOUND_ROWS
						cdr_id, user_account_id Account,user_company_name, user_src_caller 'SRC-CLI', user_src_callee 'SRC-DST', user_src_ip 'SRC-IP',user_incodecs 'Incoming-Codecs',carrier_outcodecs 'Outgoing-Codecs',call_codecs 'Call\'s-Codec',
						user_tariff_id_name 'User-Tariff',	user_prefix 'Prefix', user_destination 'Destination', user_duration 'Duration',
						user_callcost_total 'Cost',	reseller1_account_id 'R1-Account',	reseller1_tariff_id_name 'R1-Tariff',reseller1_duration 'R1-Duration',
						reseller1_callcost_total 'R1-Cost',	reseller2_account_id 'R2-Account',	reseller2_tariff_id_name 'R2-Tariff', reseller2_duration 'R2-Duration',
						reseller2_callcost_total 'R2-Cost',	reseller3_account_id 'R3-Account',	reseller3_tariff_id_name 'R3-Tariff', reseller3_duration 'R3-Duration',
						reseller3_callcost_total 'R3-Cost', carrier_dialplan_id_name 'Routing',	carrier_carrier_id_name 'Carrier', carrier_gateway_ipaddress 'C-IP',
						carrier_src_caller 'USER-CLI',	carrier_src_callee 'User-DST', carrier_dst_caller 'C-CLI', carrier_dst_callee 'C-DST', carrier_tariff_id_name 'C-Tariff',
						carrier_prefix 'C-Prefix',	carrier_destination 'C-Destination', carrier_duration 'C-Duration',	carrier_callcost_total 'C-Cost',
						billsec 'Org-Duration',	 Q850CODE, SIPCODE , hangupby,						
						IF(user_duration > 0, answer_time, start_time ) AS 'Start Time',
						IF(user_duration > 0, end_time, start_time ) AS 'End Time' 
						FROM " . $DB1->dbprefix('bill_cdrs') . " WHERE 1 ";


            if (trim($search_data['s_cdr_user_account']) != '') {
                if ($search_data['s_cdr_user_type'] == 'U')
                    $sql .= " AND user_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R1')
                    $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R2')
                    $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
                elseif ($search_data['s_cdr_user_type'] == 'R3')
                    $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
            }

            if (trim($search_data['s_cdr_dialed_no']) != '')
                $sql .= " AND user_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
            if (trim($search_data['s_cdr_carrier_dst_no']) != '')
                $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
            if (trim($search_data['s_cdr_user_cli']) != '')
                $sql .= " AND user_src_caller like '" . trim($search_data['s_cdr_user_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier_cli']) != '')
                $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier']) != '')
                $sql .= " AND carrier_carrier_id_name like '" . trim($search_data['s_cdr_carrier']) . "%' ";
            if (trim($search_data['s_cdr_carrier_ip']) != '')
                $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
            if (trim($search_data['s_cdr_user_ip']) != '')
                $sql .= " AND user_src_ip = '" . trim($search_data['s_cdr_user_ip']) . "' ";

            if (trim($search_data['s_cdr_call_duration']) != '') {

                if (trim($search_data['s_cdr_call_duration_range']) == 'gt') {

                    $sql .= " AND carrier_duration > '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'ls') {

                    $sql .= " AND carrier_duration < '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'gteq') {

                    $sql .= " AND carrier_duration >= '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'lseq') {

                    $sql .= " AND carrier_duration <= '" . trim($search_data['s_cdr_call_duration']) . "' ";
                } elseif (trim($search_data['s_cdr_call_duration_range']) == 'eq') {

                    $sql .= " AND carrier_duration = '" . trim($search_data['s_cdr_call_duration']) . "' ";
                }
            }

            if (trim($search_data['s_time_range']) != '') {
                //$sql .= " AND start_time >= '".$start_dt."' AND end_time <= '".$end_dt."' ";
                $sql .= " AND end_time BETWEEN '" . $start_dt . "' AND '" . $end_dt . "' ";
            }
            /* ------------------------------ */
            if (trim($search_data['s_cdr_user_company_name']) != '')
                $sql .= " AND user_company_name LIKE '%" . trim($search_data['s_cdr_user_company_name']) . "%' ";

            /* ------------------------------ */

            //////////////
            if (isset($search_data['s_superagent']) && $search_data['s_superagent'] != '') {
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['s_superagent'] . "'";

                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;

                $sql .= " AND user_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_account_manager']) && $search_data['s_account_manager'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['s_account_manager'] . "'";

                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql .= " AND user_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $search_data['s_parent_account_id'] . "'";
                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql .= " AND user_account_id IN(" . $account_id_str . ")";
            }

            ////////////////////

            $group_by = '';
            $orderby = ' order by cdr_id desc ';
            $query = $sql . $group_by . $orderby;

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $query .= " LIMIT $limit_from, $limit_to";
            else
                $query .= " LIMIT 2000";


            //echo $query;
            $result = $DB1->query($query);

            ///find total
            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $DB1->query($sql);
            $row_count = $query_count->row();
            $return['all_total'] = $row_count->total;
            //////////////

            $return['total'] = $result->num_rows();
            $return['result'] = $result->result_array();
            $return['status'] = 'success';


            //var_dump($return);die;
            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function accounting_billing($search_data, $limit_to = '', $limit_from = '') {

        try {
            $DB1 = $this->load->database('cdrdb', true);

            if (!isset($search_data['time_range'])) {
                throw new Exception('time range missing');
            }

            $range = explode(' - ', $search_data['time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];



            $sub_sql_where .= "  end_time BETWEEN '" . $start_dt . "' AND '" . $end_dt . "' ";
            $group_by = '';

            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'user_account_id' || $key == 'carrier_carrier_id_name') {
                            $sub_sql_where .= " AND $key ='" . $value . "' ";
                        } elseif ($key == 'group_by') {
                            $group_by .= $value;
                        } elseif ($value != '') {
                            
                        }
                    }
                }
            }

            $sql = "SELECT 
					end_time AS `date`,
					user_account_id AS `customer`,
					carrier_carrier_id_name AS `carrier`, 
					count(cdr_id) AS `answered_calls`,
					sum(user_duration)/60 AS `minute_usage_cost`,
					SUM(carrier_callcost_total_usercurrency) AS `carrier_cost`, 
					SUM(profit_usercurrency) AS `profit`, 
					user_user_currency_id AS `currency`					
					FROM " . $DB1->dbprefix('bill_cdrs') . " WHERE 1 ";

            $sql .= ' AND ' . $sub_sql_where;


            $group_by = ' GROUP BY ' . $group_by;
            $orderby = ' ';
            $sql = $sql . $group_by . $orderby;


            //echo $sql;
            $query = $DB1->query($sql);
            if (!$query) {
                $error_array = $DB1->error();
                throw new Exception($error_array['message']);
            }


            //////////////

            $return['result'] = $query->result_array();
            $return['status'] = 'success';
            //$return['sql'] = $sql;
            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function get_carrier_daily_usage($search_data, $limit_to = '', $limit_from = '') {
        try {

            $group_by = '';
            $where = '';

            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if (in_array($key, array('g_account_id', 'grp_destination', 'grp_calls_date'))) {
                            if ($value == 'Y') {
                                if ($group_by != '')
                                    $group_by .= ', ';

                                if ($key == 'g_account_id')
                                    $group_by .= 'carrier_account';
                                if ($key == 'grp_destination')
                                    $group_by .= 'destination';
                                if ($key == 'grp_calls_date') {
                                    $group_by .= "calls_date";
                                }
                            }
                            continue;
                        }

                        if ($key == 'carrier_account' || $key == 'carrier_currency_id') {
                            if ($where != '')
                                $where .= ' AND ';
                            $where .= " $key ='" . $value . "' ";
                        }
                        elseif (in_array($key, array('s_account_manager', 's_parent_account_id', 's_superagent'))) {
                            continue;
                        } elseif ($key == 'calls_date') {
                            if ($where != '')
                                $where .= ' AND ';
                            $range = explode(' - ', $search_data['calls_date']);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $where .= " calls_date BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
                        }
                        else {
                            if ($where != '')
                                $where .= ' AND ';
                            $where .= " $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
            }

            if ($group_by != '') {
                $group_by = " GROUP BY " . $group_by;

                $sql = "SELECT switch_carrier_daily_usage.carrier_account,
 switch_carrier_daily_usage.carrier_name,switch_carrier_daily_usage.prefix,switch_carrier_daily_usage.destination, switch_carrier_daily_usage.currency_name,
 round((sum(switch_carrier_daily_usage.answercalls)/sum(switch_carrier_daily_usage.totalcalls))*100,2) asr,
 round((sum(switch_carrier_daily_usage.out_minute) * 60) / sum(switch_carrier_daily_usage.answercalls),0) acd, 
 sum(switch_carrier_daily_usage.answercalls) answercalls, round(sum(switch_carrier_daily_usage.out_minute),0) out_minute,
 ROUND(sum(switch_carrier_daily_usage.carriercost),2) carriercost, sum(code402) code402 ,sum(code403 ) code403,
 sum(code404 ) code404,sum(code407) code407,sum( code500) code500,sum( code503) code503,sum( code487) code487,sum( code488) code488,sum( code501) code501,
 sum( code483) code483,sum( code410) code410,sum( code515) CCLimit ,sum( code486) code486,sum( code480) code480, 
 calls_date, DATE_FORMAT(calls_date, '%m-%Y') calls_date_month FROM " . $this->db->dbprefix('carrier_daily_usage') . "";

                $orderby = ' ORDER BY calls_date_month DESC ';
            } else {
                $sql = "select SQL_CALC_FOUND_ROWS carrier_account, carrier_name,prefix,destination,currency_name,asr,acd,answercalls,totalcalls,carriercost,out_minute,code402,
code403 ,code404 ,code407, code500, code503, code487, code488, code501, code483,code410, code515, code486, code480,calls_date, DATE_FORMAT(calls_date, '%m-%Y') calls_date_month FROM " . $this->db->dbprefix('carrier_daily_usage') . " ";

                $orderby = ' ORDER BY carrier_daily_usage_id DESC ';
            }

            /////////////
            if (isset($search_data['s_superagent']) && $search_data['s_superagent'] != '') {
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['s_superagent'] . "'";

                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;

                if ($where != '')
                    $where .= ' AND ';
                $where .= " account_id IN(" . $account_id_str . ")";
            }
            elseif (isset($search_data['s_account_manager']) && $search_data['s_account_manager'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['s_account_manager'] . "'";

                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;

                if ($where != '')
                    $where .= ' AND ';
                $where .= " account_id IN(" . $account_id_str . ")";
            }
            elseif (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $search_data['s_parent_account_id'] . "'";
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;

                if ($where != '')
                    $where .= ' AND ';
                $where .= " account_id IN(" . $account_id_str . ")";
            }
            ////////////


            if ($where != '') {
                $sql = $sql . ' WHERE ' . $where;
            }


            if ($group_by != '')
                $group_by .= ', currency_name  ';


            $query = $sql . $group_by . $orderby;

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $query .= " LIMIT $limit_from, $limit_to";
            else
                $query .= "";


            //echo $query; //die;
            $result = $this->db->query($query);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;


            $return['result'] = $result->result_array();
            $return['status'] = 'success';
            $return['sql'] = $query;


            return $return;
        } catch (Exception $e) {
            $return['status'] = 'failed';
            $return['message'] = $e->getMessage();
            return $return;
        }
    }

    function topup_daily($search_data) {
        $final_return_array = array();
        try {
            if (!isset($search_data['time_range'])) {
                throw new Exception('time range missing');
            }
            $range = explode(' - ', $search_data['time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];



            $sql = " SELECT payment_option_id, SUM(amount) sum_amount, DATE_FORMAT(`paid_on`,'%d-%m-%Y') date_formatted, u.user_currency_id 
			FROM " . $this->db->dbprefix('payment_history') . " ph INNER JOIN " . $this->db->dbprefix('user') . " u ON ph.account_id =u.account_id
			WHERE `payment_option_id` IN ('ADDBALANCE','REMOVEBALANCE') AND paid_on BETWEEN '$start_dt' AND '$end_dt'";
            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND ph.account_id ='" . $value . "'";
                        } elseif (in_array($key, array('s_account_manager', 's_parent_account_id', 's_superagent'))) {
                            continue;
                        }
                    }
                }
            }


            $where = '';
            if (isset($search_data['superagent']) && $search_data['superagent'] != '') {
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['superagent'] . "'";
                if (isset($search_data['am_under_sm']) && $search_data['am_under_sm'] != '') {
                    $sub_sub_sql .= " AND user_access_id_name='" . $search_data['am_under_sm'] . "'";
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

                $where .= " AND ph.account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['account_manager']) && $search_data['account_manager'] != '') {
                $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['account_manager'] . "'";

                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            } elseif (isset($search_data['parent_account_id']) && $search_data['parent_account_id'] != '') {
                $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $search_data['parent_account_id'] . "' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            } else {
                $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            }



            $sql .= $where;
            $sql .= " GROUP BY date_formatted, u.user_currency_id, payment_option_id ";
            $sql .= " ORDER BY paid_on";

            //echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $row_count = $query->row();

            $final_return_array['result'] = array();

            if ($row_count > 0) {
                foreach ($query->result_array() as $row) {
                    $payment_option_id = $row['payment_option_id'];
                    $sum_amount = $row['sum_amount'];
                    $date_formatted = $row['date_formatted'];
                    $user_currency_id = $row['user_currency_id'];

                    $final_return_array['result'][$user_currency_id][$date_formatted][$payment_option_id] = $row;
                }
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Topup report fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function topup_monthly($search_data) {
        $final_return_array = array();
        try {
            if (!isset($search_data['time_range'])) {
                throw new Exception('time range missing');
            }
            $range = explode(' - ', $search_data['time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];



            $sql = " SELECT payment_option_id, SUM(amount) sum_amount, DATE_FORMAT(`paid_on`,'%Y-%m') date_formatted, u.user_currency_id 
			FROM " . $this->db->dbprefix('payment_history') . " ph INNER JOIN " . $this->db->dbprefix('user') . " u ON ph.account_id =u.account_id
			WHERE `payment_option_id` IN ('ADDBALANCE','REMOVEBALANCE') AND paid_on BETWEEN '$start_dt' AND '$end_dt'";
            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND ph.account_id ='" . $value . "'";
                        } elseif (in_array($key, array('s_account_manager', 's_parent_account_id', 's_superagent'))) {
                            continue;
                        }
                    }
                }
            }


            $where = '';
            if (isset($search_data['superagent']) && $search_data['superagent'] != '') {
                /* $sub_sub_sql = "SELECT user_access_id_name FROM ".$this->db->dbprefix('user_access')." WHERE user_type='ACCOUNTMANAGER' AND superagent='".$search_data['superagent']."'";			

                  $sub_sql = "SELECT user_access_id_name FROM ".$this->db->dbprefix('user_access')." WHERE account_manager IN(".$sub_sub_sql.")";

                  $where .=" AND ph.account_id IN(".$sub_sql.")"; */


                ///////////////////
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['superagent'] . "'";
                if (isset($search_data['am_under_sm']) && $search_data['am_under_sm'] != '') {
                    $sub_sub_sql .= " AND user_access_id_name='" . $search_data['am_under_sm'] . "'";
                }
                $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";
                //	echo	$sub_sql.'<br><br>';	//die;

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

                $where .= " AND ph.account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['account_manager']) && $search_data['account_manager'] != '') {
                /* $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM ".$this->db->dbprefix('user_access')." WHERE account_manager='".$search_data['account_manager']."'";						

                  $query = $this->db->query($sub_sql);
                  if(!$query)
                  {
                  $error_array = $this->db->error();
                  throw new Exception($error_array['message']);
                  }
                  $row =  $query->row();
                  $account_id_str = $row->account_ids;
                  $where .=" AND ph.account_id IN(".$account_id_str.")";
                 */


                $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['account_manager'] . "'";

                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            } elseif (isset($search_data['parent_account_id']) && $search_data['parent_account_id'] != '') {
                /* $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM ".$this->db->dbprefix('user')." WHERE parent_account_id='".$search_data['parent_account_id']."'";						
                  $query = $this->db->query($sub_sql);
                  if(!$query)
                  {
                  $error_array = $this->db->error();
                  throw new Exception($error_array['message']);
                  }
                  $row =  $query->row();
                  $account_id_str = $row->account_ids;

                  $where .=" AND ph.account_id IN(".$account_id_str.")"; */


                $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $search_data['parent_account_id'] . "' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            } else {
                /* $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM ".$this->db->dbprefix('user')." WHERE parent_account_id !=''";						
                  $query = $this->db->query($sub_sql);
                  if(!$query)
                  {
                  $error_array = $this->db->error();
                  throw new Exception($error_array['message']);
                  }
                  $row =  $query->row();
                  $account_id_str = $row->account_ids;

                  $where .=" AND ph.account_id NOT IN(".$account_id_str.")"; */


                $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            }



            $sql .= $where;
            $sql .= " GROUP BY date_formatted, u.user_currency_id, payment_option_id ";
            $sql .= " ORDER BY paid_on";

            //echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $row_count = $query->row();

            $final_return_array['result'] = array();

            if ($row_count > 0) {
                foreach ($query->result_array() as $row) {
                    $payment_option_id = $row['payment_option_id'];
                    $sum_amount = $row['sum_amount'];
                    $date_formatted = $row['date_formatted'];
                    $user_currency_id = $row['user_currency_id'];

                    $final_return_array['result'][$user_currency_id][$date_formatted][$payment_option_id] = $row;
                }
            }



            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Topup summary fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function topup_customer($search_data) {
        $final_return_array = array();
        try {
            if (!isset($search_data['time_range'])) {
                throw new Exception('time range missing');
            }
            $range = explode(' - ', $search_data['time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];



            $sql = " SELECT payment_option_id, SUM(amount) sum_amount, account_id
			FROM " . $this->db->dbprefix('payment_history') . " ph 
			WHERE `payment_option_id` IN ('ADDBALANCE','REMOVEBALANCE') AND paid_on BETWEEN '$start_dt' AND '$end_dt'";
            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND ph.account_id ='" . $value . "'";
                        } elseif (in_array($key, array('s_account_manager', 's_parent_account_id', 's_superagent'))) {
                            continue;
                        }
                    }
                }
            }


            $where = '';
            if (isset($search_data['superagent']) && $search_data['superagent'] != '') {

                ///////////////////
                $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['superagent'] . "'";

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

                $where .= " AND ph.account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['account_manager']) && $search_data['account_manager'] != '') {


                $sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['account_manager'] . "'";

                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            } elseif (isset($search_data['parent_account_id']) && $search_data['parent_account_id'] != '') {


                $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $search_data['parent_account_id'] . "' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            } else {


                $sub_sql = "SELECT account_id FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            }



            $sql .= $where;
            $sql .= " GROUP BY account_id, payment_option_id ";
            $sql .= " ORDER BY account_id";

            //echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $row_count = $query->row();

            $final_return_array['result'] = array();

            if ($row_count > 0) {
                foreach ($query->result_array() as $row) { //payment_option_id, SUM(amount) sum_amount, account_id
                    $payment_option_id = $row['payment_option_id'];
                    $sum_amount = $row['sum_amount'];
                    $account_id = $row['account_id'];

                    $final_return_array['result'][$account_id][$payment_option_id] = $sum_amount;
                }
            }



            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Topup summary fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function old_api_analytics_cdr_in($search_data) {


        $DB1 = $this->load->database('cdrdb', true);

        $range = explode(' - ', $search_data['s_time_range']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);

        $start_dt = $range[0];
        $end_dt = $range[1];

        $sql = "SELECT cdr_id, user_account_id Account, user_src_caller 'SRC-CLI', user_src_callee 'SRC-DST', user_src_ip 'SRC-IP',
					user_tariff_id_name 'User-Tariff',	user_prefix 'Prefix', user_destination 'Destination', user_duration 'Duration',
					user_callcost_total 'Cost',	reseller1_account_id 'R1-Account',	reseller1_tariff_id_name 'R1-Tariff',reseller1_duration 'R1-Duration',
					reseller1_callcost_total 'R1-Cost',	reseller2_account_id 'R2-Account',	reseller2_tariff_id_name 'R2-Tariff', reseller2_duration 'R2-Duration',
					reseller2_callcost_total 'R2-Cost',	reseller3_account_id 'R3-Account',	reseller3_tariff_id_name 'R3-Tariff', reseller3_duration 'R3-Duration',
					reseller3_callcost_total 'R3-Cost', carrier_dialplan_id_name 'Routing',	carrier_carrier_id_name 'Carrier', carrier_gateway_ipaddress 'C-IP',
					carrier_src_caller 'USER-CLI',	carrier_src_callee 'User-DST', carrier_dst_caller 'C-CLI', carrier_dst_callee 'C-DST', carrier_tariff_id_name 'C-Tariff',
					carrier_prefix 'C-Prefix',	carrier_destination 'C-Destination', carrier_duration 'C-Duration',	carrier_callcost_total 'C-Cost',
                    billsec 'Org-Duration',	start_time 'Start Time', answer_time 'Answer Time',	end_time 'End time', Q850CODE, SIPCODE , hangupby
					FROM " . $DB1->dbprefix('bill_cdrs_incoming') . " WHERE 1 "; //bill_cdrs_incoming


        if (trim($search_data['s_cdr_user_account']) != '') {
            if ($search_data['s_cdr_user_type'] == 'U')
                $sql .= " AND user_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
            elseif ($search_data['s_cdr_user_type'] == 'R1')
                $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
            elseif ($search_data['s_cdr_user_type'] == 'R2')
                $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
            elseif ($search_data['s_cdr_user_type'] == 'R3')
                $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_user_account']) . "' ";
        }

        if (trim($search_data['s_cdr_dialed_no']) != '')
            $sql .= " AND user_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
        if (trim($search_data['s_cdr_carrier_dst_no']) != '')
            $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
        if (trim($search_data['s_cdr_user_cli']) != '')
            $sql .= " AND user_src_caller like '" . trim($search_data['s_cdr_user_cli']) . "%' ";
        if (trim($search_data['s_cdr_carrier_cli']) != '')
            $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
        if (trim($search_data['s_cdr_carrier']) != '')
            $sql .= " AND carrier_carrier_id_name like '" . trim($search_data['s_cdr_carrier']) . "%' ";
        if (trim($search_data['s_cdr_carrier_ip']) != '')
            $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
        if (trim($search_data['s_cdr_user_ip']) != '')
            $sql .= " AND user_src_ip = '" . trim($search_data['s_cdr_user_ip']) . "' ";



        if (trim($search_data['s_cdr_call_duration']) != '') {

            if (trim($search_data['s_cdr_call_duration_range']) == 'gt') {

                $sql .= " AND carrier_duration > '" . trim($search_data['s_cdr_call_duration']) . "' ";
            } elseif (trim($search_data['s_cdr_call_duration_range']) == 'ls') {

                $sql .= " AND carrier_duration < '" . trim($search_data['s_cdr_call_duration']) . "' ";
            } elseif (trim($search_data['s_cdr_call_duration_range']) == 'gteq') {

                $sql .= " AND carrier_duration >= '" . trim($search_data['s_cdr_call_duration']) . "' ";
            } elseif (trim($search_data['s_cdr_call_duration_range']) == 'lseq') {

                $sql .= " AND carrier_duration <= '" . trim($search_data['s_cdr_call_duration']) . "' ";
            } elseif (trim($search_data['s_cdr_call_duration_range']) == 'eq') {

                $sql .= " AND carrier_duration = '" . trim($search_data['s_cdr_call_duration']) . "' ";
            }
        }



        if (trim($search_data['s_time_range']) != '')
            $sql .= " AND start_time >= '" . $start_dt . "' AND end_time <= '" . $end_dt . "' ";


        $group_by = '';
        $orderby = ' order by cdr_id desc ';
        $query = $sql . $group_by . $orderby . " Limit 2000";
        //echo $query;
        die;
        $result = $DB1->query($query);
        $return['total'] = $result->num_rows();
        $return['result'] = $result->result_array();

        //var_dump($return);die;
        return $return;
    }

    function supplier_detail_audit($filter_data, $limit_to = '', $limit_from = '') {
        $final_return_array = array();
        try {


            //////////////////////////

            $connected_calls_where = "";
            if (isset($filter_data['record_date']) && $filter_data['record_date'] != '') {
                $value = $filter_data['record_date'];
                $range = explode(' - ', $value);
                $range_from = explode(' ', $range[0]);
                $range_to = explode(' ', $range[1]);
                $connected_calls_where .= " AND call_date BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
            }


            //if(rule_type in ('SUPPLIERSETUPDID','SUPPLIERRENTALDID','SUPPLIERNEWHOSTEDSERVERSETUP'), 'DID','SERVER') otherunit, 			
            $sql1 = "SELECT 
				'supplier_usage' as from_query,
				
				service_type otherunit,
				supplier_usage_outer.supplier_name, 	
				supplier_id, 			
				supplier_usage_outer.currency_id, 
				service_type , 
				supplier_reference,
				system_reference, 
				
				(SELECT supplier_usage_inner.service_status FROM " . $this->db->dbprefix('supplier_usage') . "  supplier_usage_inner WHERE supplier_usage_inner.supplier_reference = supplier_usage_outer.supplier_reference ORDER BY action_dt desc LIMIT 1) service_status, 
				MIN(start_date) start_date, 
				max(end_date) end_date, 
				quantity, 
				sum(if(rule_type in ('SUPPLIERSETUPDID','SUPPLIERNEWHOSTEDSERVERSETUP'), total_cost, 0)) as 'OneOffCharge', 
				sum(if(rule_type in ( 'SUPPLIERRENTALDID','SUPPLIERNEWHOSTEDSERVERRENTAL'), total_cost, 0)) as 'MonthlyCharge', 
				0 as 'UsageCharge', 
				0 as InMinute, 
				0 as OutMinute 
				FROM " . $this->db->dbprefix('supplier_usage') . " supplier_usage_outer 
				WHERE rule_type IN ('SUPPLIERSETUPDID','SUPPLIERRENTALDID','SUPPLIERNEWHOSTEDSERVERRENTAL','SUPPLIERNEWHOSTEDSERVERSETUP') ";


            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'record_date') {
                            $range = explode(' - ', $value);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $sql1 .= " AND action_dt BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
                        } elseif ($key == 'supplier') {
                            $sql1 .= " AND (supplier_id LIKE '%" . $value . "%' OR supplier_name LIKE '%" . $value . "%') ";
                        } elseif ($key == 'service_type') {
                            $sql1 .= " AND $key ='" . $value . "' ";
                        } elseif ($value != '') {
                            $sql1 .= " AND $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
            }
            $sql1 .= " GROUP BY supplier_reference, supplier_name ";
            ///////////////////////////////	

            $is_search = false;
            if ($filter_data['service_type'] == '' || $filter_data['service_type'] == 'VOIP') {
                $is_search = true;
            }

            if ($is_search) {
                $sql2 = " SELECT 
					'connected_calls_out' as from_query,
					'MINUTES' otherunit,
					suppliers.supplier_name,
					suppliers.supplier_id,  
					suppliers.currency_id, 
					'VoIP' as service_type, 
					destination as supplier_reference, 
					destination as system_reference, 
					'' as service_status, 
					min(call_date) as start_date, 
					max(call_date) as end_date, 
					SUM(carrier_duration) as quantity, 
					0 as 'OneOffCharge', 
					0 as 'Monthly Charge', 
					sum(carrier_callcost_total) as 'UsageCharge', 
					0 as InMinute, 
					sum(carrier_duration) as OutMinute 
					FROM " . $this->db->dbprefix('connected_calls') . " connected_calls INNER JOIN " . $this->db->dbprefix('carrier') . " carrier ON connected_calls.carrier_id = carrier.carrier_id_name 
												INNER JOIN " . $this->db->dbprefix('suppliers') . " suppliers  ON suppliers.supplier_id_name = carrier.supplier_id_name 
					WHERE calltype= 'OUT' ";


                if (count($filter_data) > 0) {
                    foreach ($filter_data as $key => $value) {
                        if ($value != '') {
                            if ($key == 'record_date') {
                                $range = explode(' - ', $value);
                                $range_from = explode(' ', $range[0]);
                                $range_to = explode(' ', $range[1]);
                                $sql2 .= " AND call_date BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
                            } elseif ($key == 'supplier') {
                                $sql2 .= " AND (suppliers.supplier_id_name LIKE '%" . $value . "%' OR suppliers.supplier_name LIKE '%" . $value . "%')";
                            } elseif ($key == 'currency_id') {
                                $sql2 .= " AND suppliers.currency_id ='" . $value . "' ";
                            } elseif ($value != '') {
                                
                            }
                        }
                    }
                }

                $sql2 .= " GROUP BY supplier_reference, suppliers.supplier_name  ";
                //$sql2 .=" ORDER BY supplier_name";
            }


            $is_search = false;
            if ($filter_data['service_type'] == '' || $filter_data['service_type'] == 'DID') {
                $is_search = true;
            }

            if ($is_search) {
                $sql3 = "SELECT 
						'connected_calls_in' as from_query,
						'MINUTES' otherunit, 
						suppliers.supplier_name, 
						suppliers.supplier_id,
						suppliers.currency_id, 
						'DID' as service_type, 
						destination as supplier_reference, 
						destination as system_reference, 
						'' as service_status, 
						min(call_date) as start_date, 
						max(call_date) as end_date, 
						SUM(carrier_duration) as quantity, 
						0 as 'OneOffCharge', 
						0 as 'Monthly Charge', 
						sum(carrier_callcost_total) as 'UsageCharge', 
						sum(carrier_duration) as InMinute, 
						0 as OutMinute 
					FROM " . $this->db->dbprefix('connected_calls') . " connected_calls INNER JOIN " . $this->db->dbprefix('carrier') . " carrier ON connected_calls.carrier_id = carrier.carrier_id_name 
												INNER JOIN " . $this->db->dbprefix('suppliers') . " suppliers  ON suppliers.supplier_id_name = carrier.supplier_id_name 
					WHERE calltype= 'IN' ";

                if (count($filter_data) > 0) {
                    foreach ($filter_data as $key => $value) {
                        if ($value != '') {
                            if ($key == 'record_date') {
                                $range = explode(' - ', $value);
                                $range_from = explode(' ', $range[0]);
                                $range_to = explode(' ', $range[1]);
                                $sql3 .= " AND call_date BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
                            } elseif ($key == 'supplier') {
                                $sql3 .= " AND (suppliers.supplier_id_name LIKE '%" . $value . "%' OR suppliers.supplier_name LIKE '%" . $value . "%')";
                            } elseif ($key == 'currency_id') {
                                $sql3 .= " AND suppliers.currency_id ='" . $value . "' ";
                            } elseif ($value != '') {
                                
                            }
                        }
                    }
                }

                $sql3 .= " GROUP BY supplier_reference, suppliers.supplier_name ";
                //$sql3 .=" ORDER BY supplier_name";
            }




            /*

              UsageCharge sum_min_charge ,
              InMinute sum_in_minute,
              OutMinute sum_out_minute
             */

            $sql = "SELECT SQL_CALC_FOUND_ROWS  
			from_query,
			otherunit,
			supplier_name, 
			supplier_id, 
			currency_id, 
			service_type, 
			service_status,
			supplier_reference, 
			system_reference, 
			
			quantity, 
			start_date,
			end_date,
			 
			OneOffCharge one_Off_charge, 
			MonthlyCharge monthly_charge,
			
			UsageCharge usage_charge
			FROM ( " . $sql1;
            if (isset($sql2) && $sql2 != '')
                $sql .= " UNION 
				" . $sql2;

            if (isset($sql3) && $sql3 != '')
                $sql .= " UNION 
				" . $sql3;

            $sql .= ") a ";


            //$sql .=" GROUP BY  supplier_name, service_type, currency_id ";
            $sql .= " ORDER BY service_type , supplier_name, otherunit DESC";

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            else {
                $sql .= " LIMIT 2000";
                $limit_to = 2000; //used for export
            }

            //echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $fetched_records = $query->num_rows();
            $final_return_array['result'] = array();

            if ($fetched_records > 0) {
                foreach ($query->result_array() as $row) {
                    $final_return_array['result'][] = $row;
                }
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;



            ///////////////////		
            ///////////////////


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Supplier data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function supplier_summary_audit($filter_data, $limit_to = '', $limit_from = '') {
        $final_return_array = array();
        try {


            //////////////////////////

            $connected_calls_where = "";
            if (isset($filter_data['record_date']) && $filter_data['record_date'] != '') {
                $value = $filter_data['record_date'];
                $range = explode(' - ', $value);
                $range_from = explode(' ', $range[0]);
                $range_to = explode(' ', $range[1]);
                $connected_calls_where .= " AND call_date BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
            }



            $sql1 = "SELECT 
				if(rule_type in ('SUPPLIERSETUPDID','SUPPLIERNEWHOSTEDSERVERSETUP'), 'SERVER','DID') otherunit, 
				supplier_usage_outer.supplier_name, 				
				supplier_usage_outer.currency_id, 
				service_type , 
				supplier_reference,
				system_reference, 
				(SELECT supplier_usage_inner.service_status FROM " . $this->db->dbprefix('supplier_usage') . "  supplier_usage_inner WHERE supplier_usage_inner.supplier_reference = supplier_usage_outer.supplier_reference ORDER BY action_dt desc LIMIT 1) service_status, 
				MIN(start_date) start_date, 
				max(end_date) end_date, 
				quantity, 
				sum(if(rule_type in ('SUPPLIERSETUPDID','SUPPLIERNEWHOSTEDSERVERSETUP'), total_cost, 0)) as 'OneOffCharge', 
				sum(if(rule_type in ( 'SUPPLIERRENTALDID','SUPPLIERNEWHOSTEDSERVERRENTAL'), total_cost, 0)) as 'MonthlyCharge', 
				0 as 'UsageCharge', 
				0 as InMinute, 
				0 as OutMinute 
				FROM " . $this->db->dbprefix('supplier_usage') . " supplier_usage_outer 
				WHERE rule_type IN ('SUPPLIERSETUPDID','SUPPLIERRENTALDID','SUPPLIERNEWHOSTEDSERVERRENTAL','SUPPLIERNEWHOSTEDSERVERSETUP') ";


            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'record_date') {
                            $range = explode(' - ', $value);
                            $range_from = explode(' ', $range[0]);
                            $range_to = explode(' ', $range[1]);
                            $sql1 .= " AND action_dt BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
                        } elseif ($key == 'supplier') {
                            $sql1 .= " AND (supplier_id LIKE '%" . $value . "%' OR supplier_name LIKE '%" . $value . "%') ";
                        } elseif ($key == 'service_type') {
                            $sql1 .= " AND $key ='" . $value . "' ";
                        } elseif ($value != '') {
                            $sql1 .= " AND $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
            }
            $sql1 .= " GROUP BY supplier_usage_outer.supplier_name ";
            ///////////////////////////////	

            $is_search = false;
            if ($filter_data['service_type'] == '' || $filter_data['service_type'] == 'VOIP') {
                $is_search = true;
            }

            if ($is_search) {
                $sql2 = " SELECT 
					'MINUTES' otherunit,
					suppliers.supplier_name, 
					suppliers.currency_id, 
					'VoIP' as service_type, 
					destination as supplier_reference, 
					destination as system_reference, 
					'' as service_status, 
					min(call_date) as start_date, 
					max(call_date) as end_date, 
					0 as quantity, 
					0 as 'OneOffCharge', 
					0 as 'Monthly Charge', 
					sum(carrier_callcost_total) as 'UsageCharge', 
					0 as InMinute, 
					sum(carrier_duration) as OutMinute 
					FROM " . $this->db->dbprefix('connected_calls') . " connected_calls INNER JOIN " . $this->db->dbprefix('carrier') . " carrier ON connected_calls.carrier_id = carrier.carrier_id_name 
												INNER JOIN " . $this->db->dbprefix('suppliers') . " suppliers  ON suppliers.supplier_id_name = carrier.supplier_id_name 
					WHERE calltype= 'OUT' ";


                if (count($filter_data) > 0) {
                    foreach ($filter_data as $key => $value) {
                        if ($value != '') {
                            if ($key == 'record_date') {
                                $range = explode(' - ', $value);
                                $range_from = explode(' ', $range[0]);
                                $range_to = explode(' ', $range[1]);
                                $sql2 .= " AND call_date BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
                            } elseif ($key == 'supplier') {
                                $sql2 .= " AND (suppliers.supplier_id_name LIKE '%" . $value . "%' OR suppliers.supplier_name LIKE '%" . $value . "%')";
                            } elseif ($key == 'currency_id') {
                                $sql2 .= " AND suppliers.currency_id ='" . $value . "' ";
                            } elseif ($value != '') {
                                
                            }
                        }
                    }
                }

                $sql2 .= " GROUP BY suppliers.supplier_name ";
                //$sql2 .=" ORDER BY supplier_name";
            }


            $is_search = false;
            if ($filter_data['service_type'] == '' || $filter_data['service_type'] == 'DID') {
                $is_search = true;
            }

            if ($is_search) {
                $sql3 = "SELECT 
						'MINUTES' otherunit, 
						suppliers.supplier_name, 
						suppliers.currency_id, 
						'DID' as service_type, 
						destination as supplier_reference, 
						destination as system_reference, 
						'' as service_status, 
						min(call_date) as start_date, 
						max(call_date) as end_date, 
						0 as quantity, 
						0 as 'OneOffCharge', 
						0 as 'Monthly Charge', 
						sum(carrier_callcost_total) as 'UsageCharge', 
						sum(carrier_duration) as InMinute, 
						0 as OutMinute 
					FROM " . $this->db->dbprefix('connected_calls') . " connected_calls INNER JOIN " . $this->db->dbprefix('carrier') . " carrier ON connected_calls.carrier_id = carrier.carrier_id_name 
												INNER JOIN " . $this->db->dbprefix('suppliers') . " suppliers  ON suppliers.supplier_id_name = carrier.supplier_id_name 
					WHERE calltype= 'IN' ";

                if (count($filter_data) > 0) {
                    foreach ($filter_data as $key => $value) {
                        if ($value != '') {
                            if ($key == 'record_date') {
                                $range = explode(' - ', $value);
                                $range_from = explode(' ', $range[0]);
                                $range_to = explode(' ', $range[1]);
                                $sql3 .= " AND call_date BETWEEN '" . $range_from[0] . "' AND '" . $range_to[0] . "' ";
                            } elseif ($key == 'supplier') {
                                $sql3 .= " AND (suppliers.supplier_id_name LIKE '%" . $value . "%' OR suppliers.supplier_name LIKE '%" . $value . "%')";
                            } elseif ($key == 'currency_id') {
                                $sql3 .= " AND suppliers.currency_id ='" . $value . "' ";
                            } elseif ($value != '') {
                                
                            }
                        }
                    }
                }

                $sql3 .= " GROUP BY suppliers.supplier_name ";
                //$sql3 .=" ORDER BY supplier_name";
            }






            $sql = "SELECT SQL_CALC_FOUND_ROWS  
			supplier_name, 
			currency_id, 
			service_type, 
			otherunit,
			quantity sum_quantity, 
			OneOffCharge sum_one_Off_charge, 
			MonthlyCharge sum_monthly_charge, 
			UsageCharge sum_min_charge ,
			InMinute sum_in_minute, 
			OutMinute sum_out_minute
			FROM ( " . $sql1;
            if (isset($sql2) && $sql2 != '')
                $sql .= " UNION 
				" . $sql2;

            if (isset($sql3) && $sql3 != '')
                $sql .= " UNION 
				" . $sql3;

            $sql .= ") a ";


            //$sql .=" GROUP BY  supplier_name, service_type, currency_id ";
            $sql .= " ORDER BY service_type , supplier_name, otherunit DESC";

            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            else {
                $sql .= " LIMIT 2000";
                $limit_to = 2000; //used for export
            }

            //echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $fetched_records = $query->num_rows();
            $final_return_array['result'] = array();

            if ($fetched_records > 0) {
                foreach ($query->result_array() as $row) {
                    $final_return_array['result'][] = $row;
                }
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;



            ///////////////////		
            ///////////////////


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Supplier data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function customer_call_sipcode_review($account_id, $date_from, $date_to) {
        $final_return_array = array();
        try {
            $DB1 = $this->load->database('cdrdb', true);
            $sql = "SELECT 
				sum(totalcalls) totalcalls, 
				sum(answeredcalls) answeredcalls, 
				SUM(bill_duration) bill_duration,
				sum(totalcalls) - sum(answeredcalls) unansweredcalls, 
				sum(account_cost) account_cost,
				SIPCODE sipcode, 
				prefix_name,
				account_id 
				FROM " . $this->db->dbprefix('calls_statistics') . "  
				WHERE 
				account_id = '" . $account_id . "' 
				AND concat(call_date, ' ',calltime_h,':', calltime_m,':00') BETWEEN '" . $date_from . "' AND '" . $date_to . "'
				GROUP BY SIPCODE, prefix_name
				";

            //echo $sql;
            $query = $DB1->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $row_count = $query->num_rows();

            $final_return_array['result'] = array();

            if ($row_count > 0) {
                foreach ($query->result_array() as $row) {
                    $final_return_array['result'][] = $row;
                }
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Call SIPCODE data fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

}
