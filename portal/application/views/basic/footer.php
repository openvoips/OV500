<div class="clearfix"></div>
</div><!-- <div class="right_col" role="main">-->

<!-- footer content -->
<footer>
    <div class="pull-right">
       Copyright©<?php echo date('Y'); ?> <a href="https://ov500.openvoips.org" target="_blank">Billing & Switching Software</a>. All Rights Reserved  
    </div>
    <div class="clearfix"></div>
</footer>
<!-- /footer content -->
</div>
</div>

<!-- div to open modal--->
<div id="idMyModal"></div>  
<div class="overlay-img hide col-md-12 col-sm-12 col-xs-12" id="overlay-img-id" style="text-align: center;top: 320px;position: absolute;">
    <img src="<?php echo base_url() ?>/theme/default/images/processing.gif" align="middle" >
</div>


<!--<script src="<?php echo base_url() ?>theme/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>-->

<script src="<?php echo base_url() ?>theme/vendors/moment/min/moment.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/moment/min/moment-timezone-with-data.js"></script>
<script>
    moment.tz.setDefault('<?php echo MOMENT_TIMEZONE; ?>');
</script>
<link href="<?php echo base_url() ?>theme/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet"/>
<script src="<?php echo base_url() ?>theme/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="<?php echo base_url() ?>theme/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
<link href="<?php echo base_url() ?>theme/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet"/>

<script src="<?php echo base_url() ?>theme/default/js/custom.js?v=1"></script>
</body>
</html>