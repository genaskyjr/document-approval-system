<?php 
session_start();
//if not logged or role is not admin goto logout
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1){
    header("Location: ../logout.php");
}else{
  include '../backend/dbconnect.php';

  //count
  $sql = "SELECT COUNT(*) AS total_count FROM `pdfs`";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);


  
  //count
  $sql = "SELECT COUNT(*) AS total_users FROM `users`";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $result_count = $stmt->fetch(PDO::FETCH_ASSOC);


  $sql = "SELECT pdf_current_state, pdf_count_sign FROM `pdfs`";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC); // Correct variable name
  
  $pending = 0;
  $complete = 0;
  
  foreach ($results as $row) {
      $pdf_current_state = $row['pdf_current_state'];
      $pdf_count_sign = $row['pdf_count_sign'];
  
      if ($pdf_current_state != $pdf_count_sign) {
          $pending = $pending + 1;
      }

      if($pdf_current_state == $pdf_count_sign){
        $complete = $complete + 1;
      }
  }
 

}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Css -->
  <link href="../css/upload.css" rel="stylesheet">
  
  <script src="https://kit.fontawesome.com/c3cf7a82ce.js" crossorigin="anonymous"></script>

  <script src="../js/sweetAlert/sweetalert2.all.min.js"></script>


</head>
<body class="bg-light">


<?php 
include '../components/header.php';
include '../backend/function.php';



$storagePath = '/home/atsphdas/';
$usagePercentage = calculateStorageUsagePercentage($storagePath);
?>

<div class="container pt-5 bg-light pb-2">

<div class="d-flex justify-content-between mt-5 text-center mb-3">
  <div class="border card p-3 flex-grow-1 m-0 mr-3 fs-5">Total Storage <?php echo getTotalSpaceGB($storagePath); ?> GB</div>
  <div class="border card p-3 flex-grow-1 m-0 mx-3 fs-5">Free Storage <?php echo getFreeSpaceGB($storagePath); ?> GB</div>
  <div class="border card p-3 flex-grow-1 m-0 mx-3 fs-5">Storage Usage <?php echo round($usagePercentage, 2); ?>%</div>
  <div class="border card p-3 flex-grow-1 m-0 ml-3 fs-5">Total User <?php echo $result_count['total_users'] - 1; ?></div>
</div>



  <div class="card p-4" style="width: 100%;"> 
    <h1 class="text-start fs-2">User List</h1>
    <nav class="navbar bg-body-tertiary">
      <a class="navbar-brand" href="#" onclick="window.location.href = '/admin/add-user.php'">
        <button type="button" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add User</button>
      </a>
      <form class="d-flex" role="search" id="search">
        <input name="search"class="form-control me-2" type="search" placeholder="Search" aria-label="Search" required>
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </nav>

    <table class="table table-striped border">
      <thead>
        <tr>
          <!-- <th scope="col">Id</th> -->
          <th scope="col">Full Name</th>
          <th scope="col">Email</th>
          <th scope="col">Singature</th>
          <th scope="col">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php
// Check if search parameter is provided
if(isset($_GET['search'])) {
    // Get the search term from the GET request
    $searchTerm = $_GET['search'];

    // Define the search condition
    $searchCondition = "`user_email` LIKE :searchTerm OR `user_full_name` LIKE :searchTerm AND user_role = 0";

    // Construct the SQL query with the search condition
    $sql = "SELECT `user_id`, `user_email`, `user_full_name`, `user_signature` FROM `users` WHERE $searchCondition";

    try {
        // Prepare the SQL statement
        $stmt = $pdo->prepare($sql);

        // Bind the search term parameter
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);

        // Execute the statement
        $stmt->execute();

        // Fetch all matching rows
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . $e->getMessage();
        exit(); // Exit the script
    }
} else {
    // If search parameter is not provided, retrieve all users
    $sql = "SELECT `user_id`, `user_email`, `user_full_name`, `user_signature` FROM `users` WHERE user_role = 0";

    try {
        // Prepare the SQL statement
        $stmt = $pdo->prepare($sql);

        // Execute the statement
        $stmt->execute();

        // Fetch all rows
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . $e->getMessage();
        exit(); // Exit the script
    }
}

// Output the results as HTML table rows
foreach ($results as $row) {
    echo "<tr>";
    // echo "<td>{$row['user_id']}</td>";
    echo "<td>{$row['user_full_name']}</td>";
    echo "<td>{$row['user_email']}</td>";
    echo "<td><img class='img-fluid' style='width: 50px' src='/{$row['user_signature']}' alt='User Signature'></td>";
    echo "<td>";

  
echo '<a class="navbar-brand" href="/admin/edit-user.php?id=' . $row["user_id"] . '">';
echo '<button type="button" class="btn btn-success btn-block"><i class="fa-solid fa-pen-to-square"></i> Edit</button>';
echo '</a>';

echo '<button type="button" class="btn btn-danger" style="margin-left: 10px;" onclick="deleteUser(' . $row["user_id"] . ');"><i class="fa-solid fa-xmark"></i> Delete</button>';
echo "</td>";
    echo "</tr>";
}
?>


        
        
        </tr>
      </tbody>
    </table>
  </div>
</div>











</div>



<script>
  function deleteUser(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will not be able to recover this user!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Make AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "backend/delete_user.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle the response
                    var response = JSON.parse(xhr.responseText);
                    if (response.status == "success") {
                        Swal.fire('Deleted!', response.message, 'success');
                        // Optionally, reload the page or update the user interface
                        location.reload();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                }
            };
            // Send the request with the user ID as data
            xhr.send("id=" + userId);
        }
    });
}

</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>

</html>

