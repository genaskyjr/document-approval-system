<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    // Prepare response array
    $response = array();
    include_once 'dbconnect.php';
    // Prepare SQL statement to select user by email
    $stmt = $pdo->prepare("SELECT `user_email` FROM `users` WHERE `user_email` = :user_email");

    // Bind parameters
    $stmt->bindParam(':user_email', $email, PDO::PARAM_STR);

    // Execute the query
    $stmt->execute();

    // Fetch the user data
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // User with this email exists
        $response['status'] = 1;
        $response['message'] = '';
        //send code to email
   
        // Generate or retrieve the code
        $code = generateResetCode(); // You need to implement this function
        
        // Prepare the SQL statement for inserting reset code
        $sql = "INSERT INTO `reset_codes`(`user_email`, `code`, `is_valid`, `time_stamp`) VALUES (:user_email, :code, 1, :time_stamp)";
        
        // Prepare the PDO statement
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':user_email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindValue(':time_stamp', date("Y-m-d H:i:s"), PDO::PARAM_STR);
        
        // Execute the statement
        if ($stmt->execute()) {
            $response['message'] .= ' Code sent to email, Enter Code'; // Append code sent to email message

            //send code to email
            include 'function.php';
            
            if(sendForgotCode($email,$code)){
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "Code has been sent to your Email";      
            }


        } else {
            $response['status'] = 0;
            $response['message'] = 'Error inserting reset code: ' . $stmt->errorInfo()[2];
        }
    } else {
        // User with this email does not exist
        $response['status'] = 0;
        $response['message'] = 'User with this email does not exist.';
    }

    // Send the JSON response
    echo json_encode($response);
} else {
    // Handle case when email parameter is not provided
    $response['status'] = 0;
    $response['message'] = 'Email parameter not provided.';
    echo json_encode($response);
}

function generateResetCode() {
    // Generate a random code (you can adjust the length and characters as needed)
    $characters = '0123456789';
    $code = '';
    $code_length = 4; // Adjust the length as needed
    for ($i = 0; $i < $code_length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}


?>
