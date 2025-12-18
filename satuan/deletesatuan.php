<?php
session_start();

// Izinkan admin (1) dan super admin (2) untuk hapus satuan
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
        $idsatuan = $_GET['id'];
        
        // Cek status satuan
        $stmt = $conn->prepare("SELECT status FROM satuan WHERE idsatuan = ?");
        $stmt->execute([$idsatuan]);
        $satuan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$satuan) {
            $_SESSION['error'] = "Satuan tidak ditemukan!";
        } elseif ($satuan['status'] == 1) {
            // Jika aktif, nonaktifkan (soft delete)
            $stmt = $conn->prepare("UPDATE satuan SET status = 0 WHERE idsatuan = ?");
            if ($stmt->execute([$idsatuan]) && $stmt->rowCount() > 0) {
                $_SESSION['success'] = "Satuan berhasil dinonaktifkan!";
            } else {
                $_SESSION['error'] = "Gagal menonaktifkan satuan!";
            }
        } else {
            // Jika sudah nonaktif, hapus permanen
            $stmt = $conn->prepare("DELETE FROM satuan WHERE idsatuan = ?");
            if ($stmt->execute([$idsatuan]) && $stmt->rowCount() > 0) {
                $_SESSION['success'] = "Satuan berhasil dihapus permanen!";
            } else {
                $_SESSION['error'] = "Gagal menghapus satuan!";
            }
        }
    } else {
        $_SESSION['error'] = "ID Satuan tidak valid!";
    }
    
} catch(PDOException $e) {
    if ($e->getCode() == 23000) {
        $_SESSION['error'] = "Satuan tidak dapat dihapus karena masih digunakan!";
    } else {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header("Location: indexsatuan.php");
exit();
?>