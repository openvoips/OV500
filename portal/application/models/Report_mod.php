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

class Report_mod extends CI_Model {

    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function CarrQOSR($search_data) {
        $DB1 = $this->load->database('cdrdb', true);
        $range = explode(' - ', $search_data['timerange']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);
        $start_dt = $range_from[0];
        $date = $start_dt . " 00:00:00";
        $table = date('Ym', strtotime($date)) . "_carrierstate";
        $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
        $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
        $end_dt = $range_to[0];
        $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
        $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
        $str = "select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls , round((sum(answeredcalls)/sum(totalcalls))*100,2) as asr, ifnull(round((sum(carrier_duration)/sum(answeredcalls))/60,2),0.00) as acd, ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd, ";
        $str .= " round(sum(carrier_duration)/60,2) as total_duration ";
        if ($search_data['group_by_ip'] == 'Y')
            $str .= " ,carrier_ipaddress  as ip_address ";
        if ($search_data['group_by_carrier'] == 'Y')
            $str .= " ,concat(carrier_name,'(',carrier_id,')') carrier_id";
        if ($search_data['group_by_date'] == 'Y')
            $str .= " ,call_date ";
        if ($search_data['group_by_hour'] == 'Y')
            $str .= " ,calltime_h ";
        if ($search_data['group_by_minute'] == 'Y')
            $str .= " ,calltime_m ";
        if ($search_data['group_by_prefix'] == 'Y')
            $str .= " ,carrier_prefix as prefix";
        if ($search_data['group_by_destination'] == 'Y')
            $str .= " ,carrier_prefix_name as prefix_name";
        if ($search_data['group_by_sip'] == 'Y')
            $str .= " ,SIPCODE ";
        if ($search_data['group_by_q850'] == 'Y')
            $str .= " ,Q850CODE ";

        //////////////////////////////
        /// showing account's cost///	
        if ($search_data['group_by_carrier'] == 'Y' || $search_data['carrier_id'] != '') {
            $str .= " ,round(sum(carrier_cost)*1.0000000000000000,2)  as cost ,carrier_currency_id as currency_id";
        }
        /// showing account's cost///	
        //////////////////////////////
        $str .= " from $table where date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "'
and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";
        if ($search_data['ip'] != '')
            $str .= " and carrier_ipaddress = '" . $search_data['ip'] . "'";
        if ($search_data['carrier_id'] != '')
            $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
        if ($search_data['prefix'] != '')
            $str .= " and carrier_prefix like '" . $search_data['prefix'] . "'";
        if ($search_data['destination'] != '')
            $str .= " and carrier_prefix_name like '" . $search_data['destination'] . "'";
        if ($search_data['sip'] != '')
            $str .= " and SIPCODE = '" . $search_data['sip'] . "'";
        if ($search_data['q850'] != '')
            $str .= " and Q850CODE = '" . $search_data['q850'] . "'";

        $group_by = "";

        if ($search_data['group_by_ip'] == 'Y')
            $group_by .= " carrier_ipaddress ,";
        if ($search_data['group_by_carrier'] == 'Y')
            $group_by .= " carrier_id ,";
        if ($search_data['group_by_date'] == 'Y')
            $group_by .= " call_date ,";
        if ($search_data['group_by_hour'] == 'Y')
            $group_by .= " calltime_h ,";
        if ($search_data['group_by_minute'] == 'Y')
            $group_by .= " calltime_m ,";
        if ($search_data['group_by_prefix'] == 'Y')
            $group_by .= " carrier_prefix ,";
        if ($search_data['group_by_destination'] == 'Y')
            $group_by .= " carrier_prefix_name ,";
        if ($search_data['group_by_sip'] == 'Y')
            $group_by .= " SIPCODE ,";
        if ($search_data['group_by_q850'] == 'Y')
            $group_by .= " Q850CODE ,";


        if ($group_by != '')
            $group_by = " group by " . rtrim($group_by, ',');

        $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
        $query = $str . $group_by . $orderby;
//        die($query);
        $result = $DB1->query($query);

        $return['total'] = $result->num_rows();
        $return['result'] = $result->result_array();

        //var_dump($return);
        return $return;
    }

