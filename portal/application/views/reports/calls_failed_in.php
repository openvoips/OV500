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

<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>CDR Reporting : Incoming Failed Calls</h2>			
            <div class="clearfix"></div>
			
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url();?>reports/calls_failed_in">
			<input type="hidden" name="search_action" value="search" />
			  <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
		            
            <div class="form-group">
            
				<?php if($logged_user_type !='CUSTOMER') { ?>
				<label class="control-label col-md-1 col-sm-3 col-xs-12">UserType</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<select class="form-control" id="user_type" name="user_type">
					<?php if($get_logged_user_level == 0) { ?>
					<option value="U"  <?php if ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'U' ) echo 'selected' ; ?>>User</option>
					<option value="R1" <?php if ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'R1' ) echo 'selected' ; ?>>Reseller 1</option>
					<?php } ?>
					<?php if($get_logged_user_level == 1) { ?>
					<option value="U"  <?php if ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'U' ) echo 'selected' ; ?>>User</option>
					<option value="R2" <?php if ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'R2' ) echo 'selected' ; ?>>Reseller 2</option>
					<?php } ?>
					<?php if($get_logged_user_level == 2) { ?>
					<option value="U"  <?php if ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'U' ) echo 'selected' ; ?>>User</option>
					<option value="R3" <?php if ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'R3' ) echo 'selected' ; ?>>Reseller 3</option>
					<?php } ?>
					<?php if($get_logged_user_level == 3) { ?>
					<option value="U"  <?php if ($_SESSION['search_failed_in_data']['s_cdr_user_type'] == 'U' ) echo 'selected' ; ?>>User</option>
					<?php } ?>
					</select>
				</div>
				<label class="control-label col-md-1 col-sm-3 col-xs-12">Account</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
					<input type="text" name="user_account" id="user_account" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_user_account']; ?>" class="form-control data-search-field" placeholder="Account">
				</div>
                
                 <label class="control-label col-md-3 col-sm-3 col-xs-12">Company Name</label>
				<div class="col-md-3 col-sm-9 col-xs-12">
					<input type="text" name="user_company_name" id="user_company_name" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_user_company_name']; ?>" class="form-control data-search-field" placeholder="Company Name">
				</div>	
                
                	
				<?php } ?>
                
               </div> <!-- end of the row-->
               
               <div class="form-group"> 
                
				<label class="control-label col-md-1 col-sm-3 col-xs-12">Dialed No</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="dialed_no" id="dialed_no" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_dialed_no']; ?>" class="form-control data-search-field" placeholder=" Dialed No">
				</div>
                <?php if(isset($all_field_array['C-DST'])):?>
				<label class="control-label col-md-1 col-sm-3 col-xs-12">C-DST No</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="carrier_dst_no" id="carrier_dst_no" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_carrier_dst_no']; ?>" class="form-control data-search-field" placeholder="Carrier DST">
				</div>
                <?php endif;?>
              
             
            				
				<?php if(isset($all_field_array['USER-CLI'])):?>	
				<label class="control-label col-md-1 col-sm-3 col-xs-12">User CLI</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="user_cli" id="user_cli" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_user_cli']; ?>" class="form-control data-search-field" placeholder="User CLI">
				</div>
                <?php endif;?>
                <?php if(isset($all_field_array['C-CLI'])):?>	
				<label class="control-label col-md-1 col-sm-3 col-xs-12">C-CLI</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="carrier_cli" id="carrier_cli" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_carrier_cli']; ?>" class="form-control data-search-field" placeholder="Carrier CLI">
				</div>
                <?php endif;?>
                
                </div> <!-- end of the row-->
                
                <div class="form-group">
                
                <?php if(isset($all_field_array['Carrier'])):?>
				<label class="control-label col-md-1 col-sm-3 col-xs-12">Carrier</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="carrier" id="carrier" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_carrier']; ?>" class="form-control data-search-field" placeholder="Carrier">
				</div>
                <?php endif;?>
               
                <?php if(isset($all_field_array['C-IP'])):?>
				<label class="control-label col-md-1 col-sm-3 col-xs-12">Carrier IP</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="carrier_ip" id="carrier_ip" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_carrier_ip']; ?>" class="form-control data-search-field" placeholder="Carrier IP">
				
                
                </div>
                <?php endif;?>
            
              
            
                <label class="control-label col-md-1 col-sm-3 col-xs-12">User IP</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="user_ip" id="user_ip" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_user_ip']; ?>" class="form-control data-search-field" placeholder="User IP">
				</div>
               
				<label class="control-label col-md-1 col-sm-3 col-xs-12">SIP Code</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="sip_code" id="sip_code" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_sip_code']; ?>" class="form-control data-search-field" placeholder="Sip Code">
				</div>
				
                </div> <!-- end of the row-->
                
                <div class="form-group">
                
				<label class="control-label col-md-1 col-sm-3 col-xs-12">Q850CODE</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" name="Q850CODE" id="Q850CODE" value="<?php echo $_SESSION['search_failed_in_data']['s_cdr_Q850CODE']; ?>" class="form-control data-search-field" placeholder="Q850 Code">
				</div>
                
			
                 <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                <div class="col-md-5 col-sm-9 col-xs-12">
                    <input type="text" name="time_range" id="time_range" class="form-control data-search-field" value="<?php if(isset($_SESSION['search_failed_in_data']['s_time_range'])) echo $_SESSION['search_failed_in_data']['s_time_range']; ?>" readonly="readonly" data-parsley-required="" />
                </div> 
           
       
        
				<div class="searchBar">
					<input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
					<input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">  
                    
                    <div class="btn-group">
                    
                    
						<button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
						Export <span class="caret"></span></button>
						<ul class="dropdown-menu" role="menu">
						<?php
						$export_format_array = get_export_formats();
						foreach($export_format_array as $export_format)
						{
							echo '<li><a href="'.base_url().'reports/calls_failed_in/export/'.param_encrypt($export_format).'">'.strtoupper($export_format).'</a></li>';
						}							
						?>                            
						</ul>
					</div>  
				 </div>
                 
                 </div> <!-- end of the row-->
               
			
			</form>	
        </div>  
    </div>
	
	
    <div class="x_panel"  >
        <div class="row">  
			<?php		
             dispay_pagination_row($total_records, $_SESSION['search_failed_in_data']['s_no_of_records'], $pagination);
            ?>                    
       </div> 
        <div class="x_content">
            <div class="table-responsive">
                <table id="table-sort" class="table table-striped jambo_table bulk_action table-bordered">
                    <thead>
                        <tr class="headings thc">                                                   
                             <?php
							foreach($all_field_array as $field_lebel)
							{
								echo '<th class="column-title"><nobr>'.$field_lebel.'</nobr></th>';
							}				
							?>
							
                        </tr>
                    </thead>	
                     <tfoot>
                        <tr class="headings thc">                            
                            <?php
							foreach($all_field_array as $field_lebel)
							{
								echo '<th class="column-title">'.$field_lebel.'</th>';
							}				
							?>							
                        </tr>
                    </tfoot>		
                    <tbody>
                        <?php						
                        if (isset($listing_count) && $listing_count > 0) {
                            foreach ($listing_data as $listing_row) {
                                 //var_dump($listing_row);
								 echo '<tr>';
								 foreach($all_field_array as $field_name=>$field_lebel)
								 {
								 	echo '<td><nobr>';
								if($field_name=='Account'){
								
									if($listing_row['user_company_name'] !=''){
								
									echo $listing_row['user_company_name'].' ( '.$listing_row[$field_name].' ) ';
									
									}else{
										echo $listing_row[$field_name];
									}
								
								
									}else{
									
										echo $listing_row[$field_name];
									}
									echo '</nobr></td>';
								 }
								 echo '</tr>';
							}
						}else{
						?>
                       
						<tr>
							<td colspan="27" ><nobr><strong>No Record Found</strong></nobr></td>
						</tr>
						<?php
						}
						?>
                        
                    </tbody>
                </table>
            </div>
			
        </div>
        
        <br />
              <?php 
			  	echo '<div class="btn-toolbar" role="toolbar">
							  <div class="btn-group col-md-5 col-sm-12 col-xs-12">';							 
					     echo '</div>						
							  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
									   '.$pagination.'
							  </div>
					  </div>';
				?>
    </div>
</div>

<style type="text/css">

.fixedHeader-floating{position:fixed;}

table.jambo_table tfoot {
    background: rgba(52,73,94,.94);
    color: #ECF0F1;
}
</style>
<script>
$(document).ready(function () {

	$("#time_range").daterangepicker({
		timePicker: !0,
		timePickerIncrement: 1,
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
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	});
	
	$('#OkFilter').click(function(){
			var no_of_records=$('#no_of_records').val();				
		 	$('#no_of_rows').val(no_of_records);			
	});
	
	//---------------------------------	
	
});
$(document).ready(function() {
	showDatatable('table-sort',[], [1,"asc" ] );
});
</script>