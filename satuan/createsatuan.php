<?php
session_start();

// Cek apakah user sudah login dan role admin ATAU super admin
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: ../login/login.php");
    exit();
}

$is_super_admin = ($_SESSION['role_id'] == 2);

// Koneksi database
$host = 'localhost';
$dbname = 'db_mitrajayasupermarket';
$db_username = 'root';
$db_password = '';

$success = '';
$error = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Proses form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nama_satuan = $_POST['nama_satuan'];
        $status = 1; // Status selalu Aktif untuk satuan baru
        
        // Validasi
        if (empty($nama_satuan)) {
            $error = "Nama satuan harus diisi!";
        } else {
            // Insert ke database
            $stmt = $conn->prepare("INSERT INTO satuan (nama_satuan, status) VALUES (?, ?)");
            $stmt->execute([$nama_satuan, $status]);
            
            $success = "Satuan berhasil ditambahkan!";
            header("refresh:2;url=indexsatuan.php");
        }
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Sidebar content
if ($is_super_admin) {
    $sidebar_content = '
    <div class="logo">
        <div class="logo-icon">🛒</div>
        <h2>Mitra Jaya</h2>
        <p>Super Admin Panel</p>
    </div>
    <ul class="menu">
        <li><a href="../dashboard/dashboardsuperadmin.php"><span class="menu-icon">📊</span> Dashboard</a></li>
        <li><a href="../kartu_stok/indexkartustok.php"><span class="menu-icon">📋</span> Kartu Stok</a></li>
        <div class="menu-section">Data Master</div>
        <li><a href="../barang/indexbarang.php"><span class="menu-icon">📦</span> Data Barang</a></li>
        <li><a href="../vendor/indexvendor.php"><span class="menu-icon">🏢</span> Data Vendor</a></li>
        <li><a href="indexsatuan.php" class="active"><span class="menu-icon">📏</span> Data Satuan</a></li>
        <li><a href="../margin/indexmargin.php"><span class="menu-icon">💹</span> Margin Penjualan</a></li>
        <li><a href="../role/indexrole.php"><span class="menu-icon">🎭</span> Data Role</a></li>
        <li><a href="../user/indexuser.php"><span class="menu-icon">👥</span> Data User</a></li>
        <div class="menu-section">Transaksi</div>
        <li><a href="../penjualan/indexpenjualan.php"><span class="menu-icon">💰</span> Data Penjualan</a></li>
        <li><a href="../penerimaan/indexpenerimaan.php"><span class="menu-icon">📥</span> Data Penerimaan</a></li>
        <li><a href="../pengadaan/indexpengadaan.php"><span class="menu-icon">🛍️</span> Data Pengadaan</a></li>
        <li><a href="../retur/indexretur.php"><span class="menu-icon">↩️</span> Data Retur</a></li>
    </ul>
    <div class="user-info">
        <p>Login sebagai:</p>
        <strong>' . $_SESSION['username'] . '</strong>
        <p style="margin-top: 5px; font-size: 12px;">Role: ' . $_SESSION['role_name'] . '</p>
    </div>';
} else {
    $sidebar_content = '
    <div class="logo">
        <div class="logo-icon">🛒</div>
        <h2>Mitra Jaya</h2>
        <p>Admin Panel</p>
    </div>
    <ul class="menu">
        <li><a href="../dashboard/dashboardadmin.php"><span class="menu-icon">📊</span> Dashboard</a></li>
        <li><a href="../barang/indexbarang.php"><span class="menu-icon">📦</span> Data Barang</a></li>
        <li><a href="../vendor/indexvendor.php"><span class="menu-icon">🏢</span> Data Vendor</a></li>
        <li><a href="indexsatuan.php" class="active"><span class="menu-icon">📏</span> Data Satuan</a></li>
        <li><a href="../margin/indexmargin.php"><span class="menu-icon">💹</span> Margin Penjualan</a></li>
        <li><a href="../role/indexrole.php"><span class="menu-icon">🎭</span> Data Role</a></li>
        <li><a href="../user/indexuser.php"><span class="menu-icon">👥</span> Data User</a></li>
    </ul>
    <div class="user-info">
        <p>Login sebagai:</p>
        <strong>' . $_SESSION['username'] . '</strong>
        <p style="margin-top: 5px; font-size: 12px;">Role: ' . $_SESSION['role_name'] . '</p>
    </div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Satuan - Mitra Jaya Supermarket</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; display: flex; }
        .sidebar { width: 260px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); min-height: 100vh; height: 100vh; color: #333; padding: 20px 0; position: fixed; left: 0; top: 0; box-shadow: 4px 0 30px rgba(0, 0, 0, 0.1); overflow-y: auto; display: flex; flex-direction: column; }
        .logo { text-align: center; padding: 20px; border-bottom: 1px solid rgba(0,0,0,0.1); margin-bottom: 20px; }
        .logo-icon { font-size: 50px; margin-bottom: 10px; filter: drop-shadow(0 5px 15px rgba(116, 235, 213, 0.3)); }
        .logo h2 { font-size: 22px; margin-bottom: 5px; color: #333; }
        .logo p { font-size: 12px; opacity: 0.7; color: #666; }
        .menu { list-style: none; padding: 0 10px; }
        .menu li { margin-bottom: 5px; }
        .menu a { display: flex; align-items: center; padding: 15px 20px; color: #333; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; font-weight: 500; }
        .menu a:hover, .menu a.active { background: rgba(255,255,255,0.6); color: #ff9a9e; transform: translateX(5px); }
        .menu-icon { margin-right: 15px; font-size: 20px; }
        .menu-section { padding: 15px 20px 10px 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #666; letter-spacing: 1px; margin-top: 15px; }
        .user-info { padding: 20px; border-top: 1px solid rgba(0,0,0,0.1); margin-top: auto; }
        .user-info p { font-size: 14px; margin-bottom: 5px; color: #666; }
        .user-info strong { font-size: 16px; color: #333; }
        .main-content { margin-left: 260px; flex: 1; padding: 30px; }
        .header { background: white; padding: 25px 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { color: #333; font-size: 28px; }
        .btn-logout { background: linear-gradient(135deg, #ff6b6b, #ee5a6f); color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; }
        .btn-logout:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(238, 90, 111, 0.4); }
        .form-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px; }
        .page-title { font-size: 24px; color: #333; margin-bottom: 30px; padding-bottom: 15px; border-left: 4px solid #ff9a9e; padding-left: 15px; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; color: #555; margin-bottom: 8px; }
        .form-group input, .form-group select { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #a8edea; box-shadow: 0 0 0 3px rgba(168, 237, 234, 0.1); }
        .button-group { display: flex; gap: 15px; margin-top: 30px; }
        .btn { flex: 1; padding: 15px; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .input-hint { font-size: 12px; color: #999; margin-top: 5px; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; } .main-content { margin-left: 0; } .form-card { padding: 25px; } }
    </style>
</head>
<body>
    <div class="sidebar"><?php echo $sidebar_content; ?></div>
    <div class="main-content">
        <div class="header">
            <h1>Tambah Satuan</h1>
            <button class="btn-logout" onclick="window.location.href='../login/logout.php'">🚪 Logout</button>
        </div>
        <div class="form-card">
            <h2 class="page-title">📏 Tambah Satuan Baru</h2>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama_satuan">Nama Satuan:</label>
                    <input type="text" id="nama_satuan" name="nama_satuan" required placeholder="Contoh: Pcs, Box, Kg, Liter, Pack">
                    <div class="input-hint">Masukkan satuan unit barang (misal: Pcs, Kg, Liter)</div>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='indexsatuan.php'">↩️ Kembali</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>