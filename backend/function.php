<?php


function getPdfDirectory($id){
    include 'dbconnect.php';
    $stmt = $pdo->prepare("SELECT `pdf_path` FROM `pdfs` WHERE `pdf_id` = :pdfId");
    $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pdfpath = $result['pdf_path'];

    return $pdfpath;
}

function getEmailsFromPDFid($id) {
    include 'dbconnect.php';

    $stmt = $pdo->prepare("SELECT `pdf_sign_json`, `pdf_current_state` FROM `pdfs` WHERE `pdf_id` = :pdfId");
    $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $jsonData = $result['pdf_sign_json'];
    $current_state = $result['pdf_current_state'];

    $data = json_decode($jsonData, true);

    $matchingOrders = [];
    foreach ($data['orders'] as $order) {
        if ($order['order'] == $current_state && isset($order['emails'])) {
            $matchingOrders[] = $order['emails'];
        }
    }

    // Flatten the array of arrays into a single array of emails
    $emails = array_merge(...$matchingOrders);

    return $emails;
}




function sendNeedToSign($id) {
    //get current state and send email to current state 
    //then if sent current state+1
    
    $response = array();


    include 'dbconnect.php';
    $stmt = $pdo->prepare("SELECT `pdf_current_state`, `pdf_count_sign`, `pdf_sign_json`, `pdf_sender`, title FROM `pdfs` WHERE `pdf_id` = :pdfId");
    $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $count = $result['pdf_count_sign'];
    $current_state = $result['pdf_current_state'];
    $current_emails = getEmailsFromJson($current_state,$id);

    $request_maker = $result['pdf_sender'];
    $title = $result['title'];


    if($current_emails){

        $current_state = $current_state + 1;
        $stmt = $pdo->prepare("UPDATE `pdfs` SET `pdf_current_state` = :newState WHERE `pdf_id` = :pdfId");
        $stmt->bindParam(':newState', $current_state, PDO::PARAM_INT);
        $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);

        $status = 1;
        $response['status'] = $status;
        $response['message'] = sendEmailtoSign($current_emails,$id,$request_maker,$title);
    }
    


    return $response;


}


function returnCurrentState($id){
    include 'dbconnect.php';
    $stmt = $pdo->prepare("SELECT `pdf_current_state` FROM `pdfs` WHERE `pdf_id` = :pdfId");
    $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $current = $result['pdf_current_state'];

    return $current;
}


function returnSigningJson($id){
    include 'dbconnect.php';
    $stmt = $pdo->prepare("SELECT `pdf_sign_json` FROM `pdfs` WHERE `pdf_id` = :pdfId");
    $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $SigningJson = $result['pdf_sign_json'];

    return $SigningJson;
}






