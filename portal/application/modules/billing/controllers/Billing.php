<?php

/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__) . "/Billingapi.php");

class Billing extends Billingapi {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->helper('Billing_helper');
    }

    public function index() {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->load->view('basic/header');
        $this->load->view('billing');
        $this->load->view('basic/footer');
    }
}
