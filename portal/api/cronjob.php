<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */


/*
 * This script is to generate the daily calls sdr and billing  
 */


include_once 'config.php';
include_once 'lib/APIS.php';
$APIS = New APIS();
$date = date('Y-m-d');
$date = date('Y-m-d', strtotime($date . ' -1 day'));
/*
 * Generating the last day SDR and billing 
 */
$APIS->service($date);


exit;
$account_id =  '';
$date = '2021-06-02';
//$APIS->usage_data($date, $account_id) ;
?>
