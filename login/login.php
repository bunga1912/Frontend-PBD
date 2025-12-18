<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['role_id'] == 1) {
        header("Location: ../dashboard/dashboardadmin.php");
    } elseif ($_SESSION['role_id'] == 2) {
        header("Location: ../dashboard/dashboardsuperadmin.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mitra Jaya Supermarket</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            overflow-x: hidden;
            position: relative;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 15s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -50px;
            right: 100px;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            right: -50px;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 50px;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px;
            z-index: 10;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(116, 235, 213, 0.4);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(5deg); }
        }

        .logo-container h1 {
            font-size: 32px;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .logo-container p {
            color: #74ebd5;
            font-size: 14px;
            font-weight: 500;
            opacity: 0.8;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .login-form h2 {
            color: #ff9a9e;
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
        }

        .login-form .subtitle {
            color: #666;
            font-size: 14px;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #555;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            opacity: 0.6;
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-group input:focus {
            outline: none;
            border-color: #74ebd5;
            background: white;
            box-shadow: 0 0 0 4px rgba(116, 235, 213, 0.1);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            color: #666;
        }

        .remember-me input[type="checkbox"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .submit-button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #ff9a9e, #fecfef);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(116, 235, 213, 0.3);
            margin-bottom: 20px;
        }

        .submit-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(116, 235, 213, 0.4);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #74ebd5;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #ff9a9e;
        }

        .info-box {
            background: rgba(116, 235, 213, 0.1);
            padding: 15px;
            border-radius: 12px;
            margin-top: 25px;
            text-align: center;
        }

        .info-box p {
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .login-container { padding: 40px 30px; }
            .logo-container h1 { font-size: 28px; }
            .form-options { flex-direction: column; gap: 15px; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>

    <div class="login-container">
        <div class="logo-container">
            <div class="logo-icon">🛒</div>
            <h1>Mitra Jaya</h1>
            <p>SUPERMARKET SYSTEM</p>
        </div>

        <form class="login-form" method="POST" action="proseslogin.php">
            <h2>Selamat Datang!</h2>
            <p class="subtitle">Silakan login untuk melanjutkan</p>

            <?php
            // Tampilkan error message jika ada
            if (isset($_SESSION['error'])) {
                echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            
            // Tampilkan pesan logout jika ada
            if (isset($_SESSION['logout_message'])) {
                echo '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-size: 14px; font-weight: 600;">';
                echo $_SESSION['logout_message'];
                echo '</div>';
                unset($_SESSION['logout_message']);
            }
            ?>

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    Ingat Saya
                </label>
            </div>

            <button type="submit" class="submit-button">
                🔐 Login ke Sistem
            </button>

            <div class="info-box">
                <p>📌 Sistem Management Supermarket<br>Untuk Pegawai Internal</p>
            </div>

            <div class="back-link">
                <a href="../index.html">← Kembali ke Halaman Utama</a>
            </div>
        </form>
    </div>
</body>
</html>