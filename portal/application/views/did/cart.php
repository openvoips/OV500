<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<style type="text/css">
    .purchase {cursor:pointer;}
    .cart_div div{min-height:32px; padding:4px inherit;}
    .cart_div{font-size:16px; line-height:30px;}
</style>    
<?php
$rental_total = $setup_total = 0;
$cart_count = 0;
if (isset($_SESSION['cart']['did']))
    $cart_count = count($_SESSION['cart']['did']);
?>
<div class="">
    <div class="clearfix"></div>    
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12 row">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Shopping Cart</h2>
                    <ul class="nav navbar-right panel_toolbox">    1
                        <?php if (check_logged_user_group(array(ADMIN_ACCOUNT_ID))): ?>
                            <li><a href="<?php echo base_url() ?>dids/add"><input type="button" value="Add Incoming Number" name="add_link" class="btn btn-primary"></a></li>
                        <?php elseif (check_logged_user_group(array('CUSTOMER', 'RESELLER'))): ?>
                            <li><a href="<?php echo base_url() ?>dids/purchase_did_bulk"><input type="button" value="Purchase DID" name="add_link" class="btn btn-primary"></a></li>
                        <?php endif; ?>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content row">






                    <div class='row'>                     
                        <div class="col-md-7 col-sm-6 col-xs-12">

                            <div class="col-md-12 col-sm-12 col-xs-12 text-center" id="id_cart_message">
                            </div>
                            <?php if ($cart_count > 0) { ?>
                                <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                                    <input type="button" value="Remove All DID(s)" id="id_empty_cart" class="btn btn-warning btn-xs" onclick="DoEmptyCart()"/>    
                                </div>
                            <?php } ?>
                            <div class="clearfix"></div>
                            <div class="table-responsive">					
                                <table class="table table-bordered table-striped jambo_table" id="didlisting">
                                    <thead>
                                        <tr>
                                            <th>Number</th>
                                            <th>Setup Cost</th>
                                            <th>Rental Cost</th>
                                            <th>PPM</th>
                                            <th>PPC</th>
                                            <th>Pulse</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="id_tbody_cart">
                                        <?php
                                        if (isset($did_cart['dids']) && count($did_cart['dids']) > 0) {
                                            foreach ($did_cart['dids'] as $did_data) {
                                                $rental_total += $did_data['rental'];
                                                $setup_total += $did_data['setup'];
                                                $did = $did_data['did'];
                                                ?>
                                                <tr id="<?php echo 'id_tr_' . $did; ?>">

                                                    <td><?php echo $did_data['did']; ?></td>
                                                    <td><?php echo $did_data['setup']; ?></td>
                                                    <td><?php echo $did_data['rental']; ?></td>
                                                    <td><?php echo $did_data['ppm']; ?></td>
                                                    <td><?php echo $did_data['ppc']; ?></td>
                                                    <td><?php echo $did_data['min'] . ' / ' . $did_data['res']; ?></td>
                                                    <td><?php echo '<a href="javascript:void(0);" onclick=doConfirmDelete(\'' . $did . '\') title="Remove"><i class="fa fa-trash"></i></a>'; ?></td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="8" align="center"><strong>Cart is Empty</strong></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>               
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($cart_count > 0) { ?>
                                <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                                    <input type="button" value="Remove All DID(s)" id="id_empty_cart" class="btn btn-warning btn-xs" onclick="DoEmptyCart()"/>    
                                </div>
                            <?php } ?>

                        </div>

                        <div class="col-md-5 col-sm-6 col-xs-12 " style="margin-top: 6px !important;">


                            <!----------configure block------------------->
                            <?php
                            if ($cart_count > 0) {

                                $dp = $did_enduser['dp'];
                                $tax = $did_enduser['tax1'] + $did_enduser['tax2'] + $did_enduser['tax3'];

                                $current_date = date(DATE_FORMAT_1);
                                $last_date = date('t-m-Y');

                                $rental_monthly_charge = charges($rental_total, $current_date);


                                $setup_monthly_charge = charges($setup_total, $current_date);


                                if ($did_enduser['tax_type'] == 'exclusive') {
                                    $rental_charges = exclusive_tax($tax, $rental_monthly_charge);
                                    $setup_charges = exclusive_tax($tax, $setup_monthly_charge);
                                } else {
                                    $rental_charges = inclusive_tax($tax, $rental_monthly_charge);
                                    $setup_charges = inclusive_tax($tax, $setup_monthly_charge);
                                }

                                $rental_cost = round($rental_charges['cost'], $dp);
                                $setup_cost = round($setup_charges['cost'], $dp);

                                $rental_tax = round($rental_charges['tax_amount'], $dp);
                                $setup_tax = round($setup_charges['tax_amount'], $dp);


                                $amount_total_before_tax = $rental_cost + $setup_cost;
                                $tax_total = $rental_tax + $setup_tax;

                                $amount_total = $amount_total_before_tax + $tax_total;
//print_r($setup_charges);
                                ?>
                                <div class="x_panel" id="id_price_block"> 
                                    <div class="x_content text-left row cart_div"> 

                                        <div class="col-md-12 row cart_div" style="border-bottom:1px solid #ddd;">PRICE DETAILS</div>
                                        <div class="row cart_div"> 
                                            <div class="col-md-8">Total Rental (<?php echo $current_date . ' to ' . $last_date; ?>)</div>
                                            <div class="col-md-4 text-right"><?php echo $rental_cost; ?></div>
                                        </div>
                                        <div class="row cart_div" style="border-bottom:1px dashed #ddd;">
                                            <div class="col-md-8">Total Setup (<?php echo $current_date . ' to ' . $last_date; ?>)</div>
                                            <div class="col-md-4 text-right"><?php echo $setup_cost; ?></div>
                                        </div>
                                        <div class="row cart_div" style="border-bottom:1px dashed #ddd;">
                                            <div class="col-md-8">Total Tax (<?php echo $tax . '%'; ?>)</div>
                                            <div class="col-md-4 text-right"><?php echo $tax_total; ?></div>
                                        </div>
                                        <div class="row cart_div" style="border-bottom:1px dashed #ddd;">
                                            <div class="col-md-8"><strong>Total Amount</strong></div>
                                            <div class="col-md-4 text-right"><strong><?php echo $amount_total; ?></strong></div>
                                        </div>

                                        <div class="row" style="margin:5px auto auto 5px; font-size:11px;">                                      	
                                            <p style="margin-bottom:0px; padding-bottom:0px;">* Number setup charges is one time charge and refundable.</p>
                                            <p style="margin-bottom:0px; padding-bottom:0px;">* Monthly rental will not refundable if the number is discontinued mid of the month.</p>

                                        </div>

                                    </div>
                                </div>
                                <div class="x_panel" id="id_purchase_block">    
                                    <div class="x_content text-left row">

                                        <form action="<?php echo site_url('dids/cart'); ?>" method="post" name="assign_form_bulk" id="assign_form_bulk" data-parsley-validate class="form-horizontal form-label-left">
                                            <input type="hidden" name="action" value="OkPurchaseDidBulk">

                                            <div class="form-group row">
                                                <label class="control-label col-md-6 col-sm-3 col-xs-12">Confirure Destination </label>
                                                <div class=" col-md-6 col-sm-6 col-xs-12">
                                                    <input class="form-control" type="checkbox" id="id_checkbox_configure_dest" name="id_checkbox_configure_dest" value="yes"/>
                                                </div>
                                            </div>
                                            <div class="form-group row dest_configure_related hide">
                                                <label class="control-label col-md-6 col-sm-6 col-xs-12">Destination Type <span class="required">*</span></label>
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <select class="form-control" name="dst_type" id="dst_type" >
                                                        <option value="">Select Type</option>
                                                        <option value="CUSTOMER" >USER Based</option>
                                                        <option value="IP" selected="selected">IP Based</option>
                                                        <option value="PSTN" >PSTN Number</option>                            
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group dest_configure_related hide">
                                                <label class="control-label col-md-6 col-sm-6 col-xs-12">Destination Endpoint <span class="required">*</span></label>
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <select class="form-control" name="dst_point_ip" id="dst_point_ip" >
                                                        <option value="">Select IP Address</option>
                                                        <?php
                                                        if (count($did_enduser['ip']) > 0) {
                                                            foreach ($did_enduser['ip'] as $k => $ip_data) {
                                                                if ($ip_data['ip_status'] == '1'):
                                                                    ?>
                                                                    <option value="<?php echo $ip_data['ipaddress']; ?>" ><?php echo $ip_data['ipaddress']; ?></option>	
                                                                    <?php
                                                                endif;
                                                            }
                                                        }
                                                        ?>
                                                    </select>


                                                    <select class="form-control" name="dst_point_sip" id="dst_point_sip"  style="display:none;">
                                                        <option value="">Select User</option>
                                                        <?php
                                                        if (count($did_enduser['sipuser']) > 0) {
                                                            foreach ($did_enduser['sipuser'] as $k => $sip_data) {
                                                                if ($sip_data['status'] == '1'):
                                                                    ?>
                                                                    <option value="<?php echo $sip_data['username']; ?>"><?php echo $sip_data['username']; ?></option>	
                                                                    <?php
                                                                endif;
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                    <input type="text" name="dst_point_pstn" id="dst_point_pstn" value="" class="form-control col-md-7 col-xs-12" style="display:none;">
                                                </div>
                                            </div>





                                            <div class="form-group dest_configure_related hide" id="div_dst_type">
                                                <label class="control-label col-md-6 col-sm-6 col-xs-12">Failover Destination Type </span></label>
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <select class="form-control" name="dst_type2" id="dst_type2"  >
                                                        <option value="">Select Type</option>
                                                        <option value="CUSTOMER" >USER Based</option>
                                                        <option value="IP" >IP Based</option>
                                                        <option value="PSTN" >PSTN Number</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group dest_configure_related hide" id="div_dst_endpoint">
                                                <label class="control-label col-md-6 col-sm-6 col-xs-12">Failover Destination Endpoint </label>
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <select class="form-control" name="dst_point2_ip" id="dst_point2_ip" style="display:none;">
                                                        <option value="">Select IP Address</option>
                                                        <?php
                                                        if (count($did_enduser['ip']) > 0) {
                                                            foreach ($did_enduser['ip'] as $k => $ip_data) {
                                                                if ($ip_data['ip_status'] == '1'):
                                                                    ?>
                                                                    <option value="<?php echo $ip_data['ipaddress']; ?>" ><?php echo $ip_data['ipaddress']; ?></option>	
                                                                    <?php
                                                                endif;
                                                            }
                                                        }
                                                        ?>
                                                    </select>


                                                    <select class="form-control" name="dst_point2_sip" id="dst_point2_sip" style="display:none;">
                                                        <option value="">Select User</option>
                                                        <?php
                                                        if (count($did_enduser['sipuser']) > 0) {
                                                            foreach ($did_enduser['sipuser'] as $k => $sip_data) {
                                                                if ($sip_data['status'] == '1'):
                                                                    ?>
                                                                    <option value="<?php echo $sip_data['username']; ?>" ><?php echo $sip_data['username']; ?></option>	
                                                                    <?php
                                                                endif;
                                                            }
                                                        }
                                                        ?>
                                                    </select>


                                                    <input type="text" name="dst_point2_pstn" id="dst_point2_pstn" value="" class="form-control col-md-7 col-xs-12" style="display:none;">
                                                </div>
                                            </div>



                                            <div class="form-group">
                                                <label class="control-label col-md-6 col-sm-6 col-xs-12">I accepts the terms & conditions <span class="required">*</span></label>
                                                <div class="col-md-6 col-sm-6 col-xs-12"><input class="form-control" type="checkbox" id="terms" value="yes" data-parsley-required="true" data-parsley-error-message="Please Accepts T&C"/> </div>								
                                            </div>
                                            <div class="form-group row">* <small>This Configuration will apply to all DID(s) going to purchase</small></div>

                                            <div class="ln_solid"></div>

                                            <div class="form-group">
                                                <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                                                    <button type="button" id="btnPurchaseBulk" class="btn btn-success btn-lg btn-block">Purchase <?php echo $cart_count; ?> DID(s)</button>                        			
                                                </div>
                                            </div>		



                                        </form>

                                    </div>
                                </div>
                            <?php } ?>
                        </div> 

                    </div>                             



                </div>





            </div>
        </div>
    </div>
</div>
</div> 

<script>
    function doDeleteCartDID(did_number)
    {
        $.post(BASE_URL + "dids/api_remove_cart_did", {action: 'OkRemoveDid', did: did_number})
                .done(function (data) {
                    if (data.status == true)
                    {
                        var tr = 'id_tr_' + did_number;
                        $('#' + tr).remove();
                    }
                })
                .fail(function () {
                    //alert("error");
                });
    }
    function DoEmptyCart()
    {
        $.post(BASE_URL + "dids/api_remove_cart_did", {action: 'OkRemoveAllDid'})
                .done(function (data) {

                    if (data.status == true)
                    {
                        $('#id_tbody_cart').html('<tr><td colspan="8" align="center"><strong>Cart is Empty</strong></td></tr>');
                        $('#id_price_block').hide();
                        $('#id_purchase_block').hide();
                    }

                })
                .fail(function () {
                    //alert("error");
                });

    }


    function dest_configure_checkbox_changed()
    {
        var is_configure_checked = document.getElementById("id_checkbox_configure_dest").checked;
        if (is_configure_checked)
        {
            $('.dest_configure_related').removeClass('hide');
        } else
        {
            $('.dest_configure_related').addClass('hide');
            $('#dst_point_ip').attr('data-parsley-required', 'false');
            $('#dst_point_sip').attr('data-parsley-required', 'false');
            $('#dst_point_pstn').attr('data-parsley-required', 'false');
        }
    }

    $('#id_checkbox_configure_dest').change(function () {
        dest_configure_checkbox_changed();
    });

    $('#dst_type').change(function () {//console.log("under dst_type change");
        if (this.value == 'IP') {
            $('#dst_point_ip').show();
            $('#dst_point_sip').hide();
            $('#dst_point_pstn').hide();

            $('#dst_point_ip').attr('data-parsley-required', 'true');
            $('#dst_point_sip').attr('data-parsley-required', 'false');
            $('#dst_point_pstn').attr('data-parsley-required', 'false');
        } else if (this.value == 'PSTN') {
            $('#dst_point_ip').hide();
            $('#dst_point_sip').hide();
            $('#dst_point_pstn').show();

            $('#dst_point_ip').attr('data-parsley-required', 'false');
            $('#dst_point_sip').attr('data-parsley-required', 'false');
            $('#dst_point_pstn').attr('data-parsley-required', 'true');
        } else {
            $('#dst_point_ip').hide();
            $('#dst_point_sip').show();
            $('#dst_point_pstn').hide();

            $('#dst_point_ip').attr('data-parsley-required', 'false');
            $('#dst_point_sip').attr('data-parsley-required', 'true');
            $('#dst_point_pstn').attr('data-parsley-required', 'false');
        }
    });


    function dst_type_changed(dst_type)
    {
        if (dst_type == 'IP') {
            $('#dst_point2_ip').show();
            $('#dst_point2_sip').hide();
            $('#dst_point2_pstn').hide();

            $('#dst_point2_ip').attr('data-parsley-required', 'true');
            $('#dst_point2_sip').attr('data-parsley-required', 'false');
            $('#dst_point2_pstn').attr('data-parsley-required', 'false');
        } else if (dst_type == 'PSTN') {
            $('#dst_point2_ip').hide();
            $('#dst_point2_sip').hide();
            $('#dst_point2_pstn').show();

            $('#dst_point2_ip').attr('data-parsley-required', 'false');
            $('#dst_point2_sip').attr('data-parsley-required', 'false');
            $('#dst_point2_pstn').attr('data-parsley-required', 'true');
        } else {
            $('#dst_point2_ip').hide();
            $('#dst_point2_sip').show();
            $('#dst_point2_pstn').hide();

            $('#dst_point2_ip').attr('data-parsley-required', 'false');
            $('#dst_point2_sip').attr('data-parsley-required', 'true');
            $('#dst_point2_pstn').attr('data-parsley-required', 'false');
        }

    }

    $('#dst_type2').change(function () {
        dst_type_changed(this.value);
    });


    $('#btnPurchaseBulk, #btnPurchaseBulkClose').click(function () {
        var is_ok = $("#assign_form_bulk").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#assign_form_bulk").submit();
        } else
        {
            $('#assign_form_bulk').parsley().validate();
        }
    });

</script>