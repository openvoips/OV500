<div class="container-fluid">
	<div class="block-header">
		<h2>Blocked Destination Number</h2>
		<ul class="nav navbar-right panel_toolbox">
			<li><a href="<?php echo base_url() ?>blocknumbers/add"><input type="button" value="Add Number"
						name="add_link" class="btn btn-primary"></a></li>
		</ul>
	</div>


	<div class="row clearfix">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="card">
				<div class="header">


					<form class="block-content form-horizontal " id="search_form" name="search_form" method="post"
						action="<?php echo site_url('blocknumbers/index'); ?>">
						<input type="hidden" name="search_action" value="search" />
						<input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
						<div class="form-group">
							<label class="control-label col-md-2 col-sm-3 col-xs-12">Destination Number</label>
							<div class="col-md-4 col-sm-8 col-xs-12">
								<input type="text" name="dstnumber" id="dstnumber"
									value="<?php echo $_SESSION['search_blocknumbers_data']['dstnumber']; ?>"
									class="form-control data-search-field" placeholder="Number">
							</div>


						</div>

						<div class="form-group">
							<div class="searchBar text-center">
								<input type="submit" value="Search" name="OkFilter" id="OkFilter"
									class="btn btn-primary">
								<input type="button" value="Reset" name="search_reset" id="search_reset"
									class="btn btn-info">
								<div class="btn-group ">
									<button type="button" class="btn bg-blue-grey  dropdown-toggle"
										data-toggle="dropdown" value="Export" name="search_export" id="search_export">
										Export <span class="caret"></span></button>
									<ul class="dropdown-menu" role="menu">
										<?php
										$export_format_array = get_export_formats();
										foreach ($export_format_array as $export_format) {
											echo '<li><a href="' . base_url() . 'blocknumbers/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
										}
										?>
									</ul>
								</div>
							</div>



					</form>



				</div>
				<div class="body">
					<div class="row">
						<?php echo '<div class="btn-toolbar" role="toolbar">
			
			<div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
					 ' . $pagination . '
			</div>
		  </div>'; ?>
					</div>
					<div class="table-responsive">
						<table class="table table-bordered">
							<thead>
								<tr class="headings thc">
									<th class="column-title"><input type="checkbox" id="check-all" class="flat12"></th>
									<th class="column-title">Destination Number</th>
									<th class="column-title">Customer</th>
									<th class="column-title last">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (count($blocknumbers_data['result']) > 0) {
									foreach ($blocknumbers_data['result'] as $dnd_array) {
										?>
										<tr >
										<td class="a-center"><input type="checkbox" class="check-row" name="table_records"
												value="<?php echo $dnd_array['id']; ?>"></td>
										<td><?php echo $dnd_array['dstnumber']; ?></td>
										<td><?php if ($dnd_array['account_id'] != '')
											echo $dnd_array['company_name'] . '[' . $dnd_array['account_id'] . ']';
										?></td>
										<td class="last">
											<a href="javascript:void(0);" onclick=doConfirmDelete('<?php echo $dnd_array['id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>
										</td>
										</tr>
										<?php
									}
								} else {
									?>
									<tr>
										<td colspan="6" align="center"><strong>No Record Found</strong></td>
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
	</div>
</div>
</div>