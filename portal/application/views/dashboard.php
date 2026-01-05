<?php
if (isset($user_result)) {
    $is_user_details_exists = true;

    $dp = 4;
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
                if (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
                    ?>
                    <div class="col-md-6 col-sm-6 col-xs-12">

                    </div>    
                    <div class="col-md-6 col-sm-6 col-xs-12">


                    </div>
                    <div class="clearfix"></div>
                    <?php
                } elseif (check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {
                    ?>
                    <div class="animated flipInY  col-md-12 col-sm-12 col-xs-12">  
                        <div class=" text-center col-md-12 col-sm-12 col-xs-12">  
                            <div class="text-center"><h2>Welcome To <?php echo SITE_FULL_NAME; ?>. </h2>
                            </div>



                            <p><h3>Hello <?php echo get_logged_user_name(); ?></h3></p> 
                            <p><h4>Let's get started!</h4></p>
                            <p>
                                First make the your Tariff and Rates in the system and move to configure Carrier and Routing.


                            </p>
                            <p>
                                Create the Customer or Reseller and assign the Tariff and routes.


                            </p>

                            <p> Live System Monitoring & Traffic Statistics 
                                <a class="title" href="<?php echo base_url('reports/monin'); ?>"><button type="button" class="btn btn-primary btn-lg active"><i class="fa fa-hand-o-right"></i> Monitor System</button></a> </p> 
                        </div>   </div>

                    <?php
                }
                ?>      

            </div>              
            <div class="clearfix"></div>
        </div>




        <div class="container" >
            <div class="col-xs-4 col-sm-4 col-lg-4">  
                <div class="card">
                    <!--                    <div class="header text-center">
                                            <h2><strong>CPU USAGE</strong></h2>
                                        </div>-->
                    <div class="body">
                        <div id="id_cpu" style="height:300px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xs-4 col-sm-4 col-lg-4">  
                <div class="card">

                    <div class="body">
                        <div id="id_memory" style="height:300px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xs-4 col-sm-4 col-lg-4">  
                <div class="card">

                    <div class="body">
                        <div id="id_disk" style="height:300px;"></div>
                    </div>
                </div>
            </div>
        </div>



    </div>
    <div class="clearfix"></div>
</div>      




<script type="text/javascript" src="<?php echo base_url() ?>theme/gauge/jquery-asPieProgress.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>theme/vendors/echarts-master/dist/echarts.js"></script>
<script type="text/javascript">
    function set_graph($type, $used)
    {
        var $id = '';
        var $title = '';
        var $colorplate = [];
        if ($type == 'cpu')
        {
            $id = 'id_cpu';
            $title = 'CPU Usage';

            $colorplate = ['#c23531', '#2f4554', '#61a0a8', '#d48265', '#91c7ae', '#749f83', '#ca8622', '#bda29a', '#6e7074', '#546570', '#c4ccd3'];
        } else if ($type == 'memory')
        {
            $id = 'id_memory';
            $title = 'Memory Usage';
            $colorplate = ['#91c7ae', '#749f83', '#ca8622', '#bda29a', '#c23531', '#2f4554', '#61a0a8', '#d48265', '#6e7074', '#546570', '#c4ccd3'];
        } else
        {
            $id = 'id_disk';
            $title = 'Disk Usage';
            $colorplate = ['#6e7074', '#546570', '#c4ccd3', '#c23531', '#2f4554', '#61a0a8', '#d48265', '#91c7ae', '#749f83', '#ca8622', '#bda29a'];
        }
        var myChart = echarts.init(document.getElementById($id));

        var $unused = 100 - $used;
        // Specify the configuration items and data for the chart
        var option = {
            title: {
                text: $title,
                left: 'right'
            },
            tooltip: {
                trigger: 'item'
            },
            legend: {
                orient: 'vertical',
                left: 'left'
            },
            color: $colorplate,
            series: [
                {
                    name: $title,
                    type: 'pie',
                    radius: '50%',
                    data: [
                        {value: $used, name: 'Used(' + $used + '%)'},
                        {value: $unused, name: 'Unused(' + $unused + '%)'}
                    ],
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        };


        // Display the chart using the configuration items and data just specified.
        myChart.setOption(option);
    }

</script>
<script type="text/javascript">
    $(document).ready(function () {
        // Example with grater loading time - loads longer
        $('.pie_progress_temperature,.pie_progress_cpu, .pie_progress_mem, .pie_progress_disk').asPieProgress({});

        getCpu();
        getMem();
        getDisk();
    });

    function getTemp() {
        $.ajax({
            url: BASE_URL + 'dashboard/temperature',
            success: function (response) {
                update('temperature', response);
                setTimeout(function () {
                    getTemp();
                }, 1000);
            }
        });
    }


    function getCpu() {
        $.ajax({
            url: BASE_URL + 'dashboard/cpu',
            success: function (response) {
                console.log(response);
                update('cpu', response);
                set_graph('cpu', response.percent);
                setTimeout(function () {
                    getCpu();
                }, 5000);
            }
        });
    }

    function getMem() {
        $.ajax({
            url: BASE_URL + 'dashboard/memory',
            success: function (response) {
                update('mem', response);
                set_graph('memory', response.percent);
                setTimeout(function () {
                    getMem();
                }, 7000);
            }
        });
    }

    function getDisk() {
        $.ajax({
            url: BASE_URL + 'dashboard/disk',
            success: function (response) {
                update('disk', response);
                set_graph('disk', response.percent);
                setTimeout(function () {
                    getDisk();
                }, 6000);
            }
        });
    }

    function update(name, response) {
        $('.pie_progress_' + name).asPieProgress('go', response.percent);
        $("#" + name + "Div div.title").text(response.title);
        $("#" + name + "Div pre").text(response.output.join('\n'));
    }
</script>
