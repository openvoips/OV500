<?php

$CI = & get_instance();
$account_id = get_logged_account_id();

////////////
$account_id_check = $account_id;
$parent_menu_array = array();
while (1) {
    $sql = "SELECT menu FROM menus WHERE account_id='$account_id_check'";
    $query = $CI->db->query($sql);
    $row = $query->row();
    if (isset($row)) {
        $custom_menu = $row->menu;
        $parent_menu_array = unserialize($custom_menu);
        break;
    } else {
        $sql = "SELECT parent_account_id, account_level FROM account WHERE account_id='$account_id_check' AND parent_account_id!=''";
        $query = $CI->db->query($sql);
        $row = $query->row();
        if (isset($row)) {
            $account_id_check = $row->parent_account_id;
        } else {
            break;
        }
    }
}

if (check_logged_user_type(array('EXTENSION'))) {

    unset( $menu_array);
    

    $menu_array['featurecodes'] = array(
		'page_name'=>array('extension','add_extension','edit_extension'),
		'page_url'=>'pbx/featurecodes',
		'menu_name'=>'Feature Codes'
	);
	$menu_array['mysettings'] = array(
		'page_name'=>array('mysettings_extension'),
		'page_url'=>'pbx/extension',
		'menu_name'=>'Profile & Settings'
	);
	$menu_array['myvoicemail'] = array(
		'page_name'=>array('myvoicemail'),
		'page_url'=>'pbx/extension/myvoicemail',
		'menu_name'=>'Voice Mails'
	);
	


}
else{


    $menu_title = 'Make Payment';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="material-icons">paid</i>'
    );
    $menu_title = 'Rates & System Config';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="material-icons">price_check</i>'
    );

    $menu_array[$menu_title]['MyRates'] = array(
        'page_name' => array('my_rates'),
        'page_url' => 'MyRates',
        'menu_name' => 'My Call Rates'
    );

    $menu_title = 'DID Numbers';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="material-icons">format_list_numbered</i>'
    );

    $menu_array[$menu_title]['dids'] = array(
        'page_name' => array('did_index', 'did_edit'),
        'page_url' => 'dids',
        'menu_name' => 'Numbers'
    );
    // Reports
    $menu_title = 'Calls Report';
    $menu_array[$menu_title] = array(
        'icon' => '<i class="material-icons">call</i>'
    );

    if (check_account_permission('reports', 'cdr')) {

        $menu_array[$menu_title]['reports/Calls'] = array(
            'page_name' => array('calls'),
            'page_url' => 'reports/Calls',
            'menu_name' => 'CDR'
        );
        if (count($parent_menu_array) > 0 && !in_array('reports/Calls', $parent_menu_array)) {
            unset($menu_array[$menu_title]['reports/calls']);
        }
    }
    ///////////////////
    $menu_title = 'Settings & Services';
        $menu_array[$menu_title] = array(
        'icon' => '<i class="material-icons">settings</i>'
    );

    $menu_array[$menu_title]['blocknumbers'] = array(
        'page_name' => array('blocknumbers_number','add_blocknumbers_number'),
        'page_url' => 'blocknumbers',
        'menu_name' => 'DNC Numbers'
    );

}
?>

