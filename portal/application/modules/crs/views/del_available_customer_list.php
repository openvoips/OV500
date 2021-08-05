<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Assign Tariff and Services</h2>
            <ul class="nav navbar-right panel_toolbox">
                <?php //if (check_account_permission('Signup/addplan', 'add')): ?>
                <li><a href="<?php echo base_url('crs/index') ?>"><input type="button" value="Back To Users Listing" name="add_link" class="btn btn-danger"></a></li>
                <?php // endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url('crs/assignvoip'); ?>">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="account_id" id="account_id" value="<?php echo $_SESSION[$search_session_key]['account_id']; ?>" class="form-control data-search-field" placeholder="Account ID">
                    </div>
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="company_name" id="company_name" value="<?php echo $_SESSION[$search_session_key]['company_name']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>
                </div>
                <div class="form-group">
                                 <label class="control-label col-md-2 col-sm-3 col-xs-12">Account Type</label>
                                        <div class="col-md-4 col-sm-8 col-xs-12">
                    
                                            <select name="account_type" id="account_type"  class="form-control" >
                                                <option value="">Select Account Type</option>                    
                    <?php
                            $str = '';
                                $account_type = array('CUSTOMER' => 'CUSTOMER', 'RESELLER' => 'RESELLER');
                            foreach ($account_type as $key => $value) {

                                $selected = '';
                                if ($_SESSION[$search_session_key]['account_type'] == $value)
                                    $selected = 'selected';
                                $str .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                            }
                            echo $str;
                    ?>
                                            </select>
                                        </div>
                    <div class="searchBar text-right ">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary"> 
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">        
                    </div>
                </div>
            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>           
            <div class="row">  
                <?php dispay_pagination_row($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination); ?>
            </div>

            <div class="table-responsive">
                <table class="table table-striped jambo_table table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings ">                  
                            <th class="column-title">Name/Account ID</th>
                            <th class="column-title">Account Type</th>
                            <th class="column-title no-link last"><span class="nobr">Actions</span> </th>

                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if ($voipacnt_data['result'] > 0) {
                            foreach ($voipacnt_data['result'] as $listing_row) {
                                ?>
                                <tr>                                   
                                    <?php
                                    if (!empty($listing_row['company_name'])) {
                                        $acc = $listing_row['company_name'] . ' (' . $listing_row['account_id'] . ')';
                                    } else {
                                        $acc = $listing_row['name'] . ' (' . $listing_row['account_id'] . ')';
                                    }
                                    ?>

                                    <td ><?php echo $acc; ?></td>
                                    <?php
                                    $account_type = '';
                                    if ($listing_row['account_type'] === 'CUSTOMER')
                                        $account_type = '<span class="label label-primary">' . $listing_row['account_type'] . '</span>';
                                    else
                                        $account_type = '<span class="label label-warning">' . $listing_row['account_type'] . '</span>';
                                    ?>
                                    <td><?php echo $account_type; ?></td>
                                    <td class=" last" >

                                        <a href="<?php echo site_url('crs/addvoip/'.param_encrypt($listing_row['account_id'])); ?>" title="Edit" class="edit"><i class="fa fa-plus"></i></a>
                                        <!--.'/'.param_encrypt($listing_row['account_type'])-->


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
                <?php dispay_pagination_row_bottom($total_records, $_SESSION[$search_session_key]['no_of_records'], $pagination); ?>
            </div> 
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
	showDatatable('table-sort', [5], [1, "asc"]);
	/*$('#OkFilter').click(function() {
		var no_of_records = $('#no_of_records').val();
		$('#no_of_rows').val(no_of_records);
	});*/
});
</script>
