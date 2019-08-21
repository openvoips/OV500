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
//echo '<pre>'; print_r($trace_data);echo '</pre>';
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Order Details</h2>
            <ul class="nav navbar-right panel_toolbox">
                

            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 

            <table class="table table-striped jambo_table  table-bordered">

                <tbody>  
                    <?php
                    $order_timestamp = strtotime($trace_data['order_date']);
                    $order_date = date(DATE_FORMAT_2, $order_timestamp);


                    if ($trace_data['order_status'] == 'initiated')
                        $status = '<span class="label label-info">Initiated</span>';
                    elseif ($trace_data['order_status'] == 'card_attempt')
                        $status = '<span class="label label-primary">Card Pay Attempt</span>';
                    elseif ($trace_data['order_status'] == 'failed')
                        $status = '<span class="label label-danger">Failed</span>';

                    elseif ($trace_data['order_status'] == 'success')
                        $status = '<span class="label label-success">Success</span>';
                    else
                        $status = '<span class="label label-warning">Not Accepted</span>';



                    if ($trace_data['payment_method'] == 'paypal')
                        $payment_method = 'Paypal';

                    elseif ($trace_data['payment_method'] == 'ccavenue')
                        $payment_method = 'CCAvenue';

                    elseif ($trace_data['payment_method'] == 'secure_trading')
                        $payment_method = 'Secure Trading';
                    ?>

                    <tr>     
                        <td>Order ID</td>                      
                        <td><?php echo $trace_data['order_id']; ?></td>
                    </tr>    
                    <tr>     
                        <td>Amount</td>                      
                        <td><?php echo $trace_data['amount']; ?></td>
                    </tr>  
                    <tr>     
                        <td>Date</td>                      
                        <td><?php echo $order_date; ?></td>
                    </tr>      
                    <tr>     
                        <td>Status</td>                      
                        <td><?php echo $status; ?></td>
                    </tr>    
                    <tr>     
                        <td>Transaction ID</td>                      
                        <td><?php echo $trace_data['tracking_id']; ?></td>
                    </tr> 
                    <tr>     
                        <td>Pay Method</td>                      
                        <td><?php echo $payment_method; ?></td>
                    </tr> 
                    <?php
                    if ($trace_data['order_status'] == 'card_attempt') {
                        ?>
                        <tr>     
                            <td>Send data</td>                      
                            <td><?php echo ($trace_data['send_string'] != '') ? '<pre>' . $trace_data['send_string'] . '</pre>' : ''; ?></td>
                        </tr>   
                        <?php
                    } else {
                        ?> 
                        <tr>     
                            <td>Response</td>                      
                            <td><?php echo ($trace_data['response_string'] != '') ? '<pre>' . $trace_data['response_string'] . '</pre>' : ''; ?></td>
                        </tr>   

                    <?php }
                    ?>    

                </tbody>
            </table>



        </div>
        <div style="float:right"> 
            <a href="<?php echo base_url(); ?>payment/trace/<?php echo param_encrypt($rec['order_id']); ?>"><?php echo $rec['order_id']; ?><input type="button" value="Back" name="search_reset" id="search_reset" class="btn btn-info"></a>
        </div>
    </div>
</div>
