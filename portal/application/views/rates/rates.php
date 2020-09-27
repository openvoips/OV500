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
//print_r($tariff_data); print_r($ratecard_data); 
//echo "total_records $total_records"; echo '</pre>';
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
            <h2>Rates Management</h2>
            <ul class="nav navbar-right panel_toolbox">
                <?php if (check_account_permission('rate', 'add')): ?>
                    <li><a href="<?php echo base_url() ?>rates/addR"><input type="button" value="Add Rate" name="add_link" class="btn btn-primary"></a></li>
                <?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>rates/index/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div style="color: red;">*One item must be select from the Tariff or Ratecard.</div>
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Tariff Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <select name="tariff" id="tariff" class="combobox form-control data-search-field">
                            <option value="">Select Tariff</option>
                            <?php for ($i = 0; $i < $tariff_data['total']; $i++) { ?>
                                <option value="<?php echo $tariff_data['result'][$i]['tariff_id']; ?>" <?php if ($_SESSION['search_rate_data']['s_tariff'] == $tariff_data['result'][$i]['tariff_id']) echo 'selected'; ?>><?php echo $tariff_data['result'][$i]['tariff_name'] . ' (' . $tariff_data['result'][$i]['currency_name'] . ')' . ' [' . $tariff_data['result'][$i]['tariff_id'] . ']'; ?></option>
                            <?php } ?>
                        </select>
                    </div>	
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Ratecard Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <select name="card" id="card" class="combobox form-control data-search-field">
                            <option value="">Select Rate</option>
                            <?php
                            for ($i = 0; $i < $ratecard_data['total']; $i++) {
                                ?>								
                                <option value="<?php echo $ratecard_data['result'][$i]['ratecard_id']; ?>" <?php if ($_SESSION['search_rate_data']['s_rate_card'] == $ratecard_data['result'][$i]['ratecard_id']) echo 'selected'; ?>><?php echo $ratecard_data['result'][$i]['ratecard_for'] . "::" . $ratecard_data['result'][$i]['ratecard_name'] . ' (' . $ratecard_data['result'][$i]['currency_name'] . ') [' . $ratecard_data['result'][$i]['ratecard_id'] . '] '; ?></option>
                            <?php } ?>
                        </select>
                    </div>	  

                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Rates Prefix</label>
                        <div class="col-md-4 col-sm-8 col-xs-12">
                            <input type="text" name="prefix" id="prefix" value="<?php echo $_SESSION['search_rate_data']['s_rate_prefix']; ?>" class="form-control data-search-field" placeholder="Prefix">
                        </div>
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Destination</label>
                        <div class="col-md-4 col-sm-8 col-xs-12">
                            <input type="text" name="dest" id="dest" value="<?php echo $_SESSION['search_rate_data']['s_rate_destination']; ?>" class="form-control data-search-field" placeholder="Destination">
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Rate Status</label>
                        <div class="col-md-4 col-sm-8 col-xs-12">
                            <select name="status" id="status" class="form-control data-search-field">
                                <option value="">ALL</option>
                                <option value="1" <?php if ($_SESSION['search_rate_data']['s_status'] == '1') echo 'selected'; ?>>Active</option>
                                <option value="0" <?php if ($_SESSION['search_rate_data']['s_status'] == '0') echo 'selected'; ?>>Inactive</option>
                            </select>
                        </div>	

                        <div class="searchBar ">
                            <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                            <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                           
                            <div class="btn-group">
                                <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                    Export <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('csv'); ?>">CSV</a></li>
                                    <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('xlsx'); ?>">XLSX</a></li>
                                    <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('xls'); ?>">XLS</a></li>
                                    <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('txt'); ?>">TXT</a></li>
                                    <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('pdf'); ?>">PDF</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>           
            <div class="row">  
                <?php
                dispay_pagination_row($total_records, $_SESSION['search_rate_data']['s_no_of_records'], $pagination);
                ?>    
            </div>

            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">                        
                            <th class="column-title">Ratecard</th>
                            <th class="column-title">Prefix</th>
                            <th class="column-title">Destination</th>
                            <th class="column-title">Rate per Minute</th>
                            <th class="column-title">Charge per Connection</th>
                            <th class="column-title">Grace Period</th>
                            <th class="column-title">Status </th>
                            <th class="column-title no-link last"><span class="nobr">Actions</span> </th>
                            <th class="bulk-actions" colspan="8">
                                <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                            </th>
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if ($listing_count > 0) {
                            foreach ($listing_data as $listing_row) {

                                if ($listing_row['rates_status'] == '1')
                                    $status = '<span class="label label-success">Active</span>';
                                else
                                    $status = '<span class="label label-danger">Inactive</span>';
                                ?>
                                <tr>                                   
                                    <td><?php echo $listing_row['card_name'] . ' [' . $listing_row['ratecard_id'] . ']'; ?></td>
                                    <td ><?php echo $listing_row['prefix']; ?></td>
                                    <td><?php echo $listing_row['destination']; ?></td>
                                    <td ><?php echo number_format($listing_row['rate'], 6, '.', ''); ?></td>
                                    <td ><?php echo number_format($listing_row['connection_charge'], 6, '.', ''); ?></td>
                                    <td ><?php echo number_format($listing_row['grace_period'], 6, '.', ''); ?></td>                                      
                                    <td ><?php echo $status; ?></td>                                   
                                    <td class=" last" >

                                        <?php if (check_account_permission('rate', 'edit')): ?>
                                            <a href="<?php echo base_url(); ?>rates/editR/<?php echo param_encrypt($listing_row['rate_id'] . '@' . $listing_row['ratecard_id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                        <?php endif; ?>

                                        <?php if (check_account_permission('rate', 'delete')): ?>
                                            <a href="javascript:doConfirmDelete('<?php echo param_encrypt($listing_row['rate_id'] . '@' . $listing_row['ratecard_id'], 'rates'); ?>');" title="Delete" class="delete"><i class="fa fa-trash"></i></a>		
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
            <?php echo '<div class="btn-toolbar" role="toolbar">
				  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
						   ' . $pagination . '
				  </div>
				</div>'; ?>
            <?php //endif; ?>
        </div>
    </div>
</div>
</div>
<script>
<?php
$options = '';
for ($i = 0; $i < $ratecard_dropdown['total']; $i++)
    $options .= '<option value="' . $ratecard_dropdown['result'][$i]['ratecard_id'] . '">' . $ratecard_dropdown['result'][$i]['ratecard_name'] . '</option>';
?>
    $('#tariff').change(function () {
        if ($('#tariff').val() != '')
            $.ajax({
                url: '<?php echo base_url(); ?>tariffs/apiTM/' + $('#tariff').val(),
                async: true,
                cache: false,
                success: function (data) {
                    $("#card").empty();
                    if (data.total > 0)
                    {
                        $.each(data.result, function (key, value) {
                            console.log(value);
                            $('#card')
                                    .append($("<option></option>")
                                            .attr("value", value.ratecard_id)
                                            .text(value.ratecard_name + ' (' + value.currency_name + ') [' + value.ratecard_id + ']'));
                        });
                    } else
                        $('#card')
                                .append($("<option></option>")
                                        .attr("value", '')
                                        .text('No Ratecard Found'));
                }
            });
        else {
            $("#card").empty();
            $("#card").append('<?php echo $options; ?>');
        }
    });

    $(document).ready(function () {
   showDatatable('table-sort', [5], [1, "asc"]);
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
</script>
