<?php

/**
 * Plugin Name: Activity Log
 * Plugin URI: http://openvoips.org/
 * Version: 1.0
 * Description: Check Site Activity Log, Block IP....<br>1. Set '$config['enable_hooks'] to TRUE in application\config.php<br>2.	Put this code in application\hooks.php <br> $hook['post_controller_constructor'] = array(<br>	'class'    => 'Activityloghook',<br>	'function' => 'post_controller_constructor',<br>	'filename' => 'activitylog-hook.php',<br>	'filepath' => 'modules/activitylog',<br>	'params'   => ""<br>);
 * Author: Seema Anand  openvoips@gmail.com
 * Author URI: http://openvoips.org/
 */
?>
<?php

function plugin_activitylog_install() {
    $sql = "CREATE TABLE  IF NOT EXISTS `activity_site_log` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `event` enum('track','insert','update','delete') NOT NULL DEFAULT 'track',
	  `session_id` varchar(100) NOT NULL,
	  `user_name` varchar(128) NOT NULL,
	  `account_id` varchar(30) NOT NULL,
	  `ip_address` varchar(45) NOT NULL,
	  `remote_address` varchar(255) NOT NULL,
	  `page_url` varchar(255) NOT NULL,
	  `referrer_url` varchar(255) NOT NULL,
	  `user_agent` varchar(255) NOT NULL,
	  `ci_class_method` varchar(255) NOT NULL,
	  `created_dt` timestamp NULL DEFAULT current_timestamp(),
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
    $ci = & get_instance();
    $query = $ci->db->query($sql);
    if (!$query) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    return true;
}

function plugin_activitylog_uninstall() {
    $sql = 'DROP TABLE IF EXISTS `activity_site_log`';
    $ci = & get_instance();
    $query = $ci->db->query($sql);
    if (!$query) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    return true;
}

?>