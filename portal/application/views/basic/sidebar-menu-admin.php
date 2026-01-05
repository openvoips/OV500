<?php

$menu_title = 'Live System Reports';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">call</i>'
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



 $menu_array[$menu_title]['causesummary'] = array(
        'page_name' => array('causesummary'),
        'page_url' => 'report/causesummary',
        'menu_name' => 'Cause Summary'
    );





$menu_title = 'Rates & Package';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">price_check</i>'
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
    /*
    $menu_array[$menu_title]['bundle'] = array(
        'page_name' => array('bundle', 'bundle_index', 'bundle_add', 'bundle_edit'),
        'page_url' => 'bundle',
        'menu_name' => 'Package'
    );*/
}

//////////////////////

$menu_title = 'Routing Management';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">directions</i>'
);

if (check_account_permission('carrier', 'view')) {

    $menu_array[$menu_title]['carriers'] = array(
        'page_name' => array('diversion_edit', 'diversion_bulkadd', 'diversion_add', 'diversion', 'carrier_index', 'carrier_edit', 'carrier_add', 'carrier_editG', 'carrier_addG', 'carrier_editSRCNo', 'carrier_editDSTNo', 'carrier_editINSRCNo', 'carrier_editINDSTNo'),
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
        'menu_name' => 'Incoming Numbers (DID)'
    );
}




/////////////////////////////////////
// User management  Admin / Reseller / Customer based on access list

$menu_title = 'User Management';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">person_outline</i>'
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
    'icon' => '<i class="material-icons">widgets</i>'
);

if (check_account_permission('reports', 'cdr')) {

    $menu_array[$menu_title]['reports/Calls'] = array(
        'page_name' => array('calls'),
        'page_url' => 'reports/Calls',
        'menu_name' => 'CDR'
    );
}



///////////////////////
// Invoice And Customer Random CLI Features
$menu_title = 'Business Report';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">summarize</i>'
);

//////////////////////
// Admin/ Subadmin  system management

$menu_title = 'System & Services';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">settings</i>'
);

$menu_array[$menu_title]['currency'] = array(
    'page_name' => array('Currency', 'currency_add', 'Currencyexc'),
    'page_url' => 'currency/exc',
    'menu_name' => 'Currency & Exchange Rate'
);
	
	$menu_array[$menu_title]['blockcli'] = array(
        'page_name' => array('blockcli_number','add_blockcli_number'),
        'page_url' => 'blockcli',
        'menu_name' => 'Blocked CLI'
    );
    $menu_array[$menu_title]['blocknumbers'] = array(
        'page_name' => array('blocknumbers_number','add_blocknumbers_number'),
        'page_url' => 'blocknumbers',
        'menu_name' => 'DNC Numbers'
    );
?>