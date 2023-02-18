<style type="text/css">
    .new_comment {
        appearance:none;
        -moz-appearance:none;
        -webkit-appearance:none;
        background-color: #169F85;
    }
    .new_ticket {
        appearance:none;
        -moz-appearance:none;
        -webkit-appearance:none;
        background-color: #31B0D5;
    }

</style>
<?php
$tab_index = 1;
$status_array = get_t_status();
?>
<link href="<?php echo base_url() ?>theme/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css" rel="stylesheet">
<link href="<?php echo base_url() ?>theme/default/css/ticket.css" rel="stylesheet">
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Search </h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                <li><a href="<?php echo base_url() ?>ticket/create"><input type="button" value="Create Ticket" name="add_link" class="btn btn-primary"></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">     

            <form action="<?php echo base_url(); ?>ticket" method="post" name="search_form" id="search_form" data-parsley-validate class="form-horizontal form-label-left">

                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">

                    <label class="control-label col-md-1 col-sm-3 col-xs-12" >Ticket No.</label>
                    <div class="col-md-2 col-sm-6 col-xs-12">
                        <input type="text" name="ticket_number" id="ticket_number" value="<?php echo $_SESSION['search_t_data']['s_ticket_number']; ?>" class="form-control col-md-7 col-xs-12 data-search-field" >
                    </div>	

                    <label class="control-label col-md-2 col-sm-3 col-xs-12 col-md-offset-1" >Account ID</label>
                    <div class="col-md-2 col-sm-6 col-xs-12">
                        <input type="text" name="account_id" id="account_id" value="<?php echo $_SESSION['search_t_data']['s_account_id']; ?>" class="form-control col-md-7 col-xs-12 data-search-field" >
                    </div>	
                    <label class="control-label col-md-1 col-sm-3 col-xs-12" >Status</label>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <select name="status" id="status" class="form-control data-search-field">
                            <option value="">Select</option>

                            <?php
                            foreach ($status_array as $status_key => $status_value) {
                                $selected = '';
                                if ($_SESSION['search_t_data']['s_status'] == $status_key)
                                    $selected = 'selected="selected"';

                                echo '<option value="' . $status_key . '" ' . $selected . '>' . $status_value . '</option>';
                            }
                            ?>                
                            <option value="new" <?php if ($_SESSION['search_t_data']['s_status'] == 'new') echo 'selected="selected"'; ?> class="new_ticket" >New Ticket</option>
                            <option value="customer_replied" <?php if ($_SESSION['search_t_data']['s_status'] == 'customer_replied') echo 'selected="selected"'; ?> class="new_comment" >New Comment</option>
                        </select>
                    </div>	


                </div>   

                <div class="form-group">

                    <label class="control-label col-md-1 col-sm-3 col-xs-12">Category</label>
                    <div class="col-md-2 col-sm-6 col-xs-12">
                        <select name="category_id" id="category_id" class="form-control data-search-field">
                            <option value="">Select</option>   
                            <?php
                            $str = '';
                            if (isset($category_data['result']) && count($category_data['result']) > 0) {
                                foreach ($category_data['result'] as $key => $parent_category_array) {
                                    $str .= '<optgroup label="' . $parent_category_array['category_name'] . '">';
                                    if (isset($parent_category_array['sub']) && count($parent_category_array['sub']) > 0) {
                                        foreach ($parent_category_array['sub'] as $key => $category_array) {
                                            $selected = ' ';
                                            if ($_SESSION['search_t_data']['s_category_id'] == $category_array['category_id'])
                                                $selected = ' selected="selected" ';
                                            $str .= '<option value="' . $category_array['category_id'] . '" ' . $selected . '>' . $category_array['category_name'] . '</option>';
                                        }
                                    }
                                    else {
                                        $selected = ' ';
                                        if ($_SESSION['search_t_data']['s_category_id'] == $parent_category_array['category_id'])
                                            $selected = '  selected="selected" ';
                                        $str .= '<option value="' . $parent_category_array['category_id'] . '" ' . $selected . '>' . $parent_category_array['category_name'] . '</option>';
                                    }
                                    $str .= '</optgroup>';
                                }
                            }
                            echo $str;
                            ?>
                        </select>   


                    </div>	

                    <label class="control-label col-md-2 col-sm-3 col-xs-12 col-md-offset-1">Assigned to</label>
                    <div class="col-md-2 col-sm-6 col-xs-12">
                        <select name="assigned_to_id" id="assigned_to_id" class="form-control data-search-field">
                            <option value="">Select</option> 
                            <option value="myself" <?php if ($_SESSION['search_t_data']['s_assigned_to_id'] == 'myself') echo '  selected="selected" '; ?>>Myself</option> 
                            <?php
                            $str = '';
                            if (isset($assignto_data['result']) && count($assignto_data['result']) > 0) {
                                foreach ($assignto_data['result'] as $key => $assignto_array) {
                                    $selected = ' ';
                                    if ($_SESSION['search_t_data']['s_assigned_to_id'] == $assignto_array['assigned_to_id'])
                                        $selected = '  selected="selected" ';
                                    $str .= '<option value="' . $assignto_array['assigned_to_id'] . '" ' . $selected . '>' . $assignto_array['assigned_to_name'] . '</option>';
                                }
                            }
                            echo $str;
                            ?>
                        </select>
                    </div>	

                    <label class="control-label col-md-1 col-sm-3 col-xs-12" >Subject</label>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <input type="text" name="subject" id="subject" value="<?php echo $_SESSION['search_t_data']['s_subject']; ?>" class="form-control col-md-7 col-xs-12 data-search-field" >
                    </div>	


                </div> 
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12" >Create Date Range</label>                   

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text" name="create_dt" id="create_dt" value="<?php echo $_SESSION['search_t_data']['s_create_dt']; ?>"  readonly="readonly"  class="form-control col-md-7 col-xs-12 data-search-field">                            
                    </div>

                </div>					

                <div class="form-group">


                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <input type="checkbox" name="assigned_to_me" id="assigned_to_me" value="Y" <?php if ($_SESSION['search_t_data']['s_assigned_to_id'] == 'myself') echo 'checked="checked"'; ?>  />&nbsp;&nbsp;&nbsp;Assigned to me
                    </div>


                    <div class="searchBar ">  
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" >
                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    echo '<li><a href="' . base_url() . 'ticket/index/-1/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
                                }
                                ?>                            
                            </ul>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div class="clearfix"></div>
        <div class="ln_solid"></div>
        <div class="row">  
            <?php
            dispay_pagination_row($total_records, $_SESSION['search_t_data']['s_no_of_records'], $pagination);
            ?>                    
        </div>  


        <table class="table table-striped ">
            <thead>
                <tr>
                    <th >Subject</th>
                    <th width="230">Account</th>
                    <th width="150">Last Post</th>
                    <th width="90">Status</th>
                    <th width="190" align="center"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($data['result']) && count($data['result']) > 0) {
                    foreach ($data['result'] as $ticket_data) {
                        ?>
                        <tr>                  
                            <td class="td_2">
                                <a href="<?php echo base_url(); ?>ticket/details/<?php echo param_encrypt($ticket_data['ticket_id']); ?>" class="tag3"><?php echo $ticket_data['subject']; ?></a>
                            </td>
                            <td class="td_3">
                                <p>
                                    <a href="<?php echo base_url('customers/edit/') . param_encrypt($ticket_data['account_id']); ?>" class="tag2"><?php echo $ticket_data['account_id']; ?></a>
                                    <?php
                                    if ($ticket_data['company_name'] != '') {
                                        echo '<br />' . $ticket_data['company_name'];
                                    }
                                    ?>
                                    <br />
                                    Created: <small><i class="fa fa-clock-o"></i> <?php echo date(DATE_FORMAT_1, strtotime($ticket_data['create_date'])); ?></small>
                                </p>
                            </td>
                            <td class="td_4">
                                <p>
                                    <?php
                                    $last_post_by = 'Customer';
                                    $last_post_created_date = '';
                                    if (isset($ticket_data['last_post']['created_by'])) {
                                        if ($ticket_data['last_post']['created_by'] != $ticket_data['account_id'])
                                            $last_post_by = 'ADMIN';

                                        $last_post_created_date = date(DATE_FORMAT_1, strtotime($ticket_data['last_post']['create_date']));
                                        $last_post_created_date = '<small><i class="fa fa-clock-o"></i> ' . $last_post_created_date . '</small>';
                                    }
                                    ?>
                                    By <strong><?php echo $last_post_by; ?></strong> <br /> 
                                    <?php echo $last_post_created_date; ?>
                                </p>


                            </td>
                            <td class="td_5">
                                <?php
                                $status_array = get_t_status();
                                $ticket_status = $ticket_data['status'];
                                if (in_array($ticket_status, array('open', 'assigned', 'working', 'waiting-confirmation', 'not-fixed')))
                                    $btn_success = 'btn-success';
                                else
                                    $btn_success = 'btn-danger';

                                if (isset($status_array[$ticket_status]))
                                    $ticket_status_display = $status_array[$ticket_status];
                                else
                                    $ticket_status_display = $ticket_status;

                                echo '<button type="button" class="btn ' . $btn_success . ' btn-xs">' . $ticket_status_display . '</button>';
                                ?>
                            </td>
                            <td class="td_6" align="center" >
                                <p>
                                    <strong><?php echo $ticket_data['ticket_number']; ?></strong>
                                    <br />
                                    <strong><?php echo $ticket_data['total_post']; ?></strong> Comments
                                    <br />
                                    Category:<i># <strong><?php echo $ticket_data['category']['category_name']; ?></strong></i>
                                    <br />Assigned to:<i># <strong><?php
                                            echo $ticket_data['assigned_to']['assigned_to_name'];
                                            if (isset($ticket_data['assigned_to_user_name']) && $ticket_data['assigned_to_user_name'] != '')
                                                echo ' / ' . $ticket_data['assigned_to_user_name'];
                                            ?></strong></i>
                                </p>
                            </td>
                        </tr>

                        <?php
                    }
                }
                else {
                    ?>
                    <tr>
                        <td colspan="6" align="center"><strong>No Record Found</strong></td>
                    </tr>
                    <?php
                }
                ?>	




            </tbody>
        </table>

        <div class="clearfix"></div>


    </div>                

</div> 
<script src="<?php echo base_url() ?>theme/vendors/moment/min/moment.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/moment/min/moment-timezone-with-data.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script> 
<script>
    $(document).ready(function () {

        $("#create_dt").daterangepicker({
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
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
</script>