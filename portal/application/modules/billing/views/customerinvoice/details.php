<?php
if (check_logged_user_group(array('CUSTOMER'))) {
    $title = 'Invoice';
} else {
    $title = 'Customer Invoice';
}
?>


<div class="container-fluid">
    <div class="block-header">
        <h2><?php echo $title; ?></h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url('Billing/customerinvoice') ?>"><button class="btn btn-danger" type="button" >Back to <?php echo $title; ?> Listing Page</button></a> </li>
            <li><a href="<?php echo base_url('Billing/customerinvoicedownload/' . param_encrypt($customerinvoice_data['invoice_id'])) ?>" target="_blank"><button class="btn btn-success" type="button" >Download</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">

<?php $this->load->view('invoicetemplate/template1'); ?>




                    <br />

                </div>


            </div>
        </div>
    </div>
    <div class="block-header">
        <h2><?php echo $title; ?></h2>
        <ul class="nav navbar-right panel_toolbox">           
            <li><a href="<?php echo base_url('Billing/customerinvoicedownload/' . param_encrypt($customerinvoice_data['invoice_id'])) ?>" target="_blank"><button class="btn btn-success" type="button" >Download</button></a> </li>
        </ul>
    </div>

</div>
