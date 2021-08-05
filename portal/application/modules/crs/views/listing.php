<style>
    .panel_toolbox>li>a:hover {
        background: #425567 !important;
    }
    .panel_toolbox>li>a {
        padding: 5px;
        color: #fff;
        font-size: 14px;
    }
</style>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Customer / Reseller Services Management</h2>
            <ul class="nav navbar-right panel_toolbox">
                <?php if (check_account_permission('customer', 'add')): ?>    
                    <li>  
                        <a class="btn btn-primary" href="<?php echo site_url('crs/customers/add'); ?>">
                            <i class="fa fa-plus"></i> Customer
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (check_account_permission('reseller', 'add')): ?>
                    <li>
                        <a class="btn btn-primary" href="<?php echo site_url('crs/resellers') ?>/add">
                            <i class="fa fa-plus" ></i> Reseller</a>
                    </li>
                <?php endif; ?>
              
                <?php // endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url('crs/index'); ?>">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />

                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text"  name="account_id" id="account_id" data-parsley-required="" value="<?php echo $_SESSION[$search_session_key]['account_id']; ?>" class="form-control data-search-field" placeholder="Account ID">
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

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Web-User</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="web_username" id="web_username" value="<?php echo $_SESSION[$search_session_key]['web_username']; ?>" class="form-control data-search-field" placeholder="web User">
                    </div>

                </div> 
                <div class="form-group">   
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">SIP-User</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="sip_user" id="web_username" value="<?php echo $_SESSION[$search_session_key]['sip_user']; ?>" class="form-control data-search-field" placeholder="sip user">
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
                        <tr class="headings thc">               
                            <th class="column-title">Account ID</th>
                            <th class="column-title">Account Type</th>
                            <th class="column-title">Tariff</th>
                            <th class="column-title">Balance</th>                             
                            <th class="column-title">Credit</th>                             
                            <th class="column-title">Usable Balance</th>
                            <th class="column-title">Currency</th>
                            <th class="column-title">Web-Users</th>
                            <th class="column-title">Status</th>
                            <th class="column-title no-link last"><span class="nobr">Actions</span> </th>

                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if ($voip_data['result'] > 0) {
                            foreach ($voip_data['result'] as $listing_row) {
                                ?>
                                <tr> <?php
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

                                    <?php if (strlen($listing_row['tariff_id']) == 0) { ?>
                                        <td> - </td>
                                    <?php } else { ?>

                                        <td><?php echo $listing_row['tariff_name'] . ' (' . $listing_row['tariff_id'] . ')'; ?></td>
                                    <?php } ?>
                                    <td><?php echo ( number_format(-1 * $listing_row['balance'], $listing_row['dp'], '.', '')); ?></td>
                                    <td><?php echo (number_format($listing_row['credit_limit'], $listing_row['dp'], '.', '')); ?></td>
                                    <td><?php echo (number_format(number_format($listing_row['credit_limit'], $listing_row['dp'], '.', '') + ( number_format(-1 * $listing_row['balance'], $listing_row['dp'], '.', '')), $listing_row['dp'], '.', '')); ?></td>  

                                    <td><?php echo $listing_row['currency_name'] . ' (' . $listing_row['symbol'] . ')'; ?></td>
                                    <td><?php echo $listing_row['web_username']; ?></td>  
                                    <?php
                                    $status = '';
                                    if ($listing_row['status'] === '1')
                                        $status = '<span class="label label-success">Active</span>';
                                    else
                                        $status = '<span class="label label-danger">Inactive</span>';
                                    ?>
                                    <td><?php echo $status; ?></td>
                                    <td class=" last" >
                                        <?php if ($listing_row['account_type'] === 'CUSTOMER') { ?>
                                            <a href="<?php echo base_url(); ?>crs/customers/edit/<?php echo param_encrypt($listing_row['account_id']); ?>" title="Edit" class="edit" id="cedit"><i class="fa fa-bars" aria-hidden="true"></i>
                                            </a>
                                        <?php } else { ?>
                                            <a href="<?php echo base_url(); ?>crs/resellers/edit/<?php echo param_encrypt($listing_row['account_id']); ?>" title="Edit" class="edit"><i class="fa fa-bars" aria-hidden="true"></i>


                                            </a>
                                        <?php } ?>



                                        <a href="<?php echo base_url(); ?>crs/editvoip/<?php echo param_encrypt($listing_row['account_id']); ?>" title="Services Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>



                                        <?php
                                        if (check_logged_user_type(array('ADMIN', 'SUBADMIN', 'RESELLERADMIN', 'ACCOUNT'))):
                                            ?>  
                                            <a href="<?php echo site_url(); ?>crs/payment/index/<?php echo param_encrypt($listing_row['account_id']); ?>" title="Payment"><i class="fa fa-money"></i></a>
                                              
                                            
                                            
                                        <?php endif; ?>	
                                        <?php if (check_logged_user_type(array('ACCOUNT'))): ?>
                                            <a href="<?php echo site_url(); ?>reports/call_report/<?php echo param_encrypt($listing_row['account_id']); ?>" title="Call Report"><i class="fa fa-phone"></i></a>
                                            <a href="<?php echo site_url(); ?>reports/cdr/<?php echo param_encrypt($listing_row['account_id']); ?>" title="CDR" target="_blank">CDR</a>
                                        <?php endif; ?>



                                        <?php if ($listing_row['account_type'] === 'CUSTOMER') { ?>     
                                            <a href="<?php echo site_url(); ?>crs/customers/statement/<?php echo param_encrypt($listing_row['account_id']); ?>" title="Account Statement"><i class="fa fa-hand-o-right"></i></a>                                         
                                           
                                            <?php if (check_account_permission('customer', 'login')) {
                                                ?>                                       
                                                <a href="<?php echo site_url(); ?>cuautologin/<?php echo param_encrypt($listing_row['account_id']); ?>" title="Login As User"><i class="fa fa-sign-in"></i></a>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <a href="<?php echo site_url(); ?>crs/resellers/statement/<?php echo param_encrypt($listing_row['account_id']); ?>" title="Account Statement"><i class="fa fa-hand-o-right"></i></a>
                                           
                                            <a href="<?php echo site_url(); ?>reautologin/<?php echo param_encrypt($listing_row['account_id']); ?>" title="Login As User"><i class="fa fa-sign-in"></i></a>
                                        <?php };	
										if (check_logged_user_type(array('ADMIN', 'SUBADMIN', 'RESELLERADMIN')) && $listing_row['account_type'] == 'CUSTOMER')
										{									
                                        ?>	
                                         <a href="javascript:void(0);"  onclick=doConfirmDelete('<?php echo $listing_row['account_id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>
                                        <?php }?>
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
</div>
<script>
    $(document).ready(function () {
        showDatatable('table-sort', [5], [1, "asc"]);
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });


</script>
