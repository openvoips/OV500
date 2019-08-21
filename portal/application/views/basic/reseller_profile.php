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
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<!--<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">-->

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


<div class="">
    <div class="clearfix"></div>    

    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Profile</h2>
                <ul class="nav navbar-right panel_toolbox">
                    
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br />
                <form  class="form-horizontal form-label-left">


                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Account ID    </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:
                            <?php echo $data['account_id']; ?>
                        </div>
                    </div>





                    <?php if ($data['account_manager'] != '') { ?>
                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Account Manager</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:
                                <?php echo $data['account_manager']; ?>
                            </div>
                        </div>
                    <?php } ?>


                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Currency </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:
                            <?php
                            $user_currency_display = '';
                            foreach ($currency_options as $key => $currency_array) {
                                if ($data['user_currency_id'] == $currency_array['currency_id']) {
                                    $user_currency_display = $currency_array['name'];
                                    break;
                                }
                            }
                            echo $user_currency_display;
                            ?>                
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Billing Type</label> 
                        <div class="col-md-7 col-sm-6 col-xs-12">:                           
                            <?php
                            $billing_type_array = array('prepaid', 'postpaid');
                            $str = '';
                            foreach ($billing_type_array as $key => $billing_type) {

                                if ($data['billing_type'] == $billing_type)
                                    $str = ucfirst($billing_type);
                            }
                            echo $str;
                            ?>                   
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Billing Cycle </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                                   
                            <?php
                            $billing_cycle_array = array('weekly', 'monthly');
                            $str = '';
                            foreach ($billing_cycle_array as $key => $billing_cycle) {
                                if ($data['billing_cycle'] == $billing_cycle)
                                    $str = ucfirst($billing_cycle);
                            }
                            echo $str;
                            ?>                   
                        </div>
                    </div>         


                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Payment Terms </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                                
                            <?php
                            $payment_terms_array = range(1, 30);
                            $str = '';
                            foreach ($payment_terms_array as $key => $payment_terms) {
                                if ($data['payment_terms'] == $payment_terms)
                                    $str = ucfirst($payment_terms);
                            }
                            echo $str;
                            ?>                  
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Tax Type </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                                   
                            <?php
                            $tax_type_array = array('exclusive', 'inclusive');
                            $str = '';
                            foreach ($tax_type_array as $key => $tax_type) {
                                if ($data['tax_type'] == $tax_type)
                                    $str = ucfirst($tax_type);
                            }
                            echo $str;
                            ?>                  
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Tax Number</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:  
                            <?php echo $data['tax_number']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Tax 1 %</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:            
                            <?php
                            if ($data['tax1'] != '' && $data['tax1'] > 0)
                                echo $data['tax1'] . ' %';
                            ?>
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Tax 2 % </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:               
                            <?php if ($data['tax2'] != '' && $data['tax2'] > 0) echo $data['tax2'] . ' %'; ?>
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Tax 3 %</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                 
                            <?php if ($data['tax3'] != '' && $data['tax3'] > 0) echo $data['tax3'] . ' %'; ?>
                        </div>              
                    </div>

                    <?php
                    $vatflag_array = array('NONE', 'REVERSE', 'SEZ');
                    ?>
                    <div class="form-group">
                        <label for="middle-name" class="col-md-4 col-sm-3 col-xs-12">VAT Flag </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                 
                            <?php
                            $str = '';
                            foreach ($vatflag_array as $key => $vat) {
                                if ($data['vat_flag'] == $vat)
                                    $str = $vat;
                            }
                            echo $str;
                            ?>               
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">DP </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:             
                            <?php echo $data['dp']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">CC </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:               
                            <?php echo $data['user_cc']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">CPS </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                
                            <?php echo $data['user_cps']; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12">Caller ID Check </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                  
                            <?php echo ($data['cli_check'] == 1) ? 'Yes' : 'No'; ?>                        

                        </div>                     
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12">Number Check </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                  
                            <?php echo ($data['dialpattern_check'] == 1) ? 'Yes' : 'No'; ?>                        

                        </div>                     
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12">LLR Check </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                  
                            <?php echo ($data['llr_check'] == 1) ? 'Yes' : 'No'; ?>                        

                        </div>                     
                    </div>

                    <div class="form-group">
                        <label for="middle-name" class="col-md-4 col-sm-3 col-xs-12">With-media </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">:                   
                            <?php echo ($data['user_media_rtpproxy'] == 1) ? 'Yes' : 'No'; ?>
                        </div> 
                    </div>




                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border">Registered Address</legend>
                        <div class="form-group">
                            <label class=" col-md-4 col-sm-3 col-xs-12" for="first-name">Name </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:                  
                                <?php echo $data['name']; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class=" col-md-4 col-sm-3 col-xs-12" for="last-name">Company </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:                
                                <?php echo $data['company_name']; ?>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class=" col-md-4 col-sm-3 col-xs-12" for="last-name">Email Address</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:               
                                <?php echo $data['emailaddress']; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class=" col-md-4 col-sm-3 col-xs-12" for="last-name">Address </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:                
                                <?php echo $data['address']; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Phone Number </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:                
                                <?php echo $data['phone']; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Country </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:                               
                                <?php
                                $str = '';
                                foreach ($country_options as $key => $country_array) {
                                    if ($data['country_id'] == $country_array->country_id) {
                                        $str = $country_array->country_name;
                                        break;
                                    }
                                }
                                echo $str;
                                ?>

                            </div>
                        </div>              
                        <?php if ($data['country_id'] == '100') {
                            ?>
                            <div class="form-group">
                                <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">State </label>
                                <div class="col-md-7 col-sm-6 col-xs-12">:                                
                                    <?php
                                    $str = '';
                                    foreach ($state_options as $key => $state_array) {
                                        if ($data['state_code_id'] == $state_array['state_code_id']) {
                                            $str = $state_array['state_name'];
                                            break;
                                        }
                                    }
                                    echo $str;
                                    ?>

                                </div>
                            </div>
                        <?php } ?> 
                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">PIN</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:  
                                <?php echo $data['pincode']; ?> 
                            </div>
                        </div>
                    </fieldset>




                    <fieldset class="scheduler-border"> 
                        <legend class="scheduler-border">Billing Address</legend>

                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Name </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:                 
                                <?php echo $data['billing_name']; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="last-name">Company </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:              
                                <?php echo $data['billing_company_name']; ?>                  
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="last-name">Email Address </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:
                                <?php echo $data['billing_emailaddress']; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="last-name">Address </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:                
                                <?php echo $data['billing_address']; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Phone Number </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:                              
                                <?php echo $data['billing_phone']; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">Country </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:                                    
                                <?php
                                $str = '';
                                foreach ($country_options as $key => $country_array) {
                                    if ($data['billing_country_id'] == $country_array->country_id) {
                                        $str = $country_array->country_name;
                                        break;
                                    }
                                }
                                echo $str;
                                ?>                    
                            </div>
                        </div>              

                        <?php if ($data['billing_country_id'] == '100') { ?>
                            <div class="form-group" id="id_state_div">
                                <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">State </label>
                                <div class="col-md-7 col-sm-6 col-xs-12">:                                
                                    <?php
                                    $str = '';
                                    foreach ($state_options as $key => $state_array) {
                                        if ($data['billing_state_code_id'] == $state_array['state_code_id']) {
                                            $str = $state_array['state_name'];
                                            break;
                                        }
                                    }
                                    echo $str;
                                    ?>

                                </div>
                            </div>
                        <?php } ?>    
                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" for="first-name">PIN</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">:  
                                <?php echo $data['billing_pincode']; ?> 
                            </div>
                        </div>

                    </fieldset>  
                </form>
            </div>
        </div>



    </div>



    <div class="col-md-6 col-sm-12 col-xs-12">

        <!-----------Change Password --------------->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Change Password</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        
                    </ul>
                    <div class="clearfix"></div>
                </div>			
                <div class="x_content">

                    <form action="" method="post" name="user_form" id="user_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">   
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Password </label>
                            <div class="col-md-6 col-sm-6 col-xs-10">
                                <input type="text" name="secret" id="secret" value="<?php echo $data['secret']; ?>" class="form-control col-md-7 col-xs-12" data-parsley-minlength="8" data-parsley-maxlength="30" data-parsley-password="" data-parsley-required="" autocomplete="off">
                            </div>
                            <div class="col-md-1 col-sm-6 col-xs-2">
                                    <!--<button type="button" class="btn btn-primary btn_view_password" data-password-field="secret">&nbsp;<i class="fa fa-eye"></i></button>-->
                            </div>
                        </div>              

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Re-type Password</label>
                            <div class="col-md-6 col-sm-6 col-xs-10">
                                <input type="text" name="repassword" id="repassword" value="" data-parsley-required="" data-parsley-equalto="#secret" class="form-control col-md-7 col-xs-12" autocomplete="off">
                            </div>
                            <div class="col-md-1 col-sm-6 col-xs-2">
                                    <!--<button type="button" class="btn btn-primary btn_view_password" data-password-field="repassword">&nbsp;<i class="fa fa-eye"></i></button>-->
                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-4">			
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                            </div>
                        </div>
                    </form>



                </div>
            </div>
        </div>

        <!-----------Change Password --------------->
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

                var is_ok = $("#user_form").parsley().isValid();
                if (is_ok === true)
                {
                    $('#button_action').val('save');		//alert("dd");
                    $("#user_form").submit();
                } else
                {
                    $('#user_form').parsley().validate();
                }
            })


            $(document).ready(function () {

            });

        </script>

        <!-----------Caller ID Translation Rules--------------->
        <div class="col-md-12 col-sm-6 col-xs-12">
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
                                <th class="column-title">Rule</th>
                                <th class="column-title">Type</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['callerid']) > 0 || count($data['ofcom_callerid']) > 0) {
                                if (count($data['callerid']) > 0) {
                                    foreach ($data['callerid'] as $callerid_data) {
                                        if ($callerid_data['action_type'] == '1')
                                            $status = '<span class="label label-success">Allowed</span>';
                                        else
                                            $status = '<span class="label label-danger">Blocked</span>';
                                        ?>
                                        <tr >
                                            <td><?php echo $callerid_data['display_string']; ?></td>
                                            <td><?php echo $status; ?></td>  
                                        </tr>

                                        <?php
                                    }
                                }
                                if (count($data['ofcom_callerid']) > 0) {
                                    foreach ($data['ofcom_callerid'] as $callerid_data) {
                                        $status = '<span class="label label-info">DST Prefix Based</span>';
                                        ?>
                                        <tr >
                                            <td><?php echo $callerid_data['display_string']; ?></td>                                 			<td><?php echo $status; ?></td>         									   
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

                </div>

            </div>

        </div>

        <!-----------Caller ID Translation Rules--------------->


        <!-----------Number Translation Rules--------------->
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Number Translation Rules</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        

                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Rule</th>
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
                                    <tr >
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

                </div>

            </div>

        </div>

        <!-----------Number Translation Rules--------------->


        <!-----------Dialing Plans--------------->    
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Dialing Plans</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        

                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">                              
                                <th class="column-title">Dialplan</th>                              
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($data['dialplan']) > 0) {
                                foreach ($data['dialplan'] as $dialplan_data) {
                                    ?>
                                    <tr >

                                        <td><?php echo $dialplan_data['dialplan_name'] . ' (' . $dialplan_data['dialplan_id_name'] . ')'; ?></td>
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

            </div>

        </div>

        <!-----------Dialing Plans--------------->     

        <!-----------Incoming Caller ID Translation Rules--------------->    
        <div class="col-md-12 col-sm-6 col-xs-12">
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
                                <th class="column-title">Rule</th>
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

                </div>

            </div>

        </div>

        <!-----------Incoming Caller ID Translation Rules--------------->  


        <!-----------Incoming Number Translation Rules--------------->  
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Incoming Number Translation Rules</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        

                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Rule</th>
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

                </div>

            </div>

        </div>

        <!-----------Incoming Number Translation Rules---------------> 

    </div>    

    <div class="clearfix"></div>