function sendEmailtoSign($emails,$id,$request_maker,$title){
    require 'vendor/autoload.php';
    $response = array();
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $Exception = new PHPMailer\PHPMailer\Exception();

        $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'server301.orangehost.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail);

        foreach ($emails as $recipient) {
            // Add recipient to the email
            $mail->addAddress($recipient);
        }




        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $message = $baseUrl . '/sign.php?id='. $id;
        $subject = "AEHR - Document for Signing from " . $request_maker;
       
        
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $subject;
        $mail->Body = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Orders</title>
            <style>
                /* Add your CSS styles for the table here */
                table {
                    width: 60%;
                    border-collapse: collapse;
                }

                th, td {
                    border: 1px solid #dddddd;
                    text-align: left;
                    padding: 8px;
                }

                th {
                    background-color: #f2f2f2;
                }

                /* Add CSS style for the row with status "Signing" */
                tr.signing {
                    background-color: #ffeeba; /* You can change the color as per your preference */
                }
            </style>
        </head>
        <body>';

        $jsonData = returnSigningJson($id);

        $data = json_decode($jsonData, true);
        $current_signing_in_json = returnCurrentState($id);
        
        
            $mail->Body .= 'Dear ' . $recipient . ',';
    $mail->Body .= '<br><br>';
    $mail->Body .= $request_maker . ' has sent you a document for signing.';
    $mail->Body .= '<br><br>';
    $mail->Body .= 'Click the link below to access the document:';
    $mail->Body .= '<br><br>';
    $mail->Body .= '<a href="' . $message . '" class="button">Sign Now</a>';
    $mail->Body .= '<br><br>';
    $mail->Body .= 'Thank you,<br>';
    

        $mail->Body .= $title;
        $mail->Body .= '<table>';
        $mail->Body .= '<tr>';
        $mail->Body .= '<th>Order</th>';
        $mail->Body .= '<th>Email</th>';
        $mail->Body .= '<th>Status</th>';
        $mail->Body .= '</tr>';

        foreach ($data['orders'] as $order) {
            // Set the status based on the current signer
            if ($order['order'] == $current_signing_in_json) {
                $status = "Signing ";
                // Append the link if the current signer matches
                if ($emails[0] == $order['emails'][0]) {
                    $status .= '<a href="' . $message . '" class="button">Sign Now</a>';
                }
            } else if ($order['order'] < $current_signing_in_json) {
                $status = "Approved";
                //$mail->addCC($order['emails'][0]);
                
            } else {
                $status = "Pending";
                $mail->addCC($order['emails'][0]);
            }

            // Add signing class if it's currently signing
            $rowClass = ($order['order'] == $current_signing_in_json) ? 'signing' : '';

            // Append table row to the mail body
            $mail->Body .= '<tr class="' . $rowClass . '">';
            $mail->Body .= '<td>' . $order['order'] . '</td>';
            $mail->Body .= '<td>' . $order['emails'][0] . '</td>';
            $mail->Body .= '<td>' . $status . '</td>';
            $mail->Body .= '</tr>';
        }

        $mail->Body .= '</table>';


        $mail->Body .= '</body></html>';

            $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';


        if($mail->send()){
            $status = 1;
            $response['status'] = $status;
            $response['message'] = "Message has been sent";
        }else{
            $status = 1;
            $response['status'] = $status;
            $response['message'] = "Message not sent";
        }

        

        
    } catch (Exception $e) {
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;


}






function getEmailsFromJson($current_state, $id){
    include 'dbconnect.php';
    
    $stmt = $pdo->prepare("SELECT `pdf_sign_json` FROM `pdfs` WHERE `pdf_id` = :pdfId");
    $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
    $stmt->execute();

    $jsonData = $stmt->fetchColumn(); 

    $data = json_decode($jsonData, true);

    $emails = []; // Array to store emails

    foreach ($data['orders'] as $order) {
        if ($order['order'] == $current_state && isset($order['emails']) && is_array($order['emails'])) {
            // Add all emails associated with the current state to the $emails array
            $emails = array_merge($emails, $order['emails']);
        }
    }

    return $emails;
}


function sendForgotCode($email,$code){
        require 'vendor/autoload.php';
        $response = array();
        try {
            // Create a PHPMailer object
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $Exception = new PHPMailer\PHPMailer\Exception();

             $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'mail.atsphdas.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail);
            $mail->addAddress($email);
            

            $mail->isHTML(true);


            $mail->Subject = 'AEHR Reset Code';
            $mail->Body = $code;
            
            
            $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';



            if($mail->send()){
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "Code has been sent to your Email";      
            }

        } catch (Exception $e) {
            $status = 0;
            $response['status'] = $status;
            $response['message'] = "Error: " . $e->getMessage();
        }
        
        return $response;
}




function sendAllNeedFinalCopy($id, $request_maker) {
    require 'vendor/autoload.php';
    $response = array();
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $Exception = new PHPMailer\PHPMailer\Exception();

        $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'server301.orangehost.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail);

        
        include 'dbconnect.php';

        $stmt = $pdo->prepare("SELECT `pdf_receiver_json` FROM `pdfs` WHERE `pdf_id` = :pdfId");
        $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $pdfReceiverJson = $result['pdf_receiver_json'];
            $data = json_decode($pdfReceiverJson, true);

            // Get orders with action value of 1
            $filteredOrders = array_filter($data['orders'], function ($order) {
                return $order['action'] == 2;
            });

            foreach ($filteredOrders as $order) {
                $emails = $order['emails'];

                // Loop through emails and send copy
                foreach ($emails as $email) {
                    $mail->addAddress($email);
                }
            }


            //http://127.0.0.1/view-pdf.php?id=76


            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

            
                   // Set email subject and body
                   $mail->isHTML(true);

                   $subject = $request_maker . ' sent you the final copy of the document';

        // Construct the email body with HTML
        $message = $baseUrl . '/view-final-copy.php?id=' . $id;
        
        
        $mail->Body = '
            <a href="' . $message . '">Click here to view the copy</a>
            
            </p>';
            
            


        $mail->Subject = $subject; // Assign subject


            $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';




            // Send email
            if ($mail->send()) {
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "Email sent!";
            } else {
                throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        } else {
            $status = 0;
            $response['status'] = $status;
            $response['message'] = "No record found for pdf_id: " . $id;
        }
    } catch (Exception $e) {
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;
}




