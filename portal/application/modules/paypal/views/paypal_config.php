<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2021 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 2.0.0
// License https://www.gnu.org/licenses/agpl-3.0.html
//
//
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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
//echo '<pre>';
//print_r($data);
//echo '</pre>';
if(isset($data['credentials']))
	$data2 = json_decode($data['credentials'],true);
else
	$data2 =array('business'=>'');
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Paypal Configuration Management</h2>            
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="x_panel">
        <div class="x_content"> 
            <form class="block-content form-horizontal " data-parsley-validate id="add_form" name="add_form" ction="" method="post" >
                <input type="hidden" name="action" value="OkSaveData">
              
                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" >Email ID<span class="required">*</span></label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <input type="text" name="business" id="business" data-parsley-required="" value="<?php echo $data2['business']; ?>" class="form-control col-md-7 col-xs-12">
                    </div>
                </div>

				<?php
				if($testingMode===true)
				{
					echo '<div class="form-group">
							<label class="control-label col-md-4 col-sm-3 col-xs-12" >Current Mode</label>
							<div class="control-label col-md-2 col-sm-6 col-xs-12">Testing</div>
						</div>';
				}?>
                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" >Status</label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                         <div class="radio">
                             <label><input type="radio" name="status" id="status1" value="Y" <?php if ($data['status'] == 'Y') echo 'checked'; ?>  checked /> Active</label>
                                    <label> <input type="radio" name="status" id="status2" value="N"  <?php if ($data['status'] == 'N') echo 'checked'; ?>/> Inactive</label>
                                </div> 
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" ></label>
                    <div class="col-md-8 col-sm-6 col-xs-12 searchBar ">
                        <button type="button" id="btnSave" class="btn btn-success">Save</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-9 col-sm-6 col-xs-6 " style="color:red;"><strong> Note:-</strong> Paypal implementation is based on IPN. So, Merchent account must be enabled  <a href="https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNSetup/" target="_blank">IPN on Paypal</a> account.</label>
                   
                </div>
            </form> 

            <div class="clearfix"></div>           
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Paypal Configuration Management</h2>            
            <div class="clearfix"></div>
        </div>

    </div>
</div>    
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script> 