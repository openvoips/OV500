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
//echo '<pre>';print_r($data['result']);echo '</pre>';
echo '<pre>';print_r($data['sql']);echo '</pre>';
?>
<?php
$sdr_terms_array=array();
?>
<script src="<?php echo base_url()?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Search </h2>
            <ul class="nav navbar-right panel_toolbox">
              
            </ul>
            <div class="clearfix"></div>
        </div>
    
     	<div class="x_content">
     
		
        <form action="<?php echo base_url();?>reports/payment_history" method="post" name="search_form" id="search_form" data-parsley-validate class="form-horizontal form-label-left">
       
		<input type="hidden" name="search_action" value="search" />
        <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
         <div class="form-group">
        
			 <label class="control-label col-md-2 col-sm-3 col-xs-12" >Payment Date</label>
            <div class="col-md-4 col-sm-6 col-xs-12">
              <input type="text" name="search_pay_date" id="search_pay_date" value="<?php echo $_SESSION['search_payment_data']['s_pay_date']; ?>"  class="form-control col-md-7 col-xs-12 data-search-field" readonly="readonly" data-parsley-required="">
            </div>		
          <?php if(!check_logged_user_type(array('CUSTOMER'))):?>
            <label class="control-label col-md-2 col-sm-3 col-xs-12" >Account ID</label>
            <div class="col-md-4 col-sm-6 col-xs-12">
              <input type="text" name="search_account_id" id="search_account_id" value="<?php echo $_SESSION['search_payment_data']['s_account_id']?>"  class="form-control col-md-7 col-xs-12 data-search-field">
            </div>
			
			 
          </div>  
			<div class="form-group pull-right"> 
            
            <?php endif; ?>	
				<input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
				<!--<input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" > -->
            </div>
		
       </form>
       </div>
	</div>       
</div>    

        
<div class="col-md-12 col-sm-6 col-xs-12">
   <div class="x_panel">
              <div class="x_title">
                <h2>Payment History</h2>
                <ul class="nav navbar-right panel_toolbox">
                  
                </ul>
                <div class="clearfix"></div>
              </div>
            
            <div class="row">  
			<?php		
             dispay_pagination_row($total_records, $_SESSION['search_payment_data']['s_no_of_records'], $pagination);
            ?>                    
           </div>     
             <div class="x_content">
             
				<div class="table-responsive">
                <table id="table-sort" class="table table-striped jambo_table table-bordered">
                    <thead>
                      <tr class="headings thc">
					   <th class="column-title"><nobr>Account ID</nobr></th>
                        <th class="column-title">Option</th>
                        <th class="column-title">Amount</th>
                        <th class="column-title"><nobr>Payment Date</nobr></th>
                        <th class="column-title">Notes</th>
                        <th class="column-title"><nobr>Entry Date</nobr></th>
                      </tr>
                    </thead>

                    <tbody>
                     <?php
                    if(count($data['result']) > 0)
                    {	
						$payment_history =$data['result'];
						$sum_array=array();
                        foreach($payment_history as $payment_data)
                        {	
                            $paid_on_display='';
                            $create_dt_display='';		                                    
                            if($payment_data['paid_on'] !='')
                            {
                                $paid_on = $payment_data['paid_on'];
                                $paid_on_timestamp = strtotime($paid_on);
                                $paid_on_display=date(DATE_FORMAT_1,$paid_on_timestamp);									
                            }
                            if($payment_data['create_dt'] !='')
                            {
                                $create_dt = $payment_data['create_dt'];
                                $create_dt_timestamp = strtotime($create_dt);
                                $create_dt_display=date('Y-m-d h:i A',$create_dt_timestamp);									
                            }
							
							$payment_option_id=$payment_data['payment_option_id'];
							$amount = number_format($payment_data['amount'], $dp, '.','');
							
							if(isset($sum_array[$payment_option_id]))
								$sum_array[$payment_option_id] +=$amount;
							else 
								$sum_array[$payment_option_id] =$amount;	
								
							$sdr_terms_array[$payment_option_id] =$payment_data['payment_option'];
                        ?>
                            <tr> 
								<td><?php echo $payment_data['account_id']; ?></td>							
                                <td><?php echo $payment_data['payment_option']; ?></td>
                                <td class="text-right"><?php echo $amount; ?></td>         
                                <td><?php echo $paid_on_display; ?></td>                                   
                                <td><?php echo $payment_data['notes']; ?></td>
                                <td class=" last"><?php echo $create_dt_display; ?></td>  
                            </tr>
                    <?php
                        }
						
                    }
                    else
                    {
                    ?>
                    <tr>
                        <td colspan="6" align="center"><strong>No Records Found</strong></td>
                    </tr>
                    <?php }?>
                      
                     
                    </tbody>
                  </table>
                 </div> 
                  
              </div>
                
              </div>
			
                   
			</div> 
	
    
    <?php if(count($sum_array) > 0):?>
					
	<div class="col-md-8 col-sm-6 col-xs-12">  
		<div class="x_panel">
              <div class="x_title">
                <h2>Total Amount</h2>
                <ul class="nav navbar-right panel_toolbox">
                  
                </ul>
                <div class="clearfix"></div>
              </div>
            
            
            <?php 
             	echo '<div class="x_content">';			
             	echo '<table class="table table-striped jambo_table table-bordered">';
               
				foreach($sum_array as $key=>$value)
				{	
					$payment_option = $sdr_terms_array[$key];
					
					echo '<tr>'.
					'<td>'.$payment_option.'</td><td>'.$value.'</td>'.
					'</tr>';				
				}                    
				echo '</table>	';					
				echo '</div>';
			
			?>
			
	 </div>
			  
</div>		
	<?php endif;?>	

<script>
/*search form*/
$('#OkFilter').click(function(){
	var no_of_records=$('#no_of_records').val();			
	$('#no_of_rows').val(no_of_records);			
});
</script> 
 
<script>
 $(document).ready(function () {
   $("#search_pay_date").daterangepicker({
            timePicker: !0,
            timePickerIncrement: 5,
            locale: {
                format: "YYYY-MM-DD HH:mm"
            },
            timePicker24Hour: true,
            ranges: {
                'Last 15 Minute': [moment().subtract(15, 'minute'), moment()],
				'Last 30 Minute': [moment().subtract(30, 'minute'), moment()],
				'Last 1 Hour': [moment().subtract(1, 'hour'), moment()],
				'Today': [moment().startOf('days'), moment().endOf('days')],
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
 	});
 
	showDatatable('table-sort', [], [ 5,"desc" ] );
 
 });
</script>