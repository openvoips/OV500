<?php
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

//echo '<pre>';
//print_r($data);
//echo '</pre>';
?>

<script src="<?php echo base_url() ?>theme/vendors/parsleyjs/dist/parsley.min.js"></script>
<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="col-md-12 col-sm-12 col-xs-12 right">      
        <div class="x_title">
            <h2>Invoice Configuration Management</h2>
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="x_panel">
        <div class="x_content">
            <form class="block-content form-horizontal " id="add_form" name="add_form" ction="<?php echo base_url(); ?>sysconfig/pGConfig" method="post" enctype="multipart/form-data" data-parsley-validate>
                <input type="hidden" name="action" value="OkSaveData">
                <input type="hidden" name="data[account_id]" value="<?php echo get_logged_account_id();?>">
                <input type="hidden" name="existing_invoicelogo" value="<?php echo $data['logo'];?>">
                <div class="form-group" id="uploadFile" >
                    <label class="control-label col-md-4 col-sm-3 col-xs-12">Invoice Logo</label>
                    <div class="col-md-5 col-sm-6 col-xs-6">
                        <input type="file" id="invoicelogo" name="invoicelogo" size="50" />
                        <small>Invoice Logo Image (in 300px) and it will be in same size in Invoice</small>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-6">
                    <?php
					$upload_path = 'uploads/invoicelogo';
					$file_path=$upload_path.'/'.$data['logo'];
					if($data['logo']!='' && file_exists($file_path))
					{
						$image_html = '<img src="'.base_url().$file_path.'" width="50" />';
                       echo $image_html;
					}
					?>
                    </div>
                </div>
                

                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" >Business name / Company name<span class="required">*</span></label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <input type="text" name="data[company_name]" id="company_name" value="<?php echo $data['company_name']; ?>" class="form-control col-md-7 col-xs-12 " data-parsley-required="">
                    </div>
                </div>



                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" >Business / Company Address<span class="required">*</span></label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <textarea name="data[address]" id="address" class="form-control col-md-7 col-xs-12"  data-parsley-required=""><?php echo $data['address']; ?></textarea>
                    </div>
                </div>


                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" >Business / Company Bank Account Detail where want to recive Payment<span class="required">*</span></label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <textarea name="data[bank_detail]" id="bank_detail" class="form-control col-md-7 col-xs-12"  data-parsley-required=""><?php echo $data['bank_detail']; ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" >Customer / Billing Support Detail In invoice<span class="required">*</span></label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <textarea name="data[support_text]" id="support_text" class="form-control col-md-7 col-xs-12"  data-parsley-required=""><?php echo $data['support_text']; ?></textarea>
                    </div>
                </div>


                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" >Invoice Footer Text<span class="required">*</span></label>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <textarea name="data[footer_text]" id="footer_text" class="form-control col-md-7 col-xs-12"  data-parsley-required=""><?php echo $data['footer_text']; ?></textarea>
                    </div>
                </div>



                <div class="form-group">
                    <label class="control-label col-md-4 col-sm-3 col-xs-12" ></label>
                    <div class="col-md-8 col-sm-6 col-xs-12 searchBar ">
                        <button type="button" id="btnSave" class="btn btn-success">Save Invoice Configuration</button>
                    </div>
                </div>
            </form>

            <div class="clearfix"></div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="ln_solid"></div>
        <div class="x_title">
            <h2>Invoice Configuration Management</h2>
            <div class="clearfix"></div>
        </div>

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
<?php // echo '<pre>';print_r($data);echo '</pre>';?>