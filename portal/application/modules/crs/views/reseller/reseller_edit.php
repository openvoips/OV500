<?php
$vatflag_array = array('NONE', 'TAX', 'VAT');
$section_array = array();
$section_array['1'] = array('title' => 'Login', 'file_name' => 'inner/login.php');
$section_array['2'] = array('title' => 'Contact Detail', 'file_name' => 'inner/registration.php');
$section_array['3'] = array('title' => 'Settings', 'file_name' => 'inner/setting.php');
$section_total = count($section_array);
$active_tab = (int) $active_tab;
if ($active_tab == 0)
    $active_tab = 1;
?>
<link href="<?php echo base_url(); ?>theme/default/css/tabs.css" rel="stylesheet" type="text/css"/>
   <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">      
        <div class="x_title">
            <h2>Reseller Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo site_url('crs/resellers'); ?>"><button class="btn btn-danger" type="button" >Back to Reseller Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">

            <div class="clearfix"></div>
            <div class="ln_solid"></div>

            <ul class="nav nav-tabs bar_tabs2" id="myTab" role="tablist">
                <?php
                foreach ($section_array as $key => $section_single_array) {
                    $class = '';
                    if ($key == $active_tab)
                        $class = 'active';
                    echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
                    echo '<a class="nav-link " id="contact-tab" data-toggle="tab" href="#contact" role="tab"  onclick="show_section(\'' . $key . '\')">' . $section_single_array['title'] . '</a>';
                    echo '</li>';
                }
                ?>
            </ul>
            <div class="clearfix" ></div>
        </div>

        <?php
        foreach ($section_array as $key => $section_single_array) {

            if ($key == $active_tab)
                $class = '';
            else
                $class = 'hide';
            ?>
            <div class="x_content content_div <?php echo $class; ?>" id="<?php echo 'id_content_div_' . $key; ?>">

                <?php
//                echo $section_single_array['file_name'];
                include($section_single_array['file_name']);
                ?>    
            </div>

        <?php } ?>     
    </div>
     <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">      
        <div class="x_title">
            <h2>Reseller Account Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">     
                <li><a href="<?php echo site_url('crs/resellers'); ?>"><button class="btn btn-danger" type="button" >Back to Reseller Listing Page</button></a> </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
</div>

<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">

<script>
    $(document).ready(function () {
        $('.combobox').combobox()
    });
</script>                                

<script>

