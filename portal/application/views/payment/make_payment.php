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
<?php
//print_r($user_result);
//echo '<pre>';print_r($payment_gateways_result);echo '</pre>';
$dp = 2;
?>
<style>
    #st-payment input.st-error {
        background-color: #ffc6c7;
        border: 2px solid #ffb5b5;
    }
    #st-message .st-error {
        color: #E9EDEF;
        background-color: rgba(231,76,60,.88);
        border-color: rgba(231,76,60,.88);
        padding: 15px;
    }
    .span-remove{color:maroon;  margin-left:20px; font-size:16px;}
    a.remove{color:maroon;}				
</style>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script>
    var account_id = "<?php echo $user_result['account_id']; ?>";
</script>
<div class="col-md-9 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Make Payment</h2>
            <ul class="nav navbar-right panel_toolbox">
                
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content" id="id_x_content">
            <div id="st-message" class=" fade in"  ></div>
            <?php
            if (isset($payment_gateways_result['result']) && count($payment_gateways_result['result']) > 0) {
                ?>			
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Payment Method</label>
                    <div class="col-md-7 col-sm-6 col-xs-12">
                        <select name="payment_method" id="payment_method" class="form-control" tabindex="<?php echo $tab_index++; ?>" onChange="payment_method_changed()">
                            <option value="">Select</option>                    
                            <?php
                            $k = 0;
                            $gateway_str = '';
                            $valid_payment_gateways_result = array();
                            foreach ($payment_gateways_result['result'] as $payment_gateway_array) {
                                $credentials = json_decode($payment_gateway_array['credentials'], true);
                                if ($payment_gateway_array['payment_method'] == 'paypal-client') {
                                    
                                } elseif ($payment_gateway_array['payment_method'] == 'ccavenue' && $user_result['currency']['name'] == 'INR') {
                                    $selected = ' ';
                                    $gateway_str .= '<option value="' . $payment_gateway_array['payment_method'] . '" ' . $selected . '>CCAvenue</option>';
                                    $valid_payment_gateways_result[] = $payment_gateway_array;
                                } elseif ($payment_gateway_array['payment_method'] == 'paypal-sdk' && isset($credentials['pdt_identity_token']) && isset($credentials['business'])) {
                                    $selected = ' ';
                                    $gateway_str .= '<option value="' . $payment_gateway_array['payment_method'] . '" ' . $selected . '>Paypal</option>';
                                    $valid_payment_gateways_result[] = $payment_gateway_array;
                                } elseif ($payment_gateway_array['payment_method'] == 'secure-trading') {
                                    $selected = ' ';
                                    $gateway_str .= '<option value="' . $payment_gateway_array['payment_method'] . '" ' . $selected . '>Debit / Credit Card</option>';
                                    $valid_payment_gateways_result[] = $payment_gateway_array;
                                }
                                $k++;
                            }
                            echo $gateway_str;
                            ?>
                        </select>
                    </div>
                </div>

                <br><br>

                <?php
                foreach ($valid_payment_gateways_result as $payment_gateway_array) {
                    $payment_method = $payment_gateway_array['payment_method'];
                    $credentials = json_decode($payment_gateway_array['credentials'], true);
                    echo '<div id="id_div_' . $payment_method . '" class="id_div_payment_method_class col-md-12 col-sm-12 col-xs-12 hide" style="padding:0px;">';
                    if ($payment_method == 'ccavenue') {
                        ?>	
                        <form action="" method="post" name="pay_form" id="<?php echo $payment_method; ?>_pay_form" data-parsley-validate class="form-horizontal form-label-left">
                            <input type="hidden" name="action" value="OkPay"> 
                            <input type="hidden" name="method" id="<?php echo $payment_method; ?>_method" value="<?php echo $payment_method; ?>" />
                            <div class="form-group" >
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Amount">Amount</label>
                                <div class="col-md-7 col-sm-6 col-xs-12">
                                    <input type="text" name="amount" id="<?php echo $payment_method; ?>amount" value=""  class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-pattern="/^\d+(\.\d{1,<?php echo $dp; ?>})?$/" data-parsley-pattern-message="Positive number with maximum <?php echo $dp; ?> decimal"  >
                                </div>
                            </div>   
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                                <div class="col-md-7 col-sm-6 col-xs-12 ">			
                                    <button type="button" id="<?php echo $payment_method; ?>-button" class="btn btn-success btn-lg active btn-block"><strong>Submit</strong></button>
                                </div>
                            </div>  

                        </form>           
                        <script>
                            /*submit form*/
                            $('#<?php echo $payment_method; ?>-button').click(function () {
                                $("#<?php echo $payment_method; ?>_pay_form").parsley().reset();
                                var is_ok = $("#<?php echo $payment_method; ?>_pay_form").parsley().isValid();
                                if (is_ok === true)
                                {
                                    $("#<?php echo $payment_method; ?>_pay_form").submit();
                                } else
                                {
                                    $("#<?php echo $payment_method; ?>_pay_form").parsley().validate();
                                }

                            });
                        </script>    
                        <?php
                    }//if
                    elseif ($payment_method == 'paypal-sdk') {
                        ?>	
                        <form action="" method="post" name="pay_form" id="<?php echo $payment_method; ?>_pay_form" data-parsley-validate class="form-horizontal form-label-left">
                            <input type="hidden" name="action" value="OkPay"> 
                            <input type="hidden" name="method" id="<?php echo $payment_method; ?>_method" value="<?php echo $payment_method; ?>" />
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Amount">Amount</label>
                                <div class="col-md-7 col-sm-6 col-xs-12">
                                    <input type="text" name="amount" id="<?php echo $payment_method; ?>amount" value=""  class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-pattern="/^\d+(\.\d{1,<?php echo $dp; ?>})?$/" data-parsley-pattern-message="Positive number with maximum <?php echo $dp; ?> decimal"  >
                                </div>
                            </div>   
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                                <div class="col-md-7 col-sm-6 col-xs-12 ">			
                                    <button type="button" id="<?php echo $payment_method; ?>-button" class="btn btn-primary btn-lg active btn-block"><strong>Submit</strong></button>
                                </div>
                            </div>  

                        </form>           
                        <script>
                            /*submit form*/
                            $('#<?php echo $payment_method; ?>-button').click(function () {
                                $("#<?php echo $payment_method; ?>_pay_form").parsley().reset();
                                var is_ok = $("#<?php echo $payment_method; ?>_pay_form").parsley().isValid();
                                if (is_ok === true)
                                {
                                    $("#<?php echo $payment_method; ?>_pay_form").submit();
                                } else
                                {
                                    $("#<?php echo $payment_method; ?>_pay_form").parsley().validate();
                                }

                            });
                        </script>         
                        <?php
                    } elseif ($payment_method == 'secure-trading') {
                        ?>	

                        <form action="" method="post" name="pay_form" id="<?php echo $payment_method; ?>_pay_form" data-parsley-validate class="form-horizontal form-label-left">
                            <input type="hidden" name="method" id="<?php echo $payment_method; ?>_method" value="<?php echo $payment_method; ?>" />
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Amount">Amount</label>
                                <div class="col-md-7 col-sm-6 col-xs-12">
                                    <input type="text" name="amount" id="<?php echo $payment_method; ?>amount" value=""  class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-pattern="/^\d+(\.\d{1,<?php echo $dp; ?>})?$/" data-parsley-pattern-message="Positive number with maximum <?php echo $dp; ?> decimal"  >
                                </div>
                            </div>                      

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Card Number</label>
                                <div class="col-md-7 col-sm-6 col-xs-12 "><input type="text" data-st-field="pan" class="form-control col-md-7 col-xs-12" data-parsley-required="" autocomplete="off" id="id_card_number"/></div>
                            </div> 

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Expiry Month</label>
                                <div class="col-md-7 col-sm-6 col-xs-12 ">
                                    <select data-st-field="expirymonth" class="form-control col-md-7 col-xs-12" data-parsley-required=""  id="id_expirymonth">
                                        <option value="">Select</option>                    
                                        <?php
                                        $month_array = range(1, 12);
                                        $str = '';
                                        foreach ($month_array as $month_value) {
                                            $selected = ' ';
                                            $month_value = sprintf('%02d', $month_value);
                                            $str .= '<option value="' . $month_value . '" ' . $selected . '>' . $month_value . '</option>';
                                        }
                                        echo $str;
                                        ?>
                                    </select>                        
                                </div>
                            </div> 


                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Expiry Year</label>
                                <div class="col-md-7 col-sm-6 col-xs-12 ">
                                    <select data-st-field="expiryyear" class="form-control col-md-7 col-xs-12" data-parsley-required="" id="id_expiryyear"/>                        
                                    <option value="">Select</option>                    
                                    <?php
                                    $j = $current_year = date('Y') - 1;
                                    $str = '';

                                    while ($j++ < $current_year + 9) {
                                        $selected = ' ';
                                        $str .= '<option value="' . $j . '" ' . $selected . '>' . $j . '</option>';
                                    }
                                    echo $str;
                                    ?>
                                    </select>
                                </div>
                            </div> 
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Security Code</label>
                                <div class="col-md-7 col-sm-6 col-xs-12 "><input type="text" data-st-field="securitycode" class="form-control col-md-7 col-xs-12" data-parsley-required="" autocomplete="off" id="id_securitycode"/></div>
                            </div> 



                            <!-- --->
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Save Card Details</label>
                                <div class="col-md-1 col-sm-1 col-xs-1"><input type="checkbox" id="id_save_card_details" class="form-control col-md-7 col-xs-12" /></div>
                                <div class="col-md-6 col-sm-6 col-xs-6">						
                                </div>
                            </div>
                            <!-- --->

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                                <div class="col-md-7 col-sm-6 col-xs-12"><button type="button" id="<?php echo $payment_method; ?>-button" class="btn btn-warning btn-lg active btn-block"><strong>Submit</strong></button></div>
                            </div> 				  

                            <div class="hide">
                                <input type="submit" name="mybtn" id="mybtn" value="Submit" />
                            </div>

                        </form>  

                        <script src="https://webservices.securetrading.net/js/st.js"></script>        
                        <script>
                            var payment_method = "<?php echo $payment_method; ?>";
                            var sitereference = "<?php echo $credentials['sitereference']; ?>";
                        </script>
                        <script src="<?php echo base_url() ?>theme/default/js/payment.js"></script>
                        <script>


                        </script>
                        <?php
                    }//if
                    echo '</div>';
                }
                ?>

                <?php
            }//if(isset($payment_gateways_result['result']) && count($payment_gateways_result['result'])>0)
            ?>




        </div>
    </div>
