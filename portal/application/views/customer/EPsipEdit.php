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
<?php
$account_id_sip_data = current($data['sipuser']);
//echo '<pre>';print_r($data);echo '</pre>';
?>

<div class="">
    <div class="clearfix"></div> 


    <div class="col-md-12 col-sm-12 col-xs-12 right">          
        <div class="x_title">
            <h2>Customer Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php  echo base_url('endpoints/index/') . param_encrypt($data['account_id']);?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Endpoints Edit Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>


    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Customer SIP Users Devices(EDIT)</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br />
                <form action="" method="post" name="sip_form" id="sip_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="button_action" id="button_action" value="">
                    <input type="hidden" name="action" value="OkSaveData"> 
                    <input type="hidden" name="id" value="<?php echo $account_id_sip_data['id']; ?>"/>
                    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>



                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Account Code </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="account_id_name_display" id="account_id_name_display" value="<?php echo $data['account_id'] . ' (' . $data['name'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
<!--                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="callingcard_pin">Calling Card Access PIN </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">-->
                            <input type="hidden" name="callingcard_pin" id="account_id_name_display" value="<?php echo $account_id_sip_data['callingcard_pin']; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
<!--                        </div>
                    </div>-->

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">SIP Device Login <span class="required">*</span> </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="username" id="username" value="<?php echo $account_id_sip_data['username']; ?>" class="form-control " data-parsley-required="" data-parsley-type="alphanum" data-parsley-minlength="6" data-parsley-maxlength="30" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">SIP Device Secret </label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="secret" id="secret" value="<?php echo $account_id_sip_data['secret']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-minlength="8" data-parsley-maxlength="30"   data-parsley-password="" autocomplete="off">
                        </div>
                        <div class="col-md-1 col-sm-6 col-xs-2">

                        </div>
                    </div>              

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Extension No <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="extension_no" id="extension" value="<?php echo $account_id_sip_data['extension_no']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-minlength="3" data-parsley-maxlength="8" data-parsley-required="" data-parsley-type="number" autocomplete="off" >
                        </div>                   
                    </div>   


                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Voice Mail</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="voicemail" id="status1" value="1"  <?php if ($account_id_sip_data['voicemail'] == 1) { ?> checked="checked" <?php } ?> /> Active</label>
                            </div>  
                            <div class="radio">
                                <label> <input type="radio" name="voicemail" id="status0" value="0" <?php if ($account_id_sip_data['voicemail'] == 0) { ?> checked="checked" <?php } ?>/> Inactive</label>
                            </div>

                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Email Address for Voice Mail</label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="voicemail_email" id="voicemail_email" value="<?php echo $account_id_sip_data['voicemail_email']; ?>"  data-parsley-email="" class="form-control col-md-7 col-xs-12">
                        </div>
                        <div class="col-md-1 col-sm-6 col-xs-2">

                        </div>
                    </div>   



                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">IP Address </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="ipaddress" id="ipaddress" value="<?php echo $account_id_sip_data['ipaddress']; ?>"  data-parsley-ip="" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Maximum Call Sessions <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="sip_cc" id="sip_cc" value="<?php echo $account_id_sip_data['sip_cc']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Call Sessions per Second  <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="sip_cps" id="sip_cps" value="<?php echo $account_id_sip_data['sip_cps']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="status" id="status1" value="1"  <?php if ($account_id_sip_data['status'] == 1) { ?> checked="checked" <?php } ?> /> Active</label>
                            </div>  
                            <div class="radio">
                                <label> <input type="radio" name="status" id="status0" value="0" <?php if ($account_id_sip_data['status'] == 0) { ?> checked="checked" <?php } ?>/> Inactive</label>
                            </div>

                        </div>
                    </div>

                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">                          
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to listing Page</button>
<?php// endif; ?>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>


    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Customer Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php  echo base_url('endpoints/index/') . param_encrypt($data['account_id']);?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Endpoints Edit Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>


</div>    
<script>

    window.Parsley
            .addValidator('ip', {
                validateString: function (value) {
                    var pattern = /^[0-9:.]+$/;
                    if (!pattern.test(value))
                        return false;
                    else
                        return true;
                },
                messages: {
                    en: 'Invalid IP'
                }
            });


    window.Parsley
            .addValidator('password', {
                validateString: function (value) {
                    r = true;
                    if (!vCheckPassword(value))
                    {
                        r = false;
                    }
                    return r;
                },
                messages: {
                    en: 'min 8 char, 1 special char, 1 uppercase, 1 lowercase, 1 number'
                }
            });


    $('#btnSave, #btnSaveClose').click(function () {

        pass_value = $('#secret').val().trim();
        if (pass_value != '')
        {
            $('#repassword').attr('data-parsley-required', 'true');
            $('#repassword').attr('data-parsley-equalto', '#secret');
        } else
        {
            $('#repassword').attr('data-parsley-required', 'false');
            $('#repassword').removeAttr('data-parsley-equalto');
        }


        ipaddress_value = $('#ipaddress').val().trim();
        if (ipaddress_value != '')
        {
            $('#ipaddress').attr('data-parsley-required', 'true');
            $('#ipaddress').attr('data-parsley-ip', '#secret');
        } else
        {
            $('#ipaddress').attr('data-parsley-required', 'false');
            $('#ipaddress').removeAttr('data-parsley-ip');
        }


        $('#sip_form').parsley().reset();

        var is_ok = $("#sip_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            if (is_ok === true)
            {
                //alert('ok');
                $("#sip_form").submit();
            }
        } else
        {
            $('#sip_form').parsley().validate();
        }
    })



    $(document).ready(function () {


    });

</script>
