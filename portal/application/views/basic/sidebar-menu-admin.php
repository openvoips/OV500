<?php

$menu_title = 'Live System Reports';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa  fa-chevron-down"></i>'
);

if (check_account_permission('reports', 'monin')) {
    $menu_array[$menu_title]['monin'] = array(
        'page_name' => array('monin'),
        'page_url' => 'reports/monin',
        'menu_name' => 'Live Call Summary'
    );
}
if (check_account_permission('reports', 'livecall')) {
    $menu_array[$menu_title]['livecall'] = array(
        'page_name' => array('livecall'),
        'page_url' => 'livecall',
        'menu_name' => 'Live Call'
    );
}


if (check_account_permission('reports', 'CustQOSR')) {
    $menu_array[$menu_title]['reports/CustQOSR'] = array(
        'page_name' => array('CustQOSR'),
        'page_url' => 'reports/CustQOSR',
        'menu_name' => 'Customer QoS Summary'
    );
}

if (check_account_permission('reports', 'CarrQOSR')) {
    $menu_array[$menu_title]['reports/CarrQOSR'] = array(
        'page_name' => array('CarrQOSR'),
        'page_url' => 'reports/CarrQOSR',
        'menu_name' => 'Carrier QoS Summary'
    );
}


////////////////////	
$menu_title = 'Rates & Pachage';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-registered"></i>'
);


// For All type User if they have Rate, Tariff and Ratecard management access
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
        'page_name' => array('bundle', 'bundle_index', 'bundle_add', 'bundle_edit'),
        'page_url' => 'bundle',
        'menu_name' => 'Bundle & Pachage'
    );
}

//////////////////////

$menu_title = 'Routing Management';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-exchange"></i>'
);


if (check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {
$menu_array[$menu_title]['providers'] = array(
    'page_name' => array('provider_index', 'provider_add', 'provider_edit'),
    'page_url' => 'providers',
    'menu_name' => 'Provider'
);
}
if (check_account_permission('carrier', 'view')) {

    $menu_array[$menu_title]['carriers'] = array(
        'page_name' => array('carrier_index', 'carrier_edit', 'carrier_add', 'carrier_editG', 'carrier_addG', 'carrier_editSRCNo', 'carrier_editDSTNo', 'carrier_editINSRCNo', 'carrier_editINDSTNo'),
        'page_url' => 'carriers',
        'menu_name' => 'Carriers'
    );
}
if (check_account_permission('routing', 'view')) {
    $menu_array[$menu_title]['routes'] = array(
        'page_name' => array('route_index', 'route_add', 'route_edit'),
        'page_url' => 'routes',
        'menu_name' => 'Routes'
    );
}
if (check_account_permission('dialplan', 'view')) {

    $menu_array[$menu_title]['dialplans'] = array(
        'page_name' => array('dialplan_index', 'dialplan_add', 'dialplan_edit'),
        'page_url' => 'dialplans',
        'menu_name' => 'Dial Plans'
    );
}
//////////////////
if (check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {


    $menu_array[$menu_title]['dids'] = array(
        'page_name' => array('did_index', 'did_add', 'did_edit'),
        'page_url' => 'dids',
        'menu_name' => 'Direct Inward dialing'
    );
}




/////////////////////////////////////
// User management  Admin / Reseller / Customer based on access list

$menu_title = 'User Management';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-user"></i>'
);

if (check_account_permission('user', 'view')) {
    $menu_array[$menu_title]['users'] = array(
        'page_name' => array('users_index', 'users_add', 'users_edit'),
        'page_url' => 'users',
        'menu_name' => 'System Users'
    );
}


//////////////////////
$menu_title = 'Call Detail Reports';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-plus-square"></i>'
);

if (check_account_permission('reports', 'cdr')) {

    $menu_array[$menu_title]['reports/AnsCalls'] = array(
        'page_name' => array('cdr_index'),
        'page_url' => 'reports/AnsCalls',
        'menu_name' => 'Connected Calls'
    );
}

if (check_account_permission('reports', 'fail_calls')) {
    $menu_array[$menu_title]['reports/FailCalls'] = array(
        'page_name' => array('report_failed'),
        'page_url' => 'reports/FailCalls',
        'menu_name' => 'Failed Calls'
    );
}

///////////////////////
// Invoice And Customer Random CLI Features
$menu_title = 'Business Report';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-bar-chart"></i>'
);



$menu_array[$menu_title]['reports/ProfitLoss'] = array(
    'page_name' => array('ProfitLoss'),
    'page_url' => 'reports/ProfitLoss',
    'menu_name' => 'Tarffic Profit & Loss'
);



$menu_array[$menu_title]['reports/topup'] = array(
    'page_name' => array('report_topup'),
    'page_url' => 'reports/topup',
    'menu_name' => 'Topup Daily Summary'
);

$menu_array[$menu_title]['reports/topup_monthly'] = array(
    'page_name' => array('report_topup_monthly'),
    'page_url' => 'reports/topup_monthly',
    'menu_name' => 'Topup Monthly Summary'
);


// Admin/ Subadmin  system management

$menu_title = 'System & Services';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-gear"></i>'
);

$menu_array[$menu_title]['currency'] = array(
    'page_name' => array('Currency', 'currency_add'),
    'page_url' => 'currency',
    'menu_name' => 'Currency & Exchange Rate'
);


$menu_array[$menu_title]['ticket'] = array(
    'page_name' => array('ticket_index', 'ticket_details', 'ticket_add'),
    'page_url' => 'ticket',
    'menu_name' => 'Support Ticket'
);

?>