function sendAllNeedCopy($id,$request_maker) {
    require 'vendor/autoload.php';
    $response = array();
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $Exception = new PHPMailer\PHPMailer\Exception();

        $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'server301.orangehost.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail);

        
        include 'dbconnect.php';

        $stmt = $pdo->prepare("SELECT `pdf_receiver_json` FROM `pdfs` WHERE `pdf_id` = :pdfId");
        $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $pdfReceiverJson = $result['pdf_receiver_json'];
            $data = json_decode($pdfReceiverJson, true);

            // Get orders with action value of 1
            $filteredOrders = array_filter($data['orders'], function ($order) {
                return $order['action'] == 1;
            });

            foreach ($filteredOrders as $order) {
                $emails = $order['emails'];

                // Loop through emails and send copy
                foreach ($emails as $email) {
                    $mail->addAddress($email);
                }
            }


            //http://127.0.0.1/view-pdf.php?id=76


            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
       


                        // Set email subject and body
                        $mail->isHTML(true);

                        $subject = $request_maker . ' sent you the copy of the document';

        // Construct the email body with HTML
        $message = $baseUrl . '/view-pdf.php?id=' . $id;
     

        $mail->Body = '
            <a href="' . $message . '">Click here to view the copy</a>
            
            </p>';

        $subject = $request_maker . ' sent you the copy of the document'; // Define subject
        $mail->Subject = $subject; // Assign subject



            $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';



            // Send email
            if ($mail->send()) {
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "Email sent!";
            } else {
                throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        } else {
            $status = 0;
            $response['status'] = $status;
            $response['message'] = "No record found for pdf_id: " . $id;
        }
    } catch (Exception $e) {
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;
}

