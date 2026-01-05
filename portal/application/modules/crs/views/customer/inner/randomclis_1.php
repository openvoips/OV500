<?php
$tab_index = 0;
?>
<div style="margin-bottom:75px;">
    <table class="table table-striped jambo_table table-bordered">
        <thead>
            <tr class="headings">
                <th class="column-title">Destination Pattern</th>
                <th class="column-title">Caller ID</th>
                <th class="column-title">Status </th>
                <th class="column-title">Action </th>
            </tr>
        </thead>


        <tbody>
            <?php
            $str = '';
            if (count($data['randomcli']) > 0) {
                foreach ($data['randomcli'] as $randomcli_data) {
                    if ($randomcli_data['rule_type'] != '1')
                        continue;
                    if ($randomcli_data['cli_status'] == '1')
                        $status = '<span class="label label-success">Active</span>';
                    else
                        $status = '<span class="label label-danger">Inactive</span>';
                    $delete_link = "crs/customers/randomclis/" . param_encrypt($randomcli_data['account_id']) . '/1';
                    $str .= '<tr>
                                                <td>' . $randomcli_data['destination_prefix'] . '</td>
                                                <td>' . $randomcli_data['cli_fixprefix'] . '</td>
                                                <td>' . $status . '</td>
                                                <td class=" last">
                                                <a href="' . site_url('crs/customers/edit_randomcli/' . param_encrypt($data['account_id']) . '/' . param_encrypt($randomcli_data['id'])) . '" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>

                                                    <a href="javascript:void(0);" title="Delete" class="delete"
                                                    onclick=doConfirmDelete(\'' . $randomcli_data['id'] . '\',"' . $delete_link . '","customer_randomcli_delete")><i class="fa fa-trash"></i></a>
                                                
                                                </td>
                                            </tr>';

                }

            }

            if ($str != '') {
                echo $str;
            } else {
                ?>
                <tr>
                    <td colspan="6" align="center"><strong>No Record Found</strong></td>
                </tr>
                <?php
            }
            ?>

        </tbody>

    <div class="col-md-12 col-sm-12 col-xs-12 text-right">
        <a href="<?php echo site_url('crs/customers/add_randomcli/' . param_encrypt($data['account_id']) . '/1'); ?>"><input
                type="button" value="Add Random CLI" name="add_link" class="btn btn-primary"></a>

    </div>    </table>
</div>
 