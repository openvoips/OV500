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

if (isset($account_result)) {
    $account_permission_array = $account_result['permission'];
    $is_account_details_exists = true;
//    print_r($roles_data);
//    print_r($account_result);
} else {
    $is_account_details_exists = false;
}
?>

<div class="col-md-8 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Customer Details</h2>
            <ul class="nav navbar-right panel_toolbox">
                
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">
            <?php if ($is_account_details_exists): ?>
                <table class="table jambo_table table-bordered ">
                    <tbody>
                        <tr><td><strong>Account Code</strong></td><td><?php echo $account_result['account_id']; ?> </td></tr>
                        <tr><td><strong>Name</strong></td><td><?php echo $account_result['name']; ?></td></tr>
                        <tr><td><strong>User Type</strong></td><td><?php echo $account_result['account_type']; ?></td></tr>        
                    </tbody>
                </table>
            <?php else: ?>  
                <script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
                <form action="" method="post" name="search_form" id="search_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="action" value="OkSearchData"> 
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="Account ID">Account ID</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="search_account_id" id="search_account_id" value="<?php echo set_value('search_account_id') ?>"  class="form-control col-md-7 col-xs-12" data-parsley-required="">
                        </div>
                    </div>             
                    <div class="form-group">
                        <div class="col-md-12 col-sm-6 col-xs-12 col-md-offset-4">			
                            <button type="button" id="btnSearch" class="btn btn-success">Search</button>
                        </div>
                    </div>


                </form>


            <?php endif; ?>   
        </div>

    </div>    
</div>




<?php if ($is_account_details_exists): ?>
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Permissions </h2>        
                <div class="clearfix"></div>
            </div>
            <div class="x_content">          


                <?php
                if (isset($roles_data['permissions'])) {
                    ?>


                    <form action="" method="post" name="permission_form" id="permission_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="account_id" value="<?php echo $account_result['account_id']; ?>"/>



                        <ul class="to_do">
                            <?php
                            foreach ($roles_data['permissions'] as $item_name => $permission_array_single) {
                                echo '<li class="mail_list"><h3>' . ucfirst($item_name) . '</h3></li>';
                                echo '<li>';

                                $permission_column_array = array();

                                $empty_block = '<div class="col-md-2 col-sm-2 col-xs-12">&nbsp;</div>';
                                $permission_column_array = array('view' => $empty_block, 'add' => $empty_block, 'edit' => $empty_block, 'delete' => $empty_block);

                                foreach ($permission_array_single as $key => $permission_name) {
                                    $checked = ' ';
                                    if (isset($account_permission_array[$item_name]) && in_array($permission_name, $account_permission_array[$item_name])) {
                                        $checked = '  checked="checked"';
                                    }


                                    $str = '<div class="col-md-2 col-sm-2 col-xs-12"><input type="checkbox" class="' . $item_name . '" name="' . $item_name . '[]" value="' . $permission_name . '" ' . $checked . '/> ' . ucfirst($permission_name) . '</div>';
                                    $permission_column_array[$permission_name] = $str;
                                }
                                $permission_string = implode('', $permission_column_array);
                                echo $permission_string;
                                echo '<div class="clearfix"></div>';
                                echo '</li>';
                            }
                            ?>
                        </ul>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                <a href="<?php echo base_url() . 'roles/account_permission'; ?>"><button class="btn btn-primary" type="button">Cancel</button></a>				
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                            </div>
                        </div>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
<?php endif; ?>  
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
    });

    /*search form*/
    $('#btnSearch').click(function () {
        $('#search_form').parsley().reset();
        var is_ok = $("#search_form").parsley().isValid();
        if (is_ok === true)
        {
            if (is_ok === true)
            {
                $("#search_form").submit();
            }
        } else
        {
            $('#search_form').parsley().validate();
        }

    })




</script>              
