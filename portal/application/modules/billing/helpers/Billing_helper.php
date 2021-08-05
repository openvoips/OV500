<?php

/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

function get_tax($tax, $total) {
    $tax_amount = $total - ( $total / (1 + ($tax / 100)));
    return $tax_amount;
}

function get_mail_variables() {
    $mail_variables = array();
    $mail_variables[] = array('{{CUSTOMER_NAME}}', 'Customer Name');
    $mail_variables[] = array('{{AMOUNT}}', 'Amount');
    $mail_variables[] = array('{{COMPANY_NAME}}', 'Company Name');
    $mail_variables[] = array('{{SITE_URL}}', 'Site URL');

    return $mail_variables;
}

function replace_mail_variables($content, $replace_array = array()) {
    $mail_variables = get_mail_variables();
    $body = $content;
    if (count($mail_variables) > 0) {
        foreach ($mail_variables as $temp_array) {
            $variable = $temp_array[0];
            if (isset($replace_array[$variable]))
                $replace_text = $replace_array[$variable];
            else
                $replace_text = '';


            $body = str_replace($variable, $replace_text, $body);
        }
    }

    return $body;
}

function get_invoice_header_array($service_id) {
    $header_array = array();
    $header_array[0] = array('Description', 'Quantity', 'Price', 'Total Excl', 'VAT', 'Total Incl');
    $header_array['EQUIPMENTRENTAL'] = array('Description', 'Term', 'Month', 'Total Excl', 'VAT', 'Total Incl');
    $header_array['VOICECALL'] = array('Description', 'Minutes', 'Rate', 'Total Excl', 'VAT', 'Total Incl');

    if (isset($header_array[$service_id]))
        return $header_array[$service_id];
    else
        return $header_array[0];
}

function invoice_rearrange_did($item_array_multiple) {
    $final_send_array = array();
    $matched_key_array = array();

    $previous_did_array = array_shift($item_array_multiple);
    $previous_did = $previous_did_array['dst'];
    $final_send_array[$previous_did] = $previous_did_array;
    foreach ($item_array_multiple as $key => $item_array) {
        $current_did = $item_array['dst'];
        if ($previous_did + 1 == $current_did) {
            $previous_array = $final_send_array[$previous_did];
            unset($final_send_array[$previous_did]);

            $previous_array['dst'] = $previous_array['dst'] . '-' . $item_array['dst'];
            $previous_array['rate'] = $previous_array['rate'] + $item_array['rate'];
            $previous_array['quantity'] = $previous_array['quantity'] + $item_array['quantity'];
            $previous_array['tax1_amount'] = $previous_array['tax1_amount'] + $item_array['tax1_amount'];
            $previous_array['tax2_amount'] = $previous_array['tax2_amount'] + $item_array['tax2_amount'];
            $previous_array['tax3_amount'] = $previous_array['tax3_amount'] + $item_array['tax3_amount'];
            $previous_array['charges'] = $previous_array['charges'] + $item_array['charges'];
            $previous_array['total_charges'] = $previous_array['total_charges'] + $item_array['total_charges'];

            $final_send_array[$current_did] = $previous_array;

            $matched_key_array[$current_did] = $current_did;
            if (isset($matched_key_array[$previous_did]))
                unset($matched_key_array[$previous_did]);
        }
        else {
            $final_send_array[$current_did] = $item_array;
        }

        $previous_did = $current_did;
    }

    if (count($matched_key_array) > 0) {
        foreach ($matched_key_array as $did_key) {
            $array_temp = $final_send_array[$did_key];
            $dst = $array_temp['dst'];
            $dst_array = explode('-', $dst);
            $length = count($dst_array);
            $first_str = $dst_array[0];
            $last_str = $dst_array[$length - 1];
            for ($i = 1; $i <= $length; $i++) {
                if (substr($first_str, 0, -$i) == substr($last_str, 0, -$i)) {
                    $updated_dst = $first_str . '-' . substr($last_str, -$i - 1);
                    $final_send_array[$did_key]['dst'] = $updated_dst;
                    break;
                }
            }
        }
    }

    //$final_send_array =$item_array_multiple;

    return $final_send_array;
}

function calculate_total_item($main_array) {
    $count = 0;
    foreach ($main_array as $sub_array) {
        $count += count($sub_array);
    }
    return $count;
}

?>