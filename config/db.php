<?php
$host = "localhost";
$user = "root";      
$pass = "";          
$db   = "db_mitrajayasupermarket";

$conn = new mysqli($host, $user, $pass, $db);

// cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?><?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_mitrajayasupermarket');
define('DB_USER', 'root');
define('DB_PASS', '');

// Fungsi untuk koneksi database
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, 
            DB_USER, 
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch(PDOException $e) {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

// Fungsi untuk format rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}
?>