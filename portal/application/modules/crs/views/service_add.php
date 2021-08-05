<?php

$section_array=array();
$section_array['1']=array('title'=>'Tariff','file_name'=>'inner/add_tariff.php');
$section_array['2']=array('title'=>'Bundle & Package','file_name'=>'inner/add_bundle.php');
$section_array['3']=array('title'=>'Plan Item','file_name'=>'inner/add_item.php');
$section_array['4']=array('title'=>'Dialplan','file_name'=>'inner/add_dialplan.php');
$section_array['5']=array('title'=>'Invoice','file_name'=>'inner/add_invoice.php');

$section_total = count($section_array);
$active_tab = (int)$active_tab;
if($active_tab==0)
	$active_tab=1;
	
?>
<link href="<?php echo base_url(); ?>theme/default/css/tabs.css" rel="stylesheet" type="text/css"/>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Service <span class="text-right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $accountinfo['company_name'].' ('.$accountinfo['account_id'].') '.$accountinfo['account_type'];?></span></h2><div class="clearfix"></div>
            <div class="ln_solid"></div>
            
            <ul class="nav nav-tabs bar_tabs2" id="myTab" role="tablist">
            <?php foreach($section_array as $key=>$section_single_array){ 
			$class='';
			if($key==$active_tab)
				$class='active';
			echo '<li class="nav-item header_section '.$class.'" id="id_header_section_'.$key.'">';
			echo '<a class="nav-link " id="contact-tab" data-toggle="tab" href="#contact" role="tab"  onclick="show_section(\''.$key.'\')">'.$section_single_array['title'].'</a>';
			echo '</li>';
			}?>
            </ul>
            <div class="clearfix" ></div>
        </div>

	<?php
	foreach($section_array as $key=>$section_single_array){
		
		if($key==$active_tab)
			$class='';
		else
			$class='hide';	
		?>
        <div class="x_content content_div <?php echo $class;?>" id="<?php echo 'id_content_div_'.$key;?>">
         
        <?php  
			include($section_single_array['file_name']);

		?>
        </div>
        
   <?php }?>     
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <!--<h2>Customer Account Configuration Management</h2>-->
            <ul class="nav navbar-right panel_toolbox">             
                <li> <a href="<?php echo site_url('crs/assignvoip') ?>"><button class="btn btn-danger" type="button">Back to Listing Page</button></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>

    </div>
</div><?php //ddd($accountinfo);?>
<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>
<script>
var section_total = "<?php echo $section_total;?>";
function show_section(key)
{
	/*key = 1;	
	var id_header_section = 'id_header_section_'+key;
	var id_content_div = 'id_content_div_'+key;
	$('.content_div').addClass('hide');
	$('#'+id_content_div).removeClass('hide');
	///////////////
	$('.header_section').removeClass('active');
	$('#'+id_header_section).addClass('active');*/
		
}

function save_button(key)
{
	var form_name = 'tab_form_'+key;
	var is_ok = $("#"+form_name).parsley().isValid();
	if (is_ok === true)
	{			
		$("#"+form_name).submit();		
	} else
	{
		$('#'+form_name).parsley().validate();
	}
}
</script>