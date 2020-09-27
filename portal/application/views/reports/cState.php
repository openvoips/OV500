<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0.3
// License https://www.gnu.org/licenses/agpl-3.0.html
//
//
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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
<script>
    var randomColorFactor = function () {
        return Math.round(Math.random() * 255);
    };
    var randomColor = function (opacity) {
        return 'rgba(' + randomColorFactor() + ',' + randomColorFactor() + ',' + randomColorFactor() + ',' + (opacity || '.3') + ')';
    };
</script>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php

function randomColorFactor() {
    $num = rand(1, 255);
    return $num;
}

function randomColor($opacity = '0.7') {
    $str = 'rgba(' . randomColorFactor() . ',' . randomColorFactor() . ',' . randomColorFactor() . ',' . $opacity . ')';
    return $str;
}

//echo '<pre>';
//print_r($report_data['result']);
//echo '</pre>';


$sipcode_meaningfull_array = array(
    'connected' => array('Connected', '#71B37C'),
    'others' => array('Others', '#2A3F54'),
    '401' => array('Unauthorised Calls', '#E14D57', 'Please check your IP is whitelisted / your credentials are correct'),
    '402' => array('Balance Issue', '#9AD9EA', 'Add funds to your account'),
    '487' => array('Caller Hangup', '#990000', 'Request has terminated'),
    '0' => array('Caller Hangup', '#990000'),
    '200' => array('Caller Hangup', '#990000'),
    '503' => array('Service Unavailable', '#EC932F', 'Check your channels and Calls Per Second limits. Contact NOC if required'),
    '486' => array('Busy', '#5290E9', 'Try again later'),
    '600' => array('Busy', '#5290E9', 'Try again later'),
    '603' => array('Declined', '#5290E9', 'Your CLI may be blocked on the terminating network'),
    '404' => array('Wrong Number', '#9900CC', 'Check your calling data, the number is disconnected'),
        )
?>
<div class="">
    <div class="clearfix"></div>   
    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="clearfix"></div>
        <div class="x_content">


            <div class="col-md-12 col-sm-12 col-xs-12 x_panel">


                <form class="block-content form-horizontal" id="search_form" name="search_form" method="post" data-parsley-validate action="">
                    <input type="hidden" name="search_action" value="search" />

                    <div class="form-group">
<?php if ($account_id == get_logged_account_id()) { ?>					
                            <div class="col-md-6 col-sm-3 col-xs-12"><h2>Your Calls</h2></div>
                        <?php } else { ?>

                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID </label>
                            <div class="col-md-3 col-sm-6 col-xs-12">                	
                                <input type="text" name="account_id" id="account_id" value="<?php echo $account_id; ?>" class="form-control"  data-parsley-required="" tabindex="<?php echo $tab_index++; ?>" >       
                            </div>
<?php } ?> 

                        <label class="control-label col-md-2 col-sm-3 col-xs-12">IP</label>
                        <div class="col-md-3 col-sm-6 col-xs-12">                	
                            <input type="text" name="src_ipaddress" id="src_ipaddress" value="<?php echo $src_ipaddress; ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">       
                        </div>




                    </div>
                    <div class="form-group">   
                        <label class="control-label col-md-1 col-sm-3 col-xs-12">Date </label>
                        <div class="col-md-4 col-sm-9 col-xs-12">
                            <input type="text" name="report_time" id="report_time" class="form-control" value="<?php if (isset($_SESSION['search_data']['s_time'])) echo $_SESSION['search_data']['s_time']; ?>" readonly="readonly" />
                        </div> 



                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Destination</label>
                        <div class="col-md-3 col-sm-6 col-xs-12">                	
                            <input type="text" name="prefix_name" id="prefix_name" value="<?php echo $prefix_name; ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">       
                        </div>











                        <div class="searchBar ">      
                            <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                        </div>                        

                    </div>


                </form>                 
