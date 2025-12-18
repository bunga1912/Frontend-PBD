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

$penerimaan = null;
$pengadaan = null;
$detail_items = [];
$error = '';

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: indexpenerimaan.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query untuk mendapatkan data penerimaan dengan subquery untuk subtotal
    $sql = "SELECT pn.idpenerimaan, pn.created_at, pn.status, pn.idpengadaan, pn.iduser,
                   COALESCE((SELECT SUM(sub_total_terima) FROM detail_penerimaan dp 
                            WHERE dp.idpenerimaan = pn.idpenerimaan), 0) as subtotal_terima,
                   u.username
            FROM penerimaan pn
            LEFT JOIN user u ON pn.iduser = u.iduser
            WHERE pn.idpenerimaan = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $penerimaan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$penerimaan) {
        $_SESSION['delete_error'] = "Data penerimaan tidak ditemukan!";
        header("Location: indexpenerimaan.php");
        exit();
    }
    
    // Query untuk mendapatkan data pengadaan
    $sql_pengadaan = "SELECT pg.idpengadaan, pg.timestamp, pg.subtotal_nilai, pg.ppn, pg.total_nilai,
                             v.nama_vendor, v.badan_hukum
                      FROM pengadaan pg
                      LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
                      WHERE pg.idpengadaan = :idpengadaan";
    
    $stmt_pengadaan = $conn->prepare($sql_pengadaan);
    $stmt_pengadaan->bindParam(':idpengadaan', $penerimaan['idpengadaan']);
    $stmt_pengadaan->execute();
    $pengadaan = $stmt_pengadaan->fetch(PDO::FETCH_ASSOC);
    
    // Query untuk mendapatkan detail item penerimaan dengan perbandingan pengadaan
    $sql_detail = "SELECT dp.iddetail_penerimaan, dp.jumlah_terima, dp.harga_satuan_terima, dp.sub_total_terima,
                          b.nama as nama_barang, b.idbarang, s.nama_satuan,
                          dpg.jumlah as jumlah_pesan, dpg.harga_satuan as harga_pesan
                   FROM detail_penerimaan dp
                   LEFT JOIN barang b ON dp.barang_idbarang = b.idbarang
                   LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                   LEFT JOIN detail_pengadaan dpg ON dpg.idbarang = b.idbarang 
                        AND dpg.idpengadaan = :idpengadaan
                   WHERE dp.idpenerimaan = :id
                   ORDER BY dp.iddetail_penerimaan ASC";
    
    $stmt_detail = $conn->prepare($sql_detail);
    $stmt_detail->bindParam(':id', $id);
    $stmt_detail->bindParam(':idpengadaan', $penerimaan['idpengadaan']);
    $stmt_detail->execute();
    $detail_items = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
    echo "<script>console.log('Database Error: " . addslashes($e->getMessage()) . "');</script>";
}

// Function untuk status label
function getStatusLabel($status) {
    switch($status) {
        case 'P': return 'Pending (50%)';
        case 'S': return 'Sebagian (70%)';
        case 'C': return 'Selesai (100%)';
        case 'B': return 'Batal (0%)';
        default: return 'Unknown';
    }
}

