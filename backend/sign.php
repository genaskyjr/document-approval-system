<?php 
$response = array();
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: logout.php");
}else{

    $requestJsonModify_json = $_POST['jsonData'];
    $requestJsonModify = json_decode($requestJsonModify_json, true);

    // Check if JSON decoding was successful
    if ($requestJsonModify !== null) {
        // Iterate over each item in the decoded JSON array
        foreach ($requestJsonModify['data'] as $item) {
            // Access properties like 'pdfUrlKO' and 'modifiedPdfBase64' for each object
            $modifiedPdfBase64 = $item['modifiedPdfBase64'];
            $pdfUrlKO = $item['pdfUrlKO'];

            // Now you can use $pdfUrlKO and $modifiedPdfBase64 as needed
            // For example, you can save them to a database or perform other operations
            // Save PDF
            $base64_string = $modifiedPdfBase64;
            $binary_data = base64_decode($base64_string);
            $filename = str_replace('backend/', '', $pdfUrlKO);
            $file = fopen($filename, 'wb');
            fwrite($file, $binary_data);
            fclose($file);

        }
    }


    //send email to next
    include 'dbconnect.php';
    include 'function.php';

    //add current state then send email
    $id = $_POST['id'];

    $stmt = $pdo->prepare("SELECT `pdf_current_state`,`pdf_count_sign`, `pdf_sender` FROM `pdfs` WHERE `pdf_id` = :pdfId");
    $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);


    // if current state = count sign
    //sent email to recieve reciever final copy with sender

    
    $count_sign = $result['pdf_count_sign'];
    $current_state = $result['pdf_current_state'];

    $email_sender = $result['pdf_sender'];

    //send email notification that current user email is approved

    sendApprovedNotification($_SESSION['user_email'], $id);
    

    if($count_sign == $current_state){
        //send all need copy include sender
        
        sendAllNeedFinalCopy($id, $email_sender);
    
        //send email to sender that the document is complete
 
        sendEmailDocumentIsComplete($email_sender,$id);

        //set is_complete = 1
        $complete = 1;
        $stmt = $pdo->prepare("UPDATE `pdfs` SET `is_complete` = :newState WHERE `pdf_id` = :pdfId");
        $stmt->bindParam(':newState', $complete, PDO::PARAM_INT);
        $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
        $stmt->execute();

        
        echo 'end , send email final copy'.  $count_sign . $current_state;

        
        $response['id'] = $id;
        
    }else{
        $current_state = $current_state + 1;
        $stmt = $pdo->prepare("UPDATE `pdfs` SET `pdf_current_state` = :newState WHERE `pdf_id` = :pdfId");
        $stmt->bindParam(':newState', $current_state, PDO::PARAM_INT);
        $stmt->bindParam(':pdfId', $id, PDO::PARAM_INT);
        $stmt->execute();
        sendNeedToSign($id);
        echo $id;
        $response['id'] = $id;
    }
    

}





?>