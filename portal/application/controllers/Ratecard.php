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

class Ratecard extends CI_Controller {

    public $search_serialize = '';

    function __construct() {
        parent::__construct();

        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('ratecard_mod');
        $this->load->model('Utils_model');
        $this->output->enable_profiler(ENABLE_PROFILE);
    }

    public function index($arg1 = '', $format = '') {
        $data = Array();
        $page_name = "ratecard_index";
        $file_name = 'Ratecard_' . date('Ymd');
        $is_file_downloaded = false;
        if (!check_account_permission('ratecard', 'view'))
            show_404('403');
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkDeleteData') {
            if (!check_account_permission('ratecard', 'delete')) {
                $this->session->set_flashdata('err_msgs', 'Dont have enough permission');
                redirect(base_url() . 'ratecard', 'location', '301');
            }
            $delete_id_array = json_decode($_POST['delete_id']);
            if (isset($_POST['delete_id']) && count($delete_id_array) > 0) {
                $delete_param_array = array('delete_id' => $delete_id_array);
                $result = $this->ratecard_mod->delete($delete_param_array);
                if ($result['status'] === true) {
                    $suc_msgs = count($delete_id_array) . ' Ratecard';
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
            redirect(base_url() . 'ratecard', 'location', '301');
        }
        $data['currency_data'] = $this->Utils_model->get_currencies();
        $search_data = array();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_ratecard_data'] = array(
                's_ratecard_name' => $_POST['name'],
                's_ratecard_id' => $_POST['abbr'],
                's_ratecard_currency' => $_POST['currency'],
                's_ratecard_type' => $_POST['type'],
                's_no_of_records' => $_POST['no_of_rows']
            );
        } else {
            $r = $this->uri->segment(2);
            if ($r == '') {
                $_SESSION['search_ratecard_data']['s_ratecard_name'] = isset($_SESSION['search_ratecard_data']['s_ratecard_name']) ? $_SESSION['search_ratecard_data']['s_ratecard_name'] : '';
                $_SESSION['search_ratecard_data']['s_ratecard_id'] = isset($_SESSION['search_ratecard_data']['s_ratecard_id']) ? $_SESSION['search_ratecard_data']['s_ratecard_id'] : '';
                $_SESSION['search_ratecard_data']['s_ratecard_currency'] = isset($_SESSION['search_ratecard_data']['s_ratecard_currency']) ? $_SESSION['search_ratecard_data']['s_ratecard_currency'] : '';
                $_SESSION['search_ratecard_data']['s_ratecard_type'] = isset($_SESSION['search_ratecard_data']['s_ratecard_type']) ? $_SESSION['search_ratecard_data']['s_ratecard_type'] : '';
                $_SESSION['search_ratecard_data']['s_no_of_records'] = isset($_SESSION['search_ratecard_data']['s_no_of_records']) ? $_SESSION['search_ratecard_data']['s_no_of_records'] : RECORDS_PER_PAGE;
            }
        }

