<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
// echo '<pre>';print_r($data);echo '</pre>';
?>
<div class="">
    <div class="clearfix"></div>

    <div class="col-md-12 col-sm-12 col-xs-12 right">       
        <div class="x_title">
            <h2>Customer Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php $tab_index = 0;
echo base_url('customers') . '/edit/' . param_encrypt($data['account_id']); ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Edit Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Bundle & Package(ADD)</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br />
                <form action="" method="post" name="carrier_form" id="carrier_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="button_action" id="button_action" value="">
                    <input type="hidden" name="action" value="OkSaveData"> 
                    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Account Code </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="account_name_display" id="account_name_display" value="<?php echo $data['account_id'] . ' (' . $data['name'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Bundle & Package <span class="required">*</span> </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="bundle_package_id" id="bundle_package_id" class="form-control col-md-7 col-xs-12">
                                <?php
                                if (count($bundle_data) > 0) {
                                    foreach ($bundle_data as $bundle_row) {
                                        echo '<option value="' . $bundle_row['bundle_package_id'] . '">' . $bundle_row['bundle_package_name'] . ' (' . $bundle_row['bundle_package_id'] . ')</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
		  <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Number of Packages<span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input name="no_of_package" id="no_of_package" class="form-control col-md-7 col-xs-12" value="<?php echo set_value('no_of_package','1'); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Description</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <textarea name="bundle_package_desc" id="bundle_package_desc" class="form-control col-md-7 col-xs-12"> <?php echo set_value('bundle_package_desc'); ?></textarea>
                        </div>
                    </div>



                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                            <!--<a href="<?php echo base_url('customers') . '/edit/' . param_encrypt($data['account_id']); ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
                            <!--<button type="button" id="btnSave" class="btn btn-success">Save</button>-->
                            <button type="button" id="btnSaveClose" class="btn btn-info">Assign Bundle & Go Back to Edit Page</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Customer Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li><a href="<?php echo base_url('customers') . '/edit/' . param_encrypt($data['account_id']); ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Edit Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>

</div>    
<script>


    $('#btnSave, #btnSaveClose').click(function () {
        var is_ok = $("#carrier_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            if (is_ok === true)
            {
                //alert('ok');
                $("#carrier_form").submit();
            }
        } else
        {
            $('#carrier_form').parsley().validate();
        }
    })



    $(document).ready(function () {


    });

</script>
<?php //echo '<pre>';print_r($bundle_data);echo '</pre>';
?>