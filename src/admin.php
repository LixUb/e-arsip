<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Admin Dashboard</h2>
        <p>Selamat datang di panel admin, <?php echo $_SESSION['username']; ?>!</p>
        
        <a href="index.php" class="btn btn-secondary">Kembali ke Beranda</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>
