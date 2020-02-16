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
//echo '<pre>';
//print_r($data);
//echo '</pre>';
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
            <h2>Customers</h2>
            <ul class="nav navbar-right panel_toolbox">

                <?php if (check_account_permission('customer', 'add')): ?>    <li><a href="<?php echo base_url() ?>customers/add"><input type="button" value="Add Customers" name="add_link" class="btn btn-primary"></a></li><?php endif; ?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>customers/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-6 col-xs-12">Company Name</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text" name="company_name" id="company_name" value="<?php echo $_SESSION['search_customers_data']['s_company_name']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>                  

                    <label class="control-label col-md-2 col-sm-6 col-xs-12">Account ID</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text"  name="account_id" id="account_id"  value="<?php echo $_SESSION['search_customers_data']['s_account_id']; ?>" class="form-control data-search-field" placeholder="Account ID">
                    </div>
                </div>


                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-6 col-xs-12">Login User name</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text"  name="username" id="username"  value="<?php echo $_SESSION['search_customers_data']['s_username']; ?>" class="form-control data-search-field" placeholder="Login User name">
                    </div>

                    <label class="control-label col-md-2 col-sm-6 col-xs-12">SIP User</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text"  name="sip_username" id="sip_username" value="<?php echo $_SESSION['search_customers_data']['s_sip_username']; ?>" class="form-control data-search-field" placeholder="SIP User">
                    </div> 


                </div>


                <div class="form-group">                      	

                    <label class="control-label col-md-2 col-sm-6 col-xs-12">Name</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text" name="name" id="name" value="<?php echo $_SESSION['search_customers_data']['s_name']; ?>" class="form-control data-search-field" placeholder="Name">
                    </div>    

                    <label class="control-label col-md-2 col-sm-6 col-xs-12">Status</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <select name="status" id="status" class="form-control data-search-field">
                            <option value="">Select</option>
                            <option value="1" <?php if ($_SESSION['search_customers_data']['s_status'] == '1') echo 'selected="selected"'; ?> >Active</option>
                            <option value="0" <?php if ($_SESSION['search_customers_data']['s_status'] == '0') echo 'selected="selected"'; ?>>Closed</option>                          
                            <option value="-2" <?php if ($_SESSION['search_customers_data']['s_status'] == '-2') echo 'selected="selected"'; ?>>Temporarily Suspended</option>
                            <option value="-3" <?php if ($_SESSION['search_customers_data']['s_status'] == '-3') echo 'selected="selected"'; ?>>Suspected Blocked</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">            
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">IP address</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text"  name="ipaddress" id="ipaddress" value="<?php echo $_SESSION['search_customers_data']['s_ipaddress']; ?>" class="form-control data-search-field" placeholder="IP address">
                    </div> 
                    <div class="searchBar col-md-6 col-sm-6 col-xs-12">      
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                     
                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url() . 'customers/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
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
                dispay_pagination_row($total_records, $_SESSION['search_customers_data']['s_no_of_records'], $pagination);
                ?>    
            </div>       
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title">Account ID </th>
                            <?php if (check_logged_account_type(array('SALESMANAGER'))): ?>
                                <th class="column-title">Account Manager </th>
                            <?php endif; ?>
                            <th class="column-title">Company </th>
                            <th class="column-title">Tariff-plan</th>
                            <th class="column-title">Currency</th>
                            <th class="column-title">Balance</th>
                            <th class="column-title">Credit</th> 
                            <th class="column-title">Billing Type</th>  
                            <th class="column-title">CC/CPS</th>   
                        <!--<th class="column-title">Open Routes & Package</th>-->
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
                                //    var_dump($account_data);
                                //   echo "--------------------";
                                //    var_dump($status_name_array);
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
                                $status = '<span class="label ' . $class . '">' . $status_name . '</span>';
                                //display account type only for direct customers
                                if (get_logged_account_level() == 0) {
                                    if ($account_data['account_type'] == 'REAL') { //$status .=' (LIVE)';
                                    } elseif ($account_data['account_type'] == 'DEMO') {
                                        $status .= ' <span class="label label-default" style="margin-top: 5px;">' . $account_data['account_type'] . '</span>';
                                    }
                                }

                                $tariff_name = isset($account_data['tariff']['tariff_name']) ? $account_data['tariff']['tariff_name'] : '';
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
                                    <td><?php echo $account_data['account_id']; ?></td>                                   
                                    <td><?php echo $account_data['company_name']; ?></td>
                                    <td><?php echo $tariff_name; ?></td>
                                    <td><?php echo $account_data['currency']['symbol'] . " - " . $account_data['currency']['name']; ?></td>
                                    <td><nobr><?php echo $outstanding_balance; ?></nobr></td>
                            <td><nobr><?php echo $credit_limit; ?></nobr></td>

                            <td class="text-right"><nobr><?php echo $account_data['billing_type']; ?></nobr></td>
                            <td><?php echo $account_data['account_cc'] . "/ " . $account_data['account_cps']; ?></td>
                            <td><?php echo $status; ?></td>                                   
                            <td class=" last"><nobr>
                                <?php if (check_account_permission('customer', 'view')): ?>
                                    <a href="<?php echo base_url(); ?>customers/edit/<?php echo param_encrypt($account_data['account_id']); ?>" title="Edit"><i class="fa fa-pencil-square-o"></i></a>

                                    <?php
                                endif;
                                ?>	
                                <?php
                                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'RESELLER', 'ACCOUNTS'))):
                                    ?>  
                                    <a href="<?php echo base_url(); ?>payment/index/<?php echo param_encrypt($account_data['account_id']); ?>" title="Payment"><i class="fa fa-money"></i></a>
                                <?php endif; ?>	
                                <?php if (check_logged_account_type(array('ACCOUNTS'))): ?>
                                    <a href="<?php echo base_url(); ?>reports/call_report/<?php echo param_encrypt($account_data['account_id']); ?>" title="Call Report"><i class="fa fa-phone"></i></a>
                                    <a href="<?php echo base_url(); ?>reports/cdr/<?php echo param_encrypt($account_data['account_id']); ?>" title="CDR" target="_blank">CDR</a>
                                <?php endif; ?>

                                <!--<a href="<?php echo base_url(); ?>customers/statement/<?php echo param_encrypt($account_data['account_id']); ?>" title="Account Statement"><i class="fa fa-hand-o-right"></i></a>-->
                                <a href="<?php echo base_url(); ?>customers/cState/<?php echo param_encrypt($account_data['account_id']); ?>" title="Call Analysis"><i class="fa fa-bar-chart-o"></i></a>
                                <?php
                                if (check_account_permission('customer', 'delete') && $this->session->userdata('session_current_account_id') != $account_data['id'] && strtolower($status_name) == 'closed'):
                                    ?>
                                    <a href="javascript:void(0);"  onclick=doConfirmDelete('<?php echo $account_data['account_id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>
                                <?php endif; ?> 

                                <?php
                                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'RESELLER', 'ACCOUNTS'))):
                                    ?>                                       
                                    <a href="<?php echo base_url(); ?>autologin/<?php echo param_encrypt($account_data['account_id']); ?>" title="Login As User"><i class="fa fa-sign-in"></i></a>
                                    <?php endif; ?>	
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
            <?php
            echo '<div class="btn-toolbar" role="toolbar"> <div class="btn-group col-md-5 col-sm-12 col-xs-12">';

            echo '</div>  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">  ' . $pagination . ' </div></div>';
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
        showDatatable('table-sort', [5], [2, "asc"]);
    });
</script>  