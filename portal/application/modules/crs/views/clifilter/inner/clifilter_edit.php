<?php
$tab_index = 0;
?>




<form action="" method="post" name="<?php echo 'tab_form_' . $key; ?>" id="<?php echo 'tab_form_' . $key; ?>"
    data-parsley-validate class="form-horizontal form-label-left">
    <input type="hidden" name="button_action" id="button_action2" value="">
   
    <input type="hidden" name="action" value="OkSaveData">
    <input type="hidden" name="clifilterdid_id" value="<?php echo $cli_data['id']; ?>" />
    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>" />
    <input type="hidden" name="type" value="<?php echo $active_tab;?>">




    <div class="form-group">
        <label class="control-label col-md-4 col-sm-6 col-xs-12">Account Code <span class="required">*</span> </label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input type="text" name="carrier_name" id="carrier_name_display"
                value="<?php echo $data['account_id'] . ' (' . $data['company_name'] . ')'; ?>" disabled="disabled"
                class="form-control col-md-7 col-xs-12">
        </div>
    </div>



        <div class="form-group">
            <label class="control-label col-md-4 col-sm-6 col-xs-12">Caller ID<span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <input type="text" name="callerid" id="callerid"
                    value="<?php echo $cli_data['callerid']; ?>" data-parsley-required=""
                    class="form-control col-md-7 col-xs-12">
            </div>
        </div>







    <div class="form-group">
        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <input class="with-gap" type="radio" name="cli_status" id="cli_status1" value="1" <?php if ($cli_data['cli_status'] == 1) { ?> checked="checked" <?php } ?> /><label for="cli_status1">
                    Active</label>

                <input class="with-gap" type="radio" name="cli_status" id="cli_status0" value="2" <?php if ($cli_data['cli_status'] == 2) { ?> checked="checked" <?php } ?> /> <label
                    for="cli_status0">Not In Use</label>
                <?php if ($cli_data['cli_status'] == 0){?>
                <input class="with-gap" type="radio" name="cli_status" id="cli_status2" value="0" <?php if ($cli_data['cli_status'] == 0) { ?> checked="checked" <?php } ?> /> <label
                    for="cli_status0">Blocked</label>
                <?php } ?>
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




<?php //echo '<pre>';print_r($data);echo '</pre>';
?>