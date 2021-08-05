<table class="table table-striped jambo_table table-bordered">
    <thead>
        <tr class="headings thc">
            <th class="column-title">Username</th>
            <th class="column-title">Secret</th>                               
            <th class="column-title">Extension No</th>                                
            <th class="column-title">Status </th>
            <th class="column-title">Action </th>
        </tr>
    </thead>

    <tbody>
        <?php
        if (count($accountinfo['sipuser']) > 0) {
            foreach ($accountinfo['sipuser'] as $sip_data) {
                if ($sip_data['status'] == '1')
                    $status = '<span class="label label-success">Active</span>';
                else
                    $status = '<span class="label label-danger">Inactive</span>';

                if ($sip_data['voicemail'] == '1')
                    $voicemail = '<span class="label label-success">Active</span>';
                else
                    $voicemail = '<span class="label label-danger">Inactive</span>';
                ?>
                <tr>
                    <td><?php echo $sip_data['username']; ?></td>
                    <td><?php echo $sip_data['secret']; ?></td>                                      
                    <td><?php echo $sip_data['extension_no']; ?></td>

                                                                                                         <!--<td><?php echo $voicemail; ?></td>-->
                    <td><?php echo $status; ?></td>
                    <td class="last">
                        <a href="<?php echo base_url('crs'); ?>/sipEdit/<?php echo param_encrypt($accountinfo['account_id']); ?>/<?php echo param_encrypt($sip_data['id']); ?>/<?php echo $key ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>

                        <?php if (check_account_permission('customer', 'delete')): ?>
                            <a href="javascript:void(0);"
                               onclick=doConfirmDelete('<?php echo $sip_data['id']; ?>','<?php echo 'crs' ?>/editvoip/<?php echo param_encrypt($accountinfo['account_id']); ?>/<?php echo $key ?>','account_sip_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
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
    <a href="<?php echo base_url('crs'); ?>/sipAdd/<?php echo param_encrypt($accountinfo['account_id']); ?>/<?php echo $key ?>" ><input type="button" value="Add SIP User" name="add_link" class="btn btn-primary"></a>
</div>