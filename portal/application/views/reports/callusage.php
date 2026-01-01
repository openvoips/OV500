<?php
$currency_array = [];
foreach ($currency_options as $single_array) {
    $currency_id = $single_array['currency_id'];
    $currency_array[$currency_id] = $single_array;
}
?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Call Usage Detail</h2>			
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url('report/callusage'); ?>">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />   
                <?php if (!check_logged_account_type(array('CUSTOMER'))) { ?>           
                    <div class="form-group">                        
                        <label class="control-label col-md-1 col-sm-3 col-xs-6">Account</label>
                        <div class="col-md-2 col-sm-9 col-xs-6">
                            <input type="text" name="customer_account_id" id="customer_account_id" value="<?php echo $_SESSION[$page_name]['customer_account_id']; ?>" class="form-control data-search-field" placeholder="Account">
                        </div>	

                        <label class="control-label col-md-2 col-sm-3 col-xs-6">Company Name</label>
                        <div class="col-md-3 col-sm-9 col-xs-6">
                            <input type="text" name="customer_company_name" id="customer_company_name" value="<?php echo $_SESSION[$page_name]['customer_company_name']; ?>" class="form-control data-search-field" placeholder="Company Name">
                        </div>	
                    </div>
                <?php } ?>


                <div class="row">                 			
                    <label class="control-label col-md-1 col-sm-3 col-xs-6">Date</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text" name="time_range" id="time_range" class="form-control " value="<?php if (isset($_SESSION[$page_name]['time_range'])) echo $_SESSION[$page_name]['time_range']; ?>" readonly="readonly" data-parsley-required="" />
                    </div> 

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Call Type</label>
                    <div class="col-md-2 col-sm-6 col-xs-6">
                        <select class="form-control data-search-field" id="cdr_type" name="cdr_type">
                            <option value="">Select</option>	
                            <option value="IN"  <?php if ($_SESSION[$page_name]['cdr_type'] == 'IN') echo 'selected'; ?>>IN</option>
                            <option value="OUT" <?php if ($_SESSION[$page_name]['cdr_type'] == 'OUT') echo 'selected'; ?>>OUT</option>
                        </select>
                    </div>


                    <div class="searchBar text-right">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" >                          
                    </div>
                </div>
                <div>
                </div>
            </form>		
        </div>  
    </div>


    <div class="x_panel">

        <div class="x_content">
            <h4>Summary</h4>
            <div class="table-responsive">
                <table  class="table table-striped jambo_table table-bordered">
                    <thead>
                        <tr class="headings thc">                            
                            <th class="column-title"><nobr>&nbsp</nobr></th>
                    <th class="column-title"><nobr>Calls</nobr></th>
                    <th class="column-title"><nobr>Plan Charge</nobr></th>
                    <th class="column-title"><nobr>Total Cost</nobr></th>
                    <th class="column-title"><nobr>Currency</nobr></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($sum_result) && count($sum_result) > 0) {
                            foreach ($sum_result as $listing_array) {
                                foreach ($listing_array as $listing_row) {
                                    if ($listing_row['cdr_type'] == 'IN')
                                        $call_type = 'Inbound Calls';
                                    else
                                        $call_type = 'Outbound Calls';

                                    $currency_name = '';
                                    $customer_currency_id = $listing_row['customer_currency_id'];
                                    if ($customer_currency_id != '' && isset($currency_array[$customer_currency_id]))
                                        $currency_name = $currency_array[$customer_currency_id]['name'];

                                    echo '<tr>';
                                    echo '<td>' . $call_type . '</td>';
                                    echo '<td>' . $listing_row['call_count'] . '</td>';
                                    echo '<td>' . number_format(round($listing_row['plan_charge'], 4), 4) . '</td>';
                                    echo '<td>' . number_format(round($listing_row['total_cost'], 4), 4) . '</td>';
                                    echo '<td>' . $currency_name . '</td>';
                                    echo '</tr>';
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>   
            </div>
        </div>
        <div class="row"><div class="clearfix"></div><hr /><div class="clearfix"></div></div>
        <div class="row">  
            <?php dispay_pagination_row($total_records, $_SESSION[$page_name]['no_of_records'], $pagination); ?>                    
        </div>     


        <div class="x_content">
            <div class="table-responsive">
                <table id="analytics" class="table table-striped jambo_table bulk_action table-bordered">
                    <thead>
                        <tr class="headings thc">     
                            <?php if (!check_logged_account_type(array('CUSTOMER'))) { ?>                        
                                <th class="column-title"><nobr>Account</nobr></th>
                        <th class="column-title"><nobr>Company Name</nobr></th>
                    <?php } ?>
                    <th class="column-title"><nobr>Call Type</nobr></th>
                    <th class="column-title"><nobr>Plan Type</nobr></th>
                    <th class="column-title"><nobr>Used(Sec)</nobr></th>
                    <th class="column-title"><nobr>Plan Rate</nobr></th>
                    <th class="column-title"><nobr>Plan Charge</nobr></th>
                    <th class="column-title"><nobr>Total Cost</nobr></th>
                    </tr>
                    </thead>	
                    <tfoot>
                        <tr class="headings thc">                            
                            <?php if (!check_logged_account_type(array('CUSTOMER'))) { ?> 
                                <th class="column-title">Account</th>
                                <th class="column-title">Company Name</th>
                            <?php } ?>
                            <th class="column-title"><nobr>Call Type</nobr></th>
                    <th class="column-title">Plan Type</th>
                    <th class="column-title">Used(Sec)</th>
                    <th class="column-title">Plan Rate</th>
                    <th class="column-title">Plan Charge</th>
                    <th class="column-title">Total Cost</th>						
                    </tr>
                    </tfoot>		
                    <tbody>
                        <?php
                        if (isset($listing_data) && count($listing_data) > 0) {
                            foreach ($listing_data as $listing_row) {

                                echo '<tr>';
                                if (!check_logged_account_type(array('CUSTOMER'))) {
                                    echo '<td>' . $listing_row['customer_account_id'] . '</td>';
                                    echo '<td>' . $listing_row['customer_company_name'] . '</td>';
                                }
                                echo '<td>' . $listing_row['cdr_type'] . '</td>';
                                echo '<td>' . $listing_row['plan_type'] . '</td>';
                                // echo '<td>' . gmdate("H:i:s", $listing_row['used']) . '</td>';

                                echo '<td>' . $listing_row['used'] . '</td>';

                                echo '<td>' . number_format(round($listing_row['plan_rate'], 4), 4) . '</td>';
                                echo '<td>' . number_format(round($listing_row['plan_charge'], 4), 4) . '</td>';
                                echo '<td>' . number_format(round($listing_row['total_cost'], 4), 4) . '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php //echo $sql.'<br><br><br>'.$sum_sql; ?>


        </div>

        <br />
        <?php
        echo '<div class="btn-toolbar" role="toolbar">
                    <div class="btn-group col-md-5 col-sm-12 col-xs-12">
                    </div>						
                    <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
                            ' . $pagination . '
                    </div>
            </div>';
        ?>
    </div>
</div>
<style type="text/css">

    .fixedHeader-floating{
        position:fixed;
    }

    table.jambo_table tfoot {
        background: rgba(52,73,94,.94);
        color: #ECF0F1;
    }
</style>


<script>
    $(document).ready(function () {
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

        showDatatable('analytics', [], [1, "asc"]);

        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });

    });
</script>