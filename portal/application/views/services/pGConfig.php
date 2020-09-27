<?php
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

//echo '<pre>';
//print_r($data);
//echo '</pre>';
if ($data['payment_method'] == 'paypal-sdk') {
    $data2 = ((array) (json_decode($data['credentials'])));
}
?>

<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Payment Configuration Management</h2>            
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="x_panel">
        <!--        <div class="x_title">
                    <h2>Payment gateway Config Management</h2>
                    <ul class="nav navbar-right panel_toolbox">
                    </ul>
                    <div class="clearfix"></div>
                </div>-->
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="add_form" name="add_form" ction="<?php echo base_url(); ?>sysconfig/pGConfig" method="post" >
                <input type="hidden" name="action" value="OkSaveData">
                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Payment Gateway Name<span class="required">*</span></label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <select name="paymentgateway" id="paymentgateway" class="form-control data-search-field">
                            <option value="">Select Currency</option>
                            <option value="PayPal" selected>PayPal</option> 
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Business Name<span class="required">*</span></label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <input type="text" name="business" id="business" value="<?php echo $data2['business']; ?>" class="form-control col-md-7 col-xs-12">
                    </div>
                </div>


                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Token Key<span class="required">*</span></label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <input type="text" name="pdt_identity_token" id="pdt_identity_token" value="<?php echo $data2['pdt_identity_token']; ?>"  class="form-control col-md-7 col-xs-12">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name"></label>
                    <div class="col-md-8 col-sm-6 col-xs-12 searchBar ">
                        <button type="button" id="btnSave" class="btn btn-success">Save Payment Gateway Config</button>
                    </div>
                </div>
            </form> 

            <div class="clearfix"></div>           
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Payment Configuration Management</h2>            
            <div class="clearfix"></div>
        </div>

    </div>
</div>    
<script>
    $('#btnSave, #btnSaveClose').click(function () {
        var is_ok = $("#add_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#add_form").submit();
        } else
        {
            $('#add_form').parsley().validate();
        }
    })
</script>