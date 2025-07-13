<?php
session_start();

// Pastikan pengguna sudah login DAN memiliki role 'admin'
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika tidak login atau role bukan 'admin', set pesan error dan arahkan kembali ke halaman utama (login.php)
    $_SESSION['pesan_error'] = "Anda tidak memiliki akses ke halaman ini atau sesi Anda telah berakhir.";
    header("Location: ../login.php"); // Mengarahkan ke login.php di root
    exit;
}

include "../includes/db.php"; // Sesuaikan path koneksi database Anda

// Query untuk mendapatkan total lomba
$query_total_lomba = $koneksi->query("SELECT COUNT(*) AS total FROM lomba");
$total_lomba = $query_total_lomba->fetch_assoc()['total'];

// Query untuk mendapatkan total peserta
$query_total_peserta = $koneksi->query("SELECT COUNT(*) AS total FROM users WHERE role = 'peserta'");
$total_peserta = $query_total_peserta->fetch_assoc()['total'];

// Query untuk mendapatkan total juri
$query_total_juri = $koneksi->query("SELECT COUNT(*) AS total FROM users WHERE role = 'juri'");
$total_juri = $query_total_juri->fetch_assoc()['total'];

$koneksi->close(); // Tutup koneksi setelah semua data diambil
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4ff; /* Latar belakang yang lebih cerah */
            margin: 0;
            padding: 0;
        }

        /* Navbar Styling (Konsisten di semua halaman admin) */
        .navbar {
            background-color: #287bff;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar .logo {
            font-size: 20px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .navbar .menu {
            display: flex;
            gap: 25px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .navbar .menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 5px 0;
            position: relative;
        }

        .navbar .menu a:hover {
            text-decoration: none;
        }
        .navbar .menu a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: white;
            left: 0;
            bottom: -5px;
            transition: width .3s ease-in-out;
        }
        .navbar .menu a:hover::after,
        .navbar .menu a.active::after {
            width: 100%;
        }
        .navbar .menu a.active {
            font-weight: bold;
        }

        .logout-btn {
            background-color: white;
            color: #287bff;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.2s, transform 0.2s;
        }

        .logout-btn:hover {
            background-color: #e2e6ea;
            transform: translateY(-2px);
        }

        /* Konten Utama (Konsisten di semua halaman admin) */
        .content {
            width: 90%;
            max-width: 1200px;
            margin: 50px auto;
            padding: 40px;
            background-color: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            box-sizing: border-box;
        }

        .dashboard-header {
            padding: 0;
            border-radius: 0;
            box-shadow: none;
            margin-bottom: 40px;
            text-align: center;
        }

        .dashboard-header h1 {
            color: #287bff;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .dashboard-header p {
            color: #666;
            font-size: 1.1em;
        }

        /* Statistik Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: #f9f9f9;
            border: 1px solid #e0e6f0;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .stat-card h3 {
            color: #287bff;
            font-size: 1.4em;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 3em; 
            font-weight: bold;
            color: #333;
        }

        /* Quick Access */
        .quick-access-section {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            border: 1px solid #e0e6f0;
        }

        .quick-access-section h2 {
            color: #287bff;
            margin-bottom: 25px;
            font-size: 2em;
        }

        .quick-access-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap; 
            justify-content: center; 
            gap: 20px; 
        }

        .quick-access-section ul li a {
            color: white; 
            background-color: #3498db; 
            text-decoration: none;
            font-size: 1.1em;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            display: inline-block; 
        }

        .quick-access-section ul li a:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 15px 20px;
            }
            .navbar .menu {
                flex-wrap: wrap;
                justify-content: center;
                margin-top: 15px;
                gap: 15px;
            }
            .navbar .menu a {
                padding: 5px 10px;
            }
            .logout-btn {
                margin-top: 15px;
            }
            .content {
                width: 95%;
                padding: 20px;
                margin: 30px auto;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .quick-access-section ul {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="dashboard_admin.php" class="logo">Portal Lomba</a>
        <div class="navbar-right">
            <ul class="menu">
                <li><a href="dashboard_admin.php" class="active">Dashboard</a></li> <li><a href="kelola_lomba.php">Kelola Lomba</a></li>
                <li><a href="kelola_peserta.php">Kelola Peserta</a></li>
                <li><a href="kelola_juri.php">Kelola Juri</a></li>
                <li><a href="pengumuman.php">Pengumuman</a></li>
            </ul>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <h1>Selamat datang, Admin!</h1>
            <p>Ini adalah dashboard utama untuk mengelola lomba, peserta, juri, dan pengumuman.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Lomba</h3>
                <p class="value"><?php echo $total_lomba; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Peserta</h3>
                <p class="value"><?php echo $total_peserta; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Juri</h3>
                <p class="value"><?php echo $total_juri; ?></p>
            </div>
        </div>

        <div class="quick-access-section">
            <h2>Akses Cepat</h2>
            <ul>
                <li><a href="kelola_lomba.php">Kelola Lomba</a></li>
                <li><a href="kelola_peserta.php">Kelola Peserta</a></li>
                <li><a href="kelola_juri.php">Kelola Juri</a></li>
                <li><a href="pengumuman.php">Pengumuman</a></li>
            </ul>
        </div>
    </div>

</body>
</html>