<?php 
include '../function.php';
include '../dbconnect.php';

// Calculate storage usage percentage
$storagePath = '/var/www/html'; // Adjust this path as per your requirements
$usagePercentage = calculateStorageUsagePercentage($storagePath);

// Check if storage usage exceeds 80%
// if (90 > 80) {
if (round($usagePercentage, 2) > 80) {
    // Your code for handling the case where usage percentage is greater than 80%
    // For example, getting email addresses and sending warning emails

    $stmt = $pdo->prepare("SELECT `user_email` FROM `users` WHERE `user_role` = 1");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        sendEmailStorageWarning($row['user_email']);
        echo 'sent notify';
    }
} else {
    // If storage usage is not greater than 80%, do something else (optional)
    echo "Storage usage is not greater than 80%.";
}
?>
