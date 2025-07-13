<?php
session_start();

// Pastikan hanya peserta yang dapat mengakses halaman ini
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'peserta') {
    header("Location: login.php");
    exit;
}

include "includes/db.php"; // Pastikan koneksi.php ada di folder 'includes'

// Tangkap ID Lomba dari URL
if (isset($_GET['id_lomba']) && !empty($_GET['id_lomba'])) {
    $id_lomba = $koneksi->real_escape_string($_GET['id_lomba']);

    // Ambil data lomba dari database
    $query_lomba = $koneksi->query("SELECT * FROM lomba WHERE id_lomba = '$id_lomba'");
    $lomba = $query_lomba->fetch_assoc();

    if (!$lomba) {
        // Jika lomba tidak ditemukan
        header("Location: daftar_lomba.php?pesan=Lomba tidak ditemukan.");
        exit;
    }
} else {
    // Jika tidak ada ID lomba di URL
    header("Location: daftar_lomba.php?pesan=ID lomba tidak valid.");
    exit;
}

// Cek apakah peserta sudah mendaftar lomba ini
$id_peserta = $_SESSION['id_user']; // Asumsikan Anda menyimpan ID user di sesi dengan key 'id_user'
$sudah_daftar = false;
$query_cek_daftar = $koneksi->query("SELECT * FROM pendaftaran_lomba WHERE id_lomba = '$id_lomba' AND id_peserta = '$id_peserta'");
if ($query_cek_daftar->num_rows > 0) {
    $sudah_daftar = true;
}

// Untuk navbar aktif
$current_page = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Lomba: <?php echo htmlspecialchars($lomba['nama']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
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
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="content container"> <h2>Detail Lomba: <?php echo htmlspecialchars($lomba['nama']); ?></h2>

    <div class="lomba-detail">
        <div class="detail-header text-center">
            <?php if (!empty($lomba['gambar'])): ?>
                <img src="assets/img/<?php echo htmlspecialchars($lomba['gambar']); ?>" alt="<?php echo htmlspecialchars($lomba['nama']); ?>" style="max-width: 400px; height: auto; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <?php else: ?>
                <img src="assets/img/placeholder.png" alt="Gambar Lomba" style="max-width: 400px; height: auto; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($lomba['nama']); ?></h3>
        </div>

        <div class="detail-body">
            <p><strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($lomba['deskripsi'])); ?></p>
            <p><strong>Syarat & Ketentuan:</strong> <?php echo nl2br(htmlspecialchars($lomba['syarat_ketentuan'])); ?></p>
            <p><strong>Batas Pengumpulan:</strong> <?php echo date("d M Y H:i", strtotime($lomba['batas_pengumpulan'])); ?> WIB</p>
            <p><strong>Hadiah:</strong> <?php echo htmlspecialchars($lomba['hadiah']); ?></p>
            <p><strong>Kategori:</strong> <?php echo htmlspecialchars($lomba['kategori']); ?></p>
            
            <div class="text-center" style="margin-top: 30px;">
                <?php if ($sudah_daftar): ?>
                    <p class="message success">Anda sudah terdaftar di lomba ini.</p>
                    <a href="upload_karya.php" class="btn-primary">Upload Karya Saya</a>
                <?php else: ?>
                    <?php if (strtotime($lomba['batas_pengumpulan']) > time()): ?>
                        <a href="proses_daftar_lomba.php?id_lomba=<?php echo htmlspecialchars($lomba['id_lomba']); ?>" class="btn-register">Daftar Sekarang</a>
                    <?php else: ?>
                        <p class="message error">Pendaftaran untuk lomba ini sudah ditutup.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>