    public function monin_data($incoming_calls = 'Y', $outgoing_calls = 'Y', $incoming_duration = 'Y', $outgoing_duration = 'Y', $gateway_calls = 'Y', $customer_calls = 'Y', $show_usage = 'Y', $customer_call_stat = 'N', $carrier_call_stat = 'N', $livecalls_destination = 'N') {
        $logged_customer_type = get_logged_account_type();
        $logged_customer_account_id = get_logged_account_id();
        $logged_customer_level = get_logged_account_level();
        $DB1 = $this->load->database('cdrdb', true);
        if ($livecalls_destination == 'Y') {
            $str = "SELECT customer_destination, count(id) total_calls, sum( if(callstatus = 'answer',1,0)) answering, sum( if(callstatus <> 'answer',1,0)) ringing FROM livecalls GROUP BY customer_destination ORDER BY total_calls DESC";
            $result = $this->db->query($str);
            $return['livecalls_destination'] = $result->result_array();
        }

        if ($customer_call_stat == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $str = "SELECT concat(customer_company_name,' (',account_id,')') account_id, IFNULL(SUM(totalcalls),0) tot_calls, IFNULL(SUM(answeredcalls),0) tot_answered, IFNULL(round((SUM(answeredcalls)/SUM(totalcalls))*100),0) asr,  IFNULL(round((SUM(customer_duration)/SUM(answeredcalls))),0) acd, round(SUM(customer_cost),2) tot_cost FROM $tablecustomerstate  WHERE call_date =  '" . date('Y-m-d') . "'  AND calltime_h >= HOUR(NOW())-1 AND calltime_m >= MINUTE(NOW())-1 GROUP BY account_id ORDER BY asr, tot_calls DESC;";
            $result = $DB1->query($str);
            $return['customer_call_stat'] = $result->result_array();
        }

        if ($carrier_call_stat == 'Y') {
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "SELECT  concat(carrier_name ,' (',carrier_id,')') carrier_id,
                        IFNULL(SUM(totalcalls),0) tot_calls,
						IFNULL(SUM(answeredcalls),0) tot_answered,
						IFNULL(round((SUM(answeredcalls)/SUM(totalcalls))*100),0) asr, 
						IFNULL(round((SUM(carrier_duration)/SUM(answeredcalls))),0) acd,
						round(SUM(carrier_cost),2) tot_cost	
					FROM $tablecarrierstate 
					WHERE call_date =  '" . date('Y-m-d') . "'
					  AND calltime_h >= HOUR(NOW())-1
					  AND calltime_m >= MINUTE(NOW())-1
					GROUP BY carrier_id
                    ORDER BY asr, tot_calls DESC
				";
            $result = $DB1->query($str);
            $return['carrier_call_stat'] = $result->result_array();
        }
        /////////////////////////

        if ($show_usage == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $currency_data = $this->utils_model->get_currencies();
            $currency_array = array();
            for ($i = 0; $i < count($currency_data); $i++) {
                $currency_id = $currency_data[$i]['currency_id'];
                $currency_array[$currency_id] = $currency_data[$i]['symbol'];
            }

            $str = "SELECT round(sum(customer_cost),2) customer_cost_total,
							customer_currency_id currency_id, 
							'customer' customer_type 
					FROM $tablecustomerstate 
					WHERE call_date =  '" . date('Y-m-d') . "'
					GROUP BY customer_currency_id
				UNION
				SELECT round(sum(carrier_cost),2) customer_cost_total,
						carrier_currency_id currency_id ,
						'carrier' customer_type 
				FROM $tablecarrierstate 
				where call_date =  '" . date('Y-m-d') . "'
				GROUP BY carrier_currency_id";
            $result = $DB1->query($str);
            //$current_data = $result->result_array();
            //$return['usage_data'] = $result->result_array();
            $return['usage_data'] = array();
            foreach ($result->result_array() as $row) {
                $currency_id = $row['currency_id'];
                if (isset($currency_array[$currency_id]))
                    $currency_name = $currency_array[$currency_id];
                else
                    $currency_name = '--';

                $row['currency_name'] = $currency_name;
                $return['usage_data'][] = $row;
            }
        }

        if ($incoming_calls == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY 
						ifnull(sum(totalcalls),0) as tot_calls,
						ifnull(sum(answeredcalls),0)  as tot_answered,
						ifnull(round(sum(customer_duration)/60,2),0) as tot_duration,
						ifnull(round((sum(answeredcalls)/sum(totalcalls))*100,2),0) as asr, 
						ifnull(round((sum(customer_duration)/sum(answeredcalls)),2),0.00) as acd, 
						ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd
					from $tablecustomerstate where call_date = '" . date('Y-m-d') . "'";
            $result = $DB1->query($str);
            $current_data = $result->result_array();
            $return['incoming_calls'] = $current_data[0];
        }

        if ($incoming_duration == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY calltime_h, round(sum(customer_duration)/60,2) as hour_duration from $tablecustomerstate where call_date = '" . date('Y-m-d') . "' group by calltime_h";
            $result = $DB1->query($str);
            $return['incoming_duration'] = $result->result_array();
        }

        if ($outgoing_calls == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY 
	ifnull(sum(totalcalls),0) as tot_calls,
	ifnull(sum(answeredcalls),0)  as tot_answered,
	ifnull(round(sum(carrier_duration)/60,2),0) as tot_duration,
	ifnull(round((sum(answeredcalls)/sum(totalcalls))*100,2),0) as asr, 
	ifnull(round((sum(carrier_duration)/sum(answeredcalls)),2),0.00) as acd, 
	ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd
	from $tablecarrierstate where call_date = '" . date('Y-m-d') . "'";
            $result = $DB1->query($str);
            $current_data = $result->result_array();
            $return['outgoing_calls'] = $current_data[0];
        }

        if ($outgoing_duration == 'Y') {
            $tablecustomerstate = date('Ym') . '_customerstate';
            $tablecarrierstate = date('Ym') . '_carrierstate';
            $str = "select HIGH_PRIORITY calltime_h, round(sum(carrier_duration)/60,2) as hour_duration from $tablecarrierstate where call_date = '" . date('Y-m-d') . "' group by calltime_h";
            $result = $DB1->query($str);
            $return['outgoing_duration'] = $result->result_array();
        }

        if ($gateway_calls == 'Y') {
            $str = "select HIGH_PRIORITY carrier_id  as name, carrier_ipaddress as ip,count(*) as total_calls ,
	sum(if(callstatus = 'answer',1,0)) as 'answer',
	sum(if(callstatus = 'ring',1,0)) as 'ringing',
	sum(if(callstatus = 'progress',1,0)) as 'progress'
	from livecalls where 1 
	group by carrier_name, carrier_ipaddress order by carrier_name asc";

            //$result = $DB1->query($str);
            $result = $this->db->query($str);
            $return['gateway_calls'] = $result->result_array();
        }

        if ($customer_calls == 'Y') {
            $str = "select HIGH_PRIORITY customer_company as name, 'enduser' as type ,customer_ipaddress as ip,count(*) as total_calls ,
sum(if(callstatus = 'answer',1,0)) as 'answer',
sum(if(callstatus = 'ring',1,0)) as 'ringing',
sum(if(callstatus = 'progress',1,0)) as 'progress'
from livecalls where reseller1_account_id is NULL 
group by customer_account_id, customer_ipaddress order by customer_company ,total_calls desc ";

            // $result = $DB1->query($str);
            $result = $this->db->query($str);
            $customer_calls = $result->result_array();

            $str = "select HIGH_PRIORITY reseller1_account_id as name, 'reseller' as type ,customer_ipaddress as ip,count(*) as total_calls ,
sum(if(callstatus = 'answer',1,0)) as 'answer',
sum(if(callstatus = 'ring',1,0)) as 'ringing',
sum(if(callstatus = 'progress',1,0)) as 'progress'
from livecalls where reseller1_account_id is not NULL 
group by reseller1_account_id order by total_calls desc ";

            //  $result = $DB1->query($str);
            $result = $this->db->query($str);
            $reseller_calls = $result->result_array();


            $return['customer_calls'] = array_merge($customer_calls, $reseller_calls);
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

    function ProfitLoss($search_data) {

        if ($search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {

            if ($search_data['logged_customer_level'] == '1')
                return $this->ProfitLossR1($search_data);
            if ($search_data['logged_customer_level'] == '2')
                return $this->ProfitLossR2($search_data);
            if ($search_data['logged_customer_level'] == '3')
                return $this->ProfitLossR3($search_data);
        }else {
            return $this->ProfitLossAdmin($search_data);
        }
    }

    function ProfitLossAdmin($search_data) {

        $range = explode(' - ', $search_data['call_date']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);
        $start_dt = $range_from[0];
        $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
        $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
        $end_dt = $range_to[0];
        $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
        $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
        $table = date('Ym', strtotime($start_dt)) . "_customerstate";

        $str = "   select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls ,if(r1_account_id is null or r1_account_id = '' , round(sum(customer_duration)/60,2),  round(sum(r1_duration)/60,2)) as total_duration,  if(r1_account_id is null or r1_account_id = '' , concat(if(customer_company_name = '',account_id, customer_company_name ),' (',account_id,')'), r1_account_id)  as account_code , sum(carrier_callcost_total_usercurrency) carrier_cost, if(r1_account_id is null or r1_account_id = '', round(sum(customer_cost)*1.0000000000000000,4),round(sum(r1_cost)*1.0000000000000000,4)) as cost, customer_currency_id as currency_id ";

        if ($search_data['group_by_carrier'] == 'Y')
            $str .= " ,carrier_id ";
        if ($search_data['group_by_date'] == 'Y')
            $str .= " ,call_date ";
        if ($search_data['group_by_hour'] == 'Y')
            $str .= " ,calltime_h ";
        if ($search_data['group_by_minute'] == 'Y')
            $str .= " ,calltime_m ";
        if ($search_data['group_by_prefix'] == 'Y')
            $str .= " ,prefix ";
        if ($search_data['group_by_destination'] == 'Y')
            $str .= " ,prefix_name ";

        //  $str .= " from $table WHERE 1 and ((r2_account_id = '' or r2_account_id is null ) and (r3_account_id = '' or r3_account_id is null)) and answeredcalls > 0 ";

        $str .= " from $table WHERE 1  and answeredcalls > 0 ";

        if ($search_data['account_type'] == 'U') {
            if ($search_data['account_id'] != '') {
                $str .= " and account_id = '" . $search_data['account_id'] . "'";
            }
        } else if ($search_data['account_type'] == 'R') {
            if ($search_data['account_id'] != '') {
                $str .= " and r1_account_id = '" . $search_data['account_id'] . "'";
            }
        }

        $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "' and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";

        if (trim($search_data['company_name']) != '') {
            $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";
        }

        if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
            $level = $search_data['logged_customer_level'];
            $field_name = 'r' . $level . '_account_id';
            $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
        }
        if ($search_data['carrier_id'] != '')
            $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
        if ($search_data['prefix'] != '')
            $str .= " and prefix like '" . $search_data['prefix'] . "'";
        if ($search_data['destination'] != '')
            $str .= " and prefix_name like '" . $search_data['destination'] . "'";

        $group_by = "";

        if ($search_data['group_by_user'] == 'Y') {
            if ($search_data['account_type'] == 'U')
                $group_by .= " account_code ,";
            else {
                $group_by .= " account_code ,";
            }
        }
        if ($search_data['group_by_carrier'] == 'Y')
            $group_by .= " carrier_id ,";
        if ($search_data['group_by_date'] == 'Y')
            $group_by .= " call_date ,";
        if ($search_data['group_by_hour'] == 'Y')
            $group_by .= " calltime_h ,";
        if ($search_data['group_by_minute'] == 'Y')
            $group_by .= " calltime_m ,";
        if ($search_data['group_by_prefix'] == 'Y')
            $group_by .= " prefix ,";
        if ($search_data['group_by_destination'] == 'Y')
            $group_by .= " prefix_name ,";
        if ($group_by != '')
            $group_by = " group by customer_currency_id," . rtrim($group_by, ',');

        $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
        $query = $str . $group_by . $orderby;
        $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($query);
        // echo $DB1->last_query();
        $return['total'] = $result->num_rows();
        if ($return['total'] == '')
            $return['total'] = 0;
        $return['result'] = $result->result_array();
        return $return;
    }

    function ProfitLossR1($search_data) {
        $range = explode(' - ', $search_data['call_date']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);
        $start_dt = $range_from[0];
        $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
        $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
        $end_dt = $range_to[0];
        $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
        $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
        $table = date('Ym', strtotime($start_dt)) . "_customerstate";
        $str = "   select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls ,if(r2_account_id is null or r2_account_id = '' , round(sum(customer_duration)/60,2),  round(sum(r2_duration)/60,2)) as total_duration,  if(r2_account_id is null or r2_account_id = '' , concat(if(customer_company_name = '',account_id, customer_company_name ),' (',account_id,')'), r2_account_id)  as account_code , sum(r1_cost) carrier_cost, if(r2_account_id is null or r2_account_id = '', round(sum(customer_cost)*1.0000000000000000,4),round(sum(r2_cost)*1.0000000000000000,4)) as cost, customer_currency_id as currency_id ";

        if ($search_data['group_by_carrier'] == 'Y')
            $str .= " ,carrier_id ";
        if ($search_data['group_by_date'] == 'Y')
            $str .= " ,call_date ";
        if ($search_data['group_by_hour'] == 'Y')
            $str .= " ,calltime_h ";
        if ($search_data['group_by_minute'] == 'Y')
            $str .= " ,calltime_m ";
        if ($search_data['group_by_prefix'] == 'Y')
            $str .= " ,prefix ";
        if ($search_data['group_by_destination'] == 'Y')
            $str .= " ,prefix_name ";

        $str .= " from $table WHERE 1 and answeredcalls > 0 ";
        if ($search_data['account_type'] == 'U') {
            if ($search_data['account_id'] != '') {
                $str .= " and account_id = '" . $search_data['account_id'] . "'";
            }
        } else {
            if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                $level = $search_data['logged_customer_level'] + 1;
                $field_name = 'r' . $level . '_account_id';
                $str .= " AND `" . $field_name . "` = '" . $search_data['account_id'] . "'";
            } else {
                $str .= " and r2_account_id = '" . $search_data['account_id'] . "'";
            }
        }
        $str .= " and r1_account_id = '" . $search_data['logged_customer_account_id'] . "'";


        $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "' and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";

        if (trim($search_data['company_name']) != '') {
            $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";
        }

        if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
            $level = $search_data['logged_customer_level'];
            $field_name = 'r' . $level . '_account_id';
            $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
        }
        if ($search_data['carrier_id'] != '')
            $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
        if ($search_data['prefix'] != '')
            $str .= " and prefix like '" . $search_data['prefix'] . "'";
        if ($search_data['destination'] != '')
            $str .= " and prefix_name like '" . $search_data['destination'] . "'";

        $group_by = "";

        if ($search_data['group_by_user'] == 'Y') {
            if ($search_data['account_type'] == 'U')
                $group_by .= " account_code ,";
            else {
                $group_by .= " account_code ,";
            }
        }
        if ($search_data['group_by_carrier'] == 'Y')
            $group_by .= " carrier_id ,";
        if ($search_data['group_by_date'] == 'Y')
            $group_by .= " call_date ,";
        if ($search_data['group_by_hour'] == 'Y')
            $group_by .= " calltime_h ,";
        if ($search_data['group_by_minute'] == 'Y')
            $group_by .= " calltime_m ,";
        if ($search_data['group_by_prefix'] == 'Y')
            $group_by .= " prefix ,";
        if ($search_data['group_by_destination'] == 'Y')
            $group_by .= " prefix_name ,";
        if ($group_by != '')
            $group_by = " group by customer_currency_id," . rtrim($group_by, ',');

        $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
        $query = $str . $group_by . $orderby;
        $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($query);
        //   echo $DB1->last_query();
        $return['total'] = $result->num_rows();
        if ($return['total'] == '')
            $return['total'] = 0;
        $return['result'] = $result->result_array();
        return $return;
    }

