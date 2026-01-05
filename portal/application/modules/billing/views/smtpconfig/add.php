<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>



<div class="container-fluid">
    <div class="block-header">
        <h2>SMTP Email Configuration</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url('Billing/smtpconfig') ?>"><button class="btn btn-primary" type="button">Back to SMTP Listing Page</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">

                    <form action="<?php echo base_url(); ?>Billing/smtpconfigadd" method="post" name="Random_cli_form" id="Random_cli_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">SMTP Auth<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <input type="radio" name="smtp_auth" id="smtp_auth" value="1" class="with-gap" checked/>  <label for="smtp_auth">Yes</label>
                                <label> <input type="radio" name="smtp_auth" id="smtp_auth2" value="0" class="with-gap" /> <label for="smtp_auth2">  No</label>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP SECURE <span class="required">*</span></label>                
                            <div class="col-md-8 col-sm-6 col-xs-12">    
                                <select name="smtp_secure" id="smtp_secure" class="form-control data-search-field combobox" data-parsley-required="">
                                    <option value="">Select SMTP Secure</option>                
                                    <option value="SSL" >SSL</option>
                                    <option value="TSL" >TSL</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Host<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <input type="text" name="smtp_host" id="smtp_host" value="" class="form-control" data-parsley-required="" placeholder="SMTP Host">  
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Port<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <input type="text"  name="smtp_port" id="smtp_port"  class="form-control" data-parsley-required="" placeholder="SMTP Port">                
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Username<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <input type="text" name="smtp_username" id="smtp_username" value="" class="form-control" data-parsley-required="" placeholder="SMTP Username">  
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Password<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <input type="text" name="smtp_password" id="smtp_password" value="" class="form-control" data-parsley-required="" placeholder="SMTP Password">  
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP From Email<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <input type="text" name="smtp_from" id="smtp_from" value="" class="form-control" >  
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP From Name<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <input type="text" name="smtp_from_name" id="smtp_from_name" value="" class="form-control" >  
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Xmailer<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <input type="text" name="smtp_xmailer" id="smtp_xmailer" value="" class="form-control" >  
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Host Name<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <input type="text" name="smtp_host_name" id="smtp_host_name" value="" class="form-control" >  
                            </div>
                        </div>

                        <div class="ln_solid"></div> 
                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4 text-center">                             
                                <button type="button" id="form_btnSave" class="btn btn-success" >Save</button>
                                <button type="button" id="form_btnSaveClose" class="btn btn-info" >Save & Go back to Listing Page</button>
                            </div>
                        </div>
                    </form>   
                </div>
            </div>
        </div>
    </div>
    <div class="block-header">
        <h2>SMTP Email Configuration</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url('Billing/smtpconfig') ?>"><button class="btn btn-primary" type="button">Back to SMTP Listing Page</button></a> </li>
        </ul>
    </div>

</div>

<script>
    $('#form_btnSave, #form_btnSaveClose').click(function () {
        var is_ok = $("#Random_cli_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'form_btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');


            $("#Random_cli_form").submit();
        } else
        {
            $('#Random_cli_form').parsley().validate();
        }
    });
</script>