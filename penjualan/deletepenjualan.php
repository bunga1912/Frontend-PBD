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

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['delete_error'] = "ID penjualan tidak valid!";
    header("Location: indexpenjualan.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cek apakah data penjualan ada
    $stmt = $conn->prepare("SELECT idpenjualan FROM penjualan WHERE idpenjualan = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['delete_error'] = "Data penjualan tidak ditemukan!";
        header("Location: indexpenjualan.php");
        exit();
    }
    
    // Mulai transaksi
    $conn->beginTransaction();
    
    try {
        // Hapus detail penjualan terlebih dahulu (karena foreign key)
        $stmt = $conn->prepare("DELETE FROM detail_penjualan WHERE penjualan_idpenjualan = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Hapus data penjualan
        $stmt = $conn->prepare("DELETE FROM penjualan WHERE idpenjualan = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Commit transaksi
        $conn->commit();
        
        $_SESSION['delete_success'] = "Data penjualan #$id berhasil dihapus!";
        
    } catch(Exception $e) {
        // Rollback jika ada error
        $conn->rollBack();
        $_SESSION['delete_error'] = "Gagal menghapus data penjualan: " . $e->getMessage();
    }
    
} catch(PDOException $e) {
    $_SESSION['delete_error'] = "Error database: " . $e->getMessage();
}

// Redirect kembali ke index
header("Location: indexpenjualan.php");
exit();
?>