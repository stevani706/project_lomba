<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Lomba Mahasiswa</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap'); /* Contoh font dari Google Fonts */

        body {
            font-family: 'Poppins', Arial, sans-serif; /* Gunakan font yang lebih modern */
            background: linear-gradient(135deg, #7F8C8D 0%, #2C3E50 100%); /* Gradien abu-biru gelap */
            display: flex;
            flex-direction: column; 
            align-items: center;    
            justify-content: center; 
            min-height: 100vh;      
            margin: 0;
            text-align: center;
            padding: 20px; 
            box-sizing: border-box; 
            color: #ecf0f1; /* Warna teks umum lebih cerah */
        }
        .welcome-text {
            font-size: 3em; /* Lebih besar lagi */
            color: #FFFFFF; /* Warna putih untuk kontras */
            margin-bottom: 40px; 
            font-weight: 700; /* Lebih tebal */
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3); /* Efek bayangan teks */
        }
        .login-box-container {
            background-color: rgba(255, 255, 255, 0.95); /* Sedikit transparan putih */
            padding: 40px; /* Padding lebih besar */
            border-radius: 15px; /* Sudut lebih membulat */
            box-shadow: 0 8px 30px rgba(0,0,0,0.25); /* Bayangan lebih dramatis */
            text-align: center;
            width: 100%;
            max-width: 450px; /* Perbesar lebar kotak login */
            transition: transform 0.3s ease-in-out; /* Efek hover */
        }
        .login-box-container:hover {
            transform: translateY(-5px); /* Sedikit terangkat saat di-hover */
        }
        .login-box-container div.prompt-text {
            font-size: 1.5em; /* Ukuran font untuk "Silahkan" lebih besar */
            color: #34495e; /* Warna teks yang lebih gelap */
            margin-bottom: 25px;
            font-weight: 600;
        }
        .image-wrapper {
            border: 2px solid #3498db; /* Border biru cerah */
            border-radius: 8px; /* Sedikit radius pada border gambar */
            padding: 10px; /* Padding di sekitar gambar */
            margin-bottom: 30px; /* Jarak bawah gambar */
            display: inline-block; 
            max-width: 90%; /* Pastikan responsif */
            overflow: hidden; /* Pastikan gambar tidak keluar dari wrapper */
        }
        .image-wrapper img {
            max-width: 100%; 
            height: auto;   /* Tinggi otomatis */
            max-height: 250px; /* Batasi tinggi maksimum gambar */
            display: block; 
            border-radius: 5px; /* Radius pada gambar itu sendiri */
            object-fit: contain; /* Memastikan gambar pas tanpa terpotong */
        }
        .login-button {
            display: inline-block;
            padding: 15px 35px; /* Padding lebih besar untuk tombol */
            background: linear-gradient(45deg, #3498db, #2980b9); /* Gradien biru */
            color: white;
            text-decoration: none;
            border-radius: 8px; /* Radius tombol lebih besar */
            font-size: 1.2em; /* Ukuran font tombol lebih besar */
            font-weight: 700;
            letter-spacing: 1px; /* Jarak antar huruf */
            box-shadow: 0 4px 10px rgba(0,0,0,0.2); /* Bayangan tombol */
            transition: all 0.3s ease;
        }
        .login-button:hover {
            background: linear-gradient(45deg, #2980b9, #3498db); /* Ubah gradien saat hover */
            box-shadow: 0 6px 15px rgba(0,0,0,0.3); /* Bayangan lebih besar saat hover */
            transform: translateY(-2px); /* Sedikit terangkat */
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 600px) {
            .welcome-text {
                font-size: 2.2em;
                margin-bottom: 25px;
            }
            .login-box-container {
                padding: 25px;
                max-width: 90%;
            }
            .login-box-container div.prompt-text {
                font-size: 1.3em;
                margin-bottom: 20px;
            }
            .login-button {
                padding: 12px 25px;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-text">
        Selamat datang di lomba mahasiswa
    </div>

    <div class="login-box-container">
        <div class="prompt-text">Silahkan</div>
        
        <div class="image-wrapper">
            <img src="assets/img/logo.jpg" alt="Ilustrasi Login">
            </div>
        
        <a href="login.php" class="login-button">Login</a>
    </div>
</body>
</html>