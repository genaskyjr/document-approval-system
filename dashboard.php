<?php 
session_start();
//if not logged or role is not admin goto logout
if(!isset($_SESSION['user_id'])){
    header("Location: logout.php");
}else{
    
    
    
    if (strpos($_SESSION['user_signature'], '.png') === false) {
        header('Location: account.php');
        exit; // Ensure that script execution stops after redirecting
    }


    
  include 'backend/dbconnect.php';


//request count
$email = $_SESSION['user_email'];
$sql = "SELECT COUNT(*) AS request_count FROM `pdfs` WHERE pdf_sender = :email";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$request_count = $result['request_count'];



$sql = "SELECT `pdf_current_state`, `pdf_count_sign`, is_complete, is_canceled FROM `pdfs` WHERE `pdf_sender` = :email";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pending = 0;
$complete = 0;
$canceled = 0;

foreach ($results as $row) {
  $pdf_current_state = $row['pdf_current_state'];
  $pdf_count_sign = $row['pdf_count_sign'];
  $is_complete = $row['is_complete'];
  $is_canceled = $row['is_canceled'];

    if($is_canceled==1){
      $canceled++;
    }else{
      if ($is_complete==1) {
        $complete++;
      }else{
        $pending++;
      }
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
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Css -->
  <link href="css/upload.css" rel="stylesheet">
  
  <script src="https://kit.fontawesome.com/c3cf7a82ce.js" crossorigin="anonymous"></script>

  <script src="js/sweetAlert/sweetalert2.all.min.js"></script>


</head>
<body class="bg-light">


<?php 
include 'components/header.php';
include 'backend/function.php';


?>


<style>
    a {
        text-decoration: none; /* Removes underline */
        color: black; /* Default color */
    }

    a:hover {
        color: blue; /* Change color on hover */
    }
</style>


<div class="container pt-5 bg-light pb-2">

<div class="d-flex justify-content-between mt-5 text-center mb-3">
<a href="dashboard.php" class="border card p-3 flex-grow-1 m-0 mr-3 fs-5">Total Request <?php echo $request_count; ?></a>
<a href="dashboard.php?filter=waiting" class="border card p-3 flex-grow-1 m-0 mx-3 fs-5">Total Waiting <?php echo $pending; ?></a>
<a href="dashboard.php?filter=complete" class="border card p-3 flex-grow-1 m-0 mx-3 fs-5">Total Complete <?php echo $complete; ?></a>
<div class="border card p-3 flex-grow-1 m-0 ml-3 fs-5">Total Canceled <?php echo $canceled; ?></div>

</div>



  <div class="card p-4" style="width: 100%;"> 
    <h1 class="text-start fs-2">My Request List</h1>
    <nav class="navbar bg-body-tertiary">
      <a class="navbar-brand">
        <button type="button" onclick="window.location.href = 'upload.php'" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Request</button>
        <button type="button" onclick="window.location.href = 'inbox.php'" class="btn btn-primary"><i class="fa-solid fa-envelope"></i> Inbox</button>
      </a>

      
      <!-- <form class="d-flex" role="search" id="search">
        <input name="search"class="form-control me-2" type="search" placeholder="Search" aria-label="Search" required>
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form> -->
    </nav>

    <table class="table table-striped border">
      <thead>
        <tr>
          <!-- <th scope="col">Id</th> -->
          <th scope="col">ID</th>
          <th scope="col">Title</th>
          <th scope="col">File</th>
          <th scope="col">Status</th>
          <th scope="col">Action</th>
          <th scope="col">Delete Duration</th>
        </tr>
      </thead>
      <tbody>

    

      <style>
  .text-overflow {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
</style>


      <?php 
      

      
$sql = "SELECT pdf_id, pdf_path, pdf_sender, pdf_receiver_json, 
pdf_current_state, pdf_count_sign, pdf_sign_json, pdf_count_copy, pdf_count_final_copy, 
is_complete, is_canceled, duration_delete, time_stamp, title
FROM pdfs 
WHERE pdf_sender = :pdf_sender 
ORDER BY time_stamp DESC";

if(isset($_GET['filter']) && $_GET['filter'] === 'waiting') {
    $sql = "SELECT pdf_id, pdf_path, pdf_sender, pdf_receiver_json, 
    pdf_current_state, pdf_count_sign, pdf_sign_json, pdf_count_copy, pdf_count_final_copy, 
    is_complete, is_canceled, duration_delete, time_stamp , title
    FROM pdfs 
    WHERE pdf_sender = :pdf_sender AND is_complete = 0
    ORDER BY time_stamp DESC";
}

if(isset($_GET['filter']) && $_GET['filter'] === 'complete') {
    $sql = "SELECT pdf_id, pdf_path, pdf_sender, pdf_receiver_json, 
    pdf_current_state, pdf_count_sign, pdf_sign_json, pdf_count_copy, pdf_count_final_copy, 
    is_complete, is_canceled, duration_delete, time_stamp , title
    FROM pdfs 
    WHERE pdf_sender = :pdf_sender AND is_complete = 1
    ORDER BY time_stamp DESC";
}

$stmt = $pdo->prepare($sql);
$pdf_sender = $_SESSION['user_email'];
$stmt->bindParam(':pdf_sender', $pdf_sender); 
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    
    
foreach ($result as $row) {
    $array = unserialize($row['pdf_path']);

    $pdf_current_state = $row['pdf_current_state'];
    $pdf_count_sign = $row['pdf_count_sign'];
    $is_complete = $row['is_complete'];
    $is_canceled = $row['is_canceled'];

    $status = 'None';


    if($is_canceled==1){
      $status = 'Canceled';
    }else{
      if ($is_complete==1) {
        $status = 'Complete';
      }else{
        $status = 'Waiting';
      }
    }



    // Perform actions for each row
    echo "<tr>";
    echo "<td>" . $row['pdf_id'] . "</td>";
     echo "<td>" . $row['title'] . "</td>";

// Output PDF paths
echo "<td class='text-overflow' style='max-width: 150px;'>";
foreach ($array as $element) {
  echo substr(rawurldecode($element), 23) . " ";
}
echo "</td>";


    // Output status
    echo "<td>$status</td>";

    // Output action links
    echo "<td>";
    
    echo "<a href='view-status.php?id=" . $row['pdf_id'] . "&action=view' class='btn btn-primary'>View</a>  ";
    echo "<a href='backend/action.php?id=" . $row['pdf_id'] . "&action=download' class='btn btn-success'>Download</a>  ";
    // echo "<a href='backend/action.php?id=" . $row['pdf_id'] . "&action=delete' class='btn btn-danger'>Delete</a> | ";
    echo "<button class='btn btn-danger' onClick=\"cancel('" . $row['pdf_id'] . "')\">Cancel</button>";
  
    echo "</td>";

    // Output duration_delete
    echo "<td>" . $row['duration_delete'] . " Days</td>";
    echo "</tr>";
}
?>





<!-- Repeat for each user -->

      </tbody>
    </table>
  </div>
</div>











</div>



<script>
  function cancel(id) {
    console.log(id);
    Swal.fire({
      title: 'Are you sure?',
      text: 'You will not be able to recover this pdf!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Cancel it!',
      cancelButtonText: 'Cancel',
      input: 'text',
      inputPlaceholder: 'Please enter your reason for cancellation' // Optional placeholder
    }).then((result) => {
      if (result.isConfirmed) {
        // Get the reason entered by the user
        const reason = result.value;
        // Make AJAX request
        $.ajax({
          url: 'backend/action.php',
          type: 'GET',
          contentType: 'application/x-www-form-urlencoded',
          data: {
            id: id,
            action: 'cancel',
            reason: reason // Pass the reason to the server
          },
          success: function(response) {
            // Handle success response
            Swal.fire({
              title: 'Canceled!',
              text: 'pdf ' + id + ' cancel successfully.',
              icon: 'success',
              confirmButtonText: 'OK'
            }).then((result) => {
              if (result.isConfirmed) {
                location.reload();
              }
            });
          },
          error: function(xhr, status, error) {
            // Handle error
            Swal.fire('Error!', 'Canceling pdf error', 'error');
            console.error('There was a problem with the AJAX request:', error);
          }
        });
      }
    });
  }
</script>




<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>

</html>

