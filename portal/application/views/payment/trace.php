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
