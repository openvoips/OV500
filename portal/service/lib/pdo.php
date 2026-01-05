<?php

class MYPDO extends PDO {

    var $fetch_mode = PDO::FETCH_ASSOC;
    var $stmt;
    var $dbswitch = null;

    function __construct() {
        $this->dbconnect();
    }

    function connection() {

        try {
            $this->dbswitch = new PDO(SWITCH_DSN, SWITCH_DSN_LOGIN, SWITCH_DSN_PASSWORD);
        } catch (Exception $e) {
            $log = SWITCH_DSN . " " . SWITCH_DSN_LOGIN . " " . SWITCH_DSN_PASSWORD;
            $this->writelog('Switch DB connection issue ' . $log);
            exit('App shoutdown');
        }
    }

    function dbconnect() {
        $this->connection();
    }

    function query($query) {

        $this->stmt = $this->dbswitch->prepare($query);
        return $this;
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

}

?>
