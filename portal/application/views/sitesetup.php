<!-- Parsley -->
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>

<div class="">
    <!--<div class="page-title">
        <div class="title_left">
        <h3>Form Elements</h3>
        </div>
    </div>-->
    <div class="clearfix"></div>

    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Site Setup</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        
                        <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />

                    <form action="<?php echo base_url(); ?>sitesetup" method="post" name="setup_form" id="setup_form" data-parsley-validate  class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="users_id" value="<?php echo $data->user_id; ?>"/>

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Site Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="data[site_name]" id="site_name" data-parsley-required="" value="<?php echo $data['site_name']; ?>" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Mail Sent From <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="data[mail_sent_from]" id="mail_sent_from" class="form-control col-md-7 col-xs-12" value="<?php echo $data['mail_sent_from']; ?>" data-parsley-required="" data-parsley-type="email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Mail Sent To</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="data[mail_sent_to]" id="mail_sent_to" class="form-control col-md-7 col-xs-12" value="<?php echo $data['mail_sent_to']; ?>" >
                            </div>
                        </div>


                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <a href="<?php echo base_url() ?>dashboard"><button class="btn btn-primary" type="button">Cancel</button></a>				
                                <button type="button" id="btnSave" class="btn btn-success">Save</button>
                                <button type="button" id="btnSaveClose" class="btn btn-info">Save & Close</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>    
<script>
    $('#btnSave, #btnSaveClose').click(function () {

        var is_ok = $("#setup_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#setup_form").submit();
        } else
        {
            $('#setup_form').parsley().validate();
        }



    })
</script>

