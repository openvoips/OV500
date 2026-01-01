<div class="container-fluid">
    <div class="block-header">
        <h2>Diversion Management</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url('carriers/adddiversion/') . param_encrypt($carrier_id) ?>"><input type="button" value="Add Diversion" name="add_link" class="btn btn-primary"></a> 
                <a href="<?php echo base_url('carriers/addbulkdiversion/') . param_encrypt($carrier_id) ?>"><input type="button" value="Add Bulk Diversion" name="add_link" class="btn btn-primary"></a> 
                <a href="<?php echo base_url('carriers') ?>"><input type="button" value="Back To Carrier Listing Page" name="add_link" class="btn btn-primary"></a></li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">



                    <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url('carriers/diversion/') . param_encrypt($carrier_id); ?>">
                        <input type="hidden" name="search_action" value="search" />
                        <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />

                        <div class="form-group">

                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Customer Account Id</label>
                            <div class="col-md-4 col-sm-8 col-xs-12">
                                <input type="text"  name="account_id" id="diversion_number" data-parsley-required="" value="<?php echo $_SESSION[$search_session_key]['account_id']; ?>" class="form-control data-search-field" placeholder="Customer Account Id">
                            </div>
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Customer Name</label>
                            <div class="col-md-4 col-sm-8 col-xs-12">
                                <input type="text"  name="name" id="diversion_number" data-parsley-required="" value="<?php echo $_SESSION[$search_session_key]['name']; ?>" class="form-control data-search-field" placeholder="Customer Name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Diversion Number</label>
                            <div class="col-md-4 col-sm-8 col-xs-12">
                                <input type="text"  name="diversion_number" id="diversion_number" data-parsley-required="" value="<?php echo $_SESSION[$search_session_key]['diversion_number']; ?>" class="form-control data-search-field" placeholder="Diversion Number">
                            </div>
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-4 col-sm-8 col-xs-12">
                                <select name="number_status" id="number_status" class="form-control data-search-field">
                                    <option value="">ALL</option>
                                    <option value="1" <?php if ($_SESSION[$search_session_key]['number_status'] == '1') echo 'selected'; ?>>Active</option>
                                    <option value="0" <?php if ($_SESSION[$search_session_key]['number_status'] == '0') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="searchBar col-md-offset-2 ">
                                <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                                <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                           

                            </div>
                        </div>


                    </form> 

                    <div class="clearfix"></div>
                    <div class="ln_solid"></div>           
                    <div class="body">
                        <div class="row">  
                            <?php dispay_pagination_row_bsd($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination); ?>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped jambo_table table-bordered" id="table-sort">
                                <thead>
                                    <tr class="headings ">   
                                        <th><input type="checkbox" id="check-all1" class="check-all1" /></th>
                                        <th class="column-title">Carrier</th>
                                        <th class="column-title">Diversion Number</th>
                                        <th class="column-title">Customer</th>
                                        <th class="column-title">Status</th>
                                        <th class="column-title">Assign Date</th>
                                        <th class="column-title no-link last"><span class="nobr">Actions</span> </th>

                                    </tr>
                                </thead>		
                                <tbody>
                                    <?php
                                    if ($diversion_data['result'] > 0) {
                                        foreach ($diversion_data['result'] as $listing_row) {
                                            ?>
                                            <tr>
                                                <?php
                                                echo '<td class="a-center ">
						<input type="checkbox" class="check-row1" name="did_number_list" id="lead_id_list_' . $listing_row['id'] . '" value="' . $listing_row['diversion_number'] . '"></td>';
                                                $company_name = '';
                                                if (isset($listing_row['company_name'])) {
                                                    $company_name = $listing_row['company_name'] . ' (' . $listing_row['account_id'] . ')';
                                                }
                                                ?>

                                                <td><?php echo $listing_row['carrier_name'] . ' (' . $listing_row['carrier_id'] . ')'; ?></td>
                                                <td><?php echo $listing_row['diversion_number']; ?></td>
                                                <td><?php echo $company_name; ?></td>

                                                <?php
                                                $status = '';
                                                if ($listing_row['number_status'] === '1')
                                                    $status = '<span class="label label-success">Active</span>';
                                                else
                                                    $status = '<span class="label label-danger">Inactive</span>';
                                                ?>

                                                <td><?php echo $status; ?></td>
                                                <td><?php echo $listing_row['assign_date']; ?></td>
                                                <td class=" last" >
                                                    <a href="<?php echo base_url('carriers/editdiversion/') . param_encrypt($carrier_id) . '/' . param_encrypt($listing_row['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                                    <a href="javascript:doConfirmDelete('<?php echo param_encrypt($listing_row['id']); ?>');" title="Delete" class="delete"><i class="fa fa-trash"></i></a>	
                                                    </a>




                                                </td>
                                            </tr>

                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="8" align="center" style="font-size:14px"><strong>No Record Found</strong></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>                    
                        <div class="row">  
                            <?php dispay_pagination_row_bsd($total_records, $_SESSION[$search_session_key]['no_of_records'], $pagination); ?>
                        </div> 
                    </div>
                </div>
                <div class=" hide float-right" id="id_bulk_div">

                    <div class="x_panel">
                        <div class="x_content">
                            <button type="button" id="btnDeleteBulk" class="btn btn-danger btn-lg btn-block" title="Cancel" onclick="DoBulkDeleteConfirm()">Delete Selected Diversion (<small>s</small>) <i class="fa fa-trash-o"></i></button>
                            <form action="<?php echo base_url('carriers/diversion/') . param_encrypt($carrier_id); ?>" method="post" name="cancel_form" id="cancel_form" class="form-horizontal form-label-left">
                                <input type="hidden" name="cancel_diversion_number" id="cancel_did_number" value="">
                                <input type="hidden" name="action" value="OkDeleteDataBulk" >                
                            </form> 
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div> 
</div>
<script>
    function DoBulkDeleteConfirm()
    {
        var modal_body = '<h1 class="text-center"><i class="fa fa-exclamation-circle"></i></h1>' +
                '<h4 class="text-center">Are you sure!</h4>' +
                '<p class="text-center">You won\'t be able to revert this!</p>';

        var modal_footer = '<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>' +
                '<button type="button" class="btn btn-danger" id="modal-btn-yes-single">Yes. delete it!</button>';

        openModal('', '', modal_body, modal_footer);
        $("#my-modal").modal('show');
        $("#modal-btn-yes-single").on("click", function () {

            var lead_id_array2 = [];
            $.each($("input[name='did_number_list']:checked"), function () {
                lead_id_array2.push($(this).val());
            });
            $('#cancel_did_number').val(lead_id_array2);
            $("#cancel_form").submit();
            $("#my-modal").modal('hide');

        });
    }
    $('#check-all1').change(function () {
        var is_all_checked = document.getElementById("check-all1").checked;
        $(':checkbox.check-row1').prop('checked', this.checked);
        $(".check-row1").trigger("change");
    });

    $('.check-row1').change(function () {
        var total_checked = $("input[name='did_number_list']:checked").length;
        if (total_checked > 0)
        {
            $('#id_bulk_div').removeClass('hide');
        } else
        {
            $('#id_bulk_div').addClass('hide');
            $('#check-all1').prop('checked', false);
        }
    });
    $(document).ready(function () {
        showDatatable('table-sort', [5], [1, "asc"]);
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
</script>
