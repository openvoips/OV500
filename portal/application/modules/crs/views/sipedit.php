
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$account_id_sip_data = current($data['sipuser']);

?>

<div class="">
    <div class="clearfix"></div> 


    <div class="col-md-12 col-sm-12 col-xs-12 right">          
        <div class="x_title">
            <h2>Customer Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php $tab_index = 0;
echo base_url('crs') . '/editvoip/' . param_encrypt($data['account_id']); ?>/<?php echo $active_tab?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Edit Page</button></a> </li>
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
                     <input type="hidden" name="tab" value="<?php echo $active_tab?>"> 
                    <input type="hidden" name="id" value="<?php echo $account_id_sip_data['id']; ?>"/>
                    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>
<input type="hidden" name="extension_id" value="<?php echo $account_id_sip_data['extension_id']; ?>"/>


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Account Code </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="account_id_name_display" id="account_id_name_display" value="<?php echo $data['company_name'] . ' (' . $data['account_id'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
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
                            <input type="text" name="username" id="username" value="<?php echo $account_id_sip_data['username']; ?>" class="form-control " data-parsley-required="" data-parsley-type="alphanum" data-parsley-minlength="4" data-parsley-maxlength="30" >
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
                            <input type="text" name="extension_no" id="extension" value="<?php echo $account_id_sip_data['extension_no']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-minlength="3" data-parsley-maxlength="18" data-parsley-required="" data-parsley-type="number" autocomplete="off" >
                        </div>                   
                    </div>   


                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Voice Mail</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="voicemail_enabled" id="voicemail_enabled1" value="Y"  <?php if ($account_id_sip_data['voicemail_enabled'] == 'Y') { ?> checked="checked" <?php } ?> /> Active</label>
                            </div>  
                            <div class="radio">
                                <label> <input type="radio" name="voicemail_enabled" id="voicemail_enabled0" value="N" <?php if ($account_id_sip_data['voicemail_enabled'] == 'N') { ?> checked="checked" <?php } ?>/> Inactive</label>
                            </div>

                        </div>
                    </div>


<!--                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Email Address for Voice Mail</label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="voicemail" id="voicemail_email" value="<?php //echo $account_id_sip_data['voicemail']; ?>"  data-parsley-email="" class="form-control col-md-7 col-xs-12">
                        </div>
                        <div class="col-md-1 col-sm-6 col-xs-2">

                        </div>
                    </div>   -->



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
                            <!--<a href="<?php echo base_url($customer_type . 's') . '/edit/' . param_encrypt($data['account_id']); ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
<?php if (check_account_permission('customer', 'edit')): ?>
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                   <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Edit Page</button>
<?php endif; ?>
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
                <li><a href="<?php echo base_url('crs') . '/editvoip/' . param_encrypt($data['account_id']); ?>/<?php echo $active_tab?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Edit Page</button></a> </li>
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
