<div class="header">
    <table class="table table-striped jambo_table table-bordered">
        <thead>
            <tr class="headings thc">
                <th class="column-title">CLI Rule </th>
                <th class="column-title">DST Prefix</th>
                <th class="column-title">Fix Prefix</th>
                <th class="column-title">Suffix length</th>
                <th class="column-title">Status </th>
                <th class="column-title">Action </th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($data['randomcli']) > 0) {
                foreach ($data['randomcli'] as $randomcli_data) {
                    if ($randomcli_data['cli_status'] == '1')
                        $status = '<span class="label label-success">Active</span>';
                    else
                        $status = '<span class="label label-danger">Inactive</span>';
                    ?>
                    <tr >
                        <td><?php echo $randomcli_data['clirule_name']; ?></td>
                        <td><?php echo $randomcli_data['destination_prefix']; ?></td>
                        <td><?php echo $randomcli_data['cli_fixprefix']; ?></td>
                        <td><?php echo $randomcli_data['cli_length']; ?></td>
                        <td><?php echo $status; ?></td>
                        <td class=" last">
                            <a href="<?php echo base_url(); ?>carriers/editRandomCLI/<?php echo param_encrypt($data['carrier_id']); ?>/<?php echo param_encrypt($randomcli_data['id']); ?>/<?php echo $key ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>

                            <?php if (check_account_permission('carrier', 'delete')): ?>
                                <a href="javascript:void(0);"
                                   onclick=doConfirmDelete('<?php echo $randomcli_data['id']; ?>','carriers/edit/<?php
                                   echo
                                   param_encrypt($randomcli_data['carrier_id']);
                                   ?>/<?php echo $key ?>','RandomCLI_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                               <?php endif; ?>


                        </td>
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
        <a href="<?php echo base_url(); ?>carriers/addRandomCLI/<?php echo param_encrypt($data['carrier_id']); ?>/<?php echo $key ?>" ><input type="button" value="Add RandomCLI Rule" name="add_link" class="btn btn-primary"></a>
    </div>
    <br>
    <br>
</div>
