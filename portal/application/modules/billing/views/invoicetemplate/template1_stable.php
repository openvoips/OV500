<style type="text/css">
.height30{height:30px;}
table.borderless td,table.borderless th{
border: none !important;
border-top: none !important;
}
tr td{vertical-align:top;}
.tablebill tr td {
border: 1px solid #990505;
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
function rearrange_did($item_array_multiple)
{
	$final_send_array = array();
	$matched_key_array = array();
	
	$previous_did_array= array_shift($item_array_multiple);
	$previous_did = $previous_did_array['dst'];
	$final_send_array[$previous_did] = $previous_did_array;
	foreach($item_array_multiple as $key=>$item_array)
	{
		$current_did = $item_array['dst'];	
		if($previous_did + 1 == $current_did)
		{
			//echo "<br>$previous_did + 1 == $current_did";
			$previous_array = $final_send_array[$previous_did];
			unset($final_send_array[$previous_did]);
			
			$previous_array['dst'] = $previous_array['dst'] .'-'.$item_array['dst'];
			$previous_array['rate'] = $previous_array['rate'] + $item_array['rate'];
			$previous_array['quantity'] = $previous_array['quantity'] + $item_array['quantity'];
			$previous_array['tax1_amount'] = $previous_array['tax1_amount'] + $item_array['tax1_amount'];
			$previous_array['tax2_amount'] = $previous_array['tax2_amount'] + $item_array['tax2_amount'];
			$previous_array['tax3_amount'] = $previous_array['tax3_amount'] + $item_array['tax3_amount'];
			$previous_array['charges'] = $previous_array['charges'] + $item_array['charges'];
			$previous_array['total_charges'] = $previous_array['total_charges'] + $item_array['total_charges'];
			
			$final_send_array[$current_did] = $previous_array;
			
			$matched_key_array[$current_did]=$current_did;
			if(isset($matched_key_array[$previous_did]))
				unset($matched_key_array[$previous_did]);
				
		}
		else
		{
			//echo "<br>>>= $current_did";	
			$final_send_array[$current_did] = $item_array;
		}
		
		$previous_did = $current_did;
	}
	
	if(count($matched_key_array)>0)
	{
		foreach($matched_key_array as $did_key)
		{
			$array_temp = $final_send_array[$did_key];
			$dst = $array_temp['dst'];
			$dst_array = explode('-',$dst);
			$length = count($dst_array);
			$first_str = $dst_array[0];
			$last_str = $dst_array[$length-1];
			for($i=1;$i<=$length;$i++)
			{
				if(substr($first_str, 0, -$i) == substr($last_str, 0, -$i))
				{
					$updated_dst = $first_str.'-'.substr($last_str, -$i-1);
					$final_send_array[$did_key]['dst'] = $updated_dst;
					break;
				}
			}
			
		}
		
	}
	
	//$final_send_array =$item_array_multiple;
	
	return $final_send_array;
}


?>
<?php
$sdr_service_array=array();
if(isset($sdr_data) && count($sdr_data) > 0) 
{
	foreach($sdr_data as $sdr_data_single)
	{
		$service_id = $sdr_data_single['service_id'];
		$item_id = $sdr_data_single['item_id'];
		$sdr_service_array[$service_id][$item_id][] = $sdr_data_single;
	}
}



//  $sdr_service_array[$service_id][$item_id][] = $sdr_data_single;
$itemized_html = '';
$bill_summary_html = '';
$bill_summary_total = 0;
if(count($sdr_service_array) > 0) 
{//$service_id][$item_id
	foreach ($sdr_service_array as $service_id_key =>$sdr_service_array_single) 
	{
		//ddd($sdr_service_array_single);
		$html = '';
        $html = '<table class="tablebill"  style="width: 100%;" >';
            
				$k = 0;		
				$sub_total =0;
			foreach ($sdr_service_array_single as $item_id_key=>$item_array_multiple) 
			{		//ddd($item_array);die;
				if($item_id_key=='DIDRENTAL')
				{
					$item_array_multiple = rearrange_did($item_array_multiple);
				}
				
				{
					foreach ($item_array_multiple as $key=>$item_array) 
					{
					
						if($k==0)
						{
							$html .= '<tr style="height:25px;">
									<td colspan="6" style="padding:5px 5px 0px 10px;"><p class="heading2"><strong>'.$item_array['service_name'].'</strong></p></td>
								</tr>       
								<tr style="background-color:#990505; height:25px;color:#FFF;">
									<td style="text-align: center; " ><strong>Description</strong>&nbsp;</td>
									<td style="text-align: center; "><strong>Quantity</strong>&nbsp;</td>
									<td style="text-align: center; " ><strong>Price</strong>&nbsp;</td>
									<td style="text-align: center; "><strong>Total Excl</strong>&nbsp;</td>
									<td style="text-align: center; " ><strong>VAT</strong>&nbsp;</td>
									<td style="text-align: center; "><strong>Total Incl</strong>&nbsp;</td>
								</tr>';
						}
						$vat = $item_array['tax1_amount']+$item_array['tax2_amount']+$item_array['tax3_amount'];
					
						$html .= '<tr style="height:25px;">
									<td style="text-align:center;">'.$item_array['dst'].'</td>
									<td style="text-align:center;">'.number_format($item_array['quantity'], 2, '.', '').'</td>
									<td style="text-align:right;">'.number_format($item_array['rate'], 2, '.', '').'</td>
									<td style="text-align:right;">'.number_format($item_array['charges'], 2, '.', '').'</td>
									<td style="text-align:right;">'.number_format($vat, 2, '.', '').'</td>
									<td style="text-align:right;">'.number_format($item_array['total_charges'], 2, '.', '').'</td>
								</tr>';	
						$sub_total += $item_array['total_charges'];
						$k++;		
					}
				
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
    $header_text2 = nl2br($invoice_config['address']);

    $address_html = $header_text2;

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
                <td style="text-align: right;" colspan="2"><?php echo '<span class="heading1"><strong>'.$invoice_config['company_name'] . "</strong></span><br>" . $address_html; ?></td>
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
                <div style="border:1px solid #990505;" class="pad5">
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
            <td style="width:5%;">&nbsp;</td>
            <td style="width:45%;">
            <div style="border:1px solid #990505;" class="pad5">
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
              <div style="border:1px solid #990505;" class="pad5">
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
            <tr style="border:1px solid #990505;  ">
                <td style="text-align: center;">
                    <p><strong><?php echo number_format($customerinvoice_data['last_bill_amount'], 2, '.', ''); ?><br/>Last Bill</strong></p>
                </td>


                <td style="text-align: center;vertical-align: middle;">
                    <p class="heading1"><strong>+</strong></p>
                </td>
                <td style="text-align: center;">

                    <p><strong><?php echo number_format($customerinvoice_data['current_due_amount'], 2, '.', ''); ?><br/>Current Charges</strong></p> 

                </td>

                <td style="text-align: center;vertical-align: middle;">
                    <p class="heading1"><strong>-</strong></p>
                </td>

                <td style="text-align: center;">
                    <p><strong><?php echo number_format(($customerinvoice_data['payments'] - $customerinvoice_data['refund_amount']), 2, '.', ''); ?><br/>Payment</strong></p>  


                </td>

                <td style="text-align: center;vertical-align: middle;">
                    <p class="heading1"><strong>=</strong></p>
                </td>
                <td style="text-align: center; ">


                    <p><strong><?php echo number_format($customerinvoice_data['bill_amount'], 2, '.', ''); ?><br/>Bill Amount</strong></p> 
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
                <td style="width: 45%; font-size: 95%; "  >
                    
                   
                   
                   
                   
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
                <td style="width: 10%;"></td>
                <td> 
                        <table class="tablebill"  style="width: 100%;" >
                    		<tr style="height: 18px;">
                                <td style="height: 18px; text-align: right;"><strong>Balance Brought Forward</strong>&nbsp;</td>
                                <td style="text-align: right; height: 18px;"><?php echo number_format($customerinvoice_data['last_bill_amount'], 2, '.', ''); ?>&nbsp;</td>
                            </tr>
                            <tr style="height: 18px;">
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <?php echo $bill_summary_html;?>
                            <tr style="height: 18px;">
                                <td style="height: 18px; text-align: right;"><strong>VAT</strong>&nbsp;</td>
                                <td style="text-align: right; height: 18px;"><strong><?php echo number_format($customerinvoice_data['tax1_amount']+$customerinvoice_data['tax2_amount']+$customerinvoice_data['tax3_amount'], 2, '.', ''); ?></strong>&nbsp;</td>
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
                                <td style="text-align: right; height: 18px;"><?php echo number_format($customerinvoice_data['last_bill_amount']+$bill_summary_total, 2, '.', ''); ; ?>&nbsp;</td>
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
