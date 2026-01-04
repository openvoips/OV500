<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */


$starttime = microtime(true);
include_once 'config.php';
$section = $_REQUEST['section'];
$fc_ip = $_SERVER['REMOTE_ADDR'];

$start = microtime(true);
include_once 'config.php';
include_once 'lib/OVS.php';
$cdr = NEW OVS();
$str = '';
foreach ($_REQUEST as $key => $value) {
    //$cdr->writelog($key ."::".$value);
    $str .= "$key  :  $value \n";
    if ($key == 'cdr')
        $cdrstr = $value;
}


$result = $cdr->cdr($cdrstr);
echo $result;
exit();
?>
