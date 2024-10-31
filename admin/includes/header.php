<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Gacha Accounts</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/27.1.0/classic/ckeditor.js"></script>
    <script src="assets/js/main.js" defer></script>
</head>
<body>
    <nav class="admin-nav">
        <div class="logo">
            <h1>Admin Panel</h1>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="games.php">Games</a></li>
            <li><a href="heroes.php">Heroes</a></li>
            <li><a href="accounts.php">Accounts</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="reviews.php">Reviews</a></li>
            <li><a href="promocodes.php">Promocodes</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="auth/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main class="admin-content">