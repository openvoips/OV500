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
<?php
$logged_user_type = get_logged_account_type();
$get_logged_account_level = get_logged_account_level();
?>
<!-- Parsley -->
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>CDR</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>cdrs/index/">
                <input type="hidden" name="search_action" value="search" />

                <div class="form-group">
                    <?php if ($logged_user_type != 'CUSTOMER') { ?>
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">UserType</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <select class="form-control" id="user_type" name="user_type">
                                <?php if ($get_logged_account_level == 0) { ?>
                                    <option value="U"  <?php if ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'U') echo 'selected'; ?>>User</option>
                                    <option value="R1" <?php if ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'R1') echo 'selected'; ?>>Reseller 1</option>
                                <?php } ?>
                                <?php if ($get_logged_account_level == 1) { ?>
                                    <option value="U"  <?php if ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'U') echo 'selected'; ?>>User</option>
                                    <option value="R2" <?php if ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'R2') echo 'selected'; ?>>Reseller 2</option>
                                <?php } ?>
                                <?php if ($get_logged_account_level == 2) { ?>
                                    <option value="U"  <?php if ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'U') echo 'selected'; ?>>User</option>
                                    <option value="R3" <?php if ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'R3') echo 'selected'; ?>>Reseller 3</option>
                                <?php } ?>
                                <?php if ($get_logged_account_level == 3) { ?>
                                    <option value="U"  <?php if ($_SESSION['search_cdr_data']['s_cdr_user_type'] == 'U') echo 'selected'; ?>>User</option>
                                <?php } ?>
                            </select>
                        </div>
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Account</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" name="user_account" id="user_account" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_user_account']; ?>" class="form-control data-search-field" placeholder="Account">
                        </div>	
                    <?php } ?>
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Dialed No</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <input type="text" name="dialed_no" id="dialed_no" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_dialed_no']; ?>" class="form-control data-search-field" placeholder=" Dialed No">
                    </div>
                    <?php if (isset($all_field_array['C-DST'])): ?>
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">C-DST No</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" name="carrier_dst_no" id="carrier_dst_no" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_carrier_dst_no']; ?>" class="form-control data-search-field" placeholder="Carrier DST">
                        </div>
                    <?php endif; ?>
                    <BR /><BR />				
                    <?php if (isset($all_field_array['USER-CLI'])): ?>	
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">User CLI</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" name="user_cli" id="user_cli" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_user_cli']; ?>" class="form-control data-search-field" placeholder="User CLI">
                        </div>
                    <?php endif; ?>
                    <?php if (isset($all_field_array['C-CLI'])): ?>	
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">C-CLI</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" name="carrier_cli" id="carrier_cli" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_carrier_cli']; ?>" class="form-control data-search-field" placeholder="Carrier CLI">
                        </div>
                    <?php endif; ?>
                    <?php if (isset($all_field_array['Carrier'])): ?>
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Carrier</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" name="carrier" id="carrier" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_carrier']; ?>" class="form-control data-search-field" placeholder="Carrier">
                        </div>
                    <?php endif; ?>
                    <?php if (isset($all_field_array['C-IP'])): ?>
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Carrier IP</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" name="carrier_ip" id="carrier_ip" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_carrier_ip']; ?>" class="form-control data-search-field" placeholder="Carrier IP">
                        </div>
                    <?php endif; ?>
                    <BR /><BR />

                    <label class="control-label col-md-1 col-sm-3 col-xs-12">User IP</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <input type="text" name="user_ip" id="user_ip" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_user_ip']; ?>" class="form-control data-search-field" placeholder="User IP">
                    </div>

                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Duration</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <input type="text" name="call_duration" id="call_duration" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_call_duration']; ?>" class="form-control data-search-field" placeholder="Duration">
                    </div>

                    <BR /><BR />


                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                    <div class="col-md-5 col-sm-9 col-xs-12">
                        <input type="text" name="time_range" id="time_range" class="form-control " value="<?php if (isset($_SESSION['search_log_data']['s_time_range'])) echo $_SESSION['search_cdr_data']['s_time_range']; ?>" readonly="readonly" data-parsley-required="" />
                    </div>


                    <!--	<label class="control-label col-md-1 col-sm-3 col-xs-12">Start Date</label>
                            <div class="col-md-3 col-sm-9 col-xs-12">
                            <div class="form-group">
                                    <div class='input-group date' id='myDatepicker'>
                                            <input type='text' class="form-control" id="start_dt" name="start_dt" data-parsley-required="" 
                                            value="<?php echo ($_SESSION['search_cdr_data']['s_cdr_start_dt']) ? $_SESSION['search_cdr_data']['s_cdr_start_dt'] : date("Y-m-01 00:00", time()); ?>" />
                                            <span class="input-group-addon">
                                               <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                    </div>
                            </div>
                                    
                            </div>	
                            <label class="control-label col-md-1 col-sm-3 col-xs-12">End Date</label>
                            <div class="col-md-3 col-sm-9 col-xs-12">
                                    <div class="form-group">
                                    <div class='input-group date' id='myDatepicker1'>
                                            <input type='text' class="form-control" id="end_dt" name="end_dt" data-parsley-required="" 
                                            value="<?php echo ($_SESSION['search_cdr_data']['s_cdr_end_dt']) ? $_SESSION['search_cdr_data']['s_cdr_end_dt'] : date("Y-m-t 23:59", time()); ?>" />
                                            <span class="input-group-addon">
                                               <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                    </div>
                            </div>
                            </div>	-->

                    <div class="searchBar ">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">  

                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url() . 'cdrs/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>                            
                            </ul>
                        </div>


                    </div>
                </div>
            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>


            <table class="table table-striped jambo_table bulk_action table-bordered" id="cdrs">
                <thead>
                    <tr class="headings thc">
                        <?php
                        foreach ($all_field_array as $field_lebel) {
                            echo '<th class="column-title">' . $field_lebel . '</th>';
                        }
                        ?>


                    </tr>
                </thead>		
                <tbody>
                    <?php
                    if ($listing_count > 0) {
                        $scrollY = 400;
                        foreach ($listing_data as $listing_row):

                            echo '<tr>';
                            foreach ($all_field_array as $field_name => $field_lebel) {
                                echo '<td>';
                                if ($field_name == 'Start Time')
                                    echo '<nobr>' . $listing_row['Start Time'] . '</nobr>';
                                else
                                    echo $listing_row[$field_name];
                                echo '</td>';
                            }
                            echo '</tr>';

                        endforeach;
                    }
                    else {
                        $scrollY = 60;
                    }
                    ?>
                </tbody>
            </table>

            <?php echo '<div class="btn-toolbar" role="toolbar">
					 
				
					  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
							   ' . $pagination . '
					  </div>
					</div>'; ?>  

        </div>
    </div>
