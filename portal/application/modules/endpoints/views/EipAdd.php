 
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

<div class="container-fluid">
    <div class="block-header">
        <h2>Customer IP Device(s) Management</h2>
        <ul class="nav navbar-right panel_toolbox">             
            <li><a href="<?php
                $tab_index = 0;
                echo base_url('endpoints') . '/ipdevice/' . param_encrypt($data['account_id']);
                ?>"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Back to IP Device Page</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">
                    <form action="" method="post" name="account_form" id="account_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>
                        <input type="hidden" name="id" value="<?php echo $data['id']; ?>"/>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Account Code </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="account_name_display" id="account_name_display" value="<?php echo $data['account_id'] . ' (' . $data['company_name'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">IP Address <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="ipaddress" id="ipaddress" value="<?php echo set_value('ipaddress'); ?>" data-parsley-required="" data-parsley-ip="" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Description </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <textarea name="description" id="description" rows="5" class="form-control col-md-7 col-xs-12"><?php echo set_value('description'); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Dial Prefix <span class="required">*</span>
                            </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="dialprefix" id="dialprefix" value="<?php echo set_value('dialprefix', '%'); ?>" data-parsley-required="" data-parsley-pattern="^\d*%$" data-parsley-pattern-message="Number with % at end" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Maximum Call Sessions <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="ip_cc" id="ip_cc" value="<?php echo set_value('ip_cc', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Call Sessions per Second <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="ip_cps" id="ip_cps" value="<?php echo set_value('ip_cps', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <input type="radio" class="with-gap"  name="ip_status" id="status1" value="1"  <?php echo set_radio('ip_status', '1', TRUE); ?> />  <label for="status1">Active</label>

                                    <input type="radio" class="with-gap"  name="ip_status" id="status0" value="0" <?php echo set_radio('ip_status', '0'); ?> />  <label for="status0">Inactive</label>
                                </div>

                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">

                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Edit Page</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="block-header">
        <h2>Customer Account Configuration Management</h2>
        <ul class="nav navbar-right panel_toolbox">             
            <li><a href="<?php
                $tab_index = 0;
                echo base_url('endpoints') . '/ipdevice/' . param_encrypt($data['account_id']);
                ?>"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Edit Page</button></a> </li>
        </ul>
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





    $('#btnSave, #btnSaveClose').click(function () {
        var is_ok = $("#account_form").parsley().isValid();
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
                $("#account_form").submit();
            }
        } else
        {
            $('#account_form').parsley().validate();
        }
    })


    $(document).ready(function () {


    });

</script>
