<?php

$menu_array = array();

$menu_title = 'Rates & System Config';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">paid</i>'
);

$menu_array[$menu_title]['my_rates'] = array(
    'page_name' => array('my_rates'),
    'page_url' => 'MyRates',
    'menu_name' => 'Call Rates'
);

$menu_title = 'Rates & Package';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">price_check</i>'
);
if (check_account_permission('ratecard', 'view')) {
    $menu_array[$menu_title]['ratecard'] = array(
        'page_name' => array('ratecard_index', 'ratecard_add', 'ratecard_edit'),
        'page_url' => 'ratecard',
        'menu_name' => 'Ratecard'
    );
}
if (check_account_permission('rate', 'view')) {
    $menu_array[$menu_title]['rates'] = array(
        'page_name' => array('rates', 'rate_add', 'rate_edit', 'rate_index'),
        'page_url' => 'rates',
        'menu_name' => 'Rates'
    );
}
if (check_account_permission('tariff', 'view')) {
    $menu_array[$menu_title]['tariffs'] = array(
        'page_name' => array('tariff_index', 'tariff_add', 'tariff_edit', 'mapping_add', 'mapping_edit'),
        'page_url' => 'tariffs',
        'menu_name' => 'Tariffs'
    );
}
if (check_account_permission('bundle', 'view')) {
    /*
    $menu_array[$menu_title]['bundle'] = array(
        'page_name' => array('bundle_index', 'bundle_add', 'bundle_edit'),
        'page_url' => 'bundle',
        'menu_name' => 'Package'
    );
    */
}


$menu_array['dids'] = array(
    'page_name' => array('did_index', 'did_add', 'did_edit'),
    'page_url' => 'dids',
    'menu_name' => 'Incoming Numbers',
    'icon' => '<i class="material-icons">format_list_numbered</i>'
);

$menu_title = 'User Management';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">person_outline</i>'
);

$menu_array[$menu_title]['users'] = array(
    'page_name' => array('account_index', 'account_add_admin', 'account_edit_admin'),
    'page_url' => 'users',
    'menu_name' => 'System Users'
);

$menu_title = 'Business Report';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">summarize</i>'
);

$menu_array[$menu_title]['reports/Calls'] = array(
    'page_name' => array('cdr_index', 'calls'),
    'page_url' => 'reports/Calls',
    'menu_name' => 'CDR(s)'
);
?>