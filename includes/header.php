<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Lomba</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">Portal Lomba</div>
    <div class="navbar-right">
        <ul class="nav-links">
            <li><a href="dashboard_peserta.php">Dashboard</a></li>
            <li><a href="upload_karya.php">Upload Karya</a></li>
            <li><a href="pengumuman.php">Pengumuman</a></li>
            <li><a href="admin/kelola_lomba.php">Daftar Lomba</a></li> <!-- Link ke kelola_lomba.php -->
        </ul>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<!-- End of Navbar -->
