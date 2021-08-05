<form action="" method="post" name="<?php echo 'tab_form_' . $key; ?>" id="<?php echo 'tab_form_' . $key; ?>" data-parsley-validate class="form-horizontal form-label-left">
    <input type="hidden" name="button_action" id="button_action3" value="">
    <input type="hidden" name="action" value="OkSaveAddressData"> 
    <input type="hidden" name="tab" value="<?php echo $key;?>">
    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>

    <fieldset class="scheduler-border">
        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Account Manager</label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <select name="account_manager" id="account_manager"  class="form-control" >
                    <option value="">Select Account Manager</option>
                    <?php
                    $str = '';
                    foreach ($account_manager_options as $account_manager) {
                        $selected = ' ';
                        if ($account_manager_data['account_manager'] == $account_manager['user_id'])
                            $selected = '  selected="selected" ';
                        $str .= '<option value="' . $account_manager['user_id'] . '" ' . $selected . '>' . $account_manager['name'] . '</option>';
                    }
                    echo $str;
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Contact Name <span class="required">*</span>   </label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <input type="text" name="contact_name" id="contact_name" value="<?php echo $data['contact_name']; ?>"  data-parsley-required="" data-parsley-minlength="3" class="form-control col-md-7 col-xs-12" >
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Company Name <span class="required">*</span>   </label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <input type="text" name="company_name" id="company_name" value="<?php echo $data['company_name']; ?>"  data-parsley-required="" data-parsley-minlength="3" class="form-control col-md-7 col-xs-12" >
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Email Address <span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <input type="text" name="emailaddress" id="emailaddress" value="<?php echo $data['emailaddress']; ?>"  data-parsley-required="" data-parsley-type="email" class="form-control col-md-7 col-xs-12" >
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Country </label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <select name="country_id" id="country_id" class="combobox form-control" >
                    <option value="">Select</option>                    
                    <?php
                    $str = '';
                    foreach ($country_options as $keys => $country_array) {
                        $selected = ' ';
                        if ($data['country_id'] == $country_array->country_id)
                            $selected = '  selected="selected" ';
                        $str .= '<option value="' . $country_array->country_id . '" ' . $selected . '>' . $country_array->country_name . '</option>';
                    }
                    echo $str;
                    ?>
                </select>
            </div>
        </div>   
        <div class="form-group" id="id_state_div">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >State </label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <select name="state_code_id" id="state_code_id" class="form-control" >
                    <option value="">Select</option>                    
                    <?php
                    $str = '';
                    foreach ($state_options as $keys => $state_array) {
                        $selected = ' ';
                        if ($data['state_code_id'] == $state_array['state_code_id'])
                            $selected = '  selected="selected" ';
                        $str .= '<option value="' . $state_array['state_code_id'] . '" ' . $selected . '>' . $state_array['state_name'] . '</option>';
                    }
                    echo $str;
                    ?>
                </select>
            </div>
        </div>    
        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Address </label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <textarea name="address" id="address" class="form-control col-md-7 col-xs-12" ><?php echo $data['address']; ?></textarea>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Phone Number </label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <input type="text" name="phone" id="phone" value="<?php echo $data['phone']; ?>" data-parsley-type="digits" data-parsley-minlength="10" data-parsley-maxlength="12" class="form-control" >
            </div>
        </div>




        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Pin-Code</label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <input type="text" name="pincode" id="pincode" value="<?php echo $data['pincode']; ?>" class="form-control" >
            </div>
        </div>
    </fieldset>

    <div class="ln_solid"></div>
    <div class="form-group">
        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
            <?php if (check_account_permission('reseller', 'edit')): ?>	
                   <button type="button" id="<?php echo 'btnSaveClose'.$key;?>" class="btn btn-info" onclick="save_button('<?php echo $key;?>')">Save</button>
                <!--                                            <button type="button" id="btnSaveClose3" class="btn btn-info"  >Save & Go Back to Listing Page</button>-->
            <?php endif; ?>
        </div>
    </div>

</form>