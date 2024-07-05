<?php 
session_start();
$response = array();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1){
    header("Location: ../logout.php");
    exit(); // Exit to prevent further execution
} else {
    // Check if the file is uploaded
    if(isset($_FILES['user_signature'])) {
        // Handle file upload
        $file_name = $_FILES['user_signature']['name'];
        $file_tmp = $_FILES['user_signature']['tmp_name'];
        $file_destination = "../../users_folder/" . time() . basename($file_name); // Adjust destination directory as needed
        
        $file_destination = strtolower($file_destination);

        
        
        // Check if the file is a PNG
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if($file_extension !== 'png') {
            $response['status'] = 4;
            $response['message'] = 'Error: Only PNG files are allowed.';
            echo json_encode($response);
            exit(); // Exit further execution
        }
        
        
        if(move_uploaded_file($file_tmp, $file_destination)) {
            include '../../backend/dbconnect.php'; // Include database connection

            // Get user data from POST
            $user_id = $_POST['user_id'];
            $user_email = $_POST['user_email'];
            $user_password = $_POST['password'];
            $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
            $user_full_name = $_POST['user_full_name'];
            $user_signature = $file_destination; // Use the file destination path
            $user_role = 0;

            try {
                // Prepare the SQL statement
                $sql = "UPDATE users SET 
                        user_email = :user_email,
                        user_password = :user_password,
                        user_full_name = :user_full_name,
                        user_signature = :user_signature,
                        user_role = :user_role
                        WHERE user_id = :user_id";

                // Prepare the SQL statement
                $stmt = $pdo->prepare($sql);

                // Bind parameters
                $stmt->bindParam(':user_email', $user_email);
                $stmt->bindParam(':user_password', $hashed_password);
                $stmt->bindParam(':user_full_name', $user_full_name);
                $stmt->bindParam(':user_signature', $user_signature);
                $stmt->bindParam(':user_role', $user_role);
                $stmt->bindParam(':user_id', $user_id);

                // Execute the statement
                $stmt->execute();

                // Check if any rows were affected
                if ($stmt->rowCount() > 0) {
                    $response['status'] = 1;
                    $response['message'] = 'User updated successfully.';
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'No changes were made.';
                }
            } catch (PDOException $e) {
                // Handle database errors
                $response['status'] = 0;
                $response['message'] = 'Database Error: ' . $e->getMessage();
            }
        } else {
            // Failed to move uploaded file
            include '../../backend/dbconnect.php'; // Include database connection

            // Get user data from POST
            $user_id = $_POST['user_id'];
            $user_email = $_POST['user_email'];
            $user_password = $_POST['password'];
            $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
            $user_full_name = $_POST['user_full_name'];
            $user_signature = $file_destination; // Use the file destination path
            $user_role = 0;

            try {
                // Prepare the SQL statement
                $sql = "UPDATE users SET 
                        user_email = :user_email,
                        user_password = :user_password,
                        user_full_name = :user_full_name,
                        user_signature = :user_signature,
                        user_role = :user_role
                        WHERE user_id = :user_id";

                // Prepare the SQL statement
                $stmt = $pdo->prepare($sql);

                // Bind parameters
                $stmt->bindParam(':user_email', $user_email);
                $stmt->bindParam(':user_password', $hashed_password);
                $stmt->bindParam(':user_full_name', $user_full_name);
                $stmt->bindParam(':user_signature', $user_signature);
                $stmt->bindParam(':user_role', $user_role);
                $stmt->bindParam(':user_id', $user_id);

                // Execute the statement
                $stmt->execute();

                // Check if any rows were affected
                if ($stmt->rowCount() > 0) {
                    $response['status'] = 1;
                    $response['message'] = 'User updated successfully.';
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
    } else {
        // No file uploaded
        $response['status'] = 0;
        $response['message'] = 'Error: No file uploaded.';
    }
}

echo json_encode($response);
?>
