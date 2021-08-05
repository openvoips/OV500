<?php
$delete_option = '';
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Bundle & Package Management</h2>
            <ul class="nav navbar-right panel_toolbox">

                <?php if (check_account_permission('bundle', 'add')): ?>
                    <li><a href="<?php echo base_url() ?>bundle/addBP"><input type="button" value="Add Bundle & Package" name="add_link" class="btn btn-primary"></a></li>
                <?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>bundle/index/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Bundle & Package Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="name" id="name" value="<?php echo $_SESSION['search_bundle_data']['s_bundle_package_name']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Bundle Code</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="bundle_package_id" id="bundle_package_id" value="<?php echo $_SESSION['search_bundle_data']['s_bundle_package_id']; ?>" class="form-control data-search-field" placeholder="Bundle Code">
                    </div>
                </div>
                <div class="form-group">
                    <?php if (!check_logged_user_group(array('RESELLER'))) : ?>
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Currency</label>
                        <div class="col-md-4 col-sm-8 col-xs-12">
                            <select name="currency" id="currency" class="form-control data-search-field">
                                <option value="">Select Currency</option>
                                <?php for ($i = 0; $i < count($currency_data); $i++) { ?>								

                                    <?php if (get_logged_account_level() == 0): ?>	
                                        <option value="<?php echo $currency_data[$i]['currency_id']; ?>" 
                                                <?php if ($_SESSION['search_bundle_data']['s_bundle_package_currency'] == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name'] . ' (' . $currency_data[$i]['symbol'] . ')'; ?></option>
                                            <?php elseif (get_logged_account_level() != 0 && get_logged_account_currency() == $currency_data[$i]['currency_id']): ?>
                                        <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if ($_SESSION['search_bundle_data']['s_bundle_package_currency'] == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name'] . ' (' . $currency_data[$i]['symbol'] . ')'; ?></option>
                                    <?php endif; ?>

                                <?php } ?>
                            </select>
                        </div>		

                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                        <div class="col-md-4 col-sm-8 col-xs-12">
                            <select name="status" id="status" class="form-control data-search-field">
                                <option value="">ALL</option>
                                <option value="1" <?php if ($_SESSION['search_bundle_data']['s_bundle_package_status'] == '1') echo 'selected'; ?>>Active</option>
                                <option value="0" <?php if ($_SESSION['search_bundle_data']['s_bundle_package_status'] == '0') echo 'selected'; ?>>Inactive</option>
                            </select>
                        </div>			
                    <?php endif; ?>
                </div>
                <div class="form-group">


                    <div class="searchBar text-right">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                           

                    </div>
                </div>
            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>         
            <div class="row">  
                <?php
                dispay_pagination_row($total_records, $_SESSION['search_bundle_data']['s_no_of_records'], $pagination);
                ?>    
            </div>
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">                          
                            <th class="column-title">Bundle</th>
                            <th class="column-title">Monthly Charge</th>

                            <th class="column-title">Bundle1 Type</th> 
                            <th class="column-title">Bundle1 Value</th> 
                            <th class="column-title">Bundle2 Type</th> 
                            <th class="column-title">Bundle2 Value</th> 
                            <th class="column-title">Bundle3 Type</th> 
                            <th class="column-title">Bundle3 Value</th> 

                            <th class="column-title">Currency</th>
                            <th class="column-title">Package Status </th>
                            <th class="column-title">Bundle Status</th>  
                            <th class="column-title no-link last"><span class="nobr">Actions</span> </th>

                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if ($listing_count > 0) {
                            foreach ($listing_data as $listing_row) {

                                if ($listing_row['bundle_package_status'] == '1')
                                    $status = '<span class="label label-success">Active</span>';
                                else
                                    $status = '<span class="label label-danger">Inactive</span>';


                                if ($listing_row['bundle_option'] == '1')
                                    $bundle_option = '<span class="label label-success">Active</span>';
                                else
                                    $bundle_option = '<span class="label label-danger">Inactive</span>';

                                if ($listing_row['user_count'] == 0)
                                    $delete_option = true;
                                else
                                    $delete_option = false;
                                ?>
                                <tr>                                    
                                    <td><?php echo $listing_row['bundle_package_name'] . " (" . $listing_row['bundle_package_id'] . ")"; ?></td>

                                    <td ><?php echo number_format($listing_row['monthly_charges'], 2); ?></td>



                                    <td ><?php echo $listing_row['bundle1_type']; ?></td>
                                    <td ><?php echo empty($listing_row['bundle1_value']) ? '0.00' : number_format(round($listing_row['bundle1_value'], 2), 2); ?></td>    
                                    <td ><?php echo $listing_row['bundle2_type']; ?></td>
                                    <td ><?php echo empty($listing_row['bundle2_value']) ? '0.00' : number_format(round($listing_row['bundle2_value'], 2), 2); ?></td>
                                    <td ><?php echo $listing_row['bundle3_type']; ?></td>
                                    <td ><?php echo empty($listing_row['bundle3_value']) ? '0.00' : number_format(round($listing_row['bundle3_value'], 2), 2); ?></td>    

                                    <td ><?php echo $listing_row['currency_name'] . " (" . $listing_row['currency_symbol'] . ")"; ?></td>

                                    <td ><?php echo $status; ?></td>   
                                    <td ><?php echo $bundle_option; ?></td>
                                    <td class="last" >
                                        <?php if (check_account_permission('bundle', 'edit')): ?>
                                            <a href="<?php echo base_url(); ?>bundle/editBP/<?php echo param_encrypt($listing_row['bundle_package_id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                        <?php endif; ?>	
                                        <?php //if(check_account_permission('tariff','delete')):    ?>
                                        <?php if ($delete_option) { ?>
                                            <a href="javascript:doConfirmDelete('<?php echo param_encrypt($listing_row['bundle_package_id']); ?>','bundle');" title="Delete" class="delete"><i class="fa fa-trash"></i></a>						
                                        <?php } else { ?>
                                            <a href="javascript:void(0);" onclick="new PNotify({
                                                        title: 'Data deletion',
                                                        text: 'You can not delete bundle as it is already used.',
                                                        type: 'info',
                                                        styling: 'bootstrap3',
                                                        addclass: 'dark'
                                                    });" title="Delete" class="text-dark"><i class="fa fa-trash"></i><a/>
                                            <?php } ?>
                                            <?php //endif;  ?>	

                                    </td>
                                </tr>

                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="27" align="center"><strong>No Record Found</strong></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>                    
            <?php echo '<div class="btn-toolbar" role="toolbar">
				  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
						   ' . $pagination . '
				  </div>
				</div>'; ?>

            <?php
            $attributes = array('name' => 'view_frm', 'id' => 'view_frm');
            echo form_open('rates/index/', $attributes);
            echo form_input(array('name' => 'search_action', 'type' => 'hidden', 'id' => 'search_action', 'value' => 'search'));
            echo form_input(array('name' => 'card', 'type' => 'hidden', 'id' => 'card'));
            echo form_input(array('name' => 'bundle', 'type' => 'hidden', 'id' => 'bundle'));
            echo form_input(array('name' => 'prefix', 'type' => 'hidden', 'id' => 'prefix'));
            echo form_input(array('name' => 'dest', 'type' => 'hidden', 'id' => 'dest'));
            echo form_input(array('name' => 'OkFilter', 'type' => 'hidden', 'id' => 'OkFilter', 'value' => 'Search'));
            echo form_close();
            ?>

        </div>
    </div>
</div>
<script language="javascript" type="text/javascript">
    $(document).ready(function () {
        showDatatable('table-sort', [6], [1, "asc"]);
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
    $('.rates').click(function () {
        $('#view_frm #bundle').val($(this).data('id'));
        $('#view_frm').submit();
    });
</script>