    function ProfitLossR2($search_data) {
        $range = explode(' - ', $search_data['call_date']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);
        $start_dt = $range_from[0];
        $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
        $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
        $end_dt = $range_to[0];
        $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
        $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
        $table = date('Ym', strtotime($start_dt)) . "_customerstate";
        $str = "   select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls ,if(r3_account_id is null or r3_account_id = '' , round(sum(customer_duration)/60,2),  round(sum(r3_duration)/60,2)) as total_duration,  if(r3_account_id is null or r3_account_id = '' , concat(if(customer_company_name = '',account_id, customer_company_name ),' (',account_id,')'), r3_account_id)  as account_code , sum(r2_cost) carrier_cost, if(r3_account_id is null or r3_account_id = '', round(sum(customer_cost)*1.0000000000000000,4),round(sum(r3_cost)*1.0000000000000000,4)) as cost, customer_currency_id as currency_id ";

        if ($search_data['group_by_carrier'] == 'Y')
            $str .= " ,carrier_id ";
        if ($search_data['group_by_date'] == 'Y')
            $str .= " ,call_date ";
        if ($search_data['group_by_hour'] == 'Y')
            $str .= " ,calltime_h ";
        if ($search_data['group_by_minute'] == 'Y')
            $str .= " ,calltime_m ";
        if ($search_data['group_by_prefix'] == 'Y')
            $str .= " ,prefix ";
        if ($search_data['group_by_destination'] == 'Y')
            $str .= " ,prefix_name ";

        $str .= " from $table WHERE 1 and answeredcalls > 0 ";
        if ($search_data['account_type'] == 'U') {
            if ($search_data['account_id'] != '') {
                $str .= " and account_id = '" . $search_data['account_id'] . "'";
            }
        } else {
            if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                $level = $search_data['logged_customer_level'] + 1;
                $field_name = 'r' . $level . '_account_id';
                $str .= " AND `" . $field_name . "` = '" . $search_data['account_id'] . "'";
            } else {
                $str .= " and r3_account_id = '" . $search_data['account_id'] . "'";
            }
        }
        $str .= " and r2_account_id = '" . $search_data['logged_customer_account_id'] . "'";
        $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "' and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";
        if (trim($search_data['company_name']) != '') {
            $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";
        }

        if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
            $level = $search_data['logged_customer_level'];
            $field_name = 'r' . $level . '_account_id';
            $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
        }
        if ($search_data['carrier_id'] != '')
            $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
        if ($search_data['prefix'] != '')
            $str .= " and prefix like '" . $search_data['prefix'] . "'";
        if ($search_data['destination'] != '')
            $str .= " and prefix_name like '" . $search_data['destination'] . "'";

        $group_by = "";

        if ($search_data['group_by_user'] == 'Y') {
            if ($search_data['account_type'] == 'U')
                $group_by .= " account_code ,";
            else {
                $group_by .= " account_code ,";
            }
        }
        if ($search_data['group_by_carrier'] == 'Y')
            $group_by .= " carrier_id ,";
        if ($search_data['group_by_date'] == 'Y')
            $group_by .= " call_date ,";
        if ($search_data['group_by_hour'] == 'Y')
            $group_by .= " calltime_h ,";
        if ($search_data['group_by_minute'] == 'Y')
            $group_by .= " calltime_m ,";
        if ($search_data['group_by_prefix'] == 'Y')
            $group_by .= " prefix ,";
        if ($search_data['group_by_destination'] == 'Y')
            $group_by .= " prefix_name ,";
        if ($group_by != '')
            $group_by = " group by customer_currency_id," . rtrim($group_by, ',');

        $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
        $query = $str . $group_by . $orderby;
        $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($query);
        //echo $DB1->last_query();
        $return['total'] = $result->num_rows();
        if ($return['total'] == '')
            $return['total'] = 0;
        $return['result'] = $result->result_array();
        return $return;
    }

    function ProfitLossR3($search_data) {
        $range = explode(' - ', $search_data['call_date']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);
        $start_dt = $range_from[0];
        $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
        $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
        $end_dt = $range_to[0];
        $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
        $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);
        $table = date('Ym', strtotime($start_dt)) . "_customerstate";
        $str = "   select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls ,round(sum(customer_duration)/60,2) as total_duration,  concat(if(customer_company_name = '',account_id, customer_company_name ),' (',account_id,')')  as account_code , sum(r3_cost) carrier_cost, round(sum(customer_cost)*1.0000000000000000,4) as cost, customer_currency_id as currency_id ";

        if ($search_data['group_by_carrier'] == 'Y')
            $str .= " ,carrier_id ";
        if ($search_data['group_by_date'] == 'Y')
            $str .= " ,call_date ";
        if ($search_data['group_by_hour'] == 'Y')
            $str .= " ,calltime_h ";
        if ($search_data['group_by_minute'] == 'Y')
            $str .= " ,calltime_m ";
        if ($search_data['group_by_prefix'] == 'Y')
            $str .= " ,prefix ";
        if ($search_data['group_by_destination'] == 'Y')
            $str .= " ,prefix_name ";

        $str .= " from $table WHERE 1 and answeredcalls > 0 ";
        if ($search_data['account_type'] == 'U') {
            if ($search_data['account_id'] != '') {
                $str .= " and account_id = '" . $search_data['account_id'] . "'";
            }
        } else {
            if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                $level = $search_data['logged_customer_level'] + 1;
                $field_name = 'r' . $level . '_account_id';
                $str .= " AND " . $field_name . " = '" . $search_data['account_id'] . "'";
            }
        }

        $str .= " and r3_account_id = '" . $search_data['logged_customer_account_id'] . "'";

        $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "' and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";
        if (trim($search_data['company_name']) != '') {
            $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";
        }

        if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
            $level = $search_data['logged_customer_level'];
            $field_name = 'r' . $level . '_account_id';
            $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
        }

        if ($search_data['carrier_id'] != '')
            $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
        if ($search_data['prefix'] != '')
            $str .= " and prefix like '" . $search_data['prefix'] . "'";
        if ($search_data['destination'] != '')
            $str .= " and prefix_name like '" . $search_data['destination'] . "'";

        $group_by = "";

        if ($search_data['group_by_user'] == 'Y') {
            if ($search_data['account_type'] == 'U')
                $group_by .= " account_code ,";
            else {
                $group_by .= " account_code ,";
            }
        }
        if ($search_data['group_by_carrier'] == 'Y')
            $group_by .= " carrier_id ,";
        if ($search_data['group_by_date'] == 'Y')
            $group_by .= " call_date ,";
        if ($search_data['group_by_hour'] == 'Y')
            $group_by .= " calltime_h ,";
        if ($search_data['group_by_minute'] == 'Y')
            $group_by .= " calltime_m ,";
        if ($search_data['group_by_prefix'] == 'Y')
            $group_by .= " prefix ,";
        if ($search_data['group_by_destination'] == 'Y')
            $group_by .= " prefix_name ,";
        if ($group_by != '')
            $group_by = " group by customer_currency_id," . rtrim($group_by, ',');

        $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
        $query = $str . $group_by . $orderby;
        $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($query);
        //  echo $DB1->last_query();
        $return['total'] = $result->num_rows();
        if ($return['total'] == '')
            $return['total'] = 0;
        $return['result'] = $result->result_array();
        return $return;
    }

    function CustQOSR($search_data) {

        $range = explode(' - ', $search_data['call_date']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);
        $start_dt = $range_from[0];
        $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
        $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);
        $end_dt = $range_to[0];
        $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
        $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);


        // $date = $search_data['call_date'] . " 00:00:00";
        $table = date('Ym', strtotime($start_dt)) . "_customerstate";
        $str = "select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls , round((sum(answeredcalls)/sum(totalcalls))*100,2) as asr, ifnull(round((sum(customer_duration)/sum(answeredcalls))/60,2),0.00) as acd, ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd, ";

        if ($search_data['account_type'] == 'U')
            $str .= " round(sum(customer_duration)/60,2) as total_duration ";
        else {

            $str .= " round(sum(r1_duration)/60,2) as total_duration ";
        }

        if ($search_data['group_by_user'] == 'Y') {
            if ($search_data['account_type'] == 'U')
                $str .= " ,concat(if(customer_company_name = '',account_id,customer_company_name  ),' (',account_id,')')  as account_code ";
            else {
                $str .= " ,r1_account_id  as account_code ";
            }
        }

        if ($search_data['group_by_carrier'] == 'Y')
            $str .= " ,carrier_id ";
        if ($search_data['group_by_date'] == 'Y')
            $str .= " ,call_date ";
        if ($search_data['group_by_hour'] == 'Y')
            $str .= " ,calltime_h ";
        if ($search_data['group_by_minute'] == 'Y')
            $str .= " ,calltime_m ";
        if ($search_data['group_by_prefix'] == 'Y')
            $str .= " ,prefix ";
        if ($search_data['group_by_destination'] == 'Y')
            $str .= " ,prefix_name ";
        if ($search_data['group_by_sip'] == 'Y')
            $str .= " ,SIPCODE ";
        if ($search_data['group_by_q850'] == 'Y')
            $str .= " ,Q850CODE ";

        //////////////////////////////
        /// showing account's cost///	
        if ($search_data['group_by_user'] == 'Y' || $search_data['account_id'] != '') {
            if ($search_data['account_type'] == 'U')
                $str .= " ,round(sum(customer_cost)*1.0000000000000000,2)  as cost ";
            else
                $str .= " ,round(sum(r1_cost)*1.0000000000000000,2)  as cost ";

            $str .= " ,customer_currency_id as currency_id";
        }
        /// showing account's cost///	
        //////////////////////////////

        $str .= " from $table WHERE 1 ";


        if ($search_data['account_id'] != '') {
            if ($search_data['account_type'] == 'U')
                $str .= " and account_id = '" . $search_data['account_id'] . "'";
            else {

                if (isset($search_data['logged_account_type']) && isset($search_data['logged_account_level']) && $search_data['logged_account_type'] == 'RESELLER' && in_array($search_data['logged_account_level'], array(1, 2, 3))) {
                    $level = $search_data['logged_account_level'] + 1;
                    $field_name = 'r' . $level . '_account_id';

                    $str .= " AND `" . $field_name . "` = '" . $search_data['account_id'] . "'";
                } else {
                    $str .= " and r1_account_id = '" . $search_data['account_id'] . "'";
                }
            }
        }


        ////////datetime/////////////
