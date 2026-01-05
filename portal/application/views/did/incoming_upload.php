<?php
$currency_array = array();
foreach ($currency_options as $currency_options_temp) {
    $currency_array[$currency_options_temp['currency_id']] = $currency_options_temp['symbol'] . " - " . $currency_options_temp['name'];
}
$tab_index = 1;
?>
<div class="container-fluid">
    <div class="block-header">
        <h2>Direct inward dialing numbers Configuration Management</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url() ?>dids"><button class="btn btn-primary" type="button" >DIDs' Listing Page</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">



            <div class="card">
                <div class="header">                      

                    <form action="<?php echo base_url(); ?>dids/upload" method="post" name="edit_form2" id="edit_form2" data-parsley-validate class="form-horizontal" enctype="multipart/form-data">
                        <input type="hidden" name="button_action" id="button_action2" value="">
                        <input type="hidden" name="action" value="OkSaveDataBulk"> 

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier <span class="required">*</span></label>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <select name="carrier_id_bulk" id="carrier_id_bulk" data-parsley-required="" class="combobox form-control" >
                                    <option value="">Select</option>                    
                                    <?php
                                    $str = '';
                                    if (count($carriers_data['result']) > 0) {
                                        foreach ($carriers_data['result'] as $key => $carrier_array) {
                                            $carrier_currency_id = $carrier_array['carrier_currency_id'];
                                            $currency_name = $currency_array[$carrier_currency_id];

                                            $selected = ' ';
                                            if (set_value('carrier_id_bulk') == $carrier_array['carrier_id'])
                                                $selected = '  selected="selected" ';
                                            $str .= '<option value="' . $carrier_array['carrier_id'] . '" ' . $selected . ' data-currency-name="' . $currency_name . '">' . $carrier_array['carrier_name'] . ' [' . $carrier_array['carrier_id'] . ']</option>';
                                        }
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>            

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-6 col-xs-12">File <span class="required">*</span></label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <input type="file" name="did_file" id="did_file" value="" class="col-md-12 col-xs-12" data-parsley-required="" data-parsley-fileextension='csv'>
                            </div>
                        </div> 
                        <div class="form-group">                
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-4">
                                <a href="<?php echo base_url('download/sample/' . param_encrypt('did')); ?>"><button type="button" class="btn btn-dark btn-sm">Download Sample File</button></a>                </div>                                
                        </div>  

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                                <button type="button" id="btnSave2" class="btn btn-success" >Save</button>
                                <button type="button" id="btnSave2" class="btn btn-info" >Save & Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
    function carrier_changed()
    {
        var element = $('#carrier_id').find('option:selected');

        if (element.val() != '')
        {
            var currency_name = element.attr("data-currency-name");
            $('#currency_display').val(currency_name);
            $('#id_currency_div').show();
        } else
        {
            $('#currency_display').val('');
            $('#id_currency_div').hide();
        }
    }
    $(document).ready(function () {
        carrier_changed();
    });
    $("#carrier_id").change(function () {
        carrier_changed();
    });
</script>   