<?php
$host = 'localhost';
$dbname = 'assetflow';
$username = 'root'; 
$password = ''; // Kosongkan jika pakai XAMPP default, 'root' jika MAMP

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi Gagal: " . $e->getMessage());
}

// Mulai sesi otomatis untuk semua halaman yang memanggil file ini
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $activeUserStmt = $conn->prepare("SELECT id_user FROM users WHERE id_user = ? AND deleted_at IS NULL");
    $activeUserStmt->execute([$_SESSION['user_id']]);

    if (!$activeUserStmt->fetch()) {
        session_unset();
        session_destroy();

        if (PHP_SAPI !== 'cli') {
            header("Location: login.php?error=account_inactive");
        }
        exit();
    }
}

?>
