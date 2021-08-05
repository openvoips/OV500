<form action="" method="post" name="<?php echo 'tab_form_'.$key;?>" id="<?php echo 'tab_form_'.$key;?>" data-parsley-validate class="form-horizontal form-label-left">
<input type="hidden" name="button_action" id="button_action" value="">
<input type="hidden" name="action" value="OkSaveTariff">
<input type="hidden" name="account_id" value="<?php echo $accountinfo['account_id']; ?>">
<input type="hidden" name="account_type" value="<?php echo $accountinfo['account_type']; ?>">
        
    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Tariff<span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-10">
            <select name="tariff_id" id="tariff_id" data-parsley-required="" class="form-control" >
                <option value="">Select Tariff</option>                    
                <?php
                $str = '';
                foreach ($tariff_options as $tariff_row) {
        
                    $selected = '';
                    if (set_value('tariff_id') == $tariff_row['tariff_id'])
                        $selected = 'selected';
                    $str .= '<option value="' . $tariff_row['tariff_id'] . '" ' . $selected . '>' . $tariff_row['tariff_name'] . '</option>';
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <label for="status-name" class="control-label col-md-4 col-sm-3 col-xs-12">Billing Code</label>
        <div class="col-md-7 col-sm-6 col-xs-12">
             <input type="text" name="billingcode" id="billingcode" class="form-control" value="<?php set_value('billingcode');?>"  data-parsley-type="alphanum" >
        </div>
    </div>
    
    
    
    <div class="form-group">
        <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
            <button type="button" id="<?php echo 'btnSaveClose'.$key;?>" class="btn btn-info" onclick="save_button('<?php echo $key;?>')">Add Tariff</button>
        </div>
    </div>
</form>