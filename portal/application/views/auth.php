<!--
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019-2020 Chinna Technologies   
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0.3
// License https://www.gnu.org/licenses/agpl-3.0.html
//
//
// The Initial Developer of the Original Code is
// Anand Kumar <kanand81@gmail.com> & Seema Anand <openvoips@gmail.com>
// Portions created by the Initial Developer are Copyright (C)
// the Initial Developer. All Rights Reserved.
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
-->
<!DOCTYPE html>
<!--Please use another username and value reset with original value.-->
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?php echo "OV500 Billing & Routing Software"; ?></title>
        <meta name="description" content="OV500 Billing & Routing Software">
        <meta name="author" content="Chinna Technologies">

        <!-- Bootstrap -->
        <link href="<?php echo base_url() ?>theme/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
            <!-- <link href="<?php echo base_url() ?>theme/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">-->
        <!-- NProgress -->
        <!--<link href="<?php echo base_url() ?>theme/vendors/nprogress/nprogress.css" rel="stylesheet">-->
        <!-- Animate.css -->
        <link href="<?php echo base_url() ?>theme/vendors/animate.css/animate.min.css" rel="stylesheet">
        <!-- Custom Theme Style -->
        <link href="<?php echo base_url() ?>theme/default/css/custom.css" rel="stylesheet">
        <style>.login{ background-color:#ffffff;}.logo{ margin:0px 0px 10px 70px;}</style>
    </head>
    <style type="text/css">
    .login_wrapper {
    max-width: 400px;
    }
.container {
  width: 27em;
  padding: 1em 2em;
  /*
  background-color: #ffffff;
  position: absolute;
  transform: translate(-50%, -50%);
  top: 50%;
  left: 50%;
  */


}
.inputfield {
  width: 100%;
  display: flex;
  justify-content: space-around;
}
.input {
  height: 3em;
  width: 3em;
  border: 2px solid #dad9df;
  outline: none;
  text-align: center;
  font-size: 1.5em;
  border-radius: 0.3em;
  background-color: #ffffff;
  outline: none;
  /*Hide number field arrows*/
  -moz-appearance: textfield;
}
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.input:disabled {
  color: #89888b;
}
.input:focus {
  border: 3px solid #ffb800;
}
</style>
    <body class="login">
        <?php
        $logo = 'logo.png';
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

                    <div class="ovlogo">
                    <?php 					
					$logo_url = get_logo();
					echo '<img class="rounded" style="max-width: 300px;display: block; margin-left: auto; margin-right: auto;" src="'.$logo_url.'">';
					?>
                    </div>
                    <div class="row" style="margin-bottom:10px;">OTP sent to your email address ('<?php echo $session_user_mailid;?>') </div>
                    <section class="login_content">

                        <div class="container">
                            <div class="inputfield">
                            <input type="number" maxlength="1" class="input"  />
                            <input type="number" maxlength="1" class="input"  />
                            <input type="number" maxlength="1" class="input"  />
                            <input type="number" maxlength="1" class="input"  />
                            </div>

                            <button class="btn btn-primary hide" id="submit" onclick=validateOTP() >Validate</button>
                        </div>

                    

                        <form class="form with-margin" name="login-form" id="login-form" method="post" action="<?php echo site_url('login/auth'); ?>" style="margin:0px;">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="code" id="code" value="">
                        </form>

                     
                    </section>
                </div>

           
            </div>
        </div>



        <script src="<?php echo base_url() ?>theme/vendors/jquery/dist/jquery.min.js"></script>

        <script>
    //Initial references
const input = document.querySelectorAll(".input");
const inputField = document.querySelector(".inputfield");
const submitButton = document.getElementById("submit");
let inputCount = 0,
  finalInput = "";

//Update input
const updateInputConfig = (element, disabledStatus) => {
  //element.disabled = disabledStatus;
  if ( typeof(element) !== "undefined" && element !== null ) {
    if (!disabledStatus) {
        element.focus();
    } else {
        element.blur();
    }
    }
};

input.forEach((element) => {
  element.addEventListener("keyup", (e) => {
    e.target.value = e.target.value.replace(/[^0-9]/g, "");
    let { value } = e.target;
    
    if (value.length == 1) {
      updateInputConfig(e.target, true);
      if (inputCount <= 3 && e.key != "Backspace") {
        finalInput += value;
        if (inputCount < 3) {
          updateInputConfig(e.target.nextElementSibling, false);
        }
      }
      inputCount += 1;
    } else if (value.length == 0 && e.key == "Backspace") {
      finalInput = finalInput.substring(0, finalInput.length - 1);
      if (inputCount == 0) {
        updateInputConfig(e.target, false);
        return false;
      }
      updateInputConfig(e.target, true);
      e.target.previousElementSibling.value = "";
      updateInputConfig(e.target.previousElementSibling, false);
      inputCount -= 1;
    } else if (value.length > 1) {
      e.target.value = value.split("")[0];
    }
    submitButton.classList.add("hide");
  });
});

window.addEventListener("keyup", (e) => {
  if (inputCount > 3) {
    submitButton.classList.remove("hide");
    submitButton.classList.add("show");
    if (e.key == "Backspace") {
      finalInput = finalInput.substring(0, finalInput.length - 1);
      updateInputConfig(inputField.lastElementChild, false);
      inputField.lastElementChild.value = "";
      inputCount -= 1;
      submitButton.classList.add("hide");
    }
  }
});

const validateOTP = () => {
  var error_message = '';
  var code='';
  input.forEach((element) => {
    code += element.value;;
  });
  if (code.length < 4) {
    error_message = 'Please enter code';
  }

  if (error_message != '')
  {
    //event.preventDefault();
   // console.log(error_message);
    alert(error_message);
  }
  else
  {
    $('#code').val(code);
    $('#login-form').submit() ;
  }
 

};

//Start
const startInput = () => {
  inputCount = 0;
  finalInput = "";
  input.forEach((element) => {
    element.value = "";
  });
  updateInputConfig(inputField.firstElementChild, false);
};

window.onload = startInput();



</script>



    </body>
</html>
