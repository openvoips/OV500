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
<?php
$current_timestamp = time() * 1000;
?>
<script>
    var site_subdomain = "<?php echo SITE_SUBDOMAIN; ?>";
    var current_timestamp_t = "<?php echo $current_timestamp; ?>";
    var current_timestamp = parseInt(current_timestamp_t);
//console.log(current_timestamp);
    var interval_time = 40000;
    function showTime()
    {
        current_timestamp = current_timestamp + interval_time;

//		var time=moment(current_timestamp).tz("GMT").utcOffset("+05:30").format('hh:mm A');
        var time = moment().tz("Europe/London").format('hh:mm A');
        //var time=moment(current_timestamp).tz("BST").format('hh:mm A');			
        $('#id_clock').html('Server Time: ' + time);
        //console.log(time);
    }

    $(document).ready(function () {
        //showTime();	
        //setInterval(showTime, interval_time);
    });
</script> 

<div class="nav_menu">
    <nav>
        <div class="nav toggle">
            <a id="menu_toggle"><i class="fa fa-bars"></i></a>
        </div>
        <div class="clockOuter"><div id="id_clock" class="clock"></div></div>

        <ul class="nav navbar-nav navbar-right">
            <li class="">
                <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-user"></i> <?php echo get_account_full_name(); ?>
                    <span class=" fa fa-angle-down"></span>
                </a>
                <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="<?php echo base_url() ?>profile"><i class="glyphicon glyphicon-user pull-right"></i> Profile</a></li>
                    <li><a href="<?php echo base_url() ?>logout"><i class="glyphicon glyphicon-off pull-right"></i> Log Out</a></li>
                </ul>
            </li>




            <?php
            if (isset($_SESSION['customer']) && count($_SESSION['customer']) > 1) {
                ?>
                <li role="presentation" class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-users"></i> <?php echo count($_SESSION['customer']) . ' users'; ?>
                    </a>
                    <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                        <?php
                        $topstr = $str = '';
                        foreach ($_SESSION['customer'] as $account_array) {
                            if ($account_array['session_account_id'] == $_SESSION['session_current_customer_id']) {
                                $session_account_type = $account_array['session_account_type'];
                                $topstr .= '<li style="padding: 0px;">'
                                        . '<div class="alert alert-success" style="margin-bottom: 0px;padding: 3px 10px;">'
                                        . '<strong>' . $account_array['session_fullname'] . '</strong>'
                                        . '<p>' . $session_account_type . '</p>'
                                        . '</div>
								</li>';
                            }                            else {
                                $unswitch_link = base_url() . 'admins/unswitch_user/' . param_encrypt($account_array['session_account_id']);
                                $session_account_type = $account_array['session_account_type'];                          

                                $str .= '<li style="padding: 0px;">'
                                        . '<div class="alert" style="margin-bottom: 0px;padding: 3px 10px;">'
                                        . '<button type="button" class="close" onclick="location.href=\'' . $unswitch_link . '\'"  ><span aria-hidden="true">x</span></button>'
                                        . '<a href="' . base_url() . 'admins/switch_user/' . param_encrypt($account_array['session_account_id']) . '" style="padding: 0px !important;">'
                                        . '<strong>' . $account_array['session_fullname'] . '</strong>'
                                        . '<p>' . $session_account_type . '</p></a>'
                                        . '</div>
								</li>';
                            }
                        }
                        echo $topstr . $str;
                        ?>


                    <?php } ?>
                </ul>
                </nav>
                </div>