<?php
// Enhanced upload.php - Handles file upload with database integration
session_start();
require_once 'config/database.php';

$uploadDir = __DIR__ . '/uploads/';
$message = "";
$messageType = "success";

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $description = trim($_POST['description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $categoryId = !empty($_POST['category']) ? intval($_POST['category']) : null;

    // Check for upload errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Validate file type
        $allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain'
        ];

        // Get file info
        $originalFilename = $file['name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        $fileExtension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        
        // Additional MIME type check based on extension
        $extensionMimeMap = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain'
        ];

        // Validate file type by extension and MIME type
        $isValidType = in_array($fileType, $allowedTypes) || 
                      (isset($extensionMimeMap[$fileExtension]) && 
                       in_array($extensionMimeMap[$fileExtension], $allowedTypes));

        if ($isValidType) {
            // Check file size (max 50MB)
            $maxFileSize = 50 * 1024 * 1024; // 50MB
            if ($fileSize > $maxFileSize) {
                $message = "File is too large. Maximum file size is 50MB.";
                $messageType = "error";
            } else {
                // Generate unique filename to prevent conflicts
                $uniqueFilename = time() . '_' . uniqid() . '_' . $originalFilename;
                $targetPath = $uploadDir . $uniqueFilename;
                
                // Calculate file hash for duplicate detection
                $fileHash = hash_file('sha256', $file['tmp_name']);
                
                try {
                    // Check if file already exists based on hash
                    $duplicateStmt = $pdo->prepare("SELECT filename, original_filename FROM documents WHERE file_hash = :hash");
                    $duplicateStmt->execute([':hash' => $fileHash]);
                    $duplicate = $duplicateStmt->fetch();
                    
                    if ($duplicate) {
                        $message = "A file with identical content already exists: " . $duplicate['original_filename'];
                        $messageType = "error";
                    } else {
                        // Move uploaded file
                        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                            // Insert document record into database
                            $insertStmt = $pdo->prepare("
                                INSERT INTO documents (filename, original_filename, file_size, file_type, description, tags, file_hash) 
                                VALUES (:filename, :original_filename, :file_size, :file_type, :description, :tags, :file_hash)
                            ");
                            
                            $insertResult = $insertStmt->execute([
                                ':filename' => $uniqueFilename,
                                ':original_filename' => $originalFilename,
                                ':file_size' => $fileSize,
                                ':file_type' => $fileType,
                                ':description' => $description,
                                ':tags' => $tags,
                                ':file_hash' => $fileHash
                            ]);
                            
                            if ($insertResult) {
                                $documentId = $pdo->lastInsertId();
                                
                                // Add category relationship if specified
                                if ($categoryId) {
                                    $categoryStmt = $pdo->prepare("
                                        INSERT INTO document_category_relations (document_id, category_id) 
                                        VALUES (:document_id, :category_id)
                                    ");
                                    $categoryStmt->execute([
                                        ':document_id' => $documentId,
                                        ':category_id' => $categoryId
                                    ]);
                                }
                                
                                // Auto-categorize based on file type if no category specified
                                if (!$categoryId) {
                                    $autoCategoryMap = [
                                        'application/pdf' => 'PDFs',
                                        'image/jpeg' => 'Images',
                                        'image/jpg' => 'Images',
                                        'image/png' => 'Images',
                                        'image/gif' => 'Images',
                                        'application/msword' => 'Word Documents',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word Documents',
                                        'application/vnd.ms-excel' => 'Spreadsheets',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Spreadsheets',
                                        'application/vnd.ms-powerpoint' => 'Presentations',
                                        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'Presentations'
                                    ];
                                    
                                    if (isset($autoCategoryMap[$fileType])) {
                                        $autoCategory = $autoCategoryMap[$fileType];
                                        $autoCategoryStmt = $pdo->prepare("
                                            SELECT id FROM document_categories WHERE name = :name
                                        ");
                                        $autoCategoryStmt->execute([':name' => $autoCategory]);
                                        $autoCategoryResult = $autoCategoryStmt->fetch();
                                        
                                        if ($autoCategoryResult) {
                                            $categoryStmt = $pdo->prepare("
                                                INSERT INTO document_category_relations (document_id, category_id) 
                                                VALUES (:document_id, :category_id)
                                            ");
                                            $categoryStmt->execute([
                                                ':document_id' => $documentId,
                                                ':category_id' => $autoCategoryResult['id']
                                            ]);
                                        }
                                    }
                                }
                                
                                $message = "File '{$originalFilename}' uploaded successfully!";
                                $messageType = "success";
                            } else {
                                // Delete uploaded file if database insert failed
                                unlink($targetPath);
                                $message = "File uploaded but failed to save to database.";
                                $messageType = "error";
                            }
                        } else {
                            $message = "Error uploading file. Please check file permissions.";
                            $messageType = "error";
                        }
                    }
                } catch (PDOException $e) {
                    // Delete uploaded file if there was a database error
                    if (file_exists($targetPath)) {
                        unlink($targetPath);
                    }
                    $message = "Database error: " . $e->getMessage();
                    $messageType = "error";
                }
            }
        } else {
            $allowedExtensions = ['PDF', 'JPEG', 'JPG', 'PNG', 'GIF', 'DOC', 'DOCX', 'XLS', 'XLSX', 'PPT', 'PPTX', 'TXT'];
            $message = "Invalid file type. Only " . implode(', ', $allowedExtensions) . " files are allowed.";
            $messageType = "error";
        }
    } else {
        // Handle upload errors
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
        ];
        
        $message = isset($uploadErrors[$file['error']]) ? $uploadErrors[$file['error']] : "Unknown upload error occurred.";
        $messageType = "error";
    }
} else {
    $message = "No file was selected for upload.";
    $messageType = "error";
}

// Store message in session for display
$_SESSION['message'] = $message;
$_SESSION['messageType'] = $messageType;

// Redirect back to the index page
header("Location: index.php");
exit();
?>