<?php 
session_start();
$response = array();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../logout.php");
} else {
    include '../../backend/dbconnect.php';
    
    // Check if the ID is provided in the POST request
    if(isset($_POST['id'])) {
        try {
            // Prepare and execute the DELETE query
            $stmt = $pdo->prepare("DELETE FROM `users` WHERE `user_id` = :id");
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->execute();

            // Check if any row was affected
            if ($stmt->rowCount() > 0) {
                $response['status'] = 'success';
                $response['message'] = 'User deleted successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'No user found with provided ID';
            }
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'User ID not provided';
    }

    // Output the response as JSON
    echo json_encode($response);
}
?>
