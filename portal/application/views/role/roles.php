<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0
// License https://www.gnu.org/licenses/agpl-3.0.html
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################

$account_types_array_temp = get_account_types();
$account_types_array = array();
foreach ($account_types_array_temp as $account_types_array_temp_sub) {
    $account_types_array = array_merge($account_types_array, $account_types_array_temp_sub);
}
unset($account_types_array['ADMIN']);
?>
<?php
//echo '<pre>';
//print_r($account_types_array);
//print_r($data);
//echo '</pre>';
?>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Roles</h2>         
            <div class="clearfix"></div>
        </div>
        <div class="x_content">          

            <div class="table-responsive">
                <table class="table table-striped jambo_table table-bordered" id="table-sort">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title">Role </th>
                            <th class="column-title">Permissions </th>
                            <th class="column-title no-link last" width="120"><span class="nobr">Actions</span> </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (count($account_types_array) > 0) {
                            foreach ($account_types_array as $account_type => $account_type_display) {
                                $permissions = '';

                                if (isset($data[$account_type])) {
                                    $permissions .= '<ul class="to_do">';
                                    foreach ($data[$account_type]['permissions'] as $item_name => $permission_array_single) {
                                        $permissions .= '<li class="mail_list"><h3>' . ucfirst($item_name) . '</h3></li>';
                                        $permissions .= '<li>';
                                        $permissions .= implode(', ', $permission_array_single);
                                        $permissions .= '</li>';
                                    }
                                    $permissions .= '</ul>';
                                    $permissions = htmlentities($permissions);
                                }
                                ?>
                                <tr>

                                    <td><?php echo $account_type_display; ?></td>                        
                                    <td><?php
                                        if ($permissions != '')
                                            echo '<button type="button" class="btn btn-primary" data-placement="left" data-toggle="popover" title="" data-content="' . $permissions . '"><i class="fa fa-info-circle"></i> </button>';
                                        ?></td>
                                    <td class="last">
                                        <a href="<?php echo base_url(); ?>roles/accessConfig/<?php echo param_encrypt($account_type); ?>" title="Edit"><i class="fa fa-pencil-square-o"></i></a>                        </td>
                                </tr>

                                <?php
                            }
                        }
                        else {
                            ?>
                            <tr>
                                <td colspan="8" align="center"><strong>No Record Found</strong></td>
                            </tr>
                            <?php
                        }
                        ?>


                    </tbody>
                </table>
            </div>                    

        </div>
    </div>
</div>
<script>


    $(function () {
        $('[data-toggle="popover"]').popover({
            animation: true,
            container: "body",
            placement: "bottom",
            trigger: "hover",
            "html": true,
        });
    })
</script>              
<script>
    $(document).ready(function () {
        showDatatable('table-sort', [2], [0, "asc"]);
    });
</script>            