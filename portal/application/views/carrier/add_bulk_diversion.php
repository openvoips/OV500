<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>  



<div class="container-fluid">
    <div class="block-header">
        <h2>Bulk Diversion Configuration </h2>
        <ul class="nav navbar-right panel_toolbox">
            <li> <a href="<?php echo base_url('carriers/diversion/') . param_encrypt($carrier_id); ?>"><button class="btn btn-primary" type="button">Back To Diversion Listing page</button></a></li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">






                    <form action="<?php echo base_url('carriers/addbulkdiversion/') . param_encrypt($carrier_id); ?>" method="post" name="add_diversion_form" id="add_diversion_form" data-parsley-validate class="form-horizontal form-label-left" enctype="multipart/form-data">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">
                        <input type="hidden" name="carrier_id" value="<?php echo $carrier_id; ?>">

                        <div class="x_panel">
                            <div class="x_content">

                                <div class="form-group" id="uploadFile" >
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12">Upload CSV File <span class="required">*</span></label>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <input name="file_diversion_number" type="file" />
                                    </div>
                                </div>  



                                <div class="form-group" id="sampleFile" >
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12">Download Sample File </label>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <a href="<?php echo base_url('download/sample/' . param_encrypt('diversion_number_sample')); ?>"><button type="button" class="btn btn-dark btn-sm">Sample Diversion Number</button></a>

                                    </div>                                
                                </div>  

                                <div class="form-group"  id="sampletext"  >
                                    <div class="col-md-12 col-sm-12 col-xs-12 text-primary">
                                        <small>* Use Diversion number sample sheet for Diversion number configuration.</small>
                                    </div>
                                </div>
                                <div class="ln_solid"></div>
                                <div class="form-group"   >
                                    <div class="col-md-8 col-sm-12 col-xs-12 col-md-offset-4">
                                        <button type="button" id="btnSave" class="btn btn-success">Upload</button>

                                    </div>
                                </div>
                            </div>

                        </div>

                    </form>

                </div>


            </div>
        </div>
    </div>
    <div class="block-header">
        <h2>Bulk Diversion Configuration </h2>
        <ul class="nav navbar-right panel_toolbox">
            <li> <a href="<?php echo base_url('carriers/diversion/') . param_encrypt($carrier_id); ?>"><button class="btn btn-primary" type="button">Back To Diversion Listing page</button></a></li>
        </ul>
    </div>
</div>

<script type="text/javascript">
    window.ParsleyValidator
            .addValidator('fileextension', function (value, requirement) {
                var fileExtension = value.split('.').pop();

                return fileExtension === requirement;
            }, 32)
            .addMessage('en', 'fileextension', 'The extension does not match the required');


</script>
<script>
    $('#btnSave, #btnSaveClose').click(function () {

        $('#add_diversion_form').parsley().reset();

        var is_ok = $("#add_diversion_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            if (is_ok === true)
            {
                //alert("ok");
                $("#add_diversion_form").submit();
            }
        } else
        {
            $('#add_diversion_form').parsley().validate();
        }

    })
</script>