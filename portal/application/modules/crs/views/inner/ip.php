<?php 
//echo '<pre>';
//print_r($accountinfo);
//echo '</pre>';
?>
<table class="table table-striped jambo_table table-bordered">
    <thead>
        <tr class="headings thc">
            <th class="column-title">IP</th>
            <th class="column-title">Billing Code</th>
            <th class="column-title">Open Prefix</th>
            <th class="column-title">Status </th>
            <th class="column-title">Action </th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (count($accountinfo['ip']) > 0) {
            foreach ($accountinfo['ip'] as $ip_data) {
                if ($ip_data['ip_status'] == '1')
                    $status = '<span class="label label-success">Active</span>';
                else
                    $status = '<span class="label label-danger">Inactive</span>';
                ?>
                <tr >
                    <td><?php echo $ip_data['ipaddress']; ?></td>
                    <td><?php echo $ip_data['billingcode']; ?></td>
                    <td><?php echo $ip_data['dialprefix']; ?></td>
                    <td><?php echo $status; ?></td>
                    <td class=" last">
                        <a href="<?php echo base_url('crs'); ?>/ipEdit/<?php echo param_encrypt($ip_data['account_id']); ?>/<?php echo param_encrypt($ip_data['id']); ?>/<?php echo $key ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                        <?php if (check_account_permission('customer', 'delete')): ?>
                            <a href="javascript:void(0);"
                               onclick=doConfirmDelete('<?php echo $ip_data['id']; ?>',"<?php echo 'crs' ?>/editvoip/<?php echo param_encrypt($ip_data['account_id']); ?>/<?php echo $key ?>",'account_ips_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                           <?php endif; ?>

                    </td>
                </tr>

                <?php
            }
        }
        else {
            ?>
            <tr>
                <td colspan="5" align="center"><strong>No Record Found</strong></td>
            </tr>
            <?php
        }
        ?>


    </tbody>
</table>
<div class="col-md-12 col-sm-12 col-xs-12 text-right">
    <a href="<?php echo base_url('crs'); ?>/ipAdd/<?php echo param_encrypt($accountinfo['account_id']); ?>/<?php echo $key ?>" ><input type="button" value="Add IP User" name="add_link" class="btn btn-primary"></a>
</div>