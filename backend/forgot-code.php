<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['code']) && isset($_POST['email'])) {
    $email = $_POST['email'];
    $code = $_POST['code'];
    
    $response = array();
    include_once 'dbconnect.php';
   
    // Prepare SQL statement to select reset code by email and code
    $stmt = $pdo->prepare("SELECT `id`, `user_email`, `code`, `is_valid`, `time_stamp` FROM `reset_codes` WHERE `user_email` = :user_email AND `code` = :code");

    // Bind parameters
    $stmt->bindParam(':user_email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':code', $code, PDO::PARAM_STR);

    // Execute the query
    $stmt->execute();

    // Fetch the user data
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // A row matching the provided email and code was found
        if ($result['is_valid'] == 1) {

            $updateStmt = $pdo->prepare("UPDATE `reset_codes` SET `is_valid` = 0 WHERE `user_email` = :user_email AND `code` = :code");
            $updateStmt->bindParam(':user_email', $email, PDO::PARAM_STR);
            $updateStmt->bindParam(':code', $code, PDO::PARAM_STR);
            $updateStmt->execute();

            // Check if the update was successful
            if ($updateStmt->rowCount() > 0) {
                $response['status'] = 1;
                $response['message'] = 'Code Confirmed, Enter New Password';
            } else {
                $response['status'] = 0;
                $response['message'] = 'Failed to update code validity';
            }

        } else {
            $response['status'] = 0;
            $response['message'] = 'Code is Expired';
        }
    } else {
        // No row matching the provided email and code was found
        $response['status'] = 0;
        $response['message'] = 'Code is invalid';
    }
} else {
    // Code or email parameter not provided
    $response['status'] = 0;
    $response['message'] = 'Code or email parameter not provided';
}

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
