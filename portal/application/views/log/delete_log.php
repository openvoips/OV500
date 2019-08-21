<?php
//echo '<pre>';print_r($delete_type_options);echo '</pre>';
?>

<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Delete Log</h2>
            <ul class="nav navbar-right panel_toolbox">
                

            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>recyclebin/">
                <input type="hidden" name="search_action" value="search" />

                <div class="form-group">


                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID</label>
                    <div class="col-md-2 col-sm-8 col-xs-12">
                        <input type="text"  name="account_id" id="account_id"  value="<?php echo $_SESSION['search_log_data']['s_account_id']; ?>" class="form-control data-search-field" placeholder="Account ID">
                    </div>

                    <label class="control-label col-md-1 col-sm-3 col-xs-12 col-md-offset-1">Type</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <select name="delete_type" id="delete_type" class="form-control data-search-field">
                            <option value="">Select</option>                    
                            <?php
                            $str = '';
                            foreach ($delete_type_options as $key => $delete_type_array) {
                                $delete_type = $delete_type_array['sql_table'];
                                $selected = ' ';
                                if ($_SESSION['search_log_data']['s_delete_type'] == $delete_type)
                                    $selected = '  selected="selected" ';
                                $str .= '<option value="' . $delete_type . '" ' . $selected . '>' . $delete_type . '</option>';
                            }
                            echo $str;
                            ?>
                        </select>
                    </div>





                </div>
                <div class="form-group">  

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Date</label>
                    <div class="col-md-6 col-sm-9 col-xs-12">
                        <input type="text" name="time_range" id="time_range" class="form-control data-search-field" value="<?php if (isset($_SESSION['search_log_data']['s_time_range'])) echo $_SESSION['search_data']['s_time_range']; ?>" readonly="readonly" />
                    </div>

                    <div class="searchBar ">      
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary" >                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" >                

                    </div>


                </div>


            </form> 


            <div class="clearfix"></div>
            <div class="ln_solid"></div>
            <?php echo '<div class="btn-toolbar" role="toolbar">							  						
							  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
									   ' . $pagination . '
							  </div>
							</div>'; ?>
            <div class="table-responsive">
                <table class="table table-striped jambo_table  table-bordered">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title">Account ID </th>                            
                            <th class="column-title">Delete Type </th>                            
                            <th class="column-title">Delete Date </th>
                            <th class="column-title no-link last" width="140"><span class="nobr">Actions</span> </th>                           
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (count($data) > 0) {
                            foreach ($data as $log_data) {
                                ?>
                                <tr>

                                    <td><?php echo $log_data['sql_key']; ?></td>
                                    <td><?php echo $log_data['sql_table']; ?></td>
                                    <td><?php echo $log_data['dt_created']; ?></td>                              
                                    <td class=" last">
                                        <a href="<?php echo base_url(); ?>recyclebin/details/<?php echo param_encrypt($log_data['activity_id']); ?>" title="Details"><strong>Details</strong></a>
                                    </td>
                                </tr>

                                <?php
                            }
                        } else {
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
            <?php echo '<div class="btn-toolbar" role="toolbar">
							  
							  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
									   ' . $pagination . '
							  </div>
							</div>'; ?>

        </div>
    </div>
</div>


<link href="<?php echo base_url() ?>theme/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet"> 
<script src="http://localhost/mt_switch/theme/vendors/moment/min/moment.min.js"></script>
<script src="http://localhost/mt_switch/theme/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
<script>
    $(document).ready(function () {
        $("#time_range").daterangepicker({
            timePicker: !0,
            autoUpdateInput: false,
            timePickerIncrement: 5,
            timePicker24Hour: true,
            ranges: {
                'Last 15 Minute': [moment().subtract(15, 'minute'), moment()],
                'Last 30 Minute': [moment().subtract(30, 'minute'), moment()],
                'Last 1 Hour': [moment().subtract(1, 'hour'), moment()],
                'Today': [moment().startOf('days'), moment().endOf('days')],
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('#time_range').val("<?php echo $_SESSION['search_log_data']['s_time_range']; ?>");

        $('#time_range').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format("<?php echo SCRIPT_DATE_FORMAT_1; ?>") + ' - ' + picker.endDate.format("<?php echo SCRIPT_DATE_FORMAT_1; ?>"));
        });

        $('#time_range').on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
        })

    });
</script>