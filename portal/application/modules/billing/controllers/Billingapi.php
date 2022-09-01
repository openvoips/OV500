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

class Billingapi extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Billingapi_mod');
    }

    public function api() {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $REQUEST = $_POST;
        } elseif ($_SERVER['REQUEST_METHOD'] == "GET") {
            $REQUEST = $_GET;
        } else {
            $REQUEST = $_REQUEST;
        }
 
        $result = $this->Billingapi_mod->billing($REQUEST);
        echo json_encode($result);
    }

    public function cron() {
        $date = date('Y-m-d');
 
        $this->Billingapi_mod->carrier_usage_data($date);
        $account = '';
        $account_type = 'CUSTOMER';
        $this->Billingapi_mod->cdr_service($account, $date, $account_type);
        $this->Billingapi_mod->monthlycharges($date);
        $this->ManageBalance();
    }

    public function quickservice() {
        $this->Billingapi_mod->creditmanagement();
    }

    function ManageBalance() {
        $DB1 = $this->load->database('default', true);
        $sql = "select account_id from customer_balance";
        $query = $DB1->query($sql);
        if (!$query) {
            $error_array = $DB1->error();
            throw new Exception($error_array['message']);
        }
        $result = $query->result_array();
        if (count($result) > 0) {
            foreach ($result as $sdr_data) {
                $account_id = $sdr_data['account_id'];
                $me = $this->calculate_total_available_balance($account_id);
                $sql = "UPDATE customer_balance SET balance ='" . -1 * $me['current_balance'] . "' WHERE account_id='" . $me['account_id'] . "';";
                echo $sql . PHP_EOL;
                $query = $DB1->query($sql);
            }
        }
    }

    function calculate_total_available_balance($account_id, $invoice_id = '') {
        $DB1 = $this->load->database('default', true);
        $customer_dp = 3;
        $sql = "SELECT
			id,
			rule_type,
			billing_date as action_date,
			group_concat(DISTINCT  service_number ORDER BY service_number ASC SEPARATOR ', ')  notes,
			startdate service_startdate,
			enddate service_stopdate,
			account_id,
			SUM(totalcost) total_cost,
			sys_sdr_terms.term,
			sys_sdr_terms.term_group,
			sys_sdr_terms.cost_calculation_formula	 
	FROM bill_account_sdr INNER JOIN sys_sdr_terms ON bill_account_sdr.rule_type=sys_sdr_terms.term
	WHERE account_id ='" . $account_id . "' ";

        if (strlen(trim($invoice_id)) > 0)
            $sql .= " AND invoice_id ='" . $invoice_id . "' ";
        else
            $sql .= " AND (invoice_id IS NULL OR invoice_id = '')  ";

        $sql .= " GROUP BY rule_type, date(billing_date)  ";
        $query = $DB1->query($sql);
        if (!$query) {
            $error_array = $DB1->error();
            throw new Exception($error_array['message']);
        }
        $result = $query->result_array();

        $openingbalance = $addbalance = $removebalance = $usage = 0;
        $debit_sum = $credit_sum = 0;

        if (count($result) > 0) {
            foreach ($result as $sdr_data) {
                $debit = $credit = 0;
                $display_text = '';
                $rule_type = $sdr_data['rule_type'];
                $term_group = $sdr_data['term_group'];
                $display_text = '';
                $cost_calculation_formula = trim($sdr_data['cost_calculation_formula']);

                $total_cost = round($sdr_data['total_cost'], $customer_dp);
                if ($term_group == 'opening') {
                    if ($cost_calculation_formula == '+') {
                        $openingbalance = $openingbalance + $total_cost;
                        $credit = $total_cost;
                    } elseif ($cost_calculation_formula == '-') {
                        $openingbalance = $openingbalance - $total_cost;
                        $debit = $total_cost;
                    }
                } elseif ($term_group == 'balance') {
                    if ($cost_calculation_formula == '+') {
                        $addbalance = $addbalance + $total_cost;
                        $credit = $total_cost;
                    } elseif ($cost_calculation_formula == '-') {
                        $removebalance = $removebalance + $total_cost;
                        $debit = $total_cost;
                    }
                } else {
                    if ($cost_calculation_formula == '+') {
                        $usage = $usage + $total_cost;
                        $credit = $total_cost;
                    } elseif ($cost_calculation_formula == '-') {
                        $usage = $usage + $total_cost;
                        $debit = $total_cost;
                    }
                }


                $debit_sum += $debit;
                $credit_sum += $credit;

                if ($cost_calculation_formula == '')
                    continue;
            }
        }
        $current_balance = $openingbalance + $addbalance - $removebalance - $usage;

        return array('current_balance' => $current_balance,
            'openingbalance' => $openingbalance,
            'addbalance' => $addbalance,
            'removebalance' => $removebalance,
            'usage' => $usage,
            'account_id' => $account_id,
            'invoice_id' => $invoice_id
        );
    }

}
