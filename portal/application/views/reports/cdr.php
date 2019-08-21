<!--
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
-->
<?php
$account_id = $search_account_id;
$dir = CDR_DIRECTORY;

//$dailycdr_dir = "uploads/USERDATA/{{ACCOUNT_ID}}/cdr/dailycdr/";
$dailycdr_dir  = str_replace('{{ACCOUNT_ID}}',$account_id,$dir);
$dailycdr_dir  = str_replace('{{FOLDER}}','dailycdr',$dailycdr_dir);
$dailycdr_result_array = dirToArray($dailycdr_dir);


//$monthlycdr_dir = "uploads/USERDATA/{{ACCOUNT_ID}}/cdr/monthlycdr/";
$monthlycdr_dir  = str_replace('{{ACCOUNT_ID}}',$account_id,$dir);
$monthlycdr_dir  = str_replace('{{FOLDER}}','monthlycdr',$monthlycdr_dir);
$monthlycdr_result_array = dirToArray($monthlycdr_dir);



$archivecdr_dir  = str_replace('{{ACCOUNT_ID}}',$account_id,$dir);
$archivecdr_dir  = str_replace('{{FOLDER}}','archivecdr',$archivecdr_dir);
$archivecdr_result_array = dirToArray($archivecdr_dir);
?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Daily CDR</h2>
        <ul class="nav navbar-right panel_toolbox">
                            
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">                               
      
        <div class="table-responsive">
        <table class="table table-striped jambo_table table-bordered" id="table-sort">
            <thead>
              <tr class="headings thc">
                <th class="column-title text-center">Date</th>
                <th class="column-title text-center">Type</th>
                <th class="column-title text-center">File Size</th>
                <th class="column-title text-center">Downlaod</th>                            
              </tr>
            </thead>

            <tbody>
             <?php
            if(count($dailycdr_result_array) > 0)
            {								
                foreach($dailycdr_result_array as $file_name)
                {			
                    $file_name_display = $file_name;
                    if(strlen($file_name)>7)			
                    {
                        $file_date_year = substr($file_name,0,4);
                        $file_date_month = substr($file_name,4,2);
                        $file_date_day = substr($file_name,6,2);
                        
                        $file_date = $file_date_year.'-'.$file_date_month.'-'.$file_date_day;
                        
                        $file_name_display = date(DATE_FORMAT_1,strtotime($file_date));
						
						$file_size = convertToReadableSize(filesize($dailycdr_dir.'/'.$file_name));					
						$is_incoming = strpos($file_name, 'incoming');
                    }
                ?>
                    <tr>                                    
                        <td class="text-center"><?php echo $file_name_display; ?></td>
                        <td class="text-center"><?php 
						if ($is_incoming === false) { echo 'Outbound ';}
						else 				{ echo 'Inbound ';}
						 ; ?></td>
                        <td class="text-center"><?php echo $file_size; ?></td>
                        <td  class="text-center"><a href="<?php echo base_url('download/cdr/'.param_encrypt($account_id).'/'.param_encrypt('dailycdr').'/'.param_encrypt($file_name));?>"><button type="button" class="btn btn-dark btn-sm">download</button></a></td>                                    
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
</div>                    
         
        
      </div>
    </div>
    
    
    
    
    
    <div class="x_panel">
      <div class="x_title">
        <h2>Archive CDR</h2>
        <ul class="nav navbar-right panel_toolbox">
                            
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">                               
      
        <div class="table-responsive">
        <table class="table table-striped jambo_table table-bordered" id="table-sort">
            <thead>
              <tr class="headings thc">
                <th class="column-title text-center">Date</th>
                <th class="column-title text-center">Type</th>
                <th class="column-title text-center">File Size</th>
                <th class="column-title text-center">Downlaod</th>                            
              </tr>
            </thead>

            <tbody>
             <?php
            if(count($archivecdr_result_array) > 0)
            {								
                foreach($archivecdr_result_array as $file_name)
                {			
                    $file_name_display = $file_name;
                    if(strlen($file_name)>7)			
                    {
                        $file_date_year = substr($file_name,0,4);
                        $file_date_month = substr($file_name,4,2);
                        $file_date_day = substr($file_name,6,2);
                        
                        $file_date = $file_date_year.'-'.$file_date_month.'-'.$file_date_day;
                        
                        $file_name_display = date(DATE_FORMAT_1,strtotime($file_date));
						
						$file_size = convertToReadableSize(filesize($archivecdr_dir.'/'.$file_name));					
						$is_incoming = strpos($file_name, 'incoming');
                    }
                ?>
                    <tr>                                    
                        <td class="text-center"><?php echo $file_name_display; ?></td>
                        <td class="text-center"><?php 
						if ($is_incoming === false) { echo 'Outbound ';}
						else 				{ echo 'Inbound ';}
						 ; ?></td>
                        <td class="text-center"><?php echo $file_size; ?></td>
                        <td  class="text-center"><a href="<?php echo base_url('download/cdr/'.param_encrypt($account_id).'/'.param_encrypt('archivecdr').'/'.param_encrypt($file_name));?>"><button type="button" class="btn btn-dark btn-sm">download</button></a></td>                                    
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
</div>                    
         
        
      </div>
    </div>
    
    
    
    
    
    
 </div>
              

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Monthly CDR</h2>
        <ul class="nav navbar-right panel_toolbox">
                            
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">                               
      
        <div class="table-responsive">
        <table class="table table-striped jambo_table table-bordered" id="table-sort2">
            <thead>
              <tr class="headings thc">
                <th class="column-title text-center">Month</th>
                <th class="column-title text-center">Type</th>
                <th class="column-title text-center">File Size</th>
                <th class="column-title text-center">Downlaod</th>                            
              </tr>
            </thead>

            <tbody>
             <?php
            if(count($monthlycdr_result_array) > 0)
            {								
                foreach($monthlycdr_result_array as $file_name)
                {			
                    $file_name_display = $file_name;
                    if(strlen($file_name)>7)			
                    {
                        $file_date_year = substr($file_name,0,4);
                        $file_date_month = substr($file_name,4,2);
                        $file_date_day = substr($file_name,6,2);
                        
                        $file_date = $file_date_year.'-'.$file_date_month.'-'.$file_date_day;
                        
                        $file_name_display = date('F-Y',strtotime($file_date));
						
						$file_size = convertToReadableSize(filesize($monthlycdr_dir.'/'.$file_name));					
						$is_incoming = strpos($file_name, 'incoming');
                    }
                ?>
                    <tr>                                    
                        <td class="text-center"><?php echo $file_name_display; ?></td>
                         <td class="text-center"><?php 
						if ($is_incoming === false) { echo 'Outbound ';}
						else 				{ echo 'Inbound ';}
						 ; ?></td>
                        <td class="text-center"><?php echo $file_size; ?></td>
                        <td class="text-center"><a href="<?php echo base_url('download/cdr/'.param_encrypt($account_id).'/'.param_encrypt('monthlycdr').'/'.param_encrypt($file_name));?>"><button type="button" class="btn btn-dark btn-sm">download</button></a></td>                                    
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
</div>                    
         
        
      </div>
    </div>
  </div>                      
<script>
$(document).ready(function() {
	showDatatable('table-sort', [1], [ 0,"asc" ] );
	
	showDatatable('table-sort2', [1], [ 0,"asc" ] );
});
</script>  