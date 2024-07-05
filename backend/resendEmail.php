<?php 
session_start();

if(!isset($_SESSION['user_id'])) {
    echo 0;
    return;
}

include 'dbconnect.php';
include 'function.php';

$id = $_POST['id'];


if(sendNeedToSign($id)){
    $status = 1;
    $response['status'] = $status;
    $response['message'] = 'sent';
}else{
    $status = 0;
    $response['status'] = $status;
    $response['message'] = 'error resending email';
}


echo json_encode($response);


?>