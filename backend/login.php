<?php
include_once 'dbconnect.php';

// Start the session
session_start();

// Assuming the form sends 'email' and 'password' as POST parameters
$email = $_POST['email'];
$password = $_POST['password'];

$response = array();

// Prepare SQL statement to select user by email
$stmt = $pdo->prepare("SELECT `user_id`, `user_email`, `user_password`, `user_full_name`, `user_signature`, `user_role` FROM `users` WHERE `user_email` = :user_email");

// Bind parameters
$stmt->bindParam(':user_email', $email, PDO::PARAM_STR);

// Execute the query
$stmt->execute();

// Fetch the user data
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Verify the password
    if (password_verify($password, $user['user_password'])) {
        // Store user data in session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['user_email'];
        $_SESSION['user_full_name'] = $user['user_full_name'];
        $_SESSION['user_signature'] = $user['user_signature'];
        $_SESSION['user_role'] = $user['user_role'];
        
        
        

        
        

        // Set status and response message based on user role
        if ($_SESSION['user_role'] == 1) {
            $status = 3;
            $response['status'] = $status;
            $response['message'] = "admin/dashboard.php";
        } elseif (isset($_SESSION['link'])) {
            $status = 2;
            $response['status'] = $status;
            $response['message'] = $_SESSION['link'];
        } else {
            $status = 1;
            $response['status'] = $status;
            $response['message'] = "Login successful!";
        }
    } else {
        // Invalid password
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Login failed. Invalid Email or Password.";
    }
} else {
    // User not found
    $status = 0;
    $response['status'] = $status;
    $response['message'] = "Login failed. User not found.";
}

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
