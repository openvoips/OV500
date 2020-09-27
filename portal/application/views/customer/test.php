<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0.3
// License https://www.gnu.org/licenses/agpl-3.0.html
//
//
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script>
    $(document).ready(function () {
        $('.combobox').combobox()
    });
</script>
<?php
$tab_index = 1;

$cli_group_options = $cli_group_options['result'];


if (isset($available_cli_data['result']) && count($available_cli_data['result']) > 0) {
    $available_cli_data = $available_cli_data['result'];
} else
    $available_cli_data = array();


/* if(isset($assigned_cli_data['result']) && count($assigned_cli_data['result'])>0)
  {
  $assigned_cli_data = current($assigned_cli_data['result']);
  }
  else
  $assigned_cli_data = array(); */

//echo '<pre>';
//print_r($assigned_cli_data);
//print_r($cli_group_options);
//print_r($available_cli_data);
//print_r($logged_user_result);
//print_r($tariff_options);	
//echo '</pre>';		
?>


<div class="">
    <div class="clearfix"></div>      

    <div class="col-md-4 col-sm-12 col-xs-12">
        <div class="col-md-12 col-sm-12 col-xs-12" style="padding:0;">
            <div class="x_panel">
                <div class="x_title">
                    <h2>End User Details</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />
                    <form action="" method="post" class="form-horizontal form-label-left">
                        <div class="form-group">
                            <label class=" col-md-5 col-sm-3 col-xs-12" for="first-name">Account ID</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">: <?php echo $data['account_id']; ?></div>
                        </div>

                        <div class="form-group">
                            <label class=" col-md-5 col-sm-3 col-xs-12" for="first-name">Name <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">: <?php echo $data['name']; ?></div>
                        </div>

                        <div class="form-group">
                            <label class=" col-md-5 col-sm-3 col-xs-12" for="last-name">Company <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">: <?php echo $data['company_name']; ?></div>
                        </div>




                    </form>
                </div>
            </div>


        </div>
    </div>  
    <div class="col-md-8 col-sm-12 col-xs-12">  
        <div class="x_panel">
            <div class="x_title">
                <h2>Add CLI Group</h2>
                <ul class="nav navbar-right panel_toolbox">
                    

                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <?php
                if (count($country_options['result']) == 0) {
                    ?>
                    <label class="col-md-12 col-sm-12 col-xs-12 text-center text-danger">You don't have required services</label>		

                    <div class="clearfix"></div>
                    <br /><br />
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($enduser_type . 's') ?>/cli_lookup/<?php echo param_encrypt($data['account_id']); ?>"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Cancel</button></a>		
                    </div>		
                    <?php
                } elseif (count($available_cli_data) == 0) {
                    ?>
                    <label class="col-md-12 col-sm-12 col-xs-12 text-center text-danger">No More CLI Available</label>		

                    <div class="clearfix"></div>
                    <br /><br />
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                        <a href="<?php echo base_url($enduser_type . 's') ?>/cli_lookup/<?php echo param_encrypt($data['account_id']); ?>"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Cancel</button></a>		
                    </div>		
                    <?php
                } else {
                    ?>


                    <form action="<?php echo base_url($enduser_type . 's'); ?>/cli_lookup_add/<?php echo param_encrypt($data['account_id']); ?>" method="post" name="cli_form" id="cli_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="action" value="OkAddCli"> 
                        <input type="hidden" name="account_id" value="<?php echo $data['account_id']; ?>"/>
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <div class="form-group">
                            <label class="col-md-3 col-sm-3 col-xs-12" for="first-name">Select Country</label>
                            <div class="col-md-9 col-sm-6 col-xs-12">                
                                <select name="country_abbr" id="country_abbr" class="form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="" >Select</option>                    
                                    <?php
                                    $str = '';
                                    foreach ($country_options['result'] as $key => $country_array) {
                                        $selected = ' ';
                                        $str .= '<option value="' . $country_array['country_abbr'] . '" ' . $selected . '>' . $country_array['country_name'] . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                </select>
                            </div>
                        </div>   

                        <div class="form-group">
                            <label class="col-md-3 col-sm-3 col-xs-12" for="first-name">Select Group</label>
                            <div class="col-md-9 col-sm-6 col-xs-12">
                                <select name="group_id" id="group_id" class="form-control" tabindex="<?php echo $tab_index++; ?>">


                                </select>
                            </div>
                        </div>  

                        <div class="form-group hide">
                            <label class="col-md-3 col-sm-3 col-xs-12" for="first-name">Group ALL</label>
                            <div class="col-md-9 col-sm-6 col-xs-12">
                                <select name="group_all" id="group_all" class="form-control"  tabindex="<?php echo $tab_index++; ?>">
                                    <option value="" >Select</option>         
                                    <?php
                                    $str = '';
                                    foreach ($cli_group_options as $country_abbr => $country_array) {
                                        $str .= '<OPTGROUP LABEL="' . $country_abbr . '" id="group_all_' . $country_abbr . '">';
                                        foreach ($country_array as $key => $group_array) {
                                            $selected = ' ';
                                            $str .= '<option value="' . $group_array['group_name'] . '" ' . $selected . ' >' . $group_array['group_name'] . '</option>';
                                        }
                                        echo ' </OPTGROUP> ';
                                    }
                                    echo $str;
                                    ?>                           
                                </select>
                            </div>
                        </div>  
                        <div class="ln_solid"></div> 
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Group Name <span class="required">*</span> </label>
                            <div class="col-md-9 col-sm-6 col-xs-12">
                                <input type="text" name="group_name" id="group_name" value="<?php echo set_value('group_name'); ?>"  data-parsley-required="" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><span id="id_postcode_prefix">Prefixes</span> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-6 col-xs-12">
                                <textarea name="prefixes" id="prefixes" class="form-control col-md-7 col-xs-12" rows="10" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>"><?php echo set_value('prefixes'); ?></textarea>
                                <small>(comma or new line separated)</small>
                            </div>
                        </div>

                        <div id="cli_div">
                            <?php
                            $cli_string_temp = '  
              <div class="form-group" id="cli_div_1">
                    <label class="col-md-3 col-sm-3 col-xs-12">CLI <span class="required">*</span></label>
                    <div class="col-md-7 col-sm-10 col-xs-10">
                        <select name="cli[]" id="cli_1" class="combobox form-control" data-parsley-required="" >
                       <option value="">Select</option>';

                            foreach ($available_cli_data as $did_array) {
                                $did_number_dispaly = $did_array['did_number'];
                                if ($did_array['did_name'] != '')
                                    $did_number_dispaly = $did_number_dispaly . ' [' . $did_array['did_name'] . ']';

                                $cli_string_temp .= '<option value="' . $did_array['did_number'] . '">' . $did_number_dispaly . '</option>';
                            }
                            $cli_string_temp .= ' 	      
                       </select>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                   
                    </div>
             </div> ';
                            echo $cli_string_temp;
                            ?>

                        </div>

                        <div class="ln_solid"></div>    
                        <div class="form-group">
                            <div class="col-md-12 col-sm-6 col-xs-12 text-right">	                  	
                                <button type="button" id="add_cli_row" class="btn btn-dark active btn-sm" tabindex="<?php echo $tab_index++; ?>">Add Another CLI</button>             
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-9 col-sm-6 col-xs-12 col-md-offset-3 ">	  
                                <a href="<?php echo base_url($enduser_type . 's') ?>/cli_lookup/<?php echo param_encrypt($data['account_id']); ?>""><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Cancel</button></a>	
                                <button type="button" id="cli_form_btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save</button>
                                <button type="button" id="cli_form_btnSaveClose" class="btn btn-info" tabindex="<?php echo $tab_index++; ?>">Save & Close</button>
                            </div>
                        </div>

                    </form>   

                    <?php
                    $cli_string = '  
              <div class="form-group" id="cli_div_REPLACE">
                    <label class="col-md-3 col-sm-3 col-xs-12">CLI <span class="required">*</span></label>
                    <div class="col-md-7 col-sm-10 col-xs-10">
                        <select name="cli[]" id="cli_REPLACE" class="combobox form-control" data-parsley-required="" >
                       <option value="">Select</option>';

                    foreach ($available_cli_data as $did_array) {
                        $did_number_dispaly = $did_array['did_number'];
                        if ($did_array['did_name'] != '')
                            $did_number_dispaly = $did_number_dispaly . ' [' . $did_array['did_name'] . ']';

                        $cli_string .= '<option value="' . $did_array['did_number'] . '">' . $did_number_dispaly . '</option>';
                    }
                    $cli_string .= ' 	      
                       </select>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                    <button type="button" class="btn btn-danger active btn-block" onclick="removeCliRow(REPLACE)"><i class="fa fa-remove"></i></button>
                    </div>
             </div> ';
                    $cli_string = trim(preg_replace('/\s+/', ' ', $cli_string));
                    $cli_string = str_replace("'", '', $cli_string);
                    ?>


                    <script>
                        var cli_string = '<?php echo $cli_string; ?>';
                        var cli_count = 2;
                        function addCliRow( )
                        {
                            cli_row = cli_string.replace(/REPLACE/g, cli_count);

                            $("#cli_div").append(cli_row);
                            $('#cli_' + cli_count).combobox();
                            cli_count = cli_count + 1;
                        }
                        function removeCliRow(row_id)
                        {
                            var row_div = "cli_div_" + row_id;
                            $('#' + row_div).remove();
                        }

                        $("#add_cli_row").click(function () {
                            addCliRow( );
                        });

                        //////////////////////////////
                        $("#country_abbr").change(function () {
                            var country_selected = $("#country_abbr").val();

                            var group_name_options_id = '#group_all_' + country_selected;
                            var group_name_options_html = $(group_name_options_id).html();

                            group_name_options_html = '<option value="" >Select</option> ' + group_name_options_html;
                            $('#group_id').html(group_name_options_html);

                            if (country_selected == 'GBR')
                                $('#id_postcode_prefix').html('Postcodes');
                            else
                                $('#id_postcode_prefix').html('Prefixes');

                            $("#group_id").trigger("change");

                        });

                        $("#group_id").change(function () {
                            var country_selected = $("#country_abbr").val();
                            var element = $('#group_id').find('option:selected');
                            var group_name_selected = $('#group_id').val();
                            if (element.val() != '')
                            {
                                $('#group_name').val(group_name_selected);


                                $('#prefixes').val('');


                                var target_url = BASE_URL + "endusers/get_prefixes_by_group";


                                var data_post = {
                                    country: country_selected,
                                    group_name: group_name_selected,
                                };

                                $.ajax({
                                    url: target_url,
                                    dataType: 'json',
                                    type: 'POST',
                                    data: data_post,
                                    success: function (data, textStatus, XMLHttpRequest)
                                    {
                                        console.log(data);
                                        if (data == 'error')
                                        {
                                            alert("error");
                                        } else
                                        {
                                            $('#prefixes').val(data);
                                        }
                                    },
                                    error: function (XMLHttpRequest, textStatus, errorThrown)
                                    {
                                        alert("error");
                                    }
                                });











                                console.log(target_url);
                                /* $.get(target_url, function (data, status) {//alert("start 2");
                                     
                                 console.log(data);
                                     
                                 $('#prefixes').val(data);
                                     
                                     
                                 });*/





















                            } else
                            {
                                $('#prefixes').val('');
                                $('#group_name').val('');
                            }

                        });


                        //////////////////////
                        $('#cli_form_btnSave, #cli_form_btnSaveClose').click(function () {
                            var is_ok = $("#cli_form").parsley().isValid();
                            if (is_ok === true)
                            {
                                var clicked_button_id = this.id;
                                if (clicked_button_id == 'cli_form_btnSaveClose')
                                    $('#button_action').val('save_close');
                                else
                                    $('#button_action').val('save');

                                $("#cli_form").submit();
                            } else
                            {
                                $('#cli_form').parsley().validate();
                            }
                        });
                    </script>

                    <?php
                }//else
                ?>      

            </div>

        </div>


        <!----->



    </div>

    <div class="clearfix"></div>

</div>