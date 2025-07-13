<?php
session_start(); // Pastikan session dimulai di paling atas

include "includes/db.php"; // Sesuaikan path koneksi database Anda

// Variabel untuk menampung pesan status (login error, dll.)
$pesan_login = '';
$tipe_pesan_login = '';

// --- BAGIAN 1: PENGATUR LALU LINTAS / ROUTER ---
// Jika pengguna sudah login, arahkan ke dashboard yang sesuai
if (isset($_SESSION['role']) && isset($_SESSION['user_id'])) {
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

// --- BAGIAN 2: LOGIKA PEMROSESAN LOGIN (jika belum login) ---
// Periksa apakah ada pesan dari halaman lain (misal dari register.php)
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_login = $_SESSION['pesan_sukses'];
    $tipe_pesan_login = 'success';
    unset($_SESSION['pesan_sukses']);
}
if (isset($_SESSION['pesan_error'])) {
    $pesan_login = $_SESSION['pesan_error'];
    $tipe_pesan_login = 'error';
    unset($_SESSION['pesan_error']);
}


// Hanya proses jika ada pengiriman form POST (untuk login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    $role_login = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($email) || empty($password)) {
        $pesan_login = "Email dan password harus diisi!";
        $tipe_pesan_login = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_login = "Format email tidak valid!";
        $tipe_pesan_login = 'error';
    } else {
        // Cek kredensial di database
        $stmt = $koneksi->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ? AND role = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $email, $role_login);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Login berhasil, set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect ke dashboard yang sesuai
                    if ($user['role'] == 'admin') {
                        header("Location: admin/dashboard_admin.php");
                    } elseif ($user['role'] == 'juri') {
                        header("Location: juri/dashboard_juri.php");
                    } elseif ($user['role'] == 'peserta') {
                        header("Location: peserta/dashboard_peserta.php");
                    }
                    exit;
                } else {
                    $pesan_login = "Password salah. Silakan coba lagi.";
                    $tipe_pesan_login = 'error';
                }
            } else {
                $pesan_login = "Kombinasi email/peran tidak ditemukan. Silakan cek kembali.";
                $tipe_pesan_login = 'error';
            }
            $stmt->close();
        } else {
            $pesan_login = "Terjadi kesalahan sistem saat login (prepare error).";
            $tipe_pesan_login = 'error';
            error_log("Error preparing login statement: " . $koneksi->error);
        }
    }
}
// --- AKHIR LOGIKA PEMROSESAN LOGIN ---

$koneksi->close(); // Tutup koneksi setelah semua operasi selesai
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login / Portal Lomba</title>
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
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
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
</head>
<body>
<div class="login-box">
    <div class="emoji-icon">👤</div>

    <h2>Login Akun</h2>

    <?php
    // Menampilkan pesan sukses atau error
    if (!empty($pesan_login)) {
        echo '<div class="message-container message-' . htmlspecialchars($tipe_pesan_login) . '">' . htmlspecialchars($pesan_login) . '</div>';
    }
    ?>

    <form action="" method="POST"> <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="role" required>
            <option value="">-- Login Sebagai --</option>
            <option value="peserta">Peserta</option>
            <option value="juri">Juri</option>
            <option value="admin">Admin</option>
        </select><br>
        <button type="submit">Login</button>
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </form>
</div>
</body>
</html>