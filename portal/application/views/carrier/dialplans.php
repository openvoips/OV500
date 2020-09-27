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
<?php
// echo '<pre>';
//print_r($listing_data); 
//print_r($route_data);
//echo '</pre>';
?>
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script>
    $(document).ready(function () {
        $('.combobox').combobox()
    });
</script>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Dialplans</h2>
            <ul class="nav navbar-right panel_toolbox">

                <?php if (check_account_permission('dialplan', 'edit')): ?>
                    <li><a href="<?php echo base_url() ?>dialplans/addD"><input type="button" value="Add Dialplan" name="add_link" class="btn btn-primary"></a></li>
                <?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>dialplans/index/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Dial Prefix</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <input type="text" name="prefix" id="prefix" value="<?php echo $_SESSION['search_dialplan_data']['s_prefix']; ?>" class="form-control data-search-field" placeholder="Prefix">
                    </div>
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Carrier / Termination</label>
                    <div class="col-md-4 col-sm-9 col-xs-12 ">
                        <select name="carrier" id="carrier" class="form-control data-search-field combobox">
                            <option value="">Select Carrier</option>
                            <?php foreach ($carrier_data as $k => $v) { ?>							
                                <option value="<?php echo $carrier_data[$k]['carrier_id']; ?>" <?php if ($_SESSION['search_dialplan_data']['s_carrier'] == $carrier_data[$k]['carrier_id']) echo 'selected'; ?>><?php echo $carrier_data[$k]['carrier_name'] . ' [' . $carrier_data[$k]['carrier_id'] . ']'; ?></option>
                            <?php } ?>
                        </select>
                    </div>				  

                </div>

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Route Name</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <select name="abbr" id="abbr" class="form-control data-search-field combobox">
                            <option value="">Select Route</option>
                            <?php for ($i = 0; $i < count($route_data); $i++) { ?>								
                                <option value="<?php echo $route_data[$i]['dialplan_id']; ?>" <?php if ($_SESSION['search_dialplan_data']['s_route_abbr'] == $route_data[$i]['dialplan_id']) echo 'selected'; ?>><?php echo $route_data[$i]['dialplan_name'] . ' (' . $route_data[$i]['dialplan_id'] . ')'; ?></option>
                            <?php } ?>
                        </select>
                    </div>	
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Dialplan Status</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <select name="status" id="status" class="form-control data-search-field">
                            <option value="">ALL</option>
                            <option value="1" <?php if ($_SESSION['search_dialplan_data']['s_status'] == '1') echo 'selected'; ?>>Active</option>
                            <option value="0" <?php if ($_SESSION['search_dialplan_data']['s_status'] == '0') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>	
                </div>	
                <div class="form-group">
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
                                    echo '<li><a href="' . base_url() . 'dialplans/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>   						
                            </ul>
                        </div>
                    </div>
                </div>
            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>
            <?php if ($searching): ?>

                <div class="row">  
                    <?php
                    dispay_pagination_row($total_records, $_SESSION['search_dialplan_data']['s_no_of_records'], $pagination);
                    ?>    
                </div>
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                        <thead>
                            <tr class="headings thc">                              
                                <th class="column-title">Prefix</th>
                                <th class="column-title">Route</th>
                                <th class="column-title">Carrier</th>
                                <th class="column-title">Priority</th>
                                <th class="column-title">Start Day [Start Time]</th>
                                <th class="column-title">End Day [End Time]</th>
                                <th class="column-title">Load</th>
                                <th class="column-title">Status </th>
                                <th class="column-title no-link last"><span class="nobr">Actions</span> </th>
                                <th class="bulk-actions" colspan="12">
                                    <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                                </th>
                            </tr>
                        </thead>		
                        <tbody>
                            <?php
                            if ($listing_count > 0) {
                                $w = unserialize(DAY_FROM_WEEK);
                                foreach ($listing_data as $listing_row) {

                                    if ($listing_row['route_status'] == '1')
                                        $status = '<span class="label label-success">Active</span>';
                                    else
                                        $status = '<span class="label label-danger">Inactive</span>';
                                    ?>
                                    <tr>
                                        <td><?php echo $listing_row['dial_prefix']; ?></td>
                                        <td><?php echo $listing_row['dialplan_id']; ?></td>
                                        <td><a href="<?php echo base_url(); ?>carriers/edit/<?php echo param_encrypt($listing_row['carrier_id']); ?>"><u><?php echo $listing_row['carrier_id']; ?></u></a></td>
                                        <td><?php echo $listing_row['priority']; ?></td>
                                        <td><?php echo $w[$listing_row['start_day']] . " [" . $listing_row['start_time'] . "]"; ?></td>
                                        <td><?php echo $w[$listing_row['end_day']] . " [" . $listing_row['end_time'] . "]"; ?></td>
                                        <td><?php echo $listing_row['load_share']; ?></td>
                                        <td><?php echo $status; ?></td>                                   
                                        <td class=" last">
                                            <?php if (check_account_permission('dialplan', 'edit')): ?>
                                                <a href="<?php echo base_url(); ?>dialplans/editD/<?php echo param_encrypt($listing_row['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                            <?php endif; ?>
                                            <?php if (check_account_permission('dialplan', 'delete')): ?>
                                                <a href="javascript:doConfirmDelete('<?php echo param_encrypt($listing_row['id']); ?>','dialplans');" title="Delete" class="delete"><i class="fa fa-trash"></i></a>	
                                                <?php endif; ?>					 
                                        </td>
                                    </tr>

                                    <?php
                                }
                            }
                            else {
                                ?>
                                <tr>
                                    <td colspan="12" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>                    
                <?php echo '<div class="btn-toolbar" role="toolbar">
				  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
						   ' . $pagination . '
				  </div>
				</div>'; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        showDatatable('table-sort', [7], [1, "asc"]);
    });
    $(document).ready(function () {
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });

</script>  
