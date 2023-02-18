<?php

// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Openvoips Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
//OV500 Version 1.0.3
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

function call_billing_api($data, $method = 'GET') {

    $url = site_url('billing/api');
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


    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    $result = curl_exec($curl);
    $result_raw = $result;
    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
    }


    if (!$result) {
        $return_array = array('status' => false, 'msg' => $error_msg);
        $result = json_encode($return_array);
    }
    curl_close($curl); 	 
    $CI = & get_instance();
    $log_data_insert_array = array();
    $log_data_insert_array['request_data'] = json_encode($data);
    $log_data_insert_array['response_data'] = $result_raw;
    $log_data_insert_array['function_return'] = $result;
    $str = $CI->db->insert('activity_api_log', $log_data_insert_array);
	

	
    return $result;
}

function get_user_types($key = '') {
    $user_type_array = array();
    $user_type_array[1] = array('ADMIN' => 'Admin', 'SUBADMIN' => 'Sub Admin', 'ACCOUNT' => 'Account', 'ACCOUNT' => 'Account', 'ACCOUNTMANAGER' => 'Account Manager');
    $user_type_array[2] = array('RESELLERADMIN' => 'Reseller Admin', 'RESELLERUSER' => 'Reseller User', 'RESELLERACCOUNT' => 'Reseller Account');
    $user_type_array[3] = array('CUSTOMERADMIN' => 'Customer Admin', 'CUSTOMERUSER' => 'Customer User');

    if ($key == '')
        return $user_type_array;
    elseif (isset($user_type_array[$key]))
        return $user_type_array[$key];
    else {
        if (isset($user_type_array[1][$key]))
            return $user_type_array[1];
        elseif (isset($user_type_array[2][$key]))
            return $user_type_array[2];
        elseif (isset($user_type_array[3][$key]))
            return $user_type_array[3];
        else
            return array();
    }
}

function get_logged_account_id() {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }
    $session_account_id = $_SESSION['customer'][$session_current_user_id]['session_account_id'];

    $logged_user_type = get_logged_user_type();
    $user_types_array = get_user_types();

    if (isset($user_types_array[1][$logged_user_type])) {//admin
        $re_sess_account_id = ADMIN_ACCOUNT_ID;
    }

    return $session_account_id;
}

function get_logged_user_id() {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }
    $session_user_id_name = $_SESSION['customer'][$session_current_user_id]['session_user_id'];
    return $session_user_id_name;
}

function check_is_loggedin() {
	 $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
	 if ($session_current_user_id == '')
        return false;
    elseif (count($_SESSION['customer']) == 0)
        return false;
    else
        return true;
}

function check_logged_user_type($user_types) {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }

    $re_sess_type = $_SESSION['customer'][$session_current_user_id]['session_user_type'];

    if (gettype($user_types) == "array") {
        if (in_array($re_sess_type, $user_types))
            return true;
        else
            return false;
    }
    else {
        if ($re_sess_type == $user_types)
            return true;
        else
            return false;
    }
}

function get_logged_user_group() {
    $logged_user_type = get_logged_user_type();

    $user_types_array = get_user_types();

    if (isset($user_types_array[1][$logged_user_type])) {//admin
        $user_group_key = ADMIN_ACCOUNT_ID;
    } elseif (isset($user_types_array[2][$logged_user_type])) {//reseller
        $user_group_key = 'RESELLER';
    } elseif (isset($user_types_array[3][$logged_user_type])) {//customer
        $user_group_key = 'CUSTOMER';
    } else
        return false;

    return $user_group_key;
}

function check_logged_user_group($user_types) {
    $logged_user_type = get_logged_user_type();

    $user_types_array = get_user_types();

    if (isset($user_types_array[1][$logged_user_type])) {//admin
        $user_group_key = ADMIN_ACCOUNT_ID;
    } elseif (isset($user_types_array[2][$logged_user_type])) {//reseller
        $user_group_key = 'RESELLER';
    } elseif (isset($user_types_array[3][$logged_user_type])) {//customer
        $user_group_key = 'CUSTOMER';
    } else
        return false;

    if (gettype($user_types) == "array") {
        if (in_array($user_group_key, $user_types))
            return true;
        elseif (in_array(strtolower($user_group_key), $user_types))
            return true;
        else
            return false;
    }
    else {
        if ($user_group_key == $user_types)
            return true;
        else
            return false;
    }
}

