<?php

class MY_Form_validation extends CI_Form_validation {

	 public $CI;
    function run($module = '', $group = '') {
        (is_object($module)) AND $this->CI = &$module;
        return parent::run($group);
    }

} 