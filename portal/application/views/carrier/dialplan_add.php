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
<script src="<?php echo base_url()?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url()?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script>
$(document).ready(function(){
  $('.combobox').combobox()
});
</script>

<!-- Parsley -->
<script src="<?php echo base_url()?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<!-- bootstrap-daterangepicker -->
<script src="<?php echo base_url()?>theme/vendors/moment/min/moment.min.js"></script>
<!-- bootstrap-datetimepicker -->    
<script src="<?php echo base_url()?>theme/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
<div class="">
    <div class="clearfix"></div>   
    
   <div class="col-md-12 col-sm-12 col-xs-12 right">
     
        <div class="x_title">
            <h2>Dialplan Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('dialplans/index/') ?>"><button class="btn btn-danger" type="button" >Dialplan Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Dialplan (ADD)</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />         
                    <form action="<?php echo base_url(); ?>dialplans/addD" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">  
                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-xs-12">       
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Prefix <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_prefix" id="frm_prefix" value="<?php echo set_value('frm_prefix'); ?>" data-parsley-required="" data-parsley-length="[1, 10]" data-parsley-type="digits" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Route <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">							
                                        <select name="frm_route" id="frm_route" class="combobox form-control col-md-7 col-xs-12">
                                            <?php for ($i = 0; $i < count($route_data); $i++) { ?>								
                                                <option value="<?php echo $route_data[$i]['dialplan_id']; ?>" <?php if (set_value('frm_route') == $route_data[$i]['dialplan_id']) echo 'selected'; ?>><?php echo $route_data[$i]['dialplan_name'] . ' (' . $route_data[$i]['dialplan_id'] . ')'; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>              
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="frm_carrier" id="frm_carrier" class="combobox form-control col-md-7 col-xs-12">
                                            <?php foreach ($carrier_data as $k => $v) { ?>							
                                                <option value="<?php echo $carrier_data[$k]['carrier_id']; ?>" <?php if (set_value('frm_carrier') == $carrier_data[$k]['carrier_id']) echo 'selected'; ?>><?php echo $carrier_data[$k]['carrier_name'] . ' [' . $carrier_data[$k]['carrier_id'] . ']'; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>              
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Priority</label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="frm_priority" id="frm_priority" class="form-control col-md-7 col-xs-12">
                                            <option value="1" <?php if (set_value('frm_priority') == 1) echo 'selected'; ?>>1</option>
                                            <option value="2" <?php if (set_value('frm_priority') == 2) echo 'selected'; ?>>2</option>
                                            <option value="3" <?php if (set_value('frm_priority') == 3) echo 'selected'; ?>>3</option>
                                            <option value="4" <?php if (set_value('frm_priority') == 4) echo 'selected'; ?>>4</option>
                        
                                        </select>
                                    </div>
                                </div> 							
                                <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <div class="radio">
                                            <label><input type="radio" name="frm_status" id="status1" value="1"  <?php echo set_radio('frm_status', '1', TRUE); ?> /> Active</label>
                                            <label> <input type="radio" name="frm_status" id="status0" value="0" <?php echo set_radio('frm_status', '0'); ?> /> Inactive</label>
                                        </div>                   
                                    </div>
                                </div>							
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">  							
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Start Day</label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="frm_start_day" id="frm_start_day"  class="form-control col-md-7 col-xs-12">
                                            <option value="0" <?php if (set_value('frm_start_day') == 0) echo 'selected'; ?>>Sunday</option>
                                            <option value="1" <?php if (set_value('frm_start_day') == 1) echo 'selected'; ?>>Monday</option>
                                            <option value="2" <?php if (set_value('frm_start_day') == 2) echo 'selected'; ?>>Tuesday</option>
                                            <option value="3" <?php if (set_value('frm_start_day') == 3) echo 'selected'; ?>>Wednesday</option>
                                            <option value="4" <?php if (set_value('frm_start_day') == 4) echo 'selected'; ?>>Thursday</option>
                                            <option value="5" <?php if (set_value('frm_start_day') == 5) echo 'selected'; ?>>Friday</option>
                                            <option value="6" <?php if (set_value('frm_start_day') == 6) echo 'selected'; ?>>Saturday</option>
                                        </select>
                                    </div>
                                </div>    
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Start Time <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <div class="input-group date" id="frm_datepicker_start" style="margin-bottom: 0px;">
                                            <input class="form-control" type="text" name="frm_start_time" id="frm_start_time" value="<?php echo set_value('frm_start_time', '00:00:00'); ?>" placeholder="00:00:00">
                                            <span class="input-group-addon" style="">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">End Day</label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="frm_end_day" id="frm_end_day"  class="form-control col-md-7 col-xs-12">
                                            <option value="0" <?php if (set_value('frm_end_day') == 0) echo 'selected'; ?>>Sunday</option>
                                            <option value="1" <?php if (set_value('frm_end_day') == 1) echo 'selected'; ?>>Monday</option>
                                            <option value="2" <?php if (set_value('frm_end_day') == 2) echo 'selected'; ?>>Tuesday</option>
                                            <option value="3" <?php if (set_value('frm_end_day') == 3) echo 'selected'; ?>>Wednesday</option>
                                            <option value="4" <?php if (set_value('frm_end_day') == 4) echo 'selected'; ?>>Thursday</option>
                                            <option value="5" <?php if (set_value('frm_end_day') == 5) echo 'selected'; ?>>Friday</option>
                                            <option value="6" <?php if (set_value('frm_end_day', 6) == 6) echo 'selected'; ?>>Saturday</option>
                                        </select>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">End Time <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <div class="input-group date" id="frm_datepicker_end" style="margin-bottom: 0px;">
                                            <input class="form-control" type="text" name="frm_end_time" id="frm_end_time" value="<?php echo set_value('frm_end_time', '23:59:59'); ?>" placeholder="23:59:59">
                                            <span class="input-group-addon" style="">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Load Share <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_load" id="frm_load" value="<?php echo set_value('frm_load', 100); ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-type="digits" data-parsley-min="0" data-parsley-max="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-12 col-sm-12 col-xs-12 col-md-offset-6">
                                <!--<a href="<?php echo base_url('dialplans/index/') ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go back to Edit Page</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
       <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Dialplan Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('dialplans/index/') ?>"><button class="btn btn-danger" type="button" >Dialplan Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
</div>    
<script>
    $('#frm_datepicker_start, #frm_datepicker_end').datetimepicker({
        format: 'HH:mm:ss'
    });
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
