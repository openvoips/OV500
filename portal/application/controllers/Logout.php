<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class logout extends MY_Controller {

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
