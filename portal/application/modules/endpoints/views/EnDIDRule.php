<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
//echo '<pre>';print_r($data);echo '</pre>';
$translation_rule_data = $data['translation_rules_incoming'];

$allowed_rules = $disallowed_rules = '';

foreach ($translation_rule_data as $translation_rule_data_temp) {
    if ($translation_rule_data_temp['action_type'] == 1) {
        if ($allowed_rules != '')
            $allowed_rules .= "\n";
        $allowed_rules .= $translation_rule_data_temp['display_string'];
    } else {
        if ($disallowed_rules != '')
            $disallowed_rules .= "\n";
        $disallowed_rules .= $translation_rule_data_temp['display_string'];
    }
}
?>


<?php if (!isset($data['name'])) $data['name'] =''; ?>

<div class="container-fluid">

    <div class="block-header">
        <h2>Incoming(DID) No. Rule(s) Management</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php
                $tab_index = 0;
                echo base_url('endpoints/didrules/') . param_encrypt($data['account_id']) . "/" . param_encrypt($customer_type);
                ?>"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Back to DID No. Rule Edit Page</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">


                    <h2>DID Calls Destination Number Translation</h2>

                    <form action="" method="post" name="carrier_form" id="carrier_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">
                        <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>
                        <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Account Code </label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <input type="text" name="account_name_display" id="account_name_display" value="<?php echo $data['account_id'] . ' (' . $data['name'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Allowed Rules  </label>
                            <div class="col-md-8 col-sm-6 col-xs-10">
                                <textarea name="allowed_rules" id="allowed_rules" rows="5" class="form-control col-md-7 col-xs-12"><?php echo $allowed_rules; ?></textarea>
                                <small>(Rules will be comma or in new line separated)</small> 
                            </div>
                            <label class="control-label col-md-4 col-sm-3 col-xs-12"></label>
                            <div class="col-md-8 col-sm-6 col-xs-10">
                                <small style="color: #dd4814;">
                                    <p>Notes: %=>% : allow all dialed number without allying and translation.
                                        1|%=>% : allow only 1 prefix Dialed number and removing 1 prefix from dialed number.
                                        1|%=>001% : allow only 1 prefix dialed number and removing 1 and adding 001 prefix in dialed number.
                                        1{4}|%=>% : allowing only 1 prefix dialed number with 4 length and removing 1 from the dialed number.
                                        {10}%=>91% : allowing only 10 digit dialed number and adding 91 prefix in the number.
                                        %=>1128200000 : allowing all dialed number and replacing incoming dialed number with 1128200000. </p>
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Disallowed Rules  </label>
                            <div class="col-md-8 col-sm-6 col-xs-10">
                                <textarea name="disallowed_rules" id="disallowed_rules" rows="5" class="form-control col-md-7 col-xs-12"><?php echo $disallowed_rules; ?></textarea>
                                <small>(Rules will be comma or in new line separated)</small> 
                            </div>

                        </div>


                        <label class="control-label col-md-4 col-sm-3 col-xs-12"></label>
                        <div class="col-md-8 col-sm-6 col-xs-10">
                            <small style="color: #dd4814;">
                                <p>
                                    Notes:
                                    1% : Starting with 1 prefix  number calls will not allow.
                                    % : all calls will not allow.
                                    11282550000 : 11282550000 number calls will not allow. </p>
                            </small>
                        </div>


                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-8 col-sm-12 col-xs-12 col-md-offset-4">
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Back to Endpoints Page</button>
                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>

    </div>
    <div class="block-header">
        <h2>Incoming(DID) No. Rule(s) Management</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php
                $tab_index = 0;
                echo base_url('endpoints/didrules/') . param_encrypt($data['account_id']) . "/" . param_encrypt($customer_type);
                ?>"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Back to DID No. Rule Edit Page</button></a> </li>
        </ul>
    </div>
</div>


<script>

    $('#btnSave, #btnSaveClose').click(function () {
        var is_ok = $("#carrier_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            if (is_ok === true)
            {
                //alert('ok');
                $("#carrier_form").submit();
            }
        } else
        {
            $('#carrier_form').parsley().validate();
        }
    })
</script>