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

class dashboard extends MY_Controller {

    function __construct() {
        parent::__construct();

        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        
    }

    public function index() {
        $page_name = "dashboard_index";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_id = get_logged_account_id();
 
        if (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
            $option_param = array('balance' => true, 'currency' => true);
            $user_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
            $data['user_result'] = $user_result;

            $this->load->model('report_mod');
            $yesterday = date('Y-m-d', strtotime('yesterday'));
            $search_data = array('action_date' => $yesterday);
            $lastday_statement_data = $this->report_mod->sdr_statement($account_id, $search_data);
            $data['statement_data'] = $lastday_statement_data;
            $data['statement_data_date'] = $yesterday;
            $data['sdr_terms'] = $this->utils_model->get_sdr_terms();
            if (check_logged_account_type(array('RESELLER')))
                $search_data['parent_account_id'] = $account_id;
            elseif (check_logged_account_type(array('CUSTOMER')))
                $search_data['user_account_id'] = $account_id;
            $today_statement_data = $this->report_mod->call_statistics($search_data);
            $data['today_call_statistics_data'] = $today_statement_data;
        }

        $this->load->view('basic/header', $data);
        $this->load->view('dashboard', $data);
        $this->load->view('basic/footer', $data);
    }

}
