<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Dids extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('did_mod');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->output->enable_profiler(ENABLE_PROFILE);
    }

    function index($arg1 = '', $format = '') {
        $page_name = "did_index";
        $data['page_name'] = $page_name;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkUpdateDestinationBulk') {

            if ($_POST['dst_type'] == 'IP')
                $this->form_validation->set_rules('dst_point_ip', 'Destination Endpoint', 'trim|required');
            elseif ($_POST['dst_type'] == 'CUSTOMER')
                $this->form_validation->set_rules('dst_point_sip', 'Destination Endpoint', 'trim|required');
            elseif ($_POST['dst_type'] == 'CAMPAINGN')
                $this->form_validation->set_rules('dst_campaign_id', 'Destination Endpoint', 'trim|required');
            else
                $this->form_validation->set_rules('dst_point_pstn', 'Destination Endpoint', 'trim|required');



            $this->form_validation->set_rules('assign_did_number', 'DID Numbers', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->did_mod->destination_bulk($_POST);

                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'DID destination mapped successfully');
                    redirect(base_url() . 'dids', 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteDataBulk') {

            //echo '<pre>'; print_r($_POST);die;
            $logged_account_type = get_logged_account_type();
            $logged_account_id = get_logged_account_id();
            $logged_account_level = get_logged_account_level();
            if (check_logged_user_group(ADMIN_ACCOUNT_ID)) {
                $action_type = 'delete';
                $cancel_did_number = trim($_POST['cancel_did_number']);
                $delete_id_array = explode(',', $cancel_did_number);
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->did_mod->delete($logged_account_id, $delete_param_array);
                //ddd($delete_param_array);	var_dump( $result);		die;
                if ($result === true) {
                    $suc_msgs = 'DIDs Deleted Successfully';
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } elseif (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
                $result = $this->did_mod->release_bulk($_POST['cancel_did_number'], $logged_account_id);
                $suc_msgs = 'DIDs Cancelled Successfully';
            } else {
                $this->session->set_flashdata('err_msgs', 'You do not have enough permission');
                redirect(base_url() . 'dids', 'location', '301');
            }

            if ($result === true) {
                $this->session->set_flashdata('suc_msgs', $suc_msgs);
                redirect(current_url(), 'location', '301');
            } else {
                $err_msgs = $result;
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }
        }
        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            $logged_account_type = get_logged_account_type();
            $logged_account_id = get_logged_account_id();
            $logged_account_level = get_logged_account_level();
            if (check_logged_user_group(ADMIN_ACCOUNT_ID)) {
                $action_type = 'delete';
            } elseif (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
                $action_type = 'cancel';
            } else {
                $this->session->set_flashdata('err_msgs', 'You do not have enough permission');
                redirect(base_url() . 'dids', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                if ($action_type == 'delete') {
                    $delete_param_array = array('delete_id' => $delete_id_array);
                    $result = $this->did_mod->delete($logged_account_id, $delete_param_array);
                } else {
                    $result = $this->did_mod->release($delete_id_array[0], $logged_account_id);
                }
                if ($result === true) {
                    $suc_msgs = count($delete_id_array) . ' DID';
                    if (count($delete_id_array) > 1)
                        $suc_msgs .= 's';
                    if ($action_type == 'delete')
                        $suc_msgs .= ' Deleted Successfully';
                    else
                        $suc_msgs .= ' Canceled Successfully';
                    $this->session->set_flashdata('suc_msgs', $suc_msgs);
                    redirect(current_url(), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $this->session->set_flashdata('err_msgs', $err_msgs);
                    redirect(current_url(), 'location', '301');
                }
            } else {
                $err_msgs = 'Select DID to delete';
                $this->session->set_flashdata('err_msgs', $err_msgs);
                redirect(current_url(), 'location', '301');
            }

            redirect(base_url() . 'dids', 'location', '301');
        }

        if (isset($_POST['search_action'])) {
            $_SESSION['search_did_data'] = array('s_did_number' => $_POST['did_number'], 's_status' => $_POST['status'], 's_assigned_to' => $_POST['assigned_to'], 's_no_of_records' => $_POST['no_of_rows'],);
        } else {
            $_SESSION['search_did_data']['s_did_number'] = isset($_SESSION['search_did_data']['s_did_number']) ? $_SESSION['search_did_data']['s_did_number'] : '';
            $_SESSION['search_did_data']['s_status'] = isset($_SESSION['search_did_data']['s_status']) ? $_SESSION['search_did_data']['s_status'] : '';
            $_SESSION['search_did_data']['s_assigned_to'] = isset($_SESSION['search_did_data']['s_assigned_to']) ? $_SESSION['search_did_data']['s_assigned_to'] : '';
            $_SESSION['search_did_data']['s_no_of_records'] = isset($_SESSION['search_did_data']['s_no_of_records']) ? $_SESSION['search_did_data']['s_no_of_records'] : '';
        }
        $search_data = array(
            'did_number' => $_SESSION['search_did_data']['s_did_number'],
            'did_status' => $_SESSION['search_did_data']['s_status'],
            'assigned_to' => $_SESSION['search_did_data']['s_assigned_to'],
            'logged_account_type' => get_logged_account_type(),
            'logged_current_customer_id' => get_logged_account_id(),
            'logged_account_level' => get_logged_account_level(),
        );
        $order_by = '';

        $logged_account_type = get_logged_account_type();
        $get_logged_account_level = get_logged_account_level();

        $all_field_array = array(
            'did_number' => 'Number',
            'did_status' => 'Status',
            'carrier_id' => 'Carrier',
            'account_id' => 'Account',
            'assign_date' => 'Date',
            'reseller1_account_id' => 'R1',
            'reseller1_assign_date' => 'R1 Date',
            'reseller2_account_id' => 'R2',
            'reseller2_assign_date' => 'R2 Date',
            'reseller3_account_id' => 'R3',
            'reseller3_assign_date' => 'R3 Date',
            'create_date' => 'Create date',
            'dst_type' => 'DST Type',
            'did_dst_name' => 'DST Destination'
        );

        unset($all_field_array['create_date']);
        if (check_logged_user_group('RESELLER')) {
            unset($all_field_array['carrier_id']);
            unset($all_field_array['dst_type']);
            unset($all_field_array['did_dst_name']);//dst_destination
            if ($get_logged_account_level == 1) {
                unset($all_field_array['reseller3_account_id']);
                unset($all_field_array['reseller3_assign_date']);
                unset($all_field_array['reseller1_account_id']);
                $all_field_array['reseller1_assign_date'] = 'My Assign Date';
                $all_field_array['reseller2_account_id'] = 'Sub Reseller';
                $all_field_array['reseller2_assign_date'] = 'Sub Reseller Assign Date';
            } elseif ($get_logged_account_level == 2) {
                unset($all_field_array['reseller1_account_id']);
                unset($all_field_array['reseller1_assign_date']);
                unset($all_field_array['reseller2_account_id']);
                $all_field_array['reseller2_assign_date'] = 'My Assign Date';
                $all_field_array['reseller3_account_id'] = 'Sub Reseller';
                $all_field_array['reseller3_assign_date'] = 'Sub Reseller Assign Date';
            } else {
                unset($all_field_array['reseller1_account_id']);
                unset($all_field_array['reseller1_assign_date']);
                unset($all_field_array['reseller2_account_id']);
                unset($all_field_array['reseller2_assign_date']);
                unset($all_field_array['reseller3_account_id']);
                $all_field_array['reseller3_assign_date'] = 'My Assign Date';
            }
        } elseif (check_logged_user_group('CUSTOMER')) {
            unset($all_field_array['carrier_id']);
            unset($all_field_array['user_account_id']);
            unset($all_field_array['reseller1_account_id']);
            unset($all_field_array['reseller1_assign_date']);
            unset($all_field_array['reseller2_account_id']);
            unset($all_field_array['reseller2_assign_date']);
            unset($all_field_array['reseller3_account_id']);
            unset($all_field_array['reseller3_assign_date']);
        } else {
            
        }
        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            $format = param_decrypt($format);
            $option_param = array('did_dst' => true);
            $did_data = $this->did_mod->get_data($order_by, '', '', $search_data, $option_param);
            $search_array = array();
            if ($_SESSION['search_did_data']['s_did_number'] != '')
                $search_array['DID'] = $_SESSION['search_did_data']['s_did_number'];
            if ($_SESSION['search_did_data']['s_status'] != '')
                $search_array['Assigned To'] = $_SESSION['search_did_data']['s_status'];
            if ($_SESSION['search_did_data']['s_assigned_to'] != '')
                $search_array['Status'] = $_SESSION['search_did_data']['s_assigned_to'];
            foreach ($all_field_array as $field_lebel) {
                $export_header[] = $field_lebel;
            }
            if (count($did_data['result']) > 0) {
                foreach ($did_data['result'] as $did_data) {
                    $array_temp = array();

                    foreach ($all_field_array as $field_name => $field_lebel) {
                        if ($field_name == 'did_status') {
                            $status = ucfirst(strtolower($did_data[$field_name]));
                            $array_temp[] = $status;
                        } elseif ($field_name == 'reseller1_assign_date' || $field_name == 'reseller2_assign_date' || $field_name == 'reseller3_assign_date' || $field_name == 'create_date') {
                            $date_display = $did_data[$field_name];
                            if ($did_data[$field_name] != '') {
                                $date_timestamp = strtotime($did_data[$field_name]);
                                $date_display = date(DATE_FORMAT_1, $date_timestamp);
                            }
                            $array_temp[] = $date_display;
                        } elseif ($field_name == 'dst_type' || $field_name == 'dst_destination') {
                            if (isset($did_data['did_dst'][$field_name]))
                                $array_temp[] = $did_data['did_dst'][$field_name];
                            else
                                $array_temp[] = '';
                        } else
                            $array_temp[] = $did_data[$field_name];
                    }
                    $export_data[] = $array_temp;
                }
            } else
                $export_data[] = array();

            $file_name = 'IncomingNumbers' . rand(100, 200);

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);

            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        if ($is_file_downloaded === false) {
            /*             * **** pagination code start here ********* */
            $pagination_uri_segment = 3;

            if (isset($_SESSION['search_did_data']['s_no_of_records']) && $_SESSION['search_did_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_did_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }

            $option_param = array('did_dst' => true);
            $did_data = Array();
            $did_data = $this->did_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            $data['total_records'] = $total = $this->did_mod->get_data_total_count();

            $config = array();
            $config = $this->utils_model->setup_pagination_option($total, 'dids/index', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);

            /*             * **** pagination code ends  here ********* */
            $data['pagination'] = $this->pagination->create_links();
            $data['did_data'] = $did_data;
            $data['all_field_array'] = $all_field_array;

            $this->load->view('basic/header', $data);
            //echo get_logged_account_type();
            if (check_logged_user_group('CUSTOMER')) {//if module installed
                $this->load->model('Customer_mod');
                $option_param = array('ip' => true, 'sipuser' => true, 'CAMPAINGN' => true);
                $search_data = array('account_id' => get_logged_account_id());
                $endusers_data = $this->Customer_mod->get_data('', 1, 0, $search_data, $option_param);
                $data['did_enduser'] = current($endusers_data['result']);

                $this->load->view('did/incoming_numbers_user', $data);
            } else {
                $this->load->view('did/incoming_numbers', $data);
            }

            $this->load->view('basic/footer', $data);
        }
    }

    
    
     public function upload() {
        $page_name = "did_add";
        $data['page_name'] = $page_name;
        if (!check_logged_user_group(array(ADMIN_ACCOUNT_ID)))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->model('carrier_mod');
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveDataBulk') {
            $this->form_validation->set_rules('carrier_id_bulk', 'Carrier', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $config['upload_path'] = 'uploads/';
                $config['allowed_types'] = 'csv';
                $config['file_name'] = 'DID_' . date('YmdHis');
                $config['file_ext_tolower'] = TRUE;
                $config['max_size'] = 0;
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('did_file')) {
                    $this->session->set_flashdata('err_msgs', $this->upload->display_errors());
                    redirect(base_url() . 'dids/add', 'location', '301');
                } else {
                    ini_set('memory_limit', '1024M');
                    $file_with_path = 'uploads/' . $config['file_name'] . '.csv';
                    $csv = array_map('str_getcsv', file($file_with_path));
                    unset($file_with_path);
                    $result = $this->did_mod->add_bulk($_POST['carrier_id_bulk'], $csv);
                    //echo '<pre>'; print_r($csv); print_r($result);echo '</pre>';die;
                    if ($result === true) {
                        //echo '<pre>'; print_r($_POST); echo '</pre>';die;					
                        $this->session->set_flashdata('suc_msgs', 'Incoming Number Added Successfully');

                        if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                            $action = trim($_POST['button_action']);
                            if ($action == 'save')
                                redirect(base_url() . 'dids/upload/', 'location', '301');
                            elseif ($action == 'save_close')
                                redirect(base_url() . 'dids', 'location', '301');
                        } else {
                            redirect(base_url() . 'dids', 'location', '301');
                        }

                        redirect(base_url() . 'dids/add', 'location', '301');
                        exit();
                    } else {
                        $err_msgs = $result;
                        $data['err_msgs'] = $err_msgs;
                    }
                }
            }
        }  
        $data['currency_options'] = $this->utils_model->get_currencies();

        $search_data = array();
        $data['carriers_data'] = $this->carrier_mod->get_data('carrier_name', '', '', $search_data);
        $this->load->view('basic/header', $data);
        $this->load->view('did/incoming_upload', $data);
        $this->load->view('basic/footer', $data);
    }
    
    public function add() {
        $page_name = "did_add";
        $data['page_name'] = $page_name;
        if (!check_logged_user_group(array(ADMIN_ACCOUNT_ID)))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->model('carrier_mod');
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveDataBulk') {
            $this->form_validation->set_rules('carrier_id_bulk', 'Carrier', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $config['upload_path'] = 'uploads/';
                $config['allowed_types'] = 'csv';
                $config['file_name'] = 'DID_' . date('YmdHis');
                $config['file_ext_tolower'] = TRUE;
                $config['max_size'] = 0;
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('did_file')) {
                    $this->session->set_flashdata('err_msgs', $this->upload->display_errors());
                    redirect(base_url() . 'dids/add', 'location', '301');
                } else {
                    ini_set('memory_limit', '1024M');
                    $file_with_path = 'uploads/' . $config['file_name'] . '.csv';
                    $csv = array_map('str_getcsv', file($file_with_path));
                    unset($file_with_path);
                    $result = $this->did_mod->add_bulk($_POST['carrier_id_bulk'], $csv);
                    //echo '<pre>'; print_r($csv); print_r($result);echo '</pre>';die;
                    if ($result === true) {
                        //echo '<pre>'; print_r($_POST); echo '</pre>';die;					
                        $this->session->set_flashdata('suc_msgs', 'Incoming Number Added Successfully');

                        if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                            $action = trim($_POST['button_action']);
                            if ($action == 'save')
                                redirect(base_url() . 'dids/add/', 'location', '301');
                            elseif ($action == 'save_close')
                                redirect(base_url() . 'dids', 'location', '301');
                        } else {
                            redirect(base_url() . 'dids', 'location', '301');
                        }

                        redirect(base_url() . 'dids/add', 'location', '301');
                        exit();
                    } else {
                        $err_msgs = $result;
                        $data['err_msgs'] = $err_msgs;
                    }
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('carrier_id', 'Carrier', 'trim|required');
            // $this->form_validation->set_rules('did_number', 'DID', 'trim|required|is_unique[did.did_number]', array('is_unique' => 'DID number is already available in the system. Please add the new DID which is not listed in the system'));
            $this->form_validation->set_rules('did_number', 'DID', 'trim|required');
            $this->form_validation->set_rules('destination', 'DID Name', 'trim|required');
            $this->form_validation->set_rules('setup_charge', 'Setup Charge', 'trim|required');
            $this->form_validation->set_rules('rental', 'Rental', 'trim|required');
            $this->form_validation->set_rules('rate', 'Call Rate', 'trim|required');
            $this->form_validation->set_rules('connection_charge', 'Connection Charge', 'trim|required');
            $this->form_validation->set_rules('minimal_time', 'Minimum Time', 'trim|required');
            $this->form_validation->set_rules('resolution_time', 'Resolution Time', 'trim|required');
            $this->form_validation->set_rules('channels', 'Channels', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['did_status'] = 'NEW';
                $result = $this->did_mod->add($_POST);
                //	echo '<pre>';print_r($_POST);var_dump($result);die;
                if ($result === true) {
                    $did_id = $this->did_mod->did_id;
                    $this->session->set_flashdata('suc_msgs', 'Incoming Number Added Successfully');

                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'dids/edit/' . param_encrypt($did_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'dids', 'location', '301');
                    } else {
                        redirect(base_url() . 'dids', 'location', '301');
                    }

                    redirect(base_url() . 'dids/add', 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        $data['currency_options'] = $this->utils_model->get_currencies();

        $search_data = array();
        $data['carriers_data'] = $this->carrier_mod->get_data('carrier_name', '', '', $search_data);
        $this->load->view('basic/header', $data);
        $this->load->view('did/incoming_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function edit($id = -1) {
        $this->load->model('did_mod');
        if ($id == -1)
            show_404();
        $page_name = "did_edit";
        $data['page_name'] = $page_name;

        if (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
            $this->reseller_did_edit($id);
        } else {
            $this->admin_did_edit($id);
        }
    }

    public function reseller_did_edit($id) {
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->model('carrier_mod');

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $did_id = $_POST['did_id'];
            $data['did_id'] = $did_id;

            $this->form_validation->set_rules('did_id', 'DID', 'trim|required');

            /* non mandatory fields */
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->did_mod->update($_POST);
                //echo '<pre>';	print_r($_POST); print_r($result);die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'DID Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'dids/edit/' . param_encrypt($did_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'dids', 'location', '301');
                    } else {
                        redirect(base_url() . 'dids', 'location', '301');
                    }

                    redirect(base_url() . 'dids/edit/' . param_encrypt($did_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (!empty($id)) {
            $did_id = param_decrypt($id);
            //	echo 	$did_id;die;				
            $order_by = '';
            $per_page = 1;
            $segment = 0;

            $search_data = array(
                'did_id' => $did_id,
                'logged_account_type' => get_logged_account_type(),
                'logged_current_customer_id' => get_logged_account_id(),
                'logged_account_level' => get_logged_account_level(),
            );
            $did_data = $this->did_mod->get_data($order_by, $per_page, $segment, $search_data, array('carrier_rates' => true));
            if (isset($did_data['result']))
                $did_data = current($did_data['result']);
            else {
                show_404();
            }
        } else {
            show_404();
        }
        /*         * **** pagination code ends  here ********* */
        $data['did_data'] = $did_data;
        $data['did_id'] = $did_id;
        $did = $did_data['did_number'];
        //	echo '<pre>';print_r($data);echo '</pre>'; 
        //echo $did;die("eee");
        $data['did_rates_data'] = $this->did_mod->getDIDRates($did);

        //echo '<pre>';print_r($did_data);echo '</pre>';die;

        $data['currency_options'] = $this->utils_model->get_currencies();

        $search_data = array('carrier_type' => 'INBOUND');
        $data['carriers_data'] = $this->carrier_mod->get_data($order_by, '', '', $search_data);

        
        $this->load->view('basic/header', $data);
        $this->load->view('did/incoming_edit_reseller', $data);
        $this->load->view('basic/footer', $data);
    }

    public function admin_did_edit($id) {
        $data['page_name'] = "did_edit";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->model('carrier_mod');
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $did_id = $_POST['did_id'];
            $data['did_id'] = $did_id;
            $this->form_validation->set_rules('did_id', 'DID', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->did_mod->update($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'DID Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save') {
                            redirect(base_url() . 'dids/edit/' . param_encrypt($did_id), 'location', '301');
                        } elseif ($action == 'save_close') {
                            redirect(base_url() . 'dids', 'location', '301');
                        }
                    } else {
                        redirect(base_url() . 'dids', 'location', '301');
                    }
                    redirect(base_url() . 'dids/edit/' . param_encrypt($did_id), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (!empty($id)) {
            $did_id = param_decrypt($id);
            $order_by = '';
            $per_page = 1;
            $segment = 0;
            $search_data = array(
                'did_id' => $did_id,
                'logged_account_type' => get_logged_account_type(),
                'logged_current_customer_id' => get_logged_account_id(),
                'logged_account_level' => get_logged_account_level(),
            );
            $did_data = $this->did_mod->get_data($order_by, $per_page, $segment, $search_data, array('carrier_rates' => true));
            if (isset($did_data['result'])) {
                $did_data = current($did_data['result']);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        $data['did_data'] = $did_data;
        $data['did_id'] = $did_id;
        $data['currency_options'] = $this->utils_model->get_currencies();
        $search_data = array('carrier_type' => 'INBOUND');
        $data['carriers_data'] = $this->carrier_mod->get_data($order_by, '', '', $search_data);
        $this->load->view('basic/header', $data);
        $this->load->view('did/incoming_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    public function api_did() {
        $this->load->model('member_mod');
        $did = $this->input->post('did', TRUE);
        $area_specific = $this->input->post('area_specific', 'N');
        if ($area_specific == 'Y')
            $is_area_specific = true;
        else
            $is_area_specific = false;

        $return = $this->did_mod->getAvailableDID($did, $is_area_specific);
        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

    public function purchase_did() {
        $data['page_name'] = "did_purchase";
        if (check_logged_user_group(ADMIN_ACCOUNT_ID))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('didno', 'DID', 'trim|required|min_length[10]|max_length[15]');
            $this->form_validation->set_rules('setup_charge', 'Setup Charge', 'trim|required|numeric|greater_than_equal_to[0]');
            $this->form_validation->set_rules('rental_charge', 'Rental Charge', 'trim|required|numeric|greater_than_equal_to[0]');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->did_mod->assignment($_POST['didno'], $_POST['setup_charge'], $_POST['rental_charge']);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'DID purchased Successfully');
                    redirect(base_url() . 'dids', 'location', '301');
                } else {
                    $data['err_msgs'] = $result;
                }
            }
        }

        if (check_logged_user_group('CUSTOMER')) {//if module installed
            $this->load->model('Customer_mod');
            $option_param = array('ip' => true, 'sipuser' => true, 'CAMPAINGN' => true);
            $search_data = array('account_id' => get_logged_account_id());
            $endusers_data = $this->Customer_mod->get_data('', 1, 0, $search_data, $option_param);
            $data['did_enduser'] = current($endusers_data['result']);
        }
        $this->load->view('basic/header', $data);
        $this->load->view('did/purchase', $data);
        $this->load->view('basic/footer', $data);
    }

    public function purchase_did_bulk() {
        $data['page_name'] = "did_purchase";
        if (check_logged_user_group(ADMIN_ACCOUNT_ID))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkPurchaseDidBulk') {
            if (!isset($_SESSION['cart']['did']) || count($_SESSION['cart']['did']) == 0) {
                $this->session->set_flashdata('err_msgs', 'No DID in your cart');
                redirect(base_url('dids/purchase_did_bulk'), 'location', '301');
            }

            $this->form_validation->set_rules('action', 'Action', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $bulk_data = array_merge($_SESSION['cart'], $_POST);
                $result = $this->did_mod->assignment_bulk($bulk_data);

                if ($result === true) {
                    $msg = $this->did_mod->completed_did_purchase . ' DID(s) purchased Successfully.';
                    $this->session->set_flashdata('suc_msgs', $msg);
                    redirect(base_url() . 'dids', 'location', '301');
                } elseif (isset($this->did_mod->completed_did_purchase) && $this->did_mod->completed_did_purchase > 0) {
                    $msg = $this->did_mod->completed_did_purchase . ' DID(s) purchased Successfully.Please Try Again to Complete the remaining DID';
                    $this->session->set_flashdata('suc_msgs', $msg);
                    redirect(base_url() . 'dids', 'location', '301');
                } else {
                    $data['err_msgs'] = $result;
                }
            }
        }

        if (check_logged_user_group('CUSTOMER')) {
            $this->load->model('Customer_mod');
            $option_param = array('ip' => true, 'sipuser' => true, 'CAMPAINGN' => true);
            $search_data = array('account_id' => get_logged_account_id());
            $endusers_data = $this->Customer_mod->get_data('', 1, 0, $search_data, $option_param);
            $data['did_enduser'] = current($endusers_data['result']);
        }
        $this->load->view('basic/header', $data);
        $this->load->view('did/purchase_bulk', $data);
        $this->load->view('basic/footer', $data);
    }

    public function assign_did($account_id) {

        $data['page_name'] = "did_assign";

        //if(!check_account_permission('did','did_purchase')) show_404('403');
        if (check_logged_user_group(array('CUSTOMER')))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $data['account_id'] = $account_id;

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {

            $this->form_validation->set_rules('didno', 'DID', 'trim|required|min_length[10]|max_length[15]');
            $this->form_validation->set_rules('setup_charge', 'Setup Charge', 'trim|required|numeric|greater_than_equal_to[0]');
            $this->form_validation->set_rules('rental_charge', 'Rental Charge', 'trim|required|numeric|greater_than_equal_to[0]');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->did_mod->assignment($_POST['didno'], $_POST['setup_charge'], $_POST['rental_charge']);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'DID purchased Successfully');
                    redirect(base_url() . 'dids', 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }

        $this->load->view('basic/header', $data);
        $this->load->view('did/assign', $data);
        $this->load->view('basic/footer', $data);
    }

    public function config($id = -1) {
        if ($id == -1)
            show_404();



        if (check_logged_user_group(ADMIN_ACCOUNT_ID))
            show_404('403');

        $page_name = "did_edit";
        $data['page_name'] = $page_name;

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $this->load->model('carrier_mod');

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {

            if ($_POST['dst_type'] == 'IP')
                $this->form_validation->set_rules('dst_point_ip', 'Destination Endpoint', 'trim|required');
            elseif ($_POST['dst_type'] == 'CUSTOMER')
                $this->form_validation->set_rules('dst_point_sip', 'Destination Endpoint', 'trim|required');
            elseif ($_POST['dst_type'] == 'CAMPAINGN')
                $this->form_validation->set_rules('dst_campaign_id', 'Destination Endpoint', 'trim|required');
            else
                $this->form_validation->set_rules('dst_point_pstn', 'Destination Endpoint', 'trim|required');


            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->did_mod->destination($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'DID destination mapped successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'dids/config/' . $id, 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'dids', 'location', '301');
                    } else {
                        redirect(base_url() . 'dids', 'location', '301');
                    }

                    redirect(base_url() . 'dids/add', 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        ///////////////////////////		

        if (!empty($id)) {
            $did_id = param_decrypt($id);
            $order_by = '';
            $per_page = 1;
            $segment = 0;

            $search_data = array(
                'did_id' => $did_id,
                'logged_account_type' => get_logged_account_type(),
                'logged_current_customer_id' => get_logged_account_id(),
                'logged_account_level' => get_logged_account_level(),
            );
            $did_data = $this->did_mod->get_data($order_by, $per_page, $segment, $search_data, array('did_dst' => true));

            if (isset($did_data['result'])) {
                $did_data = current($did_data['result']);

                $this->load->model('Customer_mod');
                $option_param = array('ip' => true, 'sipuser' => true, 'CAMPAINGN' => true);
                $search_data = array('account_id' => get_logged_account_id());
                $endusers_data = $this->Customer_mod->get_data('', 1, 0, $search_data, $option_param);
                //var_dump($endusers_data);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
        /*         * **** pagination code ends  here ********* */
        $data['did_data'] = $did_data;
        $data['did_enduser'] = current($endusers_data['result']);

        $this->load->view('basic/header', $data);
        $this->load->view('did/incoming_config', $data);
        $this->load->view('basic/footer', $data);
    }

    public function api_add_to_cart() {
        if (!isset($_SESSION['cart']['did']))
            $_SESSION['cart']['did'] = array();
        if (isset($_POST['did_numbers']) && count($_POST['did_numbers']) > 0) {
            $_SESSION['cart']['did'] = array_merge($_SESSION['cart']['did'], $_POST['did_numbers']);
            $_SESSION['cart']['did'] = array_unique($_SESSION['cart']['did']);

            $return = array('status' => true, 'total_cart_did' => count($_SESSION['cart']['did']));
        } else
            $return = array('status' => false, 'total_cart_did' => count($_SESSION['cart']['did']));


        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

    public function ajax_cart() {
        if (!isset($_SESSION['cart']['did']) || count($_SESSION['cart']['did']) == 0) {
            
        }
        $str = '';
        if (isset($_SESSION['cart']['did']) && count($_SESSION['cart']['did']) > 0) {
            $str .= '<div class="col-md-12 col-sm-12 col-xs-12 text-right">
                            <input type="button" value="Remove All DID(s)" id="id_empty_cart" class="btn btn-warning btn-xs" onclick="DoEmptyCart()"/>    
                        </div><div class="clearfix"></div> ';
        }
        $str .= '<table class="table table table-bordered table-condensed" id="id_table_cart">';

        if (isset($_SESSION['cart']['did']) && count($_SESSION['cart']['did']) > 0) {
            $i = 1;
            $current = current($_SESSION['cart']['did']);
            while (1) {
                $did1 = $current;
                $did2 = next($_SESSION['cart']['did']);
                $str .= '<tr>';
                $str .= '<td id="' . $did1 . '">' . $did1 . '&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick=doDeleteCartDID(\'' . $did1 . '\') title="Remove"><i class="fa fa-trash"></i></a></td>';
                if ($did2 === false) {
                    $str .= '<td ></td >';
                } else {
                    $str .= '<td id="' . $did2 . '">' . $did2 . '&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick=doDeleteCartDID(\'' . $did2 . '\') title="Remove"><i class="fa fa-trash"></i></td>';
                }
                $str .= '</tr>';

                if ($did2 === false)
                    break;

                $i++;

                $current = next($_SESSION['cart']['did']);
                if ($current === false)
                    break;
            }
        } else {
            $str .= '<tr><td align="center">Cart is Empty</td></tr>';
        }

        $str .= '</table>';

        $str .= '<div class="clearfix"></div>';
        if (isset($_SESSION['cart']['did']) && count($_SESSION['cart']['did']) > 0) {
            $str .= '<div class="col-md-12 col-sm-12 col-xs-12 text-right">
                            <input type="button" value="Remove All DID(s)" id="id_empty_cart" class="btn btn-warning btn-xs" onclick="DoEmptyCart()"/>    
                        </div><div class="clearfix"></div> ';
        }



        echo $str;
    }

    public function api_remove_cart_did() {
        if (isset($_POST['action']) && $_POST['action'] == 'OkRemoveDid') {
            $did = $_POST['did'];
            if (isset($_SESSION['cart']['did'])) {
                $did_key = array_search($did, $_SESSION['cart']['did']);
                if ($did_key !== false)
                    unset($_SESSION['cart']['did'][$did_key]);
            }

            $return = array('status' => true, 'total_cart_did' => count($_SESSION['cart']['did']));
            $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($return));
        } elseif (isset($_POST['action']) && $_POST['action'] == 'OkRemoveAllDid') {
            if (isset($_SESSION['cart']['did'])) {
                $_SESSION['cart']['did'] = array();
            }

            $return = array('status' => true, 'total_cart_did' => count($_SESSION['cart']['did']));
            $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($return));
        }
    }

    public function cart() {
        $data['page_name'] = "did_cart";
        if (check_logged_user_group(ADMIN_ACCOUNT_ID))
            show_404('403');

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {

            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                if (isset($_SESSION['cart']['did'])) {
                    foreach ($delete_id_array as $did) {
                        $did_key = array_search($did, $_SESSION['cart']['did']);
                        if ($did_key !== false)
                            unset($_SESSION['cart']['did'][$did_key]);
                    }
                }
                $this->session->set_flashdata('suc_msgs', 'DID Removed From Cart Successfully');
            }

            redirect(base_url() . 'dids/cart', 'location', '301');
        }

        if (isset($_POST['action']) && $_POST['action'] == 'OkPurchaseDidBulk') {


            if (!isset($_SESSION['cart']['did']) || count($_SESSION['cart']['did']) == 0) {
                $this->session->set_flashdata('err_msgs', 'No DID in your cart');
                redirect(base_url('dids/purchase_did_bulk'), 'location', '301');
            }

            $this->form_validation->set_rules('action', 'Action', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $bulk_data = array_merge($_SESSION['cart'], $_POST);
                $result = $this->did_mod->assignment_bulk($bulk_data);
                if ($result === true) {
                    $msg = $this->did_mod->completed_did_purchase . ' DID(s) purchased Successfully.';
                    $this->session->set_flashdata('suc_msgs', $msg);
                    redirect(base_url() . 'dids', 'location', '301');
                } elseif (isset($this->did_mod->completed_did_purchase) && $this->did_mod->completed_did_purchase > 0) {
                    $msg = $this->did_mod->completed_did_purchase . ' DID(s) purchased Successfully.Please Try Again to Complete the remaining DID';
                    $this->session->set_flashdata('suc_msgs', $msg);
                    redirect(base_url() . 'dids', 'location', '301');
                } else {
                    $data['err_msgs'] = $result;
                }
            }
        }

        if (check_logged_user_group('CUSTOMER')) {//if module installed
            $this->load->model('Customer_mod');
            $option_param = array('ip' => true, 'sipuser' => true, 'CAMPAINGN' => true);
            $search_data = array('account_id' => get_logged_account_id());
            $endusers_data = $this->Customer_mod->get_data('', 1, 0, $search_data, $option_param);
            $data['did_enduser'] = current($endusers_data['result']);
        }

        if (isset($_SESSION['cart']['did']) && count($_SESSION['cart']['did']) > 0) {
            $did_data = $this->did_mod->get_cart_dids();
            $data['did_cart'] = $did_data;
        }



        $this->load->view('basic/header', $data);
        $this->load->view('did/cart', $data);
        $this->load->view('basic/footer', $data);
    }

}
