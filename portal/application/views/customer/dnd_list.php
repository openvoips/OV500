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

<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script> 
<div class="col-md-12 col-sm-12 col-xs-12 right">
    <div class="x_title">
        <h2>Customer Restricted number list Configuration Management</h2>
        <div class="clearfix"></div>
    </div>

</div>

<div class="col-md-6 col-sm-12 col-xs-12">
    <div class="x_panel">		

        <div class="x_title">
            <h2>Restricted Number</h2>
            <ul class="nav navbar-right panel_toolbox">
                				
                <li><a href="<?php echo base_url() ?>dnd/add"><input type="button" value="Add Restricted number" name="add_link" class="btn btn-primary"></a></li>				
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">

            <form class="block-content form-horizontal " id="frmSearchDnd" name="frmSearchDnd"  method="post" action="<?php echo base_url() ?>dnd/index" data-parsley-validate >
                <input type="hidden" name="search_action" value="search" />


                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12">Restricted Number</label>
                    <div class="col-md-8 col-sm-9 col-xs-12">
                        <input type="text" name="dnd_number" id="dnd_number" value="<?php echo $_SESSION['search_dnd_data']['s_dnd_number']; ?>" class="form-control data-search-field" placeholder="Restricted Number">
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
                            foreach ($export_format_array as $export_format) {
                                echo '<li><a href="' . base_url() . 'dnd/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
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
                
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="frmdndnumber" name="frmdndnumber"  method="post" action="<?php echo base_url() ?>dnd/index" data-parsley-validate  enctype="multipart/form-data"> 
                <div class="form-group">

                    <input type="hidden" name="action" value="OkSaveFile">
                    <div class="col-md-6 col-sm-9 col-xs-12">
                        <input type="file" name="file_dnd_number" id="file_dnd_number" class=""   data-parsley-fileextension='csv' />
                    </div>

                    <div class="col-md-3">			
                        <button type="button" id="btnSave" class="btn btn-primary" tabindex="">Import</button>
                    </div>


                </div>
                <div class="form-group">
                    <div class="col-md-6 col-sm-9 col-xs-12">File format: <small>CSV</small></div>


                </div>

                <div class="form-group">
                    <div class="col-md-12 text-primary">
                        <small>Restricted Number must be in Country + Number format. For example: 441202777001</small>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6 col-sm-9 col-xs-12">
                        <a href="<?php echo base_url('download/sample/' . param_encrypt('dnd_numbers')); ?>"><button type="button" class="btn btn-dark btn-sm">Download Sample File</button></a> 
                    </div>
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
						   ' . $pagination . '
				  </div>
				</div>'; ?>

            <div class="table-responsive">
                <table id="table-sort" class="table table-striped jambo_table bulk_action table-bordered">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title"><input type="checkbox" id="check-all" class="flat12"></th>
                            <th class="column-title">Restricted Number</th>

                            <th class="column-title last">Actions</th>
                            <th class="bulk-actions" colspan="10">
                                <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                            </th>				
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if (count($dnd_data['result']) > 0) {
                            foreach ($dnd_data['result'] as $dnd_array) {
                                ?>
                                <tr>
                                    <td class="a-center"><input type="checkbox" class="check-row" name="table_records" value="<?php echo $dnd_array['id']; ?>"></td>
                                    <td><?php echo $dnd_array['dnd_number']; ?></td>							

                                    <td class="last">							 
                                        <a href="javascript:void(0);" onclick=doConfirmDelete('<?php echo $dnd_array['id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>
                                    </td>	
                                </tr>
                                <?php
                            }
                        } else {
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
					   ' . $pagination . '
			  </div>
			</div>'; ?>         


        </div>

    </div>


</div>

<div class="col-md-12 col-sm-12 col-xs-12 right">
    <div class="x_title">
        <h2>Customer Restricted number list Configuration Management</h2>
        <div class="clearfix"></div>
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
    $(document).ready(function () {
        showDatatable('table-sort', [0, 5], [1, "desc"]);

        $('#btnSave').click(function () {
            var is_ok = $("#frmdndnumber").parsley().isValid();

            if (is_ok === true)
            {
                $("#frmdndnumber").submit();
            } else
            {
                $('#frmdndnumber').parsley().validate();
            }
        });
    });
</script>