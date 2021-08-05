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

class MY_DB_mysqli_driver extends CI_DB_mysqli_driver 
{
    /*
    |--------------------------------------------------------------------------
    | DB_mysqli_driver Class
    |--------------------------------------------------------------------------
    |
    | This class extends DB_mysqli_driver class of system for perform user audit
    | trails of the application.
    */
    private $CI;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->CI =& get_instance();
        $this->CI->config->load('trail');
    }

    /**
     * Insert
     *
     * Compiles an insert string and runs the query
     *
     * @param	string	the table to insert data into
     * @param	array	an associative array of insert values
     * @param	bool	$escape	Whether to escape values and identifiers
     * @return	bool	TRUE on success, FALSE on failure
     */
    public function insert($table = '', $set = NULL, $escape = NULL)
    {
        $status = parent::insert($table , $set, $escape);
        $this->trail($status,'insert', $table, $set);
       
        return $status;
    }

    /**
     * Insert_Batch
     *
     * Compiles batch insert strings and runs the queries
     *
     * @param	string	$table	Table to insert into
     * @param	array	$set 	An associative array of insert values
     * @param	bool	$escape	Whether to escape values and identifiers
     * @return	int	Number of rows inserted or FALSE on failure
     */
    public function insert_batch($table, $set = NULL, $escape = NULL, $batch_size = 100)
    {
        $affected_rows = parent::insert_batch($table , $set, $escape, $batch_size);
        $this->trail($affected_rows,'insert', $table, $set);

        return $affected_rows;
    }

    /**
     * UPDATE
     *
     * Compiles an update string and runs the query.
     *
     * @param	string	$table
     * @param	array	$set	An associative array of update values
     * @param	mixed	$where
     * @param	int	$limit
     * @return	bool	TRUE on success, FALSE on failure
     */
    public function update($table = '', $set = NULL, $where = NULL, $limit = NULL)
    {
        $condition = isset($this->qb_where) ? $this->qb_where : ""; // Temporary hold the where condition

        // Read current values as previous values
        $previous_values = null;

        if(empty($where))
            $query = $this->get($table);
        else
            $query = $this->get_where($table, $where);

        $previous_values = $query->row_array();

        $query->free_result();

        $this->qb_where = $condition; // reset where condition

        $status= parent::update($table, $set, $where, $limit);
        $this->trail($status,'update', $table, $set, $previous_values);

        return $status;
    }

    /**
     * Delete
     *
     * Compiles a delete string and runs the query
     *
     * @param	mixed	the table(s) to delete from. String or array
     * @param	mixed	the where clause
     * @param	mixed	the limit clause
     * @param	bool
     * @return	mixed
     */
    public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE)
    {
        $condition = isset($this->qb_where) ? $this->qb_where : ''; // Temporary hold the where condition

        // Read current values as previous values
        $previous_values = null;

        if(empty($where))
            $query = $this->get($table);
        else
            $query = $this->get_where($table, $where);

        $previous_values = $query->row_array();

        $query->free_result();

        $this->qb_where = $condition; // reset where condition

        if($this->CI->config->item('sess_save_path') == $table && $where == ''){
            $where = "id = '{" . $this->CI->session->session_id . "}'";
        }

        $status= parent::delete($table, $where, $limit, $reset_data);
        $this->trail($status,'delete', $table, $where, $previous_values);

        return $status;
    }

    /**
     * Save user audit trail data.
     *
     * @param $status
     * @param $event
     * @param $table
     * @param null $set
     * @param null $previous_values
     * @return bool|int
     */
    public function trail($status, $event, $table, $set = NULL, $previous_values = NULL)
    {
        //return without save resource
        if(!$status) return 1;  // event not performed
        if(!$this->CI->config->item('audit_enable')) return 1; // trail not enabled
        if($event === 'insert' && !$this->CI->config->item('track_insert')) return 1; // insert tracking not enabled
        if($event === 'update' && !$this->CI->config->item('track_update')) return 1; // update tracking not enabled
        if($event === 'delete' && !$this->CI->config->item('track_delete')) return 1; // delete tracking not enabled
        if(in_array($table, $this->CI->config->item('not_allowed_tables'))) return 1; // table tracking not allowed


        if($event == 'update') {
            $this->diff_on_update($previous_values, $set);
            //data has not been update
            if(empty($previous_values) && empty($set))
                return 1;
        }

        $old_value = null;
        if(!empty($previous_values)) $old_value = json_encode($previous_values);

        $new_value = json_encode($set); // For delete event it stores where condition

        return parent::insert('user_audit_trails' ,
            [
                'user_id' => $this->CI->auth->userID,
                'event' => $event,
                'table_name' => $table,
                'old_values' => $old_value,
                'new_values' => $new_value,
                'url' => $this->CI->uri->ruri_string(),
                'ip_address' => $this->CI->input->ip_address(),
                'user_agent' => $this->CI->input->user_agent(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Find out the difference of values.
     *
     * @param $old_value
     * @param $new_value
     */
    public function diff_on_update(&$old_value, &$new_value)
    {
        $old = [];
        $new = [];
        foreach($new_value as $key => $val) {
            if(isset($new_value[$key])) {
                if(isset($old_value[$key])) {
                    if($new_value[$key] != $old_value[$key]) {
                        $old[$key] = $old_value[$key];
                        $new[$key] = $new_value[$key];
                    }
                } else {
                     $old[$key] = '';
                     $new[$key] = $new_value[$key];
                }
            }
        }

        $old_value = $old;
        $new_value = $new;
    }
}
