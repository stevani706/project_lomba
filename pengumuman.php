<?php
session_start();

// Pastikan hanya peserta yang dapat mengakses halaman ini
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'peserta') {
    header("Location: login.php");
    exit;
}

include "includes/db.php";  // Pastikan koneksi.php ada di folder 'includes'

// Mendapatkan nama file saat ini untuk menentukan link aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Juara</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f9fc;
            margin: 0;
        }

        /* Navbar styling */
        .navbar {
            background-color: #287bff;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }

        .logo {
            font-weight: bold;
            font-size: 20px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
        }

        .menu {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .menu li a {
            color: white;
            font-weight: 500;
            text-decoration: none;
        }

        .menu li a:hover, .menu li a.active {
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

        /* Content styling */
        .content {
            padding: 40px;
            width: 90%;
            max-width: 1200px;
            margin: auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        .content h2 {
            text-align: center;
            color: #287bff;
            margin-bottom: 30px;
        }

        .pengumuman-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .pengumuman-card {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .pengumuman-card:hover {
            transform: scale(1.05);
        }

        .pengumuman-card h4 {
            color: #287bff;
            margin-bottom: 10px;
            font-size: 22px;
        }

        .pengumuman-card p {
            font-size: 16px;
            color: #444;
            margin-bottom: 10px;
        }

        .pengumuman-card .juara-1 {
            background-color: #ffd700;
            color: #fff;
        }

        .pengumuman-card .juara-2 {
            background-color: #c0c0c0;
            color: #fff;
        }

        .pengumuman-card .juara-3 {
            background-color: #cd7f32;
            color: #fff;
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            .pengumuman-card h4 {
                font-size: 18px;
            }

            .pengumuman-card p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">Portal Lomba</div>
    <div class="navbar-right">
        <ul class="menu">
            <li><a href="dashboard_peserta.php">Dashboard</a></li>
            <li><a href="daftar_lomba.php">Daftar Lomba</a></li>
            <li><a href="upload_karya.php">Upload Karya</a></li>
            <li><a href="pengumuman.php" class="active">Pengumuman</a></li>
        </ul>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<!-- KONTEN -->
<div class="content">
    <h2>Pengumuman Juara</h2>

    <div class="pengumuman-list">
        <?php
        // Mengambil pengumuman pemenang dari database
        $query = $koneksi->query("SELECT p.*, l.nama AS lomba, u.nama AS peserta 
                                  FROM pemenang p
                                  JOIN lomba l ON p.id_lomba = l.id
                                  JOIN users u ON p.id_peserta = u.id
                                  ORDER BY FIELD(p.posisi, 'Juara 1', 'Juara 2', 'Juara 3')");

        // Menampilkan hasil pengumuman juara
        while ($data = $query->fetch_assoc()) {
            $posisi_class = strtolower(str_replace(' ', '-', $data['posisi'])); // Membuat kelas berdasarkan posisi
            echo '
            <div class="pengumuman-card ' . $posisi_class . '">
                <h4>' . $data['posisi'] . ' - ' . $data['peserta'] . '</h4>
                <p>Lomba: ' . $data['lomba'] . '</p>
                <p><strong>Posisi:</strong> ' . $data['posisi'] . '</p>
            </div>';
        }
        ?>
    </div>
</div>

</body>
</html>