/////////			
    /* $('#btnLoginSave').click(function () {
     $('#login_form').parsley().reset();
     var is_ok = $("#login_form").parsley().isValid();
     if (is_ok === true)
     {
     var clicked_button_id = this.id;
     if (clicked_button_id == 'btnLoginSave')
     $('#button_login_action').val('save_close');
     else
     $('#button_login_action').val('save');
     if (is_ok === true)
     {
     $("#login_form").submit();
     }
     } else
     {
     $('#login_form').parsley().validate();
     }
     })*/
    ///////////			



    /*  $('#btnSave, #btnSaveClose').click(function () {
     $('#account_form').parsley().reset();
     var is_ok = $("#account_form").parsley().isValid();
     if (is_ok === true)
     {
     var clicked_button_id = this.id;
     if (clicked_button_id == 'btnSaveClose')
     $('#button_action').val('save_close');
     else
     $('#button_action').val('save');
     if (is_ok === true)
     {
     //alert("ok");
     $("#account_form").submit();
     }
     } else
     {
     $('#account_form').parsley().validate();
     }
     })*/




    function media_changed()
    {
        media_value = $("input[name='media_rtpproxy']:checked").val();
        if (media_value == '1')
            $('#id_transcoding_div').show();
        else
            $('#id_transcoding_div').hide();
    }

    function country_changed()
    {
        country_id = $("#country_id").val();
        if (country_id == '100')
        {
            $('#id_state_div').show();
            $('#state_code_id').attr('data-parsley-required', 'true');
        } else
        {
            $('#id_state_div').hide();
            $('#state_code_id').attr('data-parsley-required', 'false');
        }
    }



    function currency_changed()
    {
        var tariff_str = '<option value="">Select</option>';
        currency_id = $("#currency_id").val();
        if (currency_id == '')
        {

        } else
        {
            var arrayLength = tariff_array[currency_id].length;
            if (arrayLength > 0)
            {
                for (var i in tariff_array[currency_id])
                {
                    tariff_id = tariff_array[currency_id][i][0];
                    tariff_name = tariff_array[currency_id][i][1];
                    tariff_str = tariff_str + '<option value="' + tariff_id + '">' + tariff_name + '</option>';
                }
            }
        }
        $('#tariff_id').html(tariff_str);
    }

    function tax_chnaged() {
        vat_flag = $("#vat_flag").val();
        if (vat_flag == 'NONE') {
            $('#taxchange').hide();
            $('#tax1').attr('data-parsley-required', 'false');
            $('#tax2').attr('data-parsley-required', 'false');
            $('#tax3').attr('data-parsley-required', 'false');
            $('#tax_type').attr('data-parsley-required', 'false');
            $("#tax_type").val("exclusive");
            $("#tax1").val("0.0");
            $("#tax2").val("0.0");
            $("#tax3").val("0.0");
        } else {
            $('#taxchange').show();
            /*set to initial status*/
            $('.tax_class').show();
            $('#vat_flag').attr('data-parsley-required', 'true');
            $('#tax_type').attr('data-parsley-required', 'true');
            $('#tax1').attr('data-parsley-required', 'true');
            $('#tax2').attr('data-parsley-required', 'true');
            $('#tax3').attr('data-parsley-required', 'true');
        }
    }

    function doConfirmCancel(delete_val, delete_action_url = '', delete_type = '')
    {
        var delete_id_array = [];
        delete_id_array.push(delete_val);
        var modal_body = '<h1 class="text-center"><i class="fa fa-exclamation-circle"></i></h1>' +
                '<h4 class="text-center">Are you sure!</h4>' +
                '<p class="text-center">You won\'t be able to revert this!</p>';
        var modal_footer = '<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>' +
                '<button type="button" class="btn btn-danger" id="modal-btn-yes-single">Yes. cancel it!</button>';
        openModal('', '', modal_body, modal_footer);
        $("#my-modal").modal('show');
        $("#modal-btn-yes-single").on("click", function () {
//alert("single");

            var form = document.createElement("form");
            document.body.appendChild(form);
            form.method = "POST";
            if (delete_action_url == '')
                form.action = window.location.href;
            else
            {
                form.action = BASE_URL + delete_action_url;
            }

            var element2 = document.createElement("INPUT");
            element2.name = "action";
            element2.value = 'OkDeleteData';
            element2.type = 'hidden';
            form.appendChild(element2);
            var element3 = document.createElement("INPUT");
            element3.name = "delete_id";
            element3.value = JSON.stringify(delete_id_array);
            element3.type = 'hidden';
            form.appendChild(element3);
            if (delete_type == '')
            {
            } else
            {
                var element4 = document.createElement("INPUT");
                element4.name = "delete_parameter_two";
                element4.value = delete_type;
                element4.type = 'hidden';
                form.appendChild(element4);
            }


            form.submit();
//alert("yes");
            $("#my-modal").modal('hide');
        });
    }

    $('input[type=radio][name=media_rtpproxy]').change(function () {
        media_changed();
    });
    $("#account_currency_id").change(function () {
        currency_changed();
    });
    $("#country_id").change(function () {
        country_changed();
    });
    $('#vat_flag').change(function () {
        tax_chnaged();
    });
    $(document).ready(function () {
        media_changed();
        country_changed();
        tax_chnaged();
    });
</script>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>
<script>
    var section_total = "<?php echo $section_total; ?>";
    function show_section(key)
    {
        var id_header_section = 'id_header_section_' + key;
        var id_content_div = 'id_content_div_' + key;
        $('.content_div').addClass('hide');
        $('#' + id_content_div).removeClass('hide');
        ///////////////
        $('.header_section').removeClass('active');
        $('#' + id_header_section).addClass('active');
    }

    function save_button(key)
    {
        var form_name = 'tab_form_' + key;
        var is_ok = $("#" + form_name).parsley().isValid();
        if (is_ok === true)
        {
            if (is_ok === true)
            {
                $("#" + form_name).submit();
            }
        } else
        {
            $('#' + form_name).parsley().validate();
        }
    }
</script>