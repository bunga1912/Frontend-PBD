<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitra Jaya Supermarket - Sistem Informasi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            overflow: hidden;
            position: relative;
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

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 10;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 60px;
        }

        .logo-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(116, 235, 213, 0.4);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(5deg); }
        }

        .logo-container h1 {
            font-size: 28px;
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

        .sidebar-content {
            width: 100%;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 40px;
        }

        .welcome-text h2 {
            color: #ff9a9e;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .welcome-text p {
            color: #666;
            font-size: 14px;
        }

        .login-button {
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
            margin-bottom: 15px;
        }

        .login-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(116, 235, 213, 0.4);
        }

        .info-box {
            background: rgba(116, 235, 213, 0.1);
            padding: 20px;
            border-radius: 15px;
            margin-top: 30px;
            text-align: center;
        }

        .info-box p {
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
            z-index: 5;
        }

        .content-box {
            text-align: center;
            color: white;
            animation: fadeIn 1.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .content-box h1 {
            font-size: 64px;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            line-height: 1.2;
        }

        .content-box p {
            font-size: 24px;
            opacity: 0.95;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .decorative-icon {
            font-size: 120px;
            margin-bottom: 30px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; padding: 30px 20px; }
            .main-content { padding: 40px 20px; }
            .content-box h1 { font-size: 42px; }
            .content-box p { font-size: 18px; }
            .decorative-icon { font-size: 80px; }
        }
    </style>
</head>
<body>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>

    <div class="sidebar">
        <div class="logo-container">
            <div class="logo-icon">🛒</div>
            <h1>Mitra Jaya</h1>
            <p>SUPERMARKET</p>
        </div>

        <div class="sidebar-content">
            <div class="welcome-text">
                <h2>Selamat Datang!</h2>
                <p>Sistem Informasi Management</p>
            </div>

            <button class="login-button" onclick="window.location.href='login/login.php'">
                🔐 Login ke Sistem
            </button>

            <div class="info-box">
                <p>📌 Sistem Management Supermarket<br>Untuk Pegawai Internal</p>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="content-box">
            <div class="decorative-icon">🏪</div>
            <h1>Mitra Jaya<br>Supermarket</h1>
            <p>Sistem Informasi Management</p>
        </div>
    </div>
</body>
</html>