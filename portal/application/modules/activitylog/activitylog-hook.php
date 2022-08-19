<?php

class Activityloghook {

    private $ignore_classes = array('activitylog');

    function __construct() {
        
    }

    function post_controller_constructor() {
        $CI = & get_instance();
        $DB1 = $CI->load->database('default', true);

        $db_name = $DB1->database;
        $table_name = $DB1->dbprefix('activity_site_log');


        $sql_check = "SELECT * FROM information_schema.TABLES 
		WHERE  TABLE_NAME = '$table_name'";
        $query = $DB1->query($sql_check);
        $num_rows = $query->num_rows();

        if ($num_rows > 0) {

            $log_data_insert_array = array();
            $ip = trim($_SERVER["REMOTE_ADDR"]);
            $ip2 = getUserIP();
            if ($ip != $ip2)
                $ip .= '/' . $ip2;
            $log_data_insert_array['ip_address'] = $ip;
            $log_data_insert_array['remote_address'] = trim($_SERVER["REMOTE_ADDR"]);
            $log_data_insert_array['page_url'] = trim($_SERVER["REQUEST_URI"]);
            $log_data_insert_array['referrer_url'] = trim($_SERVER["HTTP_REFERER"]);
            $log_data_insert_array['user_agent'] = trim($_SERVER["HTTP_USER_AGENT"]);
            if (is_cli())
                $log_data_insert_array['user_agent'] .= ' CLI';

            $log_data_insert_array['session_id'] = session_id();
            $log_data_insert_array['user_name'] = get_account_full_name();
            $log_data_insert_array['account_id'] = get_logged_account_id();


            $log_data_insert_array['ci_class_method'] = $CI->router->fetch_class() . '/' . $CI->router->fetch_method();
            $class_name = $CI->router->fetch_class();

			
			$request_uri = trim($_SERVER["REQUEST_URI"]);
			$ignore_words = array('/theme/','billing/api');
			foreach($ignore_words as $word)
			{
				if(stripos($request_uri,$word) !== false) 
					return;
			}
		
		
            if (in_array($class_name, $this->ignore_classes)) {
                
            } else {
                $str = $DB1->insert('activity_site_log', $log_data_insert_array);
            }
        }
    }

    function postCall() {
        /* $CI = & get_instance();
          $DB1 = $CI->load->database('default', true);


          $log_data_insert_array = array();
          $log_data_insert_array['ip_address'] = trim($_SERVER["REMOTE_ADDR"]);
          //$log_data_insert_array['response_data'] = $result_raw;
          //$log_data_insert_array['function_return'] = $result;
          //$str = $CI->db->insert('activity_api_log', $log_data_insert_array);
          $str = $DB1->insert('user_audit_trails', $log_data_insert_array); */
    }

}

?>