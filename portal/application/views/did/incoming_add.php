
<?php
$currency_array = array();
foreach ($currency_options as $currency_options_temp) {
    $currency_array[$currency_options_temp['currency_id']] = $currency_options_temp['name'];
}
$tab_index = 1;
?>
<div class="">
    <div class="clearfix"></div>    
    <div class="col-md-12 col-sm-12 col-xs-12 right">     
        <div class="x_title">
            <h2>Direct inward dialing numbers Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url() ?>dids"><button class="btn btn-danger" type="button" >DIDs' Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

    <div class="col-md-5 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Incoming Number File Upload</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br />
                <form action="<?php echo base_url(); ?>dids/add" method="post" name="edit_form2" id="edit_form2" data-parsley-validate class="form-horizontal" enctype="multipart/form-data">
                    <input type="hidden" name="button_action" id="button_action2" value="">
                    <input type="hidden" name="action" value="OkSaveDataBulk"> 

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
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
                        <div class="col-md-12 col-sm-12 col-xs-12 col-md-offset-4">
                            <a href="<?php echo base_url('download/sample/' . param_encrypt('did')); ?>"><button type="button" class="btn btn-dark btn-sm">Download Sample File</button></a>                </div>                                
                    </div>  

                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                            <button type="button" id="btnSave2" class="btn btn-success" >Save</button>
                            <button type="button" id="btnSave2" class="btn btn-info" >Save & Close</button>
                        </div>
                    </div>
                </form>
            </div>  
        </div>
    </div>         



    <div class="col-md-7 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Add Incoming Number</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br />
                <form action="<?php echo base_url(); ?>dids/add" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="button_action" id="button_action" value="">
                    <input type="hidden" name="action" value="OkSaveData"> 

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="carrier_id" id="carrier_id" data-parsley-required="" class="combobox form-control" >
                                <option value="">Select</option>                    
                                <?php
                                $str = '';
                                if (count($carriers_data['result']) > 0) {
                                    foreach ($carriers_data['result'] as $key => $carrier_array) {
                                        $carrier_currency_id = $carrier_array['carrier_currency_id'];
                                        $currency_name = $currency_array[$carrier_currency_id];

                                        $selected = ' ';
                                        if (set_value('carrier_id') == $carrier_array['carrier_id'])
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $carrier_array['carrier_id'] . '" ' . $selected . ' data-currency-name="' . $currency_name . '">' . $carrier_array['carrier_name'] . ' [' . $carrier_array['carrier_id'] . ']</option>';
                                    }
                                }
                                echo $str;
                                ?>
                            </select>
                        </div>
                    </div>


                    <div class="form-group" id="id_currency_div">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Currency </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="currency_display" id="currency_display" value="" class="form-control col-md-7 col-xs-12" disabled="disabled">
                        </div>
                    </div>



                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID <span class="required">*</span>   </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="did_number" id="did_number" value="<?php echo set_value('did_number'); ?>"  data-parsley-required="" data-parsley-minlength="3"  data-parsley-maxlength="16" data-parsley-type="digits" class="form-control col-md-7 col-xs-12" >
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID Name <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="destination" id="destination" value="<?php echo set_value('destination', ''); ?>" data-parsley-required="" data-parsley-pattern="/^[\w ]+$/" data-parsley-minlength="2" class="form-control col-md-7 col-xs-12" >
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Setup Charge <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="setup_charge" id="setup_charge" value="<?php echo set_value('setup_charge', '0'); ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Rental <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="rental" id="rental" value="<?php echo set_value('rental', '0'); ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Call Rate <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="rate" id="rate" value="<?php echo set_value('rate', '0'); ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Connection Charge <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="connection_charge" id="connection_charge" value="<?php echo set_value('connection_charge', '0'); ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Minimum Time <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="minimal_time" id="minimal_time" value="<?php echo set_value('minimal_time', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Resolution Time <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="resolution_time" id="resolution_time" value="<?php echo set_value('resolution_time', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Channels <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="channels" id="channels" value="<?php echo set_value('channels', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                        </div>
                    </div>



                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                            <button type="button" id="btnSave" class="btn btn-success" >Save</button>
                            <button type="button" id="btnSaveClose" class="btn btn-info" >Save & Close</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 col-xs-12 right">       
        <div class="x_title">
            <div class="ln_solid"></div>
            <h2>Direct inward dialing numbers Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url() ?>dids"><button class="btn btn-danger" type="button" >DIDs' Listing Page</button></a> </li>
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