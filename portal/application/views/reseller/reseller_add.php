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
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script>
    $(document).ready(function () {
        $('.combobox').combobox()
    });
</script>
<!-- Parsley -->
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
if (isset($logged_account_result['tariff']))
    $tariff_options[] = $logged_account_result['tariff'];
$tab_index = 1;
//echo '<pre>';
//print_r($logged_account_result);
//print_r($tariff_options);
//echo '</pre>';

$dp = 4;
$vatflag_array = array('NONE', 'TAX', 'VAT');
?>
<div class="">
    <div class="clearfix"></div>   
    <div class="col-md-12 col-sm-12 col-xs-12 right">      
        <div class="x_title">
            <h2>Reseller User Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('resellers') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Reseller Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
    <form action="<?php echo base_url($reseller_type . 's'); ?>/add" method="post" name="account_form" id="account_form" data-parsley-validate class="form-horizontal form-label-left">
        <input type="hidden" name="button_action" id="button_action" value="">
        <input type="hidden" name="action" value="OkSaveData"> 

        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2><?php echo ucfirst($reseller_type) . ' Management (ADD NEW)'; ?></h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
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
                    if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC'))) {
                        ?> 
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Currency <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="currency_id" id="currency_id" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select Currency</option>                    
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
                    } else {

                        echo '<input type="hidden" name="currency_id" id="currency_id" value="' . $logged_account_result['currency_id'] . '" data-parsley-required="" class="form-control"  readonly="readonly">';
                    }
                    ?>   
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tariff Name<span class="required">*</span>
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
                                <option value="">Select</option>                    
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
                                <option value="">Select</option>                    
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


                    <div class="form-group" >
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">VAT / Tax Flag <span class="required">*</span></label>
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
                                        if (set_value('tax_type', 'inclusive') == $key)
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $key . '" ' . $selected . '>' . ucfirst($tax_type) . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
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
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Billing in Decimal <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="dp" id="dp" value="<?php echo set_value('dp', $dp); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
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
                            <input type="text" name="account_cps" id="user_cps" value="<?php echo set_value('account_cps', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">Caller ID Check</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="cli_check" id="cli_check1" value="1" <?php echo set_radio('cli_check', '1', TRUE); ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>

                                <label> <input type="radio" name="cli_check" id="cli_check2" value="0" <?php echo set_radio('cli_check', '0'); ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>
                    <div class="form-group">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">Number check</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="dialpattern_check" id="dialpattern_check1" value="1" <?php echo set_radio('dialpattern_check', '1', TRUE); ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>

                                <label> <input type="radio" name="dialpattern_check" id="dialpattern_check2" value="0" <?php echo set_radio('dialpattern_check', '0'); ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>





                    <div class="form-group">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">LLR Check</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="llr_check" id="llr_check1" value="1" <?php echo set_radio('llr_check', '1', TRUE); ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>

                                <label> <input type="radio" name="llr_check" id="llr_check2" value="0" <?php echo set_radio('llr_check', '0'); ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>


                    <div class="form-group">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">With-media</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="media_rtpproxy" id="media_rtpproxy1" value="1" <?php echo set_radio('media_rtpproxy', '1'); ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>

                                <label> <input type="radio" name="media_rtpproxy" id="media_rtpproxy2" value="0" <?php echo set_radio('media_rtpproxy', '0', TRUE); ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>

                    <div class="form-group" id="id_transcoding_div">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12">Transcoding</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="media_transcoding" id="media_transcoding1" value="1" <?php echo set_radio('media_transcoding', '1'); ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>

                                <label> <input type="radio" name="media_transcoding" id="media_transcoding2" value="0" <?php echo set_radio('media_transcoding', '0', TRUE); ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>



                    <div class="form-group">
                        <label  class="control-label col-md-4 col-sm-3 col-xs-12"><?php echo ucfirst($reseller_type); ?> Status</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="account_status" id="status1" value="1" <?php echo set_radio('account_status', '1', TRUE); ?>  tabindex="<?php echo $tab_index++; ?>"/> Active</label>
                                <label> <input type="radio" name="account_status" id="status0" value="0" <?php echo set_radio('account_status', '0'); ?> tabindex="<?php echo $tab_index++; ?>"/> Inactive</label>
                            </div>

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
                    <!-- change 101-->
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >PIN</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="pincode" id="pincode" value="<?php echo set_value('pincode'); ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12 center">
            <div class="ln_solid"></div>
            <div class="form-group">
                <div class="col-md-12 col-sm-6 col-xs-12 col-md-offset-4">
                    <!--<a href="<?php echo base_url($reseller_type . 's') ?>"><button class="btn btn-primary" type="button"  tabindex="<?php echo $tab_index++; ?>">Cancel</button></a>-->				
                    <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save</button>
                    <button type="button" id="btnSaveClose" class="btn btn-info" tabindex="<?php echo $tab_index++; ?>">Save & Go Back to Listing page</button>
                </div>
            </div>
        </div>
    </form>


    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Reseller User Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('resellers') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Reseller Listing Page</button></a> </li>
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
foreach ($tariff_options as $tariff_name_array) {
    ?>
    <script>
        tariff_array[<?php echo $tariff_name_array['tariff_currency_id']; ?>]["<?php echo $k; ?>"] = ["<?php echo $tariff_name_array['tariff_id']; ?>", "<?php echo $tariff_name_array['tariff_name']; ?>"];
    </script>
    <?php
    $k++;
}
?>

<script>

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
//        alert(currency_id);
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

    function tax_chnaged() {
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
            $('.tax_class').show();
            $('#vat_flag').attr('data-parsley-required', 'true');
            $('#tax_type').attr('data-parsley-required', 'true');
            $('#tax1').attr('data-parsley-required', 'true');
            $('#tax2').attr('data-parsley-required', 'true');
            $('#tax3').attr('data-parsley-required', 'true');
        }
    }

    $('input[type=radio][name=media_rtpproxy]').change(function () {
        media_changed();
    });

    $("#currency_id").change(function () {
        currency_changed();
    });

    $("#country_id").change(function () {
        country_changed();
    });

    $("#billing_country_id").change(function () {
        billing_country_changed();
    });

    $('#vat_flag').change(function () {
        tax_chnaged();
    });
    $(document).ready(function () {
        currency_changed();
        media_changed();
        country_changed();
        tax_chnaged();
    });

</script>