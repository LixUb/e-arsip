<?php
// upload.php - Handles the file upload process

session_start();
require_once 'config/database.php';

$uploadDir = __DIR__ . '/uploads/';
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];

    // Check for upload errors
    if ($file['error'] === 0) {
        // Validate file type (you can customize this as needed)
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (in_array($file['type'], $allowedTypes)) {
            $target = $uploadDir . basename($file['name']);
            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $message = "File uploaded successfully.";
                // Optionally, you can save file metadata to the database here
            } else {
                $message = "Error uploading file.";
            }
        } else {
            $message = "Invalid file type. Only PDF, JPEG, PNG, and Word documents are allowed.";
        }
    } else {
        $message = "Error: " . $file['error'];
    }
}

// Redirect back to the index page with a message
header("Location: index.php?message=" . urlencode($message));
exit();
?>