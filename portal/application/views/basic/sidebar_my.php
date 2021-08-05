<?php
$menu_array = array();
if (check_logged_user_group('reseller')) {
    include('sidebar-menu-reseller.php');
} elseif (check_logged_user_group('customer')) {
    include('sidebar-menu-customer.php');
} else {
    include('sidebar-menu-admin.php');
}



//if (check_logged_user_type(array('RESELLER','CUSTOMER'))) 
{
    $logo_url = get_logo();
    $logo_html = '<img class="rounded" style=" max-height:57px;display: block; margin-left: auto; margin-right: auto;" src="' . $logo_url . '"';
    ?>

    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a href="<?php echo base_url() ?>dashboard" class="site_title"> <span><?php echo $logo_html; ?></span></a>
        </div>

        <div class="clearfix"></div>    
        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu" >
            <div class="menu_section">
                <ul class="nav side-menu">
                    <li><a href="<?php echo base_url() ?>dashboard" title="Home"><i class="fa fa-home"></i>Home</a></li>
                    <?php echo create_menu_html($menu_array, $page_name); ?>               	
                </ul>
            </div>
        </div>
        <!-- /sidebar menu -->



        <!-- /sidebar menu -->

        <!-- /menu footer buttons -->
        <div class="sidebar-footer hidden-small">
            <?php if (check_logged_user_group(array('reseller', 'customer'))) { ?>
                <a data-toggle="tooltip" data-placement="top" title="Settings" href="<?php echo site_url('account') ?>">
                    <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                </a>
            <?php } ?>
            <a data-toggle="tooltip" data-placement="top" title="Profile"  href="<?php echo base_url() ?>profile">
                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="Logout" href="<?php echo base_url() ?>logout">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
            </a>
        </div>
        <!-- /menu footer buttons -->
    </div>
<?php }
?>
