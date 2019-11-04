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

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Api extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('api_mod');
    }

    function index() {
        $data = $_REQUEST;
        try {
            $page_name = "api";
            $result = '';
			$success_message = 'Request is submited';
			$error_message = 'Request submission failed';
			
            if ($data['request'] == 'REMOVEBALANCE' or $data['request'] == 'ADDBALANCE' or $data['request'] == 'ADDCREDIT' or $data['request'] == 'REMOVECREDIT' or $data['request'] == 'ADDTESTBALANCE' or $data['request'] == 'REMOVETESTBALANCE' or $data['request'] == 'BALANCETRANSFERADD' or $data['request'] == 'BALANCETRANSFERREMOVE') {
                $result = $this->api_mod->save_payment($data);
            } elseif ($data['request'] == 'TARIFFCHARGES' or $data['request'] == 'SERVICES' or $data['request'] == 'NEWDIDSETUP' or $data['request'] == 'DIDSETUP' or $data['request'] == 'DIDRENTAL' or $data['request'] == 'DIDCANCEL' or $data['request'] == 'DIDEXTRACHRENTAL') {
                $result = $this->api_mod->didsetupcharge($data);
            }
            if ($result) {
                header('Content-Type: application/json');
                $op = array('status' => 'SUCCESS', 'message' => $success_message);
                echo json_encode($op);
            } else {
                $error_message = $result;
                header('Content-Type: application/json');
                $op = array('status' => 'FAILED', 'message' => $error_message);
                echo json_encode($op);
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            header('Content-Type: application/json');
            $op = array('status' => 'FAILED', 'message' => $error_message);
            echo json_encode($op);
        }
    }

}
