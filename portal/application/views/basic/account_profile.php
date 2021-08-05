<div class="">
    <div class="clearfix"></div>    

    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Profile</h2>
                <ul class="nav navbar-right panel_toolbox">
                    
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12">Account Type</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">                    
                            <?php
                            if ($data['account_type'] == 'DEMO')
                                echo '<strong>DEMO</strong>';
                            elseif ($data['account_type'] == 'TEST')
                                echo '<strong>IN-HOUSE</strong>';
                            else
                                echo '<strong>LIVE</strong>';
                            ?>	                   
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >Account ID    </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <?php echo $data['account_id']; ?>
                        </div>
                    </div>
 


                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >Currency </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <?php
                            $user_currency_display = '';
                            foreach ($currency_options as $key => $currency_array) {
                                if ($data['currency_id'] == $currency_array['currency_id']) {
                                    $user_currency_display = $currency_array['name'];
                                    break;
                                }
                            }
                            echo $user_currency_display;
                            ?>                
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >Tax Type </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                                  
                            <?php
                            $tax_type_array = array('exclusive', 'inclusive');
                            $str = '';
                            foreach ($tax_type_array as $key => $tax_type) {
                                if ($data['tax_type'] == $tax_type)
                                    $str = ucfirst($tax_type);
                            }
                            echo $str;
                            ?>                  
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >Tax Number</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">  
                            <?php echo $data['tax_number']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >Tax 1 </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">            
                            <?php
                            if ($data['tax1'] != '' && $data['tax1'] > 0)
                                echo $data['tax1'] . ' %';
                            else
                                echo "0.00" . ' %';
                            ?>
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >Tax 2  </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">               
                            <?php
                            if ($data['tax2'] != '' && $data['tax2'] > 0)
                                echo $data['tax2'] . ' %';
                            else
                                echo "0.00" . ' %';
                            ?>
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >Tax 3 </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                 
                            <?php
                            if ($data['tax3'] != '' && $data['tax3'] > 0)
                                echo $data['tax3'] . ' %';
                            else
                                echo "0.00" . ' %';
                            ?>
                        </div>              
                    </div>

                    <?php
                    $vatflag_array = array('NONE', 'REVERSE', 'SEZ');
                    ?>
                    <div class="form-group">
                        <label for="middle-name" class="col-md-4 col-sm-3 col-xs-12">VAT Flag </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                 
                            <?php
                            $str = '';
                            foreach ($vatflag_array as $key => $vat) {
                                if ($data['vat_flag'] == $vat)
                                    $str = $vat;
                            }
                            echo $str;
                            ?>               
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >DP </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">             
                            <?php echo $data['dp']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >CC </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">               
                            <?php echo $data['user_cc']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >CPS </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                
<?php echo $data['user_cps']; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12">Codec Checking</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                  
<?php echo ($data['codecs_force'] == 1) ? 'Yes' : 'No'; ?>                        

                        </div>                     
                    </div>

                    <div class="form-group">
                        <label for="middle-name" class="col-md-4 col-sm-3 col-xs-12">Codecs</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">
<?php echo $data['user_codecs']; ?>  
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">With-media</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                   
<?php echo ($data['user_media_rtpproxy'] == 1) ? 'Yes' : 'No'; ?>
                        </div> 
                    </div>

                    <div class="form-group">
                        <label for="middle-name" class="col-md-4 col-sm-3 col-xs-12">Transcoding</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
<?php echo ($data['user_media_rtpproxy_transcoding'] == 1) ? 'Yes' : 'No'; ?> 
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="middle-name" class="col-md-6 col-sm-3 col-xs-12">Dont Allow Call With Loss Route</label>
                        <div class="col-md-3 col-sm-6 col-xs-12">
<?php echo ($data['loss_carrier_check'] == 1) ? 'Yes' : 'No'; ?> 
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="middle-name" class="col-md-6 col-sm-3 col-xs-12">Reduce Channels as Balance Approaches Zero</label>
                        <div class="col-md-3 col-sm-6 col-xs-12">
<?php echo ($data['nigativebalance_cc_check'] == 1) ? 'Yes' : 'No'; ?> 
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="middle-name" class="col-md-6 col-sm-3 col-xs-12">Presentation CLI Audit</label>
                        <div class="col-md-3 col-sm-6 col-xs-12">
<?php echo ($data['account_presentation_cli_audit'] == 1) ? 'Yes' : 'No'; ?> 
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="middle-name" class="col-md-6 col-sm-3 col-xs-12">Change CLI Based On DST Prefix</label>
                        <div class="col-md-5 col-sm-6 col-xs-12">
<?php echo ($data['force_dst_src_cli_prefix'] == 1) ? 'Yes' : 'No'; ?> 
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="middle-name" class="col-md-7 col-sm-3 col-xs-12">All Concurrent Calls On Same Number</label>
                        <div class="col-md-5 col-sm-3 col-xs-12">
