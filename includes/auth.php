<?php
// Memulai session
session_start();

// Fungsi untuk mengecek apakah pengguna sudah login dan role-nya sesuai
function check_login($role = null) {
    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit;
    }

    // Cek jika role spesifik diminta dan tidak cocok
    if ($role && $_SESSION['role'] != $role) {
        // Redirect jika role tidak sesuai
        header("Location: dashboard_" . $_SESSION['role'] . ".php");
        exit;
    }
}

// Fungsi untuk mengecek apakah pengguna admin
function check_admin() {
    check_login('admin');
}

// Fungsi untuk mengecek apakah pengguna peserta
function check_peserta() {
    check_login('peserta');
}

// Fungsi untuk mengecek apakah pengguna juri
function check_juri() {
    check_login('juri');
}
?>
