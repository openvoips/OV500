<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

<?php
$vatflag_array = array('NONE', 'TAX', 'VAT');
$section_array = array();
$section_array['1'] = array('title' => 'Login', 'file_name' => 'inner/login.php');
$section_array['2'] = array('title' => 'Contact Detail', 'file_name' => 'inner/registration.php');
$section_array['3'] = array('title' => 'Settings', 'file_name' => 'inner/settings.php');
 
$section_total = count($section_array);

$tab_index=0;
$active_tab = (int) $active_tab;
if ($active_tab == 0)
    $active_tab = 1;
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

$notification_section_array['1'] = array('title' => 'Low Balance Notification', 'file_name' => 'inner/low_balance_notification.php');
?>
<link href="<?php echo base_url(); ?>theme/default/css/tabs.css" rel="stylesheet" type="text/css"/>
          
             
<div class="container-fluid">
    <div class="block-header">
        <h2>Low Balance Notification (ADD)</h2>
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
					$k=1;
					foreach ($voip_section_array as $key => $section_single_array) {
						$class = '';
						$link=site_url('crs/editvoip/'.$account_id_enc.'/'.$key);
						echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
						echo '<a class="nav-link "  href="'.$link.'"   >' . $section_single_array['title'] . '</a>';
						echo '</li>';
					}
					?>
                
                <?php
				
					$k=1;
					
                    foreach ($section_array as $key => $section_single_array) {
						$class = '';
						$link=site_url('crs/customers/edit/'.$account_id_enc.'/'.$key);
						echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
						echo '<a class="nav-link "  href="'.$link.'"   >' . $section_single_array['title'] . '</a>';
						echo '</li>';
					}
					?>
                    <?php
                foreach ($notification_section_array as $key => $section_single_array) {
                    $class = '';
                    if ($key == $active_tab)
                        $class = 'active';
                        $link=site_url('crs/customers/notification/'.$account_id_enc);
                    echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
                    echo '<a class="nav-link "  href="'.$link.'"   >' . $section_single_array['title'] . '</a>';
                    echo '</li>';
                }
                ?>
            </ul>
            <div class="clearfix" ></div>
        </div>







                   





                    <form action="" method="post" name="account_form" id="account_form" data-parsley-validate class="form-horizontal form-label-left">

                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="tab" value="<?php echo $active_tab ?>"> 
                        <input type="hidden" name="account_id" value="<?php echo $account_id; ?>"/>


                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Account Code </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <input type="text" name="account_name_display" id="account_name_display" value="<?php echo $data['company_name'] . ' (' . $account_id . ')'; ?>"  disabled="disabled"  class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Notification Type</label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                            <div class="radio">
                                <input  class="with-gap"  type="radio" name="notify_name" id="notify_name1" value="low-balance" <?php if (set_value('notify_name','low-balance') == 'low-balance') { ?> checked="checked" <?php } ?>  /><label for="notify_name1">Low Balance</label>

                                <input  class="with-gap"  type="radio" name="notify_name" id="notify_name2" value="daily-balance" <?php if (set_value('notify_name') == 'daily-balance') { ?> checked="checked" <?php } ?>  /> <label for="notify_name2">Daily Balance</label>
                            </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Trigger Value <span class="required">*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="notify_amount" id="notify_amount" value="<?php echo set_value('notify_amount'); ?>" placeholder="Alert value" data-parsley-required="" data-parsley-price="true" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12" for="first-name">Email Addresses </label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                            <input type="text" name="notify_emails" id="notify_emails" value="<?php echo set_value('notify_emails'); ?>" placeholder="Email Address" data-parsley-required="" data-parsley-multipleemail="true" class="form-control col-md-7 col-xs-12" ><br>comma seperated mulitiple email address'+
                            
                            </div>
                        </div>
                        


                     

                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-4 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <input class="with-gap" type="radio" name="status" id="status1" value="1"  <?php echo set_radio('ip_status', 'Y', TRUE); ?> /><label for="status1"> Active</label>

                                    <input class="with-gap" type="radio" name="status" id="status0" value="0" <?php echo set_radio('ip_status', 'N'); ?> /> <label for="status0">Inactive</label>
                                </div>

                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">

                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Edit Page</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>


    </div>

    <div class="block-header">
         <h2>Low Balance Notification (ADD)</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php
                $tab_index = 0;
                echo base_url('crs') . '/editvoip/' . param_encrypt($data['account_id']);
                ?>/<?php echo $active_tab ?>"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Back to Customer Edit Page</button></a> </li>
        </ul>
    </div>

</div>    
<script>







    $('#btnSave, #btnSaveClose').click(function () {
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
                //alert('ok');
                $("#account_form").submit();
            }
        } else
        {
            $('#account_form').parsley().validate();
        }
    })


    $(document).ready(function () {


    });

</script>
