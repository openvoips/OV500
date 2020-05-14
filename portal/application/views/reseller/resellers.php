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
$status_name_array = array(
    '1' => array('name' => 'Active', 'class' => 'label-success'),
    '0' => array('name' => 'Closed', 'class' => 'label-danger'),
    '-2' => array('name' => 'Temporarily Suspended', 'class' => 'label-primary'),
    '-3' => array('name' => 'Suspected Blocked', 'class' => 'label-info')
);
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2><?php echo ucfirst($reseller_type) . 's Users'; ?></h2>
            <ul class="nav navbar-right panel_toolbox">                
                <?php if (check_account_permission('reseller', 'add')): ?><li><a href="<?php echo base_url($reseller_type . 's') ?>/add"><input type="button" value="Add <?php echo ucfirst($reseller_type); ?>" name="add_link" class="btn btn-primary"></a></li><?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url($reseller_type . 's'); ?>">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12"><?php echo ucfirst($reseller_type); ?> Name</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="name" id="name" value="<?php echo $_SESSION['search_resellers_data']['s_name']; ?>" class="form-control data-search-field" placeholder="<?php echo ucfirst($reseller_type); ?> Name">
                    </div>
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account Code</label>
                    <div class="col-md-3 col-sm-8 col-xs-12">
                        <input type="text"  name="account_id" id="account_id"  value="<?php echo $_SESSION['search_resellers_data']['s_account_id']; ?>" class="form-control data-search-field" placeholder="Account Code">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-6 col-xs-12">Status</label>
                    <div class="col-md-3 col-sm-8 col-xs-12">
                        <select name="status" id="status" class="form-control data-search-field">
                            <option value="">Select</option>
                            <option value="1" <?php if ($_SESSION['search_resellers_data']['s_status'] == '1') echo 'selected="selected"'; ?> >Active</option>
                            <option value="0" <?php if ($_SESSION['search_resellers_data']['s_status'] == '0') echo 'selected="selected"'; ?>>Closed</option>                          
                            <option value="-2" <?php if ($_SESSION['search_resellers_data']['s_status'] == '-2') echo 'selected="selected"'; ?>>Temporarily Suspended</option>
                            <option value="-3" <?php if ($_SESSION['search_resellers_data']['s_status'] == '-3') echo 'selected="selected"'; ?>>Suspected Blocked</option>
                        </select>
                    </div>
                    <div class="searchBar "> 
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                           
                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url($reseller_type . 's') . '/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
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
                dispay_pagination_row($total_records, $_SESSION['search_resellers_data']['s_no_of_records'], $pagination);
                ?>    
            </div>

            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort1">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title">Account ID </th>
                            <th class="column-title">Name </th>
                            <th class="column-title">Tariff-plan</th>
                            <th class="column-title">Currency</th>
                            <th class="column-title">Balance </th>
                            <th class="column-title">Credit</th> 
                            <th class="column-title">Billing Type</th>  
                            <th class="column-title">CC/CPS</th>                         
                            <th class="column-title">Status </th>
                            <th class="column-title no-link last" width="160"><span class="nobr">Actions</span> </th>
                            <th class="bulk-actions" colspan="7">
                                <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) </a>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (count($data['result']) > 0) {
                            foreach ($data['result'] as $account_data) {
                                $status_name = '';
                                if ($account_data['account_status'] == '1')
                                    $status = '<span class="label label-success">Active</span>';
                                else if ($account_data['account_status'] == '0')
                                    $status = '<span class="label label-danger">Closed</span>';
                                else if ($account_data['account_status'] == '-2')
                                    $status = '<span class="label label-info ">Temporarily Suspended</span>';
                                else if ($account_data['account_status'] == '-3')
                                    $status = '<span class="label label-danger">Blocked(Suspected Activity)</span>';
                                else
                                    $status = '<span class="label label-danger">Issue In Account</span>';

                                $account_status = $account_data['account_status'];

                                //  echo "............... $account_status....................";
                                if (isset($status_name_array[$account_status])) {
                                    $status_name = $status_name_array[$account_status]['name'];

                                    //  echo "lllllllllllllllll $status_name llllllllllllll";
                                    $class = $status_name_array[$account_status]['class'];
                                } else {
                                    $status_name = '';
                                    $class = '';
                                }

                                $tariff_name = isset($account_data['tariff']['tariff_name']) ? $account_data['tariff']['tariff_name'] : '';

                                $balance_display = '';
                                if (isset($account_data['balance']['balance'])) {
                                    $credit_limit = number_format($account_data['balance']['credit_limit'], 2, '.', '');
                                    $usable_balance = number_format(-$account_data['balance']['balance'], 2, '.', '');
                                    $balance = number_format($account_data['balance']['balance'], 2, '.', '');
                                    if (isset($account_data['currency']['symbol'])) {
                                        $balance = $balance . '(' . $account_data['currency']['symbol'] . ')';
                                    }
                                    $balance_display = $usable_balance . ' + ' . $credit_limit . ' = ' . $balance;
                                }
                                ?>
                                <tr>                                    
                                    <td><?php echo $account_data['account_id']; ?></td>
                                    <td><?php echo $account_data['name']; ?></td>
                                    <td><?php echo $tariff_name; ?></td>
                                    <td><?php echo $account_data['currency']['symbol'] . " - " . $account_data['currency']['name']; ?></td>
                                    <td><nobr><?php echo $usable_balance; ?></nobr></td>
                            <td><nobr><?php echo $credit_limit; ?></nobr></td>
                            <td class="text-right"><nobr><?php echo $account_data['billing_type']; ?></nobr></td>
                            <td><?php echo $account_data['account_cc'] . "/ " . $account_data['account_cps']; ?></td>
                            <td><?php echo $status; ?></td>                                   
                            <td class=" last">

                                <?php if (check_account_permission('reseller', 'view')): ?>
                                    <a href="<?php echo base_url($reseller_type . 's'); ?>/edit/<?php echo param_encrypt($account_data['account_id']); ?>" title="Edit"><i class="fa fa-pencil-square-o"></i></a>
                                    <a href="<?php echo base_url(); ?>payment/index/<?php echo param_encrypt($account_data['account_id']); ?>" title="Payment"><i class="fa fa-money"></i></a>
                                <?php endif; ?>
                                <?php
                                if (check_account_permission('reseller', 'delete') && $this->session->userdata('session_current_account_id') != $account_data['id']):
                                    ?>                                  
                                   <a href="<?php echo base_url(); ?>resellers/statement/<?php echo param_encrypt($account_data['account_id']); ?>" title="Account Statement"><i class="fa fa-hand-o-right"></i></a>
                                    <a href="<?php echo base_url(); ?>resellers/rState/<?php echo param_encrypt($account_data['account_id']); ?>" title="Call Analysis"><i class="fa fa-bar-chart-o"></i></a>

                                    <?php
                                    if (check_account_permission('reseller', 'delete') && $this->session->userdata('session_current_account_id') != $account_data['id'] && strtolower($status_name) == 'closed'):
                                        ?>
                                        <a href="javascript:void(0);" onclick=doConfirmDelete('<?php echo $account_data['account_id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>                                        <?php endif; ?> 
                                <?php endif; ?> 
                                <?php
                                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'RESELLER'))):
                                    ?>                                    
                                    <a href="<?php echo base_url(); ?>autologin/<?php echo param_encrypt($account_data['account_id']); ?>" title="Login As User"><i class="fa fa-sign-in"></i></a>
                                        <?php
                                    endif;
                                    ?>

                            </td>
                            </tr>

                            <?php
                        }
                    }
                    else {
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
            echo '<div class="btn-toolbar" role="toolbar">  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">  ' . $pagination . '</div> </div>';
            ?>

        </div>
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
        //showDatatable('table-sort', [ 0,6], [ 2,"asc" ] );
        showDatatable('table-sort1', [6], [1, "asc"]);
    });
</script>         