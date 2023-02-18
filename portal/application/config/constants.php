<?php

defined('BASEPATH') OR exit('No direct script access allowed');


/* determine subdoamin */

define('SITE_SUBDOMAIN', 'OV500');


/*
  |--------------------------------------------------------------------------
  | Display Debug backtrace
  |--------------------------------------------------------------------------
  |
  | If set to TRUE, a backtrace will be displayed along with php errors. If
  | error_reporting is disabled, the backtrace will not display, regardless
  | of this setting
  |
 */
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
  |--------------------------------------------------------------------------
  | File and Directory Modes
  |--------------------------------------------------------------------------
  |
  | These prefs are used when checking and setting modes when working
  | with the file system.  The defaults are fine on servers with proper
  | security, but you may wish (or even need) to change the values in
  | certain environments (Apache running a separate process for each
  | user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
  | always be used to set the mode correctly.
  |
 */
defined('FILE_READ_MODE') OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') OR define('DIR_WRITE_MODE', 0755);

/*
  |--------------------------------------------------------------------------
  | File Stream Modes
  |--------------------------------------------------------------------------
  |
  | These modes are used when working with fopen()/popen()
  |
 */
defined('FOPEN_READ') OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
  |--------------------------------------------------------------------------
  | Exit Status Codes
  |--------------------------------------------------------------------------
  |
  | Used to indicate the conditions under which the script is exit()ing.
  | While there is no universal standard for error codes, there are some
  | broad conventions.  Three such conventions are mentioned below, for
  | those who wish to make use of them.  The CodeIgniter defaults were
  | chosen for the least overlap with these conventions, while still
  | leaving room for others to be defined in future versions and user
  | applications.
  |
  | The three main conventions used for determining exit status codes
  | are as follows:
  |
  |    Standard C/C++ Library (stdlibc):
  |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
  |       (This link also contains other GNU-specific conventions)
  |    BSD sysexits.h:
  |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
  |    Bash scripting:
  |       http://tldp.org/LDP/abs/html/exitcodes.html
  |
 */
defined('EXIT_SUCCESS') OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
/////////////////////
define('DATE_FORMAT_1', 'd-m-Y');
define('DATE_FORMAT_2', 'd-m-Y h:i A');

define('SCRIPT_DATE_FORMAT_1', 'DD-MM-YYYY HH:mm');
////////////////////////

define('DAY_FROM_WEEK', serialize(array(0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday')));


define('RECORDS_PER_PAGE', 10);

define('KEY_PREFIX', 'SW');

define('ENABLE_PROFILE', false);




$help_text_array = array();
$help_text_array['cli_riles'] = array(
    array('%=>%', 'allow all CLI without CLI translation.'),
    array('44|%=>%', 'allow only 44 prefix CLI and removing 44 prefix from CLI.'),
    array('44|%=>0044%', 'allow only 44 prefix CLI and removing 44 and adding 0044 prefix in CLI.'),
    array('44{4}|%=>%', 'allowing only 44 prefix CLI with 4 length and removing 44 from the CLI.'),
    array('{10}%=>91%', 'allowing only 10 digit CLI and adding 91 prefix in the CLI.'),
    array('%=>919949800228', 'allowing all CLI and replacing incoming CLI with 919949800228.')
);
$help_text_array['trans_riles'] = array(
    array('%=>%', 'allow all dialed number without any  translation.'),
    array('44|%=>%', 'allow only 44 prefix Dialed number and removing 44 prefix from dialed number.'),
    array('44|%=>0044%', 'allow only 44 prefix dialed number and removing 44 and adding 0044 prefix in dialed number.'),
    array('44{4}|%=>%', 'allowing only 44 prefix dialed number with 4 length and removing 44 from the dialed number.'),
    array('{10}%=>91%', 'allowing only 10 digit dialed number and adding 91 prefix in the number.'),
    array('%=>919949800228', 'allowing all dialed number and replacing incoming dialed number with 919949800228.'),
);

$help_text_array['term_prefix_riles'] = array(
    array('%=>%', 'allow all dialed number without any  translation.'),
    array('44|%=>%', 'allow only 44 prefix Dialed number and removing 44 prefix from dialed number.'),
    array('44|%=>0044%', 'allow only 44 prefix dialed number and removing 44 and adding 0044 prefix in dialed number.'),
    array('44{4}|%=>%', 'allowing only 44 prefix dialed number with 4 length and removing 44 from the dialed number.'),
    array('{10}%=>91%', 'allowing only 10 digit dialed number and adding 91 prefix in the number.'),
    array('%=>919949800228', 'allowing all dialed number and replacing incoming dialed number with 919949800228.'),
);

if (!defined('SITE_SUBDOMAIN')) define('SITE_SUBDOMAIN', 'OV500');
define('SITE_NAME', 'Telecoms Billing Solution');
define('SITE_FULL_NAME', 'Telecoms Billing Solution');
define('SDR_API_URL', 'https://localhost/portal/api/sdrapi.php');
define('LOGO_IMAGE', 'logo.png');



define('MOMENT_TIMEZONE', 'Asia/Kolkata');


define('CUSTOMERCODEPREFIX','STC');
define('RESELLERCODEPREFIX','STR');


define('CUSTOMERCOMPANY','OpenVoips Technologies');



define('SITE_MAIL_FROM', 'support@suretel.co.za');
define('SITE_MAIL_TO', 'support@suretel.co.za');
define('CREDIT_MAIL_TO', 'accounts@suretel.co.za');


//define('PAYPAL_LINK', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
define('PAYPAL_LINK', 'https://www.paypal.com/cgi-bin/webscr');

define('RECORDS_PER_PAGE_ARRAY', serialize(array(1,2,3,10, 20, 30, 50, 100, 200,500,1000)));
define('ADMIN_ACCOUNT_ID', 'SYSTEM');
