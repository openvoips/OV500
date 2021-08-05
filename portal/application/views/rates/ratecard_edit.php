<div class="">
    <div class="clearfix"></div>    
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Ratecard Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('ratecard') ?>"><button class="btn btn-danger" type="button" >Back to Ratecard Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Upload Rates</h2>
                            <ul class="nav navbar-right panel_toolbox">

                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <form action="<?php echo base_url(); ?>ratecard/editRC/<?php echo param_encrypt($data['ratecard_id']); ?>" method="post" name="upload_form" id="upload_form" data-parsley-validate class="form-horizontal form-label-left" enctype="multipart/form-data">
                                <input type="hidden" name="button_action" id="button_action" value="">
                                <input type="hidden" name="action" value="OkUploadData">    
                                <input type="hidden" name="frm_key" value="<?php echo $data['ratecard_id']; ?>"/>
                                <input type="hidden" name="frm_id" value="<?php echo $data['id']; ?>"/>
                                <input type="hidden" name="frm_ratecard_for" value="<?php echo $data['ratecard_for']; ?>"/> 

                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12">Upload From <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <?php
                                        $options = array(
                                            'C' => 'Copy rates from existing ratecard',
                                            'U' => 'Upload from file (csv)'
                                        );
                                        echo form_dropdown('uploadType', $options, set_value('uploadType', true, $options), array('class' => 'form-control col-md-7 col-xs-12', 'id' => 'uploadType'));

                                        if (set_value('uploadType') == 'U') {
                                            $uploadRatecard_display = 'style="display:none;"';
                                            $uploadFile_display = 'style="display:block;"';
                                        } else {
                                            $uploadRatecard_display = 'style="display:block;"';
                                            $uploadFile_display = 'style="display:none;"';
                                        }
                                        ?>

                                    </div>
                                </div>							
                                <div class="form-group" id="uploadRatecard" <?php echo $uploadRatecard_display; ?>>
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12">Ratecard <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="frm_card" id="frm_card" class="combobox form-control data-search-field" data-parsley-required="">
                                            <option value="">Select Ratecard</option>
                                            <?php
                                            for ($i = 0; $i < $ratecard_data['total']; $i++) {
                                                if ($ratecard_data['result'][$i]['ratecard_id'] != $data['ratecard_id']):
                                                    ?>								
                                                    <option value="<?php echo $ratecard_data['result'][$i]['ratecard_id']; ?>" <?php if ($data['ratecard_id'] == $ratecard_data['result'][$i]['ratecard_id']) echo 'selected'; ?>><?php echo $ratecard_data['result'][$i]['ratecard_name'] . ' (' . $ratecard_data['result'][$i]['currency_name'] . ') [' . $ratecard_data['result'][$i]['ratecard_id'] . ']'; ?></option>
                                                    <?php
                                                endif;
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>  
                                <div class="form-group" id="uploadFile" <?php echo $uploadFile_display; ?>>
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12">File <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input name="file" type="file" />
                                    </div>
                                </div>  



                                <div class="form-group" id="sampleFile" <?php echo $uploadFile_display; ?>>
                                    <label class="control-label col-md-5 col-sm-3 col-xs-12">Download Sample File </label>
                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                        <a href="<?php echo base_url('download/sample/' . param_encrypt('rate_incoming')); ?>"><button type="button" class="btn btn-dark btn-sm">DID Incoming Rate</button></a>
                                        <a href="<?php echo base_url('download/sample/' . param_encrypt('rate_outgoing')); ?>"><button type="button" class="btn btn-dark btn-sm">Outgoing Rate</button></a>
                                    </div>                                
                                </div>  

                                <div class="form-group"  id="sampletext" <?php echo $uploadFile_display; ?> >
                                    <div class="col-md-12 col-sm-12 col-xs-12 text-primary">
                                        <small>* Use incoming rate card sample sheet for Incoming rate card and Outgoing rate card sample sheet for outgoing rate card. Check the rate card type in the rate card configuration.</small>
                                    </div>
                                </div>



                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12">Delete Existing Rates</label>
                                    <div class="col-md-8 col-sm-6 col-xs-12 text-left">
                                        <div class="checkbox">
                                            <label><input value="" name="frm_del" id="frm_del" type="checkbox"> Delete all prefix with rates</label>
                                        </div>							
                                    </div>
                                </div>
                                <div id="rate_conf">
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Rates</label>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <?php
                                            $options = array(
                                                1 => 'No-Action',
                                                2 => 'Multiply',
                                                3 => 'Addition',
                                                4 => 'Replace'
                                            );
                                            echo form_dropdown('frm_action_rate', $options, set_value('frm_action_rate', true, $options), array('class' => 'form-control', 'id' => 'frm_action_rate'));
                                            ?>
                                        </div>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <input class="form-control" type="text" name="frm_val_rate" id="frm_val_rate" value="<?php echo set_value('frm_val_rate'); ?>">							
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Connection</label>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <?php
                                            $options = array(
                                                1 => 'No-Action',
                                                2 => 'Multiply',
                                                3 => 'Addition',
                                                4 => 'Replace'
                                            );
                                            echo form_dropdown('frm_action_connect', $options, set_value('frm_action_connect', true, $options), array('class' => 'form-control', 'id' => 'frm_action_connect'));
                                            ?>
                                        </div>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <input class="form-control" type="text" name="frm_val_connect" id="frm_val_connect" value="<?php echo set_value('frm_val_connect'); ?>">							
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Minimal Time </label>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <?php
                                            $options = array(
                                                1 => 'No-Action',
                                                2 => 'Multiply',
                                                3 => 'Addition',
                                                4 => 'Replace'
                                            );
                                            echo form_dropdown('frm_action_min', $options, set_value('frm_action_min', true, $options), array('class' => 'form-control', 'id' => 'frm_action_min'));
                                            ?>
                                        </div>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <input class="form-control" type="text" name="frm_val_min" id="frm_val_min" value="<?php echo set_value('frm_val_min'); ?>">								
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Resolution Time </label>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <?php
                                            $options = array(
                                                1 => 'No-Action',
                                                2 => 'Multiply',
                                                3 => 'Addition',
                                                4 => 'Replace'
                                            );
                                            echo form_dropdown('frm_action_res', $options, set_value('frm_action_res', true, $options), array('class' => 'form-control', 'id' => 'frm_action_res'));
                                            ?>
                                        </div>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <input class="form-control" type="text" name="frm_val_res" id="frm_val_res" value="<?php echo set_value('frm_val_res'); ?>">									
                                        </div>
                                    </div>
                                </div>
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                        <?php if (check_account_permission('ratecard', 'upload')): ?>			
                                            <button type="button" id="btnUpload" class="btn btn-success">Upload</button>
                                        <?php endif; ?>		
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Ratecard (EDIT)</h2>
                            <ul class="nav navbar-right panel_toolbox">

                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <br />         
                            <form action="<?php echo base_url(); ?>ratecard/editRC/<?php echo param_encrypt($data['ratecard_id']); ?>" method="post" name="edit_form" id="edit_form" data-parsley-validate class="form-horizontal form-label-left">
                                <input type="hidden" name="button_action" id="button_action" value="">
                                <input type="hidden" name="action" value="OkSaveData">    
                                <input type="hidden" name="frm_key" value="<?php echo $data['ratecard_id']; ?>"/>
                                <input type="hidden" name="frm_id" value="<?php echo $data['ratecard_id']; ?>"/> 
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Ratecard Name <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_name" id="frm_name" value="<?php echo $data['ratecard_name']; ?>"  data-parsley-required="" data-parsley-alphanumspace="" data-parsley-length="[5, 30]" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Ratecard Code <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <input type="text" name="frm_abbr" id="frm_abbr" value="<?php echo $data['ratecard_id']; ?>"  disabled="disabled" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <?php if (!check_logged_user_group(array('RESELLER'))) : ?>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Who can Use <span class="required">*</span></label>
                                        <div class="col-md-8 col-sm-6 col-xs-12">
                                            <select name="frm_type" id="frm_type" class="form-control data-search-field" disabled="disabled">
                                                <option value="carrier"  <?php if (strtolower($data['ratecard_type']) == 'carrier') echo 'selected'; ?>>Carrier</option>
                                                <option value="CUSTOMER"  <?php if (strtolower($data['ratecard_type']) == 'customer') echo 'selected'; ?>>Customer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Currency <span class="required">*</span></label>
                                        <div class="col-md-8 col-sm-6 col-xs-12">
                                            <select name="frm_currency" id="frm_currency" class="form-control data-search-field" disabled="disabled">
                                                <option value="">Select Route</option>
                                                <?php for ($i = 0; $i < count($currency_data); $i++) { ?>								
                                                    <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if ($data['ratecard_currency_id'] == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['symbol'] . " - " . $currency_data[$i]['name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <input type="hidden" name="frm_type" id="frm_type" value="<?php echo $data['ratecard_type']; ?>" data-parsley-required="" class="form-control" />
                                    <input type="hidden" name="frm_currency" id="frm_currency" value="<?php echo $data['ratecard_currency_id']; ?>" data-parsley-required="" class="form-control" />
                                <?php endif; ?>


                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Usage For <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="ratecard_for" id="ratecard_for" class="form-control data-search-field" disabled="disabled">
                                            <option value="INCOMING"  <?php if ($data['ratecard_for'] == 'INCOMING') echo 'selected'; ?>>DID Incoming calls</option>
                                            <option value="OUTGOING"  <?php if ($data['ratecard_for'] == 'OUTGOING') echo 'selected'; ?>>Outgoing Calls</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                        <!--<button type="button" id="btnSave" class="btn btn-success">Save</button>-->
                                        <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to listing Page</button>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Used in Tariff(s)</h2>
                            <ul class="nav navbar-right panel_toolbox">

                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">

                            <div class="table-responsive">
                                <table class="table table-striped jambo_table bulk_action table-bordered">
                                    <tbody>
                                        <?php
                                        if (count($data_tariff) > 0) {
                                            for ($i = 0; $i < count($data_tariff); $i++) {
                                                if ($data_tariff[$i]['status'] == '1')
                                                    $status = '<span class="label label-success">Active</span>';
                                                else
                                                    $status = '<span class="label label-danger">Inactive</span>';
                                                ?>
                                                <tr>
                                                    <td><?php echo $data_tariff[$i]['tariff_name'] . " (" . $data_tariff[$i]['tariff_id'] . ")"; ?></td>
                                                </tr>

                                                <?php
                                            }
                                        }else {
                                            ?>
                                            <tr>

                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>		

    </div>




    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Ratecard Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('ratecard') ?>"><button class="btn btn-danger" type="button" >Back to Ratecard Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
</div>    
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>
<script>
    $(document).ready(function () {
        $('.combobox').combobox()
    });
</script>

<script>
    $('#uploadType').change(function () {
        if (this.value == 'U') {
            $('#sampleFile').show();
            $('#uploadFile').show();
            $('#sampletext').show();
            $('#uploadRatecard').hide();
            $('#rate_conf').hide();
            $('#frm_card').removeAttr("data-parsley-required");
        } else {
            $('#sampleFile').hide();
            $('#uploadFile').hide();
            $('#sampletext').hide();
            $('#uploadRatecard').show();
            $('#rate_conf').show();
        }
    });

    $('#btnUpload').click(function () {
        var is_ok = $("#upload_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            $('#button_action').val('save');
            $("#upload_form").submit();
        } else
        {
            $('#upload_form').parsley().validate();
        }
    });
</script>