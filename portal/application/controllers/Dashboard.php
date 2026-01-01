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
 

class dashboard extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('sitesetup_mod');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
    }

    public function cpu() {
        header('Content-Type: application/json');
        echo json_encode($this->getCpu());
    }

    public function disk() {
        header('Content-Type: application/json');
        echo json_encode($this->getDisk());
    }

    public function memory() {
        header('Content-Type: application/json');
        echo json_encode($this->getMemory());
    }

    public function temperature() {
        header('Content-Type: application/json');
        echo json_encode($this->getTemp());
    }

    function secondsToTime($seconds) {

        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;

        $display = sprintf('%02d', $hours) . ':' . sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds);
        return $display;
    }

    public function documents() {
        $page_name = "dashboard_documents";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->view('basic/header', $data);
        $this->load->view('documents', $data);
        $this->load->view('basic/footer', $data);
    }

    public function index() {
        $page_name = "dashboard_index";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_id = get_logged_account_id();

        if (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
            $option_param = array('balance' => true, 'currency' => true);
            $this->load->model('report_mod');
            $yesterday = date('Y-m-d', strtotime('yesterday'));
            $search_data = array('action_date' => $yesterday);
            $lastday_statement_data = Array();
            $data['statement_data'] = $lastday_statement_data;
            $data['statement_data_date'] = $yesterday;
            $data['sdr_terms'] = $this->utils_model->get_sdr_terms();
            if (check_logged_account_type(array('RESELLER')))
                $search_data['parent_account_id'] = $account_id;
            elseif (check_logged_account_type(array('CUSTOMER')))
                $search_data['user_account_id'] = $account_id;
            $today_statement_data = Array();
            $data['today_call_statistics_data'] = $today_statement_data;
        }


        $this->load->view('basic/header', $data);
        if (get_logged_user_group() == 'CUSTOMER') {
            $this->load->model('dashboard_mod');
            $result = $this->dashboard_mod->get_customer_dashboard();
            $data['ddata'] = $result['result'];
            $this->load->view('dashboard_customer', $data);
        } else if (get_logged_user_group() == 'RESELLER') {
            $this->load->model('dashboard_mod');
            $result = $this->dashboard_mod->get_reseller_dashboard();
            $data['ddata'] = $result['result'];
            $this->load->view('dashboard_reseller', $data);
        } else {
            $this->load->view('dashboard', $data);
        }
        $this->load->view('basic/footer', $data);
    }

    public function ajax_get_calls() {

        $month_start_date = date('Y-m-01 00:00:00');
        $month_end_date = date('Y-m-31 23:59:59');

        $DB1 = $this->load->database('cdrdb', true);
        $table = date('Ym') . "_ratedcdr";
        //month
        $logged_account_id = get_logged_account_id();
        if ($_GET['month'] == 'Y') {
            $sql = "SELECT HIGH_PRIORITY SUM(customer_duration) duration,count(customer_duration) calls 
			FROM $table  
			WHERE 
			 customer_account_id ='" . $logged_account_id . "'";
            // echo $sql;
            $query = $DB1->query($sql);
            $month_calls1 = $query->result_array();
            $month_calls = $month_calls1[0];

            $month_data = '';
            $month_data = '<h2 class="text-center1">This Month</h2> <h4>Calls: ' . (int) $month_calls['calls'] . '</h4> <h4>Duration: ' . $this->secondsToTime((int) $month_calls['duration']) . '</h4>';

            $return['month_data'] = $month_data;
        }

        //today
        if ($_GET['today'] == 'Y') {
            $today_start_date = date('Y-m-d 00:00:00');
            $today_end_date = date('Y-m-d 23:59:59');

            $sql = "SELECT SUM(customer_duration) duration,count(customer_duration) calls 
			FROM $table  
			WHERE customer_account_id ='" . $logged_account_id . "'
			 AND DATE(end_time) = CURDATE()";
            //  echo $sql;
            $query = $DB1->query($sql);
            $today_calls1 = $query->result_array();
            $today_calls = $today_calls1[0];
            $today_data = '<h2 class="text-center1">Today</h2>'
                    . '<h4>Calls: ' . (int) $today_calls['calls'] . '</h4>'
                    . '<h4>Duration: ' . $this->secondsToTime((int) $today_calls['duration']) . '</h4>';

            $return['today_data'] = $today_data;
        }

        //active
        if ($_GET['active'] == 'Y') {
            $sql = "SELECT count(id) calls FROM livecalls  WHERE customer_account_id ='" . $logged_account_id . "' ";
            // AND answer_time IS NOT NULL";
            // echo $sql;
            $query = $this->db->query($sql);
            $active_calls = $query->row_array();

            $active_data = ' <h2 class="text-center1">Active Calls</h2>'
                    . '<h4 class="text-center">' . (int) $active_calls['calls'] . '</h4>'
                    . '<h4 class="text-center">&nbsp;</h4>';

            $return['active_data'] = $active_data;
        } {
            $sql = "SELECT balance, credit_limit from  customer_balance where account_id ='$logged_account_id'";

            $query = $this->db->query($sql);
            $balance_row = $query->row_array();

            $balance = 0 - $balance_row['balance'];
            $str = '<h2 class="text-center1">Finances</h2>'
                    . '<h3>Transaction: $' . number_format($balance, 2) . '</h3>'
                    . '<h4>Credit Limit: $' . number_format($balance_row['credit_limit'], 2) . '</h4>'
                    . '<h9>Portal Balance may be Fictitious!</h9>';
            $return['balance'] = $str;
        }
        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

    public function ajax_get_calls_r() {

        $month_start_date = date('Y-m-01 00:00:00');
        $month_end_date = date('Y-m-31 23:59:59');

        $DB1 = $this->load->database('cdrdb', true);
        $table = date('Ym') . "_ratedcdr";
        //month
        $logged_account_id = get_logged_account_id();

        $logindata = get_logged_data();
        $filter_field = 'reseller1_account_id';
        $callcost_field = 'reseller1_callcost_total';

        $reseller_duration = "reseller1_duration";
        if ($logindata['account_type'] == 'RESELLER') {
            $filter_field = "reseller" . $logindata['account_level'] . "_account_id";
            $callcost_field = "reseller" . $logindata['account_level'] . "_callcost_total";
            $reseller_duration = "reseller" . $logindata['account_level'] . "_duration";
        }



        if ($_GET['month'] == 'Y') {
            $sql = "SELECT HIGH_PRIORITY SUM($reseller_duration) duration, count(id) calls 
			FROM $table  
			WHERE 
			 $filter_field ='" . $logged_account_id . "'";

            $query = $DB1->query($sql);
            $month_calls1 = $query->result_array();
            $month_calls = $month_calls1[0];

            $month_data = '';
            $month_data = '<h2 class="text-center1">This Month</h2> <h4>Calls: ' . (int) $month_calls['calls'] . '</h4> <h4>Duration: ' . $this->secondsToTime((int) $month_calls['duration']) . '</h4>';

            $return['month_data'] = $month_data;
        }

        //today
        if ($_GET['today'] == 'Y') {
            $today_start_date = date('Y-m-d 00:00:00');
            $today_end_date = date('Y-m-d 23:59:59');

            $sql = "SELECT SUM($reseller_duration) duration,count(id) calls 
			FROM $table  
			WHERE $filter_field ='" . $logged_account_id . "'
			 AND DATE(end_time) = CURDATE()";
            //  echo $sql;
            $query = $DB1->query($sql);
            $today_calls1 = $query->result_array();
            $today_calls = $today_calls1[0];
            $today_data = '<h2 class="text-center1">Today</h2>'
                    . '<h4>Calls: ' . (int) $today_calls['calls'] . '</h4>'
                    . '<h4>Duration: ' . $this->secondsToTime((int) $today_calls['duration']) . '</h4>';

            $return['today_data'] = $today_data;
        }

        //active
        if ($_GET['active'] == 'Y') {
            $sql = "SELECT count(id) calls FROM livecalls  WHERE $filter_field ='" . $logged_account_id . "' ";
            // AND answer_time IS NOT NULL";
            // echo $sql;
            $query = $this->db->query($sql);
            $active_calls = $query->row_array();

            $active_data = ' <h2 class="text-center1">Active Calls</h2>'
                    . '<h4 class="text-center">' . (int) $active_calls['calls'] . '</h4>'
                    . '<h4 class="text-center">&nbsp;</h4>';

            $return['active_data'] = $active_data;
        } {
            $sql = "SELECT balance, credit_limit from  customer_balance where account_id ='$logged_account_id'";

            $query = $this->db->query($sql);
            $balance_row = $query->row_array();

            $balance = 0 - $balance_row['balance'];
            $str = '<h2 class="text-center1">Finances</h2>'
                    . '<h3>Transaction: $' . number_format($balance, 2) . '</h3>'
                    . '<h4>Credit Limit: $' . number_format($balance_row['credit_limit'], 2) . '</h4>'
                    . '<h9>Portal Balance may be Fictitious!</h9>';
            $return['balance'] = $str;
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

    
      
    
    
    
    
    
      static function getTemp() {
        $obj = new stdClass();
        $cmd = "cat /sys/class/thermal/thermal_zone0/temp";
        exec($cmd . "  2>&1", $output, $return_val);
        if ($return_val !== 0) {
            $obj->error = "Get t°  ERROR** " . print_r($output, true);
            $obj->command = $cmd;
        } else {
            $obj->title = "t° Raspberry";
            $obj->success = 1;
            $obj->output = $output;
            $obj->command = $cmd;
            $obj->percent = 0;
            $obj->percent = $output[0] / 1000;
//            $obj->percent = intval(self::getServerLoad());
        }
        return $obj;
    }

    static function getCpu() {
        $obj = new stdClass();
        $cmd = "cat /proc/cpuinfo";
        exec($cmd . "  2>&1", $output, $return_val);
        if ($return_val !== 0) {
            $obj->error = "Get CPU ERROR** " . print_r($output, true);
            $obj->command = $cmd;
        } else {
            $obj->title = "";
            $obj->success = 1;
            $obj->output = $output;
            $obj->command = $cmd;
            $obj->percent = 0;
            $obj->percent = intval(self::getServerLoad());
            // find model name
            foreach ($output as $value) {
                if (preg_match("/model name.+:(.*)/i", $value, $match)) {
                    $obj->title = $match[1];
                    break;
                }
            }
        }
        return $obj;
    }

    static function getMemory() {
        $obj = new stdClass();
        $cmd = "free";
        exec($cmd . "  2>&1", $output, $return_val);
        if ($return_val !== 0) {
            $obj->error = "Get Memmory ERROR** " . print_r($output, true);
            $obj->command = $cmd;
        } else {
            $obj->title = "";
            $obj->success = 1;
            $obj->output = $output;
            $obj->command = $cmd;
            $obj->memTotalBytes = 0;
            $obj->memUsedBytes = 0;
            $obj->memFreeBytes = 0;
            if (preg_match("/Mem: *([0-9]+) *([0-9]+) *([0-9]+) */i", $output[1], $match)) {
                $obj->memTotalBytes = $match[1] * 1024;
                $obj->memUsedBytes = $match[2] * 1024;
                $obj->memFreeBytes = $match[3] * 1024;
                $onePc = $obj->memTotalBytes / 100;
                $obj->memTotal = self::humanFileSize($obj->memTotalBytes);
                $obj->memUsed = self::humanFileSize($obj->memUsedBytes);
                $obj->memFree = self::humanFileSize($obj->memFreeBytes);
                $obj->percent = intval($obj->memUsedBytes / $onePc);
                $obj->title = "Total: {$obj->memTotal} | Free: {$obj->memFree} | Used: {$obj->memUsed}";
            }
        }
        return $obj;
    }

    static function getDisk() {
        $obj = new stdClass();
        $cmd = "df -h";
        exec($cmd . "  2>&1", $output, $return_val);
        if ($return_val !== 0) {
            $obj->error = "Get Disk ERROR** " . print_r($output, true);
            $obj->command = $cmd;
        } else {
            $obj->percent = 0;
            foreach ($output as $value) {
                if (preg_match("/([0-9]+)% \/$/i", $value, $match)) {
                    $obj->percent = intval($match[1]);
                    break;
                }
            }
            $obj->title = "Usage of {$obj->percent}%";
            $obj->success = 1;
            $obj->output = $output;
            $obj->command = $cmd;
        }
        return $obj;
    }

    static function humanFileSize($size, $unit = "") {
        if ((!$unit && $size >= 1 << 30) || $unit == "GB")
            return number_format($size / (1 << 30), 2) . "GB";
        if ((!$unit && $size >= 1 << 20) || $unit == "MB")
            return number_format($size / (1 << 20), 2) . "MB";
        if ((!$unit && $size >= 1 << 10) || $unit == "KB")
            return number_format($size / (1 << 10), 2) . "KB";
        return number_format($size) . " bytes";
    }

    static private function _getServerLoadLinuxData() {
        if (is_readable("/proc/stat")) {
            $stats = @file_get_contents("/proc/stat");

            if ($stats !== false) {
                // Remove double spaces to make it easier to extract values with explode()
                $stats = preg_replace("/[[:blank:]]+/", " ", $stats);

                // Separate lines
                $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
                $stats = explode("\n", $stats);

                // Separate values and find line for main CPU load
                foreach ($stats as $statLine) {
                    $statLineData = explode(" ", trim($statLine));

                    // Found!
                    if
                    (
                            (count($statLineData) >= 5) &&
                            ($statLineData[0] == "cpu")
                    ) {
                        return array(
                            $statLineData[1],
                            $statLineData[2],
                            $statLineData[3],
                            $statLineData[4],
                        );
                    }
                }
            }
        }

        return null;
    }

    // Returns server load in percent (just number, without percent sign)
    static function getServerLoad() {
        $load = null;

        if (stristr(PHP_OS, "win")) {
            $cmd = "wmic cpu get loadpercentage /all";
            @exec($cmd, $output);

            if ($output) {
                foreach ($output as $line) {
                    if ($line && preg_match("/^[0-9]+\$/", $line)) {
                        $load = $line;
                        break;
                    }
                }
            }
        } else {
            if (is_readable("/proc/stat")) {
                // Collect 2 samples - each with 1 second period
                // See: https://de.wikipedia.org/wiki/Load#Der_Load_Average_auf_Unix-Systemen
                $statData1 = self::_getServerLoadLinuxData();
                sleep(1);
                $statData2 = self::_getServerLoadLinuxData();

                if
                (
                        (!is_null($statData1)) &&
                        (!is_null($statData2))
                ) {
                    // Get difference
                    $statData2[0] -= $statData1[0];
                    $statData2[1] -= $statData1[1];
                    $statData2[2] -= $statData1[2];
                    $statData2[3] -= $statData1[3];

                    // Sum up the 4 values for User, Nice, System and Idle and calculate
                    // the percentage of idle time (which is part of the 4 values!)
                    $cpuTime = $statData2[0] + $statData2[1] + $statData2[2] + $statData2[3];

                    // Invert percentage to get CPU time, not idle time
                    $load = 100 - ($statData2[3] * 100 / $cpuTime);
                }
            }
        }

        return $load;
    }
    
    
}
