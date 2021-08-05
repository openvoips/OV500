<div class="">
    <div class="clearfix"></div>   

    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Ratecard Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('ratecard') ?>"><button class="btn btn-danger" type="button" >Back to Ratecard Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Ratecard Management (ADD)</h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />         
                    <form action="<?php echo base_url(); ?>ratecard/addRC" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">    
                        <input type="hidden" name="frm_key" value=""/>
                        <input type="hidden" name="frm_id" value=""/>               
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Ratecard Name <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="frm_name" id="frm_name" value="<?php echo set_value('frm_name'); ?>"  data-parsley-required="" data-parsley-alphanumspace="" data-parsley-length="[5, 30]" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <?php if (!check_logged_user_group(array('RESELLER'))) : ?>
                            <div class="form-group">
                                <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Who can Use <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select name="frm_type" id="frm_type" class="form-control data-search-field">
                                        <option value="CUSTOMER"  <?php if (strtolower(set_value('frm_type')) == 'customer') echo 'selected'; ?>>Customer</option>
                                        <?php if (get_logged_account_level() == 0): ?>
                                            <option value="CARRIER"  <?php if (strtolower(set_value('frm_type')) == 'carrier') echo 'selected'; ?>>Provider</option>
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
                                                <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if (set_value('frm_currency', get_logged_account_currency()) == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['symbol'] . " - " . $currency_data[$i]['name']; ?></option>
                                            <?php elseif (get_logged_account_level() != 0 && get_logged_account_currency() == $currency_data[$i]['currency_id']): ?>
                                                <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if (set_value('frm_currency', get_logged_account_currency()) == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['symbol'] . " - " . $currency_data[$i]['name']; ?></option>
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
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">Usage For <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <select name="ratecard_for" id="ratecard_for" class="form-control data-search-field" data-parsley-required="">
                                    <option value="INCOMING"  <?php echo set_select('ratecard_for', 'INCOMING'); ?>>DID Incoming Calls</option>
                                    <option value="OUTGOING"  <?php echo set_select('ratecard_for', 'OUTGOING', true); ?>>Outgoing Calls</option>
                                </select>
                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <button type="button" id="btnSave" class="btn btn-success">Save Rate Card</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go back to  RateCard Listing Page</button>
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
            <h2>Ratecard Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo base_url('ratecard') ?>"><button class="btn btn-danger" type="button" >Back to Ratecard Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
</div>    
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>