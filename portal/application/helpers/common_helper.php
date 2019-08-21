<?php
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

function dispay_pagination_row($total_records, $s_no_of_records, $pagination) {

    $str = '<div class="col-md-2 col-sm-12 col-xs-12 form-group form-inline">';
    if (isset($total_records))
        $str .='<label class="label-control"><h5>Records Count : <strong>' . $total_records . '</strong></h5></label>';

    $str .='</div>
            <div class=" col-md-3 col-sm-12 col-xs-12 form-group form-inline">
                  <label><h5>No of Records : </h5></label>
                  <select name="no_of_records" id="no_of_records" class="form-control data-search-field">';

    $records_per_page_array = unserialize(RECORDS_PER_PAGE_ARRAY);

    foreach ($records_per_page_array as $records_per_page) {
        $selected = ' ';
        if ($s_no_of_records == $records_per_page)
            $selected = '  selected="selected" ';
        $str .= '<option value="' . $records_per_page . '" ' . $selected . '>' . $records_per_page . '</option>';
    }

    $str .=' </select>          
            </div>            
			<div class=" navigation-bar col-md-7 col-sm-12 col-xs-12 text-right">' .
            $pagination .
            '</div>';
    echo $str;
}

function get_export_formats() {
    $export_format = array('csv', 'txt', 'pdf', 'xlsx', 'xls'); //'xlsx', 'xls', 
    return $export_format;
}

function get_account_types($key = '') {
    $account_type_array = array();
    $account_type_array[1] = array('ADMIN' => 'Supper Admin', 'SUBADMIN' => 'Admin User', 'ACCOUNTS' => 'Accounts & Billing');
    $account_type_array[2] = array('CUSTOMER' => 'Customer');
    $account_type_array[3] = array('RESELLER' => 'Reseller');

    if ($key == '')
        return $account_type_array;
    elseif (isset($account_type_array[$key]))
        return $account_type_array[$key];
    else
        return array();
}

function get_account_full_name() {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return '';
    }

    $session_fullname = $_SESSION['customer'][$session_current_customer_id]['session_fullname'];
    return $session_fullname;
}

function get_logged_account_status() {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return '';
    }

    $session_account_status = $_SESSION['customer'][$session_current_customer_id]['session_account_status'];
    return $session_account_status;
}

function get_logged_account_currency() {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return '';
    }

    $session_currency_id = $_SESSION['customer'][$session_current_customer_id]['session_currency_id'];
    return $session_currency_id;
}

function get_logged_account_type() {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return '';
    }

    $session_type = $_SESSION['customer'][$session_current_customer_id]['session_account_type'];
    return $session_type;
}

function get_logged_account_id() {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return '';
    }

    $session_account_id = $_SESSION['customer'][$session_current_customer_id]['session_account_id'];
    return $session_account_id;
}

function get_logged_customer_id() {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return '';
    }

    $session_customer_id = $_SESSION['customer'][$session_current_customer_id]['session_customer_id'];
    return $session_customer_id;
}

function check_is_loggedin() {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '')
        return false;
    else
        return true;
}

function check_logged_account_type($account_types) {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return false;
    }
    $session_type = $_SESSION['customer'][$session_current_customer_id]['session_account_type'];
    if (gettype($account_types) == "array") {
        if (in_array($session_type, $account_types))
            return true;
        else
            return false;
    }
    else {
        if ($session_type == $account_types)
            return true;
        else
            return false;
    }
}

/* returns user level */

function get_logged_account_level() {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return '';
    }
    $session_account_level = $_SESSION['customer'][$session_current_customer_id]['session_account_level'];
    return $session_account_level;
}

/* returns user session variable */

function get_logged_acount_parameter($var_name) {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return '';
    }

    $session_account_level = $_SESSION['customer'][$session_current_customer_id][$var_name];
    return $session_account_level;
}

function param_encrypt($pure_string) {
    $dirty = array("+", "/", "=");
    $clean = array("_PLUS_", "_SLASH_", "_EQUALS_");


    $encrypted_string = base64_encode($pure_string);
    return str_replace($dirty, $clean, $encrypted_string);
}

function param_decrypt($string) {
    $dirty = array("+", "/", "=");
    $clean = array("_PLUS_", "_SLASH_", "_EQUALS_");
    $result = '';
    $result = str_replace($clean, $dirty, $string);
    $result = base64_decode($result);
    return $result;
}

