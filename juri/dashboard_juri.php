<?php
session_start();

// Pastikan hanya juri yang dapat mengakses halaman ini
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'juri') {
    header("Location: ../login.php");
    exit;
}

include "../includes/db.php"; // Pastikan path koneksi benar sesuai struktur folder

// Mendapatkan nama file saat ini untuk menandai link aktif di navbar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Juri</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4ff;
            margin: 0;
        }

        .navbar {
            background-color: #287bff;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo {
            font-size: 18px;
            font-weight: bold;
        }

        .navbar .menu {
            display: flex;
            gap: 20px;
        }

        .navbar .menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }

        .navbar .menu a:hover,
        .navbar .menu a.active {
            text-decoration: underline;
        }

        .logout-btn {
            background-color: white;
            color: #287bff;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #e2e6ea;
            color: #0056b3;
        }

        .content {
            padding: 40px;
            text-align: center;
        }

        .content h2 {
            color: #287bff;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            margin: 20px auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .card p {
            font-size: 16px;
            color: #333;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">Portal Lomba</div>
    <div class="menu">
        <a href="dashboard_juri.php" class="<?php echo ($current_page == 'dashboard_juri.php') ? 'active' : ''; ?>">Dashboard</a>
        <a href="penilaian.php" class="<?php echo ($current_page == 'penilaian.php') ? 'active' : ''; ?>">Penilaian</a>
        <a href="pengumuman.php" class="<?php echo ($current_page == 'pengumuman.php') ? 'active' : ''; ?>">Pengumuman</a>
    </div>
    <a href="../logout.php" class="logout-btn">Logout</a>
</div>

<!-- CONTENT -->
<div class="content">
    <h2>Selamat Datang, <?php echo $_SESSION['nama']; ?>!</h2>
    <div class="card">
        <p>Anda login sebagai <strong>Juri</strong>.</p>
        <p>Gunakan menu di atas untuk menilai karya peserta atau melihat pengumuman pemenang.</p>
    </div>
</div>

</body>
</html>
