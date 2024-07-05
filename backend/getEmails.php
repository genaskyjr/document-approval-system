<?php
// Include database connection
include 'dbconnect.php';

// Get the input parameter
$input = isset($_GET['input']) ? $_GET['input'] : '';

// Prepare SQL statement to fetch email list based on input
$sql = "SELECT `user_email` FROM `users` WHERE `user_email` LIKE :input";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':input', "$input%", PDO::PARAM_STR);
$stmt->execute();

// Fetch all email addresses as an associative array
$emailList = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Echo the email list as JSON encoded
echo json_encode($emailList);
?>
