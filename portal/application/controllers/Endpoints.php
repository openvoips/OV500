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

class Endpoints extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('customer_mod');
        $this->load->model('reseller_mod');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
    }

    function index($account_id = '', $customer_type = 'customer') {
        $page_name = "Endpoint_index";
        $data['page_name'] = $page_name;
        $account_id = param_decrypt($account_id);
        if ($customer_type != 'customer')
            $customer_type = param_decrypt($customer_type);
        if (strlen($account_id) < 1) {
            show_404();
        }

        $page_name = "{$customer_type}_edit";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('customer', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(current_url(), 'location', '301');
            }
            if (!isset($_POST['delete_parameter_two'])) {
                $this->session->set_flashdata('err_msgs', 'Insufficient Parameters');
                redirect(current_url(), 'location', '301');
            }
            if (!isset($_POST['delete_id'])) {
                $err_msgs = 'Select to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }

            switch ($_POST['delete_parameter_two']) {
                case 'account_ips_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->customer_mod->delete_ip($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'IP User Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    redirect(current_url(), 'location', '301');
                    break;
                case 'account_sip_delete':
                    $delete_id_array = json_decode($_POST['delete_id']);
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->customer_mod->delete_sip($account_id, $delete_param_array);
                    if ($result === true) {
                        $suc_msgs = 'SIP User Deleted Successfully';
                        $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    } else {
                        $err_msgs = $result;
                        $this->session->set_flashdata('err_msgs', $err_msgs);
                    }
                    redirect(current_url(), 'location', '301');
                    break;
                default:
                    $this->session->set_flashdata('err_msgs', 'Parameter mismatch');
                    redirect(current_url(), 'location', '301');
            }
        }


        if (strlen($account_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            if ($customer_type == 'RESELLER') {
                if (check_logged_account_type(array('ACCOUNTMANAGER')))
                    $search_data['account_manager'] = get_logged_account_id();
            }else {
                if (check_logged_account_type(array('ACCOUNTMANAGER')))
                    $search_data['account_manager'] = get_logged_account_id();
                elseif (check_logged_account_type(array('RESELLER')))
                    $search_data['parent_account_id'] = get_logged_account_id();
                elseif (check_logged_account_type(array('NOC', 'ADMIN', 'SUBADMIN'))) {
                    
                }
            }


            if ($customer_type == 'RESELLER') {
                $option_param = array('callerid' => true, 'user' => false, 'prefix' => false, 'dialplan' => true, 'translation_rules' => true, 'callerid_incoming' => true, 'translation_rules_incoming' => true, 'notification' => false);

                $customers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            } else {
                $option_param = array('ip' => true, 'callerid' => true, 'sipuser' => true, 'user' => false, 'prefix' => false, 'dialplan' => true, 'translation_rules' => true, 'callerid_incoming' => true, 'translation_rules_incoming' => true, 'notification' => true);
                $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            }

            if (isset($customers_data_temp['result'])) {
                $customers_data = current($customers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $data['account_id'] = $account_id;
        $logged_account_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();


        $view_file = 'customer/endpoint';

        $this->load->view('basic/header', $data);
        $this->load->view($view_file, $data);
        $this->load->view('basic/footer', $data);
    }

    public function EPsipAdd($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        if (isset($id2)) {
            $id = param_decrypt($id2);
        }
        if (strlen($account_id) < 1) {
            show_404();
        }
        $page_name = "{$customer_type}_sip_add";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim');
            $this->form_validation->set_rules('sip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('sip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('status', 'Status', 'trim|required');
            $this->form_validation->set_rules('voicemail', 'Voicemail Option', 'trim|required');
            $this->form_validation->set_rules('voicemail_email', 'Voicemail EmailList', 'trim');
            $this->form_validation->set_rules('extension_no', 'Extension', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->add_sip($_POST);
                $id = $this->customer_mod->last_customer_sip_id;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User SIP Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(base_url('endpoints') . '/EPsipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(base_url('endpoints/index/') . param_encrypt($account_id), 'location', '301');
                        }
                    } else {
                        redirect(base_url('endpoints/index/') . param_encrypt($account_id), 'location', '301');
                    }
                    redirect(base_url('endpoints') . '/EPsipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array();
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result'])) {
                $customers_data = current($customers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }

        $data['data'] = $customers_data;
        $data['account_id'] = $account_id;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/EPsipAdd', $data);
        $this->load->view('basic/footer', $data);
    }

    public function EPsipEdit($id1 = -1, $id2 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        $id = param_decrypt($id2);
        if (strlen($account_id) < 1 and $id < 1)
            show_404();
        $page_name = "EPsipEdit";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $id = $_POST['id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'User ID', 'trim|required');
            $this->form_validation->set_rules('id', 'SIP ID', 'trim|required');
            $this->form_validation->set_rules('username', 'Username', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim');
            $this->form_validation->set_rules('voicemail', 'Voicemail Option', 'trim|required');
            $this->form_validation->set_rules('voicemail_email', 'Voicemail', 'trim');
            $this->form_validation->set_rules('extension_no', 'Extension', 'trim|required');
            $this->form_validation->set_rules('sip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('sip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('status', 'Status', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->update_sip($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User SIP Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url('endpoints') . '/EPsipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url('endpoints/index/') . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(base_url('endpoints/index/') . param_encrypt($account_id), 'location', '301');
                    }
                    redirect(base_url('endpoints') . '/EPsipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (strlen($account_id) > 1 and $id > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('sipuser' => true, 'customer_sip_id' => $id);
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result'])) {
                $customers_data = current($customers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/EPsipEdit', $data);
        $this->load->view('basic/footer', $data);
    }

     public function ipAdd($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        if (isset($id2))
            $id = param_decrypt($id2);

        if (strlen($account_id) < 1)
            show_404();
//        if (!check_account_permission('customer', 'edit'))
//            show_404('403');

        $page_name = "{$customer_type}_ipAdd";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim|required');
            $this->form_validation->set_rules('dialprefix', 'Dial Prefix', 'trim|required');
            $this->form_validation->set_rules('ip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('ip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('ip_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('description', 'description', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->add_ip($_POST);
                $id = $this->customer_mod->last_customer_ip_id;

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User IP Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(base_url('endpoints') . '/ipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(base_url( 'endpoints') . '/index/' . param_encrypt($account_id), 'location', '301');
                        }
                    } else {
                        redirect(base_url( 'endpoints'), 'location', '301');
                    }
                    redirect(base_url( 'endpoints'), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 1) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array();
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result'])) {
                $customers_data = current($customers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $data['account_id'] = $account_id;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/EipAdd', $data);
        $this->load->view('basic/footer', $data);
    }

    public function ipEdit($id1 = -1, $id2 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        $id = param_decrypt($id2);
        if (strlen($account_id) < 1 and $id < 1)
            show_404();
//        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
//            show_404('403');

        $page_name = "{$customer_type}_ipEdit";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;


        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $id = $_POST['id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('id', 'IP ID', 'trim|required');
            $this->form_validation->set_rules('ipaddress', 'IP', 'trim|required');
            $this->form_validation->set_rules('dialprefix', 'Dial Prefix', 'trim|required');
            $this->form_validation->set_rules('ip_cc', 'CC', 'trim|required');
            $this->form_validation->set_rules('ip_cps', 'CPS', 'trim|required');
            $this->form_validation->set_rules('ip_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('description', 'description', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->customer_mod->update_ip($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'User IP Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url('endpoints') . '/ipEdit/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url( 'endpoints') . '/index/' . param_encrypt($account_id), 'location', '301');
                    } else {
                        redirect(base_url($customer_type . 's'), 'location', '301');
                    }

                    redirect(base_url( 'endpoints') . '/index/' . param_encrypt($account_id) . '/' . param_encrypt($id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (strlen($account_id) > 1 and $id > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('ip' => true, 'account_ip_id' => $id);
            $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/EipEdit', $data);
        $this->load->view('basic/footer', $data);
    }

    
    public function editSRCNo($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        $customer_type = param_decrypt($customer_type);
        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_editSRCNo";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');
            $this->form_validation->set_rules('dst_src_cli_rules', 'Destination prefix based CLI transalation Rules', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['account_id'] = $_POST['account_id'];

                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }
                if ($_POST['dst_src_cli_rules'] != '') {
                    $post_array['dst_src_cli_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['dst_src_cli_rules']);
                }

                $result = $this->customer_mod->update_callerid($post_array);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Caller ID Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url('endpoints') . '/editSRCNo/' . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url('endpoints/index/') . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                    }
                    else {
                        redirect(base_url('endpoints/index/') . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                    }
                    redirect(base_url('endpoints/index/') . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 1) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('callerid' => true);
            if (strtoupper($customer_type) == 'CUSTOMER') {
                $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            } elseif (strtoupper($customer_type) == 'RESELLER') {
                $this->load->model('reseller_mod');
                $customers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            }
            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/ENeditSRCNo', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editINSRCNo($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        $customer_type = param_decrypt($customer_type);


        if (strlen($account_id) < 1)
            show_404();
        if ($customer_type == 'CUSTOMER') {
            if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
                show_404('403');
        }elseif ($customer_type == 'RESELLER') {
            if (!check_account_permission('reseller', 'view') && !check_account_permission('reseller', 'edit'))
                show_404('403');
        }

        $page_name = "editINSRCNo";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'User ID', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['account_id'] = $_POST['account_id'];
                $post_array['account_id'] = $_POST['account_id'];

                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }
//                echo $customer_type;
//                if (strtoupper($customer_type) == 'CUSTOMER' or strtoupper($customer_type) == 'reseller') {
                $result = $this->customer_mod->update_callerid_incoming($post_array);
//                } 
//                print_r($post_array); 
//                print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Incoming Caller ID Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url('endpoints') . '/editINSRCNo/' . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url('endpoints/index/') . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                    }
                    else {
                        redirect(base_url('endpoints/index/') . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                    }

                    redirect(base_url('endpoints') . '/editINSRCNo/' . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 1) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('callerid_incoming' => true);

            if (strtoupper($customer_type) == 'CUSTOMER') {
                $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            } elseif (strtoupper($customer_type) == 'RESELLER') {
                $this->load->model('reseller_mod');
                $customers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            }

            if (isset($customers_data_temp['result'])) {
                $customers_data = current($customers_data_temp['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/EnINSRCNo', $data);
        $this->load->view('basic/footer', $data);
    }

    public function EnDSTrules($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        $customer_type = param_decrypt($customer_type);
        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "{$customer_type}_EnDSTrules";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;
            $this->form_validation->set_rules('account_id', 'Account Code', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['account_id'] = $_POST['account_id'];
                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }

                $result = $this->customer_mod->update_translation_rules($post_array);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(base_url('endpoints') . '/EnDSTrules/' . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(base_url('endpoints/index/') . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                        }
                    } else {
                        redirect(base_url('endpoints/index/') . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                    }

                    redirect(base_url('endpoints') . '/EnDSTrules/' . param_encrypt($account_id) . '/' . param_encrypt(strtoupper($customer_type)), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 0) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('translation_rules' => true);

            if (strtoupper($customer_type) == 'CUSTOMER') {
                $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            } elseif (strtoupper($customer_type) == 'RESELLER') {
                $this->load->model('reseller_mod');
                $customers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            }


            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/EnDSTrules', $data);
        $this->load->view('basic/footer', $data);
    }

    public function EnDIDRule($id1 = -1, $customer_type = 'customer') {
        $account_id = param_decrypt($id1);
        $customer_type = param_decrypt($customer_type);


        if (strlen($account_id) < 1)
            show_404();
        if (!check_account_permission('customer', 'view') && !check_account_permission('customer', 'edit'))
            show_404('403');

        $page_name = "EnDIDRule";
        $data['page_name'] = $page_name;
        $data['customer_type'] = $customer_type;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $account_id = $_POST['account_id'];
            $data['account_id'] = $account_id;

            $this->form_validation->set_rules('account_id', 'User ID', 'trim|required');
            $this->form_validation->set_rules('allowed_rules', 'Allowed Rules', 'trim');
            $this->form_validation->set_rules('disallowed_rules', 'Disallowed Rules', 'trim');

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $post_array['account_id'] = $_POST['account_id'];
                $post_array['account_id'] = $_POST['account_id'];

                $post_array['allowed_rules_array'] = $post_array['disallowed_rules_array'] = array();
                if ($_POST['allowed_rules'] != '') {
                    $post_array['allowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['allowed_rules']);
                }
                if ($_POST['disallowed_rules'] != '') {
                    $post_array['disallowed_rules_array'] = preg_split('/\r\n|\r|\n|,/', $_POST['disallowed_rules']);
                }

                $result = $this->customer_mod->update_translation_rules_incoming($post_array);
                //print_r($post_array); print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Incoming Translation Rules Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url('endpoints') . '/EnDIDRule/' . param_encrypt($account_id) . "/" . param_encrypt(strtoupper($customer_type)), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url('endpoints/index/') . param_encrypt($account_id) . "/" . param_encrypt(strtoupper($customer_type)), 'location', '301');
                    }
                    else {
                        redirect(base_url('endpoints/index/') . param_encrypt($account_id) . "/" . param_encrypt(strtoupper($customer_type)), 'location', '301');
                    }

                    redirect(base_url('endpoints/index/') . param_encrypt($account_id) . "/" . param_encrypt(strtoupper($customer_type)), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (strlen($account_id) > 1) {
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array('account_id' => $account_id);
            $option_param = array('translation_rules_incoming' => true);
            //$customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);


            if (strtoupper($customer_type) == 'CUSTOMER') {
                $customers_data_temp = $this->customer_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            } elseif (strtoupper($customer_type) == 'RESELLER') {
                $this->load->model('reseller_mod');
                $customers_data_temp = $this->reseller_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            }


            if (isset($customers_data_temp['result']))
                $customers_data = current($customers_data_temp['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['data'] = $customers_data;
        $this->load->view('basic/header', $data);
        $this->load->view('customer/EnDIDRule', $data);
        $this->load->view('basic/footer', $data);
    }

    function get_prefixes_by_group() {
        $this->load->model('did_mod');
        $country_abbr = trim($_POST['country']);
        $group_name = trim($_POST['group_name']);
        $result = $this->did_mod->get_prefixes_by_group($country_abbr, $group_name);
        if ($result['status'] = 'success')
            $return = $result['result'];
        else
            $return = 'error';

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

}
