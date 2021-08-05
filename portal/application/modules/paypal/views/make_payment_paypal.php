<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0.3
// License https://www.gnu.org/licenses/agpl-3.0.html
//
//
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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
$dp = 2;
?>
<div class="col-md-8 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Paypal Payment</h2>
            <ul class="nav navbar-right panel_toolbox">
                
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content" id="id_x_content">
            <div id="st-message" class=" fade in"  ></div>
         			
              
                        <form action="" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                            <input type="hidden" name="action" value="OkPay"> 
                            <input type="hidden" name="method" id="method" value="paypal" />
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
                                <div class="col-md-7 col-sm-6 col-xs-12">
                                    <input type="text" name="amount" id="amount" value=""  class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-pattern="/^\d+(\.\d{1,<?php echo $dp; ?>})?$/" data-parsley-pattern-message="Positive number with maximum <?php echo $dp; ?> decimal"  >
                                </div>
                            </div>   
                              
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                                <div class="col-md-7 col-sm-6 col-xs-12 ">			
                                    <button type="button" id="btnSave" class="btn btn-primary btn-lg active btn-block"><strong>Make Payment</strong></button>
                                </div>
                            </div>  

                        </form>           
                       
                      

        </div>
    </div>
</div>    


<div class="col-md-4 col-sm-6 col-xs-12" >
    <div class="x_panel">
        <div class="x_title">
            <h2>Balance</h2>
            <ul class="nav navbar-right panel_toolbox">                
            </ul>
            <div class="clearfix"></div>
        </div>

		<div class="x_content">
        	<table class="table table-bordered"> 
                <tr >
                    <th>Balance</th>
                    <th><?php echo number_format(-$account_result['balance']['balance'], $dp, '.', ''); ?> </th>
                </tr>
                <tr >
                    <th>Temporary Credit </th>
                    <th><?php echo number_format($account_result['balance']['credit_limit'], $dp, '.', ''); ?> </th>
                </tr>
                <tr >
                    <th>Available Balance</th>
                    <th><?php echo number_format($account_result['balance']['usable_balance'], $dp, '.', ''); ?> </th>
                </tr>
            </table>
        </div>
        
    </div>
</div> <div class="clearfix"></div>		
<?php // ddd($payment_gateways_result);?>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>