function get_logged_user_type() {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }

    $session_user_type = $_SESSION['customer'][$session_current_user_id]['session_user_type'];
    return $session_user_type;
}

function get_logged_account_name() {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }

    $session_fullname = $_SESSION['customer'][$session_current_user_id]['session_account_name'];
    return $session_fullname;
}

function get_logged_user_name() {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }

    $session_fullname = $_SESSION['customer'][$session_current_user_id]['session_user_name'];
    return $session_fullname;
}

function get_logged_account_currency() {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }

    $session_currency_id = $_SESSION['customer'][$session_current_user_id]['session_currency_id'];
    return $session_currency_id;
}

function get_logged_account_status() {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }

    $session_account_status = $_SESSION['customer'][$session_current_user_id]['session_account_status'];
    return $session_account_status;
}

function get_logged_account_level() {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }
    $session_account_level = $_SESSION['customer'][$session_current_user_id]['session_account_level'];
    return $session_account_level;
}

function get_logged_acount_parameter($var_name) {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }

    $session_account_level = $_SESSION['customer'][$session_current_user_id][$var_name];
    return $session_account_level;
}

///
function get_logged_account_type() {
    return get_logged_user_group();
}

function check_logged_account_type($account_types) {
    return check_logged_user_group($account_types);
}

function get_logged_users_array() {
    $session_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($session_current_user_id == '') {
        return '';
    }
    if (isset($_SESSION['customer']) && count($_SESSION['customer']) > 1) {
        return $_SESSION['customer'];
    }
}

