<style type="text/css">
    a.underline{
        text-decoration:underline;
    }

</style>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Available Modules</h2>

            <div class="clearfix"></div>
        </div>

        <div class="clearfix"></div>

        <div class="table-responsive">       
            <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">
                <thead>
                    <tr class="headings thc">                     
                        <th class="column-title">Module Name </th>   
                        <th class="column-title">Description</th>
                        <th class="column-title">Details</th>
                        <th class="column-title">Charges</th>
                        <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (isset($plugins_available['plugins']) && is_array($plugins_available['plugins']) && count($plugins_available['plugins']) > 0) {

                        foreach ($plugins_available['plugins'] as $plugin_system_name => $plugin_data) {
                            //if(isset( $plugin_data['plugin_info']	))
                            {      //print_r($plugin_data);		
                                $plugin_name = $plugin_data['plugin_name'];
                                $plugin_code = $plugin_data['plugin_code'];
                                $plugin_description = $plugin_data['plugin_description'];
                                $link = $plugin_data['plugin_uri'];
                                $link_html = '<a href="' . $link . '" target="_blank" class="underline">Details</a>';
                                $price = $plugin_data['price'];
                                $module_directory = $plugin_data['module_directory'];

                                $directory_path = APPPATH . 'modules/' . $module_directory;
                                if ($module_directory != '' && file_exists($directory_path)) {
                                    $link = '<span class="label label-success">Installed</span>';
                                } elseif ($plugin_data['is_purchased'] == 'Y') {
                                    $link = '<a class="btn btn-primary" href="' . site_url('module/download/' . $plugin_code) . '" role="button">Download</a>';
                                } else {
                                    $link = '<span class="label label-info">Buy</span>';
                                }
                            }
                            ?>
                            <tr >         
                                <td><?php echo $plugin_name; ?></td>
                                <td><?php echo $plugin_description; ?></td>
                                <td><?php echo $link_html; ?></td>
                                <td><?php echo $price; ?></td>
                                <td><?php echo $link; ?></td>                              
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