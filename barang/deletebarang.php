<?php
session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login/login.php");
    exit();
}

// Koneksi database
$host = 'localhost';
$dbname = 'db_mitrajayasupermarket';
$db_username = 'root';
$db_password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cek apakah ada ID di URL
    if (!isset($_GET['id'])) {
        header("Location: indexbarang.php");
        exit();
    }
    
    $id = $_GET['id'];
    
    // Cek apakah barang ada
    $stmt = $conn->prepare("SELECT nama FROM barang WHERE idbarang = ?");
    $stmt->execute([$id]);
    $barang = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$barang) {
        $_SESSION['delete_error'] = "Barang tidak ditemukan!";
        header("Location: indexbarang.php");
        exit();
    }
    
    // Cek apakah barang sudah digunakan di tabel lain
    $stmt = $conn->prepare("SELECT COUNT(*) FROM detail_pengadaan WHERE idbarang = ?");
    $stmt->execute([$id]);
    $count_pengadaan = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM detail_penerimaan WHERE barang_idbarang = ?");
    $stmt->execute([$id]);
    $count_penerimaan = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM detail_penjualan WHERE idbarang = ?");
    $stmt->execute([$id]);
    $count_penjualan = $stmt->fetchColumn();
    
    // Jika barang sudah digunakan, tidak bisa dihapus
    if ($count_pengadaan > 0 || $count_penerimaan > 0 || $count_penjualan > 0) {
        $_SESSION['delete_error'] = "Barang tidak dapat dihapus karena sudah digunakan dalam transaksi!";
        header("Location: indexbarang.php");
        exit();
    }
    
    // Hapus barang
    $stmt = $conn->prepare("DELETE FROM barang WHERE idbarang = ?");
    $stmt->execute([$id]);
    
    $_SESSION['delete_success'] = "Barang '" . $barang['nama'] . "' berhasil dihapus!";
    header("Location: indexbarang.php");
    exit();
    
} catch(PDOException $e) {
    $_SESSION['delete_error'] = "Error: " . $e->getMessage();
    header("Location: indexbarang.php");
    exit();
}
?>