<?php
 
if (isset($accountinfo['customer_priceplan']) && count($accountinfo['customer_priceplan']) > 0) {
    $customer_priceplan_data = $accountinfo['customer_priceplan'];
    $save_text = 'Update';
} else {
    $customer_priceplan_data = array('billing_cycle' => '', 'payment_terms' => '', 'itemised_billing' => '', 'invoice_via_email' => '');
    $save_text = 'Save';
}
?>
<form action="" method="post" name="<?php echo 'tab_form_' . $key; ?>" id="<?php echo 'tab_form_' . $key; ?>" data-parsley-validate class="form-horizontal form-label-left">
    <input type="hidden" name="button_action" id="button_action" value="">
    <input type="hidden" name="action" value="OkSaveInvoice">
    <input type="hidden" name="tab" value="<?php echo $key; ?>">
    <input type="hidden" name="account_id" value="<?php echo $accountinfo['account_id']; ?>">


    <div class="form-group hidden">
        <label class="control-label col-md-3 col-sm-6 col-xs-12" >Monthly Services Charges date of Month<span class="required">*</span></label>
        <div class="col-md-4 col-sm-6 col-xs-12">                
            <select name="monthly_charges_day" id="monthly_charges_day" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                <option value="">Select</option>
                <?php
                $date_array = range(1, 27);
                $str = '';
                foreach ($date_array as $day) {
                    $selected = ' ';
                    if ($customer_priceplan_data['monthly_charges_day'] == $day)
                        $selected = '  selected="selected" ';
                    else if ($day == '26')
                        $selected = '  selected="selected" ';
                    $str .= '<option value="' . $day . '" ' . $selected . '> ' . $day . '</option>';
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-6 col-xs-12" >Billing Cycle<span class="required">*</span></label>
        <div class="col-md-4 col-sm-6 col-xs-12">                
            <select name="billing_cycle" id="billing_cycle" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>" onchange="monthly_charges_day_updated()">
                <option value="">Select</option>
                <?php
				if($customer_priceplan_data['billing_cycle']!='')
					$billing_cycle_selected = $customer_priceplan_data['billing_cycle'];
				else
					$billing_cycle_selected = 'MONTHLY';	
                $billing_cycle_array = array('DAILY', 'WEEKLY', 'MONTHLY');
                $str = '';
                foreach ($billing_cycle_array as $key1 => $name) {
                    $selected = ' ';
                    if ( $billing_cycle_selected== $name)
                        $selected = '  selected="selected" ';
                    $str .= '<option value="' . $name . '" ' . $selected . '>' . ucfirst(strtolower($name)) . '</option>';
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>
    <div class="form-group hide" id="id_div_billing_day">
        <label class="control-label col-md-3 col-sm-6 col-xs-12" >Billing Date Of Month<span class="required">*</span></label>
        <div class="col-md-4 col-sm-6 col-xs-12">                
            <select name="billing_day" id="billing_day" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                <option value="">Select</option>
                <?php
               
                $date_array = range(1, 27);
                $str = '';
                foreach ($date_array as $day) {
                    $selected = ' ';
                    if ($customer_priceplan_data['billing_day'] == $day)
                        $selected = '  selected="selected" ';
                     
                    $str .= '<option value="' . $day . '" ' . $selected . '>' . $day . '</option>';
                }
                echo $str;
                ?>
            </select>
            <div>Last one month usage will calculate in billing & Invoicing.</div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-6 col-xs-12" >Payment Terms<span class="required">*</span></label>
        <div class="col-md-4 col-sm-6 col-xs-12">                
            <select name="payment_terms" id="payment_terms" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                <option value="">Select</option>
                <?php
                $payment_terms_array = range(1, 30);
                $str = '';
                foreach ($payment_terms_array as $key1 => $payment_terms) {
                    $selected = ' ';
                    if ($customer_priceplan_data['payment_terms'] == $payment_terms)
                        $selected = '  selected="selected" ';
                    $str .= '<option value="' . $payment_terms . '" ' . $selected . '>' . ucfirst($payment_terms) . '</option>';
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>
    <div class="form-group">
    <?php
	if($customer_priceplan_data['itemised_billing']!='')
		$itemised_billing_selected = $customer_priceplan_data['itemised_billing'];
	else
		$itemised_billing_selected = 1;	
	?>
        <label class="control-label col-md-3 col-sm-6 col-xs-12" >Itemized Bill<span class="required">*</span></label>
        <div class="radio">
            <label><input type="radio" name="itemised_billing" id="itemised_billing1" value="1" <?php if ($itemised_billing_selected == 1) echo 'checked="checked"'; ?> /> Yes</label>
            <label> <input type="radio" name="itemised_billing" id="itemised_billing2" value="0" <?php if ($itemised_billing_selected == 0) echo 'checked="checked"'; ?>/> No</label>
        </div>                        
    </div>
    <div class="form-group">
     <?php
	if($customer_priceplan_data['invoice_via_email']!='')
		$invoice_via_email_selected = $customer_priceplan_data['invoice_via_email'];
	else
		$invoice_via_email_selected = 1;	
	?>
        <label class="control-label col-md-3 col-sm-6 col-xs-12" >Send Invoice On Mail<span class="required">*</span></label>
        <div class="radio">
            <label><input type="radio" name="invoice_via_email" id="invoice_via_email1" value="1" <?php if ($invoice_via_email_selected == 1) echo 'checked="checked"'; ?>/> Yes</label>
            <label> <input type="radio" name="invoice_via_email" id="invoice_via_email2" value="0" <?php if ($invoice_via_email_selected == 0) echo 'checked="checked"'; ?>/> No</label>
        </div>                        
    </div>



    <div class="form-group">
        <div class="col-md-12 col-sm-12 col-xs-12 col-md-offset-5">
            <button type="button" id="<?php echo 'btnSaveClose' . $key; ?>" class="btn btn-info" onclick="save_button('<?php echo $key; ?>')"><?php echo $save_text; ?></button>
        </div>
    </div>
</form>
<script>
    function monthly_charges_day_updated()
    {
        var billing_cycle = $('#billing_cycle').val();
        if (billing_cycle == 'MONTHLY')
        {
            $('#id_div_billing_day').removeClass('hide');
            $('#billing_day').attr('data-parsley-required', 'true');
        } else
        {
            $('#id_div_billing_day').addClass('hide');
            $('#billing_day').attr('data-parsley-required', 'false');
        }
    }

    $(document).ready(function () {

        monthly_charges_day_updated()

    });
</script>