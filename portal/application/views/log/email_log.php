<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Search </h2>
            <ul class="nav navbar-right panel_toolbox">

            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form action="<?php echo base_url(); ?>recyclebin/email_log" method="post" name="search_form" id="search_form" data-parsley-validate class="form-horizontal form-label-left">

                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12" >Date Range</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text" name="time_range" id="time_range" value="<?php echo $_SESSION['search_emaillog_data']['s_time_range']; ?>" class="form-control col-md-7 col-xs-12 data-search-field" readonly="readonly" data-parsley-required="">
                    </div>	
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Type</label>
                    <div class="col-md-2 col-sm-9 col-xs-12">
                        <select name="action_type" id="action_type" class="form-control data-search-field">
                            <option value="">Select</option>
                            <?php
                            foreach ($type_options as $option_array) {
                                if ($_SESSION['search_emaillog_data']['s_type'] == $option_array['actionfrom'])
                                    $selected = 'selected="selected"';
                                else
                                    $selected = '';
                                echo '<option value="' . $option_array['actionfrom'] . '" ' . $selected . '>' . $option_array['actionfrom'] . '</option>';
                            }
                            ?>

                        </select>
                    </div>	

                </div>  
                <div class="form-group">     
                    <label class="control-label col-md-2 col-sm-3 col-xs-12" >Text</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <input type="text" name="account_id" id="account_id" value="<?php echo $_SESSION['search_emaillog_data']['s_account_id'] ?>"  class="form-control col-md-7 col-xs-12 data-search-field">
                    </div>


                    <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                    <!--<input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" > -->
                </div>

            </form>
        </div>
    </div>       
</div>    


<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Email Log</h2>
            <ul class="nav navbar-right panel_toolbox">

            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="row">  
            <?php
            dispay_pagination_row($total_records, $_SESSION['search_emaillog_data']['s_no_of_records'], $pagination);
            ?>                    
        </div>     
        <div class="x_content">

            <div class="table-responsive">
                <table id="table-sort" class="table table-striped jambo_table table-bordered">
                    <thead>
                        <tr class="headings thc">
                            <th class="column-title"><nobr>Account</nobr></th>
                    <th class="column-title">Action</th>
                    <th class="column-title">Date</th>
                    <th class="column-title">Subject</th>
                    <th class="column-title">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (count($data['result']) > 0) {
                            $email_history = $data['result'];

                            foreach ($email_history as $email_data) {

                                if ($email_data['company_name'] != '')
                                    $account = $email_data['company_name'];
                                else
                                    $account = $email_data['account_id'];


                                $paid_on_display = '';
                                if ($email_data['action_date'] != '') {
                                    $paid_on = $email_data['action_date'];
                                    $paid_on_timestamp = strtotime($paid_on);
                                    $paid_on_display = date(DATE_FORMAT_1, $paid_on_timestamp);
                                }
                                ?>
                                <tr> 
                                    <td><?php echo $account; ?></td>							
                                    <td><?php echo $email_data['actionfrom']; ?></td>        
                                    <td><?php echo $paid_on_display; ?></td>                                   
                                    <td><?php echo $email_data['subject']; ?></td>
                                    <td class=" last"><a href="javascript:void(0);"  onclick=viewDetails('<?php echo $email_data['email_log_id']; ?>') title="Details">Details</a></td>  
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6" align="center"><strong>No Records Found</strong></td>
                            </tr>
                        <?php } ?>


                    </tbody>
                </table>
            </div> 

        </div>

    </div>


</div> 



<script>
    /*search form*/
    $('#OkFilter').click(function () {
        var no_of_records = $('#no_of_records').val();
        $('#no_of_rows').val(no_of_records);
    });
</script> 

<script>
    $(document).ready(function () {
        $("#time_range").daterangepicker({
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
                'Last 30 Days': [moment().subtract(29, 'days').startOf('days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        showDatatable('table-sort', [], [5, "desc"]);

    });
</script>
<script>
    function viewDetails(id)
    {

        var target_url = BASE_URL + "recyclebin/ajax_email_log";
        var data_post = {
            email_log_id: id
        };
        $.ajax({
            url: target_url,
            dataType: 'json',
            type: 'POST',
            data: data_post,
            success: function (data, textStatus, XMLHttpRequest)
            {
                console.log(data);
                if (data == 'error')
                {
                    alert("error");
                } else
                {
                    //console.log(data.account_id);


                    var table_str = '<table class="table table-striped table-bordered table-collapsed">';
                    table_str += '<tr><td><strong>Account</strong></td><td>' + data.account_id + '</td>';
                    if (data.company_name != null)
                        table_str += '<tr><td><strong>Company</strong></td><td>' + data.company_name + '</td>';
                    table_str += '<tr><td><strong>Action</strong></td><td>' + data.actionfrom + '</td>';
                    table_str += '<tr><td><strong>Date</strong></td><td>' + data.action_date + '</td>';
                    table_str += '<tr><td><strong>Subject</strong></td><td>' + data.subject + '</td>';
                    table_str += '<tr><td><strong>Mail To</strong></td><td>' + data.email_to + '</td>';
                    table_str += '<tr><td><strong>Mail</strong></td><td>' + data.body + '</td>';
                    table_str += '</table>';




                    var modal_header = '<h3 class="modal-title">Email Log' +
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span>' +
                            '</button>' +
                            '</h3>';
                    var modal_body = table_str;

                    var modal_footer = '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';


                    openModal('lg', modal_header, modal_body, modal_footer);
                    $("#my-modal").modal('show');










                    //  alert('sssss 11');	


                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown)
            {
                alert(errorThrown);
            }
        });






    }
</script>