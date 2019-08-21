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
$currency_array = array();
foreach($currency_options as $currency_option)
{
	$currency_id = $currency_option['currency_id'];
	$name = $currency_option['name'];
	$currency_array[$currency_id] = $name;
}
//print_r($currency_array);
?>
<!-- Parsley -->
<script src="<?php echo base_url()?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Reporting : Accounting & Billing</h2>			
            <div class="clearfix"></div>
			
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url('reports/accounting_billing'); ?>">
			<input type="hidden" name="search_action" value="search" />
			
			<div class="form-group">			
				
				<label class="control-label col-md-1 col-sm-3 col-xs-12">Account</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
					<input type="text" name="user_account_id" id="user_account_id" value="<?php echo $_SESSION['search_billing_data']['s_user_account_id']; ?>" class="form-control data-search-field" placeholder="Account ID">
				</div>				
				
				<label class="control-label col-md-1 col-sm-3 col-xs-12 col-md-offset-1">Carrier</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="carrier_carrier_id_name" id="carrier_carrier_id_name" value="<?php echo $_SESSION['search_billing_data']['s_carrier_carrier_id_name']; ?>" class="form-control data-search-field" placeholder="Carrier ID">
				</div>
                
                <label class="control-label col-md-1 col-sm-3 col-xs-12 col-md-offset-2">Group by</label>
                                           
                <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                    <div class="radio" style="padding-top:2px;">                    
                        <input type="radio" name="group_by" value="carrier_carrier_id_name" <?php if (isset($_SESSION['search_billing_data']['s_group_by']) && $_SESSION['search_billing_data']['s_group_by'] == 'carrier_carrier_id_name') echo 'checked="checked"'; ?> data-parsley-required="" > Carrier
                    
                    </div>
                </div>
                
                <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                    <div class="radio" style="padding-top:2px;">                   
                        <input type="radio" name="group_by" value="user_account_id" <?php if (isset($_SESSION['search_billing_data']['s_group_by']) && $_SESSION['search_billing_data']['s_group_by'] == 'user_account_id') echo 'checked="checked"'; ?> data-parsley-required=""> Customer
                
                    </div>
                </div>
                
             </div>
            
             <div class="form-group">
                
                <label class="control-label col-md-1 col-sm-3 col-xs-12">Time</label>
                <div class="col-md-2 col-sm-3 col-xs-3">
                    <select name="year" id="year" class="form-control data-search-field" data-parsley-required="">
                        <option value="">Year</option>
                        <?php
						$current_year =  date('Y');
						for($y=2018;$y<=$current_year;$y++)
						{
							$selected='';
							if($_SESSION['search_billing_data']['s_year']==$y) 
							 	$selected = 'selected="selected"';
							 
							echo '<option value="'.$y.'" '.$selected.'>'.$y.'</option>';
						}
						?>                        
                    </select>
                </div>   
                 <div class="col-md-2 col-sm-3 col-xs-3">
                    <select name="month" id="month" class="form-control data-search-field" data-parsley-required="">
                         <option value="">Month</option>
						 <?php
						$month_array = range(1,12);
						foreach($month_array as $month)
						{
							$month = sprintf('%02d', $month);
							$selected='';
							if($_SESSION['search_billing_data']['s_month']==$month) 
							 	$selected = 'selected="selected"';
							 
							echo '<option value="'.$month.'" '.$selected.'>'.$month.'</option>';
						}
						?>         
                     </select>
                </div>   
                <div class="col-md-2 col-sm-3 col-xs-3">
                    <select name="day" id="day" class="form-control data-search-field">
                        <option value="">Day</option>
                       <?php
						$day_array = range(1,31);
						foreach($day_array as $day)
						{
							$day = sprintf('%02d', $day);
							$selected='';
							if($_SESSION['search_billing_data']['s_day']==$day) 
							 	$selected = 'selected="selected"';
							 
							echo '<option value="'.$day.'" '.$selected.'>'.$day.'</option>';
						}
						?>        
                     </select>
                </div>    
                
                
                        
				
				<div class="searchBar ">
					<input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
					<input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" onclick="location.href = ''">  
                    
                    <div class="btn-group">
						<button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
						Export <span class="caret"></span></button>
						<ul class="dropdown-menu" role="menu">
						<?php
						$export_format_array = get_export_formats();
						foreach($export_format_array as $export_format)
						{
							echo '<li><a href="'.base_url().'reports/accounting_billing/export/'.param_encrypt($export_format).'">'.strtoupper($export_format).'</a></li>';
						}							
						?>                            
						</ul>
					</div>  
				 </div>
			  </div>
			</form>		
        </div>  
    </div>
    <div class="x_panel" id="recordset" >
      <?php echo '<div class="btn-toolbar" role="toolbar">							  						
					  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
						'.$pagination.'
					  </div>
					</div>';
	?>
      
        <div class="x_content">
            <div class="table-responsive">
                <table id="analytics" class="table table-striped jambo_table bulk_action table-bordered" >
                    <thead>
                        <tr class="headings thc">    
                        	<?php
							if($_SESSION['search_billing_data']['s_group_by'] == 'user_account_id')
							{?>              
                            <th class="column-title">Customer</th>
                            <th class="column-title">Carrier</th>
                            <?php
							}else{?>
								 <th class="column-title">Carrier</th>
                                 <th class="column-title">Customer</th>						
							<?php }?>
                            <th class="column-title">Answered Calls</th>
                            <th class="column-title">Minute Usage Cost</th>                            
                            <th class="column-title">Carrier Cost</th>
                            <th class="column-title">Profit</th>
                            <th class="column-title">Currency</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="headings thc">                  
                            <?php
							if($_SESSION['search_billing_data']['s_group_by'] == 'user_account_id')
							{?>              
                            <th class="column-title">Customer</th>
                            <th class="column-title">Carrier</th>
                            <?php
							}else{?>
								 <th class="column-title">Carrier</th>
                                 <th class="column-title">Customer</th>						
							<?php }?>
                            <th class="column-title">Answered Calls</th>
                            <th class="column-title">Minute Usage Cost</th>                            
                            <th class="column-title">Carrier Cost</th>
                            <th class="column-title">Profit</th>
                            <th class="column-title">Currency</th>
                        </tr>
                    </tfoot>
                   
                    			
                    <tbody>
                        <?php						
                        if (isset($listing_data) && count($listing_data) > 0)
						{
							$previous_customer = $previous_carrier='';
                            foreach ($listing_data as $listing_row) 
							{
								$customer = $listing_row['customer'];
								$carrier  = $listing_row['carrier'];
								
								$currency_id = $listing_row['currency'];
								$currency = $currency_array[$currency_id];
								
								if($_SESSION['search_billing_data']['s_group_by'] == 'user_account_id')
								{
								
									if($previous_customer ==$customer)
										$customer_display = '';
									else
										$customer_display = $customer;									
									 
									 
									 
									echo '<tr>';
									echo '
										<td>'.$customer_display.'</td>
										<td>'.$listing_row['carrier'].'</td>
										<td>'.$listing_row['answered_calls'].'</td>
										<td>'.$listing_row['minute_usage_cost'].'</td>                            
										<td>'.$listing_row['carrier_cost'].'</td>
										<td>'.$listing_row['profit'].'</td>
										<td>'.$currency.'</td>';									
									 echo '</tr>';
									 
									 $previous_customer = $customer;
								}
								else
								{
								
									if($previous_carrier ==$carrier)
										$carrier_display = '';
									else
										$carrier_display = $carrier;									
									 
									echo '<tr>';
									echo '
										<td>'.$carrier_display.'</td>
										<td>'.$listing_row['customer'].'</td>
										<td>'.$listing_row['answered_calls'].'</td>
										<td>'.$listing_row['minute_usage_cost'].'</td>                            
										<td>'.$listing_row['carrier_cost'].'</td>
										<td>'.$listing_row['profit'].'</td>
										<td>'.$currency.'</td>';									
									 echo '</tr>';
									 
									 $previous_carrier = $carrier;
								}
							}
							
						}
						else
						{
							echo '<tr>';
									echo '
										<td colspan="7" class="text-center">No data found</td>';								
									 echo '</tr>';
						}
						?>
                    </tbody>
                </table>
            </div>
            <br />
              <?php echo '<div class="btn-toolbar" role="toolbar">
							  <div class="btn-group col-md-5 col-sm-12 col-xs-12">';
							 
							  echo '</div>
						
							  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
									   '.$pagination.'
							  </div>
							</div>';?>
 					
        </div>
    </div>
</div>
<style type="text/css">



table.jambo_table tfoot {
    background: rgba(52,73,94,.94);
    color: #ECF0F1;
}
</style>
<script>

$('#OkFilter').click(function() {
	var is_ok = $("#search_form").parsley().isValid();
	if(is_ok === true)
	{		
		if(is_ok === true)
		{
			$("#search_form").submit();
		}	
	}
	else
	{
		$('#search_form').parsley().validate();
	}
})
$(document).ready(function () {

		 
		
});
</script>