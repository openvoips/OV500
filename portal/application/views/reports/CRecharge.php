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
<?php
$data_array =$data_array0 =$data_array1 =$data_array2 = array();
$customer_list=array();
$currency_data_array=array();

//echo '<pre>';print_r($sales_data['ME100255']); echo '</pre>';

foreach($sales_data as $account_id =>$sales_data_array)
{
	$topup = $sales_data_array['cost'];
	//
	if(isset($endusers_data[$account_id]))
		$user_currency_id = $endusers_data[$account_id]['user_currency_id'];
	elseif(isset($resellers_data[$account_id]))
		$user_currency_id = $resellers_data[$account_id]['user_currency_id'];	
	else
		continue;
	$data_array0[$account_id] = array('currency'=>$user_currency_id,'topup'=>$topup, 'company_name'=>$sales_data_array['company_name']);
//	echo $cost.'<br>';
	$currency_data_array[]=$user_currency_id;
	if(isset($endusers_data[$account_id]))
	unset($endusers_data[$account_id]);
}
//echo '<pre>';print_r($data_array0);echo '</pre>';

foreach($endusers_data as $endusers_data_array)
{
	$account_id = $endusers_data_array['account_id'];
	$user_currency_id = $endusers_data_array['user_currency_id'];
	$company_name = $endusers_data_array['company_name'];
	
	if(isset($sales_data[$account_id]))
	{	$topup = $sales_data[$account_id]['cost'];
		
		$data_array1[$account_id] = array('currency'=>$user_currency_id,'topup'=>$topup,  'company_name'=>$company_name);
	}
	else
	{
		$topup = 0;
		$data_array2[$account_id] = array('currency'=>$user_currency_id,'topup'=>$topup, 'company_name'=>$company_name);
	}
	$currency_data_array[]=$user_currency_id;
	
}
$data_array = array_merge($data_array0,$data_array1,$data_array2);

//echo '<pre>';print_r($sales_data);echo '</pre>';//
//echo '<pre>';print_r($data_array);echo '</pre>';
if(in_array(1,$currency_data_array))
	$usd_currency_array=array();
if(in_array(2,$currency_data_array))
	$gbp_currency_array=array();
if(in_array(3,$currency_data_array))
	$eur_currency_array=array();
if(in_array(4,$currency_data_array))
	$inr_currency_array=array();	
//echo '<pre>';print_r($currency_data_array);print_r($gbp_currency_array);echo '</pre>';

foreach($data_array as  $account_id=>$currency_array_temp)
{
	$currency_id = $currency_array_temp['currency'];
	$topup = $currency_array_temp['topup'];	
	$company_name = $currency_array_temp['company_name'];
	
	if($company_name =='')
		$company_name = $account_id;
	
	array_push($customer_list,$company_name);
	
	
	if($currency_array_temp['currency']=='1')
	{
		if(isset($usd_currency_array)) array_push($usd_currency_array,$topup);		
		if(isset($gbp_currency_array)) array_push($gbp_currency_array,0);
		if(isset($eur_currency_array)) array_push($eur_currency_array,0);
		if(isset($inr_currency_array)) array_push($inr_currency_array,0);
	}
	elseif($currency_array_temp['currency']=='2')
	{
		if(isset($usd_currency_array))array_push($usd_currency_array,0);		
		if(isset($gbp_currency_array))array_push($gbp_currency_array,$topup);
		if(isset($eur_currency_array)) array_push($eur_currency_array,0);
		if(isset($inr_currency_array)) array_push($inr_currency_array,0);
	}
	elseif($currency_array_temp['currency']=='3')
	{
		if(isset($usd_currency_array)) array_push($usd_currency_array,0);		
		if(isset($gbp_currency_array)) array_push($gbp_currency_array,0);
		if(isset($eur_currency_array)) array_push($eur_currency_array,$topup);
		if(isset($inr_currency_array)) array_push($inr_currency_array,0);
	}	
	elseif($currency_array_temp['currency']=='4')
	{
		if(isset($usd_currency_array)) array_push($usd_currency_array,0);		
		if(isset($gbp_currency_array))array_push($gbp_currency_array,0);
		if(isset($eur_currency_array)) array_push($eur_currency_array,0);
		if(isset($inr_currency_array)) array_push($inr_currency_array,$topup);
	}	
	else
	{
		array_push($usd_currency_array,0);		
		array_push($gbp_currency_array,0);
		array_push($eur_currency_array,0);
		array_push($inr_currency_array,0);
	}
	

	
}



//echo '<pre>';print_r($usd_currency_array);echo '</pre>';


	
	
	

	


/*whichever currency exists in report data, define only those array*/
if(in_array(1, $currency_data_array))
{?><script>var usd_array=new Array();</script><?php }

