<?php
// Tugas: Memastikan user sudah login sebelum melihat konten halaman
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Cek apakah ada data user_id di session?
if(!isset($_SESSION['user_id'])) {
    // Kalau tidak ada, berarti belum login. Tendang ke login.php
    header("Location: login.php");
    exit();
}
?>