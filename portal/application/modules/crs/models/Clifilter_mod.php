<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */

class Clifilter_mod extends CI_Model {

    public $account_id;
    public $select_sql;
    public $total_count_sql;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array('result' => array());
        $account_access_id_name_array = $tariff_id_name_array = array();
        $tariff_id_account_access_id_mapping_array = $currency_id_account_access_id_mapping_array = array();
        $service_id_name_array = array();
        try {
            $sql = "SELECT 
            a.id,  a.account_id,a.parent_account_id,a.status_id,a.account_type,a.account_level,a.recording,a.dp,
			c.contact_name, c.company_name
			FROM account a INNER JOIN customers c ON a.account_id = c.account_id 
			WHERE a.account_type='CUSTOMER' ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'parent_account_id')
                        $sql .= " AND a.$key ='" . $value . "' ";
                    elseif ($value != '') {
                        if (in_array($key, array('id', 'account_id', 'status_id', 'account_type', 'account_level', 'currency_id'))) {
                            $sql .= " AND a.$key ='" . $value . "' ";
                        } elseif ($key == 'ipaddress' and strlen($value) > 0) {
                            $sql .= " AND c.account_id IN( SELECT account_id FROM customer_ips WHERE $key LIKE '%" . $value . "%' )";
                        } elseif ($key == 'sip_username' and strlen($value) > 0) {
                            $sql .= " AND c.account_id IN( SELECT account_id FROM customer_devices WHERE username LIKE '%" . $value . "%' )";
                        } else {
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY a.create_dt DESC";
            }
            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";

            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = $sql;

            $final_return_array['result'] = Array();
            $tariff_id_array = Array();
            foreach ($query->result_array() as $row) {
                $account_id = $row['account_id'];
                //$tariff_id = $row['tariff_id'];
                $currency_id = $row['currency_id'];

               
                if (isset ($option_param['clifilterdid']) && $option_param['clifilterdid'] == true) {//set default value
                    $row['clifilterdid'] = array();
                }
                if (isset ($option_param['clifilterpstn']) && $option_param['clifilterpstn'] == true) {//set default value
                    $row['clifilterpstn'] = array();
                }
               

                $final_return_array['result'][$account_id] = $row;
                $account_id_array[] = $account_id;
                //$tariff_id_array[] = $tariff_id;
                // $tariff_id_account_id_mapping_array[$tariff_id][] = $account_id;
                $currency_id_account_id_mapping_array[$currency_id][] = $account_id;
            }
            //$tariff_id_array = array_unique($tariff_id_array);


            

           

            if (isset($option_param['clifilterdid']) && $option_param['clifilterdid'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM didclifilter WHERE account_id IN($account_id_str)";
                if (isset($option_param['clifilterdid_id'])) {
                    $sql .= " AND id ='" . $option_param['clifilterdid_id'] . "'";
                }
                //echo $sql;
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];
                    $final_return_array['result'][$account_id]['clifilterdid'][$id] = $row;
                }


            }

          
            
            if (isset($option_param['clifilterpstn']) && $option_param['clifilterpstn'] == true && count($final_return_array['result']) > 0) {
                $account_id_str = implode("','", $account_id_array);
                $account_id_str = "'" . $account_id_str . "'";
                $sql = "SELECT * FROM pstnclifilter WHERE account_id IN($account_id_str)";
                if (isset($option_param['clifilterpstn_id'])) {
                    $sql .= " AND id ='" . $option_param['clifilterpstn_id'] . "'";
                }
                //echo $sql;
                $query = $this->db->query($sql);
                if (!$query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $account_id = $row['account_id'];
                    $id = $row['id'];
                    $final_return_array['result'][$account_id]['clifilterpstn'][$id] = $row;
                }


            }

           



            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'End users fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_cli_data($type='', $order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array('result' => array());
        $account_access_id_name_array = $tariff_id_name_array = array();
        $tariff_id_account_access_id_mapping_array = $currency_id_account_access_id_mapping_array = array();
        $service_id_name_array = array();

        if($type=='did')
                $table = 'didclifilter';
            elseif($type=='pstn')
                $table = 'pstnclifilter';
            else 
                return 'Data missing';

