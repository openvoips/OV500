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

class Login_mod extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_user($admin_login, $admin_password) {
        $sql = "select customers.customer_id, account_id, name, account_type, emailaddress from web_access INNER JOIN customers on web_access.customer_id = customers.customer_id  where username='" . $admin_login . "' AND secret ='" . $admin_password . "'";
        $query = $this->db->query($sql);
        $num_rows = $query->num_rows();

        if ($num_rows === 1) {
            $row = $query->row();
            $account_type = $row->account_type;
            $account_id = $row->account_id;

            if (in_array($account_type, array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                $sql = "SELECT account_id,  account_status  from customers where account_id ='" . $account_id . "';";
                $query = $this->db->query($sql);
                $row_sub = $query->row();
                if (isset($row_sub)) {
                    $permissions = $this->get_account_acl($row->account_id, $account_type);
                    $data_array = array(
                        'account_status' => $row_sub->account_status,
                        'customer_id' => $row->customer_id,
                        'account_id' => $row->account_id,
                        'name' => $row->name,
                        'account_type' => $row->account_type,
                        'currency_id' => '',
                        'account_level' => 0,
                        'key' => $account_id,
                        'permissions' => $permissions,
                        'username' => $admin_login,
                    );
                    return arrayToObject($data_array);
                }
            } elseif (in_array($account_type, array('CUSTOMER', 'RESELLER'))) {
                $sql = "SELECT account.id, account.account_id, customers.account_status, currency_id, account_level, username FROM account INNER JOIN customers on customers.account_id = account.account_id INNER JOIN web_access on customers.customer_id =  web_access.customer_id WHERE account.account_id ='" . $account_id . "' ";
                $query = $this->db->query($sql);
                //echo $sql;
                $row_sub = $query->row();
                if (isset($row_sub)) {
                    $permissions = $this->get_account_acl($row->account_id, $account_type);
                    $data_array = array(
                        'account_status' => $row_sub->account_status,
                        'currency_id' => $row_sub->currency_id,
                        'customer_id' => $row->customer_id,
                        'account_id' => $row->account_id,
                        'name' => $row->name,
                        'account_type' => $row->account_type,
                        'account_level' => $row_sub->account_level,
                        'key' => $account_id,
                        'permissions' => $permissions,
                        'username' => $row_sub->username,
                    );
                    return arrayToObject($data_array);
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        return false;
    }

    function get_account_acl($account_id, $account_type) {
        $permissions_str = '';
        $sql = "SELECT id,account_id,permissions FROM customer_permissions WHERE account_id='" . $account_id . "' LIMIT 0,1";
        $query = $this->db->query($sql);
        $num_rows = $query->num_rows();
        if ($num_rows === 1) {
            $row = $query->row();
            $permissions_str = $row->permissions;
        } else {
            $sql = "SELECT id,account_type,permissions FROM customer_default_permissions WHERE account_type='" . $account_type . "' LIMIT 0,1";

            $query = $this->db->query($sql);
            if ($query->num_rows() == 1) {
                $row = $query->row();
                $permissions_str = $row->permissions;
            }
        }
        return $permissions_str;
    }

}
