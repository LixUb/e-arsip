<?php
// Enhanced Document Archiving Application (index.php)
session_start();
require_once 'config/database.php';

$message = "";
$messageType = "success";

// Handle session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = isset($_SESSION['messageType']) ? $_SESSION['messageType'] : 'success';
    unset($_SESSION['message'], $_SESSION['messageType']);
}

// Handle URL messages
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $messageType = strpos($message, 'Error') !== false ? 'error' : 'success';
}

// Fetch documents with enhanced query
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';

try {
    $sql = "SELECT d.*, GROUP_CONCAT(dc.name) as categories, GROUP_CONCAT(dc.color) as category_colors 
            FROM documents d 
            LEFT JOIN document_category_relations dcr ON d.id = dcr.document_id 
            LEFT JOIN document_categories dc ON dcr.category_id = dc.id";
    
    $conditions = [];
    $params = [];
    
    if (!empty($searchTerm)) {
        $conditions[] = "(d.filename LIKE :search OR d.original_filename LIKE :search OR d.description LIKE :search OR d.tags LIKE :search)";
        $params[':search'] = "%$searchTerm%";
    }
    
    if (!empty($categoryFilter)) {
        $conditions[] = "dc.name = :category";
        $params[':category'] = $categoryFilter;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " GROUP BY d.id ORDER BY d.upload_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch categories for filter dropdown
    $categoriesStmt = $pdo->query("SELECT * FROM document_categories ORDER BY name");
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $message = "Error fetching documents: " . $e->getMessage();
    $messageType = "error";
    $documents = [];
    $categories = [];
}

// Helper function to get file icon class
function getFileIcon($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $iconMap = [
        'pdf' => 'pdf',
        'doc' => 'doc', 'docx' => 'doc',
        'jpg' => 'jpg', 'jpeg' => 'jpg', 'png' => 'jpg', 'gif' => 'jpg',
        'xls' => 'xls', 'xlsx' => 'xls',
        'ppt' => 'ppt', 'pptx' => 'ppt',
        'txt' => 'txt'
    ];
    return isset($iconMap[$extension]) ? $iconMap[$extension] : 'default';
}

// Helper function to format file size
function formatFileSize($bytes) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Archive - Modern File Management</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233b82f6'><path d='M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z' /></svg>">
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header class="header">
            <h1>Document Archive</h1>
            <p>Organize, store, and manage your digital documents with elegance</p>
        </header>

        <!-- Message Display -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType === 'error' ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Upload Section -->
        <section class="upload-section">
            <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                <div class="file-input-wrapper">
                    <input type="file" name="document" class="file-input" id="fileInput" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.ppt,.pptx,.txt" required>
                    <div class="file-input-display" id="fileInputDisplay">
                        <svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <div class="file-input-text">Click to select a file or drag and drop</div>
                        <div class="file-input-subtext">Supports PDF, Word, Images, Excel, PowerPoint, and Text files</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea name="description" id="description" class="form-textarea" placeholder="Brief description of the document..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tags" class="form-label">Tags (Optional)</label>
                        <input type="text" name="tags" id="tags" class="form-input" placeholder="Enter tags separated by commas">
                        <small style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">e.g., invoice, 2024, important</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category" class="form-label">Category</label>
                        <select name="category" id="category" class="form-select">
                            <option value="">Select a category...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; align-items: end;">
                        <button type="submit" class="btn btn-primary" id="uploadBtn">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Upload Document
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Documents Section -->
        <section class="documents-section">
            <div class="section-header">
                <h2 class="section-title">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Archived Documents (<?php echo count($documents); ?>)
                </h2>
                <div class="search-filter">
                    <form method="GET" style="display: flex; gap: 1rem; align-items: center;">
                        <input type="text" name="search" class="search-input" placeholder="Search documents..." 
                               value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <select name="category" class="form-select" style="width: 200px;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>" 
                                        <?php echo $categoryFilter === $category['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-secondary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search
                        </button>
                        <?php if (!empty($searchTerm) || !empty($categoryFilter)): ?>
                            <a href="index.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <?php if (empty($documents)): ?>
                <div class="empty-state">
                    <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3>No documents found</h3>
                    <p><?php echo !empty($searchTerm) || !empty($categoryFilter) ? 'Try adjusting your search criteria or upload your first document to get started.' : 'Upload your first document to get started with organizing your digital archive.'; ?></p>
                </div>
            <?php else: ?>
                <div class="documents-grid">
                    <?php foreach ($documents as $document): ?>
                        <div class="document-card">
                            <div class="document-header">
                                <div class="document-icon <?php echo getFileIcon($document['filename']); ?>">
                                    <?php echo strtoupper(pathinfo($document['filename'], PATHINFO_EXTENSION)); ?>
                                </div>
                                <div class="document-actions">
                                    <a href="download.php?file=<?php echo urlencode($document['filename']); ?>" 
                                       class="btn btn-success" title="Download">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </a>
                                    <a href="delete.php?file=<?php echo urlencode($document['filename']); ?>" 
                                       class="btn btn-danger" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this document?')">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="document-title">
                                <?php echo htmlspecialchars($document['original_filename'] ?: $document['filename']); ?>
                            </div>
                            
                            <div class="document-meta">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?php echo date('M j, Y \a\t g:i A', strtotime($document['upload_date'])); ?>
                                </div>
                                <?php if (!empty($document['file_size'])): ?>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <?php echo formatFileSize($document['file_size']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($document['file_type'])): ?>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <?php echo htmlspecialchars($document['file_type']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($document['description'])): ?>
                            <div class="document-description">
                                <?php echo htmlspecialchars($document['description']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($document['categories'])): ?>
                            <div class="document-tags">
                                <?php 
                                $categoryNames = explode(',', $document['categories']);
                                foreach ($categoryNames as $categoryName): 
                                ?>
                                    <span class="tag"><?php echo htmlspecialchars(trim($categoryName)); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($document['tags'])): ?>
                            <div class="document-tags">
                                <?php 
                                $tags = explode(',', $document['tags']);
                                foreach ($tags as $tag): 
                                ?>
                                    <span class="tag" style="background: #f3f4f6; color: #374151;"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
        // Enhanced file upload functionality
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('fileInput');
            const fileInputDisplay = document.getElementById('fileInputDisplay');
            const uploadForm = document.getElementById('uploadForm');
            const uploadBtn = document.getElementById('uploadBtn');

            // File input change handler
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    updateFileDisplay(file);
                }
            });

            // Drag and drop functionality
            fileInputDisplay.addEventListener('dragover', function(e) {
                e.preventDefault();
                fileInputDisplay.classList.add('dragover');
            });

            fileInputDisplay.addEventListener('dragleave', function(e) {
                e.preventDefault();
                fileInputDisplay.classList.remove('dragover');
            });

            fileInputDisplay.addEventListener('drop', function(e) {
                e.preventDefault();
                fileInputDisplay.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    fileInput.files = files;
                    updateFileDisplay(file);
                }
            });

            // Update file display
            function updateFileDisplay(file) {
                fileInputDisplay.classList.add('selected-file');
                fileInputDisplay.innerHTML = `
                    <svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="file-input-text">Selected: ${file.name}</div>
                    <div class="file-input-subtext">Size: ${formatFileSize(file.size)} • Type: ${file.type || 'Unknown'}</div>
                `;
            }

            // Format file size helper
            function formatFileSize(bytes) {
                if (bytes >= 1073741824) {
                    return (bytes / 1073741824).toFixed(2) + ' GB';
                } else if (bytes >= 1048576) {
                    return (bytes / 1048576).toFixed(2) + ' MB';
                } else if (bytes >= 1024) {
                    return (bytes / 1024).toFixed(2) + ' KB';
                } else {
                    return bytes + ' bytes';
                }
            }

            // Form submission handler
            uploadForm.addEventListener('submit', function(e) {
                const file = fileInput.files[0];
                if (!file) {
                    e.preventDefault();
                    alert('Please select a file to upload.');
                    return;
                }

                // Show loading state
                uploadBtn.innerHTML = `
                    <span class="spinner"></span>
                    Uploading...
                `;
                uploadBtn.disabled = true;
            });

            // Auto-hide messages after 5 seconds
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-20px)';
                    setTimeout(function() {
                        message.style.display = 'none';
                    }, 300);
                }, 5000);
            });

            // Search functionality
            const searchForm = document.querySelector('.search-filter form');
            if (searchForm) {
                const searchInput = searchForm.querySelector('input[name="search"]');
                let searchTimeout;

                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        if (searchInput.value.length >= 3 || searchInput.value.length === 0) {
                            searchForm.submit();
                        }
                    }, 500);
                });
            }
        });
    </script>
</body>
</html>