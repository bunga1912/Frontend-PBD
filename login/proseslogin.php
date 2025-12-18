<?php
session_start();

// Cek apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

// Koneksi database
$host = 'localhost';
$dbname = 'db_mitrajayasupermarket';
$db_username = 'root';
$db_password = '';

try {
    // Koneksi ke database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ambil data dari form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validasi input tidak boleh kosong
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username dan password tidak boleh kosong!";
        header("Location: login.php");
        exit();
    }
    
    // Query untuk cek user di database dengan JOIN ke tabel role
    $stmt = $conn->prepare("
        SELECT u.*, r.nama_role 
        FROM user u
        JOIN role r ON u.idrole = r.idrole
        WHERE u.username = :username
    ");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Cek apakah user ditemukan
    if (!$result) {
        $_SESSION['error'] = "Username tidak ditemukan!";
        header("Location: login.php");
        exit();
    }
    
    // Verifikasi password (plain text sesuai database Anda)
    // Untuk production, sebaiknya gunakan password_hash() dan password_verify()
    if ($password === $result['password']) {
        // Login berhasil - Set session
        $_SESSION['user_id'] = $result['iduser'];
        $_SESSION['username'] = $result['username'];
        $_SESSION['role_id'] = $result['idrole'];
        $_SESSION['role_name'] = $result['nama_role'];
        $_SESSION['logged_in'] = true;
        
        // Set cookie jika remember me dicentang
        if ($remember) {
            setcookie('user_login', $username, time() + (86400 * 30), "/"); // 30 hari
        }
        
        // Redirect berdasarkan role
        switch ($result['idrole']) {
            case 1: // Admin
                header("Location: ../dashboard/dashboardadmin.php");
                break;
            case 2: // Super Admin
                header("Location: ../dashboard/dashboardsuperadmin.php");
                break;
            case 3: // Kasir
                header("Location: ../dashboard/dashboardindex.php");
                break;
            case 4: // Warehouse
                header("Location: ../dashboard/dashboardindex.php");
                break;
            case 5: // Purchasing
                header("Location: ../dashboard/dashboardindex.php");
                break;
            default:
                header("Location: ../dashboard/dashboardindex.php");
        }
        exit();
        
    } else {
        // Password salah
        $_SESSION['error'] = "Password salah!";
        header("Location: login.php");
        exit();
    }
    
} catch(PDOException $e) {
    // Error database
    $_SESSION['error'] = "Error koneksi database: " . $e->getMessage();
    header("Location: login.php");
    exit();
}
?>