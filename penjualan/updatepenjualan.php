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

$error = '';
$penjualan = null;
$detail_items = [];
$margins = [];
$barangs = [];
$users = [];

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: indexpenjualan.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ambil data penjualan
    $stmt = $conn->prepare("SELECT * FROM penjualan WHERE idpenjualan = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $penjualan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$penjualan) {
        $_SESSION['delete_error'] = "Data penjualan tidak ditemukan!";
        header("Location: indexpenjualan.php");
        exit();
    }
    
    // Ambil detail items
    $stmt = $conn->prepare("SELECT * FROM detail_penjualan WHERE penjualan_idpenjualan = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $detail_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil data margin aktif
    $stmt = $conn->query("SELECT idmargin_penjualan, persen FROM view_margin_aktif ORDER BY persen ASC");
    $margins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil data barang aktif
    $stmt = $conn->query("SELECT b.idbarang, b.nama, b.harga, s.nama_satuan 
                          FROM view_barang_aktif b
                          LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                          ORDER BY b.nama ASC");
    $barangs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil data user dengan role kasir
    $stmt = $conn->query("SELECT iduser, username FROM user WHERE idrole = 3 ORDER BY username ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Proses form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $iduser = $_POST['iduser'];
        $idmargin = $_POST['idmargin_penjualan'];
        $ppn = (int)$_POST['ppn'];
        
        if (!isset($_POST['items']) || empty($_POST['items'])) {
            $error = "Minimal harus ada 1 item barang!";
        } else {
            $conn->beginTransaction();
            
            try {
                // Update data penjualan
                $sql = "UPDATE penjualan SET ppn = :ppn, iduser = :iduser, 
                        idmargin_penjualan = :idmargin WHERE idpenjualan = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':ppn', $ppn);
                $stmt->bindParam(':iduser', $iduser);
                $stmt->bindParam(':idmargin', $idmargin);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                // Hapus detail lama
                $stmt = $conn->prepare("DELETE FROM detail_penjualan WHERE penjualan_idpenjualan = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                // Reset subtotal
                $stmt = $conn->prepare("UPDATE penjualan SET subtotal_nilai = 0 WHERE idpenjualan = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                // Insert detail baru
                $sql_detail = "INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang) 
                               VALUES (:harga, :jumlah, :subtotal, :idpenjualan, :idbarang)";
                $stmt_detail = $conn->prepare($sql_detail);
                
                foreach ($_POST['items'] as $item) {
                    if (!empty($item['idbarang']) && !empty($item['jumlah']) && $item['jumlah'] > 0) {
                        $subtotal = $item['harga_satuan'] * $item['jumlah'];
                        
                        $stmt_detail->bindParam(':harga', $item['harga_satuan']);
                        $stmt_detail->bindParam(':jumlah', $item['jumlah']);
                        $stmt_detail->bindParam(':subtotal', $subtotal);
                        $stmt_detail->bindParam(':idpenjualan', $id);
                        $stmt_detail->bindParam(':idbarang', $item['idbarang']);
                        $stmt_detail->execute();
                    }
                }
                
                // Panggil stored procedure
                $stmt = $conn->prepare("CALL hitung_total_penjualan(:id)");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                $conn->commit();
                
                $_SESSION['success'] = "Data penjualan berhasil diupdate!";
                header("Location: indexpenjualan.php");
                exit();
                
            } catch(Exception $e) {
                $conn->rollBack();
                $error = "Gagal mengupdate data: " . $e->getMessage();
            }
        }
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
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
    <title>Edit Penjualan #<?php echo $id; ?> - Mitra Jaya Supermarket</title>
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
        .page-title { font-size: 24px; color: #333; margin-bottom: 25px; padding-bottom: 15px; border-left: 4px solid #ff9a9e; padding-left: 15px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 500; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .section-title { font-size: 18px; color: #333; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .item-table th { background: #f8f9fa; padding: 12px; text-align: left; font-size: 13px; color: #666; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
        .item-table td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .item-table input, .item-table select { padding: 8px 10px; border: 1px solid #e0e0e0; border-radius: 5px; font-size: 13px; width: 100%; }
        .btn-add-item { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; margin-top: 10px; }
        .btn-add-item:hover { background: #218838; }
        .btn-remove { background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; }
        .btn-remove:hover { background: #c82333; }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .summary-box { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 15px; }
        .summary-row.total { font-size: 18px; font-weight: 700; border-top: 2px solid #dee2e6; padding-top: 12px; margin-top: 8px; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; } .main-content { margin-left: 0; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="sidebar"><?php echo $sidebar_content; ?></div>
    <div class="main-content">
        <div class="header">
            <h1>Edit Penjualan #<?php echo $id; ?></h1>
            <button class="btn btn-logout" onclick="window.location.href='../login/logout.php'">🚪 Logout</button>
        </div>
        
        <div class="content-card">
            <h2 class="page-title">✏️ Edit Data Penjualan</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Kasir *</label>
                        <select name="iduser" required>
                            <option value="">-- Pilih Kasir --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['iduser']; ?>"
                                    <?php echo ($user['iduser'] == $penjualan['iduser']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Margin Penjualan *</label>
                        <select name="idmargin_penjualan" required>
                            <option value="">-- Pilih Margin --</option>
                            <?php foreach ($margins as $margin): ?>
                                <option value="<?php echo $margin['idmargin_penjualan']; ?>"
                                    <?php echo ($margin['idmargin_penjualan'] == $penjualan['idmargin_penjualan']) ? 'selected' : ''; ?>>
                                    <?php echo number_format($margin['persen'], 2); ?>%
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>PPN (Rp) *</label>
                        <input type="number" name="ppn" id="ppn" value="<?php echo $penjualan['ppn']; ?>" min="0" required>
                    </div>
                </div>
                
                <h3 class="section-title">🛒 Detail Item Barang</h3>
                
                <table class="item-table">
                    <thead>
                        <tr>
                            <th width="35%">Barang</th>
                            <th width="20%">Harga Satuan</th>
                            <th width="15%">Jumlah</th>
                            <th width="20%">Subtotal</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemBody">
                        <?php 
                        $index = 0;
                        foreach ($detail_items as $item): 
                        ?>
                        <tr>
                            <td>
                                <select name="items[<?php echo $index; ?>][idbarang]" class="item-select" 
                                        onchange="updatePrice(this, <?php echo $index; ?>)" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?php foreach ($barangs as $barang): ?>
                                        <option value="<?php echo $barang['idbarang']; ?>" 
                                                data-harga="<?php echo $barang['harga']; ?>"
                                                <?php echo ($barang['idbarang'] == $item['idbarang']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($barang['nama']); ?> 
                                            (<?php echo htmlspecialchars($barang['nama_satuan']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[<?php echo $index; ?>][harga_satuan]" 
                                       class="harga-input" id="harga_<?php echo $index; ?>" 
                                       readonly value="<?php echo $item['harga_satuan']; ?>">
                            </td>
                            <td>
                                <input type="number" name="items[<?php echo $index; ?>][jumlah]" 
                                       class="jumlah-input" id="jumlah_<?php echo $index; ?>" 
                                       min="1" value="<?php echo $item['jumlah']; ?>" 
                                       onchange="calculateSubtotal(<?php echo $index; ?>)" required>
                            </td>
                            <td>
                                <input type="number" class="subtotal-input" id="subtotal_<?php echo $index; ?>" 
                                       readonly value="<?php echo $item['subtotal']; ?>">
                            </td>
                            <td>
                                <button type="button" class="btn-remove" onclick="removeItem(this)">Hapus</button>
                            </td>
                        </tr>
                        <?php 
                        $index++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
                
                <button type="button" class="btn-add-item" onclick="addItem()">+ Tambah Item</button>
                
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="displaySubtotal">Rp 0</span>
                    </div>
                    <div class="summary-row">
                        <span>PPN:</span>
                        <span id="displayPPN">Rp 0</span>
                    </div>
                    <div class="summary-row total">
                        <span>TOTAL (belum termasuk margin):</span>
                        <span id="displayTotal">Rp 0</span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Update Penjualan</button>
                    <a href="indexpenjualan.php" class="btn btn-secondary">❌ Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let itemCount = <?php echo $index; ?>;
        const barangData = <?php echo json_encode($barangs); ?>;
        
        function addItem() {
            const tbody = document.getElementById('itemBody');
            const row = document.createElement('tr');
            
            let options = '<option value="">-- Pilih Barang --</option>';
            barangData.forEach(barang => {
                options += `<option value="${barang.idbarang}" data-harga="${barang.harga}">
                    ${barang.nama} (${barang.nama_satuan})
                </option>`;
            });
            
            row.innerHTML = `
                <td>
                    <select name="items[${itemCount}][idbarang]" class="item-select" 
                            onchange="updatePrice(this, ${itemCount})" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${itemCount}][harga_satuan]" 
                           class="harga-input" id="harga_${itemCount}" readonly value="0">
                </td>
                <td>
                    <input type="number" name="items[${itemCount}][jumlah]" 
                           class="jumlah-input" id="jumlah_${itemCount}" min="1" value="1" 
                           onchange="calculateSubtotal(${itemCount})" required>
                </td>
                <td>
                    <input type="number" class="subtotal-input" id="subtotal_${itemCount}" readonly value="0">
                </td>
                <td>
                    <button type="button" class="btn-remove" onclick="removeItem(this)">Hapus</button>
                </td>
            `;
            
            tbody.appendChild(row);
            itemCount++;
        }
        
        function removeItem(btn) {
            const tbody = document.getElementById('itemBody');
            if (tbody.children.length > 1) {
                btn.closest('tr').remove();
                calculateTotal();
            } else {
                alert('Minimal harus ada 1 item!');
            }
        }
        
        function updatePrice(select, index) {
            const selectedOption = select.options[select.selectedIndex];
            const harga = selectedOption.getAttribute('data-harga') || 0;
            document.getElementById('harga_' + index).value = harga;
            calculateSubtotal(index);
        }
        
        function calculateSubtotal(index) {
            const harga = parseFloat(document.getElementById('harga_' + index).value) || 0;
            const jumlah = parseFloat(document.getElementById('jumlah_' + index).value) || 0;
            const subtotal = harga * jumlah;
            document.getElementById('subtotal_' + index).value = subtotal;
            calculateTotal();
        }
        
        function calculateTotal() {
            const subtotals = document.querySelectorAll('.subtotal-input');
            let total = 0;
            subtotals.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            
            const ppn = parseFloat(document.getElementById('ppn').value) || 0;
            const grandTotal = total + ppn;
            
            document.getElementById('displaySubtotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('displayPPN').textContent = 'Rp ' + ppn.toLocaleString('id-ID');
            document.getElementById('displayTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }
        
        document.getElementById('ppn').addEventListener('input', calculateTotal);
        
        // Initial calculation
        calculateTotal();
    </script>
</body>
</html>