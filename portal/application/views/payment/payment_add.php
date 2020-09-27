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
<?php
if ($account_result['currency']['name'] == 'INR')
    $max_emergency_limit = 5000;
else
    $max_emergency_limit = 50;
//echo '<pre>';
//print_r($account_result);
//echo '</pre>';
//echo '<pre>';
//print_r($account_result);
//
//print_r($payment_option_array);
//echo '</pre>';
//print_r($credit_scheduler_result);
if (isset($account_result)) {
    $is_account_details_exists = true;
    $dp = 4;
    if (in_array(strtolower($account_result['account_type']), array('user', 'reseller')) && $account_result['dp'] != '')
        $dp = $account_result['dp'];
}else {
    $is_account_details_exists = false;
}
$csrf = array(
    'name' => $this->security->get_csrf_token_name(),
    'hash' => $this->security->get_csrf_hash()
);
?>

<div class="col-md-12 col-sm-12 col-xs-12 right">
    <div class="ln_solid"></div>
    <div class="x_title">
        <h2>Payment Management & Processing</h2>
        <ul class="nav navbar-right panel_toolbox">             
            <li>
                <?php $tab_index = 0;
                $redirect_page = 'customers';
                ?>

                <a href="<?php echo base_url() . $redirect_page; ?>"><button class="btn btn-danger" type="button"  tabindex="<?php echo $tab_index++; ?>">Back to Customer Listing Page</button></a>


            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<?php if (!$is_account_details_exists): ?>

    <div class="col-md-8 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Search User</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">


                <form action="" method="post" name="search_form" id="search_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="<?php echo $csrf['name']; ?>" value="<?php echo $csrf['hash']; ?>" />
                    <input type="hidden" name="action" value="OkSearchData"> 
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="Account ID">Account ID</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="search_account_id" id="search_account_id" value="<?php echo set_value('search_account_id') ?>"  class="form-control col-md-7 col-xs-12" data-parsley-required="">
                        </div>
                    </div>             
                    <div class="form-group">
                        <div class="col-md-12 col-sm-6 col-xs-12 col-md-offset-4">			
                            <button type="button" id="btnSearch" class="btn btn-success">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>       
    </div>    


    <script>
        /*search form*/
        $('#btnSearch').click(function () {
            $('#search_form').parsley().reset();
            var is_ok = $("#search_form").parsley().isValid();
            if (is_ok === true)
            {
                if (is_ok === true)
                {
                    $("#search_form").submit();
                }
            } else
            {
                $('#search_form').parsley().validate();
            }

        })
    </script>    

