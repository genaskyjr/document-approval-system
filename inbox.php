<?php 
session_start();
//if not logged or role is not admin goto logout
if(!isset($_SESSION['user_id'])){
    header("Location: logout.php");
}else{

  include 'backend/dbconnect.php';

}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR Inbox</title>

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


$sql = "SELECT pdf_path, pdf_id, pdf_sign_json, is_complete, is_canceled, pdf_current_state FROM pdfs";





try {
$stmt = $pdo->prepare($sql);
$stmt->execute();

$needSignCount = 0; 
$pending = 0;  
$isComplete = 0;
$isCanceled = 0;


// Fetch the results as associative arrays
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $signData = json_decode($row['pdf_sign_json'], true);

    $current_signing_in_json = returnCurrentState($row['pdf_id']);

$isCompleteIncremented = false; // Initialize flag

$needSignCountIncremented = false; // Initialize flag

foreach ($signData['orders'] as $order) {
    if (in_array($_SESSION['user_email'], $order['emails'])) {
        // Means in array

        if (!$isCompleteIncremented && $row['is_complete'] == 1) {
            $isComplete++;
            $isCompleteIncremented = true; // Set flag to true
        } elseif (!$needSignCountIncremented && $order['order'] == $row['pdf_current_state']) {
            $needSignCount++;
            $needSignCountIncremented = true; // Set flag to true
        } elseif ($row['is_canceled'] == 1) {
            $isCanceled++;
        } elseif ($order['order'] < $current_signing_in_json) {
            // Handle logic for orders less than current_signing_in_json
        } else {
            $pending++;
        }
    }
}


    
    
    
}


} catch (PDOException $e) {
// Handle database errors
echo "Error: " . $e->getMessage();
}



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
  <a href="inbox.php?filter=needtosign" class="border card p-3 flex-grow-1 m-0 mr-3 fs-5" id="needsign">Need to Sign </a>
  <a href="inbox.php?filter=pending" class="border card p-3 flex-grow-1 m-0 mx-3 fs-5">Pending <?php echo $pending; ?></a>
  <a href="inbox.php?filter=complete" class="border card p-3 flex-grow-1 m-0 mx-3 fs-5">Complete <?php echo $isComplete; ?></a>
  <div class="border card p-3 flex-grow-1 m-0 ml-3 fs-5">Total Canceled <?php echo $isCanceled; ?></div>
</div>






  <div class="card p-4" style="width: 100%;"> 




    <h1 class="text-start fs-2">My Inbox</h1>
    <nav class="navbar bg-body-tertiary">
    <div class="mb-3">
                <button type="button" class="btn btn-primary" onclick="goBack()">Back</button>
            </div>
            <script>
                function goBack() {
                    window.history.back();
                }
            </script>

      
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
// Fetch the results as associative arrays

$sql = "SELECT pdf_path, pdf_id, pdf_sign_json, is_complete, is_canceled, pdf_current_state, duration_delete, time_stamp, title FROM pdfs 
    ORDER BY time_stamp DESC";



if(isset($_GET['filter']) && $_GET['filter'] === 'complete') {
    $sql .= " WHERE is_complete = 1"; // Filter for complete status
}

$stmt = $pdo->prepare($sql);
$stmt->execute();





$stmt = $pdo->prepare($sql);
$stmt->execute();


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $signData = json_decode($row['pdf_sign_json'], true);


    $pdf_current_state = $row['pdf_current_state'];
    $pdf_count_sign = $row['pdf_count_sign'];
    $is_complete = $row['is_complete'];
    $is_canceled = $row['is_canceled'];

    $current_signing_in_json = returnCurrentState($row['pdf_id']);


    $status = 'None';

    foreach ($signData['orders'] as $order) {
        if (in_array($_SESSION['user_email'], $order['emails'])) {
            if ($row['is_complete'] == 1) {
                $status = 'Complete';
            } else {
              
                if ($order['order'] == $row['pdf_current_state']) {
                    $status = 'Need to Sign ';
                    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        
                    $status .= '<a href="' . $baseUrl . '/sign.php?id=' . $row['pdf_id'] . '" class="btn btn-primary"><i class="fa-solid fa-signature"></i> Sign</a>';


                    if ($row['is_canceled'] == 1) {
                        $status = 'Canceled';
                    }
                }else{
                    if ($row['is_canceled'] == 1) {
                        $status = 'Canceled';
                    }else{

                        if ($order['order'] < $current_signing_in_json) {
                            $status = "Approved";
                        }else{
                            $status = 'Pending';
                        }
                        
                    }
                }
        
            }

        }
    }

// Initialize a flag to track whether the row has been outputted
$rowOutputted = false;

foreach ($signData['orders'] as $order) {
    if (in_array($_SESSION['user_email'], $order['emails'])) {
        // If the row has not been outputted yet, output it
        if (!$rowOutputted) {
            // Display the table row
            echo "<tr>";
            echo "<td>{$row['pdf_id']}</td>";
            echo "<td>{$row['title']}</td>";
            // Output PDF paths
            echo "<td class='text-overflow' style='max-width: 150px;'>";
            $array = unserialize($row['pdf_path']);
            foreach ($array as $element) {
                echo substr(rawurldecode($element), 23) . " ";
            }
            echo "</td>";
        
            // Output status
            echo "<td>$status</td>";
        
            // Output action links
            echo "<td>";
            echo "<a href='view-status.php?id=" . $row['pdf_id'] . "&action=view' class='btn btn-primary'>View</a> ";
            echo "<a href='backend/action.php?id=" . $row['pdf_id'] . "&action=download' class='btn btn-success'>Download</a>";
            echo "</td>";
        
            // Output duration_delete
            echo "<td>{$row['duration_delete']} Days</td>";
            echo "</tr>";

            // Set the flag to true to indicate that the row has been outputted
            $rowOutputted = true;
        }
    }
}

    
    
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if the URL contains the filter parameter 'needtosign'
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter');
    
    if (filter === 'needtosign') {
        // Select all table rows
        const tableCells = document.querySelectorAll('td:nth-child(4)');
        
        // Loop through each cell
        tableCells.forEach(function(cell) {
            // Get the status text
            const status = cell.textContent.trim();
                
            // Check if the status is 'Pending', 'Complete', or 'Approved'
            if (status === 'Pending' || status === 'Complete' || status === 'Approved') {
                // If the status matches, remove the row
                const row = cell.parentNode;
                row.parentNode.removeChild(row);
            }
        });
    }
    
    
    
    
    if (filter === 'pending') {
        // Select all table rows
        const tableCells = document.querySelectorAll('td:nth-child(4)');
        
        // Loop through each cell
        tableCells.forEach(function(cell) {
            // Get the status text
            const status = cell.textContent.trim();
                
       
         
            if (status.includes('Need to Sign') || status === 'Complete' || status === 'Approved') {
                // If the status matches, remove the row
                const row = cell.parentNode;
                row.parentNode.removeChild(row);
            }

            
            
            
        });
    }
    
    

    
    
    
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableCells = document.querySelectorAll('td:nth-child(4)');
    let countNeedToSign = 0;
    
    tableCells.forEach(function(cell) {
        const status = cell.textContent.trim();
        if (status.includes('Need to Sign')) {
            countNeedToSign++;
        }
    });

    // Update the count of "Need to Sign" items in the link
    const needToSignLink = document.getElementById('needsign');
    needToSignLink.textContent = 'Need to Sign ' + countNeedToSign;
    
    console.log(countNeedToSign);
});
</script>






<!-- Repeat for each user -->

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

