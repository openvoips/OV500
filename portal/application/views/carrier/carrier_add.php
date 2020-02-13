<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0
// License https://www.gnu.org/licenses/agpl-3.0.html
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################
-->
<!-- Parsley -->
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$tab_index = 1;
$dp = 4;
$vatflag_array = array('NONE', 'TAX', 'VAT');

//echo '<pre>';
//print_r($provider_data);
//print_r($tariff_options);
//print_r($currency_options);
//echo '</pre>';
?>    
<div class="">
    <div class="clearfix"></div>    

    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Carrier Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li>                    
                    <a href="<?php echo base_url() ?>carriers"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Carrier Listing Page</button></a>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_content">
                <br />
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

                    <div class="form-group" >
                        <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Carrier Type <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <select name="carrier_type" id="carrier_type" class="form-control data-search-field" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                <option value="INBOUND"  <?php echo set_select('carrier_type', 'inbound', true); ?>>DID Provider</option>
                                <option value="OUTBOUND"  <?php echo set_select('carrier_type', 'outbound'); ?>>VoIP Minute Provider</option>
                            </select>
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
                                    $str .= '<option value="' . $currency_array['currency_id'] . '" ' . $selected . '>' . $currency_array['symbol']." - ".$currency_array['name'] . '</option>';
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
                        <label class="control-label col-md-4 col-sm-3 col-xs-12">Provider <span class="required">*</span></label>
                        <div class="col-md-7 col-sm-6 col-xs-12"> 
                            <select name="provider_id" id="provider_id" class="form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                <option value="" >Select</option> 
                            </select>
                            <div class="hide">
                                <select name="provider_id_name_all" id="provider_id_name_all">  
                                    <?php
                                    $str = '';
                                    $str_array = array();
                                    if (isset($provider_data['result']) && count($provider_data['result']) > 0) {
                                        foreach ($provider_data['result'] as $provider_array) {
                                            $selected = ' ';
                                            if ($server_data['provider_id'] == $provider_array['provider_id'])
                                                $selected = '  selected="selected" ';

                                            $currency_id = $provider_array['currency_id'];
                                            $str_array[$currency_id][] = '<option value="' . $provider_array['provider_id'] . '" ' . $selected . '>' . $provider_array['provider_name'] . '</option>';
                                        }
                                    }

                                    foreach ($currency_options as $currency_array) {
                                        $currency_id = $currency_array['currency_id'];
                                        $str .= '<OPTGROUP LABEL="' . $currency_id . '" id="group_all_' . $currency_id . '">';
                                        if (isset($str_array[$currency_id])) {
                                            foreach ($str_array[$currency_id] as $key => $str_array_temp) {
                                                $str .= $str_array_temp;
                                            }
                                        }
                                        $str .= ' </OPTGROUP> ';
                                    }
                                    echo $str;
                                    ?>
                                </select>  
                            </div> 
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
                    ?>
                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Codecs List</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <?php
                            foreach ($codecs_array as $key => $codec) {
                                $checked = '';

                                if ($codec == 'G729')
                                    $checked = true;
                                else
                                    $checked = true;

                                echo '<div class="checkbox">' .
                                '<label><input type="checkbox" name="codecs[]" id="codec' . $key . '" value="' . $codec . '"  tabindex="' . $tab_index++ . '"' . set_checkbox('codecs[]', $codec, $checked) . '/> ' . $codec . '</label>' .
                                '</div>';
                            }
                            ?>  
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="radio">
                                <label><input type="radio" name="carrier_status" id="status1" value="1" <?php echo set_radio('carrier_status', '1', TRUE); ?> /> Active</label>
                                <label><input type="radio" name="carrier_status" id="status0" value="0" <?php echo set_radio('carrier_status', '0'); ?> /> Inactive</label>
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


    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Carrier Configuration Management Form End</h2>
            <ul class="nav navbar-right panel_toolbox">             
                <li>                    
                    <a href="<?php echo base_url() ?>carriers"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Carrier Listing Page</button></a>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>


</div>    
<script>
    var tariff_array = [];
    tariff_array[1] = new Array();
    tariff_array[2] = new Array();
    tariff_array[3] = new Array();
    tariff_array[4] = new Array();
    tariff_array[5] = new Array();
</script>
<?php
$k = 0;
if (count($tariff_options) > 0) {
    foreach ($tariff_options as $tariff_name_array) {
        ?>
        <script>
            tariff_array[<?php echo $tariff_name_array['tariff_currency_id']; ?>]["<?php echo $k; ?>"] = ["<?php echo $tariff_name_array['tariff_id']; ?>", "<?php echo $tariff_name_array['tariff_name']; ?>"];
        </script>
        <?php
        $k++;
    }
}
?>

<script>

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
            })

            ;


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



    function carrier_type_changed()
    {
        if ($("#carrier_type").val() == 'INBOUND')
        {
            $('#id_incoming_cdr_billing_div').show();
        } else
        {
            $('#id_incoming_cdr_billing_div').hide();
        }

    }

    function currency_changed()
    {
        var tariff_str = '<option value="">Select</option>';
        carrier_currency_id = $("#carrier_currency_id").val();
        var arrayLength = tariff_array[carrier_currency_id].length;

        if (arrayLength > 0)
        {
            for (var i in tariff_array[carrier_currency_id])
            {
                tariff_id = tariff_array[carrier_currency_id][i][0];
                tariff_name = tariff_array[carrier_currency_id][i][1];

                tariff_str = tariff_str + '<option value="' + tariff_id + '" selected="selected">' + tariff_name + '</option>';
            }
        }

        $('#tariff_id').html(tariff_str);



        ////supplier
        var group_name_options_id = '#group_all_' + carrier_currency_id;
        var group_name_options_html = $(group_name_options_id).html();

        group_name_options_html = '<option value="" >Select</option> ' + group_name_options_html;
        $('#provider_id').html(group_name_options_html);
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
        //$( "#menu_toggle" ).trigger( "click" );
        //currency_changed();
        carrier_type_changed();
         tax_chnaged();
    });

    $("#carrier_type").change(function () {
        carrier_type_changed();
    });
    $("#carrier_currency_id").change(function () {
        currency_changed();
    });
</script>
