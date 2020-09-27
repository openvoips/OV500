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
//echo '<pre>';print_r($pay_data_array);echo '</pre>';die;
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Processing</h2>

            <div class="clearfix"></div>
        </div>

        <div class="x_content">
            <div class="col-md-12 col-sm-6 col-xs-12 text-center" id="search_loader" style="margin:0 auto; ">
                <img src="<?php echo base_url(); ?>theme/default/images/loading.gif">
            </div>


            <form name="redirect" id="redirect" action="<?php echo PAYPAL_LINK; ?>" method="post">   
                <input type="hidden" name="business" value="<?php echo $pay_data_array['business']; ?>"> 
                <input type="hidden" name="cmd" value="<?php echo $pay_data_array['cmd']; ?>">

                <input type="hidden" name="return" value="<?php echo $pay_data_array['return']; ?>">
                <input type="hidden" name="cancel_return" value="<?php echo $pay_data_array['cancel_return']; ?>" />            
                <input type="hidden" name="rm" value="2" />

                <input type="hidden" name="item_name" value="<?php echo $pay_data_array['item_name']; ?>"> 
                <input type="hidden" name="item_number" value="<?php echo $pay_data_array['item_number']; ?>"> 
                <input type="hidden" name="no_shipping" value="<?php echo $pay_data_array['no_shipping']; ?>"> 
                <input type="hidden" name="no_note" value="<?php echo $pay_data_array['no_note']; ?>">

                <input type="hidden" name="amount" value="<?php echo $pay_data_array['amount']; ?>">                
                <input type="hidden" name="currency_code" value="<?php echo $pay_data_array['currency_code']; ?>">
                <!--<input type="hidden" name="image_url" value="http://I-Solution.co.uk/images/logo.jpg">-->
                <input type="hidden" name="first_name" value="<?php echo $pay_data_array['payer_first_name']; ?>"> 
                <input type="hidden" name="last_name" value="<?php echo $pay_data_array['payer_last_name']; ?>"> 
                <input type="hidden" name="email" value="<?php echo $pay_data_array['payer_email']; ?>">
            </form>
            <script>
                $('#redirect').submit();
                //exit();
            </script>

        </div>
    </div>       
</div>    
