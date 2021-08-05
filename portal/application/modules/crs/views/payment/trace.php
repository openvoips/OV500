<?php
$trace_data = $trace_data['result'];
?>

<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Delete Log Details</h2>
            <ul class="nav navbar-right panel_toolbox">


            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 

            <table class="table table-striped jambo_table  table-bordered">

                <tbody>                        
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
                        <td><?php echo $trace_data['order_date']; ?></td>
                    </tr>      
                    <tr>     
                        <td>Status</td>                      
                        <td><?php echo $trace_data['order_status']; ?></td>
                    </tr>    
                    <tr>     
                        <td>Transaction ID</td>                      
                        <td><?php echo $trace_data['tracking_id']; ?></td>
                    </tr> 
                    <tr>     
                        <td>Pay Method</td>                      
                        <td><?php echo $trace_data['payment_method']; ?></td>
                    </tr> 
                    <tr>     
                        <td>Response</td>                      
                        <td><?php
                            echo '<pre>';
                            echo $trace_data['response_string'];
                            echo '</pre>';
                            ?></td>
                    </tr>       

                </tbody>
            </table>



        </div>
    </div>
</div>
