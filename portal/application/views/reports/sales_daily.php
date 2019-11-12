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
<script>
/*define array for labels and data*/
var label_array=new Array();
var final_data_array=new Array();
</script>
<?php
$currency_array=array();
foreach($currency_options as  $key=>$currency_array_temp)
{
	$currency_id = $currency_array_temp['currency_id'];
	$name = $currency_array_temp['name'];
	$currency_array[$name]=$currency_id;
}
/*whichever currency exists in report data, define only those array*/
if(isset($sales_data[1]))
{?><script>var usd_data_array=new Array();</script><?php }

if(isset($sales_data[2]))
{?><script>var gbp_data_array=new Array();</script> <?php }

if(isset($sales_data[3]))
{?><script>var eur_data_array=new Array();</script><?php }

if(isset($sales_data[4]))
{?><script>var inr_data_array=new Array();</script><?php }

//echo '<pre>'; print_r($sales_data);echo '</pre>';


/*
1: usd
2: gbp
3: eur
4: inr
*/

///*
$total_usd = $total_gbp = $total_eur = $total_inr= 0;
$range = explode(' - ',$_SESSION['search_sales_day_data']['s_cdr_record_date']);
$range_from = explode(' ',$range[0]);
$range_to = explode(' ',$range[1]);
			
$start_dt = $range[0];		
$end_dt = $range[1];


$start_day_stamp =  strtotime($start_dt);
$day_stamp =$start_day_stamp;
$end_day_stamp = strtotime($end_dt);
/*loop through starting from start date upto end date*/
$i=0;
while(1)
{
	$i++;
	if($i==1)
	{
	}
	else
		$day_stamp = $day_stamp+24*60*60;
	if($day_stamp>$end_day_stamp)
		break;
	
	
	$day_formatted =  date('Y-m-d', $day_stamp);
	$day_formatted_for_label =  date('d-m-Y', $day_stamp);
	
	$usd = $gbp = $eur = $inr= 0;
	
	
	//1: usd
	if(isset($sales_data[1][$day_formatted]['cost']))
		$usd +=$sales_data[1][$day_formatted]['cost'];
	
	///2: gbp
	if(isset($sales_data[2][$day_formatted]['cost']))
		$gbp +=$sales_data[2][$day_formatted]['cost'];
	
	///3: eur
	if(isset($sales_data[3][$day_formatted]['cost']))
		$eur +=$sales_data[3][$day_formatted]['cost'];
	
	//4: inr
	if(isset($sales_data[4][$day_formatted]['cost']))
		$inr +=$sales_data[4][$day_formatted]['cost'];	
		
		
	$total_usd += $usd;
	$total_gbp += $gbp;
	$total_eur += $eur;
	$total_inr += $inr;
	?>
	<script>
	/*making final array
	assign amount to respective array 
	0 to all other array if amount not exists but array is defined
	*/
	label_array["<?php echo $i;?>"]="<?php echo $day_formatted_for_label;?>";

	if (typeof usd_data_array !== 'undefined')
	{
		usd_data_array["<?php echo $i;?>"]="<?php echo $usd;?>";
	}	

	if (typeof gbp_data_array !== 'undefined')
	{
		gbp_data_array["<?php echo $i;?>"]="<?php echo $gbp;?>";
	}
	
	if (typeof eur_data_array !== 'undefined')
	{
		eur_data_array["<?php echo $i;?>"]="<?php echo $eur;?>";
	}
	
	if (typeof inr_data_array !== 'undefined')
	{
		inr_data_array["<?php echo $i;?>"]="<?php echo $inr;?>";
	}
	</script>
	<?php
}
?>
<script>
label_array.shift();
if (typeof usd_data_array !== 'undefined')
{
	usd_data_array.shift();
	var obj_temp = {label:"USD (<?php echo $total_usd;?>)", backgroundColor:"#26B99A", data:usd_data_array};
	final_data_array.push(obj_temp); 
	//console.log('usd_data_array');	console.log(usd_data_array);
}
if (typeof gbp_data_array !== 'undefined')	
{
	gbp_data_array.shift(); 
	var obj_temp = {label:"GBP (<?php echo $total_gbp;?>)", backgroundColor:"#03586A", data:gbp_data_array};
	final_data_array.push(obj_temp); 
	//console.log('gbp_data_array');	console.log(gbp_data_array);
}

