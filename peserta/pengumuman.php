<?php
session_start();

// Pastikan hanya peserta yang dapat mengakses halaman ini
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'peserta') {
    $_SESSION['pesan_error'] = "Anda tidak memiliki akses ke halaman ini atau sesi Anda telah berakhir.";
    header("Location: ../index.php");
    exit;
}

// BARIS 5: include db.php
include "../includes/db.php"; 

// --- PERBAIKAN: TAMBAHKAN PENGECEKAN KONEKSI ---
if (!isset($koneksi) || $koneksi->connect_error) {
    // Tambahkan log error atau tampilkan pesan yang lebih informatif jika koneksi gagal
    die("Koneksi database gagal: " . (isset($koneksi) ? $koneksi->connect_error : 'Variabel $koneksi tidak terdefinisi. Pastikan db.php benar dan ditemukan di lokasi yang benar (../includes/db.php).'));
}
// --- AKHIR PERBAIKAN ---

// Mendapatkan nama file saat ini untuk menentukan link aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Juara</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding-bottom: 20px; /* Tambahkan padding bottom */
        }

        /* Navbar styling (konsisten) */
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
            position: relative;
        }

        /* Hover effect untuk menu */
        .menu li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: white;
            left: 0;
            bottom: -5px;
            transition: width .3s ease-in-out;
        }
        .menu li a:hover::after,
        .menu li a.active::after {
            width: 100%;
        }
        .menu li a.active {
            font-weight: bold;
        }

        .logout-btn {
            background-color: white;
            color: #287bff;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.2s, transform 0.2s;
        }

        .logout-btn:hover {
            background-color: #e2e6ea;
            transform: translateY(-1px);
        }

        /* Kontainer Utama */
        .container { 
            max-width: 900px; 
            margin: 20px auto; 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }

        h1, h2 { 
            text-align: center; 
            color: #287bff; 
            margin-bottom: 30px; 
            font-size: 2.2em; /* Ukuran font lebih besar */
        }

        /* Gaya Tabel */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; /* Padding lebih besar */
            text-align: left; 
        }
        th { 
            background-color: #287bff; 
            color: white; 
            font-weight: 600; /* Lebih tebal */
        }
        tr:nth-child(even) { 
            background-color: #f2f2f2; 
        }
        tr:hover {
            background-color: #e8f0fe; /* Efek hover pada baris */
        }
        .no-results { 
            text-align: center; 
            color: #666; 
            padding: 20px; 
            margin-top: 20px;
            border: 1px dashed #ccc;
            border-radius: 8px;
            background-color: #fcfcfc;
        }

        /* Responsive */
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
            .container {
                width: 95%;
                padding: 20px;
                margin: 20px auto;
            }
            table {
                font-size: 0.9em;
            }
            th, td {
                padding: 8px;
            }
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
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="container"> 
    <h1>Pengumuman Juara (3 Teratas)</h1>

    <?php
    // Mengambil pengumuman pemenang dari database
    $query = $koneksi->query("
        SELECT 
            p.peringkat, 
            p.status_pemenang, 
            p.total_nilai,
            l.nama AS lomba_nama, 
            u.username AS peserta_nama, -- Sesuaikan 'username' ini jika nama kolom di tabel 'users' untuk nama adalah 'nama' atau 'full_name'
            k.judul_karya 
        FROM pemenang p
        JOIN karya k ON p.id_karya = k.id 
        JOIN lomba l ON k.id_lomba = l.id 
        JOIN users u ON k.email_peserta = u.email 
        ORDER BY 
            p.peringkat ASC, 
            p.total_nilai DESC 
        LIMIT 3 
    ");

    // Periksa apakah ada hasil
    if ($query && $query->num_rows > 0) {
        echo "<div class='table-responsive'>"; // Tambahkan div responsif
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Peringkat</th>";
        echo "<th>Status</th>";
        echo "<th>Lomba</th>";
        echo "<th>Judul Karya</th>";
        echo "<th>Peserta</th>";
        echo "<th>Total Nilai</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        // Menampilkan hasil pengumuman juara
        while ($data = $query->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($data['peringkat']) . "</td>";
            echo "<td>" . htmlspecialchars($data['status_pemenang']) . "</td>";
            echo "<td>" . htmlspecialchars($data['lomba_nama']) . "</td>";
            echo "<td>" . htmlspecialchars($data['judul_karya']) . "</td>";
            echo "<td>" . htmlspecialchars($data['peserta_nama']) . "</td>";
            echo "<td>" . number_format($data['total_nilai'], 2) . "</td>"; // Format nilai ke 2 desimal
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>"; // Tutup div responsif
    } else {
        echo '<p class="no-results">Belum ada pengumuman juara saat ini.</p>';
    }
    ?>
</div>

<?php
// Pastikan $koneksi ada sebelum ditutup
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $koneksi->close(); 
}
?>
</body>
</html>