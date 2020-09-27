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
$vatflag_array = array('NONE', 'SEZ', 'REVERSE', 'TAX', 'VAT');
$customer_type = strtolower($customer_type);
$tab_index = 1;
//echo '<pre>';
//print_r($_SESSION);
//print_r($logged_user_result);
//print_r($data);
//print_r($tariff_options);
//echo '</pre>';
//die;


//$customer_type = $data['customer_type'];
$customer_type = "/".param_encrypt($customer_type);
?>

<div class="">
    <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Customer Account Configuration Management</h2>
<!--            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php echo base_url() . 'endpoints/index/' . param_encrypt($_SESSION['session_current_customer_id'])."$customer_type"; ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Listing Page</button></a> </li>
            </ul>-->
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="col-md-12 col-sm-12 col-xs-12">
        <!---->
        
        <?php  if (count($data['ip']) > 0) { ?>
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Customer IP's Devices</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        

                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Call Originator SIP System IP Address</th>
                                <th class="column-title">Open Prefix</th>
                                <th class="column-title">Maximum Call Sessions</th>
                                <th class="column-title">Call Sessions per Second</th>

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
                                        <td><?php echo $ip_data['ip_cc']; ?></td>
                                        <td><?php echo $ip_data['ip_cps']; ?></td>


                                        <td><?php echo $status; ?></td>
                                        <td class=" last">
                                            <a href="<?php echo base_url('endpoints'); ?>/ipEdit/<?php echo param_encrypt($data['account_id']); ?>/<?php echo param_encrypt($ip_data['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                            <?php if (check_account_permission('customer', 'delete')): ?>
                                                <a href="javascript:void(0);"
                                                   onclick=doConfirmDelete('<?php echo $ip_data['id']; ?>',"<?php echo 'endpoints' ?>/edit/<?php echo param_encrypt($data['account_id']); ?>",'account_ips_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
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
                        <a href="<?php echo base_url('endpoints'); ?>/ipAdd/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Add IP User" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>
        
        <?php }?>
        <!----->

        <?php
 if (count($data['sipuser']) > 0) {
        ?>
        <!---->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Customer SIP Users Devices</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        

                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">SIP Device Username</th>
                                <th class="column-title">SIP Device Password</th>
                                <th class="column-title">Extension No</th>                              
                                <th class="column-title">Maximum Call Sessions</th>
                                <th class="column-title">Call Sessions per Second</th>
                                <th class="column-title">Caller ID</th>
                                <th class="column-title">CLI Preference In SIP Header</th>
                                <th class="column-title">Allowed Codecs</th>
                                <th class="column-title">VoiceMail</th>
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
                                        <td><?php echo $sip_data['sip_cc']; ?></td>
                                        <td><?php echo $sip_data['sip_cps']; ?></td>
                                        <td><?php echo $sip_data['caller_id']; ?></td>
                                        <td><?php echo $sip_data['cli_prefer']; ?></td>
                                        <td><?php echo $sip_data['codecs']; ?></td>

                                        <td><?php echo $voicemail; ?></td>
                                        <td><?php echo $status; ?></td>
                                        <td class="last">
                                            <a href="<?php echo base_url('endpoints'); ?>/EPsipEdit/<?php echo param_encrypt($data['account_id']); ?>/<?php echo param_encrypt($sip_data['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>

                                            <?php if (check_account_permission('customer', 'delete')): ?>
                                                <a href="javascript:void(0);"
                                                   onclick=doConfirmDelete('<?php echo $sip_data['id']; ?>','<?php echo 'endpoints' ?>/edit/<?php echo param_encrypt($data['account_id']); ?>','account_sip_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
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
                        <a href="<?php echo base_url('endpoints'); ?>/EPsipAdd/<?php echo param_encrypt($data['account_id']); ?>" ><input type="button" value="Add SIP User" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>
 <?php }?>
        <!---->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Source Number Translation Rules</h2>
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
                                <th class="column-title">Rule Type</th>
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
                                            <td><?php echo $callerid_data['maching_string']; ?></td>
                                            <td><?php echo $callerid_data['remove_string']; ?></td>
                                            <td><?php echo $callerid_data['add_string']; ?></td>
                                            <td><?php echo $callerid_data['display_string']; ?></td>
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
                                            <td><?php echo $callerid_data['maching_string']; ?></td>
                                            <td><?php echo $callerid_data['remove_string']; ?></td>
                                            <td><?php echo $callerid_data['add_string']; ?></td>
                                            <td><?php echo $callerid_data['display_string']; ?></td>

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
                        <a href="<?php echo base_url('endpoints') ?>/editSRCNo/<?php echo param_encrypt($data['account_id'])."$customer_type"; ?>" ><input type="button" value="Manage Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>
        <!----->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Destination Number Translation Rules</h2>
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
                                <th class="column-title">Rule Type</th>
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
                                    <tr >
                                        <td><?php echo $translation_rule_data['maching_string']; ?></td>
                                        <td><?php echo $translation_rule_data['remove_string']; ?></td>
                                        <td><?php echo $translation_rule_data['add_string']; ?></td>
                                        <td><?php echo $translation_rule_data['display_string']; ?></td> 
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
                        <a href="<?php echo base_url('endpoints') ?>/EnDSTrules/<?php echo param_encrypt($data['account_id'])."$customer_type"; ?>" ><input type="button" value="Manage Rules" name="add_link" class="btn btn-primary"></a>
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
                                <th class="column-title">Rule Type</th>
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

                                        <td><?php echo $callerid_data['maching_string']; ?></td>
                                        <td><?php echo $callerid_data['remove_string']; ?></td>
                                        <td><?php echo $callerid_data['add_string']; ?></td>
                                        <td><?php echo $callerid_data['display_string']; ?></td> 

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
                        <a href="<?php echo base_url('endpoints') ?>/editINSRCNo/<?php echo param_encrypt($data['account_id'])."$customer_type"; ?>" ><input type="button" value="Manage Rules" name="add_link" class="btn btn-primary"></a>
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
                                <th class="column-title">Rule Type</th>
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
                                        
                                        <td><?php echo $translation_rule_data['maching_string']; ?></td>
                                        <td><?php echo $translation_rule_data['remove_string']; ?></td>
                                        <td><?php echo $translation_rule_data['add_string']; ?></td>
                                        <td><?php echo $translation_rule_data['display_string']; ?></td> 
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
                        <a href="<?php echo base_url('endpoints') ?>/EnDIDRule/<?php echo param_encrypt($data['account_id'])."$customer_type"; ?>" ><input type="button" value="Manage Rules" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>

            </div>

        </div>



    </div>    

    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
<!--        <div class="x_title">
            <h2>Customer Account Configuration Management Form Ending</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php echo base_url('endpoints') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>-->
    </div>
</div>


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
                    $('#tariff_id_name').html(tariff_str);
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
                            function tax_chnaged()
                            {
                            billing_type = $("#billing_type").val(); //'prepaid','postpaid','netoff'
                                    vat_flag = $("#vat_flag").val(); ///'REVERSE','NONE','SEZ'


                                    /*set to initial status*/
                                    $('#div_id_vat_flag').show();
                                    $('#div_id_tax_type').show();
                                    $('.tax_class').show();
                                    $('#vat_flag').attr('data-parsley-required', 'true');
                                    $('#tax_type').attr('data-parsley-required', 'true');
                                    $('#tax1').attr('data-parsley-required', 'true');
                                    $('#tax2').attr('data-parsley-required', 'true');
                                    $('#tax3').attr('data-parsley-required', 'true');
                                    $('#note_vat_reverse').addClass('hide');
                                    if (billing_type == 'prepaid')
                            {
                            $('#div_id_vat_flag').hide();
                                    $('#vat_flag').attr('data-parsley-required', 'false');
                                    $("#vat_flag").val("NONE");
                            }
                            else if (billing_type == 'postpaid')
                            {
                            if (vat_flag == 'REVERSE' || vat_flag == 'SEZ')
                            {
                            $('#div_id_tax_type').hide();
                                    $('#tax_type').attr('data-parsley-required', 'false');
                                    $("#tax_type").val("exclusive");
                                    $('.tax_class').hide();
                                    $('#tax1').attr('data-parsley-required', 'false');
                                    $('#tax2').attr('data-parsley-required', 'false');
                                    $('#tax3').attr('data-parsley-required', 'false');
                                    $("#tax1").val("0");
                                    $("#tax2").val("0");
                                    $("#tax3").val("0");
                                    if (vat_flag == 'REVERSE')
                            {
                            $('#note_vat_reverse').removeClass('hide');
                            }
                            }

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
                            $('#billing_type, #vat_flag').change(function() {
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