//        if (trim($search_data['call_date']) != '') {
//            $str .= " AND call_date ='" . trim($search_data['call_date']) . "' ";
//        }
//        if (trim($search_data['calltime_from']) != '' && trim($search_data['calltime_to']) != '') {
//            $str .= " AND concat(calltime_h,':',calltime_m,':00') BETWEEN '" . trim($search_data['calltime_from']) . "' AND '" . trim($search_data['calltime_to']) . "'";
//        }
        //////////////

        $str .= " and  date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "'
and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";



        /* ------------------------------ */
        if (trim($search_data['company_name']) != '')
            $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";

        /* ------------------------------ */


        ////
        if (isset($search_data['logged_account_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_account_level']) && $search_data['logged_account_type'] == 'RESELLER' && in_array($search_data['logged_account_level'], array(1, 2, 3))) {
            $level = $search_data['logged_account_level'];
            $field_name = 'r' . $level . '_account_id';

            $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
        }




        if ($search_data['carrier_id'] != '')
            $str .= " and carrier_id like '%" . $search_data['carrier_id'] . "%'";
        if ($search_data['prefix'] != '')
            $str .= " and prefix like '" . $search_data['prefix'] . "'";
        if ($search_data['destination'] != '')
            $str .= " and prefix_name like '" . $search_data['destination'] . "'";
        if ($search_data['sip'] != '')
            $str .= " and SIPCODE = '" . $search_data['sip'] . "'";
        if ($search_data['q850'] != '')
            $str .= " and Q850CODE = '" . $search_data['q850'] . "'";

        $group_by = "";

        if ($search_data['group_by_user'] == 'Y') {
            if ($search_data['account_type'] == 'U')
                $group_by .= " account_id ,";
            else {
                $group_by .= " r1_account_id ,";
            }
        }
        if ($search_data['group_by_carrier'] == 'Y')
            $group_by .= " carrier_id ,";
        if ($search_data['group_by_date'] == 'Y')
            $group_by .= " call_date ,";
        if ($search_data['group_by_hour'] == 'Y')
            $group_by .= " calltime_h ,";
        if ($search_data['group_by_minute'] == 'Y')
            $group_by .= " calltime_m ,";
        if ($search_data['group_by_prefix'] == 'Y')
            $group_by .= " prefix ,";
        if ($search_data['group_by_destination'] == 'Y')
            $group_by .= " prefix_name ,";
        if ($search_data['group_by_sip'] == 'Y')
            $group_by .= " SIPCODE ,";
        if ($search_data['group_by_q850'] == 'Y')
            $group_by .= " Q850CODE ,";


        if ($group_by != '')
            $group_by = " group by " . rtrim($group_by, ',');

        $orderby = " order by date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) desc ";
        $query = $str . $group_by . $orderby;
        $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($query);
