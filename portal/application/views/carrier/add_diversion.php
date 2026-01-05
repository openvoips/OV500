<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>  





<div class="container-fluid">
    <div class="block-header">
        <h2>Diversion(ADD) Configuration </h2>
        <ul class="nav navbar-right panel_toolbox">
            <li> <a href="<?php echo base_url('carriers/diversion/') . param_encrypt($carrier_id); ?>"><button class="btn btn-primary" type="button">Back To Diversion Listing page</button></a></li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">


                    <form action="<?php echo base_url('carriers/adddiversion/') . param_encrypt($carrier_id); ?>" method="post" name="add_voip_form" id="add_voip_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">
                        <input type="hidden" name="carrier_id" value="<?php echo $carrier_id; ?>">

                        <div class="x_panel">
                            <div class="x_content">
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12" >Diversion Number </label>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <input type="text" name="diversion_number" id="diversion_number" value="<?php echo set_value('diversion_number'); ?>" data-parsley-required="" data-parsley-minlength="4"  class="form-control col-md-7 col-xs-12" placeholder="Diversion Number">
                                    </div>
                                </div>

                                <div class="">
                                    <div class="col-md-8 col-sm-12 col-xs-12 col-md-offset-4">
                                        <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                        <button type="button" id="btnSaveClose" class="btn btn-info">Save & Close</button>

                                    </div>
                                </div>
                            </div>

                        </div>

                    </form>

                </div>
                <div class="clearfix"></div> <div class="clearfix"></div> <div class="clearfix"></div>

            </div>
        </div>
    </div>
    <div class="block-header">
        <h2>Diversion(ADD) Configuration </h2>
        <ul class="nav navbar-right panel_toolbox">
            <li> <a href="<?php echo base_url('carriers/diversion/') . param_encrypt($carrier_id); ?>"><button class="btn btn-primary" type="button">Back To Diversion Listing page</button></a></li>
        </ul>
    </div>
</div>

<script>
    $('#btnSave, #btnSaveClose').click(function () {

        $('#add_voip_form').parsley().reset();

        var is_ok = $("#add_voip_form").parsley().isValid();
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
                $("#add_voip_form").submit();
            }
        } else
        {
            $('#add_voip_form').parsley().validate();
        }

    })
</script>