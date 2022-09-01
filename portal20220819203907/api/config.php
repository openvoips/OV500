<?php
	error_reporting(0);
	ini_set('memory_limit', '1024M');
	date_default_timezone_set('Asia/Kolkata');
	define('CDR_DSN', 'mysql:dbname=switchcdr;host=localhost');
	define('CDR_DSN_LOGIN', 'ovswitch');
	define('CDR_DSN_PASSWORD','Sqrk*gQJWNqmCA5qHikG');
	define('SWITCH_DSN', 'mysql:dbname=switch;host=localhost');
	define('SWITCH_DSN_LOGIN', 'ovswitch', );
	define('SWITCH_DSN_PASSWORD','Sqrk*gQJWNqmCA5qHikG');
	define('LOGPATH', 'log/');
	define('LOGWRITE', '0');
	define('DBLOGWRITE', '1');
