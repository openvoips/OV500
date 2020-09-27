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

class dashboard extends CI_Controller {

    function __construct() {
        parent::__construct();

        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->output->enable_profiler(ENABLE_PROFILE);
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
        // print_r($_SESSION);


        if (check_logged_account_type(array('RESELLER', 'CUSTOMER'))) {
            $option_param = array('balance' => true, 'currency' => true);
            $user_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
            $data['user_result'] = $user_result;

            //////////////////
            $this->load->model('report_mod');
            $yesterday = date('Y-m-d', strtotime('yesterday'));
            $search_data = array('action_date' => $yesterday);
            $lastday_statement_data = $this->report_mod->sdr_statement($account_id, $search_data);
            $data['statement_data'] = $lastday_statement_data;
            $data['statement_data_date'] = $yesterday;
            $data['sdr_terms'] = $this->utils_model->get_sdr_terms();
            ////////////////////
            /*
              $today = date('Y-m-d',strtotime('today'));
              $search_data = array('action_date'=> $today );
              $lastday_statement_data = $this->report_mod->sdr_statement($account_id,$search_data);
              $data['today_statement_data'] = $lastday_statement_data;
             */


            //////////////call statistics///////
            /* $yearmonth = date("Y-m");			
              $search_data = array('action_month'=> $yearmonth);
              if(check_logged_account_type(array('ACCOUNTMANAGER')))
              $search_data['account_manager']=$account_id;
              elseif(check_logged_account_type(array('RESELLER')))//,'CUSTOMER'
              $search_data['parent_account_id']=$account_id;
              elseif(check_logged_account_type(array('CUSTOMER')))
              $search_data['user_account_id']=$account_id;
              $lastday_statement_data =$this->report_mod->call_statistics($search_data);
              $data['call_statistics_data'] = $lastday_statement_data;
             */

            //////////////today call statistics///////
            /* $yearmonth = date("Y-m-d");			
              $search_data = array('action_date'=> $yearmonth );
              if(check_logged_account_type(array('ACCOUNTMANAGER')))
              $search_data['account_manager']=$account_id;
              elseif(check_logged_account_type(array('RESELLER')))//,'CUSTOMER'
              $search_data['parent_account_id']=$account_id;
              elseif(check_logged_account_type(array('CUSTOMER')))
              $search_data['user_account_id']=$account_id;

              $today_statement_data =$this->report_mod->call_statistics($search_data);
              $data['today_call_statistics_data'] = $today_statement_data; */
        } elseif (check_logged_account_type('CREDITCONTROL')) {
            $this->load->model('Customer_mod');
            $search_data = array('user_status' => '-1');
            $order_by = '';
            $per_page = '';
            $segment = '';
            $option_param = array();
            $endusers_data = $this->Customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            $data['enduser_data'] = $endusers_data;

            //////pending services///////

            $enduser_service_data = $this->Customer_mod->get_pending_services();
            $data['enduser_service_data'] = $enduser_service_data;
            //	echo '<pre>';print_r($enduser_service_data);echo '</pre>';	
        } elseif (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC'))) {

            $total_cli_pending = Array();
            $data['total_cli_pending'] = $total_cli_pending;
        }






        $this->load->view('basic/header', $data);
        $this->load->view('dashboard', $data);
        $this->load->view('basic/footer', $data);
    }

}
