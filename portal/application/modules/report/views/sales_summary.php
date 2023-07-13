
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Sales Summary</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>report/salessummary">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />

                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="account_id" id="account_id" data-parsley-required="" value="<?php echo $_SESSION[$search_session_key]['account_id']; ?>" class="form-control data-search-field" placeholder="Account ID">
                    </div>
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="company_name" id="company_name" value="<?php echo $_SESSION[$search_session_key]['company_name']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>
                </div>
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Date</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="clienttime" id="reservation-time" class="form-control" value="<?php echo $_SESSION[$search_session_key]['clienttime']; ?>" />
                    </div>
                    <div class="searchBar text-right">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">   
                        <a href="<?php echo base_url('report/salessummary/export/csv'); ?>"><input type="button" value="Export" name="export" id="export" class="btn btn-dark">   </a>

                    </div>
                </div>


            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>           


            <div class="table-responsive">
                <table class="table table-striped jambo_table table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">               
                            <th class="column-title" width="50%">Client Name</th>
                            <th class="column-title text-right">Total Cost excl.</th>
                            <th class="column-title text-right">Total Sell excl.</th>
                            <th class="column-title text-right">Profit excl.</th>
                            <th class="column-title">Currency</th>
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                     
                        if (isset($listing_data['result']) && count($listing_data['result']) > 0) {
                            foreach ($listing_data['result'] as $listing_row) {
                                //$currency = get_currency($listing_row['currency_id']);
                                $acc = '';
                                if (!empty($listing_row['account_id'])) {
                                    if (!empty($listing_row['company_name'])) {
                                        $acc = $listing_row['company_name'] . ' (' . $listing_row['account_id'] . ')';
                                    }
                                }
                                if ($listing_row['account_type'] == 'CUSTOMER')
                                    $acc .= ' (C)';
                                else
                                    $acc .= ' (R)';
                                ?>
                                <tr>                                     
                                    <td><?php echo $acc; ?></td>
                                    <td class="text-right"><?php echo number_format($listing_row['buy_cost'], 2, '.', ''); ?></td>
                                    <td class="text-right"><?php echo number_format($listing_row['total_cost'], 2, '.', ''); ?></td>
                                    <td class="text-right"><?php echo number_format($listing_row['profit'], 2, '.', ''); ?></td>
                                    <td><?php echo $listing_row['cname']; ?></td>

                                </tr>

                                <?php
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

                <table class="table table-striped jambo_table table-bordered" >

                    <tbody>
                        <?php
                        if (isset($listing_data['sum_result']) && count($listing_data['sum_result']) > 0) {
                            foreach ($listing_data['sum_result'] as $listing_row) {
                                //$currency = get_currency($listing_row['currency_id']);
                                ?>
                                <tr>                                     
                                    <td width="50%" ><strong>Grand Totals excl</strong></td>
                                    <td class="text-right"><strong><?php echo number_format($listing_row['buy_cost'], 2, '.', ''); ?></strong></td>
                                    <td class="text-right"><strong><?php echo number_format($listing_row['total_cost'], 2, '.', ''); ?></strong></td>
                                    <td class="text-right"><strong><?php echo number_format($listing_row['profit'], 2, '.', ''); ?></strong></td>
                                    <td><strong><?php echo $listing_row['cname']; ?></strong></td>

                                </tr>

                                <?php
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
        </div>
    </div><?php //echo '<pre>';print_r($listing_data['result']); echo '</pre>';  ?>
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
            maxDate: moment().endOf('days'),
            ranges: {
                'Today': [moment().startOf('days'), moment().endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                /*'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],*/
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
