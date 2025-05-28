<?php
// upload.php - Handle file uploads
session_start();
require_once 'config/database.php';

// Set maximum execution time and memory limit for large files
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

// Configuration
$uploadDir = 'uploads/';
$maxFileSize = 50 * 1024 * 1024; // 50MB
$allowedTypes = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'txt' => 'text/plain'
];

/**
 * Sanitize filename to prevent security issues
 */
function sanitizeFilename($filename) {
    // Remove path information and special characters
    $filename = basename($filename);
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
    return $filename;
}

/**
 * Generate unique filename to prevent conflicts
 */
function generateUniqueFilename($originalFilename) {
    $pathInfo = pathinfo($originalFilename);
    $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';
    $basename = isset($pathInfo['filename']) ? $pathInfo['filename'] : 'file';
    
    // Create unique filename with timestamp and random string
    $timestamp = date('Y-m-d_H-i-s');
    $randomString = substr(md5(uniqid(rand(), true)), 0, 8);
    
    return $basename . '_' . $timestamp . '_' . $randomString . ($extension ? '.' . $extension : '');
}

/**
 * Get human readable file size
 */
function getReadableFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Create upload directory if it doesn't exist
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $_SESSION['message'] = 'Error: Could not create upload directory.';
        $_SESSION['messageType'] = 'error';
        header('Location: index.php');
        exit;
    }
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = 'Error: Invalid request method.';
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}

// Validate file upload
if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File is too large (server limit).',
        UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit).',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension.'
    ];
    
    $error = $_FILES['document']['error'];
    $message = isset($errorMessages[$error]) ? $errorMessages[$error] : 'Unknown upload error.';
    
    $_SESSION['message'] = 'Error: ' . $message;
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}

$uploadedFile = $_FILES['document'];
$originalFilename = sanitizeFilename($uploadedFile['name']);
$fileSize = $uploadedFile['size'];
$fileTmpPath = $uploadedFile['tmp_name'];
$mimeType = $uploadedFile['type'];

// Additional form data
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
$categoryId = isset($_POST['category']) && !empty($_POST['category']) ? (int)$_POST['category'] : null;

// Validate file size
if ($fileSize > $maxFileSize) {
    $_SESSION['message'] = 'Error: File is too large. Maximum size is ' . getReadableFileSize($maxFileSize) . '.';
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}

// Validate file type
$fileExtension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
if (!isset($allowedTypes[$fileExtension])) {
    $_SESSION['message'] = 'Error: File type not allowed. Allowed types: ' . implode(', ', array_keys($allowedTypes)) . '.';
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}

// Additional MIME type validation
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$actualMimeType = finfo_file($finfo, $fileTmpPath);
finfo_close($finfo);

// Some basic MIME type validation (not foolproof, but helps)
$expectedMimeType = $allowedTypes[$fileExtension];
if ($actualMimeType !== $expectedMimeType && !in_array($actualMimeType, $allowedTypes)) {
    $_SESSION['message'] = 'Error: File content does not match its extension.';
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}

// Generate unique filename
$uniqueFilename = generateUniqueFilename($originalFilename);
$destinationPath = $uploadDir . $uniqueFilename;

// Move uploaded file
if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
    $_SESSION['message'] = 'Error: Failed to save uploaded file.';
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}

// Set file permissions
chmod($destinationPath, 0644);

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert document record
    $stmt = $pdo->prepare("
        INSERT INTO documents (filename, original_filename, file_path, file_size, file_type, mime_type, description, tags) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $uniqueFilename,
        $originalFilename,
        $destinationPath,
        $fileSize,
        $fileExtension,
        $actualMimeType,
        $description,
        $tags
    ]);
    
    if (!$result) {
        throw new Exception('Failed to insert document record.');
    }
    
    $documentId = $pdo->lastInsertId();
    
    // Link document to category if provided
    if ($categoryId) {
        $categoryStmt = $pdo->prepare("INSERT INTO document_category_relations (document_id, category_id) VALUES (?, ?)");
        $categoryResult = $categoryStmt->execute([$documentId, $categoryId]);
        
        if (!$categoryResult) {
            throw new Exception('Failed to link document to category.');
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['message'] = 'Document uploaded successfully! File: ' . htmlspecialchars($originalFilename) . ' (' . getReadableFileSize($fileSize) . ')';
    $_SESSION['messageType'] = 'success';
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollBack();
    
    // Delete uploaded file if database insert failed
    if (file_exists($destinationPath)) {
        unlink($destinationPath);
    }
    
    error_log("Upload error: " . $e->getMessage());
    $_SESSION['message'] = 'Error: Failed to save document information. ' . $e->getMessage();
    $_SESSION['messageType'] = 'error';
}

// Redirect back to main page
header('Location: index.php');
exit;
?>