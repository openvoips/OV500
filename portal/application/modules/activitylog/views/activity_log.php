<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Site Activity Log</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
        <?php 
		$enable_hooks= $this->config->item('enable_hooks');
		if( $enable_hooks===false)
		{
			echo '<div class="alert alert-warning alert-dismissible fade in" role="alert" >
                		<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>'
                        . '$config[\'enable_hooks\'] is set FALSE. Set it TRUE in config.php'
                        . '</div>';
		}		
		?>
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url(); ?>activitylog">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
				
                
                <div class="form-group">
                	<label class="control-label col-md-2 col-sm-3 col-xs-12">Date</label>
                    <div class="col-md-5 col-sm-9 col-xs-12">
                        <input type="text" name="time_range" id="time_range" class="form-control data-search-field" value="" readonly="readonly" data-parsley-required="" />
                    </div> 
                </div>     
                
				<div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID/Name</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <input type="text" name="account_id" value="<?php echo $_SESSION[$search_session_key]['account_id']; ?>" class="form-control data-search-field">
                    </div>
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Page URL</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="page_url" value="<?php echo $_SESSION[$search_session_key]['page_url']; ?>" class="form-control data-search-field">
                    </div>
             </div>
             <div class="form-group">  
             		<label class="control-label col-md-1 col-sm-3 col-xs-12">IP</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <input type="text" name="ip_address" value="<?php echo $_SESSION[$search_session_key]['ip_address']; ?>" class="form-control data-search-field">
                    </div>
                     
                    
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Session ID</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="session_id" value="<?php echo $_SESSION[$search_session_key]['session_id']; ?>" class="form-control data-search-field">
                    </div>
				</div>
             <div class="form-group">    
             		<label class="control-label col-md-2 col-sm-3 col-xs-12">Group By</label>
                    <div class="control-label col-md-5 col-sm-9 col-xs-12">
                        <input type="checkbox" name="group_by_ip" value="Y" <?php if(isset($_SESSION[$search_session_key]['group_by']) && in_array('ip_address',$_SESSION[$search_session_key]['group_by'])) echo ' checked="checked" ';?> class="data-search-field"/> IP&nbsp;&nbsp;
                        <input type="checkbox" name="group_by_session" value="Y" <?php if(isset($_SESSION[$search_session_key]['group_by']) && in_array('session_id',$_SESSION[$search_session_key]['group_by'])) echo ' checked="checked" ';?> class=" data-search-field"/> Session&nbsp;&nbsp;
                        <input type="checkbox" name="group_by_page" value="Y" <?php if(isset($_SESSION[$search_session_key]['group_by']) && in_array('page_url',$_SESSION[$search_session_key]['group_by']))  echo ' checked="checked" ';?> class=" data-search-field"/> Page&nbsp;&nbsp;
                        <input type="checkbox" name="group_by_account" value="Y" <?php if(isset($_SESSION[$search_session_key]['group_by']) && in_array('account_id',$_SESSION[$search_session_key]['group_by']))  echo ' checked="checked" ';?> class=" data-search-field"/> Account
                    </div>
             
                       
						<div class="searchBar text-right">                         
							<input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
							<input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">
						</div>
					</div>
                </div>
              
              
            </form> 


            <div class="clearfix"></div>
            <div class="ln_solid"></div>
			
			
            <div class="row">  
                <?php	dispay_pagination_row($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination); ?>
            </div> 
           

            <div class="table-responsive">
                <table class="table table-striped jambo_table table-bordered" id="table-sort">
                    <thead>
                         <tr class="headings"> 
                         	<th class="column-title text-center">Activity Date </th>
                            <th class="column-title text-center">Activity Done By </th>
                            <th class="column-title text-center">Page </th>
                            <th class="column-title text-center">IP</th>
                            <th class="column-title text-center">Session</th>
                            <?php if($_SESSION[$search_session_key]['group_by']!=''){?>
                            <th class="column-title text-center">Count</th>
                            <?php }?>
                            <th class="column-title text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
					  <?php
						if(isset($activity_log_data['result']) && count($activity_log_data['result']) > 0)
						{		
							foreach($activity_log_data['result'] as $log_row)
							{																
								$created_by = $log_row['user_name'];
								if($log_row['account_id']!='')
									$created_by .= ' ('.$log_row['account_id'].')';
															
								$created_dt_timestamp = strtotime($log_row['created_dt']);
								$created_dt_display=date(DATE_FORMAT_2,$created_dt_timestamp);
								
								
						?>
							<tr>
							<td class="text-center"><?php echo $created_dt_display; ?></td>
							<td class="text-center"><?php echo $created_by; ?></td>
							<td class="text-center"><?php echo $log_row['page_url'] ;?></td>
							<td class="text-center1"><?php echo $log_row['ip_address'];?></td>
                            <td class="text-center1"><?php echo $log_row['session_id'];?></td>
                            <?php if($_SESSION[$search_session_key]['group_by']!=''){?>
                            <td class="text-center">
                            <?php if(isset($log_row['group_count']))
							 echo $log_row['group_count'];?>
                            </td>
                            <?php }?>
							<td class="text-center"><a href="<?php echo base_url('activitylog/details/' . param_encrypt($log_row['id']));  ?>" title="Log Details" class="highlight"><button type="button" class="btn btn-primary btn-xs">Details</button></a></td>
							</tr>
						<?php   
							} 
						}
						else{
							?>
							 <tr>
								<td colspan="6" align="center"><strong>No Record Found</strong></td>
							</tr>
						<?php }?>
						
                    </tbody>
                </table>
            </div>	
             <div class="row">  
                <?php dispay_pagination_row_bottom($total_records, $_SESSION['search_log_data']['s_no_of_records'], $pagination); ?>
             </div> 
      		

        </div>
    </div>
<?php //echo '<pre>';print_r(current($activity_log_data['result'])); print_r($_SESSION[$search_session_key]);echo '<pre>';?>
</div>
<script>
var time_range_val= "<?php echo $_SESSION[$search_session_key]['time_range']; ?>";
$(document).ready(function() {		
	$('#OkFilter').click(function(){
			var no_of_records=$('#no_of_records').val();				
		 	$('#no_of_rows').val(no_of_records);			
	});
	
	min_date = moment(new Date()).subtract(1,'months');
	$("#time_range").daterangepicker({
		timePicker: !0,
		timePickerIncrement: 1,
		
		locale: {
			format: "YYYY-MM-DD HH:mm:ss"
		},
		timePicker24Hour: true,
		ranges: {
			'Last 15 Minute': [moment().subtract(15, 'minute'), moment()],
			'Last 30 Minute': [moment().subtract(30, 'minute'), moment()],
			'Today': [moment().startOf('days'), moment().endOf('days')],
			'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	});
	
	if(time_range_val=='')
		$("#time_range").val('');
	else
		$("#time_range").val(time_range_val);
			
	
});
</script>