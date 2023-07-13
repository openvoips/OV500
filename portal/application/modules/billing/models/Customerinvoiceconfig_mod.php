<?php

// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2021 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 2.0.0
// License https://www.gnu.org/licenses/agpl-3.0.html
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################

class customerinvoiceconfig_mod extends CI_Model {

    public $account_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    
    function inConfig_data($account_id) {
        $table_name = 'bill_invoice_config';
        $sql = "SELECT * FROM " . $table_name . " WHERE  account_id ='" . $account_id . "'";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        return $row;
    }

    function inConfig_update($data) {
        $table_name = 'bill_invoice_config';
        $result = $this->db->replace($table_name, $data);
        if (!$result) {
            $error_array = $this->db->error();
            return array('status' => false, 'msg' => $error_array['message']);
        } else {
            return array('status' => true, 'msg' => 'Updated Successfully');
        }
    }
    
    
    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        try {

            $sql = "SELECT SQL_CALC_FOUND_ROWS 
		bill_customer_priceplan.*,account.currency_id,account.account_type,
		if( account_type = 'CUSTOMER',( select company_name from customers where customers.account_id =  account.account_id ),
 (select company_name from resellers where resellers.account_id =  account.account_id ) )  as company_name 
FROM bill_customer_priceplan inner join account on bill_customer_priceplan.account_id=account.account_id where  1 ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND bill_customer_priceplan.account_id ='" . $value . "' ";
                        } elseif ($key == 'id') {
                            $sql .= " AND bill_customer_priceplan.id ='" . $value . "' ";
                        }elseif ($key == 'currency_id') {
                            $sql .= " AND bill_customer_priceplan.currency_id ='" . $value . "' ";
                        } else {
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
            }
            if ( check_logged_user_type(array('ADMIN','SUBADMIN')) ) {
                $sql .= " AND (parent_account_id  is null or  parent_account_id = '')";
            } else {
                $parent_account_id = get_logged_account_id();
                $sql .= " AND parent_account_id = '" . $parent_account_id . "' ";
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY id desc ";
            }

            $limit_from = intval($limit_from);

            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
          //  echo $sql;die;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;
            $final_return_array['result'] = $query->result_array();
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'CLI fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function find_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {        
        $final_return_array = array();
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS* FROM(SELECT account.parent_account_id, account.account_id,account.currency_id,sys_currencies.name,
sys_currencies.symbol,customer_voipminuts.tariff_id,account.account_type, 
if( account.account_type = 'CUSTOMER',( select company_name from customers where customers.account_id = account.account_id ),
 (select company_name from resellers where resellers.account_id = account.account_id ) ) as company_name 
from account INNER JOIN sys_currencies on sys_currencies.currency_id=account.currency_id 
INNER JOIN customer_voipminuts on customer_voipminuts.account_id=account.account_id )aa where 1
 AND tariff_id !='' ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'account_id') {
                            $sql .= " AND account_id ='" . $value . "' ";
                            $account_id = $value;
                        } elseif ($key == 'name')
                            $sql .= " AND ($key LIKE '%" . $value . "%' OR company_name LIKE '%" . $value . "%' )";
                    }
                }
            }
             if ( check_logged_user_type(array('ADMIN','SUBADMIN')) ) {
                $sql .= " AND ( parent_account_id  is null or  parent_account_id = '')";
            } else {
                $parent_account_id = get_logged_account_id();
                $sql .= " AND parent_account_id = '" . $parent_account_id . "' ";
            }
            $sql .= " AND account_id NOT IN(SELECT account_id FROM bill_customer_priceplan)";

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY company_name ";
            }

            $limit_from = intval($limit_from);

            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
       
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;

            foreach ($query->result_array() as $row) {
                $account_id = $row['account_id'];
                $final_return_array['result'][$account_id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Customer fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function add($data) {
        try {
            $this->db->trans_begin();

			$insert_data['account_id'] = $data['account_id'];
			$insert_data['billing_cycle'] = $data['billing_cycle'];
			$insert_data['priceplan_id'] = $data['priceplan_id'];
			$insert_data['payment_terms'] = $data['payment_terms'];
			$insert_data['itemised_billing'] = $data['itemised_billing'];
			$insert_data['invoice_via_email'] = $data['invoice_via_email'];
			$insert_data['created_by'] = $data['created_by'];
			
			if ($insert_data['billing_cycle'] == 'DAILY') {
					$next_invoice_date = date('Y-m-d', strtotime('+1 day'));
				} elseif ($insert_data['billing_cycle'] == 'WEEKLY') {
					$next_invoice_date = date('Y-m-d', strtotime('+7 day'));
				} else{
					$next_invoice_date = date('Y-m-d', strtotime('+1 month'));
				}
			$insert_data['next_invoice_date'] = $next_invoice_date;	


            if (count($insert_data) > 0) {
                $str = $this->db->insert_string('bill_customer_priceplan', $insert_data);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $this->id = $this->db->insert_id();
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function update($data) {
        try {
            $this->db->trans_begin();

            if (!isset($data['id']) || $data['id'] == '')
                throw new Exception('Id Missing');

            if (isset($data['billing_cycle']))
                $insert_data['billing_cycle'] = $data['billing_cycle'];
            if (isset($data['payment_terms']))
                $insert_data['payment_terms'] = $data['payment_terms'];
            if (isset($data['itemised_billing']))
                $insert_data['itemised_billing'] = $data['itemised_billing'];
               if (isset($data['priceplan_id']))
             $insert_data['priceplan_id'] = $data['priceplan_id'];
            if (isset($data['invoice_via_email']))
                $insert_data['invoice_via_email'] = $data['invoice_via_email'];

            if (isset($data['created_by']))
                $insert_data['created_by'] = $data['created_by'];
            if (count($insert_data) > 0) {
                $where = "id='" . $data['id'] . "'";
                $str = $this->db->update_string('bill_customer_priceplan', $insert_data, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'bill_customer_priceplan', 'sql_key' => $where, 'sql_query' => $str);
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
                set_activity_log($log_data_array);
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function delete($id_array) {
        try {
            $this->db->trans_begin();
            foreach ($id_array['delete_id'] as $id) {
                $log_data_array = array();

                $result = $this->db->delete('bill_customer_priceplan', array('id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => 'bill_customer_priceplan', 'sql_key' => $id, 'sql_query' => $data_dump);
            }

            $log_data_array[] = array('activity_type' => 'delete_recovery', 'sql_table' => 'bill_customer_priceplan', 'sql_key' => $id, 'sql_query' => '');
            set_activity_log($log_data_array);


            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
                return true;
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

    function get_data_total_count() {
        return $this->total_count;
    }

}
