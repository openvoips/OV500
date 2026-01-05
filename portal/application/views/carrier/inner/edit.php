<form action="" method="post" name="<?php echo 'tab_form_' . $key; ?>" id="<?php echo 'tab_form_' . $key; ?>" data-parsley-validate class="form-horizontal form-label-left">
    <input type="hidden" name="button_action" id="button_action" value="">
    <input type="hidden" name="action" value="OkSaveData">
    <input type="hidden" name="tab" value="<?php echo $key; ?>">
    <input type="hidden" name="key" value="<?php echo $data['carrier_id']; ?>"/>
    <input type="hidden" name="carrier_id" value="<?php echo $data['carrier_id']; ?>"/>

    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Carrier Code </label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input type="text" name="carrier_id_display" id="carrier_id_display" value="<?php echo $data['carrier_id']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Carrier Name <span class="required">*</span> </label>
        <div class="col-md-7 col-sm-6 col-xs-12"><!-- switch_user_access.name-->
            <input type="text" name="carrier_name" id="carrier_name" value="<?php echo $data['carrier_name']; ?>"  data-parsley-required="" data-parsley-minlength="3"  data-parsley-maxlength="30" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Maximum Call Sessions  <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input type="text" name="carrier_cc" id="carrier_cc" value="<?php echo $data['carrier_cc']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Call Sessions per Second <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input type="text" name="carrier_cps" id="carrier_cps" value="<?php echo $data['carrier_cps']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Billing in Decimal <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input type="text" name="dp" id="dp" value="<?php echo $data['dp']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
        </div>
    </div>
    <div class="form-group" id="div_id_vat_flag">
        <label for="middle-name" class="control-label col-md-5 col-sm-3 col-xs-12">VAT / Tax Flag <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <select name="vat_flag" id="vat_flag" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                <option value="">Select</option>
                <?php
                $str = '';
                foreach ($vatflag_array as $keys => $vat) {
                    $selected = ' ';
                    if ($data['vat_flag'] == $vat)
                        $selected = '  selected="selected" ';
                    $str .= '<option value="' . $vat . '" ' . $selected . '>' . $vat . '</option>';
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>

    <div id ="taxchange">

        <div class="form-group" id="div_id_tax_type">
            <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax on bill Amount Calculation <span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <select name="tax_type" id="tax_type" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                    <option value="">Select</option>
                    <?php
                    $tax_type_array = array('exclusive' => 'Tax On Bill Amount (exclusive)', 'inclusive' => 'Bill Amount with Tax (inclusive)');
                    $str = '';
                    foreach ($tax_type_array as $keys => $tax_type) {
                        $selected = ' ';
                        if ($data['tax_type'] == $tax_type)
                            $selected = '  selected="selected" ';
                        $str .= '<option value="' . $keys . '" ' . $selected . '>' . ucfirst($tax_type) . '</option>';
                    }
                    echo $str;
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax Certificate Number</label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="tax_number" id="tax_number" value="<?php echo $data['tax_number']; ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">
            </div>
        </div>
        <div class="form-group tax_class">
            <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax 1(%) <span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="tax1" id="tax1" value="<?php echo $data['tax1']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control " tabindex="<?php echo $tab_index++; ?>">
            </div>                       
        </div>
        <div class="form-group tax_class">
            <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax 2(%) <span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="tax2" id="tax2" value="<?php echo $data['tax2']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
            </div>

        </div>
        <div class="form-group tax_class">
            <label class="control-label col-md-5 col-sm-3 col-xs-12" >Tax 3(%) <span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="tax3" id="tax3" value="<?php echo $data['tax3']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
            </div>
        </div>

    </div>

    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Currency <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <select name="carrier_currency_id" id="carrier_currency_id" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">                              
                <?php
                $str = '';
                foreach ($currency_options as $keys => $currency_array) {
                    $selected = '';
                    if ($data['carrier_currency_id'] == $currency_array['currency_id']) {
                        $selected = '  selected="selected" ';
                        $str .= '<option value="' . $currency_array['currency_id'] . '" ' . $selected . '>' . $currency_array['symbol'] . " - " . $currency_array['name'] . '</option>';
                    }
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Tariff Plan <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <select name="tariff_id" id="tariff_id" class="combobox form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                <option value="">Select</option>

            </select>
        </div>
    </div>


    <div class="form-group">              
        <label class="control-label col-md-5 col-sm-3 col-xs-12">Progress Timeout <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-10">
            <input type="text" name="carrier_progress_timeout" id="carrier_progress_timeout" value="<?php echo $data['carrier_progress_timeout']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">                </div>

    </div>
    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">Ring Timeout <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-10">
            <input type="text" name="carrier_ring_timeout" id="carrier_ring_timeout" value="<?php echo $data['carrier_ring_timeout']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
        </div>

    </div>


    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12" for="first-name">CLI Prefer <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <select name="cli_prefer" id="cli_prefer" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                <option value="">Select</option>
                <?php
                $cli_prefer_array = array('rpid', 'pid', 'no');
                $str = '';
                foreach ($cli_prefer_array as $keys => $cli_prefer) {
                    $selected = ' ';
                    if ($data['cli_prefer'] == $cli_prefer)
                        $selected = '  selected="selected" ';
                    $str .= '<option value="' . $cli_prefer . '" ' . $selected . '>' . strtoupper($cli_prefer) . '</option>';
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>

    <?php
    $codecs_array = array('G729', 'PCMU', 'PCMA', 'G722');
    ?>
    <div class="form-group">
        <label for="middle-name" class="control-label col-md-5 col-sm-3 col-xs-12">Codecs</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <?php
            echo '<div class="checkbox">';
            foreach ($codecs_array as $keys => $codec) {
                if (strpos($data['carrier_codecs'], $codec) !== FALSE)
                    $checked = 'checked="checked"';
                else
                    $checked = '';
                echo '' .
                '<input type="checkbox" name="codecs[]" id="codec' . $keys . '" value="' . $codec . '" tabindex="' . $tab_index++ . '" ' . $checked . '/><label for ="codec' . $keys . '"> ' . $codec . '</label>' .
                '';
            }

            echo '</div>';
            ?>
        </div>
    </div>


    <?php
    $logged_account_type = get_logged_account_type();
    $account_status = $data['carrier_status'];
    $status_update_options_array = array();
    $status_update_options_array['SYSTEM'] = array(
        '1' => array(0, 2),
        '0' => array(1),
        '2' => array(0, 1),
    );

    $status_name_array = array(
        '1' => array('name' => 'Active', 'tooltip' => 'Carrier is active'),
        '0' => array('name' => 'Closed', 'tooltip' => 'Carrier Closed'),
        '2' => array('name' => 'Inactive', 'tooltip' => 'Carrier is Inactive'),
    );
    ?>


    <div class="form-group">
        <label for="middle-name" class="control-label col-md-5 col-sm-3 col-xs-12">Call Extend Option</label>
        <div class="col-md-7 col-sm-6 col-xs-10">
            <div class="radio">
                <input type="radio" class="with-gap"  name="extend_call_duration" id="extend_call_duration1" value="1" <?php if ($data['extend_call_duration'] == '1') echo ' checked'; ?> /> <label for="extend_call_duration1">Active</label>
                <input type="radio"  class="with-gap" name="extend_call_duration" id="extend_call_duration0" value="0" <?php if ($data['extend_call_duration'] == '0') echo ' checked'; ?> /> <label for="extend_call_duration0">Inactive</label>
            </div>                    
        </div>
    </div>
    
    
    <div class="extend_call_class">
          <div class="form-group ">
            <label for="middle-name" class="control-label col-md-5 col-sm-3 col-xs-12" >Call Extend Duration (Sec.)<span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="minimumcallduration" id="minimumcallduration" value="<?php echo $data['minimumcallduration']; ?>"  class="form-control" tabindex="<?php echo $tab_index++; ?>">
            </div>                     
        </div>
    </div>    
    
    
    

    <div class="form-group">
        <label for="middle-name" class="control-label col-md-5 col-sm-3 col-xs-12">Diversion Header Option</label>
        <div class="col-md-7 col-sm-6 col-xs-10">
            <div class="radio">
                <input type="radio" class="with-gap"  name="diversion_header_option" id="diversion_header_option1" value="1" <?php if ($data['diversion_header_option'] == '1') echo ' checked'; ?> /> <label for="diversion_header_option1">Active</label>
                <input type="radio"  class="with-gap" name="diversion_header_option" id="diversion_header_option0" value="0" <?php if ($data['diversion_header_option'] == '0') echo ' checked'; ?> /> <label for="diversion_header_option0">Inactive</label>
            </div>                    
        </div>
    </div>
    
    
    
    
    
    
    
    
    <div class="diversion_class">
        <div class="form-group">
            <label for="middle-name" class="control-label col-md-5 col-sm-3 col-xs-12">Diversion Header As</label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <div class="radio">
                    <input  class="with-gap" type="radio" name="diversion_header_as_comingcli_db" id="diversion_header_as_comingcli_db1" value="1" <?php if ($data['diversion_header_as_comingcli_db'] == '1') echo ' checked'; ?> /> <label for="diversion_header_as_comingcli_db1">As Incoming</label>
                    <input  class="with-gap" type="radio" name="diversion_header_as_comingcli_db" id="diversion_header_as_comingcli_db0" value="0" <?php if ($data['diversion_header_as_comingcli_db'] == '0') echo ' checked'; ?> /> <label for="diversion_header_as_comingcli_db0">From DB</label>
                </div>                    
            </div>
        </div>
        <div class="form-group ">
            <label class="control-label col-md-5 col-sm-3 col-xs-12" >Diversion Header Format<span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="diversion_header_format" id="diversion_header_format" value="<?php echo $data['diversion_header_format']; ?>"  class="form-control" tabindex="<?php echo $tab_index++; ?>">
            </div>                     
        </div>
    </div>
    
    
    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12">Status</label>
        <div class="col-md-7 col-sm-6 col-xs-10">
            <?php
            if (isset($status_update_options_array[$logged_account_type][$account_status])) {
                foreach ($status_update_options_array[$logged_account_type][$account_status] as $status_value) {
                    if ($account_status == $status_value)
                        $checked = ' checked="checked"';
                    else
                        $checked = '';

                    if (isset($status_name_array[$status_value])) {
                        $status_name = $status_name_array[$status_value]['name'];
                        $tooltip = $status_name_array[$status_value]['tooltip'];
                    } else {
                        $status_name = $status_value;
                        $tooltip = '';
                    }
                    ?>

                    <input type="radio"  class="with-gap" name="carrier_status" id="carrier_status<?php echo $status_value; ?>" value="<?php echo $status_value; ?>" <?php echo $checked; ?>  tabindex="<?php echo $tab_index++; ?>" /> <label for="carrier_status<?php echo $status_value; ?>"><?php echo $status_name; ?></label>
                    <?php
                    if ($tooltip != '')
                        echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '"><i class="fa fa-question-circle"></i></a>';
                    ?>

                <?php }
                ?>

                <?php
                if (isset($status_name_array[$account_status])) {
                    $status_name = $status_name_array[$account_status]['name'];
                    $tooltip = $status_name_array[$account_status]['tooltip'];
                } else {
                    $status_name = $account_status;
                    $tooltip = '';
                }
                ?>

                <input type="radio"  class="with-gap" name="carrier_status" id="status<?php echo $account_status; ?>" value="<?php echo $account_status; ?>"  checked="checked"  tabindex="<?php echo $tab_index++; ?>" /><label for="status<?php echo $account_status; ?>"> <?php echo $status_name; ?></label>
                <?php
                if ($tooltip != '')
                    echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '"><i class="fa fa-question-circle"></i></a>';
                ?>

                <?php
            } else {
                if (isset($status_name_array[$account_status])) {
                    $status_name = $status_name_array[$account_status]['name'];
                    $tooltip = $status_name_array[$account_status]['tooltip'];
                } else {
                    $status_name = $account_status;
                    $tooltip = '';
                }
                echo '<div class="col-md-12 col-sm-6 col-xs-12 radio"><label for="status<?php echo $account_status; ?>">' . $status_name . '</label> ';
                if ($tooltip != '')
                    echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '" ><i class="fa fa-question-circle"></i></a>';
                echo '<input type="hidden" name="carrier_status" id="status1" value="' . $account_status . '" /></div>';
            }
            ?>
        </div>
    </div>

    <div class="form-group text-center">                
        <div class="col-md-12 col-sm-12 col-xs-12">
            <button type="button" id="<?php echo 'btnSaveClose' . $key; ?>" class="btn btn-primary" onclick="save_button('<?php echo $key; ?>')">Save Carrier Detail</button> 

        </div>
    </div>



    </br></br></br>
</form>
    
<script>
    var account_id = '';
    var currency_id = '';
    var existing_value2 = "<?php echo $data['tariff_id']; ?>";
    ;

    $(document).ready(function () {
        currency_id = $('#carrier_currency_id').val();
        if (currency_id != '')
            destination_type_changed('tariff_id', account_id, currency_id, existing_value2);
    });

//if on currency_id change
    $("#carrier_currency_id").change(function () {
        currency_id = $('#carrier_currency_id').val();
        destination_type_changed('tariff_id', account_id, currency_id, existing_value2);
    });







    function destination_type_changed(id_tariff, account_id, currency_id, existing_value2)
    {

        {
            data_array = {
                action: 'get_tariffs',
                account_id: account_id,
                currency_id: currency_id,
                existing_value: existing_value2,
            };
            console.log(data_array);
            var target = BASE_URL + "ajax/ajax_get_tariff";

            $.ajax({
                method: "POST",
                url: target,
                dataType: 'json',
                data: data_array
            })
                    .done(function (msg) {
                        //console.log(msg);
                        if (typeof msg['html'] === 'undefined')
                        {
                        } else
                        {
                            $('#' + id_tariff).html(msg['html']);
                        }

                    });

        }
    }
</script>
<script>
    var account_id = '';
    var currency_id = '';

    $(document).ready(function () {
        currency_id = $('#carrier_currency_id').val();

    });

</script>