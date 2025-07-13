<?php
session_start();

// Pastikan pengguna sudah login dan memiliki role 'peserta'
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'peserta') {
    header("Location: ../login.php"); // Pastikan path ini benar jika file login.php ada di folder root
    exit;
}

include "../includes/db.php"; // Pastikan koneksi ke database sudah diatur dengan benar

// Inisialisasi variabel pesan sesi
$_SESSION['upload_message'] = '';
$_SESSION['upload_status'] = ''; // 'success' atau 'error'

// Pastikan form sudah di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = '../uploads/karya/'; // Folder untuk menyimpan file yang di-upload
    
    // Buat nama file unik untuk mencegah penimpaan file
    $originalFileName = basename($_FILES['file_karya']['name']);
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('karya_', true) . '.' . $fileExtension; // Contoh: karya_65f3f0a7b8c9d0e1f2a3b4c5.jpg
    $uploadFile = $uploadDir . $newFileName;
    
    $uploadOk = 1;
    $errorMessages = []; // Untuk mengumpulkan semua pesan kesalahan

    // 1. Validasi Ukuran File (maksimal 5MB)
    if ($_FILES['file_karya']['size'] > 5000000) {
        $errorMessages[] = "File terlalu besar. Maksimal 5MB.";
        $uploadOk = 0;
    }

    // 2. Validasi Jenis File (PDF, gambar JPG, PNG, GIF)
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array($fileExtension, $allowedTypes)) {
        $errorMessages[] = "Hanya file dengan ekstensi JPG, JPEG, PNG, GIF, dan PDF yang diperbolehkan.";
        $uploadOk = 0;
    }

    // 3. Ambil dan Validasi Input Form
    $judul_karya = trim($_POST['judul_karya'] ?? ''); // Ambil judul karya, bersihkan spasi ekstra
    $id_lomba = $_POST['id_lomba'] ?? ''; // Ambil ID lomba

    if (empty($judul_karya)) {
        $errorMessages[] = "Judul karya tidak boleh kosong.";
        $uploadOk = 0;
    }
    if (empty($id_lomba) || !is_numeric($id_lomba)) {
        $errorMessages[] = "ID Lomba tidak valid atau tidak dipilih. Mohon pilih lomba yang benar.";
        $uploadOk = 0;
    } else {
        $id_lomba = (int)$id_lomba; // Pastikan ini integer
    }

    // Jika ada validasi awal yang gagal
    if ($uploadOk == 0) {
        $_SESSION['upload_message'] = implode("<br>", $errorMessages); // Gabungkan semua pesan kesalahan
        $_SESSION['upload_status'] = 'error';
        // --- PERUBAHAN DI SINI: dashboard_peserta.php ---
        header("Location: ../dashboard_peserta.php"); // Redirect kembali
        exit;
    }

    // 4. Lanjutkan Proses Upload File
    if (move_uploaded_file($_FILES['file_karya']['tmp_name'], $uploadFile)) {
        // 5. Cek apakah id_lomba yang dipilih ada di tabel lomba (menggunakan Prepared Statements)
        $stmt_check_lomba = $koneksi->prepare("SELECT COUNT(*) FROM lomba WHERE id = ?");
        if ($stmt_check_lomba === false) {
            $errorMessages[] = "Error mempersiapkan cek lomba: " . $koneksi->error;
            $uploadOk = 0;
        } else {
            $stmt_check_lomba->bind_param("i", $id_lomba);
            $stmt_check_lomba->execute();
            $stmt_check_lomba->bind_result($lomba_exists);
            $stmt_check_lomba->fetch();
            $stmt_check_lomba->close();

            if ($lomba_exists == 0) {
                $errorMessages[] = "Lomba dengan ID tersebut tidak ditemukan. Mohon pilih lomba yang valid.";
                $uploadOk = 0;
            }
        }

        // Jika cek lomba gagal
        if ($uploadOk == 0) {
            // Hapus file yang sudah diupload jika ada kesalahan setelah upload fisik
            if (file_exists($uploadFile)) {
                unlink($uploadFile);
            }
            $_SESSION['upload_message'] = implode("<br>", $errorMessages);
            $_SESSION['upload_status'] = 'error';
            // --- PERUBAHAN DI SINI: dashboard_peserta.php ---
            header("Location: ../dashboard_peserta.php");
            exit;
        }

        // 6. Simpan Data Karya ke Database (menggunakan Prepared Statements)
        $email_peserta = $_SESSION['email']; // Ambil email peserta yang sedang login

        $stmt_insert_karya = $koneksi->prepare("INSERT INTO karya (judul_karya, file_karya, id_lomba, email_peserta) VALUES (?, ?, ?, ?)");
        if ($stmt_insert_karya === false) {
            $errorMessages[] = "Error mempersiapkan query insert: " . $koneksi->error;
            $uploadOk = 0;
        } else {
            $stmt_insert_karya->bind_param("ssis", $judul_karya, $newFileName, $id_lomba, $email_peserta);
            
            if ($stmt_insert_karya->execute()) {
                $_SESSION['upload_message'] = "Karya berhasil di-upload!";
                $_SESSION['upload_status'] = 'success';
            } else {
                $errorMessages[] = "Gagal menyimpan data karya ke database. Error: " . $stmt_insert_karya->error;
                $_SESSION['upload_message'] = implode("<br>", $errorMessages);
                $_SESSION['upload_status'] = 'error';
            }
            $stmt_insert_karya->close();
        }

    } else {
        // Kesalahan saat memindahkan file fisik
        $errorMessages[] = "Terjadi kesalahan saat meng-upload file. Kode Error: " . $_FILES['file_karya']['error'];
        $_SESSION['upload_message'] = implode("<br>", $errorMessages);
        $_SESSION['upload_status'] = 'error';
    }

    // Setelah semua proses selesai, redirect ke dashboard peserta
    // --- PERUBAHAN DI SINI: dashboard_peserta.php ---
    header("Location: ../dashboard_peserta.php");
    exit;

} else {
    // Jika bukan POST request, arahkan kembali ke form atau tampilkan pesan
    $_SESSION['upload_message'] = "Akses tidak langsung. Mohon kirim melalui form upload karya.";
    $_SESSION['upload_status'] = 'error';
    // --- PERUBAHAN DI SINI: dashboard_peserta.php ---
    header("Location: ../dashboard_peserta.php"); // Redirect jika diakses langsung
    exit;
}

// Tutup koneksi database
if (isset($koneksi)) {
    $koneksi->close();
}
?>