<?php

add_action('update_menu', 'reports_update_menu_func');

function reports_update_menu_func($args) {
    if (check_logged_user_group(array('RESELLER', 'SYSTEM'))) {
        $menu_title = 'Business Report';

        $args[$menu_title]['BusinessReport'] = array(
            'page_name' => array('report_dashboard', 'clientprofitdetails_list', 'resellerprofitdetails_list', 'vendorprofitdetails_list', 'servicedetails_list'),
            'page_url' => 'report',
            'menu_name' => 'Report',
            'icon' => '<i class="fa fa-moon-o"></i>'
        );

        $args[$menu_title]['PaymentHistory2'] = array(
            'page_name' => array('salesdetails'),
            'page_url' => 'report/salesdetails',
            'menu_name' => 'Sales Detail',
            'icon' => '<i class="fa fa-moon-o"></i>'
        );

        $args[$menu_title]['PaymentHistory3'] = array(
            'page_name' => array('salessummary'),
            'page_url' => 'report/salessummary',
            'menu_name' => 'Sales Summary',
            'icon' => '<i class="fa fa-moon-o"></i>'
        );
    }

    if (check_logged_user_group(array('SYSTEM'))) {
        $args[$menu_title]['vendorReport'] = array(
            'page_name' => array('carrierreport_list'),
            'page_url' => 'report/carrierreport',
            'menu_name' => 'Carrier Report',
            'icon' => '<i class="fa fa-moon-o"></i>'
        );
    }


    if (check_logged_user_group(array( 'SYSTEM'))) {

 
 






$menu_title = 'Call Detail Reports';
$menu_array[$menu_title] = array(
    'icon' => '<i class="material-icons">widgets</i>'
);

if (check_account_permission('reports', 'cdr')) {

    $menu_array[$menu_title]['causesummary'] = array(
        'page_name' => array('Cause Summary'),
        'page_url' => 'reports/Calls',
        'menu_name' => 'CDR'
    );

 $menu_array[$menu_title]['reports/Calls'] = array(
        'page_name' => array('calls'),
        'page_url' => 'reports/Calls',
        'menu_name' => 'CDR'
    );

}

}






    if (check_logged_user_group(array('RESELLER', 'SYSTEM'))) {
        $menu_title = 'Business Report';

        $args[$menu_title]['PaymentHistory'] = array(
            'page_name' => array('report_paymenthistory'),
            'page_url' => 'report/paymenthistory',
            'menu_name' => 'Payment Log',
            'icon' => '<i class="fa fa-moon-o"></i>'
        );

        $args[$menu_title]['PaymentHistory2'] = array(
            'page_name' => array('salesdetails','sales_details'),
            'page_url' => 'report/salesdetails',
            'menu_name' => 'Sales Detail',
            'icon' => '<i class="fa fa-moon-o"></i>'
        );

        $args[$menu_title]['PaymentHistory3'] = array(
            'page_name' => array('salessummary','sales_summary'),
            'page_url' => 'report/salessummary',
            'menu_name' => 'Sales Summary',
            'icon' => '<i class="fa fa-moon-o"></i>'
        );
    }

    return $args;
}

?>