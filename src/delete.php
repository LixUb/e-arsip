<?php
// Document deletion script (delete.php)
session_start();
require_once 'config/database.php';

if (isset($_GET['file'])) {
    $fileName = basename($_GET['file']);
    $filePath = __DIR__ . '/uploads/' . $fileName;

    // Check if the file exists
    if (file_exists($filePath)) {
        // Attempt to delete the file
        if (unlink($filePath)) {
            $_SESSION['message'] = "File '$fileName' deleted successfully.";
        } else {
            $_SESSION['message'] = "Error deleting file '$fileName'.";
        }
    } else {
        $_SESSION['message'] = "File '$fileName' does not exist.";
    }
}

// Redirect back to index page
header('Location: index.php');
exit;
?>