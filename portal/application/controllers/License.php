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

class License extends MY_Controller {


    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        if (!check_is_loggedin())
            redirect(site_url(), 'refresh');
        if (!check_logged_user_type(array('ADMIN', 'SUBADMIN')))
            show_404('403');
        $this->load->model('license_mod');             
    }

  
    function index($arg1 = '', $format = '') {
        $page_name = "license";
        $data['page_name'] = $page_name;
   
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        
        //ddd($data['license_data']);die;
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('license', 'License', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->license_mod->update_license($_POST['license']);
                //var_dump($result);die;
                if ($result === true) {                    
                    $this->session->set_flashdata('suc_msgs', 'License Updated Successfully');
                    redirect(site_url('license'), 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $data['license_data'] = $this->license_mod->get_license();
        $data['license_history_data'] = $this->license_mod->get_data();

        $this->load->view('basic/header', $data);
        $this->load->view('license/license', $data);
        $this->load->view('basic/footer', $data);
        
    }
}
