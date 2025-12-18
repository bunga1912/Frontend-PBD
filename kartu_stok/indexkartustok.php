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

$kartu_stoks = [];
$barangs = [];
$error = '';
$filter_barang = isset($_GET['idbarang']) ? $_GET['idbarang'] : '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ambil list barang untuk dropdown filter
    $stmt = $conn->query("SELECT idbarang, nama FROM view_barang_aktif ORDER BY nama ASC");
    $barangs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query kartu stok dengan join ke barang
    if ($filter_barang) {
        $sql = "SELECT k.*, b.nama as nama_barang, b.jenis, s.nama_satuan
                FROM kartu_stok k
                INNER JOIN barang b ON k.idbarang = b.idbarang
                LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                WHERE k.idbarang = :idbarang
                ORDER BY k.created_at DESC, k.idkartu_stok DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idbarang', $filter_barang, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $sql = "SELECT k.*, b.nama as nama_barang, b.jenis, s.nama_satuan
                FROM kartu_stok k
                INNER JOIN barang b ON k.idbarang = b.idbarang
                LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                ORDER BY k.created_at DESC, k.idkartu_stok DESC
                LIMIT 100";
        $stmt = $conn->query($sql);
    }
    
    $kartu_stoks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
    echo "<script>console.log('Database Error: " . addslashes($e->getMessage()) . "');</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Stok - Mitra Jaya Supermarket</title>
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
        .content-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .page-title { font-size: 24px; color: #333; margin-bottom: 25px; padding-bottom: 15px; border-left: 4px solid #ff9a9e; padding-left: 15px; }
        .info-box { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); padding: 20px; border-radius: 10px; margin-bottom: 25px; }
        .info-box h3 { font-size: 16px; color: #333; margin-bottom: 10px; }
        .info-box p { font-size: 14px; color: #555; line-height: 1.6; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap; }
        .filter-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .filter-form label { font-weight: 600; color: #555; }
        .filter-form select { padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; min-width: 250px; transition: all 0.3s ease; }
        .filter-form select:focus { outline: none; border-color: #667eea; }
        .btn-filter { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-reset { background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .btn-reset:hover { background: #5a6268; transform: translateY(-2px); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        th { padding: 15px 10px; text-align: left; font-weight: 600; font-size: 13px; }
        td { padding: 15px 10px; border-bottom: 1px solid #f0f0f0; font-size: 13px; color: #555; }
        tr:hover { background: #f9f9f9; }
        .datetime-text { font-size: 12px; color: #666; }
        .jenis-badge { padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: 600; display: inline-block; }
        .jenis-M { background: #d4edda; color: #155724; }
        .jenis-K { background: #f8d7da; color: #721c24; }
        .jenis-R { background: #fff3cd; color: #856404; }
        .jenis-P { background: #d1ecf1; color: #0c5460; }
        .stock-positive { color: #28a745; font-weight: 600; }
        .stock-negative { color: #dc3545; font-weight: 600; }
        .stock-zero { color: #6c757d; font-weight: 600; }
        .empty-state { text-align: center; padding: 60px 20px; color: #999; }
        .empty-state-icon { font-size: 80px; margin-bottom: 20px; opacity: 0.3; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 500; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; } .main-content { margin-left: 0; } table { font-size: 11px; } th, td { padding: 10px 5px; } }
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
            <li><a href="../dashboard/dashboardsuperadmin.php"><span class="menu-icon">📊</span> Dashboard</a></li>
            <li><a href="indexkartustok.php" class="active"><span class="menu-icon">📋</span> Kartu Stok</a></li>
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
            <h1>Kartu Stok</h1>
            <button class="btn-logout" onclick="window.location.href='../login/logout.php'">🚪 Logout</button>
        </div>

        <div class="content-card">
            <h2 class="page-title">📋 History Kartu Stok</h2>

            <div class="info-box">
                <h3>ℹ️ Informasi Kartu Stok</h3>
                <p>
                    <strong>Kartu Stok</strong> adalah catatan otomatis dari semua pergerakan barang (masuk & keluar). 
                    Data ini <strong>tidak dapat diedit manual</strong> karena merupakan audit trail dari transaksi.
                    Jenis Transaksi: <span class="jenis-badge jenis-M">M = Masuk</span> 
                    <span class="jenis-badge jenis-K">K = Keluar</span> 
                    <span class="jenis-badge jenis-R">R = Retur</span> 
                    <span class="jenis-badge jenis-P">P = Pengadaan</span>
                </p>
            </div>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <form method="GET" action="" class="filter-form">
                    <label for="idbarang">Filter Barang:</label>
                    <select name="idbarang" id="idbarang">
                        <option value="">-- Semua Barang (100 Terakhir) --</option>
                        <?php foreach ($barangs as $barang): ?>
                            <option value="<?php echo $barang['idbarang']; ?>" <?php echo ($filter_barang == $barang['idbarang']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($barang['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-filter">🔍 Filter</button>
                    <?php if ($filter_barang): ?>
                        <a href="indexkartustok.php" class="btn-reset">↻ Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($kartu_stoks)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <h3>Tidak ada data kartu stok</h3>
                    <p><?php echo $filter_barang ? 'Barang ini belum memiliki transaksi' : 'Belum ada transaksi yang tercatat'; ?></p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Barang</th>
                            <th>Jenis</th>
                            <th>ID Transaksi</th>
                            <th>Masuk</th>
                            <th>Keluar</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kartu_stoks as $ks): ?>
                        <tr>
                            <td>
                                <span class="datetime-text">
                                    <?php 
                                        if ($ks['created_at']) {
                                            $date = new DateTime($ks['created_at']);
                                            echo $date->format('d/m/Y H:i:s');
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($ks['nama_barang']); ?></strong>
                                <br><small style="color: #999;"><?php echo htmlspecialchars($ks['jenis']); ?> - <?php echo htmlspecialchars($ks['nama_satuan'] ?? '-'); ?></small>
                            </td>
                            <td>
                                <?php
                                    $jenis_text = '';
                                    $jenis_class = '';
                                    switch ($ks['jenis_transaksi']) {
                                        case 'M': $jenis_text = 'Masuk'; $jenis_class = 'jenis-M'; break;
                                        case 'K': $jenis_text = 'Keluar'; $jenis_class = 'jenis-K'; break;
                                        case 'R': $jenis_text = 'Retur'; $jenis_class = 'jenis-R'; break;
                                        case 'P': $jenis_text = 'Pengadaan'; $jenis_class = 'jenis-P'; break;
                                        default: $jenis_text = $ks['jenis_transaksi']; $jenis_class = 'jenis-M';
                                    }
                                ?>
                                <span class="jenis-badge <?php echo $jenis_class; ?>"><?php echo $jenis_text; ?></span>
                            </td>
                            <td>#<?php echo $ks['idtransaksi']; ?></td>
                            <td><strong style="color: #28a745;"><?php echo $ks['masuk'] > 0 ? '+' . $ks['masuk'] : '-'; ?></strong></td>
                            <td><strong style="color: #dc3545;"><?php echo $ks['keluar'] > 0 ? '-' . $ks['keluar'] : '-'; ?></strong></td>
                            <td>
                                <strong class="<?php 
                                    if ($ks['stock'] > 0) echo 'stock-positive';
                                    elseif ($ks['stock'] < 0) echo 'stock-negative';
                                    else echo 'stock-zero';
                                ?>">
                                    <?php echo $ks['stock']; ?>
                                </strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>