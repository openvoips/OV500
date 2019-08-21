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
//echo '<pre>';
//print_r($report_data['result'][0]);
//echo '</pre>';
?>
<?php
$currency_array=array();
for($i=0; $i<count($currency_data); $i++)
{									
	$currency_id = $currency_data[$i]['currency_id'];
	$currency_array[$currency_id] = $currency_data[$i]['name'];
}	
?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Reporting : Supplier Detail Audit</h2>			
            <div class="clearfix"></div>			
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url();?>reports/supplier_detail_audit">
			<input type="hidden" name="search_action" value="search" />
			<input type="hidden" name="no_of_rows" id="no_of_rows" value="" />		
            
            <div class="form-group">
           		<label class="control-label col-md-2 col-sm-4 col-xs-12">Record Date</label>
                <div class="col-md-4 col-sm-9 col-xs-12">
                    <input type="text" name="record_date" id="record_date" class="form-control " value="<?php if(isset($_SESSION['search_c_reconcilliation']['s_record_date'])) echo $_SESSION['search_c_reconcilliation']['s_record_date']; ?>" readonly="readonly" data-parsley-required="" />
                </div>   
                
                <label class="control-label col-md-2 col-sm-3 col-xs-12">Service Type</label>
				<div class="col-md-3 col-sm-8 col-xs-12">
					<?php
                    $service_type_array = array('DID','SERVER','VOIP');
                    ?>
					<select name="service_type" id="service_type" class="form-control data-search-field">	
                    	<option value="">Select</option>
						<?php foreach($service_type_array as $service_type){?>						
						<option value="<?php echo $service_type; ?>" <?php if($_SESSION['search_c_reconcilliation']['s_service_type']==$service_type) echo 'selected="selected"';?> ><?php echo $service_type; ?></option>
						<?php }?>
					</select>
				</div>
                   
			</div>
				
           <div class="form-group">     
                <label class="control-label col-md-2 col-sm-3 col-xs-12">Supplier</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" class="form-control data-search-field" name="supplier" id="supplier" value="<?php echo $_SESSION['search_c_reconcilliation']['s_supplier'];?>"> 
				</div>	
                
                
                <label class="control-label col-md-1 col-sm-3 col-xs-12">Currency</label>
				<div class="col-md-3 col-sm-8 col-xs-12">
					<select name="currency_id" id="currency_id" class="form-control data-search-field">
						<option value="">Select</option>
						<?php for($i=0; $i<count($currency_data); $i++){?>										
						<option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if($_SESSION['search_c_reconcilliation']['s_currency_id']==$currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name']; ?></option>					
						<?php }?>
					</select>
				</div>        
                
                							
				<div class="searchBar">
					<input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                    <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info" >
                    
                    <div class="btn-group">
                                <button type="button" class="btn btn-dark  dropdown-toggle" data-toggle="dropdown" value="Export" name="search_export" id="search_export">
                                Export <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
								<?php
                                $export_format_array = get_export_formats();
                                foreach($export_format_array as $export_format)
                                {
									if($export_format=='pdf')
										continue;
                                    echo '<li><a href="'.base_url().'reports/supplier_detail_audit/export/'.param_encrypt($export_format).'">'.strtoupper($export_format).'</a></li>';
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
    	<div class="row">  
        <?php		
		 dispay_pagination_row($total_records, $_SESSION['search_c_reconcilliation']['s_no_of_records'], $pagination);
		?>                    
       </div>  
        <div class="x_content">
            <div class="table-responsive">
                <table id="table-sort" class="table table-striped jambo_table bulk_action table-bordered">       
                <thead>
                    <tr class="headings thc">                            
                        <th class="column-title text-center"><nobr>Supplier</nobr></th>
                        <th class="column-title text-center"><nobr>Supplier Reference</nobr></th>
                        <th class="column-title text-center"><nobr>Service Type</nobr></th>
                        <th class="column-title text-center"><nobr>Status</nobr></th>                           
                        <th class="column-title text-center"><nobr>Microtalk Reference</nobr></th>
                        <th class="column-title text-center"><nobr>Start Date</nobr></th>					
                        <th class="column-title text-center"><nobr>End Date</nobr></th>
                        <th class="column-title text-center"><nobr>Quantity</nobr></th>
                        <th class="column-title text-center"><nobr>Unit</nobr></th> 
                        <th class="column-title text-center"><nobr>One-Off Charges</nobr></th>
                        <th class="column-title text-center"><nobr>Monthly Charges</nobr></th>
                        <th class="column-title text-center"><nobr>Usage Charge</nobr></th>   
                        <th class="column-title text-center"><nobr>Currency</nobr></th>                  
                    </tr>
                </thead>	
                <tfoot>
                    <tr class="headings thc">                           
                        <th class="column-title text-center"><nobr>Supplier</nobr></th>
                        <th class="column-title text-center"><nobr>Supplier Reference</nobr></th>
                        <th class="column-title text-center"><nobr>Service Type</nobr></th>
                        <th class="column-title text-center"><nobr>Status</nobr></th>                           
                        <th class="column-title text-center"><nobr>Microtalk Reference</nobr></th>
                        <th class="column-title text-center"><nobr>Start Date</nobr></th>					
                        <th class="column-title text-center"><nobr>End Date</nobr></th>
                        <th class="column-title text-center"><nobr>Quantity</nobr></th>                        
                        <th class="column-title text-center"><nobr>Unit</nobr></th> 
                        <th class="column-title text-center"><nobr>One-Off Charges</nobr></th>
                        <th class="column-title text-center"><nobr>Monthly Charges</nobr></th>
                        <th class="column-title text-center"><nobr>Usage Charge</nobr></th>
                        <th class="column-title text-center"><nobr>Currency</nobr></th>                     
                    </tr>
                </tfoot>			
                <tbody>
                    <?php					 
                    if(count($report_data['result']) > 0)
                    {								
                        foreach($report_data['result'] as $supplier_data)
                        {	
                            $display_start_date = date(DATE_FORMAT_1,strtotime($supplier_data['start_date']));
                            $display_end_date = date(DATE_FORMAT_1,strtotime($supplier_data['end_date']));
							
							$currency_id = $supplier_data['currency_id'];
							
							$currency_name = '';
							if(isset($currency_array[$currency_id]))
								$currency_name = $currency_array[$currency_id];
								
							//$one_Off_charge = round($supplier_data['one_Off_charge'], 4);
							//$monthly_charge = round($supplier_data['monthly_charge'], 4);
							//$usage_charge = $supplier_data['usage_charge'].'<br>'.round($supplier_data['usage_charge'], 4);
							
							
							$one_Off_charge = number_format($supplier_data['one_Off_charge'], 4, '.', '');
							$monthly_charge = number_format($supplier_data['monthly_charge'], 4, '.', '');
							$usage_charge = number_format($supplier_data['usage_charge'], 4, '.', '');	
							
							
							$quantity = $supplier_data['quantity'];
							//if(strtolower($supplier_data['from_query'])=='connected_calls_in' || strtolower($supplier_data['from_query'])=='connected_calls_out')
							
							if($supplier_data['otherunit']=='MINUTES')
							{
								$quantity=round($supplier_data['quantity']/60,2);
							}
                        ?>
                            <tr>
                                <td class="text-center"><nobr><?php echo $supplier_data['supplier_name'];?></nobr>
                                <input type="hidden" name="table_from" value="<?php echo $supplier_data['from_query'];?>" />
                                </td>
                                <td class="text-center"><nobr><?php echo $supplier_data['supplier_reference'];?></nobr></td>
                                <td class="text-center"><?php echo $supplier_data['service_type'];?></td>
                                <td class="text-center"><?php echo $supplier_data['service_status'];?></td>
                                <td class="text-center"><?php echo $supplier_data['system_reference'];?></td>
                                <td class="text-center"><?php echo $display_start_date;?></td>
                                <td class="text-center"><?php echo $display_end_date;?></td>
                                <td class="text-center"><?php echo $quantity;?></td>                                
                                <td class="text-center"><?php echo $supplier_data['otherunit'];?></td>
                                <td class="text-right"><?php echo $one_Off_charge;?></td>
                                <td class="text-right"><?php echo $monthly_charge;?></td>
                                <td class="text-right"><?php echo $usage_charge;?></td>
                                <td class="text-center"><?php echo $currency_name;?></td>
                             </tr>
    
                    <?php
                        }                        
                    }
                    else
                    {
                    ?>
                    <tr>
                        <td colspan="18" align="center"><strong>No Record Found</strong></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
                                
            </div>			
        </div>
        
        <br />
	  <?php 
        echo '<div class="btn-toolbar" role="toolbar">
                      <div class="btn-group col-md-5 col-sm-12 col-xs-12">';							 
                 echo '</div>						
                      <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">
                               '.$pagination.'
                      </div>
              </div>';
        ?>
    </div>          
</div>      
<style type="text/css">

.fixedHeader-floating{position:fixed;}

table.jambo_table tfoot {
    background: rgba(52,73,94,.94);
    color: #ECF0F1;
}
</style>
<script>
$(document).ready(function() {

	 $("#record_date").daterangepicker({
            timePicker: 0,
            timePickerIncrement: 5,
            locale: {
                format: "YYYY-MM-DD HH:mm"
            },
            timePicker24Hour: true,
            ranges: {
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
             
            }
        });
		
		$("#record_date").val("<?php echo $_SESSION['search_c_reconcilliation']['s_record_date']?>");

	showDatatable('table-sort', [], [ 0,"asc" ] );
	
	$('#OkFilter').click(function(){
			var no_of_records=$('#no_of_records').val();				
		 	$('#no_of_rows').val(no_of_records);			
		 });
});
</script> 