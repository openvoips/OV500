<?php
$vatflag_array = array('NONE', 'TAX', 'VAT');
$section_array = array();
$section_array['1'] = array('title' => 'Login', 'file_name' => 'inner/login.php');
$section_array['2'] = array('title' => 'Contact Detail', 'file_name' => 'inner/registration.php');
$section_array['3'] = array('title' => 'Settings', 'file_name' => 'inner/settings.php');
if ($data['account_type'] == 'CUSTOMER') {
    $section_array['4'] = array('title' => 'Random ANI Rule', 'file_name' => 'inner/randomclis_1.php');
    $section_array['5'] = array('title' => 'Random ANI list', 'file_name' => 'inner/randomclis_2.php');
    $section_array['6'] = array('title' => 'Random ANI Add', 'file_name' => 'inner/randomcli_add.php');
}
$section_total = count($section_array);

$tab_index = 0;
$active_tab = 6;
?>
<?php
$voip_section_array = array();
$voip_section_array['1'] = array('title' => 'Tariff', 'file_name' => 'inner/edit_tariff.php');
$voip_section_array['2'] = array('title' => 'Bundle', 'file_name' => 'inner/add_bundle.php');
$voip_section_array['4'] = array('title' => 'Dialplan', 'file_name' => 'inner/add_dialplan_' . strtolower($data['account_type']) . '.php');
$voip_section_array['5'] = array('title' => 'Bill Config', 'file_name' => 'inner/add_invoice.php');
if ($data['account_type'] == 'CUSTOMER') {
    $voip_section_array['6'] = array('title' => 'IP-Trunk', 'file_name' => 'inner/ip.php');
    $voip_section_array['7'] = array('title' => 'SIP-Truk', 'file_name' => 'inner/sip.php');
}
$voip_section_array['8'] = array('title' => 'CLI Rule', 'file_name' => 'inner/srcno.php');
$voip_section_array['9'] = array('title' => 'DST Rule', 'file_name' => 'inner/dstno.php');
$voip_section_array['10'] = array('title' => 'DID CLI Rule', 'file_name' => 'inner/didsrcno.php');
$voip_section_array['11'] = array('title' => 'DID Dial Rule', 'file_name' => 'inner/diddstno.php');
?>
<link href="<?php echo base_url(); ?>theme/default/css/tabs.css" rel="stylesheet" type="text/css"/>


