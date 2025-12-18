<?php
session_start();

// Cek apakah user sudah login dan role super admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login/login.php");
    exit();
}

$is_super_admin = true;

// Koneksi database
$host = 'localhost';
$dbname = 'db_mitrajayasupermarket';
$db_username = 'root';
$db_password = '';

$penjualans = [];
$error = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query untuk mendapatkan data penjualan dengan JOIN ke user dan margin_penjualan
    $sql = "SELECT p.idpenjualan, p.created_at, p.subtotal_nilai, p.ppn, p.total_nilai,
                   p.iduser, p.idmargin_penjualan,
                   u.username, m.persen as margin_persen
            FROM penjualan p
            LEFT JOIN user u ON p.iduser = u.iduser
            LEFT JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan
            ORDER BY p.idpenjualan DESC";
    
    $stmt = $conn->query($sql);
    $penjualans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
    echo "<script>console.log('Database Error: " . addslashes($e->getMessage()) . "');</script>";
}

// Include sidebar template
$sidebar_content = '
<div class="logo">
    <div class="logo-icon">🛒</div>
    <h2>Mitra Jaya</h2>
    <p>Super Admin Panel</p>
</div>

<ul class="menu">
    <li>
        <a href="../dashboard/dashboardsuperadmin.php">
            <span class="menu-icon">📊</span> Dashboard
        </a>
    </li>
    <li><a href="../kartu_stok/indexkartustok.php"><span class="menu-icon">📋</span> Kartu Stok</a></li>
    <div class="menu-section">Data Master</div>
    <li><a href="../barang/indexbarang.php"><span class="menu-icon">📦</span> Data Barang</a></li>
    <li><a href="../vendor/indexvendor.php"><span class="menu-icon">🏢</span> Data Vendor</a></li>
    <li><a href="../satuan/indexsatuan.php"><span class="menu-icon">📏</span> Data Satuan</a></li>
    <li><a href="../margin/indexmargin.php"><span class="menu-icon">💹</span> Margin Penjualan</a></li>
    <li><a href="../role/indexrole.php"><span class="menu-icon">🎭</span> Data Role</a></li>
    <li><a href="../user/indexuser.php"><span class="menu-icon">👥</span> Data User</a></li>
    <div class="menu-section">Transaksi</div>
    <li><a href="indexpenjualan.php" class="active"><span class="menu-icon">💰</span> Data Penjualan</a></li>
    <li><a href="../penerimaan/indexpenerimaan.php"><span class="menu-icon">📥</span> Data Penerimaan</a></li>
    <li><a href="../pengadaan/indexpengadaan.php"><span class="menu-icon">🛍️</span> Data Pengadaan</a></li>
    <li><a href="../retur/indexretur.php"><span class="menu-icon">↩️</span> Data Retur</a></li>
</ul>

<div class="user-info">
    <p>Login sebagai:</p>
    <strong>' . $_SESSION['username'] . '</strong>
    <p style="margin-top: 5px; font-size: 12px;">Role: ' . $_SESSION['role_name'] . '</p>
</div>';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penjualan - Mitra Jaya Supermarket</title>
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
        .btn { border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-logout { background: linear-gradient(135deg, #ff6b6b, #ee5a6f); color: white; }
        .btn-logout:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(238, 90, 111, 0.4); }
        .content-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .page-title { font-size: 24px; color: #333; margin-bottom: 25px; padding-bottom: 15px; border-left: 4px solid #ff9a9e; padding-left: 15px; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        th { padding: 15px; text-align: left; font-weight: 600; font-size: 14px; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #555; }
        tr:hover { background: #f9f9f9; }
        .action-buttons { display: flex; gap: 8px; }
        .btn-view { background: #17a2b8; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s ease; }
        .btn-view:hover { background: #138496; transform: translateY(-2px); }
        .btn-edit { background: #ffc107; color: #333; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s ease; }
        .btn-edit:hover { background: #ffb300; transform: translateY(-2px); }
        .btn-delete { background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s ease; }
        .btn-delete:hover { background: #c82333; transform: translateY(-2px); }
        .empty-state { text-align: center; padding: 60px 20px; color: #999; }
        .empty-state-icon { font-size: 80px; margin-bottom: 20px; opacity: 0.3; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 500; animation: slideDown 0.3s ease; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .datetime-text { font-size: 13px; color: #666; }
        .price-text { font-weight: 600; color: #667eea; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; } .main-content { margin-left: 0; } table { font-size: 12px; } th, td { padding: 10px 8px; } }
    </style>
</head>
<body>
    <div class="sidebar"><?php echo $sidebar_content; ?></div>
    <div class="main-content">
        <div class="header">
            <h1>Data Penjualan</h1>
            <button class="btn btn-logout" onclick="window.location.href='../login/logout.php'">🚪 Logout</button>
        </div>
        <div class="content-card">
            <h2 class="page-title">💰 Daftar Transaksi Penjualan</h2>
            <?php if (isset($_SESSION['delete_success'])): ?>
                <div class="alert alert-success">✅ <?php echo $_SESSION['delete_success']; unset($_SESSION['delete_success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['delete_error'])): ?>
                <div class="alert alert-error">❌ <?php echo $_SESSION['delete_error']; unset($_SESSION['delete_error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">✅ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <div class="toolbar">
                <a href="createpenjualan.php" class="btn btn-primary">➕ Tambah Penjualan Baru</a>
            </div>
            <?php if (isset($error) && $error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px;"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (empty($penjualans)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">💰</div>
                    <h3>Tidak ada data penjualan</h3>
                    <p>Silakan tambahkan transaksi penjualan baru</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Subtotal</th>
                            <th>PPN</th>
                            <th>Margin (%)</th>
                            <th>Total Nilai</th>
                            <th>Kasir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($penjualans as $penjualan): ?>
                        <tr>
                            <td><?php echo $penjualan['idpenjualan']; ?></td>
                            <td>
                                <span class="datetime-text">
                                    <?php 
                                        if (isset($penjualan['created_at']) && $penjualan['created_at']) {
                                            $date = new DateTime($penjualan['created_at']);
                                            echo $date->format('d/m/Y H:i');
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </span>
                            </td>
                            <td><span class="price-text">Rp <?php echo number_format($penjualan['subtotal_nilai'], 0, ',', '.'); ?></span></td>
                            <td><span class="price-text">Rp <?php echo number_format($penjualan['ppn'], 0, ',', '.'); ?></span></td>
                            <td><strong><?php echo number_format($penjualan['margin_persen'] ?? 0, 2); ?>%</strong></td>
                            <td><span class="price-text">Rp <?php echo number_format($penjualan['total_nilai'], 0, ',', '.'); ?></span></td>
                            <td><?php echo htmlspecialchars($penjualan['username'] ?? '-'); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-view" onclick="window.location.href='detailpenjualan.php?id=<?php echo $penjualan['idpenjualan']; ?>'">Detail</button>
                                    <button class="btn-edit" onclick="window.location.href='updatepenjualan.php?id=<?php echo $penjualan['idpenjualan']; ?>'">Edit</button>
                                    <button class="btn-delete" onclick="confirmDelete(<?php echo $penjualan['idpenjualan']; ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus transaksi penjualan ini?\n\nPerhatian: Data detail penjualan juga akan terhapus!')) {
                window.location.href = 'deletepenjualan.php?id=' + id;
            }
        }
    </script>
</body>
</html>