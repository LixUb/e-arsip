<?php
// Enhanced document deletion script (delete.php)
session_start();
require_once 'config/database.php';

$message = "";
$messageType = "success";

if (isset($_GET['file'])) {
    $fileName = basename($_GET['file']);
    $filePath = __DIR__ . '/uploads/' . $fileName;

    try {
        // First, get document info from database
        $stmt = $pdo->prepare("SELECT id, filename, original_filename FROM documents WHERE filename = :filename");
        $stmt->execute([':filename' => $fileName]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($document) {
            // Begin transaction for data consistency
            $pdo->beginTransaction();

            try {
                // Delete category relationships first (foreign key constraint)
                $deleteRelationsStmt = $pdo->prepare("DELETE FROM document_category_relations WHERE document_id = :document_id");
                $deleteRelationsStmt->execute([':document_id' => $document['id']]);

                // Delete document record from database
                $deleteDocStmt = $pdo->prepare("DELETE FROM documents WHERE id = :id");
                $deleteResult = $deleteDocStmt->execute([':id' => $document['id']]);

                if ($deleteResult) {
                    // Commit database transaction
                    $pdo->commit();

                    // Now try to delete the physical file
                    if (file_exists($filePath)) {
                        if (unlink($filePath)) {
                            $message = "Document '{$document['original_filename']}' deleted successfully.";
                            $messageType = "success";
                        } else {
                            $message = "Document removed from database, but physical file could not be deleted.";
                            $messageType = "success"; // Still consider success since DB is clean
                        }
                    } else {
                        $message = "Document '{$document['original_filename']}' removed from database (file was already missing).";
                        $messageType = "success";
                    }
                } else {
                    $pdo->rollBack();
                    $message = "Error removing document from database.";
                    $messageType = "error";
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Error deleting document: " . $e->getMessage();
                $messageType = "error";
            }
        } else {
            // Document not found in database, check if physical file exists
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    $message = "Orphaned file '$fileName' deleted successfully.";
                    $messageType = "success";
                } else {
                    $message = "Error deleting orphaned file '$fileName'.";
                    $messageType = "error";
                }
            } else {
                $message = "Document '$fileName' does not exist.";
                $messageType = "error";
            }
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $messageType = "error";
    }
} else {
    $message = "No file specified for deletion.";
    $messageType = "error";
}

// Store message in session
$_SESSION['message'] = $message;
$_SESSION['messageType'] = $messageType;

// Redirect back to index page
header('Location: index.php');
exit;
?>