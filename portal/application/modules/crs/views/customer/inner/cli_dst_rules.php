<?php
$tab_index = 0;

$button_label='';
if (isset($data['cli_dst_rules']) && count($data['cli_dst_rules']) > 0)
{
    $cli_dst_rules = $data['cli_dst_rules'];
    $button_label='Update';
}
else {
    $cli_dst_rules = [
        'account_id' => $data['account_id'],
        'pstn_cli_usage_option' => '0',
        'pstn_max_calls_per_cli_in_aday' => '100',
        'pstn_max_call_per_cli_live' => '10',
      
        'pstn_max_cli_length' => '13',
        'pstn_min_cli_length' => '10',
        'pstn_cli_malfunction' => '0',
        'pstn_min_dst_number_length_option' => '0',
        'pstn_min_dst_number_length' => '10',
        'pstn_max_dst_number_length' => '',
        'did_cli_usage_option' => '0',
        'did_max_calls_per_cli_in_aday' => '0',
        'did_max_call_per_cli_live' => '0',
     
        'did_max_cli_length' => '0',
        'did_min_cli_length' => '0',
    ];
    $button_label='Save';
}
/*
echo '<pre>';
print_r($cli_dst_rules);
echo '</pre>';
*/



?>
<form action="" method="post" name="<?php echo 'tab_form_' . $key; ?>" id="<?php echo 'tab_form_' . $key; ?>" data-parsley-validate class="form-horizontal form-label-left">
    <input type="hidden" name="button_action" id="button_action2" value="">
    <input type="hidden" name="tab" value="<?php echo $key; ?>">
    <input type="hidden" name="action" value="OkSaveCliDst">
    <input type="hidden" name="account_id" value="<?php echo $cli_dst_rules['account_id']; ?>" />

 



    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12">

            <h4 class="text-center">PSTN</h4>




            <div class="form-group ">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Advance Options for CLI and Call Limit Restriction</label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="radio">
                        <input type="radio" name="pstn_cli_usage_option" id="pstn_cli_usage_option1" class="with-gap" value="1" <?php if ($cli_dst_rules['pstn_cli_usage_option'] == 1) { ?> checked="checked" <?php } ?>  /> <label for="pstn_cli_usage_option1">Enabled</label>
                        <input type="radio" name="pstn_cli_usage_option" id="pstn_cli_usage_option2" class="with-gap" value="0" <?php if ($cli_dst_rules['pstn_cli_usage_option'] == 0) { ?> checked="checked" <?php } ?>  /> <label for="pstn_cli_usage_option2">Disabled</label>
                    </div>
                </div>
            </div>
            <div class="form-group pstn_cli_usage_option_dep">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Allowed max call per CLI in a day <span class="required">*</span></label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" name="pstn_max_calls_per_cli_in_aday" id="pstn_max_calls_per_cli_in_aday" value="<?php echo $cli_dst_rules['pstn_max_calls_per_cli_in_aday']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                </div>
            </div>
            <div class="form-group pstn_cli_usage_option_dep">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Allowed max call per CLI simultaneously <span class="required">*</span></label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" name="pstn_max_call_per_cli_live" id="pstn_max_call_per_cli_live" value="<?php echo $cli_dst_rules['pstn_max_call_per_cli_live']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                </div>
            </div>

            
      
                        
            <div class="form-group pstn_cli_usage_option_dep">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Minimum CLI length allowed <span class="required">*</span></label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" name="pstn_min_cli_length" id="pstn_min_cli_length" value="<?php echo $cli_dst_rules['pstn_min_cli_length']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                </div>
            </div>
            <div class="form-group pstn_cli_usage_option_dep">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Maximum CLI length allowed <span class="required">*</span></label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" name="pstn_max_cli_length" id="pstn_max_cli_length" value="<?php echo $cli_dst_rules['pstn_max_cli_length']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                </div>
            </div>
            <!------->
            <hr/>

            <div class="form-group ">
                <label  class="control-label col-md-7 col-sm-6 col-xs-12">Minimum Destination Number length allowed</label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="radio">
                       <input type="radio" name="pstn_min_dst_number_length_option" id="pstn_min_dst_number_length_option1" class="with-gap" value="1" <?php if ($cli_dst_rules['pstn_min_dst_number_length_option'] == 1) { ?> checked="checked" <?php } ?>  />  <label for="pstn_min_dst_number_length_option1">Enabled</label>
                        <input type="radio" name="pstn_min_dst_number_length_option" id="pstn_min_dst_number_length_option2" class="with-gap" value="0" <?php if ($cli_dst_rules['pstn_min_dst_number_length_option'] == 0) { ?> checked="checked" <?php } ?> />  <label for="pstn_min_dst_number_length_option2">Disabled</label>
                    </div>
                </div>
            </div>
            <div class="form-group pstn_min_dst_number_length_option_dep">
                <label class="control-label col-md-7 col-sm-6 col-xs-12" >Minimum Destination Number length <span class="required">*</span></label>
                <div class="col-md-5 col-sm-6 col-xs-12">
                    <input type="text" name="pstn_min_dst_number_length" id="pstn_min_dst_number_length" value="<?php echo $cli_dst_rules['pstn_min_dst_number_length']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                </div>
            </div>  
            


        </div>
        <div class="col-md-6 col-sm-6 col-xs-12">


            <h4 class="text-center">DID</h4>


            <div class="form-group ">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Advance Options for CLI and Call Limit Restriction</label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="radio">
                        <input type="radio" name="did_cli_usage_option" id="did_cli_usage_option1" class="with-gap" value="1" <?php if ($cli_dst_rules['did_cli_usage_option'] == 1) { ?> checked="checked" <?php } ?>  /> <label for="did_cli_usage_option1">Enabled</label>
                        <input type="radio" name="did_cli_usage_option" id="did_cli_usage_option2" class="with-gap" value="0" <?php if ($cli_dst_rules['did_cli_usage_option'] == 0) { ?> checked="checked" <?php } ?>  /> <label for="did_cli_usage_option2">Disabled</label>
                    </div>
                </div>
            </div>
            <div class="form-group did_cli_usage_option_dep">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Allowed max call per CLI in a day <span class="required">*</span></label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" name="did_max_calls_per_cli_in_aday" id="did_max_calls_per_cli_in_aday" value="<?php echo $cli_dst_rules['did_max_calls_per_cli_in_aday']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                </div>
            </div>
            <div class="form-group did_cli_usage_option_dep">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Allowed max call per CLI simultaneously <span class="required">*</span></label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" name="did_max_call_per_cli_live" id="did_max_call_per_cli_live" value="<?php echo $cli_dst_rules['did_max_call_per_cli_live']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                </div>
            </div>

            
           
                        
            <div class="form-group did_cli_usage_option_dep">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Minimum CLI length allowed <span class="required">*</span></label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" name="did_min_cli_length" id="did_min_cli_length" value="<?php echo $cli_dst_rules['did_min_cli_length']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                </div>
            </div>
            <div class="form-group did_cli_usage_option_dep">
                <label class="control-label col-md-7 col-sm-6 col-xs-12">Maximum CLI length allowed <span class="required">*</span></label>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <input type="text" name="did_max_cli_length" id="did_max_cli_length" value="<?php echo $cli_dst_rules['did_max_cli_length']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                </div>
            </div>






        </div>
    </div>




    <div class="ln_solid"></div>
    <div class="form-group">
        <div class="col-md-12 col-sm-6 col-xs-12 text-center">
            <?php if (check_account_permission('customer', 'edit')): ?>
                <button type="button" id="<?php echo 'btnSaveClose' . $key; ?>" class="btn btn-info" onclick="save_button('<?php echo $key; ?>')"><?php echo $button_label;?> </button>

            <?php endif; ?>
        </div>
    </div>
    <br>

