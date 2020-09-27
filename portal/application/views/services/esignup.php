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
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$tab_index = 1;
//echo '<pre>';
//print_r($provider_data);
//print_r($currency_options);
//print_r($netoff_accounts);
//echo '</pre>';

$netoff_counter = 1;
?>

<div class="">
    <div class="clearfix"></div>


    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Signup Management</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a href="<?php echo base_url('sysconfig/signupConfig') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Signup Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>


    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_title">
                    <h2>Signup (EDIT)</h2>
                    <ul class="nav navbar-right panel_toolbox">

                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form action="<?php echo base_url(); ?>sysconfig/eSignupConfig/<?php echo param_encrypt($listing_data['signupkey']); ?>" method="post" name="signup_form" id="provider_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="action" value="OkSaveData">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="signupkey" value="<?php echo $listing_data['signupkey']; ?>"/>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Singup Plan name <span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <input type="text" name="signup_plan" id="provider_name" value="<?php echo $listing_data['signup_plan']; ?>" class="form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Tariff Plan <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="tariff_id" id="tariff_id" class="combobox form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select</option>
                                    <?php
                                    $str = '';
                                    $is_assigned_tariff_found = false;
                                    foreach ($tariff_options as $key => $tariff_name_array) {
                                      //  print_r($tariff_name_array);
                                        $selected = ' ';
                                        if ($listing_data['tariff_id'] == $tariff_name_array['tariff_id']) {
                                            $selected = '  selected="selected" ';
                                            $is_assigned_tariff_found = true;
                                        }
                                        $str .= '<option value="' . $tariff_name_array['tariff_id'] . '" ' . $selected . '>' . $tariff_name_array['tariff_name'] . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Dial Plan <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="dialplan_id" id="tariff_id" class="combobox form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select</option>
                                    <?php
                                    $str = '';
                                    $is_assigned_dialplan_found = false;
                                    foreach ($dialplan_options as $key => $dialplan_name_array) {
                                        //print_r($dialplan_name_array);
                                        $selected = ' ';
                                        if ($listing_data['dialplan_id'] == $dialplan_name_array['dialplan_id']) {
                                            $selected = '  selected="selected" ';
                                            $is_assigned_dialplan_found = true;
                                        }
                                        $str .= '<option value="' . $dialplan_name_array['dialplan_id'] . '" ' . $selected . '>' . $dialplan_name_array['dialplan_name'] . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        
                        <div class="form-group">
                        	<label  class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <label><input type="radio" name="status_id" id="status_id1" value="1" <?php if ($listing_data['status_id'] == '1') { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>"/> Active &nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    <label> <input type="radio" name="status_id" id="status_id2" value="0" <?php if ($listing_data['status_id'] != '1') { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>" /> Inactive</label>
                                </div>
    
                            </div>
                        </div>



                        <div class="ln_solid"></div>


                        <div class="form-group">
                            <div class="col-md-9 col-sm-6 col-xs-12 col-md-offset-3 text-right">
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
            <h2>Signup Management</h2>
            <ul class="nav navbar-right panel_toolbox">
                 <li><a href="<?php echo base_url('sysconfig/signupConfig') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Signup Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

</div>
<?php // echo '<pre>'; print_r($listing_data);echo '</pre>';?>
<script>
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