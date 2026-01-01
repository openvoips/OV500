<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$RandomCLI_data = $data;
?>


<div class="container-fluid">
    <div class="block-header">
        <h2>Carrier RandomCLI(EDIT) Configuration</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url() . 'carriers/edit/' . param_encrypt($data['carrier_id']); ?>/<?php echo $active_tab; ?>"><button class="btn btn-primary" type="button" >Back to Carrier Edit Page</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">
                    <form action="" method="post" name="carrier_form" id="carrier_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="id" value="<?php echo $RandomCLI_data['id']; ?>"/>
                        <input type="hidden" name="carrier_id" value="<?php echo $RandomCLI_data['carrier_id']; ?>"/>    
                        <input type="hidden" name="carrier_key" value="<?php echo $RandomCLI_data['carrier_id']; ?>"/>                         <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier <span class="required">*</span>          </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="carrier_name" id="carrier_name_display" value="<?php echo $RandomCLI_data['carrier_id'] . ' (' . $RandomCLI_data['carrier_name'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">CLI Rule Name<span class="required">*</span>            </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="clirule_name" id="clirule_name" value="<?php echo $RandomCLI_data['clirule_name']; ?>" data-parsley-required="" data-parsley-minlength="4" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Destination Prefix <span class="required">*</span>            </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="destination_prefix" id="destination_prefix" value="<?php echo $RandomCLI_data['destination_prefix']; ?>" data-parsley-required="" data-parsley-minlength="1" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Random CLI Starting with<span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="cli_fixprefix" id="cli_fixprefix" value="<?php echo $RandomCLI_data['cli_fixprefix']; ?>" data-parsley-required="" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Random CLI Suffix length</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="cli_length" id="cli_length" value="<?php echo $RandomCLI_data['cli_length']; ?>" data-parsley-required="" data-parsley-type="digits"  data-parsley-range="[1, 100]" class="form-control col-md-7 col-xs-12">

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <input type="radio" class="with-gap" name="cli_status" id="status1" value="1"  <?php if ($RandomCLI_data['cli_status'] == 1) { ?> checked="checked" <?php } ?> /><label for="status1"> Active</label>

                                    <input type="radio" class="with-gap" name="cli_status" id="status0" value="0" <?php if ($RandomCLI_data['cli_status'] == 0) { ?> checked="checked" <?php } ?> /> <label for="status0">Inactive</label>
                                </div>

                            </div>
                        </div>





                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-12 col-sm-12 col-xs-12 col-md-offset-6">

                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go back to Edit Page</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="block-header">
        <h2>Carrier RandomCLI(EDIT) Configuration</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url() . 'carriers/edit/' . param_encrypt($data['carrier_id']); ?>/<?php echo $active_tab; ?>"><button class="btn btn-primary" type="button" >Back to Carrier Edit Page</button></a> </li>
        </ul>
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
        //auth_type_change();


    });

</script>