function get_account_full_name() {
    return get_logged_user_name();
}
function dispay_pagination_row($total_records, $s_no_of_records, $pagination) {
	if($s_no_of_records=='')
		$s_no_of_records = RECORDS_PER_PAGE;

    $str = '<div class="col-md-2 col-sm-12 col-xs-12 form-group form-inline">';
    if (isset($total_records))
        $str .= '<label class="label-control"><h5>Records Count : <strong>' . $total_records . '</strong></h5></label>';

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

function get_export_formats() {
    $export_format = array('csv', 'txt', 'pdf', 'xlsx', 'xls'); //'xlsx', 'xls', 
    return $export_format;
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
    } else {//1_way //disallowed
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

function get_permission_options($user_type) {

    $user_types_array = get_user_types();

    if (isset($user_types_array[1][$user_type])) {//admin
        $user_group_key = ADMIN_ACCOUNT_ID;
    } elseif (isset($user_types_array[2][$user_type])) {//reseller
        $user_group_key = 'RESELLER';
    } elseif (isset($user_types_array[3][$user_type])) {//customer
        $user_group_key = 'CUSTOMER';
    } else
        return array();

    if ($user_group_key == ADMIN_ACCOUNT_ID) {
        $permission_array = array(
            'user' => array('view', 'add', 'edit', 'login'),
            'reseller' => array('view', 'add', 'edit', 'delete', 'cliedit', 'login'),
            'customer' => array('view', 'add', 'edit', 'delete', 'cliedit', 'login'),
            'carrier' => array('view', 'add', 'edit', 'delete'),
            'routing' => array('view', 'add', 'edit', 'delete'),
            'dialplan' => array('view', 'add', 'edit', 'delete'),
            'ratecard' => array('view', 'add', 'edit', 'delete', 'upload'),
            'rate' => array('view', 'add', 'edit', 'delete'),
            'tariff' => array('view', 'add', 'edit', 'delete'),
            
            'provider' => array('view', 'add', 'edit', 'delete'),
         
            'system' => array('system_load'),
          
            'reports' => array('cdr', 'fail_calls', 'live', 'monin', 'CustQOSR', 'monitCarrier', 'analytics_carrier', 'accounting_billing', 'summary', 'call_report', 'report_topup', 'report_topup_monthly', 'customer_topup_summery', 'report_daily_sales', 'report_daily_sales_monthly', 'customer_sales_summery', 'statement', 'myinvoice', 'report_statement', 'ProfitLoss', 'CarrQOSR'),
        );
    } elseif ($user_group_key == 'RESELLER') {
        $permission_array = array(
            'user' => array('view', 'add', 'edit', 'login'),
            'reseller' => array('view', 'add', 'edit', 'delete', 'cliedit', 'login'),
            'customer' => array('view', 'add', 'edit', 'delete', 'cliedit', 'login'),
            'carrier' => array('view', 'add', 'edit', 'delete'),
            'routing' => array('view', 'add', 'edit', 'delete'),
            'dialplan' => array('view', 'add', 'edit', 'delete'),
            'ratecard' => array('view', 'add', 'edit', 'delete', 'upload'),
            'rate' => array('view', 'add', 'edit', 'delete'),
            'tariff' => array('view', 'add', 'edit', 'delete'),
          
            
           
            'reports' => array('cdr', 'fail_calls', 'live', 'monin', 'CustQOSR', 'monitCarrier', 'analytics_carrier', 'accounting_billing', 'summary', 'call_report', 'report_topup', 'report_topup_monthly', 'customer_topup_summery', 'report_daily_sales', 'report_daily_sales_monthly', 'customer_sales_summery', 'statement', 'myinvoice', 'report_statement'),
        );
    } elseif ($user_group_key == 'CUSTOMER') {
        $permission_array = array(
            'user' => array('view', 'add', 'edit', 'login'),
            'reseller' => array('view', 'add', 'edit', 'delete', 'cliedit'),
            'customer' => array('view', 'add', 'edit', 'delete', 'cliedit'),
            'reports' => array('fail_calls', 'report_statement', 'cdr'),
     );
    }
    return $permission_array;
}

function check_account_permission($item_name, $permission_name = '') {


    $re_sess_current_user_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
    if ($re_sess_current_user_id == '') {
        return false;
    }

    $logged_user_type = get_logged_user_type();
    if (strtolower($logged_user_type) == 'admin') {
        $permission_array = get_permission_options($logged_user_type);
        if (isset($permission_array[$item_name])) {
            if ($permission_name != '') {
                if (in_array($permission_name, $permission_array[$item_name]))
                    $is_permitted = true;
            } else
                $is_permitted = true;
        }
        return $is_permitted;
    }

    $is_permitted = false;
    $re_sess_permissions = $_SESSION['customer'][$re_sess_current_user_id]['session_permissions'];
    $permission_array = unserialize($re_sess_permissions);
    if (isset($permission_array[$item_name])) {
        if ($permission_name != '') {
            if (in_array($permission_name, $permission_array[$item_name]))
                $is_permitted = true;
        } else
            $is_permitted = true;
    }

    return $is_permitted;
}

function del_check_account_permission($item_name, $permission_name = '') {
    $session_current_customer_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';


    if ($session_current_customer_id == '') {
        return false;
    }
    if (strtolower(get_logged_user_type()) == 'admin')
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

function get_account_permission($item_name = '') {
    $session_current_customer_id = isset($_SESSION['session_current_user_id']) ? $_SESSION['session_current_user_id'] : '';
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
        $log_data_insert_array[$key]['account_id'] = $CI->session->userdata('session_current_user_id');
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
    /////
    if (count($smpt_details) > 0) {
        $config['protocol'] = 'smtp';
        $config['mailpath'] = '/usr/sbin/sendmail';

        $config['smtp_host'] = $smpt_details['smtp_host'];
        $config['smtp_user'] = $smpt_details['smtp_username'];
        $config['smtp_pass'] = $smpt_details['smtp_password'];
        $config['smtp_port'] = $smpt_details['smtp_port'];
    }
    /////
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
                } else {
                    $arg[$key] = $val;
                    //echo $val; die;
                }
            } else {
                
            }
        }
    }
    return $arg;
}

function remove_sign($singlevalue) {


    $singlesplit = array('value' => abs($singlevalue));
    if ($singlevalue < 0)
        $singlesplit['sign'] = '-';
    else
        $singlesplit['sign'] = '+';
    return $singlesplit['value'];
}

function value_without_tax($value, $tax) {
    $value = 100;
    $tax = 10;

    $ab = ($value * $tax) / 100;
}

function charges($charges, $date) {
    $no_of_days = date('t', strtotime($date));
    $current_day = date('d', strtotime($date));
    $billingdays = ($no_of_days - $current_day) + 1;
    $current_month_charges = ($charges / $no_of_days) * $billingdays;
    return $current_month_charges;
}

function exclusive_tax($tax, $cost, $taxon = 100) {
    $tax_amount = 0;
    if ($tax > 0 and $cost > 0)
        $tax_amount = (($cost * $tax) / $taxon);
    $data['cost'] = $cost;
    $data['tax_amount'] = $tax_amount;
    return $data;
}

