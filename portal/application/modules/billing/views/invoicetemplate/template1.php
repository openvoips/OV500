<style type="text/css">
.height30{height:30px;}
table.borderless td,table.borderless th{
border: none !important;
border-top: none !important;
}
tr td{vertical-align:top;}
.tablebill tr td {
border: 1px solid #F26624;
border-collapse: collapse;
padding:2px;
}
.minheight150{min-height:160px;}
.padding5 tr td {
padding:5px;
}
.pad1 td{
padding:1px;
}
.pad2 td{
padding:2px;
}
.pad5 {
padding:5px;
}
.heading1{font-size:20px;}
.heading2{font-size:14px;}
	
</style>
<?php 
$color_code = '#E4540E';
$opening_balance=0;
$balance_added= $balance_refunded= 0;
$sdr_service_array=array();
if(isset($sdr_data) && count($sdr_data) > 0) 
{
	foreach($sdr_data as $sdr_data_single)
	{
		$service_id = $sdr_data_single['service_id'];
		$item_id = $sdr_data_single['item_id'];
		if($item_id=='OPENINGBALANCE')
		{
			$opening_balance = -1 * $sdr_data_single['total_charges'];
			continue;
		}
		elseif($item_id=='ADDBALANCE')
		{
			$balance_added += $sdr_data_single['total_charges'];
			continue;
		}
		elseif($item_id=='REMOVEBALANCE')
		{
			$balance_refunded += $sdr_data_single['total_charges'];
			continue;
		}elseif($item_id=='ADDCREDIT' or $item_id=='REMOVECREDIT' ){
			continue;
		}
		$sdr_service_array[$service_id][$item_id][] = $sdr_data_single;
	}
}



$total_tax_percentage = $customerinvoice_data['tax1']  +$customerinvoice_data['tax2']+$customerinvoice_data['tax13'];

