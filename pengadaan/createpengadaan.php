<?php
session_start();

// Cek apakah user sudah login dan role purchasing atau super admin
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

$error = '';
$vendors = [];
$barangs = [];
$users = [];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ambil data vendor aktif
    $stmt = $conn->query("SELECT idvendor, nama_vendor, badan_hukum 
                          FROM view_vendor_aktif 
                          ORDER BY nama_vendor ASC");
    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil data barang aktif dengan satuan
    $stmt = $conn->query("SELECT b.idbarang, b.nama, b.jenis, b.harga, s.nama_satuan
                          FROM view_barang_aktif b
                          LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                          ORDER BY b.jenis, b.nama ASC");
    $barangs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil data user dengan role purchasing atau super admin
    $stmt = $conn->query("SELECT u.iduser, u.username, r.nama_role 
                          FROM user u
                          JOIN role r ON u.idrole = r.idrole
                          WHERE u.idrole IN (2, 5)
                          ORDER BY u.username ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Proses form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $iduser = $_POST['iduser'];
        $idvendor = $_POST['idvendor'];
        $ppn = (int)$_POST['ppn'];
        $status = 'P'; // P = Pending
        
        // Validasi item barang
        if (!isset($_POST['items']) || empty($_POST['items'])) {
            $error = "Minimal harus ada 1 item barang yang dipesan!";
        } else {
            $conn->beginTransaction();
            
            try {
                // Insert ke tabel pengadaan (subtotal=0, total=0, akan diupdate trigger)
                $sql = "INSERT INTO pengadaan (user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai) 
                        VALUES (:iduser, :status, :idvendor, 0, :ppn, 0)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':iduser', $iduser);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':idvendor', $idvendor);
                $stmt->bindParam(':ppn', $ppn);
                $stmt->execute();
                
                $idpengadaan = $conn->lastInsertId();
                
                // Insert detail pengadaan (trigger akan update subtotal otomatis)
                $sql_detail = "INSERT INTO detail_pengadaan (harga_satuan, jumlah, sub_total, idbarang, idpengadaan) 
                               VALUES (:harga, :jumlah, :subtotal, :idbarang, :idpengadaan)";
                $stmt_detail = $conn->prepare($sql_detail);
                
                foreach ($_POST['items'] as $item) {
                    if (!empty($item['idbarang']) && !empty($item['jumlah']) && $item['jumlah'] > 0) {
                        $jumlah = (int)$item['jumlah'];
                        $harga_satuan = (int)$item['harga_satuan'];
                        $subtotal = $jumlah * $harga_satuan;
                        
                        $stmt_detail->bindParam(':harga', $harga_satuan);
                        $stmt_detail->bindParam(':jumlah', $jumlah);
                        $stmt_detail->bindParam(':subtotal', $subtotal);
                        $stmt_detail->bindParam(':idbarang', $item['idbarang']);
                        $stmt_detail->bindParam(':idpengadaan', $idpengadaan);
                        $stmt_detail->execute();
                    }
                }
                
                // Panggil stored procedure untuk hitung total nilai
                $stmt = $conn->prepare("CALL hitung_total_pengadaan(:id)");
                $stmt->bindParam(':id', $idpengadaan);
                $stmt->execute();
                
                $conn->commit();
                
                $_SESSION['success'] = "Pengadaan berhasil ditambahkan dengan ID: " . $idpengadaan;
                header("Location: indexpengadaan.php");
                exit();
                
            } catch(Exception $e) {
                $conn->rollBack();
                $error = "Gagal menyimpan data: " . $e->getMessage();
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
    <p>' . ($is_super_admin ? 'Super Admin Panel' : 'Purchasing Panel') . '</p>
</div>

<ul class="menu">
    <li>
        <a href="../dashboard/dashboard' . ($is_super_admin ? 'superadmin' : 'admin') . '.php">
            <span class="menu-icon">📊</span> Dashboard
        </a>
    </li>
    <li><a href="../kartu_stok/indexkartustok.php"><span class="menu-icon">📋</span> Kartu Stok</a></li>
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
    <title>Tambah Pengadaan - Mitra Jaya Supermarket</title>
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
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .section-title { font-size: 18px; color: #333; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .item-table th { background: #f8f9fa; padding: 12px; text-align: left; font-size: 13px; color: #666; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
        .item-table td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .item-table input, .item-table select { padding: 8px 10px; border: 1px solid #e0e0e0; border-radius: 5px; font-size: 13px; width: 100%; }
        .item-table .btn-remove { background: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; }
        .item-table .btn-remove:hover { background: #c82333; }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; padding: 8px 16px; font-size: 13px; }
        .btn-success:hover { background: #218838; }
        .summary-box { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 15px; }
        .summary-row.total { font-size: 18px; font-weight: 700; border-top: 2px solid #dee2e6; padding-top: 12px; margin-top: 8px; color: #667eea; }
        .text-center { text-align: center; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; } .main-content { margin-left: 0; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="sidebar"><?php echo $sidebar_content; ?></div>
    <div class="main-content">
        <div class="header">
            <h1>Tambah Pengadaan Baru</h1>
            <button class="btn btn-logout" onclick="window.location.href='../login/logout.php'">🚪 Logout</button>
        </div>
        
        <div class="content-card">
            <h2 class="page-title">➕ Form Pengadaan Barang</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="alert alert-info">
                ℹ️ <strong>Informasi:</strong> Tambahkan minimal 1 item barang untuk membuat pengadaan. PPN akan ditambahkan ke total nilai.
            </div>
            
            <form method="POST" id="formPengadaan">
                <div class="form-row">
                    <div class="form-group">
                        <label>Vendor *</label>
                        <select name="idvendor" required>
                            <option value="">-- Pilih Vendor --</option>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?php echo $vendor['idvendor']; ?>">
                                    <?php echo htmlspecialchars($vendor['nama_vendor']); ?>
                                    <?php echo $vendor['badan_hukum'] == '1' ? '(PT)' : '(CV/UD)'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>User Pembuat *</label>
                        <select name="iduser" required>
                            <option value="">-- Pilih User --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['iduser']; ?>" 
                                    <?php echo ($user['iduser'] == $_SESSION['user_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['username']); ?> 
                                    (<?php echo htmlspecialchars($user['nama_role']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>PPN (Rp) *</label>
                        <input type="number" name="ppn" id="ppnInput" value="0" min="0" required onchange="calculateTotal()">
                    </div>
                </div>
                
                <div class="section-title">
                    <span>📦 Detail Barang yang Dipesan</span>
                    <button type="button" class="btn btn-success" onclick="addItem()">➕ Tambah Item</button>
                </div>
                
                <table class="item-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="30%">Nama Barang *</th>
                            <th width="10%">Satuan</th>
                            <th width="15%">Harga Satuan</th>
                            <th width="10%" class="text-center">Qty *</th>
                            <th width="20%">Subtotal</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemBody">
                        <!-- Items akan ditambahkan di sini via JavaScript -->
                    </tbody>
                </table>
                
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal Barang:</span>
                        <span id="displaySubtotal">Rp 0</span>
                    </div>
                    <div class="summary-row">
                        <span>PPN:</span>
                        <span id="displayPPN">Rp 0</span>
                    </div>
                    <div class="summary-row total">
                        <span>TOTAL NILAI PENGADAAN:</span>
                        <span id="displayTotal">Rp 0</span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Simpan Pengadaan</button>
                    <a href="indexpengadaan.php" class="btn btn-secondary">❌ Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const barangData = <?php echo json_encode($barangs); ?>;
        let itemCounter = 0;
        
        function addItem() {
            const tbody = document.getElementById('itemBody');
            const row = document.createElement('tr');
            row.id = `item-${itemCounter}`;
            
            row.innerHTML = `
                <td class="text-center">${itemCounter + 1}</td>
                <td>
                    <select name="items[${itemCounter}][idbarang]" class="barang-select" 
                            onchange="updateItemInfo(${itemCounter})" required>
                        <option value="">-- Pilih Barang --</option>
                        ${barangData.map(b => `
                            <option value="${b.idbarang}" 
                                    data-harga="${b.harga}" 
                                    data-satuan="${b.nama_satuan || '-'}">
                                ${b.nama} (${b.jenis})
                            </option>
                        `).join('')}
                    </select>
                </td>
                <td id="satuan-${itemCounter}">-</td>
                <td>
                    <input type="number" name="items[${itemCounter}][harga_satuan]" 
                           id="harga-${itemCounter}" readonly 
                           style="background: #f8f9fa;" value="0">
                </td>
                <td>
                    <input type="number" name="items[${itemCounter}][jumlah]" 
                           id="jumlah-${itemCounter}" min="1" value="1" 
                           onchange="calculateItemSubtotal(${itemCounter})" required>
                </td>
                <td>
                    <input type="text" id="subtotal-${itemCounter}" 
                           readonly style="background: #f8f9fa;" value="Rp 0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn-remove" onclick="removeItem(${itemCounter})">
                        🗑️ Hapus
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
            itemCounter++;
            updateRowNumbers();
        }
        
        function updateItemInfo(index) {
            const select = document.querySelector(`select[name="items[${index}][idbarang]"]`);
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                const harga = option.getAttribute('data-harga');
                const satuan = option.getAttribute('data-satuan');
                
                document.getElementById(`harga-${index}`).value = harga;
                document.getElementById(`satuan-${index}`).textContent = satuan;
                
                calculateItemSubtotal(index);
            } else {
                document.getElementById(`harga-${index}`).value = 0;
                document.getElementById(`satuan-${index}`).textContent = '-';
                document.getElementById(`subtotal-${index}`).value = 'Rp 0';
                calculateTotal();
            }
        }
        
        function calculateItemSubtotal(index) {
            const harga = parseFloat(document.getElementById(`harga-${index}`).value) || 0;
            const jumlah = parseFloat(document.getElementById(`jumlah-${index}`).value) || 0;
            const subtotal = harga * jumlah;
            
            document.getElementById(`subtotal-${index}`).value = 'Rp ' + subtotal.toLocaleString('id-ID');
            calculateTotal();
        }
        
        function calculateTotal() {
            const tbody = document.getElementById('itemBody');
            const rows = tbody.getElementsByTagName('tr');
            let subtotal = 0;
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const index = row.id.split('-')[1];
                const harga = parseFloat(document.getElementById(`harga-${index}`).value) || 0;
                const jumlah = parseFloat(document.getElementById(`jumlah-${index}`).value) || 0;
                subtotal += harga * jumlah;
            }
            
            const ppn = parseFloat(document.getElementById('ppnInput').value) || 0;
            const total = subtotal + ppn;
            
            document.getElementById('displaySubtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('displayPPN').textContent = 'Rp ' + ppn.toLocaleString('id-ID');
            document.getElementById('displayTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        
        function removeItem(index) {
            const row = document.getElementById(`item-${index}`);
            if (row) {
                row.remove();
                updateRowNumbers();
                calculateTotal();
            }
        }
        
        function updateRowNumbers() {
            const tbody = document.getElementById('itemBody');
            const rows = tbody.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                rows[i].querySelector('td:first-child').textContent = i + 1;
            }
        }
        
        // Tambah 1 item default saat halaman load
        window.onload = function() {
            addItem();
        };
        
        // Validasi sebelum submit
        document.getElementById('formPengadaan').addEventListener('submit', function(e) {
            const tbody = document.getElementById('itemBody');
            const rows = tbody.getElementsByTagName('tr');
            
            if (rows.length === 0) {
                e.preventDefault();
                alert('❌ Minimal harus ada 1 item barang yang dipesan!');
                return false;
            }
            
            let hasValidItem = false;
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const index = row.id.split('-')[1];
                const barangSelect = document.querySelector(`select[name="items[${index}][idbarang]"]`);
                const jumlah = parseFloat(document.getElementById(`jumlah-${index}`).value) || 0;
                
                if (barangSelect.value && jumlah > 0) {
                    hasValidItem = true;
                    break;
                }
            }
            
            if (!hasValidItem) {
                e.preventDefault();
                alert('❌ Minimal harus ada 1 item barang yang valid (barang dipilih dan qty > 0)!');
                return false;
            }
        });
    </script>
</body>
</html>