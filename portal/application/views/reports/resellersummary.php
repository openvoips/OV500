<?php
if (isset($user_result)) {
    $is_user_details_exists = true;
    $dp = 4;
    if (in_array(strtolower($user_result['user_type']), array('user', 'reseller')) && $user_result['dp'] != '')
        $dp = $user_result['dp'];
}
?>
<style type="text/css">
    th.big_td{
        line-height: 40px!important;
        font-size:14px;
    }
    td.big_td{
        line-height: 40px!important;
    }
</style> 
<div class="container-fluid">
    <div class="card">
        <div class="header">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="x_panel">

                    <div class="x_title">
                        <h3>TODAY</h3>     
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <p> Outbound Calls: <strong> <?php echo $today_callcost['OUT']['calls']; ?> </strong>   Cost  <strong> <?php echo $currency['symbol'] . "" . number_format($today_callcost['OUT']['customer_callcost_total'], 4); ?>  </strong> </p>
                    </div>
                    <div class="col-md-8 col-sm-8 col-xs-12">
                        <p>Inbound Calls: <strong> <?php echo $today_callcost['IN']['calls']; ?> </strong>      Cost  <strong> <?php echo $currency['symbol'] . "" . number_format($today_callcost['IN']['customer_callcost_total'], 4); ?>  </strong> </p>
                    </div> May not include fees
                </div>
            </div>

            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h3>THIS MONTH</h3>           
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="col-md-8 col-sm-8 col-xs-12">
                            <p>Outbound Calls: <strong> <?php echo $month_callcost['OUT']['calls']; ?> </strong> Cost  <strong> <?php echo $currency['symbol'] . "" . number_format($month_callcost['OUT']['customer_callcost_total'], 4); ?> </strong> </p>
                        </div>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                            <p>Inbound Calls: <strong> <?php echo $month_callcost['IN']['calls']; ?> </strong>  Cost  <strong> <?php echo $currency['symbol'] . "" . number_format($month_callcost['IN']['customer_callcost_total'], 4); ?> </strong> </p>
                        </div> May not include fees
                    </div>
                </div>
            </div>


            <div class="x_panel">
                <div class="x_title">
                    <h3>ACCOUNT SUMMARY</h3> Balance may include advanced credit and processing fees.<h2>       
                        <div class="clearfix"></div>
                </div>
                <div class="x_content">


                    <p> Payments Process Today: <strong>  <?php echo $currency['symbol'] . "" . number_format($today_spent_data['balance'], 4); ?>  </strong>   
                        |  Current Month Payment(s): <strong>  <?php echo $currency['symbol'] . "" . number_format($month_spent_data['balance'], 4); ?>  </strong>   
                        |  Estimated Charges: <strong>  <?php echo $currency['symbol'] . "" . (number_format($today_spent_data['usage'], 4) + number_format($today_callcost['OUT']['customer_callcost_total'], 4) + number_format($today_callcost['IN']['customer_callcost_total'], 4)) * -1; ?> </strong>   
                        |  Estimated Balance (fees may not be included):  <strong>  <?php echo $currency['symbol'] . "" . number_format((0 - $currentBalance['balance']), 4); ?> </strong> </p>

                </div>
            </div>

            <?php if ($plan_data) { ?>
                <div class="x_panel">

                    <div class="x_title">
                        <h3>Service Plan Detail</h3> Your Plan will be automatically removed once offer limit will consumed.  You may add additional plans to your account at anytime with the help of system administrator.</h2>          
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">

                        <div class="table-responsive">
                            <table  id="bundlelist" class="table table-striped jambo_table bulk_action table-bordered">
                                <thead>
                                    <tr class="headings thc">
                                        <th class="column-title"><nobr>Plan Name</nobr></th>
                                <th class="column-title"><nobr>Cost</nobr></th>
                                <th class="column-title"><nobr>Allowed Prefix(s)</nobr></th>
                                <th class="column-title"><nobr>Allowed / Used (Sec)</nobr></th>



                                <th class="column-title"><nobr>Purchased Date</nobr></th>
                                </tr>
                                </thead>
                                <tbody>

                                    <?php foreach ($plan_data as $data) {
                                        ?>
                                        <tr>
                                            <td><?php echo $data['bundle_package_name'].'('.$data['bundle_for'].')'; ?></td>

                                            <td><?php echo $currency['symbol'] . "" . number_format($data['monthly_charges'], 4); ?></td> 

                                            <td><?php echo wordwrap($data['prefix1'], 45, ",<br>\n", TRUE); ?></td>

                                            <td><?php echo number_format($data['total_allowed'] *60, 0) . " / <strong style='color:red'>" . number_format($data['sdr_consumption'] * 60, 0) . "</strong> "; ?></td>


                                            <td><?php echo $data['action_date']; ?></td>


                                        </tr> 


                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div></div></div>           
<div class="clearfix"></div>

 
