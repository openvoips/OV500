<?php
/* 
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026
 * http://www.openvoips.com 
 
	ssfGnqKpfHODe3eDi4WMcbPBvLOhxr60urq7ibu1w6+5wXyrtZqvuLu2kZqelLKwvLe3xpS2xrh5v8mzoca+tLq6u8KDsba0sMOqvcWasraIv7eulpG1tY67qLbBtMassorHsLqUwsS3t8a0hMC0ubeurQ==
	expiry:20240228,free_module:report;billing;endpoints;crs,pre_modules:activitylog;payfast;recharge;pbx;stripe;paypal
 
 */
class License_mod extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
      
    }

    /*return all data from table */
    function get_data($filter_data = array()) {
        $final_return_array =[];
        try {
            
            $final_return_array['result']= Array();
            $sql = "SELECT * FROM sys_license WHERE 1 ";
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'status_id')
                            $sql .= " AND $key ='" . $value . "' ";                      
                        else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }


            $sql .= " ORDER BY status_id DESC, id DESC ";            
            $query = $this->db->query($sql);
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            //$final_return_array['result']=$query->result_array();
            foreach ($query->result_array() as $row) {
                $license_str = decrypt($row['license_key']);
                $return=$this->get_license_breakup($license_str);
                $row['allowed_modules']=[];
                if(isset($return['allowed_modules']))
                {
                    $row['allowed_modules']=$return['allowed_modules'];
                }
				
			 
				if(isset($return['expiry_date']))
                {
                    $row['expiry']=$return['expiry_date'];
                }
				
                $final_return_array['result'][]=$row;

            }
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'License fetched successfully';



 
            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

  

    /*return active license data */
    function get_license() {
        try {
            $return_array=[];
            $return_array['license_status']=false;
            $sql = "SELECT * FROM sys_license WHERE status_id ='1' ORDER BY id DESC LIMIT 1";
            $query = $this->db->query($sql);
            $row = $query->row_array();
			
			 
            if(isset($row)) 
            {
                //echo '----'.LICENSE.'---';die;
				//echo $license_str1 = $row['license_key'];
                $license_str=decrypt($row['license_key']);       
                //$return_array['license']= $license_str1;   

                $return = $this->get_license_breakup($license_str);                
                return $return;                
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
    /* set new license */
    function update_license($value) {
        $data_array=[];
        $value = trim($value);
        $value1 = decrypt($value);
        $return = $this->get_license_breakup($value1);

        if($return['license_status']==false)
        {//die("invalid");
            return 'Invalid Key'; 
        }

        $datetime = date("Y-m-d H:i:s");
        $logged_account_id = get_logged_account_id();
       
        $where = "default_variable='LICENSE'";
        $sql = "UPDATE sys_license SET
        status_id='0',
        dt_cancelled='$datetime',
        cancelled_by='$logged_account_id'
        WHERE  status_id ='1'";
        $query = $this->db->query($sql);
       
        {
            $data_array['license_key']=$value;
            $data_array['status_id'] = '1';
            $data_array['dt_created']=$datetime;
            $data_array['created_by']=$logged_account_id;
            $str = $this->db->insert_string('sys_license', $data_array);
            $result = $this->db->query($str);
            if (!$result) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
        }
        return true;
    }

    
    /*validates license string */
    function get_license_breakup($license_str)
    {
        try {
            $return_array=[];

            $license_array1=explode(',',$license_str);
			
	 
            if(count($license_array1)<2 || count($license_array1)>3)
                throw new Exception('Invalid License');
            
            {
                $expiry_str=$license_array1[0];
                $expiry_array=explode(':',$expiry_str);
                
                $free_module_str=$license_array1[1];
                $free_module_str_array=explode(':',$free_module_str);
                
				if($free_module_str_array[0] != 'free_module'){					
					throw new Exception('Invalid License');
				}
                if(count($expiry_array)!=2 )
                    throw new Exception('Invalid License');
                if(count($free_module_str_array)!=2 )
                    throw new Exception('Invalid License');

                if(isset($license_array1[2]))
                {
					$pre_modules_str=$license_array1[2];
                    $pre_modules_str_array=explode(':',$pre_modules_str);
					
				if($pre_modules_str_array[0] != 'pre_modules'){
					 throw new Exception('Invalid License');
				}

                    if(count($pre_modules_str_array)!=2 )
                        throw new Exception('Invalid License');
                }
                else
                {
                    $pre_modules_str='pre_modules:';
                    $pre_modules_str_array=explode(':',$pre_modules_str);
                }
                
                //if(count($expiry_array)==2 && count($pre_modules_str_array)==2 && count($free_module_str_array)==2)
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

    function del_check_license($key)
    {        
        //echo '----'.LICENSE.'---';die;
        $license_str=$key;          
        $license_array1=explode(',',$license_str);
        if(count($license_array1)<2 || count($license_array1)>3)
            return false;
         
        {
            $expiry_str=$license_array1[0];
            $expiry_array=explode(':',$expiry_str);

            $free_module_str=$license_array1[1];
            $free_module_str_array=explode(':',$free_module_str);
            
            if(count($expiry_array)!=2 )
                return false;
            if(count($free_module_str_array)!=2 )
                return false;
               
            if(isset($license_array1[2]))
            {
                $pre_modules_str=$license_array1[2];
                $pre_modules_str_array=explode(':',$pre_modules_str);
                if(count($pre_modules_str_array)!=2 )
                    return false;
            }
          
            //if(count($pre_modules_str_array)==2 && count($free_module_str_array)==2)
            {
                $expiry_dt_str=$expiry_array[1];
                $pre_modules_str2=$pre_modules_str_array[1];
                $free_module_str2=$free_module_str_array[1];
                //echo $expiry_dt_str;
                $pre_module_array = explode(';',$pre_modules_str2);
                $free_module_array = explode(';',$free_module_str2);
                $alloed_modules=$free_module_array;
                //$return_array['free_modules']=$free_module_array;
                //$return_array['premium_modules']=$pre_module_array;
                //$return_array['allowed_modules']=$return_array['free_modules'];
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


}
