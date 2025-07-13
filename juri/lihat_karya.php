<?php
session_start();

// Pastikan hanya juri yang dapat mengakses halaman ini
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'juri') {
    header("Location: ../login.php");
    exit;
}

include "../includes/db.php";  // Pastikan koneksi database sudah benar

// Tangkap ID karya yang ingin dilihat
$id_karya = isset($_GET['id_karya']) ? $_GET['id_karya'] : '';

// Cek apakah id_karya ada
if (empty($id_karya)) {
    echo '<p>Id Karya tidak valid.</p>';
    exit;
}

// Query untuk mendapatkan data karya berdasarkan id
$query = $koneksi->prepare("SELECT karya.*, users.nama as nama_peserta, lomba.nama as nama_lomba
                            FROM karya
                            JOIN users ON karya.email_peserta = users.email
                            JOIN lomba ON karya.id_lomba = lomba.id
                            WHERE karya.id = ?");
$query->bind_param("i", $id_karya);
$query->execute();
$result = $query->get_result();

// Jika karya tidak ditemukan
if ($result->num_rows == 0) {
    echo '<p>Karya tidak ditemukan.</p>';
    exit;
}

// Ambil data karya
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Karya</title>
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
        .navbar .menu a:hover {
            text-decoration: underline;
        }
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
        .content .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .content .card h3 {
            color: #287bff;
        }
        iframe, img {
            width: 100%;
            height: auto;
            max-width: 900px;
            display: block;
            margin: 20px auto;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #287bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
        .back-btn:hover {
            background-color: #004bb5;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">Portal Lomba</div>
    <div class="menu">
        <a href="dashboard_juri.php">Dashboard</a>
        <a href="penilaian.php">Penilaian</a>
        <a href="pengumuman.php">Pengumuman</a>
    </div>
    <a href="../logout.php" class="logout-btn">Logout</a>
</div>

<div class="content">
    <h2>Lihat Karya Peserta</h2>

    <div class="card">
        <h3>Judul Karya: <?php echo htmlspecialchars($data['judul_karya']); ?></h3>
        <p>Peserta: <strong><?php echo htmlspecialchars($data['nama_peserta']); ?></strong></p>
        <p>Lomba: <strong><?php echo htmlspecialchars($data['nama_lomba']); ?></strong></p>

        <?php
        $file_path = "../uploads/karya/" . htmlspecialchars($data['file_karya']);
        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        // Jika file berupa PDF
        if ($file_ext == "pdf") {
            echo '<iframe src="' . $file_path . '" frameborder="0"></iframe>';
        }
        // Jika file berupa gambar
        elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo '<img src="' . $file_path . '" alt="Karya Peserta">';
        }
        else {
            echo '<p>File tidak dapat ditampilkan langsung, silakan unduh file di bawah.</p>';
            echo '<a href="' . $file_path . '" class="back-btn" download>Unduh Karya</a>';
        }
        ?>

        <a href="penilaian.php" class="back-btn">Kembali ke Penilaian</a>
    </div>
</div>

</body>
</html>
