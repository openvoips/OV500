<?php
/* 
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026
 * http://www.openvoips.com 
 */
class Login_mod extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_user($admin_login, $admin_password) {
        $sql = "select u.user_id, u.account_id, u.user_type, u.name, u.status_id 
		FROM users u
		where username='" . $admin_login . "' AND secret = BINARY '" . $admin_password . "'";
        $query = $this->db->query($sql);
        $num_rows = $query->num_rows();

        if ($num_rows === 1) {
            $user_types_group = get_user_types();
            $row = $query->row();

            $user_type = $row->user_type;
            //$user_id_name = $row->user_id_name;
            $account_id = $row->account_id;
            if (isset($user_types_group[1][$user_type])) {

                $permissions = $this->get_user_acl($user_type);
                //  echo $permissions;die;
                $data_array = array(
                    'user_status' => $row->status_id,
                    'user_id' => $row->user_id,
                    'name' => $row->name,
                    'user_type' => $row->user_type,
                    'account_status' => '',
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
                //echo $sql;
                $row_sub = $query->row();
                if (isset($row_sub)) {
                    $permissions = $this->get_user_acl($row_sub->account_id, $user_type);

                    $data_array = array(
                        'user_status' => $row->status_id,
                        'user_id' => $row->user_id,
                        'name' => $row->name,
                        'user_type' => $row->user_type,
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
                    
                    if($row->user_type=='EXTENSION')
                    {
                        $sql = "SELECT  extension_id,name
                        FROM  customer_devices 
                        WHERE user_type = 'PBX' AND  account_id='" . $account_id . "' AND user_id='".$row->user_id."'	";
                        $query = $this->db->query($sql);
                        $row_ext = $query->row();

                        $data_array = array(
                            'user_status' => $row->status_id,
                            'user_id' => $row->user_id,
                            'name' => $row->name,
                            'user_type' => $row->user_type,
                            'account_status' => $row_sub->status_id,
                            'account_id' => $row_sub->account_id,
                            'account_name' => $row_sub->company_name,
                            'account_type' => $row_sub->account_type,
                            'currency_id' => $row_sub->currency_id,
                            'account_level' => $row_sub->account_level,
                            'permissions' => $permissions,

                            'extension_id' => $row_ext->extension_id,
                        );
                    }
                    else
                    {
                        $data_array = array(
                            'user_status' => $row->status_id,
                            'user_id' => $row->user_id,
                            'name' => $row->name,
                            'user_type' => $row->user_type,
                            'account_status' => $row_sub->status_id,
                            'account_id' => $row_sub->account_id,
                            'account_name' => $row_sub->company_name,
                            'account_type' => $row_sub->account_type,
                            'currency_id' => $row_sub->currency_id,
                            'account_level' => $row_sub->account_level,
                            'permissions' => $permissions,
                        );
                    }

                    
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
        $sql = "SELECT id,user_type,permissions FROM  user_type_permissions  WHERE user_type='" . $user_type . "' LIMIT 0,1";
        $query = $this->db->query($sql);
        if ($query->num_rows() == 1) {
            $row = $query->row();
            $permissions_str = $row->permissions;
        }
        return $permissions_str;
    }

}
