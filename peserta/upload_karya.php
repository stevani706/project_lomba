<?php
session_start(); // Selalu mulai session di paling atas

// Periksa apakah pengguna sudah login DAN memiliki role 'peserta'
// Menggunakan user_id sebagai indikator utama login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'peserta') {
    // Jika tidak login atau role bukan 'peserta', set pesan error dan arahkan kembali ke halaman utama (index.php)
    $_SESSION['pesan_error'] = "Anda tidak memiliki akses ke halaman ini atau sesi Anda telah berakhir.";
    header("Location: ../index.php"); // Path relatif dari peserta/upload_karya.php ke index.php
    exit;
}

// Sertakan koneksi database
// Pastikan path relatif dari peserta/upload_karya.php ke includes/db.php
include "../includes/db.php";

// Tangkap id_lomba dari URL jika ada
// Menggunakan FILTER_SANITIZE_NUMBER_INT untuk sanitasi input angka
$id_lomba_terpilih = isset($_GET['id_lomba']) ? filter_var($_GET['id_lomba'], FILTER_SANITIZE_NUMBER_INT) : '';

// Mendapatkan nama file saat ini untuk menentukan link aktif di navbar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Karya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: linear-gradient(to right, #dbeeff, #f5faff);
        }

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

        .nav-links li a:hover, .nav-links li a.active { /* Tambahkan .active di sini */
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

        .container {
            min-height: calc(100vh - 70px); /* Kurangi tinggi navbar */
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 20px; /* Tambah padding horizontal */
            box-sizing: border-box; /* Include padding in element's total width and height */
        }

        .upload-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .upload-box .emoji {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .upload-box h2 {
            color: #2d7dff;
            margin-bottom: 20px;
        }

        .upload-box input[type="text"],
        .upload-box select,
        .upload-box input[type="file"] {
            width: calc(100% - 24px); /* Kurangi padding dari lebar */
            padding: 12px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box; /* Include padding in element's total width and height */
        }

        .upload-box input[type="file"] {
            padding: 8px 12px; /* Lebih sedikit padding vertikal untuk file input */
        }

        .upload-box button {
            width: 100%;
            background-color: #2d7dff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s ease; /* Transisi hover */
        }

        .upload-box button:hover {
            background-color: #004bb5;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">Project Lomba</div>
    <div class="navbar-right">
        <ul class="nav-links">
            <li><a href="dashboard_peserta.php" class="<?php echo ($current_page == 'dashboard_peserta.php') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="daftar_lomba.php" class="<?php echo ($current_page == 'daftar_lomba.php') ? 'active' : ''; ?>">Daftar Lomba</a></li>
            <li><a href="upload_karya.php" class="<?php echo ($current_page == 'upload_karya.php') ? 'active' : ''; ?>">Upload Karya</a></li>
            <li><a href="pengumuman.php" class="<?php echo ($current_page == 'pengumuman.php') ? 'active' : ''; ?>">Pengumuman</a></li>
        </ul>
        <a href="../logout.php" class="logout-btn">Logout</a> </div>
</div>

<div class="container">
    <div class="upload-box">
        <div class="emoji">📤</div>
        <h2>Upload Karya Anda</h2>
        <form action="../proses/upload_karya.php" method="POST" enctype="multipart/form-data"> <select name="id_lomba" required>
                <option value="">-- Pilih Lomba --</option>
                <?php
                // Mengambil daftar lomba dari database
                $result = $koneksi->query("SELECT id, nama FROM lomba ORDER BY nama ASC"); // Ambil id dan nama
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Menggunakan 'id' sebagai nilai, dan 'nama' sebagai teks pilihan
                        // Pastikan 'id' di tabel lomba adalah primary key yang unik
                        $selected = ($row['id'] == $id_lomba_terpilih) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['id']) . '" ' . $selected . '>' . htmlspecialchars($row['nama']) . '</option>';
                    }
                } else {
                    echo '<option value="">Tidak ada lomba tersedia</option>';
                }
                ?>
            </select>
            <input type="text" name="judul_karya" placeholder="Judul Karya" required>
            <input type="file" name="file_karya" accept=".pdf,.jpg,.png,.docx,.zip" required>
            <button type="submit" name="upload">Upload</button>
        </form>
    </div>
</div>

<?php
$koneksi->close(); // Tutup koneksi database setelah selesai menggunakannya
?>
</body>
</html>