<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
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