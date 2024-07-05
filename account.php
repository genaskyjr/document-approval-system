<?php 
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: logout.php");
}else{
    include 'backend/dbconnect.php';  
    
    $id = $_SESSION['user_id'];
    
    $sql = "SELECT `user_id`, `user_email`, `user_full_name`,user_signature FROM `users` WHERE `user_id` = :id";

    $stmt = $pdo->prepare($sql);
    // Bind parameters
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    // Execute the query
    $stmt->execute();
    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Extract data from the result
        $user_id = $result['user_id'];
        $user_email = $result['user_email'];
        $user_full_name = $result['user_full_name'];
        $signature = $result['user_signature'];
    
        // Now you can use these variables as needed
    }
    
    
}





?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR Update Account</title>

  <!-- Bootstrap CSS -->
  <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Css -->
  <link href="/css/upload.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/c3cf7a82ce.js" crossorigin="anonymous"></script>

</head>
<body class="bg-light">


<?php 

include 'components/header.php';

?>

<div class="container pt-5 bg-light pb-2">
    <h1 class="text-start mt-5 fs-2">My Account</h1>
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
        <input value="<?php echo $user_id ?>" type="id" class="form-control d-none" id="id" name="user_id" placeholder="id" >
    </div>
    
    
    <?php 
    if (strpos($_SESSION['user_signature'], '.png') === false) {
        echo '<div class="alert alert-warning" role="alert">
            System detected you dont have signature uploaded yet, please upload your signature and update
        </div>';
    }
?>

    

    
    <div class="mb-3">
        <input value="<?php echo $user_email ?>" type="email" class="form-control" id="email" name="user_email" placeholder="Email"  required readonly>
    </div>
    <div class="mb-3">
        <input value="<?php echo $user_full_name ?>" type="text" class="form-control" id="full_name" name="user_full_name" placeholder="Full Name"  required>
    </div>
    <div class="mb-3">
        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
    </div>
    <div class="mb-3">
        <label for="formFile" class="form-label">Signature <span class="text-muted">Use .png only</span></label>
        <input class="form-control" type="file" id="formFile" name="user_signature" accept=".png">
        <p><a href="https://www.signwell.com/online-signature/draw/" target="_blank">Generate Signature signwell.com here</a></p>
    </div>
    
<style>
    .mb-3 img {
        max-height: 50px;
    }
</style>

<div class="mb-3">
    <img src="<?php echo $signature ?>" alt="..." class="img-thumbnail">
</div>

    
    
    <div class="mb-3">
        <button type="submit" class="btn btn-success" name="update">Update</button>
    </div>
</form>

<!-- JavaScript -->
<script src="js/sweetAlert/sweetalert2.all.min.js"></script>

<script>
    function submitForm(event) {
        event.preventDefault(); // Prevent the default form submission
        
        // Create FormData object
        var formData = new FormData(document.getElementById("updateForm"));
        console.log(formData);
        // Send FormData via AJAX POST request
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "backend/update_account.php", true);
        xhr.onload = function() {
            console.log(xhr.responseText);
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
            }else if(response.status == 2){
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

