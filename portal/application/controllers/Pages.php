<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends MY_Controller {

  public function __construct() {
		parent::__construct();
		$this->load->model('sitesetup_mod');
  }

  public function index($id=-1) 
	{
	
		if($id==-1)
		{
		//redirtect
		}
	
		
		if (check_is_loggedin()) {
            $this->session->set_flashdata('err_msgs', 'Sorry, The Page You Are Looking For Is Missing or Replaced.');
			redirect(site_url('dashboard'), 'location', '301');
        }
		else {
			//$this->session->set_flashdata('err_msgs', $err_msgs);
			redirect(site_url('login'), 'location', '301');
		}

		

	  	$id =  $this->uri->segment(1);
    	
		
		
		
  }



}
