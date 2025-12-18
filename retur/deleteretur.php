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
    $_SESSION['delete_error'] = "ID retur tidak valid!";
    header("Location: indexretur.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cek apakah data retur ada
    $stmt = $conn->prepare("SELECT idretur FROM retur WHERE idretur = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['delete_error'] = "Data retur tidak ditemukan!";
        header("Location: indexretur.php");
        exit();
    }
    
    // Mulai transaksi
    $conn->beginTransaction();
    
    try {
        // Hapus detail retur terlebih dahulu (karena foreign key)
        $stmt = $conn->prepare("DELETE FROM detail_retur WHERE idretur = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Hapus data retur
        $stmt = $conn->prepare("DELETE FROM retur WHERE idretur = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Commit transaksi
        $conn->commit();
        
        $_SESSION['delete_success'] = "Data retur #$id berhasil dihapus!";
        
    } catch(Exception $e) {
        // Rollback jika ada error
        $conn->rollBack();
        $_SESSION['delete_error'] = "Gagal menghapus data retur: " . $e->getMessage();
    }
    
} catch(PDOException $e) {
    $_SESSION['delete_error'] = "Error database: " . $e->getMessage();
}

// Redirect kembali ke index
header("Location: indexretur.php");
exit();
?>