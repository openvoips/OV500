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

class Ticket extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->load->model('ticket_mod');
    }

    public function index($id = -1) {
        
        
        $page_name = "ticket_index";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $logged_account_id = get_logged_account_id();
        if (isset($_POST['search_action'])) {
            $_SESSION['search_t_data'] = array('s_ticket_number' => trim($_POST['ticket_number']), 's_status' => trim($_POST['status']), 's_no_of_records' => $_POST['no_of_rows']);
            if (!check_logged_account_type(array('CUSTOMER', 'RESELLER'))) {
                $_SESSION['search_t_data']['s_account_id'] = trim($_POST['account_id']);
                $_SESSION['search_t_data']['s_category_id'] = trim($_POST['category_id']);
                $_SESSION['search_t_data']['s_assigned_to_id'] = trim($_POST['assigned_to_id']);
            }
        } else {
            $_SESSION['search_t_data']['s_ticket_number'] = isset($_SESSION['search_t_data']['s_ticket_number']) ? $_SESSION['search_t_data']['s_ticket_number'] : '';
            $_SESSION['search_t_data']['s_status'] = isset($_SESSION['search_t_data']['s_status']) ? $_SESSION['search_t_data']['s_status'] : '';
            if ($id != -1 && is_numeric($id) != 'integer') {
                $s_account_id = param_decrypt($id);
                $_SESSION['search_t_data']['s_account_id'] = $s_account_id;
            } else {
                $_SESSION['search_t_data']['s_account_id'] = isset($_SESSION['search_t_data']['s_account_id']) ? $_SESSION['search_t_data']['s_account_id'] : '';
            }
            $_SESSION['search_t_data']['s_category_id'] = isset($_SESSION['search_t_data']['s_category_id']) ? $_SESSION['search_t_data']['s_category_id'] : '';
            $_SESSION['search_t_data']['s_assigned_to_id'] = isset($_SESSION['search_t_data']['s_assigned_to_id']) ? $_SESSION['search_t_data']['s_assigned_to_id'] : '';
            if (isset($_SESSION['search_t_data']['s_no_of_records']) && $_SESSION['search_t_data']['s_no_of_records'] != '') {
                $per_page = $_SESSION['search_t_data']['s_no_of_records'];
            } else {
                $per_page = RECORDS_PER_PAGE;
            }
            $_SESSION['search_t_data']['s_no_of_records'] = $per_page;
        }
        if (check_logged_account_type(array('CUSTOMER', 'RESELLER'))) {
            $view_page = 'listing_user';
            $search_data = array('ticket_number' => $_SESSION['search_t_data']['s_ticket_number'], 'status' => $_SESSION['search_t_data']['s_status'], 'account_id' => $logged_account_id, 'parent_id' => '0');
            $option_param = array('category' => true, 'last_post' => true, 'total_post' => true);
              
        } else {
            $view_page = 'listing_admin';
            $search_data = array(
                'parent_id' => '0',
                'ticket_number' => $_SESSION['search_t_data']['s_ticket_number'],
                'status' => $_SESSION['search_t_data']['s_status'],
                'account_id' => $_SESSION['search_t_data']['s_account_id'],
                'category_id' => $_SESSION['search_t_data']['s_category_id'],
                'assigned_to_id' => $_SESSION['search_t_data']['s_assigned_to_id'],
            );
            
          
            $data['category_data'] = $this->ticket_mod->get_categories();
            $data['assignto_data'] = $this->ticket_mod->get_assignto();
            $option_param = array('category' => true, 'assigned_to' => true, 'last_post' => true, 'total_post' => true);
        }
        
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
        $tickets_data = $this->ticket_mod->get_data($order_by, $per_page, $segment, $search_data, $option_param);
        $total = $this->ticket_mod->get_data_total_count();
        $data['total_records'] = $total;
        $config = array();
        $config = $this->utils_model->setup_pagination_option($total, 'ticket/index', $per_page, $pagination_uri_segment);
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['data'] = $tickets_data;
        $this->load->view('basic/header', $data);
        $this->load->view('ticket/' . $view_page, $data);
        $this->load->view('basic/footer', $data);
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
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $ticket_account_id = trim($_POST['ticket_account_id']);
                $post_data = array();
                if (is_uploaded_file($_FILES['file_upload1']['tmp_name'])) {
                    $upload_path = 'uploads/ticket/' . strtolower(SITE_SUBDOMAIN) . '/' . $ticket_account_id;
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
                    $upload_path = 'uploads/ticket/' . strtolower(SITE_SUBDOMAIN) . '/' . $ticket_account_id;
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
                    $upload_path = 'uploads/ticket/' . strtolower(SITE_SUBDOMAIN) . '/' . $ticket_account_id;
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

                $result = $this->ticket_mod->add_reply($post_data);

                if ($result === true) {
                    $send_mail_to = array();
                    if (isset($_POST['send_mail_to_customer']) && trim($_POST['user_emailaddress']) != '') {
                        $send_mail_to[] = trim($_POST['user_emailaddress']);
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
                            $upload_path = 'uploads/ticket/' . strtolower(SITE_SUBDOMAIN) . '/' . $ticket_account_id;
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
                    redirect(base_url() . 'ticket/details/' . $id, 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }
        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveCategory') {
            $this->form_validation->set_rules('category_id', 'Category', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $post_data = array();
                $post_data['category_id'] = $_POST['category_id'];
                $post_data['ticket_id'] = $ticket_id;
                $result = $this->ticket_mod->update($post_data);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Category Updated Successfully.');
                    redirect(base_url() . 'ticket/details/' . $id, 'location', '301');
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
                $post_data['ticket_id'] = $ticket_id;
                $result = $this->ticket_mod->update($post_data);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Assigned To Updated Successfully.');
                    redirect(base_url() . 'ticket/details/' . $id, 'location', '301');
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
                    redirect(base_url() . 'ticket/details/' . $id, 'location', '301');
                    exit();
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        }

        if (check_logged_account_type(array('CUSTOMER', 'RESELLER'))) {
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
            if (check_logged_account_type(array('CUSTOMER', 'RESELLER'))) {
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
                    $upload_path = 'uploads/ticket/' . strtolower(SITE_SUBDOMAIN) . '/' . $ticket_account_id;
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
                    $upload_path = 'uploads/ticket/' . strtolower(SITE_SUBDOMAIN) . '/' . $ticket_account_id;
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
                    $upload_path = 'uploads/ticket/' . strtolower(SITE_SUBDOMAIN) . '/' . $ticket_account_id;
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
        if (check_logged_account_type(array('CUSTOMER', 'RESELLER'))) {
            $view_page = 'create_user';
        } else {
            $view_page = 'create_admin';
        }
        $this->load->view('basic/header', $data);
        $this->load->view('ticket/' . $view_page, $data);
        $this->load->view('basic/footer', $data);
    }

}
