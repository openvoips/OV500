<?php

/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

function call_cloudpbx_billing_api($data, $method = 'GET') {

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
?>