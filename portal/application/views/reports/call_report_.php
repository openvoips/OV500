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
<?php
//echo $_SESSION['search_call_data']['s_yearmonth'];

$timestamp = strtotime('last day of '.$_SESSION['search_call_data']['s_yearmonth']);
$last_day = date("j", $timestamp);


$yearmonth_array = explode('-',$_SESSION['search_call_data']['s_yearmonth']);

$year = $yearmonth_array[0];
$month = $yearmonth_array[1];

if(count($data['result']) > 0)
{
	$account_id_array = array_keys($data['result']);
}
$all_date_array=array();
$dp=4;	
?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Reporting : Destination Wise Call</h2>			
            <div class="clearfix"></div>
			
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url('reports/call_report'); ?>">
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
						if($yearmonth_value ==$_SESSION['search_call_data']['s_yearmonth'])
							$selected = ' selected="selected"';
						echo '<option value="'.$yearmonth_value.'" '.$selected.'>'.$yearmonth_display.'</option>';                    
					}?>					
					</select>
				</div>
                <?php if(!check_logged_user_type(array('CUSTOMER'))):?>
                <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" class="form-control data-search-field" name="account_id" id="account_id" value="<?php echo $_SESSION['search_call_data']['s_account_id'];?>"> 				</div>                
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
                    <table class="table table-striped table-bordered">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title">Account ID</th>
                            <th class="column-title">Destination</th>
                            <th class="column-title text-center">Connected Calls</th>
                            <th class="column-title text-center">Duration (Sec)</th>		
                            <th class="column-title text-right">Cost</th>			
                        </tr>
                    </thead>		
                    <tbody>
                    <?php
					if(count($call_statistics_data['result']) > 0)
					{	
						$total_array = array('connected_calls'=>0,'duration'=>0,'cost'=>0);													
						foreach($call_statistics_data['result'] as $call_data)
						{	
							$cost = round($call_data['cost'], $dp);
							$total_array['connected_calls'] += $call_data['connected_calls'];
							$total_array['duration'] += $call_data['duration'];
							$total_array['cost'] += $call_data['cost'];
							
							if($call_data['user_company_name']!=''){
							
								 $account_name=$call_data['user_company_name'].' ( '.$call_data['user_account_id'].' ) ';
							
							}else{
							
								$account_name=$call_data['user_account_id'];
							}
							
							$tr_html ='<tr>'.
							
							
							'<td>'.$account_name.'</td>'.
								
							'<td>'.$call_data['destination'].'</td>'.                      
							'<td class="text-center">'.$call_data['connected_calls'].'</td>'.  
							'<td class="text-center">'.$call_data['duration'].'</td>'.  
							'<td class="text-right">'.$cost.'</td>'.  
						'</tr>';
						
							echo $tr_html;									
						}
						
						$total_cost = round($total_array['cost'], $dp);
						$tr_html ='<tr>'.
										'<th class="text-right"></th>'.
										'<th class="text-right">Total</th>'.                      
										'<th class="text-center">'.$total_array['connected_calls'].'</th>'.  
										'<th class="text-center">'.$total_array['duration'].'</th>'.  
										'<th class="text-right">'.$total_cost.'</th>'.  
									'</tr>';
								echo $tr_html;			
					}	
					else
					{
					?>
					<tr>
						<td colspan="5" align="center"><strong>No Record Found</strong></td>
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
<div class="clearfix"></div>
<script>
$(document).ready(function() {
	showDatatable('table-sort', [], [ 1,"asc" ] );
});
</script> 