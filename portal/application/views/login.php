<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <title><?php echo SITE_NAME; ?></title>
        <meta name="description" content="<?php echo SITE_FULL_NAME; ?>">
        <meta name="author" content="Chinna Technologies">


        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
        <link href="<?php echo base_url() ?>/theme/vendors/bootstrap/css/bootstrap.css" rel="stylesheet">
        <link href="<?php echo base_url() ?>/theme/vendors/node-waves/waves.css" rel="stylesheet" />
        <link href="<?php echo base_url() ?>/theme/vendors/animate-css/animate.css" rel="stylesheet" />
        <link href="<?php echo base_url() ?>/theme/css/style.css" rel="stylesheet">
    </head>

    <body class="login-page">
        <div class="login-box">
            <div class="logo">

                <h4 class="text-center"><?php echo SITE_FULL_NAME; ?></h4>
            </div>
            <div class="card">
                <div class="body">

                    <?php
                    if (isset($err_msgs) && $err_msgs != '') {
                        ?>

                        <div class="alert bg-red">
                            <?php echo $err_msgs; ?>
                        </div>
                        <?php
                    }
                    ?>



                    <form class="form with-margin" name="login-form" id="login-form" method="post" action="<?php echo base_url(); ?>login">
                        <input type="hidden" name="action" value="login">
                        <?php
                        if (isset($_REQUEST['redirect'])) {
                            ?>
                            <input type="hidden" name="redirect" id="redirect" value="<?php echo htmlspecialchars($_REQUEST['redirect']); ?>">
                        <?php }
                        ?>

                        <div class="msg p-t-20">Sign in to start your session</div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="material-icons">person</i>
                            </span>
                            <div class="form-line">
                                <input type="text" name="login" id="login" class="form-control" placeholder="Account Username" required  autofocus/>
                            </div>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="material-icons">lock</i>
                            </span>
                            <div class="form-line">
                                <input type="password" name="pass" id="pass" class="form-control" placeholder="Account Password" required />
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-xs-12">
                                <button type="submit" id="login_button" class="btn btn-block bg-pink btn-lg waves-effect">Login</button>
                            </div>
                        </div>
                        <div class="row m-t-15 m-b--20">

                            <div class="col-xs-12 align-center">
                                <p class="align-center">Copyright &copy; <?php echo date('Y'); ?> All Rights Reserved  </p>
                            </div>
                            <br><br>

                            <div class="col-xs-12 align-center">
                                <!-- <a href="forgot-password.html">Forgot Password?</a> -->
                            </div>
                        </div>
                    </form>


                </div>
            </div>
        </div>

        <!-- Jquery Core Js -->
        <script src="<?php echo base_url() ?>/theme/vendors/jquery/jquery.min.js"></script>
        <script src="<?php echo base_url() ?>/theme/vendors/bootstrap/js/bootstrap.js"></script>
        <script src="<?php echo base_url() ?>/theme/vendors/node-waves/waves.js"></script>
        <script src="<?php echo base_url() ?>theme/js/admin.js"></script>
    </body>

</html>