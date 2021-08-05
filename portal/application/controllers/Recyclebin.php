<?php

// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
// OV500 Version 2.0.0
// Copyright (C) 2019-2021 Openvoips Technologies   
// http://www.openvoips.com  http://www.openvoips.org
// 
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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

class Recyclebin extends MY_Controller {

    function __construct() {
        parent::__construct();

        $this->load->library('pagination'); // pagination class			
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('log_mod');
        //permission check		
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        if (!check_logged_user_group('admin'))
            show_404('403');

        //$this->output->enable_profiler(ENABLE_PROFILE);		
    }

    function index($arg1 = '', $format = '') {
        $page_name = "recyclebin_index";
        $data['page_name'] = $page_name;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['search_action'])) {// coming from search button
            $_SESSION['search_log_data'] = array('s_delete_type' => $_POST['delete_type'], 's_account_id' => $_POST['account_id'], 's_time_range' => $_POST['time_range']);
        } else {
            $_SESSION['search_log_data']['s_delete_type'] = isset($_SESSION['search_log_data']['s_delete_type']) ? $_SESSION['search_log_data']['s_delete_type'] : '';
            $_SESSION['search_log_data']['s_account_id'] = isset($_SESSION['search_log_data']['s_account_id']) ? $_SESSION['search_log_data']['s_account_id'] : '';
            $_SESSION['search_log_data']['s_time_range'] = isset($_SESSION['search_log_data']['s_time_range']) ? $_SESSION['search_log_data']['s_time_range'] : '';
        }

        ////////////////////////////////////////////////
        $search_data = array('activity_type' => 'delete_recovery', 'sql_table' => $_SESSION['search_log_data']['s_delete_type'], 'sql_key' => $_SESSION['search_log_data']['s_account_id']);
        if ($_SESSION['search_log_data']['s_time_range'] != '') {
            $range = explode(' - ', $_SESSION['search_log_data']['s_time_range']);
            $range_from = explode(' ', $range[0]);
            $range_to = explode(' ', $range[1]);

            $start_dt_array = explode('-', $range_from[0]);
            $search_data['start_dt'] = $start_dt_array[2] . '-' . $start_dt_array[1] . '-' . $start_dt_array[0] . ' ' . $range_from[1];

            $end_dt_array = explode('-', $range_to[0]);
            $search_data['end_dt'] = $end_dt_array[2] . '-' . $end_dt_array[1] . '-' . $end_dt_array[0] . ' ' . $range_to[1];
        }


        $log_result = $this->log_mod->get_data('', '', '', $search_data);
        if (isset($log_result['result']))
            $delete_log_data = $log_result['result'];
        else
            $delete_log_data = array();

        $data['data'] = $delete_log_data;

        $data['delete_type_options'] = $this->log_mod->get_delete_types();

        $this->load->view('basic/header', $data);
        $this->load->view('log/delete_log', $data);
        $this->load->view('basic/footer', $data);
    }

    function details($id = -1) {
        if ($id == -1)
            show_404();

        $page_name = "recyclebin_details";
        $data['page_name'] = $page_name;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $activity_id = param_decrypt($id);
        $search_data = array('activity_id' => $activity_id, 'activity_type' => 'delete_recovery');
        $log_result = $this->log_mod->get_data('', '', '', $search_data);

        //echo '<pre>';	print_r($log_result);			die;
        if (!isset($log_result['result'])) {
            show_404();
        } else {
            $search_data = array('activity_id' => $activity_id, 'activity_type' => 'delete');
            $log_details_result = $this->log_mod->get_data('', '', '', $search_data);

            $data['data'] = current($log_result['result']);
            $data['activity_data'] = $log_details_result['result'];
        }


        $this->load->view('basic/header', $data);
        $this->load->view('log/delete_log_details', $data);
        $this->load->view('basic/footer', $data);
    }

    function rollback() {
        $page_name = "recyclebin_rollback";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'rollback' && isset($_POST['activity_id']) && $_POST['activity_id'] != '') {
            $activity_id = trim($_POST['activity_id']);
        } else
            show_404();


        $search_data = array('activity_id' => $activity_id, 'activity_type' => 'delete_recovery');
        $log_result = $this->log_mod->get_data('', '', '', $search_data);
        if (!isset($log_result['result'])) {
            show_404();
        }

        //	print_r($_POST);
        //	echo '<pre>';	print_r($log_result);			die;

        $main_data = current($log_result['result']);


        /**/

        switch ($main_data['sql_table']) {
            case 'NOTPERMITTED':
                $this->session->set_flashdata('err_msgs', 'Rollback not implemented for this');
                redirect(base_url() . 'recyclebin/details/' . param_encrypt($activity_id), 'location', '301');

                break;

            default:
                $result = $this->log_mod->rollback($activity_id);
                //var_dump($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Rollback Complete Successfully');
                    redirect(base_url() . 'recyclebin', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(base_url() . 'recyclebin/details/' . param_encrypt($activity_id), 'location', '301');
                }
                exit();

            //echo '<pre>';print_r($activity_data);	echo '</pre>';
        }
    }

    function email_log() {
        $page_name = "recyclebin_email_log";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        //$this->load->model('log_mod');	
        $this->load->library('pagination');
        $is_file_downloaded = false;

        if (isset($_POST['search_action'])) {// coming from search button
            $_SESSION['search_emaillog_data'] = array(
                's_type' => trim($_POST['action_type']),
                's_account_id' => trim($_POST['account_id']),
                's_time_range' => trim($_POST['time_range']),
                's_no_of_records' => $_POST['no_of_rows']
            );
        } else {
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';

            $_SESSION['search_emaillog_data']['s_type'] = isset($_SESSION['search_emaillog_data']['s_type']) ? $_SESSION['search_emaillog_data']['s_type'] : '';
            $_SESSION['search_emaillog_data']['s_account_id'] = isset($_SESSION['search_emaillog_data']['s_account_id']) ? $_SESSION['search_emaillog_data']['s_account_id'] : '';
            $_SESSION['search_emaillog_data']['s_time_range'] = isset($_SESSION['search_emaillog_data']['s_time_range']) ? $_SESSION['search_emaillog_data']['s_time_range'] : $time_range;
            $_SESSION['search_emaillog_data']['s_no_of_records'] = isset($_SESSION['search_emaillog_data']['s_no_of_records']) ? $_SESSION['search_emaillog_data']['s_no_of_records'] : RECORDS_PER_PAGE;
        }

        ////////////////////////////////////////////////
        $search_data = array(
            'actionfrom' => $_SESSION['search_emaillog_data']['s_type'],
            'account_id' => $_SESSION['search_emaillog_data']['s_account_id'],
            'time_range' => $_SESSION['search_emaillog_data']['s_time_range']
        );


        if ($arg1 == 'export' && $format != '') {
            $is_file_downloaded = true;
        }

        if ($is_file_downloaded === false) {
            /*             * **** pagination code start here ********* */
            $pagination_uri_segment = 3;

            if (isset($_SESSION['search_emaillog_data']['s_no_of_records']) && $_SESSION['search_emaillog_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_emaillog_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }

            $log_result = $this->log_mod->email_get_data($order_by, $per_page, $segment, $search_data);

            $total = $this->log_mod->total_count;


            $config = array();
            $config = $this->utils_model->setup_pagination_option($total, 'recyclebin/email_log', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);

            /*             * **** pagination code ends  here ********* */
            $data['pagination'] = $this->pagination->create_links();
            $data['data'] = $log_result;
            $data['total_records'] = $total;



            //echo '<pre>';print_r($report_search_data );echo '<pre>'; //die('11111');		

            $data['type_options'] = $this->log_mod->get_email_get_types();

            $this->load->view('basic/header', $data);
            $this->load->view('log/email_log', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function ajax_email_log() {
        $email_log_id = trim($_POST['email_log_id']);
        $search_data = array(
            'email_log_id' => $email_log_id
        );
        $result = $this->log_mod->email_get_data('', '', '', $search_data);


        if ($result['status'] = 'success')
            $return = current($result['result']);
        else
            $return = 'error';

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

}
