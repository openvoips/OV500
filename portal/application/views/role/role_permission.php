<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0
// License https://www.gnu.org/licenses/agpl-3.0.html
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

$permission_array = get_permission_options();
//echo "<pre>";
//print_r($permission_array);
//print_r($data);
//echo "</pre>";
?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2><?php echo ucfirst($account_type); ?> Permissions</h2>        
            <div class="clearfix"></div>
        </div>
        <div class="x_content">          

            <form action="" method="post" name="permission_form" id="permission_form" data-parsley-validate class="form-horizontal form-label-left">
                <input type="hidden" name="button_action" id="button_action" value="">
                <input type="hidden" name="action" value="OkSaveData"> 
                <input type="hidden" name="account_type" value="<?php echo $account_type; ?>"/>

                <ul class="to_do">
                    <?php
                    foreach ($permission_array as $item_name => $permission_array_single) {
                        echo '<li class="mail_list"><h3>' . ucfirst($item_name) . '</h3></li>';
                        echo '<li>';
                        foreach ($permission_array_single as $key => $permission_name) {
                            $checked = ' ';
                            if (isset($data['permissions'][$item_name]) && in_array($permission_name, $data['permissions'][$item_name])) {
                                $checked = '  checked="checked"';
                            }

                            echo '<div class="col-md-2 col-sm-2 col-xs-12"><input type="checkbox" class="' . $item_name . '" name="' . $item_name . '[]" value="' . $permission_name . '" ' . $checked . '/> ' . ucfirst($permission_name) . '</div>';
                        }
                        echo '<div class="clearfix"></div>';
                        echo '</li>';
                    }
                    ?>
                </ul>

                <div class="ln_solid"></div>
                <div class="form-group">
                    <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                        <a href="<?php echo base_url() . 'roles'; ?>"><button class="btn btn-primary" type="button">Cancel</button></a>				
                        <button type="button" id="btnSave" class="btn btn-success">Save</button>
                        <button type="button" id="btnSaveClose" class="btn btn-info">Save & Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $('#btnSave, #btnSaveClose').click(function () {

        var is_ok = true;
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#permission_form").submit();
        } else
        {
            //$('#permission_form').parsley().validate();
        }



    })
</script>