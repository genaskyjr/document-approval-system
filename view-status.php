<?php 
session_start();
//if not logged or role is not admin goto logout
if(!isset($_SESSION['user_id'])){
    header("Location: logout.php");
    exit(); // Always exit after a header redirect
} else {

    include 'backend/dbconnect.php';



}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEHR Status</title>

  <!-- Bootstrap CSS -->
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Css -->
  <link href="css/upload.css" rel="stylesheet">
  
  <script src="https://kit.fontawesome.com/c3cf7a82ce.js" crossorigin="anonymous"></script>
  <script src="js/sweetAlert/sweetalert2.all.min.js"></script>

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>


</head>
<body class="bg-light">
<?php include 'components/header.php'; ?>
<div class="container pt-5 bg-light pb-2 mt-5">
    <div class="card p-4" style="width: 100%;"> 
        <h1 class="text-start fs-2">View Status <?php echo $_GET['id']; ?></h1>
        <nav class="navbar bg-body-tertiary">
            <div class="mb-3">
                <button type="button" class="btn btn-primary" onclick="goBack()">Back</button>
            </div>
            <script>
                function goBack() {
                    window.history.back();
                }
            </script>
        </nav>

        <style>
            /* Add your CSS styles for the table here */
            table {
                width: 60%;
                border-collapse: collapse;
            }

            th, td {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

            th {
                background-color: #f2f2f2;
            }

            /* Add CSS style for the row with status "Signing" */
            tr.signing {
                background-color: #ffeeba; /* You can change the color as per your preference */
            }
        </style>

        <table>
            <tr>
                <th>Order</th>
                <th>Email</th>
                <th>Status</th>
            </tr>

            <?php 
            include 'backend/function.php';
            $jsonData = returnSigningJson($_GET['id']); // Assuming the order ID is passed via GET parameter

        
            $data = json_decode($jsonData, true);
            $current_signing_in_json = returnCurrentState($_GET['id']);

            // echo $jsonData;
            // echo '<br>';
            // echo $current_signing_in_json;


            $is_canceled = 0;
       
            $sql = "SELECT is_canceled,is_complete, pdf_sender, pdf_current_state
                    FROM pdfs 
                    WHERE pdf_id = :pdf_id";

        
                $stmt = $pdo->prepare($sql);
                $pdf_id = $_GET['id'];
                $stmt->bindParam(':pdf_id', $pdf_id); 
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);


         



            foreach ($data['orders'] as $order) {
                // Set the status based on the current signer
                

                if ($order['order'] == $current_signing_in_json) {
                    $status = "Signing ";


                        if($_SESSION['user_email'] == $result['pdf_sender']){

                            if($result['is_canceled']!=1){
                                $status .= '<button type="button" id="send" class="btn" onclick="resendEmailFunction(\'' . $order['emails'][0] . '\', \'' . $_GET['id'] . '\');"><i class="fa-solid fa-paper-plane btn-sm"></i> Remind</button>';
                            }
                        }


                        
                        if($result['is_complete']==1){
                            $status = "Approved";
                        }

                   
                } else if ($order['order'] < $current_signing_in_json) {
                    $status = "Approved";

                } else {
                    $status = "Pending";
               

                }

                // Add signing class if it's currently signing
                $rowClass = ($order['order'] == $current_signing_in_json) ? 'signing' : '';

                // Output table row
                echo '<tr class="' . $rowClass . '">';
                echo '<td>' . $order['order'] . '</td>';
                echo '<td>' . $order['emails'][0] . '</td>';
                echo '<td>' . $status . '</td>';
                echo '</tr>';
            }
            ?>
        </table>
    </div>
</div>

<script>

function resendEmailFunction(email, id) {

    
 
    var myAlert = Swal.fire({
    icon: 'info',
    title: 'Resending Document!',
    text: 'Your document is resending, please wait.',
    timerProgressBar: true,
    showConfirmButton: false,
    didOpen: () => {
        Swal.showLoading(); // Show loading spinner
    }
}); 


    // Create a new FormData object
    var formData = new FormData();

    // Append the email and id to the FormData object
    formData.append('email', email);
    formData.append('id', id);

    // Make the AJAX request
    $.ajax({
        url: 'backend/resendEmail.php',
        type: 'POST',
        processData: false, // Prevent jQuery from processing the data
        contentType: false, // Prevent jQuery from setting the content type
        data: formData, // Pass the FormData object
        success: function(response) {

            myAlert.close();

                Swal.fire({
                    icon: 'success',
                    title: 'Document Sent!',
                    text: 'Your document resend successfully.',
                    timer: 3000, // Automatically close after 3 seconds
                    timerProgressBar: true,
                    showConfirmButton: false
                });


        },
        error: function(xhr, status, error) {
            // Handle error response
            console.error('Error:', error);
        }
    });
}





</script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
