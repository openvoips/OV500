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
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Carrier QOS Summary</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
                <input type="hidden" name="search_action" value="search" />			
                <div class="form-group">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <input type="text" name="frmtime" id="reservation-time" class="form-control" value="<?php if (isset($_SESSION['search_data']['s_time'])) echo $_SESSION['search_data']['s_time']; ?>" />
                    </div>
                </div>
                <div class="form-group">	
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Destination</label>
                    <div class="col-md-4 col-sm-9 col-xs-12">
                        <input type="text" name="frmdest" id="frmdest" value="<?php if (isset($_SESSION['search_data']['s_dest'])) echo $_SESSION['search_data']['s_dest']; ?>" class="form-control data-search-field" placeholder="Destination">
                    </div>	
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Carrier</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="frmcarrier" id="frmcarrier" value="<?php if (isset($_SESSION['search_data']['s_carrier'])) echo $_SESSION['search_data']['s_carrier']; ?>" class="form-control data-search-field" placeholder="Carrier">
                        <!--<select name="frmcarrier" id="frmcarrier" class="form-control data-search-field">
                            <option value="">ALL</option>
                        <?php foreach ($carrier_data as $k => $v) { ?>								
                                    <option value="<?php echo $carrier_data[$k]['carrier_id_name']; ?>" <?php if ($_SESSION['search_data']['s_carrier'] == $carrier_data[$k]['carrier_id_name']) echo 'selected'; ?>><?php echo $carrier_data[$k]['carrier_name']; ?></option>
                        <?php } ?>
                        </select>-->
                    </div>                    
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">SIP Code</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <input type="text" name="frmsipcode" id="frmsipcode" value="<?php if (isset($_SESSION['search_data']['s_sip'])) echo $_SESSION['search_data']['s_sip']; ?>" class="form-control data-search-field" placeholder="SIP Code">
                    </div>	                 
                </div>
                <label class="control-label col-md-1 col-sm-3 col-xs-12">Prefix</label>
                <div class="col-md-4 col-sm-9 col-xs-12">
                    <input type="text" name="frmprefix" id="frmprefix" value="<?php if (isset($_SESSION['search_data']['s_prefix'])) echo $_SESSION['search_data']['s_prefix']; ?>" class="form-control data-search-field" placeholder="Prefix">
                </div> 
                <div class="form-group">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Ip Address</label>
                    <div class="col-md-3 col-sm-9 col-xs-12">
                        <input type="text" name="frmcode" id="frmcode" value="<?php if (isset($_SESSION['search_data']['s_code'])) echo $_SESSION['search_data']['s_code']; ?>" class="form-control data-search-field" placeholder="Ip Address">
                    </div>


                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Q850 Code</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <input type="text" name="frmq850code" id="frmq850code" value="<?php if (isset($_SESSION['search_data']['s_q850'])) echo $_SESSION['search_data']['s_q850']; ?>" class="form-control data-search-field" placeholder="Q850 Code">
                    </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Group by</label>
                    <div class="">
                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_date" <?php if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y') echo 'checked'; ?>> Date
                            </label>
                        </div>
                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_hour" <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y') echo 'checked'; ?>> Hour
                            </label>
                        </div>

                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_minute" <?php if (isset($_SESSION['search_data']['s_g_minute']) && $_SESSION['search_data']['s_g_minute'] == 'Y') echo 'checked'; ?>> Minute
                            </label>
                        </div>

                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_prefix" <?php if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y') echo 'checked'; ?>> Prefix
                            </label>
                        </div>
                        <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                            <label>
                                <input value="" type="checkbox" name="g_dest" <?php if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y') echo 'checked'; ?>> Destination
                            </label>
                        </div>



                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input type="checkbox" name="g_carrier" <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo 'checked'; ?>> Carrier
                            </label>
                        </div>
                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input type="checkbox" name="g_ip" <?php if (isset($_SESSION['search_data']['s_g_ip']) && $_SESSION['search_data']['s_g_ip'] == 'Y') echo 'checked'; ?>> IP
                            </label>
                        </div>
                        <div class="checkbox col-md-1 col-sm-6 col-xs-12">
                            <label>
                                <input type="checkbox" name="g_sip" <?php if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y') echo 'checked'; ?>> SIPCode
                            </label>
                        </div>
                        <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                            <label>
                                <input type="checkbox" name="g_q850" <?php if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y') echo 'checked'; ?>> Q850 Code
                            </label>
                        </div>

                    </div>


                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                    <input type="button" value="Reset" name="search_reset" class="btn btn-info" onclick="location.href = ''">                           
                    <div class="btn-group">
                        <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                            Export <span class="caret"></span></button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?php echo base_url() ?>reports/monitCarrier/export/<?php echo param_encrypt('csv'); ?>">CSV</a></li>
                            <li><a href="<?php echo base_url() ?>reports/monitCarrier/export/<?php echo param_encrypt('xlsx'); ?>">XLSX</a></li>
                            <li><a href="<?php echo base_url() ?>reports/monitCarrier/export/<?php echo param_encrypt('xls'); ?>">XLS</a></li>
                            <li><a href="<?php echo base_url() ?>reports/monitCarrier/export/<?php echo param_encrypt('txt'); ?>">TXT</a></li>
                            <li><a href="<?php echo base_url() ?>reports/monitCarrier/export/<?php echo param_encrypt('pdf'); ?>">PDF</a></li>
                        </ul>
                    </div>



                </div>
            </form>		
        </div>  
    </div>

    <div class="x_panel">
        <div class="x_title">
            <h2>Total Records : <?php if (isset($listing_count)) echo $listing_count;
        else echo '0'; ?></h2>
            <div class="clearfix"></div>
        </div>	
        <div class="x_content">
            <div class="table-responsive">
                <table id="analytics" class="table table-striped jambo_table bulk_action table-bordered">
                    <thead>
                        <tr class="headings thc">
                            <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo'<th class="column-title">Carrier</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_ip']) && $_SESSION['search_data']['s_g_ip'] == 'Y') echo '<th class="column-title">IP Address</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_date']) && $_SESSION['search_data']['s_g_date'] == 'Y') echo '<th class="column-title">Date</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_hour'] == 'Y') echo '<th class="column-title">Hour</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_hour']) && $_SESSION['search_data']['s_g_minute'] == 'Y') echo '<th class="column-title">Minute</th>'; ?>
                            <?php if (isset($_SESSION['search_data']['s_g_prefix']) && $_SESSION['search_data']['s_g_prefix'] == 'Y') echo '<th class="column-title">Prefix</th>'; ?>

                            <?php if (isset($_SESSION['search_data']['s_g_dest']) && $_SESSION['search_data']['s_g_dest'] == 'Y') echo'<th class="column-title">Destination</th>'; ?>
                            <th class="column-title" >Total Duration</th>
                            <th class="column-title" >Total Calls</th>
                            <th class="column-title" >Answered Calls</th>
                            <th class="column-title">ACD</th>
                            <th class="column-title">ASR</th>
                            <th class="column-title">Avg PDD</th>
                            <?php if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y')
                                echo'<th class="column-title">SIP Code</th>';
                            ?>
                            <?php if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y')
                                echo'<th class="column-title">Q850 Code</th>';
                            ?>
                            <?php
                            if ((isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') || $_SESSION['search_data']['s_carrier'] != '') {                                 
                                echo '<th class="column-title">Cost</th>';
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

                        if (isset($listing_count) && $listing_count > 0) {
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

                                    <?php if (isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') echo '<td>' . $listing_row['carrier_id_name'] . '</td>'; ?>
                                    <?php if (isset($_SESSION['search_data']['s_g_ip']) && $_SESSION['search_data']['s_g_ip'] == 'Y') echo '<td>' . $listing_row['ip_address'] . '</td>'; ?>

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

                                    <?php if (isset($_SESSION['search_data']['s_g_sip']) && $_SESSION['search_data']['s_g_sip'] == 'Y') echo '<td>' . $listing_row['SIPCODE'] . '</td>'; ?>
                                    <?php if (isset($_SESSION['search_data']['s_g_q850']) && $_SESSION['search_data']['s_g_q850'] == 'Y') echo '<td>' . $listing_row['Q850CODE'] . '</td>'; ?>
                                    <?php
                                    if ((isset($_SESSION['search_data']['s_g_carrier']) && $_SESSION['search_data']['s_g_carrier'] == 'Y') || $_SESSION['search_data']['s_carrier'] != '') {                           
                                        echo '<td>' . $currency_abbr($listing_row['currency_id']) . ' ' . $listing_row['cost'] . '</td>';
                                    }
                                    ?>
                                </tr>

        <?php
    }
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

        $('#analytics').DataTable({
            searching: false,
            paging: false,
            bInfo: false,
            scrollY: 400,
            scrollX: true
        });

        /*$('#analytics tbody')
         .on( 'mouseenter', 'td', function () {
         var colIdx = table.cell(this).index().column;
         
         $( table.cells().nodes() ).removeClass( 'alert_red' );
         $( table.column( colIdx ).nodes() ).addClass( 'alert_red' );
         } );*/

    });
</script>