<?php
session_start();

// Pastikan hanya juri yang dapat mengakses halaman ini
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'juri') {
    header("Location: ../login.php");
    exit;
}

include "../includes/db.php";  // Pastikan path koneksi benar sesuai struktur folder

// Mendapatkan email juri dari session
$email_juri = $_SESSION['email'];

// Mendapatkan nama file saat ini untuk menandai link aktif di navbar
$current_page = basename($_SERVER['PHP_SELF']);

// =================================================================
// Query untuk mengambil data karya yang BELUM dinilai oleh juri yang sedang login
// Menggunakan tabel 'juri' di sub-query, konsisten dengan proses/beri_nilai.php
// =================================================================
$query = $koneksi->prepare("
    SELECT k.*, u.nama AS nama_peserta, l.nama AS nama_lomba
    FROM karya k
    JOIN users u ON k.email_peserta = u.email
    JOIN lomba l ON k.id_lomba = l.id
    WHERE k.id NOT IN (
        SELECT p.id_karya
        FROM penilaian p
        JOIN juri j ON p.id_juri = j.id  -- JOIN ke tabel 'juri'
        WHERE j.email = ?
    )
    ORDER BY k.id DESC
");
$query->bind_param("s", $email_juri);
$query->execute();
$result = $query->get_result();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Karya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4ff;
            margin: 0;
            padding-bottom: 20px;
        }
        /* Navbar Styling (Konsisten) */
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
            flex-basis: 150px;
            text-align: left;
        }
        .navbar .menu {
            display: flex;
            gap: 20px;
            flex-grow: 1;
            justify-content: center;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .navbar .menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .navbar .menu a:hover,
        .navbar .menu a.active {
            text-decoration: underline;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .logout-btn {
            background-color: white;
            color: #287bff;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .logout-btn:hover {
            background-color: #e2e6ea;
            color: #0056b3;
        }
        .user-info {
            color: white;
            margin-right: 15px;
            font-weight: bold;
        }

        /* Content Styling */
        .content {
            padding: 40px;
            width: 90%;
            max-width: 1200px;
            margin: auto;
        }
        .content h2 {
            text-align: center;
            color: #287bff;
            margin-bottom: 30px;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin-top: 10px;
            color: #287bff;
        }
        .card p {
            font-size: 14px;
            color: #333;
        }
        .btn-nilai {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 18px;
            background-color: #287bff;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .btn-nilai:hover {
            background-color: #004bb5;
        }
        .form-penilaian {
            display: none;
            margin-top: 15px;
            text-align: left;
        }
        .form-penilaian input,
        .form-penilaian textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        iframe {
            width: 100%;
            height: 400px;
            border: none;
            margin-bottom: 10px;
        }
        .btn-fullscreen, .btn-fullscreen-img {
            margin-top: 12px;
            padding: 8px 18px;
            background-color: #f0ad4e;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .btn-fullscreen:hover, .btn-fullscreen-img:hover {
            background-color: #ec971f;
        }

        .image-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            cursor: pointer;
            border-radius: 5px;
        }

        /* Styles for alert messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            text-align: center;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .alert-info {
            color: #31708f;
            background-color: #d9edf7;
            border-color: #bce8f1;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">Portal Lomba</div>
    <ul class="menu">
        <li><a href="dashboard_juri.php" class="<?php echo ($current_page == 'dashboard_juri.php') ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="penilaian.php" class="<?php echo ($current_page == 'penilaian.php') ? 'active' : ''; ?>">Penilaian</a></li>
        <li><a href="pengumuman.php" class="<?php echo ($current_page == 'pengumuman.php') ? 'active' : ''; ?>">Pengumuman</a></li>
    </ul>
    <?php if (isset($_SESSION['nama'])): ?>
        <span class="user-info">Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</span>
        <a href="../logout.php" class="logout-btn">Logout</a>
    <?php else: ?>
        <a href="../login.php" class="logout-btn">Login</a>
    <?php endif; ?>
</div>

<div class="content">
    <h2>Penilaian Karya Peserta</h2>

    <?php
    // Tampilkan pesan sukses, error, atau info dari session
    if (isset($_SESSION['pesan_sukses'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['pesan_sukses']) . '</div>';
        unset($_SESSION['pesan_sukses']); // Hapus pesan setelah ditampilkan
    }
    if (isset($_SESSION['pesan_error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['pesan_error']) . '</div>';
        unset($_SESSION['pesan_error']);
    }
    if (isset($_SESSION['pesan_info'])) {
        echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['pesan_info']) . '</div>';
        unset($_SESSION['pesan_info']);
    }
    ?>

    <div class="grid-container">
        <?php
        if ($result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
                $file_path = "../uploads/karya/" . $data['file_karya'];
                echo '
                <div class="card">
                    <h3>' . htmlspecialchars($data['nama_lomba']) . '</h3>
                    <p>Peserta: <strong>' . htmlspecialchars($data['nama_peserta']) . '</strong></p>
                    <p>Judul Karya: ' . htmlspecialchars($data['judul_karya']) . '</p>';

                    $file_ext = pathinfo($data['file_karya'], PATHINFO_EXTENSION);
                    if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo '<div class="image-container">';
                        echo '<img src="' . $file_path . '" alt="Gambar Karya" onclick="window.open(\'' . $file_path . '\', \'_blank\')">';
                        echo '<a href="' . $file_path . '" target="_blank" class="btn-fullscreen-img">Lihat Gambar di Layar Penuh</a>';
                        echo '</div>';
                    } elseif ($file_ext == 'pdf') {
                        echo '<iframe src="' . $file_path . '"></iframe>';
                        echo '<a href="' . $file_path . '" target="_blank" class="btn-fullscreen">Lihat PDF di Layar Penuh</a>';
                    } else {
                        echo '<p>Tipe file tidak didukung untuk preview.</p>';
                    }

                echo '
                    <form action="proses/beri_nilai.php" method="POST" class="form-penilaian" id="form_' . $data['id'] . '">
                        <input type="hidden" name="id_karya" value="' . $data['id'] . '">
                        <input type="number" name="nilai" min="0" max="100" placeholder="Nilai (0-100)" required>
                        <textarea name="komentar" placeholder="Komentar" rows="4" required></textarea>
                        <button type="submit" class="btn-nilai">Kirim Penilaian</button>
                    </form>
                    <button class="btn-nilai" onclick="toggleForm(\'form_' . $data['id'] . '\')">Beri Nilai</button>
                </div>';
            }
        } else {
            echo "<p style='text-align: center; width: 100%;'>Tidak ada karya yang perlu dinilai saat ini.</p>";
        }
        ?>
    </div>
</div>

<script>
    function toggleForm(formId) {
        var form = document.getElementById(formId);
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    }
</script>

</body>
</html>