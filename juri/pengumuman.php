<?php
session_start();

// Pastikan hanya juri yang dapat mengakses halaman ini
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'juri') {
    header("Location: ../login.php");
    exit;
}

include "../includes/db.php"; 

// Mendapatkan nama file saat ini untuk menandai link aktif di navbar
$current_page = basename($_SERVER['PHP_SELF']);

// LOGIKA PENGUMUMAN PEMENANG UNTUK JURI
// Query untuk mengambil data pemenang dari tabel 'pemenang'
$query_pemenang = "
    SELECT
        p.peringkat,
        p.status_pemenang,
        p.total_nilai,
        k.judul_karya,
        u.nama AS nama_peserta,
        l.nama AS nama_lomba
    FROM
        pemenang p
    JOIN
        karya k ON p.id_karya = k.id
    JOIN
        users u ON k.email_peserta = u.email
    JOIN
        lomba l ON k.id_lomba = l.id  
    ORDER BY
        p.peringkat ASC, p.total_nilai DESC
    LIMIT 3; 
";

$result_pemenang = $koneksi->query($query_pemenang); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Pemenang</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4ff;
            margin: 0;
            padding-bottom: 20px; 
        }
        
        /* Navbar Styling (Konsisten dengan dashboard_juri.php dan penilaian.php) */
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
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #287bff; 
            color: white; 
        }
        tr:nth-child(even) { 
            background-color: #f2f2f2; 
        }
        .no-results { 
            text-align: center; 
            color: #666; 
            padding: 20px; 
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

<div class="container">
    <h1>Pengumuman Pemenang (3 Teratas)</h1>

    <?php
    if ($result_pemenang && $result_pemenang->num_rows > 0) {
        echo "<table>";
        echo "<thead><tr><th>Peringkat</th><th>Status</th><th>Lomba</th><th>Judul Karya</th><th>Peserta</th><th>Total Nilai</th></tr></thead>";
        echo "<tbody>";
        while ($row = $result_pemenang->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['peringkat']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status_pemenang']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_lomba']) . "</td>";
            echo "<td>" . htmlspecialchars($row['judul_karya']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_peserta']) . "</td>";
            echo "<td>" . number_format($row['total_nilai'], 2) . "</td>"; // Menampilkan total nilai
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p class='no-results'>Belum ada pemenang yang ditentukan.</p>";
    }

    $koneksi->close();
    ?>
</div>

</body>
</html>