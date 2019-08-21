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

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Roles extends CI_Controller {

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
        if (!check_logged_account_type(array('ADMIN', 'SUBADMIN')))
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
        if (!check_logged_account_type(array('ADMIN', 'SUBADMIN')))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_type = $_POST['account_type'];
            $this->form_validation->set_rules('account_type', 'Role', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->role_mod->role_permission_update($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', ucfirst($account_type) . ' Permissions Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'roles/accessConfig/' . param_encrypt($account_type), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'roles', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'roles', 'location', '301');
                    }

                    redirect(base_url() . 'roles/edit/' . param_encrypt($account_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $account_types_array_temp = get_account_types();
        $account_types_array = array();
        foreach ($account_types_array_temp as $account_types_array_temp_sub) {
            $account_types_array = array_merge($account_types_array, $account_types_array_temp_sub);
        }
        if (!empty($id)) {
            $account_type = param_decrypt($id);
            if (!isset($account_types_array[$account_type]))
                show_404();
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_type' => $account_type);
            $roles_result = $this->role_mod->get_role_permission($order_by, $per_page, $segment, $search_data);
            if (isset($roles_result['result']))
                $roles_data = current($roles_result['result']);
            else
                $roles_data = array();
        } else {
            show_404();
        }
        $data['account_type'] = $account_type;
        $data['data'] = $roles_data;
        $logged_account_type = get_logged_account_type();
        $this->load->view('basic/header', $data);
        $this->load->view('role/accessConfig', $data);
        $this->load->view('basic/footer', $data);
    }

    function account_permission($id = '') {
        $page_name = "role_account_permission";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSearchData') {
            if (isset($_POST['search_account_id']))
                $id = param_encrypt($_POST['search_account_id']);
        }
        elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->role_mod->account_permission_update($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', ucfirst($account_id) . ' Permissions Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'roles/account_permission/' . param_encrypt($account_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'roles/account_permission', 'location', '301');
                    } else {
                        redirect(base_url() . 'roles', 'location', '301');
                    }
                    redirect(base_url() . 'roles/account_permission/', 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($id) > 0) {
            $account_id = param_decrypt($id);
            $option_param = array('permission' => true,);
            $account_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
            if (!$account_result) {
                $data['err_msgs'] = 'User Not Found';
            } else {
                $is_permitted = true;
                if (check_logged_account_type(array('ADMIN', 'SUBADMIN', 'ACCOUNTS'))) {
                    
                } elseif (check_logged_account_type(array('RESELLER'))) {
                    if ($account_result['parent_account_id'] != get_logged_account_id()) {
                        $data['err_msgs'] = 'Not Permitted';
                        $is_permitted = false;
                    }
                } else {
                    $data['err_msgs'] = 'Not Permitted';
                    $is_permitted = false;
                }
                if ($is_permitted) {
                    $account_type = $account_result['account_type'];
                    $order_by = '';
                    $per_page = 1;
                    $segment = 0;
                    $search_data = array('account_type' => $account_type);
                    $roles_result = $this->role_mod->get_role_permission($order_by, $per_page, $segment, $search_data);
                    if (isset($roles_result['result']) && count($roles_result['result']) > 0) {
                        $roles_data = current($roles_result['result']);
                    } else {
                        $roles_data = array();
                    }
                    $data['roles_data'] = $roles_data;
                    $data['account_result'] = $account_result;
                }
            }
        } else {
            show_404();
        }
        $logged_account_type = get_logged_account_type();
        $this->load->view('basic/header', $data);
        $this->load->view('role/account_permission', $data);
        $this->load->view('basic/footer', $data);
    }

}