<?php
if ($account_id != '') {
    if (count($report_data['result']) == 0) {
        echo '<div class="text-center"><strong>No Call Record Found</strong></div>';
    }
}
?>

            </div>


            <?php
            if (count($report_data['result']) > 0) {
                ?>
                <div class="row">

                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Call Analysis</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>                          
                                </ul>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content">                            
                                <canvas id="chart-area"  />                          
                            </div>

                        </div>         
                    </div>




                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Key Metrics</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>                          
                                </ul>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content">   

                                <ul class="to_do hide" id="key_metrics">                        
                                </ul>  

                            </div>

                        </div>         
                    </div>
                </div> 
                <div class="row">           

                    <div class="col-md-12 col-sm-6 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Call Outcome History</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                                </ul>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content">                      	

                                <div id="canvas-holder2"  >
                                    <canvas id="canvas"></canvas>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="clearfix"></div>         

                </div>  
















                <div class="row">           

                    <div class="col-md-12 col-sm-6 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Failed Call Response Explanations</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                                </ul>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content">                      	

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr class="headings">
                                                <th class="column-title">Explanation</th>
                                                <th class="column-title">Resolution</th>		
                                            </tr>
                                        </thead>		
                                        <tbody>
                                            <?php
                                            $response_array = array();
                                            foreach ($sipcode_meaningfull_array as $sipcode_meaningfull_array_single) {
                                                if (!isset($sipcode_meaningfull_array_single[2]) || $sipcode_meaningfull_array_single[2] == '')
                                                    continue;
                                                if (in_array($sipcode_meaningfull_array_single[2], $response_array))
                                                    continue;
                                                echo '<tr>
                                    <td>' . $sipcode_meaningfull_array_single[0] . '</td>
                                    <td>' . $sipcode_meaningfull_array_single[2] . '</td>		
                                </tr>';
                                                $response_array[] = $sipcode_meaningfull_array_single[2];
                                            }
                                            ?>
                                        </tbody>
                                    </table>  
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="clearfix"></div>         

                </div>    









                <?php
            }//if(count($report_data['result'])>0)
            ?>	       
            <div class="clearfix"></div>
        </div>



    </div>
    <div class="clearfix"></div>
</div> 
<script>
    /*define array for labels and data*/
    var pie_data_array = new Array();
    var pie_label_array = new Array();
    var pie_color_array = new Array();
