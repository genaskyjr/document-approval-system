<?php 
session_start();
$response = array();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1){
    header("Location: ../logout.php");
    exit(); // Exit to prevent further execution
} else {
    include '../../backend/dbconnect.php'; // Include database connection

    // Get user data from POST
    $user_id = $_POST['user_id'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['password']; // Changed from $_POST['password']
    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
    $user_full_name = $_POST['user_full_name'];

    try {
        // Prepare the SQL statement
        $sql = "UPDATE users SET 
                user_email = :user_email,
                user_password = :user_password,
                user_full_name = :user_full_name
                WHERE user_id = :user_id"; // Removed the extra comma

        // Prepare the SQL statement
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_email', $user_email);
        $stmt->bindParam(':user_password', $hashed_password);
        $stmt->bindParam(':user_full_name', $user_full_name);
        $stmt->bindParam(':user_id', $user_id);

        // Execute the statement
        $stmt->execute();

        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            $response['status'] = 1;
            $response['message'] = 'updated';
            
            //$_SESSION['user_full_name'] = $user['user_full_name'];
            
            
        } else {
            $response['status'] = 0;
            $response['message'] = 'No changes were made.';
        }
    } catch (PDOException $e) {
        // Handle database errors
        $response['status'] = 0;
        $response['message'] = 'Database Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>
