<?php
$section_array = array();
$section_array['1'] = array('title' => 'Tariff', 'file_name' => 'inner/edit_tariff.php');
//$section_array['2'] = array('title' => 'Bundle', 'file_name' => 'inner/add_bundle.php');
$section_array['4'] = array('title' => 'Dialplan', 'file_name' => 'inner/add_dialplan_' . strtolower($accountinfo['account_type']) . '.php');
$section_array['5'] = array('title' => 'Bill Config', 'file_name' => 'inner/add_invoice.php');
if ($accountinfo['account_type'] == 'CUSTOMER') {
    $section_array['6'] = array('title' => 'IP-Trunk', 'file_name' => 'inner/ip.php');
    $section_array['7'] = array('title' => 'SIP-Truk', 'file_name' => 'inner/sip.php');
}
$section_array['8'] = array('title' => 'CLI Rule', 'file_name' => 'inner/srcno.php');
$section_array['9'] = array('title' => 'DST Rule', 'file_name' => 'inner/dstno.php');
$section_array['10'] = array('title' => 'DID CLI Rule', 'file_name' => 'inner/didsrcno.php');
$section_array['11'] = array('title' => 'DID Dial Rule', 'file_name' => 'inner/diddstno.php');

$section_total = count($section_array);
$active_tab = (int) $active_tab;
if ($active_tab == 0)
    $active_tab = 1;
?>
<?php
$cust_section_array = array();
$cust_section_array['1'] = array('title' => 'Login', 'file_name' => 'inner/login.php');
$cust_section_array['2'] = array('title' => 'Contact Detail', 'file_name' => 'inner/registration.php');
$cust_section_array['3'] = array('title' => 'Settings', 'file_name' => 'inner/settings.php');
 

?>
<link href="<?php echo base_url(); ?>theme/default/css/tabs.css" rel="stylesheet" type="text/css"/>


<div class="container-fluid">
                <div class="block-header">
                                <h2>Account <?php echo $accountinfo['company_name'] . ' (' . $accountinfo['account_id'] . ') ' . $accountinfo['account_type']; ?></h2>
                                <ul class="nav navbar-right panel_toolbox">
                                                <li> <a href="<?php echo site_url('crs'); ?>"><button class="btn btn-primary" type="button">Back to Listing Page</button></a></li>
                                </ul>

                </div>

                <div class="row clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="card">
                                                                <div class="header">

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
                                                                                                <?php
                                                                                                $account_id_enc = param_encrypt($accountinfo['account_id']);
                                                                                                $k = 1;
                                                                                                foreach ($cust_section_array as $key => $section_single_array) {
                                                                                                    $class = '';
                                                                                                    if ($accountinfo['account_type'] == 'RESELLER')
                                                                                                        $link = site_url('crs/resellers/edit/' . $account_id_enc . '/' . $k++);
                                                                                                    else
                                                                                                        $link = site_url('crs/customers/edit/' . $account_id_enc . '/' . $k++);
                                                                                                    echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
                                                                                                    echo '<a class="nav-link "  href="' . $link . '"   >' . $section_single_array['title'] . '</a>';
                                                                                                    echo '</li>';
                                                                                                }
                                                                                                ////////////
                                                                                                $link = site_url('crs/customers/notification/' . $account_id_enc );
                                                                                                echo '<li class="nav-item header_section " id="id_header_section_' . $key . '">';
                                                                                                echo '<a class="nav-link "  href="' . $link . '"   >Low Balance Notification</a>';
                                                                                                echo '</li>';
                                                                                                ?>
                                                                                                
                                                                                </ul>
                                                                                <br><br>


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
                </div>
                <div class="block-header">
                                <h2>Account Configuration Management</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                                <li> <a href="<?php echo site_url('crs'); ?>"><button class="btn btn-primary" type="button">Back to Listing Page</button></a></li>
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