        $search_data = array(
            'ratecard_name' => $_SESSION['search_ratecard_data']['s_ratecard_name'],
            'ratecard_id' => $_SESSION['search_ratecard_data']['s_ratecard_id'],
            'ratecard_currency_id' => $_SESSION['search_ratecard_data']['s_ratecard_currency'],
            'ratecard_type' => $_SESSION['search_ratecard_data']['s_ratecard_type'],
            'logged_account_type' => get_logged_account_type(),
            'logged_current_customer_id' => get_logged_account_id(),
            'logged_account_level' => get_logged_account_level(),
        );

               
        $order_by =  array('id'=>'DESC');
        if ($arg1 == 'export' && $format != '') {
            $this->load->library('Export');
            $format = param_decrypt($format);
            $option_param = array('tariff' => true);
            $response_data = $this->ratecard_mod->get_data($order_by, '', '', $search_data, $option_param);
            $export_header = array('Name', 'Currency');

            if ($response_data['total'] > 0) {
                foreach ($response_data['result'] as $row) {
                    $export_data[] = array($row['ratecard_name'], $row['currency_abbr']);
                }
            } else {
                $export_data = array('');
            }
            $downloaded_message = $this->export->download($file_name, $format, $search_data, $export_header, $export_data);
            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = $this->uri->segment(3, 0);
            if (isset($_SESSION['search_ratecard_data']['s_no_of_records']) && $_SESSION['search_ratecard_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_ratecard_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            $response = $this->ratecard_mod->get_data($order_by, $pagination_uri_segment, $per_page, $search_data);
            $config = array();
            $config = $this->utils_model->setup_pagination_option($response['total'], 'ratecard/index', $per_page, 3);
            $this->pagination->initialize($config);
            $data['page_name'] = $page_name;
            $data['pagination'] = $this->pagination->create_links();
            $data['listing_data'] = $response['result'];
            $data['total_records'] = $data['listing_count'] = $response['total'];
            $this->load->view('basic/header', $data);
            $this->load->view('rates/ratecard', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function addRC() {
        $data['page_name'] = "ratecard_add";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('ratecard', 'add'))
            show_404('403');

        $data['currency_data'] = $this->Utils_model->get_currencies();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_name', 'Name', 'trim|required|min_length[5]|max_length[30]');
            $this->form_validation->set_rules('frm_currency', 'Currency', 'trim|min_length[0]|max_length[10]');
            $this->form_validation->set_rules('ratecard_for', 'Ratecard For', 'trim|required');
			if ($_POST['frm_type'] == 'CARRIER')
                $_POST['frm_type'] = 'CARRIER';
            else
                $_POST['frm_type'] = 'CUSTOMER';
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->ratecard_mod->add($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Ratecard Added Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'ratecard/editRC/' . param_encrypt($result['id']), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'ratecard', 'location', '301');
                    } else {
                        redirect(base_url() . 'ratecard', 'location', '301');
                    }
                    redirect(base_url() . 'ratecard/editRC/' . param_encrypt($ratecard_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }
        $this->load->view('basic/header', $data);
        $this->load->view('rates/ratecard_add', $data);
        $this->load->view('basic/footer', $data);
    }

    public function editRC() {
        $data['page_name'] = "ratecard_edit";
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_account_permission('ratecard', 'edit'))
            show_404('403');
        $data['currency_data'] = $this->Utils_model->get_currencies();
        $ratecard_id = param_decrypt($this->uri->segment(3));
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('frm_id', 'Ratecard ID', 'trim|required');
            $this->form_validation->set_rules('frm_name', 'Name', 'trim|required|min_length[5]|max_length[30]');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $result = $this->ratecard_mod->update($_POST);
                if ($result['status']) {
                    $this->session->set_flashdata('suc_msgs', 'Ratecard Updated Successfully');
                    if (isset($_POST['button_action']) && trim($_POST['button_action']) != '') {
                        $action = trim($_POST['button_action']);
                        if ($action == 'save')
                            redirect(base_url() . 'ratecard/editRC/' . param_encrypt($ratecard_id), 'location', '301');
                        elseif ($action == 'save_close')
                            redirect(base_url() . 'ratecard', 'location', '301');
                    } else {
                        redirect(base_url() . 'ratecard', 'location', '301');
                    }
                    redirect(base_url() . 'ratecard/editRC/' . param_encrypt($ratecard_id), 'location', '301');
                } else {
                    $data['err_msgs'] = $result['msg'];
                }
            }
        }

        if (isset($_POST['action']) && $_POST['action'] == 'OkUploadData') {
            $uploadType = $this->input->post("uploadType");
            $this->form_validation->set_rules('frm_id', 'Ratecard ID', 'trim|required');
            $this->form_validation->set_rules('uploadType', 'Upload Type', 'trim|required|in_list[U,C]');
            $this->form_validation->set_rules('frm_action_rate', 'Rates', 'trim|required|in_list[1,2,3,4]|callback_RateValue');
            $this->form_validation->set_rules('frm_action_connect', 'Connection', 'trim|required|in_list[1,2,3,4]|callback_ConnectValue');
            $this->form_validation->set_rules('frm_action_min', 'Minimal Time', 'trim|required|in_list[1,2,3,4]|callback_MinValue');
            $this->form_validation->set_rules('frm_action_res', 'Resolution Time', 'trim|required|in_list[1,2,3,4]|callback_ResValue');
            $this->form_validation->set_rules('frm_val_rate', 'Rate parameter', 'trim');
            $this->form_validation->set_rules('frm_val_min', 'Minimal parameter', 'trim');
            $this->form_validation->set_rules('frm_val_res', 'Resolution parameter', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = $this->session->set_flashdata('err_msgs', validation_errors());
            } else {
                if ($uploadType == 'U') {
                    $config['upload_path'] = './uploads/';
                    $config['allowed_types'] = 'csv';
                    $config['file_name'] = 'CARD_' . date('YmdHis');
                    $config['file_ext_tolower'] = TRUE;
                    $config['max_size'] = 0;
                    $this->load->library('upload', $config);

                    if (!$this->upload->do_upload('file')) {
                        $this->session->set_flashdata('err_msgs', $this->upload->display_errors());
                        redirect(base_url() . 'ratecard/editRC/' . param_encrypt($ratecard_id), 'location', '301');
                    } else {
                        ini_set('memory_limit', '2048M');
                        $data = $this->upload->data();
                        $file_with_path = './uploads/' . $data['file_name'];
                        $file = fopen($file_with_path, "r");
                        $cnt = 0;
                        $error = 0;
                        $error_msg = '';
                        $csv_data = array();
                        while (!feof($file)) {
                            $d = fgetcsv($file);
                            $csv_data[] = $d;
                            if ($cnt > 0 && is_array($d)) {
                                $error_type = '';
                                $pref = trim($d[0]);
                                if (!preg_match('/^\d{1,12}$/', $pref)) {
                                    $error++;
                                    $error_type .= 'Prefix (' . $pref . ')';
                                }
                                $dest = trim($d[1]);
                                if (!preg_match('/^[a-z0-9 \/ \-()&\.]+$/i', $dest)) {
                                    $error++;
                                    $error_type .= 'Destination (' . $dest . ')';
                                }
                                $ppm = trim($d[2]);
                                if (!preg_match('/^[0-9]+(\.[0-9]{1,6})?$/', $ppm)) {
                                    $error++;
                                    $error_type .= 'PPM';
                                }
                                $ppc = trim($d[3]);
                                if (!preg_match('/^[0-9]+(\.[0-9]{1,6})?$/', $ppc)) {
                                    $error++;
                                    $error_type .= 'PPC';
                                }
                                $min = trim($d[4]);
                                if (!ctype_digit($min)) {
                                    $error++;
                                    $error_type .= 'Minimal';
                                }
                                $res = trim($d[5]);
                                if (!ctype_digit($res)) {
                                    $error++;
                                    $error_type .= 'Resolution';
                                }
                                $grace = trim($d[6]);
                                if (!ctype_digit($grace)) {
                                    $error++;
                                    $error_type .= 'Grace';
                                }
                                $mul = trim($d[7]);
                                if (!preg_match('/^[0-9]+(\.[0-9]{1,6})?$/', $mul)) /* if(!ctype_digit($mul)) */ {
                                    $error++;
                                    $error_type .= 'Multiply';
                                }
                                $add = trim($d[8]);
                                if (!ctype_digit($add)) {
                                    $error++;
                                    $error_type .= 'Addition';
                                }

                                $stat = trim($d[9]);
                                if (!preg_match('/(0|1)/', $stat)) {
                                    $error++;
                                    $error_type .= 'Status';
                                }

                                if ($_POST['frm_ratecard_for'] == 'INCOMING') {
                                    $inclusive_channel = trim($d[10]);
                                    if (!ctype_digit($inclusive_channel)) {
                                        $error++;
                                        $error_type .= 'Inclusive Channel';
                                    }
                                    $exclusive_per_channel_rental = trim($d[11]);
                                    if (!preg_match('/^[0-9]+(\.[0-9]{1,6})?$/', $exclusive_per_channel_rental)) {
                                        $error++;
                                        $error_type .= 'Exclusive Per Channel Rental';
                                    } //decimal		


                                    $rental = trim($d[12]);
                                    if (!preg_match('/^[0-9]+(\.[0-9]{1,6})?$/', $rental)) {
                                        $error++;
                                        $error_type .= 'Rental';
                                    } //decimal		
                                    $setup_charge = trim($d[13]);
                                    if (!preg_match('/^[0-9]+(\.[0-9]{1,6})?$/', $setup_charge)) {
                                        $error++;
                                        $error_type .= 'Setup Charge';
                                    } //decimal		
                                }
                                if ($error) {
                                    $lineno = $cnt + 1;
                                    $error_msg = 'Error in Line no. ' . $lineno . ' - column [' . $error_type . ']'; /* echo 'Error in '.$cnt.' ['.$error_type.']'; */ break;
                                } else {
                                    //echo $pref.'|'.$dest.'|'.$ppm.'|'.$ppc.'|'.$min.'|'.$res.'|'.$grace.'|'.$mul.'|'.$add.'|'.$stat.'<br>';
                                }
                            }

                            ++$cnt;
                        }
                        //echo '<pre>';print_r($csv_data);	echo '</pre>';die;
                        fclose($file);
                        unlink($file_with_path);

                        if ($error) {
                            $this->session->set_flashdata('err_msgs', $error_msg);
                            redirect(base_url() . 'ratecard/editRC/' . param_encrypt($ratecard_id), 'location', '301');
                        } else {
                            $this->load->model('rate_mod');
                            unset($csv_data[0]);
                            $result = $this->rate_mod->bulkRates($_POST, $csv_data);
                            if ($result['status']) {
                                $this->session->set_flashdata('suc_msgs', 'Ratecard Updated Successfully');
                                redirect(base_url() . 'ratecard/editRC/' . param_encrypt($ratecard_id), 'location', '301');
                            } else {
                                $this->session->set_flashdata('err_msgs', $result['msg']);
                                redirect(base_url() . 'ratecard/editRC/' . param_encrypt($ratecard_id), 'location', '301');
                            }
                        }
                    }
                } else {
                    $this->load->model('rate_mod');
                    $result = $this->rate_mod->copyRates($_POST);
                    if ($result['status']) {
                        $this->session->set_flashdata('suc_msgs', 'Ratecard Updated Successfully');
                        redirect(base_url() . 'ratecard/editRC/' . param_encrypt($ratecard_id), 'location', '301');
                    } else {
                        $data['err_msgs'] = $result['msg'];
                    }
                }
            }
        }

        $show_404 = false;
        if (strlen($ratecard_id) > 0) {
            $search_data = array('ratecard_id' => $ratecard_id);
            $response_data = $this->ratecard_mod->get_data('', 0, RECORDS_PER_PAGE, $search_data, array());
            if ($response_data['total'] > 0) {
                $data['data'] = $response_data['result'][0];
                $this->load->model('tariff_mod');
                $ratecard_response_data = $this->tariff_mod->get_mapping('', 0, RECORDS_PER_PAGE, array('ratecard_id' => $response_data['result'][0]['ratecard_id']), array());
                $data['data_tariff'] = $ratecard_response_data['result'];
                $this->load->model('ratecard_mod');
                $data['ratecard_data'] = $this->ratecard_mod->get_data('', 0, '', array('ratecard_currency_id' => $data['data']["ratecard_currency_id"], 'ratecard_for' => $data['data']['ratecard_for']), array());
            } else
                $show_404 = true;
        } else
            $show_404 = true;

        $data['ratecard_id'] = $ratecard_id;
        $this->load->view('basic/header', $data);
        if ($show_404)
            $this->load->view('basic/404', $data);
        else
            $this->load->view('rates/ratecard_edit', $data);
        $this->load->view('basic/footer', $data);
    }

