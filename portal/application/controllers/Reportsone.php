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

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Reportsone extends CI_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();

        $this->form_validation->set_error_delimiters('', '');
        //permission check		
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
    }

    public function index() {
        $page_name = "report_index";
        $this->livecall();
    }

    public function api_livecall() {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
            die();
        }
        $logged_user_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();


        $DB1 = $this->load->database('cdrdb', true);

        $sql = "select user_account_id, user_src_ip, user_destination,user_src_caller, user_src_callee, carrier_carrier_id_name, carrier_gateway_ipaddress, carrier_gateway_ipaddress_name, carrier_dialplan_id_name, start_time, answer_time, TIMESTAMPDIFF(SECOND , answer_time, NOW()) as duration, callstatus, fs_host, notes 
		FROM switch_livecalls 
		WHERE callstatus in ('answer','ring','progress') ";


        if (check_logged_account_type(array('ACCOUNTMANAGER'))) {
            $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $logged_account_id . "'";

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
        } elseif (check_logged_account_type(array('SALESMANAGER'))) {
            $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $logged_account_id . "'";

            $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

            $query = $this->db->query($sub_sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $row = $query->row();
            $account_id_str = $row->account_ids;

            $sql .= " AND user_account_id IN(" . $account_id_str . ")";
        } elseif (check_logged_account_type(array('RESELLER'))) {
            $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM " . $this->db->dbprefix('user') . " WHERE parent_account_id='" . $logged_account_id . "'";
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






        $sql .= " ORDER BY livecalls_id desc limit 1000";
        $result = $DB1->query($sql);

        $return['allCalls'] = $result->num_rows();
        $return['data'] = $result->result_array();

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
        //->set_output(json_encode(array('foo' => 'bar')));
    }

    public function livecall() {
        $data['page_name'] = "report_livecall";

        //check page action permission
        if (!check_account_permission('reports', 'live'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////			

        $this->load->view('basic/header', $data);
        $this->load->view('reports/livecall', $data);
        $this->load->view('basic/footer', $data);
    }

    private function api_analytics($search_data) {


        $range = explode(' - ', $search_data['timerange']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);

        $start_dt = $range_from[0];
        $start_hh = substr($range_from[1], 0, strpos($range_from[1], ':'));
        $start_mm = substr($range_from[1], strpos($range_from[1], ':') + 1);

        $end_dt = $range_to[0];
        $end_hh = substr($range_to[1], 0, strpos($range_to[1], ':'));
        $end_mm = substr($range_to[1], strpos($range_to[1], ':') + 1);


        $str = "select sum(totalcalls) as total_calls, sum(answeredcalls) as answered_calls , round((sum(answeredcalls)/sum(totalcalls))*100,2) as asr, ifnull(round((sum(account_duration)/sum(answeredcalls))/60,2),0.00) as acd, ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd, ";

        if ($search_data['account_type'] == 'U')
            $str .= " round(sum(account_duration)/60,2) as total_duration ";
        else {

            $str .= " round(sum(r1_duration)/60,2) as total_duration ";
        }

        if ($search_data['group_by_user'] == 'Y') {
            if ($search_data['account_type'] == 'U')
                $str .= " ,account_id  as account_code ";
            else {
                $str .= " ,r1_account_id  as account_code ";
            }
        }

        if ($search_data['group_by_carrier'] == 'Y')
            $str .= " ,carrier_id_name ";
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
                $str .= " ,round(sum(account_cost)*1.0000000000000000,2)  as cost ";
            else
                $str .= " ,round(sum(r1_cost)*1.0000000000000000,2)  as cost ";

            $str .= " ,account_currency_id as currency_id";
        }
        /// showing account's cost///	
        //////////////////////////////

        $str .= " from switch_calls_statistics where date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "'
and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";

        if ($search_data['account_id'] != '') {
            if ($search_data['account_type'] == 'U')
                $str .= " and account_id = '" . $search_data['account_id'] . "'";
            else {

                if (isset($search_data['logged_user_type']) && isset($search_data['logged_user_level']) && $search_data['logged_user_type'] == 'RESELLER' && in_array($search_data['logged_user_level'], array(1, 2, 3))) {
                    $level = $search_data['logged_user_level'] + 1;
                    $field_name = 'r' . $level . '_account_id';

                    $str .= " AND `" . $field_name . "` = '" . $search_data['account_id'] . "'";
                } else {
                    $str .= " and r1_account_id = '" . $search_data['account_id'] . "'";
                }
            }
        }


        ////
        if (isset($search_data['logged_user_type']) && isset($search_data['logged_user_account_id']) && isset($search_data['logged_user_level']) && $search_data['logged_user_type'] == 'RESELLER' && in_array($search_data['logged_user_level'], array(1, 2, 3))) {
            $level = $search_data['logged_user_level'];
            $field_name = 'r' . $level . '_account_id';

            $str .= " AND `" . $field_name . "` = '" . $search_data['logged_user_account_id'] . "'";
        }
        //
        if (isset($search_data['logged_user_type']) && isset($search_data['logged_user_account_id']) && $search_data['logged_user_type'] == 'ACCOUNTMANAGER') {
            $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager='" . $search_data['logged_user_account_id'] . "'";

            /////////////
            $query = $this->db->query($sub_sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $row = $query->row();
            $account_id_str = $row->account_ids;
            /////////////
            $str .= " AND account_id IN(" . $account_id_str . ")";
        }
        if (isset($search_data['logged_user_type']) && isset($search_data['logged_user_account_id']) && $search_data['logged_user_type'] == 'SALESMANAGER') {
            $sub_sub_sql = "SELECT user_access_id_name FROM " . $this->db->dbprefix('user_access') . " WHERE user_type='ACCOUNTMANAGER' AND superagent='" . $search_data['logged_user_account_id'] . "'";

            $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM " . $this->db->dbprefix('user_access') . " WHERE account_manager IN(" . $sub_sub_sql . ")";

            $query = $this->db->query($sub_sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $row = $query->row();
            $account_id_str = $row->account_ids;

            $str .= " AND account_id IN(" . $account_id_str . ")";
        }
        ////


        if ($search_data['carrier_id_name'] != '')
            $str .= " and carrier_id_name like '%" . $search_data['carrier_id_name'] . "%'";
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
            $group_by .= " carrier_id_name ,";
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
        //echo $query;
        $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($query);

        $return['total'] = $result->num_rows();
        $return['result'] = $result->result_array();


        return $return;
    }

    private function api_analytics_carrier($search_data) {
        $DB1 = $this->load->database('cdrdb', true);

        $range = explode(' - ', $search_data['timerange']);
        $range_from = explode(' ', $range[0]);
        $range_to = explode(' ', $range[1]);

        $start_dt = $range_from[0];
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
            $str .= " ,carrier_id_name ";
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
        if ($search_data['group_by_carrier'] == 'Y' || $search_data['carrier_id_name'] != '') {
            $str .= " ,round(sum(carrier_cost)*1.0000000000000000,2)  as cost ,carrier_currency_id as currency_id";
        }
        /// showing account's cost///	
        //////////////////////////////


        $str .= " from switch_carrier_statistics where date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) >= '" . $start_dt . " " . $start_hh . ":" . $start_mm . "'
and date_add(call_date, interval concat(calltime_h,':',calltime_m) HOUR_MINUTE) <= '" . $end_dt . " " . $end_hh . ":" . $end_mm . "'";

        if ($search_data['ip'] != '')
            $str .= " and carrier_ipaddress = '" . $search_data['ip'] . "'";
        if ($search_data['carrier_id_name'] != '')
            $str .= " and carrier_id_name like '%" . $search_data['carrier_id_name'] . "%'";
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
            $group_by .= " carrier_id_name ,";
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
        //die($query);
        $result = $DB1->query($query);

        $return['total'] = $result->num_rows();
        $return['result'] = $result->result_array();

        //var_dump($return);
        return $return;
    }

    public function api_analysis_system() {
        $logged_user_type = get_logged_account_type();
        $logged_user_account_id = get_logged_account_id();
        $logged_user_level = get_logged_account_level();

        /* if($logged_user_type=='ACCOUNTMANAGER')
          {
          $sub_sql = "SELECT GROUP_CONCAT(\"'\",user_access_id_name,\"'\") account_ids FROM ".$this->db->dbprefix('user_access')." WHERE account_manager='".$search_data['s_account_manager']."'";

          $query = $this->db->query($sub_sql);
          if(!$query)
          {
          $error_array = $this->db->error();
          throw new Exception($error_array['message']);
          }
          $row =  $query->row();
          $account_id_str = $row->account_ids;
          /////////////
          //$str .=" AND account_id IN(".$account_id_str.")";
          } */





        $DB1 = $this->load->database('cdrdb', true);

        $str = "select 
ifnull(sum(totalcalls),0) as tot_calls,
ifnull(sum(answeredcalls),0)  as tot_answered,
ifnull(round(sum(account_duration)/60,2),0) as tot_duration,
ifnull(round((sum(answeredcalls)/sum(totalcalls))*100,2),0) as asr, 
ifnull(round((sum(account_duration)/sum(answeredcalls))/60,2),0.00) as acd, 
ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd
from switch_calls_statistics where call_date = '" . date('Y-m-d') . "'";
        $result = $DB1->query($str);
        $current_data = $result->result_array();
        $return['incoming_calls'] = $current_data[0];

        $str = "select calltime_h, round(sum(account_duration)/60,2) as hour_duration from switch_calls_statistics where call_date = '" . date('Y-m-d') . "' group by calltime_h";
        $result = $DB1->query($str);
        $return['incoming_duration'] = $result->result_array();

        $str = "select 
ifnull(sum(totalcalls),0) as tot_calls,
ifnull(sum(answeredcalls),0)  as tot_answered,
ifnull(round(sum(carrier_duration)/60,2),0) as tot_duration,
ifnull(round((sum(answeredcalls)/sum(totalcalls))*100,2),0) as asr, 
ifnull(round((sum(carrier_duration)/sum(answeredcalls))/60,2),0.00) as acd, 
ifnull(round(sum(pdd)/sum(totalcalls),2),0.00) as pdd
from switch_carrier_statistics where call_date = '" . date('Y-m-d') . "'";
        $result = $DB1->query($str);
        $current_data = $result->result_array();
        $return['outgoing_calls'] = $current_data[0];

        $str = "select calltime_h, round(sum(carrier_duration)/60,2) as hour_duration from switch_carrier_statistics where call_date = '" . date('Y-m-d') . "' group by calltime_h";
        $result = $DB1->query($str);
        $return['outgoing_duration'] = $result->result_array();

        $str = "select carrier_carrier_id_name as name, carrier_gateway_ipaddress as ip,count(*) as total_calls ,
sum(if(callstatus = 'answer',1,0)) as 'answer',
sum(if(callstatus = 'ring',1,0)) as 'ringing',
sum(if(callstatus = 'progress',1,0)) as 'progress'
from switch_livecalls where 1 
group by carrier_carrier_id_name, carrier_gateway_ipaddress order by carrier_carrier_id_name asc";

        $result = $DB1->query($str);
        $return['gateway_calls'] = $result->result_array();

        $str = "select user_account_id as name, 'enduser' as type ,user_ipaddress as ip,count(*) as total_calls ,
sum(if(callstatus = 'answer',1,0)) as 'answer',
sum(if(callstatus = 'ring',1,0)) as 'ringing',
sum(if(callstatus = 'progress',1,0)) as 'progress'
from switch_livecalls where reseller1_account_id is NULL 
group by user_account_id order by total_calls desc ";

        $result = $DB1->query($str);
        $customer_calls = $result->result_array();

        $str = "select reseller1_account_id as name, 'reseller' as type ,user_ipaddress as ip,count(*) as total_calls ,
sum(if(callstatus = 'answer',1,0)) as 'answer',
sum(if(callstatus = 'ring',1,0)) as 'ringing',
sum(if(callstatus = 'progress',1,0)) as 'progress'
from switch_livecalls where reseller1_account_id is not NULL 
group by reseller1_account_id order by total_calls desc ";

        $result = $DB1->query($str);
        $reseller_calls = $result->result_array();

        $return['customer_calls'] = array_merge($customer_calls, $reseller_calls);


        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

    public function analytics_customer() {
        $data['page_name'] = "report_analytics";

        //check page action permission
        if (!check_account_permission('reports', 'analytics_customer'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	

        $this->load->model('carrier_mod');


        $response = $this->carrier_mod->get_data('', 0, '', array(), array());
        $data['carrier_data'] = $response['result'];
        $data['currency_data'] = $this->utils_model->get_currencies();
        ///////////////// Searching ////////////////////

        $search_data = array();
        if (isset($_POST['OkFilter'])) {
            $_SESSION['search_data'] = array(
                's_time' => $_POST['frmtime'],
                's_carrier' => $_POST['frmcarrier'],
                's_dest' => $_POST['frmdest'],
                's_prefix' => $_POST['frmprefix'],
                's_ctype' => $_POST['frmctype'],
                's_code' => $_POST['frmcode'],
                's_sip' => $_POST['frmsipcode'],
                's_q850' => $_POST['frmq850code'],
                's_g_user' => (isset($_POST['g_user']) ? 'Y' : 'N'),
                's_g_carrier' => (isset($_POST['g_carrier']) ? 'Y' : 'N'),
                's_g_date' => (isset($_POST['g_date']) ? 'Y' : 'N'),
                's_g_hour' => (isset($_POST['g_hour']) ? 'Y' : 'N'),
                's_g_minute' => (isset($_POST['g_minute']) ? 'Y' : 'N'),
                's_g_prefix' => (isset($_POST['g_prefix']) ? 'Y' : 'N'),
                's_g_dest' => (isset($_POST['g_dest']) ? 'Y' : 'N'),
                's_g_sip' => (isset($_POST['g_sip']) ? 'Y' : 'N'),
                's_g_q850' => (isset($_POST['g_q850']) ? 'Y' : 'N')
            );
            $search_data = array('timerange' => $_SESSION['search_data']['s_time'],
                'carrier_id_name' => $_SESSION['search_data']['s_carrier'],
                'account_id' => $_SESSION['search_data']['s_code'],
                'account_type' => $_SESSION['search_data']['s_ctype'],
                'prefix' => $_SESSION['search_data']['s_prefix'],
                'destination' => $_SESSION['search_data']['s_dest'],
                'sip' => $_SESSION['search_data']['s_sip'],
                'q850' => $_SESSION['search_data']['s_q850'],
                'group_by_carrier' => $_SESSION['search_data']['s_g_carrier'],
                'group_by_user' => $_SESSION['search_data']['s_g_user'],
                'group_by_hour' => $_SESSION['search_data']['s_g_hour'],
                'group_by_minute' => $_SESSION['search_data']['s_g_minute'],
                'group_by_date' => $_SESSION['search_data']['s_g_date'],
                'group_by_prefix' => $_SESSION['search_data']['s_g_prefix'],
                'group_by_destination' => $_SESSION['search_data']['s_g_dest'],
                'group_by_sip' => $_SESSION['search_data']['s_g_sip'],
                'group_by_q850' => $_SESSION['search_data']['s_g_q850'],
                'logged_user_type' => get_logged_account_type(),
                'logged_user_account_id' => get_logged_account_id(),
                'logged_user_level' => get_logged_account_level(),
            );

            $response = $this->api_analytics($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
        } else
            $_SESSION['search_data'] = array('s_time' => '',
                's_carrier' => '',
                's_dest' => '',
                's_prefix' => '',
                's_code' => '',
                's_g_user' => '',
                's_g_carrier' => '',
                's_g_date' => '',
                's_g_hour' => '',
                's_g_minute' => '',
                's_g_prefix' => '',
                's_g_dest' => '',
                's_g_sip' => '',
                's_g_q850' => ''
            );

        ///////////////// Searching ////////////////////		

        $this->load->view('basic/header', $data);
        $this->load->view('reports/analytics_customer', $data);
        $this->load->view('basic/footer', $data);
    }

    public function analytics_carrier() {
        $data['page_name'] = "report_analytics_carrier";

        //check page action permission
        if (!check_account_permission('reports', 'analytics_carrier'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	

        $this->load->model('carrier_mod');


        $response = $this->carrier_mod->get_data('', 0, '', array(), array());
        $data['carrier_data'] = $response['result'];
        $data['currency_data'] = $this->utils_model->get_currencies();

        ///////////////// Searching ////////////////////

        $search_data = array();
        if (isset($_POST['OkFilter'])) {
            $_SESSION['search_data'] = array(
                's_time' => $_POST['frmtime'],
                's_carrier' => $_POST['frmcarrier'],
                's_dest' => $_POST['frmdest'],
                's_prefix' => $_POST['frmprefix'],
                's_code' => $_POST['frmcode'],
                's_sip' => $_POST['frmsipcode'],
                's_q850' => $_POST['frmq850code'],
                's_g_ip' => (isset($_POST['g_ip']) ? 'Y' : 'N'),
                's_g_carrier' => (isset($_POST['g_carrier']) ? 'Y' : 'N'),
                's_g_date' => (isset($_POST['g_date']) ? 'Y' : 'N'),
                's_g_hour' => (isset($_POST['g_hour']) ? 'Y' : 'N'),
                's_g_minute' => (isset($_POST['g_minute']) ? 'Y' : 'N'),
                's_g_prefix' => (isset($_POST['g_prefix']) ? 'Y' : 'N'),
                's_g_dest' => (isset($_POST['g_dest']) ? 'Y' : 'N'),
                's_g_sip' => (isset($_POST['g_sip']) ? 'Y' : 'N'),
                's_g_q850' => (isset($_POST['g_q850']) ? 'Y' : 'N')
            );
            $search_data = array('timerange' => $_SESSION['search_data']['s_time'],
                'carrier_id_name' => $_SESSION['search_data']['s_carrier'],
                'ip' => $_SESSION['search_data']['s_code'],
                'prefix' => $_SESSION['search_data']['s_prefix'],
                'destination' => $_SESSION['search_data']['s_dest'],
                'sip' => $_SESSION['search_data']['s_sip'],
                'q850' => $_SESSION['search_data']['s_q850'],
                'group_by_carrier' => $_SESSION['search_data']['s_g_carrier'],
                'group_by_ip' => $_SESSION['search_data']['s_g_ip'],
                'group_by_hour' => $_SESSION['search_data']['s_g_hour'],
                'group_by_minute' => $_SESSION['search_data']['s_g_minute'],
                'group_by_date' => $_SESSION['search_data']['s_g_date'],
                'group_by_prefix' => $_SESSION['search_data']['s_g_prefix'],
                'group_by_destination' => $_SESSION['search_data']['s_g_dest'],
                'group_by_sip' => $_SESSION['search_data']['s_g_sip'],
                'group_by_q850' => $_SESSION['search_data']['s_g_q850']
            );

            $response = $this->api_analytics_carrier($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
        } else
            $_SESSION['search_data'] = array('s_time' => '',
                's_carrier' => '',
                's_dest' => '',
                's_prefix' => '',
                's_code' => '',
                's_g_user' => '',
                's_g_carrier' => '',
                's_g_date' => '',
                's_g_hour' => '',
                's_g_minute' => '',
                's_g_prefix' => '',
                's_g_dest' => '',
                's_g_sip' => '',
                's_g_q850' => ''
            );

        ///////////////// Searching ////////////////////		

        $this->load->view('basic/header', $data);
        $this->load->view('reports/analytics_carrier', $data);
        $this->load->view('basic/footer', $data);
    }

    public function analytics_system() {
        $data['page_name'] = "report_analytics_system";
        if (!check_account_permission('reports', 'analytics_system'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////			

        $this->load->view('basic/header', $data);
        $this->load->view('reports/analytics_system', $data);
        $this->load->view('basic/footer', $data);
    }

    public function calls_connected_in($account_id_temp = '', $format = '') {
        $arg1 = $account_id_temp;
        $data['page_name'] = "report_connected_in";
        $this->load->model('report_mod');
        //check page action permission
        //if(!check_account_permission('report','analytics_carrier')) show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	
        $logged_user_type = get_logged_account_type();
        $get_logged_account_level = get_logged_account_level();
        $logged_account_id = get_logged_account_id();
        ///////////////// Searching ////////////////////
        $is_report_searched = false;
        $is_file_downloaded = false;
        $search_data = array();
        if (isset($_POST['OkFilter'])) {
            $_SESSION['search_cdr_in_data'] = array(
                's_cdr_user_type' => $_POST['user_type'],
                's_cdr_user_account' => $_POST['user_account'],
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'],
                's_cdr_user_cli' => $_POST['user_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'],
                's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'],
                's_cdr_user_ip' => $_POST['user_ip'],
                's_cdr_call_duration' => $_POST['call_duration'],
                's_time_range' => $_POST['time_range'],
                's_no_of_records' => $_POST['no_of_rows'] //1 no_of_records	
            );
        } elseif (isset($_POST['search_action'])) {//coming from reset
            $_SESSION['search_cdr_in_data'] = array('s_cdr_user_type' => '',
                's_cdr_user_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_user_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_user_ip' => '',
                's_cdr_call_duration' => '',
                's_time_range' => '',
                's_no_of_records' => RECORDS_PER_PAGE//2 no_of_records
            );
        } elseif ($arg1 != 'export' && !isset($_SESSION['search_cdr_in_data']['s_time_range'])) {
            //default date is todays date
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION['search_cdr_in_data'] = array('s_cdr_user_type' => '',
                's_cdr_user_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_user_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_user_ip' => '',
                's_cdr_call_duration' => '',
                's_time_range' => $time_range,
                's_no_of_records' => RECORDS_PER_PAGE//3 no_of_records
            );
        }

        $search_data = array(
            's_cdr_user_type' => $_SESSION['search_cdr_in_data']['s_cdr_user_type'],
            's_cdr_user_account' => $_SESSION['search_cdr_in_data']['s_cdr_user_account'],
            's_cdr_dialed_no' => $_SESSION['search_cdr_in_data']['s_cdr_dialed_no'],
            's_cdr_carrier_dst_no' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_dst_no'],
            's_cdr_user_cli' => $_SESSION['search_cdr_in_data']['s_cdr_user_cli'],
            's_cdr_carrier_cli' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_cli'],
            's_cdr_carrier' => $_SESSION['search_cdr_in_data']['s_cdr_carrier'],
            's_cdr_carrier_ip' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_ip'],
            's_cdr_user_ip' => $_SESSION['search_cdr_in_data']['s_cdr_user_ip'],
            's_cdr_call_duration' => $_SESSION['search_cdr_in_data']['s_cdr_call_duration'],
            's_time_range' => $_SESSION['search_cdr_in_data']['s_time_range']
        );
        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['s_account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER')))
            $search_data['s_superagent'] = $logged_account_id;
        elseif (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;

        ///////////////// Searching ////////////////////

        $all_field_array = array(
            'Account' => 'Account'
            , 'SRC-DST' => 'SRC-DST'
            , 'SRC-CLI' => 'SRC-CLI'
            , 'Start Time' => 'Start Time'
            , 'Duration' => 'Duration'
            , 'C-Duration' => 'C-Duration'
            , 'hangupby' => 'Hangup By'
            , 'SRC-IP' => 'SRC-IP'
            , 'Cost' => 'Cost'
            , 'Carrier' => 'Carrier'
            , 'Q850CODE' => 'Q850CODE'
            , 'SIPCODE' => 'SIPCODE'
            , 'User-Tariff' => 'User-Tariff'
            , 'Prefix' => 'Prefix'
            , 'Destination' => 'Destination'
            , 'Routing' => 'Routing'
            , 'C-Tariff' => 'C-Tariff'
            , 'C-Prefix' => 'C-Prefix'
            , 'C-Destination' => 'C-Destination'
            , 'C-Cost' => 'C-Cost'
            , 'Org-Duration' => 'Org-Duration'
            , 'C-IP' => 'C-IP'
            , 'USER-CLI' => 'USER-CLI'
            , 'User-DST' => 'User-DST'
            , 'C-CLI' => 'C-CLI'
            , 'C-DST' => 'C-DST'
            , 'R1-Account' => 'R1-Account'
            , 'R1-Tariff' => 'R1-Tariff'
            , 'R1-Duration' => 'R1-Duration'
            , 'R1-Cost' => 'R1-Cost'
            , 'R2-Account' => 'R2-Account'
            , 'R2-Tariff' => 'R2-Tariff'
            , 'R2-Duration' => 'R2-Duration'
            , 'R2-Cost' => 'R2-Cost'
            , 'R3-Account' => 'R3-Account'
            , 'R3-Tariff' => 'R3-Tariff'
            , 'R3-Duration' => 'R3-Duration'
            , 'R3-Cost' => 'R3-Cost'
        );

        if (check_logged_account_type('RESELLER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-Duration']);
            unset($all_field_array['C-Cost']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);
            unset($all_field_array['Org-Duration']);

            if ($get_logged_account_level == 1) {
                unset($all_field_array['R3-Account']);
                unset($all_field_array['R3-Tariff']);
                unset($all_field_array['R3-Duration']);
                unset($all_field_array['R3-Cost']);
            } elseif ($get_logged_account_level == 2) {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Duration']);
                unset($all_field_array['R1-Cost']);
            } else {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Duration']);
                unset($all_field_array['R1-Cost']);
                unset($all_field_array['R2-Account']);
                unset($all_field_array['R2-Tariff']);
                unset($all_field_array['R2-Duration']);
                unset($all_field_array['R2-Cost']);
            }
        } elseif (check_logged_account_type('CUSTOMER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-Duration']);
            unset($all_field_array['C-Cost']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);
            unset($all_field_array['Org-Duration']);
            unset($all_field_array['R1-Account']);
            unset($all_field_array['R1-Tariff']);
            unset($all_field_array['R1-Duration']);
            unset($all_field_array['R1-Cost']);
            unset($all_field_array['R2-Account']);
            unset($all_field_array['R2-Tariff']);
            unset($all_field_array['R2-Duration']);
            unset($all_field_array['R2-Cost']);
            unset($all_field_array['R3-Account']);
            unset($all_field_array['R3-Tariff']);
            unset($all_field_array['R3-Duration']);
            unset($all_field_array['R3-Cost']);
        }

        //////// add export  ////////////////	
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $format = param_decrypt($format);

            $per_page = 50000;
            $segment = 0;

            $response = $this->report_mod->api_analytics_cdr_in($search_data, $per_page, $segment);
            $listing_data = $response['result'];
            $listing_count = $response['total'];

            $export_data = array();
            if ($listing_count > 0) {
                foreach ($listing_data as $listing_row) {
                    $export_data_temp = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {
                        $export_data_temp[] = $listing_row[$field_name];
                    }
                    $export_data[] = $export_data_temp;
                }
            }


            //prepare search data
            $search_array = array();
            if ($_SESSION['search_cdr_in_data']['s_cdr_user_type'] != '') {
                if ($_SESSION['search_cdr_in_data']['s_cdr_user_type'] == 'U')
                    $search_array['User Type'] = 'User';
                elseif ($_SESSION['search_cdr_in_data']['s_cdr_user_type'] == 'R1')
                    $search_array['User Type'] = 'Reseller 1';
                elseif ($_SESSION['search_cdr_in_data']['s_cdr_user_type'] == 'R2')
                    $search_array['User Type'] = 'Reseller 2';
                elseif ($_SESSION['search_cdr_in_data']['s_cdr_user_type'] == 'R3')
                    $search_array['User Type'] = 'Reseller 3';
            }
            if ($_SESSION['search_cdr_in_data']['s_cdr_user_account'] != '')
                $search_array['User Account'] = $_SESSION['search_cdr_in_data']['s_cdr_user_account'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_dialed_no'] != '')
                $search_array['Dialed No'] = $_SESSION['search_cdr_in_data']['s_cdr_dialed_no'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_carrier_dst_no'] != '')
                $search_array['Carrier DST No'] = $_SESSION['search_cdr_in_data']['s_cdr_carrier_dst_no'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_user_cli'] != '')
                $search_array['User Cli'] = $_SESSION['search_cdr_in_data']['s_cdr_user_cli'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_carrier_cli'] != '')
                $search_array['Carrier Cli'] = $_SESSION['search_cdr_in_data']['s_cdr_carrier_cli'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_carrier'] != '')
                $search_array['Carrier'] = $_SESSION['search_cdr_in_data']['s_cdr_carrier'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_carrier_ip'] != '')
                $search_array['Carrier IP'] = $_SESSION['search_cdr_in_data']['s_cdr_carrier_ip'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_user_ip'] != '')
                $search_array['User IP'] = $_SESSION['search_cdr_in_data']['s_cdr_user_ip'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_call_duration'] != '')
                $search_array['Call Duration'] = $_SESSION['search_cdr_in_data']['s_cdr_call_duration'];
            if ($_SESSION['search_cdr_in_data']['s_time_range'] != '')
                $search_array['Time Range'] = $_SESSION['search_cdr_in_data']['s_time_range'];

            // column titles
            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }

            $file_name = 'incoming_connected_calls';

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);

            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        //////// end export  ////////////////	


        if ($is_file_downloaded === false) {
            /*             * **** pagination code start here ********* */
            $pagination_uri_segment = 3;

            //4 no_of_records

            if (isset($_SESSION['search_cdr_in_data']['s_no_of_records']) && $_SESSION['search_cdr_in_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_cdr_in_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;


            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }

            //echo '<pre>';print_r($search_data);echo '</pre>';
            $response = $this->report_mod->api_analytics_cdr_in($search_data, $per_page, $segment);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $data['total_records'] = $response['all_total'];

            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['all_total'], 'reports/calls_connected_in', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();

            $data['is_report_searched'] = $is_report_searched;
            $data['logged_user_type'] = $logged_user_type;
            $data['get_logged_account_level'] = $get_logged_account_level;
            $data['all_field_array'] = $all_field_array;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/calls_connected_in', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    /////////incoming
    public function calls_connected_in_old() {
        $data['page_name'] = "report_connected_in";
        $this->load->model('report_mod');
        //check page action permission
        //if(!check_account_permission('report','analytics_carrier')) show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	
        $logged_user_type = get_logged_account_type();
        $get_logged_account_level = get_logged_account_level();
        ///////////////// Searching ////////////////////
        $is_report_searched = false;
        $search_data = array();
        //echo '<pre>';print_r($_POST);	echo '</pre>';	
        if (isset($_POST['OkFilter'])) {
            $_SESSION['search_cdr_in_data'] = array(
                's_cdr_user_type' => $_POST['user_type'],
                's_cdr_user_account' => $_POST['user_account'],
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'],
                's_cdr_user_cli' => $_POST['user_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'],
                's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'],
                's_cdr_user_ip' => $_POST['user_ip'],
                's_cdr_call_duration' => $_POST['call_duration'],
                's_time_range' => $_POST['time_range']
            );

            $search_data = array(
                's_cdr_user_type' => $_SESSION['search_cdr_in_data']['s_cdr_user_type'],
                's_cdr_user_account' => $_SESSION['search_cdr_in_data']['s_cdr_user_account'],
                's_cdr_dialed_no' => $_SESSION['search_cdr_in_data']['s_cdr_dialed_no'],
                's_cdr_carrier_dst_no' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_dst_no'],
                's_cdr_user_cli' => $_SESSION['search_cdr_in_data']['s_cdr_user_cli'],
                's_cdr_carrier_cli' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_cli'],
                's_cdr_carrier' => $_SESSION['search_cdr_in_data']['s_cdr_carrier'],
                's_cdr_carrier_ip' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_ip'],
                's_cdr_user_ip' => $_SESSION['search_cdr_in_data']['s_cdr_user_ip'],
                's_cdr_call_duration' => $_SESSION['search_cdr_in_data']['s_cdr_call_duration'],
                's_time_range' => $_SESSION['search_cdr_in_data']['s_time_range']
            );
            //echo '<pre>';print_r($search_data);	echo '</pre>';				
            $is_report_searched = true;
            $response = $this->report_mod->api_analytics_cdr_in($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
        } elseif (isset($_POST['search_action'])) {//coming from reset
            $_SESSION['search_cdr_in_data'] = array('s_cdr_user_type' => '',
                's_cdr_user_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_user_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_user_ip' => '',
                's_cdr_call_duration' => '',
                's_time_range' => ''
            );
        } elseif (!isset($_SESSION['search_cdr_in_data'])) {//default data for view seach
            $_SESSION['search_cdr_in_data'] = array('s_cdr_user_type' => '',
                's_cdr_user_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_user_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_user_ip' => '',
                's_cdr_call_duration' => '',
                's_time_range' => ''
            );
        }
        ///////////////// Searching ////////////////////

        $all_field_array = array(
            'Account' => 'Account'
            , 'Start Time' => 'Start Time'
            , 'Q850CODE' => 'Q850CODE'
            , 'SIPCODE' => 'SIPCODE'
            , 'SRC-IP' => 'SRC-IP'
            , 'SRC-CLI' => 'SRC-CLI'
            , 'SRC-DST' => 'SRC-DST'
            , 'User-Tariff' => 'User-Tariff'
            , 'Prefix' => 'Prefix'
            , 'Destination' => 'Destination'
            , 'Duration' => 'Duration'
            , 'Cost' => 'Cost'
            , 'Routing' => 'Routing'
            , 'Carrier' => 'Carrier'
            , 'C-Tariff' => 'C-Tariff'
            , 'C-Prefix' => 'C-Prefix'
            , 'C-Destination' => 'C-Destination'
            , 'C-Duration' => 'C-Duration'
            , 'C-Cost' => 'C-Cost'
            , 'Org-Duration' => 'Org-Duration'
            , 'C-IP' => 'C-IP'
            , 'USER-CLI' => 'USER-CLI'
            , 'User-DST' => 'User-DST'
            , 'C-CLI' => 'C-CLI'
            , 'C-DST' => 'C-DST'
            , 'R1-Account' => 'R1-Account'
            , 'R1-Tariff' => 'R1-Tariff'
            , 'R1-Duration' => 'R1-Duration'
            , 'R1-Cost' => 'R1-Cost'
            , 'R2-Account' => 'R2-Account'
            , 'R2-Tariff' => 'R2-Tariff'
            , 'R2-Duration' => 'R2-Duration'
            , 'R2-Cost' => 'R2-Cost'
            , 'R3-Account' => 'R3-Account'
            , 'R3-Tariff' => 'R3-Tariff'
            , 'R3-Duration' => 'R3-Duration'
            , 'R3-Cost' => 'R3-Cost'
            , 'hangupby' => 'Hangup By'
        );

        if (check_logged_account_type('RESELLER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-Duration']);
            unset($all_field_array['C-Cost']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);
            unset($all_field_array['Org-Duration']);

            if ($get_logged_account_level == 1) {
                unset($all_field_array['R3-Account']);
                unset($all_field_array['R3-Tariff']);
                unset($all_field_array['R3-Duration']);
                unset($all_field_array['R3-Cost']);
            } elseif ($get_logged_account_level == 2) {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Duration']);
                unset($all_field_array['R1-Cost']);
            } else {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Duration']);
                unset($all_field_array['R1-Cost']);
                unset($all_field_array['R2-Account']);
                unset($all_field_array['R2-Tariff']);
                unset($all_field_array['R2-Duration']);
                unset($all_field_array['R2-Cost']);
            }
        } elseif (check_logged_account_type('CUSTOMER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-Duration']);
            unset($all_field_array['C-Cost']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);
            unset($all_field_array['Org-Duration']);
            unset($all_field_array['R1-Account']);
            unset($all_field_array['R1-Tariff']);
            unset($all_field_array['R1-Duration']);
            unset($all_field_array['R1-Cost']);
            unset($all_field_array['R2-Account']);
            unset($all_field_array['R2-Tariff']);
            unset($all_field_array['R2-Duration']);
            unset($all_field_array['R2-Cost']);
            unset($all_field_array['R3-Account']);
            unset($all_field_array['R3-Tariff']);
            unset($all_field_array['R3-Duration']);
            unset($all_field_array['R3-Cost']);
        } else {
            
        }


        $data['is_report_searched'] = $is_report_searched;
        $data['logged_user_type'] = $logged_user_type;
        $data['get_logged_account_level'] = $get_logged_account_level;
        $data['all_field_array'] = $all_field_array;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/calls_connected_in', $data);
        $this->load->view('basic/footer', $data);
    }

    ////////incoming
    public function calls_failed_in($arg1 = '', $format = '') {
        $data['page_name'] = "report_failed_in";
        $this->load->model('report_mod');
        //check page action permission
        //if(!check_account_permission('report','analytics_carrier')) show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	
        $logged_user_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();
        ///////////////// Searching ////////////////////		
        $is_file_downloaded == false;
        $search_data = array();

        if (isset($_POST['search_action'])) {
            $_SESSION['search_failed_in_data'] = array(
                's_cdr_user_type' => $_POST['user_type'],
                's_cdr_user_account' => $_POST['user_account'],
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'],
                's_cdr_user_cli' => $_POST['user_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'],
                's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'],
                's_cdr_user_ip' => $_POST['user_ip'],
                's_cdr_sip_code' => $_POST['sip_code'],
                's_cdr_Q850CODE' => $_POST['Q850CODE'],
                's_time_range' => $_POST['time_range'],
                's_no_of_records' => $_POST['no_of_rows']
            );
        } else {
            $_SESSION['search_failed_in_data']['s_cdr_user_type'] = isset($_SESSION['search_failed_in_data']['s_cdr_user_type']) ? $_SESSION['search_failed_in_data']['s_cdr_user_type'] : '';

            $_SESSION['search_failed_in_data']['s_cdr_user_account'] = isset($_SESSION['search_failed_in_data']['s_cdr_user_account']) ? $_SESSION['search_failed_in_data']['s_cdr_user_account'] : '';

            $_SESSION['search_failed_in_data']['s_cdr_dialed_no'] = isset($_SESSION['search_failed_in_data']['s_cdr_dialed_no']) ? $_SESSION['search_failed_in_data']['s_cdr_dialed_no'] : '';

            $_SESSION['search_failed_in_data']['s_cdr_carrier_dst_no'] = isset($_SESSION['search_failed_in_data']['s_cdr_carrier_dst_no']) ? $_SESSION['search_failed_in_data']['s_cdr_carrier_dst_no'] : '';

            $_SESSION['search_failed_in_data']['s_cdr_user_cli'] = isset($_SESSION['search_failed_in_data']['s_cdr_user_cli']) ? $_SESSION['search_failed_in_data']['s_cdr_user_cli'] : '';

            $_SESSION['search_failed_in_data']['s_cdr_carrier_cli'] = isset($_SESSION['search_failed_in_data']['s_cdr_carrier_cli']) ? $_SESSION['search_failed_in_data']['s_cdr_carrier_cli'] : '';

            $_SESSION['search_failed_in_data']['s_cdr_carrier'] = isset($_SESSION['search_failed_in_data']['s_cdr_carrier']) ? $_SESSION['search_failed_in_data']['s_cdr_carrier'] : '';

            $_SESSION['search_failed_in_data']['s_cdr_carrier_ip'] = isset($_SESSION['search_failed_in_data']['s_cdr_carrier_ip']) ? $_SESSION['search_failed_in_data']['s_cdr_carrier_ip'] : '';
            $_SESSION['search_failed_in_data']['s_cdr_user_ip'] = isset($_SESSION['search_failed_in_data']['s_cdr_user_ip']) ? $_SESSION['search_failed_in_data']['s_cdr_user_ip'] : '';

            $_SESSION['search_failed_in_data']['s_cdr_sip_code'] = isset($_SESSION['search_failed_in_data']['s_cdr_sip_code']) ? $_SESSION['search_failed_in_data']['s_cdr_sip_code'] : '';

            $_SESSION['search_failed_in_data']['s_cdr_Q850CODE'] = isset($_SESSION['search_failed_in_data']['s_cdr_Q850CODE']) ? $_SESSION['search_failed_in_data']['s_cdr_Q850CODE'] : '';

            $_SESSION['search_failed_in_data']['s_time_range'] = isset($_SESSION['search_failed_in_data']['s_time_range']) ? $_SESSION['search_failed_in_data']['s_time_range'] : '';
        }

        if ($_SESSION['search_failed_in_data']['s_time_range'] == '') {
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION['search_failed_in_data']['s_time_range'] = $time_range;
        }

        $_SESSION['search_failed_in_data']['s_no_of_records'] = isset($_SESSION['search_failed_in_data']['s_no_of_records']) ? $_SESSION['search_failed_in_data']['s_no_of_records'] : RECORDS_PER_PAGE;



        $search_data = array(
            's_cdr_user_type' => $_SESSION['search_failed_in_data']['s_cdr_user_type'],
            's_cdr_user_account' => $_SESSION['search_failed_in_data']['s_cdr_user_account'],
            's_cdr_dialed_no' => $_SESSION['search_failed_in_data']['s_cdr_dialed_no'],
            's_cdr_carrier_dst_no' => $_SESSION['search_failed_in_data']['s_cdr_carrier_dst_no'],
            's_cdr_user_cli' => $_SESSION['search_failed_in_data']['s_cdr_user_cli'],
            's_cdr_carrier_cli' => $_SESSION['search_failed_in_data']['s_cdr_carrier_cli'],
            's_cdr_carrier' => $_SESSION['search_failed_in_data']['s_cdr_carrier'],
            's_cdr_carrier_ip' => $_SESSION['search_failed_in_data']['s_cdr_carrier_ip'],
            's_cdr_user_ip' => $_SESSION['search_failed_in_data']['s_cdr_user_ip'],
            's_cdr_sip_code' => $_SESSION['search_failed_in_data']['s_cdr_sip_code'],
            's_cdr_Q850CODE' => $_SESSION['search_failed_in_data']['s_cdr_Q850CODE'],
            's_time_range' => $_SESSION['search_failed_in_data']['s_time_range']
        );

        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['s_account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER')))
            $search_data['s_superagent'] = $logged_account_id;
        elseif (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;

        ///////////////// Searching ////////////////////

        $all_field_array = array(
            'Account' => 'Account'
            , 'Start Time' => 'Start Time'
            , 'Q850CODE' => 'Q850 CODE'
            , 'SIPCODE' => 'SIP CODE'
            , 'FS-Cause' => 'FS-Cause'
            , 'SRC-IP' => 'SRC-IP'
            , 'SRC-CLI' => 'SRC-CLI'
            , 'SRC-DST' => 'SRC-DST'
            , 'User-Tariff' => 'User-Tariff'
            , 'Prefix' => 'Prefix'
            , 'Destination' => 'Destination'
            , 'Routing' => 'Routing'
            , 'Carrier' => 'Carrier'
            , 'C-Tariff' => 'C-Tariff'
            , 'C-Prefix' => 'C-Prefix'
            , 'C-Destination' => 'C-Destination'
            , 'C-IP' => 'C-IP'
            , 'USER-CLI' => 'USER-CLI'
            , 'User-DST' => 'User-DST'
            , 'C-CLI' => 'C-CLI'
            , 'C-DST' => 'C-DST'
            , 'R1-Account' => 'R1-Account'
            , 'R1-Tariff' => 'R1-Tariff'
            , 'R1-Prefix' => 'R1-Prefix'
            , 'R1-DST' => 'R1-DST'
            , 'R2-Account' => 'R2-Account'
            , 'R2-Tariff' => 'R2-Tariff'
            , 'R2-Prefix' => 'R2-Prefix'
            , 'R2-DST' => 'R2-DST'
            , 'R3-Account' => 'R3-Account'
            , 'R3-Tariff' => 'R3-Tariff'
            , 'R3-Prefix' => 'R3-Prefix'
            , 'R3-DST' => 'R3-DST'
            , 'hangupby' => 'Hangup By'
        );

        if (check_logged_account_type('RESELLER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);

            if ($get_logged_account_level == 1) {
                unset($all_field_array['R3-Account']);
                unset($all_field_array['R3-Tariff']);
                unset($all_field_array['R3-Prefix']);
                unset($all_field_array['R3-DST']);
            } elseif ($get_logged_account_level == 2) {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Prefix']);
                unset($all_field_array['R1-DST']);
            } else {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Prefix']);
                unset($all_field_array['R1-DST']);

                unset($all_field_array['R2-Account']);
                unset($all_field_array['R2-Tariff']);
                unset($all_field_array['R2-Prefix']);
                unset($all_field_array['R2-DST']);
            }
        } elseif (check_logged_account_type('CUSTOMER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);
            unset($all_field_array['R1-Account']);
            unset($all_field_array['R1-Tariff']);
            unset($all_field_array['R1-Prefix']);
            unset($all_field_array['R1-DST']);
            unset($all_field_array['R2-Account']);
            unset($all_field_array['R2-Tariff']);
            unset($all_field_array['R2-Prefix']);
            unset($all_field_array['R2-DST']);
            unset($all_field_array['R3-Account']);
            unset($all_field_array['R3-Tariff']);
            unset($all_field_array['R3-Prefix']);
            unset($all_field_array['R3-DST']);
        } else {
            
        }



        //////// add export  ////////////////	
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $format = param_decrypt($format);

            $per_page = 50000;
            $segment = 0;

            $response = $this->report_mod->api_analytics_cdr_failed_in($search_data, $per_page, $segment);
            $listing_data = $response['result'];
            $listing_count = $response['total'];

            $export_data = array();
            if ($listing_count > 0) {
                foreach ($listing_data as $listing_row) {
                    $export_data_temp = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {
                        $export_data_temp[] = $listing_row[$field_name];
                    }
                    $export_data[] = $export_data_temp;
                }
            }


            //prepare search data
            $search_array = array();
            if ($_SESSION['search_failed_in_data']['s_cdr_user_type'] != '') {
                if ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'U')
                    $search_array['User Type'] = 'User';
                elseif ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'R1')
                    $search_array['User Type'] = 'Reseller 1';
                elseif ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'R2')
                    $search_array['User Type'] = 'Reseller 2';
                elseif ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'R3')
                    $search_array['User Type'] = 'Reseller 3';
            }
            if ($_SESSION['search_failed_in_data']['s_cdr_user_account'] != '')
                $search_array['User Account'] = $_SESSION['search_failed_in_data']['s_cdr_user_account'];
            if ($_SESSION['search_failed_in_data']['s_cdr_dialed_no'] != '')
                $search_array['Dialed No'] = $_SESSION['search_failed_in_data']['s_cdr_dialed_no'];
            if ($_SESSION['search_failed_in_data']['s_cdr_carrier_dst_no'] != '')
                $search_array['Carrier DST No'] = $_SESSION['search_failed_in_data']['s_cdr_carrier_dst_no'];
            if ($_SESSION['search_failed_in_data']['s_cdr_user_cli'] != '')
                $search_array['User Cli'] = $_SESSION['search_failed_in_data']['s_cdr_user_cli'];
            if ($_SESSION['search_failed_in_data']['s_cdr_carrier_cli'] != '')
                $search_array['Carrier Cli'] = $_SESSION['search_failed_in_data']['s_cdr_carrier_cli'];
            if ($_SESSION['search_failed_in_data']['s_cdr_carrier'] != '')
                $search_array['Carrier'] = $_SESSION['search_failed_in_data']['s_cdr_carrier'];
            if ($_SESSION['search_failed_in_data']['s_cdr_carrier_ip'] != '')
                $search_array['Carrier IP'] = $_SESSION['search_failed_in_data']['s_cdr_carrier_ip'];
            if ($_SESSION['search_failed_in_data']['s_cdr_user_ip'] != '')
                $search_array['User IP'] = $_SESSION['search_failed_in_data']['s_cdr_user_ip'];


            if ($_SESSION['search_failed_in_data']['s_cdr_sip_code'] != '')
                $search_array['Sip Code'] = $_SESSION['search_failed_in_data']['s_cdr_sip_code'];
            if ($_SESSION['search_failed_in_data']['s_cdr_Q850CODE'] != '')
                $search_array['Q850CODE'] = $_SESSION['search_failed_in_data']['s_cdr_Q850CODE'];


            if ($_SESSION['search_failed_in_data']['s_time_range'] != '')
                $search_array['Time Range'] = $_SESSION['search_failed_in_data']['s_time_range'];

            // column titles
            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }

            $file_name = 'incoming_failed_calls';

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);

            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        //////// end export  ////////////////	


        if ($is_file_downloaded == false) {
            /*             * **** pagination code start here ********* */
            $pagination_uri_segment = 3;

            if (isset($_SESSION['search_failed_in_data']['s_no_of_records']) && $_SESSION['search_failed_in_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_failed_in_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;


            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }


            $response = $this->report_mod->api_analytics_cdr_failed_in($search_data, $per_page, $segment);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $data['total_records'] = $response['all_total'];

            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['all_total'], 'reports/calls_failed_in', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();

            $data['is_report_searched'] = $is_report_searched;
            $data['logged_user_type'] = $logged_user_type;
            $data['get_logged_account_level'] = $get_logged_account_level;
            $data['all_field_array'] = $all_field_array;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/calls_failed_in', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function cdr($account_id = '') {
        $page_name = "report_cdr";
        $data['page_name'] = $page_name;

        //	echo get_logged_account_type();die;
        if (check_logged_account_type(array('RESELLER', 'CUSTOMER')))
            $account_id = get_logged_account_id();
        elseif (check_logged_account_type(array('ACCOUNTMANAGER'))) {
            if ($account_id == '')
                show_404('403');

            $account_id = param_decrypt($account_id);
        } else
            show_404('403');

        $data['search_account_id'] = $account_id;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $this->load->view('basic/header', $data);
        $this->load->view('reports/cdr', $data);
        $this->load->view('basic/footer', $data);
    }

    function statement($account_id = '') {
        $page_name = "report_statement";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');

        /* if(!check_account_permission('enduser','view'))
          show_404('403'); */
        if (!check_logged_account_type(array('RESELLER', 'CUSTOMER')) && $account_id == '') {
            //not permitted
            show_404('403');
        }

        if ($account_id != '') {
            $account_id = param_decrypt($account_id);
        } else {
            $account_id = get_logged_account_id();
        }

        ////

        $user_result = $this->member_mod->get_account_by_key('user_access_id_name', $account_id);
        if (!$user_result) {
            show_404();
        }

        ////


        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ////////////////////////////////////////////////


        if (isset($_POST['search_action'])) {// coming from search button
            $_SESSION['search_sdr_summary_data'] = array('s_yearmonth' => $_POST['yearmonth']);
        } else {
            $_SESSION['search_sdr_summary_data']['s_yearmonth'] = isset($_SESSION['search_sdr_summary_data']['s_yearmonth']) ? $_SESSION['search_sdr_summary_data']['s_yearmonth'] : date("Ym");
        }

        $search_data = array('yearmonth' => $_SESSION['search_sdr_summary_data']['s_yearmonth']);

        $report_data = $this->report_mod->sdr_statement($account_id, $search_data);


        $data['user_dp'] = $user_result['dp'];
        $data['sdr_terms'] = $this->utils_model->get_sdr_terms();
        $data['searched_account_id'] = $account_id;
        $data['data'] = $report_data;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/sdr_statement', $data);
        $this->load->view('basic/footer', $data);
    }

    function summary() {
        $page_name = "report_summary";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');

        //if(!check_logged_account_type(array('ACCOUNTMANAGER')) )
        {
            //not permitted
            //show_404('403');
        }

        $account_id = get_logged_account_id();


        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ////////////////////////////////////////////////


        if (isset($_POST['search_action'])) {// coming from search button							
            $_SESSION['search_sdr_summ_data'] = array('s_yearmonth' => $_POST['yearmonth']);
            $_SESSION['search_sdr_summ_data']['s_account_id'] = $_POST['account_id'];
        } else {
            $_SESSION['search_sdr_summ_data']['s_yearmonth'] = isset($_SESSION['search_sdr_summ_data']['s_yearmonth']) ? $_SESSION['search_sdr_summ_data']['s_yearmonth'] : date("Y-m");

            $_SESSION['search_sdr_summ_data']['s_account_id'] = isset($_SESSION['search_sdr_summ_data']['s_account_id']) ? $_SESSION['search_sdr_summ_data']['s_account_id'] : '';
        }
        $search_data = array('account_id' => $_SESSION['search_sdr_summ_data']['s_account_id'], 'yearmonth' => $_SESSION['search_sdr_summ_data']['s_yearmonth']);
        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['account_manager'] = $account_id;
        elseif (check_logged_account_type(array('RESELLER')))
            $search_data['parent_account_id'] = $account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $search_data['account_id'] = $account_id;
        elseif (check_logged_account_type(array('ADMIN'))) {
            
        } else {
            show_404('403');
        }


        $report_data = $this->report_mod->sdr_summary($search_data);

        $data['data'] = $report_data;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/sdr_summary', $data);
        $this->load->view('basic/footer', $data);
    }

    function call_report($account_id_temp = '') {
        $page_name = "report_call";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');

        //if(!check_logged_account_type(array('ACCOUNTMANAGER')) )
        {
            //not permitted
            //show_404('403');
        }

        $logged_account_id = get_logged_account_id();

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ////////////////////////////////////////////////


        if (isset($_POST['search_action'])) {// coming from search button							
            $_SESSION['search_call_data'] = array('s_yearmonth' => $_POST['yearmonth']);
            $_SESSION['search_call_data']['s_account_id'] = $_POST['account_id'];
        } else {
            $_SESSION['search_call_data']['s_yearmonth'] = isset($_SESSION['search_call_data']['s_yearmonth']) ? $_SESSION['search_call_data']['s_yearmonth'] : date("Y-m");

            $_SESSION['search_call_data']['s_account_id'] = isset($_SESSION['search_call_data']['s_account_id']) ? $_SESSION['search_call_data']['s_account_id'] : '';

            if ($account_id_temp != '')
                $_SESSION['search_call_data']['s_account_id'] = param_decrypt($account_id_temp);
        }
        $search_data = array('user_account_id' => $_SESSION['search_call_data']['s_account_id'], 'action_month' => $_SESSION['search_call_data']['s_yearmonth']);
        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('RESELLER')))//,'CUSTOMER'
            $search_data['parent_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $search_data['user_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('ADMIN'))) {
            
        } else {
            show_404('403');
        }

        $report_data = $this->report_mod->call_statistics($search_data);

        $data['call_statistics_data'] = $report_data;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/call_report', $data);
        $this->load->view('basic/footer', $data);
    }

    public function calls_failed($arg1 = '', $format = '') {
        $this->load->model('report_mod');
        $data['page_name'] = "report_failed";
        //check page action permission
        //if(!check_account_permission('report','analytics_carrier')) show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	
        $logged_user_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();


        ///////////////// Searching ////////////////////

        $search_data = array();
        //if(isset($_POST['OkFilter']))
        if (isset($_POST['search_action'])) {

            $_SESSION['search_failed_data'] = array(
                's_cdr_user_type' => $_POST['user_type'],
                's_cdr_user_account' => $_POST['user_account'],
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'],
                's_cdr_user_cli' => $_POST['user_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'],
                's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'],
                's_cdr_user_ip' => $_POST['user_ip'],
                's_cdr_sip_code' => $_POST['sip_code'],
                's_cdr_Q850CODE' => $_POST['Q850CODE'],
                's_cdr_fserrorcode' => $_POST['fs_errorcode'],
                's_time_range' => $_POST['time_range'],
                's_no_of_records' => $_POST['no_of_rows']
            );
        } elseif ($arg1 == 'export') {
            
        } elseif (!isset($_SESSION['search_failed_data'])) {
            //default date is todays date
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION['search_failed_data'] = array('s_cdr_user_type' => '',
                's_cdr_user_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_user_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_user_ip' => '',
                's_cdr_sip_code' => '',
                's_cdr_Q850CODE' => '',
                's_cdr_fserrorcode' => '',
                's_time_range' => $time_range,
                's_no_of_records' => RECORDS_PER_PAGE
            );
        }

        $search_data = array(
            's_cdr_user_type' => $_SESSION['search_failed_data']['s_cdr_user_type'],
            's_cdr_user_account' => $_SESSION['search_failed_data']['s_cdr_user_account'],
            's_cdr_dialed_no' => $_SESSION['search_failed_data']['s_cdr_dialed_no'],
            's_cdr_carrier_dst_no' => $_SESSION['search_failed_data']['s_cdr_carrier_dst_no'],
            's_cdr_user_cli' => $_SESSION['search_failed_data']['s_cdr_user_cli'],
            's_cdr_carrier_cli' => $_SESSION['search_failed_data']['s_cdr_carrier_cli'],
            's_cdr_carrier' => $_SESSION['search_failed_data']['s_cdr_carrier'],
            's_cdr_carrier_ip' => $_SESSION['search_failed_data']['s_cdr_carrier_ip'],
            's_cdr_user_ip' => $_SESSION['search_failed_data']['s_cdr_user_ip'],
            's_cdr_sip_code' => $_SESSION['search_failed_data']['s_cdr_sip_code'],
            's_cdr_Q850CODE' => $_SESSION['search_failed_data']['s_cdr_Q850CODE'],
            's_cdr_fserrorcode' => $_SESSION['search_failed_data']['s_cdr_fserrorcode'],
            's_time_range' => $_SESSION['search_failed_data']['s_time_range'],
        );


        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['s_account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER')))
            $search_data['s_superagent'] = $logged_account_id;
        elseif (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;
        /////////////////determine which fields to display///////////////////////////
        $all_field_array = array(
            'Account' => 'Account'
            , 'Start Time' => 'Start Time'
            , 'Q850CODE' => 'Q850 CODE'
            , 'SIPCODE' => 'SIP CODE'
            , 'FS-Cause' => 'FS-Cause'
            , 'SRC-IP' => 'SRC-IP'
            , 'SRC-CLI' => 'SRC-CLI'
            , 'SRC-DST' => 'SRC-DST'
            , 'User-Tariff' => 'User-Tariff'
            , 'Prefix' => 'Prefix'
            , 'Destination' => 'Destination'
            , 'Routing' => 'Routing'
            , 'Carrier' => 'Carrier'
            , 'C-Tariff' => 'C-Tariff'
            , 'C-Prefix' => 'C-Prefix'
            , 'C-Destination' => 'C-Destination'
            , 'C-IP' => 'C-IP'
            , 'USER-CLI' => 'USER-CLI'
            , 'User-DST' => 'User-DST'
            , 'C-CLI' => 'C-CLI'
            , 'C-DST' => 'C-DST'
            , 'R1-Account' => 'R1-Account'
            , 'R1-Tariff' => 'R1-Tariff'
            , 'R1-Prefix' => 'R1-Prefix'
            , 'R1-DST' => 'R1-DST'
            , 'R2-Account' => 'R2-Account'
            , 'R2-Tariff' => 'R2-Tariff'
            , 'R2-Prefix' => 'R2-Prefix'
            , 'R2-DST' => 'R2-DST'
            , 'R3-Account' => 'R3-Account'
            , 'R3-Tariff' => 'R3-Tariff'
            , 'R3-Prefix' => 'R3-Prefix'
            , 'R3-DST' => 'R3-DST'
            , 'hangupby' => 'Hangup By'
        );

        if (check_logged_account_type('RESELLER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);

            if ($get_logged_account_level == 1) {
                unset($all_field_array['R3-Account']);
                unset($all_field_array['R3-Tariff']);
                unset($all_field_array['R3-Prefix']);
                unset($all_field_array['R3-DST']);
            } elseif ($get_logged_account_level == 2) {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Prefix']);
                unset($all_field_array['R1-DST']);
            } else {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Prefix']);
                unset($all_field_array['R1-DST']);

                unset($all_field_array['R2-Account']);
                unset($all_field_array['R2-Tariff']);
                unset($all_field_array['R2-Prefix']);
                unset($all_field_array['R2-DST']);
            }
        } elseif (check_logged_account_type('CUSTOMER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);
            unset($all_field_array['R1-Account']);
            unset($all_field_array['R1-Tariff']);
            unset($all_field_array['R1-Prefix']);
            unset($all_field_array['R1-DST']);
            unset($all_field_array['R2-Account']);
            unset($all_field_array['R2-Tariff']);
            unset($all_field_array['R2-Prefix']);
            unset($all_field_array['R2-DST']);
            unset($all_field_array['R3-Account']);
            unset($all_field_array['R3-Tariff']);
            unset($all_field_array['R3-Prefix']);
            unset($all_field_array['R3-DST']);
        } else {
            
        }

        ///////////////// Searching ////////////////////
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $format = param_decrypt($format);

            $per_page = 50000;
            $segment = 0;

            $response = $this->report_mod->api_analytics_cdr_failed($search_data, $per_page, $segment); //???
            $listing_data = $response['result'];
            $listing_count = $response['total'];


            $search_array = array();
            if ($_SESSION['search_failed_data']['s_cdr_user_type'] != '') {
                if ($_SESSION['search_failed_data']['s_cdr_user_type'] == 'U')
                    $search_array['User Type'] = 'User';
                elseif ($_SESSION['search_failed_data']['s_cdr_user_type'] == 'R1')
                    $search_array['User Type'] = 'Reseller 1';
                elseif ($_SESSION['search_failed_data']['s_cdr_user_type'] == 'R2')
                    $search_array['User Type'] = 'Reseller 2';
                elseif ($_SESSION['search_failed_data']['s_cdr_user_type'] == 'R3')
                    $search_array['User Type'] = 'Reseller 3';
            }
            if ($_SESSION['search_failed_data']['s_cdr_dialed_no'] != '')
                $search_array['Dialed No'] = $_SESSION['search_failed_data']['s_cdr_dialed_no'];
            if ($_SESSION['search_failed_data']['s_cdr_carrier_dst_no'] != '')
                $search_array['Carrier DST No'] = $_SESSION['search_failed_data']['s_cdr_carrier_dst_no'];
            if ($_SESSION['search_failed_data']['s_cdr_user_cli'] != '')
                $search_array['User Cli'] = $_SESSION['search_failed_data']['s_cdr_user_cli'];
            if ($_SESSION['search_failed_data']['s_cdr_carrier_cli'] != '')
                $search_array['Carrier Cli'] = $_SESSION['search_failed_data']['s_cdr_carrier_cli'];
            if ($_SESSION['search_failed_data']['s_cdr_carrier'] != '')
                $search_array['Carrier'] = $_SESSION['search_failed_data']['s_cdr_carrier'];
            if ($_SESSION['search_failed_data']['s_cdr_carrier_ip'] != '')
                $search_array['Carrier IP'] = $_SESSION['search_failed_data']['s_cdr_carrier_ip'];
            if ($_SESSION['search_failed_data']['s_cdr_user_ip'] != '')
                $search_array['User IP'] = $_SESSION['search_failed_data']['s_cdr_user_ip'];
            if ($_SESSION['search_failed_data']['s_cdr_sip_code'] != '')
                $search_array['SIP Code'] = $_SESSION['search_failed_data']['s_cdr_sip_code'];
            if ($_SESSION['search_failed_data']['s_cdr_Q850CODE'] != '')
                $search_array['Q850CODE'] = $_SESSION['search_failed_data']['s_cdr_Q850CODE'];
            if ($_SESSION['search_failed_data']['s_cdr_fserrorcode'] != '')
                $search_array['FS Error-Code'] = $_SESSION['search_failed_data']['s_cdr_fserrorcode'];
            if ($_SESSION['search_failed_data']['s_time_range'] != '')
                $search_array['Time Range'] = $_SESSION['search_failed_data']['s_time_range'];



            // column titles
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }



            if (isset($listing_count) && $listing_count > 0) {
                foreach ($listing_data as $listing_row) {
                    $array_temp = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {
                        $array_temp[] = $listing_row[$field_name];
                    }
                    $export_data[] = $array_temp;
                }
            } else
                $export_data[] = array();



            $file_name = 'Failed Calls';

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);


            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        if ($is_file_downloaded === false) {

            /*             * **** pagination code start here ********* */
            $pagination_uri_segment = 3;

            if (isset($_SESSION['search_failed_data']['s_no_of_records']) && $_SESSION['search_failed_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_cdr_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;


            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }

            $response = $this->report_mod->api_analytics_cdr_failed($search_data, $per_page, $segment);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $data['total_records'] = $response['all_total'];

            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['all_total'], 'reports/calls_failed', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();
            ////////////////
            //////////fields/////////////



            $data['logged_user_type'] = $logged_user_type;
            $data['get_logged_account_level'] = $get_logged_account_level;
            $data['all_field_array'] = $all_field_array;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/calls_failed', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    //$arg1='',$format=''
    public function calls_connected($account_id_temp = '', $format = '') {
        $arg1 = $account_id_temp; //it can be account id or export
        $this->load->model('report_mod');
        $data['page_name'] = "report_connected";

        //check page action permission
        //if(!check_account_permission('report','analytics_carrier')) show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	
        $logged_user_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();
        ///////////////// Searching ////////////////////

        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_cdr_data'] = array(
                's_cdr_user_type' => $_POST['user_type'],
                's_cdr_user_account' => $_POST['user_account'],
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'],
                's_cdr_user_cli' => $_POST['user_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'],
                's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'],
                's_cdr_user_ip' => $_POST['user_ip'],
                's_cdr_call_duration' => $_POST['call_duration'],
                's_time_range' => $_POST['time_range'],
                's_no_of_records' => $_POST['no_of_rows']
            );
        } elseif ($arg1 != 'export' && !isset($_SESSION['search_cdr_data']['s_time_range'])) {
            //default date is todays date
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION['search_cdr_data'] = array('s_cdr_user_type' => '',
                's_cdr_user_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_user_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_user_ip' => '',
                's_cdr_call_duration' => '',
                's_time_range' => $time_range,
                's_no_of_records' => RECORDS_PER_PAGE
            );
        }

        if ($account_id_temp != '' && $arg1 != 'export' && !is_numeric($account_id_temp)) {
            $account_id_temp = param_decrypt($account_id_temp);
            $_SESSION['search_cdr_data']['s_cdr_user_account'] = $account_id_temp;
        }

        $search_data = array(
            's_cdr_user_type' => $_SESSION['search_cdr_data']['s_cdr_user_type'],
            's_cdr_user_account' => $_SESSION['search_cdr_data']['s_cdr_user_account'],
            's_cdr_dialed_no' => $_SESSION['search_cdr_data']['s_cdr_dialed_no'],
            's_cdr_carrier_dst_no' => $_SESSION['search_cdr_data']['s_cdr_carrier_dst_no'],
            's_cdr_user_cli' => $_SESSION['search_cdr_data']['s_cdr_user_cli'],
            's_cdr_carrier_cli' => $_SESSION['search_cdr_data']['s_cdr_carrier_cli'],
            's_cdr_carrier' => $_SESSION['search_cdr_data']['s_cdr_carrier'],
            's_cdr_carrier_ip' => $_SESSION['search_cdr_data']['s_cdr_carrier_ip'],
            's_cdr_user_ip' => $_SESSION['search_cdr_data']['s_cdr_user_ip'],
            's_cdr_call_duration' => $_SESSION['search_cdr_data']['s_cdr_call_duration'],
            's_time_range' => $_SESSION['search_cdr_data']['s_time_range']
        );
        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['s_account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER')))
            $search_data['s_superagent'] = $logged_account_id;
        elseif (check_logged_account_type(array('RESELLER', 'CUSTOMER')))
            $search_data['s_parent_account_id'] = $logged_account_id;
        ///////////////// Searching ////////////////////





        $all_field_array = array(
            'Account' => 'Account'
            , 'SRC-DST' => 'SRC-DST'
            , 'SRC-CLI' => 'SRC-CLI'
            , 'Start Time' => 'Start Time'
            , 'End Time' => 'End Time'
            , 'Duration' => 'Duration'
            , 'C-Duration' => 'C-Duration'
            , 'hangupby' => 'Hangup By'
            , 'SRC-IP' => 'Caller-IP'
            , 'Cost' => 'Cost'
            , 'Carrier' => 'Carrier'
            , 'Q850CODE' => 'Q850CODE'
            , 'SIPCODE' => 'SIPCODE'
            , 'User-Tariff' => 'User-Tariff'
            , 'Prefix' => 'Prefix'
            , 'Destination' => 'Destination'
            , 'Routing' => 'Routing'
            , 'C-Tariff' => 'C-Tariff'
            , 'C-Prefix' => 'C-Prefix'
            , 'C-Destination' => 'C-Destination'
            , 'C-Cost' => 'C-Cost'
            , 'Org-Duration' => 'Org-Duration'
            , 'C-IP' => 'C-IP'
            , 'USER-CLI' => 'USER-CLI'
            , 'User-DST' => 'User-DST'
            , 'C-CLI' => 'C-CLI'
            , 'C-DST' => 'C-DST'
            , 'R1-Account' => 'R1-Account'
            , 'R1-Tariff' => 'R1-Tariff'
            , 'R1-Duration' => 'R1-Duration'
            , 'R1-Cost' => 'R1-Cost'
            , 'R2-Account' => 'R2-Account'
            , 'R2-Tariff' => 'R2-Tariff'
            , 'R2-Duration' => 'R2-Duration'
            , 'R2-Cost' => 'R2-Cost'
            , 'R3-Account' => 'R3-Account'
            , 'R3-Tariff' => 'R3-Tariff'
            , 'R3-Duration' => 'R3-Duration'
            , 'R3-Cost' => 'R3-Cost'
        );

        if (check_logged_account_type('RESELLER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-Duration']);
            unset($all_field_array['C-Cost']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);
            unset($all_field_array['Org-Duration']);

            if ($get_logged_account_level == 1) {
                unset($all_field_array['R3-Account']);
                unset($all_field_array['R3-Tariff']);
                unset($all_field_array['R3-Duration']);
                unset($all_field_array['R3-Cost']);
            } elseif ($get_logged_account_level == 2) {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Duration']);
                unset($all_field_array['R1-Cost']);
            } else {
                unset($all_field_array['R1-Account']);
                unset($all_field_array['R1-Tariff']);
                unset($all_field_array['R1-Duration']);
                unset($all_field_array['R1-Cost']);
                unset($all_field_array['R2-Account']);
                unset($all_field_array['R2-Tariff']);
                unset($all_field_array['R2-Duration']);
                unset($all_field_array['R2-Cost']);
            }
        } elseif (check_logged_account_type('CUSTOMER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-Duration']);
            unset($all_field_array['C-Cost']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['USER-CLI']);
            unset($all_field_array['Org-Duration']);
            unset($all_field_array['R1-Account']);
            unset($all_field_array['R1-Tariff']);
            unset($all_field_array['R1-Duration']);
            unset($all_field_array['R1-Cost']);
            unset($all_field_array['R2-Account']);
            unset($all_field_array['R2-Tariff']);
            unset($all_field_array['R2-Duration']);
            unset($all_field_array['R2-Cost']);
            unset($all_field_array['R3-Account']);
            unset($all_field_array['R3-Tariff']);
            unset($all_field_array['R3-Duration']);
            unset($all_field_array['R3-Cost']);
        } else {
            
        }

        //////////////////
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {//die;
            ini_set('memory_limit', '2048M');
            $format = param_decrypt($format);

            $per_page = 50000;
            $segment = 0;

            $response = $this->report_mod->api_analytics_cdr($search_data, $per_page, $segment);
            $listing_data = $response['result'];
            $listing_count = $response['total'];

            $export_data = array();
            if ($listing_count > 0) {
                //$export_data_temp = array('');
                foreach ($listing_data as $listing_row) {
                    $export_data_temp = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {
                        $export_data_temp[] = $listing_row[$field_name];
                    }
                    $export_data[] = $export_data_temp;
                }
            }


            //prepare search data
            $search_array = array();
            if ($_SESSION['search_cdr_data']['s_cdr_user_type'] != '') {
                if ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'U')
                    $search_array['User Type'] = 'User';
                elseif ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'R1')
                    $search_array['User Type'] = 'Reseller 1';
                elseif ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'R2')
                    $search_array['User Type'] = 'Reseller 2';
                elseif ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'R3')
                    $search_array['User Type'] = 'Reseller 3';
            }
            if ($_SESSION['search_cdr_data']['s_cdr_user_account'] != '')
                $search_array['User Account'] = $_SESSION['search_cdr_data']['s_cdr_user_account'];
            if ($_SESSION['search_cdr_data']['s_cdr_dialed_no'] != '')
                $search_array['Dialed No'] = $_SESSION['search_cdr_data']['s_cdr_dialed_no'];
            if ($_SESSION['search_cdr_data']['s_cdr_carrier_dst_no'] != '')
                $search_array['Carrier DST No'] = $_SESSION['search_cdr_data']['s_cdr_carrier_dst_no'];
            if ($_SESSION['search_cdr_data']['s_cdr_user_cli'] != '')
                $search_array['User Cli'] = $_SESSION['search_cdr_data']['s_cdr_user_cli'];
            if ($_SESSION['search_cdr_data']['s_cdr_carrier_cli'] != '')
                $search_array['Carrier Cli'] = $_SESSION['search_cdr_data']['s_cdr_carrier_cli'];
            if ($_SESSION['search_cdr_data']['s_cdr_carrier'] != '')
                $search_array['Carrier'] = $_SESSION['search_cdr_data']['s_cdr_carrier'];
            if ($_SESSION['search_cdr_data']['s_cdr_carrier_ip'] != '')
                $search_array['Carrier IP'] = $_SESSION['search_cdr_data']['s_cdr_carrier_ip'];
            if ($_SESSION['search_cdr_data']['s_cdr_user_ip'] != '')
                $search_array['User IP'] = $_SESSION['search_cdr_data']['s_cdr_user_ip'];
            if ($_SESSION['search_cdr_data']['s_cdr_call_duration'] != '')
                $search_array['Call Duration'] = $_SESSION['search_cdr_data']['s_cdr_call_duration'];
            if ($_SESSION['search_cdr_data']['s_time_range'] != '')
                $search_array['Time Range'] = $_SESSION['search_cdr_data']['s_time_range'];

            // column titles
            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }

            $file_name = 'cdr_report_connected_calls';

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);


            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;

            //////////////////
        }

        if ($is_file_downloaded === false) {

            /*             * **** pagination code start here ********* */
            $pagination_uri_segment = 3;
            if (isset($_SESSION['search_cdr_data']['s_no_of_records']) && $_SESSION['search_cdr_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_cdr_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;
            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }



            //print_r($search_data);die;
            $response = $this->report_mod->api_analytics_cdr($search_data, $per_page, $segment);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $data['total_records'] = $response['all_total'];


            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['all_total'], 'reports/calls_connected', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();


            $data['logged_user_type'] = $logged_user_type;
            $data['get_logged_account_level'] = $get_logged_account_level;
            $data['all_field_array'] = $all_field_array;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/calls_connected', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function accounting_billing($arg1 = '', $format = '') {
        $this->load->model('report_mod');

        $data['page_name'] = "report_accounting_billing";

        //check page action permission
        //if(!check_account_permission('report','analytics_carrier')) show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	
        $logged_user_type = get_logged_account_type();
        $get_logged_account_level = get_logged_account_level();


        /*         * **** pagination code start here ********* */
        /* 	$pagination_uri_segment = 3;
          $per_page = 20;
          if($this->uri->segment($pagination_uri_segment)==''){ $segment= 0; }
          else{ $segment= $this->uri->segment($pagination_uri_segment); } */

        ///////////////// Searching ////////////////////

        $search_data = array();
        if (isset($_POST['OkFilter'])) {
            $time_range_post = $_POST['year'] . '-' . $_POST['month'];
            if ($_POST['day'] != '') {
                $time_range_post = $time_range_post . '-' . $_POST['day'];
                $time_range = $time_range_post . ' 00:00:00 - ' . $time_range_post . ' 23:59:59';
            } else {
                $time_range = $time_range_post . '-01 00:00:00 - ' . $time_range_post . '-31 23:59:59';
            }
            $_SESSION['search_billing_data'] = array(
                's_user_account_id' => $_POST['user_account_id'],
                's_carrier_carrier_id_name' => $_POST['carrier_carrier_id_name'],
                's_group_by' => isset($_POST['group_by']) ? $_POST['group_by'] : 'user_account_id',
                's_time_range' => $time_range,
                's_year' => $_POST['year'],
                's_month' => $_POST['month'],
                's_day' => $_POST['day'],
            );
        } elseif ($arg1 == 'export') {
            
        } else {
            //default date is todays date
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';

            $today_array = explode('-', $today);

            $_SESSION['search_billing_data'] = array(
                's_user_account_id' => isset($_SESSION['search_billing_data']['s_user_account_id']) ? $_SESSION['search_billing_data']['s_user_account_id'] : '',
                's_carrier_carrier_id_name' => isset($_SESSION['search_billing_data']['s_carrier_carrier_id_name']) ? $_SESSION['search_billing_data']['s_carrier_carrier_id_name'] : '',
                's_group_by' => isset($_SESSION['search_billing_data']['s_group_by']) ? $_SESSION['search_billing_data']['s_group_by'] : 'user_account_id',
                's_time_range' => $time_range,
                's_year' => $today_array['0'],
                's_month' => $today_array['1'],
                's_day' => $today_array['2'],
            );
        }

        if ($_SESSION['search_billing_data']['s_group_by'] == 'user_account_id')
            $group_by = 'user_account_id, carrier_carrier_id_name';
        elseif ($_SESSION['search_billing_data']['s_group_by'] == 'carrier_carrier_id_name')
            $group_by = 'carrier_carrier_id_name, user_account_id';
        else
            $group_by = 'user_account_id, carrier_carrier_id_name';

        $search_data = array(
            'user_account_id' => $_SESSION['search_billing_data']['s_user_account_id'],
            'carrier_carrier_id_name' => $_SESSION['search_billing_data']['s_carrier_carrier_id_name'],
            'group_by' => $group_by,
            'time_range' => $_SESSION['search_billing_data']['s_time_range'],
        );

        ///////////////// Searching ////////////////////
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {

            $is_file_downloaded = true;

            //	die("aa");
        }
        if ($is_file_downloaded === false) {
            $response = $this->report_mod->accounting_billing($search_data, $per_page, $segment);

            $data['listing_data'] = $response['result'];
            $data['currency_options'] = $this->utils_model->get_currencies();
            $data['logged_user_type'] = $logged_user_type;
            $data['get_logged_account_level'] = $get_logged_account_level;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/accounting_billing', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function cdr_daily_usage($arg1 = '', $format = '') {
        $this->load->model('report_mod');
        $data['page_name'] = "report_daily_usage";

        //check page action permission
        //if(!check_account_permission('report','analytics_carrier')) show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	
        $logged_user_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();
        ///////////////// Searching ////////////////////

        $search_data = array();
        //print_r($_POST);
        if (isset($_POST['search_action'])) {
            $_SESSION['search_cdr_daily_usage_data'] = array(
                's_cdr_username' => $_POST['username'],
                's_cdr_user_account' => $_POST['user_account'],
                's_cdr_company' => $_POST['company'],
                's_cdr_currency' => $_POST['currency'],
                's_cdr_record_date' => $_POST['record_date'],
                's_cdr_g_account_id' => (isset($_POST['g_account_id']) ? 'Y' : 'N'),
                's_cdr_g_rec_date' => (isset($_POST['g_rec_date']) ? 'Y' : 'N'),
                's_cdr_g_rec_month' => (isset($_POST['g_rec_month']) ? 'Y' : 'N'), 's_no_of_records' => $_POST['no_of_rows']
            );
        } else {
            //default date is todays date
            $today_timestamp = strtotime("yesterday");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_cdr_daily_usage_data']['s_cdr_username'] = isset($_SESSION['search_cdr_daily_usage_data']['s_cdr_username']) ? $_SESSION['search_cdr_daily_usage_data']['s_cdr_username'] : '';
            $_SESSION['search_cdr_daily_usage_data']['s_cdr_user_account'] = isset($_SESSION['search_cdr_daily_usage_data']['s_cdr_user_account']) ? $_SESSION['search_cdr_daily_usage_data']['s_cdr_user_account'] : '';
            $_SESSION['search_cdr_daily_usage_data']['s_cdr_company'] = isset($_SESSION['search_cdr_daily_usage_data']['s_cdr_company']) ? $_SESSION['search_cdr_daily_usage_data']['s_cdr_company'] : '';
            $_SESSION['search_cdr_daily_usage_data']['s_cdr_currency'] = isset($_SESSION['search_cdr_daily_usage_data']['s_cdr_currency']) ? $_SESSION['search_cdr_daily_usage_data']['s_cdr_currency'] : '';
            $_SESSION['search_cdr_daily_usage_data']['s_cdr_record_date'] = isset($_SESSION['search_cdr_daily_usage_data']['s_cdr_record_date']) ? $_SESSION['search_cdr_daily_usage_data']['s_cdr_record_date'] : $time_range;

            $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_account_id'] = isset($_SESSION['search_cdr_daily_usage_data']['s_cdr_g_account_id']) ? $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_account_id'] : 'N';
            $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_date'] = isset($_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_date']) ? $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_date'] : 'N';
            $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_month'] = isset($_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_month']) ? $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_month'] : 'N';

            $_SESSION['search_cdr_daily_usage_data']['s_no_of_records'] = isset($_SESSION['search_cdr_daily_usage_data']['s_no_of_records']) ? $_SESSION['search_cdr_daily_usage_data']['s_no_of_records'] : RECORDS_PER_PAGE;
        }

        $search_data = array(
            'username' => $_SESSION['search_cdr_daily_usage_data']['s_cdr_username'],
            'company_name' => $_SESSION['search_cdr_daily_usage_data']['s_cdr_company'],
            'currency_id' => $_SESSION['search_cdr_daily_usage_data']['s_cdr_currency'],
            'account_id' => $_SESSION['search_cdr_daily_usage_data']['s_cdr_user_account'],
            'record_date' => $_SESSION['search_cdr_daily_usage_data']['s_cdr_record_date'],
        );

        if ($_SESSION['search_cdr_daily_usage_data']['s_cdr_g_account_id'] == 'Y' && $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_date'] == 'Y') {
            
        } else {
            $search_data['g_account_id'] = $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_account_id'];
            $search_data['g_rec_date'] = $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_date'];
        }
        $search_data['g_rec_month'] = $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_month'];

        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['s_account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER')))
            $search_data['s_superagent'] = $logged_account_id;
        elseif (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;


        $all_field_array = array(
            'account_id' => 'Account Id'
            , 'company_name' => 'Company Name'
            , 'username' => 'User Name'
            , 'record_date' => 'Record Date'
            , 'record_date_month' => 'Record Month'
            , 'currency' => 'Currency'
            , 'mins_out' => 'Mins Out'
            , 'calls_out' => 'Calls Out'
            , 'acd_out' => 'ACD Out'
            , 'asr_out' => 'ASR Out'
            , 'usercost_out' => 'User Cost Out'
            , 'carriercost_out' => 'Carriercost Out'
            , 'profit_out' => 'Profit Out'
            , 'calls_in' => 'Calls In'
            , 'mins_in' => 'Mins In'
            , 'usercost_in' => 'User Cost In'
            , 'carriercost_in' => 'Carrier Cost In'
            , 'did_rental_user' => 'Did Rental User'
            , 'did_setup_user' => 'Did Setup User'
            , 'didrental_carrier' => 'Didrental Carrier'
            , 'didsetup_carrier' => 'Didsetup Carrier'
            , 'other_services' => 'Other Services'
            , 'profit_in' => 'Profit In'
            , 'total_profit' => 'Total Profit'
            , 'payment' => 'Payment'
            , 'reimburse' => 'Reimburse'
            , 'credit_added' => 'Credit Added'
            , 'credit_remove' => 'Credit Remove'
        );


        if ($_SESSION['search_cdr_daily_usage_data']['s_cdr_g_account_id'] == 'Y' && $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_date'] == 'Y') {
            
        } elseif ($_SESSION['search_cdr_daily_usage_data']['s_cdr_g_account_id'] == 'Y') {
            unset($all_field_array['record_date']);
        } elseif ($_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_date'] == 'Y') {
            unset($all_field_array['account_id']);
            unset($all_field_array['company_name']);
            unset($all_field_array['username']);
        }

        if ($_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_month'] == 'Y' && $_SESSION['search_cdr_daily_usage_data']['s_cdr_g_account_id'] == 'Y') {
            
        } elseif ($_SESSION['search_cdr_daily_usage_data']['s_cdr_g_rec_month'] == 'Y') {
            unset($all_field_array['account_id']);
            unset($all_field_array['company_name']);
            unset($all_field_array['username']);
            unset($all_field_array['record_date']);
        } else
            unset($all_field_array['record_date_month']);


        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {//die;
            $format = param_decrypt($format);

            $currency = '';
            if ($_SESSION['search_cdr_daily_usage_data']['s_cdr_currency'] != '') {
                if ($_SESSION['search_cdr_daily_usage_data']['s_cdr_currency'] == '1')
                    $currency = 'USD';
                elseif ($_SESSION['search_cdr_daily_usage_data']['s_cdr_currency'] == '2')
                    $currency = 'GBP';
                elseif ($_SESSION['search_cdr_daily_usage_data']['s_cdr_currency'] == '3')
                    $currency = 'EUR';
                elseif ($_SESSION['search_cdr_daily_usage_data']['s_cdr_currency'] == '4')
                    $currency = 'INR';
            }

            $search_array = array();
            if ($_SESSION['search_cdr_daily_usage_data']['s_cdr_username'] != '')
                $search_array['User Name'] = $_SESSION['search_cdr_daily_usage_data']['s_cdr_username'];
            if ($_SESSION['search_cdr_daily_usage_data']['s_cdr_user_account'] != '')
                $search_array['Account Id'] = $_SESSION['search_cdr_daily_usage_data']['s_cdr_user_account'];
            if ($_SESSION['search_cdr_daily_usage_data']['s_cdr_company'] != '')
                $search_array['Company Name'] = $_SESSION['search_cdr_daily_usage_data']['s_cdr_company'];
            if ($currency != '')
                $search_array['Currency'] = $currency;
            if ($_SESSION['search_cdr_daily_usage_data']['s_cdr_record_date'] != '')
                $search_array['Record Date'] = $_SESSION['search_cdr_daily_usage_data']['s_cdr_record_date'];


            // column titles
            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }

            $per_page = 1000;
            $segment = 0;


            $response = $this->report_mod->get_cdr_daily_usage($search_data, $per_page, $segment);

            $data['listing_data'] = $response['result'];

            $export_data = array();
            if (count($data['listing_data']) > 0) {
                $export_data_temp = array('');
                foreach ($data['listing_data'] as $listing_row) {
                    $export_data_temp = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {
                        $display_value = $listing_row[$field_name];
                        if ($field_name == 'record_date') {
                            $display_value = date(DATE_FORMAT_1, strtotime($display_value));
                        }
                        $export_data_temp[] = $display_value;
                    }
                    $export_data[] = $export_data_temp;
                }
            }


            $file_name = 'daily_usage_report';
            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        if ($is_file_downloaded === false) {

            /*             * **** pagination code start here ********* */
            $pagination_uri_segment = 3;

            if (isset($_SESSION['search_cdr_daily_usage_data']['s_no_of_records']) && $_SESSION['search_cdr_daily_usage_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_cdr_daily_usage_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }
            //print_r($search_data);
            $response = $this->report_mod->get_cdr_daily_usage($search_data, $per_page, $segment);

            $data['listing_data'] = $response['result'];

            $totalRows = $this->report_mod->total_count;
            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($totalRows, 'reports/cdr_daily_usage', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();


            $data['logged_user_type'] = $logged_user_type;
            $data['get_logged_account_level'] = $get_logged_account_level;

            $data['all_field_array'] = $all_field_array;
            $data['currency_data'] = $this->utils_model->get_currencies();

            $this->load->view('basic/header', $data);
            $this->load->view('reports/cdr_daily_usage', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function carrier_daily_usage($arg1 = '', $format = '') {
        $this->load->model('report_mod');
        $data['page_name'] = "carrier_daily_usage";

        //check page action permission
        //if(!check_account_permission('report','analytics_carrier')) show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	
        $logged_user_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();
        ///////////////// Searching ////////////////////

        $search_data = array();
        //print_r($_POST);
        if (isset($_POST['search_action'])) {
            $_SESSION['search_carrier_daily_usage_data'] = array(
                's_carrier_account' => $_POST['carrier_account'],
                's_carrier_name' => $_POST['carrier_name'],
                's_carrier_currency' => $_POST['currency'],
                's_calls_date' => $_POST['calls_date'],
                'carrier_grp_account_id' => (isset($_POST['grp_account_id']) ? 'Y' : 'N'),
                'carrier_grp_dest' => (isset($_POST['grp_destination']) ? 'Y' : 'N'),
                'carrier_grp_calls_date' => (isset($_POST['grp_calls_date']) ? 'Y' : 'N'),
                's_no_of_records' => $_POST['no_of_rows']
            );
        } else {
            //default date is todays date
            $today_timestamp = strtotime("yesterday");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';


            $_SESSION['search_carrier_daily_usage_data']['s_carrier_account'] = isset($_SESSION['search_carrier_daily_usage_data']['s_carrier_account']) ? $_SESSION['search_carrier_daily_usage_data']['s_carrier_account'] : '';
            $_SESSION['search_carrier_daily_usage_data']['s_carrier_name'] = isset($_SESSION['search_carrier_daily_usage_data']['s_carrier_name']) ? $_SESSION['search_carrier_daily_usage_data']['s_carrier_name'] : '';

            $_SESSION['search_carrier_daily_usage_data']['s_carrier_currency'] = isset($_SESSION['search_carrier_daily_usage_data']['s_carrier_currency']) ? $_SESSION['search_carrier_daily_usage_data']['s_carrier_currency'] : '';
            $_SESSION['search_carrier_daily_usage_data']['s_calls_date'] = $time_range;

            $_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'] = isset($_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id']) ? $_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'] : 'N';
            $_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'] = isset($_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest']) ? $_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'] : 'N';
            $_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'] = isset($_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date']) ? $_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'] : 'N';

            $_SESSION['search_carrier_daily_usage_data']['s_no_of_records'] = isset($_SESSION['search_carrier_daily_usage_data']['s_no_of_records']) ? $_SESSION['search_carrier_daily_usage_data']['s_no_of_records'] : RECORDS_PER_PAGE;
        }

        $search_data = array(
            'carrier_account' => $_SESSION['search_carrier_daily_usage_data']['s_carrier_account'],
            'carrier_name' => $_SESSION['search_carrier_daily_usage_data']['s_carrier_name'],
            'carrier_currency_id' => $_SESSION['search_carrier_daily_usage_data']['s_carrier_currency'],
            'calls_date' => $_SESSION['search_carrier_daily_usage_data']['s_calls_date']
        );

        if (($_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'] == 'Y') && ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'] == 'Y') && ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'] == 'Y')) {
            $search_data['g_account_id'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'];
            $search_data['grp_destination'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'];
            $search_data['grp_calls_date'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'];
        } elseif (($_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'] == 'Y') && ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'] == 'Y')) {
            $search_data['g_account_id'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'];
            $search_data['grp_destination'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'];
        } elseif (($_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'] == 'Y') && ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'] == 'Y')) {
            $search_data['g_account_id'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'];
            $search_data['grp_calls_date'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'];
        } elseif (($_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'] == 'Y') && ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'] == 'Y')) {
            $search_data['grp_destination'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'];
            $search_data['grp_calls_date'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'];
        } elseif ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'] == 'Y') {
            $search_data['g_account_id'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'];
        } elseif ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'] == 'Y') {
            $search_data['grp_destination'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'];
        } else {
            $search_data['grp_calls_date'] = $_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'];
        }


        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['s_account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER')))
            $search_data['s_superagent'] = $logged_account_id;
        elseif (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;


        $all_field_array = array(
            'carrier_account' => 'Carrier Account'
            , 'carrier_name' => 'Carrier name'
            , 'prefix' => 'Prefix'
            , 'destination' => 'Destination'
            , 'currency_name' => 'Currency'
            , 'asr' => 'ASR'
            , 'acd' => 'ACD'
            , 'answercalls' => 'Calls'
            , 'out_minute' => 'OutMins'
            , 'calls_date' => 'Calls Date'
            , 'carriercost' => 'Cost'
            , 'code402' => 'code402'
            , 'code403' => 'code403'
            , 'code404' => 'code404'
            , 'code407' => 'code407'
            , 'code500' => 'code500'
            , 'code503' => 'code503'
            , 'code487' => 'code487'
            , 'code488' => 'code488'
            , 'code501' => 'code501'
            , 'code483' => 'code483'
            , 'code410' => 'code410'
            , 'code515' => 'CCLimit'
            , 'code486' => 'code486'
            , 'code480' => 'code480'
        );

        if ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'] == 'Y' && $_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'] == 'Y' && $_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'] == 'Y') {
            unset($all_field_array['calls_date']);
        }

        if ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'] == 'Y' && $_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'] == 'Y') {
            unset($all_field_array['calls_date']);
        } elseif ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_account_id'] == 'Y') {
            unset($all_field_array['calls_date']);
            unset($all_field_array['prefix']);
        } elseif ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_dest'] == 'Y') {
            unset($all_field_array['carrier_account']);
            unset($all_field_array['prefix']);
            unset($all_field_array['calls_date']);
        } elseif ($_SESSION['search_carrier_daily_usage_data']['carrier_grp_calls_date'] == 'Y') {
            unset($all_field_array['carrier_account']);
            unset($all_field_array['prefix']);
        } else {
            
        }


        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {//die;
            $format = param_decrypt($format);

            $currency = '';
            if ($_SESSION['search_carrier_daily_usage_data']['s_carrier_currency'] != '') {
                if ($_SESSION['search_carrier_daily_usage_data']['s_carrier_currency'] == '1')
                    $currency = 'USD';
                elseif ($_SESSION['search_carrier_daily_usage_data']['s_carrier_currency'] == '2')
                    $currency = 'GBP';
                elseif ($_SESSION['search_carrier_daily_usage_data']['s_carrier_currency'] == '3')
                    $currency = 'EUR';
                elseif ($_SESSION['search_carrier_daily_usage_data']['s_carrier_currency'] == '4')
                    $currency = 'INR';
            }

            $search_array = array();
            if ($_SESSION['search_carrier_daily_usage_data']['s_carrier_account'] != '')
                $search_array['Carrier Account'] = $_SESSION['search_carrier_daily_usage_data']['s_carrier_account'];
            if ($_SESSION['search_carrier_daily_usage_data']['s_carrier_name'] != '')
                $search_array['Carrier Name'] = $_SESSION['search_carrier_daily_usage_data']['s_carrier_name'];

            if ($currency != '')
                $search_array['Currency'] = $currency;
            if ($_SESSION['search_carrier_daily_usage_data']['s_calls_date'] != '')
                $search_array['Calls Date'] = $_SESSION['search_carrier_daily_usage_data']['s_calls_date'];

            // column titles
            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }

            $per_page = 1000;
            $segment = 0;


            $response = $this->report_mod->get_carrier_daily_usage($search_data, $per_page, $segment);

            $data['listing_data'] = $response['result'];


            $export_data = array();
            if (count($data['listing_data']) > 0) {
                $export_data_temp = array('');
                foreach ($data['listing_data'] as $listing_row) {
                    $export_data_temp = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {
                        $display_value = $listing_row[$field_name];
                        if ($field_name == 'calls_date') {
                            $display_value = date(DATE_FORMAT_1, strtotime($display_value));
                        }
                        $export_data_temp[] = $display_value;
                    }
                    $export_data[] = $export_data_temp;
                }
            }

            $file_name = 'carrier_daily_usage';
            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        if ($is_file_downloaded === false) {

            /*             * *** pagination code start here ********* */
            $pagination_uri_segment = 3;
            if (isset($_SESSION['search_carrier_daily_usage_data']['s_no_of_records']) && $_SESSION['search_carrier_daily_usage_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_carrier_daily_usage_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;


            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }

            $response = $this->report_mod->get_carrier_daily_usage($search_data, $per_page, $segment);

            $data['listing_data'] = $response['result'];

            $totalRows = $this->report_mod->total_count;
            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($totalRows, 'reports/carrier_daily_usage', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();


            $data['logged_user_type'] = $logged_user_type;
            $data['get_logged_account_level'] = $get_logged_account_level;

            $data['all_field_array'] = $all_field_array;
            $data['currency_data'] = $this->utils_model->get_currencies();

            $data['total_records'] = $totalRows;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/carrier_daily_usage', $data);
            $this->load->view('basic/footer', $data);
        }
    }

}
