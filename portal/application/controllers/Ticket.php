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

class Ticket extends MY_Controller {

    function __construct() {
        parent::__construct();

        $this->load->library('pagination'); // pagination class			
        $this->form_validation->set_error_delimiters('', '');
        //permission check		
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');

        $this->load->model('ticket_mod');
        $this->load->helper('common_helper');
    }

    public function index($id = -1, $arg1 = '', $format = '') {
        $page_name = "ticket_index";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $logged_account_id = get_logged_account_id();


        if (isset($_POST['search_action'])) {// coming from search button
            $_SESSION['search_t_data'] = array(
                's_ticket_number' => trim($_POST['ticket_number']),
                's_status' => trim($_POST['status']),
                's_subject' => trim($_POST['subject']),
                's_no_of_records' => $_POST['no_of_rows'],
                's_create_dt' => $_POST['create_dt']
            );

            if (!check_logged_user_group(array('CUSTOMER', 'RESELLER'))) {
                $_SESSION['search_t_data']['s_account_id'] = trim($_POST['account_id']);
                $_SESSION['search_t_data']['s_category_id'] = trim($_POST['category_id']);
                $_SESSION['search_t_data']['s_assigned_to_id'] = trim($_POST['assigned_to_id']);
            }

            //
        } else {
            $_SESSION['search_t_data']['s_ticket_number'] = isset($_SESSION['search_t_data']['s_ticket_number']) ? $_SESSION['search_t_data']['s_ticket_number'] : '';
            $_SESSION['search_t_data']['s_status'] = isset($_SESSION['search_t_data']['s_status']) ? $_SESSION['search_t_data']['s_status'] : '';
            $_SESSION['search_t_data']['s_subject'] = isset($_SESSION['search_t_data']['s_subject']) ? $_SESSION['search_t_data']['s_subject'] : '';
            $_SESSION['search_t_data']['s_create_dt'] = isset($_SESSION['search_t_data']['s_create_dt']) ? $_SESSION['search_t_data']['s_create_dt'] : '';


            //if account id passed through url, else take session value
            if ($id != -1 && is_numeric($id) != 'integer') {
                $s_account_id = param_decrypt($id);
                $_SESSION['search_t_data']['s_account_id'] = $s_account_id;
            } else {
                $_SESSION['search_t_data']['s_account_id'] = isset($_SESSION['search_t_data']['s_account_id']) ? $_SESSION['search_t_data']['s_account_id'] : '';
            }


            $_SESSION['search_t_data']['s_category_id'] = isset($_SESSION['search_t_data']['s_category_id']) ? $_SESSION['search_t_data']['s_category_id'] : '';
            $_SESSION['search_t_data']['s_assigned_to_id'] = isset($_SESSION['search_t_data']['s_assigned_to_id']) ? $_SESSION['search_t_data']['s_assigned_to_id'] : '';

            if (isset($_SESSION['search_t_data']['s_no_of_records']) && $_SESSION['search_t_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_t_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;

            $_SESSION['search_t_data']['s_no_of_records'] = $per_page;
        }




        if (check_logged_user_group(array('CUSTOMER', 'RESELLER'))) {
            $view_page = 'listing_user';
            $search_data = array('ticket_number' => $_SESSION['search_t_data']['s_ticket_number'], 'status' => $_SESSION['search_t_data']['s_status'], 'account_id' => $logged_account_id, 'parent_id' => '0');
            $option_param = array('category' => true, 'last_post' => true, 'total_post' => true);
        } else {
            $view_page = 'listing_admin';

            if (isset($_POST['assigned_to_me']) && $_POST['assigned_to_me'] == 'Y')
                $_SESSION['search_t_data']['s_assigned_to_id'] = 'myself';



            ///////////////////////
            if ($_SESSION['search_t_data']['s_create_dt'] == '') {
                $to = date('Y-m-d 23:59:59');
                $to_timestamp = strtotime($to);
                $from_timestamp = $to_timestamp - 7 * 24 * 60 * 60;
                $from = date('Y-m-d 00:00:00', $from_timestamp);
                $date_range = $from . ' - ' . $to;
                $_SESSION['search_t_data']['s_create_dt'] = $date_range;
            }
            ////////////////////////		




            $search_data = array(
                'parent_id' => '0',
                'ticket_number' => $_SESSION['search_t_data']['s_ticket_number'],
                'status' => $_SESSION['search_t_data']['s_status'],
                'account_id' => $_SESSION['search_t_data']['s_account_id'],
                'category_id' => $_SESSION['search_t_data']['s_category_id'],
                'assigned_to_id' => $_SESSION['search_t_data']['s_assigned_to_id'],
                'subject' => $_SESSION['search_t_data']['s_subject'],
                'create_date' => $_SESSION['search_t_data']['s_create_dt'],
                'logged_account_id' => $logged_account_id,
            );


            $data['category_data'] = $this->ticket_mod->get_categories();
            $data['assignto_data'] = $this->ticket_mod->get_assignto();
            $option_param = array('category' => true, 'assigned_to' => true, 'last_post' => true, 'total_post' => true);
        }

        //----------------Export Start---------------------------

        $is_file_downloaded = false;
        if ($arg1 == 'export' && $format != '') {
            $format = param_decrypt($format);
            $order_by = 'create_date DESC';
            $tickets_data = $this->ticket_mod->get_data($order_by, '', '', $search_data, $option_param);

            if (isset($data['category_data']['result']) && count($data['category_data']['result']) > 0) {
                foreach ($data['category_data']['result'] as $key => $parent_category_array) {
                    if (isset($parent_category_array['sub']) && count($parent_category_array['sub']) > 0) {
                        foreach ($parent_category_array['sub'] as $key => $category_array) {
                            if ($_SESSION['search_t_data']['s_category_id'] == $category_array['category_id'])
                                $category_name = $category_array['category_name'];
                        }
                    }
                    else {
                        if ($_SESSION['search_t_data']['s_category_id'] == $parent_category_array['category_id'])
                            $category_name = $parent_category_array['category_name'];
                    }
                }
            }

            if (isset($data['assignto_data']['result']) && count($data['assignto_data']['result']) > 0) {
                foreach ($data['assignto_data']['result'] as $key => $assignto_array) {
                    if ($_SESSION['search_t_data']['s_assigned_to_id'] == $assignto_array['assigned_to_id'])
                        $assign_to_name = $assignto_array['assigned_to_name'];
                }
            }

            //prepare search data
            $search_array = array();

            if ($_SESSION['search_t_data']['s_ticket_number'] != '')
                $search_array['Ticket No'] = $_SESSION['search_t_data']['s_ticket_number'];
            if ($_SESSION['search_t_data']['s_status'] != '')
                $search_array['Status'] = $_SESSION['search_t_data']['s_status'];
            if ($_SESSION['search_t_data']['s_account_id'] != '')
                $search_array['Account ID'] = $_SESSION['search_t_data']['s_account_id'];
            if ($_SESSION['search_t_data']['s_category_id'] != '')
                $search_array['Category'] = $category_name;
            if ($_SESSION['search_t_data']['s_assigned_to_id'] != '')
                $search_array['Assigned To'] = $assign_to_name;
            if ($_SESSION['search_t_data']['s_subject'] != '')
                $search_array['Subject'] = $_SESSION['search_t_data']['s_subject'];
            if ($_SESSION['search_t_data']['s_create_dt'] != '') {
                $range = explode(' - ', $_SESSION['search_t_data']['s_create_dt']);
                $start_dt = $range[0];
                $end_dt = $range[1];
                $search_array['Create Date'] = $start_dt . ' To ' . $end_dt;
            }


            // column titles		
            $export_header = array('Ticket No', 'Date Created', 'Date Closed', 'Account ID', 'Status', 'Category', 'Assigned To', 'Subject');

            if (count($tickets_data['result']) > 0) {

                foreach ($tickets_data['result'] as $ticket_data_temp) {

                    $create_dt = date('d-M-Y', strtotime($ticket_data_temp['create_date']));

                    if (!is_null($ticket_data_temp['close_date']))
                        $close_dt = date('d-M-Y H:i', strtotime($ticket_data_temp['close_date']));
                    else
                        $close_dt = '-';

                    if ($ticket_data_temp['status'] == 'open')
                        $ex_status = 'Open';
                    elseif ($ticket_data_temp['status'] == 'closed')
                        $ex_status = 'Closed';
                    elseif ($ticket_data_temp['status'] == 'assigned')
                        $ex_status = 'Assigned';
                    elseif ($ticket_data_temp['status'] == 'working')
                        $ex_status = 'Working';
                    elseif ($ticket_data_temp['status'] == 'waiting-confirmation')
                        $ex_status = 'Waiting Confirmation';
                    elseif ($ticket_data_temp['status'] == 'not-fixed')
                        $ex_status = 'Not Fixed';

                    $category = $ticket_data_temp['category']['category_name'];
                    $assign_to = $ticket_data_temp['assigned_to']['assigned_to_name'];
                    $subject = $ticket_data_temp['subject'];


                    $export_data[] = array($ticket_data_temp['ticket_number'], $create_dt, $close_dt, $ticket_data_temp['account_id'], $ex_status, $category, $assign_to, $subject);
                }
            } else
                $export_data = array('');

            $file_name = 'tickets_' . date('YmdHis');

            $this->load->library('Export');
            $downloaded_message = $this->export->download($file_name, $format, $search_array, $export_header, $export_data);


            if (gettype($downloaded_message) == 'string')
                $data['err_msgs'] = $downloaded_message;
            else
                $is_file_downloaded = true;
        }

        //----------------Export End---------------------------


        /*         * **** pagination code start here ********* */

        if ($is_file_downloaded === false) {
            $order_by = '';
            $pagination_uri_segment = 3;
            if (isset($_SESSION['search_t_data']['s_no_of_records']) && $_SESSION['search_t_data']['s_no_of_records'] != '')
                $per_page = $_SESSION['search_t_data']['s_no_of_records'];
            else
                $per_page = RECORDS_PER_PAGE;


            if ($this->uri->segment($pagination_uri_segment) == '') {
                $segment = 0;
            } else {
                $segment = $this->uri->segment($pagination_uri_segment);
            }
            //print_r($search_data);

            $tickets_data = $this->ticket_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
            $total = $this->ticket_mod->get_data_total_count();
            $data['total_records'] = $total;

            $config = array();
            $config = $this->utils_model->setup_pagination_option($total, 'ticket/index', $per_page, $pagination_uri_segment);
            $this->pagination->initialize($config);

            /*             * **** pagination code ends  here ********* */
            $data['pagination'] = $this->pagination->create_links();
            $data['data'] = $tickets_data;


            $this->load->view('basic/header', $data);
            $this->load->view('ticket/' . $view_page, $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function details($id = -1) {
        if ($id == -1)
            show_404();
        $page_name = "ticket_details";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $ticket_id = param_decrypt($id);
        $logged_account_id = get_logged_account_id();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('subject', 'Subject', 'trim|required|min_length[4]');
            $this->form_validation->set_rules('content', 'Content', 'trim|required');
            $this->form_validation->set_rules('ticket_account_id', 'Account ID', 'trim|required');

            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $ticket_account_id = trim($_POST['ticket_account_id']);
                $post_data = array();
                if (is_uploaded_file($_FILES['file_upload1']['tmp_name'])) {
                    $upload_path = 'uploads/ticket/' . $ticket_account_id;
                    if (!file_exists($upload_path))
                        mkdir($upload_path);
                    $file_name = $ticket_account_id . '_' . time();
                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx|xls|xlsx';
                    $config['file_name'] = $file_name;
                    $config['file_ext_tolower'] = TRUE;
                    $config['max_size'] = 0;
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload('file_upload1')) {
                        $uploaded_data_array = $this->upload->data();
                        $client_name = $uploaded_data_array['client_name'];
                        $file_name = $uploaded_data_array['file_name'];
                        $post_data['attachment'][] = array('file_name' => $file_name, 'file_name_display' => $client_name);
                    }
                }


                if (is_uploaded_file($_FILES['file_upload2']['tmp_name'])) {
                    $upload_path = 'uploads/ticket/' . $ticket_account_id;
                    if (!file_exists($upload_path))
                        mkdir($upload_path);

                    $file_name = $ticket_account_id . '_' . time();
                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx|xls|xlsx';
                    $config['file_name'] = $file_name;
                    $config['file_ext_tolower'] = TRUE;
                    $config['max_size'] = 0;
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload('file_upload2')) {
                        $uploaded_data_array = $this->upload->data();
                        $client_name = $uploaded_data_array['client_name'];
                        $file_name = $uploaded_data_array['file_name'];
                        $post_data['attachment'][] = array('file_name' => $file_name, 'file_name_display' => $client_name);
                    }
                }

                if (is_uploaded_file($_FILES['file_upload3']['tmp_name'])) {
                    $upload_path = 'uploads/ticket/' . $ticket_account_id;
                    if (!file_exists($upload_path))
                        mkdir($upload_path);
                    $file_name = $ticket_account_id . '_' . time();
                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx|xls|xlsx';
                    $config['file_name'] = $file_name;
                    $config['file_ext_tolower'] = TRUE;
                    $config['max_size'] = 0;
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload('file_upload3')) {
                        $uploaded_data_array = $this->upload->data();
                        $client_name = $uploaded_data_array['client_name'];
                        $file_name = $uploaded_data_array['file_name'];
                        $post_data['attachment'][] = array('file_name' => $file_name, 'file_name_display' => $client_name);
                    }
                }

                $post_data['subject'] = $_POST['subject'];
                $post_data['content'] = $_POST['content'];
                $post_data['parent_id'] = $ticket_id;
                $post_data['created_by'] = $logged_account_id;
                $post_data['created_by_name'] = get_account_full_name();
                if (isset($_POST['hide_from_customer']))
                    $post_data['hide_from_customer'] = 'Y';

                if (isset($_POST['status']) && $_POST['status'] != '')
                    $post_data['status'] = $_POST['status'];

                $result = $this->ticket_mod->add_reply($post_data);

                if ($result === true) {
                    //send mail					
                    $send_mail_to = array();
                    if (isset($_POST['send_mail_to_customer']) && trim($_POST['user_emailaddress']) != '') {
                        $send_mail_to[] = trim($_POST['user_emailaddress']);
                    }

                    if (isset($_POST['author_email']) && trim($_POST['author_email']) != '') {
                        $send_mail_to[] = trim($_POST['author_email']);
                    }

                    if (isset($_POST['send_mail_to_ac_manager']) && trim($_POST['send_mail_to_ac_manager']) != '') {
                        $result_ac = $this->member_mod->get_ac_manager($_POST['ticket_account_id']);
                        if (isset($result_ac['emailaddress']) && $result_ac['emailaddress'] != '') {
                            $send_mail_to[] = trim($result_ac['emailaddress']);
                        }
                    }




                    if (isset($_POST['other_emails']) && trim($_POST['other_emails']) != '') {
                        $other_emails_array = explode(',', $_POST['other_emails']);
                        foreach ($other_emails_array as $other_email) {
                            $send_mail_to[] = trim($other_email);
                        }
                    }
                    if (count($send_mail_to) > 0) {
                        $attachment_array = array();
                        $heading = addslashes($_POST['subject']);

                        $message = '<p> ' . addslashes($_POST['content']) . '</p>';
                        $body = file_get_contents(base_url() . 'email_templates/yellow.html');
                        $body = str_replace("#SITE_URL#", base_url(), $body);
                        $body = str_replace("#SITE_LOGO#", SITE_FULL_NAME, $body);
                        $body = str_replace("#HEADING#", $heading, $body);
                        $body = str_replace("#BODY#", $message, $body);
                        $body = str_replace("#SITE_NAME#", 'Kind regards,<br><strong>' . SITE_FULL_NAME . '</strong>', $body);
                        $subject = addslashes($_POST['ticket_number']) . ": " . addslashes($_POST['subject']);

                        $mail_to = implode(', ', $send_mail_to);

                        $mail_from = SITE_MAIL_FROM;
                        $mail_from_name = SITE_FULL_NAME;


                        if (isset($post_data['attachment']) && count($post_data['attachment']) > 0) {
                            $upload_path = 'uploads/ticket/' . $ticket_account_id;
                            foreach ($post_data['attachment'] as $attachment) {
                                $file_path = $upload_path . '/' . $attachment['file_name'];
                                $attachment_array[] = array($file_path, $attachment['file_name_display']);
                            }
                        }
                        $cc = '';
                        $bcc = '';
                        $mail_account_id = '';
                        $actionfrom = 'TicketReply';

                        send_mail($body, $subject, $mail_to, $mail_from, $mail_from_name, $cc, $bcc, $mail_account_id, $actionfrom, $attachment_array);
                    }
                    $this->session->set_flashdata('suc_msgs', 'Reply Added Successfully.');
                    $action = trim($_POST['button_action']);
                    if ($action == 'save')
                        redirect(base_url() . 'ticket/details/' . $id, 'location', '301');
                    else
                        redirect(base_url() . 'ticket', 'location', '301');
                    exit();
                }
                else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveCategory') {

            $this->form_validation->set_rules('category_id', 'Category', 'trim|required');
            if ($this->form_validation->run() == FALSE) {// error
                $data['err_msgs'] = validation_errors();
            } else {
                $post_data = array();

                $post_data['category_id'] = $_POST['category_id'];
                $post_data['ticket_id'] = $ticket_id;
                $result = $this->ticket_mod->update($post_data);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Category Updated Successfully.');
                    redirect(base_url() . 'ticket/details/' . $id, 'location', '301'); // 301 redirected	
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveAssignedto') {
            $this->form_validation->set_rules('assigned_to_id', 'Assigned To', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_data = array();
                $post_data['assigned_to_id'] = $_POST['assigned_to_id'];
                if (isset($_POST['assigned_to_user_id'])) {
                    $assigned_to_user_id_array = explode('-', $_POST['assigned_to_user_id']);
                    $post_data['assigned_to_user_id'] = $assigned_to_user_id_array[0];
                    $post_data['assigned_to_user_name'] = $assigned_to_user_id_array[1];
                }
                $post_data['ticket_id'] = $ticket_id;
                $result = $this->ticket_mod->update($post_data);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Assigned To Updated Successfully.');
                    redirect(base_url() . 'ticket/details/' . $id, 'location', '301'); // 301 redirected	
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveStatus') {
            $this->form_validation->set_rules('status', 'Status', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_data = array();

                $post_data['status'] = $_POST['status'];
                $post_data['ticket_id'] = $ticket_id;
                $result = $this->ticket_mod->update($post_data);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Status Updated Successfully.');
                    redirect(base_url() . 'ticket/details/' . $id, 'location', '301'); // 301 redirected	
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (check_logged_user_group(array('CUSTOMER', 'RESELLER'))) {
            $view_page = 'details_user';
            $hide_from_customer = 'N';
        } else {
            $view_page = 'details_admin';
            $hide_from_customer = '';

            $data['category_data'] = $this->ticket_mod->get_categories();
            $data['assignto_data'] = $this->ticket_mod->get_assignto();
        }
        $order_by = '';
        $per_page = 1;
        $segment = 0;
        $search_data = array(
            'ticket_id' => $ticket_id,
        );
        $option_param = array('category' => true, 'replies' => true, 'assigned_to' => true, 'hide_from_customer' => $hide_from_customer, 'attachment' => true);
        $tickets_data = $this->ticket_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
        if (isset($tickets_data['result'])) {
            $tickets_data = current($tickets_data['result']);
        } else {
            show_404();
        }

        if ($view_page == 'details_admin') {
            $user_data = $this->member_mod->get_account_by_key('account_id', $tickets_data['account_id'], array());
            $data['user_data'] = $user_data;
        }

        $data['ticket_data'] = $tickets_data;
        $this->load->view('basic/header', $data);
        $this->load->view('ticket/' . $view_page, $data);
        $this->load->view('basic/footer', $data);
    }

    public function create() {
        $page_name = "ticket_add";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $logged_account_id = get_logged_account_id();
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $post_data = array();
            $this->form_validation->set_rules('subject', 'Subject', 'trim|required|min_length[4]');
            $this->form_validation->set_rules('category_id', 'Category', 'trim|required');
            $this->form_validation->set_rules('content', 'Content', 'trim|required');
            if (check_logged_user_group(array('CUSTOMER', 'RESELLER'))) {
                $post_data['account_id'] = $logged_account_id;
                $ticket_account_id = $logged_account_id;
            } else {
                $this->form_validation->set_rules('account_id', 'Account ID', 'trim|required');
                $post_data['account_id'] = $ticket_account_id = trim($_POST['account_id']);
            }

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                if (is_uploaded_file($_FILES['file_upload1']['tmp_name'])) {
                    $upload_path = 'uploads/ticket/' . $ticket_account_id;
                    if (!file_exists($upload_path))
                        mkdir($upload_path);
                    $file_name = $ticket_account_id . '_' . time();
                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx|xls|xlsx';
                    $config['file_name'] = $file_name;
                    $config['file_ext_tolower'] = TRUE;
                    $config['max_size'] = 0;
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload('file_upload1')) {
                        $uploaded_data_array = $this->upload->data();
                        $client_name = $uploaded_data_array['client_name'];
                        $file_name = $uploaded_data_array['file_name'];
                        $post_data['attachment'][] = array('file_name' => $file_name, 'file_name_display' => $client_name);
                    }
                }

                if (is_uploaded_file($_FILES['file_upload2']['tmp_name'])) {
                    $upload_path = 'uploads/ticket/' . $ticket_account_id;
                    if (!file_exists($upload_path))
                        mkdir($upload_path);
                    $file_name = $ticket_account_id . '_' . time();
                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx|xls|xlsx';
                    $config['file_name'] = $file_name;
                    $config['file_ext_tolower'] = TRUE;
                    $config['max_size'] = 0;
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload('file_upload2')) {
                        $uploaded_data_array = $this->upload->data();
                        $client_name = $uploaded_data_array['client_name'];
                        $file_name = $uploaded_data_array['file_name'];
                        $post_data['attachment'][] = array('file_name' => $file_name, 'file_name_display' => $client_name);
                    }
                }

                if (is_uploaded_file($_FILES['file_upload3']['tmp_name'])) {
                    $upload_path = 'uploads/ticket/' . $ticket_account_id;
                    if (!file_exists($upload_path))
                        mkdir($upload_path);
                    $file_name = $ticket_account_id . '_' . time();
                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx|xls|xlsx';
                    $config['file_name'] = $file_name;
                    $config['file_ext_tolower'] = TRUE;
                    $config['max_size'] = 0;
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload('file_upload3')) {
                        $uploaded_data_array = $this->upload->data();
                        $client_name = $uploaded_data_array['client_name'];
                        $file_name = $uploaded_data_array['file_name'];
                        $post_data['attachment'][] = array('file_name' => $file_name, 'file_name_display' => $client_name);
                    }
                }
                $post_data['subject'] = $_POST['subject'];
                $post_data['content'] = $_POST['content'];
                $post_data['category_id'] = $_POST['category_id'];

                if (isset($_POST['status']) && $_POST['status'] == 'closed') {
                    $post_data['status'] = 'closed';
                }

                $post_data['author_email_subscribe'] = 'N';
                if (isset($_POST['author_email'])) {
                    $post_data['author_name'] = $_POST['author_name'];
                    $post_data['author_email'] = $_POST['author_email'];
                    if (isset($_POST['author_email_subscribe']))
                        $post_data['author_email_subscribe'] = 'Y';
                }

                $post_data['created_by'] = $logged_account_id;
                $post_data['created_by_name'] = get_account_full_name();
                $result = $this->ticket_mod->add($post_data);
                //echo '<pre>';print_r($_POST);print_r($post_data);var_dump($result );die;
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Ticket Created Successfully.');
                    redirect(base_url() . 'ticket', 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        $category_data = $this->ticket_mod->get_categories();
        $data['category_data'] = $category_data;

        if (check_logged_user_group(array('CUSTOMER', 'RESELLER'))) {
            $view_page = 'create_user';
        } else {
            $view_page = 'create_admin';
        }
        $this->load->view('basic/header', $data);
        $this->load->view('ticket/' . $view_page, $data);
        $this->load->view('basic/footer', $data);
    }

    function ajax_get_assigned_users() {

        $assigned_to_id = trim($_POST['assigned_to_id']);
        $result = $this->ticket_mod->get_assigned_users($assigned_to_id);
        $return = '<option value="">Select </option>';

        if ($result['status'] == 'success' && count($result['result']) > 0) {
            foreach ($result['result'] as $user_array) {
                $account_id = $user_array['account_id'];
                $name = $user_array['name'];
                $val = $account_id . '-' . $name;
                $return .= '<option value="' . $val . '">' . $name . '</option>';
            }
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($return));
    }

    function ajax_get_category_by_subject() {
//all subjects comes from get_t_subjects() under ticket helper;	 
        $subject_category_mapping_array = array(
//subject id=>category id
            0 => 6,
            1 => 9,
            2 => 3,
            3 => 6,
            4 => 6,
            5 => 6,
            6 => 6,
            7 => 6,
            8 => 6,
            9 => 6,
            10 => 6,
            11 => 6,
            12 => 5,
            13 => 6,
            14 => 7,
            15 => 6,
            16 => 12,
            17 => 6,
            18 => 21,
            19 => 6,
            20 => 6,
            21 => 20,
            22 => 6,
            23 => 6,
            24 => 6,
            25 => 6,
            26 => 5,
            27 => 15,
            28 => 21,
            29 => 7,
            30 => 12,
            31 => 14,
            32 => 14,
            33 => 14,
            34 => 14,
            35 => 14,
            36 => 14,
            37 => 14,
            38 => 14,
            39 => 14,
            40 => 7,
            41 => 7,
            42 => 8,
            43 => 6,
            44 => 7,
            45 => 7,
            46 => 7,
            47 => 5,
            48 => 7,
            49 => 6,
            50 => 6,
            51 => 6,
            52 => 7,
            53 => 6,
            54 => 7,
            55 => 21,
            56 => 21,
            57 => 6
        );

        $subject_select_value = trim($_POST['subject_select']);
        $category_id = $subject_category_mapping_array[$subject_select_value];

        $category_data = $this->ticket_mod->get_categories();

        $str = '';
        if (isset($category_data['result']) && count($category_data['result']) > 0) {
            foreach ($category_data['result'] as $key => $parent_category_array) {
                $str .= '<optgroup label="' . $parent_category_array['category_name'] . '">';
                if (isset($parent_category_array['sub']) && count($parent_category_array['sub']) > 0) {
                    foreach ($parent_category_array['sub'] as $key => $category_array) {
                        $selected = ' ';
                        if ($category_id == $category_array['category_id'])
                            $selected = 'selected="selected" ';
                        $str .= '<option value="' . $category_array['category_id'] . '" ' . $selected . '>' . $category_array['category_name'] . '</option>';
                    }
                }
                else {
                    $selected = ' ';
                    if ($category_id == $parent_category_array['category_id'])
                        $selected = '  selected="selected" ';
                    $str .= '<option value="' . $parent_category_array['category_id'] . '" ' . $selected . '>' . $parent_category_array['category_name'] . '</option>';
                }

                $str .= '</optgroup>';
            }
        }


        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($str));
    }

    function dashboard() {
        $page_name = "ticket_dashboard";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();


        $this->load->view('basic/header', $data);
        $this->load->view('ticket/ticket_dashboard', $data);
        $this->load->view('basic/footer', $data);
    }

}
