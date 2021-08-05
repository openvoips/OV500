<?php

$CI = & get_instance();
$account_id = get_logged_account_id();


$account_id_check = $account_id;
 


$menu_title = 'Rates & System Config';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-registered"></i>'
);


$menu_array[$menu_title]['MyRates'] = array(
    'page_name' => array('my_rates'),
    'page_url' => 'MyRates',
    'menu_name' => 'My Call Rates'
);
 
$menu_array[$menu_title]['endpoints'] = array(
    'page_name' => array('extension_index', 'extension_edit', 'extension_delete', 'extension_add'),
    'page_url' => 'endpoints/index/' . param_encrypt(get_logged_account_id()),
    'menu_name' => 'My Devices & Lines'
);
 

$menu_array[$menu_title]['dids'] = array(
    'page_name' => array('did_index', 'did_edit'),
    'page_url' => 'dids',
    'menu_name' => 'My Phone Numbers'
);
 
/////////////////
// Reports
$menu_title = 'Calls Report';
$menu_array[$menu_title] = array(
    'icon' => '<i class="fa fa-bar-chart"></i>'
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
        'menu_name' => 'No Answer Calls'
    );
    
}

if (check_account_permission('reports', 'report_statement')) {
    $menu_array[$menu_title]['endpoints/statement'] = array(
        'page_name' => array('report_statement'),
        'page_url' => 'endpoints/statement',
        'menu_name' => 'Account Statement'
    );
     
}


$menu_array['supportticket'] = array(
    'icon' => '<i class="fa fa-ticket"></i>',
    'page_name' => array('ticket_index'),
    'page_url' => 'ticket',
    'menu_name' => 'Support HelpDesk'
);
 
?>
