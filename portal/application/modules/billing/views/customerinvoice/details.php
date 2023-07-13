<!--
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
-->
<?php 
if (check_logged_user_group(array('CUSTOMER'))) 
 {
     $title='Invoice';
 }
 else {
	$title='Customer Invoice';
}
?>
<div class="">
    <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2><?php echo $title;?></h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('Billing/customerinvoice') ?>"><button class="btn btn-danger" type="button" >Back to <?php echo $title;?> Listing Page</button></a> </li>
                <li><a href="<?php echo base_url('Billing/customerinvoicedownload/' . param_encrypt($customerinvoice_data['invoice_id'])) ?>" target="_blank"><button class="btn btn-success" type="button" >Download</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12"> 
        <div class="x_panel">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <!--<div class="x_title">
                    <h2>Customer Invoice</h2>
                    <div class="clearfix"></div>
                </div>-->
                <div class="x_content">

					<?php $this->load->view('invoicetemplate/template1'); //,$data ?>
                   

                  
                  
                    <br />

                </div>


            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="ln_solid"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2><?php echo $title;?></h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('Billing/customerinvoice') ?>"><button class="btn btn-danger" type="button" >Back to <?php echo $title;?> Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

</div>
<?php
/*ddd($sdr_data);
ddd($invoice_config);
ddd($customerinvoice_data);*/
?>