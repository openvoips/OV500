<?php
/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */

class Sitesetup_mod extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
       $this->defaultdata();
    }

    function defaultdata() {
        $sql = "SELECT * FROM  sys_system_config";
        $query = $this->db->query($sql);
        $rows = $query->result_array();

        foreach ($rows as $row) {
            define($row['default_variable'], $row['values']);
        }
    }

    function get_license() {
        try {
            $return_array=[];
            $return_array['license_status']=false;
            $sql = "SELECT `values` FROM ".$this->db->dbprefix('sys_system_config')." WHERE  default_variable ='LICENSE'";
            $query = $this->db->query($sql);
            $row = $query->row_array();
            if(isset($row)) 
            {
                //echo '----'.LICENSE.'---';die;
                $license_str=$row['values'];       
                $return_array['license']= $license_str;   
                $license_array1=explode(',',$license_str);
                if(count($license_array1)==3)
                {
                    $expiry_str=$license_array1[0];
                    $pre_modules_str=$license_array1[1];
                    $free_module_str=$license_array1[2];

                    $expiry_array=explode(':',$expiry_str);
                    $pre_modules_str_array=explode(':',$pre_modules_str);
                    $free_module_str_array=explode(':',$free_module_str);
                    if(count($expiry_array)==2 && count($pre_modules_str_array)==2 && count($free_module_str_array)==2)
                    {
                        $expiry_dt_str=$expiry_array[1];
                        $pre_modules_str2=$pre_modules_str_array[1];
                        $free_module_str2=$free_module_str_array[1];
                        //echo $expiry_dt_str;
                        $pre_module_array = explode(';',$pre_modules_str2);
                        $free_module_array = explode(';',$free_module_str2);
                        $alloed_modules=$free_module_array;
                        $return_array['free_modules']=$free_module_array;
                        $return_array['premium_modules']=$pre_module_array;
                        $return_array['allowed_modules']=$return_array['free_modules'];
                        if(strlen($expiry_dt_str)==8)
                        {
                            $expiry_year=substr($expiry_dt_str,0,4);
                            $expiry_month=substr($expiry_dt_str,4,2);
                            $expiry_day=substr($expiry_dt_str,6,2);

                            //echo $expiry_year.'-'.$expiry_month.'-'.$expiry_day;
                            if(checkdate($expiry_month, $expiry_day, $expiry_year))
                            {
                                $expiry_date=date_create("$expiry_year-$expiry_month-$expiry_day");
                                $today_date=date_create("yesterday");
                                $diff=date_diff($today_date,$expiry_date);
                                $return_array['expiry_date']=date_format($expiry_date,'Y-m-d');
                                if($expiry_date > $today_date)
                                {
                                    $return_array['license_status']=true;
                                    $alloed_modules=array_merge($pre_module_array,$free_module_array);
                                    $return_array['allowed_modules']=$alloed_modules;
                                }
                                else
                                {
                                    throw new Exception('License Expired');
                                }
                            }
                        }

                    }

                }
                
            }
            else
            {                
                throw new Exception('License Key Not Found');
            }
            if($return_array['license_status']===true)
                return $return_array;
            else
                throw new Exception('Invalid License Key');
        } catch (Exception $e) {
            $return_array['license_status']=false;
            $return_array['message']=$e->getMessage();
            return $return_array;
        }           
    }
    function update_license($value) {
        $data_array=[];
        $value = trim($value);
        if(!$this->check_license($value))
            return 'Invalid Key'; 

        $data_array['values']=$value;
        $where = "default_variable='LICENSE'";
        $sql = "SELECT `values` FROM ".$this->db->dbprefix('sys_system_config')." WHERE  default_variable ='LICENSE'";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        if(isset($row)) 
        {
            $str = $this->db->update_string('sys_system_config', $data_array, $where);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                return $error_array['message'];
            }
        }
        else
        {
            $data_array['default_variable'] = 'LICENSE';
            $str = $this->db->insert_string('sys_system_config', $data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
        }
        return true;
    }

    function update_sitesetup($data) {

        $sql = " UPDATE sys_sitesetup SET 
		invoice_count = invoice_count + 1 ";
        $result = $this->db->query($sql);

        if (!$result) {
            $error_array = $this->db->error();
            return $error_array['message'];
        }
        return true;
    }

    function check_license($key)
    {        
        //echo '----'.LICENSE.'---';die;
        $license_str=$key;          
        $license_array1=explode(',',$license_str);
        if(count($license_array1)==3)
        {
            $expiry_str=$license_array1[0];
            $pre_modules_str=$license_array1[1];
            $free_module_str=$license_array1[2];

            $expiry_array=explode(':',$expiry_str);
            $pre_modules_str_array=explode(':',$pre_modules_str);
            $free_module_str_array=explode(':',$free_module_str);
            if(count($expiry_array)==2 && count($pre_modules_str_array)==2 && count($free_module_str_array)==2)
            {
                $expiry_dt_str=$expiry_array[1];
                $pre_modules_str2=$pre_modules_str_array[1];
                $free_module_str2=$free_module_str_array[1];
                //echo $expiry_dt_str;
                $pre_module_array = explode(';',$pre_modules_str2);
                $free_module_array = explode(';',$free_module_str2);
                $alloed_modules=$free_module_array;
                $return_array['free_modules']=$free_module_array;
                $return_array['premium_modules']=$pre_module_array;
                $return_array['allowed_modules']=$return_array['free_modules'];
                if(strlen($expiry_dt_str)==8)
                {
                    $expiry_year=substr($expiry_dt_str,0,4);
                    $expiry_month=substr($expiry_dt_str,4,2);
                    $expiry_day=substr($expiry_dt_str,6,2);

                    //echo $expiry_year.'-'.$expiry_month.'-'.$expiry_day;
                    if(checkdate($expiry_month, $expiry_day, $expiry_year))
                    {
                        $expiry_date=date_create("$expiry_year-$expiry_month-$expiry_day");
                        $today_date=date_create("yesterday");
                        $diff=date_diff($today_date,$expiry_date);
                        $return_array['expiry_date']=date_format($expiry_date,'Y-m-d');
                        if($expiry_date > $today_date)
                        {
                            return true;                           
                        }
                        
                    }
                }

            }

        }
            
        return false;
    }

    function update_sitesetup_with_file($filename) {
        $sql = " UPDATE sys_sitesetup SET 
		admin_logo=" . $this->db->escape($filename) . " ";
        $query = $this->db->query($sql);
    }

    function get_sitesetup_data() {
        $sql = "SELECT * FROM  sys_sitesetup limit 1";
        $query = $this->db->query($sql);
        $row_data = $query->row_array();
 
        return $row_data;
    }

}
