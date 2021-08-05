<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

<div class="">
    <div class="clearfix"></div>    
    <div class="col-md-12 col-sm-12 col-xs-12 right">    
        <div class="x_title">
            <h2>Route Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('routes') ?>"><button class="btn btn-danger" type="button" >Back to Dial Route(s) Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Dial Routes Management(EDIT)</h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />         
                    <form action="<?php echo base_url(); ?>routes/editR/<?php echo param_encrypt($data['dialplan_id']); ?>" method="post" name="edit_form" id="edit_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">    
                        <input type="hidden" name="frm_key" value="<?php echo $data['dialplan_id']; ?>"/>
                        <input type="hidden" name="frm_id" value="<?php echo $data['dialplan_id']; ?>"/>               
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-6 col-xs-12" for="first-name">Routing Name <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="frm_name" id="frm_name" value="<?php echo $data['dialplan_name']; ?>"  data-parsley-required="" data-parsley-length="[5, 20]" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-6 col-xs-12" for="first-name">Routing Code <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="frm_abbr" id="frm_abbr" value="<?php echo $data['dialplan_id']; ?>" class="form-control col-md-7 col-xs-12" disabled="disabled">
                            </div>
                        </div>              
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-6 col-xs-12" for="last-name">Description</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea name="frm_desc" id="frm_desc" class="form-control col-md-7 col-xs-12"><?php echo $data['dialplan_description']; ?></textarea>
                            </div>
                        </div>              
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-6 col-xs-12" for="last-name">Failover SIP Cause</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea name="frm_failover" id="frm_failover" class="form-control col-md-7 col-xs-12"><?php echo $data['failover_sipcause_list']; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-4 col-sm-6 col-xs-12">Status</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <label><input type="radio" name="frm_status" id="status1" value="1"  <?php if ($data['dialplan_status'] == 1) { ?> checked="checked" <?php } ?> /> Active</label>

                                    <label> <input type="radio" name="frm_status" id="status0" value="0" <?php if ($data['dialplan_status'] == 0) { ?> checked="checked" <?php } ?> /> Inactive</label>
                                </div>                     
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-12 col-sm-12 col-xs-12 col-md-offset-6">
                                <!--<a href="<?php echo base_url('routes') ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
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
            <h2>Route Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('routes') ?>"><button class="btn btn-danger" type="button" >Back to Dial Route(s) Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
</div>    
<script>
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
