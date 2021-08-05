<?php
if (isset($user_result)) {
    $is_user_details_exists = true;
    $dp = 4;
    if (in_array(strtolower($user_result['user_type']), array('user', 'reseller')) && $user_result['dp'] != '')
        $dp = $user_result['dp'];
}
?>
<div class="">
    <div class="clearfix"></div>   
    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="clearfix"></div>
        <div class="x_content">

            <div class="row">

                <?php
                if (check_logged_user_group(array('RESELLER', 'CUSTOMER'))) {
                    ?>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Balance detail</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    </li>
                                </ul>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content">
                                <div class="table-responsive">
                                    <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort">  
                                        <tr >
                                            <th>Balance</th>
                                            <th class="text-right"><?php echo number_format(-$user_result['balance']['balance'], $dp, '.', '') . ' ' . $user_result['currency']['name']; ?> </th>
                                        </tr>						
                                        <tr >
                                            <th>Temporary Credit </th>
                                            <th class="text-right"><?php echo number_format($user_result['balance']['credit_limit'], $dp, '.', '') . ' ' . $user_result['currency']['name']; ?> </th>
                                        </tr>
                                        <tr >
                                            <th>Available Balance</th>
                                            <th class="text-right"><?php echo number_format($user_result['balance']['usable_balance'], $dp, '.', '') . ' ' . $user_result['currency']['name']; ?> </th>
                                        </tr>

                                    </table>  

                                </div>
                            </div>
                        </div>   
                    </div>    

                    <div class="clearfix"></div>
                    <?php
                } elseif (check_logged_user_type(array('ADMIN', 'SUBADMIN'))) {
                    ?>
                    <div class="animated flipInY  col-md-12 col-sm-12 col-xs-12">  
                        <div class="text-center"><h1>Welcome To <?php echo SITE_FULL_NAME; ?>. </h1>
                        </div>
                        <div class="text-center">
                            <p><h3>Live System Monitoring & Traffic Statistics</h3></p> 
                            <a class="title" href="<?php echo base_url('reports/monin'); ?>"><button type="button" class="btn btn-primary btn-lg active"><i class="fa fa-hand-o-right"></i> Monitor System</button></a>
                        </div> 
                    </div>
                    <?php
                }
                ?>      

            </div>              
            <div class="clearfix"></div>
        </div>



    </div>
    <div class="clearfix"></div>
</div>      