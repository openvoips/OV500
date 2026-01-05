<?php

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
    var $recordingxml = '0';
    var $recording_status = 0;
    var $recording_service = 0;
    var $out_dialplan = 0;
    var $inboundcalls = 0;
    var $mpstncall = '';
    var $last_array_j_value = 200;
    var $internaldidroute = null;
    var $passcode = '';
    var $ast = 0;
    var $attest_in = null;
    var $did2pst = '0';
    var $incomingtopstn = 0;
    var $fscodlist = array(
    );

    function __construct() {
        $this->writelog('New Hit');
        $this->lb = LB;
    }

    function IdentityHeader($data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, STIRURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $contents = curl_exec($ch);
        curl_close($ch);
        return $contents;
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
    }

    public function cdr($cdrstr) {
        $this->basic_cdrvariables($cdrstr);
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
        if (LOGWRITE) {
            $datestr = date("dmY/");
            $recording_file = RECORDING . "recording/" . $this->account_id . "/" . date('Ymd');

            if (!file_exists($recording_file)) {
                mkdir($recording_file, 0777, true);
            }
            if (!file_exists(LOGPATH . $datestr)) {
                mkdir(LOGPATH . $datestr, 0777, true);
            }
            $this->fh = fopen(LOGPATH . $datestr . "ovs.log", 'a+');
            $datestr = date("M d H:i:s");
            $log = $this->uuid . " :: " . "$datestr :: $log\n";
            fwrite($this->fh, $log);
        }
    }

    function writelogcallupdate($log) {
        if (LOGWRITE) {
            $datestr = date("dmY/");

            $recording_file = RECORDING . "recording/" . $this->account_id . "/" . date('Ymd');

            if (!file_exists($recording_file)) {
                mkdir($recording_file, 0777, true);
            }

            if (!file_exists(LOGPATH . $datestr)) {
                mkdir(LOGPATH . $datestr, 0777, true);
            }
            $this->fh = fopen(LOGPATH . $datestr . "callupdate.log", 'a+');
            $datestr = date("M d H:i:s");

            if (strlen($this->leg) > 1)
                $log = $this->leg . " " . $this->uuid . " :: " . "$datestr :: $log\n";
            else
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

    function basic_cdrvariables($str) {
        $str = json_decode(json_encode((array) simplexml_load_string($str)), 1);

        $this->xmldata = serialize($str);
        foreach ($str as $key => $value) {
            if ($key == 'variables') {
                if (is_array($value)) {

                    $cdr_ORG = $value['direction'];
                    $this->uuid = $cdr_ORG . " :: " . $value['uuid'];
                    foreach ($value as $key1 => $value1) {
                        $this->writelog($this->direction . "  $key1 => $value1");
                        $this->cdr_variable[$key1] = trim(urldecode($value1));
                        $this->cdrlogdata .= "[$key1 : " . urldecode($value1) . "]";
                        $this->cdrlogdata1 .= "$key1 : " . urldecode($value1) . "\n";
                    }
                }
            }
        }

        $this->direction = strtolower($this->cdr_variable['direction']);
        if ($this->direction == 'outbound')
            $this->leg = 'B';
        else
            $this->leg = 'A';
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

    function get_routeVariable($request_data) {
        foreach ($request_data as $key => $value) {
            $this->request[$key] = $value;
        }
    }

    function usableg_xml() {
        $carrier = $this->request['Caller-Destination-Number'];
        $fixduration = 12;
        $this->writelog("System Time " . $this->request['variable_current_application_data']);
        $a_epoch = $this->request['variable_current_application_data'];
        $a_epoch2 = explode('=', $a_epoch);
        $a_epoch = $a_epoch2[1];
        $this->writelog("Time Answer " . $a_epoch);
        $this->writelog("SYSTEM Time " . time());
        $answer_duration = time() - $a_epoch;
        $this->writelog("A-Leg Bill Sec " . $answer_duration);
        $this->writelog("CARRIER " . $carrier);

        $this->writelog("AAAAAAA NUMBER " . $carrier);
        $query = sprintf("select  minimumcallduration   from  carrier where carrier_id = '%s'  limit 1; ", $carrier);
        $this->writelog($query);

        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $minimumcallduration = $value;
        }
        $fixduration = $minimumcallduration;
        if ($answer_duration > $fixduration) {
            $fixduration = 0;
        } else {
            $fixduration = $fixduration - $answer_duration;
        }
        $responce = "<?xml version = \"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>
                            <document type=\"OV500Switch/xml\">
                            <section name=\"dialplan\" description=\"RE Dial Plan For OV500Switch\">";

        $responce .= "\n<context name=\"usableg\">
<extension name=\"1000\">
<condition field=\"destination_number\" expression=\"^(.+)$\">
	<action application=\"log\" data=\"AFTER BRIDGE4\"/>
	<action application=\"set\" data=\"fixduration=" . $fixduration . "\"/>
	<action application=\"set\" data=\"b_lenght=\${expr(\${strepoch()} - \${a_epoch})}\"/>

        <action application=\"sched_hangup\" data=\"+" . $fixduration . " NORMAL_CLEARING\"/>
        <action application=\"playback\" data=\"tone_stream://%(400,200,400,450);%(400,2000,400,450);;loops=20\"/>
        <action application=\"hangup\"/>

</condition>
</extension>
</context>
    </section>
</document>";
        $this->writelog($responce);
        RETURN $responce;
    }

    function main($REQUEST) {
        error_reporting(0);
        $this->set_requestdata($REQUEST);
        $this->get_routeVariable($REQUEST);
        $this->dbconnect();
        if ($this->request['xmlformat'] == 'json') {
            $this->uuid = $this->request['UniqueID'];
            if (strlen(trim($this->request['didnumber'])) > 0) {
                $this->didnumber = $this->request['didnumber'];
                $this->carrierdata['did_number'] = $this->request['didnumber'];
            }
            $this->callernumber = $this->request['Caller_Orig_Caller_ID_Number'];
            $this->destination_number_org = $this->destination_number = $this->request['Caller_Destination_Number'];
            $this->caller_number = $this->request['Hunt_Username'];
            if ($this->caller_number == NULL)
                $this->caller_number = $this->request['Hunt_Caller_ID_Number'];
            $this->caller_orggnetwork = $this->request['Caller_Network_Addr'];
            $this->caller_auth = $this->request['variable_sip_auth_username'];
            if ($this->caller_auth == null)
                $this->caller_auth = $this->caller_auth_calerid = $this->request['Hunt_Caller_ID_Number'];
            $this->lega_codecs = $this->request['variable_ep_codec_string'];
            $this->caller_orggnetwork_auth = $this->request['variable_sip_contact_host'];
            $this->caller_orggnetwork = $this->request['variable_sip_contact_host'];
            $this->profile = 'internal';
            if ($this->caller_orggnetwork_auth == '' or $this->caller_orggnetwork_auth == NULL) {
                $this->caller_orggnetwork = $this->request['variable_sip_contact_host'];
                $this->caller_orggnetwork_auth = $this->request['variable_sip_contact_host'];
            }
            if (strlen($this->request['changedCLI']) > 0) {
                $this->callernumber_user = $this->callernumber = $this->caller_number = $this->request['changedCLI'];
                $this->writelog("callernumber_user " . $this->callernumber_user);
                $this->writelog("callernumber " . $this->callernumber);
                $this->writelog("caller_number " . $this->caller_number);
            } else if (strlen($this->request['outcli']) > 0) {
                $this->callernumber_user = $this->callernumber = $this->caller_number = $this->request['outcli'];
                $this->writelog("callernumber_user " . $this->callernumber_user);
                $this->writelog("callernumber " . $this->callernumber);
                $this->writelog("caller_number " . $this->caller_number);
            }

            if (strlen($this->request['dst']) > 0) {
                $this->destination_number = $this->request['dst'];
            }
            $this->src_caller = $this->callernumber;
            $this->src_callee = $this->destination_number;
            $this->dst_caller = $this->caller_number;
            $this->dst_callee = $this->destination_number;
            $this->issue = 0;
            $this->rip = $this->caller_orggnetwork;
            $this->rip2 = $this->caller_orggnetwork_auth;
            $this->account_id = $this->request['account_id'];
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
            $this->user_account_id = $this->request['variable_sip_h_X_ACCOUNTCODE'];
            $this->user_originator_ip = $this->request['variable_sip_h_X_MEDIP'];
            $this->user_device_id = $this->request['variable_sip_h_X_DEVICEID'];
            $this->user_device_cc = $this->request['variable_sip_h_X_DEVICECC'];
            $this->user_device_cps = $this->request['variable_sip_h_X_DEVICECPS'];
            $this->user_id = $this->request['variable_sip_h_X_ACCOUNTID'];
            $this->user_cc = $this->request['variable_sip_h_X_ACCOUNTCC'];
            $this->user_cps = $this->request['variable_sip_h_X_ACCOUNTCPS'];
            $this->user_withmedia = $this->request['variable_sip_h_X_WITHMEDIA'];
            $this->user_device_type = $this->request['variable_sip_h_X_DEVICETYPE'];
            $this->caller_callid = $this->request['variable_sip_call_id'];
            $this->Hunt_Network_Addr = $this->request['Hunt_Network_Addr'];
            $this->incomingcarrier = $this->request['variable_sip_h_X_INCOMINGCARRIER'];
            $this->incomingcarrierdst = $this->request['variable_sip_h_X_INCOMINGCARRIERDST'];
            $this->extension_id = $this->request['extension_id'];
            $this->sdp = $this->request['variable_switch_r_sdp'];
            $this->request['Event-Date-Local'] = date('Y-m-d h:i:s');
            $this->request['FreeSWITCH-IPv4'] = $this->request['FreeSWITCH_IPv4'];
        } else {
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
            if (strlen(trim($this->request['account_id'])) > 0)
                $this->account_id = $this->request['account_id'];
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
        }


        if ($this->request['Hunt-Context'] == 'usableg') {
            $responce = $this->usableg_xml();
            RETURN $responce;
        }


        $this->lb = $this->Hunt_Network_Addr;
        if (strlen($this->lb) == 0) {
            $this->lb = LB;
        }

        $this->extension_src_id = '';
        if (strlen($this->request['variable_sip_h_X-DEVICEID']) > 0 and $this->request['variable_sip_h_X-DEVICETYPE'] == 'u') {
            $this->extension_src_id = $this->request['variable_sip_h_X-DEVICEID'];
        }

        if (STIRSHAKEN) {
            $cmdd1 = $this->request['variable_sip_h_identity'];
            if (strlen($cmdd1) > 20) {
                $cmd = '/usr/bin/stirshaken jwt-check --jwt "' . $cmdd1 . '"';
                $this->writelog("variable_sip_h_identity  =>  " . $cmd);

                exec($cmd, $output);
                $uuidlist = array_filter($output);
                foreach ($uuidlist as $udata) {
                    $myString = trim($udata);
                    if (strstr($myString, 'attest')) {
                        $myString = rtrim($myString, ',');
                        $ast = explode(":", $myString);
                        $ast1 = trim(str_replace('"', '', $ast[0])) . PHP_EOL;
                        $ast11 = trim(str_replace('"', '', $ast[1])) . PHP_EOL;
                        $this->attest_in = trim($ast11);
                        $this->writelog("ast1  " . $ast1);
                        $this->writelog("ast  " . $ast11);
                    }
                }
            } else {
                $this->writelog("variable_sip_h_identity  is not in call  ");
            }
        }

        if (strlen($this->incomingcarrier) > 0) {
            $this->DID_Call();

            if ($this->status_accountstaus == 'FAIL') {
                return $this->responce;
            }
            if ($this->status != 'FAIL') {
                $responce = $this->DID_route_dialplan_xml();
                if ($this->request['xmlformat'] == 'json') {
                    return json_encode($this->Gateway_ARRAY2);
                }
                RETURN $responce;
            }
            $responce = $this->DID_route_dialplan_xml();
            if ($this->request['xmlformat'] == 'json') {
                return json_encode($this->Gateway_ARRAY2);
            }
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


        $this->ispbxcall = 'NO';
        $data_result = $this->pbxcalls();
        if ($this->ispbxcall == 'OK') {
            return $data_result;
        }


        $this->PSTN_Call();
        if ($this->status == 'FAIL') {
            $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
            $responce = $this->PSTN_route_dialplan_xml();
            if ($this->request['xmlformat'] == 'json') {
                return json_encode($this->Gateway_ARRAY2);
            }
            RETURN $responce;
        } else {
            $this->PSTN_routing();
        }
        if ($this->status == 'FAIL') {
            $responce = $this->PSTN_route_dialplan_xml();
            if ($this->request['xmlformat'] == 'json') {
                return json_encode($this->Gateway_ARRAY2);
            }
            RETURN $responce;
        }

        $responce = $this->PSTN_route_dialplan_xml();
        if ($this->request['xmlformat'] == 'json') {
            return json_encode($this->Gateway_ARRAY2);
        }
        RETURN $responce;
    }

    function pbxcalls() {
        if (strlen($this->incomingcarrier) > 0) {
            
        } else {
            $query = sprintf(" select * from allnumbers where account_id = '%s' and extension_no = '%s' limit 1;", $this->account_id, $this->destination_number);

            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            foreach ($rs[0] as $key => $value) {
                $this->pbxnumber[$key] = $value;
                $this->customers[$key] = $value;
            }
            if ($this->pbxnumber['dst_type'] == 'DID') {
                return;
            }
        }
        $this->customers['account_id'] = $this->account_id;
        $this->timeout = 7200;
        $this->internalcall_pbx();
        if (strlen($this->pbxnumber['account_id']) > 0) {
            $this->responce = "<?xml version=\"2.0.0\"?>
              <document type=\"OvSwitch/xml\">
              <section name=\"dialplan\" description=\"Regex/XML Dialplan\">
              <context name=\"default\">";
            $this->responce .= "\n<extension name=\"outbound_international\">
                <condition field=\"destination_number\" expression=\"^(.+)$\">";
            $this->responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=0\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=0\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"hangup_after_bridge=TRUE\"/>";
            $this->responce .= "\n<action application=\"pre_answer\"/>";
            $codecs = $this->usercodeclist();
            $this->responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"userdata=" . $this->rates . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"userdata=" . $this->rates . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"execute_on_answer=sched_hangup +" . $this->timeout . " alloted_timeout \"/>";
            $this->responce .= "\n <action application=\"lua\" data=\"" . PATH . "portal/application/modules/pbx/script/pbx.lua\"/>";
            $this->responce .= "  </condition>
                    </extension>
                    </context>
                    </section>
              </document>";
            $this->status = 'OK';
            $this->ispbxcall = 'OK';
            return $this->responce;
        }
    }

    function DID_Call() {
        $this->currencies_data();
        $lb = $this->Hunt_Network_Addr;
        $lb = $this->lb;

        $this->incomingdid_org = $this->incomingcarrierdst;
        $query = sprintf("SELECT did.did_number, did.did_status, did.carrier_id, did.account_id, did.reseller1_account_id, did.reseller2_account_id, did.reseller3_account_id   from did   where did.did_number   = '%s'  ORDER BY did.did_number limit 1;", $this->incomingcarrierdst, $this->incomingcarrier);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->carrierdata[$key] = $value;
        }


        $this->didnumber = $this->carrierdata['did_number'];
        $this->accountstatus = Array();
        $this->accountstatus['account_status'] = '0';
        $this->accountstatus['balance'] = 0;
        $query = sprintf("select account.account_id, parent_account_id, dp, currency_id, tax_type,tax1,tax2, tax3, round_logic, recording,account_codecs, status_id as account_status,  (credit_limit - balance ) as balance from account INNER JOIN customer_balance on customer_balance.account_id = account.account_id  where account.account_id ='%s'  limit 1;", $this->carrierdata['account_id']);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->accountstatus[$key] = $value;
            $this->customersdata['user'][$key] = $value;
        }


        $query = sprintf("select company_name , name  from customers where account_id = '%s';", $this->carrierdata['account_id']);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->customersdata['user'][$key] = preg_replace("/[^A-Za-z0-9_ ()]/", '', $value);
        }

        if (strlen($this->accountstatus['parent_account_id']) > 0) {
            $query = sprintf("select account.account_id, parent_account_id, dp, currency_id, tax_type,tax1,tax2, tax3, round_logic, recording,account_codecs, status_id as account_status,  (credit_limit - balance ) as balance from account INNER JOIN customer_balance on customer_balance.account_id = account.account_id  where account.account_id ='%s'  limit 1;", $this->accountstatus['parent_account_id']);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            foreach ($rs[0] as $key => $value) {

                $this->customersdata['Reseller1'][$key] = $value;
                $this->writelog("Inbound-call-Balance and status data -- " . $key . " value " . $value);
            }
        }
        if (strlen($this->customersdata['Reseller1']['parent_account_id']) > 0) {
            $query = sprintf("select account.account_id, parent_account_id, dp, currency_id, tax_type,tax1,tax2, tax3, round_logic, recording,account_codecs, status_id as account_status,  (credit_limit - balance ) as balance from account INNER JOIN customer_balance on customer_balance.account_id = account.account_id  where account.account_id ='%s'  limit 1;", $this->accountstatus['parent_account_id']);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            foreach ($rs[0] as $key => $value) {

                $this->customersdata['Reseller2'][$key] = $value;
                $this->writelog("Inbound-call-Balance and status data -- " . $key . " value " . $value);
            }
        }
        if (strlen($this->customersdata['Reseller2']['parent_account_id']) > 0) {
            $query = sprintf("select account.account_id, parent_account_id, dp, currency_id, tax_type,tax1,tax2, tax3, round_logic, recording,account_codecs, status_id as account_status,  (credit_limit - balance ) as balance from account INNER JOIN customer_balance on customer_balance.account_id = account.account_id  where account.account_id ='%s'  limit 1;", $this->accountstatus['parent_account_id']);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            foreach ($rs[0] as $key => $value) {

                $this->customersdata['Reseller3'][$key] = $value;
                $this->writelog("Inbound-call-Balance and status data -- " . $key . " value " . $value);
            }
        }



        $this->writelog("Inbound-call-Balance and status data STATUS - " . $this->accountstatus['account_status'] . " BALANCE " . $this->accountstatus['balance']);

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

        $this->carrierdata['src_caller'] = $this->caller_number;
        $this->carrierdata['src_callee'] = $this->incomingdid_org;
        $this->carrierdata['dst_caller'] = $this->caller_number;
        $this->carrierdata['dst_callee'] = $this->incomingcarrierdst;
        $this->carrierdata['ipaddress'] = $this->account_originator_ip;
        $this->carrierdata['ipaddress_name'] = $this->account_originator_ip;
        if ($this->carrierdata['carrier_status'] != '1' or $this->carrierdata['tariff_status'] != '1') {
            $this->status_accountstaus = 'FAIL';
            $otherinfo = $this->incomingcarrier;
            $this->responce = "<?xml version=\"2.0.0\"?>
              <document type=\"OvSwitch/xml\">
              <section name=\"dialplan\" description=\"Regex/XML Dialplan\">
              <context name=\"default\">";
            $this->responce .= "\n<extension name=\"outbound_international\">
                <condition field=\"destination_number\" expression=\"^(.+)$\">";
            if ($this->carrierdata['carrier_status'] != '1') {
                $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode_in=CARRIERINACTIVE\"/>";
                $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode_in=CARRIERINACTIVE\"/>";
                $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode=CARRIERINACTIVE\"/>";
                $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode=CARRIERINACTIVE\"/>";
                $this->otherinfo = $this->carrierdata['carrier_id'] . ' carrier is not active';
                $this->responce .= "\n<action application=\"set\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
                $this->responce .= "\n<action application=\"export\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            } else {
                $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode_in=CARRIERTARIFFINACTIVE\"/>";
                $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode_in=CARRIERTARIFFINACTIVE\"/>";
                $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode=CARRIERTARIFFINACTIVE\"/>";
                $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode=CARRIERTARIFFINACTIVE\"/>";
                $this->otherinfo = $this->carrierdata['tariff_id'] . ' tariff is not active';
                $this->responce .= "\n<action application=\"set\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
                $this->responce .= "\n<action application=\"export\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            }
            $this->responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
            $this->customers['account_id'] = $this->carrierdata['account_id'];

            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata));
            if (STIRSHAKEN) {
                if ($this->attest_in == 'A' or $this->attest_in == 'B' or $this->attest_in == 'C') {
                    $this->responce .= "\n <action application=\"set\" data=\"attest_in=" . $this->attest_in . "\"/>";
                    $this->responce .= "\n <action application=\"export\" data=\"attest_in=" . $this->attest_in . "\"/>";
                }
            }
            $this->responce .= "\n<action application=\"set\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"sip_term_status=503\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"sip_term_status=503\"/>";
            $this->responce .= "<action application=\"hangup\" data=\"NORMAL_TEMPORARY_FAILURE\"/>";
            $this->responce .= "  </condition>
                    </extension>
                    </context>
                    </section>
              </document>";
            $this->status = 'FAIL';
            return $this->responce;
        }

        $query = sprintf("SELECT did.did_number, did.did_status, did.carrier_id, did.account_id, did.reseller1_account_id, did.reseller2_account_id, did.reseller3_account_id   from did   where did.did_number   =  '%s'  ORDER BY did.did_number limit 1;", $this->carrierdata['did_number']);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->carrierdata[$key] = $value;
        }
        $query = sprintf("SELECT   did_dst.account_id, did_dst.dst_type, did_dst.dst_destination  from     did_dst  where did_number  = '%s'   and account_id = '%s'  ORDER BY did_number desc, did_dst_id desc limit 1;", $this->carrierdata['did_number'], $this->carrierdata['account_id']);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->carrierdata[$key] = $value;
        }

        if (strlen($this->carrierdata['did_number']) > 0) {
            $did_after_carrier_dst_rule = $this->carrierdata['did_number'];
        }

        $query = sprintf("SELECT LENGTH(maching_string) lndata, remove_string, add_string, action_type FROM carrier_callerid where carrier_id = '%s' and '%s'  like maching_string   and route = 'INBOUND' like maching_string ORDER BY lndata desc limit 1;", $this->incomingcarrier, $this->caller_number);

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
        $query = sprintf("SELECT tariff.tariff_status, tariff_ratecard_map.id, tariff_ratecard_map.ratecard_id, tariff_ratecard_map.tariff_id, tariff_ratecard_map.start_day, tariff_ratecard_map.end_day, tariff_ratecard_map.start_time, tariff_ratecard_map.end_time, carrier_rates.prefix, REPLACE(REPLACE( carrier_rates.destination,',',' '),'-',' ') as destination, carrier_rates.rate, carrier_rates.connection_charge, carrier_rates.minimal_time, carrier_rates.resolution_time, carrier_rates.grace_period, carrier_rates.rate_multiplier, carrier_rates.rate_addition, carrier_rates.rates_status, tariff.tariff_currency_id, tariff.tariff_currency_id FROM tariff_ratecard_map  INNER JOIN carrier_rates on carrier_rates.ratecard_id = tariff_ratecard_map.ratecard_id    INNER JOIN ratecard on carrier_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'INCOMING' INNER JOIN tariff on  tariff.tariff_id = tariff_ratecard_map.tariff_id where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and tariff_ratecard_map.tariff_id = '%s'  and (%s) ORDER BY priority asc, prefix desc, rate ASC, end_time ASC limit 1;", $this->carrierdata['tariff_id'], $str);

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

        $this->account_id = $this->carrierdata['account_id'];
        $this->carrierdata['src_caller'] = $this->caller_number;
        $this->carrierdata['src_callee'] = $this->incomingdid_org; //$this->incomingcarrierdst;
        $this->carrierdata['dst_caller'] = $this->caller_number;
        $this->carrierdata['dst_callee'] = $this->incomingcarrierdst;
        $this->carrierdata['ipaddress'] = $this->account_originator_ip;
        $this->carrierdata['ipaddress_name'] = $this->account_originator_ip;
        if ($this->carrierdata['rates_status'] != '1') {
            $this->status_accountstaus = 'FAIL';
            $otherinfo = $this->incomingcarrier;
            $this->responce = "<?xml version=\"2.0.0\"?>
              <document type=\"OvSwitch/xml\">
              <section name=\"dialplan\" description=\"Regex/XML Dialplan\">
              <context name=\"default\">";
            $this->responce .= "\n<extension name=\"outbound_international\">
                <condition field=\"destination_number\" expression=\"^(.+)$\">";
            $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode_in=CARRIERRATEINACTIVE\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode_in=CARRIERRATEINACTIVE\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode=CARRIERRATEINACTIVE\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode=CARRIERRATEINACTIVE\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
            $this->customers['account_id'] = $this->carrierdata['account_id'];
            $this->otherinfo = $this->carrierdata['tariff_id'] . " tariff " . $this->carrierdata["prefix"] . " rate is not active";
            $this->responce .= "\n<action application=\"set\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata));
            $this->responce .= "\n<action application=\"set\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"sip_term_status=503\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"sip_term_status=503\"/>";
            $this->responce .= "<action application=\"hangup\" data=\"NORMAL_TEMPORARY_FAILURE\"/>";
            $this->responce .= "  </condition>
                    </extension>
                    </context>
                    </section>
              </document>";

            $this->status = 'FAIL';
            return $this->responce;
        }



        if ($this->accountstatus['balance'] <= 0) {
            $this->status_accountstaus = 'FAIL';
            $otherinfo = $this->incomingcarrier;
            $this->responce = "<?xml version=\"2.0.0\"?>
              <document type=\"OvSwitch/xml\">
              <section name=\"dialplan\" description=\"Regex/XML Dialplan\">
              <context name=\"default\">";
            $this->responce .= "\n<extension name=\"outbound_international\">
                <condition field=\"destination_number\" expression=\"^(.+)$\">";
            $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode_in=USERBALANCE\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode_in=USERBALANCE\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode=USERBALANCE\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode=USERBALANCE\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
            $this->customers['account_id'] = $this->carrierdata['account_id'];
            $this->otherinfo = $this->carrierdata['account_id'] . " account " . $this->accountstatus['balance'] . " balance is low";
            $this->responce .= "\n<action application=\"set\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata));
            $this->responce .= "\n<action application=\"set\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            if (STIRSHAKEN) {
                if ($this->attest_in == 'A' or $this->attest_in == 'B' or $this->attest_in == 'C') {
                    $this->responce .= "\n <action application=\"set\" data=\"attest_in=" . $this->attest_in . "\"/>";
                    $this->responce .= "\n <action application=\"export\" data=\"attest_in=" . $this->attest_in . "\"/>";
                }
            }
            $this->responce .= "\n<action application=\"set\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"sip_term_status=402\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"sip_term_status=402\"/>";
            $this->responce .= "<action application=\"hangup\" data=\"NORMAL_TEMPORARY_FAILURE\"/>";
            $this->responce .= "  </condition>
                    </extension>
                    </context>
                    </section>
              </document>";

            $this->status = 'FAIL';
            return $this->responce;
        }

        if ($this->accountstatus['account_status'] == '1' and $this->accountstatus['balance'] >= 0) {
            $this->status_accountstaus = 'OK';
        } else if ($this->accountstatus['account_status'] != '1') {
            $this->status_accountstaus = 'FAIL';

            $this->responce = "<?xml version=\"2.0.0\"?>
              <document type=\"OvSwitch/xml\">
              <section name=\"dialplan\" description=\"Regex/XML Dialplan\">
              <context name=\"default\">";
            $this->responce .= "\n<extension name=\"outbound_international\">
                <condition field=\"destination_number\" expression=\"^(.+)$\">";
            $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode_in=USERINACTIVE\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode_in=USERINACTIVE\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode=USERINACTIVE\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode=USERINACTIVE\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
            $this->customers['account_id'] = $this->carrierdata['account_id'];
            $this->otherinfo = $this->carrierdata['account_id'] . " account is inactive";
            $this->responce .= "\n<action application=\"set\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata));
            $this->responce .= "\n<action application=\"set\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"sip_term_status=503\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"sip_term_status=503\"/>";
            $this->responce .= "<action application=\"hangup\" data=\"NORMAL_TEMPORARY_FAILURE\"/>";
            $this->responce .= "  </condition>
                    </extension>
                    </context>
                    </section>
              </document>";

            $this->status = 'FAIL';
            return $this->responce;
        }
        /*  DNC call checking */
        $block_dst = substr($this->caller_number, -10);
        $block_dst = ltrim($this->caller_number, "-");
        $query = sprintf("SELECT Substring(dstnumber,-10) as blockdst FROM block_dst  where dstnumber = '%s'  and  account_id = '%s' limit 1;", $block_dst, $this->account_id);

        $blockdst = '';
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $blockdst = $value;
        }

        if (strlen(trim($blockdst)) > 0) {
            
        } else {
            $query = sprintf("SELECT length(dstnumber) as dstlength,  dstnumber as blockdst FROM block_dst  where  account_id = '%s' and '%s' like concat('%%',dstnumber,'%%') order by dstlength desc limit 1;", $this->account_id, $this->caller_number);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            foreach ($rs[0] as $key => $value) {
                $blockdst = $value;
            }
        }

        if (strlen(trim($blockdst)) > 0) {
            $otherinfo = $this->account_id;
            $this->fail_route_xml('USERCC', $otherinfo);

            $this->otherinfo = $this->account_id . " " . $this->caller_number . " number DNC issue";
            $this->status = 'FAIL';
            $this->otherinfo = $this->destination_number . ' number is block';
            $this->status_accountstaus = 'FAIL';

            $this->responce = "<?xml version=\"2.0.0\"?>
              <document type=\"OvSwitch/xml\">
              <section name=\"dialplan\" description=\"Regex/XML Dialplan\">
              <context name=\"default\">";
            $this->responce .= "\n<extension name=\"outbound_international\">
                <condition field=\"destination_number\" expression=\"^(.+)$\">";
            $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode_in=DSTBLOCK\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode_in=DSTBLOCK\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
            $this->customers['account_id'] = $this->carrierdata['account_id'];
            $this->customersdata['user'] = $this->customers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata));
            $this->responce .= "\n<action application=\"set\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"CALLTYPE=PSTN\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"CALLTYPE=PSTN\"/>";
            $this->responce .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"sip_term_status=503\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"sip_term_status=503\"/>";
            $this->responce .= "<action application=\"hangup\" data=\"NORMAL_TEMPORARY_FAILURE\"/>";
            $this->responce .= "  </condition>
                    </extension>
                    </context>
                    </section>
              </document>";
            $this->status = 'FAIL';
            return $this->responce;
        }

        /*  end DNC call checking */


        $block_cli = substr($this->caller_number, -10);
        $query = sprintf("SELECT Substring(callerid,-10) as didclifilter FROM block_cli  where Substring(callerid,-10) = '%s' and  account_id = '%s' limit 1;", $block_cli, $this->customers['account_id']);

        $blockcli = '';
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $blockcli = $value;
        }

        if (strlen(trim($blockcli)) > 0) {
            
        } else {
            $query = sprintf("SELECT length(callerid) as clilength,  cli as didclifilter FROM block_cli  where '%s' like concat('%%',callerid,'%%')  and  account_id = '%s' order by clilength desc limit 1;", $this->caller_number, $this->customers['account_id']);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            foreach ($rs[0] as $key => $value) {
                $blockcli = $value;
            }
        }

        if (strlen(trim($blockcli)) > 0) {

            $this->status_accountstaus = 'FAIL';

            $this->responce = "<?xml version=\"2.0.0\"?>
              <document type=\"OvSwitch/xml\">
              <section name=\"dialplan\" description=\"Regex/XML Dialplan\">
              <context name=\"default\">";
            $this->responce .= "\n<extension name=\"outbound_international\">
                <condition field=\"destination_number\" expression=\"^(.+)$\">";
            $this->responce .= "\n<action application=\"set\" data=\"fs_errorcode_in=GLOBLENUMBERBLOCK\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode_in=GLOBLENUMBERBLOCK\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"fs_errorcoden=GLOBLENUMBERBLOCK\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"fs_errorcode=GLOBLENUMBERBLOCK\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
            $this->customers['account_id'] = $this->carrierdata['account_id'];
            $this->responce .= "\n<action application=\"set\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";

            $this->otherinfo = $this->caller_number . " caller ID is blocked(G)";
            $this->responce .= "\n<action application=\"set\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";

            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata));
            if (STIRSHAKEN) {
                if ($this->attest_in == 'A' or $this->attest_in == 'B' or $this->attest_in == 'C') {
                    $this->responce .= "\n <action application=\"set\" data=\"attest_in=" . $this->attest_in . "\"/>";
                    $this->responce .= "\n <action application=\"export\" data=\"attest_in=" . $this->attest_in . "\"/>";
                }
            }
            $this->responce .= "\n<action application=\"set\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"CALLTYPE=INCOMING\"/>";
            $this->responce .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
            $this->responce .= "\n<action application=\"set\" data=\"sip_term_status=503\"/>";
            $this->responce .= "\n<action application=\"export\" data=\"sip_term_status=503\"/>";
            $this->responce .= "<action application=\"hangup\" data=\"NORMAL_TEMPORARY_FAILURE\"/>";
            $this->responce .= "  </condition>
                    </extension>
                    </context>
                    </section>
              </document>";
            $this->status = 'FAIL';
            return $this->responce;
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
        $this->carrierdata['src_callee'] = $this->incomingdid_org; //$this->incomingcarrierdst;
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
        $this->route22['dst_callee'] = $this->incomingcarrierdst;
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
        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"sip_require_timer=true\"/>";
        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
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
        if ($this->carrierdata['carrier_progress_timeout'] > 5) {
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"progress_timeout=" . $this->carrierdata['carrier_progress_timeout'] . "\"/>";
        } else {
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"progress_timeout=10\"/>";
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
        if (STIRSHAKEN) {
            if ($this->attest_in == 'A' or $this->attest_in == 'B' or $this->attest_in == 'C') {
                $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"attest_in=" . $this->attest_in . "\"/>";
                $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"attest_in=" . $this->attest_in . "\"/>";
            }
        }
        $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"sip_h_X-CARRIERID=DIDGATEWAY\"/>";
        $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"sip_h_X-CARRIERCPS=5000\"/>";

        $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:execute_on_ring=curl " . APIDOAMIN . "api/api.php?calltype=IN&r=ring&common_uuid=" . $this->uuid . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&account=" . $this->carrierdata['account_id'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . " | -k \"/>";
        $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?calltype=IN&r=ring&common_uuid=" . $this->uuid . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&account=" . $this->carrierdata['account_id'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . " | -k \"/>";
        $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?calltype=IN&r=answer&common_uuid=" . $this->uuid . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&account=" . $this->carrierdata['account_id'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . " | -k \"/>";
        $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?calltype=IN&r=answer&common_uuid=" . $this->uuid . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&account=" . $this->carrierdata['account_id'] . "&gateway_ipaddress=" . $this->account_originator_ip . "&carrier_gateway_ipaddress_name=" . $this->account_originator_ip . "&carrier=" . $this->carrierdata['carrier_id'] . "&destination_number=" . $this->carrierdata['destination'] . " | -k \"/>";
        $this->pbxcall = 0;
        $dst_type = Array('CAMPAINGN', 'IPDEVICE', 'CONFERENCE', 'EXTEN', 'RINGGROUP', 'GROUP', 'VOICEMAIL', 'QUEUE', 'DIALCODE', 'SIPDEVICE', 'IP', 'CUSTOMER', 'PSTN', 'SIPURI', 'HANGUP', 'IVR', 'VOICEMESSAGE', 'ANNOUNCEMENT', 'ANNUNCEMENT', 'TIMEROUTE');
        $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"DIDNUMBER=" . $this->incomingcarrierdst . "\"/>";
        if (in_array($this->carrierdata['dst_type'], $dst_type)) {
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"call_timeout=60\"/>";
            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"progress_timeout=60\"/>";
            $this->pbxcall = 0;
            if ($this->carrierdata['dst_type'] == 'CAMPAINGN') {
                $dst_list = Array();
                $dst_list = $this->campaign_dstlist();
                $query = sprintf("SELECT campaigns.campaign_name ,  campaigns.campaign_name, campaigns.description, campaigns.campaign_id, campaigns.call_strategy,   campaigns.call_theading,   campaigns.account_id FROM campaigns where campaign_id = '%s' limit 1   ;", $this->carrierdata['dst_destination']);

                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs = $this->resultset();
                if (count($rs) > 0) {
                    foreach ($rs as $data) {
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"campaign_id=" . $data['campaign_id'] . "\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"campaign_id=" . $data['campaign_id'] . "\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"campaign_name=" . $data['campaign_name'] . "\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"campaign_name=" . $data['campaign_name'] . "\"/>";
                    }
                }
                $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"did_number=" . $this->carrierdata['did_number'] . "\"/>";
                $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"did_number=" . $this->carrierdata['did_number'] . "\"/>";
                $this->writelog("CMP -----" . str_replace('"', "'", json_encode($dst_list, JSON_UNESCAPED_UNICODE)));
                if (count($dst_list) > 0 or !empty($dst_list)) {
                    foreach ($dst_list as $dst) {

                        $this->incomingtopstn = 1;
                        $this->src_callee = $dst['buyer_number'];
                        $this->account_id = $this->carrierdata['account_id'];
                        $this->destination_number = $dst['buyer_number'];
                        $this->destination_number_check = $dst['buyer_number'];
                        $this->campaign_id = $dst['campaign_id'];
                        $this->campaign_name = $dst['campaign_name'];
                        $this->did_number = $this->carrierdata['did_number'];
                        $this->buyer_number = $dst['buyer_number'];
                        $this->buyer_name = $dst['buyer_name'];
                        $this->destination_number_user = $this->destination_number;
                        $this->src_callee = $this->destination_number;
                        $this->ringtime_buyer = $dst['ringtime'];
                        $this->writelog(" Buyer number ---------------  " . $this->destination_number);
                        $this->src_caller = $this->callernumber_user = $this->callernumber;
                        if ($this->callernumber != '' AND $this->callernumber != NULL) {
                            $this->src_caller = $this->callernumber_user = $this->callernumber = $route_callid;
                        }
                        $this->inboundcalls = 1;
                        $this->PSTN_Call();
                        if ($this->status == 'FAIL') {
                            
                        } else {

                            $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"did_number=" . $this->carrierdata['did_number'] . "\"/>";
                            $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"did_number=" . $this->carrierdata['did_number'] . "\"/>";
                            $this->PSTN_routing();
                            if ($this->out_dialplan == '1') {
                                $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"INCOMING2PSTN=1\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"INCOMING2PSTN=1\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"userdata=" . $this->rates . "\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"userdata=" . $this->rates . "\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"campaign_id=" . $dst['campaign_id'] . "\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"campaign_id=" . $dst['campaign_id'] . "\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"campaign_name=" . $dst['campaign_name'] . "\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"campaign_name=" . $dst['campaign_name'] . "\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"buyer_name=" . $dst['buyer_name'] . "\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"buyer_name=" . $dst['buyer_name'] . "\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"buyer_number=" . $dst['buyer_number'] . "\"/>";
                                $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"buyer_number=" . $dst['buyer_number'] . "\"/>";

                                $this->Gateway_XML_incoming .= $this->Gateway_XML;
                            }
                        }
                    }
                } else {
                    $this->status = 'FAIL';

                    $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
                    if (strlen($this->fs_errorcode) > 0) {
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"fs_errorcode=" . $this->fs_errorcode . "\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"fs_errorcode=" . $this->fs_errorcode . "\"/>";
                    } else {
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"fs_errorcode=CAMPAINGNISSUE\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"fs_errorcode=CAMPAINGNISSUE\"/>";
                    }
                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";

                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"didnumber=" . $this->carrierdata['did_number'] . "\"/>";

                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"disposition_cause=" . $this->otherinfo . "\"/>";

                    $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata));
                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"CALLTYPE=INCOMING\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"CALLTYPE=INCOMING\"/>";
                    $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
                    $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"userdataincoming=" . $this->rates_incoming . "\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"sip_term_status=503\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"sip_term_status=503\"/>";
                    $this->Gateway_XML_incoming .= "<action application=\"hangup\" data=\"NORMAL_TEMPORARY_FAILURE\"/>";
                }
            } elseif ($this->carrierdata['dst_type'] == 'SIPURI') {
                $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"sip_h_X-DSTURI=sip:" . $this->carrierdata['dst_destination'] . "\"/>";
                $this->Gateway_XML_incoming .= "\n <action application=\"bridge\" data=\"sofia/internal/" . $this->incomingcarrierdst . "@" . $lb . "\"/>";
            } elseif (
                    $this->carrierdata['dst_type'] == 'IVR'
                    or $this->carrierdata['dst_type'] == 'VOICEMESSAGE'
                    or $this->carrierdata['dst_type'] == 'ANNOUNCEMENT'
                    or $this->carrierdata['dst_type'] == 'ANNUNCEMENT'
                    or $this->carrierdata['dst_type'] == 'TIMEROUTE'
                    or $this->carrierdata['dst_type'] == 'EXTEN'
                    or $this->carrierdata['dst_type'] == 'GROUP'
                    or $this->carrierdata['dst_type'] == 'VOICEMAIL'
                    or $this->carrierdata['dst_type'] == 'QUEUE'
                    or $this->carrierdata['dst_type'] == 'RINGGROUP'
                    or $this->carrierdata['dst_type'] == 'DIALCODE'
                    or $this->carrierdata['dst_type'] == 'SIPDEVICE'
                    or $this->carrierdata['dst_type'] == 'CONFERENCE'
            ) {

                $this->pbxcall = '1';
                $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"sip_h_X-ACCOUNTIDe=" . $this->carrierdata['account_id'] . "\"/>";
                $this->Gateway_XML_incoming .= "\n <action application=\"export\" data=\"sip_h_X-ACCOUNTIDe=" . $this->carrierdata['account_id'] . "\"/>";

                $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"dst_type=" . $this->carrierdata['dst_type'] . "\"/>";
                $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"dst_destination=" . $this->carrierdata['dst_destination'] . "\"/>";

                $this->Gateway_XML_incoming .= "\n <action application=\"lua\" data=\"" . PATH . "portal/application/modules/pbx/script/pbx.lua\"/>";
            } elseif ($this->carrierdata['dst_type'] == 'HANGUP') {
                $this->Gateway_XML_incoming .= "\n    <action application=\"hangup\"/>";
            } elseif ($this->carrierdata['dst_type'] == 'IP') {


                $this->endpoint_app = 'IP';
                $this->endpoint_name = $this->incomingcarrierdst;
                $this->endpoint_number = $this->incomingcarrierdst . "@" . $this->carrierdata['dst_destination'];
                $this->endpoint_extension_no = $this->incomingcarrierdst;
                $this->uuid = $this->uuid;

                $this->writelog("did_number " . $this->isthisdidcall['did_number']);

                $this->writelog("identity passcode " . $this->request['variable_sip_h_identity']);
                $this->writelog("passcode genertaed and assigned sip_h_Identity " . $this->ast);
                if (STIRSHAKEN) {
                    /* Identity header */
                    $this->callid = trim(file_get_contents('/proc/sys/kernel/random/uuid'));
                    $data = array(
                        'attest' => "A",
                        'dest' => "$this->incomingcarrierdst",
                        'origid' => "$this->callid",
                        'orig' => "$this->callernumber",
                    );

                    $send_data = str_replace('"', "'", json_encode($data, JSON_UNESCAPED_UNICODE));
                    $this->writelog("-------------- passcode genertaed and assigned " . $send_data);
                    $passcode = $this->IdentityHeader($data);
                    $this->passcode = $passcode;
                    $this->writelog("-------------- passcode genertaed and assigned " . $passcode);
                    if (strlen($passcode) > 20) {
                        $this->Gateway_XML_incoming .= "\n   <action application=\"unset\" data=\"Identity\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"sip_h_Identity=" . $passcode . "\"/>";
                        $this->writelog("-------------- passcode genertaed and assigned sip_h_Identity " . $this->ast);
                    }
                }
                $this->Gateway_XML_incoming .= "\n <action application=\"set\" data=\"sip_h_X-DSTURI=sip:" . $this->incomingcarrierdst . "@" . $this->carrierdata['dst_destination'] . "\"/>";

                $this->Gateway_XML_incoming .= "\n <action application=\"bridge\" data=\"sofia/internal/" . $this->incomingcarrierdst . "@" . $lb . "\"/>";
            } elseif ($this->carrierdata['dst_type'] == 'CUSTOMER') {
                $this->endpoint_app = 'SIPUSER';
                $this->endpoint_name = $this->incomingcarrierdst;
                $this->endpoint_number = $this->incomingcarrierdst . "@" . $lb;
                $this->endpoint_extension_no = $this->incomingcarrierdst;
                $this->uuid = $this->uuid;

                $this->Gateway_XML_incoming .= "\n <action application=\"bridge\" data=\"{ignore_early_media=true,hangup_after_bridge=false,continue_on_fail=true}[leg_timeout=" . $call_timeout . "]sofia/internal/" . $this->carrierdata['dst_destination'] . "@" . $lb . "\"/>";

                if ($this->SYSTEM_VOICEMAIL == 1) {
                    $this->Gateway_XML_incoming .= "\n<action application=\"answer\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"voicemail_greeting_number=1\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"voicemail_alternate_greet_id=" . $this->incomingcarrierdst . "\"/>";
                    $this->Gateway_XML_incoming .= "\n   <action application=\"voicemail\" data=\"default \$\${domain} " . $this->carrierdata['dst_destination'] . "\" />";
                    $this->Gateway_XML_incoming .= "\n    <action application=\"hangup\"/>";
                }
            } elseif ($this->carrierdata['dst_type'] == 'SIPDEVICE') {
                $username = '';
                $query = sprintf("SELECT username FROM customer_devices where id = '%s' limit 1;", $this->carrierdata['dst_destination']);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs = $this->resultset();
                foreach ($rs[0] as $key => $value) {
                    $username = $value;
                }


                $this->endpoint_app = 'SIPUSER';
                $this->endpoint_name = $this->incomingcarrierdst;
                $this->endpoint_number = $username . "@" . $lb;
                $this->endpoint_extension_no = '';
                $this->uuid = $this->uuid;

                if (strlen(trim($username)) > 0) {
                    $this->Gateway_XML_incoming .= "\n <action application=\"bridge\" data=\"{ignore_early_media=true,hangup_after_bridge=false,continue_on_fail=true}[leg_timeout=" . $call_timeout . "]sofia/internal/" . $username . "@" . $lb . "\"/>";
                    if ($this->SYSTEM_VOICEMAIL == 1) {
                        $this->Gateway_XML_incoming .= "\n<action application=\"answer\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"voicemail_greeting_number=1\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"voicemail_alternate_greet_id=" . $this->incomingcarrierdst . "\"/>";
                        $this->Gateway_XML_incoming .= "\n   <action application=\"voicemail\" data=\"default \$\${domain} " . $this->carrierdata['dst_destination'] . "\" />";
                        $this->Gateway_XML_incoming .= "\n    <action application=\"hangup\"/>";
                    }
                } else {
                    $this->Gateway_XML_incoming .= "\n<action application=\"answer\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"playback_terminators=none\"/>";
                    $this->Gateway_XML_incoming .= "\n<action application=\"playback\" data=\"/home/OV500/sounds/DisconnectedNumberNotice.wav\"/>";
                }
            } elseif ($this->carrierdata['dst_type'] == 'PSTN') {
                $this->account_id = $this->carrierdata['account_id'];
                $this->destination_number = $this->carrierdata['dst_destination'];
                $this->destination_number_check = $this->destination_number_user = $this->destination_number;
                $this->src_callee = $this->destination_number;
                $this->incomingtopstn = 1;
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

            if ($this->carrierdata['dst_type1'] != 'PSTN' or $this->carrierdata['dst_type2'] != 'PSTN') {
                $this->livecalls_in();
            }


            $dst_type = Array('SIPDEVICE', 'RINGGROUP', 'GROUP', 'QUEUE', 'IP', 'CUSTOMER', 'SIPURI', 'HANGUP', 'IVR', 'VOICEMESSAGE', 'ANNOUNCEMENT', 'ANNUNCEMENT', 'TIMEROUTE');
            if (in_array($this->carrierdata['dst_type'], $dst_type)) {



                if ($this->SYSTEM_VOICEMAIL == 0) {
                    if ($this->carrierdata['dst_type'] != 'IVR' and $this->carrierdata['dst_type'] != 'RINGGROUP' and $this->carrierdata['dst_type'] != 'VOICEMESSAGE' and $this->carrierdata['dst_type'] != 'ANNOUNCEMENT' and $this->carrierdata['dst_type'] != 'ANNUNCEMENT' and $this->carrierdata['dst_type'] != 'TIMEROUTE') {
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"vmextension_account=" . $this->carrierdata['account_id'] . "\" />";
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"vmextension_email=" . $this->customers['emailaddress'] . "\" />";
                        $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"vmextension_no=" . $this->incomingcarrierdst . "\" />";
                        $this->Gateway_XML_incoming .= "\n<action application=\"answer\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"voicemail_greeting_number=1\"/>";
                        $this->Gateway_XML_incoming .= "\n<action application=\"export\" data=\"voicemail_alternate_greet_id=" . $this->incomingcarrierdst . "\"/>";
                        $this->Gateway_XML_incoming .= "\n   <action application=\"voicemail\" data=\"default \$\${domain} " . $this->incomingcarrierdst . "\" />";
                    }
                }
            }
        } else {
            if ($this->carrierdata['dst_type'] != 'IVR' and $this->carrierdata['dst_type'] != 'VOICEMESSAGE' and $this->carrierdata['dst_type'] != 'ANNOUNCEMENT' and $this->carrierdata['dst_type'] != 'ANNUNCEMENT' and $this->carrierdata['dst_type'] != 'TIMEROUTE') {
                $this->Gateway_XML_incoming .= "\n<action application=\"answer\"/>";
                $this->Gateway_XML_incoming .= "\n<action application=\"set\" data=\"playback_terminators=none\"/>";
                $this->Gateway_XML_incoming .= "\n<action application=\"playback\" data=\"/home/OV500/sounds/DisconnectedNumberNotice.wav\"/>";
            }
        }

        // $this->Gateway_XML_incoming .= "\n    <action application=\"hangup\"/>";
        $this->Gateway_XML = $this->Gateway_XML_incoming;
        return;
    }

    function timezone_minute_diff($customer_timezone) {

        /*
          $currentTimeInZone1 = new DateTime("now", new DateTimeZone($customer_timezone));
          $currentTimeDiff1 = $currentTimeInZone1->format('Z');
          $currentTimeInZone2 = new DateTime("now", new DateTimeZone(SYSTEMTIMEZONE));
          $currentTimeDiff2 = $currentTimeInZone2->format('Z');
          $diff = $currentTimeDiff1 - $currentTimeDiff2;
          $sec = $diff % 60;
          $min1 = (int) ($diff / 60);
          $hr = (int) ($min1 / 60);
          $min = $min1 % 60;
          $str = $hr . ' : ' . $min . ' : ' . $sec;
          return $min1;
         */
        return 0;
    }

    function campaign_dstlist() {

        $query = '';
        $campaigns = Array();
        $customer_timezone = SYSTEMTIMEZONE;
        $query = sprintf("SELECT timezone from customers where account_id ='%s'", $this->carrierdata['account_id']);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $trs = $this->resultset();
        if (count($trs) > 0) {
            foreach ($trs as $tdata) {
                $customer_timezone = $tdata['timezone'];
            }
        }
        $minutes = '0';
        $minutes = $this->timezone_minute_diff($customer_timezone);
        if ($minutes > 0) {
            
        } else {
            $minutes = 0;
        }

        $query = sprintf("SELECT campaign_buyer.status_id, campaigns.campaign_name, campaign_buyer.buyer_name, campaign_buyer.buyer_number, campaign_buyer.ringtime, campaign_buyer.call_limits,  campaign_buyer.from_time, campaign_buyer.to_time, campaign_buyer.distributed_weightage, campaign_buyer.cc_call,  campaigns.campaign_name, campaigns.description, campaigns.campaign_id, campaigns.call_strategy,   campaigns.call_theading,   campaigns.account_id FROM campaign_buyer  INNER JOIN campaigns on campaigns.campaign_id = campaign_buyer.campaign_id WHERE campaigns.campaign_id = '%s'  ;", $this->carrierdata['dst_destination']);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs2 = $this->resultset();

        $query = sprintf("SELECT campaign_buyer.status_id, campaigns.campaign_name, campaign_buyer.buyer_name, campaign_buyer.buyer_number, campaign_buyer.ringtime, campaign_buyer.call_limits,  campaign_buyer.from_time, campaign_buyer.to_time, campaign_buyer.distributed_weightage,  campaign_buyer.cc_call,  campaigns.campaign_name, campaigns.description, campaigns.campaign_id, campaigns.call_strategy,   campaigns.call_theading,   campaigns.account_id FROM campaign_buyer  INNER JOIN campaigns on campaigns.campaign_id = campaign_buyer.campaign_id WHERE campaigns.campaign_id = '%s'    and  TIME_FORMAT(DATE_ADD(CURTIME(), INTERVAL %s minute ),'%%H:%%i:%%s')  BETWEEN from_time and to_time;", $this->carrierdata['dst_destination'], $minutes);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        $status_count = 0;
        if (count($rs) > 0) {
            foreach ($rs as $data) {
                $call_strategy = $data['call_strategy'];
                $ringtime = $data['ringtime'];
                $call_theading = $data['call_theading'];
                if ($data['status_id'] == '1') {
                    array_push($campaigns, $data);
                } else {
                    $status_count = $status_count + 1;
                }
                $data = Array();
            }
        }
        if (count($campaigns) == 0 and count($rs) > 0 and $status_count > 0) {
            $this->fs_errorcode = 'NOACTIVEBUYER';
            $this->otherinfo = "All Buyer(s) are inactive in campaign " . $this->carrierdata['dst_destination'];
            return;
        }

        if (count($rs2) > 0 and count($campaigns) == 0) {
            $this->fs_errorcode = 'OUTOFTIMERANGE';
            $this->otherinfo = " Buyer not found due to out Of time in campaign " . $this->carrierdata['dst_destination'];
            return;
        }
        if (count($rs) == 0 and $status_count > 0) {
            $this->fs_errorcode = 'NOBUYER';
            $this->otherinfo = "Buyer(s) not found in campaign " . $this->carrierdata['dst_destination'];
            return;
        }


        $campaign_after_buyer_limit = Array();

        /* This for daily Limit */
        foreach ($campaigns as $campaign) {
            $table_campaign_buyer_statistics = 'campaign_buyer_statistics_' . date('Ym');
            $query_agent_limit = sprintf(" select sum(total_calls) today_calls from %s where call_date = CURRENT_DATE()  and campaign_id = '%s' and buyer_number = '%s';", $table_campaign_buyer_statistics, $campaign['campaign_id'], $campaign['buyer_number']);
            $this->writelog($query_agent_limit);
            $this->query('SWITCH', $query_agent_limit);
            $agent_data_limit = $this->resultset();

            if (count($agent_data_limit) > 0) {
                foreach ($agent_data_limit as $agent_limit) {
                    $this->writelog($campaign['buyer_number'] . "  Buyer Today Calls " . $agent_limit['today_calls']);
                    if ($campaign['call_limits'] == 0 or $campaign['call_limits'] == '-1')
                        $campaign['call_limits'] = 10000000;

                    $campaign['today_calls'] = $agent_limit['today_calls'];
                    $campaign['callcounts'] = 0;
                    if ($agent_limit['today_calls'] < $campaign['call_limits']) {
                        $campaign['callcounts'] = $agent_limit['today_calls'];

                        array_push($campaign_after_buyer_limit, $campaign);
                    }
                }
            } else {
                $campaign['today_calls'] = 0;
                $campaign['callcounts'] = 0;
                array_push($campaign_after_buyer_limit, $campaign);
            }
        }

        if (count($campaign_after_buyer_limit) == 0) {
            $this->fs_errorcode = 'DAILYLIMITISUE';
            $this->otherinfo = "All Buyer(s) dailylimit excited in campaign " . $this->carrierdata['dst_destination'];
            return;
        }

        /* Runing call Limit */
        $campaign_after_buyer_dailylimit = Array();

        foreach ($campaign_after_buyer_limit as $campaign_limit) {
            $query_agent_limit = sprintf(" select count(id) as calllimit from livecalls where customer_src_callee = '%s' and customer_account_id = '%s' and call_flow = 'PSTN';", $campaign_limit['buyer_number'], $campaign_limit['account_id']);
            $this->writelog($query_agent_limit);
            $this->query('SWITCH', $query_agent_limit);
            $agent_data_limit = $this->resultset();
            if (count($agent_data_limit) > 0) {
                foreach ($agent_data_limit as $agent_limit) {
                    if ($campaign_limit['cc_call'] == 0 or $campaign_limit['cc_call'] == '-1')
                        $campaign_limit['cc_call'] = 1000000;
                    if ($agent_limit['calllimit'] < $campaign_limit['cc_call']) {
                        $campaign_limit['runingcalls'] = $agent_limit['calllimit'];
                        array_push($campaign_after_buyer_dailylimit, $campaign_limit);
                    }
                }
            } else {
                $campaign_limit['runingcalls'] = 0;
                array_push($campaign_after_buyer_dailylimit, $campaign_limit);
            }
        }

        if (count($campaign_after_buyer_dailylimit) == 0) {
            $this->fs_errorcode = 'BUYERLIVECALLSISSUE';
            $this->otherinfo = "All Buyer(s) live calls limit id reached in campaign " . $this->carrierdata['dst_destination'];
            return;
        }



        $this->writelog("CMP 1 -----" . str_replace('"', "'", json_encode($campaign_after_buyer_dailylimit, JSON_UNESCAPED_UNICODE)));
        /* Pri calling feature */
        if ($call_strategy == 'pri') {
            $this->orderBy($campaign_after_buyer_dailylimit, 'distributed_weightage ASC');
            if ($call_theading == '1') {
                $table_campaign_buyer_statistics = 'campaign_buyer_statistics_' . date('Ym');
                $threading = sprintf(" select buyer_number from %s  where   call_date = CURRENT_DATE() and campaign_id = '%s' and caller_id = '%s' ORDER BY create_date desc limit 1;", $table_campaign_buyer_statistics, $campaign['campaign_id'], $this->carrierdata['src_caller']);
                $this->writelog($threading);
                $this->query('SWITCH', $threading);
                $campaign_threading = $this->resultset();
                $threading_buyer_number = '';
                foreach ($campaign_threading as $campaignThreading) {
                    $threading_buyer_number = $campaignThreading['buyer_number'];
                }
                if (strlen(trim($threading_buyer_number)) > 0) {
                    $threading_campaign_buyer = Array();
                    $threading_campaign_after_buyer_dailylimit = $campaign_after_buyer_dailylimit;
                    foreach ($threading_campaign_after_buyer_dailylimit as $campaign) {
                        if ($campaign['buyer_number'] == $threading_buyer_number)
                            $campaign['distributed_weightage'] = 0;
                        array_push($threading_campaign_buyer, $campaign);
                    }
                }
                $this->orderBy($threading_campaign_buyer, 'distributed_weightage ASC');
                if (count($threading_campaign_buyer) > 0)
                    $campaign_after_buyer_dailylimit = $threading_campaign_buyer;
                else
                    $campaign_after_buyer_dailylimit = $campaign_after_buyer_dailylimit;
            }
            $this->writelog("CMP -----2 " . str_replace('"', "'", json_encode($campaign_after_buyer_dailylimit, JSON_UNESCAPED_UNICODE)));
        } else {
            /* loabbalance calling feature */
            $this->orderBy($campaign_after_buyer_dailylimit, 'distributed_weightage ASC');
            $routing_data = $campaign_after_buyer_dailylimit;
            // percentage array
            $p_data = ARRAY();
            // percentage gateway array
            $p_routinglist = ARRAY();
            // total percentage data
            $t_p_data = 0;
            $this->writelog("route_routing_lb in : - " . str_replace('"', "'", json_encode($data)));
            $routing_list = array();

            foreach ($routing_data as $route) {
                $allow = 0;
                $buyer_number = $route['buyer_number'];
                if (!in_array($buyer_number, $routing_list)) {
                    array_push($p_data, $route);
                    $tmpstr = array('buyer_number' => $route['buyer_number'], 'percentage' => $route['distributed_weightage'], 'callcounts' => $route['callcounts'], 'runingcalls' => $route['runingcalls']);
                    array_push($p_routinglist, $tmpstr);
                    array_push($routing_list, $buyer_number);
                    $t_p_data = $t_p_data + $route['distributed_weightage'];
                }

                $p_routinglist1 = $p_routinglist;
                $routingid_data = Array();
                if (count($p_routinglist) > 0) {
                    $r_call_count = 0;
                    foreach ($p_routinglist as $r_call) {
                        /* LB Based on Processed calls */
                        // $callcounts = $r_call['callcounts'];
                        /* LB Based on Live runing calls */
                        $callcounts = $r_call['runingcalls'];

                        $this->writelog("Running Calls - " . $r_call['buyer_number'] . " - " . $callcounts);
                        $r_call_count = $r_call_count + $callcounts;
                        $tmpstr1 = array('buyer_number' => $r_call['buyer_number'], 'calls' => $callcounts);
                        array_push($routingid_data, $tmpstr1);
                    }
                }
                foreach ($p_routinglist1 as $data) {
                    $per = $data['percentage'] * 100 / $t_p_data;
                    $percentage[$data['buyer_number']] = $per;
                }
                foreach ($routingid_data as $rdata) {
                    $rper = $rdata['calls'] * 100 / $r_call_count;
                    $rpercentage[$rdata['buyer_number']] = $rper;
                    $rcalls[$rdata['buyer_number']] = $rdata['calls'];
                }
                $p_data_next = Array();
                foreach ($p_data as $data_n) {
                    if ($rpercentage[$data_n['buyer_number']] > 0)
                        $data_n['running_percentage'] = $rpercentage[$data_n['buyer_number']];
                    else
                        $data_n['running_percentage'] = 0;
                    $data_n['rcalls'] = $rcalls[$data_n['buyer_number']];
                    $data_n['req_percentage'] = $percentage[$data_n['buyer_number']];
                    array_push($p_data_next, $data_n);
                }
                $pg_data = Array();
                $routlist = Array();
                foreach ($p_data_next as $data) {
                    $data['rpriority'] = 1000;
                    if (!in_array($data['buyer_number'], $routlist)) {

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
                    array_push($routlist, $data['buyer_number']);
                    array_push($pg_data, $data);
                }
                $this->orderBy($pg_data, 'priority ASC, rpriority ASC, running_percentage ASC');
                $campaign_after_buyer_dailylimit = $pg_data;
            }
        }

        $this->writelog("CMP -----3 " . str_replace('"', "'", json_encode($campaign_after_buyer_dailylimit, JSON_UNESCAPED_UNICODE)));

        foreach ($campaign_after_buyer_dailylimit as $key => $value) {
            $this->writelog($key . " " . $value);
        }
        return $campaign_after_buyer_dailylimit;
    }

    function DID_user($diddata, $incomingcarrierdst, $callernumber) {
        $user = $diddata['account_id'];
        $this->account_id = $user;
        $this->CCSTRING = $diddata['account_id'];
        $query = sprintf("SELECT max_callduration, round_logic, account.account_id, status_id account_status, account.account_type, parent_account_id, dp, customer_voipminuts.tariff_id, account_cc, account_cps, tax1, tax2, tax3, tax_type, currency_id, cli_check, dialpattern_check, llr_check, account_codecs, media_transcoding  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id where account.account_id = '%s';", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
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


        $query = sprintf("SELECT account_id, pstn_cli_usage_option, pstn_max_calls_per_cli_in_aday, pstn_max_call_per_cli_live,  pstn_max_cli_length, pstn_min_cli_length, pstn_cli_malfunction, pstn_min_dst_number_length_option, pstn_min_dst_number_length, pstn_max_dst_number_length, did_cli_usage_option, did_max_calls_per_cli_in_aday, did_max_call_per_cli_live, did_max_cli_length, did_min_cli_length from account_cli_dst_rules where account_id = '%s' limit 1;", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
            }
        }






        if ($this->customers['did_cli_usage_option'] != '1') {
            $this->DID_CLIUSAGE();
            if ($this->status == 'FAIL') {
                $otherinfo = $user . " CallerID usage Limit is executed ";
                $this->writelog($otherinfo);
                $this->status = 'FAIL';
                $cause = 'CLILENISSUE';
                $this->fail_route_xml($cause, $otherinfo);
            }
        }



        $this->timeout = $this->customers['max_callduration'] * 60;

        if ($this->carrierdata['dst_type'] == 'IP') {
            $this->customers['device_type'] = 'ip';

            $query = sprintf("SELECT customer_ips.id, customer_ips.ipaddress, customer_ips.account_id, customer_ips.ip_status, customer_ips.ip_cc, customer_ips.ip_cps from customer_ips  INNER JOIN account on customer_ips.account_id = account.account_id where ipaddress = '%s'  and account.account_id = '%s' limit 1;", $user);

            $this->customers['device_id'] = str_replace('.', "", json_encode($this->customers['dst_destination'], JSON_UNESCAPED_UNICODE));
        } elseif ($this->carrierdata['dst_type'] == 'CUSTOMER') {
            $this->customers['device_type'] = 'u';

            $query = sprintf("SELECT account_ips.id, account_ips.ipaddress, account_ips.account_id, account_ips.ip_status, account_ips.ip_cc, account_ips.ip_cps from account_ips  INNER JOIN account on account_ips.account_id = account.account_id where ipaddress = '%s'  and account.account_id = '%s' limit 1;", $user);

            $this->customers['device_id'] = str_replace('.', "", json_encode($this->customers['dst_destination']));
        }


        $query = sprintf("select company_name , name  from customers where account_id = '%s';", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->customers[$key] = preg_replace("/[^A-Za-z0-9_ ()]/", '', $value);
        }

        $this->customers['src_caller'] = $this->caller_number;
        $this->customers['src_callee'] = $this->carrierdata['dst_destination'];
        $this->customers['src_ip'] = $this->customers['ipaddress'];
        $this->customers['ipaddress'] = $this->customers['ipaddress'];
        $this->customers['didcallflow'] = 'DID';
        /*
         * User status check
         */
        $this->customers['account_id'] = $user;

        $otherinfo = $this->account_id;
        if ($this->customers['account_status'] == 0) {
            $this->otherinfo = $diddata['account_id'] . " customer account is inactive";
            $this->fail_route_xml_inbound('USERINACTIVE', $otherinfo);
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));

            return;
        }

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

                $this->otherinfo = $diddata['account_id'] . " customer caller ID " . $callernumber . " is not allowed";
                $this->fail_route_xml_inbound('USERCLI', $otherinfo);
                $this->status = 'FAIL';
                $this->customersdata['user'] = $this->customers;
                $this->rates_incoming = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));

                return;
            }
        }
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
        if ($this->customers['balance'] < 0) {

            $this->otherinfo = $diddata['account_id'] . " customer balance is low " . $this->customers['balance'];
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

            $this->otherinfo = "DID call rate is missing in " . $this->customers['tariff_id'] . " for " . $this->destination_number;
            $this->fail_route_xml_inbound('USERDIDRATE', $otherinfo);
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

            $this->otherinfo = "Customer " . $this->customers['tariff_id'] . " tariff is inactive";
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
            $this->otherinfo = $this->destination_number . " DID call rate is Inactive in " . $this->customers['tariff_id'] . " tariff ";
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


        $this->customers['bundle_type_minute'] = 'MINUTE';
        $this->customers['bundle_value_minute'] = 0;
        $query = sprintf("SELECT * from customer_bundle_sdr INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id  where customer_bundle_sdr.account_id  = '%s' and  (%s) and LENGTH(prefix) > 0  and bundle_for = 'IN'   GROUP BY customer_bundle_sdr.bundle_package_id order by prefix desc;", $user, $str);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {

            $available_bundle_minute = 0;
            $available_bundle_cost = 0;
            foreach ($rs as $data) {
                $query = sprintf("SELECT account_id, bundle_type,    sum(total_allowed_sec) as  total_allowed_sec , sum(sdr_consumption) as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s'  and  (total_allowed_sec  - sdr_consumption   > 0 )  and bundle_for = 'IN'    ;", $user, $data['bundle_package_id'], date("Y-m-d"));
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs3 = $this->resultset();
                $available_bundle1 = 0;
                $available_bundle2 = 0;
                if (count($rs3) > 0) {
                    foreach ($rs3 as $data_b) {
                        if ($data_b['bundle_type'] == 'MINUTE') {
                            $available_bundle1 = $data_b['total_allowed_sec'] - $data_b['sdr_consumption'];
                            $this->writelog("available minutes $available_bundle1");
                            $available_bundle_minute = $available_bundle_minute + $available_bundle1;
                            $this->writelog("available total minutes $available_bundle_minute");
                            $this->customers['bundle_type_minute'] = 'MINUTE';
                            $this->customers['bundle_value_minute'] = $available_bundle_minute;
                            $this->customers['bundle_prefix_number'] = $this->destination_number;
                        }
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

        if ($balance_b > 0 AND $this->customers['bundle_type_cost'] == 'COST') {
            $this->customers['balance'] = $this->customers['balance'] + $this->customers['bundle_value_cost'];
        }

        $this->customers['duration'] = $this->duration($this->customers);
        $this->writelog("Normal duration " . $this->customers['duration']);
        $this->writelog("Bundle cost " . $this->customers['bundle_type_cost']);

        $this->writelog("Normal duration " . $this->customers['duration']);

        /*
         * Call Duration is zero. maybe rates are higher compared to balance
         */
        $otherinfo = $this->account_id;
        if ($this->customers['duration'] == 0) {
            $this->fail_route_xml_inbound('USERBALANCE', $otherinfo);
            $this->status = 'FAIL';
            $this->otherinfo = $diddata['account_id'] . " customer balance is low " . $this->customers['balance'];
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
                $this->otherinfo = $dialnumber1 . " number dialing is blocked in customer account";
                return;
            }
        }

        if (count($this->account_dialpattern) == 0) {
            /*
             * Dialed number pattern is black.
             */
            $otherinfo = $this->account_id;
            $this->fail_route_xml_inbound('USERDIALEDNUMBER', $otherinfo);

            $this->otherinfo = $dialnumber1 . " number dialing is pattert not allowd in customer account";
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
                $this->otherinfo = $dialnumber1 . " number dialing is blocked in customer account";
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

            $this->otherinfo = $reseller_id . " reseller account is inactive";
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

                $this->otherinfo = $reseller_id . " reseller caller ID " . $callernumber . " is not allowed";
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
        if ($resellerusers['balance'] < 0) {
            $this->fail_route_xml_inbound('RESELLERBALANCE', $otherinfo);
            $this->status = 'FAIL';

            $this->otherinfo = $reseller_id . " reseller balance is low " . $resellerusers['balance'];

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

        $query = sprintf("SELECT tariff.tariff_status, tariff_ratecard_map.id, tariff_ratecard_map.ratecard_id, tariff_ratecard_map.tariff_id, tariff_ratecard_map.start_day, tariff_ratecard_map.end_day, tariff_ratecard_map.start_time, tariff_ratecard_map.end_time, customer_rates.prefix,  REPLACE(REPLACE( customer_rates.destination,',',' '),'-',' ') as destination   , customer_rates.rate, customer_rates.connection_charge, customer_rates.minimal_time, customer_rates.resolution_time, customer_rates.grace_period, customer_rates.rate_multiplier, customer_rates.rate_addition, customer_rates.rates_status, tariff.tariff_currency_id, tariff.tariff_currency_id  FROM tariff_ratecard_map  INNER JOIN customer_rates on customer_rates.ratecard_id = tariff_ratecard_map.ratecard_id    INNER JOIN ratecard on customer_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'INCOMING' INNER JOIN tariff on  tariff.tariff_id = tariff_ratecard_map.tariff_id where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and tariff_ratecard_map.tariff_id = '%s' and (%s) ORDER BY priority asc, prefix desc, rate ASC, end_time ASC limit 1;", $resellerusers['tariff_id'], $str);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $resellerusers[$key] = $value;
        }


        /*
         * Bundle & Plan
         */


        $resellerusers['bundle_type_cost'] = 'COST';
        $resellerusers['bundle_value_cost'] = 0;

        $query = sprintf("SELECT * from customer_bundle_sdr INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id  where customer_bundle_sdr.account_id  = '%s' and  (%s) and LENGTH(prefix) > 0  and bundle_for = 'IN' GROUP BY customer_bundle_sdr.bundle_package_id order by prefix desc;", $reseller_id, $str);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {

            $available_bundle_minute = 0;
            $available_bundle_cost = 0;
            foreach ($rs as $data) {
                $query = sprintf("SELECT account_id, bundle_type,    sum(total_allowed_sec) as  total_allowed_sec , sum(sdr_consumption) as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s'  and  (total_allowed_sec  - sdr_consumption   > 0 )  and bundle_for = 'IN';", $user, $data['bundle_package_id'], date("Y-m-d"));
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs3 = $this->resultset();
                $available_bundle1 = 0;
                $available_bundle2 = 0;
                if (count($rs3) > 0) {
                    foreach ($rs3 as $data_b) {
                        if ($data_b['bundle_type'] == 'MINUTE') {
                            $available_bundle1 = $data_b['total_allowed_sec'] - $data_b['sdr_consumption'];
                            $this->writelog("available minutes $available_bundle1");
                            $available_bundle_minute = $available_bundle_minute + $available_bundle1;
                            $this->writelog("available total minutes $available_bundle_minute");
                            $resellerusers['bundle_type_minute'] = 'MINUTE';
                            $resellerusers['bundle_value_minute'] = $available_bundle_minute;

                            $resellerusers['bundle_prefix_number'] = $incomingcarrierdst;
                        }
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
            $this->otherinfo = $reseller_id . " reseller buy rate is  " . $llr . $resellerusers['currency_id'] . " and sale rates is " . $this->LLRRates . $resellerusers['currency_id'];
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

    function PSTN_customer_random_cli() {

        $query = sprintf("SELECT LENGTH(destination_prefix) lndata,  rule_id, rule_name, account_id, destination_prefix, cli_fixprefix, cli_length, cli_status   FROM randomcli_cust where account_id = '%s' and '%s' like concat(destination_prefix,'%%') and cli_status = '1' ORDER BY lndata desc , rand() limit 1;", $this->account_id, $this->destination_number_org);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $data = $this->resultset();

        if (count($data[0]) > 0) {
            $caller = $data[0];
            $sub_lenth = strlen(trim($caller['cli_fixprefix']));
            $gen_length = $caller['cli_length'];
            if ($gen_length > 0) {
                $newnumber = $this->random_number_cli($gen_length);
                $org_callernumber = $caller['cli_fixprefix'] . $newnumber;
                $this->writelog("New CLI $org_callernumber");
                return $org_callernumber;
            } elseif (strlen(trim($carrier_caller['cli_fixprefix'])) > 0) {
                $org_callernumber = $carrier_caller['cli_fixprefix'];
                $this->writelog("New cli_fixprefix  $org_callernumber");
                return $org_callernumber;
            }
        }
        return '';
    }

    function random_number_cli($length) {
        return join('', array_map(function ($value) {
                    return $value == 1 ? mt_rand(1, 9) : mt_rand(0, 9);
                }, range(1, $length)));
    }

    function DID_CLIUSAGE() {

        if ($this->customers['did_cli_usage_option'] != '1') {
            $this->status = 'OK';
            return;
        }
        $user_cli = $this->caller_number;
        if ($this->customers['did_max_cli_length'] < strlen($user_cli)) {
            $otherinfo = "Customer " . $this->customers['account_id'] . " and " . $user_cli . " CLI max length issue";
            $this->writelog($otherinfo);
            $this->otherissue = $otherinfo;
            $this->cause = 'CLILENISSUE';
            $this->fail_route_xml('CLILENISSUE', $otherinfo);
            $this->status = 'FAIL';
            return;
        }
        if ($this->customers['did_min_cli_length'] > strlen($user_cli)) {
            $otherinfo = "Customer " . $this->customers['account_id'] . " and " . $user_cli . " CLI min length issue";
            $this->writelog($otherinfo);
            $this->otherissue = $otherinfo;
            $this->cause = 'CLILENISSUE';
            $this->fail_route_xml('CLILENISSUE', $otherinfo);
            $this->status = 'FAIL';
            return;
        }

        $table = date('Ym') . '_didcalls';
        $query = sprintf("select count(total_call_count) totalcalls from %s  where   call_date = CURDATE()  and account_id = '%s' and ( carrier_dst_caller = '%s' or customer_src_caller = '%s' ); );", $table, $this->customers['account_id'], $user_cli, $user_cli);
        $this->writelog($query);
        $this->dbcdr->query($query);
        $rs = $this->dbcdr->resultset();
        foreach ($rs[0] as $key => $value) {
            $totalcalls = $value;
        }

        if ($totalcalls >= $this->customers['did_max_calls_per_cli_in_aday']) {
            $otherinfo = "Customer " . $this->customers['account_id'] . " and " . $user_cli . " usage limit executed";
            $this->writelog($otherinfo);
            $this->otherissue = $otherinfo;
            $this->cause = 'CLILENISSUE';
            $this->fail_route_xml('CLILENISSUE', $otherinfo);
            $this->status = 'FAIL';
            return;
        }
        $query = sprintf("select count(id) totalcalls  from livecalls   where  call_flow = 'DID' and  customer_account_id  = '%s'  and ( customer_src_caller = '%s' or carrier_dst_caller = '%s');", $this->customers['account_id'], $user_cli, $user_cli);
        $this->writelog($query);
        $this->db->query($query);
        $rs = $this->db->resultset();
        foreach ($rs[0] as $key => $value) {
            $totalcalls = $value;
        }
        if ($totalcalls >= $this->customers['did_max_call_per_cli_live']) {
            $otherinfo = "Customer " . $this->customers['account_id'] . " and " . $user_cli . "  limit executed";
            $this->writelog($otherinfo);
            $this->otherissue = $otherinfo;
            $this->cause = 'CLILENISSUE';
            $this->fail_route_xml('CLILENISSUE', $otherinfo);
            $this->status = 'FAIL';
            return;
        }
    }

    function PSTN_CLIUSAGE() {

        $this->caller_number;
        $this->callernumber_user;
        $user_cli = $this->request['Caller-Orig-Caller-ID-Number'];
        $user_cli = $this->request['Caller-Orig-Caller-ID-Number'];
        $route_callid = $this->callernumber_user;
        if ($this->customers['pstn_max_cli_length'] < strlen($route_callid)) {
            $otherinfo = "Customer " . $this->customers['account_id'] . " and " . $route_callid . " CLI max length issue";
            $this->writelog($otherinfo);
            $this->otherissue = $otherinfo;
            $this->cause = 'CLILENISSUE';
            $this->fail_route_xml('CLILENISSUE', $otherinfo);
            $this->status = 'FAIL';
            return;
        }

        if ($this->customers['pstn_min_cli_length'] > strlen($route_callid)) {
            $otherinfo = "Customer " . $this->customers['account_id'] . " and " . $route_callid . " CLI min length issue";
            $this->writelog($otherinfo);
            $this->otherissue = $otherinfo;
            $this->cause = 'CLILENISSUE';
            $this->fail_route_xml('CLILENISSUE', $otherinfo);
            $this->status = 'FAIL';
            return;
        }

        $table = date('Ym') . '_pstncalls';
        $query = sprintf("select count(total_call_count) totalcalls from %s  where call_date = CURDATE() and  account_id = '%s' and ( carrier_dst_caller = '%s' or customer_src_caller = '%s' ); );", $table, $this->customers['account_id'], $route_callid, $route_callid);
        $this->writelog($query);
        $this->dbcdr->query($query);
        $rs = $this->dbcdr->resultset();
        foreach ($rs[0] as $key => $value) {
            $totalcalls = $value;
        }
        if ($totalcalls >= $this->customers['pstn_max_calls_per_cli_in_aday']) {
            $otherinfo = "Customer " . $this->customers['account_id'] . " and " . $route_callid . " usage limit executed";
            $this->writelog($otherinfo);
            $this->otherissue = $otherinfo;
            $this->cause = 'CLILENISSUE';
            $this->fail_route_xml('CLILENISSUE', $otherinfo);
            $this->status = 'FAIL';
            return;
        }

        $query = sprintf("select count(id) totalcalls  from livecalls   where   call_flow = 'PSTN' and   customer_account_id  = '%s'  and ( customer_src_caller = '%s' or carrier_dst_caller = '%s');", $this->customers['account_id'], $route_callid, $route_callid);
        $this->writelog($query);
        $this->db->query($query);
        $rs = $this->db->resultset();
        foreach ($rs[0] as $key => $value) {
            $totalcalls = $value;
        }


        if ($totalcalls >= $this->customers['pstn_max_call_per_cli_live']) {
            $otherinfo = "Customer " . $this->customers['account_id'] . " and " . $route_callid . "  limit executed";
            $this->writelog($otherinfo);
            $this->otherissue = $otherinfo;
            $this->cause = 'CLILENISSUE';
            $this->fail_route_xml('CLILENISSUE', $otherinfo);
            $this->status = 'FAIL';
            return;
        }
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

        if (strlen($this->extension_src_id) > 0) {
            $query = sprintf("SELECT extension_no, name as extension_name,  extension_id, caller_id , call_recording from customer_devices  where account_id = '%s' and id = '%s';", $user, $this->extension_src_id);

            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
            }
        }


        if ($this->customers['call_recording'] == '1') {
            $this->recording_status = '1';
            $this->recordingxml = '0';
        }


        /* this is code for dialed number and cli controling process */
        $query = sprintf("SELECT account_id, pstn_cli_usage_option, pstn_max_calls_per_cli_in_aday, pstn_max_call_per_cli_live,  pstn_max_cli_length, pstn_min_cli_length, pstn_cli_malfunction, pstn_min_dst_number_length_option, pstn_min_dst_number_length, pstn_max_dst_number_length  from account_cli_dst_rules where account_id = '%s' limit 1;", $user);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
            }
        }
        if ($this->customers['pstn_cli_usage_option'] == '1') {
            $this->customers['pstn_max_calls_per_cli_in_aday'];
            $this->customers['pstn_max_call_per_cli_live'];
            $this->customers['pstn_max_cli_length'];
            $this->customers['pstn_min_cli_length'];
            $this->customers['pstn_cli_malfunction'];
            $user_cli = $this->request['Caller-Orig-Caller-ID-Number'];
            if ($this->customers['pstn_max_cli_length'] == $this->customers['pstn_min_cli_length']) {
                if ($this->customers['pstn_max_cli_length'] != strlen(trim($user_cli)) or $this->customers['pstn_max_cli_length'] != strlen(trim($this->callernumber_user)) or $this->customers['pstn_min_cli_length'] != strlen(trim($this->callernumber))) {
                    $this->writelog(" Customer " . $user . " and " . $user_cli . " ANI length issue");
                    $otherinfo = " Customer " . $user . " and " . $user_cli . " ANI length issue";
                    $this->fail_route_xml('CLILENISSUE', $otherinfo);
                }
            } else {
                if ($this->customers['pstn_max_cli_length'] < strlen(trim($user_cli))) {
                    $this->writelog(" Customer " . $user . " and " . $user_cli . " ANI long length issue");
                    $otherinfo = " Customer " . $user . " and " . $user_cli . " ANI long length issue";
                    $this->fail_route_xml('CLILENISSUE', $otherinfo);
                }
                if ($this->customers['pstn_max_cli_length'] < strlen(trim($this->callernumber_user))) {
                    $this->writelog(" Customer " . $user . " and " . $this->callernumber_user . " ANI long length issue");
                    $otherinfo = " Customer " . $user . " and " . $this->callernumber_user . " ANI long length issue";
                    $this->fail_route_xml('CLILENISSUE', $otherinfo);
                }
                if ($this->customers['pstn_max_cli_length'] < strlen(trim($this->callernumber))) {
                    $this->writelog(" Customer " . $user . " and " . $this->callernumber . " ANI long length issue");
                    $otherinfo = " Customer " . $user . " and " . $this->callernumber . " ANI long length issue";
                    $this->fail_route_xml('CLILENISSUE', $otherinfo);
                }
                if ($this->customers['pstn_max_cli_length'] > strlen(trim($user_cli))) {
                    $this->writelog(" Customer " . $user . " and " . $user_cli . " ANI short length issue");
                    $otherinfo = " Customer " . $user . " and " . $user_cli . " ANI short length issue";
                    $this->fail_route_xml('CLILENISSUE', $otherinfo);
                }
                if ($this->customers['pstn_min_cli_length'] > strlen(trim($this->callernumber_user))) {
                    $this->writelog(" Customer " . $user . " and " . $this->callernumber_user . " ANI short length issue");
                    $otherinfo = " Customer " . $user . " and " . $this->callernumber_user . " ANI short length issue";
                    $this->fail_route_xml('CLILENISSUE', $otherinfo);
                }
                if ($this->customers['pstn_min_cli_length'] > strlen(trim($this->callernumber))) {
                    $this->writelog(" Customer " . $user . " and " . $this->callernumber . " ANI short length issue");
                    $otherinfo = " Customer " . $user . " and " . $this->callernumber . " ANI short length issue";
                    $this->fail_route_xml('CLILENISSUE', $otherinfo);
                }
                $this->PSTN_CLIUSAGE();
                if ($this->status == 'FAIL') {
                    $otherinfo = $this->user_account_id . " callerID usage Limit is executed " . $this->otherissue;
                    $this->writelog($otherinfo);
                    $this->status = 'FAIL';
                    $cause = 'CLILENISSUE';
                    $this->fail_route_xml($cause, $otherinfo);
                }
            }
            if ($this->customers['pstn_min_dst_number_length_option'] == '1') {
                if ($this->customers['pstn_min_dst_number_length'] == $this->customers['pstn_max_dst_number_length']) {
                    if ($this->customers['pstn_min_dst_number_length'] != strlen(trim($this->destination_number)) or $this->customers['pstn_min_dst_number_length'] != strlen(trim($this->destination_number)) or $this->customers['pstn_max_dst_number_length'] != strlen(trim($this->destination_number))) {
                        $otherinfo = " Customer " . $user . " and " . $this->destination_number . " Dst length issue";
                        $this->writelog($otherinfo);
                        $this->fail_route_xml('CLILENISSUE', $otherinfo);
                        $this->status = 'FAIL';
                    }
                } else {
                    if ($this->customers['pstn_min_dst_number_length'] > strlen(trim($this->destination_number))) {
                        $this->writelog(" Customer " . $user . " and " . $this->destination_number . " DST short length issue");
                        $otherinfo = " Customer " . $user . " and " . $this->destination_number . " DST short length issue";
                        $this->fail_route_xml('CLILENISSUE', $otherinfo);
                    }
                    if ($this->customers['pstn_min_cli_length'] < strlen(trim($this->destination_number))) {
                        $this->writelog(" Customer " . $user . " and " . $this->destination_number . " DST long length issue");
                        $otherinfo = " Customer " . $user . " and " . $this->destination_number . " DST long length issue";
                        $this->fail_route_xml('CLILENISSUE', $otherinfo);
                    }
                }
            }
        }
        /* end */

        $sipuser = $this->request['Hunt-Username'];
        $query = sprintf(" SELECT extension_no, extension_id, extensionplan_package_id, caller_id from customer_devices where username = '%s' and account_id = '%s';", $sipuser, $user);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $this->customers[$key] = $value;
        }
        $extension_id = $this->customers['extension_id'];
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
        if (strlen(trim($this->customers['caller_id'])) > 0)
            $this->callernumber = $this->caller_number = trim($this->customers['caller_id']);
        $this->customers['src_caller'] = $this->src_caller;
        $this->customers['pstncallflow'] = 'PSTN';
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
            $this->otherinfo = $this->account_id . " Customer is inactive";
            if ($this->incomingtopstn == 1) {
                $carrier = Array();
                $user = $this->customersdata;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERINACTIVE';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
            }
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

                $this->otherinfo = $this->account_id . " Customer calls incoming codecs issue";
                $this->fail_route_xml('CODECNOTALLOWED', $otherinfo);
                $this->status = 'FAIL';
                $this->customersdata['user'] = $this->customers;
                if ($this->incomingtopstn == 1) {
                    $carrier = Array();
                    $user = $this->customersdata;
                    $user_inbound = $this->rates_incoming;
                    $carrier_inbound = $this->carrierdata;
                    $this->fserror = 'CODECNOTALLOWED';
                    $fserror = $this->fserror;
                    $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                }
                return;
            }
        }

        $this->internalcall();
        if ($this->is_internalcall == '0') {

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

                    $this->otherinfo = $this->account_id . " Customer calls caller ID " . $this->callernumber . " is not allowed";
                    if ($this->incomingtopstn == 1) {
                        $carrier = Array();
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'USERCLI';
                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    } return;
                }
            }
            $org_callernumber = $this->PSTN_customer_random_cli();
            if (strlen(trim($org_callernumber)) > 1) {
                $this->callernumber = $org_callernumber;
            }


            /*
             * Checking CLI based on Destination when this option enabled
             */
            if ($this->customers['force_dst_src_cli_prefix'] == '1') {
                $this->force_dst_src_cli_prefix();
            }
        }
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

        if ($this->customers['balance'] < 0) {
            $sql = sprintf("update account set status_id = '-2' where account_id = '%s';", $user);
            $this->query('SWITCH', $sql);
            $this->execute();
        }

        if ($this->customers['balance'] < 0) {
            $this->fail_route_xml('USERBALANCE', $otherinfo);
            $this->status = 'FAIL';

            $this->otherinfo = $this->account_id . " Customer balance is low " . $this->customers['balance'];

            $this->customersdata['user'] = $this->customers;
            if ($this->incomingtopstn == 1) {
                $carrier = Array();
                $user = $this->customersdata;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERBALANCE';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
            }
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
        if ($this->customers['balance'] < 0) {
            $this->fail_route_xml('USERBALANCE', $otherinfo);
            $this->otherinfo = $this->account_id . " Customer balance is low " . $this->customers['balance'];
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            if ($this->incomingtopstn == 1) {
                $carrier = Array();
                $user = $this->customersdata;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERBALANCE';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
            }
            return;
        }

        $this->vmaccess_internalcall();
        if ($this->is_vmaccess_internalcall == '0') {
            $this->internalcall();
            if ($this->is_internalcall == '0') {
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
                $query = sprintf("SELECT tariff.tariff_status,  tariff_ratecard_map.ratecard_id, tariff_ratecard_map.tariff_id, tariff_ratecard_map.start_day, tariff_ratecard_map.end_day, tariff_ratecard_map.start_time, tariff_ratecard_map.end_time, customer_rates.prefix,   REPLACE(REPLACE( customer_rates.destination,',',' '),'-',' ') as destination, customer_rates.rate, customer_rates.connection_charge, customer_rates.minimal_time, customer_rates.resolution_time, customer_rates.grace_period, customer_rates.rate_multiplier, customer_rates.rate_addition, customer_rates.rates_status, tariff.tariff_currency_id, tariff.tariff_currency_id  FROM tariff_ratecard_map  INNER JOIN customer_rates on customer_rates.ratecard_id = tariff_ratecard_map.ratecard_id    INNER JOIN ratecard on customer_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'OUTGOING' INNER JOIN tariff on  tariff.tariff_id = tariff_ratecard_map.tariff_id where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and tariff_ratecard_map.tariff_id = '%s' and (%s)  ORDER BY priority asc, prefix desc, rate ASC, end_time ASC limit 1;", $this->customers['tariff_id'], $str);
                $this->writelog("PSTN_CALL   " . $query);
                $this->query('SWITCH', $query);
                $rs = $this->resultset();
                /*
                 * if user rates is not available
                 */

                $otherinfo = $this->account_id;
                if (count($rs) == 0) {

                    $this->otherinfo = $this->account_id . " Customer " . $this->customers['tariff_id'] . " is missing rates for " . $this->billing_destination_number . " number";
                    $this->fail_route_xml('USERRATE', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customers['src_callee'] = $this->destination_number;
                    $this->customersdata['user'] = $this->customers;

                    $this->otherinfo = $this->billing_destination_number . " Dialed number rates missing in " . $this->customers['tariff_id'] . " tariff";

                    if ($this->incomingtopstn == 1) {
                        $carrier = Array();
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'USERRATE';

                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    }
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


                $this->customers['bundle_type_minute'] = 'MINUTE';
                $this->customers['bundle_value_minute'] = 0;
                $query = sprintf("SELECT * from customer_bundle_sdr INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id  where customer_bundle_sdr.account_id  = '%s' and  (%s) and LENGTH(prefix) > 0 and bundle_for = 'OUT'   GROUP BY customer_bundle_sdr.bundle_package_id order by prefix desc;", $user, $str);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs = $this->resultset();
                if (count($rs) > 0) {

                    $available_bundle_minute = 0;
                    $available_bundle_cost = 0;
                    foreach ($rs as $data) {
                        $query = sprintf("SELECT account_id, bundle_type,    sum(total_allowed_sec) as  total_allowed_sec , sum(sdr_consumption) as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s'  and  (total_allowed_sec  - sdr_consumption   > 0 )   and bundle_for = 'OUT'  ;", $user, $data['bundle_package_id'], date("Y-m-d"));
                        $this->writelog($query);
                        $this->query('SWITCH', $query);
                        $rs3 = $this->resultset();
                        $available_bundle1 = 0;
                        $available_bundle2 = 0;
                        if (count($rs3) > 0) {
                            foreach ($rs3 as $data_b) {
                                if ($data_b['bundle_type'] == 'MINUTE') {
                                    $available_bundle1 = $data_b['total_allowed_sec'] - $data_b['sdr_consumption'];
                                    $this->writelog("available minutes $available_bundle1");
                                    $available_bundle_minute = $available_bundle_minute + $available_bundle1;
                                    $this->writelog("available total minutes $available_bundle_minute");
                                    $this->customers['bundle_type_minute'] = 'MINUTE';
                                    $this->customers['bundle_value_minute'] = $available_bundle_minute;

                                    $this->customers['bundle_prefix_number'] = $this->billing_destination_number;
                                }
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

                    $this->otherinfo = $this->account_id . " Customer " . $this->customers['tariff_id'] . " tariff currency is missmatch";
                    $this->fail_route_xml('USERTARIFFCURRENCY', $otherinfo);
                    $this->status = 'FAIL';
                    $this->customersdata['user'] = $this->customers;
                    if ($this->incomingtopstn == 1) {
                        $carrier = Array();
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'USERTARIFFCURRENCY';
                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    }
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

                    $this->otherinfo = $this->account_id . " Customer " . $this->customers['tariff_id'] . " tariff is inactive";
                    if ($this->incomingtopstn == 1) {
                        $carrier = Array();
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'USERTARIFFINACTIVE';
                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    } return;
                }

                /*
                 * User rates is blocked
                 */
                $otherinfo = $this->account_id;
                if ($this->customers['rates_status'] != '1') {
                    $this->fail_route_xml('USERRATEBLOCKED', $otherinfo);
                    $this->status = 'FAIL';

                    $this->otherinfo = $this->account_id . " Customer " . $this->customers['tariff_id'] . " rates is blocked for " . $this->billing_destination_number . " number";

                    $this->customersdata['user'] = $this->customers;
                    if ($this->incomingtopstn == 1) {
                        $carrier = Array();
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'USERRATEBLOCKED';
                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    }
                    return;
                }


                /*
                 * Building the users billing infomation Array which will pass in CDR event
                 */
            }
        }


        if ($this->customers['extension_no'] > 0) {
            $this->customers['src_caller'] = $this->customers['extension_no'];
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

                    $this->otherinfo = $this->account_id . " Customer CC limit issue";
                    $this->status = 'FAIL';
                    if ($this->incomingtopstn == 1) {
                        $carrier = Array();
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'USERCC';
                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    }
                    return;
                }
            }
        }
        $block_dst = ltrim($this->destination_number, "-");
        $query = sprintf("SELECT Substring(dstnumber,-10) as blockdst FROM block_dst  where dstnumber = '%s'  and  account_id = '%s' limit 1;", $block_dst, $this->account_id);

        $blockdst = '';
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $blockdst = $value;
        }
        if (strlen(trim($blockdst)) > 0) {
            $otherinfo = $this->account_id;
            $this->fail_route_xml('DNC', $otherinfo);

            $this->otherinfo = $this->account_id . " " . $this->destination_number . " number DNC issue";
            $this->status = 'FAIL';
            if ($this->incomingtopstn == 1) {
                $carrier = Array();
                $user = $this->customersdata;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'DNC';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
            }
            return;
        }

        /*
         * Users call duration calculations
         */
        $this->customers['duration'] = 0;

        if ($this->customers['bundle_value_cost'] > 0 AND $this->customers['bundle_type'] == 'COST') {
            $this->customers['balance'] = $this->customers['balance'] + $this->customers['bundle_value_cost'];
        }

        $this->customers['duration'] = $this->duration($this->customers);
        $this->writelog("Normal duration " . $this->customers['duration']);
        $this->writelog("Bundle Cost " . $this->customers['bundle_value_cost']);

        /*
         * Call Duration is zero. maybe rates are higher compared to balance
         */
        $this->writelog("Normal duration " . $this->customers['duration']);
        $otherinfo = $this->account_id;
        if ($this->customers['duration'] == 0) {
            $this->fail_route_xml('USERBALANCE', $otherinfo);
            $this->otherinfo = $this->account_id . " Customer balance is low " . $this->customers['balance'];
            $this->status = 'FAIL';
            $this->customersdata['user'] = $this->customers;
            if ($this->incomingtopstn == 1) {
                $carrier = Array();
                $user = $this->customersdata;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERBALANCE';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
            }
            return;
        }

        /*
         * If $this->customers['parent_account_id'] length is greter than 0. it means User account is under reseller. So, system will check the reseller account, rates and routes.
         */

        if (strlen($this->customers['parent_account_id']) > 0 and $this->customers['parent_account_id'] != '0') {
            $this->PSTN_reseller($this->customers['parent_account_id']);
        }
        $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));

        $query = sprintf("SELECT length(callerid) as dstlength,  callerid as blockdst FROM pstnclifilter  where '%s' like concat('%%',callerid,'%%') and account_id = '%s' order by dstlength desc limit 1;", $this->callernumber, $this->account_id);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $blockdst = $value;
        }

        if (strlen(trim($blockdst)) > 0) {
            $this->status = 'FAIL';
            $carrier = Array();
            $user = $this->customersdata;
            $user_inbound = $this->rates_incoming;
            $carrier_inbound = $this->carrierdata;
            $this->fserror = 'USERCLIBLOCKEDPSTN';
            $fserror = $this->fserror;
            $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
            return;
        }
        return;
    }

    function force_dst_src_cli_prefix() {
        /*
         * change the CallerID based on Destination number prefix.
         * Manupulation the callerID number
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

    function internalcall_pbx() {
        if ($this->mpstncall == 'PSTN') {
            $this->is_internalcall = 0;
            return;
        }
        $user = $this->customers['account_id'];
        $this->is_internalcall = 0;

        $query = sprintf("select   allnumbers.*,  '1' as tariff_status,  '' as ratecard_id, '' as  tariff_id, 0 as start_day, 6 asend_day, '00:00:00' start_time, '23:59:59' as end_time, '%s' as prefix, '%s' as destination,  0 as rate, 0 as connection_charge,  1 as minimal_time, 1 as resolution_time,  0 as grace_period,  0 as rate_multiplier, 0 as rate_addition, '1' as rates_status, '' as tariff_currency_id, '' as tariff_currency_id, 0 as monthly_charges, '0' as bundle_option, '' as bundle1_type, 0 as bundle1_value from allnumbers where account_id = '%s' and extension_no = '%s';", $this->destination_number, $this->destination_number, $this->customers['account_id'], $this->destination_number);

        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
                $this->is_internalcall = '1';
            }
        }

        $query2 = sprintf("select extension_no as dst_extension_no from allnumbers where extension_no = '%s' and account_id ='%s';", $this->destination_number, $this->customers['account_id']);
        $this->writelog($query2);
        $this->query('SWITCH', $query2);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
                $this->is_internalcall = '1';
            }
        }


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

        //preg_replace("/[^A-Za-z0-9_]/", '', $username);
        foreach ($rs[0] as $key => $value) {
            $this->customers[$key] = preg_replace("/[^A-Za-z0-9_ ()]/", '', $value);
        }

        $this->customersdata['user'] = $this->customers;
        $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
        if (strlen($this->customers['company_name']) == 0) {
            $this->customers['company_name'] = $this->customers['name'];
        }

        $this->customers['src_caller'] = $this->src_caller;
        if ($this->customers['extension_no'] > 0) {
            $this->customers['src_caller'] = $this->customers['extension_no'];
        }
        $this->customers['src_callee'] = $this->src_callee;
        $this->customers['device_id'] = $this->account_device_id;
        $this->customers['device_type'] = $this->account_device_type;
        $this->customers['src_ip'] = $this->account_originator_ip;
        $this->customers['ipaddress'] = $this->account_originator_ip;
        $this->customersdata['user'] = $this->customers;
        $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
        if (strlen($this->customers['parent_account_id']) > 0 and $this->customers['parent_account_id'] != '0') {
            $this->internalcall_reseller_pbx($this->customers['parent_account_id']);
        }
        $this->rates = str_replace('"', "'", json_encode($this->customersdata, JSON_UNESCAPED_UNICODE));
    }

    function internalcall_reseller_pbx($user) {
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

            $carrier = Array();
            $user = $this->rates;
            $user_inbound = $this->rates_incoming;
            $carrier_inbound = $this->carrierdata;
            $this->fserror = 'RESELLERINACTIVE';
            $fserror = $this->fserror;
            $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
            $carrier = Array();
            $user = $this->rates;
            $user_inbound = $this->rates_incoming;
            $carrier_inbound = $this->carrierdata;
            $this->fserror = 'RESELLERCURRENCY';
            $fserror = $this->fserror;
            $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
            return;
        }
        $this->is_internalcall = 0;
        $query = sprintf("select customer_devices.*, '1' as tariff_status,  '' as ratecard_id, '' as  tariff_id, 0 as start_day, 6 asend_day, '00:00:00' start_time, '23:59:59' as end_time, '%s' as prefix, '%s' as destination,  0 as rate, 0 as connection_charge,  1 as minimal_time, 1 as resolution_time,  0 as grace_period,  1 as rate_multiplier, 0 as rate_addition, '1' as rates_status, '' as tariff_currency_id, '' as tariff_currency_id, 0 as monthly_charges, '0' as bundle_option, '' as bundle1_type, 0 as bundle1_value  from customer_devices where account_id = '%s' and extension_no = '%s';", $this->destination_number, $this->destination_number, $user, $this->destination_number);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $reseller[$key] = $value;
            $this->is_internalcall = '1';
        }

        $query2 = sprintf("select extension_no as dst_extension_no from customer_devices where extension_no = '%s' and account_id ='%s';", $this->destination_number, $this->customers['account_id']);
        $this->writelog($query2);
        $this->query('SWITCH', $query2);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $reseller[$key] = $value;
            $this->is_internalcall = '1';
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
            $this->internalcall_reseller_pbx($parent_account_id);
            $parent_account_id = '';
            return;
        }
    }

    function internalcall() {
        if ($this->mpstncall == 'PSTN') {
            $this->is_internalcall = 0;
            return;
        }
        $this->is_internalcall = 0;
        $query = sprintf("select customer_devices.username,customer_devices.secret, customer_devices.ipaddress,customer_devices.status,customer_devices.sip_cc, customer_devices.sip_cps, customer_devices.ipauthfrom, customer_devices.extension_no, customer_devices.voicemail_enabled, customer_devices.voicemail, customer_devices.display_name, customer_devices.caller_id, customer_devices.cli_prefer, customer_devices.codecs, customer_devices.moh_sound, customer_devices.name, customer_devices.email_address, customer_devices.phone_number, customer_devices.ring_timeout, customer_devices.call_forward_all, customer_devices.cfall_destination_type, customer_devices.cfall_destination, customer_devices.call_forward_no_answer, customer_devices.cfnoans_destination_type, customer_devices.cfnoans_destination,  customer_devices.call_forward_busy,  customer_devices.cfbusy_destination_type, customer_devices.cfbusy_destination, customer_devices.cfnoans_timeout, customer_devices.call_recording, customer_devices.dnd, customer_devices.created_by,customer_devices.created_by_account_id, customer_devices.updated_by, customer_devices.created_dt,customer_devices.updated_dt, customer_devices.user_type, customer_devices.extension_id,  customer_devices.pushtoken, customer_devices.firebasetoken, customer_devices.appos, customer_devices.id, '1' as tariff_status,  '' as ratecard_id, '' as  tariff_id, 0 as start_day, 6 asend_day, '00:00:00' start_time, '23:59:59' as end_time, '%s' as prefix, '%s' as destination,  0 as rate, 0 as connection_charge,  1 as minimal_time, 1 as resolution_time,  0 as grace_period,  1 as rate_multiplier, 0 as rate_addition, '1' as rates_status, '' as tariff_currency_id, '' as tariff_currency_id, 0 as monthly_charges, '0' as bundle_option, '' as bundle1_type, 0 as bundle1_value from customer_devices where account_id = '%s' and extension_no = '%s';", $this->destination_number, $this->destination_number, $this->customers['account_id'], $this->destination_number);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $this->customers[$key] = $value;
                $this->is_internalcall = '1';
            }
        }
        if ($this->customers['call_recording'] == '1') {
            $this->recording_status = '1';
            $this->recordingxml = '0';
        }
        $query2 = sprintf("select extension_no as dst_extension_no from customer_devices where extension_no = '%s' and account_id ='%s';", $this->destination_number, $this->customers['account_id']);
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
        $query = sprintf("select customer_devices.*, '1' as tariff_status,  '' as ratecard_id, '' as  tariff_id, 0 as start_day, 6 asend_day, '00:00:00' start_time, '23:59:59' as end_time, '%s' as prefix, '%s' as destination,  0 as rate, 0 as connection_charge,  1 as minimal_time, 1 as resolution_time,  0 as grace_period,  1 as rate_multiplier, 0 as rate_addition, '1' as rates_status, '' as tariff_currency_id, '' as tariff_currency_id, 0 as monthly_charges, '0' as bundle_option, '' as bundle1_type, 0 as bundle1_value  from customer_devices where account_id = '%s' and extension_no = '%s';", $this->destination_number, $this->destination_number, $reseller, $this->destination_number);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            $reseller[$key] = $value;
            $this->is_internalcall = '1';
        }

        $query2 = sprintf("select extension_no as dst_extension_no from customer_devices where extension_no = '%s' and account_id ='%s';", $this->destination_number, $this->customers['account_id']);
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
                $this->otherinfo = $reseller['account_id'] . " Reseller caller CLI " . $this->callernumber . "  is blocked";
                $otherinfo = $this->account_id;
                $this->fail_route_xml('RESELLERCALLERIDBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'RESELLERCALLERIDBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                return;
            }
        }


        if (count($this->reseller_callerid) == 0) {
            /*
             * CallerID number pattern is black.
             */
            $this->otherinfo = $reseller['account_id'] . " Reseller caller CLI " . $this->callernumber . "  is blocked";
            $otherinfo = $this->account_id;
            $this->fail_route_xml('RESELLERCALLERIDBLOCKED', $otherinfo);
            $this->status = 'FAIL';
            $carrier = Array();
            $user = $this->rates;
            $user_inbound = $this->rates_incoming;
            $carrier_inbound = $this->carrierdata;
            $this->fserror = 'RESELLERCALLERIDBLOCKED';
            $fserror = $this->fserror;
            $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
                $this->otherinfo = $reseller['account_id'] . " Reseller caller CLI " . $this->callernumber . "  is blocked";
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'RESELLERCALLERIDBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
                $this->otherinfo = $this->account_id . " Customer CLI is " . $this->callernumber . "  is blocked for incoming DID call";
                $this->fail_route_xml('USERCALLERIDBLOCKED', $otherinfo);
                $this->status = 'FAIL';
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERCALLERIDBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
                $this->otherinfo = $this->account_id . " Customer CLI is " . $this->callernumber . "  is blocked for incoming DID call";
                $this->status = 'FAIL';
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERCALLERIDBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
                $this->otherinfo = $this->customers['account_id'] . " Customer CLI is " . $this->callernumber . "  is blocked for PSTN call";
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERCALLERIDBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
                $this->otherinfo = $this->customers['account_id'] . " Customer CLI is " . $this->callernumber . "  is blocked for PSTN call";
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERCALLERIDBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
                $this->otherinfo = $reseller['account_id'] . " Reseller dialed number " . $this->destination_number . "  is blocked";
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'RESELLERDIALEDNUMBERBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
                $this->otherinfo = $reseller['account_id'] . " Reseller dialed number " . $this->destination_number . "  is blocked";
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'RESELLERDIALEDNUMBERBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERDIALEDNUMBERBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
            $carrier = Array();
            $user = $this->rates;
            $user_inbound = $this->rates_incoming;
            $carrier_inbound = $this->carrierdata;
            $this->fserror = 'USERDIALEDNUMBER';
            $fserror = $this->fserror;
            $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
                $carrier = Array();
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'USERDIALEDNUMBERBLOCKED';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
            $carrier = Array();
            $user = $this->rates;
            $user_inbound = $this->rates_incoming;
            $carrier_inbound = $this->carrierdata;
            $this->fserror = 'USERDIALPLAN';
            $fserror = $this->fserror;
            $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
            $this->otherinfo = $reseller['account_id'] . 'Reseller Routing is not found';
            $this->fail_route_xml('RESELLERDIALPLAN', $otherinfo);
            $this->status = 'FAIL';
            $carrier = Array();
            $user = $this->rates;
            $user_inbound = $this->rates_incoming;
            $carrier_inbound = $this->carrierdata;
            $this->fserror = 'RESELLERDIALPLAN';
            $fserror = $this->fserror;
            $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
        $this->llrratelog = "Buy Rate with CC  " . $llrrates . $route['tariff_currency_id'] . " (" . round($llrrates * $this->ratio, 6) . $currency['currency_id'] . ") and sale rate " . $this->LLRRates . " " . $this->customers['currency_id'];
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
        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_ring=curl " . APIDOAMIN . "api/api.php?calltype=INTERNAL&r=ring&common_uuid=" . $this->uuid . "&gatewayname=EXTEN&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=EXTEN&carrier_gateway_ipaddress_name=EXTEN&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl  " . APIDOAMIN . "api/api.php?calltype=INTERNAL&r=ring&common_uuid=" . $this->uuid . "&gatewayname=EXTEN&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=EXTEN&carrier_gateway_ipaddress_name=EXTEN&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?calltype=INTERNAL&r=ring&common_uuid=" . $this->uuid . "&gatewayname=EXTEN&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=EXTEN&carrier_gateway_ipaddress_name=EXTEN&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?calltype=INTERNAL&r=answer&common_uuid=" . $this->uuid . "&gatewayname=EXTEN&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=EXTEN&carrier_gateway_ipaddress_name=EXTEN&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
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
        $this->Gateway_XML .= "\n <action application=\"bridge\" data=\"{sip_invite_domain=" . $lb . "}sofia/internal/" . $destination_number . "@" . $lb . "\"/>";
        $this->Gateway_XML .= "\n <action application=\"answer\"/>";
        $this->Gateway_XML .= "\n<action application=\"export\" data=\"voicemail_greeting_number=1\"/>";
        $this->Gateway_XML .= "\n<action application=\"export\" data=\"voicemail_alternate_greet_id=" . $this->destination_number . "\"/>";
        $this->Gateway_XML .= "\n   <action application=\"voicemail\" data=\"default \$\${domain} " . $destination_number . "\" />";
    }

    function PSTN_routing() {

        if ($this->is_internalcall == '1') {
            $this->internalcallxml();
            return;
        }
        $query = sprintf("SELECT dialplan_status, failover_sipcause_list FROM dialplan WHERE dialplan_id = '%s' limit 1;", $this->dialplan_id);
        $dialplan_status = '1';
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $rs = $this->resultset();
        foreach ($rs[0] as $key => $value) {
            if ($key == 'failover_sipcause_list')
                $failover_sipcause_list = $value;
            if ($key == 'dialplan_status')
                $dialplan_status = $value;
        }
        if ($dialplan_status == '0') {
            $otherinfo = $this->dialplan_id . " routing is inactive";
            $this->fail_route_xml('ROUTINGINACTIVE', $otherinfo);
            $this->status = 'FAIL';
            $this->otherinfo = $otherinfo;
            $carrier = $fs;
            $user = $this->customersdata;
            $user_inbound = $this->rates_incoming;
            $carrier_inbound = $this->carrierdata;
            $this->fserror = 'ROUTINGINACTIVE';
            $fserror = $this->fserror;
            $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
            return;
        }
        $dstarray = str_split($this->destination_number);
        $tmpstr = '';
        $str = '';
        foreach ($dstarray as $key => $value) {
            $tmpstr .= $value;
            $str .= "dial_prefix = '" . $tmpstr . "' or ";
        }
        $str = rtrim($str, ' or ');
        $maxcarriers = 0;
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
                $carrier = $fs;
                $user = $this->rates;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'RESELLERBALANCE';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
        return join('', array_map(function ($value) {
                    return $value == 1 ? mt_rand(1, 9) : mt_rand(0, 9);
                }, range(1, $length)));
    }

    function PSTN_route_change_random_cli($carrier_callerid_raw) {

        $carrier_id = $carrier_callerid_raw[0]['carrier_id'];
        $query = sprintf("SELECT LENGTH(destination_prefix) lndata,  clirule_id, clirule_name, carrier_id, destination_prefix, cli_fixprefix, cli_length, cli_status   FROM carrier_randomcli where carrier_id = '%s' and '%s' like concat(destination_prefix,'%%') and cli_status = '1' ORDER BY lndata desc limit 1;", $carrier_id, $this->destination_number);
        $this->writelog($query);
        $this->query('SWITCH', $query);
        $carrier_caller = $this->resultset();

        if (count($carrier_caller[0]) > 0) {

            $carrier_caller = $carrier_caller[0];
            $sub_lenth = strlen(trim($carrier_caller['cli_fixprefix']));
            $gen_length = $carrier_caller['cli_length'];
            // - $sub_lenth;
            if ($gen_length > 0) {

                $newnumber = $this->random_number($gen_length);
                $org_callernumber = $carrier_caller['cli_fixprefix'] . $newnumber;
                $this->writelog("New CLI $org_callernumber");
                return $org_callernumber;
            } elseif (strlen(trim($carrier_caller['cli_fixprefix'])) > 0) {
                $org_callernumber = $carrier_caller['cli_fixprefix'];
                $this->writelog("New cli_fixprefix  $org_callernumber");
                return $org_callernumber;
            }
        }
        return '';
    }

    function PSTN_route_change_callid($carrier_callerid_raw) {
        $org_callernumber = '';
        $org_callernumber = $this->PSTN_route_change_random_cli($carrier_callerid_raw);
        if (strlen(trim($org_callernumber))) {
            return $org_callernumber;
        }
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
        $rs3 = $rs2 = $route1 = Array();
        foreach ($routelist as $route1) {
            if ($route1['route_status'] == '1') {

                $query = sprintf("SELECT  carrier.extend_call_duration, diversion_header_option, diversion_header_as_comingcli_db, diversion_header_format,  '%s' dialplan_id, '%s' failover_sipcause_list, carrier.carrier_currency_id tariff_currency_id , carrier.tax1,carrier.tax2, carrier.tax3, carrier.tax_type, carrier.dp,carrier_ips.carrier_ip_id, carrier.carrier_id, carrier.carrier_name, carrier.tariff_id, carrier.carrier_status, carrier.cli_prefer, carrier.carrier_progress_timeout, carrier.carrier_ring_timeout, carrier_ips.ipaddress, carrier_ips.ipaddress_name, carrier_ips.load_share, carrier_ips.priority, carrier_ips.ip_status, carrier_ips.auth_type, carrier_ips.username, carrier_ips.passwd, carrier_codecs, carrier.carrier_cps, carrier.carrier_cc, carrier.carrier_currency_id from carrier INNER JOIN carrier_ips on carrier.carrier_id = carrier_ips.carrier_id where carrier.carrier_id = '%s'  and ip_status = '1' limit 4;", $route1['dialplan_id'], $route1['failover_sipcause_list'], $route1['carrier_id']);
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
                    $query = sprintf("SELECT '%s' as billing_number, tariff_ratecard_map.ratecard_id, tariff_id, start_day, end_day, start_time, end_time, priority, rate_id, prefix, REPLACE(REPLACE( destination,',',' '),'-',' ') as destination, rate, connection_charge, minimal_time, resolution_time, grace_period, rate_multiplier, rate_addition, rates_status from tariff_ratecard_map  INNER JOIN carrier_rates on carrier_rates.ratecard_id = tariff_ratecard_map.ratecard_id INNER JOIN ratecard on carrier_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'OUTGOING' where tariff_id = '%s' and  WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and  (%s) ORDER BY priority asc, prefix desc, rate DESC, end_time ASC limit 1;", $this->destination_number, $rs2[0]['tariff_id'], $str);

                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $rs3 = $this->resultset();
                    $data1 = $data = Array();
                    foreach ($rs2 as $data1) {
                        foreach ($data1 as $key => $value) {
                            if ($key == 'carrier_name' or $key == 'ipaddress_name' or $key == 'destination')
                                $data2[$key] = preg_replace("/[^A-Za-z0-9_ ()]/", '', $value);
                            else
                                $data2[$key] = $value;
                        }

                        if (count($rs3[0]) > 0) {
                            $rs4 = array_merge($data2, $rs3[0], $tech_prefix[0]);
                        } else {
                            $rs4 = array_merge($data2, $tech_prefix[0]);
                        }

                        array_push($data, $rs4);
                    }
                    //$data = $this->PSTN_route_gateway_lb($data);


                    $this->writelog(" Making Array carrier rates  All data " . str_replace('"', "'", json_encode($data)));
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

    function diversion_header($data) {
        $this->writelog('diversion_header -- ' . json_encode($data, JSON_UNESCAPED_UNICODE));
        if ($data['diversion_header_option'] == '0') {
            $this->Gateway_XML .= "\n <action application=\"unset\" data=\"sip_h_Diversion\"/>";

            return;
        }
        if ($data['diversion_header_as_comingcli_db'] == '0') {
            $query = sprintf("select diversion_number from carrier_diversion_number where account_id = '%s' and carrier_id = '%s' and number_status = '1' limit 1;", $data['account_id'], $data['carrier_id']);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            if (count($rs) > 0) {
                foreach ($rs as $detail) {
                    $diversion_number = str_replace('_', '', trim($detail[diversion_number]));
                    if (strlen($data['diversion_header_format']) > 0) {
                        $diversion_header_format = $data['diversion_header_format'];
                        $diversion_header_format = str_replace('${RDN}', $diversion_number, $diversion_header_format);
                        $diversion_header_format = str_replace('${network_addr}', SERVERIP, $diversion_header_format);

                        $diversion_header_format = str_replace('${BILLINGCODE}', $diversion_number, $diversion_header_format);
                    } else {
                        $diversion_header_format = "<sip:+" . $diversion_number . "@" . SERVERIP . ">;reason=unconditional;screen=no;privacy=off";
                    }
                }
            } else {

                $query = sprintf("update carrier_diversion_number set account_id = '%s', assign_date = now(), number_status = '1' where (account_id = '' or  account_id is null)  and carrier_id = '%s' and number_status = '1' limit 1;", $data['account_id'], $data['carrier_id']);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $this->execute();
                $query = sprintf("select diversion_number from carrier_diversion_number where account_id = '%s' and carrier_id = '%s' and number_status = '1' limit 1;", $data['account_id'], $data['carrier_id']);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs = $this->resultset();
                if (count($rs) > 0) {
                    foreach ($rs as $detail) {
                        $diversion_number = str_replace('_', '', trim($detail[diversion_number]));
                        if (strlen($data['diversion_header_format']) > 0) {
                            $diversion_header_format = $data['diversion_header_format'];
                            $diversion_header_format = str_replace('${RDN}', $diversion_number, $diversion_header_format);
                            $diversion_header_format = str_replace('${BILLINGCODE}', $diversion_number, $diversion_header_format);
                            $diversion_header_format = str_replace('${network_addr}', SERVERIP, $diversion_header_format);
                        } else {
                            $diversion_header_format = "<sip:+" . trim($diversion_number) . "@" . SERVERIP . ">;reason=unconditional;screen=no;privacy=off";
                        }
                    }
                }
            }
            $this->Gateway_XML .= "\n <action application=\"unset\" data=\"sip_h_Diversion\"/>";

            if (strlen($diversion_header_format) > 0) {
                // $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_Diversion=" . $diversion_header_format . "\"/>";
                $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_h_Diversion=" . $diversion_header_format . "\"/>";
            }
        } else {
            $this->Gateway_XML .= "\n <action application=\"unset\" data=\"sip_h_Diversion\"/>";
            if (strlen(trim($data['caller_id'])) > 0) {
                $diversion_header_format = "<sip:+" . trim($data['caller_id']) . "@" . SERVERIP . ">;reason=unconditional;screen=no;privacy=off";
                //$this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_Diversion=" . $diversion_header_format . "\"/>";
                $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_h_Diversion=" . $diversion_header_format . "\"/>";
            }
        }
    }

    function PSTN_route($data) {
        $lb = $this->Hunt_Network_Addr;
        $lb = $this->lb;
        $gatewaylist = Array();
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

            if ($this->internaldidroute == 1)
                return;
            else
                $this->internaldidroute = null;



            $route2['ratio'] = $this->ratio;
            $this->writelog($route2['ipaddress'] . " and " . $route2['rates_status'] . " and " . $route2['carrier_status'] . " and " . $route2['ip_status']);
            if ($this->status == 'OK') {
                $j = $this->last_array_j_value;
                if (strlen($route2['ipaddress']) > 0 and $route2['rates_status'] == '1' and $route2['carrier_status'] == '1' and $route2['ip_status'] == '1') {
                    $this->out_dialplan = '1';
                    $dialnumber = $this->destination_number;
                    $destination_number = $this->route_change_prefix($dialnumber, $route2);
                    if ($this->recording_status == '1' and $this->recordingxml == '0') {
                        if ($this->timeout) {
                            $this->Gateway_XML .= "\n<action application=\"set\" data=\"execute_on_answer=sched_hangup +" . $this->timeout . " alloted_timeout \"/>";
                            $j = $j + 1;
                            if ($j < 1000)
                                $newj = "200" . $j;
                            else
                                $newj = $j;
                            $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "execute_on_answer=sched_hangup +" . $this->timeout . " alloted_timeout ";
                        }
                        $this->recordingxml = '1';
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"RECORD_TITLE = Recording \${destination_number} \${caller_id_number} \${strftime(%Y-%m-%d %H:%M)}\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "RECORD_TITLE = Recording \${destination_number} \${caller_id_number} \${strftime(%Y-%m-%d %H:%M)}";

                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"RECORD_COPYRIGHT = (c) 1980 Factory Records, Inc.\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "RECORD_COPYRIGHT = (c) 1980 Factory Records, Inc.";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"RECORD_SOFTWARE = FreeSWITCH\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "data=\"RECORD_SOFTWARE = FreeSWITCH";

                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"RECORD_ARTIST = Ian Curtis\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "RECORD_ARTIST = Ian Curtis";
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"RECORD_DATE = \${strftime(%Y-%m-%d %H:%M)}\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "RECORD_DATE = \${strftime(%Y-%m-%d %H:%M)}";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"RECORD_STEREO = true\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "RECORD_STEREO = true";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"media_bug_answer_req=true\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "bypass_media=false";

                        $recording_file = RECORDING . "" . $this->account_id . "/" . date('Ymd') . "/" . $this->callernumber . "_" . $this->destination_number . "_" . date('His') . ".wav";

                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"recording_file=" . $recording_file . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "recording_file=" . $recording_file;

                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"recording_file=" . $recording_file . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "recording_file=" . $recording_file;

                        $this->Gateway_XML .= "\n<action application=\"record_session\" data=\"" . $recording_file . "\"/>";

                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['record_session'] .= $recording_file;

                        $cmd = "chmod -R 777 " . RECORDING . "recording/";

                        $this->Gateway_XML .= "\n <action application=\"system\" data=\"" . $cmd . "\"/>";
                    }
                    if (strlen(trim($route2['failover_sipcause_list'])) > 0) {
                        $fscause = '16,UNKNOWN,DESTINATION_OUT_OF_ORDER,NO_USER_RESPONSE,CHANNEL_UNACCEPTABLE,NORMAL_CIRCUIT_CONGESTION,NETWORK_OUT_OF_ORDER,NORMAL_TEMPORARY_FAILURE,SERVICE_NOT_IMPLEMENTED,CALL_REJECTED,SWITCH_CONGESTION,REQUESTED_CHAN_UNAVAIL,BEARERCAPABILITY_NOTAVAIL,USER_BUSY,NO_ANSWER,INVALID_MSG_UNSPECIFIED,MANDATORY_IE_MISSING,MESSAGE_TYPE_NONEXIST,WRONG_MESSAGE,IE_NONEXIST,INVALID_IE_CONTENTS,WRONG_CALL_STATE,MANDATORY_IE_LENGTH_ERROR,PROTOCOL_ERROR,INTERWORKING,CRASH,SYSTEM_SHUTDOWN,LOSE_RACE,MANAGER_REQUEST,BLIND_TRANSFER,ATTENDED_TRANSFER,ALLOTTED_TIMEOUT,USER_CHALLENGE,MEDIA_TIMEOUT,PICKED_OFF,USER_NOT_REGISTERED,PROGRESS_TIMEOUT,GATEWAY_DOWN';

                        $failcause = explode(',', $route2['failover_sipcause_list']);
                        foreach ($failcause as $sipcode) {
                            if ($sipcode == '400') {
                                $fscause = $fscause . ',NORMAL_TEMPORARY_FAILURE';
                            } else if ($sipcode == '401') {
                                $fscause = $fscause . ',CALL_REJECTED';
                            } else if ($sipcode == '402') {
                                $fscause = $fscause . ',CALL_REJECTED';
                            } else if ($sipcode == '403') {
                                $fscause = $fscause . ',OUTGOING_CALL_BARRED,INCOMING_CALL_BARRED,BEARERCAPABILITY_NOTAUTH,CALL_REJECTED';
                            } else if ($sipcode == '404') {
                                $fscause = $fscause . ',UNALLOCATED_NUMBER,NO_ROUTE_TRANSIT_NET,NO_ROUTE_DESTINATION,NO_ROUTE_TRANSIT_NET';
                            } else if ($sipcode == '408') {
                                $fscause = $fscause . ',NO_USER_RESPONSE';
                            } else if ($sipcode == '410') {
                                $fscause = $fscause . ',NUMBER_CHANGED,REDIRECTION_TO_NEW_DESTINATION';
                            } else if ($sipcode == '480') {
                                $fscause = $fscause . ',NO_ANSWER,SUBSCRIBER_ABSENT,NORMAL_UNSPECIFIED';
                            } else if ($sipcode == '483') {
                                $fscause = $fscause . ',EXCHANGE_ROUTING_ERROR';
                            } else if ($sipcode == '484') {
                                $fscause = $fscause . ',INVALID_NUMBER_FORMAT';
                            } else if ($sipcode == '486') {
                                $fscause = $fscause . ',USER_BUSY';
                            } else if ($sipcode == '487') {
                                $fscause = $fscause . ',ORIGINATOR_CANCEL';
                            } else if ($sipcode == '488') {
                                $fscause = $fscause . ',BEARERCAPABILITY_NOTIMPL';
                            } else if ($sipcode == '488') {
                                $fscause = $fscause . ',INCOMPATIBLE_DESTINATION';
                            } else if ($sipcode == '501') {
                                $fscause = $fscause . ',FACILITY_REJECTED';
                            } else if ($sipcode == '501') {
                                $fscause = $fscause . ',FACILITY_NOT_IMPLEMENTED,SERVICE_NOT_IMPLEMENTED';
                            } else if ($sipcode == '502') {
                                $fscause = $fscause . ',DESTINATION_OUT_OF_ORDER,NETWORK_OUT_OF_ORDER';
                            } else if ($sipcode == '503') {
                                $fscause = $fscause . ',NORMAL_CIRCUIT_CONGESTION,NORMAL_TEMPORARY_FAILURE,SWITCH_CONGESTION,REQUESTED_CHAN_UNAVAIL,BEARERCAPABILITY_NOTAVAIL';
                            } else if ($sipcode == '504') {
                                $fscause = $fscause . ',RECOVERY_ON_TIMER_EXPIRE';
                            } else if ($sipcode == '603') {
                                $fscause = $fscause . ',CALL_REJECTED';
                            } else {
                                $fscause = $fscause . ",$sipcode";
                            }
                        }
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "continue_on_fail=" . trim($fscause);

                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"continue_on_fail=" . trim($fscause) . "\"/>";
                    } else {
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"continue_on_fail=TRUE\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "continue_on_fail=TRUE";
                    }

                    $j = $j + 1;
                    if ($j < 1000)
                        $newj = "200" . $j;
                    else
                        $newj = $j;
                    $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "instant_ringback=true";

                    if ($this->customers['media_transcoding'] == '1') {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=1\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_h_X-MEDIATRA=1";
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_h_X-MEDIATRA=1\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_h_X-MEDIATRA=1";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"bypass_media=false\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$j]['set'] .= "bypass_media=false";
                    } else {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=0\"/>";
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-MEDIATRA=0\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_h_X-MEDIATRA=0";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_h_X-MEDIATRA=0";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"bypass_media=true\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "bypass_media=true";
                    }

                    if ($route2['cli_prefer'] == 'pid') {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_cid_type=pid\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_cid_type=pid";
                    } else if ($route2['cli_prefer'] == 'rpid') {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_cid_type=rpid\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_cid_type=rpid";
                    } else {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_cid_type=none\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_cid_type=none";
                    }

                    $gateway_ipaddress_name = trim($route2[carrier_ip_id]);

                    $destination_number1 = preg_replace("/#/", "T", $destination_number);
                    $j = $j + 1;
                    if ($j < 1000)
                        $newj = "200" . $j;
                    else
                        $newj = $j;
                    if (CAMPAIGN) {
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:execute_on_ring=curl " . APIDOAMIN . "api/api.php?buyer_number=" . $this->buyer_number . "&buyer_name=" . str_replace(" ", 'TTT', $this->buyer_name) . "&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['gateway_ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id_name'] . "&routcallerid=" . $route_callid . "&account=" . $this->user_account_id . "&carrier=" . $route2['carrier_id_name'] . "&destination_number=" . $destination_number1;
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_ring=curl " . APIDOAMIN . "api/api.php?buyer_number=" . $this->buyer_number . "&buyer_name=" . str_replace(" ", 'TTT', $this->buyer_name) . "&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
                    } else {
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:execute_on_ring=curl " . APIDOAMIN . "api/api.php?&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['gateway_ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id_name'] . "&routcallerid=" . $route_callid . "&account=" . $this->user_account_id . "&carrier=" . $route2['carrier_id_name'] . "&destination_number=" . $destination_number1;
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_ring=curl " . APIDOAMIN . "api/api.php?&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
                    }
                    $j = $j + 1;
                    if ($j < 1000)
                        $newj = "200" . $j;
                    else
                        $newj = $j;

                    if (CAMPAIGN) {
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?buyer_number=" . $this->buyer_number . "&buyer_name=" . str_replace(" ", 'TTT', $this->buyer_name) . "&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['gateway_ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id_name'] . "&routcallerid=" . $route_callid . "&account=" . $this->user_account_id . "&carrier=" . $route2['carrier_id_name'] . "&destination_number=" . $destination_number1;
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl  " . APIDOAMIN . "api/api.php?buyer_number=" . $this->buyer_number . "&buyer_name=" . str_replace(" ", 'TTT', $this->buyer_name) . "&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . "\"/>";

                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?buyer_number=" . $this->buyer_number . "&buyer_name=" . str_replace(" ", 'TTT', $this->buyer_name) . "&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
                    } else {

                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['gateway_ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id_name'] . "&routcallerid=" . $route_callid . "&account=" . $this->user_account_id . "&carrier=" . $route2['carrier_id_name'] . "&destination_number=" . $destination_number1;
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl  " . APIDOAMIN . "api/api.php?&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . "\"/>";

                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
                    }
                    $j = $j + 1;
                    if ($j < 1000)
                        $newj = "200" . $j;
                    else
                        $newj = $j;

                    if (CAMPAIGN) {
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?buyer_number=" . $this->buyer_number . "&buyer_name=" . str_replace(" ", 'TTT', $this->buyer_name) . "&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['gateway_ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id_name'] . "&routcallerid=" . $route_callid . "&account=" . $this->user_account_id . "&carrier=" . $route2['carrier_id_name'] . "&destination_number=" . $destination_number1;

                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?buyer_number=" . $this->buyer_number . "&buyer_name=" . str_replace(" ", 'TTT', $this->buyer_name) . "&calltype=OUT&r=answer&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
                    } else {
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:execute_on_pre_answer=curl " . APIDOAMIN . "api/api.php?&calltype=OUT&r=ring&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['gateway_ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id_name'] . "&routcallerid=" . $route_callid . "&account=" . $this->user_account_id . "&carrier=" . $route2['carrier_id_name'] . "&destination_number=" . $destination_number1;

                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?&calltype=OUT&r=answer&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id'] . "&routcallerid=" . $route_callid . "&account=" . $this->account_id . "&carrier=" . $route2['carrier_id'] . "&destination_number=" . $destination_number1 . " | -k \"/>";
                    }
                    $j = $j + 1;
                    if ($j < 1000)
                        $newj = "200" . $j;
                    else
                        $newj = $j;

                    if (CAMPAIGN) {
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?buyer_number=" . $this->buyer_number . "&buyer_name=" . str_replace(" ", 'TTT', $this->buyer_name) . "&calltype=OUT&r=answer&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['gateway_ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id_name'] . "&routcallerid=" . $route_callid . "&account=" . $this->user_account_id . "&carrier=" . $route2['carrier_id_name'] . "&destination_number=" . $destination_number1;
                    } else {
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:execute_on_answer=curl " . APIDOAMIN . "api/api.php?&calltype=OUT&r=answer&common_uuid=" . $this->uuid . "&gatewayname=" . $gateway_ipaddress_name . "&atime=\${strftime(%Y-%m-%dT%H:%M:%S)}&gateway_ipaddress=" . $route2['gateway_ipaddress'] . "&carrier_gateway_ipaddress_name=" . $route2['carrier_ip_id_name'] . "&routcallerid=" . $route_callid . "&account=" . $this->user_account_id . "&carrier=" . $route2['carrier_id_name'] . "&destination_number=" . $destination_number1;
                    }
                    $gateway_ipaddress_name = '';

                    if (CAMPAIGN) {

                        if ($this->ringtime_buyer > 0) {
                            $this->Gateway_XML .= "\n<action application=\"set\" data=\"call_timeout=" . $this->ringtime_buyer . "\"/>";
                            $j = $j + 1;
                            if ($j < 1000)
                                $newj = "200" . $j;
                            else
                                $newj = $j;
                            $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "call_timeout=" . $this->ringtime_buyer;
                        } else {

                            if ($route2['carrier_ring_timeout'] > 0) {
                                $this->Gateway_XML .= "\n<action application=\"set\" data=\"call_timeout=" . $route2['carrier_ring_timeout'] . "\"/>";

                                $j = $j + 1;
                                if ($j < 1000)
                                    $newj = "200" . $j;
                                else
                                    $newj = $j;
                                $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "call_timeout=" . $route2['carrier_ring_timeout'];
                            } else {
                                $this->Gateway_XML .= "\n<action application=\"set\" data=\"call_timeout=60\"/>";
                                $j = $j + 1;
                                if ($j < 1000)
                                    $newj = "200" . $j;
                                else
                                    $newj = $j;
                                $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "call_timeout=60";
                            }
                        }
                    } else {

                        if ($route2['carrier_ring_timeout'] > 0) {
                            $this->Gateway_XML .= "\n<action application=\"set\" data=\"call_timeout=" . $route2['carrier_ring_timeout'] . "\"/>";

                            $j = $j + 1;
                            if ($j < 1000)
                                $newj = "200" . $j;
                            else
                                $newj = $j;
                            $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "call_timeout=" . $route2['carrier_ring_timeout'];
                        } else {
                            $this->Gateway_XML .= "\n<action application=\"set\" data=\"call_timeout=60\"/>";
                            $j = $j + 1;
                            if ($j < 1000)
                                $newj = "200" . $j;
                            else
                                $newj = $j;
                            $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "call_timeout=60";
                        }
                    }
                    if ($route2['carrier_progress_timeout'] > 0) {
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"progress_timeout=" . $route2['carrier_progress_timeout'] . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        //$this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] = "progress_timeout=" . $route2['carrier_progress_timeout'];
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] = "progress_timeout=25";
                    } else {
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"progress_timeout=50\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "progress_timeout=50";
                    }
                    $route_callid = $route2['route_callid'];
                    $this->min_dialplan_string = 1;
                    $route2['src_caller'] = $this->callernumber_user;

                    if ($this->customers['extension_no'] > 0) {
                        $route2['src_caller'] = $this->customers['extension_no'];
                    }
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

                    $diversion_data['account_id'] = $this->account_id;
                    $diversion_data['carrier_id'] = $route2['carrier_id'];
                    $diversion_data['caller_id'] = $cli_from;
                    $diversion_data['diversion_header_option'] = $route2['diversion_header_option'];
                    $diversion_data['diversion_header_as_comingcli_db'] = $route2['diversion_header_as_comingcli_db'];

                    $diversion_data['diversion_header_format'] = $route2['diversion_header_format'];
                    unset($route2['diversion_header_format']);
                    unset($route2['diversion_header_as_comingcli_db']);
                    unset($route2['diversion_header_option']);

                    if (count($this->route222) == 0) {
                        $this->route222 = $route2;
                    }

                    $j = $j + 1;
                    if ($j < 1000)
                        $newj = "200" . $j;
                    else
                        $newj = $j;
                    $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "gatewaydata=" . str_replace('"', "'", json_encode($route2));

                    $this->Gateway_XML .= "\n<action application=\"set\" data=\"gatewaydata=" . str_replace('"', "'", json_encode($route2, JSON_UNESCAPED_UNICODE)) . "\"/>";
                    $this->Gateway_XML .= "\n<action application=\"export\" data=\"gatewaydata=" . str_replace('"', "'", json_encode($route2, JSON_UNESCAPED_UNICODE)) . "\"/>";
                    $j = $j + 1;
                    if ($j < 1000)
                        $newj = "200" . $j;
                    else
                        $newj = $j;
                    $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "gatewaydata=" . str_replace('"', "'", json_encode($route2));
                    $commoncodecs = '';
                    if (strlen($route2['carrier_codecs']) > 0 and strlen($this->customers['account_codecs']) > 0) {
                        $carriercodecs = explode(",", $route2['carrier_codecs']);
                        $usercodecs = explode(",", $this->customers['account_codecs']);
                        $commoncodecs = array_intersect($usercodecs, $carriercodecs);
                        $commoncodecs = implode(',', $commoncodecs);
                    }
                    $this->did2pst = '1';
                    $this->writelog($this->callernumber);

                    if (strlen(trim($route_callid)) > 0) {
                        $cli_from = $route_callid;
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "ignore_dispaly_updates=true=";
                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_number=" . $route_callid . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "effective_caller_id_number=" . $route_callid;
                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_name=" . $route_callid . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "effective_caller_id_name=" . $route_callid;

                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_number=" . $route_callid . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "effective_caller_id_number=" . $route_callid;
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_name=" . $route_callid . "\"/>";

                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "effective_caller_id_name=" . $route_callid;
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-FROMURI=" . $route_callid . "@" . $lb . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_h_X-FROMURI=" . $route_callid . "@" . $lb;
                    } else {
                        $cli_from = $this->callernumber;
                        if ($cli_from == '' or strlen($cli_from) == 0)
                            $cli_from = "unknown";
                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_number=" . $cli_from . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "effective_caller_id_number=" . $cli_from;
                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"effective_caller_id_name=" . $cli_from . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "effective_caller_id_name=" . $cli_from;

                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_number=" . $cli_from . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "effective_caller_id_number=" . $cli_from;
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"effective_caller_id_name=" . $cli_from . "\"/>";
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-FROMURI" . $cli_from . "@" . $lb . "\"/>";
                    }


                    if (strlen($route2['carrier_codecs']) > 0) {
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"nolocal:absolute_codec_string=" . $route2['carrier_codecs'] . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:absolute_codec_string=" . $route2['carrier_codecs'];
                    } else {
                        $this->Gateway_XML .= "\n <action application=\"export\"  data=\"nolocal:absolute_codec_string=\${ep_codec_string}\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:absolute_codec_string=\${ep_codec_string}";
                    }

                    $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-CARRIERID=" . trim($route2['carrier_id']) . "\"/>";
                    $j = $j + 1;
                    if ($j < 1000)
                        $newj = "200" . $j;
                    else
                        $newj = $j;
                    $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_h_X-CARRIERID=" . trim($route2['carrier_id']);
                    $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-CARRIERCPS=" . trim($route2['carrier_cps']) . "\"/>";

                    $j = $j + 1;
                    if ($j < 1000)
                        $newj = "200" . $j;
                    else
                        $newj = $j;
                    $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_h_X-CARRIERCPS=" . trim($route2['carrier_cps']);
                    $query = sprintf("SELECT did.did_number, did.did_status, did.carrier_id, did.account_id, did.reseller1_account_id, did.reseller2_account_id, did.reseller3_account_id,  did_dst.account_id, did_dst.dst_type, did_dst.dst_destination, did_dst.dst_destination2, did_dst.dst_type2  from did INNER JOIN  did_dst on did.did_number =  did_dst.did_number where did.did_number   =  '%s'   ORDER BY did.did_number limit 1;", $this->destination_number);

                    $this->isthisdidcall = Array();
                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $rs = $this->resultset();
                    foreach ($rs[0] as $key => $value) {
                        $this->isthisdidcall[$key] = $value;
                    }
                    $this->writelog("did_number " . $this->isthisdidcall['did_number']);
                    $this->writelog("identity passcode " . $this->request['variable_sip_h_identity']);
                    $this->writelog("passcode genertaed and assigned sip_h_Identity " . $this->ast);
                    if (STIRSHAKEN) {
                        /*   Identity header */
                        $this->callid = trim(file_get_contents('/proc/sys/kernel/random/uuid'));
                        $data = array(
                            'attest' => "A",
                            'dest' => "$destination_number",
                            'origid' => "$this->callid",
                            'orig' => "$cli_from",
                        );
                        $send_data = json_encode($data);
                        $this->writelog("passcode genertaed and assigned " . $send_data);
                        $passcode = $this->IdentityHeader($data);
                        $this->passcode = $passcode;
                        $this->writelog("passcode genertaed and assigned " . $passcode);
                        if (strlen($passcode) > 20) {
                            $j = $j + 1;
                            if ($j < 1000)
                                $newj = "200" . $j;
                            else
                                $newj = $j;
                            $this->Gateway_XML .= "\n   <action application=\"unset\" data=\"Identity\"/>";
                            $this->Gateway_ARRAY['ROUTE'][0][$newj]['unset'] .= "Identity";
                            $j = $j + 1;
                            if ($j < 1000)
                                $newj = "200" . $j;
                            else
                                $newj = $j;
                            $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_h_Identity=" . $passcode;
                            $this->Gateway_XML .= "\n<action application=\"set\" data=\"sip_h_Identity=" . $passcode . "\"/>";
                            $this->writelog("-------------- passcode genertaed and assigned sip_h_Identity " . $this->ast);
                        }
                        /* end */
                    }
                    /* extend B-leg call duration */
                    if ($route2['extend_call_duration'] == '1') {
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "hangup_after_bridge=true";
                        $this->Gateway_XML .= "\n<action application=\"set\" data=\"hangup_after_bridge=true\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= " nolocal:execute_on_answer=lua  /home/OV500/portal/application/modules/pbx/script/setvar.lua";
                        $this->Gateway_XML .= "\n  <action application=\"export\" data=\"nolocal:execute_on_answer=lua  /home/OV500/portal/application/modules/pbx/script/setvar.lua\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "nolocal:transfer_after_bridge=" . $route2['carrier_id'] . ":XML:usableg";
                        $this->Gateway_XML .= "\n<action application=\"export\" data=\"nolocal:transfer_after_bridge=" . $route2['carrier_id'] . ":XML:usableg\"/>";
                    }
                    /* end */






                    if (strlen(trim($this->isthisdidcall['did_number'])) > 0) {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-DIDINTERNALCALL=sip:" . trim($destination_number) . "@" . trim($this->isthisdidcall['dst_destination']) . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_h_X-DIDINTERNALCALL=sip:" . trim($destination_number) . "@" . trim($this->isthisdidcall['dst_destination']);

                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_h_X-DIDINTERNALCALL=sip:" . trim($destination_number) . "@" . trim($this->isthisdidcall['dst_destination']) . "\"/>";

                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_h_X-DIDINTERNALCALL=sip:" . trim($destination_number) . "@" . trim($this->isthisdidcall['dst_destination']);

                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-DSTURI=sip:" . trim($this->destination_number_org) . "@" . trim($lb) . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_h_X-DSTURI=sip:" . trim($this->destination_number_org) . "@" . trim($lb);

                        $this->internaldidroute = 1;
                    } else {
                        $this->Gateway_XML .= "\n <action application=\"set\" data=\"sip_h_X-DSTURI=sip:" . trim($destination_number) . "@" . trim($route2['ipaddress']) . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "sip_h_X-DSTURI=sip:" . trim($destination_number) . "@" . trim($route2['ipaddress']);
                    }

                    $this->diversion_header($diversion_data);
                    $diversion_data = Array();
                    if ($gateway_type == 'CUSTOMER') {
                        $this->Gateway_XML .= "\n   <action application=\"export\" data=\"sip_contact_user=" . $cli_from . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_exclude_contact=" . $lb;

                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_from_display=" . $gateway_username;

                        $this->Gateway_XML .= "\n   <action application=\"export\" data=\"sip_contact_user=" . $gateway_username . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_contact_user=" . $gateway_username;
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_from_uri=sip:" . $gateway_username . "@" . $lb . "\"/>";

                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_from_uri=sip:" . $gateway_username . "@" . $lb;
                        $this->Gateway_XML .= "\n <action application=\"bridge\" data=\"{sip_invite_domain=" . $lb . ",sip_auth_username=" . $gateway_username . ",sip_auth_password=" . $gateway_passwd . "}sofia/internal/" . $destination_number . "@" . $lb . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['bridge'] .= "{continue_on_fail=true,hangup_after_bridge=true,sip_invite_domain=" . $lb . ",sip_auth_username=" . $gateway_username . ",sip_auth_password=" . $gateway_passwd . "}sofia/internal/" . $destination_number . "@" . $lb;
                    } else {
                        if ($cli_from == '' or strlen($cli_from) == 0)
                            $cli_from = "unknown";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_from_display=" . $cli_from;
                        $this->Gateway_XML .= "\n <action application=\"export\" data=\"sip_from_uri=sip:" . $cli_from . "@" . $lb . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_from_uri=sip:" . $cli_from . "@" . $lb;
                        $this->Gateway_XML .= "\n   <action application=\"export\" data=\"sip_contact_user=" . $cli_from . "\"/>";
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "sip_contact_user=" . $cli_from;

                        if ($this->internaldidroute == 1) {
                            if (strlen($this->callid) < 10) {
                                $callidstring = $this->basic_generateRandomString(20);
                            } else {
                                $callidstring = $this->callid;
                            }
                            $this->Gateway_XML .= "\n <action application=\"bridge\" data=\"{sip_invite_call_id=" . $callidstring . ",sip_invite_domain=" . $lb . "}sofia/internal/" . $this->destination_number_org . "@" . $lb . "\"/>";
                        } else {
                            if (strlen($this->callid) < 10) {
                                $callidstring = $this->basic_generateRandomString(20);
                            } else {
                                $callidstring = $this->callid;
                            }
                            $this->Gateway_XML .= "\n <action application=\"bridge\" data=\"{sip_invite_call_id=" . $callidstring . ",sip_invite_domain=" . $lb . "}sofia/internal/" . $destination_number . "@" . $lb . "\"/>";
                        }
                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "bridge_early_media=false";

                        $j = $j + 1;
                        if ($j < 1000)
                            $newj = "200" . $j;
                        else
                            $newj = $j;
                        $this->Gateway_ARRAY['ROUTE'][0][$newj]['bridge'] .= "{sip_invite_call_id=" . $callidstring . ",sip_invite_domain=" . $lb . "}sofia/internal/" . $destination_number . "@" . $lb;
                    }
                    $cli_from = '';
                    $this->last_array_j_value = $newj;
                    $this->out_dialplan_section = '0';
                } else {
                    $otherinfo = $this->account_id;
                    $this->out_dialplan_section = '1';
                    $otherinfo = "RS " . $route2['rates_status'] . " CS " . $route2['carrier_status'] . " IS " . $route2['ip_status'] . " ipaddress " . $route2['ipaddress'];

                    $this->fail_route_xml('ROUTESTATUSISSUE', $otherinfo);
                    if ($this->incomingtopstn == 1) {
                        $carrier = $route2;
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'ROUTESTATUSISSUE';
                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    }
                }
            } else {


                $otherinfo = $this->llrratelog;
                $this->fail_route_xml('CARRIERLLRISSUE', $otherinfo);
                if ($this->status_llr != 'OK') {
                    $otherinfo = $this->llrratelog;
                    $this->fail_route_xml('CARRIERLLRISSUE', $otherinfo);
                    if ($this->incomingtopstn == 1) {
                        $carrier = $route2;
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'CARRIERLLRISSUE';
                        $fserror = $this->fserror;
                        $this->otherinfo = $otherinfo;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    }
                } else {
                    $otherinfo = $route2['carrier_id_name'] . " " . $route2['gateway_ipaddress'] . " Rstatus-" . $route2['rates_status'] . " CStatus" . $route2['carrier_status'] . " IPStatus- " . $route2['gateway_ip_status'] . " " . $this->llrstatus . " " . $this->otherissue;
                    $this->otherinfo = $otherinfo;
                    $this->fail_route_xml('USERANIBLOCKED', $otherinfo);
                    if ($this->incomingtopstn == 1) {
                        $carrier = $route2;
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'USERANIBLOCKED';
                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    }
                }
            }
        }
    }

    function basic_generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
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

            $carrier = Array();
            $user = $this->rates;
            $user_inbound = $this->rates_incoming;
            $carrier_inbound = $this->carrierdata;
            $this->fserror = 'RESELLERINACTIVE';
            $fserror = $this->fserror;
            $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
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
            if ($this->incomingtopstn == 1) {
                $carrier = Array();
                $user = $this->customersdata;
                $user_inbound = $this->rates_incoming;
                $carrier_inbound = $this->carrierdata;
                $this->fserror = 'RESELLERCURRENCY';
                $fserror = $this->fserror;
                $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
            }
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
                $this->fail_route_xml('RESELLERCC', $otherinfo);
                $this->status = 'FAIL';

                if ($this->incomingtopstn == 1) {
                    $carrier = Array();
                    $user = $this->customersdata;

                    $user_inbound = $this->rates_incoming;
                    $carrier_inbound = $this->carrierdata;
                    $this->fserror = 'RESELLERCC';
                    $fserror = $this->fserror;
                    $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                }
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
                    if ($this->incomingtopstn == 1) {
                        $carrier = Array();
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'RESELLERCLI';
                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    }
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
                $sql = sprintf("update account set status_id = '-2' where account_id = '%s';", $user);
                $this->query('SWITCH', $sql);
                $this->execute();
            }
            if ($reseller['balance'] < 0) {
                $otherinfo = $reseller['account_id'];
                $this->fail_route_xml('RESELLERBALANCE', $otherinfo . " " . $reseller['balance']);
                $this->status = 'FAIL';
                $this->customersdata[$reseelerinfo] = $reseller;
                if ($this->incomingtopstn == 1) {
                    $carrier = Array();
                    $user = $this->customersdata;
                    $user_inbound = $this->rates_incoming;
                    $carrier_inbound = $this->carrierdata;
                    $this->fserror = 'RESELLERBALANCE';
                    $fserror = $this->fserror;
                    $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                }
                return;
            }

            if ($this->sign($reseller['balance']) == '-1')
                $reseller['orgbalance'] = abs($reseller['balance']);
            else
                $reseller['orgbalance'] = "-" . $reseller['balance'];


            /*
             * Reseller don't have sufucuient balance to process the call.
             */
            if ($reseller['balance'] < 0) {
                $otherinfo = $reseller['account_id'];
                $this->fail_route_xml('RESELLERBALANCE', $otherinfo . " " . $reseller['balance']);
                $this->status = 'FAIL';
                $this->customersdata[$reseelerinfo] = $reseller;
                if ($this->incomingtopstn == 1) {
                    $carrier = Array();
                    $user = $this->customersdata;
                    $user_inbound = $this->rates_incoming;
                    $carrier_inbound = $this->carrierdata;
                    $this->fserror = 'RESELLERBALANCE';
                    $fserror = $this->fserror;
                    $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                }
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

            $query = sprintf("SELECT tariff.tariff_status,  tariff_ratecard_map.ratecard_id, tariff_ratecard_map.tariff_id, tariff_ratecard_map.start_day, tariff_ratecard_map.end_day, tariff_ratecard_map.start_time, tariff_ratecard_map.end_time, customer_rates.prefix, REPLACE(REPLACE( customer_rates.destination,',',' '),'-',' ') as destination , customer_rates.rate, customer_rates.connection_charge, customer_rates.minimal_time, customer_rates.resolution_time, customer_rates.grace_period, customer_rates.rate_multiplier, customer_rates.rate_addition, customer_rates.rates_status, tariff.tariff_currency_id     FROM tariff_ratecard_map  INNER JOIN customer_rates on customer_rates.ratecard_id = tariff_ratecard_map.ratecard_id  INNER JOIN ratecard on customer_rates.ratecard_id = ratecard.ratecard_id and ratecard.ratecard_for = 'OUTGOING' INNER JOIN tariff on  tariff.tariff_id = tariff_ratecard_map.tariff_id where WEEKDAY(CURDATE()) BETWEEN start_day and end_day AND CURTIME() BETWEEN start_time and end_time and tariff_ratecard_map.tariff_id = '%s' and (%s)  ORDER BY priority asc, prefix desc, rate ASC, end_time ASC limit 1;", $reseller['tariff_id'], $str);

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
                if ($this->incomingtopstn == 1) {
                    $carrier = Array();
                    $user = $this->customersdata;
                    $user_inbound = $this->rates_incoming;
                    $carrier_inbound = $this->carrierdata;
                    $this->fserror = 'RESELLERRATE';
                    $fserror = $this->fserror;
                    $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                }
                return;
            }


            foreach ($rs[0] as $key => $value) {
                $reseller[$key] = $value;
            }


            /*
             * Bundle & Plan
             */


            $resellerusers['bundle_type_minute'] = 'MINUTE';
            $resellerusers['bundle_value_minute'] = 0;
            $query = sprintf("SELECT * from customer_bundle_sdr INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id  where customer_bundle_sdr.account_id  = '%s' and  (%s) and LENGTH(prefix) > 0 and bundle_for = 'OUT'  GROUP BY customer_bundle_sdr.bundle_package_id order by prefix desc;", $reseller_id, $str);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $rs = $this->resultset();
            if (count($rs) > 0) {

                $available_bundle_minute = 0;
                $available_bundle_cost = 0;
                foreach ($rs as $data) {
                    $query = sprintf("SELECT account_id, bundle_type,    sum(total_allowed_sec) as  total_allowed_sec , sum(sdr_consumption) as sdr_consumption  FROM customer_bundle_sdr where account_id = '%s' and bundle_package_id  = '%s'  and  (total_allowed_sec  - sdr_consumption   > 0 )  and bundle_for = 'OUT'  ;", $user, $data['bundle_package_id'], date("Y-m-d"));
                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $rs3 = $this->resultset();
                    $available_bundle1 = 0;
                    $available_bundle2 = 0;
                    if (count($rs3) > 0) {
                        foreach ($rs3 as $data_b) {
                            if ($data_b['bundle_type'] == 'MINUTE') {
                                $available_bundle1 = $data_b['total_allowed_sec'] - $data_b['sdr_consumption'];
                                $this->writelog("available minutes $available_bundle1");
                                $available_bundle_minute = $available_bundle_minute + $available_bundle1;
                                $this->writelog("available total minutes $available_bundle_minute");
                                $resellerusers['bundle_type_minute'] = 'MINUTE';
                                $resellerusers['bundle_value_minute'] = $available_bundle_minute;
                                $resellerusers['bundle_prefix_number'] = $this->billing_destination_number;
                            }
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
                    if ($this->incomingtopstn == 1) {
                        $carrier = Array();
                        $user = $this->customersdata;
                        $user_inbound = $this->rates_incoming;
                        $carrier_inbound = $this->carrierdata;
                        $this->fserror = 'RESELLERLLRISSUE';
                        $fserror = $this->fserror;
                        $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                    }
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
                if ($this->incomingtopstn == 1) {
                    $carrier = Array();
                    $user = $this->customersdata;
                    $user_inbound = $this->rates_incoming;
                    $carrier_inbound = $this->carrierdata;
                    $this->fserror = 'RESELLERTARIFFCURRENCY';
                    $fserror = $this->fserror;
                    $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                }
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
                if ($this->incomingtopstn == 1) {
                    $carrier = Array();
                    $user = $this->customersdata;
                    $user_inbound = $this->rates_incoming;
                    $carrier_inbound = $this->carrierdata;
                    $this->fserror = 'RESELLERTARIFFINACTIVE';
                    $fserror = $this->fserror;
                    $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                }
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
                if ($this->incomingtopstn == 1) {
                    $carrier = Array();
                    $user = $this->customersdata;
                    $user_inbound = $this->rates_incoming;
                    $carrier_inbound = $this->carrierdata;
                    $this->fserror = 'RESELLERRATEBLOCKED';
                    $fserror = $this->fserror;
                    $this->systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $this->fserror, $this->status);
                }
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


        if ($user['bundle_type_minute'] == 'MINUTE')
            $duration = $duration + ($user['bundle_value_minute'] * 60);
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
        $responce .= "\n<action application=\"set\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";
        $responce .= "\n<action application=\"export\" data=\"gatewaydataincoming=" . str_replace('"', "'", json_encode($this->carrierdata, JSON_UNESCAPED_UNICODE)) . "\"/>";

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
            if ($this->incomingtopstn == '1')
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
        // $j=$this->last_array_j_value;
        $j = 100;
        $responce .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=0\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "SWITCH_ISSUE=0";
        $responce .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=0\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "SWITCH_ISSUE=0";
        $responce .= "\n<action application=\"set\" data=\"hangup_after_bridge=TRUE\"/>";

        $responce .= "\n<action application=\"pre_answer\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['pre_answer'] .= "";

        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "hangup_after_bridge=TRUE";
        $codecs = $this->usercodeclist();
        $responce .= "\n <action application=\"set\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "USERCODECSCLIST=" . $codecs;
        $responce .= "\n <action application=\"export\" data=\"USERCODECSCLIST=" . $codecs . "\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "USERCODECSCLIST=" . $codecs;
        $responce .= "\n<action application=\"set\" data=\"common_uuid=" . $this->uuid . "\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "common_uuid=" . $this->uuid;
        $responce .= "\n<action application=\"export\" data=\"common_uuid=" . $this->uuid . "\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "common_uuid=" . $this->uuid;
        $responce .= "\n<action application=\"set\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "caller_callid=" . $this->caller_callid;

        $responce .= "\n<action application=\"export\" data=\"caller_callid=" . $this->caller_callid . "\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "caller_callid=" . $this->caller_callid;
        $responce .= "\n<action application=\"set\" data=\"userdata=" . $this->rates . "\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "userdata=" . $this->rates;
        $responce .= "\n<action application=\"export\" data=\"userdata=" . $this->rates . "\"/>";
        $j = $j + 1;
        if ($j < 1000)
            $newj = "200" . $j;
        else
            $newj = $j;
        $this->Gateway_ARRAY['ROUTE'][0][$newj]['export'] .= "userdata=" . $this->rates;
        if ($this->timeout) {
            $responce .= "\n<action application=\"set\" data=\"execute_on_answer=sched_hangup +" . $this->timeout . " alloted_timeout \"/>";
            $j = $j + 1;
            if ($j < 1000)
                $newj = "200" . $j;
            else
                $newj = $j;
            $this->Gateway_ARRAY['ROUTE'][0][$newj]['set'] .= "execute_on_answer=sched_hangup +" . $this->timeout . " alloted_timeout";
        }
        $this->last_array_j_value = $newj;
        $this->Gateway_ARRAY2 = $this->Gateway_ARRAY;
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

        if (count($this->customersdata) > 0) {
            foreach ($this->customersdata as $userkey => $uservalue) {
                if ($userkey == 'user') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'customer_currency_id' or $key == 'ipaddress' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate' or $key == 'src_caller' or $key == 'src_callee' or $key == 'src_ip') {
                            $data .= "customer_$key = '" . addslashes($value) . "',";
                        } elseif ($key == 'company_name') {
                            $data .= "customer_company = '" . addslashes($value) . "',";
                        } elseif ($key == 'extension_no') {
                            $data .= "src_extension_no = '" . addslashes($value) . "',";
                        } elseif ($key == 'extension_name') {
                            $data .= "src_extension_name = '" . addslashes($value) . "',";
                        } elseif ($key == 'extension_id') {
                            $data .= "src_extension_id = '" . addslashes($value) . "',";
                        }
                    }
                }
                if ($userkey == 'Reseller1') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                            $data .= "reseller1_$key = '" . addslashes($value) . "',";
                        }
                    }
                }
                if ($userkey == 'Reseller2') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                            $data .= "reseller2_$key = '" . addslashes($value) . "',";
                        }
                    }
                }
                if ($userkey == 'Reseller3') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                            $data .= "reseller3_$key = '" . addslashes($value) . "',";
                        }
                    }
                }
            }

            foreach ($this->route22 as $key => $value) {

                if ($key == 'carrier_id' or $key == 'carrier_name' or $key == 'carrier_currency_id')
                    $data .= "$key='" . addslashes($value) . "',";

                if ($key == 'ratecard_id' or $key == 'tariff_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate' or $key == 'src_caller' or $key == 'src_callee' or $key == 'dst_caller' or $key == 'dst_callee') {
                    $data .= "carrier_$key = '" . addslashes($value) . "',";
                }
                if ($key == 'ipaddress')
                    $data .= "carrier_ipaddress='" . addslashes($value) . "',";
                if ($key == 'ipaddress_name')
                    $data .= "carrier_ipaddress_name='" . addslashes($value) . "',";
            }

            $data .= "dialplan_id = '" . addslashes($this->dialplan_id) . "',";
            $data .= "common_uuid = '" . addslashes($this->uuid) . "',";
            $data .= "start_time= '" . addslashes($this->request['Event-Date-Local']) . "',";
            $data .= "fs_host= '" . addslashes($this->request['FreeSWITCH-IPv4']) . "',";
            $data .= "loadbalancer= '" . trim($this->Hunt_Network_Addr) . "',";
            if ($this->pbxcall == '1') {
                $data .= "callstatus='answer',";
                $data .= "answer_time = '" . addslashes($this->request['Event-Date-Local']) . "',";
            } else {
                $data .= "callstatus='progress',";
            }



            $data .= "dst_app='DID',";
            $data .= "dst_app_number='" . $this->didnumber . "',";
            $data .= "dst_app_name='" . $this->didnumber . "',";

            $data .= "call_flow='DID',";
            $data .= "calltype='IN',";

            if (CAMPAIGN) {

                if (strlen(trim($this->campaign_id)) > 0)
                    $data .= "campaign_id='" . $this->campaign_id . "',";
                if (strlen(trim($this->campaign_name)) > 0)
                    $data .= "campaign_name='" . $this->campaign_name . "',";
            }


            if (strlen(trim($this->endpoint_app)) > 0) {

                $data .= "endpoint_app='" . $this->endpoint_app . "',";
                $data .= "endpoint_name='" . $this->endpoint_name . "',";
                $data .= "endpoint_number='" . $this->endpoint_number . "',";
                $data .= "endpoint_extension_no='" . $this->endpoint_extension_no . "',";
                $data .= "endpoint_uuid='" . $this->uuid . "',";
            }
            if (strlen(trim($this->didnumber)) > 0)
                $data .= "did_number='" . $this->didnumber . "',";

            $livecalldata = rtrim($data, ',');
            $query = "insert into livecalls  set " . $livecalldata;
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $this->execute();
            $this->route22 = Array();
        }
    }

    function livecalls() {
        if ($this->pbxcall == '1') {
            return;
        }
        if (count($this->customersdata) > 0) {
            foreach ($this->customersdata as $userkey => $uservalue) {
                if ($userkey == 'user') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'customer_currency_id' or $key == 'ipaddress' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate' or $key == 'src_caller' or $key == 'src_callee' or $key == 'src_ip') {
                            $data .= "customer_$key = '" . addslashes($value) . "',";
                        } elseif ($key == 'company_name') {
                            $data .= "customer_company = '" . addslashes($value) . "',";
                        } elseif ($key == 'extension_no') {
                            $data .= "src_extension_no = '" . addslashes($value) . "',";
                        } elseif ($key == 'extension_name') {
                            $data .= "src_extension_name = '" . addslashes($value) . "',";
                        } elseif ($key == 'extension_id') {
                            $data .= "src_extension_id = '" . addslashes($value) . "',";
                        }
                    }
                }
                if ($userkey == 'Reseller1') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                            $data .= "reseller1_$key = '" . addslashes($value) . "',";
                        }
                    }
                }
                if ($userkey == 'Reseller2') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                            $data .= "reseller2_$key = '" . addslashes($value) . "',";
                        }
                    }
                }
                if ($userkey == 'Reseller3') {
                    foreach ($uservalue as $key => $value) {
                        if ($key == 'account_id' or $key == 'tariff_id' or $key == 'ratecard_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate') {
                            $data .= "reseller3_$key = '" . addslashes($value) . "',";
                        }
                    }
                }
            }

            foreach ($this->route222 as $key => $value) {

                if ($key == 'carrier_id' or $key == 'carrier_name' or $key == 'carrier_currency_id')
                    $data .= "$key='" . addslashes($value) . "',";

                if ($key == 'ratecard_id' or $key == 'tariff_id' or $key == 'prefix' or $key == 'destination' or $key == 'rate' or $key == 'src_caller' or $key == 'src_callee' or $key == 'dst_caller' or $key == 'dst_callee') {
                    $data .= "carrier_$key = '" . addslashes($value) . "',";

                    if ($key == 'destination')
                        $dst_app_name = $value;

                    if ($key == 'dst_callee')
                        $dst_app_number = $value;



                    $this->writelog($key . " == " . $value);
                }
                if ($key == 'ipaddress')
                    $data .= "carrier_ipaddress='" . addslashes($value) . "',";
                if ($key == 'ipaddress_name')
                    $data .= "carrier_ipaddress_name='" . addslashes($value) . "',";
            }

            if (strlen(trim($this->callmodule)) > 0 and $this->callmodule == 'PBX') {
                $data .= "calltype='EXTEN',";
            } else {
                $data .= "calltype='OUT',";
            }



            $data .= "dst_app='PSTN',";
            $data .= "dst_app_number='" . $dst_app_number . "',";
            $data .= "dst_app_name='" . $dst_app_name . "',";

            $data .= "endpoint_app='PSTN',";
            $data .= "endpoint_number='" . $dst_app_number . "',";
            $data .= "endpoint_name='" . $dst_app_name . "',";
            $data .= "endpoint_extension_no='" . $dst_app_number . "',";

            $data .= "dialplan_id = '" . addslashes($this->dialplan_id) . "',";
            $data .= "common_uuid = '" . addslashes($this->uuid) . "',";
            $data .= "start_time= '" . addslashes($this->request['Event-Date-Local']) . "',";
            $data .= "fs_host= '" . addslashes($this->request['FreeSWITCH-IPv4']) . "',";
            $data .= "loadbalancer= '" . trim($this->Hunt_Network_Addr) . "',";
            $data .= "callstatus='progress',";
            if (CAMPAIGN) {

                if (strlen(trim($this->campaign_id)) > 0)
                    $data .= "campaign_id='" . $this->campaign_id . "',";
                if (strlen(trim($this->campaign_name)) > 0)
                    $data .= "campaign_name='" . $this->campaign_name . "',";
            }
            if (strlen(trim($this->didnumber)) > 0)
                $data .= "did_number='" . $this->didnumber . "',";

            $livecalldata = rtrim($data, ',');
            $query = "insert into livecalls  set " . $livecalldata;

            $this->writelog($query);
            $this->query('SWITCH', $query);
            $this->execute();
            $this->route222 = Array();
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
                $this->Gateway_XML2 = "\n<action application=\"set\" data=\"fs_errorcode=" . $errer . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"fs_errorcode=" . $errer . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"set\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"sip_term_status=" . $causeSIP . " \"/>";
                $this->Gateway_XML2 .= "\n<action application=\"respond\" data=\"" . $causeSIP . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"hangup\"/>";
            } else {
                $this->error = $errer;
                $this->Gateway_XML2 = "\n<action application=\"set\" data=\"fs_errorcode_in=" . $errer . "\"/>";
                $this->Gateway_XML2 .= "\n<action application=\"export\" data=\"fs_errorcode_in=" . $errer . "\"/>";
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
        $j = 21;
        if ($this->Gateway_XML1 == '') {
            if (strlen($custommessage) > 0) {
                $this->error = $errer;
                $this->Gateway_XML1 = "\n<action application=\"set\" data=\"fs_errorcode=" . $errer . "\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['set'] .= "fs_errorcode=" . $errer;
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"fs_errorcode=" . $errer . "\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['export'] .= "fs_errorcode=" . $errer;

                $this->Gateway_XML1 .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['set'] .= "SWITCH_ISSUE=1";
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['export'] .= "SWITCH_ISSUE=1";
                $this->Gateway_XML1 .= "\n<action application=\"set\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['set'] .= "sip_term_status=" . $causeSIP;

                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['export'] .= "sip_term_status=" . $causeSIP;
                $this->Gateway_XML1 .= "\n<action application=\"respond\" data=\"" . $causeSIP . "\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['respond'] .= "$causeSIP";
                $this->Gateway_XML1 .= "\n<action application=\"hangup\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['hangup'] .= "";
            } else {
                $this->error = $errer;
                $this->Gateway_XML1 = "\n<action application=\"set\" data=\"fs_errorcode=" . $errer . "\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['set'] .= "fs_errorcode=" . $errer;
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"fs_errorcode=" . $errer . "\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['export'] .= "fs_errorcode=" . $errer;
                $this->Gateway_XML1 .= "\n<action application=\"set\" data=\"SWITCH_ISSUE=1\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['set'] .= "SWITCH_ISSUE=1";
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"SWITCH_ISSUE=1\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['export'] .= "SWITCH_ISSUE=1";
                $this->Gateway_XML1 .= "\n<action application=\"set\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['set'] .= "sip_term_status=" . $causeSIP;
                $this->Gateway_XML1 .= "\n<action application=\"export\" data=\"sip_term_status=" . $causeSIP . "\"/>";
                $j = $j + 1;
                if ($j < 1000)
                    $newj = "100" . $j;
                else
                    $newj = $j;
                $this->Gateway_ARRAY2['ROUTEFAIL'][0][$newj]['export'] .= "sip_term_status=" . $causeSIP;
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

            if ($rateinfo['rate_multiplier'] == 0) {
                $rateinfo['rate_multiplier'] = 1;
            }
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
                $this->hangupby = 'CALLEE';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_bye') {
                //FS received BYE from the caller (they hung up)
                $this->hangupby = 'CALLER';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_refuse') {
                //FS rejected the call (e.g. 4xx or 5xx)
                $this->hangupby = 'SYSTEM';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_bye') {
                //FS sent BYE to the call
                //er (we hung up)
                $this->hangupby = 'CALLEE';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_cancel') {
                //n/a
                $this->hangupby = 'CALLER';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_refuse') {
                //Endpoint rejected the call (e.g. 4xx or 5xx)
                $this->hangupby = 'CALLEE';
                // $this->cdr_variable['sip_term_status'] = '403';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_cancel') {
                $this->hangupby = 'CALLEE';
            }
        } else if ($this->leg == 'B') {
            // Leg-b
            if ($this->cdr_variable['sip_invite_failure_status'] == '404') {
                $this->hangupby = 'CALLEE';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_bye') {
                //FS received BYE from the endpoint (they hung up)
                $this->hangupby = 'CALLEE';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_refuse') {
                //Endpoint rejected the call (e.g. 4xx or 5xx)
                $this->hangupby = 'SYSTEM';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_refuse') {
                //Endpoint rejected the call (e.g. 4xx or 5xx)
                $this->hangupby = 'CALLEE';
                //$this->cdr_variable['sip_term_status'] = '503';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_bye') {
                //FS sent BYE to the endpoint (we hung up)
                $this->hangupby = 'CALLER';
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_cancel') {
                //FS aborted the call (we sent CANCEL)
                $this->hangupby = 'CALLER';
            }
        }


        if ($this->cdr_variable['sip_term_status'] == 515) {
            $this->hangupby = 'SYSTEM';
            $this->cdr_variable['fs_errorcode'] = 'CARRIERCPSISSUE';
        }
        if ($this->cdr_variable['billsec'] > 0 and $this->cdr_variable['sip_term_status'] == 515) {
            $this->cdr_variable['sip_term_status'] = '200';
            $this->cdr_variable['fs_errorcode'] = '';
            $this->hangupby = 'CALLEE';
        }
        if ($this->cdr_variable['billsec'] > 0) {
            $this->cdr_variable['sip_term_status'] = '200';
            $this->cdr_variable['sip_invite_failure_status'] = '200';
        }


        if (strlen($this->cdr_variable['cc_queue']) > 1) {
            $this->recording_file = $this->cdr_variable['cc_record_filename'];
        } else {
            $this->recording_file = $this->cdr_variable['recording_file'];
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
            // if ((strlen($gatewaydataincoming) > 10 and strlen($userdataincoming) > 10) or ( $this->switch_issue == '1')) {
            if (strlen($gatewaydataincoming) > 10 or ( $this->switch_issue == '1')) {
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
            $this->writelog("######### Inbound To PSTN ######### outbound " . $this->cdr_variable['is_outbound']);
            if ($this->cdr_variable['is_outbound'])
                $this->cdr_variable['INCOMING2PSTN'] = '1';
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
//                    $this->switch_calls_statistics = '1';
                    $this->switch_carrier_statistics = '1';
                }
                if (strlen($gateways) > 10 and strlen($users) > 10) {
                    $carrierdata = json_decode(str_replace("'", '"', $gateways));
                    $userdata = json_decode(str_replace("'", '"', $users));
                    $data = $this->cdr_data_process($carrierdata, $userdata);

                    $this->writelog("######### Inbound To PSTN  #########");
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
                    $data_array["carrier_" . $key] = trim($value);
                } elseif ($key == 'ipaddress_name') {
                    $data_array["carrier_" . $key] = trim($value);
                } elseif ($key == 'carrier_id') {
                    $data_array[$key] = trim($value);
                } elseif ($key == 'carrier_currency_id') {
                    $data_array[$key] = trim($value);
                } elseif ($key == 'carrier_name') {
                    $data_array[$key] = trim($value);
                } else {
                    $data_array["carrier_" . $key] = trim($value);
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
                        $data_array["customer_" . $key] = trim($value);
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
                        $data_array["reseller1_" . $key] = trim($value);
                    }
                }
            }
            $this->reseller2_data = Array();
            if ($userkey == 'Reseller2') {

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
                        $data_array["reseller2_" . $key] = trim($value);
                    }
                }
            }
            $this->reseller3_data = Array();
            if ($userkey == 'Reseller3') {

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
                        $data_array["reseller3_" . $key] = trim($value);
                    }
                }
            }
        }
        return $data_array;
    }

    function cdr_process_internal_call($data) {
        $query = sprintf("delete from livecalls where common_uuid = '%s'", addslashes($this->cdr_variable['common_uuid']));
        $this->query('SWITCH', $query);
        $this->execute();
        $this->writelog($query);
        $dbdate = date('Ym');

        $dbdate = date('Ym', strtotime("+0 day", strtotime($this->cdr_variable['end_stamp'])));

        if ($this->cdr_variable['billsec'] > 0) {
            if ($this->hangupby == 'CALLEE')
                $this->cdr_variable["disposition_cause"] = 'Call disconnected by Callee';
            else
                $this->cdr_variable["disposition_cause"] = 'Call disconnected by Caller';
        }





        if ($this->cdr_variable['billsec'] > 0) {
            $data = "uuid = '" . addslashes($this->cdr_variable['uuid']) . "',";

            $data .= "pdd = '" . addslashes($pdd) . "',";

            $data .= "start_time= '" . addslashes($this->cdr_variable['start_stamp']) . "',";
            $data .= "answer_time= '" . addslashes($this->cdr_variable['answer_stamp']) . "',";
            $data .= "end_time= '" . addslashes($this->cdr_variable['end_stamp']) . "',";

            $data .= "customer_company_name = '" . $this->userdata['company_name'] . "',";

            $data .= "carrier_duration = '" . addslashes($this->cdr_variable['billsec']) . "',";
            $data .= "customer_duration = '" . addslashes($this->cdr_variable['billsec']) . "',";

            $query = sprintf("delete from livecalls where common_uuid = '%s'", addslashes($this->cdr_variable['common_uuid']));
            $this->query('SWITCH', $query);
            $this->execute();

            if (strlen(trim($this->cdr_variable['fs_errorcode'])) == 0) {
                $this->cdr_variable['fs_errorcode'] = $this->cdr_variable['hangup_cause'];
            }

            $data .= "fs_errorcode = '" . addslashes($this->cdr_variable['fs_errorcode']) . "',";
            $data .= "carrier_ratecard_id = '" . addslashes($this->carrierdata['ratecard_id']) . "',";
            $data .= "carrier_tariff_id = '" . addslashes($this->carrierdata['tariff_id']) . "',";
            $data .= "carrier_prefix = '" . addslashes($this->carrierdata['prefix']) . "',";
            $data .= "carrier_destination = '" . addslashes($this->carrierdata['destination']) . "',";
            $data .= "carrier_ipaddress = '" . addslashes($this->carrierdata['ipaddress']) . "',";
            $data .= "carrier_ipaddress_name = '" . addslashes($this->carrierdata['ipaddress_name']) . "',";
            $data .= "carrier_src_caller = '" . addslashes($this->carrierdata['src_caller']) . "',";
            $data .= "carrier_src_callee = '" . addslashes($this->carrierdata['src_callee']) . "',";
            $data .= "carrier_dst_caller = '" . addslashes($this->carrierdata['dst_caller']) . "',";
            $data .= "carrier_dst_callee = '" . addslashes($this->carrierdata['dst_callee']) . "',";
            $data .= "customer_tariff_id = '" . addslashes($this->userdata['tariff_id']) . "',";
            $data .= "customer_ipaddress = '" . addslashes($this->userdata['ipaddress']) . "',";
            $data .= "customer_ratecard_id = '" . addslashes($this->userdata['ratecard_id']) . "',";
            $data .= "customer_prefix = '" . addslashes($this->userdata['prefix']) . "',";
            $data .= "customer_destination = '" . addslashes($this->userdata['destination']) . "',";
            $data .= "customer_src_caller = '" . addslashes($this->userdata['src_caller']) . "',";
            $data .= "customer_src_callee = '" . addslashes($this->userdata['src_callee']) . "',";
            $data .= "customer_src_ip = '" . addslashes($this->userdata['src_ip']) . "',";
            $data .= "customer_account_id = '" . addslashes($this->userdata['account_id']) . "',";
            $data .= "reseller1_account_id = '" . addslashes($this->reseller1_data['account_id']) . "',";
            $data .= "reseller1_tariff_id = '" . addslashes($this->reseller1_data['tariff_id']) . "',";
            $data .= "reseller1_ratecard_id = '" . addslashes($this->reseller1_data['ratecard_id']) . "',";
            $data .= "reseller1_prefix = '" . addslashes($this->reseller1_data['prefix']) . "',";
            $data .= "reseller1_destination = '" . addslashes($this->reseller1_data['destination']) . "',";
            $data .= "reseller2_destination = '" . addslashes($this->reseller2_data['destination']) . "',";
            $data .= "reseller2_account_id = '" . addslashes($this->reseller2_data['account_id']) . "',";
            $data .= "reseller2_tariff_id = '" . addslashes($this->reseller2_data['tariff_id']) . "',";
            $data .= "reseller2_ratecard_id = '" . addslashes($this->reseller2_data['ratecard_id']) . "',";
            $data .= "reseller2_prefix = '" . addslashes($this->reseller2_data['prefix']) . "',";
            $data .= "reseller3_account_id = '" . addslashes($this->reseller3_data['account_id']) . "',";
            $data .= "reseller3_tariff_id = '" . addslashes($this->reseller3_data['tariff_id']) . "',";
            $data .= "reseller3_ratecard_id = '" . addslashes($this->reseller3_data['ratecard_id']) . "',";
            $data .= "reseller3_prefix = '" . addslashes($this->reseller3_data['prefix']) . "',";
            $data .= "reseller3_destination = '" . addslashes($this->reseller3_data['destination']) . "',";

            $data .= "sip_from_user  = '" . addslashes($this->cdr_variable['sip_from_user']) . "',";
            $data .= "sip_from_uri  = '" . addslashes($this->cdr_variable['sip_from_uri']) . "',";
            $data .= "sip_from_host = '" . addslashes($this->cdr_variable['sip_from_host']) . "',";
            $data .= "channel_name = '" . addslashes($this->cdr_variable['channel_name']) . "',";
            $data .= "common_uuid = '" . addslashes($this->cdr_variable['common_uuid']) . "',";
            $data .= "caller_callid = '" . addslashes($this->cdr_variable['caller_callid']) . "',";
            $data .= "callee_callid = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
            $data .= "sip_call_id = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
            $data .= "ep_codec_string  = '" . addslashes($this->cdr_variable['ep_codec_string']) . "',";
            $data .= "sip_network_ip = '" . addslashes($this->cdr_variable['sip_network_ip']) . "',";
            $data .= "sip_received_ip = '" . addslashes($this->cdr_variable['sip_received_ip']) . "',";
            $data .= "sip_received_port = '" . addslashes($this->cdr_variable['sip_received_port']) . "',";
            $data .= "sip_via_protocol = '" . addslashes($this->cdr_variable['sip_via_protocol']) . "',";
            $data .= "sip_from_user_stripped = '" . addslashes($this->cdr_variable['sip_from_user_stripped']) . "',";
            $data .= "sip_from_display = '" . addslashes($this->cdr_variable['sip_from_display']) . "',";
            $data .= "sip_full_from = '" . addslashes($this->cdr_variable['sip_full_from']) . "',";
            $data .= "sip_to_display = '" . addslashes($this->cdr_variable['sip_to_display']) . "',";
            $data .= "sip_full_to = '" . addslashes($this->cdr_variable['sip_full_to']) . "',";
            $data .= "sip_req_user = '" . addslashes($this->cdr_variable['sip_req_user']) . "',";
            $data .= "sip_req_uri = '" . addslashes($this->cdr_variable['sip_req_uri']) . "',";
            $data .= "sip_to_user = '" . addslashes($this->cdr_variable['sip_to_user']) . "',";
            $data .= "sip_contact_user = '" . addslashes($this->cdr_variable['sip_contact_user']) . "',";
            $data .= "sip_contact_port = '" . addslashes($this->cdr_variable['sip_contact_port']) . "',";
            $data .= "sip_contact_uri = '" . addslashes($this->cdr_variable['sip_contact_uri']) . "',";
            $data .= "sip_contact_host = '" . addslashes($this->cdr_variable['sip_contact_host']) . "',";
            $data .= "rtp_use_codec_string = '" . addslashes($this->cdr_variable['rtp_use_codec_string']) . "',";
            $data .= "sip_user_agent = '" . addslashes($this->cdr_variable['sip_user_agent']) . "',";
            $data .= "sip_via_host = '" . addslashes($this->cdr_variable['sip_via_host']) . "',";
            $data .= "sip_via_port = '" . addslashes($this->cdr_variable['sip_via_port']) . "',";
            $data .= "sip_via_rport = '" . addslashes($this->cdr_variable['sip_via_rport']) . "',";
            $data .= "switch_r_sdp = '" . addslashes($this->cdr_variable['switch_r_sdp']) . "',";

            $causeQ850 = $this->cdr_variable['hangup_cause_q850'];

            if ($causeQ850 == '487') {
                $data .= "endpoint_disposition = 'ORIGINATOR_CANCEL',";
            } else {
                $data .= "endpoint_disposition = '" . addslashes($this->cdr_variable['endpoint_disposition']) . "',";
            }
            $data .= "effective_caller_id_name = '" . addslashes($this->cdr_variable['effective_caller_id_name']) . "',";
            $data .= "effective_caller_id_number = '" . addslashes($this->cdr_variable['effective_caller_id_number']) . "',";
            $data .= "digits_dialed = '" . addslashes($this->cdr_variable['digits_dialed']) . "',";
            $data .= "start_stamp = '" . addslashes($this->cdr_variable['start_stamp']) . "',";
            $data .= "profile_start_stamp = '" . addslashes($this->cdr_variable['profile_start_stamp']) . "',";
            $data .= "end_stamp = '" . addslashes($this->cdr_variable['end_stamp']) . "',";
            $data .= "caller_id = '" . addslashes($this->cdr_variable['caller_id']) . "',";
            $data .= "duration = '" . addslashes($this->cdr_variable['duration']) . "',";
            $data .= "billsec = '" . addslashes($this->cdr_variable['billsec']) . "',";
            $data .= "progresssec = '" . addslashes($this->cdr_variable['progresssec']) . "',";
            $data .= "answersec = '" . addslashes($this->cdr_variable['answersec']) . "',";
            $data .= "waitsec = '" . addslashes($this->cdr_variable['waitsec']) . "',";
            $data .= "flow_billsec = '" . addslashes($this->cdr_variable['flow_billsec']) . "',";

            $data .= "sip_hangup_disposition = '" . addslashes($this->cdr_variable['sip_hangup_disposition']) . "',";
            $data .= "in_useragent= '" . addslashes($this->cdr_variable['in_useragent']) . "',";
            $data .= "out_useragent= '" . addslashes($this->cdr_variable['out_useragent']) . "',";
            $data .= "in_rtp_audio_in_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_in_media_bytes']) . "',";
            $data .= "in_rtp_audio_out_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_out_media_bytes']) . "',";
            $data .= "out_rtp_audio_in_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_in_media_bytes']) . "',";
            $data .= "out_rtp_audio_out_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_out_media_bytes']) . "',";
            $data .= "rtp_audio_in_media_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_media_packet_count']) . "',	";
            $data .= "rtp_audio_out_media_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_out_media_packet_count']) . "',";
            $data .= "in_rtp_audio_in_jitter_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']) . "',";
            $data .= "out_rtp_audio_in_jitter_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']) . "',";
            $data .= "in_rtp_audio_in_skip_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']) . "',	";
            $data .= "out_rtp_audio_in_skip_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']) . "',	";
            $data .= "in_rtp_audio_in_jitter_min_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']) . "',";
            $data .= "in_rtp_audio_in_jitter_max_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']) . "',";
            $data .= "out_rtp_audio_in_jitter_min_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']) . "',";
            $data .= "out_rtp_audio_in_jitter_max_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']) . "',";
            $data .= "in_rtp_audio_in_mos= '" . addslashes($this->cdr_variable['rtp_audio_in_mos']) . "',";
            $data .= "out_rtp_audio_in_mos= '" . addslashes($this->cdr_variable['rtp_audio_in_mos']) . "', ";

            $fscause = $this->cdr_variable['bridge_hangup_cause'];
            $causeSIP = $this->cdr_variable['sip_term_status'];
            $data .= "fscause = '" . addslashes($fscause) . "',";
            $data .= "Q850CODE = '" . addslashes($causeQ850) . "',";
            $data .= "SIPCODE = '" . addslashes($causeSIP) . "',";
            $data .= "carrier_dialplan_id = '" . addslashes($this->carrierdata['dialplan_id']) . "',";
            $data .= "carrier_id = '" . addslashes($this->carrierdata['carrier_id']) . "',";
            $data .= "hangupby = '" . addslashes($this->hangupby) . "',";
            $data .= "customer_incodecs = '" . $this->cdr_variable['USERCODECSCLIST'] . "',";
            $data .= "carrier_outcodecs = '" . $this->carrierdata['carrier_codecs'] . "',";
            $data .= "call_codecs = '" . $this->cdr_variable['write_codec'] . "',";

            if ($this->hangupby == 'CALLEE')
                $this->cdr_variable["disposition_cause"] = 'Call disconnected by Callee';
            else
                $this->cdr_variable["disposition_cause"] = 'Call disconnected by Caller';

            $data .= "disposition_cause = '" . $this->cdr_variable['disposition_cause'] . "',";
            $data .= "disposition  = 'ANSWRED',";
            $data .= "cdr_type = 'EXTEN',";

            if (strlen(trim($this->cdr_variable['cc_queue'])) > 0) {
                $data .= "endpoint_number = '" . addslashes($this->cdr_variable['cc_agent_code']) . "',";
                $data .= "endpoint_extension_no = '" . addslashes($this->cdr_variable['cc_extension_no']) . "',";
                $data .= "endpoint_extension_id = '" . addslashes($this->cdr_variable['cc_extension_id']) . "',";
                $data .= "endpoint_app = '" . 'QUEUE' . "',";
                $data .= "endpoint_name = '" . addslashes($this->cdr_variable['cc_agent_displayname']) . "',";
            } else {
                $data .= "endpoint_number = '" . addslashes($this->cdr_variable['endpoint_number']) . "',";
                $data .= "endpoint_extension_no = '" . addslashes($this->cdr_variable['endpoint_extension_no']) . "',";
                $data .= "endpoint_extension_id = '" . addslashes($this->cdr_variable['endpoint_extension_id']) . "',";
                $data .= "endpoint_app = '" . addslashes($this->cdr_variable['dst_app']) . "',";
                $data .= "endpoint_name = '" . addslashes($this->cdr_variable['endpoint_name']) . "',";
            }

            $data .= "dst_extension_id = '" . addslashes($this->cdr_variable['dst_extension_id']) . "',";
            $data .= "dst_app = '" . addslashes($this->cdr_variable['dst_app']) . "',";
            $data .= "dst_app_number = '" . addslashes($this->cdr_variable['dst_app_number']) . "',";
            $data .= "dst_app_name = '" . addslashes($this->cdr_variable['dst_app_name']) . "',";

            $data .= "src_extension_id = '" . addslashes($this->cdr_variable['src_extension_id']) . "',";
            $data .= "src_extension_no = '" . addslashes($this->cdr_variable['src_extension_no']) . "',";
            $data .= "src_extension_name = '" . addslashes($this->cdr_variable['src_extension_name']) . "',";

            if ($this->cdr_variable['billsec'] > 0) {


                if (strlen($this->cdr_variable['cc_queue']) > 1) {
                    $recording_file = $this->cdr_variable['cc_record_filename'];
                } else {
                    $recording_file = $this->cdr_variable['recording_file'];
                }


                if (strlen($recording_file) > 0) {
                    chmod($recording_file, '0777');
                    $this->writelog("Recording File ------- " . $recording_file);
                    sleep(2);
                    $size = 0;
                    $data .= "recording_file = '" . addslashes($recording_file) . "',";
                    $size = filesize($recording_file);
                    if ($size > 0) {
                        
                    } else {
                        
                    }
                }
            } else {
                
            }



            $cdrdata = rtrim($data, ',');
            $query = "insert into " . $dbdate . "_ratedcdr  set " . $cdrdata;
            $this->writelog("Extension Connected calls .... " . $query);
            $this->query('CDR', $query);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $query);
                $this->execute();
            }
        } else {

            if (strlen(trim($this->cdr_variable['fs_errorcode'])) == 0) {
                $this->cdr_variable['fs_errorcode'] = $this->cdr_variable['hangup_cause'];
            }
            $failcallsql .= "fs_errorcode = '" . addslashes($this->cdr_variable['fs_errorcode']) . "',";
            $failcallsql .= "carrier_ratecard_id = '" . addslashes($this->carrierdata['ratecard_id']) . "',";
            $failcallsql .= "carrier_tariff_id = '" . addslashes($this->carrierdata['tariff_id']) . "',";
            $failcallsql .= "carrier_prefix = '" . addslashes($this->carrierdata['prefix']) . "',";
            $failcallsql .= "carrier_destination = '" . addslashes($this->carrierdata['destination']) . "',";
            $failcallsql .= "carrier_ipaddress = '" . addslashes($this->carrierdata['ipaddress']) . "',";
            $failcallsql .= "carrier_ipaddress_name = '" . addslashes($this->carrierdata['ipaddress_name']) . "',";
            $failcallsql .= "carrier_src_caller = '" . addslashes($this->carrierdata['src_caller']) . "',";
            $failcallsql .= "carrier_src_callee = '" . addslashes($this->carrierdata['src_callee']) . "',";
            $failcallsql .= "carrier_dst_caller = '" . addslashes($this->carrierdata['dst_caller']) . "',";
            $failcallsql .= "carrier_dst_callee = '" . addslashes($this->carrierdata['dst_callee']) . "',";
            $failcallsql .= "customer_tariff_id = '" . addslashes($this->userdata['tariff_id']) . "',";
            $failcallsql .= "customer_ipaddress = '" . addslashes($this->userdata['ipaddress']) . "',";
            $failcallsql .= "customer_ratecard_id = '" . addslashes($this->userdata['ratecard_id']) . "',";
            $failcallsql .= "customer_prefix = '" . addslashes($this->userdata['prefix']) . "',";
            $failcallsql .= "customer_destination = '" . addslashes($this->userdata['destination']) . "',";
            $failcallsql .= "customer_src_caller = '" . addslashes($this->userdata['src_caller']) . "',";
            $failcallsql .= "customer_src_callee = '" . addslashes($this->userdata['src_callee']) . "',";
            $failcallsql .= "customer_src_ip = '" . addslashes($this->userdata['src_ip']) . "',";
            $failcallsql .= "customer_account_id = '" . addslashes($this->userdata['account_id']) . "',";
            $failcallsql .= "reseller1_account_id = '" . addslashes($this->reseller1_data['account_id']) . "',";
            $failcallsql .= "reseller1_tariff_id = '" . addslashes($this->reseller1_data['tariff_id']) . "',";
            $failcallsql .= "reseller1_ratecard_id = '" . addslashes($this->reseller1_data['ratecard_id']) . "',";
            $failcallsql .= "reseller1_prefix = '" . addslashes($this->reseller1_data['prefix']) . "',";
            $failcallsql .= "reseller1_destination = '" . addslashes($this->reseller1_data['destination']) . "',";
            $failcallsql .= "reseller2_destination = '" . addslashes($this->reseller2_data['destination']) . "',";
            $failcallsql .= "reseller2_account_id = '" . addslashes($this->reseller2_data['account_id']) . "',";
            $failcallsql .= "reseller2_tariff_id = '" . addslashes($this->reseller2_data['tariff_id']) . "',";
            $failcallsql .= "reseller2_ratecard_id = '" . addslashes($this->reseller2_data['ratecard_id']) . "',";
            $failcallsql .= "reseller2_prefix = '" . addslashes($this->reseller2_data['prefix']) . "',";
            $failcallsql .= "reseller3_account_id = '" . addslashes($this->reseller3_data['account_id']) . "',";
            $failcallsql .= "reseller3_tariff_id = '" . addslashes($this->reseller3_data['tariff_id']) . "',";
            $failcallsql .= "reseller3_ratecard_id = '" . addslashes($this->reseller3_data['ratecard_id']) . "',";
            $failcallsql .= "reseller3_prefix = '" . addslashes($this->reseller3_data['prefix']) . "',";
            $failcallsql .= "reseller3_destination = '" . addslashes($this->reseller3_data['destination']) . "',";
            $failcallsql .= "uuid = '" . addslashes($this->cdr_variable['uuid']) . "',";
            $failcallsql .= "sip_from_user  = '" . addslashes($this->cdr_variable['sip_from_user']) . "',";
            $failcallsql .= "sip_from_uri  = '" . addslashes($this->cdr_variable['sip_from_uri']) . "',";
            $failcallsql .= "sip_from_host = '" . addslashes($this->cdr_variable['sip_from_host']) . "',";
            $failcallsql .= "channel_name = '" . addslashes($this->cdr_variable['channel_name']) . "',";
            $failcallsql .= "common_uuid = '" . addslashes($this->cdr_variable['common_uuid']) . "',";
            $failcallsql .= "caller_callid = '" . addslashes($this->cdr_variable['caller_callid']) . "',";
            $failcallsql .= "callee_callid = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
            $failcallsql .= "sip_call_id = '" . addslashes($this->cdr_variable['sip_call_id']) . "',";
            $failcallsql .= "ep_codec_string  = '" . addslashes($this->cdr_variable['ep_codec_string']) . "',";
            $failcallsql .= "sip_network_ip = '" . addslashes($this->cdr_variable['sip_network_ip']) . "',";
            $failcallsql .= "sip_received_ip = '" . addslashes($this->cdr_variable['sip_received_ip']) . "',";
            $failcallsql .= "sip_received_port = '" . addslashes($this->cdr_variable['sip_received_port']) . "',";
            $failcallsql .= "sip_via_protocol = '" . addslashes($this->cdr_variable['sip_via_protocol']) . "',";
            $failcallsql .= "sip_from_user_stripped = '" . addslashes($this->cdr_variable['sip_from_user_stripped']) . "',";
            $failcallsql .= "sip_from_display = '" . addslashes($this->cdr_variable['sip_from_display']) . "',";
            $failcallsql .= "sip_full_from = '" . addslashes($this->cdr_variable['sip_full_from']) . "',";
            $failcallsql .= "sip_to_display = '" . addslashes($this->cdr_variable['sip_to_display']) . "',";
            $failcallsql .= "sip_full_to = '" . addslashes($this->cdr_variable['sip_full_to']) . "',";
            $failcallsql .= "sip_req_user = '" . addslashes($this->cdr_variable['sip_req_user']) . "',";
            $failcallsql .= "sip_req_uri = '" . addslashes($this->cdr_variable['sip_req_uri']) . "',";
            $failcallsql .= "sip_to_user = '" . addslashes($this->cdr_variable['sip_to_user']) . "',";
            $failcallsql .= "sip_contact_user = '" . addslashes($this->cdr_variable['sip_contact_user']) . "',";
            $failcallsql .= "sip_contact_port = '" . addslashes($this->cdr_variable['sip_contact_port']) . "',";
            $failcallsql .= "sip_contact_uri = '" . addslashes($this->cdr_variable['sip_contact_uri']) . "',";
            $failcallsql .= "sip_contact_host = '" . addslashes($this->cdr_variable['sip_contact_host']) . "',";
            $failcallsql .= "rtp_use_codec_string = '" . addslashes($this->cdr_variable['rtp_use_codec_string']) . "',";
            $failcallsql .= "sip_user_agent = '" . addslashes($this->cdr_variable['sip_user_agent']) . "',";
            $failcallsql .= "sip_via_host = '" . addslashes($this->cdr_variable['sip_via_host']) . "',";
            $failcallsql .= "sip_via_port = '" . addslashes($this->cdr_variable['sip_via_port']) . "',";
            $failcallsql .= "sip_via_rport = '" . addslashes($this->cdr_variable['sip_via_rport']) . "',";
            $failcallsql .= "switch_r_sdp = '" . addslashes($this->cdr_variable['switch_r_sdp']) . "',";

            $failcallsql .= "start_time = '" . addslashes($this->cdr_variable['start_stamp']) . "',";
            $failcallsql .= "end_time = '" . addslashes($this->cdr_variable['end_stamp']) . "',";
            $failcallsql .= "effective_caller_id_name = '" . addslashes($this->cdr_variable['effective_caller_id_name']) . "',";
            $failcallsql .= "effective_caller_id_number = '" . addslashes($this->cdr_variable['effective_caller_id_number']) . "',";
            $failcallsql .= "digits_dialed = '" . addslashes($this->cdr_variable['digits_dialed']) . "',";
            $failcallsql .= "start_stamp = '" . addslashes($this->cdr_variable['start_stamp']) . "',";
            $failcallsql .= "profile_start_stamp = '" . addslashes($this->cdr_variable['profile_start_stamp']) . "',";
            $failcallsql .= "end_stamp = '" . addslashes($this->cdr_variable['end_stamp']) . "',";
            $failcallsql .= "caller_id = '" . addslashes($this->cdr_variable['caller_id']) . "',";
            $failcallsql .= "duration = '" . addslashes($this->cdr_variable['duration']) . "',";
            $failcallsql .= "billsec = '" . addslashes($this->cdr_variable['billsec']) . "',";
            $failcallsql .= "progresssec = '" . addslashes($this->cdr_variable['progresssec']) . "',";
            $failcallsql .= "answersec = '" . addslashes($this->cdr_variable['answersec']) . "',";
            $failcallsql .= "waitsec = '" . addslashes($this->cdr_variable['waitsec']) . "',";
            $failcallsql .= "flow_billsec = '" . addslashes($this->cdr_variable['flow_billsec']) . "',";
            $failcallsql .= "sip_hangup_disposition = '" . addslashes($this->cdr_variable['sip_hangup_disposition']) . "',";
            $failcallsql .= "in_useragent= '" . addslashes($this->cdr_variable['in_useragent']) . "',";
            $failcallsql .= "out_useragent= '" . addslashes($this->cdr_variable['out_useragent']) . "',";
            $failcallsql .= "in_rtp_audio_in_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_in_media_bytes']) . "',";
            $failcallsql .= "in_rtp_audio_out_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_out_media_bytes']) . "',";
            $failcallsql .= "out_rtp_audio_in_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_in_media_bytes']) . "',";
            $failcallsql .= "out_rtp_audio_out_media_bytes= '" . addslashes($this->cdr_variable['rtp_audio_out_media_bytes']) . "',";
            $failcallsql .= "rtp_audio_in_media_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_media_packet_count']) . "',	";
            $failcallsql .= "rtp_audio_out_media_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_out_media_packet_count']) . "',";
            $failcallsql .= "in_rtp_audio_in_jitter_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']) . "',";
            $failcallsql .= "out_rtp_audio_in_jitter_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']) . "',";
            $failcallsql .= "in_rtp_audio_in_skip_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']) . "',	";
            $failcallsql .= "out_rtp_audio_in_skip_packet_count= '" . addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']) . "',	";
            $failcallsql .= "in_rtp_audio_in_jitter_min_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']) . "',";
            $failcallsql .= "in_rtp_audio_in_jitter_max_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']) . "',";
            $failcallsql .= "out_rtp_audio_in_jitter_min_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']) . "',";
            $failcallsql .= "out_rtp_audio_in_jitter_max_variance= '" . addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']) . "',";
            $failcallsql .= "in_rtp_audio_in_mos= '" . addslashes($this->cdr_variable['rtp_audio_in_mos']) . "',";
            $failcallsql .= "out_rtp_audio_in_mos= '" . addslashes($this->cdr_variable['rtp_audio_in_mos']) . "', ";

            $failcallsql .= "disposition  = 'MISSCALL',";
            $causeQ850 = $this->cdr_variable['hangup_cause_q850'];
            if (strlen(trim($this->cdr_variable['originate_disposition'])) > 0) {
                $failcallsql .= "endpoint_disposition = '" . addslashes($this->cdr_variable['originate_disposition']) . "',";
                $fscause = $this->cdr_variable['originate_disposition'];
            } else {
                if ($causeQ850 == '487') {
                    $failcallsql .= "endpoint_disposition = 'ORIGINATOR_CANCEL',";
                    $fscause = 'ORIGINATOR_CANCEL';
                } else {
                    $failcallsql .= "endpoint_disposition = '" . addslashes($this->cdr_variable['endpoint_disposition']) . "',";
                }
            }

            $causeSIP = $this->cdr_variable['sip_term_status'];
            $failcallsql .= "fscause = '" . addslashes($fscause) . "',";
            $failcallsql .= "Q850CODE = '" . addslashes($causeQ850) . "',";
            $failcallsql .= "SIPCODE = '" . addslashes($causeSIP) . "',";
            $failcallsql .= "carrier_dialplan_id = '" . addslashes($this->carrierdata['dialplan_id']) . "',";
            $failcallsql .= "carrier_id = '" . addslashes($this->carrierdata['carrier_id']) . "',";
            $failcallsql .= "hangupby = '" . addslashes($this->hangupby) . "',";
            $failcallsql .= "customer_incodecs = '" . $this->cdr_variable['USERCODECSCLIST'] . "',";
            $failcallsql .= "carrier_outcodecs = '" . $this->carrierdata['carrier_codecs'] . "',";
            $failcallsql .= "call_codecs = '" . $this->cdr_variable['write_codec'] . "',";
            $failcallsql .= "customer_company_name = '" . $this->userdata['company_name'] . "',";
            if ($this->hangupby == 'CALLEE')
                $this->cdr_variable["disposition_cause"] = 'Call disconnected by Callee';
            else
                $this->cdr_variable["disposition_cause"] = 'Call disconnected by Caller';

            $failcallsql .= "disposition_cause = '" . $this->cdr_variable["disposition_cause"] . "',";

            $failcallsql .= "cdr_type = 'EXTEN',";

            if (strlen(trim($this->cdr_variable['cc_queue'])) > 0) {
                $failcallsql .= "endpoint_number = '" . addslashes($this->cdr_variable['cc_agent_code']) . "',";
                $failcallsql .= "endpoint_extension_no = '" . addslashes($this->cdr_variable['cc_extension_no']) . "',";
                $failcallsql .= "endpoint_extension_id = '" . addslashes($this->cdr_variable['cc_extension_id']) . "',";
                $failcallsql .= "endpoint_app = '" . 'QUEUE' . "',";
                $failcallsql .= "endpoint_name = '" . addslashes($this->cdr_variable['cc_agent_displayname']) . "',";
            } else {
                $failcallsql .= "endpoint_number = '" . addslashes($this->cdr_variable['endpoint_number']) . "',";
                $failcallsql .= "endpoint_extension_no = '" . addslashes($this->cdr_variable['endpoint_extension_no']) . "',";
                $failcallsql .= "endpoint_extension_id = '" . addslashes($this->cdr_variable['endpoint_extension_id']) . "',";
                $failcallsql .= "endpoint_app = '" . addslashes($this->cdr_variable['dst_app']) . "',";
                $failcallsql .= "endpoint_name = '" . addslashes($this->cdr_variable['endpoint_name']) . "',";
            }
            $failcallsql .= "dst_extension_id = '" . addslashes($this->cdr_variable['dst_extension_id']) . "',";
            $failcallsql .= "dst_app = '" . addslashes($this->cdr_variable['dst_app']) . "',";
            $failcallsql .= "dst_app_number = '" . addslashes($this->cdr_variable['dst_app_number']) . "',";
            $failcallsql .= "dst_app_name = '" . addslashes($this->cdr_variable['dst_app_name']) . "',";
            $failcallsql .= "src_extension_id = '" . addslashes($this->cdr_variable['src_extension_id']) . "',";
            $failcallsql .= "src_extension_no = '" . addslashes($this->cdr_variable['src_extension_no']) . "',";
            $failcallsql .= "src_extension_name = '" . addslashes($this->cdr_variable['src_extension_name']) . "',";

            $failcallsql = rtrim($failcallsql, ',');

            $failcallsql_IN = 'insert into ' . $dbdate . "_ratedcdr  set " . $failcallsql;
            $this->writelog("------------------------ Exten Fail Calls " . $failcallsql_IN);
            $this->query('CDR', $failcallsql_IN);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $failcallsql_IN);
                $this->execute();
            }
        }
    }

    function cdr_process($data) {

        $this->fscause = $this->cdr_variable['hangup_cause'];
        $dbdate = date('Ym');
        $dbdate = date('Ym', strtotime("+0 day", strtotime($this->cdr_variable['end_stamp'])));
        if ($this->carrierdata['dp'] == '' or $this->carrierdata['dp'] == null)
            $this->carrierdata['dp'] = 4;
        $data["uuid"] = addslashes($this->cdr_variable['uuid']);
        $data["billsec"] = addslashes($this->cdr_variable['billsec']);

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

        if ($this->carrierdata['ratio'] == 0)
            $this->carrierdata['ratio'] = 1;
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
        $this->sip_term_cause = 0;
        $this->sip_term_cause = (int) $this->cdr_variable['sip_term_cause'];
        $log = "  sip_term_cause " . $this->sip_term_cause;
        $this->writelog($log);
        if ($this->cdr_variable['sip_term_cause'] > 0) {
            $causeSIP = $this->sip_term_cause;
            $causeQ850 = $this->cdr_variable['hangup_cause_q850'];
            $fscause = $this->cdr_variable['hangup_cause'];
            $log = "causeSIP $causeSIP causeQ850 $causeQ850 fscause $fscause ";
            $this->writelog($log);
            if ($this->cdr_variable['sip_hangup_disposition'] == 'recv_cancel') {
                $this->hangupby = "CALLER";
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_bye') {
                if ($this->leg == 'A')
                    $this->hangupby = "CALLEE";
                else
                    $this->hangupby = "CALLER";
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'recv_bye') {
                if ($this->leg == 'A')
                    $this->hangupby = "CALLER";
                else
                    $this->hangupby = "CALLEE";
            } elseif ($this->cdr_variable['sip_hangup_disposition'] == 'send_refuse') {
                if ($this->leg == 'A')
                    $this->hangupby = "CALLEE";
                else
                    $this->hangupby = "CALLEE";
            }

            if ($this->leg == 'A') {
                if ($causeSIP == '487') {
                    $this->hangupby = 'CALLER';
                }
            }
            if ($this->leg == 'B') {
                $causeSIP = $this->cdr_variable['sip_term_status'];
                if (strlen($this->cdr_variable['endpoint_disposition']) == 0)
                    $this->cdr_variable['endpoint_disposition'] = $this->cdr_variable['hangup_cause'];

                $carrierarray_hangup = Array('401', '403', '404', '488', '486', '408', '480', '480', '603', '410', '480', '483', '484', '501', '502', '503', '500', '601', '602', '603', '604', '605', '607', '609');
                if (in_array(trim($causeSIP), $carrierarray_hangup)) {
                    $this->hangupby = 'CALLEE';
                }
            }

            $log = "causeSIP $causeSIP causeQ850 $causeQ850 fscause $fscause hangupby " . $this->hangupby;
            $this->writelog($log);
        } else {
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
        }
        $customer_actual_callcost = $customer_callcost_total;
        $reseller1_actual_callcost = $reseller1_callcost_total;
        $reseller2_actual_callcost = $reseller2_callcost_total;
        $reseller3_actual_callcost = $reseller3_callcost_total;

        if ($this->switch_issue == '0' and addslashes($this->cdr_variable['billsec']) > 0) {
            $yearmonth = date('Ym');
            // Customer Bundle
            $b_value = 0;
            $sub_used = 0;

            $bundle_for = '';
            if ($this->userdata['pstncallflow'] == 'PSTN')
                $bundle_for = " and bundle_for = 'OUT'";
            if ($this->userdata['didcallflow'] == 'DID')
                $bundle_for = " and bundle_for = 'IN'";

            $this->writelog($bundle_for);

            $customer_callcost_total_org = $customer_callcost_total;
            $this->writelog($this->userdata['bundle_type_minute'] . " -------- " . $this->userdata['bundle_value_minute']);
            if ($this->userdata['bundle_type_minute'] == 'MINUTE' and $this->userdata['bundle_value_minute'] > 0 and $customer_callcost_total > 0) {
                if ($customer_duration <= $this->userdata['bundle_value_minute']) {
                    $b_value = ceil($customer_duration);
                    $customer_callcost_total = 0;
                } else {
                    $b_value = $this->userdata['bundle_value_minute'];
                    $remaining_duration = $customer_duration - ($this->userdata['bundle_value_minute'] );
                    $customer_callcost_total = ($customer_callcost_total / $customer_duration) * $remaining_duration;
                }
                $cdr_date = date("Ym");
                $dstarray = str_split($this->userdata['bundle_prefix_number']);
                $tmpstr = '';
                $str = '';
                foreach ($dstarray as $key => $value) {
                    $tmpstr .= $value;
                    $str .= "prefix = '" . $tmpstr . "' or ";
                }
                $str = rtrim($str, ' or ');

                if (strlen(trim($bundle_for)) > 0) {
                    $query = sprintf("SELECT customer_bundle_sdr.id, account_id, customer_bundle_sdr.bundle_package_id, bundle_package_name, total_allowed_sec, sdr_consumption from customer_bundle_sdr INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id where customer_bundle_sdr.account_id  = '%s' and   (%s)  and   (total_allowed_sec  - sdr_consumption   > 0 )  %s ;", $this->userdata['account_id'], $str, $bundle_for);
                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $rs3 = $this->resultset();
                    if (count($rs3) > 0) {
                        $sub_used = $b_value;
                        foreach ($rs3 as $data_b) {
                            if ($sub_used > 0) {
                                if ($b_value <= ($data_b['total_allowed_sec'] - $data_b['sdr_consumption'])) {
                                    $sub_used = $b_value;
                                    $b_value = 0;
                                } else {
                                    $sub_used = $data_b['total_allowed_sec'] - $data_b['sdr_consumption'];
                                    $b_value = $b_value - $sub_used;
                                }
                                $query = sprintf("update customer_bundle_sdr  set sdr_consumption =  sdr_consumption + %s where id = '%s'", $sub_used, $data_b['id']);
                                $this->writelog($query);
                                $this->query('SWITCH', $query);
                                $this->execute();
                            }
                        }
                    }
                } else {
                    $customer_callcost_total = $customer_callcost_total_org;
                }
            }
            // Reseller 1 Bundle
            $sub_used = 0;
            $b_value = 0;
            $reseller1_callcost_total_org = $reseller1_callcost_total;
            if ($this->reseller1_data['bundle_type_minute'] == 'MINUTE' and $this->reseller1_data['bundle_value_minute'] > 0 and $reseller1_callcost_total > 0) {
                if ($reseller1_duartion <= $this->reseller1_data['bundle_value_minute']) {
                    $b_value = ceil($reseller1_duartion);
                    $reseller1_callcost_total = 0;
                } else {
                    $b_value = $this->reseller1_data['bundle_value_minute'];
                    $remaining_duration = $reseller1_duartion - $this->reseller1_data['bundle_value_minute'];
                    $reseller1_callcost_total = ($reseller1_callcost_total / $reseller1_duartion) * $remaining_duration;
                }
                $dstarray = str_split($this->reseller1_data['bundle_prefix_number']);
                $tmpstr = '';
                $str = '';
                foreach ($dstarray as $key => $value) {
                    $tmpstr .= $value;
                    $str .= "prefix = '" . $tmpstr . "' or ";
                }
                $str = rtrim($str, ' or ');
                $cdr_date = date("Ym");

                if (strlen(trim($bundle_for)) > 0) {
                    $query = sprintf("SELECT customer_bundle_sdr.id, account_id, customer_bundle_sdr.bundle_package_id, bundle_package_name, total_allowed_sec, sdr_consumption from customer_bundle_sdr INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id where customer_bundle_sdr.account_id  = '%s' and   (%s)  and   (total_allowed_sec  - sdr_consumption   > 0 ) %s;", $this->reseller1_data['account_id'], $str, $bundle_for);
                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $rs3 = $this->resultset();
                    if (count($rs3) > 0) {
                        $sub_used = $b_value;
                        foreach ($rs3 as $data_b) {
                            if ($sub_used > 0) {
                                if ($b_value <= $data_b['total_allowed_sec'] - $data_b['sdr_consumption']) {
                                    $sub_used = $b_value;
                                    $b_value = 0;
                                } else {
                                    $sub_used = $data_b['total_allowed_sec'] - $data_b['sdr_consumption'];
                                    $b_value = $b_value - $sub_used;
                                }
                                $query = sprintf("update customer_bundle_sdr  set sdr_consumption =  sdr_consumption + %s where id = '%s'", $sub_used, $data_b['id']);
                                $this->writelog($query);
                                $this->query('SWITCH', $query);
                                $this->execute();
                            }
                        }
                    }
                } else {
                    $reseller1_callcost_total = $reseller1_callcost_total_org;
                }
            }

            // Reseller 2nd level Bundle calculation
            $sub_used = 0;
            $b_value = 0;
            $reseller2_callcost_total_org = $reseller2_callcost_total;
            if ($this->reseller2_data['bundle_type_minute'] == 'MINUTE' and $this->reseller2_data['bundle_value_minute'] > 0 and $reseller2_callcost_total > 0) {
                if ($reseller2_duartion <= $this->reseller2_data['bundle_value_minute']) {
                    $b_value = ceil($reseller2_duartion);

                    $reseller2_callcost_total = 0;
                } else {
                    $b_value = $this->reseller2_data['bundle_value_minute'];
                    $remaining_duration = $reseller2_duartion - $this->reseller2_data['bundle_value_minute'];
                    $reseller2_callcost_total = ($reseller2_callcost_total / $reseller2_duartion) * $remaining_duration;
                }
                $dstarray = str_split($this->reseller2_data['bundle_prefix_number']);
                $tmpstr = '';
                $str = '';
                foreach ($dstarray as $key => $value) {
                    $tmpstr .= $value;
                    $str .= "prefix = '" . $tmpstr . "' or ";
                }
                $str = rtrim($str, ' or ');
                $cdr_date = date("Ym");

                if (strlen(trim($bundle_for)) > 0) {
                    $query = sprintf("SELECT customer_bundle_sdr.id, account_id, customer_bundle_sdr.bundle_package_id, bundle_package_name, total_allowed_sec, sdr_consumption from customer_bundle_sdr INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id where customer_bundle_sdr.account_id  = '%s' and   (%s)  and   (total_allowed_sec  - sdr_consumption   > 0 ) %s;", $this->reseller2_data['account_id'], $str, $bundle_for);
                    $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $rs3 = $this->resultset();
                    if (count($rs3) > 0) {
                        foreach ($rs3 as $data_b) {
                            $sub_used = $b_value;
                            if ($sub_used > 0) {
                                if ($b_value <= $data_b['total_allowed_sec'] - $data_b['sdr_consumption']) {
                                    $sub_used = $b_value;
                                    $b_value = 0;
                                } else {
                                    $sub_used = $data_b['total_allowed_sec'] - $data_b['sdr_consumption'];
                                    $b_value = $b_value - $sub_used;
                                }
                                $query = sprintf("update customer_bundle_sdr  set sdr_consumption =  sdr_consumption + %s where id = '%s'", $sub_used, $data_b['id']);
                                $this->writelog($query);
                                $this->query('SWITCH', $query);
                                $this->execute();
                            }
                        }
                    }
                } else {
                    $reseller2_callcost_total = $reseller2_callcost_total_org;
                }
            }

            // Reseller 3rd level Bundle
            $sub_used = 0;
            $b_value = 0;
            $reseller3_callcost_total_org = $reseller3_callcost_total;
            if ($this->reseller3_data['bundle_type_minute'] == 'MINUTE' and $this->reseller3_data['bundle_value_minute'] > 0 and $reseller3_callcost_total > 0) {
                if ($reseller3_duartion <= $this->reseller3_data['bundle_value_minute']) {
                    $b_value = ceil($reseller3_duartion);
                    $reseller3_callcost_total = 0;
                } else {
                    $b_value = $this->reseller3_data['bundle_value_minute'];
                    $remaining_duration = $reseller3_duartion - ($this->reseller3_data['bundle_value_minute'] );
                    $reseller3_callcost_total = ($reseller3_callcost_total / $reseller3_duartion) * $remaining_duration;
                }
                $dstarray = str_split($this->reseller3_data['bundle_prefix_number']);
                $tmpstr = '';
                $str = '';
                foreach ($dstarray as $key => $value) {
                    $tmpstr .= $value;
                    $str .= "prefix = '" . $tmpstr . "' or ";
                }
                $str = rtrim($str, ' or ');
                $cdr_date = date("Ym");

                $bundle_for = '';
                if ($this->switch_bill_cdrs == '1') {
                    $bundle_for = " and bundle_for = 'OUT' ";
                }
                if ($this->switch_bill_cdrs_incoming == '1') {
                    $bundle_for = " and bundle_for = 'IN' ";
                }
                $query = sprintf("SELECT customer_bundle_sdr.id, account_id, customer_bundle_sdr.bundle_package_id, bundle_package_name, total_allowed_sec, sdr_consumption from customer_bundle_sdr INNER JOIN bundle_package_prefixes on bundle_package_prefixes.bundle_package_id = customer_bundle_sdr.bundle_package_id where customer_bundle_sdr.account_id  = '%s' and   (%s)  and   (total_allowed_sec  - sdr_consumption   > 0 )  %s;", $this->reseller3_data['account_id'], $str, $bundle_for);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $rs3 = $this->resultset();
                if (count($rs3) > 0) {
                    $sub_used = $b_value;
                    foreach ($rs3 as $data_b) {
                        if ($sub_used > 0)
                            if ($b_value <= $data_b['total_allowed_sec'] - $data_b['sdr_consumption']) {
                                $sub_used = $b_value;
                                $b_value = 0;
                            } else {
                                $sub_used = $data_b['total_allowed_sec'] - $data_b['sdr_consumption'];
                                $b_value = $b_value - $sub_used;
                            }
                        $query = sprintf("update customer_bundle_sdr  set sdr_consumption =  sdr_consumption + %s where id = '%s'", $sub_used, $data_b['id']);
                        $this->writelog($query);
                        $this->query('SWITCH', $query);
                        $this->execute();
                    }
                } else {
                    $reseller3_callcost_total = $reseller3_callcost_total_org;
                }
            }
        }

        if ($this->cdr_variable['hangup_cause'] == 'PROGRESS_TIMEOUT') {
            $fscause = "PROGRESS_TIMEOUT";
        }

        if ($this->cdr_variable['billsec'] > 0) {


            if (strlen($this->cdr_variable['cc_queue']) > 1) {
                $recording_file = $this->cdr_variable['cc_record_filename'];
            } else {
                $recording_file = $this->cdr_variable['recording_file'];
            }


            if (strlen($recording_file) > 0) {
                chmod($recording_file, '0777');
                $this->writelog("Recording File ------- " . $recording_file);
                sleep(2);
                $size = 0;
                $data["recording_file"] = $recording_file;

                $size = filesize($recording_file);
                if ($size > 0) {
                    
                } else {
                    $data["recording_file"] = '';
                }
            }
        } else {
            $data["recording_file"] = '';
        }

        if ($this->cdr_variable['sip_term_status'] > 0) {
            $causeSIP = $this->cdr_variable['sip_term_status'];
        }

        $data["pdd"] = addslashes($pdd);
        $data["fscause"] = addslashes($fscause);
        $data["Q850CODE"] = addslashes($causeQ850);
        $data["SIPCODE"] = addslashes($causeSIP);
        $data["common_uuid"] = addslashes($this->cdr_variable['common_uuid']);
        $data["caller_callid"] = addslashes($this->cdr_variable['caller_callid']);
        $data["callee_callid"] = addslashes($this->cdr_variable['sip_call_id']);
        $data["reseller3_tax1_cost"] = addslashes($reseller3_tax1_cost);
        $data["reseller3_tax2_cost"] = addslashes($reseller3_tax2_cost);
        $data["reseller3_tax3_cost"] = addslashes($reseller3_tax3_cost);
        $data["reseller2_tax1_cost"] = addslashes($reseller2_tax1_cost);
        $data["reseller2_tax2_cost"] = addslashes($reseller2_tax2_cost);
        $data["reseller2_tax3_cost"] = addslashes($reseller2_tax3_cost);
        $data["reseller1_tax1_cost"] = addslashes($reseller1_tax1_cost);
        $data["reseller1_tax2_cost"] = addslashes($reseller1_tax2_cost);
        $data["reseller1_tax3_cost"] = addslashes($reseller1_tax3_cost);
        $data["customer_tax1_cost"] = addslashes($customer_tax1_cost);
        $data["customer_tax2_cost"] = addslashes($customer_tax2_cost);
        $data["customer_tax3_cost"] = addslashes($customer_tax3_cost);
        $data["carrier_tax1_cost"] = addslashes($carrier_tax1_cost);
        $data["carrier_tax2_cost"] = addslashes($carrier_tax2_cost);
        $data["carrier_tax3_cost"] = addslashes($carrier_tax3_cost);
        $data["carrier_callcost_total"] = addslashes($carrier_callcost_total);
        $data["carrier_duration"] = addslashes($carrier_duration);
        $data["customer_duration"] = addslashes($customer_duration);
        $data["reseller1_duration"] = addslashes($reseller1_duartion);
        $data["reseller2_duration"] = addslashes($reseller2_duartion);
        $data["reseller3_duration"] = addslashes($reseller3_duartion);
        $data["carrier_callcost"] = addslashes($carrier_cost);
        $data["customer_callcost"] = addslashes($customer_cost);
        $data["reseller1_callcost"] = addslashes($reseller1_cost);
        $data["reseller2_callcost"] = addslashes($reseller2_cost);
        $data["reseller3_callcost"] = addslashes($reseller3_cost);
        $data["start_time"] = addslashes($this->cdr_variable['start_stamp']);
        $data["answer_time"] = addslashes($this->cdr_variable['answer_stamp']);
        $data["end_time"] = addslashes($this->cdr_variable['end_stamp']);

        $data["hangupby"] = addslashes($this->hangupby);
        $data["customer_callcost_total"] = addslashes($customer_callcost_total);
        $data["reseller1_callcost_total"] = addslashes($reseller1_callcost_total);
        $data["reseller2_callcost_total"] = addslashes($reseller2_callcost_total);
        $data["reseller3_callcost_total"] = addslashes($reseller3_callcost_total);
        $data["customer_actual_callcost"] = addslashes($customer_actual_callcost);
        $data["reseller1_actual_callcost"] = addslashes($reseller1_actual_callcost);
        $data["reseller2_actual_callcost"] = addslashes($reseller2_actual_callcost);
        $data["reseller3_actual_callcost"] = addslashes($reseller3_actual_callcost);
        $data["carrier_ratio"] = addslashes($this->carrierdata['ratio']);
        $data["carrier_callcost_total_usercurrency"] = addslashes($carrier_callcost_total_usercurrency);
        $data["carrier_callcost_inclusive_usercurrency"] = addslashes($carrier_callcost_inclusive_usercurrency);
        $data["profit_usercurrency"] = addslashes($profit_usercurrency);
        $data["customer_balance"] = addslashes($this->userdata['orgbalance']);
        $data["reseller1_balance"] = addslashes($this->reseller1_data['orgbalance']);
        $data["reseller2_balance"] = addslashes($this->reseller2_data['orgbalance']);
        $data["reseller3_balance"] = addslashes($this->reseller3_data['orgbalance']);

        $data["customer_company_name"] = $this->userdata['company_name'];
        $data["customer_incodecs"] = $this->cdr_variable['USERCODECSCLIST'];
        $data["carrier_outcodecs"] = $this->carrierdata['carrier_codecs'];
        $data["call_codecs"] = $this->cdr_variable['write_codec'];
        $data["route"] = $this->cdr_variable['route'];

        if (CAMPAIGN) {
            $data["campaign_name"] = $this->cdr_variable['campaign_name'];
            $data["campaign_id"] = $this->cdr_variable['campaign_id'];
            $data["buyer_number"] = $this->cdr_variable['buyer_number'];

            $data["buyer_name"] = $this->cdr_variable['buyer_name'];
        }
        $data["did_number"] = $this->cdr_variable['did_number'];
        $data["billing_number"] = $this->carrierdata['billing_number'];
        $data["route_endpoint"] = $this->cdr_variable['route_endpoint'];
        $data["route_endpoint_name"] = $this->cdr_variable['route_endpoint_name'];
        $data["digits_dialed"] = addslashes($this->cdr_variable['digits_dialed']);
        $data["carrier_ratio"] = addslashes($this->carrierdata['ratio']);
        $data["carrier_callcost_total_usercurrency"] = addslashes($carrier_callcost_total_usercurrency);
        $data["profit_usercurrency"] = addslashes($profit_usercurrency);
        $data["uuid"] = addslashes($this->cdr_variable['uuid']);
        $data["sip_from_user"] = addslashes($this->cdr_variable['sip_from_user']);
        $data["sip_from_uri"] = addslashes($this->cdr_variable['sip_from_uri']);
        $data["sip_from_host"] = addslashes($this->cdr_variable['sip_from_host']);
        $data["channel_name"] = addslashes($this->cdr_variable['channel_name']);
        $data["common_uuid"] = addslashes($this->cdr_variable['common_uuid']);
        $data["caller_callid"] = addslashes($this->cdr_variable['caller_callid']);
        $data["callee_callid"] = addslashes($this->cdr_variable['sip_call_id']);
        $data["sip_call_id"] = addslashes($this->cdr_variable['sip_call_id']);
        $data["ep_codec_string"] = addslashes($this->cdr_variable['ep_codec_string']);
        $data["sip_network_ip"] = addslashes($this->cdr_variable['sip_network_ip']);
        $data["sip_received_ip"] = addslashes($this->cdr_variable['sip_received_ip']);
        $data["sip_received_port"] = addslashes($this->cdr_variable['sip_received_port']);
        $data["sip_via_protocol"] = addslashes($this->cdr_variable['sip_via_protocol']);
        $data["sip_from_user_stripped"] = addslashes($this->cdr_variable['sip_from_user_stripped']);
        $data["sip_from_display"] = addslashes($this->cdr_variable['sip_from_display']);
        $data["sip_full_from"] = addslashes($this->cdr_variable['sip_full_from']);
        $data["sip_to_display"] = addslashes($this->cdr_variable['sip_to_display']);
        $data["sip_full_to"] = addslashes($this->cdr_variable['sip_full_to']);
        $data["sip_req_user"] = addslashes($this->cdr_variable['sip_req_user']);
        $data["sip_req_uri"] = addslashes($this->cdr_variable['sip_req_uri']);
        $data["sip_to_user"] = addslashes($this->cdr_variable['sip_to_user']);
        $data["sip_contact_user"] = addslashes($this->cdr_variable['sip_contact_user']);
        $data["sip_contact_port"] = addslashes($this->cdr_variable['sip_contact_port']);
        $data["sip_contact_uri"] = addslashes($this->cdr_variable['sip_contact_uri']);
        $data["sip_contact_host"] = addslashes($this->cdr_variable['sip_contact_host']);
        $data["rtp_use_codec_string"] = addslashes($this->cdr_variable['rtp_use_codec_string']);
        $data["sip_user_agent"] = addslashes($this->cdr_variable['sip_user_agent']);
        $data["sip_via_host"] = addslashes($this->cdr_variable['sip_via_host']);
        $data["sip_via_port"] = addslashes($this->cdr_variable['sip_via_port']);
        $data["sip_via_rport"] = addslashes($this->cdr_variable['sip_via_rport']);
        $data["switch_r_sdp"] = addslashes($this->cdr_variable['switch_r_sdp']);
        $data["endpoint_disposition"] = addslashes($this->cdr_variable['endpoint_disposition']);
        $data["effective_caller_id_name"] = addslashes($this->cdr_variable['effective_caller_id_name']);
        $data["effective_caller_id_number"] = addslashes($this->cdr_variable['effective_caller_id_number']);
        $data["digits_dialed"] = addslashes($this->cdr_variable['digits_dialed']);
        $data["start_time"] = addslashes($this->cdr_variable['start_stamp']);
        $data["profile_start_stamp"] = addslashes($this->cdr_variable['profile_start_stamp']);
        $data["end_time"] = addslashes($this->cdr_variable['end_stamp']);
        $data["caller_id"] = addslashes($this->cdr_variable['caller_id']);
        $data["duration"] = addslashes($this->cdr_variable['duration']);
        $data["billsec"] = addslashes($this->cdr_variable['billsec']);
        $data["progresssec"] = addslashes($this->cdr_variable['progresssec']);
        $data["answersec"] = addslashes($this->cdr_variable['answersec']);
        $data["waitsec"] = addslashes($this->cdr_variable['waitsec']);
        $data["flow_billsec"] = addslashes($this->cdr_variable['flow_billsec']);
        $data["sip_hangup_disposition"] = addslashes($this->cdr_variable['sip_hangup_disposition']);
        $data["in_useragent"] = addslashes($this->cdr_variable['in_useragent']);
        $data["out_useragent"] = addslashes($this->cdr_variable['out_useragent']);
        $data["in_rtp_audio_in_media_bytes"] = addslashes($this->cdr_variable['rtp_audio_in_media_bytes']);
        $data["in_rtp_audio_out_media_bytes"] = addslashes($this->cdr_variable['rtp_audio_out_media_bytes']);
        $data["out_rtp_audio_in_media_bytes"] = addslashes($this->cdr_variable['rtp_audio_in_media_bytes']);
        $data["out_rtp_audio_out_media_bytes"] = addslashes($this->cdr_variable['rtp_audio_out_media_bytes']);
        $data["rtp_audio_in_media_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_media_packet_count']);
        $data["rtp_audio_out_media_packet_count"] = addslashes($this->cdr_variable['rtp_audio_out_media_packet_count']);
        $data["in_rtp_audio_in_jitter_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']);
        $data["out_rtp_audio_in_jitter_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']);
        $data["in_rtp_audio_in_skip_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']);
        $data["out_rtp_audio_in_skip_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']);
        $data["in_rtp_audio_in_jitter_min_variance"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']);
        $data["in_rtp_audio_in_jitter_max_variance"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']);
        $data["out_rtp_audio_in_jitter_min_variance"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']);
        $data["out_rtp_audio_in_jitter_max_variance"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']);
        $data["in_rtp_audio_in_mos"] = addslashes($this->cdr_variable['rtp_audio_in_mos']);
        $data["out_rtp_audio_in_mos"] = addslashes($this->cdr_variable['rtp_audio_in_mos']);
        $data["disposition"] = 'ANSWRED';

        if (strlen(trim($this->cdr_variable['cc_queue'])) > 0) {
            $data["endpoint_number"] = addslashes($this->cdr_variable['cc_agent_code']);
            $data["endpoint_extension_no"] = addslashes($this->cdr_variable['cc_extension_no']);
            $data["endpoint_extension_id"] = addslashes($this->cdr_variable['cc_extension_id']);
            $data["endpoint_app"] = 'QUEUE';
            $data["endpoint_name"] = addslashes($this->cdr_variable['cc_agent_displayname']);
        } else if ($this->cdr_variable['dst_type'] == 'RINGGROUP') {

            $this->cdr_variable['last_sent_callee_id_number'];

            $query = sprintf(" SELECT 'EXTEN' as app, extension_id, extension_no, name from customer_devices where username = '%s' limit 1;", $this->cdr_variable['last_sent_callee_id_number']);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $agentdata1 = $this->resultset();
            if (count($agentdata1) > 0) {
                foreach ($agentdata1 as $agentdata) {
                    $data["endpoint_number"] = addslashes($agentdata['extension_no']);
                    $data["endpoint_extension_no"] = addslashes($agentdata['extension_no']);
                    $data["endpoint_extension_id"] = addslashes($agentdata['extension_id']);
                    $data["endpoint_app"] = addslashes($agentdata['app']);
                    $data["endpoint_name"] = addslashes($agentdata['name']);
                }
            }
        } else {
            $data["endpoint_number"] = addslashes($this->cdr_variable['endpoint_number']);
            $data["endpoint_extension_no"] = addslashes($this->cdr_variable['endpoint_extension_no']);
            $data["endpoint_extension_id"] = addslashes($this->cdr_variable['endpoint_extension_id']);
            $data["endpoint_app"] = addslashes($this->cdr_variable['dst_app']);
            $data["endpoint_name"] = addslashes($this->cdr_variable['endpoint_name']);
        }





        $data["dst_extension_id"] = addslashes($this->cdr_variable['dst_extension_id']);
        $data["dst_app"] = addslashes($this->cdr_variable['dst_app']);
        $data["dst_app_number"] = addslashes($this->cdr_variable['dst_app_number']);
        $data["dst_app_name"] = addslashes($this->cdr_variable['dst_app_name']);

        $data["src_extension_id"] = addslashes($this->cdr_variable['src_extension_id']);
        $data["src_extension_no"] = addslashes($this->cdr_variable['src_extension_no']);
        $data["src_extension_name"] = addslashes($this->cdr_variable['src_extension_name']);

        $data["identity_in"] = addslashes($this->cdr_variable['sip_h_identity']);
        $data["attest_in"] = addslashes($this->cdr_variable['attest_in']);

        if ($this->cdr_variable['billsec'] > 0) {
            if ($this->hangupby == 'CALLEE')
                $data["disposition_cause"] = 'Call disconnected by Callee';
            else
                $data["disposition_cause"] = 'Call disconnected by Caller';
        }
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
                $data["cdr_type"] = 'IN';

                $cdrdata = "";
                foreach ($data as $key => $value) {
                    $cdrdata .= " $key='$value',";
                }

                $cdrdata = rtrim($cdrdata, ',');

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
                $data["cdr_type"] = 'OUT';

                $cdrdata = "";
                foreach ($data as $key => $value) {
                    $cdrdata .= " $key='$value',";
                }

                $cdrdata = rtrim($cdrdata, ',');
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

        if ($this->leg == 'A') {
            if ($causeSIP == '487') {
                $this->hangupby = 'CALLER';
            }
        }
        if ($this->leg == 'B') {
            $causeSIP = $this->cdr_variable['sip_term_status'];
            if (strlen($this->cdr_variable['endpoint_disposition']) == 0)
                $this->cdr_variable['endpoint_disposition'] = $this->cdr_variable['hangup_cause'];

            $carrierarray_hangup = Array('401', '403', '404', '488', '486', '408', '480', '480', '603', '410', '480', '483', '484', '501', '502', '503', '500', '601', '602', '603', '604', '605', '607', '609');
            if (in_array(trim($causeSIP), $carrierarray_hangup)) {
                $this->hangupby = 'CALLEE';
            }
        }

        $notes = "Carrier:" . $carrier_id . " Carrier IPAddress:" . $this->carrierdata['gateway_ipaddress'] . " [Responce Codes] SIP:" . $causeSIP . " Q850:" . $causeQ850 . ", $fscause \n";

        if ($this->switch_carrier_statistics_incoming == '1') {
            $table_carrier_statistics = $dbdate . '_carrierstate';

            if ($this->cdr_variable['sip_term_status'] > 0) {
                $causeSIP = $this->cdr_variable['sip_term_status'];
            }


            if ($causeSIP == 0) {
                $causeSIP = $causeQ850;
            }

            if ($this->fserror == 'CARRIERCPSISSUE') {
                $causeSIP = '503';
                $causeQ850 = '16';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'USERINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Account is inactive';
            } else if ($this->fserror == 'USERCLI') {
                $causeSIP = '21';
                $causeQ850 = '401';
                $this->otherinfo = 'CallerID unauthorized';
            } else if ($this->fserror == 'USERBALANCE') {
                $causeSIP = '21';
                $causeQ850 = '402';
                $this->otherinfo = 'Low Balance';
            } else if ($this->fserror == 'USERRATE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'USERTARIFFCURRENCY') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Currency Issue';
            } else if ($this->fserror == 'USERTARIFFINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Tariff Inactive';
            } else if ($this->fserror == 'USERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Rates Blocked';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Device Inactive';
            } else if ($this->fserror == 'USERCPS' or $this->fserror == 'USERCC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECPS') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'RESELLERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'USERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'RESELLERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERINACTIVE' or $this->fserror == 'RESELLERCURRENCY' or $this->fserror == 'RESELLERCLI' or $this->fserror == 'RESELLERBALANCE' or $this->fserror == 'RESELLERRATE' or $this->fserror == 'RESELLERLLRISSUE' or $this->fserror == 'RESELLERTARIFFCURRENCY' or $this->fserror == 'RESELLERTARIFFINACTIVE' or $this->fserror == 'RESELLERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Reseler Issue';
            } else if ($this->fserror == 'LLRISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route(LCR) Issue';
            } else if ($this->fserror == 'CARRIERISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier Issue';
            } else if ($this->fserror == 'CARRIERCLIISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier CLI Issue';
            } else if ($this->fserror == 'ROUTINGISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Routing(Out) Issue';
            } else if ($this->fserror == 'ROUTESTATUSISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Gateway and Rates disabled or inactive or missing';
            } else if ($this->fserror == 'ROUTEMISSING') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'ROUTINGBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Blocked';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '88';
                $causeQ850 = '488';
                $this->otherinfo = 'Codec Mismatch';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Multiple call on same Number';
            } else if ($this->fserror == 'DIDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Dialed Number in User DND List';
            } else if ($this->fserror == 'DNC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'DND List';
            }
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
            if ($this->cdr_variable['sip_term_status'] > 0) {
                $causeSIP = $this->cdr_variable['sip_term_status'];
            }

            if ($causeSIP == 0) {
                $causeSIP = $causeQ850;
            }

            if ($this->cdr_variable['fscause'] == 'DAILYLIMITISUE') {
                $this->otherinfo = "Campaign  Daily Call limit usage";
                $causeSIP = '487';
                $causeQ850 = '487';
            }


            if ($this->cdr_variable['fscause'] == 'ORIGINATOR_CANCEL') {
                $this->otherinfo = "Call disconnected(487) by Caller";
                $causeSIP = '487';
                $causeQ850 = '487';
            }

            if ($this->cdr_variable['fscause'] == 'PROGRESS_TIMEOUT') {
                $this->otherinfo = "Out PROGRESS_TIMEOUT";
                $causeSIP = '21';
                $causeQ850 = '607';
            }

            if ($this->cdr_variable['fscause'] == 'MEDIA_TIMEOUT') {
                $this->otherinfo = "Out MEDIA_TIMEOUT";
                $causeSIP = '21';
                $causeQ850 = '604';
            }

            if ($this->cdr_variable['fscause'] == 'GATEWAY_DOWN') {
                $this->otherinfo = "Out GATEWAY_DOWN";
                $causeSIP = '21';
                $causeQ850 = '609';
            }

            if ($this->cdr_variable['fscause'] == 'INCOMPATIBLE_DESTINATION') {
                $this->otherinfo = "Codecs Issue";
                $causeSIP = '88';
                $causeQ850 = '488';
            }



            if ($this->cdr_variable['fscause'] == 'RESPONSE_TO_STATUS_ENQUIRY') {
                $this->otherinfo = "Out Request Disconnected before Response";
                $causeSIP = '102';
                $causeQ850 = '30';
            }
            if ($this->cdr_variable['fscause'] == 'RECOVERY_ON_TIMER_EXPIRE') {
                $this->otherinfo = "Out Call is not established";
                $causeSIP = '504';
                $causeQ850 = '102';
            }

            if ($this->cdr_variable['fscause'] == 'NO_USER_RESPONSE') {
                $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                $causeSIP = '408';
                $causeQ850 = '18';
            }
            if ($this->cdr_variable['fscause'] == 'NORMAL_UNSPECIFIED') {
                $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                $causeSIP = '480';
                $causeQ850 = '31';
            }
            if ($this->fserror == 'CARRIERCPSISSUE') {
                $causeSIP = '503';
                $causeQ850 = '16';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'USERINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Account is inactive';
            } else if ($this->fserror == 'USERCLI') {
                $causeSIP = '21';
                $causeQ850 = '401';
                $this->otherinfo = 'CallerID unauthorized';
            } else if ($this->fserror == 'USERBALANCE') {
                $causeSIP = '21';
                $causeQ850 = '402';
                $this->otherinfo = 'Low Balance';
            } else if ($this->fserror == 'USERRATE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'USERTARIFFCURRENCY') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Currency Issue';
            } else if ($this->fserror == 'USERTARIFFINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Tariff Inactive';
            } else if ($this->fserror == 'USERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Rates Blocked';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Device Inactive';
            } else if ($this->fserror == 'USERCPS' or $this->fserror == 'USERCC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECPS') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'RESELLERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'USERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'RESELLERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERINACTIVE' or $this->fserror == 'RESELLERCURRENCY' or $this->fserror == 'RESELLERCLI' or $this->fserror == 'RESELLERBALANCE' or $this->fserror == 'RESELLERRATE' or $this->fserror == 'RESELLERLLRISSUE' or $this->fserror == 'RESELLERTARIFFCURRENCY' or $this->fserror == 'RESELLERTARIFFINACTIVE' or $this->fserror == 'RESELLERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Reseler Issue';
            } else if ($this->fserror == 'LLRISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route(LCR) Issue';
            } else if ($this->fserror == 'CARRIERISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier Issue';
            } else if ($this->fserror == 'CARRIERCLIISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier CLI Issue';
            } else if ($this->fserror == 'ROUTINGISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Routing(Out) Issue';
            } else if ($this->fserror == 'ROUTESTATUSISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Gateway and Rates disabled or inactive or missing';
            } else if ($this->fserror == 'ROUTEMISSING') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'ROUTINGBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Blocked';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '88';
                $causeQ850 = '488';
                $this->otherinfo = 'Codec Mismatch';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Multiple call on same Number';
            } else if ($this->fserror == 'DIDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Dialed Number in User DND List';
            } else if ($this->fserror == 'DNC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'DND List';
            }
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
            if ($this->cdr_variable['sip_term_status'] > 0) {
                $causeSIP = $this->cdr_variable['sip_term_status'];
            }
            $query = sprintf("UPDATE livecalls SET Q850CODE ='%s', SIPCODE = '%s', fscause = '%s',notes = CONCAT(ifnull(notes,''),' ','%s') , end_time='%s' where  common_uuid = '%s' ", $causeQ850, $causeSIP, $fscause, $notes, addslashes($this->cdr_variable['end_stamp']), addslashes($this->cdr_variable['common_uuid']));
            $this->query('SWITCH', $query);
            $this->execute();
            $this->writelog($query);
        }





        if ($this->switch_calls_statistics_incoming == '1') {
            if ($this->cdr_variable['sip_term_status'] > 0) {
                $causeSIP = $this->cdr_variable['sip_term_status'];
            }
            if ($causeSIP == 0) {
                $causeSIP = $causeQ850;
            }

            if ($this->fserror == 'CARRIERCPSISSUE') {
                $causeSIP = '503';
                $causeQ850 = '16';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'USERINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Account is inactive';
            } else if ($this->fserror == 'USERCLI') {
                $causeSIP = '21';
                $causeQ850 = '401';
                $this->otherinfo = 'CallerID unauthorized';
            } else if ($this->fserror == 'USERBALANCE') {
                $causeSIP = '21';
                $causeQ850 = '402';
                $this->otherinfo = 'Low Balance';
            } else if ($this->fserror == 'USERRATE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'USERTARIFFCURRENCY') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Currency Issue';
            } else if ($this->fserror == 'USERTARIFFINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Tariff Inactive';
            } else if ($this->fserror == 'USERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Rates Blocked';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Device Inactive';
            } else if ($this->fserror == 'USERCPS' or $this->fserror == 'USERCC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECPS') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'RESELLERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'USERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'RESELLERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERINACTIVE' or $this->fserror == 'RESELLERCURRENCY' or $this->fserror == 'RESELLERCLI' or $this->fserror == 'RESELLERBALANCE' or $this->fserror == 'RESELLERRATE' or $this->fserror == 'RESELLERLLRISSUE' or $this->fserror == 'RESELLERTARIFFCURRENCY' or $this->fserror == 'RESELLERTARIFFINACTIVE' or $this->fserror == 'RESELLERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Reseler Issue';
            } else if ($this->fserror == 'LLRISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route(LCR) Issue';
            } else if ($this->fserror == 'CARRIERISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier Issue';
            } else if ($this->fserror == 'CARRIERCLIISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier CLI Issue';
            } else if ($this->fserror == 'ROUTINGISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Routing(Out) Issue';
            } else if ($this->fserror == 'ROUTESTATUSISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Gateway and Rates disabled or inactive or missing';
            } else if ($this->fserror == 'ROUTEMISSING') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'ROUTINGBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Blocked';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '88';
                $causeQ850 = '488';
                $this->otherinfo = 'Codec Mismatch';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Multiple call on same Number';
            } else if ($this->fserror == 'DIDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Dialed Number in User DND List';
            } else if ($this->fserror == 'DNC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'DND List';
            }

            if ($this->cdr_variable['fscause'] == 'ORIGINATOR_CANCEL') {
                $this->otherinfo = "Call disconnected(487) by Caller";
                $causeSIP = '487';
                $causeQ850 = '487';
            }

            if ($this->cdr_variable['fscause'] == 'PROGRESS_TIMEOUT') {
                $this->otherinfo = "Out PROGRESS_TIMEOUT";
                $causeSIP = '21';
                $causeQ850 = '607';
            }

            if ($this->cdr_variable['fscause'] == 'MEDIA_TIMEOUT') {
                $this->otherinfo = "Out MEDIA_TIMEOUT";
                $causeSIP = '21';
                $causeQ850 = '604';
            }

            if ($this->cdr_variable['fscause'] == 'GATEWAY_DOWN') {
                $this->otherinfo = "Out GATEWAY_DOWN";
                $causeSIP = '21';
                $causeQ850 = '609';
            }

            if ($this->cdr_variable['fscause'] == 'INCOMPATIBLE_DESTINATION') {
                $this->otherinfo = "Codecs Issue";
                $causeSIP = '88';
                $causeQ850 = '488';
            }



            if ($this->cdr_variable['fscause'] == 'RESPONSE_TO_STATUS_ENQUIRY') {
                $this->otherinfo = "Out Request Disconnected before Response";
                $causeSIP = '102';
                $causeQ850 = '30';
            }
            if ($this->cdr_variable['fscause'] == 'RECOVERY_ON_TIMER_EXPIRE') {
                $this->otherinfo = "Out Call is not established";
                $causeSIP = '504';
                $causeQ850 = '102';
            }

            if ($this->cdr_variable['fscause'] == 'NO_USER_RESPONSE') {
                $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                $causeSIP = '408';
                $causeQ850 = '18';
            }
            if ($this->cdr_variable['fscause'] == 'NORMAL_UNSPECIFIED') {
                $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                $causeSIP = '480';
                $causeQ850 = '31';
            }


            $table_calls_statistics = $dbdate . '_customerstate';
            $query = sprintf("insert into %s (account_id, totalcalls, answeredcalls, prefix, carrier_id, call_date, calltime_h, calltime_m, r1_account_id, r2_account_id, r3_account_id, bill_duration, customer_duration, r1_duration, r2_duration, r3_duration, pdd,r1_cost, r2_cost, r3_cost, customer_cost, carrier_cost, carrier_duration, carrier_prefix, prefix_name, carrier_prefix_name, Q850CODE, SIPCODE,fscause, carrier_currency_id, customer_currency_id , carrier_ratio, carrier_callcost_total_usercurrency, profit_usercurrency, customer_company_name, carrier_name, src_ipaddress, cdr_type, rate, carrier_rate, r1_prefix, r1_rate, r2_prefix, r2_rate, r3_prefix, r3_rate ) VALUES ('%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s','%s', '%s', '%s','%s','%s','%s','%s', '%s','%s','%s','%s', '%s', '%s', '%s','%s', '%s','%s','%s', '%s','%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') ON DUPLICATE KEY UPDATE totalcalls = totalcalls + 1, bill_duration = bill_duration + values(bill_duration), customer_duration =  customer_duration + values(customer_duration), r1_duration =  r1_duration + values(r1_duration), r2_duration = r2_duration + values(r2_duration), r3_duration = r3_duration + values(r3_duration), pdd = pdd + values(pdd), answeredcalls = answeredcalls + values(answeredcalls), r1_cost = r1_cost +values(r1_cost), r2_cost = r2_cost+values(r2_cost), r3_cost =r3_cost +values(r3_cost), customer_cost = customer_cost + values(customer_cost), carrier_cost = carrier_cost +values(carrier_cost), carrier_duration = carrier_duration + values(carrier_duration),  carrier_callcost_total_usercurrency = carrier_callcost_total_usercurrency + values(carrier_callcost_total_usercurrency), profit_usercurrency =values(profit_usercurrency) ;", $table_calls_statistics, $account_id, $totalcalls, $answeredcalls, $prefix, $carrier_id, $call_date, $calltime_h, $calltime_m, $r1_account_id, $r2_account_id, $r3_account_id, $bill_duration, $customer_duration, $r1_duration, $r2_duration, $r3_duration, $pdd, $reseller1_callcost_total, $reseller2_callcost_total, $reseller3_callcost_total, $customer_callcost_total, $carrier_cost, $carrier_duration, $carrier_prefix, $prefix_name, $carrier_prefix_name, $causeQ850, $causeSIP, $fscause, $this->carrierdata['carrier_currency_id'], $this->userdata['currency_id'], $carrier_ratio, $carrier_callcost_total_usercurrency, $profit_usercurrency, $this->userdata['company_name'], $this->carrierdata['carrier_name'], $this->userdata['src_ip'], 'IN', $rate, $carrier_rate, $r1_prefix, $r1_rate, $r2_prefix, $r2_rate, $r3_prefix, $r3_rate);
            $this->writelog($query);
            $this->query('CDR', $query);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $query);
                $this->execute();
            }
        }

        if ($this->leg == 'B') {
            $didnumber = $this->cdr_variable['didnumber'];
            $table_campaign_buyer_statistics = 'campaign_buyer_statistics_' . $dbdate;
            $caller_id = $this->cdr_variable['effective_caller_id_number'];

            $query = sprintf("INSERT INTO %s ( account_id,did, campaign_id,buyer_name, buyer_number, total_calls, answer_calls,call_duration,  caller_id, call_date, create_date  )values ('%s','%s', '%s','%s', '%s', '%s', '%s','%s', '%s','%s' , '%s') ON DUPLICATE KEY UPDATE total_calls = total_calls + values(total_calls) ,
 call_duration = call_duration + values(call_duration) ,
 answer_calls = answer_calls + values(answer_calls) ;", $table_campaign_buyer_statistics, $account_id, $didnumber, $this->cdr_variable['campaign_id'], $this->cdr_variable['buyer_name'], $this->cdr_variable['buyer_number'], $totalcalls, $answeredcalls, $customer_duration, $caller_id, $call_date, $this->cdr_variable['end_stamp']);
            $this->writelog($query);
            $this->query('SWITCH', $query);
            if ($this->execute()) {
                
            } else {
                $this->campaign_buyer_statistics_tables($dbdate);
                $this->query('SWITCH', $query);
                $this->execute();
            }
        }



        if ($this->switch_calls_statistics == '1') {
            if ($this->cdr_variable['sip_term_status'] > 0) {
                $causeSIP = $this->cdr_variable['sip_term_status'];
            }
            if ($causeSIP == 0) {
                $causeSIP = $causeQ850;
            }

            if ($this->fserror == 'CARRIERCPSISSUE') {
                $causeSIP = '503';
                $causeQ850 = '16';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'USERINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Account is inactive';
            } else if ($this->fserror == 'USERCLI') {
                $causeSIP = '21';
                $causeQ850 = '401';
                $this->otherinfo = 'CallerID unauthorized';
            } else if ($this->fserror == 'USERBALANCE') {
                $causeSIP = '21';
                $causeQ850 = '402';
                $this->otherinfo = 'Low Balance';
            } else if ($this->fserror == 'USERRATE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'USERTARIFFCURRENCY') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Currency Issue';
            } else if ($this->fserror == 'USERTARIFFINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Tariff Inactive';
            } else if ($this->fserror == 'USERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Rates Blocked';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Device Inactive';
            } else if ($this->fserror == 'USERCPS' or $this->fserror == 'USERCC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECPS') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'RESELLERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'USERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'RESELLERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERINACTIVE' or $this->fserror == 'RESELLERCURRENCY' or $this->fserror == 'RESELLERCLI' or $this->fserror == 'RESELLERBALANCE' or $this->fserror == 'RESELLERRATE' or $this->fserror == 'RESELLERLLRISSUE' or $this->fserror == 'RESELLERTARIFFCURRENCY' or $this->fserror == 'RESELLERTARIFFINACTIVE' or $this->fserror == 'RESELLERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Reseler Issue';
            } else if ($this->fserror == 'LLRISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route(LCR) Issue';
            } else if ($this->fserror == 'CARRIERISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier Issue';
            } else if ($this->fserror == 'CARRIERCLIISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier CLI Issue';
            } else if ($this->fserror == 'ROUTINGISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Routing(Out) Issue';
            } else if ($this->fserror == 'ROUTESTATUSISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Gateway and Rates disabled or inactive or missing';
            } else if ($this->fserror == 'ROUTEMISSING') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'ROUTINGBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Blocked';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '88';
                $causeQ850 = '488';
                $this->otherinfo = 'Codec Mismatch';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Multiple call on same Number';
            } else if ($this->fserror == 'DIDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Dialed Number in User DND List';
            } else if ($this->fserror == 'DNC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'DND List';
            }

            if ($this->leg == 'B' and $this->billcdr_outgoing == '1') {
                if ($this->cdr_variable['fscause'] == 'ORIGINATOR_CANCEL') {
                    $this->otherinfo = "Call disconnected(487) by Caller";
                    $causeSIP = '487';
                    $causeQ850 = '487';
                }

                if ($this->cdr_variable['fscause'] == 'PROGRESS_TIMEOUT') {
                    $this->otherinfo = "Out PROGRESS_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '607';
                }

                if ($this->cdr_variable['fscause'] == 'MEDIA_TIMEOUT') {
                    $this->otherinfo = "Out MEDIA_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '604';
                }

                if ($this->cdr_variable['fscause'] == 'GATEWAY_DOWN') {
                    $this->otherinfo = "Out GATEWAY_DOWN";
                    $causeSIP = '21';
                    $causeQ850 = '609';
                }

                if ($this->cdr_variable['fscause'] == 'INCOMPATIBLE_DESTINATION') {
                    $this->otherinfo = "Codecs Issue";
                    $causeSIP = '88';
                    $causeQ850 = '488';
                }



                if ($this->cdr_variable['fscause'] == 'RESPONSE_TO_STATUS_ENQUIRY') {
                    $this->otherinfo = "Out Request Disconnected before Response";
                    $causeSIP = '102';
                    $causeQ850 = '30';
                }
                if ($this->cdr_variable['fscause'] == 'RECOVERY_ON_TIMER_EXPIRE') {
                    $this->otherinfo = "Out Call is not established";
                    $causeSIP = '504';
                    $causeQ850 = '102';
                }

                if ($this->cdr_variable['fscause'] == 'NO_USER_RESPONSE') {
                    $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                    $causeSIP = '408';
                    $causeQ850 = '18';
                }
                if ($this->cdr_variable['fscause'] == 'NORMAL_UNSPECIFIED') {
                    $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                    $causeSIP = '480';
                    $causeQ850 = '31';
                }


                if ($this->cdr_variable['fscause'] == 'ORIGINATOR_CANCEL') {
                    $this->otherinfo = "Call disconnected(487) by Caller";
                    $causeSIP = '487';
                    $causeQ850 = '487';
                }

                if ($this->cdr_variable['fscause'] == 'PROGRESS_TIMEOUT') {
                    $this->otherinfo = "Out PROGRESS_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '607';
                }


                if ($this->cdr_variable['fscause'] == 'MEDIA_TIMEOUT') {
                    $this->otherinfo = "Out MEDIA_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '604';
                }

                if ($this->cdr_variable['fscause'] == 'GATEWAY_DOWN') {
                    $this->otherinfo = "Out GATEWAY_DOWN";
                    $causeSIP = '21';
                    $causeQ850 = '609';
                }
            }
            $table_calls_statistics = $dbdate . '_customerstate';
            $query = sprintf("insert into %s (account_id, totalcalls, answeredcalls, prefix, carrier_id, call_date, calltime_h, calltime_m, r1_account_id, r2_account_id, r3_account_id, bill_duration, customer_duration, r1_duration, r2_duration, r3_duration, pdd,r1_cost, r2_cost, r3_cost, customer_cost, carrier_cost, carrier_duration, carrier_prefix, prefix_name, carrier_prefix_name, Q850CODE, SIPCODE,fscause, carrier_currency_id, customer_currency_id , carrier_ratio, carrier_callcost_total_usercurrency, profit_usercurrency, customer_company_name, carrier_name , src_ipaddress, cdr_type,  rate, carrier_rate, r1_prefix, r1_rate, r2_prefix, r2_rate, r3_prefix, r3_rate ) VALUES ('%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s','%s', '%s', '%s','%s','%s','%s','%s', '%s','%s','%s','%s', '%s', '%s', '%s','%s', '%s','%s','%s', '%s','%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') ON DUPLICATE KEY UPDATE totalcalls = totalcalls + 1, bill_duration = bill_duration + values(bill_duration), customer_duration =  customer_duration + values(customer_duration), r1_duration =  r1_duration + values(r1_duration), r2_duration = r2_duration + values(r2_duration), r3_duration = r3_duration + values(r3_duration), pdd = pdd + values(pdd), answeredcalls = answeredcalls + values(answeredcalls), r1_cost = r1_cost +values(r1_cost), r2_cost = r2_cost+values(r2_cost), r3_cost =r3_cost +values(r3_cost), customer_cost = customer_cost + values(customer_cost), carrier_cost = carrier_cost +values(carrier_cost), carrier_duration = carrier_duration + values(carrier_duration),  carrier_callcost_total_usercurrency = carrier_callcost_total_usercurrency + values(carrier_callcost_total_usercurrency), profit_usercurrency =values(profit_usercurrency) ;", $table_calls_statistics, $account_id, $totalcalls, $answeredcalls, $prefix, $carrier_id, $call_date, $calltime_h, $calltime_m, $r1_account_id, $r2_account_id, $r3_account_id, $bill_duration, $customer_duration, $r1_duration, $r2_duration, $r3_duration, $pdd, $reseller1_callcost_total, $reseller2_callcost_total, $reseller3_callcost_total, $customer_callcost_total, $carrier_cost, $carrier_duration, $carrier_prefix, $prefix_name, $carrier_prefix_name, $causeQ850, $causeSIP, $fscause, $this->carrierdata['carrier_currency_id'], $this->userdata['currency_id'], $carrier_ratio, $carrier_callcost_total_usercurrency, $profit_usercurrency, $this->userdata['company_name'], $this->carrierdata['carrier_name'], $this->userdata['src_ip'], 'OUT', $rate, $carrier_rate, $r1_prefix, $r1_rate, $r2_prefix, $r2_rate, $r3_prefix, $r3_rate);
            $this->writelog($query);
            $this->query('CDR', $query);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $query);
                $this->execute();
            }
        }

        if ($this->cdr_variable['billsec'] == 0) {
            if ($this->leg == 'A' and $this->billcdr_outgoing == '1') {
                $causeSIP = str_replace(":", "", $causeSIP);
                $causeSIP = str_replace("sip", "", $causeSIP);
                $causeSIP = str_replace("SIP", "", $causeSIP);
            }
            if ($this->cdr_variable['sip_term_status'] > 0) {
                $causeSIP = $this->cdr_variable['sip_term_status'];
            }
            $failcallsql["fs_errorcode"] = addslashes($this->cdr_variable['fs_errorcode']);
            $failcallsql["carrier_ratecard_id"] = addslashes($this->carrierdata['ratecard_id']);
            $failcallsql["carrier_tariff_id"] = addslashes($this->carrierdata['tariff_id']);
            $failcallsql["carrier_prefix"] = addslashes($this->carrierdata['prefix']);
            $failcallsql["carrier_destination"] = addslashes($this->carrierdata['destination']);
            $failcallsql["carrier_ipaddress"] = addslashes($this->carrierdata['ipaddress']);
            $failcallsql["carrier_ipaddress_name"] = addslashes($this->carrierdata['ipaddress_name']);
            $failcallsql["carrier_src_caller"] = addslashes($this->carrierdata['src_caller']);
            $failcallsql["carrier_src_callee"] = addslashes($this->carrierdata['src_callee']);
            $failcallsql["carrier_dst_caller"] = addslashes($this->carrierdata['dst_caller']);
            $failcallsql["carrier_dst_callee"] = addslashes($this->carrierdata['dst_callee']);
            $failcallsql["customer_tariff_id"] = addslashes($this->userdata['tariff_id']);
            $failcallsql["customer_ipaddress"] = addslashes($this->userdata['ipaddress']);
            $failcallsql["customer_ratecard_id"] = addslashes($this->userdata['ratecard_id']);
            $failcallsql["customer_prefix"] = addslashes($this->userdata['prefix']);
            $failcallsql["customer_destination"] = addslashes($this->userdata['destination']);
            $failcallsql["customer_src_caller"] = addslashes($this->userdata['src_caller']);
            $failcallsql["customer_src_callee"] = addslashes($this->userdata['src_callee']);
            $failcallsql["customer_src_ip"] = addslashes($this->userdata['src_ip']);
            $failcallsql["customer_account_id"] = addslashes($this->userdata['account_id']);
            $failcallsql["reseller1_account_id"] = addslashes($this->reseller1_data['account_id']);
            $failcallsql["reseller1_tariff_id"] = addslashes($this->reseller1_data['tariff_id']);
            $failcallsql["reseller1_ratecard_id"] = addslashes($this->reseller1_data['ratecard_id']);
            $failcallsql["reseller1_prefix"] = addslashes($this->reseller1_data['prefix']);
            $failcallsql["reseller1_destination"] = addslashes($this->reseller1_data['destination']);
            $failcallsql["reseller2_destination"] = addslashes($this->reseller2_data['destination']);
            $failcallsql["reseller2_account_id"] = addslashes($this->reseller2_data['account_id']);
            $failcallsql["reseller2_tariff_id"] = addslashes($this->reseller2_data['tariff_id']);
            $failcallsql["reseller2_ratecard_id"] = addslashes($this->reseller2_data['ratecard_id']);
            $failcallsql["reseller2_prefix"] = addslashes($this->reseller2_data['prefix']);
            $failcallsql["reseller3_account_id"] = addslashes($this->reseller3_data['account_id']);
            $failcallsql["reseller3_tariff_id"] = addslashes($this->reseller3_data['tariff_id']);
            $failcallsql["reseller3_ratecard_id"] = addslashes($this->reseller3_data['ratecard_id']);
            $failcallsql["reseller3_prefix"] = addslashes($this->reseller3_data['prefix']);
            $failcallsql["reseller3_destination"] = addslashes($this->reseller3_data['destination']);
            $failcallsql["uuid"] = addslashes($this->cdr_variable['uuid']);
            $failcallsql["sip_from_user"] = addslashes($this->cdr_variable['sip_from_user']);
            $failcallsql["sip_from_uri"] = addslashes($this->cdr_variable['sip_from_uri']);
            $failcallsql["sip_from_host"] = addslashes($this->cdr_variable['sip_from_host']);
            $failcallsql["channel_name"] = addslashes($this->cdr_variable['channel_name']);
            $failcallsql["common_uuid"] = addslashes($this->cdr_variable['common_uuid']);
            $failcallsql["caller_callid"] = addslashes($this->cdr_variable['caller_callid']);
            $failcallsql["callee_callid"] = addslashes($this->cdr_variable['sip_call_id']);
            $failcallsql["sip_call_id"] = addslashes($this->cdr_variable['sip_call_id']);
            $failcallsql["ep_codec_string"] = addslashes($this->cdr_variable['ep_codec_string']);
            $failcallsql["sip_network_ip"] = addslashes($this->cdr_variable['sip_network_ip']);
            $failcallsql["sip_received_ip"] = addslashes($this->cdr_variable['sip_received_ip']);
            $failcallsql["sip_received_port"] = addslashes($this->cdr_variable['sip_received_port']);
            $failcallsql["sip_via_protocol"] = addslashes($this->cdr_variable['sip_via_protocol']);
            $failcallsql["sip_from_user_stripped"] = addslashes($this->cdr_variable['sip_from_user_stripped']);
            $failcallsql["sip_from_display"] = addslashes($this->cdr_variable['sip_from_display']);
            $failcallsql["sip_full_from"] = addslashes($this->cdr_variable['sip_full_from']);
            $failcallsql["sip_to_display"] = addslashes($this->cdr_variable['sip_to_display']);
            $failcallsql["sip_full_to"] = addslashes($this->cdr_variable['sip_full_to']);
            $failcallsql["sip_req_user"] = addslashes($this->cdr_variable['sip_req_user']);
            $failcallsql["sip_req_uri"] = addslashes($this->cdr_variable['sip_req_uri']);
            $failcallsql["sip_to_user"] = addslashes($this->cdr_variable['sip_to_user']);
            $failcallsql["sip_contact_user"] = addslashes($this->cdr_variable['sip_contact_user']);
            $failcallsql["sip_contact_port"] = addslashes($this->cdr_variable['sip_contact_port']);
            $failcallsql["sip_contact_uri"] = addslashes($this->cdr_variable['sip_contact_uri']);
            $failcallsql["sip_contact_host"] = addslashes($this->cdr_variable['sip_contact_host']);
            $failcallsql["rtp_use_codec_string"] = addslashes($this->cdr_variable['rtp_use_codec_string']);
            $failcallsql["sip_user_agent"] = addslashes($this->cdr_variable['sip_user_agent']);
            $failcallsql["sip_via_host"] = addslashes($this->cdr_variable['sip_via_host']);
            $failcallsql["sip_via_port"] = addslashes($this->cdr_variable['sip_via_port']);
            $failcallsql["sip_via_rport"] = addslashes($this->cdr_variable['sip_via_rport']);
            $failcallsql["switch_r_sdp"] = addslashes($this->cdr_variable['switch_r_sdp']);

            $failcallsql["effective_caller_id_name"] = addslashes($this->cdr_variable['effective_caller_id_name']);
            $failcallsql["effective_caller_id_number"] = addslashes($this->cdr_variable['effective_caller_id_number']);
            $failcallsql["digits_dialed"] = addslashes($this->cdr_variable['digits_dialed']);
            $failcallsql["start_time"] = addslashes($this->cdr_variable['start_stamp']);
            $failcallsql["profile_start_stamp"] = addslashes($this->cdr_variable['profile_start_stamp']);
            $failcallsql["end_time"] = addslashes($this->cdr_variable['end_stamp']);
            $failcallsql["caller_id"] = addslashes($this->cdr_variable['caller_id']);
            $failcallsql["duration"] = addslashes($this->cdr_variable['duration']);
            $failcallsql["billsec"] = addslashes($this->cdr_variable['billsec']);
            $failcallsql["progresssec"] = addslashes($this->cdr_variable['progresssec']);
            $failcallsql["answersec"] = addslashes($this->cdr_variable['answersec']);
            $failcallsql["waitsec"] = addslashes($this->cdr_variable['waitsec']);
            $failcallsql["flow_billsec"] = addslashes($this->cdr_variable['flow_billsec']);
            $failcallsql["sip_hangup_disposition"] = addslashes($this->cdr_variable['sip_hangup_disposition']);
            $failcallsql["in_useragent"] = addslashes($this->cdr_variable['in_useragent']);
            $failcallsql["out_useragent"] = addslashes($this->cdr_variable['out_useragent']);
            $failcallsql["in_rtp_audio_in_media_bytes"] = addslashes($this->cdr_variable['rtp_audio_in_media_bytes']);
            $failcallsql["in_rtp_audio_out_media_bytes"] = addslashes($this->cdr_variable['rtp_audio_out_media_bytes']);
            $failcallsql["out_rtp_audio_in_media_bytes"] = addslashes($this->cdr_variable['rtp_audio_in_media_bytes']);
            $failcallsql["out_rtp_audio_out_media_bytes"] = addslashes($this->cdr_variable['rtp_audio_out_media_bytes']);
            $failcallsql["rtp_audio_in_media_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_media_packet_count']);
            $failcallsql["rtp_audio_out_media_packet_count"] = addslashes($this->cdr_variable['rtp_audio_out_media_packet_count']);
            $failcallsql["in_rtp_audio_in_jitter_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']);
            $failcallsql["out_rtp_audio_in_jitter_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_packet_count']);
            $failcallsql["in_rtp_audio_in_skip_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']);
            $failcallsql["out_rtp_audio_in_skip_packet_count"] = addslashes($this->cdr_variable['rtp_audio_in_skip_packet_count']);
            $failcallsql["in_rtp_audio_in_jitter_min_variance"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']);
            $failcallsql["in_rtp_audio_in_jitter_max_variance"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']);
            $failcallsql["out_rtp_audio_in_jitter_min_variance"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_min_variance']);
            $failcallsql["out_rtp_audio_in_jitter_max_variance"] = addslashes($this->cdr_variable['rtp_audio_in_jitter_max_variance']);
            $failcallsql["in_rtp_audio_in_mos"] = addslashes($this->cdr_variable['rtp_audio_in_mos']);
            $failcallsql["out_rtp_audio_in_mos"] = addslashes($this->cdr_variable['rtp_audio_in_mos']);
            $failcallsql["fscause"] = addslashes($fscause);

            $failcallsql["carrier_dialplan_id"] = addslashes($this->carrierdata['dialplan_id']);
            $failcallsql["carrier_id"] = addslashes($this->carrierdata['carrier_id']);
            unset($data['disposition']);
            if ($this->cdr_variable['endpoint_disposition'] == 'CALL_REJECTED')
                $failcallsql["disposition"] = 'REJECTED';
            elseif ($this->cdr_variable['endpoint_disposition'] == 'ORIGINATOR_CANCEL')
                $failcallsql["disposition"] = 'CANCEL';
            elseif ($fscause == 'ORIGINATOR_CANCEL')
                $failcallsql["disposition"] = 'CANCEL';
            elseif (strlen($this->cdr_variable['fs_errorcode']) > 0)
                $failcallsql["disposition"] = $this->cdr_variable['fs_errorcode'];
            else
                $failcallsql["disposition"] = 'BUSY';


            if (strlen($this->cdr_variable['endpoint_disposition']) == 0 and $this->switch_issue == '1') {
                $carrierarray_hangup = Array('401', '403', '404', '488', '486', '408', '480', '480', '603', '410', '480', '483', '484', '501', '502', '503', '500', '601', '602', '603', '604', '605', '607', '609');
                if ($this->hangupby == 'CALLEE') {
                    if ($this->cdr_variable['endpoint_disposition'] == 'CALL_REJECTED')
                        $this->otherinfo = "Call Rejected";
//                    if (in_array(trim($causeSIP), $carrierarray_hangup))
//                        $this->otherinfo = 'Call rejected';
                }
                if ($this->cdr_variable['endpoint_disposition'] == 'ORIGINATOR_CANCEL')
                    $this->otherinfo = "Caller call disconnected";
                else
                    $this->otherinfo = $fscause;
            } else {
                $this->otherinfo = addslashes($this->cdr_variable['endpoint_disposition']);
            }

            if ($this->cdr_variable['sip_term_status'] > 0) {
                $causeSIP = $this->cdr_variable['sip_term_status'];
            }
            if ($causeSIP == 0) {
                $causeSIP = $causeQ850;
            }

            $failcallsql["hangupby"] = addslashes($this->hangupby);
            $failcallsql["customer_incodecs"] = $this->cdr_variable['USERCODECSCLIST'];
            $failcallsql["carrier_outcodecs"] = $this->carrierdata['carrier_codecs'];
            $failcallsql["call_codecs"] = $this->cdr_variable['write_codec'];
            $failcallsql["did_number"] = $this->cdr_variable['did_number'];
            $failcallsql["customer_company_name"] = $this->userdata['company_name'];

            $failcallsql["identity_in"] = addslashes($this->cdr_variable['sip_h_identity']);
            $failcallsql["attest_in"] = addslashes($this->cdr_variable['attest_in']);
            $this->otherinfo = $this->cdr_variable['disposition_cause'];

            if (CAMPAIGN) {
                if (strlen($this->cdr_variable['disposition_cause']) == 0) {
                    $carrierarray_hangup = Array('401', '403', '404', '488', '486', '408', '480', '480', '603', '410', '480', '483', '484', '501', '502', '503', '500', '601', '602', '603', '604', '605', '607', '609');
                    if ($this->hangupby == 'CALLEE') {
                        if ($this->cdr_variable['endpoint_disposition'] == 'CALL_REJECTED')
                            $RAWCDRQUERY["disposition_cause"] = "Buyer Call Rejected";
                        if (in_array(trim($causeSIP), $carrierarray_hangup))
                            $RAWCDRQUERY["disposition_cause"] = 'Call rejected by Buyer';
                    }
                    if ($this->cdr_variable['endpoint_disposition'] == 'ORIGINATOR_CANCEL' or $causeSIP == '487')
                        $RAWCDRQUERY["disposition_cause"] = "Caller call disconnected";
                    else
                        $RAWCDRQUERY["disposition_cause"] = 'Call rejected by Buyer';
                } else {
                    $RAWCDRQUERY["disposition_cause"] = $this->cdr_variable['disposition_cause'];
                }
            } else {
                if (strlen($this->cdr_variable['disposition_cause']) == 0) {
                    $carrierarray_hangup = Array('401', '403', '404', '488', '486', '408', '480', '480', '603', '410', '480', '483', '484', '501', '502', '503', '500', '601', '602', '603', '604', '605', '607', '609');
                    if ($this->hangupby == 'CALLEE') {
                        if ($this->cdr_variable['endpoint_disposition'] == 'CALL_REJECTED')
                            $this->otherinfo = "Call Rejected";
//                    if (in_array(trim($causeSIP), $carrierarray_hangup))
//                        $this->otherinfo = 'Call rejected';
                    }
                    if ($this->cdr_variable['endpoint_disposition'] == 'ORIGINATOR_CANCEL' or $causeSIP == '487')
                        $this->otherinfo = "Caller call disconnected";
                    else
                        $this->otherinfo = 'Call rejected';
                } else {
                    $this->otherinfo = $this->cdr_variable['disposition_cause'];
                }
            }
            if ($this->leg == 'A') {
                $this->otherinfo = "Issue with outbound carrier connectivity";
                if ($fscause == 'ORIGINATOR_CANCEL') {
                    $this->otherinfo = "Call disconnected(487) by Caller";
                    $causeSIP = '487';
                    $causeQ850 = '487';
                } else if ($fscause == 'INCOMPATIBLE_DESTINATION') {
                    $this->otherinfo = "Codecs Issue";
                    $causeSIP = '88';
                    $causeQ850 = '488';
                } else if ($fscause == 'PROGRESS_TIMEOUT') {
                    $this->otherinfo = "Out PROGRESS_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '607';
                } else if ($fscause == 'MEDIA_TIMEOUT') {
                    $this->otherinfo = "Out MEDIA_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '604';
                } else if ($fscause == 'GATEWAY_DOWN') {
                    $this->otherinfo = "Out GATEWAY_DOWN";
                    $causeSIP = '21';
                    $causeQ850 = '609';
                } else if ($fscause == 'ORIGINATOR_CANCEL') {
                    $this->otherinfo = "Call disconnected(487) by Caller";
                    $causeSIP = '487';
                    $causeQ850 = '487';
                } else if ($fscause == 'PROGRESS_TIMEOUT') {
                    $this->otherinfo = "Out PROGRESS_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '607';
                } else if ($fscause == 'MEDIA_TIMEOUT') {
                    $this->otherinfo = "Out MEDIA_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '604';
                } else if ($fscause == 'GATEWAY_DOWN') {
                    $this->otherinfo = "Out GATEWAY_DOWN";
                    $causeSIP = '21';
                    $causeQ850 = '609';
                } else if ($fscause == 'INCOMPATIBLE_DESTINATION') {
                    $this->otherinfo = "Codecs Issue";
                    $causeSIP = '88';
                    $causeQ850 = '488';
                } else if ($fscause == 'RESPONSE_TO_STATUS_ENQUIRY') {
                    $this->otherinfo = "Out Request Disconnected before Response";
                    $causeSIP = '102';
                    $causeQ850 = '30';
                } else if ($fscause == 'RECOVERY_ON_TIMER_EXPIRE') {
                    $this->otherinfo = "Out Call is not established";
                    $causeSIP = '504';
                    $causeQ850 = '102';
                } else if ($fscause == 'NO_USER_RESPONSE') {
                    $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                    $causeSIP = '408';
                    $causeQ850 = '18';
                } else if ($fscause == 'NORMAL_UNSPECIFIED') {
                    $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                    $causeSIP = '480';
                    $causeQ850 = '31';
                }
            }

            if ($this->leg == 'B') {

                if ($fscause == 'ORIGINATOR_CANCEL') {
                    $this->otherinfo = "Call disconnected(487) by Caller";
                    $causeSIP = '487';
                    $causeQ850 = '487';
                } else if ($fscause == 'PROGRESS_TIMEOUT') {
                    $this->otherinfo = "Out PROGRESS_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '607';
                } else if ($fscause == 'MEDIA_TIMEOUT') {
                    $this->otherinfo = "Out MEDIA_TIMEOUT";
                    $causeSIP = '21';
                    $causeQ850 = '604';
                } else if ($fscause == 'GATEWAY_DOWN') {
                    $this->otherinfo = "Out GATEWAY_DOWN";
                    $causeSIP = '21';
                    $causeQ850 = '609';
                } else if ($fscause == 'INCOMPATIBLE_DESTINATION') {
                    $this->otherinfo = "Codecs Issue";
                    $causeSIP = '88';
                    $causeQ850 = '488';
                } else if ($fscause == 'RESPONSE_TO_STATUS_ENQUIRY') {
                    $this->otherinfo = "Out Request Disconnected before Response";
                    $causeSIP = '102';
                    $causeQ850 = '30';
                } else if ($fscause == 'RECOVERY_ON_TIMER_EXPIRE') {
                    $this->otherinfo = "Out Call is not established";
                    $causeSIP = '504';
                    $causeQ850 = '102';
                } else if ($fscause == 'NO_USER_RESPONSE') {
                    $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                    $causeSIP = '408';
                    $causeQ850 = '18';
                } else if ($fscause == 'NORMAL_UNSPECIFIED') {
                    $this->otherinfo = "Out NORMAL_UNSPECIFIED";
                    $causeSIP = '480';
                    $causeQ850 = '31';
                }
            }
            $this->fserror = $this->cdr_variable['fs_errorcode'];
            if ($this->fserror == 'CARRIERCPSISSUE') {
                $causeSIP = '503';
                $causeQ850 = '16';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'USERINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Account is inactive';
            } else if ($this->fserror == 'USERCLI') {
                $causeSIP = '21';
                $causeQ850 = '401';
                $this->otherinfo = 'CallerID unauthorized';
            } else if ($this->fserror == 'USERBALANCE') {
                $causeSIP = '21';
                $causeQ850 = '402';
                $this->otherinfo = 'Low Balance';
            } else if ($this->fserror == 'USERRATE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'USERTARIFFCURRENCY') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Currency Issue';
            } else if ($this->fserror == 'USERTARIFFINACTIVE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Tariff Inactive';
            } else if ($this->fserror == 'USERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'User Rates Blocked';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Device Inactive';
            } else if ($this->fserror == 'USERCPS' or $this->fserror == 'USERCC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Port Limit';
            } else if ($this->fserror == 'USERDEVICECPS') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CPS Limit';
            } else if ($this->fserror == 'RESELLERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'USERCALLERIDBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'CLI Issue';
            } else if ($this->fserror == 'RESELLERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALEDNUMBERBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'USERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERDIALPLAN') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Issue';
            } else if ($this->fserror == 'RESELLERINACTIVE' or $this->fserror == 'RESELLERCURRENCY' or $this->fserror == 'RESELLERCLI' or $this->fserror == 'RESELLERBALANCE' or $this->fserror == 'RESELLERRATE' or $this->fserror == 'RESELLERLLRISSUE' or $this->fserror == 'RESELLERTARIFFCURRENCY' or $this->fserror == 'RESELLERTARIFFINACTIVE' or $this->fserror == 'RESELLERRATEBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Reseler Issue';
            } else if ($this->fserror == 'LLRISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route(LCR) Issue';
            } else if ($this->fserror == 'CARRIERISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier Issue';
            } else if ($this->fserror == 'CARRIERCLIISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Carrier CLI Issue';
            } else if ($this->fserror == 'ROUTINGISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Routing(Out) Issue';
            } else if ($this->fserror == 'ROUTESTATUSISSUE') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Gateway and Rates disabled or inactive or missing';
            } else if ($this->fserror == 'ROUTEMISSING') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Missing';
            } else if ($this->fserror == 'ROUTINGBLOCKED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Route Blocked';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '88';
                $causeQ850 = '488';
                $this->otherinfo = 'Codec Mismatch';
            } else if ($this->fserror == 'CODECNOTALLOWED') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'Multiple call on same Number';
            } else if ($this->fserror == 'DIDNUMBER') {
                $causeSIP = '21';
                $causeQ850 = '404';
                $this->otherinfo = 'Dialed Number in User DND List';
            } else if ($this->fserror == 'DNC') {
                $causeSIP = '21';
                $causeQ850 = '503';
                $this->otherinfo = 'DND List';
            }




            $failcallsql["disposition_cause"] = $this->otherinfo;
            $failcallsql["SIPCODE"] = addslashes($causeSIP);
            $failcallsql["Q850CODE"] = addslashes($causeQ850);
            $failcallsql["carrier_name"] = $this->carrierdata['carrier_name'];
            if ($this->switch_cdrs_incoming == '1') {
                $failcallsql["cdr_type"] = 'IN';
            }

            if ($this->switch_cdrs == '1') {
                $failcallsql["cdr_type"] = 'OUT';
            }


            $failcallsql_SQL = "";
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $failcallsql)) {
                    $failcallsql[$key] = $value;
                }
            }

            if (!in_array("carrier_duration", $failcallsql)) {
                $failcallsql['carrier_duration'] = 0;
            }
            if (!in_array("customer_duration", $failcallsql)) {
                $failcallsql['customer_duration'] = 0;
            }
            if (!in_array("customer_duration", $failcallsql)) {
                $failcallsql['customer_duration'] = 0;
            }

            foreach ($failcallsql as $key => $value) {
                $failcallsql_SQL .= " $key='$value',";
            }

            $failcallsql_SQL = rtrim($failcallsql_SQL, ',');
            $failcallsql_CDR = 'insert into ' . $dbdate . "_ratedcdr set " . $failcallsql_SQL;
            $this->writelog($failcallsql_CDR);
            $this->query('CDR', $failcallsql_CDR);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $failcallsql_IN);
                $this->execute();
            }

            if ($this->userdata['account_id'] == '' or strlen($this->userdata['account_id']) == 0) {
                $failcallsql["cdr_type"] = 'DOSS';
                $failcallsql = rtrim($failcallsql, ',');
                $failcallsql_IN = 'insert into ' . $dbdate . "_ratedcdr set " . $failcallsql;
                $this->writelog($failcallsql_IN);
                $this->query('CDR', $failcallsql_IN);
                if ($this->execute()) {
                    
                } else {
                    $this->newtable_process($dbdate);
                    $this->query('CDR', $failcallsql_IN);
                    $this->execute();
                }
            }
        }



        if ($this->leg == 'A' and $this->billcdr_outgoing != '1' and strlen($this->carrierdata['did_number']) > 0 and $this->calltype == 'INCOMING') {
            $clidata = "account_id = '" . addslashes($this->userdata['account_id']) . "',";
            $clidata .= "carrier_id = '" . addslashes($this->carrierdata['carrier_id']) . "',";
            if ($this->cdr_variable['sip_term_status'] > 0) {
                $causeSIP = $this->cdr_variable['sip_term_status'];
            }
            $clidata .= "sipcode = '" . addslashes($causeSIP) . "',";
            $clidata .= "carrier_dst_caller = '" . addslashes($this->carrierdata['src_caller']) . "',";
            $clidata .= "call_date = '" . addslashes($date = date('Y-m-d')) . "',";
            $clidata .= "call_hour = '" . addslashes($date = date('H')) . "',";
            $clidata .= "call_minute = '" . addslashes($date = date('i')) . "',";
            ;
            $clidata .= "didnumber_destination = '" . addslashes($this->carrierdata['did_name']) . "',";
            $clidata .= "didnumber = '" . addslashes($this->carrierdata['did_number']) . "',";
            if ($this->cdr_variable['billsec'] > 1) {
                $clidata .= "total_call_count = '1',";
                $clidata .= "total_duration  = '" . addslashes($this->cdr_variable['billsec']) . "',";
                $clidata .= "ans_call_count = '1' ";
                $clidata .= " ON DUPLICATE KEY UPDATE total_call_count = total_call_count + 1 , total_duration = total_duration + values(total_duration) , ans_call_count =  ans_call_count + 1";
            } else {
                $clidata .= "total_call_count = '1',";
                $clidata .= "total_duration  = '" . addslashes($this->cdr_variable['billsec']) . "',";
                $clidata .= "ans_call_count = '0' ";
                $clidata .= " ON DUPLICATE KEY UPDATE total_call_count = total_call_count + 1 , total_duration = total_duration + values(total_duration)";
            }
            $clidata = 'insert into ' . $dbdate . '_didcalls set ' . $clidata;
            $this->writelog($clidata);
            $this->query('CDR', $clidata);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $clidata);
                $this->execute();
            }
        } else {
            $clidata = "account_id = '" . addslashes($this->userdata['account_id']) . "',";
            $clidata .= "carrier_id = '" . addslashes($this->carrierdata['carrier_id']) . "',";
            if ($this->cdr_variable['sip_term_status'] > 0) {
                $causeSIP = $this->cdr_variable['sip_term_status'];
            }
            $clidata .= "sipcode = '" . addslashes($causeSIP) . "',";
            $clidata .= "customer_src_caller = '" . addslashes($this->userdata['src_caller']) . "',";
            $clidata .= "carrier_dst_caller = '" . addslashes($carrier_dst_caller) . "',";
            $clidata .= "call_date = '" . addslashes($date = date('Y-m-d')) . "',";
            $clidata .= "call_hour = '" . addslashes($date = date('H')) . "',";
            $clidata .= "call_minute = '" . addslashes($date = date('i')) . "',";
            $clidata .= "callid_prefix = '" . addslashes($this->userdata['prefix']) . "',";
            $clidata .= "callerid_destination = '" . addslashes($this->userdata['destination']) . "',";
            if (strlen(trim($this->carrierdata['dst_callee'])) == 0) {
                $carrier_dst_callee = $this->cdr_variable['carrier_dst_callee'];
            }
            if ($carrier_dst_callee == "")
                $carrier_dst_callee = $this->carrierdata['dst_callee'];
            $clidata .= "carrier_dst_callee = '" . addslashes($carrier_dst_callee) . "',";
            $clidata .= "carrier_dst_destination = '" . addslashes($this->carrierdata['destination']) . "',";

            if ($this->cdr_variable['billsec'] > 1) {
                $clidata .= "total_call_count = '1',";
                $clidata .= "total_duration  = '" . addslashes($this->cdr_variable['billsec']) . "',";
                $clidata .= "ans_call_count = '1' ";
                $clidata .= " ON DUPLICATE KEY UPDATE total_call_count = total_call_count + 1 , total_duration = total_duration + values(total_duration) , ans_call_count =  ans_call_count + 1";
            } else {
                $clidata .= "total_call_count = '1',";
                $clidata .= "total_duration  = '" . addslashes($this->cdr_variable['billsec']) . "',";
                $clidata .= "ans_call_count = '0' ";
                $clidata .= " ON DUPLICATE KEY UPDATE total_call_count = total_call_count + 1 , total_duration = total_duration + values(total_duration)";
            }
            $clidata = 'insert into ' . $dbdate . '_pstncalls set ' . $clidata;
            $this->writelog($clidata);
            $this->query('CDR', $clidata);
            if ($this->execute()) {
                
            } else {
                $this->newtable_process($dbdate);
                $this->query('CDR', $clidata);
                $this->execute();
            }
        }
    }

    function systemfailcallcdr($carrier, $user, $user_inbound, $carrier_inbound, $fserror, $status) {
        $this->writelog("carrier_inbound " . str_replace('"', "'", json_encode($carrier_inbound)));
        $this->writelog("user_inbound " . str_replace('"', "'", json_encode($user_inbound)));
        $this->writelog("user " . str_replace('"', "'", json_encode($user)));

        $this->writelog("user 2  " . $user);
        $this->writelog("Carrier " . str_replace('"', "'", json_encode($carrier)));
        $this->sql_data = '';
        //$data = $this->cdr_data_process($carrier_inbound, $user_inbound);


        foreach ($user as $userkey => $uservalue) {
            if ($userkey == 'user') {
                foreach ($uservalue as $key => $value) {
                    $this->writelog("User: " . $key . ": " . $value);
                }
            }
        }
        if ($this->incomingtopstn == 1) {
            $this->writelog("In to PSTN ");
            $data = $this->cdr_data_process($carrier, $user);
        } else {
            $data = $this->cdr_data_process($carrier_inbound, $user_inbound);
        }



        $this->writelog("Data " . str_replace('"', "'", json_encode($data)));
        foreach ($data as $key => $value) {
            $this->sql_data = $this->sql_data . " $key='$value',";
        }

        $dbdate = date('Ym');
        $causeSIP = '';
        $causeQ850 = '';
        $this->fserror = $this->cdr_variable['fs_errorcode'];
        if ($this->fserror == 'CARRIERCPSISSUE') {
            $causeSIP = '503';
            $causeQ850 = '16';
            $this->otherinfo = 'CPS Limit';
        } else if ($this->fserror == 'USERINACTIVE') {
            $causeSIP = '21';
            $causeQ850 = '404';
            $this->otherinfo = 'Account is inactive';
        } else if ($this->fserror == 'USERCLI') {
            $causeSIP = '21';
            $causeQ850 = '401';
            $this->otherinfo = 'CallerID unauthorized';
        } else if ($this->fserror == 'USERBALANCE') {
            $causeSIP = '21';
            $causeQ850 = '402';
            $this->otherinfo = 'Low Balance';
        } else if ($this->fserror == 'USERRATE') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Route Missing';
        } else if ($this->fserror == 'USERTARIFFCURRENCY') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'User Currency Issue';
        } else if ($this->fserror == 'USERTARIFFINACTIVE') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'User Tariff Inactive';
        } else if ($this->fserror == 'USERRATEBLOCKED') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'User Rates Blocked';
        } else if ($this->fserror == 'USERDEVICECC') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Device Inactive';
        } else if ($this->fserror == 'USERCPS' or $this->fserror == 'USERCC') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Port Limit';
        } else if ($this->fserror == 'USERDEVICECC') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Port Limit';
        } else if ($this->fserror == 'USERDEVICECPS') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'CPS Limit';
        } else if ($this->fserror == 'RESELLERCALLERIDBLOCKED') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'CLI Issue';
        } else if ($this->fserror == 'USERCALLERIDBLOCKED') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'CLI Issue';
        } else if ($this->fserror == 'RESELLERDIALEDNUMBERBLOCKED') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Route Issue';
        } else if ($this->fserror == 'USERDIALEDNUMBER') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Route Issue';
        } else if ($this->fserror == 'USERDIALEDNUMBERBLOCKED') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Route Issue';
        } else if ($this->fserror == 'USERDIALPLAN') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Route Issue';
        } else if ($this->fserror == 'RESELLERDIALPLAN') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Route Issue';
        } else if ($this->fserror == 'RESELLERINACTIVE' or $this->fserror == 'RESELLERCURRENCY' or $this->fserror == 'RESELLERCLI' or $this->fserror == 'RESELLERBALANCE' or $this->fserror == 'RESELLERRATE' or $this->fserror == 'RESELLERLLRISSUE' or $this->fserror == 'RESELLERTARIFFCURRENCY' or $this->fserror == 'RESELLERTARIFFINACTIVE' or $this->fserror == 'RESELLERRATEBLOCKED') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Reseler Issue';
        } else if ($this->fserror == 'LLRISSUE') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Route(LCR) Issue';
        } else if ($this->fserror == 'CARRIERISSUE') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Carrier Issue';
        } else if ($this->fserror == 'CARRIERCLIISSUE') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Carrier CLI Issue';
        } else if ($this->fserror == 'ROUTINGISSUE') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Routing(Out) Issue';
        } else if ($this->fserror == 'ROUTESTATUSISSUE') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Gateway and Rates disabled or inactive or missing';
        } else if ($this->fserror == 'ROUTEMISSING') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Route Missing';
        } else if ($this->fserror == 'ROUTINGBLOCKED') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Route Blocked';
        } else if ($this->fserror == 'CODECNOTALLOWED') {
            $causeSIP = '88';
            $causeQ850 = '488';
            $this->otherinfo = 'Codec Mismatch';
        } else if ($this->fserror == 'CODECNOTALLOWED') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'Multiple call on same Number';
        } else if ($this->fserror == 'DIDNUMBER') {
            $causeSIP = '21';
            $causeQ850 = '404';
            $this->otherinfo = 'Dialed Number in User DND List';
        } else if ($this->fserror == 'DNC') {
            $causeSIP = '21';
            $causeQ850 = '503';
            $this->otherinfo = 'DND List';
        }






        $this->sql_data = $this->sql_data . "uuid = '" . addslashes($this->uuid) . "',";
        $this->sql_data = $this->sql_data . "pdd = '" . addslashes(0) . "',";

        $this->sql_data = $this->sql_data . "Q850CODE = '" . addslashes($causeQ850) . "',";

        if ($this->cdr_variable['sip_term_status'] > 0) {
            $causeSIP = $this->cdr_variable['sip_term_status'];
        }


        if ($this->fserror == 'DNC') {
            $causeSIP = '503';
        }

        $this->sql_data = $this->sql_data . "SIPCODE = '" . addslashes($causeSIP) . "',";
        $this->sql_data = $this->sql_data . "common_uuid = '" . addslashes($this->uuid) . "',";

        $this->sql_data = $this->sql_data . "callee_callid = '" . $this->caller_callid . "',";

        $this->sql_data = $this->sql_data .= "fs_errorcode = '" . $fserror . "',";
        $this->sql_data = $this->sql_data . "start_time= '" . $this->request['Event-Date-Local'] . "',";
        $this->sql_data = $this->sql_data . "end_time= '" . $this->request['Event-Date-Local'] . "',";
        $this->sql_data = $this->sql_data . "cdr_type = 'PSTN',";
        $this->sql_data = $this->sql_data . "hangupby= 'SYSTEM',";
        $this->sql_data = $this->sql_data . "disposition = '" . $fserror . "',";
        $this->sql_data = $this->sql_data . "endpoint_disposition = '" . $fserror . "',";
        $this->sql_data = $this->sql_data . "sip_hangup_disposition = '" . $fserror . "',";

if(CAMPAIGN){

        $this->sql_data = $this->sql_data . "campaign_name = '" . $this->campaign_name . "',";
        $this->sql_data = $this->sql_data . "campaign_id = '" . $this->campaign_id . "',";
        $this->sql_data = $this->sql_data . "buyer_number = '" . $this->buyer_number . "',";
        $this->sql_data = $this->sql_data . "buyer_name = '" . $this->buyer_name . "',";

}

        $this->sql_data = $this->sql_data . "did_number = '" . $this->did_number . "',";
        $this->sql_data = $this->sql_data . "billing_number = '" . $this->destination_number . "',";

        $this->sql_data = $this->sql_data . "disposition_cause = '" . $this->otherinfo . "',";
        $this->sql_data = $this->sql_data . "customer_company_name = '" . $this->userdata['company_name'] . "',";

        $this->sql_data = $this->sql_data . "carrier_src_caller= '" . addslashes($this->userdata['src_caller']) . "',";
        $this->sql_data = $this->sql_data . "carrier_src_callee= '" . addslashes($this->buyer_number) . "',";
        $this->sql_data = $this->sql_data . "carrier_dst_caller= '" . addslashes($this->carrierdata['route_callid']) . "',";
        $this->sql_data = $this->sql_data . "carrier_dst_callee= '" . addslashes($this->buyer_number) . "',";

        $cdrdata = rtrim($this->sql_data, ',');

        $query = "insert into " . $dbdate . "_ratedcdr  set " . $cdrdata;
        $this->writelog($query);
        $this->query('CDR', $query);
        $this->execute();
        $this->writelog(" SQL Data " . $this->sql_data);
    }
  function campaign_buyer_statistics_tables($date) {

        $table['campaign_buyer_statistics'] = "CREATE TABLE campaign_buyer_statistics_" . $date . " (
  id int(11) NOT NULL AUTO_INCREMENT,
  account_id varchar(30) DEFAULT NULL,
  did varchar(30) DEFAULT NULL,
  campaign_id varchar(30) DEFAULT NULL,
  buyer_name varchar(50) DEFAULT NULL,
  buyer_number varchar(30) DEFAULT NULL,
  total_calls int(11) DEFAULT '1',
  answer_calls int(11) DEFAULT '0',
  call_date date DEFAULT NULL,
  caller_id varchar(30) DEFAULT NULL,
  create_date datetime DEFAULT NULL,
  call_duration int(11) DEFAULT '0',
  PRIMARY KEY (id),
  KEY buyer_number (buyer_number),
  KEY campaign_id (campaign_id),
    UNIQUE KEY campaign_id_buyer (campaign_id,buyer_number,caller_id,call_date) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;";

        foreach ($table as $query) {
            $this->writelog($query);
            $this->query('SWITCH', $query);
            if ($this->execute()) {
                
            } else {
                $this->query('SWITCH', $query);
                $this->execute();
            }
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
        $tables = $this->campaign_buyer_statistics_tables($date);
    }

    function table_craetion_schema($date) {
        $tables['ratedcdrs'] = "CREATE TABLE IF NOT EXISTS   " . $date . "_ratedcdr (
  id int(11) NOT NULL AUTO_INCREMENT,
  uuid varchar(150) DEFAULT NULL,
  carrier_ratecard_id varchar(30) DEFAULT NULL,
  carrier_tariff_id varchar(30) DEFAULT NULL,
  carrier_prefix varchar(15) DEFAULT NULL,
  carrier_destination varchar(50) DEFAULT NULL,
  carrier_rate double(20,10) DEFAULT '0.0000000000',
  carrier_connection_charge double(20,10) DEFAULT '0.0000000000',
  carrier_minimal_time int(11) DEFAULT NULL,
  carrier_resolution_time int(11) DEFAULT NULL,
  carrier_grace_period int(11) DEFAULT NULL,
  carrier_rate_multiplier double(3,2) DEFAULT '1.00',
  carrier_rate_addition double(20,10) DEFAULT '0.0000000000',
  carrier_id varchar(30) DEFAULT NULL,
  carrier_name varchar(30) DEFAULT NULL,
  carrier_ipaddress varchar(30) DEFAULT NULL,
  carrier_ipaddress_name varchar(30) DEFAULT NULL,
  carrier_currency_id varchar(10) DEFAULT NULL,
  carrier_src_caller varchar(30) DEFAULT NULL,
  carrier_src_callee varchar(30) DEFAULT NULL,
  carrier_dst_caller varchar(30) DEFAULT NULL,
  carrier_dst_callee varchar(30) DEFAULT NULL,
  carrier_duration int(11) DEFAULT '0',
  carrier_dp int(11) DEFAULT NULL,
  carrier_tax1 double(20,10) DEFAULT '0.0000000000',
  carrier_tax2 double(20,10) DEFAULT '0.0000000000',
  carrier_tax3 double(20,10) DEFAULT '0.0000000000',
  carrier_tax_type varchar(15) DEFAULT NULL,
  carrier_callcost double(20,10) DEFAULT '0.0000000000',
  carrier_tax1_cost double(20,10) DEFAULT '0.0000000000',
  carrier_tax2_cost double(20,10) DEFAULT '0.0000000000',
  carrier_tax3_cost double(20,10) DEFAULT '0.0000000000',
  carrier_callcost_total double(20,10) DEFAULT '0.0000000000',
  carrier_dialplan_id varchar(30) DEFAULT NULL,
  customer_account_id varchar(30) DEFAULT NULL,
  customer_dp int(11) DEFAULT NULL,
  customer_tariff_id varchar(30) DEFAULT NULL,
  customer_currency_id varchar(10) DEFAULT NULL,
  customer_ipaddress varchar(30) DEFAULT NULL,
  customer_ratecard_id varchar(30) DEFAULT NULL,
  customer_prefix varchar(15) DEFAULT NULL,
  customer_destination varchar(50) DEFAULT NULL,
  customer_rate double(20,10) DEFAULT '0.0000000000',
  customer_connection_charge double(20,10) DEFAULT '0.0000000000',
  customer_minimal_time int(11) DEFAULT '1',
  customer_resolution_time int(11) DEFAULT '1',
  customer_grace_period int(11) DEFAULT '0',
  customer_rate_multiplier double(3,2) DEFAULT '1.00',
  customer_rate_addition double(20,10) DEFAULT '0.0000000000',
  customer_duration int(11) DEFAULT '0',
  customer_src_caller varchar(30) DEFAULT NULL,
  customer_src_callee varchar(30) DEFAULT NULL,
  customer_tax1 double(20,10) DEFAULT '0.0000000000',
  customer_tax2 double(20,10) DEFAULT '0.0000000000',
  customer_tax3 double(20,10) DEFAULT '0.0000000000',
  customer_tax1_cost double(20,10) DEFAULT '0.0000000000',
  customer_tax_type varchar(20) DEFAULT NULL,
  customer_callcost double(20,10) DEFAULT '0.0000000000',
  customer_tax2_cost double(20,10) DEFAULT '0.0000000000',
  customer_tax3_cost double(20,10) DEFAULT '0.0000000000',
  customer_callcost_total double(20,10) DEFAULT '0.0000000000',
  customer_src_ip varchar(30) DEFAULT NULL,
  reseller1_account_id varchar(30) DEFAULT NULL,
  reseller1_tariff_id varchar(30) DEFAULT NULL,
  reseller1_dp int(11) DEFAULT NULL,
  reseller1_ratecard_id varchar(30) DEFAULT NULL,
  reseller1_prefix varchar(15) DEFAULT NULL,
  reseller1_destination varchar(50) DEFAULT NULL,
  reseller1_rate double(20,10) DEFAULT '0.0000000000',
  reseller1_connection_charge double(20,10) DEFAULT NULL,
  reseller1_minimal_time int(11) DEFAULT '1',
  reseller1_resolution_time int(11) DEFAULT '1',
  reseller1_grace_period int(11) DEFAULT NULL,
  reseller1_rate_multiplier double(3,2) DEFAULT '1.00',
  reseller1_rate_addition double(20,10) DEFAULT '0.0000000000',
  reseller1_duration int(11) DEFAULT '0',
  reseller1_tax1 double(20,10) DEFAULT '0.0000000000',
  reseller1_tax2 double(20,10) DEFAULT '0.0000000000',
  reseller1_tax3 double(20,10) DEFAULT '0.0000000000',
  reseller1_tax_type varchar(20) DEFAULT NULL,
  reseller1_callcost double(20,10) DEFAULT '0.0000000000',
  reseller1_tax1_cost double(20,10) DEFAULT '0.0000000000',
  reseller1_tax2_cost double(20,10) DEFAULT '0.0000000000',
  reseller1_tax3_cost double(20,10) DEFAULT '0.0000000000',
  reseller1_callcost_total double(20,10) DEFAULT '0.0000000000',
  reseller2_account_id varchar(30) DEFAULT NULL,
  reseller2_tariff_id varchar(30) DEFAULT NULL,
  reseller2_dp int(11) DEFAULT NULL,
  reseller2_ratecard_id varchar(30) DEFAULT NULL,
  reseller2_prefix varchar(15) DEFAULT NULL,
  reseller2_destination varchar(50) DEFAULT NULL,
  reseller2_rate double(20,10) DEFAULT '0.0000000000',
  reseller2_connection_charge double(20,10) DEFAULT '0.0000000000',
  reseller2_minimal_time int(11) DEFAULT '1',
  reseller2_resolution_time int(11) DEFAULT '1',
  reseller2_grace_period int(11) DEFAULT '0',
  reseller2_rate_multiplier double(3,2) DEFAULT '1.00',
  reseller2_rate_addition double(20,10) DEFAULT '0.0000000000',
  reseller2_duration int(11) DEFAULT '0',
  reseller2_callcost double(20,10) DEFAULT '0.0000000000',
  reseller2_tax1 double(20,10) DEFAULT '0.0000000000',
  reseller2_tax2 double(20,10) DEFAULT '0.0000000000',
  reseller2_tax3 double(20,10) DEFAULT '0.0000000000',
  reseller2_tax_type varchar(20) DEFAULT NULL,
  reseller2_tax1_cost double(20,10) DEFAULT '0.0000000000',
  reseller2_tax2_cost double(20,10) DEFAULT '0.0000000000',
  reseller2_tax3_cost double(20,10) DEFAULT '0.0000000000',
  reseller2_callcost_total double(20,10) DEFAULT '0.0000000000',
  reseller3_account_id varchar(30) DEFAULT NULL,
  reseller3_tariff_id varchar(30) DEFAULT NULL,
  reseller3_dp int(11) DEFAULT NULL,
  reseller3_ratecard_id varchar(50) DEFAULT NULL,
  reseller3_prefix varchar(50) DEFAULT NULL,
  reseller3_destination varchar(50) DEFAULT NULL,
  reseller3_rate double(20,10) DEFAULT '0.0000000000',
  reseller3_connection_charge double(20,10) DEFAULT '0.0000000000',
  reseller3_minimal_time int(11) DEFAULT '1',
  reseller3_resolution_time int(11) DEFAULT '1',
  reseller3_grace_period int(11) DEFAULT '0',
  reseller3_rate_multiplier double(3,2) DEFAULT '1.00',
  reseller3_rate_addition double(3,2) DEFAULT '0.00',
  reseller3_duration int(11) DEFAULT '0',
  reseller3_tax1 double(20,10) DEFAULT '0.0000000000',
  reseller3_tax2 double(20,10) DEFAULT '0.0000000000',
  reseller3_tax3 double(20,10) DEFAULT '0.0000000000',
  reseller3_tax_type varchar(30) DEFAULT NULL,
  reseller3_callcost double(20,10) DEFAULT '0.0000000000',
  reseller3_tax1_cost double(20,10) DEFAULT '0.0000000000',
  reseller3_tax2_cost double(20,10) DEFAULT '0.0000000000',
  reseller3_tax3_cost double(20,10) DEFAULT '0.0000000000',
  reseller3_callcost_total double(20,10) DEFAULT '0.0000000000',
  customer_actual_callcost double(20,10) DEFAULT '0.0000000000',
  reseller1_actual_callcost double(20,10) DEFAULT '0.0000000000',
  reseller2_actual_callcost double(20,10) DEFAULT '0.0000000000',
  reseller3_actual_callcost double(12,6) DEFAULT '0.000000',
  billsec int(11) DEFAULT '0',
  start_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  answer_time datetime DEFAULT '0000-00-00 00:00:00',
  end_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  pdd int(11) DEFAULT '0',
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
  carrier_callcost_total_usercurrency double(20,10) DEFAULT '0.0000000000',
  carrier_ratio double(20,10) DEFAULT '1.0000000000',
  profit_usercurrency double(20,10) DEFAULT '0.0000000000',
  customer_company_name varchar(150) DEFAULT NULL,
  customer_incodecs varchar(300) DEFAULT NULL,
  carrier_outcodecs varchar(30) DEFAULT NULL,
  call_codecs varchar(30) DEFAULT NULL,
  data1 varchar(30) DEFAULT NULL,
  data2 varchar(30) DEFAULT NULL,
  cdr_type varchar(5) DEFAULT 'OUT',
  buyer_number varchar(30) DEFAULT NULL,
  buyer_name varchar(30) DEFAULT NULL,
  did_number varchar(100) DEFAULT NULL,
  campaign_name varchar(100) DEFAULT NULL,
  campaign_id varchar(30) DEFAULT NULL,
  route varchar(100) DEFAULT NULL,
  route_endpoint varchar(100) DEFAULT NULL,
  route_endpoint_name varchar(1000) DEFAULT NULL,
  digits_dialed varchar(100) DEFAULT NULL,
  carrier_callcost_inclusive_usercurrency double(20,10) DEFAULT NULL,
  billing_number varchar(30) DEFAULT NULL,
  fs_errorcode varchar(300) DEFAULT NULL,
  sip_from_user varchar(300) DEFAULT NULL,
  sip_from_uri varchar(300) DEFAULT NULL,
  sip_from_host varchar(300) DEFAULT NULL,
  channel_name varchar(300) DEFAULT NULL,
  sip_call_id varchar(300) DEFAULT NULL,
  ep_codec_string varchar(300) DEFAULT NULL,
  sip_network_ip varchar(300) DEFAULT NULL,
  sip_received_ip varchar(300) DEFAULT NULL,
  sip_received_port varchar(10) DEFAULT NULL,
  sip_via_protocol varchar(10) DEFAULT NULL,
  sip_from_display varchar(300) DEFAULT NULL,
  sip_full_from varchar(300) DEFAULT NULL,
  sip_to_display varchar(300) DEFAULT NULL,
  sip_full_to varchar(300) DEFAULT NULL,
  sip_req_user varchar(300) DEFAULT NULL,
  sip_req_uri varchar(300) DEFAULT NULL,
  sip_to_user varchar(300) DEFAULT NULL,
  sip_to_uri varchar(300) DEFAULT NULL,
  sip_contact_user varchar(300) DEFAULT NULL,
  sip_contact_port varchar(300) DEFAULT NULL,
  sip_contact_uri varchar(300) DEFAULT NULL,
  sip_contact_host varchar(300) DEFAULT NULL,
  rtp_use_codec_string varchar(300) DEFAULT NULL,
  sip_user_agent varchar(300) DEFAULT NULL,
  sip_via_host varchar(300) DEFAULT NULL,
  sip_via_port varchar(300) DEFAULT NULL,
  sip_via_rport varchar(300) DEFAULT NULL,
  switch_r_sdp varchar(300) DEFAULT NULL,
  endpoint_disposition varchar(300) DEFAULT NULL,
  effective_caller_id_name varchar(300) DEFAULT NULL,
  effective_caller_id_number varchar(300) DEFAULT NULL,
  sip_from_user_stripped varchar(300) DEFAULT NULL,
  start_stamp varchar(50) DEFAULT NULL,
  profile_start_stamp varchar(50) DEFAULT NULL,
  end_stamp varchar(50) DEFAULT NULL,
  caller_id varchar(300) DEFAULT NULL,
  duration int(11) DEFAULT '0',
  progresssec int(11) DEFAULT NULL,
  answersec int(11) DEFAULT NULL,
  waitsec int(11) DEFAULT NULL,
  flow_billsec int(11) DEFAULT NULL,
  sip_hangup_disposition varchar(300) DEFAULT NULL,
  in_useragent varchar(300) DEFAULT NULL,
  out_useragent varchar(300) DEFAULT NULL,
  src_extension_id varchar(30) DEFAULT NULL,
  src_extension_no varchar(30) DEFAULT NULL,
  src_extension_name varchar(30) DEFAULT NULL,
  dst_extension_id varchar(30) DEFAULT NULL,
  dst_app varchar(30) DEFAULT NULL,
  dst_app_number varchar(30) DEFAULT NULL,
  dst_app_name varchar(30) DEFAULT NULL,
  endpoint_app varchar(30) DEFAULT NULL,
  endpoint_name varchar(30) DEFAULT NULL,
  endpoint_number varchar(30) DEFAULT NULL,
  endpoint_extension_no varchar(30) DEFAULT NULL,
  endpoint_uuid varchar(500) DEFAULT NULL,
  endpoint_extension_id varchar(30) DEFAULT NULL,
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
  in_rtp_audio_in_jitter_min_variance double(20,6) DEFAULT NULL,
  in_rtp_audio_in_jitter_max_variance double(20,6) DEFAULT NULL,
  out_rtp_audio_in_jitter_min_variance double(20,6) DEFAULT NULL,
  out_rtp_audio_in_jitter_max_variance double(20,6) DEFAULT NULL,
  in_rtp_audio_in_mos double(20,6) DEFAULT NULL,
  out_rtp_audio_in_mos double(20,6) DEFAULT NULL,
  recording_file varchar(500) DEFAULT NULL,
  disposition varchar(100) DEFAULT NULL,
  disposition_cause varchar(500) DEFAULT NULL,
  identity_in varchar(500) DEFAULT NULL,
  attest_in varchar(500) DEFAULT NULL,
  identity_out varchar(500) DEFAULT NULL,
  attest_out varchar(500) DEFAULT NULL,
  PRIMARY KEY (id,end_time),
  KEY customer_account_id (customer_account_id),
  KEY end_time (end_time),
  KEY reseller3_account_id (reseller3_account_id),
  KEY reseller1_account_id (reseller1_account_id),
  KEY reseller2_account_id (reseller2_account_id),
  KEY customer_src_caller (customer_src_caller) USING BTREE,
  KEY customer_src_callee (customer_src_callee) USING BTREE,
  KEY SIPCODE (SIPCODE) USING BTREE,
  KEY billsec (billsec) USING BTREE,
  KEY customer_ipaddress (customer_ipaddress) USING BTREE,
  KEY carrier_id (carrier_id) USING BTREE,
  KEY billing_number (billing_number) USING BTREE,
  KEY carrier_currency_id (carrier_currency_id) USING BTREE,
  KEY customer_currency_id (customer_currency_id) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

        $tables['carrierstate'] = "CREATE TABLE " . $date . "_carrierstate (
  id int(11) NOT NULL AUTO_INCREMENT,
  carrier_id varchar(30) DEFAULT NULL,
  carrier_ipaddress varchar(30) DEFAULT NULL,
  bill_duration int(11) DEFAULT '0',
  carrier_duration int(11) DEFAULT '0',
  carrier_currency_id varchar(10) DEFAULT '0',
  carrier_cost double(20,10) DEFAULT '0.0000000000',
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";

        $tables['customerstate'] = "CREATE TABLE " . $date . "_customerstate (
  id int(11) NOT NULL AUTO_INCREMENT,
  account_id varchar(30) DEFAULT NULL,
  totalcalls int(11) DEFAULT '0',
  answeredcalls int(11) DEFAULT '0',
  call_date date DEFAULT NULL,
  calltime_h varchar(5) DEFAULT '0',
  calltime_m varchar(5) DEFAULT '0',
  rate double(20,10) DEFAULT NULL,
  prefix varchar(20) DEFAULT NULL,
  prefix_name varchar(50) DEFAULT NULL,
  bill_duration int(11) DEFAULT '0',
  customer_duration int(11) DEFAULT '0',
  customer_currency_id varchar(10) DEFAULT '0',
  customer_cost double(20,10) DEFAULT '0.0000000000',
  r1_prefix varchar(15) DEFAULT NULL,
  r1_account_id varchar(30) DEFAULT NULL,
  r1_rate double(20,10) DEFAULT NULL,
  r1_duration int(11) DEFAULT '0',
  r1_cost double(20,10) DEFAULT '0.0000000000',
  r2_prefix varchar(15) DEFAULT NULL,
  r2_account_id varchar(30) DEFAULT NULL,
  r2_rate double(20,10) DEFAULT NULL,
  r2_duration int(11) DEFAULT '0',
  r2_cost double(20,10) DEFAULT '0.0000000000',
  r3_prefix varchar(15) DEFAULT NULL,
  r3_account_id varchar(30) DEFAULT NULL,
  r3_rate double(20,10) DEFAULT NULL,
  r3_duration int(11) DEFAULT '0',
  r3_cost double(20,10) DEFAULT '0.0000000000',
  carrier_prefix varchar(20) DEFAULT NULL,
  carrier_ratio double(20,10) DEFAULT '1.0000000000',
  carrier_id varchar(30) DEFAULT NULL,
  carrier_duration int(11) DEFAULT '0',
  carrier_rate double(16,6) DEFAULT NULL,
  carrier_currency_id varchar(10) DEFAULT '0',
  carrier_cost double(20,10) DEFAULT '0.0000000000',
  carrier_prefix_name varchar(50) DEFAULT NULL,
  carrier_name varchar(150) DEFAULT NULL,
  carrier_callcost_total_usercurrency double(20,10) DEFAULT '0.0000000000',
  customer_company_name varchar(150) DEFAULT NULL,
  pdd int(11) DEFAULT '0',
  fscause varchar(150) DEFAULT NULL,
  Q850CODE int(11) DEFAULT NULL,
  SIPCODE varchar(15) DEFAULT NULL,
  profit_usercurrency double(20,10) DEFAULT '0.0000000000',
  src_ipaddress varchar(30) DEFAULT NULL,
  cdr_type varchar(5) DEFAULT 'OUT',
  PRIMARY KEY (id),
  UNIQUE KEY account_id (account_id,prefix,carrier_id,call_date,calltime_h,calltime_m,r1_account_id,r2_account_id,r3_account_id,Q850CODE,SIPCODE,src_ipaddress,cdr_type,rate,carrier_prefix,carrier_rate) USING BTREE,
  KEY call_date (call_date),
  KEY calltime_h (calltime_h),
  KEY calltime_m (calltime_m),
  KEY account_id_2 (account_id),
  KEY carrier_id (carrier_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";


        $tables['tcpaapilog'] = "CREATE TABLE " . $date . "_tcpaapilog (
  id int(11) NOT NULL AUTO_INCREMENT,
  account_id varchar(30) DEFAULT NULL,
  process_date datetime DEFAULT NULL,
  responce text,
  phone_number varchar(30) DEFAULT NULL,
  clean int(11) DEFAULT NULL,
  is_bad_number varchar(20) DEFAULT NULL,
  status varchar(50) DEFAULT NULL,
  account_cost double(20,6) DEFAULT NULL,
  api_cost double(20,6) DEFAULT NULL,
  request_numbertype enum('PSTN','DID') DEFAULT 'PSTN',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        $tables['pstncalls_190525'] = "CREATE TABLE " . $date . "_pstncalls (        
  id int(11) NOT NULL AUTO_INCREMENT,
  account_id varchar(30) DEFAULT NULL,
  carrier_id varchar(30) DEFAULT NULL,
  sipcode varchar(10) DEFAULT NULL,
  customer_src_caller varchar(30) DEFAULT NULL,
  carrier_dst_caller varchar(30) DEFAULT NULL,
  call_date date DEFAULT NULL,
  call_hour varchar(2) DEFAULT '00',
  call_minute varchar(2) DEFAULT '00',
  total_call_count int(11) DEFAULT '1',
  ans_call_count int(11) DEFAULT '0',
  total_duration int(11) DEFAULT '0',
  callid_prefix varchar(30) DEFAULT NULL,
  callerid_destination varchar(150) DEFAULT NULL,
  carrier_dst_callee varchar(30) DEFAULT NULL,
  carrier_dst_destination varchar(150) DEFAULT NULL, 
  PRIMARY KEY (id),
  UNIQUE KEY ccuid (account_id,carrier_id,sipcode,carrier_dst_caller,call_date,call_hour,call_minute,callerid_destination,carrier_dst_callee) USING BTREE,
  KEY carrier (carrier_id) USING BTREE,
  KEY callee (carrier_dst_callee) USING BTREE,
  KEY customer_src_caller (customer_src_caller),
  KEY account_id (account_id),
  KEY carrier_dst_caller (carrier_dst_caller)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";

        $tables['didcalls_190525'] = "CREATE TABLE " . $date . "_didcalls (        
  id int(11) NOT NULL AUTO_INCREMENT,
  account_id varchar(30) DEFAULT NULL,
  carrier_id varchar(30) DEFAULT NULL,
  sipcode varchar(10) DEFAULT NULL,
  carrier_dst_caller varchar(30) DEFAULT NULL,
  call_date date DEFAULT NULL,
  call_hour varchar(2) DEFAULT '00',
  call_minute varchar(2) DEFAULT '00',
  total_call_count int(11) DEFAULT '1',
  ans_call_count int(11) DEFAULT '0',
  total_duration int(11) DEFAULT '0',  
  didnumber_destination varchar(150) DEFAULT NULL,
  didnumber varchar(30) DEFAULT NULL, 
  PRIMARY KEY (id),
  UNIQUE KEY ccuid (account_id,carrier_id,sipcode,carrier_dst_caller,call_date,call_hour,call_minute, didnumber_destination,didnumber) USING BTREE,
  KEY carrier (carrier_id) USING BTREE,
  KEY callee (didnumber) USING BTREE,
  KEY carrier_dst_caller (carrier_dst_caller),
  KEY account_id (account_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";

        return $tables;
    }

    function __destruct() {
        try {
            $this->dbswitch = null;
            $this->dbcdr = null;
        } catch (PDOException $e) {
            exit('App shoutdown');
        }
    }
}
