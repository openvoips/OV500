
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
                        <label class="control-label col-md-2 col-sm-3 col-xs-12">DID Number <span class="required">*</span></label>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <input type="text" name="frm_prefix" id="frm_prefix" value="" class="form-control col-md-7 col-xs-12" data-parsley-type="digits" data-parsley-minlength="2"/>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="button" value="Search" name="frm_search" id="frm_search" class="btn btn-info" />    
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">One DID For Every USA Area Code &nbsp;&nbsp;&nbsp;<input type="checkbox" name="area_specific" id="area_specific" value="Y" /></label>                        
                    </div>

                    <div class="clearfix"></div> 
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
                                            <th width="15">&nbsp;<input type="checkbox" id="check-all1" class="check-all1" /></th>
                                            <th>Number</th>
                                            <th>Setup Cost</th>
                                            <th>Rental Cost</th>
                                            <th>PPM</th>
                                            <th>PPC</th>
                                            <th>Pulse</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6 col-xs-12 " style="margin-top: 6px !important;">


                            <!----------Add to cart block------------------->
                            <div class="x_panel" id="id_add_to_card_div" style="display:none;"> 
                                <div class="x_content text-left"> 
                                    <h4 id="id_add_to_card_message" class="text-center"></h4>
                                    <button type="button" id="id_add_to_card_button" class="btn btn-info btn-lg btn-block" title="Cancel" onclick="DoAddDidToCart()">Add Selected DID(<small>s</small>) to Cart</button>
                                </div>
                            </div>

                            <!----------Cart Count display block------------------->
                            <div class="x_panel" id="id_card_count_div"> 
                                <div class="x_content text-left"> 
                                    <?php
                                    $cart_count = 0;
                                    if (isset($_SESSION['cart']['did']))
                                        $cart_count = count($_SESSION['cart']['did']);
                                    ?>	
                                    <div class="text-center" >DID(s) In Cart: <span id="id_cart_count"><?php echo $cart_count; ?></span>    </div><br />
                                    <div class="text-center" >
                                        <a href="<?php echo site_url('dids/cart'); ?>"><button type="button" class="btn btn-success btn-lg btn-block">Go To Cart</button> </a>
                                    </div>
                                </div>
                            </div>

                            <!----------configure block------------------->


                        </div> 

                    </div>                             



                </div>





            </div>
        </div>
    </div>
</div>
</div> 
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script>

                                        $('#check-all1').change(function () {
                                            var is_all_checked = document.getElementById("check-all1").checked;
                                            $(':checkbox.check-row1').prop('checked', this.checked);
                                            $(".check-row1").trigger("change");
                                        });

                                        function cart_button_text_update(did_count)
                                        {
                                            if (did_count == 0)
                                                var button_text = 'Select More DID';
                                            else
                                                var button_text = 'Add ' + did_count + ' Selected DID(<small>s</small>) to Cart';
                                            $('#id_add_to_card_button').html(button_text);
                                        }
                                        function cart_text_update(did_count)
                                        {
                                            $('#id_cart_count').html(did_count);
                                        }

                                        function purchase_button_text_update(did_count)
                                        {
                                            if (did_count == 0)
                                            {
                                                $('#id_purchase_cart_div').hide();
                                            } else
                                            {
                                            }
                                        }



                                        function checked_changed()
                                        {
                                            var total_checked = $("input[name='did_number_list']:checked").length;
                                            if (total_checked > 0)
                                            {
                                                $('#id_add_to_card_div').show();
                                                cart_button_text_update(total_checked);
                                            } else
                                            {
                                                $('#id_add_to_card_div').hide();
                                                $('#check-all1').prop('checked', false);
                                            }
                                        }


                                        function DoAddDidToCart()
                                        {
                                            var did_number_array2 = [];
                                            $.each($("input[name='did_number_list']:checked"), function () {
                                                did_number_array2.push($(this).val());
                                            });

                                            $.post("<?php echo base_url() ?>dids/api_add_to_cart", {did_numbers: did_number_array2})
                                                    .done(function (data) {
                                                        if (data.status == true)
                                                        {
                                                            cart_button_text_update(0);
                                                            cart_text_update(data.total_cart_did);

                                                            $('.check-row1').prop("checked", false);

                                                            var message = '<span class="text-success">DID(s) added to cart Successfully</span>';
                                                            $('#id_add_to_card_message').html(message);
                                                        } else
                                                        {
                                                            var message = '<span class="text-danger">Please Try Again</span>';
                                                            $('#id_add_to_card_message').html(message);
                                                        }
                                                        purchase_button_text_update(data.total_cart_did);
                                                    })
                                                    .fail(function () {
                                                        //alert("error");
                                                    });

                                        }






</script>


<script>
    $(document).ready(function () {

        /*  var table = $('#didlisting').DataTable({
         searching: false,
         ordering: false,
         paging: false,
         bInfo: false,
         });*/
        showDatatable('didlisting', [], [2, "desc"]);

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
                        '<td>' + value.min + ' / ' + value.res + '</td>' +
                        '</tr>';
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
            var frm_prefix = $('#frm_prefix').val();


            var area_specific_checked = document.getElementById("area_specific").checked;
            if (area_specific_checked)
                is_area_specific_checked = 'Y';
            else
                is_area_specific_checked = 'N';
            //console.log(is_area_specific_checked);	

            frm_prefix = frm_prefix.trim();

            if (frm_prefix != '')
            {
                $('#search_loader').show();
                $('#search_notfound').hide();
                $('#search_result').hide();

                $.post(BASE_URL + "dids/api_did", {did: frm_prefix, area_specific: is_area_specific_checked})
                        .done(function (data) {
                            $('#search_loader').hide();
                            if (data.status == true)
                            {
                                $('#search_result').show();
                                $.listBox(data.dids);
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

            }
        });

    })
</script>