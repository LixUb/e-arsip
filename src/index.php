<?php
// Document Archiving Application (index.php)
session_start();
require_once 'config/database.php';

$message = "";

// Fetch uploaded documents from the database
try {
    $stmt = $pdo->query("SELECT * FROM documents");
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching documents: " . $e->getMessage();
}

// Handle file upload form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: upload.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Archiving Application</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <h1>Document Archiving Application</h1>
    <?php if ($message) echo "<p><strong>$message</strong></p>"; ?>
    <form method="POST">
        <button type="submit">Upload Document</button>
    </form>
    <h2>Archived Documents</h2>
    <ul>
        <?php foreach ($documents as $document): ?>
            <li>
                <?php echo htmlspecialchars($document['filename']); ?>
                - <a href="download.php?file=<?php echo urlencode($document['filename']); ?>">Download</a>
                - <a href="delete.php?file=<?php echo urlencode($document['filename']); ?>">Delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>