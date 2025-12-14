<?php
// Menghancurkan session dan mengarahkan ke halaman login

// Memulai session atau melanjutkan session yang sudah ada
session_start();

// Mengkosongkan semua data session
$_SESSION = array();

// Menghancurkan session sepenuhnya
session_destroy();

// Mengarahkan pengguna ke halaman login setelah logout
header("Location: login.php");
exit();
?>