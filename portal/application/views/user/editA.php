
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<div class="">
    <div class="clearfix"></div> 
    <div class="col-md-12 col-sm-12 col-xs-12 right">      
        <div class="x_title">
            <h2>System Users Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('users') ?>"><button class="btn btn-danger" type="button" >Back to System Users Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>System User (EDIT)</h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />                
                    <form action="<?php echo site_url('users/editA/' . param_encrypt($data['user_id'])); ?>" method="post" name="edit_form" id="edit_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="user_id_name" value="<?php echo $data['user_id']; ?>"/>
                        <input type="hidden" name="existing_user_type" value="<?php echo $data['account_type']; ?>"/>        

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" >User Code</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="user_id_name_display" id="user_id_name_display" value="<?php echo $data['user_id']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" >Web Access Username
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="username_display" id="username_display" value="<?php echo $data['username']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" >Web Access Password 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-10">
                                <input type="text" name="secret" id="secret" value="<?php echo $data['secret']; ?>"   class="form-control col-md-7 col-xs-12" data-parsley-minlength="8" data-parsley-maxlength="30" data-parsley-password="" autocomplete="off">
                            </div>                           
                        </div>             


                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" >User Type <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">  
                                <select name="user_type" id="user_type" class="form-control" data-parsley-required="">
                                    <option value="">Select</option>                    
                                    <?php
                                    $str = '';
                                    foreach ($user_type_array as $type => $type_name) {
                                        $selected = ' ';
                                        if ($data['user_type'] == $type)
                                            $selected = '  selected="selected" ';

                                        $str .= '<option value="' . $type . '" ' . $selected . '>' . $type_name . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" >Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="user_fullname" id="user_fullname" value="<?php echo $data['name']; ?>"  data-parsley-required="" data-parsley-minlength="3" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" >Email Address <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="user_emailaddress" id="user_emailaddress" value="<?php echo $data['emailaddress']; ?>"  data-parsley-required="" data-parsley-type="email" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" >Address </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea name="user_address" id="user_address" class="form-control col-md-7 col-xs-12"><?php echo $data['address']; ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" >Phone Number </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="user_phone" id="user_phone" value="<?php echo $data['phone']; ?>" data-parsley-type="digits" data-parsley-minlength="10" data-parsley-maxlength="12" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" >Country 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <select name="user_country_id" id="user_country_id" class="form-control">
                                    <option value="">Select</option>                    
                                    <?php
                                    $str = '';
                                    foreach ($country_options as $key => $country_array) {
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


                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-9 col-sm-6 col-xs-12">
                                <div class="radio">	
                                    <label><input type="radio" name="status_id" id="status1" value="1"  <?php if ($data['status_id'] == 1) { ?> checked="checked" <?php } ?> /> Active</label>

                                    <label> <input type="radio" name="status_id" id="status0" value="0" <?php if ($data['status_id'] == 0) { ?> checked="checked" <?php } ?> /> Inactive</label>
                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Reset Google 2FA Code</label>
                            <div class="col-md-9 col-sm-6 col-xs-12">
                                <div class="checkbox">	
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="reset_gcode" id="reset_gcode" value="1" />
                                    <span class="text-info"><small>Checking this option will reset the existing code and user will get option to scan again</small></span>
                                </div>
                            </div>
                        </div>


                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go back to Listing Page</button>
                            </div>
                        </div>



                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>System Users Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('users') ?>"><button class="btn btn-danger" type="button" >Back to System Users Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>

</div>    
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>