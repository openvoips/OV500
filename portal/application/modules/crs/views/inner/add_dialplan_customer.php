<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?> theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script>
    $(document).ready(function () {
        $('.combobox').combobox()
    });
</script>
<?php
$assigned_route_array=array();
?>
<div class="table-responsive">
	<table class="table table-striped jambo_table table-bordered">
        <thead>
            <tr class="headings thc">
                <th class="column-title">Rule</th>
                <th class="column-title">Dialing Routes</th>
                <th class="column-title">Action</th>
            </tr>

            </tr>
        </thead>

        <tbody>
            <?php
            if (count($accountinfo['dialplan']) > 0) {
                foreach ($accountinfo['dialplan'] as $dialplan_data) {
                   $assigned_route_array[]=$dialplan_data['dialplan_id'];
				   $delete_url = 'crs/editvoip/'.param_encrypt($dialplan_data['account_id']).'/'.$key;
                    ?>
                    <tr >
                        <td><?php echo $dialplan_data['maching_string']; ?></td>
                        <td><?php echo $dialplan_data['dialplan_name'] . ' (' . $dialplan_data['dialplan_id'] . ')'; ?></td>
                        <td class=" last">
                             <a href="javascript:void(0);"
                               onclick=doConfirmDelete('<?php echo $dialplan_data['id']; ?>','<?php echo $delete_url;?>','customer_dialplan_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>                      

                        </td>
                    </tr>
                    <?php
                }
            }
            else {
                ?>
                <tr>
                    <td colspan="3" align="center"><strong>No Record Found</strong></td>
                </tr>
                <?php
            }
            ?>


        </tbody>
    </table>                            
</div>
                    
                    
                     
<form action="" method="post" name="<?php echo 'tab_form_'.$key;?>" id="<?php echo 'tab_form_'.$key;?>" data-parsley-validate class="form-horizontal form-label-left">
<input type="hidden" name="button_action" id="button_action" value="">
<input type="hidden" name="action" value="OkSaveDialplanCustomer">
<input type="hidden" name="tab" value="<?php echo $key;?>">
<input type="hidden" name="account_id" value="<?php echo $accountinfo['account_id']; ?>">
                    
   <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12" >Routes <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-12">
            <select name="dialplan_id" id="dialplan_id_name" data-parsley-required="" class="combobox form-control">
                <option value="">Select Route</option>                    
                <?php
                $str = '';
                if (count($route_data) > 0) {
                    foreach ($route_data as $route_array) {
						/*if(in_array($route_array['dialplan_id'],$assigned_route_array))
							continue;*/
                        $selected = ' ';
                        if (set_value('dialplan_id') == $route_array['dialplan_id'])
                            $selected = '  selected="selected" ';
                        $str .= '<option value="' . $route_array['dialplan_id'] . '" ' . $selected . '>' . $route_array['dialplan_name'] . ' (' . $route_array['dialplan_id'] . ')</option>';
                    }
                }
                echo $str;
                ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-4 col-sm-3 col-xs-12">Dialing Pattern <span class="required">*</span></label>
        <div class="col-md-7 col-sm-6 col-xs-10">                  
            <input type="text" name="maching_string" id="maching_string" value="<?php echo set_value('maching_string', '%'); ?>" data-parsley-required=""  class="form-control">
        </div>

    </div>
	

    <div class="ln_solid"></div>
    <div class="form-group">
        <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">
            <button type="button" id="<?php echo 'btnSaveClose'.$key;?>" class="btn btn-info" onclick="save_button('<?php echo $key;?>')">Add Dialplan</button>
        </div>
    </div>

</form>