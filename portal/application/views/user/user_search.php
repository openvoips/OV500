
<div class="col-md-8 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Search User</h2>
            <ul class="nav navbar-right panel_toolbox">

            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">

            <script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
            <form action="" method="post" name="search_form" id="search_form" data-parsley-validate class="form-horizontal form-label-left">
                <input type="hidden" name="action" value="OkSearchData"> 
                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" for="Account ID">Account Code</label>
                    <div class="col-md-7 col-sm-6 col-xs-12">
                        <input type="text" name="search_account_id" id="search_account_id" value="<?php echo $_SESSION['search_user_search']['s_account_id']; ?>"  class="form-control col-md-7 col-xs-12" data-parsley-required="">
                    </div>
                </div>             
                <div class="form-group">
                    <div class="col-md-12 col-sm-6 col-xs-12 col-md-offset-4">			
                        <button type="button" id="btnSearch" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>

        </div>

    </div>    
</div>

<?php if ($is_id_exists): ?>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>User Details</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <table class="table jambo_table table-bordered ">
                    <tbody>
                        <?php if (isset($user_result)): ?>

                            <tr><td><strong>Account ID</strong></td><td><?php echo $user_result['user_access_id_name']; ?> </td></tr>
                            <tr><td><strong>Name</strong></td><td><?php echo $user_result['name']; ?></td></tr>
                            <tr><td><strong>User Type</strong></td><td><?php echo $user_result['user_type']; ?></td></tr>
                            <tr><td><strong>User Level</strong></td><td><?php echo $user_result['user_level']; ?></td></tr>	  
                            <?php if ($user_result['user_access_id_name'] != '') {
                                ?>
                                <tr>
                                    <td colspan="2"><a href="<?php echo base_url(); ?>autologin/<?php echo param_encrypt($user_result['user_access_id_name']); ?>"><button type="button" class="btn btn-success btn-lg btn-block" data-dismiss="modal" id="modal-btn-self">Login As User</button></a></td>
                                </tr>
                            <?php } ?>

                        <?php else: ?>
                            <tr><td align="center"><strong>No Data Found</strong></td></tr>
                        <?php endif; ?> 
                    </tbody>
                </table>

            </div>        
        </div>    
    </div>

    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Parent Details</h2>
                <ul class="nav navbar-right panel_toolbox">

                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <table class="table jambo_table table-bordered">
                    <tbody>
                        <?php if (isset($parent_result)): ?>
                            <tr><td><strong>Account ID</strong></td><td><?php echo $parent_result['user_access_id_name']; ?> </td></tr>
                            <tr><td><strong>Name</strong></td><td><?php echo $parent_result['name']; ?></td></tr>
                            <tr><td><strong>User Type</strong></td><td><?php echo $parent_result['user_type']; ?></td></tr>    
                            <?php if ($user_result['user_access_id_name'] != '') {
                                ?>
                                <tr>
                                    <td colspan="2"><a href="<?php echo base_url(); ?>autologin/<?php echo param_encrypt($user_result['user_access_id_name']); ?>/parent"><button type="button" class="btn btn-warning btn-lg btn-block" data-dismiss="modal" id="modal-btn-parent">Login As Parent</button></a></td>
                                </tr>  
                            <?php } ?>  
                        <?php else: ?>
                            <tr><td align="center"><strong>No Data Found</strong></td></tr>
                        <?php endif; ?> 
                    </tbody>
                </table>

            </div>        
        </div>    
    </div>

<?php endif; ?>

<script>
    /*search form*/
    $('#btnSearch').click(function () {
        $('#search_form').parsley().reset();
        var is_ok = $("#search_form").parsley().isValid();
        if (is_ok === true)
        {
            if (is_ok === true)
            {
                $("#search_form").submit();
            }
        } else
        {
            $('#search_form').parsley().validate();
        }

    })
</script>              
