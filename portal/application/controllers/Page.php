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

defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends CI_Controller {
    
	function __construct()
	{
		parent::__construct();				
		$this->load->model('sitesetup_mod');
	}
	
	public function index()
	{
		$page_code = 'page';
		$data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
		
		
		if(!check_is_loggedin())
		{//not looged in
			$this->load->view('404',$data); // pass the $data		
		}
		else
		{
		//loggedin
			$this->load->view('basic/header',$data);
			$this->load->view('basic/404', $data);
			$this->load->view('basic/footer', $data);
		
		}
		//////////////////////////////////
	
					
							
	}
}