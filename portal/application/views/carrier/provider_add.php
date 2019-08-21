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
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$tab_index = 1;
//echo '<pre>';
//print_r($currency_conversion['result']);
//print_r($currency_options);
//echo '</pre>';	
?>


<div class="">
    <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Provider Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('providers') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Provider Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12"> 
        <div class="x_panel">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_title">
                    <h2>Provider Management (ADD)</h2>
                    <ul class="nav navbar-right panel_toolbox">
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form action="<?php echo base_url(); ?>providers/add" method="post" name="provider_form" id="provider_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Provider Name <span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                	
                                <input type="text" name="provider_name" id="provider_name" value="<?php echo set_value('provider_name'); ?>" class="form-control" data-parsley-required="" data-parsley-minlength="4" tabindex="<?php echo $tab_index++; ?>">       
                            </div>
                        </div> 

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Select Currency <span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <select name="currency_id" id="currency_id" class="form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="" >Select</option>                    
                                    <?php
                                    $str = '';
                                    $customer_currency_id = get_logged_account_currency();
                                    foreach ($currency_options as $currency_array) {
                                        $selected = ' ';
                                        if (set_value('currency_id') == $currency_array['currency_id'])
                                            $selected = '  selected="selected" ';

                                        if (check_logged_account_type(array('CUSTOMER', 'CARRIER', 'RESELLER'))) {
                                            if ($currency_array['currency_id'] != $customer_currency_id)
                                                continue;
                                        }
                                        $str .= '<option value="' . $currency_array['currency_id'] . '" ' . $selected . '>' . $currency_array['symbol'] ." - ". $currency_array['name'] . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Address </label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <textarea name="provider_address" id="provider_address" class="form-control col-md-7 col-xs-12"  tabindex="<?php echo $tab_index++; ?>"> <?php echo set_value('provider_address'); ?></textarea>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="last-name">Email Address </label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <input type="text" name="provider_emailid" id="provider_emailid" value="<?php echo set_value('provider_emailid'); ?>" data-parsley-type="email" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>

                        <div class="ln_solid"></div> 
                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4 text-right">
                                <!--<a href="<?php echo base_url() ?>providers"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Cancel</button></a>-->
                                <button type="button" id="form_btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save</button>
                                <button type="button" id="form_btnSaveClose" class="btn btn-info" tabindex="<?php echo $tab_index++; ?>">Save & Go back to Edit Page</button>
                            </div>
                        </div>
                    </form>   
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="ln_solid"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Provider Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('providers') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Provider Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

</div>

<script>
    window.Parsley
            .addValidator('price', {
                validateString: function (value) {
                    return true == (/^\d+(?:[.,]\d+)*$/.test(value));
                },
                messages: {
                    en: 'This value should be in price format'
                }
            }
            );


//////////////////////////////
    function provider_type_changed()
    {
        if ($('#provider_term').val() == '')
        {
            $('#id_div_description').html('');
        } else
        {
            var element = $('#provider_term').find('option:selected');
            var description = element.attr("data-description");
            $('#id_div_description').html(description);
        }
        buy_rate_update();
    }

    function buy_rate_update()
    {
        var buy_rate = '';
        if ($('#provider_term').val() == '' || $('#currency_id').val() == '')
        {
            $('#id_div_hosteddialler').addClass('hide');
        } else
        {
            var element = $('#provider_term').find('option:selected');
            var currency_id = $('#currency_id').val();

            buy_rate = element.attr("data-buy-rate-" + currency_id);
        }


        if (buy_rate != '')
        {
            $('#buy_rate').val(buy_rate);
            $('#id_div_hosteddialler').removeClass('hide');

            $('#buy_rate').attr('data-parsley-required', 'true');
            $('#buy_rate').attr('data-parsley-price', 'true');
        } else
        {
            $('#buy_rate').val('');
            $('#id_div_hosteddialler').addClass('hide');

            $('#buy_rate').attr('data-parsley-required', 'false');
            $('#buy_rate').removeAttr('data-parsley-price');
        }


    }

    $("#provider_term").change(function () {
        provider_type_changed();
    });
    $("#currency_id").change(function () {
        buy_rate_update();
    });
    $(document).ready(function () {
        provider_type_changed();
    });

//////////////////////
    $('#form_btnSave, #form_btnSaveClose').click(function () {
        var is_ok = $("#provider_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'form_btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');


            $("#provider_form").submit();
        } else
        {
            $('#provider_form').parsley().validate();
        }
    });
</script>