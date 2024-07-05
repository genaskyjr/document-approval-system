<?php 
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit(); // Exit to prevent further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR Add User</title>

  <!-- Bootstrap CSS -->
  <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="../css/upload.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/c3cf7a82ce.js" crossorigin="anonymous"></script>
  
</head>
<body class="bg-light">

<?php 
include '../components/header.php';
?>

<div class="container pt-5 bg-light pb-2">
    <h1 class="text-start mt-5 fs-2">Add User</h1>
    <div class="card p-4" style="width: 100%;"> 

    <div class="mb-3">
        <button type="button" class="btn btn-primary" onclick="goBack()">Back</button>
    </div>

    <script>
    function goBack() {
        window.history.back();
    }
    </script>


    <form id="adduserForm" onsubmit="submitForm(event)" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Full Name" required>
        </div>
        <div class="mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
        </div>
        <div class="mb-3">
            <input type="password" class="form-control" id="Password" name="Password" placeholder="Password" required minlength="8">

        </div>
        <div class="mb-3">
            <label for="formFile" class="form-label">Signature</label>
            <input class="form-control" type="file" id="formFile" name="user_signature" accept=".png">
            <div class="mb-3">
            <p><a href="https://www.signwell.com/online-signature/draw/" target="_blank">Generate Signature signwell.com here</a></p>

    
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-success" name="adduser">Register</button>
        </div>
    </form>
    </div>
</div>

<!-- JavaScript -->
<script src="../js/sweetAlert/sweetalert2.all.min.js"></script>

<script>
    function submitForm(event) {
        event.preventDefault(); // Prevent the default form submission
        
        // Create FormData object
        var formData = new FormData(document.getElementById("adduserForm"));
        
        // Send FormData via AJAX POST request
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "backend/add_user.php", true);
        xhr.onload = function() {
            var response = JSON.parse(xhr.responseText);
            console.log(response);
            if (response.status == 2) {
                // Email is already registered
                Swal.fire({
                    icon: 'error',
                    title: 'Email Already Registered',
                    text: 'The email address provided is already registered.'
                });
            } else if (response.status == 1) {
                // Registration successful
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'User registered successfully!'
                });
            }else if (response.status == 4) {
                 Swal.fire({
                    icon: 'warning',
                    title: 'Wrong file extension',
                    text: 'Please select .png image'
                });
            } else {
                // Other cases
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again later.'
                });
            }
        };
        xhr.onerror = function() {
            // Connection error
            console.error("Error: Connection failed.");
        };
        xhr.send(formData);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>
