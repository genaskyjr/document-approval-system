<?php 
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1){
    header("Location: ../logout.php");
}else{
    include '../backend/dbconnect.php';  
    $user_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT `user_id`, `user_email`, `user_full_name` FROM `users` WHERE `user_id` = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR Edit User</title>

  <!-- Bootstrap CSS -->
  <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Css -->
  <link href="../css/upload.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/c3cf7a82ce.js" crossorigin="anonymous"></script>

</head>
<body class="bg-light">


<?php 

include '../components/header.php';

?>

<div class="container pt-5 bg-light pb-2">
    <h1 class="text-start mt-5 fs-2">Update User</h1>
    <div class="card p-4" style="width: 100%;"> 

    <div class="mb-3">
        <button type="button" class="btn btn-primary" onclick="goBack()">Back</button>
    </div>

    <script>
    function goBack() {
        window.history.back();
    }
    </script>


    <form id="updateForm" onsubmit="submitForm(event)" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <input type="id" class="form-control d-none" id="id" name="user_id" placeholder="id" value='<?php echo $result['user_id'] ?>'>
    </div>
    <div class="mb-3">
        <input type="email" class="form-control" id="email" name="user_email" placeholder="Email" value='<?php echo $result['user_email'] ?>' required>
    </div>
    <div class="mb-3">
        <input type="text" class="form-control" id="full_name" name="user_full_name" placeholder="Full Name" value='<?php echo $result['user_full_name'] ?>' required>
    </div>
    <div class="mb-3">
        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="8">
    </div>
    <div class="mb-3">
        <label for="formFile" class="form-label">Signature</label>
        <input class="form-control" type="file" id="formFile" name="user_signature" accept=".png" >
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-success" name="update">Update</button>
    </div>
</form>

<!-- JavaScript -->
<script src="../js/sweetAlert/sweetalert2.all.min.js"></script>

<script>
    function submitForm(event) {
        event.preventDefault(); // Prevent the default form submission
        
        // Create FormData object
        var formData = new FormData(document.getElementById("updateForm"));
        console.log(formData);
        // Send FormData via AJAX POST request
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "backend/edit_user.php", true);
        xhr.onload = function() {
            var response = JSON.parse(xhr.responseText);
            console.log(response);
            if (response.status == 1) {
                // Registration successful
                               Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Update successfully!'
                }).then((result) => {
                    if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                        location.reload();
                    }
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

</body>
</html>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>







    

    </div>

</div>










</div>






</body>

</html>

