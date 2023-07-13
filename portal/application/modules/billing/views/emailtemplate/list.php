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
//echo '<pre>';
//print_r($providers_data);
//echo '</pre>';
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Email Template</h2>
            <ul class="nav navbar-right panel_toolbox">		
    		<li><a href="<?php echo base_url() ?>Billing/emailtemplateadd"><input type="button" value="Add Template" name="add_link" class="btn btn-primary"></a></li>	
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>Billing/emailtemplate">
                <input type="hidden" name="search_action" value="search" />
		<input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="account_id" id="account_id" value="<?php echo $_SESSION['search_EmailTemplate_data']['s_account_id']; ?>" class="form-control data-search-field" placeholder="Account ID" >
                    </div> 
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Email Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="email_name" id="email_name" value="<?php echo $_SESSION['search_EmailTemplate_data']['s_email_name']; ?>" class="form-control data-search-field" placeholder="Email Name">
                    </div>            

                </div>
                <div class="form-group">
		    <label class="control-label col-md-2 col-sm-3 col-xs-12">Email Subject</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="email_subject" id="email_subject" value="<?php echo $_SESSION['search_EmailTemplate_data']['s_email_subject']; ?>" class="form-control data-search-field" placeholder="Email Subject">
                    </div>   
		    <label class="control-label col-md-2 col-sm-3 col-xs-12">Template For</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="template_for" id="template_for" value="<?php echo $_SESSION['search_EmailTemplate_data']['s_template_for']; ?>" class="form-control data-search-field" placeholder="Template For">
                    </div>  

                    <div class="searchBar col-md-offset-2 mt-2" style="margin-top: 45px;">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary ">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info ">


                    </div>
                </div>


            </form> 

        </div>


        <div class="clearfix"></div>
        <div class="ln_solid"></div>
	<div class="row">  
	    <?php
	    dispay_pagination_row($total_records, $_SESSION['search_EmailTemplate_data']['s_no_of_records'], $pagination);
	    ?>    
	</div>
        <div class="table-responsive">
            <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort1">
                <thead>
                    <tr class="headings thc">
                        <th class="column-title text-center">Email Name</th>
			<th class="column-title text-center">Template For</th>  
			<th class="column-title text-center">Email Subject</th>  
                        <th class="column-title text-center">Email Body</th>   
			<th class="column-title text-center">Email BCC</th>   
			<th class="column-title text-center">Email CC</th>    
			<th class="column-title text-center">Email Daemon</th>    
                        <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>

                    </tr>
                </thead>

                <tbody>
		    <?php
		    if(count($template_data['result']) > 0) {
			foreach ($template_data['result'] as $template) {
			    //    print_r($cli);die;
			    ?>
			    <tr>

				<td class="text-left"><?php echo $template['email_name']; ?></td>
				<td class="text-left"><?php echo $template['template_for']; ?></td>
				<td class="text-left"><?php echo $template['email_subject']; ?></td>
				<td class="text-left"><?php echo $template['email_body']; ?></td>
				<td class="text-left"><?php echo $template['email_bcc']; ?></td>
				<td class="text-left"><?php echo $template['email_cc']; ?></td>
				<td class="text-left"><?php echo $template['email_daemon']; ?></td>
				<td class=" last">
				    <a href="<?php echo base_url(); ?>Billing/emailtemplateedit/<?php echo param_encrypt($template['id']); ?>" title="Edit"><i class="fa fa-pencil-square-o"></i></a>

				    <a href="javascript:void(0);" onclick="doConfirmDelete('<?php echo $template['id']; ?>')" title="Delete"><i class="fa fa-trash"></i></a>


				</td>
			    </tr>

			    <?php
			}
		    } else {
			?>
    		    <tr>
    			<td colspan="8" align="center"><strong>No Record Found</strong></td>
    		    </tr>
			<?php
		    }
		    ?>


                </tbody>
            </table>


        </div>             
	<?php
	echo '<div class="btn-toolbar" role="toolbar">
                  <div class="btn-group col-md-5 col-sm-12 col-xs-12">';
	echo '</div>
            
                  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
                           ' . $pagination . '
                  </div>
                </div>';
	?>   

    </div>
</div>
<script>
    $(document).ready(function () {
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
    $(document).ready(function () {
        showDatatable('table-sort1', [7], [1, "asc"]);
    });
</script>