//echo $DB1->last_query();
        $return['total'] = $result->num_rows();
        if ($return['total'] == '')
            $return['total'] = 0;
        $return['result'] = $result->result_array();


        return $return;
    }

    function ConnectedCalls($search_data, $limit_to = '', $limit_from = '') {
        try {
            $table = date('Ym') . "_ratedcdr";
            $DB1 = $this->load->database('cdrdb', true);
            $range = explode(' - ', $search_data['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];

            //start_time 'Start Time', answer_time 'Answer Time',	end_time 'End time',
            $sql = "SELECT SQL_CALC_FOUND_ROWS
						id, customer_account_id Account,customer_company_name, customer_src_caller 'SRC-CLI', customer_src_callee 'SRC-DST', customer_src_ip 'SRC-IP',customer_incodecs 'Incoming-Codecs',carrier_outcodecs 'Outgoing-Codecs',call_codecs 'Call\'s-Codec',
						customer_tariff_id 'User-Tariff',	customer_prefix 'Prefix', customer_destination 'Destination', customer_duration 'Duration',
						customer_callcost_total 'Cost',	reseller1_account_id 'R1-Account',	reseller1_tariff_id 'R1-Tariff',reseller1_duration 'R1-Duration',
						reseller1_callcost_total 'R1-Cost',	reseller2_account_id 'R2-Account',	reseller2_tariff_id 'R2-Tariff', reseller2_duration 'R2-Duration',
						reseller2_callcost_total 'R2-Cost',	reseller3_account_id 'R3-Account',	reseller3_tariff_id 'R3-Tariff', reseller3_duration 'R3-Duration',
						reseller3_callcost_total 'R3-Cost', carrier_dialplan_id 'Routing',	carrier_id 'Carrier', carrier_ipaddress 'C-IP',
						carrier_src_caller 'USER-CLI',	carrier_src_callee 'User-DST', carrier_dst_caller 'C-CLI', carrier_dst_callee 'C-DST', carrier_tariff_id 'C-Tariff',
						carrier_prefix 'C-Prefix',	carrier_destination 'C-Destination', carrier_duration 'C-Duration',	carrier_callcost_total 'C-Cost',
						billsec 'Org-Duration',	 Q850CODE, SIPCODE , hangupby,						
						IF(customer_duration > 0, answer_time, start_time ) AS 'Start Time',
						IF(customer_duration > 0, end_time, start_time ) AS 'End Time' , cdr_type
						FROM  $table WHERE 1 ";


            if (trim($search_data['s_cdr_customer_account']) != '') {
                if ($search_data['s_cdr_customer_type'] == 'U')
                    $sql .= " AND customer_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R1')
                    $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R2')
                    $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R3')
                    $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
            }

            if (trim($search_data['s_cdr_dialed_no']) != '')
                $sql .= " AND customer_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
            if (trim($search_data['s_cdr_carrier_dst_no']) != '')
                $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
            if (trim($search_data['s_cdr_customer_cli']) != '')
                $sql .= " AND customer_src_caller like '" . trim($search_data['s_cdr_customer_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier_cli']) != '')
                $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier']) != '')
                $sql .= " AND carrier_carrier_id like '" . trim($search_data['s_cdr_carrier']) . "%' ";
            if (trim($search_data['s_cdr_carrier_ip']) != '')
                $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
            if (trim($search_data['s_cdr_customer_ip']) != '')
                $sql .= " AND customer_src_ip = '" . trim($search_data['s_cdr_customer_ip']) . "' ";
            if (trim($search_data['s_cdr_cdr_type']) != '')
                $sql .= " AND cdr_type = '" . trim($search_data['s_cdr_cdr_type']) . "' ";

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

            if (trim($search_data['s_cdr_customer_company_name']) != '')
                $sql .= " AND customer_company_name LIKE '%" . trim($search_data['s_cdr_customer_company_name']) . "%' ";

            if (isset($search_data['s_cdr_customer_type_login']) and $search_data['s_cdr_customer_type_login'] == 'CUSTOMER') {

                $account_id_str = $search_data['s_cdr_customer_account'];
                $sql .= " AND customer_account_id IN('" . $account_id_str . "')";
            } elseif (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM account WHERE parent_account_id='" . $search_data['s_parent_account_id'] . "'";
                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql .= " AND customer_account_id IN(" . $account_id_str . ")";
            }
            ////////////////////

            $group_by = '';
            $orderby = ' order by id desc ';
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

    function FaildCalls($search_data, $limit_to = '', $limit_from = '') {  //print_r($search_data);
        try {
            $DB1 = $this->load->database('cdrdb', true);

            $range = explode(' - ', $search_data['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];
            $table = date('Ym') . '_cdr';

            $sql = "SELECT SQL_CALC_FOUND_ROWS
					id , 
					customer_account_id 'Account',customer_company_name,customer_src_ip 'SRC-IP',customer_src_caller 'SRC-CLI',customer_src_callee 'SRC-DST',customer_incodecs 'Incoming-Codecs',carrier_outcodecs 'Outgoing-Codecs',call_codecs  'Call\'s-Codec',customer_tariff_id 'User-Tariff',customer_prefix 'Prefix',customer_destination 'Destination',
					reseller1_account_id 'R1-Account',reseller1_tariff_id 'R1-Tariff',	reseller1_prefix 'R1-Prefix',reseller1_destination 'R1-DST',	
					reseller2_account_id 'R2-Account',reseller2_tariff_id 'R2-Tariff', reseller2_prefix 'R2-Prefix',reseller2_destination 'R2-DST',
					reseller3_account_id 'R3-Account',reseller3_tariff_id 'R3-Tariff',	reseller3_prefix 'R3-Prefix',reseller3_destination 'R3-DST',
					carrier_dialplan_id 'Routing',
					carrier_id 'Carrier',carrier_tariff_id 'C-Tariff', carrier_prefix 'C-Prefix',carrier_destination 'C-Destination',
					carrier_ipaddress 'C-IP',carrier_src_caller 'USER-CLI',carrier_src_callee 'User-DST',
					carrier_dst_caller 'C-CLI',carrier_dst_callee 'C-DST',start_stamp 'Start Time', end_stamp 'End Time', duration 'Duration',billsec 'Org-Duration',Q850CODE,SIPCODE,concat(fscause,'<br>',fs_errorcode) 'FS-Cause',hangupby, cdr_type
					FROM $table  WHERE billsec=0 ";
            //fscause != 'NORMAL_CLEARING' 


            if (trim($search_data['s_cdr_customer_account']) != '') {
                if ($search_data['s_cdr_customer_type'] == 'U')
                    $sql .= " AND customer_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R1')
                    $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R2')
                    $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R3')
                    $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
            }
            if (trim($search_data['s_cdr_cdr_type']) != '')
                $sql .= " AND cdr_type = '" . trim($search_data['s_cdr_cdr_type']) . "' ";
            if (trim($search_data['s_cdr_dialed_no']) != '')
                $sql .= " AND customer_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
            if (trim($search_data['s_cdr_carrier_dst_no']) != '')
                $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
            if (trim($search_data['s_cdr_customer_cli']) != '')
                $sql .= " AND customer_src_caller like '" . trim($search_data['s_cdr_customer_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier_cli']) != '')
                $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier']) != '')
                $sql .= " AND carrier_id like '" . trim($search_data['s_cdr_carrier']) . "%' ";
            if (trim($search_data['s_cdr_carrier_ip']) != '')
                $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
            if (trim($search_data['s_cdr_customer_ip']) != '')
                $sql .= " AND customer_src_ip = '" . trim($search_data['s_cdr_customer_ip']) . "' ";
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
            if (trim($search_data['s_cdr_customer_company_name']) != '')
                $sql .= " AND customer_company_name LIKE '%" . trim($search_data['s_cdr_customer_company_name']) . "%' ";

            if (isset($search_data['s_cdr_customer_type_login']) and $search_data['s_cdr_customer_type_login'] == 'CUSTOMER') {

                $account_id_str = $search_data['s_cdr_customer_account'];
                $sql .= " AND customer_account_id IN('" . $account_id_str . "')";
            } elseif (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM account WHERE parent_account_id='" . $search_data['s_parent_account_id'] . "'";
                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql .= " AND customer_account_id IN(" . $account_id_str . ")";
            }

            $group_by = '';
            $orderby = ' order by id desc ';
            $query = $sql . $group_by . $orderby;


            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $query .= " LIMIT $limit_from, $limit_to";
            else
                $query .= " LIMIT 2000";

            //  echo $query;
            $result = $DB1->query($query);

            if (!$result){
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

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

    function api_analytics_cdr_in($search_data, $limit_to = '', $limit_from = '') { //print_r($search_data);
        try {
            $DB1 = $this->load->database('cdrdb', true);

            $range = explode(' - ', $search_data['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt = $range[0];
            $end_dt = $range[1];

            $sql = "SELECT SQL_CALC_FOUND_ROWS cdr_id, customer_account_id Account,customer_company_name, customer_src_caller 'SRC-CLI', customer_src_callee 'SRC-DST', customer_src_ip 'SRC-IP',customer_incodecs 'Incoming-Codecs',carrier_outcodecs 'Outgoing-Codecs',call_codecs 'Call\'s-Codec',customer_tariff_id_name 'User-Tariff',	customer_prefix 'Prefix', customer_destination 'Destination', customer_duration 'Duration',
			customer_callcost_total 'Cost',	reseller1_account_id 'R1-Account',	reseller1_tariff_id_name 'R1-Tariff',reseller1_duration 'R1-Duration',
			reseller1_callcost_total 'R1-Cost',	reseller2_account_id 'R2-Account',	reseller2_tariff_id_name 'R2-Tariff', reseller2_duration 'R2-Duration',
			reseller2_callcost_total 'R2-Cost',	reseller3_account_id 'R3-Account',	reseller3_tariff_id_name 'R3-Tariff', reseller3_duration 'R3-Duration',
			reseller3_callcost_total 'R3-Cost', carrier_dialplan_id_name 'Routing',	carrier_carrier_id 'Carrier', carrier_gateway_ipaddress 'C-IP',
			carrier_src_caller 'USER-CLI',	carrier_src_callee 'User-DST', carrier_dst_caller 'C-CLI', carrier_dst_callee 'C-DST', carrier_tariff_id_name 'C-Tariff',
			carrier_prefix 'C-Prefix',	carrier_destination 'C-Destination', carrier_duration 'C-Duration',	carrier_callcost_total 'C-Cost',
			billsec 'Org-Duration',	start_time 'Start Time', answer_time 'Answer Time',	end_time 'End time', Q850CODE, SIPCODE , hangupby,lrn_number
			FROM " . $DB1->dbprefix('bill_cdrs_incoming') . " WHERE 1 ";

            if (trim($search_data['s_cdr_customer_account']) != '') {
                if ($search_data['s_cdr_customer_type'] == 'U')
                    $sql .= " AND customer_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R1')
                    $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R2')
                    $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R3')
                    $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
            }

            if (trim($search_data['s_cdr_dialed_no']) != '')
                $sql .= " AND customer_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
            if (trim($search_data['s_cdr_carrier_dst_no']) != '')
                $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
            if (trim($search_data['s_cdr_customer_cli']) != '')
                $sql .= " AND customer_src_caller like '" . trim($search_data['s_cdr_customer_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier_cli']) != '')
                $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier']) != '')
                $sql .= " AND carrier_  like '" . trim($search_data['s_cdr_carrier']) . "%' ";
            if (trim($search_data['s_cdr_carrier_ip']) != '')
                $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
            if (trim($search_data['s_cdr_customer_ip']) != '')
                $sql .= " AND customer_src_ip = '" . trim($search_data['s_cdr_customer_ip']) . "' ";


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
            if (trim($search_data['s_cdr_customer_company_name']) != '')
                $sql .= " AND customer_company_name LIKE '%" . trim($search_data['s_cdr_customer_company_name']) . "%' ";

            /* ------------------------------ */


            if (isset($search_data['s_sales_manager']) && $search_data['s_sales_manager'] != '') {
                $sub_sub_sql = "SELECT customer_access_id_name FROM " . $this->db->dbprefix('customer_access') . " WHERE customer_type='ACCOUNTMANAGER' AND sales_manager='" . $search_data['s_sales_manager'] . "'";

                $sub_sql = "SELECT GROUP_CONCAT(\"'\",customer_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('customer_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;

                $sql .= " AND customer_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_account_manager']) && $search_data['s_account_manager'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",customer_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('customer_access') . " WHERE account_manager='" . $search_data['s_account_manager'] . "'";

                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql .= " AND customer_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                /* $sub_sql = ""; */
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
					customer_account_id 'Account',customer_company_name,customer_src_ip 'SRC-IP',customer_src_caller 'SRC-CLI',customer_src_callee 'SRC-DST',
					customer_incodecs 'Incoming-Codecs',carrier_outcodecs 'Outgoing-Codecs',call_codecs 'Call\'s-Codec',customer_tariff_id_name 'User-Tariff',customer_prefix 'Prefix',customer_destination 'Destination',
					reseller1_account_id 'R1-Account',reseller1_tariff_id_name 'R1-Tariff',	reseller1_prefix 'R1-Prefix',reseller1_destination 'R1-DST',	
					reseller2_account_id 'R2-Account',reseller2_tariff_id_name 'R2-Tariff', reseller2_prefix 'R2-Prefix',reseller2_destination 'R2-DST',
					reseller3_account_id 'R3-Account',reseller3_tariff_id_name 'R3-Tariff',	reseller3_prefix 'R3-Prefix',reseller3_destination 'R3-DST',
					carrier_dialplan_id_name 'Routing',
					carrier_id  'Carrier',carrier_tariff_id_name 'C-Tariff', carrier_prefix 'C-Prefix',carrier_destination 'C-Destination',
					carrier_gateway_ipaddress 'C-IP',carrier_src_caller 'USER-CLI',carrier_src_callee 'User-DST',
					carrier_dst_caller 'C-CLI',carrier_dst_callee 'C-DST',start_stamp 'Start Time',duration 'Duration',billsec 'Org-Duration',Q850CODE,SIPCODE,concat(fscause,'<br>',fs_errorcode) 'FS-Cause',hangupby,lrn_number
					FROM " . $DB1->dbprefix('cdrs_incoming') . " WHERE 1 ";


            if (trim($search_data['s_cdr_customer_account']) != '') {
                if ($search_data['s_cdr_customer_type'] == 'U')
                    $sql .= " AND customer_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R1')
                    $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R2')
                    $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
                elseif ($search_data['s_cdr_customer_type'] == 'R3')
                    $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
            }

            if (trim($search_data['s_cdr_dialed_no']) != '')
                $sql .= " AND customer_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
            if (trim($search_data['s_cdr_carrier_dst_no']) != '')
                $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
            if (trim($search_data['s_cdr_customer_cli']) != '')
                $sql .= " AND customer_src_caller like '" . trim($search_data['s_cdr_customer_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier_cli']) != '')
                $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
            if (trim($search_data['s_cdr_carrier']) != '')
                $sql .= " AND carrier_id like '" . trim($search_data['s_cdr_carrier']) . "%' ";


            if (trim($search_data['s_cdr_carrier_ip']) != '')
                $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
            if (trim($search_data['s_cdr_customer_ip']) != '')
                $sql .= " AND customer_src_ip = '" . trim($search_data['s_cdr_customer_ip']) . "' ";
            if (trim($search_data['s_cdr_sip_code']) != '')
                $sql .= " AND SIPCODE = '" . trim($search_data['s_cdr_sip_code']) . "' ";
            if (trim($search_data['s_cdr_Q850CODE']) != '')
                $sql .= " AND Q850CODE = '" . trim($search_data['s_cdr_Q850CODE']) . "' ";
            if (trim($search_data['s_time_range']) != '')
                $sql .= " AND start_stamp >= '" . $start_dt . "' AND end_stamp <= '" . $end_dt . "' ";


            /* ------------------------------ */
            if (trim($search_data['s_cdr_customer_company_name']) != '')
                $sql .= " AND customer_company_name LIKE '%" . trim($search_data['s_cdr_customer_company_name']) . "%' ";

            /* ------------------------------ */

            if (isset($search_data['s_sales_manager']) && $search_data['s_sales_manager'] != '') {
                $sub_sub_sql = "SELECT customer_access_id_name FROM " . $this->db->dbprefix('customer_access') . " WHERE customer_type='ACCOUNTMANAGER' AND sales_manager='" . $search_data['s_sales_manager'] . "'";

                $sub_sql = "SELECT GROUP_CONCAT(\"'\",customer_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('customer_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;

                $sql .= " AND customer_account_id IN(" . $account_id_str . ")";
            } elseif (isset($search_data['s_account_manager']) && $search_data['s_account_manager'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",customer_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('customer_access') . " WHERE account_manager='" . $search_data['s_account_manager'] . "'";

                /////////////
                $query = $this->db->query($sub_sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $row = $query->row();
                $account_id_str = $row->account_ids;
                /////////////
                $sql .= " AND customer_account_id IN(" . $account_id_str . ")";
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
						customer_sdr.rule_type,
						customer_sdr.action_date,
						customer_sdr.service_number,
						customer_sdr.total_cost,
						customer_sdr.service_startdate,
						customer_sdr.service_stopdate
						FROM " . $this->db->dbprefix('customer_sdr') . "
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
//           echo $sql;
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
            $sql = "SELECT us.account_id, us.rule_type, DATE_FORMAT(us.action_date,'%d/%m/%Y') date_formatted, SUM(us.total_cost) sum_total_cost, SUM(us.total_seller_cost) sum_total_seller_cost, ua.company_name
			FROM " . $this->db->dbprefix('customer_sdr') . " us LEFT JOIN " . $this->db->dbprefix('customer_access') . " ua ON us.account_id=ua.customer_access_id_name
			WHERE 
			 rule_type IN('DAILYUSAGE', 'DAILYUSAGEIN', 'DIDEXTRACHRENTAL', 'DIDRENTAL', 'DIDSETUP', 'TARIFFCHARGES', 'ADDBALANCE', 'REMOVEBALANCE', 'ADDCREDIT', 'REMOVECREDIT')
			AND (total_cost >0 OR total_seller_cost>0)";

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'account_manager') {
                        $sub_sql = "SELECT customer_access_id_name FROM " . $this->db->dbprefix('customer_access') . " WHERE account_manager='" . $value . "'";
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
					customer_account_id,customer_company_name,
					customer_destination 'destination', 
					COUNT(cdr_id) 'connected_calls', 
					SUM(customer_duration) 'duration', 
					SUM(customer_callcost_total) 'cost' 
					FROM " . $this->db->dbprefix('bill_cdrs') . "
					WHERE 1 ";


            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {

                    if ($key == 'account_manager' && $value != '') {
                        $sub_sql = "SELECT GROUP_CONCAT(\"'\",customer_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('customer_access') . " WHERE account_manager='" . $value . "'";

                        /////////////
                        $query = $this->db->query($sub_sql);
                        if (!$query) {
                            $error_array = $this->db->error();
                            throw new Exception($error_array['message']);
                        }
                        $row = $query->row();
                        $account_id_str = $row->account_ids;
                        /////////////
                        $sql .= " AND customer_account_id IN(" . $account_id_str . ")";
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
                        $sql .= " AND customer_account_id IN(" . $account_id_str . ")";
                    } elseif ($key == 'action_month' && $value != '') {
                        $sql .= " AND DATE_FORMAT(end_time, '%Y-%m')='" . $value . "' ";
                    } elseif ($key == 'action_date' && $value != '') {
                        $sql .= " AND DATE_FORMAT(end_time, '%Y-%m-%d')='" . $value . "' ";
                    } elseif ($key == 'customer_account_id' && $value != '') {
                        $sql .= " AND customer_account_id='" . $value . "' ";
                    } else if ($key == 'customer_company_name' && $value != '') {

                        $sql .= " AND customer_company_name LIKE '%" . $value . "%' ";
                    }
                }
            }

            if (isset($filter_data['groupby_account']) && $filter_data['groupby_account'] == 'Y')
                $sql .= " GROUP BY customer_account_id ORDER BY Cost desc";
            else
                $sql .= " GROUP BY customer_account_id, Destination ORDER BY Cost desc";
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
                        if ($key == 'customer_account_id' || $key == 'carrier_carrier_id') {
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
					customer_account_id AS `customer`,
					carrier_carrier_id AS `carrier`, 
					count(cdr_id) AS `answered_calls`,
					sum(customer_duration)/60 AS `minute_usage_cost`,
					SUM(carrier_callcost_total_usercurrency) AS `carrier_cost`, 
					SUM(profit_usercurrency) AS `profit`, 
					customer_customer_currency_id AS `currency`					
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

    function CarrierUsage($search_data, $limit_to = '', $limit_from = '') {
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
                        elseif (in_array($key, array('s_parent_account_id'))) {
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

                $sql = "SELECT carrier_daily_usage.carrier_account,
 carrier_daily_usage.carrier_name,carrier_daily_usage.prefix,carrier_daily_usage.destination, carrier_daily_usage.currency_name,  round((sum(carrier_daily_usage.answercalls)/sum(carrier_daily_usage.totalcalls))*100,2) asr,  round((sum(carrier_daily_usage.out_minute) * 60) / sum(carrier_daily_usage.answercalls),0) acd,   sum(carrier_daily_usage.answercalls) answercalls, round(sum(carrier_daily_usage.out_minute),0) out_minute,  ROUND(sum(carrier_daily_usage.carriercost),2) carriercost, sum(code402) code402 ,sum(code403 ) code403,  sum(code404 ) code404,sum(code407) code407,sum( code500) code500,sum( code503) code503,sum( code487) code487,sum( code488) code488,sum( code501) code501,  sum( code483) code483,sum( code410) code410,sum( code515) CCLimit ,sum( code486) code486,sum( code480) code480 ,  calls_date, DATE_FORMAT(calls_date, '%m-%Y') calls_date_month FROM carrier_daily_usage";
                $orderby = ' ORDER BY calls_date_month DESC ';
            } else {
                $sql = "select SQL_CALC_FOUND_ROWS carrier_account, carrier_name,prefix,destination,currency_name,asr,acd,answercalls,totalcalls,carriercost,out_minute,code402,
code403 ,code404 ,code407, code500, code503, code487, code488, code501, code483,code410, code515, code486, code480,calls_date, DATE_FORMAT(calls_date, '%m-%Y') calls_date_month FROM carrier_daily_usage ";

                $orderby = ' ORDER BY carrier_daily_usage_id DESC ';
            }
            if (isset($search_data['s_parent_account_id']) && $search_data['s_parent_account_id'] != '') {
                $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM account WHERE parent_account_id='" . $search_data['s_parent_account_id'] . "'";
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


            //  echo $query; //die;
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



            $sql = " SELECT payment_option_id, SUM(amount) sum_amount, DATE_FORMAT(`paid_on`,'%d-%m-%Y') date_formatted, u.currency_id customer_currency_id 
			FROM payment_history ph INNER JOIN  account u ON ph.account_id =u.account_id
			WHERE `payment_option_id` IN ('ADDBALANCE','REMOVEBALANCE') AND paid_on BETWEEN '$start_dt' AND '$end_dt'";
            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND ph.account_id ='" . $value . "'";
                        } elseif (in_array($key, array('s_account_manager', 's_parent_account_id', 's_sales_manager'))) {
                            continue;
                        }
                    }
                }
            }


            $where = '';
            if (isset($search_data['parent_account_id']) && $search_data['parent_account_id'] != '') {
                $sub_sql = "SELECT account_id FROM account WHERE parent_account_id='" . $search_data['parent_account_id'] . "' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            } else {
                $sub_sql = "SELECT account_id FROM account WHERE parent_account_id='' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            }

            $sql .= $where;
            $sql .= " GROUP BY date_formatted, u.currency_id, payment_option_id ";
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
                    $customer_currency_id = $row['customer_currency_id'];

                    $final_return_array['result'][$customer_currency_id][$date_formatted][$payment_option_id] = $row;
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



            $sql = " SELECT payment_option_id, SUM(amount) sum_amount, DATE_FORMAT(`paid_on`,'%Y-%m') date_formatted, u.currency_id customer_currency_id  FROM payment_history ph INNER JOIN account u ON ph.account_id =u.account_id WHERE `payment_option_id` IN ('ADDBALANCE','REMOVEBALANCE') AND paid_on BETWEEN '$start_dt' AND '$end_dt'";
            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND ph.account_id ='" . $value . "'";
                        } elseif (in_array($key, array('s_account_manager', 's_parent_account_id', 's_sales_manager'))) {
                            continue;
                        }
                    }
                }
            }


            $where = '';
            if (isset($search_data['parent_account_id']) && $search_data['parent_account_id'] != '') {
                /* $sub_sql = ""; */
                $sub_sql = "SELECT account_id FROM account WHERE parent_account_id='" . $search_data['parent_account_id'] . "' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            } else {
                /* $sub_sql = ""; */
                $sub_sql = "SELECT account_id FROM account WHERE parent_account_id='' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            }

            $sql .= $where;
            $sql .= " GROUP BY date_formatted, u.currency_id, payment_option_id ";
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
                    $customer_currency_id = $row['customer_currency_id'];

                    $final_return_array['result'][$customer_currency_id][$date_formatted][$payment_option_id] = $row;
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

    function CRecharge($search_data) {
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

            $sql = " SELECT payment_option_id, SUM(amount) sum_amount, ph.account_id, ua.company_name
			FROM payment_history ph LEFT JOIN customers ua 
			ON ph.account_id=ua.account_id
			WHERE `payment_option_id` IN ('ADDBALANCE','REMOVEBALANCE') AND paid_on BETWEEN '$start_dt' AND '$end_dt'";
            if (count($search_data) > 0) {
                foreach ($search_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND ph.account_id ='" . $value . "'";
                        }
                    }
                }
            }


            $where = '';
            if (isset($search_data['parent_account_id']) && $search_data['parent_account_id'] != '') {


                $sub_sql = "SELECT account_id FROM account WHERE parent_account_id='" . $search_data['parent_account_id'] . "' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            } else {


                $sub_sql = "SELECT account_id FROM account WHERE parent_account_id='' ";
                $where .= " AND ph.account_id IN(" . $sub_sql . ")";
            }

            $sql .= $where;
            $sql .= " GROUP BY ph.account_id, payment_option_id ";
            $sql .= " ORDER BY ph.account_id";
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
                    $company_name = $row['company_name'];


                    $final_return_array['result'][$account_id][$payment_option_id] = $sum_amount;
                    $final_return_array['result'][$account_id]['company_name'] = $company_name;
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

        $sql = "SELECT cdr_id, customer_account_id Account, customer_src_caller 'SRC-CLI', customer_src_callee 'SRC-DST', customer_src_ip 'SRC-IP',
					customer_tariff_id_name 'User-Tariff',	customer_prefix 'Prefix', customer_destination 'Destination', customer_duration 'Duration',
					customer_callcost_total 'Cost',	reseller1_account_id 'R1-Account',	reseller1_tariff_id_name 'R1-Tariff',reseller1_duration 'R1-Duration',
					reseller1_callcost_total 'R1-Cost',	reseller2_account_id 'R2-Account',	reseller2_tariff_id_name 'R2-Tariff', reseller2_duration 'R2-Duration',
					reseller2_callcost_total 'R2-Cost',	reseller3_account_id 'R3-Account',	reseller3_tariff_id_name 'R3-Tariff', reseller3_duration 'R3-Duration',
					reseller3_callcost_total 'R3-Cost', carrier_dialplan_id_name 'Routing',	carrier_carrier_id 'Carrier', carrier_gateway_ipaddress 'C-IP',
					carrier_src_caller 'USER-CLI',	carrier_src_callee 'User-DST', carrier_dst_caller 'C-CLI', carrier_dst_callee 'C-DST', carrier_tariff_id_name 'C-Tariff',
					carrier_prefix 'C-Prefix',	carrier_destination 'C-Destination', carrier_duration 'C-Duration',	carrier_callcost_total 'C-Cost',
                    billsec 'Org-Duration',	start_time 'Start Time', answer_time 'Answer Time',	end_time 'End time', Q850CODE, SIPCODE , hangupby
					FROM " . $DB1->dbprefix('bill_cdrs_incoming') . " WHERE 1 "; //bill_cdrs_incoming


        if (trim($search_data['s_cdr_customer_account']) != '') {
            if ($search_data['s_cdr_customer_type'] == 'U')
                $sql .= " AND customer_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
            elseif ($search_data['s_cdr_customer_type'] == 'R1')
                $sql .= " AND reseller1_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
            elseif ($search_data['s_cdr_customer_type'] == 'R2')
                $sql .= " AND reseller2_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
            elseif ($search_data['s_cdr_customer_type'] == 'R3')
                $sql .= " AND reseller3_account_id = '" . trim($search_data['s_cdr_customer_account']) . "' ";
        }

        if (trim($search_data['s_cdr_dialed_no']) != '')
            $sql .= " AND customer_src_callee like '" . trim($search_data['s_cdr_dialed_no']) . "%' ";
        if (trim($search_data['s_cdr_carrier_dst_no']) != '')
            $sql .= " AND carrier_dst_callee like '" . trim($search_data['s_cdr_carrier_dst_no']) . "%' ";
        if (trim($search_data['s_cdr_customer_cli']) != '')
            $sql .= " AND customer_src_caller like '" . trim($search_data['s_cdr_customer_cli']) . "%' ";
        if (trim($search_data['s_cdr_carrier_cli']) != '')
            $sql .= " AND carrier_dst_caller like '" . trim($search_data['s_cdr_carrier_cli']) . "%' ";
        if (trim($search_data['s_cdr_carrier']) != '')
            $sql .= " AND carrier_carrier_id like '" . trim($search_data['s_cdr_carrier']) . "%' ";
        if (trim($search_data['s_cdr_carrier_ip']) != '')
            $sql .= " AND carrier_gateway_ipaddress = '" . trim($search_data['s_cdr_carrier_ip']) . "' ";
        if (trim($search_data['s_cdr_customer_ip']) != '')
            $sql .= " AND customer_src_ip = '" . trim($search_data['s_cdr_customer_ip']) . "' ";



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
//        die;
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
					FROM " . $this->db->dbprefix('connected_calls') . " connected_calls INNER JOIN " . $this->db->dbprefix('carrier') . " carrier ON connected_calls.carrier_id = carrier.carrier_id 
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
					FROM " . $this->db->dbprefix('connected_calls') . " connected_calls INNER JOIN " . $this->db->dbprefix('carrier') . " carrier ON connected_calls.carrier_id = carrier.carrier_id 
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
					FROM " . $this->db->dbprefix('connected_calls') . " connected_calls INNER JOIN " . $this->db->dbprefix('carrier') . " carrier ON connected_calls.carrier_id = carrier.carrier_id 
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
					FROM " . $this->db->dbprefix('connected_calls') . " connected_calls INNER JOIN " . $this->db->dbprefix('carrier') . " carrier ON connected_calls.carrier_id = carrier.carrier_id 
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

    function reseller_call_sipcode_review($account_id, $date_from, $date_to, $src_ipaddress = '', $prefix_name = '', $account_level = 1) {
        $final_return_array = array();
        try {
            $DB1 = $this->load->database('cdrdb', true);
            $sql = "SELECT 
				sum(totalcalls) totalcalls, 
				sum(answeredcalls) answeredcalls, 
				SUM(bill_duration) bill_duration,
				sum(totalcalls) - sum(answeredcalls) unansweredcalls, 
				sum(customer_cost) customer_cost,
				SIPCODE sipcode, 
				prefix_name,
				account_id 
				FROM " . date('Ym') . "_customerstate 
				WHERE ";
            if ($account_level == '1')
                $sql .= " r1_account_id = '" . $account_id . "' ";
            elseif ($account_level == '2')
                $sql .= " r2_account_id = '" . $account_id . "' ";
            elseif ($account_level == '3')
                $sql .= " r3_account_id = '" . $account_id . "' ";
            else
                $sql .= " 1 ";


            $sql .= " AND concat(call_date, ' ',calltime_h,':', calltime_m,':00') BETWEEN '" . $date_from . "' AND '" . $date_to . "'";

            if ($src_ipaddress != '')
                $sql .= " AND src_ipaddress like '%" . $src_ipaddress . "%' ";
            if ($prefix_name != '')
                $sql .= " AND prefix_name like '%" . $prefix_name . "%' ";
            $sql .= " GROUP BY SIPCODE, prefix_name";

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

    function customer_call_sipcode_review($account_id, $date_from, $date_to, $src_ipaddress = '', $prefix_name = '') {
        $final_return_array = array();
        try {
            $DB1 = $this->load->database('cdrdb', true);
            $sql = "SELECT 
				sum(totalcalls) totalcalls, 
				sum(answeredcalls) answeredcalls, 
				SUM(bill_duration) bill_duration,
				sum(totalcalls) - sum(answeredcalls) unansweredcalls, 
				sum(customer_cost) customer_cost,
				SIPCODE sipcode, 
				prefix_name,
				account_id 
				FROM " . date('Ym') . "_customerstate 
				WHERE 
				account_id = '" . $account_id . "' 
				AND concat(call_date, ' ',calltime_h,':', calltime_m,':00') BETWEEN '" . $date_from . "' AND '" . $date_to . "'";

            if ($src_ipaddress != '')
                $sql .= " AND src_ipaddress like '%" . $src_ipaddress . "%' ";
            if ($prefix_name != '')
                $sql .= " AND prefix_name like '%" . $prefix_name . "%' ";

            $sql .= " GROUP BY SIPCODE, prefix_name";
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

    /* updated uses switch_customer_daily_usage table */

    function get_businesHistory($search_data, $limit_to = '', $limit_from = '') {
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
                            $where .= " customer_currency_id ='" . $value . "' ";
                        }
                        elseif ($key == 'account_id') {
                            if ($where != '')
                                $where .= ' AND ';
                            $where .= " $key ='" . $value . "' ";
                        }
                        elseif (in_array($key, array('s_parent_account_id'))) {
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
				customer_currency_id currency_id,
				customer_currency_id_name currency,				
				action_date record_date, 
				DATE_FORMAT(action_date, '%m-%Y') record_date_month,
                                  ROUND(tariff_net_cost,2) 'tariff_net_cost',
				ROUND(SUM(answeredcalls)) 'calls_out',
				ROUND(SUM(account_duration)/60) 'mins_out', 
				ROUND(SUM(callcost_net),2) 'customer_cost_out', 
				ROUND(SUM(callcost_net_carrier),2) 'carrier_cost_out',
				ROUND(SUM(answeredcalls_in)) 'calls_in', 
				ROUND(SUM(account_duration_in)/60) 'mins_in', 
				ROUND(SUM(callcost_net_in),2) 'customer_cost_in', 
				ROUND(SUM(callcost_net_carrier_in),2) 'carrier_cost_in', 				
ROUND(SUM(did_extra_channel_cost_net + did_rental_cost_net + did_setup_cost_net),2) 'did_setup_rental_customer_cost', 
ROUND(SUM(did_extra_channel_cost_net_carrier + did_rental_cost_net_carrier + did_setup_cost_net_carrier),2) 'did_setup_rental_carrier_cost',  	


				ROUND(SUM(tariff_net_cost+callcost_net_in + callcost_net + callcost_net_in + did_extra_channel_cost_net + did_rental_cost_net + did_setup_cost_net) - sum(callcost_net_carrier + callcost_net_carrier_in + callcost_net_carrier_in + did_extra_channel_cost_net_carrier + did_rental_cost_net_carrier + did_setup_cost_net_carrier),2) 'profit' , 
				
				SUM(credit) credit_added,
				SUM(credit_remove) credit_remove,
				SUM(payment) payment
				  FROM customer_daily_usages ";

                $orderby = ' ORDER BY record_date_month desc ';
            } else {
                $sql = "SELECT SQL_CALC_FOUND_ROWS 
				id,
				account_id, 
				company_name,				
				customer_currency_id currency_id,
				customer_currency_id_name currency,				
				action_date record_date, 
				DATE_FORMAT(action_date, '%m-%Y') record_date_month,
                                ROUND(tariff_net_cost,2) 'tariff_net_cost',
				ROUND(answeredcalls) 'calls_out',
				ROUND(account_duration/60) 'mins_out', 
				ROUND(callcost_net,2) 'customer_cost_out', 
				ROUND(callcost_net_carrier,2) 'carrier_cost_out',
				ROUND(answeredcalls_in) 'calls_in', 
				ROUND(account_duration_in/60) 'mins_in', 
				ROUND(callcost_net_in,2) 'customer_cost_in', 
				ROUND(callcost_net_carrier_in,2) 'carrier_cost_in',
				ROUND((did_extra_channel_cost_net + did_rental_cost_net + did_setup_cost_net),2) 'did_setup_rental_customer_cost', 
				ROUND((tariff_net_cost+did_extra_channel_cost_net_carrier + did_rental_cost_net_carrier + did_setup_cost_net_carrier),2) 'did_setup_rental_carrier_cost', 
				
				
				ROUND(SUM(callcost_net_in + callcost_net + callcost_net_in + did_extra_channel_cost_net + did_rental_cost_net + did_setup_cost_net) - sum(callcost_net_carrier + callcost_net_carrier_in + callcost_net_carrier_in + did_extra_channel_cost_net_carrier + did_rental_cost_net_carrier + did_setup_cost_net_carrier),2) 'profit' , 
				
				credit credit_added,
				credit_remove,
				payment  						
				
				FROM customer_daily_usages  ";

                $orderby = ' ORDER BY id desc ';
            }

            if (isset($search_data['s_parent_account_id'])) {   //&& $search_data['s_parent_account_id']!=''
                $sub_sql = "SELECT account_id FROM account WHERE parent_account_id='" . $search_data['s_parent_account_id'] . "' ";
                if ($where != '')
                    $where .= ' AND ';
                $where .= " account_id IN(" . $sub_sql . ")";
            }else {
                if ($where != '')
                    $where .= ' AND ';
                $where .= " parent_account_id is null or parent_account_id = '' ";
            }

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
//            echo $query; //die;
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

    function supplier_netting($supplier_id_name, $from_date, $to_date) {
        $final_return_array = array();
        try {
            $sql_in = "SELECT si_in.* , s.supplier_name, 'IN' invoice_type
					FROM " . $this->db->dbprefix('supplier_invoice_in') . " si_in 
					LEFT JOIN " . $this->db->dbprefix('suppliers') . " s ON si_in.supplier_id_name=s.supplier_id_name 
			 WHERE si_in.delete_status='0' AND si_in.status='accepted'";

            $sql_in .= " AND si_in.supplier_id_name ='" . $supplier_id_name . "' ";
            $sql_in .= " AND si_in.invoice_date BETWEEN '" . $from_date . "' AND '" . $to_date . "' ";

            $sql_out = "SELECT si_out.* , s.supplier_name, 'OUT' invoice_type
					FROM " . $this->db->dbprefix('supplier_invoice_out') . " si_out 
					LEFT JOIN " . $this->db->dbprefix('suppliers') . " s ON si_out.supplier_id_name=s.supplier_id_name 
			 WHERE si_out.delete_status='0' AND si_out.status='accepted'";

            $sql_out .= " AND si_out.supplier_id_name ='" . $supplier_id_name . "' ";
            $sql_out .= " AND si_out.invoice_date BETWEEN '" . $from_date . "' AND '" . $to_date . "' ";

            if ($sql_where != '') {
                $sql_in .= $sql_where;
                $sql_out .= $sql_where;
            }


            $sql = "SELECT * FROM (" . $sql_in . " UNION ALL " . $sql_out . ") t_table";

            $sql .= " ORDER BY invoice_date";


            //echo $sql;
            $query = $this->db->query($sql);

            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }


            foreach ($query->result_array() as $row) {
                $final_return_array['result'][] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['sql'] = $sql;
            $final_return_array['message'] = 'Supplier Invoices fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

}
