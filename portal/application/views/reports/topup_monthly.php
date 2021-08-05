<script>
    var label_array = new Array();
    var final_data_array = new Array();
</script>
<?php
$currency_array = array();
foreach ($currency_options as $key => $currency_array_temp) {
    $currency_id = $currency_array_temp['currency_id'];
    $name = $currency_array_temp['name'];
    $currency_array[$name] = $currency_id;
}
/* whichever currency exists in report data, define only those array */
if (isset($topup_data['result'][1])) {
    ?><script>var usd_data_array = new Array();</script><?php
}

if (isset($topup_data['result'][2])) {
    ?><script>var gbp_data_array = new Array();</script> <?php
}

if (isset($topup_data['result'][3])) {
    ?><script>var inr_data_array = new Array();</script><?php
}
if (isset($topup_data['result'][4])) {
    ?><script>var susd_data_array = new Array();</script><?php
}

if (isset($topup_data['result'][5])) {
    ?><script>var eur_data_array = new Array();</script><?php
}

$table_data_str='';

$total_usd =$total_susd = $total_gbp = $total_eur = $total_inr = 0;
$range = explode(' - ', $_SESSION['search_topup_monthly_data']['s_time_range']);
$range_from = explode(' ', $range[0]);
$range_to = explode(' ', $range[1]);

$start_dt = $range[0];
$end_dt = $range[1];


