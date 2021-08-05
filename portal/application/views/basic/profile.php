<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Profile</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">                   
                <form action="" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="button_action" id="button_action" value="">
                    <input type="hidden" name="action" value="OkSaveData">          

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="account_id_display" id="account_id_display" value="<?php echo $data['account_id']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">User ID</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="uuu" id="uuu" value="<?php echo $data['user_id']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Name <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="user_fullname" id="user_fullname" value="<?php echo $data['name']; ?>"  data-parsley-required="" data-parsley-minlength="3" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12" for="last-name">Email Address <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="user_emailaddress" id="user_emailaddress" value="<?php echo $data['emailaddress']; ?>"  data-parsley-required="" data-parsley-type="email" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12" for="last-name">Address </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <textarea name="user_address" id="user_address" class="form-control col-md-7 col-xs-12"><?php echo $data['address']; ?></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Phone Number </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="user_phone" id="user_phone" value="<?php echo $data['phone']; ?>" data-parsley-type="digits" data-parsley-minlength="10" data-parsley-maxlength="12" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Country 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <select name="user_country_id" id="user_country_id" class="form-control">
                                <option value="">select</option>                    
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
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Password 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-10">
                            <input type="text" name="secret" id="secret" value=""   class="form-control col-md-7 col-xs-12" data-parsley-minlength="8" data-parsley-maxlength="30" data-parsley-password="" >
                        </div>
                    </div>              




                    <div class="ln_solid"></div>
                    <div class="form-group text-right">
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <button type="button" id="btnSave" class="btn btn-success">Save</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>
<script>
    $(document).ready(function () {
        $("#repassword").val("");
    });
</script>
