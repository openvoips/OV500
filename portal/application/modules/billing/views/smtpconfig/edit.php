<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2021 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 2.0.0
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


<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>


<div class="">
    <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>SMTP Email Configuration</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('Billing/smtpconfig') ?>"><button class="btn btn-danger" type="button">Back to SMTP Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12"> 
        <div class="x_panel">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_title">
                    <h2>SMTP Config (Update)</h2>
                    <ul class="nav navbar-right panel_toolbox">
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                <form action="<?php echo site_url('Billing/smtpconfigedit/'.param_encrypt($smtp_data['smtp_config_id'])); ?>" method="post" name="smtpconfig_form" id="smtpconfig_form" data-parsley-validate class="form-horizontal form-label-left">
                <input type="hidden" name="action" value="OkSaveData"> 
                <input type="hidden" name="button_action" id="button_action" value="">
                <input type="hidden" name="smtp_config_id" value="<?php echo $smtp_data['smtp_config_id'] ?>"> 
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12">SMTP Auth<span class="required">*</span></label>
                <div class="col-md-8 col-sm-6 col-xs-12">                	
                
                <label><input type="radio" name="smtp_auth" id="smtp_auth" value="1" <?php if($smtp_data['smtp_auth']=='1'){echo 'checked';} ?>/>  Yes</label>
                <label> <input type="radio" name="smtp_auth" id="smtp_auth2" value="0" <?php if($smtp_data['smtp_auth']=='0'){echo 'checked';} ?>/>  No</label>
                </div>
                </div> 
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP SECURE <span class="required">*</span></label>
                
                <div class="col-md-8 col-sm-6 col-xs-12">    
                <select name="smtp_secure" id="smtp_secure" class="form-control data-search-field combobox" data-parsley-required="">
                <option value="">Select SMTP Secure</option>                
                <option value="SSL" <?php if($smtp_data['smtp_secure']=='SSL'){ echo 'selected';}?> >SSL</option>
                <option value="TSL" <?php if($smtp_data['smtp_secure']=='TSL'){ echo 'selected';}?>>TSL</option>
                </select>
                </div>
                </div>
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Host<span class="required">*</span></label>
                <div class="col-md-8 col-sm-6 col-xs-12">                
                <input type="text" name="smtp_host" id="smtp_host" value="<?php echo $smtp_data['smtp_host'] ?>" class="form-control" data-parsley-required="" placeholder="SMTP Host">  
                </div>
                </div>
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Port<span class="required">*</span></label>
                <div class="col-md-8 col-sm-6 col-xs-12">                
                <input type="text"  name="smtp_port" id="smtp_port" value="<?php echo $smtp_data['smtp_port'] ?>"  class="form-control" data-parsley-required="" placeholder="SMTP Port">
                </div>
                </div>
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Username<span class="required">*</span></label>
                <div class="col-md-8 col-sm-6 col-xs-12">                
                <input type="text" name="smtp_username" id="smtp_username" value="<?php echo $smtp_data['smtp_username'] ?>" class="form-control" data-parsley-required="" placeholder="SMTP Username">  
                </div>
                </div>
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Password<span class="required">*</span></label>
                <div class="col-md-8 col-sm-6 col-xs-12">                
                <input type="text" name="smtp_password" id="smtp_password" value="<?php echo $smtp_data['smtp_password'] ?>" class="form-control" data-parsley-required="" placeholder="SMTP Password">  
                </div>
                </div>
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP From Email<span class="required">*</span></label>
                <div class="col-md-8 col-sm-6 col-xs-12">                
                <input type="text" name="smtp_from" id="smtp_from" value="<?php echo $smtp_data['smtp_from'] ?>" class="form-control" >  
                </div>
                </div>
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP From Name<span class="required">*</span></label>
                <div class="col-md-8 col-sm-6 col-xs-12">                
                <input type="text" name="smtp_from_name" id="smtp_from_name" value="<?php echo $smtp_data['smtp_from_name'] ?>" class="form-control" >  
                </div>
                </div>
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Xmailer<span class="required">*</span></label>
                <div class="col-md-8 col-sm-6 col-xs-12">                
                <input type="text" name="smtp_xmailer" id="smtp_xmailer" value="<?php echo $smtp_data['smtp_xmailer'] ?>" class="form-control" >  
                </div>
                </div>
                <div class="form-group">
                <label class="control-label col-md-4 col-sm-3 col-xs-12" >SMTP Host Name<span class="required">*</span></label>
                <div class="col-md-8 col-sm-6 col-xs-12">                
                <input type="text" name="smtp_host_name" id="smtp_host_name" value="<?php echo $smtp_data['smtp_host_name'] ?>" class="form-control" >  
                </div>
                </div>
                
                <div class="ln_solid"></div> 
                <div class="form-group">
                <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4 text-right">                               
                <button type="button" id="form_btnSave" class="btn btn-success" >Save</button>
                <button type="button" id="form_btnSaveClose" class="btn btn-info" >Save & Go back to Listing Page</button>
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
           <h2>SMTP Email Configuration</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('Billing/smtpconfig') ?>"><button class="btn btn-danger" type="button">Back to SMTP Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

</div>

<script>
$('#form_btnSave, #form_btnSaveClose').click(function () {
	var is_ok = $("#smtpconfig_form").parsley().isValid();
	if (is_ok === true)
	{
		var clicked_button_id = this.id;
		if (clicked_button_id == 'form_btnSaveClose')
			$('#button_action').val('save_close');
		else
			$('#button_action').val('save');


		$("#smtpconfig_form").submit();
	} else
	{
		$('#smtpconfig_form').parsley().validate();
	}
});
</script>