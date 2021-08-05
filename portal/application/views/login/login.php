<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>i-Switch</title>

        <!-- Bootstrap -->
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <!--<link href="<?php echo base_url() ?>theme/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">-->
        <!-- NProgress -->
        <!--<link href="<?php echo base_url() ?>theme/vendors/nprogress/nprogress.css" rel="stylesheet">-->
        <!-- Animate.css -->
        <link href="<?php echo base_url() ?>theme/vendors/animate.css/animate.min.css" rel="stylesheet">

        <!-- Custom Theme Style -->
        <link href="<?php echo base_url() ?>theme/default/css/custom.min.css" rel="stylesheet">
        <style>.login{ background-color:#ffffff;}.logo{ margin:0px 0px 10px 70px;}</style>
    </head>

    <body class="login">
        <div>
            <a class="hiddenanchor" id="signup"></a>
            <a class="hiddenanchor" id="signin"></a>

            <div class="login_wrapper">
                <div class="animate form login_form">

                    <div class="logo">
                        <img src="<?php echo base_url() ?>theme/default/images/logo2.png" style="width: 200px;"/>
                    </div>
                    <section class="login_content">
                        <form class="form with-margin" name="login-form" id="login-form" method="post" action="<?php echo base_url(); ?>login/access">
                            <input type="hidden" name="action" value="login">
                            <div><input type="text" name="login" id="login" class="form-control" placeholder="Username" required=""  /></div>
                            <div><input type="password" name="pass" id="pass" class="form-control" placeholder="Password" required="" /></div>
                            <div><button type="submit" id="login_button" class="btn btn-primary">Login</button></div>

                            <div class="separator">
                                <div class="clearfix"></div>
                                <br />

                                <div>
                                    <p>Copyright @ Openvoips Technologies All Rights Reserved.</p>
                                    <p> By using this Portal you are agreeing to OV500 Terms of Use and Privacy <p>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>


            </div>
        </div>
    </body>
</html>