<form action="" method="post" name="<?php echo 'tab_form_' . $key; ?>" id="<?php echo 'tab_form_' . $key; ?>" data-parsley-validate class="form-horizontal form-label-left">
    <input type="hidden" name="button_action" id="button_action" value="">
    <input type="hidden" name="action" value="OkSaveLoginData"> 
      <input type="hidden" name="tab" value="<?php echo $key;?>">
    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/> 
    <input type="hidden" name="user_id" value="<?php echo $user_data['user_id']; ?>"/> 
    <div class="form-group">
        <label class="control-label col-md-5 col-sm-6 col-xs-12" >Web Access Username <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input type="text" name="username" id="username" value="<?php echo $user_data['username']; ?>" class="form-control col-md-7 col-xs-12" >
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-5 col-sm-6 col-xs-12" >Web Access Password <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-10">
            <input type="text" name="secret" id="secret" value="<?php echo $user_data['secret']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-minlength="8" data-parsley-maxlength="30" data-parsley-password="" autocomplete="off" >
        </div>                       
    </div>  

    <div class="form-group">
        <label class="control-label col-md-5 col-sm-6 col-xs-12" >Email Address <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-10">
            <input type="text" name="emailaddress" id="emailaddress" value="<?php echo $user_data['emailaddress']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-required="true" data-parsley-type="email" >
        </div>                       
    </div>  
    
    <div class="form-group">
        <label for="middle-name" class="control-label col-md-5 col-sm-6 col-xs-12">Reset Google 2FA Code</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="checkbox">	
           &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="reset_gcode" id="reset_gcode" value="1" />
           <span class="text-info"><small>Checking this option will reset the existing code and user will get option to scan again</small></span>
           </div>
        </div>
    </div>

    <div class="ln_solid"></div>
    <div class="form-group">
        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
            <?php if (check_account_permission('reseller', 'edit')): ?>	
                  <button type="button" id="<?php echo 'btnSaveClose'.$key;?>" class="btn btn-info" onclick="save_button('<?php echo $key;?>')">Save</button>                        
            <?php endif; ?>
        </div>
    </div>

</form>