<?php

session_start();

// Include database connection (assuming it's in dbconnect.php)
include_once 'dbconnect.php';

$response = array();
$status = 0;
$response['status'] = $status;
$response['message'] = "Default";

$pdfSender = $_SESSION['user_email'];   // Replace with actual value

$pdfReceiverJson = $_POST['myjson']; 
$data = json_decode($pdfReceiverJson, true);

$actionZeroCount = 0;
$actionZeroCount1 = 0;
$actionZeroCount2 = 0;

foreach ($data['orders'] as $order) {
    if ($order['action'] == 0) {
        $actionZeroCount++;
    }
}

foreach ($data['orders'] as $order) {
    if ($order['action'] == 1) {
        $actionZeroCount1++;
    }
}

foreach ($data['orders'] as $order) {
    if ($order['action'] == 2) {
        $actionZeroCount2++;
    }
}

$pdfCountSign = $actionZeroCount;
$pdfCountCopy = $actionZeroCount1;
$pdfCountFinalCopy = $actionZeroCount2;

//get all need to sign
$data = json_decode($pdfReceiverJson, true);

// Filter the orders where action is 0
$filteredOrders = array_values(array_filter($data['orders'], function ($order) {
    return $order['action'] == 0;
}));

// Reset the 'order' values to 1, 2, 3, ...
foreach ($filteredOrders as $key => &$order) {
    $order['order'] = $key + 1;
}

// Construct the new JSON data
$newJson = ['orders' => $filteredOrders];

// Encode the modified data back to JSON
$pdfSignJson = json_encode($newJson);

$pdfCurrentState = 1;   // Replace with actual value

// Check if the form is submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if files are uploaded
    if (!empty($_FILES['pdfFile']['name'][0])) {
        $uploadedFiles = $_FILES['pdfFile'];

        // Initialize an empty array to store file paths
        $pdfPathsArray = array();

        foreach ($uploadedFiles['tmp_name'] as $key => $tmp_name) {
            $uploadDirectory = 'pdf_folders/';
            // Append timestamp to file name to avoid conflicts
            $timestamp = time();
            $pdfFileName = $timestamp . '_' . basename($uploadedFiles['name'][$key]);
            $pdfPath = $uploadDirectory . $pdfFileName;

            if (move_uploaded_file($uploadedFiles['tmp_name'][$key], $pdfPath)) {
                // File uploaded successfully, add path to the array
                $pdfPathsArray[] = rawurlencode($pdfPath);
            } else {
                // Error uploading this file
                $response['message'] = "Error uploading PDF";
            }
        }

        // Serialize the array of file paths before storing it in the database
        $serializedPdfPaths = serialize($pdfPathsArray);
        
        try {
            // Prepare the SQL statement
            $stmt = $pdo->prepare("INSERT INTO `pdfs` 
                                   (`pdf_path`, `pdf_sender`, `pdf_receiver_json`, `pdf_current_state`,
                                    `pdf_count_sign`, `pdf_sign_json`, `pdf_count_copy`, `pdf_count_final_copy`, duration_delete, time_stamp, title) 
                                   VALUES (:pdfPaths, :pdfSender, :pdfReceiverJson, :pdfCurrentState, :pdfCountSign, :pdfSignJson, :pdfCountCopy, :pdfCountFinalCopy, :duration_delete, :time_stamp, :title)");

            // Bind parameters
            $duration_delete = 90;
            $title = $_POST['title'];
            
            $time_stamp = date('Y-m-d H:i:s'); // Current timestamp
            $stmt->bindParam(':pdfPaths', $serializedPdfPaths, PDO::PARAM_STR);
            $stmt->bindParam(':pdfSender', $pdfSender, PDO::PARAM_STR);
            $stmt->bindParam(':pdfReceiverJson', $pdfReceiverJson, PDO::PARAM_STR);
            $stmt->bindParam(':pdfCurrentState', $pdfCurrentState, PDO::PARAM_INT); // Use PDO::PARAM_INT for integer values
            $stmt->bindParam(':pdfCountSign', $pdfCountSign, PDO::PARAM_INT);
            $stmt->bindParam(':pdfSignJson', $pdfSignJson, PDO::PARAM_STR);
            $stmt->bindParam(':pdfCountCopy', $pdfCountCopy, PDO::PARAM_INT);
            $stmt->bindParam(':pdfCountFinalCopy', $pdfCountFinalCopy, PDO::PARAM_INT);
            $stmt->bindParam(':duration_delete', $duration_delete, PDO::PARAM_INT);
            $stmt->bindParam(':time_stamp', $time_stamp);
            $stmt->bindParam(':title', $title);
            // Execute the query
            $stmt->execute();

            $lastInsertId = $pdo->lastInsertId();

            // Check if the insertion was successful
            if ($stmt->rowCount() > 0) {
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "PDF record inserted successfully!";

                include 'function.php';

                $id = $lastInsertId;
                $stmt = $pdo->prepare("SELECT `pdf_current_state`,`pdf_count_sign`,`pdf_count_copy`,`pdf_count_final_copy`, `pdf_sender` FROM `pdfs` WHERE `pdf_id` = :pdfId");
                $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count_sign = $result['pdf_count_sign'];
                $count_copy = $result['pdf_count_copy'];
                $count_final_copy = $result['pdf_count_final_copy'];
                $request_maker = $result['pdf_sender'];

                $current_state = $result['pdf_current_state'];

                if($count_copy > 0) {
                    $status = 1;
                    $response['status'] = $status;
                    $response['message'] = sendAllNeedCopy($lastInsertId, $request_maker);
                }

                if($count_sign == 0) {  
                    $status = 1;
                    $response['status'] = $status;
                    $response['message'] = sendAllNeedFinalCopy($id, $request_maker);
                } else {
                    $status = 1;
                    $response['status'] = $status;
                    $response['message'] = sendNeedToSign($lastInsertId, $request_maker);
                }

                sendEmailNotificationToSender($request_maker);

            } else {
                $status = 0;
                $response['status'] = $status;
                $response['message'] = "Failed to insert PDF record.";
            }
        } catch (PDOException $e) {
            // Handle any potential exceptions
            $status = 0;
            $response['status'] = $status;
            $response['message'] = "Error: " . $e->getMessage();
        }
    } else {
        // No files uploaded
        $response['message'] = "No files uploaded";
    }
}

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
