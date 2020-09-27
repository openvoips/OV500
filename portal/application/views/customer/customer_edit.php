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
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script>
            $(document).ready(function(){
    $('.combobox').combobox()
    });</script>
<style type="text/css">
    fieldset.scheduler-border {
        border: 1px groove #ddd !important;
        padding: 1em 1.4em 1em 1.4em !important;
        margin: 0 -12px 1.5em -12px !important;
        -webkit-box-shadow:  0px 0px 0px 0px #000;
        box-shadow:  0px 0px 0px 0px #000;
    }

    legend.scheduler-border {
        /*font-size: 1.2em !important;*/
        /* font-weight: bold !important;*/
        font-weight:600!important;
        font-size: 14px;
        text-align: left !important;
        width:auto;
        padding:0 10px;
        border-bottom:none;
        margin-bottom:0px;
        font-family: "Helvetica Neue",Roboto,Arial,"Droid Sans",sans-serif;
    }
</style>
<?php
if (isset($logged_account_result['tariff'])) {
    $tariff_options[$logged_account_result['tariff']['tariff_id']] = $logged_account_result['tariff'];
}
$vatflag_array = array('NONE', 'TAX', 'VAT');

$tab_index = 1;
//echo '<pre>';
//print_r($_SESSION);
//print_r($logged_user_result);
//print_r($data);
//print_r($tariff_options);
//print_r($notification_options) ;
//
//echo '</pre>';
//die;
?>