if(in_array(2, $currency_data_array))
{?><script>var gbp_array=new Array();</script> <?php }

if(in_array(3, $currency_data_array))
{?><script>var eur_array=new Array();</script><?php }

if(in_array(4, $currency_data_array))
{?><script>var inr_array=new Array();</script><?php }?>



<script type="text/javascript" language="javascript">
var final_data_array=new Array();
	
   var cust_list=new Array();




	
   
   
    <?php 
	foreach($customer_list as $key => $val)
	{ ?>
        cust_list.push('<?php echo $val; ?>');
    <?php 
	} 
	
	if(isset($gbp_currency_array))
	{
		foreach($gbp_currency_array as $key => $val)
		{ ?>
			gbp_array.push(<?php echo $val; ?>);
		<?php 
		} 	
		$gbp_total = array_sum($gbp_currency_array);
	}
	
	if(isset($usd_currency_array))
	{
		foreach($usd_currency_array as $key => $val)
		{ ?>
			usd_array.push(<?php echo $val; ?>);
		<?php 
		} 
		$usd_total = array_sum($usd_currency_array);
	}
	
	if(isset($eur_currency_array))
	{
		foreach($eur_currency_array as $key => $val)
		{ ?>
        	eur_array.push(<?php echo $val; ?>);
    <?php 
		}
		$eur_total = array_sum($eur_currency_array);
	} 
	
	if(isset($inr_currency_array))
	{
		foreach($inr_currency_array as $key => $val)
		{ ?>
        	inr_array.push(<?php echo $val; ?>);
    <?php 
		}
		$inr_total = array_sum($inr_currency_array);
	} 
	?>
	
	

//console.log(gbp_array);

if (typeof gbp_array !== 'undefined')
{	
	var obj_temp = {label:"GBP (<?php echo $gbp_total;?>)", backgroundColor:"#03586A", data:gbp_array};
	final_data_array.push(obj_temp); 	
}

if (typeof usd_array !== 'undefined')
{	
	var obj_temp = {label:"USD (<?php echo $usd_total;?>)", backgroundColor:"#26B99A", data:usd_array};
	final_data_array.push(obj_temp); 	
}

if (typeof eur_array !== 'undefined')
{	
	var obj_temp = {label:"EUR (<?php echo $eur_total;?>)", backgroundColor:"#CC0033", data:eur_array};
	final_data_array.push(obj_temp); 	
}

if (typeof inr_array !== 'undefined')
{	
	var obj_temp = {label:"INR (<?php echo $inr_total;?>)", backgroundColor:"yellow", data:inr_array};
	final_data_array.push(obj_temp); 	
}
//console.log(final_data_array);
</script>

<div class="col-md-12 col-sm-12 col-xs-12">
  <div class="x_panel">
    <div class="x_title">
      <h2>Reporting : Topup Customer Summary</h2>
      <div class="clearfix"></div>
    </div>
    <div class="x_content">
      <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
        <input type="hidden" name="search_action" value="search" />
		
        <div class="form-group">
          
		<label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
		<div class="col-md-5 col-sm-9 col-xs-12">
			<input type="text" name="time_range" id="time_range" class="form-control " value="<?php if(isset($_SESSION['search_topup_cust_data']['s_cdr_record_date'])) echo $_SESSION['search_topup_cust_data']['s_cdr_record_date']; ?>" readonly="readonly" data-parsley-required="" />
		</div>   
        
          <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
          <div class="col-md-2 col-sm-9 col-xs-12">
            <input type="text" class="form-control data-search-field" name="account_id" id="account_id" value="<?php echo $_SESSION['search_topup_cust_data']['s_account_id'];?>">
          </div>
       
          <div class="searchBar">
            <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="x_panel">
   	<?php
	$count = count($customer_list);
	$canvas_width = $count *40;	
	
	if($canvas_width < 300)
		$canvas_width = 300;
	?>
	  <div style=" height:<?php echo $canvas_width;?>px;">
	  <canvas id="mybarChart" ></canvas>
	</div> 
   
  </div>
</div>

<script src="<?php echo base_url()?>theme/vendors/Chart.js/dist/Chart.bundle.js"></script>
<script>		
var barChartData = {
	labels:cust_list,
	datasets: final_data_array
};

window.onload = function() {
	var ctx = document.getElementById("mybarChart").getContext("2d");
	window.myBar = new Chart(ctx, {
		type: 'horizontalBar',
		data: barChartData,
		options: {
			
			tooltips: {
				mode: 'label'
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
};


$(document).ready(function () {
	$("#time_range").daterangepicker({
		timePicker: !0,
		timePickerIncrement: 1,
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
			'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	});			 
});
</script>