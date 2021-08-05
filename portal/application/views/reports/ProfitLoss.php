
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Traffic Profit & Loss Reports</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
                <input type="hidden" name="search_action" value="search" />			
                <div class="form-group">

                    <div class="row">
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                        <div class="col-md-4 col-sm-9 col-xs-12">
                            <input type="text" name="frmtime" id="reservation-time" class="form-control" value="<?php if (isset($_SESSION['search_data']['s_call_date'])) echo $_SESSION['search_data']['s_call_date']; ?>" />
                        </div>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <select id="frmctype" name="frmctype" class="form-control">
                                <option value="U" <?php if (isset($_SESSION['search_data']['s_ctype']) && $_SESSION['search_data']['s_ctype'] == 'U') echo 'selected'; ?>>User</option>
                                <option value="R" <?php if (isset($_SESSION['search_data']['s_ctype']) && $_SESSION['search_data']['s_ctype'] == 'R') echo 'selected'; ?>>Reseller</option>
                            </select>
                        </div>

                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Account Code</label>
                        <div class="col-md-3 col-sm-9 col-xs-12">
                            <input type="text" name="frmcode" id="frmcode" value="<?php if (isset($_SESSION['search_data']['s_code'])) echo $_SESSION['search_data']['s_code']; ?>" class="form-control data-search-field" placeholder="Account Code">
                        </div>

                    </div> <!-- end of the row-->
                    <br />
                    <div class="row"> 


                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Company Name</label>
                        <div class="col-md-3 col-sm-9 col-xs-12">
                            <input type="text" name="customer_company_name" id="user_company_name" value="<?php echo $_SESSION['search_data']['s_customer_company_name']; ?>" class="form-control data-search-field" placeholder="Company Name">
                        </div>	

                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Destination</label>
                        <div class="col-md-3 col-sm-9 col-xs-12">
                            <input type="text" name="frmdest" id="frmdest" value="<?php if (isset($_SESSION['search_data']['s_dest'])) echo $_SESSION['search_data']['s_dest']; ?>" class="form-control data-search-field" placeholder="Destination">
                        </div>	

                    </div>  <!-- end of the row-->


                    <div class="row">

                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Prefix</label>
                        <div class="col-md-3 col-sm-9 col-xs-12">
                            <input type="text" name="frmprefix" id="frmprefix" value="<?php if (isset($_SESSION['search_data']['s_prefix'])) echo $_SESSION['search_data']['s_prefix']; ?>" class="form-control data-search-field" placeholder="Prefix">
                        </div>
                        <div class="form-group">
                            <?php if (!check_logged_user_group(array('RESELLER'))) : ?>
                                <label class="control-label col-md-1 col-sm-3 col-xs-12">Carrier</label>
                                <div class="col-md-3 col-sm-9 col-xs-12">
                                    <!--<input type="text" name="frmcarrier" id="frmcarrier" value="<?php if (isset($_SESSION['search_data']['s_carrier'])) echo $_SESSION['search_data']['s_carrier']; ?>" class="form-control data-search-field" placeholder="Carrier">-->

                                    <select name="frmcarrier" id="frmcarrier" class="form-control data-search-field">
                                        <option value="">ALL</option>
                                        <?php foreach ($carrier_data as $k => $v) { ?>								
                                            <option value="<?php echo $carrier_data[$k]['carrier_id']; ?>" <?php if ($_SESSION['search_data']['s_carrier'] == $carrier_data[$k]['carrier_id']) echo 'selected'; ?>><?php echo $carrier_data[$k]['carrier_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <div class="col-md-4 col-sm-12 col-xs-12">
                                </div>
                            <?php endif; ?>     

                        </div>

                    </div>  <!--end of the row-->

                    <div class="ln_solid"></div>

                    <div class="form-group">
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Group by</label>
                        <div class="">
                            <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                <label>
                                    <input value="" type="checkbox" name="g_date" <?php if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y') echo 'checked'; ?> > Date
                                </label>
                            </div>
                            <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                <label>
                                    <input value="" type="checkbox" name="g_hour" <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y') echo 'checked'; ?>> Hour
                                </label>
                            </div>

                            <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                <label>
                                    <input value="" type="checkbox" name="g_minute" <?php if (isset($_SESSION['search_data']['s_g_minute']) && $_SESSION['search_data']['s_g_minute'] == 'Y') echo 'checked'; ?>> Minute 
                                </label>

                            </div>

                            <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                <label>
                                    <input value="" type="checkbox" name="g_prefix" <?php if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y') echo 'checked'; ?>> Prefix &nbsp;
                                </label>
                            </div>
                            <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                <label>
                                    <input value="" type="checkbox" name="g_dest" <?php if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y') echo 'checked'; ?>> Destination
                                </label>
                            </div>


                            <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                <label>
                                    <input type="checkbox" name="g_user" <?php if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') echo 'checked'; ?>> Customer
                                </label>
                            </div>
                            <?php if (!check_logged_user_group(array('RESELLER'))) : ?>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <label>
                                        <input type="checkbox" name="g_carrier" <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo 'checked'; ?>> Carrier
                                    </label>
                                </div>
                            <?php endif; ?>                            
                        </div>
                    </div> 
                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                        <input type="button" value="Reset" name="search_reset" class="btn btn-info" onclick="location.href = ''">                           

                    </div>
            </form>		
        </div>  
    </div>

    <div class="x_panel">
        <?php if (isset($listing_count) && $listing_count > 0): ?>
            <div class="btn-group pull-right">                    

                <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                    Export <span class="caret"></span></button>
                <ul class="dropdown-menu" role="menu">
                    <?php
                    $export_format_array = get_export_formats();
                    foreach ($export_format_array as $export_format) {
                        echo '<li><a href="' . base_url() . 'reports/ProfitLoss/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                    }
                    ?>                            
                </ul>
            </div> 
        <?php endif; ?>

        <div class="x_title">
            <h2>Total Records : <?php
                if (isset($listing_count))
                    echo $listing_count;
                else
                    echo '0';
                ?></h2>
            <div class="clearfix"></div>
        </div>	
        <div class="x_content">
            <div class="table-responsive">
                <table id="analytics" class="table table-striped jambo_table bulk_action table-bordered">
                    <thead>
                        <tr class="headings thc">
                            <?php if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') echo '<th class="column-title">Customer</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo'<th class="column-title">Carrier</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y') echo '<th class="column-title">Date</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y') echo '<th class="column-title">Hour</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_minute'] == 'Y') echo '<th class="column-title">Minute</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y') echo '<th class="column-title">Prefix</th>'; ?>

                            <?php if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y') echo'<th class="column-title">Destination</th>'; ?>
                            <th class="column-title" width="">Total Duration</th>
                            <th class="column-title" width="">Total Calls</th>
                            <th class="column-title" width="">Ans Calls</th>                           
                            <th class="column-title" width="60">Currency</th>
                            <th class="column-title" width="60">Cost</th>
                            <th class="column-title" width="60">Seller Cost</th>
                            <th class="column-title" width="60">Profit & Loss</th>
                            <?php if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y') echo'<th class="column-title" width="">SIP Code</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y') echo'<th class="column-title" width="">Q850 Code</th>'; ?>
                            <?php
                            if ((isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') || $_SESSION['search_data']['s_code'] != '') {
                                //echo '<th class="column-title" width="60">Cost</th>'; 
                            }
                            ?>
                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        $currency_abbr = function($id) use ($currency_data) {
                            $key = array_search($id, array_column($currency_data, 'currency_id'));
                            if ($key === false)
                                return '';
                            else
                                return $currency_data[$key]['name'];
                        };

                        if (isset($listing_count) && $listing_count > 0) {//var_dump($listing_data);
                            foreach ($listing_data as $listing_row) {
                                ?>
                                <tr>
                                    <?php if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') echo '<td>' . $listing_row['account_code'] . '</td>'; ?>
                                    <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo '<td>' . $listing_row['carrier_id'] . '</td>'; ?>

                                    <?php if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y') echo '<td>' . $listing_row['call_date'] . '</td>'; ?>
                                    <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y') echo '<td>' . $listing_row['calltime_h'] . '</td>'; ?>
                                    <?php if (isset($_SESSION['search_data']['s_g_minute']) && $_SESSION['search_data']['s_g_minute'] == 'Y') echo '<td>' . $listing_row['calltime_m'] . '</td>'; ?>
                                    <?php if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y') echo '<td>' . $listing_row['prefix'] . '</td>'; ?>
                                    <?php if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y') echo '<td>' . $listing_row['prefix_name'] . '</td>'; ?>
                                    <td><?php echo $listing_row['total_duration']; ?></td>
                                    <td><?php echo $listing_row['total_calls']; ?></td>
                                    <td><?php echo $listing_row['answered_calls']; ?></td>
                                    <td><?php
                                        echo $currency_abbr($listing_row['currency_id']);
                                        ?></td>
                                    <td><?php
                                        if ($currency_abbr($listing_row['currency_id'])) {
                                            echo $listing_row['cost'];
                                        } else {
                                            echo "0.00";
                                        }
                                        ?></td>
                                    <td><?php
                                        if (strlen($listing_row['r1_account_id']) == 0) {
                                            echo round($listing_row['carrier_cost'], 4);
                                        }
                                        ?></td>
                                    <td><?php
                                        if (strlen($listing_row['r1_account_id']) == 0) {
                                            echo round($listing_row['cost'] - $listing_row['carrier_cost'], 4);
                                        }
                                        ?></td>


                                    <?php
                                    if ((isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') || $_SESSION['search_data']['s_code'] != '') {
                                        //echo '<td>' . $currency_abbr($listing_row['currency_id']) .' '. $listing_row['cost'] . '</td>'; 
                                    }
                                    ?>
                                </tr>

                                <?php
                            }
                        } else {
                            ?>
                                           <!--<tr>
                                                    <td colspan="11" align="center"><strong>No Record Found</strong></td>
                                            </tr>-->
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
    .form-horizontal .control-label {
        padding: 7px 5px;
    }
    .alert_yellow{ background-color:#FFFF33; color:#000000;}
    .alert_green{ background-color:#2E8B57; color:#ffffff;}
    .alert_red{ background-color:#F14E66; color:#ffffff;}
    table.jambo_table tbody tr:hover td { color:#8b87bb; background-color:#f0faf8;}
</style>

<script>
    $(document).ready(function () {

        $("#reservation-time").daterangepicker({
            timePicker: !0,
            timePickerIncrement: 5,
            locale: {
                format: "YYYY-MM-DD HH:mm"
            },
            timePicker24Hour: true,
            ranges: {
                'Last 15 Minute': [moment().subtract(15, 'minute'), moment()],
                'Last 30 Minute': [moment().subtract(30, 'minute'), moment()],
                'Last 1 Hour': [moment().subtract(1, 'hour'), moment()],
                'Today': [moment().startOf('days'), moment().endOf('days')],
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                /*'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],*/
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        showDatatable('analytics', [], [1, "asc"]);


    });

</script>