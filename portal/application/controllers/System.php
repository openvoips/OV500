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

class System extends MY_Controller {

    function __construct() {
        parent::__construct();

        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        //$this->output->enable_profiler(ENABLE_PROFILE);
    }

    public function index() {
        $page_name = "server_load";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $account_id = get_logged_account_id();
        $this->load->model('System_mod');

        $data['proxy_data'] = $this->System_mod->get_proxy_server_data();
        $data['switch_data'] = $this->System_mod->get_switch_server_data();
        $data['proxy_switch_data'] = $this->System_mod->get_proxy_switch_server_data();
        $data['customer_calls_data'] = $this->System_mod->get_proxy_customer_calls_data();
        $this->load->view('basic/header', $data);
        $this->load->view('system/system_load');
        $this->load->view('basic/footer', $data);
    }

}
