<div class="container-fluid">
    
    <div class="block-header">
        <h2>Report Summary</h2>
        <ul class="nav navbar-right panel_toolbox">
            
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card clearfix">
                <div class="body clearfix">   
                <br><br>
                    <div class="row clearfix">
                   
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="card">
                                <div class="header">
                                    <h4>Clients Profit </h4>
                                </div>
                                <div class="body" style="margin: 10px;">


                                    <form class="block-content form-horizontal " id="client_form" name="client_form" >
                                        <input type="hidden" name="search_action" value="searchcustomer" />			
                                        <div class="form-group">

                                            <div class="row">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                                                <div class="col-md-8 col-sm-9 col-xs-12">
                                                    <input type="text" name="clienttime" id="id_clienttime" class="form-control" value="" />
                                                </div>
                                                <div class="col-md-2 text-right ">
                                                    <input type="button" value="Search" name="OkFilter" id="OkFilterClientProfit" class="btn btn-primary">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="clearfix"></div>
                                    <div class="ln_solid"></div>
                                    <div class="row">
                                        <table class="table table-condensed">
                                            <thead
                                                <tr><th>Currency</th><th>Total Customers</th><th class="text-right">Total Profit</th></tr>
                                            </thead>
                                            <tbody id="id_tbody_clientprofit">

                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="clearfix"></div>
                                    <div class="ln_solid"></div>
                                    <div class="text-right">
                                        <a href="<?php echo base_url('report/clientprofitdetails') ?>" class="btn btn-info">Detail</a>                              
                                    </div>



                                </div>				
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="card">
                                <div class="header">
                                    <h4>Resellers Profit</h4>
                                </div>
                                <div class="body bg-pink111" style="margin: 10px;">





                                    <form class="block-content form-horizontal " id="reseller_form" name="reseller_form">
                                        <input type="hidden" name="search_action" value="searchreseller" />			
                                        <div class="form-group">
                                            <div class="row">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                                                <div class="col-md-8 col-sm-9 col-xs-12">
                                                    <input type="text" name="resellertime" id="id_resellertime" class="form-control" value="" />
                                                </div>
                                                <div class="col-md-2 text-right">
                                                    <input type="button" value="Search" name="OkFilter" id="OkFilterResellerProfit" class="btn btn-primary"> </div>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="clearfix"></div>
                                    <div class="ln_solid"></div>

                                    <div class="row">
                                        <table class="table table-condensed">
                                            <thead
                                                <tr><th>Currency</th><th>Total Resellers</th><th class="text-right">Total Profit</th></tr>
                                            </thead>
                                            <tbody id="id_tbody_resellerprofit">

                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="clearfix"></div>
                                    <div class="ln_solid"></div>
                                    <div class="text-right">
                                        <a href="<?php echo base_url('report/resellerprofitdetails') ?>" class="btn btn-info">Detail</a>                                
                                    </div>



                                </div>				
                            </div>
                        </div>
                    </div>


                    <div class="row clearfix">





                        <?php if (check_logged_user_group(array('SYSTEM'))) { ?>
                            <script>
                                var is_admin = true;
                            </script>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="card">
                                    <div class="header">
                                        <h4>Carrier Cost </h4>
                                    </div>
                                    <div class="body bg-pink111 " style="margin: 10px;">

                                        <form class="block-content form-horizontal " id="vendor_form" name="vendor_form">
                                            <input type="hidden" name="search_action" value="searchvendor" />			
                                            <div class="form-group">

                                                <div class="row">
                                                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                                                    <div class="col-md-8 col-sm-9 col-xs-12">
                                                        <input type="text" name="vendortime" id="id_vendortime" class="form-control" value="" />
                                                    </div>
                                                    <div class="col-md-2 text-right ">
                                                        <input type="button" value="Search" name="OkFilter" id="OkFiltervendor" class="btn btn-primary">                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="clearfix"></div>
                                        <div class="ln_solid"></div>

                                        <div class="row">
                                            <table class="table table-condensed">
                                                <thead
                                                    <tr><th>Currency</th><th>Total vendor</th><th class="text-right">Total Cost</th></tr>
                                                </thead>
                                                <tbody id="id_vendor_cost">

                                                </tbody>
                                            </table>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        <?php } ?>    



                    </div>	

                </div>	
            </div>	
        </div>	
    </div>	
</div>	





</div>
<script>
    const loading_img = '<tr><td colspan="5" align="center"><img src="' + BASE_URL + 'theme/default/images/loading.gif" /></td></tr>';
    $(document).ready(function () {
        var today = new Date();
        var endDate = new Date();
        endDate = today = moment().subtract(1, 'days');
        $("#id_clienttime, #id_resellertime, #id_vendortime").daterangepicker({
            timePicker: !0,
            timePickerIncrement: 5,
            startDate: today,
            endDate: endDate,
            locale: {
                format: "YYYY-MM-DD"
            },
            timePicker24Hour: true,
            ranges: {
                /* 'Today': [moment().startOf('days'), moment().endOf('days')],*/
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').startOf('days')],
                'Last 7 Days': [moment().subtract(7, 'days').startOf('days'), moment().subtract(1, 'days').startOf('days')],
                /*'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],*/
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

    });


    $(document).ready(function () {
        clientprofit();
        resellerprofit();

        activeservices();
        if (typeof is_admin !== 'undefined')
        {
            vendorprofit();
        }
    });
    $('#OkFilterClientProfit').click(function (e) {
        clientprofit();
    });
    $('#OkFilterResellerProfit').click(function (e) {
        resellerprofit();
    });
    $('#OkFiltervendor').click(function (e) {
        vendorprofit();
    });


    function clientprofit() {
        var tdata = $("#client_form").serializeArray();
        var url = BASE_URL + "report/ajax_clientprofit";
        //console.log(url);
        $('#id_tbody_clientprofit').html(loading_img);
        $.ajax({
            url: url,
            type: 'POST',
            data: tdata,
            success: function (datas) {
                //console.log(datas);
                $('#id_tbody_clientprofit').html(datas);
            }
        });
    }
    function resellerprofit() {
        var tdata = $("#reseller_form").serializeArray();
        var url = BASE_URL + "report/ajax_resellerprofit";
        //	console.log(url);
        $('#id_tbody_resellerprofit').html(loading_img);
        $.ajax({
            url: url,
            type: 'POST',
            data: tdata,
            success: function (datas) {
                //console.log(datas);
                $('#id_tbody_resellerprofit').html(datas);
            }
        });
    }
    function vendorprofit() {

        var tdata = $("#vendor_form").serializeArray();
        var url = BASE_URL + "report/ajax_vendorprofit";
        //console.log(url);
        $('#id_vendor_cost').html(loading_img);
        $.ajax({
            url: url,
            type: 'POST',
            data: tdata,
            success: function (datas) {
                //console.log(datas);
                $('#id_vendor_cost').html(datas);
            }
        });
    }
    function activeservices() {
        var tdata = $("#services_form").serializeArray();
        var url = BASE_URL + "report/ajax_activeservices";
        $('#id_activeservices').html(loading_img);
        $.ajax({
            url: url,
            type: 'POST',
            data: tdata,
            success: function (datas) {
                //console.log(datas);
                $('#id_activeservices').html(datas);
            }
        });
    }
</script>

