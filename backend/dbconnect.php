<?php

// Database configuration
$host = '127.0.0.1';
$dbname = 'atsphdas_aehr';
$user = 'atsphdas_user';
$password = 'atsphdas_pass';

try {
    // Establish a connection to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //echo "Connected successfully";
} catch (PDOException $e) {
    //Handle connection errors
    //echo "Connection failed: " . $e->getMessage();
}
?>
