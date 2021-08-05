<?php

add_action('update_menu', 'activitylog_update_menu_func');

function activitylog_update_menu_func($args) {
    if (check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {
        $menu_title = 'System & Services';

        $args[$menu_title]['activitylog'] = array(
            'page_name' => array('activitylog', 'activitylog_index', 'activitylog_details'),
            'page_url' => 'activitylog',
            'menu_name' => 'Activity Log',
            'icon' => '<i class="fa fa-moon-o"></i>'
        );
    }
    return $args;
}

add_action('cron', 'activitylog_cron_func');

function activitylog_cron_func($args) {
    $args = array(
        'param1' => 'activity log',
        'param2' => 'action.php',
        'param3' => 'activitylog_cron_func()'
    );
    return $args;
}

?>