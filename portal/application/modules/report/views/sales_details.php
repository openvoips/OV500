
<style type="text/css">
    .detail_row{background-color: #D98568;
                color: white;
    }
    .detail_row th, .detail_row td{
        color:#464646;	
    }
</style>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Sales Details</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>report/salesdetails">
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
                        <a href="<?php echo base_url('report/salesdetails/export/csv'); ?>"><input type="button" value="Export" name="export" id="export" class="btn btn-dark">   </a>

                    </div>
                </div>


            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>           
            <div class="row">  
                <?php //dispay_pagination_row($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination); ?>
            </div>

            <div class="table-responsive">
                <table class="table table-striped jambo_table table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">               
                            <th class="column-title">&nbsp;</th>
                            <th class="column-title">Client Name</th>
                            <th class="column-title text-right">Total Cost excl.</th>
                            <th class="column-title text-right">Total Sell excl.</th>
                            <th class="column-title text-right">Profit excl.</th>
                            <th class="column-title">Currency</th>
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if (isset($listing_data['result']) && count($listing_data['result']) > 0) {
                            $previous_account_id = '';
                            $buy_cost_sum = $total_cost_sum = $profit_sum = 0;
                            foreach ($listing_data['result'] as $listing_row) {
                                $current_account_id = $listing_row['account_id'];

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

                                $buy_cost = $listing_row['buy_cost'];
                                $total_cost = $listing_row['total_cost'];
                                $profit = $listing_row['profit'];

                                if ($previous_account_id != $current_account_id) {
                                    if (isset($sum_array[$previous_account_id])) {

                                        if (number_format($sum_array[$previous_account_id]['profit_sum'], 2, '.', '') < 0) {
                                            $profit = " <p style='color:red;'>" . number_format($sum_array[$previous_account_id]['profit_sum'], 2, '.', '') . "</p>";
                                        } else {
                                            $profit = number_format($sum_array[$previous_account_id]['profit_sum'], 2, '.', '');
                                        }
                                        $main_row = '<tr class="header"> 
						<th class="plus_icon" width="50"><i class="fa fa-minus-square"></i></th>                                
						<td>' . $sum_array[$previous_account_id]['company_name'] . '</td>						
						<td class="text-right">' . number_format($sum_array[$previous_account_id]['buy_cost_sum'], 2, '.', '') . '</td>
						<td class="text-right">' . number_format($sum_array[$previous_account_id]['total_cost_sum'], 2, '.', '') . '</td>
						<td class="text-right ">' . $profit . '</td>
						<td>' . $listing_row['cname'] . '</td>                                    
					</tr>';
                                        echo $main_row;

					$profit = 0;
                                        $detail_row_display = '<tr class="detail_row hide1">
									<td colspan="6">
										<table class="table table-hover table-bordered table-condensed">'
                                                . '<tr>
												<th class="column-title">Item Name</th>
												<th class="column-title">Units</th>
												<th class="column-title">Total Cost excl.</th>
												<th class="column-title">Total Sell excl.</th>
												<th class="column-title">Profit excl.</th>
												</tr>'
                                                . $detail_row
                                                . '</table>
										</td>
									</tr>';

                                        echo $detail_row_display;
                                        $detail_row = '';
                                    }
                                } {
                                    if (!isset($sum_array[$current_account_id]['buy_cost_sum'])) {
                                        $sum_array[$current_account_id]['buy_cost_sum'] = 0;
                                        $sum_array[$current_account_id]['total_cost_sum'] = 0;
                                        $sum_array[$current_account_id]['profit_sum'] = 0;
                                    }

                                    $sum_array[$current_account_id]['buy_cost_sum'] += $buy_cost;
                                    $sum_array[$current_account_id]['total_cost_sum'] += $total_cost;
                                    $sum_array[$current_account_id]['profit_sum'] += $profit;
                                    $sum_array[$current_account_id]['cname'] = $listing_row['cname'];
                                    $sum_array[$current_account_id]['company_name'] = $acc;
                                }

                                if (number_format($listing_row['profit'], 2, '.', '') < 0) {
                                    $profit = " <p style='color:red;'>" . number_format($listing_row['profit'], 2, '.', '') . "</p>";
                                } else {
                                    $profit = number_format($listing_row['profit'], 2, '.', '');
                                }

                                $detail_row .= '<tr >       
					<td>' . $listing_row['display_text'] . '</td>
					<td>' . $listing_row['quantity'] . '</td>
					<td class="text-right">' . number_format($listing_row['buy_cost'], 2, '.', '') . '</td>
					<td class="text-right">' . number_format($listing_row['total_cost'], 2, '.', '') . '</td>
					<td class="text-right">' . $profit . '</td>
				</tr>';


                                // echo $detail_row;


                                $previous_account_id = $current_account_id;
                            }//foreach
                            if (isset($sum_array[$previous_account_id])) {
                                $main_row = '<tr class="header"> 
						<th class="plus_icon"><i class="fa fa-minus-square"></i></th>                                
						<td>' . $sum_array[$previous_account_id]['company_name'] . '</td>						
						<td class="text-right">' . number_format($sum_array[$previous_account_id]['buy_cost_sum'], 2, '.', '') . '</td>
						<td class="text-right">' . number_format($sum_array[$previous_account_id]['total_cost_sum'], 2, '.', '') . '</td>
						<td class="text-right">' . number_format($sum_array[$previous_account_id]['profit_sum'], 2, '.', '') . '</td>
						<td>' . $listing_row['cname'] . '</td>                                    
					</tr>';
                                echo $main_row;

                                $detail_row_display = '<tr class="detail_row hide1">
									<td colspan="6">
										<table class="table table-hover table-bordered table-condensed">'
                                        . '<tr>
												<th class="column-title">Item Name</th>
												<th class="column-title">Units</th>
												<th class="column-title">Total Cost excl.</th>
												<th class="column-title">Total Sell excl.</th>
												<th class="column-title">Profit excl.</th>
												</tr>'
                                        . $detail_row
                                        . '</table>
										</td>
									</tr>';
                                echo $detail_row_display;
                                $detail_row = '';
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
                <?php // dispay_pagination_row_bottom($total_records, $_SESSION[$search_session_key]['no_of_records'], $pagination);  ?>
            </div> 
        </div>
    </div><?php //echo '<pre>';print_r($listing_data['result']); echo '<pre>';   ?>
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




    $('.plus_icon').click(function ()
    {
        $(this).parent().next('tr').toggle(700);
        $("i", this).toggleClass("fa fa-plus-square fa fa-minus-square");
    });

</script>
