<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

include "../includes/db.php"; // Koneksi ke database

$message = '';
$message_class = '';

// Menambahkan juri baru
if (isset($_POST['tambah_juri'])) {
    $nama_juri = $_POST['nama_juri'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Query untuk menyimpan juri baru ke database
    // Menggunakan prepared statement untuk keamanan
    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'juri')";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("sss", $nama_juri, $email, $password);

    if ($stmt->execute()) {
        $message = "Juri berhasil ditambahkan!";
        $message_class = "success";
    } else {
        $message = "Terjadi kesalahan saat menambahkan juri: " . $stmt->error;
        $message_class = "error";
    }
    $stmt->close();
}

// Hapus juri
if (isset($_GET['hapus_juri'])) {
    $id_juri = $_GET['id_juri'];

    // Query untuk menghapus juri dari database
    // Menggunakan prepared statement untuk keamanan
    $query = "DELETE FROM users WHERE id = ? AND role = 'juri'";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id_juri);

    if ($stmt->execute()) {
        $message = "Juri berhasil dihapus!";
        $message_class = "success";
    } else {
        $message = "Terjadi kesalahan saat menghapus juri: " . $stmt->error;
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
    <title>Kelola Juri - Admin Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4ff; /* Mengubah dari #f7f8fc agar konsisten */
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
            font-size: 20px; /* Menyesuaikan ukuran font */
            font-weight: bold;
            color: white;
            text-decoration: none; /* Menambahkan ini agar logo juga link */
        }

        .navbar .menu {
            display: flex;
            gap: 25px; /* Menyesuaikan jarak */
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
            text-decoration: none; /* Hilangkan underline di hover */
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
            padding: 8px 15px; /* Menyesuaikan padding */
            border-radius: 8px; /* Menyesuaikan border-radius */
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.2s, transform 0.2s; /* Menambahkan transform */
        }

        .logout-btn:hover {
            background-color: #e2e6ea;
            transform: translateY(-2px); /* Menambahkan efek hover */
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
            font-size: 2.2em; /* Menyesuaikan ukuran font */
        }

        /* Message Success / Error */
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            opacity: 0; /* Tambahkan ini untuk animasi */
            animation: fadein 0.5s forwards; /* Tambahkan ini untuk animasi */
        }

        @keyframes fadein { /* Keyframes untuk animasi fadein */
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb; /* Tambahkan border */
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb; /* Tambahkan border */
        }

        /* Tabel Styling (Menggunakan tabel standar seperti kelola_peserta.php) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .data-table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: 600;
        }

        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .data-table tr:hover {
            background-color: #f1f1f1;
        }

        .btn-action {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s ease;
        }

        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }

        /* Form Tambah Juri */
        .form-tambah-juri {
            background-color: #f9f9f9; /* Mengubah dari white */
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); /* Menyesuaikan shadow */
            margin-top: 40px;
            border: 1px solid #e0e6f0; /* Tambahkan border */
            text-align: left; /* Teks di form rata kiri */
        }

        .form-tambah-juri h3 {
            color: #287bff;
            margin-bottom: 25px;
            font-size: 1.8em;
            text-align: center;
        }

        .form-group { /* Menambahkan grup form untuk label dan input */
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-tambah-juri input[type="text"],
        .form-tambah-juri input[type="email"],
        .form-tambah-juri input[type="password"] {
            width: calc(100% - 22px); /* Sesuaikan lebar input */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box; /* Pastikan padding masuk hitungan lebar */
        }
        
        .form-tambah-juri button[type="submit"] {
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

        .form-tambah-juri button[type="submit"]:hover {
            background-color: #004bb5;
            transform: translateY(-2px);
        }

        /* Tombol untuk Tambah Juri */
        .btn-tambah-juri {
            display: block; /* Menjadikan block agar bisa text-align center */
            width: fit-content; /* Sesuai lebar konten */
            margin: 40px auto 20px; /* Menyesuaikan margin */
            padding: 12px 25px;
            background-color: #287bff;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .btn-tambah-juri:hover {
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
            .data-table {
                font-size: 0.9em;
            }
            .data-table th, .data-table td {
                padding: 8px;
            }
            .table-responsive {
                overflow-x: auto;
            }
            .form-tambah-juri input {
                width: 100%; /* Full width */
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
                <li><a href="kelola_lomba.php">Kelola Lomba</a></li>
                <li><a href="kelola_peserta.php">Kelola Peserta</a></li>
                <li><a href="kelola_juri.php" class="active">Kelola Juri</a></li> <li><a href="pengumuman.php">Pengumuman</a></li>
            </ul>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="content">
        <h2>Kelola Juri</h2>

        <?php if (isset($message)): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div id="formTambahJuri" class="form-tambah-juri" style="display:none;">
            <h3>Tambah Juri Baru</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="nama_juri">Nama Juri:</label>
                    <input type="text" id="nama_juri" name="nama_juri" placeholder="Nama Juri" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Juri:</label>
                    <input type="email" id="email" name="email" placeholder="Email Juri" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="tambah_juri">Tambah Juri</button>
            </form>
        </div>

        <a href="#" class="btn-tambah-juri" onclick="document.getElementById('formTambahJuri').style.display='block'; return false;">Tambah Juri</a>

        <h3>Daftar Juri</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Terdaftar Sejak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query untuk menampilkan semua juri
                    // Pastikan koneksi $koneksi masih aktif di sini.
                    $query_juri_list = $koneksi->query("SELECT id, username, email, created_at FROM users WHERE role = 'juri' ORDER BY username ASC");
                    if ($query_juri_list->num_rows > 0) {
                        while ($data = $query_juri_list->fetch_assoc()) {
                            echo '
                            <tr>
                                <td>' . htmlspecialchars($data['id']) . '</td>
                                <td>' . htmlspecialchars($data['username']) . '</td>
                                <td>' . htmlspecialchars($data['email']) . '</td>
                                <td>' . date("d M Y H:i", strtotime($data['created_at'])) . '</td>
                                <td>
                                    <a href="?hapus_juri=true&id_juri=' . htmlspecialchars($data['id']) . '" class="btn-action btn-delete" onclick="return confirm(\'Apakah Anda yakin ingin menghapus juri ini?\')">Hapus</a>
                                </td>
                            </tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align: center;">Belum ada juri terdaftar.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
<?php
// Tutup koneksi database di akhir script
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $koneksi->close();
}
?>