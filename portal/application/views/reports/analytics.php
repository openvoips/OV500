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
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Analytics Reports : Customers(Incoming)</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>reports/analytics">
                <input type="hidden" name="search_action" value="search" />			
                <div class="form-group">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">From Date</label>
                    <div class="col-md-7 col-sm-9 col-xs-12">
                        <input type="text" name="frmtime" id="reservation-time" class="form-control" value="<?php if (isset($_SESSION['search_data']['s_time'])) echo $_SESSION['search_data']['s_time']; ?>" />
                    </div>
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Code</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="frmcode" id="frmcode" value="<?php if (isset($_SESSION['search_data']['s_code'])) echo $_SESSION['search_data']['s_code']; ?>" class="form-control data-search-field" placeholder="Account Code">
                    </div>

                </div>
                <div class="form-group">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Prefix</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="frmprefix" id="frmprefix" value="<?php if (isset($_SESSION['search_data']['s_prefix'])) echo $_SESSION['search_data']['s_prefix']; ?>" class="form-control data-search-field" placeholder="Prefix">
                    </div>	
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Destination</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="frmdest" id="frmdest" value="<?php if (isset($_SESSION['search_data']['s_dest'])) echo $_SESSION['search_data']['s_dest']; ?>" class="form-control data-search-field" placeholder="Destination">
                    </div>					
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Carrier</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <select name="frmcarrier" id="frmcarrier" class="form-control data-search-field">
                            <option value="">ALL</option>
                            <?php foreach ($carrier_data as $k => $v) { ?>								
                                <option value="<?php echo $carrier_data[$k]['carrier_id_name']; ?>" <?php if ($_SESSION['search_data']['s_carrier'] == $carrier_data[$k]['carrier_id_name']) echo 'selected'; ?>><?php echo $carrier_data[$k]['carrier_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                </div>

                <div class="form-group">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Group by</label>
                    <div class="">

                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input type="checkbox" name="g_carrier" <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo 'checked'; ?>> Carrier
                            </label>
                        </div>
                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input type="checkbox" name="g_user" <?php if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') echo 'checked'; ?>> User
                            </label>
                        </div>
                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_date" <?php if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y') echo 'checked'; ?>> Date
                            </label>
                        </div>
                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_hour" <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y') echo 'checked'; ?>> Hour
                            </label>
                        </div>
                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_prefix" <?php if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y') echo 'checked'; ?>> Prefix
                            </label>
                        </div>
                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_dest" <?php if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y') echo 'checked'; ?>> Destination
                            </label>
                        </div>
                    </div>

                    <div class="searchBar ">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" class="btn btn-info" onclick="location.href = '<?php echo base_url(); ?>reports/analytics'">                           
                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="<?php echo base_url() ?>dialplans/index/export/<?php echo param_encrypt('csv'); ?>">CSV</a></li>
                                <li><a href="<?php echo base_url() ?>dialplans/index/export/<?php echo param_encrypt('xlsx'); ?>">XLSX</a></li>
                                <li><a href="<?php echo base_url() ?>dialplans/index/export/<?php echo param_encrypt('xls'); ?>">XLS</a></li>
                                <li><a href="<?php echo base_url() ?>dialplans/index/export/<?php echo param_encrypt('txt'); ?>">TXT</a></li>
                                <li><a href="<?php echo base_url() ?>dialplans/index/export/<?php echo param_encrypt('pdf'); ?>">PDF</a></li>
                            </ul>
                        </div>
                    </div>	
                </div>


            </form>		
        </div>  
    </div>

    <div class="x_panel">
        <div class="x_title">
            <h2>Total Records : <?php if (isset($listing_count)) echo $listing_count;
                            else echo '0'; ?></h2>
<!--            			<ul class="nav navbar-right panel_toolbox">
                                            <li><i class="fa fa-circle" style="color:#FFFF33;"></i> RED</li>
                                            <li><i class="fa fa-circle" style="color:#00FF66;"></i> GREEN</li>
                                            <li><i class="fa fa-circle" style="color:#F14E66;"></i> YEALLOW</li>
                                    </ul>-->
            <div class="clearfix"></div>
        </div>	
        <div class="x_content">
            <div class="table-responsive">
                <table class="table table-striped bulk_action table-bordered">
                    <thead>
                        <tr class="headings thc">
                            <?php if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') echo '<th class="column-title">User</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo '<th class="column-title">Carrier</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y') echo '<th class="column-title">Date</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y') echo '<th class="column-title">Hour</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y') echo '<th class="column-title">Prefix</th>'; ?>
