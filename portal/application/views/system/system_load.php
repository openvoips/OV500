<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
 <meta http-equiv="refresh" content="30">
 
<div class="clearfix"></div>    



<div class="col-md-6 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>SIP Proxy</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>

                </li>
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

</div>



<!---->
<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Freeswitch</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>

                </li>
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
                                <td class="text-left"><?php echo $row['anscalls'] . " / " . $row['calls'];
                    ; ?></td>
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


<!-- start customers calls table-->
<!--
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Customer Calls </h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>

                </li>
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">
            <table class="table table-striped jambo_table table-bordered" id="customer_calls_table">
                <thead>
                    <tr class="headings thc">
                        <th class="column-title">Load Balance</th>
                        <th class="column-title">Customer</th>
                        <th class="column-title">Customer IP</th>
                        <th class="column-title">Calls (A/T)</th>
                    </tr>
                </thead>

                <tbody>
<?php /*
  if (count($customer_calls_data['result']) > 0) {
  foreach ($customer_calls_data['result'] as $row) {
  ?>
  <tr>
  <td class="text-left"><?php echo $row['lbaddress']; ?></td>
  <td class="text-left"><?php echo $row['company']; ?></td>
  <td class="text-left"><?php echo $row['user_ip']; ?></td>
  <td class="text-left"><?php  echo  $row['anscalls'] ." / ". $row['calls']; ?></td>
  </tr>
  <?php
  }
  } else {
 */ ?>
                        <tr>
                            <td colspan="3" align="center"><strong>No Record Found</strong></td>
                        </tr>
<?php // } ?>

                </tbody>
            </table>

        </div>

    </div>

</div>-->

<!-- end customers calls table-->


<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Proxy & freeSwitch Calls </h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>

                </li>
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
