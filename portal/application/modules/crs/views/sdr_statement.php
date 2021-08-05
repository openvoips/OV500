<?php
$customer_dp = $customer_data['dp'];
if (!$customer_dp || $customer_dp == '')
    $customer_dp = 2;

$amount_format = "%." . $customer_dp . "f";

$credit_history = array();
?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Reporting : Account Statement <?php if (isset($customer_data['company_name'])) echo '(' . $customer_data['company_name'] . ')'; ?></h2>	         
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
                <input type="hidden" name="search_action" value="search" />

                <div class="form-group">

                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Invoice</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <select class="form-control data-search-field" id="invoice_id" name="invoice_id">
                            <?php
                            echo ' <option value="">UnBilled Invoice</option>';
                            foreach ($invoice_list as $invoice_id) {

                                $selected = '';
                                if ($invoice_id['invoice_id'] == $_SESSION[$search_session_key]['invoice_id'])
                                    $selected = ' selected="selected"';
                                echo '<option value="' . $invoice_id['invoice_id'] . '" ' . $selected . '>' . $invoice_id['invoice_id'] . '</option>';
                            }
                            ?>					
                        </select>
                    </div>
                    <?php
                    if (check_logged_user_group(array('CUSTOMER', 'RESELLER'))) {
                        ?>
                        <label class="control-label col-md-6 col-sm-3 col-xs-12">Account : <?php echo $searched_account_id; ?></label>
                        <?php
                    } else {
                        ?>     
                        <label class="control-label col-md-2 col-sm-3 col-xs-12 col-md-offset-1">Account ID </label>
                        <div class="col-md-3 col-sm-6 col-xs-12">                	
                            <input type="text" name="account_id" id="account_id" value="<?php echo $searched_account_id; ?>" class="form-control"  data-parsley-required=""  placeholder="Account ID">       
                        </div>
                    <?php } ?>          




                    <div class="searchBar text-right">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                        <?php if (isset($data['result']) && count($data['result']) > 0) { ?>
                            <div class="btn-group pull-right <?php echo $display ?>">
                                <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                    Export <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <?php
                                    $export_format_array = get_export_formats();
                                    foreach ($export_format_array as $export_format) {
                                        if ($export_format == 'pdf' || $export_format == 'xlsx') {
                                            echo '<li><a href="' . base_url() . 'crs/customers/statement/' . param_encrypt($searched_account_id) . '/export/' . param_encrypt($export_format) . '" id="link_export">' . strtoupper($export_format) . '</a></li>';
                                        }
                                    }
                                    ?>                            
                                </ul>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </form>		
        </div>  
    </div>

    <div class="x_content">
        <?php if (isset($searched_account_id)) {
            ?>
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered">
                    <thead>
                        <tr class="headings thc">

                            <th class="column-title">Date</th>
                            <th class="column-title">Activity </th>
                            <th class="column-title">Debit</th>
                            <th class="column-title">Credit</th>							
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        $credit_history['ADDCREDIT'] = $credit_history['REMOVECREDIT'] = array();
                        //$new_openingbalance = ($openingbalance + $balance) - $usage;
                        $openingbalance = $addbalance = $removebalance = $usage = 0;
                        $debit_sum = $credit_sum = 0;
                        $top_html_str = $last_html_str = '';
                        if (count($data['result']) > 0) {
                            foreach ($data['result'] as $sdr_data) {
                                $debit = $credit = 0;
                                $display_text = '';
                                $rule_type = $sdr_data['rule_type'];



                                if (isset($sdr_terms[$rule_type])) {
                                    $term_array = $sdr_terms[$rule_type];

                                    $term_group = $term_array['term_group'];
                                    $display_text = $term_array['display_text'];
                                    $cost_calculation_formula = trim($term_array['cost_calculation_formula']);

                                    $total_cost = round($sdr_data['total_cost'], $customer_dp);



                                    if ($term_group == 'opening') {
                                        if ($cost_calculation_formula == '+') {
                                            $openingbalance = $openingbalance + $total_cost;
                                            $credit = $total_cost;
                                        } elseif ($cost_calculation_formula == '-') {
                                            $openingbalance = $openingbalance - $total_cost;
                                            $debit = $total_cost;
                                        }
                                    } elseif ($term_group == 'balance') {
                                        if ($cost_calculation_formula == '+') {
                                            $addbalance = $addbalance + $total_cost;
                                            $credit = $total_cost;
                                        } elseif ($cost_calculation_formula == '-') {
                                            $removebalance = $removebalance + $total_cost;
                                            $debit = $total_cost;
                                        }
                                    } else {//usage
                                        if ($cost_calculation_formula == '+') {
                                            $usage = $usage + $total_cost;
                                            $credit = $total_cost;
                                        } elseif ($cost_calculation_formula == '-') {
                                            $usage = $usage + $total_cost;
                                            $debit = $total_cost;
                                        }
                                    }
                                }

                                if ($display_text == '')
                                    $display_text = $sdr_data['rule_type'];

                                if ($sdr_data['notes'] != '')
                                    $display_text .= ' (' . $sdr_data['notes'] . ')';

                                /////////////
                                if ($sdr_data['service_startdate'] != '' && $sdr_data['service_stopdate'] != '') {
                                    $start_date_timestamp = strtotime($sdr_data['service_startdate']);
                                    $start_date_display = date(DATE_FORMAT_1, $start_date_timestamp);

                                    $stop_date_timestamp = strtotime($sdr_data['service_stopdate']);
                                    $stop_date_display = date(DATE_FORMAT_1, $stop_date_timestamp);

                                    $display_text .= ' for the period ' . $start_date_display . ' to ' . $stop_date_display;
                                }
                                ////////////	


                                $action_date_timestamp = strtotime($sdr_data['action_date']);
                                $action_date_display = date(DATE_FORMAT_1, $action_date_timestamp);

                                //$sdr_data['service_startdate']							
                                //$sdr_data['service_stopdate']

                                $debit_sum += $debit;
                                $credit_sum += $credit;

                                if ($debit != '')
                                    $debit = sprintf($amount_format, $debit);
                                if ($credit != '')
                                    $credit = sprintf($amount_format, $credit);

                                $tr_html = '<tr>' .
                                        '<td>' . $action_date_display . '</td>' .
                                        '<td>' . $display_text . '</td>' .
                                        '<td align="right">' . $debit . '</td>' .
                                        '<td align="right">' . $credit . '</td>' .
                                        '</tr>';


                                //keep credit history, display at end
                                if (in_array($rule_type, array('ADDCREDIT', 'REMOVECREDIT'))) {
                                    $debit_cost = $credit_cost = '';
                                    if ($rule_type == 'ADDCREDIT')
                                        $debit_cost = $total_cost;
                                    else
                                        $credit_cost = $total_cost;
                                    $str = '<tr>' .
                                            '<td>' . $action_date_display . '</td>' .
                                            '<td>' . $display_text . '</td>' .
                                            '<td align="right">' . $debit_cost . '</td>' .
                                            '<td align="right">' . $credit_cost . '</td>' .
                                            '</tr>';
                                    $credit_history[$rule_type][] = $str;
                                }

                                if ($cost_calculation_formula == '')
                                    continue;

                                if ($term_group == 'opening') {
                                    $top_html_str .= $tr_html;
                                } else {
                                    $last_html_str .= $tr_html;
                                }
                                ?>


                                <?php
                            }
                            echo $top_html_str;
                            echo $last_html_str;



                            $current_balance = $openingbalance + $addbalance - $removebalance - $usage;

                            echo '<tr><td colspan="2" align="right"><strong>Total</strong></td> <td align="right">' . sprintf($amount_format, $debit_sum) . '</td><td align="right">' . sprintf($amount_format, $credit_sum) . '</td></tr>';

                            echo '<tr><td colspan="3" align="right"><strong>Total Payment</strong> </td> <td align="right">';
                            echo sprintf($amount_format, $addbalance);
                            echo '</td></tr>';

                            echo '<tr><td colspan="3" align="right"><strong>Total Refund</strong> </td> <td align="right">';
                            echo sprintf($amount_format, $removebalance);
                            echo '</td></tr>';

                            echo '<tr><td colspan="3" align="right"><strong>Total Charges</strong> </td> <td align="right">';
                            echo sprintf($amount_format, $usage);
                            echo '</td></tr>';


                            echo '<tr><td colspan="3" align="right"><strong>Total Available Balance</strong> </td> <td align="right">';
                            echo '<strong>' . sprintf($amount_format, $current_balance) . '</strong>';
                            echo '</td></tr>';
                        } else {
                            $error_message = '';
                            if (isset($statement_error_message) && $statement_error_message != '')
                                $error_message = $statement_error_message;
                            else
                                $error_message = 'No Record Found';
                            ?>
                            <tr>
                                <td colspan="8" align="center"><strong><?php echo $error_message; ?></strong></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>




            </div>

        <?php } ?>	
    </div>
</div>


<?php
$credit_history_html = '';
foreach ($credit_history as $rule_type => $credit_history_array) {
    if (count($credit_history_array) > 0) {
        foreach ($credit_history_array as $tr) {
            if ($tr != '')
                $credit_history_html .= $tr;
        }
    }
}
if ($credit_history_html != '') {
    ?>

    <div class="x_panel">
        <div class="x_content">
            <div class="table-responsive">
                <h4>Credit History</h4>
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">

                            <th class="column-title">Date</th>
                            <th class="column-title">Activity </th>
                            <th class="column-title">Debit</th>
                            <th class="column-title">Credit</th>							
                        </tr>
                    </thead>		
                    <tbody>

                        <?php echo $credit_history_html; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>   

<?php } ?>     



</div>


<!--            <div role="tabpanel" class="tab-pane fade <?php echo $tab_invoice_class; ?>" id="tab_invoice" aria-labelledby="invoices"> 				invoice section section

                <div class="x_panel ">
                    <div class="x_title">
                        <h2>Invoices</h2>			
                        <div class="clearfix"></div>                
                    </div>
                    <div class="x_content">
                        <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
                            <input type="hidden" name="invoice_search_action" value="search" />

                            <div class="form-group">

                                <label class="control-label col-md-1 col-sm-3 col-xs-12">Month</label>
                                <div class="col-md-2 col-sm-9 col-xs-12">
                                    <select class="form-control data-search-field" id="yearmonth" name="yearmonth">
<?php
$date = date('Y-m-d');
for ($i = 0; $i <= 11; $i++) {
    $yearmonth_value = date('Ym', strtotime($date));
    $yearmonth_display = date('F Y', strtotime($date));
    $date = date('Y-m-d', strtotime($date . 'first day of last month'));


    $year = substr($yearmonth_value, 0, 4);
    $month = substr($yearmonth_value, -2);
    if (intval($year) < 2019 || (intval($year) == 2019 && intval($month) < 4)) {//no invoice before April 2019
        continue;
    }

    $selected = '';
    if ($yearmonth_value == $_SESSION['search_sdr_summary_data']['s_yearmonth_invoice'])
        $selected = ' selected="selected"';
    echo '<option value="' . $yearmonth_value . '" ' . $selected . '>' . $yearmonth_display . '</option>';
}
?>					
                                    </select>
                                </div>
<?php
if (check_logged_user_group(array('CUSTOMER', 'RESELLER'))) {
    ?>
                                            <label class="control-label col-md-6 col-sm-3 col-xs-12">Account : <?php echo $searched_account_id; ?></label>
    <?php
} else {
    ?>     
                                            <label class="control-label col-md-2 col-sm-3 col-xs-12 col-md-offset-1">Account ID </label>
                                            <div class="col-md-3 col-sm-6 col-xs-12">                	
                                                <input type="text" name="account_id" id="account_id" value="<?php echo $searched_account_id; ?>" class="form-control"  data-parsley-required="" tabindex="<?php echo $tab_index++; ?>" placeholder="Account ID">       
                                            </div>
<?php } ?>          




                                <div class="searchBar">
                                    <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                    <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">
                                </div>
                            </div>
                        </form>		

<?php
/* echo '<pre>';
  print_r($payment_history);
  echo '</pre>'; */
?>

                        <div class="table-responsive">
                            <table class="table table-striped jambo_table bulk_action table-bordered">
                                <thead>
                                    <tr class="headings thc">                      
                                        <th class="column-title">Date </th>
                                        <th class="column-title">Invoice Number </th>
                                        <th class="column-title text-right">Amount </th>
                                        <th class="column-title">&nbsp; </th>
                                    </tr>
                                </thead>
                                <tbody>
<?php
if (isset($payment_history) && count($payment_history) > 0) {
    foreach ($payment_history as $payment_data) {
        $create_dt_display = '';


        $payment_option = $payment_data['payment_option'];
        if ($payment_data['create_dt'] != '') {
            $create_dt = $payment_data['create_dt'];
            $create_dt_timestamp = strtotime($create_dt);
            $create_dt_display = date(DATE_FORMAT_1, $create_dt_timestamp);
        }

        $amount = number_format($payment_data['amount'], $customer_dp, '.', '');
        ?>
                                                            <tr >                          
                                                                <td class="column-title" ><?php echo $create_dt_display; ?></td>                                 <td ><?php echo $payment_data['file_name']; ?></td>
                                                                <td class="text-right" ><?php echo $amount; ?></td>  
                                                                <td class="text-center" >
        <?php
        $account_id = $payment_data['account_id'];
        // $dir_path = '/home/telcoportal/webroot/switch/mt/uploads/' . strtolower(SITE_SUBDOMAIN) . '/payment_receipt/' . $account_id . '/';
        $dir_path = '';
        if ($payment_data['file_name'] != '') {
            $file_name = $payment_data['file_name'] . '.pdf';
            $file_path = $dir_path . $file_name;

            if (file_exists($file_path)) {
                ?>

                                                                                            <a href="<?php echo base_url('download/payment_receipt/' . param_encrypt($account_id) . '/' . param_encrypt($file_name)); ?>">download</a>
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
                </div>
            </div>-->
</div>  
<!--</div>-->




</div>
<script>
    $(document).ready(function () {
        showDatatable('table-sort', [], [1, "asc"]);
    });
</script> 