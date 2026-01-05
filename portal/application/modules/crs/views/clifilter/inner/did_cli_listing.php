<?php
$tab_index = 0;
$type = 'did';
?>
<div style="margin-bottom:75px;">
    <!--
    <div class="col-md-12 col-sm-12 col-xs-12 text-right" style="margin-bottom:10px;">
        <a href="<?php echo site_url('crs/clifilter/add/' . param_encrypt($data['account_id']) . '/' . $type); ?>"><input
                type="button" value="Add DID CLI Filter" name="add_link" class="btn btn-primary"></a>

    </div>-->
    <div class="col-md-12">
        <form class="block-content form-horizontal " id="search_form" name="search_form" method="post"
            action="<?php echo site_url('crs/clifilter/index/' . param_encrypt($data['account_id']) . '/' . $key); ?>"
            style="padding:10px 10px;">
            <input type="hidden" name="search_action" value="search" />
            <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />


            <div class="form-group">

                <label class="control-label col-md-2 col-sm-6 col-xs-6">Caller ID</label>
                <div class="col-md-2 col-sm-6 col-xs-12">
                    <input type="text" name="callerid" id="callerid"
                        value="<?php echo $_SESSION[$search_session_key]['callerid']; ?>"
                        class="form-control data-search-field" placeholder="Caller ID">
                </div>

                <label class="control-label col-md-2 col-sm-6 col-xs-12">Status</label>
                <div class="col-md-3 col-sm-8 col-xs-12">
                    <select name="cli_status" id="cli_status" class="form-control data-search-field">
                        <option value="">Select</option>
                        <option value="1" <?php if ($_SESSION[$search_session_key]['cli_status'] == '1')
                            echo 'selected="selected"'; ?>>Active</option>
                        <option value="2" <?php if ($_SESSION[$search_session_key]['cli_status'] == '2')
                            echo 'selected="selected"'; ?>>Not In Use</option>
                        <option value="0" <?php if ($_SESSION[$search_session_key]['cli_status'] == '0')
                            echo 'selected="selected"'; ?>>Blocked</option>

                    </select>
                </div>
                <div class="searchBar  ">
                    <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                    <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">

                </div>


            </div>


        </form>
    </div>
    <div class="clearfix"></div>
    <div class="ln_solid"></div>
    <div class="col-md-12 row">
        <?php
        dispay_pagination_row_bsd($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination);
        ?>
    </div>
    <div class="clearfix"></div>
    <div class="table-responsive">
        <table class="table table-striped jambo_table table-bordered">
            <thead>
                <tr class="headings">
                    <th class="column-title">Caller ID</th>
                    <th class="column-title">Status </th>
                    <th class="column-title">Action </th>
                </tr>
            </thead>


            <tbody>
                <?php
                $str = '';
                if (count($cli_data['result']) > 0) {
                    foreach ($cli_data['result'] as $single_row) {

                        if ($single_row['cli_status'] == '1')
                            $status = '<span class="label label-success">Active</span>';
                        else if ($single_row['status'] == '2')
                            $status = '<span class="label label-success">Not In Use</span>';
                        else
                            $status = '<span class="label label-danger">Blocked</span>';
                        $delete_link = "crs/clifilter/index/" . param_encrypt($single_row['account_id']) . '/' . $type;
                        $str .= '<tr>                                               
                            <td>' . $single_row['callerid'] . '</td>
                            <td>' . $status . '</td>
                            <td class=" last">
                            <a href="' . site_url('crs/clifilter/edit/' . param_encrypt($data['account_id']) . '/' . param_encrypt($single_row['id']) . '/' . $type) . '" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                            <a href="javascript:void(0);" title="Delete" class="delete"
                                onclick=doConfirmDelete(\'' . $single_row['id'] . '\',"' . $delete_link . '","didclifilter_delete")><i class="fa fa-trash"></i></a>
                            
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


        </table>


    </div>
    <div class="row">
        <?php dispay_pagination_row_bottom($total_records, $_SESSION[$search_session_key]['no_of_rows'], $pagination); ?>
    </div>

</div>