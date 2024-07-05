<?php 
    // Include database connection
    include '../dbconnect.php';
    
    try {
        // Decrement duration_delete by 1 for all records
        $sql = "UPDATE pdfs SET duration_delete = duration_delete - 1";
        
        // Execute the SQL statement
        $stmt = $pdo->prepare($sql);

        // Check if the statement executed successfully
        if ($stmt->execute()) {
            // Select records where duration_delete equals or less than 0
            $selectSql = "SELECT pdf_path FROM pdfs WHERE duration_delete <= 0";
            $selectStmt = $pdo->prepare($selectSql);
            $selectStmt->execute();
            $paths = $selectStmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete records where duration_delete equals or less than 0
            $deleteSql = "DELETE FROM pdfs WHERE duration_delete <= 0";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->execute();

            // Unlink files
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            echo 1; // Success
        } else {
            echo 0; // Failure
        }
    } catch (PDOException $e) {
        // Handle any errors
        echo "Error: " . $e->getMessage();
    }
?>
