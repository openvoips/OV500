<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Reports extends MY_Controller {

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

    function round_up($value, $precision) {
        $pow = pow(10, $precision);
        return ( ceil($pow * $value) + ceil($pow * $value - ceil($pow * $value)) ) / $pow;
    }

    function accountsummary() {


        $page_name = "report_accountsummary";
        $data['page_name'] = $page_name;

        $logged_account_id = get_logged_account_id();
        if (!check_logged_account_type(array('CUSTOMER'))) {
            show_404('dashboard');
        }
        $data = $this->report_mod->account_summery($logged_account_id);
        $this->load->view('basic/header', $data);
        $this->load->view('reports/accountsummary', $data);
        $this->load->view('basic/footer', $data);
    }

    function resellersummary() {
        $page_name = "report_accountsummary";
        $data['page_name'] = $page_name;
        $logged_account_id = get_logged_account_id();
        if (!check_logged_account_type(array('RESELLER'))) {
            show_404('dashboard');
        }

        $data = $this->report_mod->reseller_summery($logged_account_id);
        $this->load->view('basic/header', $data);
        $this->load->view('reports/resellersummary', $data);
        $this->load->view('basic/footer', $data);
    }

    function customers_status() {
        $DB1 = $this->load->database('cdrdb', true);
        $sql = " select concat(( select concat(count(id),'C')  FROM account  where account_level = '1'  and account.account_type = 'CUSTOMER' and status_id = '1'),'/', ( select concat(count(id),'R')  FROM account  where account_level = '1'  and account.account_type = 'RESELLER' and status_id = '1' )) as customer  ; ";

        $result = $this->db->query($sql);

        $customer = $result->result_array();

        echo $customer[0]['customer'];
    }

    function callusage($arg1 = '', $format = '') {


        $page_name = 'report_callusage';
        $data['page_name'] = $page_name;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $logged_customer_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();

///////////////// Searching ////////////////////
// reservation-time
        $is_make_search = false;
        $search_data = array();
        if (isset($_POST['OkFilter'])) {
            $_SESSION[$page_name] = array(
                'time_range' => $_POST['time_range'],
                'cdr_type' => $_POST['cdr_type'],
                'customer_company_name' => $_POST['customer_company_name'],
                'customer_account_id' => $_POST['customer_account_id'],
                'no_of_records' => $_POST['no_of_rows']
            );
            $is_make_search = true;
        } elseif ($arg1 != 'export' && !isset($_SESSION[$page_name]['time_range'])) {
//default date is todays date
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION[$page_name] = array(
                'time_range' => $time_range,
                'cdr_type' => '',
                'customer_company_name' => '',
                'customer_account_id' => '',
                'no_of_records' => isset($_SESSION[$page_name]['no_of_records']) ? $_SESSION[$page_name]['no_of_records'] : RECORDS_PER_PAGE
            );
        } else {
            $is_make_search = true;
        }


        $search_data = array(
            'time_range' => $_SESSION[$page_name]['time_range'],
            'cdr_type' => $_SESSION[$page_name]['cdr_type'],
        );

        if (!check_logged_account_type(array('CUSTOMER'))) {
            $search_data['customer_company_name'] = $_SESSION[$page_name]['customer_company_name'];
            $search_data['customer_account_id'] = $_SESSION[$page_name]['customer_account_id'];
        }


        $is_file_downloaded = false;

        if ($is_file_downloaded === false) {

            $pagination_uri_segment = 3;
            if (isset($_SESSION[$page_name]['no_of_records']) && $_SESSION[$page_name]['no_of_records'] != '')
                $per_page = $_SESSION[$page_name]['no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;
            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }

            $response = $this->report_mod->callusage_mod($search_data, $per_page, $segment);
            $data['listing_data'] = $response['result'];
            $total_records = $response['total'];
            $data['sql'] = $response['sql'];
            $data['sum_sql'] = $response['sum_sql'];

            $this->load->library('pagination'); // pagination class		
            $config = array();
            $config = $this->utils_model->setup_pagination_option($total_records, 'report/callusage', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();
            $data['total_records'] = $total_records;

            $data['sum_result'] = $response['sum_result'];

            $data['currency_options'] = $this->utils_model->get_currencies();

            $this->load->view('basic/header', $data);
            $this->load->view('reports/callusage', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function usage() {

        $DB1 = $this->load->database('cdrdb', true);
        $sql = "select currency_id , symbol from sys_currencies where status_id = '1'";

        $result = $this->db->query($sql);
        $dd = date('Ym');
        $i = 0;
        $currencies = $result->result_array();
        foreach ($currencies as $currency) {
            $sql = "select sum(if (reseller1_account_id is not null or reseller1_account_id != '' ,reseller1_callcost_total, customer_callcost_total)) salecost  from " . $dd . "_ratedcdr  where customer_currency_id = '" . $currency['currency_id'] . "' and date(end_time) = '" . date("Y-m-d") . "'";

// $DB1 = $this->load->database('cdrdb', true);
            $result = $DB1->query($sql);
            $usage = $result->result_array();
            $usagedata[$i]['currency'] = $currency['currency_id'] . " (" . $currency['symbol'] . ")";
            $usagedata[$i]['salecost'] = $this->round_up($usage[0]['salecost'], 4);
            $sql = "select sum(carrier_callcost_total) buycost  from " . $dd . "_ratedcdr  where carrier_currency_id = '" . $currency['currency_id'] . "' and date(end_time) = '" . date("Y-m-d") . "'";
//$DB1 = $this->load->database('cdrdb', true);
            $result = $DB1->query($sql);
            $usage2 = $result->result_array();
            $usagedata[$i]['buycost'] = $this->round_up($usage2[0]['buycost'], 4);
            $i = $i + 1;
        }
        $str = '';
        $str .= '<table class="table table-bordered table-condensed">';
        $str .= '<tr><td></td><td>Buy</td><td>Sell</td><td>Margin</td></tr>';
        foreach ($usagedata as $usagedata_array) {
            if ($usagedata_array['buycost'] > 0 or $usagedata_array['salecost'] > 0) {
                $str .= '<tr><td>' . $usagedata_array['currency'] . '</td><td>' . round($usagedata_array['buycost'], 4) . '</td><td>' . round($usagedata_array['salecost'], 4) . '</td><td>' . round($usagedata_array['salecost'] - $usagedata_array['buycost'], 4) . '</td></tr>';
            }
        }
        $str .= '<table>';
        echo $str;
    }

/////////////////monin_update_05_30/////////////////
    public function monin_new() {
        $data['page_name'] = "monin";
        if (!check_account_permission('reports', 'monin'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->view('basic/header', $data);
        if (check_logged_user_group(array('RESELLER')))
            $this->load->view('reports/monin-reseller', $data);
        else
            $this->load->view('reports/monin2', $data);
        $this->load->view('basic/footer', $data);
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

        if (check_logged_user_group(array('RESELLER'))) {
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
                's_code' => '',
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
                's_g_q850' => '',
                's_sip' => '',
                's_q850' => '',
                's_g_ip' => ''
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
            $currency_abbr = function ($id) use ($currency_data) {
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

    public function monin_data_reseller($incoming_calls = 'Y', $outgoing_calls = 'Y', $incoming_duration = 'Y', $outgoing_duration = 'Y', $gateway_calls = 'Y', $customer_calls = 'Y', $show_usage = 'Y', $customer_call_stat = 'N', $carrier_call_stat = 'N', $livecalls_destination = 'N') {
        $this->report_mod->monin_data_reseller($incoming_calls, $outgoing_calls, $incoming_duration, $outgoing_duration, $gateway_calls, $customer_calls, $show_usage, $customer_call_stat, $carrier_call_stat, $livecalls_destination);
    }

    public function CustQOSR($arg1 = '', $format = '') {
        $data['page_name'] = "CustQOSR";

        if (!check_account_permission('reports', 'CustQOSR'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->model('carrier_mod');

        $data['currency_data'] = $this->utils_model->get_currencies();
        $currency_data = $data['currency_data'];

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
            $currency_abbr = function ($id) use ($currency_data) {
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

        if (check_logged_user_group(array('RESELLER')))
            $view_file = 'monin2_reseller';
        else
            $view_file = 'monin2';
        $this->load->view('basic/header', $data);
        $this->load->view('reports/' . $view_file, $data);
        $this->load->view('basic/footer', $data);
    }

    public function Calls($account_id_temp = '', $format = '') {
        $arg1 = $account_id_temp;
        $this->load->model('report_mod');
        $data['page_name'] = "calls";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $logged_customer_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $get_logged_account_level = get_logged_account_level();
        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_cdr_data'] = array(
                's_cdr_customer_type' => isset($_POST['customer_type']) ? $_POST['customer_type'] : '',
                's_cdr_customer_account' => isset($_POST['customer_account']) ? $_POST['customer_account'] : '',
                's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => isset($_POST['carrier_dst_no']) ? $_POST['carrier_dst_no'] : '',
                's_cdr_customer_cli' => $_POST['customer_cli'],
                's_cdr_carrier_cli' => isset($_POST['carrier_cli']) ? $_POST['carrier_cli'] : '',
                's_cdr_carrier' => isset($_POST['carrier']) ? $_POST['carrier'] : '',
                's_cdr_carrier_ip' => isset($_POST['carrier_ip']) ? $_POST['carrier_ip'] : '',
                's_cdr_customer_ip' => $_POST['customer_ip'],
                's_cdr_call_duration' => $_POST['call_duration'],
                's_time_range' => $_POST['time_range'],
                's_cdr_customer_company_name' => isset($_POST['customer_company_name']) ? $_POST['customer_company_name'] : '',
                's_cdr_call_duration_range' => $_POST['duration_range'],
                's_cdr_cdr_type' => $_POST['cdr_type'],
                'disposition' => $_POST['disposition'],
                'cdr_type' => $_POST['cdr_type'],
                's_no_of_records' => $_POST['no_of_rows'],
            );
        } elseif ($arg1 != 'export' && !isset($_SESSION['search_cdr_data']['s_time_range'])) {

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
                's_cdr_call_duration_range' => '',
                'disposition' => '',
                'cdr_type' => '',
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
            'disposition' => $_SESSION['search_cdr_data']['disposition'],
            'cdr_type' => $_SESSION['search_cdr_data']['cdr_type'],
        );

        if (check_logged_user_group(array('RESELLER')))
            $search_data['s_parent_account_id'] = $logged_account_id;

        if (check_logged_user_group(array('CUSTOMER'))) {
            $search_data['s_cdr_customer_account'] = $logged_account_id;
            $search_data['s_cdr_customer_type'] = 'CUSTOMER';
            $search_data['s_cdr_customer_type_login'] = 'CUSTOMER';
        }


        $all_field_array = array(
            'Account' => 'Account'
            , 'disposition' => 'Call Status'
            , 'recording_file' => 'Recording'
            , 'cdr_type' => 'Call-Type'
            , 'C-CLI' => 'C-CLI'
            , 'C-DST' => 'C-DST'
	    ,'digits_dialed' => 'DTMF'
            , 'Start Time' => 'Start Time'
            , 'End Time' => 'End Time'
            , 'Carrier' => 'Carrier'
            , 'C-Duration' => 'C-Duration'
	    , 'pdd'=>'pdd'
            , 'C-Tariff' => 'C-Tariff'
            , 'C-Prefix' => 'C-Prefix'
            , 'C-Destination' => 'C-Destination'
            , 'C-Cost' => 'C-Cost'
            , 'Routing' => 'Routing'
            , 'C-IP' => 'C-IP'
            , 'SRC-IP' => 'Caller-IP'
            , 'Duration' => 'Duration'
            , 'Cost' => 'Cost'
            , 'Q850CODE' => 'Q850CODE'
            , 'SIPCODE' => 'SIPCODE'
            , 'SRC-DST' => 'SRC-DST'
            , 'SRC-CLI' => 'SRC-CLI'
            , 'User-Tariff' => 'User-Tariff'
            , 'Prefix' => 'Prefix'
            , 'Destination' => 'Destination'
            , 'Org-Duration' => 'Org-Duration'
            , 'USER-CLI' => 'USER-CLI'
            , 'User-DST' => 'User-DST'
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
            , "disposition_cause" => "Disposition"
            , "src_extension_no" => "SRC Exten-No"
            , "src_extension_name" => "SRC Name"
            , "dst_app" => "ViA APP"
            , "dst_app_number" => "ViA APP-No"
            , "dst_app_name" => "ViA APP-Name"
            , "endpoint_app" => "EndPoint APP No"
            , "endpoint_name" => "EndPoint Name"
            , "endpoint_number" => "EndPoint Dst"
            , "endpoint_extension_no" => "EndPoint"
        );

        if (check_logged_user_group('RESELLER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-Duration']);
            unset($all_field_array['C-Cost']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
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
        } elseif (check_logged_user_group('CUSTOMER')) {
            unset($all_field_array['Routing']);
            unset($all_field_array['Carrier']);
            unset($all_field_array['C-Tariff']);
            unset($all_field_array['C-Prefix']);
            unset($all_field_array['C-Destination']);
            unset($all_field_array['C-Duration']);
            unset($all_field_array['C-Cost']);
            unset($all_field_array['C-IP']);
            unset($all_field_array['C-DST']);
            unset($all_field_array['C-CLI']);
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
            $per_page = 600000000;
            $segment = 0;
            $response = $this->report_mod->ConnectedCalls($search_data, $per_page, $segment);
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


            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }

            $file_name = 'cdr_' . date('YmdHis');

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);

            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        if ($is_file_downloaded === false) {

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


            $response = $this->report_mod->ConnectedCalls($search_data, $per_page, $segment);
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $data['total_records'] = $response['all_total'];

            $this->load->library('pagination');
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['all_total'], 'reports/Calls', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();

            $data['logged_customer_type'] = $logged_customer_type;
            $data['get_logged_account_level'] = $get_logged_account_level;
            $data['all_field_array'] = $all_field_array;

            if (check_logged_user_group(array('RESELLER'))) {
                $this->load->view('basic/header', $data);
                $this->load->view('reports/callsRC', $data);
                $this->load->view('basic/footer', $data);
            } elseif (check_logged_user_group(array('CUSTOMER'))) {
                $this->load->view('basic/header', $data);
                $this->load->view('reports/callsC', $data);
                $this->load->view('basic/footer', $data);
            } else {
                $this->load->view('basic/header', $data);
                $this->load->view('reports/calls', $data);
                $this->load->view('basic/footer', $data);
            }
        }
    }

}
