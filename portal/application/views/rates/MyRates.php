<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>PSTN Call & DID Call Rates</h2>
            <ul class="nav navbar-right panel_toolbox">

            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-4 col-xs-12">Prefix</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="prefix" id="prefix" value="<?php echo $_SESSION['search_myrate']['s_myrate_prefix']; ?>" class="form-control data-search-field" placeholder="Prefix">
                    </div>
                    <label class="control-label col-md-2 col-sm-4 col-xs-12">Destination</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="dest" id="dest" value="<?php echo $_SESSION['search_myrate']['s_myrate_dest']; ?>" class="form-control data-search-field" placeholder="Destination">
                    </div>	


                </div>
                <div class="form-group">
                    <label for="middle-name" class="control-label col-md-2 col-sm-3 col-xs-12">Rate Type</label>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <select name="ratecard_for" id="ratecard_for" class="form-control data-search-field">
                            <option value="ALL">ALL</option>
                            <option value="OUTGOING" <?php if ($_SESSION['search_myrate']['s_myrate_ratecard_for'] == 'OUTGOING') echo 'selected'; ?>>Outgoing Rates</option>
                            <option value="INCOMING" <?php if ($_SESSION['search_myrate']['s_myrate_ratecard_for'] == 'INCOMING') echo 'selected'; ?>>Incoming Rates</option>
                        </select>
                    </div>                
                    <div class="searchBar">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">           
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">
                        <a href="javascript:void(0);"  onclick=doOpenModal() title="Delete"><input type="button" value="Download Rate Card" name="add_link" class="btn btn-primary"></a>
                    </div>
                </div>
                <div class="col-md-12 col-sm-9 col-xs-12 text-left hide" id="id_error_div">
                    <ul class="parsley-errors-list filled" id="parsley-id-5"><li class="parsley-required">Please input any one of the search parameters</li></ul>	
                </div>
            </form> 
            <div class="clearfix"></div>
            <div class="ln_solid"></div>
            <?php if ($searching): ?>
                 <div class="row">  
                <?php dispay_pagination_row($total_records, $_SESSION['search_myrate']['no_of_rows'], $pagination); ?>
            </div>
                <div class="table-responsive">
                    <table class="table table-striped jambo_table  table-bordered">
                        <thead>
                            <tr class="headings thc">
                                <th class="column-title">Dial Prefix</th>
                                <th class="column-title">Destination</th>
                                <th class="column-title">Rate per Minute</th>
                                <th class="column-title">Charge per Connection</th>      

                                <th class="column-title">Fix Charge per call</th>
                                <th class="column-title">Grace Period</th>
                                <th class="column-title">Billing Pulse slab</th>					
                                <?php if (!check_logged_user_group(array('CUSTOMER', 'RESELLER'))): ?>
                                    <th class="column-title">Rate Multiplier</th>                                   
                                <?php endif; ?>

                                <th class="column-title">Status & Type </th>
                            </tr>
                        </thead>		
                        <tbody>
                            <?php
                            if ($listing_count > 0) {
                                foreach ($listing_data as $listing_row) {
                                    if ($listing_row['rates_status'] == '1')
                                        $status = '<span class="label label-success">Active</span>';
                                    else
                                        $status = '<span class="label label-danger">Inactive</span>';

                                    if ($listing_row['ratecard_for'] == 'OUTGOING')
                                        $rate_type = '<span class="label label-info">PSTN</span>';
                                    else
                                        $rate_type = '<span class="label label-warning">DID</span>';
                                    ?>
                                    <tr>
                                        <td ><?php echo $listing_row['prefix']; ?></td>
                                        <td><?php echo $listing_row['destination']; ?></td>
                                        <td ><?php echo number_format($listing_row['rate'], 6, '.', ''); ?></td>
                                        <td ><?php echo number_format($listing_row['connection_charge'], 6, '.', ''); ?></td>
                                        <td class="column-title"><?php echo $listing_row['rate_addition']; ?></td>
                                        <td class="column-title"><?php echo $listing_row['grace_period']; ?></td>
                                        <td class="column-title"><?php echo $listing_row['minimal_time'] . "/" . $listing_row['resolution_time']; ?></td> 
                                        <?php if (!check_logged_user_group(array('CUSTOMER', 'RESELLER'))): ?>
                                            <td class="column-title"><?php echo $listing_row['rate_multiplier']; ?> </td>
                                        <?php endif; ?>
                                        <td ><?php echo $rate_type . " " . $status; ?></td>                                   

                                    </tr>	
                                    <?php
                                }
                            }
                            else {
                                ?>
                                <tr>
                                    <td colspan="20" align="center"><strong>No Record Found</strong></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>                    
                <?php echo '<div class="btn-toolbar" role="toolbar"> <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">' . $pagination . '</div></div>'; ?>
            <?php endif; ?>
        </div>
    </div>
</div> 

<script>
    $('#OkFilter').click(function (event) {
        if ($('#prefix').val() == '' && $('#dest').val() == '' && $('#ratecard_for').val() == '')
        {
            $('#id_error_div').removeClass('hide');
            event.preventDefault();
        }
    });
    function doOpenModal()
    {
        var modal_body = '<h4 class="text-center">Select Ratecard Type</h4><br /> This action will provide all rates which are available in the your Tariff Plan for selected Rate type.';
        var modal_footer = '<button type="button" class="btn btn-primary"  id="modal-btn-in">Incoming Rates</button>' +
                '<button type="button" class="btn btn-success" id="modal-btn-out">Outgoing Rates</button>';
        openModal('', '', modal_body, modal_footer);
        $("#my-modal").modal('show');
        $("#modal-btn-in").on("click", function () {
            $("#my-modal").modal('hide');
            window.location.href = BASE_URL + 'rates/download/in';
        });
        $("#modal-btn-out").on("click", function () {
            $("#my-modal").modal('hide');
            window.location.href = BASE_URL + 'rates/download';
        });
    }
</script>