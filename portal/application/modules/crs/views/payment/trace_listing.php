<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Payment Tracking</h2>       
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>crs/payment/trace">
                <input type="hidden" name="search_action" value="search" />

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <input type="text" name="account_id" id="account_id" value="<?php echo $_SESSION['search_tracing_data']['s_account_id']; ?>" class="form-control data-search-field" placeholder="Account Id">
                    </div>      

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Company Name</label>
                    <div class="col-md-2 col-sm-8 col-xs-12">
                        <input type="text"  name="company_name" id="company_name"  value="<?php echo $_SESSION['search_tracing_data']['s_company_name']; ?>" class="form-control data-search-field" placeholder="Company Name">
                    </div>                  

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Transaction ID</label>
                    <div class="col-md-2 col-sm-8 col-xs-12">
                        <input type="text"  name="transact_id" id="transact_id"  value="<?php echo $_SESSION['search_tracing_data']['s_transact_id']; ?>" class="form-control data-search-field" placeholder="Transact ID">
                    </div>



                </div>

                <div class="form-group">                      	

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Order Status</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">

                        <select name="order_status" id="order_status" class="form-control data-search-field">
                            <option value="">Select</option>
                            <option value="initiated" <?php if ($_SESSION['search_tracing_data']['s_order_status'] == 'initiated') echo 'selected="selected"'; ?> >Initated</option>
                            <option value="failed" <?php if ($_SESSION['search_tracing_data']['s_order_status'] == 'failed') echo 'selected="selected"'; ?>>Failed</option>
                            <option value="success" <?php if ($_SESSION['search_tracing_data']['s_order_status'] == 'success') echo 'selected="selected"'; ?>>Success</option>
                            <option value="not_accepted" <?php if ($_SESSION['search_tracing_data']['s_order_status'] == 'not_accepted') echo 'selected="selected"'; ?>>Not Accepted</option>                 
                            <option value="card_attempt" <?php if ($_SESSION['search_tracing_data']['s_order_status'] == 'card_attempt') echo 'selected="selected"'; ?>>Card Pay Attempt</option>
                        </select>
                    </div>    

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Payment Method</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <select name="payment_method" id="payment_method" class="form-control data-search-field">
                            <option value="">Select</option>
                            <option value="paypal" <?php if ($_SESSION['search_tracing_data']['s_payment_method'] == 'paypal') echo 'selected="selected"'; ?> >Paypal</option>
                            <option value="ccavenue" <?php if ($_SESSION['search_tracing_data']['s_payment_method'] == 'ccavenue') echo 'selected="selected"'; ?>>Ccavenue</option>
                            <option value="secure_trading" <?php if ($_SESSION['search_tracing_data']['s_payment_method'] == 'secure_trading') echo 'selected="selected"'; ?>>Secure Trading</option>
                        </select>
                    </div>

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Order ID</label>
                    <div class="col-md-2 col-sm-8 col-xs-12">
                        <input type="text"  name="order_id" id="order_id" value="<?php echo $_SESSION['search_tracing_data']['s_order_id']; ?>" class="form-control data-search-field" placeholder="Order Id">
                    </div>     
                </div>

                <div class="form-group">       
                    <label class="control-label col-md-2 col-sm-4 col-xs-12">Order Date</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <input type="text" name="order_date" id="order_date" class="form-control data-search-field" value="<?php if (isset($_SESSION['search_tracing_data']['s_order_date'])) echo $_SESSION['search_tracing_data']['s_order_date']; ?>" readonly="readonly" data-parsley-required="" />
                    </div> 

                    <div class="form-group">       
                        <label class="control-label col-md-2 col-sm-4 col-xs-12">Card Number</label>
                        <div class="col-md-4 col-sm-9 col-xs-12">
                            <input type="text"  name="card_number" id="card_number" value="<?php echo $_SESSION['search_tracing_data']['s_card_number']; ?>" class="form-control data-search-field" placeholder="Order Id">
                        </div> 
                    </div>
                    <div class="form-group"> 
                        <div class="searchBar col-md-6 col-sm-9 col-xs-12">      
                            <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                            <input type="submit" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">    
                        </div>

                    </div> 

            </form> 


            <div class="clearfix"></div>
            <div class="ln_solid"></div>
           
            <div class="row">  
                <?php dispay_pagination_row($total_records, $_SESSION['search_tracing_data']['no_of_rows'], $pagination); ?>
            </div>
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">                           
                            <th class="column-title">Account ID </th>
                            <th class="column-title" width="100">Date Time </th>
                            <th class="column-title">Amount </th> 
                            <th class="column-title" width="100"><nobr>Pay Method</nobr></th> 
                    <th class="column-title" width="100">Status </th>
                    <th class="column-title">Order ID </th>                           

                    </tr>
                    </thead>

                    <tfoot>
                        <tr class="headings thc">                           
                            <th class="column-title">Account ID </th>
                            <th class="column-title" width="100">Date Time </th>
                            <th class="column-title">Amount </th> 
                            <th class="column-title" width="100"><nobr>Pay Method</nobr></th> 
                    <th class="column-title" width="100">Status </th>
                    <th class="column-title">Order ID </th>

                    </tr>
                    </tfoot>	

                    <tbody>
                        <?php
                        if (count($trace_data['result']) > 0) {
                            foreach ($trace_data['result'] as $rec) {

                                $order_timestamp = strtotime($rec['order_date']);
                                $order_date = date(DATE_FORMAT_2, $order_timestamp);


                                if ($rec['order_status'] == 'initiated')
                                    $status = '<span class="label label-info">Initiated</span>';
                                elseif ($rec['order_status'] == 'card_attempt')
                                    $status = '<span class="label label-primary">Card Pay Attempt</span>';
                                elseif ($rec['order_status'] == 'failed')
                                    $status = '<span class="label label-danger">Failed</span>';
                                elseif ($rec['order_status'] == 'success')
                                    $status = '<span class="label label-success">Success</span>';
                                else
                                    $status = '<span class="label label-warning">Not Accepted</span>';

                                if ($rec['payment_method'] == 'paypal')
                                    $payment_method = 'Paypal';
                                elseif ($rec['payment_method'] == 'ccavenue')
                                    $payment_method = 'CCAvenue';
                                elseif ($rec['payment_method'] == 'secure_trading')
                                    $payment_method = 'Secure Trading';
                                else
                                    $payment_method = $rec['payment_method'];
                                ?>
                                <tr>
                                    <td><?php echo $rec['company_name'] . ' (' . $rec['account_id'] . ')'; ?></td>
                                    <td><nobr><?php echo $order_date; ?></nobr></td>
                            <td><?php echo $rec['amount']; ?></td>
                            <td><?php echo $payment_method; ?></td>
                            <td><?php echo $status; ?></td>
                            <td> <a href="<?php echo base_url(); ?>crs/payment/trace_details/<?php echo param_encrypt($rec['order_id']); ?>" title="Order Details"><u><?php echo $rec['order_id']; ?></u></a>
                            </td>


                            </tr>

                            <?php
                        }
                    }
                    else {
                        ?>
                        <tr>
                            <td colspan="6" align="center"><strong>No Record Found</strong></td>
                        </tr>
                        <?php
                    }
                    ?>                          

                    </tbody>
                </table>
            </div>                    
            <?php echo '<div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
                           ' . $pagination . '
                  </div>
                </div>'; ?>

        </div>
    </div>
</div>



<style type="text/css">
    fixedHeader-floating{position:fixed;}
    table.jambo_table tfoot {
        background: rgba(52,73,94,.94);
        color: #ECF0F1;
    }
</style>

<script>

    $(document).ready(function () {

        $("#order_date").daterangepicker({
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
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Month': [moment().startOf('month'), moment().endOf('month')]

            }
        });




    });

    $(document).ready(function () {
        showDatatable('table-sort', [], [1, "desc"]);
    });
</script>