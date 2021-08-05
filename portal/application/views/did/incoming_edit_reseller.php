
<?php
$currency_array = array();
foreach ($currency_options as $currency_options_temp) {
    $currency_array[$currency_options_temp['currency_id']] = $currency_options_temp['name'];
}

$carrier_rates = $did_data['carrier_rates'];
$did_status = $did_data['did_status'];

$display_field_array = $updatable_field_array = array(
    'carrier_id' => true,
    'did_number' => true,
    'destination' => true,
    'setup_charge' => true,
    'rental' => true,
    'rate' => true,
    'connection_charge' => true,
    'minimal_time' => true,
    'resolution_time' => true,
    'channels' => true,
    'did_status' => array()
);

unset($updatable_field_array['destination']);
unset($updatable_field_array['setup_charge']);
unset($updatable_field_array['rental']);
unset($updatable_field_array['rate']);
unset($updatable_field_array['connection_charge']);
unset($updatable_field_array['minimal_time']);
unset($updatable_field_array['resolution_time']);
unset($updatable_field_array['channels']);


/* manage which fields is not editable */
$is_updatable = true;
$page_heading = 'Edit Incoming Number';
switch ($did_status) {
    case 'NEW':

        break;
    case 'USED':

        unset($updatable_field_array['did_number']);

        $updatable_field_array['did_status'] = array('DEAD', 'BLOCKED');
        break;
    case 'DEAD':

        unset($updatable_field_array);
        $is_updatable = false;
        break;
    case 'BLOCKED':

        unset($updatable_field_array);
        $is_updatable = false;
        break;
}

/* manage which fields not to display */
if (check_logged_user_group('RESELLER')) {
    unset($display_field_array['carrier_id']);
} elseif (check_logged_user_group('CUSTOMER')) {
    unset($display_field_array['carrier_id']);
    unset($updatable_field_array);
    $updatable_field_array['channels'] = true; //one item only
    //$is_updatable = false;
    $page_heading = 'Incoming Number';
} else {
    
}

/* echo '<pre>';
  echo '<br>DID data<br>';
  print_r($did_data);
  echo '<br>DID matched rate from reseller tariff<br>';
  print_r($did_rates_data);
  //print_r($display_field_array);
  echo '</pre>'; */
