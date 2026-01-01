<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

 <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="x_panel">		
		
		<div class="x_title">
			<h2>Blocked Destination Number</h2>
			<ul class="nav navbar-right panel_toolbox">
		  		<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>				
		  		<li><a href="<?php echo base_url()?>blocknumbers/add"><input type="button" value="Add Number" name="add_link" class="btn btn-primary"></a></li>				
			</ul>
		<div class="clearfix"></div>
		</div>
	
      <div class="x_content">
		  
		 <form class="block-content form-horizontal " id="frmSearchDnd" name="frmSearchDnd"  method="post" action="<?php echo base_url()?>blocknumbers/index" data-parsley-validate >
			<input type="hidden" name="search_action" value="search" />
			
			
			<div class="form-group">
				<label class="control-label col-md-3 col-sm-3 col-xs-12">Destination Number</label>
				<div class="col-md-6 col-sm-9 col-xs-12">
				  	<input type="text" name="dstnumber" id="dstnumber" value="<?php echo $_SESSION['search_blocknumbers_data']['dstnumber']; ?>" class="form-control data-search-field" placeholder="CLI">
				</div>
           				
			</div>
			<br/>
			 <div class="form-group pull-right">      
					<input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
					<input type="submit" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                     
					<div class="btn-group">
						<button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
						Export <span class="caret"></span></button>
						<ul class="dropdown-menu" role="menu">
						<?php
						$export_format_array = get_export_formats();
						foreach($export_format_array as $export_format)
						{
							echo '<li><a href="'.base_url().'blocknumbers/index/export/'.param_encrypt($export_format).'">'.strtoupper($export_format).'</a></li>';
						}							
						?>                            
						</ul>
					</div>
					
				</div>
			</form> 
		
		  
		  </div>
		  
		</div>
</div>


 <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>File Upload</h2>
            <ul class="nav navbar-right panel_toolbox">
              <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
            </ul>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">
		 <form class="block-content form-horizontal " id="frmdndnumber" name="frmdndnumber"  method="post" action="<?php echo base_url()?>blocknumbers/index" data-parsley-validate  enctype="multipart/form-data"> 
		  <div class="form-group">
		  
				<input type="hidden" name="action" value="OkSaveFile">

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

				<div class="col-md-6 col-sm-9 col-xs-12">
				  	 <input type="file" name="file_blocknumbers_number" id="file_blocknumbers_number" class=""   data-parsley-fileextension='csv' />
				</div>
				
				<div class="col-md-3">			
					<button type="button" id="btnSave" class="btn btn-primary" tabindex="">Import</button>
				</div>
				
					
				</div>
				<div class="form-group">
					<div class="col-md-6 col-sm-9 col-xs-12">File format: <small>CSV</small></div>
					
				
				</div>
				
				
				
				
				
			</form>
		  
		  </div>
		  
		</div>
</div>

<div class="col-md-12 col-sm-6 col-xs-12">
 <div class="x_panel">
	
   <div class="x_content">   
  
					
			<?php echo '<div class="btn-toolbar" role="toolbar">
			
				  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
						   '.$pagination.'
				  </div>
				</div>';?>
			
			<div class="table-responsive">
			<table id="table-sort" class="table table-striped jambo_table bulk_action table-bordered">
				<thead>
			  	<tr class="headings">
					<th class="column-title"><input type="checkbox" id="check-all" class="flat12"></th>
					<th class="column-title">Destination Number</th>	
					<th class="column-title">Customer</th>				               					
					<th class="column-title last">Actions</th>
					<th class="bulk-actions" colspan="5">
					  <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
					</th>				
				 </tr>
				</thead>		
				<tbody>
				<?php
				if(count($blocknumbers_data['result']) > 0)
				{								
					foreach($blocknumbers_data['result'] as $dnd_array)
					{	
						
					?>
						<tr>
							<td class="a-center"><input type="checkbox" class="check-row" name="table_records" value="<?php echo $dnd_array['id'];?>"></td>
							<td><?php echo $dnd_array['dstnumber'];?></td>	
							<td><?php if($dnd_array['account_id']!='')
							echo $dnd_array['company_name'].'['.$dnd_array['account_id'].']';
							?></td>							
							<td class="last">							 
							 <a href="javascript:void(0);" onclick=doConfirmDelete('<?php echo $dnd_array['id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>
							</td>	
						</tr>
						<?php						
					}
				}
				else
				{
				?>
					<tr>
						<td colspan="7" align="center"><strong>No Record Found</strong></td>
					</tr>
					<?php
					}
					?>	
					
		
			   </tbody>
		  </table>		
		  </div>
		   
		       <?php echo '<div class="btn-toolbar" role="toolbar">
			  <div class="btn-group col-md-5 col-sm-12 col-xs-12">
				<button type="button" id="btnBulkDelete" class="btn btn-warning " disabled="disabled">Delete Selected</button>
			  </div>
		
			  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
					   '.$pagination.'
			  </div>
			</div>';?>         
   
   
   </div>
 
 </div>


</div>

<script type="text/javascript">
window.ParsleyValidator
        .addValidator('fileextension', function (value, requirement) {
            var fileExtension = value.split('.').pop();
            
            return fileExtension === requirement;
        }, 32)
        .addMessage('en', 'fileextension', 'The extension does not match the required');

	
</script>
<script>
 $(document).ready(function() { 
	showDatatable('table-sort', [0,2], [ 1,"desc" ] );
		
	$('#btnSave').click(function() { 
		var is_ok = $("#frmdndnumber").parsley().isValid();
		
		if(is_ok === true)
		{
			$("#frmdndnumber").submit();
		}
		else
		{
			$('#frmdndnumber').parsley().validate();
		}
	});	
});
</script>