<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<div class="container-fluid">
    <div class="block-header">
        <h2>Route(ADD) Configuration</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url('routes') ?>"><button class="btn btn-primary" type="button" >Back to Dial Route(s) Listing Page</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">

                    <form action="<?php echo base_url(); ?>routes/addR" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">             
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Routing Name <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="frm_name" id="frm_name" value="<?php echo set_value('frm_name'); ?>"  data-parsley-required="" data-parsley-length="[5, 20]" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>                                
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Description</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea name="frm_desc" id="frm_desc" class="form-control col-md-7 col-xs-12"><?php echo set_value('frm_desc'); ?></textarea>
                            </div>
                        </div>              
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Failover SIP Cause list which want to re-routes</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea name="frm_failover" id="frm_failover" class="form-control col-md-7 col-xs-12"><?php echo set_value('frm_failover'); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-9 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <input type="radio"  class="with-gap" name="frm_status" id="status1" value="1"  <?php echo set_radio('frm_status', '1', TRUE); ?> /> <label for="status1">Active</label>
                                    <input type="radio"  class="with-gap" name="frm_status" id="status0" value="0" <?php echo set_radio('frm_status', '0'); ?> /><label for="status0"> Inactive</label>
                                </div>                     
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-12 col-sm-12 col-xs-12 col-md-offset-6">

                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go back to Edit Page</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="block-header">
        <h2>Route(ADD) Configuration</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url('routes') ?>"><button class="btn btn-primary" type="button" >Back to Dial Route(s) Listing Page</button></a> </li>
        </ul>
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
