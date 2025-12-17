<?php
require_once 'auth_check.php';
require_once 'database/db.php';
checkrole(['hrga']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_user.php");
    exit;
}

$aksi = $_POST['aksi'] ?? '';

/* =========================
   TAMBAH USER
========================= */
if ($aksi === 'tambah') {

    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $divisi   = $_POST['divisi'];

    $stmt = $conn->prepare("
        INSERT INTO users (nama, email, password, role, divisi)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nama, $email, $password, $role, $divisi]);

    header("Location: kelola_user.php?success=tambah");
    exit;
}

/* =========================
   EDIT USER
========================= */
if ($aksi === 'edit') {

    $id_user = $_POST['id_user'];
    $nama    = $_POST['nama'];
    $email   = $_POST['email'];
    $role    = $_POST['role'];
    $divisi  = $_POST['divisi'];

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

/* =========================
   DEFAULT
========================= */
header("Location: kelola_user.php");
exit;
