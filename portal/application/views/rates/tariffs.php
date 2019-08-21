<!--
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
-->
<?php
//echo '<pre>';
//print_r($listing_data);
//echo '</pre>';
$delete_option='';
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Tariff Management</h2>
            <ul class="nav navbar-right panel_toolbox">

                <?php if (check_account_permission('tariff', 'add')): ?>
                    <li><a href="<?php echo base_url() ?>tariffs/addTP"><input type="button" value="Add Tariff" name="add_link" class="btn btn-primary"></a></li>
                <?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>tariffs/index/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Tariff Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="name" id="name" value="<?php echo $_SESSION['search_tariff_data']['s_tariff_name']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Tariff Code</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="tariff_id" id="tariff_id" value="<?php echo $_SESSION['search_tariff_data']['s_tariff_id']; ?>" class="form-control data-search-field" placeholder="Tariff Code">
                    </div>
                </div>
                <div class="form-group">
                    <?php if (!check_logged_account_type(array('RESELLER'))) : ?>
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Currency</label>
                        <div class="col-md-4 col-sm-8 col-xs-12">
                            <select name="currency" id="currency" class="form-control data-search-field">
                                <option value="">Select Currency</option>
                                <?php for ($i = 0; $i < count($currency_data); $i++) { ?>								

                                    <?php if (get_logged_account_level() == 0): ?>	
                                        <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if ($_SESSION['search_tariff_data']['s_tariff_currency'] == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name'] . ' (' . $currency_data[$i]['symbol'] . ')'; ?></option>
                                    <?php elseif (get_logged_account_level() != 0 && get_logged_account_currency() == $currency_data[$i]['currency_id']): ?>
                                        <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if ($_SESSION['search_tariff_data']['s_tariff_currency'] == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name'] . ' (' . $currency_data[$i]['symbol'] . ')'; ?></option>
                                    <?php endif; ?>

                                <?php } ?>
                            </select>
                        </div>		

                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Who can Use</label>
                        <div class="col-md-4 col-sm-8 col-xs-12">
                            <select name="type" id="type" class="form-control data-search-field">
                                <option value="">ALL</option>
                                <option value="CARRIER" <?php if ($_SESSION['search_tariff_data']['s_tariff_type'] == 'CARRIER') echo 'selected'; ?>>Carrier</option>
                                <option value="CUSTOMER" <?php if ($_SESSION['search_tariff_data']['s_tariff_type'] == 'CUSTOMER') echo 'selected'; ?>>Customer</option>
                            </select>
                        </div>	
                    <?php endif; ?>
                </div>
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <select name="status" id="status" class="form-control data-search-field">
                            <option value="">ALL</option>
                            <option value="1" <?php if ($_SESSION['search_tariff_data']['s_tariff_status'] == '1') echo 'selected'; ?>>Active</option>
                            <option value="0" <?php if ($_SESSION['search_tariff_data']['s_tariff_status'] == '0') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>				  
                    <div class="searchBar ">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                           
                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="<?php echo base_url() ?>tariffs/index/export/<?php echo param_encrypt('csv'); ?>">CSV</a></li>
                                <li><a href="<?php echo base_url() ?>tariffs/index/export/<?php echo param_encrypt('xlsx'); ?>">XLSX</a></li>
                                <li><a href="<?php echo base_url() ?>tariffs/index/export/<?php echo param_encrypt('xls'); ?>">XLS</a></li>
                                <li><a href="<?php echo base_url() ?>tariffs/index/export/<?php echo param_encrypt('txt'); ?>">TXT</a></li>
                                <li><a href="<?php echo base_url() ?>tariffs/index/export/<?php echo param_encrypt('pdf'); ?>">PDF</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>         
            <div class="row">  
                <?php
                dispay_pagination_row($total_records, $_SESSION['search_tariff_data']['s_no_of_records'], $pagination);
                ?>    
            </div>
            <div class="table-responsive">
                 <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">                          
                            <th class="column-title">Tariff Name</th>                            
                            <th class="column-title">Currency</th>
                            <th class="column-title">Tariff Charge</th>
                            <th class="column-title">Who can Use</th>  
                            <th class="column-title">Open Routes & Package</th>



                            <th class="column-title">Status </th>
                            <th class="column-title no-link last"><span class="nobr">Actions</span> </th>
                            <th class="bulk-actions" colspan="6">
                                <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                            </th>
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if ($listing_count > 0) {
                            foreach ($listing_data as $listing_row) {

                                if ($listing_row['tariff_status'] == '1')
                                    $status = '<span class="label label-success">Active</span>';
                                else
                                    $status = '<span class="label label-danger">Inactive</span>';
                                ?>
                                <tr>                                    
                                    <td><?php echo $listing_row['tariff_name'] . " (" . $listing_row['tariff_id'] . ")"; ?></td>
                                    <td ><?php echo $listing_row['currency_symbol'] . " - " . $listing_row['currency_name']; ?></td>
                                    <td ><?php echo $listing_row['monthly_charges']; ?></td>

                                    <?php
                                    if ($listing_row['ratecard_count'] > 0)
                                        $outrates = '<span class="label label-success">PSTN</span>';
                                    else
                                        $outrates = '<span class="label label-danger">PSTN</span>';

                                    if ($listing_row['ratecard_in_count'] > 0)
                                        $inrates = '<span class="label label-success">DID</span>';
                                    else
                                        $inrates = '<span class="label label-danger">DID</span>';

                                    if ($listing_row['package_option'] == '1')
                                        $package_option = '<span class="label label-success">PACKAGE</span>';
                                    else
                                        $package_option = '<span class="label label-danger">PACKAGE</span>';

                                    if ($listing_row['bundle_option'] == '1')
                                        $bundle_option = '<span class="label label-success">BUNDLE</span>';
                                    else
                                        $bundle_option = '<span class="label label-danger">BUNDLE</span>';
                                    ?>

                                    <td ><?php echo $listing_row['tariff_type']; ?></td>                               
                                    <td ><?php
                                        echo $outrates . " " . $inrates . " " . $package_option . " " . $bundle_option;
                                        $inrates = '';
                                        $outrates = '';
                                        $package_option = '';
                                        $bundle_option = '';
                                        ?> </td>       

                                    <td ><?php echo $status; ?></td>                                   
                                    <td class="last" >
                                        <?php if (check_account_permission('tariff', 'edit')): ?>
                                            <a href="<?php echo base_url(); ?>tariffs/editTP/<?php echo param_encrypt($listing_row['tariff_id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                        <?php endif; ?>	

                                        <?php if (check_account_permission('rate', 'view')): ?>
                                            <a href="javascript:void(0);" data-id="<?php echo $listing_row['tariff_id']; ?>" title="View Rates" class="rates"><i class="fa fa-list"></i></a>
                                        <?php endif; ?>	

                                        <?php //if(check_account_permission('tariff','delete')):   ?>
                                        <?php if ($delete_option) { ?>
                                            <a href="javascript:doConfirmDelete('<?php echo param_encrypt($listing_row['tariff_id']); ?>','tariffs');" title="Delete" class="delete"><i class="fa fa-trash"></i></a>						
                                        <?php } else { ?>
                                            <a href="javascript:void(0);" onclick="new PNotify({
                                                                    title: 'Data deletion',
                                                                    text: 'You can not delete tariff as it is attached with [Ratecard, Carrier, User].',
                                                                    type: 'info',
                                                                    styling: 'bootstrap3',
                                                                    addclass: 'dark'
                                                                });" title="Delete" class="text-dark"><i class="fa fa-trash"></i><a/>
                                            <?php } ?>
                                            <?php //endif; ?>	

                                    </td>
                                </tr>

                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7" align="center"><strong>No Record Found</strong></td>
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

            <?php
            $attributes = array('name' => 'view_frm', 'id' => 'view_frm');
            echo form_open('rates/index/', $attributes);
            echo form_input(array('name' => 'search_action', 'type' => 'hidden', 'id' => 'search_action', 'value' => 'search'));
            echo form_input(array('name' => 'card', 'type' => 'hidden', 'id' => 'card'));
            echo form_input(array('name' => 'tariff', 'type' => 'hidden', 'id' => 'tariff'));
            echo form_input(array('name' => 'prefix', 'type' => 'hidden', 'id' => 'prefix'));
            echo form_input(array('name' => 'dest', 'type' => 'hidden', 'id' => 'dest'));
            echo form_input(array('name' => 'OkFilter', 'type' => 'hidden', 'id' => 'OkFilter', 'value' => 'Search'));
            echo form_close();
            ?>

        </div>
    </div>
</div>
<script language="javascript" type="text/javascript">
    $(document).ready(function () {
         showDatatable('table-sort', [6], [1, "asc"]);
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
    $('.rates').click(function () {
        $('#view_frm #tariff').val($(this).data('id'));
        $('#view_frm').submit();
    });
</script>
