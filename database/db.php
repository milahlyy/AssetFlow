<?php
function env_value($key, $default = null) {
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

$databaseUrl = env_value('DATABASE_URL') ?: env_value('MYSQL_URL');

$host = env_value('DB_HOST', env_value('MYSQLHOST', 'localhost'));
$port = env_value('DB_PORT', env_value('MYSQLPORT', '3306'));
$dbname = env_value('DB_NAME', env_value('MYSQLDATABASE', 'assetflow'));
$username = env_value('DB_USER', env_value('MYSQLUSER', 'root'));
$password = env_value('DB_PASSWORD', env_value('MYSQLPASSWORD', '')); // Kosongkan jika pakai XAMPP default.

if ($databaseUrl) {
    $parts = parse_url($databaseUrl);

    if (is_array($parts)) {
        $host = $parts['host'] ?? $host;
        $port = isset($parts['port']) ? (string) $parts['port'] : $port;
        $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : $dbname;
        $username = isset($parts['user']) ? rawurldecode($parts['user']) : $username;
        $password = isset($parts['pass']) ? rawurldecode($parts['pass']) : $password;
    }
}

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
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
