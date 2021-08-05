<?php

/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

class Billingapi_mod extends CI_Model {

    var $customer = Array();
    var $accountinfo = Array();
    var $debug = false;
    var $dobilling = true;
    var $prorata_billing = true;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function billing($data) {
        $this->requesttype = 'MANUAL';
        if ($data['REQUEST'] == 'CHECK') {
            return $this->check($data);
        } elseif ($data['REQUEST'] == 'START') {
            return $this->start($data);
        } elseif ($data['REQUEST'] == 'STOP') {
            return $this->stop($data);
        } elseif ($data['REQUEST'] == 'EXTRACHARGES') {
            return array('status' => true, 'msg' => 'Ok');
        } elseif ($data['REQUEST'] == 'TARIFFCHARGES') {
            $result = $this->tariffchanges($data);
            if ($result) {
                header('Content-Type: application/json');
                $op = array('status' => 'SUCCESS', 'message' => "Tariff addedd", 'error' => 0);
                return $op;
            } else {
                $error_message = "";
                header('Content-Type: application/json');
                $op = array('status' => 'FAILED', 'message' => $error_message, 'error' => 1);
                return $op;
            }
        } elseif ($data['REQUEST'] == 'OPENINGBALANCE') {
            $date = date('Y-m-d');
            $result = $this->openingbalance($data);
            if ($result) {
                header('Content-Type: application/json');
                $op = array('status' => 'SUCCESS', 'message' => "Opening Balance added", 'error' => 0);
                return $op;
            } else {
                $error_message = "";
                header('Content-Type: application/json');
                $op = array('status' => 'FAILED', 'message' => $error_message, 'error' => 1);
                return $op;
            }
        } elseif ($data['REQUEST'] == 'BUNDLECHARGES') {
            $this->data = Array();
            $date = date('Y-m-d');
            $this->assign_bundle($data, $date);

            if ($this->data['error'] == '0') {
                header('Content-Type: application/json');
                $msg = $this->data['message'];
                $op = array('status' => 'SUCCESS', 'message' => $msg, 'error' => 0);
                return $op;
            } else {
                header('Content-Type: application/json');
                $msg = $this->data['message'];
                $op = array('status' => 'FAILED', 'message' => $msg, 'error' => 1);
                return $op;
            }
        } elseif ($data['REQUEST'] == 'REMOVEBALANCE' or $data['REQUEST'] == 'ADDBALANCE' or $data['REQUEST'] == 'ADDCREDIT' or $data['REQUEST'] == 'REMOVECREDIT' or $data['REQUEST'] == 'ADDTESTBALANCE' or $data['REQUEST'] == 'REMOVETESTBALANCE' or $data['REQUEST'] == 'BALANCETRANSFERADD' or $data['REQUEST'] == 'BALANCETRANSFERREMOVE') {
            $result = $this->save_payment($data);

            if ($result) {
                header('Content-Type: application/json');
                $op = array('status' => 'SUCCESS', 'message' => "Added successfully", 'error' => 0);
                return $op;
            } else {
                $error_message = "";
                header('Content-Type: application/json');
                $op = array('status' => 'FAILED', 'message' => $error_message, 'error' => 1);
                return $op;
            }
        } elseif ($data['REQUEST'] == 'SERVICES' or $data['REQUEST'] == 'NEWDIDSETUP' or $data['REQUEST'] == 'DIDSETUP' or $data['REQUEST'] == 'DIDRENTAL' or $data['REQUEST'] == 'DIDCANCEL' or $data['REQUEST'] == 'DIDEXTRACHRENTAL') {
            $date = date('Y-m-d');
            $didsetup = 0;
            $rental = 0;
            $extrachannels = 0;
            if ($data['REQUEST'] == 'NEWDIDSETUP' or $data['REQUEST'] == 'DIDSETUP') {
                $didsetup = 1;
                $rental = 1;
            }
            if ($data['REQUEST'] == 'SERVICES' or $data['REQUEST'] == 'DIDRENTAL') {
                $rental = 1;
            }
            if ($data['REQUEST'] == 'DIDEXTRACHRENTAL') {
                $extrachannels = 0;
            }
			$this->data=Array();
            $this->request = Array();
            $this->request['account_id'] = $data['account_id'];
            $this->request['service_number'] = $data['service_number'];
            $this->request['channels'] = $data['channels'];
            $this->request['request_from'] = 'service';
            $this->request['carrier_id'] = $data['carrier_id'];
            $this->request['lastbilldate'] = 'lastbilldate';
            $this->data['error'] = '0';

            if ($this->debug)
                print_r($data);
            if ($this->debug)
                print_r($this->request);

			$this->customertype();
			if ($this->request['account_type'] == 'RESELLER') {
				$this->resellerinfo();
			} else {
				$this->customerinfo();
			}
					
					
            if ($this->debug)
                print_r($this->request);

            $result = $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
            if ($result) {
                header('Content-Type: application/json');
                $op = array('status' => 'SUCCESS', 'message' => "DID added successfully", 'error' => 0);
                return $op;
            } else {
                $error_message = $this->data['message'];
                header('Content-Type: application/json');
                $op = array("status" => "FAILED", "message" =>  $this->data['message'], "error" => 1);
                return $op;
            }
        }
    }

    function charges_cal_bundle($charges, $service_startdate, $service_stopdate) {
        if ($this->prorata_billing) {
            
        } else {
            $service_startdate = date('Y-m-01', strtotime($service_startdate));
        }

        $no_of_days = date('t', strtotime($service_startdate));
        $diff = abs(strtotime($service_startdate) - strtotime($service_stopdate));
        $years = floor($diff / (365 * 60 * 60 * 24));
        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        $current_month_charges = (($charges / $no_of_days) * $days) + ($charges * $months) + ( 12 * $charges * $years);
        return $current_month_charges;
    }

