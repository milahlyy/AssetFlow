<?php
// Memastikan user sudah login sebelum melihat konten halaman
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek ada data user_id di session
if(!isset($_SESSION['user_id'])) {
    // Kalau tidak ada, berarti belum login. Tendang ke login.php
    header("Location: login.php");
    exit();
}

// Membuat session timeout
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > 1800)) {
    // Jika aktivitas terakhir lebih dari 30 menit, logout user  
    session_destroy();   
    header("Location: login.php?timeout=1");
    exit();
}
// Update waktu aktivitas terakhir
$_SESSION['last_activity'] = time();

// masing-masing halaman hanya bisa diakses oleh masing-masing role
function checkrole($allowed_roles) {
    // Cek apakah role ada di session
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        // Jika role user tidak diizinkan, arahkan ke halaman yang sesuai
        header("Location: login.php?error=access_denied");
        exit();
    }
}   