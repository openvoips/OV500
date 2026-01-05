<?php
if (isset($data['credentials']))
    $data2 = json_decode($data['credentials'], true);
else
    $data2 = array('business' => '');
?> 




<div class="container-fluid">
    <div class="block-header">
        <h2>Paypal Configuration Management</h2>       
        <ul class="nav navbar-right panel_toolbox">
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">      

                    <form class="block-content form-horizontal " data-parsley-validate id="add_form" name="add_form" ction="" method="post" >
                        <input type="hidden" name="action" value="OkSaveData">

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Email ID<span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <input type="text" name="business" id="business" data-parsley-required="" value="<?php echo $data2['business']; ?>" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <?php
                        if ($testingMode === true) {
                            echo '<div class="form-group">
							<label class="control-label col-md-4 col-sm-3 col-xs-12" >Current Mode</label>
							<div class="control-label col-md-2 col-sm-6 col-xs-12">Testing</div>
						</div>';
                        }
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Status</label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <input type="radio" class="with-gap" name="status" id="status1" value="Y" <?php if ($data['status'] == 'Y') echo 'checked'; ?>  checked /> <label for="status1" >Active</label>
                                    <input type="radio" class="with-gap" name="status" id="status2" value="N"  <?php if ($data['status'] == 'N') echo 'checked'; ?>/><label for="status2"> Inactive</label>
                                </div> 
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" ></label>
                            <div class="col-md-8 col-sm-6 col-xs-12 searchBar ">
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <label class="control-label col-md-12 col-sm-3 col-xs-12" >N.B.: <a href="https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNSetup/" target="_blank">IPIN on Paypal</a> need to be enabled</label>

                        </div>
                    </form> 

                    <div class="clearfix"></div>           
                </div>
            </div>
            <div class="block-header">
                <h2>Paypal Configuration Management</h2>       
                <ul class="nav navbar-right panel_toolbox">
                </ul>
            </div>
        </div>   
    </div>
</div>  
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script> 