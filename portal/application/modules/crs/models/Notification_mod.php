<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */

class Notification_mod extends CI_Model
{

    public $notification_id;
    public $total_count;
    public $select_sql;
    public $total_count_sql;

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_notification_data($account_id,$search_data=[])
    {
        $sql = "select * FROM account_notification where account_id='" . $account_id . "'";
        if (count($search_data) > 0) {
            foreach ($search_data as $key => $value) {
                if ($value != '') {
                    $sql .= " AND $key  = '" . $value . "'";
                }
            }
        }
        $query = $this->db->query($sql);
        $result = $query->result_array();

        return $result;
    }

    function add_notification($data)
    {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'Account missing';
            }


if(strlen( $account_id) > 2 ){

  	$sql = "select count(*) itexist FROM account_notification where account_id='" . $account_id . "' and notify_name ='" . $data['notify_name'] . "'";
        if (count($search_data) > 0) {
            foreach ($search_data as $key => $value) {
                if ($value != '') {
                    $sql .= " AND $key  = '" . $value . "'";
                }
            }
        }
        $query = $this->db->query($sql);
        $result = $query->result_array();
 
 
	if($result[0]['itexist'] == 1){
		return $data['notify_name'].' Notification already exists.';
	}

}




            $ip_data_array = array();
            $ip_data_array['account_id'] = $data['account_id'];
            $ip_data_array['notify_name'] = $data['notify_name'];
            $ip_data_array['notify_emails'] = $data['notify_emails'];
            $ip_data_array['notify_amount'] = $data['notify_amount'];
            $ip_data_array['status'] = $data['status'];

            //$ip_data_array['email_status'] = $data['email_status'];



            $str = $this->db->insert_string('account_notification', $ip_data_array);
            $result = $this->db->query($str);

            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->notification_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => 'account_notification', 'sql_key' => '', 'sql_query' => $str);


            set_activity_log($log_data_array);

            return TRUE;
        } catch (Exception $e) {

            return $e->getMessage();
        }
    }

    function update_notification($data)
    {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'User missing';
            }
            if (isset($data['notification_id'])) {
                $id = $data['notification_id'];
            } else {
                return 'ID missing';
            }


            if (isset($data['ipaddress'])) {
                $sql = "SELECT account_id FROM customer_ips  WHERE ipaddress='" . $data['ipaddress'] . "'  AND dialprefix='" . $data['dialprefix'] . "' AND billingcode='" . $data['billingcode'] . "' AND  id !='" . $id . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if ($row == NULL) {
                } else {
                    return 'This Billing Code, IP & Dial Prefix already exists in system';
                }
            }
            $ip_data_array = array();



            if (isset($data['notify_name']))
                $ip_data_array['notify_name'] = $data['notify_name'];

            if (isset($data['notify_emails']))
                $ip_data_array['notify_emails'] = $data['notify_emails'];

            if (isset($data['notify_amount']))
                $ip_data_array['notify_amount'] = $data['notify_amount'];

            if (isset($data['status']))
                $ip_data_array['status'] = $data['status'];





            if (count($ip_data_array) > 0) {
                $where = " notification_id='" . $id . "' AND account_id='" . $account_id . "' ";
                $str = $this->db->update_string('account_notification', $ip_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'account_notification', 'sql_key' => $where, 'sql_query' => $str);
            }

            set_activity_log($log_data_array);

            return true;
        } catch (Exception $e) {

            return $e->getMessage();
        }
    }

    function delete_notification($account_id, $id_array)
    {
        try {//echo $account_id; ddd($id_array);die;
            $log_data_array = array();

            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete('account_notification', array('account_id' => $account_id, 'notification_id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'account_notification', 'sql_key' => $id, 'sql_query' => $this->db->last_query());
                if ($this->db->affected_rows() == 0)
                    throw new Exception('Data not found');
            }


            set_activity_log($log_data_array);
            return true;
        } catch (Exception $e) {

            return $e->getMessage();
        }
    }
}
