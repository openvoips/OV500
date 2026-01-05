<?php
$vatflag_array = array('NONE', 'TAX', 'VAT');
$section_array = array();

if ($active_tab == 'did')
    $title = 'Edit DID CLI Filter';
else
    $title = 'Edit PSTN CLI Filter';

$add_file_name = '';//$active_tab
$section_array['did'] = array('title' => 'DID CLI Filter', 'file_name' => 'inner/did_cli_listing.php');
$section_array['pstn'] = array('title' => 'PSTN CLI Filter', 'file_name' => 'inner/pstn_cli_listing.php');
$section_array['add'] = array('title' => $title, 'file_name' => 'inner/clifilter_edit.php');

$section_total = count($section_array);

$tab_index = 0;

?>

<link href="<?php echo base_url(); ?>theme/default/css/tabs.css" rel="stylesheet" type="text/css" />


<div class="container-fluid">
    <div class="block-header">
        <h2>Customer Account Configuration Management <span class="text-info"><?php echo $data['account_id'] . ' (' . $data['company_name'] . ')'; ?></span></h2>
        <ul class="nav navbar-right panel_toolbox">
            <li> <a href="<?php echo site_url('crs'); ?>"><button class="btn btn-primary" type="button">Back to Customer
                        Listing Page</button></a></li>
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
                        foreach ($section_array as $key => $section_single_array) {
                            $class = '';
                            if ($key == 'add')
                                $class = 'active';


                            $link = site_url('crs/clifilter/index/' . $account_id_enc . '/' . $key);
                            echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
                            echo '<a class="nav-link "  href="' . $link . '"   >' . $section_single_array['title'] . '</a>';
                            echo '</li>';



                        }
                        ?>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <?php
                foreach ($section_array as $key => $section_single_array) {
                    //echo $key.' == '.$active_tab.'<br>';
                    if ($key == 'add')
                        $class = '';
                    else
                        continue;
                    ?>
                    <div class="x_content content_div <?php echo $class; ?>" id="<?php echo 'id_content_div_' . $key; ?>"
                        style="margin-bottom:10px;">

                        <?php
                        include ($section_single_array['file_name']);
                        ?>
                    </div>

                <?php } ?>
            </div>
            <div class="block-header">
                <h2>Customer Account Configuration Management</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li> <a href="<?php echo site_url('crs'); ?>"><button class="btn btn-primary" type="button">Back to
                                Customer Listing Page</button></a></li>
                </ul>
            </div>
        </div>



        <script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
        <script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>
        <script>
            var section_total = "<?php echo $section_total; ?>";
            function show_section(key) {
                var id_header_section = 'id_header_section_' + key;
                var id_content_div = 'id_content_div_' + key;
                $('.content_div').addClass('hide');
                $('#' + id_content_div).removeClass('hide');
                ///////////////
                $('.header_section').removeClass('active');
                $('#' + id_header_section).addClass('active');
            }

            function save_button(key) {
                var form_name = 'tab_form_' + key;
                var is_ok = $("#" + form_name).parsley().isValid();
                if (is_ok === true) {
                    if (is_ok === true) {
                        $("#" + form_name).submit();
                    }
                } else {
                    $('#' + form_name).parsley().validate();
                }
            }
        </script>