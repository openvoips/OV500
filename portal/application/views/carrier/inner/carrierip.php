<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Carrier IP</h2>
            <ul class="nav navbar-right panel_toolbox">
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">
            <table class="table table-striped jambo_table table-bordered">
                <thead>
                    <tr class="headings thc">
                        <th class="column-title">IP </th>
                        <th class="column-title">Type</th>
                        <th class="column-title">Load</th>
                        <th class="column-title">Status </th>
                        <th class="column-title">Action </th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (count($data['ip']) > 0) {
                        foreach ($data['ip'] as $carrier_ip_data) {
                            if ($carrier_ip_data['ip_status'] == '1')
                                $status = '<span class="label label-success">Active</span>';
                            else
                                $status = '<span class="label label-danger">Inactive</span>';
                            ?>
                            <tr >
                                <td><?php echo $carrier_ip_data['ipaddress']; ?></td>
                                <td><?php echo $carrier_ip_data['auth_type']; ?></td>
                                <td><?php echo $carrier_ip_data['load_share']; ?></td>
                                <td><?php echo $status; ?></td>
                                <td class=" last">
                                    <a href="<?php echo base_url(); ?>carriers/editG/<?php echo param_encrypt($data['carrier_id']); ?>/<?php echo param_encrypt($carrier_ip_data['id']); ?>/<?php echo $key ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>

                                    <?php if (check_account_permission('carrier', 'delete')): ?>
                                        <a href="javascript:void(0);"
                                           onclick=doConfirmDelete('<?php echo $carrier_ip_data['carrier_ip_id']; ?>','carriers/edit/<?php
                                           echo
                                           param_encrypt($carrier_ip_data['carrier_id']);
                                           ?>/<?php echo $key ?>','carrier_ip_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                                       <?php endif; ?>


                                </td>
                            </tr>

                            <?php
                        }
                    }
                    else {
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
                <a href="<?php echo base_url(); ?>carriers/addG/<?php echo param_encrypt($data['carrier_id']); ?>/<?php echo $key ?>" ><input type="button" value="Add Carrier IP" name="add_link" class="btn btn-primary"></a>
            </div>
        </div>

    </div>

</div>   