<?php

// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
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

class APIS extends PDO {

    var $fetch_mode = PDO::FETCH_ASSOC;
    var $stmt;
    var $dbcdr = '';
    var $dbswitch = '';
    var $status = 'OK';

    function __construct() {
        error_reporting(0);
        $this->dbconnect();
    }

    function connection($dbname) {
        if ($dbname == 'CDR') {
            try {
                $this->dbcdr = new PDO(CDR_DSN, CDR_DSN_LOGIN, CDR_DSN_PASSWORD);
            } catch (Exception $e) {
                $log = CDR_DSN . " " . CDR_DSN_LOGIN . " " . CDR_DSN_PASSWORD;
                $this->writelog('dbcdr DB connection issue ' . $log);
                exit('App shoutdown');
            }
        } else if ($dbname == 'SWITCH') {
            try {
                $this->dbswitch = new PDO(SWITCH_DSN, SWITCH_DSN_LOGIN, SWITCH_DSN_PASSWORD);
            } catch (Exception $e) {
                $log = SWITCH_DSN . " " . SWITCH_DSN_LOGIN . " " . SWITCH_DSN_PASSWORD;
                $this->writelog('Switch DB connection issue ' . $log);
                exit('App shoutdown');
            }
        }
    }

    function dbconnect() {
        $this->connection('CDR');
        $this->connection('SWITCH');
    }

    function query($dbname, $query) {
        if ($dbname == 'SWITCH') {
            $this->stmt = $this->dbswitch->prepare($query);
            return $this;
        } elseif ($dbname == 'CDR') {
            $this->stmt = $this->dbcdr->prepare($query);
            return $this;
        }
    }

    function execute() {
        return $this->stmt->execute();
    }

    function resultset() {
        $this->execute();
        return $this->stmt->fetchAll($this->fetch_mode);
    }

    function single() {
        $this->execute();
        return $this->stmt->fetch($this->fetch_mode);
    }

    function charges($charges, $date) {
        $no_of_days = date('t', strtotime($date));
        $current_day = date('d', strtotime($date));
        $billingdays = ($no_of_days - $current_day) + 1;
        $current_month_charges = ($charges / $no_of_days) * $billingdays;
        return $current_month_charges;
    }

    function dp($number, $dp) {
        $num = ceil($number * pow(10, $dp)) / pow(10, $dp);
        return number_format($num, $dp, '.', '');
    }

    function exclusive_tax($tax, $cost, $taxon = 100) {
        $tax_amount = 0;
        if ($tax > 0 and $cost > 0)
            $tax_amount = (($cost * $tax) / $taxon);
        return $tax_amount;
    }

    function inclusive_tax($tax, $cost, $taxon = 100) {
        $tax_amount = 0;
        if ($tax > 0 and $cost > 0)
            $tax_amount = ($cost / ($taxon + $tax)) * $tax;
        return $tax_amount;
    }

