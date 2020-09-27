<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0.3
// License https://www.gnu.org/licenses/agpl-3.0.html
//
//
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################
-->
<!-- Parsley -->
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

<div class="">
    <div class="clearfix"></div>    
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
                    <br />
                    <?php // print_r($data);?>
                    <form action="" method="post" name="user_form" id="user_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">          

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Account ID</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="account_id_display" id="account_id_display" value="<?php echo $data['account_id']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="name" id="name" value="<?php echo $data['name']; ?>"  data-parsley-required="" data-parsley-minlength="3" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="last-name">Email Address <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="emailaddress" id="emailaddress" value="<?php echo $data['emailaddress']; ?>"  data-parsley-required="" data-parsley-type="email" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="last-name">Address </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea name="address" id="address" class="form-control col-md-7 col-xs-12"><?php echo $data['address']; ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Phone Number </label>
                            <div class="col-md-6 col-sm-6 col-xs-12"><!-- switch_user_access.phone-->
                                <input type="text" name="phone" id="phone" value="<?php echo $data['phone']; ?>" data-parsley-type="digits" data-parsley-minlength="10" data-parsley-maxlength="12" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Country 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12"><!-- switch_user_access.country-->
                                <select name="country_id" id="country_id" class="form-control">
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
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Username
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="username_display" id="username_display" value="<?php echo $data['username']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Password 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-10">
                                <input type="text" name="secret" id="secret" value="<?php echo $data['secret']; ?>"   class="form-control col-md-7 col-xs-12" data-parsley-minlength="8" data-parsley-maxlength="30" data-parsley-password="" >
                            </div>
                            <div class="col-md-1 col-sm-6 col-xs-2">
                                <!-- <button type="button" class="btn btn-primary btn_view_password" data-password-field="secret">&nbsp;<i class="fa fa-eye"></i></button>-->
                            </div>
                        </div>              

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Re-type Password
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-10">
                                <input type="text" name="repassword" id="repassword" value="" data-parsley-equalto="#secret" class="form-control col-md-7 col-xs-12" autocomplete="off">
                            </div>
                            <div class="col-md-1 col-sm-6 col-xs-2">
                            <!--<button type="button" class="btn btn-primary btn_view_password" data-password-field="repassword">&nbsp;<i class="fa fa-eye"></i></button>-->
                            </div>
                        </div>








                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <a href="<?php echo base_url() ?>users"><button class="btn btn-primary" type="button">Cancel</button></a>				
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>    
<script>

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
            })

            ;


    $('#btnSave, #btnSaveClose').click(function () {

        var is_ok = $("#user_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#user_form").submit();
        } else
        {
            $('#user_form').parsley().validate();
        }



    })


    $(document).ready(function () {
        $("#secret1").val("");

    });

</script>
