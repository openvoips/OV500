<!--<link href="<?php echo base_url() ?>theme/vendors/nprogress/nprogress.css" rel="stylesheet"/>
<link href="<?php echo base_url() ?>theme/vendors/pnotify/dist/pnotify.css" rel="stylesheet"/>
<link href="<?php echo base_url() ?>theme/vendors/pnotify/dist/pnotify.buttons.css" rel="stylesheet"/>
<link href="<?php echo base_url() ?>theme/vendors/pnotify/dist/pnotify.nonblock.css" rel="stylesheet"/>-->
<link href="<?php echo base_url() ?>theme/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet"/>
<link href="<?php echo base_url() ?>/theme/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet"/>   

<style type="text/css">
    .data-search-field{
        margin-bottom: 3px;
    }
</style>
<div class="container-fluid">
    <div class="block-header">
        <h2>Analytics Reports : Customers (Outbound)</h2>
        <ul class="nav navbar-right panel_toolbox">

        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">
                    <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
                        <input type="hidden" name="search_action" value="search" />			
                        <div class="form-group">


                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Call Date Range</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="frmtime" id="reservation-time" class="form-control data-search-field" value="<?php if (isset($_SESSION['search_data']['s_call_date'])) echo $_SESSION['search_data']['s_call_date']; ?>" />
                            </div>

                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Customer Type</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">

                                <select id="frmctype" name="frmctype" class="form-control data-search-field">
                                    <option value="U" <?php if (isset($_SESSION['search_data']['s_ctype']) && $_SESSION['search_data']['s_ctype'] == 'U') echo 'selected'; ?>>Customer</option>
                                    <option value="R" <?php if (isset($_SESSION['search_data']['s_ctype']) && $_SESSION['search_data']['s_ctype'] == 'R') echo 'selected'; ?>>Reseller</option>
                                </select>
                            </div>

                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Account Code</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="frmcode" id="frmcode" value="<?php if (isset($_SESSION['search_data']['s_code'])) echo $_SESSION['search_data']['s_code']; ?>" class="form-control data-search-field" placeholder="Account Code">
                            </div>


                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Customer/Reseller Name</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="customer_company_name" id="user_company_name" value="<?php echo $_SESSION['search_data']['s_customer_company_name']; ?>" class="form-control data-search-field" placeholder="Company Name">
                            </div>	

                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Dialed destination</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="frmdest" id="frmdest" value="<?php if (isset($_SESSION['search_data']['s_dest'])) echo $_SESSION['search_data']['s_dest']; ?>" class="form-control data-search-field" placeholder="Destination">
                            </div>	
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Prefix</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="frmprefix" id="frmprefix" value="<?php if (isset($_SESSION['search_data']['s_prefix'])) echo $_SESSION['search_data']['s_prefix']; ?>" class="form-control data-search-field" placeholder="Prefix">
                            </div>                  

                        </div>
                        <div class="form-group">

                            <?php if (!check_logged_user_group(array('RESELLER'))) : ?>
                                <label class="control-label col-md-2 col-sm-3 col-xs-12">Carrier</label>
                                <div class="col-md-2 col-sm-3 col-xs-12">


                                    <input type="text" name="frmcarrier" id="frmcarrier" value="<?php echo $_SESSION['search_data']['s_carrier']; ?>" class="form-control data-search-field" placeholder="Carrier">


                                </div>
                            <?php else: ?>

                            <?php endif; ?>     

                            <label class="control-label col-md-2 col-sm-3 col-xs-12">SIP Code</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="frmsipcode" id="frmsipcode" value="<?php if (isset($_SESSION['search_data']['s_sip'])) echo $_SESSION['search_data']['s_sip']; ?>" class="form-control data-search-field" placeholder="SIP Code">
                            </div>	
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Q850 Code</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="frmq850code" id="frmq850code" value="<?php if (isset($_SESSION['search_data']['s_q850'])) echo $_SESSION['search_data']['s_q850']; ?>" class="form-control data-search-field" placeholder="Q850 Code">
                            </div>


                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Search data should Group by</label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input value="" type="checkbox" id="g_date" name="g_date" <?php if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y') echo 'checked'; ?> > <label for="g_date"> Date  </label>
                                </div>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input value="" type="checkbox" id="g_hour" name="g_hour" <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y') echo 'checked'; ?>><label for="g_hour"> Hour    </label>
                                </div>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input value="" type="checkbox" id="g_minute" name="g_minute" <?php if (isset($_SESSION['search_data']['s_g_minute']) && $_SESSION['search_data']['s_g_minute'] == 'Y') echo 'checked'; ?>> <label for="g_minute">Minute   </label>
                                </div>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input value="" type="checkbox" id="g_prefix" name="g_prefix" <?php if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y') echo 'checked'; ?>> <label for="g_prefix">Prefix   </div>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input value="" type="checkbox" id="g_dest" name="g_dest" <?php if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y') echo 'checked'; ?>> <label for="g_dest">Destination    </label>
                                </div>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input type="checkbox" id="g_user" name="g_user" <?php if (isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') echo 'checked'; ?>> <label for="g_user"> Customer      </label>
                                </div>
                                <?php if (!check_logged_user_group(array('RESELLER'))) : ?>
                                    <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                        <input type="checkbox" id="g_carrier" name="g_carrier" <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo 'checked'; ?>> <label for="g_carrier"> Carrier   </label>
                                    </div>
                                <?php endif; ?>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input type="checkbox" id="g_sip" name="g_sip" <?php if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y') echo 'checked'; ?>> <label for="g_sip"> SIPCode    </label>
                                </div>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input type="checkbox" id="g_q850" name="g_q850" <?php if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y') echo 'checked'; ?>> <label for="g_q850">Q850 Code    </label>
                                </div>
                            </div>
                        </div> 
                        <div class="ln_solid"></div>
                        <div class="form-group text-center">
                            <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                            <input type="button" value="Reset" name="search_reset" class="btn btn-info" onclick="location.href = ''">                           

                        </div>
                    </form>		
                </div>  


                <div class="body">
                    <?php if (isset($listing_count) && $listing_count > 0): ?>
                        <div class="btn-group pull-right">                    

                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url() . 'reports/CustQOSR/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>                            
                            </ul>
                        </div> 
                    <?php endif; ?>



                    <h5>Total Records : <?php
                        if (isset($listing_count))
                            echo $listing_count;
                        else
                            echo '0';
                        ?></h5>
                    <div class="clearfix"></div>
                </div>	
                <div class="body">

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
                                    <th class="column-title" width="">ACD</th>
                                    <th class="column-title" width="">ASR</th>
                                    <th class="column-title" width="">Avg PDD</th>
                                    <th class="column-title" width="60">Cost</th>
                                    <?php if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y') echo'<th class="column-title" width="">SIP Code</th>'; ?>
                                    <?php if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y') echo'<th class="column-title" width="">Q850 Code</th>'; ?>
                                    <?php
                                    if ((isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') || $_SESSION['search_data']['s_code'] != '') {
                                        
                                    }
                                    ?>
                                </tr>
                            </thead>		
                            <tbody>
                                <?php
                                $currency_abbr = function ($id) use ($currency_data) {
                                    $key = array_search($id, array_column($currency_data, 'currency_id'));
                                    if ($key === false)
                                        return '';
                                    else
                                        return $currency_data[$key]['name'];
                                };

                                if (isset($listing_count) && $listing_count > 0) {//var_dump($listing_data);
                                    foreach ($listing_data as $listing_row) {

                                        if ($listing_row['asr'] <= 20)
                                            $asr_status = 'alert_red';
                                        elseif ($listing_row['asr'] > 20 && $listing_row['asr'] <= 50)
                                            $asr_status = 'alert_yellow';
                                        else
                                            $asr_status = 'alert_green';

                                        if ($listing_row['acd'] <= 1)
                                            $acd_status = 'alert_red';
                                        elseif ($listing_row['acd'] > 1 && $listing_row['acd'] <= 2)
                                            $acd_status = 'alert_yellow';
                                        else
                                            $acd_status = 'alert_green';

                                        if ($listing_row['pdd'] <= 1)
                                            $pdd_status = 'alert_green';
                                        elseif ($listing_row['pdd'] > 1 && $listing_row['pdd'] <= 2)
                                            $pdd_status = 'alert_yellow';
                                        else
                                            $pdd_status = 'alert_red';
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
                                            <td class="<?php echo $acd_status; ?>"><?php echo $listing_row['acd']; ?></td>
                                            <td class="<?php echo $asr_status; ?>"><?php echo $listing_row['asr']; ?></td>
                                            <td class="<?php echo $pdd_status; ?>"><?php echo $listing_row['pdd']; ?></td>
                                            <td><?php
                                                if ($currency_abbr($listing_row['currency_id'])) {
                                                    echo $currency_abbr($listing_row['currency_id']) . ' ' . $listing_row['cost'];
                                                } else {
                                                    echo "0.00";
                                                }
                                                ?></td>


                                            <?php if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y') echo '<td>' . $listing_row['SIPCODE'] . '</td>'; ?>
                                            <?php if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y') echo '<td>' . $listing_row['Q850CODE'] . '</td>'; ?>
                                            <?php
                                            if ((isset($_SESSION['search_data']['s_g_user']) && $_SESSION['search_data']['s_g_user'] == 'Y') || $_SESSION['search_data']['s_code'] != '') {
                                                
                                            }
                                            ?>
                                        </tr>

                                        <?php
                                    }
                                } else {
                                    
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<style>
    .form-horizontal .control-label {
        padding: 7px 5px;
    }
    .alert_yellow{
        background-color:#FFFF33;
        color:#000000;
    }
    .alert_green{
        background-color:#2E8B57;
        color:#ffffff;
    }
    .alert_red{
        background-color:#F14E66;
        color:#ffffff;
    }
    table.jambo_table tbody tr:hover td {
        color:#8b87bb;
        background-color:#f0faf8;
    }
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

        $('#analytics').DataTable({
            searching: false,
            paging: false,
            bInfo: false,
            //			scrollY: 400,
            //        	scrollX: true
        });
    });

</script>


<!-- 
<script src="<?php echo base_url() ?>theme/vendors/fastclick/lib/fastclick.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/nprogress/nprogress.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/pnotify/dist/pnotify.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/pnotify/dist/pnotify.buttons.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/pnotify/dist/pnotify.nonblock.js"></script>-->

<!-- Datatables -->
<script src="<?php echo base_url() ?>theme/vendors/datatables.net/js/jquery.dataTables.min.js"></script> 
<script src="<?php echo base_url() ?>theme/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

