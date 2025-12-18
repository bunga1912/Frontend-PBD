<?php
session_start();

// Simpan username untuk pesan (opsional)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Hapus semua session variables
$_SESSION = array();

// Hapus session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hapus remember me cookie jika ada
if (isset($_COOKIE['user_login'])) {
    setcookie('user_login', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Set pesan logout (start session baru untuk pesan)
session_start();
$_SESSION['logout_message'] = "Anda telah berhasil logout. Sampai jumpa!";

// Redirect ke halaman login
header("Location: login.php");
exit();
?>