</div>    


<div class="col-md-3 col-sm-6 col-xs-12 hide" id="id_div_saved_cards">
    <div class="x_panel">
        <div class="x_title">
            <h2>Saved Card List</h2>
            <ul class="nav navbar-right panel_toolbox">
                
            </ul>
            <div class="clearfix"></div>
        </div>


        <div class="x_content">
            <?php
            if (count($saved_card_result) > 0) {
                echo '<ul class="to_do">';

                $j = 1;
                foreach ($saved_card_result as $saved_card_data) {
                    echo '<li><a href="javascript:void(0)" id="id_li_card_' . $j . '" onclick="populate_card_data(\'id_li_card_' . $j . '\')" 
					data_card_number="' . $saved_card_data['card_number'] . '"  
					data_expirymonth="' . $saved_card_data['expirymonth'] . '" 
					data_expiryyear="' . $saved_card_data['expiryyear'] . '"  	
					data_securitycode="' . $saved_card_data['securitycode'] . '"							
					>' . $saved_card_data['card_name'] . '</a>';
                    ?>								
                    <span class="span-remove">
                        <a  class="remove" href="javascript:void(0);"  
                            onclick="doConfirmDelete('<?php echo $saved_card_data['id']; ?>', 'payment/remove_card_details', '<?php echo $saved_card_data['account_id']; ?>')"
                            title="Delete"><i class="fa fa-trash"></i></a>
                    </span>
                    <?php
                    echo '</li>';
                    $j++;
                }
                echo '</ul>';
            }
            ?>
        </div>
    </div>
</div>		
<script>
    var payment_method_t = '';
    function payment_method_changed()
    {
        payment_method_t = $('#payment_method').val();
        div_id = 'id_div_' + payment_method_t;

        $('.id_div_payment_method_class').addClass('hide');
        $('#' + div_id).removeClass('hide');

        $('#st-message').html('');


        if (payment_method_t == 'secure-trading')
        {
            $('#id_div_saved_cards').removeClass('hide');
        } else
        {
            $('#id_div_saved_cards').addClass('hide');
        }

    }


    function populate_card_data(li_id)
    {
        var val = $('#' + li_id).attr('data_card_number');
        $('#id_card_number').val(val);

        val = $('#' + li_id).attr('data_expirymonth');
        $('#id_expirymonth').val(val);

        val = $('#' + li_id).attr('data_expiryyear');
        $('#id_expiryyear').val(val);

        val = $('#' + li_id).attr('data_securitycode');
        $('#id_securitycode').val(val);

    }


</script>  