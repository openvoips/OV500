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

defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('sitesetup_mod');
    }

    public function index() {
        $page_code = 'page';
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();


        if (!check_is_loggedin()) {
            $this->load->view('404', $data);
        } else {
            $this->load->view('basic/header', $data);
            $this->load->view('basic/404', $data);
            $this->load->view('basic/footer', $data);
        }
    }

}
