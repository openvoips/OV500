<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Caller ID Translation Rules</h2>
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
                        <th class="column-title">Type</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (count($data['callerid']) > 0) {
                        foreach ($data['callerid'] as $callerid_data) {
                            if ($callerid_data['action_type'] == '1')
                                $status = '<span class="label label-success">Allowed</span>';
                            else
                                $status = '<span class="label label-danger">Blocked</span>';
                            ?>
                            <tr >
                                <td><?php
                                    if (str_replace('%', '', $callerid_data['maching_string']) == '')
                                        echo "Any Number";
                                    else
                                        echo str_replace('%', '', $callerid_data['maching_string']);
                                    ?></td>

                                <td><?php echo str_replace('%', '', $callerid_data['remove_string']); ?></td>
                                <td><?php echo str_replace('%', '', $callerid_data['add_string']); ?></td>
                                <td><?php echo $callerid_data['display_string']; ?></td>
                                <td><?php echo $status; ?></td>
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
                <a href="<?php echo base_url() ?>carriers/editSRCNo/<?php echo param_encrypt($data['carrier_id']); ?>/<?php echo $key; ?>" ><input type="button" value="Edit Rules" name="add_link" class="btn btn-primary"></a>
            </div>
        </div>

    </div>

</div>