<?php else: ?>  
    <?php
    if ($account_result['account_id'] == get_logged_account_id() || check_logged_account_type(array('ACCOUNTS'))) {
        $add_payment_class = 'hide';
        $current_balance_class = 'col-md-12 col-sm-12 col-xs-12';
    } else {
        $add_payment_class = '';
        $current_balance_class = 'col-md-5 col-sm-6 col-xs-12';
    }
    ?>

    <div class="col-md-7 col-sm-6 col-xs-12 <?php echo $add_payment_class; ?>">
        <div class="x_panel">
            <div class="x_title">
                <h2>Manage Payments</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <br />
                <form action="" method="post" name="payment_form" id="payment_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="<?php echo $csrf['name']; ?>" value="<?php echo $csrf['hash']; ?>" />
                    <input type="hidden" name="button_action" id="button_action" value="">
                    <input type="hidden" name="action" value="OkSaveData"> 
                    <input type="hidden" name="account_id" value="<?php echo $account_result['account_id']; ?>"/>
                    <input type="hidden" name="account_id" value="<?php echo $account_result['account_id']; ?>"/>            
                    <input type="hidden" name="account_type" value="<?php echo $account_result['account_type']; ?>"/>  

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Account ID
                        </label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" name="username_display" id="username_display" value="<?php echo $account_result['account_id']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>             

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Option <span class="required">*</span></label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <select name="payment_option" id="payment_option_id" data-parsley-required="" class="form-control">
                                <option value="">Select</option>                    
                                <?php
                                $str = '';
                                foreach ($payment_options['result'] as $payment_option_array) {
                                    $selected = ' ';
                                    if (set_value('payment_option') == $payment_option_array['option_id'])
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $payment_option_array['option_id'] . '" ' . $selected . '>' . $payment_option_array['option_name'] . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Collection Method<span class="required">*</span></label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <select name="collection_option" id="collection_option" data-parsley-required="" class="form-control">
                                <option value="">Select</option>    

                            </select>
                        </div>
                    </div>     


                    <div id="divtransactiondetails" class="form-group hide">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Transaction Details <span class="required">*</span> </label>
                        <div class="col-md-8 col-sm-6 col-xs-12">                	
                            <input type="text" class="form-control" name="transactiondetails" id="transactiondetails" value="">       
                        </div>
                    </div>              

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Amount <span class="required">*</span>   </label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" name="amount" id="amount" value=""  data-parsley-required="" data-parsley-pattern="/^\d+(\.\d{1,<?php echo $dp; ?>})?$/" data-parsley-pattern-message="Positive number with maximum <?php echo $dp; ?> decimal"  class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>              

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Payment Date<span class="required">*</span></label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control" name="paid_on" id="paid_on" data-parsley-required="" readonly="readonly" value="">                   
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Notes </label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <textarea name="notes" id="notes" class="form-control col-md-7 col-xs-12"></textarea>
                        </div>
                    </div> 

                    <div id="divapprovedby" class="form-group hide">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Approved By <span class="required">*</span> </label>
                        <div class="col-md-8 col-sm-6 col-xs-12">                	
                            <input type="text" class="form-control" name="approvedby" id="approvedby" value="">       
                        </div>
                    </div>                   
                    <div id="div_credit_scheduler" class="form-group hide">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Credit Revert Scheduler 
    <?php if ($account_result['billing_type'] == 'prepaid') echo '<span class="required">*</span>'; ?>
                        </label>
                        <div class="col-md-8 col-sm-6 col-xs-12">                   
                            <select name="credit_scheduler_hour" id="credit_scheduler_hour" class="form-control">                     <option value="">Select</option>                  
                                <?php
                                if ($account_result['billing_type'] == 'prepaid') {
                                    $credit_scheduler_array = array(4 => '4 Hours', 12 => '12 Hours', 24 => '24 Hours', 48 => '2 Days', 72 => '3 Days',96 => '4 Days',168=>'7 Days');
                                } else {
                                    $credit_scheduler_array = array(4 => '4 Hours', 12 => '12 Hours', 24 => '24 Hours', 48 => '2 Days', 72 => '3 Days',168 => '7 Days', 360 => '15 Days', 720 => '30 Days', 1080 => '45 Days',1440=>'60 Days');
                                }
                                $str = '';
                                foreach ($credit_scheduler_array as $key => $credit_scheduler) {
                                    $selected = ' ';
                                    if (set_value('credit_scheduler_hour', 12) == $key)
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $key . '" ' . $selected . '>' . $credit_scheduler . '</option>';
                                }
                                echo $str;
                                ?>
                            </select>             	      
                        </div>
                    </div> 



                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-12 col-sm-6 col-xs-12 col-md-offset-4">			
    <!--                                <a href="<?php echo base_url() . $redirect_page; ?>"><button class="btn btn-primary" type="button"  tabindex="<?php echo $tab_index++; ?>">Cancel</button></a>-->
                            <button type="button" id="btnSave" class="btn btn-success">Save</button>
                            <button type="button" id="btnSaveClose" class="btn btn-info">Save & Close</button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div class="<?php echo $current_balance_class; ?>">
        <div class="x_panel">
            <div class="x_title">
                <h2>Current Balance</h2>
                <ul class="nav navbar-right panel_toolbox">


                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <table class="table table-bordered">               
                    <tr >
                        <th>Account Type</th>
                        <th>
                            <?php
                            $billing_type = $account_result['billing_type'];
                            $billing_type_array = array('prepaid' => 'Prepaid', 'postpaid' => 'Postpaid', 'netoff' => 'Net-Off');
                            if (isset($billing_type_array[$billing_type]))
                                echo $billing_type_array[$billing_type];
                            ?>
                        </th>
                    </tr>
                    <tr >
                        <th>Currency </th>
                        <th><?php echo $account_result['currency']['name']; ?> </th>
                    </tr>
                    <tr >
                        <th>Balance</th>
                        <th><?php echo number_format(-$account_result['balance']['balance'], $dp, '.', ''); ?> </th>
                    </tr>
                    <tr >
                        <th>Temporary Credit </th>
                        <th><?php echo number_format($account_result['balance']['credit_limit'], $dp, '.', ''); ?> </th>
                    </tr>
                    <tr >
                        <th>Available Balance</th>
                        <th><?php echo number_format($account_result['balance']['usable_balance'], $dp, '.', ''); ?> </th>
                    </tr>

                </table>

            </div>

        </div>         


    <?php if (isset($credit_scheduler_result) && count($credit_scheduler_result['result']) > 0): ?>            
            <div class="x_panel">
                <div class="x_title">
                    <h2>Credit Scheduler</h2>
                    <ul class="nav navbar-right panel_toolbox">


                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Amount</th>
                                <th class="column-title">Date</th>
                                <th class="column-title">&nbsp;</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (count($credit_scheduler_result['result']) > 0) {
                                foreach ($credit_scheduler_result['result'] as $credit_scheduler_array) {
                                    $execution_date_display = '';
                                    if ($credit_scheduler_array['execution_date'] != '') {
                                        $execution_date = $credit_scheduler_array['execution_date'];
                                        $execution_date_timestamp = strtotime($execution_date);
                                        $execution_date_display = date(DATE_FORMAT_2, $execution_date_timestamp);
                                    }
                                    ?>
                                    <tr>                           
                                        <td ><?php echo $credit_scheduler_array['credit_amount']; ?></td>
                                        <td ><?php echo $execution_date_display; ?></td> 
                                        <td class="text-right">
                                            <?php
                                            if ($account_result['account_id'] != get_logged_account_id()) {
                                                if ($credit_scheduler_array['is_emergency_credit'] == 'N') {
                                                    ?>
                                                    <a href="javascript:void(0);"  onclick=doConfirmDelete('<?php echo $credit_scheduler_array['id']; ?>','payment/delete_scheduler','<?php echo $account_result['account_id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </td>      
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

                </div>

            </div>
    <?php endif; ?>


        <?php
        if (check_logged_account_type('CREDITCONTROL')) {
            ?>
            <div class="x_panel">
                <div class="x_title">
                    <h2>Max Credit Limit</h2>
                    <ul class="nav navbar-right panel_toolbox">


                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <form action="" method="post" name="maxcredit_form" id="maxcredit_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="<?php echo $csrf['name']; ?>" value="<?php echo $csrf['hash']; ?>" />
                        <input type="hidden" name="button_action" id="button_action_maxcredit" value="">
                        <input type="hidden" name="action" value="OkSaveMaxcredit"> 
                        <input type="hidden" name="account_id" value="<?php echo $account_result['account_id']; ?>"/>  

                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-6 col-sm-12 col-xs-12">Current Limit</label>
                            <div class="col-md-6 col-sm-12 col-xs-12 control-label">
        <?php echo number_format($account_result['balance']['maxcredit_limit'], $dp, '.', ''); ?>				  
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12 col-sm-12 col-xs-12">               
                                <input type="text" name="maxcredit_limit" id="maxcredit_limit" value=""  data-parsley-required="" data-parsley-pattern="/^\d+(\.\d{1,<?php echo $dp; ?>})?$/" data-parsley-pattern-message="Positive number with maximum <?php echo $dp; ?> decimal" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">

                            </div>
                        </div>

                        <div class="ln_solid"></div>              

                        <div class="form-group">
                            <div class="col-md-12 col-sm-6 col-xs-12 ">	
                                <?php if (check_account_permission('enduser', 'edit')): ?>		
                                    <button type="button" id="btnSaveMaxcredit" class="btn btn-success btn-block" tabindex="<?php echo $tab_index++; ?>">Update</button>
        <?php endif; ?>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

    <?php } ?>   


    </div>


    <div class="col-md-12 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Payment History</h2>
                <ul class="nav navbar-right panel_toolbox">


                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <table class="table table-striped jambo_table table-bordered">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title" width="150">Option </th>
                            <th class="column-title " width="120">Amount </th>
                            <th class="column-title" width="150">Payment Date </th>
                            <th class="column-title">Notes </th>
                            <th class="column-title" width="150">Entry Date </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (count($account_result['payment_history']) > 0) {
                            foreach ($account_result['payment_history'] as $payment_data) {
                                $paid_on_display = '';
                                $create_dt_display = '';
                                if ($payment_data['paid_on'] != '') {
                                    $paid_on = $payment_data['paid_on'];
                                    $paid_on_timestamp = strtotime($paid_on);
                                    $paid_on_display = date(DATE_FORMAT_2, $paid_on_timestamp);
                                }
                                if ($payment_data['create_dt'] != '') {
                                    $create_dt = $payment_data['create_dt'];
                                    $create_dt_timestamp = strtotime($create_dt);
                                    $create_dt_display = date(DATE_FORMAT_2, $create_dt_timestamp);
                                }

                                $amount = number_format($payment_data['amount'], $dp, '.', '')
                                ?>
                                <tr >                           
                                    <td><?php echo $payment_data['payment_option']; ?></td>
                                    <td class="text-right"><?php echo $amount; ?></td>         
                                    <td><?php echo $paid_on_display; ?></td>                                   
                                    <td><?php echo $payment_data['notes']; ?></td>
                                    <td class=" last"><?php echo $create_dt_display; ?></td>  
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

            </div>

        </div>

        <div class="col-md-12 col-sm-12 col-xs-12 right">
            <div class="ln_solid"></div>
            <div class="x_title">
                <h2>Payment Management & Processing</h2>
                <ul class="nav navbar-right panel_toolbox">             
                    <li>
    <?php $redirect_page = 'customers'; ?>

                        <a href="<?php echo base_url() . $redirect_page; ?>"><button class="btn btn-danger" type="button"  tabindex="<?php echo $tab_index++; ?>">Back to Customer Listing Page</button></a>


                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>    
    <?php
    $billing_type = $account_result['billing_type'];
    ?>            
    <link href="<?php echo base_url() ?>theme/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css" rel="stylesheet">
    <script src="<?php echo base_url() ?>theme/vendors/moment/min/moment.min.js"></script>   
    <script src="<?php echo base_url() ?>theme/vendors/moment/min/moment-timezone-with-data.js"></script>
    <script src="<?php echo base_url() ?>theme/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script> 

    <script>
                                            var account_type = "<?php echo $account_result['account_type']; ?>";
                                            var billing_type = "<?php echo $account_result['billing_type']; ?>";
                                            var cur_date = moment().tz("GMT").format('DD-MM-YYYY HH:mm:ss');
                                            var month_start_date = moment().startOf('month');
                                            var end = moment();
                                            $('#paid_on').datetimepicker({
                                                format: 'DD-MM-YYYY HH:mm:ss',
                                                ignoreReadonly: true,
                                                maxDate: end,
                                                minDate: month_start_date,
                                                useCurrent: true,
                                                /*	sideBySide:true*/
                                            });

                                            $('#paid_on').val(cur_date);
                                            $('#btnSave, #btnSaveClose').click(function () {
                                                $('#payment_form').parsley().reset();

                                                var is_ok = $("#payment_form").parsley().isValid();
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
                                                        $("#payment_form").submit();
                                                    }
                                                } else
                                                {
                                                    $('#payment_form').parsley().validate();
                                                }

                                            });


                                            function collection_option_changed()
                                            {
                                                var payment_option = $('#payment_option_id').val();
                                                var collection_option = $('#collection_option').val();

                                                var action_type = 'hide';
                                                if (payment_option == 'ADDCREDIT' || payment_option == 'REMOVECREDIT') {

                                                } else if (payment_option == 'ADDNETOFFBALANCE' || payment_option == 'REMOVENETOFFBALANCE') {
                                                    action_type = 'show';
                                                } else
                                                {
                                                    if (collection_option != 'Cash' && collection_option != 'Cash Refund' && collection_option != 'Test Balance' && collection_option != '')
                                                    {
                                                        action_type = 'show';
                                                    }
                                                }

                                                if (action_type == 'hide')
                                                {
                                                    $("#divtransactiondetails").addClass("hide");
                                                    $('#transactiondetails').val('');
                                                    $('#transactiondetails').attr('data-parsley-required', 'false');
                                                } else
                                                {
                                                    $("#divtransactiondetails").removeClass("hide");
                                                    $('#transactiondetails').attr('data-parsley-required', 'true');
                                                }

                                                if (collection_option == 'Emergency Credits')
                                                    $('#amount').attr('data-parsley-max', "<?php echo $max_emergency_limit; ?>");
                                                else
                                                    $('#amount').removeAttr('data-parsley-max');
                                            }

                                            function payment_option_changed()
                                            {
                                                var payment_option = $('#payment_option_id').val();


                                                if (payment_option == 'ADDCREDIT') {

                                                    $("#divapprovedby").removeClass("hide");
                                                    $('#approvedby').attr('data-parsley-required', 'true');
                                                    $("#div_credit_scheduler").removeClass("hide");
                                                    if (billing_type == 'prepaid')
                                                    {
                                                        $('#credit_scheduler_hour').attr('data-parsley-required', 'true');
                                                    }

                                                } else if (payment_option == 'REMOVECREDIT') {

                                                    $("#divapprovedby").removeClass("hide");
                                                    $('#approvedby').attr('data-parsley-required', 'true');
                                                    $("#div_credit_scheduler").addClass("hide");
                                                    if (billing_type == 'prepaid')
                                                    {
                                                        $('#credit_scheduler_hour').attr('data-parsley-required', 'false');
                                                    }
                                                } else {

                                                    $("#divapprovedby").addClass("hide");
                                                    $('#approvedby').attr('data-parsley-required', 'false');
                                                    $("#div_credit_scheduler").addClass("hide");
                                                    if (billing_type == 'prepaid')
                                                    {
                                                        $('#credit_scheduler_hour').attr('data-parsley-required', 'false');
                                                    }
                                                }

                                                var option_options = '<option value="">Select</option>';
                                                if (payment_option == 'ADDBALANCE')
                                                {
                                                    option_options = option_options + '<option value="Cash">Cash</option><option value="Bank Transfer Payment">Bank Transfer Payment</option><option value="Paypal Payment">Paypal Payment</option><option value="Debit / Credit Card Payment">Debit / Credit Card Payment</option>';

                                                } else if (payment_option == 'REMOVEBALANCE')
                                                {

                                                    option_options = option_options + '<option value="Cash Refund">Cash Refund</option><option value="Refund  Bank Transfer Payment">Refund  Bank Transfer Payment</option><option value="Refund Paypal Payment">Refund Paypal Payment</option><option value="Refund Debit / Credit Card Payment">Refund Debit / Credit Card Payment</option><option value="Cheque Refund">Cheque Refund</option><option value="Test Balance">Test Balance</option>';

                                                } else if (payment_option == 'ADDCREDIT')
                                                {
                                                    option_options = option_options + '<option value="Temporary Credits">Temporary Credits</option><option value="Emergency Credits">Emergency Credits</option>';
                                                } else if (payment_option == 'REMOVECREDIT')
                                                {
                                                    option_options = option_options + '<option value="Reduce Credit">Reduce Credit</option>';
                                                } else if (payment_option == 'ADDNETOFFBALANCE')
                                                {
                                                    option_options = option_options + '<option value="Cash">Cash</option><option value="Against Invoice">Against Invoice</option>';
                                                } else if (payment_option == 'REMOVENETOFFBALANCE')
                                                {
                                                    option_options = option_options + '<option value="Cash">Cash</option><option value="Against Invoice">Against Invoice</option>';
                                                } else if (payment_option == 'BALANCETRANSFERADD')
                                                {
                                                    option_options = option_options + '<option value="Balance Transfer">Balance Transfer</option>';
                                                } else if (payment_option == 'BALANCETRANSFERREMOVE')
                                                {
                                                    option_options = option_options + '<option value="Balance Transfer">Balance Transfer</option>';
                                                }





                                                $('#collection_option').html(option_options);

                                                $('#collection_option').trigger('change');

                                            }

                                            $('#collection_option').change(function () {

                                                collection_option_changed();

                                            });
                                            $(document).ready(function () {
                                                payment_option_changed();
                                                collection_option_changed();
                                            });


                                            $('#payment_option_id').change(function () {

                                                payment_option_changed();

                                                //alert(payment_option);
                                            });



                                            $('#btnSaveMaxcredit').click(function () {
                                                $('#maxcredit_form').parsley().reset();

                                                var is_ok = $("#maxcredit_form").parsley().isValid();
                                                if (is_ok === true)
                                                {
                                                    if (is_ok === true)
                                                    {
                                                        $("#maxcredit_form").submit();
                                                    }
                                                } else
                                                {
                                                    $('#maxcredit_form').parsley().validate();
                                                }

                                            });
    </script>
<?php endif; ?>  