<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Module Details</h2>            
            <div class="clearfix"></div>
        </div>

        <div class="clearfix"></div>

        <div class="table-responsive">
            <table class="table   table-bordered" >
                <thead>
                    <tr class=" ">                     
                        <th class="thc" style="color:#FFFFFF; width:30%;">Module Name </th>  <td><?php echo $plugin_header['plugin_name']; ?></td></tr> 
                <th class="thc" style="color:#FFFFFF; width:30%;">Description</th>  <td><?php echo $plugin_header['plugin_description']; ?></td></tr> 
                <th class="thc" style="color:#FFFFFF; width:30%;">Version </th>  <td><?php echo $plugin_header['plugin_version']; ?></td></tr> 
                <th class="thc" style="color:#FFFFFF; width:30%;">Plugin URI </th>  <td><?php echo $plugin_header['plugin_uri']; ?></td></tr>                         
                <th class="thc" style="color:#FFFFFF; width:30%;">Author</th>  <td><?php echo $plugin_header['plugin_author']; ?></td></tr> 
                <th class="thc" style="color:#FFFFFF; width:30%;">Author URI</th>  <td><?php echo $plugin_header['plugin_author_uri']; ?></td></tr>  

                </thead>
                <tbody>
                    <tr><td colspan="2" align="right">
                            <?php
                            if (isset($plugins_active[$plugin_name])) {
                                $link = site_url('module/status/' . $plugin_header['plugin_system_name'] . '/inactivate');
                                $link_html = '<a href="' . $link . '" title="Deactivate"><button type="button" id="btnSave" class="btn btn-danger" >Deactivate Module</button></a>';
                            } else {
                                $link = site_url('module/status/' . $plugin_header['plugin_system_name'] . '/activate');
                                $link_html = '<a href="' . $link . '" title="Activate"><button type="button" id="btnSave" class="btn btn-success" >Activate Module</button></a>';
                            }
                            echo $link_html;
                            ?>
                        </td></tr>
                </tbody>


            </table>
        </div>             


    </div>

</div>            