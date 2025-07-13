<?php
session_start(); // Pastikan session dimulai di paling atas

// === Logika Pemrosesan Registrasi ===

include "includes/db.php"; // Sesuaikan path koneksi database Anda

// Variabel untuk menampung pesan sukses/error
$pesan_status = '';
$tipe_pesan = '';

// Cek dan ambil pesan dari session jika ada (untuk ditampilkan di halaman ini)
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_status = $_SESSION['pesan_sukses'];
    $tipe_pesan = 'success';
    unset($_SESSION['pesan_sukses']); // Hapus pesan setelah diambil
}
if (isset($_SESSION['pesan_error'])) {
    $pesan_status = $_SESSION['pesan_error'];
    $tipe_pesan = 'error';
    unset($_SESSION['pesan_error']); // Hapus pesan setelah diambil
}

// Pastikan koneksi database berhasil
if ($koneksi->connect_error) {
    $pesan_status = "Koneksi database gagal: " . $koneksi->connect_error;
    $tipe_pesan = 'error';
    error_log("Database connection failed: " . $koneksi->connect_error);
}

// Jika request adalah POST, artinya form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($pesan_status)) { // Hanya proses jika belum ada pesan status dari koneksi
    // Ambil dan sanitasi input dari form
    $nama = trim(filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // --- Validasi Input Server-Side ---
    $has_error = false;
    if (empty($nama) || empty($email) || empty($password)) {
        $pesan_status = "Nama, email, dan password harus diisi!";
        $tipe_pesan = 'error';
        $has_error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_status = "Format email tidak valid!";
        $tipe_pesan = 'error';
        $has_error = true;
    }
    // CATATAN: Validasi panjang password minimum DIHILANGKAN di sini sesuai permintaan Anda.

    // Validasi role yang diizinkan
    $allowed_roles = ['peserta', 'juri'];
    if (!in_array($role, $allowed_roles)) {
        $pesan_status = "Peran yang dipilih tidak valid.";
        $tipe_pesan = 'error';
        $has_error = true;
    }

    if (!$has_error) {
        // --- Hashing Password ---
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            $pesan_status = "Gagal memproses password. Silakan coba lagi.";
            $tipe_pesan = 'error';
            error_log("Password hashing failed for email: " . $email);
            $has_error = true;
        }
    }

    if (!$has_error) {
        // --- Cek Email Duplikat ---
        $stmt_check_email = $koneksi->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt_check_email) {
            $pesan_status = "Terjadi kesalahan sistem (cek email prepare).";
            $tipe_pesan = 'error';
            error_log("Prepare failed for check_email: " . $koneksi->error);
            $has_error = true;
        } else {
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            $stmt_check_email->store_result();

            if ($stmt_check_email->num_rows > 0) {
                $pesan_status = "Email sudah terdaftar. Silakan login atau gunakan email lain.";
                $tipe_pesan = 'error';
                $has_error = true;
            }
            $stmt_check_email->close();
        }
    }

    if (!$has_error) {
        $koneksi->begin_transaction(); // Mulai transaksi database

        try {
            $stmt_insert_user = $koneksi->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
            if (!$stmt_insert_user) {
                throw new Exception("Prepare failed for insert_user: " . $koneksi->error);
            }
            $stmt_insert_user->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if (!$stmt_insert_user->execute()) {
                throw new Exception("Execute failed for insert_user: " . $stmt_insert_user->error);
            }
            $stmt_insert_user->close();

            // --- Jika Role adalah 'juri', Masukkan juga ke Tabel 'juri' ---
            if ($role === 'juri') {
                $stmt_insert_juri = $koneksi->prepare("INSERT INTO juri (nama_juri, email) VALUES (?, ?)");
                if (!$stmt_insert_juri) {
                    throw new Exception("Prepare failed for insert_juri: " . $koneksi->error);
                }
                $stmt_insert_juri->bind_param("ss", $nama, $email);
                if (!$stmt_insert_juri->execute()) {
                    throw new Exception("Execute failed for insert_juri: " . $stmt_insert_juri->error);
                }
                $stmt_insert_juri->close();
            }

            $koneksi->commit(); // Commit transaksi jika semua berhasil
            $pesan_status = "Registrasi berhasil! Anda akan dialihkan ke halaman Login dalam 3 detik..."; // Pesan untuk user
            $tipe_pesan = 'success';
            // TIDAK ADA REDIRECT HEADER DI SINI, akan ditangani oleh meta refresh/JavaScript

        } catch (Exception $e) {
            $koneksi->rollback(); // Rollback transaksi jika ada error
            $pesan_status = "Registrasi gagal: " . $e->getMessage();
            $tipe_pesan = 'error';
            error_log("Registration failed: " . $e->getMessage() . " for email: " . $email);
        }
    }
}

// === Akhir Logika Pemrosesan Registrasi ===

// Jika user sudah login, arahkan ke dashboard yang sesuai (ini tetap ada)
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard_admin.php");
        exit;
    } elseif ($_SESSION['role'] == 'juri') {
        header("Location: juri/dashboard_juri.php");
        exit;
    } elseif ($_SESSION['role'] == 'peserta') {
        header("Location: peserta/dashboard_peserta.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Akun</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-box { /* Menggunakan nama kelas yang Anda berikan */
            width: 300px;
            padding: 40px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .emoji-icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 10px;
        }
        .login-box h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-box input,
        .login-box select,
        .login-box button {
            width: calc(100% - 20px); /* Menyesuaikan lebar dengan padding jika ada */
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* Agar padding tidak menambah lebar */
        }
        .login-box button {
            background-color: #287bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .login-box button:hover {
            background-color: #004bb5;
        }
        .login-box p {
            margin-top: 15px;
            font-size: 14px;
        }
        .login-box a {
            color: #287bff;
            text-decoration: none;
        }
        .login-box a:hover {
            text-decoration: underline;
        }
        /* Style untuk pesan sukses/error */
        .message-container {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
    <?php
    // META REFRESH HANYA JIKA ADA PESAN SUKSES
    if ($tipe_pesan === 'success') {
        echo '<meta http-equiv="refresh" content="3;url=login.php">'; // Redirect setelah 3 detik
    }
    ?>
</head>
<body>
<div class="login-box">
    <div class="emoji-icon">👤</div>

    <h2>Daftar Akun</h2>

    <?php
    // Menampilkan pesan sukses atau error yang sudah disiapkan
    if (!empty($pesan_status)) {
        echo '<div class="message-container message-' . htmlspecialchars($tipe_pesan) . '">' . htmlspecialchars($pesan_status) . '</div>';
    }
    ?>

    <form action="" method="POST">
        <input type="text" name="nama" placeholder="Nama Lengkap" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="role" required>
            <option value="">-- Pilih Peran --</option>
            <option value="peserta">Peserta</option>
            <option value="juri">Juri</option>
			<option value="admin">admin</option>
        </select><br>
        <button type="submit">Daftar</button>
        <p>Sudah punya akun? <a href="login.php">Login</a></p>
    </form>
</div>
</body>
</html>