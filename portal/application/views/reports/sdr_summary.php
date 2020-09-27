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
<?php
//echo $_SESSION['search_sdr_summ_data']['s_yearmonth'];

$timestamp = strtotime('last day of '.$_SESSION['search_sdr_summ_data']['s_yearmonth']);
$last_day = date("j", $timestamp);


$yearmonth_array = explode('-',$_SESSION['search_sdr_summ_data']['s_yearmonth']);

$year = $yearmonth_array[0];
$month = $yearmonth_array[1];

if(count($data['result']) > 0)
{
	$account_id_array = array_keys($data['result']);
}
$all_date_array=array();	
?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Reporting : Account Summary</h2>			
            <div class="clearfix"></div>
			
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
			<input type="hidden" name="search_action" value="search" />
			
			<div class="form-group">
				
                <label class="control-label col-md-1 col-sm-3 col-xs-12">Month</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<select class="form-control data-search-field" id="yearmonth" name="yearmonth">
					<?php 
					for($i=0; $i<=5; $i++) 
					{
						$yearmonth_timestamp = strtotime("-".$i." month");
     					$yearmonth_display =  date("F Y", $yearmonth_timestamp);
						$yearmonth_value =  date("Y-m", $yearmonth_timestamp);
						$selected='';
						if($yearmonth_value ==$_SESSION['search_sdr_summ_data']['s_yearmonth'])
							$selected = ' selected="selected"';
						echo '<option value="'.$yearmonth_value.'" '.$selected.'>'.$yearmonth_display.'</option>';                    
					}?>					
					</select>
				</div>
                <?php if(!check_logged_user_type(array('CUSTOMER'))):?>
                <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" class="form-control data-search-field" name="account_id" id="account_id" value="<?php echo $_SESSION['search_sdr_summ_data']['s_account_id'];?>"> 
				</div>
                <?php endif; ?>
				
                
                
								
				<div class="searchBar">
					<input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
				 </div>
			  </div>
			</form>		
        </div>  
    </div>
  
	

    <div class="x_panel">
        <div class="x_content">
            <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">
                            
                            <th class="column-title"><nobr>Account ID</nobr></th>
                            <th class="column-title"></th>
                            <?php
							
							for($d=1; $d<=$last_day; $d++)
							{ 
								$day = sprintf('%02d',$d);
								$date = $day.'/'.$month.'/'.$year;
								$loop_date =$date;
								$all_date_array[]=$loop_date;
								echo '<th class="column-title">'.$loop_date.'</th>';
							}
							
							?>
                            <th class="column-title">Total</th>
                            <th class="column-title" colspan="2">Action</th>							
                        </tr>
                    </thead>		
                    <tbody>
                         <?php
						 $total_sum_array=array();
						 $parameter_array = array('call_cost'=>'Call Cost', 'other_charges'=>'Other Charges', 'profit'=>'Profit', 'payment'=>'Payment', 'crdit'=>'Credit');
						 
						if(count($data['result']) > 0)
						{								
							foreach($account_id_array as $account_id)
							{
								$array_temp = current(current($data['result'][$account_id]));
								$company_name = $array_temp['company_name'];
								
								echo '<tr>'
									.'<td rowspan="5" class="align-middle text-center">'.$account_id.'<br>'.$company_name.'</td>';
								
								$i=0;	
								foreach($parameter_array as $parameter_key => $parameter)
								{
									echo '<td><nobr>'.$parameter.'</nobr></td>';	
									foreach($all_date_array as $date_value)
									{										
										$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost']=$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] =0;
										if(isset($data['result'][$account_id][$date_value]))
										{
										
											if($parameter_key =='call_cost')
											{
												if(isset($data['result'][$account_id][$date_value]['DAILYUSAGE']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] + $data['result'][$account_id][$date_value]['DAILYUSAGE']['sum_total_cost'];
													
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] + $data['result'][$account_id][$date_value]['DAILYUSAGE']['sum_total_seller_cost'];
												}	
												if(isset($data['result'][$account_id][$date_value]['DAILYUSAGEIN']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] + $data['result'][$account_id][$date_value]['DAILYUSAGEIN']['sum_total_cost'];
													
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] + $data['result'][$account_id][$date_value]['DAILYUSAGEIN']['sum_total_seller_cost'];
												}
											}
											
											if($parameter_key =='other_charges')
											{
												if(isset($data['result'][$account_id][$date_value]['DIDEXTRACHRENTAL']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] + $data['result'][$account_id][$date_value]['DIDEXTRACHRENTAL']['sum_total_cost'];
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] + $data['result'][$account_id][$date_value]['DIDEXTRACHRENTAL']['sum_total_seller_cost'];
												}	
												if(isset($data['result'][$account_id][$date_value]['DIDRENTAL']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] + $data['result'][$account_id][$date_value]['DIDRENTAL']['sum_total_cost'];
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] + $data['result'][$account_id][$date_value]['DIDRENTAL']['sum_total_seller_cost'];
												}	
												if(isset($data['result'][$account_id][$date_value]['DIDSETUP']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] + $data['result'][$account_id][$date_value]['DIDSETUP']['sum_total_cost'];
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] + $data['result'][$account_id][$date_value]['DIDSETUP']['sum_total_seller_cost'];
												}	
												if(isset($data['result'][$account_id][$date_value]['TARIFFCHARGES']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] + $data['result'][$account_id][$date_value]['TARIFFCHARGES']['sum_total_cost'] ;
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] + $data['result'][$account_id][$date_value]['TARIFFCHARGES']['sum_total_seller_cost'] ;
												}	
											}	
											
											if($parameter_key =='profit')
											{
												$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = 											
												$data['result'][$account_id][$date_value]['call_cost']['sum_total_cost'] 
												- $data['result'][$account_id][$date_value]['call_cost']['sum_total_seller_cost']
												+ $data['result'][$account_id][$date_value]['other_charges']['sum_total_cost'] 
												- $data['result'][$account_id][$date_value]['other_charges']['sum_total_seller_cost'] ;
											
											}											
											
											if($parameter_key =='payment')
											{
												if(isset($data['result'][$account_id][$date_value]['ADDBALANCE']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] + $data['result'][$account_id][$date_value]['ADDBALANCE']['sum_total_cost'] ;
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] + $data['result'][$account_id][$date_value]['ADDBALANCE']['sum_total_seller_cost'] ;
												}	
													
												if(isset($data['result'][$account_id][$date_value]['REMOVEBALANCE']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] - $data['result'][$account_id][$date_value]['REMOVEBALANCE']['sum_total_cost'] ;
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] - $data['result'][$account_id][$date_value]['REMOVEBALANCE']['sum_total_seller_cost'] ;
												}	
											}																						
											
											if($parameter_key =='crdit')
											{
												if(isset($data['result'][$account_id][$date_value]['ADDCREDIT']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] + $data['result'][$account_id][$date_value]['ADDCREDIT']['sum_total_cost'];
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] + $data['result'][$account_id][$date_value]['ADDCREDIT']['sum_total_seller_cost'];
												}
												
												if(isset($data['result'][$account_id][$date_value]['REMOVECREDIT']))
												{
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'] - $data['result'][$account_id][$date_value]['REMOVECREDIT']['sum_total_cost'];
													
													$data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] = $data['result'][$account_id][$date_value][$parameter_key]['sum_total_seller_cost'] - $data['result'][$account_id][$date_value]['REMOVECREDIT']['sum_total_seller_cost'];
												
												}	
											}
											
										}
																				
										 $total_sum_array[$account_id][$parameter_key]= $total_sum_array[$account_id][$parameter_key] + $data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'];												
										
										echo '<td>'.$data['result'][$account_id][$date_value][$parameter_key]['sum_total_cost'].'</td>';
										
									}	//foreach date
									
									echo '<td>'.$total_sum_array[$account_id][$parameter_key].'</td>';	
									if($i==0)
										echo '<td rowspan="5"><a href="'.base_url().'reports/statement/'.param_encrypt($account_id).'" title="Account Statement" target="_blank"><u>Account Statement</u></a></td>';							
										
									if($i==0)
										echo '<td rowspan="5"><a href="'.base_url().'reports/cdr/'.param_encrypt($account_id).'" title="CDR" target="_blank"><u>CDR</u></a></td>';
										
										
									echo '</tr>';
									$i++;
								}
								
								
							
								//echo '</tr>';
							}
							
							
                        }
						else
						{
						?>
						<tr>
							<td colspan="18" align="center"><strong>No Record Found</strong></td>
						</tr>
						<?php
						}
						?>
                    </tbody>
                </table>
                
                
                
                
            </div>
			
        </div>
    </div>
          
</div>      
	
<script>
$(document).ready(function() {
	showDatatable('table-sort', [], [ 1,"asc" ] );
});
</script> 