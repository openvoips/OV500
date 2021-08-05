<div class="row">
    <div class="col-md-4 col-sm-4 col-xs-12" >
        <div class="x_panel">
            <div class="x_content">						
                <h4 style="margin:18px">Ticket by status</h4>
                <table class="" style="width:100%">												
                    <tr>
                        <td style="width:45%;">
                            <canvas id="canvasDoughnut" height="140" width="140" style="width: 140px; height: 140px;"></canvas>
                        </td>
                        <td>
                            <table class="tile_info">
                                <tr>
                                    <td><p><i class="fa fa-square"></i>Open</p></td>
                                    <td style="text-align: right">2</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square blue"></i>Assigned</p></td>
                                    <td style="text-align: right">5</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square green"></i>Working</p></td>
                                    <td style="text-align: right">8</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square green"></i>Waiting Confirmation</p></td>
                                    <td style="text-align: right">8</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square green"></i>Not Fixed</p></td>
                                    <td style="text-align: right">8</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square green"></i>Overdue Tickets</p></td>
                                    <td style="text-align: right">8</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square red"></i>Closed </p></td>
                                    <td style="text-align: right">10</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-4 col-xs-12" >
        <div class="x_panel" style="height: 297px;">
            <div class="x_content">						
                <h4 style="margin:18px">Satisfaction score</h4>
                <table width="140" align="center">
                    <tr>
                        <td><canvas id="collectionDoughnut0" height="140" width="140" style="margin: 5px 10px 10px 0;"></canvas></td>
                    </tr>
                </table>
                <!--
                <table class="tile_info" align="center" style="width: 200px !important;">
                        <tr>
                                <td style="width: 50% !important;"><p><i class="fa fa-square green"></i>Good</p></td>
                                <td style="width: 50% !important;"><p><i class="fa fa-square"></i>Bad</p></td>
                        </tr>
                </table>
                -->
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-4 col-xs-12" >
        <div class="x_panel">
            <div class="x_content">						
                <h4 style="margin:18px">Open tickets by group</h4>
                <table class="" style="width:100%">												
                    <tr>
                        <td style="width:45%;">
                            <canvas id="canvasDoughnut1" height="140" width="140" style="width: 140px; height: 140px;"></canvas>
                        </td>
                        <td>
                            <table class="tile_info">
                                <tr>
                                    <td><p><i class="fa fa-square"></i>Billing</p></td>
                                    <td style="text-align: right">2</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square"></i>Invoice</p></td>
                                    <td style="text-align: right">2</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square"></i>Payment</p></td>
                                    <td style="text-align: right">2</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square blue"></i>Networking & Operations</p></td>
                                    <td style="text-align: right">5</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square green"></i>R&D </p></td>
                                    <td style="text-align: right">8</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square"></i>Payment</p></td>
                                    <td style="text-align: right">2</td>
                                </tr>
                                <tr>
                                    <td><p><i class="fa fa-square green"></i>Others</p></td>
                                    <td style="text-align: right">8</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>			
    </div>

</div>
<div class="row">

    <div class="col-md-4 col-sm-4 col-xs-12" >
        <div class="x_panel">
            <div class="x_content">

            </div>
        </div>					
    </div>

    <div class="col-md-8 col-sm-8 col-xs-12" >
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12" >
                <div class="x_panel">
                    <div class="x_content">
                        <h4 style="margin:18px">tickets by group</h4>
                        <div style="height: 450px">
                            <canvas id="stack"></canvas></td>
                        </div>
                    </div>
                </div>	
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12" >

            </div>
        </div>
    </div>
</div>
<!-- Chart.js -->
<script src="https://ins.telcoportal.com/theme/vendors/Chart.js/dist/Chart.min.js"></script>
<!-- jQuery Sparklines -->
<script src="https://ins.telcoportal.com/theme/vendors/jquery-sparkline/dist/jquery.sparkline.min.js"></script>

<script>
    var config = {type: 'doughnut', height: 140, width: 140, data: {datasets: [{data: [3, 4, 5, 6, 7, 8, 9], backgroundColor: ["#26b99a", "#bdc3c7", "#9b59b6", "#bdc3c7", "#3498db", "#00ff00", "#ff0000"]}], labels: ['Open', 'Assigned', 'Working', 'Waiting Confirmation', 'Not Fixed', 'Overdue Tickets', 'Closed']}, options: {responsive: true, maintainAspectRatio: false, legend: {display: false, position: 'top', }, title: {display: false, text: 'Chart.js Doughnut Chart'}, animation: {animateScale: true, animateRotate: true}}};
    var ctx = document.getElementById('canvasDoughnut').getContext('2d');
    ctx.height = 140;
    window.myDoughnut = new Chart(ctx, config);
    var config = {type: 'doughnut', height: 140, width: 140, data: {datasets: [{data: [75, 25], backgroundColor: ["#26b99a", "#bdc3c7"]}], labels: ['Collection', 'Target']}, options: {responsive: true, maintainAspectRatio: false, legend: {display: false, position: 'top', }, title: {display: false, text: 'Chart.js Doughnut Chart'}, animation: {animateScale: true, animateRotate: true}}};
    var ctx = document.getElementById('collectionDoughnut0').getContext('2d');
    ctx.height = 140;
    window.myDoughnut = new Chart(ctx, config);
    var config = {type: 'doughnut', height: 140, width: 140, data: {datasets: [{data: [3, 4, 5, 6], backgroundColor: ["#26b99a", "#bdc3c7", "#9b59b6", "#bdc3c7"]}], labels: ['Open', 'Assigned', 'Working', 'Closed']}, options: {responsive: true, maintainAspectRatio: false, legend: {display: false, position: 'top', }, title: {display: false, text: 'Chart.js Doughnut Chart'}, animation: {animateScale: true, animateRotate: true}}};
    var ctx = document.getElementById('canvasDoughnut1').getContext('2d');
    ctx.height = 140;
    window.myDoughnut = new Chart(ctx, config);
    $(".sparkline_1").sparkline([2, 4, 3, 4, 5, 4, 2, 4, 3, 4, 5, 4, 2, 4, 3, 4, 5, 4, 2, 4, 3, 4, 5, 4, 2, 4, 3, 4, 5, 4], {type: "bar", height: "210", display: "block", barWidth: 8, colorMap: {7: "#a1a1a1"}, barSpacing: 2, barColor: "#26b99a"});


    var barChartData = {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        datasets: [{
                label: 'Open',
                backgroundColor: '#e74c3c',
                data: [12, 34, 23, 4, 56, 67]
            }, {
                label: 'Closed',
                backgroundColor: '#3498db',
                data: [4, 21, 5, 0, 45, 34]
            }, {
                label: 'Hold',
                backgroundColor: '#26b99a',
                data: [3, 12, 2, 1, 12, 23]
            }]

    };
    var ctx = document.getElementById('stack').getContext('2d');
    window.myBar = new Chart(ctx, {
        type: 'bar',
        data: barChartData,
        options: {
            title: {
                display: false,
            },
            tooltips: {
                mode: 'index',
                intersect: false
            },
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                        stacked: true,
                    }],
                yAxes: [{
                        stacked: true
                    }]
            }
        }
    });
</script>


