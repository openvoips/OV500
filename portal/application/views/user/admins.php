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
//echo '<pre>';
//print_r($data);
//print_r($account_type_array);
//echo '</pre>';
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>
                <?php
              
                    echo 'Admin & Accounts Users';
                ?>
            </h2>
            <ul class="nav navbar-right panel_toolbox">

                <?php if (check_account_permission('admin', 'add')): ?> <li><a href="<?php echo base_url() ?>admins/addA"><input type="button" value="Add User" name="add_link" class="btn btn-primary"></a></li><?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>admins/index/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="name" id="name" value="<?php echo $_SESSION['search_account_data']['s_name']; ?>" class="form-control data-search-field" placeholder="Account Name">
                    </div>
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account Code</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="account_id" id="account_id"  value="<?php echo $_SESSION['search_account_data']['s_account_id']; ?>" class="form-control data-search-field" placeholder="Account Code">
                    </div>
                </div>
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Web Access Username</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="username" id="username"  value="<?php echo $_SESSION['search_account_data']['s_username']; ?>" class="form-control data-search-field" placeholder="Username">
                    </div>

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <select name="status" id="status" class="form-control data-search-field">
                            <option value="">Select</option>
                            <option value="1" <?php if ($_SESSION['search_account_data']['s_status'] == '1') echo 'selected="selected"'; ?> >Active</option>
                            <option value="0" <?php if ($_SESSION['search_account_data']['s_status'] == '0') echo 'selected="selected"'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <?php if (!check_logged_account_type(array('SUPERAGENT'))): ?>
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">User Type</label>
                        <div class="col-md-4 col-sm-8 col-xs-12">
                            <select name="account_type" id="account_type" class="form-control data-search-field">
                                <option value="">Select</option>
                                <?php
                                foreach ($account_type_array as $type => $type_name) {
                                    if ($_SESSION['search_account_data']['s_account_type'] == $type)
                                        $selected = ' selected="selected" ';
                                    else
                                        $selected = '';
                                    echo '<option value="' . $type . '" ' . $selected . '>' . $type_name . '</option>';
                                }
                                ?>

                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="searchBar ">                         
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">   
                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url() . 'admins/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>                            
                            </ul>
                        </div>
                    </div>


                </div>


            </form> 


            <div class="clearfix"></div>
            <div class="ln_solid"></div>
            <div class="row">  
                <?php
                dispay_pagination_row($total_records, $_SESSION['search_account_data']['s_no_of_records'], $pagination);
                ?>    
            </div>

            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title">Account Code </th>
                            <th class="column-title">Name</th>                               
<!--                            <th class="column-title">Web Login User</th>
                            <th class="column-title">Web Login Password</th>-->
                            <th class="column-title">User Type</th>
                            <th class="column-title">Status</th>
                            <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>
                            <th class="bulk-actions" colspan="6">
                                <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
// $account_type_array = Array();
                        if (count($data['result']) > 0) {
                            foreach ($data['result'] as $accounts_data) {
                                if ($accounts_data['account_status'] == '1')
                                    $status = '<span class="label label-success">Active</span>';
                                else
                                    $status = '<span class="label label-danger">Inactive</span>';
                                $account_type = $accounts_data['account_type'];
                                $accounts_type = $account_type_array[$account_type];
                                ?>
                                <tr >                                   
                                    <td><?php echo $accounts_data['account_id']; ?></td>

                                    <td><?php echo $accounts_data['name']; ?></td>
        <!--                                    <td><?php echo $accounts_data['username']; ?></td>
                                    <td><?php echo $accounts_data['secret']; ?></td>-->
                                    <td><?php echo $accounts_type; ?></td>
                                    <td><?php echo $status; ?></td>                                   
                                    <td class=" last">
                                        <?php if (check_account_permission('admin', 'edit')): ?>
                                            <a href="<?php echo base_url(); ?>admins/editA/<?php echo param_encrypt($accounts_data['account_id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                            <?php
                                        endif;
                                        if ($accounts_type != 'ADMIN' && check_logged_account_type(array('ADMIN', 'SUBADMIN'))):
                                            ?>
                                            <!--<a href="<?php echo base_url(); ?>roles/user_permission/<?php echo param_encrypt($accounts_data['account_id']); ?>" title="Permissions"><i class="fa fa-key"></i></a>-->                                    
                                            <?php
                                        endif;

                                        if (check_account_permission('admin', 'delete') && $this->session->userdata('session_current_user_id') != $accounts_data['customer_id']) :
                                            ?>
                                            <a href="javascript:void(0);" onclick=doConfirmDelete('<?php echo $accounts_data['account_id']; ?>')  title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                                        <?php endif; ?> 

                                        <?php
                                        if (!in_array($accounts_type, array('ADMIN', 'ACCOUNTS')) && check_logged_account_type(array('ADMIN'))):
                                            ?>
                                            <a href="<?php echo base_url(); ?>autologin/<?php echo param_encrypt($accounts_data['account_id']); ?>" title="Login As User"><i class="fa fa-sign-in"></i></a>               
                                                <?php
                                            endif;
                                            ?>

                                    </td>
                                </tr>

                                <?php
                            }
                        }
                        else {
                            ?>
                            <tr>
                                <td colspan="6" align="center"><strong>No Record Found</strong></td>
                            </tr>
                            <?php
                        }
                        ?>


                    </tbody>
                </table>
            </div>                    
            <?php echo '<div class="btn-toolbar" role="toolbar"> <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">' . $pagination . '  </div></div>'; ?>

        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });

    $(document).ready(function () {
        showDatatable('table-sort', [0, 6], [2, "asc"]);
    });
</script>           