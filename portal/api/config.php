<?php

// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//OV500 Version 1.0.3.3
// License https://www.gnu.org/licenses/agpl-3.0.html
//
//
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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
error_reporting(0);
ini_set('memory_limit', '1024M');
date_default_timezone_set("GMT0");
define('CDR_DSN', 'mysql:dbname=switchcdr;host=localhost');
define('CDR_DSN_LOGIN', 'ovswitch');
define('CDR_DSN_PASSWORD', 'ovswitch123');
define('SWITCH_DSN', 'mysql:dbname=sasswtch;host=localhost');
define('SWITCH_DSN_LOGIN', 'ovswitch');
define('SWITCH_DSN_PASSWORD', 'ovswitch123');
define('LOGPATH', 'log/');
define('LOGWRITE', '1');


?>
