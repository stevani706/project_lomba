<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Include koneksi database di awal, dan biarkan terbuka sampai akhir script
include "../includes/db.php"; 

$message = '';
$message_class = '';

// Menambahkan lomba baru
if (isset($_POST['tambah_lomba'])) {
    $nama_lomba = $_POST['nama_lomba'];
    $deskripsi = $_POST['deskripsi'];
    $batas_pengumpulan = $_POST['batas_pengumpulan'];
    
    // Proses upload gambar
    $gambar = $_FILES['gambar']['name'];
    $gambar_tmp = $_FILES['gambar']['tmp_name'];
    $gambar_size = $_FILES['gambar']['size'];
    $gambar_error = $_FILES['gambar']['error'];

    if ($gambar_error === 0) {
        $upload_dir = '../assets/img/';
        $gambar_destination = $upload_dir . basename($gambar);
        
        // Memeriksa ukuran file (maksimal 5MB)
        if ($gambar_size <= 5 * 1024 * 1024) {
            // Proses upload gambar
            if (move_uploaded_file($gambar_tmp, $gambar_destination)) {
                // Query untuk menyimpan lomba baru ke database
                $query = "INSERT INTO lomba (nama, deskripsi, batas_pengumpulan, gambar) 
                            VALUES (?, ?, ?, ?)"; // Menggunakan prepared statement untuk keamanan
                $stmt = $koneksi->prepare($query);
                $stmt->bind_param("ssss", $nama_lomba, $deskripsi, $batas_pengumpulan, $gambar);

                if ($stmt->execute()) {
                    $message = "Lomba berhasil ditambahkan!";
                    $message_class = "success";
                } else {
                    $message = "Terjadi kesalahan saat menambahkan lomba: " . $stmt->error;
                    $message_class = "error";
                }
                $stmt->close();
            } else {
                $message = "Gagal mengunggah gambar.";
                $message_class = "error";
            }
        } else {
            $message = "Ukuran file gambar terlalu besar. Maksimal 5MB.";
            $message_class = "error";
        }
    } else {
        $message = "Terjadi kesalahan dalam pengunggahan file gambar.";
        $message_class = "error";
    }
}

