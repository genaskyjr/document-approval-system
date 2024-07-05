<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (isset($_POST['email'], $_POST['password'], $_POST['password1'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password1 = $_POST['password1'];

    $response = array();
    // Perform any validation on the email and passwords here if needed
    // For simplicity, we'll just check if the passwords match
    if ($password != $password1) {
        $response['status'] = 0;
        $response['message'] = 'Password and confirmed password do not match';
    } else {
        // Passwords match, proceed with updating the password in the database
        include_once 'dbconnect.php';
        // You need to hash the password before storing it in the database for security
        $hashedPassword = password_hash($password1, PASSWORD_DEFAULT);

        
        // Update the user's password in the database
        $stmt = $pdo->prepare("UPDATE `users` SET `user_password` = :password WHERE `user_email` = :user_email");
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':user_email', $email, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            // Password updated successfully
            $response['status'] = 1;
            $response['message'] = 'Password updated successfully, You will redirect to login page after 3 seconds.';
        } else {
            // Failed to update password
            $response['status'] = 0;
            $response['message'] = 'Failed to update password';
        }
    }
} else {
    // Invalid or missing parameters
    $response['status'] = 0;
    $response['message'] = 'Invalid or missing parameters';
}

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