</script>     
<?php
//prepare pie code data
$pie_data_array = $all_sipcode_array = array();
$pie_answeredcalls = $pie_unansweredcalls = $pie_totalcalls = $pie_bill_duration = $pie_bill_duration_average = 0;
$total_call_cost = 0;
if (count($report_data['result']) > 0) {
    //answered would be at first
    $pie_data_array['Connected'] = array();
    foreach ($report_data['result'] as $report_single_array) {
        $sipcode = $report_single_array['sipcode'];
        if ($sipcode == '')//|| $sipcode=='0'
            $sipcode_group = 'others';
        elseif (!$sipcode_meaningfull_array[$sipcode])
            $sipcode_group = 'others';
        else
            $sipcode_group = $sipcode;

        $group_name = $sipcode_meaningfull_array[$sipcode_group][0];
        $group_color = $sipcode_meaningfull_array[$sipcode_group][1];

        if ($report_single_array['unansweredcalls'] > 0) {
            if (isset($pie_data_array[$group_name])) {
                $pie_data_array[$group_name]['total'] += $report_single_array['unansweredcalls'];
            } else {
                $pie_data_array[$group_name] = array('label' => $group_name, 'color' => $group_color, 'total' => $report_single_array['unansweredcalls']);
            }

            if (!isset($all_sipcode_array[$group_name]))
                $all_sipcode_array[$group_name] = $group_name;
        }
        $pie_answeredcalls += $report_single_array['answeredcalls'];
        $pie_totalcalls += $report_single_array['totalcalls'];
        $pie_bill_duration += $report_single_array['bill_duration'];
        $pie_unansweredcalls += $report_single_array['unansweredcalls'];

        $total_call_cost += $report_single_array['account_cost'];
    }
    $total_call_cost = round($total_call_cost, 2);
    $pie_answeredcalls_percentage = round(($pie_answeredcalls * 100) / $pie_totalcalls, 1);
    $pie_bill_duration_average = round(($pie_bill_duration) / $pie_answeredcalls, 1);

    if (isset($pie_data_array['Others'])) {
        ////Others would be at last
        $array_temp = $pie_data_array['Others'];
        unset($pie_data_array['Others']);
        $pie_data_array['Others'] = $array_temp;

        $all_sipcode_array['Others'] = 'Others';
    }

    if (isset($pie_data_array['Dont Display'])) {//dont display this group
        unset($pie_data_array['Dont Display']);
    }
    //put answered call data
    if ($pie_answeredcalls > 0) {
        $sipcode_group = 'connected';
        $group_name = $sipcode_meaningfull_array[$sipcode_group][0];
        $group_color = $sipcode_meaningfull_array[$sipcode_group][1];
        $pie_data_array[$group_name] = array('label' => $group_name, 'color' => $group_color, 'total' => $pie_answeredcalls);
    } else {
        unset($pie_data_array['Connected']);
    }


    $i = 0;
    foreach ($pie_data_array as $pie_data_array_single) {
        $label_display = $label = $pie_data_array_single['label'];
        $color = $pie_data_array_single['color'];
        ?>
        <script>
            pie_label_array["<?php echo $i; ?>"] = "<?php echo $label_display; ?>";
            pie_data_array["<?php echo $i; ?>"] =<?php echo $pie_data_array_single['total']; ?>;
            pie_color_array["<?php echo $i; ?>"] = "<?php echo $color; ?>";

        </script>
        <?php
        $i++;
    }
    ?>
    <?php
    //print_r($all_sipcode_array);
    //prepare bar code data
    $bar_data_array = array();
    foreach ($report_data['result'] as $report_single_array) {
        $prefix_name = $report_single_array['prefix_name'];
        $sipcode = $report_single_array['sipcode'];


        if ($sipcode == '')// || $sipcode=='0'
            $sipcode = 'others';
        elseif (!$sipcode_meaningfull_array[$sipcode])
            $sipcode = 'others';
        else
            $sipcode = $sipcode;

        /////////////////
        if ($sipcode == '')//|| $sipcode=='0'
            $sipcode_group = 'others';
        elseif (!$sipcode_meaningfull_array[$sipcode])
            $sipcode_group = 'others';
        else
            $sipcode_group = $sipcode;

        $group_name = $sipcode_meaningfull_array[$sipcode_group][0];
        $group_color = $sipcode_meaningfull_array[$sipcode_group][1];

        //////////////	

        if ($prefix_name == '')
            $prefix_name = 'Others';

        foreach ($all_sipcode_array as $sipcode_temp) {
            if ($group_name == $sipcode_temp) {
                $answeredcalls = $report_single_array['answeredcalls'];
                $totalcalls = $report_single_array['totalcalls'];

                $unansweredcalls = $report_single_array['unansweredcalls'];
            } else {
                $answeredcalls = 0;
                $totalcalls = 0;
                $unansweredcalls = 0;
            }

            $bar_data_array[$sipcode_temp]['data'][$prefix_name] += $unansweredcalls;

            //put answered call data								
            if (!isset($bar_data_array['Connected']['data'][$prefix_name]) || $bar_data_array['Connected']['data'][$prefix_name] == 0)
                $bar_data_array['Connected']['data'][$prefix_name] = $answeredcalls;
            else
                $bar_data_array['Connected']['data'][$prefix_name] += $answeredcalls;
        }
        $bar_data_array[$group_name]['color'] = $group_color;

        ////
    }

    ////Others would be at last
    if (isset($bar_data_array['others'])) {
        $group_color = $sipcode_meaningfull_array['others'][1];
        $bar_data_array['others']['color'] = $group_color;

        ////Others would be at last
        $array_temp = $bar_data_array['others'];
        unset($bar_data_array['others']);
        $bar_data_array['others'] = $array_temp;
    }
    ////////
    if (isset($bar_data_array['Connected'])) {
        $group_color = $sipcode_meaningfull_array['connected'][1];
        $bar_data_array['Connected']['color'] = $group_color;
    }
    ?>
    <script>
        /*define array for labels and data*/
        var bar_label_array = new Array();
        var bar_final_array = new Array();
    </script>
    <?php
    $i = 0;
    $bar_data_array_temp = current($bar_data_array);

    foreach ($bar_data_array_temp['data'] as $prefix_name => $prefix_name_value) {
        ?>
        <script>
            bar_label_array["<?php echo $i; ?>"] = "<?php echo $prefix_name; ?>";
        </script>
        <?php
        $i++;
    }

    $i = 0;
    foreach ($bar_data_array as $sipcode_name => $bar_data_array_single) {
        $label_display = $label = $sipcode_name;
        $color = $bar_data_array_single['color'];
        ?>
        <script>
            var array_temp = [];
        </script>
        <?php
        $k = 0;
        if (isset($bar_data_array_single['data'])) {
            foreach ($bar_data_array_single['data'] as $prefix_name => $prefix_name_value) {
                ?>
                <script>
                    array_temp["<?php echo $k; ?>"] =<?php echo $prefix_name_value; ?>;
                </script>
                <?php
                $k++;
            }
        }
        ?>
        <script>
            var bar_data_temp = {
                label: "<?php echo $label_display; ?>",
                backgroundColor: "<?php echo $color; ?>",
                data: array_temp
            };
            bar_final_array["<?php echo $i; ?>"] = bar_data_temp;
        </script>
        <?php
        $i++;
    }
    ?>


    <script src="<?php echo base_url() ?>theme/vendors/Chart.js/dist/Chart.min.js"></script>


    <script>

            $('#OkFilter').click(function () {

                var is_ok = $("#search_form").parsley().isValid();

                if (is_ok === true)
                {
                    $("#search_form").submit();

                } else
                {
                    $('#search_form').parsley().validate();
                }


            });
    //console.log(bar_final_array);
    //console.log(pie_label_array);
            console.log(pie_data_array);

            function pie_chart()
            {
                var config = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                data: pie_data_array,
                                backgroundColor: pie_color_array,
                            }],
                        labels: pie_label_array
                    },
                    options: {
                        responsive: true
                    }
                };
                console.log(config);
                var ctx = document.getElementById("chart-area").getContext("2d");
                window.myPie = new Chart(ctx, config);
            }


            function bar_stacked()
            {
                var ctx = document.getElementById("canvas").getContext("2d");
                window.myBar = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: bar_label_array,
                        datasets: bar_final_array
                    },
                    options: {
                        responsive: true,
                        title: {
                            display: false,
                        },
                        tooltips: {
                            mode: 'label'
                        },

                        scales: {
                            xAxes: [{
                                    stacked: true,
                                    ticks: {autoSkip: false}
                                }],
                            yAxes: [{
                                    stacked: true
                                }]
                        }
                    }
                });
            }
            ;

            function key_metrics_fun()
            {
                $str = '<li><h4 class="text-primary"><i class="fa fa-hand-o-right"></i> Total Calls: <?php echo $pie_totalcalls; ?></h4></li>' +
                        '<li><h4 class="text-primary"><i class="fa fa-hand-o-right"></i> Connected Calls: <?php echo $pie_answeredcalls; ?> (<?php echo $pie_answeredcalls_percentage; ?>%)</h4></li>' +
                        '<li><h4 class="text-primary"><i class="fa fa-hand-o-right"></i> Average Duration: <?php echo $pie_bill_duration_average; ?> Sec</h4></li>' +
                        '<li><h4 class="text-primary"><i class="fa fa-hand-o-right"></i> Total Call Cost: <?php echo $total_call_cost; ?></h4></li>';




                $('#key_metrics').html($str);
                $('#key_metrics').removeClass('hide');

            }
    </script>

    <script>
        window.onload = function () {
            pie_chart();
            bar_stacked();
            key_metrics_fun();
        };
    </script>


    <?php
}//if(count($report_data['result'])>0)
?>

