<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<div class="">
    <div class="clearfix"></div>

    <div class="col-md-12 col-sm-12 col-xs-12 right">       
        <div class="x_title">
            <h2>Customer Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php echo base_url('endpoints/index/') . param_encrypt($data['account_id']); ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Endpoints Edit Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Customer SIP Users Devices(ADD)</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br />
                <form action="" method="post" name="carrier_form" id="carrier_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="button_action" id="button_action" value="">
                    <input type="hidden" name="action" value="OkSaveData"> 
                    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Account Code </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="account_name_display" id="account_name_display" value="<?php echo $data['account_id'] . ' (' . $data['company_name'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">SIP Device Login <span class="required">*</span> </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="username" id="username" value="<?php echo set_value('username'); ?>" class="form-control " data-parsley-required="" data-parsley-type="alphanum" data-parsley-minlength="4" data-parsley-maxlength="30" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">SIP Device Secret <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="secret" id="secret" value="" class="form-control col-md-7 col-xs-12" data-parsley-minlength="8" data-parsley-maxlength="30"  data-parsley-required="" data-parsley-password="" autocomplete="off">
                        </div>
                        <div class="col-md-1 col-sm-6 col-xs-2">

                        </div>
                    </div>       

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Extension No <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="extension_no" id="extension" value="" class="form-control col-md-7 col-xs-12" data-parsley-minlength="3" data-parsley-maxlength="15" data-parsley-required="" data-parsley-type="number" autocomplete="off" >

                        </div>
                        <div class="col-md-1 col-sm-6 col-xs-2">

                        </div>
                    </div>   


                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Voice Mail</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="voicemail" id="status1" value="1"  <?php echo set_radio('status', '1', TRUE); ?> /> Active</label>
                            </div>  
                            <div class="radio">
                                <label> <input type="radio" name="voicemail" id="status0" value="0" <?php echo set_radio('status', '0'); ?> /> Inactive</label>
                            </div>

                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Email Address for Voice Mail </label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="voicemail_email" value=""  data-parsley-email="" class="form-control col-md-7 col-xs-12">
                        </div>
                        <div class="col-md-1 col-sm-6 col-xs-2">

                        </div>
                    </div>   


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Device Registartion allowed from IP Address Only</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="ipaddress" id="ipaddress" value="<?php echo set_value('ipaddress'); ?>"  data-parsley-ip="" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Maximum Call Sessions <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="sip_cc" id="sip_cc" value="<?php echo set_value('sip_cc', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Call Sessions per Second <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="sip_cps" id="sip_cps" value="<?php echo set_value('sip_cps', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="status" id="status1" value="1"  <?php echo set_radio('status', '1', TRUE); ?> /> Active</label>
                            </div>  
                            <div class="radio">
                                <label> <input type="radio" name="status" id="status0" value="0" <?php echo set_radio('status', '0'); ?> /> Inactive</label>
                            </div>

                        </div>
                    </div>

                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                            <button type="button" id="btnSave" class="btn btn-success">Save</button>
                            <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to listing Page</button>
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
                <li><a href="<?php echo base_url('endpoints/index/') . param_encrypt($data['account_id']); ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Endpoints Edit Page</button></a> </li>
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
        var is_ok = $("#carrier_form").parsley().isValid();
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
                $("#carrier_form").submit();
            }
        } else
        {
            $('#carrier_form').parsley().validate();
        }
    })



    $(document).ready(function () {


    });

</script>