function inclusive_tax($tax, $cost, $taxon = 100) {
    $tax_amount = 0;
    if ($tax > 0 and $cost > 0)
        $tax_amount = ($cost / ($taxon + $tax)) * $tax;
    $data['cost'] = $cost - $tax_amount;
    $data['tax_amount'] = $tax_amount;
    return $data;
}

function setup_pagination_option($total, $page, $per_page, $segment, &$pagination_instance) {

    $config = array();
    $config['total_rows'] = $total;
    $config['num_links'] = 2;
    $config['base_url'] = base_url() . $page;
    $config['per_page'] = $per_page;
    $config['uri_segment'] = $segment;
    $config['use_page_numbers'] = FALSE;
    $config['full_tag_open'] = '<ul class="pagination pagination-sm justify-content-end">';
    $config['full_tag_close'] = '</ul>';
    $config['num_tag_open'] = '<li>';
    $config['num_tag_close'] = '</li>';
    $config['cur_tag_open'] = '<li class="page-item active"><a href="javascript:void(0);" class="current"><b>';
    $config['cur_tag_close'] = '</b></a></li>';
    $config['prev_link'] = '<i class="success fa fa-angle-double-left"></i> Prev';
    $config['prev_tag_open'] = '<li class="previous">';
    $config['prev_tag_close'] = '</li>';
    $config['next_link'] = 'Next <i class="success fa fa-angle-double-right"></i>';
    $config['next_tag_open'] = '<li class="next">';
    $config['next_tag_close'] = '</li>';
    $config['first_tag_open'] = '<li class="first">';
    $config['first_tag_close'] = '</li>';
    $config['last_tag_open'] = '<li class="last">';
    $config['last_tag_close'] = '</li>';

    $pagination_instance->initialize($config);
    $pagination = $pagination_instance->create_links();

    return $pagination;
}

function generate_count_total_sql($sql) {
    $count_sql = trim($sql);

    if ($count_sql == '') {
        return 'error-Sql empty';
    }

    $pos_select = strpos($count_sql, 'FROM');
    if ($pos_select === false) {
        //Throw New Exception('select no found');
        return 'error-select no found';
    }

    $pos_limit = strrpos($count_sql, 'LIMIT');
    if ($pos_limit !== false) {
        $count_sql = substr($count_sql, 0, $pos_limit);
    }
    /* $pos_orderby = strrpos($count_sql, 'ORDER BY');	
      if($pos_orderby !== false) {
      $count_sql = substr($count_sql,0,$pos_orderby);
      } */
    $pos_groupby = strrpos($count_sql, 'GROUP BY');
    if ($pos_groupby !== false) {
        $count_sql = "SELECT COUNT(*) total FROM ($count_sql) total";
    } else {
        $count_sql = substr($count_sql, $pos_select);
        $count_sql = 'SELECT COUNT(*) total ' . $count_sql;
    }//echo $count_sql;
    return $count_sql;
}

function get_pagination_param($pagination_uri_segment, $session_key) {
    $CI = & get_instance();
    if (isset($_SESSION[$session_key]['no_of_rows']) && $_SESSION[$session_key]['no_of_rows'] != '')
        $per_page = $_SESSION[$session_key]['no_of_rows'];
    else
        $per_page = RECORDS_PER_PAGE;

    if ($CI->uri->segment($pagination_uri_segment) == '') {
        $segment = 0;
    } else {
        $segment = $CI->uri->segment($pagination_uri_segment);
    }
    return array($per_page, $segment);
}

function set_post_to_session($session_key, $param_array) {
    if (is_array($param_array) && count($param_array) >= 0) {
        foreach ($param_array as $param_key) {
            if (isset($_POST[$param_key])) {
                $_SESSION[$session_key][$param_key] = $_POST[$param_key];
            } else {
                $_SESSION[$session_key][$param_key] = '';
            }
        }
    }

    return;
}

function set_session_to_session($session_key, $param_array) {
    if (is_array($param_array) && count($param_array) >= 0) {
        foreach ($param_array as $param_key) {
            if ($param_key == 'no_of_rows')
                $_SESSION[$session_key][$param_key] = isset($_SESSION[$session_key][$param_key]) ? $_SESSION[$session_key][$param_key] : RECORDS_PER_PAGE;
            else
                $_SESSION[$session_key][$param_key] = isset($_SESSION[$session_key][$param_key]) ? $_SESSION[$session_key][$param_key] : '';
        }
    }
}

