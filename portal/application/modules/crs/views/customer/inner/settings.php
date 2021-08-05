
<form action="" method="post" name="<?php echo 'tab_form_' . $key; ?>" id="<?php echo 'tab_form_' . $key; ?>" data-parsley-validate class="form-horizontal form-label-left">
    <input type="hidden" name="button_action" id="button_action2" value="">
<input type="hidden" name="tab" value="<?php echo $key;?>">
    <input type="hidden" name="action" value="OkSaveData">
    <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>

    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Account Code</label>
        <div class="col-md-7 col-sm-6 col-xs-12 pad8">
            <?php echo $data['account_id']; ?>
        </div>
    </div>


    <?php
    if (!check_logged_user_group(array('RESELLER'))) {
        ?>
        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Currency <span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-12">
                <?php
                $account_currency_display = '';
                foreach ($currency_options as $keys => $currency_array) {
                    if ($data['currency_id'] == $currency_array['currency_id']) {
                        $account_currency_display = $currency_array['name'];
                        break;
                    }
                }

                echo '<input type="hidden" name="account_currency_id" id="account_currency_id" value="' . $data['currency_id'] . '" data-parsley-required="" class="form-control"  readonly="readonly" tabindex="' . $tab_index++ . '">';
                echo '<input type="text" name="account_currency_display" id="account_currency_display" value="' . $account_currency_display . '" class="form-control"  readonly="readonly"  tabindex="' . $tab_index++ . '">';
                ?>

            </div>
        </div>
        <?php
    } else {
        echo '<input type="hidden" name="account_currency_id" id="account_currency_id" value="' . $data['currency_id'] . '" class="form-control">';
    }
    ?>







    <input type="hidden" name="billing_cycle" value="monthly">



    <div class="form-group" >
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax on bill Amount Calculation <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <select name="tax_type" id="tax_type" data-parsley-required="" class="form-control" >

                <?php
                $tax_type_array = array('exclusive' => 'Tax On Bill Amount (exclusive)', 'inclusive' => 'Bill Amount with Tax (inclusive)');
                $str = '';
                foreach ($tax_type_array as $key => $tax_type) {
                    $selected = ' ';
                    if ($data['tax_type'] == $key)
                        $selected = '  selected="selected" ';
                    $str .= '<option value="' . $key . '" ' . $selected . '>' . ucfirst($tax_type) . '</option>';
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>

    <div class="form-group" >
        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">VAT / Tax Flag <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <select name="vat_flag" id="vat_flag" data-parsley-required="" class="form-control" >                                
                <?php
                $str = '';
                foreach ($vatflag_array as $key => $vat) {
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
        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax Certificate Number</label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="tax_number" id="tax_number" value="<?php echo $data['tax_number']; ?>" class="form-control" >
            </div>
        </div>
        <div class="form-group tax_class">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 1(%) <span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="tax1" id="tax1" value="<?php echo $data['tax1']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control " >
            </div>                      
        </div>
        <div class="form-group tax_class">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 2(%) <span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="tax2" id="tax2" value="<?php echo $data['tax2']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" >
            </div>                      
        </div>
        <div class="form-group tax_class">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 3(%) <span class="required">*</span></label>
            <div class="col-md-7 col-sm-6 col-xs-10">
                <input type="text" name="tax3" id="tax3" value="<?php echo $data['tax3']; ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" >
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Billing in Decimal <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input type="text" name="dp" id="dp" value="<?php echo $data['dp']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Maximum Call Sessions <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input type="text" name="account_cc" id="account_cc" value="<?php echo $data['account_cc']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Call Sessions per Second <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <input type="text" name="account_cps" id="account_cps" value="<?php echo $data['account_cps']; ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" >
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Codecs Checking</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="codecs_force" id="codecs_force1" value="1" <?php if ($data['codecs_force'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="codecs_force" id="codecs_force2" value="0" <?php if ($data['codecs_force'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>

    <?php
    $codecs_array = array('G729', 'PCMU', 'PCMA', 'G722');
    ?>
    <div class="form-group">
        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Codecs List</label>
        <div class="col-md-8 col-sm-6 col-xs-12">
            <?php
            foreach ($codecs_array as $key => $codec) {
                if (strpos($data['account_codecs'], $codec) !== FALSE)
                    $checked = 'checked="checked"';
                else
                    $checked = '';
                echo '<div class="checkbox">' .
                '<label><input type="checkbox" name="codecs[]" id="codec' . $key . '" value="' . $codec . '" tabindex="' . $tab_index++ . '" ' . $checked . '/> ' . $codec . '</label>' .
                '</div>';
            }
            ?>
            <label>Only possible if G729 codec is installed in system.</label>
        </div>
    </div>

    <div class="form-group">
        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Call With Media</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="media_rtpproxy" id="media_rtpproxy1" value="1" <?php if ($data['media_rtpproxy'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="media_rtpproxy" id="media_rtpproxy2" value="0" <?php if ($data['media_rtpproxy'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>

    <div class="form-group" id="id_transcoding_div">
        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Codecs Transcoding</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="media_transcoding" id="media_transcoding1" value="1" <?php if ($data['media_transcoding'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="media_transcoding" id="media_transcoding2" value="0" <?php if ($data['media_transcoding'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Change CLI Based On DST Prefix</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="force_dst_src_cli_prefix" id="force_dst_src_cli_prefix1" value="1" <?php if ($data['force_dst_src_cli_prefix'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="force_dst_src_cli_prefix" id="force_dst_src_cli_prefix2" value="0" <?php if ($data['force_dst_src_cli_prefix'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Max Call Duration (Minutes) <span class="required">*</span></label>                        
        <div class="col-md-7 col-sm-6 col-xs-10">
            <input type="text" name="max_callduration" id="max_callduration" value="<?php echo $data['max_callduration']; ?>" data-parsley-required=""  data-parsley-min="1" data-parsley-type="digits" data-parsley-type-message="Digits only" class="form-control" >
        </div>

    </div>
    <?php
    $logged_account_type = get_logged_user_type();
    $account_status = $data['status_id'];
    $status_update_options_array = array();
    $status_update_options_array['ADMIN'] = $status_update_options_array['SUBADMIN'] = array(
        '-1' => array(),
        '1' => array(0, -2, -3),
        '0' => array(),
        '-2' => array(0, 1, -3),
        '-3' => array(0, -2, 1),
    );
    $status_update_options_array['CREDITCONTROL'] = array(
        '-1' => array(1),
        '1' => array(0, -2, -3),
        '0' => array(),
        '-2' => array(0, 1, -3),
        '-3' => array(0, -2, 1),
    );
    $status_update_options_array['NOC'] = array(
        '1' => array(0, -2, -3),
        '-3' => array(0, -2, 1),
    );

    $status_name_array = array(
        '-1' => array('name' => 'Not Approved', 'tooltip' => 'Waiting for approval'),
        '1' => array('name' => 'Active', 'tooltip' => 'Account is active'),
        '0' => array('name' => 'Closed', 'tooltip' => 'Account Closed'),
        '-2' => array('name' => 'Temporarily Suspended', 'tooltip' => 'If balance is zero'),
        '-3' => array('name' => 'Suspected Blocked', 'tooltip' => 'For suspicious activity, make user blocked'),
        '-4' => array('name' => 'Account Closed', 'tooltip' => 'Account is closed'),
    );
    ?>


    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
        <div class="col-md-8 col-sm-6 col-xs-12">
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
                        <label><input type="radio" name="status_id" id="status<?php echo $status_value; ?>" value="<?php echo $status_value; ?>" <?php echo $checked; ?>   /> <?php echo $status_name; ?></label>
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
                    <label><input type="radio" name="status_id" id="status<?php echo $account_status; ?>" value="<?php echo $account_status; ?>"  checked="checked"   /> <?php echo $status_name; ?></label>
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
                echo '<div class="col-md-12 col-sm-6 col-xs-12 radio""><label>' . $status_name . '</label> ';
                if ($tooltip != '')
                    echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '" ><i class="fa fa-question-circle"></i></a>';
                echo '<input type="hidden" name="status_id" id="status1" value="' . $account_status . '" /></div>';
            }
            ?>
        </div>
    </div>


    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Allow IP Config to Customer</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="view_ipdevices" id="codecs_force1" value="1" <?php if ($data['view_ipdevices'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="view_ipdevices" id="codecs_force2" value="0" <?php if ($data['view_ipdevices'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>


    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Allow SIP User Config to Customer</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="view_sipdevice" id="codecs_force1" value="1" <?php if ($data['view_sipdevice'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="view_sipdevice" id="codecs_force2" value="0" <?php if ($data['view_sipdevice'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>


    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Allow CallerID Rule Config to Customer</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="view_src_out" id="codecs_force1" value="1" <?php if ($data['view_src_out'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="view_src_out" id="codecs_force2" value="0" <?php if ($data['view_src_out'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>


    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Allow Dialed No. Rule Config to Customer</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="view_dst_out" id="codecs_force1" value="1" <?php if ($data['view_dst_out'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="view_dst_out" id="codecs_force2" value="0" <?php if ($data['view_dst_out'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>


    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Allow CallerID Rule Config for DID call to Customer</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="view_src_did" id="codecs_force1" value="1" <?php if ($data['view_src_did'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="view_src_did" id="codecs_force2" value="0" <?php if ($data['view_src_did'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Allow DID No. Rule Config to Customer</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <div class="radio">
                <label><input type="radio" name="view_dst_did" id="codecs_force1" value="1" <?php if ($data['view_dst_did'] == 1) { ?> checked="checked" <?php } ?>  /> Yes</label>

                <label> <input type="radio" name="view_dst_did" id="codecs_force2" value="0" <?php if ($data['view_dst_did'] == 0) { ?> checked="checked" <?php } ?>  /> No</label>
            </div>

        </div>
    </div>
    
    
    

    <div class="ln_solid"></div>
    <div class="form-group">
        <div class="col-md-12 col-sm-6 col-xs-12 text-right">
            <?php if (check_account_permission('customer', 'edit')): ?>	
                 <button type="button" id="<?php echo 'btnSaveClose'.$key;?>" class="btn btn-info" onclick="save_button('<?php echo $key;?>')">Save</button>
<!--                <button type="button" id="btnSaveClose2" class="btn btn-info"  >Save & Go Back to Listing Page</button>-->
            <?php endif; ?>
        </div>
    </div>

</form>