function arrayToObject($array) {
    if (!is_array($array)) {
        return $array;
    }

    $object = new stdClass();
    if (is_array($array) && count($array) > 0) {
        foreach ($array as $name => $value) {
            //$name = strtolower(trim($name));
            $name = trim($name);
            if (!empty($name)) {
                $object->$name = arrayToObject($value);
            }
        }
        return $object;
    } else {
        return FALSE;
    }
}

//validates and return rule fields for callerid
function generate_rule_fields($rule_string_temp, $checking_type = '2_way') {
    //$pattern = '/[^0-9%|=> ]/';
    $pattern = '/[\\\\<>`~!\/@]/';
    $pattern_2 = '/[|]/';
    $pattern_3 = '#\{([0-9]+)\}#'; //find match length

    $match_length = '';
    if ($checking_type == 'dst_src_cli') {
        $rule_string = str_replace(array('=>'), ':==:', $rule_string_temp);

        if (preg_match($pattern, $rule_string)) {
            return array('status' => false, 'message' => 'Unsupported characters at ' . $rule_string_temp);
        }
        $sep_count = substr_count($rule_string, ':==:');
        if ($sep_count != 1)
            return array('status' => false, 'message' => 'Unsupported format at ' . $rule_string_temp);

        $rule_string_array = explode(':==:', $rule_string);

        //dont allow pipe in add string
        if (preg_match($pattern_2, $rule_string_array[1])) {
            return array('status' => false, 'message' => 'Unsupported characters at ' . $rule_string_array[1]);
        }

        $pos1 = strpos($rule_string_array[0], '{');
        $pos2 = strpos($rule_string_array[0], '}');
        if ($pos1 !== false && $pos2 !== false) {//both found
            $bracket_str = substr($rule_string_array[0], $pos1 + 1, $pos2 - $pos1 - 1);
            $match_length = $bracket_str;

            if (preg_match('#([^0-9]+)#', $match_length, $match)) {
                return array('status' => false, 'message' => 'Invalid character within brackets');
            }

            $rule_string_array[0] = str_replace('{' . $bracket_str . '}', '', $rule_string_array[0]);
        } elseif ($pos1 !== false) {//only { found
            return array('status' => false, 'message' => 'Closing curly bracket not found');
        } elseif ($pos2 !== false) {//only } found
            return array('status' => false, 'message' => 'Opening curly bracket not found');
        }


        $maching_string = str_replace('|', '', $rule_string_array[0]);

        if (strpos($rule_string_array[0], '|') === false)
            $remove_string = '';
        else
            $remove_string = strstr($rule_string_array[0], '|', true);

        $add_string = str_replace(array('|'), '', $rule_string_array[1]);
    } elseif ($checking_type == '2_way') {

        $rule_string = str_replace(array('=>'), ':==:', $rule_string_temp);

        if (preg_match($pattern, $rule_string)) {
            return array('status' => false, 'message' => 'Unsupported characters at ' . $rule_string_temp);
        }
        $sep_count = substr_count($rule_string, ':==:');
        if ($sep_count != 1)
            return array('status' => false, 'message' => 'Unsupported format at ' . $rule_string_temp);

        $rule_string_array = explode(':==:', $rule_string);

        //dont allow pipe in add string
        if (preg_match($pattern_2, $rule_string_array[1])) {
            return array('status' => false, 'message' => 'Unsupported characters at ' . $rule_string_array[1]);
        }

        $pos1 = strpos($rule_string_array[0], '{');
        $pos2 = strpos($rule_string_array[0], '}');
        if ($pos1 !== false && $pos2 !== false) {//both found
            $bracket_str = substr($rule_string_array[0], $pos1 + 1, $pos2 - $pos1 - 1);
            $match_length = $bracket_str;

            if (preg_match('#([^0-9]+)#', $match_length, $match)) {
                return array('status' => false, 'message' => 'Invalid character within brackets');
            }

            $rule_string_array[0] = str_replace('{' . $bracket_str . '}', '', $rule_string_array[0]);
        } elseif ($pos1 !== false) {//only { found
            return array('status' => false, 'message' => 'Closing curly bracket not found');
        } elseif ($pos2 !== false) {//only } found
            return array('status' => false, 'message' => 'Opening curly bracket not found');
        }

        /* if(	preg_match('#\{([0-9]+)\}#', $rule_string_array[0], $match))
          {
          $match_length = $match[1];
          $rule_string_array[0] = str_replace($match[0],'',$rule_string_array[0]);
          }
         */
        $maching_string = str_replace('|', '', $rule_string_array[0]);

        if (strpos($rule_string_array[0], '|') === false)
            $remove_string = '';
        else
            $remove_string = strstr($rule_string_array[0], '|', true);

        $add_string = str_replace(array('|'), '', $rule_string_array[1]);
    }
    else {//1_way //disallowed
        if (preg_match($pattern, $rule_string_temp)) {
            return array('status' => false, 'message' => 'Unsupported characters at ' . $rule_string_temp);
        }
        //dont allow pipe in add string
        if (preg_match($pattern_2, $rule_string_temp)) {
            return array('status' => false, 'message' => 'Unsupported characters at ' . $rule_string_temp);
        }

        $pos1 = strpos($rule_string_temp, '{');
        $pos2 = strpos($rule_string_temp, '}');
        if ($pos1 !== false && $pos2 !== false) {//both found
            $bracket_str = substr($rule_string_temp, $pos1 + 1, $pos2 - $pos1 - 1);
            $match_length = $bracket_str;

            if (preg_match('#([^0-9]+)#', $match_length, $match)) {
                return array('status' => false, 'message' => 'Invalid character within brackets');
            }

            $rule_string_temp = str_replace('{' . $bracket_str . '}', '', $rule_string_temp);
        } elseif ($pos1 !== false) {//only { found
            return array('status' => false, 'message' => 'Closing curly bracket not found');
        } elseif ($pos2 !== false) {//only } found
            return array('status' => false, 'message' => 'Opening curly bracket not found');
        }

        $maching_string = str_replace('|', '', $rule_string_temp);
        $add_string = '%';
        $remove_string = '%';
    }
    return array(
        'status' => true,
        'maching_string' => trim($maching_string),
        'match_length' => trim($match_length),
        'remove_string' => trim($remove_string),
        'add_string' => trim($add_string)
    );
}

