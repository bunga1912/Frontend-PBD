<?php
session_start();

// Cek apakah user sudah login dan role admin atau super admin
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

$users = [];
$error = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query untuk mengambil data user dengan join ke tabel role menggunakan VIEW
    $sql = "SELECT u.iduser, u.username, u.idrole, r.nama_role 
            FROM view_user u 
            LEFT JOIN role r ON u.idrole = r.idrole 
            ORDER BY u.iduser DESC";
    
    $stmt = $conn->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
    echo "<script>console.log('Database Error: " . addslashes($e->getMessage()) . "');</script>";
}

// Include sidebar template
$sidebar_content = '
<div class="logo">
    <div class="logo-icon">🛒</div>
    <h2>Mitra Jaya</h2>
    <p>' . ($is_super_admin ? 'Super Admin Panel' : 'Admin Panel') . '</p>
</div>

<ul class="menu">
    <li>
        <a href="../dashboard/' . ($is_super_admin ? 'dashboardsuperadmin.php' : 'dashboardadmin.php') . '">
            <span class="menu-icon">📊</span> Dashboard
        </a>
    </li>
    ' . ($is_super_admin ? '<li><a href="../kartu_stok/indexkartustok.php"><span class="menu-icon">📋</span> Kartu Stok</a></li><div class="menu-section">Data Master</div>' : '') . '
    <li><a href="../barang/indexbarang.php"><span class="menu-icon">📦</span> Data Barang</a></li>
    <li><a href="../vendor/indexvendor.php"><span class="menu-icon">🏢</span> Data Vendor</a></li>
    <li><a href="../satuan/indexsatuan.php"><span class="menu-icon">📏</span> Data Satuan</a></li>
    <li><a href="../margin_penjualan/indexmargin.php"><span class="menu-icon">💹</span> Margin Penjualan</a></li>
    <li><a href="../role/indexrole.php"><span class="menu-icon">🎭</span> Data Role</a></li>
    <li><a href="indexuser.php" class="active"><span class="menu-icon">👥</span> Data User</a></li>
    ' . ($is_super_admin ? '
    <div class="menu-section">Transaksi</div>
    <li><a href="../penjualan/indexpenjualan.php"><span class="menu-icon">💰</span> Data Penjualan</a></li>
    <li><a href="../penerimaan/indexpenerimaan.php"><span class="menu-icon">📥</span> Data Penerimaan</a></li>
    <li><a href="../pengadaan/indexpengadaan.php"><span class="menu-icon">🛍️</span> Data Pengadaan</a></li>
    <li><a href="../retur/indexretur.php"><span class="menu-icon">↩️</span> Data Retur</a></li>
    ' : '') . '
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
    <title>Data User - Mitra Jaya Supermarket</title>
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
        .role-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .role-admin { background: #e3f2fd; color: #1976d2; }
        .role-super-admin { background: #f3e5f5; color: #7b1fa2; }
        .role-kasir { background: #fff3e0; color: #f57c00; }
        .role-warehouse { background: #e8f5e9; color: #388e3c; }
        .role-purchasing { background: #fce4ec; color: #c2185b; }
        .action-buttons { display: flex; gap: 8px; }
        .btn-edit { background: #ffc107; color: #333; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s ease; }
        .btn-edit:hover { background: #ffb300; transform: translateY(-2px); }
        .btn-delete { background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s ease; }
        .btn-delete:hover { background: #c82333; transform: translateY(-2px); }
        .empty-state { text-align: center; padding: 60px 20px; color: #999; }
        .empty-state-icon { font-size: 80px; margin-bottom: 20px; opacity: 0.3; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 500; animation: slideDown 0.3s ease; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; } .main-content { margin-left: 0; } table { font-size: 12px; } th, td { padding: 10px 8px; } }
    </style>
</head>
<body>
    <div class="sidebar"><?php echo $sidebar_content; ?></div>
    <div class="main-content">
        <div class="header">
            <h1>Data User</h1>
            <button class="btn btn-logout" onclick="window.location.href='../login/logout.php'">🚪 Logout</button>
        </div>
        <div class="content-card">
            <h2 class="page-title">👥 Daftar User</h2>
            <?php if (isset($_SESSION['delete_success'])): ?>
                <div class="alert alert-success">✅ <?php echo $_SESSION['delete_success']; unset($_SESSION['delete_success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['delete_error'])): ?>
                <div class="alert alert-error">❌ <?php echo $_SESSION['delete_error']; unset($_SESSION['delete_error']); ?></div>
            <?php endif; ?>
            <div class="toolbar">
                <a href="createuser.php" class="btn btn-primary">➕ Tambah User</a>
            </div>
            <?php if (isset($error) && $error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px;"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (empty($users)): ?>
                <div class="empty-state"><div class="empty-state-icon">👥</div><h3>Tidak ada data user</h3><p>Silakan tambahkan user baru</p></div>
            <?php else: ?>
                <table>
                    <thead><tr><th>ID User</th><th>Username</th><th>Role</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <?php
                            $roleClass = 'role-admin';
                            $roleName = $user['nama_role'] ?? 'Tidak ada role';
                            if (stripos($roleName, 'super admin') !== false) { $roleClass = 'role-super-admin'; }
                            elseif (stripos($roleName, 'kasir') !== false) { $roleClass = 'role-kasir'; }
                            elseif (stripos($roleName, 'warehouse') !== false) { $roleClass = 'role-warehouse'; }
                            elseif (stripos($roleName, 'purchasing') !== false) { $roleClass = 'role-purchasing'; }
                        ?>
                        <tr>
                            <td><?php echo $user['iduser']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><span class="role-badge <?php echo $roleClass; ?>"><?php echo htmlspecialchars($roleName); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-edit" onclick="window.location.href='updateuser.php?id=<?php echo $user['iduser']; ?>'">Edit</button>
                                    <button class="btn-delete" onclick="confirmDelete(<?php echo $user['iduser']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">Delete</button>
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
        function confirmDelete(id, username) {
            if (confirm('Apakah Anda yakin ingin menghapus user "' + username + '"?')) {
                window.location.href = 'deleteuser.php?id=' + id;
            }
        }
    </script>
</body>
</html>