<style type="text/css">
    .height30{height:30px;}
    table.borderless td,table.borderless th{
        border: none !important;
        border-top: none !important;
    }
    .tablebill tr td {
        border: 1px solid #990505;
        border-collapse: collapse;
    }
	
	.padding5 tr td {
		padding:5px;
	}
	
</style>

 <div class="col-md-12 col-sm-12 col-xs-12 row" style="border:0px solid #000;">       

						

                        <?php
                        $logo_directory = 'uploads/invoicelogo';
                        $logo_path = $logo_directory . '/' . $invoice_config['logo'];
                        if ($invoice_config['logo'] != '' && file_exists($logo_path)) {
                            $image_url = base_url() . $logo_path;
                            $header_text1 = '<img src="' . $image_url . '" width="150" align="left" />';
                        } else {
                            $header_text1 = $invoice_config['company_name'];
                        }
                        $header_text2 = nl2br($invoice_config['address']);

                        $address_html = $header_text2;

                        $footer_text = nl2br($invoice_config['footer_text']);
                        ?>




                        <table style="width: 100%;">              
                                <tr style="border-bottom: 1pt solid #990505;">
                                    <td colspan="2"><?php echo $header_text1; ?></td>
                                    <td style="text-align: right;" colspan="2"><?php echo $invoice_config['company_name'] . "<br>" . $address_html; ?></td>
                                </tr>
                        </table>
                        <table style="width: 100%;">
                                <tr style="margin-top: 20px;">
                                    <td style="text-align: center;" colspan="4">
                                        <p><strong>INVOICE</strong></p>
                                    </td>
                                </tr>
                        </table>
                        <table style="width: 100%;">
                                <tr>
                                    <td colspan="2"><?php
                                        if (strlen(trim($customerinvoice_data['company_name'])) > 0)
                                            echo $customerinvoice_data['company_name'] . '<br>';
                                        if (strlen(trim($customerinvoice_data['company_address'])) > 0)
                                            echo $customerinvoice_data['company_address'] . '<br>';
                                        if (strlen(trim($customerinvoice_data['email_address'])) > 0)
                                            echo "Email- " . $customerinvoice_data['email_address'] . '<br>';
                                        if (strlen(trim($customerinvoice_data['phone_number'])) > 0)
                                            echo "Phone- " . $customerinvoice_data['phone_number'] . '<br>';
                                        if (strlen(trim($customerinvoice_data['tax_number'])) > 0)
                                            echo "TAX No- " . $customerinvoice_data['tax_number'] . '<br>';
                                        ?></td>
                                    <td style="text-align: right;" colspan="2"><?php
                                        $bill_date = $due_date = '';
                                        if ($customerinvoice_data['bill_date'] != '') {
                                            $bill_date = DATE(DATE_FORMAT_1, strtotime($customerinvoice_data['bill_date']));
                                            $due_date = strtotime($customerinvoice_data['bill_date']) + (int) $customerinvoice_data['payment_terms'] * 24 * 60 * 60;
                                            $due_date = DATE(DATE_FORMAT_1, $due_date);
                                        }

                                        echo 'Account No: ' . $customerinvoice_data['account_id'] . '<br>';
                                        echo 'Invoice No: ' . $customerinvoice_data['invoice_id'] . '<br>';
                                        echo 'Invoice Date: ' . $bill_date . '<br>';
                                        echo 'Bill Period:  ' . $customerinvoice_data['billing_date_from'] . ' To ' . $customerinvoice_data['billing_date_to'];
                                        // echo 'Due Date: ' . $due_date;
                                        ?></td>
                                </tr>
                        </table>
                        <table style="width: 100%; margin-top:20px;" class="padding5" >                        
                                <tr style="border:1px solid #990505;  ">
                                    <td style="text-align: center;">
                                        <p><strong><?php echo number_format($customerinvoice_data['last_bill_amount'], 2); ?><br/>Last Bill</strong></p>
                                    </td>


                                    <td style="text-align: center;">
                                        <p><strong>+</strong></p>
                                    </td>
                                    <td style="text-align: center;">

                                        <p><strong><?php echo number_format($customerinvoice_data['current_due_amount'], 2); ?><br/>Current Charges</strong></p> 

                                    </td>

                                    <td style="text-align: center;">
                                        <p><strong>-</strong></p>
                                    </td>

                                    <td style="text-align: center;">
                                        <p><strong><?php echo number_format(number_format($customerinvoice_data['payments'], 2) - number_format($customerinvoice_data['refund_amount'], 2), 2); ?><br/>Payment</strong></p>  


                                    </td>

                                    <td style="text-align: center;">
                                        <p><strong>=</strong></p>
                                    </td>
                                    <td style="text-align: center; ">


                                        <p><strong><?php echo number_format($customerinvoice_data['bill_amount'], 2); ?><br/>Bill Amount</strong></p> 
                                    </td> 

                                    <td style="text-align: center; ">


                                        <p><strong><?php echo $due_date; ?><br/>Due Date</strong></p> 
                                    </td> 
                                </tr>
                        </table>

                        <table style="width: 100%;margin-top:20px;" >
                            
                                <tr>
                                    <td style="text-align: left;"><strong>Payment & Support Detail</strong></td>
                                    <td></td>
                                    <td style="text-align: center;"><strong>Usage & Payment Summary</strong></td>
                                </tr>
                                <tr> 
                                    <td style="width: 45%; font-size: 75%; "  >
                                    	<p>
                                        <?php
										echo '<strong>Bank Details-</strong><br>';
										echo "<i>  " . nl2br($invoice_config['bank_detail']) . " </i>";
										?>
										</p>
                                        <br /><br />
                                        <p>
                                        <?php
										echo '<strong>Support Details-</strong><br>';
										echo "<i>  " . nl2br($invoice_config['support_text']) . " </i>";
										?>
                                       </p>                 
                                    
                                        
                                    </td>
                                    <td style="width: 10%;"></td>
                                    <td> 
                                    		<table class="tablebill"  style="width: 100%;" >
                                        
                                                <tr style="background-color:#990505; height:25px;color:#FFF;">
                                                    <td style="text-align: right; width: 20%;" ><strong>Description</strong>&nbsp;</td>
                                                    <td style="text-align: right; width: 10%;"><strong>Amount</strong>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 18px;">
                                                    <td style="height: 18px; text-align: right;">Usage&nbsp;</td>
                                                    <td style="text-align: right; height: 18px;"><?php echo number_format($customerinvoice_data['usage_amount'], 2); ?>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 18px;">
                                                    <td style="height: 18px; text-align: right; ">Tax 1 (<?php echo number_format($customerinvoice_data['tax1'], 2); ?>%)&nbsp;</td>
                                                    <td style="text-align: right; height: 18px; "><?php echo number_format($customerinvoice_data['tax1_amount'], 2); ?>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 18px;">
                                                    <td style="height: 18px; text-align: right; " >Tax 2 (<?php echo number_format($customerinvoice_data['tax2'], 2); ?>%)&nbsp;</td>
                                                    <td style="text-align: right; height: 18px; "><?php echo number_format($customerinvoice_data['tax2_amount'], 2); ?>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 18px;">
                                                    <td style="height: 18px; text-align: right; " >Tax 3 (<?php echo number_format($customerinvoice_data['tax3'], 2); ?>%)&nbsp;</td>
                                                    <td style="text-align: right; height: 18px; "><?php echo number_format($customerinvoice_data['tax3_amount'], 2); ?>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 18px;">
                                                    <td style="height: 18px; text-align: right; "  ><strong>Total Tax</strong>&nbsp;</td>
                                                    <td style="text-align: right; height: 18px; "><strong><?php echo number_format(number_format($customerinvoice_data['tax1_amount'], 2) + number_format($customerinvoice_data['tax2_amount'], 2) + number_format($customerinvoice_data['tax3_amount'], 2), 2); ?></strong>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 18px;">
                                                    <td style="height: 18px; text-align: right; "  ><strong>Total Charges (Usage + Total Tax)</strong>&nbsp;</td>
                                                    <td style="text-align: right; height: 18px; "><strong> <?php echo number_format($customerinvoice_data['current_due_amount'], 2); ?> </strong>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 18px;">
                                                    <td style="height: 18px; text-align: right; " >Payment&nbsp;</td>
                                                    <td style="text-align: right; height: 18px; "><?php echo number_format($customerinvoice_data['payments'], 2); ?>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 20px;">
                                                    <td style="height: 20px; text-align: right; "  >Refund&nbsp;</td>
                                                    <td style="text-align: right; height: 20px;"><?php echo number_format($customerinvoice_data['refund_amount'], 2); ?>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 20px;">
                                                    <td style="height: 20px; text-align: right; " >Last Bill&nbsp;</td>
                                                    <td style="text-align: right; height: 20px; "><?php echo number_format($customerinvoice_data['last_bill_amount'], 2); ?>&nbsp;</td>
                                                </tr>
                                                <tr style="height: 18px;">
                                                    <td style="height: 18px; text-align: right; " ><strong>Billing Amount&nbsp;<br />(Total Charges - (Payment - Refund))</strong>&nbsp;</td>
                                                    <td style="text-align: right; height: 18px; "><strong><?php echo number_format($customerinvoice_data['bill_amount'], 2); ?></strong>&nbsp;</td>
                                                </tr>
                                            
                                        </table>
                                    </td>

                                </tr>
                            </tbody>
                        </table>



                       

                    </div>
                    
                    
                      <div class="clearfix"></div>
                    <?php
					echo '<br><br><br>';
                    if ($customerinvoice_data['itemised_billing'] == 1 && isset($sdr_data['result']) && count($sdr_data['result']) > 0) {
                        echo '<hr style=" border:1px dashed RGB(153,5,5); " />';
                        echo '<p><strong>Itemized bill</strong></p>';

                        $html = '<table class="table table-condensed"  style="border:1px solid #990505; width:100%;">
            <tr style="background-color:#990505; height:30px;color:#FFF;">
                <td>Description</td>
                <td>Quantity</td>
                <td align="right">Amount</td>
            </tr> ';
                        foreach ($sdr_data['result'] as $data_array_single) {
                            $html .= '<tr>
                            <td>' . $data_array_single['item_name'] . '</td>
                            <td width="100">' . number_format($data_array_single['buy_quantity'], 2, '.', ''). '</td>
                            <td width="200" align="right">' . number_format($data_array_single['total_charges'], 2) . '</td>
                        </tr> ';
                        }
                        $html .= '</table><br>';

                        echo $html;
                    }
                    ?>   
                    <?php
                    echo '<p style="text-align:center;margin-top:20px;"><i><small>' . $footer_text . '</small></i></p>';
                    ?>