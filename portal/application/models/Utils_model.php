<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */

class Utils_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_countries() {
        $sql = "SELECT country_id, country_name, country_prefix, country_abbr FROM sys_countries WHERE status_id='1' ORDER BY display_sequence DESC, country_name";
        $query = $this->db->query($sql);
        $rows = $query->result();
        return $rows;
    }

    function get_currencies() {
        $sql = "SELECT currency_id, symbol,  name FROM sys_currencies where status_id = '1' ORDER BY display_sequence,  name";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        return $rows;
    }

    function get_sdr_terms($term_group = '') {
        $final_return_array = array();
        $sql = "SELECT term_group, term, display_text, cost_calculation_formula FROM sys_sdr_terms WHERE 1";
        if ($term_group != '')
            $sql .= " AND term_group='" . $term_group . "'";
        $sql .= " ORDER BY term_group, term";
        $query = $this->db->query($sql);
        foreach ($query->result_array() as $row) {
            $term = $row['term'];
            $final_return_array[$term] = $row;
        }
        return $final_return_array;
    }

    

    function get_tariffs($user_type, $tariff_type = '', $created_by = '') {
        $sql = "SELECT id, tariff_id, tariff_name, tariff_currency_id, tariff_type FROM tariff t WHERE t.tariff_status='1' ";

        $logged_account_id = get_logged_account_id();
        $sql .= " AND t.account_id= '$logged_account_id'";

        if ($tariff_type != '')
            $sql .= " AND t.tariff_type='" . $tariff_type . "'";

        $sql .= " ORDER BY t.tariff_type, t.tariff_name";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        return $rows;
    }

    function createOptionsByArray($values, $names, $selValue = false) {
        $limit = count($values);
        for ($x = 0; $x < $limit; $x++) {
            $value = $values[$x];
            $name = $names[$x];
            if (!$name)
                $name = $value;

            if ($value == $selValue)
                $selected = " Selected ";
            else
                $selected = " ";

            $str = $str . "<option value='" . $value . "'" . $selected . ">" . $name . " </option>";
        }

        return $str;
    }

    function setup_pagination_option($total, $page, $per_page, $segment) {

        $config['total_rows'] = $total;
        $config['num_links'] = 2;
        $config['base_url'] = base_url() . $page;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = $segment;
        $config['use_page_numbers'] = FALSE;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm justify-content-end">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a href="javascript:void(0);" class="current"><b>';
        $config['cur_tag_close'] = '</b></a></li>';
        $config['prev_link'] = '<i class="success fa fa-angle-double-left"></i> Prev';
        $config['prev_tag_open'] = '<li class="previous">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = 'Next <i class="success fa fa-angle-double-right"></i>';
        $config['next_tag_open'] = '<li class="next">';
        $config['next_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li class="first">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="last">';
        $config['last_tag_close'] = '</li>';

        return $config;
    }

    function get_rule_options($option_group) {
        $final_return_array = array();
        try {
            $sql = "SELECT id, option_id, option_name FROM sys_rule_options WHERE status_id='1' AND option_group='" . $option_group . "'";
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }

            $final_return_array['result'] = array();
            foreach ($query->result_array() as $row) {
                $id = $row['id'];
                $final_return_array['result'][$id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Options fetched successfully';
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_currency_conversion($currency_id = '') {
        $final_return_array = array();
        $sql = "SELECT t1.* FROM sys_currencies_conversions t1 WHERE t1.id IN (SELECT max(id) FROM sys_currencies_conversions GROUP BY currency_id)  ";

        if ($currency_id != '')
            $sql .= " AND t1.currency_id='" . $currency_id . "'";

        $sql .= " ORDER BY t1.currency_id";

        $query = $this->db->query($sql);

        foreach ($query->result_array() as $row) {
            $currency_id = $row['currency_id'];
            $final_return_array['result'][$currency_id] = $row;
        }

        $final_return_array['status'] = 'success';
        $final_return_array['message'] = 'Currency Conversion fetched successfully';

        return $final_return_array;
    }

    function get_data_total_count($sql, $db = 'default') {
        try {
            $count_sql = generate_count_total_sql($sql);
            if (substr($count_sql, 0, 5) == 'error') {
                throw new Exception($count_sql);
            }

            $this->total_count_sql = $count_sql;
            if ($db == 'default')
                $query_count = $this->db->query($count_sql);
            else
                $query_count = $this->cdrdb->query($count_sql);
            $row_count = $query_count->row();

            return $row_count->total;
        } catch (Exception $e) {

            return 0;
        }

        return 0;
    }

    function generate_key($name, $prefix1, $table, $unique_field_name) {
        $prefix2 = '';
        $key = generate_key($name, '');

        $sql = "SELECT $unique_field_name as table_key FROM " . $table . " ";
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)) {
            $max_key = $row->table_key;
            $new_key_int = $max_key;

            while (1) {
                $new_key = $prefix1 . $prefix2 . $key . rand(100, 999);
                $new_key = sprintf('%-015s', $new_key);

                $sql = "SELECT $unique_field_name
				 FROM " . $table . " 
				 WHERE  $unique_field_name ='" . $new_key . "'";
                $query = $this->db->query($sql);
                $row = $query->row();
                if (isset($row)) {
                    
                } else {
                    break;
                }
            }
        } else {
            $new_key = $prefix1 . $prefix2 . $key . rand(100, 999);
            $new_key = sprintf('%-015s', $new_key);
        }

        return $new_key;
    }

    function get_customers($parent_account_id='') 
    {
        $sql = "SELECT ua.account_id, ua.name, ua.company_name, u.parent_account_id            
			FROM customers ua INNER JOIN account u ON u.account_id=ua.account_id  
			WHERE  u.status_id >'0'";
   
        $sql .=" AND parent_account_id='".$parent_account_id."'";
        $sql .="ORDER BY company_name";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        return $rows;
    } 

}

?>