function create_menu_html($menu_array, $page_name) {

    $menu_array = do_action('update_menu', $menu_array);

    $menu_str = '';
    $site_url = site_url();
    $default_icon = '<i class="fas fa-certificate"></i>';
    foreach ($menu_array as $key => $sub_menu_array) {
        if (isset($sub_menu_array['menu_name'])) {//single page link
            //echo $sub_menu_array['menu_name'].'=>'.$site_url.$sub_menu_array['page_url'].'<br>';
            $page_name_array = $sub_menu_array['page_name'];
            $menu_name = $sub_menu_array['menu_name'];
            $page_url = $sub_menu_array['page_url'];
            $target = (isset($sub_menu_array['target']) && $sub_menu_array['target'] != '') ? 'target="' . $sub_menu_array['target'] . '"' : '';

            if (in_array($page_name, $page_name_array))
                $class = 'class="current-page"';
            else
                $class = '';

            if (isset($sub_menu_array['icon']) && $sub_menu_array['icon'] != '')
                $icon = $sub_menu_array['icon'];
            else
                $icon = $default_icon;

            if (strpos($page_url, 'http') === false)
                $link_url = $site_url . $page_url;
            else
                $link_url = $page_url;

            $menu_str .= '<li ' . $class . '><a href="' . $link_url . '" ' . $target . '>' . $icon . $menu_name . '</a></li>';
        }
        else {
            //	echo $key.'<br>';
            $menu_temp = '';
            if (isset($sub_menu_array['icon']) && $sub_menu_array['icon'] != '') {
                $icon = $sub_menu_array['icon'];
                unset($sub_menu_array['icon']);
            } else
                $icon = $default_icon;


            foreach ($sub_menu_array as $sub_key => $sub_sub_menu_array) {

                $menu_name = $sub_sub_menu_array['menu_name'];

                if (isset($sub_menu_array[$menu_name]) && count($sub_menu_array[$menu_name]) > 0) {
                    $sub_menu_temp = '';
                    $upper_li_class = '';
                    foreach ($sub_menu_array[$menu_name] as $sub_sub_key => $sub_sub_sub_menu_array) {

                        $sub_page_name_array = $sub_sub_sub_menu_array['page_name'];
                        $sub_menu_name = $sub_sub_sub_menu_array['menu_name'];
                        $sub_page_url = $sub_sub_sub_menu_array['page_url'];
                        $target = (isset($sub_sub_sub_menu_array['target']) && $sub_sub_sub_menu_array['target'] != '') ? 'target="' . $sub_sub_sub_menu_array['target'] . '"' : '';

                        if (in_array($page_name, $sub_page_name_array)) {
                            $class_sub = 'class="current-page1"';
                            $upper_li_class = 'class="active"';
                        } else
                            $class_sub = '';

                        if (strpos($sub_page_url, 'http') === false)
                            $link_url = $site_url . $sub_page_url;
                        else
                            $link_url = $sub_page_url;


                        $sub_menu_temp .= '<li ' . $class_sub . '><a href="' . $link_url . '"  ' . $target . '>' . $sub_menu_name . '</a></li>';
                    } {
                        $menu_temp .= '<li ' . $upper_li_class . '><a>' . $menu_name . ' <span class="fa fa-chevron-down"></span></a>' .
                                '<ul class="nav child_menu">' .
                                $sub_menu_temp .
                                '</ul>' .
                                '</li>';
                    }
                } elseif (isset($sub_sub_menu_array['page_url'])) {


                    $page_name_array = $sub_sub_menu_array['page_name'];
                    $page_url = $sub_sub_menu_array['page_url'];
                    $target = (isset($sub_sub_menu_array['target']) && $sub_sub_menu_array['target'] != '') ? 'target="' . $sub_sub_menu_array['target'] . '"' : '';

                    if (in_array($page_name, $page_name_array))
                        $class_sub = 'class="current-page"';
                    else
                        $class_sub = '';

                    $icon_sub = '';

                    if (strpos($page_url, 'http') === false)
                        $link_url = $site_url . $page_url;
                    else
                        $link_url = $page_url;

                    $menu_temp .= '<li ' . $class_sub . '><a href="' . $link_url . '"  ' . $target . '>' . $icon_sub . $menu_name . '</a></li>';
                }
            }
            if ($menu_temp != '') {
                $menu_str .= '<li><a>' . $icon . $key . ' <span class="fa fa-plus"></span></a>' .
                        '<ul class="nav child_menu">' .
                        $menu_temp .
                        '</ul>' .
                        '</li>';
            }
        }
    }

    return $menu_str;
}

