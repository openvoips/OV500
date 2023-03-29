<?php

$menu_array = array();
$menu_title = 'My Plans & Rates';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-registered"></i>'
);
//$menu_array[$menu_title]['MyPackage'] = array(
//    'page_name' => array('rate_MyRates'),
//    'page_url' => 'MyPackage/' . param_encrypt(get_logged_account_id()),
//    'menu_name' => 'My Package'
//);
$menu_array[$menu_title]['my_rates'] = array(
    'page_name' => array('my_rates'),
    'page_url' => 'MyRates',
    'menu_name' => 'Call Rates'
);
$menu_array[$menu_title]['endpoints/index'] = array(
    'page_name' => array('extension_index', 'extension_edit', 'extension_delete', 'extension_add'),
    'page_url' => 'endpoints/index/' . param_encrypt(get_logged_account_id()) . '/' . param_encrypt('RESELLER'),
    'menu_name' => 'SRC & DST Rules'
);
/////////////
$menu_title = 'Rates & Package';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-registered"></i>'
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
    $menu_array[$menu_title]['bundle'] = array(
        'page_name' => array('bundle_index', 'bundle_add', 'bundle_edit'),
        'page_url' => 'bundle',
        'menu_name' => 'Bundle & Package'
    );
}

//////////////////////



$menu_array['dids'] = array(
    'page_name' => array('did_index', 'did_add', 'did_edit'),
    'page_url' => 'dids',
    'menu_name' => 'Incoming Numbers',
    'icon' => '<i class="fa fa-exchange"></i>'
);


$menu_title = 'User Management';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-user"></i>'
);


$menu_array[$menu_title]['users'] = array(
    'page_name' => array('account_index', 'account_add_admin', 'account_edit_admin'),
    'page_url' => 'users',
    'menu_name' => 'System Users'
);


$menu_title = 'Business Report';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-bar-chart"></i>'
); {

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
    $menu_array[$menu_title]['endpoints/statement'] = array(
        'page_name' => array('report_statement'),
        'page_url' => 'endpoints/statement',
        'menu_name' => 'Account Statement'
    );
}


$menu_array[$menu_title]['reports/ProfitLoss'] = array(
    'page_name' => array('ProfitLoss'),
    'page_url' => 'reports/ProfitLoss',
    'menu_name' => 'Tarffic Profit & Loss'
);


$menu_title = 'System & Services';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-gear"></i>'
);



?>