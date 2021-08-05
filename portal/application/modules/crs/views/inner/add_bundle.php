<?php
$assigned_bundle_package_array = array();
if(isset($accountinfo['bundle_package']))
	$bundle_package_data = $accountinfo['bundle_package'];
else
	$bundle_package_data = array();	
?>
<div class="table-responsive">
<table class="table table-striped jambo_table table-bordered">
<thead>
    <tr class="headings thc">
    <th class="column-title">Bundle</th>
    <th class="column-title">Assign</th>
    <th class="column-title">Allowed Prefixes</th>
    <th class="column-title">Action</th>
    </tr>
</thead>
<tbody>
  <?php
  
	if (count($bundle_package_data) > 0) {
		foreach($bundle_package_data as $package_data) {
			$assigned_bundle_package_array[] = $package_data['bundle_package_id'];
			$delete_url = 'crs/editvoip/'.param_encrypt($package_data['account_id']).'/'.$key;
			?>
			<tr >
				<td><?php echo $package_data['bundle_package_name'] . ' (' . $package_data['bundle_package_id'] . ')'; ?></td>
				<td ><?php echo $package_data['bundle_count']; ?></td>
				<td ><?php echo wordwrap(implode(', ', array_unique(explode(',', $package_data['prefix']))), 20, "<br>\n", TRUE); ?></td>
				<td class=" last">                                           
					<?php if (check_account_permission('customer', 'edit')): ?>
						<a href="javascript:void(0);"
						   onclick=doConfirmDelete('<?php echo $package_data['bundle_account_id']; ?>','<?php echo $delete_url;?>','account_bundle_delete') title="One Bundle will delete at a time." class="delete"><i class="fa fa-trash"></i></a>
					   <?php endif; ?>
	
				</td>
			</tr>
			<?php
		}
	}
	else {
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
                    
                    
                    
                    
                    
<form action="" method="post" name="<?php echo 'tab_form_'.$key;?>" id="<?php echo 'tab_form_'.$key;?>" data-parsley-validate class="form-horizontal form-label-left">
<input type="hidden" name="button_action" id="button_action" value="">
<input type="hidden" name="action" value="OkSaveBundle">
<input type="hidden" name="tab" value="<?php echo $key;?>">
<input type="hidden" name="account_id" value="<?php echo $accountinfo['account_id']; ?>">
                    
   <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Bundle & Package <span class="required">*</span> </label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <select name="bundle_package_id" id="bundle_package_id" class="form-control col-md-7 col-xs-12" data-parsley-required="">
                <option value="">Select Bundle</option> 
				<?php
                if (count($bundle_data) > 0) {
                    foreach ($bundle_data as $bundle_row) {
						
                        echo '<option value="' . $bundle_row['bundle_package_id'] . '">' . $bundle_row['bundle_package_name'] . ' (' . $bundle_row['bundle_package_id'] . ')</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>
	<div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Number of Packages<span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input name="no_of_package" id="no_of_package" class="form-control col-md-7 col-xs-12" value="<?php echo set_value('no_of_package','1'); ?>" data-parsley-required="" data-parsley-type="digits">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Description</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <textarea name="bundle_package_desc" id="bundle_package_desc" class="form-control col-md-7 col-xs-12"> <?php echo set_value('bundle_package_desc'); ?></textarea>
        </div>
    </div>



    <div class="ln_solid"></div>
    <div class="form-group">
        <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
            <button type="button" id="<?php echo 'btnSaveClose'.$key;?>" class="btn btn-info" onclick="save_button('<?php echo $key;?>')">Add Bundle</button>
        </div>
    </div>

</form>