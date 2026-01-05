 <style type="text/css">
    .small-box{
        min-height:200px;
    }
    .small-box {
        min-height: 200px;
    }
    .small-box2 {
        min-height: 250px;
    }
    .bg-info, .bg-info > a {
        color: #fff !important;
    }
    .bg-success {
        background-color:#28a745 !important;
        color: #fff !important;
    }
    .bg-warning {
        background-color: #ffc107 !important;
        color: #fff !important;
    }
    .bg-danger {
        background-color: #dc3545 !important;
        color: #fff !important;
    }
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0, 0, 0, 0.125), 0 1px 3px rgba(0, 0, 0, 0.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }
    .bg-info {
        background-color: #133E5E !important;
        color: #fff !important;
    }

    .bg-custom {
        background-color: #fd7e14 !important;
        color: #fff !important;
    }

    .small-box > .inner {
        padding: 10px;
    }
    .small-box .icon {
        color: rgba(0, 0, 0, 0.15);
        z-index: 0;
    }

    .small-box > .small-box-footer {
        background-color: rgba(0, 0, 0, 0.1);
        color: rgba(255, 255, 255, 0.8);
        display: block;
        padding: 3px 0;
        position: relative;
        text-align: center;
        text-decoration: none;
        z-index: 10;
    }

    small-box .icon > i.fa, .small-box .icon > i.fas, .small-box .icon > i.far, .small-box .icon > i.fab, .small-box .icon > i.fal, .small-box .icon > i.fad, .small-box .icon > i.ion {
        font-size: 70px;
        top: 20px;
    }
    .small-box .icon > i {
        font-size: 90px;
        position: absolute;
        right: 15px;
        top: 15px;
        transition: -webkit-transform 0.3s linear;
        transition: transform 0.3s linear;
        transition: transform 0.3s linear, -webkit-transform 0.3s linear;
    }

    /***** */


    .small-box .icon > i.fa, .small-box .icon > i.fas, .small-box .icon > i.far, .small-box .icon > i.fab, .small-box .icon > i.fal, .small-box .icon > i.fad, .small-box .icon > i.ion {

        font-size: 70px;
        top: 20px;

    }
    .small-box .icon > i {

        font-size: 90px;
        position: absolute;
        right: 15px;
        top: 15px;
        transition: -webkit-transform 0.3s linear;
        transition: transform 0.3s linear;
        transition: transform 0.3s linear, -webkit-transform 0.3s linear;

    }


    .fa-flip-horizontal {

        -ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=0, mirror=1)";
        -webkit-transform: scaleX(-1);
        transform: scaleX(-1);

    }

    .vcenter {
        /* min-height:300px;*/
        display: flex;
        align-items: center;     /* Align the flex-items vertically */
        justify-content: center;
    }
    .table-borderless > tbody > tr > td,
    .table-borderless > tbody > tr > th,
    .table-borderless > tfoot > tr > td,
    .table-borderless > tfoot > tr > th,
    .table-borderless > thead > tr > td,
    .table-borderless > thead > tr > th {
        border: none;
    }
</style> 
<div class="">
    <div class="clearfix"></div>   
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="clearfix"></div>
        <div class="x_content">
            <div class="row">






                <div class="col-lg-4 col-md-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner" id="id_month_data">
                            <h1 class="text-center1 ">This Month</h1>
                            <h4>Calls: 0</h4>
                            <h4>Duration: 00:00:00</h4>
                        </div>
                        <div class="icon" style="color:#fff";>
                            <i class="fa fa-bar-chart-o"></i>
                        </div>
                        <?php
                        echo '<a href="' . site_url('reports/Calls') . '" class="small-box-footer">More info <i class="fa fa-arrow-circle-o-right "></i></a>';
                        ?>

                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner" id="id_today_data">
                            <h1 class="text-center1 ">Today</h1>
                            <h4>Calls: 0</h4>
                            <h4>Duration: 00:00:00</h4>
                        </div>

                        <div class="icon">
                            <i class="fa fa-line-chart"></i>
                        </div>
                        <?php
                        echo '<a href="' . site_url('reports/Calls') . '" class="small-box-footer">More info <i class="fa fa-arrow-circle-o-right "></i></a>';
                        ?>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner" id="id_active_data">

                        </div>

                        <div class="icon">
                            <i class="fa fa-calendar"></i>
                        </div>

                    </div>
                </div>

            </div>
            <div class="row">

                <div class="col-lg-4 col-md-6">
                    <!-- small box -->
                    <div class="small-box bg-custom small-box2">                        
                        <div class="inner" id="id_bundle_data">

                            <h4 class="text-center1 ">Account & Package Summary</h4>
                            <br><br>
                            <table class="table table-condensed table-borderless">
                                <tr><td><h4>Plan</h4></td><td><h4>Allowed / Used </h4></td></tr>
                            </table>

                        </div>
                        <div class="icon">
                            <i class="fa fa-phone-square"></i>
                        </div>

                        <?php
                        echo '<a href="' . site_url('reports/accountsummary') . '" class="small-box-footer">More info <i class="fa fa-arrow-circle-o-right "></i></a>';
                        ?>
                    </div>
                </div>


                <div class="col-md-4 vcenter small-box2">
                    <a href="#"><img alt="Globe logo" src="<?php echo base_url('theme/default/images/globe2.gif'); ?>" style="border-style:none" title="Globe" width="160"></a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <!-- small box -->
                    <div class="small-box bg-danger small-box2">
                        <div class="inner" id="id_balance_data">
                            <h4 class="text-center1">Finances</h4>
                            <h3>Portal Balance: 00.00</h3>
                            <h4>Credit Limit: 00.00</h4>
                            <h9>Portal Balance is not account credit!</h9>
                        </div>

                        <div class="icon">
                            <i class="fa fa-dollar"></i>
                        </div><br>
                        <?php
                        echo '<a href="' . site_url('crs/customers/statement') . '" class="small-box-footer">More info <i class="fa fa-arrow-circle-o-right "></i></a>';
                        ?>
                    </div>
                </div>

            </div>




            <div class="clearfix"></div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>      


<script>
    var count = 0;
    function load_calls(month, today, active)
    {
        var url = BASE_URL + 'dashboard/ajax_get_calls';

        url = url + '?month=' + month + '&today=' + today + '&active=' + active;
        console.log(url);
        $.ajax({
            url: url,
            type: 'post',
            success: function (response) {
                console.log(response);

                if (typeof response['month_data'] !== 'undefined')
                    $('#id_month_data').html(response['month_data']);

                if (typeof response['today_data'] !== 'undefined')
                    $('#id_today_data').html(response['today_data']);

                if (typeof response['active_data'] !== 'undefined')
                    $('#id_active_data').html(response['active_data']);

                if (typeof response['balance'] !== 'undefined')
                    $('#id_balance_data').html(response['balance']);



                if (typeof response['active_bundle'] !== 'undefined')
                    $('#id_bundle_data').html(response['active_bundle']);



                else if (count == 1)
                    load_calls('Y', 'Y', 'Y');
                console.log(count);
                count++;

            }
        });
    }

    $(document).ready(function () {
        load_calls('Y', 'Y', 'Y');
    });

</script>  