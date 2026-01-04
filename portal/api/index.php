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
if ($section == 'dialplan') {
    include_once 'lib/OVS.php';
    $dialplan = NEW OVS();
    $result = $dialplan->main($_REQUEST);
    echo $result;
    $endtime = microtime(true);
    $dialplan->writelog($result);
    $time = number_format(($endtime - $starttime), 16);
    $log = "Call Process time :: $time Sec " . $dialplan->error;
    $dialplan->writelog($log);
} else {
    echo "<?xml version=\"1.0\"?>
        <document type=\"OvSwitch/xml\">
        <section name=\"dialplan\" description=\"Regex/XML Dialplan\">
        <context name=\"default\">
        <extension name=\"outbound_international\">
        <condition field=\"destination_number\" expression=\"^(\+?)(\d+)$\">
        <action application=\"answer\"/>
        <action application=\"sleep\" data=\"50000\"/>
        <action application=\"hangup\"/>
        </condition>
        </extension>
        </context>
        </section>
        </document>";
}
exit();
?>