/*
  set all the possible permissions
 */

function get_permission_options() {
    $permission_array = array(
        'admin' => array('view', 'add', 'edit'),
        'reseller' => array('view', 'add', 'edit', 'delete', 'cliedit'),
        'customer' => array('view', 'add', 'edit', 'delete', 'cliedit'),
        'carrier' => array('view', 'add', 'edit', 'delete'),
        'routing' => array('view', 'add', 'edit', 'delete'),
        'dialplan' => array('view', 'add', 'edit', 'delete'),
        'ratecard' => array('view', 'add', 'edit', 'delete', 'upload'),
        'rate' => array('view', 'add', 'edit', 'delete'),
        'tariff' => array('view', 'add', 'edit', 'delete'),
        'service' => array('view', 'add', 'edit', 'delete'),
        'reports' => array('cdr', 'fail_calls', 'live', 'monin', 'CustQOSR', 'monitCarrier', 'analytics_carrier', 'accounting_billing', 'summary', 'call_report', 'report_topup', 'report_topup_monthly', 'customer_topup_summery', 'report_daily_sales', 'report_daily_sales_monthly', 'customer_sales_summery', 'statement', 'myinvoice', 'report_statement'),
    );
    return $permission_array;
}

/*
  parameters
  item_name= 'rates','carrier' etc
  permission_name = 'add','edit' etc

  return true/false
 */

function check_account_permission($item_name, $permission_name = '') {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';

    //echo $session_current_customer_id;
    if ($session_current_customer_id == '') {
        return false;
    }
    if (strtolower(get_logged_account_type()) == 'admin')
        return true;

    if (strtolower(get_logged_account_type()) == 'reseller')
        return true;

    $is_permitted = false;
    $session_permissions = $_SESSION['customer'][$session_current_customer_id]['session_permissions'];
    $permission_array = unserialize($session_permissions);
    if (isset($permission_array[$item_name])) {
        if ($permission_name != '') {
            if (in_array($permission_name, $permission_array[$item_name]))
                $is_permitted = true;
        } else
            $is_permitted = true;
    }
    return $is_permitted;
}

/*
  parameters
  item_name= 'rates','carrier' etc
  return
  full permission array
  specific item permission array
 */

function get_account_permission($item_name = '') {
    $session_current_customer_id = isset($_SESSION['session_current_customer_id']) ? $_SESSION['session_current_customer_id'] : '';
    if ($session_current_customer_id == '') {
        return false;
    }

    $session_permissions = $_SESSION['customer'][$session_current_customer_id]['session_permissions'];
    $permission_array = unserialize($session_permissions);

    $account_permission_array = array();
    if ($item_name != '') {
        if (isset($permission_array[$item_name])) {
            $account_permission_array = $permission_array[$item_name];
        }
    } else {
        $account_permission_array = $permission_array;
    }
    return $account_permission_array;
}

