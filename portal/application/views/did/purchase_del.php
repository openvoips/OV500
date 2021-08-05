
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<style type="text/css">
    .purchase {cursor:pointer;}
</style>    
<div class="">
    <div class="clearfix"></div>    
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12 row">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Purchase DID</h2>
                    <ul class="nav navbar-right panel_toolbox">

                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content row">

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12" for="first-name">DID Number <span class="required">*</span></label>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <input type="text" name="frm_prefix" id="frm_prefix" value="" class="form-control col-md-7 col-xs-12" data-parsley-type="digits" data-parsley-minlength="2"/>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="button" value="Search" name="frm_search" id="frm_search" class="btn btn-info" />    
                        </div>
                    </div>
                    <br />  
                    <div class="ln_solid"></div>  




                    <div class="col-md-12 col-sm-6 col-xs-12 text-center" id="search_loader" style="margin:0 auto; display:none;">
                        <img src="<?php echo base_url(); ?>theme/default/images/loading.gif">
                    </div>
                    <div class="alert alert-danger alert-dismissible fade in" role="alert" id="search_notfound" style="display:none;">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong></strong>
                    </div>


                    <div class='row'>                     
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div id="search_result" style="display:none;">							
                                <table class="table table-bordered table-striped jambo_table" id="didlisting">
                                    <thead>
                                        <tr>
                                            <th width="15"><input type="checkbox" id="check-all1" class="check-all1" /></th>
                                            <th>Number</th>
                                            <th>Setup</th>
                                            <th>Rental</th>
                                            <th>PPM</th>
                                            <th>PPC</th>
                                            <th>Minimal Time</th>
                                            <th>Resolution Time</th>
                                            <th>Grace Period</th>
                                            <!--<th>Action</th>-->
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6 col-xs-12 " style="margin-top: 6px !important;">


                            <!----------Single DID display block------------------->
                            <div id="id_did_details" class="well" style="display:none;">
                                <b>DID Details</b>	
                                <form action="<?php echo base_url(); ?>dids/purchase_did" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                                    <input type="hidden" name="action" value="OkSaveData">    
                                    <input type="hidden" name="frm_key" value=""/>     	
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">DID</label>
                                        <div class="col-md-8 col-sm-9 col-xs-12">
                                            <input class="form-control" disabled="disabled" type="text" value="" id="did" name="did">								  
                                        </div>
                                    </div>
                                    <div class="ln_solid"></div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Setup Charge</label>
                                        <div class="col-md-8 col-sm-9 col-xs-12">
                                            <input class="form-control has-feedback-left" disabled="disabled" type="text" value="" id="setup" name="setup">
                                            <span class="fa fa-inr form-control-feedback left" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Rental Charge</label>
                                        <div class="col-md-8 col-sm-9 col-xs-12">
                                            <input class="form-control has-feedback-left" disabled="disabled" type="text" value="" id="rental" name="rental">
                                            <span class="fa fa-inr form-control-feedback left" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">PPM</label>
                                        <div class="col-md-8 col-sm-9 col-xs-12">
                                            <input class="form-control has-feedback-left" disabled="disabled" type="text" value="" id="ppm" name="ppm">
                                            <span class="fa fa-inr form-control-feedback left" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">PPC</label>
                                        <div class="col-md-8 col-sm-9 col-xs-12">
                                            <input class="form-control has-feedback-left" disabled="disabled" type="text" value="" id="ppc" name="ppc">
                                            <span class="fa fa-inr form-control-feedback left" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Minimal Time</label>
                                        <div class="col-md-8 col-sm-9 col-xs-12">
                                            <input class="form-control" disabled="disabled" type="text" value="" id="min" name="min">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Resolution Time</label>
                                        <div class="col-md-8 col-sm-9 col-xs-12">
                                            <input class="form-control" disabled="disabled" type="text" value="" id="res" name="res">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Grace Period</label>
                                        <div class="col-md-8 col-sm-9 col-xs-12">
                                            <input class="form-control" disabled="disabled" type="text" value="" id="grace" name="grace">
                                        </div>
                                    </div>

                                    <input class="form-control has-feedback-left"  type="hidden" value="" id="add" name="add">
                                    <input class="form-control"  type="hidden" value="" id="mul" name="mul">

                                    <div class="form-group">
                                        <label class="control-label col-md-6 col-sm-6 col-xs-12">I accepts the terms & conditions </label>
                                        <div class="col-md-2 col-sm-6 col-xs-12"><input class="form-control" type="checkbox" id="terms" value="yes" data-parsley-error-message="Please accepts"/> </div>								
                                    </div>

                                    <div class="form-group">     
                                        <div class="col-md-12">
                                            <ul class="list-unstyled text-info">
                                                <li><small><i class="fa fa-star"></i>DID Number setup charges will be not refundable and it is one-time charge.</small></li>
                                                <li><small><i class="fa fa-star"></i>Monthly DID number rental will not refundable if the DID number is discontinued mid of the month.</small></li>
                                                <li><small><i class="fa fa-star"></i>DID channel limit charges are monthly based and it will not refundable in case reduce the channels mid of the month.</small></li>
                                            </ul>   
                                        </div> 
                                    </div>
                                    <div class="ln_solid"></div>
                                    <div class="form-group">
                                        <div class="col-md-8 col-sm-9 col-xs-12 col-md-offset-4">
                                            <button type="submit" class="btn btn-success" style="margin-bottom: -15px;display:none;">Purchase</button>
                                            <input class="form-control" type="hidden" value="" id="didno" name="didno">
                                            <input class="form-control" type="hidden" value="" id="setup_charge" name="setup_charge">
                                            <input class="form-control" type="hidden" value="" id="rental_charge" name="rental_charge">
                                        </div>
                                    </div>
                                </form>
                            </div> 


                            <!----------Cart Count display block------------------->
                            <div class="x_panel" id="id_card_count_div"> 
                                <div class="x_content text-left"> 
                                    <?php
                                    $cart_count = 0;
                                    if (isset($_SESSION['cart']['did']))
                                        $cart_count = count($_SESSION['cart']['did']);
                                    ?>	
                                    <div class="text-center" >Cart: <span id="id_cart_count"><?php echo $cart_count; ?></span> DID(s)
                                        <a href="javascript:void(0);" onclick="manage_cart();" style="text-decoration:underline;">view all</a>
                                    </div>
                                </div>
                            </div>

                            <!----------Add to cart block------------------->
                            <div class="x_panel" id="id_add_to_card_div" style="display:none;"> 
                                <div class="x_content text-left"> 
                                    <h4 id="id_add_to_card_message" class="text-center"></h4>
                                    <button type="button" id="id_add_to_card_button" class="btn btn-info btn-lg btn-block" title="Cancel" onclick="DoAddDidToCart()">Add Selected DID(<small>s</small>) to Cart</button>
                                </div>
                            </div>

                            <!----------configure block------------------->

                            <div class="x_panel" id="id_purchase_cart_div" style="<?php if ($cart_count == 0) echo 'display:none;' ?>"> 
                                <div class="x_content text-left row"> 



                                    <form action="<?php echo site_url('dids'); ?>" method="post" name="assign_form" id="assign_form" data-parsley-validate class="form-horizontal form-label-left">
                                        <input type="hidden" name="assign_did_number" id="assign_did_number" value="">
                                        <input type="hidden" name="action" value="OkPurchaseDidBulk">

                                        <div class="form-group row">
                                            <label class="control-label col-md-6 col-sm-3 col-xs-12">Confirure Destination </label>
                                            <div class=" col-md-6 col-sm-6 col-xs-12">
                                                <input class="form-control" type="checkbox" id="id_checkbox_configure_dest" value="yes"/>
                                            </div>
                                        </div>
                                        <div class="form-group row dest_configure_related hide">
                                            <label class="control-label col-md-6 col-sm-6 col-xs-12">Destination Type <span class="required">*</span></label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <select class="form-control" name="dst_type" id="dst_type" data-parsley-required="">
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
                                                <select class="form-control" name="dst_point_ip" id="dst_point_ip"  data-parsley-required="">
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
                                                <select class="form-control" name="dst_type2" id="dst_type2" tabindex="<?php echo $tab_index++; ?>" >
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


                                        <div class="ln_solid"></div>

                                        <div class="form-group">
                                            <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                                                <button type="button" id="btnSave" class="btn btn-success">Purchase Selected DID</button>						
                                            </div>
                                        </div>		



                                    </form>

                                </div>
                            </div>
                        </div> 

                    </div>                             



                </div>





            </div>
        </div>
    </div>
