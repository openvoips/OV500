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
//print_r($pagination);
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
            <h2>Currency Exchange Rate</h2>
            <ul class="nav navbar-right panel_toolbox">
                
                <li><a href="<?php echo base_url() ?>currency/ExcRate"><input type="button" value="Add Exchange Rate" name="add_link" class="btn btn-primary"></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>currency/index/">
                <input type="hidden" name="search_action" value="search" />

                <div class="form-group">

                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Currency</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <select name="currency" id="currency" class="form-control data-search-field combobox">
                            <option value="">Select Currency</option>
                            <?php
                            for ($i = 0; $i < $currency_dropdown['total']; $i++) {
                                ?>
                                <option value="<?php echo $currency_dropdown['result'][$i]['currency_id']; ?>" <?php if ($_SESSION['search_currency_data']['s_currency_id'] == $currency_dropdown['result'][$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_dropdown['result'][$i]['detail_name'] . ' (' . $currency_dropdown['result'][$i]['name'] . '  ' . $currency_dropdown['result'][$i]['symbol'] . ')'; ?></option>
                            <?php } ?>                         
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
            <?php if ($searching): ?>
                <?php echo '<div class = "btn-toolbar" role = "toolbar">  <div class = "btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right"> ' . $pagination . ' </div> </div>'; ?>
                Note: The Base Currency is USD.
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action table-bordered">
                        <thead>

                            <tr class="headings thc">                               
                                <th class="column-title">Currency</th>                               
                                <th class="column-title">Exchange Rate</th>
                                <th class="column-title">Date</th>
                            </tr>
                        </thead>		
                        <tbody>
                            <?php
                            if ($listing_count > 0) {
                                foreach ($listing_data as $listing_row) {
                                    ?>

                                    <tr>                                        
                                        <td width="100"><?php echo $listing_row['currency_symbol'] . " - " . $listing_row['detail_name'] . " (" . $listing_row['currency_name'] . ")"; ?></td> 
                                        <td width="100"><?php echo $listing_row['ratio']; ?></td> 
                                        <td width="100"><?php echo $listing_row['date']; ?></td> 
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="3" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>                    
                <?php echo '<div class="btn-toolbar" role="toolbar"> <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">' . $pagination . '</div></div>'; ?>
            <?php endif; ?>
        </div>
    </div>
</div>