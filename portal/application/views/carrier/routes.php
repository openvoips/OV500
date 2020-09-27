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
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Dial Routes</h2>
            <ul class="nav navbar-right panel_toolbox">                
                <?php if (check_account_permission('routing', 'edit')): ?>
                    <li><a href="<?php echo base_url() ?>routes/addR"><input type="button" value="Add Route" name="add_link" class="btn btn-primary"></a></li>
                <?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>routes/index/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Routing Name</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <input type="text" name="name" id="name" value="<?php echo $_SESSION['search_routes_data']['s_route_name']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Routing Code</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <input type="text" name="abbr" id="abbr"  value="<?php echo $_SESSION['search_routes_data']['s_route_abbr']; ?>" class="form-control data-search-field" placeholder="Routing Code">
                    </div>		


                </div>
                <div class="form-group">             

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <select name="status" id="status" class="form-control data-search-field ">
                            <option value="">Select</option>
                            <option value="1" <?php if ($_SESSION['search_routes_data']['s_status_id'] == '1') echo 'selected="selected"'; ?> >Active</option>
                            <option value="0" <?php if ($_SESSION['search_routes_data']['s_status_id'] == '0') echo 'selected="selected"'; ?>>Inactive</option>
                        </select>
                    </div>
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
                                    echo '<li><a href="' . base_url() . 'routes/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
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
                dispay_pagination_row($total_records, $_SESSION['search_routes_data']['s_no_of_records'], $pagination);
                ?>    
            </div>
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort1">
                    <thead>
                        <tr class="headings thc">                        
                            <th class="column-title">Name</th>
                            <th class="column-title">Routing Code</th>
                            <th class="column-title">Status </th>
                            <th class="column-title no-link last"><span class="nobr">Actions</span> </th>
                            <th class="bulk-actions" colspan="5">
                                <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                            </th>
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if ($listing_count > 0) {
                            foreach ($listing_data as $listing_row) {
                                if ($listing_row['dialplan_status'] == '1')
                                    $status = '<span class="label label-success">Active</span>';
                                else
                                    $status = '<span class="label label-danger">Inactive</span>';
                                if (check_account_permission('routing', 'delete')):
                                    if ($listing_row['customer_count'] == 0)
                                        $delete_option = true;
                                    else
                                        $delete_option = false;
                                endif;
                                ?>
                                <tr>                                  
                                    <td><?php echo $listing_row['dialplan_name']; ?></td>
                                    <td><?php echo $listing_row['dialplan_id']; ?></td>
                                    <td><?php echo $status; ?></td>                                   
                                    <td class="last" >
                                        <?php if (check_account_permission('routing', 'edit')): ?>
                                            <a href="<?php echo base_url(); ?>routes/editR/<?php echo param_encrypt($listing_row['dialplan_id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                        <?php endif; ?>

                                        <?php if (check_account_permission('dialplan', 'view')): ?>
                                            <a href="javascript:void(0);" data-id="<?php echo $listing_row['dialplan_id']; ?>" title="Dialplan View" class="dialplanlist"><i class="fa fa-list"></i></a>                                            
                                        <?php endif; ?>


                                        <?php if ($delete_option) { ?>
                                            <a href="javascript:void(0);"  onclick=doConfirmDelete('<?php echo $listing_row['dialplan_id']; ?>') title="Delete" class="text-danger"><i class="fa fa-trash"></i></a>
                                        <?php } else { ?>
                                            <a href="javascript:void(0);" onclick="new PNotify({
                                                        title: 'Data deletion',
                                                        text: 'You can not delete dialplan as it is used for Customer.',
                                                        type: 'info',
                                                        styling: 'bootstrap3',
                                                        addclass: 'dark'
                                                    });" title="Delete" class="text-dark"><i class="fa fa-trash"></i><a/>
                                            <?php } ?>	                                         
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" align="center"><strong>No Record Found</strong></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>                    
            <?php echo '<div class="btn-toolbar" role="toolbar"><div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">' . $pagination . '  </div></div>'; ?>

            <?php
            $attributes = array('name' => 'view_frm', 'id' => 'view_frm');
            echo form_open('dialplans/index/', $attributes);
            echo form_input(array('name' => 'search_action', 'type' => 'hidden', 'id' => 'search_action', 'value' => 'search'));
            echo form_input(array('name' => 'abbr', 'type' => 'hidden', 'id' => 'abbr'));
            echo form_input(array('name' => 'status', 'type' => 'hidden', 'id' => 'status'));
            echo form_input(array('name' => 'carrier', 'type' => 'hidden', 'id' => 'carrier'));
            echo form_input(array('name' => 'prefix', 'type' => 'hidden', 'id' => 'prefix'));
            echo form_input(array('name' => 'no_of_rows', 'type' => 'hidden', 'id' => 'no_of_rows', 'value' => '10'));
            echo form_input(array('name' => 'OkFilter', 'type' => 'hidden', 'id' => 'OkFilter', 'value' => 'Search'));
            echo form_close();
            ?>

        </div>
    </div>
</div>
<script language="javascript" type="text/javascript">
    $('.dialplanlist').click(function () {
        $('#view_frm #abbr').val($(this).data('id'));
        $('#view_frm').submit();
    })
</script>
<script>
    $(document).ready(function () {
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });

    $(document).ready(function () {
        showDatatable('table-sort1', [3], [1, "asc"]);
    });
</script> 