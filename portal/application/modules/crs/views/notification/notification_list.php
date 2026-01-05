<?php
$vatflag_array = array('NONE', 'TAX', 'VAT');
$section_array = array();
$section_array['1'] = array('title' => 'Login', 'file_name' => 'inner/login.php');
$section_array['2'] = array('title' => 'Contact Detail', 'file_name' => 'inner/registration.php');
$section_array['3'] = array('title' => 'Settings', 'file_name' => 'inner/settings.php');

$section_total = count($section_array);

$tab_index=0;
$active_tab = (int) $active_tab;
if ($active_tab == 0)
    $active_tab = 1;
?>
<?php 
$voip_section_array = array();
$voip_section_array['1'] = array('title' => 'Tariff', 'file_name' => 'inner/edit_tariff.php');
$voip_section_array['2'] = array('title' => 'Bundle', 'file_name' => 'inner/add_bundle.php');
$voip_section_array['4'] = array('title' => 'Dialplan', 'file_name' => 'inner/add_dialplan_' . strtolower($data['account_type']) . '.php');
$voip_section_array['5'] = array('title' => 'Bill Config', 'file_name' => 'inner/add_invoice.php');
if ($data['account_type'] == 'CUSTOMER') {
    $voip_section_array['6'] = array('title' => 'IP-Trunk', 'file_name' => 'inner/ip.php');
    $voip_section_array['7'] = array('title' => 'SIP-Truk', 'file_name' => 'inner/sip.php');
}
$voip_section_array['8'] = array('title' => 'CLI Rule', 'file_name' => 'inner/srcno.php');
$voip_section_array['9'] = array('title' => 'DST Rule', 'file_name' => 'inner/dstno.php');
$voip_section_array['10'] = array('title' => 'DID CLI Rule', 'file_name' => 'inner/didsrcno.php');
$voip_section_array['11'] = array('title' => 'DID Dial Rule', 'file_name' => 'inner/diddstno.php');

$notification_section_array['1'] = array('title' => 'Low Balance Notification', 'file_name' => 'inner/low_balance_notification.php');
?>
<link href="<?php echo base_url(); ?>theme/default/css/tabs.css" rel="stylesheet" type="text/css"/>
          
             
<div class="container-fluid">
    <div class="block-header">
        <h2>Low Balance Notification</h2>
        <ul class="nav navbar-right panel_toolbox">
           <li> <a href="<?php echo site_url('crs'); ?>"><button class="btn btn-primary" type="button">Back to Customer Listing Page</button></a></li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">
                     
                    
                    

            <div class="clearfix"></div>
            <div class="ln_solid"></div>

            <ul class="nav nav-tabs bar_tabs2" id="myTab" role="tablist">
				<?php
					$account_id_enc = param_encrypt($data['account_id']);
					$k=1;
					foreach ($voip_section_array as $key => $section_single_array) {
						$class = '';
						$link=site_url('crs/editvoip/'.$account_id_enc.'/'.$key);
						echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
						echo '<a class="nav-link "  href="'.$link.'"   >' . $section_single_array['title'] . '</a>';
						echo '</li>';
					}
					?>
                
                <?php
				
					$k=1;
					
                    foreach ($section_array as $key => $section_single_array) {
						$class = '';
						$link=site_url('crs/customers/edit/'.$account_id_enc.'/'.$key);
						echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
						echo '<a class="nav-link "  href="'.$link.'"   >' . $section_single_array['title'] . '</a>';
						echo '</li>';
					}
					?>
                    <?php
                foreach ($notification_section_array as $key => $section_single_array) {
                    $class = '';
                    if ($key == $active_tab)
                        $class = 'active';
                    echo '<li class="nav-item header_section ' . $class . '" id="id_header_section_' . $key . '">';
                    echo '<a class="nav-link " id="contact-tab" data-toggle="tab" href="#contact" role="tab"  onclick="show_section(\'' . $key . '\')">' . $section_single_array['title'] . '</a>';
                    echo '</li>';
                }
                ?>
            </ul>
            <div class="clearfix" ></div>
        </div>

        <?php

 
        foreach ($notification_section_array as $key => $section_single_array) {

            if ($key == $active_tab)
                $class = '';
            else
                $class = 'hide';
            ?>
            <div class="x_content content_div <?php echo $class; ?>" id="<?php echo 'id_content_div_' . $key; ?>">

                <?php
//                echo $section_single_array['file_name'];
               // include($section_single_array['file_name']);
                ?>    
                
<table class="table table-striped jambo_table table-bordered">
    <thead>
        <tr class="headings thc">
            <th class="column-title">Type</th>
            <th class="column-title">Trigger Value</th>
            <th class="column-title">Email Address</th>
            <th class="column-title">Status </th>
            <th class="column-title">Action </th>
        </tr>
    </thead>
    <tbody>
        <?php
        $notify_name_array = ['low-balance'=>'Low Balance','daily-balance'=>'Daily Balance'];
        if (count($notification_data) > 0) {
            foreach ($notification_data as $ip_data) {
                if ($ip_data['status'] == 'Y')
                    $status = '<span class="label label-success">Active</span>';
                else
                    $status = '<span class="label label-danger">Inactive</span>';

                    $notify_name_display= $notify_name=$ip_data['notify_name'];
                    if(isset($notify_name_array [$notify_name_display]))
                    $notify_name_display=$notify_name_array [$notify_name_display];
        ?>
                <tr>
                    <td><?php echo $notify_name_display; ?></td>
                    <td><?php echo $ip_data['notify_amount']; ?></td>
                    <td><?php echo $ip_data['notify_emails']; ?></td>
                    <td><?php echo $status; ?></td>
                    <td class=" last">
                        <a href="<?php echo base_url('crs/customers/notificationEdit'); ?>/<?php echo param_encrypt($ip_data['account_id']); ?>/<?php echo param_encrypt($ip_data['notification_id']); ?>/<?php echo $key ?>" title="Edit" class="edit"><i class="fa fa-pencil-square-o"></i></a>
                        <?php if (check_account_permission('customer', 'delete')): ?>
                            <a href="javascript:void(0);"
                                onclick=doConfirmDelete('<?php echo $ip_data['notification_id']; ?>',"",'account_ips_delete') title="Delete" class="delete"><i class="fa fa-trash"></i></a>
                        <?php endif; ?>

                    </td>
                </tr>

            <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="5" align="center"><strong>No Record Found</strong></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>
<div class="col-md-12 col-sm-12 col-xs-12 text-right">
    <a href="<?php echo base_url('crs/customers/notificationAdd'); ?>/<?php echo param_encrypt($data['account_id']); ?>/<?php echo $key ?>"><input type="button" value="Create" name="add_link" class="btn btn-primary"></a>
</div>

<br><br>


            </div>

        <?php } ?>     
    </div>
    <div class="block-header">
     <h2>Low Balance Notification</h2>
        <ul class="nav navbar-right panel_toolbox">
           <li> <a href="<?php echo site_url('crs'); ?>"><button class="btn btn-primary" type="button">Back to Customer Listing Page</button></a></li>
        </ul>
    </div>
</div>