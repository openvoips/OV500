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
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script>
    $(document).ready(function () {
        $('.combobox').combobox()
    });
</script>


<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
if (isset($logged_account_result['tariff']))
    $tariff_options[] = $logged_account_result['tariff'];
$tab_index = 1;
$dp = 4;
$vatflag_array = array('NONE', 'TAX', 'VAT');
//echo '<pre>';
////  print_r($logged_account_result);
//print_r($tariff_options);
//print_r($currency_options);
//echo '</pre>';
?>    
<div class="">
    <div class="clearfix"></div>  
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Customer Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li> <a href="<?php echo base_url() ?>customers"><button class="btn btn-danger" type="button">Back to Customer Listing Page</button></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <form action="<?php echo base_url(); ?>customers/add" method="post" name="account_form" id="account_form" data-parsley-validate class="form-horizontal form-label-left">
        <input type="hidden" name="button_action" id="button_action" value="">
        <input type="hidden" name="action" value="OkSaveData">
        <input type="hidden" name="credit_limit" id="credit_limit" value="0">

        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Customers (ADD)</h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <?php
                    if (get_logged_account_level() == 0) {
                        ?>
                        <div class="form-group">
                            <label class=" col-md-4 col-sm-3 col-xs-12">Customers Type</label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                    

                                <div class="col-md-12 col-sm-6 col-xs-12 radio1">
                                    <label><input type="radio" name="account_type" id="account_type_real" value="REAL" <?php echo set_radio('account_type', 'REAL', TRUE); ?> tabindex="<?php echo $tab_index++; ?>" /> LIVE</label>	

                                    <label><input type="radio" name="account_type" id="account_type_demo" value="DEMO" <?php echo set_radio('account_type', 'DEMO'); ?> tabindex="<?php echo $tab_index++; ?>" /> DEMO</label>	
                                </div>
                            </div>
                        </div>
                    <?php }
                    ?> 

                    <?php
                    if (check_logged_account_type(array('ADMIN', 'SUBADMIN'))) {
                        ?>                  
                    <?php } ?>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Web Access Username <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="username" id="username" value="<?php echo set_value('username'); ?>" data-parsley-required="" data-parsley-type="alphanum" data-parsley-minlength="6" data-parsley-maxlength="30"  class="form-control col-md-7 col-xs-12" autocomplete="off" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Web Access Password <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="secret" id="secret" value="<?php echo set_value('secret'); ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-minlength="8" data-parsley-maxlength="30" data-parsley-password="" autocomplete="off" tabindex="<?php echo $tab_index++; ?>">
                        </div>                        
                    </div>
                        <?php
                    if (check_logged_account_type(array('ADMIN', 'SUBADMIN'))) {
                        ?> 
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Currency <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="currency_id" id="currency_id" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select</option>                    
                                    <?php
                                    $str = '';
                                    foreach ($currency_options as $key => $currency_array) {
                                        $selected = ' ';
                                        if (set_value('currency_id') == $currency_array['currency_id'])
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $currency_array['currency_id'] . '" ' . $selected . '>' . $currency_array['symbol'] . " - " . $currency_array['name'] . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php
                    }
                    else {
                        echo '<input type="hidden" name="currency_id" id="currency_id" value="' . $logged_account_result['currency_id'] . '" data-parsley-required="" class="form-control" >';
                    }
                    ?>  
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Customer Tariff <span class="required">*</span>
                        </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="tariff_id" id="tariff_id" class="form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>                    

                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Billing Type <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="billing_type" id="billing_type" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">                                                 
                                <?php
                                $billing_type_array = array('prepaid' => 'Prepaid', 'postpaid' => 'Postpaid');
                                $str = '';
                                foreach ($billing_type_array as $key => $billing_type) {
                                    $selected = ' ';
                                    if (set_value('billing_type') == $key)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $key . '" ' . $selected . '>' . ucfirst($billing_type) . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="billing_cycle" value="monthly">
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Payment Terms from Invoice Date <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="payment_terms" id="payment_terms" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">                                       
                                <?php
                                $payment_terms_array = range(1, 30);
                                $str = '';
                                foreach ($payment_terms_array as $key => $payment_terms) {
                                    $selected = ' ';
                                    if (set_value('payment_terms') == $payment_terms)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $payment_terms . '" ' . $selected . '>' . ucfirst($payment_terms) . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>  


<!--                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Max Credit Limit <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="maxcredit_limit" id="maxcredit_limit" value="<?php echo set_value('maxcredit_limit', '0'); ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>-->

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Billing in Decimal <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="dp" id="dp" value="<?php echo set_value('dp', $dp); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>

                    <div class="form-group" id="div_id_vat_flag">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">VAT / Tax Flag<span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="vat_flag" id="vat_flag" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">                               
                                <?php
                                $str = '';
                                foreach ($vatflag_array as $key => $vat) {
                                    $selected = ' ';
                                    if (set_value('vat_flag', 'NONE') == $vat)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $vat . '" ' . $selected . '>' . $vat . '</option>';
                                }
                                echo $str;
                                ?>  
                            </select>                            
                        </div>
                    </div>



                    <div id ="taxchange">
                        <div class="form-group" id="div_id_tax_type">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax on bill Amount Calculation <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="tax_type" id="tax_type" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">                                                   
                                    <?php
                                    $tax_type_array = array('exclusive' => 'Tax On Bill Amount (exclusive)', 'inclusive' => 'Bill Amount with Tax (inclusive)');
                                    $str = '';
                                    foreach ($tax_type_array as $key => $tax_type) {
                                        $selected = ' ';
                                        if (set_value('tax_type', 'inclusive') == $tax_type)
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $key . '" ' . $selected . '>' . ucfirst($tax_type) . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax Certificate Number</label>
                            <div class="col-md-7 col-sm-6 col-xs-10">
                                <input type="text" name="tax_number" id="tax_number" value="<?php echo set_value('tax_number'); ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>

                        <div class="form-group tax_class">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 1(%) <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-10">
                                <input type="text" name="tax1" id="tax1" value="<?php echo set_value('tax1', '0'); ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                            </div>                       
                        </div>
                        <div class="form-group tax_class">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 2(%) <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-10">
                                <input type="text" name="tax2" id="tax2" value="<?php echo set_value('tax2', '0'); ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                            </div>                        
                        </div>
                        <div class="form-group tax_class">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 3(%) <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-10">
                                <input type="text" name="tax3" id="tax3" value="<?php echo set_value('tax3', '0'); ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                            </div>                       
                        </div>

                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Maximum Call Sessions <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="account_cc" id="account_cc" value="<?php echo set_value('account_cc', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Call Sessions per Second <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="account_cps" id="account_cps" value="<?php echo set_value('account_cps', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Codec Checking</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="codecs_force" id="codecs_force1" value="1" <?php echo set_radio('codecs_force', '1', TRUE); ?> tabindex="<?php echo $tab_index++; ?>"/> Yes</label>
                                <label> <input type="radio" name="codecs_force" id="codecs_force2" value="0" <?php echo set_radio('codecs_force', '0'); ?> tabindex="<?php echo $tab_index++; ?>"/> No</label>
								<label>Only possible if G729 codec is installed in system.</label>
                            </div>                     
                        </div>
                    </div>  

                    <?php
                    $codecs_array = array('G729', 'PCMU', 'PCMA', 'G722');
                    ?>
                    <div class="form-group">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">Codecs List</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <?php
                            $data['account_codecs'] = '';
                            foreach ($codecs_array as $key => $codec) {
                                if (strpos($data['account_codecs'], $codec) !== FALSE)
                                    $checked = 'checked="checked"';
                                else
                                    $checked = '';
                                if ($codec == 'G729')
                                    $checked = true;
                                else
                                    $checked = false;
                                echo '<div class="checkbox">' .
                                '<label><input type="checkbox" name="codecs[]" id="codec' . $key . '" value="' . $codec . '"  tabindex="' . $tab_index++ . '"' . set_checkbox('codecs[]', $codec, $checked) . '/> ' . $codec . '</label>' .
                                '</div>';
                            }
                            ?>  
                        </div>
                    </div>



                    <div class="form-group">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">Call With Media</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="media_rtpproxy" id="_media_rtpproxy1" value="1" <?php echo set_radio('media_rtpproxy', '1'); ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>
                                <label> <input type="radio" name="media_rtpproxy" id="media_rtpproxy2" value="0" <?php echo set_radio('media_rtpproxy', '0', TRUE); ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>

                    <div class="form-group" id="id_transcoding_div">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">Codecs Transcoding</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="media_transcoding" id="media_transcoding1" value="1" <?php echo set_radio('media_transcoding', '1'); ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>                          
                                <label> <input type="radio" name="media_transcoding" id="media_transcoding2" value="0" <?php echo set_radio('media_transcoding', '0', TRUE); ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>                     
                        </div>
                    </div> 


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Don't Allow Call With Loss Route</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="loss_carrier_check" id="loss_carrier_check1" value="1" <?php echo set_radio('loss_carrier_check', '1'); ?> tabindex="<?php echo $tab_index++; ?>"/> Yes</label>                            
                                <label> <input type="radio" name="loss_carrier_check" id="loss_carrier_check2" value="0" <?php echo set_radio('loss_carrier_check', '0', TRUE); ?> tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>                     
                        </div>
                    </div>   


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Change CLI Based On DST Prefix</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="force_dst_src_cli_prefix" id="force_dst_src_cli_prefix1" value="1" <?php echo set_radio('force_dst_src_cli_prefix', '1'); ?> tabindex="<?php echo $tab_index++; ?>"/> Yes</label>                            
                                <label> <input type="radio" name="force_dst_src_cli_prefix" id="force_dst_src_cli_prefix2" value="0" <?php echo set_radio('force_dst_src_cli_prefix', '0', TRUE); ?> tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>                     
                        </div>
                    </div> 

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Max Call Duration (Minutes)<span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="max_callduration" id="max_callduration" value="<?php echo set_value('max_callduration', '120'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>		                  
                </div>

            </div>
        </div>

        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Registered Address</h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Name <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="name" id="name" value="<?php echo set_value('name'); ?>"  data-parsley-required="" data-parsley-minlength="3" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="last-name">Company <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="company_name" id="company_name" value="<?php echo set_value('company_name'); ?>" data-parsley-required=""  class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="last-name">Email Address <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="emailaddress" id="emailaddress" value="<?php echo set_value('emailaddress'); ?>"  data-parsley-required="" data-parsley-type="email" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="last-name">Address </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <textarea name="address" id="address" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>"><?php echo set_value('address'); ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Phone Number </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="phone" id="phone" value="<?php echo set_value('phone'); ?>" data-parsley-type="digits" data-parsley-minlength="10" data-parsley-maxlength="12" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Country </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="country_id" id="country_id" class="combobox form-control" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>                    
                                <?php
                                $str = '';
                                foreach ($country_options as $key => $country_array) {
                                    $selected = ' ';
                                    if (set_value('country_id') == $country_array->country_id)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $country_array->country_id . '" ' . $selected . '>' . $country_array->country_name . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="id_state_div">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >State </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="state_code_id" id="state_code_id" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>                    
                                <?php
                                $str = '';
                                foreach ($state_options as $key => $state_array) {
                                    $selected = ' ';
                                    if (set_value('state_code_id') == $state_array['state_code_id'])
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $state_array['state_code_id'] . '" ' . $selected . '>' . $state_array['state_name'] . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >PIN</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="pincode" id="pincode" value="<?php echo set_value('pincode'); ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <input type="hidden" name="account_status" id="status1" value="1" />        
        <div class="x_content">                    
            <div class="ln_solid"></div>
            <div class="form-group">
                <div class="col-md-12 col-sm-12 col-xs-12 col-md-offset-5">
                    <button type="button" id="btnSave" class="btn btn-success" >Save</button>
                    <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Listing Page</button>
                </div>
            </div>
            <div class="ln_solid"></div>
        </div>

    </form>




    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Customer Account Configuration Management form ending</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li> <a href="<?php echo base_url() ?>customers"><button class="btn btn-danger" type="button">Back to Customer Listing Page</button></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
</div>    
<script>
    var tariff_array = [];
    tariff_array[1] = new Array();
    tariff_array[2] = new Array();
    tariff_array[3] = new Array();
    tariff_array[4] = new Array();
    tariff_array[5] = new Array();
</script>
<?php
$k = 0;
if (count($tariff_options) > 0) {
    foreach ($tariff_options as $tariff_name_array) {
        ?>
        <script>
            tariff_array[<?php echo $tariff_name_array['tariff_currency_id']; ?>]["<?php echo $k; ?>"] = ["<?php echo $tariff_name_array['tariff_id']; ?>", "<?php echo $tariff_name_array['tariff_name']; ?>"];
        </script>
        <?php
        $k++;
    }
}
?>

<script>
    var tax_fixed_rules_apply = "N";

    window.Parsley
            .addValidator('password', {
                validateString: function (value) {
                    r = true;
                    if (!vCheckPassword(value))
                    {
                        r = false;
                    }
                    return r;
                },
                messages: {
                    en: 'min 8 char, 1 special char, 1 uppercase, 1 lowercase, 1 number'
                }
            });


    $('#btnSave, #btnSaveClose').click(function () {

        $('#account_form').parsley().reset();

        var is_ok = $("#account_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            if (is_ok === true)
            {
                //alert("ok");
                $("#account_form").submit();
            }
        } else
        {
            $('#account_form').parsley().validate();
        }

    })




    function media_changed()
    {
        media_value = $("input[name='media_rtpproxy']:checked").val();
        if (media_value == '1')
            $('#id_transcoding_div').show();
        else
            $('#id_transcoding_div').hide();
    }

    function currency_changed()
    {
        var tariff_str = '<option value="">Select</option>';
        currency_id = $("#currency_id").val();
        if (currency_id == '')
        {

        } else
        {
            var arrayLength = tariff_array[currency_id].length;

            if (arrayLength > 0)
            {
                for (var i in tariff_array[currency_id])
                {
                    tariff_id = tariff_array[currency_id][i][0];
                    tariff_name = tariff_array[currency_id][i][1];

                    tariff_str = tariff_str + '<option value="' + tariff_id + '" selected="selected">' + tariff_name + '</option>';
                }
            }
        }
        $('#tariff_id').html(tariff_str);
    }


    function account_id_checkbox_changed()
    {
        if ($('#account_id_checkbox').is(":checked"))
        {
            $('#input_account_id').removeAttr('data-parsley-required');
            $('#input_account_id').removeAttr('data-parsley-minlength');
            $('#input_account_id').removeAttr('data-parsley-maxlength');
            $('#input_account_id').removeAttr('data-parsley-type');

            $('.class_input_account_id').hide();
        } else
        {
            $('.class_input_account_id').show();

            $('#input_account_id').attr('data-parsley-required', 'true');
            $('#input_account_id').attr('data-parsley-minlength', '6');
            $('#input_account_id').attr('data-parsley-maxlength', '12');
            $('#input_account_id').attr('data-parsley-type', 'alphanum');
        }
    }

    function country_changed()
    {
        country_id = $("#country_id").val();
        if (country_id == '100')
        {
            $('#id_state_div').show();
            $('#state_code_id').attr('data-parsley-required', 'true');

        } else
        {
            $('#id_state_div').hide();
            $('#state_code_id').attr('data-parsley-required', 'false');
        }
        state_chnaged();
    }
    function billing_country_changed()
    {
        country_id = $("#billing_country_id").val();
        if (country_id == '100')
        {
            $('#id_billing_state_div').show();
            $('#billing_state_code_id').attr('data-parsley-required', 'true');
        } else
        {
            $('#id_billing_state_div').hide();
            $('#billing_state_code_id').attr('data-parsley-required', 'false');
        }
    }

    function multicallonsameno_chnaged()
    {
        if ($('#multicallonsameno_allow').is(":checked"))
        {
            $('#multicallonsameno_limit').attr('data-parsley-required', 'true');
            $('#multicallonsameno_limit').attr('data-parsley-type', 'digits');
            $("#multicallonsameno_limit").attr('readonly', false);
            if ($("#multicallonsameno_limit").val() == '')
                $("#multicallonsameno_limit").val(5);
        } else {
            $('#multicallonsameno_limit').attr('data-parsley-required', 'false');
            $('#multicallonsameno_limit').removeAttr('data-parsley-type');
            $("#multicallonsameno_limit").attr('readonly', true);
            $("#multicallonsameno_limit").val('');
        }
    }


    function tax_chnaged()
    {
        vat_flag = $("#vat_flag").val();
        if (vat_flag == 'NONE') {
            $('#taxchange').hide();
            $('#tax1').attr('data-parsley-required', 'false');
            $('#tax2').attr('data-parsley-required', 'false');
            $('#tax3').attr('data-parsley-required', 'false');
            $('#tax_type').attr('data-parsley-required', 'false');
            $("#tax_type").val("exclusive");
            $("#tax1").val("0.0");
            $("#tax2").val("0.0");
            $("#tax3").val("0.0");
        } else {
            $('#taxchange').show();
            /*set to initial status*/
            $('#div_id_vat_flag').show();
            $('#div_id_tax_type').show();
            $('.tax_class').show();
            $('#vat_flag').attr('data-parsley-required', 'true');
            $('#tax_type').attr('data-parsley-required', 'true');
              $('#tax_type').val('exclusive');
            $('#tax1').attr('data-parsley-required', 'true');
            $('#tax2').attr('data-parsley-required', 'true');
            $('#tax3').attr('data-parsley-required', 'true');
        }
    }


    function state_chnaged()
    {
        $("#tax1, #tax2, #tax3").attr('readonly', false);

        country_id = $("#country_id").val();
        if (country_id == '100')
        {
            if (tax_fixed_rules_apply == 'Y')
            {
                state_id = $("#state_code_id").val();
                if (state_id == 19)
                {
                    $('#tax1').val(0);
                    $('#tax2').val(0);
                    $('#tax3').val(18);
                    $("#tax1, #tax2, #tax3").attr('readonly', true);
                } else if (state_id != '')
                {
                    $('#tax1').val(9);
                    $('#tax2').val(9);
                    $('#tax3').val(0);
                    $("#tax1, #tax2, #tax3").attr('readonly', true);
                }
            }
        }

    }
    $("#state_code_id").change(function () {
        state_chnaged();
    });

    $('input[type=radio][name=account_media_rtpproxy]').change(function () {
        media_changed();
    });

    $("#currency_id").change(function () {
        currency_changed();
    });

    $("#account_id_checkbox").change(function () {
        account_id_checkbox_changed();
    });
    $("#country_id").change(function () {
        country_changed();
    });
    $("#billing_country_id").change(function () {
        billing_country_changed();
    });
    $('#multicallonsameno_allow').click(function () {
        multicallonsameno_chnaged();
    });

    $('#vat_flag').change(function () {
        tax_chnaged();
    });

    $("#same_as_registered_address").change(function () {

        if ($('#same_as_registered_address').is(":checked"))
        {
            var name = $('#name').val();
            var company_name = $('#company_name').val();
            var emailaddress = $('#emailaddress').val();
            var phone = $('#phone').val();
            var address = $('#address').val();
            var pincode = $('#pincode').val();
            var country_id = $('#country_id').val();
            var state_code_id = $('#state_code_id').val();

            $('#billing_name').val(name);
            $('#billing_company_name').val(company_name);
            $('#billing_emailaddress').val(emailaddress);
            $('#billing_phone').val(phone);
            $('#billing_address').val(address);
            $('#billing_pincode').val(pincode);

            $('#billing_country_id').val(country_id);
            $('#billing_state_code_id').val(state_code_id);
        } else
        {
            $('#billing_name').val('');
            $('#billing_company_name').val('');
            $('#billing_emailaddress').val('');
            $('#billing_phone').val('');
            $('#billing_address').val('');
            $('#billing_pincode').val('');

            $('#billing_country_id').val('');
            $('#billing_state_code_id').val('');

        }

        billing_country_changed();
    });







    $(document).ready(function () {
        currency_changed();
        account_id_checkbox_changed();
        media_changed();
        country_changed();
        billing_country_changed();
        multicallonsameno_chnaged();
        tax_chnaged();
    });
</script> 
<div class="clearfix"></div>