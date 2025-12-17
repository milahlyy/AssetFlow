<?php
// memanggil koneksi database dan memulai session
require_once 'database/db.php';
// mengecek apakah user sudah login
if (isset($_SESSION['user_id'])) {
    // Kalau sudah login, arahkan ke dashboard masing masing role
    if ($_SESSION['role'] === 'hrga'){
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['role'] === 'pegawai'){
        header("Location: index.php");
    } elseif ($_SESSION['role'] === 'supir' || $_SESSION['role'] === 'satpam'){
        header("Location: dashboard_operasional.php");
    }
    exit();
}

$error = '';

// proses login jika user belum login dan menekan tombol login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (empty($email) || empty($password)) {
        $error = "Email dan Password harus diisi.";
    } else {
        // Mencari user di database berdasarkan email
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID untuk mencegah session fixation
                session_regenerate_id(true);
                
                // Login berhasil, simpan data user di session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['divisi'] = $user['divisi'];
                $_SESSION['last_activity'] = time(); // Set waktu aktivitas untuk timeout

                // Arahkan ke dashboard sesuai role
                if ($user['role'] === 'hrga'){
                    header("Location: admin_dashboard.php");
                } elseif ($user['role'] === 'pegawai'){
                    header("Location: index.php");
                } elseif ($user['role'] === 'supir' || $user['role'] === 'satpam'){
                    header("Location: dashboard_operasional.php");
                }
                exit();
            } else {
                $error = "Email atau Password salah.";
            }
        } catch (PDOException $e) {
            // Log error ke file log (jangan tampilkan ke user)
            error_log("Login error: " . $e->getMessage());
            $error = "Email atau Password salah.";
        }
    } 
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login AssetFlow</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="left-panel">
    <h1>
        Asset<br>
        Flow
    </h1>
</div>

<div class="right-panel">
    <div class="login-box">
        <h2>Selamat Datang</h2>
        <p>Silahkan masuk terlebih dahulu</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Kata Sandi</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</div>
</body>
</html>
