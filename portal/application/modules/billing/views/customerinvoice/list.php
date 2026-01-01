<?php
if (check_logged_user_group(array('CUSTOMER'))) {
    $title = 'Invoice';
} else {
    $title = 'Customer Invoice';
}
?>

<div class="container-fluid">
    <div class="block-header">
        <h2><?php echo $title; ?></h2>
        <ul class="nav navbar-right panel_toolbox">

        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">

                    <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url('Billing/customerinvoice'); ?>">
                        <input type="hidden" name="search_action" value="search" />
                        <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                        <div class="form-group">
                            <?php if (!check_logged_user_group(array('CUSTOMER'))) { ?>
                                <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                                <div class="col-md-4 col-sm-8 col-xs-12">
                                    <input type="text" name="account_id" id="account_id" value="<?php echo $_SESSION['search_customerinvoice_data2']['s_account_id']; ?>" class="form-control data-search-field" placeholder="Account ID" >
                                </div> 
                            <?php } ?>
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Invoice No</label>
                            <div class="col-md-4 col-sm-8 col-xs-12">
                                <input type="text" name="invoice_id" id="invoice_id" value="<?php echo $_SESSION['search_customerinvoice_data2']['s_invoice_id']; ?>" class="form-control data-search-field" placeholder="Invoice No" >
                            </div> 
                        </div>   
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Billing Date</label>
                            <div class="col-md-4 col-sm-9 col-xs-12">
                                <input type="text" name="billing_date" id="billing_date" class="form-control data-search-field" value="" readonly="readonly" />
                            </div>
  </div>   
                        <div class="form-group">
                            <div class="searchBar col-md-offset-2 mt-2 text-center" >
                                <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary ">                          
                                <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info ">
                            </div>
                        </div>
                    </form> 
                </div>


       


                <div class="body">        
                    <div class="row">  
                        <?php dispay_pagination_row_bsd($total_records, $_SESSION[$search_session_key]['s_no_of_records'], $pagination); ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort1">
                            <thead>
                                <tr class="headings thc">
                                    <?php if (!check_logged_user_group(array('CUSTOMER'))) { ?>
                                        <th class="column-title text-left">Customer (Account ID)</th>
                                    <?php } ?>
                                    <th class="column-title text-left">Invoice No</th>  
                                    <th class="column-title text-left">Bill Amount</th>  
                                    <th class="column-title text-left">Bill Date</th>  
                                    <th class="column-title text-left">Due Date</th>
                                    <th class="column-title no-link last">Actions</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (count($customerinvoice_data['result']) > 0) {
                                    foreach ($customerinvoice_data['result'] as $cli) {
                                        ?>
                                        <tr>
                                            <?php
                                            $account_type = '<span class="label label-warning">' . $cli['account_type'] . '</span>';
                                            if ($cli['account_type'] == 'CUSTOMER') {
                                                $account_type = '<span class="label label-primary">' . $cli['account_type'] . '</span>';
                                            }
                                            if (isset($cli['company_name']) && $cli['company_name'] != '') {
                                                $account = $cli['company_name'] . ' (' . $cli['account_id'] . ')';
                                            } else {
                                                $account = $cli['name'] . ' (' . $cli['account_id'] . ')';
                                            }

                                            $bill_date = '';
                                            $due_date = '';
                                            if ($cli['bill_date'] != '') {
                                                $bill_date = DATE(DATE_FORMAT_1, strtotime($cli['bill_date']));
                                                $due_date = DATE(DATE_FORMAT_1, strtotime($cli['bill_date'] . ' +' . $cli['payment_terms'] . ' day'));
                                            }

                                            if ($cli['status_id'] == 'generated')
                                                $status = ucwords($cli['status_id']);
                                            elseif ($cli['status_id'] == 'mail-sent')
                                                $status = 'Mail Sent';
                                            elseif ($cli['status_id'] == 'no-mail')
                                                $status = 'No Mail';
                                            else
                                                $status = 'Failed';


                                            $last_bill_amount = 0 - $cli['sum']['openingbalance'];
                                            $payment = $cli['sum']['paymentadd'] - $cli['sum']['paymentrefund'];
                                            $current_charges = $cli['sum']['usage'];
                                            $billed_amount = $last_bill_amount + $current_charges - $payment;
                                            ?>
                                            <?php if (!check_logged_user_group(array('CUSTOMER'))) { ?>
                                                <td class="text-left text-info"><?php
                                                    echo $account;
                                                    echo "  $account_type";
                                                    ?></td>
                                            <?php } ?>
                                            <td class="text-left"><?php echo $cli['invoice_id']; ?></td>
                                            <td class="text-left"><?php echo number_format($billed_amount, 2); ?></td>
                                            <td class="text-left"><?php echo $bill_date; ?></td>
                                            <td class="text-left"><?php echo $due_date; ?></td>
                                            <?php
                                            if ($cli['due_status'] == 'PAID') {
                                                $paymentstatus = '<span class="label label-primary">PAID</span>';
                                            } elseif ($cli['due_status'] == 'UNPAID') {

                                                $paymentstatus = '<span class="label label-warning">UNPAID</span>';
                                            } else {
                                                $paymentstatus = '<span class="label label-danger">OVERDUE</span>';
                                            }


                                            if (number_format($cli['bill_amount'], 2) < 0) {
                                                $paymentstatus = '<span class="label label-primary">PAID</span>';
                                            }
                                            ?>

                                            <td class=" last">
                                                <a href="<?php echo base_url(); ?>Billing/customerinvoicedetails/<?php echo param_encrypt($cli['invoice_id']); ?>" title="Details"><i class="fa fa-pencil-square-o"></i></a>

                                            </td>


                                        </tr>

                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="12" align="center"><strong>No Record Found</strong></td>
                                    </tr>
                                    <?php
                                }
                                ?>


                            </tbody>
                        </table>


                    </div>             

                    <div class="row">  
                        <?php dispay_pagination_row_bsd($total_records, $_SESSION[$search_session_key]['s_no_of_records'], $pagination); ?>
                    </div>
                </div>
            </div>
            <script>
                var create_dt = "<?php echo $_SESSION['search_customerinvoice_data2']['s_billing_date']; ?>";
                $(document).ready(function () {
                    $('#OkFilter').click(function () {
                        var no_of_records = $('#no_of_records').val();
                        $('#no_of_rows').val(no_of_records);
                    });
                });
                $(document).ready(function () {
                    showDatatable('table-sort1', [], [0, "asc"]);
                });

                $(document).ready(function () {
                    var today = new Date();
                    var endDate = new Date();
                    endDate = today = moment().subtract(1, 'days');
                    $("#billing_date").daterangepicker({
                        timePicker: !0,
                        locale: {
                            format: "YYYY-MM-DD"
                        },
                        timePicker24Hour: true,
                        maxDate: moment().endOf('days'),
                        ranges: {
                            'Today': [moment().startOf('days'), moment().endOf('days')],
                            'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').startOf('days')],
                            'Last 7 Days': [moment().subtract(7, 'days').startOf('days'), moment().subtract(1, 'days').startOf('days')],
                            /*'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],*/
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                        }
                    });
                    $(document).ready(function () {
                        //showDatatable('table-sort', [5], [1, "asc"]);
                        $('#OkFilter').click(function () {
                            var no_of_records = $('#no_of_records').val();
                            $('#no_of_rows').val(no_of_records);
                        });
                    });

                    if (create_dt == '')
                        $("#billing_date").val(create_dt);
                    else
                        $("#billing_date").val(create_dt);
                });
            </script>