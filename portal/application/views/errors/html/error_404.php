<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?php echo $sitesetup_data->site_name; ?></title>
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Bootstrap -->
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
            <!-- <link href="<?php echo base_url() ?>theme/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">-->
        <!-- NProgress -->
        <!--<link href="<?php echo base_url() ?>theme/vendors/nprogress/nprogress.css" rel="stylesheet">-->
        <!-- Animate.css -->
        <link href="<?php echo base_url() ?>theme/vendors/animate.css/animate.min.css" rel="stylesheet">
        <!-- Custom Theme Style -->
        <link href="<?php echo base_url() ?>theme/default/css/custom.min.css" rel="stylesheet">

    </head>

    <body class="login">

        <?php
        if (isset($err_msgs) && $err_msgs != '') {
            ?><div class="alert alert-danger alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                </button>
                <?php echo $err_msgs; ?>
            </div>

        <?php }
        ?>
        <div>
            <a class="hiddenanchor" id="signup"></a>
            <a class="hiddenanchor" id="signin"></a>

            <div class="login_wrapper">
                <div class="animate form login_form">
                    <section class="login_content">
                        <form class="form with-margin" name="login-form" id="login-form" method="post" action="<?php echo base_url(); ?>login">
                            <input type="hidden" name="a" id="a" value="send">
                            <?php
// Check if a redirect page has been forwarded
                            if (isset($_REQUEST['redirect'])) {
                                ?><input type="hidden" name="redirect" id="redirect" value="<?php echo htmlspecialchars($_REQUEST['redirect']); ?>">
                            <?php }
                            ?>
                            <h1>Login </h1>
                            <div>
                                <input type="text" name="login" id="login" class="form-control" placeholder="Username" required=""  />
                            </div>
                            <div>
                                <input type="password" name="pass" id="pass" class="form-control" placeholder="Password" required="" />
                            </div>
                            <div>
                                <button type="submit" id="login_button" class="btn btn-primary">Login</button>
                            </div>

                            <div class="clearfix"></div>

                            <div class="separator">
                                <p class="change_link"><a href="#signup" class="to_register"> Forgotten Password </a>    </p>

                                <div class="clearfix"></div>
                                <br />

                                <div>
                                    <h1><i class="fa fa-paw"></i> <?php echo $sitesetup_data->site_name; ?></h1>
                                    <p>©<?php echo date('Y'); ?> All Rights Reserved. Privacy and Terms</p>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>

                <div id="register" class="animate form registration_form">
                    <section class="login_content">
                        <form>
                            <h1>Forgotten Password</h1>
                            <div>
                                <input type="text" class="form-control" placeholder="Username Or Email" required="" />
                            </div>

                            <div>
                                <button type="button" class="btn btn-primary">Submit</button>
                            </div>

                            <div class="clearfix"></div>

                            <div class="separator">
                                <p class="change_link">Already a member ?
                                    <a href="#signin" class="to_register"> Log in </a>
                                </p>

                                <div class="clearfix"></div>
                                <br />

                                <div>
                                    <h1><i class="fa fa-paw"></i> <?php echo $sitesetup_data->site_name; ?></h1>
                                    <p>©<?php echo date('Y'); ?> All Rights Reserved. Privacy and Terms</p>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>



    </body>
</html>