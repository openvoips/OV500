div class="">
<div class="clearfix"></div>  
<div class="col-md-12 col-sm-12 col-xs-12 right">
    <div class="ln_solid"></div>
    <div class="x_title">
        <h2>Tariff Configuration Management</h2>
        <ul class="nav navbar-right panel_toolbox">     
            <li><a href="<?php echo base_url('tariffs') ?>"><button class="btn btn-danger" type="button" >Back to Tariff Listing Page</button></a> </li>
        </ul>
        <div class="clearfix"></div>
    </div>

</div>
<div class="row">
    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Tariff (EDIT)</h2>

                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form action="<?php echo base_url(); ?>tariffs/editTP/<?php echo param_encrypt($data['tariff_id']); ?>" method="post" name="edit_form" id="edit_form" data-parsley-validate class="form-horizontal form-label-left">
                            <input type="hidden" name="button_action" id="button_action" value="">
                            <input type="hidden" name="action" value="OkSaveData">    
                            <input type="hidden" name="frm_key" value="<?php echo $data['tariff_id']; ?>"/>
                            <input type="hidden" name="frm_id" value="<?php echo $data['id']; ?>"/>               
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Tariff Name <span class="required">*</span></label>
                                <div class="col-md-8 col-sm-6 col-xs-12">
                                    <input type="text" name="frm_name" id="frm_name" value="<?php echo $data['tariff_name']; ?>"  data-parsley-required="" data-parsley-length="[5, 30]" class="form-control col-md-7 col-xs-12">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Tariff Code <span class="required">*</span></label>
                                <div class="col-md-8 col-sm-6 col-xs-12">
                                    <input type="text" name="frm_abbr" id="frm_abbr" value="<?php echo $data['tariff_id']; ?>" class="form-control col-md-7 col-xs-12" disabled="disabled">
                                </div>
                            </div>

                            <?php if (!check_logged_user_group(array('RESELLER'))) : ?>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Who can Use <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="frm_type" id="frm_type" class="form-control data-search-field" disabled="disabled">
                                            <option value="CARRIER"  <?php if ($data['tariff_type'] == 'CARRIER') echo 'selected'; ?>>Carrier</option>
                                            <option value="CUSTOMER"  <?php if ($data['tariff_type'] == 'CUSTOMER') echo 'selected'; ?>>Customer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Currency <span class="required">*</span></label>
                                    <div class="col-md-8 col-sm-6 col-xs-12">
                                        <select name="frm_currency" id="frm_currency" class="form-control data-search-field" disabled="disabled">
                                            <option value="">Select Route</option>
                                            <?php for ($i = 0; $i < count($currency_data); $i++) { ?>								
                                                <option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if ($data['tariff_currency_id'] == $currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>    
                            <?php else: ?>
                                <input type="hidden" name="frm_type" id="frm_type" value="<?php echo $data['tariff_type']; ?>" data-parsley-required="" class="form-control" />
                                <input type="hidden" name="frm_currency" id="frm_currency" value="<?php echo $data['tariff_currency_id']; ?>" data-parsley-required="" class="form-control" />
                            <?php endif; ?>

                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" for="last-name">Description</label>
                                <div class="col-md-8 col-sm-6 col-xs-12">
                                    <textarea name="frm_desc" id="frm_desc" class="form-control col-md-7 col-xs-12"><?php echo $data['tariff_description']; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Tariff Status</label>
                                <div class="col-md-8 col-sm-6 col-xs-12">
                                    <div class="radio">
                                        <label><input type="radio" name="frm_status" id="status1" value="1"  <?php if ($data['tariff_status'] == 1) { ?> checked="checked" <?php } ?> /> Active</label>
                                        <label> <input type="radio" name="frm_status" id="status0" value="0" <?php if ($data['tariff_status'] == 0) { ?> checked="checked" <?php } ?> /> Inactive</label>
                                    </div>                    
                                </div>
                            </div>
                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                                    <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                    <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Listing Page</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>





    </div>

    <div class="col-md-6 col-sm-12 col-xs-12">

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>OutGoing(PSTN) Calls Rate's Ratecard List</h2>                            
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <br /> 
                        <div class="table-responsive">
                            <table class="table  jambo_table bulk_action table-bordered">
                                <thead>
                                    <tr class="headings thc">
                                        <th class="column-title">Ratecard</th>
                                        <th class="column-title" width="80">Start</th>
                                        <th class="column-title" width="80">End</th>
                                        <th class="column-title" width="50">Priority</th>
                                        <th class="column-title no-link last" width="50"><span class="nobr">Actions</span> </th>
                                    </tr>
                                </thead>		
                                <tbody>
                                    <?php
                                    $w = unserialize(DAY_FROM_WEEK);
                                    for ($i = 0; $i < count($data_ratecard); $i++) {
                                        $text_class = "";
                                        ?>
                                        <tr class=" <?php echo $text_class; ?>">
                                            <td >
                                                <?php echo $data_ratecard[$i]['ratecard_name'] . '<br />[' . $data_ratecard[$i]['ratecard_id'] . ']'; ?>
                                            </td>
                                            <td><?php echo $w[$data_ratecard[$i]['start_day']]; ?> <br /><?php echo $data_ratecard[$i]['start_time']; ?></td>
                                            <td><?php echo $w[$data_ratecard[$i]['end_day']]; ?> <br /><?php echo $data_ratecard[$i]['end_time']; ?></td>
                                            <td><?php echo $data_ratecard[$i]['priority']; ?></td>                                   
                                            <td class="last">
                                                <a href="<?php echo base_url(); ?>tariffs/editTMP/<?php echo param_encrypt($data_ratecard[$i]['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                                <a href="javascript:doConfirmDelete('<?php echo param_encrypt($data_ratecard[$i]['id']); ?>','tariffs','mapping');" title="Delete" class="delete"><i class="fa fa-trash"></i></a>						 
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                            <a href="<?php echo base_url(); ?>tariffs/addTMP/<?php echo param_encrypt($data['tariff_id'] . '@OUTGOING'); ?>"><input value="Add Ratecard" name="add_link" class="btn btn-primary" type="button"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Incoming(DID's) Calls Rate's Ratecard List</h2>                           
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <br /> 
                        <div class="table-responsive">
                            <table class="table jambo_table bulk_action table-bordered">
                                <thead>
                                    <tr class="headings thc">
                                        <th class="column-title">Ratecard</th>
                                        <th class="column-title" width="80">Start</th>
                                        <th class="column-title" width="80">End</th>
                                        <th class="column-title" width="50">Priority</th>
                                        <th class="column-title no-link last" width="50"><span class="nobr">Actions</span> </th>
                                    </tr>
                                </thead>		
                                <tbody>
                                    <?php
                                    $w = unserialize(DAY_FROM_WEEK);
                                    for ($i = 0; $i < count($data_ratecard_in); $i++) {
                                        $text_class = "";
                                        ?>
                                        <tr class=" <?php echo $text_class; ?>">
                                            <td >
                                                <?php echo $data_ratecard_in[$i]['ratecard_name'] . '<br />[' . $data_ratecard_in[$i]['ratecard_id'] . ']'; ?>
                                            </td>
                                            <td><?php echo $w[$data_ratecard_in[$i]['start_day']]; ?> <br /><?php echo $data_ratecard_in[$i]['start_time']; ?></td>
                                            <td><?php echo $w[$data_ratecard_in[$i]['end_day']]; ?> <br /><?php echo $data_ratecard_in[$i]['end_time']; ?></td>
                                            <td><?php echo $data_ratecard_in[$i]['priority']; ?></td>                                   
                                            <td class="last">
                                                <a href="<?php echo base_url(); ?>tariffs/editTMP/<?php echo param_encrypt($data_ratecard_in[$i]['id']); ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                                                <a href="javascript:doConfirmDelete('<?php echo param_encrypt($data_ratecard_in[$i]['id']); ?>','tariffs','mapping');" title="Delete" class="delete"><i class="fa fa-trash"></i></a>						 
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                            <a href="<?php echo base_url(); ?>tariffs/addTMP/<?php echo param_encrypt($data['tariff_id'] . '@INCOMING'); ?>"><input value="Add Ratecard" name="add_link" class="btn btn-primary" type="button"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="col-md-12 col-sm-12 col-xs-12 right">
    <div class="ln_solid"></div>
    <div class="x_title">
        <h2>Tariff Configuration Management</h2>
        <ul class="nav navbar-right panel_toolbox">     
            <li><a href="<?php echo base_url('tariffs') ?>"><button class="btn btn-danger" type="button" >Back to Tariff Listing Page</button></a> </li>
        </ul>
        <div class="clearfix"></div>
    </div>

</div>
</div>  
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>