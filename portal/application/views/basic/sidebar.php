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
<div class="left_col scroll-view">
    <div class="navbar nav_title" style="border: 0;">
        <a href="<?php echo base_url() ?>dashboard" class="site_title"> <span><img src="<?php echo base_url() ?>theme/default/images/<?php echo LOGO_IMAGE; ?>" style="width:200px;"/></span></a>
    </div>

    <div class="clearfix"></div>    
    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu" >
        <div class="menu_section">
            <ul class="nav side-menu">
                <li><a href="<?php echo base_url() ?>dashboard" title="Home"><i class="fa fa-home"></i>Home</a></li>


                <?php
                // user rates menu

                $menu_user2 = '';
                if (check_logged_account_type(array('CUSTOMER'))) {
                    $class = '';
                    if (in_array($page_name, array('my_rates')))
                        $class = 'class="current-page"';
                    $menu_user2 .= '<li ' . $class . '><a href="' . base_url() . 'MyPlan/' . param_encrypt($_SESSION['session_current_customer_id']) . '">My Plan</a></li>';
                    $menu_user2 .= '<li ' . $class . '><a href="' . base_url() . 'MyRates">Call Rates</a></li>';
                    if (in_array($page_name, array('extension_index', 'extension_edit', 'extension_delete', 'extension_add')))
                        $class = 'class="current-page"';
                    $menu_user2 .= '<li ' . $class . '><a href="' . base_url() . 'endpoints/index/' . param_encrypt($_SESSION['session_current_customer_id']) . '">Devices (SRC & DST Rules)</a></li>';
                    $class = '';
                    if (in_array($page_name, array('did_index', 'did_edit')))
                        $class = 'class="current-page"';
                    $menu_user2 .= '<li ' . $class . '><a href="' . base_url() . 'dids"></i>My DIDs</a></li>';
                    $class = '';

                    if ($menu_user2 != '') {
                        echo '<li><a><i class="fa  fa-registered" aria-hidden="true"></i>Rates & System Config<span class="fa fa-chevron-down"></span></a><ul class="nav child_menu">';
                        echo $menu_user2;
                        echo '</ul></li>';
                    }
                }
                // Reseller Rate Menu
                $menu_my_rates = '';
                if (check_logged_account_type(array('RESELLER'))) {
                    $class = '';
                    if (in_array($page_name, array('my_rates')))
                        $class = 'class="current-page"';
                    $menu_my_rates .= '<li ' . $class . '><a href="' . base_url() . 'MyPackage/' . param_encrypt($_SESSION['session_current_customer_id']) . '">My Package</a></li>';

                    $menu_my_rates .= '<li ' . $class . '><a href="' . base_url() . 'MyRates">Call Rates</a></li>';
                    $menu_my_rates .= '<li ' . $class . '><a href="' . base_url() . 'endpoints/index/' . param_encrypt($_SESSION['session_current_customer_id']) . '/' . param_encrypt('RESELLER') . '">SRC & DST Rules</a></li>';
                    if ($menu_my_rates != '') {
                        echo '<li><a><i class="fa  fa-registered" aria-hidden="true"></i>My Package & Rates<span class="fa fa-chevron-down"></span></a><ul class="nav child_menu">';
                        echo $menu_my_rates;
                        echo '</ul></li>';
                    }
                }

                // FOR ADMIN Live Calls and statestics Menu
                $menu_user = '';
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                    if (check_account_permission('reports', 'monin')) {
                        $class = '';
                        if (in_array($page_name, array('monin')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/monin">Live Call Summary</a></li>';
                    }
                    if (check_account_permission('reports', 'CustQOSR')) {
                        $class = '';
                        if (in_array($page_name, array('CustQOSR')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/CustQOSR">Customer QoS Summary</a></li>';
                    }
                    if (check_account_permission('reports', 'CarrQOSR')) {
                        $class = '';
                        if (in_array($page_name, array('CarrQOSR')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/CarrQOSR">Carrier QoS Summary</a></li>';
                    }

                    if ($menu_user != '') {
                        echo '<li><a><i class="fa fa-th-large" aria-hidden="true"></i> Live System Reports<span class="fa  fa-chevron-down"></span></a> <ul class="nav child_menu">';
                        echo $menu_user;
                        echo '</ul></li>';
                    }
                }

                // For All type User if they have Rate, Tariff and Ratecard management access
                $menu_user = '';
                if (get_logged_account_level() < 3 && check_account_permission('ratecard', 'view')) {
                    $class = '';
                    if (in_array($page_name, array('ratecard_index', 'ratecard_add', 'ratecard_edit')))
                        $class = 'class="current-page"';
                    $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'ratecard">Ratecard</a></li>';
                }
                if (get_logged_account_level() < 3 && check_account_permission('rate', 'view')) {
                    $class = '';
                    if (in_array($page_name, array('rate_index', 'rate_add', 'rate_edit')))
                        $class = 'class="current-page"';
                    $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'rates">Rates</a></li>';
                }
                if (get_logged_account_level() < 3 && check_account_permission('tariff', 'view')) {
                    $class = '';
                    if (in_array($page_name, array('tariff_index', 'tariff_add', 'tariff_edit', 'mapping_add', 'mapping_edit')))
                        $class = 'class="current-page"';
                    $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'tariffs">Tariffs</a></li>';
                }

                if ($menu_user != '') {
                    echo '<li><a><i class="fa fa-registered"></i> Rates Management <span class="fa fa-chevron-down"></span></a> <ul class="nav child_menu">';
                    echo $menu_user;
                    echo '</ul></li>';
                }
                //  Provider/Carrier/Routes/Dialplan/ DID Menu for Admin/ Subadmin/ Accounts. DID option is available for all type of user based on access
                $menu_user = '';
                $menu_counter = 0;
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                    $class = '';
                    if (in_array($page_name, array('provider_index', 'provider_add', 'provider_edit')))
                        $class = 'class="current-page"';
                    $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'providers" >Provider</a></li>';
                    if (get_logged_account_level() < 3 && check_account_permission('carrier', 'view')) {
                        $class = '';
                        if (in_array($page_name, array('carrier_index', 'carrier_edit', 'carrier_add', 'carrier_editG', 'carrier_addG', 'carrier_editSRCNo', 'carrier_editDSTNo', 'carrier_editINSRCNo', 'carrier_editINDSTNo')))
                            $class = 'class="current-page"';
                        $menu_counter++;
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'carriers">Carriers</a></li>';
                    }
                    if (get_logged_account_level() < 3 && check_account_permission('routing', 'view')) {
                        $class = '';
                        if (in_array($page_name, array('route_index', 'route_add', 'route_edit')))
                            $class = 'class="current-page"';
                        $menu_counter++;
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'routes">Routes</a></li>';
                    }
                    if (get_logged_account_level() < 3 && check_account_permission('dialplan', 'view')) {
                        $class = '';
                        if (in_array($page_name, array('dialplan_index', 'dialplan_add', 'dialplan_edit')))
                            $class = 'class="current-page"';
                        $menu_counter++;
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'dialplans">Dial Plans</a></li>';
                    }
                }
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'RESELLER'))) {
                    $class = '';
                    if (in_array($page_name, array('did_index', 'did_add', 'did_edit')))
                        $class = 'class="current-page"';
                    $menu_counter++;
                    if ($menu_counter > 1) {
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'dids">Direct Inward dialing</a></li>';
                    } else {
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'dids"><i class="fa fa-exchange"></i> Incoming Numbers</a></li>';
                    }
                }
                if ($menu_user != '') {
                    if ($menu_counter > 1) {
                        echo '<li><a><i class="fa fa-exchange"></i> Routing Management<span class="fa fa-chevron-down"></span></a> <ul class="nav child_menu">';
                        echo $menu_user;
                        echo '</ul></li>';
                    } else {
                        echo $menu_user;
                    }
                }

                // User management  Admin / Reseller / Customer based on access list
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'RESELLER', 'ACCOUNTS'))) {
                    $menu_user = '';
                    if (get_logged_account_level() <= 3 && check_account_permission('customer', 'view')) {
                        $class = '';
                        if (in_array($page_name, array('customer_index', 'customer_edit', 'customer_add', 'customer_ip_add', 'customer_ip_edit', 'customer_sip_add', 'customer_sip_edit', 'customer_editSRCNo', 'customer_dialplan_edit', 'customer_dialplan_add', 'customer_translation_rules_edit', 'account_payment_history', 'customer_editINSRCNo', 'customer_translation_rules_incoming_edit', 'cState', 'statement')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'customers">Customers</a></li>';
                    }
                    if (get_logged_account_level() < 3 && check_account_permission('reseller', 'view')) {
                        $class = '';
                        if (in_array($page_name, array('reseller_index', 'reseller_edit', 'reseller_add', 'reseller_payment_history', 'reseller_editSRCNo', 'reseller_translation_rules_edit', 'reseller_dialplan_add', 'reseller_dialplan_edit', 'reseller_translation_rules_incoming_edit', 'reseller_incoming_editSRCNo', 'r_statement', 'rState')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'resellers" >Resellers</a></li>';
                    }

                    if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                        if (get_logged_account_level() < 3 && check_account_permission('admins', 'view')) {
                            $title = ' System Users';
                            $class = '';
                            if (in_array($page_name, array('account_index', 'account_add_admin', 'account_edit_admin')))
                                $class = 'class="current-page"';
                            $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'admins">' . $title . '</a></li>';
                        }
                    }

                    if ($menu_user != '') {
                        echo '<li><a><i class="fa fa-user"></i> User Management <span class="fa fa-chevron-down"></span></a><ul class="nav child_menu">';
                        echo $menu_user;
                        echo '</ul></li>';
                    }
                }

/// FOR ADMIN
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                    $menu_user = '';
                    if (check_account_permission('reports', 'cdr')) {
                        $class = '';
                        if (in_array($page_name, array('cdr_index')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/AnsCalls">Connected Calls</a></li>';
                    }

                    if (check_account_permission('reports', 'fail_calls')) {
                        $class = '';
                        if (in_array($page_name, array('report_failed')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/FailCalls">Failed Calls</a></li>';
                    }

                    if ($menu_user != '') {
                        echo '<li><a><i class="fa fa-plus-square"></i> Call Detail Reports <span class="fa fa-chevron-down"></span></a><ul class="nav child_menu">';
                        echo $menu_user;
                        echo '</ul></li>';
                    }
                }

                // Reports
                $menu_user = '';
                if (check_logged_account_type(array('CUSTOMER', 'RESELLER'))) {
                    if (check_account_permission('reports', 'cdr')) {
                        $class = '';
                        if (in_array($page_name, array('cdr_index')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/AnsCalls">Connected Calls</a></li>';
                    }
                    if (check_account_permission('reports', 'fail_calls')) {
                        $class = '';
                        if (in_array($page_name, array('report_failed')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/FailCalls">Failed Calls</a></li>';
                    }

                    if (check_account_permission('reports', 'report_statement')) {
                        $class = '';
                        if (in_array($page_name, array('report_statement')))
                            $class = 'class="current-page"';
                        $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'customers/statement">Account Statement</a></li>';
                    }
                }

                /*
                  if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                  $class = '';
                  if (in_array($page_name, array('report_daily_usage')))
                  $class = 'class="current-page"';
                  $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/businesHistory">Daily Usage </a></li>';
                  }
                  if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                  $class = '';
                  if (in_array($page_name, array('CarrierUsage')))
                  $class = 'class="current-page"';
                  $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/CarrierUsage">Carrier Daily Usage</a></li>';
                  }

                 */
                if (check_logged_account_type(array('ADMIN', 'RESELLER', 'SUBADMIN', 'ACCOUNTS'))) {
                    $class = '';
                    if (in_array($page_name, array('ProfitLoss')))
                        $class = 'class="current-page"';
                    $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/ProfitLoss">Profit & Loss</a></li>';
                }

                if (check_logged_account_type(array('ADMIN', 'RESELLER', 'SUBADMIN', 'ACCOUNTS'))) {
                    $class = '';
                    if (in_array($page_name, array('report_topup')))
                        $class = 'class="current-page"';
                    $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/topup">Topup Daily Summary</a></li>';
                }
                if (check_logged_account_type(array('ADMIN', 'RESELLER', 'SUBADMIN', 'ACCOUNTS'))) {
                    $class = '';
                    if (in_array($page_name, array('report_topup_monthly')))
                        $class = 'class="current-page"';
                    $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/topup_monthly">Topup Monthly Summary</a></li>';
                }
                if (check_logged_account_type(array('ADMIN', 'RESELLER', 'SUBADMIN', 'ACCOUNTS'))) {
                    $class = '';
                    if (in_array($page_name, array('CRecharge')))
                        $class = 'class="current-page"';
                    $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/CRecharge">Topup Customer Summary</a></li>';
                }
                /*
                  if (check_logged_account_type(array('ADMIN', 'RESELLER', 'SUBADMIN', 'ACCOUNTS'))) {
                  $class = '';
                  if (in_array($page_name, array('report_daily_sales')))
                  $class = 'class="current-page"';
                  $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/daily_sales">Sales Daily Summary</a></li>';
                  }
                  if (check_logged_account_type(array('ADMIN', 'RESELLER', 'SUBADMIN', 'ACCOUNTS'))) {
                  $class = '';
                  if (in_array($page_name, array('report_daily_sales_monthly')))
                  $class = 'class="current-page"';
                  $menu_user .= '<li ' . $class . '><a href="' . base_url() . 'reports/daily_sales_monthly">Sales Monthly Summary</a></li>';
                  }
                 */
                if (check_logged_account_type(array('ADMIN', 'RESELLER', 'SUBADMIN', 'ACCOUNTS'))) {
                    if ($menu_user != '') {
                        echo '<li><a><i class="fa fa-bar-chart"></i>Business Report<span class="fa fa-chevron-down"></span></a><ul class="nav child_menu">';
                        echo $menu_user;
                        echo '</ul></li>';
                    }
                }

                if (check_logged_account_type(array('CUSTOMER'))) {
                    if ($menu_user != '') {
                        echo '<li><a><i class="fa fa-bar-chart"></i>Calls Report<span class="fa fa-chevron-down"></span></a><ul class="nav child_menu">';
                        echo $menu_user;
                        echo '</ul></li>';
                    }
                }
                ?>

                <?php
                $menu_balance = '';
                if (check_logged_account_type(array('CUSTOMER', 'RESELLER'))) {

                    $class = '';
                    if (in_array($page_name, array('make_payment')))
                        $class = 'class="current-page"';
                    echo '<li><a href="' . base_url() . 'payment/make_payment"><i class="fa fa-money"></i>Make Payment</a></li>';
                }
                // Reseller System Config
                $menu_system = '';
                if (check_logged_account_type(array('RESELLER'))) {
                    $class = '';
                    if (in_array($page_name, array('payment_config')))
                        $class = 'class="current-page"';
                    $menu_system .= '<li ' . $class . '><a href="' . base_url() . 'sysconfig/pGConfig">Payment Gateway Config</a></li>';
                    $class = '';
//                    if (in_array($page_name, array('invoice_config')))
//                        $class = 'class="current-page"';
//                    $menu_system .= '<li ' . $class . '><a href="' . base_url() . 'sysconfig/inConfig">Invoice Config</a></li>';
//                    $class = '';
                    if (in_array($page_name, array('payment_trace')))
                        $class = 'class="current-page"';
                    $menu_system .= '<li ' . $class . '><a href="' . base_url() . 'payment/trace">Payment Tracking</a></li>';
                    if ($menu_system != '') {
                        echo '<li><a><i class="fa fa-gear"></i> System & Services<span class="fa fa-chevron-down"></span></a><ul class="nav child_menu">';
                        echo $menu_system;
                        echo '</ul></li>';
                    }
                }

                // Admin/ Subadmin / Accounts system management
                $menu_system = '';
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                    $class = '';
                    if (in_array($page_name, array('Currency')))
                        $class = 'class="current-page"';
                    $menu_system .= '<li ' . $class . '><a href="' . base_url() . 'currency">Currency & Exchange Rate</a></li>';
                }
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN'))) {
                    $class = '';
                    if (in_array($page_name, array('role_index', 'role_role_permission', 'role_account_permission')))
                        $class = 'class="current-page"';
                    $menu_system .= '<li ' . $class . '><a href="' . base_url() . 'roles" >Roles & Permissions</a></li>';
                }
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                    $class = '';
                    if (in_array($page_name, array('payment_config')))
                        $class = 'class="current-page"';
                    $menu_system .= '<li ' . $class . '><a href="' . base_url() . 'sysconfig/pGConfig">Payment Gateway Config</a></li>';
                }
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                    $class = '';
                    if (in_array($page_name, array('invoice_config')))
                        $class = 'class="current-page"';
                    $menu_system .= '<li ' . $class . '><a href="' . base_url() . 'sysconfig/inConfig">Invoice Config</a></li>';
                }



                if ($menu_system != '') {
                    echo '<li><a><i class="fa fa-gear"></i> System & Services<span class="fa fa-chevron-down"></span></a><ul class="nav child_menu">';
                    echo $menu_system;
                    echo '</ul></li>';
                }
                // Ticket Module  For All type User
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                    $class = '';
                    if (in_array($page_name, array('ticket_index', 'ticket_details', 'ticket_add')))
                        $class = 'class="current-page"';
                    echo '<li ' . $class . '><a href="' . base_url() . 'ticket" id="id_ticket_menu"><i class="fa fa-ticket"></i>Support Ticket</a></li>';
                    echo '<li ' . $class . '>  <a href="https://ov500.openvoips.org/documentation/ov500-switch-introduction-and-documentation/" target="_blank" id="help"><i class="fa fa-book"></i>User Manual</a></li>';
                } elseif (get_logged_account_level() < 2 && check_logged_account_type(array('CUSTOMER', 'RESELLER'))) {
                    $class = '';
                    if (in_array($page_name, array('ticket_index', 'ticket_details', 'ticket_add')))
                        $class = 'class="current-page"';
                    echo '<li ' . $class . '><a href="' . base_url() . 'ticket" id="id_ticket_menu"><i class="fa fa-ticket"></i>Support Ticket</a></li>';
                }
                ?>  
            </ul>
        </div>
    </div>
    <!-- /sidebar menu -->

    <!-- /menu footer buttons -->
    <div class="sidebar-footer hidden-small">
            <!--<a data-toggle="tooltip" data-placement="top" title="Settings" href="<?php echo base_url() ?>sitesetup">
                    <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
            </a>-->
        <a data-toggle="tooltip" data-placement="top" title="Profile"  href="<?php echo base_url() ?>profile">
            <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
        </a>
        <a data-toggle="tooltip" data-placement="top" title="Logout" href="<?php echo base_url() ?>logout">
            <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
        </a>
    </div>
    <!-- /menu footer buttons -->
</div>