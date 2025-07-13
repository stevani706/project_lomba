<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php"); // Arahkan ke login jika tidak ada role admin
    exit;
}

include "../includes/db.php"; 

if (!isset($koneksi) || $koneksi->connect_error) {
    die("Koneksi database gagal: " . (isset($koneksi) ? $koneksi->connect_error : 'Variabel $koneksi tidak terdefinisi. Pastikan db.php benar dan ditemukan.'));
}

$message = '';
$message_class = '';

// --- LOGIKA PROSES PENENTUAN PEMENANG ---
if (isset($_POST['tentukan_pemenang'])) {
    $koneksi->begin_transaction(); // Mulai transaksi
    try {
        // Hapus data pemenang lama
        $delete_query = "DELETE FROM pemenang";
        if (!$koneksi->query($delete_query)) {
            throw new Exception("Gagal menghapus pemenang lama: " . $koneksi->error);
        }

        // Query untuk mendapatkan 3 karya teratas berdasarkan total nilai final
        $query_karya_nilai_top_3 = "
            SELECT 
                k.id, 
                COALESCE(SUM(nilai.nilai), 0) AS total_nilai_final
            FROM karya k
            LEFT JOIN penilaian nilai ON k.id = nilai.id_karya
            GROUP BY k.id
            ORDER BY total_nilai_final DESC
            LIMIT 3; -- HANYA AMBIL 3 TERATAS
        ";
        
        $result_karya_nilai = $koneksi->query($query_karya_nilai_top_3);
        
        if ($result_karya_nilai && $result_karya_nilai->num_rows > 0) {
            $peringkat = 1;
            $success_count = 0;
            
            while ($row = $result_karya_nilai->fetch_assoc()) {
                $status_pemenang = "Finalis"; // Default, meskipun untuk top 3 ini akan di-override
                if ($peringkat == 1) {
                    $status_pemenang = "Juara 1";
                } else if ($peringkat == 2) {
                    $status_pemenang = "Juara 2";
                } else if ($peringkat == 3) {
                    $status_pemenang = "Juara 3";
                }
                
                $stmt_insert = $koneksi->prepare("
                    INSERT INTO pemenang (id_karya, peringkat, status_pemenang, total_nilai)
                    VALUES (?, ?, ?, ?)
                ");
                if ($stmt_insert) {
                    $stmt_insert->bind_param("iisd", $row['id'], $peringkat, $status_pemenang, $row['total_nilai_final']); 
                    if ($stmt_insert->execute()) {
                        $success_count++;
                    } else {
                        throw new Exception("Error inserting winner: " . $stmt_insert->error);
                    }
                    $stmt_insert->close();
                } else {
                    throw new Exception("Error preparing statement: " . $koneksi->error);
                }
                $peringkat++;
            }
            $koneksi->commit(); // Commit transaksi jika berhasil semua
            $message = "Pemenang berhasil ditentukan dan disimpan! " . $success_count . " pemenang tercatat.";
            $message_class = "success";
        } else {
            $koneksi->rollback(); // Rollback jika tidak ada karya dengan nilai
            $message = "Tidak ada peserta yang memiliki penilaian untuk ditentukan sebagai pemenang.";
            $message_class = "info";
        }
    } catch (Exception $e) {
        $koneksi->rollback(); // Rollback jika ada error
        $message = "Terjadi kesalahan saat menentukan pemenang: " . $e->getMessage();
        $message_class = "error";
    }
}
// --- AKHIR LOGIKA PROSES PENENTUAN PEMENANG ---

// --- LOGIKA PENGUMUMAN PEMENANG UNTUK DITAMPILKAN ---
// Mengambil 3 pemenang teratas dari tabel pemenang (sudah diisi 3 teratas)
$query_pemenang = "
    SELECT
        p.peringkat,
        p.status_pemenang,
        p.total_nilai,
        k.judul_karya,
        u.username AS nama_peserta, 
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
    LIMIT 3; -- Meskipun tabel pemenang sudah hanya berisi 3, tetap ada limit untuk jaga-jaga
";

$result_pemenang_tampil = $koneksi->query($query_pemenang);
// --- AKHIR LOGIKA PENGUMUMAN PEMENANG ---

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penentuan dan Pengumuman Pemenang - Admin</title>
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

        .content h1, .content h2 {
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
        .message.info { /* Tambahkan style info */
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }


        /* Tabel Styling */
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
            background-color: #287bff;
            color: white;
            font-weight: 600;
        }

        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .data-table tr:hover {
            background-color: #f1f1f1;
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

        /* Button Styling */
        .btn-primary {
            display: block; /* Agar bisa pakai margin auto */
            width: fit-content;
            margin: 30px auto;
            padding: 12px 25px;
            background-color: #28a745; /* Warna hijau untuk button penentuan */
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        hr {
            border: 0;
            height: 1px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0));
            margin: 40px 0;
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
                <li><a href="kelola_juri.php">Kelola Juri</a></li>
                <li><a href="pengumuman.php" class="active">Pengumuman</a></li> </ul>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="content">
        <h1>Penentuan dan Pengumuman Pemenang</h1>

        <?php if (isset($message)): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <button type="submit" name="tentukan_pemenang" class="btn-primary">Tentukan Pemenang Sekarang</button>
        </form>

        <hr>

        <h2>Daftar Pemenang (3 Teratas)</h2>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Status</th>
                        <th>Lomba</th>
                        <th>Judul Karya</th>
                        <th>Peserta</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_pemenang_tampil && $result_pemenang_tampil->num_rows > 0) { 
                        while ($row = $result_pemenang_tampil->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['peringkat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status_pemenang']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_lomba']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['judul_karya']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_peserta']) . "</td>";
                            echo "<td>" . number_format($row['total_nilai'], 2) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="6" class="no-results">Belum ada pemenang yang ditentukan, atau belum ada 3 pemenang.</td></tr>';
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