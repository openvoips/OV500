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

defined('BASEPATH') OR exit('No direct script access allowed');

class Cdrs extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination'); // pagination class	
        $this->load->model('cdr_mod');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('Utils_model');
        //$this->output->enable_profiler(ENABLE_PROFILE);	
    }

    /**
     *
     * Function: To retrive connected calls
     * Author: Manohar
     *
     * */
    public function index($arg1 = '', $format = '') {
        $page_name = "cdr_index";
        $file_name = 'CDR_' . date('Ymd');
        $is_file_downloaded = false;

        //check page action permission
        if (!check_account_permission('reports', 'cdr'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        ///////////////// Searching ////////////////////
        $search_data = array();
        $is_fetch_data = false;
        if (isset($_POST['search_action'])) {//through search
            $_SESSION['search_cdr_data'] = array(
                's_cdr_user_type' => $_POST['user_type'], 's_cdr_user_account' => $_POST['user_account'], 's_cdr_dialed_no' => $_POST['dialed_no'],
                's_cdr_carrier_dst_no' => $_POST['carrier_dst_no'], 's_cdr_user_cli' => $_POST['user_cli'],
                's_cdr_carrier_cli' => $_POST['carrier_cli'], 's_cdr_carrier' => $_POST['carrier'],
                's_cdr_carrier_ip' => $_POST['carrier_ip'], 's_cdr_user_ip' => $_POST['user_ip'],
                's_cdr_call_duration' => $_POST['call_duration'],
                's_time_range' => $_POST['time_range']
            );
            $is_fetch_data = true;
        } else {
            if ($arg1 == 'pag') {//through pagination
                $is_fetch_data = true;
                $_SESSION['search_cdr_data']['s_time_range'] = isset($_SESSION['search_cdr_data']['s_time_range']) ? $_SESSION['search_cdr_data']['s_time_range'] : '';
            } else {
                $query_date = date('Y-m-d');
                ;
                $date = new DateTime($query_date);
                //First day of month
                $date->modify('first day of this month');
                $firstday = $date->format('d-m-Y') . ' 00:00';
                //Last day of month
                $date->modify('last day of this month');
                $lastday = $date->format('d-m-Y') . ' 23:59';

                $default_date = $firstday . ' - ' . $lastday;
                $_SESSION['search_cdr_data']['s_time_range'] = isset($_SESSION['search_cdr_data']['s_time_range']) ? $_SESSION['search_cdr_data']['s_time_range'] : $default_date;
            }

            $_SESSION['search_cdr_data']['s_cdr_user_type'] = isset($_SESSION['search_cdr_data']['s_cdr_user_type']) ? $_SESSION['search_cdr_data']['s_cdr_user_type'] : '';
            $_SESSION['search_cdr_data']['s_cdr_user_account'] = isset($_SESSION['search_cdr_data']['s_cdr_user_account']) ? $_SESSION['search_cdr_data']['s_cdr_user_account'] : '';
            $_SESSION['search_cdr_data']['s_cdr_dialed_no'] = isset($_SESSION['search_cdr_data']['s_cdr_dialed_no']) ? $_SESSION['search_cdr_data']['s_cdr_dialed_no'] : '';
            $_SESSION['search_cdr_data']['s_cdr_carrier_dst_no'] = isset($_SESSION['search_cdr_data']['s_cdr_carrier_dst_no']) ? $_SESSION['search_cdr_data']['s_cdr_carrier_dst_no'] : '';
            $_SESSION['search_cdr_data']['s_cdr_user_cli'] = isset($_SESSION['search_cdr_data']['s_cdr_user_cli']) ? $_SESSION['search_cdr_data']['s_cdr_user_cli'] : '';
            $_SESSION['search_cdr_data']['s_cdr_carrier_cli'] = isset($_SESSION['search_cdr_data']['s_cdr_carrier_cli']) ? $_SESSION['search_cdr_data']['s_cdr_carrier_cli'] : '';
            $_SESSION['search_cdr_data']['s_cdr_carrier'] = isset($_SESSION['search_cdr_data']['s_cdr_carrier']) ? $_SESSION['search_cdr_data']['s_cdr_carrier'] : '';
            $_SESSION['search_cdr_data']['s_cdr_carrier_ip'] = isset($_SESSION['search_cdr_data']['s_cdr_carrier_ip']) ? $_SESSION['search_cdr_data']['s_cdr_carrier_ip'] : '';
            $_SESSION['search_cdr_data']['s_cdr_user_ip'] = isset($_SESSION['search_cdr_data']['s_cdr_user_ip']) ? $_SESSION['search_cdr_data']['s_cdr_user_ip'] : '';
            $_SESSION['search_cdr_data']['s_cdr_call_duration'] = isset($_SESSION['search_cdr_data']['s_cdr_call_duration']) ? $_SESSION['search_cdr_data']['s_cdr_call_duration'] : '';
        }

        if ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'U')
            $user = 'user_account_id';
        elseif ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'R1')
            $user = 'reseller1_account_id';
        elseif ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'R2')
            $user = 'reseller2_account_id';
        elseif ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'R3')
            $user = 'reseller3_account_id';

        $search_data = array(
            $user => $_SESSION['search_cdr_data']['s_cdr_user_account'], 'user_src_callee' => $_SESSION['search_cdr_data']['s_cdr_dialed_no'],
            'carrier_dst_callee' => $_SESSION['search_cdr_data']['s_cdr_carrier_dst_no'], 'user_src_caller' => $_SESSION['search_cdr_data']['s_cdr_user_cli'],
            'carrier_dst_caller' => $_SESSION['search_cdr_data']['s_cdr_carrier_cli'], 'carrier_carrier_id_name' => $_SESSION['search_cdr_data']['s_cdr_carrier'],
            'carrier_gateway_ipaddress' => $_SESSION['search_cdr_data']['s_cdr_carrier_ip'], 'user_src_ip' => $_SESSION['search_cdr_data']['s_cdr_user_ip'],
            'carrier_duration' => $_SESSION['search_cdr_data']['s_cdr_call_duration'],
            'logged_user_type' => get_logged_account_type(),
            'logged_current_customer_id' => get_logged_account_id(),
            'logged_user_level' => get_logged_account_level(),
        );


        if ($_SESSION['search_cdr_data']['s_time_range'] != '') {
            $range = explode(' - ', $_SESSION['search_cdr_data']['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt_array = explode('-', $range_from[0]);
            $search_data['start_time'] = $start_dt_array[2] . '-' . $start_dt_array[1] . '-' . $start_dt_array[0] . ' ' . $range_from[1];

            $end_dt_array = explode('-', $range_to[0]);
            $search_data['end_time'] = $end_dt_array[2] . '-' . $end_dt_array[1] . '-' . $end_dt_array[0] . ' ' . $range_to[1];
        }


        ///////set fields according to user type & level////
        //////for display & export/////////////////
        $logged_user_type = get_logged_account_type();
        $get_logged_account_level = get_logged_account_level();

        //field_name => display lebel									
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
        ///////////////// Export ////////////////////

        $order_by = '';
        $option_param = array();
        if ($arg1 == 'export' && $format != '') {
            $this->load->library('Export');
            $format = param_decrypt($format);
            $option_param = array($export => true);
            $response_data = $this->cdr_mod->get_data($order_by, '', '', $search_data, $option_param);

            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }
            //var_dump($response_data);die();
            if ($response_data['total'] > 0) {
                $export_data[] = array();
                foreach ($response_data['result'] as $row) {
                    $temp_array = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {
                        $temp_array[] = $row[$field_name];
                    }
                    $export_data[] = $temp_array;
                }
            } else
                $export_data = array();

            $downloaded_message = $this->export->download($file_name, $format, $search_data, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        ///////////////// Viewing ////////////////////

        if ($is_file_downloaded === false) {   //print_r($search_data);
            $pagination_uri_segment = $this->uri->segment(4, 0);
            $response = array('total' => 0, 'result' => '');
            if ($is_fetch_data) {//die("searched");
                $response = $this->cdr_mod->get_data($order_by, $pagination_uri_segment, RECORDS_PER_PAGE, $search_data, $option_param);
            }

            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['total'], 'cdrs/index/pag', RECORDS_PER_PAGE, 4);

            $this->pagination->initialize($config);

            $data['page_name'] = $page_name;
            $data['pagination'] = $this->pagination->create_links();
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $data['all_field_array'] = $all_field_array;

            $this->load->view('basic/header', $data);
            $this->load->view('cdr/cdr', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    /**
     *
     * Function: To retrive failed calls
     * Author: Manohar
     *
     * */
    public function failed_calls($arg1 = '', $format = '') {
        $this->load->model('failedcdr_mod');

        $page_name = "cdr_failed_calls";
        $file_name = 'FailedCDR_' . date('Ymd');
        $is_file_downloaded = false;

        //check page action permission
        if (!check_account_permission('reports', 'fail_calls'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        //print_r($_POST);
        ///////////////// Searching ////////////////////
        $search_data = array();
        $is_fetch_data = false;
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
                's_time_range' => $_POST['time_range']
            );
            $is_fetch_data = true;
        } else {
            if ($arg1 == 'pag') {//through pagination
                $is_fetch_data = true;
                $_SESSION['search_failed_data']['s_time_range'] = isset($_SESSION['search_failed_data']['s_time_range']) ? $_SESSION['search_failed_data']['s_time_range'] : '';
            } else {
                $query_date = date('Y-m-d');
                ;
                $date = new DateTime($query_date);
                //First day of month
                $date->modify('first day of this month');
                $firstday = $date->format('d-m-Y') . ' 00:00';
                //Last day of month
                $date->modify('last day of this month');
                $lastday = $date->format('d-m-Y') . ' 23:59';

                $default_date = $firstday . ' - ' . $lastday;
                $_SESSION['search_failed_data']['s_time_range'] = isset($_SESSION['search_cdr_data']['search_failed_data']) ? $_SESSION['search_failed_data']['s_time_range'] : $default_date;
            }

            $_SESSION['search_failed_data']['s_cdr_user_type'] = isset($_SESSION['search_failed_data']['s_cdr_user_type']) ? $_SESSION['search_failed_data']['s_cdr_user_type'] : '';
            $_SESSION['search_failed_data']['s_cdr_user_account'] = isset($_SESSION['search_failed_data']['s_cdr_user_account']) ? $_SESSION['search_failed_data']['s_cdr_user_account'] : '';
            $_SESSION['search_failed_data']['s_cdr_dialed_no'] = isset($_SESSION['search_failed_data']['s_cdr_dialed_no']) ? $_SESSION['search_failed_data']['s_cdr_dialed_no'] : '';
            $_SESSION['search_failed_data']['s_cdr_carrier_dst_no'] = isset($_SESSION['search_failed_data']['s_cdr_carrier_dst_no']) ? $_SESSION['search_failed_data']['s_cdr_carrier_dst_no'] : '';
            $_SESSION['search_failed_data']['s_cdr_user_cli'] = isset($_SESSION['search_failed_data']['s_cdr_user_cli']) ? $_SESSION['search_failed_data']['s_cdr_user_cli'] : '';
            $_SESSION['search_failed_data']['s_cdr_carrier_cli'] = isset($_SESSION['search_failed_data']['s_cdr_carrier_cli']) ? $_SESSION['search_failed_data']['s_cdr_carrier_cli'] : '';
            $_SESSION['search_failed_data']['s_cdr_carrier'] = isset($_SESSION['search_failed_data']['s_cdr_carrier']) ? $_SESSION['search_failed_data']['s_cdr_carrier'] : '';
            $_SESSION['search_failed_data']['s_cdr_carrier_ip'] = isset($_SESSION['search_failed_data']['s_cdr_carrier_ip']) ? $_SESSION['search_failed_data']['s_cdr_carrier_ip'] : '';
            $_SESSION['search_failed_data']['s_cdr_user_ip'] = isset($_SESSION['search_failed_data']['s_cdr_user_ip']) ? $_SESSION['search_failed_data']['s_cdr_user_ip'] : '';
            $_SESSION['search_failed_data']['s_cdr_sip_code'] = isset($_SESSION['search_failed_data']['s_cdr_sip_code']) ? $_SESSION['search_failed_data']['s_cdr_sip_code'] : '';
            $_SESSION['search_failed_data']['s_cdr_Q850CODE'] = isset($_SESSION['search_failed_data']['s_cdr_Q850CODE']) ? $_SESSION['search_failed_data']['s_cdr_Q850CODE'] : '';
        }

        if ($_SESSION['search_failed_data']['s_cdr_user_type'] == 'U')
            $user = 'user_account_id';
        elseif ($_SESSION['search_failed_data']['s_cdr_user_type'] == 'R1')
            $user = 'reseller1_account_id';
        elseif ($_SESSION['search_failed_data']['s_cdr_user_type'] == 'R2')
            $user = 'reseller2_account_id';
        elseif ($_SESSION['search_failed_data']['s_cdr_user_type'] == 'R3')
            $user = 'reseller3_account_id';

        $search_data = array(
            $user => $_SESSION['search_failed_data']['s_cdr_user_account'],
            'user_src_callee' => $_SESSION['search_failed_data']['s_cdr_dialed_no'],
            'carrier_dst_callee' => $_SESSION['search_failed_data']['s_cdr_carrier_dst_no'],
            'user_src_caller' => $_SESSION['search_failed_data']['s_cdr_user_cli'],
            'carrier_dst_caller' => $_SESSION['search_failed_data']['s_cdr_carrier_cli'],
            'carrier_carrier_id_name' => $_SESSION['search_failed_data']['s_cdr_carrier'],
            'carrier_gateway_ipaddress' => $_SESSION['search_failed_data']['s_cdr_carrier_ip'],
            'user_src_ip' => $_SESSION['search_failed_data']['s_cdr_user_ip'],
            'SIPCODE' => $_SESSION['search_failed_data']['s_cdr_sip_code'],
            'Q850CODE' => $_SESSION['search_failed_data']['s_cdr_Q850CODE'],
            'carrier_duration' => $_SESSION['search_failed_data']['s_cdr_call_duration'],
            'logged_user_type' => get_logged_account_type(),
            'logged_current_customer_id' => get_logged_account_id(),
            'logged_user_level' => get_logged_account_level(),
        );
        /*
          'start_stamp'=>$_SESSION['search_data']['s_cdr_start_dt'],
          'end_stamp'=>$_SESSION['search_data']['s_cdr_end_dt'],
         */

        if ($_SESSION['search_cdr_data']['s_time_range'] != '') {
            $range = explode(' - ', $_SESSION['search_cdr_data']['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt_array = explode('-', $range_from[0]);
            $search_data['start_stamp'] = $start_dt_array[2] . '-' . $start_dt_array[1] . '-' . $start_dt_array[0] . ' ' . $range_from[1];

            $end_dt_array = explode('-', $range_to[0]);
            $search_data['end_stamp'] = $end_dt_array[2] . '-' . $end_dt_array[1] . '-' . $end_dt_array[0] . ' ' . $range_to[1];
        }


        ///////set fields according to user type & level////
        //////for display & export/////////////////
        $logged_user_type = get_logged_account_type();
        $get_logged_account_level = get_logged_account_level();

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
        ////////////////////////////////
        ///////////////// Export ////////////////////
        $order_by = '';
        if ($arg1 == 'export' && $format != '') {
            $this->load->library('Export');
            $format = param_decrypt($format);
            $option_param = array($export => true);
            $response_data = $this->failedcdr_mod->get_data($order_by, '', '', $search_data, $option_param);

            //////////
            $export_header = array();
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }
            //var_dump($response_data);die();
            if ($response_data['total'] > 0) {
                $export_data[] = array();
                foreach ($response_data['result'] as $row) {
                    $temp_array = array();
                    foreach ($all_field_array as $field_name => $field_lebel) {
                        $temp_array[] = $row[$field_name];
                    }
                    $export_data[] = $temp_array;
                }
            } else
                $export_data = array();

            /////////

            $downloaded_message = $this->export->download($file_name, $format, $search_data, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        ///////////////// View ////////////////////
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = $this->uri->segment(4, 0);
            $response = array('total' => 0, 'result' => '');
            //print_r($search_data);

            if ($is_fetch_data) {
                $response = $this->failedcdr_mod->get_data($order_by, $pagination_uri_segment, RECORDS_PER_PAGE, $search_data);
            }
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['total'], 'cdrs/failed_calls/pag', RECORDS_PER_PAGE, 4);
            $this->pagination->initialize($config);

            $data['page_name'] = $page_name;
            $data['pagination'] = $this->pagination->create_links();
            $data['listing_data'] = $response['result'];
            $data['listing_count'] = $response['total'];
            $data['all_field_array'] = $all_field_array;

            $this->load->view('basic/header', $data);
            $this->load->view('cdr/failed_cdrs', $data);
            $this->load->view('basic/footer', $data);
        }
    }

}
