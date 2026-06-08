<?php
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['hrga']);

$message = '';
$error = '';
$allowed_roles = ['hrga', 'pegawai', 'satpam', 'supir'];

// HANDLE TAMBAH & UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();

    if (isset($_POST['delete_user'])) {
        $id_user = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT);

        if (!$id_user) {
            $error = "Data user tidak valid.";
        } elseif ($id_user == $_SESSION['user_id']) {
            $error = "User yang sedang login tidak bisa dihapus.";
        } else {
            $active_check = $conn->prepare("
                SELECT COUNT(*)
                FROM loans
                WHERE (id_user = ? OR driver_id = ?)
                  AND status_loan IN ('pending', 'approved', 'on_loan')
            ");
            $active_check->execute([$id_user, $id_user]);

            if ((int) $active_check->fetchColumn() > 0) {
                $error = "User tidak bisa dihapus karena masih memiliki peminjaman atau tugas aktif.";
            } else {
                $stmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id_user=? AND deleted_at IS NULL");
                $stmt->execute([$id_user]);
                $message = $stmt->rowCount() > 0 ? "User berhasil dihapus" : "User sudah dihapus sebelumnya.";
            }
        }
    }

    // TAMBAH USER
    if (isset($_POST['tambah_user'])) {
        $nama     = trim($_POST['nama'] ?? '');
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password_plain = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? '';
        $divisi   = trim($_POST['divisi'] ?? '');

        if ($nama === '' || !$email || $password_plain === '' || !in_array($role, $allowed_roles, true)) {
            $error = "Input user tidak valid.";
        } else {
            $password = password_hash($password_plain, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO users (nama, email, password, role, divisi)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nama, $email, $password, $role, $divisi]);

            $message = "User berhasil ditambahkan";
        }
    }

    // UPDATE USER
    if (isset($_POST['update_user'])) {
        $id_user = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT);
        $nama    = trim($_POST['nama'] ?? '');
        $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $role    = $_POST['role'] ?? '';
        $divisi  = trim($_POST['divisi'] ?? '');

        if (!$id_user || $nama === '' || !$email || !in_array($role, $allowed_roles, true)) {
            $error = "Input user tidak valid.";
        } else {
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "UPDATE users
                     SET nama=?, email=?, password=?, role=?, divisi=?
                     WHERE id_user=? AND deleted_at IS NULL"
                );
                $stmt->execute([$nama, $email, $password, $role, $divisi, $id_user]);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE users
                     SET nama=?, email=?, role=?, divisi=?
                     WHERE id_user=? AND deleted_at IS NULL"
                );
                $stmt->execute([$nama, $email, $role, $divisi, $id_user]);
            }

            $message = "User berhasil diperbarui";
        }
    }
}

// AMBIL DATA USER
$users = $conn->query("SELECT * FROM users WHERE deleted_at IS NULL ORDER BY id_user DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User</title>
    <link rel="stylesheet" href="css/kelola_user.css">
</head>
<body>


<div class="sidebar">
    <h2>AssetFlow</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="kelola_aset.php">Kelola Aset</a>
    <a href="persetujuan.php">Persetujuan Peminjaman</a>
    <a href="laporan.php">Laporan</a>
    <a href="kelola_user.php">Kelola User</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main-content">
    <h1>Kelola User</h1>

    <?php if($message): ?>
        <p style="color:green; font-weight:bold;"><?= e($message) ?></p>
    <?php endif; ?>

    <?php if($error): ?>
        <p style="color:red; font-weight:bold;"><?= e($error) ?></p>
    <?php endif; ?>

    <h2>Tambah User</h2>
    <form method="POST">
        <?= csrf_field() ?>
        <label>Nama</label>
        <input type="text" name="nama" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role" required>
            <option value="hrga">HRGA</option>
            <option value="pegawai">Pegawai</option>
            <option value="satpam">Satpam</option>
            <option value="supir">Supir</option>
        </select>

        <label>Divisi</label>
        <input type="text" name="divisi">

        <button type="submit" name="tambah_user">Tambah User</button>
    </form>

    <hr>

    <h2>Daftar User</h2>
    <table>
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Divisi</th>
            <th>Aksi</th>
        </tr>

        <?php foreach($users as $u): ?>
        <tr>
            <td><?= e($u['nama']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['role']) ?></td>
            <td><?= e($u['divisi']) ?></td>
            <td>
                <button type="button" onclick='editUser(<?= json_encode([
                    'id' => $u['id_user'],
                    'nama' => $u['nama'],
                    'email' => $u['email'],
                    'role' => $u['role'],
                    'divisi' => $u['divisi'],
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>

                <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus user ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_user" value="<?= e($u['id_user']) ?>">
                    <button type="submit" name="delete_user">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div id="editUserForm" style="
    display:none;
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    background:white;
    padding:25px;
    border-radius:12px;
    z-index:2000;
">
    <h2>Edit User</h2>

    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id_user" id="edit_id">

        <label>Nama</label>
        <input type="text" name="nama" id="edit_nama" required>

        <label>Email</label>
        <input type="email" name="email" id="edit_email" required>

        <label>Password (kosongkan jika tidak diubah)</label>
        <input type="password" name="password">

        <label>Role</label>
        <select name="role" id="edit_role">
            <option value="hrga">HRGA</option>
            <option value="pegawai">Pegawai</option>
            <option value="satpam">Satpam</option>
            <option value="supir">Supir</option>
        </select>

        <label>Divisi</label>
        <input type="text" name="divisi" id="edit_divisi">

        <button type="submit" name="update_user">Update</button>
        <button type="button"
            onclick="document.getElementById('editUserForm').style.display='none'">
            Batal
        </button>
    </form>
</div>

<script>
function editUser(user) {
    document.getElementById('editUserForm').style.display = 'block';
    edit_id.value     = user.id;
    edit_nama.value   = user.nama || '';
    edit_email.value  = user.email || '';
    edit_role.value   = user.role;
    edit_divisi.value = user.divisi || '';
}
</script>

</body>
</html>
