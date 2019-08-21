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

class Routes extends CI_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('route_mod');
    }

    public function index($arg1 = '', $format = '') {
        $page_name = "route_index";
        $file_name = 'Dialplan_' . date('Ymd');
        $is_file_downloaded = false;
        if (!check_account_permission('routing', 'view'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('routes', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'routes', 'location', '301');
            }

            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->route_mod->delete($delete_param_array);

                if ($result['status'] === true) {
                    $suc_msgs = count($delete_id_array) . ' Route';
                    if (count($delete_id_array) > 1)
                        $suc_msgs .= 's';
                    $suc_msgs .= ' Deleted Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    redirect(current_url(), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } else {
                $err_msgs = 'Select row to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
            redirect(base_url() . 'routes', 'location', '301');
        }
        $search_data = array();
        if (isset($_POST['search_action']))
            $_SESSION['search_routes_data'] = array('s_route_name' => $_POST['name'],
                's_route_abbr' => $_POST['abbr'],
                's_status_id' => $_POST['status'],
                's_no_of_records' => $_POST['no_of_rows']
            );
        else {
            $r = $this->uri->segment(2);
            if ($r == '') {
                $_SESSION['search_routes_data']['s_route_name'] = isset($_SESSION['search_routes_data']['s_route_name']) ? $_SESSION['search_routes_data']['s_route_name'] : '';
                $_SESSION['search_routes_data']['s_route_abbr'] = isset($_SESSION['search_routes_data']['s_route_abbr']) ? $_SESSION['search_routes_data']['s_route_abbr'] : '';
                $_SESSION['search_routes_data']['s_status_id'] = isset($_SESSION['search_routes_data']['s_status_id']) ? $_SESSION['search_routes_data']['s_status_id'] : '';
                $_SESSION['search_routes_data']['s_no_of_records'] = isset($_SESSION['search_routes_data']['s_no_of_records']) ? $_SESSION['search_routes_data']['s_no_of_records'] : '';
            }
        }

        $search_data = array('dialplan_name' => $_SESSION['search_routes_data']['s_route_name'],
            'dialplan_id' => $_SESSION['search_routes_data']['s_route_abbr'],
            'dialplan_status' => $_SESSION['search_routes_data']['s_status_id'],
        );
        $order_by = '';
        if ($arg1 == 'export' && $format != '') {
            $this->load->library('Export');
            $format = param_decrypt($format);
            $option_param = array('tariff' => true);
            $response_data = $this->route_mod->get_data($order_by, '', '', $search_data, $option_param);

            $search_array = array();
            if ($_SESSION['search_routes_data']['s_route_name'] != '')
                $search_array['Name'] = $_SESSION['search_routes_data']['s_route_name'];
            if ($_SESSION['search_routes_data']['s_route_abbr'] != '')
                $search_array['Abbreviation'] = $_SESSION['search_routes_data']['s_route_abbr'];
            if ($_SESSION['search_routes_data']['s_status_id'] != '')
                $search_array['Status'] = $_SESSION['search_routes_data']['s_status_id'] == '0' ? 'Inactive' : 'Active';

            $export_header = array('Name', 'Abbreviation', 'Status');

            if ($response_data['total'] > 0) {
                foreach ($response_data['result'] as $row) {
                    $ex_status = $row['dialplan_status'] == 1 ? 'Active' : 'Inactive';
                    $export_data[] = array($row['dialplan_name'], $row['dialplan_id'], $ex_status);
                }
            } else
                $export_data = array('');

            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }
            if (isset($_SESSION['search_routes_data']['s_no_of_records']) && $_SESSION['search_routes_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_routes_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            $option_param = array();
            $response = $this->route_mod->get_data($order_by,  $segment, $per_page, $search_data, $option_param);
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['total'], 'routes/index', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);
            $data['page_name'] = $page_name;
            $data['pagination'] = $this->pagination->create_links();
            $data['listing_data'] = $response['result'];
            $data['total_records'] = $data['listing_count'] = $response['total'];

            
            //print_r($data);
            $this->load->view('basic/header', $data);
            $this->load->view('carrier/routes', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function addR() {
        $data['page_name'] = "route_add";
        if (!check_account_permission('routing', 'add'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_name', 'Name', 'trim|required|min_length[5]|max_length[20]');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('frm_desc', 'Description', 'trim|min_length[0]|max_length[50]');
            $this->form_validation->set_rules('frm_failover', 'Failover SIP Cause', 'trim|min_length[0]|max_length[300]');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->route_mod->add($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Route Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'routes/editR/' . param_encrypt($result['id']), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'routes', 'location', '301');
                    } else {
                        redirect(base_url() . 'routes', 'location', '301');
                    }
                    redirect(base_url() . 'routes/editR/' . param_encrypt($route_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        $this->load->view('basic/header', $data);
        $this->load->view('carrier/route_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editR() {
        $data['page_name'] = "route_edit";
        if (!check_account_permission('routing', 'edit'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $route_id = param_decrypt($this->uri->segment(3));
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_id', 'Route ID', 'trim|required');
            $this->form_validation->set_rules('frm_name', 'Name', 'trim|required|min_length[5]|max_length[20]');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');
            $this->form_validation->set_rules('frm_desc', 'Description', 'trim|min_length[0]|max_length[50]');
            $this->form_validation->set_rules('frm_failover', 'Failover SIP Cause', 'trim|min_length[0]|max_length[300]');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->route_mod->update($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Route Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'routes/editR/' . param_encrypt($route_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'routes', 'location', '301');
                    } else {
                        redirect(base_url() . 'routes', 'location', '301');
                    }
                    redirect(base_url() . 'routes/editR/' . param_encrypt($route_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        $show_404 = false;
        if (!empty($route_id) && strlen($route_id) > 0) {
            $search_data = array('dialplan_id' => $route_id);
            $response_data = $this->route_mod->get_data('', 0, RECORDS_PER_PAGE, $search_data, array());
            if ($response_data['total'] > 0)
                $data['data'] = $response_data['result'][0];
            else
                $show_404 = true;
        } else
            $show_404 = true;

        $data['route_id'] = $route_id;
        $this->load->view('basic/header', $data);
        if ($show_404)
            $this->load->view('basic/404', $data);
        else
            $this->load->view('carrier/route_edit', $data);
        $this->load->view('basic/footer', $data);
    }

}
