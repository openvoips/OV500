<div class="container-fluid">    
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

            <h4>Page Start</h4>
            <?php
            $str = '<div class="container-fluid">
    <div class="block-header">
        <h2>User Management</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url() ?>users/addA"><input type="button" value="Add User" name="add_link" class="btn btn-primary"></a></li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">
                    <h2> Add User</h2>';

            //echo $str;
            echo '<pre>' . htmlentities($str) . '</pre>';
            ?> 

            <h4>Form Part</h4>
            <?php
            $str = '
<form action="<?php echo base_url(); ?>;users/addA" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">User Type <span class="required">*</span>
        </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select name="user_type" id="user_type" class="form-control" data-parsley-required="">
                <option value="">Select</option>                   
                
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Name <span class="required">*</span>
        </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="user_fullname" id="user_fullname" value=""  data-parsley-required="" data-parsley-minlength="3" class="form-control ">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Email Address <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="user_emailaddress" id="user_emailaddress" value=""  data-parsley-required="" data-parsley-type="email" class="form-control ">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Address </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <textarea name="user_address" id="user_address" class="form-control "></textarea>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Phone Number </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="user_phone" id="user_phone" value="" data-parsley-type="digits" data-parsley-minlength="10" data-parsley-maxlength="12" class="form-control ">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label  col-md-3 col-sm-3 col-xs-12" for="first-name">Country 
        </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select name="user_country_id" id="user_country_id" class="form-control">
                <option value="">Select</option>                    
                
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
        <div class="col-md-9 col-sm-6 col-xs-12">            
                <input type="radio" name="status_id" id="status1" class="with-gap" value="1"   /> 
                <label for="status1">Active</label>
                                            
                <input type="radio" name="status_id" id="status0" class="with-gap" value="0"  /> 
                <label for="status0">Inactive</label>
        </div>
    </div>
    <div class="ln_solid"></div>
    <div class="form-group">
        <div class="col-xs-12 text-center">
            <a href="<?php echo base_url() ?>users"><button class="btn btn-warning btn-lg" type="button">Cancel</button></a>			
            <button type="button" id="btnSave" class="btn btn-success btn-lg">Save</button>
            <button type="button" id="btnSaveClose" class="btn btn-info btn-lg">Save & Go back to Listing Page</button>
        </div>
    </div>
</form>';
            echo '<pre>' . htmlentities($str) . '</pre>';
            ?>
            <h4>Page End</h4>
            <?php
            $str = '             </div>
            </div>
        </div>
    </div>    
</div>';
            echo '<pre>' . htmlentities($str) . '</pre>';
            ?>
            <h4>Script files</h4>
            <?php
            $str = '
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>';
            echo '<pre>' . htmlentities($str) . '</pre>';
            ?>

        </div>
    </div>    
</div>