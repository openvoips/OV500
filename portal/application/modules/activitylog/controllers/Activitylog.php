<?php
/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class activitylog extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('activitylog_mod');

        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
    }

    public function index() {
        $page_name = "activitylog_index";
        $search_session_key = 'search_' . $page_name;


        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $search_parameters = array('account_id', 'page_url', 'ip_address', 'session_id', 'group_by_ip', 'group_by_session', 'group_by_page', 'group_by_account', 'time_range', 'no_of_rows');

        //group_by
        if (isset($_POST['search_action'])) {// coming from search button
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        $search_data = array(
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'page_url' => $_SESSION[$search_session_key]['page_url'],
            'ip_address' => $_SESSION[$search_session_key]['ip_address'],
            'session_id' => $_SESSION[$search_session_key]['session_id'],
			'time_range' => $_SESSION[$search_session_key]['time_range'],
        );
        if ($_SESSION[$search_session_key]['group_by_ip'] == 'Y')
            $search_data['group_by'][] = 'ip_address';
        if ($_SESSION[$search_session_key]['group_by_session'] == 'Y')
            $search_data['group_by'][] = 'session_id';
        if ($_SESSION[$search_session_key]['group_by_page'] == 'Y')
            $search_data['group_by'][] = 'page_url';
        if ($_SESSION[$search_session_key]['group_by_account'] == 'Y')
            $search_data['group_by'][] = 'account_id';

        $_SESSION[$search_session_key]['group_by'] = $search_data['group_by'];

        $is_file_downloaded = false;
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $activitylog_data = $this->activitylog_mod->get_data('', $per_page, $segment, $search_data);
            $total_count = $this->activitylog_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'activitylog/index', $per_page, $pagination_uri_segment, $this->pagination);

            $data['activity_log_data'] = $activitylog_data;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key; //new

            $this->load->view('basic/header', $data);
            $this->load->view('activity_log', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    public function details($id_encrypted = -1) {
        $page_name = "activitylog_details";
        $search_session_key = 'search_' . $page_name;
        $is_file_downloaded = false;

        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $id = param_decrypt($id_encrypted);
        $search_data = array(
            'id' => $id,
        ); {
            $activitylog_data = $this->activitylog_mod->get_data('', 1, 0, $search_data);
            if (count($activitylog_data['result']) > 0)
                $data['activity_log_data'] = current($activitylog_data['result']);

            $this->load->view('basic/header', $data);
            $this->load->view('activity_log_details', $data);
            $this->load->view('basic/footer', $data);
        }
    }

}
