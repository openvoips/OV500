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
strong{font-weight: 700; font-size:11px; }
	
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
		}
		$sdr_service_array[$service_id][$item_id][] = $sdr_data_single;
	}	
}



$total_tax_percentage = $customerinvoice_data['tax1']  +$customerinvoice_data['tax2']+$customerinvoice_data['tax13'];

$itemized_html = '';
$bill_summary_html = '';
$bill_summary_total = 0;

$page_counter = 0;
$max_row_limit=36;
if(count($sdr_service_array) > 0) 
{
	$table_start = '<table class="tablebill"  style="width: 100%;border: 1px solid '.$color_code.';"  cellpadding="5" >';
	$table_end = '</table>';
	foreach ($sdr_service_array as $service_id_key =>$sdr_service_array_single) 
	{
		$html = ''; 		
		$item_counter = 0;		
		$k = 0;		
		$sub_total =0;
		$total_item_count = calculate_total_item($sdr_service_array_single);
		
		foreach ($sdr_service_array_single as $item_id_key=>$item_array_multiple) 
		{	
			if($item_id_key=='DIDRENTAL')
			{
				$item_array_multiple = invoice_rearrange_did($item_array_multiple);//revert
			}
			$header_labels_array = get_invoice_header_array($service_id_key);		
			
			
			foreach ($item_array_multiple as $key=>$item_array) 
			{			
				if($k==0)
				{
					$page_counter++;
					$theader = '<tr style="height:25px;">
							<td colspan="6" style="padding:5px 5px 0px 10px;border:1px solid '.$color_code.';"><p class="heading2"><strong>'.$item_array['service_name'].'</strong></p></td>
						</tr> ';
					$page_counter++;	   
					$theader .= '<tr style="background-color:'.$color_code.'; height:25px;color:#FFF;">
							<td style="text-align: center;border:1px solid '.$color_code.';" width="40%" ><strong>'.$header_labels_array[0].'</strong>&nbsp;</td>
							<td style="text-align: center;border:1px solid '.$color_code.'; " width="12%" ><strong>'.$header_labels_array[1].'</strong>&nbsp;</td>
							<td style="text-align: center;border:1px solid '.$color_code.'; " width="12%" ><strong>'.$header_labels_array[2].'</strong>&nbsp;</td>
							<td style="text-align: center;border:1px solid '.$color_code.'; " width="12%" ><strong>'.$header_labels_array[3].'</strong>&nbsp;</td>
							<td style="text-align: center;border:1px solid '.$color_code.'; " width="12%" ><strong>'.$header_labels_array[4].'</strong>&nbsp;</td>
							<td style="text-align: center;border:1px solid '.$color_code.'; " width="12%" ><strong>'.$header_labels_array[5].'</strong>&nbsp;</td>
						</tr>';
					$html .=  $table_start;  	
					$html .= $theader;					
				}
				
				if(strtolower($header_labels_array[1])=='minutes')
				{
					$srt ='';
							 if($item_id_key=='IN')
								 $srt = "Incoming";
							 else
								  $srt = "PSTN ";
							  
					$quantity_value = round($item_array['quantity']/60, 2);	
					$service_date_range = '';	
					$description = $item_array['dst']." ($srt)";
				}
				else
				{
					
					
				 
							$str = '';
							if($item_id_key=='DIDRENTAL')
								$str = "Rental ";
							
							if($item_id_key=='DIDEXTRACHRENTAL')
								$str = "Extra Channels ";
							
							if($item_id_key=='DIDSETUP')
								$str = "Setup ";
							
			    		$service_date_range = "<b>". date('j F y',strtotime($item_array['startdate'])).' To '.date('j F y',strtotime($item_array['enddate']))."</b>";					
					$quantity_value = number_format($item_array['quantity'], 2, '.', '');	
					$service_date_range = "<b>". date('j F y',strtotime($item_array['startdate'])).' To '.date('j F y',strtotime($item_array['enddate']))."</b>";
					
					$description = $str.$item_array['dst'].' ('.$service_date_range.')';
				
				}


				
				
				$total_inclusive=$item_array['total_charges'];
				$vat = get_tax($total_tax_percentage, $total_inclusive);
				$total_exclusive= $total_inclusive- $vat;
				
				$page_counter++;
				$item_counter++;
				$html .= '<tr style="height:25px;">
							<td style="text-align:center;border:1px solid '.$color_code.';">'.$description.'</td>
							<td style="text-align:center;border:1px solid '.$color_code.';">'.$quantity_value.'</td>
							<td style="text-align:right;border:1px solid '.$color_code.';">'.number_format($item_array['rate'], 2, '.', '').'</td>
							<td style="text-align:right;border:1px solid '.$color_code.';">'.number_format($total_exclusive, 2, '.', '').'</td>
							<td style="text-align:right;border:1px solid '.$color_code.';">'.number_format($vat, 2, '.', '').'</td>
							<td style="text-align:right;border:1px solid '.$color_code.';">'.number_format($total_inclusive, 2, '.', '').'</td>
						</tr>';	
				$sub_total += $item_array['total_charges'];
				$k++;		
				
				
			
				
				if($page_counter>=$max_row_limit)
				{
					
					$html .= $table_end;
					$html .='{{ITEM_SEPERATOR}}';
					$page_counter =0;
					if($item_counter < $total_item_count)
					{
						$html .=  $table_start;  	
						$html .= $theader;	
					}
				}
			}//foreach
			
			
		}//foreach
			$page_counter++;		
			$html .= ' <tr style="height:25px; ">
							<td colspan="6" style="text-align: right;padding:5px 5px 10px 10px;border:1px solid '.$color_code.'; "><strong>Sub Total :</strong> '.number_format($sub_total, 2, '.', '').'</td>							
						</tr>';
			$html .=$table_end;		
			
			$available_row_limit = $max_row_limit - $page_counter;
			
			$minimum_required = 4 + 2 + 3;
			if($available_row_limit > $minimum_required)
			{
				$page_counter = $page_counter + 4;//4 row
				$html .='<br><br><br><br>';
				
			}
			else
			{
				$html .='{{ITEM_SEPERATOR}}';
				$page_counter =0;
			}
			
			 $itemized_html .=$html;
			//////summary table html/////		
			$bill_summary_html .='<tr style="height: 18px;">
                                <td style="height: 18px; text-align: right;">'.$item_array['service_name'].'&nbsp;</td>
                                <td style="text-align: right; height: 18px;">'.number_format($sub_total, 2, '.', '').'&nbsp;</td>
                            </tr>';  
							
			$bill_summary_total +=	$sub_total;			
			  
    }
}
?>

						

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

	
	$header_text2 ='<span style="font-size:14px; font-weight:800px;">'.$invoice_config['company_name'] . "</span><br>" . $address;

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
    <table style="width: 100%; padding-bottom:10px;">
            <tr style="margin-top: 20px;">
                <td style="text-align: center;" colspan="4">
                    <div style="font-size:14px; font-weight:800px;">TAX INVOICE</div>
                </td>
            </tr>
    </table>
   <br /> 
   <table style="width: 100%;padding:0px;">
        <tr>
            <td style="width:50%;border:1px solid <?php echo $color_code;?>"><br />
                <table border="0" cellpadding="5">
                <tr><td><?php 
                if (strlen(trim($customerinvoice_data['company_name'])) > 0)
                        echo "<strong>To: ".$customerinvoice_data['company_name'] . "</strong><br><br><br>";
                if (strlen(trim($customerinvoice_data['tax_number'])) > 0)
                        echo 'VAT No: ' . $customerinvoice_data['tax_number'] . '<br>';
               if (strlen(trim($customerinvoice_data['company_address'])) > 0)
                        echo $customerinvoice_data['company_address'] . '<br>';	
                        
                echo '<br><br><br>';		
                if (strlen(trim($customerinvoice_data['contact_name'])) > 0)
                {
                        echo '<strong>Attention : '. $customerinvoice_data['contact_name'] . '</strong><br>';
                        if (strlen(trim($customerinvoice_data['phone_number'])) > 0)
                            echo 'Contact No: '.$customerinvoice_data['phone_number'] . '<br>';	
                        
                        if (strlen(trim($customerinvoice_data['email_address'])) > 0)
                            echo 'Email: '.$customerinvoice_data['email_address'] . '<br>';											
                }
                ?>
                </td></tr>
                </table>
               
            </td>
            <td style="width:2%;">&nbsp;</td>
            <td style="width:48%;"><br />
            	<table border="0" style="border:1px solid <?php echo $color_code;?>;" cellpadding="5">
                <?php 
				 echo '<tr><td width="35%"><strong>Invoice Date</strong></td><td width="65%"> ' . $bill_date . '</td></tr>';
                    echo '<tr><td><strong>Due Date</strong></td><td> ' . $due_date . '</td></tr>';
                    echo '<tr><td><strong>Invoice No</strong></td><td> ' . $customerinvoice_data['invoice_id'] . '</td></tr>';
                    echo '<tr><td><strong>Account No</strong></td><td> ' . $customerinvoice_data['account_id'] . '</td></tr>';
					echo '<tr><td><strong>Bill Period</strong></td><td> ' . $customerinvoice_data['billing_date_from'].' To '.$customerinvoice_data['billing_date_to'].'</td></tr>';
                  
					?>             	
                </table>
                
                
                
                <br /><br />
              <?php  if (isset($account_manager_details) && count($account_manager_details) > 0)
                {?>
              <table border="0" style="border:1px solid <?php echo $color_code;?>;" cellpadding="3">
                <?php 
                 echo "<tr><td><strong>Account Manager : " . $account_manager_details['name'] . '</strong></td></tr>';
                 if (strlen(trim($account_manager_details['phone'])) > 0)
                    echo "<tr><td>Contact No: ".$account_manager_details['phone'] . '</td></tr>';	
                        
                if (strlen(trim($account_manager_details['emailaddress'])) > 0)
                   echo "<tr><td>Email: ".$account_manager_details['emailaddress'] . '</td></tr>';		
                ?>
               </table>
              <?php } ?>    
                
                
                
            </td>
    	</tr>
    </table>
    <br clear="all" /><br /><br />
    <table style="width: 99%;  border:1px solid <?php echo $color_code;?>;" cellpadding="5" >                        
            <tr >
                <td style="text-align: center;vertical-align: middle;" width="15%">
                    <strong><?php echo number_format($opening_balance, 2, '.', ''); ?><br/>Last Bill</strong>
                </td>
                <td style="text-align: center;vertical-align: middle;" width="6%"><span class="heading1"><strong>+</strong></span>
                </td>
                <td style="text-align: center;" width="18%">
                    <span><strong><?php echo number_format($bill_summary_total, 2, '.', ''); ?><br/>Current Charges</strong></span> 
                </td>
                <td style="text-align: center;vertical-align: middle;" width="4%">
                    <span class="heading1"><strong>-</strong></span>
                </td>
                 <?php 
				$total_payment = $balance_added - $balance_refunded;
				?>

                <td style="text-align: center;" width="17%">
                    <span><strong><?php echo number_format($total_payment, 2, '.', ''); ?><br/>Payment</strong></span> 
                </td>

                <td style="text-align: center;vertical-align: middle;" width="4%"><span class="heading1"><strong>=</strong></span>
                </td>
                 <?php 
				$bill_amount = $opening_balance + $bill_summary_total - $total_payment;
				?>
                <td style="text-align: center; " width="18%">
                    <span><strong><?php echo number_format($bill_amount, 2, '.', ''); ?><br/>Bill Amount</strong></span> 
                </td> 
                <td style="text-align: center; " width="19%">
                    <span><strong><?php echo $due_date; ?><br/>Due Date</strong></span> 
                </td> 
            </tr>
    </table>
    
    
    
    <br /><br /><br />
    
    <table style="width: 100%; padding:0px;margin-top:20px;"  >
        
            <tr>
                <td style="text-align: left;" ><p class="heading2"><strong>&nbsp;&nbsp;Banking Details</strong></p></td>
                <td></td>
                <td style="text-align: left;"><p class="heading2"><strong>Account Overview / Summary</strong></p></td>
            </tr>
            <tr> 
                <td style="width: 50%; font-size: 95%; "  >
                    
                 <br /> 
                   
                   <table class="tablebill minheight150"  style="width: 100%;padding:0px;" cellpadding="5" >
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
                <td style="width: 48%;"> <br />
                        <table class="tablebill"  style="width: 100%; padding:3px;"  >
                    		<tr style="height: 18px;">
                                <td style="height: 18px; text-align: right;width: 60%;"><strong>Balance Brought Forward</strong>&nbsp;</td>
                                <td style="text-align: right; height: 18px;width: 40%;"><?php echo number_format($opening_balance, 2, '.', ''); ?>&nbsp;</td>
                            </tr>
                            <tr style="height: 18px;">
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <?php echo $bill_summary_html;?>
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
    
    <br />
  
  <?php
	if($customerinvoice_data['itemised_billing'] == 1) 
	{	echo '{{CONTENT_SEPERATOR}}';
		echo $itemized_html;
	}
?>