function sendEmailNotificationToSender($email){
    require 'vendor/autoload.php';
    $response = array();
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $Exception = new PHPMailer\PHPMailer\Exception();

       $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'server301.orangehost.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail);
        $mail->addAddress($email);
                
          
        $mail->isHTML(true);
        $subject = 'AEHR your document is successfully sent!';
        

        $mail->Subject = $subject;
            
        
            
            // Set email subject
            $mail->Subject = $subject;
            
            $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';


            // Send email
            if ($mail->send()) {
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "Email sent!";
            } else {
                throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
    } catch (Exception $e) {
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;
}



function sendCancelReasontoEmails($id, $reason){
    require 'vendor/autoload.php';
    $response = array();
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $Exception = new PHPMailer\PHPMailer\Exception();

        $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'server301.orangehost.com';  // SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for SMTP
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail);

        // Retrieve recipient emails from database
        include 'dbconnect.php';
        $stmt = $pdo->prepare("SELECT `pdf_current_state`, `pdf_count_sign`, `pdf_sign_json`, `pdf_sender`, title FROM `pdfs` WHERE `pdf_id` = :pdfId");
        $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $current_state = $result['pdf_current_state'];
        $emails = getEmailsFromJson($current_state, $id);

        // Loop through recipients and add them to the email
        foreach ($emails as $recipient) {
            $mail->addAddress($recipient);
        }

        // Set email content
        $subject = 'AEHR Document is canceled!';
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        
    $mail->Body .= 'Reason: ' . $reason . '<br>';
    $mail->Body .= 'ID: ' . $id . '<br>';
    $mail->Body .= 'Title: ' . $result['title'] . '<br>';


            $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';



        // Send email
        if ($mail->send()) {
            $status = 1;
            $response['status'] = $status;
            $response['message'] = "Email sent!";
        } else {
            throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    } catch (Exception $e) {
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;
}




function sendApprovedNotification($email,$id){
    require 'vendor/autoload.php';
    $response = array();
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $Exception = new PHPMailer\PHPMailer\Exception();

        $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'server301.orangehost.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail);
        $mail->addAddress($email);
                
          
        $mail->isHTML(true);
        $subject = 'AEHR your document approved successfully!';
        
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        
        // Construct the email body with HTML
        $message = '<a href="' . $baseUrl . '/view-pdf.php?id=' . $id . '">Click here to view the document</a>';
 
        $mail->Body .= $message;

       
            $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';


// Set email subject
$mail->Subject = $subject;




// Set email subject
$mail->Subject = $subject;

            

            // Send email
            if ($mail->send()) {
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "Email sent!";
            } else {
                throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
    } catch (Exception $e) {
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;

}



function sendEmailDocumentIsComplete($email,$id){

    require 'vendor/autoload.php';
    $response = array();
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $Exception = new PHPMailer\PHPMailer\Exception();

        $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'server301.orangehost.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail);
        $mail->addAddress($email);
                
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
       
            $mail->isHTML(true);
            $subject = 'AEHR your document is complete';
            
            $message = $baseUrl . '/view-final-copy.php?id='. $id;
            $mail->Body = '<p><a href="' . $message . '">Click here to view the copy</a></p>';
            
            // Set email subject
            $mail->Subject = $subject;
            
            
                        $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';




            // Send email
            if ($mail->send()) {
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "Email sent!";
            } else {
                throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
    } catch (Exception $e) {
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;


}


//storage
function calculateStorageUsagePercentage($path) {
    $totalSpace = disk_total_space($path);
    $freeSpace = disk_free_space($path);
    $usedSpace = $totalSpace - $freeSpace;

    $percentage = ($usedSpace / $totalSpace) * 100;

    return $percentage;
}

function getTotalSpaceGB($path){
    return round(disk_total_space($path) / (1024 * 1024 * 1024), 2); // Convert bytes to gigabytes
}

function getFreeSpaceGB($path){
    return round(disk_free_space($path) / (1024 * 1024 * 1024), 2); // Convert bytes to gigabytes
}






function sendEmailStorageWarning($email){

    require 'vendor/autoload.php';
    $response = array();
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $Exception = new PHPMailer\PHPMailer\Exception();

        $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'server301.orangehost.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        $mail->setFrom($adminEmail);
        $mail->addAddress($email);
                
        
            $mail->isHTML(true);
            $subject = 'AEHR System Storage is greater than 80%';
            

                  $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';



            // Set email subject
            $mail->Subject = $subject;
            

            // Send email
            if ($mail->send()) {
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "Email sent!";
            } else {
                throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
    } catch (Exception $e) {
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;


}


function sendEmailNotificationToNewUser($email,$password){
    require 'vendor/autoload.php';
    $response = array();
    try {
        // Create a PHPMailer object
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $Exception = new PHPMailer\PHPMailer\Exception();

        $adminEmail = 'das@atsphdas.com';
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'mail.atsphdas.com ';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;  // Your email
        $mail->Password = '81QI*FRL8{uW';  // App password created for Gmail
        $mail->Port = 465;  // SMTP port
        $mail->SMTPSecure = 'ssl';

        // Sender information
        
        $mail->setFrom($adminEmail);
        
        
        $mail->addAddress($email);
                
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            
            $mail->isHTML(true);
            $subject = 'AEHR System here\'s your account information.';

            $mail->Body .= '<p>Login: '.$baseUrl.'</p>';
            $mail->Body .= '<p>Email: '.$email.'</p>';
            $mail->Body .= '<p>Password: '.$password.'</p>';
           
 
      
            // Set email subject
            $mail->Subject = $subject;
            

            $mail->Body .= '<p><img src="atsphdas.com/images/ATS-PH-LOGO.png" width="301" height="72" /></p>
            <p>Ground Floor Office Center 08A-E</p>
            <p>Berthaphil III Clark Center, Jose Abad Santos Ave.</p>
            <p>Clark Freeport Zone, Pampanga 2023, Philippines</p>
            <p>(045) 499-4671</p>';



            // Send email
            if ($mail->send()) {
                $status = 1;
                $response['status'] = $status;
                $response['message'] = "Email sent!";

                
            } else {
                throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
    } catch (Exception $e) {
        $status = 0;
        $response['status'] = $status;
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;
}







?>
