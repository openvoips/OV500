<style type="text/css">
    .data-search-field{
        margin-bottom: 3px;
    }
</style>
<?php 
 $listing_count = 0;
if(isset($listing_data['result']))
$listing_count=count($listing_data['result']);
            ?>
<div class="container-fluid">
    <div class="block-header">
        <h2>Cause Summary</h2>
        <ul class="nav navbar-right panel_toolbox">

        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">
                    <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
                        <input type="hidden" name="search_action" value="search" />			
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Date Range</label>
                            <div class="col-md-3 col-sm-3 col-xs-12">
                                <input type="text" name="daterange" id="daterange" class="form-control data-search-field" value="<?php if (isset($_SESSION[$search_session_key]['daterange'])) echo $_SESSION[$search_session_key]['daterange']; ?>" />
                            </div>

                             <label class="control-label col-md-2 col-sm-3 col-xs-12">CDR Type</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <select name="cdr_type" id="cdr_type"  class="form-control data-search-field" >
                                    <option value="">All Type</option>     
                                    <?php
                                    $call_status_array = ['IN' => 'IN (DID calls)', 'OUT' => 'OUT (PSTN Calls)', 'DEVICE' => 'Device to Device Calls'];
                                    $str = '';
                                    foreach ($call_status_array as $key => $value) {
                                        $selected = ' ';
                                        if ($_SESSION[$search_session_key]['cdr_type'] == $key)
                                            $selected = '  selected="selected" ';

                                        $str .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                                    }
                                    echo $str;
                                    ?>  
                                </select>
                               
                            </div>	           

                            
                           
                        </div>       
                        <div class="form-group">

                                 

                            <label class="control-label col-md-2 col-sm-3 col-xs-12">SIP Code</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="sipcode" id="sipcode" value="<?php if (isset($_SESSION[$search_session_key]['sipcode'])) echo $_SESSION[$search_session_key]['sipcode']; ?>" class="form-control data-search-field" placeholder="SIP Code">
                            </div> 

                             <label class="control-label col-md-2 col-sm-3 col-xs-12">Carrier Code</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="carrier_id" id="carrier_id" value="<?php if (isset($_SESSION[$search_session_key]['carrier_id'])) echo $_SESSION[$search_session_key]['carrier_id']; ?>" class="form-control data-search-field" placeholder="Carrier Code">

                            </div>      

                        </div>       
                      
                     
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Search data should Group by</label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input type="checkbox"  id="g_sip" name="g_sip" <?php if (isset($_SESSION[$search_session_key2]['s_g_sip']) && $_SESSION[$search_session_key2]['s_g_sip'] == 'Y') echo 'checked'; ?>> <label for="g_sip">SIP Code </label>
                                </div>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input type="checkbox" id="g_carrier" name="g_carrier" <?php if (isset($_SESSION[$search_session_key2]['s_g_carrier']) && $_SESSION[$search_session_key2]['s_g_carrier'] == 'Y') echo 'checked'; ?>>  <label for="g_carrier">Carrier   </label>
                                </div>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input value="" type="checkbox" id="g_cdr_type" name="g_cdr_type" <?php if (isset($_SESSION[$search_session_key2]['s_g_cdr_type']) && $_SESSION[$search_session_key2]['s_g_cdr_type'] == 'Y') echo 'checked'; ?>> <label for="g_cdr_type">CDR Type</label>
                                </div>
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input value="" type="checkbox" id="g_disposition_cause" name="g_disposition_cause" <?php if (isset($_SESSION[$search_session_key2]['s_g_disposition_cause']) && $_SESSION[$search_session_key2]['s_g_disposition_cause'] == 'Y') echo 'checked'; ?>><label for="g_disposition_cause"> Cause</label>
                                </div>                                
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input type="checkbox" id="g_q850" name="g_q850" <?php if (isset($_SESSION[$search_session_key2]['s_g_q850']) && $_SESSION[$search_session_key2]['s_g_q850'] == 'Y') echo 'checked'; ?>> <label for="g_q850">Q850 Code </label>
                                </div> 
                                <div class="checkbox col-md-2 col-sm-6 col-xs-12">
                                    <input type="checkbox" id="g_date" name="g_date" <?php if (isset($_SESSION[$search_session_key2]['s_g_date']) && $_SESSION[$search_session_key2]['s_g_date'] == 'Y') echo 'checked'; ?>> <label for="g_date">Date</label>
                                </div> 
                                
                            </div>


                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group text-center">
                            <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                            <input type="button" value="Reset" name="search_reset" class="btn btn-info" onclick="location.href = ''">                           

                        </div>
                    </form>		

                </div>

                <div class="body">

                    

                    <h5>Total Records : <?php
                        if (isset($listing_count))
                            echo $listing_count;
                        else
                            echo '0';
                        ?></h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table id="table-sort" class="table table-striped jambo_table bulk_action table-bordered">
                            <thead>
                                <tr class="headings thc">
                                   <?php if (isset($_SESSION[$search_session_key2]['s_g_date']) && $_SESSION[$search_session_key2]['s_g_date'] == 'Y') echo'<th class="column-title">Date</th>'; ?>
                                     <?php if (isset($_SESSION[$search_session_key2]['s_g_carrier']) && $_SESSION[$search_session_key2]['s_g_carrier'] == 'Y') echo'<th class="column-title">Carrier</th>'; ?>
                                    <?php if (isset($_SESSION[$search_session_key2]['s_g_cdr_type']) && $_SESSION[$search_session_key2]['s_g_cdr_type'] == 'Y') echo '<th class="column-title">CDR Type</th>'; ?>
                                   
                                    <th class="column-title" >Total Calls</th>                                   
                                    <th class="column-title" >Total Duration (Sec)</th> 
				    <th class="column-title" >Total Cost</th>
                                    <?php
                                    if (isset($_SESSION[$search_session_key2]['s_g_sip']) && $_SESSION[$search_session_key2]['s_g_sip'] == 'Y')
                                        echo'<th class="column-title" width="100">SIP Code</th>';
                                    ?>
 					<?php if (isset($_SESSION[$search_session_key2]['s_g_disposition_cause']) && $_SESSION[$search_session_key2]['s_g_disposition_cause'] == 'Y') echo'<th class="column-title">Cause</th>'; ?>
                                     
                                    <?php
                                    if (isset($_SESSION[$search_session_key2]['s_g_q850']) && $_SESSION[$search_session_key2]['s_g_q850'] == 'Y')
                                        echo'<th class="column-title" width="100">Q850 Code</th>';
                                    ?>
                                    
                                </tr>
                            </thead>		
                            <tbody>
                                <?php
                                /*
                                $currency_abbr = function ($id) use ($currency_data) {
                                    $key = array_search($id, array_column($currency_data, 'currency_id'));
                                    if ($key === false)
                                        return '';
                                    else
                                        return $currency_data[$key]['name'];
                                };*/

                                if (isset($listing_data['result']) && $listing_data['result'] > 0) {
                                    foreach ($listing_data['result'] as $listing_row) {

                    
                                        ?>
                                        <tr>

                                          <?php if (isset($_SESSION[$search_session_key2]['s_g_date']) && $_SESSION[$search_session_key2]['s_g_date'] == 'Y') echo '<td>' . $listing_row['end_date'] . '</td>'; ?>

					    <?php if (isset($_SESSION[$search_session_key2]['s_g_carrier']) && $_SESSION[$search_session_key2]['s_g_carrier'] == 'Y') echo '<td>' .$listing_row['carrier_name'].' ['.$listing_row['carrier_id'].']' . '</td>'; ?>
                                            <?php if (isset($_SESSION[$search_session_key2]['s_g_cdr_type']) && $_SESSION[$search_session_key2]['s_g_cdr_type'] == 'Y') echo '<td>' . $listing_row['cdr_type'] . '</td>'; ?>
                                           
                                            <td><?php echo $listing_row['calls']; ?></td>
                                            
                                            <td><?php echo $listing_row['duration']; ?></td>
					    <td><?php echo $listing_row['cost']; ?></td>

                                            <?php if (isset($_SESSION[$search_session_key2]['s_g_sip']) && $_SESSION[$search_session_key2]['s_g_sip'] == 'Y') echo '<td>' . $listing_row['SIPCODE'] . '</td>'; ?>
                                            <?php if (isset($_SESSION[$search_session_key2]['s_g_disposition_cause']) && $_SESSION[$search_session_key2]['s_g_disposition_cause'] == 'Y') echo '<td>' . $listing_row['disposition_cause'] . '</td>'; ?>
                                             <?php if (isset($_SESSION[$search_session_key2]['s_g_q850']) && $_SESSION[$search_session_key2]['s_g_q850'] == 'Y') echo '<td>' . $listing_row['Q850CODE'] . '</td>'; ?>
                                            
                                        </tr>

                                        <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?php //   ddd($listing_data);?>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .form-horizontal .control-label {
        padding: 7px 5px;
    }
    .alert_yellow{
        background-color:#FFFF33;
        color:#000000;
    }
    .alert_green{
        background-color:#2E8B57;
        color:#ffffff;
    }
    .alert_red{
        background-color:#F14E66;
        color:#ffffff;
    }
    table.jambo_table tbody tr:hover td {
        color:#8b87bb;
        background-color:#f0faf8;
    }
</style>

<script>
    $(document).ready(function () {

        $("#daterange").daterangepicker({
            timePicker: !0,
            timePickerIncrement: 5,
            locale: {
                format: "YYYY-MM-DD HH:mm"
            },
            timePicker24Hour: true,
            ranges: {
                'Last 15 Minute': [moment().subtract(15, 'minute'), moment()],
                'Last 30 Minute': [moment().subtract(30, 'minute'), moment()],
                'Last 1 Hour': [moment().subtract(1, 'hour'), moment()],
                'Today': [moment().startOf('days'), moment().endOf('days')],
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                /*'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],*/
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

      
        showDatatable('table-sort', [], [0, "asc"]);

    });
</script>