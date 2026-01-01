<script src="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/js/bootstrap-combobox.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<script>
    $(document).ready(function () {
        $('.combobox').combobox()
    });
</script>





<div class="container-fluid">
    <div class="block-header">
        <h2>Currency Exchange Rate</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url() ?>currency/ExcRate"><input type="button" value="Add Exchange Rate" name="add_link" class="btn btn-primary"></a></li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">   




                    <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>currency/exc/">
                        <input type="hidden" name="search_action" value="search" />

                        <div class="form-group">

                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Currency exchange rate looking for </label>
                            <div class="col-md-3 col-sm-9 col-xs-12">
                                <select name="currency" id="currency" class="form-control data-search-field combobox">
                                    <option value="">Select Currency</option>
                                    <?php
                                    for ($i = 0; $i < $currency_dropdown['total']; $i++) {
                                        ?>
                                        <option value="<?php echo $currency_dropdown['result'][$i]['currency_id']; ?>" <?php if ($_SESSION['search_currency_data']['s_currency_id'] == $currency_dropdown['result'][$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_dropdown['result'][$i]['symbol'] . " - " . $currency_dropdown['result'][$i]['name']; ?></option>
                                    <?php } ?>                         
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="searchBar col-md-8 col-sm-12 col-xs-12 text-right">
                                <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                                <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">                           
                                <div class="btn-group">
                                    <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                        Export <span class="caret"></span></button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('csv'); ?>">CSV</a></li>
                                        <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('xlsx'); ?>">XLSX</a></li>
                                        <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('xls'); ?>">XLS</a></li>
                                        <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('txt'); ?>">TXT</a></li>
                                        <li><a href="<?php echo base_url() ?>rates/index/export/<?php echo param_encrypt('pdf'); ?>">PDF</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </form> 

                    <div class="clearfix"></div>
                    <div class="ln_solid"></div>
                    <div class="body">
                        <?php if ($searching): ?>


                            <div class="row">  
                                <?php dispay_pagination_row_bsd($total_records, $_SESSION[$search_session_key]['no_of_records'], $pagination); ?>
                            </div> 
                            <div class="table-responsive">
                                <table class="table table-striped jambo_table bulk_action table-bordered">
                                    <thead>

                                        <tr class="headings thc">                                              
                                            <th class="column-title">Currency</th>                               
                                            <th class="column-title">Exchange Rate</th>
                                            <th class="column-title">Date</th>
                                            <th class="column-title">note</th>

                                        </tr>
                                    </thead>		
                                    <tbody>
                                        <?php
                                        if ($listing_count > 0) {
                                            foreach ($listing_data as $listing_row) {
                                                ?>

                                                <tr>                                        
                                                    <td width="300"><?php echo $listing_row['currency_symbol'] . " - " . $listing_row['detail_name'] . " (" . $listing_row['currency_name'] . ")"; ?></td> 
                                                    <td width="100"><?php echo $listing_row['ratio']; ?></td> 
                                                    <td width="300"><?php echo $listing_row['date']; ?></td> 

                                                    <td><?php echo "1 USD ($) to " . $listing_row['currency_symbol'] . " - " . $listing_row['detail_name'] . " (" . $listing_row['currency_name'] . ") exchange rate was <b>" . $listing_row['ratio'] . "</b>  on date " . $listing_row['date']; ?></td> 
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="3" align="center"><strong>No Record Found</strong></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>                    
                            <div class="row">  
                                <?php dispay_pagination_row_bsd($total_records, $_SESSION[$search_session_key]['no_of_records'], $pagination); ?>
                            </div> 
                        <?php endif; ?>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>