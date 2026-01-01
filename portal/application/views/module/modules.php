<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Modules Management</h2>

            <div class="clearfix"></div>
        </div>

        <div class="clearfix"></div>

        <div class="table-responsive">       
            <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                <thead>
                    <tr class="headings thc">                     
                        <th class="column-title">Module Name </th>   
                        <th class="column-title">Description</th>
                        <th class="column-title">Status </th>
                        <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (is_array($plugins_installed) && count($plugins_installed) > 0) {

                        foreach ($plugins_installed as $plugin_system_name => $plugin_data) {
                            if (isset($plugin_data['plugin_info'])) {
                                $status = '<span class="label label-success">Active</span>';

                                $plugin_name = $plugin_data['plugin_info']['plugin_name'];
                                $plugin_description = $plugin_data['plugin_info']['plugin_description'];
                                $link = site_url('module/status/' . $plugin_system_name . '/inactivate');
                                $link_html = '<a href="' . $link . '" title="Deactivate">Deactivate</a>';
                            } else {
                                $status = '<span class="label label-danger">Inactive</span>';
                                $header_info = $this->plugins->fetch_plugin_headers($plugin_system_name);
                                $plugin_name = $header_info['plugin_name'];
                                $plugin_description = $header_info['plugin_description'];
                                $link = site_url('module/status/' . $plugin_system_name . '/activate');
                                $link_html = '<a href="' . $link . '" title="Activate">Activate</a>';
                            }
                            $module_url = site_url($plugin_system_name);
                            ?>
                            <tr >         
                                <td><a href="<?php echo $module_url; ?>" target="_blank"><?php echo $plugin_name; ?></a></td>
                                <td><?php echo $plugin_description; ?></td>
                                <td><?php echo $status; ?></td>
                                <td>
                                    <a href="<?php echo site_url('module/view/' . $plugin_system_name); ?>" title="View Details"><i class="fa fa-pencil-square-o"></i></a> 
                                        <?php echo $link_html; ?>
                                </td>                                
                            </tr>     
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8" align="center"><strong>No Module Found</strong></td>
                        </tr>
                        <?php
                    }
                    ?>


                </tbody>
            </table>
        </div>             


    </div>

</div>          
<?php //echo '<pre>';print_r($plugins_installed);  echo '</pre>';