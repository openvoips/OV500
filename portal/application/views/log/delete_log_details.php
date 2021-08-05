<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Delete Log Details</h2>
            <ul class="nav navbar-right panel_toolbox">


            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 

            <table class="table table-striped jambo_table  table-bordered">
                <thead>
                    <tr class="headings thc">
                        <th class="column-title">Account ID </th>                            
                        <th class="column-title">Delete Type </th>                            
                        <th class="column-title">Delete Date </th>                          
                    </tr>
                </thead>
                <tbody>                        
                    <tr>                               
                        <td><?php echo $data['sql_key']; ?></td>
                        <td><?php echo $data['sql_table']; ?></td>
                        <td><?php echo $data['dt_created']; ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="clearfix"></div>
            <div class="ln_solid"></div>
            <table class="table table-striped jambo_table  table-bordered">
                <thead>
                    <tr class="headings thc">
                        <th class="column-title">Table</th>                            
                        <th class="column-title">Data </th>                                 
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($activity_data) > 0) {
                        foreach ($activity_data as $log_data) {
                            $table_data = unserialize($log_data['sql_query']);

                            echo '<tr>  
									<th class="column-title">' . $log_data['sql_table'] . '</th>                            
                            		<th class="column-title">';
                            echo '<pre>';
                            print_r($table_data);
                            echo '</pre>';
                            echo '</th>
									</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>

            <div class="btn-toolbar" role="toolbar">
                <div class="btn-group col-md-12 col-sm-12 col-xs-12 text-right">
                    <form class=" " id="delete_form" name="delete_form"  method="post" action="<?php echo base_url(); ?>recyclebin/rollback">
                        <input type="hidden" name="action" value="rollback" />
                        <input type="hidden" name="activity_id" value="<?php echo $data['activity_id']; ?>" />
                        <input type="button" name="rollback" id="rollback" value="Rollback" class="btn btn-warning" onclick="doSubmitDelete();"/>
                    </form>
                </div>
            </div>       

        </div>
    </div>
</div>
<script>

    function doSubmitDelete()
    {

        var modal_body = '<h1 class="text-center"><i class="fa fa-exclamation-circle"></i></h1>' +
                '<h4 class="text-center">Are you sure yoy want to<br> rollback this?</h4>';

        var modal_footer = '<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>' +
                '<button type="button" class="btn btn-danger" id="modal-btn-yes-rollback">Confirm</button>';


        openModal('sm', '', modal_body, modal_footer);
        $("#my-modal").modal('show');
        $("#modal-btn-yes-rollback").on("click", function () {
            //alert("single");		



            //alert("yes");
            $("#my-modal").modal('hide');
            $("#delete_form").submit();




        });
    }
</script>              