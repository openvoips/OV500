<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Attestation extends MY_Controller {

    function __construct() {
        parent::__construct();

        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');

        $this->account_id = get_logged_account_id();
    }

    public function index() {
        $page_name = "attestation_settings";
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkSaveData') {
            $this->form_validation->set_rules('attest_in_checking', 'Attestation chacking', 'trim|required|in_list[1,0]');
            $this->form_validation->set_rules('attest_in_a', 'Level A', 'trim|in_list[1,0]');
            $this->form_validation->set_rules('attest_in_b', 'Level B', 'trim|in_list[1,0]');
            $this->form_validation->set_rules('attest_in_c', 'Level C', 'trim|in_list[1,0]');

            if ($this->form_validation->run() == FALSE) {
                $data['err_msgs'] = validation_errors();
            } else {
                $_POST['account_id'] = $this->account_id;
                $result = $this->update_attestation_data($_POST);
                if ($result === true) {
                    $this->session->set_flashdata('suc_msgs', 'Attestation Settings Updated Successfully');
                    redirect(site_url('attestation'), 'location', '301');
                } else {
                    $err_msgs = $result;
                    $data['err_msgs'] = $err_msgs;
                }
            }
        } {
            $customers_data_temp = $this->get_attestation_data($this->account_id);
            if (isset($customers_data_temp))
                $data['data'] = $customers_data_temp;
            else {
                show_404();
            }
        }

        $this->load->view('basic/header', $data);
        $this->load->view('attestationSettings', $data);
        $this->load->view('basic/footer', $data);
    }

    function get_attestation_data($account_id) {
        $DB1 = $this->db; //load->database('default', true);
        $sql = "SELECT account_id, attest_in_checking, attest_in_a, attest_in_b, attest_in_c FROM customers  WHERE account_id ='" . $account_id . "'";
        $query = $DB1->query($sql);
        $row = $query->row_array();

        return $row;
    }

    function update_attestation_data($data) {
        try {
            $log_data_array = array();
            if (isset($data['account_id'])) {
                $account_id = $data['account_id'];
            } else {
                return 'User missing';
            }

            //ddd($data);die;

            /*
              if (isset($data['ipaddress'])) {
              $sql = "SELECT account_id FROM customer_ips  WHERE ipaddress='" . $data['ipaddress'] . "'  AND dialprefix='" . $data['dialprefix'] . "' AND  id !='" . $id . "'";
              $query = $this->db->query($sql);
              $row = $query->row();
              if ($row == NULL) {

              } else {
              return 'This IP & Dial Prefix already exists in system';
              }
              }
             */
            $attestation_data_array = array();
            $attestation_data_array['attest_in_checking'] = $attestation_data_array['attest_in_a'] = $attestation_data_array['attest_in_b'] = $attestation_data_array['attest_in_c'] = '0';
            if (isset($data['attest_in_checking']))
                $attestation_data_array['attest_in_checking'] = $data['attest_in_checking'];
            if (isset($data['attest_in_a']))
                $attestation_data_array['attest_in_a'] = 1;
            if (isset($data['attest_in_b']))
                $attestation_data_array['attest_in_b'] = 1;
            if (isset($data['attest_in_c']))
                $attestation_data_array['attest_in_c'] = 1;

            $this->db->trans_begin();
            if (count($attestation_data_array) > 0) {
                $where = " account_id='" . $account_id . "' ";
                $str = $this->db->update_string('customers', $attestation_data_array, $where);
                $result = $this->db->query($str);
                if (!$result) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return $error_array['message'];
            } else {
                $this->db->trans_commit();
            }
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $e->getMessage();
        }
    }

}
