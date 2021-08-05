
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>Billing & Switch Software</title>
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet"/>
        <link href="<?php echo base_url() ?>theme/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet"/>
        <link href="<?php echo base_url() ?>theme/default/css/custom.css?v=1" rel="stylesheet"/>        
        <script src="<?php echo base_url() ?>theme/vendors/jquery/dist/jquery.min.js"></script>
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