    function RateValue($d) {

        $data = trim($this->input->post("frm_val_rate"));
        if ($d == 2 && ($data == '' || $data == 0 || $data < 0)) {
            $this->form_validation->set_message('RateValue', 'Multiply cannot be BLANK or ZERO or NEGATIVE');
            return FALSE;
        } elseif ($d == 3 && ($data == '' || $data < 0)) {
            $this->form_validation->set_message('RateValue', 'Addition cannot be BLANK or NEGATIVE');
            return FALSE;
        } elseif ($d == 4 && ($data == '' || $data < 0)) {
            $this->form_validation->set_message('RateValue', 'Replace cannot be BLANK or NEGATIVE');
            return FALSE;
        }
        return TRUE;
    }

    function ConnectValue($d) {
        $data = trim($this->input->post("frm_val_connect"));
        if ($d == 2 && ($data == '' || $data == 0 || $data < 0)) {
            $this->form_validation->set_message('ConnectValue', 'Multiply cannot be BLANK or ZERO or NEGATIVE');
            return FALSE;
        } elseif ($d == 3 && ($data == '' || $data < 0)) {
            $this->form_validation->set_message('ConnectValue', 'Addition cannot be BLANK or NEGATIVE');
            return FALSE;
        } elseif ($d == 4 && ($data == '' || $data < 0)) {
            $this->form_validation->set_message('ConnectValue', 'Replace cannot be BLANK or NEGATIVE');
            return FALSE;
        }
        return TRUE;
    }

