<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$tab_index = 1;
$dp = 4;
$vatflag_array = array('NONE', 'TAX', 'VAT');
?>    


<div class="container-fluid">
    <div class="block-header">
        <h2>Carrier(ADD) Configuration</h2>
        <ul class="nav navbar-right panel_toolbox">
            <a href="<?php echo base_url() ?>carriers"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Carrier Listing Page</button></a>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">

                    <form action="<?php echo base_url(); ?>carriers/addC" method="post" name="carrier_form" id="carrier_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData"> 

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier Name <span class="required">*</span>   </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="carrier_name" id="carrier_name" value="<?php echo set_value('carrier_name'); ?>"  data-parsley-required="" data-parsley-minlength="3"  data-parsley-maxlength="30" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Maximum Call Sessions <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="carrier_cc" id="carrier_cc" value="<?php echo set_value('carrier_cc', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Call Sessions per Second <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="carrier_cps" id="carrier_cps" value="<?php echo set_value('carrier_cps', '1'); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Billing in Decimal <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="dp" id="dp" value="<?php echo set_value('dp', $dp); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>



                        <div class="form-group" id="div_id_vat_flag">
                            <label  class="control-label col-md-4 col-sm-3 col-xs-12">VAT / Tax Flag<span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="vat_flag" id="vat_flag" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select</option>     
                                    <?php
                                    $str = '';
                                    foreach ($vatflag_array as $key => $vat) {
                                        $selected = ' ';
                                        if (set_value('vat_flag', 'NONE') == $vat)
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $vat . '" ' . $selected . '>' . $vat . '</option>';
                                    }
                                    echo $str;
                                    ?>  
                                </select>                            
                            </div>
                        </div>
                        <div id ="taxchange">
                            <div class="form-group" id="div_id_tax_type">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax on bill Amount Calculation <span class="required">*</span></label>
                                <div class="col-md-7 col-sm-6 col-xs-12">
                                    <select name="tax_type" id="tax_type" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                        <option value="">Select</option>                    
                                        <?php
                                        $tax_type_array = array('exclusive' => 'Tax On Bill Amount (exclusive)', 'inclusive' => 'Bill Amount with Tax (inclusive)');
                                        $str = '';
                                        foreach ($tax_type_array as $key => $tax_type) {
                                            $selected = ' ';
                                            if (set_value('tax_type', 'inclusive') == $tax_type)
                                                $selected = '  selected="selected" ';
                                            $str .= '<option value="' . $key . '" ' . $selected . '>' . ucfirst($tax_type) . '</option>';
                                        }
                                        echo $str;
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group tax_class">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 1(%) <span class="required">*</span></label>
                                <div class="col-md-7 col-sm-6 col-xs-10">
                                    <input type="text" name="tax1" id="tax1" value="<?php echo set_value('tax1', '0'); ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                </div>                      
                            </div>
                            <div class="form-group tax_class">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 2(%) <span class="required">*</span></label>
                                <div class="col-md-7 col-sm-6 col-xs-10">
                                    <input type="text" name="tax2" id="tax2" value="<?php echo set_value('tax2', '0'); ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                </div>                       
                            </div>
                            <div class="form-group tax_class">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" >Tax 3(%) <span class="required">*</span></label>
                                <div class="col-md-7 col-sm-6 col-xs-10">
                                    <input type="text" name="tax3" id="tax3" value="<?php echo set_value('tax3', '0'); ?>" data-parsley-required="" data-parsley-pattern="^[0-9]\d*(\.\d+)?$" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                </div>                     
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Currency <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="carrier_currency_id" id="carrier_currency_id" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select</option>                    
                                    <?php
                                    $str = '';
                                    foreach ($currency_options as $key => $currency_array) {
                                        $selected = ' ';
                                        if (set_value('carrier_currency_id') == $currency_array['currency_id'])
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $currency_array['currency_id'] . '" ' . $selected . '>' . $currency_array['symbol'] . " - " . $currency_array['name'] . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier Tariff <span class="required">*</span>
                            </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="tariff_id" id="tariff_id" class="form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select Tariff</option>                    

                                </select>
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Progress Timeout (Sec) <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="carrier_progress_timeout" id="carrier_progress_timeout" value="<?php echo set_value('carrier_progress_timeout', 2); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>

                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Ring Timeout (Sec) <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="carrier_ring_timeout" id="carrier_ring_timeout" value="<?php echo set_value('carrier_ring_timeout', 60); ?>" data-parsley-required="" data-parsley-type="digits" data-parsley-min="1" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>                      
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">CLI Prefer <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select name="cli_prefer" id="cli_prefer" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select</option>                    
                                    <?php
                                    $cli_prefer_array = array('rpid', 'pid', 'no');
                                    $str = '';
                                    foreach ($cli_prefer_array as $key => $cli_prefer) {
                                        $selected = ' ';
                                        if (set_value('cli_prefer', 'rpid') == $tax_type)
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $cli_prefer . '" ' . $selected . '>' . strtoupper($cli_prefer) . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>




                        <?php
                        $codecs_array = array('G729', 'PCMU', 'PCMA', 'G722');
                        $data['carrier_codecs'] = array('G729', 'PCMU', 'PCMA');
                        ?>
                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Codecs</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <?php
                                echo '<div class="checkbox">';
                                foreach ($codecs_array as $keys => $codec) {

                                    $checked = '';
                                    echo '' .
                                    '<input type="checkbox" name="codecs[]" id="codec' . $keys . '" value="' . $codec . '" tabindex="' . $tab_index++ . '" ' . $checked . '/><label for ="codec' . $keys . '"> ' . $codec . '</label>' .
                                    '';
                                }

                                echo '</div>';
                                ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Diversion Header Option</label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <input type="radio"  class="with-gap" name="diversion_header_option" id="diversion_header_option1" value="1" <?php echo set_radio('diversion_header_option', '1', false); ?> /><label for="diversion_header_option1"> Active</label>
                                    <input type="radio"  class="with-gap" name="diversion_header_option" id="diversion_header_option0" value="0" <?php echo set_radio('diversion_header_option', '0', TRUE); ?> /> <label for="diversion_header_option0">Inactive</label>
                                </div>                    
                            </div>
                        </div>
                        <div class="diversion_class">
                            <div class="form-group">
                                <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Diversion Header As</label>
                                <div class="col-md-8 col-sm-6 col-xs-12">
                                    <div class="radio">
                                        <input type="radio"  class="with-gap" name="diversion_header_as_comingcli_db" id="diversion_header_as_comingcli_db1" value="1" <?php echo set_radio('diversion_header_as_comingcli_db', '1', TRUE); ?> /> <label for="diversion_header_as_comingcli_db1">As Incoming</label>
                                        <input type="radio"  class="with-gap" name="diversion_header_as_comingcli_db" id="diversion_header_as_comingcli_db0" value="0" <?php echo set_radio('diversion_header_as_comingcli_db', '0'); ?> /><label for="diversion_header_as_comingcli_db0"> From DB</label>
                                    </div>                    
                                </div>
                            </div>
                            <div class="form-group ">
                                <label class="control-label col-md-4 col-sm-3 col-xs-12" >Diversion Header Format<span class="required">*</span></label>
                                <div class="col-md-7 col-sm-6 col-xs-10">
                                    <input type="text" name="diversion_header_format" id="diversion_header_format" value="<?php echo set_value('diversion_header_format', '<sip:${RDN}@${network_addr}>;reason=no-answer;counter=1;privacy=off'); ?>" data-parsley-required="" class="form-control" tabindex="<?php echo $tab_index++; ?>">
                                </div>                     
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <input type="radio" class="with-gap" name="carrier_status" id="status1" value="1" <?php echo set_radio('carrier_status', '1', TRUE); ?> /> <label for="status1">Active</label>
                                    <input type="radio" class="with-gap" name="carrier_status" id="status0" value="0" <?php echo set_radio('carrier_status', '0'); ?> /><label for="status0"> Inactive</label>
                                </div>                    
                            </div>
                        </div>	
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-12 col-sm-12 col-xs-12 text-center">

                                <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save Carrier Detail</button>

                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>


        <div class="block-header">
            <h2>Carrier(ADD) Configuration</h2>
            <ul class="nav navbar-right panel_toolbox">
                <a href="<?php echo base_url() ?>carriers"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Carrier Listing Page</button></a>
            </ul>
        </div>


    </div>    

</div>    

<script>

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


    $('#btnSave, #btnSaveClose').click(function () {

        $('#carrier_form').parsley().reset();

        var is_ok = $("#carrier_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            //alert('ok');



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

            //carrier_progress_timeout   carrier_ring_timeout



            if (is_ok === true)
            {
                $("#carrier_form").submit();
            }
        } else
        {
            $('#carrier_form').parsley().validate();
        }



    })



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

        diversion_header_option();
        tax_chnaged();
    });


    $("#carrier_currency_id").change(function () {

    });