$did_rates_data = $did_rates_data['dids'];
?>
<!-- Parsley -->
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$tab_index = 1;
?>    
<div class="">
    <div class="clearfix"></div>    
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>DIDs Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('dids') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to DIDs Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?php echo $page_heading; ?></h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br />
                <form action="<?php echo base_url(); ?>dids/edit/<?php echo param_encrypt($did_data['did_id']); ?>" method="post" name="did_form" id="did_form" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="button_action" id="button_action" value="">
                    <input type="hidden" name="action" value="OkSaveData"> 
                    <input type="hidden" name="did_id" value="<?php echo $did_data['did_id']; ?>"/>
                    <input type="hidden" name="rate_id" value="<?php echo $carrier_rates['rate_id']; ?>"/>

                    <?php if (isset($display_field_array['carrier_id'])): ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="carrier_id_display" id="carrier_id_display" value="<?php echo $did_data['carrier_id']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    if (isset($display_field_array['carrier_id'])):
                        $currency = '';
                        if (isset($did_data['carrier']['carrier_currency_id'])) {
                            $carrier_currency_id = $did_data['carrier']['carrier_currency_id'];
                            $currency = $currency_array[$carrier_currency_id];
                        }
                        ?>
                        <div class="form-group" id="id_currency_div">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Currency </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="currency_display" id="currency_display" value="<?php echo $currency; ?>" class="form-control col-md-7 col-xs-12" disabled="disabled">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    if (isset($display_field_array['did_number'])):
                        $is_disabled = '';
                        if (!isset($updatable_field_array['did_number']))
                            $is_disabled = ' disabled="disabled"';
                        ?>          
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID <span class="required">*</span>   </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="did_number" id="did_number" value="<?php echo $did_data['did_number']; ?>"  data-parsley-required="" data-parsley-minlength="3"  data-parsley-maxlength="15" data-parsley-type="digits" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo $is_disabled; ?> >
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php
                    if (isset($display_field_array['destination'])):
                        $is_disabled = '';
                        if (!isset($updatable_field_array['destination']))
                            $is_disabled = ' disabled="disabled"';
                        ?>    
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DID Name <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="destination" id="destination" value="<?php echo $carrier_rates['destination']; ?>" data-parsley-required="" data-parsley-pattern="/^[\w ]+$/" data-parsley-minlength="2" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo $is_disabled; ?>>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php
                    if (isset($display_field_array['setup_charge'])):
                        $is_disabled = '';
                        if (!isset($updatable_field_array['setup_charge']))
                            $is_disabled = ' disabled="disabled"';
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Setup Charge <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="setup_charge" id="setup_charge" value="<?php echo $did_rates_data['setup']; ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo $is_disabled; ?>>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php
                    if (isset($display_field_array['rental'])):
                        $is_disabled = '';
                        if (!isset($updatable_field_array['rental']))
                            $is_disabled = ' disabled="disabled"';
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Rental <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="rental" id="rental" value="<?php echo $did_rates_data['rental']; ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo $is_disabled; ?>>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php
                    if (isset($display_field_array['rate'])):
                        $is_disabled = '';
                        if (!isset($updatable_field_array['rate']))
                            $is_disabled = ' disabled="disabled"';
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Call Rate <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="rate" id="rate" value="<?php echo $did_rates_data['ppm']; ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo $is_disabled; ?>>
                            </div>
                        </div>
                    <?php endif; ?> 
                    <?php
                    if (isset($display_field_array['connection_charge'])):
                        $is_disabled = '';
                        if (!isset($updatable_field_array['connection_charge']))
                            $is_disabled = ' disabled="disabled"';
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Connection Charge <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="connection_charge" id="connection_charge" value="<?php echo $did_rates_data['ppc']; ?>" data-parsley-required="" data-parsley-price="" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo $is_disabled; ?>>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php
                    if (isset($display_field_array['minimal_time'])):
                        $is_disabled = '';
                        if (!isset($updatable_field_array['minimal_time']))
                            $is_disabled = ' disabled="disabled"';
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Minimum Time <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="minimal_time" id="minimal_time" value="<?php echo $did_rates_data['min']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo $is_disabled; ?>>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php
                    if (isset($display_field_array['resolution_time'])):
                        $is_disabled = '';
                        if (!isset($updatable_field_array['resolution_time']))
                            $is_disabled = ' disabled="disabled"';
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Resolution Time <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="resolution_time" id="resolution_time" value="<?php echo $did_rates_data['res']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo $is_disabled; ?>>
                            </div>
                        </div>
                    <?php endif; ?> 


                    <?php
                    if (isset($display_field_array['channels'])):
                        $is_disabled = '';
                        if (!isset($updatable_field_array['channels']))
                            $is_disabled = ' disabled="disabled"';
                        ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Channels <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="channels" id="channels" value="<?php echo $did_data['channels']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php echo $is_disabled; ?>>
                            </div>
                        </div>
                    <?php endif; ?> 



                    <div class="form-group" id="id_currency_div">
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Status </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">



                            <?php
                            $str = '';
                            if (count($updatable_field_array['did_status']) > 0) {
                                echo '<select name="did_status" id="did_status" data-parsley-required="" class="form-control" tabindex="' . $tab_index . '">';

                                $str .= '<option value="' . $did_status . '" selected="selected" >' . ucfirst(strtolower($did_status)) . '</option>';
                                foreach ($updatable_field_array['did_status'] as $status_name) {
                                    $str .= '<option value="' . $status_name . '" >' . ucfirst(strtolower($status_name)) . '</option>';
                                }
                                echo $str;
                                echo '</select>';
                            } else {
                                echo ucfirst(strtolower($did_status));
                            }
                            ?>


                        </div>
                    </div>

                    <?php if (!check_logged_user_group('CUSTOMER')) { ?>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
                                <a href="<?php echo base_url() ?>dids"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Cancel</button></a>		
                                <?php if ($is_updatable): ?>		
                                    <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save</button>
                                    <button type="button" id="btnSaveClose" class="btn btn-info" tabindex="<?php echo $tab_index++; ?>">Save & Close</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>DID Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('dids') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to DID Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

</div>    
<script>
    window.Parsley
            .addValidator('price', {
                validateString: function (value) {
                    return true == (/^\d+(?:[.,]\d+)*$/.test(value));
                },
                messages: {
                    en: 'This value should be in price format'
                }
            }
            );

    $(document).ready(function () {

    });



    $('#btnSave, #btnSaveClose').click(function () {
        var is_ok = $("#did_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#did_form").submit();
        } else
        {
            $('#did_form').parsley().validate();
        }
    });



</script>