    function MinValue($d) {
        $data = trim($this->input->post("frm_val_min"));
        if ($d == 2 && ($data == '' || $data == 0 || $data < 0)) {
            $this->form_validation->set_message('MinValue', 'Multiply cannot be BLANK or ZERO or NEGATIVE');
            return FALSE;
        } elseif ($d == 3 && ($data == '' || $data < 0)) {
            $this->form_validation->set_message('MinValue', 'Addition cannot be BLANK or NEGATIVE');
            return FALSE;
        } elseif ($d == 4 && ($data == '' || $data < 0)) {
            $this->form_validation->set_message('MinValue', 'Replace cannot be BLANK or NEGATIVE');
            return FALSE;
        }
        return TRUE;
    }

    function ResValue($d) {
        $data = trim($this->input->post("frm_val_res"));
        if ($d == 2 && ($data == '' || $data == 0 || $data < 0)) {
            $this->form_validation->set_message('ResValue', 'Multiply cannot be BLANK or ZERO or NEGATIVE');
            return FALSE;
        } elseif ($d == 3 && ($data == '' || $data < 0)) {
            $this->form_validation->set_message('ResValue', 'Addition cannot be BLANK or NEGATIVE');
            return FALSE;
        } elseif ($d == 4 && ($data == '' || $data < 0)) {
            $this->form_validation->set_message('ResValue', 'Replace cannot be BLANK or NEGATIVE');
            return FALSE;
        }
        return TRUE;
    }

}
