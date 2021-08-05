<?php
/**
 * CodeIgniter User Audit Trail
 *
 * Version 1.0, October - 2017
 * Author: Firoz Ahmad Likhon <likh.deshi@gmail.com>
 * Website: https://github.com/firoz-ahmad-likhon
 *
 * Copyright (c) 2018 Firoz Ahmad Likhon
 * Released under the MIT license
 *       ___            ___  ___    __    ___      ___  ___________  ___      ___
 *      /  /           /  / /  /  _/ /   /  /     /  / / _______  / /   \    /  /
 *     /  /           /  / /  /_ / /    /  /_____/  / / /      / / /     \  /  /
 *    /  /           /  / /   __|      /   _____   / / /      / / /  / \  \/  /
 *   /  /_ _ _ _ _  /  / /  /   \ \   /  /     /  / / /______/ / /  /   \    /
 *  /____________/ /__/ /__/     \_\ /__/     /__/ /__________/ /__/     /__/
 * Likhon the hackman, who claims himself as a hacker but really he isn't.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Enable Audit Trail
|--------------------------------------------------------------------------
|
| Set [TRUE/FALSE] to use of audit trail
|
*/
$config['audit_enable'] = FALSE;

/*
|--------------------------------------------------------------------------
| Not Allowed table for auditing
|--------------------------------------------------------------------------
|
| The following setting contains a list of the not allowed database tables for auditing.
| You may add those tables that you don't want to perform audit.
|
*/
$config['not_allowed_tables'] = [
    'ci_sessions',
    'user_audit_trails',
];

/*
|--------------------------------------------------------------------------
| Enable Insert Event Track
|--------------------------------------------------------------------------
|
| Set [TRUE/FALSE] to track insert event.
|
*/
$config['track_insert'] = TRUE;

/*
|--------------------------------------------------------------------------
| Enable Update Event Track
|--------------------------------------------------------------------------
|
| Set [TRUE/FALSE] to track update event
|
*/
$config['track_update'] = TRUE;

/*
|--------------------------------------------------------------------------
| Enable Delete Event Track
|--------------------------------------------------------------------------
|
| Set [TRUE/FALSE] to track delete event
|
*/
$config['track_delete'] = TRUE;
