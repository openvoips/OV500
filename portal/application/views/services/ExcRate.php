<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>



<div class="container-fluid">
    <div class="block-header">
        <h2>Currency Exchange Rate(ADD) Config</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url('currency') ?>"><button class="btn btn-primary" type="button" >Back to Currency Exchange Rate Listing Page</button></a> </li>
        </ul>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">


                    <form action="<?php echo base_url('currency/ExcRate'); ?>" method="post" name="add_form" id="add_form" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" name="button_action" id="button_action" value="">
                        <input type="hidden" name="action" value="OkSaveData">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <label class="  col-md-12 col-sm-12 col-xs-12"><p>The Base Currency is the united states dollar ($) and add the exchange rate from USD ($) to the selected currency.</p>
                                <p>System suggesting to using any one currency conversion site to get the conversion rate from USD to any other currency with you want to support in the system. In any case, if currency conversion configuration is not available in the system, then the system will do the billing / call cost call calculation in the one is to one ratio. You can try to use  <a href="https://www.xe.com/currencyconverter/convert/?Amount=1&From=USD&To=INR" target="_blank"> https://www.xe.com/currencyconverter </a>  website for finding the conversion rate.  </p>

                                <p>The system recommends updating the currency conversion in the system regularly to avoid the wrong profit calculation.</p>
                            </label>  </div>

                        <div class="form-group">


                            <label class="control-label col-md-2 col-sm-4 col-xs-12">Currency conversion form USD ($) to </label>
                            <div class="col-md-4 col-sm-9 col-xs-12">
                                <select name="currency" id="currency" class="form-control data-search-field combobox">
                                    <option value="">Select Currency</option>
                                    <?php
                                    for ($i = 0; $i < $currency_dropdown['total']; $i++) {
                                        ?>
                                        <option value="<?php echo $currency_dropdown['result'][$i]['currency_id']; ?>" <?php if ($_SESSION['search_currency_data']['s_currency_id'] == $currency_dropdown['result'][$i]['currency_id']) echo 'selected'; ?>><?php echo $currency_dropdown['result'][$i]['symbol'] . ' - ' . $currency_dropdown['result'][$i]['name']; ?></option>
                                    <?php } ?>                         
                                </select>

                            </div>    
                        </div>


                        <div class="form-group">
                            <label class="control-label col-md-2 col-sm-4 col-xs-12" for="first-name">Exchange Rate<span class="required">*</span></label>
                            <div class="col-md-4 col-sm-9 col-xs-12">
                                <input type="text" name="exc_rate" id="frm_name" value="<?php echo set_value('exc_rate'); ?>"  data-parsley-required="" data-parsley-length="[1, 10]" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">

                                <button type="button" id="btnSave" class="btn btn-success">Save exchange rate</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="block-header">
        <h2>Currency Exchange Rate(ADD) Config</h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a href="<?php echo base_url('currency') ?>"><button class="btn btn-primary" type="button" >Back to Currency Exchange Rate Listing Page</button></a> </li>
        </ul>
    </div>

</div>    
<script>
    $('#btnSave, #btnSaveClose').click(function () {
        var is_ok = $("#add_form").parsley().isValid();
        if (is_ok === true)
        {
            var clicked_button_id = this.id;
            if (clicked_button_id == 'btnSaveClose')
                $('#button_action').val('save_close');
            else
                $('#button_action').val('save');

            $("#add_form").submit();
        } else
        {
            $('#add_form').parsley().validate();
        }
    })
</script>