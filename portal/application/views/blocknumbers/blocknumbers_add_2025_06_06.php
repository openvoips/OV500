<div class="container-fluid">
	<div class="block-header">
		<h2>Add Destination Number</h2>
		<ul class="nav navbar-right panel_toolbox">
			<li><a href="<?php echo site_url('blocknumbers') ?>"><button class="btn btn-primary" type="button">Back to
						Destination Number Listing Page</button></a> </li>
		</ul>
	</div>
	<div class="row clearfix">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

				<div class="card">
					<div class="header">

						<form action="<?php echo base_url(); ?>blocknumbers/add" method="post" name="add_form" id="add_form"
							data-parsley-validate class="form-horizontal form-label-left">
							<input type="hidden" name="button_action" id="button_action" value="">
							<input type="hidden" name="action" value="OkSaveData">


							<div class="form-group">
								<label class="control-label col-md-4 col-sm-5 col-xs-12" for="first-name">Destination Number <span	class="required">*</span>
								</label>
								<div class="col-md-6 col-sm-6 col-xs-12">

								<input type="text" name="dstnumber" id="dstnumber" value="<?php echo set_value('dstnumber'); ?>" data-parsley-required="" data-parsley-minlength="4"  class="form-control col-md-7 col-xs-12" placeholder="dstnumber">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-4 col-sm-5 col-xs-12" for="first-name">Customer <span
										class="required">*</span>
								</label>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<select name="account_id" id="account_id" data-parsley-required=""
										class="form-control">
										<option value="">Select</option>
										<?php
										$str = '';
										if (count($customer_options) > 0) {
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
								<div class="col-md-10 col-sm-10 col-xs-12 text-right">
									<button type="button" id="btnSave" class="btn btn-success">Save</button>
								</div>
							</div>

						</form>
					</div>
				</div>

			</div>
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

				<div class="card">
					<div class="header">
						<form class="block-content form-horizontal " id="add_form2" name="add_form2" method="post"
							action="<?php echo base_url() ?>blocknumbers/add" data-parsley-validate
							enctype="multipart/form-data">
							<div class="form-group">
							
								<input type="hidden" name="action" value="OkSaveFile">
								
							<input type="hidden" name="button_action" id="button_action2" value="">


								<div class="form-group">
									<label class="control-label col-md-4 col-sm-5 col-xs-12" for="first-name">Customer <span
											class="required">*</span>
									</label>
									<div class="col-md-6 col-sm-6 col-xs-12">
									<select name="account_id" id="account_id" data-parsley-required=""
											class="form-control">
											<option value="">Select</option>
											<?php
											$str = '';
											if (count($customer_options) > 0) {
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

						

								<div class="form-group">
									<label class="control-label col-md-4 col-sm-5 col-xs-12" for="first-name"></label>
									<div class="col-md-6 col-sm-6 col-xs-12">
									<input type="file" name="file_blocknumbers_number" id="file_blocknumbers_number" class=""
									data-parsley-required=""	data-parsley-fileextension='csv' />
										</div>
								</div>

							
								<div class="form-group">
									<div class="col-md-10 col-sm-10 col-xs-12 text-right">
										<button type="button" id="btnSave2" class="btn btn-success">Import</button>
									</div>
								</div>


							</div>
							<div class="form-group">
								<div class="col-md-10 col-sm-10 col-xs-12">File format: <small>CSV</small></div>


							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="block-header">
		<h2>Add Destination Number</h2>
		<ul class="nav navbar-right panel_toolbox">
			<li><a href="<?php echo site_url('blocknumbers') ?>"><button class="btn btn-primary" type="button">Back to
						Destination Number Listing Page</button></a> </li>
		</ul>
	</div>
</div>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>