</script>
<script>
    var account_id = '';
    var currency_id = '';
    var existing_value2 = '';

    $(document).ready(function () {
        currency_id = $('#carrier_currency_id').val();
        if (currency_id != '')
            destination_type_changed('tariff_id', account_id, currency_id, existing_value2);
    });

    //if on currency_id change
    $("#carrier_currency_id").change(function () {
        currency_id = $('#carrier_currency_id').val();
        destination_type_changed('tariff_id', account_id, currency_id, existing_value2);
    });



    function destination_type_changed(id_tariff, account_id, currency_id, existing_value2)
    {

        {
            data_array = {
                action: 'get_tariffs',
                account_id: account_id,
                currency_id: currency_id,
                existing_value: existing_value2,
            };
            //console.log(data_array);
            var target = BASE_URL + "ajax/ajax_get_tariff";

            $.ajax({
                method: "POST",
                url: target,
                dataType: 'json',
                data: data_array
            })
                    .done(function (msg) {
                        //console.log(msg);
                        if (typeof msg['html'] === 'undefined')
                        {
                        } else
                        {
                            $('#' + id_tariff).html(msg['html']);
                        }

                    });

        }
    }
</script>
<script>
    var account_id = '';
    var currency_id = '';
    var existing_value = '';

    $(document).ready(function () {
        currency_id = $('#carrier_currency_id').val();

    });






</script>