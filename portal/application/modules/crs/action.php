<?php

add_action('update_menu', 'crs_update_menu_func');

function crs_update_menu_func($args) {
    if (check_logged_user_group(array('RESELLER', ADMIN_ACCOUNT_ID))) {



        $menu_title = 'User Management';
        $menu_array[$menu_title] = array(
            'icon' => '<i class="fa fa-user"></i>'
        );
        $args[$menu_title]['voip'] = array(
            'page_name' => array('reseller_index', 'reseller_edit', 'reseller_add', 'reseller_payment_history', 'reseller_editSRCNo', 'reseller_translation_rules_edit', 'reseller_dialplan_add', 'reseller_dialplan_edit', 'reseller_translation_rules_incoming_edit', 'reseller_incoming_editSRCNo', 'r_statement', 'rState', 'customer_index', 'customer_edit', 'customer_add', 'customer_ip_add', 'customer_ip_edit', 'customer_sip_add', 'customer_sip_edit', 'customer_editSRCNo', 'customer_dialplan_edit', 'customer_dialplan_add', 'customer_translation_rules_edit', 'account_payment_history', 'customer_editINSRCNo', 'customer_translation_rules_incoming_edit', 'cState', 'statement', 'customer_addBundle', 'customers_edit', 'crs_index', 'crs_asignvoip', 'crs_addvoip', 'crs_editvoip', 'voip_list', 'asignvoip_list', 'add_voip', 'voip_edit', 'reseller_voip_edit', 'customer_voip_ipadd', 'customer_voip_ipedit', 'dialing_voip_addd', 'voip_editD', 'customer_voip_sipEdit', 'customer_voip_sipAdd', 'srcno_voip_editsrcno', 'voip_dstrule', 'voip_editinsrcno', 'customer_voip_didnumbertrule', 'voip_addbundle', 'reseller_voip_adddp', 'reseller_voip_srcnu', 'reseller_voip_incoming_editsrcno', 'reseller_voip_dstrules', 'reseller_voip_dstrulesin', 'customer_addBundle', 'crs_paymenthistory', 'crs_balance_edit'),
            'page_url' => 'crs',
            'menu_name' => 'My Users & Services',
        );
		  
		$menu_title = 'System & Services';    
		$args[$menu_title]['Paypal'] = array(
				'page_name' => array('paypal_index'),
				'page_url' => 'crs/payment/trace',
				'menu_name' => 'Online Payment Log',
				'icon' => '<i class="fa fa-folder-o"></i>'
		);
			
    }


    return $args;
}

?>