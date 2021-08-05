<?php
add_action('update_menu', 'paypal_update_menu_func');

function paypal_update_menu_func($args) {
    if (check_logged_user_group(array('reseller', 'customer'))) {	
	
		$session_current_user_id = get_logged_user_id();
		$logged_account_id = get_logged_account_id();
		
		if(!isset($_SESSION['customer'][$session_current_user_id]['is_paypal']))
		{
			$CI = & get_instance();
			$sql = "SELECT COUNT(account_id) total_count FROM sys_payment_credentials WHERE 
			account_id=(SELECT IF(parent_account_id='', 'SYSTEM',parent_account_id) parent_account_id  FROM account WHERE account_id='$logged_account_id') 
			AND payment_method='paypal'
			AND status='Y'
			LIMIT 1";
            $query = $CI->db->query($sql);
			$row = $query->row_array();	
					
			if($row['total_count']==0)
				$_SESSION['customer'][$session_current_user_id]['is_paypal']='N';
			else
				$_SESSION['customer'][$session_current_user_id]['is_paypal']='Y';
				
		}
		
		if($_SESSION['customer'][$session_current_user_id]['is_paypal']=='Y')
		{
			$menu_title = 'Make Payment';
            $args[$menu_title] = array(
                'icon' => '<i class="fa fa-money"></i>'
            );
			 
			$args[$menu_title]['paypal'] = array(
				'page_name' => array('payment_trace','trace_details'),
				'page_url' => 'paypal',
				'menu_name' => 'Paypal Gateway',
				'icon' => '<i class="fa fa-folder-o"></i>'
			);
		}
    }
 if (check_logged_user_group(array(ADMIN_ACCOUNT_ID,'reseller'))) {
    $menu_title = 'System & Services';
    $args[$menu_title]['paypal'] = array(
        'page_name' => array('paypal_config'),
        'page_url' => 'paypal/config',
        'menu_name' => 'Paypal Config',
        'icon' => '<i class="fa fa-folder-o"></i>'
    );
  }
    return $args;
}

include_once('helpers/paypal_helper.php');
?>