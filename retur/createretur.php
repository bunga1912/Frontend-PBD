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

$error = '';
$penerimaanList = [];
$detailPenerimaan = [];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ambil daftar penerimaan
    $stmt = $conn->query("SELECT p.idpenerimaan, p.created_at, pen.idpengadaan, v.nama_vendor
                          FROM penerimaan p
                          LEFT JOIN pengadaan pen ON p.idpengadaan = pen.idpengadaan
                          LEFT JOIN vendor v ON pen.vendor_idvendor = v.idvendor
                          ORDER BY p.idpenerimaan DESC");
    $penerimaanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Jika ada pilihan penerimaan, ambil detail barangnya
    if (isset($_POST['idpenerimaan']) && !isset($_POST['submit_retur'])) {
        $idpenerimaan = $_POST['idpenerimaan'];
        
        $stmt = $conn->prepare("SELECT dp.iddetail_penerimaan, dp.jumlah_terima, dp.harga_satuan_terima,
                                       b.nama as nama_barang, s.nama_satuan
                                FROM detail_penerimaan dp
                                LEFT JOIN barang b ON dp.barang_idbarang = b.idbarang
                                LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                                WHERE dp.idpenerimaan = :id AND dp.jumlah_terima > 0
                                ORDER BY dp.iddetail_penerimaan ASC");
        $stmt->bindParam(':id', $idpenerimaan);
        $stmt->execute();
        $detailPenerimaan = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Proses form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_retur'])) {
        $idpenerimaan = $_POST['idpenerimaan'];
        $iduser = $_SESSION['iduser'];
        
        // Validasi item retur
        if (!isset($_POST['items']) || empty($_POST['items'])) {
            $error = "Minimal harus ada 1 item yang diretur!";
        } else {
            $conn->beginTransaction();
            
            try {
                // Insert ke tabel retur
                $stmt = $conn->prepare("INSERT INTO retur (idpenerimaan, iduser) VALUES (:idpenerimaan, :iduser)");
                $stmt->bindParam(':idpenerimaan', $idpenerimaan);
                $stmt->bindParam(':iduser', $iduser);
                $stmt->execute();
                
                $idretur = $conn->lastInsertId();
                
                // Insert detail retur
                $stmt_detail = $conn->prepare("INSERT INTO detail_retur (jumlah, alasan, idretur, iddetail_penerimaan) 
                                               VALUES (:jumlah, :alasan, :idretur, :iddetail_penerimaan)");
                
                $hasValidItem = false;
                foreach ($_POST['items'] as $item) {
                    if (!empty($item['jumlah']) && $item['jumlah'] > 0) {
                        $stmt_detail->bindParam(':jumlah', $item['jumlah']);
                        $stmt_detail->bindParam(':alasan', $item['alasan']);
                        $stmt_detail->bindParam(':idretur', $idretur);
                        $stmt_detail->bindParam(':iddetail_penerimaan', $item['iddetail_penerimaan']);
                        $stmt_detail->execute();
                        $hasValidItem = true;
                    }
                }
                
                if (!$hasValidItem) {
                    throw new Exception("Tidak ada item dengan jumlah retur yang valid!");
                }
                
                $conn->commit();
                
                $_SESSION['success'] = "Retur berhasil ditambahkan!";
                header("Location: indexretur.php");
                exit();
                
            } catch(Exception $e) {
                $conn->rollBack();
                $error = "Gagal menyimpan data retur: " . $e->getMessage();
            }
        }
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

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
    <li><a href="../satuan/indexsatuan.php"><span class="menu-icon">📏</span> Data Satuan</a></li>
    <li><a href="../margin/indexmargin.php"><span class="menu-icon">💹</span> Margin Penjualan</a></li>
    <li><a href="../role/indexrole.php"><span class="menu-icon">🎭</span> Data Role</a></li>
    <li><a href="../user/indexuser.php"><span class="menu-icon">👥</span> Data User</a></li>
    <div class="menu-section">Transaksi</div>
    <li><a href="../penjualan/indexpenjualan.php"><span class="menu-icon">💰</span> Data Penjualan</a></li>
    <li><a href="../penerimaan/indexpenerimaan.php"><span class="menu-icon">📥</span> Data Penerimaan</a></li>
    <li><a href="../pengadaan/indexpengadaan.php"><span class="menu-icon">🛍️</span> Data Pengadaan</a></li>
    <li><a href="indexretur.php" class="active"><span class="menu-icon">↩️</span> Data Retur</a></li>
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
    <title>Tambah Retur - Mitra Jaya Supermarket</title>
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
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
        .form-group select { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; }
        .form-group select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .btn { border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 500; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .section-title { font-size: 18px; color: #333; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        th { padding: 15px; text-align: left; font-weight: 600; font-size: 14px; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #555; }
        tr:hover { background: #f9f9f9; }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        input[type="number"], textarea { width: 100%; padding: 8px 10px; border: 1px solid #e0e0e0; border-radius: 5px; font-size: 13px; }
        textarea { resize: vertical; min-height: 60px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .info-note { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #856404; font-size: 14px; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="sidebar"><?php echo $sidebar_content; ?></div>
    <div class="main-content">
        <div class="header">
            <h1>Tambah Retur</h1>
            <button class="btn-logout" onclick="window.location.href='../login/logout.php'">🚪 Logout</button>
        </div>
        
        <div class="content-card">
            <h2 class="page-title">↩️ Form Retur Barang</h2>
            
            <div class="info-note">
                ⚠️ <strong>Perhatian:</strong> Retur hanya bisa dibuat dari penerimaan yang sudah ada. Pilih penerimaan terlebih dahulu untuk melihat barang yang bisa diretur.
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Pilih Penerimaan *</label>
                    <select name="idpenerimaan" required onchange="this.form.submit()">
                        <option value="">-- Pilih Penerimaan --</option>
                        <?php foreach ($penerimaanList as $pen): ?>
                            <option value="<?php echo $pen['idpenerimaan']; ?>" 
                                <?php echo (isset($_POST['idpenerimaan']) && $_POST['idpenerimaan'] == $pen['idpenerimaan']) ? 'selected' : ''; ?>>
                                ID: <?php echo $pen['idpenerimaan']; ?> - 
                                Pengadaan: <?php echo $pen['idpengadaan']; ?> - 
                                Vendor: <?php echo htmlspecialchars($pen['nama_vendor']); ?> - 
                                <?php echo date('d/m/Y', strtotime($pen['created_at'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if (!empty($detailPenerimaan)): ?>
                    <h3 class="section-title">📦 Detail Barang yang Diterima</h3>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Jumlah Diterima</th>
                                <th>Jumlah Retur</th>
                                <th>Alasan Retur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detailPenerimaan as $index => $item): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['nama_barang']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['nama_satuan']); ?></td>
                                <td><?php echo $item['jumlah_terima']; ?></td>
                                <td>
                                    <input type="hidden" name="items[<?php echo $index; ?>][iddetail_penerimaan]" value="<?php echo $item['iddetail_penerimaan']; ?>">
                                    <input type="number" name="items[<?php echo $index; ?>][jumlah]" 
                                           min="0" max="<?php echo $item['jumlah_terima']; ?>" 
                                           value="0" placeholder="0">
                                </td>
                                <td>
                                    <textarea name="items[<?php echo $index; ?>][alasan]" 
                                              placeholder="Isi alasan jika ada retur"></textarea>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="form-actions">
                        <button type="submit" name="submit_retur" class="btn btn-primary">💾 Simpan Retur</button>
                        <a href="indexretur.php" class="btn btn-secondary">❌ Batal</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>