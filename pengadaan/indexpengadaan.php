<?php
session_start();

// Cek apakah user sudah login dan role super admin atau purchasing
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role_id'], [2, 5])) {
    header("Location: ../login/login.php");
    exit();
}

$is_super_admin = ($_SESSION['role_id'] == 2);

// Koneksi database
$host = 'localhost';
$dbname = 'db_mitrajayasupermarket';
$db_username = 'root';
$db_password = '';

$pengadaans = [];
$error = '';
$success = '';

// Ambil pesan dari session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query dengan kolom status
    $sql = "SELECT 
                pg.idpengadaan, 
                pg.timestamp, 
                pg.subtotal_nilai,
                pg.ppn,
                pg.total_nilai,
                pg.status,
                v.nama_vendor,
                u.username
            FROM pengadaan pg
            LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
            LEFT JOIN user u ON pg.user_iduser = u.iduser
            ORDER BY pg.idpengadaan DESC";
    
    $stmt = $conn->query($sql);
    $pengadaans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle delete
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        
        // Cek apakah ada penerimaan yang terkait
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM penerimaan WHERE idpengadaan = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = "Tidak dapat menghapus! Pengadaan ini sudah memiliki data penerimaan.";
        } else {
            $conn->beginTransaction();
            try {
                // Hapus detail pengadaan
                $stmt = $conn->prepare("DELETE FROM detail_pengadaan WHERE idpengadaan = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                // Hapus pengadaan
                $stmt = $conn->prepare("DELETE FROM pengadaan WHERE idpengadaan = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                $conn->commit();
                $_SESSION['success'] = "Data pengadaan berhasil dihapus!";
            } catch(Exception $e) {
                $conn->rollBack();
                $_SESSION['error'] = "Gagal menghapus data: " . $e->getMessage();
            }
        }
        
        header("Location: indexpengadaan.php");
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Function untuk badge status (tanpa icon)
function getStatusBadge($status) {
    switch($status) {
        case 'P':
            return '<span style="background: #ffc107; color: #000; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Pending</span>';
        case 'A':
            return '<span style="background: #28a745; color: #fff; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Approved</span>';
        case 'D':
            return '<span style="background: #dc3545; color: #fff; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Ditolak</span>';
        default:
            return '<span style="background: #6c757d; color: #fff; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">-</span>';
    }
}

// Include sidebar template
$sidebar_content = '
<div class="logo">
    <div class="logo-icon">🛒</div>
    <h2>Mitra Jaya</h2>
    <p>' . ($is_super_admin ? 'Super Admin Panel' : 'Purchasing Panel') . '</p>
</div>

<ul class="menu">
    <li>
        <a href="../dashboard/dashboard' . ($is_super_admin ? 'superadmin' : 'purchasing') . '.php">
            <span class="menu-icon">📊</span> Dashboard
        </a>
    </li>
    ' . ($is_super_admin ? '<li><a href="../kartu_stok/indexkartustok.php"><span class="menu-icon">📋</span> Kartu Stok</a></li>' : '') . '
    <div class="menu-section">Data Master</div>
    <li><a href="../barang/indexbarang.php"><span class="menu-icon">📦</span> Data Barang</a></li>
    <li><a href="../vendor/indexvendor.php"><span class="menu-icon">🏢</span> Data Vendor</a></li>
    <li><a href="../satuan/indexsatuan.php"><span class="menu-icon">📏</span> Data Satuan</a></li>
    <li><a href="../margin/indexmargin.php"><span class="menu-icon">💹</span> Margin Penjualan</a></li>
    ' . ($is_super_admin ? '<li><a href="../role/indexrole.php"><span class="menu-icon">🎭</span> Data Role</a></li>' : '') . '
    ' . ($is_super_admin ? '<li><a href="../user/indexuser.php"><span class="menu-icon">👥</span> Data User</a></li>' : '') . '
    <div class="menu-section">Transaksi</div>
    ' . ($is_super_admin ? '<li><a href="../penjualan/indexpenjualan.php"><span class="menu-icon">💰</span> Data Penjualan</a></li>' : '') . '
    ' . ($is_super_admin ? '<li><a href="../penerimaan/indexpenerimaan.php"><span class="menu-icon">📥</span> Data Penerimaan</a></li>' : '') . '
    <li><a href="indexpengadaan.php" class="active"><span class="menu-icon">🛍️</span> Data Pengadaan</a></li>
    ' . ($is_super_admin ? '<li><a href="../retur/indexretur.php"><span class="menu-icon">↩️</span> Data Retur</a></li>' : '') . '
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
    <title>Data Pengadaan - Mitra Jaya Supermarket</title>
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
        .btn-logout { background: linear-gradient(135deg, #ff6b6b, #ee5a6f); color: white; }
        .btn-logout:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(238, 90, 111, 0.4); }
        .content-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .page-title { font-size: 24px; color: #333; margin: 0; padding-left: 15px; border-left: 4px solid #ff9a9e; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-add { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .data-table th { padding: 15px 12px; text-align: left; font-weight: 600; font-size: 14px; }
        .data-table td { padding: 15px 12px; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .data-table tbody tr:hover { background: #f8f9fa; }
        .btn-action { padding: 8px 15px; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.3s ease; }
        .btn-detail { background: #17a2b8; color: white; }
        .btn-detail:hover { background: #138496; transform: translateY(-2px); }
        .btn-edit { background: #ffc107; color: #000; }
        .btn-edit:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; transform: translateY(-2px); }
        .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
        .no-data { text-align: center; padding: 60px 20px; color: #999; font-size: 16px; }
        .no-data-icon { font-size: 48px; margin-bottom: 15px; opacity: 0.5; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; } .main-content { margin-left: 0; } .data-table { font-size: 12px; } .data-table th, .data-table td { padding: 10px 8px; } }
    </style>
</head>
<body>
    <div class="sidebar"><?php echo $sidebar_content; ?></div>
    <div class="main-content">
        <div class="header">
            <h1>Data Pengadaan</h1>
            <button class="btn btn-logout" onclick="window.location.href='../login/logout.php'">🚪 Logout</button>
        </div>
        
        <div class="content-card">
            <div class="toolbar">
                <h2 class="page-title">🛍️ Daftar Pengadaan</h2>
                <button class="btn btn-add" onclick="window.location.href='createpengadaan.php'">➕ Tambah Pengadaan Baru</button>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (count($pengadaans) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="11%">Tanggal</th>
                            <th width="15%">Vendor</th>
                            <th width="11%">Subtotal</th>
                            <th width="9%">PPN</th>
                            <th width="12%">Total Nilai</th>
                            <th width="10%">Status</th>
                            <th width="10%">User</th>
                            <th width="17%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pengadaans as $row): ?>
                        <tr>
                            <td><strong><?php echo $row['idpengadaan']; ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['timestamp'])); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_vendor']); ?></td>
                            <td>Rp <?php echo number_format($row['subtotal_nilai'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($row['ppn'], 0, ',', '.'); ?></td>
                            <td><strong>Rp <?php echo number_format($row['total_nilai'], 0, ',', '.'); ?></strong></td>
                            <td><?php echo getStatusBadge($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="detailpengadaan.php?id=<?php echo $row['idpengadaan']; ?>" class="btn-action btn-detail">Detail</a>
                                    <?php if ($is_super_admin): ?>
                                    <a href="editpengadaan.php?id=<?php echo $row['idpengadaan']; ?>" class="btn-action btn-edit">Edit</a>
                                    <a href="?delete=<?php echo $row['idpengadaan']; ?>" 
                                       class="btn-action btn-delete" 
                                       onclick="return confirm('❌ Yakin ingin menghapus pengadaan #<?php echo $row['idpengadaan']; ?>?\n\nData yang dihapus tidak dapat dikembalikan!')">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📦</div>
                    <strong>Belum ada data pengadaan</strong><br>
                    <p style="margin-top: 10px; color: #666;">Klik tombol "Tambah Pengadaan Baru" untuk membuat pengadaan pertama.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>