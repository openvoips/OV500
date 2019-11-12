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
// echo '<pre>'; print_r($listing_data[0]); 
//print_r($currency_data);
//echo '</pre>'; 
?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Daily Usage Summary</h2>			
            <div class="clearfix"></div>

        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>reports/businesHistory">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account Id</label>
                    <div class="col-md-2 col-sm-8 col-xs-12">
                        <input type="text" name="user_account_id" id="user_account_id" value="<?php echo $_SESSION['search_businesHistory_data']['s_cdr_customer_account_id']; ?>" class="form-control data-search-field" placeholder="Account ID">
                    </div>	

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Company Name</label>
                    <div class="col-md-2 col-sm-8 col-xs-12">
                        <input type="text" name="company" id="company" value="<?php echo $_SESSION['search_businesHistory_data']['s_cdr_company']; ?>" class="form-control data-search-field" placeholder="Company Name">
                    </div>

                </div>
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-4 col-xs-12">Record Date</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <input type="text" name="record_date" id="record_date" class="form-control " value="<?php if (isset($_SESSION['search_businesHistory_data']['s_cdr_record_date'])) echo $_SESSION['search_businesHistory_data']['s_cdr_record_date']; ?>" readonly="readonly" data-parsley-required="" />
                    </div>   

                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Currency</label>
                    <div class="col-md-3 col-sm-8 col-xs-12">
                        <select name="currency" id="currency" class="form-control data-search-field">
                            <option value="">Select Currency</option>
                            <?php for ($i = 0; $i < count($currency_data); $i++) { ?>	

                                <?php if (get_logged_account_level() == 0): ?>							
                                    <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if ($_SESSION['search_businesHistory_data']['s_cdr_currency'] == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name']; ?></option>
                                <?php elseif (get_logged_account_level() != 0 && get_logged_account_currency() == $currency_data[$i]['currency_id']): ?>
                                    <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if ($_SESSION['search_businesHistory_data']['s_cdr_currency'] == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name']; ?></option>
    <?php endif; ?>
<?php } ?>
                        </select>
                    </div>

                </div>

                <div class="form-group">

                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Group by</label>
                    <div class="">
                        <div class="checkbox col-md-2 col-sm-7 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_account_id" id="g_account_id" <?php if (isset($_SESSION['search_businesHistory_data']['s_cdr_g_account_id']) && $_SESSION['search_businesHistory_data']['s_cdr_g_account_id'] == 'Y') echo 'checked'; ?>> Account ID
                            </label>
                        </div>
                        <div class="checkbox col-md-2 col-sm-7 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_rec_month" id="g_rec_month" <?php if (isset($_SESSION['search_businesHistory_data']['s_cdr_g_rec_month']) && $_SESSION['search_businesHistory_data']['s_cdr_g_rec_month'] == 'Y') echo 'checked'; ?>> Monthly
                            </label>
                        </div>
                        <div class="checkbox col-md-2 col-sm-7 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_rec_date" id="g_rec_date" <?php if (isset($_SESSION['search_businesHistory_data']['s_cdr_g_rec_date']) && $_SESSION['search_businesHistory_data']['s_cdr_g_rec_date'] == 'Y') echo 'checked'; ?>> Record Date
                            </label>
                        </div>


                    </div>     

                    <div class="searchBar ">
                        <input type="button" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">  

                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url() . 'reports/businesHistory/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>                            
                            </ul>
                        </div>  
                    </div>
                </div>
            </form>		
        </div>  
    </div>


    <div class="x_panel" id="recordset" >
        <div class="row">  
            <?php
            dispay_pagination_row($total_records, $_SESSION['search_businesHistory_data']['s_no_of_records'], $pagination);
            ?>                    
        </div>    

        <div class="x_content">


            <div class="table-responsive">
                <table id="table-sort" class="table table-striped jambo_table bulk_action table-bordered">
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
                        if (count($listing_data) > 0) {
                            foreach ($listing_data as $listing_row) {
                                echo '<tr>';
                                foreach ($all_field_array as $field_name => $field_lebel) {
                                    $display_value = $listing_row[$field_name];
                                    if ($field_name == 'record_date') {
                                        $display_value = date(DATE_FORMAT_1, strtotime($display_value));
                                    }

                                    echo '<td><nobr>';
                                    echo $display_value;
                                    echo '</nobr></td>';
                                }
                                echo '</tr>';
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="27" ><strong>No Record Found</strong></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            echo '<div class="btn-toolbar" role="toolbar"><div class="btn-group col-md-5 col-sm-12 col-xs-12"> </div> <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">  ' . $pagination . '  </div></div>';
            ?>
        </div>

        <div class="clearfix"></div>

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

    $(document).ready(function () {

        $("#record_date").daterangepicker({
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
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]

            }
        });

        $("#record_date").val("<?php echo $_SESSION['search_businesHistory_data']['s_cdr_record_date'] ?>");


        $('#OkFilter').click(function () {

            if ($('#g_rec_date').is(":checked") && $('#g_rec_month').is(":checked"))
            {
                alert("Please select either Monthly or Record Date");
            }
            else
            {
                var no_of_records = $('#no_of_records').val();
                $('#no_of_rows').val(no_of_records);
                $("#search_form").submit();
            }

        });



    });

    $(document).ready(function () {
        showDatatable('table-sort', [0], [0, "asc"]);
    });

</script>