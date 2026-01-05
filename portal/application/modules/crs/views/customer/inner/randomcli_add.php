<?php
$tab_index = 0;
?>



<?php if ($type == 1) { ?>
    <form action="" method="post" name="<?php echo 'tab_form_' . $key; ?>" id="<?php echo 'tab_form_' . $key; ?>"
          data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="button_action" id="button_action2" value="">
                    <input type="hidden" name="tab" value="<?php echo $key; ?>">

                    <input type="hidden" name="action" value="OkSaveData">
                    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>" />

                    <input type="hidden" name="rule_type" value="1">

                    <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-6 col-xs-12">CLI Rule Name<span class="required">*</span>
                                    </label>
                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                    <input type="text" name="clirule_name" id="clirule_name" value="<?php echo set_value('clirule_name'); ?>"
                                                           data-parsley-required="" data-parsley-minlength="4" class="form-control col-md-7 col-xs-12">
                                    </div>
                    </div>

                    <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-6 col-xs-12">Destination Prefix <span class="required">*</span>
                                    </label>
                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                    <input type="text" name="destination_prefix" id="destination_prefix"
                                                           value="<?php echo set_value('destination_prefix'); ?>" data-parsley-required=""
                                                           data-parsley-minlength="1" class="form-control col-md-7 col-xs-12">
                                    </div>
                    </div>
                    <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-6 col-xs-12">Random CLI Starting
                                                    with<span class="required">*</span></label>
                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                    <input type="text" name="cli_fixprefix" id="cli_fixprefix" value="<?php echo set_value('cli_fixprefix'); ?>"
                                                           data-parsley-required="" class="form-control col-md-7 col-xs-12">
                                    </div>
                    </div>

                    <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-6 col-xs-12">Random CLI Suffix
                                                    length</label>
                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                    <input type="text" name="cli_length" id="cli_length" value="<?php echo set_value('cli_length'); ?>"
                                                           data-parsley-required="" data-parsley-type="digits" data-parsley-range="[1, 100]"
                                                           class="form-control col-md-7 col-xs-12">
                                    </div>
                    </div>


                    <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                    <div class="radio">
                                                                    <input class="with-gap" type="radio" name="cli_status" id="cli_status1" value="1" <?php echo set_radio('cli_status', '1', true); ?> /><label for="cli_status1"> Active</label>

                                                                    <input class="with-gap" type="radio" name="cli_status" id="cli_status0" value="0" <?php echo set_radio('cli_status', '0'); ?> /> <label for="cli_status0">Inactive</label>
                                                    </div>

                                    </div>
                    </div>





                    <div class="ln_solid"></div>
                    <div class="form-group">
                                    <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                                    <button type="button" id="<?php echo 'btnSaveClose' . $key; ?>" class="btn btn-info"
                                                            onclick="save_button('<?php echo $key; ?>')">Update</button>

                                    </div>
                    </div>

    </form>


<?php } else {
    ?>
    <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="col-md-7 col-sm-12 col-xs-12">
                                    <form action="" method="post" name="<?php echo 'tab_form_' . $key; ?>" id="<?php echo 'tab_form_' . $key; ?>"
                                          data-parsley-validate class="form-horizontal form-label-left">
                                                    <input type="hidden" name="button_action" id="button_action2" value="">
                                                    <input type="hidden" name="tab" value="<?php echo $key; ?>">

                                                    <input type="hidden" name="action" value="OkSaveDataType2">
                                                    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>" />
                                                    <input type="hidden" name="rule_type" value="2">


                                                    <div class="form-group">
                                                                    <label class="control-label col-md-4 col-sm-6 col-xs-12">Destination Pattern
                                                                                    <span class="required">*</span>
                                                                    </label>
                                                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                                                    <input type="text" name="destination_prefix" id="destination_prefix"
                                                                                           value="<?php echo set_value('destination_prefix'); ?>" data-parsley-required=""
                                                                                           data-parsley-minlength="1" class="form-control col-md-7 col-xs-12">
                                                                    </div>
                                                    </div>
                                                    <div class="form-group">
                                                                    <label class="control-label col-md-4 col-sm-6 col-xs-12">Caller ID<span
                                                                                                    class="required">*</span></label>
                                                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                                                    <input type="text" name="cli_fixprefix" id="cli_fixprefix"
                                                                                           value="<?php echo set_value('cli_fixprefix'); ?>" data-parsley-required=""
                                                                                           class="form-control col-md-7 col-xs-12">
                                                                    </div>
                                                    </div>


                                                    <div class="form-group">
                                                                    <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                                                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                                                    <div class="radio">
                                                                                                    <input class="with-gap" type="radio" name="cli_status" id="cli_status1" value="1" <?php echo set_radio('cli_status', '1', true); ?> /><label for="cli_status1"> Active</label>

                                                                                                    <input class="with-gap" type="radio" name="cli_status" id="cli_status0" value="0" <?php echo set_radio('cli_status', '0'); ?> /> <label for="cli_status0">Inactive</label>
                                                                                    </div>

                                                                    </div>
                                                    </div>




                                                    <div class="ln_solid"></div>
                                                    <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                                                    <button type="button" id="<?php echo 'btnSaveClose' . $key; ?>" class="btn btn-info"
                                                                            onclick="save_button('<?php echo $key; ?>')">Update</button>

                                                    </div>

                                    </form>



                    </div>
                    <div class="col-md-5 col-sm-12 col-xs-12">
                                    <form action="" method="post" name="cust_form_file" id="cust_form_file" data-parsley-validate
                                          class="form-horizontal form-label-left" enctype="multipart/form-data">
                                                    <input type="hidden" name="button_action" id="button_action" value="">
                                                    <input type="hidden" name="action" value="OkSaveDataFile">
                                                    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>" />
                                                    <input type="hidden" name="rule_type" value="2">


                                                    <div class="form-group">
                                                                    <label class="control-label col-md-4 col-sm-6 col-xs-12">File <span class="required">*</span></label>
                                                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                                                    <input name="file" type="file" required />
                                                                    </div>
                                                    </div>

                                                    <div class="form-group">
                                                                    <div class="control-label col-md-12 col-sm-12 col-xs-12">
                                                                                    <input type="checkbox" name="delete_existing" id="delete_existing" value="1">
                                                                                    <label for="delete_existing">Delete All Existing Entries</label></div>
                                                    </div>

                                                    <div class="form-group" id="sampleFile">
                                                                    <label class="control-label col-md-5 col-sm-3 col-xs-12"> </label>
                                                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                                                                    <a href="<?php echo base_url('crs/customers/download/' . param_encrypt('randomcli.csv')); ?>">
                                                                                                    <button  type="button" class="btn btn-dark btn-sm">Download Sample File</button>
                                                                                    </a>
                                                                    </div>
                                                    </div>


                                                    <div class="form-group">&nbsp;</div>


                                                    <div class="ln_solid"></div>
                                                    <div class="form-group">
                                                                    <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                                                                    <button type="button" id="btnSaveFile" class="btn btn-success">Update</button>

                                                                    </div>
                                                    </div>




                                    </form>

                    </div>
    </div>

<?php } ?>
<script>
    $('#btnSaveFile, #btnSaveCloseFile').click(function () {
                    var is_ok = $("#cust_form_file").parsley().isValid();
                    if (is_ok === true) {
                                    if (is_ok === true) {
                                                    //alert('ok');
                                                    $("#cust_form_file").submit();
                                    }
                    } else {
                                    $('#cust_form_file').parsley().validate();
                    }
    })
</script>