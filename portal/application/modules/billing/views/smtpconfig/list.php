<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2021 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 2.0.0
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
            <h2>SMTP Configuration</h2>
            <ul class="nav navbar-right panel_toolbox">	
                <li><a href="<?php echo base_url() ?>Billing/smtpconfigadd"><input type="button" value="Add SMTP Config" name="add_link" class="btn btn-primary"></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>Billing/smtpconfig">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">SMTP Host</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="smtp_host" id="smtp_host" value="<?php echo $_SESSION['search_smtpconfig_data']['s_smtp_host']; ?>" class="form-control data-search-field" placeholder="SMTP Host">
                    </div>        

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">SMTP From Email</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="smtp_from" id="smtp_from" value="<?php echo $_SESSION['search_smtpconfig_data']['s_smtp_from']; ?>" class="form-control data-search-field" placeholder="SMTP From Email">
                    </div>      

                </div>
                <div class="form-group">		             
		    		<label class="control-label col-md-2 col-sm-3 col-xs-12">SMTP Port</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="smtp_port" id="smtp_port" value="<?php echo $_SESSION['search_smtpconfig_data']['s_smtp_port']; ?>" class="form-control data-search-field" placeholder="SMTP Port">
                    </div>  

                    <div class="searchBar col-md-offset-2 mt-2 text-right" >
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
	    dispay_pagination_row($total_records, $_SESSION['search_smtpconfig_data']['s_no_of_records'], $pagination);
	    ?>    
	</div>
        <div class="table-responsive">
            <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort1">
                <thead>
                    <tr class="headings thc">
                        <th class="column-title text-center">SMTP Host</th>  
                        <th class="column-title text-center">SMTP Host Name</th>  
                        <th class="column-title text-center">SMTP From Email</th>   
                        <th class="column-title text-center">SMTP From Name</th>   
                        <th class="column-title text-center">SMTP Port</th>    
                        <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>
                    </tr>
                </thead>

                <tbody>
		    <?php
		    if (count($smtp_data['result']) > 0) {
			foreach ($smtp_data['result'] as $smtp) {
			    //    print_r($cli);die;
			    ?>
			    <tr>
				<td class="text-left"><?php echo $smtp['smtp_host']; ?></td>
				<td class="text-left"><?php echo $smtp['smtp_host_name']; ?></td>
				<td class="text-left"><?php echo $smtp['smtp_from']; ?></td>
				<td class="text-left"><?php echo $smtp['smtp_from_name']; ?></td>
				<td class="text-left"><?php echo $smtp['smtp_port']; ?></td>
				<td class=" last">
				    <a href="<?php echo base_url(); ?>Billing/smtpconfigedit/<?php echo param_encrypt($smtp['smtp_config_id']); ?>" title="Edit"><i class="fa fa-pencil-square-o"></i></a>

                                    <a href="javascript:void(0);" onclick="doConfirmDelete('<?php echo $smtp['smtp_config_id']; ?>')" title="Delete"><i class="fa fa-trash"></i></a>


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
    $(document).ready(function() {
        $('#OkFilter').click(function() {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
    $(document).ready(function() {
        showDatatable('table-sort1', [5], [1, "asc"]);
    });
</script>