<div class="container-fluid">    
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

            <h4>Page Start</h4>
            <?php
            $str = '
<div class="container-fluid">
    <div class="block-header">
        <h2>System Users Management</h2>
        <ul class="nav navbar-right panel_toolbox">
        <?php if (check_account_permission(\'user\', \'add\')): ?> <li><a href="<?php echo base_url() ?>users/addA"><input type="button" value="Add User" name="add_link" class="btn btn-warning"></a></li><?php endif; ?>
        </ul>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">';

            echo '<pre>' . htmlentities($str) . '</pre>';
            ?> 

            <h4>Search Form</h4>
            <?php
            $str = '
<div class="header">
    <h2>System User </h2>

    <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo site_url(\'users/index\'); ?>">
        <input type="hidden" name="search_action" value="search" />
        <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-3 col-xs-12">Name</label>
            <div class="col-md-4 col-sm-8 col-xs-12">
                <input type="text" name="name" id="name" value="<?php echo $_SESSION[$search_session_key][\'name\']; ?>" class="form-control data-search-field" placeholder="Name">
            </div>
            
            <label class="control-label col-md-2 col-sm-3 col-xs-12">User ID</label>
            <div class="col-md-4 col-sm-8 col-xs-12">
                <input type="text"  name="user_id" id="user_id"  value="<?php echo $_SESSION[$search_session_key][\'user_id\']; ?>" class="form-control data-search-field" placeholder="USER ID">
            </div>
        </div>
        
        <div class="form-group">

            <label class="control-label col-md-2 col-sm-3 col-xs-12">User Type</label>
            <div class="col-md-4 col-sm-8 col-xs-12">
                <select name="user_type" id="user_type" class="form-control data-search-field">
                    <option value="">Select</option>
                    <?php
                    foreach ($user_type_array as $type => $type_name) {
                        if ($_SESSION[$search_session_key][\'user_type\'] == $type)
                            $selected = \' selected="selected" \';
                        else
                            $selected = \'\';
                        echo \'<option value="\' . $type . \'" \' . $selected . \'>\' . $type_name . \'</option>\';
                    }
                    ?>
                </select>
            </div>
            <div class="searchBar text-right">                         
                <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">                          
                <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">   
                <div class="btn-group ">
                    <button type="button" class="btn bg-blue-grey  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                        Export <span class="caret"></span></button>
                    <ul class="dropdown-menu" role="menu">
                        <?php
                        $export_format_array = get_export_formats();
                        foreach ($export_format_array as $export_format) {
                            echo \'<li><a href="\' . base_url() . \'users/index/export/\' . param_encrypt($export_format) . \'">\' . strtoupper($export_format) . \'</a></li>\';
                        }
                        ?>                            
                    </ul>
                </div>
            </div>
        </div>
    </form>                            
</div>';

            echo '<pre>' . htmlentities($str) . '</pre>';
            ?> 
            <h4>Data Listing</h4>
            <?php
            $str = '
<div class="body">
    <div class="row">  
        <?php   dispay_pagination_row_bsd($total_records, $_SESSION[$search_session_key][\'no_of_rows\'], $pagination);            ?>   
    </div>
    <div class="table-responsive">

        <table class="table table-bordered">
            <thead>
            <tr class="headings thc">
                <th class="column-title">Name</th> 
                <th class="column-title">ID</th>
                <th class="column-title">User Type</th>
                <th class="column-title">Status</th>
                <th class="column-title no-link last" ><span class="nobr">Actions</span> </th>                   
            </tr>
            </thead>
            <tbody>
                <tr>                                   
                    <td>Admin</td>
                    <td>ADMIN</td>
                    <td>Admin</td>
                    <td><span class="label label-success">Active</span></td>                                   
                    <td class=" last">
                    <a href="http://portal.openvoips.org/users/editA/QURNSU4_EQUALS_" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>                        
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>';

            echo '<pre>' . htmlentities($str) . '</pre>';
            ?> 

            <h4>Page End</h4>
            <?php
            $str = '
            </div>
        </div>
    </div>
</div>
';

            echo '<pre>' . htmlentities($str) . '</pre>';
            ?> 




        </div>
    </div>    
</div>