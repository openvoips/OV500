<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0
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

class Billing_mod extends CI_Model {

    public $customer_id;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /* Add /update customer */

    function update_customer($data) {
        $log_data_array = array(); //reset array		
        $billingDB = $this->load->database('billingdb', true);

        try {
            ///////////
            $action_type = '';
            if (!isset($data['customer_id'])) {
                throw new Exception('Account ID missing');
            }
            $customer_id = $data['customer_id'];

            $sql = "SELECT id FROM " . $billingDB->dbprefix('customer') . " WHERE customer_id ='" . $customer_id . "' ";
            $query = $billingDB->query($sql);
            $row = $query->row();
            if (isset($row)) {
                $action_type = 'update';
            } else {
                $action_type = 'add';
            }
            //	echo $sql;die;
            ///////////



            $customer_data_array = array();


            if (isset($data['name']))
                $customer_data_array['name'] = $data['name'];
            if (isset($data['emailaddress']))
                $customer_data_array['emailaddress'] = $data['emailaddress'];


            ////////////////
            $billingDB->trans_begin();

            if ($action_type == 'update') {
                if (count($customer_data_array) > 0) {
                    $where = "customer_id='" . $customer_id . "'";
                    $str = $billingDB->update_string($billingDB->dbprefix('customer'), $customer_data_array, $where);
                    $result = $billingDB->query($str);
                    if (!$result) {
                        $error_array = $billingDB->error();
                        throw new Exception($error_array['message']);
                    }
                    $log_data_array[] = array('activity_type' => 'update', 'sql_table' => 'billing.' . $billingDB->dbprefix('customer'), 'sql_key' => $where, 'sql_query' => $str);
                }
            } else {

                if (count($customer_data_array) > 0) {
                    $customer_data_array['customer_id'] = $data['customer_id'];
                    $str = $billingDB->insert_string($billingDB->dbprefix('customer'), $customer_data_array);
                    $result = $billingDB->query($str);
                    if (!$result) {
                        $error_array = $billingDB->error();
                        throw new Exception($error_array['message']);
                    }
                    $this->customer_id = $billingDB->insert_id();

                    $log_data_array[] = array('activity_type' => 'add', 'sql_table' => 'billing.' . $billingDB->dbprefix('customer'), 'sql_key' => $customer_id, 'sql_query' => $str);
                }
            }



            if ($billingDB->trans_status() === FALSE) {
                $error_array = $billingDB->error();
                $billingDB->trans_rollback();
                return $error_array['message'];
            } else {
                $billingDB->trans_commit();
                set_activity_log($log_data_array);
                return true;
            }
        } catch (Exception $e) {
            $billingDB->trans_rollback();
            return $e->getMessage();
        }

        return true;
    }

}