</div>

<link href="<?php echo base_url() ?>theme/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet"> 
<script src="http://localhost/mt_switch/theme/vendors/moment/min/moment.min.js"></script>
<script src="http://localhost/mt_switch/theme/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>   
<script>
    $(document).ready(function () {



        ///////////////////
        $("#time_range").daterangepicker({
            timePicker: !0,
            autoUpdateInput: false,
            timePickerIncrement: 5,
            timePicker24Hour: true,
            locale: {format: "<?php echo SCRIPT_DATE_FORMAT_1; ?>"},
            ranges: {
                'Last 15 Minute': [moment().subtract(15, 'minute'), moment()],
                'Last 30 Minute': [moment().subtract(30, 'minute'), moment()],
                'Last 1 Hour': [moment().subtract(1, 'hour'), moment()],
                'Today': [moment().startOf('days'), moment().endOf('days')],
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
            }
        });

        $('#time_range').val("<?php echo $_SESSION['search_cdr_data']['s_time_range']; ?>");

        $('#time_range').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format("<?php echo SCRIPT_DATE_FORMAT_1; ?>") + ' - ' + picker.endDate.format("<?php echo SCRIPT_DATE_FORMAT_1; ?>"));
        });

        $('#time_range').on('cancel.daterangepicker', function (ev, picker) {
            // $(this).val('');
        })


        $('#cdrs').DataTable({
            searching: false,
            paging: false,
            bInfo: false,
            scrollY: "<?php echo $scrollY; ?>",
            scrollX: true,
            "order": [[1, "desc"]],
        });

    });

    $('#OkFilter').click(function () {
        var is_ok = $("#search_form").parsley().isValid();
        if (is_ok === true)
            $("#search_form").submit();
        else
            $('#search_form').parsley().validate();
    })
</script>

