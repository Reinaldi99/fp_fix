<?php
session_start(); // Memulai sesi

// Mencegah caching agar browser tidak menyimpan data halaman
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // Mencegah penyimpanan cache
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: 0"); // Waktu kedaluwarsa di masa lalu

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true) {
    // Jika belum login, tampilkan pesan dan redirect ke halaman login
    echo "You must login";
    header("Location: index.php"); // Redirect ke halaman login
    exit();
}

// Jika pengguna sudah login, tampilkan konten halaman
echo "Welcome, " . $_SESSION['username'];
?>