function get_currency($currency_id, $format = 'name') {
    $currency_array = array(
        '1' => array('name' => 'USD', 'symbol' => '<i class="fa fa-usd"></i>'), //'&#36;'
        '2' => array('name' => 'GBP', 'symbol' => '<i class="fa fa-gbp"></i>'), //'&#163;'
        '3' => array('name' => 'EUR', 'symbol' => '<i class="fa fa-eur"></i>'), //'&#128;'
        '4' => array('name' => 'INR', 'symbol' => '<i class="fa fa-inr"></i>'), //'&#x20B9;'
    );

    if (isset($currency_array[$currency_id])) {
        $currency_single_array = $currency_array[$currency_id];

        if ($format == 'name')
            return $currency_single_array['name'];
        elseif ($format == 'symbol')
            return $currency_single_array['symbol'];
        else
            return $currency_single_array['name'] . '(' . $currency_single_array['symbol'] . ')';
    }
    else {
        return false;
    }
}

function dispay_pagination_row_bottom($total_records, $s_no_of_records, $pagination) {
    $str .= '<div class=" navigation-bar col-md-12 col-sm-12 col-xs-12 text-right">' .
            $pagination .
            '</div>';
    echo $str;
}

function get_logo() {
    $current_url = current_url();
    $default_logo = base_url() . 'theme/default/images/' . LOGO_IMAGE;
    $logo = $default_logo;
    if (function_exists('get_subdomain_logo')) {
        $subdomain_logo = get_subdomain_logo();
        if ($subdomain_logo != '')
            $logo = $subdomain_logo;
    }
    $random = rand(100, 1000);
    return $logo . '?r=' . $random;
}

function get_reseller_menu() {
    $menu_array = array();
    $menu_title = 'My Plans & Rates';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="fa fa-registered"></i>'
    );
    $menu_array[$menu_title]['MyPackage'] = array(
        'page_name' => array('rate_MyRates'),
        'page_url' => 'MyPackage/' . param_encrypt($_SESSION['session_current_user_id']),
        'menu_name' => 'My Package'
    );
    $menu_array[$menu_title]['my_rates'] = array(
        'page_name' => array('my_rates'),
        'page_url' => 'MyRates',
        'menu_name' => 'Call Rates'
    );
    $menu_array[$menu_title]['endpoints/index'] = array(
        'page_name' => array('extension_index', 'extension_edit', 'extension_delete', 'extension_add'),
        'page_url' => 'endpoints/index/' . param_encrypt($_SESSION['session_current_user_id']) . '/' . param_encrypt('RESELLER'),
        'menu_name' => 'SRC & DST Rules'
    );

    $menu_title = 'Rates & Package';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="fa fa-registered"></i>'
    );
    if (get_logged_account_level() < 2 && check_account_permission('ratecard', 'view')) {
        $menu_array[$menu_title]['ratecard'] = array(
            'page_name' => array('ratecard_index', 'ratecard_add', 'ratecard_edit'),
            'page_url' => 'ratecard',
            'menu_name' => 'Ratecard'
        );
    }
    if (get_logged_account_level() < 2 && check_account_permission('rate', 'view')) {
        $menu_array[$menu_title]['rates'] = array(
            'page_name' => array('rate_index', 'rate_add', 'rate_edit'),
            'page_url' => 'rates',
            'menu_name' => 'Rates'
        );
    }
    if (get_logged_account_level() < 2 && check_account_permission('tariff', 'view')) {
        $menu_array[$menu_title]['tariffs'] = array(
            'page_name' => array('tariff_index', 'tariff_add', 'tariff_edit', 'mapping_add', 'mapping_edit'),
            'page_url' => 'tariffs',
            'menu_name' => 'Tariffs'
        );
    }
    if (get_logged_account_level() < 2 && check_account_permission('bundle', 'view')) {
        $menu_array[$menu_title]['bundle'] = array(
            'page_name' => array('bundle_index', 'bundle_add', 'bundle_edit'),
            'page_url' => 'bundle',
            'menu_name' => 'Bundle & Package'
        );
    }

    //////////////////////


    $menu_title = 'Routing Management';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="fa fa-exchange"></i>'
    );
    $menu_array[$menu_title]['dids'] = array(
        'page_name' => array('did_index', 'did_add', 'did_edit'),
        'page_url' => 'dids',
        'menu_name' => 'Incoming Numbers'
    );
