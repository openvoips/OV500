<?php

class Service extends PDO {

    var $db = null;

    function __construct($db1) {
        error_reporting(0);
        $this->db = $db1;
    }

    function main() {
        $this->eng1();
        $this->eng2();
    }

// engine 2
    function eng2() {

        $sql = "SELECT tid, date, time, id, `engine`, current_status, last_status, stop_date, stop_time, running_time from engine_log where `engine` = 'E2'  ORDER BY tid desc limit 1";

        echo "\n$sql";
        $this->db->query($sql);
        $laststatus_data = $this->db->resultset();
        print_r($laststatus_data);

        if (count($laststatus_data) > 0) {
            $engine_status = $laststatus_data['current_status'];
            $tid = $laststatus_data['tid'];
            $log_entry = 1;
        } else {
            $log_entry = 0;
        }

        $sql = "select DATE,TIME, FE2, ER2 , concat( DATE, TIME) datakey FROM engine_data2 where ER2C is null";
        echo "\n$sql";
        $this->db->query($sql);
        $bundal_data = $this->db->resultset();
        // print_r($bundal_data);
        $datainsert = 2;
        if (count($bundal_data) > 0) {
            foreach ($bundal_data as $data) {
                print_r($data);
                echo "\n log_entry $log_entry FE2 " . $data['FE2'] . "\n";
                if ($log_entry == 0 and $data['FE2'] == '1') {
                    $sql_insert = '';
                    $sql_insert = "insert into engine_log set ";
                    $sql_insert .= "date = '" . $data['DATE'] . "',";
                    $sql_insert .= "time = '" . $data['TIME'] . "',";
                    $sql_insert .= "engine = 'E2',";
                    $sql_insert .= "current_status = '1',";
                    $sql_insert .= "last_status = '1',";
                    $sql_insert = rtrim($sql_insert, ',');
                    echo "\n$sql_insert";
                    $this->db->query($sql_insert);
                    $this->db->execute();
                    echo "\n$sql_insert";
                    $tid = '';
                    $engine_status = '1';
                    $log_entry = '1';

                    $sql_upadte = "update engine_data2 set  ";
                    $sql_upadte .= "ER1C = '0',";
                    $sql_upadte = rtrim($sql_upadte, ',');
                    $sql_upadte .= " where concat(DATE,TIME) = '" . $data['datakey'] . "'";
                    $this->db->query($sql_upadte);
                    $this->db->execute();
                    continue;
                } elseif ($log_entry == 0 and $data['FE2'] == '0') {
                    $sql_insert = '';
                    $sql_insert = "insert into engine_log set ";
                    $sql_insert .= "stop_date = '" . $data['DATE'] . "',";
                    $sql_insert .= "stop_time = '" . $data['TIME'] . "',";
                    $sql_insert .= "engine = 'E2',";
                    $sql_insert .= "current_status = '0',";
                    $sql_insert .= "last_status = '0',";
                    $sql_insert = rtrim($sql_insert, ',');
                    $this->db->query($sql_insert);
                    echo "\n$sql_insert";
                    $this->db->execute();
                    $engine_status = '0';
                    $log_entry = '1';

                    $sql_upadte = "update engine_data2 set  ";
                    $sql_upadte .= "ER1C = '0',";
                    $sql_upadte = rtrim($sql_upadte, ',');
                    $sql_upadte .= " where concat(DATE,TIME) = '" . $data['datakey'] . "'";
                    $this->db->query($sql_upadte);
                    $this->db->execute();
                    continue;
                }

                $sql = "SELECT tid, current_status,  stop_date, stop_time  from engine_log where `engine` = 'E2'  ORDER BY tid desc limit 1";

                echo "\n$sql";
                $this->db->query($sql);
                $laststatus_data = $this->db->resultset();
                print_r($laststatus_data);

                if (count($laststatus_data) > 0) {
                    $laststatus_data = $laststatus_data[0];
                    $engine_status = $laststatus_data['current_status'];
                    $tid = $laststatus_data['tid'];
                    $log_entry = 1;
                }
                echo "\n log_entry $log_entry FE2 " . $data['FE2'] . " tid $tid \n";

                if ($data['FE2'] == '1' and $engine_status == '1' and strlen($laststatus_data['stop_date']) == 0 and strlen($laststatus_data['stop_time']) == 0) {
                    
                } elseif ($data['FE2'] == '0' and $engine_status == '1' and strlen($laststatus_data['stop_date']) == 0 and strlen($laststatus_data['stop_time']) == 0) {
                    $sql_upadte = "update engine_log set  ";
                    $sql_upadte .= "current_status = '0',";
                    $sql_upadte .= "stop_date = '" . $data['DATE'] . "',";
                    $sql_upadte .= "stop_time = '" . $data['TIME'] . "',";
                    $sql_upadte .= "last_status = '0',";
                    $sql_upadte = rtrim($sql_upadte, ',');
                    $sql_upadte .= " where tid = '$tid'";
                    $this->db->query($sql_upadte);
                    $this->db->execute();

                    echo "\n$sql_upadte";
                } else if ($data['FE2'] == '1' and $engine_status == '0') {
                    $sql_insert = '';
                    $sql_insert = "insert into engine_log set ";
                    $sql_insert .= "date = '" . $data['DATE'] . "',";
                    $sql_insert .= "time = '" . $data['TIME'] . "',";
                    $sql_insert .= "engine = 'E2',";
                    $sql_insert .= "current_status = '1',";
                    $sql_insert .= "last_status = '1',";
                    $sql_insert = rtrim($sql_insert, ',');
                    echo "\n$sql_insert";
                    $this->db->query($sql_insert);
                    $this->db->execute();
                    echo "\n$sql_insert";
                    $tid = '';
                    $engine_status = '1';
                    $log_entry = '1';

                    $sql_upadte = "update engine_data2 set  ";
                    $sql_upadte .= "ER2C = '0',";
                    $sql_upadte = rtrim($sql_upadte, ',');
                    $sql_upadte .= " where concat(DATE,TIME) = '" . $data['datakey'] . "'";
                    $this->db->query($sql_upadte);
                    $this->db->execute();
                    continue;
                }


                $sql_upadte = "update engine_data2 set  ";
                $sql_upadte .= "ER2C = '0',";
                $sql_upadte = rtrim($sql_upadte, ',');
                $sql_upadte .= " where concat(DATE,TIME) = '" . $data['datakey'] . "'";
                $this->db->query($sql_upadte);
                $this->db->execute();
            }
        }
    }

