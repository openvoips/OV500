
<div class="">
    <div class="clearfix"></div> 
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>System Users Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo site_url('users') ?>"><button class="btn btn-danger" type="button" >Back to System Users Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>System User (ADD)</h2>
                    <ul class="nav navbar-right panel_toolbox">                        
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">

                    <form action="<?php echo base_url(); ?>users/addA" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData"> 


                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">User Type <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">

                                <select name="user_type" id="user_type" class="form-control" data-parsley-required="">
                                    <option value="">Select</option>                    
                                    <?php
                                    $str = '';
                                    foreach ($user_type_array as $type => $type_name) {
                                        $selected = ' ';
                                        if (set_value('user_type') == $type)
                                            $selected = '  selected="selected" ';

                                        $str .= '<option value="' . $type . '" ' . $selected . '>' . $type_name . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Web Access Username <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="username" id="username" value="<?php echo set_value('username'); ?>"  data-parsley-required="" data-parsley-type="alphanum" data-parsley-minlength="6" data-parsley-maxlength="30" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Web Access Password <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-10">
                                <input type="text" name="secret" id="secret" value="<?php echo set_value('secret'); ?>" class="form-control col-md-7 col-xs-12"  data-parsley-maxlength="30" data-parsley-required="" data-parsley-password="" autocomplete="off">
                            </div>                          
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="user_fullname" id="user_fullname" value="<?php echo set_value('user_fullname'); ?>"  data-parsley-required="" data-parsley-minlength="3" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Email Address <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="user_emailaddress" id="user_emailaddress" value="<?php echo set_value('user_emailaddress'); ?>"  data-parsley-required="" data-parsley-type="email" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Address </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea name="user_address" id="user_address" class="form-control col-md-7 col-xs-12"><?php echo set_value('user_address'); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Phone Number </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="user_phone" id="user_phone" value="<?php echo set_value('user_phone'); ?>" data-parsley-type="digits" data-parsley-minlength="10" data-parsley-maxlength="12" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label  col-md-3 col-sm-3 col-xs-12" for="first-name">Country 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <select name="user_country_id" id="user_country_id" class="form-control">
                                    <option value="">Select</option>                    
                                    <?php
                                    $str = '';
                                    foreach ($country_options as $key => $country_array) {
                                        $selected = ' ';
                                        if (set_value('user_country_id') == $country_array->country_id)
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
                                    <label><input type="radio" name="status_id" id="status1" value="1"  <?php echo set_radio('status_id', '1', TRUE); ?> /> Active</label>                               
                                    <label> <input type="radio" name="status_id" id="status0" value="0" <?php echo set_radio('status_id', '0'); ?> /> Inactive</label>
                                </div>

                            </div>
                        </div>


                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <!--<a href="<?php echo base_url() ?>users"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info ">Save & Go back to Listing Page</button>
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
                <li><a href="<?php echo site_url('users') ?>"><button class="btn btn-danger" type="button" >Back to System Users Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
</div>    
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>