</form>
<script>
    function pstn_cli_usage_option_changed()
    {
        media_value = $("input[name='pstn_cli_usage_option']:checked").val();
        if (media_value == '1')
        {
            $('#pstn_max_calls_per_cli_in_aday').attr('data-parsley-required', 'true');
            $('#pstn_max_calls_per_cli_in_aday').attr('data-parsley-type', 'digits');
            $('#pstn_max_calls_per_cli_in_aday').attr('data-parsley-min', '1');

            $('#pstn_max_call_per_cli_live').attr('data-parsley-required', 'true');
            $('#pstn_max_call_per_cli_live').attr('data-parsley-type', 'digits');
            $('#pstn_max_call_per_cli_live').attr('data-parsley-min', '1');

            $('.pstn_cli_usage_option_dep').show();
        }
        else
        {
            $('#pstn_max_calls_per_cli_in_aday').removeAttr('data-parsley-required');
            $('#pstn_max_calls_per_cli_in_aday').removeAttr('data-parsley-type');
            $('#pstn_max_calls_per_cli_in_aday').removeAttr('data-parsley-min');

            $('#pstn_max_call_per_cli_live').removeAttr('data-parsley-required');
            $('#pstn_max_call_per_cli_live').removeAttr('data-parsley-type');
            $('#pstn_max_call_per_cli_live').removeAttr('data-parsley-min');
        
            $('.pstn_cli_usage_option_dep').hide();
        }

    }

    function pstn_min_dst_number_length_option_changed()
    {
        media_value = $("input[name='pstn_min_dst_number_length_option']:checked").val();
        if (media_value == '1')
        {
            $('#pstn_min_dst_number_length').attr('data-parsley-required', 'true');
            $('#pstn_min_dst_number_length').attr('data-parsley-type', 'digits');
            $('#pstn_min_dst_number_length').attr('data-parsley-min', '1');

            $('.pstn_min_dst_number_length_option_dep').show();
        }
        else
        {
            $('#pstn_min_dst_number_length').removeAttr('data-parsley-required');
            $('#pstn_min_dst_number_length').removeAttr('data-parsley-type');
            $('#pstn_min_dst_number_length').removeAttr('data-parsley-min');
        
            $('.pstn_min_dst_number_length_option_dep').hide();
        }

    }


    function did_cli_usage_option_changed()
    {
        media_value = $("input[name='did_cli_usage_option']:checked").val();
        if (media_value == '1')
        {
            $('#did_max_calls_per_cli_in_aday').attr('data-parsley-required', 'true');
            $('#did_max_calls_per_cli_in_aday').attr('data-parsley-type', 'digits');
            $('#did_max_calls_per_cli_in_aday').attr('data-parsley-min', '1');

            $('#did_max_call_per_cli_live').attr('data-parsley-required', 'true');
            $('#did_max_call_per_cli_live').attr('data-parsley-type', 'digits');
            $('#did_max_call_per_cli_live').attr('data-parsley-min', '1');
            
            $('#did_max_calls_in_day').attr('data-parsley-required', 'true');
            $('#did_max_calls_in_day').attr('data-parsley-type', 'digits');
            $('#did_max_calls_in_day').attr('data-parsley-min', '1');
            
            $('#did_min_cli_length').attr('data-parsley-required', 'true');
            $('#did_min_cli_length').attr('data-parsley-type', 'digits');
            $('#did_min_cli_length').attr('data-parsley-min', '1');
            
            $('#did_max_cli_length').attr('data-parsley-required', 'true');
            $('#did_max_cli_length').attr('data-parsley-type', 'digits');
            $('#did_max_cli_length').attr('data-parsley-min', '1');

            $('.did_cli_usage_option_dep').show();
        }
        else
        {
            $('#did_max_calls_per_cli_in_aday').removeAttr('data-parsley-required');
            $('#did_max_calls_per_cli_in_aday').removeAttr('data-parsley-type');
            $('#did_max_calls_per_cli_in_aday').removeAttr('data-parsley-min');

            $('#did_max_call_per_cli_live').removeAttr('data-parsley-required');
            $('#did_max_call_per_cli_live').removeAttr('data-parsley-type');
            $('#did_max_call_per_cli_live').removeAttr('data-parsley-min');
            
            $('#did_max_calls_in_day').removeAttr('data-parsley-required');
            $('#did_max_calls_in_day').removeAttr('data-parsley-type');
            $('#did_max_calls_in_day').removeAttr('data-parsley-min');
            
            $('#did_min_cli_length').removeAttr('data-parsley-required');
            $('#did_min_cli_length').removeAttr('data-parsley-type');
            $('#did_min_cli_length').removeAttr('data-parsley-min');
            
            $('#did_max_cli_length').removeAttr('data-parsley-required');
            $('#did_max_cli_length').removeAttr('data-parsley-type');
            $('#did_max_cli_length').removeAttr('data-parsley-min');
        
            $('.did_cli_usage_option_dep').hide();
        }

    }

    $(document).ready(function () {
        pstn_cli_usage_option_changed();
        pstn_min_dst_number_length_option_changed();
        did_cli_usage_option_changed();
    });

    $('input[type=radio][name=pstn_cli_usage_option]').change(function () {
        pstn_cli_usage_option_changed();
    });
    $('input[type=radio][name=pstn_min_dst_number_length_option]').change(function () {
        pstn_min_dst_number_length_option_changed();
    });

    $('input[type=radio][name=did_cli_usage_option]').change(function () {
        did_cli_usage_option_changed();
    });
</script>