<?php	
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0
// License https://www.gnu.org/licenses/agpl-3.0.html
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

$tab_index=1;
//echo '<pre>';
//print_r($currency_conversion['result']);
//print_r($data);
//echo '</pre>';	
?>
<link href="<?php echo base_url()?>theme/default/css/ticket.css" rel="stylesheet">
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Support Tickets </h2>
            <ul class="nav navbar-right panel_toolbox">
              <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
              <li><a href="<?php echo base_url()?>ticket/create"><input type="button" value="Create Ticket" name="add_link" class="btn btn-primary"></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>
    
     	<div class="x_content">
     
		
        <form action="<?php echo base_url();?>ticket" method="post" name="search_form" id="search_form" data-parsley-validate class="form-horizontal form-label-left">
       
		<input type="hidden" name="search_action" value="search" />
        <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
         <div class="form-group">
        
			<label class="control-label col-md-1 col-sm-3 col-xs-12" >Ticket No.</label>
            <div class="col-md-2 col-sm-6 col-xs-12">
              <input type="text" name="ticket_number" id="ticket_number" value="<?php echo $_SESSION['search_t_data']['s_ticket_number']; ?>" class="form-control col-md-7 col-xs-12 data-search-field" >
            </div>	
            
           
            <label class="control-label col-md-1 col-sm-3 col-xs-12 col-md-offset-1" >Status</label>
            <div class="col-md-2 col-sm-6 col-xs-12">
             <select name="status" id="status" class="form-control data-search-field">
                <option value="">Select</option>
                <option value="open" <?php if($_SESSION['search_t_data']['s_status']=='open') echo 'selected="selected"';?> >Open</option>
                <option value="closed" <?php if($_SESSION['search_t_data']['s_status']=='closed') echo 'selected="selected"';?>>Closed</option>
             </select>
            </div>
        
        	<div class="searchBar">  
				<input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
				<input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" >
            </div>
         </div>   
		
       </form>
       </div>
   

        <div class="clearfix"></div>
        <div class="ln_solid"></div>
		<div class="row">  
			<?php		
			 dispay_pagination_row($total_records, $_SESSION['search_t_data']['s_no_of_records'], $pagination);
			?>                    
        </div>         
                    
         <table class="table table-striped ">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th width="120">Ticket Number</th>
                  <th width="150">Last Post</th>
                  <th width="70">Status</th>
                  <th width="180" align="center"></th>
                </tr>
              </thead>
              <tbody>
            
            <?php
			if(count($data['result']) > 0)
			{								
				foreach($data['result'] as $ticket_data)
				{	    
				?>
                
                <tr>
                  
                  <td class="td_2">
                    <a href="<?php echo base_url();?>ticket/details/<?php echo param_encrypt($ticket_data['ticket_id']);?>" class="tag3"><?php echo $ticket_data['subject'];?></a>
                  </td>
                  <td><strong><?php echo $ticket_data['ticket_number'];?></strong></td>
                  
                  <td class="td_4">
                    <p>
                    <?php
					$last_post_by = 'Me';
					$last_post_created_date = '';
					if(isset($ticket_data['last_post']['created_by']))
					{
						if($ticket_data['last_post']['created_by'] != $ticket_data['account_id'])
							$last_post_by = 'ADMIN';
							
						$last_post_created_date =date(DATE_FORMAT_1, strtotime($ticket_data['last_post']['create_date']));
						$last_post_created_date = '<small><i class="fa fa-clock-o"></i> '.$last_post_created_date.'</small>';	
					}		
				
					?>
                    By <strong><?php echo $last_post_by;?></strong> <br /> 
                    <?php echo $last_post_created_date;?>
                    </p>
                  </td>
                  <td class="td_5">
                   <?php if($ticket_data['status']=='open'){ ?>
                    <button type="button" class="btn btn-success btn-xs">Open</button>
                   <?php  }else{?> 
                    <button type="button" class="btn btn-danger btn-xs">Closed</button>
                   <?php } ?>
                  </td>
                  <td class="td_6" align="center" >
                      <p>                      
                      <strong><?php echo $ticket_data['total_post'];?></strong> Comments
                      <br />
                      Category:<i># <strong><?php echo $ticket_data['category']['category_name'];?></strong></i>
                      <br />
                      Created: <small><i class="fa fa-clock-o"></i> <?php echo date(DATE_FORMAT_1, strtotime($ticket_data['create_date'])); ?></small>
                      </p>
                  </td>
                </tr>
                                
                <?php
				}
			}
			else
            {
            ?>
            <tr>
                <td colspan="4" align="center"><strong>No Record Found</strong></td>
            </tr>
            <?php
            }
            ?>	
                
                
                
                
                
                
                
                
                
              </tbody>
         </table>
         
         <div class="clearfix"></div>
         
         
         
         

                      
  </div>      
</div> 
<script>
$(document).ready(function() {
	$('#OkFilter').click(function(){
			var no_of_records=$('#no_of_records').val();				
		 	$('#no_of_rows').val(no_of_records);			
		 });
});
</script>