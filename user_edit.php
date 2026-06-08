<?php
require_once 'auth_check.php';
require_once 'database/db.php';
checkrole(['hrga']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_user.php");
    exit;
}

verify_csrf();

$aksi = $_POST['aksi'] ?? '';
$allowed_roles = ['hrga', 'pegawai', 'satpam', 'supir'];

/* TAMBAH USER */
if ($aksi === 'tambah') {

    $nama     = trim($_POST['nama'] ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password_plain = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';
    $divisi   = trim($_POST['divisi'] ?? '');

    if ($nama === '' || !$email || $password_plain === '' || !in_array($role, $allowed_roles, true)) {
        header("Location: kelola_user.php?error=input");
        exit;
    }

    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (nama, email, password, role, divisi)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nama, $email, $password, $role, $divisi]);

    header("Location: kelola_user.php?success=tambah");
    exit;
}

/*  EDIT USER */
if ($aksi === 'edit') {

    $id_user = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT);
    $nama    = trim($_POST['nama'] ?? '');
    $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $role    = $_POST['role'] ?? '';
    $divisi  = trim($_POST['divisi'] ?? '');

    if (!$id_user || $nama === '' || !$email || !in_array($role, $allowed_roles, true)) {
        header("Location: kelola_user.php?error=input");
        exit;
    }

    // Kalau password diisi → update
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE users
            SET nama=?, email=?, password=?, role=?, divisi=?
            WHERE id_user=?
        ");
        $stmt->execute([$nama, $email, $password, $role, $divisi, $id_user]);
    } 
    // Kalau password kosong → jangan diubah
    else {
        $stmt = $conn->prepare("
            UPDATE users
            SET nama=?, email=?, role=?, divisi=?
            WHERE id_user=?
        ");
        $stmt->execute([$nama, $email, $role, $divisi, $id_user]);
    }

    header("Location: kelola_user.php?success=edit");
    exit;
}

/*  DEFAULT  */
header("Location: kelola_user.php");
exit;