        try {
            $sql = "SELECT *
			FROM $table
			WHERE 1 ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($key == 'parent_account_id')
                        $sql .= " AND a.$key ='" . $value . "' ";
                    elseif ($value != '') {
                        if (in_array($key, array('id', 'account_id', 'status_id'))) {
                            $sql .= " AND $key ='" . $value . "' ";
                        } else {
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                        }
                    }
                }
            }

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY callerid";
            }
            $limit_from = intval($limit_from);
            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";
            //echo $sql;
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $this->select_sql = $sql;

            $final_return_array['result'] = Array();
       
            $final_return_array['result']=$query->result_array();
            /*
            foreach ($query->result_array() as $row) {
                //$account_id = $row['account_id']; 
                $final_return_array['result'][] = $row;
      
            }
          */



            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Caller ID fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }


    ///////////////////////////////////
    function delete_cli($account_id, $id_array, $type) {
        try {
            $log_data_array = array();
            if($type=='did')
                $table = 'didclifilter';
            elseif($type=='pstn')
                $table = 'pstnclifilter';
            else 
                return 'Data missing';

          
            foreach ($id_array['delete_id'] as $id) {
                $result = $this->db->delete($table, array('id' => $id));
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'delete', 'sql_table' => $table, 'sql_key' => $id, 'sql_query' => $this->db->last_query());
                if ($this->db->affected_rows() == 0)
                    throw new Exception('Data not found');
            }

                //set_activity_log($log_data_array);
                return true;
            
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    function update_didfiltercli($data) {
        try {
            $log_data_array = array();

            if (isset($data['account_id']))
                $account_id = $data['account_id'];
            else
                return 'Account missing';
            if (isset($data['clifilterdid_id']))
                $id = $data['clifilterdid_id'];
            else
                return 'CLI ID missing';

            if (isset($data['type'])) {
                $type = $data['type'];
            } else {
                return 'Data missing';
            }

            if($type=='did')
                $table = 'didclifilter';
            elseif($type=='pstn')
                $table = 'pstnclifilter';
            else 
                return 'Data missing';

            $randomcli_data_array = array();           
            $randomcli_data_array['cli_status'] = $data['cli_status'];
            $randomcli_data_array['callerid'] = $data['callerid'];


                //check
            $sql = "SELECT count(id) total_count FROM  $table WHERE account_id='" . $randomcli_data_array['account_id'] . "' AND callerid='" . $randomcli_data_array['callerid'] . "' AND id !='" . $id . "'";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            $row = $query->row_array();
            if($row['total_count'] > 0 )
                return 'Caller ID already exists';

          
            if (count($randomcli_data_array) > 0) {
                $randomcli_data_array['updated_by'] = get_logged_account_id();
                $randomcli_data_array['updated_dt'] = date('Y-m-d h:i:s');

                $where = " id='" . $id . "' AND account_id='" . $account_id . "' ";
               // print_r($ip_data_array);die;
                $str = $this->db->update_string($table, $randomcli_data_array, $where);
                $result = $this->db->query($str);

                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $log_data_array[] = array('activity_type' => 'update', 'sql_table' => $table, 'sql_key' => $where, 'sql_query' => $str);
            }

            
               // set_activity_log($log_data_array);
            
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function add_didfiltercli($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'Account missing';
            }
            if (isset($data['type'])) {
                $type = $data['type'];
            } else {
                return 'Data Type missing';
            }
            
            if($type=='did')
                $table = 'didclifilter';
            elseif($type=='pstn')
                $table = 'pstnclifilter';
            else 
                return 'Invalid type'.$type;
            
            $randomcli_data_array = array();
            $randomcli_data_array['account_id'] = $data['account_id'];          
            $randomcli_data_array['cli_status'] = $data['cli_status'];  
            $randomcli_data_array['callerid'] = $data['callerid'];
            $randomcli_data_array['created_by'] = get_logged_account_id();
            $randomcli_data_array['created_dt'] = date('Y-m-d h:i:s');

            //check
            $sql = "SELECT count(id) total_count FROM  $table WHERE account_id='" . $randomcli_data_array['account_id'] . "' AND callerid='" . $randomcli_data_array['callerid'] . "'";
            $existing_callerid_array = array();
            $query = $this->db->query($sql);
            $row = $query->row_array();
            if($row['total_count'] > 0 )
                return 'Caller ID already exists';
            
            $str = $this->db->insert_string($table, $randomcli_data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $insert_id = $this->db->insert_id();
            $log_data_array[] = array('activity_type' => 'insert', 'sql_table' => $table, 'sql_key' => '', 'sql_query' => $str);
            
            $this->clifilterdid_id= $insert_id;
               
                //set_activity_log($log_data_array);
            

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    function add_didfiltercli_bulk($data, $csv_data)
    {
        try 
        {
            if (isset($data['type'])) {
                $type = $data['type'];
            } else {
                return 'Data Type missing';
            }
            
            if($type=='did')
                $table = 'didclifilter';
            elseif($type=='pstn')
                $table = 'pstnclifilter';
            else 
                return 'Invalid type'.$type;


            $account_id = $data['account_id'];
            $rule_name = $data['clirule_name'];
            $cli_status = 1;
            $timestamp = time();
            $rule_id = $timestamp;
            $created_by = get_logged_account_id();
            $created_dt = date('Y-m-d h:i:s');

         
            if(isset($data['delete_existing']) && $data['delete_existing']=='1')
            {
                $sql ="DELETE FROM $table WHERE account_id='$account_id'";
                $result = $this->db->query($sql);
            }
            


            $error_msg='';
            $sql_insert = "INSERT INTO $table (account_id, callerid, cli_status,  
            created_by,created_dt) VALUES ";
            $sql_values = '';

            for ($i = 1; $i < count($csv_data); $i++) {                

                $sql_values .= "('" . $account_id . "', " . $csv_data[$i][0] . ", " .$cli_status . ", 
                '" . $created_by . "', '" .$created_dt . "'),";

                if (($i % 400) == 399 || $i == count($csv_data) - 1) {
                    $sql = $sql_insert . rtrim($sql_values, ',');
                    $result = $this->db->query($sql);
                    $sql_values = '';
                    if ($result) {
                        
                    } else {
                        $success = false;
                        $e = $this->db->error();
                        $error_msg .= $e['message'];
                    }
                }

            }

            if ($error_msg=='') {
                return true;
            }
            else
            {
                return $error_msg;
            }

        
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function get_data_total_count($sql_exists = false) {
        try {

            if ($sql_exists && isset($this->total_count_sql) && $this->total_count_sql != '') {
                $count_sql = trim($this->total_count_sql);
            } else {

                $count_sql = generate_count_total_sql($this->select_sql);
                if (substr($count_sql, 0, 5) == 'error') {
                    throw new \Exception($count_sql);
                }
            }           
            $this->total_count_sql = $count_sql;
            $query_count = $this->db->query($count_sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;
            return $this->total_count;
        } catch (\Exception $e) {          
            return 0;
        }
        return 0; 
    }

}