<?php echo $data['multicallonsameno_limit']; ?> 
                        </div>
                    </div> 

                    <div class="form-group">
                        <label for="middle-name" class="col-md-7 col-sm-3 col-xs-12">Max Call Duration (Minutes)</label>
                        <div class="col-md-5 col-sm-3 col-xs-12">  <?php echo $data['max_callduration']; ?> 
                        </div>
                    </div> 






                    <?php
                    $logged_user_type = get_logged_account_type();
                    $user_status = $data['user_status'];
                    //die($user_status);
                    $status_update_options_array = array();
                    $status_update_options_array['ADMIN'] = array(
                        '-1' => array(),
                        '1' => array(0, -2, -3),
                        '0' => array(),
                        '-2' => array(0, 1, -3),
                        '-3' => array(0, -2, 1),
                    );
                    $status_update_options_array['ACCOUNTS'] = array(
                        '-1' => array(1),
                        '1' => array(0, -2, -3),
                        '0' => array(),
                        '-2' => array(0, 1, -3),
                        '-3' => array(0, -2, 1),
                    );
                    $status_update_options_array['NOC'] = array(
                        '1' => array(0, -2, -3),
                        '-3' => array(0, -2, 1),
                    );

                    $status_name_array = array(
                         '1' => array('name' => 'Active', 'tooltip' => 'Account is active'),
                        '0' => array('name' => 'Closed', 'tooltip' => 'Account Closed'),
                        '-2' => array('name' => 'Temporarily Suspended', 'tooltip' => 'If balance is zero'),
                        '-3' => array('name' => 'Suspected Blocked', 'tooltip' => 'For suspicious activity, make user blocked'),
                        '-4' => array('name' => 'Account Closed', 'tooltip' => 'Account is closed'),
                    );
                    ?>  
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12">Status</label>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <?php
                            if (isset($status_update_options_array[$logged_user_type][$user_status])) {
                                foreach ($status_update_options_array[$logged_user_type][$user_status] as $status_value) {

                                    if (isset($status_name_array[$status_value])) {
                                        $status_name = $status_name_array[$status_value]['name'];
                                        $tooltip = $status_name_array[$status_value]['tooltip'];
                                    } else {
                                        $status_name = $status_value;
                                        $tooltip = '';
                                    }
                                    ?>

                                <?php }
                                ?>

                                <?php
                                if (isset($status_name_array[$user_status])) {
                                    $status_name = $status_name_array[$user_status]['name'];
                                    $tooltip = $status_name_array[$user_status]['tooltip'];
                                } else {
                                    $status_name = $user_status;
                                    $tooltip = '';
                                }
                                ?>
                                <div class="col-md-12 col-sm-6 col-xs-12 radio1">						
                                    <label><?php echo $status_name; ?></label>		
                                    <?php
                                    if ($tooltip != '')
                                        echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '"><i class="fa fa-question-circle"></i></a>';
                                    ?>				
                                </div>
                                <?php
                            }
                            else {
                                if (isset($status_name_array[$user_status])) {
                                    $status_name = $status_name_array[$user_status]['name'];
                                    $tooltip = $status_name_array[$user_status]['tooltip'];
                                } else {
                                    $status_name = $user_status;
                                    $tooltip = '';
                                }
                                echo '<label>' . $status_name . '</label> ';
                                if ($tooltip != '')
                                    echo '<a href="#" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '" ><i class="fa fa-question-circle"></i></a>';
                                echo '<input type="hidden" name="user_status" id="status1" value="' . $user_status . '" /></div>';
                            }
                            ?>
                        </div>

                    </div>

                </form>

            </div>



        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Login Details</h2>
                <ul class="nav navbar-right panel_toolbox"></ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form action="<?php echo base_url('customers'); ?>/edit/<?php echo param_encrypt($data['account_id']); ?>" method="post" name="edit_form" id="edit_form" data-parsley-validate class="form-horizontal form-label-left">
                    <div class="form-group">
                        <label class=" col-md-4 col-sm-3 col-xs-12" >Name </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                  
                            <?php echo $data['name']; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" col-md-4 col-sm-3 col-xs-12" >Company </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                
                            <?php echo $data['company_name']; ?>

                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" col-md-4 col-sm-3 col-xs-12" >Email Address</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">               
                            <?php echo $data['emailaddress']; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" col-md-4 col-sm-3 col-xs-12" >Address </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                
                            <?php echo $data['address']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >Phone Number </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                
                            <?php echo $data['phone']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >Country </label>
                        <div class="col-md-7 col-sm-6 col-xs-12">                               
                            <?php
                            $str = '';
                            foreach ($country_options as $key => $country_array) {
                                if ($data['country_id'] == $country_array->country_id) {
                                    $str = $country_array->country_name;
                                    break;
                                }
                            }
                            echo $str;
                            ?>

                        </div>
                    </div>              
                    <?php if ($data['country_id'] == '100') {
                        ?>
                        <div class="form-group">
                            <label class="col-md-4 col-sm-3 col-xs-12" >State </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">                                
                                <?php
                                $str = '';
                                foreach ($state_options as $key => $state_array) {
                                    if ($data['state_code_id'] == $state_array['state_code_id']) {
                                        $str = $state_array['state_name'];
                                        break;
                                    }
                                }
                                echo $str;
                                ?>

                            </div>
                        </div>
                    <?php } ?> 
                    <div class="form-group">
                        <label class="col-md-4 col-sm-3 col-xs-12" >PIN</label>
                        <div class="col-md-7 col-sm-6 col-xs-12">  
                            <?php echo $data['pincode']; ?> 
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>


</div>