function generate_key($name, $type = '') {
    $abbreviate = function($strString, $intLength) {
        $defaultAbbrevLength = 8;
        $strString = ucwords($strString);
        $strString = preg_replace("/[^A-Za-z0-9]/", '', $strString);
        $stringIndex = 0;
        $uppercaseCount = preg_match_all('/[A-Z0-9]/', $strString, $uppercaseLetters, PREG_OFFSET_CAPTURE);
        $targetLength = isset($intLength) ? intval($intLength) : $defaultAbbrevLength;
        $uppercaseCount = $uppercaseCount > $targetLength ? $targetLength : $uppercaseCount;
        $targetWordLength = round($targetLength / intval($uppercaseCount));
        $abbrevLength = 0;
        $abbreviation = '';
        for ($i = 0; $i < $uppercaseCount; $i++) {
            $ucLetters[] = $uppercaseLetters[0][$i][0];
        }
        $characterDeficit = 0;
        $wordIndex = $targetWordLength;
        while ($stringIndex < strlen($strString)) {
            if ($abbrevLength >= $targetLength)
                break;
            $currentChar = $strString[$stringIndex++];
            if (in_array($currentChar, $ucLetters)) {
                $characterDeficit += $targetWordLength - $wordIndex;
                $wordIndex = 0;
            } else if ($wordIndex >= $targetWordLength) {
                if ($characterDeficit == 0)
                    continue;
                else
                    $characterDeficit--;
            }
            $abbreviation .= $currentChar;
            $abbrevLength++;
            $wordIndex++;
        }
        return $abbreviation;
    };
    $abbr = strtoupper($abbreviate($name, 8));
    $num = abs(crc32($name . time()));
    $sum = 0;
    $rem = 0;
    for ($i = 0; $i <= strlen($num); $i++) {
        $rem = $num % 10;
        $sum = $sum + $rem;
        $num = $num / 10;
    }
    return $abbr . $sum . $type;
}

function callSdrAPI($data, $method = 'GET') { 
   // $url = SDR_API_URL;
    $url = "http://localhost/ardent/api/";
    $curl = curl_init();
    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }


    //echo $url.'<br><br><br>';
    // OPTIONS:
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'APIKEY: SWITCH_KEY',
        'Content-Type: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // EXECUTE:
    $result_raw = $result = curl_exec($curl);
    if (!$result) {
        $return_array = array('error' => 0, 'message' => 'Connection Failure');
        $result = json_encode($return_array);
        //die("Connection Failure");
    }
    curl_close($curl);


    ///////////save api data & response to table////////////
    $CI = & get_instance();
    $log_data_insert_array = array();
    $log_data_insert_array['request_data'] = json_encode($data);
    $log_data_insert_array['response_data'] = $result_raw;
    $log_data_insert_array['function_return'] = $result;
    //$str = $CI->db->insert('activity_api_log', $log_data_insert_array); 
    $DB1 = $CI->load->database('cdrdb', true);
    $str = $DB1->insert('activity_api_log', $log_data_insert_array);
    //var_dump($str);
    //////////////////////////


    return $result;
}

function set_activity_log($log_data_array) {//$activity_type, $sql_table, $sql_key,$sql_query,$logged_customer_id
    if (count($log_data_array) == 0)
        return;
    $CI = & get_instance();
    $sql = "SELECT MAX(activity_id) max_activity_id FROM " . $CI->db->dbprefix('activity_log');
    $query = $CI->db->query($sql);
    $row = $query->row();
    if (isset($row)) {
        $max_activity_id = $row->max_activity_id;
    } else {
        $max_activity_id = 0;
    }
    $new_activity_id = $max_activity_id + 1;
    $log_data_insert_array = array();
    $key = 0;
    foreach ($log_data_array as $log_data_temp_array) {//print_r($log_data_array);echo $key;die;
        $log_data_insert_array[$key]['activity_id'] = $new_activity_id;
        $log_data_insert_array[$key]['activity_type'] = $log_data_temp_array['activity_type'];
        $log_data_insert_array[$key]['sql_table'] = $log_data_temp_array['sql_table'];
        $log_data_insert_array[$key]['sql_key'] = $log_data_temp_array['sql_key'];
        $log_data_insert_array[$key]['sql_query'] = $log_data_temp_array['sql_query'];
        $log_data_insert_array[$key]['account_id'] = $CI->session->userdata('session_current_customer_id');
        $key++;
    }
    $result = $CI->db->insert_batch('activity_log', $log_data_insert_array);
    //echo '<pre>';print_r($log_data_insert_array);echo $result;die;
}

