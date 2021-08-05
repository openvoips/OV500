
<table class="table table-striped jambo_table table-bordered fixed">
    <thead class="fixedHeader-locked ">
        <tr class="headings thc">
            <th class="column-title">Number should start with</th>
            <th class="column-title">Remove Prefix</th>
            <th class="column-title">Add Prefix</th>
            <!--<th class="column-title">Translation Rule</th>-->
            <th class="column-title">Type</th>

        </tr>
    </thead>
    <tbody>
        <?php
        if (count($accountinfo['callerid']) > 0 || count($accountinfo['dst_src_cli']) > 0) {
            if (count($accountinfo['callerid']) > 0) {
                foreach ($accountinfo['callerid'] as $callerid_data) {
                    if ($callerid_data['action_type'] == '1')
                        $status = '<span class="label label-success">Allowed</span>';
                    else
                        $status = '<span class="label label-danger">Blocked</span>';
                    ?>
                    <tr >
                        <td><?php
                            if (str_replace('%', '', $callerid_data['maching_string']) == '')
                                echo "Any Number";
                            else
                                echo str_replace('%', '', $callerid_data['maching_string']);
                            ?></td>

                        <td><?php echo str_replace('%', '', $callerid_data['remove_string']); ?></td>
                        <td><?php echo str_replace('%', '', $callerid_data['add_string']); ?></td>
                        <!--<td><?php echo $callerid_data['display_string']; ?></td>-->
                        <td><?php echo $status; ?></td>

                    </tr>

                    <?php
                }
            }
            if (count($data['dst_src_cli']) > 0) {
                foreach ($data['dst_src_cli'] as $callerid_data) {
                    $status = '<span class="label label-info">DST Prefix Based</span>';
                    ?>
                    <tr >
                        <td><?php echo $callerid_data['display_string']; ?></td>                                 			<td></td>

                        <td></td>
                        <td><?php echo $status; ?></td>
                    </tr>

                    <?php
                }
            }
        } else {
            ?>
            <tr>
                <td colspan="4" align="center"><strong>No Record Found</strong></td>
            </tr>
            <?php
        }
        ?>


    </tbody>
</table>
<!--                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url('voip') ?>/editSRCNo/<?php echo param_encrypt($account_id); ?>" ><input type="button" value="Manage Rules" name="add_link" class="btn btn-primary"></a>
                    </div>-->
<?php
//echo '<pre>';
//print_r($accountinfo);
//echo '</pre>';
$callerid_data = $accountinfo['callerid'];
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
$dst_src_cli_callerid_data = $accountinfo['dst_src_cli'];
foreach ($dst_src_cli_callerid_data as $callerid_data_temp) {
    if ($dst_src_cli_rules != '')
        $dst_src_cli_rules .= "\n";
    $dst_src_cli_rules .= $callerid_data_temp['display_string'];
}
?>

<form action="" method="post" name="<?php echo 'tab_form_'.$key;?>" id="<?php echo 'tab_form_'.$key;?>" data-parsley-validate class="form-horizontal form-label-left">
    <input type="hidden" name="button_action" id="button_action" value="">
    <input type="hidden" name="action" value="OkSavesrcno">                 
    <input type="hidden" name="tab" value="<?php echo $key;?>">
    <input type="hidden" name="account_id" value="<?php echo $accountinfo['account_id']; ?>"/>             
    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Account Code </label>
        <div class="col-md-8 col-sm-6 col-xs-12">
            <input type="text" name="account_name_display" id="account_name_display" value="<?php echo $accountinfo['company_name'] . ' (' . $accountinfo['account_id'] . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Allowed Rules </label>
        <div class="col-md-8 col-sm-6 col-xs-10">                  
            <textarea name="allowed_rules" id="allowed_rules" rows="5" class="form-control col-md-7 col-xs-12"><?php echo $allowed_rules; ?></textarea>   
            <small>(comma or new line separated)</small> 
        </div>

        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Rules Notes </label>
        <div class="col-md-8 col-sm-6 col-xs-12" style="color: blue">
            %=>% : allow all CLI without CLI translation.
            <br/>44|%=>% : allow only 44 prefix CLI and removing 44 prefix from CLI.
            <br/>44|%=>0044% : allow only 44 prefix CLI and removing 44 and adding 0044 prefix in CLI.
            <br/>44{4}|%=>% : allowing only 44 prefix CLI with 4 length and removing 44 from the CLI.
            <br/>{10}%=>91% : allowing only 10 digit CLI and adding 91 prefix in the CLI.
            <br/>%=>441149800228 : allowing all CLI and replacing incoming CLI with 441149800228.
        </div>



    </div>
    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Disallowed Rules </label>
        <div class="col-md-8 col-sm-6 col-xs-10">
            <textarea name="disallowed_rules" id="disallowed_rules" rows="5" class="form-control col-md-7 col-xs-12"><?php echo $disallowed_rules; ?></textarea>
            <small>(comma or new line separated)</small>  
        </div>

        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Disallowed Rules Notes </label>
        <div class="col-md-8 col-sm-6 col-xs-12" style="color: blue"> 
            44%: Blocked all 44 CLI prefix calls.
            <br/>%: Blocked any incoming CLI calls
            <br/>44125456987456: Blocked 44125456987456 CLI Calls.
        </div>


    </div>
    <?php if ($accountinfo['force_dst_src_cli_prefix'] == 1): ?> 
        <div class="form-group">
            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DST Prefix Based CLI Rules </label>
            <div class="col-md-8 col-sm-6 col-xs-10">
                <textarea name="dst_src_cli_rules" id="dst_src_cli_rules" rows="5" class="form-control col-md-7 col-xs-12"><?php echo $dst_src_cli_rules; ?></textarea>
                <small>(comma or new line separated)</small>  
            </div>

        </div>
        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">DST Prefix Based CLI Rules Notes </label>
        <div class="col-md-8 col-sm-6 col-xs-12" style="color: blue"> 
            44%=>441234567: Convert 44 Destination Number prefix calls source number CLI with 441234567.
            <br/>%=>44%: Add the 44 in the source CLI for any Destination Number calls.
            <br/>44%=>%: Any incoming call with 44 Destination prefix; incoming calls source number CLI will not change.
        </div>
        <br/>
        <br/>
    <?php else: ?>
        <input type="hidden" name="dst_src_cli_rules" id="dst_src_cli_rules" value="">
    <?php endif; ?>  
    <div class="ln_solid"></div>
    <div class="form-group">
        <div class="col-md-8 col-sm-12 col-xs-12 col-md-offset-4">
            <!--<a href="<?php echo base_url('voip') . '/editvoip/' . param_encrypt($data['account_id']); ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->	
            <?php if (check_account_permission('customer', 'edit')): ?>	
               <button type="button" id="<?php echo 'btnSaveClose'.$key;?>" class="btn btn-info" onclick="save_button('<?php echo $key;?>')">Update Source Number</button>
<!--                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Customer Edit Page</button>-->
            <?php endif; ?>
        </div>
    </div>

</form>