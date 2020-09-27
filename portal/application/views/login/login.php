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
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>i-Switch</title>

        <!-- Bootstrap -->
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <!--<link href="<?php echo base_url() ?>theme/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">-->
        <!-- NProgress -->
        <!--<link href="<?php echo base_url() ?>theme/vendors/nprogress/nprogress.css" rel="stylesheet">-->
        <!-- Animate.css -->
        <link href="<?php echo base_url() ?>theme/vendors/animate.css/animate.min.css" rel="stylesheet">

        <!-- Custom Theme Style -->
        <link href="<?php echo base_url() ?>theme/default/css/custom.min.css" rel="stylesheet">
        <style>.login{ background-color:#ffffff;}.logo{ margin:0px 0px 10px 70px;}</style>
    </head>

    <body class="login">
        <div>
            <a class="hiddenanchor" id="signup"></a>
            <a class="hiddenanchor" id="signin"></a>

            <div class="login_wrapper">
                <div class="animate form login_form">

                    <div class="logo">
                        <img src="<?php echo base_url() ?>theme/default/images/logo2.png" style="width: 200px;"/>
                    </div>
                    <section class="login_content">
                        <form class="form with-margin" name="login-form" id="login-form" method="post" action="<?php echo base_url(); ?>login/access">
                            <input type="hidden" name="action" value="login">
                            <div><input type="text" name="login" id="login" class="form-control" placeholder="Username" required=""  /></div>
                            <div><input type="password" name="pass" id="pass" class="form-control" placeholder="Password" required="" /></div>
                            <div><button type="submit" id="login_button" class="btn btn-primary">Login</button></div>

                            <div class="separator">
                                <div class="clearfix"></div>
                                <br />

                                <div>
                                    <p>©2019 All Rights Reserved.</p>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>

           
            </div>
        </div>
    </body>
</html>