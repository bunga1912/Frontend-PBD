<?php
session_start();

// Izinkan admin (1) dan super admin (2) untuk hapus role
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: ../login/login.php");
    exit();
}

$host = 'localhost';
$dbname = 'db_mitrajayasupermarket';
$db_username = 'root';
$db_password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Cek apakah role digunakan oleh user
        $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE idrole = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error'] = "Role tidak dapat dihapus karena masih digunakan oleh $count user!";
        } else {
            // Hapus role
            $stmt = $conn->prepare("DELETE FROM role WHERE idrole = ?");
            if ($stmt->execute([$id])) {
                // Cek apakah ada row yang terhapus
                if ($stmt->rowCount() > 0) {
                    $_SESSION['success'] = "Role berhasil dihapus!";
                } else {
                    $_SESSION['error'] = "Role tidak ditemukan atau sudah terhapus!";
                }
            } else {
                $_SESSION['error'] = "Gagal menghapus role!";
            }
        }
    } else {
        $_SESSION['error'] = "ID Role tidak valid!";
    }
    
} catch(PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: indexrole.php");
exit();
?>