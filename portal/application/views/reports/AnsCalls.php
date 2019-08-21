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
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Connected Calls Detail</h2>			
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>reports/AnsCalls">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />              
                <div class="form-group">
                    <?php if ($logged_customer_type != 'CUSTOMER') { ?>
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Customer Type</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <select class="form-control" id="customer_type" name="customer_type">
                                <?php if ($get_logged_customer_level == 0) { ?>
                                    <option value="U"  <?php if ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'U') echo 'selected'; ?>>User</option>
                                    <option value="R1" <?php if ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'R1') echo 'selected'; ?>>Reseller 1</option>
                                <?php } ?>
                                <?php if ($get_logged_customer_level == 1) { ?>
                                    <option value="U"  <?php if ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'U') echo 'selected'; ?>>User</option>
                                    <option value="R2" <?php if ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'R2') echo 'selected'; ?>>Reseller 2</option>
                                <?php } ?>
                                <?php if ($get_logged_customer_level == 2) { ?>
                                    <option value="U"  <?php if ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'U') echo 'selected'; ?>>User</option>
                                    <option value="R3" <?php if ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'R3') echo 'selected'; ?>>Reseller 3</option>
                                <?php } ?>
                                <?php if ($get_logged_customer_level == 3) { ?>
                                    <option value="U"  <?php if ($_SESSION['search_cdr_data']['s_cdr_customer_type'] == 'U') echo 'selected'; ?>>User</option>
                                <?php } ?>
                            </select>
                        </div>
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Account</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" name="customer_account" id="customer_account" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_customer_account']; ?>" class="form-control data-search-field" placeholder="Account">
                        </div>	

                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Company Name</label>
                        <div class="col-md-3 col-sm-9 col-xs-12">
                            <input type="text" name="customer_company_name" id="customer_company_name" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_customer_company_name']; ?>" class="form-control data-search-field" placeholder="Company Name">
                        </div>	

                    <?php } ?>

                </div>

                <div class="form-group">


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

                    <?php if (isset($all_field_array['USER-CLI'])): ?>	
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">User CLI</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" name="customer_cli" id="customer_cli" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_customer_cli']; ?>" class="form-control data-search-field" placeholder="User CLI">
                        </div>
                    <?php endif; ?>


                    <?php if (isset($all_field_array['C-CLI'])): ?>	
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">C-CLI</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" name="carrier_cli" id="carrier_cli" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_carrier_cli']; ?>" class="form-control data-search-field" placeholder="Carrier CLI">
                        </div>
                    <?php endif; ?>

                </div> <!--end of row-->

                <div class="form-group">

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


                    <label class="control-label col-md-1 col-sm-3 col-xs-12">User IP</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="customer_ip" id="customer_ip" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_customer_ip']; ?>" class="form-control data-search-field" placeholder="User IP">
                    </div>

                </div>


                <div class="row">

                    <div class="col-md-6">				
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Date</label>
                        <div class="col-md-8 col-sm-9 col-xs-12">
                            <input type="text" name="time_range" id="time_range" class="form-control " value="<?php if (isset($_SESSION['search_cdr_data']['s_time_range'])) echo $_SESSION['search_cdr_data']['s_time_range']; ?>" readonly="readonly" data-parsley-required="" />
                        </div> 

                    </div>

                    <div class="col-md-6">	


                        <div class="col-md-10">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Duration (Sec)</label>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <select name="duration_range" id="duration_range" class="form-control" data-parsley-required="">
                                    <option value="gt" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'gt') ? 'selected="selected"' : ''; ?>>> </option> 
                                    <option value="ls" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'ls') ? 'selected="selected"' : ''; ?>>< </option> 
                                    <option value="gteq" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'gteq') ? 'selected="selected"' : ''; ?>>>= </option>
                                    <option value="lseq" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'lseq') ? 'selected="selected"' : ''; ?>> <= </option>

                                    <option value="eq" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'eq') ? 'selected="selected"' : ''; ?>> = </option>

                                </select>
                            </div>

                            <div class="col-md-5">

                                <input type="text" name="call_duration" id="call_duration" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_call_duration']; ?>" class="form-control data-search-field" placeholder="Duration"> 

                            </div>


                        </div>


                    </div>	


                </div>

                <br>


                <div class="row"> 

                    <div class="searchBar ">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" >  

                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    if ($export_format != 'pdf')
                                        echo '<li><a href="' . base_url() . 'reports/AnsCalls/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>                            
                            </ul>
                        </div>  
                    </div>
                </div>
            </form>		
        </div>  
    </div>


    <div class="x_panel" id="recordset">

        <div class="row">  
            <?php
            dispay_pagination_row($total_records, $_SESSION['search_cdr_data']['s_no_of_records'], $pagination);
            ?>                    
        </div>     


        <div class="x_content">
            <div class="table-responsive">
                <table id="analytics" class="table table-striped jambo_table bulk_action table-bordered">
                    <thead>
                        <tr class="headings thc">                            
                            <?php
                            foreach ($all_field_array as $field_lebel) {
                                echo '<th class="column-title"><nobr>' . $field_lebel . '</nobr></th>';
                            }
                            ?>							
                        </tr>
                    </thead>	
                    <tfoot>
                        <tr class="headings thc">                            
                            <?php
                            foreach ($all_field_array as $field_lebel) {
                                echo '<th class="column-title">' . $field_lebel . '</th>';
                            }
                            ?>							
                        </tr>
                    </tfoot>		
                    <tbody>
                        <?php
                        if (isset($listing_count) && $listing_count > 0) {
                            foreach ($listing_data as $listing_row) {
                                //var_dump($listing_row);
                                echo '<tr>';
                                foreach ($all_field_array as $field_name => $field_lebel) {
                                    echo '<td><nobr>';
                                    if ($field_name == 'Account') {

                                        if ($listing_row['customer_company_name'] != '') {

                                            echo $listing_row['customer_company_name'] . ' ( ' . $listing_row[$field_name] . ' ) ';
                                        } else {
                                            echo $listing_row[$field_name];
                                        }
                                    } else {
                                        echo $listing_row[$field_name];
                                    }

                                    echo '</nobr></td>';
                                }
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>

        <br />
        <?php
        echo '<div class="btn-toolbar" role="toolbar">
							  <div class="btn-group col-md-5 col-sm-12 col-xs-12">';
        echo '</div>						
							  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
									   ' . $pagination . '
							  </div>
					  </div>';
        ?>
    </div>
</div>
<style type="text/css">

    .fixedHeader-floating{position:fixed;}

    table.jambo_table tfoot {
        background: rgba(52,73,94,.94);
        color: #ECF0F1;
    }
</style>


<script>
//var cur_date = moment().tz("GMT").format('YYYY-MM-DD HH:mm');
    $(document).ready(function () {
        //moment.tz.setDefault('GMT');
        //moment.tz.setDefault('Asia/Calcutta');
        //Asia/Calcutta
//	moment().tz("America/Los_Angeles").format();
//moment.tz("2012-03-11 01:59:59", "America/New_York").format();


        $("#time_range").daterangepicker({
            timePicker: !0,
            timePickerIncrement: 1,
            locale: {
                format: "YYYY-MM-DD HH:mm"
            },
            timePicker24Hour: true,
            ranges: {
                'Last 15 Minute': [moment().subtract(15, 'minute'), moment()],
                'Last 30 Minute': [moment().subtract(30, 'minute'), moment()],
                'Last 1 Hour': [moment().subtract(1, 'hour'), moment()],
                'Today': [moment().startOf('days'), moment().endOf('days')],
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                /*'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],*/
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        //$('#recordset').removeAttr("style");
        // $('#search_loader').css('display','none');
        $('#analytics').DataTable({
            searching: false,
            paging: false,
            bInfo: false,
            scrollX: true,
        });
        // showDatatable('analytics',[], [1,"asc" ] );

        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });



    });
</script>