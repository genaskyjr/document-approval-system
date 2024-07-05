<?php

// Include the PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'backend/vendor/autoload.php';

$response = array();
$email = 'genaskypinlac0@gmail.com';
// Check if email is set before proceeding
if (isset($email)) {
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer(true);

        $adminEmail = 'admin@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'server301.orangehost.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail, 'AEHR Document Approval');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'AEHR Reset Code';
        $code = '123131';
        $mail->Body = $code;

        // Send email
        $mail->send();

        // Set response
        $response['status'] = 1;
        $response['message'] = 'Code has been sent to your Email';
    } catch (Exception $e) {
        // Handle exceptions
        $response['status'] = 0;
        $response['message'] = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    }
} else {
    // Handle case where $email is not set
    $response['status'] = 0;
    $response['message'] = 'Email address is not provided.';
}

// Output JSON response
echo json_encode($response);
?>