/////////////////////	
    $menu_array[$menu_title]['dids'] = array(
        'page_name' => array('did_index', 'did_add', 'did_edit'),
        'page_url' => 'dids',
        'menu_name' => 'Incoming Numbers',
        'icon' => '<i class="fa fa-exchange"></i>'
    );
////////////////////////

    $menu_title = 'User Management';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="fa fa-user"></i>'
    );
    if (get_logged_account_level() <= 2 && check_account_permission('customer', 'view')) {
        $menu_array[$menu_title]['customers'] = array(
            'page_name' => array('customer_index', 'customer_edit', 'customer_add', 'customer_ip_add', 'customer_ip_edit', 'customer_sip_add', 'customer_sip_edit', 'customer_editSRCNo', 'customer_dialplan_edit', 'customer_dialplan_add', 'customer_translation_rules_edit', 'account_payment_history', 'customer_editINSRCNo', 'customer_translation_rules_incoming_edit', 'cState', 'statement', 'customer_addBundle'),
            'page_url' => 'customers',
            'menu_name' => 'Customers'
        );
    }
    if (get_logged_account_level() < 2 && check_account_permission('reseller', 'view')) {
        $menu_array[$menu_title]['resellers'] = array(
            'page_name' => array('reseller_index', 'reseller_edit', 'reseller_add', 'reseller_payment_history', 'reseller_editSRCNo', 'reseller_translation_rules_edit', 'reseller_dialplan_add', 'reseller_dialplan_edit', 'reseller_translation_rules_incoming_edit', 'reseller_incoming_editSRCNo', 'r_statement', 'rState'),
            'page_url' => 'resellers',
            'menu_name' => 'Resellers'
        );
    }
    if (get_logged_account_level() < 2) {
        $menu_array[$menu_title]['users'] = array(
            'page_name' => array('account_index', 'account_add_admin', 'account_edit_admin'),
            'page_url' => 'users',
            'menu_name' => 'Users'
        );
    }


//////////////
    ////////////
    $menu_title = 'Invoices & Configuration';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="fa fa-plus-square"></i>'
    );
    if (get_logged_account_level() < 2) {
        $menu_array[$menu_title]['invoicemanagement/customerinvoice'] = array(
            'page_name' => array('customerinvoice_index', 'customerinvoice_add', 'customerinvoice_edit'),
            'page_url' => 'invoicemanagement/customerinvoice',
            'menu_name' => 'Customer Invoice'
        );

        $menu_array[$menu_title]['invoicemanagement/invoiceconfig'] = array(
            'page_name' => array('customerinvoiceconfig_index', 'customerinvoiceconfig_find', 'customerinvoiceconfig_add', 'customerinvoiceconfig_edit'),
            'page_url' => 'invoicemanagement/invoiceconfig',
            'menu_name' => 'Customer Invoice Config'
        );
        $menu_array[$menu_title]['invoicemanagement/inConfig'] = array(
            'page_name' => array('inConfig'),
            'page_url' => 'invoicemanagement/inConfig',
            'menu_name' => 'Invoice Config'
        );
        $menu_array[$menu_title]['invoicemanagement/smtpconfig'] = array(
            'page_name' => array('smtp_config_add', 'smtp_config_list', 'smtp_config_edit'),
            'page_url' => 'invoicemanagement/smtpconfig',
            'menu_name' => 'SMTP Configuration'
        );
        $menu_array[$menu_title]['invoicemanagement/emailtemplate'] = array(
            'page_name' => array('email_template_list', 'email_template_add', 'smtp_config_edit'),
            'page_url' => 'invoicemanagement/emailtemplate',
            'menu_name' => 'EMail Configuration'
        );
    }




