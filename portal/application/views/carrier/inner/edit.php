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
                <?php
                $str = '';
                $is_assigned_tariff_found = false;
                foreach ($tariff_options as $keys => $tariff_name_array) {
                    if ($data['carrier_currency_id'] != $tariff_name_array['tariff_currency_id'])
                        continue;
                    $selected = ' ';
                    if ($data['tariff_id'] == $tariff_name_array['tariff_id']) {
                        $selected = '  selected="selected" ';
                        $is_assigned_tariff_found = true;
                    }
                    $str .= '<option value="' . $tariff_name_array['tariff_id'] . '" ' . $selected . '>' . $tariff_name_array['tariff_name'] . '</option>';
                }
                if (!$is_assigned_tariff_found && isset($data['tariff']) && count($data['tariff']) > 0) {
                    $str .= '<option value="' . $data['tariff']['tariff_id'] . '">' . $data['tariff']['tariff_name'] . '</option>';
                    $tariff_options[] = $data['tariff'];
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>




    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12">Provider <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <select name="provider_id" id="provider_id" class="combobox form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                <option value="" >Select</option>
                <?php
                $str = '';
                if (isset($provider_data['result']) && count($provider_data['result']) > 0) {
                    foreach ($provider_data['result'] as $provider_array) {
                        $selected = ' ';
                        if ($data['provider_id'] == $provider_array['provider_id'])
                            $selected = '  selected="selected" ';
                        $currency_id = $provider_array['currency_id'];

                        if ($data['carrier_currency_id'] != $provider_array['currency_id'])
                            continue;

                        $str .= '<option value="' . $provider_array['provider_id'] . '" ' . $selected . '>' . $provider_array['provider_name'] . '</option>';
                    }
                }


                echo $str;
                ?>
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
//echo $data['user_codecs'];
            foreach ($codecs_array as $keys => $codec) {
                if (strpos($data['carrier_codecs'], $codec) !== FALSE)
                    $checked = 'checked="checked"';
                else
                    $checked = '';
                echo '<div class="checkbox">' .
                '<label><input type="checkbox" name="codecs[]" id="codec' . $keys . '" value="' . $codec . '" tabindex="' . $tab_index++ . '" ' . $checked . '/> ' . $codec . '</label>' .
                '</div>';
            }
            ?>
        </div>
    </div>


    <?php
    $logged_account_type = get_logged_account_type();
    $account_status = $data['carrier_status'];
    $status_update_options_array = array();
    $status_update_options_array['SYSTEM'] = array(
        '1' => array(0, -1),
        '0' => array(1),
        '-1' => array(0, 1),
    );


    $status_name_array = array(
        '1' => array('name' => 'Active', 'tooltip' => 'Carrier is active'),
        '0' => array('name' => 'Closed', 'tooltip' => 'Carrier Closed'),
        '-1' => array('name' => 'Inactive', 'tooltip' => 'Carrier is Inactive'),
    );
    ?>
 
    <div class="form-group">
        <label class="control-label col-md-5 col-sm-3 col-xs-12">Status</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
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
                    <div class="col-md-12 col-sm-6 col-xs-12 radio1">
                        <label><input type="radio" name="carrier_status" id="status<?php echo $status_value; ?>" value="<?php echo $status_value; ?>" <?php echo $checked; ?>  tabindex="<?php echo $tab_index++; ?>" /> <?php echo $status_name; ?></label>
                        <?php
                        if ($tooltip != '')
                            echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '"><i class="fa fa-question-circle"></i></a>';
                        ?>
                    </div>
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
                <div class="col-md-12 col-sm-6 col-xs-12 radio1">
                    <label><input type="radio" name="carrier_status" id="status<?php echo $account_status; ?>" value="<?php echo $account_status; ?>"  checked="checked"  tabindex="<?php echo $tab_index++; ?>" /> <?php echo $status_name; ?></label>
                    <?php
                    if ($tooltip != '')
                        echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '"><i class="fa fa-question-circle"></i></a>';
                    ?>
                </div>
                <?php
            }
            else {
                if (isset($status_name_array[$account_status])) {
                    $status_name = $status_name_array[$account_status]['name'];
                    $tooltip = $status_name_array[$account_status]['tooltip'];
                } else {
                    $status_name = $account_status;
                    $tooltip = '';
                }
                echo '<div class="col-md-12 col-sm-6 col-xs-12 radio"><label>' . $status_name . '</label> ';
                if ($tooltip != '')
                    echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '" ><i class="fa fa-question-circle"></i></a>';
                echo '<input type="hidden" name="carrier_status" id="status1" value="' . $account_status . '" /></div>';
            }
            ?>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="form-group">                
        <div class="col-md-12 col-sm-12 col-xs-12 col-md-offset-8">
            <button type="button" id="<?php echo 'btnSaveClose' . $key; ?>" class="btn btn-info" onclick="save_button('<?php echo $key; ?>')">Save</button> 
            <!--            <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Listing Page</button>-->
        </div>
    </div>

</form>