// Hapus lomba
if (isset($_GET['hapus_lomba'])) {
    $id_lomba = $_GET['id_lomba'];

    // Pertama, dapatkan nama file gambar untuk dihapus dari server
    $query_gambar = $koneksi->prepare("SELECT gambar FROM lomba WHERE id = ?");
    $query_gambar->bind_param("i", $id_lomba);
    $query_gambar->execute();
    $result_gambar = $query_gambar->get_result();
    if ($result_gambar->num_rows > 0) {
        $data_gambar = $result_gambar->fetch_assoc();
        $gambar_file_path = '../assets/img/' . $data_gambar['gambar'];
        if (file_exists($gambar_file_path)) {
            unlink($gambar_file_path); // Hapus file gambar dari server
        }
    }
    $query_gambar->close();

    // Query untuk menghapus lomba dari database
    $query = "DELETE FROM lomba WHERE id = ?"; // Menggunakan prepared statement
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id_lomba);

    if ($stmt->execute()) {
        $message = "Lomba berhasil dihapus!";
        $message_class = "success";
    } else {
        $message = "Terjadi kesalahan saat menghapus lomba: " . $stmt->error;
        $message_class = "error";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lomba - Admin Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4ff;
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

        .content h2 {
            color: #287bff;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.2em;
        }

        /* Message Success / Error */
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            opacity: 0;
            animation: fadein 0.5s forwards;
        }

        @keyframes fadein {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Grid Lomba */
        .grid-lomba {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .lomba-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e0e6f0;
        }

        .lomba-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .img-container {
            width: 100%;
            height: 200px;
            background-color: #ecf0f1;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .img-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            display: block;
        }

        .lomba-card h3 {
            margin: 15px 15px 10px;
            font-size: 1.5em;
            color: #2c3e50;
        }

        .lomba-card p {
            margin: 0 15px 10px;
            font-size: 0.95em;
            color: #555;
            line-height: 1.5;
        }
        .lomba-card p strong {
            color: #287bff;
        }

        .btn-hapus-lomba {
            display: inline-block;
            margin: 10px 15px 20px;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .btn-hapus-lomba:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .btn-tambah-lomba {
            display: inline-block;
            margin: 40px auto 20px;
            padding: 12px 25px;
            background-color: #287bff;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .btn-tambah-lomba:hover {
            background-color: #004bb5;
            transform: translateY(-2px);
        }

        /* Form Tambah Lomba */
        .form-tambah-lomba {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-top: 40px;
            border: 1px solid #e0e6f0;
            text-align: left;
        }

        .form-tambah-lomba h3 {
            color: #287bff;
            margin-bottom: 25px;
            font-size: 1.8em;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-tambah-lomba input[type="text"],
        .form-tambah-lomba input[type="date"],
        .form-tambah-lomba textarea,
        .form-tambah-lomba input[type="file"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
        }
        .form-tambah-lomba textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-tambah-lomba button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #287bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .form-tambah-lomba button[type="submit"]:hover {
            background-color: #004bb5;
            transform: translateY(-2px);
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
            .content {
                width: 95%;
                padding: 20px;
                margin: 30px auto;
            }
            .grid-lomba {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .form-tambah-lomba input,
            .form-tambah-lomba textarea {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="dashboard_admin.php" class="logo">Portal Lomba</a>
        <div class="navbar-right">
            <ul class="menu">
                <li><a href="dashboard_admin.php">Dashboard</a></li>
                <li><a href="kelola_lomba.php" class="active">Kelola Lomba</a></li> <li><a href="kelola_peserta.php">Kelola Peserta</a></li>
                <li><a href="kelola_juri.php">Kelola Juri</a></li>
                <li><a href="pengumuman.php">Pengumuman</a></li>
            </ul>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="content">
        <h2>Kelola Lomba</h2>

        <?php if (isset($message)): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div id="formTambahLomba" class="form-tambah-lomba" style="<?php echo (isset($_POST['tambah_lomba']) && $message_class == 'error') ? 'display:block;' : 'display:none;'; ?>">
            <h3>Tambah Lomba Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_lomba">Nama Lomba:</label>
                    <input type="text" id="nama_lomba" name="nama_lomba" placeholder="Nama Lomba" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Lomba:</label>
                    <textarea id="deskripsi" name="deskripsi" placeholder="Deskripsi Lomba" required></textarea>
                </div>
                <div class="form-group">
                    <label for="batas_pengumpulan">Batas Pengumpulan:</label>
                    <input type="date" id="batas_pengumpulan" name="batas_pengumpulan" required>
                </div>
                <div class="form-group">
                    <label for="gambar">Gambar Lomba (maks 5MB):</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*" required>
                </div>
                <button type="submit" name="tambah_lomba">Tambah Lomba</button>
            </form>
        </div>

        <a href="#" class="btn-tambah-lomba" onclick="document.getElementById('formTambahLomba').style.display='block'">Tambah Lomba</a>

        <div class="grid-lomba">
            <?php
            // Query untuk menampilkan semua lomba
            // Penting: Pastikan $koneksi masih aktif di sini.
            // Jika koneksi ditutup di awal file, ini akan error.
            // Saya sudah memindahkan $koneksi->close() ke akhir script PHP di kelola_lomba.php
            $query = $koneksi->query("SELECT * FROM lomba ORDER BY batas_pengumpulan ASC");
            if ($query->num_rows > 0) {
                while ($data = $query->fetch_assoc()) {
                    echo '
                    <div class="lomba-card">
                        <div class="img-container">
                            <img src="../assets/img/' . htmlspecialchars($data['gambar']) . '" alt="' . htmlspecialchars($data['nama']) . '">
                        </div>
                        <h3>' . htmlspecialchars($data['nama']) . '</h3>
                        <p>' . htmlspecialchars(substr($data['deskripsi'], 0, 100)) . (strlen($data['deskripsi']) > 100 ? '...' : '') . '</p>
                        <p><strong>Batas:</strong> ' . date("d M Y", strtotime($data['batas_pengumpulan'])) . '</p>
                        <a href="?hapus_lomba=true&id_lomba=' . htmlspecialchars($data['id']) . '" class="btn-hapus-lomba" onclick="return confirm(\'Apakah Anda yakin ingin menghapus lomba ini?\')">Hapus Lomba</a>
                    </div>';
                }
            } else {
                echo '<p style="grid-column: 1 / -1; text-align: center; color: #777;">Belum ada lomba yang ditambahkan.</p>';
            }
            ?>
        </div>
    </div>

</body>
</html>
<?php
// Tutup koneksi database di akhir script
$koneksi->close();
?>