<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

<div class="">
    <div class="clearfix"></div>   

    <div class="col-md-12 col-sm-12 col-xs-12 right">       
        <div class="x_title">
            <h2>Tariff Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('tariffs') ?>"><button class="btn btn-danger" type="button" >Back to Ratecard Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Add Tariff</h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />         
                    <form action="<?php echo base_url(); ?>tariffs/addTP" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">    
                        <input type="hidden" name="frm_key" value=""/>
                        <input type="hidden" name="frm_id" value=""/>               
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Name <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="frm_name" id="frm_name" value="<?php echo set_value('frm_prefix'); ?>"  data-parsley-required="" data-parsley-length="[5, 30]" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <?php if (!check_logged_user_group(array('RESELLER'))) : ?>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Type <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select name="frm_type" id="frm_type" class="form-control data-search-field">
                                        <option value="CUSTOMER"  <?php if (set_value('frm_type') == 'customer') echo 'selected'; ?>>Customer</option>
                                        <?php if (get_logged_account_level() == 0): ?>
                                            <option value="CARRIER"  <?php if (set_value('frm_type') == 'carrier') echo 'selected'; ?>>Carrier</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div> 
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Currency <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select name="frm_currency" id="frm_currency" class="form-control data-search-field" <?php if (get_logged_account_level() != 0) echo 'readonly'; ?>>
                                        <?php for ($i = 0; $i < count($currency_data); $i++) { ?>	
                                            <?php if (get_logged_account_level() == 0): ?>							
                                                <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if (set_value('frm_currency', get_logged_account_currency()) == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name']; ?></option>
                                            <?php elseif (get_logged_account_level() != 0 && get_logged_account_currency() == $currency_data[$i]['currency_id']): ?>
                                                <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if (set_value('frm_currency', get_logged_account_currency()) == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name']; ?></option>
                                            <?php endif; ?>

                                        <?php } ?>
                                    </select>
                                </div>
                            </div>    
                        <?php else: ?>
                            <input type="hidden" name="frm_type" id="frm_type" value="user" data-parsley-required="" class="form-control" />
                            <input type="hidden" name="frm_currency" id="frm_currency" value="<?php echo get_logged_account_currency(); ?>" data-parsley-required="" class="form-control" />
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="last-name">Description</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea name="frm_desc" id="frm_desc" class="form-control col-md-7 col-xs-12"><?php echo set_value('frm_desc'); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-9 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <label><input type="radio" name="frm_status" id="status1" value="1"  <?php echo set_radio('frm_status', '1', TRUE); ?> /> Active</label>
                                    <label> <input type="radio" name="frm_status" id="status0" value="0" <?php echo set_radio('frm_status', '0'); ?> /> Inactive</label>
                                </div>                   
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <!--<a href="<?php echo base_url('tariffs') ?>"><button class="btn btn-primary" type="button">Cancel</button></a>-->				
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Listing Page</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Tariff Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('tariffs') ?>"><button class="btn btn-danger" type="button" >Back to Ratecard Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
</div>    
<script>
    $('#btnSave, #btnSaveClose').click(function () {
        var is_ok = $("#add_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#add_form").submit();
        } else
        {
            $('#add_form').parsley().validate();
        }
    })
</script>