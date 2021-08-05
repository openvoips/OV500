<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Openvoips Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
// OV500 Version 2.0
// License https://www.gnu.org/licenses/agpl-3.0.html
//
//
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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

class OVS extends PDO {

    var $fetch_mode = PDO::FETCH_ASSOC;
    var $stmt;
    var $dialplan = Array();
    var $db;
    var $responce;
    var $gtresponce;
    var $destination_number;
    var $destination_number_org;
    var $redis;
    var $cli_auth = 0;
    var $status = 'OK';
    var $dialplan_id = '';
    var $Gateway_XML = '';
    var $route22 = Array();
    var $str = Array();
    var $recording_status = 0;
    var $recording_service = 0;
    var $out_dialplan = 0;
    var $inboundcalls = 0;
    var $mpstncall = '';
    var $fscodlist = array(
        array("GROUP" => "1", "Q850CODE" => "550", "SIPCODE" => "550", "FSSTRING" => "CARRIERCPSISSUE", "CUSTOMMESSAGE" => "CARRIERCPSISSUE"),
        array("GROUP" => "1", "Q850CODE" => "31", "SIPCODE" => "550", "FSSTRING" => "CARRIERCPSISSUE", "CUSTOMMESSAGE" => "CARRIERCPSISSUE"),
        array("GROUP" => "1", "Q850CODE" => "0", "SIPCODE" => "", "FSSTRING" => "UNSPECIFIED", "CUSTOMMESSAGE" => "UNSPECIFIED"),
        array("GROUP" => "1", "Q850CODE" => "1", "SIPCODE" => "404", "FSSTRING" => "UNALLOCATED_NUMBER", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "2", "SIPCODE" => "404", "FSSTRING" => "NO_ROUTE_TRANSIT_NET", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "3", "SIPCODE" => "404", "FSSTRING" => "NO_ROUTE_DESTINATION", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "6", "SIPCODE" => "", "FSSTRING" => "CHANNEL_UNACCEPTABLE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "7", "SIPCODE" => "", "FSSTRING" => "CALL_AWARDED_DELIVERED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "16", "SIPCODE" => "200", "FSSTRING" => "NORMAL_CLEARING", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "17", "SIPCODE" => "486", "FSSTRING" => "USER_BUSY", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "18", "SIPCODE" => "408", "FSSTRING" => "NO_USER_RESPONSE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "19", "SIPCODE" => "480", "FSSTRING" => "NO_ANSWER", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "20", "SIPCODE" => "480", "FSSTRING" => "SUBSCRIBER_ABSENT", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "21", "SIPCODE" => "603", "FSSTRING" => "CALL_REJECTED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "22", "SIPCODE" => "410", "FSSTRING" => "NUMBER_CHANGED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "23", "SIPCODE" => "410", "FSSTRING" => "REDIRECTION_TO_NEW_DESTINATION", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "25", "SIPCODE" => "483", "FSSTRING" => "EXCHANGE_ROUTING_ERROR", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "27", "SIPCODE" => "502", "FSSTRING" => "DESTINATION_OUT_OF_ORDER", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "28", "SIPCODE" => "484", "FSSTRING" => "INVALID_NUMBER_FORMAT", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "29", "SIPCODE" => "501", "FSSTRING" => "FACILITY_REJECTED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "30", "SIPCODE" => "503", "FSSTRING" => "RESPONSE_TO_STATUS_ENQUIRY", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "31", "SIPCODE" => "480", "FSSTRING" => "NORMAL_UNSPECIFIED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "34", "SIPCODE" => "503", "FSSTRING" => "NORMAL_CIRCUIT_CONGESTION", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "38", "SIPCODE" => "503", "FSSTRING" => "NETWORK_OUT_OF_ORDER", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "41", "SIPCODE" => "503", "FSSTRING" => "NORMAL_TEMPORARY_FAILURE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "42", "SIPCODE" => "503", "FSSTRING" => "SWITCH_CONGESTION", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "43", "SIPCODE" => "503", "FSSTRING" => "ACCESS_INFO_DISCARDED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "44", "SIPCODE" => "503", "FSSTRING" => "REQUESTED_CHAN_UNAVAIL", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "45", "SIPCODE" => "503", "FSSTRING" => "PRE_EMPTED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "47", "SIPCODE" => "503", "FSSTRING" => "NONE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "50", "SIPCODE" => "503", "FSSTRING" => "FACILITY_NOT_SUBSCRIBED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "52", "SIPCODE" => "403", "FSSTRING" => "OUTGOING_CALL_BARRED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "54", "SIPCODE" => "403", "FSSTRING" => "INCOMING_CALL_BARRED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "57", "SIPCODE" => "403", "FSSTRING" => "BEARERCAPABILITY_NOTAUTH", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "58", "SIPCODE" => "503", "FSSTRING" => "BEARERCAPABILITY_NOTAVAIL", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "63", "SIPCODE" => "503", "FSSTRING" => "SERVICE_UNAVAILABLE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "65", "SIPCODE" => "488", "FSSTRING" => "BEARERCAPABILITY_NOTIMPL", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "66", "SIPCODE" => "503", "FSSTRING" => "CHAN_NOT_IMPLEMENTED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "69", "SIPCODE" => "501", "FSSTRING" => "FACILITY_NOT_IMPLEMENTED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "79", "SIPCODE" => "501", "FSSTRING" => "SERVICE_NOT_IMPLEMENTED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "81", "SIPCODE" => "503", "FSSTRING" => "INVALID_CALL_REFERENCE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "88", "SIPCODE" => "488", "FSSTRING" => "INCOMPATIBLE_DESTINATION", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "95", "SIPCODE" => "503", "FSSTRING" => "INVALID_MSG_UNSPECIFIED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "96", "SIPCODE" => "407", "FSSTRING" => "MANDATORY_IE_MISSING", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "97", "SIPCODE" => "503", "FSSTRING" => "MESSAGE_TYPE_NONEXIST", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "98", "SIPCODE" => "503", "FSSTRING" => "WRONG_MESSAGE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "99", "SIPCODE" => "503", "FSSTRING" => "IE_NONEXIST", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "100", "SIPCODE" => "503", "FSSTRING" => "INVALID_IE_CONTENTS", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "101", "SIPCODE" => "503", "FSSTRING" => "WRONG_CALL_STATE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "102", "SIPCODE" => "504", "FSSTRING" => "RECOVERY_ON_TIMER_EXPIRE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "103", "SIPCODE" => "503", "FSSTRING" => "MANDATORY_IE_LENGTH_ERROR", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "111", "SIPCODE" => "503", "FSSTRING" => "PROTOCOL_ERROR", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "127", "SIPCODE" => "503", "FSSTRING" => "INTERWORKING", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "487", "SIPCODE" => "487", "FSSTRING" => "ORIGINATOR_CANCEL", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "500", "SIPCODE" => "500", "FSSTRING" => "CRASH", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "501", "SIPCODE" => "501", "FSSTRING" => "SYSTEM_SHUTDOWN", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "502", "SIPCODE" => "502", "FSSTRING" => "LOSE_RACE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "503", "SIPCODE" => "503", "FSSTRING" => "MANAGER_REQUEST", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "600", "SIPCODE" => "600", "FSSTRING" => "BLIND_TRANSFER", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "601", "SIPCODE" => "601", "FSSTRING" => "ATTENDED_TRANSFER", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "602", "SIPCODE" => "602", "FSSTRING" => "ALLOTTED_TIMEOUT", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "603", "SIPCODE" => "603", "FSSTRING" => "USER_CHALLENGE", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "604", "SIPCODE" => "604", "FSSTRING" => "MEDIA_TIMEOUT", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "605", "SIPCODE" => "605", "FSSTRING" => "PICKED_OFF", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "606", "SIPCODE" => "606", "FSSTRING" => "USER_NOT_REGISTERED", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "607", "SIPCODE" => "607", "FSSTRING" => "PROGRESS_TIMEOUT", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => '609', "SIPCODE" => "609", "FSSTRING" => "GATEWAY_DOWN", "CUSTOMMESSAGE" => ""),
        array("GROUP" => "1", "Q850CODE" => "21", "SIPCODE" => "402", "FSSTRING" => "PROGRESS_TIMEOUT", "CUSTOMMESSAGE" => "Low Balance"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "USERINACTIVE", "CUSTOMMESSAGE" => "Account is inactive."),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "401", "FSSTRING" => "USERCLI", "CUSTOMMESSAGE" => "CallerID unauthorized"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "402", "FSSTRING" => "USERBALANCE", "CUSTOMMESSAGE" => "Low Balance"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "USERRATE", "CUSTOMMESSAGE" => "Route Missing"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "USERTARIFFCURRENCY", "CUSTOMMESSAGE" => "Route Missing"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "USERTARIFFINACTIVE", "CUSTOMMESSAGE" => "Route Missing"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "USERRATEBLOCKED", "CUSTOMMESSAGE" => "Route Blocked"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "USERCPS", "CUSTOMMESSAGE" => "User available channels are busy"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "USERCC", "CUSTOMMESSAGE" => "User available channels are busy"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "USERDEVICECC", "CUSTOMMESSAGE" => "Device available channels are busy"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "USERDEVICECPS", "CUSTOMMESSAGE" => "Device available channels are busy"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "402", "FSSTRING" => "RESELLERCALLERIDBLOCKED", "CUSTOMMESSAGE" => "Low Balance"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "603", "FSSTRING" => "USERCALLERIDBLOCKED", "CUSTOMMESSAGE" => "CallerID blocked"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERDIALEDNUMBERBLOCKED", "CUSTOMMESSAGE" => "Route is not available for dialed number(blocked)."),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "USERDIALEDNUMBER", "CUSTOMMESSAGE" => "Route is not available for dialed number.)"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "USERDIALEDNUMBERBLOCKED", "CUSTOMMESSAGE" => "Route is not available for dialed number(blocked)"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "USERDIALPLAN", "CUSTOMMESSAGE" => "Route is not available for dialed number(blocked)"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERDIALPLAN", "CUSTOMMESSAGE" => "Route is not available for dialed number(blocked)"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERINACTIVE", "CUSTOMMESSAGE" => "Service is not active."),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERCURRENCY", "CUSTOMMESSAGE" => "Service is not active."),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERCLI", "CUSTOMMESSAGE" => "Low Balance"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "402", "FSSTRING" => "RESELLERBALANCE", "CUSTOMMESSAGE" => "RESELLERBALANCE"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERRATE", "CUSTOMMESSAGE" => "RESELLERRATE"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERLLRISSUE", "CUSTOMMESSAGE" => "RESELLERLLRISSUE"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERTARIFFCURRENCY", "CUSTOMMESSAGE" => "RESELLERTARIFFCURRENCY"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERTARIFFINACTIVE", "CUSTOMMESSAGE" => "RESELLERTARIFFINACTIVE"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "RESELLERRATEBLOCKED", "CUSTOMMESSAGE" => "RESELLERRATEBLOCKED"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "LLRISSUE", "CUSTOMMESSAGE" => "LLRISSUE"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "CARRIERISSUE", "CUSTOMMESSAGE" => "CARRIERISSUE"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "CARRIERCLIISSUE", "CUSTOMMESSAGE" => "CARRIERCLIISSUE"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "ROUTINGISSUE", "CUSTOMMESSAGE" => "ROUTINGISSUE"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "ROUTESTATUSISSUE", "CUSTOMMESSAGE" => "Gateway and Rates disabled or inactive or missing"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "ROUTEMISSING", "CUSTOMMESSAGE" => "ROUTEMISSING"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "ROUTINGBLOCKED", "CUSTOMMESSAGE" => "ROUTINGBLOCKED"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "USERCCANDBALANCE", "CUSTOMMESSAGE" => "ROUTINGBLOCKED"),
        array("GROUP" => "2", "Q850CODE" => "700", "SIPCODE" => "700", "FSSTRING" => "CARRIERCPSISSUE", "CUSTOMMESSAGE" => "CARRIERCPSISSUE"),
        array("GROUP" => "2", "Q850CODE" => "88", "SIPCODE" => "488", "FSSTRING" => "CODECNOTALLOWED", "CUSTOMMESSAGE" => "Customer Codecs Mis Match"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "503", "FSSTRING" => "MULTIPLECALLSONSAMENUMBER", "CUSTOMMESSAGE" => "Multiple call on same Number"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "DIDNUMBER", "CUSTOMMESSAGE" => "Dialed Number in User DND List"),
        array("GROUP" => "2", "Q850CODE" => "21", "SIPCODE" => "404", "FSSTRING" => "404LISTEDNUMBER", "CUSTOMMESSAGE" => "Dialed Number in 404 Number List"),
    );

    function __construct() {
        $this->writelog('New Hit');
        $this->lb = LB;
    }

    function api($request) {
        $this->rdata = $request;
        $carrier = $this->rdata['carrier'];
        $account = $this->rdata['account'];
        $common_uuid = $this->rdata['common_uuid'];
        $status = $this->rdata['r'];
        $ipaddress = $this->rdata['ipaddress'];
        $destination_number = $this->rdata['destination_number'];
        $ipaddress_name = $this->rdata['ipaddress_name'];
        $routcallerid = $this->rdata['routcallerid'];
        $device = $this->rdata['device'];
        $atime = $this->rdata['atime'];
        $gatewayname = $this->rdata['gatewayname'];
        $atime = str_replace("T", ' ', $atime);
        $destination_number = str_replace("T", '#', $destination_number);
        $this->dbconnect();
        $query = sprintf("SELECT id, ipaddress as   gateway_ipaddress from carrier_ips WHERE carrier_ip_id = '%s' limit 1;", $gatewayname);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->gatewayname[$key] = $value;
        }

        if ($status == 'ring') {
            $query = sprintf("update livecalls set carrier_id ='%s',callstatus='%s', carrier_dst_caller = '%s', carrier_dst_callee ='%s', carrier_ipaddress ='%s' , carrier_ipaddress_name='%s' where common_uuid = '%s' ;", $carrier, $status, $routcallerid, $destination_number, $this->gatewayname['gateway_ipaddress'], $ipaddress_name, $common_uuid);

            $this->writelog($query);
            $this->query('SWITCH', $query);
            $this->execute();
        } elseif ($status == 'answer') {
            $query = sprintf("update livecalls set carrier_id ='%s',callstatus='%s',answer_time =now() , carrier_dst_caller = '%s', carrier_dst_callee ='%s', carrier_ipaddress ='%s', carrier_ipaddress_name='%s' where common_uuid = '%s' ;", $carrier, $status, $routcallerid, $destination_number, $this->gatewayname['gateway_ipaddress'], $ipaddress_name, $common_uuid);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $this->execute();
        }


        $query = "DELETE  from livecalls  WHERE ( callstatus  in ('progress', 'ring') and start_time < ADDDATE(NOW(), INTERVAL -65 SECOND) ) or ( callstatus  in ('answer' ) and start_time < ADDDATE(NOW(), INTERVAL -120 MINUTE) );";
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $this->execute();
    }

    public function cdr($cdrstr) {
        $this->getCDRVariables($cdrstr);
        $this->writelog($this->cdrlogdata1);
        $this->direction = strtolower($this->cdr_variable['direction']);
        if ($this->direction == 'outbound')
            $this->leg = 'B';
        else
            $this->leg = 'A';

        $this->switch_issue = $this->cdr_variable['SWITCH_ISSUE'];
        $this->switch_issue_in = $this->cdr_variable['SWITCH_ISSUE_IN'];
        $this->uuid = $this->cdr_variable['uuid'];
        $this->calltype = $this->cdr_variable['CALLTYPE'];
        $this->dbconnect();
        $this->cdrinsert();
        return $this->cdrlogdata1;
    }

    function connection($dbname) {
        if ($dbname == 'CDR') {
            try {
                $this->dbcdr = new PDO(CDR_DSN, CDR_DSN_LOGIN, CDR_DSN_PASSWORD);
            } catch (Exception $e) {
                $log = CDR_DSN . " " . CDR_DSN_LOGIN . " " . CDR_DSN_PASSWORD;
                $this->writelog('dbcdr DB connection issue ' . $log);
                exit('App shoutdown');
            }
        } else if ($dbname == 'SWITCH') {
            try {
                $this->dbswitch = new PDO(SWITCH_DSN, SWITCH_DSN_LOGIN, SWITCH_DSN_PASSWORD);
            } catch (Exception $e) {
                $log = SWITCH_DSN . " " . SWITCH_DSN_LOGIN . " " . SWITCH_DSN_PASSWORD;
                $this->writelog('Switch DB connection issue ' . $log);
                exit('App shoutdown');
            }
        }
    }

    function dbconnect() {
        $this->connection('CDR');
        $this->connection('SWITCH');
    }

    function query($dbname, $query) {
        if ($dbname == 'SWITCH') {
            $this->stmt = $this->dbswitch->prepare($query);
            return $this;
        } elseif ($dbname == 'CDR') {
            $this->stmt = $this->dbcdr->prepare($query);
            return $this;
        }
    }

    function execute() {
        return $this->stmt->execute();
    }

    function resultset() {
        $this->execute();
        return $this->stmt->fetchAll($this->fetch_mode);
    }

    function single() {
        $this->execute();
        return $this->stmt->fetch($this->fetch_mode);
    }

    function orderBy(&$ary, $clause, $ascending = true) {
        $clause = str_ireplace('order by', '', $clause);
        $clause = preg_replace('/\s+/', ' ', $clause);
        $keys = explode(',', $clause);
        $dirMap = array('desc' => 1, 'asc' => -1);
        $def = $ascending ? -1 : 1;
        $keyAry = array();
        $dirAry = array();
        foreach ($keys as $key) {
            $key = explode(' ', trim($key));
            $keyAry[] = trim($key[0]);
            if (isset($key[1])) {
                $dir = strtolower(trim($key[1]));
                $dirAry[] = $dirMap[$dir] ? $dirMap[$dir] : $def;
            } else {
                $dirAry[] = $def;
            }
        }
        $fnBody = '';
        for ($i = count($keyAry) - 1; $i >= 0; $i--) {
            $k = $keyAry[$i];
            $t = $dirAry[$i];
            $f = -1 * $t;
            $aStr = '$a[\'' . $k . '\']';
            $bStr = '$b[\'' . $k . '\']';
            if (strpos($k, '(') !== false) {
                $aStr = '$a->' . $k;
                $bStr = '$b->' . $k;
            }
            if ($fnBody == '') {
                $fnBody .= "if({$aStr} == {$bStr}) { return 0; }\n";
                $fnBody .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";
            } else {
                $fnBody = "if({$aStr} == {$bStr}) {\n" . $fnBody;
                $fnBody .= "}\n";
                $fnBody .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";
            }
        }
        if ($fnBody) {
            $sortFn = create_function('$a,$b', $fnBody);
            usort($ary, $sortFn);
        }
    }

    function encrypt($string) {
        $result = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->auth_key, ($i % strlen($this->auth_key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }

    function decrypt($string) {
        $result = '';
        $string = base64_decode($string);
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->auth_key, ($i % strlen($this->auth_key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

    function writelog($log) {
        if (LOGWRITE == '1') {
            $datestr = date("dmY/");
            if (!file_exists(LOGPATH . $datestr)) {
                mkdir(LOGPATH . $datestr, 0777, true);
            }
            $this->fh = fopen(LOGPATH . $datestr . "ovs.log", 'a+');
            $datestr = date("M d H:i:s");
            $log = $this->uuid . " :: " . "$datestr :: $log\n";
            fwrite($this->fh, $log);
        }
    }

    /* Retrieve request variable */

    function set_requestdata($REQUEST) {
        $this->xmldata = serialize($REQUEST);
        foreach ($REQUEST as $key => $value) {
            $this->lstr .= "[$key : " . urldecode($value) . "]";
            $this->request[$key] = trim(urldecode($value));
        }
        $this->writelog($this->lstr);
    }

    /* Retrieve CDR variables value */

    function getCDRVariables($str) {
        $str = json_decode(json_encode((array) simplexml_load_string($str)), 1);
        echo "$str";
        $this->xmldata = serialize($str);
        foreach ($str as $key => $value) {
            if ($key == 'variables') {
                if (is_array($value)) {
                    foreach ($value as $key1 => $value1) {
                        $this->cdr_variable[$key1] = trim(urldecode($value1));
                        $this->cdrlogdata .= "[$key1 : " . urldecode($value1) . "]";
                        $this->cdrlogdata1 .= "$key1 : " . urldecode($value1) . "\n";
                    }
                }
            }
        }
    }

    function charges_cal($charges) {
        $no_of_days = date('t');
        $current_day = date('d');
        $billingdays = ($no_of_days - $current_day) + 1;
        $current_month_charges = ($charges / $no_of_days) * $billingdays;
        return $current_month_charges;
    }

    function format_period($seconds_input) {
        $hours = (int) ($minutes = (int) ($seconds = (int) ($milliseconds = (int) ($seconds_input * 1000)) / 1000) / 60) / 60;
        return $hours . ':' . ($minutes % 60) . ':' . ($seconds % 60) . (($milliseconds === 0) ? '' : '.' . rtrim($milliseconds % 1000, '0'));
    }

    function main($REQUEST) {
        error_reporting(0);
        $this->set_requestdata($REQUEST);
        $this->dbconnect();
        $this->uuid = $this->request['Unique-ID'];
        $this->callernumber = $this->request['Caller-Orig-Caller-ID-Number'];
        $this->destination_number_org = $this->destination_number = $this->request['Caller-Destination-Number'];
        $this->caller_number = $this->request['Hunt-Username'];
        if ($this->caller_number == NULL)
            $this->caller_number = $this->request['Hunt-Caller-ID-Number'];
        $this->caller_orggnetwork = $this->request['Caller-Network-Addr'];
        $this->caller_auth = $this->request['variable_sip_auth_username'];
        if ($this->caller_auth == null)
            $this->caller_auth = $this->caller_auth_calerid = $this->request['Hunt-Caller-ID-Number'];
        $this->lega_codecs = $this->request['variable_ep_codec_string'];
        $this->caller_orggnetwork_auth = $this->request['variable_sip_contact_host'];
        $this->caller_orggnetwork = $this->request['variable_sip_contact_host'];
        $this->profile = 'internal';
        if ($this->caller_orggnetwork_auth == '' or $this->caller_orggnetwork_auth == NULL) {
            $this->caller_orggnetwork = $this->request['variable_sip_contact_host'];
            $this->caller_orggnetwork_auth = $this->request['variable_sip_contact_host'];
        }
        $this->src_caller = $this->callernumber;
        $this->src_callee = $this->destination_number;
        $this->dst_caller = '';
        $this->dst_callee = '';
        $this->issue = 0;
        $this->rip = $this->caller_orggnetwork;
        $this->rip2 = $this->caller_orggnetwork_auth;
        $this->account_id = $this->request['variable_sip_h_X-ACCOUNTCODE'];
        $this->account_originator_ip = $this->request['variable_sip_h_X-MEDIP'];
        $this->account_device_id = $this->request['variable_sip_h_X-DEVICEID'];
        $this->account_device_cc = $this->request['variable_sip_h_X-DEVICECC'];
        $this->account_device_cps = $this->request['variable_sip_h_X-DEVICECPS'];
        $this->account_table_id = $this->request['variable_sip_h_X-ACCOUNTID'];
        $this->account_cc = $this->request['variable_sip_h_X-ACCOUNTCC'];
        $this->account_cps = $this->request['variable_sip_h_X-ACCOUNTCPS'];
        $this->account_withmedia = $this->request['variable_sip_h_X-WITHMEDIA'];
        $this->account_device_type = $this->request['variable_sip_h_X-DEVICETYPE'];
        $this->caller_callid = $this->request['variable_sip_call_id'];
        $this->Hunt_Network_Addr = $this->request['Hunt-Network-Addr'];
        $this->incomingcarrier = $this->request['variable_sip_h_X-INCOMINGCARRIER'];
        $this->incomingcarrierdst = $this->request['variable_sip_h_X-INCOMINGCARRIERDST'];
        $this->sdp = $this->request['variable_r_sdp'];
        $this->Hunt_Network_Addr_port = $this->request['variable_sip_network_port'];
        $this->lb = $this->Hunt_Network_Addr . ":" . $this->Hunt_Network_Addr_port;

        if (strlen($this->incomingcarrier) > 0) {
            $this->DID_Call();
            if ($this->status != 'FAIL') {
                $responce = $this->DID_route_dialplan_xml();
                RETURN $responce;
            }
            $responce = $this->DID_route_dialplan_xml();
            RETURN $responce;
        }
        if ($this->caller_auth == '' AND $this->caller_auth == NULL)
            $this->caller_auth = $this->request['Hunt-Username'];
        if ($this->destination_number == 'hangup') {
            $this->responce = "<?xml version=\"1.0\"?>
              <document type=\"OvSwitch/xml\">
              <section name=\"dialplan\" description=\"Regex/XML Dialplan\">
              <context name=\"default\">
              <action application=\"hangup\"/>
              </context>
              </section>
              </document>";
            return $this->responce;
        }

        $this->PSTN_Call();
        if ($this->status == 'FAIL') {
            $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            $responce = $this->PSTN_route_dialplan_xml();
            RETURN $responce;
        } else {
            $this->PSTN_routing();
        }
        if ($this->status == 'FAIL') {
            $responce = $this->PSTN_route_dialplan_xml();
            RETURN $responce;
        }

        $responce = $this->PSTN_route_dialplan_xml();
        RETURN $responce;
    }

    function DID_Call() {
        $this->currencies_data();
        $lb = $this->Hunt_Network_Addr;
        $lb = $this->lb;
        $query = sprintf("SELECT did.did_number, did.did_status, did.carrier_id, did.account_id, did.reseller1_account_id, did.reseller2_account_id, did.reseller3_account_id,  did_dst.account_id, did_dst.dst_type, did_dst.dst_destination, did_dst.dst_destination2, did_dst.dst_type2  from did INNER JOIN did_dst on did.account_id = did_dst.account_id where did.did_number  like concat('%%', TRIM(LEADING '0' FROM  TRIM(LEADING '+' FROM '%s')))  ORDER BY did.did_number limit 1;", $this->incomingcarrierdst, $this->incomingcarrier);



        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->carrierdata[$key] = $value;
        }


        if (strlen($this->carrierdata['did_number']) > 0) {
            $this->destination_number = $this->incomingcarrierdst = $did_after_carrier_dst_rule2 = $did_after_carrier_dst_rule = $this->carrierdata['did_number'];
        } else {
            $did_after_carrier_dst_rule2 = $this->incomingcarrierdst;
        }
        $query = sprintf("SELECT LENGTH(maching_string) lndata, remove_string, add_string FROM carrier_prefix where carrier_id= '%s' and '%s' like maching_string and route  = 'INBOUND' order by lndata desc limit 1;", $this->incomingcarrier, $did_after_carrier_dst_rule2);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $tech_prefix = $this->resultset();
        if (count($tech_prefix[0]) == 0) {
            $tech_prefix[0]['add_string'] = '';
            $tech_prefix[0]['remove_string'] = '';
        }
        $did_after_carrier_dst_rule = $this->route_change_prefix($this->incomingcarrierdst, $tech_prefix);

        $query = sprintf("SELECT tax_type, carrier.dp, carrier.carrier_progress_timeout, carrier.carrier_ring_timeout, carrier.carrier_name, carrier.carrier_id, tariff.tariff_id, carrier.carrier_status, carrier.carrier_currency_id, carrier.tax1, carrier.tax2, carrier.tax3, carrier.carrier_codecs, tariff.tariff_currency_id, tariff.tariff_status, tariff.tariff_type FROM carrier INNER JOIN tariff ON tariff.tariff_id = carrier.tariff_id WHERE carrier.carrier_id = '%s' limit 1;", $this->incomingcarrier);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->carrierdata[$key] = $value;
        }


        $query = sprintf("SELECT did.did_number, did.did_status, did.carrier_id, did.account_id, did.reseller1_account_id, did.reseller2_account_id, did.reseller3_account_id,  did_dst.account_id, did_dst.dst_type, did_dst.dst_destination, did_dst.dst_destination2, did_dst.dst_type2  from did INNER JOIN did_dst on did.account_id = did_dst.account_id where did_dst.did_number  like concat('%', TRIM(LEADING '0' FROM  TRIM(LEADING '+' FROM '%s')))  ORDER BY did.did_number limit 1;", $this->incomingcarrierdst, $this->incomingcarrier);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->carrierdata[$key] = $value;
        }

        if (strlen($this->carrierdata['did_number']) > 0) {
            $did_after_carrier_dst_rule = $this->carrierdata['did_number'];
        }

        $query = sprintf("SELECT LENGTH(maching_string) lndata, remove_string, add_string, action_type FROM carrier_callerid where carrier_id = '%s' and '%s' and route = 'INBOUND' like maching_string ORDER BY lndata desc limit 1;", $this->incomingcarrier, $this->caller_number);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $carrier_caller = $this->resultset();
        if (count($carrier_caller) == 0) {
            $otherinfo = $this->incomingcarrier;
            $this->fail_route_xml_inbound('CARRIERCLIISSUE', $otherinfo);
            return;
        }
        $route_callid = $this->DID_route_change_callid($carrier_caller);
        if (strlen(trim($route_callid)) > 0) {
            $this->caller_number = $route_callid;
        }
        $dstarray = str_split($did_after_carrier_dst_rule);
        $tmpstr = '';
        $str = '';
        foreach ($dstarray as $key => $value) {
            $tmpstr .= $value;
            $str .= "prefix = '" . $tmpstr . "' or ";
        }

        $str = rtrim($str, ' or ');

        $query = sprintf("SELECT  tariff.tariff_status, tariff_ratecard_map.id, tariff_ratecard_map.ratecard_id, tariff_ratecard_map.tariff_id, tariff_ratecard_map.start_day, tariff_ratecard_map.end_day, tariff_ratecard_map.start_time, tariff_ratecard_map.end_time, carrier_rates.prefix, REPLACE(REPLACE( carrier_rates.destination,',',' '),'-',' ') as destination, carrier_rates.rate, carrier_rates.connection_charge, carrier_rates.minimal_time, carrier_rates.resolution_time, carrier_rates.grace_period, carrier_rates.rate_multiplier, carrier_rates.rate_addition, carrier_rates.rates_status, tariff.tariff_currency_id, tariff.tariff_currency_id FROM tariff_ratecard_map  INNER JOIN carrier_rates on carrier_rates.ratecard_id = tariff_ratecard_map.ratecard_id    INNER JOIN ratecard on carrier_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'INCOMING' INNER JOIN tariff on  tariff.tariff_id = tariff_ratecard_map.tariff_id where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and tariff_ratecard_map.tariff_id = '%s'  and (%s) ORDER BY priority asc, prefix   desc, rate ASC, end_time ASC limit 1;", $this->carrierdata['tariff_id'], $str);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) == 0) {
            $otherinfo = $this->incomingcarrier;
            $this->fail_route_xml_inbound('CARRIERRATESCLIISSUE', $otherinfo);
            return;
        }
        foreach ($rs[0] as $key => $value) {
            $this->carrierdata[$key] = $value;
        }
        $this->callfrom_did = 1;
        if ($this->carrierdata['reseller1_account_id'] != '' and $this->carrierdata['reseller1_account_id'] != null) {
            $rd = $this->DID_reseller($this->carrierdata, $did_after_carrier_dst_rule, 1, $route_callid);


            if ($this->status == 'FAIL') {
                return;
            }
        }

        if ($this->carrierdata['reseller2_account_id'] != '' and $this->carrierdata['reseller2_account_id'] != null) {
            $rd = $this->DID_reseller($this->carrierdata, $did_after_carrier_dst_rule, 2, $rd['callernumber']);

            if ($this->status == 'FAIL') {
                return;
            }
        }

        if ($this->carrierdata['reseller3_account_id'] != '' and $this->carrierdata['reseller3_account_id'] != null) {
            $rd = $this->DID_reseller($this->carrierdata, $did_after_carrier_dst_rule, 3, $rd['callernumber']);
            if ($this->status == 'FAIL') {
                return;
            }
        }

        if ($this->carrierdata['account_id'] != '' and $this->carrierdata['account_id'] != NULL) {
            if (strlen(trim($rd['callernumber'])) > 0) {
                
            } else {
                $rd['callernumber'] = $this->caller_number;
            }
            $this->DID_user($this->carrierdata, $this->incomingcarrierdst, $rd['callernumber']);
            $this->caller_number = $this->callernumber;
            if ($this->status == 'FAIL') {
                return;
            }
        }

        $this->carrierdata['src_caller'] = $this->caller_number;
        $this->carrierdata['src_callee'] = $this->incomingcarrierdst;
        $this->carrierdata['dst_caller'] = $this->caller_number;
        $this->carrierdata['dst_callee'] = $this->incomingcarrierdst;
        $this->carrierdata['ipaddress'] = $this->account_originator_ip;
        $this->carrierdata['ipaddress_name'] = $this->account_originator_ip;
        $route['rate'] = $this->carrierdata['rate'];
        $route['connection_charge'] = $this->carrierdata['connection_charge'];
        $route['tariff_currency_id'] = $this->carrierdata['tariff_currency_id'];
        $this->route22['carrier_id'] = $this->carrierdata['carrier_id'];
        $this->route22['carrier_name'] = $this->carrierdata['carrier_name'];
        $this->route22['carrier_currency_id'] = $this->carrierdata['carrier_currency_id'];
        $this->route22['ratecard_id'] = $this->carrierdata['ratecard_id'];
        $this->route22['tariff_id'] = $this->carrierdata['tariff_id'];
        $this->route22['prefix'] = $this->carrierdata['prefix'];
        $this->route22['destination'] = $this->carrierdata['destination'];
        $this->route22['rate'] = $this->carrierdata['rate'];
        $this->route22['src_caller'] = $this->carrierdata['src_caller'];
        $this->route22['src_callee'] = $this->carrierdata['src_callee'];
        $this->route22['dst_caller'] = $this->carrierdata['dst_caller'];
        $this->route22['dst_callee'] = $this->carrierdata['dst_callee'];
        $this->route22['ipaddress'] = $this->account_originator_ip;
        $this->route22['ipaddress_name'] = $this->account_originator_ip;
        $this->llr_check($route);
        $this->carrierdata['ratio'] = $this->ratio;
        $this->carrierdata_xml = str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE));
        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"ringback=\%(2000, 4000, 440.0, 480.0)\"/>";
        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"CALLTYPE=INCOMING\"/>";
        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"CALLTYPE=INCOMING\"/>";
        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"continue_on_fail=TRUE\"/>";
        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"bypass_media=false\"/>";
        if ($this->carrierdata['carrier_ring_timeout'] > 0) {
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"call_timeout=" . $this->carrierdata['carrier_ring_timeout'] . "\"/>";
            $call_timeout = $this->carrierdata['carrier_ring_timeout'];
            $call_timeout = $call_timeout - 4;
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"hangup_after_bridge=true\"/>";
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"continue_on_fail=true\"/>";
        } else {
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"call_timeout=60\"/>";
            $call_timeout = 55;
        }
        if ($this->carrierdata['carrier_progress_timeout'] > 0) {
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"progress_timeout=" . $this->carrierdata['carrier_progress_timeout'] . "\"/>";
        } else {
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"progress_timeout=5\"/>";
        }
        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
        if (strlen($this->callernumber) > 0) {
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"effective_caller_id_number=" . $this->callernumber . "\"/>";
        } else {
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"effective_caller_id_number=" . $route_callid . "\"/>";
        }
        if ($this->timeout > 0 and $this->timeout < 0) {
            $responce .= "\n<action application=\"set\" data=\"execute_on_answer=sched_hangup +" . $this->timeout . " alloted_timeout \"/>";
        } else {
            $responce .= "\n<action application=\"set\" data=\"execute_on_answer=sched_hangup +7200 alloted_timeout \"/>";
        }
        $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $this->request['variable_ep_codec_string'] . "\"/>";
        $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $this->request['variable_ep_codec_string'] . "\"/>";

        if (strlen($this->carrierdata['carrier_codecs']) > 0) {
            $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"inherit_codec=true\"/>";
            $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:absolute_codec_string=" . $this->carrierdata['carrier_codecs'] . "\"/>";
        } else {
            $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"inherit_codec=true\"/>";
            $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:absolute_codec_string=\${ep_codec_string}\"/>";
        }
        $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"sip_h_X-CARRIERID=DIDGATEWAY\"/>";
        $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"sip_h_X-CARRIERCPS=5000\"/>";

        $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:execute_on_ring=curl " . APIDOAMIN . "api/api.php?r=ring&common_uuid=" . $this->uuid . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&account=" . $this->carrierdata['account_id'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . " | -k \"/>";

        $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?r=ring&common_uuid=" . $this->uuid . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&account=" . $this->carrierdata['account_id'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . " | -k \"/>";

        $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?r=answer&common_uuid=" . $this->uuid . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&account=" . $this->carrierdata['account_id'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . " | -k \"/>";


        $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?r=answer&common_uuid=" . $this->uuid . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&account=" . $this->carrierdata['account_id'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . " | -k \"/>";


        $dst_type = Array('IP', 'PSTN', 'CUSTOMER');
        if (in_array($this->carrierdata['dst_type'], $dst_type)) {

            if ($this->carrierdata['dst_type'] == 'IP') {
                $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"sip_h_X-DSTURI=sip:" . $this->incomingcarrierdst . "@" . $this->carrierdata['dst_destination'] . "\"/>";
                $this->Gateway_XML_incoming .= "\n <action application=\"bridge\" data=\"sofia/internal/" . $this->incomingcarrierdst . "@" . $lb . "\"/>";
            } elseif ($this->carrierdata['dst_type'] == 'CUSTOMER') {
                $this->Gateway_XML_incoming .= "\n <action application=\"bridge\" data=\"{ignore_early_media=true,hangup_after_bridge=false,continue_on_fail=true}[leg_timeout=" . $call_timeout . "]sofia/internal/" . $this->carrierdata['dst_destination'] . "@" . $lb . "\"/>";

                if ($this->SYSTEM_VOICEMAIL == 1) {
                    $this->Gateway_XML_incoming .= "\n<action application=\"answer\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"voicemail_greeting_number=1\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"voicemail_alternate_greet_id=" . $this->incomingcarrierdst . "\"/>";
                    $this->Gateway_XML_incoming .= "\n   <action application=\"voicemail\" data=\"default \$\${domain} " . $this->carrierdata['dst_destination'] . "\" />";
                    $this->Gateway_XML_incoming .= "\n    <action application=\"hangup\"/>";
                }
            } elseif ($this->carrierdata['dst_type'] == 'PSTN') {

                $this->account_id = $this->carrierdata['account_id'];
                $this->destination_number = $this->carrierdata['dst_destination'];
                $this->destination_number_check = $this->destination_number_user = $this->destination_number;
                $this->src_callee = $this->destination_number;
                $this->src_caller = $this->callernumber_user = $this->callernumber;
                if ($this->callernumber != '' AND $this->callernumber != NULL) {
                    $this->src_caller = $this->callernumber_user = $this->callernumber = $route_callid;
                }
                $this->inboundcalls = 1;
                $this->PSTN_Call();
                if ($this->status == 'FAIL') {
                    
                } else {
                    $this->PSTN_routing();
                    if ($this->out_dialplan == '1') {
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"INCOMING2PSTN=1\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"INCOMING2PSTN=1\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"userdata=" . $this->rates . "\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"userdata=" . $this->rates . "\"/>";
                        $this->Gateway_XML_incoming .= $this->Gateway_XML;
                    }
                }
            }
            if ($this->carrierdata['dst_type2'] == 'IP') {
                $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"sip_h_X-DSTURI=sip:" . $this->incomingcarrierdst . "@" . $this->carrierdata['dst_destination2'] . "\"/>";
                $this->Gateway_XML_incoming .= "\n <action application=\"bridge\" data=\"sofia/internal/" . $this->incomingcarrierdst . "@" . $lb . "\"/>";
            } elseif ($this->carrierdata['dst_type2'] == 'CUSTOMER') {
                $this->Gateway_XML_incoming .= "\n <action application=\"bridge\" data=\"sofia/internal/" . $this->carrierdata['dst_destination2'] . "@" . $lb . "\"/>";
            } elseif ($this->carrierdata['dst_type2'] == 'PSTN') {
                $this->account_id = $this->carrierdata['account_id'];
                $this->destination_number = $this->carrierdata['dst_destination2'];
                $this->account_id = $this->carrierdata['account_id'];
                $this->src_callee = $this->destination_number = $this->carrierdata['dst_destination2'];
                $this->src_caller = $this->callernumber_user = $this->callernumber;
                if ($this->callernumber != '' AND $this->callernumber != NULL) {
                    $this->src_caller = $this->callernumber_user = $this->callernumber = $route_callid;
                }
                $this->PSTN_Call();
                if ($this->status == 'FAIL') {
                    
                } else {
                    $this->PSTN_routing();
                    if ($this->out_dialplan == '1') {
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"INCOMING2PSTN=1\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"INCOMING2PSTN=1\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"userdata=" . $this->rates . "\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"userdata=" . $this->rates . "\"/>";
                        $this->Gateway_XML_incoming .= $this->Gateway_XML;
                    } else {
                        
                    }
                }
            }
        }

        if ($this->carrierdata['dst_type1'] != 'PSTN' or $this->carrierdata['dst_type2'] != 'PSTN') {
            $this->livecalls_in();
        }

        $this->Gateway_XML = $this->Gateway_XML_incoming;
        return;
    }

    function DID_user($diddata, $incomingcarrierdst, $callernumber) {
        $user = $diddata['account_id'];
        $this->account_id = $user;
        $this->CCSTRING = $diddata['account_id'];
        $query = sprintf("SELECT  max_callduration, round_logic, account.account_id, status_id account_status, account.account_type, parent_account_id, dp, customer_voipminuts.tariff_id, account_cc, account_cps, tax1, tax2, tax3, tax_type, currency_id, cli_check, dialpattern_check, llr_check, account_codecs, media_transcoding  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id where account.account_id = '%s';", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
                $this->writelog("1st query $key $value");
            }
            $this->customers['account_id'] = $user;
        } else {
            $otherinfo = $user;
            $this->customers['account_id'] = $user;
            $this->fail_route_xml_inbound('USERINACTIVE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            return;
        }

        $this->timeout = $this->customers['max_callduration'] * 60;
        if ($this->carrierdata['dst_type'] == 'CUSTOMER') {
            $this->customers['device_type'] = 'u';
            $query = sprintf("SELECT customer_sip_account.id, customer_sip_account.username, customer_sip_account.`status`, customer_sip_account.account_id, customer_sip_account.sip_cc, customer_sip_account.sip_cps from customer_sip_account INNER JOIN account on customer_sip_account.account_id = account.account_id where username = '%s' and account.account_id = '%s' limit 1;", $user);

            $this->customers['device_id'] = str_replace('.', "", json_encode($this->customers['dst_destination'], JSON_UNESCAPED_UNICODE));
        } elseif ($this->carrierdata['dst_type'] == 'IP') {
            $this->customers['device_type'] = 'ip';
            $query = sprintf("SELECT customer_ips.id, customer_ips.ipaddress, customer_ips.account_id, customer_ips.ip_status, customer_ips.ip_cc, customer_ips.ip_cps from customer_ips  INNER JOIN account on customer_ips.account_id = account.account_id where ipaddress = '%s'  and account.account_id = '%s' limit 1;", $user);
            $this->customers['device_id'] = str_replace('.', "", json_encode($this->customers['dst_destination'], JSON_UNESCAPED_UNICODE));
        }


        $query = sprintf("select company_name , name  from customers where account_id = '%s';", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->customers[$key] = preg_replace("/[^A-Za-z0-9_ ()]/", '', $value);
        }
        $this->writelog($query);
        $this->customers['src_caller'] = $this->caller_number;
        $this->customers['src_callee'] = $this->carrierdata['dst_destination'];

        $this->customers['src_ip'] = $this->customers['ipaddress'];
        $this->customers['ipaddress'] = $this->customers['ipaddress'];

        /*
         * User status check
         */
        $this->customers['account_id'] = $user;

        $otherinfo = $this->account_id;
        if ($this->customers['account_status'] == 0) {
            $this->fail_route_xml_inbound('USERINACTIVE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            return;
        }
        $this->writelog($query);
        /*
         * Checking CLI when this option enabled
         */
        if ($this->customers['cli_check'] == '1') {
            /*
             * Check the CallerIDs
             */
            $route_callid = $this->DID_users_change_callid($this->customers, $callernumber);

            /*
             * User CLI is not allowed
             */
            $otherinfo = $this->account_id;
            if ($this->cli_auth == 0) {
                $this->fail_route_xml_inbound('USERCLI', $otherinfo);
                $this->status = 'FAIL';
                $this->customersdata['user'] = $this->customers;
                $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
                return;
            }
        }
        $this->writelog($query);
        /*
         * Checking the user balance
         */
        $query = sprintf("SELECT id, credit_limit - balance as balance from customer_balance where account_id = '%s';", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->customers[$key] = $value;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
        }

        $balancelog = "$user user balance befor process is " . $this->customers['balance'];
        $this->writelog($balancelog);

        foreach ($this->currencies as $currency) {
            if ($currency['currency_id'] == $this->customers['account_currency_id']) {
                $account_currency_ratio = $this->currencies['ratio'];
            }
        }
        $this->account_currency_ratio = $account_currency_ratio;
        /*
         * User balance issue
         */
        $otherinfo = $this->account_id;
        if ($this->customers['balance'] < 0.1) {
            $this->fail_route_xml_inbound('USERBALANCE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            return;
        }
        /*
         * Checking User Rates
         */

        $dstarray = str_split($this->destination_number);
        $tmpstr = '';
        $str = '';
        foreach ($dstarray as $key => $value) {
            $tmpstr .= $value;
            $str .= "prefix = '" . $tmpstr . "' or ";
        }

        $str = rtrim($str, ' or ');
        $query = sprintf("SELECT tariff.tariff_status,  tariff_ratecard_map.ratecard_id, tariff_ratecard_map.tariff_id, tariff_ratecard_map.start_day, tariff_ratecard_map.end_day, tariff_ratecard_map.start_time, tariff_ratecard_map.end_time, customer_rates.prefix, REPLACE(REPLACE( customer_rates.destination,',',' '),'-',' ') as destination , customer_rates.rate, customer_rates.connection_charge, customer_rates.minimal_time, customer_rates.resolution_time, customer_rates.grace_period, customer_rates.rate_multiplier, customer_rates.rate_addition, customer_rates.rates_status, tariff.tariff_currency_id, tariff.tariff_currency_id  FROM tariff_ratecard_map  INNER JOIN customer_rates on customer_rates.ratecard_id = tariff_ratecard_map.ratecard_id    INNER JOIN ratecard on customer_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'INCOMING' INNER JOIN tariff on  tariff.tariff_id = tariff_ratecard_map.tariff_id where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and tariff_ratecard_map.tariff_id = '%s' and (%s)  ORDER BY priority asc, prefix desc, rate ASC, end_time ASC limit 1;", $this->customers['tariff_id'], $str);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        /*
         * if user rates is not available
         */

        $otherinfo = $this->account_id;
        if (count($rs) == 0) {
            $this->fail_route_xml_inbound('USERRATE', $otherinfo);
            $this->status = 'FAIL';
            $this->customers['src_callee'] = $this->destination_number;
            $this->customersdata['user'] = $this->customers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            return;
        }

        foreach ($rs[0] as $key => $value) {
            $this->customers[$key] = $value;
        }
        /*
         * Assign the rates value to LLRRate variable to check the LLR in reseler and carrier
         */
        $this->LLRRates = $user['rate'] + $user['connection_charge'];
        $this->writelog("Calleer Rate  RAW user " . $this->LLRRates);

        /*
         * User tariff is inactive
         */
        $otherinfo = $this->account_id;
        if ($this->customers['tariff_status'] == 0) {
            $this->fail_route_xml_inbound('USERTARIFFINACTIVE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            return;
        }

        /*
         * User rates is blocked
         */
        $otherinfo = $this->account_id;
        if ($this->customers['rates_status'] == 0) {
            $this->fail_route_xml_inbound('USERRATEBLOCKED', $otherinfo);
            $this->status = 'FAIL';
            $this->customers['src_callee'] = $this->destination_number;
            $this->customersdata['user'] = $this->customers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            return;
        }

        $this->DID_DNIS($incomingcarrierdst, $this->account_id);

        /*
         * Bundle & Plan  bundle_option
         */
        $this->bundle_package_management($user);
        $query = sprintf("SELECT bundle_option,  bundle_id, prefix , bundle_package_prefixes.bundle_package_id, monthly_charges,   bundle1_type, bundle1_value, bundle2_type, bundle2_value, bundle3_type, bundle3_value from bundle_package INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = bundle_package.bundle_package_id  where bundle_package_prefixes.bundle_package_id in (select bundle_package_id from bundle_account where account_id  = '%s') and  (%s) and LENGTH(prefix) > 0  order by prefix desc limit 1;", $user, $str);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
            }
        }
        if (strlen($this->customers['bundle_package_id']) > 0 and $this->customers['bundle_option'] == '1') {
            $query = sprintf("SELECT account_id, rule_type, yearmonth,  sum(total_allowed) as  total_allowed , sum(sdr_consumption) as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s' and rule_type in ('bundle1', 'bundle2', 'bundle2') and '%s'  BETWEEN service_startdate and service_stopdate ;", $user, $this->customers['bundle_package_id'], date("Y-m-d"));
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs3 = $this->resultset();
            $available_bundle1 = '';
            $available_bundle2 = '';
            $available_bundle3 = '';
            if (count($rs3) > 0) {
                foreach ($rs3 as $data_b) {
                    if ($data_b['rule_type'] == 'bundle1') {
                        $available_bundle1 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                        $this->writelog("available_bundle1 $available_bundle1");
                        $insert_allow = 0;
                    }
                    if ($data_b['rule_type'] == 'bundle2') {
                        $available_bundle2 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                        $this->writelog("available_bundle2 $available_bundle2");
                        $insert_allow = 0;
                    }
                    if ($data_b['rule_type'] == 'bundle3') {
                        $available_bundle3 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                        $this->writelog("available_bundle3 $available_bundle3");
                        $insert_allow = 0;
                    }
                }
            }

            foreach ($rs as $data) {
                if ($data['bundle_id'] == '1') {
                    $bundle1 = $this->customers['bundle1_value'];
                    if ($this->customers['bundle1_type'] == 'MINUTE') {
                        $duration_b = $available_bundle1;
                        $this->customers['bundle_type'] = 'MINUTE';
                        $this->customers['bundle_value'] = $duration_b;
                        $this->customers['bundle_number'] = 'bundle1';
                    }
                    if ($this->customers['bundle1_type'] == 'COST') {
                        $balance_b = $available_bundle1;
                        $this->customers['bundle_type'] = 'COST';
                        $this->customers['bundle_value'] = $balance_b;
                        $this->customers['bundle_number'] = 'bundle1';
                    }
                    $this->writelog("available_bundle1 " . $this->customers['bundle_value']);
                } elseif ($data['bundle_id'] == '2') {
                    $bundle2 = $this->customers['bundle2_value'];
                    if ($this->customers['bundle2_type'] == 'MINUTE') {
                        $duration_b = $available_bundle2;
                        $this->customers['bundle_type'] = 'MINUTE';
                        $this->customers['bundle_value'] = $duration_b;
                        $this->customers['bundle_number'] = 'bundle2';
                    }
                    if ($this->customers['bundle2_type'] == 'COST') {
                        $balance_b = $available_bundle2;
                        $this->customers['bundle_type'] = 'COST';
                        $this->customers['bundle_value'] = $balance_b;
                        $this->customers['bundle_number'] = 'bundle2';
                    }
                } elseif ($data['bundle_id'] == '3') {
                    $bundle1 = $this->customers['bundle3_value'];
                    if ($this->customers['bundle3_type'] == 'MINUTE') {
                        $duration_b = $available_bundle3;
                        $this->customers['bundle_type'] = 'MINUTE';
                        $this->customers['bundle_value'] = $duration_b;
                        $this->customers['bundle_number'] = 'bundle3';
                    }
                    if ($this->customers['bundle3_type'] == 'COST') {
                        $balance_b = $available_bundle3;
                        $this->customers['bundle_type'] = 'COST';
                        $this->customers['bundle_value'] = $balance_b;
                        $this->customers['bundle_number'] = 'bundle3';
                    }
                }
            }
        }


        /*
         * Building the users billing infomation Array which will pass in CDR event
         */

        unset($this->customers['account_status']);
        unset($this->customers['cli_check']);
        unset($this->customers['dialpattern_check']);
        unset($this->customers['llr_check']);
        unset($this->customers['tariff_status']);
        unset($this->customers['end_time']);
        unset($this->customers['start_time']);
        unset($this->customers['end_day']);
        unset($this->customers['start_day']);
        unset($this->customers['rates_status']);
        unset($this->customers['account_ip_id']);
        unset($this->customers['dial_prefix']);
        unset($this->customers['ip_status']);
        unset($this->customers['ip_cc']);
        unset($this->customers['ip_cps']);
        $this->customersdata['user'] = $this->customers;

        /*
         *  $this->writelog("$key $value");
         * building User and reseller billing data Array for CDR event
         */
        $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
        /*
         * Users call duration calculations
         */
        $this->customers['duration'] = 0;

        if ($balance_b > 0 AND $this->customers['bundle_type'] == 'COST') {
            $this->customers['balance'] = $this->customers['balance'] + $balance_b;
        }

        $this->customers['duration'] = $this->duration($this->customers);
        $this->writelog("Normal duration " . $this->customers['duration']);
        $this->writelog("Bundle duration " . $duration_b);


        if ($duration_b > 0) {
            $this->customers['duration'] = $this->customers['duration'] + $duration_b;
        }
        $this->writelog("Normal duration " . $this->customers['duration']);


        /*
         * Call Duration is zero. maybe rates are higher compared to balance
         */
        $otherinfo = $this->account_id;
        if ($this->customers['duration'] == 0) {
            $this->fail_route_xml_inbound('USERBALANCE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            return;
        }

        $this->dst_caller = $route_callid;
        $this->carrierdata['dst_caller'] = $route_callid;
        $this->CCSTRING = $this->CCSTRING . ":" . $device_cc;
        $this->writelog("Device Running CPS $device_cps ------ " . $result);
        $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
        return;
    }

    function sign($number) {
        return ( $number > 0 ) ? 1 : ( ( $number < 0 ) ? -1 : 0 );
    }

    function bundle_package_management($account_id) {
        $query = sprintf("SELECT bundle_account.bundle_package_id, bundle_account.account_id, bundle_account.assign_dt, bundle_account.account_bundle_key, bundle_account.bundle_package_desc, if(MONTH(assign_dt) = MONTH(CURDATE()),1,0) AS isthismonthbuy, bundle_package.monthly_charges, bundle_package.bundle_option, bundle_package.bundle1_type, bundle_package.bundle1_value, bundle_package.bundle2_type, bundle_package.bundle2_value, bundle_package.bundle3_type, bundle_package.bundle3_value, bundle_package.bundle_package_id, bundle_package.bundle_package_name FROM bundle_account INNER JOIN bundle_package on bundle_package.bundle_package_id = bundle_account.bundle_package_id where bundle_account.account_id = '%s' and account_bundle_key not in ( select account_bundle_key from customer_bundle_sdr where account_id = '%s' and '%s'  BETWEEN service_startdate and service_stopdate);", $account_id, $account_id, date("Y-m-d"));
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $unassign_bundledata = $this->resultset();
        foreach ($unassign_bundledata as $unassign_bundle) {
            if (strlen($unassign_bundle['bundle_package_id']) > 0 and strlen($unassign_bundle['account_id']) > 0) {
                $query = sprintf("SELECT plugin_system_name FROM `plugins` where plugin_system_name = 'billing';");

                $this->writelog($query);
                $this->query('SWITCH', $query);
                $billingmodule = $this->resultset();
                if (count($billingmodule) > 0) {
                    foreach ($billingmodule as $billingdata) {
                        $plugin_system_name = $billingdata['plugin_system_name'];
                    }
                }
                if ($plugin_system_name != 'billing') {
                    $service_startdate = date('Y-m-01');
                    $service_stopdate = date('Y-m-t');
                } else {
                    $query = sprintf("SELECT billing_day FROM bill_customer_priceplan where account_id = '%s';", $account_id);

                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $billingmodule = $this->resultset();
                    if (count($billingmodule) > 0) {
                        foreach ($billingmodule as $billingdata) {
                            if ($billingdata['billing_day'] < 10) {
                                $day = '0' . $billingdata['billing_day'];
                            } else {
                                $day = $billingdata['billing_day'];
                            }

                            $date = date("Y-m-$day");
                            $service_startdate = $date;
                            $service_stopdate = date('Y-m-d', strtotime($date . ' +1 month'));
                        }
                    } else {
                        $service_startdate = date('Y-m-01');
                        $service_stopdate = date('Y-m-t');
                    }
                }
                /*
                 * Bundle 1 Package free minute / cost setup. If there is no free minute of cost then sdr will not generate
                 */
                $this->writelog("service_startdate $service_startdate  service_stopdate $service_stopdate");



                if ($unassign_bundle['bundle1_value'] == '' or $unassign_bundle['bundle1_value'] == null) {
                    $unassign_bundle['bundle1_value'] = 0;
                }
                if ($unassign_bundle['bundle1_value'] > 0) {
                    $this->writelog("B1");
                    $bundle1_value = $unassign_bundle['bundle1_value'];
                    if ($unassign_bundle['bundle1_type'] == 'MINUTE')
                        $bundle1_value = floor($bundle1_value);
                    $query = sprintf("INSERT INTO customer_bundle_sdr (account_id, rule_type, yearmonth,bundle_type,total_allowed,sdr_consumption,action_date,account_bundle_key,bundle_package_id,service_startdate, service_stopdate,bundle_package_name) VALUES('%s','%s','%s','%s','%s','%s',now(),'%s','%s','%s','%s','%s')", $unassign_bundle['account_id'], 'bundle1', date("Ym"), $unassign_bundle['bundle1_type'], $bundle1_value, '0', $unassign_bundle['account_bundle_key'], $unassign_bundle['bundle_package_id'], $service_startdate, $service_stopdate, $unassign_bundle['bundle_package_name']);
                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $this->execute();
                }
                /*
                 * Bundle 2 Package free minute / cost setup
                 */
                if ($unassign_bundle['bundle2_value'] == '' or $unassign_bundle['bundle2_value'] == null) {
                    $unassign_bundle['bundle2_value'] = 0;
                }
                if ($unassign_bundle['bundle2_value'] > 0) {
                    $this->writelog("B2");
                    $bundle2_value = $unassign_bundle['bundle2_value'];
                    if ($unassign_bundle['bundle2_type'] == 'MINUTE')
                        $bundle2_value = floor($bundle2_value);
                    $query = sprintf("INSERT INTO customer_bundle_sdr (account_id, rule_type, yearmonth,bundle_type,total_allowed,sdr_consumption,action_date,account_bundle_key,bundle_package_id,service_startdate, service_stopdate,bundle_package_name) VALUES('%s','%s','%s','%s','%s','%s',now(),'%s','%s','%s','%s','%s');", $unassign_bundle['account_id'], 'bundle2', date("Ym"), $unassign_bundle['bundle2_type'], $bundle2_value, '0', $unassign_bundle['account_bundle_key'], $unassign_bundle['bundle_package_id'], $service_startdate, $service_stopdate, $unassign_bundle['bundle_package_name']);


                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $this->execute();
                }

                /*
                 * Bundle 3 Package free minute / cost setup
                 */
                if ($unassign_bundle['bundle3_value'] == '' or $unassign_bundle['bundle3_value'] == null) {
                    $unassign_bundle['bundle3_value'] = 0;
                }
                if ($unassign_bundle['bundle3_value'] > 0) {
                    $this->writelog("B3");
                    $bundle3_value = $unassign_bundle['bundle3_value'];
                    if ($unassign_bundle['bundle3_type'] == 'MINUTE')
                        $bundle3_value = floor($bundle3_value);
                    $query = sprintf("INSERT INTO customer_bundle_sdr (account_id, rule_type, yearmonth,bundle_type,total_allowed,sdr_consumption,action_date,account_bundle_key,bundle_package_id,service_startdate, service_stopdate,bundle_package_name) VALUES('%s','%s','%s','%s','%s','%s',now(),'%s','%s','%s','%s','%s')", $unassign_bundle['account_id'], 'bundle3', date("Ym"), $unassign_bundle['bundle3_type'], $bundle3_value, '0', $unassign_bundle['account_bundle_key'], $unassign_bundle['bundle_package_id'], $service_startdate, $service_stopdate, $unassign_bundle['bundle_package_name']);
                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $this->execute();
                }
            }
        }
    }

    function charges_cal_bundle($charges, $date) {
        $no_of_days = date('t', strtotime($date));
        $current_day = date('d', strtotime($date));
        $billingdays = ($no_of_days - $current_day) + 1;
        $current_month_charges = ($charges / $no_of_days) * $billingdays;
        return $current_month_charges;
    }

    function DID_DNIS($incomingcarrierdst, $account_id) {
        /*
         *  Dialed number manupulation
         */
        $this->account_id = $account_id;
        $this->destination_number = $dialnumber1 = $incomingcarrierdst;

        $query = sprintf("SELECT LENGTH(maching_string) lndata,  match_length, account_id, maching_string, remove_string, display_string, add_string, action_type  FROM customer_dialpattern where account_id = '%s' and '%s' like maching_string and route = 'INBOUND' ORDER BY lndata desc limit 1;", $account_id, $dialnumber1);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->account_dialpattern[$key] = $value;
        }
        if (count($this->account_dialpattern) > 0 and $this->account_dialpattern['action_type'] == '0') {
            /*
             * Dialed number pattern is black.
             */
            $status = '1';
            if ($this->account_dialpattern['match_length'] > 0) {
                if ($this->account_dialpattern['match_length'] == strlen(trim($dialnumber1))) {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml_inbound('USERDIALEDNUMBERBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }
        }

        if (count($this->account_dialpattern) == 0) {
            /*
             * Dialed number pattern is black.
             */
            $otherinfo = $this->account_id;
            $this->fail_route_xml_inbound('USERDIALEDNUMBER', $otherinfo);
            $this->status = 'FAIL';
            return;
        }

        $route2 = $this->account_dialpattern;
        $dialnumber1 = $this->destination_number;
        if (count($route2) > 0) {
            $status = '0';
            if ($route2['match_length'] > 0) {
                if ($route2['match_length'] == strlen(trim($dialnumber1))) {
                    $status = '0';
                } else {
                    $status = '1';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('USERDIALEDNUMBERBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }
            $remove_string = $route2['remove_string'];
            $add_string = $route2['add_string'];
            if (trim($remove_string) != '') {
                if (substr($dialnumber1, 0, strlen($remove_string)) == $remove_string AND $remove_string != NULL AND $remove_string != '' AND $remove_string != '%') {
                    $dialnumber1 = substr($dialnumber1, strlen($remove_string), strlen($dialnumber1));
                }
            }

            if ($add_string == '%' or $add_string == '') {
                $dialnumber1 = trim($dialnumber1);
            } else if (substr(trim($add_string), -1) == '%') {
                $dialnumber1 = trim($add_string . trim($dialnumber1));
                $dialnumber1 = preg_replace("/%/", "", $dialnumber1);
            } else {
                $dialnumber1 = trim($add_string);
            }
        }
        $this->incomingcarrierdst = $this->destination_number_user = $this->destination_number = $dialnumber1;
    }

    function DID_reseller($diddata, $incomingcarrierdst, $account_level, $callernumber) {
        $reseelerinfo = 'Reseller' . $account_level;
        if ($account_level == '1')
            $reseller_id = $diddata['reseller1_account_id'];
        if ($account_level == '2')
            $reseller_id = $diddata['reseller2_account_id'];
        if ($account_level == '3')
            $reseller_id = $diddata['reseller3_account_id'];
        $query = sprintf("SELECT  round_logic, account.account_id, account.status_id account_status, account.account_type, account.account_level, account.parent_account_id, account.dp,  customer_voipminuts.tariff_id, account.account_cc, account.account_cps, account.tax1, account.tax2, account.tax3, account.tax_type, account.cli_check, account.dialpattern_check,  account.llr_check, account.account_codecs, account.media_transcoding, account.media_rtpproxy, account.currency_id from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id where account.account_id = '%s' and account_level = '%s' limit 1;", $reseller_id, $account_level);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();


        foreach ($rs[0] as $key => $value) {
            $resellerusers[$key] = $value;
        }
        $this->customersdata[$reseelerinfo] = $resellerusers;

        /*
         * User status check
         */
        $otherinfo = $reseller_id;
        if ($resellerusers['account_status'] == 0) {
            $this->fail_route_xml_inbound('RESELLERINACTIVE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata[$reseelerinfo] = $resellerusers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            $this->writelog($this->rates_incoming);
            return;
        }

        /*
         * Checking CLI when this option enabled
         */
        if ($resellerusers['cli_check'] == '1') {
            /*
             * Check the CallerIDs
             */
            $callernumber = $this->DID_users_change_callid($resellerusers, $callernumber);

            /*
             * User CLI is not allowed
             */
            $otherinfo = $reseller_id;
            if ($this->cli_auth == 0) {
                $this->fail_route_xml_inbound('RESELLERCLI', $otherinfo);
                $this->status = 'FAIL';
                $this->customersdata[$reseelerinfo] = $resellerusers;
                $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
                $this->writelog($this->rates_incoming);
                return;
            }
        }

        /*
         * Checking the user balance
         */
        $query = sprintf("SELECT id, credit_limit - balance balance from customer_balance where account_id = '%s';", $reseller_id);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $resellerusers[$key] = $value;
        }
        $this->customersdata[$reseelerinfo] = $resellerusers;
        foreach ($this->currencies as $currency) {
            if ($currency['currency_id'] == $resellerusers['currency_id']) {
                $account_currency_ratio = $this->currencies['ratio'];
            }
        }
        $this->account_currency_ratio = $account_currency_ratio;

        if (($resellerusers['balance'] / $this->account_currency_ratio) < 5) {
            $resellerusers['balance'] = ($resellerusers['balance'] - (100 - $resellerusers['balance']) * $resellerusers['balance'] / 100);
        }

        /*
         * Reseller balance issue
         */
        $otherinfo = $reseller_id;
        if ($resellerusers['balance'] < 0.1) {
            $this->fail_route_xml_inbound('USERBALANCE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata[$reseelerinfo] = $resellerusers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            $this->writelog($this->rates_incoming);
            return;
        }
        /*
         * Checking User Rates
         */


        $dstarray = str_split($incomingcarrierdst);
        $tmpstr = '';
        $str = '';
        foreach ($dstarray as $key => $value) {
            $tmpstr .= $value;
            $str .= "prefix = '" . $tmpstr . "' or ";
        }

        $str = rtrim($str, ' or ');

        $query = sprintf("SELECT   tariff.tariff_status, tariff_ratecard_map.id, tariff_ratecard_map.ratecard_id, tariff_ratecard_map.tariff_id, tariff_ratecard_map.start_day, tariff_ratecard_map.end_day, tariff_ratecard_map.start_time, tariff_ratecard_map.end_time, customer_rates.prefix,  REPLACE(REPLACE( customer_rates.destination,',',' '),'-',' ') as destination   , customer_rates.rate, customer_rates.connection_charge, customer_rates.minimal_time, customer_rates.resolution_time, customer_rates.grace_period, customer_rates.rate_multiplier, customer_rates.rate_addition, customer_rates.rates_status, tariff.tariff_currency_id, tariff.tariff_currency_id  FROM tariff_ratecard_map  INNER JOIN customer_rates on customer_rates.ratecard_id = tariff_ratecard_map.ratecard_id    INNER JOIN ratecard on customer_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'INCOMING' INNER JOIN tariff on  tariff.tariff_id = tariff_ratecard_map.tariff_id where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and tariff_ratecard_map.tariff_id = '%s' and (%s) ORDER BY priority asc, prefix desc,  rate ASC, end_time ASC limit 1;", $resellerusers['tariff_id'], $str);


        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $resellerusers[$key] = $value;
        }


        /*
         * Bundle & Plan
         */
        $this->bundle_package_management($user);
        $query = sprintf("SELECT bundle_option, bundle_id, prefix , bundle_package_prefixes.bundle_package_id, monthly_charges,   bundle1_type, bundle1_value, bundle2_type, bundle2_value, bundle3_type, bundle3_value from bundle_package INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = bundle_package.bundle_package_id  where bundle_package_prefixes.bundle_package_id in (select bundle_package_id from bundle_account where account_id  = '%s') and  (%s) and LENGTH(prefix) > 0  order by prefix desc limit 1;", $reseller_id, $str);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $resellerusers[$key] = $value;
            }
        }

        if (strlen($resellerusers['bundle_package_id']) > 0 and $resellerusers['bundle_option'] == '1') {
            $query = sprintf("SELECT account_id, rule_type, yearmonth,  sum(total_allowed) as  total_allowed , sum(sdr_consumption) as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s' and rule_type in ('bundle1', 'bundle2', 'bundle2') and '%s'  BETWEEN service_startdate and service_stopdate;", $reseller_id, $resellerusers['bundle_package_id'], date("Y-m-d"));

            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs3 = $this->resultset();
            $available_bundle1 = '';
            $available_bundle2 = '';
            $available_bundle3 = '';
            $insert_allow = 1;
            if (count($rs3) > 0) {
                foreach ($rs3 as $data_b) {
                    if ($data_b['rule_type'] == 'bundle1') {
                        $available_bundle1 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                        $insert_allow = 0;
                    }
                    if ($data_b['rule_type'] == 'bundle2') {
                        $available_bundle2 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                        $insert_allow = 0;
                    }
                    if ($data_b['rule_type'] == 'bundle3') {
                        $available_bundle3 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                        $insert_allow = 0;
                    }
                }
            }

            foreach ($rs as $data) {
                if ($data['bundle_id'] == '1') {
                    $bundle1 = $resellerusers['bundle1_value'];
                    if ($resellerusers['bundle1_type'] == 'MINUTE') {
                        $duration_b = $available_bundle1;
                        $resellerusers['bundle_type'] = 'MINUTE';
                        $resellerusers['bundle_value'] = $duration_b;
                        $resellerusers['bundle_number'] = 'bundle1';
                    }
                    if ($resellerusers['bundle1_type'] == 'COST') {
                        $balance = $available_bundle1;
                        $resellerusers['bundle_type'] = 'COST';
                        $resellerusers['bundle_value'] = $balance_b;
                        $resellerusers['bundle_number'] = 'bundle1';
                    }
                } elseif ($data['bundle_id'] == '2') {
                    $bundle2 = $resellerusers['bundle2_value'];
                    if ($resellerusers['bundle2_type'] == 'MINUTE') {
                        $duration_b = $available_bundle2;
                        $resellerusers['bundle_type'] = 'MINUTE';
                        $resellerusers['bundle_value'] = $duration_b;
                        $resellerusers['bundle_number'] = 'bundle2';
                    }
                    if ($resellerusers['bundle2_type'] == 'COST') {
                        $balance = $available_bundle2;
                        $resellerusers['bundle_type'] = 'COST';
                        $resellerusers['bundle_value'] = $balance_b;
                        $resellerusers['bundle_number'] = 'bundle2';
                    }
                } elseif ($data['bundle_id'] == '3') {
                    $bundle1 = $resellerusers['bundle3_value'];
                    if ($resellerusers['bundle3_type'] == 'MINUTE') {
                        $duration_b = $available_bundle3;
                        $resellerusers['bundle_type'] = 'MINUTE';
                        $resellerusers['bundle_value'] = $duration_b;
                        $resellerusers['bundle_number'] = 'bundle3';
                    }
                    if ($resellerusers['bundle3_type'] == 'COST') {
                        $balance_b = $available_bundle3;
                        $resellerusers['bundle_type'] = 'COST';
                        $resellerusers['bundle_value'] = $balance_b;
                        $resellerusers['bundle_number'] = 'bundle3';
                    }
                }
            }
        }


        $this->customersdata[$reseelerinfo] = $resellerusers;
        $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
        $this->writelog($this->rates_incoming);
        $llr = $resellerusers['rate'] + $resellerusers['connection_charge'];
        if ($this->LLRRates <= $llr) {
            $this->LLRRates = $llr;
        } else {
            $otherinfo = $resellerusers['account_id'];
            $this->fail_route_xml_inbound('RESELLERLLRISSUE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata[$reseelerinfo] = $resellerusers;
            return;
        }

        $rd['parent_account_id'] = $resellerusers['parent_account_id'];
        $rd['callernumber'] = $callernumber;
        return $rd;
    }

    function currencies_data() {
        $query = sprintf("SELECT id, currency_id,ratio,date  from sys_currencies_conversions where id in (select max(id) from sys_currencies_conversions group by currency_id) order by id desc;");
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $this->currencies = $this->resultset();
    }

    function PSTN_Call() {
        $this->currencies_data();
        $user = $this->account_id;
        $this->CCSTRING = $user;
        $query = sprintf("SELECT round_logic,   max_callduration, codecs_force, force_dst_src_cli_prefix, account.account_id, status_id  account_status, account.account_type, parent_account_id, dp, customer_voipminuts.tariff_id, account_cc, account_cps, tax1, tax2, tax3, tax_type, currency_id, cli_check, dialpattern_check, llr_check, account_codecs, media_transcoding, media_rtpproxy  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id where account.account_id = '%s';", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->customers[$key] = $value;
        }

        $this->customersdata['user'] = $this->customers;
        $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));

        $query = sprintf("select company_name , name  from customers where account_id = '%s';", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->customers[$key] = preg_replace("/[^A-Za-z0-9_ ()]/", '', $value);
        }


        $this->customersdata['user'] = $this->customers;
        $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));

        if (strlen($this->customers['company_name']) == 0) {
            $this->customers['company_name'] = $this->customers['name'];
        }

        $this->customers['src_caller'] = $this->src_caller;
        $this->customers['src_callee'] = $this->src_callee;
        $this->customers['device_id'] = $this->account_device_id;
        $this->customers['device_type'] = $this->account_device_type;
        $this->customers['src_ip'] = $this->account_originator_ip;
        $this->customers['ipaddress'] = $this->account_originator_ip;
        $this->customersdata['user'] = $this->customers;
        $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
        /*
         * User status check
         */
        $otherinfo = $this->account_id;
        if ($this->customers['account_status'] != '1') {
            $this->fail_route_xml('USERINACTIVE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            return;
        }
        /*
         * Codec Checking
         */

        if ($this->customers['codecs_force'] == '1') {
            $codes_list = explode(',', $this->customers['account_codecs']);
            $this->status = 'FAIL';
            foreach ($codes_list as $codec) {
                if (strstr($this->sdp, $codec)) {
                    $this->status = 'OK';
                }
            }
            $otherinfo = $this->account_id;
            if ($this->status == 'FAIL') {
                $this->fail_route_xml('CODECNOTALLOWED', $otherinfo);
                $this->status = 'FAIL';
                $this->customersdata['user'] = $this->customers;
                return;
            }
        }

        $this->internalcall();
        if ($this->is_internalcall == '0') {
            /*
             * Checking dialplan when this option enabled
             */

            /*
             * Check the User Dialplan and dialed prefix blocking
             */
            $this->status = 'OK';
            $this->PSTN_customer_dialpattern();

            $this->writelog("Outgoing user dialplan status " . $this->status);

            /*
             * User Dialplan not configured.
             */
            if ($this->status == 'FAIL') {
                $this->customersdata['user'] = $this->customers;
                return;
            }


            /*
             * Checking CLI when this option enabled
             */
            if ($this->customers['cli_check'] == '1') {
                /*
                 * Check the CallerIDs
                 */
                $this->PSTN_customer_callerid();
                /*
                 * User CLI is not allowed
                 */
                $otherinfo = $this->account_id;
                if ($this->cli_auth == 0) {
                    $this->fail_route_xml('USERCLI', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customersdata['user'] = $this->customers;
                    return;
                }
            }


            /*
             * Checking CLI based on Destination when this option enabled
             */
            if ($this->customers['force_dst_src_cli_prefix'] == '1') {
                $this->force_dst_src_cli_prefix();
            }
        }


        $this->vmaccess_internalcall();
        if ($this->is_vmaccess_internalcall == '0') {
            $this->internalcall();
            if ($this->is_internalcall == '0') {
                /*
                 * Checking the user balance
                 */
                $query = sprintf("SELECT id, credit_limit - balance as 'balance'  from customer_balance where account_id = '%s';", $user);

                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs = $this->resultset();
                foreach ($rs[0] as $key => $value) {
                    $this->customers[$key] = $value;
                }

                if ($this->customers['balance'] < 0.1) {
                    $this->fail_route_xml('USERBALANCE', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customersdata['user'] = $this->customers;
                    return;
                }

                if ($this->sign($this->customers['balance']) == '-1')
                    $this->customers['orgbalance'] = abs($this->customers['balance']);
                else
                    $this->customers['orgbalance'] = "-" . $this->customers['balance'];

                $balancelog = "$user user balance befor process is " . $this->customers['balance'];
                $this->writelog($balancelog);

                foreach ($this->currencies as $currency) {
                    if ($currency['currency_id'] == $this->customers['currency_id']) {
                        $account_currency_ratio = $this->currencies['ratio'];
                    }
                }
                $this->account_currency_ratio = $account_currency_ratio;

                $otherinfo = $this->account_id;
                if ($this->customers['balance'] < 0.1) {
                    $this->fail_route_xml('USERBALANCE', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customersdata['user'] = $this->customers;
                    return;
                }
                /*
                 * Checking User Rates
                 */

                $this->billing_destination_number = $this->destination_number;
                $dstarray = str_split($this->billing_destination_number);
                $tmpstr = '';
                $str = '';
                foreach ($dstarray as $key => $value) {
                    $tmpstr .= $value;
                    $str .= "prefix = '" . $tmpstr . "' or ";
                }

                $str = rtrim($str, ' or ');
                $query = sprintf("SELECT   tariff.tariff_status,  tariff_ratecard_map.ratecard_id, tariff_ratecard_map.tariff_id, tariff_ratecard_map.start_day, tariff_ratecard_map.end_day, tariff_ratecard_map.start_time, tariff_ratecard_map.end_time, customer_rates.prefix,   REPLACE(REPLACE( customer_rates.destination,',',' '),'-',' ') as destination, customer_rates.rate, customer_rates.connection_charge, customer_rates.minimal_time, customer_rates.resolution_time, customer_rates.grace_period, customer_rates.rate_multiplier, customer_rates.rate_addition, customer_rates.rates_status, tariff.tariff_currency_id, tariff.tariff_currency_id  FROM tariff_ratecard_map  INNER JOIN customer_rates on customer_rates.ratecard_id = tariff_ratecard_map.ratecard_id    INNER JOIN ratecard on customer_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'OUTGOING' INNER JOIN tariff on  tariff.tariff_id = tariff_ratecard_map.tariff_id where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and tariff_ratecard_map.tariff_id = '%s' and (%s)  ORDER BY priority asc, prefix desc,   rate ASC, end_time ASC limit 1;", $this->customers['tariff_id'], $str);
                $this->writelog("PSTN_CALL   " . $query);
                $this->query('SWITCH', $query);
                $rs = $this->resultset();
                /*
                 * if user rates is not available
                 */

                $otherinfo = $this->account_id;
                if (count($rs) == 0) {
                    $this->fail_route_xml('USERRATE', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customers['src_callee'] = $this->destination_number;
                    $this->customersdata['user'] = $this->customers;
                    return;
                }

                foreach ($rs[0] as $key => $value) {
                    if ($key == 'destination')
                        $this->customers[$key] = preg_replace("/[^A-Za-z0-9_ ()]/", '', $value);
                    else
                        $this->customers[$key] = $value;
                }
                /*
                 * Bundle & Plan
                 */
                $this->bundle_package_management($user);
                $query = sprintf("SELECT  bundle_option, bundle_id, prefix , bundle_package_prefixes.bundle_package_id, monthly_charges,   bundle1_type, bundle1_value, bundle2_type, bundle2_value, bundle3_type, bundle3_value from bundle_package INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = bundle_package.bundle_package_id  where bundle_package_prefixes.bundle_package_id in (select bundle_package_id from bundle_account where account_id  = '%s') and  (%s) and LENGTH(prefix) > 0  order by prefix desc limit 1;", $this->account_id, $str);

                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs = $this->resultset();
                if (count($rs) > 0) {
                    foreach ($rs[0] as $key => $value) {
                        $this->customers[$key] = $value;
                    }
                }
                if (strlen($this->customers['bundle_package_id']) > 0 and $this->customers['bundle_option'] == '1') {
                    $query = sprintf("SELECT account_id, rule_type, yearmonth,  sum(total_allowed) as  total_allowed , sum(sdr_consumption) as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s' and rule_type in ('bundle1', 'bundle2', 'bundle2') and '%s'  BETWEEN service_startdate and service_stopdate;", $this->account_id, $this->customers['bundle_package_id'], date("Y-m-d"));

                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $rs3 = $this->resultset();
                    $available_bundle1 = '';
                    $available_bundle2 = '';
                    $available_bundle3 = '';
                    if (count($rs3) > 0) {
                        foreach ($rs3 as $data_b) {
                            if ($data_b['rule_type'] == 'bundle1') {
                                $available_bundle1 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                                $this->writelog("available_bundle1 $available_bundle1");
                                $insert_allow = 0;
                            }
                            if ($data_b['rule_type'] == 'bundle2') {
                                $available_bundle2 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                                $this->writelog("available_bundle2 $available_bundle2");
                                $insert_allow = 0;
                            }
                            if ($data_b['rule_type'] == 'bundle3') {
                                $available_bundle3 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                                $this->writelog("available_bundle3 $available_bundle3");
                                $insert_allow = 0;
                            }
                        }
                    }

                    foreach ($rs as $data) {
                        if ($data['bundle_id'] == '1') {
                            $bundle1 = $this->customers['bundle1_value'];
                            if ($this->customers['bundle1_type'] == 'MINUTE') {
                                $duration_b = $available_bundle1;
                                $this->customers['bundle_type'] = 'MINUTE';
                                $this->customers['bundle_value'] = $duration_b;
                                $this->customers['bundle_number'] = 'bundle1';
                            }
                            if ($this->customers['bundle1_type'] == 'COST') {
                                $balance_b = $available_bundle1;
                                $this->customers['bundle_type'] = 'COST';
                                $this->customers['bundle_value'] = $balance_b;
                                $this->customers['bundle_number'] = 'bundle1';
                            }
                            $this->writelog("available_bundle1 " . $this->customers['bundle_value']);
                        } elseif ($data['bundle_id'] == '2') {
                            $bundle2 = $this->customers['bundle2_value'];
                            if ($this->customers['bundle2_type'] == 'MINUTE') {
                                $duration_b = $available_bundle2;
                                $this->customers['bundle_type'] = 'MINUTE';
                                $this->customers['bundle_value'] = $duration_b;
                                $this->customers['bundle_number'] = 'bundle2';
                            }
                            if ($this->customers['bundle2_type'] == 'COST') {
                                $balance_b = $available_bundle2;
                                $this->customers['bundle_type'] = 'COST';
                                $this->customers['bundle_value'] = $balance_b;
                                $this->customers['bundle_number'] = 'bundle2';
                            }
                        } elseif ($data['bundle_id'] == '3') {
                            $bundle1 = $this->customers['bundle3_value'];
                            if ($this->customers['bundle3_type'] == 'MINUTE') {
                                $duration_b = $available_bundle3;
                                $this->customers['bundle_type'] = 'MINUTE';
                                $this->customers['bundle_value'] = $duration_b;
                                $this->customers['bundle_number'] = 'bundle3';
                            }
                            if ($this->customers['bundle3_type'] == 'COST') {
                                $balance_b = $available_bundle3;
                                $this->customers['bundle_type'] = 'COST';
                                $this->customers['bundle_value'] = $balance_b;
                                $this->customers['bundle_number'] = 'bundle3';
                            }
                        }
                    }
                }

                /*
                 * Assign the rates value to LLRRate variable to check the LLR in reseler and carrier
                 */
                $this->LLRRates = $this->customers['rate'] + $this->customers['connection_charge'];
                /*
                 * User and Tariff currency not same
                 */
                $otherinfo = $this->account_id;
                if ($this->customers['currency_id'] != $this->customers['tariff_currency_id']) {
                    $this->fail_route_xml('USERTARIFFCURRENCY', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customersdata['user'] = $this->customers;
                    return;
                }

                /*
                 * User tariff is inactive
                 */
                $otherinfo = $this->account_id;
                if ($this->customers['tariff_status'] != '1') {
                    $this->fail_route_xml('USERTARIFFINACTIVE', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customersdata['user'] = $this->customers;
                    return;
                }

                /*
                 * User rates is blocked
                 */
                $otherinfo = $this->account_id;
                if ($this->customers['rates_status'] != '1') {
                    $this->fail_route_xml('USERRATEBLOCKED', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customersdata['user'] = $this->customers;
                    return;
                }


                /*
                 * Building the users billing infomation Array which will pass in CDR event
                 */
            }
        }
        unset($this->customers['account_status']);
        unset($this->customers['cli_check']);
        unset($this->customers['dialpattern_check']);
        unset($this->customers['llr_check']);
        unset($this->customers['tariff_status']);
        unset($this->customers['end_time']);
        unset($this->customers['start_time']);
        unset($this->customers['end_day']);
        unset($this->customers['start_day']);
        unset($this->customers['rates_status']);
        unset($this->customers['dial_prefix']);
        unset($this->customers['ip_status']);
        unset($this->customers['ip_cc']);
        unset($this->customers['ip_cps']);
        unset($this->customers['bundle1_type']);
        unset($this->customers['bundle1_value']);
        unset($this->customers['bundle2_type']);
        unset($this->customers['bundle2_value']);
        unset($this->customers['bundle3_type']);
        unset($this->customers['bundle3_value']);
        unset($this->customers['monthly_charges']);
        $this->customersdata['user'] = $this->customers;
        /*
         * Checking the customer routing
         */

        if ($this->is_vmaccess_internalcall == '0') {
            if ($this->is_internalcall == '0') {
                $this->PSTN_Call_route_check();
                /*
                 * Routing is not available for dialed number
                 */
                if ($this->status == 'FAIL') {
                    $this->customersdata['user'] = $this->customers;
                    return;
                }

                /*
                 * building User and reseller billing data Array for CDR event
                 */
                $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
                $callcounts = $this->PSTN_loadbalance_get_db($user, 'CUSTOMER');
                if ($this->customers['account_cc'] <= $callcounts) {
                    $otherinfo = $this->account_id;
                    $this->fail_route_xml('USERCC', $otherinfo);
                    $this->status = 'FAIL';
                    return;
                }
            }
        }
        /*
         * Users call duration calculations
         */
        $this->customers['duration'] = 0;


        if ($balance_b > 0 AND $this->customers['bundle_type'] == 'COST') {
            $this->customers['balance'] = $this->customers['balance'] + $balance_b;
        }

        $this->customers['duration'] = $this->duration($this->customers);
        $this->writelog("Normal duration " . $this->customers['duration']);
        $this->writelog("Bundle duration " . $duration_b);

        /*
         * Call Duration is zero. maybe rates are higher compared to balance
         */
        $this->writelog("Normal duration " . $this->customers['duration']);
        $otherinfo = $this->account_id;
        if ($this->customers['duration'] == 0) {
            $this->fail_route_xml('USERBALANCE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            return;
        }

        /*
         * If $this->customers['parent_account_id'] length is greter than 0. it means User account is under reseller. So, system will check the reseller account, rates and routes.
         */

        if (strlen($this->customers['parent_account_id']) > 0 and $this->customers['parent_account_id'] != '0') {
            $this->PSTN_reseller($this->customers['parent_account_id']);
        }
        $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
        return;
    }

    function force_dst_src_cli_prefix() {
        /*
         *  change the CallerID based on Destination number prefix.       
         *  Manupulation the callerID number
         * 44%=>441282777711  Means destination number start with 44 then send the caller ID 441282777711
          %=>44%  Means any destination except other rules prefix then add the 44 in the front of incoming CLI
          65%=>% Means any destination number start with 65 then send the callerID as coming from the origuinator or changed by callerID transaltion rules.
         */
        $query = sprintf("SELECT maching_string, match_length, remove_string, add_string, account_id, display_string , LENGTH(maching_string) lndata from customer_callerid where route = 'DTSBASEDCLI'  and  account_id = '%s' and '%s' like maching_string ORDER BY lndata desc;", $this->customers['account_id'], $this->destination_number);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->dst_src_cli_prefix[$key] = $value;
        }
        if (count($this->dst_src_cli_prefix) > 0) {
            if ($this->dst_src_cli_prefix['add_string'] != null and $this->dst_src_cli_prefix['add_string'] != '' and strlen(trim($this->dst_src_cli_prefix['add_string'])) > 0) {
                if (strpos($this->dst_src_cli_prefix['add_string'], '|') !== false) {
                    $clidata = explode('|', $this->dst_src_cli_prefix['add_string']);
                    $removestar = $clidata[0];
                    $this->dst_src_cli_prefix['add_string'] = $addstr = $clidata[1];
                    $this->callernumber = substr($this->callernumber, strlen($removestar), strlen($this->callernumber));
                }
                if (substr(trim($this->dst_src_cli_prefix['add_string']), -1) == '%') {
                    $this->callernumber = trim($this->dst_src_cli_prefix['add_string'] . trim($this->callernumber));
                    $this->callernumber = preg_replace("/%/", "", $this->callernumber);
                } else {
                    $this->callernumber = trim($this->dst_src_cli_prefix['add_string']);
                }
            }
            $this->callernumber_user = $this->callernumber_user_dst_src_cli_prefix = $this->callernumber;
        }
        return;
    }

    function vmaccess_internalcall() {
        $this->is_vmaccess_internalcall = '0';
        if ($this->destination_number == '*97') {
            $this->is_vmaccess_internalcall = '1';
            $lb = $this->Hunt_Network_Addr;
            $lb = $this->lb;
            $destination_number = $this->request['Hunt-Username'];
            $account_id = $this->customers['account_id'];
            $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=0\"/>";
            $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=0\"/>";
            $this->Gateway_XML .= "\n<action application=\"set\" data=\"bypass_media=false\"/>";
            $this->Gateway_XML .= "\n<action application=\"set\" data=\"call_timeout=20\"/>";
            $this->Gateway_XML .= "\n<action application=\"set\" data=\"continue_on_fail=true\"/>";
            $this->Gateway_XML .= "\n<action application=\"set\" data=\"hangup_after_bridge=true\"/>";

            $this->Gateway_XML .= "\n<action application=\"set\" data=\"voicemail_authorized=true\"/>";
            $this->Gateway_XML .= "\n   <action application=\"voicemail\" data=\"check default  \$\${domain} " . $destination_number . "\" />";

            $this->directory = "
                <section name=\"directory\">
                <domain name=\" \$\${domain}\"> 
<user id=\"$destination_number\" mailbox=\"$destination_number\"> 
                <params>
 <param name=\"dial-string\" value=\"{^^:sip_invite_domain = \${dialed_domain}:presence_id = \${dialed_user}\@\${dialed_domain}}\${sofia_contact(\*\/\${dialed_user}@\${dialed_domain})
        }, \${verto_contact(\${dialed_user}@\${dialed_domain})}\"/>
  <param name=\"jsonrpc-allowed-methods\" value=\"verto\"/>
                <param name=\"password\" value=\"123456\"/>
                <param name=\"vm-password\" value=\"123456\"/>
            	<param name=\"manage-shared-appearance\" value=\"true\"/>
		<param name=\"http-allowed-api\" value=\"voicemail\"/>
		 <param name=\"vm-storage-dir\" value=\"/var/www/html/vm/$destination_number/$destination_number\"/>
               <param name=\"vm-attach-file\" value=\"false\" />
             <param name=\"vm-disk-quota\" value=\"60\"/>

</params>
                <variables>
                        <variable name=\"user_context\" value=\"default\"/>
                       <variable name=\"accountcode\" value=\"$destination_number\"/>
                        <variable name=\"effective_caller_id_name\" value=\"$destination_number\"/>
                        <variable name=\"caller_id_name\" value=\"$destination_number\"/>
                        <variable name=\"effective_caller_id_number\" value=\"$destination_number\"/> 
                        <variable name=\"caller_id_number\" value=\"$destination_number\"/>  
                        <variable name=\"sip-force-expires\" value=\"300\"/>

<variable name=\"sip-force-contact\" value=\"NDLB-connectile-dysfunction\"/>
        </variables>
                </user>
                </domain>
                </section>";
        }
    }

    function internalcall() {
        if ($this->mpstncall == 'PSTN') {
            $this->is_internalcall = 0;
            return;
        }

        $this->is_internalcall = 0;

        $query = sprintf("select customer_sip_account.username,
customer_sip_account.secret,
customer_sip_account.ipaddress,
customer_sip_account.`status`,
customer_sip_account.sip_cc,
customer_sip_account.sip_cps,
customer_sip_account.ipauthfrom,
customer_sip_account.extension_no,
customer_sip_account.voicemail_enabled,
customer_sip_account.voicemail,
customer_sip_account.display_name,
customer_sip_account.caller_id,
customer_sip_account.cli_prefer,
customer_sip_account.codecs,
customer_sip_account.moh_sound,
customer_sip_account.`name`,
customer_sip_account.email_address,
customer_sip_account.phone_number,
customer_sip_account.ring_timeout,
customer_sip_account.call_forward_all,
customer_sip_account.cfall_destination_type,
customer_sip_account.cfall_destination,
customer_sip_account.call_forward_no_answer,
customer_sip_account.cfnoans_destination_type,
customer_sip_account.cfnoans_destination,
customer_sip_account.call_forward_busy,
customer_sip_account.cfbusy_destination_type,
customer_sip_account.cfbusy_destination,
customer_sip_account.cfnoans_timeout,
customer_sip_account.call_recording,
customer_sip_account.dnd,
customer_sip_account.created_by,
customer_sip_account.created_by_account_id,
customer_sip_account.updated_by,
customer_sip_account.created_dt,
customer_sip_account.updated_dt,
customer_sip_account.user_type,
customer_sip_account.extension_id,
customer_sip_account.pushtoken,
customer_sip_account.firebasetoken,
customer_sip_account.appos,
customer_sip_account.id, '1' as tariff_status,  '' as ratecard_id, '' as  tariff_id, 0 as start_day, 6 asend_day, '00:00:00' start_time, '23:59:59' as end_time, '%s' as prefix, '%s' as destination,  0 as rate, 0 as connection_charge,  1 as minimal_time, 1 as resolution_time,  0 as grace_period,  0 as rate_multiplier, 0 as rate_addition, '1' as rates_status, '' as tariff_currency_id, '' as tariff_currency_id, 0 as monthly_charges, '0' as bundle_option, '' as bundle1_type, 0 as bundle1_value, '' as bundle2_type, 0 as bundle2_value, '' as bundle3_type, 0 as bundle3_value from customer_sip_account where account_id = '%s' and extension_no = '%s';", $this->destination_number, $this->destination_number, $this->customers['account_id'], $this->destination_number);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
                $this->is_internalcall = '1';
            }
        }

        $query2 = sprintf("select extension_no as dst_extension_no from customer_sip_account where extension_no = '%s' and account_id ='%s';", $this->destination_number, $this->customers['account_id']);
        $this->writelog($query2);
        $this->query('SWITCH', $query2);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
                $this->is_internalcall = '1';
            }
        }
    }

    function internalcall_reseller($reseller) {
        $this->is_internalcall = 0;
        if (GLOBALEXTEN == '1')
            $query = sprintf("select customer_sip_account.*, '1' as tariff_status,  '' as ratecard_id, '' as  tariff_id, 0 as start_day, 6 asend_day, '00:00:00' start_time, '23:59:59' as end_time, '%s' as prefix, '%s' as destination,  0 as rate, 0 as connection_charge,  1 as minimal_time, 1 as resolution_time,  0 as grace_period,  0 as rate_multiplier, 0 as rate_addition, '1' as rates_status, '' as tariff_currency_id, '' as tariff_currency_id, 0 as monthly_charges, '0' as bundle_option, '' as bundle1_type, 0 as bundle1_value, '' as bundle2_type, 0 as bundle2_value, '' as bundle3_type, 0 as bundle3_value from customer_sip_account where   and extension_no = '%s';", $this->destination_number, $this->destination_number, $this->destination_number);
        else
            $query = sprintf("select customer_sip_account.*, '1' as tariff_status,  '' as ratecard_id, '' as  tariff_id, 0 as start_day, 6 asend_day, '00:00:00' start_time, '23:59:59' as end_time, '%s' as prefix, '%s' as destination,  0 as rate, 0 as connection_charge,  1 as minimal_time, 1 as resolution_time,  0 as grace_period,  0 as rate_multiplier, 0 as rate_addition, '1' as rates_status, '' as tariff_currency_id, '' as tariff_currency_id, 0 as monthly_charges, '0' as bundle_option, '' as bundle1_type, 0 as bundle1_value, '' as bundle2_type, 0 as bundle2_value, '' as bundle3_type, 0 as bundle3_value from customer_sip_account where account_id = '%s' and extension_no = '%s';", $this->destination_number, $this->destination_number, $reseller, $this->destination_number);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $reseller[$key] = $value;
            $this->is_internalcall = '1';
        }
        if (GLOBALEXTEN == '1')
            $query2 = sprintf("select extension_no as dst_extension_no from customer_sip_account where extension_no = '%s';", $this->destination_number);
        else
            $query2 = sprintf("select extension_no as dst_extension_no from customer_sip_account where extension_no = '%s' and account_id ='%s';", $this->destination_number, $this->customers['account_id']);
        $this->writelog($query2);
        $this->query('SWITCH', $query2);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $reseller[$key] = $value;
            $this->is_internalcall = '1';
        }
    }

    function PSTN_reseller_change_callid($reseller) {
        /*
         *  Manupulation the callerID number
         */
        $query = sprintf("SELECT LENGTH(maching_string) lndata, match_length,  maching_string, remove_string, add_string, account_id, display_string, action_type FROM customer_callerid where account_id = '%s' and '%s' like maching_string and route = 'OUTBOUND' order by lndata desc;", $reseller['account_id'], $this->callernumber);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->reseller_callerid[$key] = $value;
        }
        if (count($this->reseller_callerid) > 0 and $this->reseller_callerid['action_type'] == '0') {
            /*
             * CallerID number pattern is black.
             */
            $status = '1';
            if ($this->reseller_callerid['match_length'] > 0) {
                if ($this->reseller_callerid['match_length'] == strlen(trim($this->callernumber))) {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('RESELLERCALLERIDBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }
        }


        if (count($this->reseller_callerid) == 0) {
            /*
             * CallerID number pattern is black.
             */
            $otherinfo = $this->account_id;
            $this->fail_route_xml('RESELLERCALLERIDBLOCKED', $otherinfo);
            $this->status = 'FAIL';
            return;
        }


        if (count($this->reseller_callerid) > 0) {
            /*
             * Removing the user callerid prefix from the user callerid number if any
             */
            $status = '0';
            if ($this->reseller_callerid['match_length'] > 0) {
                if ($this->reseller_callerid['match_length'] == strlen(trim($this->callernumber))) {
                    $status = '0';
                } else {
                    $status = '1';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('RESELLERCALLERIDBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }

            if ($this->reseller_callerid['remove_string'] != null and $this->reseller_callerid['remove_string'] != '' and strlen(trim($this->reseller_callerid['remove_string'])) > 0) {
                if (substr($this->callernumber, 0, strlen($this->reseller_callerid['remove_string'])) == $this->reseller_callerid['remove_string'] AND $this->reseller_callerid['remove_string'] != NULL AND $this->reseller_callerid['remove_string'] != '' AND $this->reseller_callerid['remove_string'] != '%') {
                    $this->callernumber = substr($this->callernumber, strlen($this->reseller_callerid['remove_string']), strlen($this->callernumber));
                }
            }

            /*
             * Adding the callerid prefix in user callerid number. New CallerID number will be sent to termination as callerid.
             */
            if ($this->reseller_callerid['add_string'] != null and $this->reseller_callerid['add_string'] != '' and strlen(trim($this->reseller_callerid['add_string'])) > 0) {
//  $this->callernumber = trim($this->reseller_callerid['add_string'] . trim($this->callernumber));
                if (substr(trim($this->reseller_callerid['add_string']), -1) == '%') {
                    $this->callernumber = trim($this->reseller_callerid['add_string'] . trim($this->callernumber));
                    $this->callernumber = preg_replace("/%/", "", $this->callernumber);
                } else {
                    $this->callernumber = trim($this->reseller_callerid['add_string']);
                }
            }
        }
    }

    function PSTN_loadbalance_get_dialednumber_count($user, $type) {
        if ($type == 'CUSTOMER') {
            $query = sprintf("select count(carrier_src_callee) count, carrier_carrier_id_name from livecalls where customer_account_id = '%s' and carrier_src_callee = '%s' limit 1", $user, $this->destination_number);
        } else if ($type == 'Reseller1') {
            $query = sprintf("select count(carrier_src_callee) count, carrier_carrier_id_name from livecalls where reseller1_account_id = '%s'  and carrier_src_callee = '%s' limit 1", $user, $this->destination_number);
        } elseif ($type == 'Reseller2') {
            $query = sprintf("select count(carrier_src_callee) count, carrier_carrier_id_name from livecalls where reseller2_account_id = '%s'  and carrier_src_callee = '%s' limit 1", $user, $this->destination_number);
        } elseif ($type == 'Reseller3') {
            $query = sprintf("select count(carrier_src_callee) count, carrier_carrier_id_name from livecalls where reseller3_account_id = '%s' and carrier_src_callee = '%s' ", $user, $this->destination_number);
        } elseif ($type == 'CARRIER') {
            $query = sprintf("select count(carrier_src_callee) count, carrier_carrier_id_name from livecalls where carrier_id = '%s' and carrier_src_callee = '%s'  limit 1", $user, $this->destination_number);
        } elseif ($type == 'GATEWAY') {
            $query = sprintf("select count(carrier_src_callee) count, carrier_carrier_id_name from livecalls where carrier_ipaddress_name = '%s' and carrier_src_callee = '%s' limit 1", $user, $this->destination_number);
        }
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();

        $calldata_of_dialednumber = Array();
        $calldata_of_dialednumber['count'] = 0;
        $calldata_of_dialednumber['carrier_carrier_id_name'] = '';

        foreach ($rs[0] as $key => $value) {
            $calldata_of_dialednumber[$key] = $values;
        }
        if ($calldata_of_dialednumber['count'] == '' or $calldata_of_dialednumber['count'] == null)
            $calldata_of_dialednumber['count'] = 0;
        if ($calldata_of_dialednumber['carrier_carrier_id_name'] == '' or $calldata_of_dialednumber['carrier_carrier_id_name'] == null or strlen($calldata_of_dialednumber['carrier_carrier_id_name']) == 0)
            $calldata_of_dialednumber['carrier_carrier_id_name'] = '';
        return $calldata_of_dialednumber;
    }

    function PSTN_loadbalance_get_db($user, $type) {
        if ($type == 'CUSTOMER') {
            $query = sprintf("select count(id) ccount from livecalls where customer_account_id = '%s' limit 1", $user);
        } else if ($type == 'Reseller1') {
            $query = sprintf("select count(id) ccount from livecalls where reseller1_account_id = '%s'  limit 1", $user);
        } elseif ($type == 'Reseller2') {
            $query = sprintf("select count(id) ccount from livecalls where reseller2_account_id = '%s'  limit 1", $user);
        } elseif ($type == 'Reseller3') {
            $query = sprintf("select count(id) ccount from livecalls where reseller3_account_id = '%s'", $user);
        } elseif ($type == 'CARRIER') {
            $query = sprintf("select count(id) ccount from livecalls where carrier_id = '%s'  limit 1", $user);
        } elseif ($type == 'GATEWAY') {
            $query = sprintf("select count(id) ccount from livecalls where carrier_ipaddress_name = '%s'  limit 1", $user);
        }
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $count = $value;
        }
        if ($count == '' or $count == null)
            $count = 0;
        return $count;
    }

    function DID_users_change_callid($users, $callernumber) {
        $this->cli_auth = 0;
        $this->callernumber = $callernumber;
        $this->writelog("CLI S " . $callernumber);
        /*
         *  Manupulation the callerID number
         */
        $query = sprintf("SELECT  LENGTH(maching_string) lndata, match_length, id, maching_string, remove_string, add_string, account_id, display_string, action_type FROM customer_callerid where account_id = '%s' and '%s' like maching_string and route = 'INBOUND'  ORDER BY lndata desc limit 1;", $users['account_id'], $this->callernumber);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->account_callerid[$key] = $value;
        }

        if (count($this->account_callerid) > 0 and $this->account_callerid['action_type'] == '0') {
            /*
             * CallerID number pattern is black.
             */
            $status = '1';
            if ($this->account_callerid['match_length'] > 0) {
                if ($this->account_callerid['match_length'] == strlen(trim($this->callernumber))) {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('USERCALLERIDBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }
        }

        if (count($this->account_callerid) > 0) {
            $status = '0';
            if ($this->account_callerid['match_length'] > 0) {
                if ($this->account_callerid['match_length'] == strlen(trim($this->callernumber))) {
                    $status = '0';
                } else {
                    $status = '1';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('USERCALLERIDBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }

            $this->cli_auth = 1;
            /*
             * Removing the user callerid prefix from the user callerid number if any
             */
            if ($this->account_callerid['remove_string'] != null and $this->account_callerid['remove_string'] != '' and strlen(trim($this->account_callerid['remove_string'])) > 0) {
                if (substr($this->callernumber, 0, strlen($this->account_callerid['remove_string'])) == $this->account_callerid['remove_string'] AND $this->account_callerid['remove_string'] != NULL AND $this->account_callerid['remove_string'] != '' AND $this->account_callerid['remove_string'] != '%') {
                    $this->callernumber = substr($this->callernumber, strlen($this->account_callerid['remove_string']), strlen($this->callernumber));
                }
            }

            /*
             * Adding the callerid prefix in user callerid number. New CallerID number will be sent to termination as callerid.
             */
            if ($this->account_callerid['add_string'] != null and $this->account_callerid['add_string'] != '' and strlen(trim($this->account_callerid['add_string'])) > 0) {
                if (substr(trim($this->account_callerid['add_string']), -1) == '%') {
                    $this->callernumber = trim($this->account_callerid['add_string'] . trim($this->callernumber));
                    $this->callernumber = preg_replace("/%/", "", $this->callernumber);
                } else {
                    $this->callernumber = trim($this->account_callerid['add_string']);
                }
            }
        }
        $this->callernumber_user = $this->callernumber;

        $this->writelog("CLI C " . $this->callernumber);
        return $this->callernumber;
    }

    function PSTN_customer_callerid() {
        $this->cli_auth = 0;
        /*
         *  Manupulation the callerID number
         */
        $query = sprintf("SELECT LENGTH(maching_string) lndata, match_length, id, maching_string, remove_string, add_string, account_id, display_string, action_type FROM customer_callerid where account_id = '%s' and '%s' like maching_string    and route = 'OUTBOUND'  ORDER BY lndata desc limit 1;", $this->customers['account_id'], $this->callernumber);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->account_callerid[$key] = $value;
        }

        if (count($this->account_callerid) > 0 and $this->account_callerid['action_type'] == '0') {
            /*
             * CallerID number pattern is black.
             */
            $status = '1';
            if ($this->account_callerid['match_length'] > 0) {
                if ($this->account_callerid['match_length'] == strlen(trim($this->callernumber))) {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('USERCALLERIDBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }
        }

        if (count($this->account_callerid) > 0) {
            $status = '0';
            if ($this->account_callerid['match_length'] > 0) {
                if ($this->account_callerid['match_length'] == strlen(trim($this->callernumber))) {
                    $status = '0';
                } else {
                    $status = '1';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('USERCALLERIDBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }

            $this->cli_auth = 1;
            /*
             * Removing the user callerid prefix from the user callerid number if any
             */
            if ($this->account_callerid['remove_string'] != null and $this->account_callerid['remove_string'] != '' and strlen(trim($this->account_callerid['remove_string'])) > 0) {
                if (substr($this->callernumber, 0, strlen($this->account_callerid['remove_string'])) == $this->account_callerid['remove_string'] AND $this->account_callerid['remove_string'] != NULL AND $this->account_callerid['remove_string'] != '' AND $this->account_callerid['remove_string'] != '%') {
                    $this->callernumber = substr($this->callernumber, strlen($this->account_callerid['remove_string']), strlen($this->callernumber));
                }
            }

            /*
             * Adding the callerid prefix in user callerid number. New CallerID number will be sent to termination as callerid.
             */
            if ($this->account_callerid['add_string'] != null and $this->account_callerid['add_string'] != '' and strlen(trim($this->account_callerid['add_string'])) > 0) {
                if (substr(trim($this->account_callerid['add_string']), -1) == '%') {
                    $this->callernumber = trim($this->account_callerid['add_string'] . trim($this->callernumber));
                    $this->callernumber = preg_replace("/%/", "", $this->callernumber);
                } else {
                    $this->callernumber = trim($this->account_callerid['add_string']);
                }
            }
        }
        $this->callernumber_user = $this->callernumber;
    }

    function PSTN_reseller_dialplan($reseller) {
        /*
         *  Dialed number manupulation
         */
        $query = sprintf("SELECT LENGTH(maching_string) lndata, match_length, id, account_id, maching_string, remove_string, display_string, add_string, action_type FROM customer_dialpattern where account_id = '%s' and '%s' like maching_string  and route = 'OUTBOUND' ORDER BY lndata desc limit 1;", $reseller['account_id'], $this->destination_number);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->reseller_dialplan[$key] = $value;
        }


        if (count($this->reseller_dialplan) > 0 and $this->reseller_dialplan['action_type'] == '0') {
            /*
             * Dialed number pattern is black.
             */
            $status = '1';
            if ($route2['match_length'] > 0) {
                if ($route2['match_length'] == strlen(trim($dialnumber1))) {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }
            if ($status == '1') {
                $otherinfo = $reseller['account_id'];
                $this->fail_route_xml('RESELLERDIALEDNUMBERBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }
        }

        $route2 = $this->reseller_dialplan;
        $dialnumber1 = $this->destination_number_user;


        if (count($route2) > 0) {
            $status = '0';
            if ($route2['match_length'] > 0) {
                if ($route2['match_length'] == strlen(trim($dialnumber1))) {
                    $status = '0';
                } else {
                    $status = '1';
                }
            }
            if ($status == '1') {
                $otherinfo = $reseller['account_id'];
                $this->fail_route_xml('RESELLERDIALEDNUMBERBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }

            $remove_string = $route2['remove_string'];
            $add_string = $route2['add_string'];

            if (trim($remove_string) != '') {
                if (substr($dialnumber1, 0, strlen($remove_string)) == $remove_string AND $remove_string != NULL AND $remove_string != '' AND $remove_string != '%') {
                    $dialnumber1 = substr($dialnumber1, strlen($remove_string), strlen($dialnumber1));
                }
            }
            if ($add_string == '%' or $add_string == '') {
                $dialnumber1 = trim($dialnumber1);
            } else if (substr(trim($add_string), -1) == '%') {
                $dialnumber1 = trim($add_string . trim($dialnumber1));
                $dialnumber1 = preg_replace("/%/", "", $dialnumber1);
            } else {
                $dialnumber1 = trim($add_string);
            }
        }
        $this->destination_number_user = $dialnumber1;
    }

    function PSTN_customer_dialpattern() {
        /*
         *  Dialed number manupulation
         */
        $dialnumber1 = $this->destination_number_route = $this->destination_number;
        $query = sprintf("SELECT LENGTH(maching_string) lndata,  match_length, id, account_id, maching_string, remove_string, display_string, add_string, action_type  FROM customer_dialpattern where account_id = '%s' and '%s' like maching_string and route = 'OUTBOUND' ORDER BY lndata desc limit 1;", $this->customers['account_id'], $dialnumber1);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->account_dialpattern[$key] = $value;
        }


        if (count($this->account_dialpattern) > 0 and $this->account_dialpattern['action_type'] == '0') {
            /*
             * Dialed number pattern is black.
             */
            $status = '1';
            if ($this->account_dialpattern['match_length'] > 0) {
                if ($this->account_dialpattern['match_length'] == strlen(trim($dialnumber1))) {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('USERDIALEDNUMBERBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }
        }

        if (count($this->account_dialpattern) == 0) {
            /*
             * Dialed number pattern is black.
             */
            $otherinfo = $this->account_id;
            $this->fail_route_xml('USERDIALEDNUMBER', $otherinfo);
            $this->status = 'FAIL';
            return;
        }

        $route2 = $this->account_dialpattern;
        $dialnumber1 = $this->destination_number;
        if (count($route2) > 0) {
            $status = '0';
            if ($route2['match_length'] > 0) {
                if ($route2['match_length'] == strlen(trim($dialnumber1))) {
                    $status = '0';
                } else {
                    $status = '1';
                }
            }
            if ($status == '1') {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('USERDIALEDNUMBERBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }
            $remove_string = $route2['remove_string'];
            $add_string = $route2['add_string'];
            if (trim($remove_string) != '') {
                if (substr($dialnumber1, 0, strlen($remove_string)) == $remove_string AND $remove_string != NULL AND $remove_string != '' AND $remove_string != '%') {
                    $dialnumber1 = substr($dialnumber1, strlen($remove_string), strlen($dialnumber1));
                }
            }

            if ($add_string == '%' or $add_string == '') {
                $dialnumber1 = trim($dialnumber1);
            } else if (substr(trim($add_string), -1) == '%') {
                $dialnumber1 = trim($add_string . trim($dialnumber1));
                $dialnumber1 = preg_replace("/%/", "", $dialnumber1);
            } else {
                $dialnumber1 = trim($add_string);
            }
        }

        $this->destination_number_user = $this->destination_number = $dialnumber1;
    }

    function PSTN_Call_route_check() {
        /*
         * Building the user prefered routing dialplan
         */
        $this->writelog($this->customers['account_id']);
        $query = sprintf("SELECT remove_string, add_string, LENGTH(maching_string) lndata, id, account_id, dialplan_id, maching_string, display_string FROM customer_dialplan where account_id = '%s' and ('%s' like maching_string or  '%s' like maching_string) ORDER BY lndata DESC limit 1;", $this->customers['account_id'], $this->destination_number, $this->destination_number_route);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->customer_dialplan[$key] = $value;
        }

        if (strlen($this->customer_dialplan['dialplan_id']) == 0) {
            $otherinfo = $this->account_id;
            $this->fail_route_xml('USERDIALPLAN', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            return;
        } else {
            $this->dialplan_id = $this->customer_dialplan['dialplan_id'];
            $dialnumber1 = $this->destination_number;
            $remove_string = $this->customer_dialplan['remove_string'];
            $add_string = $this->customer_dialplan['add_string'];
            if (trim($remove_string) != '') {
                if (substr($dialnumber1, 0, strlen($remove_string)) == $remove_string AND $remove_string != NULL AND $remove_string != '' AND $remove_string != '%') {
                    $dialnumber1 = substr($dialnumber1, strlen($remove_string), strlen($dialnumber1));
                }
            }
            $this->destination_number_user = $this->destination_number = $dialnumber1;
        }
    }

    function PSTN_reseller_route_check($reseller) {
        /*
         * Building the user prefered routing dialplan
         */

        if ($this->is_internalcall == '1') {
            return;
        }
        $query = sprintf("SELECT id, account_id, dialplan_id FROM reseller_dialplan WHERE account_id = '%s' and  dialplan_id = '%s' limit 1;", $reseller['account_id'], $this->dialplan_id);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->reseller_carrier_dialplan[$key] = $value;
        }
        if (strlen($this->reseller_carrier_dialplan['dialplan_id']) == 0) {
            $otherinfo = $reseller['account_id'];
            $this->fail_route_xml('RESELLERDIALPLAN', $otherinfo);
            $this->status = 'FAIL';
            return;
        } else {
            $this->dialplan_id = $this->reseller_carrier_dialplan['dialplan_id'];
        }
    }

    function llr_check($route) {
        $this->status = 'OK';
        $llrrates = $route['rate'] + $route['connection_charge'];
        foreach ($this->currencies as $currency) {
            if ($currency['currency_id'] == $this->customers['currency_id']) {
                $account_currency_ratio = $currency['ratio'];
            }
            if ($currency['currency_id'] == $route['tariff_currency_id']) {
                $route_currency_ratio = $currency['ratio'];
            }
        }
        $this->ratio = $account_currency_ratio / $route_currency_ratio;

        if ($account_currency_ratio == $route_currency_ratio) {
            if ($llrrates > $this->LLRRates) {
                $this->status = 'FAIL';
            }
        } else if (($llrrates * $this->ratio) > $this->LLRRates) {
            $this->status = 'FAIL';
        }
        return;
    }

    function PSTN_route_selection($rs1) {
        $this->orderBy($rs1, 'dial_prefix desc', 'false');
        $route_list = Array();
        $route_data = Array();
        $route_str = '';
        $max_match_dialprefix = $rs1[0]['dial_prefix'];
        foreach ($rs1 as $data) {
            $route_str = $data['carrier_id'] . $data['dialplan_id'];
            if ($max_match_dialprefix == $data['dial_prefix'] and $data['route_status'] == '1') {
                if (!in_array($route_str, $route_list)) {
                    array_push($route_list, $route_str);
                    array_push($route_data, $data);
                }
            }
        }
        $this->orderBy($route_data, 'priority asc', 'false');
        return $route_data;
    }

    function PSTN_route_routing_lb($data) {
        $routing_data = $data;
        // percentage array
        $p_data = ARRAY();
        // percentage gateway array
        $p_routinglist = ARRAY();
        // total percentage data
        $t_p_data = 0;

        $routing_list = array();
        foreach ($routing_data as $route) {
            $allow = 0;
            $carrier_id = $route['carrier_id'];
            if (!in_array($carrier_id, $routing_list)) {
                array_push($p_data, $route);
                $tmpstr = array('carrier_id' => $route['carrier_id'], 'percentage' => $route['load_share']);
                array_push($p_routinglist, $tmpstr);
                array_push($routing_list, $carrier_id);
                $t_p_data = $t_p_data + $route['load_share'];
            }
        }
        $p_routinglist1 = $p_routinglist;
        $routingid_data = Array();
        if (count($p_routinglist) > 0) {
            $r_call_count = 0;
            foreach ($p_routinglist as $r_call) {
                $dialednumber_count = $this->PSTN_loadbalance_get_dialednumber_count($user, $type);
                if ($dialednumber_count['count'] > 0) {
                    $priority_carrier = 0;
                } else {
                    $priority_carrier = 100;
                }
                $callcounts = $this->PSTN_loadbalance_get_db($r_call['carrier_id'], 'CARRIER');
                $this->writelog("Running Calls - " . $r_call['carrier_id'] . " - " . $callcounts);
                $r_call_count = $r_call_count + $callcounts;
                $tmpstr1 = array('carrier_id' => $r_call['carrier_id'], 'calls' => $callcounts, 'priority_carrier' => $priority_carrier);
                array_push($routingid_data, $tmpstr1);
            }
        }
        foreach ($p_routinglist1 as $data) {
            $per = $data['percentage'] * 100 / $t_p_data;
            $percentage[$data['carrier_id']] = $per;
        }
        foreach ($routingid_data as $rdata) {
            $rper = $rdata['calls'] * 100 / $r_call_count;
            $rpercentage[$rdata['carrier_id']] = $rper;
            $rcalls[$rdata['carrier_id']] = $rdata['calls'];
            $rpriority_carrier[$rdata['carrier_id']] = $rdata['priority_carrier'];
        }
        $p_data_next = Array();
        foreach ($p_data as $data_n) {
            if ($rpercentage[$data_n['carrier_id']] > 0)
                $data_n['running_percentage'] = $rpercentage[$data_n['carrier_id']];
            else
                $data_n['running_percentage'] = 0;

            $data_n['priority_carrier'] = $rpriority_carrier[$data_n['carrier_id']];
            $data_n['rcalls'] = $rcalls[$data_n['carrier_id']];
            $data_n['req_percentage'] = $percentage[$data_n['carrier_id']];
            array_push($p_data_next, $data_n);
        }
        $pg_data = Array();
        $routlist = Array();
        foreach ($p_data_next as $data) {
            $data['rpriority'] = 1000;
            if (!in_array($data['carrier_id'], $routlist)) {
                if ($data['running_percentage'] == 0) {
                    $data['rpriority'] = 1;
                } elseif ($data['req_percentage'] > $data['running_percentage']) {
                    $data['rpriority'] = ceil($data['req_percentage'] - $data['running_percentage']);
                } else {
                    $data['rpriority'] = 300;
                }
            } else {
                $data['rpriority'] = 1000;
            }
            array_push($routlist, $data['carrier_id']);
            array_push($pg_data, $data);
        }
        $this->orderBy($pg_data, ' priority_carrier ASC, rpriority ASC, rate ASC, running_percentage ASC');

        $this->writelog('I am in route LB end');
        return $pg_data;
    }

    function PSTN_route_gateway_lb($data) {
        $routing_data = $data;
        $p_data = ARRAY();  // percentage array
        $p_routinglist = ARRAY(); // percentage gateway array
        $t_p_data = 0; // total percentage data
        $routing_list = array();
        foreach ($routing_data as $route) {
            if ($route['rates_status'] == '1' and $route['carrier_status'] == '1' and $route['ip_status'] == '1') {
                $allow = 0;
                $carrier_ip_id = $route['carrier_ip_id'];
                if (!in_array($carrier_ip_id, $routing_list)) {
                    array_push($p_data, $route);
                    $tmpstr = array('carrier_ip_id' => $route['carrier_ip_id'], 'gpercentage' => $route['gateway_load_share']);
                    array_push($p_routinglist, $tmpstr);
                    array_push($routing_list, $carrier_ip_id);
                    $t_p_data += $route['gateway_load_share'];
                }
            }
        }
        $p_routinglist1 = $p_routinglist;
        $routingid_data = Array();
        if (count($p_routinglist) > 0) {
            $r_call_count = 0;
            foreach ($p_routinglist as $r_call) {
                $gateway_data = $r_call['carrier_ip_id'];
                $callcounts = $this->PSTN_loadbalance_get_db($gateway_data, 'GATEWAY');
                $r_call_count += $callcounts;
                $tmpstr = array('carrier_ip_id' => $r_call['carrier_ip_id'], 'calls' => $callcounts);
                array_push($routingid_data, $tmpstr);
            }
        }
        foreach ($p_routinglist1 as $data) {
            $per = $data['gpercentage'] * 100 / $t_p_data;
            $percentage[$data['carrier_ip_id']] = $per;
        }
        foreach ($routingid_data as $rdata) {
            $rper = $rdata['calls'] * 100 / $r_call_count;
            $rpercentage[$rdata['carrier_ip_id']] = $rper;
            $rcalls[$rdata['carrier_ip_id']] = $rdata['calls'];
        }
        $p_data_next = Array();
        foreach ($p_data as $data_n) {
            if ($rpercentage[$data_n['carrier_ip_id']] > 0)
                $data_n['grunning_percentage'] = $rpercentage[$data_n['carrier_ip_id']];
            else
                $data_n['grunning_percentage'] = 0;
            $data_n['rcalls'] = $rcalls[$data_n['carrier_ip_id']];
            $data_n['greq_percentage'] = $percentage[$data_n['carrier_ip_id']];
            array_push($p_data_next, $data_n);
        }
        $pg_data = Array();
        $routlist = Array();
        foreach ($p_data_next as $data) {
            $data['gpriority'] = 1000;
            if ($data['rates_status'] == '1' and $data['carrier_status'] == '1' and $data['ip_status'] == '1') {
                if (!in_array($data['carrier_ip_id'], $routlist)) {
                    if ($data['grunning_percentage'] == 0) {
                        $data['gpriority'] = 1;
                    } elseif ($data['greq_percentage'] > $data['grunning_percentage']) {
                        $data['gpriority'] = ceil($data['greq_percentage'] - $data['grunning_percentage']);
                    } else {
                        $data['gpriority'] = 300;
                    }
                } else {
                    $data['gpriority'] = 1000;
                }
                array_push($routlist, $data['carrier_ip_id']);
                array_push($pg_data, $data);
            }
        }
        $this->orderBy($pg_data, 'gpriority ASC, grunning_percentage ASC');
        $pg_data2[0] = current($pg_data);
        return $pg_data2;
    }

    function internalcallxml() {
        $lb = $this->Hunt_Network_Addr;
        $lb = $this->lb;
        $this->destination_number;
        $destination_number = $this->customers['username'];
        if (strlen(trim($destination_number)) == 0)
            $destination_number = $this->customers['dst_extension_no'];



        $sdp = $this->request['variable_switch_r_sdp'];
        if (strstr($sdp, "m=video")) {
            $video = 1;
        } else {
            $video = 0;
        }

        $this->Gateway_XML .= "\n<action application=\"log\" data=\"DIALING Extension SDP ---------  Data----  $sdp\"/>";

        if ($this->customers['media_transcoding'] == '1' and $video == 0) {
            $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=1\"/>";
            $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_h_X-MEDIATRA=1\"/>";
            $this->Gateway_XML .= "\n<action application=\"set\" data=\"bypass_media=false\"/>";
        } else {
            $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=0\"/>";
            $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=0\"/>";
            $this->Gateway_XML .= "\n<action application=\"set\" data=\"bypass_media=true\"/>";
        }

        $route_callid = $this->customers['dst_extension_no'];

        $this->Gateway_XML .= "\n<action application=\"export\" data=\"INTERNALCALL=1\"/>";
        $this->Gateway_XML .= "\n<action application=\"export\" data=\"INTERNALCALL=1\"/>";

        $destination_number1 = preg_replace("/#/", "T", $destination_number);

        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_ring=curl " . APIDOAMIN . "api/api.php?r=ring&common_uuid=" . $this->uuid . "&gatewayname=EXTEN&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=EXTEN&carrier_gateway_ipaddress_name=EXTEN&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";


        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl  " . APIDOAMIN . "api/api.php?r=ring&common_uuid=" . $this->uuid . "&gatewayname=EXTEN&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=EXTEN&carrier_gateway_ipaddress_name=EXTEN&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";

        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?r=ring&common_uuid=" . $this->uuid . "&gatewayname=EXTEN&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=EXTEN&carrier_gateway_ipaddress_name=EXTEN&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";


        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?r=answer&common_uuid=" . $this->uuid . "&gatewayname=EXTEN&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=EXTEN&carrier_gateway_ipaddress_name=EXTEN&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_number=" . $route_callid . "\"/>";
        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_name=" . $route_callid . "\"/>";
        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_number=" . $route_callid . "\"/>";
        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_name=" . $route_callid . "\"/>";
        $this->Gateway_XML .= "\n <action application=\"export\"  data=\"nolocal:absolute_codec_string=\${ep_codec_string}\"/>";

        $this->Gateway_XML .= "\n<action application=\"set\" data=\"continue_on_fail=true\"/>";
        $this->Gateway_XML .= "\n<action application=\"set\" data=\"hangup_after_bridge=true\"/>";


        $this->Gateway_XML .= "\n<action application=\"set\" data=\"call_timeout=60\"/>";

        $this->Gateway_XML .= "\n<action application=\"ring_ready\" />";

        $this->Gateway_XML .= "\n<action application=\"set\" data=\"ringback=$\${uk-ring}\"/>";
        $this->Gateway_XML .= "\n <action application=\"bridge\" data=\"sofia/internal/" . $destination_number . "@" . $lb . "\"/>";
        $this->Gateway_XML .= "\n <action application=\"hangup\"/>";
    }

    function PSTN_routing() {

        if ($this->is_internalcall == '1') {
            $this->internalcallxml();
            return;
        }
        $query = sprintf("SELECT failover_sipcause_list FROM dialplan WHERE dialplan_id = '%s' limit 1;", $this->dialplan_id);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $failover_sipcause_list = $value;
        }
        $dstarray = str_split($this->destination_number);
        $tmpstr = '';
        $str = '';
        foreach ($dstarray as $key => $value) {
            $tmpstr .= $value;
            $str .= "dial_prefix = '" . $tmpstr . "' or ";
        }

        $str = rtrim($str, ' or ');

        $query = sprintf("SELECT  id, dial_prefix, priority, route_status, carrier_id, start_day, end_day, start_time, end_time, load_share, dialplan_id, '%s' failover_sipcause_list FROM dialplan_prefix_list  where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and dialplan_id = '%s' and (%s);", $failover_sipcause_list, $this->dialplan_id, $str);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $fs = $this->resultset();

        if (count($fs) > 0) {
            $this->writelog('Result are ok');
        } else {
            $str = " '" . $this->destination_number . "' like CONCAT(dial_prefix,'%%') ";
            $query = sprintf("SELECT  id, dial_prefix, priority, route_status, carrier_id, start_day, end_day, start_time, end_time, load_share, dialplan_id, '%s' failover_sipcause_list FROM dialplan_prefix_list  where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and dialplan_id = '%s' and %s ;", $failover_sipcause_list, $this->dialplan_id, $str);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $fs = $this->resultset();
        }

        if (count($fs) > 0) {
            $this->writelog('Result are ok');
            /*
             * Filtaring the route to select for call routing
             */
            $routelist = $this->PSTN_route_selection($fs);
            $this->writelog('Result are ok');
            if (count($routelist) == 0) {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('ROUTINGBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                return;
            }
            $this->writelog('Result are ok');
            $route0 = Array();
            $route1 = Array();
            $route2 = Array();
            $route3 = Array();
            $route4 = Array();
            $route5 = Array();
            $route6 = Array();
            $route_pre = Array();
            foreach ($routelist as $data) {
                if ($data['priority'] == 0)
                    array_push($route0, $data);
                else if ($data['priority'] == 1)
                    array_push($route1, $data);
                else if ($data['priority'] == 2)
                    array_push($route2, $data);
                else if ($data['priority'] == 3)
                    array_push($route3, $data);
                else if ($data['priority'] == 4)
                    array_push($route4, $data);
                else if ($data['priority'] == 5)
                    array_push($route5, $data);
                else
                    array_push($route6, $data);
                if (!in_array($data['priority'], $route_pre)) {
                    array_push($route_pre, $data['priority']);
                }
            }
            $this->writelog('Result are ok');
            foreach ($route_pre as $route_p) {
                $this->writelog('Result are ok');
                if ($route_p == 0) {
                    $routelist = Array();
                    if (count($route0) > 0) {
                        $routelist = $this->PSTN_route_routing_lb($route0);
                        if (count($routelist) > 0) {
                            $this->orderBy($routelist, 'priority ASC');
                            $this->PSTN_route_xml_builder($routelist);
                        }
                    }
                } elseif ($route_p == 1) {
                    $routelist = Array();
                    if (count($route1) > 0) {
                        $routelist = $this->PSTN_route_routing_lb($route1);
                        if (count($routelist) > 0) {
                            $this->orderBy($routelist, 'priority ASC');
                            $this->PSTN_route_xml_builder($routelist);
                        }
                    }
                } elseif ($route_p == 2) {
                    $routelist = Array();
                    if (count($route2) > 0) {
                        $routelist = $this->PSTN_route_routing_lb($route2);
                    }
                    $this->orderBy($routelist, 'priority ASC');
                    $this->PSTN_route_xml_builder($routelist);
                } elseif ($route_p == 3) {
                    $routelist = Array();
                    if (count($route3) > 0) {
                        $routelist = $this->PSTN_route_routing_lb($route3);
                        $this->orderBy($routelist, 'priority ASC');
                    }
                    $this->PSTN_route_xml_builder($routelist);
                } elseif ($route_p == 4) {
                    $routelist = Array();
                    if (count($route4) > 0) {
                        $routelist = $this->PSTN_route_routing_lb($route4);
                    }
                    $this->orderBy($routelist, 'priority ASC');
                    $this->PSTN_route_xml_builder($routelist);
                } elseif ($route_p == 5) {
                    $routelist = Array();
                    if (count($route5) > 0) {
                        $routelist = $this->PSTN_route_routing_lb($route5);
                        $this->orderBy($routelist, 'priority ASC');
                    }
                    $this->PSTN_route_xml_builder($routelist);
                } elseif ($route_p == 6) {
                    $routelist = Array();
                    if (count($route6) > 0) {
                        $routelist = $this->PSTN_route_routing_lb($route6);
                        if (count($routelist) > 0) {
                            $this->orderBy($routelist, 'priority ASC');
                            $this->PSTN_route_xml_builder($routelist);
                        }
                    }
                }
            }
        } else {
            $otherinfo = $this->account_id;
            $this->fail_route_xml('ROUTEMISSING', $otherinfo);
        }
    }

    function random_number($length) {
        return join('', array_map(function($value) {
                    return $value == 1 ? mt_rand(1, 9) : mt_rand(0, 9);
                }, range(1, $length)));
    }

    function PSTN_route_change_callid($carrier_callerid_raw) {
        if (strlen($this->callernumber_user) > 0) {
            $org_callernumber = $this->callernumber_user;
        } else {
            $org_callernumber = $this->callernumber;
        }
        $carrier_callerid = Array();
        foreach ($carrier_callerid_raw[0] as $key => $value) {
            $carrier_callerid[$key] = $value;
        }
        if (count($carrier_callerid) > 0) {
            $remove_string = $carrier_callerid['remove_string'];
            $add_string = $carrier_callerid['add_string'];
            if (trim($remove_string) != '') {
                if (substr($org_callernumber, 0, strlen($remove_string)) == $remove_string AND $remove_string != NULL AND $remove_string != '' AND $remove_string != '%') {
                    $org_callernumber = substr($org_callernumber, strlen($remove_string), strlen($org_callernumber));
                }
            }
            if ($add_string == '%' or $add_string == '')
                $org_callernumber = trim($org_callernumber);
            else if (substr(trim($add_string), -1) == '%') {
                $add_string = preg_replace("/%/", "", $add_string);
                $org_callernumber = trim($add_string . trim($org_callernumber));
            } else {
                $org_callernumber = trim($add_string);
            }
        }
        return $org_callernumber;
    }

    function DID_route_change_callid($carrier_callerid_raw) {
        if (strlen($this->callernumber_user) > 0) {
            $org_callernumber = $this->callernumber_user;
        } else {
            $org_callernumber = $this->callernumber;
        }
        $carrier_callerid = Array();
        foreach ($carrier_callerid_raw[0] as $key => $value) {
            $carrier_callerid[$key] = $value;
        }
        if (count($carrier_callerid) > 0) {
            $remove_string = $carrier_callerid['remove_string'];
            $add_string = $carrier_callerid['add_string'];
            if (trim($remove_string) != '') {
                if (substr($org_callernumber, 0, strlen($remove_string)) == $remove_string AND $remove_string != NULL AND $remove_string != '' AND $remove_string != '%') {
                    $org_callernumber = substr($org_callernumber, strlen($remove_string), strlen($org_callernumber));
                }
            }
            if ($add_string == '%' or $add_string == '')
                $org_callernumber = trim($org_callernumber);
            else if (substr(trim($add_string), -1) == '%') {
                $add_string = preg_replace("/%/", "", $add_string);
                $org_callernumber = trim($add_string . trim($org_callernumber));
            } else {
                $org_callernumber = trim($add_string);
            }
        }
        return $org_callernumber;
    }

    function route_change_prefix($dialnumber, $route2) {
        $dialnumber1 = $dialnumber;
        if (count($route2) > 0) {
            $remove_string = $route2['remove_string'];
            $add_string = $route2['add_string'];
            if (trim($remove_string) != '') {
                if (substr($dialnumber, 0, strlen($remove_string)) == $remove_string AND $remove_string != NULL AND $remove_string != '' AND $remove_string != '%') {
                    $dialnumber1 = substr($dialnumber, strlen($remove_string), strlen($dialnumber1));
                }
            }
            if ($add_string == '%' or $add_string == '') {
                $dialnumber1 = trim($dialnumber1);
            } else if (substr(trim($add_string), -1) == '%') {
                $dialnumber1 = trim($add_string . trim($dialnumber1));
                $dialnumber1 = preg_replace("/%/", "", $dialnumber1);
            } else {
                $dialnumber1 = trim($add_string);
            }
        }
        return $dialnumber1;
    }

    function PSTN_route_xml_builder($routelist) {
        $i = 0;
        foreach ($routelist as $route1) {
            if ($route1['route_status'] == '1') {
                $query = sprintf("SELECT  '%s' dialplan_id, '%s' failover_sipcause_list, carrier.carrier_currency_id tariff_currency_id , carrier.tax1,carrier.tax2, carrier.tax3, carrier.tax_type, carrier.dp,carrier_ips.carrier_ip_id, carrier.carrier_id, carrier.carrier_name, carrier.tariff_id, carrier.carrier_status, carrier.cli_prefer, carrier.carrier_progress_timeout, carrier.carrier_ring_timeout, carrier_ips.ipaddress, carrier_ips.ipaddress_name, carrier_ips.load_share, carrier_ips.priority, carrier_ips.ip_status, carrier_ips.auth_type, carrier_ips.username, carrier_ips.passwd, carrier_codecs, carrier.carrier_cps, carrier.carrier_cc, carrier.carrier_currency_id from carrier INNER JOIN carrier_ips on carrier.carrier_id = carrier_ips.carrier_id where carrier.carrier_id = '%s'  and ip_status = '1';", $route1['dialplan_id'], $route1['failover_sipcause_list'], $route1['carrier_id']);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs2 = $this->resultset();
                if (count($rs2) > 0) {
                    $str = $rs2[0]['carrier_name'] . " Allowed calls " . $rs2[0]['carrier_cc'] . "  running Calls " . $route1['rcalls'];
                    $this->writelog($str);
                    if ($route1['rcalls'] > $rs2[0]['carrier_cc']) {
                        continue;
                    }
                    $query = sprintf("SELECT LENGTH(maching_string) lndata, remove_string, add_string FROM carrier_prefix where carrier_id = '%s' and '%s' like maching_string and route = 'OUTBOUND' order by lndata desc limit 1;", $route1['carrier_id'], $this->destination_number);

                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $tech_prefix = $this->resultset();
                    if (count($tech_prefix[0]) == 0) {
                        $tech_prefix[0]['add_string'] = '';
                        $tech_prefix[0]['remove_string'] = '';
                    }
                    $account_cli = $this->callernumber;
                    $query = sprintf("SELECT LENGTH(maching_string) lndata, carrier_id, remove_string, add_string, action_type FROM carrier_callerid where carrier_id = '%s' and '%s' like maching_string  and route = 'OUTBOUND' ORDER BY lndata desc limit 1;", $route1['carrier_id'], $account_cli);

                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $carrier_caller = $this->resultset();
                    if (count($carrier_caller) == 0) {
                        $otherinfo = $this->account_id;
                        $this->fail_route_xml('CARRIERCLIISSUE', $otherinfo);
                        continue;
                    }
                    $tech_prefix[0]['route_callid'] = $this->PSTN_route_change_callid($carrier_caller);
                    $this->billing_destination_number = $this->destination_number;
                    $dstarray = str_split($this->billing_destination_number);
                    $tmpstr = '';
                    $str = '';

                    foreach ($dstarray as $key => $value) {
                        $tmpstr .= $value;
                        $str .= "prefix = '" . $tmpstr . "' or ";
                    }

                    $str = rtrim($str, ' or ');
                    $query = sprintf("SELECT '%s' as billing_number,  tariff_ratecard_map.ratecard_id, tariff_id, start_day, end_day, start_time, end_time, priority, rate_id, prefix, REPLACE(REPLACE( destination,',',' '),'-',' ') as destination, rate, connection_charge, minimal_time, resolution_time, grace_period, rate_multiplier, rate_addition, rates_status from tariff_ratecard_map  INNER JOIN carrier_rates on carrier_rates.ratecard_id = tariff_ratecard_map.ratecard_id INNER JOIN ratecard on carrier_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'OUTGOING' where tariff_id = '%s' and  WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and  (%s) ORDER BY priority asc, prefix desc, rate DESC,  end_time ASC limit 1;", $this->destination_number, $rs2[0]['tariff_id'], $str);

                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $rs3 = $this->resultset();
                    $data = Array();
                    foreach ($rs2 as $data1) {
                        foreach ($data1 as $key => $value) {
                            if ($key == 'carrier_name' or $key == 'ipaddress_name' or $key == 'destination')
                                $data2[$key] = preg_replace("/[^A-Za-z0-9_ ()]/", '', $value);
                            else
                                $data2[$key] = $value;
                        }
                        $rs4 = array_merge($rs3[0], $data2, $tech_prefix[0]);
                        array_push($data, $rs4);
                    }
                    //$data = $this->PSTN_route_gateway_lb($data);
                    $this->PSTN_route($data);
                } else {
                    if (strlen(trim($this->Gateway_XML)) == 0) {
                        $otherinfo = $this->account_id;
                        $this->fail_route_xml('CARRIERISSUE', $otherinfo);
                    }
                }
            } else {
                if (strlen(trim($this->Gateway_XML)) == 0) {
                    $otherinfo = $this->account_id;
                    $this->fail_route_xml('ROUTINGISSUE', $otherinfo);
                }
            }
        }
    }

    function PSTN_route($data) {
        $lb = $this->Hunt_Network_Addr;
        $lb = $this->lb;
        $gatewaylist = Array();
        $this->writelog(str_replace('"', "'", json_encode($data, JSON_UNESCAPED_UNICODE)));
        foreach ($data as $route2) {
            $carrier_id = $route2['carrier_id'];
            if (!in_array($carrier_id, $gatewaylist)) {
                array_push($gatewaylist, $carrier_id);
            } else {
                continue;
            }
            $this->status = 'OK';
            $this->llr_check($route2);
            $this->writelog("LLR Check status $this->status");


            $this->writelog($route2['ipaddress'] . " and " . $route2['rates_status'] . " and " . $route2['carrier_status'] . " and " . $route2['ip_status']);
            if ($this->status == 'OK') {
                if (strlen($route2['ipaddress']) > 0 and $route2['rates_status'] == '1' and $route2['carrier_status'] == '1' and $route2['ip_status'] == '1') {
                    $this->out_dialplan = '1';
                    $dialnumber = $this->destination_number;
                    $destination_number = $this->route_change_prefix($dialnumber, $route2);
                    if ($this->recording_status == '1' and $this->recording_service == '1') {
                        if ($this->timeout) {
                            $this->Gateway_XML .= "\n<action application=\"set\" data=\"execute_on_answer=sched_hangup +" . $this->timeout . " alloted_timeout \"/>";
                        }
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"RECORD_TITLE = Recording \${destination_number} \${caller_id_number} \${strftime(%Y-%m-%d %H:%M)}\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"RECORD_COPYRIGHT = (c) 1980 Factory Records, Inc.\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"RECORD_SOFTWARE = FreeSWITCH\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"RECORD_ARTIST = Ian Curtis\"/>";
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"RECORD_DATE = \${strftime(%Y-%m-%d %H:%M)}\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"RECORD_STEREO = true\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"media_bug_answer_req=true\"/>";
                        $this->Gateway_XML .= "\n<action application=\"record_session\" data=\"$\${recordings_dir}/" . $this->account_id . "/\${strftime(%Y%m%d)}/\${strftime(%Y%m%d%H%M%S)}_\${destination_number}_\${caller_id_number}.wav\"/>";
                    }
                    if (strlen(trim($route2['failover_sipcause_list'])) > 0) {
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"continue_on_fail=16,UNKNOWN,USER_BUSY,DESTINATION_OUT_OF_ORDER,NO_USER_RESPONSE,CHANNEL_UNACCEPTABLE,NORMAL_CIRCUIT_CONGESTION,NETWORK_OUT_OF_ORDER,NORMAL_TEMPORARY_FAILURE,SERVICE_NOT_IMPLEMENTED,CALL_REJECTED,SWITCH_CONGESTION,REQUESTED_CHAN_UNAVAIL,BEARERCAPABILITY_NOTAVAIL,USER_BUSY,NO_ANSWER,410,501,504,401,402,403,503,408,PROGRESS_TIMEOUT,31,47,17,18,27,41,63," . trim($route2['failover_sipcause_list']) . "\"/>";
                    } else {
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"continue_on_fail=TRUE\"/>";
                    }
                    if ($this->customers['media_transcoding'] == '1') {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=1\"/>";
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_h_X-MEDIATRA=1\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"bypass_media=false\"/>";
                    } else {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=0\"/>";
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=0\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"bypass_media=true\"/>";
                    }

                    if ($route2['cli_prefer'] == 'pid') {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_cid_type=pid\"/>";
                    } else if ($route2['cli_prefer'] == 'rpid') {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_cid_type=rpid\"/>";
                    } else {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_cid_type=none\"/>";
                    }

                    $gateway_ipaddress_name = trim($route2[carrier_ip_id]);

                    $destination_number1 = preg_replace("/#/", "T", $destination_number);

                    $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_ring=curl " . APIDOAMIN . "api/api.php?r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";


                    $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl  " . APIDOAMIN . "api/api.php?r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";

                    $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";


                    $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?r=answer&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";

                    $gateway_ipaddress_name = '';
                    if ($route2['carrier_ring_timeout'] > 0) {
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"call_timeout=" . $route2['carrier_ring_timeout'] . "\"/>";
                    } else {
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"call_timeout=60\"/>";
                    }
                    if ($route2['carrier_progress_timeout'] > 0) {
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"progress_timeout=" . $route2['carrier_progress_timeout'] . "\"/>";
                    } else {
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"progress_timeout=10\"/>";
                    }
                    $route_callid = $route2['route_callid'];
                    $route2['src_caller'] = $this->callernumber_user;
                    $route2['src_callee'] = $this->destination_number_user;
                    $route2['dst_caller'] = $route_callid;
                    $route2['dst_callee'] = $destination_number;
                    $route2['ratio'] = $this->ratio;
                    $gateway_username = $route2['username'];
                    $gateway_passwd = $route2['passwd'];
                    $gateway_type = $route2['auth_type'];
                    unset($route2['remove_string']);
                    unset($route2['add_string']);
                    unset($route2['running_percentage']);
                    unset($route2['req_percentage']);
                    unset($route2['username']);
                    unset($route2['passwd']);
                    unset($route2['start_day']);
                    unset($route2['end_day']);
                    unset($route2['end_time']);
                    unset($route2['priority']);
                    unset($route2['carrier_progress_timeout']);
                    unset($route2['carrier_ring_timeout']);
                    unset($route2['gateway_load_share']);
                    unset($route2['gateway_priority']);
                    unset($route2['ip_status']);
                    unset($route2['auth_type']);

                    if (count($this->route22) == 0) {
                        $this->route22 = $route2;
                    }

                    $this->Gateway_XML .= "\n<action application=\"set\" data=\"gatewaydata=" . str_replace('"', "'", json_encode($route2, JSON_UNESCAPED_UNICODE)) . "\"/>";
                    $this->Gateway_XML .= "\n<action application=\"export\" data=\"gatewaydata=" . str_replace('"', "'", json_encode($route2, JSON_UNESCAPED_UNICODE)) . "\"/>";
                    $commoncodecs = '';
                    if (strlen($route2['carrier_codecs']) > 0 and strlen($this->customers['account_codecs']) > 0) {
                        $carriercodecs = explode(",", $route2['carrier_codecs']);
                        $usercodecs = explode(",", $this->customers['account_codecs']);
                        $commoncodecs = array_intersect($usercodecs, $carriercodecs);
                        $commoncodecs = implode(',', $commoncodecs);
                    }

                    $this->writelog($this->callernumber);

                    if (strlen(trim($route_callid)) > 0) {
                        $cli_from = $route_callid;
                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_number=" . $route_callid . "\"/>";
                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_name=" . $route_callid . "\"/>";

                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_number=" . $route_callid . "\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_name=" . $route_callid . "\"/>";

                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-FROMURI=" . $route_callid . "@" . $lb . "\"/>";
                    } else {
                        $cli_from = $this->callernumber;
                        if ($cli_from == '' or strlen($cli_from) == 0)
                            $cli_from = "unknown";
                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_number=" . $cli_from . "\"/>";
                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_name=" . $cli_from . "\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_number=" . $cli_from . "\"/>";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_name=" . $cli_from . "\"/>";
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-FROMURI" . $cli_from . "@" . $lb . "\"/>";
                    }


                    if (strlen($route2['carrier_codecs']) > 0) {
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:absolute_codec_string=" . $route2['carrier_codecs'] . "\"/>";
                    } else {
                        $this->Gateway_XML .= "\n <action application=\"export\"  data=\"nolocal:absolute_codec_string=\${ep_codec_string}\"/>";
                    }

                    $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-CARRIERID=" . trim($route2['carrier_id']) . "\"/>";
                    $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-CARRIERCPS=" . trim($route2['carrier_cps']) . "\"/>";

                    $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-DSTURI=sip:" . trim($destination_number) . "@" . trim($route2['ipaddress']) . "\"/>";
                    if ($gateway_type == 'CUSTOMER') {
                        $this->Gateway_XML .= "\n   <action application=\"export\" data=\"sip_contact_user=" . $cli_from . "\"/>";

                        $this->Gateway_XML .= "\n   <action application=\"export\" data=\"sip_contact_user=" . $gateway_username . "\"/>";
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_from_uri=sip:" . $gateway_username . "@" . $lb . "\"/>";

                        $this->Gateway_XML .= "\n <action application=\"bridge\" data=\"{sip_invite_domain=" . $lb . ",sip_auth_username=" . $gateway_username . ",sip_auth_password=" . $gateway_passwd . "}sofia/internal/" . $destination_number . "@" . $lb . "\"/>";
                    } else {
                        if ($cli_from == '' or strlen($cli_from) == 0)
                            $cli_from = "unknown";
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_from_uri=sip:" . $cli_from . "@" . $lb . "\"/>";
                        $this->Gateway_XML .= "\n   <action application=\"export\" data=\"sip_contact_user=" . $cli_from . "\"/>";
                        $this->Gateway_XML .= "\n <action application=\"bridge\" data=\"{sip_invite_domain=" . $lb . "}sofia/internal/" . $destination_number . "@" . $lb . "\"/>";
                    }
                    $cli_from = '';
                } else {
                    $otherinfo = "RS " . $route2['rates_status'] . " CS " . $route2['carrier_status'] . " IS " . $route2['ip_status'] . " ipaddress " . $route2['ipaddress'];
                    $this->fail_route_xml('ROUTESTATUSISSUE', $otherinfo);
                }
            } else {
                $otherinfo = $this->account_id;
                $this->fail_route_xml('LLRISSUE', $otherinfo);
            }
        }
    }

    function PSTN_reseller($user) {
        $query = sprintf("select round_logic, account_level, account.account_id, customer_voipminuts.tariff_id, account.account_type, parent_account_id, dp,account_cc, account_cps,tax1,tax2,tax3,tax_type, status_id account_status, currency_id, cli_check, dialpattern_check, llr_check from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id where account.account_id = '%s';", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $reseller[$key] = $value;
        }
        if ($reseller['account_level'] > 0)
            $reseelerinfo = 'Reseller' . $reseller['account_level'];
        else
            $reseelerinfo = 'Reseller1';
        /*
         * Reseller Account is inactive
         */
        if ($reseller['account_status'] == 0) {
            $otherinfo = $reseller['account_id'];
            $this->fail_route_xml('RESELLERINACTIVE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata[$reseelerinfo] = $reseller;
            return;
        }

        /*
         * Reseller currency and user currency not matched
         */

        if ($reseller['currency_id'] != $this->customers['currency_id']) {
            $otherinfo = $reseller['account_id'];
            $this->fail_route_xml('RESELLERCURRENCY', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata[$reseelerinfo] = $reseller;
            return;
        }
        $reseller2 = $this->internalcall_reseller($user);

        if ($this->is_internalcall == '1') {
            $reseller = $reseller2;
        }
        if ($this->is_internalcall == '0') {
            $callcounts = $this->PSTN_loadbalance_get_db($user, $reseelerinfo);
            if ($reseller['account_cc'] <= $callcounts) {
                $otherinfo = $reseller['account_id'];
                $this->fail_route_xml('USERCC', $otherinfo);
                $this->status = 'FAIL';
                return;
            }

            $this->CCSTRING = $this->CCSTRING . ":" . $user;

            /*
             * Check the Reseller Dialplan and dialed prefix blocking
             */
            $this->PSTN_reseller_dialplan($reseller);

            /*
             * User Dialplan not configured.
             */
            if ($this->status == 'FAIL') {
                $this->customersdata[$reseelerinfo] = $reseller;
                return;
            }


            /*
             * Check Reseller CallerIDs
             */

            if ($reseller['cli_check'] == '1') {
                $this->PSTN_reseller_change_callid($reseller);
                if ($this->cli_auth == 0) {
                    $otherinfo = $reseller['account_id'];
                    $this->fail_route_xml('RESELLERCLI', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customersdata[$reseelerinfo] = $reseller;
                    return;
                }
            }

            $query = sprintf("SELECT id, credit_limit - balance balance from customer_balance where account_id = '%s';", $user);

            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            foreach ($rs[0] as $key => $value) {
                $reseller[$key] = $value;
            }

            if ($reseller['balance'] == 0 or $reseller['balance'] < 0) {
                $sql = sprintf("update account set account_status = '-2' where account_id = '%s';", $user);
                $this->query('SWITCH', $sql);
                $this->execute();
            }
            if ($reseller['balance'] < 0.1) {
                $otherinfo = $reseller['account_id'];
                $this->fail_route_xml('RESELLERBALANCE', $otherinfo . " " . $reseller['balance']);
                $this->status = 'FAIL';
                $this->customersdata[$reseelerinfo] = $reseller;
                return;
            }

            if ($this->sign($reseller['balance']) == '-1')
                $reseller['orgbalance'] = abs($reseller['balance']);
            else
                $reseller['orgbalance'] = "-" . $reseller['balance'];


            /*
             * Reseller don't have sufucuient balance to process the call.
             */
            if ($reseller['balance'] < 0.1) {
                $otherinfo = $reseller['account_id'];
                $this->fail_route_xml('RESELLERBALANCE', $otherinfo . " " . $reseller['balance']);
                $this->status = 'FAIL';
                $this->customersdata[$reseelerinfo] = $reseller;
                return;
            }



            /*
             * Check the User Dialplan and dialed prefix blocking
             */
            $this->PSTN_reseller_route_check($reseller);
            if ($this->status == 'FAIL') {
                $this->customersdata[$reseelerinfo] = $reseller;
                return;
            }


            $dstarray = str_split($this->billing_destination_number);
            $tmpstr = '';
            $str = '';
            foreach ($dstarray as $key => $value) {
                $tmpstr .= $value;
                $str .= "prefix = '" . $tmpstr . "' or ";
            }
            $str = rtrim($str, ' or ');

            $query = sprintf("SELECT tariff.tariff_status,  tariff_ratecard_map.ratecard_id, tariff_ratecard_map.tariff_id, tariff_ratecard_map.start_day, tariff_ratecard_map.end_day, tariff_ratecard_map.start_time, tariff_ratecard_map.end_time, customer_rates.prefix, REPLACE(REPLACE( customer_rates.destination,',',' '),'-',' ') as destination , customer_rates.rate, customer_rates.connection_charge, customer_rates.minimal_time, customer_rates.resolution_time, customer_rates.grace_period, customer_rates.rate_multiplier, customer_rates.rate_addition, customer_rates.rates_status, tariff.tariff_currency_id     FROM tariff_ratecard_map  INNER JOIN customer_rates on customer_rates.ratecard_id = tariff_ratecard_map.ratecard_id  INNER JOIN ratecard on customer_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'OUTGOING' INNER JOIN tariff on  tariff.tariff_id = tariff_ratecard_map.tariff_id where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and tariff_ratecard_map.tariff_id = '%s' and (%s)  ORDER BY priority asc, prefix desc,  rate ASC, end_time ASC limit 1;", $reseller['tariff_id'], $str);


            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            /*
             * Reseller rates not found for dialed number
             */
            if (count($rs) == 0) {
                $otherinfo = $reseller['account_id'];
                $this->fail_route_xml('RESELLERRATE', $otherinfo);
                $this->status = 'FAIL';
                $this->customersdata[$reseelerinfo] = $reseller;
                return;
            }


            foreach ($rs[0] as $key => $value) {
                $reseller[$key] = $value;
            }


            /*
             * Bundle & Plan
             */
            $this->bundle_package_management($user);
            $query = sprintf("SELECT bundle_option, bundle_id, prefix , bundle_package_prefixes.bundle_package_id, monthly_charges,   bundle1_type, bundle1_value, bundle2_type, bundle2_value, bundle3_type, bundle3_value from bundle_package INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = bundle_package.bundle_package_id  where bundle_package_prefixes.bundle_package_id in (select bundle_package_id from bundle_account where account_id  = '%s') and  (%s) and LENGTH(prefix) > 0  order by prefix desc limit 1;", $user, $str);

            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            if (count($rs) > 0) {
                foreach ($rs[0] as $key => $value) {
                    $reseller[$key] = $value;
                }
            }
            if (strlen($reseller['bundle_package_id']) > 0 and $reseller['bundle_option'] == '1') {
                $query = sprintf("SELECT account_id, rule_type, yearmonth,  sum(total_allowed) as  total_allowed , sum(sdr_consumption) as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s' and rule_type in ('bundle1', 'bundle2', 'bundle2') and  '%s'  BETWEEN service_startdate and service_stopdate;", $user, $reseller['bundle_package_id'], date("Y-m-d"));

                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs3 = $this->resultset();

                $available_bundle1 = '';
                $available_bundle2 = '';
                $available_bundle3 = '';
                $insert_allow = 1;
                if (count($rs3) > 0) {
                    foreach ($rs3 as $data_b) {
                        if ($data_b['rule_type'] == 'bundle1') {
                            $available_bundle1 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                            $insert_allow = 0;
                        }
                        if ($data_b['rule_type'] == 'bundle2') {
                            $available_bundle2 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                            $insert_allow = 0;
                        }
                        if ($data_b['rule_type'] == 'bundle3') {
                            $available_bundle3 = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                            $insert_allow = 0;
                        }
                    }
                }


                foreach ($rs as $data) {
                    if ($data['bundle_id'] == '1') {
                        $bundle1 = $reseller['bundle1_value'];
                        if ($reseller['bundle1_type'] == 'MINUTE') {
                            $duration_b = $available_bundle1;
                            $reseller['bundle_type'] = 'MINUTE';
                            $reseller['bundle_value'] = $duration_b;
                            $reseller['bundle_number'] = 'bundle1';
                        }
                        if ($reseller['bundle1_type'] == 'COST') {
                            $balance = $available_bundle1;
                            $reseller['bundle_type'] = 'COST';
                            $reseller['bundle_value'] = $balance_b;
                            $reseller['bundle_number'] = 'bundle1';
                        }
                    } elseif ($data['bundle_id'] == '2') {
                        $bundle2 = $reseller['bundle2_value'];
                        if ($reseller['bundle2_type'] == 'MINUTE') {
                            $duration_b = $available_bundle2;
                            $reseller['bundle_type'] = 'MINUTE';
                            $reseller['bundle_value'] = $duration_b;
                            $reseller['bundle_number'] = 'bundle2';
                        }
                        if ($reseller['bundle2_type'] == 'COST') {
                            $balance = $available_bundle2;
                            $reseller['bundle_type'] = 'COST';
                            $reseller['bundle_value'] = $balance_b;
                            $reseller['bundle_number'] = 'bundle2';
                        }
                    } elseif ($data['bundle_id'] == '3') {
                        $bundle1 = $reseller['bundle3_value'];
                        if ($reseller['bundle3_type'] == 'MINUTE') {
                            $duration_b = $available_bundle3;
                            $reseller['bundle_type'] = 'MINUTE';
                            $reseller['bundle_value'] = $duration_b;
                            $reseller['bundle_number'] = 'bundle3';
                        }
                        if ($reseller['bundle3_type'] == 'COST') {
                            $balance_b = $available_bundle3;
                            $reseller['bundle_type'] = 'COST';
                            $reseller['bundle_value'] = $balance_b;
                            $reseller['bundle_number'] = 'bundle3';
                        }
                    }
                }
            }

            /*
             * check the LLR(lossless Routing) for reseller
             */
            $llr = $reseller['rate'] + $reseller['connection_charge'];
            if ($reseller['llr_check'] == '1') {
                $llr = $reseller['rate'] + $reseller['connection_charge'];
                if ($this->LLRRates >= $llr) {
                    $this->LLRRates = $llr;
                } else {
                    $otherinfo = $reseller['account_id'];
                    $this->fail_route_xml('RESELLERLLRISSUE', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customersdata[$reseelerinfo] = $reseller;
                    return;
                }
            }


            /*
             * Reseller and Tariff currency not same
             */
            if ($reseller['currency_id'] != $reseller['tariff_currency_id']) {
                $otherinfo = $reseller['account_id'] . "=>" . $reseller['tariff_id'];
                $this->fail_route_xml('RESELLERTARIFFCURRENCY', $otherinfo);
                $this->status = 'FAIL';
                $this->customersdata[$reseelerinfo] = $reseller;
                return;
            }

            /*
             * if Reseller tariff is inactive
             */
            if ($reseller['tariff_status'] == 0) {
                $otherinfo = $reseller['account_id'] . "=>" . $reseller['tariff_id'];
                $this->fail_route_xml('RESELLERTARIFFINACTIVE', $otherinfo);
                $this->status = 'FAIL';
                $this->customersdata[$reseelerinfo] = $reseller;
                return;
            }

            /*
             * if Reseller rates is blocked
             */
            if ($reseller['rates_status'] == 0) {
                $otherinfo = $reseller['account_id'] . "=>" . $reseller['tariff_id'];
                $this->fail_route_xml('RESELLERRATEBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                $this->customersdata[$reseelerinfo] = $reseller;
                return;
            }

            if ($reseller['bundle_type'] == 'COST')
                $reseller['balance'] = $reseller['balance'] + $reseller['bundle_value'];


            $reseller['duration'] = 0;
            $reseller['duration'] = $this->duration($reseller);


            if ($reseller['bundle_type'] == 'MINUTE')
                $reseller['duration'] = $reseller['duration'] + $reseller['bundle_value'];

            /*
             * Bunlde duration
             * 
             */

            array_push($this->str, $reseller['duration']);
            if ($reseller['account_level'] == '1')
                $level = 1;

            if ($reseller['account_level'] == '2')
                $level = 2;

            if ($reseller['account_level'] == '3')
                $level = 3;

            unset($reseller['account_status']);
            unset($reseller['cli_check']);
            unset($reseller['dialpattern_check']);
            unset($reseller['llr_check']);
            unset($reseller['tariff_status']);
            unset($reseller['end_time']);
            unset($reseller['start_time']);
            unset($reseller['end_day']);
            unset($reseller['start_day']);
            unset($reseller['rates_status']);
            unset($reseller['bundle1_type']);
            unset($reseller['bundle1_value']);
            unset($reseller['bundle2_type']);
            unset($reseller['bundle2_value']);
            unset($reseller['bundle3_type']);
            unset($reseller['bundle3_value']);
        }
        $this->customersdata[$reseelerinfo] = $reseller;
        $parent_account_id = '';
        $parent_account_id = $reseller['parent_account_id'];
        $this->writelog("RESELLER" . $level . ":" . $reseller['parent_account_id'] . " " . strlen($reseller['parent_account_id']));

        if ($parent_account_id == '' or $parent_account_id == NULL or $reseller['account_level'] == 0) {
            return;
        }
        if (strlen($parent_account_id) > 0) {
            $reseller = Array();
            $level = $level + 1;
            $this->writelog("RESELLER" . $level . ":" . $parent_account_id);
            $this->PSTN_reseller($parent_account_id);
            $parent_account_id = '';
            return;
        }
    }

    function duration($user) {
        /* $this->str 
         * variable used to store the all level users and carrier duration to findout the minimum duration value 
         */
        $duration = $this->call_duration($user);
        array_push($this->str, $duration);
        $maxdur = $this->customers['max_callduration'] * 60;
        array_push($this->str, $maxdur);
        return $duration;
    }

    function call_duration($user) {
        /* Loss less routing check is not implemented */
        $duration = 0;
        if ($user['rate'] > 0) {
            $minimal_cost = ($user['rate'] / 60) * $user['minimal_time'];
            $resolution_cost = ($user['rate'] / 60) * $user['resolution_time'];
            $effective_balance = $user['balance'] - $user['connection_charge'] - $minimal_cost;
            if ($effective_balance > 0) {
                $duration = $user['minimal_time'] + floor($effective_balance / $resolution_cost) * $user['resolution_time'];
            }
        } elseif ($this->callfrom_did == 1 or $user['rate'] == 0) {
            $duration = 7200;
        }


        if ($user['bundle_type'] == 'MINUTE')
            $duration = $duration + ($user['bundle_value'] * 60);
        return $duration;
    }

    function usercodeclist() {
        $codecs = array();
        $sdp = $this->request['variable_switch_r_sdp'];
        if (strlen(trim($sdp)) > 0) {
            
        } else {
            $sdp = $this->request['variable_r_sdp'];
        }
        if (strstr($sdp, "G729")) {

            array_push($codecs, "G729");
        }
        if (strstr($sdp, "g729")) {
            array_push($codecs, "g729");
        }
        if (strstr($sdp, "PCMU")) {

            array_push($codecs, "PCMU");
        }
        if (strstr($sdp, "pcmu")) {
            array_push($codecs, "pcmu");
        }
        if (strstr($sdp, "pcma")) {
            array_push($codecs, "pcma");
        }
        if (strstr($sdp, "PCMA")) {
            array_push($codecs, "PCMA");
        }
        if (strstr($sdp, "G711")) {
            array_push($codecs, "G711");
        }
        if (strstr($sdp, "G722.1")) {
            array_push($codecs, "G722.1");
        }
        if (strstr($sdp, "G722")) {
            array_push($codecs, "G722");
        }
        if (strstr($sdp, "GSM")) {
            array_push($codecs, "GSM");
        }
        if (strstr($sdp, "gsm")) {
            array_push($codecs, "gsm");
        }
        if (strstr($sdp, "G723")) {
            array_push($codecs, "G723");
        }
        if (strstr($sdp, "g723")) {
            array_push($codecs, "g723");
        }
        if (strstr($sdp, "G726")) {
            array_push($codecs, "G726");
        }

        if (strstr($sdp, "g726")) {
            array_push($codecs, "g726");
        }

        if (strstr($sdp, "AMR")) {
            array_push($codecs, "AMR");
        }

        if (strstr($sdp, "amr")) {
            array_push($codecs, "amr");
        }

        if (strstr($sdp, "speex")) {
            array_push($codecs, "speex");
        }

        if (strstr($sdp, "SPEEX")) {
            array_push($codecs, "SPEEX");
        }

        if (strstr($sdp, "SILK")) {
            array_push($codecs, "SILK");
        }

        if (strstr($sdp, "silk")) {
            array_push($codecs, "silk");
        }
        if (strstr($sdp, "OPUS")) {
            array_push($codecs, "OPUS");
        }
        if (strstr($sdp, "opus")) {
            array_push($codecs, "opus");
        }
        $codecs = implode(',', $codecs);

        if (strlen(trim($codecs)) > 0)
            return $codecs;
        else
            return $sdp;
    }

    function DID_route_dialplan_xml() {
        array_push($this->str, 7200);
        $this->timeout = min($this->str);

        $responce = "<?xml version = \"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>
                            <document type=\"OvSwitch/xml\">
                            <section name=\"dialplan\" description=\"RE Dial Plan For OvSwitch-2.0.0\">";
        $responce .= "\n<context name=\"default\">";
        $responce .= "\n<extension name=\"outbound_international\">
                <condition field=\"destination_number\" expression=\"^(.+)$\">";
        $responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=0\"/>";
        $responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=0\"/>";
        $responce .= "\n<action application=\"set\" data=\"hangup_after_bridge=TRUE\"/>";

        $codecs = $this->usercodeclist();
        $responce .= "\n<action application=\"set\" data=\"CALLTYPE=INCOMING\"/>";
        $responce .= "\n<action application=\"export\" data=\"CALLTYPE=INCOMING\"/>";
        $responce .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
        $responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
        $responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
        $responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
        $responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
        $responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
        $responce .= "\n<action application=\"set\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
        $responce .= "\n<action application=\"export\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";

        if (strlen($this->Gateway_XML) > 0) {
            $responce .= $this->Gateway_XML;
            if (strlen($this->Gateway_XML1) > 0) {
                $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
                $responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
                $responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
                $responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
                $responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
                $responce .= "\n<action application=\"set\" data=\"userdata=" . $this->rates . "\"/>";
                $responce .= "\n<action application=\"export\" data=\"userdata=" . $this->rates . "\"/>";
                $responce .= "\n<action application=\"set\" data=\"INCOMING2PSTN=1\"/>";
                $responce .= "\n<action application=\"export\" data=\"INCOMING2PSTN=1\"/>";
                $responce .= $this->Gateway_XML1;
            }
            $this->livecalls();
        } else {
            $responce .= $this->Gateway_XML2;
            if (strlen($this->Gateway_XML1) > 0) {
                $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
                $responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
                $responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
                $responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
                $responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
                $responce .= "\n<action application=\"set\" data=\"userdata=" . $this->rates . "\"/>";
                $responce .= "\n<action application=\"export\" data=\"userdata=" . $this->rates . "\"/>";
                $responce .= "\n<action application=\"set\" data=\"INCOMING2PSTN=1\"/>";
                $responce .= "\n<action application=\"export\" data=\"INCOMING2PSTN=1\"/>";
                $responce .= $this->Gateway_XML1;
            }
        }
        $responce .= "\n
                </condition>
                    </extension>
                    </context>
                    </section>
                    </document>";


        RETURN $responce;
    }

    function PSTN_route_dialplan_xml() {
        array_push($this->str, 7200);
        $this->timeout = min($this->str);

        $responce .= "<?xml version = \"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>
                            <document type=\"OvSwitch/xml\">";

        $responce .= $this->directory;
        $responce .= "<section name=\"dialplan\" description=\"RE Dial Plan For OV500-2.0.0\">";
        $responce .= "\n<context name=\"default\">";
        $responce .= "\n<extension name=\"outbound_international\">
                <condition field=\"destination_number\" expression=\"^(.+)$\">";
        $responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=0\"/>";
        $responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=0\"/>";
        $responce .= "\n<action application=\"set\" data=\"hangup_after_bridge=TRUE\"/>";
        $responce .= "\n<action application=\"pre_answer\"/>";
        $codecs = $this->usercodeclist();
        $responce .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
        $responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
        $responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
        $responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
        $responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
        $responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
        $responce .= "\n<action application=\"set\" data=\"userdata=" . $this->rates . "\"/>";
        $responce .= "\n<action application=\"export\" data=\"userdata=" . $this->rates . "\"/>";
        if ($this->timeout) {
            $responce .= "\n<action application=\"set\" data=\"execute_on_answer=sched_hangup +" . $this->timeout . " alloted_timeout \"/>";
        }
        if (strlen($this->Gateway_XML) > 0) {
            $responce .= $this->Gateway_XML;
            $this->livecalls();
        } else {
            $responce .= $this->Gateway_XML1;
        }
        $responce .= "\n
                </condition>
                    </extension>
                    </context>
                    </section>
                    </document>";


        RETURN $responce;
    }

    function livecalls_in() {
        // if ($this->status != 'FAIL') {
        if (count($this->customersdata) > 0) {
            foreach ($this->customersdata as $userkey => $uservalue) {
                if ($userkey == 'user') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'customer_currency_id' or $key == 'ipaddress' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate' or $key == 'src_caller' or $key == 'src_callee' or $key == 'src_ip') {
                            $data = $data . "customer_$key = '" . addslashes($value) . "',";
                        } elseif ($key == 'company_name') {
                            $data = $data . "customer_company = '" . addslashes($value) . "',";
                        }
                    }
                }
                if ($userkey == 'Reseller1') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                            $data = $data . "reseller1_$key = '" . addslashes($value) . "',";
                        }
                    }
                }
                if ($userkey == 'Reseller2') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                            $data = $data . "reseller2_$key = '" . addslashes($value) . "',";
                        }
                    }
                }
                if ($userkey == 'Reseller3') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                            $data = $data . "reseller3_$key = '" . addslashes($value) . "',";
                        }
                    }
                }
            }
            foreach ($this->route22 as $key => $value) {

                if ($key == 'carrier_id' or $key == 'carrier_name' or $key == 'carrier_currency_id')
                    $data = $data . "$key='" . addslashes($value) . "',";

                if ($key == 'ratecard_id' or $key == 'tariff_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate' or $key == 'src_caller' or $key == 'src_callee' or $key == 'dst_caller' or $key == 'dst_callee') {
                    $data = $data . "carrier_$key = '" . addslashes($value) . "',";
                }
                if ($key == 'ipaddress')
                    $data = $data . "carrier_ipaddress='" . addslashes($value) . "',";
                if ($key == 'ipaddress_name')
                    $data = $data . "carrier_ipaddress_name='" . addslashes($value) . "',";
            }

            $data = $data . "dialplan_id = '" . addslashes($this->dialplan_id) . "',";
            $data = $data . "common_uuid = '" . addslashes($this->uuid) . "',";
            $data = $data . "start_time= '" . addslashes($this->request['Event-Date-Local']) . "',";
            $data = $data . "fs_host= '" . addslashes($this->request['FreeSWITCH-IPv4']) . "',";
            $data = $data . "loadbalancer= '" . trim($this->Hunt_Network_Addr) . "',";
            $data = $data . "callstatus='progress',";
            $data = $data . "call_flow='DID'";

            $livecalldata = rtrim($data, ',');
            $query = "insert into livecalls  set " . $livecalldata;
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $this->execute();
            $this->route22 = Array();
        }
        //}
    }

    function livecalls() {
        if ($this->status != 'FAIL') {
            if (count($this->customersdata) > 0) {
                foreach ($this->customersdata as $userkey => $uservalue) {
                    if ($userkey == 'user') {
                        foreach ($uservalue as $key => $value) {
                            if ($key == 'account_id' or $key == 'tariff_id' or $key == 'customer_currency_id' or $key == 'ipaddress' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate' or $key == 'src_caller' or $key == 'src_callee' or $key == 'src_ip') {
                                $data = $data . "customer_$key = '" . addslashes($value) . "',";
                            } elseif ($key == 'company_name') {
                                $data = $data . "customer_company = '" . addslashes($value) . "',";
                            }
                        }
                    }
                    if ($userkey == 'Reseller1') {
                        foreach ($uservalue as $key => $value) {
                            if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                                $data = $data . "reseller1_$key = '" . addslashes($value) . "',";
                            }
                        }
                    }
                    if ($userkey == 'Reseller2') {
                        foreach ($uservalue as $key => $value) {
                            if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                                $data = $data . "reseller2_$key = '" . addslashes($value) . "',";
                            }
                        }
                    }
                    if ($userkey == 'Reseller3') {
                        foreach ($uservalue as $key => $value) {
                            if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                                $data = $data . "reseller3_$key = '" . addslashes($value) . "',";
                            }
                        }
                    }
                }

                foreach ($this->route22 as $key => $value) {

                    if ($key == 'carrier_id' or $key == 'carrier_name' or $key == 'carrier_currency_id')
                        $data = $data . "$key='" . addslashes($value) . "',";

                    if ($key == 'ratecard_id' or $key == 'tariff_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate' or $key == 'src_caller' or $key == 'src_callee' or $key == 'dst_caller' or $key == 'dst_callee') {
                        $data = $data . "carrier_$key = '" . addslashes($value) . "',";
                    }
                    if ($key == 'ipaddress')
                        $data = $data . "carrier_ipaddress='" . addslashes($value) . "',";
                    if ($key == 'ipaddress_name')
                        $data = $data . "carrier_ipaddress_name='" . addslashes($value) . "',";
                }



                $data = $data . "dialplan_id = '" . addslashes($this->dialplan_id) . "',";
                $data = $data . "common_uuid = '" . addslashes($this->uuid) . "',";
                $data = $data . "start_time= '" . addslashes($this->request['Event-Date-Local']) . "',";
                $data = $data . "fs_host= '" . addslashes($this->request['FreeSWITCH-IPv4']) . "',";
                $data = $data . "loadbalancer= '" . trim($this->Hunt_Network_Addr) . "',";
                $data = $data . "callstatus='progress'";

                $livecalldata = rtrim($data, ',');
                $query = "insert into livecalls  set " . $livecalldata;


                $this->writelog($query);
                $this->query('SWITCH', $query);
                $this->execute();
                $this->route22 = Array();
            }
        }
    }

    function fail_route_xml_inbound($errer, $otherinfo) {
        $key = 'FSSTRING';
        $cause_result = $this->sipcode_search($this->fscodlist, $key, $errer, '2');
        if (count($cause_result) > 0) {
            $causeQ850 = $cause_result['Q850CODE'];
            $causeSIP = $cause_result['SIPCODE'];
            $fscause = $cause_result['FSSTRING'];
            $custommessage = $cause_result['CUSTOMMESSAGE'];
        }
        if ($this->Gateway_XML2 == '') {
            if (strlen($custommessage) > 0) {
                $this->error = $errer;
                $this->Gateway_XML2 = "\n<action application=\"set\" data=\"fs_errorcode=" . $errer . " " . $otherinfo . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"fs_errorcode=" . $errer . " " . $otherinfo . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"set\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"sip_term_status=" . $causeSIP . " " . $otherinfo . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"respond\" data=\"" . $causeSIP . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"hangup\"/>";
            } else {
                $this->error = $errer;
                $this->Gateway_XML2 = "\n<action application=\"set\" data=\"fs_errorcode_in=" . $errer . " " . $otherinfo . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"fs_errorcode_in=" . $errer . " " . $otherinfo . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"set\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $this->Gateway_XML2 .= "<action application=\"hangup\" data=\"$fscause\"/>";
            }
        }
        return;
    }

    function sipcode_search($array, $key, $value, $group = 1) {

        if (is_array($array)) {
            foreach ($array as $subarray) {
                if ($subarray[$key] == $value and $group == $subarray['GROUP']) {
                    return $subarray;
                }
            }
            return array("GROUP" => "1", "Q850CODE" => "$value", "SIPCODE" => "", "FSSTRING" => "", "CUSTOMMESSAGE" => "");
        }
    }

    function fail_route_xml($errer, $otherinfo) {
        $key = 'FSSTRING';
        $cause_result = $this->sipcode_search($this->fscodlist, $key, $errer, '2');
        if (count($cause_result) > 0) {
            $causeQ850 = $cause_result['Q850CODE'];
            $causeSIP = $cause_result['SIPCODE'];
            $fscause = $cause_result['FSSTRING'];
            $custommessage = $cause_result['CUSTOMMESSAGE'];
        }
        if ($this->Gateway_XML1 == '') {
            if (strlen($custommessage) > 0) {
                $this->error = $errer;
                $this->Gateway_XML1 = "\n<action application=\"set\" data=\"fs_errorcode=" . $errer . " " . $otherinfo . "\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"fs_errorcode=" . $errer . " " . $otherinfo . "\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"set\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"sip_term_status=" . $causeSIP . " " . $otherinfo . "\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"respond\" data=\"" . $causeSIP . "\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"hangup\"/>";
            } else {
                $this->error = $errer;
                $this->Gateway_XML1 = "\n<action application=\"set\" data=\"fs_errorcode=" . $errer . " " . $otherinfo . "\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"fs_errorcode=" . $errer . " " . $otherinfo . "\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"set\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $this->Gateway_XML1 .= "<action application=\"hangup\" data=\"$fscause\"/>";
            }
        }
        return;
    }

    function BillDuration($duration, $rateinfo) {
        $duration_new = 0;
        if ($duration > 0) {
            if (abs($rateinfo['grace_period']) > $duration) {
                $duration_new = 0;
            } else {
                if (abs($rateinfo['minimal_time']) > $duration) {
                    $duration_new = abs($rateinfo['minimal_time']);
                } else {
                    $duration_new = (ceil($duration / abs($rateinfo['resolution_time'])) * abs($rateinfo['resolution_time']));
                }
            }
        }
        return abs($duration_new);
    }

    function BillCost($duration, $rateinfo) {
        $cost = 0;
        if ($duration > 0) {
            $cost = ((((abs($rateinfo['rate']) / 60 ) * abs($duration))) * abs($rateinfo['rate_multiplier'])) + abs($rateinfo['rate_addition']) + abs($rateinfo['connection_charge']);
        }
        if ($rateinfo['round_logic'] == 'ROUND') {
            $this->writelog("ROUND");
            return round($cost, $rateinfo['dp']);
        } else {
            $this->writelog("CEIL");
            return $this->dp($cost, $rateinfo['dp']);
        }
    }

    function dp($number, $dp) {
        return abs(number_format(ceil($number * pow(10, $dp)) / pow(10, $dp), $dp, '.', ''));
    }

    function exclusive_tax($tax, $carrier_cost, $taxon = 100) {
        $tax_amount = 0;
        if ($tax > 0 and $carrier_cost > 0)
            $tax_amount = (($carrier_cost * $tax) / $taxon);
        return $tax_amount;
    }

    function inclusive_tax($tax, $carrier_cost, $taxon = 100) {
        $tax_amount = 0;
        if ($tax > 0 and $carrier_cost > 0)
            $tax_amount = ($carrier_cost / ($taxon + $tax)) * $tax;
        return $tax_amount;
    }

    function cdrinsert() {
        if ($this->leg == 'A') {
            // Leg-a
            if ($this->cdr_variable['sip_invite_failure_status'] == '404') {
                $this->hangupby = 'callee';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_bye') {
                //FS received BYE from the caller (they hung up)
                $this->hangupby = 'caller';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_refuse') {
                //FS rejected the call (e.g. 4xx or 5xx)
                $this->hangupby = 'system';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_bye') {
                //FS sent BYE to the call
                //er (we hung up)
                $this->hangupby = 'callee';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_cancel') {
                //n/a
                $this->hangupby = 'caller';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_refuse') {
                //Endpoint rejected the call (e.g. 4xx or 5xx)
                $this->hangupby = 'callee';
                // $this->cdr_variable['sip_term_status'] = '403';
            }
        } else if ($this->leg == 'B') {
            // Leg-b
            if ($this->cdr_variable['sip_invite_failure_status'] == '404') {
                $this->hangupby = 'callee';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_bye') {
                //FS received BYE from the endpoint (they hung up)
                $this->hangupby = 'callee';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_refuse') {
                //Endpoint rejected the call (e.g. 4xx or 5xx)
                $this->hangupby = 'system';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_refuse') {
                //Endpoint rejected the call (e.g. 4xx or 5xx)
                $this->hangupby = 'callee';
                //$this->cdr_variable['sip_term_status'] = '503';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_bye') {
                //FS sent BYE to the endpoint (we hung up)
                $this->hangupby = 'caller';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_cancel') {
                //FS aborted the call (we sent CANCEL)
                $this->hangupby = 'caller';
            }
        }

        if ($this->cdr_variable['sip_term_status'] == 515) {
            $this->hangupby = 'System';
            $this->cdr_variable['fs_errorcode'] = 'CARRIERCPSISSUE';
        }
        if ($this->cdr_variable['billsec'] > 0 and $this->cdr_variable['sip_term_status'] == 515) {
            $this->cdr_variable['sip_term_status'] = '200';
            $this->cdr_variable['fs_errorcode'] = '';
            $this->hangupby = 'callee';
        }


        if ($this->cdr_variable['billsec'] > 0) {
            $this->cdr_variable['sip_term_status'] = '200';
            $this->cdr_variable['sip_invite_failure_status'] = '200';
        }


        $gateways = $this->cdr_variable['gatewaydata'];
        $users = $this->cdr_variable['userdata'];
        $gatewaydataincoming = $this->cdr_variable['gatewaydataincoming'];
        $userdataincoming = $this->cdr_variable['userdataincoming'];

        $this->switch_bill_cdrs_incoming = '0';
        $this->switch_bill_cdrs = '0';
        $this->switch_carrier_statistics_incoming = '0';
        $this->switch_carrier_statistics = '0';
        $this->switch_calls_statistics_incoming = '0';
        $this->switch_calls_statistics = '0';
        $this->switch_cdrs_incoming = '0';
        $this->switch_cdrs = '0';
        $this->switch_balance = '0';
        if ($this->leg == 'A' and $this->calltype != 'INCOMING') {
            $this->writelog("######### OutBound A leg #########");
            if ($this->switch_issue == '1') {
                $this->switch_calls_statistics = '1';
                $this->switch_cdrs = '1';
            } elseif ($this->cdr_variable['billsec'] > 0) {
                $this->switch_bill_cdrs = '1';
                $this->switch_carrier_statistics = '1';
                $this->switch_calls_statistics = '1';
                $this->switch_balance = '1';
            } elseif ($this->cdr_variable['billsec'] < 1) {
                $this->switch_calls_statistics = '1';
            }
            if ((strlen($gateways) > 10 and strlen($users) > 10) or ( $this->switch_issue == '1')) {
                $carrierdata = json_decode(str_replace("'", '"', $gateways));
                $userdata = json_decode(str_replace("'", '"', $users));
                $data = $this->cdr_data_process($carrierdata, $userdata);
                $this->cdr_process($data);
            } else if (( strlen($users) > 10) or ( $this->cdr_variable['INTERNALCALL'] == '1')) {
                $userdata = json_decode(str_replace("'", '"', $users));
                $carrierdata = Array();
                $data = $this->cdr_data_process($carrierdata, $userdata);
                $this->cdr_process_internal_call($data);
            }
        }
        if ($this->leg == 'B' and $this->calltype != 'INCOMING') {
            $this->writelog("######### OutBound B leg #########");
            if ($this->cdr_variable['billsec'] < 1) {
                $this->switch_cdrs = '1';
                $this->switch_carrier_statistics = '1';
            }
            if (strlen($gateways) > 10 and strlen($users) > 10) {
                $carrierdata = json_decode(str_replace("'", '"', $gateways));
                $userdata = json_decode(str_replace("'", '"', $users));
                $data = $this->cdr_data_process($carrierdata, $userdata);
                $this->cdr_process($data);
            }
        }
        if ($this->leg == 'A' and $this->calltype == 'INCOMING') {
            $this->writelog("######### InBound A leg #########");
            if ($this->switch_issue == '1') {
                $this->switch_carrier_statistics_incoming = '1';
                $this->switch_cdrs_incoming = '1';
            } elseif ($this->cdr_variable['billsec'] > 0) {
                $this->switch_bill_cdrs_incoming = '1';
                $this->switch_carrier_statistics_incoming = '1';
                $this->switch_calls_statistics_incoming = '1';
                $this->switch_balance = '1';
            } elseif ($this->cdr_variable['billsec'] < 1) {
                $this->switch_cdrs_incoming = '1';
                $this->switch_carrier_statistics_incoming = '1';
                $this->switch_calls_statistics_incoming = '1';
            }
            if ((strlen($gatewaydataincoming) > 10 and strlen($userdataincoming) > 10) or ( $this->switch_issue == '1')) {
                $gatewaydataincoming = json_decode(str_replace("'", '"', $gatewaydataincoming));
                $userdataincoming = json_decode(str_replace("'", '"', $userdataincoming));
                $data = $this->cdr_data_process($gatewaydataincoming, $userdataincoming);
                $this->cdr_process($data);
            }


            $this->switch_bill_cdrs_incoming = '0';
            $this->switch_bill_cdrs = '0';
            $this->switch_carrier_statistics_incoming = '0';
            $this->switch_carrier_statistics = '0';
            $this->switch_calls_statistics_incoming = '0';
            $this->switch_calls_statistics = '0';
            $this->switch_cdrs_incoming = '0';
            $this->switch_cdrs = '0';
            $this->switch_balance = '0';
            if ($this->switch_issue == '1' and strlen($users) > 10) {
                $this->switch_cdrs = '1';
                $this->switch_calls_statistics = '1';
            }


            if (strlen($users) > 10 and $this->switch_cdrs == '1' and $this->switch_calls_statistics == '1' and $this->switch_issue == '1') {
                $carrierdata = json_decode(str_replace("'", '"', $gateways));
                $userdata = json_decode(str_replace("'", '"', $users));
                $data = $this->cdr_data_process($carrierdata, $userdata);
                $this->cdr_process($data);
            }
        }

        if ($this->leg == 'B' and $this->calltype == 'INCOMING') {
            $this->writelog("######### Inbound To PSTN #########");
            if ($this->cdr_variable['INCOMING2PSTN'] == '1') {
                if ($this->switch_issue == '1') {
                    $this->switch_cdrs = '1';
                    $this->switch_calls_statistics = '1';
                } else if ($this->cdr_variable['billsec'] > 0) {
                    $this->switch_bill_cdrs = '1';
                    $this->switch_calls_statistics = '1';
                    $this->switch_carrier_statistics = '1';
                    $this->switch_balance = '1';
                } else if ($this->cdr_variable['billsec'] < 1) {
                    $this->switch_cdrs = '1';
                    $this->switch_calls_statistics = '1';
                    $this->switch_carrier_statistics = '1';
                }
                if (strlen($gateways) > 10 and strlen($users) > 10) {
                    $carrierdata = json_decode(str_replace("'", '"', $gateways));
                    $userdata = json_decode(str_replace("'", '"', $users));
                    $data = $this->cdr_data_process($carrierdata, $userdata);
                    $this->cdr_process($data);
                } elseif ($this->switch_issue == '1') {
                    $carrierdata = json_decode(str_replace("'", '"', $gateways));
                    $userdata = json_decode(str_replace("'", '"', $users));
                    $data = $this->cdr_data_process($carrierdata, $userdata);
                    $this->cdr_process($data);
                }
            }
        }
        return;
    }

    function cdr_data_process($carrierdata, $userdata) {
        $this->carrierdata = Array();
        foreach ($carrierdata as $key => $value) {
            $this->carrierdata[$key] = $value;
            $this->writelog("Gateway: " . $key . ": " . $value);
            if ($key == 'ratecard_id'
                    or $key == 'tariff_id'
                    or $key == 'prefix'
                    or $key == 'destination'
                    or $key == 'rate'
                    or $key == 'connection_charge'
                    or $key == 'minimal_time'
                    or $key == 'resolution_time'
                    or $key == 'grace_period'
                    or $key == 'rate_multiplier'
                    or $key == 'rate_addition'
                    or $key == 'carrier_id'
                    or $key == 'carrier_name'
                    or $key == 'ipaddress'
                    or $key == 'ipaddress_name'
                    or $key == 'carrier_currency_id'
                    or $key == 'src_caller'
                    or $key == 'src_callee'
                    or $key == 'dst_caller'
                    or $key == 'dst_callee'
                    or $key == 'tax1'
                    or $key == 'tax2'
                    or $key == 'tax3'
                    or $key == 'dp'
                    or $key == 'tax_type'
                    or $key == 'dialplan_id'
            ) {

                if ($key == 'ipaddress') {
                    $data = $data . "carrier_$key = '" . $value . "',";
                } elseif ($key == 'ipaddress_name') {
                    $data = $data . "carrier_$key = '" . $value . "',";
                } elseif ($key == 'carrier_id') {
                    $data = $data . "$key = '" . $value . "',";
                } elseif ($key == 'carrier_currency_id') {
                    $data = $data . "$key = '" . $value . "',";
                } elseif ($key == 'carrier_name') {
                    $data = $data . "$key = '" . $value . "',";
                } else {
                    $data = $data . "carrier_$key = '" . trim($value) . "',";
                }
            }
        }

        $this->userdata = Array();
        foreach ($userdata as $userkey => $uservalue) {
            if ($userkey == 'user') {

                foreach ($uservalue as $key => $value) {
                    $this->writelog("User: " . $key . ": " . $value);
                    $this->userdata[$key] = $value;
                    if ($key == 'account_id'
                            or $key == 'dp'
                            or $key == 'tariff_id'
                            or $key == 'tax1'
                            or $key == 'tax2'
                            or $key == 'tax3'
                            or $key == 'tax_type'
                            or $key == 'currency_id'
                            or $key == 'ipaddress'
                            or $key == 'ratecard_id'
                            or $key == 'prefix'
                            or $key == 'destination'
                            or $key == 'rate'
                            or $key == 'connection_charge'
                            or $key == 'minimal_time'
                            or $key == 'resolution_time'
                            or $key == 'grace_period'
                            or $key == 'rate_multiplier'
                            or $key == 'rate_addition'
                            or $key == 'src_caller'
                            or $key == 'src_callee'
                            or $key == 'src_ip'
                    ) {
                        $data = $data . "customer_$key = '" . addslashes($value) . "',";
                    }
                }
            }

            $this->reseller1_data = Array();
            if ($userkey == 'Reseller1') {
                foreach ($uservalue as $key => $value) {
                    $this->reseller1_data[$key] = $value;
                    $this->writelog("Reseller1: " . $key . ": " . $value);
                    if ($key == 'account_id'
                            or $key == 'tariff_id'
                            or $key == 'dp'
                            or $key == 'tax1'
                            or $key == 'tax2'
                            or $key == 'tax3'
                            or $key == 'tax_type'
                            or $key == 'ratecard_id'
                            or $key == 'prefix'
                            or $key == 'destination'
                            or $key == 'rate'
                            or $key == 'connection_charge'
                            or $key == 'minimal_time'
                            or $key == 'resolution_time'
                            or $key == 'grace_period'
                            or $key == 'rate_multiplier'
                            or $key == 'rate_addition'
                    ) {
                        $data = $data . "reseller1_$key = '" . addslashes($value) . "',";
                    }
                }
            }
            $this->reseller2_data = Array();
            if ($userkey == 'Reseller2') {
                //  $this->reseller2_data = $uservalue;
                foreach ($uservalue as $key => $value) {
                    $this->reseller2_data[$key] = $value;
                    $this->writelog("Reseller2: " . $key . ": " . $value);
                    if ($key == 'account_id'
                            or $key == 'tariff_id'
                            or $key == 'dp'
                            or $key == 'tax1'
                            or $key == 'tax2'
                            or $key == 'tax3'
                            or $key == 'tax_type'
                            or $key == 'ratecard_id'
                            or $key == 'prefix'
                            or $key == 'destination'
                            or $key == 'rate'
                            or $key == 'connection_charge'
                            or $key == 'minimal_time'
                            or $key == 'resolution_time'
                            or $key == 'grace_period'
                            or $key == 'rate_multiplier'
                            or $key == 'rate_addition'
                    ) {
                        $data = $data . "reseller2_$key = '" . addslashes($value) . "',";
                    }
                }
            }
            $this->reseller3_data = Array();
            if ($userkey == 'Reseller3') {
                //$this->reseller3_data = $uservalue;
                foreach ($uservalue as $key => $value) {
                    $this->reseller3_data[$key] = $value;
                    $this->writelog("Reseller3: " . $key . ": " . $value);
                    if ($key == 'account_id'
                            or $key == 'tariff_id'
                            or $key == 'dp'
                            or $key == 'tax1'
                            or $key == 'tax2'
                            or $key == 'tax3'
                            or $key == 'tax_type'
                            or $key == 'ratecard_id'
                            or $key == 'prefix'
                            or $key == 'destination'
                            or $key == 'rate'
                            or $key == 'connection_charge'
                            or $key == 'minimal_time'
                            or $key == 'resolution_time'
                            or $key == 'grace_period'
                            or $key == 'rate_multiplier'
                            or $key == 'rate_addition'
                    ) {
                        $data = $data . "reseller3_$key = '" . addslashes($value) . "',";
                    }
                }
            }
        }
        return $data;
    }

    function cdr_process_internal_call($data) {
        $query = sprintf("delete from livecalls where common_uuid = '%s'", addslashes($this->cdr_variable['common_uuid']));
        $this->query('SWITCH', $query);
        $this->execute();
        $this->writelog($query);

        $dbdate = date('Ym');
        if ($this->cdr_variable['billsec'] > 0) {
            $data = $data . "uuid = '" . addslashes($this->cdr_variable['uuid']) . "',";
            $data = $data . "billsec = '" . addslashes($this->cdr_variable['billsec']) . "',";
            $data = $data . "pdd = '" . addslashes($pdd) . "',";
            $data = $data . "fscause = '" . addslashes($fscause) . "',";
            $data = $data . "Q850CODE = '" . addslashes($causeQ850) . "',";
            $data = $data . "SIPCODE = '" . addslashes($causeSIP) . "',";
            $data = $data . "common_uuid = '" . addslashes($this->cdr_variable['common_uuid']) . "',";
            $data = $data . "caller_callid = '" . addslashes($this->cdr_variable['caller_callid']) . "',";
            $data = $data . "callee_callid = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
            $data = $data . "start_time= '" . addslashes($this->cdr_variable['start_stamp']) . "',";
            $data = $data . "answer_time= '" . addslashes($this->cdr_variable['answer_stamp']) . "',";
            $data = $data . "end_time= '" . addslashes($this->cdr_variable['end_stamp']) . "',";
            $data = $data . "hangupby= '" . addslashes($this->hangupby) . "',";
            $data = $data .= "customer_company_name = '" . $this->userdata['company_name'] . "',";
            $data = $data .= "customer_incodecs = '" . $this->cdr_variable['USERCODECSCLIST'] . "',";
            $data = $data .= "carrier_outcodecs = '" . $this->carrierdata['carrier_codecs'] . "',";
            $data = $data .= "call_codecs = '" . $this->cdr_variable['write_codec'] . "',";
            $data = $data . "carrier_duration = '" . addslashes($this->cdr_variable['billsec']) . "',";
            $data = $data . "customer_duration = '" . addslashes($this->cdr_variable['billsec']) . "',";

            $query = sprintf("delete from livecalls where common_uuid = '%s'", addslashes($this->cdr_variable['common_uuid']));
            $this->query('SWITCH', $query);
            $this->execute();
            $this->writelog($query);
            $data = $data .= "cdr_type = 'EXTEN',";
            $cdrdata = rtrim($data, ',');
            $query = "insert into " . $dbdate . "_ratedcdr  set " . $cdrdata;
            $this->writelog($query);
            $this->query('CDR', $query);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $query);
                $this->execute();
            }
        } else {
            $RAWCDRQUERY .= "fs_errorcode = '" . addslashes($this->cdr_variable['fs_errorcode']) . "',";
            $RAWCDRQUERY .= "carrier_ratecard_id = '" . addslashes($this->carrierdata['ratecard_id']) . "',";
            $RAWCDRQUERY .= "carrier_tariff_id = '" . addslashes($this->carrierdata['tariff_id']) . "',";
            $RAWCDRQUERY .= "carrier_prefix = '" . addslashes($this->carrierdata['prefix']) . "',";
            $RAWCDRQUERY .= "carrier_destination = '" . addslashes($this->carrierdata['destination']) . "',";
            $RAWCDRQUERY .= "carrier_ipaddress = '" . addslashes($this->carrierdata['ipaddress']) . "',";
            $RAWCDRQUERY .= "carrier_ipaddress_name = '" . addslashes($this->carrierdata['ipaddress_name']) . "',";
            $RAWCDRQUERY .= "carrier_src_caller = '" . addslashes($this->carrierdata['src_caller']) . "',";
            $RAWCDRQUERY .= "carrier_src_callee = '" . addslashes($this->carrierdata['src_callee']) . "',";
            $RAWCDRQUERY .= "carrier_dst_caller = '" . addslashes($this->carrierdata['dst_caller']) . "',";
            $RAWCDRQUERY .= "carrier_dst_callee = '" . addslashes($this->carrierdata['dst_callee']) . "',";
            $RAWCDRQUERY .= "customer_tariff_id = '" . addslashes($this->userdata['tariff_id']) . "',";
            $RAWCDRQUERY .= "customer_ipaddress = '" . addslashes($this->userdata['ipaddress']) . "',";
            $RAWCDRQUERY .= "customer_ratecard_id = '" . addslashes($this->userdata['ratecard_id']) . "',";
            $RAWCDRQUERY .= "customer_prefix = '" . addslashes($this->userdata['prefix']) . "',";
            $RAWCDRQUERY .= "customer_destination = '" . addslashes($this->userdata['destination']) . "',";
            $RAWCDRQUERY .= "customer_src_caller = '" . addslashes($this->userdata['src_caller']) . "',";
            $RAWCDRQUERY .= "customer_src_callee = '" . addslashes($this->userdata['src_callee']) . "',";
            $RAWCDRQUERY .= "customer_src_ip = '" . addslashes($this->userdata['src_ip']) . "',";
            $RAWCDRQUERY .= "customer_account_id = '" . addslashes($this->userdata['account_id']) . "',";
            $RAWCDRQUERY .= "reseller1_account_id = '" . addslashes($this->reseller1_data['account_id']) . "',";
            $RAWCDRQUERY .= "reseller1_tariff_id = '" . addslashes($this->reseller1_data['tariff_id']) . "',";
            $RAWCDRQUERY .= "reseller1_ratecard_id = '" . addslashes($this->reseller1_data['ratecard_id']) . "',";
            $RAWCDRQUERY .= "reseller1_prefix = '" . addslashes($this->reseller1_data['prefix']) . "',";
            $RAWCDRQUERY .= "reseller1_destination = '" . addslashes($this->reseller1_data['destination']) . "',";
            $RAWCDRQUERY .= "reseller2_destination = '" . addslashes($this->reseller2_data['destination']) . "',";
            $RAWCDRQUERY .= "reseller2_account_id = '" . addslashes($this->reseller2_data['account_id']) . "',";
            $RAWCDRQUERY .= "reseller2_tariff_id = '" . addslashes($this->reseller2_data['tariff_id']) . "',";
            $RAWCDRQUERY .= "reseller2_ratecard_id = '" . addslashes($this->reseller2_data['ratecard_id']) . "',";
            $RAWCDRQUERY .= "reseller2_prefix = '" . addslashes($this->reseller2_data['prefix']) . "',";
            $RAWCDRQUERY .= "reseller3_account_id = '" . addslashes($this->reseller3_data['account_id']) . "',";
            $RAWCDRQUERY .= "reseller3_tariff_id = '" . addslashes($this->reseller3_data['tariff_id']) . "',";
            $RAWCDRQUERY .= "reseller3_ratecard_id = '" . addslashes($this->reseller3_data['ratecard_id']) . "',";
            $RAWCDRQUERY .= "reseller3_prefix = '" . addslashes($this->reseller3_data['prefix']) . "',";
            $RAWCDRQUERY .= "reseller3_destination = '" . addslashes($this->reseller3_data['destination']) . "',";
            $RAWCDRQUERY .= "uuid = '" . addslashes($this->cdr_variable['uuid']) . "',";
            $RAWCDRQUERY .= "sip_from_user  = '" . addslashes($this->cdr_variable['sip_from_user']) . "',";
            $RAWCDRQUERY .= "sip_from_uri  = '" . addslashes($this->cdr_variable['sip_from_uri']) . "',";
            $RAWCDRQUERY .= "sip_from_host = '" . addslashes($this->cdr_variable['sip_from_host']) . "',";
            $RAWCDRQUERY .= "channel_name = '" . addslashes($this->cdr_variable['channel_name']) . "',";
            $RAWCDRQUERY .= "common_uuid = '" . addslashes($this->cdr_variable['common_uuid']) . "',";
            $RAWCDRQUERY .= "caller_callid = '" . addslashes($this->cdr_variable['caller_callid']) . "',";
            $RAWCDRQUERY .= "callee_callid = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
            $RAWCDRQUERY .= "sip_call_id = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
            $RAWCDRQUERY .= "ep_codec_string  = '" . addslashes($this->cdr_variable['ep_codec_string']) . "',";
            $RAWCDRQUERY .= "sip_network_ip = '" . addslashes($this->cdr_variable['sip_network_ip']) . "',";
            $RAWCDRQUERY .= "sip_received_ip = '" . addslashes($this->cdr_variable['sip_received_ip']) . "',";
            $RAWCDRQUERY .= "sip_received_port = '" . addslashes($this->cdr_variable['sip_received_port']) . "',";
            $RAWCDRQUERY .= "sip_via_protocol = '" . addslashes($this->cdr_variable['sip_via_protocol']) . "',";
            $RAWCDRQUERY .= "sip_from_user_stripped = '" . addslashes($this->cdr_variable['sip_from_user_stripped']) . "',";
            $RAWCDRQUERY .= "sip_from_display = '" . addslashes($this->cdr_variable['sip_from_display']) . "',";
            $RAWCDRQUERY .= "sip_full_from = '" . addslashes($this->cdr_variable['sip_full_from']) . "',";
            $RAWCDRQUERY .= "sip_to_display = '" . addslashes($this->cdr_variable['sip_to_display']) . "',";
            $RAWCDRQUERY .= "sip_full_to = '" . addslashes($this->cdr_variable['sip_full_to']) . "',";
            $RAWCDRQUERY .= "sip_req_user = '" . addslashes($this->cdr_variable['sip_req_user']) . "',";
            $RAWCDRQUERY .= "sip_req_uri = '" . addslashes($this->cdr_variable['sip_req_uri']) . "',";
            $RAWCDRQUERY .= "sip_to_user = '" . addslashes($this->cdr_variable['sip_to_user']) . "',";
            $RAWCDRQUERY .= "sip_contact_user = '" . addslashes($this->cdr_variable['sip_contact_user']) . "',";
            $RAWCDRQUERY .= "sip_contact_port = '" . addslashes($this->cdr_variable['sip_contact_port']) . "',";
            $RAWCDRQUERY .= "sip_contact_uri = '" . addslashes($this->cdr_variable['sip_contact_uri']) . "',";
            $RAWCDRQUERY .= "sip_contact_host = '" . addslashes($this->cdr_variable['sip_contact_host']) . "',";
            $RAWCDRQUERY .= "rtp_use_codec_string = '" . addslashes($this->cdr_variable['rtp_use_codec_string']) . "',";
            $RAWCDRQUERY .= "sip_user_agent = '" . addslashes($this->cdr_variable['sip_user_agent']) . "',";
            $RAWCDRQUERY .= "sip_via_host = '" . addslashes($this->cdr_variable['sip_via_host']) . "',";
            $RAWCDRQUERY .= "sip_via_port = '" . addslashes($this->cdr_variable['sip_via_port']) . "',";
            $RAWCDRQUERY .= "sip_via_rport = '" . addslashes($this->cdr_variable['sip_via_rport']) . "',";
            $RAWCDRQUERY .= "switch_r_sdp = '" . addslashes($this->cdr_variable['switch_r_sdp']) . "',";
            $RAWCDRQUERY .= "endpoint_disposition = '" . addslashes($this->cdr_variable['endpoint_disposition']) . "',";
            $RAWCDRQUERY .= "effective_caller_id_name = '" . addslashes($this->cdr_variable['effective_caller_id_name']) . "',";
            $RAWCDRQUERY .= "effective_caller_id_number = '" . addslashes($this->cdr_variable['effective_caller_id_number']) . "',";
            $RAWCDRQUERY .= "digits_dialed = '" . addslashes($this->cdr_variable['digits_dialed']) . "',";
            $RAWCDRQUERY .= "start_stamp = '" . addslashes($this->cdr_variable['start_stamp']) . "',";
            $RAWCDRQUERY .= "profile_start_stamp = '" . addslashes($this->cdr_variable['profile_start_stamp']) . "',";
            $RAWCDRQUERY .= "end_stamp = '" . addslashes($this->cdr_variable['end_stamp']) . "',";
            $RAWCDRQUERY .= "caller_id = '" . addslashes($this->cdr_variable['caller_id']) . "',";
            $RAWCDRQUERY .= "duration = '" . addslashes($this->cdr_variable['duration']) . "',";
            $RAWCDRQUERY .= "billsec = '" . addslashes($this->cdr_variable['billsec']) . "',";
            $RAWCDRQUERY .= "progresssec = '" . addslashes($this->cdr_variable['progresssec']) . "',";
            $RAWCDRQUERY .= "answersec = '" . addslashes($this->cdr_variable['answersec']) . "',";
            $RAWCDRQUERY .= "waitsec = '" . addslashes($this->cdr_variable['waitsec']) . "',";
            $RAWCDRQUERY .= "flow_billsec = '" . addslashes($this->cdr_variable['flow_billsec']) . "',";
            $RAWCDRQUERY .= "sip_hangup_disposition = '" . addslashes($this->cdr_variable['sip_hangup_disposition']) . "',";
            $RAWCDRQUERY .= "in_useragent= '" . addslashes($this->cdr_variable['in_useragent']) . "',";
            $RAWCDRQUERY .= "out_useragent= '" . addslashes($this->cdr_variable['out_useragent']) . "',";
            $RAWCDRQUERY .= "in_rtp_audio_in_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_in_media_bytes']) . "',";
            $RAWCDRQUERY .= "in_rtp_audio_out_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_out_media_bytes']) . "',";
            $RAWCDRQUERY .= "out_rtp_audio_in_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_in_media_bytes']) . "',";
            $RAWCDRQUERY .= "out_rtp_audio_out_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_out_media_bytes']) . "',";
            $RAWCDRQUERY .= "rtp_audio_in_media_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_media_packet_count']) . "',	";
            $RAWCDRQUERY .= "rtp_audio_out_media_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_out_media_packet_count']) . "',";
            $RAWCDRQUERY .= "in_rtp_audio_in_jitter_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']) . "',";
            $RAWCDRQUERY .= "out_rtp_audio_in_jitter_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']) . "',";
            $RAWCDRQUERY .= "in_rtp_audio_in_skip_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']) . "',	";
            $RAWCDRQUERY .= "out_rtp_audio_in_skip_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']) . "',	";
            $RAWCDRQUERY .= "in_rtp_audio_in_jitter_min_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']) . "',";
            $RAWCDRQUERY .= "in_rtp_audio_in_jitter_max_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']) . "',";
            $RAWCDRQUERY .= "out_rtp_audio_in_jitter_min_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']) . "',";
            $RAWCDRQUERY .= "out_rtp_audio_in_jitter_max_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']) . "',";
            $RAWCDRQUERY .= "in_rtp_audio_in_mos= '" . addslashes($this->cdr_variable['rtp_audio_in_mos']) . "',";
            $RAWCDRQUERY .= "out_rtp_audio_in_mos= '" . addslashes($this->cdr_variable['rtp_audio_in_mos']) . "', ";
            $RAWCDRQUERY .= "fscause = '" . addslashes($fscause) . "',";
            $RAWCDRQUERY .= "Q850CODE = '" . addslashes($causeQ850) . "',";
            $RAWCDRQUERY .= "SIPCODE = '" . addslashes($causeSIP) . "',";
            $RAWCDRQUERY .= "carrier_dialplan_id = '" . addslashes($this->carrierdata['dialplan_id']) . "',";
            $RAWCDRQUERY .= "carrier_id = '" . addslashes($this->carrierdata['carrier_id']) . "',";
            $RAWCDRQUERY .= "hangupby = '" . addslashes($this->hangupby) . "',";
            $RAWCDRQUERY .= "customer_incodecs = '" . $this->cdr_variable['USERCODECSCLIST'] . "',";
            $RAWCDRQUERY .= "carrier_outcodecs = '" . $this->carrierdata['carrier_codecs'] . "',";
            $RAWCDRQUERY .= "call_codecs = '" . $this->cdr_variable['write_codec'] . "',";
            $RAWCDRQUERY .= "customer_company_name = '" . $this->userdata['company_name'] . "',";
            $RAWCDRQUERY .= "carrier_carrier_name = '" . $this->carrierdata['carrier_name'] . "',";


            $RAWCDRQUERY .= "cdr_type = 'EXTEN',";
            $RAWCDRQUERY = rtrim($RAWCDRQUERY, ',');
            $RAWCDRQUERY_IN = 'insert into ' . $dbdate . "_cdr set " . $RAWCDRQUERY;
            $this->writelog($RAWCDRQUERY_IN);
            $this->query('CDR', $RAWCDRQUERY_IN);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $RAWCDRQUERY_IN);
                $this->execute();
            }
        }
    }

    function cdr_process($data) {
        $dbdate = date('Ym');
        if ($this->carrierdata['dp'] == '' or $this->carrierdata['dp'] == null)
            $this->carrierdata['dp'] = 4;
        $data = $data . "uuid = '" . addslashes($this->cdr_variable['uuid']) . "',";
        $data = $data . "billsec = '" . addslashes($this->cdr_variable['billsec']) . "',";
        $rate = $this->userdata['rate'];
        $carrier_rate = $this->carrierdata['rate'];
        $r1_prefix = $this->reseller1_data['prefix'];
        $r1_rate = $this->reseller1_data['rate'];
        $r2_prefix = $this->reseller2_data['prefix'];
        $r2_rate = $this->reseller2_data['rate'];
        $r3_prefix = $this->reseller3_data['prefix'];
        $r3_rate = $this->reseller3_data['rate'];
        if ($this->switch_issue == '0') {
            $carrier_duration = $this->BillDuration($this->cdr_variable['billsec'], $this->carrierdata);
            $customer_duration = $this->BillDuration($this->cdr_variable['billsec'], $this->userdata);
            $reseller1_duartion = $this->BillDuration($this->cdr_variable['billsec'], $this->reseller1_data);
            $reseller2_duartion = $this->BillDuration($this->cdr_variable['billsec'], $this->reseller2_data);
            $reseller3_duartion = $this->BillDuration($this->cdr_variable['billsec'], $this->reseller3_data);
            $carrier_cost = $this->BillCost($carrier_duration, $this->carrierdata);
            $customer_cost = $this->BillCost($customer_duration, $this->userdata);
            $reseller1_cost = $this->BillCost($reseller1_duartion, $this->reseller1_data);
            $reseller2_cost = $this->BillCost($reseller2_duartion, $this->reseller2_data);
            $reseller3_cost = $this->BillCost($reseller3_duartion, $this->reseller3_data);
            /*
             * TAX CALCULATIONS
             */
            $total_tax = 0;
            $tax = 0;
            if ($this->carrierdata['dp'] == 0 or $this->carrierdata['dp'] == '' or $this->carrierdata['dp'] == null)
                $this->carrierdata['dp'] = 6;
            if ($this->carrierdata['tax_type'] == 'exclusive') {
                $tax = $this->carrierdata['tax1'] + $this->carrierdata['tax2'] + $this->carrierdata['tax3'];
                $total_tax = $this->exclusive_tax($tax, $carrier_cost, 100);
                $total_tax = $this->dp($total_tax, $this->carrierdata['dp']);
                $carrier_tax1_cost = $this->exclusive_tax($this->carrierdata['tax1'], $total_tax, $tax);
                $carrier_tax1_cost = $this->dp($carrier_tax1_cost, $this->carrierdata['dp']);
                $carrier_tax2_cost = $this->exclusive_tax($this->carrierdata['tax2'], $total_tax, $tax);
                $carrier_tax2_cost = $this->dp($carrier_tax2_cost, $this->carrierdata['dp']);
                $carrier_tax3_cost = $this->exclusive_tax($this->carrierdata['tax3'], $total_tax, $tax);
                $carrier_tax3_cost = $this->dp($carrier_tax3_cost, $this->carrierdata['dp']);
                $carrier_callcost_total = $carrier_tax1_cost + $carrier_tax2_cost + $carrier_tax3_cost + $carrier_cost;

                $carrier_callcost_total = $this->dp($carrier_callcost_total, $this->carrierdata['dp']);
            } else if ($this->carrierdata['tax_type'] == 'inclusive') {
                $tax = $this->carrierdata['tax1'] + $this->carrierdata['tax2'] + $this->carrierdata['tax3'];
                $total_tax = $this->inclusive_tax($tax, $carrier_cost, 100);
                $total_tax = $this->dp($total_tax, $this->carrierdata['dp']);
                $carrier_tax1_cost = $this->exclusive_tax($this->carrierdata['tax1'], $total_tax, $tax);
                $carrier_tax1_cost = $this->dp($carrier_tax1_cost, $this->carrierdata['dp']);
                $carrier_tax2_cost = $this->exclusive_tax($this->carrierdata['tax2'], $total_tax, $tax);
                $carrier_tax2_cost = $this->dp($carrier_tax2_cost, $this->carrierdata['dp']);
                $carrier_tax3_cost = $this->exclusive_tax($this->carrierdata['tax3'], $total_tax, $tax);
                $carrier_tax3_cost = $this->dp($carrier_tax3_cost, $this->carrierdata['dp']);
                $carrier_callcost_total = $carrier_cost;
                $carrier_callcost_total = $this->dp($carrier_callcost_total, $this->carrierdata['dp']);
                $carrier_cost = $carrier_callcost_total - $carrier_tax1_cost - $carrier_tax2_cost - $carrier_tax3_cost;

                $carrier_cost = $this->dp($carrier_cost, $this->carrierdata['dp']);
            }
            $total_tax = 0;
            $tax = 0;
            if ($this->userdata['tax_type'] == 'exclusive') {
                $tax = $this->userdata['tax1'] + $this->userdata['tax2'] + $this->userdata['tax3'];
                $total_tax = $this->exclusive_tax($tax, $customer_cost, 100);
                $total_tax = $this->dp($total_tax, $this->userdata['dp']);
                $customer_tax1_cost = $this->exclusive_tax($this->userdata['tax1'], $total_tax, $tax);
                $customer_tax1_cost = $this->dp($customer_tax1_cost, $this->userdata['dp']);
                $customer_tax2_cost = $this->exclusive_tax($this->userdata['tax2'], $total_tax, $tax);
                $customer_tax2_cost = $this->dp($customer_tax2_cost, $this->userdata['dp']);
                $customer_tax3_cost = $this->exclusive_tax($this->userdata['tax3'], $total_tax, $tax);
                $customer_tax3_cost = $this->dp($customer_tax3_cost, $this->userdata['dp']);
                $customer_callcost_total = $customer_tax1_cost + $customer_tax2_cost + $customer_tax3_cost + $customer_cost;

                $customer_callcost_total = $this->dp($customer_callcost_total, $this->userdata['dp']);
            } else if ($this->userdata['tax_type'] == 'inclusive') {
                $tax = $this->userdata['tax1'] + $this->userdata['tax2'] + $this->userdata['tax3'];
                $total_tax = $this->inclusive_tax($tax, $customer_cost, 100);
                $total_tax = $this->dp($total_tax, $this->userdata['dp']);
                $customer_tax1_cost = $this->exclusive_tax($this->userdata['tax1'], $total_tax, $tax);
                $customer_tax1_cost = $this->dp($customer_tax1_cost, $this->userdata['dp']);
                $customer_tax2_cost = $this->exclusive_tax($this->userdata['tax2'], $total_tax, $tax);
                $customer_tax2_cost = $this->dp($customer_tax2_cost, $this->userdata['dp']);
                $customer_tax3_cost = $this->exclusive_tax($this->userdata['tax3'], $total_tax, $tax);
                $customer_tax3_cost = $this->dp($customer_tax3_cost, $this->userdata['dp']);
                $customer_callcost_total = $customer_cost;
                $customer_callcost_total = $this->dp($customer_callcost_total, $this->userdata['dp']);
                $customer_cost = $customer_callcost_total - ($customer_tax1_cost + $customer_tax2_cost + $customer_tax3_cost);
                $customer_cost = $this->dp($customer_cost, $this->userdata['dp']);
            }
            $total_tax = 0;
            $tax = 0;

            if ($this->reseller1_data['tax_type'] == 'exclusive') {
                $tax = $this->reseller1_data['tax1'] + $this->reseller1_data['tax2'] + $this->reseller1_data['tax3'];
                $total_tax = $this->exclusive_tax($tax, $reseller1_cost, 100);
                $total_tax = $this->dp($total_tax, $this->reseller1_data['dp']);
                $reseller1_tax1_cost = $this->exclusive_tax($this->reseller1_data['tax1'], $total_tax, $tax);
                $reseller1_tax1_cost = $this->dp($reseller1_tax1_cost, $this->reseller1_data['dp']);
                $reseller1_tax2_cost = $this->exclusive_tax($this->reseller1_data['tax2'], $total_tax, $tax);
                $reseller1_tax2_cost = $this->dp($reseller1_tax2_cost, $this->reseller1_data['dp']);
                $reseller1_tax3_cost = $this->exclusive_tax($this->reseller1_data['tax3'], $total_tax, $tax);
                $reseller1_tax3_cost = $this->dp($reseller1_tax3_cost, $this->reseller1_data['dp']);
                $reseller1_callcost_total = $reseller1_tax1_cost + $reseller1_tax2_cost + $reseller1_tax3_cost + $reseller1_cost;
                $reseller1_callcost_total = $this->dp($reseller1_callcost_total, $this->reseller1_data['dp']);
            } else if ($this->reseller1_data['tax_type'] == 'inclusive') {
                $tax = $this->reseller1_data['tax1'] + $this->reseller1_data['tax2'] + $this->reseller1_data['tax3'];
                $total_tax = $this->inclusive_tax($tax, $reseller1_cost, 100);
                $total_tax = $this->dp($total_tax, $this->reseller1_data['dp']);
                $reseller1_tax1_cost = $this->exclusive_tax($this->reseller1_data['tax1'], $total_tax, $tax);
                $reseller1_tax1_cost = $this->dp($reseller1_tax1_cost, $this->reseller1_data['dp']);
                $reseller1_tax2_cost = $this->exclusive_tax($this->reseller1_data['tax2'], $total_tax, $tax);
                $reseller1_tax2_cost = $this->dp($reseller1_tax2_cost, $this->reseller1_data['dp']);
                $reseller1_tax3_cost = $this->exclusive_tax($this->reseller1_data['tax3'], $total_tax, $tax);
                $reseller1_tax3_cost = $this->dp($reseller1_tax3_cost, $this->reseller1_data['dp']);
                $reseller1_callcost_total = $reseller1_cost;
                $reseller1_callcost_total = $this->dp($reseller1_callcost_total, $this->reseller1_data['dp']);
                $reseller1_cost = $reseller1_callcost_total - $reseller1_tax1_cost - $reseller1_tax2_cost - $reseller1_tax3_cost;

                $reseller1_cost = $this->dp($reseller1_cost, $this->reseller1_data['dp']);
            }
            $total_tax = 0;
            $tax = 0;
            if ($this->reseller2_data['tax_type'] == 'exclusive') {
                $tax = $this->reseller2_data['tax1'] + $this->reseller2_data['tax2'] + $this->reseller2_data['tax3'];
                $total_tax = $this->exclusive_tax($tax, $reseller2_cost, 100);
                $total_tax = $this->dp($total_tax, $this->reseller2_data['dp']);
                $reseller2_tax1_cost = $this->exclusive_tax($this->reseller2_data['tax1'], $total_tax, $tax);
                $reseller2_tax1_cost = $this->dp($reseller2_tax1_cost, $this->reseller2_data['dp']);
                $reseller2_tax2_cost = $this->exclusive_tax($this->reseller2_data['tax2'], $total_tax, $tax);
                $reseller2_tax2_cost = $this->dp($reseller2_tax2_cost, $this->reseller2_data['dp']);
                $reseller2_tax3_cost = $this->exclusive_tax($this->reseller2_data['tax3'], $total_tax, $tax);
                $reseller2_tax3_cost = $this->dp($reseller2_tax3_cost, $this->reseller2_data['dp']);
                $reseller2_callcost_total = $reseller2_tax1_cost + $reseller2_tax2_cost + $reseller2_tax3_cost + $reseller2_cost;
                $reseller2_callcost_total = $this->dp($reseller2_callcost_total, $this->reseller2_data['dp']);
            } else if ($this->reseller2_data['tax_type'] == 'inclusive') {
                $tax = $this->reseller2_data['tax1'] + $this->reseller2_data['tax2'] + $this->reseller2_data['tax3'];
                $total_tax = $this->inclusive_tax($tax, $reseller2_cost, 100);
                $total_tax = $this->dp($total_tax, $this->reseller2_data['dp']);
                $reseller2_tax1_cost = $this->exclusive_tax($this->reseller2_data['tax1'], $total_tax, $tax);
                $reseller2_tax1_cost = $this->dp($reseller2_tax1_cost, $this->reseller2_data['dp']);
                $reseller2_tax2_cost = $this->exclusive_tax($this->reseller2_data['tax2'], $total_tax, $tax);
                $reseller2_tax2_cost = $this->dp($reseller2_tax2_cost, $this->reseller2_data['dp']);
                $reseller2_tax3_cost = $this->exclusive_tax($this->reseller2_data['tax3'], $total_tax, $tax);
                $reseller2_tax3_cost = $this->dp($reseller2_tax3_cost, $this->reseller2_data['dp']);
                $reseller2_callcost_total = $reseller2_cost;
                $reseller2_cost = $reseller2_callcost_total - $reseller2_tax1_cost - $reseller2_tax2_cost - $reseller2_tax3_cost;

                $reseller2_cost = $this->dp($reseller2_cost, $this->reseller2_data['dp']);
            }
            $total_tax = 0;
            $tax = 0;
            if ($this->reseller3_data['tax_type'] == 'exclusive') {
                $tax = $this->reseller3_data['tax1'] + $this->reseller3_data['tax2'] + $this->reseller3_data['tax3'];
                $total_tax = $this->exclusive_tax($tax, $reseller3_cost, 100);
                $total_tax = $this->dp($total_tax, $this->reseller3_data['dp']);

                $reseller3_tax1_cost = $this->exclusive_tax($this->reseller3_data['tax1'], $total_tax, $tax);
                $reseller3_tax1_cost = $this->dp($reseller3_tax1_cost, $this->reseller3_data['dp']);

                $reseller3_tax2_cost = $this->exclusive_tax($this->reseller3_data['tax2'], $total_tax, $tax);
                $reseller3_tax2_cost = $this->dp($reseller3_tax2_cost, $this->reseller3_data['dp']);

                $reseller3_tax3_cost = $this->exclusive_tax($this->reseller3_data['tax3'], $total_tax, $tax);
                $reseller3_tax3_cost = $this->dp($reseller3_tax3_cost, $this->reseller3_data['dp']);
                $reseller3_callcost_total = $reseller3_tax1_cost + $reseller3_tax2_cost + $reseller3_tax3_cost + $reseller3_cost;
                $reseller3_callcost_total = $this->dp($reseller3_callcost_total, $this->reseller3_data['dp']);
            } else if ($this->reseller3_data['tax_type'] == 'inclusive') {
                $tax = $this->reseller3_data['tax1'] + $this->reseller3_data['tax2'] + $this->reseller3_data['tax3'];
                $total_tax = $this->inclusive_tax($tax, $reseller3_cost, 100);
                $total_tax = $this->dp($total_tax, $this->reseller3_data['dp']);
                $reseller3_tax1_cost = $this->exclusive_tax($this->reseller3_data['tax1'], $total_tax, $tax);
                $reseller3_tax1_cost = $this->dp($reseller3_tax1_cost, $this->reseller3_data['dp']);
                $reseller3_tax2_cost = $this->exclusive_tax($this->reseller3_data['tax2'], $total_tax, $tax);
                $reseller3_tax2_cost = $this->dp($reseller3_tax2_cost, $this->reseller3_data['dp']);
                $reseller3_tax3_cost = $this->exclusive_tax($this->reseller3_data['tax3'], $total_tax, $tax);
                $reseller3_tax3_cost = $this->dp($reseller3_tax3_cost, $this->reseller3_data['dp']);
                $reseller3_callcost_total = $this->dp($reseller3_cost, $this->reseller3_data['dp']);
                $reseller3_cost = $reseller3_callcost_total - $reseller3_tax1_cost - $reseller3_tax2_cost - $reseller3_tax3_cost;

                $reseller3_cost = $this->dp($reseller3_cost, $this->reseller3_data['dp']);
            }
            /*
             * TAX CALCULATION SECTION CLOSE
             */
        }
        $carrier_cost_exclusive = $carrier_cost;

        $blockuser = $this->userdata['account_id'];
        $customer_callcost_total_usercurrency = $customer_callcost_total;
        if (count($this->reseller1_data['account_id']) > 0) {
            $customer_callcost_total_usercurrency = $reseller1_callcost_total;
            $blockuser = $this->reseller1_data['account_id'];
        }
        if (count($this->reseller2_data['account_id']) > 0) {
            $customer_callcost_total_usercurrency = $reseller2_callcost_total;
            $blockuser = $this->reseller2_data['account_id'];
        }
        if (count($this->reseller2_data['account_id']) > 0) {
            $customer_callcost_total_usercurrency = $reseller3_callcost_total;
            $blockuser = $this->reseller3_data['account_id'];
        }

        $profit_usercurrency = $customer_callcost_total_usercurrency - ($carrier_callcost_total * $this->carrierdata['ratio']);
        $carrier_callcost_inclusive_usercurrency = $carrier_callcost_total * $this->carrierdata['ratio'];
        $carrier_callcost_total_usercurrency = $carrier_cost_exclusive * $this->carrierdata['ratio'];
        $account_id = $this->userdata['account_id'];
        $totalcalls = 1;
        if (addslashes($this->cdr_variable['billsec']) > 0) {
            $answeredcalls = 1;
        } else {
            $answeredcalls = 0;
        }
        $prefix = $this->userdata['prefix'];
        $carrier_id = $this->carrierdata['carrier_id'];
        $end_stamp = addslashes($this->cdr_variable['end_stamp']);
        $s = strtotime($end_stamp);
        $call_date = date('Y-m-d H:i', $s);
        $calltime_h = date('H', $s);
        $calltime_m = date('i', $s);
        $r1_account_id = $this->reseller1_data['account_id'];
        $r2_account_id = $this->reseller2_data['account_id'];
        $r3_account_id = $this->reseller3_data['account_id'];
        $bill_duration = addslashes($this->cdr_variable['billsec']);
        $customer_duration = $customer_duration;
        $r1_duration = $reseller1_duartion;
        $r2_duration = $reseller2_duartion;
        $r3_duration = $reseller3_duartion;
        $pdd = addslashes($this->cdr_variable['progresssec']);
        $r1_cost = $reseller1_callcost_total;
        $r2_cost = $reseller2_callcost_total;
        $r3_cost = $reseller3_callcost_total;
        $carrier_duration = $carrier_duration;
        $carrier_prefix = $this->carrierdata['prefix'];
        $prefix_name = $this->userdata['destination'];
        $carrier_prefix_name = $this->carrierdata['destination'];
        $causeQ850 = $causeQ850_data = addslashes($this->cdr_variable['hangup_cause_q850']);
        $causeSIP = str_replace("SIP:", "", addslashes($this->cdr_variable['last_bridge_proto_specific_hangup_cause']));


        $fscause = $hangup_cause = addslashes($this->cdr_variable['hangup_cause']);
        if ($causeSIP == '' or $causeSIP == null) {
            $causeSIP = addslashes($this->cdr_variable['sip_term_status']);
        }

        $sip_hangup_disposition = addslashes($this->cdr_variable['sip_hangup_disposition']);
        $sip_invite_failure_status = addslashes($this->cdr_variable['sip_invite_failure_status']);
        $sip_invite_failure_phrase = addslashes($this->cdr_variable['sip_invite_failure_phrase']);

        if ($sip_invite_failure_status != '' and strlen($sip_invite_failure_status) > 2) {
            $causeQ850 = 1;
            $causeSIP = $sip_invite_failure_status;
            $fscause = $sip_invite_failure_phrase . "($hangup_cause)";
            $key = 'SIPCODE';
            $cause_result = $this->sipcode_search($this->fscodlist, $key, $sip_invite_failure_status);

            if (count($cause_result) > 0) {
                $causeQ850 = $cause_result['Q850CODE'];
                $causeSIP = $cause_result['SIPCODE'];
                $fscause = $cause_result['FSSTRING'];
            }
        } else if (($causeSIP == '' or strlen($causeSIP) < 3 or $causeSIP == NULL) and $causeQ850_data > 0) {
            $key = 'Q850CODE';
            $cause_result = $this->sipcode_search($this->fscodlist, $key, $causeQ850_data);
            if (count($cause_result) > 0) {
                $causeQ850 = $cause_result['Q850CODE'];
                $causeSIP = $cause_result['SIPCODE'];
                $fscause = $cause_result['FSSTRING'];
            }
        } elseif (($causeSIP == '' or strlen($causeSIP) < 3 or $causeSIP == NULL) and strlen($hangup_cause) > 0) {
            $key = 'FSSTRING';
            $cause_result = $this->sipcode_search($this->fscodlist, $key, $hangup_cause);
            if (count($cause_result) > 0) {
                $causeQ850 = $cause_result['Q850CODE'];
                $causeSIP = $cause_result['SIPCODE'];
                $fscause = $cause_result['FSSTRING'];
            }
        }

        if ($causeQ850 == '0' or $causeQ850 == '' or $causeQ850 == NULL) {
            $causeQ850 = $causeQ850_data = addslashes($this->cdr_variable['hangup_cause_q850']);
            $causeSIP = str_replace("SIP:", "", addslashes($this->cdr_variable['last_bridge_proto_specific_hangup_cause']));
            $fscause = $hangup_cause = addslashes($this->cdr_variable['hangup_cause']);
            if ($causeSIP == '' or $causeSIP == null) {
                $causeSIP = addslashes($this->cdr_variable['sip_term_status']);
            }
        }
        if ($causeQ850 == '0') {
            $causeQ850 = $causeQ850_data = addslashes($this->cdr_variable['hangup_cause_q850']);
            $causeSIP = str_replace("SIP:", "", addslashes($this->cdr_variable['last_bridge_proto_specific_hangup_cause']));
            $fscause = $hangup_cause = addslashes($this->cdr_variable['hangup_cause']);
            if ($causeSIP == '' or $causeSIP == null) {
                $causeSIP = addslashes($this->cdr_variable['sip_term_status']);
            }
        }

        $customer_actual_callcost = $customer_callcost_total;
        $reseller1_actual_callcost = $reseller1_callcost_total;
        $reseller2_actual_callcost = $reseller2_callcost_total;
        $reseller3_actual_callcost = $reseller3_callcost_total;

        if ($this->leg == 'A' and $this->switch_issue == '0' and addslashes($this->cdr_variable['billsec']) > 0) {
            $yearmonth = date('Ym');
            $b_value = 0;
            $sub_used = 0;
            if ($this->userdata['bundle_option'] == '1') {
                $customer_callcost_total_org = $customer_callcost_total;
                if ($this->userdata['bundle_type'] == 'MINUTE') {
                    if ($customer_duration <= $this->userdata['bundle_value'] * 60) {
                        $b_value = ceil($customer_duration);
                        $customer_callcost_total = 0;
                    } else {
                        $b_value = $this->userdata['bundle_value'];
                        $remaining_duration = $customer_duration - ($this->userdata['bundle_value'] * 60);
                        $customer_callcost_total = ($customer_callcost_total / $customer_duration) * $remaining_duration;
                    }
                }
                if ($this->userdata['bundle_type'] == 'COST') {
                    if ($customer_callcost_total <= $this->userdata['bundle_value']) {
                        $b_value = $customer_callcost_total;
                        $customer_callcost_total = 0;
                    } else {
                        $b_value = $this->userdata['bundle_value'];
                        $customer_callcost_total = $customer_callcost_total - $this->userdata['bundle_value'];
                    }
                }
                $cdr_date = date("Ym");
                $query = sprintf("SELECT id, account_id, rule_type, yearmonth,  total_allowed as  total_allowed , sdr_consumption as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s' and total_allowed > 0 and rule_type = '%s' and yearmonth = '%s';", $this->userdata['account_id'], $this->userdata['bundle_package_id'], $this->userdata['bundle_number'], $cdr_date);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs3 = $this->resultset();
                if (count($rs3) > 0) {
                    $sub_used = $b_value;
                    foreach ($rs3 as $data_b) {
                        if ($sub_used > 0) {
                            if ($b_value <= ($data_b['total_allowed'] - $data_b['sdr_consumption'])) {
                                $sub_used = $b_value;
                                $b_value = 0;
                            } else {
                                $sub_used = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                                $b_value = $b_value - $sub_used;
                            }
                            $query = sprintf("update customer_bundle_sdr  set sdr_consumption =  sdr_consumption + %s where id = '%s'", $sub_used / 60, $data_b['id']);
                            $this->writelog($query);
                            $this->query('SWITCH', $query);
                            $this->execute();
                        }
                    }

                    if ($b_value > 0) {
                        if ($this->userdata['bundle_type'] == 'COST') {
                            $customer_callcost_total = $customer_callcost_total + $b_value;
                        } elseif ($this->userdata['bundle_type'] == 'MINUTE') {
                            $remaining_duration = $customer_duration - ($this->userdata['bundle_value'] * 60);
                            $customer_callcost_total = $customer_callcost_total + ($customer_callcost_total_org / $customer_duration) * $b_value;
                        }
                    }
                } else {
                    $customer_callcost_total = $customer_callcost_total_org;
                }
            }
            $sub_used = 0;
            $b_value = 0;
            if ($this->reseller1_data['bundle_option'] == '1') {
                $reseller1_callcost_total_org = $reseller1_callcost_total;
                if ($this->reseller1_data['bundle_type'] == 'MINUTE') {
                    if ($reseller1_duartion <= $this->reseller1_data['bundle_value'] * 60) {
                        $b_value = ceil($reseller1_duartion);
                        $reseller1_callcost_total = 0;
                    } else {
                        $b_value = $this->reseller1_data['bundle_value'];
                        $remaining_duration = $reseller1_duartion - ($this->reseller1_data['bundle_value'] * 60);
                        $reseller1_callcost_total = ($reseller1_callcost_total / $reseller1_duartion) * $remaining_duration;
                    }
                }

                if ($this->reseller1_data['bundle_type'] == 'COST') {
                    if ($reseller1_callcost_total <= $this->reseller1_data['bundle_value']) {
                        $b_value = $reseller1_callcost_total;
                        $reseller1_callcost_total = 0;
                    } else {
                        $b_value = $this->reseller1_data['bundle_value'];
                        $reseller1_callcost_total = $reseller1_callcost_total - $this->reseller1_data['bundle_value'];
                    }
                }
                $cdr_date = date("Ym");
                $query = sprintf("SELECT id, account_id, rule_type, yearmonth,  total_allowed as  total_allowed , sdr_consumption as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s' and total_allowed > 0 and rule_type = '%s' and yearmonth = '%s';", $this->reseller1_data['account_id'], $this->reseller1_data['bundle_package_id'], $this->reseller1_data['bundle_number'], $cdr_date);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs3 = $this->resultset();
                if (count($rs3) > 0) {
                    $sub_used = $b_value;
                    foreach ($rs3 as $data_b) {
                        if ($sub_used > 0) {
                            if ($b_value <= ($data_b['total_allowed'] - $data_b['sdr_consumption'])) {
                                $sub_used = $b_value;
                                $b_value = 0;
                            } else {
                                $sub_used = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                                $b_value = $b_value - $sub_used;
                            }
                            $query = sprintf("update customer_bundle_sdr  set sdr_consumption =  sdr_consumption + %s where id = '%s'", $sub_used / 60, $data_b['id']);
                            $this->writelog($query);
                            $this->query('SWITCH', $query);
                            $this->execute();
                        }
                    }

                    if ($b_value > 0) {
                        if ($this->userdata['bundle_type'] == 'COST') {
                            $reseller1_callcost_total = $reseller1_callcost_total + $b_value;
                        } elseif ($this->userdata['bundle_type'] == 'MINUTE') {
                            $reseller1_callcost_total = $reseller1_callcost_total + ($reseller1_callcost_total_org / $reseller1_duartion) * $b_value;
                        }
                    }
                } else {
                    $reseller1_callcost_total = $reseller1_callcost_total_org;
                }
            }


            $sub_used = 0;
            $b_value = 0;
            if ($this->reseller2_data['bundle_option'] == '1') {
                $reseller2_callcost_total_org = $reseller2_callcost_total;
                if ($this->reseller2_data['bundle_type'] == 'MINUTE') {
                    if ($reseller2_duartion <= $this->reseller2_data['bundle_value'] * 60) {
                        $b_value = ceil($reseller2_duartion);



                        $reseller2_callcost_total = 0;
                    } else {
                        $b_value = $this->reseller2_data['bundle_value'];
                        $remaining_duration = $reseller2_duartion - ($this->reseller2_data['bundle_value'] * 60);
                        $reseller2_callcost_total = ($reseller2_callcost_total / $reseller2_duartion) * $remaining_duration;
                    }
                }

                if ($this->reseller2_data['bundle_type'] == 'COST') {
                    if ($reseller2_callcost_total <= $this->reseller2_data['bundle_value']) {
                        $b_value = $reseller2_callcost_total;
                        $reseller2_callcost_total = 0;
                    } else {
                        $b_value = $this->reseller2_data['bundle_value'];
                        $reseller2_callcost_total = $reseller2_callcost_total - $this->reseller2_data['bundle_value'];
                    }
                }


                $cdr_date = date("Ym");
                $query = sprintf("SELECT id, account_id, rule_type, yearmonth,  total_allowed as  total_allowed , sdr_consumption as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s' and total_allowed > 0 and rule_type = '%s' and yearmonth = '%s';", $this->reseller2_data['account_id'], $this->reseller2_data['bundle_package_id'], $this->reseller2_data['bundle_number'], $cdr_date);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs3 = $this->resultset();
                if (count($rs3) > 0) {
                    foreach ($rs3 as $data_b) {
                        $sub_used = $b_value;
                        if ($sub_used > 0) {
                            if ($b_value <= ($data_b['total_allowed'] - $data_b['sdr_consumption'])) {
                                $sub_used = $b_value;
                                $b_value = 0;
                            } else {
                                $sub_used = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                                $b_value = $b_value - $sub_used;
                            }
                            $query = sprintf("update customer_bundle_sdr  set sdr_consumption =  sdr_consumption + %s where id = '%s'", $sub_used / 60, $data_b['id']);
                            $this->writelog($query);
                            $this->query('SWITCH', $query);
                            $this->execute();
                        }
                    }

                    if ($b_value > 0) {
                        if ($this->userdata['bundle_type'] == 'COST') {
                            $reseller2_callcost_total = $reseller2_callcost_total + $b_value;
                        } elseif ($this->userdata['bundle_type'] == 'MINUTE') {
                            $reseller2_callcost_total = $reseller2_callcost_total + ($reseller2_callcost_total_org / $reseller2_duartion) * $b_value;
                        }
                    }
                } else {
                    $reseller2_callcost_total = $reseller2_callcost_total_org;
                }
            }


            $sub_used = 0;
            $b_value = 0;
            if ($this->reseller3_data['bundle_option'] == '1') {
                $reseller3_callcost_total_org = $reseller3_callcost_total;
                if ($this->reseller3_data['bundle_type'] == 'MINUTE') {
                    if ($reseller3_duartion <= $this->reseller3_data['bundle_value'] * 60) {
                        $b_value = ceil($reseller3_duartion / 60);
                        $reseller3_callcost_total = 0;
                    } else {
                        $b_value = $this->reseller3_data['bundle_value'];
                        $remaining_duration = $reseller3_duartion - ($this->reseller3_data['bundle_value'] * 60);
                        $reseller3_callcost_total = ($reseller3_callcost_total / $reseller3_duartion) * $remaining_duration;
                    }
                }

                if ($this->reseller3_data['bundle_type'] == 'COST') {
                    if ($reseller3_callcost_total <= $this->reseller3_data['bundle_value']) {
                        $b_value = $reseller3_callcost_total;
                        $reseller3_callcost_total = 0;
                    } else {
                        $b_value = $this->reseller3_data['bundle_value'];
                        $reseller3_callcost_total = $reseller3_callcost_total - $this->reseller3_data['bundle_value'];
                    }
                }

                $cdr_date = date("Ym");
                $query = sprintf("SELECT id, account_id, rule_type, yearmonth,  total_allowed as  total_allowed , sdr_consumption as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s' and total_allowed > 0 and rule_type = '%s' and yearmonth = '%s';", $this->reseller3_data['account_id'], $this->reseller3_data['bundle_package_id'], $this->reseller3_data['bundle_number'], $cdr_date);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs3 = $this->resultset();
                if (count($rs3) > 0) {
                    $sub_used = $b_value;
                    foreach ($rs3 as $data_b) {
                        if ($sub_used > 0)
                            if ($b_value <= ($data_b['total_allowed'] - $data_b['sdr_consumption'])) {
                                $sub_used = $b_value;
                                $b_value = 0;
                            } else {
                                $sub_used = $data_b['total_allowed'] - $data_b['sdr_consumption'];
                                $b_value = $b_value - $sub_used;
                            }
                        $query = sprintf("update customer_bundle_sdr  set sdr_consumption =  sdr_consumption + %s where id = '%s'", $sub_used, $data_b['id']);
                        $this->writelog($query);
                        $this->query('SWITCH', $query);
                        $this->execute();
                    }

                    if ($b_value > 0) {
                        if ($this->userdata['bundle_type'] == 'COST') {
                            $reseller3_callcost_total = $reseller3_callcost_total + $b_value;
                        } elseif ($this->userdata['bundle_type'] == 'MINUTE') {
                            $reseller3_callcost_total = $reseller3_callcost_total + ($reseller3_callcost_total_org / $reseller3_duration) * $b_value;
                        }
                    }
                } else {
                    $reseller3_callcost_total = $reseller3_callcost_total_org;
                }
            }
        }

        if ($this->cdr_variable['hangup_cause'] == 'PROGRESS_TIMEOUT') {
            $fscause = "PROGRESS_TIMEOUT";
        }

        $data = $data . "pdd = '" . addslashes($pdd) . "',";
        $data = $data . "fscause = '" . addslashes($fscause) . "',";
        $data = $data . "Q850CODE = '" . addslashes($causeQ850) . "',";
        $data = $data . "SIPCODE = '" . addslashes($causeSIP) . "',";
        $data = $data . "common_uuid = '" . addslashes($this->cdr_variable['common_uuid']) . "',";
        $data = $data . "caller_callid = '" . addslashes($this->cdr_variable['caller_callid']) . "',";
        $data = $data . "callee_callid = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
        $data = $data . "reseller3_tax1_cost = '" . addslashes($reseller3_tax1_cost) . "',";
        $data = $data . "reseller3_tax2_cost = '" . addslashes($reseller3_tax2_cost) . "',";
        $data = $data . "reseller3_tax3_cost = '" . addslashes($reseller3_tax3_cost) . "',";
        $data = $data . "reseller2_tax1_cost = '" . addslashes($reseller2_tax1_cost) . "',";
        $data = $data . "reseller2_tax2_cost = '" . addslashes($reseller2_tax2_cost) . "',";
        $data = $data . "reseller2_tax3_cost = '" . addslashes($reseller2_tax3_cost) . "',";
        $data = $data . "reseller1_tax1_cost = '" . addslashes($reseller1_tax1_cost) . "',";
        $data = $data . "reseller1_tax2_cost = '" . addslashes($reseller1_tax2_cost) . "',";
        $data = $data . "reseller1_tax3_cost = '" . addslashes($reseller1_tax3_cost) . "',";
        $data = $data . "customer_tax1_cost = '" . addslashes($customer_tax1_cost) . "',";
        $data = $data . "customer_tax2_cost = '" . addslashes($customer_tax2_cost) . "',";
        $data = $data . "customer_tax3_cost = '" . addslashes($customer_tax3_cost) . "',";
        $data = $data . "carrier_tax1_cost = '" . addslashes($carrier_tax1_cost) . "',";
        $data = $data . "carrier_tax2_cost = '" . addslashes($carrier_tax2_cost) . "',";
        $data = $data . "carrier_tax3_cost = '" . addslashes($carrier_tax3_cost) . "',";
        $data = $data . "carrier_callcost_total = '" . addslashes($carrier_callcost_total) . "',";
        $data = $data . "carrier_duration = '" . addslashes($carrier_duration) . "',";
        $data = $data . "customer_duration = '" . addslashes($customer_duration) . "',";
        $data = $data . "reseller1_duration = '" . addslashes($reseller1_duartion) . "',";
        $data = $data . "reseller2_duration = '" . addslashes($reseller2_duartion) . "',";
        $data = $data . "reseller3_duration = '" . addslashes($reseller3_duartion) . "',";
        $data = $data . "carrier_callcost = '" . addslashes($carrier_cost) . "',";
        $data = $data . "customer_callcost = '" . addslashes($customer_cost) . "',";
        $data = $data . "reseller1_callcost = '" . addslashes($reseller1_cost) . "',";
        $data = $data . "reseller2_callcost = '" . addslashes($reseller2_cost) . "',";
        $data = $data . "reseller3_callcost = '" . addslashes($reseller3_cost) . "',";
        $data = $data . "start_time= '" . addslashes($this->cdr_variable['start_stamp']) . "',";
        $data = $data . "answer_time= '" . addslashes($this->cdr_variable['answer_stamp']) . "',";
        $data = $data . "end_time= '" . addslashes($this->cdr_variable['end_stamp']) . "',";
        $data = $data . "hangupby= '" . addslashes($this->hangupby) . "',";
        $data = $data . "customer_callcost_total = '" . addslashes($customer_callcost_total) . "',";
        $data = $data . "reseller1_callcost_total = '" . addslashes($reseller1_callcost_total) . "',";
        $data = $data . "reseller2_callcost_total = '" . addslashes($reseller2_callcost_total) . "',";
        $data = $data . "reseller3_callcost_total = '" . addslashes($reseller3_callcost_total) . "',";
        $data = $data . "customer_actual_callcost= '" . addslashes($customer_actual_callcost) . "',";
        $data = $data . "reseller1_actual_callcost= '" . addslashes($reseller1_actual_callcost) . "',";
        $data = $data . "reseller2_actual_callcost= '" . addslashes($reseller2_actual_callcost) . "',";
        $data = $data . "reseller3_actual_callcost= '" . addslashes($reseller3_actual_callcost) . "',";
        $data = $data . "carrier_ratio= '" . addslashes($this->carrierdata['ratio']) . "',";
        $data = $data . "carrier_callcost_total_usercurrency= '" . addslashes($carrier_callcost_total_usercurrency) . "',";
        $data = $data . "profit_usercurrency= '" . addslashes($profit_usercurrency) . "',";
        $data = $data . "customer_balance= '" . addslashes($this->userdata['orgbalance']) . "',";
        $data = $data . "reseller1_balance= '" . addslashes($this->reseller1_data['orgbalance']) . "',";
        $data = $data . "reseller2_balance= '" . addslashes($this->reseller2_data['orgbalance']) . "',";
        $data = $data . "reseller3_balance= '" . addslashes($this->reseller3_data['orgbalance']) . "',";

        $data = $data .= "customer_company_name = '" . $this->userdata['company_name'] . "',";
        $data = $data .= "customer_incodecs = '" . $this->cdr_variable['USERCODECSCLIST'] . "',";
        $data = $data .= "carrier_outcodecs = '" . $this->carrierdata['carrier_codecs'] . "',";
        $data = $data .= "call_codecs = '" . $this->cdr_variable['write_codec'] . "',";
        $data = $data .= " billing_number = '" . $this->carrierdata['billing_number'] . "',";


        if ($this->switch_issue == '0' and addslashes($this->cdr_variable['billsec']) > 0 and $this->switch_balance == '1') {
            if (strlen($this->userdata['account_id']) > 0 and $customer_callcost_total > 0) {
                $sql = sprintf("update customer_balance set balance =  balance + '%s' where account_id = '%s';", $customer_callcost_total, $this->userdata['account_id']);
                $this->writelog($sql);
                $this->query('SWITCH', $sql);
                $this->execute();
            }
            if (strlen($this->reseller1_data['account_id']) > 0 and $reseller1_callcost_total > 0) {
                $sql = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $reseller1_callcost_total, $this->reseller1_data['account_id']);
                $this->writelog($sql);
                $this->query('SWITCH', $sql);
                $this->execute();
            }
            if (strlen($this->reseller2_data['account_id']) > 0 and $reseller2_callcost_total > 0) {
                $sql = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $reseller2_callcost_total, $this->reseller2_data['account_id']);
                $this->writelog($sql);
                $this->query('SWITCH', $sql);
                $this->execute();
            }
            if (strlen($this->reseller3_data['account_id']) > 0 and $reseller3_callcost_total > 0) {
                $sql = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $reseller3_callcost_total, $this->reseller3_data['account_id']);
                $this->writelog($sql);
                $this->query('SWITCH', $sql);
                $this->execute();
            }
            if ($this->switch_bill_cdrs_incoming == '1') {
                $data = $data .= "cdr_type = 'IN',";
                $cdrdata = rtrim($data, ',');
                $query = "insert into " . $dbdate . "_ratedcdr  set " . $cdrdata;
                $this->writelog($query);
                $this->query('CDR', $query);
                if ($this->execute()) {
                    
                } else {
                    $this->newtable_process($dbdate);
                    $this->query('CDR', $query);
                    $this->execute();
                }
            }

            if ($this->switch_bill_cdrs == '1') {
                $data = $data .= "cdr_type = 'OUT',";
                $cdrdata = rtrim($data, ',');
                $query = "insert into " . $dbdate . "_ratedcdr  set " . $cdrdata;
                $this->writelog($query);
                $this->query('CDR', $query);
                if ($this->execute()) {
                    
                } else {
                    $this->newtable_process($dbdate);
                    $this->query('CDR', $query);
                    $this->execute();
                }
            }
        }

        $notes = "Carrier:" . $carrier_id . " Carrier IPAddress:" . $this->carrierdata['gateway_ipaddress'] . " [Responce Codes] SIP:" . $causeSIP . " Q850:" . $causeQ850 . ", $fscause \n";

        if ($this->switch_carrier_statistics_incoming == '1') {
            $table_carrier_statistics = $dbdate . '_carrierstate';

            $query = sprintf("INSERT INTO %s ( carrier_id, bill_duration, carrier_duration, carrier_cost, totalcalls, answeredcalls, carrier_prefix, carrier_prefix_name, call_date, calltime_h, calltime_m, pdd, Q850CODE, SIPCODE,fscause, carrier_ipaddress, carrier_currency_id, carrier_name, cdr_type) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s' ,'%s','%s','%s','%s') ON DUPLICATE KEY UPDATE bill_duration = bill_duration+ values(bill_duration), carrier_duration = carrier_duration + values(carrier_duration), carrier_cost = carrier_cost + values(carrier_cost), totalcalls = totalcalls + values(totalcalls), answeredcalls = answeredcalls + values(answeredcalls), pdd = pdd+ values(pdd);", $table_carrier_statistics, $carrier_id, $bill_duration, $carrier_duration, $carrier_cost, $totalcalls, $answeredcalls, $carrier_prefix, $carrier_prefix_name, $call_date, $calltime_h, $calltime_m, $pdd, $causeQ850, $causeSIP, $fscause, $this->carrierdata['gateway_ipaddress'], $this->carrierdata['carrier_currency_id'], $this->carrierdata['carrier_name'], 'IN');

            $this->writelog($query);
            $this->query('CDR', $query);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $query);
                $this->execute();
            }
        }


        if ($this->switch_carrier_statistics == '1') {
            $table_carrier_statistics = $dbdate . '_carrierstate';

            $query = sprintf("INSERT INTO %s ( carrier_id, bill_duration, carrier_duration, carrier_cost, totalcalls, answeredcalls, carrier_prefix, carrier_prefix_name, call_date, calltime_h, calltime_m, pdd, Q850CODE, SIPCODE,fscause, carrier_ipaddress, carrier_currency_id, carrier_name, cdr_type ) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s' ,'%s','%s','%s','%s') ON DUPLICATE KEY UPDATE bill_duration = bill_duration+ values(bill_duration), carrier_duration = carrier_duration + values(carrier_duration), carrier_cost = carrier_cost + values(carrier_cost), totalcalls = totalcalls + values(totalcalls), answeredcalls = answeredcalls + values(answeredcalls), pdd = pdd+ values(pdd);", $table_carrier_statistics, $carrier_id, $bill_duration, $carrier_duration, $carrier_cost, $totalcalls, $answeredcalls, $carrier_prefix, $carrier_prefix_name, $call_date, $calltime_h, $calltime_m, $pdd, $causeQ850, $causeSIP, $fscause, $this->carrierdata['gateway_ipaddress'], $this->carrierdata['carrier_currency_id'], $this->carrierdata['carrier_name'], 'OUT');
            $this->writelog($query);
            $this->query('CDR', $query);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $query);
                $this->execute();
            }
        }
        if ($this->switch_issue == '0' and addslashes($this->cdr_variable['billsec']) == 0) {
            $query = sprintf("UPDATE livecalls SET Q850CODE ='%s', SIPCODE = '%s', fscause = '%s',notes = CONCAT(ifnull(notes,''),' ','%s') , end_time='%s' where  common_uuid = '%s' ", $causeQ850, $causeSIP, $fscause, $notes, addslashes($this->cdr_variable['end_stamp']), addslashes($this->cdr_variable['common_uuid']));
            $this->query('SWITCH', $query);
            $this->execute();
            $this->writelog($query);
        }

        if ($this->leg == 'A') {
            $query = sprintf("delete from livecalls where common_uuid = '%s'", addslashes($this->cdr_variable['common_uuid']));
            $this->query('SWITCH', $query);
            $this->execute();
            $this->writelog($query);
        }

        $data = $data . "carrier_ratio= '" . addslashes($this->carrierdata['ratio']) . "',";
        $data = $data . "carrier_callcost_total_usercurrency= '" . addslashes($carrier_callcost_total_usercurrency) . "',";
        $data = $data . "profit_usercurrency= '" . addslashes($profit_usercurrency) . "',";
        if ($this->switch_calls_statistics_incoming == '1') {
            $table_calls_statistics = $dbdate . '_customerstate';
            $query = sprintf("insert into %s (account_id, totalcalls, answeredcalls, prefix, carrier_id, call_date, calltime_h, calltime_m, r1_account_id, r2_account_id, r3_account_id, bill_duration, customer_duration, r1_duration, r2_duration, r3_duration, pdd,r1_cost, r2_cost, r3_cost, customer_cost, carrier_cost, carrier_duration, carrier_prefix, prefix_name, carrier_prefix_name, Q850CODE, SIPCODE,fscause, carrier_currency_id, customer_currency_id , carrier_ratio, carrier_callcost_total_usercurrency, profit_usercurrency, customer_company_name, carrier_name, src_ipaddress, cdr_type, rate,
carrier_rate,
r1_prefix,
r1_rate,
r2_prefix,
r2_rate,
r3_prefix,
r3_rate ) VALUES ('%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s','%s', '%s', '%s','%s','%s','%s','%s', '%s','%s','%s','%s', '%s', '%s', '%s','%s', '%s','%s','%s', '%s','%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') ON DUPLICATE KEY UPDATE totalcalls = totalcalls + 1, bill_duration = bill_duration + values(bill_duration), customer_duration =  customer_duration + values(customer_duration), r1_duration =  r1_duration + values(r1_duration), r2_duration = r2_duration + values(r2_duration), r3_duration = r3_duration + values(r3_duration), pdd = pdd + values(pdd), answeredcalls = answeredcalls + values(answeredcalls), r1_cost = r1_cost +values(r1_cost), r2_cost = r2_cost+values(r2_cost), r3_cost =r3_cost +values(r3_cost), customer_cost = customer_cost + values(customer_cost), carrier_cost = carrier_cost +values(carrier_cost), carrier_duration = carrier_duration + values(carrier_duration),  carrier_callcost_total_usercurrency = carrier_callcost_total_usercurrency + values(carrier_callcost_total_usercurrency), profit_usercurrency =values(profit_usercurrency) ;", $table_calls_statistics, $account_id, $totalcalls, $answeredcalls, $prefix, $carrier_id, $call_date, $calltime_h, $calltime_m, $r1_account_id, $r2_account_id, $r3_account_id, $bill_duration, $customer_duration, $r1_duration, $r2_duration, $r3_duration, $pdd, $reseller1_callcost_total, $reseller2_callcost_total, $reseller3_callcost_total, $customer_callcost_total, $carrier_cost, $carrier_duration, $carrier_prefix, $prefix_name, $carrier_prefix_name, $causeQ850, $causeSIP, $fscause, $this->carrierdata['carrier_currency_id'], $this->userdata['currency_id'], $carrier_ratio, $carrier_callcost_total_usercurrency, $profit_usercurrency, $this->userdata['company_name'], $this->carrierdata['carrier_name'], $this->userdata['src_ip'], 'IN', $rate, $carrier_rate, $r1_prefix, $r1_rate, $r2_prefix, $r2_rate, $r3_prefix, $r3_rate);
            $this->writelog($query);
            $this->query('CDR', $query);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $query);
                $this->execute();
            }
        }

        if ($this->switch_calls_statistics == '1') {
            $table_calls_statistics = $dbdate . '_customerstate';
            $query = sprintf("insert into %s (account_id, totalcalls, answeredcalls, prefix, carrier_id, call_date, calltime_h, calltime_m, r1_account_id, r2_account_id, r3_account_id, bill_duration, customer_duration, r1_duration, r2_duration, r3_duration, pdd,r1_cost, r2_cost, r3_cost, customer_cost, carrier_cost, carrier_duration, carrier_prefix, prefix_name, carrier_prefix_name, Q850CODE, SIPCODE,fscause, carrier_currency_id, customer_currency_id , carrier_ratio, carrier_callcost_total_usercurrency, profit_usercurrency, customer_company_name, carrier_name , src_ipaddress, cdr_type,  rate,
carrier_rate,
r1_prefix,
r1_rate,
r2_prefix,
r2_rate,
r3_prefix,
r3_rate ) VALUES ('%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s','%s', '%s', '%s','%s','%s','%s','%s', '%s','%s','%s','%s', '%s', '%s', '%s','%s', '%s','%s','%s', '%s','%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') ON DUPLICATE KEY UPDATE totalcalls = totalcalls + 1, bill_duration = bill_duration + values(bill_duration), customer_duration =  customer_duration + values(customer_duration), r1_duration =  r1_duration + values(r1_duration), r2_duration = r2_duration + values(r2_duration), r3_duration = r3_duration + values(r3_duration), pdd = pdd + values(pdd), answeredcalls = answeredcalls + values(answeredcalls), r1_cost = r1_cost +values(r1_cost), r2_cost = r2_cost+values(r2_cost), r3_cost =r3_cost +values(r3_cost), customer_cost = customer_cost + values(customer_cost), carrier_cost = carrier_cost +values(carrier_cost), carrier_duration = carrier_duration + values(carrier_duration),  carrier_callcost_total_usercurrency = carrier_callcost_total_usercurrency + values(carrier_callcost_total_usercurrency), profit_usercurrency =values(profit_usercurrency) ;", $table_calls_statistics, $account_id, $totalcalls, $answeredcalls, $prefix, $carrier_id, $call_date, $calltime_h, $calltime_m, $r1_account_id, $r2_account_id, $r3_account_id, $bill_duration, $customer_duration, $r1_duration, $r2_duration, $r3_duration, $pdd, $reseller1_callcost_total, $reseller2_callcost_total, $reseller3_callcost_total, $customer_callcost_total, $carrier_cost, $carrier_duration, $carrier_prefix, $prefix_name, $carrier_prefix_name, $causeQ850, $causeSIP, $fscause, $this->carrierdata['carrier_currency_id'], $this->userdata['currency_id'], $carrier_ratio, $carrier_callcost_total_usercurrency, $profit_usercurrency, $this->userdata['company_name'], $this->carrierdata['carrier_name'], $this->userdata['src_ip'], 'OUT', $rate, $carrier_rate, $r1_prefix, $r1_rate, $r2_prefix, $r2_rate, $r3_prefix, $r3_rate);
            $this->writelog($query);
            $this->query('CDR', $query);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $query);
                $this->execute();
            }
        }

        if ($this->leg == 'A' and $this->billcdr_outgoing == '1') {
            $causeSIP = str_replace(":", "", $causeSIP);
            $causeSIP = str_replace("sip", "", $causeSIP);
            $causeSIP = str_replace("SIP", "", $causeSIP);
        }

        $RAWCDRQUERY .= "fs_errorcode = '" . addslashes($this->cdr_variable['fs_errorcode']) . "',";
        $RAWCDRQUERY .= "carrier_ratecard_id = '" . addslashes($this->carrierdata['ratecard_id']) . "',";
        $RAWCDRQUERY .= "carrier_tariff_id = '" . addslashes($this->carrierdata['tariff_id']) . "',";
        $RAWCDRQUERY .= "carrier_prefix = '" . addslashes($this->carrierdata['prefix']) . "',";
        $RAWCDRQUERY .= "carrier_destination = '" . addslashes($this->carrierdata['destination']) . "',";
        $RAWCDRQUERY .= "carrier_ipaddress = '" . addslashes($this->carrierdata['ipaddress']) . "',";
        $RAWCDRQUERY .= "carrier_ipaddress_name = '" . addslashes($this->carrierdata['ipaddress_name']) . "',";
        $RAWCDRQUERY .= "carrier_src_caller = '" . addslashes($this->carrierdata['src_caller']) . "',";
        $RAWCDRQUERY .= "carrier_src_callee = '" . addslashes($this->carrierdata['src_callee']) . "',";
        $RAWCDRQUERY .= "carrier_dst_caller = '" . addslashes($this->carrierdata['dst_caller']) . "',";
        $RAWCDRQUERY .= "carrier_dst_callee = '" . addslashes($this->carrierdata['dst_callee']) . "',";
        $RAWCDRQUERY .= "customer_tariff_id = '" . addslashes($this->userdata['tariff_id']) . "',";
        $RAWCDRQUERY .= "customer_ipaddress = '" . addslashes($this->userdata['ipaddress']) . "',";
        $RAWCDRQUERY .= "customer_ratecard_id = '" . addslashes($this->userdata['ratecard_id']) . "',";
        $RAWCDRQUERY .= "customer_prefix = '" . addslashes($this->userdata['prefix']) . "',";
        $RAWCDRQUERY .= "customer_destination = '" . addslashes($this->userdata['destination']) . "',";
        $RAWCDRQUERY .= "customer_src_caller = '" . addslashes($this->userdata['src_caller']) . "',";
        $RAWCDRQUERY .= "customer_src_callee = '" . addslashes($this->userdata['src_callee']) . "',";
        $RAWCDRQUERY .= "customer_src_ip = '" . addslashes($this->userdata['src_ip']) . "',";
        $RAWCDRQUERY .= "customer_account_id = '" . addslashes($this->userdata['account_id']) . "',";
        $RAWCDRQUERY .= "reseller1_account_id = '" . addslashes($this->reseller1_data['account_id']) . "',";
        $RAWCDRQUERY .= "reseller1_tariff_id = '" . addslashes($this->reseller1_data['tariff_id']) . "',";
        $RAWCDRQUERY .= "reseller1_ratecard_id = '" . addslashes($this->reseller1_data['ratecard_id']) . "',";
        $RAWCDRQUERY .= "reseller1_prefix = '" . addslashes($this->reseller1_data['prefix']) . "',";
        $RAWCDRQUERY .= "reseller1_destination = '" . addslashes($this->reseller1_data['destination']) . "',";
        $RAWCDRQUERY .= "reseller2_destination = '" . addslashes($this->reseller2_data['destination']) . "',";
        $RAWCDRQUERY .= "reseller2_account_id = '" . addslashes($this->reseller2_data['account_id']) . "',";
        $RAWCDRQUERY .= "reseller2_tariff_id = '" . addslashes($this->reseller2_data['tariff_id']) . "',";
        $RAWCDRQUERY .= "reseller2_ratecard_id = '" . addslashes($this->reseller2_data['ratecard_id']) . "',";
        $RAWCDRQUERY .= "reseller2_prefix = '" . addslashes($this->reseller2_data['prefix']) . "',";
        $RAWCDRQUERY .= "reseller3_account_id = '" . addslashes($this->reseller3_data['account_id']) . "',";
        $RAWCDRQUERY .= "reseller3_tariff_id = '" . addslashes($this->reseller3_data['tariff_id']) . "',";
        $RAWCDRQUERY .= "reseller3_ratecard_id = '" . addslashes($this->reseller3_data['ratecard_id']) . "',";
        $RAWCDRQUERY .= "reseller3_prefix = '" . addslashes($this->reseller3_data['prefix']) . "',";
        $RAWCDRQUERY .= "reseller3_destination = '" . addslashes($this->reseller3_data['destination']) . "',";
        $RAWCDRQUERY .= "uuid = '" . addslashes($this->cdr_variable['uuid']) . "',";
        $RAWCDRQUERY .= "sip_from_user  = '" . addslashes($this->cdr_variable['sip_from_user']) . "',";
        $RAWCDRQUERY .= "sip_from_uri  = '" . addslashes($this->cdr_variable['sip_from_uri']) . "',";
        $RAWCDRQUERY .= "sip_from_host = '" . addslashes($this->cdr_variable['sip_from_host']) . "',";
        $RAWCDRQUERY .= "channel_name = '" . addslashes($this->cdr_variable['channel_name']) . "',";
        $RAWCDRQUERY .= "common_uuid = '" . addslashes($this->cdr_variable['common_uuid']) . "',";
        $RAWCDRQUERY .= "caller_callid = '" . addslashes($this->cdr_variable['caller_callid']) . "',";
        $RAWCDRQUERY .= "callee_callid = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
        $RAWCDRQUERY .= "sip_call_id = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
        $RAWCDRQUERY .= "ep_codec_string  = '" . addslashes($this->cdr_variable['ep_codec_string']) . "',";
        $RAWCDRQUERY .= "sip_network_ip = '" . addslashes($this->cdr_variable['sip_network_ip']) . "',";
        $RAWCDRQUERY .= "sip_received_ip = '" . addslashes($this->cdr_variable['sip_received_ip']) . "',";
        $RAWCDRQUERY .= "sip_received_port = '" . addslashes($this->cdr_variable['sip_received_port']) . "',";
        $RAWCDRQUERY .= "sip_via_protocol = '" . addslashes($this->cdr_variable['sip_via_protocol']) . "',";
        $RAWCDRQUERY .= "sip_from_user_stripped = '" . addslashes($this->cdr_variable['sip_from_user_stripped']) . "',";
        $RAWCDRQUERY .= "sip_from_display = '" . addslashes($this->cdr_variable['sip_from_display']) . "',";
        $RAWCDRQUERY .= "sip_full_from = '" . addslashes($this->cdr_variable['sip_full_from']) . "',";
        $RAWCDRQUERY .= "sip_to_display = '" . addslashes($this->cdr_variable['sip_to_display']) . "',";
        $RAWCDRQUERY .= "sip_full_to = '" . addslashes($this->cdr_variable['sip_full_to']) . "',";
        $RAWCDRQUERY .= "sip_req_user = '" . addslashes($this->cdr_variable['sip_req_user']) . "',";
        $RAWCDRQUERY .= "sip_req_uri = '" . addslashes($this->cdr_variable['sip_req_uri']) . "',";
        $RAWCDRQUERY .= "sip_to_user = '" . addslashes($this->cdr_variable['sip_to_user']) . "',";
        $RAWCDRQUERY .= "sip_contact_user = '" . addslashes($this->cdr_variable['sip_contact_user']) . "',";
        $RAWCDRQUERY .= "sip_contact_port = '" . addslashes($this->cdr_variable['sip_contact_port']) . "',";
        $RAWCDRQUERY .= "sip_contact_uri = '" . addslashes($this->cdr_variable['sip_contact_uri']) . "',";
        $RAWCDRQUERY .= "sip_contact_host = '" . addslashes($this->cdr_variable['sip_contact_host']) . "',";
        $RAWCDRQUERY .= "rtp_use_codec_string = '" . addslashes($this->cdr_variable['rtp_use_codec_string']) . "',";
        $RAWCDRQUERY .= "sip_user_agent = '" . addslashes($this->cdr_variable['sip_user_agent']) . "',";
        $RAWCDRQUERY .= "sip_via_host = '" . addslashes($this->cdr_variable['sip_via_host']) . "',";
        $RAWCDRQUERY .= "sip_via_port = '" . addslashes($this->cdr_variable['sip_via_port']) . "',";
        $RAWCDRQUERY .= "sip_via_rport = '" . addslashes($this->cdr_variable['sip_via_rport']) . "',";
        $RAWCDRQUERY .= "switch_r_sdp = '" . addslashes($this->cdr_variable['switch_r_sdp']) . "',";
        $RAWCDRQUERY .= "endpoint_disposition = '" . addslashes($this->cdr_variable['endpoint_disposition']) . "',";
        $RAWCDRQUERY .= "effective_caller_id_name = '" . addslashes($this->cdr_variable['effective_caller_id_name']) . "',";
        $RAWCDRQUERY .= "effective_caller_id_number = '" . addslashes($this->cdr_variable['effective_caller_id_number']) . "',";
        $RAWCDRQUERY .= "digits_dialed = '" . addslashes($this->cdr_variable['digits_dialed']) . "',";
        $RAWCDRQUERY .= "start_stamp = '" . addslashes($this->cdr_variable['start_stamp']) . "',";
        $RAWCDRQUERY .= "profile_start_stamp = '" . addslashes($this->cdr_variable['profile_start_stamp']) . "',";
        $RAWCDRQUERY .= "end_stamp = '" . addslashes($this->cdr_variable['end_stamp']) . "',";
        $RAWCDRQUERY .= "caller_id = '" . addslashes($this->cdr_variable['caller_id']) . "',";
        $RAWCDRQUERY .= "duration = '" . addslashes($this->cdr_variable['duration']) . "',";
        $RAWCDRQUERY .= "billsec = '" . addslashes($this->cdr_variable['billsec']) . "',";
        $RAWCDRQUERY .= "progresssec = '" . addslashes($this->cdr_variable['progresssec']) . "',";
        $RAWCDRQUERY .= "answersec = '" . addslashes($this->cdr_variable['answersec']) . "',";
        $RAWCDRQUERY .= "waitsec = '" . addslashes($this->cdr_variable['waitsec']) . "',";
        $RAWCDRQUERY .= "flow_billsec = '" . addslashes($this->cdr_variable['flow_billsec']) . "',";
        $RAWCDRQUERY .= "sip_hangup_disposition = '" . addslashes($this->cdr_variable['sip_hangup_disposition']) . "',";
        $RAWCDRQUERY .= "in_useragent= '" . addslashes($this->cdr_variable['in_useragent']) . "',";
        $RAWCDRQUERY .= "out_useragent= '" . addslashes($this->cdr_variable['out_useragent']) . "',";
        $RAWCDRQUERY .= "in_rtp_audio_in_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_in_media_bytes']) . "',";
        $RAWCDRQUERY .= "in_rtp_audio_out_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_out_media_bytes']) . "',";
        $RAWCDRQUERY .= "out_rtp_audio_in_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_in_media_bytes']) . "',";
        $RAWCDRQUERY .= "out_rtp_audio_out_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_out_media_bytes']) . "',";
        $RAWCDRQUERY .= "rtp_audio_in_media_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_media_packet_count']) . "',	";
        $RAWCDRQUERY .= "rtp_audio_out_media_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_out_media_packet_count']) . "',";
        $RAWCDRQUERY .= "in_rtp_audio_in_jitter_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']) . "',";
        $RAWCDRQUERY .= "out_rtp_audio_in_jitter_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']) . "',";
        $RAWCDRQUERY .= "in_rtp_audio_in_skip_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']) . "',	";
        $RAWCDRQUERY .= "out_rtp_audio_in_skip_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']) . "',	";
        $RAWCDRQUERY .= "in_rtp_audio_in_jitter_min_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']) . "',";
        $RAWCDRQUERY .= "in_rtp_audio_in_jitter_max_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']) . "',";
        $RAWCDRQUERY .= "out_rtp_audio_in_jitter_min_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']) . "',";
        $RAWCDRQUERY .= "out_rtp_audio_in_jitter_max_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']) . "',";
        $RAWCDRQUERY .= "in_rtp_audio_in_mos= '" . addslashes($this->cdr_variable['rtp_audio_in_mos']) . "',";
        $RAWCDRQUERY .= "out_rtp_audio_in_mos= '" . addslashes($this->cdr_variable['rtp_audio_in_mos']) . "', ";
        $RAWCDRQUERY .= "fscause = '" . addslashes($fscause) . "',";
        $RAWCDRQUERY .= "Q850CODE = '" . addslashes($causeQ850) . "',";
        $RAWCDRQUERY .= "SIPCODE = '" . addslashes($causeSIP) . "',";
        $RAWCDRQUERY .= "carrier_dialplan_id = '" . addslashes($this->carrierdata['dialplan_id']) . "',";
        $RAWCDRQUERY .= "carrier_id = '" . addslashes($this->carrierdata['carrier_id']) . "',";
        $RAWCDRQUERY .= "hangupby = '" . addslashes($this->hangupby) . "',";
        $RAWCDRQUERY .= "customer_incodecs = '" . $this->cdr_variable['USERCODECSCLIST'] . "',";
        $RAWCDRQUERY .= "carrier_outcodecs = '" . $this->carrierdata['carrier_codecs'] . "',";
        $RAWCDRQUERY .= "call_codecs = '" . $this->cdr_variable['write_codec'] . "',";
        $RAWCDRQUERY .= "customer_company_name = '" . $this->userdata['company_name'] . "',";
        $RAWCDRQUERY .= "carrier_carrier_name = '" . $this->carrierdata['carrier_name'] . "',";
        if ($this->switch_cdrs_incoming == '1') {
            $RAWCDRQUERY .= "cdr_type = 'IN',";
            $RAWCDRQUERY = rtrim($RAWCDRQUERY, ',');
            $RAWCDRQUERY_IN = 'insert into ' . $dbdate . "_cdr set " . $RAWCDRQUERY;
            $this->writelog($RAWCDRQUERY_IN);
            $this->query('CDR', $RAWCDRQUERY_IN);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $RAWCDRQUERY_IN);
                $this->execute();
            }
        }

        if ($this->switch_cdrs == '1') {
            $RAWCDRQUERY .= "cdr_type = 'OUT',";
            $RAWCDRQUERY = rtrim($RAWCDRQUERY, ',');
            $RAWCDRQUERY_IN = 'insert into ' . $dbdate . "_cdr set " . $RAWCDRQUERY;
            $this->writelog($RAWCDRQUERY_IN);
            $this->query('CDR', $RAWCDRQUERY_IN);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $RAWCDRQUERY_IN);
                $this->execute();
            }
        }
        if ($this->userdata['account_id'] == '' or strlen($this->userdata['account_id']) == 0) {
            $RAWCDRQUERY .= "cdr_type = 'DOSS',";
            $RAWCDRQUERY = rtrim($RAWCDRQUERY, ',');
            $RAWCDRQUERY_IN = 'insert into ' . $dbdate . "_cdr set " . $RAWCDRQUERY;
            $this->writelog($RAWCDRQUERY_IN);
            $this->query('CDR', $RAWCDRQUERY_IN);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $RAWCDRQUERY_IN);
                $this->execute();
            }
        }
    }

    function __destruct() {
        try {
            $this->dbswitch = null;
            $this->dbcdr = null;
        } catch (PDOException $e) {
            exit('App shoutdown');
        }
    }

    function newtable_process($date) {
        $tables = $this->table_craetion_schema($date);
        foreach ($tables as $query) {
            $this->writelog($query);
            $this->query('CDR', $query);
            if ($this->execute()) {
                
            } else {
                $this->query('CDR', $query);
                $this->execute();
            }
        }
    }

    function table_craetion_schema($date) {
        $tables['ratedcdrs'] = "CREATE TABLE IF NOT EXISTS   " . $date . "_ratedcdr (
  id int(11) NOT NULL AUTO_INCREMENT,
  uuid varchar(150) DEFAULT NULL,
  carrier_ratecard_id varchar(30) DEFAULT NULL,
  carrier_tariff_id varchar(30) DEFAULT NULL,
  carrier_prefix varchar(15) DEFAULT NULL,
  carrier_destination varchar(50) DEFAULT NULL,
  carrier_rate double(20,10) DEFAULT NULL,
  carrier_connection_charge double(20,10) DEFAULT NULL,
  carrier_minimal_time int(11) DEFAULT NULL,
  carrier_resolution_time int(11) DEFAULT NULL,
  carrier_grace_period int(11) DEFAULT NULL,
  carrier_rate_multiplier double(20,10) DEFAULT NULL,
  carrier_rate_addition double(20,10) DEFAULT NULL,
  carrier_id varchar(30) DEFAULT NULL,
  carrier_name varchar(30) DEFAULT NULL,
  carrier_ipaddress varchar(30) DEFAULT NULL,
  carrier_ipaddress_name varchar(30) DEFAULT NULL,
  carrier_currency_id int(11) DEFAULT NULL,
  carrier_src_caller varchar(30) DEFAULT NULL,
  carrier_src_callee varchar(30) DEFAULT NULL,
  carrier_dst_caller varchar(30) DEFAULT NULL,
  carrier_dst_callee varchar(30) DEFAULT NULL,
  carrier_duration int(11) DEFAULT NULL,
  carrier_dp int(11) DEFAULT NULL,
  carrier_tax1 double(20,10) DEFAULT '0.000000',
  carrier_tax2 double(20,10) DEFAULT '0.000000',
  carrier_tax3 double(20,10) DEFAULT '0.000000',
  carrier_tax_type varchar(15) DEFAULT NULL,
  carrier_callcost double(20,10) DEFAULT NULL,
  carrier_tax1_cost double(20,10) DEFAULT '0.000000',
  carrier_tax2_cost double(20,10) DEFAULT '0.000000',
  carrier_tax3_cost double(20,10) DEFAULT '0.000000',
  carrier_callcost_total double(20,10) DEFAULT '0.000000',
  carrier_dialplan_id varchar(30) DEFAULT NULL,
  customer_account_id varchar(30) DEFAULT NULL,
  customer_dp int(11) DEFAULT NULL,
  customer_tariff_id varchar(30) DEFAULT NULL,
  customer_currency_id int(11) DEFAULT NULL,
  customer_ipaddress varchar(30) DEFAULT NULL,
  customer_ratecard_id varchar(30) DEFAULT NULL,
  customer_prefix varchar(15) DEFAULT NULL,
  customer_destination varchar(50) DEFAULT NULL,
  customer_rate double(20,10) DEFAULT NULL,
  customer_connection_charge double(20,10) DEFAULT NULL,
  customer_minimal_time int(11) DEFAULT NULL,
  customer_resolution_time int(11) DEFAULT NULL,
  customer_grace_period int(11) DEFAULT NULL,
  customer_rate_multiplier double(20,10) DEFAULT NULL,
  customer_rate_addition double(20,10) DEFAULT NULL,
  customer_duration int(11) DEFAULT NULL,
  customer_src_caller varchar(30) DEFAULT NULL,
  customer_src_callee varchar(30) DEFAULT NULL,
  customer_tax1 double(20,10) DEFAULT NULL,
  customer_tax2 double(20,10) DEFAULT NULL,
  customer_tax3 double(20,10) DEFAULT NULL,
  customer_tax1_cost double(20,10) DEFAULT '0.000000',
  customer_tax_type varchar(20) DEFAULT NULL,
  customer_callcost double(20,10) DEFAULT NULL,
  customer_tax2_cost double(20,10) DEFAULT '0.000000',
  customer_tax3_cost double(20,10) DEFAULT '0.000000',
  customer_callcost_total double(20,10) DEFAULT '0.000000',
  customer_src_ip varchar(30) DEFAULT NULL,
  reseller1_account_id varchar(30) DEFAULT NULL,
  reseller1_tariff_id varchar(30) DEFAULT NULL,
  reseller1_dp int(11) DEFAULT NULL,
  reseller1_ratecard_id varchar(30) DEFAULT NULL,
  reseller1_prefix varchar(15) DEFAULT NULL,
  reseller1_destination varchar(50) DEFAULT NULL,
  reseller1_rate double(20,10) DEFAULT NULL,
  reseller1_connection_charge double(20,10) DEFAULT NULL,
  reseller1_minimal_time int(11) DEFAULT NULL,
  reseller1_resolution_time int(11) DEFAULT NULL,
  reseller1_grace_period int(11) DEFAULT NULL,
  reseller1_rate_multiplier double(20,10) DEFAULT NULL,
  reseller1_rate_addition double(20,10) DEFAULT NULL,
  reseller1_duration int(11) DEFAULT NULL,
  reseller1_tax1 double(20,10) DEFAULT NULL,
  reseller1_tax2 double(20,10) DEFAULT NULL,
  reseller1_tax3 double(20,10) DEFAULT NULL,
  reseller1_tax_type varchar(20) DEFAULT NULL,
  reseller1_callcost double(20,10) DEFAULT NULL,
  reseller1_tax1_cost double(20,10) DEFAULT '0.000000',
  reseller1_tax2_cost double(20,10) DEFAULT '0.000000',
  reseller1_tax3_cost double(20,10) DEFAULT '0.000000',
  reseller1_callcost_total double(20,10) DEFAULT '0.000000',
  reseller2_account_id varchar(30) DEFAULT NULL,
  reseller2_tariff_id varchar(30) DEFAULT NULL,
  reseller2_dp int(11) DEFAULT NULL,
  reseller2_ratecard_id  varchar(30) DEFAULT NULL,
  reseller2_prefix varchar(15) DEFAULT NULL,
  reseller2_destination varchar(50) DEFAULT NULL,
  reseller2_rate double(20,10) DEFAULT NULL,
  reseller2_connection_charge double(20,10) DEFAULT NULL,
  reseller2_minimal_time int(11) DEFAULT NULL,
  reseller2_resolution_time int(11) DEFAULT NULL,
  reseller2_grace_period int(11) DEFAULT NULL,
  reseller2_rate_multiplier double(20,10) DEFAULT NULL,
  reseller2_rate_addition double(20,10) DEFAULT NULL,
  reseller2_duration int(11) DEFAULT NULL,
  reseller2_callcost double(20,10) DEFAULT NULL,
  reseller2_tax1 double(20,10) DEFAULT NULL,
  reseller2_tax2 double(20,10) DEFAULT NULL,
  reseller2_tax3 double(20,10) DEFAULT NULL,
  reseller2_tax_type varchar(20) DEFAULT NULL,
  reseller2_tax1_cost double(20,10) DEFAULT '0.000000',
  reseller2_tax2_cost double(20,10) DEFAULT '0.000000',
  reseller2_tax3_cost double(20,10) DEFAULT '0.000000',
  reseller2_callcost_total double(20,10) DEFAULT '0.000000',
  reseller3_account_id varchar(30) DEFAULT NULL,
  reseller3_tariff_id  varchar(30) DEFAULT NULL,
  reseller3_dp int(11) DEFAULT NULL,
  reseller3_ratecard_id  varchar(50) DEFAULT NULL,
  reseller3_prefix varchar(50) DEFAULT NULL,
  reseller3_destination varchar(50) DEFAULT NULL,
  reseller3_rate double(20,10) DEFAULT NULL,
  reseller3_connection_charge double(20,10) DEFAULT NULL,
  reseller3_minimal_time int(11) DEFAULT NULL,
  reseller3_resolution_time int(11) DEFAULT NULL,
  reseller3_grace_period int(11) DEFAULT NULL,
  reseller3_rate_multiplier double(20,10) DEFAULT NULL,
  reseller3_rate_addition double(20,10) DEFAULT NULL,
  reseller3_duration int(11) DEFAULT NULL,
  reseller3_tax1 double(20,10) DEFAULT NULL,
  reseller3_tax2 double(20,10) DEFAULT NULL,
  reseller3_tax3 double(20,10) DEFAULT NULL,
  reseller3_tax_type varchar(30) DEFAULT NULL,
  reseller3_callcost double(20,10) DEFAULT NULL,
  reseller3_tax1_cost double(20,10) DEFAULT '0.000000',
  reseller3_tax2_cost double(20,10) DEFAULT '0.000000',
  reseller3_tax3_cost double(20,10) DEFAULT '0.000000',
  reseller3_callcost_total double(20,10) DEFAULT '0.000000',
  customer_actual_callcost double(20,10) DEFAULT NULL,
  reseller1_actual_callcost double(20,10) DEFAULT NULL,
  reseller2_actual_callcost double(20,10) DEFAULT NULL,
  reseller3_actual_callcost double(12,0) DEFAULT NULL,
  billsec int(11) DEFAULT NULL,
  start_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  answer_time datetime DEFAULT NULL,
  end_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  pdd int(11) DEFAULT NULL,
  fscause varchar(50) DEFAULT NULL,
  Q850CODE int(11) DEFAULT NULL,
  SIPCODE int(11) DEFAULT NULL,
  caller_callid varchar(150) DEFAULT NULL,
  callee_callid varchar(150) DEFAULT NULL,
  common_uuid varchar(150) DEFAULT NULL,
  hangupby varchar(20) DEFAULT NULL,
  customer_balance varchar(30) DEFAULT NULL,
  reseller1_balance varchar(30) DEFAULT NULL,
  reseller2_balance varchar(30) DEFAULT NULL,
  reseller3_balance varchar(30) DEFAULT NULL,
  carrier_callcost_total_usercurrency double(20,10) DEFAULT '0.000000',
  carrier_ratio double(20,10) DEFAULT '1.000000',
  profit_usercurrency double(20,10) DEFAULT '0.000000',
  customer_company_name varchar(150) DEFAULT NULL,
  customer_incodecs varchar(300) DEFAULT NULL,
  carrier_outcodecs varchar(30) DEFAULT NULL,
  call_codecs varchar(30) DEFAULT NULL, 
  data1 varchar(30) DEFAULT NULL,
  data2 varchar(30) DEFAULT NULL,
  cdr_type varchar(5) DEFAULT 'OUT',
  PRIMARY KEY (id,end_time),
  KEY customer_account_id (customer_account_id),
  KEY end_time (end_time),
  KEY reseller3_account_id (reseller3_account_id),
  KEY reseller1_account_id (reseller1_account_id),
  KEY reseller2_account_id (reseller2_account_id),
  KEY `customer_src_caller` (`customer_src_caller`) USING BTREE,
  KEY `customer_src_callee` (`customer_src_callee`) USING BTREE, 
  KEY `SIPCODE` (`SIPCODE`) USING BTREE,
  KEY `billsec` (`billsec`) USING BTREE,
  KEY `customer_ipaddress` (`customer_ipaddress`) USING BTREE,
  KEY `carrier_id` (`carrier_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";


        $tables['cdrs'] = "CREATE TABLE IF NOT EXISTS   " . $date . "_cdr (
id int(11) NOT NULL AUTO_INCREMENT,
common_uuid varchar(300) DEFAULT NULL,
carrier_dialplan_id varchar(30) DEFAULT NULL,
caller_callid varchar(150) DEFAULT NULL,
callee_callid varchar(150) DEFAULT NULL,
uuid varchar(150) DEFAULT NULL,
sip_from_user varchar(150) DEFAULT NULL,
sip_from_uri varchar(150) DEFAULT NULL,
sip_from_host varchar(150) DEFAULT NULL,
channel_name varchar(150) DEFAULT NULL,
sip_call_id varchar(150) DEFAULT NULL,
ep_codec_string varchar(300) DEFAULT NULL,
sip_network_ip varchar(150) DEFAULT NULL,
dialstatus varchar(30) DEFAULT NULL,
sip_received_ip varchar(30) DEFAULT NULL,
sip_received_port varchar(7) DEFAULT NULL,
sip_via_protocol varchar(30) DEFAULT NULL,
sip_from_user_stripped varchar(150) DEFAULT NULL,
sip_from_display varchar(150) DEFAULT NULL,
sip_full_from varchar(150) DEFAULT NULL,
sip_to_display varchar(30) DEFAULT NULL,
sip_full_to varchar(150) DEFAULT NULL,
sip_req_user varchar(150) DEFAULT NULL,
sip_req_uri varchar(150) DEFAULT NULL,
sip_to_user varchar(150) DEFAULT NULL,
sip_to_uri varchar(150) DEFAULT NULL,
sip_contact_user varchar(150) DEFAULT NULL,
sip_contact_port varchar(150) DEFAULT NULL,
sip_contact_uri varchar(150) DEFAULT NULL,
sip_contact_host varchar(150) DEFAULT NULL,
rtp_use_codec_string varchar(150) DEFAULT NULL,
sip_user_agent varchar(150) DEFAULT NULL,
sip_via_host varchar(150) DEFAULT NULL,
sip_via_port varchar(150) DEFAULT NULL,
sip_via_rport varchar(150) DEFAULT NULL,
switch_r_sdp text,
endpoint_disposition varchar(150) DEFAULT NULL,
effective_caller_id_name varchar(150) DEFAULT NULL,
effective_caller_id_number varchar(150) DEFAULT NULL,
digits_dialed varchar(150) DEFAULT NULL,
start_stamp varchar(150) DEFAULT NULL,
profile_start_stamp varchar(150) DEFAULT NULL,
caller_id varchar(30) DEFAULT NULL,
duration int(11) DEFAULT NULL,
billsec int(11) DEFAULT NULL,
progresssec int(11) DEFAULT NULL,
answersec int(11) DEFAULT NULL,
waitsec int(11) DEFAULT NULL,
flow_billsec int(11) DEFAULT NULL,
sip_hangup_disposition varchar(30) DEFAULT NULL,
in_useragent varchar(150) DEFAULT NULL,
out_useragent varchar(150) DEFAULT NULL,
in_rtp_audio_in_media_bytes int(11) DEFAULT NULL,
in_rtp_audio_out_media_bytes int(11) DEFAULT NULL,
out_rtp_audio_in_media_bytes int(11) DEFAULT NULL,
out_rtp_audio_out_media_bytes int(11) DEFAULT NULL,
rtp_audio_in_media_packet_count int(11) DEFAULT NULL,
rtp_audio_out_media_packet_count int(11) DEFAULT NULL,
in_rtp_audio_in_jitter_packet_count int(11) DEFAULT NULL,
out_rtp_audio_in_jitter_packet_count int(11) DEFAULT NULL,
in_rtp_audio_in_skip_packet_count int(11) DEFAULT NULL,
out_rtp_audio_in_skip_packet_count int(11) DEFAULT NULL,
in_rtp_audio_in_jitter_min_variance float(20,4) DEFAULT NULL,
in_rtp_audio_in_jitter_max_variance float(20,4) DEFAULT NULL,
out_rtp_audio_in_jitter_min_variance float(20,4) DEFAULT NULL,
out_rtp_audio_in_jitter_max_variance float(20,4) DEFAULT NULL,
in_rtp_audio_in_mos float(6,4) DEFAULT NULL,
out_rtp_audio_in_mos float(6,4) DEFAULT NULL,
fscause varchar(50) DEFAULT NULL,
Q850CODE int(11) DEFAULT NULL,
SIPCODE int(11) DEFAULT NULL,
carrier_id varchar(30) DEFAULT NULL,
carrier_ratecard_id varchar(30) DEFAULT NULL,
carrier_tariff_id varchar(30) DEFAULT NULL,
carrier_prefix varchar(30) DEFAULT NULL,
carrier_destination varchar(30) DEFAULT NULL,
carrier_ipaddress varchar(50) DEFAULT NULL,
carrier_ipaddress_name varchar(30) DEFAULT NULL,
carrier_src_caller varchar(30) DEFAULT NULL,
carrier_src_callee varchar(30) DEFAULT NULL,
carrier_dst_caller varchar(30) DEFAULT NULL,
carrier_dst_callee varchar(30) DEFAULT NULL,
customer_tariff_id varchar(30) DEFAULT NULL,
customer_ipaddress varchar(30) DEFAULT NULL,
customer_ratecard_id varchar(30) DEFAULT NULL,
customer_prefix varchar(30) DEFAULT NULL,
customer_destination varchar(30) DEFAULT NULL,
customer_src_caller varchar(30) DEFAULT NULL,
customer_src_callee varchar(30) DEFAULT NULL,
customer_src_ip varchar(30) DEFAULT NULL,
customer_account_id varchar(30) DEFAULT NULL,
reseller1_account_id varchar(30) DEFAULT NULL,
reseller1_tariff_id varchar(30) DEFAULT NULL,
reseller1_ratecard_id varchar(30) DEFAULT NULL,
reseller1_prefix varchar(20) DEFAULT NULL,
reseller1_destination varchar(50) DEFAULT NULL,
reseller2_destination varchar(50) DEFAULT NULL,
reseller2_account_id varchar(30) DEFAULT NULL,
reseller2_tariff_id varchar(30) DEFAULT NULL,
reseller2_ratecard_id varchar(30) DEFAULT NULL,
reseller2_prefix varchar(20) DEFAULT NULL,
reseller3_account_id varchar(30) DEFAULT NULL,
reseller3_tariff_id varchar(30) DEFAULT NULL,
reseller3_ratecard_id varchar(30) DEFAULT NULL,
reseller3_prefix varchar(20) DEFAULT NULL,
reseller3_destination varchar(50) DEFAULT NULL,
fs_errorcode varchar(40) DEFAULT NULL,
hangupby varchar(40) DEFAULT NULL,
end_stamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
customer_company_name varchar(150) DEFAULT NULL,
carrier_carrier_name varchar(150) DEFAULT NULL,
customer_incodecs varchar(300) DEFAULT NULL,
carrier_outcodecs varchar(30) DEFAULT NULL,
call_codecs varchar(30) DEFAULT NULL,
cdr_type varchar(5) DEFAULT 'OUT',
data1 varchar(30) DEFAULT NULL,
data2 varchar(30) DEFAULT NULL,
PRIMARY KEY (id,end_stamp),
KEY uuid (uuid) USING BTREE,
KEY customer_account_id (customer_account_id) USING BTREE,
KEY reseller1_account_id (reseller1_account_id) USING BTREE,
KEY reseller2_account_id (reseller2_account_id) USING BTREE,
KEY reseller3_account_id (reseller3_account_id) USING BTREE,
KEY end_stamp (end_stamp) USING BTREE,
KEY billsec (billsec) USING BTREE,
KEY `customer_src_caller` (`customer_src_caller`) USING BTREE,
KEY `customer_src_callee` (`customer_src_callee`) USING BTREE, 
KEY `SIPCODE` (`SIPCODE`) USING BTREE,
KEY `customer_ipaddress` (`customer_ipaddress`) USING BTREE,
KEY `carrier_id` (`carrier_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";

        $tables['carrier_state'] = "CREATE TABLE IF NOT EXISTS " . $date . "_carrierstate (
  id int(11) NOT NULL AUTO_INCREMENT,
  carrier_id varchar(30) DEFAULT NULL,
  carrier_ipaddress varchar(30) DEFAULT NULL,
  bill_duration int(11) DEFAULT '0',
  carrier_duration int(11) DEFAULT '0',
  carrier_currency_id int(11) DEFAULT '0',
  carrier_cost double(20,10) DEFAULT '0.000000',
  totalcalls int(11) DEFAULT '0',
  answeredcalls int(11) DEFAULT '0',
  carrier_prefix varchar(20) DEFAULT NULL,
  carrier_prefix_name varchar(50) DEFAULT NULL,
  call_date date DEFAULT NULL,
  calltime_h varchar(5) DEFAULT '0',
  calltime_m varchar(5) DEFAULT '0',
  pdd int(11) DEFAULT '0',
  fscause varchar(150) DEFAULT NULL,
  Q850CODE int(11) DEFAULT NULL,
  SIPCODE int(11) DEFAULT NULL,
  carrier_name varchar(150) DEFAULT NULL,
  cdr_type varchar(5) DEFAULT 'OUT',
  PRIMARY KEY (id),
  UNIQUE KEY carrier_id_key (carrier_id,call_date,calltime_h,calltime_m,Q850CODE,SIPCODE,fscause,carrier_ipaddress,cdr_type) USING BTREE,
  KEY call_date (call_date)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";



        $tables['statistics'] = "CREATE TABLE IF NOT EXISTS  " . $date . "_customerstate (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `totalcalls` int(11) DEFAULT '0',
  `answeredcalls` int(11) DEFAULT '0',
  `call_date` date DEFAULT NULL,
  `calltime_h` varchar(5) DEFAULT '0',
  `calltime_m` varchar(5) DEFAULT '0',
  `rate` double(20,10) DEFAULT NULL,
  `prefix` varchar(20) DEFAULT NULL,
  `prefix_name` varchar(50) DEFAULT NULL,
  `bill_duration` int(11) DEFAULT '0',
  `customer_duration` int(11) DEFAULT '0',
  `customer_currency_id` int(11) DEFAULT '0',
  `customer_cost` double(20,10) DEFAULT '0.000000',
  `r1_prefix` varchar(15) DEFAULT NULL,
  `r1_account_id` varchar(30) DEFAULT NULL,
  `r1_rate` double(20,10) DEFAULT NULL,
  `r1_duration` int(11) DEFAULT '0',
  `r1_cost` double(20,10) DEFAULT '0.000000',
  `r2_prefix` varchar(15) DEFAULT NULL,
  `r2_account_id` varchar(30) DEFAULT NULL,
  `r2_rate` double(20,10) DEFAULT NULL,
  `r2_duration` int(11) DEFAULT '0',
  `r2_cost` double(20,10) DEFAULT '0.000000',
  `r3_prefix` varchar(15) DEFAULT NULL,
  `r3_account_id` varchar(30) DEFAULT NULL,
  `r3_rate` double(20,10) DEFAULT NULL,
  `r3_duration` int(11) DEFAULT '0',
  `r3_cost` double(20,10) DEFAULT '0.000000',
  `carrier_prefix` varchar(20) DEFAULT NULL,
  `carrier_ratio` double(20,10) DEFAULT '1.000000',
  `carrier_id` varchar(30) DEFAULT NULL,
  `carrier_duration` int(11) DEFAULT '0',
  `carrier_rate` double(16,6) DEFAULT NULL,
  `carrier_currency_id` int(11) DEFAULT '0',
  `carrier_cost` double(20,10) DEFAULT '0.000000',
  `carrier_prefix_name` varchar(50) DEFAULT NULL,
  `carrier_name` varchar(150) DEFAULT NULL,
  `carrier_callcost_total_usercurrency` double(20,10) DEFAULT '0.000000',
  `customer_company_name` varchar(150) DEFAULT NULL,
  `pdd` int(11) DEFAULT '0',
  `fscause` varchar(150) DEFAULT NULL,
  `Q850CODE` int(11) DEFAULT NULL,
  `SIPCODE` int(11) DEFAULT NULL,
  `profit_usercurrency` double(20,10) DEFAULT '0.000000',
  `src_ipaddress` varchar(30) DEFAULT NULL,
  `cdr_type` varchar(5) DEFAULT 'OUT',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`,`prefix`,`carrier_id`,`call_date`,`calltime_h`,`calltime_m`,`r1_account_id`,`r2_account_id`,`r3_account_id`,`Q850CODE`,`SIPCODE`,`src_ipaddress`,`cdr_type`,`rate`,`r1_prefix`,`r1_rate`,`r2_prefix`,`r2_rate`,`r3_prefix`,`r3_rate`,`carrier_prefix`,`carrier_rate`) USING BTREE,
  KEY `call_date` (`call_date`),
  KEY `calltime_h` (`calltime_h`),
  KEY `calltime_m` (`calltime_m`),
  KEY `account_id_2` (`account_id`),
  KEY `carrier_id` (`carrier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";
        $tables['ratedcdr_alter23052021'] = "ALTER TABLE   " . $date . "_ratedcdr 
ADD COLUMN `carrier_callcost_inclusive_usercurrency`  double(20,10) NULL AFTER `cdr_type`,
ADD COLUMN `billing_number`  varchar(30) NULL AFTER `carrier_callcost_inclusive_usercurrency`,
ADD INDEX `billing_number` (`billing_number`) USING BTREE ;";

        $tables['ratedcdr_alter23052021'] = "ALTER TABLE   " . $date . "_ratedcdr 
 
ADD COLUMN `billing_number`  varchar(30) NULL AFTER `cdr_type`,
ADD INDEX `billing_number` (`billing_number`) USING BTREE ;";
        return $tables;
    }

}
