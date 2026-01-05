<?php
$vatflag_array = array('NONE', 'TAX', 'VAT');
$section_array = array();
$section_array['1'] = array('title' => 'Carrier Config', 'file_name' => 'inner/edit.php');
$section_array['2'] = array('title' => 'Randon CLI', 'file_name' => 'inner/randomcli.php');
$section_array['3'] = array('title' => 'IP Trunk', 'file_name' => 'inner/carrierip.php');
$section_array['4'] = array('title' => 'Caller Rules', 'file_name' => 'inner/calleridtranslation.php');
$section_array['5'] = array('title' => 'Prefix Rules', 'file_name' => 'inner/terminationprefix.php');
$section_array['6'] = array('title' => 'DID Caller Rules', 'file_name' => 'inner/incomingcallerid.php');
$section_array['7'] = array('title' => 'DID Prefix Rules', 'file_name' => 'inner/incomingtermination.php');

$section_total = count($section_array);
$active_tab = (int) $active_tab;
if ($active_tab == 0)
    $active_tab = 1;

$tab_index = 1;
?>
<link href="<?php echo base_url(); ?>theme/default/css/tabs.css" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
                <div class="block-header">
                                <h2>Carrier(EDIT) Configuration</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                                <a href="<?php echo base_url() ?>carriers"><button class="btn btn-primary" type="button" >Back to Carrier Listing Page</button></a>
                                </ul>
                </div>
                <div class="form-horizontal">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="card">
                                                                <div class="header">

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
                                                                                    include($section_single_array['file_name']);
                                                                                    ?>    
                                                                    </div>

                                                                <?php } ?>     
                                                </div>
                                </div>
                </div>

                <div class="block-header">
                                <h2>Carrier(EDIT) Configuration</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                                <a href="<?php echo base_url() ?>carriers"><button class="btn btn-primary" type="button" >Back to Carrier Listing Page</button></a>
                                </ul>
                </div>
</div>

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
                                    carrier_progress_timeout = $('#carrier_progress_timeout').val().trim();
                                    carrier_ring_timeout = $('#carrier_ring_timeout').val().trim();
                                    if (parseFloat(carrier_progress_timeout) > parseFloat(carrier_ring_timeout))
                                    {
                                                    var response = [];
                                                    response.item = 'carrier_progress_timeout';
                                                    response.message = 'Can not more than ring timeout';
                                                    var FieldInstance = $('[name=' + response.item + ']').parsley(),
                                                            errorName = response.item + '-custom';
                                                    window.ParsleyUI.removeError(FieldInstance, errorName);
                                                    window.ParsleyUI.addError(FieldInstance, errorName, response.message);
                                                    is_ok = false;
                                    }
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




<script>

    $('input[type=radio][name=extend_call_duration]').change(function () {
                    extend_call_duration_option();
    });
    function extend_call_duration_option() {
                    extend_call_duration_option_value = $("input[name='extend_call_duration']:checked").val();
                    if (extend_call_duration_option_value == '1') {
                                    $('#minimumcallduration').attr('data-parsley-required', 'true');
                                    $('.extend_call_class').show();
                    } else {
                                    $('#minimumcallduration').attr('data-parsley-required', 'false');
                                    $('.extend_call_class').hide();
                    }
    }


    $('input[type=radio][name=diversion_header_option]').change(function () {
                    diversion_header_option();
    });
    function diversion_header_option() {
                    call_forward_busy_value = $("input[name='diversion_header_option']:checked").val();
                    if (call_forward_busy_value == '1') {
                                    $('#diversion_header_format').attr('data-parsley-required', 'true');
                                    $('.diversion_class').show();
                    } else {
                                    $('#diversion_header_format').attr('data-parsley-required', 'false');
                                    $('.diversion_class').hide();
                    }
    }



    window.Parsley
            .addValidator('password', {
                            validateString: function (value) {
                                            r = true;
                                            if (!vCheckPassword(value))
                                            {
                                                            r = false;
                                            }
                                            return r;
                            },
                            messages: {
                                            en: 'min 8 char, 1 special char, 1 uppercase, 1 lowercase, 1 number'
                            }
            });




    function currency_changed()
    {
                    var tariff_str = '<option value="">Select</option>';
                    carrier_currency_id = $("#carrier_currency_id").val();


                    // $('#tariff_id').html(tariff_str);
    }
    $('#vat_flag').change(function () {
                    tax_chnaged();
    });
    function tax_chnaged()
    {
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
                                    $('#div_id_vat_flag').show();
                                    $('#div_id_tax_type').show();
                                    $('.tax_class').show();
                                    $('#vat_flag').attr('data-parsley-required', 'true');
                                    $('#tax_type').attr('data-parsley-required', 'true');
                                    $('#tax_type').val('exclusive');
                                    $('#tax1').attr('data-parsley-required', 'true');
                                    $('#tax2').attr('data-parsley-required', 'true');
                                    $('#tax3').attr('data-parsley-required', 'true');
                    }
    }

    $(document).ready(function () {
                    extend_call_duration_option();
                    tax_chnaged();
                    diversion_header_option();
    });
    $("#carrier_currency_id").change(function () {
                    currency_changed();
    });
</script>
