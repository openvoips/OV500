<?php
$permission_array = get_permission_options();
$menu_array = get_reseller_menu('permission');
if (isset($menu_data['menu_array']))
    $reseller_menu_array = $menu_data['menu_array'];
else
    $reseller_menu_array = array();
?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <div class='col-md-10 col-sm-2 col-xs-3'> <h2>Menus for <?php echo $resellers_data['name'] . ' (' . $resellers_data['account_id'] . ')'; ?></h2>    </div>

            <div class='text-right col-md-2 col-sm-2 col-xs-3'><input type="checkbox" id="check_all" name="check_all" onchange="select_all()" />    </div>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">          

            <form action="" method="post" name="permission_form" id="permission_form" data-parsley-validate class="form-horizontal form-label-left">
                <input type="hidden" name="button_action" id="button_action" value="">
                <input type="hidden" name="action" value="OkSaveData"> 
                <input type="hidden" name="account_id" value="<?php echo $resellers_data['account_id']; ?>"/>

                <ul class="to_do">
                    <?php
                    $menu_str1 = '';
                    $site_url = site_url();
                    foreach ($menu_array as $key => $sub_menu_array) {
                        if (isset($sub_menu_array['menu_name'])) {//single page link			
                            $page_name_array = $sub_menu_array['page_name'];
                            $menu_name = $sub_menu_array['menu_name'];
                            $page_url = $sub_menu_array['page_url'];


                            if (isset($sub_menu_array['icon']) && $sub_menu_array['icon'] != '')
                                $icon = $sub_menu_array['icon'];
                            else
                                $icon = $default_icon;


                            if (in_array($key, $reseller_menu_array))
                                $checked = ' checked ';
                            else
                                $checked = '';

                            $menu_str1 .= '<li class="mail_list">'
                                    . '<div class="col-md-1 col-sm-2 col-xs-4"><input type="checkbox" class="class_check" name="menu_items[]" value="' . $key . '" ' . $checked . ' /></div>'
                                    . '<div class="col-md-10 col-sm-2 col-xs-4">' . $icon . '&nbsp;&nbsp;' . $menu_name . '</div>'
                                    . '</li>';
                        }
                        else {
                            $menu_temp = '';
                            if (isset($sub_menu_array['icon']) && $sub_menu_array['icon'] != '') {
                                $icon = $sub_menu_array['icon'];
                                unset($sub_menu_array['icon']);
                            } else
                                $icon = $default_icon;


                            foreach ($sub_menu_array as $sub_key => $sub_sub_menu_array) {

                                $menu_name = $sub_sub_menu_array['menu_name'];

                                if (isset($sub_menu_array[$menu_name]) && count($sub_menu_array[$menu_name]) > 0) {/*
                                  $sub_menu_temp = '';
                                  $upper_li_class='';
                                  foreach($sub_menu_array[$menu_name] as $sub_sub_key=>$sub_sub_sub_menu_array)
                                  {

                                  $sub_page_name_array = $sub_sub_sub_menu_array['page_name'];
                                  $sub_menu_name = $sub_sub_sub_menu_array['menu_name'];
                                  $sub_page_url = $sub_sub_sub_menu_array['page_url'];

                                  $sub_menu_temp .='<li class="mail_list">'.$sub_menu_name.'</li>';

                                  }

                                  {
                                  $menu_temp .='<li '.$upper_li_class.'>Z<a>'.$menu_name.' <span class="fa fa-chevron-down"></span></a>'.
                                  $sub_menu_temp;


                                  }


                                 */
                                } elseif (isset($sub_sub_menu_array['page_url'])) {
                                    $page_name_array = $sub_sub_menu_array['page_name'];
                                    $page_url = $sub_sub_menu_array['page_url'];

                                    $icon_sub = '';

                                    if (in_array($sub_key, $reseller_menu_array))
                                        $checked = ' checked ';
                                    else
                                        $checked = '';

                                    $menu_temp .= '<li class="mail_list">'
                                            . '<div class="col-md-1 col-sm-2 col-xs-2"></div>'
                                            . '<div class="col-md-1 col-sm-2 col-xs-4"><input type="checkbox" class="class_check" name="menu_items[]" value="' . $sub_key . '"  ' . $checked . '/></div>'
                                            . '<div class="col-md-10 col-sm-2 col-xs-4">' . $icon_sub . '&nbsp;&nbsp;' . $menu_name . '</div>'
                                            . '</li>';
                                }
                            }

                            if ($menu_temp != '') {
                                $menu_str1 .= '<li class="mail_list">'
                                        . '<div class="col-md-12 col-sm-12 col-xs-12">' . $icon . '&nbsp;&nbsp;' . $key . '</div>'
                                        . '</li>';
                                $menu_str1 .=$menu_temp;
                            }
                        }
                    }
                    echo $menu_str1;
                    ?>

                </ul>

                <div class="ln_solid"></div>
                <div class="form-group">
                    <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4">				
                        <button type="button" id="btnSave" class="btn btn-success">Save</button>
                        <button type="button" id="btnSaveClose" class="btn btn-info">Save & Go Back to Listing Page</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-12 col-sm-12 col-xs-12 right">
            <div class="ln_solid"></div>
            <div class="x_title">
                <h2>Reseller User Configuration Management</h2>
                <ul class="nav navbar-right panel_toolbox">     
                    <li><a href="<?php echo site_url('resellers') ?>"><button class="btn btn-danger" type="button" >Back to Reseller Listing Page</button></a> </li>
                </ul>
                <div class="clearfix"></div>
            </div>
        </div>
    </div><?php //ddd($reseller_menu_array); ddd($menu_array); ?>
</div>
<script>
    $('#btnSave, #btnSaveClose').click(function () {

        var is_ok = true;
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#permission_form").submit();
        } else
        {
            //$('#permission_form').parsley().validate();
        }



    })

    function select_all()
    {
        console.log('aa');
        if ($('#check_all').is(':checked'))
            $(".class_check").prop('checked', true);
        else
            $(".class_check").prop('checked', false);
    }
</script>
