<?php
/* Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use 
 * OV500Pro Version 2.1.0
 * Written by Seema Anand <openvoips@gmail.com> , 2021 
 * http://www.openvoips.com 
 * License https://www.openvoips.com/license.html
 */

class Activitylog_mod extends CI_Model {

    public $total_count;
	public $select_sql;
   function __construct()
	{
		parent::__construct();
		$this->load->database();	
	}

    function get_data_total_count($sql_exists=false,$db='default') {
	
		try	{
		
			if($sql_exists && isset($this->total_count_sql) && $this->total_count_sql!='')
			{
				$count_sql = trim($this->total_count_sql);
			}
			else
			{			
				$count_sql = generate_count_total_sql($this->select_sql);	
				if(substr($count_sql,0,5)=='error')
				{
					throw new \Exception($count_sql);
				}
			}	
			//echo $this->select_sql.'<br>'. $count_sql;
			$this->total_count_sql=$count_sql;
			if($db=='default')
				$query_count = $this->db->query($count_sql);
			else
				$query_count = $this->cdr->query($count_sql);	
			$row_count = $query_count->row();;
			$this->total_count = $row_count->total;
			return $this->total_count;
			
		 } catch (\Exception $e) {
		 	//echo $e->getMessage();
			return 0;
		 } 
	
        return 0;//$this->total_count;
    }
	
	function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array()) 
	{
        $final_return_array = array('result'=>array());
		
        try {
			$group_by='';
			if(isset($filter_data['group_by']) && count($filter_data['group_by'])>0)
			{
				$sql = "SELECT log.*, count(id) group_count FROM " . $this->db->dbprefix('activity_site_log') . " log  WHERE 1";
				
				$group_by = implode(', ',$filter_data['group_by']);
				//$group_by = $filter_data['group_by'];
				unset($filter_data['group_by']);
			}
			else
			{
            	$sql = "SELECT log.* FROM " . $this->db->dbprefix('activity_site_log') . " log  WHERE 1";
			}
			
            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                  if ($value != '') {
                        if (in_array($key, array('account_id','id')))
                            $sql .= " AND $key ='" . $value . "' ";
                        if ($key=='time_range')
						{
							$range = explode(' - ', $value);				
							$start_dt = trim($range[0]);
							$end_dt = trim($range[1]);
                            $sql .= " AND created_dt BETWEEN '$start_dt' AND '$end_dt' ";
						}
						else
                            $sql .= " AND $key LIKE '%" . $value . "%' ";
                    }
                }
            }
			
			if ($group_by != '') {
				 $sql .= " GROUP BY $group_by";
			}

            if ($order_by != '') {
                $sql .= " ORDER BY $order_by ";
            } else {
                $sql .= " ORDER BY `created_dt` DESC ";
            }
			
			

            $limit_from = intval($limit_from);

            if ($limit_to != '')
                $sql .= " LIMIT $limit_from, $limit_to";

            $query = $this->db->query($sql);
		
			//echo $sql;
            if (!$query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
			$this->select_sql=$sql;

           /* $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $this->db->query($sql);
            $row_count = $query_count->row();
            $this->total_count = $row_count->total;
*/
            $final_return_array['result'] = $query->result_array();
          
			
            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Account activity log fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {

            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

function get_activity_types()
{
	try{
		$final_return_array = array();
		$sql = "SELECT DISTINCT event FROM ".$this->db->dbprefix('activity_site_log')." WHERE event!='' ORDER BY event";
		$query = $this->db->query($sql);				
		$final_return_array['result'] = $query->result_array();
		$final_return_array['status'] = 'success';
        $final_return_array['message'] = 'Activity types fetched successfully';
		return $final_return_array;
	
	}
	catch (Exception $e) {

		$final_return_array['status'] = 'failed';
		$final_return_array['message'] = $e->getMessage();
		return $final_return_array;
	}		
		
}
  
}
