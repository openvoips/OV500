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

class Ajax extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');

        if (!check_is_loggedin())
            redirect(site_url(), 'refresh');
        $this->load->helper('crs_helper');

        $this->account_id = get_logged_account_id();
       
    }


    public function ajax_get_tariff() 
    {
       
        $ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        if (isset($_POST['action']) && trim($_POST['action']) == 'get_tariffs') {
            
        } else {
           // die;
        }
        if (!$ajax) {
           // die;
        }

        $currency_id = trim($_REQUEST['currency_id']); //the type selected
        $account_id = trim($_REQUEST['account_id']);
        $existing_value = trim($_REQUEST['existing_value']); 
        if($currency_id=='' && $account_id=='')
        {
            die;
        }



        $this->load->model('crsvoip_mod');
        $logged_account_id = get_logged_account_id();
        $logged_user_group = get_logged_user_type();

        if($currency_id=='')
        {
            $accountinfo = $this->member_mod->get_account_by_key('account_id', $account_id, array());
            $currency_id = $accountinfo['currency_id'];
        }

        
       // echo $account_id .'--'. $logged_user_group.'---'.get_logged_user_id();
       // ddd($accountinfo);//die;
        //$final_array['accountinfo'] = $accountinfo;

        if ($logged_user_group == 'reseller') {
            $tariff_options = $this->crsvoip_mod->get_tariffs($currency_id, $logged_user_group, 'CUSTOMER', $logged_account_id);
        } else {
            $tariff_options = $this->crsvoip_mod->get_tariffs($currency_id, $logged_user_group, 'CUSTOMER');
        }

        $option_html = '';
        
        
        if (count($tariff_options) > 0) {
            foreach ($tariff_options as $tariff_row) {
                $selected = '';
                if ($existing_value == $tariff_row['tariff_id'])
                    $selected = 'selected';
                $option_html .= '<option value="' . $tariff_row['tariff_id'] . '" ' . $selected . '>' . $tariff_row['tariff_name'] . '</option>';
            }
            $option_html = '<option value="">--Select--</option>' . $option_html;
        }
        else
            $option_html = '<option value="">--No Tariff Found--</option>';
            
        $str = $option_html;
        
        $final_array['html'] = $str; 
        
        {
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: ' . date('r', time() + (86400 * 365)));
            header('Content-type: application/json');

            echo json_encode($final_array);
            exit();
        }

    }





}