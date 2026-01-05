<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('sitesetup_mod');
        $this->load->model('login_mod');
    }

    public function dashboard()
    {
        $this->load->view('basic/header', $data);
        $this->load->view('test_dashboard', $data);
        $this->load->view('basic/footer', $data);
    }

    public function session() {
       ddd($_SESSION);
    }

}
