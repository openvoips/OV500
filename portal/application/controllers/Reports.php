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

class Reports extends CI_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();
        $this->form_validation->set_error_delimiters('', '');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->load->model('report_mod');
    }

    public function index() {
        $page_name = "report_index";
        $this->livecall();
    }

    function ProfitLoss($arg1 = '', $format = '') {

        $data['page_name'] = "ProfitLoss";

        //  print_r($_POST);
        //check page action permission
        if (!check_account_permission('reports', 'ProfitLoss'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->model('carrier_mod');
        //$response = $this->carrier_mod->get_data('', 0, '', array(), array());
        // $data['carrier_data'] = $response['result'];
        $data['currency_data'] = $this->utils_model->get_currencies();
        $currency_data = $data['currency_data'];
        ///////////////// Searching ////////////////////
        // reservation-time
        $is_make_search = false;
        $search_data = array();
        if (isset($_POST['OkFilter'])) {
            $_SESSION['search_data'] = array(
                's_call_date' => $_POST['frmtime'],
                's_carrier' => $_POST['frmcarrier'],
                's_dest' => $_POST['frmdest'],
                's_prefix' => $_POST['frmprefix'],
                's_ctype' => $_POST['frmctype'],
                's_code' => $_POST['frmcode'],
                's_customer_company_name' => $_POST['customer_company_name'],
                's_g_user' => (isset($_POST['g_user']) ? 'Y' : 'N'),
                's_g_carrier' => (isset($_POST['g_carrier']) ? 'Y' : 'N'),
                's_g_date' => (isset($_POST['g_date']) ? 'Y' : 'N'),
                's_g_hour' => (isset($_POST['g_hour']) ? 'Y' : 'N'),
                's_g_minute' => (isset($_POST['g_minute']) ? 'Y' : 'N'),
                's_g_prefix' => (isset($_POST['g_prefix']) ? 'Y' : 'N'),
                's_g_dest' => (isset($_POST['g_dest']) ? 'Y' : 'N'),
            );
            $is_make_search = true;
        } elseif ($arg1 != 'export') {
            $_SESSION['search_data'] = array(
                's_call_date' => '',
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
                's_customer_company_name' => '',
                's_ctype' => '',
            );
        } else {
            $is_make_search = true;
        }

        $search_data = array(
            'call_date' => $_SESSION['search_data']['s_call_date'],
            'carrier_id' => $_SESSION['search_data']['s_carrier'],
            'account_id' => $_SESSION['search_data']['s_code'],
            'company_name' => $_SESSION['search_data']['s_customer_company_name'],
            'account_type' => $_SESSION['search_data']['s_ctype'],
            'prefix' => $_SESSION['search_data']['s_prefix'],
            'destination' => $_SESSION['search_data']['s_dest'],
            'group_by_carrier' => $_SESSION['search_data']['s_g_carrier'],
            'group_by_user' => $_SESSION['search_data']['s_g_user'],
            'group_by_hour' => $_SESSION['search_data']['s_g_hour'],
            'group_by_minute' => $_SESSION['search_data']['s_g_minute'],
            'group_by_date' => $_SESSION['search_data']['s_g_date'],
            'group_by_prefix' => $_SESSION['search_data']['s_g_prefix'],
            'group_by_destination' => $_SESSION['search_data']['s_g_dest'],
            'logged_customer_type' => get_logged_account_type(),
            'logged_customer_account_id' => get_logged_account_id(),
            'logged_customer_level' => get_logged_account_level(),
        );

        if ($is_make_search) {
            $response = $this->report_mod->ProfitLoss($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
        }
        $is_file_downloaded = false;

        //////// add export  ////////////////	
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $response = $this->report_mod->ProfitLoss($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $format = param_decrypt($format);
            $file_name = 'ProfitLoss';
            $export_header = array();
            if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y')
                $export_header[] = 'Customer';
            if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y')
                $export_header[] = 'Carrier';
            if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y')
                $export_header[] = 'Date';
            if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y')
                $export_header[] = 'Hour';
            if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_minute'] == 'Y')
                $export_header[] = 'Minute';
            if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y')
                $export_header[] = 'Prefix';
            if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y')
                $export_header[] = 'Destination';

            $export_header[] = 'Total Duration';
            $export_header[] = 'Total Calls';
            $export_header[] = 'Ans Calls';
            $export_header[] = 'Cost';
            $export_data = array();
            $currency_abbr = function($id) use ($currency_data) {
                $key = array_search($id, array_column($currency_data, 'currency_id'));
                if ($key === false)
                    return '';
                else
                    return $currency_data[$key]['name'];
            };
            if (isset($response['result']) && $response['total'] > 0) {

                foreach ($response['result'] as $listing_row) {
                    $export_data_temp = array();
                    if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y')
                        $export_data_temp[] = $listing_row['account_code'];
                    if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y')
                        $export_data_temp[] = $listing_row['carrier_id'];
                    if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y')
                        $export_data_temp[] = $listing_row['call_date'];
                    if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y')
                        $export_data_temp[] = $listing_row['calltime_h'];
                    if (isset($_SESSION['search_data']['s_g_minute']) && $_SESSION['search_data']['s_g_minute'] == 'Y')
                        $export_data_temp[] = $listing_row['calltime_m'];
                    if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y')
                        $export_data_temp[] = $listing_row['prefix'];
                    if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y')
                        $export_data_temp[] = $listing_row['prefix_name'];

                    $export_data_temp[] = $listing_row['total_duration'];
                    $export_data_temp[] = $listing_row['total_calls'];
                    $export_data_temp[] = $listing_row['answered_calls'];
                    $export_data_temp[] = $currency_abbr($listing_row['currency_id']) . ' ' . $listing_row['cost'];
                    $export_data[] = $export_data_temp;
                }
            }
            if ($_SESSION['search_data']['s_date'] != '')
                $search_array['Date'] = date('d-m-Y', strtotime($_SESSION['search_data']['s_date']));
            if ($_SESSION['search_data']['s_time_from'] != '' && $_SESSION['search_data']['s_time_to'] != '')
                $search_array['Time'] = 'From ' . $_SESSION['search_data']['s_time_from'] . ' To ' . $_SESSION['search_data']['s_time_to'];


            if ($_SESSION['search_data']['s_carrier'] != '')
                $search_array['Carrier ID Name'] = $_SESSION['search_data']['s_carrier'];
            if ($_SESSION['search_data']['s_code'] != '')
                $search_array['Account ID'] = $_SESSION['search_data']['s_code'];
            if ($_SESSION['search_data']['s_customer_company_name'] != '')
                $search_array['Company Name'] = $_SESSION['search_data']['s_customer_company_name'];
            if ($_SESSION['search_data']['s_ctype'] != '') {
                if ($_SESSION['search_data']['s_ctype'] == 'U')
                    $search_array['Account Type'] = 'User';
                else
                    $search_array['Account Type'] = 'Reseller';
            }
            if ($_SESSION['search_data']['s_prefix'] != '')
                $search_array['Prefix'] = $_SESSION['search_data']['s_prefix'];
            if ($_SESSION['search_data']['s_dest'] != '')
                $search_array['Destination'] = $_SESSION['search_data']['s_dest'];

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        if ($is_file_downloaded === false) {
            $this->load->view('basic/header', $data);
            $this->load->view('reports/ProfitLoss', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function api_livecall() {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
            die();
        }
        $logged_customer_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();


        $DB1 = $this->load->database('cdrdb', true);

        $sql = "select customer_account_id, customer_src_ip, customer_destination,customer_src_caller, customer_src_callee, carrier_carrier_id, carrier_gateway_ipaddress, carrier_gateway_ipaddress_name, carrier_dialplan_id_name, start_time, answer_time, TIMESTAMPDIFF(SECOND , answer_time, NOW()) as duration, callstatus, fs_host, notes 
		FROM livecalls 
		WHERE callstatus in ('answer','ring','progress') ";


        if (check_logged_account_type(array('RESELLER'))) {
            $sub_sql = "SELECT GROUP_CONCAT(\"'\",account_id,\"'\") account_ids FROM  account WHERE parent_account_id='" . $logged_account_id . "'";
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






        $sql .= " ORDER BY livecalls_id desc limit 1000";
        // $result = $DB1->query($sql);
        $result = $this->db->query($sql);

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

    private function del_api_analytics($search_data) {


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

                if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
                    $level = $search_data['logged_customer_level'] + 1;
                    $field_name = 'r' . $level . '_account_id';

                    $str .= " AND `" . $field_name . "` = '" . $search_data['account_id'] . "'";
                } else {
                    $str .= " and r1_account_id = '" . $search_data['account_id'] . "'";
                }
            }
        }

        /* ------------------------------ */
        if (trim($search_data['company_name']) != '')
            $str .= " AND customer_company_name LIKE '%" . trim($search_data['company_name']) . "%' ";

        /* ------------------------------ */


        ////
        if (isset($search_data['logged_customer_type']) && isset($search_data['logged_customer_account_id']) && isset($search_data['logged_customer_level']) && $search_data['logged_customer_type'] == 'RESELLER' && in_array($search_data['logged_customer_level'], array(1, 2, 3))) {
            $level = $search_data['logged_customer_level'];
            $field_name = 'r' . $level . '_account_id';

            $str .= " AND `" . $field_name . "` = '" . $search_data['logged_customer_account_id'] . "'";
        }
        //
        ////


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
        //echo $query;
        $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($query);

        $return['total'] = $result->num_rows();
        $return['result'] = $result->result_array();


        return $return;
    }

    public function CarrQOSR($arg1 = '', $format = '') {
        $data['page_name'] = "CarrQOSR";

        //check page action permission
        if (!check_account_permission('reports', 'CarrQOSR'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	

        $this->load->model('carrier_mod');


        $response = $this->carrier_mod->get_data('', 0, '', array(), array());
        $data['carrier_data'] = $response['result'];
        $data['currency_data'] = $this->utils_model->get_currencies();
        $currency_data = $data['currency_data'];
        ///////////////// Searching ////////////////////
        $is_make_search = false;
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
            $is_make_search = true;
        } elseif ($arg1 != 'export') {
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
        }
        $search_data = array('timerange' => $_SESSION['search_data']['s_time'],
            'carrier_id' => $_SESSION['search_data']['s_carrier'],
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

        if ($is_make_search) {
            $response = $this->report_mod->CarrQOSR($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
        }

        $is_file_downloaded = false;
        //////// add export  ////////////////	
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $format = param_decrypt($format);

            $file_name = 'CarrQOSR';

            $response = $this->report_mod->CarrQOSR($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];

            $export_header = array();

            if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y')
                $export_header[] = 'Carrier';
            if (isset($_SESSION['search_data']['s_g_ip']) && $_SESSION['search_data']['s_g_ip'] == 'Y')
                $export_header[] = 'IP Address';
            if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y')
                $export_header[] = 'Date';
            if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y')
                $export_header[] = 'Hour';
            if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_minute'] == 'Y')
                $export_header[] = 'Minute';
            if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y')
                $export_header[] = 'Prefix';
            if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y')
                $export_header[] = 'Destination';

            $export_header[] = 'Total Duration';
            $export_header[] = 'Total Calls';
            $export_header[] = 'Ans Calls';
            $export_header[] = 'ACD';
            $export_header[] = 'ASR';
            $export_header[] = 'Avg PDD';

            if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y' || $_SESSION['search_data']['s_carrier'] != '')
                $export_header[] = 'Cost';

            if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y')
                $export_header[] = 'SIP Code';
            if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y')
                $export_header[] = 'Q850 Code';


            $export_data = array();
            $currency_abbr = function($id) use ($currency_data) {
                $key = array_search($id, array_column($currency_data, 'currency_id'));
                if ($key === false)
                    return '';
                else
                    return $currency_data[$key]['name'];
            };
            if (isset($response['result']) && $response['total'] > 0) {

                foreach ($response['result'] as $listing_row) {
                    $export_data_temp = array();


                    if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y')
                        $export_data_temp[] = $listing_row['carrier_id'];
                    if (isset($_SESSION['search_data']['s_g_ip']) && $_SESSION['search_data']['s_g_ip'] == 'Y')
                        $export_data_temp[] = $listing_row['ip_address'];
                    if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y')
                        $export_data_temp[] = $listing_row['call_date'];
                    if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y')
                        $export_data_temp[] = $listing_row['calltime_h'];
                    if (isset($_SESSION['search_data']['s_g_minute']) && $_SESSION['search_data']['s_g_minute'] == 'Y')
                        $export_data_temp[] = $listing_row['calltime_m'];
                    if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y')
                        $export_data_temp[] = $listing_row['prefix'];
                    if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y')
                        $export_data_temp[] = $listing_row['prefix_name'];

                    $export_data_temp[] = $listing_row['total_duration'];
                    $export_data_temp[] = $listing_row['total_calls'];
                    $export_data_temp[] = $listing_row['answered_calls'];
                    $export_data_temp[] = $listing_row['acd'];
                    $export_data_temp[] = $listing_row['asr'];
                    $export_data_temp[] = $listing_row['pdd'];

                    if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y' || $_SESSION['search_data']['s_carrier'] != '')
                        $export_data_temp[] = $currency_abbr($listing_row['currency_id']) . ' ' . $listing_row['cost'];

                    if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y')
                        $export_data_temp[] = $listing_row['SIPCODE'];
                    if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y')
                        $export_data_temp[] = $listing_row['Q850CODE'];

                    $export_data[] = $export_data_temp;
                }
            }

            if ($_SESSION['search_data']['s_time'] != '')
                $search_array['Time Range'] = $_SESSION['search_data']['s_time'];
            if ($_SESSION['search_data']['s_carrier'] != '')
                $search_array['Carrier ID Name'] = $_SESSION['search_data']['s_carrier'];
            if ($_SESSION['search_data']['s_code'] != '')
                $search_array['IP Address'] = $_SESSION['search_data']['s_code'];

            if ($_SESSION['search_data']['s_prefix'] != '')
                $search_array['Prefix'] = $_SESSION['search_data']['s_prefix'];
            if ($_SESSION['search_data']['s_dest'] != '')
                $search_array['Destination'] = $_SESSION['search_data']['s_dest'];
            if ($_SESSION['search_data']['s_sip'] != '')
                $search_array['SIP Code'] = $_SESSION['search_data']['s_sip'];
            if ($_SESSION['search_data']['s_q850'] != '')
                $search_array['Q850 Code'] = $_SESSION['search_data']['s_q850'];


            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);


            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        //------------------ end export ----------------------	

        if ($is_file_downloaded === false) {
            $this->load->view('basic/header', $data);
            $this->load->view('reports/CarrQOSR', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function monin_data($incoming_calls = 'Y', $outgoing_calls = 'Y', $incoming_duration = 'Y', $outgoing_duration = 'Y', $gateway_calls = 'Y', $customer_calls = 'Y', $show_usage = 'Y', $customer_call_stat = 'N', $carrier_call_stat = 'N', $livecalls_destination = 'N') {
        $this->report_mod->monin_data($incoming_calls, $outgoing_calls, $incoming_duration, $outgoing_duration, $gateway_calls, $customer_calls, $show_usage, $customer_call_stat, $carrier_call_stat, $livecalls_destination);
    }

    public function CustQOSR($arg1 = '', $format = '') {
        $data['page_name'] = "CustQOSR";

        //  print_r($_POST);
        //check page action permission
        if (!check_account_permission('reports', 'CustQOSR'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->model('carrier_mod');
        //$response = $this->carrier_mod->get_data('', 0, '', array(), array());
        // $data['carrier_data'] = $response['result'];
        $data['currency_data'] = $this->utils_model->get_currencies();
        $currency_data = $data['currency_data'];
        ///////////////// Searching ////////////////////
// reservation-time
        $is_make_search = false;
        $search_data = array();
        if (isset($_POST['OkFilter'])) {
            $_SESSION['search_data'] = array(
                's_call_date' => $_POST['frmtime'],
                's_carrier' => $_POST['frmcarrier'],
                's_dest' => $_POST['frmdest'],
                's_prefix' => $_POST['frmprefix'],
                's_ctype' => $_POST['frmctype'],
                's_code' => $_POST['frmcode'],
                's_sip' => $_POST['frmsipcode'],
                's_q850' => $_POST['frmq850code'],
                's_customer_company_name' => $_POST['customer_company_name'],
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
            $is_make_search = true;
        } elseif ($arg1 != 'export') {
            $_SESSION['search_data'] = array(
                's_call_date' => '',
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
                's_g_q850' => '',
                's_customer_company_name' => '',
                's_ctype' => '',
                's_sip' => '',
                's_q850' => '',
            );
        } else {
            $is_make_search = true;
        }

        $search_data = array(
            'call_date' => $_SESSION['search_data']['s_call_date'],
            'carrier_id' => $_SESSION['search_data']['s_carrier'],
            'account_id' => $_SESSION['search_data']['s_code'],
            'company_name' => $_SESSION['search_data']['s_customer_company_name'],
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
            'logged_customer_type' => get_logged_account_type(),
            'logged_customer_account_id' => get_logged_account_id(),
            'logged_customer_level' => get_logged_account_level(),
        );

        if ($is_make_search) {
            $response = $this->report_mod->CustQOSR($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
        }
        $is_file_downloaded = false;

        //////// add export  ////////////////	
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $response = $this->report_mod->CustQOSR($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $format = param_decrypt($format);
            $file_name = 'CustQOSR';
            $export_header = array();
            if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y')
                $export_header[] = 'Customer';
            if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y')
                $export_header[] = 'Carrier';
            if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y')
                $export_header[] = 'Date';
            if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y')
                $export_header[] = 'Hour';
            if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_minute'] == 'Y')
                $export_header[] = 'Minute';
            if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y')
                $export_header[] = 'Prefix';
            if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y')
                $export_header[] = 'Destination';

            $export_header[] = 'Total Duration';
            $export_header[] = 'Total Calls';
            $export_header[] = 'Ans Calls';
            $export_header[] = 'ACD';
            $export_header[] = 'ASR';
            $export_header[] = 'Avg PDD';
            $export_header[] = 'Cost';

            if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y')
                $export_header[] = 'SIP Code';
            if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y')
                $export_header[] = 'Q850 Code';


            $export_data = array();
            $currency_abbr = function($id) use ($currency_data) {
                $key = array_search($id, array_column($currency_data, 'currency_id'));
                if ($key === false)
                    return '';
                else
                    return $currency_data[$key]['name'];
            };
            if (isset($response['result']) && $response['total'] > 0) {

                foreach ($response['result'] as $listing_row) {
                    $export_data_temp = array();
                    if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y')
                        $export_data_temp[] = $listing_row['account_code'];
                    if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y')
                        $export_data_temp[] = $listing_row['carrier_id'];
                    if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y')
                        $export_data_temp[] = $listing_row['call_date'];
                    if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y')
                        $export_data_temp[] = $listing_row['calltime_h'];
                    if (isset($_SESSION['search_data']['s_g_minute']) && $_SESSION['search_data']['s_g_minute'] == 'Y')
                        $export_data_temp[] = $listing_row['calltime_m'];
                    if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y')
                        $export_data_temp[] = $listing_row['prefix'];
                    if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y')
                        $export_data_temp[] = $listing_row['prefix_name'];

                    $export_data_temp[] = $listing_row['total_duration'];
                    $export_data_temp[] = $listing_row['total_calls'];
                    $export_data_temp[] = $listing_row['answered_calls'];
                    $export_data_temp[] = $listing_row['acd'];
                    $export_data_temp[] = $listing_row['asr'];
                    $export_data_temp[] = $listing_row['pdd'];
                    $export_data_temp[] = $currency_abbr($listing_row['currency_id']) . ' ' . $listing_row['cost'];


                    if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y')
                        $export_data_temp[] = $listing_row['SIPCODE'];
                    if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y')
                        $export_data_temp[] = $listing_row['Q850CODE'];

                    $export_data[] = $export_data_temp;
                }
            }
            if ($_SESSION['search_data']['s_date'] != '')
                $search_array['Date'] = date('d-m-Y', strtotime($_SESSION['search_data']['s_date']));
            if ($_SESSION['search_data']['s_time_from'] != '' && $_SESSION['search_data']['s_time_to'] != '')
                $search_array['Time'] = 'From ' . $_SESSION['search_data']['s_time_from'] . ' To ' . $_SESSION['search_data']['s_time_to'];


            if ($_SESSION['search_data']['s_carrier'] != '')
                $search_array['Carrier ID Name'] = $_SESSION['search_data']['s_carrier'];
            if ($_SESSION['search_data']['s_code'] != '')
                $search_array['Account ID'] = $_SESSION['search_data']['s_code'];
            if ($_SESSION['search_data']['s_customer_company_name'] != '')
                $search_array['Company Name'] = $_SESSION['search_data']['s_customer_company_name'];
            if ($_SESSION['search_data']['s_ctype'] != '') {
                if ($_SESSION['search_data']['s_ctype'] == 'U')
                    $search_array['Account Type'] = 'User';
                else
                    $search_array['Account Type'] = 'Reseller';
            }
            if ($_SESSION['search_data']['s_prefix'] != '')
                $search_array['Prefix'] = $_SESSION['search_data']['s_prefix'];
            if ($_SESSION['search_data']['s_dest'] != '')
                $search_array['Destination'] = $_SESSION['search_data']['s_dest'];
            if ($_SESSION['search_data']['s_sip'] != '')
                $search_array['SIP Code'] = $_SESSION['search_data']['s_sip'];
            if ($_SESSION['search_data']['s_q850'] != '')
                $search_array['Q850 Code'] = $_SESSION['search_data']['s_q850'];


            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);


            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        if ($is_file_downloaded === false) {
            $this->load->view('basic/header', $data);
            $this->load->view('reports/CustQOSR', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function monin() {
        $data['page_name'] = "monin";
        if (!check_account_permission('reports', 'monin'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->view('basic/header', $data);
        $this->load->view('reports/monin', $data);
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
        $logged_customer_type = get_logged_account_type();
        $get_logged_account_level = get_logged_account_level();
        $logged_account_id = get_logged_account_id();
        ///////////////// Searching ////////////////////
        $is_report_searched = false;
        $is_file_downloaded = false;
        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_cdr_in_data'] = array(
                's_cdr_customer_type' => $_POST['customer_type'],
                's_cdr_customer_account' => $_POST['customer_account'],
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'],
                's_cdr_customer_cli' => $_POST['customer_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'],
                's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'],
                's_cdr_customer_ip' => $_POST['customer_ip'],
                's_cdr_call_duration' => $_POST['call_duration'],
                's_time_range' => $_POST['time_range'],
                's_no_of_records' => $_POST['no_of_rows'], //1 no_of_records	
                's_cdr_customer_company_name' => $_POST['customer_company_name'],
                's_cdr_call_duration_range' => $_POST['duration_range']
            );
        } elseif ($arg1 != 'export' && !isset($_SESSION['search_cdr_in_data']['s_time_range'])) {
            //default date is todays date
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION['search_cdr_in_data'] = array('s_cdr_customer_type' => '',
                's_cdr_customer_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_customer_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_customer_ip' => '',
                's_cdr_call_duration' => '',
                's_time_range' => $time_range,
                's_no_of_records' => RECORDS_PER_PAGE, //3 no_of_records
                's_cdr_customer_company_name' => '',
                's_cdr_call_duration_range' => ''
            );
        }

        $search_data = array(
            's_cdr_customer_type' => $_SESSION['search_cdr_in_data']['s_cdr_customer_type'],
            's_cdr_customer_account' => $_SESSION['search_cdr_in_data']['s_cdr_customer_account'],
            's_cdr_dialed_no' => $_SESSION['search_cdr_in_data']['s_cdr_dialed_no'],
            's_cdr_carrier_dst_no' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_dst_no'],
            's_cdr_customer_cli' => $_SESSION['search_cdr_in_data']['s_cdr_customer_cli'],
            's_cdr_carrier_cli' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_cli'],
            's_cdr_carrier' => $_SESSION['search_cdr_in_data']['s_cdr_carrier'],
            's_cdr_carrier_ip' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_ip'],
            's_cdr_customer_ip' => $_SESSION['search_cdr_in_data']['s_cdr_customer_ip'],
            's_cdr_call_duration' => $_SESSION['search_cdr_in_data']['s_cdr_call_duration'],
            's_time_range' => $_SESSION['search_cdr_in_data']['s_time_range'],
            's_cdr_customer_company_name' => $_SESSION['search_cdr_in_data']['s_cdr_customer_company_name'],
            's_cdr_call_duration_range' => $_SESSION['search_cdr_in_data']['s_cdr_call_duration_range']
        );
        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['s_account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER')))
            $search_data['s_sales_manager'] = $logged_account_id;
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
            , 'lrn_number' => 'LRN Number'
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
            , 'Incoming-Codecs' => 'Incoming-Codecs'
            , 'Outgoing-Codecs' => 'Outgoing-Codecs'
            , "Call's-Codec" => "Call's-Codec"
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

            $per_page = 100000;
            $segment = 0;

            $response = $this->report_mod->api_analytics_cdr_in($search_data, $per_page, $segment);
            $listing_data = $response['result'];
            $listing_count = $response['total'];

            $export_data = array();
            if ($listing_count > 0) {
                foreach ($listing_data as $listing_row) {
                    $export_data_temp = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {

                        if ($field_name == 'Account') {

                            if ($listing_row['customer_company_name'] != '') {

                                $export_data_temp[] = $listing_row['customer_company_name'] . ' ( ' . $listing_row[$field_name] . ' ) ';
                            } else {

                                $export_data_temp[] = $listing_row[$field_name];
                            }
                        } else {

                            $export_data_temp[] = $listing_row[$field_name];
                        }
                    }
                    $export_data[] = $export_data_temp;
                }
            }


            //prepare search data
            $search_array = array();
            if ($_SESSION['search_cdr_in_data']['s_cdr_customer_type'] != '') {
                if ($_SESSION['search_cdr_in_data']['s_cdr_customer_type'] == 'U')
                    $search_array['User Type'] = 'User';
                elseif ($_SESSION['search_cdr_in_data']['s_cdr_customer_type'] == 'R1')
                    $search_array['User Type'] = 'Reseller 1';
                elseif ($_SESSION['search_cdr_in_data']['s_cdr_customer_type'] == 'R2')
                    $search_array['User Type'] = 'Reseller 2';
                elseif ($_SESSION['search_cdr_in_data']['s_cdr_customer_type'] == 'R3')
                    $search_array['User Type'] = 'Reseller 3';
            }
            if ($_SESSION['search_cdr_in_data']['s_cdr_customer_account'] != '')
                $search_array['User Account'] = $_SESSION['search_cdr_in_data']['s_cdr_customer_account'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_dialed_no'] != '')
                $search_array['Dialed No'] = $_SESSION['search_cdr_in_data']['s_cdr_dialed_no'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_carrier_dst_no'] != '')
                $search_array['Carrier DST No'] = $_SESSION['search_cdr_in_data']['s_cdr_carrier_dst_no'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_customer_cli'] != '')
                $search_array['User Cli'] = $_SESSION['search_cdr_in_data']['s_cdr_customer_cli'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_carrier_cli'] != '')
                $search_array['Carrier Cli'] = $_SESSION['search_cdr_in_data']['s_cdr_carrier_cli'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_carrier'] != '')
                $search_array['Carrier'] = $_SESSION['search_cdr_in_data']['s_cdr_carrier'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_carrier_ip'] != '')
                $search_array['Carrier IP'] = $_SESSION['search_cdr_in_data']['s_cdr_carrier_ip'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_customer_ip'] != '')
                $search_array['User IP'] = $_SESSION['search_cdr_in_data']['s_cdr_customer_ip'];
            if ($_SESSION['search_cdr_in_data']['s_cdr_call_duration'] != '')
                $search_array['Call Duration'] = $_SESSION['search_cdr_in_data']['s_cdr_call_duration'];
            if ($_SESSION['search_cdr_in_data']['s_time_range'] != '')
                $search_array['Time Range'] = $_SESSION['search_cdr_in_data']['s_time_range'];

            if ($_SESSION['search_cdr_in_data']['s_cdr_customer_company_name'] != '')
                $search_array['Company Name'] = $_SESSION['search_cdr_in_data']['s_cdr_customer_company_name'];


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
            $data['logged_customer_type'] = $logged_customer_type;
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
        $logged_customer_type = get_logged_account_type();
        $get_logged_account_level = get_logged_account_level();
        ///////////////// Searching ////////////////////
        $is_report_searched = false;
        $search_data = array();
        //echo '<pre>';print_r($_POST);	echo '</pre>';	
        if (isset($_POST['OkFilter'])) {
            $_SESSION['search_cdr_in_data'] = array(
                's_cdr_customer_type' => $_POST['customer_type'],
                's_cdr_customer_account' => $_POST['customer_account'],
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'],
                's_cdr_customer_cli' => $_POST['customer_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'],
                's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'],
                's_cdr_customer_ip' => $_POST['customer_ip'],
                's_cdr_call_duration' => $_POST['call_duration'],
                's_time_range' => $_POST['time_range']
            );

            $search_data = array(
                's_cdr_customer_type' => $_SESSION['search_cdr_in_data']['s_cdr_customer_type'],
                's_cdr_customer_account' => $_SESSION['search_cdr_in_data']['s_cdr_customer_account'],
                's_cdr_dialed_no' => $_SESSION['search_cdr_in_data']['s_cdr_dialed_no'],
                's_cdr_carrier_dst_no' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_dst_no'],
                's_cdr_customer_cli' => $_SESSION['search_cdr_in_data']['s_cdr_customer_cli'],
                's_cdr_carrier_cli' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_cli'],
                's_cdr_carrier' => $_SESSION['search_cdr_in_data']['s_cdr_carrier'],
                's_cdr_carrier_ip' => $_SESSION['search_cdr_in_data']['s_cdr_carrier_ip'],
                's_cdr_customer_ip' => $_SESSION['search_cdr_in_data']['s_cdr_customer_ip'],
                's_cdr_call_duration' => $_SESSION['search_cdr_in_data']['s_cdr_call_duration'],
                's_time_range' => $_SESSION['search_cdr_in_data']['s_time_range']
            );
            //echo '<pre>';print_r($search_data);	echo '</pre>';				
            $is_report_searched = true;
            $response = $this->report_mod->api_analytics_cdr_in($search_data);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
        } elseif (isset($_POST['search_action'])) {//coming from reset
            $_SESSION['search_cdr_in_data'] = array('s_cdr_customer_type' => '',
                's_cdr_customer_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_customer_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_customer_ip' => '',
                's_cdr_call_duration' => '',
                's_time_range' => ''
            );
        } elseif (!isset($_SESSION['search_cdr_in_data'])) {//default data for view seach
            $_SESSION['search_cdr_in_data'] = array('s_cdr_customer_type' => '',
                's_cdr_customer_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_customer_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_customer_ip' => '',
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
        $data['logged_customer_type'] = $logged_customer_type;
        $data['get_logged_account_level'] = $get_logged_account_level;
        $data['all_field_array'] = $all_field_array;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/calls_connected_in', $data);
        $this->load->view('basic/footer', $data);
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
            $_SESSION['search_call_data']['s_g_user'] = (isset($_POST['g_user']) ? 'Y' : 'N');
            $_SESSION['search_call_data']['s_cdr_customer_company_name'] = $_POST['customer_company_name'];
        } else {
            $_SESSION['search_call_data']['s_yearmonth'] = isset($_SESSION['search_call_data']['s_yearmonth']) ? $_SESSION['search_call_data']['s_yearmonth'] : date("Y-m");

            $_SESSION['search_call_data']['s_account_id'] = isset($_SESSION['search_call_data']['s_account_id']) ? $_SESSION['search_call_data']['s_account_id'] : '';

            if ($account_id_temp != '')
                $_SESSION['search_call_data']['s_account_id'] = param_decrypt($account_id_temp);
        }
        $search_data = array(
            'customer_account_id' => $_SESSION['search_call_data']['s_account_id'],
            'action_month' => $_SESSION['search_call_data']['s_yearmonth'],
            'groupby_account' => $_SESSION['search_call_data']['s_g_user'],
            'customer_company_name' => $_SESSION['search_call_data']['s_cdr_customer_company_name']
        );



        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('RESELLER')))//,'CUSTOMER'
            $search_data['parent_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $search_data['customer_account_id'] = $logged_account_id;
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

    public function FailCalls($arg1 = '', $format = '') {
        $this->load->model('report_mod');
        $data['page_name'] = "report_failed";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $logged_customer_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();
        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_failed_data'] = array(
                's_cdr_customer_type' => $_POST['customer_type'],
                's_cdr_customer_account' => $_POST['customer_account'],
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'],
                's_cdr_customer_cli' => $_POST['customer_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'],
                's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'],
                's_cdr_customer_ip' => $_POST['customer_ip'],
                's_cdr_sip_code' => $_POST['sip_code'],
                's_cdr_Q850CODE' => $_POST['Q850CODE'],
                's_cdr_fserrorcode' => $_POST['fs_errorcode'],
                's_time_range' => $_POST['time_range'],
                's_no_of_records' => $_POST['no_of_rows'],
                's_cdr_cdr_type' => $_POST['cdr_type'],
                's_cdr_customer_company_name' => $_POST['customer_company_name']
            );
        } elseif ($arg1 == 'export') {
            
        } elseif (!isset($_SESSION['search_failed_data'])) {
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION['search_failed_data'] = array('s_cdr_customer_type' => '',
                's_cdr_customer_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_customer_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_customer_ip' => '',
                's_cdr_sip_code' => '',
                's_cdr_Q850CODE' => '',
                's_cdr_fserrorcode' => '',
                's_time_range' => $time_range,
                's_no_of_records' => RECORDS_PER_PAGE,
                's_cdr_customer_company_name' => '',
                's_cdr_cdr_type' => '',
            );
        }

        $search_data = array(
            's_cdr_customer_type' => $_SESSION['search_failed_data']['s_cdr_customer_type'],
            's_cdr_customer_account' => $_SESSION['search_failed_data']['s_cdr_customer_account'],
            's_cdr_dialed_no' => $_SESSION['search_failed_data']['s_cdr_dialed_no'],
            's_cdr_carrier_dst_no' => $_SESSION['search_failed_data']['s_cdr_carrier_dst_no'],
            's_cdr_customer_cli' => $_SESSION['search_failed_data']['s_cdr_customer_cli'],
            's_cdr_carrier_cli' => $_SESSION['search_failed_data']['s_cdr_carrier_cli'],
            's_cdr_carrier' => $_SESSION['search_failed_data']['s_cdr_carrier'],
            's_cdr_carrier_ip' => $_SESSION['search_failed_data']['s_cdr_carrier_ip'],
            's_cdr_customer_ip' => $_SESSION['search_failed_data']['s_cdr_customer_ip'],
            's_cdr_sip_code' => $_SESSION['search_failed_data']['s_cdr_sip_code'],
            's_cdr_Q850CODE' => $_SESSION['search_failed_data']['s_cdr_Q850CODE'],
            's_cdr_fserrorcode' => $_SESSION['search_failed_data']['s_cdr_fserrorcode'],
            's_time_range' => $_SESSION['search_failed_data']['s_time_range'],
            's_cdr_customer_company_name' => $_SESSION['search_failed_data']['s_cdr_customer_company_name'],
            's_cdr_cdr_type' => $_SESSION['search_cdr_data']['s_cdr_cdr_type']
        );


        if (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;

        if (check_logged_account_type(array('CUSTOMER'))) {
            $search_data['s_cdr_customer_account'] = $logged_account_id;
            $search_data['s_cdr_customer_type'] = 'CUSTOMER';
            $search_data['s_cdr_customer_type_login'] = 'CUSTOMER';
        }
        /////////////////determine which fields to display///////////////////////////
        $all_field_array = array(
            'Account' => 'Account'
            , 'cdr_type' => 'CDR-Type'
            , 'SRC-DST' => 'SRC-DST'
            , 'SRC-CLI' => 'SRC-CLI'
            , 'Start Time' => 'Start Time'
            , 'End Time' => 'End Time'
            , 'SIPCODE' => 'SIP CODE'
            , 'FS-Cause' => 'FS-Cause'
            , 'SRC-IP' => 'SRC-IP'
            , 'Carrier' => 'Carrier'
            , 'Q850CODE' => 'Q850 CODE'
            , 'User-Tariff' => 'User-Tariff'
            , 'Prefix' => 'Prefix'
            , 'Destination' => 'Destination'
            , 'Routing' => 'Routing'
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
            , 'Incoming-Codecs' => 'Incoming-Codecs'
            , 'Outgoing-Codecs' => 'Outgoing-Codecs'
            , "Call's-Codec" => "Call's-Codec"
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

            $response = $this->report_mod->FaildCalls($search_data, $per_page, $segment); //???
            $listing_data = $response['result'];
            $listing_count = $response['total'];


            $search_array = array();
            if ($_SESSION['search_failed_data']['s_cdr_customer_type'] != '') {
                if ($_SESSION['search_failed_data']['s_cdr_customer_type'] == 'U')
                    $search_array['User Type'] = 'User';
                elseif ($_SESSION['search_failed_data']['s_cdr_customer_type'] == 'R1')
                    $search_array['User Type'] = 'Reseller 1';
                elseif ($_SESSION['search_failed_data']['s_cdr_customer_type'] == 'R2')
                    $search_array['User Type'] = 'Reseller 2';
                elseif ($_SESSION['search_failed_data']['s_cdr_customer_type'] == 'R3')
                    $search_array['User Type'] = 'Reseller 3';
            }

            if ($_SESSION['search_failed_data']['s_cdr_customer_account'] != '')
                $search_array['User Account'] = $_SESSION['search_failed_data']['s_cdr_customer_account'];

            if ($_SESSION['search_failed_data']['s_cdr_dialed_no'] != '')
                $search_array['Dialed No'] = $_SESSION['search_failed_data']['s_cdr_dialed_no'];
            if ($_SESSION['search_failed_data']['s_cdr_carrier_dst_no'] != '')
                $search_array['Carrier DST No'] = $_SESSION['search_failed_data']['s_cdr_carrier_dst_no'];
            if ($_SESSION['search_failed_data']['s_cdr_customer_cli'] != '')
                $search_array['User Cli'] = $_SESSION['search_failed_data']['s_cdr_customer_cli'];
            if ($_SESSION['search_failed_data']['s_cdr_carrier_cli'] != '')
                $search_array['Carrier Cli'] = $_SESSION['search_failed_data']['s_cdr_carrier_cli'];
            if ($_SESSION['search_failed_data']['s_cdr_carrier'] != '')
                $search_array['Carrier'] = $_SESSION['search_failed_data']['s_cdr_carrier'];
            if ($_SESSION['search_failed_data']['s_cdr_carrier_ip'] != '')
                $search_array['Carrier IP'] = $_SESSION['search_failed_data']['s_cdr_carrier_ip'];
            if ($_SESSION['search_failed_data']['s_cdr_customer_ip'] != '')
                $search_array['User IP'] = $_SESSION['search_failed_data']['s_cdr_customer_ip'];
            if ($_SESSION['search_failed_data']['s_cdr_sip_code'] != '')
                $search_array['SIP Code'] = $_SESSION['search_failed_data']['s_cdr_sip_code'];
            if ($_SESSION['search_failed_data']['s_cdr_Q850CODE'] != '')
                $search_array['Q850CODE'] = $_SESSION['search_failed_data']['s_cdr_Q850CODE'];
            if ($_SESSION['search_failed_data']['s_cdr_fserrorcode'] != '')
                $search_array['FS Error-Code'] = $_SESSION['search_failed_data']['s_cdr_fserrorcode'];
            if ($_SESSION['search_failed_data']['s_time_range'] != '')
                $search_array['Time Range'] = $_SESSION['search_failed_data']['s_time_range'];

            if ($_SESSION['search_failed_data']['s_cdr_customer_company_name'] != '')
                $search_array['Company Name'] = $_SESSION['search_failed_data']['s_cdr_customer_company_name'];

            // column titles
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }



            if (isset($listing_count) && $listing_count > 0) {
                foreach ($listing_data as $listing_row) {
                    $export_data_temp = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {


                        if ($field_name == 'Account') {

                            if ($listing_row['customer_company_name'] != '') {

                                $export_data_temp[] = $listing_row['customer_company_name'] . ' ( ' . $listing_row[$field_name] . ' ) ';
                            } else {

                                $export_data_temp[] = $listing_row[$field_name];
                            }
                        } else {

                            $export_data_temp[] = $listing_row[$field_name];
                        }
                    }
                    $export_data[] = $export_data_temp;
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
                $per_page = $_SESSION['search_failed_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;


            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }
            $response = $this->report_mod->FaildCalls($search_data, $per_page, $segment);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $data['total_records'] = $response['all_total'];

            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['all_total'], 'reports/FailCalls', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();
            ////////////////
            //////////fields/////////////



            $data['logged_customer_type'] = $logged_customer_type;
            $data['get_logged_account_level'] = $get_logged_account_level;
            $data['all_field_array'] = $all_field_array;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/FailCalls', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function AnsCalls($account_id_temp = '', $format = '') {
        $arg1 = $account_id_temp;
        $this->load->model('report_mod');
        $data['page_name'] = "report_connected";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $logged_customer_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();
        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_cdr_data'] = array(
                's_cdr_customer_type' => $_POST['customer_type'],
                's_cdr_customer_account' => $_POST['customer_account'],
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'],
                's_cdr_customer_cli' => $_POST['customer_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'],
                's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'],
                's_cdr_customer_ip' => $_POST['customer_ip'],
                's_cdr_call_duration' => $_POST['call_duration'],
                's_time_range' => $_POST['time_range'],
                's_no_of_records' => $_POST['no_of_rows'],
                's_cdr_customer_company_name' => $_POST['customer_company_name'],
                's_cdr_call_duration_range' => $_POST['duration_range'],
                's_cdr_cdr_type' => $_POST['cdr_type'],
            );
        } elseif ($arg1 != 'export' && !isset($_SESSION['search_cdr_data']['s_time_range'])) {
            //default date is todays date
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION['search_cdr_data'] = array('s_cdr_customer_type' => '',
                's_cdr_customer_account' => '',
                's_cdr_dialed_no' => '',
                's_cdr_carrier_dst_no' => '',
                's_cdr_customer_cli' => '',
                's_cdr_carrier_cli' => '',
                's_cdr_carrier' => '',
                's_cdr_carrier_ip' => '',
                's_cdr_customer_ip' => '',
                's_cdr_call_duration' => '',
                's_time_range' => $time_range,
                's_no_of_records' => RECORDS_PER_PAGE,
                's_cdr_customer_company_name' => '',
                's_cdr_cdr_type' => '',
                's_cdr_call_duration_range' => ''
            );
        }

        if ($account_id_temp != '' && $arg1 != 'export' && !is_numeric($account_id_temp)) {
            $account_id_temp = param_decrypt($account_id_temp);
            $_SESSION['search_cdr_data']['s_cdr_customer_account'] = $account_id_temp;
        }

        $search_data = array(
            's_cdr_customer_type' => $_SESSION['search_cdr_data']['s_cdr_customer_type'],
            's_cdr_customer_account' => $_SESSION['search_cdr_data']['s_cdr_customer_account'],
            's_cdr_dialed_no' => $_SESSION['search_cdr_data']['s_cdr_dialed_no'],
            's_cdr_carrier_dst_no' => $_SESSION['search_cdr_data']['s_cdr_carrier_dst_no'],
            's_cdr_customer_cli' => $_SESSION['search_cdr_data']['s_cdr_customer_cli'],
            's_cdr_carrier_cli' => $_SESSION['search_cdr_data']['s_cdr_carrier_cli'],
            's_cdr_carrier' => $_SESSION['search_cdr_data']['s_cdr_carrier'],
            's_cdr_carrier_ip' => $_SESSION['search_cdr_data']['s_cdr_carrier_ip'],
            's_cdr_customer_ip' => $_SESSION['search_cdr_data']['s_cdr_customer_ip'],
            's_cdr_call_duration' => $_SESSION['search_cdr_data']['s_cdr_call_duration'],
            's_time_range' => $_SESSION['search_cdr_data']['s_time_range'],
            's_cdr_customer_company_name' => $_SESSION['search_cdr_data']['s_cdr_customer_company_name'],
            's_cdr_call_duration_range' => $_SESSION['search_cdr_data']['s_cdr_call_duration_range'],
            's_cdr_cdr_type' => $_SESSION['search_cdr_data']['s_cdr_cdr_type'],
        );

        if (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;

        if (check_logged_account_type(array('CUSTOMER'))) {
            $search_data['s_cdr_customer_account'] = $logged_account_id;
            $search_data['s_cdr_customer_type'] = 'CUSTOMER';
            $search_data['s_cdr_customer_type_login'] = 'CUSTOMER';
        }
        ///////////////// Searching ////////////////////

        $all_field_array = array(
            'Account' => 'Account'
            , 'cdr_type' => 'Call-Type'
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
            , 'Incoming-Codecs' => 'Incoming-Codecs'
            , 'Outgoing-Codecs' => 'Outgoing-Codecs'
            , "Call's-Codec" => "Call's-Codec"
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
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $format = param_decrypt($format);
            $per_page = 60000;
            $segment = 0;
            $response = $this->report_mod->ConnectedCalls($search_data, $per_page, $segment);
            $listing_data = $response['result'];
            $listing_count = $response['total'];
            $export_data = array();
            if ($listing_count > 0) {
                //$export_data_temp = array('');
                foreach ($listing_data as $listing_row) {
                    $export_data_temp = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {
                        if ($field_name == 'Account') {
                            if ($listing_row['customer_company_name'] != '') {
                                $export_data_temp[] = $listing_row['customer_company_name'] . ' ( ' . $listing_row[$field_name] . ' ) ';
                            } else {
                                $export_data_temp[] = $listing_row[$field_name];
                            }
                        } else {
                            $export_data_temp[] = $listing_row[$field_name];
                        }
                    }
                    $export_data[] = $export_data_temp;
                }
            }


            //prepare search data
            $search_array = array();
            if ($_SESSION['search_cdr_data']['s_cdr_customer_type'] != '') {
                if ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'U')
                    $search_array['User Type'] = 'User';
                elseif ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'R1')
                    $search_array['User Type'] = 'Reseller 1';
                elseif ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'R2')
                    $search_array['User Type'] = 'Reseller 2';
                elseif ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'R3')
                    $search_array['User Type'] = 'Reseller 3';
            }
            if ($_SESSION['search_cdr_data']['s_cdr_customer_account'] != '')
                $search_array['User Account'] = $_SESSION['search_cdr_data']['s_cdr_customer_account'];
            if ($_SESSION['search_cdr_data']['s_cdr_dialed_no'] != '')
                $search_array['Dialed No'] = $_SESSION['search_cdr_data']['s_cdr_dialed_no'];
            if ($_SESSION['search_cdr_data']['s_cdr_carrier_dst_no'] != '')
                $search_array['Carrier DST No'] = $_SESSION['search_cdr_data']['s_cdr_carrier_dst_no'];
            if ($_SESSION['search_cdr_data']['s_cdr_customer_cli'] != '')
                $search_array['User Cli'] = $_SESSION['search_cdr_data']['s_cdr_customer_cli'];
            if ($_SESSION['search_cdr_data']['s_cdr_carrier_cli'] != '')
                $search_array['Carrier Cli'] = $_SESSION['search_cdr_data']['s_cdr_carrier_cli'];
            if ($_SESSION['search_cdr_data']['s_cdr_carrier'] != '')
                $search_array['Carrier'] = $_SESSION['search_cdr_data']['s_cdr_carrier'];
            if ($_SESSION['search_cdr_data']['s_cdr_carrier_ip'] != '')
                $search_array['Carrier IP'] = $_SESSION['search_cdr_data']['s_cdr_carrier_ip'];
            if ($_SESSION['search_cdr_data']['s_cdr_customer_ip'] != '')
                $search_array['User IP'] = $_SESSION['search_cdr_data']['s_cdr_customer_ip'];
            if ($_SESSION['search_cdr_data']['s_cdr_call_duration'] != '')
                $search_array['Call Duration'] = $_SESSION['search_cdr_data']['s_cdr_call_duration'];
            if ($_SESSION['search_cdr_data']['s_time_range'] != '')
                $search_array['Time Range'] = $_SESSION['search_cdr_data']['s_time_range'];
            if ($_SESSION['search_cdr_data']['s_cdr_customer_company_name'] != '')
                $search_array['Company Name'] = $_SESSION['search_cdr_data']['s_cdr_customer_company_name'];

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



//            print_r($search_data);die;
            $response = $this->report_mod->ConnectedCalls($search_data, $per_page, $segment);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $data['total_records'] = $response['all_total'];


            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['all_total'], 'reports/AnsCalls', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();


            $data['logged_customer_type'] = $logged_customer_type;
            $data['get_logged_account_level'] = $get_logged_account_level;
            $data['all_field_array'] = $all_field_array;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/AnsCalls', $data);
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
        $logged_customer_type = get_logged_account_type();
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
                's_customer_account_id' => $_POST['customer_account_id'],
                's_carrier_carrier_id' => $_POST['carrier_carrier_id'],
                's_group_by' => isset($_POST['group_by']) ? $_POST['group_by'] : 'customer_account_id',
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
                's_customer_account_id' => isset($_SESSION['search_billing_data']['s_customer_account_id']) ? $_SESSION['search_billing_data']['s_customer_account_id'] : '',
                's_carrier_carrier_id' => isset($_SESSION['search_billing_data']['s_carrier_carrier_id']) ? $_SESSION['search_billing_data']['s_carrier_carrier_id'] : '',
                's_group_by' => isset($_SESSION['search_billing_data']['s_group_by']) ? $_SESSION['search_billing_data']['s_group_by'] : 'customer_account_id',
                's_time_range' => $time_range,
                's_year' => $today_array['0'],
                's_month' => $today_array['1'],
                's_day' => $today_array['2'],
            );
        }

        if ($_SESSION['search_billing_data']['s_group_by'] == 'customer_account_id')
            $group_by = 'customer_account_id, carrier_carrier_id';
        elseif ($_SESSION['search_billing_data']['s_group_by'] == 'carrier_carrier_id')
            $group_by = 'carrier_carrier_id, customer_account_id';
        else
            $group_by = 'customer_account_id, carrier_carrier_id';

        $search_data = array(
            'customer_account_id' => $_SESSION['search_billing_data']['s_customer_account_id'],
            'carrier_carrier_id' => $_SESSION['search_billing_data']['s_carrier_carrier_id'],
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
            $data['logged_customer_type'] = $logged_customer_type;
            $data['get_logged_account_level'] = $get_logged_account_level;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/accounting_billing', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function businesHistory($arg1 = '', $format = '') {
        $this->load->model('report_mod');
        $data['page_name'] = "report_daily_usage";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $logged_customer_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();
        $search_data = array();
        //print_r($_POST);
        if (isset($_POST['search_action'])) {
            $_SESSION['search_businesHistory_data'] = array(
                's_cdr_customer_account_id' => $_POST['customer_account_id'],
                's_cdr_company' => $_POST['company'],
                's_cdr_currency' => $_POST['currency'],
                's_cdr_record_date' => $_POST['record_date'],
                's_cdr_g_account_id' => (isset($_POST['g_account_id']) ? 'Y' : 'N'),
                's_cdr_g_rec_date' => (isset($_POST['g_rec_date']) ? 'Y' : 'N'),
                's_cdr_g_rec_month' => (isset($_POST['g_rec_month']) ? 'Y' : 'N'),
                's_no_of_records' => $_POST['no_of_rows']
            );
            //	echo '<pre>';print_r($_POST);	echo '</pre>';		
        } else {
            //default date is todays date
            $today_timestamp = strtotime("yesterday");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_businesHistory_data']['s_cdr_username'] = isset($_SESSION['search_businesHistory_data']['s_cdr_username']) ? $_SESSION['search_businesHistory_data']['s_cdr_username'] : '';
            $_SESSION['search_businesHistory_data']['s_cdr_customer_account_id'] = isset($_SESSION['search_businesHistory_data']['s_cdr_customer_account_id']) ? $_SESSION['search_businesHistory_data']['s_cdr_customer_account_id'] : '';
            $_SESSION['search_businesHistory_data']['s_cdr_company'] = isset($_SESSION['search_businesHistory_data']['s_cdr_company']) ? $_SESSION['search_businesHistory_data']['s_cdr_company'] : '';
            $_SESSION['search_businesHistory_data']['s_cdr_currency'] = isset($_SESSION['search_businesHistory_data']['s_cdr_currency']) ? $_SESSION['search_businesHistory_data']['s_cdr_currency'] : '';
            $_SESSION['search_businesHistory_data']['s_cdr_record_date'] = isset($_SESSION['search_businesHistory_data']['s_cdr_record_date']) ? $_SESSION['search_businesHistory_data']['s_cdr_record_date'] : $time_range;

            $_SESSION['search_businesHistory_data']['s_cdr_g_account_id'] = isset($_SESSION['search_businesHistory_data']['s_cdr_g_account_id']) ? $_SESSION['search_businesHistory_data']['s_cdr_g_account_id'] : 'N';
            $_SESSION['search_businesHistory_data']['s_cdr_g_rec_date'] = isset($_SESSION['search_businesHistory_data']['s_cdr_g_rec_date']) ? $_SESSION['search_businesHistory_data']['s_cdr_g_rec_date'] : 'N';
            $_SESSION['search_businesHistory_data']['s_cdr_g_rec_month'] = isset($_SESSION['search_businesHistory_data']['s_cdr_g_rec_month']) ? $_SESSION['search_businesHistory_data']['s_cdr_g_rec_month'] : 'N';

            $_SESSION['search_businesHistory_data']['s_no_of_records'] = isset($_SESSION['search_businesHistory_data']['s_no_of_records']) ? $_SESSION['search_businesHistory_data']['s_no_of_records'] : RECORDS_PER_PAGE;
        }

        $search_data = array(
            'company_name' => $_SESSION['search_businesHistory_data']['s_cdr_company'],
            'currency_id' => $_SESSION['search_businesHistory_data']['s_cdr_currency'],
            'account_id' => $_SESSION['search_businesHistory_data']['s_cdr_customer_account_id'],
            'record_date' => $_SESSION['search_businesHistory_data']['s_cdr_record_date'],
        );

        if ($_SESSION['search_businesHistory_data']['s_cdr_g_account_id'] == 'Y' && $_SESSION['search_businesHistory_data']['s_cdr_g_rec_date'] == 'Y') {
            
        } else {
            $search_data['g_account_id'] = $_SESSION['search_businesHistory_data']['s_cdr_g_account_id'];
            $search_data['g_rec_date'] = $_SESSION['search_businesHistory_data']['s_cdr_g_rec_date'];
        }
        $search_data['g_rec_month'] = $_SESSION['search_businesHistory_data']['s_cdr_g_rec_month'];

        if (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;


        $all_field_array = array(
            'account_id' => 'Account Id'
            , 'company_name' => 'Company Name'
            , 'record_date' => 'Record Date'
            , 'record_date_month' => 'Record Month'
            , 'currency' => 'Currency'
            , 'tariff_net_cost' => 'Tariff Cost'
            , 'calls_out' => 'Total Call-Out'
            , 'mins_out' => 'Mins-Out'
            , 'customer_cost_out' => 'Customer Cost-Out'
            , 'carrier_cost_out' => 'Carrier Cost-Out'
            , 'calls_in' => 'Total Calls-In'
            , 'mins_in' => 'Total Mins-In'
            , 'customer_cost_in' => 'Customer Cost-In'
            , 'carrier_cost_in' => 'Carrier Cost-In'
            , 'did_setup_rental_customer_cost' => 'Customer DID-Cost'
            , 'did_setup_rental_carrier_cost' => 'Carrier DID-Cost'
            , 'payment' => 'Payment'
            , 'credit_added' => 'Credit Added'
            , 'credit_remove' => 'Credit Remove'
            , 'profit' => 'Profit'
        );


        if ($_SESSION['search_businesHistory_data']['s_cdr_g_account_id'] == 'Y' && $_SESSION['search_businesHistory_data']['s_cdr_g_rec_date'] == 'Y') {
            
        } elseif ($_SESSION['search_businesHistory_data']['s_cdr_g_account_id'] == 'Y') {
            unset($all_field_array['record_date']);
        } elseif ($_SESSION['search_businesHistory_data']['s_cdr_g_rec_date'] == 'Y') {
            unset($all_field_array['account_id']);
            unset($all_field_array['company_name']);
        }

        if ($_SESSION['search_businesHistory_data']['s_cdr_g_rec_month'] == 'Y' && $_SESSION['search_businesHistory_data']['s_cdr_g_account_id'] == 'Y') {
            
        } elseif ($_SESSION['search_businesHistory_data']['s_cdr_g_rec_month'] == 'Y') {
            unset($all_field_array['account_id']);
            unset($all_field_array['company_name']);
            unset($all_field_array['record_date']);
        } else
            unset($all_field_array['record_date_month']);


        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {//die;
            $format = param_decrypt($format);

            $currency = '';
            if ($_SESSION['search_businesHistory_data']['s_cdr_currency'] != '') {
                if ($_SESSION['search_businesHistory_data']['s_cdr_currency'] == '1')
                    $currency = 'USD';
                elseif ($_SESSION['search_businesHistory_data']['s_cdr_currency'] == '2')
                    $currency = 'GBP';
                elseif ($_SESSION['search_businesHistory_data']['s_cdr_currency'] == '3')
                    $currency = 'EUR';
                elseif ($_SESSION['search_businesHistory_data']['s_cdr_currency'] == '4')
                    $currency = 'INR';
            }

            $search_array = array();
            if ($_SESSION['search_businesHistory_data']['s_cdr_username'] != '')
                $search_array['User Name'] = $_SESSION['search_businesHistory_data']['s_cdr_username'];
            if ($_SESSION['search_businesHistory_data']['s_cdr_customer_account'] != '')
                $search_array['Account Id'] = $_SESSION['search_businesHistory_data']['s_cdr_customer_account'];
            if ($_SESSION['search_businesHistory_data']['s_cdr_company'] != '')
                $search_array['Company Name'] = $_SESSION['search_businesHistory_data']['s_cdr_company'];
            if ($currency != '')
                $search_array['Currency'] = $currency;
            if ($_SESSION['search_businesHistory_data']['s_cdr_record_date'] != '')
                $search_array['Record Date'] = $_SESSION['search_businesHistory_data']['s_cdr_record_date'];


            // column titles
            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }

            $per_page = 1000;
            $segment = 0;


            $response = $this->report_mod->get_businesHistory($search_data, $per_page, $segment);

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

            if (isset($_SESSION['search_businesHistory_data']['s_no_of_records']) && $_SESSION['search_businesHistory_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_businesHistory_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }
            // print_r($search_data);
            $response = $this->report_mod->get_businesHistory($search_data, $per_page, $segment);
            //print_r($response);
            $data['listing_data'] = $response['result'];

            $totalRows = $this->report_mod->total_count;
            $data['total_records'] = $totalRows;
            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($totalRows, 'reports/businesHistory', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();


            $data['logged_customer_type'] = $logged_customer_type;
            $data['get_logged_account_level'] = $get_logged_account_level;

            $data['all_field_array'] = $all_field_array;
            $data['currency_data'] = $this->utils_model->get_currencies();

            $this->load->view('basic/header', $data);
            $this->load->view('reports/businesHistory', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function CarrierUsage($arg1 = '', $format = '') {
        $this->load->model('report_mod');
        $data['page_name'] = "CarrierUsage";

        //check page action permission
        //if(!check_account_permission('report','analytics_carrier')) show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////	
        $logged_customer_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();
        ///////////////// Searching ////////////////////

        $search_data = array();
        //print_r($_POST);
        if (isset($_POST['search_action'])) {
            $_SESSION['search_CarrierUsage_data'] = array(
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


            $_SESSION['search_CarrierUsage_data']['s_carrier_account'] = isset($_SESSION['search_CarrierUsage_data']['s_carrier_account']) ? $_SESSION['search_CarrierUsage_data']['s_carrier_account'] : '';
            $_SESSION['search_CarrierUsage_data']['s_carrier_name'] = isset($_SESSION['search_CarrierUsage_data']['s_carrier_name']) ? $_SESSION['search_CarrierUsage_data']['s_carrier_name'] : '';

            $_SESSION['search_CarrierUsage_data']['s_carrier_currency'] = isset($_SESSION['search_CarrierUsage_data']['s_carrier_currency']) ? $_SESSION['search_CarrierUsage_data']['s_carrier_currency'] : '';
            $_SESSION['search_CarrierUsage_data']['s_calls_date'] = $time_range;

            $_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'] = isset($_SESSION['search_CarrierUsage_data']['carrier_grp_account_id']) ? $_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'] : 'N';
            $_SESSION['search_CarrierUsage_data']['carrier_grp_dest'] = isset($_SESSION['search_CarrierUsage_data']['carrier_grp_dest']) ? $_SESSION['search_CarrierUsage_data']['carrier_grp_dest'] : 'N';
            $_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'] = isset($_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date']) ? $_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'] : 'N';

            $_SESSION['search_CarrierUsage_data']['s_no_of_records'] = isset($_SESSION['search_CarrierUsage_data']['s_no_of_records']) ? $_SESSION['search_CarrierUsage_data']['s_no_of_records'] : RECORDS_PER_PAGE;
        }

        $search_data = array(
            'carrier_account' => $_SESSION['search_CarrierUsage_data']['s_carrier_account'],
            'carrier_name' => $_SESSION['search_CarrierUsage_data']['s_carrier_name'],
            'carrier_currency_id' => $_SESSION['search_CarrierUsage_data']['s_carrier_currency'],
            'calls_date' => $_SESSION['search_CarrierUsage_data']['s_calls_date']
        );

        if (($_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'] == 'Y') && ($_SESSION['search_CarrierUsage_data']['carrier_grp_dest'] == 'Y') && ($_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'] == 'Y')) {
            $search_data['g_account_id'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'];
            $search_data['grp_destination'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_dest'];
            $search_data['grp_calls_date'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'];
        } elseif (($_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'] == 'Y') && ($_SESSION['search_CarrierUsage_data']['carrier_grp_dest'] == 'Y')) {
            $search_data['g_account_id'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'];
            $search_data['grp_destination'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_dest'];
        } elseif (($_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'] == 'Y') && ($_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'] == 'Y')) {
            $search_data['g_account_id'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'];
            $search_data['grp_calls_date'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'];
        } elseif (($_SESSION['search_CarrierUsage_data']['carrier_grp_dest'] == 'Y') && ($_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'] == 'Y')) {
            $search_data['grp_destination'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_dest'];
            $search_data['grp_calls_date'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'];
        } elseif ($_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'] == 'Y') {
            $search_data['g_account_id'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'];
        } elseif ($_SESSION['search_CarrierUsage_data']['carrier_grp_dest'] == 'Y') {
            $search_data['grp_destination'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_dest'];
        } else {
            $search_data['grp_calls_date'] = $_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'];
        }

        if (check_logged_account_type(array('RESELLER')))
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

        if ($_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'] == 'Y' && $_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'] == 'Y' && $_SESSION['search_CarrierUsage_data']['carrier_grp_dest'] == 'Y') {
            unset($all_field_array['calls_date']);
        }

        if ($_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'] == 'Y' && $_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'] == 'Y') {
            unset($all_field_array['calls_date']);
        } elseif ($_SESSION['search_CarrierUsage_data']['carrier_grp_account_id'] == 'Y') {
            unset($all_field_array['calls_date']);
            unset($all_field_array['prefix']);
            unset($all_field_array['code515']);
        } elseif ($_SESSION['search_CarrierUsage_data']['carrier_grp_dest'] == 'Y') {
            unset($all_field_array['carrier_account']);
            unset($all_field_array['prefix']);
            unset($all_field_array['calls_date']);
        } elseif ($_SESSION['search_CarrierUsage_data']['carrier_grp_calls_date'] == 'Y') {
            unset($all_field_array['carrier_account']);
            unset($all_field_array['prefix']);
        } else {
            
        }


        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {//die;
            $format = param_decrypt($format);

            $currency = '';
            if ($_SESSION['search_CarrierUsage_data']['s_carrier_currency'] != '') {
                if ($_SESSION['search_CarrierUsage_data']['s_carrier_currency'] == '1')
                    $currency = 'USD';
                elseif ($_SESSION['search_CarrierUsage_data']['s_carrier_currency'] == '2')
                    $currency = 'GBP';
                elseif ($_SESSION['search_CarrierUsage_data']['s_carrier_currency'] == '3')
                    $currency = 'EUR';
                elseif ($_SESSION['search_CarrierUsage_data']['s_carrier_currency'] == '4')
                    $currency = 'INR';
            }

            $search_array = array();
            if ($_SESSION['search_CarrierUsage_data']['s_carrier_account'] != '')
                $search_array['Carrier Account'] = $_SESSION['search_CarrierUsage_data']['s_carrier_account'];
            if ($_SESSION['search_CarrierUsage_data']['s_carrier_name'] != '')
                $search_array['Carrier Name'] = $_SESSION['search_CarrierUsage_data']['s_carrier_name'];

            if ($currency != '')
                $search_array['Currency'] = $currency;
            if ($_SESSION['search_CarrierUsage_data']['s_calls_date'] != '')
                $search_array['Calls Date'] = $_SESSION['search_CarrierUsage_data']['s_calls_date'];

            // column titles
            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }

            $per_page = 40000;
            $segment = 0;
            $response = $this->report_mod->CarrierUsage($search_data, $per_page, $segment);
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

            $file_name = 'CarrierUsage';
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
            if (isset($_SESSION['search_CarrierUsage_data']['s_no_of_records']) && $_SESSION['search_CarrierUsage_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_CarrierUsage_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;


            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }

            $response = $this->report_mod->CarrierUsage($search_data, $per_page, $segment);

            $data['listing_data'] = $response['result'];

            $totalRows = $this->report_mod->total_count;
            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($totalRows, 'reports/CarrierUsage', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();


            $data['logged_customer_type'] = $logged_customer_type;
            $data['get_logged_account_level'] = $get_logged_account_level;

            $data['all_field_array'] = $all_field_array;
            $data['currency_data'] = $this->utils_model->get_currencies();

            $data['total_records'] = $totalRows;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/CarrierUsage', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function topup() {
        //$this->output->enable_profiler(true);	
        $page_name = "report_topup";
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
            $_SESSION['search_topup_day_data']['s_time_range'] = $_POST['time_range'];
            $_SESSION['search_topup_day_data']['s_account_id'] = $_POST['account_id'];
            $_SESSION['search_topup_day_data']['s_account_manager'] = $_POST['account_manager'];
        } elseif (!isset($_SESSION['search_topup_day_data']['s_time_range'])) {
            //default date is todays date
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);

            $lastday_stamp = $today_timestamp - 30 * 24 * 60 * 60;
            $lastday = date('Y-m-d', $lastday_stamp);

            $time_range = $lastday . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_topup_day_data']['s_time_range'] = isset($_SESSION['search_topup_day_data']['s_time_range']) ? $_SESSION['search_topup_day_data']['s_time_range'] : $time_range;

            $_SESSION['search_topup_day_data']['s_account_id'] = isset($_SESSION['search_topup_day_data']['s_account_id']) ? $_SESSION['search_topup_day_data']['s_account_id'] : '';
            $_SESSION['search_topup_day_data']['s_account_manager'] = isset($_SESSION['search_topup_day_data']['s_account_manager']) ? $_SESSION['search_topup_day_data']['s_account_manager'] : '';
        }
        $search_data = array('account_id' => $_SESSION['search_topup_day_data']['s_account_id'],
            'time_range' => $_SESSION['search_topup_day_data']['s_time_range']);

        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER'))) {
            $search_data['sales_manager'] = $logged_account_id;
            $search_data['am_under_sm'] = $_SESSION['search_topup_day_data']['s_account_manager'];
        } elseif (check_logged_account_type(array('RESELLER')))
            $search_data['parent_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $search_data['account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('ADMIN'))) {
            
        } elseif (check_logged_account_type(array('CREDITCONTROL'))) {
            
        } else {
            show_404('403');
        }

        /////
        if (check_logged_account_type(array('SALESMANAGER'))) {
            $ac_search_data = array();
            $ac_search_data['sales_manager'] = $logged_account_id;
            $ac_mngrs_data = $this->member_mod->get_data('', '', '', $ac_search_data);
            $data['ac_mngrs_data'] = $ac_mngrs_data;
        }
        /////

        $data['currency_options'] = $this->utils_model->get_currencies();
        $report_data = $this->report_mod->topup_daily($search_data);

        $data['topup_data'] = $report_data;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/topup', $data);
        $this->load->view('basic/footer', $data);
    }

    function topup_monthly() {
        $page_name = "report_topup_monthly";
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
            $_SESSION['search_topup_monthly_data']['s_account_id'] = $_POST['account_id'];
            $_SESSION['search_topup_monthly_data']['s_account_manager'] = $_POST['account_manager'];
        } else {
            $_SESSION['search_topup_monthly_data']['s_account_id'] = isset($_SESSION['search_topup_monthly_data']['s_account_id']) ? $_SESSION['search_topup_monthly_data']['s_account_id'] : '';
            $_SESSION['search_topup_monthly_data']['s_account_manager'] = isset($_SESSION['search_topup_monthly_data']['s_account_manager']) ? $_SESSION['search_topup_monthly_data']['s_account_manager'] : '';
        }

        $start_timestamp = strtotime("- 11 month");
        $start_day = date('Y-m', $start_timestamp);
        $start_day = $start_day . '-01 00:00:00';

        $end_timestamp = strtotime("last day of this month");
        $end_day = date('Y-m-d', $end_timestamp);
        $end_day = $end_day . ' 23:59:59';

        $time_range = $start_day . ' - ' . $end_day;

        $_SESSION['search_topup_monthly_data']['s_time_range'] = $time_range;


        $search_data = array('account_id' => $_SESSION['search_topup_monthly_data']['s_account_id'],
            'time_range' => $_SESSION['search_topup_monthly_data']['s_time_range']);

        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $search_data['account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER'))) {
            $search_data['sales_manager'] = $logged_account_id;

            $search_data['am_under_sm'] = $_SESSION['search_topup_monthly_data']['s_account_manager'];
        } elseif (check_logged_account_type(array('RESELLER')))
            $search_data['parent_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $search_data['account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('ADMIN'))) {
            
        } elseif (check_logged_account_type(array('CREDITCONTROL'))) {
            
        } else {
            show_404('403');
        }
        /////
        if (check_logged_account_type(array('SALESMANAGER'))) {
            $ac_search_data = array();
            $ac_search_data['sales_manager'] = $logged_account_id;
            $ac_mngrs_data = $this->member_mod->get_data('', '', '', $ac_search_data);
            $data['ac_mngrs_data'] = $ac_mngrs_data;
            //echo '<pre>'; print_r($ac_mngrs_data);echo '</pre>';
        }
        /////
        //	echo '<pre>'; print_r($search_data);echo '</pre>';
        $data['currency_options'] = $this->utils_model->get_currencies();
        $report_data = $this->report_mod->topup_monthly($search_data);

        $data['topup_data'] = $report_data;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/topup_monthly', $data);
        $this->load->view('basic/footer', $data);
    }

    function CRecharge() {
        $page_name = "CRecharge";
        $data['page_name'] = $page_name;
        $this->load->model('customer_mod');
        $this->load->model('reseller_mod');
        $this->load->model('report_mod');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $logged_account_id = get_logged_account_id();

        if (check_logged_account_type(array('RESELLER', 'CUSTOMER')))
            $customer_search_data['parent_account_id'] = get_logged_account_id();
        elseif (check_logged_account_type(array('ADMIN', 'SUBADMIN')))
            $customer_search_data['parent_account_id'] = '';
        elseif (check_logged_account_type(array('ACCOUNTS'))) {
            $customer_search_data['parent_account_id'] = '';
        } else {
            die;
        }

        if (isset($_POST['search_action'])) {
            $_SESSION['search_topup_cust_data']['s_cdr_record_date'] = $_POST['time_range'];
            $_SESSION['search_topup_cust_data']['s_account_id'] = $_POST['account_id'];
        } elseif (!isset($_SESSION['search_topup_cust_data']['s_cdr_record_date'])) {
            //default date is todays date
            $today_timestamp = strtotime("yesterday");
            $today = date('Y-m-d', $today_timestamp);

            $lastday_stamp = $today_timestamp - 30 * 24 * 60 * 60;
            $lastday = date('Y-m-d', $lastday_stamp);

            $time_range = $lastday . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_topup_cust_data']['s_cdr_record_date'] = isset($_SESSION['search_topup_cust_data']['s_cdr_record_date']) ? $_SESSION['search_topup_cust_data']['s_cdr_record_date'] : $time_range;

            $_SESSION['search_topup_cust_data']['s_account_id'] = isset($_SESSION['search_topup_cust_data']['s_account_id']) ? $_SESSION['search_topup_cust_data']['s_account_id'] : '';
        }


        $customer_search_data['account_id'] = $_SESSION['search_topup_cust_data']['s_account_id'];

        $endusers_data = $this->customer_mod->get_data('', '', '', $customer_search_data, array());
        $data['endusers_data'] = $endusers_data['result'];

        //////fetch reseller data////////
        $reseller_search_data = array();
        $reseller_search_data['account_id'] = $_SESSION['search_topup_cust_data']['s_account_id'];

        if (check_logged_account_type(array('RESELLER')))
            $reseller_search_data['parent_account_id'] = get_logged_account_id();
        else
            $reseller_search_data['account_level'] = '1';

        $resellers_data = $this->reseller_mod->get_data('', '', '', $reseller_search_data, array());
        $data['resellers_data'] = $resellers_data['result'];


        ///////////////////report data//////////////
        $report_search_data = array('account_id' => $_SESSION['search_topup_cust_data']['s_account_id'],
            'time_range' => $_SESSION['search_topup_cust_data']['s_cdr_record_date'],
        );

        if (check_logged_account_type(array('RESELLER')))
            $report_search_data['parent_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $report_search_data['account_id'] = $logged_account_id;
        else {
            
        }

        $report_data = $this->report_mod->CRecharge($report_search_data);
//        echo '<pre>';
//        print_r($report_data);
//        echo '</pre>';
        $sales_data = array();
        if (count($report_data['result']) > 0) {
            foreach ($report_data['result'] as $account_id => $report_data_array) {
                $topup = 0;
                if (isset($report_data_array['ADDBALANCE']))
                    $topup += $report_data_array['ADDBALANCE'];

                if (isset($report_data_array['REMOVEBALANCE']))
                    $topup -= $report_data_array['REMOVEBALANCE'];

                $sales_data[$account_id] = array(
                    'cost' => $topup,
                    'company_name' => $report_data_array['company_name']
                );
            }
        }
        //sort sales data by sales amount
        arsort($sales_data);

        $data['sales_data'] = $sales_data;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/CRecharge', $data);
        $this->load->view('basic/footer', $data);
    }

    function daily_sales_monthly() {
        $page_name = "report_daily_sales_monthly";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');

        $logged_account_id = get_logged_account_id();
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['search_action'])) {
            $_SESSION['search_sales_monthly_data']['s_account_id'] = $_POST['account_id'];
        } else {
            $_SESSION['search_sales_monthly_data']['s_account_id'] = isset($_SESSION['search_sales_monthly_data']['s_account_id']) ? $_SESSION['search_sales_monthly_data']['s_account_id'] : '';
        }

        $start_timestamp = strtotime("- 11 month");
        $start_day = date('Y-m', $start_timestamp);
        $start_day = $start_day . '-01 00:00:00';

        $end_timestamp = strtotime("last day of this month");
        $end_day = date('Y-m-d', $end_timestamp);
        $end_day = $end_day . ' 23:59:59';

        $time_range = $start_day . ' - ' . $end_day;

        $_SESSION['search_sales_monthly_data']['s_cdr_record_date'] = $time_range;

        $search_data = array('account_id' => $_SESSION['search_sales_monthly_data']['s_account_id'],
            'record_date' => $_SESSION['search_sales_monthly_data']['s_cdr_record_date'],
            'g_rec_month' => 'Y');

        if (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $search_data['account_id'] = $logged_account_id;
        else {
            $search_data['s_parent_account_id'] = '';
        }



        $data['currency_options'] = $this->utils_model->get_currencies();
        $report_data = $this->report_mod->get_businesHistory($search_data, '', '');

        //echo '<pre>'; print_r($search_data);print_r($report_data);echo '</pre>';
        $sales_data = array();
        if (count($report_data['result']) > 0) {
            foreach ($report_data['result'] as $report_data_array) {
                $record_date_month = $report_data_array['record_date_month'];
                $currency_id = $report_data_array['currency_id'];

                $sales_data[$currency_id][$record_date_month] = array(
                    'cost' => $report_data_array['usercost_out'] + $report_data_array['usercost_in']
                );
            }
        }

        $data['sales_data'] = $sales_data;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/sales_monthly', $data);
        $this->load->view('basic/footer', $data);
    }

    function daily_sales() {
        $page_name = "report_daily_sales";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');
        $logged_account_id = get_logged_account_id();
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_sales_day_data']['s_cdr_record_date'] = $_POST['time_range'];
            $_SESSION['search_sales_day_data']['s_account_id'] = $_POST['account_id'];
            $_SESSION['search_sales_day_data']['s_account_manager'] = $_POST['account_manager'];
        } elseif (!isset($_SESSION['search_sales_day_data']['s_cdr_record_date'])) {
            $today_timestamp = strtotime("yesterday");
            $today = date('Y-m-d', $today_timestamp);

            $lastday_stamp = $today_timestamp - 30 * 24 * 60 * 60;
            $lastday = date('Y-m-d', $lastday_stamp);

            $time_range = $lastday . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_sales_day_data']['s_cdr_record_date'] = isset($_SESSION['search_sales_day_data']['s_cdr_record_date']) ? $_SESSION['search_sales_day_data']['s_cdr_record_date'] : $time_range;

            $_SESSION['search_sales_day_data']['s_account_id'] = isset($_SESSION['search_sales_day_data']['s_account_id']) ? $_SESSION['search_sales_day_data']['s_account_id'] : '';
            $_SESSION['search_sales_day_data']['s_account_manager'] = isset($_SESSION['search_sales_day_data']['s_account_manager']) ? $_SESSION['search_sales_day_data']['s_account_manager'] : '';
        }
        /* $search_data = array('account_id'=>$_SESSION['search_sales_day_data']['s_account_id'], 
          'time_range'=>$_SESSION['search_sales_day_data']['s_time_range']); */
        $search_data = array('account_id' => $_SESSION['search_sales_day_data']['s_account_id'],
            'record_date' => $_SESSION['search_sales_day_data']['s_cdr_record_date'],
            'g_rec_date' => 'Y');

        if (check_logged_account_type(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $search_data['account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('ADMIN'))) {
            
        } elseif (check_logged_account_type(array('CREDITCONTROL'))) {
            
        } else {
            show_404('403');
        }



        $data['currency_options'] = $this->utils_model->get_currencies();
        $report_data = $this->report_mod->get_businesHistory($search_data, '', '');

        //echo '<pre>'; print_r($search_data);print_r($report_data);echo '</pre>';
        $sales_data = array();
        if (count($report_data['result']) > 0) {
            foreach ($report_data['result'] as $report_data_array) {
                $record_date = $report_data_array['record_date'];
                $currency_id = $report_data_array['currency_id'];

                $sales_data[$currency_id][$record_date] = array(
                    'cost' => $report_data_array['usercost_out'] + $report_data_array['usercost_in']
                );
            }
        }
        //echo '<pre>'; print_r($search_data);print_r($report_data);echo '</pre>';
        $data['sales_data'] = $sales_data;

        $this->load->view('basic/header', $data);
        $this->load->view('reports/sales_daily', $data);
        $this->load->view('basic/footer', $data);
    }

    function customer_sales() {
        $page_name = "customer_sales_summery";
        $data['page_name'] = $page_name;
        $this->load->model('endcustomer_mod');
        $this->load->model('reseller_mod');
        $this->load->model('report_mod');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $logged_account_id = get_logged_account_id();

        ////////////get customer data///////////////////
        if (check_logged_account_type(array('RESELLER', 'CUSTOMER')))
            $customer_search_data['parent_account_id'] = get_logged_account_id();
        elseif (check_logged_account_type(array('ADMIN', 'SUBADMIN')))
            $customer_search_data['parent_account_id'] = '';
        elseif (check_logged_account_type(array('ACCOUNTS'))) {
            $customer_search_data['parent_account_id'] = '';
        } else {
            die;
        }



        if (isset($_POST['search_action'])) {// coming from search button								
            $_SESSION['search_sales_cust_data']['s_cdr_record_date'] = $_POST['time_range'];
            $_SESSION['search_sales_cust_data']['s_account_id'] = $_POST['account_id'];
        } elseif (!isset($_SESSION['search_sales_cust_data']['s_cdr_record_date'])) {
            //default date is todays date
            $today_timestamp = strtotime("yesterday");
            $today = date('Y-m-d', $today_timestamp);

            $lastday_stamp = $today_timestamp - 30 * 24 * 60 * 60;
            $lastday = date('Y-m-d', $lastday_stamp);

            $time_range = $lastday . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_sales_cust_data']['s_cdr_record_date'] = isset($_SESSION['search_sales_cust_data']['s_cdr_record_date']) ? $_SESSION['search_sales_cust_data']['s_cdr_record_date'] : $time_range;

            $_SESSION['search_sales_cust_data']['s_account_id'] = isset($_SESSION['search_sales_cust_data']['s_account_id']) ? $_SESSION['search_sales_cust_data']['s_account_id'] : '';
        }


        $customer_search_data['account_id'] = $_SESSION['search_sales_cust_data']['s_account_id'];

        $endusers_data = $this->endcustomer_mod->get_data('', '', '', $customer_search_data, array());
        $data['endusers_data'] = $endusers_data['result'];

        //////fetch reseller data////////
        $reseller_search_data = array();
        $reseller_search_data['account_id'] = $_SESSION['search_sales_cust_data']['s_account_id'];

        if (check_logged_account_type(array('RESELLER')))
            $reseller_search_data['parent_account_id'] = get_logged_account_id();
        else
            $reseller_search_data['customer_level'] = '1';

        $resellers_data = $this->reseller_mod->get_data('', '', '', $reseller_search_data, array());
        $data['resellers_data'] = $resellers_data['result'];


        ///////////////////report data//////////////
        $report_search_data = array('account_id' => $_SESSION['search_sales_cust_data']['s_account_id'],
            'record_date' => $_SESSION['search_sales_cust_data']['s_cdr_record_date'],
            'g_account_id' => 'Y');
        if (check_logged_account_type(array('RESELLER')))
            $report_search_data['s_parent_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $report_search_data['account_id'] = $logged_account_id;
        else {
            $report_search_data['s_parent_account_id'] = '';
        }
        $report_data = $this->report_mod->get_businesHistory($report_search_data, '', '');

        $sales_data = array();
        if (count($report_data['result']) > 0) {
            foreach ($report_data['result'] as $report_data_array) {
                $account_id = $report_data_array['account_id'];
                $currency_id = $report_data_array['currency_id'];
                $total_cost = $report_data_array['usercost_out'] + $report_data_array['usercost_in'];
                $sales_data[$account_id] = array(
                    'company_name' => $report_data_array['company_name'],
                    'cost' => $total_cost
                );
            }
        }
        //sort sales data by sales amount
        arsort($sales_data);
        $data['sales_data'] = $sales_data;

        //echo '<pre>11';print_r($report_search_data);print_r($report_data);echo '11</pre>';//	

        $this->load->view('basic/header', $data);
        $this->load->view('reports/customer_sales', $data);
        $this->load->view('basic/footer', $data);
    }

    function supplier_detail_audit($arg1 = '', $format = '') {
        $page_name = "report_supplier_detail_audit";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');

        $account_id = get_logged_account_id();
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (!check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC'))) {
            //not permitted
            show_404('403');
        }
        ////////////////////////////////////////////////


        if (isset($_POST['search_action'])) {// coming from search button							
            $_SESSION['search_c_reconcilliation'] = array('s_record_date' => $_POST['record_date']);
            $_SESSION['search_c_reconcilliation']['s_service_type'] = $_POST['service_type'];
            $_SESSION['search_c_reconcilliation']['s_supplier'] = $_POST['supplier'];
            $_SESSION['search_c_reconcilliation']['s_currency_id'] = $_POST['currency_id'];
            $_SESSION['search_c_reconcilliation']['s_no_of_records'] = $_POST['no_of_rows'];
        } else {
            $today_timestamp = strtotime("yesterday");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_c_reconcilliation']['s_record_date'] = isset($_SESSION['search_c_reconcilliation']['s_record_date']) ? $_SESSION['search_c_reconcilliation']['s_record_date'] : $time_range;
            $_SESSION['search_c_reconcilliation']['s_service_type'] = isset($_SESSION['search_c_reconcilliation']['s_service_type']) ? $_SESSION['search_c_reconcilliation']['s_service_type'] : '';

            $_SESSION['search_c_reconcilliation']['s_supplier'] = isset($_SESSION['search_c_reconcilliation']['s_supplier']) ? $_SESSION['search_c_reconcilliation']['s_supplier'] : '';
            $_SESSION['search_c_reconcilliation']['s_currency_id'] = isset($_SESSION['search_c_reconcilliation']['s_currency_id']) ? $_SESSION['search_c_reconcilliation']['s_currency_id'] : '';

            $_SESSION['search_c_reconcilliation']['s_no_of_records'] = isset($_SESSION['search_c_reconcilliation']['s_no_of_records']) ? $_SESSION['search_c_reconcilliation']['s_no_of_records'] : RECORDS_PER_PAGE;
        }

        $search_data = array(
            'record_date' => $_SESSION['search_c_reconcilliation']['s_record_date'],
            'service_type' => $_SESSION['search_c_reconcilliation']['s_service_type'],
            'supplier' => $_SESSION['search_c_reconcilliation']['s_supplier'],
            'currency_id' => $_SESSION['search_c_reconcilliation']['s_currency_id'],
        );


        $currency_data = $this->utils_model->get_currencies();
        ////////////export////////////
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $format = param_decrypt($format);

            $per_page = 60000;
            $segment = 0;
            $report_data = $this->report_mod->supplier_detail_audit($search_data, $per_page, $segment);

            // column titles
            $export_header = array(
                'Supplier',
                'Supplier Reference',
                'Service Type',
                'Status',
                'Our Reference',
                'Start Date',
                'End Date',
                'Quantity',
                'One-Off Charges',
                'Monthly Charges',
                'Usage Charge',
                'Currency'
            );


            $currency_array = array();
            for ($i = 0; $i < count($currency_data); $i++) {
                $currency_id = $currency_data[$i]['currency_id'];
                $currency_array[$currency_id] = $currency_data[$i]['name'];
            }

            if (count($report_data['result']) > 0) {
                foreach ($report_data['result'] as $supplier_data) {
                    $display_start_date = date(DATE_FORMAT_1, strtotime($supplier_data['start_date']));
                    $display_end_date = date(DATE_FORMAT_1, strtotime($supplier_data['end_date']));


                    $currency_id = $supplier_data['currency_id'];
                    $currency_name = '';
                    if (isset($currency_array[$currency_id]))
                        $currency_name = $currency_array[$currency_id];

                    $one_Off_charge = round($supplier_data['one_Off_charge'], 2);
                    $monthly_charge = round($supplier_data['monthly_charge'], 2);
                    $usage_charge = round($supplier_data['usage_charge'], 2);

                    $export_data[] = array(
                        $supplier_data['supplier_name'],
                        $supplier_data['supplier_reference'],
                        $supplier_data['service_type'],
                        $supplier_data['service_status'],
                        $supplier_data['system_reference'],
                        $display_start_date,
                        $display_end_date,
                        $supplier_data['quantity'],
                        $one_Off_charge,
                        $monthly_charge,
                        $usage_charge,
                        $currency_name
                    );
                }
            } else
                $export_data = array('');

            //prepare search data
            $search_array = array();

            if ($_SESSION['search_c_reconcilliation']['s_record_date'] != '')
                $search_array['Record Date'] = $_SESSION['search_c_reconcilliation']['s_record_date'];
            if ($_SESSION['search_c_reconcilliation']['s_service_type'] != '')
                $search_array['Service Type'] = $_SESSION['search_c_reconcilliation']['s_service_type'];
            if ($_SESSION['search_c_reconcilliation']['s_supplier'] != '')
                $search_array['Supplier'] = $_SESSION['search_c_reconcilliation']['s_supplier'];
            if ($_SESSION['search_c_reconcilliation']['s_currency_id'] != '') {
                $s_currency_id = $_SESSION['search_c_reconcilliation']['s_currency_id'];
                $currency_name = '';
                if (isset($currency_array[$s_currency_id]))
                    $currency_name = $currency_array[$s_currency_id];
                if ($currency_name != '')
                    $search_array['Currency'] = $currency_name;
            }



            $file_name = 'supplier_detail_audit';

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);

            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        /////////////view report/////////////
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            if (isset($_SESSION['search_c_reconcilliation']['s_no_of_records']) && $_SESSION['search_c_reconcilliation']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_c_reconcilliation']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            if ($this->uri->segment($pagination_uri_segment) == '')
                $segment = 0;
            else
                $segment = $this->uri->segment($pagination_uri_segment);

            $report_data = $this->report_mod->supplier_detail_audit($search_data, $per_page, $segment);

            $all_total = $this->report_mod->total_count;
            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($all_total, 'reports/supplier_detail_audit', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();

            $data['total_records'] = $all_total;
            $data['report_data'] = $report_data;


            $data['currency_data'] = $currency_data;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/supplier_detail_audit', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function supplier_summary_audit($arg1 = '', $format = '') {
        //$this->output->enable_profiler(true);	
        $page_name = "report_supplier_summary_audit";
        $data['page_name'] = $page_name;
        $this->load->model('report_mod');

        $account_id = get_logged_account_id();
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (!check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC'))) {
            //not permitted
            show_404('403');
        }
        ////////////////////////////////////////////////


        if (isset($_POST['search_action'])) {// coming from search button							
            $_SESSION['search_c_summary'] = array('s_record_date' => $_POST['record_date']);
            $_SESSION['search_c_summary']['s_service_type'] = $_POST['service_type'];
            $_SESSION['search_c_summary']['s_supplier'] = $_POST['supplier'];
            $_SESSION['search_c_summary']['s_currency_id'] = $_POST['currency_id'];
            $_SESSION['search_c_summary']['s_no_of_records'] = $_POST['no_of_rows'];
        } else {
            $today_timestamp = strtotime("yesterday");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_c_summary']['s_record_date'] = isset($_SESSION['search_c_summary']['s_record_date']) ? $_SESSION['search_c_summary']['s_record_date'] : $time_range;
            $_SESSION['search_c_summary']['s_service_type'] = isset($_SESSION['search_c_summary']['s_service_type']) ? $_SESSION['search_c_summary']['s_service_type'] : '';

            $_SESSION['search_c_summary']['s_supplier'] = isset($_SESSION['search_c_summary']['s_supplier']) ? $_SESSION['search_c_summary']['s_supplier'] : '';
            $_SESSION['search_c_summary']['s_currency_id'] = isset($_SESSION['search_c_summary']['s_currency_id']) ? $_SESSION['search_c_summary']['s_currency_id'] : '';

            $_SESSION['search_c_summary']['s_no_of_records'] = isset($_SESSION['search_c_summary']['s_no_of_records']) ? $_SESSION['search_c_summary']['s_no_of_records'] : RECORDS_PER_PAGE;
        }

        $search_data = array(
            'record_date' => $_SESSION['search_c_summary']['s_record_date'],
            'service_type' => $_SESSION['search_c_summary']['s_service_type'],
            'supplier' => $_SESSION['search_c_summary']['s_supplier'],
            'currency_id' => $_SESSION['search_c_summary']['s_currency_id'],
        );
        //

        $currency_data = $this->utils_model->get_currencies();
        ////////////export////////////
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $format = param_decrypt($format);

            $per_page = 60000;
            $segment = 0;
            $report_data = $this->report_mod->supplier_summary_audit($search_data, $per_page, $segment);

            // column titles
            $export_header = array(
                'Supplier',
                'Service Type',
                'Currency',
                'Mins In',
                'Mins Out',
                'Mins Cost',
                'Quantity',
                'One-Off Costs',
                'Monthly Costs'
            );


            $currency_array = array();
            for ($i = 0; $i < count($currency_data); $i++) {
                $currency_id = $currency_data[$i]['currency_id'];
                $currency_array[$currency_id] = $currency_data[$i]['name'];
            }

            if (count($report_data['result']) > 0) {
                foreach ($report_data['result'] as $supplier_data) {

                    $currency_id = $supplier_data['currency_id'];
                    $currency_name = '';
                    if (isset($currency_array[$currency_id]))
                        $currency_name = $currency_array[$currency_id];

                    $sum_in_minute = round($supplier_data['sum_in_minute'] / 60, 2);
                    $sum_out_minute = round($supplier_data['sum_out_minute'] / 60, 2);

                    $sum_min_charge = round($supplier_data['sum_min_charge'], 2);
                    $sum_one_Off_charge = round($supplier_data['sum_one_Off_charge'], 2);
                    $sum_monthly_charge = round($supplier_data['sum_monthly_charge'], 2);


                    $export_data[] = array(
                        $supplier_data['supplier_name'],
                        $supplier_data['service_type'],
                        $currency_name,
                        $sum_in_minute,
                        $sum_out_minute,
                        $sum_min_charge,
                        $supplier_data['sum_quantity'],
                        $sum_one_Off_charge,
                        $sum_monthly_charge
                    );
                }
            } else
                $export_data = array('');

            //prepare search data
            $search_array = array();


            if ($_SESSION['search_c_summary']['s_record_date'] != '')
                $search_array['Record Date'] = $_SESSION['search_c_summary']['s_record_date'];
            if ($_SESSION['search_c_summary']['s_service_type'] != '')
                $search_array['Service Type'] = $_SESSION['search_c_summary']['s_service_type'];
            if ($_SESSION['search_c_summary']['s_supplier'] != '')
                $search_array['Supplier'] = $_SESSION['search_c_summary']['s_supplier'];
            if ($_SESSION['search_c_summary']['s_currency_id'] != '') {
                $s_currency_id = $_SESSION['search_c_summary']['s_currency_id'];
                $currency_name = '';
                if (isset($currency_array[$s_currency_id]))
                    $currency_name = $currency_array[$s_currency_id];
                if ($currency_name != '')
                    $search_array['Currency'] = $currency_name;
            }



            $file_name = 'supplier_summary_audit';

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);

            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }


        /////////////view report/////////////
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            if (isset($_SESSION['search_c_summary']['s_no_of_records']) && $_SESSION['search_c_summary']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_c_summary']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            if ($this->uri->segment($pagination_uri_segment) == '')
                $segment = 0;
            else
                $segment = $this->uri->segment($pagination_uri_segment);

            $report_data = $this->report_mod->supplier_summary_audit($search_data, $per_page, $segment);

            $all_total = $this->report_mod->total_count;
            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($all_total, 'reports/supplier_summary_audit', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();

            $data['total_records'] = $all_total;
            $data['report_data'] = $report_data;


            $data['currency_data'] = $currency_data;

            $this->load->view('basic/header', $data);
            $this->load->view('reports/supplier_summary_audit', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function netting($id = '') {
        $data['page_name'] = "report_supplier_invoice";
        $this->load->model('report_mod');
        $this->load->model('supplier_mod');

        $account_id = get_logged_account_id();
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        ///////////////////////////		
        $make_search = false;
        if (isset($_POST['OkFilter'])) {// coming from search button	
            $_SESSION['search_repo_supp_inv'] = array(
                's_supplier_id_name' => $_POST['supplier_id_name'],
                's_from_date' => $_POST['from_date'],
                's_to_date' => $_POST['to_date'],
            );
            $make_search = true;
        } elseif (isset($_SESSION['search_repo_supp_inv']['s_supplier_id_name'])) {
            
        } else {
            $date = new DateTime('last day of this month');
            $last_date = $date->format('Y-m-d');

            $_SESSION['search_repo_supp_inv']['s_supplier_id_name'] = '';
            $_SESSION['search_repo_supp_inv']['s_supplier_id_name'] = '';
            $_SESSION['search_repo_supp_inv']['s_from_date'] = Date('Y-m-01');
            $_SESSION['search_repo_supp_inv']['s_to_date'] = $last_date;
        }

        $search_data = array(
            'supplier_id_name' => $_SESSION['search_repo_supp_inv']['s_supplier_id_name'],
            'from_date' => $_SESSION['search_repo_supp_inv']['s_from_date'],
            'to_date' => $_SESSION['search_repo_supp_inv']['s_to_date']
        );


        ////////////export////////////
        $is_file_downloaded = false;


        /////////////view report/////////////
        if ($is_file_downloaded === false) {
            if ($make_search) {
                $supplier_invoice_data = $this->report_mod->supplier_netting($search_data['supplier_id_name'], $search_data['from_date'], $search_data['to_date']);

                $data['supplier_invoice_data'] = $supplier_invoice_data;
            } else {
                $data['supplier_invoice_data'] = array();
            }
            $data['make_search'] = $make_search;
            $data['suppliers_data'] = $this->supplier_mod->get_data('', '', '', array('status_id' => 1), array());

            $this->load->view('basic/header', $data);
            $this->load->view('reports/netting', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function payment_history() {
        $page_name = "payment_history";
        $this->load->model('payment_mod');
        $this->load->library('pagination'); // pagination class		
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $logged_account_id = get_logged_account_id();
        $is_file_downloaded = false;
        if (isset($_POST['search_action'])) {
            $_SESSION['search_payment_data'] = array(
                's_account_id' => $_POST['search_account_id'],
                's_pay_date' => $_POST['search_pay_date'],
                's_no_of_records' => $_POST['no_of_rows'],
            );
        } else {
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_payment_data']['s_account_id'] = isset($_SESSION['search_payment_data']['s_account_id']) ? $_SESSION['search_payment_data']['s_account_id'] : '';
            $_SESSION['search_payment_data']['s_pay_date'] = isset($_SESSION['search_payment_data']['s_pay_date']) ? $_SESSION['search_payment_data']['s_pay_date'] : $time_range;
            $_SESSION['search_payment_data']['s_no_of_records'] = isset($_SESSION['search_payment_data']['s_no_of_records']) ? $_SESSION['search_payment_data']['s_no_of_records'] : RECORDS_PER_PAGE;
        }



        $report_search_data = array('date_range' => $_SESSION['search_payment_data']['s_pay_date'], 'account_id' => $_SESSION['search_payment_data']['s_account_id'],);


        if (check_logged_account_type(array('ACCOUNTMANAGER')))
            $report_search_data['s_account_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('SALESMANAGER')))
            $report_search_data['s_sales_manager'] = $logged_account_id;
        elseif (check_logged_account_type(array('RESELLER')))
            $report_search_data['s_parent_account_id'] = $logged_account_id;
        elseif (check_logged_account_type(array('CUSTOMER')))
            $report_search_data['account_id'] = $logged_account_id;
        else {//'ADMIN', 'SUBADMIN' noc, credit
        }

        if ($arg1 == 'export' && $format != '') {
            $is_file_downloaded = true;
        }
        //echo '<pre>'; print_r($_POST); print_r($_SESSION['search_payment_data']);echo '</pre>';//die;

        if ($is_file_downloaded === false) {
            /*             * **** pagination code start here ********* */
            $pagination_uri_segment = 3;

            if (isset($_SESSION['search_payment_data']['s_no_of_records']) && $_SESSION['search_payment_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_payment_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }

            $payment_history = $this->payment_mod->get_data($order_by, $per_page, $segment, $report_search_data);

            $total = $this->payment_mod->total_count;


            $config = array();
            $config = $this->utils_model->setup_pagination_option($total, 'reports/payment_history', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);

            /*             * **** pagination code ends  here ********* */
            $data['pagination'] = $this->pagination->create_links();
            $data['data'] = $payment_history;
            $data['total_records'] = $total;



            //echo '<pre>';print_r($report_search_data );echo '<pre>'; //die('11111');		


            $this->load->view('basic/header', $data);
            $this->load->view('reports/payment_history', $data);
            $this->load->view('basic/footer', $data);
        }
    }

}
