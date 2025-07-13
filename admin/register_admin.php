<?php 
session_start();
include "../includes/db.php"; // Pastikan koneksi database sudah benar

// Proses pendaftaran admin
if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama = $_POST['nama'];

    // Validasi password dan konfirmasi password
    if ($password === $confirm_password) {
        // Cek apakah email sudah terdaftar
        $stmt = $koneksi->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Hash password sebelum disimpan ke database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert data admin baru ke database
            $query = "INSERT INTO users (email, password, role, nama) VALUES ('$email', '$hashed_password', 'admin', '$nama')";
            if ($koneksi->query($query) === TRUE) {
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'admin';
                $_SESSION['nama'] = $nama;

                // Redirect ke halaman kelola lomba setelah registrasi sukses
                header("Location: kelola_lomba.php");
                exit;
            } else {
                $error = "Terjadi kesalahan saat menyimpan data.";
            }
        } else {
            $error = "Email sudah terdaftar!";
        }
    } else {
        $error = "Password dan Konfirmasi Password tidak cocok!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - Portal Lomba</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f8fc;
            margin: 0;
            padding: 0;
        }

        .register-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            text-align: center;
        }

        .register-container h2 {
            margin-bottom: 20px;
            color: #2d7dff;
        }

        .emoji-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .register-container input[type="email"],
        .register-container input[type="password"],
        .register-container input[type="text"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .register-container button {
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

        .register-container button:hover {
            background-color: #004bb5;
        }

        .error-msg {
            color: red;
            margin-top: 10px;
        }

        .register-container p {
            margin-top: 20px;
        }

        .register-container a {
            color: #2d7dff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="emoji-icon">👤</div>
    <h2>Register Admin</h2>

    <?php if (isset($error)) echo "<div class='error-msg'>$error</div>"; ?>

    <form method="POST">
        <input type="text" name="nama" placeholder="Nama" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required><br>
        <button type="submit" name="register">Register</button>
    </form>

    <p>Sudah punya akun? <a href="login.php">Login sekarang</a></p>
</div>

</body>
</html>
