<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title><?php echo SITE_TITEL ?></title>
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
                <link href="<?php echo base_url() ?>theme/vendors/bootstrap/css/bootstrap.css" rel="stylesheet">

                    <link href="<?php echo base_url() ?>theme/vendors/node-waves/waves.css" rel="stylesheet" />
                    <link href="<?php echo base_url() ?>theme/vendors/animate-css/animate.css" rel="stylesheet" />
                    <link href="<?php echo base_url() ?>theme/css/style.css" rel="stylesheet">
                        <link href="<?php echo base_url() ?>theme/css/themes/all-themes.css" rel="stylesheet" />
                        <link href="<?php echo base_url() ?>theme/css/common_style.css" rel="stylesheet">
                            <link href="<?php echo base_url() ?>theme/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet"/>
                            <script src="<?php echo base_url() ?>theme/vendors/jquery/jquery.min.js"></script>
                            <script src="<?php echo base_url() ?>theme/vendors/bootstrap/js/bootstrap.js"></script>
                            <script src="<?php echo base_url() ?>theme/vendors/momentjs/moment.js"></script>
                            <link href="<?php echo base_url() ?>theme/css/custom.css?v=<?php echo rand(); ?>" rel="stylesheet">
                                </head>
                                <body class="theme-bg-light-grey">
                                    <script>BASE_URL = "<?php echo base_url(); ?>";</script>
                                    <!-- Page Loader -->
                                    <div class="page-loader-wrapper">
                                        <div class="loader">
                                            <div class="preloader">
                                                <div class="spinner-layer pl-red">
                                                    <div class="circle-clipper left">
                                                        <div class="circle"></div>
                                                    </div>
                                                    <div class="circle-clipper right">
                                                        <div class="circle"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p>Please wait...</p>
                                        </div>
                                    </div>
                                    <!-- #END# Page Loader -->
                                    <!-- Overlay For Sidebars -->
                                    <div class="overlay"></div>
                                    <!-- #END# Overlay For Sidebars -->

                                    <?php
                                    $logo_url = get_logo();

                                    $logo_url = get_logo();
                                    $logo_html = '<img class="rounded" style="width: 200px; height: 50px;" src="' . $logo_url . '">';
                                    ?>
                                    <!-- Top Bar -->
                                    <nav class="navbar">
                                        <div class="container-fluid">
                                            <div class="navbar-header">
                                                <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                                                <a href="javascript:void(0);" class="bars"></a>
                                                <a class="navbar-brand logo" href="<?php echo base_url(); ?>"><span><?php echo $logo_html; ?> </span><br> </a>
                                            </div>
                                            <div class="collapse navbar-collapse" id="navbar-collapse">                    
                                                <ul class="nav navbar-nav navbar-right">

                                                    <?php
                                                    $session_logged_users = get_logged_users_array();
                                                    if (is_array($session_logged_users) && count($session_logged_users) > 1) {


                                                        $topstr = $str = '';
                                                        $topstr1 = $str1 = '';
                                                        foreach ($session_logged_users as $user_array) {
                                                            if ($user_array['session_user_id'] == $_SESSION['session_current_user_id']) {
                                                                $session_user_type = $user_array['session_user_type'];
                                                                $topstr .= '<li>'
                                                                        . '<div class="icon-circle bg-light-green">'
                                                                        . '<i class="material-icons">person_add</i>'
                                                                        . '</div>'
                                                                        . '<div class="menu-info">'
                                                                        . '<strong>' . $user_array['session_user_name'] . '</strong>'
                                                                        . '<p>' . $session_user_type . '</p>'
                                                                        . '</div>
                                            </li>';
                                                            } else {
                                                                $unswitch_link = base_url() . 'users/unswitch_user/' . param_encrypt($user_array['session_user_id']);
                                                                $session_user_type = $user_array['session_user_type'];

                                                                $str .= '<li>'
                                                                        . '<div class="icon-circle bg-red">'
                                                                        . '<a href="' . $unswitch_link . '">X</a>'
                                                                        . '</div>'
                                                                        . '<div class="menu-info">'
                                                                        . '<a href="' . base_url() . 'users/switch_user/' . param_encrypt($user_array['session_user_id']) . '" style="padding: 0px !important;">'
                                                                        . '<strong>' . $user_array['session_user_name'] . '</strong>'
                                                                        . '<p>' . $session_user_type . '</p></a>'
                                                                        . '</div>
								    </li>';
                                                            }
                                                        }
                                                        //echo $topstr . $str;
                                                        ?>
                                                        <li class="dropdown">
                                                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">
                                                                <i class="material-icons">notifications</i>
                                                                <span class="label-count"></i> <?php echo count($_SESSION['customer']); ?></span>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li class="header">Users</li>
                                                                <li class="body">
                                                                    <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; "><ul class="menu" style="overflow: hidden; width: auto;  ">
                                                                            <?php echo $topstr . $str; ?>                                    
                                                                        </ul><div class="slimScrollBar" style="background: rgba(0, 0, 0, 0.5); width: 4px; position: absolute; top: 0px; opacity: 0.4; display: none; border-radius: 0px; z-index: 99; right: 1px; height: 180.212px;"></div><div class="slimScrollRail" style="width: 4px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 0px; background: rgb(51, 51, 51); opacity: 0.2; z-index: 90; right: 1px;"></div></div>
                                                                </li>

                                                            </ul>
                                                        </li>
                                                        <?php
                                                    }
                                                    ?>


                                                </ul>
                                            </div>
                                        </div>
                                    </nav>
                                    <!-- #Top Bar -->
                                    <section>
                                        <!-- Left Sidebar -->
                                        <aside id="leftsidebar" class="sidebar">
                                            <!-- User Info -->
                                            <div class="user-info">
                                                <div class="user-bg">
                                                    <div class="image">
                                                        <img src="https://forwarding.therealpbx.com/images/user.png" alt="User" width="37" height="37">
                                                    </div>

                                                    <div class="info-container">
                                                        <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">   Welcome <?php echo get_logged_user_name(); ?>   </div>
                                                        <?php if (get_logged_account_id() != 'SYSTEM') { ?>                                         
                                                            <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">    <?php echo "Balance : " . mybalance(); ?>   </div>
                                                        <?php } ?>

                                                        <div class="btn-group user-helper-dropdown">
                                                            <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                                                            <ul class="dropdown-menu pull-right">
                                                                <li><a href="<?php echo site_url('profile') ?>" class="waves-effect waves-block"><i class="material-icons">person</i>Profile </a></li>
                                                                <li role="separator" class="divider"></li>
                                                                <li><a href="<?php echo site_url('logout') ?>" class=" waves-effect waves-block"><i class="material-icons">input</i>Sign Out</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- #User Info -->
                                            <div class="menu">      
                                                <?php $this->load->view('basic/sidebar.php'); //,$data   ?>
                                            </div>
                                    </section>
                                    <section class="content">

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
                                            echo '<div class="alert alert-danger alert-dismissible fade in" role="alert" >
                		<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>'
                                            . $error_message
                                            . '</div>';
                                        }
                                        if ($success_message != '') {
                                            echo '<div class="alert alert-success alert-dismissible fade in" role="alert" >
                		<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>'
                                            . $success_message
                                            . '</div>';
                                        }

                                        $flash_msgs = $this->session->flashdata('flash_msgs');
                                        if (!empty($flash_msgs)) {
                                            echo $flash_msgs;
                                        }
                                        ?>