//  $sdr_service_array[$service_id][$item_id][] = $sdr_data_single;
$itemized_html = '';
$bill_summary_html = '';
$bill_summary_total = 0;
$vat_total=0;
if(count($sdr_service_array) > 0) 
{//$service_id][$item_id
	foreach ($sdr_service_array as $service_id_key =>$sdr_service_array_single) 
	{
		$html = '';
        $html = '<table class="tablebill"  style="width: 100%;" >';
            
				$k = 0;		
				$sub_total =0;
			foreach ($sdr_service_array_single as $item_id_key=>$item_array_multiple) 
			{	
				if($item_id_key=='DIDRENTAL')
				{
					$item_array_multiple = invoice_rearrange_did($item_array_multiple);
				}
				$header_labels_array = get_invoice_header_array($service_id_key);
				
				foreach ($item_array_multiple as $key=>$item_array) 
				{
				
					if($k==0)
					{
						$html .= '<tr style="height:25px;">
								<td colspan="6" style="padding:5px 5px 0px 10px;"><p class="heading2"><strong>'.$item_array['service_name'].'</strong></p></td>
							</tr>    
							<tr style="background-color:'.$color_code.'; height:25px;color:#FFF;">
								<td style="text-align: center; " ><strong>'.$header_labels_array[0].'</strong>&nbsp;</td>
								<td style="text-align: center; "><strong>'.$header_labels_array[1].'</strong>&nbsp;</td>
								<td style="text-align: center; " ><strong>'.$header_labels_array[2].'</strong>&nbsp;</td>
								<td style="text-align: center; "><strong>'.$header_labels_array[3].'</strong>&nbsp;</td>
								<td style="text-align: center; " ><strong>'.$header_labels_array[4].'</strong>&nbsp;</td>
								<td style="text-align: center; "><strong>'.$header_labels_array[5].'</strong>&nbsp;</td>
							</tr>';
							
							
					}

 
					
					if(strtolower($header_labels_array[1])=='minutes')
					{
						$quantity_value = round($item_array['quantity']/60, 2);	
						$service_date_range = '';	
						$description = $item_array['dst'];
					}
					else
					{
						$quantity_value = number_format($item_array['quantity'], 2, '.', '');	
					}
					if($item_array['startdate']!='')
					{
						if(strtolower($header_labels_array[1])=='minutes' ){
							$srt ='';
							 if($item_id_key=='IN')
								 $srt = "Incoming";
							 else
								  $srt = "PSTN ";
							$service_date_range = "";//"<b>". date('j F y',strtotime($item_array['startdate']))."";
						$description = $item_array['dst'].' ('.$service_date_range." ".$srt.')</b>';
						}else{
							$str = '';
							if($item_id_key=='DIDRENTAL')
								$str = "Rental ";
							
							if($item_id_key=='DIDEXTRACHRENTAL')
								$str = "Extra Channels ";
							
							if($item_id_key=='DIDSETUP')
								$str = "Setup ";
							
							$service_date_range = "<b>". date('j F y',strtotime($item_array['startdate'])).' To '.date('j F y',strtotime($item_array['enddate']))."</b>";
						$description = 	$str.$item_array['dst'].' ('.$service_date_range.')';
						}
						
					}
					else
					{
						$service_date_range = '';	
						$description = $item_array['dst'];
					}
				
				
				
  

					//$vat = $item_array['tax1_amount']+$item_array['tax2_amount']+$item_array['tax3_amount'];//change1
					
					$total_inclusive=$item_array['total_charges'];
					$vat = get_tax($total_tax_percentage, $total_inclusive);
					$total_exclusive= $total_inclusive- $vat;
					
					$html .= '<tr style="height:25px;">
								<td style="text-align:center;">'.$description.'</td>
								<td style="text-align:center;">'.$quantity_value.'</td>
								<td style="text-align:right;">'.number_format($item_array['rate'], 2, '.', '').'</td>
								<td style="text-align:right;">'.number_format($total_exclusive, 2, '.', '').'</td>
								<td style="text-align:right;">'.number_format($vat, 2, '.', '').'</td>
								<td style="text-align:right;">'.number_format($total_inclusive, 2, '.', '').'</td>
							</tr>';	
							
				 
					$sub_total += $item_array['total_charges'];
					$vat_total +=$vat;
					$k++;		
				}
				
				
			}
						
			$html .= ' <tr style="height:25px; ">
							<td colspan="6" style="text-align: right;padding:5px 5px 10px 10px; "><strong>Sub Total :</strong> '.number_format($sub_total, 2, '.', '').'</td>							
						</tr>						
			  </table>  ';
			  $itemized_html .='<br><br><br>'.$html;
			  
			//////summary table html/////		
			$bill_summary_html .='<tr style="height: 18px;">
                                <td style="height: 18px; text-align: right;">'.$item_array['service_name'].'&nbsp;</td>
                                <td style="text-align: right; height: 18px;">'.number_format($sub_total, 2, '.', '').'&nbsp;</td>
                            </tr>';  
			$bill_summary_total +=	$sub_total;			
			  
    }
}
?>
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
    $address = nl2br($invoice_config['address']);

   // $address_html = $header_text2;
	
	$header_text2 ='<span class="heading1"><strong>'.$invoice_config['company_name'] . "</strong></span><br>" . $address;

    $footer_text = nl2br($invoice_config['footer_text']);
    
    /////////////
    $bill_date = $due_date = '';
    if ($customerinvoice_data['bill_date'] != '') {
        $bill_date = DATE(DATE_FORMAT_1, strtotime($customerinvoice_data['bill_date']));
        $due_date = strtotime($customerinvoice_data['bill_date']) + (int) $customerinvoice_data['payment_terms'] * 24 * 60 * 60;
        $due_date = DATE(DATE_FORMAT_1, $due_date);
    }
    ?>




    <table style="width: 100%;">              
            <tr >
                <td colspan="2"><?php echo $header_text1; ?></td>
                <td style="text-align: right;" colspan="2"><?php echo $header_text2;?></td>
            </tr>
    </table>
    <table style="width: 100%;">
            <tr style="margin-top: 20px;">
                <td style="text-align: center;" colspan="4">
                    <p class="heading1"><strong>TAX INVOICE</strong></p>
                </td>
            </tr>
    </table>
    <table style="width: 100%;">
        <tr>
            <td style="width:50%;">
                <div style="border:1px solid <?php echo $color_code;?>;" class="pad5">
                <?php 
                if (strlen(trim($customerinvoice_data['company_name'])) > 0)
                        echo '<strong>To: '.$customerinvoice_data['company_name'] . '</strong><br><br><br>';
                if (strlen(trim($customerinvoice_data['tax_number'])) > 0)
                        echo "VAT No: " . $customerinvoice_data['tax_number'] . '<br>';
               if (strlen(trim($customerinvoice_data['company_address'])) > 0)
                        echo $customerinvoice_data['company_address'] . '<br>';	
                        
                echo '<br><br><br>';		
                if (strlen(trim($customerinvoice_data['contact_name'])) > 0)
                {
                        echo "<strong>Attention : " . $customerinvoice_data['contact_name'] . '</strong><br>';
                        if (strlen(trim($customerinvoice_data['phone_number'])) > 0)
                            echo "Contact No: ".$customerinvoice_data['phone_number'] . '<br>';	
                        
                        if (strlen(trim($customerinvoice_data['email_address'])) > 0)
                            echo "Email: ".$customerinvoice_data['email_address'] . '<br>';											
                }
                ?>
                </div>
            </td>
            <td style="width:2%;">&nbsp;</td>
            <td style="width:48%;">
            <div style="border:1px solid <?php echo $color_code;?>;" class="pad5">
                <?php 
                echo '<table style="width: 100%;">';                             		
                    
                    echo '<tr class="pad2"><td><strong>Invoice Date</strong></td><td> ' . $bill_date . '</td></tr>';
                    echo '<tr class="pad2"><td><strong>Due Date</strong></td><td> ' . $due_date . '</td></tr>';
                    echo '<tr class="pad2"><td><strong>Invoice No</strong></td><td> ' . $customerinvoice_data['invoice_id'] . '</td></tr>';
                    echo '<tr class="pad2"><td><strong>Account No</strong></td><td> ' . $customerinvoice_data['account_id'] . '</td></tr>';
                    echo '<tr class="pad2"><td><strong>Bill Period</td><td>' . $customerinvoice_data['billing_date_from'] . ' To ' . $customerinvoice_data['billing_date_to'].'</td></tr>';
                    echo '</table>';
                    ?>
              </div>
              <br />
              <?php  if (isset($account_manager_details) && count($account_manager_details) > 0)
                {?>
              <div style="border:1px solid <?php echo $color_code;?>;" class="pad5">
                <?php 
                 echo "<strong>Account Manager : " . $account_manager_details['name'] . '</strong><br>';
                 if (strlen(trim($account_manager_details['phone'])) > 0)
                       echo "Contact No: ".$account_manager_details['phone'] . '<br>';	
                        
                if (strlen(trim($account_manager_details['emailaddress'])) > 0)
                    echo "Email: ".$account_manager_details['emailaddress'] . '<br>';		
                ?>
              </div>  
              <?php } ?>    
            </td>
        </tr>
    </table>            
    
    <table style="width: 100%; margin-top:20px;" class="padding5" >                        
            <tr style="border:1px solid <?php echo $color_code;?>;  ">
                <td style="text-align: center;">
                    <p><strong><?php echo number_format($opening_balance, 2, '.', ''); ?><br/>Last Bill</strong></p>
                </td>


                <td style="text-align: center;vertical-align: middle;">
                    <p class="heading1"><strong>+</strong></p>
                </td>
                <td style="text-align: center;">
                    <p><strong><?php echo number_format($bill_summary_total, 2, '.', ''); ?><br/>Current Charges</strong></p> 
                </td>

                <td style="text-align: center;vertical-align: middle;">
                    <p class="heading1"><strong>-</strong></p>
                </td>

                <td style="text-align: center;">
                <?php 
				$total_payment = $balance_added - $balance_refunded;
				?>
                    <p><strong><?php echo number_format(($total_payment), 2, '.', ''); ?><br/>Payment</strong></p>  
                </td>

                <td style="text-align: center;vertical-align: middle;">
                    <p class="heading1"><strong>=</strong></p>
                </td>
                <td style="text-align: center; ">
                <?php 
				$bill_amount = $opening_balance + $bill_summary_total - $total_payment;
				?>
                    <p><strong><?php echo number_format($bill_amount, 2, '.', ''); ?><br/>Bill Amount</strong></p> 
                </td> 

                <td style="text-align: center; ">


                    <p><strong><?php echo $due_date; ?><br/>Due Date</strong></p> 
                </td> 
            </tr>
    </table>

    <table style="width: 100%;margin-top:20px;" >
        
            <tr>
                <td style="text-align: left;" ><p class="heading2"><strong>Banking Details</strong></p></td>
                <td></td>
                <td style="text-align: left;"><p class="heading2"><strong>Account Overview / Summary</strong></p></td>
            </tr>
            <tr> 
                <td style="width: 50%; font-size: 95%; "  >
                    
                   
                   
                   
                   
                   <table class="tablebill minheight150"  style="width: 100%;" >
                            <tr >
                                <td style="padding:8px;">
                                
                    <?php
                    echo nl2br($invoice_config['bank_detail']).'<br /><br />' ;
                    echo "<strong>Deposit Ref:</strong> ".$customerinvoice_data['account_id']."<br />