function generateRandom($length) {
    $order = "";
    $possible = "0123456789abcdefghijklmnopqrstuvwxyz";
    $i = 0;
    while ($i < $length) {
        $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
        if (!strstr($order, $char)) {
            $order .= $char;
            $i++;
        }
    }
    return $order;
}

/* make an array of all files within a directory */

function dirToArray($dir) {

    $result = array();
    if (!is_dir($dir))
        return $result;
    $cdir = scandir($dir, SCANDIR_SORT_DESCENDING);
    foreach ($cdir as $key => $value) {
        if (!in_array($value, array(".", ".."))) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                //$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
            } else {
                $result[] = $value;
            }
        }
    }
    return $result;
}

function send_mail($message, $subject, $mail_to, $mail_from, $mail_from_name, $cc = '', $bcc = '', $account_id = '', $actionfrom = '', $attachment_array = array()) {
    $CI = & get_instance();

    $CI->load->library('email');
    $CI->email->clear();

    $mail_config['charset'] = 'iso-8859-1';
    $mail_config['wordwrap'] = TRUE;
    $mail_config['mailtype'] = 'html';
    $CI->email->initialize($mail_config);

    $CI->email->from($mail_from, $mail_from_name);
    $CI->email->subject($subject);
    $CI->email->message($message);

    $CI->email->to($mail_to);
    if ($cc != '')
        $CI->email->cc($cc);
    if ($bcc != '')
        $CI->email->bcc($bcc);

    /* if(count($attachment_array)>0)
      {
      foreach($attachment_array as $attachment)
      {
      $CI->email->attach($attachment);
      }
      }
     */
    if (count($attachment_array) > 0) {
        foreach ($attachment_array as $attachment) {
            if (is_array($attachment) && isset($attachment[0]) && isset($attachment[1])) {
                $CI->email->attach($attachment[0], 'attachment', $attachment[1]);
                //filepath                       file name to display
            } elseif (is_array($attachment)) {
                $CI->email->attach(current($attachment));
            } else {
                $CI->email->attach($attachment);
            }
        }
    }

    $res = $CI->email->send();


    ///////////keep log/////////
    $email_to = $mail_to;
    if ($cc != '')
        $email_to .= ',' . $cc;
    if ($account_id == '')
        $account_id = 'System';
    if ($actionfrom == '')
        $actionfrom = 'GUI';
    else
        $actionfrom = 'GUI-' . $actionfrom;

    $email_log_data_array = array();
    $email_log_data_array['account_id'] = $account_id;
    $email_log_data_array['action_date'] = date('Y-m-d h:i:s');
    $email_log_data_array['subject'] = $subject;
    $email_log_data_array['body'] = $message;
    $email_log_data_array['actionfrom'] = $actionfrom;
    $email_log_data_array['email_to'] = $email_to;
    $CI = & get_instance();
    $str = $CI->db->insert_string($CI->db->dbprefix('emaillog'), $email_log_data_array);
    $result = $CI->db->query($str);
    return $res;
}

function convertToReadableSize($size) {
    $base = log($size) / log(1024);
    $suffix = array("", "KB", "MB", "GB", "TB");
    $f_base = floor($base);
    return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
}

function dispay_pagination_row111($total_records, $s_no_of_records, $pagination) {

    $str = '<div class="col-md-2 col-sm-12 col-xs-12 form-group form-inline">';
    if (isset($total_records))
        $str .= '<label class="label-control"><h5>Total Records : <strong>' . $total_records . '</strong></h5></label>';

    $str .= '</div>
            <div class=" col-md-3 col-sm-12 col-xs-12 form-group form-inline">
                  <label><h5>No of Records : </h5></label>
                  <select name="no_of_records" id="no_of_records" class="form-control data-search-field">';

    $records_per_page_array = unserialize(RECORDS_PER_PAGE_ARRAY);

    foreach ($records_per_page_array as $records_per_page) {
        $selected = ' ';
        if ($s_no_of_records == $records_per_page)
            $selected = '  selected="selected" ';
        $str .= '<option value="' . $records_per_page . '" ' . $selected . '>' . $records_per_page . '</option>';
    }

    $str .= ' </select>          
            </div>            
			<div class=" navigation-bar col-md-7 col-sm-12 col-xs-12 text-right">' .
            $pagination .
            '</div>';
    echo $str;
}

