<?php
$dp = 2;
?>
<div class="container-fluid">
    <div class="block-header">
        <h2>Paypal Payment</h2>
        <ul class="nav navbar-right panel_toolbox">

        </ul>
    </div>
    <div class="col-sm-12">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">
                    <div id="st-message" class=" fade in"  ></div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-left">
                        <h5>Make a payment using Paypal Payment Gateway. Portal is not capturing the credentials, card , your payment gateway detail. </h5>  
                                         
                                  </div>
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-left">
                     Your available account balance is <?php echo number_format(-$account_result['balance']['balance'], $dp, '.', '') ." ". $account_result['currency']['name'] . " (" . $account_result['currency']['symbol'] . ")"; ?>    <br>   <br> </div>
                    <form action="" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="action" value="OkPay"> 
                        <input type="hidden" name="method" id="method" value="paypal" />
                        <div class="form-group">
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
                            <div class="col-md-7 col-sm-6 col-xs-12 ">			
                                <button type="button" id="btnSave" class="btn btn-primary btn-lg active btn-block"><strong>Make Payment</strong></button>
                            </div>
                        </div>  

                    </form>           



                </div>
            </div>
        </div>    



        <div class="clearfix"></div>		

    </div>
</div>  
</div>

<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<script src="<?php echo base_url() ?>theme/default/js/parsley-custom.js"></script>