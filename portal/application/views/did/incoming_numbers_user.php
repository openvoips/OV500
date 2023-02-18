
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Incoming Numbers</h2>
            <ul class="nav navbar-right panel_toolbox">

                <?php if (check_logged_user_group(array(ADMIN_ACCOUNT_ID))): ?>
                    <li><a href="<?php echo base_url() ?>dids/add"><input type="button" value="Add Incoming Number" name="add_link" class="btn btn-primary"></a></li>
                <?php elseif (check_logged_user_group(array('CUSTOMER', 'RESELLER'))): ?>
                    <li><a href="<?php echo base_url() ?>dids/purchase_did"><input type="button" value="Purchase DID" name="add_link" class="btn btn-primary"></a></li>
                <?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>dids/index/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">DID</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="did_number" id="did_number" value="<?php echo $_SESSION['search_did_data']['s_did_number']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>            
                    <?php
                    //if type user, dont display
                    if (!check_logged_user_group(array('CUSTOMER'))):
                        ?>
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Assigned To</label>
                        <div class="col-md-2 col-sm-8 col-xs-12">
                            <input type="text"  name="assigned_to" id="assigned_to"  value="<?php echo $_SESSION['search_did_data']['s_assigned_to']; ?>" class="form-control data-search-field" placeholder="Assigned To">
                        </div>

                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <select name="status" id="status" class="form-control data-search-field">
                                <option value="">Select</option>
                                <option value="NEW" <?php if ($_SESSION['search_did_data']['s_status'] == 'NEW') echo 'selected="selected"'; ?> >NEW</option>
                                <option value="USED" <?php if ($_SESSION['search_did_data']['s_status'] == 'USED') echo 'selected="selected"'; ?>>USED</option>
                                <?php
                                if (check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {
                                    ?>
                                    <option value="DEAD" <?php if ($_SESSION['search_did_data']['s_status'] == 'DEAD') echo 'selected="selected"'; ?>>DEAD</option>
                                    <option value="BLOCKED" <?php if ($_SESSION['search_did_data']['s_status'] == 'BLOCKED') echo 'selected="selected"'; ?>>BLOCKED</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>

                    <?php endif; ?>
                </div>
                <div class="form-group">

                    <div class="searchBar ">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary ">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info ">
                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url() . 'dids/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>                            
                            </ul>
                        </div>

                    </div>
                </div>


            </form> 

        </div>


        <div class="clearfix"></div>
        <div class="ln_solid"></div>

        <div class="row">  
            <?php
            dispay_pagination_row($total_records, $_SESSION['search_did_data']['s_no_of_records'], $pagination);
            ?>    
        </div>

        <div class="table-responsive">
            <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                <thead>
                    <tr class="headings thc">  
                        <th><input type="checkbox" id="check-all1" class="check-all1" /></th>
                        <?php
                        foreach ($all_field_array as $field_lebel) {
                            echo '<th class="column-title">' . $field_lebel . '</th>';
                        }
                        ?>                        
                        <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>
                        <th class="bulk-actions" colspan="7">
                            <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (isset($did_data['result']) && count($did_data['result']) > 0) {
                        foreach ($did_data['result'] as $did_data) {
                            $did_status = strtolower($did_data['did_status']);
                            $status = ucfirst($did_status);
                            $is_deletable = false;
                            $is_cancelable = false;
                            if (check_logged_user_group(array('CUSTOMER'))) {
                                $is_cancelable = true;
                            } elseif (check_logged_user_group(array('RESELLER'))) {
                                if ($did_data['account_id'] == '')
                                    $is_cancelable = true;
                            }


                            if (check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {
                                if ($did_status == 'new')
                                    $is_deletable = true;
                            }

                            //$tariff_name=isset($did_data['tariff']['tariff_name']) ? $did_data['tariff']['tariff_name']: '';	


                            echo '<tr>';
                            echo '<td class="a-center ">
						<input type="checkbox" class="check-row1" name="did_number_list" id="lead_id_list_' . $did_data_single['did_id'] . '" value="' . $did_data['did_number'] . '"></td>';

                            foreach ($all_field_array as $field_name => $field_lebel) {
                                echo '<td>';
                                if ($field_name == 'did_status') {
                                    $status = strtolower($did_data[$field_name]);
                                    if ($status == 'new')
                                        $status = '<span class="label label-primary">' . ucfirst($status) . '</span>';
                                    elseif ($status == 'used')
                                        $status = '<span class="label label-info">' . ucfirst($status) . '</span>';
                                    elseif ($status == 'dead')
                                        $status = '<span class="label label-danger">' . ucfirst($status) . '</span>';
                                    elseif ($status == 'blocked')
                                        $status = '<span class="label label-warning">' . ucfirst($status) . '</span>';
                                    else
                                        $status = '<span class="label label-primary">' . ucfirst($status) . '</span>';

                                    echo $status;
                                }elseif ($field_name == 'reseller1_assign_date' || $field_name == 'reseller2_assign_date' || $field_name == 'reseller3_assign_date' || $field_name == 'create_date') {
                                    $date_display = $did_data[$field_name];
                                    if ($did_data[$field_name] != '') {
                                        $date_timestamp = strtotime($did_data[$field_name]);
                                        $date_display = date(DATE_FORMAT_1, $date_timestamp);
                                    }
                                    echo $date_display;
                                } elseif ($field_name == 'dst_type' || $field_name == 'dst_destination') {
                                    if (isset($did_data['did_dst'][$field_name]))
                                        echo $did_data['did_dst'][$field_name];
                                }
                                elseif ($field_name == 'did_number') {
                                    $did_name_display = '';
                                    if (isset($did_data[$field_name]))
                                        $did_name_display = $did_data[$field_name];
                                    if (isset($did_data['did_name']) && $did_data['did_name'] != '')
                                        $did_name_display .= ' (' . $did_data['did_name'] . ')';

                                    echo $did_name_display;
                                } else
                                    echo $did_data[$field_name];
                                echo '</td>';
                            }
                            echo '<td class=" last">';
                            if (check_logged_user_group('CUSTOMER')) {
                                echo '<a href="' . base_url() . 'dids/edit/' . param_encrypt($did_data['did_id']) . '" title="Details"><i class="fa fa-list"></i></a>';
                                echo ' <a href="' . base_url() . 'dids/config/' . param_encrypt($did_data['did_id']) . '" title="Edit"><i class="fa fa-pencil-square-o"></i></a>';
                            } else
                                echo ' <a href="' . base_url() . 'dids/edit/' . param_encrypt($did_data['did_id']) . '" title="Edit"><i class="fa fa-pencil-square-o"></i></a>';

                            if ($is_deletable) {
                                echo ' <a href="javascript:void(0);"  onclick=doConfirmDelete(\'' . $did_data['did_id'] . '\') title="Delete"><i class="fa fa-trash"></i></a>';
                            }

                            if ($is_cancelable) {
                                echo ' <a href="javascript:void(0);"  onclick=doConfirmDelete(\'' . $did_data['did_id'] . '\') title="Cancel"><i class="fa fa-trash-o"></i></a>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="19" align="center"><strong>No Record Found</strong></td>
                        </tr>
                        <?php
                    }
                    ?>


                </tbody>
            </table>
        </div>             
        <div class="row">  
            <?php
            dispay_pagination_row_bottom($total_records, $_SESSION['search_did_data']['s_no_of_records'], $pagination);
            ?>    
        </div>  

    </div>

    <div class=" hide" id="id_bulk_div"><!---Bulk update section--->
        <div class="col-md-7 col-sm-12 col-xs-12">

            <div class="x_panel">
                <div class="x_title">
                    <h2>DID Configuration</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form action="<?php echo site_url('dids'); ?>" method="post" name="assign_form" id="assign_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="assign_did_number" id="assign_did_number" value="">
                        <input type="hidden" name="action" value="OkUpdateDestinationBulk">

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Destination Type <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select class="form-control" name="dst_type" id="dst_type" tabindex="<?php echo $tab_index++; ?>" data-parsley-required="">
                                    <option value="">Select Type</option>
                                    <option value="CUSTOMER" >USER Based</option>
                                    <option value="IP" selected="selected">IP Based</option>
                                    <option value="PSTN" >PSTN Number</option>

                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Destination Endpoint <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select class="form-control" name="dst_point_ip" id="dst_point_ip" tabindex="<?php echo $tab_index++; ?>" data-parsley-required="">
                                    <option value="">Select IP Address</option>
                                    <?php
                                    if (count($did_enduser['ip']) > 0) {
                                        foreach ($did_enduser['ip'] as $k => $ip_data) {
                                            if ($ip_data['ip_status'] == '1'):
                                                ?>
                                                <option value="<?php echo $ip_data['ipaddress']; ?>" ><?php echo $ip_data['ipaddress']; ?></option>	
                                                <?php
                                            endif;
                                        }
                                    }
                                    ?>
                                </select>


                                <select class="form-control" name="dst_point_sip" id="dst_point_sip" tabindex="<?php echo $tab_index++; ?>"  <?php echo 'style="display:none;"'; ?>>
                                    <option value="">Select User</option>
                                    <?php
                                    if (count($did_enduser['sipuser']) > 0) {
                                        foreach ($did_enduser['sipuser'] as $k => $sip_data) {
                                            if ($sip_data['status'] == '1'):
                                                ?>
                                                <option value="<?php echo $sip_data['username']; ?>"><?php echo $sip_data['username']; ?></option>	
                                                <?php
                                            endif;
                                        }
                                    }
                                    ?>
                                </select>




                                <input type="text" name="dst_point_pstn" id="dst_point_pstn" value="" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo 'style="display:none;"'; ?>>
                            </div>
                        </div>





                        <div class="form-group" id="div_dst_type">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Failover Destination Type </span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select class="form-control" name="dst_type2" id="dst_type2" tabindex="<?php echo $tab_index++; ?>" >
                                    <option value="">Select Type</option>
                                    <option value="CUSTOMER" >USER Based</option>
                                    <option value="IP" >IP Based</option>
                                    <option value="PSTN" >PSTN Number</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="div_dst_endpoint">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Failover Destination Endpoint </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select class="form-control" name="dst_point2_ip" id="dst_point2_ip" tabindex="<?php echo $tab_index++; ?>" <?php echo 'style="display:none;"'; ?>>
                                    <option value="">Select IP Address</option>
                                    <?php
                                    if (count($did_enduser['ip']) > 0) {
                                        foreach ($did_enduser['ip'] as $k => $ip_data) {
                                            if ($ip_data['ip_status'] == '1'):
                                                ?>
                                                <option value="<?php echo $ip_data['ipaddress']; ?>" ><?php echo $ip_data['ipaddress']; ?></option>	
                                                <?php
                                            endif;
                                        }
                                    }
                                    ?>
                                </select>


                                <select class="form-control" name="dst_point2_sip" id="dst_point2_sip" tabindex="<?php echo $tab_index++; ?>"  <?php echo 'style="display:none;"'; ?>>
                                    <option value="">Select User</option>
                                    <?php
                                    if (count($did_enduser['sipuser']) > 0) {
                                        foreach ($did_enduser['sipuser'] as $k => $sip_data) {
                                            if ($sip_data['status'] == '1'):
                                                ?>
                                                <option value="<?php echo $sip_data['username']; ?>" ><?php echo $sip_data['username']; ?></option>	
                                                <?php
                                            endif;
                                        }
                                    }
                                    ?>
                                </select>


                                <input type="text" name="dst_point2_pstn" id="dst_point2_pstn" value="" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo 'style="display:none;"'; ?>>
                            </div>
                        </div>


                        <div class="ln_solid"></div>

                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Update Config</button>

                            </div>
                        </div>		



                    </form>
                </div>
            </div>

        </div>	

        <div class="col-md-5 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    <button type="button" id="btnDeleteBulk" class="btn btn-danger btn-lg btn-block" title="Cancel" onclick="DoBulkDeleteConfirm()">Cancel Selected DID(<small>s</small>) <i class="fa fa-trash-o"></i></button>
                    <form action="<?php echo site_url('dids'); ?>" method="post" name="cancel_form" id="cancel_form" class="form-horizontal form-label-left">
                        <input type="hidden" name="cancel_did_number" id="cancel_did_number" value="">
                        <input type="hidden" name="action" value="OkDeleteDataBulk" >                
                    </form> 
                </div>
            </div>
        </div>
    </div>


</div>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
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

                        $('#dst_type').change(function () {
                            if (this.value == 'IP') {
                                $('#dst_point_ip').show();
                                $('#dst_point_sip').hide();
                                $('#dst_point_pstn').hide();

                                $('#dst_point_ip').attr('data-parsley-required', 'true');
                                $('#dst_point_sip').attr('data-parsley-required', 'false');
                                $('#dst_point_pstn').attr('data-parsley-required', 'false');
                            } else if (this.value == 'PSTN') {
                                $('#dst_point_ip').hide();
                                $('#dst_point_sip').hide();
                                $('#dst_point_pstn').show();

                                $('#dst_point_ip').attr('data-parsley-required', 'false');
                                $('#dst_point_sip').attr('data-parsley-required', 'false');
                                $('#dst_point_pstn').attr('data-parsley-required', 'true');
                            } else {
                                $('#dst_point_ip').hide();
                                $('#dst_point_sip').show();
                                $('#dst_point_pstn').hide();

                                $('#dst_point_ip').attr('data-parsley-required', 'false');
                                $('#dst_point_sip').attr('data-parsley-required', 'true');
                                $('#dst_point_pstn').attr('data-parsley-required', 'false');
                            }
                        });


                        function dst_type_changed(dst_type)
                        {
                            if (dst_type == 'IP') {
                                $('#dst_point2_ip').show();
                                $('#dst_point2_sip').hide();
                                $('#dst_point2_pstn').hide();

                                $('#dst_point2_ip').attr('data-parsley-required', 'true');
                                $('#dst_point2_sip').attr('data-parsley-required', 'false');
                                $('#dst_point2_pstn').attr('data-parsley-required', 'false');
                            } else if (dst_type == 'PSTN') {
                                $('#dst_point2_ip').hide();
                                $('#dst_point2_sip').hide();
                                $('#dst_point2_pstn').show();

                                $('#dst_point2_ip').attr('data-parsley-required', 'false');
                                $('#dst_point2_sip').attr('data-parsley-required', 'false');
                                $('#dst_point2_pstn').attr('data-parsley-required', 'true');
                            } else {
                                $('#dst_point2_ip').hide();
                                $('#dst_point2_sip').show();
                                $('#dst_point2_pstn').hide();

                                $('#dst_point2_ip').attr('data-parsley-required', 'false');
                                $('#dst_point2_sip').attr('data-parsley-required', 'true');
                                $('#dst_point2_pstn').attr('data-parsley-required', 'false');
                            }

                        }

                        $('#dst_type2').change(function () {
                            dst_type_changed(this.value);
                        });

                        $('#btnSave, #btnSaveClose').click(function () {
                            var is_ok = $("#assign_form").parsley().isValid();
                            if (is_ok === true)
                            {
                                var clicked_button_id = this.id;

                                var lead_id_array = [];
                                $.each($("input[name='did_number_list']:checked"), function () {
                                    lead_id_array.push($(this).val());
                                });
                                $('#assign_did_number').val(lead_id_array);


                                if (clicked_button_id == 'btnSaveClose')
                                    $('#button_action').val('save_close');
                                else
                                    $('#button_action').val('save');

                                $("#assign_form").submit();
                            } else
                            {
                                $('#assign_form').parsley().validate();
                            }
                        });

</script>
<script>
    $(document).ready(function () {
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
    $(document).ready(function () {
        showDatatable('table-sort', [0, 8], [2, "desc"]);
    });
</script>