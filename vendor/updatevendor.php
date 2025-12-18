<?php
session_start();

// Cek apakah user sudah login dan role admin (1) atau super admin (2)
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: ../login/login.php");
    exit();
}

// Tentukan apakah user adalah super admin
$is_super_admin = ($_SESSION['role_id'] == 2);

// Koneksi database
$host = 'localhost';
$dbname = 'db_mitrajayasupermarket';
$db_username = 'root';
$db_password = '';

$success = '';
$error = '';
$vendor = null;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cek apakah ada ID di URL
    if (!isset($_GET['id'])) {
        header("Location: indexvendor.php");
        exit();
    }
    
    $id = $_GET['id'];
    
    // Ambil data vendor berdasarkan ID
    $stmt = $conn->prepare("SELECT * FROM vendor WHERE idvendor = ?");
    $stmt->execute([$id]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vendor) {
        header("Location: indexvendor.php");
        exit();
    }
    
    // Proses form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nama_vendor = $_POST['nama_vendor'];
        $badan_hukum = $_POST['badan_hukum'];
        $status = $_POST['status'];
        
        // Validasi - PERBAIKAN DI SINI
        if (trim($nama_vendor) === '' || $badan_hukum === '' || $status === '') {
            $error = "Semua field harus diisi!";
        } else {
            // Update ke database
            $stmt = $conn->prepare("UPDATE vendor SET nama_vendor = ?, badan_hukum = ?, status = ? WHERE idvendor = ?");
            $stmt->execute([$nama_vendor, $badan_hukum, $status, $id]);
            
            $success = "Vendor berhasil diupdate!";
            // Redirect setelah 2 detik
            header("refresh:2;url=indexvendor.php");
        }
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vendor - Mitra Jaya Supermarket</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            display: flex;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            min-height: 100vh;
            height: 100vh;
            color: #333;
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .logo-icon {
            font-size: 50px;
            margin-bottom: 10px;
            filter: drop-shadow(0 5px 15px rgba(116, 235, 213, 0.3));
        }

        .logo h2 {
            font-size: 22px;
            margin-bottom: 5px;
            color: #333;
        }

        .logo p {
            font-size: 12px;
            opacity: 0.7;
            color: #666;
        }

        .menu {
            list-style: none;
            padding: 0 10px;
        }

        .menu li {
            margin-bottom: 5px;
        }

        .menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .menu a:hover, .menu a.active {
            background: rgba(255,255,255,0.6);
            color: #ff9a9e;
            transform: translateX(5px);
        }

        .menu-icon {
            margin-right: 15px;
            font-size: 20px;
        }

        .menu-section {
            padding: 15px 20px 10px 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
            margin-top: 15px;
        }

        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
            margin-top: auto;
        }

        .user-info p {
            font-size: 14px;
            margin-bottom: 5px;
            color: #666;
        }

        .user-info strong {
            font-size: 16px;
            color: #333;
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }

        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
        }

        .btn-logout {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(238, 90, 111, 0.4);
        }

        .form-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 800px;
        }

        .page-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-left: 4px solid #ffc107;
            padding-left: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ffc107;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1);
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #333;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .input-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .info-box {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            color: #333;
        }

        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
            .form-card {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">🛒</div>
            <h2>Mitra Jaya</h2>
            <p><?php echo $is_super_admin ? 'Super Admin Panel' : 'Admin Panel'; ?></p>
        </div>

        <ul class="menu">
            <li>
                <a href="../dashboard/<?php echo $is_super_admin ? 'dashboardsuperadmin.php' : 'dashboardadmin.php'; ?>">
                    <span class="menu-icon">📊</span> Dashboard
                </a>
            </li>
            
            <?php if ($is_super_admin): ?>
                <li><a href="../kartu_stok/indexkartustok.php"><span class="menu-icon">📋</span> Kartu Stok</a></li>
                <div class="menu-section">Data Master</div>
            <?php endif; ?>
            
            <li><a href="../barang/indexbarang.php"><span class="menu-icon">📦</span> Data Barang</a></li>
            <li><a href="indexvendor.php" class="active"><span class="menu-icon">🏢</span> Data Vendor</a></li>
            <li><a href="../satuan/indexsatuan.php"><span class="menu-icon">📏</span> Data Satuan</a></li>
            <li><a href="../margin_penjualan/indexmargin.php"><span class="menu-icon">💹</span> Margin Penjualan</a></li>
            <li><a href="../role/indexrole.php"><span class="menu-icon">🎭</span> Data Role</a></li>
            <li><a href="../user/indexuser.php"><span class="menu-icon">👥</span> Data User</a></li>
            
            <?php if ($is_super_admin): ?>
                <div class="menu-section">Transaksi</div>
                <li><a href="../penjualan/indexpenjualan.php"><span class="menu-icon">💰</span> Data Penjualan</a></li>
                <li><a href="../penerimaan/indexpenerimaan.php"><span class="menu-icon">📥</span> Data Penerimaan</a></li>
                <li><a href="../pengadaan/indexpengadaan.php"><span class="menu-icon">🛍️</span> Data Pengadaan</a></li>
                <li><a href="../retur/indexretur.php"><span class="menu-icon">↩️</span> Data Retur</a></li>
            <?php endif; ?>
        </ul>

        <div class="user-info">
            <p>Login sebagai:</p>
            <strong><?php echo $_SESSION['username']; ?></strong>
            <p style="margin-top: 5px; font-size: 12px;">Role: <?php echo $_SESSION['role_name']; ?></p>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Edit Vendor</h1>
            <button class="btn-logout" onclick="window.location.href='../login/logout.php'">
                🚪 Logout
            </button>
        </div>

        <div class="form-card">
            <h2 class="page-title">✏️ Edit Data Vendor</h2>

            <div class="info-box">
                <strong>🏢 ID Vendor: <?php echo $vendor['idvendor']; ?></strong>
                Mengubah data: <?php echo htmlspecialchars($vendor['nama_vendor']); ?>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama_vendor">Nama Vendor:</label>
                    <input type="text" id="nama_vendor" name="nama_vendor" required 
                           value="<?php echo htmlspecialchars($vendor['nama_vendor']); ?>"
                           placeholder="Contoh: PT Sumber Rejeki">
                    <div class="input-hint">Masukkan nama lengkap vendor/supplier</div>
                </div>

                <div class="form-group">
                    <label for="badan_hukum">Badan Hukum:</label>
                    <select id="badan_hukum" name="badan_hukum" required>
                        <option value="">-- Pilih Badan Hukum --</option>
                        <option value="1" <?php echo ($vendor['badan_hukum'] == 1) ? 'selected' : ''; ?>>Ya (PT/CV)</option>
                        <option value="0" <?php echo ($vendor['badan_hukum'] == 0) ? 'selected' : ''; ?>>Tidak (UD/Perorangan)</option>
                    </select>
                    <div class="input-hint">Pilih apakah vendor memiliki badan hukum resmi</div>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="1" <?php echo ($vendor['status'] == 1) ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?php echo ($vendor['status'] == 0) ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-warning">
                        💾 Update
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='indexvendor.php'">
                        ↩️ Kembali
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>