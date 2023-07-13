<div class="col-md-6 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Clients and Profit </h2>
            <div class="clearfix"></div>
        </div>
        <form class="block-content form-horizontal " id="client_form" name="client_form" >
            <input type="hidden" name="search_action" value="searchcustomer" />			
            <div class="form-group">

                <div class="row">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                    <div class="col-md-8 col-sm-9 col-xs-12">
                        <input type="text" name="clienttime" id="id_clienttime" class="form-control" value="<?php echo $_SESSION[$search_session_key]['clienttime']; ?>" />
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
<!--</div>-->
<?php //if(isset($client_data['reseller_data'])){ ?>
<!--<div class="col-md-6 col-sm-12 col-xs-12">-->
    <div class="x_panel">
        <div class="x_title">
            <h2>Resellers and Profit</h2>
            <div class="clearfix"></div>
        </div>
        <form class="block-content form-horizontal " id="reseller_form" name="reseller_form">
            <input type="hidden" name="search_action" value="searchreseller" />			
            <div class="form-group">
                <div class="row">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                    <div class="col-md-8 col-sm-9 col-xs-12">
                        <input type="text" name="resellertime" id="id_resellertime" class="form-control" value="<?php echo $_SESSION[$search_session_key]['resellertime']; ?>" />
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
<?php // }  ?>
<div class="col-md-6 col-sm-12 col-xs-12">
    
<?php if (check_logged_user_group(array('SYSTEM')))  {?>
<script>
var is_admin=true;
</script>
    <div class="x_panel">
        <div class="x_title">
            <h2>Provider Cost</h2>
            <div class="clearfix"></div>
        </div>
        <form class="block-content form-horizontal " id="provider_form" name="provider_form">
            <input type="hidden" name="search_action" value="searchprovider" />			
            <div class="form-group">

                <div class="row">
                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
                    <div class="col-md-8 col-sm-9 col-xs-12">
                        <input type="text" name="providertime" id="id_providertime" class="form-control" value="" />
                    </div>
                    <div class="col-md-2 text-right ">
                        <input type="button" value="Search" name="OkFilter" id="OkFilterProvider" class="btn btn-primary">                    </div>
                </div>
            </div>
        </form>
        <div class="clearfix"></div>
        <div class="ln_solid"></div>
        
         <div class="row">
        	<table class="table table-condensed">
            	<thead
                <tr><th>Currency</th><th>Total Provider</th><th class="text-right">Total Cost</th></tr>
                </thead>
                <tbody id="id_provider_cost">
                
                </tbody>
            </table>
        </div>

		
        <div class="clearfix"></div>
        <div class="ln_solid"></div>
        <div class="text-right">
       <!--     <a href="<?php echo base_url('report/providerprofitdetails') ?>" class="btn btn-info">Detail</a>      -->                      
        </div>
    </div>
<?php }?>    
    <div class="x_panel">
        <div class="x_title">
            <h2>Active Services</h2>
            <div class="clearfix"></div>
        </div>
        <form class="block-content form-horizontal " id="services_form" name="services_form">
            <input type="hidden" name="search_action" value="search" />			
            <div class="form-group">

                <div class="row">                    
                    <div class="col-md-12 text-right ">
                        <input type="button" value="Search" name="OkFilter" id="OkFilterServices" class="btn btn-primary" />
                    </div>
                </div>
            </div>
        </form>
        <div class="clearfix"></div>
        <div class="ln_solid"></div>
        <div class=" text-center">
            <span>Active Services</span>
            <h2 id="id_activeservices">&nbsp;</h2>
        </div>
        <div class="clearfix"></div>
        <div class="ln_solid"></div>
        <div class="text-right">
              <a href="<?php echo base_url('report/servicedetails') ?>" class="btn btn-info">Detail</a>                           
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
	var today = new Date();
	var endDate = new Date();
	endDate=today=moment().subtract(1, 'days');
        $("#id_clienttime, #id_resellertime, #id_providertime").daterangepicker({
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


    $(document).ready(function() {
        clientprofit();
        resellerprofit();
        
        activeservices();
		if(typeof is_admin !== 'undefined')
		{
			providerprofit();
		}
    });
    $('#OkFilterClientProfit').click(function(e) {
        clientprofit();
    });
    $('#OkFilterResellerProfit').click(function(e) {
        resellerprofit();
    });
    $('#OkFilterProvider').click(function(e) {
        providerprofit();
    });
    $('#OkFilterServices').click(function(e) {
        activeservices();
    });
   
    function clientprofit() {
        var tdata = $("#client_form").serializeArray();
        var url = BASE_URL +"report/ajax_clientprofit";
		//console.log(url);
        $.ajax({
            url: url,
            type: 'POST',
            data: tdata,
            success: function(datas) {
				//console.log(datas);
				$('#id_tbody_clientprofit').html(datas);
            }
        });
    }
    function resellerprofit() {		
		var tdata = $("#reseller_form").serializeArray();
        var url = BASE_URL +"report/ajax_resellerprofit";
	//	console.log(url);
        $.ajax({
            url: url,
            type: 'POST',
            data: tdata,
            success: function(datas) {
				//console.log(datas);
				$('#id_tbody_resellerprofit').html(datas);
            }
        });
    }
    function providerprofit() {		
		
	    var tdata = $("#provider_form").serializeArray();
        var url = BASE_URL +"report/ajax_providerprofit";
		//console.log(url);
        $.ajax({
            url: url,
            type: 'POST',
            data: tdata,
            success: function(datas) {
				//console.log(datas);
				$('#id_provider_cost').html(datas);
            }
        });
    }
	 function activeservices() {
        var tdata = $("#services_form").serializeArray();		
		var url = BASE_URL +"report/ajax_activeservices";
		 $.ajax({
            url: url,
            type: 'POST',
            data: tdata,
            success: function(datas) {
				//console.log(datas);
				$('#id_activeservices').html(datas);
            }
        });
    }
</script>

