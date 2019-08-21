<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0
// License https://www.gnu.org/licenses/agpl-3.0.html
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
<!DOCTYPE html>
<!--Please use another username and value reset with original value.-->
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?php echo "OV500 Billing & Routing Software"; ?></title>
        <meta name="description" content="OV500 Billing & Routing Software">
        <meta name="author" content="Chinna Technologies">

        <!-- Bootstrap -->
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
            <!-- <link href="<?php echo base_url() ?>theme/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">-->
        <!-- NProgress -->
        <!--<link href="<?php echo base_url() ?>theme/vendors/nprogress/nprogress.css" rel="stylesheet">-->
        <!-- Animate.css -->
        <link href="<?php echo base_url() ?>theme/vendors/animate.css/animate.min.css" rel="stylesheet">
        <!-- Custom Theme Style -->
        <link href="<?php echo base_url() ?>theme/default/css/custom.css" rel="stylesheet">
        <style>.login{ background-color:#ffffff;}.logo{ margin:0px 0px 10px 70px;}</style>
    </head>

    <body class="login">
        <?php
        $logo = 'logo.png';
        if (isset($err_msgs) && $err_msgs != '') {
            ?><div class="alert alert-danger alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                </button>
                <?php echo $err_msgs; ?>
            </div>

        <?php }
        ?>
        <div>
            <a class="hiddenanchor" id="signup"></a>
            <a class="hiddenanchor" id="signin"></a>

            <div class="login_wrapper">
                <div class="animate form login_form">

                    <div class="ovlogo"><img class="rounded" style="width: 300px;" src="<?php echo base_url() ?>theme/default/images/<?php echo $logo; ?>" /></div>
                    <section class="login_content">

                        <form class="form with-margin" name="login-form" id="login-form" method="post" action="<?php echo base_url(); ?>login">
                                <label class="control-label col-md-12 col-sm-12 col-xs-12">OV500 Billing & Switching Portal</label>
                            <input type="hidden" name="action" value="login">
                            <?php
                            if (isset($_REQUEST['redirect'])) {
                                ?>
                                <input type="hidden" name="redirect" id="redirect" value="<?php echo htmlspecialchars($_REQUEST['redirect']); ?>">
                            <?php }
                            ?>


                            <div>
                                <input type="text" name="login" id="login" class="form-control" placeholder="Username" required=""  />
                            </div>
                            <div>
                                <input type="password" name="pass" id="pass" class="form-control" placeholder="Password" required="" />
                            </div>
                            <div>
                                <button type="submit" id="login_button" class="btn btn-primary">Login</button>
                            </div>

                            <div class="clearfix"></div>

                            <div class="separator">
                              <!--<p class="change_link"><a href="#signup" class="to_register"> Forgotten Password </a>    </p>-->

                                <div class="clearfix"></div>
                                <br />

                                <div>
              <p>Copyright © <?php echo date('Y'); ?> <a href="https://ov500.openvoips.org" target="_blank">OV500 Billing & Switching Software</a> (Chinna Technologies). All Rights Reserved  </p>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>

                <div id="register" class="animate form registration_form">
                    <section class="login_content">
                        <form>
                            <div class="logo"><img class="rounded" src="<?php echo base_url() ?>theme/default/images/<?php echo $logo; ?>" /></div>
                            <div>
                                <input type="text" class="form-control" placeholder="Username Or Email" required="" />
                            </div>

                            <div>
                                <button type="button" class="btn btn-primary">Submit</button>
                            </div>

                            <div class="clearfix"></div>

                            <div class="separator">
                                <p class="change_link">Already a member ?
                                    <a href="#signin" class="to_register"> Log in </a>
                                </p>

                                <div class="clearfix"></div>
                                <br />

                                <div>
                                    <p>Copyright©<?php echo date('Y'); ?> <a href="https://ov500.openvoips.org" target="_blank">OV500 Billing & Switching Software</a> (Chinna Technologies). All Rights Reserved  </p>
       
    
                                   
                                </div>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>



        <script src="<?php echo base_url() ?>theme/vendors/jquery/dist/jquery.min.js"></script>

        <script>
            $(document).ready(function () {
                $('#login-form').submit(function (event)
                {
                    //event.preventDefault();
                    var error_message = '';

                    var login = $('#login').val();
                    var pass = $('#pass').val();

                    if (!login || login.length == 0)
                    {
                        //$('#login-block').removeBlockMessages().blockMessage('Please enter your user name', {type: 'warning'});
                        error_message = 'Please enter your user name';
                    } else if (!pass || pass.length == 0)
                    {
                        //$('#login-block').removeBlockMessages().blockMessage('Please enter your password', {type: 'warning'});
                        error_message = 'Please enter your password'
                    } else
                    {
                        //$('#login-form').submit();

                    }

                    if (error_message != '')
                    {
                        event.preventDefault();
                    }

                    //alert(login);
                });
            });
        </script>		



    </body>
</html>