<div class="container-fluid">
                <div class="block-header">
                                <h2>Customer Account Configuration Management</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                                <li> <a href="<?php echo site_url('crs'); ?>"><button class="btn btn-primary" type="button">Back to Customer Listing Page</button></a></li>
                                </ul>
                </div>
                <div class="row clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="card">
                                                                <div class="header">




                                                                                <div class="clearfix"></div>
                                                                                <div class="ln_solid"></div>

                                                                                <ul class="nav nav-tabs bar_tabs2" id="myTab" role="tablist">
                                                                                                <?php
                                                                                                $account_id_enc = param_encrypt($data['account_id']);
                                                                                                $k = 1;
                                                                                                foreach ($voip_section_array as $key => $section_single_array) {
                                                                                                    $class = '';
                                                                                                    $link = site_url('crs/editvoip/' . $account_id_enc . '/' . $key);
                                                                                                    echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
                                                                                                    echo '<a class="nav-link "  href="' . $link . '"   >' . $section_single_array['title'] . '</a>';
                                                                                                    echo '</li>';
                                                                                                }
                                                                                                ?>
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
                                                <div class="block-header">
                                                                <h2>Customer Account Configuration Management</h2>
                                                                <ul class="nav navbar-right panel_toolbox">
                                                                                <li> <a href="<?php echo site_url('crs'); ?>"><button class="btn btn-primary" type="button">Back to Customer Listing Page</button></a></li>
                                                                </ul>
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


                                    function media_changed() {
                                                    media_value = $("input[name='media_rtpproxy']:checked").val();
                                                    if (media_value == '1')
                                                                    $('#id_transcoding_div').show();
                                                    else
                                                                    $('#id_transcoding_div').hide();
                                    }

                                    function currency_changed() {
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


                                    function country_changed() {
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
                                    function billing_country_changed() {
                                                    country_id = $("#billing_country_id").val();
                                                    if (country_id == '100')
                                                    {
                                                                    $('#id_billing_state_div').show();
                                                                    $('#billing_state_code_id').attr('data-parsley-required', 'true');
                                                    } else
                                                    {
                                                                    $('#id_billing_state_div').hide();
                                                                    $('#billing_state_code_id').attr('data-parsley-required', 'false');
                                                    }
                                    }

                                    function multicallonsameno_chnaged() {
                                                    if ($('#multicallonsameno_allow').is(":checked"))
                                                    {
                                                                    $('#multicallonsameno_limit').attr('data-parsley-required', 'true');
                                                                    $('#multicallonsameno_limit').attr('data-parsley-type', 'digits');
                                                                    $("#multicallonsameno_limit").attr('readonly', false);
                                                                    if ($("#multicallonsameno_limit").val() == '')
                                                                                    $("#multicallonsameno_limit").val(5);
                                                    } else {
                                                                    $('#multicallonsameno_limit').attr('data-parsley-required', 'false');
                                                                    $('#multicallonsameno_limit').removeAttr('data-parsley-type');
                                                                    $("#multicallonsameno_limit").attr('readonly', true);
                                                                    $("#multicallonsameno_limit").val('');
                                                    }
                                    }
                                    function notification_changed(checked_id) {
                                                    if ($('#' + checked_id).is(':checked'))
                                                    {
                                                                    $('#email-' + checked_id).attr('data-parsley-required', 'true');
                                                                    if ($('#amount-' + checked_id).length !== 0)
                                                                    {
                                                                                    $('#amount-' + checked_id).attr('data-parsley-required', 'true');
                                                                                    $('#amount-' + checked_id).attr('data-parsley-type', 'digits');
                                                                    }

                                                    } else
                                                    {
                                                                    $('#email-' + checked_id).attr('data-parsley-required', 'false');
                                                                    if ($('#amount-' + checked_id).length !== 0)
                                                                    {
                                                                                    $('#amount-' + checked_id).attr('data-parsley-required', 'false');
                                                                                    $('#amount-' + checked_id).removeAttr('data-parsley-type');
                                                                    }
                                                    }
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

                                    $('input[type=radio][name=media_rtpproxy]').change(function () {
                                                    media_changed();
                                    });
                                    $("#currency_id").change(function () {
                                                    currency_changed();
                                    });
                                    $("#country_id").change(function () {
                                                    // country_changed();
                                    });
                                    $("#billing_country_id").change(function () {
                                                    billing_country_changed();
                                    });
                                    $(".notifications").change(function () {
                                                    var checked_id = this.id;
                                                    notification_changed(checked_id);
                                    });
                                    $('#multicallonsameno_allow').click(function () {
                                                    multicallonsameno_chnaged();
                                    });
                                    $('#vat_flag').change(function () {
                                                    tax_chnaged();
                                    });
                                    $(document).ready(function () {
                                                    media_changed();
                                                    //country_changed();
                                                    billing_country_changed();
                                                    multicallonsameno_chnaged();
                                                    tax_chnaged();
                                    });</script>

                                <script>
                                    /*notification form validation*/


                                </script>
                                <script>
                                    $("#same_as_registered_address").change(function () {

                                                    if ($('#same_as_registered_address').is(":checked"))
                                                    {
                                                                    var name = $('#name').val();
                                                                    var company_name = $('#company_name').val();
                                                                    var emailaddress = $('#emailaddress').val();
                                                                    var phone = $('#phone').val();
                                                                    var address = $('#address').val();
                                                                    var pincode = $('#pincode').val();
                                                                    var country_id = $('#country_id').val();
                                                                    var state_code_id = $('#state_code_id').val();
                                                                    $('#billing_name').val(name);
                                                                    $('#billing_company_name').val(company_name);
                                                                    $('#billing_emailaddress').val(emailaddress);
                                                                    $('#billing_phone').val(phone);
                                                                    $('#billing_address').val(address);
                                                                    $('#billing_pincode').val(pincode);
                                                                    $('#billing_country_id').val(country_id);
                                                                    $('#billing_state_code_id').val(state_code_id);
                                                    } else
                                                    {
                                                                    $('#billing_name').val('');
                                                                    $('#billing_company_name').val('');
                                                                    $('#billing_emailaddress').val('');
                                                                    $('#billing_phone').val('');
                                                                    $('#billing_address').val('');
                                                                    $('#billing_pincode').val('');
                                                                    $('#billing_country_id').val('');
                                                                    $('#billing_state_code_id').val('');
                                                    }

                                                    billing_country_changed();
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