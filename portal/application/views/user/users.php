<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>System Users</h2>
            <ul class="nav navbar-right panel_toolbox">

                <?php if (check_account_permission('user', 'add')): ?> <li><a href="<?php echo base_url() ?>users/addA"><input type="button" value="Add User" name="add_link" class="btn btn-primary"></a></li><?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url('users/index'); ?>">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="name" id="name" value="<?php echo $_SESSION[$search_session_key]['name']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">User ID</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="user_id" id="user_id"  value="<?php echo $_SESSION[$search_session_key]['user_id']; ?>" class="form-control data-search-field" placeholder="USER ID">
                    </div>
                </div>
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Web Access Username</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="username" id="username"  value="<?php echo $_SESSION[$search_session_key]['username']; ?>" class="form-control data-search-field" placeholder="Username">
                    </div>

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <select name="status" id="status" class="form-control data-search-field">
                            <option value="">Select</option>
                            <option value="1" <?php if ($_SESSION[$search_session_key]['status'] == '1') echo 'selected="selected"'; ?> >Active</option>
                            <option value="0" <?php if ($_SESSION[$search_session_key]['status'] == '0') echo 'selected="selected"'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">User Type</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <select name="user_type" id="user_type" class="form-control data-search-field">
                            <option value="">Select</option>
                            <?php
                            foreach ($user_type_array as $type => $type_name) {
                                if ($_SESSION[$search_session_key]['user_type'] == $type)
                                    $selected = ' selected="selected" ';
                                else
                                    $selected = '';
                                echo '<option value="' . $type . '" ' . $selected . '>' . $type_name . '</option>';
                            }
                            ?>

                        </select>
                    </div>


                    <div class="searchBar text-right">                         
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">   
                        <div class="btn-group ">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url() . 'users/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
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
                dispay_pagination_row($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination);
                ?>   
            </div>

            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title">Name</th> 
                            <th class="column-title">ID</th>
                            <th class="column-title">User Type</th>
                            <th class="column-title">Status</th>
                            <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>                   
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (count($data['result']) > 0) {
                            foreach ($data['result'] as $accounts_data) {
                                if ($accounts_data['status_id'] == '1')
                                    $status = '<span class="label label-success">Active</span>';
                                else
                                    $status = '<span class="label label-danger">Inactive</span>';
                                $user_type = $accounts_data['user_type'];
                                $user_type_display = $user_type_array[$user_type];
                                ?>
                                <tr >                                   
                                    <td><?php echo $accounts_data['name']; ?></td>
                                    <td><?php echo $accounts_data['user_id']; ?></td>
                                    <td><?php echo $user_type_display; ?></td>
                                    <td><?php echo $status; ?></td>                                   
                                    <td class=" last">
                                        <?php if (check_account_permission('user', 'edit')): ?>
                                            <a href="<?php echo base_url(); ?>users/editA/<?php echo param_encrypt($accounts_data['user_id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                            <?php
                                        endif;


                                        if (check_account_permission('user', 'delete') && $this->session->userdata('session_current_user_id') != $accounts_data['user_id']) :
                                            ?>
                                            <a href="javascript:void(0);" onclick=doConfirmDelete('<?php echo $accounts_data['user_id']; ?>')  title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                                            <?php
                                        endif;

                                        //if (!in_array($accounts_type, array('ADMIN', 'ACCOUNTS')) ):
                                        if (check_account_permission('user', 'login') && $_SESSION['session_current_user_id'] != $accounts_data['user_id']):
                                            ?>
                                            <a href="<?php echo base_url(); ?>autologin/<?php echo param_encrypt($accounts_data['user_id']); ?>" title="Login As User"><i class="fa fa-sign-in"></i></a>               
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
            <div class="row">  
                <?php dispay_pagination_row_bottom($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination); ?>
            </div> 

        </div>
    </div>
</div>  <div class="clearfix"></div>
<script>
    $(document).ready(function () {
        showDatatable('table-sort', [4], [2, "asc"]);
    });
</script>           