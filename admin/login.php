<?php 
session_start();

// Jika sudah login sebagai admin, langsung arahkan ke kelola_lomba.php
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: admin/kelola_lomba.php");
    exit;
}

include "../includes/db.php"; // Pastikan koneksi ke database sudah benar

// Proses login admin
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek admin di database
    $stmt = $koneksi->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $data['password'])) {
            // Set session untuk admin
            $_SESSION['email'] = $data['email'];
            $_SESSION['role'] = $data['role'];
            $_SESSION['nama'] = $data['nama'];

            // Redirect ke halaman kelola_lomba.php
            header("Location: admin/kelola_lomba.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Portal Lomba</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f8fc;
            margin: 0;
            padding: 0;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #2d7dff;
        }

        .emoji-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .login-container button {
            width: 95%;
            padding: 12px;
            background-color: #2d7dff;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }

        .login-container button:hover {
            background-color: #004bb5;
        }

        .error-msg {
            color: red;
            margin-top: 10px;
        }

        .login-container p {
            margin-top: 20px;
        }

        .login-container a {
            color: #2d7dff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="emoji-icon">👤</div>
    <h2>Login Admin</h2>

    <?php if (isset($error)) echo "<div class='error-msg'>$error</div>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="login">Login</button>
    </form>

    <p>Belum punya akun? <a href="register_admin.php">Daftar sekarang</a></p>
</div>

</body>
</html>
