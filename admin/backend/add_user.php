<?php 
session_start();
$response = array();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1){
    header("Location: ../logout.php");
}else{
    include '../../backend/dbconnect.php';

    $post_email = $_POST['email'];
    $sql = "SELECT `user_email` FROM `users` WHERE `user_email` = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $post_email, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result > 0){
        $response['status'] = 2;
        $response['message'] = 'Email already registered';
        
    }else{
        //sql insert

        // include '../../backend/dbconnect.php';
        //$pdo
        if(isset($_FILES['user_signature'])) {
            $file_name = $_FILES['user_signature']['name'];
            $file_tmp = $_FILES['user_signature']['tmp_name'];
            $file_destination = "../../users_folder/" . time() . $file_name; // Adjust destination directory as needed
            
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
                // File uploaded successfully

                // Insert user data into the database
                $user_email = $_POST['email'];
                $user_password = $_POST['Password'];

                $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);


                $user_full_name = $_POST['fullname'];
                $user_role = 0; 

                $stmt = $pdo->prepare("INSERT INTO users (user_email, user_password, user_full_name, user_signature, user_role) 
                                        VALUES (:user_email, :user_password, :user_full_name, :user_signature, :user_role)");

                $stmt->bindParam(':user_email', $user_email);
                $stmt->bindParam(':user_password', $hashed_password);
                $stmt->bindParam(':user_full_name', $user_full_name);
                $stmt->bindParam(':user_signature', $file_destination); 
                $stmt->bindParam(':user_role', $user_role);

                $stmt->execute();

                if ($stmt->rowCount() > 0) {

                    //send email    
                    include '../../backend/function.php';
                    // sendEmailNotificationToNewUser($user_email,$user_password);


                    $response['status'] = 1;
                    $response['message'] = sendEmailNotificationToNewUser($user_email,$user_password);
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Error: Failed to register user';
                }
            } else {
                // // Failed to move uploaded file
                // Insert user data into the database
                $user_email = $_POST['email'];
                $user_password = $_POST['Password'];

                $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);


                $user_full_name = $_POST['fullname'];
                $user_role = 0; 

                $stmt = $pdo->prepare("INSERT INTO users (user_email, user_password, user_full_name, user_signature, user_role) 
                                        VALUES (:user_email, :user_password, :user_full_name, :user_signature, :user_role)");

                $stmt->bindParam(':user_email', $user_email);
                $stmt->bindParam(':user_password', $hashed_password);
                $stmt->bindParam(':user_full_name', $user_full_name);
                $stmt->bindParam(':user_signature', $file_destination); 
                $stmt->bindParam(':user_role', $user_role);

                $stmt->execute();

                if ($stmt->rowCount() > 0) {

                    //send email    
                    include '../../backend/function.php';
                    // sendEmailNotificationToNewUser($user_email,$user_password);


                    $response['status'] = 1;
                    $response['message'] = sendEmailNotificationToNewUser($user_email,$user_password);
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Error: Failed to register user';
                }
            }
        } else {
            // No file uploaded
            $response['status'] = 0;
            $response['message'] = 'Error: No file uploaded';
        }

    }


}
echo json_encode($response);
?>