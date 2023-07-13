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
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>


<div class="">
    <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Email Template</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('Billing/emailtemplate') ?>"><button class="btn btn-danger" type="button">Back to Customer Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12"> 
        <div class="x_panel">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_title">
                    <h2>Email Template (Add)</h2>
                    <ul class="nav navbar-right panel_toolbox">
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                   <div class="col-md-8 col-sm-8 col-xs-12"> 
                   
                    <form action="<?php echo base_url(); ?>Billing/emailtemplateadd" method="post" name="emailtemplate_form" id="emailtemplate_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-6 col-xs-12">Email Name<span class="required">*</span></label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                	
                                <input type="text" name="template_name" id="template_name" value="" data-parsley-required="" class="form-control"   placeholder="Email Name">       
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-6 col-xs-12" >Template For <span class="required">*</span></label>

                            <div class="col-md-10 col-sm-6 col-xs-12">    
                                <select name="template_for" id="template_for" class="form-control data-search-field " data-parsley-required="">
                                    <option value="">Select Template For</option>

                                    <option value="INVOICEEMAIL">Invoice Email</option>

                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-6 col-xs-12" >Subject<span class="required">*</span></label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                
                                <input type="text" name="template_subject" id="template_subject" value="" class="form-control" data-parsley-required=""  placeholder="Subject">  
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-6 col-xs-12" data-parsley-required="" >Body</label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                
                                <textarea  name="template_body" id="template_body" rows="4" class="form-control"  placeholder="Body"></textarea>

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-6 col-xs-12" >BCC</label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                
                                <input type="text" name="template_bcc" id="template_bcc" value="" class="form-control"  placeholder="BCC">  
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-6 col-xs-12" >CC</label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                
                                <input type="text" name="template_cc" id="template_cc" value="" class="form-control"  placeholder="CC">  
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-6 col-xs-12" >Email Daemon<span class="required">*</span></label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                

                                <label><input type="radio" name="template_email_daemon" id="template_email_daemon" value="PHPMAIL" checked/> PHP Mail</label>
                                <label> <input type="radio" name="template_email_daemon" id="template_email_daemon2" value="SMTP" /> SMTP</label>
                            </div>
                        </div>
                        <div class="form-group" id="smtpConfig">
                            <label class="control-label col-md-2 col-sm-6 col-xs-12" >SMTP Config List<span class="required">*</span></label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                
                                <select name="smtp_config_id" id="smtp_config_id" class="form-control data-search-field combobox">
                                    <option value="">Select SMTP Config</option>
                                    <?php foreach ($smtp_data as $smtp) {
                                        ?>
                                        <option value="<?php echo $smtp['smtp_config_id'] ?>" ><?php echo $smtp['smtp_from_name'] ?></option>
                                    <?php } ?>    		    
                                </select>
                            </div>
                        </div>
                        <div class="ln_solid"></div> 
                        <div class="form-group">
                            <div class="col-md-10 col-sm-6 col-xs-12 col-md-offset-4 text-right">                           
                                <button type="button" id="form_btnSave" class="btn btn-success" >Save</button>
                                <button type="button" id="form_btnSaveClose" class="btn btn-info" >Save & Go back to Listing Page</button>
                            </div>
                        </div>
                    </form>   
                    
                    
                    </div>
                <div class="col-md-4 col-sm-4 col-xs-12"> 
                
                <div class="x_title">
                    <h2>Mail Variables</h2>
                    <div class="clearfix"></div>
                </div>
                
                <?php
               	$mail_variables = get_mail_variables();
                if(count($mail_variables)>0) {
                    
					 echo '<table class="table table-condensed">';
                    foreach ($mail_variables as $temp_array) {
                        echo '<tr><td>' . $temp_array[0] . ' ::&nbsp;&nbsp;' . $temp_array[1] . '<td></tr> ';
                    }
                    echo '</table>';
                }
                ?>
               
                </div> 
                    
                    
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="ln_solid"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Email Template</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('Billing/emailtemplate') ?>"><button class="btn btn-danger" type="button">Back to Customer Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

</div>

<script>
    $('#smtpConfig').hide();
    $('input[type=radio][name=template_email_daemon]').change(function () {
        if (this.value == 'SMTP') {
            $('#smtpConfig').show();
        } else if (this.value == 'PHPMAIL') {
            $('#smtpConfig').hide();
        }
    });


    $('#form_btnSave, #form_btnSaveClose').click(function () {
        var is_ok = $("#emailtemplate_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'form_btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');


            $("#emailtemplate_form").submit();
        } else
        {
            $('#emailtemplate_form').parsley().validate();
        }
    });
</script>