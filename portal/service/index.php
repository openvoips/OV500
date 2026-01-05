<?php

include_once 'config.php';
include_once 'lib/pdo.php';
include_once 'lib/service.php';

$db = New MYPDO();
$service = New Service($db);
$service->main();