    function eng1() {

        $sql = "SELECT tid, date, time, id, `engine`, current_status, last_status, stop_date, stop_time, running_time from engine_log where `engine` = 'E1'  ORDER BY tid desc limit 1";

        echo "\n$sql";
        $this->db->query($sql);
        $laststatus_data = $this->db->resultset();
        print_r($laststatus_data);

        if (count($laststatus_data) > 0) {
            $engine_status = $laststatus_data['current_status'];
            $tid = $laststatus_data['tid'];
            $log_entry = 1;
        } else {
            $log_entry = 0;
        }

        $sql = "select DATE,TIME, FE1, ER1 , concat( DATE, TIME) datakey FROM engine_data2 where ER1C is null";
        echo "\n$sql";
        $this->db->query($sql);
        $bundal_data = $this->db->resultset();
        // print_r($bundal_data);
        $datainsert = 2;
        if (count($bundal_data) > 0) {
            foreach ($bundal_data as $data) {
                print_r($data);
                echo "\n log_entry $log_entry FE1 " . $data['FE1'] . "\n";
                if ($log_entry == 0 and $data['FE1'] == '1') {
                    $sql_insert = '';
                    $sql_insert = "insert into engine_log set ";
                    $sql_insert .= "date = '" . $data['DATE'] . "',";
                    $sql_insert .= "time = '" . $data['TIME'] . "',";
                    $sql_insert .= "engine = 'E1',";
                    $sql_insert .= "current_status = '1',";
                    $sql_insert .= "last_status = '1',";
                    $sql_insert = rtrim($sql_insert, ',');
                    echo "\n$sql_insert";
                    $this->db->query($sql_insert);
                    $this->db->execute();
                    echo "\n$sql_insert";
                    $tid = '';
                    $engine_status = '1';
                    $log_entry = '1';

                    $sql_upadte = "update engine_data2 set  ";
                    $sql_upadte .= "ER1C = '0',";
                    $sql_upadte = rtrim($sql_upadte, ',');
                    $sql_upadte .= " where concat(DATE,TIME) = '" . $data['datakey'] . "'";
                    $this->db->query($sql_upadte);
                    $this->db->execute();
                    continue;
                } elseif ($log_entry == 0 and $data['FE1'] == '0') {
                    $sql_insert = '';
                    $sql_insert = "insert into engine_log set ";
                    $sql_insert .= "stop_date = '" . $data['DATE'] . "',";
                    $sql_insert .= "stop_time = '" . $data['TIME'] . "',";
                    $sql_insert .= "engine = 'E1',";
                    $sql_insert .= "current_status = '0',";
                    $sql_insert .= "last_status = '0',";
                    $sql_insert = rtrim($sql_insert, ',');
                    $this->db->query($sql_insert);
                    echo "\n$sql_insert";
                    $this->db->execute();
                    $engine_status = '0';
                    $log_entry = '1';

                    $sql_upadte = "update engine_data2 set  ";
                    $sql_upadte .= "ER1C = '0',";
                    $sql_upadte = rtrim($sql_upadte, ',');
                    $sql_upadte .= " where concat(DATE,TIME) = '" . $data['datakey'] . "'";
                    $this->db->query($sql_upadte);
                    $this->db->execute();
                    continue;
                }

                $sql = "SELECT tid, current_status,  stop_date, stop_time  from engine_log where `engine` = 'E1'  ORDER BY tid desc limit 1";

                echo "\n$sql";
                $this->db->query($sql);
                $laststatus_data = $this->db->resultset();
                print_r($laststatus_data);

                if (count($laststatus_data) > 0) {
                    $laststatus_data = $laststatus_data[0];
                    $engine_status = $laststatus_data['current_status'];
                    $tid = $laststatus_data['tid'];
                    $log_entry = 1;
                }
                echo "\n log_entry $log_entry FE1 " . $data['FE1'] . " tid $tid \n";

                if ($data['FE1'] == '1' and $engine_status == '1' and strlen($laststatus_data['stop_date']) == 0 and strlen($laststatus_data['stop_time']) == 0) {
                    
                } elseif ($data['FE1'] == '0' and $engine_status == '1' and strlen($laststatus_data['stop_date']) == 0 and strlen($laststatus_data['stop_time']) == 0) {
                    $sql_upadte = "update engine_log set  ";
                    $sql_upadte .= "current_status = '0',";
                    $sql_upadte .= "stop_date = '" . $data['DATE'] . "',";
                    $sql_upadte .= "stop_time = '" . $data['TIME'] . "',";
                    $sql_upadte .= "last_status = '0',";
                    $sql_upadte = rtrim($sql_upadte, ',');
                    $sql_upadte .= " where tid = '$tid'";
                    $this->db->query($sql_upadte);
                    $this->db->execute();

                    echo "\n$sql_upadte";
                } else if ($data['FE1'] == '1' and $engine_status == '0') {
                    $sql_insert = '';
                    $sql_insert = "insert into engine_log set ";
                    $sql_insert .= "date = '" . $data['DATE'] . "',";
                    $sql_insert .= "time = '" . $data['TIME'] . "',";
                    $sql_insert .= "engine = 'E1',";
                    $sql_insert .= "current_status = '1',";
                    $sql_insert .= "last_status = '1',";
                    $sql_insert = rtrim($sql_insert, ',');
                    echo "\n$sql_insert";
                    $this->db->query($sql_insert);
                    $this->db->execute();
                    echo "\n$sql_insert";
                    $tid = '';
                    $engine_status = '1';
                    $log_entry = '1';
                }


                $sql_upadte = "update engine_data2 set  ";
                $sql_upadte .= "ER1C = '0',";
                $sql_upadte = rtrim($sql_upadte, ',');
                $sql_upadte .= " where concat(DATE,TIME) = '" . $data['datakey'] . "'";
                $this->db->query($sql_upadte);
                $this->db->execute();
            }
        }
    }

}

?>