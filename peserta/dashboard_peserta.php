<?php
session_start(); // Selalu mulai session di paling atas

// Periksa apakah pengguna sudah login DAN memiliki role 'peserta'
// Saya menyarankan menggunakan user_id sebagai indikator utama login karena email bisa berubah
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'peserta') {
    // Jika tidak login atau role bukan 'peserta', set pesan error dan arahkan kembali ke halaman utama (index.php)
    // index.php sekarang menjadi halaman login utama Anda.
    $_SESSION['pesan_error'] = "Anda tidak memiliki akses ke halaman ini atau sesi Anda telah berakhir.";
    header("Location: ../index.php"); // Path relatif dari peserta/dashboard_peserta.php ke index.php
    exit;
}

// Sertakan koneksi database jika Anda perlu mengambil data spesifik dari database di dashboard ini
// Pastikan path ke db.php benar. Jika db.php ada di folder 'includes' yang sejajar dengan folder 'peserta', maka pathnya '../includes/db.php'
include "../includes/db.php"; 

// Data user dari session (pastikan ini di-set saat login di index.php)
$user_id = $_SESSION['user_id'] ?? 'N/A'; // Default jika tidak ada
$user_nama = $_SESSION['nama'] ?? 'Pengguna'; // Default jika tidak ada
$user_email = $_SESSION['email'] ?? 'N/A'; // Default jika tidak ada
$user_role = $_SESSION['role'] ?? 'N/A'; // Default jika tidak ada

// Anda bisa menambahkan query database di sini jika perlu mengambil data khusus peserta, contoh:
// $stmt = $koneksi->prepare("SELECT * FROM peserta_lomba WHERE user_id = ?");
// $stmt->bind_param("i", $user_id);
// $stmt->execute();
// $data_peserta = $stmt->get_result()->fetch_assoc();
// $stmt->close();

$koneksi->close(); // Tutup koneksi setelah selesai menggunakannya di halaman ini
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Peserta</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        /* CSS yang Anda berikan, saya biarkan tetap di sini atau bisa dipindahkan ke style.css */
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: linear-gradient(to right, #e0f7fa, #fce4ec);
            min-height: 100vh;
        }

        /* NAVBAR */
        .navbar {
            background-color: #2d7dff;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 30px;
        }

        .navbar .logo {
            font-weight: bold;
            font-size: 18px;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .nav-links li a {
            text-decoration: none;
            color: white;
            font-weight: 500;
        }

        .nav-links li a:hover {
            text-decoration: underline;
        }

        .logout-btn {
            background-color: white;
            color: #2d7dff;
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

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* KONTEN */
        .content {
            margin: 60px auto;
            background-color: white;
            padding: 40px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            text-align: center;
        }

        .content h2 {
            color: #2d7dff;
            margin-bottom: 10px;
        }

        .content p {
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">Project Lomba</div>
    <div class="navbar-right">
        <ul class="nav-links">
            <li><a href="dashboard_peserta.php">Dashboard</a></li> 
            <li><a href="daftar_lomba.php">Daftar Lomba</a></li>
            <li><a href="upload_karya.php">Upload Karya</a></li>
            <li><a href="pengumuman.php">Pengumuman</a></li>
        </ul>
        <a href="../logout.php" class="logout-btn">Logout</a> </div>
</div>

<div class="content">
    <h2>Halo, <?php echo htmlspecialchars($user_nama); ?>!</h2>
    <p>Selamat datang di Portal Dashboard Peserta Lomba</p>
    <p>Email Anda: <?php echo htmlspecialchars($user_email); ?></p>
    <p>Role Anda: <?php echo htmlspecialchars($user_role); ?></p>
</div>

</body>
</html>