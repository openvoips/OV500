<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<meta http-equiv="refresh" content="30">


<div class="container-fluid">
    <div class="block-header">

        <ul class="nav navbar-right panel_toolbox">

        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">




                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>SIP Proxy</h2>
                                <ul class="nav navbar-right panel_toolbox">

                                </ul>
                                <div class="clearfix"></div>
                            </div>


                            <table class="table table-striped jambo_table table-bordered">
                                <thead>
                                    <tr class="headings thc">
                                        <th class="column-title">IP</th>
                                        <th class="column-title">Calls (A/T)</th>

                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    if (count($proxy_data['result']) > 0) {
                                        foreach ($proxy_data['result'] as $row) {
                                            ?>
                                            <tr>  <td class="text-left"><?php echo $row['lbaddress']; ?></td>
                                                <td class="text-left"><?php echo $row['anscalls'] . " / " . $row['calls']; ?></td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="2" align="center"><strong>No Record Found</strong></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>



                        </div>

                    </div>



                    <!---->
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Freeswitch</h2>
                                <ul class="nav navbar-right panel_toolbox">

                                </ul>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content">
                                <table class="table table-striped jambo_table table-bordered">
                                    <thead>
                                        <tr class="headings thc">
                                            <th class="column-title">IP</th>
                                            <th class="column-title">Calls (A/T)</th>

                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php
                                        if (count($switch_data['result']) > 0) {
                                            foreach ($switch_data['result'] as $row) {
                                                ?>
                                                <tr>  <td class="text-left"><?php echo $row['fs_host']; ?></td>
                                                    <td class="text-left"><?php
                                                        echo $row['anscalls'] . " / " . $row['calls'];
                                                        ;
                                                        ?></td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="2" align="center"><strong>No Record Found</strong></td>
                                            </tr>
                                        <?php } ?>

                                    </tbody>
                                </table>

                            </div>

                        </div>

                    </div>     


                    <div class="col-md-12 col-sm-6 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Proxy & freeSwitch Calls </h2>
                                <ul class="nav navbar-right panel_toolbox">

                                </ul>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content">
                                <table class="table table-striped jambo_table table-bordered">
                                    <thead>
                                        <tr class="headings thc">
                                            <th class="column-title">Proxy</th>
                                            <th class="column-title">Switch</th>
                                            <th class="column-title">Calls (A/T)</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if (count($proxy_switch_data['result']) > 0) {
                                            foreach ($proxy_switch_data['result'] as $row) {
                                                ?>
                                                <tr> 
                                                    <td class="text-left"><?php echo $row['proxy']; ?></td>
                                                    <td class="text-left"><?php echo $row['switch']; ?></td>
                                                    <td class="text-left"><?php echo $row['anscalls'] . " / " . $row['calls']; ?></td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="3" align="center"><strong>No Record Found</strong></td>
                                            </tr>
                                        <?php } ?>

                                    </tbody>
                                </table>

                            </div>

                        </div>

                    </div>


                    <div class="clearfix"></div>

                    <script>
                        $(document).ready(function () {
                            showDatatable('customer_calls_table', [], [3, "desc"]);

                        });
                    </script>
