<?php
$logged_user_type = get_logged_user_type();
$session_current_user_id = get_logged_user_id();
$logged_account_id = get_logged_account_id();
$logged_account_level = get_logged_account_level();
$logged_user_group = get_logged_user_group();

$menu_array = array();
?>
<?php

function create_menu_html_bsd($menu_array, $page_name) {

    if (!check_logged_user_type(array('EXTENSION'))) {
    $menu_array = do_action('update_menu', $menu_array);
    }

    $menu_str = '';
    $site_url = site_url();
    $default_icon = '<i class="material-icons">widgets</i>';
    foreach ($menu_array as $key => $sub_menu_array) {
        if (isset($sub_menu_array['menu_name'])) {//single page link
            //echo $sub_menu_array['menu_name'].'=>'.$site_url.$sub_menu_array['page_url'].'<br>';
            $page_name_array = $sub_menu_array['page_name'];
            $menu_name = $sub_menu_array['menu_name'];
            $page_url = $sub_menu_array['page_url'];
            $target = (isset($sub_menu_array['target']) && $sub_menu_array['target'] != '') ? 'target="' . $sub_menu_array['target'] . '"' : '';

            if (in_array($page_name, $page_name_array))
                $class = 'class="active"';
            else
                $class = '';

            if (isset($sub_menu_array['icon']) && $sub_menu_array['icon'] != '')
                $icon = $sub_menu_array['icon'];
            else
                $icon = $default_icon;

            if (strpos($page_url, 'http') === false)
                $link_url = $site_url . $page_url;
            else
                $link_url = $page_url;

            $menu_str .= '<li ' . $class . '><a href="' . $link_url . '" ' . $target . '>' . $icon . '<span>' . $menu_name . '</span></a></li>';
        } else {
            //	echo $key.'<br>';
            $menu_temp = '';
            if (isset($sub_menu_array['icon']) && $sub_menu_array['icon'] != '') {
                $icon = $sub_menu_array['icon'];
                unset($sub_menu_array['icon']);
            } else
                $icon = $default_icon;

            $top_class = "top";
            foreach ($sub_menu_array as $sub_key => $sub_sub_menu_array) {
                //change_march_2023
                $menu_name = isset($sub_sub_menu_array['menu_name']) ? $sub_sub_menu_array['menu_name'] : '';

                if (isset($sub_menu_array[$menu_name]) && count($sub_menu_array[$menu_name]) > 0) {
                    $sub_menu_temp = '';
                    $upper_li_class = '';
                    foreach ($sub_menu_array[$menu_name] as $sub_sub_key => $sub_sub_sub_menu_array) {

                        $sub_page_name_array = $sub_sub_sub_menu_array['page_name'];
                        $sub_menu_name = $sub_sub_sub_menu_array['menu_name'];
                        $sub_page_url = $sub_sub_sub_menu_array['page_url'];
                        $target = (isset($sub_sub_sub_menu_array['target']) && $sub_sub_sub_menu_array['target'] != '') ? 'target="' . $sub_sub_sub_menu_array['target'] . '"' : '';

                        if (in_array($page_name, $sub_page_name_array)) {
                            $class_sub = 'class="active1"';
                            $upper_li_class = 'class="active"';
                        } else
                            $class_sub = '';

                        if (strpos($sub_page_url, 'http') === false)
                            $link_url = $site_url . $sub_page_url;
                        else
                            $link_url = $sub_page_url;


                        $sub_menu_temp .= '<li ' . $class_sub . '><a href="' . $link_url . '"  ' . $target . '>' . '<span>' . $sub_menu_name . '</span></a></li>';
                    } {
                        $menu_temp .= '<li ' . $upper_li_class . '>YY<a>' . $menu_name . ' <span class="fa fa-chevron-down"></span></a>' .
                                '<ul class="nav child_menu">' .
                                $sub_menu_temp .
                                '</ul>' .
                                '</li>';
                    }
                } elseif (isset($sub_sub_menu_array['page_url'])) {


                    $page_name_array = $sub_sub_menu_array['page_name'];
                    $page_url = $sub_sub_menu_array['page_url'];
                    $target = (isset($sub_sub_menu_array['target']) && $sub_sub_menu_array['target'] != '') ? 'target="' . $sub_sub_menu_array['target'] . '"' : '';

                    if (in_array($page_name, $page_name_array)) {
                        $class_sub = 'class="active"';
                        $top_class = 'active';
                    } else
                        $class_sub = '';

                    $icon_sub = '';

                    if (strpos($page_url, 'http') === false)
                        $link_url = $site_url . $page_url;
                    else
                        $link_url = $page_url;

                    $menu_temp .= '<li ' . $class_sub . '><a href="' . $link_url . '"  ' . $target . '>' . $menu_name . '</a></li>';
                }
            }
            if ($menu_temp != '') {
                $menu_str .= '<li class="' . $top_class . '"><a href="javascript:void(0);" class="menu-toggle">' . $icon . '<span>' . $key . '</span></a>' .
                        '<ul class="ml-menu">' .
                        $menu_temp .
                        '</ul>' .
                        '</li>';
            }
        }
    }

    return $menu_str;
}
?>
<ul class="list">
    <!--<li class="header">MAIN NAVIGATION</li>-->
    <li><a href="<?php echo base_url() ?>dashboard" title="Home"><i class="material-icons">home</i><span>Dashboard</span></a></li>
    <?php
    $menu_array = array();
    if (check_logged_user_group(array('RESELLER'))) {
        include('sidebar-menu-reseller.php');
    } elseif (check_logged_user_group(array('CUSTOMER'))) {
        include('sidebar-menu-customer.php');
    } else {
        include('sidebar-menu-admin.php');
    }
    ?>
    <?php
    if (!isset($page_name))
        $page_name = '';
    echo create_menu_html_bsd($menu_array, $page_name);
    ?> 
    <li><a href="<?php echo base_url() ?>logout" title="Home"><i class="material-icons">logout</i><span>Logout</span></a></li>
</ul>


