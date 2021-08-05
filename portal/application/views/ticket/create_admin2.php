<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<?php
$tab_index = 1;
$subject_auto_fill_array = get_t_subjects();
?>
<div class="">
    <div class="clearfix"></div>     
    <div class="col-md-12 col-sm-12 col-xs-12"> 

        <div class="x_panel">

            <div class="x_title">
                <h2>Create Ticket</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form action="<?php echo base_url(); ?>ticket/create" method="post" name="ticket_form" id="ticket_form" data-parsley-validate class="form-horizontal form-label-left" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="OkSaveData"> 
                    <input type="hidden" name="button_action" id="button_action" value="">

                    <div class="col-md-9 col-sm-12 col-xs-12">  
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Account ID <span class="required">*</span></label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                	
                                <input type="text" name="account_id" id="account_id" value="<?php echo set_value('account_id', $_SESSION['search_t_data']['s_account_id']); ?>" class="form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">       
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Subject <span class="required">*</span></label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                	
                                <input type="text" name="subject" id="subject" value="<?php echo set_value('subject'); ?>" class="form-control" data-parsley-required="" data-parsley-minlength="4" tabindex="<?php echo $tab_index++; ?>">  

                                <br />
                                <select name="subject_select" id="subject_select" class="form-control" tabindex="<?php echo $tab_index++; ?>" onchange="subject_selected()">
                                    <option value="">Select Subject</option> 
                                    <?php
                                    $str = '';
                                    if (isset($subject_auto_fill_array) && count($subject_auto_fill_array) > 0) {
                                        foreach ($subject_auto_fill_array as $key => $subject_auto_fill) {

                                            $selected = ' ';
                                            $str .= '<option value="' . $key . '" ' . $selected . '>' . $subject_auto_fill . '</option>';
                                        }
                                    }
                                    echo $str;
                                    ?>   						

                                </select>     
                            </div>
                        </div> 

                        <div class="form-group">
                            <label class="col-md-2 col-sm-3 col-xs-12"> </label>
                            <div class="col-md-10 col-sm-6 col-xs-12"> 

                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-md-2 col-sm-3 col-xs-12">Category <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-6 col-xs-12"> 
                                <select name="category_id" id="category_id" class="form-control" data-parsley-required="" tabindex="<?php echo $tab_index++; ?>">
                                    <option value="">Select</option>   
                                    <?php
                                    $str = '';
                                    if (isset($category_data['result']) && count($category_data['result']) > 0) {
                                        foreach ($category_data['result'] as $key => $parent_category_array) {
                                            $str .= '<optgroup label="' . $parent_category_array['category_name'] . '">';
                                            if (isset($parent_category_array['sub']) && count($parent_category_array['sub']) > 0) {
                                                foreach ($parent_category_array['sub'] as $key => $category_array) {
                                                    $selected = ' ';
                                                    if (set_value('category_id') == $category_array['category_id'])
                                                        $selected = '  selected="selected" ';
                                                    $str .= '<option value="' . $category_array['category_id'] . '" ' . $selected . '>' . $category_array['category_name'] . '</option>';
                                                }
                                            }
                                            else {
                                                $selected = ' ';
                                                if (set_value('category_id') == $parent_category_array['category_id'])
                                                    $selected = '  selected="selected" ';
                                                $str .= '<option value="' . $parent_category_array['category_id'] . '" ' . $selected . '>' . $parent_category_array['category_name'] . '</option>';
                                            }
                                            $str .= '</optgroup>';
                                        }
                                    }
                                    echo $str;
                                    ?>                     
                                </select>                        
                            </div>
                            <div class="control-label col-md-1 col-sm-6 col-xs-12"> 
                                <a href="javascript:void(0)" onclick="load_all_categories()" title="Refresh"><i class="fa fa-refresh fa-lg" aria-hidden="true"></i></a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 col-sm-3 col-xs-12">Comment <span class="required">*</span></label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                
                                <textarea name="content" id="content" tabindex="<?php echo $tab_index++; ?>"><?php echo set_value('content'); ?></textarea>
                            </div>
                        </div>





                        <div class="form-group">
                            <label  class="control-label col-md-2 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-10 col-sm-6 col-xs-12">
                                <div class="radio">
                                    <label><input type="radio" name="status" id="status1" value="open" <?php echo set_radio('status', 'open', TRUE); ?> tabindex="<?php echo $tab_index++; ?>"/> Open</label>
                                </div>  
                                <div class="radio">
                                    <label> <input type="radio" name="status" id="status0" value="closed" <?php echo set_radio('status', 'closed'); ?> tabindex="<?php echo $tab_index++; ?>"/> Closed</label>
                                </div>                        
                            </div>
                        </div>

                        <!-- changes777-->
                        <div class="ln_solid"></div>

                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Author Name </label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                	
                                <input type="text" name="author_name" id="author_name" value="<?php echo set_value('account_id', $_SESSION['search_t_data']['s_author_name']); ?>" class="form-control" tabindex="<?php echo $tab_index++; ?>">       
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-3 col-xs-12">Author Email </label>
                            <div class="col-md-10 col-sm-6 col-xs-12">                	
                                <input type="text" name="author_email" id="author_email" value="<?php echo set_value('account_id', $_SESSION['search_t_data']['s_author_email']); ?>" class="form-control" data-parsley-type="email" tabindex="<?php echo $tab_index++; ?>">       
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-3 col-xs-12">Subscribe For Email On Reply</label>
                            <div class="col-md-1 col-sm-6 col-xs-12">                	
                                <input type="checkbox" name="author_email_subscribe" id="author_email_subscribe" value="1" class="form-control" tabindex="<?php echo $tab_index++; ?>">       
                            </div>
                        </div> 
                        <!-- changes777-->




                    </div>     






                    <div class="col-md-3 col-sm-12 col-xs-12"> 
                        <div class="form-group">
                            <label>Upload Files</label>
                        </div>    
                        <ul class="messages">
                            <li><input type="file" name="file_upload1" id="file_upload1" value=""/></li>
                            <li><input type="file" name="file_upload2" id="file_upload2" value=""/></li>
                            <li><input type="file" name="file_upload3" id="file_upload3" value=""/></li>
                        </ul>  
                    </div>
                    <div class="clearfix"></div>                       
                    <div class="ln_solid"></div> 

                    <div class="form-group">
                        <div class="col-md-8 col-sm-6 col-xs-12 col-md-offset-4 text-right">
                            <a href="<?php echo base_url('ticket') ?>"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++; ?>">Cancel</button></a>
                            <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++; ?>">Save</button>
                        </div>
                    </div>




                </form>   



            </div>

        </div>

    </div>

    <div class="clearfix"></div>

</div>

<script>

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

    })

    function load_all_categories() {

        var target_url = BASE_URL + "testticket/load_all_categories";
        $.ajax({
            url: target_url,
            dataType: 'json',
            type: 'POST',
            success: function (data, textStatus, XMLHttpRequest)
            {
                $('#category_id').html(data);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown)
            {
                alert("error");
            }
        });
    }
    function subject_selected()
    {
        subject_select_text = $("#subject_select option:selected").text();
        subject_select = $('#subject_select').val();
        if (subject_select != '')
        {
            $('#subject').val(subject_select_text);


            var target_url = BASE_URL + "testticket/ajax_get_category_by_subject";
            var data_post = {subject_select: subject_select};

            $.ajax({
                url: target_url,
                dataType: 'json',
                type: 'POST',
                data: data_post,
                success: function (data, textStatus, XMLHttpRequest)
                {
                    if (data == 'error')
                    {
                        //alert("error");
                    } else
                    {
                        $('#category_id').html(data);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown)
                {
                    alert("error");
                }
            });

        }


    }
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