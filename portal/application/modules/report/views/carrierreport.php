<?php
$table_data_array=array();
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Provider Call Usage</h2>

            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>report/carrierreport">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />

                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Carrier ID</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="carrier_id" id="carrier_id" value="<?php echo $_SESSION[$search_session_key]['carrier_id']; ?>" class="form-control data-search-field" placeholder="Carrier ID">
                    </div>
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Carrier Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="carrier_name" id="carrier_name" value="<?php echo $_SESSION[$search_session_key]['carrier_name']; ?>" class="form-control data-search-field" placeholder="Carrier Name">
                    </div>
                </div>
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Date</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="providertime" id="reservation-time" class="form-control" value="<?php echo $_SESSION[$search_session_key]['providertime']; ?>" />
                    </div>
                    <div class="searchBar text-right ">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">
                    </div>
                </div>


            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>           
            <div class="row">  
                <?php dispay_pagination_row($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination); ?>
            </div>

            <div class="table-responsive">
                <table class="table table-striped jambo_table table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title">Carrier</th>
                            <th class="column-title">Currency</th>
                            <th class="column-title text-right">Cost(VAT)</th>
                            <!--<th class="column-title text-right">Gross Cost</th>-->
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if ($listing_data['result'] > 0) {
                            foreach ($listing_data['result'] as $listing_row) {
                                //$currency = get_currency($listing_row['c_currency_id']);
                                $acc = '';
                                if (!empty($listing_row['carrier_id'])) {
                                    if (!empty($listing_row['carrier_name'])) {
                                        $acc = $listing_row['carrier_name'] . ' (' . $listing_row['carrier_id'] . ')';
                                    } else
                                        $acc = $listing_row['carrier_id'];
                                }
                                ?>
                                <tr>                                     
                                    <td><?php echo $acc; ?></td>
                                    <td><?php echo $listing_row['cname']; ?></td>
                                    <td class="text-right"><?php echo number_format($listing_row['c_cost_sum'], 2, '.', ''); ?></td>
                                    <!--<td class="text-right"><?php echo number_format($listing_row['c_total_cost_sum'], 2, '.', ''); ?></td>-->
                                </tr>

                                <?php
                                $cname = $listing_row['cname'];
                                if ($cname == '')
                                    $cname = 'NA';
                                if (isset($table_data_array[$cname])) {
                                    $table_data_array[$cname]['c_cost_sum'] += $listing_row['c_cost_sum'];
//									$table_data_array[$cname]['c_total_cost_sum'] +=$listing_row['c_total_cost_sum'];
                                } else {
                                    $table_data_array[$cname] = array('cname' => $listing_row['cname'], 'c_cost_sum' => $listing_row['c_cost_sum'], 'c_total_cost_sum' => $listing_row['c_total_cost_sum']);
                                }
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="8" align="center" style="font-size:14px"><strong>No Record Found</strong></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>                    
            <div class="row">  
<?php dispay_pagination_row_bottom($total_records, $_SESSION[$search_session_key]['no_of_records'], $pagination); ?>
            </div> 
        </div><?php //ddd($listing_data['result']);  ?>


<?php if ($table_data_array > 0) { ?>

            <div class="clearfix"></div>
            <div class="table-responsive1">
                <table class="table table-striped jambo_table table-bordered" id="table-sort">                    		
                    <thead>
                        <tr class="headings thc">                           
                            <th class="column-title">Currency</th>
                            <th class="column-title text-right">Cost</th>
                            <!--<th class="column-title text-right">Gross Cost</th>-->
                    </thead>
                    <tbody>
    <?php
    foreach ($table_data_array as $listing_row) {
        ?>	
                            <tr> 
                                <td><?php echo $listing_row['cname']; ?></td>
                                <td class="text-right"><?php echo number_format($listing_row['c_cost_sum'], 2, '.', ''); ?></td>
                                <!--<td class="text-right"><?php echo number_format($listing_row['c_total_cost_sum'], 2, '.', ''); ?></td>-->
                            </tr>                                
        <?php
    }
    ?>
                    </tbody>
                </table>

            </div>                    



        </div>
<?php } ?>


</div>
</div>

<script>
    $(document).ready(function () {
        $("#reservation-time").daterangepicker({
            timePicker: !0,
            timePickerIncrement: 5,
            locale: {
                format: "YYYY-MM-DD"
            },
            timePicker24Hour: true,
            ranges: {
                'Today': [moment().startOf('days'), moment().endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
        $(document).ready(function () {
            //showDatatable('table-sort', [5], [1, "asc"]);
            $('#OkFilter').click(function () {
                var no_of_records = $('#no_of_records').val();
                $('#no_of_rows').val(no_of_records);
            });
        });
    });

</script>
