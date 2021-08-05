<!-- Chart.js -->
<script src="<?php echo base_url() ?>theme/vendors/Chart.js/dist/Chart.min.js"></script>
<!-- Nice Scroll -->
<script src="<?php echo base_url() ?>theme/vendors/jquery.nicescroll-master/dist/jquery.nicescroll.min.js"></script>
<!-- jQuery Sparklines -->
<script src="<?php echo base_url() ?>theme/vendors/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
<link href="<?php echo base_url() ?>theme/default/css/monin.css" rel="stylesheet">     
<div class="clearfix"></div>   

<div id="analytics" class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_content">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="row" style="margin:10px 0px 10px 0px;border:5px solid #32326A;" >
                    <div class="col-md-6 col-sm-6 text-center" style="" id="id_div_calls_count">
                        <div class="col-md-3" style="color:#ffc107;"><p class="h2_count">0 </p><p class="h2_title">Calls</p></div>
                        <div class="col-md-3" style="color:#28a745;"><p class="h2_count">0 </p><p class="h2_title">Answered</p></div>
                        <div class="col-md-3" style="color:#17a2b8;"><p class="h2_count">0 </p><p class="h2_title">Ringing</p></div>
                        <div class="col-md-3" style="color:#dc3545;"><p class="h2_count">0 </p><p class="h2_title">Progress</p></div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <!-- graph-->
                        <div class="col-md-6 col-sm-6" id="incoming_calls_bar_chart">
                            <span class="sparkline_two"  >
                                <canvas style="display: inline-block; width: 196px; height: 50px; " ></canvas>
                            </span>
                            <div class="div_duration">Carrier Minute(s)<span class="duration"><b>0</b></span></div> 
                            <div class="div_asd"><p>ASR: &nbsp;&nbsp;ACD:&nbsp;&nbsp;PDD</p></div>
                        </div>

                        <div class="col-md-6 col-sm-6" id="outgoing_calls_bar_chart">
                            <span class="sparkline_two" >
                                <canvas style="display: inline-block;  width: 196px; height: 50px;" ></canvas>
                            </span>
                            <div class="div_duration">Customer Minute(s)<span class="duration"><b>0</b></span></div>  
                            <div class="div_asd"><p>ASR: &nbsp;&nbsp;ACD:&nbsp;&nbsp;PDD</p></div>
                        </div>
                    </div>
                </div>


                <div class="row " style="margin:10px 0px 10px 0px;" >
                    <table class="table bg-info table-condensed text-light text-center" id="id_div_usage_row"></table>
                </div>

                <div class="row " style="margin:1px 0px 1px 0px;" >
                    <div class="col-md-6"  id="id_incoming_calls">
                        <table class="" style="width:100%">
                            <tr><td colspan="2" align="center"><h4>Customer Traffic</h4></td></tr>
                            <tr>
                                <td class="text-center" id="id_div_canvasDoughnut_in" width="55%">
                                    <span class="loading"><img src="<?php echo base_url(); ?>theme/default/images/loading.gif"></span>
                                    <canvas class="canvasDoughnut" id="id_canvasDoughnut_in" height="100" width="100"></canvas>
                                </td>
                                <td>
                                    <table class="tile_info">
                                        <tr><td><p><i class="fa fa-square grey"></i>Total </p></td><td>0</td></tr>
                                        <tr><td><p><i class="fa fa-square grey"></i>Answered </p></td><td>0</td></tr>
                                        <tr><td><p><i class="fa fa-square grey"></i>Failed </p></td><td>0</td></tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6"  id="id_outgoing_calls">
                        <table class="" style="width:100%">
                            <tr><td colspan="2" align="center"><h4>Carrier Traffic</h4></td></tr>
                            <tr>
                                <td class="text-center" id="id_div_canvasDoughnut_out" width="55%">
                                    <span class="loading"><img src="<?php echo base_url(); ?>theme/default/images/loading.gif"></span>
                                    <canvas class="canvasDoughnut" id="id_canvasDoughnut_out" height="100" width="100"></canvas>
                                </td>
                                <td>
                                    <table class="tile_info">
                                        <tr><td><p><i class="fa fa-square grey"></i>Total </p></td><td>0</td></tr>
                                        <tr><td><p><i class="fa fa-square grey"></i>Answered </p></td><td>0</td></tr>
                                        <tr><td><p><i class="fa fa-square grey"></i>Failed </p></td><td>0</td></tr>
                                    </table>
                                </td>

                            </tr>
                        </table>
                    </div>
                </div>	

                <div class="col-md-12 col-sm-12 col-xs-12">

                    <div class="row " style="padding:10px 0px 10px 0px;" >
                        <div class=" col-md-4 col-sm-4 bg-info text-light">
                            <div class=" text-center box_title"><strong>Customer </strong></div>
                            <div class="x_content fixed_height_box_inner text-dark" style="background-color:white;">  


                                <ul class="list-unstyled top_profiles scroll-view" id="id_customerList">
                                    <li class="media event">
                                        <div class="media-body">
                                            <a class="title" href="#">CUSTOMER [TYPE]</a>
                                            <p>IPADDRESS</p>
                                            <p style="color:blue;"><strong>TOTAL</strong> [ANSWER / REJECT] </p>
                                        </div>
                                    </li>
                                </ul>
                                <div class="clearfix"></div>


                            </div>
                        </div>		




                        <div class=" col-md-4 col-sm-4 bg-warning text-dark">
                            <div class=" text-center box_title"><strong>Carrier </strong></div>
                            <div class="x_content fixed_height_box_inner text-dark" style="background-color:white;">  

                                <ul class="list-unstyled top_profiles scroll-view" id="id_carrierList">
                                    <li class="media event">
                                        <div class="media-body">
                                            <a class="title" href="#">CARRIER [TYPE]</a>
                                            <p>IPADDRESS</p>
                                            <p style="color:blue;"><strong>TOTAL</strong> [ANSWER / REJECT] </p>
                                        </div>
                                    </li>
                                </ul>
                                <div class="clearfix"></div>		
                            </div>
                        </div>		




                        <div class=" col-md-4 col-sm-4 bg-primary">
                            <div class=" text-center box_title"><strong>Destination </strong></div>
                            <div class="x_content fixed_height_box_inner text-dark" style="background-color:white;">  

                                <ul class="list-unstyled top_profiles scroll-view" id="id_destinationList">
                                    <li class="media event"><div class="media-body"><b>DESTINATION</b><p><strong>TOTAL CALLS</strong> [<span style="color:#089378;">ANSWERING</span> / <span style="color:#e74c3c;">RINGING</span>] </p></div></li>
                                </ul>

                                <div class="clearfix"></div>
                            </div>
                        </div>						



                    </div>

                </div>
            </div>
        </div>
        <div id="analytics" class="col-md-12 col-sm-12 col-xs-12 ">
            <!--        <div class="x_panel">
                        <div class="x_content">-->

            <div class="clearfix"></div>
            <div class="col-md-6 col-sm-12 col-xs-12">                        
                <table class="table  table-bordered" >                                
                    <thead>
                        <tr class="headings">
                            <th colspan="6" class="text-center">Customer (Last One Hour)</th>
                        </tr>
                        <tr class="headings">
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
            <div class="col-md-6 col-sm-12 col-xs-12">
                <table class="table table-bordered" >                    
                    <thead>
                        <tr class="headings">
                            <th colspan="6" class="text-center">Carrier (Last One Hour)</th>
                        </tr>                    
                        <tr class="headings">
                            <th>Carrier</th>
                            <th>Total</th>
                            <th>Ans</th>
                            <th>ASR</th>
                            <th>ACD</th>
                            <th>Cost</th>
                        </tr>
                    </thead>                    
                    <tbody id="id_carrier_call_stat">
                        <tr class="text-center">
                            <td colspan="6"><img src="<?php echo base_url(); ?>theme/default/images/loading.gif"></td>
                        </tr>                                                        
                    </tbody>
                </table>

            </div>










        </div>
        <!--</div>-->
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

            ////
            var id_div = 'id_destinationList';
            $('#' + id_div).html(call_str);
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
                    type: "pie",
                    tooltipFillColor: "rgba(51, 51, 51, 0.55)",
                    data: {
                        labels: ["Answered", "Failed"],
                        datasets: [{
                                data: d,
                                backgroundColor: ["#28a745", "#dc3545"],
                                hoverBackgroundColor: ["#28a745", "#dc3545"]
                            }]
                    },
                    options: {
                        legend: !1,
                        responsive: !1,

                    }
                };

                if (id == 'outgoing_calls')
                {
                    $('#id_canvasDoughnut_out').remove();
                    $('#id_div_canvasDoughnut_out').html('<canvas class="canvasDoughnut" id="id_canvasDoughnut_out" height="140" width="140"></div>');
                    new Chart($('#id_canvasDoughnut_out'), a);
                } else
                {
                    $('#id_canvasDoughnut_in').remove();
                    $('#id_div_canvasDoughnut_in').html('<canvas class="canvasDoughnut" id="id_canvasDoughnut_in" height="140" width="140"></div>');
                    new Chart($('#id_canvasDoughnut_in'), a);
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

                //  $('#' + id + ' .countries_list').html(str);
                //  $('#' + id + ' .tile_info').html(info);
                //  $('#' + id + ' .duration').html('Total Duration <b>' + value.tot_duration + '</b>');
                $.doughnut(id, [parseInt(value.tot_answered), (value.tot_calls - value.tot_answered)]);

                ////////////
                var asd_info = '<p>ASR: ' + value.asr + '&nbsp;&nbsp;ACD:' + value.acd + '&nbsp;&nbsp;PDD: ' + value.pdd + '</p>';

                var id_div = id + '_bar_chart';
                $('#' + id_div + ' .duration').html(' <b>' + value.tot_duration + '</b>');
                if (id == 'outgoing_calls')
                {
                    $('#id_outgoing_calls .tile_info').html(info);
                    $('#incoming_calls_bar_chart .div_asd').html(asd_info);
                } else
                {
                    $('#id_incoming_calls .tile_info').html(info);
                    $('#outgoing_calls_bar_chart .div_asd').html(asd_info);
                }
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
                });

                //	var new_id = incoming_calls_bar_chart
                var id_div = id + '_bar_chart';
                $('#' + id_div + ' .sparkline_two').sparkline(d, {
                    type: "bar",
                    height: "50",
                    barWidth: 5,
                    colorMap: {
                        7: "#a1a1a1"
                    },
                    barSpacing: 3,
                    barColor: "#ffc107"
                });



            };

            $.tildBox = function (tot, ans, ring, pro) {


                str = '';
                str += '<div class="col-md-3" style="color:#ffc107;"><p class="h2_count">' + tot + '</p><p class="h2_title">Calls</p></div>';
                str += '<div class="col-md-3" style="color:#28a745;"><p class="h2_count">' + ans + '</p><p class="h2_title">Answered</p></div>';
                str += ' <div class="col-md-3" style="color:#17a2b8;"><p class="h2_count">' + ring + '</p><p class="h2_title">Ringing</p></div>';
                str += '<div class="col-md-3" style="color:#dc3545;"><p class="h2_count">' + pro + '</p><p class="h2_title">Progress</p></div>';

                $('#id_div_calls_count').html(str);



            };
            //test12
            $.dataBox = function (id, value) {
                var str = str_new = '';
                var totalCalls = answerCalls = ringingCalls = progressCalls = 0;
                $.each(value, function (k, v) {
                    totalCalls = parseInt(totalCalls) + parseInt(v.total_calls);
                    answerCalls = parseInt(answerCalls) + parseInt(v.answer);
                    ringingCalls = parseInt(ringingCalls) + parseInt(v.ringing);
                    progressCalls = parseInt(progressCalls) + parseInt(v.progress);

                    percent = parseInt((v.answer / v.total_calls) * 100);

                    //str += '<li class="media event"><div class="media-body"><a class="title" href="#">'+v.name+'</a><p>'+v.ip+'</p><div class="progress progress_sm" style="width: 90%;margin: 10px 0;"><div class="progress-bar bg-green" role="progressbar" data-transitiongoal="80" style="width: '+ percent +'%;" aria-valuenow="60"></div></div><p><strong>'+v.total_calls+'</strong> ['+v.answer+' / '+(v.total_calls-v.answer)+'] </p></div></li>';



                    if (id == 'carrierList')
                    {
                        str_new += '<li class="media event"><div class="media-body">' +
                                '<b>' + v.name + '</b>' +
                                '<p class="col-md-12"><span class="text-left col-md-6">' + v.ip + '</span>' +
                                ' <span class="text-right col-md-6"> <strong>' + v.total_calls + '</strong> [<span style="color:#089378;">' + v.answer + '</span> / <span style="color:#e74c3c;">' + (v.total_calls - v.answer) + '</span>]</span></p>' +
                                '</div></li>';
                    } else {
                        str_new += '<li class="media event"><div class="media-body">' +
                                '<b>' + v.name + ' [' + v.type + ']</b>' +
                                '<p class="col-md-12"><span class="text-left col-md-6">' + v.ip + '</span>' +
                                ' <span class="text-right col-md-6"> <strong>' + v.total_calls + '</strong> [<span style="color:#089378;">' + v.answer + '</span> / <span style="color:#e74c3c;">' + (v.total_calls - v.answer) + '</span>]</span></p>' +
                                '</div></li>';
                    }


                });
                //$('#' + id).html(str);
                if (id == 'carrierList')
                    $.tildBox(totalCalls, answerCalls, ringingCalls, progressCalls);//test1
                //console.log(id);	
                ////////////
                id_div = 'id_' + id;
                $('#' + id_div).html(str_new);
            };


            /**********************/
            $.show_usage = function (value) {
                //console.log('inside test')
                //   console.log(value);
                var customer_usage = [];
                var carrier_usage = [];

                var customer_str = carrier_str = '';
                value.forEach(function (single_array) {
                    //console.log(single_array);
                    var currency_id = single_array.currency_id;
                    //  console.log(single_array);                 console.log('C->'+currency_id);
                    if (single_array.customer_type == 'customer')
                    {
                        customer_str += '<div class="col-md-3">' + single_array.currency_name + ' ' + single_array.customer_cost_total + '</div>';

                        customer_usage[currency_id] = single_array.customer_cost_total;
                    } else if (single_array.customer_type == 'carrier')
                    {
                        carrier_str += '<div class="col-md-3">' + single_array.currency_name + ' ' + single_array.customer_cost_total + '</div>';
                        carrier_usage[currency_id] = single_array.customer_cost_total;
                    }



                });
                //console.log('customer_usage');console.log(customer_usage);
                var usage_str = '';
                usage_str = '<tr ><th>&nbsp;</th><th class="text-center">USD</th><th class="text-center">GBP</th><th class="text-center">INR</th><th class="text-center">S USD</th><th class="text-center">EURO</th></tr>';

                usage_str += '<tr><th class="text-center"><strong>BUY</strong></th>';
                if (carrier_usage[1] !== undefined)
                    usage_str += '<td>' + carrier_usage[1] + '</td>';
                else
                    usage_str += '<td>0.00</td>';
                if (carrier_usage[2] !== undefined)
                    usage_str += '<td>' + carrier_usage[2] + '</td>';
                else
                    usage_str += '<td>0.00</td>';
                if (carrier_usage[3] !== undefined)
                    usage_str += '<td>' + carrier_usage[3] + '</td>';
                else
                    usage_str += '<td>0.00</td>';
                if (carrier_usage[4] !== undefined)
                    usage_str += '<td>' + carrier_usage[4] + '</td>';
                else
                    usage_str += '<td>0.00</td>';
                if (carrier_usage[5] !== undefined)
                    usage_str += '<td>' + carrier_usage[5] + '</td>';
                else
                    usage_str += '<td>0.00</td>';

                usage_str += '</tr>';

                usage_str += '<tr><th class="text-center"><strong>SELL</strong></th>';
                if (customer_usage[1] !== undefined)
                    usage_str += '<td>' + customer_usage[1] + '</td>';
                else
                    usage_str += '<td>0.00</td>';
                if (customer_usage[2] !== undefined)
                    usage_str += '<td>' + customer_usage[2] + '</td>';
                else
                    usage_str += '<td>0.00</td>';
                if (customer_usage[3] !== undefined)
                    usage_str += '<td>' + customer_usage[3] + '</td>';
                else
                    usage_str += '<td>0.00</td>';
                if (customer_usage[4] !== undefined)
                    usage_str += '<td>' + customer_usage[4] + '</td>';
                else
                    usage_str += '<td>0.00</td>';

                if (customer_usage[5] !== undefined)
                    usage_str += '<td>' + customer_usage[5] + '</td>';
                else
                    usage_str += '<td>0.00</td>';
                usage_str += '</tr>';




                $('#id_div_usage_row').html(usage_str);




                //customer_str ='<div class="col-md-4"><strong>Customer Usage-</strong></div>'+customer_str;			
                //carrier_str ='<div class="col-md-3"><strong>Carrier Usage-</strong></div>'+carrier_str;
                //$('#customer_usage_id').html(customer_str);
                // $('#carrier_usage_id').html(carrier_str);
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
                console.log(target_url);
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
                            //	console.log('livecalls_destination');
                            //	console.log(value);
                            destination_wise_calls(value);
                        }
                    });
                    $('.loading').html('');
                    is_loading = false;
                    //console.log('loadAnalytics() success\n');

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
                console.log(target_url);
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
                            console.log(key);
                            console.log(value);
                        }
                    });
                    $('.loading').html('');
                    is_loading2 = false;
                    //console.log('loadAnalytics2() success\n');

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
                console.log(target_url);
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

                    //console.log('loadAnalytics3() success\n');				
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



            // $("#customerList, #carrierList, #destinationList").niceScroll({cursorborder: "", cursorcolor: "#34495E", boxzoom: false});
            $("#id_customerList, #id_carrierList, #id_destinationList").niceScroll({cursorborder: "", cursorcolor: "#dc3545", boxzoom: false});

        });
    </script>