<?php
session_start();

// Cek apakah user sudah login dan role super admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login/login.php");
    exit();
}

// Koneksi database
$host = 'localhost';
$dbname = 'db_mitrajayasupermarket';
$db_username = 'root';
$db_password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ambil statistik untuk dashboard menggunakan VIEW
    $stats = [];
    
    // Total Barang Aktif - pakai view_barang_aktif
    $stmt = $conn->query("SELECT COUNT(*) FROM view_barang_aktif");
    $stats['barang_aktif'] = $stmt->fetchColumn();
    
    // Total Vendor Aktif - pakai view_vendor_aktif
    $stmt = $conn->query("SELECT COUNT(*) FROM view_vendor_aktif");
    $stats['vendor_aktif'] = $stmt->fetchColumn();
    
    // Total Satuan Aktif - pakai view_satuan_aktif
    $stmt = $conn->query("SELECT COUNT(*) FROM view_satuan_aktif");
    $stats['satuan_aktif'] = $stmt->fetchColumn();
    
    // Total User - pakai view_user
    $stmt = $conn->query("SELECT COUNT(*) FROM view_user");
    $stats['total_user'] = $stmt->fetchColumn();
    
    // Total Role - pakai view_role
    $stmt = $conn->query("SELECT COUNT(*) FROM view_role");
    $stats['total_role'] = $stmt->fetchColumn();
    
    // Total Margin Aktif - pakai view_margin_aktif
    $stmt = $conn->query("SELECT COUNT(*) FROM view_margin_aktif");
    $stats['margin_aktif'] = $stmt->fetchColumn();
    
    // Total Penjualan - langsung dari tabel
    $stmt = $conn->query("SELECT COUNT(*) FROM penjualan");
    $stats['total_penjualan'] = $stmt->fetchColumn();
    
    // Total Penerimaan - langsung dari tabel
    $stmt = $conn->query("SELECT COUNT(*) FROM penerimaan");
    $stats['total_penerimaan'] = $stmt->fetchColumn();
    
    // Total Pengadaan - langsung dari tabel
    $stmt = $conn->query("SELECT COUNT(*) FROM pengadaan");
    $stats['total_pengadaan'] = $stmt->fetchColumn();
    
    // Total Retur - langsung dari tabel
    $stmt = $conn->query("SELECT COUNT(*) FROM retur");
    $stats['total_retur'] = $stmt->fetchColumn();
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin - Mitra Jaya Supermarket</title>
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

        .logout-btn {
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

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(238, 90, 111, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-info h3 {
            font-size: 36px;
            color: #ff9a9e;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 14px;
        }

        .stat-icon {
            font-size: 50px;
            opacity: 0.3;
        }

        .welcome-card {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(168, 237, 234, 0.3);
        }

        .welcome-card h2 {
            font-size: 32px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .welcome-card p {
            font-size: 18px;
            opacity: 0.95;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">🛒</div>
            <h2>Mitra Jaya</h2>
            <p>Super Admin Panel</p>
        </div>

        <ul class="menu">
            <li><a href="dashboardsuperadmin.php" class="active"><span class="menu-icon">📊</span> Dashboard</a></li>
            <li><a href="../kartu_stok/indexkartustok.php"><span class="menu-icon">📋</span> Kartu Stok</a></li>
            
            <div class="menu-section">Data Master</div>
            <li><a href="../barang/indexbarang.php"><span class="menu-icon">📦</span> Data Barang</a></li>
            <li><a href="../vendor/indexvendor.php"><span class="menu-icon">🏢</span> Data Vendor</a></li>
            <li><a href="../satuan/indexsatuan.php"><span class="menu-icon">📏</span> Data Satuan</a></li>
            <li><a href="../margin_penjualan/indexmargin.php"><span class="menu-icon">💹</span> Margin Penjualan</a></li>
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
            <strong><?php echo $_SESSION['username']; ?></strong>
            <p style="margin-top: 5px; font-size: 12px;">Role: <?php echo $_SESSION['role_name']; ?></p>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Dashboard Super Admin</h1>
            <button class="logout-btn" onclick="window.location.href='../login/logout.php'">
                🚪 Logout
            </button>
        </div>

        <div class="welcome-card">
            <h2>Selamat Datang, <?php echo $_SESSION['username']; ?>! 👋</h2>
            <p>Kelola semua data master dan transaksi supermarket dengan mudah</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card" onclick="window.location.href='../barang/indexbarang.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['barang_aktif']; ?></h3>
                    <p>Barang Aktif</p>
                </div>
                <div class="stat-icon">📦</div>
            </div>

            <div class="stat-card" onclick="window.location.href='../vendor/indexvendor.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['vendor_aktif']; ?></h3>
                    <p>Vendor Aktif</p>
                </div>
                <div class="stat-icon">🏢</div>
            </div>

            <div class="stat-card" onclick="window.location.href='../satuan/indexsatuan.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['satuan_aktif']; ?></h3>
                    <p>Satuan Aktif</p>
                </div>
                <div class="stat-icon">📏</div>
            </div>

            <div class="stat-card" onclick="window.location.href='../margin_penjualan/indexmargin.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['margin_aktif']; ?></h3>
                    <p>Margin Aktif</p>
                </div>
                <div class="stat-icon">💹</div>
            </div>

            <div class="stat-card" onclick="window.location.href='../role/indexrole.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['total_role']; ?></h3>
                    <p>Total Role</p>
                </div>
                <div class="stat-icon">🎭</div>
            </div>

            <div class="stat-card" onclick="window.location.href='../user/indexuser.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['total_user']; ?></h3>
                    <p>Total User</p>
                </div>
                <div class="stat-icon">👥</div>
            </div>

            <div class="stat-card" onclick="window.location.href='../penjualan/indexpenjualan.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['total_penjualan']; ?></h3>
                    <p>Total Penjualan</p>
                </div>
                <div class="stat-icon">💰</div>
            </div>

            <div class="stat-card" onclick="window.location.href='../penerimaan/indexpenerimaan.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['total_penerimaan']; ?></h3>
                    <p>Total Penerimaan</p>
                </div>
                <div class="stat-icon">📥</div>
            </div>

            <div class="stat-card" onclick="window.location.href='../pengadaan/indexpengadaan.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['total_pengadaan']; ?></h3>
                    <p>Total Pengadaan</p>
                </div>
                <div class="stat-icon">🛍️</div>
            </div>

            <div class="stat-card" onclick="window.location.href='../retur/indexretur.php'">
                <div class="stat-info">
                    <h3><?php echo $stats['total_retur']; ?></h3>
                    <p>Total Retur</p>
                </div>
                <div class="stat-icon">↩️</div>
            </div>
        </div>
    </div>
</body>
</html>