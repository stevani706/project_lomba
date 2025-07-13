<?php
session_start();

// Pastikan hanya juri yang dapat mengakses halaman ini
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'juri') {
    header("Location: ../../login.php");
    exit;
}

// Include koneksi database dengan jalur relatif yang benar
include "../../includes/db.php"; 

// Pastikan koneksi berhasil
if ($koneksi->connect_error) {
    // Jika koneksi gagal, tampilkan error dan hentikan eksekusi
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Cek apakah request datang dari POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['pesan_error'] = "Akses tidak sah.";
    header("Location: ../penilaian.php");
    exit;
}

// Ambil data dari form POST
$id_karya = filter_input(INPUT_POST, 'id_karya', FILTER_VALIDATE_INT);
$nilai = filter_input(INPUT_POST, 'nilai', FILTER_VALIDATE_INT);
$komentar = filter_input(INPUT_POST, 'komentar', FILTER_SANITIZE_STRING); // Gunakan FILTER_SANITIZE_STRING untuk komentar

// Validasi input
if ($id_karya === false || $id_karya === null || $nilai === false || $nilai === null || empty($komentar)) {
    $_SESSION['pesan_error'] = "Semua field harus diisi dengan benar (nilai harus angka).";
    header("Location: ../penilaian.php");
    exit;
}

if ($nilai < 0 || $nilai > 100) {
    $_SESSION['pesan_error'] = "Nilai harus antara 0 dan 100.";
    header("Location: ../penilaian.php");
    exit;
}

$email_juri = $_SESSION['email'];

// ===================================================================================
// LANGKAH 1: Dapatkan ID Juri yang sedang login dari tabel 'juri'
// Ini KRUSIAL. Pastikan juri_id ini benar dan ada di tabel 'juri'.
// ===================================================================================
$id_juri = null;
$stmt_get_juri_id = $koneksi->prepare("SELECT id FROM juri WHERE email = ?");
if ($stmt_get_juri_id) {
    $stmt_get_juri_id->bind_param("s", $email_juri);
    $stmt_get_juri_id->execute();
    $result_juri_id = $stmt_get_juri_id->get_result();
    if ($result_juri_id->num_rows > 0) {
        $juri_data = $result_juri_id->fetch_assoc();
        $id_juri = $juri_data['id'];
    }
    $stmt_get_juri_id->close();
} else {
    // Gagal menyiapkan statement untuk mendapatkan ID juri
    $_SESSION['pesan_error'] = "Terjadi kesalahan sistem saat mencari ID juri (prepare error).";
    error_log("Error preparing get_juri_id statement: " . $koneksi->error);
    header("Location: ../penilaian.php");
    exit;
}

// Jika ID juri tidak ditemukan, berarti juri yang login tidak ada di tabel 'juri'
if ($id_juri === null) {
    $_SESSION['pesan_error'] = "Data juri tidak ditemukan. Pastikan email juri terdaftar di tabel 'juri'.";
    error_log("Juri dengan email " . $email_juri . " tidak ditemukan di tabel 'juri'.");
    header("Location: ../penilaian.php");
    exit;
}

// ===================================================================================
// LANGKAH 2: Cek apakah juri sudah pernah menilai karya ini
// Menggunakan id_juri yang sudah didapatkan.
// ===================================================================================
$stmt_check = $koneksi->prepare("SELECT COUNT(*) AS total FROM penilaian WHERE id_karya = ? AND id_juri = ?");
if ($stmt_check) {
    $stmt_check->bind_param("ii", $id_karya, $id_juri);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result()->fetch_assoc();
    $sudah_dinilai = $result_check['total'] > 0;
    $stmt_check->close();

    if ($sudah_dinilai) {
        $_SESSION['pesan_info'] = "Anda sudah memberikan penilaian untuk karya ini sebelumnya.";
        header("Location: ../penilaian.php");
        exit;
    }
} else {
    // Gagal menyiapkan statement check
    $_SESSION['pesan_error'] = "Terjadi kesalahan sistem saat memeriksa penilaian (prepare error).";
    error_log("Error preparing check_penilaian statement: " . $koneksi->error);
    header("Location: ../penilaian.php");
    exit;
}


// ===================================================================================
// LANGKAH 3: Masukkan penilaian ke dalam tabel 'penilaian'
// Menggunakan id_juri yang sudah didapatkan.
// ===================================================================================
$stmt_insert = $koneksi->prepare("INSERT INTO penilaian (id_karya, id_juri, nilai, komentar) VALUES (?, ?, ?, ?)");
if ($stmt_insert) {
    $stmt_insert->bind_param("iiis", $id_karya, $id_juri, $nilai, $komentar);
    if ($stmt_insert->execute()) {
        $_SESSION['pesan_sukses'] = "Penilaian berhasil diberikan!";
    } else {
        $_SESSION['pesan_error'] = "Gagal menyimpan penilaian: " . $stmt_insert->error;
        error_log("Error inserting penilaian: " . $stmt_insert->error . " | Karya: " . $id_karya . " | Juri ID: " . $id_juri);
    }
    $stmt_insert->close();
} else {
    $_SESSION['pesan_error'] = "Gagal menyiapkan statement insert penilaian: " . $koneksi->error;
    error_log("Error preparing insert_penilaian statement: " . $koneksi->error);
}

// Tutup koneksi database
$koneksi->close();

// Redirect ke halaman penilaian.php setelah semua proses selesai
header("Location: ../penilaian.php");
exit;
?>