<?php
session_start(); // Selalu mulai session di paling atas

// Pastikan hanya peserta yang dapat mengakses halaman ini
// Menggunakan user_id sebagai indikator utama login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'peserta') {
    $_SESSION['pesan_error'] = "Anda tidak memiliki akses ke halaman ini atau sesi Anda telah berakhir.";
    header("Location: ../index.php"); // Arahkan ke index.php (halaman login utama)
    exit;
}

// Sertakan koneksi database
// Pastikan path relatif dari peserta/daftar_lomba.php ke includes/db.php
include "../includes/db.php"; 

// Mendapatkan nama file saat ini untuk menentukan link aktif di navbar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Lomba</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Styling untuk navbar dan halaman */
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f2f4f8;
            margin: 0;
        }

        .navbar {
            background-color: #2d7dff;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 30px;
        }

        .logo {
            font-weight: bold;
            font-size: 18px;
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

        .content {
            padding: 40px;
            width: 90%;
            max-width: 1200px;
            margin: auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            margin-bottom: 50px; /* Tambahkan sedikit margin bawah */
        }

        .content h2 {
            text-align: center;
            color: #2d7dff;
            margin-bottom: 30px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .lomba-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
            transition: transform 0.2s;
            display: flex; /* Menggunakan flexbox untuk tata letak internal */
            flex-direction: column; /* Konten diatur secara kolom */
        }

        .lomba-card:hover {
            transform: scale(1.02);
        }

        .img-container {
            width: 100%;
            height: 160px;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden; /* Pastikan gambar tidak keluar dari container */
        }

        .img-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover; /* Mengisi container sambil mempertahankan rasio aspek */
        }

        .lomba-card h3 {
            margin: 10px;
            font-size: 18px;
            color: #2d7dff;
        }

        .lomba-card p {
            margin: 5px 10px;
            font-size: 14px;
            color: #444;
            flex-grow: 1; /* Biarkan paragraf deskripsi mengisi ruang yang tersedia */
        }

        .btn-daftar {
            display: inline-block;
            margin: 12px auto 18px;
            padding: 8px 18px;
            background-color: #2d7dff;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .btn-daftar:hover {
            background-color: #004bb5;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">Portal Lomba</div>
    <div class="navbar-right">
        <ul class="menu">
            <li><a href="dashboard_peserta.php" class="<?php echo ($current_page == 'dashboard_peserta.php') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="daftar_lomba.php" class="<?php echo ($current_page == 'daftar_lomba.php') ? 'active' : ''; ?>">Daftar Lomba</a></li>
            <li><a href="upload_karya.php" class="<?php echo ($current_page == 'upload_karya.php') ? 'active' : ''; ?>">Upload Karya</a></li>
            <li><a href="pengumuman.php" class="<?php echo ($current_page == 'pengumuman.php') ? 'active' : ''; ?>">Pengumuman</a></li>
        </ul>
        <a href="../logout.php" class="logout-btn">Logout</a> </div>
</div>

<div class="content">
    <h2>Daftar Lomba Tersedia</h2>

    <div class="grid-container">
        <?php
        // Mengambil data lomba dari database
        $query = $koneksi->query("SELECT * FROM lomba ORDER BY batas_pengumpulan ASC");

        // Periksa apakah ada lomba
        if ($query && $query->num_rows > 0) {
            // Menampilkan lomba dalam bentuk kartu
            while ($data = $query->fetch_assoc()) {
                // Pastikan path gambar relatif dari daftar_lomba.php ke folder assets/img di root
                // Jika gambar disimpan di C:\xampp\htdocs\project_lomba\assets\img\
                echo '
                <div class="lomba-card">
                    <div class="img-container">
                        <img src="../assets/img/' . htmlspecialchars($data['gambar']) . '" alt="' . htmlspecialchars($data['nama']) . '">
                    </div>
                    <h3>' . htmlspecialchars($data['nama']) . '</h3>
                    <p>' . htmlspecialchars($data['deskripsi']) . '</p>
                    <p><strong>Batas:</strong> ' . date("d M Y", strtotime($data['batas_pengumpulan'])) . '</p>
                    <a href="upload_karya.php?id_lomba=' . htmlspecialchars($data['id']) . '" class="btn-daftar">Daftar</a>
                </div>';
            }
        } else {
            echo '<p style="text-align: center; grid-column: 1 / -1;">Belum ada lomba yang tersedia saat ini.</p>';
        }
        ?>
    </div>
</div>

<?php
$koneksi->close(); // Tutup koneksi database setelah selesai menggunakannya
?>
</body>
</html>