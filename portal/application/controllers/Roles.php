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

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Roles extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('role_mod');
        if (!check_is_loggedin()) {
            redirect(base_url(), 'refresh');
        }
    }

    function index($arg1 = '', $format = '') {
        $page_name = "role_index";
        $data['page_name'] = $page_name;
        if (!check_logged_user_type(array('ADMIN', 'SUBADMIN', 'RESELLERADMIN')))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $roles_result = $this->role_mod->get_role_permission();
        if (isset($roles_result['result'])) {
            $roles_data = $roles_result['result'];
        } else {
            $roles_data = array();
        }
        $data['data'] = $roles_data;

        $this->load->view('basic/header', $data);
        $this->load->view('role/roles', $data);
        $this->load->view('basic/footer', $data);
    }

    public function accessConfig($id = -1) {
        if ($id == -1)
            show_404();

        $page_name = "role_accessConfig";
        $data['page_name'] = $page_name;
        if (!check_logged_user_type(array('ADMIN', 'SUBADMIN', 'RESELLERADMIN')))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $user_type = $_POST['user_type'];
            $this->form_validation->set_rules('user_type', 'Role', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->role_mod->role_permission_update($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', ucfirst($user_type) . ' Permissions Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) == 'save')
                        redirect(base_url() . 'roles/accessConfig/' . param_encrypt($user_type), 'location', '301');
                    elseif ($action == 'save_close')
                        redirect(base_url() . 'roles', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $user_types_array_temp = get_user_types();
        $user_types_array = array();
        foreach ($user_types_array_temp as $user_types_array_temp_sub) {
            $user_types_array = array_merge($user_types_array, $user_types_array_temp_sub);
        }
        if (!empty($id)) {
            $user_type = param_decrypt($id);
            if (!isset($user_types_array[$user_type]))
                show_404();
            $search_data = array('user_type' => $user_type);
            $roles_result = $this->role_mod->get_role_permission('', 1, 0, $search_data);
            if (isset($roles_result['result']))
                $roles_data = current($roles_result['result']);
            else
                $roles_data = array();
        } else {
            show_404();
        }

        $data['user_type'] = $user_type;
        $data['data'] = $roles_data;
        $logged_user_type = get_logged_user_type();
        $this->load->view('basic/header', $data);
        $this->load->view('role/accessConfig', $data);
        $this->load->view('basic/footer', $data);
    }

}
