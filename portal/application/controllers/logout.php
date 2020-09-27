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

class logout extends CI_Controller {

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