<div class="">
    <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Customer Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php echo base_url($customer_type . 's') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="col-md-6 col-sm-12 col-xs-12">
        <!---->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Customer IP's Devices</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">IP</th>
                                <th class="column-title">Open Prefix</th>
                                <th class="column-title">Status </th>
                                <th class="column-title">Action </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['ip']) > 0) {
                                foreach ($data['ip'] as $ip_data) {
                                    if ($ip_data['ip_status'] == '1')
                                        $status = '<span class="label label-success">Active</span>';
                                    else
                                        $status = '<span class="label label-danger">Inactive</span>';
                                    ?>
                                    <tr >
                                        <td><?php echo $ip_data['ipaddress']; ?></td>
                                        <td><?php echo $ip_data['dialprefix']; ?></td>
                                        <td><?php echo $status; ?></td>
                                        <td class=" last">
                                            <a href="<?php echo base_url($customer_type . 's'); ?>/ipEdit/<?php echo param_encrypt($data['account_id']); ?>/<?php echo param_encrypt($ip_data['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                            <?php if (check_account_permission('customer', 'delete')): ?>
                                                <a href="javascript:void(0);"
                                                   onclick=doConfirmDelete('<?php echo $ip_data['id']; ?>',"<?php echo $customer_type . 's' ?>/edit/<?php echo param_encrypt($data['account_id']); ?>",'account_ips_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                                               <?php endif; ?>

                                        </td>
                                    </tr>

                                    <?php
                                }
                            }
                            else {
                                ?>
                                <tr>
                                    <td colspan="5" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>


                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($customer_type . 's'); ?>/ipAdd/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Add IP User" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>
        <!----->

        <?php
//        echo '<pre>';
//print_r($data['sipuser']);
//print_r($data['ip']);
//echo '</pre>';
        ?>
        <!---->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Customer SIP Users Devices</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Username</th>
                                <th class="column-title">Secret</th>                               
                                <th class="column-title">Extension No</th>                                
                                <th class="column-title">Status </th>
                                <th class="column-title">Action </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['sipuser']) > 0) {
                                foreach ($data['sipuser'] as $sip_data) {
                                    if ($sip_data['status'] == '1')
                                        $status = '<span class="label label-success">Active</span>';
                                    else
                                        $status = '<span class="label label-danger">Inactive</span>';

                                    if ($sip_data['voicemail'] == '1')
                                        $voicemail = '<span class="label label-success">Active</span>';
                                    else
                                        $voicemail = '<span class="label label-danger">Inactive</span>';
                                    ?>
                                    <tr>
                                        <td><?php echo $sip_data['username']; ?></td>
                                        <td><?php echo $sip_data['secret']; ?></td>                                      
                                        <td><?php echo $sip_data['extension_no']; ?></td>

                                                         <!--<td><?php echo $voicemail; ?></td>-->
                                        <td><?php echo $status; ?></td>
                                        <td class="last">
                                            <a href="<?php echo base_url($customer_type . 's'); ?>/sipEdit/<?php echo param_encrypt($data['account_id']); ?>/<?php echo param_encrypt($sip_data['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>

                                            <?php if (check_account_permission('customer', 'delete')): ?>
                                                <a href="javascript:void(0);"
                                                   onclick=doConfirmDelete('<?php echo $sip_data['id']; ?>','<?php echo $customer_type . 's' ?>/edit/<?php echo param_encrypt($data['account_id']); ?>','account_sip_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                                               <?php endif; ?>

                                        </td>
                                    </tr>

                                    <?php
                                }
                            }
                            else {
                                ?>
                                <tr>
                                    <td colspan="5" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>


                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($customer_type . 's'); ?>/sipAdd/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Add SIP User" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>
        <!----->
        <!----->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Dialing Routes</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Rule</th>
                                <th class="column-title">Dialing Routes</th>
                                <th class="column-title">Action</th>
                            </tr>

                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['dialplan']) > 0) {
                                foreach ($data['dialplan'] as $dialplan_data) {
                                    $display_string_array = explode('=>', $dialplan_data['display_string']);
                                    ?>
                                    <tr >
                                        <td><?php echo $display_string_array[0]; ?></td>
                                        <td><?php echo $dialplan_data['dialplan_name'] . ' (' . $dialplan_data['dialplan_id'] . ')'; ?></td>
                                        <td class=" last">
                                            <a href="<?php echo base_url($customer_type . 's'); ?>/editD/<?php echo param_encrypt($data['account_id']); ?>/<?php echo param_encrypt($dialplan_data['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>

                                            <?php if (check_account_permission('customer', 'delete')): ?>
                                                <a href="javascript:void(0);"
                                                   onclick=doConfirmDelete('<?php echo $dialplan_data['id']; ?>','<?php echo $customer_type . 's'; ?>/edit/<?php echo param_encrypt($data['account_id']); ?>','account_dialplan_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                                               <?php endif; ?>

                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            else {
                                ?>
                                <tr>
                                    <td colspan="3" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>


                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($customer_type . 's') ?>/addD/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Dialing Routes" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>
        <!---->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Source Number Translation Rules</h2>          
                    <div class="clearfix"></div>
                </div>

                <div class="x_content ">
                    <table class="table table-striped jambo_table table-bordered fixed">
                        <thead class="fixedHeader-locked ">
                            <tr class="headings thc">
                                <th class="column-title">Number should start with</th>
                                <th class="column-title">Remove Prefix</th>
                                <th class="column-title">Add Prefix</th>
                                <!--<th class="column-title">Translation Rule</th>-->
                                <th class="column-title">Type</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($data['callerid']) > 0 || count($data['dst_src_cli']) > 0) {
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
                                            <!--<td><?php echo $callerid_data['display_string']; ?></td>-->
                                            <td><?php echo $status; ?></td>

                                        </tr>

                                        <?php
                                    }
                                }
                                if (count($data['dst_src_cli']) > 0) {
                                    foreach ($data['dst_src_cli'] as $callerid_data) {
                                        $status = '<span class="label label-info">DST Prefix Based</span>';
                                        ?>
                                        <tr >
                                            <td><?php echo $callerid_data['display_string']; ?></td>                                 			<td></td>

                                            <td></td>
                                            <td><?php echo $status; ?></td>
                                        </tr>

                                        <?php
                                    }
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="2" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>


                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($customer_type . 's') ?>/editSRCNo/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Manage Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>
        <!----->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Destination Number Translation Rules</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Number should start with</th>
                                <th class="column-title">Remove Prefix</th>
                                <th class="column-title">Add Prefix</th>
                                <!--<th class="column-title">Translation Rule</th>-->
                                <th class="column-title">Type</th>                                
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['translation_rules']) > 0) {
                                foreach ($data['translation_rules'] as $translation_rule_data) {
                                    if ($translation_rule_data['action_type'] == '1')
                                        $status = '<span class="label label-success">Allowed</span>';
                                    else
                                        $status = '<span class="label label-danger">Blocked</span>';
                                    ?>


                                <td><?php
                                    if (str_replace('%', '', $translation_rule_data['maching_string']) == '')
                                        echo "Any Number";
                                    else
                                        echo str_replace('%', '', $translation_rule_data['maching_string']);
                                    ?></td>

                                <td><?php echo str_replace('%', '', $translation_rule_data['remove_string']); ?></td>
                                <td><?php echo str_replace('%', '', $translation_rule_data['add_string']); ?></td>
                                <!--<td><?php echo $translation_rule_data['display_string']; ?></td>-->
                                <td><?php echo $status; ?></td>


                                </tr>

                                <?php
                            }
                        }
                        else {
                            ?>
                            <tr>
                                <td colspan="2" align="center"><strong>No Record Found</strong></td>
                            </tr>
                            <?php
                        }
                        ?>


                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($customer_type . 's') ?>/DSTRule/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Manage Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>
        <!----->
        <!---->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>DID Calls Source Number Translation Rules</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Number should start with</th>
                                <th class="column-title">Remove Prefix</th>
                                <th class="column-title">Add Prefix</th>
                                <!--<th class="column-title">Translation Rule</th>-->
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
                                        <!--<td><?php echo $callerid_data['display_string']; ?></td>-->
                                        <td><?php echo $status; ?></td>

                                    </tr>

                                    <?php
                                }
                            }
                            else {
                                ?>
                                <tr>
                                    <td colspan="2" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>


                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($customer_type . 's') ?>/editINSRCNo/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Manage Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>

        <!---->

        <!---->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>DID Calls Destination Number Translation Rules</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Number should start with</th>
                                <th class="column-title">Remove Prefix</th>
                                <th class="column-title">Add Prefix</th>
                                <!--<th class="column-title">Translation Rule</th>-->
                                <th class="column-title">Type</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['translation_rules_incoming']) > 0) {
                                foreach ($data['translation_rules_incoming'] as $translation_rule_data) {
                                    if ($translation_rule_data['action_type'] == '1')
                                        $status = '<span class="label label-success">Allowed</span>';
                                    else
                                        $status = '<span class="label label-danger">Blocked</span>';
                                    ?>
                                    <tr >
        <!--                                        <td><?php echo $translation_rule_data['display_string']; ?></td>
                                        <td><?php echo $status; ?></td>-->


                                        <td><?php
                                            if (str_replace('%', '', $translation_rule_data['maching_string']) == '')
                                                echo "Any Number";
                                            else
                                                echo str_replace('%', '', $translation_rule_data['maching_string']);
                                            ?></td>

                                        <td><?php echo str_replace('%', '', $translation_rule_data['remove_string']); ?></td>
                                        <td><?php echo str_replace('%', '', $translation_rule_data['add_string']); ?></td>
                                        <!--<td><?php echo $translation_rule_data['display_string']; ?></td>-->
                                        <td><?php echo $status; ?></td>

                                    </tr>

                                    <?php
                                }
                            }
                            else {
                                ?>
                                <tr>
                                    <td colspan="2" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>


                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($customer_type . 's') ?>/DIDNumberTRule/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Manage Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>

        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Bundle & Package</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Bundle</th>
                                <th class="column-title">Assign</th>
                                <th class="column-title">Allowed Prefixes</th>
                                <th class="column-title">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (isset($data['bundle_package']) && count($data['bundle_package']) > 0) {
                                foreach ($data['bundle_package'] as $package_data) {
                                    ?>
                                    <tr >
                                        <td><?php echo $package_data['bundle_package_name'] . ' (' . $package_data['bundle_package_id'] . ')'; ?></td>
                                        <td ><?php echo $package_data['bundle_count']; ?></td>
                                        <td ><?php echo wordwrap(implode(', ', array_unique(explode(',', $package_data['prefix']))), 20, "<br>\n", TRUE); ?></td>
                                        <td class=" last">                                           
                                            <?php if (check_account_permission('customer', 'delete')): ?>
                                                <a href="javascript:void(0);"
                                                   onclick=doConfirmDelete('<?php echo $package_data['bundle_account_id']; ?>','<?php echo $customer_type . 's'; ?>/edit/<?php echo param_encrypt($data['account_id']); ?>','account_bundle_delete') title="One Bundle will delete at a time." class="delete"><i class="fa fa-trash"></i></a>
                                               <?php endif; ?>

                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            else {
                                ?>
                                <tr>
                                    <td colspan="5" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>


                        </tbody>
                    </table>
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($customer_type . 's') ?>/addBundle/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Add Bundle & Package" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>

    </div>
    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Customer Detail(EDIT)</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br />
                <form action="<?php echo base_url($customer_type . 's'); ?>/edit/<?php echo param_encrypt($data['account_id']); ?>" method="post" name="user_form" id="user_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="button_action" id="button_action" value="">
                    <input type="hidden" name="action" value="OkSaveData">
                    <input type="hidden" name="key" value="<?php echo $data['account_id']; ?>"/>
                    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>
                    <?php
                    /* option will only come to direct customers */
                    if (get_logged_account_level() == 0) {
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Customer Type</label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <?php
                                if ($data['account_type'] == 'DEMO') {
                                    ?>
                                    <div class="col-md-12 col-sm-6 col-xs-12 radio1">
                                        <label><input type="radio" name="account_type" id="account_type_real" value="REAL" tabindex="<?php echo $tab_index++; ?>" /> LIVE</label>
                                    </div>
                                    <div class="col-md-12 col-sm-6 col-xs-12 radio1">
                                        <label><input type="radio" name="account_type" id="account_type_demo" value="DEMO" <?php if ($data['account_type'] == 'DEMO') echo ' checked="checked"'; ?> tabindex="<?php echo $tab_index++; ?>" /> DEMO</label>
                                    </div>

                                    <?php
                                }else {
                                    if ($data['account_type'] == 'TEST')
                                        echo '<strong>IN-HOUSE</strong>';
                                    else
                                        echo '<strong>LIVE</strong>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php }
                    ?>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Account Code</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <?php echo $data['account_id']; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Web Access Username </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <?php echo $data['username']; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Web Access Password </label>
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="secret" id="secret" value="<?php echo $data['secret']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-minlength="8" data-parsley-maxlength="30" data-parsley-password="" autocomplete="off" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>                      
                    <?php
                    if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'NOC'))) {
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Currency <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <?php
                                $account_currency_display = '';
                                foreach ($currency_options as $key => $currency_array) {
                                    if ($data['currency_id'] == $currency_array['currency_id']) {
                                        $account_currency_display = $currency_array['name'];
                                        break;
                                    }
                                }

                                echo '<input type="hidden" name="account_currency_id" id="account_currency_id" value="' . $data['currency_id'] . '" data-parsley-required="" class="form-control"  readonly="readonly" tabindex="' . $tab_index++ . '">';
                                echo '<input type="text" name="account_currency_display" id="account_currency_display" value="' . $account_currency_display . '" class="form-control"  readonly="readonly"  tabindex="' . $tab_index++ . '">';
                                ?>

                            </div>
                        </div>
                        <?php
                    } else {
                        echo '<input type="hidden" name="account_currency_id" id="account_currency_id" value="' . $data['currency_id'] . '" class="form-control">';
                    }
                    ?>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >User Tariff <span class="required">*</span>
                        </label>
                        <div class="control-label col-md-7 col-sm-6 col-xs-12">
                            <select name="tariff_id" id="tariff_id" class="combobox input-large form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>
                                <?php
                                $str = '';
                                $is_assigned_tariff_found = false;
                                foreach ($tariff_options as $key => $tariff_name_array) {
                                    if ($data['currency_id'] != $tariff_name_array['tariff_currency_id'])
                                        continue;
                                    $selected = ' ';
                                    if ($data['tariff_id'] == $tariff_name_array['tariff_id']) {
                                        $selected = '  selected="selected" ';
                                        $is_assigned_tariff_found = true;
                                    }
                                    $str .= '<option value="' . $tariff_name_array['tariff_id'] . '" ' . $selected . '>' . $tariff_name_array['tariff_name'] . '</option>';
                                }

//if assigned tariff not in list,
                                if (!$is_assigned_tariff_found && isset($data['tariff']) && count($data['tariff']) > 0) {
                                    $str .= '<option value="' . $data['tariff']['tariff_id'] . '" selected="selected">' . $data['tariff']['tariff_name'] . '</option>';
                                    $tariff_options[] = $data['tariff'];
                                }
                                echo $str;
                                ?>

                            </select>
                        </div>
                    </div>



                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Billing Type <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <?php
                            $billing_type_array = array('prepaid' => 'Prepaid', 'postpaid' => 'Postpaid', 'netoff' => 'Net-Off');
                            foreach ($billing_type_array as $key => $billing_type) {
                                if ($data['billing_type'] == $key) {
                                    echo '<input type="hidden" name="billing_type" id="billing_type" value="' . $key . '">';
                                    echo ucfirst($billing_type);
                                    break;
                                }
                            }
                            ?>

                        </div>
                    </div>

                    <input type="hidden" name="billing_cycle" value="monthly">

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Payment Terms from Invoice Date <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="payment_terms" id="payment_terms" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>
                                <?php
                                $payment_terms_array = range(1, 30);
                                $str = '';
                                foreach ($payment_terms_array as $key => $payment_terms) {
                                    $selected = ' ';
                                    if ($data['payment_terms'] == $payment_terms)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $payment_terms . '" ' . $selected . '>' . ucfirst($payment_terms) . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" >
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax on bill Amount Calculation <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="tax_type" id="tax_type" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">

                                <?php
                                $tax_type_array = array('exclusive' => 'Tax On Bill Amount (exclusive)', 'inclusive' => 'Bill Amount with Tax (inclusive)');
                                $str = '';
                                foreach ($tax_type_array as $key => $tax_type) {
                                    $selected = ' ';
                                    if ($data['tax_type'] == $key)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $key . '" ' . $selected . '>' . ucfirst($tax_type) . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" >
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">VAT / Tax Flag <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="vat_flag" id="vat_flag" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">                                
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
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax Certificate Number</label>
                            <div class="col-md-7 col-sm-6 col-xs-10">
                                <input type="text" name="tax_number" id="tax_number" value="<?php echo $data['tax_number']; ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>
                        <div class="form-group tax_class">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 1(%) <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-10">
                                <input type="text" name="tax1" id="tax1" value="<?php echo $data['tax1']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control " tabindex="<?php echo $tab_index++; ?>">
                            </div>                      
                        </div>
                        <div class="form-group tax_class">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 2(%) <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-10">
                                <input type="text" name="tax2" id="tax2" value="<?php echo $data['tax2']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                            </div>                      
                        </div>
                        <div class="form-group tax_class">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 3(%) <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-10">
                                <input type="text" name="tax3" id="tax3" value="<?php echo $data['tax3']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Billing in Decimal <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="dp" id="dp" value="<?php echo $data['dp']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Maximum Call Sessions <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="account_cc" id="account_cc" value="<?php echo $data['account_cc']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Call Sessions per Second <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="account_cps" id="account_cps" value="<?php echo $data['account_cps']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Codecs Checking</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="codecs_force" id="codecs_force1" value="1" <?php if ($data['codecs_force'] == 1) { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>

                                <label> <input type="radio" name="codecs_force" id="codecs_force2" value="0" <?php if ($data['codecs_force'] == 0) { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>

                    <?php
                    $codecs_array = array('G729', 'PCMU', 'PCMA', 'G722');
                    ?>
                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Codecs List</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <?php
                            foreach ($codecs_array as $key => $codec) {
                                if (strpos($data['account_codecs'], $codec) !== FALSE)
                                    $checked = 'checked="checked"';
                                else
                                    $checked = '';
                                echo '<div class="checkbox">' .
                                '<label><input type="checkbox" name="codecs[]" id="codec' . $key . '" value="' . $codec . '" tabindex="' . $tab_index++ . '" ' . $checked . '/> ' . $codec . '</label>' .
                                '</div>';
                            }
                            ?>
                            <label>Only possible if G729 codec is installed in system.</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Call With Media</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="media_rtpproxy" id="media_rtpproxy1" value="1" <?php if ($data['media_rtpproxy'] == 1) { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>

                                <label> <input type="radio" name="media_rtpproxy" id="media_rtpproxy2" value="0" <?php if ($data['media_rtpproxy'] == 0) { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>

                    <div class="form-group" id="id_transcoding_div">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Codecs Transcoding</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="media_transcoding" id="media_transcoding1" value="1" <?php if ($data['media_transcoding'] == 1) { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>

                                <label> <input type="radio" name="media_transcoding" id="media_transcoding2" value="0" <?php if ($data['media_transcoding'] == 0) { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Change CLI Based On DST Prefix</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="force_dst_src_cli_prefix" id="force_dst_src_cli_prefix1" value="1" <?php if ($data['force_dst_src_cli_prefix'] == 1) { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>"/> Yes</label>

                                <label> <input type="radio" name="force_dst_src_cli_prefix" id="force_dst_src_cli_prefix2" value="0" <?php if ($data['force_dst_src_cli_prefix'] == 0) { ?> checked="checked" <?php } ?>  tabindex="<?php echo $tab_index++; ?>"/> No</label>
                            </div>

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Max Call Duration (Minutes) <span class="required">*</span></label>                        
                        <div class="col-md-7 col-sm-6 col-xs-10">
                            <input type="text" name="max_callduration" id="max_callduration" value="<?php echo $data['max_callduration']; ?>" data-parsley-required=""  data-parsley-min="1" data-parsley-type="digits" data-parsley-type-message="Digits only" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                        </div>

                    </div>
                    <?php
                    $logged_account_type = get_logged_account_type();
                    $account_status = $data['account_status'];
                    $status_update_options_array = array();
                    $status_update_options_array['ADMIN'] = array(
                        '-1' => array(),
                        '1' => array(0, -2, -3),
                        '0' => array(),
                        '-2' => array(0, 1, -3),
                        '-3' => array(0, -2, 1),
                    );
                    $status_update_options_array['CREDITCONTROL'] = array(
                        '-1' => array(1),
                        '1' => array(0, -2, -3),
                        '0' => array(),
                        '-2' => array(0, 1, -3),
                        '-3' => array(0, -2, 1),
                    );
                    $status_update_options_array['NOC'] = array(
                        '1' => array(0, -2, -3),
                        '-3' => array(0, -2, 1),
                    );

                    $status_name_array = array(
                        '-1' => array('name' => 'Not Approved', 'tooltip' => 'Waiting for approval'),
                        '1' => array('name' => 'Active', 'tooltip' => 'Account is active'),
                        '0' => array('name' => 'Closed', 'tooltip' => 'Account Closed'),
                        '-2' => array('name' => 'Temporarily Suspended', 'tooltip' => 'If balance is zero'),
                        '-3' => array('name' => 'Suspected Blocked', 'tooltip' => 'For suspicious activity, make user blocked'),
                        '-4' => array('name' => 'Account Closed', 'tooltip' => 'Account is closed'),
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
                                        <label><input type="radio" name="account_status" id="status<?php echo $status_value; ?>" value="<?php echo $status_value; ?>" <?php echo $checked; ?>  tabindex="<?php echo $tab_index++; ?>" /> <?php echo $status_name; ?></label>
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
                                    <label><input type="radio" name="account_status" id="status<?php echo $account_status; ?>" value="<?php echo $account_status; ?>"  checked="checked"  tabindex="<?php echo $tab_index++; ?>" /> <?php echo $status_name; ?></label>
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
                                echo '<input type="hidden" name="account_status" id="status1" value="' . $account_status . '" /></div>';
                            }
                            ?>
                        </div>
                    </div>




                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border">Registered Address</legend>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Name <span class="required">*</span>   </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="name" id="name" value="<?php echo $data['name']; ?>"  data-parsley-required="" data-parsley-minlength="3" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="last-name">Company <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="company_name" id="company_name" value="<?php echo $data['company_name']; ?>" data-parsley-required="" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="last-name">Email Address <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="emailaddress" id="emailaddress" value="<?php echo $data['emailaddress']; ?>"  data-parsley-required="" data-parsley-type="email" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="last-name">Address </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <textarea name="address" id="address" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>"><?php echo $data['address']; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Phone Number </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="phone" id="phone" value="<?php echo $data['phone']; ?>" data-parsley-type="digits" data-parsley-minlength="10" data-parsley-maxlength="12" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Country </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="country_id" id="country_id" class="combobox form-control" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select</option>                    
                                    <?php
                                    $str = '';
                                    foreach ($country_options as $key => $country_array) {
                                        $selected = ' ';
                                        if ($data['country_id'] == $country_array->country_id)
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $country_array->country_id . '" ' . $selected . '>' . $country_array->country_name . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>              

                        <div class="form-group" id="id_state_div">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >State </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="state_code_id" id="state_code_id" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select</option>                    
                                    <?php
                                    $str = '';
                                    foreach ($state_options as $key => $state_array) {
                                        $selected = ' ';
                                        if ($data['state_code_id'] == $state_array['state_code_id'])
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $state_array['state_code_id'] . '" ' . $selected . '>' . $state_array['state_name'] . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >PIN</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="pincode" id="pincode" value="<?php echo $data['pincode']; ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>
                    </fieldset>




                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-12 col-sm-6 col-xs-12 col-md-offset-3">                          
                            <?php if (check_account_permission('customer', 'edit')): ?>
                                <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save</button>   

                                <button type="button" id="btnSaveClose" class="btn btn-info" tabindex="<?php echo $tab_index++; ?>">Save & Go Back to Listing Page</button>
                            <?php endif; ?>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Customer Account Configuration Management Form Ending</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php echo base_url($customer_type . 's') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Listing Page</button></a> </li>
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
                                            tariff_array[5] = new Array();</script>
<?php
$k = 0;
foreach ($tariff_options as $tariff_name_array) {
    ?>
    <script>
                                                    tariff_array[<?php echo $tariff_name_array['tariff_currency_id']; ?>]["<?php echo $k; ?>"] = ["<?php echo $tariff_name_array['tariff_id']; ?>", "<?php echo $tariff_name_array['tariff_name']; ?>"];</script>
    <?php
    $k++;
}
?>

<script>

                                            window.Parsley
                                            .addValidator('password', {
                                            validateString: function(value) {
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
                                            $('#btnSave, #btnSaveClose').click(function() {

                                    $('#user_form').parsley().reset();
                                            var is_ok = $("#user_form").parsley().isValid();
                                            if (is_ok === true)
                                    {
                                    var clicked_button_id = this.id;
                                            if (clicked_button_id == 'btnSaveClose')
                                            $('#button_action').val('save_close');
                                            else
                                            $('#button_action').val('save');
                                            if (is_ok === true)
                                    {
                                    //alert("ok");
                                    $("#user_form").submit();
                                    }
                                    }
                                    else
                                    {
                                    $('#user_form').parsley().validate();
                                    }

                                    })
/////notification form

                                            $('#btnSaveNotification, #btnSaveCloseNotification').click(function() {

                                    $('#notification_form').parsley().reset();
                                            var is_ok = $("#notification_form").parsley().isValid();
                                            if (is_ok === true)
                                    {
                                    var clicked_button_id = this.id;
                                            if (clicked_button_id == 'btnSaveCloseNotification')
                                            $('#button_action_notification').val('save_close');
                                            else
                                            $('#button_action_notification').val('save');
                                            if (is_ok === true)
                                    {
                                    $("#notification_form").submit();
                                    }
                                    }
                                    else
                                    {
                                    $('#notification_form').parsley().validate();
                                    }

                                    })

                                            function media_changed()
                                            {
                                            media_value = $("input[name='media_rtpproxy']:checked").val();
                                                    if (media_value == '1')
                                                    $('#id_transcoding_div').show();
                                                    else
                                                    $('#id_transcoding_div').hide();
                                            }

                                    function currency_changed()
                                    {
                                    var tariff_str = '<option value="">Select</option>';
                                            currency_id = $("#currency_id").val();
                                            if (currency_id == '')
                                    {

                                    }
                                    else
                                    {
                                    var arrayLength = tariff_array[currency_id].length;
                                            if (arrayLength > 0)
                                    {
                                    for (var i in tariff_array[currency_id])
                                    {
                                    tariff_id = tariff_array[currency_id][i][0];
                                            tariff_name = tariff_array[currency_id][i][1];
                                            tariff_str = tariff_str + '<option value="' + tariff_id + '">' + tariff_name + '</option>';
                                    }
                                    }
                                    }
                                    $('#tariff_id').html(tariff_str);
                                    }


                                    function country_changed()
                                    {
                                    country_id = $("#country_id").val();
                                            if (country_id == '100')
                                    {
                                    $('#id_state_div').show();
                                            $('#state_code_id').attr('data-parsley-required', 'true');
                                    }
                                    else
                                    {
                                    $('#id_state_div').hide();
                                            $('#state_code_id').attr('data-parsley-required', 'false');
                                    }


                                    }
                                    function billing_country_changed()
                                    {
                                    country_id = $("#billing_country_id").val();
                                            if (country_id == '100')
                                    {
                                    $('#id_billing_state_div').show();
                                            $('#billing_state_code_id').attr('data-parsley-required', 'true');
                                    }
                                    else
                                    {
                                    $('#id_billing_state_div').hide();
                                            $('#billing_state_code_id').attr('data-parsley-required', 'false');
                                    }
                                    }

                                    function multicallonsameno_chnaged()
                                    {
                                    if ($('#multicallonsameno_allow').is(":checked"))
                                    {
                                    $('#multicallonsameno_limit').attr('data-parsley-required', 'true');
                                            $('#multicallonsameno_limit').attr('data-parsley-type', 'digits');
                                            $("#multicallonsameno_limit").attr('readonly', false);
                                            if ($("#multicallonsameno_limit").val() == '')
                                            $("#multicallonsameno_limit").val(5);
                                    }
                                    else{
                                    $('#multicallonsameno_limit').attr('data-parsley-required', 'false');
                                            $('#multicallonsameno_limit').removeAttr('data-parsley-type');
                                            $("#multicallonsameno_limit").attr('readonly', true);
                                            $("#multicallonsameno_limit").val('');
                                    }
                                    }
                                    function notification_changed(checked_id)
                                    {
                                    if ($('#' + checked_id).is(':checked'))
                                    {
                                    $('#email-' + checked_id).attr('data-parsley-required', 'true');
                                            if ($('#amount-' + checked_id).length !== 0)
                                    {
                                    $('#amount-' + checked_id).attr('data-parsley-required', 'true');
                                            $('#amount-' + checked_id).attr('data-parsley-type', 'digits');
                                    }

                                    }
                                    else
                                    {
                                    $('#email-' + checked_id).attr('data-parsley-required', 'false');
                                            if ($('#amount-' + checked_id).length !== 0)
                                    {
                                    $('#amount-' + checked_id).attr('data-parsley-required', 'false');
                                            $('#amount-' + checked_id).removeAttr('data-parsley-type');
                                    }
                                    }
                                    };
                                            function tax_chnaged(){
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
                                                    $('.tax_class').show();
                                                    $('#vat_flag').attr('data-parsley-required', 'true');
                                                    $('#tax_type').attr('data-parsley-required', 'true');
                                                    $('#tax1').attr('data-parsley-required', 'true');
                                                    $('#tax2').attr('data-parsley-required', 'true');
                                                    $('#tax3').attr('data-parsley-required', 'true');
                                            }
                                            }

                                    $('input[type=radio][name=media_rtpproxy]').change(function() {
                                    media_changed();
                                    });
                                            $("#currency_id").change(function(){
                                    currency_changed();
                                    });
                                            $("#country_id").change(function(){
                                    country_changed();
                                    });
                                            $("#billing_country_id").change(function(){
                                    billing_country_changed();
                                    });
                                            $(".notifications").change(function() {
                                    var checked_id = this.id;
                                            notification_changed(checked_id);
                                    });
                                            $('#multicallonsameno_allow').click(function() {
                                    multicallonsameno_chnaged();
                                    });
                                            $('#vat_flag').change(function() {
                                    tax_chnaged();
                                    });
                                            $(document).ready(function() {
                                    media_changed();
                                            country_changed();
                                            billing_country_changed();
                                            multicallonsameno_chnaged();
                                            tax_chnaged();
                                    });</script>

<script>
                                            /*notification form validation*/


</script>
<script>
                                            $("#same_as_registered_address").change(function() {

                                    if ($('#same_as_registered_address').is(":checked"))
                                    {
                                    var name = $('#name').val();
                                            var company_name = $('#company_name').val();
                                            var emailaddress = $('#emailaddress').val();
                                            var phone = $('#phone').val();
                                            var address = $('#address').val();
                                            var pincode = $('#pincode').val();
                                            var country_id = $('#country_id').val();
                                            var state_code_id = $('#state_code_id').val();
                                            $('#billing_name').val(name);
                                            $('#billing_company_name').val(company_name);
                                            $('#billing_emailaddress').val(emailaddress);
                                            $('#billing_phone').val(phone);
                                            $('#billing_address').val(address);
                                            $('#billing_pincode').val(pincode);
                                            $('#billing_country_id').val(country_id);
                                            $('#billing_state_code_id').val(state_code_id);
                                    }
                                    else
                                    {
                                    $('#billing_name').val('');
                                            $('#billing_company_name').val('');
                                            $('#billing_emailaddress').val('');
                                            $('#billing_phone').val('');
                                            $('#billing_address').val('');
                                            $('#billing_pincode').val('');
                                            $('#billing_country_id').val('');
                                            $('#billing_state_code_id').val('');
                                    }

                                    billing_country_changed();
                                    });
                                            function doConfirmCancel(delete_val, delete_action_url = '', delete_type = '')
                                            {
                                            var delete_id_array = [];
                                                    delete_id_array.push(delete_val);
                                                    var modal_body = '<h1 class="text-center"><i class="fa fa-exclamation-circle"></i></h1>' +
                                                    '<h4 class="text-center">Are you sure!</h4>' +
                                                    '<p class="text-center">You won\'t be able to revert this!</p>';
                                                    var modal_footer = '<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>' +
                                                    '<button type="button" class="btn btn-danger" id="modal-btn-yes-single">Yes. cancel it!</button>';
                                                    openModal('', '', modal_body, modal_footer);
                                                    $("#my-modal").modal('show');
                                                    $("#modal-btn-yes-single").on("click", function(){
                                            //alert("single");

                                            var form = document.createElement("form");
                                                    document.body.appendChild(form);
                                                    form.method = "POST";
                                                    if (delete_action_url == '')
                                                    form.action = window.location.href;
                                                    else
                                            {
                                            form.action = BASE_URL + delete_action_url;
                                            }

                                            var element2 = document.createElement("INPUT");
                                                    element2.name = "action";
                                                    element2.value = 'OkDeleteData';
                                                    element2.type = 'hidden';
                                                    form.appendChild(element2);
                                                    var element3 = document.createElement("INPUT");
                                                    element3.name = "delete_id";
                                                    element3.value = JSON.stringify(delete_id_array);
                                                    element3.type = 'hidden';
                                                    form.appendChild(element3);
                                                    if (delete_type == '')
                                            {}
                                            else
                                            {
                                            var element4 = document.createElement("INPUT");
                                                    element4.name = "delete_parameter_two";
                                                    element4.value = delete_type;
                                                    element4.type = 'hidden';
                                                    form.appendChild(element4);
                                            }


                                            form.submit();
                                                    //alert("yes");
                                                    $("#my-modal").modal('hide');
                                            });
                                            }
</script>

<div class="clearfix"></div>