$start_day_stamp = strtotime($start_dt);
$day_stamp = $start_day_stamp;
$end_day_stamp = strtotime($end_dt);
/* loop through starting from start date upto end date */
for ($i = 1; $i < 35; $i++) {
    if ($i == 1) {
        
    } else
        $day_stamp = $day_stamp + 31 * 24 * 60 * 60;
    if ($day_stamp > $end_day_stamp)
        break;


    $day_formatted = date('Y-m', $day_stamp);
    $day_formatted_for_label = date('M-Y', $day_stamp);

    $usd = $gbp = $eur = $inr = $susd=0;


    //1: usd
    if (isset($topup_data['result'][1][$day_formatted]['ADDBALANCE']))
        $usd += $topup_data['result'][1][$day_formatted]['ADDBALANCE']['sum_amount'];
    if (isset($topup_data['result'][1][$day_formatted]['REMOVEBALANCE']))
        $usd -= $topup_data['result'][1][$day_formatted]['REMOVEBALANCE']['sum_amount'];

    ///2: gbp
    if (isset($topup_data['result'][2][$day_formatted]['ADDBALANCE']))
        $gbp += $topup_data['result'][2][$day_formatted]['ADDBALANCE']['sum_amount'];
    if (isset($topup_data['result'][2][$day_formatted]['REMOVEBALANCE']))
        $gbp -= $topup_data['result'][2][$day_formatted]['REMOVEBALANCE']['sum_amount'];

    ///3: inr
    if (isset($topup_data['result'][3][$day_formatted]['ADDBALANCE']))
        $inr += $topup_data['result'][3][$day_formatted]['ADDBALANCE']['sum_amount'];
    if (isset($topup_data['result'][3][$day_formatted]['REMOVEBALANCE']))
        $inr -= $topup_data['result'][3][$day_formatted]['REMOVEBALANCE']['sum_amount'];

    //4: S USD
    if (isset($topup_data['result'][4][$day_formatted]['ADDBALANCE']))
        $susd += $topup_data['result'][4][$day_formatted]['ADDBALANCE']['sum_amount'];
    if (isset($topup_data['result'][4][$day_formatted]['REMOVEBALANCE']))
        $susd -= $topup_data['result'][4][$day_formatted]['REMOVEBALANCE']['sum_amount'];
	
	//5: EUR	
	if (isset($topup_data['result'][5][$day_formatted]['ADDBALANCE']))
        $eur += $topup_data['result'][5][$day_formatted]['ADDBALANCE']['sum_amount'];
    if (isset($topup_data['result'][5][$day_formatted]['REMOVEBALANCE']))
        $eur -= $topup_data['result'][5][$day_formatted]['REMOVEBALANCE']['sum_amount'];	


    $total_usd += $usd;
    $total_gbp += $gbp;
    $total_eur += $eur;
    $total_inr += $inr;
	$total_susd +=$susd;
	
	//////
	if($usd!=0)
	{
		$table_data_str .='<tr><td>'.$day_formatted_for_label.'</td><td>USD</td><td class="text-right">'.number_format($usd,2,".","").'</td></tr>';
	}
	if($gbp!=0)
	{
		$table_data_str .='<tr><td>'.$day_formatted_for_label.'</td><td>GBP</td><td class="text-right">'.number_format($gbp,2,".","").'</td></tr>';
	}
	if($eur!=0)
	{
		$table_data_str .='<tr><td>'.$day_formatted_for_label.'</td><td>EURO</td><td class="text-right">'.number_format($eur,2,".","").'</td></tr>';
	}
	if($inr!=0)
	{
		$table_data_str .='<tr><td>'.$day_formatted_for_label.'</td><td>INR</td><td class="text-right">'.number_format($inr,2,".","").'</td></tr>';
	}
	if($susd!=0)
	{
		$table_data_str .='<tr><td>'.$day_formatted_for_label.'</td><td>S USD</td><td class="text-right">'.number_format($susd,2,".","").'</td></tr>';
	}
	
	
    ?>
    <script>
        /*making final array
         assign amount to respective array 
         0 to all other array if amount not exists but array is defined
         */
        label_array["<?php echo $i; ?>"] = "<?php echo $day_formatted_for_label; ?>";

        if (typeof usd_data_array !== 'undefined')
        {
            usd_data_array["<?php echo $i; ?>"] = "<?php echo $usd; ?>";
        }

        if (typeof gbp_data_array !== 'undefined')
        {
            gbp_data_array["<?php echo $i; ?>"] = "<?php echo $gbp; ?>";
        }

        if (typeof eur_data_array !== 'undefined')
        {
            eur_data_array["<?php echo $i; ?>"] = "<?php echo $eur; ?>";
        }

        if (typeof inr_data_array !== 'undefined')
        {
            inr_data_array["<?php echo $i; ?>"] = "<?php echo $inr; ?>";
        }
		if (typeof susd_data_array !== 'undefined')
        {
            susd_data_array["<?php echo $i; ?>"] = "<?php echo $susd; ?>";
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
        var obj_temp = {label: "USD (<?php echo $total_usd; ?>)", backgroundColor: "#26B99A", data: usd_data_array};
        final_data_array.push(obj_temp);
        //console.log('usd_data_array');	console.log(usd_data_array);
    }
    if (typeof gbp_data_array !== 'undefined')
    {
        gbp_data_array.shift();
        var obj_temp = {label: "GBP (<?php echo $total_gbp; ?>)", backgroundColor: "#03586A", data: gbp_data_array};
        final_data_array.push(obj_temp);
        //console.log('gbp_data_array');	console.log(gbp_data_array);
    }

    if (typeof eur_data_array !== 'undefined')
    {
        eur_data_array.shift();
        var obj_temp = {label: "EURO (<?php echo $total_eur; ?>)", backgroundColor: "#CC0033", data: eur_data_array};
        final_data_array.push(obj_temp);
        //console.log('eur_data_array');	console.log(eur_data_array);
    }
    if (typeof inr_data_array !== 'undefined')
    {
        inr_data_array.shift();
        var obj_temp = {label: "INR (<?php echo $total_inr; ?>)", backgroundColor: "#FFCC00", data: inr_data_array};
        final_data_array.push(obj_temp);
        //console.log('inr_data_array');	console.log(inr_data_array);
    }
	if (typeof susd_data_array !== 'undefined')
    {
        susd_data_array.shift();
        var obj_temp = {label: "S USD (<?php echo $total_inr; ?>)", backgroundColor: "#A0F", data: susd_data_array};
        final_data_array.push(obj_temp);
        //console.log('inr_data_array');	console.log(inr_data_array);
    }
//console.log(final_data_array);
</script>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Topup Monthly Summary</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
                <input type="hidden" name="search_action" value="search" />

                <div class="form-group">




                    <?php if (isset($ac_mngrs_data)): ?>
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Account Manager</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <select name="account_manager" id="account_manager" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                <option value="">Select</option>                    
                                <?php
                                $str = '';
                                if (isset($ac_mngrs_data['result']) && count($ac_mngrs_data['result']) > 0) {
                                    foreach ($ac_mngrs_data['result'] as $key => $ac_mngr_array) {
                                        $selected = ' ';
                                        if ($ac_mngr_array['user_access_id_name'] == $_SESSION['search_topup_monthly_data']['s_account_manager'])
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $ac_mngr_array['user_access_id_name'] . '" ' . $selected . '>' . $ac_mngr_array['name'] . '</option>';
                                    }
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    <?php else:
                        ?>
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                        <div class="col-md-2 col-sm-9 col-xs-12">
                            <input type="text" class="form-control data-search-field" name="account_id" id="account_id" value="<?php echo $_SESSION['search_topup_monthly_data']['s_account_id']; ?>">
                        </div>
                        <input type="hidden" name="account_manager" value="" />
                    <?php endif; ?>

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
    <div class="clearfix"></div>
    <div class="x_panel">
    	 <div class="x_content"> 
    	<div class="table-responsive1">
                <table class="table table-striped jambo_table table-bordered" id="table-sort">                    		
                    <thead>
                        	<tr class="headings thc"> <th width="30%" class="column-title">Month</th><th width="30%" class="column-title">Currency</th><th class="text-right column-title">Amount</th></tr>
                    </thead>
                     <tbody>
                            <?php echo $table_data_str;?>
                    </tbody>
                </table>
               
            </div>                    
            
            <div class="clearfix"></div>
    	</div>
    </div>
</div>
<!-- Chart.js -->
<script src="<?php echo base_url() ?>theme/vendors/Chart.js/dist/Chart.min.js"></script>
<script>
    $(document).ready(function () {

        if ($("#mybarChart").length) {

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
            timePickerIncrement: 5,
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