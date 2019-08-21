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
            <h2>Reporting : Supplier Summary Audit</h2>			
            <div class="clearfix"></div>			
        </div>
        <div class="x_content">
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url();?>reports/supplier_summary_audit">
			<input type="hidden" name="search_action" value="search" />
			<input type="hidden" name="no_of_rows" id="no_of_rows" value="" />		
            
            <div class="form-group">
           		<label class="control-label col-md-2 col-sm-4 col-xs-12">Record Date</label>
                <div class="col-md-4 col-sm-9 col-xs-12">
                    <input type="text" name="record_date" id="record_date" class="form-control " value="<?php if(isset($_SESSION['search_c_summary']['s_record_date'])) echo $_SESSION['search_c_summary']['s_record_date']; ?>" readonly="readonly" data-parsley-required="" />
                </div>   
                
                <label class="control-label col-md-2 col-sm-3 col-xs-12">Service Type</label>
				<div class="col-md-3 col-sm-8 col-xs-12">
					<?php
                    $service_type_array = array('DID','SERVER','VOIP');
                    ?>
					<select name="service_type" id="service_type" class="form-control data-search-field">	
                    	<option value="">Select</option>
						<?php foreach($service_type_array as $service_type){?>						
						<option value="<?php echo $service_type; ?>" <?php if($_SESSION['search_c_summary']['s_service_type']==$service_type) echo 'selected="selected"';?> ><?php echo $service_type; ?></option>
						<?php }?>
					</select>
				</div>
                   
			</div>
				
           <div class="form-group">     
                <label class="control-label col-md-2 col-sm-3 col-xs-12">Supplier</label>
				<div class="col-md-2 col-sm-9 col-xs-12">
				  	<input type="text" class="form-control data-search-field" name="supplier" id="supplier" value="<?php echo $_SESSION['search_c_summary']['s_supplier'];?>"> 
				</div>	
                
                
                <label class="control-label col-md-1 col-sm-3 col-xs-12">Currency</label>
				<div class="col-md-3 col-sm-8 col-xs-12">
					<select name="currency_id" id="currency_id" class="form-control data-search-field">
						<option value="">Select</option>
						<?php for($i=0; $i<count($currency_data); $i++){?>										
						<option value="<?php echo $currency_data[$i]['currency_id']; ?>" <?php if($_SESSION['search_c_summary']['s_currency_id']==$currency_data[$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_data[$i]['name']; ?></option>					
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
                                    echo '<li><a href="'.base_url().'reports/supplier_summary_audit/export/'.param_encrypt($export_format).'">'.strtoupper($export_format).'</a></li>';
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
		 dispay_pagination_row($total_records, $_SESSION['search_c_summary']['s_no_of_records'], $pagination);
		?>                    
       </div>  
        <div class="x_content">
            <div class="table-responsive">
                <table id="table-sort" class="table table-striped jambo_table bulk_action table-bordered">       
                <thead>
                    <tr class="headings thc">                            
                        <th class="column-title text-center"><nobr>Supplier</nobr></th>
                        <th class="column-title text-center"><nobr>Service Type</nobr></th>
                        <th class="column-title text-center"><nobr>Currency</nobr></th> 
                        <th class="column-title text-center"><nobr>Usage Units</nobr></th>                           
                        <th class="column-title text-center"><nobr>Mins In</nobr></th>
                        <th class="column-title text-center"><nobr>Mins Out</nobr></th>	
                        <th class="column-title text-center"><nobr>Mins Cost</nobr></th>		
                        <th class="column-title text-center"><nobr>Quantity</nobr></th>
                        <th class="column-title text-center"><nobr>One-Off Costs</nobr></th>
                        <th class="column-title text-center"><nobr>Monthly Costs</nobr></th>
                                          
                    </tr>
                </thead>	
                <tfoot>
                    <tr class="headings thc">                            
                        <th class="column-title text-center"><nobr>Supplier</nobr></th>
                        <th class="column-title text-center"><nobr>Service Type</nobr></th>
                        <th class="column-title text-center"><nobr>Currency</nobr></th>   
                        <th class="column-title text-center"><nobr>Usage Units</nobr></th>                        
                        <th class="column-title text-center"><nobr>Mins In</nobr></th>
                        <th class="column-title text-center"><nobr>Mins Out</nobr></th>	
                        <th class="column-title text-center"><nobr>Mins Cost</nobr></th>		
                        <th class="column-title text-center"><nobr>Quantity</nobr></th>
                        <th class="column-title text-center"><nobr>One-Off Costs</nobr></th>
                        <th class="column-title text-center"><nobr>Monthly Costs</nobr></th>           
                    </tr>
                </tfoot>			
                <tbody>
                    <?php					 
                    if(count($report_data['result']) > 0)
                    {								
                        foreach($report_data['result'] as $supplier_data)
                        {                          							
							$currency_id = $supplier_data['currency_id'];
							
							$currency_name = '';
							if(isset($currency_array[$currency_id]))
								$currency_name = $currency_array[$currency_id];
								
							$sum_in_minute=	round($supplier_data['sum_in_minute']/60,2);
							$sum_out_minute=round($supplier_data['sum_out_minute']/60,2);
							
							$sum_min_charge = number_format($supplier_data['sum_min_charge'], 4, '.', '');
							$sum_one_Off_charge = number_format($supplier_data['sum_one_Off_charge'], 4, '.', '');
							$sum_monthly_charge = number_format($supplier_data['sum_monthly_charge'], 4, '.', '');													
                        ?>
                            <tr>
                                <td class="text-center"><nobr><?php echo $supplier_data['supplier_name'];?></nobr></td>
                                <td class="text-center"><nobr><?php echo $supplier_data['service_type'];?></nobr></td>
                                <td class="text-center"><?php echo $currency_name;?></td>
                                <td class="text-center"><?php echo $supplier_data['otherunit'];?></td>
                                <td class="text-center"><?php echo $sum_in_minute;?></td>
                                <td class="text-center"><?php echo $sum_out_minute;?></td>
                                <td class="text-right"><?php echo $sum_min_charge;?></td>
                                <td class="text-center"><?php echo $supplier_data['sum_quantity'];?></td>
                                <td class="text-right"><?php echo $sum_one_Off_charge;?></td>
                                <td class="text-right"><?php echo $sum_monthly_charge;?></td>
                                
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
               
				'Today': [moment().startOf('days'), moment().endOf('days')],
                'Yesterday': [moment().subtract(1, 'days').startOf('days'), moment().subtract(1, 'days').endOf('days')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
             
            }
        });
		
		$("#record_date").val("<?php echo $_SESSION['search_c_summary']['s_record_date']?>");

	showDatatable('table-sort', [], [ 0,"asc" ] );
	
	$('#OkFilter').click(function(){
			var no_of_records=$('#no_of_records').val();				
		 	$('#no_of_rows').val(no_of_records);			
		 });
});
</script> 