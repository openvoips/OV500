<?php

error_reporting(0);
ini_set('memory_limit', '1024M');
define('PATH', '/var/www/html/');
define('SERVERIP', '192.168.1.15');
define('LB', '192.168.1.15');
define('APIDOAMIN', 'http://localhost/');
define('CDR_DSN', 'mysql:dbname=cdrlog;host=127.0.0.1');
define('CDR_DSN_LOGIN', 'ovuser');
define('CDR_DSN_PASSWORD', 'OV500123SA');
define('SWITCH_DSN', 'mysql:dbname=switch;host=127.0.0.1');
define('SWITCH_DSN_LOGIN', 'ovuser');
define('SWITCH_DSN_PASSWORD', 'OV500123SA');
define('LOGPATH', 'log/');
define('LOGWRITE', true);
define('RECORDING', '/var/www/html/portal/uploads/recording/');
define('STIRURL', 'http://localhost/portal/api/pass.php');
define('STIRSHAKEN', false);

