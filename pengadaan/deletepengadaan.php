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
    $_SESSION['delete_error'] = "ID pengadaan tidak valid!";
    header("Location: indexpengadaan.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cek apakah data pengadaan ada
    $stmt = $conn->prepare("SELECT idpengadaan FROM pengadaan WHERE idpengadaan = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['delete_error'] = "Data pengadaan tidak ditemukan!";
        header("Location: indexpengadaan.php");
        exit();
    }
    
    // Mulai transaksi
    $conn->beginTransaction();
    
    try {
        // Hapus detail pengadaan terlebih dahulu (karena foreign key)
        $stmt = $conn->prepare("DELETE FROM detail_pengadaan WHERE idpengadaan = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Hapus data pengadaan
        $stmt = $conn->prepare("DELETE FROM pengadaan WHERE idpengadaan = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Commit transaksi
        $conn->commit();
        
        $_SESSION['delete_success'] = "Data pengadaan #$id berhasil dihapus!";
        
    } catch(Exception $e) {
        // Rollback jika ada error
        $conn->rollBack();
        $_SESSION['delete_error'] = "Gagal menghapus data pengadaan: " . $e->getMessage();
    }
    
} catch(PDOException $e) {
    $_SESSION['delete_error'] = "Error database: " . $e->getMessage();
}

// Redirect kembali ke index
header("Location: indexpengadaan.php");
exit();
?>