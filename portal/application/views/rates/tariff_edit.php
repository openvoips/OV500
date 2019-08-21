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

<!-- Parsley -->
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<div class="">
    <div class="clearfix"></div>  
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Tariff Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('tariffs') ?>"><button class="btn btn-danger" type="button" >Back to Tariff Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Tariff (EDIT)</h2>

                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <br />
                            <form action="<?php echo base_url(); ?>tariffs/editTP/<?php echo param_encrypt($data['tariff_id']); ?>" method="post" name="edit_form" id="edit_form" data-parsley-validate class="form-horizontal form-label-left">
                                <input type="hidden" name="button_action" id="button_action" value="">
                                <input type="hidden" name="action" value="OkSaveData">    
                                <input type="hidden" name="frm_key" value="<?php echo $data['tariff_id']; ?>"/>
                                <input type="hidden" name="frm_id" value="<?php echo $data['id']; ?>"/>               
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Tariff Name <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_name" id="frm_name" value="<?php echo $data['tariff_name']; ?>"  data-parsley-required="" data-parsley-length="[5, 30]" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Tariff Code <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_abbr" id="frm_abbr" value="<?php echo $data['tariff_id']; ?>" class="form-control col-md-7 col-xs-12" disabled="disabled">
                                    </div>
                                </div>

                                <?php if (!check_logged_account_type(array('RESELLER'))) : ?>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Who can Use <span class="required">*</span></label>
                                        <div class="col-md-8 col-sm-6 col-xs-12">
                                            <select name="frm_type" id="frm_type" class="form-control data-search-field" disabled="disabled">
                                                <option value="CARRIER"  <?php if ($data['tariff_type'] == 'CARRIER') echo 'selected'; ?>>Carrier</option>
                                                <option value="CUSTOMER"  <?php if ($data['tariff_type'] == 'CUSTOMER') echo 'selected'; ?>>Customer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Currency <span class="required">*</span></label>
                                        <div class="col-md-8 col-sm-6 col-xs-12">
                                            <select name="frm_currency" id="frm_currency" class="form-control data-search-field" disabled="disabled">
                                                <option value="">Select Route</option>
                                                <?php for ($i = 0; $i < count($currency_data); $i++) { ?>								
                                                    <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if ($data['tariff_currency_id'] == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>    
                                <?php else: ?>
                                    <input type="hidden" name="frm_type" id="frm_type" value="<?php echo $data['tariff_type']; ?>" data-parsley-required="" class="form-control" />
                                    <input type="hidden" name="frm_currency" id="frm_currency" value="<?php echo $data['tariff_currency_id']; ?>" data-parsley-required="" class="form-control" />
                                <?php endif; ?>

                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="last-name">Description</label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <textarea name="frm_desc" id="frm_desc" class="form-control col-md-7 col-xs-12"><?php echo $data['tariff_description']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Tariff Status</label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <div class="radio">
                                            <label><input type="radio" name="frm_status" id="status1" value="1"  <?php if ($data['tariff_status'] == 1) { ?> checked="checked" <?php } ?> /> Active</label>
                                            <label> <input type="radio" name="frm_status" id="status0" value="0" <?php if ($data['tariff_status'] == 0) { ?> checked="checked" <?php } ?> /> Inactive</label>
                                        </div>                    
                                    </div>
                                </div>
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                        <!--<a href="<?php echo base_url('tariffs') ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
                                        <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                        <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Listing Page</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>OutGoing(PSTN) Calls Rate's Ratecard List</h2>                            
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <br /> 
                            <div class="table-responsive">
                                <table class="table  jambo_table bulk_action table-bordered">
                                    <thead>
                                        <tr class="headings thc">
                                            <th class="column-title">Ratecard</th>
                                            <th class="column-title" width="80">Start</th>
                                            <th class="column-title" width="80">End</th>
                                            <th class="column-title" width="50">Priority</th>
                                            <th class="column-title no-link last" width="50"><span class="nobr">Actions</span> </th>
                                        </tr>
                                    </thead>		
                                    <tbody>
                                        <?php
                                        $w = unserialize(DAY_FROM_WEEK);
                                        for ($i = 0; $i < count($data_ratecard); $i++) {
                                            $text_class = "";
                                            ?>
                                            <tr class=" <?php echo $text_class; ?>">
                                                <td >
                                                    <?php echo $data_ratecard[$i]['ratecard_name'] . '<br />[' . $data_ratecard[$i]['ratecard_id'] . ']'; ?>
                                                </td>
                                                <td><?php echo $w[$data_ratecard[$i]['start_day']]; ?> <br /><?php echo $data_ratecard[$i]['start_time']; ?></td>
                                                <td><?php echo $w[$data_ratecard[$i]['end_day']]; ?> <br /><?php echo $data_ratecard[$i]['end_time']; ?></td>
                                                <td><?php echo $data_ratecard[$i]['priority']; ?></td>                                   
                                                <td class="last">
                                                    <a href="<?php echo base_url(); ?>tariffs/editTMP/<?php echo param_encrypt($data_ratecard[$i]['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                                    <a href="javascript:doConfirmDelete('<?php echo param_encrypt($data_ratecard[$i]['id']); ?>','tariffs','mapping');" title="Delete" class="delete"><i class="fa fa-trash"></i></a>						 
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                                <a href="<?php echo base_url(); ?>tariffs/addTMP/<?php echo param_encrypt($data['tariff_id'] . '@OUTGOING'); ?>"><input value="Add Ratecard" name="add_link" class="btn btn-primary" type="button"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Incoming(DID's) Calls Rate's Ratecard List</h2>                           
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <br /> 
                            <div class="table-responsive">
                                <table class="table jambo_table bulk_action table-bordered">
                                    <thead>
                                        <tr class="headings thc">
                                            <th class="column-title">Ratecard</th>
                                            <th class="column-title" width="80">Start</th>
                                            <th class="column-title" width="80">End</th>
                                            <th class="column-title" width="50">Priority</th>
                                            <th class="column-title no-link last" width="50"><span class="nobr">Actions</span> </th>
                                        </tr>
                                    </thead>		
                                    <tbody>
                                        <?php
                                        $w = unserialize(DAY_FROM_WEEK);
                                        for ($i = 0; $i < count($data_ratecard_in); $i++) {
                                            $text_class = "";
                                            ?>
                                            <tr class=" <?php echo $text_class; ?>">
                                                <td >
                                                    <?php echo $data_ratecard_in[$i]['ratecard_name'] . '<br />[' . $data_ratecard_in[$i]['ratecard_id'] . ']'; ?>
                                                </td>
                                                <td><?php echo $w[$data_ratecard_in[$i]['start_day']]; ?> <br /><?php echo $data_ratecard_in[$i]['start_time']; ?></td>
                                                <td><?php echo $w[$data_ratecard_in[$i]['end_day']]; ?> <br /><?php echo $data_ratecard_in[$i]['end_time']; ?></td>
                                                <td><?php echo $data_ratecard_in[$i]['priority']; ?></td>                                   
                                                <td class="last">
                                                    <a href="<?php echo base_url(); ?>tariffs/editTMP/<?php echo param_encrypt($data_ratecard_in[$i]['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                                    <a href="javascript:doConfirmDelete('<?php echo param_encrypt($data_ratecard_in[$i]['id']); ?>','tariffs','mapping');" title="Delete" class="delete"><i class="fa fa-trash"></i></a>						 
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                                <a href="<?php echo base_url(); ?>tariffs/addTMP/<?php echo param_encrypt($data['tariff_id'] . '@INCOMING'); ?>"><input value="Add Ratecard" name="add_link" class="btn btn-primary" type="button"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-sm-12 col-xs-12">

            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Package Services</h2>                           
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <form action="<?php echo base_url(); ?>tariffs/editTP/<?php echo param_encrypt($data['tariff_id']); ?>" method="post" name="edit_form_ratecard_p_s" id="edit_form_ratecard_p_s" data-parsley-validate class="form-horizontal form-label-left">
                                <input type="hidden" name="button_action_p_s" id="button_action_p_s" value="">
                                <input type="hidden" name="action" value="OkUpdateData">    
                                <input type="hidden" name="frm_key" value="<?php echo $data['tariff_id']; ?>"/>
                                <input type="hidden" name="frm_id" value="<?php echo $data['id']; ?>"/>    


                                <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-6 col-sm-3 col-xs-12">Plan Services</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div class="radio">
                                            <label><input type="radio" name="frm_plan" id="frm_plan1" value="1" <?php if ($data['package_option'] == '1') { ?> checked="checked" <?php } ?> /> Yes</label>
                                            <label><input type="radio" name="frm_plan" id="frm_plan2" value="0" <?php if ($data['package_option'] == '0') { ?> checked="checked" <?php } ?> /> No</label>
                                        </div>                    
                                    </div>
                                </div> 

                                <div class="form-group plan">
                                    <label class="control-label col-md-6 col-sm-3 col-xs-12" for="first-name">Monthly Tariff Charge <span class="required">*</span></label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_monthly_charge" id="frm_monthly_charge" value="<?php echo $data['monthly_charges']; ?>"  data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" placeholder="0">
                                    </div>
                                </div>

                                <div class="form-group plan">
                                    <label for="middle-name" class="control-label col-md-6 col-sm-3 col-xs-12">Has Bundle?</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div class="radio">
                                            <label><input type="radio" name="frm_bundle" id="frm_bundle1" value="1" <?php if ($data['bundle_option'] == '1') { ?> checked="checked" <?php } ?> /> Yes</label>
                                            <label><input type="radio" name="frm_bundle" id="frm_bundle2" value="0" <?php if ($data['bundle_option'] == '0' || $data['bundle_option'] == '') { ?> checked="checked" <?php } ?> /> No</label>
                                        </div>                    
                                    </div>
                                </div> 
                                <div class="form-group well plan bundle" style="padding: 5px;">	
                                    <h4 style="padding-left: 7px;">Bundle 1</h4>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <label for="heard">Type</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <div class="radio">
                                            <label><input type="radio" name="bundle1_type" value="MINUTE"  <?php if ($data['bundle1_type'] == 'MINUTE') { ?> checked="checked" <?php } ?> /> Fixed Minute</label>
                                            <label> <input type="radio" name="bundle1_type" value="COST" <?php if ($data['bundle1_type'] == 'COST') { ?> checked="checked" <?php } ?> /> Fixed Cost</label>
                                        </div> 
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <label for="heard">Value</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <input type="text" name="bundle1_value" id="bundle1_value" value="<?php echo $data['bundle1_value']; ?>" class="form-control col-md-7 col-xs-12"  data-parsley-type="number">
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <label for="heard">Prefix</label><br />
                                        <small>comma separated value, % can be used</small>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <textarea name="bundle1_prefix" id="bundle1_prefix" class="form-control col-md-7 col-xs-12"><?php echo $bundle[0]['prefixes']; ?></textarea>
                                    </div>

                                </div>
                                <div class="form-group well plan bundle" style="padding: 5px;">	
                                    <h4 style="padding-left: 7px;">Bundle 2</h4>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <label for="heard">Type</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <div class="radio">
                                            <label><input type="radio" name="bundle2_type" value="MINUTE" <?php if ($data['bundle2_type'] == 'MINUTE') { ?> checked="checked" <?php } ?> /> Fixed Minute</label>
                                            <label> <input type="radio" name="bundle2_type" value="COST" <?php if ($data['bundle2_type'] == 'COST') { ?> checked="checked" <?php } ?> /> Fixed Cost</label>
                                        </div> 
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <label for="heard">Value</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <input type="text" name="bundle2_value" id="bundle2_value" value="<?php echo $data['bundle2_value']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-type="number">
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <label for="heard">Prefix</label><br />
                                        <small>comma separted value, % can be used</small>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <textarea name="bundle2_prefix" id="bundle2_prefix" class="form-control col-md-7 col-xs-12"><?php if (isset($bundle[1]['prefixes'])) echo $bundle[1]['prefixes']; ?></textarea>
                                    </div>

                                </div>
                                <div class="form-group well plan bundle" style="padding: 5px;">	
                                    <h4 style="padding-left: 7px;">Bundle 3</h4>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <label for="heard">Type</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <div class="radio">
                                            <label><input type="radio" name="bundle3_type" value="MINUTE"  <?php if ($data['bundle3_type'] == 'MINUTE') { ?> checked="checked" <?php } ?> /> Fixed Minute</label>
                                            <label> <input type="radio" name="bundle3_type" value="COST" <?php if ($data['bundle3_type'] == 'COST') { ?> checked="checked" <?php } ?> /> Fixed Cost</label>
                                        </div> 
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <label for="heard">Value</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <input type="text" name="bundle3_value" id="bundle3_value" value="<?php echo $data['bundle3_value']; ?>" class="form-control col-md-7 col-xs-12"  data-parsley-type="number">
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <label for="heard">Prefix</label><br />
                                        <small>comma separted value, % can be used</small>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12" style="padding-bottom: 12px;">
                                        <textarea name="bundle3_prefix" id="bundle3_prefix" class="form-control col-md-7 col-xs-12"><?php if (isset($bundle[2]['prefixes'])) echo $bundle[2]['prefixes']; ?></textarea>
                                    </div>

                                </div>   


                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                <!--<a href="<?php echo base_url('tariffs') ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
                                <?php //if(count($data_carrier) == 0 && count($data_user) == 0 && count($data_reseller) == 0){?>
                                <button type="button" id="btnSaveCard" class="btn btn-success">Save</button>
                                <button type="button" id="btnCloseCard" class="btn btn-info">Save & Go Back to Listing Page </button>
                                <?php //}?>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="col-md-12 col-sm-12 col-xs-12 right">
    <div class="ln_solid"></div>
    <div class="x_title">
        <h2>Tariff Configuration Management</h2>
        <ul class="nav navbar-right panel_toolbox">     
            <li><a href="<?php echo base_url('tariffs') ?>"><button class="btn btn-danger" type="button" >Back to Tariff Listing Page</button></a> </li>
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

    $('#btnSave, #btnSaveClose').click(function () {
        var is_ok = $("#edit_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#edit_form").submit();
        } else
        {
            $('#edit_form').parsley().validate();
        }
    });



    $('#btnSaveCard_I_S, #btnCloseCard_I_S').click(function () {
        var is_ok = $("#edit_form_ratecard_i_s").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnCloseCard_I_S')
                $('#button_action_i_s').val('save_close');
            else
                $('#button_action_i_s').val('save');

            $("#edit_form_ratecard_i_s").submit();
        } else
        {
            $('#edit_form_ratecard_i_s').parsley().validate();
        }
    });






    $('#btnSaveCard, #btnCloseCard').click(function () {
        var is_ok = $("#edit_form_ratecard_p_s").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnCloseCard')
                $('#button_action_p_s').val('save_close');
            else
                $('#button_action_p_s').val('save');

            $("#edit_form_ratecard_p_s").submit();
        } else
        {
            $('#edit_form_ratecard_p_s').parsley().validate();
        }
    });

    $(".plan").hide();

    $('#frm_plan1, #frm_plan2').change(function () {
        if ($("#frm_plan1").is(":checked")) {
            $(".plan").show();
            $(".bundle").hide();
            $(".config").hide();
        } else
            $(".plan").hide();
    })
    $('#frm_bundle1, #frm_bundle2').change(function () {
        if ($("#frm_bundle1").is(":checked"))
            $(".bundle").show();
        else
            $(".bundle").hide();
    })
    $('#frm_config1, #frm_config2').change(function () {
        if ($("#frm_config1").is(":checked"))
            $(".config").show();
        else
            $(".config").hide();
    })

    fromload();

    function fromload() {
        if ($('#frm_plan1').is(":checked")) {
            $(".plan").show();
            $(".bundle").hide();
            $(".config").hide();
            if ($("#frm_bundle1").is(":checked"))
                $(".bundle").show();
            //if($("#frm_config1").is(":checked")) $(".config").show();
        }
    }

</script>