    function currencies_data() {
        $query = sprintf("select id, currency_id,ratio,date  from sys_currencies_conversions where id in (select max(id) from sys_currencies_conversions group by currency_id) order by id desc;");

        $this->query('SWITCH', $query);
        $this->currencies = $this->resultset();
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

    function tax_calculation($data, $current_month_charges) {
        $data['total_tax'] = 0;
        $data['tax1_cost'] = 0;
        $data['tax1_cost'] = 0;
        $data['tax2_cost'] = 0;
        $data['tax2_cost'] = 0;
        $data['tax3_cost'] = 0;
        $data['tax3_cost'] = 0;
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

    function orderBy(&$ary, $clause, $ascending = true) {
        $clause = str_ireplace('order by', '', $clause);
        $clause = preg_replace('/\s+/', ' ', $clause);
        $keys = explode(',', $clause);
        $dirMap = array('desc' => 1, 'asc' => -1);
        $def = $ascending ? -1 : 1;
        $keyAry = array();
        $dirAry = array();
        foreach ($keys as $key) {
            $key = explode(' ', trim($key));
            $keyAry[] = trim($key[0]);
            if (isset($key[1])) {
                $dir = strtolower(trim($key[1]));
                $dirAry[] = $dirMap[$dir] ? $dirMap[$dir] : $def;
            } else {
                $dirAry[] = $def;
            }
        }
        $fnBody = '';
        for ($i = count($keyAry) - 1; $i >= 0; $i--) {
            $k = $keyAry[$i];
            $t = $dirAry[$i];
            $f = -1 * $t;
            $aStr = '$a[\'' . $k . '\']';
            $bStr = '$b[\'' . $k . '\']';
            if (strpos($k, '(') !== false) {
                $aStr = '$a->' . $k;
                $bStr = '$b->' . $k;
            }
            if ($fnBody == '') {
                $fnBody .= "if({$aStr} == {$bStr}) { return 0; }\n";
                $fnBody .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";
            } else {
                $fnBody = "if({$aStr} == {$bStr}) {\n" . $fnBody;
                $fnBody .= "}\n";
                $fnBody .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";
            }
        }
        if ($fnBody) {
            $sortFn = create_function('$a,$b', $fnBody);
            usort($ary, $sortFn);
        }
    }

    function encrypt($string) {
        $result = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->auth_key, ($i % strlen($this->auth_key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }

    function decrypt($string) {
        $result = '';
        $string = base64_decode($string);
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->auth_key, ($i % strlen($this->auth_key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

    function writelog($log) {
        $datestr = date("dmY/");
        if (!file_exists(LOGPATH . $datestr)) {
            mkdir(LOGPATH . $datestr, 0777, true);
        }
        $this->fh = fopen(LOGPATH . $datestr . "service.log", 'a+');
        $datestr = date("M d H:i:s");
        $log = "$datestr :: $log\n";
        fwrite($this->fh, $log);
    }

    function charges_cal($charges) {
        $no_of_days = date('t');
        $current_day = date('d');
        $billingdays = ($no_of_days - $current_day) + 1;
        $current_month_charges = ($charges / $no_of_days) * $billingdays;
        return $current_month_charges;
    }

    function format_period($seconds_input) {
        $hours = (int) ($minutes = (int) ($seconds = (int) ($milliseconds = (int) ($seconds_input * 1000)) / 1000) / 60) / 60;
        return $hours . ':' . ($minutes % 60) . ':' . ($seconds % 60) . (($milliseconds === 0) ? '' : '.' . rtrim($milliseconds % 1000, '0'));
    }

    function main($data) {

        try {
            foreach ($data as $key => $value) {
                $this->request[$key] = $value;
            }
            $this->CustomerInfo();
            if ($data['request'] == 'REMOVEBALANCE' or $data['request'] == 'ADDBALANCE' or $data['request'] == 'ADDCREDIT' or $data['request'] == 'REMOVECREDIT' or $data['request'] == 'ADDTESTBALANCE' or $data['request'] == 'REMOVETESTBALANCE' or $data['request'] == 'BALANCETRANSFERADD' or $data['request'] == 'BALANCETRANSFERREMOVE') {
                $result = $this->save_payment($data);
                if ($result) {
                    header('Content-Type: application/json');
                    $op = array('status' => 'SUCCESS', 'message' => "Added successfully", 'error' => 0);
                    echo json_encode($op);
                    return;
                } else {
                    $error_message = "";
                    header('Content-Type: application/json');
                    $op = array('status' => 'FAILED', 'message' => $error_message, 'error' => 1);
                    echo json_encode($op);
                    return;
                }
            } elseif ($data['request'] == 'UPDATETARIFFCHARGES') {

                $date = date('Y-m-d');
                $result = $this->updatetariff($data, $date);
                if ($result) {
                    header('Content-Type: application/json');
                    $op = array('status' => 'SUCCESS', 'message' => "Account added successfully", 'error' => 0);
                    echo json_encode($op);
                    return;
                } else {
                    $error_message = "";
                    header('Content-Type: application/json');
                    $op = array('status' => 'FAILED', 'message' => $error_message, 'error' => 1);
                    echo json_encode($op);
                    return;
                }
            } elseif ($data['request'] == 'TARIFFCHARGES') {
                $date = date('Y-m-d');
                $result = $this->newaccountsetupcharge($data, $date);
                if ($result) {
                    header('Content-Type: application/json');
                    $op = array('status' => 'SUCCESS', 'message' => "Account added successfully", 'error' => 0);
                    echo json_encode($op);
                    return;
                } else {
                    $error_message = "";
                    header('Content-Type: application/json');
                    $op = array('status' => 'FAILED', 'message' => $error_message, 'error' => 1);
                    echo json_encode($op);
                    return;
                }
            } elseif ($data['request'] == 'SERVICES' or $data['request'] == 'NEWDIDSETUP' or $data['request'] == 'DIDSETUP' or $data['request'] == 'DIDRENTAL' or $data['request'] == 'DIDCANCEL' or $data['request'] == 'DIDEXTRACHRENTAL') {
                $date = date('Y-m-d');
                $didsetup = 0;
                $rental = 0;
                $extrachannels = 0;
                if ($data['request'] == 'NEWDIDSETUP' or $data['request'] == 'DIDSETUP') {
                    $didsetup = 1;
                    $rental = 1;
                }
                if ($data['request'] == 'SERVICES' or $data['request'] == 'DIDRENTAL') {
                    $rental = 1;
                }
                if ($data['request'] == 'DIDEXTRACHRENTAL') {
                    $extrachannels = 0;
                }
                $result = $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
                if ($result) {
                    header('Content-Type: application/json');
                    $op = array('status' => 'SUCCESS', 'message' => "DID added successfully", 'error' => 0);
                    echo json_encode($op);
                    return;
                } else {
                    $error_message = $this->data['message'];
                    header('Content-Type: application/json');
                    $op = array('status' => 'FAILED', 'message' => $error_message, 'error' => 1);
                    echo json_encode($op);
                    return;
                }
            }
            if ($result) {
                header('Content-Type: application/json');
                $op = array('status' => 'SUCCESS', 'message' => "Added successfully", 'error' => 0);
                echo json_encode($op);
                return;
            } else {
                $error_message = $this->data['message'];
                header('Content-Type: application/json');
                $op = array('status' => 'FAILED', 'message' => $error_message, 'error' => 1);
                echo json_encode($op);
                return;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            header('Content-Type: application/json');
            $op = array('status' => 'FAILED', 'message' => $error_message, 'error' => 1);
            echo json_encode($op);
            return;
        }
    }

    function updatetariff($data, $date) {
        try {
            $didcharge_data_array = array();
            $didcharge_data_array['account_id'] = $data['account_id'];
            $didcharge_data_array['payment_option_id'] = $data['request'];
            $didcharge_data_array['amount'] = $data['amount'];
            $didcharge_data_array['paid_on'] = $data['paid_on'];
            $didcharge_data_array['created_by'] = $data['created_by'];
            $didcharge_data_array['create_dt'] = date('Y-m-d H:s:i');
            $didcharge_data_array['notes'] = $data['service_number'];
            $yearmonth = date('Ym');
            $query = sprintf("select monthly_charges  from tariff where tariff_id ='%s' limit 1;", $this->request['service_number']);
            $this->query('SWITCH', $query);
            $tdata = $this->resultset();
            if (count($tdata) > 0) {
                foreach ($tdata as $fdata) {
                    if ($fdata['monthly_charges'] > 0)
                        $data['amount'] = $fdata['monthly_charges'];
                    else
                        $data['amount'] = 0;
                }
            }

            $total_cost = $data['amount'];
            $total_cost = $this->charges_cal($total_cost);
            $charges_data = $this->tax_calculation($this->accountinfo, $total_cost);
            $service_startdate = date('Y-m-d h:s:i');
            $service_stopdate = date('Y-m-d h:s:i');
            $date = date('Y-m-d h:s:i');
            if ($data['request'] == 'UPDATETARIFFCHARGES') {
                $this->request['rule_type'] = 'TARIFFCHARGES';
                $service_stopdate = date('Y-m-t h:s:i');
                $request = 'TARIFFCHARGES';
                $this->request['yearmonth'] = date('Ym');
                $query = sprintf(" INSERT INTO customer_sdr (account_id, rule_type, yearmonth, service_number, service_charges, tax1, tax1_cost, tax2, tax2_cost, tax3, tax3_cost, total_tax, cost, total_cost, service_startdate, service_stopdate, seller_tax1, seller_tax2, seller_tax3, seller_tax1_cost, seller_tax2_cost, seller_tax3_cost, seller_cost, total_seller_cost, action_date,actiondate,carrier_cost, total_carrier_cost) VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now(), '%s', '%s', '%s');", $this->request['account_id'], $this->request['rule_type'], $this->request['yearmonth'], $this->request['service_number'], $this->request['service_charges'], $this->accountinfo['tax1'], $charges_data['tax1_cost'], $this->accountinfo['tax2'], $charges_data['tax2_cost'], $this->accountinfo['tax3'], $charges_data['tax3_cost'], $charges_data['total_tax'], $charges_data['cost'], $charges_data['total_cost'], $service_startdate, $service_startdate, 0, 0, 0, 0, 0, 0, 0, 0, $date, 0, 0);
                $this->writelog($query);
                $this->query('SWITCH', $query);
                $this->execute();
                $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $didcharge_data_array['account_id']);
                $this->query('SWITCH', $query);
                $this->execute();
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            return $e->getMessage();
        }
    }

    function newaccountsetupcharge($data, $date) {
        try {
            $didcharge_data_array = array();
            $didcharge_data_array['account_id'] = $data['account_id'];
            $didcharge_data_array['payment_option_id'] = $data['request'];
            $didcharge_data_array['amount'] = $data['amount'];
            $didcharge_data_array['paid_on'] = $data['paid_on'];
            $didcharge_data_array['created_by'] = $data['created_by'];
            $didcharge_data_array['create_dt'] = date('Y-m-d H:s:i');
            $didcharge_data_array['notes'] = $data['service_number'];
            $yearmonth = date('Ym');
            $query = sprintf("select monthly_charges  from tariff where tariff_id ='%s' limit 1;", $data['service_number']);
            $this->query('SWITCH', $query);
            $tdata = $this->resultset();
            if (count($tdata) > 0) {
                foreach ($tdata as $fdata) {
                    if ($fdata['monthly_charges'] > 0)
                        $data['amount'] = $fdata['monthly_charges'];
                    else
                        $data['amount'] = 0;
                }
            }
            $total_cost = $data['amount'];
            $service_startdate = date('Y-m-d h:s:i');
            $service_stopdate = date('Y-m-d h:s:i');
            $date = date('Y-m-d h:s:i');
            if ($data['request'] == 'TARIFFCHARGES') {

                $request = 'OPENINGBALANCE';
                $query = sprintf("INSERT INTO customer_sdr( account_id, rule_type, yearmonth, service_number, service_charges, detail, otherdata, action_date, tax1_cost, tax2_cost, tax3_cost, cost, total_cost, total_tax, service_startdate, service_stopdate,tax1,tax2,tax3,actiondate) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', Now(), '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s');", $didcharge_data_array['account_id'], $request, $yearmonth, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, $service_startdate, $service_stopdate, 0, 0, 0, $date);

                $this->query('SWITCH', $query);
                $this->execute();
                $service_stopdate = date('Y-m-t h:s:i');
                $request = 'TARIFFCHARGES';
                $this->request['rule_type'] = 'TARIFFCHARGES';
                $this->request['yearmonth'] = date('Ym');
                $this->request['service_charges'] = $data['amount'];
                $total_cost = $this->charges_cal($total_cost);
                $charges_data = $this->tax_calculation($this->accountinfo, $total_cost);
                $query = sprintf(" INSERT INTO customer_sdr (account_id, rule_type, yearmonth, service_number, service_charges, tax1, tax1_cost, tax2, tax2_cost, tax3, tax3_cost, total_tax, cost, total_cost, service_startdate, service_stopdate, seller_tax1, seller_tax2, seller_tax3, seller_tax1_cost, seller_tax2_cost, seller_tax3_cost, seller_cost, total_seller_cost, action_date,actiondate,carrier_cost, total_carrier_cost) VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now(), '%s', '%s', '%s');", $this->request['account_id'], $this->request['rule_type'], $this->request['yearmonth'], $this->request['service_number'], $this->request['service_charges'], $this->accountinfo['tax1'], $charges_data['tax1_cost'], $this->accountinfo['tax2'], $charges_data['tax2_cost'], $this->accountinfo['tax3'], $charges_data['tax3_cost'], $charges_data['total_tax'], $charges_data['cost'], $charges_data['total_cost'], $service_startdate, $service_startdate, 0, 0, 0, 0, 0, 0, 0, 0, $date, 0, 0);

                $this->query('SWITCH', $query);
                $this->execute();
                $query = sprintf("INSERT INTO customer_balance( account_id, credit_limit, balance, maxcredit_limit) VALUES ('%s', '%s', '%s', '%s' );", $didcharge_data_array['account_id'], 0, 0, 0);

                $this->query('SWITCH', $query);
                $this->execute();


                $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $didcharge_data_array['account_id']);
                $this->query('SWITCH', $query);
                $this->execute();

                return true;
            }
        } catch (Exception $ex) {
            return $e->getMessage();
        }
    }

    function CustomerInfo($reseller_id = '') {
        $this->data['error'] = '0';
        $this->request['detail'] = '';
        $this->request['otherdata'] = '';
        $this->request['service_startdate'] = date("Y-m-d H:i:s");
        $this->request['service_stopdate'] = date("Y-m-t 23:59:59");
        if ($reseller_id != '')
            $account_id = $reseller_id;
        else
            $account_id = $this->request['account_id'];

        $query = sprintf("SELECT  customers.emailaddress, customers.company_name, account.currency_id, customers.billing_type, account.dp, account.tariff_id, account.tax3, account.tax2, account.tax1, account.tax_type, parent_account_id, account_level  from account INNER JOIN customers on customers.account_id= account.account_id  WHERE account.account_id = '%s' and account.account_status not in ('-3','-4') limit 1;", $account_id);

        $this->query('SWITCH', $query);
        $userdetail = $this->resultset();
        if (count($userdetail) > 0) {
            if ($reseller_id != '')
                $k = 'reseller' . $userdetail[0]['account_level'];
            foreach ($userdetail[0] as $key => $value) {
                if ($reseller_id != '')
                    $this->accountinfo[$k][$key] = $value;
                else
                    $this->accountinfo[$key] = $value;
            }
        } else {
            $this->data['error'] = '1';
            $this->data['message'] = 'Wrong User';
            return;
        }

        $query = sprintf("SELECT  maxcredit_limit, credit_limit, balance  as existing_balance , account_id, credit_limit - balance as  balance from customer_balance where account_id = '%s' limit 1;", $account_id);
        $this->query('SWITCH', $query);
        $balance = $this->resultset();
        $initial_setup = 0;
        if (count($balance) > 0) {
            foreach ($balance[0] as $key => $value) {
                if ($reseller_id != '')
                    $this->accountinfo[$k][$key] = $value;
                else
                    $this->accountinfo[$key] = $value;
            }

            if ($reseller_id != '') {
                if ($this->accountinfo[$k]['balance'] < 0) {
                    $this->data['error'] = '1';
                    $this->data['message'] = 'Low Balance in Reseller ' . $k;
                    return;
                }
            } else {
                if ($this->accountinfo['balance'] < 0) {
                    $this->data['error'] = '1';
                    $this->data['message'] = 'Low Balance';
                    return;
                }
            }
        } else {
            $query = sprintf("INSERT INTO  customer_balance (account_id, credit_limit, outstanding_balance) VALUES ('%s',0,0 )", $account_id);
            $this->query('SWITCH', $query);
            $balance = $this->resultset();
            $initial_setup = 1;
        }
    }

    function didsetupcharge($date, $didsetup = 0, $rental = 0, $extrachannels = 0) {
        try {
            $service_startdate = $date;
            $service_stopdate = date('Y-m-t', strtotime($date));
            $this->request['yearmonth'] = date('Ym', strtotime($date));
            if ($this->request['request_from'] == 'service') {
                $this->request['yearmonth'] = date('Ym', strtotime($date));
                $service_startdate = date('Y-m-01', strtotime($date . ' +1 day'));
                $service_stopdate = date('Y-m-t', strtotime($date . ' +1 day'));
            }
            /*
             * Finding DID carrier detail
             */
            $query = sprintf("SELECT did.did_number, did.did_status, did.carrier_id , did.channels from did where did_number = '%s';", $this->request['service_number']);
            $this->query('SWITCH', $query);
            $diddetail = $this->resultset();
            if (count($diddetail) > 0) {
                foreach ($diddetail[0] as $key => $value) {
                    $this->request[$key] = $value;
                }
            }
            /*
             * Fibnding the DID rates
             */
            $query = sprintf("SELECT customer_rates.prefix, customer_rates.destination, customer_rates.setup_charge, customer_rates.rental, customer_rates.inclusive_channel, customer_rates.exclusive_per_channel_rental from customer_rates INNER JOIN tariff_ratecard_map on tariff_ratecard_map.ratecard_id = customer_rates.ratecard_id INNER JOIN ratecard on ratecard.ratecard_id = customer_rates.ratecard_id and ratecard.ratecard_for= 'INCOMING' and ratecard.ratecard_type ='CUSTOMER' where tariff_ratecard_map.tariff_id = '%s' and '%s' like CONCAT(prefix,'%%')  ORDER BY prefix desc limit 1;", $this->accountinfo['tariff_id'], $this->request['service_number']);

            $this->query('SWITCH', $query);
            $didrates = $this->resultset();

            if (count($didrates) > 0) {
                foreach ($didrates[0] as $key => $value) {
                    $this->accountinfo[$key] = $value;
                }

                /*
                 * Carrier detail
                 */
                $query = sprintf("select carrier_currency_id, tariff_id, 'prepaid',dp, tax1,tax2, tax3, tax_type from carrier WHERE carrier_id = '%s' limit 1;", $this->request['carrier_id']);
                $this->query('SWITCH', $query);
                $tariff = $this->resultset();
                foreach ($tariff[0] as $key => $value) {
                    $carrierdidrates[$key] = $value;
                }

                /*
                 * DID rate for carrier
                 */
                $query = sprintf("SELECT carrier_rates.prefix, carrier_rates.destination, carrier_rates.setup_charge, carrier_rates.rental, carrier_rates.inclusive_channel, carrier_rates.exclusive_per_channel_rental from carrier_rates INNER JOIN tariff_ratecard_map on tariff_ratecard_map.ratecard_id = carrier_rates.ratecard_id INNER JOIN ratecard on ratecard.ratecard_id = carrier_rates.ratecard_id and ratecard.ratecard_for= 'INCOMING' and ratecard.ratecard_type ='CARRIER' where tariff_ratecard_map.tariff_id = '%s' and '%s' like CONCAT(prefix,'%%')  ORDER BY prefix desc limit 1;", $carrierdidrates['tariff_id'], $this->request['service_number']);


                $this->query('SWITCH', $query);
                $saller = $this->resultset();

                if (count($saller) > 0) {
                    foreach ($saller[0] as $key => $value) {
                        $carrierdidrates[$key] = $value;
                    }
                }

                if (strlen($this->accountinfo['parent_account_id']) > 0) {
                    /*
                     * Account is under reseller so seller is reseller and that Data
                     */
                    $query = sprintf("SELECT  currency_id carrier_currency_id, tariff_id, billing_type, dp, tariff_id, tax3, tax2, tax1, tax_type, parent_account_id, account_level  from account INNER JOIN customers on customers.account_id = account.account_id WHERE account.account_id = '%s' limit 1;", $this->accountinfo['parent_account_id']);
                    $this->query('SWITCH', $query);
                    $tariff = $this->resultset();
                    foreach ($tariff[0] as $key => $value) {
                        $sallerdidrates[$key] = $value;
                    }
                    $this->data['message'] = $query;
                    /*
                     * Account is under reseller so seller is reseller and that rates
                     */
                    $query = sprintf("SELECT customer_rates.prefix, customer_rates.destination, customer_rates.setup_charge, customer_rates.rental, customer_rates.inclusive_channel, customer_rates.exclusive_per_channel_rental from customer_rates INNER JOIN tariff_ratecard_map on tariff_ratecard_map.ratecard_id = customer_rates.ratecard_id INNER JOIN ratecard on ratecard.ratecard_id = customer_rates.ratecard_id and ratecard.ratecard_for= 'INCOMING' and ratecard.ratecard_type ='CUSTOMER' where tariff_ratecard_map.tariff_id = '%s' and '%s' like CONCAT(prefix,'%%')  ORDER BY prefix desc limit 1;", $sallerdidrates['tariff_id'], $this->request['service_number']);

                    $this->query('SWITCH', $query);
                    $saller = $this->resultset();

                    if (count($saller) > 0) {
                        foreach ($saller[0] as $key => $value) {
                            $sallerdidrates[$key] = $value;
                        }
                    }
                } else {
                    /*
                     * if Seller is not Reseller and account under the admin then carrier rates will be as seller rates
                     */
                    $sallerdidrates = $carrierdidrates;
                }
            } else {
                /*
                 * Customer DID rates not available in system
                 */
                $this->data['error'] = '1';
                $this->data['message'] = 'DID Rates not available';
                return;
            }

            /*
             * If DID cancelation request proceesed
             */
            if ($this->request['request'] == 'DIDCANCEL') {
                $this->request['service_charges'] = $this->accountinfo['setup_charge'];
                $this->request['rule_type'] = 'DIDCANCEL';
                $charges_data = 0;
                $saller_charges_data = 0;
                if ($this->request['request_from'] == 'service') {
                    
                } else if ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0) {
                    $this->data['error'] = '1';
                    $this->data['message'] = 'Low Balance';
                    return;
                }
                $query = sprintf(" INSERT INTO customer_sdr (account_id, rule_type, yearmonth, service_number, service_charges, tax1, tax1_cost, tax2, tax2_cost, tax3, tax3_cost, total_tax, cost, total_cost, service_startdate, service_stopdate, seller_tax1, seller_tax2, seller_tax3, seller_tax1_cost, seller_tax2_cost, seller_tax3_cost, seller_cost, total_seller_cost, action_date,actiondate,carrier_cost, total_carrier_cost) VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now(), '%s', '%s', '%s');", $this->request['account_id'], $this->request['rule_type'], $this->request['yearmonth'], $this->request['service_number'], $this->request['service_charges'], $this->accountinfo['tax1'], $charges_data['tax1_cost'], $this->accountinfo['tax2'], $charges_data['tax2_cost'], $this->accountinfo['tax3'], $charges_data['tax3_cost'], $charges_data['total_tax'], $charges_data['cost'], $charges_data['total_cost'], $service_startdate, $service_startdate, $saller_charges_data['tax1'], $saller_charges_data['tax2'], $saller_charges_data['tax3'], $saller_charges_data['tax1_cost'], $saller_charges_data['tax2_cost'], $saller_charges_data['tax3_cost'], $saller_charges_data['cost'], $saller_charges_data['total_cost'], $date, $saller_charges_data['cost'], $saller_charges_data['total_cost']);
                $this->query('SWITCH', $query);
                $this->data['message'] = $query;
                $this->execute();


                /*
                 * Remove the DID configuration from the system and release the DID. Move DID status as free
                 */

                /*
                  $query = sprintf("update did set account_id = '' where did_number = '%s' and account_id = '%s';", $this->request['service_number'], $this->request['account_id']);

                  $this->query('SWITCH', $query);
                  $this->execute();
                 */

                return;
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

                if ($this->request['request_from'] == 'service') {
                    
                } else if ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0) {
                    $this->data['error'] = '1';
                    $this->data['message'] = 'Low Balance';
                    return;
                }

                $query = sprintf(" INSERT INTO customer_sdr (account_id, rule_type, yearmonth, service_number, service_charges, tax1, tax1_cost, tax2, tax2_cost, tax3, tax3_cost, total_tax, cost, total_cost, service_startdate, service_stopdate, seller_tax1, seller_tax2, seller_tax3, seller_tax1_cost, seller_tax2_cost, seller_tax3_cost, seller_cost, total_seller_cost, action_date,actiondate, carrier_cost, total_carrier_cost) VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now(), '%s', '%s', '%s');", $this->request['account_id'], $this->request['rule_type'], $this->request['yearmonth'], $this->request['service_number'], $this->request['service_charges'], $this->accountinfo['tax1'], $charges_data['tax1_cost'], $this->accountinfo['tax2'], $charges_data['tax2_cost'], $this->accountinfo['tax3'], $charges_data['tax3_cost'], $charges_data['total_tax'], $charges_data['cost'], $charges_data['total_cost'], $service_startdate, $service_startdate, $saller_charges_data['tax1'], $saller_charges_data['tax2'], $saller_charges_data['tax3'], $saller_charges_data['tax1_cost'], $saller_charges_data['tax2_cost'], $saller_charges_data['tax3_cost'], $saller_charges_data['cost'], $saller_charges_data['total_cost'], $date, $carrier_charges_data['cost'], $carrier_charges_data['total_cost']);

                $this->query('SWITCH', $query);
                $this->execute();
                $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                $this->query('SWITCH', $query);
                $this->execute();
                $didnumber_foremail = $this->request['service_number'];
            }
            if ($rental == 1) {
                $this->request['rule_type'] = 'DIDRENTAL';
                $this->request['service_charges'] = $this->accountinfo['rental'];
                if ($this->request['request_from'] == 'service') {
                    $current_month_charges = $this->accountinfo['rental'];
                } else {
                    $current_month_charges = $this->charges($this->accountinfo['rental'], $date);
                }
                $charges_data = $this->tax_calculation($this->accountinfo, $current_month_charges);

                /*
                 * Seller Costing
                 */

                $currency_id = $this->accountinfo['currency_id'];
                $saller_currency_id = $sallerdidrates['carrier_currency_id'];
                $currencyratio = $this->currencies_ratio($currency_id, $saller_currency_id);
                $saller_rental_charge = $currencyratio * $sallerdidrates['rental'];
                if ($this->request['request_from'] == 'service') {
                    $sallecurrent_month_charges = $saller_rental_charge;
                } else {
                    $sallecurrent_month_charges = $this->charges($saller_rental_charge, $date);
                }
                $saller_charges_data = $this->tax_calculation($sallerdidrates, $sallecurrent_month_charges);
                /*
                 * carrier Costing
                 */
                $carrier_currency_id = $carrierdidrates['carrier_currency_id'];
                $currencyratio = $this->currencies_ratio($currency_id, $carrier_currency_id);
                $carrier_rental_charge = $currencyratio * $carrierdidrates['rental'];
                if ($this->request['request_from'] == 'service') {
                    $carrier_current_month_charges = $carrier_rental_charge;
                } else {
                    $carrier_current_month_charges = $this->charges($carrier_rental_charge, $date);
                }
                $carrier_charges_data = $this->tax_calculation($carrierdidrates, $carrier_current_month_charges);

                if ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0 and $this->request['request_from'] != 'service') {
                    $this->data['error'] = '1';
                    $this->data['message'] = 'Low Balance';
                    return;
                }
                /*
                 * Writing SDR
                 */
                $query = sprintf(" INSERT INTO customer_sdr (account_id, rule_type, yearmonth, service_number, service_charges, tax1, tax1_cost, tax2, tax2_cost, tax3, tax3_cost, total_tax, cost, total_cost, service_startdate, service_stopdate, seller_tax1, seller_tax2, seller_tax3, seller_tax1_cost, seller_tax2_cost, seller_tax3_cost, seller_cost, total_seller_cost, action_date,actiondate ,carrier_cost, total_carrier_cost) VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now(), '%s', '%s', '%s');", $this->request['account_id'], $this->request['rule_type'], $this->request['yearmonth'], $this->request['service_number'], $this->request['service_charges'], $this->accountinfo['tax1'], $charges_data['tax1_cost'], $this->accountinfo['tax2'], $charges_data['tax2_cost'], $this->accountinfo['tax3'], $charges_data['tax3_cost'], $charges_data['total_tax'], $charges_data['cost'], $charges_data['total_cost'], $service_startdate, $service_stopdate, $saller_charges_data['tax1'], $saller_charges_data['tax2'], $saller_charges_data['tax3'], $saller_charges_data['tax1_cost'], $saller_charges_data['tax2_cost'], $saller_charges_data['tax3_cost'], $saller_charges_data['cost'], $saller_charges_data['total_cost'], $date, $carrier_charges_data['cost'], $carrier_charges_data['total_cost']);
                $this->query('SWITCH', $query);
                $this->execute();

                /*
                 * Deducting Balance
                 */
                $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                $this->query('SWITCH', $query);
                $this->execute();


                /*
                 * Additional channels
                 */
                $remainingchannels = $this->request['channels'] - $this->accountinfo['inclusive_channel'];
                if ($remainingchannels > 0) {
                    $this->request['rule_type'] = 'DIDEXTRACHRENTAL';

                    $this->request['service_charges'] = $this->accountinfo['exclusive_per_channel_rental'];
                    if ($this->request['request_from'] == 'service') {
                        $current_month_charges = $this->accountinfo['exclusive_per_channel_rental'];
                    } else {
                        $current_month_charges = $this->charges($this->accountinfo['exclusive_per_channel_rental'], $date);
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
                    if ($this->request['request_from'] == 'service') {
                        $sallecurrent_month_charges = $saller_extarental_charge;
                    } else {
                        $sallecurrent_month_charges = $this->charges($saller_extarental_charge, $date);
                    }
                    $saller_charges_data = $this->tax_calculation($sallerdidrates, $sallecurrent_month_charges);
                    /*
                     * carrier Costing
                     */
                    $carrier_currency_id = $carrierdidrates['carrier_currency_id'];
                    $currencyratio = $this->currencies_ratio($currency_id, $carrier_currency_id);
                    $carrier_extarental_charge = $currencyratio * $carrierdidrates['exclusive_per_channel_rental'];
                    if ($this->request['request_from'] == 'service') {
                        $carrier_current_month_charges = $carrier_extarental_charge;
                    } else {
                        $carrier_current_month_charges = $this->charges($carrier_extarental_charge, $date);
                    }
                    $carrier_charges_data = $this->tax_calculation($carrierdidrates, $carrier_current_month_charges);

                    $this->request['detail'] = "Addition $remainingchannels incoming channels for " . $this->request['service_number'] . " number";
                    if ($this->request['request_from'] == 'service') {
                        
                    } elseif ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0) {
                        $this->data['error'] = '1';
                        $this->data['message'] = 'Low Balance';
                        return;
                    }
                    $query = sprintf(" INSERT INTO customer_sdr (account_id, rule_type, yearmonth, service_number, service_charges, tax1, tax1_cost, tax2, tax2_cost, tax3, tax3_cost, total_tax, cost, total_cost, service_startdate, service_stopdate, seller_tax1, seller_tax2, seller_tax3, seller_tax1_cost, seller_tax2_cost, seller_tax3_cost, seller_cost, total_seller_cost, action_date, actiondate , carrier_cost, total_carrier_cost ) VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now(),'%s' , '%s', '%s' );", $this->request['account_id'], $this->request['rule_type'], $this->request['yearmonth'], $this->request['service_number'], $this->request['service_charges'], $this->accountinfo['tax1'], $charges_data['tax1_cost'], $this->accountinfo['tax2'], $charges_data['tax2_cost'], $this->accountinfo['tax3'], $charges_data['tax3_cost'], $charges_data['total_tax'], $charges_data['cost'], $charges_data['total_cost'], $service_startdate, $service_stopdate, $saller_charges_data['tax1'], $saller_charges_data['tax2'], $saller_charges_data['tax3'], $saller_charges_data['tax1_cost'], $saller_charges_data['tax2_cost'], $saller_charges_data['tax3_cost'], $saller_charges_data['cost'], $saller_charges_data['total_cost'], $date, $carrier_charges_data['cost'], $carrier_charges_data['total_cost']);
                    $this->query('SWITCH', $query);
                    $this->execute();

                    $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                    $this->query('SWITCH', $query);
                    $this->execute();
                }
            }
            /*
             * Additioal channels only
             */
            if ($extrachannels == 1) {
                $remainingchannels = $this->request['channels'];
                if ($remainingchannels > 0) {
                    $this->request['rule_type'] = 'DIDEXTRACHRENTAL';
                    $this->request['service_charges'] = $this->accountinfo['exclusive_per_channel_rental'];
                    if ($this->request['request_from'] == 'service') {
                        $current_month_charges = $this->accountinfo['exclusive_per_channel_rental'];
                    } else {
                        $current_month_charges = $this->charges($this->accountinfo['exclusive_per_channel_rental'], $date);
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
                    if ($this->request['request_from'] == 'service') {
                        $sallecurrent_month_charges = $saller_extarental_charge;
                    } else {
                        $sallecurrent_month_charges = $this->charges($saller_extarental_charge, $date);
                    }
                    $saller_charges_data = $this->tax_calculation($sallerdidrates, $sallecurrent_month_charges);
                    /*
                     * carrier Costing
                     */
                    $carrier_currency_id = $carrierdidrates['carrier_currency_id'];
                    $currencyratio = $this->currencies_ratio($currency_id, $carrier_currency_id);
                    $carrier_extarental_charge = $currencyratio * $carrierdidrates['exclusive_per_channel_rental'];
                    if ($this->request['request_from'] == 'service') {
                        $carrier_current_month_charges = $carrier_extarental_charge;
                    } else {
                        $carrier_current_month_charges = $this->charges($carrier_extarental_charge, $date);
                    }
                    $carrier_charges_data = $this->tax_calculation($carrierdidrates, $carrier_current_month_charges);

                    $this->request['detail'] = "Addition $remainingchannels incoming channels for " . $this->request['service_number'] . " number";
                    if ($this->request['request_from'] == 'service') {
                        
                    } elseif ($this->accountinfo['balance'] < $charges_data['total_cost'] and $initial_setup == 0) {
                        $this->data['error'] = '1';
                        $this->data['message'] = 'Low Balance';
                        return;
                    }
                    $query = sprintf(" INSERT INTO customer_sdr (account_id, rule_type, yearmonth, service_number, service_charges, tax1, tax1_cost, tax2, tax2_cost, tax3, tax3_cost, total_tax, cost, total_cost, service_startdate, service_stopdate, seller_tax1, seller_tax2, seller_tax3, seller_tax1_cost, seller_tax2_cost, seller_tax3_cost, seller_cost, total_seller_cost, action_date, actiondate , carrier_cost, total_carrier_cost ) VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now(),'%s' , '%s', '%s' );", $this->request['account_id'], $this->request['rule_type'], $this->request['yearmonth'], $this->request['service_number'], $this->request['service_charges'], $this->accountinfo['tax1'], $charges_data['tax1_cost'], $this->accountinfo['tax2'], $charges_data['tax2_cost'], $this->accountinfo['tax3'], $charges_data['tax3_cost'], $charges_data['total_tax'], $charges_data['cost'], $charges_data['total_cost'], $service_startdate, $service_stopdate, $saller_charges_data['tax1'], $saller_charges_data['tax2'], $saller_charges_data['tax3'], $saller_charges_data['tax1_cost'], $saller_charges_data['tax2_cost'], $saller_charges_data['tax3_cost'], $saller_charges_data['cost'], $saller_charges_data['total_cost'], $date, $carrier_charges_data['cost'], $carrier_charges_data['total_cost']);
                    $this->query('SWITCH', $query);
                    $this->execute();

                    $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
                    $this->query('SWITCH', $query);
                    $this->execute();
                }
            }
            $this->data['error'] = '0';
            $this->data['message'] = 'Request Processed';
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function save_payment($data) {
        try {
            $payment_data_array = array();
            $payment_data_array['account_id'] = $data['account_id'];
            $payment_data_array['payment_option_id'] = $data['request'];
            $payment_data_array['amount'] = $data['amount'];
            $payment_data_array['paid_on'] = $data['paid_on'];
            $payment_data_array['created_by'] = $data['created_by'];
            $payment_data_array['create_dt'] = date('Y-m-d H:s:i');
            $payment_data_array['notes'] = $data['service_number'];

            $payment_data = $payment_data . "account_id= '" . trim($data['account_id']) . "',";
            $payment_data = $payment_data . "payment_option_id= '" . trim($data['request']) . "',";
            $payment_data = $payment_data . "amount= '" . trim($data['amount']) . "',";
            $payment_data = $payment_data . "paid_on= '" . trim($data['paid_on']) . "',";
            $payment_data = $payment_data . "created_by= '" . trim($data['created_by']) . "',";
            $payment_data = $payment_data . "create_dt= '" . trim(date('Y-m-d H:s:i')) . "',";
            $payment_data = $payment_data . "notes= '" . trim($data['service_number']) . "',";


            $payment_data = rtrim($payment_data, ',');
            $query = "insert into payment_history  set " . $payment_data;
            $this->writelog($query);
            $this->query('SWITCH', $query);
            $this->execute();

            $account_id = $data['account_id'];
            $amount = $data['amount'];

            if ($data['request'] == 'REMOVEBALANCE') {
                $sql = "update customer_balance set balance = balance + $amount where account_id = '$account_id'";
            } if ($data['request'] == 'ADDBALANCE') {
                $sql = "update customer_balance set balance = balance - $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'ADDCREDIT') {
                $sql = "update customer_balance set credit_limit = credit_limit + $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'REMOVECREDIT') {
                $sql = "update customer_balance set credit_limit = credit_limit - $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'ADDTESTBALANCE') {
                $sql = "update customer_balance set balance = balance - $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'REMOVETESTBALANCE') {
                $sql = "update customer_balance set balance = balance + $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'BALANCETRANSFERADD') {
                $sql = "update customer_balance set balance = balance - $amount where account_id = '$account_id'";
            } elseif ($data['request'] == 'BALANCETRANSFERREMOVE') {
                $sql = "update customer_balance set balance = balance + $amount where account_id = '$account_id'";
            }
            $this->query('SWITCH', $sql);
            $this->execute();

            $yearmonth = date('Ym');
            $total_cost = $data['amount'];
            $service_startdate = date('Y-m-d h:s:i');
            $service_stopdate = date('Y-m-d h:s:i');
            $date = date('Y-m-d h:s:i');
            $query = sprintf("INSERT INTO customer_sdr( account_id, rule_type, yearmonth, service_number, service_charges, detail, otherdata, action_date, tax1_cost, tax2_cost, tax3_cost, cost, total_cost, total_tax, service_startdate, service_stopdate,tax1,tax2,tax3,actiondate) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', Now(), '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s');", $account_id, $data['request'], $yearmonth, $payment_data_array['notes'], 0, 0, 0, 0, 0, 0, 0, $total_cost, 0, $service_startdate, $service_stopdate, 0, 0, 0, $date);

            $this->query('SWITCH', $query);
            $this->execute();
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function ServiceDailyCallChargesSDR($date, $account_id = '') {
        $tablename = date('Ym', strtotime($date)) . "_ratedcdr";
        $system = 'VOIP';
        $cdrtype = 'OUT';
        $subquery = "";
        if (strlen(trim($account_id)) > 0) {
            $subquery = " and customer_account_id = '" . $account_id . "'";
        } else {
            $subquery = "";
        }
        $query = sprintf("SELECT HIGH_PRIORITY customer_account_id account_id, 'user' account_type FROM %s where  '%s' = date(end_time) %s GROUP BY customer_account_id;", $tablename, $date, $subquery);
        $this->query('CDR', $query);
        $diddetail = $this->resultset();
        if (count($diddetail) > 0) {
            foreach ($diddetail as $data) {
                if (strlen($data['account_id']) > 0) {
                    $cdrtype = 'OUT';
                    $this->dailyCallChargesSDR_Entry($data['account_type'], $data['account_id'], $date, $system, $cdrtype, $tablename);
                    $cdrtype = 'IN';
                    $this->dailyCallChargesSDR_Entry($data['account_type'], $data['account_id'], $date, $system, $cdrtype, $tablename);
                }
            }
        }
        $subquery = "";
        if (strlen(trim($account_id)) > 0) {
            $subquery = " and reseller1_account_id = '" . $account_id . "'";
        } else {
            $subquery = "";
        }
        $query = sprintf("SELECT  HIGH_PRIORITY reseller1_account_id account_id, 'reseller1' as account_type FROM %s where  '%s' = date(end_time)  %s  GROUP BY reseller1_account_id;", $tablename, $date, $subquery);



        $this->query('CDR', $query);
        $diddetail = $this->resultset();
        if (count($diddetail) > 0) {
            foreach ($diddetail as $data) {
                if (strlen($data['account_id']) > 0) {
                    $cdrtype = 'OUT';
                    $this->dailyCallChargesSDR_Entry($data['account_type'], $data['account_id'], $date, $system, $cdrtype, $tablename);

                    $cdrtype = 'IN';
                    $this->dailyCallChargesSDR_Entry($data['account_type'], $data['account_id'], $date, $system, $cdrtype, $tablename);
                }
            }
        }
        $subquery = "";
        if (strlen(trim($account_id)) > 0) {
            $subquery = " and reseller2_account_id = '" . $account_id . "'";
        } else {
            $subquery = "";
        }
        $query = sprintf("SELECT  HIGH_PRIORITY reseller2_account_id account_id, 'reseller2' account_type FROM %s where  '%s' = date(end_time) %s GROUP BY reseller2_account_id;", $tablename, $date, $subquery);

        $this->query('CDR', $query);
        $diddetail = $this->resultset();
        if (count($diddetail) > 0) {
            foreach ($diddetail as $data) {
                if (strlen($data['account_id']) > 0) {
                    $cdrtype = 'OUT';
                    $this->dailyCallChargesSDR_Entry($data['user_type'], $data['account_id'], $date, $system, $cdrtype, $tablename);
                    $cdrtype = 'IN';
                    $this->dailyCallChargesSDR_Entry($data['account_type'], $data['account_id'], $date, $system, $cdrtype, $tablename);
                }
            }
        }
        $subquery = "";
        if (strlen(trim($account_id)) > 0) {
            $subquery = " and reseller2_account_id = '" . $account_id . "'";
        } else {
            $subquery = "";
        }

        $query = sprintf("SELECT  HIGH_PRIORITY reseller3_account_id account_id, 'reseller3' account_type FROM %s where  '%s' = date(end_time) %s GROUP BY reseller3_account_id;", $tablename, $date, $subquery);

        $this->query('CDR', $query);
        $diddetail = $this->resultset();
        if (count($diddetail) > 0) {
            foreach ($diddetail as $data) {
                if (strlen($data['account_id']) > 0)
                    $cdrtype = 'OUT';
                $this->dailyCallChargesSDR_Entry($data['user_type'], $data['account_id'], $date, $system, $cdrtype, $tablename);
                $cdrtype = 'IN';
                $this->dailyCallChargesSDR_Entry($data['account_type'], $data['account_id'], $date, $system, $cdrtype, $tablename);
            }
        }
    }

    function dailyCallChargesSDR_Entry($account_type, $account_id, $date, $system, $cdrtype, $tablename) {
        if (strlen($account_id) == 0)
            return;
        if ($cdrtype == 'OUT') {
            $subquery = " and cdr_type = 'OUT' ";
        } elseif ($cdrtype == 'IN') {
            $subquery = " and cdr_type = 'IN' ";
        }

        if ($account_type == 'user')
            $query = sprintf("SELECT  HIGH_PRIORITY reseller1_account_id, reseller2_account_id ,reseller3_account_id, customer_account_id account_id, sum(customer_callcost_total) callcost, sum(reseller1_callcost_total) sellercallcost1, sum(reseller2_callcost_total) sellercallcost2, sum(reseller3_callcost_total) sellercallcost3, sum(carrier_callcost_total_usercurrency) carrier_cost, sum(carrier_duration) carrier_duration, sum(customer_duration) user_duration, sum(reseller1_duration) seller_duration1 , sum(reseller2_duration) seller_duration2 , sum(reseller3_duration) seller_duration3 from  %s where '%s' = date(end_time) and customer_account_id ='%s' %s;", $tablename, $date, $account_id, $subquery);
        if ($account_type == 'reseller3')
            $query = sprintf("SELECT  HIGH_PRIORITY reseller3_account_id account_id, sum(reseller3_callcost_total) callcost, sum(reseller2_callcost_total) sellercallcost, sum(carrier_callcost_total_usercurrency) carrier_cost, sum(carrier_duration) carrier_duration, sum(reseller3_duration) user_duration, sum(reseller2_duration) seller_duration  from %s where '%s' = date(end_time)  and reseller3_account_id ='%s' %s;", $tablename, $date, $account_id, $subquery);
        if ($account_type == 'reseller2')
            $query = sprintf("SELECT  HIGH_PRIORITY reseller2_account_id account_id, sum(reseller2_callcost_total) callcost, sum(reseller1_callcost_total) sellercallcost, sum(carrier_callcost_total_usercurrency) carrier_cost, sum(carrier_duration) carrier_duration, sum(reseller2_duration) user_duration, sum(reseller1_duration) seller_duration from %s where '%s' = date(end_time) and  reseller2_account_id ='%s' %s;", $tablename, $date, $account_id, $subquery);
        if ($account_type == 'reseller1')
            $query = sprintf("SELECT  HIGH_PRIORITY reseller1_account_id account_id, sum(reseller1_callcost_total) callcost, sum(carrier_callcost_total_usercurrency) sellercallcost, sum(carrier_callcost_total_usercurrency) carrier_cost, sum(carrier_duration) carrier_duration, sum(reseller1_duration) user_duration, sum(carrier_callcost_total_usercurrency) seller_duration from %s where '%s' = date(end_time)   and reseller1_account_id ='%s' %s;", $tablename, $date, $account_id, $subquery);


        $this->query('CDR', $query);
        $cdrdetail = $this->resultset();

        if (count($cdrdetail) > 0) {
            foreach ($cdrdetail[0] as $key => $value) {
                $this->userdetail[$key] = $value;
            }

            $callcost = $this->userdetail['callcost'];
            if ($account_type == 'user' and strlen($this->userdetail['reseller3_account_id']) > 0) {
                $sellercallcost = $this->userdetail['sellercallcost3'];
            } else if ($account_type == 'user' and strlen($this->userdetail['reseller2_account_id']) > 0) {
                $sellercallcost = $this->userdetail['sellercallcost2'];
            } else if ($account_type == 'user' and strlen($this->userdetail['reseller1_account_id']) > 0) {
                $sellercallcost = $this->userdetail['sellercallcost1'];
            } else {
                $sellercallcost = $this->userdetail['sellercallcost'];
            }

            $carrier_cost = $this->userdetail['carrier_cost'];
            $query = sprintf("SELECT  HIGH_PRIORITY dp, tax3, tax2, tax1, tax_type,  parent_account_id from account WHERE account_id = '%s' limit 1;", $account_id);

            $this->query('SWITCH', $query);
            $userdetail = $this->resultset();

            if (count($userdetail) > 0) {
                foreach ($userdetail[0] as $key => $value) {
                    $this->userdetail[$key] = $value;
                }
            }
            if (strlen(trim($this->userdetail['parent_account_id'])) > 0) {
                if ($this->userdetail['sellercallcost3'] > 0) {
                    $sellercallcost = $this->userdetail['sellercallcost3'];
                } elseif ($this->userdetail['sellercallcost2'] > 0) {
                    $sellercallcost = $this->userdetail['sellercallcost2'];
                } elseif ($this->userdetail['sellercallcost1'] > 0) {
                    $sellercallcost = $this->userdetail['sellercallcost1'];
                }
            } else {
                $sellercallcost = $this->userdetail['carrier_cost'];
            }
            $this->userdetail['sellercallcost'] = $sellercallcost;

            $callcost_data = $this->tax_calculation($this->userdetail, $this->userdetail['callcost']);
            if (strlen($this->userdetail['parent_account_id']) > 0) {
                $query = sprintf("SELECT  HIGH_PRIORITY dp, tax3, tax2, tax1, tax_type from account WHERE account_id = '%s' limit 1;", $this->userdetail['parent_account_id']);
                $this->query('SWITCH', $query);
                $sellerdetail = $this->resultset();
                if (count($sellerdetail) > 0) {
                    foreach ($sellerdetail[0] as $key => $value) {
                        $this->sellerdetail[$key] = $value;
                    }
                }
                $sellercallcost_data = $this->tax_calculation($this->sellerdetail, $this->userdetail['sellercallcost']);
            } else {
                $this->carrierdetail['tax1'] = 0;
                $this->carrierdetail['tax2'] = 0;
                $this->carrierdetail['tax3'] = 0;
                $this->carrierdetail['dp'] = 6;
                $sellercallcost_data = $this->tax_calculation($this->carrierdetail, $this->userdetail['carrier_cost']);
            }
            $this->carrierdetail['tax1'] = 0;
            $this->carrierdetail['tax2'] = 0;
            $this->carrierdetail['tax3'] = 0;
            $this->carrierdetail['dp'] = 6;
            $carrier_data = $this->tax_calculation($this->carrierdetail, $this->userdetail['carrier_cost']);
        }

        $yearmonth = date('Ym', strtotime($date));
        $callcost_data['total_tax'] = $callcost_data['tax3_cost'] + $callcost_data['tax2_cost'] + $callcost_data['tax1_cost'];
        $service_startdate = $date;
        $service_stopdate = $date;
        $rule_type = '';

        if ($cdrtype == 'OUT') {
            $rule_type = 'DAILYUSAGE';
        } elseif ($cdrtype == 'IN') {
            $rule_type = 'DAILYUSAGEIN';
        }

        if (strlen($rule_type) > 0) {
            $query = sprintf("delete from customer_sdr where actiondate = '%s' and rule_type='%s' and yearmonth='%s' and account_id = '%s'", $date, $rule_type, $yearmonth, $account_id);
            $this->query('SWITCH', $query);
            $this->execute();

            $query = sprintf(" INSERT INTO customer_sdr (account_id, rule_type, yearmonth, service_number, service_charges, tax1, tax1_cost, tax2, tax2_cost, tax3, tax3_cost, total_tax, cost, total_cost, service_startdate, service_stopdate, seller_tax1, seller_tax2, seller_tax3, seller_tax1_cost, seller_tax2_cost, seller_tax3_cost, seller_cost, total_seller_cost, carrier_tax1, carrier_tax2, carrier_tax3, carrier_tax1_cost, carrier_tax2_cost, carrier_tax3_cost, carrier_cost, total_carrier_cost, user_usage, seller_usage, carrier_usage, action_date,actiondate) VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s');", $account_id, $rule_type, $yearmonth, '', '', $this->userdetail['tax1'], $callcost_data['tax1_cost'], $this->userdetail['tax2'], $callcost_data['tax2_cost'], $this->userdetail['tax3'], $callcost_data['tax3_cost'], $callcost_data['total_tax'], $callcost_data['cost'], $callcost_data['total_cost'], $service_startdate, $service_stopdate, $this->sellerdetail['tax1'], $this->sellerdetail['tax2'], $this->sellerdetail['tax3'], $sellercallcost_data['tax1_cost'], $sellercallcost_data['tax2_cost'], $sellercallcost_data['tax3_cost'], $sellercallcost_data['cost'], $sellercallcost_data['total_cost'], $this->carrierdetail['tax1'], $this->carrierdetail['tax2'], $this->carrierdetail['tax3'], $carrier_data['tax1_cost'], $carrier_data['tax2_cost'], $carrier_data['tax3_cost'], $carrier_data['cost'], $carrier_data['total_cost'], $this->userdetail['user_duration'], $this->userdetail['seller_duration'], $this->userdetail['carrier_duration'], $date, $date);

            $this->query('SWITCH', $query);
            $this->execute();
        }
    }

    function ServiceChargesMonthly($date) {
        if (date('Y-m-d', strtotime($date)) == '01' or date('Y-m-d', strtotime($date)) == '1') {
            $query = sprintf("select '0' buy_rate , '0' buy_rate_per_hit , tariff.tariff_id service_id, account.account_id, tariff.monthly_charges as charges, dp,tax1,tax2,tax3,tax_type,'TARIFFCHARGES' service_type, account.currency_id currency_id from tariff  INNER JOIN account on tariff.tariff_id = account.tariff_id where tariff.monthly_charges > 0 and account.account_status not in ('0');");
            $this->query('SWITCH', $query);
            $servicedetail = $this->resultset();
            if (count($servicedetail) > 0) {
                foreach ($servicedetail as $data) {
                    if (count($data) > 0) {
                        $service = 1;
                        $this->ChargesMonthly($data, $date);
                    }
                }
            }
        }
    }

    function ServiceDIDRental($date) {
        if (date('Y-m-d', strtotime($date)) == '01' or date('Y-m-d', strtotime($date)) == '1') {
            $query = sprintf("SELECT did.did_number, did.did_status, did.carrier_id, did.account_id, did.reseller1_account_id, did.reseller2_account_id,  did.reseller3_account_id,  did.channels from did where did_status = 'USED'");

            $this->query('SWITCH', $query);
            $diddetail = $this->resultset();
            if (DBLOGWRITE == '1')
                $this->writelog($query);
            if (count($diddetail) > 0) {
                foreach ($diddetail as $data) {
                    $didsetup = 0;
                    $rental = 1;
                    $extrachannels = 0;
                    $this->request['account_id'] = $data['account_id'];
                    $this->request['service_number'] = $data['did_number'];
                    $this->request['channels'] = $data['channels'];
                    $this->request['request_from'] = 'service';
                    $this->request['carrier_id'] = $data['carrier_id'];
                    $this->data['error'] = '0';
                    $this->CustomerInfo();
                    $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
                    $this->request = Array();
                    $this->accountinfo = Array();
                    if (strlen($data['reseller1_account_id']) > 0) {
                        $didsetup = 0;
                        $rental = 1;
                        $extrachannels = 0;
                        $this->request['account_id'] = $data['reseller1_account_id'];
                        $this->request['service_number'] = $data['did_number'];
                        $this->request['channels'] = $data['channels'];
                        $this->request['request_from'] = 'service';
                        $this->request['carrier_id'] = $data['carrier_id'];
                        $this->data['error'] = '0';
                        $this->CustomerInfo();
                        $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
                        $this->request = Array();
                        $this->accountinfo = Array();
                    }
                    if (strlen($data['reseller2_account_id']) > 0) {
                        $didsetup = 0;
                        $rental = 1;
                        $extrachannels = 0;
                        $this->request['account_id'] = $data['reseller2_account_id'];
                        $this->request['service_number'] = $data['did_number'];
                        $this->request['channels'] = $data['channels'];
                        $this->request['request_from'] = 'service';
                        $this->request['carrier_id'] = $data['carrier_id'];
                        $this->data['error'] = '0';
                        $this->CustomerInfo();
                        $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
                        $this->request = Array();
                        $this->accountinfo = Array();
                    }
                    if (strlen($data['reseller3_account_id']) > 0) {
                        $didsetup = 0;
                        $rental = 1;
                        $extrachannels = 0;
                        $this->request['account_id'] = $data['reseller3_account_id'];
                        $this->request['service_number'] = $data['did_number'];
                        $this->request['channels'] = $data['channels'];
                        $this->request['request_from'] = 'service';
                        $this->request['carrier_id'] = $data['carrier_id'];
                        $this->data['error'] = '0';
                        $this->CustomerInfo();
                        $this->didsetupcharge($date, $didsetup, $rental, $extrachannels);
                        $this->request = Array();
                        $this->accountinfo = Array();
                    }
                }
            }
        }
    }

    function ServiceOpeningBalance($date) {
        if (date('Y-m-d', strtotime($date)) == '01' or date('Y-m-d', strtotime($date)) == '1') {
            $query = sprintf("SELECT account_id from switch_user WHERE user_status not in ('0')");
            $this->query('SWITCH', $query);
            $accountdata = $this->resultset();
            if (count($accountdata) > 0) {
                foreach ($accountdata as $data) {
                    $account_id = $data['account_id'];
                    if (strlen($account_id) > 0) {
                        $usage = 0;
                        $balance = 0;
                        $openingbalance = 0;
                        $closingdate = date('Ym', strtotime($date));
                        $openingbalance = 0;
                        $usertype = '';
                        $yearmonth = $closingdate;
                        $new_openingbalance = $this->closing_balance($account_id, $yearmonth);
                        $yearmonth = date('Ym', strtotime($date . ' +1 day'));
                        $datenew = date('Y-m-d', strtotime($date . ' +1 day'));
                        $this->request['service_startdate'] = $datenew;
                        $this->request['service_stopdate'] = $datenew;
                        $query = sprintf("delete from customer_sdr where actiondate = '%s' and rule_type='OPENINGBALANCE' and yearmonth='%s' and account_id='%s'", $datenew, $yearmonth, $account_id);
                        $this->query('SWITCH', $query);
                        $this->execute();
                        $query = sprintf("INSERT INTO customer_sdr ( account_id, rule_type, yearmonth, service_number, service_charges, action_date, tax1_cost, tax2_cost, tax3_cost, cost, total_cost, total_tax, service_startdate, service_stopdate,tax1,tax2,tax3,actiondate) VALUES ('%s', '%s', '%s', '%s', '%s', Now(), '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s','%s');", $account_id, 'OPENINGBALANCE', $yearmonth, '', '', '0', '0', '0', '0', $new_openingbalance, '0', $this->request['service_startdate'], $this->request['service_stopdate'], '0', '0', '0', $datenew);
                        $this->query('SWITCH', $query);
                        $this->execute();
                        $this->request = Array();
                        $new_openingbalance = 0;
                    }
                }
            }
        }
    }

    function closing_balance($account_id, $yearmonth) {
        if (strlen($account_id) == 0)
            return;
        try {
            $openingbalance = $addbalance = $removebalance = $usage = 0;
            $sdr_terms = array();
            $query = "SELECT term_group, term, display_text, cost_calculation_formula FROM sys_sdr_terms ORDER BY term_group, term";
            $this->query('SWITCH', $query);
            $result_data = $this->resultset();
            foreach ($result_data as $row) {
                $term = $row['term'];
                $sdr_terms[$term] = $row;
            }
            $sql = sprintf("SELECT rule_type, action_date, service_number, total_cost, service_startdate, service_stopdate FROM customer_sdr WHERE account_id='%s' AND yearmonth='%s' ORDER BY action_date ASC ", $account_id, $yearmonth);
            $this->query('SWITCH', $query);
            $result_data = $this->resultset();
            if (count($result_data) > 0) {
                foreach ($result_data as $sdr_data) {
                    $rule_type = $sdr_data['rule_type'];
                    if (isset($sdr_terms[$rule_type])) {
                        $term_array = $sdr_terms[$rule_type];
                        $term_group = $term_array['term_group'];
                        $cost_calculation_formula = trim($term_array['cost_calculation_formula']);
                        $total_cost = $sdr_data['total_cost'];
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
            return $current_balance;
        } catch (Exception $e) {
            return 0;
        }
    }

    function balance_reconcile($account_id, $usertype) {
        if (strlen($account_id) == 0)
            return;
        try {
            $yearmonth = $date = date('Ym');
            $openingbalance = $addbalance = $removebalance = $usage = 0;
            $sdr_terms = array();
            $query = "SELECT term_group, term, display_text, cost_calculation_formula FROM sys_sdr_terms ORDER BY term_group, term";
            $this->query('SWITCH', $query);
            $result_data = $this->resultset();
            foreach ($result_data as $row) {
                $term = $row['term'];
                $sdr_terms[$term] = $row;
            }
            $query = sprintf("SELECT rule_type, action_date, service_number, total_cost, service_startdate, service_stopdate FROM customer_sdr WHERE account_id='%s' AND yearmonth='%s' ORDER BY action_date ASC ", $account_id, $yearmonth);
            $this->query('SWITCH', $query);
            $result_data = $this->resultset();
            if (count($result_data) > 0) {
                foreach ($result_data as $sdr_data) {
                    $rule_type = $sdr_data['rule_type'];
                    if (isset($sdr_terms[$rule_type])) {
                        $term_array = $sdr_terms[$rule_type];
                        $term_group = $term_array['term_group'];
                        $cost_calculation_formula = trim($term_array['cost_calculation_formula']);
                        $total_cost = $sdr_data['total_cost'];
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
            $customer_statistics = date('Ym', strtotime($date)) . "_customerstate";
            if ($usertype == 'CUSTOMER') {
                $query = sprintf("SELECT sum(customer_cost) cost FROM 201909_customerstate where account_id = '%s' and CURDATE() = call_date;", $customer_statistics, $account_id);
            } elseif ($usertype == 'RESELLER1') {
                $query = sprintf("SELECT sum(r1_cost) cost FROM 201909_customerstate where r1_account_id = '%s' and CURDATE() = call_date;", $customer_statistics, $account_id);
            } elseif ($usertype == 'RESELLER2') {
                $query = sprintf("SELECT sum(r2_cost) cost FROM 201909_customerstate where r2_account_id = '%s' and CURDATE() = call_date;", $customer_statistics, $account_id);
            } elseif ($usertype == 'RESELLER3') {
                $query = sprintf("SELECT sum(r3_cost) cost FROM 201909_customerstate where r2_account_id = '%s' and CURDATE() = call_date;", $customer_statistics, $account_id);
            } else {
                $query = sprintf("SELECT sum(customer_cost) cost FROM 201909_customerstate where account_id = '%s' and CURDATE() = call_date;", $customer_statistics, $account_id);
            }

            $this->query('CDR', $query);
            $result_data2 = $this->resultset();
            foreach ($result_data2 as $row2) {
                $cost = $row2['cost'];
            }
            if ($cost == null or $cost == '')
                $cost = 0;

            $balance = $current_balance - $cost;
            $balance2 = 0 - $balance;
            $query = sprintf("update customer_balance set balance =  '%s' where account_id = '%s';", $balance2, $account_id);
            $this->query('SWITCH', $query);
            $this->execute();
            $balance2 = 0;
            $outstanding_balance = 0;
            $balance = 0;
            $current_balance = 0;
        } catch (Exception $e) {
            die("exception");
            return $e->getMessage();
        }
    }

    function ManageBalance() {
        $accountlist = Array();
        $yearmonth = date('Ym');
        $query = sprintf("select HIGH_PRIORITY customer_sdr.account_id  from customer_sdr INNER JOIN account on customer_sdr.account_id = account.account_id  where yearmonth = '%s' and account_type = 'CUSTOMER' GROUP BY customer_sdr.account_id;", $yearmonth);

        $this->query('SWITCH', $query);
        $sdraccounts = $this->resultset();
        foreach ($sdraccounts as $account) {
            $account_id = $account['account_id'];
            $this->balance_reconcile($account_id, $yearmonth, 'CUSTOMER');
            $account_id = '';
        }

        $query = sprintf("select HIGH_PRIORITY customer_sdr.account_id  from customer_sdr INNER JOIN account on customer_sdr.account_id = account.account_id  where yearmonth = '%s' and account_type = 'RESELLER' and account_level = '1'  GROUP BY customer_sdr.account_id;
", $yearmonth);
        $this->query('SWITCH', $query);
        $this->writelog($query);
        $sdraccounts = $this->resultset();
        foreach ($sdraccounts as $account) {
            $account_id = $account['account_id'];
            $this->balance_reconcile($account_id, $yearmonth, 'RESELLER1');
            $account_id = '';
        }

        $query = sprintf("select HIGH_PRIORITY customer_sdr.account_id  from customer_sdr INNER JOIN account on customer_sdr.account_id = account.account_id  where yearmonth = '%s' and account_type = 'RESELLER' and account_level = '2'  GROUP BY customer_sdr.account_id;
", $yearmonth);

        $this->query('SWITCH', $query);
        $this->writelog($query);
        $sdraccounts = $this->resultset();
        foreach ($sdraccounts as $account) {
            $account_id = $account['account_id'];

            $this->balance_reconcile($account_id, $yearmonth, 'RESELLER2');
            $account_id = '';
        }

        $query = sprintf("select HIGH_PRIORITY customer_sdr.account_id  from customer_sdr INNER JOIN account on customer_sdr.account_id = account.account_id  where yearmonth = '%s' and account_type = 'RESELLER' and account_level = '3'  GROUP BY customer_sdr.account_id;
", $yearmonth);

        $this->query('SWITCH', $query);
        $this->writelog($query);
        $sdraccounts = $this->resultset();
        foreach ($sdraccounts as $account) {
            $account_id = $account['account_id'];
            $this->balance_reconcile($account_id, $yearmonth, 'RESELLER3');
            $account_id = '';
        }
    }

    function service($date) {
        /*
         * Generate Daily Call Charges SDR 
         */
        $this->ServiceDailyCallChargesSDR($date, $account_id = '');
        /*
         * THis is rining every month day one and charging for full month tariff charges
         */
        $this->ServiceChargesMonthly($date);
        /*
         * Montholy DID rental Charges
         */
        $this->ServiceDIDRental($date);
        /*
         * Opening Balance of Month in SDR for Billing 
         */
        $this->ServiceOpeningBalance($date);
        /*
         * Reconsiled Balance for each accounts
         */
        $this->ManageBalance();
    }

    function ChargesMonthly($data, $date) {

        $service_startdate = date('Y-m-d', strtotime($date . ' +1 day'));
        $service_stopdate = date('Y-m-t', strtotime($date . ' +1 day'));
        $yearmonth = date('Ym', strtotime($date));
        $this->request['service_charges'] = 0;
        $this->request['service_charges'] = $data['charges'];
        $this->request['account_id'] = $data['account_id'];
        $this->request['service_number'] = $data['service_id'];
        $this->request['request_from'] = 'service';
        $this->request['rule_type'] = $data['service_type'];
        $current_month_charges = $this->charges($data['charges'], $service_startdate);
        $charges_data = $this->cost_tax_calculation($data, $current_month_charges);
        $saller_charges_data['tax1'] = 0;
        $saller_charges_data['tax2'] = 0;
        $saller_charges_data['tax3'] = 0;
        $saller_charges_data['tax1_cost'] = 0;
        $saller_charges_data['tax2_cost'] = 0;
        $saller_charges_data['tax3_cost'] = 0;
        $saller_charges_data['cost'] = 0;
        $saller_charges_data['total_cost'] = 0;
        $query = sprintf(" INSERT INTO customer_sdr (account_id, rule_type, yearmonth, service_number, service_charges, tax1, tax1_cost, tax2, tax2_cost, tax3, tax3_cost, total_tax, cost, total_cost, service_startdate, service_stopdate, seller_tax1, seller_tax2, seller_tax3, seller_tax1_cost, seller_tax2_cost, seller_tax3_cost, seller_cost, total_seller_cost, action_date,actiondate, carrier_cost, total_carrier_cost) VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now(), '%s', '%s', '%s');", $this->request['account_id'], $this->request['rule_type'], $yearmonth, $this->request['service_number'], $this->request['service_charges'], $data['tax1'], $charges_data['tax1_cost'], $data['tax2'], $charges_data['tax2_cost'], $data['tax3'], $charges_data['tax3_cost'], $charges_data['total_tax'], $charges_data['cost'], $charges_data['total_cost'], $service_startdate, $service_stopdate, $saller_charges_data['tax1'], $saller_charges_data['tax2'], $saller_charges_data['tax3'], $saller_charges_data['tax1_cost'], $saller_charges_data['tax2_cost'], $saller_charges_data['tax3_cost'], $saller_charges_data['cost'], $saller_charges_data['total_cost'], $date, $saller_charges_data['cost'], $saller_charges_data['total_cost']);

        $this->query('SWITCH', $query);
        $this->execute();
        $query = sprintf("update customer_balance set balance = balance + '%s' where account_id = '%s';", $charges_data['total_cost'], $this->request['account_id']);
        $this->query('SWITCH', $query);
        $this->execute();
    }

    function ServiceCreditDeduction() {

        $query = sprintf("SELECT id, account_id, credit_amount, execution_date, status_id FROM credit_scheduler where execution_date < NOW() and status_id = '0'");
        $this->query('SWITCH', $query);
        $detail = $this->resultset();
        if (count($detail) > 0) {
            foreach ($detail as $data) {
                if ($data['status_id'] == '0') {
                    $query = sprintf("SELECT id, maxcredit_limit, credit_limit, account_id, balance  from customer_balance where account_id = '%s' limit 1;", $data['account_id']);
                    $aftercredit_limit = 0;
                    $this->userdetail['credit_limit'] = 0;
                    if (DBLOGWRITE == '1')
                        $this->writelog($query);
                    $this->query('SWITCH', $query);
                    $balance = $this->resultset();
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

                    $this->query('SWITCH', $query);
                    $this->execute();
                    $data['service_startdate'] = date("Y-m-d H:i:s");
                    $data['service_stopdate'] = date("Y-m-d H:i:s");
                    $charges_data['tax1_cost'] = '0';
                    $charges_data['tax2_cost'] = '0';
                    $charges_data['tax3_cost'] = '0';
                    $charges_data['cost'] = abs($data['amount']);
                    $charges_data['total_cost'] = abs($data['amount']);
                    $charges_data['total_tax'] = '0';
                    $charges_data['tax1'] = 0;
                    $charges_data['tax2'] = 0;
                    $charges_data['tax3'] = 0;
                    $data['yearmonth'] = date("Ym");
                    $data['service_number'] = 'Reduce Credit';
                    $data['service_charges'] = $data['amount'];
                    $data['detail'] = '';
                    $data['otherdata'] = '';
                    $date = date("Y-m-d");
                    $query = sprintf("INSERT INTO customer_sdr ( account_id, rule_type, yearmonth, service_number, service_charges, detail, otherdata, action_date, tax1_cost, tax2_cost, tax3_cost, cost, total_cost, total_tax, service_startdate, service_stopdate,tax1,tax2,tax3,actiondate) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', Now(), '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s');", $data['account_id'], $data['rule_type'], $data['yearmonth'], $data['service_number'], $data['service_charges'], $data['detail'], $data['otherdata'], $charges_data['tax1_cost'], $charges_data['tax2_cost'], $charges_data['tax3_cost'], $charges_data['cost'], $charges_data['total_cost'], $charges_data['total_tax'], $data['service_startdate'], $data['service_stopdate'], $charges_data['tax1'], $charges_data['tax2'], $charges_data['tax3'], $date);

                    $this->query('SWITCH', $query);
                    $this->execute();

                    $query = sprintf("update customer_balance set credit_limit = credit_limit - '%s' where account_id = '%s';", $charges_data['total_cost'], $data['account_id']);
                    $this->query('SWITCH', $query);
                    $this->execute();


                    $query = sprintf("update credit_scheduler set status_id = '1' where account_id = '%s' and execution_date = '%s';", $data['account_id'], $data['execution_date']);
                    $this->query('SWITCH', $query);
                    $this->execute();


                    $query = sprintf("update customer_notification set email_status = '0'  where account_id = '%s' and  notify_name='low-balance';", $data['account_id']);
                    $this->query('SWITCH', $query);
                    $this->execute();
                }
            }
        }
    }

    function __destruct() {
        try {
            $this->dbswitch = null;
            $this->dbcdr = null;
        } catch (PDOException $e) {
            exit('App shoutdown');
        }
    }

}