</div>
</div> 
<script>


    $('#check-all1').change(function () {
        var is_all_checked = document.getElementById("check-all1").checked;
        $(':checkbox.check-row1').prop('checked', this.checked);
        $(".check-row1").trigger("change");
    });

    function cart_button_text_update(did_count)
    {
        var button_text = 'Add ' + did_count + ' Selected DID(<small>s</small>) to Cart';
        $('#id_add_to_card_button').html(button_text);
    }
    function cart_text_update(did_count)
    {
        $('#id_cart_count').html(did_count);
    }


    function checked_changed()
    {
        var total_checked = $("input[name='did_number_list']:checked").length;
        if (total_checked > 0)
        {
            $('#id_add_to_card_div').show();

            cart_button_text_update(total_checked);
            cart_text_update(total_checked);
        } else
        {
            $('#id_add_to_card_div').hide();
            $('#check-all1').prop('checked', false);
        }

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
        }
    }

    $('#id_checkbox_configure_dest').change(function () {
        dest_configure_checkbox_changed();
    });

    $('#dst_type').change(function () {
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


    function DoAddDidToCart()
    {
        var did_number_array2 = [];
        $.each($("input[name='did_number_list']:checked"), function () {
            did_number_array2.push($(this).val());
        });

        //console.log(did_number_array2);

        $.post("<?php echo base_url() ?>dids/api_add_to_cart", {did_numbers: did_number_array2})
                .done(function (data) {
                    if (data.status == true)
                    {
                        cart_button_text_update(data.total_cart_did);
                        cart_text_update(data.total_cart_did);

                        var message = '<span class="text-success">DID(s) added to cart Successfully</span>';
                        $('#id_add_to_card_message').html(message);
                    } else
                    {
                        var message = '<span class="text-danger">Please Try Again</span>';
                        $('#id_add_to_card_message').html(message);
                    }
                    showhide_purchase_bulk(data.total_cart_did);
                })
                .fail(function () {
                    alert("error");
                });

    }

    function showhide_purchase_bulk(did_count)
    {
        if (did_count > 0)
            $('#id_purchase_cart_div').show();
        else
            $('#id_purchase_cart_div').hide();

    }

    function manage_cart()
    {

        alert('Display All Dids In cart & option to remove');
    }

    /*$('#btnSave, #btnSaveClose').click(function() {
     var is_ok = $("#assign_form").parsley().isValid();
     if(is_ok === true)
     {
     var clicked_button_id = this.id;	
     
     var lead_id_array = [];
     $.each($("input[name='did_number_list']:checked"), function(){
     lead_id_array.push($(this).val());
     });
     $('#assign_did_number').val(lead_id_array);	
     
     
     if(clicked_button_id=='btnSaveClose')
     $('#button_action').val('save_close');
     else
     $('#button_action').val('save');	
     
     $("#assign_form").submit();
     }
     else
     {
     $('#assign_form').parsley().validate();
     }
     });*/

</script>


<script>
    $(document).ready(function () {

        var table = $('#didlisting').DataTable({
            searching: false,
            ordering: false,
            paging: false,
            bInfo: false,
        });

        $('#didlisting').on('click', 'tr', function () {
            if ($(this).hasClass('selected'))
            {
                $(this).removeClass('selected');
            } else
            {
                $('#didlisting tbody tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        });

        $.dataBox = function (i, d) {
            $('#did').val(d[i].did);
            $('#didno').val(d[i].did);
            $('#setup').val(d[i].setup);
            $('#setup_charge').val(d[i].setup);
            $('#rental').val(d[i].rental);
            $('#rental_charge').val(d[i].rental);
            $('#ppm').val(d[i].ppm);
            $('#ppc').val(d[i].ppc);
            $('#min').val(d[i].min);
            $('#res').val(d[i].res);
            $('#grace').val(d[i].grace);
            $('#add').val(d[i].add);
            $('#mul').val(d[i].mul);

            $('#add_form .btn-success').show();

        };

        $.listBox = function (data) {
            var str = '';
            $.each(data, function (key, value) {
                hash = key + 1;
                str += '<tr><td>' + hash + ' ' +
                        '<input type="checkbox" class="check-row1" name="did_number_list" id="lead_id_list_' + hash + '" value="' + value.did + '" onchange="checked_changed()">' +
                        '<td>' + value.did + '</td>' +
                        '<td>' + value.setup + '</td>' +
                        '<td>' + value.rental + '</td>' +
                        '<td>' + value.ppm + '</td>' +
                        '<td>' + value.ppc + '</td>' +
                        '<td>' + value.min + '</td>' +
                        '<td>' + value.res + '</td>' +
                        '<td>' + value.grace + '</td>' +
                        '</tr>';
                /*'<td width="80"><a title="Purchase" data-key="' + key + '" class="edit purchase">View <i class="fa fa-angle-double-right"></i></a></td>'+*/
            });

            $('#id_did_details').hide();


            $('#search_result tbody').html(str);
            $('#search_result tbody a').bind("click", function () {
                $('#id_did_details').show();
                $('#search_result').show();

                $.dataBox($(this).data("key"), data);
            });
        };

        $('#frm_search').click(function () {
            $('#terms').attr('data-parsley-required', 'false');
            var is_ok = $("#add_form").parsley().isValid();
            if (is_ok === true)
            {
                $('#search_loader').show();
                $('#search_notfound').hide();
                $('#search_result').hide();



                $.post("<?php echo base_url() ?>dids/api_did", {did: $('#frm_prefix').val()})
                        .done(function (data) {
                            $('#search_loader').hide();
                            if (data.status == true)
                            {
                                $('#search_result').show();
                                $.listBox(data.dids);


                                $('#terms').attr('data-parsley-required', 'true');
                            } else
                            {
                                $('#search_notfound strong').html(data.msg);
                                $('#search_notfound strong').html(data.dids);
                                $('#search_notfound').show();
                            }
                        })
                        .fail(function () {
                            alert("error");
                        });
            } else
            {
                $('#add_form').parsley().validate();
            }
        });

    })
</script>
