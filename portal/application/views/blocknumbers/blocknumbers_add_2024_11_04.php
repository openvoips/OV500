<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<h2>Add Destination Number</h2>
				<ul class="nav navbar-right panel_toolbox">
					<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
				</ul>
				<div class="clearfix"></div>
			</div>
			<div class="x_content">    
			<form action="<?php echo base_url();?>blocknumbers/add" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
				<input type="hidden" name="button_action" id="button_action" value="">
				<input type="hidden" name="action" value="OkSaveData">    
							
				<div class="form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12" >Destination Number <span class="required">*</span></label>
					<div class="col-md-4 col-sm-6 col-xs-12">
						<input type="text" name="dstnumber" id="dstnumber" value="<?php echo set_value('dstnumber'); ?>" data-parsley-required="" data-parsley-minlength="4"  class="form-control col-md-7 col-xs-12" placeholder="dstnumber">
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12" >Customer <span class="required">*</span></label>
					<div class="col-md-4 col-sm-6 col-xs-12">
						<select name="account_id" id="account_id" data-parsley-required="" class="form-control" >
							<option value="">Select</option>                    
							<?php
							$str = '';
							if(count($customer_options)>0)
							{
								foreach ($customer_options as $key => $single_array) {
									$selected = ' ';
									if (set_value('account_id') == $single_array['account_id'])
										$selected = '  selected="selected" ';
									$str .= '<option value="' . $single_array['account_id'] . '" ' . $selected . '>' . $single_array['company_name'] . " - " . $single_array['account_id'] . '</option>';
								}
							}
							echo $str;
							?>
						</select>
					</div>
				</div>
				
        
			
			<div class="ln_solid"></div>
            <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <a href="<?php echo base_url()?>blocknumbers"><button class="btn btn-primary" type="button">Cancel</button></a>				
                    <button type="button" id="btnSave" class="btn btn-success">Save</button>
                   
                </div>
            </div>
			</form>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>   
<script>
$('#btnSave, #btnSaveClose').click(function() {
	var is_ok = $("#add_form").parsley().isValid();
	if(is_ok === true)
	{
		var dstnumbercked_button_id = this.id;
		if(dstnumbercked_button_id=='btnSaveClose')
			$('#button_action').val('save_close');
		else
			$('#button_action').val('save');	
	
		$("#add_form").submit();
	}
	else
	{
		$('#add_form').parsley().validate();
	}
})
</script>