Please quote deposit reference on all transfers<br />
We accept only EFT, Direct Debits or Cedit Card Payments";
                    ?>
                    
                    <br /><br /> <br /><br />
                   
                    <?php
                    
                    if($invoice_config['support_text']!='')
                    {
                        echo '<strong>Support Details-</strong><br>';	
                        echo "<i>  " . nl2br($invoice_config['support_text']) . " </i>";
                    }
                    ?>
                   
                                
                                </td>
                            </tr>
                           
                        
                    </table>
                   
                   
                
                    
                </td>
                <td style="width: 2%;"></td>
                <td style="width: 48%;"> 
                        <table class="tablebill"  style="width: 100%;" >
                    		<tr style="height: 18px;">
                                <td style="height: 18px; text-align: right;"><strong>Balance Brought Forward</strong>&nbsp;</td>
                                <td style="text-align: right; height: 18px;"><?php echo number_format($opening_balance, 2, '.', ''); ?>&nbsp;</td>
                            </tr>
                            <tr style="height: 18px;">
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <?php 
							echo $bill_summary_html;
							?>
                            <?php							
							$vat_total = get_tax($total_tax_percentage, $bill_summary_total);
							?>
                            <tr style="height: 18px;">
                                <td style="height: 18px; text-align: right;"><strong>VAT</strong>&nbsp;</td>
                                <td style="text-align: right; height: 18px;"><strong><?php echo number_format($vat_total, 2, '.', ''); ?></strong>&nbsp;</td>
                            </tr>
                            <tr style="height: 18px;">
                                <td style="height: 18px; text-align: right;"><strong>Total</strong>&nbsp;</td>
                                <td style="text-align: right; height: 18px;"><?php echo number_format($bill_summary_total, 2, '.', '');?>&nbsp;</td>
                            </tr>
                            <tr style="height: 18px;">
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr style="height: 18px;">
                                <td style="height: 18px; text-align: right;"><strong>New balance</strong>&nbsp;</td>
                                <td style="text-align: right; height: 18px;"><?php echo number_format($opening_balance+$bill_summary_total, 2, '.', ''); ; ?>&nbsp;</td>
                            </tr>
                            
                    
                    
                    
                        
                        
                    </table>
                </td>

            </tr>
        </tbody>
    </table>



   

</div>


  <div class="clearfix"></div>
  
  <?php
	if($customerinvoice_data['itemised_billing'] == 1) 
	{
		echo $itemized_html;
	}
?>
            
<?php
echo '<p style="text-align:center;margin-top:20px;"><i><small>' . $footer_text . '</small></i></p>';
?>
