<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$callerid_data = $data['callerid'];
$allowed_rules = $disallowed_rules = $dst_src_cli_rules = '';
foreach ($callerid_data as $callerid_data_temp) {
    if ($callerid_data_temp['action_type'] == 1) {
        if ($allowed_rules != '')
            $allowed_rules .= "\n";
        $allowed_rules .= $callerid_data_temp['display_string'];
    } else {
        if ($disallowed_rules != '')
            $disallowed_rules .= "\n";
        $disallowed_rules .= $callerid_data_temp['display_string'];
    }
}
$dst_src_cli_callerid_data = $data['dst_src_cli'];
foreach ($dst_src_cli_callerid_data as $callerid_data_temp) {
    if ($dst_src_cli_rules != '')
        $dst_src_cli_rules .= "\n";
    $dst_src_cli_rules .= $callerid_data_temp['display_string'];
}
?>

<?php if (!isset($data['name'])) $data['name'] =''; ?>


<div class="container-fluid">
    <div class="block-header">
        <h2>Endpoints Configuration Management</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php
                $tab_index = 0;
                echo base_url('endpoints/pstnrules/') . param_encrypt(get_logged_account_id()) . '/' . param_encrypt(get_logged_user_group());
                ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Number Translation Rules</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">                



                    <h2>Source Number Translation</h2>


                    <form action="" method="post" name="carrier_form" id="carrier_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">                 
                        <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>             
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Account Code </label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <input type="text" name="account_name_display" id="account_name_display" value="<?php echo $data['account_id'] . ' (' . $data['company_name'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Allowed Rules </label>
                            <div class="col-md-8 col-sm-6 col-xs-10">                  
                                <textarea name="allowed_rules" id="allowed_rules" rows="2" class="form-control col-md-7 col-xs-12"><?php echo $allowed_rules; ?></textarea>   
                                <small>(comma or new line separated)</small> 
                            </div>
                            <label class="control-label col-md-4 col-sm-3 col-xs-12"></label>
                            <div class="col-md-8 col-sm-6 col-xs-10">
                                <small style="color: #dd4814;">
                                    <br/>
                                    Notes:<br/>
                                    %=>% : allow all CLI without CLI translation.<br/>
                                    1|%=>% : allow only 1 prefix CLI and removing 1 prefix from CLI.<br/>
                                    1|%=>001% : allow only 1 prefix CLI and removing 1 and adding 001 prefix in CLI.<br/>
                                    1{4}|%=>% : allowing only 1 prefix CLI with 4 length and removing 1 from the CLI.<br/>
                                    {10}%=>91% : allowing only 10 digit CLI and adding 91 prefix in the CLI.<br/>
                                    %=>11282550000 : allowing all CLI and replacing incoming CLI with 11282550000.
                                </small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Disallowed Rules </label>
                            <div class="col-md-8 col-sm-6 col-xs-10">
                                <textarea name="disallowed_rules" id="disallowed_rules" rows="2" class="form-control col-md-7 col-xs-12"><?php echo $disallowed_rules; ?></textarea>
                                <small>(Rules will be comma or in new line separated)</small>  

                            </div>
                            <label class="control-label col-md-4 col-sm-3 col-xs-12"></label>
                            <div class="col-md-8 col-sm-6 col-xs-10">
                                <small style="color: #dd4814;">
                                    <br/>
                                    Notes:
                                    <br/>1% : Starting with 1 prefix source number calls will not allow.
                                    <br/>% : all calls will not allow.
                                    <br/>11282550000 : 11282550000 source number calls will not allow.
                                </small>
                            </div>    

                        </div>
                        <?php if ($data['force_dst_src_cli_prefix'] == 1): ?> 
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DST Prefix Based CLI Rules </label>
                                <div class="col-md-8 col-sm-6 col-xs-10">
                                    <textarea name="dst_src_cli_rules" id="dst_src_cli_rules" rows="2" class="form-control col-md-7 col-xs-12"><?php echo $dst_src_cli_rules; ?></textarea>
                                    <small>(Rules will be comma or in new line separated)</small>  
                                </div>
                                <label class="control-label col-md-4 col-sm-3 col-xs-12"></label>
                                <div class="col-md-8 col-sm-6 col-xs-10">
                                    <small style="color: #dd4814;">
                                        <br/>
                                        Notes:
                                        <br/>1%=>11234567: Convert 1 Destination Number prefix calls source number CLI with 11234567.
                                        <br/>%=>1%: Add the 1 in the source CLI for any Destination Number calls.
                                        <br/>1%=>%: Any incoming call with 1 Destination prefix; incoming calls source number CLI will not change.
                                    </small>
                                </div>    
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="dst_src_cli_rules" id="dst_src_cli_rules" value="">
                        <?php endif; ?>  
                        <div class="ln_solid"></div>
                        <div class="form-group text-center">
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
            <h2>Endpoints Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a href="<?php
                    $tab_index = 0;

                    echo base_url('endpoints/pstnrules/') . param_encrypt(get_logged_account_id()) . '/' . param_encrypt(get_logged_user_group());
                    ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Number Translation Rules</button></a> </li>
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
                    $("#carrier_form").submit();
                }
            } else
            {
                $('#carrier_form').parsley().validate();
            }
        })
    </script>