<?php
$status_name_array = array(
    '1' => array('name' => 'Active', 'class' => 'label-success'),
    '0' => array('name' => 'Closed', 'class' => 'label-danger'),
    '-2' => array('name' => 'Temporarily Suspended', 'class' => 'label-primary'),
    '-3' => array('name' => 'Suspected Blocked', 'class' => 'labe-linfo')
);
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Customers Up</h2>
            <ul class="nav navbar-right panel_toolbox">
                <?php if (check_account_permission('customer', 'add')): ?>    <li><a href="<?php echo site_url('crs/customers/add'); ?>"><input type="button" value="Add Customer" name="add_link" class="btn btn-primary"></a></li><?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url(); ?>crs/customers/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Company Name</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="company_name" id="company_name" value="<?php echo $_SESSION[$search_session_key]['company_name']; ?>" class="form-control data-search-field" placeholder="Company Name">
                    </div>
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                    <div class="col-md-3 col-sm-8 col-xs-12">
                        <input type="text"  name="account_id" id="account_id"  value="<?php echo $_SESSION[$search_session_key]['account_id']; ?>" class="form-control data-search-field" placeholder="Account ID">
                    </div>
                </div>





                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-6 col-xs-12">SIP User</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text"  name="sip_username" id="sip_username" value="<?php echo $_SESSION[$search_session_key]['sip_username']; ?>" class="form-control data-search-field" placeholder="SIP User">
                    </div> 

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">IP address</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text"  name="ipaddress" id="ipaddress" value="<?php echo $_SESSION[$search_session_key]['ipaddress']; ?>" class="form-control data-search-field" placeholder="IP address">
                    </div> 
                </div>

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-6 col-xs-12">Status</label>
                    <div class="col-md-3 col-sm-8 col-xs-12">
                        <select name="status_id" id="status_id" class="form-control data-search-field">
                            <option value="">Select</option>
                            <option value="1" <?php if ($_SESSION[$search_session_key]['status_id'] == '1') echo 'selected="selected"'; ?> >Active</option>
                            <option value="0" <?php if ($_SESSION[$search_session_key]['status_id'] == '0') echo 'selected="selected"'; ?>>Closed</option>                          
                            <option value="-2" <?php if ($_SESSION[$search_session_key]['status_id'] == '-2') echo 'selected="selected"'; ?>>Temporarily Suspended</option>
                            <option value="-3" <?php if ($_SESSION[$search_session_key]['status_id'] == '-3') echo 'selected="selected"'; ?>>Suspected Blocked</option>
                        </select>
                    </div>
                    <div class="searchBar text-right "> 
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                           
                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . site_url('crs/customers/index/export/' . param_encrypt($export_format)) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>                            
                            </ul>
                        </div>
                    </div>


                </div>


            </form> 


            <div class="clearfix"></div>
            <div class="ln_solid"></div>
            <div class="row">  
                <?php
                dispay_pagination_row($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination);
                ?>    
            </div>       
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title">Account </th>
                            <th class="column-title">Balance</th>
                            <th class="column-title">Credit</th> 
                            <th class="column-title">CC/CPS</th>   
                            <th class="column-title">Currency</th>
                            <th class="column-title" >Status </th>
                            <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>
                            <th class="bulk-actions" colspan="7">
                                <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (count($data['result']) > 0) {
                            $account_status = '';
                            foreach ($data['result'] as $account_data) {
                                $account_status = $account_data['status_id'];
                                if (isset($status_name_array[$account_status])) {
                                    $status_name = $status_name_array[$account_status]['name'];
                                    $class = $status_name_array[$account_status]['class'];
                                } else {
                                    $status_name = '';
                                    $class = '';
                                }
                                $status = '<span class="label ' . $class . '">' . $status_name . '</span>';

                                $balance_display = '';
                                if (isset($account_data['balance']['balance'])) {
                                    $credit_limit = number_format($account_data['balance']['credit_limit'], 2, '.', '');
                                    $outstanding_balance = number_format(-$account_data['balance']['balance'], 2, '.', '');
                                    $balance = number_format($account_data['balance']['balance'], 2, '.', '');

                                    if (isset($account_data['currency']['symbol'])) {
                                        $balance = $balance . " " . $account_data['currency']['name'];
                                    }
                                    $balance_display = $outstanding_balance . ' + ' . $credit_limit . ' = ' . $balance;
                                }
                                ?>
                                <tr >

                                    <td><?php echo $account_data['company_name'] . " (" . $account_data['account_id'] . ")"; ?></td>


                                    <td><nobr><?php echo number_format($outstanding_balance, $account_data['dp'], '.', ''); ?></nobr></td>
                            <td><nobr><?php echo number_format($credit_limit, $account_data['dp'], '.', ''); ?></nobr></td>
                            <td><?php echo $account_data['account_cc'] . "/ " . $account_data['account_cps']; ?></td>
                            <td><?php echo $account_data['currency']['name'] . " (" . $account_data['currency']['symbol'] . ")"; ?></td>
                            <td><?php echo $status; ?></td>                                   
                            <td class=" last"><nobr>
                                <?php if (check_account_permission('customer', 'view')): ?>
                                    <a href="<?php echo site_url(); ?>crs/customers/edit/<?php echo param_encrypt($account_data['account_id']); ?>" title="Edit"><i class="fa fa-pencil-square-o"></i></a>

                                    <?php
                                endif;
                                ?>	
                                <?php
                                if (check_logged_user_type(array('ADMIN', 'SUBADMIN', 'RESELLERADMIN', 'ACCOUNT'))):
                                    ?>  
                                    <a href="<?php echo site_url(); ?>payment/index/<?php echo param_encrypt($account_data['account_id']); ?>" title="Payment"><i class="fa fa-money"></i></a>
                                <?php endif; ?>	
                                <?php if (check_logged_user_type(array('ACCOUNT'))): ?>
                                    <a href="<?php echo site_url(); ?>reports/call_report/<?php echo param_encrypt($account_data['account_id']); ?>" title="Call Report"><i class="fa fa-phone"></i></a>
                                    <a href="<?php echo site_url(); ?>reports/cdr/<?php echo param_encrypt($account_data['account_id']); ?>" title="CDR" target="_blank">CDR</a>
                                <?php endif; ?>

                                <a href="<?php echo site_url(); ?>crs/customers/statement/<?php echo param_encrypt($account_data['account_id']); ?>" title="Account Statement"><i class="fa fa-hand-o-right"></i></a>                                         
                                <a href="<?php echo site_url(); ?>crs/customers/cState/<?php echo param_encrypt($account_data['account_id']); ?>" title="Call Analysis"><i class="fa fa-bar-chart-o"></i></a>
                                <?php
                                if (check_account_permission('customer', 'delete') && $this->session->userdata('session_current_account_id') != $account_data['id'] && strtolower($status_name) == 'closed'):
                                    ?>
                                    <a href="javascript:void(0);"  onclick=doConfirmDelete('<?php echo $account_data['account_id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>
                                <?php endif; ?> 

                                <?php
                                if (check_account_permission('customer', 'login')) {
                                    ?>                                       
                                    <a href="<?php echo site_url(); ?>cuautologin/<?php echo param_encrypt($account_data['account_id']); ?>" title="Login As User"><i class="fa fa-sign-in"></i></a>
                                <?php }; ?>	
                            </nobr>
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
            <div class="row">  
                <?php dispay_pagination_row_bottom($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination); ?>
            </div> 

        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        showDatatable('table-sort', [8], [1, "asc"]);
    });
</script>  