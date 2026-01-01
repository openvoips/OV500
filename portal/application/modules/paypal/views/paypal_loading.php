 <div class="container-fluid">
    <div class="block-header">
        <h2>Processing</h2>
        <ul class="nav navbar-right panel_toolbox">

        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">


                    <div class="col-md-12 col-sm-6 col-xs-12 text-center" id="search_loader" style="margin:0 auto; ">
                        <img src="<?php echo base_url(); ?>theme/default/images/loading.gif">
                    </div>


                    <form name="redirect" id="redirect" action="<?php echo $submit_url; ?>" method="post">   
                        <input type="hidden" name="business" value="<?php echo $pay_data_array['business']; ?>"> 
                        <input type="hidden" name="cmd" value="<?php echo $pay_data_array['cmd']; ?>">
                        <!--<input type="hidden" name="image_url" value="">-->

                        <input type="hidden" name="return" value="<?php echo $pay_data_array['return']; ?>">
                        <input type="hidden" name="notify_url" value="<?php echo $pay_data_array['notify_url']; ?>">
                        <input type="hidden" name="cancel_return" value="<?php echo $pay_data_array['cancel_return']; ?>" />            
                        <input type="hidden" name="rm" value=""<?php echo $pay_data_array['rm']; ?>"" />

                        <input type="hidden" name="item_name" value="<?php echo $pay_data_array['item_name']; ?>"> 
                        <input type="hidden" name="item_number" value="<?php echo $pay_data_array['item_number']; ?>"> 
                        <input type="hidden" name="no_shipping" value="<?php echo $pay_data_array['no_shipping']; ?>"> 
                        <input type="hidden" name="no_note" value="<?php echo $pay_data_array['no_note']; ?>">

                        <input type="hidden" name="amount" value="<?php echo $pay_data_array['amount']; ?>">                
                        <input type="hidden" name="currency_code" value="<?php echo $pay_data_array['currency_code']; ?>">

                        <input type="hidden" name="first_name" value="<?php echo $pay_data_array['payer_first_name']; ?>"> 
                        <input type="hidden" name="last_name" value="<?php echo $pay_data_array['payer_last_name']; ?>"> 
                        <input type="hidden" name="email" value="<?php echo $pay_data_array['payer_email']; ?>">                
                        <input type="hidden" name="custom" value="<?php echo $pay_data_array['custom']; ?>">

                    </form>
                    <script>
                        $('#redirect').submit();
                        //exit();
                    </script>

                </div>
            </div>      
        </div>
    </div>      
</div>