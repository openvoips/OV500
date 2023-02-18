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
                <?php
                    if (!check_logged_user_group(array('CUSTOMER'))):
                        ?>
                	 <label class="control-label col-md-1 col-sm-3 col-xs-12">Carrier</label>
                	 <div class="col-md-3 col-sm-3 col-xs-4">
                     <select name="carrier_id" id="carrier_id" class="form-control data-search-field combobox1">
                          <option value="">Select</option>
                           <?php
							$str = '';
							if (isset($carriers_data['result']) && count($carriers_data['result']) > 0) {
								foreach ($carriers_data['result'] as $key => $carrier_array) {
									$carrier_currency_id = $carrier_array['carrier_currency_id'];
									$currency_name = $currency_array[$carrier_currency_id];

									$selected = ' ';
									if ($_SESSION['search_did_data']['s_carrier_id'] == $carrier_array['carrier_id'])
										$selected = '  selected="selected" ';
									$str .= '<option value="' . $carrier_array['carrier_id'] . '" ' . $selected . ' >' . $carrier_array['carrier_name'] . ' [' . $carrier_array['carrier_id'] . ']</option>';
								}
							}
							echo $str;
							?>
                      </select>          
                     </div>
               <?php endif; ?>       

                    <div class="searchBar text-right">
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
                                echo '<a href="' . base_url() . 'dids/config/' . param_encrypt($did_data['did_id']) . '" title="Edit"><i class="fa fa-pencil-square-o"></i></a>';
                            } else
                                echo '<a href="' . base_url() . 'dids/edit/' . param_encrypt($did_data['did_id']) . '" title="Edit"><i class="fa fa-pencil-square-o"></i></a>';

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
</div>
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
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
	    $(document).ready(function () {
        $('.combobox').combobox()
    });
</script>