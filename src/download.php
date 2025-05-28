<?php
// download.php - Handle file downloads with security
session_start();
require_once 'config/database.php';

// Configuration
$uploadDir = 'uploads/';

/**
 * Send file to browser with proper headers
 */
function sendFile($filePath, $originalFilename, $mimeType, $fileSize) {
    // Security check - ensure file exists and is readable
    if (!file_exists($filePath) || !is_readable($filePath)) {
        http_response_code(404);
        die('File not found.');
    }
    
    // Security check - ensure file is within upload directory
    $realPath = realpath($filePath);
    $uploadPath = realpath($uploadDir);
    if (strpos($realPath, $uploadPath) !== 0) {
        http_response_code(403);
        die('Access denied.');
    }
    
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers for file download
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $originalFilename . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    header('Pragma: public');
    
    // Handle range requests for large files (resume support)
    $size = $fileSize;
    $begin = 0;
    $end = $fileSize - 1;
    
    if (isset($_SERVER['HTTP_RANGE'])) {
        if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
            $begin = intval($matches[1]);
            if (!empty($matches[2])) {
                $end = intval($matches[2]);
            }
        }
    }
    
    if ($begin > 0 || $end < ($fileSize - 1)) {
        header('HTTP/1.1 206 Partial Content');
    } else {
        header('HTTP/1.1 200 OK');
    }
    
    header("Content-Range: bytes $begin-$end/$fileSize");
    header("Accept-Ranges: bytes");
    header('Content-Length: ' . ($end - $begin + 1));
    
    // Output file content
    $handle = fopen($filePath, 'rb');
    if ($handle === false) {
        http_response_code(500);
        die('Cannot open file.');
    }
    
    // Seek to start position
    if ($begin > 0) {
        fseek($handle, $begin);
    }
    
    // Read and output file in chunks
    $chunkSize = 1024 * 8; // 8KB chunks
    $bytesRemaining = $end - $begin + 1;
    
    while ($bytesRemaining > 0 && !feof($handle)) {
        $chunkSize = min($chunkSize, $bytesRemaining);
        $chunk = fread($handle, $chunkSize);
        
        if ($chunk === false) {
            break;
        }
        
        echo $chunk;
        flush();
        
        $bytesRemaining -= strlen($chunk);
    }
    
    fclose($handle);
    exit;
}

// Validate request
if (!isset($_GET['file']) || empty($_GET['file'])) {
    $_SESSION['message'] = 'Error: No file specified for download.';
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
    // Look up file in database
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE filename = ? LIMIT 1");
    $stmt->execute([$requestedFilename]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$document) {
        $_SESSION['message'] = 'Error: File not found in database.';
        $_SESSION['messageType'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    $filePath = $document['file_path'];
    $originalFilename = $document['original_filename'];
    $mimeType = $document['mime_type'];
    $fileSize = $document['file_size'];
    
    // Additional security check
    if (!file_exists($filePath)) {
        $_SESSION['message'] = 'Error: File not found on server.';
        $_SESSION['messageType'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    // Log download activity (optional)
    error_log("File downloaded: " . $originalFilename . " (" . $requestedFilename . ") by IP: " . $_SERVER['REMOTE_ADDR']);
    
    // Send file to user
    sendFile($filePath, $originalFilename, $mimeType, $fileSize);
    
} catch (PDOException $e) {
    error_log("Download error: " . $e->getMessage());
    $_SESSION['message'] = 'Error: Database error occurred while retrieving file.';
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}
?>