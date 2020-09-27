<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0.3
// License https://www.gnu.org/licenses/agpl-3.0.html
//
//
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################

//echo '<pre>';
//print_r($ticket_data);
//echo '</pre>';
$tab_index = 1;
?>
<script src="<?php echo base_url()?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<link href="<?php echo base_url()?>theme/default/css/ticket.css" rel="stylesheet">

<div class="col-md-12 col-sm-6 col-xs-12">
   <div class="x_panel">
      <div class="x_title">
        <h2>Support Ticket</h2>
        <ul class="nav navbar-right panel_toolbox">
          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
        </ul>
        <div class="clearfix"></div>
      </div>
     <div class="x_content">     
     <div class="col-md-12 col-sm-9 col-xs-12">

        <ul class="messages">              
        	<li>
                <div class="col-md-2">
                    
                    <a href="<?php echo base_url('customers/edit/').param_encrypt($ticket_data['account_id']);?>" class="tag2" target="_blank"><?php echo $ticket_data['account_id'];?></a>
                    <?php if($ticket_data['company_name']!=''){
					echo '<br />'.$ticket_data['company_name'];
					}?>
                    
                    <br />
                    <i class="fa fa-clock-o"></i> <?php echo date(DATE_FORMAT_2, strtotime($ticket_data['create_date'])); ?>
                    <br />
                    <strong><?php echo $ticket_data['ticket_number'];?></strong>
                    <br /><span class="text-warning">By '<?php echo $ticket_data['created_by_name'];?>'</span>
                </div>
                <div class="col-md-9">
                    <div class="message_wrapper">
                  <h4 class="heading"><?php echo $ticket_data['subject'];?></h4>
                  <blockquote class="message"><?php echo $ticket_data['content'];?></blockquote>
                  
                   <br>                      
					<?php
                      if(isset($ticket_data['attachment']) && count($ticket_data['attachment'])>0)
                      {
                        foreach($ticket_data['attachment'] as $attachment_data)
                        {	
                            $file_name = $attachment_data['file_name'];
                            $file_name_display = $attachment_data['file_name_display'];
                            
                            $download_path = 'uploads/ticket/'.strtolower(SITE_SUBDOMAIN).'/'.$ticket_data['account_id'];						
                            $file_path = $download_path.'/'.$file_name;
                            if(file_exists($file_path))
                            {
                                $download_link = base_url('download/ticket/'.param_encrypt($ticket_data['account_id']).'/'.param_encrypt($file_name));
                                echo '<p class="url"><span class="fs1 text-info" aria-hidden="true" data-icon=""></span>
                                <a href="'.$download_link.'"><i class="fa fa-paperclip"></i> '.$file_name_display.' </a></p>';
                        
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
              <span class="name">Category:<i>  # <strong><?php echo $ticket_data['category']['category_name'];?></strong></i></span>
              <?php if($ticket_data['status']=='open'){?>
              <span class="edit_text" onclick="inline_update(1)">edit</span>
               <?php } ?>
            </li>
            <li>
              <span class="name"> Assigned to:<i># <strong><?php echo $ticket_data['assigned_to']['assigned_to_name'];?></strong></i></span>
              <?php if($ticket_data['status']=='open'){?>
              <span class="edit_text" onclick="inline_update(2)">edit</span>
               <?php } ?>
            </li>
            <li>
              <span class="name"> Status: 
              		<?php if($ticket_data['status']=='open'){ ?>
                    <button type="button" class="btn btn-success btn-xs">Open</button>
                   <?php  }else{?> 
                    <button type="button" class="btn btn-danger btn-xs">Closed</button>
                   <?php } ?>
              </span>
              <?php if($ticket_data['status']=='open'){?>
              <span class="edit_text" onclick="inline_update(3)">edit</span>
              <?php } ?>
            </li>
        </ul>
        <div class="clearfix"></div>
        <div class=" hide inline_update" id="inline_update1">
        	
            <div class="col-md-2"> Category </div>
            <div class="col-md-3">
            <form action="" method="post" name="category_form" id="category_form" data-parsley-validate class="form-horizontal form-label-left">
            <input type="hidden" name="action" value="OkSaveCategory"> 
            <select name="category_id" id="category_id" class="form-control" data-parsley-required="">
            <option value="">Select </option>   
             <?php 
            $str = '';
            if(isset($category_data['result']) && count($category_data['result']) > 0)
            {
                foreach($category_data['result'] as  $key=>$parent_category_array)
                {
					if($ticket_data['category_id']==$parent_category_array['category_id'])
						continue;
					
                    $str .= '<optgroup label="'.$parent_category_array['category_name'].'">';
                    if(isset($parent_category_array['sub']) && count($parent_category_array['sub'])>0)
                    {
                        foreach($parent_category_array['sub'] as  $key=>$category_array)
                        {						
							if($ticket_data['category_id']==$category_array['category_id'])
								continue;		
                            $selected = ' ';
                            $str .= '<option value="'.$category_array['category_id'].'" '.$selected.'>'.$category_array['category_name'].'</option>';
                        }
                    }
                    else
                    {
                        $selected = ' ';
                        $str .= '<option value="'.$parent_category_array['category_id'].'" '.$selected.'>'.$parent_category_array['category_name'].'</option>';
                    }
                    $str .= '</optgroup>';
                }
            }
            echo $str;
            ?>
                        
            </select> 
            </form>
            </div>
            <div class="col-md-4"><button type="button" id="category_id_btnSave" class="btn btn-primary" tabindex="<?php echo $tab_index++;?>">Save</button></div>
           <div class="clearfix"></div>
         </div>  
         
         <div class=" hide inline_update col-md-12" id="inline_update2">
        	
            <div class="col-md-2">Assigned To </div>
            <div class="col-md-3">
            <form action="" method="post" name="assignto_form" id="assignto_form" data-parsley-validate class="form-horizontal form-label-left">
                <input type="hidden" name="action" value="OkSaveAssignedto"> 
                <select name="assigned_to_id" id="assigned_to_id" class="form-control" data-parsley-required="">
                <option value="">Select </option>
                <?php 
				$str = '';
				if(isset($assignto_data['result']) && count($assignto_data['result']) > 0)
				{                        
					foreach($assignto_data['result'] as  $key=>$assignto_array)
					{					
						if($ticket_data['assigned_to_id']==$assignto_array['assigned_to_id'])
								continue;				
						$selected = ' ';						
						$str .= '<option value="'.$assignto_array['assigned_to_id'].'" '.$selected.'>'.$assignto_array['assigned_to_name'].'</option>';
					}
					 
				}
				echo $str;
                ?>
                   
                </select> 
            </form>
            </div>
            <div class="col-md-4">
             <button type="button" id="assignto_btnSave" class="btn btn-primary" tabindex="<?php echo $tab_index++;?>">Save</button>
             </div>
            <div class="clearfix"></div>
           
         </div>  
         <div class=" hide inline_update" id="inline_update3">
        
             <div class="col-md-2"> Status </div>
             <div class="col-md-3">
             <form action="" method="post" name="status_form" id="status_form" data-parsley-validate class="form-horizontal form-label-left">
                <input type="hidden" name="action" value="OkSaveStatus"> 
             	<select name="status" id="status" class="form-control " data-parsley-required="">
                <option value="">Select</option>
                <?php if($ticket_data['status']=='open'){ ?>                
                <option value="closed">Closed</option>
                <?php }else{?>
                <option value="open">Open</option>
                <?php } ?>
               </select>
             </form>
             </div>
            <div class="col-md-4"> <button type="button" id="status_btnSave" class="btn btn-primary" tabindex="<?php echo $tab_index++;?>">Save</button></div>
           <div class="clearfix"></div>
         </div>   
        <br>
			        
		<div class="clearfix"></div>
        <div>

			<h4><strong>Replies</strong></h4>
			<ul class="messages ">                  
            <?php
			if(count($ticket_data['replies']) > 0)
			{		
				$k = 0;						
				foreach($ticket_data['replies'] as $reply_data)
				{	    
					if($reply_data['created_by']==$ticket_data['account_id'])
						$created_by = 'CUSTOMER';
					else
						$created_by = 'ADMIN';	
						
					if($k++ % 2==0)
						$li_class = 'bg1';
					else	
						$li_class = '';		
				?>
                  <li class="<?php echo $li_class;?>">
                    <div class="col-md-2">
                        <strong><?php echo $created_by;?></strong><br />
                        <strong>(<?php echo $reply_data['created_by_name'];?>)</strong><br />
                        <i class="fa fa-clock-o"></i> <?php echo date(DATE_FORMAT_2, strtotime($reply_data['create_date'])); ?>
                        <?php if($reply_data['hide_from_customer'] == 'Y')
								echo '<br><br><span class="highlight"><small><i>Hidden from customer</i></small></span>';
						?>
                    </div>
                    <div class="col-md-9">
                        <div class="message_wrapper">
                          <h4 class="heading"><?php echo $reply_data['subject'];?></h4>
                          <blockquote class="message"><?php echo $reply_data['content'];?></blockquote>
                      <br>
                      
                        <?php
						  if(isset($reply_data['attachment']) && count($reply_data['attachment'])>0)
						  {
							foreach($reply_data['attachment'] as $attachment_data)
							{	
								$file_name = $attachment_data['file_name'];
								$file_name_display = $attachment_data['file_name_display'];
								
								$download_path = 'uploads/ticket/'.strtolower(SITE_SUBDOMAIN).'/'.$ticket_data['account_id'];						
								$file_path = $download_path.'/'.$file_name;
								if(file_exists($file_path))
								{
									$download_link = base_url('download/ticket/'.param_encrypt($ticket_data['account_id']).'/'.param_encrypt($file_name));
									echo '<p class="url"><span class="fs1 text-info" aria-hidden="true" data-icon=""></span>
									<a href="'.$download_link.'"><i class="fa fa-paperclip"></i> '.$file_name_display.' </a></p>';
							
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
     
     
      <?php if($ticket_data['status']=='open'):?>
     <div class="x_content">
     	<div class="col-md-12 col-sm-12 col-xs-12"> 
        	<div class="col-md-8 col-sm-6 col-xs-12"><h2>Reply</h2></div>
            <div class="col-md-4 col-sm-6 col-xs-12"><h2>Upload Files</h2> </div>
        </div>
      <div class="col-md-12 col-sm-12 col-xs-12"> 
        <form action="" method="post" name="ticket_form" id="ticket_form" data-parsley-validate class="form-horizontal form-label-left" enctype="multipart/form-data">
        <input type="hidden" name="action" value="OkSaveData"> 
        <input type="hidden" name="button_action" id="button_action" value="">        
        <input type="hidden" name="ticket_account_id" id="ticket_account_id" value="<?php echo $ticket_data['account_id'];?>">
        <input type="hidden" name="user_emailaddress" id="user_emailaddress" value="<?php echo $user_data['emailaddress'];?>">        
        <input type="hidden" name="ticket_number" id="ticket_number" value="<?php echo $ticket_data['ticket_number'];?>">
         <?php	 $default_subject = 'Re: '.$ticket_data['subject'];?>        
        
        <div class="form-group">
            <div class="col-md-8 col-sm-6 col-xs-12"> 
            	<div class="form-group">
                 	<label class="control-label col-md-2 col-sm-3 col-xs-12">Subject <span class="required">*</span></label>
                    <div class="col-md-10 col-sm-6 col-xs-12"> 
                    <input type="text" name="subject" id="subject" value="<?php echo set_value('subject', $default_subject); ?>" class="form-control" data-parsley-required="" data-parsley-minlength="4" tabindex="<?php echo $tab_index++;?>">
                    </div>  
                 </div>
                 <div class="form-group">
                 	<div class="col-md-12 col-sm-12 col-xs-12">
                 <textarea name="content" id="content" tabindex="<?php echo $tab_index++;?>"><?php echo set_value('content'); ?></textarea>
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
            <input type="checkbox" name="hide_from_customer" id="hide_from_customer"/> Hide This Comment From Customer
            <br />
            <input type="checkbox" name="send_mail_to_customer" id="send_mail_to_customer"/> Send mail to Customer (<span class='highlight'><?php echo $user_data['emailaddress'];?></span>)
            <br />
            Send mail to others <input type="text" class="form-control" name="other_emails" value=""/><small>(comma separated email ids)</small>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 text-right">
            <a href="<?php echo base_url()?>ticket"><button class="btn btn-primary" type="button" tabindex="<?php echo $tab_index++;?>">Cancel</button></a>
            <button type="button" id="btnSave" class="btn btn-success" tabindex="<?php echo $tab_index++;?>">Save</button>
        </div>
      </div>
      </form>
      </div>
     </div>
     
     <?php else:?>
     <div class="col-md-12 col-sm-6 col-xs-12 text-right">
           <a href="<?php echo base_url()?>ticket"><button class="btn btn-primary" type="button">Back</button></a>
      </div>
     <?php endif;?>
                     
  </div>      
</div> 

<script>

function inline_update(id_number)
{
	div_id = 'inline_update'+id_number;
	
	if($('#'+div_id).hasClass('hide'))
	{
		$('.inline_update').addClass('hide');
		$('#'+div_id).removeClass('hide');
	}
	else
	{
		$('.inline_update').addClass('hide');	
	}
}

$('#btnSave, #btnSaveClose').click(function() {
	$('#ticket_form').parsley().reset();
	
	var is_ok = $("#ticket_form").parsley().isValid();
	if(is_ok === true)
	{
		var clicked_button_id = this.id;
		if(clicked_button_id=='btnSaveClose')
			$('#button_action').val('save_close');
		else
			$('#button_action').val('save');	
		
		if(is_ok === true)
		{
			$("#ticket_form").submit();
		}	
	}
	else
	{
		$('#ticket_form').parsley().validate();
	}
});

$('#category_id_btnSave').click(function() {
	$('#category_form').parsley().reset();
	
	var is_ok = $("#category_form").parsley().isValid();
	if(is_ok === true)
	{			
		if(is_ok === true)
		{
			$("#category_form").submit();
		}	
	}
	else
	{
		$('#category_form').parsley().validate();
	}
});


$('#assignto_btnSave').click(function() {
	$('#assignto_form').parsley().reset();
	
	var is_ok = $("#assignto_form").parsley().isValid();
	if(is_ok === true)
	{			
		if(is_ok === true)
		{
			$("#assignto_form").submit();
		}	
	}
	else
	{
		$('#assignto_form').parsley().validate();
	}
});


$('#status_btnSave').click(function() {
	$('#status_form').parsley().reset();
	
	var is_ok = $("#status_form").parsley().isValid();
	if(is_ok === true)
	{			
		if(is_ok === true)
		{
			$("#status_form").submit();
		}	
	}
	else
	{
		$('#status_form').parsley().validate();
	}
});
</script>            
<script src="<?php echo base_url()?>theme/vendors/tinymce/js/tinymce/tinymce.min.js"></script>
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