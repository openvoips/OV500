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

class Sample extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('sitesetup_mod');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
    }

    public function index() {
        $page_name = "dashboard_index";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_id = get_logged_account_id();

        $this->load->view('basic/header', $data);
        $this->load->view('sample/controller', $data);
        $this->load->view('basic/footer', $data);
    }

    public function list() {
        $page_name = "dashboard_index";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_id = get_logged_account_id();

        $this->load->view('basic/header', $data);
        $this->load->view('sample/list', $data);
        $this->load->view('basic/footer', $data);
    }

    public function add() {
        $page_name = "dashboard_index";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $account_id = get_logged_account_id();

        $this->load->view('basic/header', $data);
        $this->load->view('sample/add', $data);
        $this->load->view('basic/footer', $data);
    }

}