if (typeof eur_data_array !== 'undefined')	
{
	eur_data_array.shift(); 
	var obj_temp = {label:"EURO (<?php echo $total_eur;?>)", backgroundColor:"#CC0033", data:eur_data_array};
	final_data_array.push(obj_temp); 
	//console.log('eur_data_array');	console.log(eur_data_array);
}
if (typeof inr_data_array !== 'undefined')
{
	inr_data_array.shift();
	var obj_temp = {label:"INR (<?php echo $total_inr;?>)", backgroundColor:"#FFCC00", data:inr_data_array};
	final_data_array.push(obj_temp); 
	//console.log('inr_data_array');	console.log(inr_data_array);
}
//console.log(final_data_array);
</script>
<div class="col-md-12 col-sm-12 col-xs-12">
  <div class="x_panel">
    <div class="x_title">
      <h2>Sales Daily Summary</h2>
      <div class="clearfix"></div>
    </div>
    <div class="x_content">
      <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
        <input type="hidden" name="search_action" value="search" />
		
        <div class="form-group">
          
		<label class="control-label col-md-1 col-sm-3 col-xs-12">Date</label>
		<div class="col-md-5 col-sm-9 col-xs-12">
			<input type="text" name="time_range" id="time_range" class="form-control " value="<?php if(isset($_SESSION['search_sales_day_data']['s_cdr_record_date'])) echo $_SESSION['search_sales_day_data']['s_cdr_record_date']; ?>" readonly="readonly" data-parsley-required="" />
		</div>   
        
         <?php if(isset($ac_mngrs_data)):?>
		  <label class="control-label col-md-2 col-sm-3 col-xs-12">Account Manager</label>
          <div class="col-md-2 col-sm-9 col-xs-12">
           <select name="account_manager" id="account_manager" class="form-control" tabindex="<?php echo $tab_index++;?>">
                   <option value="">Select</option>                    
                    <?php 
                    $str = '';
					if(isset($ac_mngrs_data['result']) && count($ac_mngrs_data['result']) > 0)
					{
						foreach($ac_mngrs_data['result'] as  $key=>$ac_mngr_array)
						{
							$selected = ' ';
							if($ac_mngr_array['user_access_id_name'] == $_SESSION['search_sales_day_data']['s_account_manager']) 
								$selected = '  selected="selected" ';
							$str .= '<option value="'.$ac_mngr_array['user_access_id_name'].'" '.$selected.'>'.$ac_mngr_array['name'].'</option>';
						}
					}
                    echo $str;
                    ?>
            </select>
          </div>
		  <?php 
		  else: ?>
		  <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
          <div class="col-md-2 col-sm-9 col-xs-12">
            <input type="text" class="form-control data-search-field" name="account_id" id="account_id" value="<?php echo $_SESSION['search_sales_day_data']['s_account_id'];?>">
          </div>
		  	<input type="hidden" name="account_manager" value="" />
		<?php  endif;?>
       
          <div class="searchBar">
            <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="x_panel">
   
	  <div style="height: 400px;">
	  <canvas id="mybarChart"></canvas>
	</div> 
   
  </div>
</div>
<!-- Chart.js -->
<script src="<?php echo base_url()?>theme/vendors/Chart.js/dist/Chart.min.js"></script>
<script>
$(document).ready(function() {

if($("#mybarChart").length){
	
	var f = document.getElementById("mybarChart");
	new Chart(f, {
		type: "bar",
		data: {
			labels: label_array,
			datasets: final_data_array
		},
		options: {
			responsive: true,
  			maintainAspectRatio: false,
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: !0
					}
				}]
			}
		}
	})
}
	
});


$(document).ready(function () {
	$("#time_range").daterangepicker({
		timePicker: !0,
		timePickerIncrement: 1,
		locale: {
			format: "YYYY-MM-DD HH:mm"
		},
		timePicker24Hour: true,
		maxDate: moment().subtract(1, 'days'),
		ranges: {
			'Last 15 Minute': [moment().subtract(15, 'minute'), moment()],
			'Last 30 Minute': [moment().subtract(30, 'minute'), moment()],
			'Last 1 Hour': [moment().subtract(1, 'hour'), moment()],
			//'Today': [moment().startOf('days'), moment().endOf('days')],
			'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
			'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	});			 
});
</script>