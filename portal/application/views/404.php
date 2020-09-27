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

        <title><?php echo $sitesetup_data->site_name; ?></title>
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Bootstrap -->
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Custom Theme Style -->
        <link href="<?php echo base_url() ?>theme/default/css/custom.min.css" rel="stylesheet">

    </head>

    <body class="login">


        <div>

            <div class="login_wrapper">
                <div class="animate form login_form">
                    <section class="login_content">
                        <h1>404</h1>
                        <p class="change_link">Already a member ?
                            <a href="<?php echo base_url(); ?>" class="to_register"> Log in </a>
                        </p>
                    </section><div class="clearfix"></div>
                    <br />

                    <div>
                        <h1><i class="fa fa-paw"></i> <?php echo $sitesetup_data->site_name; ?></h1>
                        <p>©<?php echo date('Y'); ?> All Rights Reserved. Privacy and Terms</p>
                    </div>
                </div>



            </div>
        </div>





    </body>
</html>
