<?php
// delete.php - Handle file deletion with security
session_start();
require_once 'config/database.php';

// Validate request
if (!isset($_GET['file']) || empty($_GET['file'])) {
    $_SESSION['message'] = 'Error: No file specified for deletion.';
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}

$requestedFilename = $_GET['file'];

// Security: Sanitize the filename
$requestedFilename = basename($requestedFilename);
if (empty($requestedFilename) || $requestedFilename === '.' || $requestedFilename === '..') {
    $_SESSION['message'] = 'Error: Invalid file name.';
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Look up file in database
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE filename = ? LIMIT 1");
    $stmt->execute([$requestedFilename]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$document) {
        throw new Exception('File not found in database.');
    }
    
    $filePath = $document['file_path'];
    $originalFilename = $document['original_filename'];
    $documentId = $document['id'];
    
    // Delete category relations first (foreign key constraint)
    $deleteCategoryRelations = $pdo->prepare("DELETE FROM document_category_relations WHERE document_id = ?");
    $deleteCategoryRelations->execute([$documentId]);
    
    // Delete document record from database
    $deleteDocument = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $result = $deleteDocument->execute([$documentId]);
    
    if (!$result) {
        throw new Exception('Failed to delete document from database.');
    }
    
    // Delete physical file
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            // If we can't delete the file, log the error but don't fail the operation
            error_log("Warning: Could not delete physical file: " . $filePath);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Log deletion activity
    error_log("File deleted: " . $originalFilename . " (" . $requestedFilename . ") by IP: " . $_SERVER['REMOTE_ADDR']);
    
    $_SESSION['message'] = 'Document deleted successfully: ' . htmlspecialchars($originalFilename);
    $_SESSION['messageType'] = 'success';
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollBack();
    
    error_log("Delete error: " . $e->getMessage());
    $_SESSION['message'] = 'Error: Failed to delete document. ' . $e->getMessage();
    $_SESSION['messageType'] = 'error';
}

// Redirect back to main page
header('Location: index.php');
exit;
?>