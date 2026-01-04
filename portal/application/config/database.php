<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	$active_group = 'default';
	$query_builder = TRUE;
	$db['default'] = array( 
	'dsn' => 'mysql:host=127.0.0.1;dbname=ov500',
	'hostname' => '', 
	'username' => 'ovuser', 
        'password' => 'OV500DBPASSWORD',
	'database' => '', 
	'dbdriver' => 'pdo', 
	'dbprefix' => '', 
	'pconnect' => TRUE, 
	'db_debug' => TRUE, 
	'cache_on' => FALSE, 
	'cachedir' => '', 
	'char_set' => 'utf8', 
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '', 
	'encrypt' => FALSE, 
	'compress' => FALSE, 
	'stricton' => FALSE, 
	'failover' => array() 
	);
	$db['cdrdb'] = array(
	'dsn' => 'mysql:host=127.0.0.1;dbname=cdrlog',
	'hostname' => '',
	'username' => 'ovuser', 
        'password' => 'OV500DBPASSWORD',
	'database' => '',
	'dbdriver' => 'pdo',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
	);

