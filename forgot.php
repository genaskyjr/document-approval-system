<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR Forgot Password</title>

  <!-- Bootstrap CSS -->
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Css -->
  <link href="css/login.css" rel="stylesheet">
</head>
<body>
  <div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">

      <img
        src="images/ATS-PH-LOGO.png"
        alt="Aehr Test Systems Logo"
        class=" mb-3 mt-3 mx-auto d-block"
        style="width: 100%; max-width: 200px;"
      >

      
      <h1 class="text-center mb-3 fs-5">Forgot password</h1>
      

      <form id="loginForm" method="post" onsubmit="submitForm()">
        <div class="mb-3">
          <input required placeholder="Email Address" type="text" class="form-control" id="email" name="email"  autocomplete="off">
        </div>

        <div class="mb-3">
          <input placeholder="Code" type="text" class="form-control d-none" id="code" name="code"  autocomplete="off">
        </div>

        <div class="mb-3">
          <input  placeholder="New password" type="password" class="d-none form-control" id="password" name="password"  autocomplete="off">
        </div>

        <div class="mb-3">
          <input  placeholder="Confirm-New Password" type="password" class="d-none form-control" id="password1" name="password1"  autocomplete="off">
        </div>


        <div class="alert d-none" id="alert"></div>
    
        <div class="mb-3">
          <!-- Specify type="submit" for accessibility -->
          <button type="submit" class="btn btn-block" id="loginBtn">Forgot</button>
          <button onclick="submitCode()" class="btn btn-block d-none" id="loginBtn1">Confirm Code</button>
          <button onclick="submitnewpassword()" class="btn btn-block d-none" id="loginBtn2">Change Password</button>
        </div>
      </form>

    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <!-- Bootstrap JavaScript -->
  <script src="bootstrap/js/bootstrap.min.js"></script>

  <script>

var $alert = $("#alert");
var $code = $("#code");
var $email_input = $("#email");

var $forgot = $("#loginBtn");
var $forgot1 = $("#loginBtn1");
var $forgot2 = $("#loginBtn2");


var $password = $("#password");
var $password1 = $("#password1");




function submitnewpassword(){
  event.preventDefault();
  var email = $("#email").val();
  var password = $("#password").val();
  var password1 = $("#password1").val();

  console.log(email);
  console.log(password)
  console.log(password1)

    $.ajax({
        type: "POST",
        url: "backend/reset-password.php",
        data: {
          email: email,
          password: password,
          password1: password1
        }, 
        dataType: "json", // Specify that the expected response is JSON
        success: function(response) {
          console.log(response);
          $alert.addClass("d-none").removeClass("alert-danger alert-success");
          if (response.status == 1) {
                //add the input for password and confirm 
                // and remove the email and code input
                console.log(response.message);
                $alert.text(response.message).addClass("alert-success").removeClass("d-none");

                setTimeout(function() {
                        window.location.href = "index.php";
                }, 3000);

          } else {
            console.log('invalid code');
            console.log(response.message);
            $alert.text(response.message).addClass("alert-danger").removeClass("d-none");
          }

        },
        error: function(xhr, status, error) {
          // Handle AJAX errors
          console.error(xhr.responseText);
          // You can display an error message or take other actions here
        }
      });

}



function submitCode(){
    event.preventDefault();
    console.log("clicked " + $("#code").val());

    var code = $("#code").val();
    var email = $("#email").val();
    
    $.ajax({
        type: "POST",
        url: "backend/forgot-code.php",
        data: {code: code, email: email}, 
        dataType: "json", // Specify that the expected response is JSON
        success: function(response) {
          console.log(response);
          $alert.addClass("d-none").removeClass("alert-danger alert-success");
          if (response.status == 1) {
                //add the input for password and confirm 
                // and remove the email and code input
                console.log('add form for new password');
                console.log(response.message);
                $alert.text(response.message).addClass("alert-success").removeClass("d-none");

                //remove code
                $code.addClass('d-none');
                $password.removeClass('d-none');
                $password1.removeClass('d-none');
                $forgot2.removeClass('d-none');
                $forgot1.addClass('d-none');

              

          } else {
            console.log('invalid code');
            console.log(response.message);
            $alert.text(response.message).addClass("alert-danger").removeClass("d-none");
          }

        },
        error: function(xhr, status, error) {
          // Handle AJAX errors
          console.error(xhr.responseText);
          // You can display an error message or take other actions here
        }
      });



}

    function submitForm() {
      event.preventDefault();

      var email = $("#email").val();
      
      console.log(email);

      $.ajax({
        type: "POST",
        url: "backend/forgot.php",
        data: {email: email}, 
        dataType: "json", // Specify that the expected response is JSON
        success: function(response) {
          console.log(response);


          $alert.addClass("d-none").removeClass("alert-danger alert-success");

          if (response.status == 1) {
            console.log("Success response received.");
            console.log("Message: " + response.message);
            $alert.text(response.message).addClass("alert-success").removeClass("d-none");

            //add the div 
            $code.removeClass('d-none');
            $email_input.removeAttr('required');
            $email_input.prop('readonly', true);
    
            $forgot.addClass('d-none');
            $forgot1.removeClass('d-none');

            $email_input.addClass('d-none');


          } else {
            console.log("Error response received.");
            console.log("Message: " + response.message);
            $alert.text(response.message).addClass("alert-danger").removeClass("d-none");
          }
        },
        error: function(xhr, status, error) {
          // Handle AJAX errors
          console.error(xhr.responseText);
          // You can display an error message or take other actions here
        }
      });
    }
</script>

</body>
</html>
