<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0
// License https://www.gnu.org/licenses/agpl-3.0.html
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

class Ticket_mod extends CI_Model {

    public $did_id;
    public $total_count;

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function get_categories() {
        $final_return_array = array();
        try {
            $DB1 = $this->load->database('cdrdb', true);

            $sql = "SELECT category_id, category_parent_id, category_name FROM ticket_categories WHERE status='Y' ORDER BY category_parent_id, category_name";
            $query = $DB1->query($sql);
            if (!$query) {
                $error_array = $DB1->error();
                throw new Exception($error_array['message']);
            }
            foreach ($query->result_array() as $row) {
                //print_r($row); die;
                $category_id = $row['category_id'];
                $category_parent_id = $row['category_parent_id'];
                if ($category_parent_id == 0)
                    $final_return_array['result'][$category_id] = $row;
                else
                    $final_return_array['result'][$category_parent_id]['sub'][] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Deleted log fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    function get_assignto() {
        $final_return_array = array();
        try {
            $DB1 = $this->load->database('cdrdb', true);

            $sql = "SELECT assigned_to_id, assigned_to_name FROM ticket_assigned_to WHERE 1 ORDER BY assigned_to_name";
            $query = $DB1->query($sql);
            if (!$query) {
                $error_array = $DB1->error();
                throw new Exception($error_array['message']);
            }
            foreach ($query->result_array() as $row) {
                //print_r($row); die;
                $assigned_to_id = $row['assigned_to_id'];
                $final_return_array['result'][$assigned_to_id] = $row;
            }

            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Assigned to fetched successfully';

            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    /* Add Ticket */

    function add($data) {
        try {
            $DB1 = $this->load->database('cdrdb', true);
            $data = add_slashes($data);
            $sql = "SELECT company_name FROM customers  WHERE account_id ='" . $data['account_id'] . "'";
            $query = $this->db->query($sql);
            $existing_user_row = $query->row_array();
            if (!isset($existing_user_row)) {
                $existing_user_row['company_name'] = $data['account_id'];
            }
            
            $DB1->trans_begin();
            $ticket_data_array = array();
            $ticket_data_array['subject'] = $data['subject'];
            $ticket_data_array['content'] = $data['content'];
            $ticket_data_array['account_id'] = $data['account_id'];
            $ticket_data_array['company_name'] = $existing_user_row['company_name'];
            $ticket_data_array['category_id'] = $data['category_id'];
            $ticket_data_array['created_by'] = $data['created_by'];
            $ticket_data_array['created_by_name'] = $data['created_by_name'];
            $ticket_data_array['parent_id'] = 0;
            $ticket_data_array['assigned_to_id'] = 1;
            $ticket_data_array['status'] = 'open';
            $ticket_data_array['create_date'] = date('Y-m-d H:i:s');
            $ticket_data_array['created_by_ip'] = getUserIP();
            if (count($ticket_data_array) > 0) {
                $str = $DB1->insert_string('tickets', $ticket_data_array);
                $result = $DB1->query($str);
                if (!$result) {
                    $error_array = $DB1->error();
                    throw new Exception($error_array['message']);
                }
                $this->ticket_id = $DB1->insert_id();

                $ticket_update_data_array = array();
                $ticket_update_data_array['ticket_number'] = 'T' . sprintf('%06s', $this->ticket_id);
                $where = "ticket_id='" . $this->ticket_id . "'";
                $str = $DB1->update_string('tickets', $ticket_update_data_array, $where);
                $result = $DB1->query($str);
            }


            if (isset($data['attachment']) && count($data['attachment']) > 0) {
                foreach ($data['attachment'] as $attachment_array) {
                    $attachment_data_array = array();
                    $attachment_data_array['ticket_id'] = $this->ticket_id;
                    $attachment_data_array['file_name'] = $attachment_array['file_name'];
                    $attachment_data_array['file_name_display'] = $attachment_array['file_name_display'];

                    $str = $DB1->insert_string('ticket_attachments', $attachment_data_array);
                    $result = $DB1->query($str);
                    if (!$result) {
                        $error_array = $DB1->error();
                        throw new Exception($error_array['message']);
                    }
                }
            }
   if ($DB1->trans_status() === FALSE) {
                $error_array = $DB1->error();
                $DB1->trans_rollback();
                return $error_array['message'];
            } else {
                $DB1->trans_commit();
            }
            return true;
        } catch (Exception $e) {
            $DB1->trans_rollback();
            return $e->getMessage();
        }
    }

    function ticket_summary($user_type, $account_id) {
        $DB1 = $this->load->database('cdrdb', true);
        if (in_array($user_type, array('CUSTOMER', 'RESELLER'))) {
            $final_return_array = array('open' => 0, 'admin_replied' => 0);
            $sql = "SELECT count(*) total_open FROM tickets t1  WHERE t1.parent_id='0' AND t1.status='open' AND account_id='" . $account_id . "'";
            $query = $DB1->query($sql);
            $row = $query->row();
            $final_return_array['open'] = $row->total_open;

            if ($final_return_array['open'] > 0) {                
                $sub_sql = "SELECT account_id FROM customers where account_type = 'ADMIN'";
                $sub_query = $this->db->query($sub_sql);
                if (!$sub_query) {
                    $error_array = $this->db->error();
                    throw new Exception($error_array['message']);
                }
                $user_access_id_name_array = array();
                if ($sub_query->row() > 0) {
                    foreach ($sub_query->result_array() as $row) {
                        $user_access_id_name_array[] = $row['account_id'];
                    }
                }
                $account_id_str = implode("','", $user_access_id_name_array);
                $account_id_str = "'" . $account_id_str . "'";

                //all tickets whose last reply is by Admin(i.e., Admin users)
                $sql = "SELECT count(*) total_replied FROM tickets  t1  WHERE t1.parent_id='0'	AND account_id='" . $account_id . "' AND t1.ticket_id IN (SELECT t2.parent_id FROM tickets t2 WHERE t2.ticket_id IN (SELECT MAX(t3.ticket_id) FROM tickets t3 WHERE t3.parent_id!='0' AND hide_from_customer='N' GROUP BY t3.parent_id) AND t2.created_by IN(" . $account_id_str . ")) AND t1.status='open'";

                $query = $DB1->query($sql);
                $row = $query->row();
                $final_return_array['admin_replied'] = $row->total_replied;
            }
        } else {
            $final_return_array = array('open' => 0, 'new' => 0, 'customer_replied' => 0);
            $sql = "SELECT count(*) total_open FROM tickets t1 WHERE t1.parent_id='0' AND t1.status='open'";
            $query = $DB1->query($sql);
            $row = $query->row();
            $final_return_array['open'] = $row->total_open;
            $sql = "SELECT count(*) total_new FROM tickets t1 WHERE t1.parent_id='0' AND t1.ticket_id NOT IN (SELECT DISTINCT parent_id FROM tickets WHERE parent_id!='0')";
            $query = $DB1->query($sql);
            $row = $query->row();
            $final_return_array['new'] = $row->total_new;
            //customer_replied
            //fetch all admin users             
            $sub_sql = "SELECT account_id FROM customers where account_type = 'ADMIN'";
            $sub_query = $this->db->query($sub_sql);
            if (!$sub_query) {
                $error_array = $this->db->error();
                throw new Exception($error_array['message']);
            }
            $user_access_id_name_array = array();
            if ($sub_query->row() > 0) {
                foreach ($sub_query->result_array() as $row) {
                    $user_access_id_name_array[] = $row['account_id'];
                }
            }
            $account_id_str = implode("','", $user_access_id_name_array);
            $account_id_str = "'" . $account_id_str . "'";

            //all tickets whose last reply is by customer(i.e., Not Admin users)
            $sql = "SELECT count(*) total_replied FROM tickets t1 WHERE t1.parent_id='0' AND t1.ticket_id IN (SELECT t2.parent_id FROM tickets t2 WHERE t2.ticket_id IN (SELECT MAX(t3.ticket_id) FROM tickets t3 WHERE t3.parent_id!='0' AND hide_from_customer='N' GROUP BY t3.parent_id) AND t2.created_by NOT IN(" . $account_id_str . ")) AND t1.status='open'";
            $query = $DB1->query($sql);
            $row = $query->row();
            $final_return_array['customer_replied'] = $row->total_replied;
        }
        return $final_return_array;
    }

    /* Ticket List */
    function get_data($order_by = '', $limit_to = '', $limit_from = '', $filter_data = array(), $option_param = array()) {
        $final_return_array = array();
        $ticket_id_array = $category_id_array = $category_id_ticket_id_mapping_array = array();
        try {
            $DB1 = $this->load->database('cdrdb', true);
            $sql_main = " FROM tickets t1 ";
            $sql_where = $sql_orderby = '';

            if (count($filter_data) > 0) {
                foreach ($filter_data as $key => $value) {
                    if ($value != '') {
                        if ($key == 'ticket_id' || $key == 'ticket_number' || $key == 'account_id' || $key == 'parent_id')
                            $sql_where .=" AND t1.$key ='" . $value . "' ";
                        elseif ($key == 'status') {
                            if ($value == 'new') {
                                $sql_where .=" AND t1.parent_id='0' AND t1.ticket_id NOT IN (SELECT DISTINCT parent_id FROM tickets WHERE parent_id!='0')";
                            } elseif ($value == 'customer_replied') {
                                //fetch all admin users
                                
                                $sub_sql = "SELECT account_id FROM customers where account_type = 'ADMIN'";
                                $sub_query = $this->db->query($sub_sql);
                                if (!$sub_query) {
                                    $error_array = $this->db->error();
                                    throw new Exception($error_array['message']);
                                }
                                $user_access_id_name_array = array();
                                if ($sub_query->row() > 0) {
                                    foreach ($sub_query->result_array() as $row) {
                                        $user_access_id_name_array[] = $row['account_id'];
                                    }
                                }
                                $account_id_str = implode("','", $user_access_id_name_array);
                                $account_id_str = "'" . $account_id_str . "'";

                                //all tickets whose last reply is by customer(i.e., Not Admin users)

                                $sql_where .=" AND t1.ticket_id IN (SELECT t2.parent_id FROM tickets t2 WHERE t2.ticket_id IN (SELECT MAX(t3.ticket_id) FROM tickets  t3 WHERE t3.parent_id!='0' AND hide_from_customer='N' GROUP BY t3.parent_id) AND t2.created_by NOT IN(" . $account_id_str . ")) AND t1.status='open'";
                            } else {
                                $sql_where .=" AND t1.$key ='" . $value . "' ";
                            }
                        } else
                            $sql_where .=" AND t1.$key LIKE '%" . $value . "%' ";
                    }
                }
            }

            if ($order_by != '') {
                $sql_orderby .=" ORDER BY t1.$order_by ";
            } else {
                $sql_orderby .=" ORDER BY t1.status ASC, t1.create_date DESC";
            }


            $sql = "SELECT SQL_CALC_FOUND_ROWS t1.* " . $sql_main . " WHERE 1 " . $sql_where . " " . $sql_orderby;


            $limit_from = intval($limit_from);

            if ($limit_to != '')
                $sql .=" LIMIT $limit_from, $limit_to";
            	//echo $sql;
            $query = $DB1->query($sql);

            if (!$query) {
                $error_array = $DB1->error();
                throw new Exception($error_array['message']);
            }

            $sql = "SELECT FOUND_ROWS() as total";
            $query_count = $DB1->query($sql);
            $row_count = $query_count->row();           
            $this->total_count = $row_count->total;

            foreach ($query->result_array() as $row) {
                $ticket_id = $row['ticket_id'];
                $category_id = $row['category_id'];
                $assigned_to = $row['assigned_to_id'];
                if (isset($option_param['category']) && $option_param['category'] == true) {//set default value
                    $row['category'] = array();
                }

                if (isset($option_param['last_post']) && $option_param['last_post'] == true) {//set default value
                    $row['last_post'] = array();
                }
                if (isset($option_param['replies']) && $option_param['replies'] == true) {//set default value
                    $row['replies'] = array();
                }

                if (isset($option_param['assigned_to']) && $option_param['assigned_to'] == true) {//set default value
                    $row['assigned_to'] = array();
                }
                if (isset($option_param['total_post']) && $option_param['total_post'] == true) {//set default value
                    $row['total_post'] = 0;
                }

                if (isset($option_param['attachment']) && $option_param['attachment'] == true) {//set default value
                    $row['attachment'] = array();
                }

                $final_return_array['result'][$ticket_id] = strip_slashes($row);
//               	echo '<pre>';
//                print_r($ticket_id_array);
//                echo '</pre>';
                $ticket_id_array[] = $ticket_id;
                $category_id_array[] = $category_id;
                $category_id_ticket_id_mapping_array[$category_id][] = $ticket_id;

                $assigned_to_array[] = $assigned_to;
                $assigned_to_ticket_id_mapping_array[$assigned_to][] = $ticket_id;
            }

            if (isset($option_param['total_post']) && $option_param['total_post'] == true && count($final_return_array['result']) > 0) {
                $ticket_id_str = implode("','", $ticket_id_array);
                $ticket_id_str = "'" . $ticket_id_str . "'";

                $sql = "SELECT parent_id, count(ticket_id) total_post FROM tickets  WHERE parent_id IN ($ticket_id_str) AND hide_from_customer='N' GROUP BY parent_id;";

               // echo $sql;
                $query = $DB1->query($sql);
                if (!$query) {
                    $error_array = $DB1->error();
                    throw new Exception($error_array['message']);
                }
                foreach ($query->result_array() as $row) {
                    $ticket_id = $row['parent_id'];
                    $total_post = $row['total_post'];
                    if (isset($final_return_array['result'][$ticket_id])) {
                        $final_return_array['result'][$ticket_id]['total_post'] = $total_post;
                    }
                }
            }
            if (isset($option_param['last_post']) && $option_param['last_post'] == true && count($final_return_array['result']) > 0) {//var_dump($ticket_id_array);
                $ticket_id_str = implode("','", $ticket_id_array);
                $ticket_id_str = "'" . $ticket_id_str . "'";

                $sql = "SELECT * FROM  tickets WHERE ticket_id IN ( SELECT MAX(ticket_id) FROM  tickets WHERE parent_id IN ($ticket_id_str) AND hide_from_customer='N' GROUP BY parent_id);";

               // echo $sql;
                $query = $DB1->query($sql);
                if (!$query) {
                    $error_array = $DB1->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $ticket_id = $row['parent_id'];
                    if (isset($final_return_array['result'][$ticket_id])) {
                        $final_return_array['result'][$ticket_id]['last_post'] = strip_slashes($row);
                    }
                }
            }

            /*
              $assigned_to_array[] =	$assigned_to;
              $assigned_to_ticket_id_mapping_array[$assigned_to][]=$ticket_id;
             */
            if (isset($option_param['assigned_to']) && $option_param['assigned_to'] == true && count($final_return_array['result']) > 0) {
                $assigned_to_str = implode("','", $assigned_to_array);
                $assigned_to_str = "'" . $assigned_to_str . "'";
                $sql = "SELECT assigned_to_id, assigned_to_name FROM ticket_assigned_to  WHERE assigned_to_id IN($assigned_to_str) ";
                $query = $DB1->query($sql);
                if (!$query) {
                    $error_array = $DB1->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $assigned_to_id = $row['assigned_to_id'];
                    if (isset($assigned_to_ticket_id_mapping_array[$assigned_to_id])) {
                        foreach ($assigned_to_ticket_id_mapping_array[$assigned_to_id] as $ticket_id) {
                            $final_return_array['result'][$ticket_id]['assigned_to'] = strip_slashes($row);
                        }
                    }
                }
            }




            if (isset($option_param['category']) && $option_param['category'] == true && count($final_return_array['result']) > 0) {
                $category_id_str = implode("','", $category_id_array);
                $category_id_str = "'" . $category_id_str . "'";
                $sql = "SELECT category_id, category_name FROM ticket_categories  WHERE category_id IN($category_id_str) ";
                $query = $DB1->query($sql);
                if (!$query) {
                    $error_array = $DB1->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $category_id = $row['category_id'];
                    if (isset($category_id_ticket_id_mapping_array[$category_id])) {
                        foreach ($category_id_ticket_id_mapping_array[$category_id] as $ticket_id) {
                            $final_return_array['result'][$ticket_id]['category'] = strip_slashes($row);
                        }
                    }
                }
            }

            if (isset($option_param['replies']) && $option_param['replies'] == true && count($final_return_array['result']) > 0) {
                $ticket_id_str = implode("','", $ticket_id_array);
                $ticket_id_str = "'" . $ticket_id_str . "'";
                $sql = "SELECT * FROM tickets WHERE parent_id IN ($ticket_id_str) ";
                if (isset($option_param['hide_from_customer']) && $option_param['hide_from_customer'] != '') {
                    $sql .= " AND hide_from_customer='" . $option_param['hide_from_customer'] . "'";
                }

                $query = $DB1->query($sql);
                if (!$query) {
                    $error_array = $DB1->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $parent_id = $row['parent_id'];
                    $ticket_id = $row['ticket_id'];
                    $final_return_array['result'][$parent_id]['replies'][$ticket_id] = strip_slashes($row);
                }

                /////////////
                if (isset($option_param['attachment']) && $option_param['attachment'] == true) {
                    $sql = "SELECT t.parent_id,ta.* FROM ticket_attachments ta INNER JOIN tickets t ON ta.ticket_id=t.ticket_id  WHERE t.parent_id IN ($ticket_id_str)";

                    $query = $DB1->query($sql);
                    if (!$query) {
                        $error_array = $DB1->error();
                        throw new Exception($error_array['message']);
                    }

                    foreach ($query->result_array() as $row) {
                        $parent_id = $row['parent_id'];
                        $ticket_id = $row['ticket_id'];
                        if (isset($final_return_array['result'][$parent_id]['replies'][$ticket_id])) {
                            $final_return_array['result'][$parent_id]['replies'][$ticket_id]['attachment'][] = strip_slashes($row);
                        }
                    }
                }
            }


            if (isset($option_param['attachment']) && $option_param['attachment'] == true && count($final_return_array['result']) > 0) {
                $ticket_id_str = implode("','", $ticket_id_array);
                $ticket_id_str = "'" . $ticket_id_str . "'";
                $sql = "SELECT * FROM ticket_attachments WHERE ticket_id IN($ticket_id_str) ";

                $query = $DB1->query($sql);
                if (!$query) {
                    $error_array = $DB1->error();
                    throw new Exception($error_array['message']);
                }

                foreach ($query->result_array() as $row) {
                    $ticket_id = $row['ticket_id'];
                    $final_return_array['result'][$ticket_id]['attachment'][] = strip_slashes($row);
                }
            }


            $final_return_array['status'] = 'success';
            $final_return_array['message'] = 'Tickets fetched successfully';
            
            return $final_return_array;
        } catch (Exception $e) {
            $final_return_array['status'] = 'failed';
            $final_return_array['message'] = $e->getMessage();
            return $final_return_array;
        }
    }

    /* Add reply */

    function add_reply($data) {
        try {
            $DB1 = $this->load->database('cdrdb', true);
            
            $data = add_slashes($data);
            

            $sql = "SELECT ticket_id FROM  tickets  WHERE ticket_id ='" . $data['parent_id'] . "'";
            $query = $DB1->query($sql);
            $existing_ticket_row = $query->row_array();
            if (!isset($existing_ticket_row)) {
                throw new Exception('Ticket Not Found');
            }


            $ticket_data_array = array();

            $ticket_data_array['subject'] = $data['subject'];
            $ticket_data_array['content'] = $data['content'];
            $ticket_data_array['created_by'] = $data['created_by'];
            $ticket_data_array['created_by_name'] = $data['created_by_name'];
            $ticket_data_array['parent_id'] = $data['parent_id'];
            $ticket_data_array['create_date'] = date('Y-m-d H:i:s');
            $ticket_data_array['created_by_ip'] = getUserIP();

            if (isset($data['hide_from_customer']))
                $ticket_data_array['hide_from_customer'] = $data['hide_from_customer'];


            $DB1->trans_begin();
            if (count($ticket_data_array) > 0) {
                $str = $DB1->insert_string('tickets', $ticket_data_array);
                $result = $DB1->query($str);
                if (!$result) {
                    $error_array = $DB1->error();
                    throw new Exception($error_array['message']);
                }
                $this->ticket_id = $DB1->insert_id();
            }


            if (isset($data['attachment']) && count($data['attachment']) > 0) {
                foreach ($data['attachment'] as $attachment_array) {
                    $attachment_data_array = array();
                    $attachment_data_array['ticket_id'] = $this->ticket_id;
                    $attachment_data_array['file_name'] = $attachment_array['file_name'];
                    $attachment_data_array['file_name_display'] = $attachment_array['file_name_display'];
                    $str = $DB1->insert_string('ticket_attachments', $attachment_data_array);
                    $result = $DB1->query($str);
                    if (!$result) {
                        $error_array = $DB1->error();
                        throw new Exception($error_array['message']);
                    }
                }
            }


            /////////////////		
            if ($DB1->trans_status() === FALSE) {
                $error_array = $DB1->error();
                $DB1->trans_rollback();
                return $error_array['message'];
            } else {
                $DB1->trans_commit();
            }
            return true;
        } catch (Exception $e) {
            $DB1->trans_rollback();
            return $e->getMessage();
        }
    }

    /* Update ticket */

    function update($data) {
        try {
            $DB1 = $this->load->database('cdrdb', true);
            $ticket_data_array = array();

            if (isset($data['ticket_id']))
                $ticket_id = $data['ticket_id'];
            else
                return 'ID missing';

            if (isset($data['subject']))
                $ticket_data_array['subject'] = $data['subject'];
            if (isset($data['content']))
                $ticket_data_array['content'] = $data['content'];
            if (isset($data['category_id']))
                $ticket_data_array['category_id'] = $data['category_id'];
            if (isset($data['assigned_to_id']))
                $ticket_data_array['assigned_to_id'] = $data['assigned_to_id'];
            if (isset($data['status']))
                $ticket_data_array['status'] = $data['status'];

            $DB1->trans_begin();
            if (count($ticket_data_array) > 0) {
                $where = "ticket_id='" . $data['ticket_id'] . "'";
                $str = $DB1->update_string('tickets', $ticket_data_array, $where);
                $result = $DB1->query($str);
                if (!$result) {
                    $error_array = $DB1->error();
                    throw new Exception($error_array['message']);
                }
            }

            if ($DB1->trans_status() === FALSE) {
                $error_array = $DB1->error();
                $DB1->trans_rollback();
                return $error_array['message'];
            } else {
                $DB1->trans_commit();
                set_activity_log($log_data_array);
            }

            return true;
        } catch (Exception $e) {
            $DB1->trans_rollback();
            return $e->getMessage();
        }
    }

    function get_data_total_count() {
        return $this->total_count;
    }

}
