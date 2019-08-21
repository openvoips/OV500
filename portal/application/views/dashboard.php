<?php
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

if (isset($user_result)) {
    $is_user_details_exists = true;

    $dp = 4; //default value
    if (in_array(strtolower($user_result['user_type']), array('user', 'reseller')) && $user_result['dp'] != '')
        $dp = $user_result['dp'];
}
?>
<div class="">
    <div class="clearfix"></div>   
    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="clearfix"></div>
        <div class="x_content">

            <div class="row">

                <?php            
                if (check_logged_account_type(array('RESELLER', 'CUSTOMER'))) {
                    ?>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Balance</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    </li>
                                </ul>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content">
                                <div class="table-responsive">
                                    <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">                       
                                    <tr >
                                        <th>Account Type</th>
                                        <th class="text-right">
                                            <?php
                                            $billing_type = $user_result['billing_type'];
                                            $billing_type_array = array('prepaid' => 'Prepaid', 'postpaid' => 'Postpaid');
                                            if (isset($billing_type_array[$billing_type]))
                                                echo $billing_type_array[$billing_type];
                                            ?> 
                                        </th>
                                    </tr>
                                    <tr >
                                        <th>Balance</th>
                                        <th class="text-right"><?php echo number_format(-$user_result['balance']['outstanding_balance'], $dp, '.', '') . ' ' . $user_result['currency']['name']; ?> </th>
                                    </tr>						
                                    <tr >
                                        <th>Temporary Credit </th>
                                        <th class="text-right"><?php echo number_format($user_result['balance']['credit_limit'], $dp, '.', '') . ' ' . $user_result['currency']['name']; ?> </th>
                                    </tr>
                                    <tr >
                                        <th>Available Balance</th>
                                        <th class="text-right"><?php echo number_format($user_result['balance']['balance'], $dp, '.', '') . ' ' . $user_result['currency']['name']; ?> </th>
                                    </tr>

                                </table>    
                                <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                                    <a class="title" href="<?php echo base_url('payment/make_payment'); ?>"><button type="button" class="btn btn-primary btn-lg "><i class="fa fa-hand-o-right"></i> Make Payment</button></a>
                                </div>
                            </div>
                            </div>
                        </div>      

                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Yesterday Activity</h2>
                                <ul class="nav navbar-right panel_toolbox">

                                </ul>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content">
                                <div class="table-responsive">
                                    <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                                        <thead>
                                            <tr class="headings thc">
                                                <th class="column-title">Activity </th>
                                                <th class="column-title text-right">Amount</th>						
                                            </tr>
                                        </thead>		
                                        <tbody>
                                            <?php
                                            if (count($statement_data['result']) > 0) {
                                                foreach ($statement_data['result'] as $sdr_data) {
                                                    $debit = $credit = '';
                                                    $display_text = '';
                                                    $rule_type = $sdr_data['rule_type'];

                                                    if (isset($sdr_terms[$rule_type])) {
                                                        $term_array = $sdr_terms[$rule_type];

                                                        $display_text = $term_array['display_text'];
                                                        $total_cost = round($sdr_data['total_cost'], $dp);

                                                        $tr_html = '<tr>' .
                                                                '<td>' . $display_text . '</td>' .
                                                                '<td align="right">' . $total_cost . ' ' . $user_result['currency']['name'] . '</td>' .
                                                                '</tr>';
                                                        echo $tr_html;
                                                    }
                                                }
                                            } else {
                                                ?>
                                                <tr>
                                                    <td colspan="3" align="center"><strong>No Record Found</strong></td>
                                                </tr>
                                                <?php
                                            }
                                            ?>			

                                        </tbody>
                                    </table>   
                                </div>
                                <div class="text-right">
                                    <a class="title" href="<?php echo base_url('customers/statement'); ?>"><button type="button" class="btn btn-primary btn-lg active"><i class="fa fa-hand-o-right"></i> Account Statement</button></a>
                                </div> 


                            </div>

                        </div>


                    </div>
                    <div class="clearfix"></div>
                    <?php
                } elseif (check_logged_account_type(array('ADMIN', 'SUBADMIN'))) {
                    ?>
                    <div class="animated flipInY  col-md-12 col-sm-12 col-xs-12">  
                        <div class="text-center"><h1>Welcome To OV500 Billing & Switching Software. </h1>
                                </div>
                       <div class="text-center">
                           <p><h3>Live System Monitoring & Traffic Statistics</h3></p> 
                                    <a class="title" href="<?php echo base_url('reports/monin'); ?>"><button type="button" class="btn btn-primary btn-lg active"><i class="fa fa-hand-o-right"></i> Monitor System</button></a>
                                </div> 
                    </div>
                    <?php
                } elseif (check_logged_account_type(array('ACCOUNTS'))) {
                    
                }
                ?>      

            </div>              
            <div class="clearfix"></div>
        </div>



    </div>
    <div class="clearfix"></div>
</div>      