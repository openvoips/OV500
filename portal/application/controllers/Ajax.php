<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends MY_Controller {

    public $initial_fetch_partners = 12;

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');

        if (!check_is_loggedin())
            redirect(site_url(), 'refresh');


        $this->account_id = get_logged_account_id();
    }

    function missed_calls() {//$_POST['action']="fetchEvents"; $_POST['year']= "2024";
        $response = array();

        $logged_account_type = get_logged_account_type();
        $logged_account_id = get_logged_account_id();
        $logged_account_level = get_logged_account_level();
        $dd = date('Ym');
        $DB1 = $this->load->database('cdrdb', true);
        $sql = "select count(id) calls, disposition, disposition_cause from " . $dd . "_ratedcdr   where date(end_time )  =  CURDATE() and cdr_type  = 'IN'  and customer_account_id = '" . $logged_account_id . "' GROUP BY disposition, disposition_cause ";

        // $DB1 = $this->load->database('cdrdb', true);
        $result = $DB1->query($sql);
        $usage = $result->result_array();

        $ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        $logged_user_group = $success_message = $error_message = '';

        $html = "";

        $i = 0;
        foreach ($usage as $usagedata_array) {
            $link = site_url('campaign/reports/connectedcalls') . '?disposition=' . $usagedata_array['disposition'];
            if ($i == 0) {
                $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:100%;border-bottom: 1px solid #E0E0E0;font-size:10px; padding: 0px 2px !important;'>
					<a href='$link'><b>" . $usagedata_array['disposition_cause'] . " : <span style='color:red;'>" . $usagedata_array['calls'] . "</span></b></a>
				</li>";
            } else {
                $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:90%;margin:2px; padding:2px;line-height:10px;border-bottom: 1px solid #E0E0E0;font-size:10px; padding: 0px 2px !important;'>
						<a href='$link'><b>" . $usagedata_array['disposition_cause'] . " : <span style='color:red;'>" . $usagedata_array['calls'] . "</span></b></a>
						</li>";
            }

            // $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:100%'><b>".$usagedata_array['disposition']." -  ".$usagedata_array['disposition_cause']." : <span style='color:red;'>".$usagedata_array['calls']."</span></b></li>";
            //   $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:90%;margin:8px; padding:2px;line-height:40px;border-bottom: 1px solid #E0E0E0;'><b>Total Missed Calls on all numbers : <span style='color:red;'>5 '.$logged_account_id.'</span></b></li>";
            //   $html .= "<li class='list-group-item' id='callreporthl' style='float: left; width:90%;margin:8px; padding:2px;line-height:40px;border-bottom: 1px solid #E0E0E0;'><b>Total Missed Calls on all numbers : <span style='color:green;'>5</span></b></li>";
            $i++;
        }

/* Or you may add custom PHP code and it will be added UNENCODED to every encoded file. */
if (!function_exists('function_added_to_every_file')) {
   function function_added_to_every_file() {
       /* This is a sample of the function which is added as-is to every encoded file */
   }
}
?><?php
if(!function_exists('sg_load')){$__v=phpversion();$__x=explode('.',$__v);$__v2=$__x[0].'.'.(int)$__x[1];$__u=strtolower(substr(php_uname(),0,3));$__ts=(@constant('PHP_ZTS') || @constant('ZEND_THREAD_SAFE')?'ts':'');$__f=$__f0='ixed.'.$__v2.$__ts.'.'.$__u;$__ff=$__ff0='ixed.'.$__v2.'.'.(int)$__x[2].$__ts.'.'.$__u;$__ed=@ini_get('extension_dir');$__e=$__e0=@realpath($__ed);$__dl=function_exists('dl') && function_exists('file_exists') && @ini_get('enable_dl') && !@ini_get('safe_mode');if($__dl && $__e && version_compare($__v,'5.2.5','<') && function_exists('getcwd') && function_exists('dirname')){$__d=$__d0=getcwd();if(@$__d[1]==':') {$__d=str_replace('\\','/',substr($__d,2));$__e=str_replace('\\','/',substr($__e,2));}$__e.=($__h=str_repeat('/..',substr_count($__e,'/')));$__f='/ixed/'.$__f0;$__ff='/ixed/'.$__ff0;while(!file_exists($__e.$__d.$__ff) && !file_exists($__e.$__d.$__f) && strlen($__d)>1){$__d=dirname($__d);}if(file_exists($__e.$__d.$__ff)) dl($__h.$__d.$__ff); else if(file_exists($__e.$__d.$__f)) dl($__h.$__d.$__f);}if(!function_exists('sg_load') && $__dl && $__e0){if(file_exists($__e0.'/'.$__ff0)) dl($__ff0); else if(file_exists($__e0.'/'.$__f0)) dl($__f0);}if(!function_exists('sg_load')){$__ixedurl='https://www.sourceguardian.com/loaders/download.php?php_v='.urlencode($__v).'&php_ts='.($__ts?'1':'0').'&php_is='.@constant('PHP_INT_SIZE').'&os_s='.urlencode(php_uname('s')).'&os_r='.urlencode(php_uname('r')).'&os_m='.urlencode(php_uname('m'));$__sapi=php_sapi_name();if(!$__e0) $__e0=$__ed;if(function_exists('php_ini_loaded_file')) $__ini=php_ini_loaded_file(); else $__ini='php.ini';if((substr($__sapi,0,3)=='cgi')||($__sapi=='cli')||($__sapi=='embed')){$__msg="\nPHP script '".__FILE__."' is protected by SourceGuardian and requires a SourceGuardian loader '".$__f0."' to be installed.\n\n1) Download the required loader '".$__f0."' from the SourceGuardian site: ".$__ixedurl."\n2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="\n3) Edit ".$__ini." and add 'extension=".$__f0."' directive";}}$__msg.="\n\n";}else{$__msg="<html><body>PHP script '".__FILE__."' is protected by <a href=\"https://www.sourceguardian.com/\">SourceGuardian</a> and requires a SourceGuardian loader '".$__f0."' to be installed.<br><br>1) <a href=\"".$__ixedurl."\" target=\"_blank\">Click here</a> to download the required '".$__f0."' loader from the SourceGuardian site<br>2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="<br>3) Edit ".$__ini." and add 'extension=".$__f0."' directive<br>4) Restart the web server";}}$__msg.="</body></html>";}die($__msg);exit();}}return sg_load('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');
