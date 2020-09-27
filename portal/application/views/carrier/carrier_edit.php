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
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script>
    $(document).ready(function () {
        $('.combobox').combobox();
    });
</script>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$tab_index = 1;
$dp = 4;
$vatflag_array = array('NONE', 'TAX', 'VAT');

//echo '<pre>';
//print_r($data);
//print_r($tariff_options);
//print_r($provider_data);
//echo '</pre>';
?>
<div class="">
    <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Carrier Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li>
                    <a href="<?php echo base_url() ?>carriers"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Carrier Listing Page</button></a>

                </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Carrier IP</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">IP </th>
                                <th class="column-title">Type</th>
                                <th class="column-title">Load</th>
                                <th class="column-title">Status </th>
                                <th class="column-title">Action </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['ip']) > 0) {
                                foreach ($data['ip'] as $carrier_ip_data) {
                                    if ($carrier_ip_data['ip_status'] == '1')
                                        $status = '<span class="label label-success">Active</span>';
                                    else
                                        $status = '<span class="label label-danger">Inactive</span>';
                                    ?>
                                    <tr >
                                        <td><?php echo $carrier_ip_data['ipaddress']; ?></td>
                                        <td><?php echo $carrier_ip_data['auth_type']; ?></td>
                                        <td><?php echo $carrier_ip_data['load_share']; ?></td>
                                        <td><?php echo $status; ?></td>
                                        <td class=" last">
                                            <a href="<?php echo base_url(); ?>carriers/editG/<?php echo param_encrypt($data['carrier_id']); ?>/<?php echo param_encrypt($carrier_ip_data['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>

                                            <?php if (check_account_permission('carrier', 'delete')): ?>
                                                <a href="javascript:void(0);"
                                                   onclick=doConfirmDelete('<?php echo $carrier_ip_data['carrier_ip_id']; ?>','carriers/edit/<?php
                                                echo
                                                param_encrypt($carrier_ip_data['carrier_id']);
                                                ?>','carrier_ip_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                                               <?php endif; ?>


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
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url(); ?>carriers/addG/<?php echo param_encrypt($data['carrier_id']); ?>" ><input type="button" value="Add Carrier IP" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>       
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Caller ID Translation Rules</h2>
                    <ul class="nav navbar-right panel_toolbox">


                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Number should start with</th>
                                <th class="column-title">Remove Prefix</th>
                                <th class="column-title">Add Prefix</th>
                                <th class="column-title">Translation Rule</th>
                                <th class="column-title">Type</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['callerid']) > 0) {
                                foreach ($data['callerid'] as $callerid_data) {
                                    if ($callerid_data['action_type'] == '1')
                                        $status = '<span class="label label-success">Allowed</span>';
                                    else
                                        $status = '<span class="label label-danger">Blocked</span>';
                                    ?>
                                    <tr >
                                        <td><?php
                            if (str_replace('%', '', $callerid_data['maching_string']) == '')
                                echo "Any Number";
                            else
                                echo str_replace('%', '', $callerid_data['maching_string']);
                                    ?></td>

                                        <td><?php echo str_replace('%', '', $callerid_data['remove_string']); ?></td>
                                        <td><?php echo str_replace('%', '', $callerid_data['add_string']); ?></td>
                                        <td><?php echo $callerid_data['display_string']; ?></td>
                                        <td><?php echo $status; ?></td>
                                    </tr>

                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>


                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url() ?>carriers/editSRCNo/<?php echo param_encrypt($data['carrier_id']); ?>" ><input type="button" value="Edit Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>

        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Termination Prefix Translation Rules</h2>
                    <ul class="nav navbar-right panel_toolbox">


                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Number should start with</th>
                                <th class="column-title">Remove Prefix</th>
                                <th class="column-title">Add Prefix</th>
                                <th class="column-title">Translation Rule</th>
                            </tr>
                        </thead>

                        <tbody>


                            <?php
                            if (count($data['prefix']) > 0) {
                                foreach ($data['prefix'] as $prefix_data) {
                                    ?>
                                    <tr >
                                        <td><?php
                            if (str_replace('%', '', $prefix_data['maching_string']) == '')
                                echo "Any Number";
                            else
                                echo str_replace('%', '', $prefix_data['maching_string']);
                                    ?></td>

                                        <td><?php echo str_replace('%', '', $prefix_data['remove_string']); ?></td>
                                        <td><?php echo str_replace('%', '', $prefix_data['add_string']); ?></td>
                                        <td><?php echo $prefix_data['display_string']; ?></td>

                                    </tr>

                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>



                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url() ?>carriers/editDSTNo/<?php echo param_encrypt($data['carrier_id']); ?>" ><input type="button" value="Edit Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>

        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Incoming Caller ID Translation Rules</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Number should start with</th>
                                <th class="column-title">Remove Prefix</th>
                                <th class="column-title">Add Prefix</th>
                                <th class="column-title">Translation Rule</th>
                                <th class="column-title">Type</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['callerid_incoming']) > 0) {
                                foreach ($data['callerid_incoming'] as $callerid_data) {
                                    if ($callerid_data['action_type'] == '1')
                                        $status = '<span class="label label-success">Allowed</span>';
                                    else
                                        $status = '<span class="label label-danger">Blocked</span>';
                                    ?>
                                    <tr >
                                        <td><?php
                            if (str_replace('%', '', $callerid_data['maching_string']) == '')
                                echo "Any Number";
                            else
                                echo str_replace('%', '', $callerid_data['maching_string']);
                                    ?></td>

                                        <td><?php echo str_replace('%', '', $callerid_data['remove_string']); ?></td>
                                        <td><?php echo str_replace('%', '', $callerid_data['add_string']); ?></td>
                                        <td><?php echo $callerid_data['display_string']; ?></td>
                                        <td><?php echo $status; ?></td>
                                    </tr>

                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>                            

                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url() ?>carriers/editINSRCNo/<?php echo param_encrypt($data['carrier_id']); ?>" ><input type="button" value="Edit Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>

        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Incoming Termination Prefix Translation Rules</h2>
                    <ul class="nav navbar-right panel_toolbox">


                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Number should start with</th>
                                <th class="column-title">Remove Prefix</th>
                                <th class="column-title">Add Prefix</th>
                                <th class="column-title">Translation Rule</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            if (count($data['prefix_incoming']) > 0) {
                                foreach ($data['prefix_incoming'] as $prefix_data) {
                                    ?>
                                    <tr >
                                        <td><?php
                            if (str_replace('%', '', $prefix_data['maching_string']) == '')
                                echo "Any Number";
                            else
                                echo str_replace('%', '', $prefix_data['maching_string']);
                                    ?></td>

                                        <td><?php echo str_replace('%', '', $prefix_data['remove_string']); ?></td>
                                        <td><?php echo str_replace('%', '', $prefix_data['add_string']); ?></td>
                                        <td><?php echo $prefix_data['display_string']; ?></td>

                                    </tr>

                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url() ?>carriers/editINDSTNo/<?php echo param_encrypt($data['carrier_id']); ?>" ><input type="button" value="Edit Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>    

    </div>

    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Carrier Management (EDIT) </h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">                                
                <form action="<?php echo base_url(); ?>carriers/edit/<?php echo param_encrypt($data['carrier_id']); ?>" method="post" name="carrier_form" id="carrier_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="button_action" id="button_action" value="">
                    <input type="hidden" name="action" value="OkSaveData">
                    <input type="hidden" name="key" value="<?php echo $data['carrier_id']; ?>"/>
                    <input type="hidden" name="carrier_id" value="<?php echo $data['carrier_id']; ?>"/>

                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Carrier Code </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="carrier_id_display" id="carrier_id_display" value="<?php echo $data['carrier_id']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Carrier Name <span class="required">*</span> </label>
                        <div class="col-md-7 col-sm-6 col-xs-12"><!-- switch_user_access.name-->
                            <input type="text" name="carrier_name" id="carrier_name" value="<?php echo $data['carrier_name']; ?>"  data-parsley-required="" data-parsley-minlength="3"  data-parsley-maxlength="30" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Maximum Call Sessions  <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="carrier_cc" id="carrier_cc" value="<?php echo $data['carrier_cc']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Call Sessions per Second <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="carrier_cps" id="carrier_cps" value="<?php echo $data['carrier_cps']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Billing in Decimal <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="dp" id="dp" value="<?php echo $data['dp']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group" id="div_id_vat_flag">
                        <label for="middle-name" class="control-label col-md-5 col-sm-3 col-xs-12">VAT / Tax Flag <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="vat_flag" id="vat_flag" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>
                                <?php
                                $str = '';
                                foreach ($vatflag_array as $key => $vat) {
                                    $selected = ' ';
                                    if ($data['vat_flag'] == $vat)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $vat . '" ' . $selected . '>' . $vat . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>
                    
                <div id ="taxchange">
                    
                    <div class="form-group" id="div_id_tax_type">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax on bill Amount Calculation <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="tax_type" id="tax_type" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>
                                <?php
                                $tax_type_array = array('exclusive' => 'Tax On Bill Amount (exclusive)', 'inclusive' => 'Bill Amount with Tax (inclusive)');
                                $str = '';
                                foreach ($tax_type_array as $key => $tax_type) {
                                    $selected = ' ';
                                    if ($data['tax_type'] == $tax_type)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $key . '" ' . $selected . '>' . ucfirst($tax_type) . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax Certificate Number</label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="tax_number" id="tax_number" value="<?php echo $data['tax_number']; ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group tax_class">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax 1(%) <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="tax1" id="tax1" value="<?php echo $data['tax1']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control " tabindex="<?php echo $tab_index++; ?>">
                        </div>                       
                    </div>
                    <div class="form-group tax_class">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax 2(%) <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="tax2" id="tax2" value="<?php echo $data['tax2']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                        </div>

                    </div>
                    <div class="form-group tax_class">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax 3(%) <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="tax3" id="tax3" value="<?php echo $data['tax3']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    
                </div>
                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Type </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="carrier_type_display" id="carrier_type" value="<?php echo ucfirst(strtolower($data['carrier_type'])); ?>" disabled="disabled" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Currency <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="carrier_currency_id" id="carrier_currency_id" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">                              
                                <?php
                                $str = '';
                                foreach ($currency_options as $key => $currency_array) {
                                    $selected = '';
                                    if ($data['carrier_currency_id'] == $currency_array['currency_id']) {
                                        $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $currency_array['currency_id'] . '" ' . $selected . '>' . $currency_array['symbol'] . " - " . $currency_array['name'] . '</option>';
                                    }
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Tariff Plan <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="tariff_id" id="tariff_id" class="combobox form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>
                                <?php
                                $str = '';
                                $is_assigned_tariff_found = false;
                                foreach ($tariff_options as $key => $tariff_name_array) {
                                    if ($data['carrier_currency_id'] != $tariff_name_array['tariff_currency_id'])
                                        continue;
                                    $selected = ' ';
                                    if ($data['tariff_id'] == $tariff_name_array['tariff_id']) {
                                        $selected = '  selected="selected" ';
                                        $is_assigned_tariff_found = true;
                                    }
                                    $str .= '<option value="' . $tariff_name_array['tariff_id'] . '" ' . $selected . '>' . $tariff_name_array['tariff_name'] . '</option>';
                                }
                                if (!$is_assigned_tariff_found && isset($data['tariff']) && count($data['tariff']) > 0) {
                                    $str .= '<option value="' . $data['tariff']['tariff_id'] . '">' . $data['tariff']['tariff_name'] . '</option>';
                                    $tariff_options[] = $data['tariff'];
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>




                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12">Provider <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="provider_id" id="provider_id" class="combobox form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                <option value="" >Select</option>
                                <?php
                                $str = '';
                                if (isset($provider_data['result']) && count($provider_data['result']) > 0) {
                                    foreach ($provider_data['result'] as $provider_array) {
                                        $selected = ' ';
                                        if ($data['provider_id'] == $provider_array['provider_id'])
                                            $selected = '  selected="selected" ';
                                        $currency_id = $provider_array['currency_id'];

                                        if ($data['carrier_currency_id'] != $provider_array['currency_id'])
                                            continue;

                                        $str .= '<option value="' . $provider_array['provider_id'] . '" ' . $selected . '>' . $provider_array['provider_name'] . '</option>';
                                    }
                                }


                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>





                    <div class="form-group">              
                        <label class="control-label col-md-5 col-sm-3 col-xs-12">Progress Timeout <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="carrier_progress_timeout" id="carrier_progress_timeout" value="<?php echo $data['carrier_progress_timeout']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">                </div>

                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Ring Timeout <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="carrier_ring_timeout" id="carrier_ring_timeout" value="<?php echo $data['carrier_ring_timeout']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>

                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">CLI Prefer <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="cli_prefer" id="cli_prefer" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>
                                <?php
                                $cli_prefer_array = array('rpid', 'pid', 'no');
                                $str = '';
                                foreach ($cli_prefer_array as $key => $cli_prefer) {
                                    $selected = ' ';
                                    if ($data['cli_prefer'] == $cli_prefer)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $cli_prefer . '" ' . $selected . '>' . strtoupper($cli_prefer) . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>

                    <?php
                    $codecs_array = array('G729', 'PCMU', 'PCMA', 'G722');
                    ?>
                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-5 col-sm-3 col-xs-12">Codecs</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <?php
//echo $data['user_codecs'];
                            foreach ($codecs_array as $key => $codec) {
                                if (strpos($data['carrier_codecs'], $codec) !== FALSE)
                                    $checked = 'checked="checked"';
                                else
                                    $checked = '';
                                echo '<div class="checkbox">' .
                                '<label><input type="checkbox" name="codecs[]" id="codec' . $key . '" value="' . $codec . '" tabindex="' . $tab_index++ . '" ' . $checked . '/> ' . $codec . '</label>' .
                                '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    
                     <?php
                    $logged_account_type = get_logged_account_type();
                    $account_status = $data['carrier_status'];
                    $status_update_options_array = array();
                    $status_update_options_array['ADMIN'] = array(                      
                        '1' => array(0,-1),
                        '0' => array(1),
                        '-1' => array(0, 1),
                    );
                 

                    $status_name_array = array(                      
                        '1' => array('name' => 'Active', 'tooltip' => 'Carrier is active'),
                        '0' => array('name' => 'Closed', 'tooltip' => 'Carrier Closed'),
                        '-1' => array('name' => 'Inactive', 'tooltip' => 'Carrier is Inactive'),
                    );
                    ?>


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <?php
                            if (isset($status_update_options_array[$logged_account_type][$account_status])) {
                                foreach ($status_update_options_array[$logged_account_type][$account_status] as $status_value) {
                                    if ($account_status == $status_value)
                                        $checked = ' checked="checked"';
                                    else
                                        $checked = '';

                                    if (isset($status_name_array[$status_value])) {
                                        $status_name = $status_name_array[$status_value]['name'];
                                        $tooltip = $status_name_array[$status_value]['tooltip'];
                                    } else {
                                        $status_name = $status_value;
                                        $tooltip = '';
                                    }
                                    ?>
                                    <div class="col-md-12 col-sm-6 col-xs-12 radio1">
                                        <label><input type="radio" name="carrier_status" id="status<?php echo $status_value; ?>" value="<?php echo $status_value; ?>" <?php echo $checked; ?>  tabindex="<?php echo $tab_index++; ?>" /> <?php echo $status_name; ?></label>
                                        <?php
                                        if ($tooltip != '')
                                            echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '"><i class="fa fa-question-circle"></i></a>';
                                        ?>
                                    </div>
                                <?php }
                                ?>

                                <?php
                                if (isset($status_name_array[$account_status])) {
                                    $status_name = $status_name_array[$account_status]['name'];
                                    $tooltip = $status_name_array[$account_status]['tooltip'];
                                } else {
                                    $status_name = $account_status;
                                    $tooltip = '';
                                }
                                ?>
                                <div class="col-md-12 col-sm-6 col-xs-12 radio1">
                                    <label><input type="radio" name="carrier_status" id="status<?php echo $account_status; ?>" value="<?php echo $account_status; ?>"  checked="checked"  tabindex="<?php echo $tab_index++; ?>" /> <?php echo $status_name; ?></label>
                                    <?php
                                    if ($tooltip != '')
                                        echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '"><i class="fa fa-question-circle"></i></a>';
                                    ?>
                                </div>
                                <?php
                            }
                            else {
                                if (isset($status_name_array[$account_status])) {
                                    $status_name = $status_name_array[$account_status]['name'];
                                    $tooltip = $status_name_array[$account_status]['tooltip'];
                                } else {
                                    $status_name = $account_status;
                                    $tooltip = '';
                                }
                                echo '<div class="col-md-12 col-sm-6 col-xs-12 radio""><label>' . $status_name . '</label> ';
                                if ($tooltip != '')
                                    echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '" ><i class="fa fa-question-circle"></i></a>';
                                echo '<input type="hidden" name="carrier_status" id="status1" value="' . $account_status . '" /></div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    
<!--                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-5 col-sm-3 col-xs-12">Status</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="carrier_status" id="status1" value="1" <?php echo set_radio('carrier_status', '1', TRUE); ?> /> Active</label>
                                <label><input type="radio" name="carrier_status" id="status0" value="0" <?php echo set_radio('carrier_status', '0'); ?> /> Inactive</label>
                            </div>                    
                        </div>
                    </div>	-->
                    <div class="clearfix"></div>

                    <div class="form-group">                
                        <div class="col-md-12 col-sm-12 col-xs-12 col-md-offset-1">
                            <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save Carrier Detail</button>          
                            <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Listing Page</button>
                        </div>
                    </div>

                </form>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">       
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Carrier Configuration Management Form Ending</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li>
                    <a href="<?php echo base_url() ?>carriers"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Carrier Listing Page </button></a>
                 </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<script>
    var tariff_array = [];
    tariff_array[1] = new Array();
    tariff_array[2] = new Array();
    tariff_array[3] = new Array();
    tariff_array[4] = new Array();
    tariff_array[5] = new Array();

</script>
<?php
$k = 0;
foreach ($tariff_options as $tariff_name_array) {
    ?>
    <script>
        tariff_array[<?php echo $tariff_name_array['tariff_currency_id']; ?>]["<?php echo $k; ?>"] = ["<?php echo $tariff_name_array['tariff_id']; ?>", "<?php echo $tariff_name_array['tariff_name']; ?>"];
    </script>
    <?php
    $k++;
}
?>

<script>

    window.Parsley
            .addValidator('password', {
                validateString: function (value) {
                    r = true;
                    if (!vCheckPassword(value))
                    {
                        r = false;
                    }
                    return r;
                },
                messages: {
                    en: 'min 8 char, 1 special char, 1 uppercase, 1 lowercase, 1 number'
                }
            });


    $('#btnSave, #btnSaveClose').click(function () {

        $('#carrier_form').parsley().reset();

        var is_ok = $("#carrier_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            //alert('ok');



            carrier_progress_timeout = $('#carrier_progress_timeout').val().trim();
            carrier_ring_timeout = $('#carrier_ring_timeout').val().trim();



            if (parseFloat(carrier_progress_timeout) > parseFloat(carrier_ring_timeout))
            {
                var response = [];
                response.item = 'carrier_progress_timeout';
                response.message = 'Can not more than ring timeout';

                var FieldInstance = $('[name=' + response.item + ']').parsley(),
                        errorName = response.item + '-custom';

                window.ParsleyUI.removeError(FieldInstance, errorName);
                window.ParsleyUI.addError(FieldInstance, errorName, response.message);
                is_ok = false;
            }

            //carrier_progress_timeout   carrier_ring_timeout



            if (is_ok === true)
            {
                $("#carrier_form").submit();
            }
        } else
        {
            $('#carrier_form').parsley().validate();
        }



    })

    function carrier_type_changed()
    {
        if ($("#carrier_type").val().toLowerCase() == 'inbound')
        {
            $('#id_incoming_cdr_billing_div').show();
        } else
        {
            $('#id_incoming_cdr_billing_div').hide();
        }
    }

    function currency_changed()
    {
        var tariff_str = '<option value="">Select</option>';
        carrier_currency_id = $("#carrier_currency_id").val();
        var arrayLength = tariff_array[carrier_currency_id].length;

        if (arrayLength > 0)
        {
            for (var i in tariff_array[carrier_currency_id])
            {
                tariff_id = tariff_array[carrier_currency_id][i][0];
                tariff_name = tariff_array[carrier_currency_id][i][1];

                tariff_str = tariff_str + '<option value="' + tariff_id + '">' + tariff_name + '</option>';
            }
        }

        $('#tariff_id').html(tariff_str);



    }
$('#vat_flag').change(function () {
        tax_chnaged();
    });
    
    function tax_chnaged()
    {
        vat_flag = $("#vat_flag").val();
        if (vat_flag == 'NONE') {
            $('#taxchange').hide();
            $('#tax1').attr('data-parsley-required', 'false');
            $('#tax2').attr('data-parsley-required', 'false');
            $('#tax3').attr('data-parsley-required', 'false');
            $('#tax_type').attr('data-parsley-required', 'false');
            $("#tax_type").val("exclusive");
            $("#tax1").val("0.0");
            $("#tax2").val("0.0");
            $("#tax3").val("0.0");
        } else {
            $('#taxchange').show();
            /*set to initial status*/
            $('#div_id_vat_flag').show();
            $('#div_id_tax_type').show();
            $('.tax_class').show();
            $('#vat_flag').attr('data-parsley-required', 'true');
            $('#tax_type').attr('data-parsley-required', 'true');
              $('#tax_type').val('exclusive');
            $('#tax1').attr('data-parsley-required', 'true');
            $('#tax2').attr('data-parsley-required', 'true');
            $('#tax3').attr('data-parsley-required', 'true');
        }
    }
    
    $(document).ready(function () {
        carrier_type_changed();
         tax_chnaged();
        //currency_changed();
    });

    $("#carrier_type").change(function () {
        carrier_type_changed();
    });
    $("#carrier_currency_id").change(function () {
        currency_changed();
    });
</script>
