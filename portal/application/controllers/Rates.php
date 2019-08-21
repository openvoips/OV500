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

class Rates extends CI_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();

        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');

        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('rate_mod');
        $this->load->model('Utils_model');
        $this->load->model('ratecard_mod');
        $this->load->model('tariff_mod');
        $this->output->enable_profiler(ENABLE_PROFILE);
    }

    public function index($arg1 = '', $format = '') {       
        $page_name = "rate_index";
        $file_name = 'Rate_' . date('Ymd');
        $is_file_downloaded = false;
        if (!check_account_permission('rate', 'view'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('rate', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'rate', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->rate_mod->delete($delete_param_array);
                if ($result['status'] === true) {
                    $suc_msgs = count($delete_id_array) . ' Rate';
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
            redirect(base_url() . 'rate', 'location', '301');
        }
        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_rate_data'] = array(
                's_rate_card' => (isset($_POST['card']) ? $_POST['card'] : ''),
                's_rate_prefix' => $_POST['prefix'],
                's_rate_destination' => $_POST['dest'],
                's_tariff' => (isset($_POST['tariff']) ? $_POST['tariff'] : ''),
                's_status' => (isset($_POST['status']) ? $_POST['status'] : ''),
                's_no_of_records' => (isset($_POST['no_of_records']) ? $_POST['no_of_records'] : '')
            );
        } else {
            $r = $this->uri->segment(2);
            if ($r == '') {
                $_SESSION['search_rate_data']['s_rate_card'] = isset($_SESSION['search_rate_data']['s_rate_card']) ? $_SESSION['search_rate_data']['s_rate_card'] : '';
                $_SESSION['search_rate_data']['s_rate_prefix'] = isset($_SESSION['search_rate_data']['s_rate_prefix']) ? $_SESSION['search_rate_data']['s_rate_prefix'] : '';
                $_SESSION['search_rate_data']['s_tariff'] = isset($_SESSION['search_rate_data']['s_tariff']) ? $_SESSION['search_rate_data']['s_tariff'] : '';
                $_SESSION['search_rate_data']['s_status'] = isset($_SESSION['search_rate_data']['s_status']) ? $_SESSION['search_rate_data']['s_status'] : '';
                $_SESSION['search_rate_data']['s_rate_destination'] = isset($_SESSION['search_rate_data']['s_rate_destination']) ? $_SESSION['search_rate_data']['s_rate_destination'] : '';

                $_SESSION['search_rate_data']['s_no_of_records'] = isset($_SESSION['search_rate_data']['s_no_of_records']) ? $_SESSION['search_rate_data']['s_no_of_records'] : RECORDS_PER_PAGE;
            }
        }

        $search_data = array(
            'ratecard_id' => $_SESSION['search_rate_data']['s_rate_card'],
            'prefix' => $_SESSION['search_rate_data']['s_rate_prefix'],
            'destination' => $_SESSION['search_rate_data']['s_rate_destination'],
            'tariff_id' => $_SESSION['search_rate_data']['s_tariff'],
            'rates_status' => $_SESSION['search_rate_data']['s_status']
        );
        $order_by = '';
        if ($arg1 == 'export' && $format != '') {
            ini_set('memory_limit', '2048M');
            $this->load->library('Export');
            $format = param_decrypt($format);
            $option_param = array('tariff' => true);
            $response_data = $this->rate_mod->get_data($order_by, '', '', $search_data, $option_param);
            if ($response_data['total'] > 0) {
                $k = 0;
                foreach ($response_data['result'] as $row) {
                    if ($k == 0)
                        $ratecard_for = $row['ratecard_for'];
                    $k++;
                    if (isset($ratecard_for) && $ratecard_for == 'INCOMING') {
                        $export_data[] = array($row['prefix'], $row['destination'], $row['rate'], $row['connection_charge'], $row['minimal_time'], $row['resolution_time'], $row['grace_period'], $row['rate_multiplier'], $row['rate_addition'], $row['rates_status'], $row['inclusive_channel'], $row['exclusive_per_channel_rental'], $row['rental'], $row['setup_charge']);
                    } else {
                        $export_data[] = array($row['prefix'], $row['destination'], $row['rate'], $row['connection_charge'], $row['minimal_time'], $row['resolution_time'], $row['grace_period'], $row['rate_multiplier'], $row['rate_addition'], $row['rates_status']);
                    }
                }
            } else {
                $export_data = array('');
            }
            if (isset($ratecard_for) && $ratecard_for == 'INCOMING') {
                $export_header = array('Prefix', 'Destination', 'PPM', 'PPC', 'Minimal', 'Resolution', 'GracePeriod', 'Multiplier', 'Addition', 'Status', 'Inclusive Channel', 'Exclusive Per Channel Rental', 'Rental', 'Setup Charge');
            } else {
                $export_header = array('Prefix', 'Destination', 'PPM', 'PPC', 'Minimal', 'Resolution', 'GracePeriod', 'Multiplier', 'Addition', 'Status');
            }

            $downloaded_message = $this->export->download($file_name, $format, $search_data, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        $ratecard_search_data = array(
            'logged_account_type' => get_logged_account_type(),
            'logged_current_customer_id' => get_logged_account_id(),
            'logged_account_level' => get_logged_account_level(),
        );

        $data['ratecard_dropdown'] = $this->ratecard_mod->get_data(array('ratecard_name' => 'ASC'), 0, '', $ratecard_search_data, array());
        if (isset($_SESSION['search_rate_data']['s_tariff']) && $_SESSION['search_rate_data']['s_tariff'] == '')
            $data['ratecard_data'] = $data['ratecard_dropdown'];
        else {
            $tariff = $_SESSION['search_rate_data']['s_tariff'];
            $data['ratecard_data'] = $this->tariff_mod->get_mapping('', 0, '', array('tariff_id' => $tariff), array());
        }

        if (check_logged_account_type(array('RESELLER', 'CUSTOMER'))) {
            $tariff_search_data['created_by'] = get_logged_account_id();
        } else {
            $tariff_search_data['created_by'] = 'admin';
        }

        $tariff_search_data['logged_account_type'] = get_logged_account_type();
        $tariff_search_data['logged_current_customer_id'] = get_logged_account_id();
        $tariff_search_data['logged_account_level'] = get_logged_account_level();
        $data['tariff_data'] = $this->tariff_mod->get_data(array('tariff_name' => 'ASC'), 0, '', $tariff_search_data);

        if ($is_file_downloaded === false) {
            $data['page_name'] = $page_name;            
            $pagination_uri_segment = $this->uri->segment(3, 0);
            if (isset($_SESSION['search_rate_data']['s_no_of_records']) && $_SESSION['search_rate_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_rate_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;
            $response = $this->rate_mod->get_data($order_by, $pagination_uri_segment, $per_page, $search_data);
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['total'], 'rates/index', $per_page, 3);
            $this->pagination->initialize($config);
            $data['searching'] = 1;
            $data['pagination'] = $this->pagination->create_links();
            $data['listing_data'] = $response['result'];
           $data['total_records']=  $data['listing_count'] = $response['total'];


            $this->load->view('basic/header', $data);
            $this->load->view('rates/rates', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function addR() {
        $data['page_name'] = "rate_add";
        if (!check_account_permission('rate', 'add'))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $data['ratecard_data'] = $this->ratecard_mod->get_data('', 0, 2000, array(), array());

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_prefix', 'Prefix', 'trim|required|numeric|min_length[1]|max_length[15]');
            $this->form_validation->set_rules('frm_dest', 'Destination', 'trim|required|alpha_numeric_spaces|min_length[1]|max_length[40]');
            $this->form_validation->set_rules('frm_ppm', 'Rate', 'trim|required');
            $this->form_validation->set_rules('frm_ppc', 'Connection charge', 'trim|required');
            $this->form_validation->set_rules('frm_min', 'Minimal Time', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('frm_res', 'Resolution Time', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('frm_grace', 'Grace Period', 'trim|required|is_natural');
            $this->form_validation->set_rules('frm_mul', 'Rate Multiplier', 'trim|required|numeric');
            $this->form_validation->set_rules('frm_add', 'Rate Addition', 'trim|required|numeric');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');

            $search_data = array('ratecard_id' => $_POST['frm_card']);
            $response_data = $this->ratecard_mod->get_data('', 0, 10, $search_data, array());
            if ($response_data['total'] > 0) {
                $_POST['ratecard_type'] = $response_data["result"][0]["ratecard_type"];
                $_POST['ratecard_for'] = $response_data["result"][0]["ratecard_for"];
            }

            if (strtolower($_POST['ratecard_for']) == 'incoming') {
                $this->form_validation->set_rules('frm_rental', 'Rental', 'trim|required');
                $this->form_validation->set_rules('frm_setup_charge', 'Setup Charge', 'trim|required');
                $this->form_validation->set_rules('frm_inclusive_channel', 'Inclusive Channel', 'trim|required|numeric');
                $this->form_validation->set_rules('frm_exclusive_per_channel_rental', 'Exclusive Per Channel Rental', 'trim|required');
            }


            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {

                /* $search_data=array('ratecard_id_name'=>$_POST['frm_card']);
                  $response_data = $this->ratecard_mod->get_data('', 0 ,10, $search_data,array());
                  if($response_data['total']>0)
                  {
                  $_POST['ratecard_type'] = $response_data["result"][0]["ratecard_type"];
                  $_POST['ratecard_for'] = $response_data["result"][0]["ratecard_for"];
                  } */

                $result = $this->rate_mod->add($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Rate Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'rates/editR/' . param_encrypt($result['id'] . '@' . $_POST['frm_card']), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'rates', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'rates', 'location', '301');
                    }
                    redirect(base_url() . 'rates/editR/' . param_encrypt($route_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        ///////////////////////////			

        $this->load->view('basic/header', $data);
        $this->load->view('rates/rate_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editR() {
        $data['page_name'] = "rate_edit";
        if (!check_account_permission('rate', 'edit'))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $data['ratecard_data'] = $this->ratecard_mod->get_data('', 0, '', array(), array());
        $rate_id_with_name = explode('@', param_decrypt($this->uri->segment(3)));
        $rate_id = intval($rate_id_with_name[0]);
        $ratecard_id = $rate_id_with_name[1];
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_id', 'Rate ID', 'trim|required');
            $this->form_validation->set_rules('frm_prefix', 'Prefix', 'trim|required|numeric|min_length[1]|max_length[15]');
            $this->form_validation->set_rules('frm_dest', 'Destination', 'trim|required|alpha_numeric_spaces|min_length[1]|max_length[40]');
            $this->form_validation->set_rules('frm_ppm', 'Rate', 'trim|required');
            $this->form_validation->set_rules('frm_ppc', 'Connection charge', 'trim|required');
            $this->form_validation->set_rules('frm_min', 'Minimal Time', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('frm_res', 'Resolution Time', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('frm_grace', 'Grace Period', 'trim|required|is_natural');
            $this->form_validation->set_rules('frm_mul', 'Rate Multiplier', 'trim|required|numeric');
            $this->form_validation->set_rules('frm_add', 'Rate Addition', 'trim|required|numeric');
            $this->form_validation->set_rules('frm_status', 'Status', 'trim|required');




            if (strpos($_POST['frm_rate_table_name'], 'incoming') !== false) {
                $this->form_validation->set_rules('frm_rental', 'Rental', 'trim|required');
                $this->form_validation->set_rules('frm_setup_charge', 'Setup Charge', 'trim|required');

                $this->form_validation->set_rules('frm_inclusive_channel', 'Inclusive Channel', 'trim|required|numeric');
                $this->form_validation->set_rules('frm_exclusive_per_channel_rental', 'Exclusive Per Channel Rental', 'trim|required');
            }


            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->rate_mod->update($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Rate Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'rates/editR/' . param_encrypt($rate_id . '@' . $ratecard_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'rates/index/', 'location', '301');
                    }
                    else {
                        redirect(base_url() . 'rates', 'location', '301');
                    }
                    redirect(base_url() . 'rates/editR/' . param_encrypt($rate_id . '@' . $ratecard_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        ///////////////////////////		
        $show_404 = false;
        if (!empty($rate_id) && $rate_id != 0) {
            $search_data = array('rate_id' => $rate_id, 'ratecard_id' => $ratecard_id);
            $response_data = $this->rate_mod->get_data('', 0, RECORDS_PER_PAGE, $search_data, array());
            if ($response_data['total'] > 0)
                $data['data'] = $response_data['result'][0];
            else
                $show_404 = true;
        } else
            $show_404 = true;

        $data['rate_id'] = $rate_id;
        $this->load->view('basic/header', $data);
        if ($show_404)
            $this->load->view('basic/404', $data);
        else
            $this->load->view('rates/rate_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function MyRates() {
        $page_name = "rate_MyRates";
        $is_searched = false;
        $data['searching'] = 0;
        if (!check_logged_account_type(array('CUSTOMER', 'RESELLER')))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $data['page_name'] = $page_name;
        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_myrate'] = array('s_myrate_prefix' => $_POST['prefix'], 's_myrate_dest' => $_POST['dest'], 's_myrate_ratecard_for' => $_POST['ratecard_for']);
            if ($_SESSION['search_myrate']['s_myrate_prefix'] != '' || $_SESSION['search_myrate']['s_myrate_dest'] != '' || $_SESSION['search_myrate']['s_myrate_ratecard_for'] != '') {
                $is_searched = true;
            }
        } else {
            $_SESSION['search_myrate']['s_myrate_prefix'] = isset($_SESSION['search_myrate']['s_myrate_prefix']) ? $_SESSION['search_myrate']['s_myrate_prefix'] : '';
            $_SESSION['search_myrate']['s_myrate_dest'] = isset($_SESSION['search_myrate']['s_myrate_dest']) ? $_SESSION['search_myrate']['s_myrate_dest'] : '';
            $_SESSION['search_myrate']['s_myrate_ratecard_for'] = isset($_SESSION['search_myrate']['s_myrate_ratecard_for']) ? $_SESSION['search_myrate']['s_myrate_ratecard_for'] : '';
        }
        if ($is_searched) {
            $account_id = get_logged_account_id();
            $option_param = array();
            $user_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
            $tariff_id = $user_result['tariff_id'];
            $search_data = array(
                'tariff_id' => $tariff_id,
                'prefix' => $_SESSION['search_myrate']['s_myrate_prefix'],
                'destination' => $_SESSION['search_myrate']['s_myrate_dest'],
                'ratecard_for' => $_SESSION['search_myrate']['s_myrate_ratecard_for'],
            );
            $order_by = '';
            $rate_data = $this->rate_mod->get_MyRates($order_by, 0, 1000, $search_data);
            $data['searching'] = 1;
            $data['pagination'] = $this->pagination->create_links();
            $data['listing_data'] = $rate_data['result'];
            $data['listing_count'] = $rate_data['total'];
        }
        $data['is_searched'] = $is_searched;

        $this->load->view('basic/header', $data);
        $this->load->view('rates/MyRates', $data);
        $this->load->view('basic/footer', $data);
    }

    function download($tariff_type = 'out', $account_id = '') {
        if (!check_logged_account_type(array('CUSTOMER', 'RESELLER')))
            show_404();
        ini_set('memory_limit', '2048M');
        set_time_limit(500);
        if ($account_id == '') {
            $account_id = get_logged_account_id();
        } else {
            $account_id = param_decrypt($account_id);
        }
        $option_param = array();
        $user_result = $this->member_mod->get_account_by_key('account_id', $account_id, $option_param);
        if ($user_result === false) {
            show_404();
        } elseif ($user_result['tariff_id'] == '') {
            show_404();
        }
        $tariff_id = $user_result['tariff_id'];

        if ($tariff_type == 'out') {

//            $search_data = array('tariff_id' => $tariff_id);
            $search_data = array(
                'tariff_id' => $tariff_id,
                'ratecard_for' => 'OUTGOING',
            );
            $rate_data = $this->rate_mod->get_MyRates('', 0, '', $search_data);
            $filename = urlencode('OutgoingRates.csv');
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$filename");
            header("Content-Type: application/csv; ");
            $file = fopen('php://output', 'w');
            $header = array("Prefix", "Destination", "PPM", "PPC", "Minimal", "Resolution", "Grace", "Multiplier", "Addition", "Status");
            fputcsv($file, $header);
            if (isset($rate_data['result']) && count($rate_data['result']) > 0) {
                foreach ($rate_data['result'] as $rate_array) {
                    if ($rate_array['rates_status'] == 1) {
                        $prefix = $rate_array['prefix'];
                        $destination = $rate_array['destination'];
                        $rate = $rate_array['rate'];
                        $connection_charge = $rate_array['connection_charge'];
                        $minimal_time = $rate_array['minimal_time'];
                        $resolution_time = $rate_array['resolution_time'];
                        $grace_period = $rate_array['grace_period'];
                        $rate_multiplier = $rate_array['rate_multiplier'];
                        $rate_addition = $rate_array['rate_addition'];
                        $rates_status = $rate_array['rates_status'];
                        $data_array_temp = array($prefix, $destination, $rate, $connection_charge, $minimal_time, $resolution_time, $grace_period, $rate_multiplier, $rate_addition, $rates_status);
                        fputcsv($file, $data_array_temp);
                    }
                }
            }

            fclose($file);
            exit;
        } else {//incoming
//            $search_data = array('tariff_id' => $tariff_id);
//             $search_data = array(
//                'tariff_id' => $tariff_id,                
//                'ratecard_for' => 'OUTGOING',
//            );
//            $rate_data = $this->rate_mod->get_MyRates('', 0, '', $search_data);
//            
//            $tariff_response = $this->tariff_mod->get_data('', '', '', $search_data, array());
//
//            $tariff_data = current($tariff_response['result']);
//
//            $ratecard_id = $tariff_data['incoming_ratecard_id'];
//            if ($ratecard_id_name != '') {
//                $search_data = array('ratecard_id_name' => $ratecard_id_name);
//                $rate_data = $this->rate_mod->get_data('', 0, '', $search_data);
//            }
            $search_data = array(
                'tariff_id' => $tariff_id,
                'ratecard_for' => 'INCOMING',
            );
            $rate_data = $this->rate_mod->get_MyRates('', 0, '', $search_data);

            $filename = urlencode('IncomingRates.csv');
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$filename");
            header("Content-Type: application/csv; ");

            $file = fopen('php://output', 'w');

            $header = array("Prefix", "Destination", "PPM", "PPC", "Minimal", "Resolution", "Grace", "Multiplier", "Addition", "Setup Charge", "Rental", "Status");
            fputcsv($file, $header);

            if (isset($rate_data['result']) && count($rate_data['result']) > 0) {
                foreach ($rate_data['result'] as $rate_array) {
                    if ($rate_array['rates_status'] == 1) {
                        $prefix = $rate_array['prefix'];
                        $destination = $rate_array['destination'];
                        $rate = $rate_array['rate'];
                        $connection_charge = $rate_array['connection_charge'];
                        $minimal_time = $rate_array['minimal_time'];
                        $resolution_time = $rate_array['resolution_time'];
                        $grace_period = $rate_array['grace_period'];
                        $rate_multiplier = $rate_array['rate_multiplier'];
                        $rate_addition = $rate_array['rate_addition'];

                        $setup_charge = $rate_array['setup_charge'];
                        $rental = $rate_array['rental'];
                        $rates_status = $rate_array['rates_status'];

                        $data_array_temp = array($prefix, $destination, $rate, $connection_charge, $minimal_time, $resolution_time, $grace_period, $rate_multiplier, $rate_addition, $setup_charge, $rental, $rates_status);
                        fputcsv($file, $data_array_temp);
                    }
                }
            }

            fclose($file);
            exit;
        }
    }

}
