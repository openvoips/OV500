<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$tab_index = 1;
$status_array = get_t_status();
?>
<link href="<?php echo base_url() ?>theme/default/css/ticket.css" rel="stylesheet"> 
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Support Tickets</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">

            <div class="col-md-12 col-sm-9 col-xs-12">

                <ul class="messages">

                    <li>

                        <div class="col-md-9">
                            <div class="message_wrapper">
                                <h4 class="heading"><?php echo $ticket_data['subject']; ?></h4>
                                <blockquote class="message"><?php echo $ticket_data['content']; ?></blockquote>              
                                <br />
                                <?php
                                if (isset($ticket_data['attachment']) && count($ticket_data['attachment']) > 0) {
                                    foreach ($ticket_data['attachment'] as $attachment_data) {
                                        $file_name = $attachment_data['file_name'];
                                        $file_name_display = $attachment_data['file_name_display'];

                                        $download_path = 'uploads/ticket/' . $ticket_data['account_id'];
                                        $file_path = $download_path . '/' . $file_name;
                                        if (file_exists($file_path)) {
                                            $download_link = base_url('download/ticket/' . param_encrypt($ticket_data['account_id']) . '/' . param_encrypt($file_name));
                                            echo '<p class="url"><span class="fs1 text-info" aria-hidden="true" data-icon=""></span>
                            <a href="' . $download_link . '"><i class="fa fa-paperclip"></i> ' . $file_name_display . ' </a></p>';
                                        }
                                    }
                                }
                                ?>    
                            </div>
                        </div>
                        <div class="clearfix"></div>

                    </li>
                </ul>


                <ul class="stats-overview">
                    <li>
                        <span class="name">Category:<i>  # <strong><?php echo $ticket_data['category']['category_name']; ?></strong></i> </span>            
                    </li>
                    <li>
                        <span class="name"> <i class="fa fa-clock-o"></i> <?php echo date(DATE_FORMAT_2, strtotime($ticket_data['create_date'])); ?> </span>
                    </li>
                    <li>
                        <span class="name"> Status: 
                            <?php if ($ticket_data['status'] != 'closed') { ?>
                                <button type="button" class="btn btn-success btn-xs">Open</button>
                            <?php } else { ?> 
                                <button type="button" class="btn btn-danger btn-xs">Closed</button>
                            <?php } ?>
                        </span>
                        <?php if ($ticket_data['status'] != 'closed') { ?>
                            <span class="edit_texta" onclick="inline_update(3)">edit</span>
                        <?php } ?>
                    </li>
                </ul>
                <div class="clearfix"></div>



                <div class=" hide inline_update" id="inline_update3">

                    <div class="col-md-2"> Status </div>
                    <div class="col-md-3">
                        <form action="" method="post" name="status_form" id="status_form" data-parsley-validate class="form-horizontal form-label-left">
                            <input type="hidden" name="action" value="OkSaveStatus"> 
                            <select name="status" id="status" class="form-control " data-parsley-required="">
                                <option value="">Select</option>                             
                                <option value="closed">Closed</option>                
                            </select>
                        </form>

                    </div>
                    <div class="col-md-4"> <button type="button" id="status_btnSave" class="btn btn-primary" tabindex="<?php echo $tab_index++; ?>">Save</button></div>
                    <div class="clearfix"></div>
                </div>   
                <br>

                <div class="clearfix"></div>
                <div>

                    <h4><strong>Replies</strong></h4>


                    <ul class="messages">

                        <?php
                        if (count($ticket_data['replies']) > 0) {
                            $k = 0;
                            foreach ($ticket_data['replies'] as $reply_data) {
                                if ($reply_data['created_by'] == $ticket_data['account_id'])
                                    $created_by = 'Me';
                                else
                                    $created_by = 'ADMIN';

                                if ($k++ % 2 == 0)
                                    $li_class = 'bg1';
                                else
                                    $li_class = '';
                                ?>
                                <li class="<?php echo $li_class; ?>">
                                    <div class="col-md-2">
                                        <strong><?php echo $created_by; ?></strong><br />
                                        <i class="fa fa-clock-o"></i> <?php echo date(DATE_FORMAT_2, strtotime($reply_data['create_date'])); ?>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="message_wrapper">
                                            <h4 class="heading"><?php echo $reply_data['subject']; ?></h4>
                                            <blockquote class="message"><?php echo $reply_data['content']; ?></blockquote>
                                            <br>

                                            <?php
                                            if (isset($reply_data['attachment']) && count($reply_data['attachment']) > 0) {
                                                foreach ($reply_data['attachment'] as $attachment_data) {
                                                    $file_name = $attachment_data['file_name'];
                                                    $file_name_display = $attachment_data['file_name_display'];

                                                    $download_path = 'uploads/ticket/' . $ticket_data['account_id'];
                                                    $file_path = $download_path . '/' . $file_name;
                                                    if (file_exists($file_path)) {
                                                        $download_link = base_url('download/ticket/' . param_encrypt($ticket_data['account_id']) . '/' . param_encrypt($file_name));
                                                        echo '<p class="url"><span class="fs1 text-info" aria-hidden="true" data-icon=""></span>
								<a href="' . $download_link . '"><i class="fa fa-paperclip"></i> ' . $file_name_display . ' </a></p>';
                                                    }
                                                }
                                            }
                                            ?>


                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                </li>
                                <?php
                            }
                        }
                        ?>	





                    </ul>
                    <!-- end of user messages -->


                </div>


            </div>















        </div>

        <?php if ($ticket_data['status'] != 'closed'): ?>


            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12"> 
                    <div class="col-md-8 col-sm-6 col-xs-12"><h2>Reply</h2></div>
                    <div class="col-md-4 col-sm-6 col-xs-12"><h2>Upload Files</h2> </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12"> 

                    <form action="" method="post" name="ticket_form" id="ticket_form" data-parsley-validate class="form-horizontal form-label-left" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="OkSaveData"> 
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="ticket_account_id" id="ticket_account_id" value="<?php echo $ticket_data['account_id']; ?>">
                        <?php
                        //changes777
                        if ($ticket_data['author_email_subscribe'] == 'Y' && $ticket_data['author_email'] != '') {
                            echo '<input type="hidden" name="author_email" id="author_email" value="' . $ticket_data['author_email'] . '">';
                        }
                        ?>
                        <?php $default_subject = 'Re: ' . $ticket_data['subject']; ?>					 

                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12"> 
                                <div class="form-group">
                                    <label class="control-label col-md-2 col-sm-3 col-xs-12">Subject <span class="required">*</span></label>
                                    <div class="col-md-10 col-sm-6 col-xs-12"> 
                                        <input type="text" name="subject" id="subject" value="<?php echo set_value('subject', $default_subject); ?>" class="form-control" data-parsley-required="" data-parsley-minlength="4" tabindex="<?php echo $tab_index++; ?>">
                                    </div>  
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <textarea name="content" id="content" tabindex="<?php echo $tab_index++; ?>"><?php echo set_value('content'); ?></textarea>
                                    </div>
                                </div>               
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12"> 
                                <ul class="messages">
                                    <li><input type="file" name="file_upload1" id="file_upload1" value="" /></li>
                                    <li><input type="file" name="file_upload2" id="file_upload2" value=""/></li>
                                    <li><input type="file" name="file_upload3" id="file_upload3" value=""/></li>
                                </ul>    
                            </div>
                        </div> 



                        <div class="form-group">
                            <div class="col-md-8 col-sm-6 col-xs-12"> 
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12 text-right">
                                <a href="<?php echo base_url() ?>ticket"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Cancel</button></a>
                                <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save</button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-12 col-sm-6 col-xs-12 text-right">
                <a href="<?php echo base_url() ?>ticket"><button class="btn btn-primary" type="button">Back</button></a>
            </div>
        <?php endif; ?>

    </div>      