function getStatusClass($status) {
    switch($status) {
        case 'P': return 'status-pending';
        case 'S': return 'status-sebagian';
        case 'C': return 'status-selesai';
        case 'B': return 'status-batal';
        default: return '';
    }
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
    <li><a href="../penjualan/indexpenjualan.php"><span class="menu-icon">💰</span> Data Penjualan</a></li>
    <li><a href="indexpenerimaan.php" class="active"><span class="menu-icon">📥</span> Data Penerimaan</a></li>
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
    <title>Detail Penerimaan #<?php echo $id; ?> - Mitra Jaya Supermarket</title>
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
        .btn-back { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-back:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-logout { background: linear-gradient(135deg, #ff6b6b, #ee5a6f); color: white; }
        .btn-logout:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(238, 90, 111, 0.4); }
        .content-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .page-title { font-size: 24px; color: #333; margin-bottom: 25px; padding-bottom: 15px; border-left: 4px solid #ff9a9e; padding-left: 15px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .info-item { padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 4px solid #667eea; }
        .info-label { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .info-value { font-size: 16px; font-weight: 600; color: #333; }
        .price-value { color: #667eea; font-size: 18px; }
        .section-title { font-size: 18px; color: #333; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
        .status-badge { padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-sebagian { background: #cce5ff; color: #004085; }
        .status-selesai { background: #d4edda; color: #155724; }
        .status-batal { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        th { padding: 15px; text-align: left; font-weight: 600; font-size: 14px; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #555; }
        tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .price-text { font-weight: 600; color: #667eea; }
        .summary-box { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); padding: 20px; border-radius: 10px; margin-top: 30px; }
        .summary-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 16px; }
        .summary-row.total { font-size: 20px; font-weight: 700; border-top: 2px solid rgba(0,0,0,0.1); padding-top: 15px; margin-top: 10px; color: #333; }
        .toolbar { display: flex; gap: 10px; margin-top: 30px; }
        .comparison-badge { font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: 600; margin-left: 5px; }
        .comparison-less { background: #fff3cd; color: #856404; }
        .comparison-equal { background: #d4edda; color: #155724; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; } .main-content { margin-left: 0; } .info-grid { grid-template-columns: 1fr; } table { font-size: 12px; } th, td { padding: 10px 8px; } }
    </style>
</head>
<body>
    <div class="sidebar"><?php echo $sidebar_content; ?></div>
    <div class="main-content">
        <div class="header">
            <h1>Detail Penerimaan #<?php echo $id; ?></h1>
            <button class="btn btn-logout" onclick="window.location.href='../login/logout.php'">🚪 Logout</button>
        </div>
        
        <?php if ($penerimaan): ?>
        <div class="content-card">
            <h2 class="page-title">
                📋 Informasi Penerimaan
                <span class="status-badge <?php echo getStatusClass($penerimaan['status']); ?>">
                    <?php echo getStatusLabel($penerimaan['status']); ?>
                </span>
            </h2>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">ID Penerimaan</div>
                    <div class="info-value">#<?php echo $penerimaan['idpenerimaan']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Penerimaan</div>
                    <div class="info-value">
                        <?php 
                            $date = new DateTime($penerimaan['created_at']);
                            echo $date->format('d/m/Y H:i:s');
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">User Warehouse</div>
                    <div class="info-value"><?php echo htmlspecialchars($penerimaan['username']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Nilai Terima</div>
                    <div class="price-value">Rp <?php echo number_format($penerimaan['subtotal_terima'], 0, ',', '.'); ?></div>
                </div>
            </div>
            
            <?php if ($pengadaan): ?>
            <h3 class="section-title">📦 Informasi Pengadaan</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">ID Pengadaan</div>
                    <div class="info-value">#<?php echo $pengadaan['idpengadaan']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Vendor</div>
                    <div class="info-value"><?php echo htmlspecialchars($pengadaan['nama_vendor']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Pengadaan</div>
                    <div class="info-value">
                        <?php 
                            $date = new DateTime($pengadaan['timestamp']);
                            echo $date->format('d/m/Y H:i:s');
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Nilai Pengadaan</div>
                    <div class="price-value">Rp <?php echo number_format($pengadaan['total_nilai'], 0, ',', '.'); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <h3 class="section-title">📦 Detail Barang yang Diterima</h3>
            
            <?php if (empty($detail_items)): ?>
                <p style="text-align: center; color: #999; padding: 40px;">Tidak ada item dalam penerimaan ini</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Satuan</th>
                            <th class="text-center">Qty Pesan</th>
                            <th class="text-center">Qty Terima</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($detail_items as $item): 
                            $persentase = ($item['jumlah_pesan'] > 0) 
                                ? round(($item['jumlah_terima'] / $item['jumlah_pesan']) * 100) 
                                : 0;
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($item['nama_barang']); ?></strong></td>
                            <td><?php echo htmlspecialchars($item['nama_satuan'] ?? '-'); ?></td>
                            <td class="text-center"><?php echo number_format($item['jumlah_pesan'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <strong><?php echo number_format($item['jumlah_terima'], 0, ',', '.'); ?></strong>
                                <?php if ($item['jumlah_terima'] < $item['jumlah_pesan']): ?>
                                    <span class="comparison-badge comparison-less"><?php echo $persentase; ?>%</span>
                                <?php elseif ($item['jumlah_terima'] == $item['jumlah_pesan']): ?>
                                    <span class="comparison-badge comparison-equal">100%</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right price-text">Rp <?php echo number_format($item['harga_satuan_terima'], 0, ',', '.'); ?></td>
                            <td class="text-right price-text">Rp <?php echo number_format($item['sub_total_terima'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div class="summary-box">
                <div class="summary-row total">
                    <span>TOTAL NILAI PENERIMAAN:</span>
                    <span>Rp <?php echo number_format($penerimaan['subtotal_terima'], 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <div class="toolbar">
                <a href="indexpenerimaan.php" class="btn btn-back">← Kembali ke Daftar</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>