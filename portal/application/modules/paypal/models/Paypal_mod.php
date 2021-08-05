<?php
class Paypal_mod extends CI_Model {

    public $total_count;
	public $select_sql;
   	
	function __construct()
	{
		parent::__construct();
		$this->load->database();	
	}

   	function get_paypal_data($account_id) {
        $final_return_array=array();
        try {
            $sql = "SELECT * FROM sys_payment_credentials WHERE account_id='$account_id' AND payment_method='paypal' LIMIT 1";
            $query = $this->db->query($sql);

            $final_return_array['result'] = $query->row_array();
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Payment List fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }
	
	
	function set_paypal_data($data) {
        try {
            if(strlen($data['business']) == 0 || strlen($data['account_id']) == 0) {
                throw new Exception('Insufficient Data');
            }
           
			$account_id = $data['account_id'];			
            $status = $data['status'];
            $credentials = json_encode(array('business' => $data['business']));
            $sql = "INSERT INTO sys_payment_credentials SET  
			account_id ='" . $account_id . "', 
			credentials= '" . $credentials . "', 
			status='$status' ,
			payment_method = 'paypal'
			ON DUPLICATE KEY UPDATE credentials=values(credentials), status=values(status)";
			
            $query = $this->db->query($sql);
			 if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            return true;
        } catch (Exception $e) {
           return $e->getMessage();
        }
    }
	

  
}
