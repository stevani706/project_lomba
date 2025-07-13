<?php
// Menyambung ke database db_lomba

$host = 'localhost';      // Ganti dengan host database kamu (biasanya 'localhost' di XAMPP)
$username = 'root';       // Ganti dengan username database kamu (biasanya 'root' di XAMPP)
$password = '';           // Ganti dengan password database kamu (kosong di XAMPP secara default)
$database = 'db_lomba';   // Nama database yang akan digunakan

// Membuat koneksi
$koneksi = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Menentukan karakter set untuk koneksi
$koneksi->set_charset("utf8");

?>
