<?php
// Start session
session_start();

// Set error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Construct the message URL
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $message = $baseUrl . '/view-final-copy.php?id=' . urlencode($_GET['id']); // Properly encode the URL

    // Store the link in session
    $_SESSION['link'] = $message;

    // Redirect user to index.php
    header("Location: index.php");
    exit();
}else{
    include 'backend/dbconnect.php'; 
    
    echo 1;
    // Ensure $_GET['id'] is set before using it
    if(isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT `pdf_receiver_json`, `pdf_sender` FROM `pdfs` WHERE `pdf_id` = :pdfId");
        $stmt->bindParam(':pdfId', $_GET['id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $EmailCount = 0;

        $pdfReceiverJson = $result['pdf_receiver_json'];

        $pdf_sender = $result['pdf_sender'];

        $data = json_decode($pdfReceiverJson, true);

// Get all orders
$orders = $data['orders'];

foreach ($orders as $order) {
    $emails = $order['emails'];

    // Loop through emails and send copy
    foreach ($emails as $email) {
        if ($email == $_SESSION['user_email']) {
            $EmailCount = $EmailCount + 1;
        }
    }
}

        if($EmailCount > 0 || $_SESSION['user_email'] == $pdf_sender || $_SESSION['user_id']==1){
            
            $stmt = $pdo->prepare("SELECT `pdf_path` FROM `pdfs` WHERE `pdf_id` = :pdf_id");
            $pdf_id = $_GET['id'];

            $stmt->bindParam(':pdf_id', $pdf_id, PDO::PARAM_INT);

            $stmt->execute();

            $pdfData = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if pdfData is not empty before accessing its elements
            if($pdfData) {
                $pdf_paths = unserialize($pdfData['pdf_path']);
            } else {
                // Handle empty result
                // Redirect to appropriate page or display error message
            }
        } else {
            // Redirect user if conditions are not met
            Header("Location: upload.php");
        }
    } else {
        // Handle case where $_GET['id'] is not set
        // Redirect to appropriate page or display error message
    }
}
?>










<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR PDF Viewer</title>

  <!-- Bootstrap CSS -->
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Css -->
  <link href="css/sign.css" rel="stylesheet">


    <!-- Bootstrap CSS JS -->
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.bundle.min.js"></script>






    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .pdf-container {
            width: 100%;
            height: 600px; /* Adjust the height as needed */
        }

        .pdf-object {
            width: 100%;
            height: 100%;
        }

        .pdf-pages{
          height: 200px;
          width: 100%;
        }


    </style>



</head>
<body class="bg-light">


<?php 

include 'components/header.php';

?>


<!-- Sign document -->
<div class="container pt-5 bg-light pb-2">
  <h1 class="text-start mt-5 fs-2">View Final Document</h1>

  <?php 
if (!empty($pdf_paths)) {
    foreach ($pdf_paths as $pdf_path) {
        // Output each path
        echo '<div class="card">';
        echo '<iframe class="mb-3" src="backend/' . rawurlencode($pdf_path) . '" width="100%" height="600px" controls="false"></iframe>';
        echo '</div>';
    }
} else {
    echo "No paths found"; // Handle case where array is empty
}
?>


  


</div>






</div>



<script>
 




</script>


    <!-- SweetAlert JS -->
    <script src="js/sweetAlert/sweetalert2.all.min.js"></script>

    <!-- jQuery JS -->
    <script type="text/javascript" src="js/jQuery/jquery-3.3.1.slim.min.js"></script>

    <meta http-equiv="Content-Security-Policy" content="script-src 'self' https://mozilla.github.io/;">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

    


</body>

</html>

