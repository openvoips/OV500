<?php
$created_dt_timestamp = strtotime($activity_log_data['created_dt']);
$created_dt_display=date(DATE_FORMAT_2,$created_dt_timestamp);

$created_by = $activity_log_data['user_name'];
if($activity_log_data['account_id']!='')
	$created_by .= ' ('.$activity_log_data['account_id'].')';
  
?>

<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Site Activity Log Details</h2>
        <ul class="nav navbar-right panel_toolbox">
          <li>
            <a href="<?php echo base_url('activitylog/index');  ?>" title="Site Activity Log" class="highlight pull-right"><input type="button" value="Back to Site Activity Log Page" name="" id="" class="btn btn-danger"></a></li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content"> 
         
        <table class="table table-striped table-bordered table-collapsed">
            <tr><td><strong>Activity Date</strong> </td> <td><?php echo $created_dt_display; ?></td></tr>
            <tr><td><strong>Activity Done By</strong> </td> <td><?php echo $created_by; ?></td></tr>
            <tr><td><strong>IP</strong> </td> <td><?php echo $activity_log_data['ip_address']; ?></td></tr>
            <tr><td><strong>Page</strong> </td> <td><?php echo $activity_log_data['page_url']; ?></td></tr>
            <tr><td><strong>Session</strong> </td> <td><?php echo $activity_log_data['session_id']; ?></td></tr>
            <tr><td><strong>Referrer URL </strong> </td> <td><?php echo $activity_log_data['referrer_url']; ?></td></tr>
            <tr><td><strong>User Agent </strong> </td> <td><?php echo $activity_log_data['user_agent']; ?></td></tr>
            <tr><td><strong>CI Controller/Method </strong> </td> <td><?php echo $activity_log_data['ci_class_method']; ?></td></tr>
          </table>
     
        <div class="clearfix"></div>
      </div>
    </div>
    
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Site Activity Log</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('activitylog/index');  ?>" title="Site Activity Log" class="highlight pull-right"><input type="button" value="Back to Site Activity Log Page" name="" id="" class="btn btn-danger"></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