</div> 

<script>
    function inline_update(id_number)
    {
        div_id = 'inline_update' + id_number;

        if ($('#' + div_id).hasClass('hide'))
        {
            $('.inline_update').addClass('hide');
            $('#' + div_id).removeClass('hide');
        } else
        {
            $('.inline_update').addClass('hide');

        }

        //alert(id_number);

    }


    $('#btnSave, #btnSaveClose').click(function () {
        //alert("SS");
        $('#ticket_form').parsley().reset();

        var is_ok = $("#ticket_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            if (is_ok === true)
            {
                //alert("ok");
                $("#ticket_form").submit();
            }
        } else
        {
            $('#ticket_form').parsley().validate();
        }

    });


    $('#status_btnSave').click(function () {
        $('#status_form').parsley().reset();

        var is_ok = $("#status_form").parsley().isValid();
        if (is_ok === true)
        {
            if (is_ok === true)
            {
                $("#status_form").submit();
            }
        } else
        {
            $('#status_form').parsley().validate();
        }
    });
</script>
<script src="<?php echo base_url() ?>theme/vendors/tinymce/js/tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: 'textarea#content',
        branding: false,
        height: 300,
        menubar: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor textcolor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount',
            'spellchecker'
        ],
        toolbar: 'undo redo |  bold italic | alignleft alignjustify | bullist numlist | removeformat link  forecolor',
    });
</script>