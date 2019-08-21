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
<link href="<?php echo base_url()?>theme/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css" rel="stylesheet">
<?php
$title = 'Netting Report';
if($make_search)
{
	if(isset($suppliers_data['result']) && count($suppliers_data['result'])>0)
	{
		foreach($suppliers_data['result'] as $supplier_array)
		{
			if($_SESSION['search_repo_supp_inv']['s_supplier_id_name'] == $supplier_array['supplier_id_name']) 
			{
				$title .=' ('.$supplier_array['supplier_name'].')'; 				
				$title .=' ('.$supplier_array['currency_name'].')';
				break;
			}
		}
	}
}
?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2><?php echo $title;?></h2>
            <ul class="nav navbar-right panel_toolbox">
                
            </ul>			
            <div class="clearfix"></div>			
        </div>
		
				  
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url();?>reports/netting">
			<input type="hidden" name="search_action" value="search" />		
			 
            <div class="form-group">
           	
                <label class="control-label col-md-2 col-sm-3 col-xs-12" >From Date</label>
                <div class="col-md-4 col-sm-6 col-xs-12" >
                    <div class="input-group date" id="from_date_div" >
                            <input class="form-control" type="text" name="from_date" id="from_date" value="<?php echo $_SESSION['search_repo_supp_inv']['s_from_date']; ?>" readonly="readonly" >
                                <span class="input-group-addon" style="">
                                <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                    </div>
                </div>        
        
                <label class="control-label col-md-2 col-sm-3 col-xs-12" >To Date</label>
                <div class="col-md-4 col-sm-6 col-xs-12" >
                    <div class="input-group date" id="to_date_div">
                            <input class="form-control" type="text" name="to_date" id="to_date" value="<?php echo $_SESSION['search_repo_supp_inv']['s_to_date']; ?>" readonly="readonly">
                                <span class="input-group-addon" style="">
                                <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                    </div>
                </div>
				
			</div>
				
           <div class="form-group">           				
				<label class="control-label col-md-2 col-sm-4 col-xs-12">Supplier</label>
                <div class="col-md-4 col-sm-9 col-xs-12">
                    <select name="supplier_id_name" id="supplier_id_name" class="form-control">  
					<?php 
					$str = '';
					$str_array = array();
					if(isset($suppliers_data['result']) && count($suppliers_data['result'])>0)
					{
                        foreach($suppliers_data['result'] as $supplier_array)
                        {
                            $selected = ' ';
							if($_SESSION['search_repo_supp_inv']['s_supplier_id_name'] == $supplier_array['supplier_id_name']) 
								$selected = '  selected="selected" ';						
							
                            $str .= '<option value="'.$supplier_array['supplier_id_name'].'" '.$selected.'>'.$supplier_array['supplier_name'].'</option>';
                        }
					}
					echo $str;
                        ?>
                     </select>
                </div> 	
               							
				<div class="searchBar">
					<input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                    <!--<input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" >-->
					
					<div class="btn-group hide">                    
                    
						<button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
						Export <span class="caret"></span></button>
						<ul class="dropdown-menu" role="menu">
						<?php
						$export_format_array = get_export_formats();
						foreach($export_format_array as $export_format)
						{
							echo '<li><a href="'.base_url().'testreports/carrier_reconcilliation/export/'.param_encrypt($export_format).'">'.strtoupper($export_format).'</a></li>';
						}							
						?>                            
						</ul>
					</div>  
				 </div>
			  </div>
			</form>		
        </div>  
    </div>
  
    <div class="x_panel">
    	
        <div class="x_content">
            <div class="table-responsive">
            <?php if($make_search)
			{?>
                <table class="table table-striped jambo_table table-bordered" id="table-sort">
                <thead>
                  <tr class="headings thc"> 				
                    <th class="column-title text-center" width="10"><nobr>S. no</nobr></th>
                    <th class="column-title text-center"><nobr>Invoice Number</nobr></th>
                    <th class="column-title text-center"><nobr>Invoice Date</nobr></th>
                    <th class="column-title text-center"><nobr>Description</nobr></th>
                    <th class="column-title text-right"><nobr>Dr.</nobr></th>
                    <th class="column-title text-right"><nobr>Cr.</nobr></th>						
                    <th class="column-title text-right"><nobr>Due Amount</nobr></th>                     
                  </tr>
                </thead>		
                <tbody>
			  <?php
			  	$counter=1;
				$amount_due=0;
			  	$credit_total = $debit_total = 0;
                if(count($supplier_invoice_data['result']) > 0)
				{								
					foreach($supplier_invoice_data['result'] as $supplier_invoice)
					{			
						$total_invoice_amount = round($supplier_invoice['total_invoice_amount'],2);
						
						$credit_t = $debit_t = '';
						if($supplier_invoice['invoice_type']=='IN')
						{
							$credit_t = $total_invoice_amount;
							$credit_total +=$credit_t;
						}	
						else
						{
							$debit_t = $total_invoice_amount;
							$debit_total +=$debit_t;
						}	
											
						$amount_due = $debit_total - $credit_total;
					?>
						<tr >
							<td class="text-center"><?php echo $counter; ?></td>
                            <td class="text-center"><?php echo $supplier_invoice['invoice_number']; ?></td>
                            <td class="text-center"><?php echo date(DATE_FORMAT_1, strtotime($supplier_invoice['invoice_date'])) ; ?></td>							
                            <td class="text-center"><?php echo $supplier_invoice['description']; ?></td>
                            <td class="text-right"><?php echo $debit_t; ?></td>
                            <td class="text-right"><?php echo $credit_t; ?></td>
                            <td class="text-right"><?php echo $amount_due; ?></td>
						</tr>

				<?php
					$counter++;
					}		
				?>
                 		<tr class="text-success success" >
							<td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>							
                            <td class="text-center"></td>
                            <td class="text-right"><strong><?php echo $debit_total; ?></strong></td>
                            <td class="text-right"><strong><?php echo $credit_total; ?></strong></td>
                            <td class="text-right"><strong><?php echo $amount_due; ?></strong></td>
						</tr>   
                    
                    
                <?php		
				}
				else
				{
				?>
				<tr>
					<td colspan="12" align="center"><strong>No Record Found</strong></td>
				</tr>
				<?php
				}
				?>
                           
                </tbody>
            </table>
             <?php
			 }else
			 {
			 
			 
			 }?>                   
            </div>			
        </div>
        
        <br />
	  
    </div>          
</div> 

<script src="<?php echo base_url()?>theme/vendors/moment/min/moment.min.js"></script>  
<script src="<?php echo base_url()?>theme/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>     
	
<script>
$(document).ready(function() {
	$('#from_date_div').datetimepicker({
	   format: 'YYYY-MM-DD',
	    ignoreReadonly: true
    });
	
	$('#to_date_div').datetimepicker({
	   format: 'YYYY-MM-DD',
	    ignoreReadonly: true
    });
});
</script> 


<?php
//echo '<pre>';
//print_r($suppliers_data);
//print_r($supplier_invoice_data);
//echo '</pre>';
?>