function getUserIP() {
    $client = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote = $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }

    return $ip;
}

/*
  sanitize data
  remove any unwanted data
  /replace double quotes with single quotes
 */

function clear_junks($string) {
    // Replace Single Curly Quotes
    $search[] = chr(226) . chr(128) . chr(152);
    $replace[] = "'";
    $search[] = chr(226) . chr(128) . chr(153);
    $replace[] = "'";
    // Replace Smart Double Curly Quotes
    $search[] = chr(226) . chr(128) . chr(156);
    $replace[] = '"';
    $search[] = chr(226) . chr(128) . chr(157);
    $replace[] = '"';
    // Replace En Dash
    $search[] = chr(226) . chr(128) . chr(147);
    $replace[] = '--';
    // Replace Em Dash
    $search[] = chr(226) . chr(128) . chr(148);
    $replace[] = '---';
    // Replace Bullet
    $search[] = chr(226) . chr(128) . chr(162);
    $replace[] = '*';
    // Replace Middle Dot
    $search[] = chr(194) . chr(183);
    $replace[] = '*';
    // Replace Ellipsis with three consecutive dots
    $search[] = chr(226) . chr(128) . chr(166);
    $replace[] = '...';

    //replace double quotes with single quotes
    $search[] = '"';
    $replace[] = "'";

    // Apply Replacements
    $string = str_replace($search, $replace, $string);
    // Remove any non-ASCII Characters
    $string = preg_replace("/[^\x01-\x7F]/", "", $string);
    return $string;
}

/*
  sanitize data
  remove any unwanted data
  add slashes & trim

 */

function add_slashes($data_array, $escape_field_array = array()) {
    $data_type = gettype($data_array);

    if ($data_type == 'string')
        $arg = addslashes(trim(clear_junks($data_array)));
    elseif ($data_type == 'integer' || $data_type == 'double')
        $arg = addslashes(trim(clear_junks($data_array)));
    elseif ($data_type == 'array') {
        foreach ($data_array as $key => $val) {
            if (in_array($key, $escape_field_array)) {
                continue;
            }
            $var_type = gettype($val);

            if ($var_type == 'string')
                $arg[$key] = addslashes(trim(clear_junks($val)));
            elseif ($var_type == 'integer' || $var_type == 'double')
                $arg[$key] = addslashes(trim(clear_junks($val)));
            elseif ($var_type == 'array') {
                foreach ($val as $sub_key => $sub_val) {
                    $sub_var_type = gettype($sub_val);
                    if ($sub_var_type == 'string')
                        $arg[$key][$sub_key] = addslashes(trim(clear_junks($sub_val)));
                    elseif ($sub_var_type == 'integer' || $sub_var_type == 'double')
                        $arg[$key][$sub_key] = addslashes(trim(clear_junks($sub_val)));
                    elseif ($sub_var_type == 'array') {
                        $arg[$key][$sub_key] = $sub_val;
                    }
                }
            } else {
                
            }
        }
    }
    return $arg;
}

/*
  remove slashes from table data
  use it after fetching dta from db
 */

function strip_slashes($data_array, $escape_field_array = array()) {
    $data_type = gettype($data_array);

    if ($data_type == 'string')
        $arg = stripslashes(trim($data_array));
    elseif ($data_type == 'integer' || $data_type == 'double')
        $arg = stripslashes(trim($data_array));
    elseif ($data_type == 'array') {
        foreach ($data_array as $key => $val) {
            if (in_array($key, $escape_field_array)) {
                continue;
            }
            $var_type = gettype($val);

            if ($var_type == 'string')
                $arg[$key] = stripslashes(trim($val));
            elseif ($var_type == 'integer' || $var_type == 'double')
                $arg[$key] = stripslashes(trim($val));
            elseif ($var_type == 'array') {
                if (count($val) > 0) {
                    foreach ($val as $sub_key => $sub_val) {
                        $sub_var_type = gettype($sub_val);
                        if ($sub_var_type == 'string')
                            $arg[$key][$sub_key] = stripslashes(trim($sub_val));
                        elseif ($sub_var_type == 'integer' || $sub_var_type == 'double')
                            $arg[$key][$sub_key] = stripslashes(trim($sub_val));
                    }
                }
                else {
                    $arg[$key] = $val;
                    //echo $val; die;
                }
            } else {
                
            }
        }
    }
    return $arg;
}