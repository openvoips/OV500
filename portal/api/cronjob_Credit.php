<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */


/*
 * This script is to deducet the credit from account which schedule for deduction 
 */
include_once 'config.php';
include_once 'lib/APIS.php';
$APIS = New APIS();
$APIS->ServiceCreditDeduction();

?>