<?php if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y') echo '<th class="column-title">Destination</th>'; ?>
                            <th class="column-title">Total Duration</th>
                            <th class="column-title" width="100">Total Calls</th>
                            <th class="column-title" width="120">Answered Calls</th>
                            <th class="column-title" width="100">ACD</th>
                            <th class="column-title" width="100">ASR</th>
                            <th class="column-title" width="100">Avg PDD</th>
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if (isset($listing_count) && $listing_count > 0) {
                            foreach ($listing_data as $listing_row) {

                                if ($listing_row['asr'] <= 20)
                                    $asr_status = 'alert_red';
                                elseif ($listing_row['asr'] > 20 && $listing_row['asr'] <= 50)
                                    $asr_status = 'alert_yellow';
                                else
                                    $asr_status = 'alert_green';

                                if ($listing_row['acd'] <= 1)
                                    $acd_status = 'alert_red';
                                elseif ($listing_row['acd'] > 1 && $listing_row['acd'] <= 2)
                                    $acd_status = 'alert_yellow';
                                else
                                    $acd_status = 'alert_green';

                                if ($listing_row['pdd'] <= 1)
                                    $pdd_status = 'alert_green';
                                elseif ($listing_row['pdd'] > 1 && $listing_row['pdd'] <= 2)
                                    $pdd_status = 'alert_yellow';
                                else
                                    $pdd_status = 'alert_red';
                                ?>
                                <tr>
        <?php if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') echo '<td>' . $listing_row['account_id'] . '</td>'; ?>
        <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo '<td>' . $listing_row['carrier_id_name'] . '</td>'; ?>

        <?php if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y') echo '<td>' . $listing_row['call_date'] . '</td>'; ?>
        <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y') echo '<td>' . $listing_row['calltime_h'] . '</td>'; ?>
        <?php if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y') echo '<td>' . $listing_row['prefix'] . '</td>'; ?>
        <?php if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y') echo '<td>' . $listing_row['prefix_name'] . '</td>'; ?>
                                    <td><?php echo $listing_row['total_duration']; ?></td>
                                    <td><?php echo $listing_row['total_calls']; ?></td>
                                    <td><?php echo $listing_row['answered_calls']; ?></td>
                                    <td class="<?php echo $acd_status; ?>"><?php echo $listing_row['acd']; ?></td>
                                    <td class="<?php echo $asr_status; ?>"><?php echo $listing_row['asr']; ?></td>
                                    <td class="<?php echo $pdd_status; ?>"><?php echo $listing_row['pdd']; ?></td>
                                </tr>

        <?php
    }
}
else {
    ?>
            <!--<tr>
                    <td colspan="11" align="center"><strong>No Record Found</strong></td>
            </tr>-->
    <?php
}
?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
    .form-horizontal .control-label {
        padding: 7px 5px;
    }
    .alert_yellow{ background-color:#FFFF33; color:#000000;}
    .alert_green{ background-color:#2E8B57; color:#003333;}
    .alert_red{ background-color:#F14E66; color:#ffffff;}
</style>

<script>
    $(document).ready(function () {

        $("#reservation-time").daterangepicker({
            timePicker: !0,
            timePickerIncrement: 30,
            locale: {
                format: "YYYY-MM-DD HH:mm"
            },
            timePicker24Hour: true,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
    });
</script>