<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Payment Report</h2>
           
             <ul class="nav navbar-right panel_toolbox">             
            <li>               
                <a href="<?php echo site_url(); ?>payment/index/<?php echo param_encrypt($listing_row['account_id']); ?>"><button class="btn btn-danger" type="button"  tabindex="<?php echo $tab_index++; ?>">Add Payment</button></a>
            </li>
        </ul>
            
             
             
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url('paymentdetail'); ?>">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12" >Payment Date</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text" name="pay_date" id="pay_date" value="<?php echo $_SESSION[$search_session_key]['pay_date']; ?>"  class="form-control col-md-7 col-xs-12 data-search-field" readonly="readonly" data-parsley-required="">
                    </div>		

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Company </label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="company_name" id="company_name" value="<?php echo $_SESSION[$search_session_key]['company_name']; ?>" class="form-control data-search-field" placeholder="Company Name">
                    </div>                        

                </div>
                <div class="form-group">    

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                    <div class="col-md-2 col-sm-8 col-xs-12">
                        <input type="text"  name="account_id" id="account_id"  value="<?php echo $_SESSION[$search_session_key]['account_id']; ?>" class="form-control data-search-field" placeholder="Account ID">
                    </div>


                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Payment Type </label>
                    <div class="col-md-2 col-sm-8 col-xs-12">
                        <select name="payment_type" id="payment_type" class="form-control data-search-field">                          
                            <option value="">ALL</option>
                            <option value="manual" <?php if ($_SESSION[$search_session_key]['payment_type'] == 'manual') echo ' selected="selected" '; ?>>Manual</option>
                            <option value="customer" <?php if ($_SESSION[$search_session_key]['payment_type'] == 'customer') echo ' selected="selected" '; ?>>Online</option>              
                        </select>    
                    </div> 

                    <div class=" pull-right">      
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                     

                    </div>

                </div>
            </form> 


            <div class="clearfix"></div>
            <div class="ln_solid"></div>
            <div class="row">  
                <?php
                dispay_pagination_row($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination);
                ?>                    
            </div> 
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings">
                            <th class="column-title"><nobr>Account ID</nobr></th>
                    <th class="column-title">Amount</th>
                    <th class="column-title"><nobr>Payment Date</nobr></th>
                    <th class="column-title">Notes</th>
                    <th class="column-title">Payment By</th>
                    <th class="column-title"><nobr>Entry Date</nobr></th>                      
                    </tr>
                    </thead>

                    <tbody>
                        <?php
                        $dp = 2;
                        if ($listing_data['result'] > 0) {
                            foreach ($listing_data['result'] as $payment_data) {
                                $paid_on_display = '';
                                $create_dt_display = '';
                                if ($payment_data['paid_on'] != '') {
                                    $paid_on = $payment_data['paid_on'];
                                    $paid_on_timestamp = strtotime($paid_on);
                                    $paid_on_display = date('Y-m-d h:i:s A', $paid_on_timestamp);
                                }
                                if ($payment_data['create_dt'] != '') {
                                    $create_dt = $payment_data['create_dt'];
                                    $create_dt_timestamp = strtotime($create_dt);
                                    $create_dt_display = date('Y-m-d h:i:s A', $create_dt_timestamp);
                                }
                                $amount = number_format($payment_data['amount'], $dp, '.', '');

                                if ($payment_data['account_type'] === 'CUSTOMER')
                                    $account_type = '<span class="label label-primary">' . $payment_data['account_type'] . '</span>';
                                else
                                    $account_type = '<span class="label label-warning">' . $payment_data['account_type'] . '</span>';

                                if ($payment_data['account_id'] == $payment_data['created_by'])
                                    $payment_by = 'Online';
                                else
                                    $payment_by = $payment_data['created_by_name'];
                                ?>
                                <tr> 
                                    <td><?php echo $payment_data['company_name'] . ' (' . $payment_data['account_id'] . ') ' . $account_type; ?></td>	
                                    <td class="text-right"><?php echo $amount; ?></td>         
                                    <td><?php echo $paid_on_display; ?></td>                                   
                                    <td><?php echo $payment_data['notes']; ?></td>
                                    <td><?php echo $payment_by; ?></td>
                                    <td class=" last"><?php echo $create_dt_display; ?></td>  
                                </tr>
        <?php
    }
}
else {
    ?>
                            <tr>
                                <td colspan="8" align="center"><strong>No Record Found</strong></td>
                            </tr>
                            <?php
                        }
                        ?>


                    </tbody>
                </table>
            </div>                    

        </div>
    </div>
<?php  
?>
</div>


<script>
    /*search form*/
    $('#OkFilter').click(function () {
        var no_of_records = $('#no_of_records').val();
        $('#no_of_rows').val(no_of_records);
    });
</script> 

<script>
    $(document).ready(function () {
        $("#pay_date").daterangepicker({
            timePicker: !0,
            timePickerIncrement: 5,
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
                'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        showDatatable('table-sort', [], [5, "desc"]);

    });
</script>