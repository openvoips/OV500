<style type="text/css">
    .data-search-field{
        margin-bottom: 3px;
    }
</style>
<div class="container-fluid">
    <div class="block-header">
        <h2>Calls Detail</h2>		
        <ul class="nav navbar-right panel_toolbox">

        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">

                    <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>reports/Calls">
                        <input type="hidden" name="search_action" value="search" />
                        <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />              
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Call Date Range</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="time_range" id="time_range" class="form-control " value="<?php if (isset($_SESSION['search_cdr_data']['s_time_range'])) echo $_SESSION['search_cdr_data']['s_time_range']; ?>" readonly="readonly" data-parsley-required="" />
                            </div> 
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" >Call disposition </label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <select name="disposition" id="disposition"  class="form-control data-search-field" >
                                    <option value="">All Calls</option>     
                                    <?php
                                    $call_status_array = ['ANSWRED' => 'Answered Calls', 'MISSED' => 'Missed Calls'];
                                    $str = '';
                                    foreach ($call_status_array as $key => $value) {
                                        $selected = ' ';
                                        if ($_SESSION['search_cdr_data']['disposition'] == $key)
                                            $selected = '  selected="selected" ';

                                        $str .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                                    }
                                    echo $str;
                                    ?>  
                                </select> 
                            </div>
                            <label class="control-label col-md-2 col-sm-3 col-xs-12" >Call flow</label>
                            <div class="col-md-2 col-sm-6 col-xs-12">
                                <select name="cdr_type" id="cdr_type"  class="form-control data-search-field" >
                                    <option value="">All Type</option>     
                                    <?php
                                    $call_status_array = ['IN' => 'IN (DID calls)', 'OUT' => 'OUT (PSTN Calls)', 'DEVICE' => 'Device to Device Calls'];
                                    $str = '';
                                    foreach ($call_status_array as $key => $value) {
                                        $selected = ' ';
                                        if ($_SESSION['search_cdr_data']['cdr_type'] == $key)
                                            $selected = '  selected="selected" ';

                                        $str .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                                    }
                                    echo $str;
                                    ?>  
                                </select>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Customer Dialed No</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="dialed_no" id="dialed_no" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_dialed_no']; ?>" class="form-control data-search-field" placeholder=" Dialed No">
                            </div>
                            <?php if (isset($all_field_array['USER-CLI'])): ?>	
                                <label class="control-label col-md-2 col-sm-3 col-xs-12">Caller Number as Received from Customer</label>
                                <div class="col-md-2 col-sm-3 col-xs-12">
                                    <input type="text" name="customer_cli" id="customer_cli" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_customer_cli']; ?>" class="form-control data-search-field" placeholder="User CLI">
                                </div>
                            <?php endif; ?>
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Customer IP from call Originated</label>
                            <div class="col-md-2 col-sm-3 col-xs-12">
                                <input type="text" name="customer_ip" id="customer_ip" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_customer_ip']; ?>" class="form-control data-search-field" placeholder="User IP">
                            </div>
                        </div> 


                        <div class="form-group">

                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Call Duration (Sec)</label>
                            <div class="col-md-2 col-sm-1 col-xs-6">
                                <select name="duration_range" id="duration_range" class="form-control" data-parsley-required="">
                                    <option value="gt" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'gt') ? 'selected="selected"' : ''; ?>>Greater than (>) </option> 
                                    <option value="ls" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'ls') ? 'selected="selected"' : ''; ?>>Less than (<) </option> 
                                    <option value="gteq" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'gteq') ? 'selected="selected"' : ''; ?>>Greater than and Equal to (>=) </option>
                                    <option value="lseq" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'lseq') ? 'selected="selected"' : ''; ?>>Less than & Equal to (<=) </option>
                                    <option value="eq" <?php echo ($_SESSION['search_cdr_data']['s_cdr_call_duration_range'] == 'eq') ? 'selected="selected"' : ''; ?>>Equal to (=) </option>

                                </select>

                                <input type="text" name="call_duration" id="call_duration" value="<?php echo $_SESSION['search_cdr_data']['s_cdr_call_duration']; ?>" class="form-control data-search-field" placeholder="Duration"> 
                            </div>



                        </div> 
                        <div class="ln_solid"></div>
                        <div class="form-group text-center">



                            <div class="searchBar  text-center">
                                <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                                <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" >  

                                <div class="btn-group">
                                    <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                        Export <span class="caret"></span></button>
                                    <ul class="dropdown-menu" role="menu">
                                        <?php
                                        $export_format_array = get_export_formats();
                                        foreach ($export_format_array as $export_format) {
                                            if ($export_format != 'pdf')
                                                echo '<li><a href="' . base_url() . 'reports/calls/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                        }
                                        ?>                            
                                    </ul>
                                </div>  
                            </div>
                        </div>




                    </form>	
                </div>	

                <div class="body">

                    <div class="row">  
                        <?php
                        dispay_pagination_row_bsd($total_records, $_SESSION['search_cdr_data']['s_no_of_records'], $pagination);
                        ?>                    
                    </div>     

                    <div class="table-responsive">
                        <table id="analytics" class="table table-striped table-bordered">
                            <thead>
                                <tr class="headings thc">                            
                                    <?php
                                    foreach ($all_field_array as $field_lebel) {
                                        echo '<th class="column-title"><nobr>' . $field_lebel . '</nobr></th>';
                                    }
                                    ?>							
                                </tr>
                            </thead>	
                            <tfoot>
                                <tr class="headings thc">                            
                                    <?php
                                    foreach ($all_field_array as $field_lebel) {
                                        echo '<th class="column-title">' . $field_lebel . '</th>';
                                    }
                                    ?>							
                                </tr>
                            </tfoot>		
                            <tbody>
                                <?php
                                if (isset($listing_count) && $listing_count > 0) {
                                    foreach ($listing_data as $listing_row) {
                                        //var_dump($listing_row);
                                        echo '<tr>';
                                        foreach ($all_field_array as $field_name => $field_lebel) {
                                            echo '<td><nobr>';
                                            if ($field_name == 'Account') {

                                                if ($listing_row['customer_company_name'] != '') {

                                                    echo $listing_row['customer_company_name'] . ' ( ' . $listing_row[$field_name] . ' ) ';
                                                } else {
                                                    echo $listing_row[$field_name];
                                                }
                                            } elseif ($field_name == 'DurationMinute') {
                                                $min = $listing_row['Duration'] / 60;
                                                echo (int) $min;
                                            } elseif ($field_name == 'recording_file') {
                                                $file_name = $listing_row[$field_name];
                                                $file_path = $file_name;
                                                if ($file_name != '' && file_exists($file_path)) {
                                                    echo '<a href="' . site_url('download/recording/' . param_encrypt($file_path)) . '">Download</a>';
                                                }
                                            } else {
                                                echo $listing_row[$field_name];
                                            }

                                            echo '</nobr></td>';
                                        }
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>



            </div>
        </div>
    </div>
</div>



<script>
    $(document).ready(function () {
        $("#time_range").daterangepicker({
            timePicker: !0,
            timePickerIncrement: 1,
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


        showDatatable('analytics', [], [1, "asc"]);

        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
</script>