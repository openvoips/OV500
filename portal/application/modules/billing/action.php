<?php
/* 
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2023 
 * http://www.openvoips.com 
 */


add_action('update_menu', 'Billing_update_menu_func');

function Billing_update_menu_func($args) {

    if (check_logged_user_group(array('RESELLER', 'SYSTEM'))) {


   $menu_title = 'Billing & Invoices';


  $args[$menu_title] = array(
            'page_name' => array('Pbx_list'),
        );
 

 $args[$menu_title]['Billing/customerinvoice'] = array(
            'page_name' => array('customerinvoice_index', 'customerinvoice_add', 'customerinvoice_edit'),
            'page_url' => 'Billing/customerinvoice',
            'menu_name' => 'Customer Invoice ',
            'icon' => '<i class="fa fa-user-plus"></i>'
        );


   $args[$menu_title]['Billing/inConfig'] = array(
            'page_name' => array('inConfig'),
            'page_url' => 'Billing/inConfig',
            'menu_name' => 'Invoice Config',
            'icon' => '<i class="fa fa-user-plus"></i>'
        );

        $args[$menu_title]['Billing/smtpconfig'] = array(
            'page_name' => array('smtp_config_list', 'smtp_config_add', 'smtp_config_edit'),
            'page_url' => 'Billing/smtpconfig',
            'menu_name' => 'SMTP Configuration',
            'icon' => '<i class="fa fa-user-plus"></i>'
        );
/*
        $args[$menu_title]['Billing/emailtemplate'] = array(
            'page_name' => array('email_template_list', 'email_template_add', 'email_template_edit'),
            'page_url' => 'Billing/emailtemplate',
            'menu_name' => 'EMail Template',
            'icon' => '<i class="fa fa-user-plus"></i>'
        );

*/
   

    }

    if (check_logged_user_group(array('CUSTOMER', 'CUSTOMERADMIN'))) {

    $menu_title = 'Billing & Invoices';
     $args[$menu_title] = array(
                'page_name' => array('Pbx_list'),
                'icon' => '<i class="material-icons">wifi_calling</i>'
            );

     $args[$menu_title]['Billing/customerinvoice'] = array(
                'page_name' => array('customerinvoice_index', 'customerinvoice_add', 'customerinvoice_edit'),
                'page_url' => 'Billing/customerinvoice',
                'menu_name' => 'Invoices ',
                'icon' => '<i class="fa fa-user-plus"></i>'
            );



    }

    return $args;
}

?>