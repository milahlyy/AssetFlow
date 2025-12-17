<?php
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['hrga']);

// HANDLE TAMBAH & UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // TAMBAH USER
    if (isset($_POST['tambah_user'])) {
        $nama     = $_POST['nama'];
        $email    = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role     = $_POST['role'];
        $divisi   = $_POST['divisi'];

        $stmt = $conn->prepare(
            "INSERT INTO users (nama, email, password, role, divisi)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nama, $email, $password, $role, $divisi]);

        $message = "User berhasil ditambahkan";
    }

    // UPDATE USER
    if (isset($_POST['update_user'])) {
        $id_user = $_POST['id_user'];
        $nama    = $_POST['nama'];
        $email   = $_POST['email'];
        $role    = $_POST['role'];
        $divisi  = $_POST['divisi'];

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "UPDATE users
                 SET nama=?, email=?, password=?, role=?, divisi=?
                 WHERE id_user=?"
            );
            $stmt->execute([$nama, $email, $password, $role, $divisi, $id_user]);
        } else {
            $stmt = $conn->prepare(
                "UPDATE users
                 SET nama=?, email=?, role=?, divisi=?
                 WHERE id_user=?"
            );
            $stmt->execute([$nama, $email, $role, $divisi, $id_user]);
        }

        $message = "User berhasil diperbarui";
    }
}

//HANDLE HAPUS
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id_user=?");
    $stmt->execute([$_GET['delete']]);
    $message = "User berhasil dihapus";
}

// AMBIL DATA USER
$users = $conn->query("SELECT * FROM users ORDER BY id_user DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User</title>
    <link rel="stylesheet" href="css/kelola_user.css">
</head>
<body>

// Sidebar
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

    <?php if(isset($message)): ?>
        <p style="color:green; font-weight:bold;"><?= $message ?></p>
    <?php endif; ?>

    // FORM TAMBAH USER 
    <h2>Tambah User</h2>
    <form method="POST">
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

   // DAFTAR USER
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
            <td><?= htmlspecialchars($u['nama']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['role'] ?></td>
            <td><?= $u['divisi'] ?></td>
            <td>
                <button onclick="editUser(
                    <?= $u['id_user'] ?>,
                    '<?= htmlspecialchars($u['nama'], ENT_QUOTES) ?>',
                    '<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>',
                    '<?= $u['role'] ?>',
                    '<?= htmlspecialchars($u['divisi'], ENT_QUOTES) ?>'
                )">Edit</button>

                <a href="?delete=<?= $u['id_user'] ?>"
                   onclick="return confirm('Yakin hapus user ini?')">
                   Hapus
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

// POPUP EDIT USER
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
function editUser(id, nama, email, role, divisi) {
    document.getElementById('editUserForm').style.display = 'block';
    edit_id.value     = id;
    edit_nama.value   = nama;
    edit_email.value  = email;
    edit_role.value   = role;
    edit_divisi.value = divisi;
}
</script>

</body>
</html>
