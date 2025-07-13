<?php
// Koneksi database
include "includes/db.php";

// Query untuk menghitung rata-rata nilai untuk setiap karya
$query = "SELECT karya.id, karya.judul_karya, users.nama as nama_peserta, AVG(penilaian.nilai) as rata_nilai
          FROM penilaian
          JOIN karya ON penilaian.id_karya = karya.id
          JOIN users ON karya.email_peserta = users.email
          GROUP BY karya.id
          ORDER BY rata_nilai DESC"; // Urutkan berdasarkan rata-rata nilai tertinggi

$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Juara</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="navbar">
    <div class="logo">Portal Lomba</div>
    <div class="menu">
        <a href="index.php">Beranda</a>
        <a href="pengumuman.php" class="active">Pengumuman Juara</a>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="content">
    <h2>Pengumuman Juara</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <h3>Berikut adalah pemenang lomba:</h3>
        <table border="1" cellpadding="10">
            <tr>
                <th>Posisi</th>
                <th>Nama Peserta</th>
                <th>Judul Karya</th>
                <th>Nilai</th>
            </tr>
            <?php
            $position = 1;
            while ($data = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $position . '</td>';
                echo '<td>' . htmlspecialchars($data['nama_peserta']) . '</td>';
                echo '<td>' . htmlspecialchars($data['judul_karya']) . '</td>';
                echo '<td>' . number_format($data['rata_nilai'], 2) . '</td>';
                echo '</tr>';
                $position++;
            }
            ?>
        </table>
    <?php else: ?>
        <p>Tidak ada penilaian yang ditemukan.</p>
    <?php endif; ?>
</div>

</body>
</html>
