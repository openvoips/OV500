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
<!-- Chart.js -->
<script src="<?php echo base_url() ?>theme/vendors/Chart.js/dist/Chart.min.js"></script>
<!-- Nice Scroll -->
<script src="<?php echo base_url() ?>theme/vendors/jquery.nicescroll-master/dist/jquery.nicescroll.min.js"></script>
<!-- jQuery Sparklines -->
<script src="<?php echo base_url() ?>theme/vendors/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
<style type="text/css">
    .red1{background-color:#AA0000;color: white;}/*FF3535*/
    .red2{background-color:#FE6363;color: white;}
    .red3{background-color:#FEA7A7;color:#F0F3F4;}
    .red4{background-color:#FFDFDF;}   
</style>         

<div id="analytics" class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_content">
            <div class="col-md-12 col-sm-12 col-xs-12" >

                <div class="row">
                    <table class="table table-condensed table-bordered">
                        <tr>
                            <td id="customer_usage_id" class="text-success">&nbsp;</td>
                            <td id="carrier_usage_id" class="text-danger">&nbsp;</td>            	
                        </tr>   
                    </table>
                </div>           

                <div class="row tile_count" style="margin-top:0px">
                    <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                        <div class="count">0</div>
                        <span class="count_bottom">Total Concurrent Calls</span>
                    </div>
                    <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                        <div class="count">0</div>
                        <span class="count_bottom">Total Answered Calls</span>
                    </div>
                    <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                        <div class="count">0</div>
                        <span class="count_bottom">Total Ringing Calls</span>
                    </div>
                    <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                        <div class="count">0</div>
                        <span class="count_bottom">Total Progress Calls</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-5 col-xs-12" style="padding: 0 0 0 0px;">
                <div class="x_panel tile">
                    <div class="x_title">
                        <h2>Incoming Calls</h2>                  
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content" id="incoming_calls">
                        <table class="" style="width:100%">
                            <tr>
                                <td class="text-center" id="div_canvasDoughnut1">
                                    <span class="loading"><img src="<?php echo base_url(); ?>theme/default/images/loading.gif"></span>
                                    <canvas class="canvasDoughnut" id="canvasDoughnut1" height="140" width="140"></canvas></td>
                            </tr>
                            <tr>  
                                <td>
                                    <table class="tile_info">
                                        <tr><td><p><i class="fa fa-square grey"></i>Total </p></td><td>0</td></tr>
                                        <tr><td><p><i class="fa fa-square grey"></i>Answered </p></td><td>0</td></tr>
                                        <tr><td><p><i class="fa fa-square grey"></i>Failed </p></td><td>0</td></tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div class="col-xs-12 tile">                        	                      	
                            <span class="sparkline_two" style="height: 160px;">
                                <canvas style="display: inline-block; vertical-align: top; width: 196px; height: 40px; margin-top: 20px;" width="196"></canvas>
                            </span>
                            <div class="duration">Total Duration <b>0</b></div>  
                        </div>
                        <div class="col-xs-12 tile" >
                            <table class="countries_list" style="border-collapse:inherit;">
                                <tbody>
                                    <tr><td>ASR</td><td class="fs15 fw700 text-right">0</td></tr>
                                    <tr><td>ACD</td><td class="fs15 fw700 text-right">0</td></tr>
                                    <tr><td>PPD</td><td class="fs15 fw700 text-right">0</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>				
            </div>
            <div class="col-md-3 col-sm-5 col-xs-12" style="padding: 0 0 0 1px;">
                <div class="x_panel tile">
                    <div class="x_title">
                        <h2>Outgoing Calls</h2>                  
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content" id="outgoing_calls">
                        <table class="" style="width:100%">
                            <tr>
                                <td class="text-center" id="div_canvasDoughnut2">
                                    <span class="loading"><img src="<?php echo base_url(); ?>theme/default/images/loading.gif"></span>
                                    <canvas class="canvasDoughnut" id="canvasDoughnut2" height="140" width="140"></canvas></td>
                            </tr>
                            <tr> 
                                <td>
                                    <table class="tile_info">
                                        <tr><td><p><i class="fa fa-square grey"></i>Total </p></td><td>0</td></tr>
                                        <tr><td><p><i class="fa fa-square grey"></i>Answered </p></td><td>0</td></tr>
                                        <tr><td><p><i class="fa fa-square grey"></i>Failed </p></td><td>0</td></tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div class="col-xs-12 tile">              	
                            <span class="sparkline_two" style="height: 160px;">
                                <canvas style="display: inline-block; vertical-align: top; width: 196px; height: 40px; margin-top: 20px;" width="196"></canvas>
                            </span>
                            <div class="duration">Total Duration <b>0</b></div>  
                        </div>
                        <div class="col-xs-12 tile" >
                            <table class="countries_list" style="border-collapse:inherit;">
                                <tbody>
                                    <tr><td>ASR</td><td class="fs15 fw700 text-right">0</td></tr>
                                    <tr><td>ACD</td><td class="fs15 fw700 text-right">0</td></tr>
                                    <tr><td>PPD</td><td class="fs15 fw700 text-right">0</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-7 col-xs-12" style="padding: 0 0 0 1px;">
                <div class="x_panel tile fixed_height_box">
                    <div class="x_title">
                        <h2>Carrier</h2>                  
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content fixed_height_box_inner">                   

                        <ul class="list-unstyled top_profiles scroll-view" id="carrierList">
                            <li class="media event">
                                <div class="media-body">
                                    <a class="title" href="#">CARRIER [TYPE]</a>
                                    <p>IPADDRESS</p>
                                    <!--<div class="progress progress_sm" style="width: 90%;margin: 10px 0;"><div class="progress-bar bg-green" role="progressbar" data-transitiongoal="80" style="width: 80%;" aria-valuenow="79"></div></div>-->
                                    <p style="color:blue;"><strong>TOTAL</strong> [ANSWER / REJECT] </p>
                                </div>
                            </li>
                        </ul>

                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-7 col-xs-12" style="padding: 0 0 0 1px;">
                <div class="x_panel tile fixed_height_box">
                    <div class="x_title">

                        <h2>Customer</h2>                  
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content fixed_height_box_inner">                   

                        <ul class="list-unstyled top_profiles scroll-view" id="customerList">
                            <li class="media event">
                                <div class="media-body">
                                    <a class="title" href="#">CUSTOMER [TYPE]</a>
                                    <p>IPADDRESS</p>
                                    <!--<div class="progress progress_sm" style="width: 90%;margin: 10px 0;"><div class="progress-bar bg-green" role="progressbar" data-transitiongoal="80" style="width: 80%;" aria-valuenow="79"></div></div>-->
                                    <p style="color:blue;"><strong>TOTAL</strong> [ANSWER / REJECT] </p>
                                </div>
                            </li>
                        </ul>			

                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>







            <!-- tonmoy -->
            <div class="col-md-2 col-sm-7 col-xs-12" style="padding: 0 0 0 1px;">
                <div class="x_panel tile fixed_height_box">
                    <div class="x_title">
                        <h2>Destination</h2>                  
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content fixed_height_box_inner">                   

                        <ul class="list-unstyled top_profiles scroll-view" id="destinationList">
                            <li class="media event"><div class="media-body"><b>DESTINATION</b><p><strong>TOTAL CALLS</strong> [<span style="color:#089378;">ANSWERING</span> / <span style="color:#e74c3c;">RINGING</span>] </p></div></li>
                        </ul>

                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>





            <div class="clearfix"></div>
            <div class="col-md-6 col-sm-12 col-xs-12">                                     
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                        <thead>

                            <tr class="headings thc">
                                <th colspan="6" class="text-center">Customer (Last One Hour)</th>
                            </tr>
                            <tr class="headings thc">
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Ans</th>
                                <th>ASR</th>
                                <th>ACD</th>
                                <th>Cost</th>
                            </tr>
                        </thead>  
                        <tfoot>
                            <tr >
                                <th colspan="6" class="text-right text-success">Refresh Time: 2 min </th>
                            </tr>
                        </tfoot>                              
                        <tbody id="id_customer_call_stat">
                            <tr class="text-center">
                                <td colspan="6"><img src="<?php echo base_url(); ?>theme/default/images/loading.gif"></td>
                            </tr>                                                        
                        </tbody>
                    </table>                   
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">

                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                        <thead>
                            <tr class="headings thc">
                                <th colspan="6" class="text-center">Carrier (Last One Hour)</th>
                            </tr>                    
                            <tr class="headings thc">
                                <th>Carrier</th>
                                <th>Total</th>
                                <th>Ans</th>
                                <th>ASR</th>
                                <th>ACD</th>
                                <th>Cost</th>
                            </tr>
                        </thead>  
                        <tfoot>
                            <tr >
                                <th colspan="6" class="text-right text-success">Refresh Time: 2 min </th>
                            </tr>
                        </tfoot>  
                        <tbody id="id_carrier_call_stat">
                            <tr class="text-center">
                                <td colspan="6"><img src="<?php echo base_url(); ?>theme/default/images/loading.gif"></td>
                            </tr>                                                        
                        </tbody>
                    </table>

                </div>
            </div>


        </div>
    </div>
</div>
<script>

    function destination_wise_calls(value)
    {
        var call_str = '';

        value.forEach(function (single_array)
        {
            call_str += '<li class="media event"><div class="media-body"><b>' + single_array.customer_destination + '</b><p><strong>' + single_array.total_calls + '</strong> [<span style="color:#089378;">' + single_array.answering + '</span> / <span style="color:#e74c3c;">' + single_array.ringing + '</span>] </p></div></li>';
        });
        $('#destinationList').html(call_str);
    }

    $(document).ready(function () {



        /////////////////////////////////////////////
        ///////////////// Analytics /////////////////
        /////////////////////////////////////////////
        $.strPad = function (i, l, s) {
            var o = i.toString();
            if (!s) {
                s = '0';
            }
            while (o.length < l) {
                o = s + o;
            }
            return o;
        };

        $.doughnut = function (id, d) {
            var a = {
                type: "doughnut",
                tooltipFillColor: "rgba(51, 51, 51, 0.55)",
                data: {
                    labels: ["Answered", "Failed"],
                    datasets: [{
                            data: d,
                            backgroundColor: ["#26B99A", "#e74c3c"],
                            hoverBackgroundColor: ["#26B99A", "#e74c3c"]
                        }]
                },
                options: {
                    legend: !1,
                    responsive: !1
                }
            };

            if (id == 'outgoing_calls')
            {
                //$('.chartjs-hidden-iframe').remove();
                $('#canvasDoughnut2').remove();
                $('#div_canvasDoughnut2').html('<canvas class="canvasDoughnut" id="canvasDoughnut2" height="140" width="140"></div>');

                new Chart($('#canvasDoughnut2'), a);
            } else
            {
                $('#canvasDoughnut1').remove();
                $('#div_canvasDoughnut1').html('<canvas class="canvasDoughnut" id="canvasDoughnut1" height="140" width="140"></div>');

                new Chart($('#canvasDoughnut1'), a);
            }

            //  new Chart($('#' + id + ' .canvasDoughnut'), a)
        };

        $.graphBox = function (id, value) {
            var str = '<tbody>';
            str += '<tr><td>ASR</td><td class="fs15 fw700 text-right">' + value.asr + '</td></tr>';
            str += '<tr><td>ACD</td><td class="fs15 fw700 text-right">' + value.acd + '</td></tr>';
            str += '<tr><td>PDD</td><td class="fs15 fw700 text-right">' + value.pdd + '</td></tr>';
            str += '</tbody>';

            var info = '<tbody>';
            info += '<tr><td><p><i class="fa fa-square blue"></i>Total </p></td><td>' + value.tot_calls + '</td></tr>';
            info += '<tr><td><p><i class="fa fa-square green"></i>Answered </p></td><td>' + value.tot_answered + '</td></tr>';
            info += '<tr><td><p><i class="fa fa-square red"></i>Failed </p></td><td>' + (value.tot_calls - value.tot_answered) + '</td></tr>';
            info += '</tbody>';

            $('#' + id + ' .countries_list').html(str);
            $('#' + id + ' .tile_info').html(info);
            $('#' + id + ' .duration').html('Total Duration <b>' + value.tot_duration + '</b>');
            $.doughnut(id, [parseInt(value.tot_answered), (value.tot_calls - value.tot_answered)]);
        };

        $.barBox = function (id, value) {
            var d = [];
            for (i = 0; i < 24; i++)
                d[i] = 0;
            $.each(value, function (k, v) {
                d[parseInt(v.calltime_h)] = parseInt(v.hour_duration)
            });

            $('#' + id + ' .sparkline_two').sparkline(d, {
                type: "bar",
                height: "40",
                barWidth: 5,
                colorMap: {
                    7: "#a1a1a1"
                },
                barSpacing: 3,
                barColor: "#26B99A"
            })
        };

        $.tildBox = function (tot, ans, ring, pro) {

            str = '';
            str += '<div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count"><div class="count">' + tot + '</div><span class="count_bottom">Total Concurrent Calls</span></div>';
            str += '<div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count"><div class="count">' + ans + '</div><span class="count_bottom">Total Answered Calls</span></div>';
            str += '<div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count"><div class="count">' + ring + '</div><span class="count_bottom">Total Ringing Calls</span></div>';
            str += '<div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count"><div class="count">' + pro + '</div><span class="count_bottom">Total Progress Calls</span></div>';
            $('#analytics .tile_count').html(str);
        };
        //test12
        $.dataBox = function (id, value) {
            var str = '';
            var totalCalls = answerCalls = ringingCalls = progressCalls = 0;
            $.each(value, function (k, v) {
                totalCalls = parseInt(totalCalls) + parseInt(v.total_calls);
                answerCalls = parseInt(answerCalls) + parseInt(v.answer);
                ringingCalls = parseInt(ringingCalls) + parseInt(v.ringing);
                progressCalls = parseInt(progressCalls) + parseInt(v.progress);

                percent = parseInt((v.answer / v.total_calls) * 100);

                //str += '<li class="media event"><div class="media-body"><a class="title" href="#">'+v.name+'</a><p>'+v.ip+'</p><div class="progress progress_sm" style="width: 90%;margin: 10px 0;"><div class="progress-bar bg-green" role="progressbar" data-transitiongoal="80" style="width: '+ percent +'%;" aria-valuenow="60"></div></div><p><strong>'+v.total_calls+'</strong> ['+v.answer+' / '+(v.total_calls-v.answer)+'] </p></div></li>';

                if (id == 'carrierList')
                    str += '<li class="media event"><div class="media-body"><b>' + v.name + '</b><p>' + v.ip + '</p><p><strong>' + v.total_calls + '</strong> [<span style="color:#089378;">' + v.answer + '</span> / <span style="color:#e74c3c;">' + (v.total_calls - v.answer) + '</span>] </p></div></li>';
                else
                    str += '<li class="media event"><div class="media-body"><b>' + v.name + ' [' + v.type + ']</b><p>' + v.ip + '</p><p><strong>' + v.total_calls + '</strong> [<span style="color:#089378;">' + v.answer + '</span> / <span style="color:#e74c3c;">' + (v.total_calls - v.answer) + '</span>] </p></div></li>';


            });
            $('#' + id).html(str);
            if (id == 'carrierList')
                $.tildBox(totalCalls, answerCalls, ringingCalls, progressCalls);//test1
        };


        /**********************/
        $.show_usage = function (value) {

            // console.log(value);
            var customer_str = carrier_str = '';
            value.forEach(function (single_array) {
                //  console.log(single_array);
                if (single_array.customer_type == 'customer')
                {
                    customer_str += '<div class="col-md-3">' + single_array.currency_name + ' ' + single_array.customer_cost_total + '</div>';
                } else if (single_array.customer_type == 'carrier')
                {
                    carrier_str += '<div class="col-md-3">' + single_array.currency_name + ' ' + single_array.customer_cost_total + '</div>';

                }

            });

            customer_str = '<div class="col-md-4"><strong>Customer Usage-</strong></div>' + customer_str;
            carrier_str = '<div class="col-md-3"><strong>Carrier Usage-</strong></div>' + carrier_str;


            $('#customer_usage_id').html(customer_str);
            $('#carrier_usage_id').html(carrier_str);
        };
        /**********************/



        /*************/
        $.show_customer_call_stat = function (value) {

            // console.log(value);
            var customer_str = '';
            if (value.length > 0)
            {
                value.forEach(function (single_array) {
                    var asr = single_array.asr;
                    if (asr < 1)
                        tr_class = 'red1';
                    else if (asr < 5)
                        tr_class = 'red2';
                    else if (asr < 10)
                        tr_class = 'red3';
                    else if (asr < 20)
                        tr_class = 'red4';
                    else
                        tr_class = '';
                    customer_str += '<tr class="' + tr_class + '"><td>' + single_array.account_id + '</td><td>' + single_array.tot_calls + '</td><td>' + single_array.tot_answered + '</td><td>' + single_array.asr + '</td><td>' + single_array.acd + '</td><td>' + single_array.tot_cost + '</td></tr>';

                });
            } else
            {
                customer_str += '<tr><td colspan="6" class="text-center">No records found</td></tr>';
            }

            $('#id_customer_call_stat').html(customer_str);
        };

        ///
        $.show_carrier_call_stat = function (value) {

            //console.log(value);
            var carrier_str = '';
            if (value.length > 0)
            {
                value.forEach(function (single_array) {
                    var asr = single_array.asr;
                    if (asr < 1)
                        tr_class = 'red1';
                    else if (asr < 5)
                        tr_class = 'red2';
                    else if (asr < 10)
                        tr_class = 'red3';
                    else if (asr < 20)
                        tr_class = 'red4';
                    else
                        tr_class = '';

                    carrier_str += '<tr class="' + tr_class + '"><td>' + single_array.carrier_id + '</td><td>' + single_array.tot_calls + '</td><td>' + single_array.tot_answered + '</td><td>' + single_array.asr + '</td><td>' + single_array.acd + '</td><td>' + single_array.tot_cost + '</td></tr>';

                });
            } else
            {
                carrier_str += '<tr><td colspan="6" class="text-center">No records found</td></tr>';
            }

            $('#id_carrier_call_stat').html(carrier_str);
        };
        /************/




        var is_loading = false;
        var is_loading2 = false;
        var is_loading3 = false;

        var is_loading_counter = 0;
        var is_loading2_counter = 0;
        var is_loading3_counter = 0;


        $.loadAnalytics = function () {
            //alert("start 1");
            is_loading = true;

            var incoming_calls = 'N';
            var outgoing_calls = 'N';
            var incoming_duration = 'N';
            var outgoing_duration = 'N';
            var gateway_calls = 'Y';
            var customer_calls = 'Y';
            var show_usage = 'N';

            var customer_call_stat = 'N';
            var carrier_call_stat = 'N';

            var livecalls_destination = 'Y';


            var get_string = incoming_calls + '/' + outgoing_calls + '/' + incoming_duration + '/' + outgoing_duration + '/' + gateway_calls + '/' + customer_calls + '/' + show_usage + '/' + customer_call_stat + '/' + carrier_call_stat + '/' + livecalls_destination;


            var target_url = BASE_URL + "reports/monin_data/" + get_string;
            //console.log(target_url);
            $.get(target_url, function (data, status) {//alert("start 2");

                // console.log(data);
                $.each(data, function (key, value) {
                    // console.log(key);
                    // console.log(value);
                    if (key == 'incoming_calls') {
                        $.graphBox('incoming_calls', value);
                    } else if (key == 'outgoing_calls') {
                        $.graphBox('outgoing_calls', value);
                    } else if (key == 'incoming_duration') {
                        $.barBox('incoming_calls', value);
                    } else if (key == 'outgoing_duration') {
                        $.barBox('outgoing_calls', value);
                    } else if (key == 'gateway_calls') {
                        $.dataBox('carrierList', value);
                    } else if (key == 'customer_calls') {
                        $.dataBox('customerList', value);
                    } else if (key == 'usage_data') {
                        $.show_usage(value);//usage_data
                    } else if (key == 'livecalls_destination') {
                        console.log('livecalls_destination');
                        console.log(value);
                        destination_wise_calls(value);
                    }
                });
                $('.loading').html('');
                is_loading = false;
                console.log('loadAnalytics() success\n');

            });
        };




        $.loadAnalytics2 = function () {
            //alert("start 1");
            is_loading2 = true;

            var incoming_calls = 'Y';
            var outgoing_calls = 'Y';
            var incoming_duration = 'Y';
            var outgoing_duration = 'Y';
            var gateway_calls = 'N';
            var customer_calls = 'N';
            var show_usage = 'Y';


            var get_string = incoming_calls + '/' + outgoing_calls + '/' + incoming_duration + '/' + outgoing_duration + '/' + gateway_calls + '/' + customer_calls + '/' + show_usage;


            var target_url = BASE_URL + "reports/monin_data/" + get_string;
            //console.log(target_url);
            $.get(target_url, function (data, status) {//alert("start 2");

                // console.log(data);
                $.each(data, function (key, value) {
                    // console.log(key);
                    // console.log(value);
                    if (key == 'incoming_calls') {
                        $.graphBox('incoming_calls', value);
                    } else if (key == 'outgoing_calls') {
                        $.graphBox('outgoing_calls', value);
                    } else if (key == 'incoming_duration') {
                        $.barBox('incoming_calls', value);
                    } else if (key == 'outgoing_duration') {
                        $.barBox('outgoing_calls', value);
                    } else if (key == 'gateway_calls') {
                        $.dataBox('carrierList', value);
                    } else if (key == 'customer_calls') {
                        $.dataBox('customerList', value);
                    } else if (key == 'usage_data') {
                        $.show_usage(value);
                    }
                });
                $('.loading').html('');
                is_loading2 = false;
                console.log('loadAnalytics2() success\n');

            });
        };


        $.loadAnalytics3 = function () {
            //alert("start 1");
            is_loading = true;

            var incoming_calls = 'N';
            var outgoing_calls = 'N';
            var incoming_duration = 'N';
            var outgoing_duration = 'N';
            var gateway_calls = 'N';
            var customer_calls = 'N';
            var show_usage = 'N';

            var customer_call_stat = 'Y';
            var carrier_call_stat = 'Y';


            var get_string = incoming_calls + '/' + outgoing_calls + '/' + incoming_duration + '/' + outgoing_duration + '/' + gateway_calls + '/' + customer_calls + '/' + show_usage + '/' + customer_call_stat + '/' + carrier_call_stat;


            var target_url = BASE_URL + "reports/monin_data/" + get_string;
            //console.log(target_url);
            $.get(target_url, function (data, status) {//alert("start 2");

                //  console.log(data);
                $.each(data, function (key, value) {

                    if (key == 'customer_call_stat') {
                        $.show_customer_call_stat(value);
                    } else if (key == 'carrier_call_stat') {
                        $.show_carrier_call_stat(value);
                    }
                });
                $('.loading').html('');
                is_loading3 = false;

                console.log('loadAnalytics3() success\n');
            });
        };

        $(document).ready(function () {
            $.loadAnalytics();
            $.loadAnalytics2();
            setTimeout($.loadAnalytics3(), 10000);

            setInterval(function () {
                console.log('loadAnalytics() approched\n');
                is_loading_counter++;
                if (is_loading_counter % 4 == 0)
                {
                    is_loading = false;
                    console.log('loadAnalytics() reset\n');
                }

                if (is_loading === false)
                {
                    console.log('loadAnalytics() called\n');
                    is_loading_counter == 0;
                    $.loadAnalytics();
                }
            }, 6000);

            setInterval(function () {
                console.log('loadAnalytics2() approched');
                is_loading2_counter++;
                if (is_loading2_counter % 4 == 0)
                {
                    is_loading2 = false;
                    console.log('loadAnalytics2() reset\n');
                }

                if (is_loading2 === false)
                {
                    console.log('loadAnalytics2() called\n');
                    is_loading2_counter = 0;
                    $.loadAnalytics2();
                }
            }, 40000);


            setInterval(function () {
                console.log('loadAnalytics3() approched');
                is_loading3_counter++;
                if (is_loading3_counter % 4 == 0)
                {
                    is_loading3 = false;
                    console.log('loadAnalytics3() reset\n');
                }

                if (is_loading3 === false)
                {
                    console.log('loadAnalytics3() called\n');
                    is_loading3_counter = 0;
                    $.loadAnalytics3();
                }
            }, 120000);


        });



        $("#customerList, #carrierList, #destinationList").niceScroll({cursorborder: "", cursorcolor: "#34495E", boxzoom: false});

    });
</script>