//////////////////////
    // Reports
    $menu_title = 'Business Report';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="fa fa-bar-chart"></i>'
    );
    {

        $menu_array[$menu_title]['reports/AnsCalls'] = array(
            'page_name' => array('cdr_index'),
            'page_url' => 'reports/AnsCalls',
            'menu_name' => 'Connected Calls'
        );
    } {
        $menu_array[$menu_title]['reports/FailCalls'] = array(
            'page_name' => array('report_failed'),
            'page_url' => 'reports/FailCalls',
            'menu_name' => 'Failed Calls'
        );
    } {
        $menu_array[$menu_title]['customers/statement'] = array(
            'page_name' => array('report_statement'),
            'page_url' => 'customers/statement',
            'menu_name' => 'Account Statement'
        );
    }


    $menu_array[$menu_title]['reports/ProfitLoss'] = array(
        'page_name' => array('ProfitLoss'),
        'page_url' => 'reports/ProfitLoss',
        'menu_name' => 'Profit & Loss'
    );
    $menu_array[$menu_title]['reports/topup'] = array(
        'page_name' => array('report_topup'),
        'page_url' => 'reports/topup',
        'menu_name' => 'Topup Daily Summary'
    );


    $menu_array[$menu_title]['reports/topup_monthly'] = array(
        'page_name' => array('report_topup_monthly'),
        'page_url' => 'reports/topup_monthly',
        'menu_name' => '>Topup Monthly Summary'
    );
        

    $menu_array[] = array(
        'icon' => '<i class="fa fa-money"></i>',
        'page_name' => array('make_payment'),
        'page_url' => 'payment/make_payment',
        'menu_name' => 'Make Payment'
    );

    $menu_title = 'System & Services';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="fa fa-gear"></i>'
    );
    if (get_logged_account_level() < 1) {

        $menu_array[] = array(
            'icon' => '<i class="fa fa-ticket"></i>',
            'page_name' => array('ticket_index', 'ticket_details', 'ticket_add'),
            'page_url' => 'ticket',
            'menu_name' => 'Support Ticket'
        );
    }

    return $menu_array;
}

function ddd($data_array) {
    echo '<pre>';
    print_r($data_array);
    echo '</pre>';
}

function get_t_subjects() {
    $subject_auto_fill_array = array('Call disconnected after 3-4 ring', 'NO RBT IN UK', 'Live call is disconnected automatically', 'Call blank after connected', 'VOICE ISSUE AND LOW CONNECTIVITY', 'LOW CONNECTIVITY', 'Calls hangup', 'Voice Break For USA Destiantion', 'One way voice', 'Connectivity Issue', 'Voice break issue', 'Voice Issue and Less Connectivity', 'High PDD on all USA Calls', 'Voice Issue on Most of the Calls', 'Low connectivity/Maximum numbers going to no answer autodial', 'TFN INCOMING ISSUE', 'Customer getting ringing on connected calls', 'Issue on Ireland route', 'Blank calls in Australia route', 'Voice break & blank at UK calls', 'Voice break at UK calls', 'Less Connectivity on USA', 'Voice blank and auto disconnect', 'Wrong disposition of calls', 'Call Disconnection and Blank call', 'DID not working', 'Blank call', 'Voice break and answering machines', 'Rate mismatched', 'Issues in US calling', 'incoming issue on soft phone',
        'Billig Issues', 'Low connectivity USA', 'Less Connectivity on USA', 'Call Localisation', 'Recquired CDR of last six month', 'ask  CDR for last 3 month', 'High call charge', 'call cannot be connected', 'call connectivity issue', 'ask  CDR for last 6 month', 'Carrier Issue to India', 'call connectivity issue to dubai', 'Call not Connecting to Dubai', 'customer want to join eazytel as she is with another provider call rates are high', 'Wants CDR', 'high call charge to Switzerland and Mauritius', 'ask for all recharge details', 'ask for all billing details  with CDR', 'Back groung voice on call', 'call CDR for last 3 months.', 'call details of starting to till date .', 'Balance getting dissapered', 'calling issue getting bust tone always', 'Wants Itmize statement', 'cdr for last 6 months.', 'Calling issue to India', 'Wants CDR of last five months', 'Cst wants the CDR'
    );
    $subject_auto_fill_array = array_unique($subject_auto_fill_array);
    sort($subject_auto_fill_array);

    return $subject_auto_fill_array;
}

function get_t_status() {
    $status_array = array(
        'open' => 'Open',
        'assigned' => 'Assigned',
        'working' => 'Working',
        'waiting-confirmation' => 'Waiting Confirmation',
        'not-fixed' => 'Not Fixed',
        'overdue_tickets' => 'Overdue Tickets',
        'closed' => 'Closed'
    );
    return $status_array;
}
