<?php
// Check if the action parameter is set
session_start();

if(!isset($_SESSION['user_id'])) {
    echo 0;
    return;
}


if(isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Perform different actions based on the action parameter
    switch($action) {
        case 'view':
            // Handle view action
            // Retrieve the PDF ID from the URL
            $pdf_id = isset($_GET['id']) ? $_GET['id'] : null;
            if($pdf_id) {
                // Implement logic to view the PDF with the specified ID
                // For example:
                // echo "Viewing PDF with ID: " . $pdf_id;
            }
            break;
            case 'download':
                $pdf_id = isset($_GET['id']) ? $_GET['id'] : null;
                if ($pdf_id) {
                    // Fetch PDF paths from the database
                    $files = getPdfPathsById($pdf_id);
                    
                    // Check if PDF paths are fetched successfully
                    if ($files !== false) {
                        // Unserialize the array of file paths
                        $pdf_paths = unserialize($files);
            
                        // Initialize array to hold files
                        $filesToZip = [];
            
                        // Loop through each path
                        foreach ($pdf_paths as $path) {
                            // Add the path to the array of files to zip
                            $filesToZip[] = $path;
                        }
            
                        // Initialize ZipArchive
                        $zip = new ZipArchive();
            
                        // Create a unique name for the zip file (based on current date)
                        $zipFileName = date('d-m-Y') . '.zip';
            
                        // Open the zip file
                        if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
                            // Add each file to the zip
                            foreach ($filesToZip as $file) {
                                if (file_exists($file)) {
                                    $zip->addFile($file, substr(basename($file), 11)); // Add file to zip with basename
                                } else {
                                    echo 'File not found: ' . $file;
                                }
                            }
                            // Close the zip
                            $zip->close();
            
                            // Set headers to force download the zip file
                            header('Content-Type: application/zip');
                            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
                            header('Content-Length: ' . filesize($zipFileName));
                            readfile($zipFileName);
            
                            // Remove the zip file after download
                            unlink($zipFileName);
            
                            exit();
                        } else {
                            echo 'Failed to create zip file';
                        }
                    } else {
                        echo 'Failed to fetch PDF paths from the database';
                    }
                } else {
                    echo 'Please provide a valid PDF ID';
                }
                break;
            
        case 'delete':
            // Handle delete action
            // Retrieve the PDF ID from the URL
            $pdf_id = isset($_GET['id']) ? $_GET['id'] : null;
            if($pdf_id) {
               //first delete the files
               // then delete in database
               $files = getPdfPathsById($pdf_id);
               $pdf_paths = unserialize($files);
               foreach ($pdf_paths as $path) {
                    unlink($path);
               }

               include 'dbconnect.php';
               $stmt = $pdo->prepare("DELETE FROM pdfs WHERE pdf_id = :pdf_id");    
               $stmt->bindParam(':pdf_id', $pdf_id, PDO::PARAM_INT);
               $stmt->execute();

              
                header("Location: ../dashboard.php"); // Redirect to index.php after deletion
                exit();

            }
            break;
        case 'cancel':
            // Handle cancel action
            // Retrieve the PDF ID and reason from the URL
            $pdf_id = isset($_GET['id']) ? $_GET['id'] : null;
            $reason = isset($_GET['reason']) ? $_GET['reason'] : null;
            
            if ($pdf_id && $reason) {
                include 'dbconnect.php';
                // Prepare the SQL statement
                $stmt = $pdo->prepare("UPDATE `pdfs` SET `is_canceled` = '1', `cancel_reason` = :reason WHERE `pdf_id` = :pdf_id");
                // Bind parameters
                $stmt->bindParam(':pdf_id', $pdf_id, PDO::PARAM_INT);
                $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
                // Execute the statement
                $stmt->execute();
                
                include 'function.php';
                sendCancelReasontoEmails($pdf_id, $reason);
                
                
                //then delete
                
                $files = getPdfPathsById($pdf_id);
               $pdf_paths = unserialize($files);
               foreach ($pdf_paths as $path) {
                    unlink($path);
               }

               include 'dbconnect.php';
               $stmt = $pdo->prepare("DELETE FROM pdfs WHERE pdf_id = :pdf_id");    
               $stmt->bindParam(':pdf_id', $pdf_id, PDO::PARAM_INT);
               $stmt->execute();

              
                header("Location: ../dashboard.php"); // Redirect to index.php after deletion
                exit();
                
                
                
                
            }

            break;
        default:
            // Handle invalid action
            echo "Invalid action specified.";
            break;
    }
} else {
    // No action parameter provided
    echo "No action specified.";
}



function getPdfPathsById($pdf_id) {
    // Assuming you have a PDO database connection established

    // Your SQL query to retrieve PDF paths based on PDF ID
    $sql = "SELECT pdf_path FROM pdfs WHERE pdf_id = :pdf_id";

    try {
        // Prepare the SQL statement
        include 'dbconnect.php';
        $stmt = $pdo->prepare($sql);

        // Bind the PDF ID parameter
        $stmt->bindParam(':pdf_id', $pdf_id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Fetch PDF paths from the result set
        $pdf_paths = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $pdf_paths_string = implode(',', $pdf_paths);

        return $pdf_paths_string;  

    } catch (PDOException $e) {
        // Handle any database errors
        // For example, you can log the error or display a generic error message
        echo "Error: " . $e->getMessage();
        return false; // Return false to indicate failure
    }
}




?>
