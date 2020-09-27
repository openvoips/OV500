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
//echo '<pre>';
//print_r($data);
//echo '</pre>';
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Carriers Management</h2>
            <ul class="nav navbar-right panel_toolbox">

                <?php if (check_account_permission('carrier', 'add')): ?>  <li><a href="<?php echo base_url() ?>carriers/addC"><input type="button" value="Add Carrier" name="add_link" class="btn btn-primary"></a></li> <?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>carriers/index/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Carrier Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="carrier_name" id="carrier_name" value="<?php echo $_SESSION['search_carrier_data']['s_carrier_name']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Carrier Code</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="carrier_id" id="carrier_id_name"  value="<?php echo $_SESSION['search_carrier_data']['s_carrier_id']; ?>" class="form-control data-search-field" placeholder="Account Code">
                    </div>

                </div>

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Carrier IP</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="gateway_ipaddress" id="gateway_ipaddress"  value="<?php echo $_SESSION['search_carrier_data']['s_gateway_ipaddress']; ?>" class="form-control data-search-field" placeholder="Carrier IP">
                    </div>

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">SIP Username</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="gateway_username" id="gateway_username"  value="<?php echo $_SESSION['search_carrier_data']['s_gateway_username']; ?>" class="form-control data-search-field" placeholder="SIP Username">
                    </div>
                </div>

                <div class="form-group">


                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <select name="status" id="status" class="form-control data-search-field">
                            <option value="">Select</option>
                            <option value="1" <?php if ($_SESSION['search_carrier_data']['s_status'] == '1') echo 'selected="selected"'; ?> >Active</option>
                            <option value="0" <?php if ($_SESSION['search_carrier_data']['s_status'] == '0') echo 'selected="selected"'; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="searchBar ">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary ">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info ">


                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url() . 'carriers/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>                            
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="clearfix"></div>
        <div class="ln_solid"></div>
        <div class="row">  
            <?php
            dispay_pagination_row($total_records, $_SESSION['search_carrier_data']['s_no_of_records'], $pagination);
            ?>    
        </div>
        <div class="table-responsive">
            <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                <thead>
                    <tr class="headings thc">                     
                        <th class="column-title">Carrier </th>      
                        <th class="column-title" style="width: 150px">Type</th>    
                        <th class="column-title">Currency</th>
                        <th class="column-title">Tariff Name</th>

                        <th class="column-title">DP</th>
                        <th class="column-title">PT / RT</th>
                        <th class="column-title">CLI Prefer</th>
                        <th class="column-title">CPS / CC</th>
                        <th class="column-title">Status </th>
                        <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>
                        <th class="bulk-actions" colspan="7">
                            <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (count($data['result']) > 0) {
                        foreach ($data['result'] as $carrier_data) {
                            if ($carrier_data['carrier_status'] == '1')
                                $status = '<span class="label label-success">Active</span>';
                            else if ($carrier_data['carrier_status'] == '-1')
                                $status = '<span class="label label-danger">Inactive</span>';
                            else
                                $status = '<span class="label label-danger">Closed</span>';
                            $tariff_name = isset($carrier_data['tariff']['tariff_name']) ? $carrier_data['tariff']['tariff_name'] : '';
                            ?>
                            <tr >                                
                                <td class=" "><?php echo $carrier_data['carrier_name'] . " (" . $carrier_data['carrier_id'] . ")"; ?></td>


                                <td> <?php
                                    if ($carrier_data['carrier_type'] == 'INBOUND')
                                        echo 'DID Provider';
                                    else
                                        echo 'VoIP Minute Provider';
                                    ?></td>
                                <td class=" "><?php echo $carrier_data['symbol'] . " - " . $carrier_data['cname']; ?></td>
                                <td class=" "><?php echo $tariff_name; ?></td>
                                <td class=" "><?php echo $carrier_data['dp']; ?></td>
                                <td class=" "><?php echo $carrier_data['carrier_progress_timeout'] . " / " . $carrier_data['carrier_ring_timeout']; ?></td>
                                <td class=" "><?php echo $carrier_data['cli_prefer']; ?></td>
                                <td class=" "><?php echo $carrier_data['carrier_cps'] . " / " . $carrier_data['carrier_cc']; ?></td>

                                                                                                <td class=" "><?php echo $status; ?><!-- <i class="success fa fa-long-arrow-up"></i>--></td>                                   
                                <td class=" last"> 
                                    <?php if (check_account_permission('carrier', 'edit')): ?>
                                        <a href="<?php echo base_url(); ?>carriers/edit/<?php echo param_encrypt($carrier_data['carrier_id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>       
                                        <?php
                                    endif;
                                    if (check_account_permission('carrier', 'delete')):
                                        ?>
                                        <a href="javascript:void(0);" onclick=doConfirmDelete('<?php echo $carrier_data['carrier_id']; ?>') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                                        <?php endif; ?>
                                </td>
                            </tr>

                            <?php
                        }
                    }
                    else {
                        ?>
                        <tr>
                            <td colspan="8" align="center"><strong>No Record Found</strong></td>
                        </tr>
                        <?php
                    }
                    ?>


                </tbody>
            </table>
        </div>             
        <?php echo '<div class="btn-toolbar" role="toolbar"><div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
									   ' . $pagination . '
							  </div>
							</div>'; ?>   

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
        showDatatable('table-sort', [0, 7], [2, "asc"]);
    });
</script>              