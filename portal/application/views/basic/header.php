<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0.3
// License https://www.gnu.org/licenses/agpl-3.0.html
//
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
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <!--<title><?php  echo $sitesetup_data['site_name']; ?></title>-->
  <title>Billing & Switch Software</title>
        <!-- Bootstrap -->
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap/dist/css/bootstrap.css" rel="stylesheet"/>
        <!-- Font Awesome -->
        <link href="<?php echo base_url() ?>theme/vendors/font-awesome/css/font-awesome.css" rel="stylesheet"/>
        <!-- NProgress -->
        <link href="<?php echo base_url() ?>theme/vendors/nprogress/nprogress.css" rel="stylesheet"/>
        <!-- iCheck -->
        <!--<link href="<?php echo base_url() ?>theme/vendors/iCheck/skins/flat/green.css" rel="stylesheet">-->

        <!-- bootstrap-progressbar -->
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet"/>

        <!-- bootstrap-daterangepicker -->
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet"/>

        <!-- jQuery custom content scroller -->
        <link href="<?php echo base_url() ?>theme/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet"/>
        <!-- Custom Theme Style -->
        <link href="<?php echo base_url() ?>theme/default/css/custom.css" rel="stylesheet"/>

        <!-- PNotify -->
        <link href="<?php echo base_url() ?>theme/vendors/pnotify/dist/pnotify.css" rel="stylesheet"/>
        <link href="<?php echo base_url() ?>theme/vendors/pnotify/dist/pnotify.buttons.css" rel="stylesheet"/>
        <link href="<?php echo base_url() ?>theme/vendors/pnotify/dist/pnotify.nonblock.css" rel="stylesheet"/>

        <!-- Datatables -->
        <link href="<?php echo base_url() ?>theme/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet"/>    
        <!-- jQuery -->
        <script src="<?php echo base_url() ?>theme/vendors/jquery/dist/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="<?php echo base_url() ?>theme/vendors/bootstrap/dist/js/bootstrap.min.js"></script>

    </head>
    <body class="nav-md">
        <script>BASE_URL = "<?php echo base_url(); ?>";</script>
        <div class="container body">
            <div class="main_container">
                <div class="col-md-3 left_col menu_fixed">        
                    <?php $this->load->view('basic/sidebar.php'); //,$data ?>
                </div>

                <!-- top navigation -->
                <div class="top_nav">
                    <?php $this->load->view('basic/topmenu.php'); //,$data ?>
                </div>
                <!-- top navigation -->

                <div class="right_col" role="main">             
                    <?php
                    $logged_user_status = get_logged_account_status();
                    if ($logged_user_status == '-1') {
                        echo '<div class="alert alert-warning alert-dismissible fade in" role="alert" style="margin-top:60px;">
                		<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>'
                        . 'Account needs approval'
                        . '</div>';
                    } elseif ($logged_user_status == '-2') {
                        echo '<div class="alert alert-warning alert-dismissible fade in" role="alert" style="margin-top:60px;">'
                        . 'Account is Temporarily Suspended. Please ' . anchor('payment/make_payment', 'make payment') . ' to make it active again'
                        . '</div>';
                    }


                    $error_message = $success_message = '';
                    if (isset($err_msgs) && $err_msgs != '') {
                        $error_message = $err_msgs;
                    } else {
                        $err_msgs = $this->session->flashdata('err_msgs');
                        if (!empty($err_msgs)) {
                            $error_message = $err_msgs;
                        }
                    }

                    if (isset($suc_msgs) && $suc_msgs != '') {
                        $success_message = $suc_msgs;
                    } else {
                        $suc_msgs = $this->session->flashdata('suc_msgs');
                        if (!empty($suc_msgs)) {
                            $success_message = $suc_msgs;
                        }
                    }

                    if ($error_message != '') {
                        echo '<div class="alert alert-danger alert-dismissible fade in" role="alert" style="margin-top:60px;">
                		<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>'
                        . $error_message
                        . '</div>';
                    }
                    if ($success_message != '') {
                        echo '<div class="alert alert-success alert-dismissible fade in" role="alert" style="margin-top:60px;">
                		<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>'
                        . $success_message
                        . '</div>';
                    }

                    $flash_msgs = $this->session->flashdata('flash_msgs');
                    if (!empty($flash_msgs)) {
                        echo $flash_msgs;
                    }
                    ?>