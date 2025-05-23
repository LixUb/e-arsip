<?php
// Database connection settings
$host = 'localhost'; // Database host
$db   = 'document_archiving'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password
$charset = 'utf8mb4';

// Set up the Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Handle connection error
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>