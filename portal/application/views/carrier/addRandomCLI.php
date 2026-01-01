<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>



<div class="container-fluid">
    <div class="block-header">
        <h2>Carrier RandomCLI(ADD)</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url() . 'carriers/edit/' . param_encrypt($data['carrier_id']); ?>/<?php echo $active_tab; ?>"><button class="btn btn-primary" type="button" >Back to Carrier Edit Page</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">

                    <form action="" method="post" name="carrier_form" id="carrier_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="carrier_id" value="<?php echo $data['carrier_id']; ?>"/>    
                        <input type="hidden" name="carrier_key" value="<?php echo $data['carrier_id']; ?>"/>        

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier <span class="required">*</span>          </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="carrier_name_display" id="carrier_name_display" value="<?php echo $data['carrier_id'] . ' (' . $data['carrier_name'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">CLI Rule Name <span class="required">*</span>
                            </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="clirule_name" id="clirule_name" value="<?php echo set_value('clirule_name'); ?>" data-parsley-required="" data-parsley-minlength="4" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Destination Prefix<span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="destination_prefix" id="destination_prefix" value="<?php echo set_value('destination_prefix'); ?>" data-parsley-required="" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Random CLI Starting with <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="cli_fixprefix" id="cli_fixprefix" value="<?php echo set_value('cli_fixprefix'); ?>" data-parsley-required="" class="form-control col-md-7 col-xs-12">

                            </div>
                        </div>



                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Random CLI Suffix length  <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="cli_length" id="cli_length" value="<?php echo set_value('cli_length'); ?>" data-parsley-required="" class="form-control col-md-7 col-xs-12">

                            </div>
                        </div>




                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <input type="radio" class="with-gap" name="cli_status" id="status1" value="1" <?php echo set_radio('cli_status', '1', TRUE); ?> /> <label for="status1">Active</label>
                                    <input type="radio" class="with-gap" name="cli_status" id="status0" value="0" <?php echo set_radio('cli_status', '0'); ?> /> <label for="status0">Inactive</label>
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

        <div class="block-header">
            <h2>Carrier RandomCLI(ADD)</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a href="<?php echo base_url() . 'carriers/edit/' . param_encrypt($data['carrier_id']); ?>/<?php echo $active_tab; ?>"><button class="btn btn-primary" type="button" >Back to Carrier Edit Page</button></a> </li>
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

        function auth_type_change()
        {
            if ($("#auth_type").val() == 'CUSTOMER')
            {
                $('.ip_dependent').show();

                $('#username').attr('data-parsley-required', 'true');
                //$('#username').attr('data-parsley-type', 'alphanum');  
                //$('#username').attr('data-parsley-minlength', '6');  
                //$('#username').attr('data-parsley-maxlength', '30');		

                ///////////
                $('#secret').attr('data-parsley-required', 'true');
                //$('#secret').attr('data-parsley-minlength', '8');
                $('#secret').attr('data-parsley-maxlength', '30');
                //$('#secret').attr('data-parsley-password', '');

                //$("#carrier_form").parsley().refresh();
            } else {
                $('#username').attr('data-parsley-required', 'false');
                //$('#username').removeAttr('data-parsley-type');  
                //$('#username').removeAttr('data-parsley-minlength');  
                //$('#username').removeAttr('data-parsley-maxlength');

                ///////////
                $('#secret').attr('data-parsley-required', 'false');
                //$('#secret').removeAttr('data-parsley-minlength');
                $('#secret').removeAttr('data-parsley-maxlength');
                //$('#secret').removeAttr('data-parsley-password');

                ///////



                $('.ip_dependent').hide();
            }
        }

        $(document).ready(function () {
            auth_type_change();


        });

    </script>
