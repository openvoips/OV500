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
//echo '<pre>';
//print_r($ratecard_data);
//print_r($data);
//echo '</pre>';
$ratecard_for = '';
?>
<!-- Parsley -->
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

<div class="">
    <div class="clearfix"></div>  
    
     <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Rate Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php  echo base_url('rates'); ?>"><button class="btn btn-danger" type="button">Back to Rate Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    
    
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Rate(EDIT)</h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />         
                    <form action="<?php echo base_url(); ?>rates/editR/<?php echo param_encrypt($data['rate_id'] . '@' . $data['ratecard_id']); ?>" method="post" name="edit_form" id="edit_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">    
                        <input type="hidden" name="frm_key" value="<?php echo $data['ratecard_id']; ?>"/>
                        <input type="hidden" name="frm_id" value="<?php echo $data['rate_id']; ?>"/> 
                        <input type="hidden" name="frm_rate_table_name" value="<?php echo $data['rate_table_name']; ?>"/> 
                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Ratecard Name <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="frm_card" id="frm_card" class="form-control data-search-field" disabled="disabled" data-parsley-required="">
                                            <option value="">Select Route</option>
                                            <?php for ($i = 0; $i < $ratecard_data['total']; $i++) { ?>								
                                                <option value="<?php echo $ratecard_data['result'][$i]['ratecard_id']; ?>" <?php if ($data['ratecard_id'] == $ratecard_data['result'][$i]['ratecard_id']) echo 'selected'; ?>><?php echo  $ratecard_data['result'][$i]['currency_name']." :: ".$ratecard_data['result'][$i]['ratecard_name'] . ' (' . $ratecard_data['result'][$i]['ratecard_id']  . ')'; ?></option>
                                                <?php
                                                if ($data['ratecard_id'] == $ratecard_data['result'][$i]['ratecard_id']) {
                                                    $ratecard_for = strtolower($ratecard_data['result'][$i]['ratecard_for']);
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>                  
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Prefix <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_prefix" id="frm_prefix" value="<?php echo $data['prefix']; ?>"  data-parsley-required="" data-parsley-type="digits" data-parsley-length="[1, 15]" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Destination <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_dest" id="frm_dest" value="<?php echo $data['destination']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-alphanumspace="">
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Rate per Minute <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_ppm" id="frm_ppm" value="<?php echo $data['rate']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-price="">
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Charge / Connection <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_ppc" id="frm_ppc" value="<?php echo $data['connection_charge']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-price="">
                                    </div>
                                </div>                            
                                <?php
                                $is_incoming = strpos($ratecard_for, 'incoming');
                                if ($is_incoming !== false) {
                                    ?>  
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID Inclusive Channel <span class="required">*</span></label>
                                        <div class="col-md-8 col-sm-6 col-xs-12">
                                            <input type="text" name="frm_inclusive_channel" id="frm_inclusive_channel" value="<?php echo $data['inclusive_channel']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-type="digits">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID Exclusive Per Channel Rental <span class="required">*</span></label>
                                        <div class="col-md-8 col-sm-6 col-xs-12">
                                            <input type="text" name="frm_exclusive_per_channel_rental" id="frm_exclusive_per_channel_rental" value="<?php echo $data['exclusive_per_channel_rental']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-price="">
                                        </div>
                                    </div>   
                                <?php } ?>

                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">First Pulse <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_min" id="frm_min" value="<?php echo $data['minimal_time']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1">
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">After First Pulse billing slab <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_res" id="frm_res" value="<?php echo $data['resolution_time']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1">
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Grace Period <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_grace" id="frm_grace" value="<?php echo $data['grace_period']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-type="digits" data-parsley-min="0">
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Rate Multiplier <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_mul" id="frm_mul" value="<?php echo $data['rate_multiplier']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-pattern="/^\d+(\.\d{1,2})?$/" data-parsley-min="0">
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Fix Charge per call <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_add" id="frm_add" value="<?php echo $data['rate_addition']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-pattern="/^\d+(\.\d{1,2})?$/" data-parsley-min="0">
                                    </div>
                                </div> 	 





                                <?php
                                if ($is_incoming !== false) {
                                    ?>

                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID Rental <span class="required">*</span></label>
                                        <div class="col-md-8 col-sm-6 col-xs-12">
                                            <input type="text" name="frm_rental" id="frm_rental" value="<?php echo $data['rental']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-price="">
                                        </div>
                                    </div> 	 
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID Setup Charge <span class="required">*</span></label>
                                        <div class="col-md-8 col-sm-6 col-xs-12">
                                            <input type="text" name="frm_setup_charge" id="frm_setup_charge" value="<?php echo $data['setup_charge']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-price="">
                                        </div>
                                    </div> 	 
                                    <?php
                                }
                                ?>

                                <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <div class="radio">
                                            <label><input type="radio" name="frm_status" id="status1" value="1"  <?php if ($data['rates_status'] == 1) { ?> checked="checked" <?php } ?> /> Active</label>
                                            <label> <input type="radio" name="frm_status" id="status0" value="0" <?php if ($data['rates_status'] == 0) { ?> checked="checked" <?php } ?> /> Inactive</label>
                                        </div>                    
                                    </div>
                                </div>					
                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <!--<a href="<?php echo base_url('rates') ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Rate listing Page</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
     <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Rate Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php  echo base_url('rates'); ?>"><button class="btn btn-danger" type="button">Back to Rate Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    
</div>    
<script>
    window.Parsley
            .addValidator('alphanumspace', {
                validateString: function (value) {
                    return true == (/^[a-zA-Z\d ]+$/.test(value));
                },
                messages: {
                    en: 'This value should be in alphanumeric and Space'
                }
            })
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
    })
</script>