<script>
    $(document).ready(function () {

        $("#report_time").daterangepicker({
            timePicker: true,
            timePickerIncrement: 5,
            locale: {
                format: "YYYY-MM-DD HH:mm:ss"
            },
            timePicker24Hour: true,
            //minDate: moment().startOf('days'),		
            //maxDate: moment().endOf('days'),
            showCustomRangeLabel: false,
            ranges: {
                'Last 15 Minute': [moment().subtract(15, 'minute'), moment()],
                'Last 30 Minute': [moment().subtract(30, 'minute'), moment()],
                'Last 1 Hour': [moment().subtract(1, 'hour'), moment()],
                'Today': [moment().startOf('days'), moment().endOf('days')],
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
                'Day Before Yesterday': [moment().subtract(2, 'days').startOf('days'), moment().subtract(2, 'days').endOf('days')],

            }
        });

        $("#report_time").val("<?php echo $time_range; ?>");
    });
</script>   
<?php
if (count($report_data['result']) > 0) {

//	echo 'Pie data ::';
//echo '<pre>';print_r($pie_data_array);echo '</pre>';
////
//echo 'Bar data ::';
//echo '<pre>';print_r($bar_data_array);echo '</pre>';
////
//echo 'query returned data ::';
//echo '<pre>';print_r($report_data['result']);echo '</pre>';
}?>