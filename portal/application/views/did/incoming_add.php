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
 


                        <form action="<?php echo base_url(); ?>dids/add" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                            <input type="hidden" name="button_action" id="button_action" value="">
                            <input type="hidden" name="action" value="OkSaveData"> 

                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier <span class="required">*</span></label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
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
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="currency_display" id="currency_display" value="" class="form-control col-md-7 col-xs-12" disabled="disabled">
                                </div>
                            </div>



                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID <span class="required">*</span>   </label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="did_number" id="did_number" value="<?php echo set_value('did_number'); ?>"  data-parsley-required="" data-parsley-minlength="3"  data-parsley-maxlength="16" data-parsley-type="digits" class="form-control col-md-7 col-xs-12" >
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID Name <span class="required">*</span></label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="destination" id="destination" value="<?php echo set_value('destination', ''); ?>" data-parsley-required="" data-parsley-pattern="/^[\w ]+$/" data-parsley-minlength="2" class="form-control col-md-7 col-xs-12" >
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Setup Charge <span class="required">*</span></label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="setup_charge" id="setup_charge" value="<?php echo set_value('setup_charge', '0'); ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Rental <span class="required">*</span></label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="rental" id="rental" value="<?php echo set_value('rental', '0'); ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Call Rate <span class="required">*</span></label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="rate" id="rate" value="<?php echo set_value('rate', '0'); ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Connection Charge <span class="required">*</span></label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="connection_charge" id="connection_charge" value="<?php echo set_value('connection_charge', '0'); ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Minimum Time <span class="required">*</span></label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="minimal_time" id="minimal_time" value="<?php echo set_value('minimal_time', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Resolution Time <span class="required">*</span></label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="resolution_time" id="resolution_time" value="<?php echo set_value('resolution_time', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Channels <span class="required">*</span></label>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <input type="text" name="channels" id="channels" value="<?php echo set_value('channels', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
                                </div>
                            </div>



                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                    <button type="button" id="btnSave" class="btn btn-lg btn-success" >Save</button>
                                    <button type="button" id="btnSaveClose" class="btn btn-lg btn-info" >Save & Close</button>
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