<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2023 
 * http://www.openvoips.com 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends MY_Controller {

    public $initial_fetch_partners = 12;

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');

        if (!check_is_loggedin())
            redirect(site_url(), 'refresh');


        $this->account_id = get_logged_account_id();
    }

    function missed_calls() {//$_POST['action']="fetchEvents"; $_POST['year']= "2024";
        $response = array();

        $logged_account_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $logged_account_level = get_logged_account_level();
        $dd = date('Ym');
        $DB1 = $this->load->database('cdrdb', true);
        $sql = "select count(id) calls, disposition, disposition_cause from " . $dd . "_ratedcdr   where date(end_time )  =  CURDATE() and cdr_type  = 'IN'  and customer_account_id = '" . $logged_account_id . "' GROUP BY disposition, disposition_cause ";

        // $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($sql);
        $usage = $result->result_array();

        $ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        $logged_user_group = $success_message = $error_message = '';

        $html = "";

        $i = 0;
        foreach ($usage as $usagedata_array) {
            $link = site_url('campaign/reports/connectedcalls') . '?disposition=' . $usagedata_array['disposition'];
            if ($i == 0) {
                $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:100%;border-bottom: 1px solid #E0E0E0;font-size:10px; padding: 0px 2px !important;'>
					<a href='$link'><b>" . $usagedata_array['disposition_cause'] . " : <span style='color:red;'>" . $usagedata_array['calls'] . "</span></b></a>
				</li>";
            } else {
                $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:90%;margin:2px; padding:2px;line-height:10px;border-bottom: 1px solid #E0E0E0;font-size:10px; padding: 0px 2px !important;'>
						<a href='$link'><b>" . $usagedata_array['disposition_cause'] . " : <span style='color:red;'>" . $usagedata_array['calls'] . "</span></b></a>
						</li>";
            }

            // $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:100%'><b>".$usagedata_array['disposition']." -  ".$usagedata_array['disposition_cause']." : <span style='color:red;'>".$usagedata_array['calls']."</span></b></li>";
            //   $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:90%;margin:8px; padding:2px;line-height:40px;border-bottom: 1px solid #E0E0E0;'><b>Total Missed Calls on all numbers : <span style='color:red;'>5 '.$logged_account_id.'</span></b></li>";
            //   $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:90%;margin:8px; padding:2px;line-height:40px;border-bottom: 1px solid #E0E0E0;'><b>Total Missed Calls on all numbers : <span style='color:green;'>5</span></b></li>";
            $i++;
        }

        echo $html;
    }

    public function ajax_get_tariff() {

        $ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        if (isset($_POST['action']) && trim($_POST['action']) == 'get_tariffs') {
            
        } else {
            // die;
        }
        if (!$ajax) {
            // die;
        }

        $currency_id = trim($_REQUEST['currency_id']); //the type selected
        //$account_id = trim($_REQUEST['account_id']);
        $existing_value = trim($_REQUEST['existing_value']);

        $logged_user_type = get_logged_user_group();
        $tariff_options = $this->utils_model->get_tariffs($logged_user_type, 'CARRIER');
        //ddd($tariff_options);die;
        $option_html = '';

        if (count($tariff_options) > 0) {
            foreach ($tariff_options as $tariff_row) {
                if ($tariff_row['tariff_currency_id'] != $currency_id)
                    continue;
                $selected = '';
                if ($existing_value == $tariff_row['tariff_id'])
                    $selected = 'selected';
                $option_html .= '<option value="' . $tariff_row['tariff_id'] . '" ' . $selected . '>' . $tariff_row['tariff_name'] . '</option>';
            }
        }
        if ($option_html == '')
            $option_html = '<option value="">--No Tariff Found--</option>';
        else
            $option_html = '<option value="">--Select--</option>' . $option_html;

        $str = $option_html;

        $final_array['html'] = $str; {
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: ' . date('r', time() + (86400 * 365)));
            header('Content-type: application/json');

            echo json_encode($final_array);
            exit();
        }
    }

}
