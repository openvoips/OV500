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

class Logout extends MY_Controller {

    function __construct() {
        parent::__construct();
    }

    public function index() {

        $userdata = array(
            'session_current_user_id' => '',
            'session_logged_in' => '',
            'session_user_id' => '',
            'session_account_id' => '',
            'session_fullname' => '',
            'session_user_type' => '',
            'session_email_id' => '',
            'session_permissions' => ''
        );
        $this->session->unset_userdata($userdata);

        $this->session->sess_destroy();

        redirect('');
        //$this->load->view('login'); // load logout page
    }

}
