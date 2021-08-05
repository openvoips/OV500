
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Providers</h2>
            <ul class="nav navbar-right panel_toolbox">                
                <?php if (check_account_permission('provider', 'add')) { ?>
                    <li><a href="<?php echo base_url() ?>providers/add"><input type="button" value="Add Provider" name="add_link" class="btn btn-primary"></a></li>
                <?php } ?>  
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>providers/index/">
                <input type="hidden" name="search_action" value="search" />
                <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Provider Code</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="provider_id" id="provider_id" value="<?php echo $_SESSION['search_provider_data']['s_provider_id']; ?>" class="form-control data-search-field" placeholder="Provider Code">
                    </div> 
                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Provider Name</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <input type="text" name="provider_name" id="provider_name" value="<?php echo $_SESSION['search_provider_data']['s_provider_name']; ?>" class="form-control data-search-field" placeholder="Provider Name">
                    </div>            

                </div>
                <div class="form-group">

                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Currency</label>
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <select name="currency_id" id="currency_id" class="form-control data-search-field" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                            <option value="" >Select</option>                    
                            <?php
                            $str = '';
                            foreach ($currency_options as $key => $currency_array) {
                                $selected = ' ';
                                if ($_SESSION['search_provider_data']['s_currency_id'] == $currency_array['currency_id'])
                                    $selected = '  selected="selected" ';
                                $str .= '<option value="' . $currency_array['currency_id'] . '" ' . $selected . '>' . $currency_array['symbol'] . " - " . $currency_array['name'] . '</option>';
                            }
                            echo $str;
                            ?>
                        </select>
                    </div>            


                    <div class="searchBar text-right">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary ">                          
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info ">
                        <div class="btn-group">
                            <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $export_format_array = get_export_formats();
                                foreach ($export_format_array as $export_format) {
                                    if ($export_format == 'pdf')
                                        continue;
                                    echo '<li><a href="' . base_url() . 'providers/index/export/' . param_encrypt($export_format) . '">' . strtoupper($export_format) . '</a></li>';
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
            dispay_pagination_row($total_records, $_SESSION['search_provider_data']['s_no_of_records'], $pagination);
            ?>    
        </div>
        <div class="table-responsive">
            <table class="table table-striped jambo_table bulk_action table-bordered" id="table-sort1">
                <thead>
                    <tr class="headings thc">
                        <th class="column-title text-center">Provider</th>
                        <th class="column-title text-center">Currency</th>                        
                        <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>

                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (count($providers_data['result']) > 0) {
                        foreach ($providers_data['result'] as $provider_data) {
                            ?>
                            <tr>
                                <td class="text-left"><?php echo $provider_data['provider_name'] . " (" . $provider_data['provider_id'] . ")"; ?></td>

                                <td class="text-left"><?php echo $provider_data['currency_symbol'] . " - " . $provider_data['currency_name']; ?></td>

                                <td class=" last">

                                    <a href="<?php echo base_url(); ?>providers/edit/<?php echo param_encrypt($provider_data['provider_id']); ?>" title="Edit"><i class="fa fa-pencil-square-o"></i></a>

                                    <a href="javascript:void(0);" onclick=doConfirmDelete('<?php echo $provider_data['provider_id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>


                                </td>
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
        <?php
        dispay_pagination_row_bottom($total_records, $_SESSION['search_provider_data']['s_no_of_records'], $pagination);
        ?>   
    </div>
</div>
<script>
    $(document).ready(function () {
		 showDatatable('table-sort1', [2], [1, "asc"]);
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
        });
    });
    $(document).ready(function () {
        //showDatatable('table-sort', [4], [2, "desc"]);
      //  showDatatable('table-sort1', [2], [1, "asc"]);
    });
</script>