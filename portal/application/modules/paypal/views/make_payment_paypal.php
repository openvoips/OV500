<?php
$dp = 2;
?>
<div class="container-fluid">
    <div class="block-header">
        <h2>Paypal Payment</h2>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">                   

                    <div class="row clearfix">
                    <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12" style="padding-top: 40px;">

                    
                    <div class="col-lg-10 col-md-10 col-sm-12 col-xs-12" style="padding-bottom: 40px;">
                        <h5 class="text-center">Paypal Payment Gateway</h5>
                        <p class="text-center text-muted">Make a payment using Paypal Payment Gateway. Portal is not capturing the credentials, card , your payment gateway detail. </p>  
                    </div>      
                    <form action="" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="action" value="OkPay"> 
                        <input type="hidden" name="method" id="method" value="paypal" />
                        <div class="form-group" style="padding-top: 20px;padding-bottom: 20px">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <input type="text" name="amount" id="amount" value="100.00"  class="form-control col-md-7 col-xs-12" data-parsley-required="" data-parsley-pattern="/^\d+(\.\d{1,<?php echo $dp; ?>})?$/" data-parsley-pattern-message="Positive number with maximum <?php echo $dp; ?> decimal"  >
                            </div>   
                            <div class="col-md-3 col-sm-3 col-xs-12">        
                                <?php echo $account_result['currency']['name'] . " (" . $account_result['currency']['symbol'] . ")"; ?> 
                            </div>
                        </div>  
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                            <div class="col-md-5 col-sm-6 col-xs-12 ">			
                                <button type="button" id="btnSave" class="btn btn-primary btn-lg active btn-block"><strong>Make Payment</strong></button>
                            </div>
                        </div>  

                    </form>  

                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12" style="margin-top: 40px;margin-bottom: 40px; padding-right:0px;">

                        <div class="col-lg-11 well" style="background-color: #f5f5f5;" >
                            <h5 class="text-left">Current Balance: <?php echo $account_result['currency']['symbol'].number_format(-$account_result['balance']['balance'], $dp, '.', ''); ?></h5>
                        </div>
                        <div class="col-lg-11 well" style="background-color: #f5f5f5;" >
                            <h5 class="text-center">Paypal Payment Gateway</h5>
                            <p class="text-center text-muted">Make a payment using Paypal Payment Gateway. Portal is not capturing the credentials, card , your payment gateway detail. </p>  
                                
                            <p><img src="https://rangers.co.th/wp-content/uploads/2018/09/paypal-logotype-png-510.png" class="img-responsive" ></p>
                        </div>
                    </div>
                    </div>



                </div>
            </div>
        </div>

    </div>

</div>


<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>