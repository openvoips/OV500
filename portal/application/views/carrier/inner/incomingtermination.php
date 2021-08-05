<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Incoming Termination Prefix Translation Rules</h2>
            <ul class="nav navbar-right panel_toolbox">


                </li>
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">
            <table class="table table-striped jambo_table table-bordered">
                <thead>
                    <tr class="headings thc">
                        <th class="column-title">Number should start with</th>
                        <th class="column-title">Remove Prefix</th>
                        <th class="column-title">Add Prefix</th>
                        <th class="column-title">Translation Rule</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    if (count($data['prefix_incoming']) > 0) {
                        foreach ($data['prefix_incoming'] as $prefix_data) {
                            ?>
                            <tr >
                                <td><?php
                                    if (str_replace('%', '', $prefix_data['maching_string']) == '')
                                        echo "Any Number";
                                    else
                                        echo str_replace('%', '', $prefix_data['maching_string']);
                                    ?></td>

                                <td><?php echo str_replace('%', '', $prefix_data['remove_string']); ?></td>
                                <td><?php echo str_replace('%', '', $prefix_data['add_string']); ?></td>
                                <td><?php echo $prefix_data['display_string']; ?></td>

                            </tr>

                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6" align="center"><strong>No Record Found</strong></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                <a href="<?php echo base_url() ?>carriers/editINDSTNo/<?php echo param_encrypt($data['carrier_id']); ?>/<?php echo $key ?>" ><input type="button" value="Edit Rules" name="add_link" class="btn btn-primary"></a>
            </div>
        </div>

    </div>

</div>  