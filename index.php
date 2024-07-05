<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR Document Approval System - ATS Login</title>

  
 <meta name="description" content="AEHR Document Approval System (ATS) - A secure platform for efficient document management and approval. ATS login.">
<meta name="keywords" content="AEHR Document Approval System, ATS Login, Document Management, Approval System, Secure Login, ats ph das">

  
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR Login</title>
  
  
  

  <!-- Bootstrap CSS -->
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Css -->
  <link href="css/login.css" rel="stylesheet">
  




</head>
<body>
  <div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">

      <img
        src="/images/ATS-PH-LOGO.png"
        alt="Aehr Test Systems Logo"
        class=" mb-3 mt-3 mx-auto d-block"
        style="width: 100%; max-width: 200px;"
      >

      
      <h1 class="text-center mb-3 fs-5">Document Approval System (DAS)</h1>
      

      <form id="loginForm" method="post" onsubmit="submitForm()">
        <div class="mb-3">

          <input required placeholder="Email Address" type="text" class="form-control" id="email" name="email"  autocomplete="off">
        </div>
        <div class="mb-3">
          <input required placeholder="Password" type="password" class="form-control" id="password" name="password"  autocomplete="off"> 
          <p><a href="forgot.php">Forgot Password?</a></p>
        </div>

        <!-- <div class="mb-3">
          <a id="forgot"style="text-decoration: none;" class="link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover" href="forgot.php">
              Forgot Password
          </a>
      </div> -->

        <div class="alert d-none" id="alert">
        </div>

       
        <div class="mb-3">
        <button type="submit" class="btn btn-block" id="loginBtn">Login</button>
        </div>
       

      </form>
    </div>
  </div>



    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- Bootstrap CSS -->
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>

    <script>
      function submitForm() {

        event.preventDefault();

        console.log('CLicked');

        var email = $("#email").val();
        var password = $("#password").val();

  
        $.ajax({
            type: "POST",
            url: "backend/login.php",
            data: {email: email, password: password}, 
            success: function(response) {
                console.log(response);

                var alert = document.getElementById("alert");
                alert.classList.add("d-none");
                alert.classList.remove("alert-success", "alert-danger");

                if(response.status == 1){
                  alert.textContent = response.message;
                  alert.classList.add("alert-success");
                  alert.classList.remove("d-none");

                  
                  window.location.href = 'dashboard.php';

                  //user
                  

                }else if(response.status == 2 ){
                  
                  window.location.href = response.message;
                  //redirect to view
                  
                }else if(response.status == 3 ){
                  
                  window.location.href = response.message;
                  //redirect to view
                  
                }else if(response.status == 0){
                  alert.textContent = response.message;
                  alert.classList.add("alert-danger");
                  alert.classList.remove("d-none");
                }

                setTimeout(function() {
                  alert.classList.add("d-none");
                }, 5000);

            }
        });

    
      }
    </script>


</body>
</html>
