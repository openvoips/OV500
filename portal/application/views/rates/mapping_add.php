<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/moment/min/moment.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>

<div class="">
    <div class="clearfix"></div> 
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Rate Card Mapping With Tariff plan</h2>
            <ul class="nav navbar-right panel_toolbox"> 
                <li><a href="<?php echo base_url('tariffs/editTP') . "/" . param_encrypt($data['tariff_id']); ?>"><button class="btn btn-danger" type="button" >Back to Tariff Management Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Add Ratecard Mapping</h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />         
                    <form action="<?php echo base_url(); ?>tariffs/addTMP/<?php echo param_encrypt($data['tariff_id']); ?>" method="post" name="edit_form" id="edit_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">    
                        <input type="hidden" name="frm_key" value="<?php echo $data['tariff_id']; ?>"/>
                        <input type="hidden" name="frm_id" value="<?php echo $tariff_id; ?>"/> 


                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-xs-12">              
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Tariff <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_tariff" id="frm_tariff" value="<?php echo $data['tariff_name'] . ' (' . $data['currency_name'] . ') [' . $data['tariff_id'] . ']'; ?>" class="form-control col-md-7 col-xs-12" disabled="disabled">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Ratecard <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="frm_card" id="frm_card" class="form-control data-search-field" data-parsley-required="">
                                            <option value="">Select Ratecard</option>
                                            <?php for ($i = 0; $i < $ratecard_data['total']; $i++) { ?>								
                                                <option value="<?php echo $ratecard_data['result'][$i]['ratecard_id']; ?>" <?php if (set_value('frm_card') == $ratecard_data['result'][$i]['ratecard_id']) echo 'selected'; ?>><?php
                                                    echo $ratecard_data['result'][$i]['ratecard_name'] . ' (' . $ratecard_data['result'][$i]['currency_name'] . ') [' . $ratecard_data['result'][$i]['ratecard_id'] . ']';
                                                    $ratecard_for = $ratecard_data['result'][$i]['ratecard_for'];
                                                    ?></option>
                                            <?php } ?>
                                        </select>

                                        <input type="hidden" name="ratecard_for" value="<?php echo $ratecard_for; ?>"/> 

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
                                            <option value="5" <?php if (set_value('frm_priority') == 5) echo 'selected'; ?>>5</option>
                                        </select>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <div class="radio">
                                            <label><input type="radio" name="frm_status" id="status1" value="1" <?php echo set_radio('frm_status', '1', TRUE); ?>/> Active</label>
                                            <label> <input type="radio" name="frm_status" id="status0" value="0" <?php echo set_radio('frm_status', '0'); ?>/> Inactive</label>
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
                                        <div class="input-group date" id="frm_datepicker_start"  style="margin-bottom: 0px;">
                                            <input class="form-control" type="text" name="frm_start_time" id="frm_start_time" value="<?php echo set_value('frm_start_time', '00:00:00'); ?>">
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
                                            <input class="form-control" type="text" name="frm_end_time" id="frm_end_time" value="<?php echo set_value('frm_end_time', '23:59:59'); ?>">
                                            <span class="input-group-addon" style="">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <!--<a href="<?php echo base_url('tariffs/editTP') ?>/<?php echo param_encrypt($data['tariff_id']); ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Tariff Edit Page</button>
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
            <h2>Rate Card Mapping With Tariff plan</h2>
            <ul class="nav navbar-right panel_toolbox"> 
                <li><a href="<?php echo base_url('tariffs/editTP') . "/" . param_encrypt($data['tariff_id']); ?>"><button class="btn btn-danger" type="button" >Back to Tariff Management Page</button></a> </li>
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
