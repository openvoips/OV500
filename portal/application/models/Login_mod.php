<?php

// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
// OV500 Version 2.0.0
// Copyright (C) 2019-2021 Openvoips Technologies   
// http://www.openvoips.com  http://www.openvoips.org
// 
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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
        $sql = "select u.user_id, u.account_id, u.user_type, u.name, u.status_id, u.gcode  
		FROM users u
		where username='" . $admin_login . "' AND secret = '" . $admin_password . "' limit 1;";
        $query = $this->db->query($sql);
        $num_rows = $query->num_rows();
        if ($num_rows == 1) {
            $user_types_group = get_user_types();
            $row = $query->row();

            $user_type = $row->user_type;
            $user_id_name = $row->user_id_name;
            $account_id = $row->account_id;
            if (isset($user_types_group[1][$user_type])) {
                $permissions = $this->get_user_acl($user_type);               
                $data_array = array(
                    'user_status' => $row->status_id,
                    'user_id' => $row->user_id,
                    'name' => $row->name,
                    'user_type' => $row->user_type,
                    'gcode' => $row->gcode,
                    'account_status' => $row->status_id,
                    'account_id' => ADMIN_ACCOUNT_ID,
                    'account_name' => '',
                    'account_type' => ADMIN_ACCOUNT_ID,
                    'currency_id' => '',
                    'account_level' => 0,
                    'permissions' => $permissions,
                );			 
                return $data_array;
            } elseif (isset($user_types_group[2][$user_type])) {
                $sql = "SELECT 
				a.status_id, a.account_id, a.account_type, a.currency_id, a.account_level,
				c.company_name
				FROM account a INNER JOIN resellers c on c.account_id = a.account_id 
				WHERE a.account_id ='" . $account_id . "'";
                $query = $this->db->query($sql);
                $row_sub = $query->row();
                if (isset($row_sub)) {
                    $permissions = $this->get_user_acl($row_sub->account_id, $user_type);

                    $data_array = array(
                        'user_status' => $row->status_id,
                        'user_id' => $row->user_id,
                        'name' => $row->name,
                        'user_type' => $row->user_type,
                        'gcode' => $row->gcode,
                        'account_status' => $row_sub->status_id,
                        'account_id' => $row_sub->account_id,
                        'account_name' => $row_sub->company_name,
                        'account_type' => $row_sub->account_type,
                        'currency_id' => $row_sub->currency_id,
                        'account_level' => $row_sub->account_level,
                        'permissions' => $permissions,
                    );
                    return $data_array;
                }
            } elseif (isset($user_types_group[3][$user_type])) {
                $sql = "SELECT 
				a.status_id, a.account_id, a.account_type, a.currency_id, a.account_level,
				c.company_name
				FROM account a INNER JOIN customers c on c.account_id = a.account_id 
				WHERE a.account_id ='" . $account_id . "'";
                $query = $this->db->query($sql);
                //echo $sql;
                $row_sub = $query->row();
                if (isset($row_sub)) {
                    $permissions = $this->get_user_acl($row_sub->account_id, $user_type);

                    $data_array = array(
                        'user_status' => $row->status_id,
                        'user_id' => $row->user_id,
                        'name' => $row->name,
                        'user_type' => $row->user_type,
                        'gcode' => $row->gcode,
                        'account_status' => $row_sub->status_id,
                        'account_id' => $row_sub->account_id,
                        'account_name' => $row_sub->company_name,
                        'account_type' => $row_sub->account_type,
                        'currency_id' => $row_sub->currency_id,
                        'account_level' => $row_sub->account_level,
                        'permissions' => $permissions,
                    );
                    return $data_array;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        return false;
    }

    function get_user_acl($user_type) {
        $permissions_str = '';
        $sql = "SELECT id,user_type,permissions FROM " . $this->db->dbprefix('user_type_permissions') . " WHERE user_type='" . $user_type . "' LIMIT 0,1";
			 
        $query = $this->db->query($sql);
        if ($query->num_rows() == 1) {
            $row = $query->row();
            $permissions_str = $row->permissions;
        }
        return $permissions_str;
    }

    function set_gcode($user_id, $gcode) {
        $sql = "UPDATE users
		SET gcode='$gcode'		
		WHERE user_id = '" . $user_id . "'";
        $query = $this->db->query($sql);
        return true;
    }

}
