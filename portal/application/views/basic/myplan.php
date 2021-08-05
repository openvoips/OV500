
<!-- Table row -->
<div class="">
    <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Your Services in Currency <?php echo $data['currency']['name'] . ' (' . $data['currency']['symbol'] . ')' ?></h2>
            <div class="clearfix"></div>
        </div>

    </div>
    <?php
    if (isset($data['tariff'])) {
        ?>
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    <div class="x_title">
                        <h2>Your Tariff Plan </h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped jambo_table table-bordered" id="table-sort">
                            <thead>
                                <tr>                                   
                                    <th>Service</th>    
                                    <th>Detail</th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr>                                    
                                    <td>Tariff</td>
                                    <td><?php echo $data['tariff']['tariff_name'] ?></td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    <?php } ?>

    <?php if (count($plan_data) > 0) { ?>
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    <div class="x_title">
                        <h2>Your Service Plan </h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped jambo_table table-bordered" id="table-sort">
                            <thead>
                                <tr class="headings ">
                                    <th class="column-title">Plan Name</th>
                                    <th class="column-title">Item</th>
                                    <th class="column-title">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($plan_data as $plan) {
                                    $reguler_charges = '';
                                    if ($plan['reguler_charges'] == 'NA') {
                                        $reguler_charges = 'No Charges';
                                    }
                                    if ($plan['reguler_charges'] == 'EMA') {
                                        $reguler_charges = 'Every Month For All Item';
                                    }
                                    if ($plan['reguler_charges'] == 'EME') {
                                        $reguler_charges = 'Every Month For Each Item';
                                    }
                                    $additional_charges_as = '';
                                    if ($plan['additional_charges_as'] == 'NA') {
                                        $additional_charges_as = 'No Charges';
                                    }
                                    if ($plan['additional_charges_as'] == 'SE') {
                                        $additional_charges_as = 'One Time Service Sertup Charges';
                                    }
                                    ?>
                                    <tr>
                                        <td ><?php echo $plan['priceplan_name'] ?></td>
                                        <td><?php echo $plan['item_name'] ?></td>
                                        <?php echo '<td >' . $plan['description'] . ' Reguler Charges ' . $plan['symbol'] . number_format((float) $plan['charges'], $data['dp'], '.', '') . ' Bassed On ' . $reguler_charges . ' <br>And Additional Charges ' . $plan['symbol'] . number_format((float) $plan['additional_charges'], $data['dp'], '.', '') . ' Bassed On ' . $additional_charges_as . '</td>' ?>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    <?php } ?>


    <?php if (count($data['bundle_package']) > 0) { ?>
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Bundle & Package</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <table class="table table-striped jambo_table table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Bundle</th>
                                <th class="column-title">Assign</th>
                                <th class="column-title">Bundle1 Type</th>
                                <th class="column-title">Bundle1 Value</th>
                                <th class="column-title">Bundle2 Type</th>
                                <th class="column-title">Bundle2 Value</th>
                                <th class="column-title">Bundle3 Type</th>
                                <th class="column-title">Bundle3 Value</th>
                                <th class="column-title">Allowed Prefixes</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if (isset($data['bundle_package']) && count($data['bundle_package']) > 0) {
                                foreach ($data['bundle_package'] as $package_data) {
                                    ?>
                                    <tr >
                                        <td><?php echo $package_data['bundle_package_name'] . ' (' . $package_data['bundle_package_id'] . ')'; ?></td>
                                        <td ><?php echo $package_data['bundle_count']; ?></td>
                                        <td ><?php echo $package_data['bundle1_type']; ?></td>
                                        <td ><?php echo number_format((float) $package_data['bundle1_value'], $data['dp'], '.', ''); ?></td>
                                        <td ><?php echo $package_data['bundle2_type']; ?></td>
                                        <td ><?php echo number_format((float) $package_data['bundle2_value'], $data['dp'], '.', ''); ?></td>
                                        <td ><?php echo $package_data['bundle3_type']; ?></td>
                                        <td ><?php echo number_format((float) $package_data['bundle3_value'], $data['dp'], '.', ''); ?></td>
                                        <td ><?php echo wordwrap(implode(', ', array_unique(explode(',', $package_data['prefix']))), 20, "<br>\n", TRUE); ?></td>

                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="5" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>


                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    <?php } ?>
</div>

