<?php
$dst_type = "";
$dst_point = "";
$dst_id = "";
if (isset($did_data['did_dst'])) {
    $dst_type = $did_data['did_dst']['dst_type'];
    $dst_point = $did_data['did_dst']['dst_destination'];
    $dst_id = $did_data['did_dst']['did_dst_id'];

    $dst_type2 = $did_data['did_dst']['dst_type2'];
    $dst_point2 = $did_data['did_dst']['dst_destination2'];
}
//echo '<pre>';
//print_r($did_data);
//
//print_r($did_enduser);
//echo '</pre>';
?>
<!-- Parsley -->
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$tab_index = 1;
?>    


<div class="container-fluid">
    <div class="block-header">
        <h2>DID Configuration Management</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url('dids') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to DID Listing Page</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">                 



                    <form action="<?php echo base_url(); ?>dids/config/<?php echo param_encrypt($did_data['did_id']); ?>" method="post" name="did_form" id="did_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="did_id" value="<?php echo $did_data['did_id']; ?>"> 
                        <input type="hidden" name="did_number" value="<?php echo $did_data['did_number']; ?>"> 
                        <input type="hidden" name="dst_id" value="<?php echo $dst_id; ?>"/>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">DID</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="carrier_id_display" id="carrier_id_display" value="<?php echo $did_data['did_number']; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Destination Type <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select class="form-control" name="dst_type" id="dst_type" tabindex="<?php echo $tab_index++; ?>" data-parsley-required="">
                                    <option value="">Select Type</option>
                                    <option value="CUSTOMER" <?php if ($dst_type == "CUSTOMER") echo 'selected'; ?>>USER Based</option>
                                    <option value="IP" <?php if ($dst_type == "IP") echo 'selected'; ?>>IP Based</option>
                                    <option value="PSTN" <?php if ($dst_type == "PSTN") echo 'selected'; ?>>PSTN Number</option>
                                    <option value="CAMPAINGN" <?php if ($dst_type == "CAMPAINGN") echo 'selected'; ?>>Campaign</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Destination Endpoint <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <select class="form-control" name="dst_point_ip" id="dst_point_ip" tabindex="<?php echo $tab_index++; ?>" <?php
                                if ($dst_type != 'IP')
                                    echo 'style="display:none;"';
                                else
                                    echo 'data-parsley-required';
                                ?>>
                                    <option value="">Select IP Address</option>
                                    <?php
                                    if (count($did_enduser['ip']) > 0) {
                                        foreach ($did_enduser['ip'] as $k => $ip_data) {
                                            if ($ip_data['ip_status'] == '1'):
                                                ?>
                                                <option value="<?php echo $ip_data['ipaddress']; ?>" <?php if ($dst_point == $ip_data['ipaddress']) echo 'selected'; ?>><?php echo $ip_data['ipaddress']; ?></option>	
                                                <?php
                                            endif;
                                        }
                                    }
                                    ?>
                                </select>



                                <select class="form-control" name="dst_campaign_id" id="dst_campaign_id" tabindex="<?php echo $tab_index++; ?>" <?php
                                if ($dst_type != 'CAMPAINGN')
                                    echo 'style="display:none;"';
                                else
                                    echo 'data-parsley-required';
                                ?>>
                                    <option value="">Select Campaign</option>
                                    <?php
                                    if (count($did_enduser['CAMPAINGN']) > 0) {
                                        foreach ($did_enduser['CAMPAINGN'] as $k => $cmp_data) {
                                            ?>
                                            <option value="<?php echo $cmp_data['campaign_id']; ?>" <?php if ($dst_point == $cmp_data['campaign_id']) echo 'selected'; ?>><?php echo $cmp_data['campaign_name']; ?></option>	
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>   




                                <select class="form-control" name="dst_point_sip" id="dst_point_sip" tabindex="<?php echo $tab_index++; ?>"  <?php
                                if ($dst_type != 'CUSTOMER')
                                    echo 'style="display:none;"';
                                else
                                    echo 'data-parsley-required';
                                ?>>
                                    <option value="">Select User</option>
                                    <?php
                                    if (count($did_enduser['sipuser']) > 0) {
                                        foreach ($did_enduser['sipuser'] as $k => $sip_data) {
                                            if ($sip_data['status'] == '1'):
                                                ?>
                                                <option value="<?php echo $sip_data['username']; ?>" <?php if ($dst_point == $sip_data['username']) echo 'selected'; ?>><?php echo $sip_data['username']; ?></option>	
                                                <?php
                                            endif;
                                        }
                                    }
                                    ?>
                                </select>


                                <input type="text" name="dst_point_pstn" id="dst_point_pstn" value="<?php if ($dst_type == 'PSTN') echo $dst_point; ?>" class="form-control col-md-7 col-xs-12" tabindex="<?php echo $tab_index++; ?>" <?php
                                if ($dst_type != 'PSTN')
                                    echo 'style="display:none;"';
                                else
                                    echo 'data-parsley-required';
                                ?>>
                            </div>
                        </div>








                        <div class="ln_solid"></div>

                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">

                                <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info" tabindex="<?php echo $tab_index++; ?>">Save & Go back to DID Listing Page</button>
                            </div>
                        </div>				
                    </form>
                </div>
            </div>
        </div>
        <div class="block-header">
            <h2>DID Configuration Management</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a href="<?php echo base_url('dids') ?>"><button class="btn btn-danger" type="button" tabindex="<?php echo $tab_index++; ?>">Back to DID Listing Page</button></a> </li>
            </ul>
        </div>
    </div>    
    <script>
        window.Parsley
                .addValidator('price', {
                    validateString: function (value) {
                        return true == (/^\d+(?:[.,]\d+)*$/.test(value));
                    },
                    messages: {
                        en: 'This value should be in price format'
                    }
                }
                );

        $(document).ready(function () {

        });



        $('#btnSave, #btnSaveClose').click(function () {
            var is_ok = $("#did_form").parsley().isValid();
            if (is_ok === true)
            {
                var clicked_button_id = this.id;
                if (clicked_button_id == 'btnSaveClose')
                    $('#button_action').val('save_close');
                else
                    $('#button_action').val('save');

                $("#did_form").submit();
            } else
            {
                $('#did_form').parsley().validate();
            }
        });

        $('#dst_type').change(function () {
            if (this.value == 'IP') {
                $('#dst_point_ip').show();
                $('#dst_point_sip').hide();
                $('#dst_point_pstn').hide();
                $('#dst_campaign_id').hide();
                $('#dst_point_ip').attr('data-parsley-required', 'true');
                $('#dst_point_sip').attr('data-parsley-required', 'false');
                $('#dst_point_pstn').attr('data-parsley-required', 'false');
                $('#dst_campaign_id').attr('data-parsley-required', 'false');

            } else if (this.value == 'CAMPAINGN') {
                $('#dst_point_ip').hide();
                $('#dst_point_sip').hide();
                $('#dst_point_pstn').hide();
                $('#dst_campaign_id').show();
                $('#dst_point_ip').attr('data-parsley-required', 'false');
                $('#dst_point_sip').attr('data-parsley-required', 'false');
                $('#dst_point_pstn').attr('data-parsley-required', 'false');
                $('#dst_campaign_id').attr('data-parsley-required', 'true');
            } else if (this.value == 'PSTN') {
                $('#dst_point_ip').hide();
                $('#dst_point_sip').hide();
                $('#dst_point_pstn').show();
                $('#dst_campaign_id').hide();
                $('#dst_point_ip').attr('data-parsley-required', 'false');
                $('#dst_point_sip').attr('data-parsley-required', 'false');
                $('#dst_point_pstn').attr('data-parsley-required', 'true');
                $('#dst_campaign_id').attr('data-parsley-required', 'false');
            } else {
                $('#dst_point_ip').hide();
                $('#dst_point_sip').show();
                $('#dst_point_pstn').hide();
                $('#dst_campaign_id').hide();
                $('#dst_point_ip').attr('data-parsley-required', 'false');
                $('#dst_point_sip').attr('data-parsley-required', 'true');
                $('#dst_point_pstn').attr('data-parsley-required', 'false');
                $('#dst_campaign_id').attr('data-parsley-required', 'false');
            }
        });


        function dst_type_changed(dst_type)
        {
            if (dst_type == 'IP') {
                $('#dst_point2_ip').show();
                $('#dst_point2_sip').hide();
                $('#dst_point2_pstn').hide();
                $('#dst_campaign_id').hide();
                $('#dst_point2_ip').attr('data-parsley-required', 'true');
                $('#dst_point2_sip').attr('data-parsley-required', 'false');
                $('#dst_point2_pstn').attr('data-parsley-required', 'false');
                $('#dst_campaign_id').attr('data-parsley-required', 'false');
            } else if (dst_type == 'CAMPAINGN') {
                $('#dst_point2_ip').hide();
                $('#dst_point2_sip').hide();
                $('#dst_point2_pstn').hide();
                $('#dst_campaign_id').show();
                $('#dst_point2_ip').attr('data-parsley-required', 'false');
                $('#dst_point2_sip').attr('data-parsley-required', 'false');
                $('#dst_point2_pstn').attr('data-parsley-required', 'false');
                $('#dst_campaign_id').attr('data-parsley-required', 'true');
            } else if (dst_type == 'PSTN') {
                $('#dst_point2_ip').hide();
                $('#dst_point2_sip').hide();
                $('#dst_point2_pstn').show();
                $('#dst_campaign_id').hide();
                $('#dst_point2_ip').attr('data-parsley-required', 'false');
                $('#dst_point2_sip').attr('data-parsley-required', 'false');
                $('#dst_point2_pstn').attr('data-parsley-required', 'true');
                $('#dst_campaign_id').attr('data-parsley-required', 'false');
            } else {
                $('#dst_point2_ip').hide();
                $('#dst_point2_sip').show();
                $('#dst_point2_pstn').hide();
                $('#dst_campaign_id').hide();
                $('#dst_point2_ip').attr('data-parsley-required', 'false');
                $('#dst_point2_sip').attr('data-parsley-required', 'true');
                $('#dst_point2_pstn').attr('data-parsley-required', 'false');
                $('#dst_campaign_id').attr('data-parsley-required', 'false');
            }

        }

        $('#dst_type2').change(function () {
            dst_type_changed(this.value);
        });

    </script>
