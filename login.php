<?php
//1. memanggil koneksi database dan memulai session
require_once 'database/db.php';
//2. mengecek apakah user sudah login
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

//3. proses login jika user belum login dan menekan tombol login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email dan Password harus diisi.";
    } else {
        // Mencari user di database berdasarkan email
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil, simpan data user di session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['divisi'] = $user['divisi'];

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
            $error = "Terjadi kesalahan pada server: " . $e->getMessage();
        }
    } 
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login AssetFlow</title>
</head>
<body>
    <h2>Login AssetFlow</h2>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit" name="login">Masuk</button>
    </form>
</body>
</html>