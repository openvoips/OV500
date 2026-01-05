<?php

add_action('update_menu', 'endpoints_update_menu_func');

function endpoints_update_menu_func($args) {

    if (check_logged_user_group(array('customer', 'reseller'))) {
        $session_current_user_id = get_logged_user_id();
        $logged_account_id = get_logged_account_id();
        $logged_account_level = get_logged_account_level();
 
        $menu_title = 'Rates & System Config';

        $args[$menu_title]['pstnrules'] = array(
            'page_name' => array('pstnrules_index', 'customer_editSRCNo', 'customer_EnDSTrules'),
            'page_url' => 'endpoints/pstnrules/' . param_encrypt(get_logged_account_id()).'/'.param_encrypt(get_logged_user_group()),
            'menu_name' => 'PSTN No. Translation Rules'
        );

        $args[$menu_title]['didrules'] = array(
            'page_name' => array('didrules_index', 'customer_editINSRCNo', 'customer_EnDIDRule', 'editINSRCNo', 'EnDIDRule'),
            'page_url' => 'endpoints/didrules/' . param_encrypt(get_logged_account_id()),
            'menu_name' => 'DID No. Translation Rules'
        );
    }
    if (check_logged_user_group(array('customer'))) {
        $session_current_user_id = get_logged_user_id();
        $logged_account_id = get_logged_account_id();
        $logged_account_level = get_logged_account_level();

        $args[$menu_title]['sipdevice'] = array(
            'page_name' => array('sipdevice_index', 'customer_EPsipEdit', 'customer_EPsipAdd'),
            'page_url' => 'endpoints/sipdevice/' . param_encrypt(get_logged_account_id()),
            'menu_name' => 'SIP Devices'
        );
        $args[$menu_title]['ipdevice'] = array(
            'page_name' => array('ipdevice_index', 'customer_editINSRCNo', 'customer_EnDIDRule','customer_ipAdd'),
            'page_url' => 'endpoints/ipdevice/' . param_encrypt(get_logged_account_id()),
            'menu_name' => 'IP Devices'
        );


    }

    return $args;
}

?>