    function tariffchanges($data) {
        try {
            $sdr_data_array['RULETYPE'] = 'OPENINGBALANCE';
            $sdr_data_array['ACCOUNTID'] = $key;
            $sdr_data_array['REQUEST'] = 'TARIFFCHARGES';
            $sdr_data_array['SERVICENUMBER'] = $plan_data->tariff_id;
            $sdr_data_array['CREATEDBY'] = $key;
            $sdr_data_array['ACCOUNTTYPE'] = $key;

            $this->request = Array();
            foreach ($data as $key => $value) {
                $this->request[$key] = trim($value);
            }

            $service_startdate = date('Y-m-d h:s:i');
            $service_stopdate = date('Y-m-d h:s:i');
            $date = date('Y-m-d h:s:i');

            $account_id = $this->request['ACCOUNTID'];
            $rule_type = 'TARIFFCHARGES';
            $service_number = $this->request['SERVICENUMBER'];
            $billing_date = $service_startdate;
            $unit = 1;
            $rate = 0;
            $cost = 0;
            $totalcost = 0;
            $sallerunit = 1;
            $sallerrate = 0;
            $sallercost = 0;
            $totalsallercost = 0;
            $startdate = $service_startdate;
            $enddate = $service_stopdate;

            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);

            if ($this->debug)
                echo $query_bill_account_sdr . PHP_EOL;
            $this->db->query($query_bill_account_sdr);

            return true;
        } catch (Exception $ex) {
            return $e->getMessage();
        }
    }

    function openingbalance($data) {
        try {
            $this->request = Array();
            foreach ($data as $key => $value) {
                $this->request[$key] = trim($value);
            }
            $service_startdate = date('Y-m-d h:s:i');
            $service_stopdate = date('Y-m-d h:s:i');
            $date = date('Y-m-d h:s:i');

            $account_id = $this->request['ACCOUNTID'];
            $rule_type = 'OPENINGBALANCE';
            $service_number = $this->request['SERVICENUMBER'];
            $billing_date = $service_startdate;
            $unit = 1;
            $rate = 0;
            $cost = 0;
            $totalcost = 0;
            $sallerunit = 1;
            $sallerrate = 0;
            $sallercost = 0;
            $totalsallercost = 0;
            $startdate = $service_startdate;
            $enddate = $service_stopdate;

            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);

            if ($this->debug)
                echo $query_bill_account_sdr . PHP_EOL;
            $this->db->query($query_bill_account_sdr);

            return true;
        } catch (Exception $ex) {
            return $e->getMessage();
        }
    }

    function assign_bundle($data, $date) {
        try {
            $this->request = Array();
            if ($data['REQUEST'] == 'BUNDLECHARGES') {
                $this->request['account_id'] = $data['ACCOUNTID'];
                $query = sprintf("SELECT bundle_package.bundle_package_name, bundle_package.monthly_charges, bundle_package.bundle_package_status, bundle_package.bundle_package_id FROM bundle_package WHERE bundle_package_id = '%s' and bundle_package_status = '1' limit 1", $data['SERVICENUMBER']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $query = $this->db->query($query);
                $bundal_data = $query->result_array();
                if (count($bundal_data) > 0) {
                    foreach ($bundal_data as $fdata) {
                        if ($fdata['monthly_charges'] > 0) {
                            $data['amount'] = $fdata['monthly_charges'];
                            $this->request['amount'] = $fdata['monthly_charges'];
                        } else {
                            $data['amount'] = 0;
                        }
                    }
                    $this->request['service_number'] = $bundal_data[0]['bundle_package_id'];
////////////////// NEED BILLING SYSTEM ////////////
                    $service_startdate = $date;
                    $service_stopdate = date('Y-m-t', strtotime($date));
////////////////// NEED BILLING SYSTEM ////////////
                    $this->request['yearmonth'] = date('Ym', strtotime($date));
                    $this->request['service_charges'] = $this->request['amount'];
                    $this->request['rule_type'] = 'BUNDLECHARGES';
                    $total_cost = $data['amount'];

                    $this->service_startdate = $service_startdate;
                    $this->service_stopdate = $service_stopdate;



                    $this->customertype();
                    if ($this->request['account_type'] == 'RESELLER') {
                        $this->resellerinfo();
                    } else {
                        $this->customerinfo();
                    }
					
					if($this->prorata_billing){
						$total_cost = $this->charges_cal_bundle($total_cost, $service_startdate, $service_stopdate);
					} 
	 						
                    $charges_data = $this->tax_calculation($this->accountinfo, $total_cost);

                    $account_id = $this->request['account_id'];
                    $rule_type = $this->request['rule_type'];
                    $service_number = $data['SERVICEKEY'];
                    $billing_date = $service_startdate;
                    $unit = $data['QUANTITY'];
                    $rate = $this->request['service_charges'];
                    $cost = $charges_data['cost'];
                    $totalcost = $charges_data['total_cost'];
                    $sallerunit = $data['QUANTITY'];
                    $sallerrate = 0;
                    $sallercost = 0;
                    $totalsallercost = 0;
                    $startdate = $service_startdate;
                    $enddate = $service_stopdate;

                    $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);

                    if ($this->debug)
                        echo $query_bill_account_sdr . PHP_EOL;
                    $this->db->query($query_bill_account_sdr);

                    $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $this->db->query($query);
                    $this->data['error'] = '0';
                    $this->data['message'] = 'Bundle Added';
                    return;
                }
                $this->data['error'] = '1';
                $this->data['message'] = 'Wrong Bundle';
                return;
            }
            $this->data['error'] = '1';
            $this->data['message'] = 'Wrong Request';
            return;
        } catch (Exception $ex) {
            $this->data['error'] = '1';
            $this->data['message'] = 'Wrong Bundle';
            return $e->getMessage();
        }
    }

    function check($data) {
        return array('status' => true, 'msg' => 'Ok');
    }

    function monthlycharges($date) {
        $day = date('d', strtotime($date . ' -0 day'));
        $query = sprintf("SELECT bill_customer_priceplan.account_id , account.account_type FROM bill_customer_priceplan INNER JOIN account on account.account_id  = bill_customer_priceplan.account_id  where  (billing_day = '%s' or concat('0', billing_day) = '%s');", $day, $day);
        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $result = $query->result_array();
        if (count($result) > 0) {
            foreach ($result as $data) {
                $this->requesttype = 'SERVICE';
                $this->ServiceMonthlyBundle($data['account_id'], $date, $data['account_type']);
                $this->additionalmonthlycharges($data['account_id'], $date, $data['account_type']);
                $this->ServiceDIDRental($data['account_id'], $date, $data['account_type']);
            }
        }
    }

    function additionalmonthlycharges($account_id, $date, $account_type) {
        $query = sprintf("SELECT lastbilldate, account.account_type, billingeventid, bill_billing_event.account_id, item_id, price_id, item_product_id, sum(if(bill_billing_event.status_id = '1', quantity,0)) - sum(if(bill_billing_event.status_id = '0', quantity,0)) as quantity, start_dt, bill_billing_event.status_id, stop_dt, lastbilldate, lastbill_execute_date, r1lastbilldate, r2lastbilldate, r3lastbilldate, r1lastbill_execute_date , r2lastbill_execute_date, r3lastbill_execute_date from bill_billing_event INNER JOIN account on account.account_id = bill_billing_event.account_id where  bill_billing_event.account_id = '%s' GROUP BY item_id,price_id;", $account_id);


        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $result = $query->result_array();
        if (count($result) > 0) {
            foreach ($result as $data) {
                $data_array = Array();
                $data_array['account_id'] = $data['account_id'];
                $this->rule_type = $data_array['item_id'] = $data['item_id'];
                $data_array['item_product_id'] = $data['item_product_id'];
                $data_array['quantity'] = $data['quantity'];
                $data_array['status_id'] = $data['status_id'];
                $data_array['price_id'] = $data['price_id'];
                $data_array['lastbilldate'] = $data['lastbilldate'];
                $data_array['billingeventid'] = $data['billingeventid'];
                $data['ACCOUNTTYPE'] = $data['account_type'];
                $data['ACCOUNTID'] = $data['account_id'];
                $data['ITEMID'] = $data['item_id'];
                $data['SERVICE'] = '1';
                if ($this->requesttype != 'SERVICE') {
                    if (strlen(trim($data['lastbilldate'])) > 0) {
                        $date1 = date_create($date);
                        $date2 = date_create($data['lastbilldate']);
                        $diff = date_diff($date1, $date2);
                        $daycount = $diff->format("%a");
                    }
                    if ($daycount < 150) {
                        $data_array['lastbilldate'] = $data_array['start_dt'] = $data['lastbilldate'];
                    } else {
                        $data_array['lastbilldate'] = $data_array['start_dt'] = $date;
                    }
                } else {
                    $data_array['lastbilldate'] = $date;
                    $data_array['start_dt'] = $date;
                }
                if ($this->debug)
                    echo $account_id . "  " . $data_array['start_dt'] . " \n\n";
                $this->customer = Array();

                $this->service_billing($data_array, $data);
                $data = Array();
                $data_array = Array();
                $this->customer = Array();
                $this->billingday = 0;
                $this->service_startdate = '';
                $this->service_stopdate = '';
            }
        }
    }

    function generate_string($n) {
        $characters = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    function no_of_month_day($startdate, $billing_noofdays) {
        if ($this->debug)
            echo "$startdate, $billing_noofdays\n";
        $day = date('t', strtotime($startdate));
        $month = date('n', strtotime($startdate));
        $month_b = 0;
        $days_in_month_leap = array(1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31);
        $days_in_month_noleap = array(1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31);
        $data = Array();
        $data['no_month'] = 0;
        $data['no_days'] = $billing_noofdays;
        $data['no_daysinlastmonth'] = $billing_noofdays;
        while (1) {
            if ($this->debug)
                echo "Start date $startdate month which checking $month  & day in month $day  and Doing billing for  $billing_noofdays \n";
            if ($day == $billing_noofdays) {
                $month_b = $month_b + 1;
                $data['no_month'] = $month_b;
                $data['no_days'] = $billing_noofdays - $days_in_month_noleap[$month];
                break;
            } else if ($day < $billing_noofdays) {
                $month_b = $month_b + 1;
                if (date('L', strtotime($startdate)) == 1) {
                    if ($this->debug)
                        echo "L Month $month days $days_in_month_leap[$month] \n";
                    $billing_noofdays = $billing_noofdays - $days_in_month_leap[$month];
                } else {
                    if ($this->debug)
                        echo "NL Month $month days  $days_in_month_noleap[$month] \n";
                    $billing_noofdays = $billing_noofdays - $days_in_month_noleap[$month];
                }
                $startdate = date("Y-m-d", strtotime($startdate . "+$days_in_month_leap[$month] day"));
                $month = date('n', strtotime($startdate));
                if (date('L', strtotime($startdate)) == 1) {
                    $day = $days_in_month_leap[$month];
                } else {
                    $day = $days_in_month_noleap[$month];
                }
                if ($this->debug)
                    echo "Now next Date for calculation $startdate M $month and D $day\n";
                $data['no_month'] = $month_b;
                $data['no_days'] = $billing_noofdays;
            } else {
                $data['no_days'] = $billing_noofdays;
                break;
            }
        }
        return $data;
    }

    function billing_data($startdate, $billingday = 1, $charges = 0) {
        $data = Array();
        $data['startdate'] = $startdate;
        $data['billingday'] = $billingday;
        $data['charges'] = $charges;
        $data['billing_charges'] = $charges;
        $current_timestamp = strtotime('today');
        $current_day = date('d', $current_timestamp);
        $current_month = date('m', $current_timestamp);
        $start_timestamp = strtotime($startdate);
        $start_day = date('d', $start_timestamp);
        $start_month = date('m', $start_timestamp);
        if ($current_day > $billingday) {
            $data['billing_date'] = date("Y-m-$billingday", strtotime("Next Month"));
            $billing_timestamp = strtotime($data['billing_date']);
        } else {
            $data['billing_date'] = date("Y-m-$billingday", $current_timestamp);
            $billing_timestamp = strtotime($data['billing_date']);
        }
        $datetime1 = date_create($startdate);
        $datetime2 = date_create($data['billing_date']);
        if ($startdate == $data['billing_date']) {
            $data['billing_noofdays'] = 1;
            $data['billing_startdate'] = $startdate;
            $data['billing_enddate'] = $data['billing_date'];
        } else {
            $data['billing_noofdays'] = round(abs(strtotime($startdate) - strtotime($data['billing_date'])) / 86400);
            $data['billing_startdate'] = $startdate;
            $data['billing_enddate'] = date("Y-m-d", strtotime($data['billing_date'] . ' -1 day'));
        }

        if ($this->debug)
            echo "Check1 " . $data['billing_noofdays'] . "\n";
        $me_data = $this->no_of_month_day($startdate, $data['billing_noofdays']);
        $data['no_month'] = $me_data['no_month'];
        $data['no_days'] = $me_data['no_days'];
        $data['no_daysinlastmonth'] = $day = date('t', strtotime($data['billing_date']));
        $data['billing_charges_new'] = ($data['no_month'] * $charges) + $data['no_days'] * ($charges / $data['no_daysinlastmonth']);
        $interval = date_diff($datetime1, $datetime2);
        $data['billing_month'] = $interval->m + ($interval->y * 12);
        $data['billing_day'] = $interval->d;
        $nomberordayinendmonth = date("t", strtotime($data['billing_date']));
        $data['billing_charges'] = ($data['billing_month'] * $charges) + $data['billing_day'] * ($charges / $nomberordayinendmonth);
        return $data;
    }

    function start($data) {
        try {
            $this->rule_type = $data['ITEMID'];
            $query = sprintf("select account_id, parent_account_id, account_type, account_level from account where account_id = '%s';", $data['ACCOUNTID']);
            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $result = $query->result_array();
            if (count($result) > 0) {
                foreach ($result as $raw) {
                    $data['ACCOUNTTYPE'] = $raw['account_type'];
                    $data['parent_account_id'] = $raw['parent_account_id'];
                    $data['account_level'] = $raw['account_level'];
                }
            }
            if ($data['ACCOUNTTYPE'] == 'RESELLER' and $data['RECORDTYPE'] == 'rate') {
                $query = sprintf("select billingeventid,  account_id, item_id , price_id, quantity from bill_billing_event where account_id = '%s' and price_id = '%s' and item_id = '%s' and status_id = '1' and record_type = 'rate'  LIMIT 1;", $data['ACCOUNTID'], $data['PRICEID'], $data['ITEMID']);
                $query = $this->db->query($query);
                $result = $query->result_array();
                if (count($result) > 0) {
                    $where = "billingeventid='" . $result['billingeventid'] . "'";
                    $data_array['price_id'] = $data['PRICEID'];
                    $str = $this->db->update_string('bill_billing_event', $data_array, $where);
                    $result = $this->db->query($str);
                    $this->db->last_query;
                    return;
                }

                $data_array['billingeventid'] = $data['ACCOUNTID'] . $data['ITEMID'] . $this->generate_string(6);
                $data_array['account_id'] = $data['ACCOUNTID'];
                $data_array['item_id'] = $data['ITEMID'];
                $data_array['item_product_id'] = $data['ITEMPRODUCTID'];
                $data_array['quantity'] = 0;
                $data_array['status_id'] = '1';
                $data_array['record_type'] = $data['RECORDTYPE'];
                $data_array['start_dt'] = date('Y-m-d');
                $data_array['price_id'] = $data['PRICEID'];
                $data_array['lastbilldate'] = date('Y-m-d');
                $data_array['billingeventid'] = $data['ACCOUNTID'] . $data['ITEMID'] . $this->generate_string(6);
                $data_array['lastbilldate'] = $data_array['start_dt'];
                $data['ACCOUNTTYPE'] = 'RESELLER';
                $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity),price_id=values(price_id)';

                if ($this->debug)
                    echo $str . PHP_EOL;
                $result = $this->db->query($str);
                $this->customer = Array();
                $this->service_billing($data_array, $data);

                return;
            }
            if ($data['ACCOUNTTYPE'] == 'CUSTOMER') {
                $data_array['account_id'] = $data['ACCOUNTID'];
                $data_array['item_id'] = $data['ITEMID'];
                $data_array['item_product_id'] = $data['ITEMPRODUCTID'];
                $data_array['quantity'] = $data['QUANTITY'];
                $data_array['status_id'] = '1';
                $data_array['start_dt'] = date('Y-m-d');
                $data_array['price_id'] = $data['PRICEID'];
                $data_array['record_type'] = 'fixcharge';
                $data_array['lastbilldate'] = date('Y-m-d');
                $data_array['billingeventid'] = $data['ACCOUNTID'] . $data['ITEMID'] . $this->generate_string(6);
                $data_array['lastbilldate'] = $data_array['start_dt'];
                $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity),price_id=values(price_id) ';
                $this->billingeventid = $data_array['billingeventid'];
                if ($this->debug)
                    echo $str . PHP_EOL;
                $result = $this->db->query($str);
                $this->customer = Array();
                $this->service_billing($data_array, $data);
            }
            if ($data['ACCOUNTTYPE'] == 'RESELLER' and $data['RECORDTYPE'] != 'rate') {
                $data_array['billingeventid'] = $data['ACCOUNTID'] . $data['ITEMID'] . $this->generate_string(6);
                $data_array['account_id'] = $data['ACCOUNTID'];
                $data_array['item_id'] = $data['ITEMID'];
                $data_array['item_product_id'] = $data['ITEMPRODUCTID'];
                $data_array['quantity'] = $data['QUANTITY'];
                $data_array['status_id'] = '1';
                $data_array['start_dt'] = date('Y-m-d');
                $data_array['price_id'] = $data['PRICEID'];
                $data_array['record_type'] = $data['RECORDTYPE'];
                $data_array['lastbilldate'] = date('Y-m-d');
                $data_array['billingeventid'] = $data['ACCOUNTID'] . $data['ITEMID'] . $this->generate_string(6);
                $data_array['lastbilldate'] = $data_array['start_dt'];
                $data['ACCOUNTTYPE'] = 'RESELLER';
                $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity),price_id=values(price_id)';
                if ($this->debug)
                    echo $str . PHP_EOL;
                $result = $this->db->query($str);
                $this->customer = Array();
                $this->service_billing($data_array, $data);
                return;
            }

            $query = sprintf("select account_id, parent_account_id, account_type, account_level from account where account_id = '%s';", $data['ACCOUNTID']);
            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $result = $query->result_array();
            if (count($result) > 0) {
                $data['ACCOUNTID'] = $data_array['account_id'] = '';
                foreach ($result as $raw) {
                    $data['ACCOUNTTYPE'] = 'RESELLER';
                    $data['ACCOUNTID'] = $raw['parent_account_id'];
                }
                if (strlen(trim($data['ACCOUNTID'])) == 0) {
                    if ($this->debug)
                        echo "Direct customer Billing Request" . PHP_EOL;
                    return;
                }

                $data_array['account_id'] = $data['ACCOUNTID'];
                $data_array['record_type'] = 'fixcharge';

                $query = sprintf("select price_id from bill_billing_event where account_id = '%s' and item_id = '%s' and record_type = 'rate' limit 1; ", $data['ACCOUNTID'], $data['ITEMID']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $query = $this->db->query($query);
                $result = $query->result_array();
                if (count($result) > 0) {
                    foreach ($result as $raw) {
                        $data_array['price_id'] = $raw['price_id'];
                    }
                }

                $data_array['child_billingeventid'] = $this->billingeventid;
                $data_array['billingeventid'] = $data['ACCOUNTID'] . $data['ITEMID'] . $this->generate_string(6);
                $this->billingeventid = $data_array['billingeventid'];

                $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity)';
                if ($this->debug)
                    echo $str . PHP_EOL;
                $result = $this->db->query($str);
                $this->customer = Array();
                $this->service_billing($data_array, $data);
                $query = sprintf("select account_id, parent_account_id, account_type, account_level from account where account_id = '%s';", $data_array['account_id']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $query = $this->db->query($query);
                $result = $query->result_array();
                if (count($result) > 0) {
                    $data['ACCOUNTID'] = $data_array['account_id'] = '';
                    foreach ($result as $raw) {
                        $data['ACCOUNTID'] = $data_array['account_id'] = $raw['parent_account_id'];
                        $data['ACCOUNTTYPE'] = 'RESELLER';
                    }
                    if (strlen(trim($data['ACCOUNTID'])) == 0) {
                        if ($this->debug)
                            echo "R1 customer Billing Request" . PHP_EOL;
                        return;
                    }


                    $data_array['account_id'] = $data['ACCOUNTID'];
                    $data_array['record_type'] = 'fixcharge';

                    $query = sprintf("select price_id from bill_billing_event where account_id = '%s' and item_id = '%s' and record_type = 'rate' limit 1; ", $data['ACCOUNTID'], $data['ITEMID']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $query = $this->db->query($query);
                    $result = $query->result_array();
                    if (count($result) > 0) {
                        foreach ($result as $raw) {
                            $data_array['price_id'] = $raw['price_id'];
                        }
                    }

                    $data_array['child_billingeventid'] = $this->billingeventid;
                    $data_array['billingeventid'] = $data['ACCOUNTID'] . $data['ITEMID'] . $this->generate_string(6);
                    $this->billingeventid = $data_array['billingeventid'];



                    $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity)';
                    if ($this->debug)
                        echo $str . PHP_EOL;
                    $result = $this->db->query($str);
                    $this->customer = Array();
                    $this->service_billing($data_array, $data);

                    $query = sprintf("select account_id, parent_account_id, account_type, account_level from account where account_id = '%s';", $data_array['account_id']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $query = $this->db->query($query);
                    $result = $query->result_array();
                    if (count($result) > 0) {
                        $data['ACCOUNTID'] = $data_array['account_id'] = '';
                        foreach ($result as $raw) {
                            $data['ACCOUNTID'] = $data_array['account_id'] = $raw['parent_account_id'];
                            $data['ACCOUNTTYPE'] = 'RESELLER';
                        }
                        if (strlen(trim($data['ACCOUNTID'])) == 0) {
                            if ($this->debug)
                                echo "R2 customer Billing Request" . PHP_EOL;
                            return;
                        }


                        $data_array['account_id'] = $data['ACCOUNTID'];
                        $data_array['record_type'] = 'fixcharge';
                        $data_array['child_billingeventid'] = $this->billingeventid;
                        $data_array['billingeventid'] = $data['ACCOUNTID'] . $data['ITEMID'] . $this->generate_string(6);
                        $this->billingeventid = $data_array['billingeventid'];

                        $query = sprintf("select price_id from bill_billing_event where account_id = '%s' and item_id = '%s' and record_type = 'rate' limit 1;", $data['ACCOUNTID'], $data['ITEMID']);
                        if ($this->debug)
                            echo $query . PHP_EOL;
                        $query = $this->db->query($query);
                        $result = $query->result_array();
                        if (count($result) > 0) {
                            foreach ($result as $raw) {
                                $data_array['price_id'] = $raw['price_id'];
                            }
                        }
                        $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity)';
                        if ($this->debug)
                            echo $query . PHP_EOL;
                        $result = $this->db->query($str);
                        $this->customer = Array();
                        $this->service_billing($data_array, $data);
                    }
                }
            }
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function service_billing($data_array, $data) {
        try {
            if ($data['SERVICE'] == '1') {
                $data_array['ITEMID'] = $this->rule_type = $data['ITEMID'];
            }
            if (strlen(trim($data['ACCOUNTID'])) == 0) {
                if ($this->debug)
                    echo "Account is missining" . PHP_EOL;
                return;
            }
            $query = sprintf("SELECT plugin_system_name FROM `plugins` where plugin_system_name = 'billing';");
            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $billingmodule = $query->result_array();

            if (count($billingmodule) > 0) {
                foreach ($billingmodule as $billingdata) {
                    $plugin_system_name = $billingdata['plugin_system_name'];
                }
            }
            $account_id = $data['ACCOUNTID'];
            if ($plugin_system_name == 'billing') {
                $query = sprintf("SELECT billing_day FROM bill_customer_priceplan where account_id = '%s';", $account_id);

                if ($this->debug)
                    echo $query . PHP_EOL;
                $query = $this->db->query($query);
                $billingmodule = $query->result_array();

                if (count($billingmodule) > 0) {
                    foreach ($billingmodule as $billingdata) {
                        if ($billingdata['billing_day'] < 10) {
                            $billingday = '0' . $billingdata['billing_day'];
                        } else {
                            $billingday = $billingdata['billing_day'];
                        }
                        $this->billingday = $billingday;
                        $date = $data_array['lastbilldate'];
                        $this->service_startdate = $date;
                        $charges = 0;
                        $data_billdate = $this->billing_data($date, $billingday, $charges);
                        $this->service_stopdate = $data_billdate['billing_enddate'];
                    }
                }
            } else {
                $this->billingday = '1';
                $this->service_startdate = date('Y-m-01');
                $this->service_stopdate = date('Y-m-t');
            }

            if ($this->requesttype == 'SERVICE') {
                if ($billingdata['billing_day'] == '1') {
                    $this->service_startdate = date('Y-m-01');
                    $this->service_stopdate = date('Y-m-t');
                } else {
                    $this->service_startdate = date("Y-m-$billingday");
                    $this->service_stopdate = date("Y-m-d", strtotime($this->service_startdate . ' +1 month'));
                }
            }


            if ($this->debug)
                echo "Server start " . $this->service_startdate . " Stop date " . $this->service_stopdate . " and billing day " . $billingday . " start_dt date " . $data_array['start_dt'] . PHP_EOL;

            if ($data['ACCOUNTTYPE'] == 'CUSTOMER') {
                $this->cusrtomer_servicebill($data_array);
                $this->bill_sdr_usage_data($data_array);
            }

            if ($data['ACCOUNTTYPE'] == 'RESELLER') {
                if (strlen($data['ACCOUNTTYPE']) > 0) {
                    $this->reseller_servicebill($data_array, $account_id);
                    $this->bill_sdr_usage_data($data_array);
                }
            }

            if ($result) {
                return array('status' => true, 'msg' => "Service Charges Applied");
            } else {
                $error_array = $this->db->error();
                return array('status' => false, 'msg' => $error_array['message']);
            }
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function account_service($bill_account_sdr_data) {

        $account_id = $bill_account_sdr_data['account_id'];
        $rule_type = $bill_account_sdr_data['rule_type'];
        $service_number = $bill_account_sdr_data['service_number'];
        $billing_date = $bill_account_sdr_data['action_date'];
        $unit = $bill_account_sdr_data['unit'];
        $rate = $bill_account_sdr_data['rate'];
        $cost = $bill_account_sdr_data['cost'];
        $totalcost = $bill_account_sdr_data['total_cost'];
        $sallerunit = $bill_account_sdr_data['sallerunit'];
        $sallerrate = $bill_account_sdr_data['sallerrate'];
        $sallercost = $bill_account_sdr_data['cost'];
        $totalsallercost = $bill_account_sdr_data['total_cost'];
        $startdate = $bill_account_sdr_data['service_startdate'];
        $enddate = $bill_account_sdr_data['service_stopdate'];

        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);

        if ($this->debug)
            echo $query_bill_account_sdr . "\n";
        $this->db->query($query_bill_account_sdr);
    }

    function stop($data) {
        try {
            $this->db->trans_begin();
            if (trim($data['QUANTITY']) < 1) {
                $data['QUANTITY'] = 1;
            }
            if (trim($data['QUANTITY']) > 0) {
                $where = "account_id='" . $data['ACCOUNTID'] . "'";
                $where .= "  and billingeventid='" . $data['BILLINGEVENTID'] . "'";
                $data_array['stop_dt'] = date('Y-m-d');
                $data_array['event_delete_status'] = "1";
                $str = $this->db->update_string('bill_billing_event', $data_array, $where);
                if ($this->debug)
                    echo $str . PHP_EOL;
                $this->itemid = $data['ITEMID'];
                $this->quantity = $data['QUANTITY'];
                $result = $this->db->query($str);


                $data_array = array();
                $data_array['account_id'] = $data['ACCOUNTID'];
                $data_array['item_id'] = $data['ITEMID'];
                $data_array['item_product_id'] = $data['ITEMPRODUCTID'];
                $data_array['quantity'] = $data['QUANTITY'];
                $data_array['status_id'] = '0';
                $data_array['start_dt'] = date('Y-m-d');
                $data_array['price_id'] = $data['PRICEID'];
                $data_array['record_type'] = $data['RECORDTYPE'];
                $data_array['lastbilldate'] = date('Y-m-d');
                $data_array['event_delete_status'] = '1';
                $data_array['child_billingeventid'] = $data['BILLINGEVENTID'];
                $data_array['billingeventid'] = $data['ACCOUNTID'] . $data['ITEMID'] . $this->generate_string(6);
                $data_array['lastbilldate'] = $data_array['start_dt'];
                $this->billingeventid = $data['BILLINGEVENTID'];
                $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity),price_id=values(price_id) ';
                if ($this->debug)
                    echo $str . PHP_EOL;
                $result = $this->db->query($str);


                $query = sprintf("select account_id, parent_account_id, account_type, account_level from account where account_id = (select parent_account_id from account where account_id = '%s')", $data['ACCOUNTID']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $query = $this->db->query($query);
                $result = $query->result_array();
                $data = Array();
                if (count($result) > 0) {
                    foreach ($result as $raw) {
                        $data['ACCOUNTTYPE'] = $raw['account_type'];
                        $data['account_id'] = $raw['account_id'];
                        $data['account_level'] = $raw['account_level'];
                    }

                    if ($data['ACCOUNTTYPE'] == 'RESELLER') {
                        $query = sprintf("select billingeventid , price_id  from bill_billing_event where child_billingeventid = '%s'  limit 1; ", $this->billingeventid);
                        if ($this->debug)
                            echo $query . PHP_EOL;
                        $query = $this->db->query($query);
                        $result = $query->result_array();
                        if (count($result) > 0) {
                            foreach ($result as $raw) {
                                $this->billingeventid = $raw['billingeventid'];
                                $this->price_id = $raw['price_id'];
                            }
                        }

                        $data_array = Array();
                        $where = "  billingeventid='" . $this->billingeventid . "'";
                        $data_array['stop_dt'] = date('Y-m-d');
                        $data_array['event_delete_status'] = "1";
                        $str = $this->db->update_string('bill_billing_event', $data_array, $where);
                        $result = $this->db->query($str);

                        if ($this->debug)
                            echo $str . PHP_EOL;

                        $data_array = array();
                        $data_array['account_id'] = $data['account_id'];
                        $data_array['item_id'] = $this->itemid;
                        $data_array['quantity'] = $this->quantity;
                        $data_array['status_id'] = '0';
                        $data_array['start_dt'] = date('Y-m-d');
                        $data_array['price_id'] = $this->price_id;
                        $data_array['record_type'] = 'fixcharge';
                        $data_array['lastbilldate'] = date('Y-m-d');
                        $data_array['event_delete_status'] = '1';
                        $data_array['billingeventid'] = $data['account_id'] . $this->itemid . $this->generate_string(6);
                        $data_array['lastbilldate'] = $data_array['start_dt'];
                        $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity),price_id=values(price_id) ';
                        if ($this->debug)
                            echo $str . PHP_EOL;
                        $result = $this->db->query($str);

                        $query = sprintf("select account_id, parent_account_id, account_type, account_level from account where account_id = (select parent_account_id from account where account_id = '%s');", $data['account_id']);
                        if ($this->debug)
                            echo $query . PHP_EOL;
                        $query = $this->db->query($query);
                        $result = $query->result_array();
                        $data = Array();
                        if (count($result) > 0) {
                            foreach ($result as $raw) {
                                $data['ACCOUNTTYPE'] = $raw['account_type'];
                                $data['account_id'] = $raw['account_id'];
                                $data['account_level'] = $raw['account_level'];
                            }
                        }
                        if ($data['ACCOUNTTYPE'] == 'RESELLER') {
                            $query = sprintf("select billingeventid , price_id  from bill_billing_event where child_billingeventid = '%s'  limit 1; ", $this->billingeventid);
                            if ($this->debug)
                                echo $query . PHP_EOL;
                            $query = $this->db->query($query);
                            $result = $query->result_array();
                            if (count($result) > 0) {
                                foreach ($result as $raw) {
                                    $this->billingeventid = $raw['billingeventid'];
                                    $this->price_id = $raw['price_id'];
                                }



                                $where = "   billingeventid='" . $this->billingeventid . "'";
                                $data_array['stop_dt'] = date('Y-m-d');
                                $data_array['event_delete_status'] = "1";
                                $str = $this->db->update_string('bill_billing_event', $data_array, $where);
                                if ($this->debug)
                                    echo $str . PHP_EOL;
                                $result = $this->db->query($str);

                                $data_array = array();

                                $data_array['account_id'] = $data['account_id'];
                                $data_array['item_id'] = $this->itemid;
                                $data_array['quantity'] = $this->quantity;
                                $data_array['status_id'] = '0';
                                $data_array['start_dt'] = date('Y-m-d');
                                $data_array['price_id'] = $this->price_id;
                                $data_array['record_type'] = 'fixcharge';
                                $data_array['lastbilldate'] = date('Y-m-d');
                                $data_array['event_delete_status'] = '1';
                                $data_array['billingeventid'] = $data['account_id'] . $this->itemid . $this->generate_string(6);

                                $data_array['lastbilldate'] = $data_array['start_dt'];
                                $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity),price_id=values(price_id) ';
                                if ($this->debug)
                                    echo $str . PHP_EOL;
                                $result = $this->db->query($str);
                                $query = sprintf("select account_id, parent_account_id, account_type, account_level from account where account_id = (select parent_account_id from account where account_id = '%s');", $data['account_id']);
                                if ($this->debug)
                                    echo $query . PHP_EOL;
                                $query = $this->db->query($query);
                                $result = $query->result_array();
                                $data = Array();
                                if (count($result) > 0) {
                                    foreach ($result as $raw) {
                                        $data['ACCOUNTTYPE'] = $raw['account_type'];
                                        $data['account_id'] = $raw['account_id'];
                                        $data['account_level'] = $raw['account_level'];
                                    }

                                    if ($data['ACCOUNTTYPE'] == 'RESELLER') {
                                        $query = sprintf("select billingeventid , price_id  from bill_billing_event where child_billingeventid = '%s'  limit 1; ", $this->billingeventid);
                                        if ($this->debug)
                                            echo $query . PHP_EOL;
                                        $query = $this->db->query($query);
                                        $result = $query->result_array();
                                        $data = Array();
                                        if (count($result) > 0) {
                                            foreach ($result as $raw) {
                                                $this->billingeventid = $raw['billingeventid'];
                                                $this->price_id = $raw['price_id'];
                                            }
                                        }

                                        $data_array = Array();
                                        $where = "  billingeventid='" . $this->billingeventid . "'";
                                        $data_array['stop_dt'] = date('Y-m-d');
                                        $data_array['event_delete_status'] = "1";
                                        $str = $this->db->update_string('bill_billing_event', $data_array, $where);
                                        if ($this->debug)
                                            echo $str . PHP_EOL;
                                        $result = $this->db->query($str);

                                        $data_array = array();
                                        $data_array['account_id'] = $data['account_id'];
                                        $data_array['item_id'] = $this->itemid;
                                        $data_array['quantity'] = $this->quantity;
                                        $data_array['status_id'] = '0';
                                        $data_array['start_dt'] = date('Y-m-d');
                                        $data_array['price_id'] = $this->price_id;
                                        $data_array['record_type'] = 'fixcharge';
                                        $data_array['lastbilldate'] = date('Y-m-d');
                                        $data_array['event_delete_status'] = '1';
                                        $data_array['billingeventid'] = $data['account_id'] . $this->itemid . $this->generate_string(6);
                                        $data_array['lastbilldate'] = $data_array['start_dt'];
                                        $str = $this->db->insert_string('bill_billing_event', $data_array) . ' ON DUPLICATE KEY UPDATE quantity=quantity + values(quantity),price_id=values(price_id) ';
                                        if ($this->debug)
                                            echo $str . PHP_EOL;
                                        $result = $this->db->query($str);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($this->db->trans_status() === FALSE) {
                $error_array = $this->db->error();
                $this->db->trans_rollback();
                return array('status' => false, 'msg' => $error_array['message']);
            } else {
                $this->db->trans_commit();
                return array('status' => true, 'msg' => 'Billing stoped');
            }
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function bill_sdr_usage_data($data_array) {
        $data = '';
        foreach ($this->customer as $userkey => $uservalue) {
            if ($userkey == 'customer') {
                foreach ($uservalue as $key => $value) {
                    if ($key == 'account_id') {
                        $account_id = addslashes($value);
                        $data = $data . "account_id = '" . addslashes($value) . "',";
                    }

                    if ($key == 'destination') {
                        $data = $data . "service_number = '" . addslashes($value) . "',";
                    }
                }
            }
            if ($userkey == 'reseller1') {
                foreach ($uservalue as $key => $value) {
                    if ($key == 'account_id') {
                        $account_id = addslashes($value);
                        $data = $data . "account_id = '" . addslashes($value) . "',";
                    }

                    if ($key == 'destination') {
                        $data = $data . "service_number = '" . addslashes($value) . "',";
                    }
                }
            }
            if ($userkey == 'reseller2') {
                foreach ($uservalue as $key => $value) {
                    if ($key == 'account_id') {
                        $account_id = addslashes($value);
                        $data = $data . "account_id = '" . addslashes($value) . "',";
                    }

                    if ($key == 'destination') {
                        $data = $data . "service_number = '" . addslashes($value) . "',";
                    }
                }
            }
            if ($userkey == 'reseller3') {
                foreach ($uservalue as $key => $value) {
                    if ($key == 'account_id') {
                        $account_id = addslashes($value);
                        $data = $data . "account_id = '" . addslashes($value) . "',";
                    }

                    if ($key == 'destination') {
                        $data = $data . "service_number = '" . addslashes($value) . "',";
                    }
                }
            }
        }

        $customerinfo['unit'] = '0';
        $customerinfo['rate'] = '0';
        $customerinfo['cost'] = '0';
        $customerinfo['totalcost'] = '0';
        $customerinfodata = $this->buyercost($data_array, $account_id);
        $data = $data . "unit = '" . $customerinfodata['unit'] . "',";
        $data = $data . "rate = '" . $customerinfodata['rate'] . "',";
        $data = $data . "cost = '" . $customerinfodata['cost'] . "',";
        $data = $data . "totalcost = '" . $customerinfodata['totalcost'] . "',";
        $sallercostdata['sallerunit'] = '0';
        $sallercostdata['sallerrate'] = '0';
        $sallercostdata['sallercost'] = '0';
        $sallercostdata['totalsallercost'] = '0';


        $sallercostdata = $this->sallercost($data_array, $account_id);
        $data = $data . "sallerunit = '" . $sallercostdata['sallerunit'] . "',";
        $data = $data . "sallerrate = '" . $sallercostdata['sallerrate'] . "',";
        $data = $data . "sallercost = '" . $sallercostdata['sallercost'] . "',";
        $data = $data . "totalsallercost = '" . $sallercostdata['totalsallercost'] . "',";
        $data = $data . "billing_date = '" . date('Y-m-d', strtotime($data_array['start_dt'] . ' -1 day')) . "',";
        $data = $data . "rule_type = '" . $this->rule_type . "',";
        $data = $data . "createdate = '" . date('Y-m-d') . "',";
        $data = $data . "startdate = '" . $data_array['start_dt'] . "',";
        $data = $data . "enddate = '" . date('Y-m-d', strtotime($data_array['start_dt'] . ' +1 month')) . "',";


        $data = rtrim($data, ',');
        $data = "insert into bill_account_sdr  set " . $data . ";";
        if ($this > debug)
            echo $data . PHP_EOL;
        $this->db->query($data);
    }

    function cusrtomer_servicebill($data_array) {
        if (strlen(trim($data_array['price_id'])) > 0) {
            $account_id = $data_array['account_id'];
            $this->customer['customer']['account_id'] = $data_array['account_id'];
            $this->customer['customer']['item_id'] = $data_array['item_id'];
            $this->customer['customer']['item_product_id'] = $data_array['item_product_id'];
            $this->customer['customer']['quantity'] = $data_array['quantity'];
            $this->customer['customer']['status_id'] = $data_array['status_id'];
            $this->customer['customer']['start_dt'] = $data_array['start_dt'];
            $this->customer['customer']['price_id'] = $data_array['price_id'];
            $this->service_startdate = $data_array['start_dt'];
            $query = sprintf("SELECT  customers.emailaddress, customers.company_name, account.currency_id, account.dp, customer_voipminuts.tariff_id, account.tax3, account.tax2, account.tax1, account.tax_type, parent_account_id, account_level  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id INNER JOIN customers on customers.account_id= account.account_id  WHERE account.account_id = '%s' and account.account_id not in ('-3','-4') limit 1;", $this->customer['customer']['account_id']);
            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $result = $query->row_array();

            if (count($result) > 0) {
                foreach ($result as $key => $value) {
                    $this->customer['customer'][$key] = $value;
                }
            }

           
            $query = sprintf("select  bill_billing_event.price_id, bill_billing_event.item_id,  bill_pricelist.currency_id, bill_pricelist.description, bill_pricelist.reguler_charges, bill_pricelist.free_item, bill_pricelist.charges, bill_pricelist.additional_charges_as, bill_pricelist.additional_charges, 'CUSTOM' priceplan_id  from bill_billing_event  INNER JOIN bill_pricelist on bill_pricelist.price_id = bill_billing_event.price_id where bill_billing_event.account_id  = '%s' and bill_billing_event.item_id = '%s' and bill_billing_event.price_id = '%s';", $this->customer['customer']['account_id'], $this->customer['customer']['item_id'], $this->customer['customer']['price_id']);


            if ($this->debug)
                echo $query . PHP_EOL;
            $result = array();
            $rate_query = $this->db->query($query);
            $result = $rate_query->row_array();
            if ($this->debug)
                echo print_r($result);
            if (count($result) > 0) {
                foreach ($result as $key => $value) {
                    $this->customer['customer'][$key] = $value;
                }
            } else {
                $query = sprintf("SELECT bill_pricelist_customer.price_id, bill_pricelist_customer.item_id, bill_pricelist_customer.currency_id, bill_pricelist_customer.description, bill_pricelist_customer.reguler_charges,bill_pricelist_customer.free_item, bill_pricelist_customer.charges, bill_pricelist_customer.additional_charges_as, bill_pricelist_customer.additional_charges, 'ADDONS' priceplan_id from bill_pricelist_customer   where bill_pricelist_customer.customer_account_id = '%s'  and bill_pricelist_customer.item_id = '%s' and bill_pricelist_customer.price_id = '%s' limit 1;", $this->customer['customer']['account_id'], $this->customer['customer']['item_id'], $this->customer['customer']['price_id']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $rate_query = $this->db->query($query);
                $result = $rate_query->row_array();
                if ($this->debug)
                    echo print_r($result);
                if (count($result) > 0) {
                    foreach ($result as $key => $value) {
                        $this->customer['customer'][$key] = $value;
                    }
                } else {
                    $query = sprintf("SELECT bill_pricelist.price_id, bill_pricelist.item_id, bill_pricelist.currency_id, bill_pricelist.description, bill_pricelist.reguler_charges, bill_pricelist.free_item, bill_pricelist.charges, bill_pricelist.additional_charges_as, bill_pricelist.additional_charges, bill_priceplan_item.priceplan_id from bill_priceplan_item   INNER JOIN bill_pricelist on bill_priceplan_item.price_id = bill_pricelist.price_id WHERE bill_priceplan_item.priceplan_id in (SELECT priceplan_id from bill_customer_priceplan where account_id = '%s') and bill_pricelist.item_id = '%s'  and bill_pricelist.price_id = '%s' limit 1;", $this->customer['customer']['account_id'], $this->customer['customer']['item_id'], $this->customer['customer']['price_id']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $rate_query = $this->db->query($query);
                    $result = $rate_query->row_array();
                    if ($this->debug)
                        echo print_r($result);
                    if (count($result) > 0) {
                        foreach ($result as $key => $value) {
                            $this->customer['customer'][$key] = $value;
                        }
                    }
                }
            }
            if ($this->customer['customer']['reguler_charges'] == 'EMA') {
                $query = sprintf("SELECT count(id) count_ema  FROM `bill_sdrdata` where account_id = '%s' and item_id = '%s';", $this->customer['customer']['account_id'], $this->customer['customer']['item_id']);
                if ($this->debug)
                    echo $query . PHP_EOL;

                $ema_query = $this->db->query($query);
                $result = Array();
                $result = $ema_query->row_array();
                if (count($result) > 0) {
                    foreach ($result as $data) {
                        if ($data['count_ema'] > 0) {
                            return;
                        }
                    }
                }
            } elseif ($this->customer['customer']['reguler_charges'] == 'NA') {
                $this->customer['customer']['regular_cost'] = 0;
                $this->customer['customer']['cost'] = 0;
                $this->customer['customer']['total_cost'] = 0;
                $this->customer['customer']['tax1_cost'] = 0;
                $this->customer['customer']['tax2_cost'] = 0;
                $this->customer['customer']['tax3_cost'] = 0;
                $this->customer['customer']['quantity'] = $this->customer['customer']['quantity'];
                $this->customer['customer']['destination'] = $this->customer['customer']['item_id'];
                $this->customer['customer']['charges'] = 0;
            }
            if ($this->debug)
                print_r($this->customer);

            $charges2 = $charges = $this->customer['customer']['charges'];
            if ($this->customer['customer']['additional_charges_as'] == 'SE') {
                $this->customer['customer']['setup_cost'] = $this->customer['customer']['additional_charges'];
                $charges = $charges + $this->customer['customer']['setup_cost'];
            } elseif ($this->customer['customer']['additional_charges_as'] == 'NA') {
                $this->customer['customer']['setup_cost'] = 0;
            }
            if ($this->requesttype == 'SERVICE') {
                $data_billdate['billing_charges_new'] = $charges2;
            } else {
                $data_billdate = $this->billing_data($this->service_startdate, $this->billingday, $charges);
            }
            if ($this->debug)
                print_r($data_billdate);
            $this->customer['customer']['cost'] = $data_billdate['billing_charges_new'];
            $customer_cost = $this->customer['customer']['cost'] = $this->dp($this->customer['customer']['cost'] * $this->customer['customer']['quantity'], $this->customer['customer']['dp']);
            if ($this->customer['customer']['tax_type'] == 'exclusive') {
                $tax = $this->customer['customer']['tax1'] + $this->customer['customer']['tax2'] + $this->customer['customer']['tax3'];
                if ($this->debug)
                    echo "Total Tax % will apply $tax" . PHP_EOL;
                $total_tax = $this->exclusive_tax($tax, $this->customer['customer']['cost'], 100);
                if ($this->debug)
                    echo "Total Tax $total_tax" . PHP_EOL;
                $total_tax = $this->dp($total_tax, $this->customer['customer']['dp']);
                $customer_tax1_cost = $this->exclusive_tax($this->customer['customer']['tax1'], $total_tax, $tax);
                if ($this->debug)
                    echo "Total exclusive_tax  $customer_tax1_cost " . PHP_EOL;
                $customer_tax1_cost = $this->dp($customer_tax1_cost, $this->customer['customer']['dp']);
                if ($this->debug)
                    echo "customer_tax1_cost  $customer_tax1_cost " . PHP_EOL;
                $customer_tax2_cost = $this->exclusive_tax($this->customer['customer']['tax2'], $total_tax, $tax);
                $customer_tax2_cost = $this->dp($customer_tax2_cost, $this->customer['customer']['dp']);
                if ($this->debug)
                    echo "customer_tax2_cost  $customer_tax2_cost " . PHP_EOL;
                $customer_tax3_cost = $this->exclusive_tax($this->customer['customer']['tax3'], $total_tax, $tax);
                $customer_tax3_cost = $this->dp($customer_tax3_cost, $this->customer['customer']['dp']);
                if ($this->debug)
                    echo "customer_tax3_cost  $customer_tax3_cost " . PHP_EOL;
                $customer_callcost_total = $customer_tax1_cost + $customer_tax2_cost + $customer_tax3_cost + $customer_cost;
                if ($this->debug)
                    echo "Cost    $customer_callcost_total $customer_tax1_cost  $customer_tax2_cost   $customer_tax3_cost  $customer_cost; " . PHP_EOL;
                $customer_callcost_total = $this->dp($customer_callcost_total, $this->customer['customer']['dp']);
            } else if ($this->customer['customer']['tax_type'] == 'inclusive') {
                $tax = $this->customer['customer']['tax1'] + $this->customer['customer']['tax2'] + $this->customer['customer']['tax3'];
                $total_tax = $this->inclusive_tax($tax, $customer_cost, 100);
                $total_tax = $this->dp($total_tax, $this->customer['customer']['dp']);
                $customer_tax1_cost = $this->exclusive_tax($this->customer['customer']['tax1'], $total_tax, $tax);
                $customer_tax1_cost = $this->dp($customer_tax1_cost, $this->customer['customer']['dp']);
                $customer_tax2_cost = $this->exclusive_tax($this->customer['customer']['tax2'], $total_tax, $tax);
                $customer_tax2_cost = $this->dp($customer_tax2_cost, $this->customer['customer']['dp']);
                $customer_tax3_cost = $this->exclusive_tax($this->customer['customer']['tax3'], $total_tax, $tax);
                $customer_tax3_cost = $this->dp($customer_tax3_cost, $this->customer['customer']['dp']);
                $customer_callcost_total = $customer_cost;
                $customer_callcost_total = $this->dp($customer_callcost_total, $this->customer['customer']['dp']);
                $customer_cost = $customer_callcost_total - $customer_tax1_cost - $customer_tax2_cost - $customer_tax3_cost;
                $customer_cost = $this->dp($customer_cost, $this->customer['customer']['dp']);
            }
            $this->customer['customer']['cost'] = $customer_cost;
            $this->customer['customer']['total_cost'] = $customer_callcost_total;
            $this->customer['customer']['tax1_cost'] = $customer_tax1_cost;
            $this->customer['customer']['tax2_cost'] = $customer_tax2_cost;
            $this->customer['customer']['tax3_cost'] = $customer_tax3_cost;
            $this->customer['customer']['quantity'] = $this->customer['customer']['quantity'];
            $this->customer['customer']['destination'] = $this->customer['customer']['item_id'];
            $this->customer['customer']['rate'] = $this->customer['customer']['charges'];


            if ($this->debug)
                print_r($this->customer);
            return;
        }
    }

    function dp($number, $dp) {
        return abs(number_format(ceil($number * pow(10, $dp)) / pow(10, $dp), $dp, '.', ''));
    }

    function exclusive_tax($tax, $carrier_cost, $taxon = 100) {
        if ($this->debug)
            echo "exclusive_tax :: Total Tax $tax cost $carrier_cost taxon $taxon" . PHP_EOL;
        $tax_amount = 0;
        if ($tax > 0 and $carrier_cost > 0)
            $tax_amount = (($carrier_cost * $tax) / $taxon);
        if ($this->debug)
            echo "exclusive_tax :: Total Tax $tax cost $carrier_cost taxon $taxon  tax_amount $tax_amount" . PHP_EOL;
        return $tax_amount;
    }

    function inclusive_tax($tax, $carrier_cost, $taxon = 100) {
        $tax_amount = 0;
        if ($tax > 0 and $carrier_cost > 0)
            $tax_amount = ($carrier_cost / ($taxon + $tax)) * $tax;
        return $tax_amount;
    }

    function buyercost($data_array, $account_id) {
        $customerinfo['unit'] = '0';
        $customerinfo['rate'] = '0';
        $customerinfo['cost'] = '0';
        $customerinfo['totalcost'] = '0';
        foreach ($data_array as $key => $value) {
            $customerinfo[$key] = $value;
        }
        $query = sprintf(" SELECT account_id, parent_account_id, account_type, account_level FROM account WHERE account_id= '%s' limit 1;", $account_id);
        if ($this->debug)
            echo "Checking Parent $account_id :  $query " . PHP_EOL;

        $query = $this->db->query($query);
        $reseller = $query->row_array();

        if (strlen(trim($reseller['account_id'])) > 0) {
            $account_id = $reseller['account_id'];
        } else {
            return $customerinfo;
        }


        if (count($reseller) > 0) {
            foreach ($reseller as $key => $value) {
                $customerinfo[$key] = $value;
            }
        }

        $customerinfo['account_id'] = $customerinfo['account_id'];
        $query = sprintf("SELECT billingeventid, account_id, item_id, price_id, item_product_id, start_dt, status_id, stop_dt, lastbilldate, record_type, lastbill_execute_date, r1lastbilldate, r2lastbilldate, r3lastbilldate, r1lastbill_execute_date, r2lastbill_execute_date, r3lastbill_execute_date FROM   bill_billing_event WHERE account_id= '%s' and item_id = '%s' limit 1;", $customerinfo['account_id'], $data_array['item_id']);

        if ($this->debug)
            echo "Rate seting of $account_id :  $query " . PHP_EOL;
        $query = $this->db->query($query);
        $reseller = $query->row_array();

        if (strlen(trim($reseller['item_id'])) > 0) {
            
        } else {
            return $customerinfo;
        }

        if (count($reseller) > 0) {
            foreach ($reseller as $key => $value) {
                $customerinfo[$key] = $value;
                if ($this->debug)
                    echo "SQL2 $key :  $value " . PHP_EOL;
            }
        }

        $query = sprintf("SELECT  account.dp,   account.currency_id, account.dp, customer_voipminuts.tariff_id, account.tax3, account.tax2, account.tax1, account.tax_type, account_level  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id  WHERE account.account_id = '%s' and account.account_id not in ('-3','-4') limit 1;", $customerinfo['account_id']);

        if ($this->debug)
            echo $query . PHP_EOL;

        $query = $this->db->query($query);
        $reseller = $query->row_array();

        if (count($reseller) > 0) {
            foreach ($reseller as $key => $value) {
                $customerinfo[$key] = $value;
                if ($this->debug)
                    echo "SQL3 $key :  $value " . PHP_EOL;
            }
        }

      
        $query = sprintf("select  bill_billing_event.price_id, bill_billing_event.item_id,  bill_pricelist.currency_id, bill_pricelist.description, bill_pricelist.reguler_charges, bill_pricelist.free_item, bill_pricelist.charges, bill_pricelist.additional_charges_as, bill_pricelist.additional_charges, 'CUSTOM' priceplan_id  from bill_billing_event  INNER JOIN bill_pricelist on bill_pricelist.price_id = bill_billing_event.price_id where bill_billing_event.account_id  = '%s' and bill_billing_event.item_id = '%s' and bill_billing_event.price_id = '%s';", $customerinfo['account_id'], $data_array['item_id'], $customerinfo['price_id']);



        if ($this->debug)
            echo $query . PHP_EOL;

        $query = $this->db->query($query);
        $reseller = $query->row_array();

        if (count($reseller) > 0) {
            foreach ($reseller as $key => $value) {
                $customerinfo[$key] = $value;
                if ($this->debug)
                    echo "SQL4 $key :  $value " . PHP_EOL;
            }
        } else {
            $query = sprintf("SELECT bill_pricelist_customer.price_id, bill_pricelist_customer.item_id, bill_pricelist_customer.currency_id, bill_pricelist_customer.description, bill_pricelist_customer.reguler_charges,bill_pricelist_customer.free_item, bill_pricelist_customer.charges, bill_pricelist_customer.additional_charges_as, bill_pricelist_customer.additional_charges, 'ADDONS' priceplan_id from bill_pricelist_customer   where bill_pricelist_customer.customer_account_id = '%s'  and bill_pricelist_customer.item_id = '%s'  and bill_pricelist_customer.price_id = '%s' limit 1;", $customerinfo['account_id'], $customerinfo['item_id'], $customerinfo['price_id']);

            $query = $this->db->query($query);
            $reseller = $query->row_array();

            if (count($reseller) > 0) {
                foreach ($reseller as $key => $value) {
                    $customerinfo[$key] = $value;
                    if ($this->debug)
                        echo "SQL5 $key :  $value " . PHP_EOL;
                }
            } else {
                $query = sprintf("SELECT bill_pricelist.price_id, bill_pricelist.item_id, bill_pricelist.currency_id, bill_pricelist.description, bill_pricelist.reguler_charges, bill_pricelist.free_item, bill_pricelist.charges, bill_pricelist.additional_charges_as, bill_pricelist.additional_charges, bill_priceplan_item.priceplan_id from bill_priceplan_item   INNER JOIN bill_pricelist on bill_priceplan_item.price_id = bill_pricelist.price_id WHERE bill_priceplan_item.priceplan_id in (SELECT priceplan_id from bill_customer_priceplan where account_id = '%s') and bill_pricelist.item_id = '%s'  and bill_pricelist.price_id = '%s' limit 1;", $customerinfo['account_id'], $customerinfo['item_id'], $customerinfo['price_id']);

                if ($this->debug)
                    echo $query . PHP_EOL;

                $query = $this->db->query($query);
                $reseller = $query->row_array();

                if (count($reseller) > 0) {
                    foreach ($reseller as $key => $value) {
                        $customerinfo[$key] = $value;
                        if ($this->debug)
                            echo "SQL6 $key :  $value " . PHP_EOL;
                    }
                }
            }
        }

        if ($customerinfo['reguler_charges'] == 'EMA') {
            $query = sprintf("SELECT count(id) count_ema  FROM  bill_account_sdr where account_id = '%s' and rule_type = '%s';", $customerinfo['account_id'], $customerinfo['item_id']);

            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $reseller = $query->row_array();
            if (count($reseller) > 0) {
                foreach ($reseller as $data) {
                    if ($data['count_ema'] > 0) {
                        return $customerinfo;
                    }
                }
            }
        } elseif ($customerinfo['reguler_charges'] == 'NA') {
            $customerinfo['regular_cost'] = 0;
            $customerinfo['cost'] = 0;
            $customerinfo['totalcost'] = 0;
            $customerinfo['tax1_cost'] = 0;
            $customerinfo['tax2_cost'] = 0;
            $customerinfo['tax3_cost'] = 0;
            $customerinfo['charges'] = 0;
            $customerinfo['quantity'] = $customerinfo['quantity'];
            $customerinfo['destination'] = $customerinfo['item_id'];
        }

        $charges = $customerinfo['charges'];
        if ($customerinfo['additional_charges_as'] == 'SE') {
            $customerinfo['setup_cost'] = $customerinfo['additional_charges'];
            $charges = $charges;
        } elseif ($customerinfo['additional_charges_as'] == 'NA') {
            $customerinfo['setup_cost'] = 0;
        }
        $customerinfo['rate'] = $charges;
        $customer_cost = $customerinfo['rate'] * $customerinfo['quantity'];
        $customerinfo['cost'] = $customer_cost;
        if ($customerinfo['tax_type'] == 'exclusive') {
            $tax = $customerinfo['tax1'] + $customerinfo['tax2'] + $customerinfo['tax3'];
            $total_tax = $this->exclusive_tax($tax, $customerinfo['cost'], 100);
            $total_tax = $this->dp($total_tax, $customerinfo['dp']);
            $customer_tax1_cost = $this->exclusive_tax($customerinfo['tax1'], $total_tax, $tax);
            $customer_tax1_cost = $this->dp($customer_tax1_cost, $customerinfo['dp']);
            $customer_tax2_cost = $this->exclusive_tax($customerinfo['tax2'], $total_tax, $tax);
            $customer_tax2_cost = $this->dp($customer_tax2_cost, $customerinfo['dp']);
            $customer_tax3_cost = $this->exclusive_tax($customerinfo['tax3'], $total_tax, $tax);
            $customer_tax3_cost = $this->dp($customer_tax3_cost, $customerinfo['dp']);

            $customer_callcost_total = $total_tax + $customer_cost;

            $customer_callcost_total = $this->dp($customer_callcost_total, $customerinfo['dp']);
        } else if ($customerinfo['tax_type'] == 'inclusive') {
            $tax = $customerinfo['tax1'] + $customerinfo['tax2'] + $customerinfo['tax3'];
            $total_tax = $this->inclusive_tax($tax, $customer_cost, 100);
            $total_tax = $this->dp($total_tax, $customerinfo['dp']);
            $customer_tax1_cost = $this->exclusive_tax($customerinfo['tax1'], $total_tax, $tax);
            $customer_tax1_cost = $this->dp($customer_tax1_cost, $customerinfo['dp']);
            $customer_tax2_cost = $this->exclusive_tax($customerinfo['tax2'], $total_tax, $tax);
            $customer_tax2_cost = $this->dp($customer_tax2_cost, $customerinfo['dp']);
            $customer_tax3_cost = $this->exclusive_tax($customerinfo['tax3'], $total_tax, $tax);
            $customer_tax3_cost = $this->dp($customer_tax3_cost, $customerinfo['dp']);
            $customer_callcost_total = $customer_cost;
            $customer_callcost_total = $this->dp($customer_callcost_total, $customerinfo['dp']);
            $customer_cost = $customer_callcost_total - $customer_tax1_cost - $customer_tax2_cost - $customer_tax3_cost;
            $customer_cost = $this->dp($customer_cost, $customerinfo['dp']);
        }
        $customerinfo['cost'] = $customer_cost;
        $customerinfo['totalcost'] = $customer_callcost_total;
        $customerinfo['tax1_cost'] = $customer_tax1_cost;
        $customerinfo['tax2_cost'] = $customer_tax2_cost;
        $customerinfo['tax3_cost'] = $customer_tax3_cost;
        $customerinfo['quantity'] = $customerinfo['quantity'];
        $customerinfo['destination'] = $customerinfo['item_id'];
        $customerinfo['rate'] = $charges;
        $customerinfo['charges'] = $charges;
        $customerinfo['unit'] = $customerinfo['quantity'];
        $customerinfo['rate'] = $customerinfo['rate'];
        if ($this > debug)
            echo "final sale cost " . PHP_EOL;
        return $customerinfo;
    }

    function sallercost($data_array, $account_id) {
        $reseelerinfo['sallerunit'] = '0';
        $reseelerinfo['sallerrate'] = '0';
        $reseelerinfo['sallercost'] = '0';
        $reseelerinfo['totalsallercost'] = '0';
        foreach ($data_array as $key => $value) {
            $reseelerinfo[$key] = $value;
        }
        $query = sprintf(" SELECT account_id, parent_account_id, account_type, account_level FROM account WHERE account_id= '%s' limit 1;", $account_id);
        if ($this->debug)
            echo "Checking Parent $account_id :  $query " . PHP_EOL;

        $query = $this->db->query($query);
        $reseller = $query->row_array();
        if (strlen(trim($reseller['parent_account_id'])) > 0) {
            $account_id = $reseller['parent_account_id'];
        } else {
            $reseelerinfo['sallerunit'] = '0';
            $reseelerinfo['sallerrate'] = '0';
            $reseelerinfo['sallercost'] = '0';
            $reseelerinfo['totalsallercost'] = '0';
            return $reseelerinfo;
        }

        if (count($reseller) > 0) {
            foreach ($reseller as $key => $value) {
                $reseelerinfo[$key] = $value;
            }
        }

        $reseelerinfo['account_id'] = $reseelerinfo['parent_account_id'];
        $query = sprintf("SELECT billingeventid, account_id, item_id, price_id, item_product_id, start_dt, status_id, stop_dt, lastbilldate, record_type, lastbill_execute_date, r1lastbilldate, r2lastbilldate, r3lastbilldate, r1lastbill_execute_date, r2lastbill_execute_date, r3lastbill_execute_date FROM   bill_billing_event WHERE account_id= '%s' and item_id = '%s' limit 1;", $reseelerinfo['account_id'], $data_array['item_id']);

        if ($this->debug)
            echo "Rate seting of $account_id :  $query " . PHP_EOL;
        $query = $this->db->query($query);
        $reseller = $query->row_array();

        if (strlen(trim($reseller['item_id'])) > 0) {
            
        } else {
            $reseelerinfo['sallerunit'] = '0';
            $reseelerinfo['sallerrate'] = '0';
            $reseelerinfo['sallercost'] = '0';
            $reseelerinfo['totalsallercost'] = '0';
            return $reseelerinfo;
        }

        if (count($reseller) > 0) {
            foreach ($reseller as $key => $value) {
                $reseelerinfo[$key] = $value;
                if ($this->debug)
                    echo "SQL2 $key :  $value " . PHP_EOL;
            }
        }


        $query = sprintf("SELECT  account.dp, account.currency_id, account.dp, customer_voipminuts.tariff_id, account.tax3, account.tax2, account.tax1, account.tax_type, account_level  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id  WHERE account.account_id = '%s' and account.account_id not in ('-3','-4') limit 1;", $reseelerinfo['account_id']);

        if ($this->debug)
            echo $query . PHP_EOL;

        $query = $this->db->query($query);
        $reseller = $query->row_array();

        if (count($reseller) > 0) {
            foreach ($reseller as $key => $value) {
                $reseelerinfo[$key] = $value;
                if ($this->debug)
                    echo "SQL3 $key :  $value " . PHP_EOL;
            }
        }

             $query = sprintf("select  bill_billing_event.price_id, bill_billing_event.item_id,  bill_pricelist.currency_id, bill_pricelist.description, bill_pricelist.reguler_charges, bill_pricelist.free_item, bill_pricelist.charges, bill_pricelist.additional_charges_as, bill_pricelist.additional_charges, 'CUSTOM' priceplan_id  from bill_billing_event  INNER JOIN bill_pricelist on bill_pricelist.price_id = bill_billing_event.price_id where bill_billing_event.account_id  = '%s' and bill_billing_event.item_id = '%s' and bill_billing_event.price_id = '%s';", $reseelerinfo['account_id'], $data_array['item_id'], $reseelerinfo['price_id']);


        if ($this->debug)
            echo $query . PHP_EOL;

        $query = $this->db->query($query);
        $reseller = $query->row_array();

        if (count($reseller) > 0) {
            foreach ($reseller as $key => $value) {
                $reseelerinfo[$key] = $value;
                if ($this->debug)
                    echo "SQL4 $key :  $value " . PHP_EOL;
            }
        } else {
            $query = sprintf("SELECT bill_pricelist_customer.price_id, bill_pricelist_customer.item_id, bill_pricelist_customer.currency_id, bill_pricelist_customer.description, bill_pricelist_customer.reguler_charges,bill_pricelist_customer.free_item, bill_pricelist_customer.charges, bill_pricelist_customer.additional_charges_as, bill_pricelist_customer.additional_charges, 'ADDONS' priceplan_id from bill_pricelist_customer   where bill_pricelist_customer.customer_account_id = '%s'  and bill_pricelist_customer.item_id = '%s'  and bill_pricelist_customer.price_id = '%s' limit 1;", $reseelerinfo['account_id'], $reseelerinfo['item_id'], $reseelerinfo['price_id']);

            $query = $this->db->query($query);
            $reseller = $query->row_array();

            if (count($reseller) > 0) {
                foreach ($reseller as $key => $value) {
                    $reseelerinfo[$key] = $value;
                    if ($this->debug)
                        echo "SQL5 $key :  $value " . PHP_EOL;
                }
            } else {
                $query = sprintf("SELECT bill_pricelist.price_id, bill_pricelist.item_id, bill_pricelist.currency_id, bill_pricelist.description, bill_pricelist.reguler_charges, bill_pricelist.free_item, bill_pricelist.charges, bill_pricelist.additional_charges_as, bill_pricelist.additional_charges, bill_priceplan_item.priceplan_id from bill_priceplan_item   INNER JOIN bill_pricelist on bill_priceplan_item.price_id = bill_pricelist.price_id WHERE bill_priceplan_item.priceplan_id in (SELECT priceplan_id from bill_customer_priceplan where account_id = '%s') and bill_pricelist.item_id = '%s'  and bill_pricelist.price_id = '%s' limit 1;", $reseelerinfo['account_id'], $reseelerinfo['item_id'], $reseelerinfo['price_id']);

                if ($this->debug)
                    echo $query . PHP_EOL;

                $query = $this->db->query($query);
                $reseller = $query->row_array();

                if (count($reseller) > 0) {
                    foreach ($reseller as $key => $value) {
                        $reseelerinfo[$key] = $value;
                        if ($this->debug)
                            echo "SQL6 $key :  $value " . PHP_EOL;
                    }
                }
            }
        }

        if ($reseelerinfo['reguler_charges'] == 'EMA') {
            $query = sprintf("SELECT count(id) count_ema  FROM `bill_sdrdata` where account_id = '%s' and item_id = '%s';", $reseelerinfo['account_id'], $reseelerinfo['item_id']);

            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $reseller = $query->row_array();


            if (count($reseller) > 0) {
                foreach ($reseller as $data) {
                    if ($data['count_ema'] > 0) {
                        return $reseelerinfo;
                    }
                }
            }
        } elseif ($reseelerinfo['reguler_charges'] == 'NA') {
            $reseelerinfo['regular_cost'] = 0;
            $reseelerinfo['cost'] = 0;
            $reseelerinfo['total_cost'] = 0;
            $reseelerinfo['tax1_cost'] = 0;
            $reseelerinfo['tax2_cost'] = 0;
            $reseelerinfo['tax3_cost'] = 0;
            $reseelerinfo['charges'] = 0;
            $reseelerinfo['quantity'] = $reseelerinfo['quantity'];
            $reseelerinfo['destination'] = $reseelerinfo['item_id'];
        }

        $charges = $reseelerinfo['charges'];

        if ($reseelerinfo['additional_charges_as'] == 'SE') {
            $reseelerinfo['setup_cost'] = $reseelerinfo['additional_charges'];
            $charges = $charges;
        } elseif ($reseelerinfo['additional_charges_as'] == 'NA') {
            $reseelerinfo['setup_cost'] = 0;
        }


        $reseelerinfo['cost'] = $charges;
        $customer_cost = $reseelerinfo['cost'] = $reseelerinfo['cost'] * $reseelerinfo['quantity'];
        if ($reseelerinfo['tax_type'] == 'exclusive') {
            $tax = $reseelerinfo['tax1'] + $reseelerinfo['tax2'] + $reseelerinfo['tax3'];
            $total_tax = $this->exclusive_tax($tax, $reseelerinfo['cost'], 100);
            $total_tax = $this->dp($total_tax, $reseelerinfo['dp']);
            $customer_tax1_cost = $this->exclusive_tax($reseelerinfo['tax1'], $total_tax, $tax);
            $customer_tax1_cost = $this->dp($customer_tax1_cost, $reseelerinfo['dp']);
            $customer_tax2_cost = $this->exclusive_tax($reseelerinfo['tax2'], $total_tax, $tax);
            $customer_tax2_cost = $this->dp($customer_tax2_cost, $reseelerinfo['dp']);
            $customer_tax3_cost = $this->exclusive_tax($reseelerinfo['tax3'], $total_tax, $tax);
            $customer_tax3_cost = $this->dp($customer_tax3_cost, $reseelerinfo['dp']);
            $customer_callcost_total = $customer_tax1_cost + $customer_tax2_cost + $customer_tax3_cost + $customer_cost;
            $customer_callcost_total = $this->dp($customer_callcost_total, $reseelerinfo['dp']);
        } else if ($reseelerinfo['tax_type'] == 'inclusive') {
            $tax = $reseelerinfo['tax1'] + $reseelerinfo['tax2'] + $reseelerinfo['tax3'];
            $total_tax = $this->inclusive_tax($tax, $customer_cost, 100);
            $total_tax = $this->dp($total_tax, $reseelerinfo['dp']);
            $customer_tax1_cost = $this->exclusive_tax($reseelerinfo['tax1'], $total_tax, $tax);
            $customer_tax1_cost = $this->dp($customer_tax1_cost, $reseelerinfo['dp']);
            $customer_tax2_cost = $this->exclusive_tax($reseelerinfo['tax2'], $total_tax, $tax);
            $customer_tax2_cost = $this->dp($customer_tax2_cost, $reseelerinfo['dp']);
            $customer_tax3_cost = $this->exclusive_tax($reseelerinfo['tax3'], $total_tax, $tax);
            $customer_tax3_cost = $this->dp($customer_tax3_cost, $reseelerinfo['dp']);
            $customer_callcost_total = $customer_cost;
            $customer_callcost_total = $this->dp($customer_callcost_total, $reseelerinfo['dp']);
            $customer_cost = $customer_callcost_total - $customer_tax1_cost - $customer_tax2_cost - $customer_tax3_cost;
            $customer_cost = $this->dp($customer_cost, $reseelerinfo['dp']);
        }
        $reseelerinfo['cost'] = $customer_cost;
        $reseelerinfo['total_cost'] = $customer_callcost_total;
        $reseelerinfo['tax1_cost'] = $customer_tax1_cost;
        $reseelerinfo['tax2_cost'] = $customer_tax2_cost;
        $reseelerinfo['tax3_cost'] = $customer_tax3_cost;
        $reseelerinfo['quantity'] = $reseelerinfo['quantity'];
        $reseelerinfo['destination'] = $reseelerinfo['item_id'];
        $reseelerinfo['rate'] = $charges;
        $reseelerinfo['charges'] = $charges;
        $reseelerinfo['sallerunit'] = $reseelerinfo['quantity'];
        $reseelerinfo['sallerrate'] = $reseelerinfo['rate'];
        $reseelerinfo['sallercost'] = $reseelerinfo['cost'];
        $reseelerinfo['totalsallercost'] = $reseelerinfo['total_cost'];

        if ($this->debug)
            echo "final sale cost " . PHP_EOL;
        return $reseelerinfo;
    }

    function reseller_servicebill($data_array, $account_id) {
        if ($this->debug)
            echo "Doing Reseller Billing $account_id" . PHP_EOL;
        $query = sprintf("SELECT  account.dp, resellers.emailaddress, resellers.company_name, account.currency_id, account.dp, customer_voipminuts.tariff_id, account.tax3, account.tax2, account.tax1, account.tax_type, parent_account_id, account_level  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id INNER JOIN resellers on resellers.account_id= account.account_id  WHERE account.account_id = '%s' and account.account_id not in ('-3','-4') limit 1;", $account_id);


        if ($this->debug)
            echo $query . PHP_EOL;

        $query = $this->db->query($query);
        $reseller = $query->row_array();

        if ($reseller['account_level'] > 0)
            $reseelerinfo = 'reseller' . $reseller['account_level'];
        else
            $reseelerinfo = 'reseller1';

        if (count($reseller) > 0) {
            foreach ($reseller as $key => $value) {
                $this->customer[$reseelerinfo][$key] = $value;
            }
        }
        $this->customer[$reseelerinfo]['account_id'] = $data_array['account_id'] = $account_id;
        $this->customer[$reseelerinfo]['item_id'] = $item_id = $data_array['item_id'];
        $this->customer[$reseelerinfo]['item_product_id'] = $item_product_id = $data_array['item_product_id'];
        $this->customer[$reseelerinfo]['quantity'] = $quantity = $data_array['quantity'];
        $this->customer[$reseelerinfo]['status_id'] = $status_id = $data_array['status_id'];
        $this->customer[$reseelerinfo]['start_dt'] = $start_dt = $data_array['start_dt'];
        $this->customer[$reseelerinfo]['price_id'] = $price_id = $data_array['price_id'];
        $query = sprintf("select  bill_billing_event.price_id, bill_billing_event.item_id,  bill_pricelist.currency_id, bill_pricelist.description, bill_pricelist.reguler_charges, bill_pricelist.free_item, bill_pricelist.charges, bill_pricelist.additional_charges_as, bill_pricelist.additional_charges, 'CUSTOM' priceplan_id  from bill_billing_event  INNER JOIN bill_pricelist on bill_pricelist.price_id = bill_billing_event.price_id where bill_billing_event.account_id  = '%s' and bill_billing_event.item_id = '%s' and bill_billing_event.price_id = '%s';", $this->customer[$reseelerinfo]['account_id'], $this->customer[$reseelerinfo]['item_id'], $this->customer[$reseelerinfo]['price_id']);

        if ($this->debug)
            echo $query . PHP_EOL;
        $this->logme = $this->logme . $query;
        $query = $this->db->query($query);
        $result = $query->row_array();

        if ($this->debug)
            print_r($result);
        if (count($result) > 0) {
            foreach ($result as $key => $value) {
                $this->customer[$reseelerinfo][$key] = $value;
            }
        } else {
            $query = sprintf("SELECT bill_pricelist_customer.price_id, bill_pricelist_customer.item_id, bill_pricelist_customer.currency_id, bill_pricelist_customer.description, bill_pricelist_customer.reguler_charges,bill_pricelist_customer.free_item, bill_pricelist_customer.charges, bill_pricelist_customer.additional_charges_as, bill_pricelist_customer.additional_charges, 'ADDONS' priceplan_id from bill_pricelist_customer   where bill_pricelist_customer.customer_account_id = '%s'  and bill_pricelist_customer.item_id = '%s'  and bill_pricelist_customer.price_id = '%s' limit 1;", $this->customer[$reseelerinfo]['account_id'], $this->customer[$reseelerinfo]['item_id'], $this->customer[$reseelerinfo]['price_id']);
            $this->logme .= $query;
            $query = $this->db->query($query);
            $result = $query->row_array();

            if ($this->debug)
                print_r($result);
            if (count($result) > 0) {
                foreach ($result as $key => $value) {
                    $this->customer[$reseelerinfo][$key] = $value;
                }
            } else {
                $query = sprintf("SELECT bill_pricelist.price_id, bill_pricelist.item_id, bill_pricelist.currency_id, bill_pricelist.description, bill_pricelist.reguler_charges, bill_pricelist.free_item, bill_pricelist.charges, bill_pricelist.additional_charges_as, bill_pricelist.additional_charges, bill_priceplan_item.priceplan_id from bill_priceplan_item   INNER JOIN bill_pricelist on bill_priceplan_item.price_id = bill_pricelist.price_id WHERE bill_priceplan_item.priceplan_id in (SELECT priceplan_id from bill_customer_priceplan where account_id = '%s') and bill_pricelist.item_id = '%s'  and bill_pricelist.price_id = '%s' limit 1;", $this->customer[$reseelerinfo]['account_id'], $this->customer[$reseelerinfo]['item_id'], $this->customer[$reseelerinfo]['price_id']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $query = $this->db->query($query);
                $result = $query->row_array();

                if ($this->debug)
                    print_r($result);
                if (count($result) > 0) {
                    foreach ($result as $key => $value) {
                        $this->customer[$reseelerinfo][$key] = $value;
                    }
                }
            }
        }

        if ($this->customer[$reseelerinfo]['reguler_charges'] == 'EMA') {
            $query = sprintf("SELECT count(id) count_ema  FROM `bill_sdrdata` where account_id = '%s' and item_id = '%s';", $this->customer[$reseelerinfo]['account_id'], $this->customer[$reseelerinfo]['item_id']);
            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $result = $query->row_array();

            if ($this->debug)
                print_r($result);
            if (count($result) > 0) {
                foreach ($result as $data) {
                    if ($data['count_ema'] > 0) {
                        return;
                    }
                }
            }
        } elseif ($this->customer[$reseelerinfo]['reguler_charges'] == 'NA') {
            $this->customer[$reseelerinfo]['regular_cost'] = 0;
            $this->customer[$reseelerinfo]['cost'] = 0;
            $this->customer[$reseelerinfo]['total_cost'] = 0;
            $this->customer[$reseelerinfo]['tax1_cost'] = 0;
            $this->customer[$reseelerinfo]['tax2_cost'] = 0;
            $this->customer[$reseelerinfo]['tax3_cost'] = 0;
            $this->customer[$reseelerinfo]['charges'] = 0;
            $this->customer[$reseelerinfo]['quantity'] = $this->customer[$reseelerinfo]['quantity'];
            $this->customer[$reseelerinfo]['destination'] = $this->customer[$reseelerinfo]['item_id'];
        }

        $charges = $this->customer[$reseelerinfo]['charges'];
        if ($this->debug)
            echo " ------ $charges";
        if ($this->customer[$reseelerinfo]['additional_charges_as'] == 'SE') {
            $this->customer[$reseelerinfo]['setup_cost'] = $this->customer[$reseelerinfo]['additional_charges'];
            $charges = $charges + $this->customer[$reseelerinfo]['setup_cost'];
        } elseif ($this->customer[$reseelerinfo]['additional_charges_as'] == 'NA') {
            $this->customer[$reseelerinfo]['setup_cost'] = 0;
        }
        if ($this->debug)
            print_r($this->customer);

        if ($this->debug)
            echo " ------ $charges  --------";
        $data_billdate = $this->billing_data($this->service_startdate, $this->billingday, $charges);
        if ($this->debug)
            print_r($data_billdate);
        $this->customer[$reseelerinfo]['cost'] = $data_billdate['billing_charges_new'];
        $customer_cost = $this->customer[$reseelerinfo]['cost'] = $this->customer[$reseelerinfo]['cost'] * $this->customer[$reseelerinfo]['quantity'];
        if ($this->customer[$reseelerinfo]['tax_type'] == 'exclusive') {
            $tax = $this->customer[$reseelerinfo]['tax1'] + $this->customer[$reseelerinfo]['tax2'] + $this->customer[$reseelerinfo]['tax3'];
            $total_tax = $this->exclusive_tax($tax, $this->customer[$reseelerinfo]['cost'], 100);
            $total_tax = $this->dp($total_tax, $this->customer[$reseelerinfo]['dp']);
            $customer_tax1_cost = $this->exclusive_tax($this->customer[$reseelerinfo]['tax1'], $total_tax, $tax);
            $customer_tax1_cost = $this->dp($customer_tax1_cost, $this->customer[$reseelerinfo]['dp']);
            $customer_tax2_cost = $this->exclusive_tax($this->customer[$reseelerinfo]['tax2'], $total_tax, $tax);
            $customer_tax2_cost = $this->dp($customer_tax2_cost, $this->customer[$reseelerinfo]['dp']);
            $customer_tax3_cost = $this->exclusive_tax($this->customer[$reseelerinfo]['tax3'], $total_tax, $tax);
            $customer_tax3_cost = $this->dp($customer_tax3_cost, $this->customer[$reseelerinfo]['dp']);
            $customer_callcost_total = $customer_tax1_cost + $customer_tax2_cost + $customer_tax3_cost + $customer_cost;
            $customer_callcost_total = $this->dp($customer_callcost_total, $this->customer[$reseelerinfo]['dp']);
        } else if ($this->customer[$reseelerinfo]['tax_type'] == 'inclusive') {
            $tax = $this->customer[$reseelerinfo]['tax1'] + $this->customer[$reseelerinfo]['tax2'] + $this->customer[$reseelerinfo]['tax3'];
            $total_tax = $this->inclusive_tax($tax, $customer_cost, 100);
            $total_tax = $this->dp($total_tax, $this->customer[$reseelerinfo]['dp']);
            $customer_tax1_cost = $this->exclusive_tax($this->customer[$reseelerinfo]['tax1'], $total_tax, $tax);
            $customer_tax1_cost = $this->dp($customer_tax1_cost, $this->customer[$reseelerinfo]['dp']);
            $customer_tax2_cost = $this->exclusive_tax($this->customer[$reseelerinfo]['tax2'], $total_tax, $tax);
            $customer_tax2_cost = $this->dp($customer_tax2_cost, $this->customer[$reseelerinfo]['dp']);
            $customer_tax3_cost = $this->exclusive_tax($this->customer[$reseelerinfo]['tax3'], $total_tax, $tax);
            $customer_tax3_cost = $this->dp($customer_tax3_cost, $this->customer[$reseelerinfo]['dp']);
            $customer_callcost_total = $customer_cost;
            $customer_callcost_total = $this->dp($customer_callcost_total, $this->customer[$reseelerinfo]['dp']);
            $customer_cost = $customer_callcost_total - $customer_tax1_cost - $customer_tax2_cost - $customer_tax3_cost;
            $customer_cost = $this->dp($customer_cost, $this->customer[$reseelerinfo]['dp']);
        }
        $this->customer[$reseelerinfo]['cost'] = $customer_cost;
        $this->customer[$reseelerinfo]['total_cost'] = $customer_callcost_total;
        $this->customer[$reseelerinfo]['tax1_cost'] = $customer_tax1_cost;
        $this->customer[$reseelerinfo]['tax2_cost'] = $customer_tax2_cost;
        $this->customer[$reseelerinfo]['tax3_cost'] = $customer_tax3_cost;
        $this->customer[$reseelerinfo]['quantity'] = $this->customer[$reseelerinfo]['quantity'];
        $this->customer[$reseelerinfo]['destination'] = $this->customer[$reseelerinfo]['item_id'];

        $this->customer[$reseelerinfo]['rate'] = $this->customer[$reseelerinfo]['charges'];

        return;
    }

    function check_billing_data($account_id, $lastbilldate) {
        $query = sprintf("SELECT plugin_system_name FROM `plugins` where plugin_system_name = 'billing';");
        if ($this->debug)
            echo $query . "\n";
        $query = $this->db->query($query);
        $billingmodule = $query->result_array();
        if (count($billingmodule) > 0) {
            foreach ($billingmodule as $billingdata) {
                $plugin_system_name = $billingdata['plugin_system_name'];
            }
        }

        if ($plugin_system_name == 'billing') {
            $query = sprintf("SELECT billing_day FROM bill_customer_priceplan where account_id = '%s';", $account_id);
            if ($this->debug)
                echo $query . "\n";
            $query = $this->db->query($query);
            $billingmodule = $query->result_array();
            if (count($billingmodule) > 0) {
                foreach ($billingmodule as $billingdata) {
                    if ($billingdata['billing_day'] < 10) {
                        $billingday = '0' . $billingdata['billing_day'];
                    } else {
                        $billingday = $billingdata['billing_day'];
                    }
                    $this->billingday = $billingday;
                    $date = $lastbilldate;
                    $this->service_startdate = $date;
                    $charges = 0;
                    if ($this->debug)
                        echo "date $date lastbilldate $lastbilldate billingday $this->billingday \n";
                    $data_billdate = $this->billing_data($date, $billingday, $charges);
                    $this->data_billdate = $data_billdate;
                    $this->service_stopdate = $data_billdate['billing_enddate'];
                }
            }
        } else {
            $this->billingday = date('t');
            $this->service_startdate = date('Y-m-01');
            $this->service_stopdate = date('Y-m-t');
        }
    }

    function ServiceMonthlyBundle($account_id, $date, $account_type) {
        $query = sprintf("SELECT bundle_account.lastbilldate, bundle_account.bundle_package_id, bundle_account.account_id, bundle_account.assign_dt, bundle_account.account_bundle_key, bundle_package.bundle_package_name, bundle_package.monthly_charges, bundle_package.bundle_package_status FROM bundle_account  INNER JOIN bundle_package on bundle_package.bundle_package_id = bundle_account.bundle_package_id  and bundle_account.account_id = '%s' and (lastbilldate <> date(now()) or lastbilldate is null) GROUP BY account_bundle_key;", $account_id);
        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $bundal_data = $query->result_array();
        if (count($bundal_data) > 0) {
            foreach ($bundal_data as $fdata) {
                $this->request = Array();
                if ($fdata['monthly_charges'] > 0) {
                    $data['amount'] = $fdata['monthly_charges'];
                    $this->request['amount'] = $fdata['monthly_charges'];
                } else {
                    $data['amount'] = 0;
                }
                if (strlen(trim($fdata['lastbilldate'])) > 0) {
                    $lastbilldate = $fdata['lastbilldate'];
                } else {
                    $lastbilldate = $date;
                }
                if ($this->debug)
                    echo "lastbilldate $lastbilldate \n";
                $this->check_billing_data($account_id, $lastbilldate);

                $this->request['account_id'] = $fdata['account_id'];
                $this->request['service_number'] = $fdata['bundle_package_id'];
                $this->customertype();
                if ($this->request['account_type'] == 'RESELLER') {
                    $this->resellerinfo();
                } else {
                    $this->customerinfo();
                }
                $this->data_billdate['billing_startdate'] = date('Y-m-d', strtotime($date . ' -0 day'));
                $this->data_billdate['billing_enddate'] = date('Y-m-d', strtotime($date . ' +1 month'));
                $this->data_billdate['billing_date'] = date('Y-m-d', strtotime($date . ' -1 day'));
                $rate = $fdata['monthly_charges'];
                $service_startdate = $this->data_billdate['billing_startdate'];
                $service_stopdate = $this->data_billdate['billing_enddate'];
                $billing_date = $this->data_billdate['billing_date'];

                if (strlen($this->request['account_id']) > 0 and strlen($this->request['service_number']) > 0) {
                    $action_date = $billing_date;
                    $this->request['yearmonth'] = date('Ym', strtotime($action_date));
                    $this->request['service_charges'] = $this->request['amount'];
                    $this->request['rule_type'] = 'BUNDLECHARGES';
                    $this->rule_type = 'BUNDLECHARGES';
                    if ($this->requesttype = 'SERVICE') {
                        $data_billdate['billing_charges_new'] = $data['amount'];
                    } else {
						 if ($this->prorata_billing) {
							$data_billdate = $this->billing_data($this->service_startdate, $this->billingday, $data['amount']);
						 }else{
							  $data_billdate['billing_charges_new'] = $data['amount'];
						 }
                    }
                    $total_cost = $data_billdate['billing_charges_new'];
                    $charges_data = $this->tax_calculation($this->accountinfo, $total_cost);
                    $quantity = 1;


                    if ($this->request['account_type'] == 'RESELLER') {
                        if ($this->request['account_level'] == '1') {

                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $fdata['account_bundle_key'];
                            $billing_date = $action_date;
                            $unit = $quantity;
                            $rate = $this->request['service_charges'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $quantity;
                            $sallerrate = 0;
                            $sallercost = 0;
                            $totalsallercost = 0;
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->request['account_level'] == '2') {


                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $fdata['account_bundle_key'];
                            $billing_date = $action_date;
                            $unit = $quantity;
                            $rate = $this->request['service_charges'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $quantity;
                            $sallerrate = 0;
                            $sallercost = 0;
                            $totalsallercost = 0;
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->request['account_level'] == '3') {

                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $fdata['account_bundle_key'];
                            $billing_date = $action_date;
                            $unit = $quantity;
                            $rate = $this->request['service_charges'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $quantity;
                            $sallerrate = 0;
                            $sallercost = 0;
                            $totalsallercost = 0;
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        }
                    } else {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $fdata['account_bundle_key'];
                        $billing_date = $billing_date;
                        $unit = $quantity;
                        $rate = $this->request['service_charges'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = $quantity;
                        $sallerrate = 0;
                        $sallercost = 0;
                        $totalsallercost = 0;
                        $startdate = $service_startdate;
                        $enddate = $service_stopdate;

                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    }
                    if ($this->debug)
                        echo $query_bill_account_sdr . "\n";
                    $this->db->query($query_bill_account_sdr);
                    if ($this->debug)
                        echo $query . "\n";
                    $this->db->query($query);
                    $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                    if ($this->debug)
                        echo $query . "\n";
                    if ($this->dobilling)
                        $this->db->query($query);


                    $query = sprintf("update bundle_account set lastbilldate =   '%s', lastbill_execute_date ='%s' where account_bundle_key = '%s';", $service_stopdate, $service_stopdate, $fdata['account_bundle_key']);
                    if ($this->dobilling)
                        $this->db->query($query);

                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $charges_data = Array();
                    $this->request = Array();
                    $this->accountinfo = Array();
                    $data_billdate = Array();
                }
            }
        }
    }

    function tax_calculation($data, $current_month_charges) {
        $data['total_tax'] = 0;
        $data['tax1_cost'] = 0;
        $data['tax1_cost'] = 0;
        $data['tax2_cost'] = 0;
        $data['tax2_cost'] = 0;
        $data['tax3_cost'] = 0;
        $data['tax3_cost'] = 0;
        if ($data['tax1'] == '' or $data['tax1'] == NULL)
            $data['tax1'] = 0;
        if ($data['tax2'] == '' or $data['tax2'] == NULL)
            $data['tax2'] = 0;
        if ($data['tax3'] == '' or $data['tax3'] == NULL)
            $data['tax3'] = 0;

        $data['cost'] = $current_month_charges;
        $data['total_cost'] = $current_month_charges;

        if ($data['tax_type'] == 'inclusive') {
            $tax = $data['tax1'] + $data['tax2'] + $data['tax3'];
            $total_tax = $this->inclusive_tax($tax, $current_month_charges, 100);
            $total_tax = $this->dp($total_tax, $data['dp']);
            $data['total_tax'] = $total_tax;
            $data['tax1_cost'] = $this->exclusive_tax($data['tax1'], $total_tax, $tax);
            $data['tax1_cost'] = $this->dp($data['tax1_cost'], $data['dp']);
            $data['tax2_cost'] = $this->exclusive_tax($data['tax2'], $total_tax, $tax);
            $data['tax2_cost'] = $this->dp($data['tax2_cost'], $data['dp']);
            $data['tax3_cost'] = $this->exclusive_tax($data['tax3'], $total_tax, $tax);
            $data['tax3_cost'] = $this->dp($data['tax3_cost'], $data['dp']);
            $data['total_cost'] = $this->dp($current_month_charges, $data['dp']);
            $data['cost'] = $current_month_charges - $total_tax;
            $data['cost'] = $this->dp($data['cost'], $data['dp']);
        } elseif ($data['tax_type'] == 'exclusive') {
            $tax = $data['tax1'] + $data['tax2'] + $data['tax3'];
            $total_tax = $this->exclusive_tax($tax, $current_month_charges, 100);
            $total_tax = $this->dp($total_tax, $data['dp']);
            $data['total_tax'] = $total_tax;
            $data['tax1_cost'] = $this->exclusive_tax($data['tax1'], $total_tax, $tax);
            $data['tax1_cost'] = $this->dp($data['tax1_cost'], $data['dp']);
            $data['tax2_cost'] = $this->exclusive_tax($data['tax2'], $total_tax, $tax);
            $data['tax2_cost'] = $this->dp($data['tax2_cost'], $data['dp']);
            $data['tax3_cost'] = $this->exclusive_tax($data['tax3'], $total_tax, $tax);
            $data['tax3_cost'] = $this->dp($data['tax3_cost'], $data['dp']);
            $data['cost'] = $this->dp($current_month_charges, $data['dp']);
            $data['total_cost'] = $current_month_charges + $total_tax;
            $data['total_cost'] = $this->dp($data['total_cost'], $data['dp']);
        } else {
            $data['tax1_cost'] = 0;
            $data['tax2_cost'] = 0;
            $data['tax3_cost'] = 0;
            $data['total_tax'] = 0;
            $data['cost'] = $this->dp($current_month_charges, $data['dp']);
            $data['total_cost'] = $this->dp($current_month_charges, $data['dp']);
        }
        return $data;
    }

    function customerinfo() {
        $this->data['error'] = '0';
        $this->request['detail'] = '';
        $this->request['otherdata'] = '';
        $this->request['service_startdate'] = $this->service_startdate;
        $this->request['service_stopdate'] = $this->service_stopdate;
        $account_id = $this->request['account_id'];
        $query = sprintf("SELECT  customers.emailaddress, customers.company_name, account.currency_id, customers.billing_type, account.dp, customer_voipminuts.tariff_id, account.tax3, account.tax2, account.tax1, account.tax_type, parent_account_id, account_level  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id INNER JOIN customers on customers.account_id= account.account_id  WHERE account.account_id = '%s' and account.status_id not in ('-3','-4') limit 1;", $account_id);

        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $userdetail = $query->result_array();
        if (count($userdetail) > 0) {
            foreach ($userdetail[0] as $key => $value) {
                $this->accountinfo[$key] = $value;
                if ($this->debug)
                    echo "$key $value" . PHP_EOL;
            }
        } else {
            $this->data['error'] = '1';
            $this->data['message'] = 'Wrong User';
            return;
        }

        $query = sprintf("SELECT  credit_limit, balance  as existing_balance , account_id, credit_limit - balance as  balance from customer_balance where account_id = '%s' limit 1;", $account_id);
        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);


        $balance = $query->result_array();
        $initial_setup = 0;
        if (count($balance) > 0) {
            foreach ($balance[0] as $key => $value) {
                $this->accountinfo[$key] = $value;
            }
            if ($this->accountinfo['balance'] < 0) {
                $this->data['error'] = '1';
                $this->data['message'] = 'Low Balance';
                return;
            }
        } else {
            $query = sprintf("INSERT INTO  customer_balance (account_id, credit_limit, balance) VALUES ('%s',0,0 )", $account_id);
            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $balance = $query->row_array();

            $initial_setup = 1;
        }
        if ($this->debug)
            print_r($this->accountinfo);
    }

    function customertype() {
        if ($this->debug)
            print_r($this->request);
        $query = sprintf("SELECT  account_id, account_type,  account_level,currency_id  from account   WHERE account.account_id = '%s' and account.account_id not in ('-3','-4') limit 1;", $this->request['account_id']);
        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $userdetail = $query->result_array();
        if (count($userdetail) > 0) {
            foreach ($userdetail as $data) {
                $reseller_id = $data['account_id'];
                $this->request['account_id'] = $data['account_id'];
                $this->request['account_type'] = $data['account_type'];
                $this->request['account_level'] = $data['account_level'];
                $this->request['currency_id'] = $data['currency_id'];
            }
        }
    }

    function resellerinfo() {
        $this->data['error'] = '0';
        $this->request['detail'] = '';
        $this->request['otherdata'] = '';
        $this->request['service_startdate'] = $this->service_startdate;
        $this->request['service_stopdate'] = $this->service_stopdate;
        $account_id = $this->request['account_id'];
        $query = sprintf("SELECT  resellers.emailaddress, resellers.company_name, account.currency_id, 'PREPAID' billing_type, account.dp, customer_voipminuts.tariff_id, account.tax3, account.tax2, account.tax1, account.tax_type, parent_account_id, account_level  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id INNER JOIN resellers on resellers.account_id= account.account_id  WHERE account.account_id = '%s' and account.account_id not in ('-3','-4') limit 1;", $account_id);
        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $userdetail = $query->result_array();
        if (count($userdetail) > 0) {
            foreach ($userdetail[0] as $key => $value) {
                if ($this->debug)
                    echo " $key => $value\n";
                $this->accountinfo[$key] = $value;
            }
        } else {
            $this->data['error'] = '1';
            $this->data['message'] = 'Wrong User ';
            return;
        }

        $query = sprintf("SELECT  credit_limit, balance  as existing_balance , account_id, credit_limit - balance as  balance from customer_balance where account_id = '%s' limit 1;", $account_id);

        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $balance = $query->result_array();

        $initial_setup = 0;
        if (count($balance) > 0) {
            foreach ($balance[0] as $key => $value) {
                if ($this->debug)
                    echo " $key => $value\n";
                $this->accountinfo[$key] = $value;
            }
            if ($this->accountinfo['balance'] < 0) {
                $this->data['error'] = '1';
                $this->data['message'] = 'Low Balance in Reseller ';
                return;
            }
        } else {
            $query = sprintf("INSERT INTO  customer_balance (account_id, credit_limit, balance) VALUES ('%s',0,0 )", $account_id);
            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $initial_setup = 1;
        }
    }

    function currencies_data() {
        $query = sprintf("select id, currency_id,ratio,date  from sys_currencies_conversions where id in (select max(id) from sys_currencies_conversions group by currency_id) order by id desc;");
        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $this->currencies = $query->row_array();
    }

    function currencies_ratio($user_currency_id, $carrier_currency_id) {
        $this->currencies_data();
        $currencyratio = 1;
        foreach ($this->currencies as $currency) {
            if ($currency['currency_id'] == $user_currency_id) {
                $user_currency_ratio = $currency['ratio'];
            }
            if ($currency['currency_id'] == $carrier_currency_id) {
                $route_currency_ratio = $currency['ratio'];
            }
        }
        if ($user_currency_ratio > 0 and $route_currency_ratio > 0)
            $currencyratio = $user_currency_ratio / $route_currency_ratio;

        return $currencyratio;
    }

    function didsetupcharge($date, $didsetup = 0, $rental = 0, $extrachannels = 0) {
		
		  if ($this->debug)
            echo "ggggggggggggggggggg $date, $didsetup, $rental, $extrachannels " . PHP_EOL;
		
        try {
            $todaydate = date('Y-m-d');
            $day = date('d', strtotime($date . ' -0 day'));
            if ($day < 10) {
                $billingday = '0' . $day;
            } else {
                $billingday = $day;
            }
            $this->billingday = $billingday;
            $this->service_startdate = $date;


            $charges = 0;
            if ($this->requesttype == 'SERVICE') {
                $nextonemonth = date('Y-m-d', strtotime($date . ' +1 month'));
                $data_billdate['billing_enddate'] = $this->service_stopdate = date('Y-m-d', strtotime($nextonemonth . ' -1 day'));
                $this->service_startdate = $date;
            } else {
                $data_billdate = $this->billing_data($date, $billingday, $charges);
            }

            $this->service_stopdate = $data_billdate['billing_enddate'];
            $service_startdate = $this->service_startdate;
            $service_stopdate = $this->service_stopdate;

            $action_date = date('Y-m-d', strtotime($date . ' -1 day'));
            $this->request['yearmonth'] = date('Ym', strtotime($action_date));
            /*
             * Finding DID carrier detail
             */
            $query = sprintf("SELECT carrier_id, did.did_number, did.did_status, did.carrier_id , did.channels from did where did_number = '%s' limit 1;", $this->request['service_number']);
            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $diddetail = $query->row_array();
            if (count($diddetail) > 0) {
                foreach ($diddetail as $key => $value) {
                    $this->request[$key] = $value;
                }
            }


            /*
             * Fibnding the DID rates
             */
            $query = sprintf("SELECT customer_rates.prefix, customer_rates.destination, customer_rates.setup_charge, customer_rates.rental, customer_rates.inclusive_channel, customer_rates.exclusive_per_channel_rental from customer_rates INNER JOIN tariff_ratecard_map on tariff_ratecard_map.ratecard_id = customer_rates.ratecard_id INNER JOIN ratecard on ratecard.ratecard_id = customer_rates.ratecard_id and ratecard.ratecard_for= 'INCOMING' and ratecard.ratecard_type ='CUSTOMER' where tariff_ratecard_map.tariff_id = '%s' and '%s' like CONCAT(prefix,'%%')  ORDER BY prefix desc limit 1;", $this->accountinfo['tariff_id'], $this->request['service_number']);

            if ($this->debug)
                echo $query . PHP_EOL;
            $query = $this->db->query($query);
            $didrates = $query->row_array();

            if (count($didrates) > 0) {
                foreach ($didrates as $key => $value) {
                    $this->accountinfo[$key] = $value;
                }

                /*
                 * Carrier detail
                 */
                $query = sprintf("select carrier_currency_id, tariff_id, 'prepaid',dp, tax1,tax2, tax3, tax_type from carrier WHERE carrier_id = '%s' limit 1;", $this->request['carrier_id']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $query = $this->db->query($query);
                $tariff = $query->row_array();

                foreach ($tariff as $key => $value) {
                    $carrierdidrates[$key] = $value;
                }

                /*
                 * DID rate for carrier
                 */
                $query = sprintf("SELECT carrier_rates.prefix, carrier_rates.destination, carrier_rates.setup_charge, carrier_rates.rental, carrier_rates.inclusive_channel, carrier_rates.exclusive_per_channel_rental from carrier_rates INNER JOIN tariff_ratecard_map on tariff_ratecard_map.ratecard_id = carrier_rates.ratecard_id INNER JOIN ratecard on ratecard.ratecard_id = carrier_rates.ratecard_id and ratecard.ratecard_for= 'INCOMING' and ratecard.ratecard_type ='CARRIER' where tariff_ratecard_map.tariff_id = '%s' and '%s' like CONCAT(prefix,'%%')  ORDER BY prefix desc limit 1;", $carrierdidrates['tariff_id'], $this->request['service_number']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $query = $this->db->query($query);
                $saller = $query->row_array();

                if (count($saller) > 0) {
                    foreach ($saller as $key => $value) {
                        $carrierdidrates[$key] = $value;
                    }
                }
                $directcustomer = 0;
                if (strlen($this->accountinfo['parent_account_id']) > 0) {
                    /*
                     * Account is under reseller so seller is reseller and that Data
                     */
                    $query = sprintf("SELECT  currency_id carrier_currency_id, tariff_id,  dp, customer_voipminuts.tariff_id, tax3, tax2, tax1, tax_type, parent_account_id, account_level  from account INNER JOIN customer_voipminuts on customer_voipminuts.account_id = account.account_id WHERE account.account_id = '%s' limit 1;", $this->accountinfo['parent_account_id']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $query = $this->db->query($query);
                    $tariff = $query->row_array();


                    foreach ($tariff as $key => $value) {
                        $sallerdidrates[$key] = $value;
                    }
                    $this->data['message'] = $query;
                    /*
                     * Account is under reseller so seller is reseller and that rates
                     */
                    $query = sprintf("SELECT customer_rates.prefix, customer_rates.destination, customer_rates.setup_charge, customer_rates.rental, customer_rates.inclusive_channel, customer_rates.exclusive_per_channel_rental from customer_rates INNER JOIN tariff_ratecard_map on tariff_ratecard_map.ratecard_id = customer_rates.ratecard_id INNER JOIN ratecard on ratecard.ratecard_id = customer_rates.ratecard_id and ratecard.ratecard_for= 'INCOMING' and ratecard.ratecard_type ='CUSTOMER' where tariff_ratecard_map.tariff_id = '%s' and '%s' like CONCAT(prefix,'%%')  ORDER BY prefix desc limit 1;", $sallerdidrates['tariff_id'], $this->request['service_number']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $query = $this->db->query($query);
                    $saller = $query->row_array();

                    if (count($saller) > 0) {
                        foreach ($saller as $key => $value) {
                            $sallerdidrates[$key] = $value;
                        }
                    }
                } else {
                    /*
                     * if Seller is not Reseller and account under the admin then carrier rates will be as seller rates
                     */
                    $directcustomer = 1;
                    $sallerdidrates = $carrierdidrates;
                }
            } else {
                /*
                 * Customer DID rates not available in system
                 */
                $this->data['error'] = '1';
                $this->data['message'] = 'DID Rates not available ';
                return FALSE;
            }

            /*
             * If DID cancelation request proceesed
             */
            if ($this->request['request'] == 'DIDCANCEL') {
                $this->request['service_charges'] = $this->accountinfo['setup_charge'];
                $this->request['rule_type'] = 'DIDCANCEL';
                $charges_data = 0;
                $saller_charges_data = 0;
                if ($this->requesttype == 'SERVICE') {
                    
                } else if ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0) {
                    $this->data['error'] = '1';
                    $this->data['message'] = 'Low Balance';
                    return FALSE;
                }

                if ($this->request['account_type'] == 'RESELLER') {
                    if ($this->request['account_level'] == '1') {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = 0;
                        $cost = 0;
                        $totalcost = 0;
                        $sallerunit = 0;
                        $sallerrate = 0;
                        $sallercost = 0;
                        $totalsallercost = 0;
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->request['account_level'] == '2') {
                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = 0;
                        $cost = 0;
                        $totalcost = 0;
                        $sallerunit = 0;
                        $sallerrate = 0;
                        $sallercost = 0;
                        $totalsallercost = 0;
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->request['account_level'] == '3') {
                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = 0;
                        $cost = 0;
                        $totalcost = 0;
                        $sallerunit = 0;
                        $sallerrate = 0;
                        $sallercost = 0;
                        $totalsallercost = 0;
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    }
                } else {
                    if ($this->accountinfo['account_level'] == '4') {
                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = 0;
                        $cost = 0;
                        $totalcost = 0;
                        $sallerunit = 0;
                        $sallerrate = 0;
                        $sallercost = 0;
                        $totalsallercost = 0;
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->accountinfo['account_level'] == '3') {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = 0;
                        $cost = 0;
                        $totalcost = 0;
                        $sallerunit = 0;
                        $sallerrate = 0;
                        $sallercost = 0;
                        $totalsallercost = 0;
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->accountinfo['account_level'] == '2') {
                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = 0;
                        $cost = 0;
                        $totalcost = 0;
                        $sallerunit = 0;
                        $sallerrate = 0;
                        $sallercost = 0;
                        $totalsallercost = 0;
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } else {
                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = 0;
                        $cost = 0;
                        $totalcost = 0;
                        $sallerunit = 0;
                        $sallerrate = 0;
                        $sallercost = 0;
                        $totalsallercost = 0;
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    }
                }

                if ($this->debug)
                    echo $query_bill_account_sdr . PHP_EOL;
                $this->db->query($query_bill_account_sdr);

                if ($this->debug)
                    echo $query . PHP_EOL;
                $this->db->query($query);

                $this->data['error'] = '0';
                $this->data['message'] = 'DID removed';
                return true;
            }
            $didsetup_foremail = '';
            /*
             * if New DID Setup request
             */
            if ($didsetup == 1) {
                $this->request['service_charges'] = $this->accountinfo['setup_charge'];
                $this->request['rule_type'] = 'DIDSETUP';
                $didsetup_foremail = 'DIDSETUP';
                /*
                 * Seller Costing
                 */
                $charges_data = $this->tax_calculation($this->accountinfo, $this->accountinfo['setup_charge']);
                $currency_id = $this->accountinfo['currency_id'];
                $saller_currency_id = $sallerdidrates['carrier_currency_id'];
                $currencyratio = $this->currencies_ratio($currency_id, $saller_currency_id);
                $saller_setup_charge = $currencyratio * $sallerdidrates['setup_charge'];
                $saller_charges_data = $this->tax_calculation($sallerdidrates, $saller_setup_charge);
                /*
                 * carrier Costing
                 */
                $carrier_currency_id = $carrierdidrates['carrier_currency_id'];
                $currencyratio = $this->currencies_ratio($currency_id, $carrier_currency_id);
                $carrier_setup_charge = $currencyratio * $carrierdidrates['setup_charge'];
                $carrier_charges_data = $this->tax_calculation($carrierdidrates, $carrier_setup_charge);



                if ($this->requesttype == 'SERVICE') {
                    
                } else if ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0) {
                    $this->data['error'] = '1';
                    $this->data['message'] = 'Low Balance';
                    return;
                }
                if ($this->request['account_type'] == 'RESELLER') {
                    if ($this->request['account_level'] == '1') {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['setup_charge'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_charges_data['setup_charge'];
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->request['account_level'] == '2') {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['setup_charge'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_charges_data['setup_charge'];
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->request['account_level'] == '3') {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['setup_charge'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_charges_data['setup_charge'];
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    }
                } else {

                    if ($this->accountinfo['account_level'] == '4') {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['setup_charge'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_charges_data['setup_charge'];
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->accountinfo['account_level'] == '3') {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['setup_charge'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_charges_data['setup_charge'];
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->accountinfo['account_level'] == '2') {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['setup_charge'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_charges_data['setup_charge'];
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } else {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['setup_charge'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_charges_data['setup_charge'];
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $this->service_startdate;
                        $enddate = $this->service_stopdate;
						
						if ($this->debug)
							echo "<br> Account Detail ------ $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate  END<br>" ;
                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    }
                }
                if ($this->debug)
                    echo $query_bill_account_sdr . PHP_EOL;
                $this->db->query($query_bill_account_sdr);
                $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $this->db->query($query);
                $didnumber_foremail = $this->request['service_number'];
            }


            if ($rental == 1) {
                $this->request['rule_type'] = 'DIDRENTAL';
                $this->request['service_charges'] = $this->accountinfo['rental'];
                if ($this->requesttype == 'SERVICE') {
                    $current_month_charges = $this->accountinfo['rental'];
                } else {	
					 if ($this->prorata_billing) {
						$current_month_charges = $this->charges($this->accountinfo['rental'], $service_startdate, $service_stopdate);
					 }else{
						 $current_month_charges = $this->accountinfo['rental'];
					 }
                }
                $charges_data = $this->tax_calculation($this->accountinfo, $current_month_charges);

                /*
                 * Seller Costing
                 */

                $currency_id = $this->accountinfo['currency_id'];
                $saller_currency_id = $sallerdidrates['carrier_currency_id'];
                $currencyratio = $this->currencies_ratio($currency_id, $saller_currency_id);
                $saller_rental_charge = $currencyratio * $sallerdidrates['rental'];
                if ($this->requesttype == 'SERVICE') {
                    $sallecurrent_month_charges = $saller_rental_charge;
                } else {
					
					 if ($this->prorata_billing) {						 
						$sallecurrent_month_charges = $this->charges($saller_rental_charge, $service_startdate, $service_stopdate);
					 }else{
						  $sallecurrent_month_charges = $saller_rental_charge;
					 }
                }

                $saller_charges_data = $this->tax_calculation($sallerdidrates, $sallecurrent_month_charges);
                /*
                 * carrier Costing
                 */
                $carrier_currency_id = $carrierdidrates['carrier_currency_id'];
                $currencyratio = $this->currencies_ratio($currency_id, $carrier_currency_id);
                $carrier_rental_charge = $currencyratio * $carrierdidrates['rental'];

                if ($this->requesttype == 'SERVICE') {
                    $carrier_current_month_charges = $carrier_rental_charge;
                } else {
					 if ($this->prorata_billing) {			
						$carrier_current_month_charges = $this->charges($carrier_rental_charge, $service_startdate, $service_stopdate);
					 }else{
						 $carrier_current_month_charges = $carrier_rental_charge;
					 }
                }
                $carrier_charges_data = $this->tax_calculation($carrierdidrates, $carrier_current_month_charges);

                if ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0 and $this->request['request_from'] != 'service') {
                    $this->data['error'] = '1';
                    $this->data['message'] = 'Low Balance';
                    return;
                }
                if ($this->request['account_type'] == 'RESELLER') {
                    if ($this->request['account_level'] == '1') {

                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['rental'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_rental_charge;
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $service_startdate;
                        $enddate = $service_stopdate;


                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->request['account_level'] == '2') {
                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['rental'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_rental_charge;
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $service_startdate;
                        $enddate = $service_stopdate;


                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->request['account_level'] == '3') {
                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['rental'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_rental_charge;
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $service_startdate;
                        $enddate = $service_stopdate;


                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    }
                } else {

                    if ($this->accountinfo['account_level'] == '4') {
                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['rental'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_rental_charge;
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $service_startdate;
                        $enddate = $service_stopdate;


                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->accountinfo['account_level'] == '3') {
                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['rental'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_rental_charge;
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $service_startdate;
                        $enddate = $service_stopdate;


                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } elseif ($this->accountinfo['account_level'] == '2') {


                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['rental'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_rental_charge;
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $service_startdate;
                        $enddate = $service_stopdate;


                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    } else {


                        $account_id = $this->request['account_id'];
                        $rule_type = $this->request['rule_type'];
                        $service_number = $this->request['service_number'];
                        $billing_date = $action_date;
                        $unit = 1;
                        $rate = $this->accountinfo['rental'];
                        $cost = $charges_data['cost'];
                        $totalcost = $charges_data['total_cost'];
                        $sallerunit = 1;
                        $sallerrate = $saller_rental_charge;
                        $sallercost = $saller_charges_data['cost'];
                        $totalsallercost = $saller_charges_data['total_cost'];
                        $startdate = $service_startdate;
                        $enddate = $service_stopdate;


                        $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                    }
                }
                if ($this->debug)
                    echo $query_bill_account_sdr . PHP_EOL;
                $this->db->query($query_bill_account_sdr);

                if ($this->debug)
                    echo $query . PHP_EOL;
                
                /*
                 * Deducting Balance
                 */
                $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                if ($this->debug)
                    echo $query . PHP_EOL;
                $this->db->query($query);
                /*
                 * Additional channels
                 */
                $remainingchannels = $this->request['channels'] - $this->accountinfo['inclusive_channel'];
                if ($remainingchannels > 0) {
                    $this->request['rule_type'] = 'DIDEXTRACHRENTAL';
                    $this->request['service_charges'] = $this->accountinfo['exclusive_per_channel_rental'];

                    if ($this->requesttype == 'SERVICE') {
                        $current_month_charges = $this->accountinfo['exclusive_per_channel_rental'];
                    } else {
						 if ($this->prorata_billing) {			
							$current_month_charges = $this->charges($this->accountinfo['exclusive_per_channel_rental'], $service_startdate, $service_stopdate);
						 }else{
							 $current_month_charges = $this->accountinfo['exclusive_per_channel_rental'];
						 }
                    }

                    $current_month_charges = $remainingchannels * $current_month_charges;
                    $charges_data = $this->tax_calculation($this->accountinfo, $current_month_charges);

                    /*
                     * Seller Costing
                     */

                    $currency_id = $this->accountinfo['currency_id'];
                    $saller_currency_id = $sallerdidrates['carrier_currency_id'];
                    $currencyratio = $this->currencies_ratio($currency_id, $saller_currency_id);
                    $saller_extarental_charge = $currencyratio * $sallerdidrates['exclusive_per_channel_rental'];

                    if ($this->requesttype == 'SERVICE') {
                        $sallecurrent_month_charges = $saller_extarental_charge;
                    } else {
						 if ($this->prorata_billing) {		
							$sallecurrent_month_charges = $this->charges($saller_extarental_charge, $service_startdate, $service_stopdate);
						 }else{
							   $sallecurrent_month_charges = $saller_extarental_charge;
						 }
                    }

                    $saller_charges_data = $this->tax_calculation($sallerdidrates, $sallecurrent_month_charges);
                    /*
                     * carrier Costing
                     */
                    $carrier_currency_id = $carrierdidrates['carrier_currency_id'];
                    $currencyratio = $this->currencies_ratio($currency_id, $carrier_currency_id);
                    $carrier_extarental_charge = $currencyratio * $carrierdidrates['exclusive_per_channel_rental'];
                    if ($this->requesttype == 'SERVICE') {
                        $carrier_current_month_charges = $carrier_extarental_charge;
                    } else {
						 if ($this->prorata_billing) {		
							$carrier_current_month_charges = $this->charges($carrier_extarental_charge, $service_startdate, $service_stopdate);	
						 }else{
							 $carrier_current_month_charges = $carrier_extarental_charge;
						}
                    }
                    $carrier_charges_data = $this->tax_calculation($carrierdidrates, $carrier_current_month_charges);
                    $this->request['detail'] = "Addition $remainingchannels incoming channels for " . $this->request['service_number'] . " number";
                    if ($this->requesttype == 'SERVICE') {
                        
                    } elseif ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0) {
                        $this->data['error'] = '1';
                        $this->data['message'] = 'Low Balance';
                        return;
                    }

                    if ($this->request['account_type'] == 'RESELLER') {
                        if ($this->request['account_level'] == '1') {
                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;


                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->request['account_level'] == '2') {

                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->request['account_level'] == '3') {


                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        }
                    } else {

                        if ($this->accountinfo['account_level'] == '4') {


                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->accountinfo['account_level'] == '3') {

                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->accountinfo['account_level'] == '2') {

                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } else {

                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        }
                    }

                    if ($this->debug)
                        echo $query_bill_account_sdr . PHP_EOL;
                    $this->db->query($query_bill_account_sdr);

                    if ($this->debug)
                        echo $query_bill_account_sdr . PHP_EOL;
                    
                    $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $this->db->query($query);
                }

                $field = $this->request['lastbilldate'];
 
            
            }
            /*
             * Additioal channels only
             */
            if ($extrachannels == 1) {
                $remainingchannels = $this->request['channels'];
                if ($remainingchannels > 0) {
                    $this->request['rule_type'] = 'DIDEXTRACHRENTAL';
                    $this->request['service_charges'] = $this->accountinfo['exclusive_per_channel_rental'];
                    $current_month_charges = $this->accountinfo['exclusive_per_channel_rental'];
                    $current_month_charges = $remainingchannels * $current_month_charges;
                    $charges_data = $this->tax_calculation($this->accountinfo, $current_month_charges);

                    /*
                     * Seller Costing
                     */

                    $currency_id = $this->accountinfo['currency_id'];
                    $saller_currency_id = $sallerdidrates['carrier_currency_id'];
                    $currencyratio = $this->currencies_ratio($currency_id, $saller_currency_id);
                    $saller_extarental_charge = $currencyratio * $sallerdidrates['exclusive_per_channel_rental'];
                    $sallecurrent_month_charges = $saller_extarental_charge;
                    $saller_charges_data = $this->tax_calculation($sallerdidrates, $sallecurrent_month_charges);
                    /*
                     * carrier Costing
                     */
                    $carrier_currency_id = $carrierdidrates['carrier_currency_id'];
                    $currencyratio = $this->currencies_ratio($currency_id, $carrier_currency_id);
                    $carrier_extarental_charge = $currencyratio * $carrierdidrates['exclusive_per_channel_rental'];
                    $carrier_current_month_charges = $carrier_extarental_charge;
                    $carrier_charges_data = $this->tax_calculation($carrierdidrates, $carrier_current_month_charges);
                    $this->request['detail'] = "Addition $remainingchannels incoming channels for " . $this->request['service_number'] . " number";
                    if ($this->requesttype == 'SERVICE') {
                        
                    } elseif ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0) {
                        $this->data['error'] = '1';
                        $this->data['message'] = 'Low Balance';
                        return;
                    }


                    if ($this->request['account_type'] == 'RESELLER') {
                        if ($this->request['account_level'] == '1') {


                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->request['account_level'] == '2') {



                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->request['account_level'] == '3') {


                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        }
                    } else {

                        if ($this->accountinfo['account_level'] == '4') {

                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->accountinfo['account_level'] == '3') {

                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } elseif ($this->accountinfo['account_level'] == '2') {

                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        } else {


                            $account_id = $this->request['account_id'];
                            $rule_type = $this->request['rule_type'];
                            $service_number = $this->request['service_number'];
                            $billing_date = $action_date;
                            $unit = $remainingchannels;
                            $rate = $this->accountinfo['exclusive_per_channel_rental'];
                            $cost = $charges_data['cost'];
                            $totalcost = $charges_data['total_cost'];
                            $sallerunit = $remainingchannels;
                            $sallerrate = $saller_extarental_charge;
                            $sallercost = $saller_charges_data['cost'];
                            $totalsallercost = $saller_charges_data['total_cost'];
                            $startdate = $service_startdate;
                            $enddate = $service_stopdate;

                            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
                        }
                    }

                    if ($this->debug)
                        echo $query_bill_account_sdr . PHP_EOL;
                    $this->db->query($query_bill_account_sdr);

                    if ($this->debug)
                        echo $query_bill_account_sdr . PHP_EOL;
                  
                    $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $this->db->query($query);
                }
            }
            $this->data['error'] = '0';
            $this->data['message'] = 'Request Processed';
            $this->billingday = '';
            $this->service_startdate = '';
            $charges = 0;
            $data_billdate = '';
            $this->service_stopdate = '';
            $service_startdate = '';
            $service_stopdate = '';

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function charges($charges, $service_startdate, $service_stopdate) {
        $no_of_days = date('t', strtotime($date));
        $current_day = date('d', strtotime($service_startdate));
        $billingdays = ($no_of_days - $current_day) + 1;
        $current_month_charges = ($charges / $no_of_days) * $billingdays;
        return $current_month_charges;
    }

    function ServiceDIDRental($account_id, $date_in, $account_type) {
        $query = sprintf("SELECT account.account_id,  account.status_id, account.account_type, account.account_level  from account where account_id = '%s';", $account_id);
        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $diddetail = $query->result_array();
        if (count($diddetail) > 0) {
            foreach ($diddetail as $data) {
                $account_type = $data['account_type'];
                $account_level = $data['account_level'];
                if ($this->debug)
                    echo "$account_type ::::: $account_level" . PHP_EOL;
            }
        }

        if ($account_type == 'CUSTOMER') {
            $query = sprintf("SELECT  assign_date, reseller1_assign_date, reseller2_assign_date, reseller3_assign_date, did_id, did_number, did_status, carrier_id, account_id, assign_date, reseller1_account_id, reseller1_assign_date, reseller2_account_id, reseller2_assign_date, reseller3_account_id, reseller3_assign_date, channels, did_name, number_type, lastbilldate, r1lastbilldate, r2lastbilldate, r3lastbilldate from did where did_status = 'USED' and account_id = '%s';", $account_id);

            if ($this->debug)
                echo "$account_type ::::: $account_level In Customer $query" . PHP_EOL;
        } else {
            if ($this->debug)
                echo "$account_type ::::: $account_level In Reseller  " . PHP_EOL;
            if ($account_type == 'RESELLER' and $account_level == '1') {
                $query = sprintf("SELECT assign_date, reseller1_assign_date, reseller2_assign_date, reseller3_assign_date, did_id, did_number, did_status, carrier_id, account_id, assign_date, reseller1_account_id, reseller1_assign_date, reseller2_account_id, reseller2_assign_date, reseller3_account_id, reseller3_assign_date, channels, did_name, number_type, lastbilldate, r1lastbilldate, r2lastbilldate, r3lastbilldate from did where did_status = 'USED' and LENGTH(trim(account_id)) > 0 and reseller1_account_id = '%s';", $account_id);
            } else if ($account_type == 'RESELLER' and $account_level == '2') {
                $query = sprintf("SELECT  assign_date, reseller1_assign_date, reseller2_assign_date, reseller3_assign_date, did_id, did_number, did_status, carrier_id, account_id, assign_date, reseller1_account_id, reseller1_assign_date, reseller2_account_id, reseller2_assign_date, reseller3_account_id, reseller3_assign_date, channels, did_name, number_type, lastbilldate, r1lastbilldate, r2lastbilldate, r3lastbilldate from did where did_status = 'USED' and LENGTH(trim(account_id)) > 0 and reseller2_account_id = '%s';", $account_id);
            } else if ($account_type == 'RESELLER' and $account_level == '3') {
                $query = sprintf("SELECT  assign_date, reseller1_assign_date, reseller2_assign_date, reseller3_assign_date, did_id, did_number, did_status, carrier_id, account_id, assign_date, reseller1_account_id, reseller1_assign_date, reseller2_account_id, reseller2_assign_date, reseller3_account_id, reseller3_assign_date, channels, did_name, number_type, lastbilldate, r1lastbilldate, r2lastbilldate, r3lastbilldate from did where did_status = 'USED' and LENGTH(trim(account_id)) > 0 and reseller3_account_id = '%s';", $account_id);
            }
        }
        if ($this->debug)
            echo $query . PHP_EOL;

        $query = $this->db->query($query);
        $diddetail = $query->result_array();
        if (count($diddetail) > 0) {
            foreach ($diddetail as $data) {
                $didsetup = 0;
                $rental = 1;
                $extrachannels = 0;
                $this->request = Array();
                $this->accountinfo = Array();
                if ($account_type == 'CUSTOMER') {
                    if (strlen(trim($data['assign_date'])) > 0 and $data['assign_date'] != '0000-00-00') {
                        if (strlen(trim($data['lastbilldate'])) > 0 and $data['lastbilldate'] != '0000-00-00') {
                            $date1 = date_create($date_in);
                            $date2 = date_create($data['lastbilldate']);
                            $diff = date_diff($date1, $date2);
                            $daycount = $diff->format("%a");
                        }
                        if ($daycount < 150) {
                            $date = date('Y-m-d', strtotime($data['lastbilldate'] . ' +1 day'));
                        } else {
                            $date = $date_in;
                        }
                        if ($this->requesttype == 'SERVICE') {
                            $date = $date_in; //date('Y-m-d', strtotime($data['lastbilldate'] . ' +0 day'));
                        }

                        $this->request['account_id'] = $account_id;
                        $this->request['service_number'] = $data['did_number'];
                        $this->request['channels'] = $data['channels'];
                        $this->request['request_from'] = 'service';
                        $this->request['carrier_id'] = $data['carrier_id'];
                        $this->request['lastbilldate'] = 'lastbilldate';
                        $this->data['error'] = '0';



                        $this->customertype();
                        $this->customerinfo();
                        if ($this->debug)
                            print_r($this->accountinfo);
                        $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
                    }
                    $this->request = Array();
                    $this->accountinfo = Array();
                } else if ($account_type == 'RESELLER' and $account_level == '1') {
                    if (strlen(trim($data['reseller1_assign_date'])) > 0 and $data['reseller1_assign_date'] != '0000-00-00') {
                        if (strlen(trim($data['r1lastbilldate'])) > 0 and $data['lastbilldate'] != '0000-00-00') {
                            $date1 = date_create($date_in);
                            $date2 = date_create($data['r1lastbilldate']);
                            $diff = date_diff($date1, $date2);
                            $daycount = $diff->format("%a");
                        }
                        if ($daycount < 150) {
                            $date = date('Y-m-d', strtotime($data['r1lastbilldate'] . ' +1 day'));
                        } else {
                            $date = $date_in;
                        }
                        if ($this->requesttype == 'SERVICE') {
                            $date = $date_in; //date('Y-m-d', strtotime($data['lastbilldate'] . ' +1 day'));
                        }

                        $this->request['account_id'] = $account_id;
                        $this->request['service_number'] = $data['did_number'];
                        $this->request['channels'] = $data['channels'];
                        $this->request['request_from'] = 'service';
                        $this->request['carrier_id'] = $data['carrier_id'];
                        $this->request['lastbilldate'] = 'r1lastbilldate';
                        $this->data['error'] = '0';
                        $this->customertype();
                        $this->resellerinfo();
                        if ($this->debug)
                            print_r($this->accountinfo);
                        $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
                    }
                    $this->request = Array();
                    $this->accountinfo = Array();
                } else if ($account_type == 'RESELLER' and $account_level == '2') {
                    if (strlen(trim($data['reseller2_assign_date'])) > 0 and $data['reseller2_assign_date'] != '0000-00-00') {
                        if (strlen(trim($data['r2lastbilldate'])) > 0 and $data['lastbilldate'] != '0000-00-00') {
                            $date1 = date_create($date_in);
                            $date2 = date_create($data['r2lastbilldate']);
                            $diff = date_diff($date1, $date2);
                            $daycount = $diff->format("%a");
                        }
                        if ($daycount < 150) {
                            $date = date('Y-m-d', strtotime($data['r2lastbilldate'] . ' +1 day'));
                        } else {
                            $date = $date_in;
                        }
                        if ($this->requesttype == 'SERVICE') {
                            $date = $date_in; // date('Y-m-d', strtotime($data['lastbilldate'] . ' +1 day'));
                        }

                        $this->request['account_id'] = $account_id;
                        $this->request['lastbilldate'] = 'r2lastbilldate';
                        $this->request['service_number'] = $data['did_number'];
                        $this->request['channels'] = $data['channels'];
                        $this->request['request_from'] = 'service';
                        $this->request['carrier_id'] = $data['carrier_id'];
                        $this->data['error'] = '0';
                        $this->customertype();

                        $this->resellerinfo();
                        if ($this->debug)
                            print_r($this->accountinfo);
                        $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
                    }
                    $this->request = Array();
                    $this->accountinfo = Array();
                } else if ($account_type == 'RESELLER' and $account_level == '3') {
                    if (strlen(trim($data['reseller3_assign_date'])) > 0 and $data['reseller3_assign_date'] != '0000-00-00') {
                        if (strlen(trim($data['r3lastbilldate'])) > 0 and $data['lastbilldate'] != '0000-00-00') {
                            $date1 = date_create($date_in);
                            $date2 = date_create($data['r3lastbilldate']);
                            $diff = date_diff($date1, $date2);
                            $daycount = $diff->format("%a");
                        }
                        if ($daycount < 150) {
                            $date = date('Y-m-d', strtotime($data['r3lastbilldate'] . ' +1 day'));
                        } else {
                            $date = $date_in;
                        }
                        if ($this->requesttype == 'SERVICE') {
                            $date = $date_in; //date('Y-m-d', strtotime($data['lastbilldate'] . ' +1 day'));
                        }

                        $this->request['account_id'] = $account_id;
                        $this->request['lastbilldate'] = 'r3lastbilldate';
                        $this->request['service_number'] = $data['did_number'];
                        $this->request['channels'] = $data['channels'];
                        $this->request['request_from'] = 'service';
                        $this->request['carrier_id'] = $data['carrier_id'];
                        $this->data['error'] = '0';
                        $this->customertype();
                        $this->resellerinfo();
                        if ($this->debug)
                            print_r($this->accountinfo);
                        $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
                        $this->request = Array();
                        $this->accountinfo = Array();
                    }
                }
            }
        }
        $query = '';
    }

    function cdr_service($account, $date, $account_type) {
        $date = date('Y-m-d', strtotime($date . ' -1 day'));
        $query = sprintf("SELECT account_id, account_type, account_level, parent_account_id FROM  account ;");
        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $result = $query->result_array();
        if (count($result) > 0) {
            foreach ($result as $data) {
                $account_id = $data['account_id'];
                $account_type = $data['account_type'];
                $account_level = $data['account_level'];
                $this->usage_data($account_id, $account_type, $account_level, $date);
            }
        }
    }

    function usage_data($account_id, $account_type, $account_level, $date) {
        $DB1 = $this->load->database('cdrdb', true);
        $tablename = date('Ym', strtotime($date)) . "_ratedcdr";
        if ($this->debug)
            echo " $account_id, $account_type, $account_level, $date ,  $tablename" . PHP_EOL;
        if ($account_type == 'CUSTOMER') {
            if ($account_type == 'CUSTOMER' and $account_level == '1') {
// Direct Customer               
                $subquery = "";
                if (strlen(trim($account_id)) > 0) {
                    $subquery = " and customer_account_id = '" . $account_id . "'";
                } else {
                    $subquery = "";
                }
                $query_account = sprintf("SELECT HIGH_PRIORITY customer_account_id as account_id FROM %s where  '%s' = date(end_time) %s GROUP BY customer_account_id;", $tablename, $date, $subquery);
                $account_id_cdr = 'customer_account_id';
                $rule_type_cdr = 'cdr_type';
                $service_number_cdr = 'customer_destination';
                $billing_date_cdr = 'end_time';
                $unit_cdr = 'customer_duration';
                $rate_cdr = 'customer_rate';
                $cost_cdr = 'customer_callcost';
                $totalcost_cdr = 'customer_callcost_total';
                $sallerunit_cdr = 'carrier_duration';
                $sallerrate_cdr = 'carrier_rate';
                $sallercost_cdr = 'carrier_callcost_inclusive_usercurrency';
                $totalsallercost_cdr = 'carrier_callcost_total_usercurrency';
                $startdate_cdr = 'end_time';
                $enddate_cdr = 'end_time';
                $createdate_cdr = 'end_time';
            } elseif ($account_type == 'CUSTOMER' and $account_level == '2') {
// R1 Customers
                $subquery = "";
                if (strlen(trim($account_id)) > 0) {
                    $subquery = " and customer_account_id = '" . $account_id . "'";
                } else {
                    $subquery = "";
                }
                $query_account = sprintf("SELECT HIGH_PRIORITY customer_account_id account_id FROM %s where  '%s' = date(end_time) %s GROUP BY customer_account_id;", $tablename, $date, $subquery);
                $account_id_cdr = 'customer_account_id';
                $rule_type_cdr = 'cdr_type';
                $service_number_cdr = 'customer_destination';
                $billing_date_cdr = 'end_time';
                $unit_cdr = 'customer_duration';
                $rate_cdr = 'customer_rate';
                $cost_cdr = 'customer_callcost';
                $totalcost_cdr = 'customer_callcost_total';
                $sallerunit_cdr = 'reseller1_duration';
                $sallerrate_cdr = 'reseller1_rate';
                $sallercost_cdr = 'reseller1_callcost';
                $totalsallercost_cdr = 'reseller1_callcost_total';
                $startdate_cdr = 'end_time';
                $enddate_cdr = 'end_time';
                $createdate_cdr = 'end_time';
            } elseif ($account_type == 'CUSTOMER' and $account_level == '3') {
// R2 Customer
                $subquery = "";
                if (strlen(trim($account_id)) > 0) {
                    $subquery = " and customer_account_id = '" . $account_id . "'";
                } else {
                    $subquery = "";
                }
                $query_account = sprintf("SELECT HIGH_PRIORITY customer_account_id account_id FROM %s where  '%s' = date(end_time) %s GROUP BY customer_account_id;", $tablename, $date, $subquery);

                $account_id_cdr = 'customer_account_id';
                $rule_type_cdr = 'cdr_type';
                $service_number_cdr = 'customer_destination';
                $billing_date_cdr = 'end_time';
                $unit_cdr = 'customer_duration';
                $rate_cdr = 'customer_rate';
                $cost_cdr = 'customer_callcost';
                $totalcost_cdr = 'customer_callcost_total';
                $sallerunit_cdr = 'reseller2_duration';
                $sallerrate_cdr = 'reseller2_rate';
                $sallercost_cdr = 'reseller2_callcost';
                $totalsallercost_cdr = 'reseller2_callcost_total';
                $startdate_cdr = 'end_time';
                $enddate_cdr = 'end_time';
                $createdate_cdr = 'end_time';
            } elseif ($account_type == 'CUSTOMER' and $account_level == '4') {
// R3 Customers
                $subquery = "";
                if (strlen(trim($account_id)) > 0) {
                    $subquery = " and customer_account_id = '" . $account_id . "'";
                } else {
                    $subquery = "";
                }
                $query_account = sprintf("SELECT HIGH_PRIORITY customer_account_id account_id FROM %s where  '%s' = date(end_time) %s GROUP BY customer_account_id;", $tablename, $date, $subquery);

                $account_id_cdr = 'customer_account_id';
                $rule_type_cdr = 'cdr_type';
                $service_number_cdr = 'customer_destination';
                $billing_date_cdr = 'end_time';
                $unit_cdr = 'customer_duration';
                $rate_cdr = 'customer_rate';
                $cost_cdr = 'customer_callcost';
                $totalcost_cdr = 'customer_callcost_total';
                $sallerunit_cdr = 'reseller3_duration';
                $sallerrate_cdr = 'reseller3_rate';
                $sallercost_cdr = 'reseller3_callcost';
                $totalsallercost_cdr = 'reseller3_callcost_total';
                $startdate_cdr = 'end_time';
                $enddate_cdr = 'end_time';
                $createdate_cdr = 'end_time';
            }
        } else {
            if ($account_type == 'RESELLER' and $account_level == '1') {
                $subquery = "";
                if (strlen(trim($account_id)) > 0) {
                    $subquery = " and reseller1_account_id = '" . $account_id . "'";
                } else {
                    $subquery = "";
                }
                $query_account = sprintf("SELECT HIGH_PRIORITY reseller1_account_id account_id FROM %s where  '%s' = date(end_time) %s GROUP BY reseller1_account_id;", $tablename, $date, $subquery);
                $account_id_cdr = 'reseller1_account_id';
                $rule_type_cdr = 'cdr_type';
                $service_number_cdr = 'reseller1_destination';
                $billing_date_cdr = 'end_time';
                $unit_cdr = 'reseller1_duration';
                $rate_cdr = 'reseller1_rate';
                $cost_cdr = 'reseller1_callcost';
                $totalcost_cdr = 'reseller1_callcost_total';
                $sallerunit_cdr = 'carrier_duration';
                $sallerrate_cdr = 'carrier_rate';
                $sallercost_cdr = 'carrier_callcost';
                $totalsallercost_cdr = 'carrier_callcost_total';
                $startdate_cdr = 'end_time';
                $enddate_cdr = 'end_time';
                $createdate_cdr = 'end_time';
            } else if ($account_type == 'RESELLER' and $account_level == '2') {
                $subquery = "";
                if (strlen(trim($account_id)) > 0) {
                    $subquery = " and reseller2_account_id = '" . $account_id . "'";
                } else {
                    $subquery = "";
                }
                $query_account = sprintf("SELECT HIGH_PRIORITY reseller2_account_id account_id FROM %s where  '%s' = date(end_time) %s GROUP BY reseller2_account_id;", $tablename, $date, $subquery);
                $account_id_cdr = 'reseller2_account_id';
                $rule_type_cdr = 'cdr_type';
                $service_number_cdr = 'reseller2_destination';
                $billing_date_cdr = 'end_time';
                $unit_cdr = 'reseller2_duration';
                $rate_cdr = 'reseller2_rate';
                $cost_cdr = 'reseller2_callcost';
                $totalcost_cdr = 'reseller2_callcost_total';
                $sallerunit_cdr = 'reseller1_duration';
                $sallerrate_cdr = 'reseller1_rate';
                $sallercost_cdr = 'reseller1_callcost';
                $totalsallercost_cdr = 'reseller1_callcost_total';
                $startdate_cdr = 'end_time';
                $enddate_cdr = 'end_time';
                $createdate_cdr = 'end_time';
            } else if ($account_type == 'RESELLER' and $account_level == '3') {
                $subquery = "";
                if (strlen(trim($account_id)) > 0) {
                    $subquery = " and reseller3_account_id = '" . $account_id . "'";
                } else {
                    $subquery = "";
                }
                $query_account = sprintf("SELECT HIGH_PRIORITY reseller3_account_id account_id FROM %s where  '%s' = date(end_time) %s GROUP BY reseller3_account_id;", $tablename, $date, $subquery);
                $account_id_cdr = 'reseller3_account_id';
                $rule_type_cdr = 'cdr_type';
                $service_number_cdr = 'reseller3_destination';
                $billing_date_cdr = 'end_time';
                $unit_cdr = 'reseller3_duration';
                $rate_cdr = 'reseller3_rate';
                $cost_cdr = 'reseller3_callcost';
                $totalcost_cdr = 'reseller3_callcost_total';
                $sallerunit_cdr = 'reseller2_duration';
                $sallerrate_cdr = 'reseller2_rate';
                $sallercost_cdr = 'reseller2_callcost';
                $totalsallercost_cdr = 'reseller2_callcost_total';
                $startdate_cdr = 'end_time';
                $enddate_cdr = 'end_time';
                $createdate_cdr = 'end_time';
            }
        }
        if ($this->debug)
            echo $query_account . PHP_EOL;
        $query = $DB1->query($query_account);
        $accountdetail = $query->result_array();
        if (count($accountdetail) > 0) {
            foreach ($accountdetail as $accountdata) {
                if (strlen($accountdata['account_id']) > 0) {
                    $yearmonth = date('Ym', strtotime($date . ' 0 day'));
                    $query = sprintf("delete from bill_account_sdr where date(billing_date) = '%s' and rule_type in ('IN','OUT') and   account_id='%s'", $date, $accountdata['account_id']);
                    $result = $this->db->query($query);
                    $query = sprintf("SELECT $account_id_cdr as account_id , $rule_type_cdr  as rule_type, $service_number_cdr  as service_number, $billing_date_cdr  as billing_date, sum($unit_cdr)  as unit, $rate_cdr  as rate, sum($cost_cdr)  as cost, sum($totalcost_cdr)  as totalcost, sum($sallerunit_cdr)  as sallerunit, $sallerrate_cdr  as sallerrate, sum($sallercost_cdr)  as sallercost, sum($totalsallercost_cdr)  as totalsallercost, $startdate_cdr  as startdate, $enddate_cdr  as enddate, $createdate_cdr  as createdate from $tablename where $account_id_cdr = '%s' and date(end_time) = '%s' group by rule_type, $service_number_cdr , rate, sallerrate;", $accountdata['account_id'], $date);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $query = $DB1->query($query);
                    $result = $query->result_array();
                    $data = '';
                    if (count($result) > 0) {
                        if ($this->debug)
                            print_r($result);
                        foreach ($result as $result_data) {
                            foreach ($result_data as $key => $value) {
                                $data = $data . "$key = '" . addslashes($value) . "',";
                            }
                            $livecalldata = rtrim($data, ',');
                            $query = "insert into bill_account_sdr  set " . $livecalldata . ";";
                            if ($this->debug)
                                echo $query . PHP_EOL;
                            $result = $this->db->query($query);
                            $query = '';
                            $data = '';
                            $livecalldata = '';
                        }
                    }
                }
            }
        }
    }

    function month_balance($account_id, $yearmonth, $usertype) {
        try {
            if ($account_id == '' || $yearmonth == '')
                throw new Exception('Insufficient data');
            $openingbalance = $addbalance = $removebalance = $usage = 0;
            $sdr_terms = array();
            $sql = "SELECT term_group, term, display_text, cost_calculation_formula FROM sys_sdr_terms WHERE cost_calculation_formula IN('+','-') ORDER BY term_group, term";
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $term = $row['term'];
                $sdr_terms[$term] = $row;
            }
            $sql = "SELECT rule_type, billing_date, service_number, totalcost, startdate, enddate 
			 FROM bill_account_sdr 
			 WHERE account_id='$account_id' AND DATE_FORMAT(billing_date, '%Y%m')='$yearmonth' 
			 ORDER BY billing_date ASC ";

            $query = $this->db->query($sql);
            $num_rows = $query->num_rows();
            if ($num_rows > 0) {
                foreach ($query->result_array() as $sdr_data) {
                    $rule_type = $sdr_data['rule_type'];
                    if (isset($sdr_terms[$rule_type])) {
                        $term_array = $sdr_terms[$rule_type];
                        $term_group = $term_array['term_group'];
                        $cost_calculation_formula = trim($term_array['cost_calculation_formula']);
                        $total_cost = $sdr_data['totalcost'];
                        if ($term_group == 'opening') {
                            if ($cost_calculation_formula == '+') {
                                $openingbalance = $openingbalance + $total_cost;
                            } elseif ($cost_calculation_formula == '-') {
                                $openingbalance = $openingbalance - $total_cost;
                            }
                        } elseif ($term_group == 'balance') {
                            if ($cost_calculation_formula == '+') {
                                $addbalance = $addbalance + $total_cost;
                            } elseif ($cost_calculation_formula == '-') {
                                $removebalance = $removebalance + $total_cost;
                            }
                        } else {
                            if ($cost_calculation_formula == '+') {
                                $usage = $usage + $total_cost;
                            } elseif ($cost_calculation_formula == '-') {
                                $usage = $usage + $total_cost;
                            }
                        }
                    }
                }
            }

            $current_balance = $openingbalance + $addbalance - $removebalance - $usage;
            $cost = 0;
            $date = date('Ym');
            $calls_cdr = date('Ym', strtotime($date)) . "_ratedcdr";
            $sql = '';
            if ($usertype == 'user') {
                $sql = sprintf("select sum(cost) cost from (SELECT sum(customer_callcost_total) cost FROM %s where customer_account_id = '%s' and CURDATE() = date(end_time) ) a;", $calls_cdr, $account_id);
            } elseif ($usertype == 'r1') {
                $sql = sprintf("select sum(cost) cost from (SELECT sum(reseller1_callcost_total) cost FROM %s where reseller1_account_id = '%s' and CURDATE() = date(end_time) ) a;", $calls_cdr, $account_id);
            } elseif ($usertype == 'r2') {
                $sql = sprintf("select sum(cost) cost from (SELECT sum(reseller2_callcost_total) cost FROM %s where reseller2_account_id = '%s' and CURDATE() = date(end_time) ) a;", $calls_cdr, $account_id);
            } elseif ($usertype == 'r3') {
                $sql = sprintf("select sum(cost) cost from (SELECT sum(reseller3_callcost_total) cost FROM %s where reseller3_account_id = '%s' and CURDATE() = date(end_time) ) a;", $calls_cdr, $account_id);
            } else {
                $sql = sprintf("select sum(cost) cost from (SELECT sum(customer_callcost_total) cost FROM %s where customer_account_id = '%s' and CURDATE() = date(end_time) ) a;", $calls_cdr, $account_id);
            }

            if ($this->debug)
                echo '<br><br><br>' . $sql . '<br>';
            $this->cdrdb = $this->load->database('cdrdb', true);
            $query = $this->cdrdb->query($sql);
            $row2 = $query->row_array();
            $cost = $row2['cost'];

            if ($cost == null or $cost == '')
                $cost = 0;

            $balance = $current_balance - $cost;
            $balance2 = 0 - $balance;

            $sql = sprintf("SELECT balance FROM customer_balance WHERE account_id = '%s'", $account_id);
            $query = $this->db->query($sql);
            $row3 = $query->row_array();
            $outstanding_balance = $row3['balance'];
            if ($this->debug)
                echo "$account_id  current_balance + cost    =  $current_balance + $cost =  $balance   Now need to update $balance2  and existing balance is  $outstanding_balance \n";
            $query = sprintf("UPDATE customer_balance SET balance =  '%s' WHERE account_id = '%s';", $balance2, $account_id);
            $this->db->query($query);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function carrier_usage_data($date) {
        $date = date('Y-m-d', strtotime($date . ' -1 day'));
        $tablename = date('Ym', strtotime($date)) . "_ratedcdr";
        $query_carrier = sprintf("select carrier_id, carrier_name, if( LENGTH(trim(reseller1_account_id)) > 0, reseller1_account_id,  customer_account_id ) as account_id, 
carrier_currency_id as currency_id, 
customer_currency_id as account_currency_id, 
carrier_ratio as currency_ratio,
 cdr_type as rule_type,
 carrier_prefix as prefix, 
 carrier_destination as destination,
sum(carrier_duration) as unit,
carrier_rate as rate, 
sum(carrier_callcost_total) as carriercost,
 sum(carrier_callcost_inclusive_usercurrency) as carriercost_customer_currency,
if( LENGTH(trim(reseller1_account_id)) > 0, sum(reseller1_callcost_total),  sum(customer_callcost_total )) customer_cost,
if( LENGTH(trim(reseller1_account_id)) > 0, reseller1_rate, customer_rate) customer_rate,
date(end_time) as billing_date ,  count(id) calls
 from %s  where date(end_time) = '%s' GROUP BY billing_date, carrier_id, carrier_currency_id, carrier_rate, carrier_prefix, customer_rate, customer_currency_id, account_id;", $tablename, $date);
        if ($this->debug)
            echo " $query_carrier\n";
        $DB1 = $this->load->database('cdrdb', true);
        $query = $DB1->query($query_carrier);
        $num_rows = $query->num_rows();
        if ($num_rows > 0) {
            $usage_sql_delete = sprintf("delete from bill_carrier_sdr  where billing_date = '%s' ;", $date);
            if ($this->debug)
                echo "$usage_sql_delete\n";
            $this->db->query($usage_sql_delete);
            foreach ($query->result_array() as $carrier_data) {
                foreach ($carrier_data as $key => $value) {
                    $data = $data . "$key = '" . addslashes($value) . "',";
                }
                $sub_query = rtrim($data, ',');
                if (strlen(trim($sub_query)) > 0) {
                    $usage_sql = "insert into bill_carrier_sdr  set " . $sub_query . ";";
                    if ($this->debug)
                        echo "$usage_sql\n";
                    $this->db->query($usage_sql);
                }
                $data = '';
                $usage_sql = '';
                $sub_query = '';
            }
        }
    }

    function save_payment($data) {
        try {

            $payment_data_array = array();
            $payment_data_array['account_id'] = $data['ACCOUNTID'];
            $payment_data_array['payment_option_id'] = $data['REQUEST'];
            $payment_data_array['amount'] = $data['AMMOUNT'];
            $payment_data_array['paid_on'] = $data['PAIDON'];
            $payment_data_array['created_by'] = $data['CREATEDBY'];
            $payment_data_array['create_dt'] = date('Y-m-d H:s:i');
            $payment_data_array['notes'] = $data['NOTES'];
            $payment_data_array['payment_collection_id'] = $data['PAYMENTMETHOD'];
            $payment_data_array['transaction_id'] = $data['TRANSACTIONID'];
            $payment_data = $payment_data . "account_id= '" . trim($data['ACCOUNTID']) . "',";
            $payment_data = $payment_data . "payment_option_id= '" . trim($data['REQUEST']) . "',";
            $payment_data = $payment_data . "amount= '" . trim($data['AMMOUNT']) . "',";
            $payment_data = $payment_data . "paid_on= '" . trim($data['PAIDON']) . "',";
            $payment_data = $payment_data . "created_by= '" . trim($data['CREATEDBY']) . "',";
            $payment_data = $payment_data . "create_dt= '" . trim(date('Y-m-d H:s:i')) . "',";
            $payment_data = $payment_data . "notes= '" . trim($data['NOTES']) . "',";
            $payment_data = $payment_data . "payment_collection_id= '" . trim($data['COLLECTIONOPTION']) . "',";
            $payment_data = $payment_data . "transaction_id= '" . trim($data['TRANSACTIONID']) . "',";
            $payment_data = rtrim($payment_data, ',');
            $query = "insert into payment_history  set " . $payment_data;
            $this->db->query($query);
            $account_id = $data['ACCOUNTID'];
            $amount = $data['AMMOUNT'];

            if ($data['REQUEST'] == 'REMOVEBALANCE') {
                $sql = "update customer_balance set balance = balance + $amount where account_id = '$account_id'";
            } if ($data['REQUEST'] == 'ADDBALANCE') {
                $sql = "update customer_balance set balance = balance - $amount where account_id = '$account_id'";
            } elseif ($data['REQUEST'] == 'ADDCREDIT') {
                $sql = "update customer_balance set credit_limit = credit_limit + $amount where account_id = '$account_id'";
            } elseif ($data['REQUEST'] == 'REMOVECREDIT') {
                $sql = "update customer_balance set credit_limit = credit_limit - $amount where account_id = '$account_id'";
            } elseif ($data['REQUEST'] == 'ADDTESTBALANCE') {
                $sql = "update customer_balance set balance = balance - $amount where account_id = '$account_id'";
            } elseif ($data['REQUEST'] == 'REMOVETESTBALANCE') {
                $sql = "update customer_balance set balance = balance + $amount where account_id = '$account_id'";
            } elseif ($data['REQUEST'] == 'BALANCETRANSFERADD') {
                $sql = "update customer_balance set balance = balance - $amount where account_id = '$account_id'";
            } elseif ($data['REQUEST'] == 'BALANCETRANSFERREMOVE') {
                $sql = "update customer_balance set balance = balance + $amount where account_id = '$account_id'";
            }

            $this->db->query($sql);
            $yearmonth = date('Ym');
            $total_cost = $data['amount'];
            $service_startdate = date('Y-m-d h:s:i');
            $service_stopdate = date('Y-m-d h:s:i');
            $date = date('Y-m-d h:s:i');
            $account_id = $data['ACCOUNTID'];
            $rule_type = $data['REQUEST'];
            $service_number = $data['NOTES'];
            $billing_date = $service_startdate;
            $unit = 1;
            $rate = 0;
            $cost = $data['AMMOUNT'];
            $totalcost = $data['AMMOUNT'];
            $sallerunit = 1;
            $sallerrate = 0;
            $sallercost = 0;
            $totalsallercost = 0;
            $startdate = $service_startdate;
            $enddate = $service_stopdate;

            $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);
            $this->db->query($query_bill_account_sdr);

            if ($data['REQUEST'] == 'ADDTESTBALANCE' || $data['REQUEST'] == 'BALANCETRANSFERADD' || $data['REQUEST'] == 'ADDTESTBALANCE' || $data['REQUEST'] == 'ADDCREDIT' || $data['REQUEST'] == 'ADDBALANCE') {
                $query = sprintf("update account set status_id = '1' where account_id = '%s';", $account_id);
                $this->db->query($query);
            }

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function creditmanagement() {
        $this->userdetail = Array();
        $query = "DELETE  FROM livecalls where start_time <  DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 10 MINUTE), '%Y-%m-%d %H:%i:00');";
        if ($this->debug)
            echo $query . PHP_EOL;
        $this->db->query($query);
        $query = "DELETE  FROM livecalls where start_time <  DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MINUTE), '%Y-%m-%d %H:%i:00') and (callstatus = 'ring' or callstatus = 'progress');";
        if ($this->debug)
            echo $query . PHP_EOL;
        $this->db->query($query);
        $query = sprintf("SELECT id, account_id, credit_amount, execution_date, status_id FROM credit_scheduler where execution_date < NOW() and status_id = '0'");
        if ($this->debug)
            echo $query . PHP_EOL;
        $query = $this->db->query($query);
        $detail = $query->result_array();
        if (count($detail) > 0) {
            foreach ($detail as $data) {
                if ($data['status_id'] == '0') {
                    $query = sprintf("SELECT id,  credit_limit, account_id, balance  from customer_balance where account_id = '%s' limit 1;", $data['account_id']);

                
                    $aftercredit_limit = 0;
                    $this->userdetail['credit_limit'] = 0;
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $query = $this->db->query($query);
                    $balance = $query->result_array();
                    if (count($balance) > 0) {
                        foreach ($balance[0] as $key => $value) {
                            $this->userdetail[$key] = $value;
                        }
                        $aftercredit_limit = $this->userdetail['credit_limit'] - $data['credit_amount'];
                        $credit_limit = $this->userdetail['credit_limit'];
                    }
                    $data['rule_type'] = 'REMOVECREDIT';
                    $data['notes'] = "Auto Credit deduction from scheduler. Befor process Credit limit was $credit_limit and after deduction credit limit is $aftercredit_limit ";
                    $data['paid_on'] = date('Y-m-d H:i:s');
                    $data['notes'] = trim($data['notes']);
                    $data['amount'] = $data['credit_amount'];
                    $data['created_by'] = '';
                    $query = sprintf("insert into  payment_history (account_id, payment_option_id, amount, paid_on, notes, created_by, create_dt ) values('%s', '%s', '%s', '%s', '%s','%s',now());", $data['account_id'], $data['rule_type'], $data['amount'], $data['paid_on'], $data['notes'], $data['created_by']);

                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $this->db->query($query);
                    $account_id = $data['account_id'];
                    $rule_type = $data['rule_type'];
                    $service_number = $data['rule_type'];
                    $billing_date = date("Y-m-d H:i:s");
                    $unit = 1;
                    $rate = 0;
                    $cost = abs($data['amount']);
                    $totalcost = abs($data['amount']);
                    $sallerunit = 0;
                    $sallerrate = 0;
                    $sallercost = 0;
                    $totalsallercost = 0;
                    $startdate = date("Y-m-d H:i:s");
                    $enddate = date("Y-m-d H:i:s");
                    $query_bill_account_sdr = sprintf("INSERT INTO bill_account_sdr (account_id, rule_type, service_number, billing_date, unit, rate, cost, totalcost, sallerunit, sallerrate, sallercost, totalsallercost, startdate, enddate, createdate) values ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now());", $account_id, $rule_type, $service_number, $billing_date, $unit, $rate, $cost, $totalcost, $sallerunit, $sallerrate, $sallercost, $totalsallercost, $startdate, $enddate);

                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $this->db->query($query_bill_account_sdr);

                    $query = sprintf("update customer_balance set credit_limit = credit_limit - '%s' where account_id = '%s';", $data['amount'], $data['account_id']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $this->db->query($query);

                    $query = sprintf("update credit_scheduler set status_id = '1' where account_id = '%s' and execution_date = '%s';", $data['account_id'], $data['execution_date']);
                    if ($this->debug)
                        echo $query . PHP_EOL;
                    $this->db->query($query);
                }
            }
        }
    }

}
