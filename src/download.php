<?php
// download.php
session_start();
require_once 'config/database.php';

if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filePath = __DIR__ . '/uploads/' . $file;

    if (file_exists($filePath)) {
        // Set headers to initiate download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
    }
} else {
    echo "No file specified.";
}
?>