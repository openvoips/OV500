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
                    <h2>SMTP Config(Add)</h2>
                    <ul class="nav navbar-right panel_toolbox">
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form action="<?php echo base_url(); ?>Billing/smtpconfigadd" method="post" name="Random_cli_form" id="Random_cli_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">SMTP Auth<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">                
                                <label><input type="radio" name="smtp_auth" id="smtp_auth" value="1" checked/>  Yes</label>
                                <label> <input type="radio" name="smtp_auth" id="smtp_auth2" value="0" />  No</label>
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
    <div class="clearfix"></div>
    <div class="ln_solid"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>SMTP Email Configuration</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('Billing/smtpconfig') ?>"><button class="btn btn-danger" type="button" >Back to SMTP Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

</div>

<script>
    $('#form_btnSave, #form_btnSaveClose').click(function() {
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