<?php
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['hrga']);

$message = '';
$error = '';
$allowed_categories = ['mobil', 'elektronik'];
$allowed_statuses = ['tersedia', 'maintenance', 'rusak'];

function upload_asset_image($field_name, &$error) {
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload gambar gagal.";
        return false;
    }

    if ($_FILES[$field_name]['size'] > 2 * 1024 * 1024) {
        $error = "Ukuran gambar maksimal 2MB.";
        return false;
    }

    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES[$field_name]['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed_mimes[$mime])) {
        $error = "Format gambar harus JPG, PNG, GIF, atau WEBP.";
        return false;
    }

    $upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img';
    if (!is_dir($upload_dir)) {
        $error = "Folder upload gambar tidak ditemukan.";
        return false;
    }

    $gambar_name = bin2hex(random_bytes(16)) . '.' . $allowed_mimes[$mime];
    $target = $upload_dir . DIRECTORY_SEPARATOR . $gambar_name;

    if (!move_uploaded_file($_FILES[$field_name]['tmp_name'], $target)) {
        $error = "Gagal menyimpan gambar.";
        return false;
    }

    return $gambar_name;
}

function delete_unused_asset_image_file($gambar, PDO $conn) {
    if (!is_string($gambar) || $gambar === '' || $gambar === 'placeholder.svg') {
        return;
    }

    $basename = basename($gambar);
    if ($basename !== $gambar) {
        return;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM assets WHERE gambar = ?");
    $stmt->execute([$basename]);
    if ((int) $stmt->fetchColumn() > 0) {
        return;
    }

    $path = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $basename;
    if (is_file($path)) {
        unlink($path);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();

    if (isset($_POST['delete_aset'])) {
        $id_aset = filter_input(INPUT_POST, 'id_aset', FILTER_VALIDATE_INT);

        if (!$id_aset) {
            $error = "Data aset tidak valid.";
        } else {
            $active_check = $conn->prepare("
                SELECT COUNT(*)
                FROM loans
                WHERE id_aset = ?
                  AND status_loan IN ('pending', 'approved', 'on_loan')
            ");
            $active_check->execute([$id_aset]);

            if ((int) $active_check->fetchColumn() > 0) {
                $error = "Aset tidak bisa dihapus karena masih memiliki peminjaman aktif.";
            } else {
                $stmt = $conn->prepare("UPDATE assets SET deleted_at = NOW() WHERE id_aset = ? AND deleted_at IS NULL");
                $stmt->execute([$id_aset]);
                $message = $stmt->rowCount() > 0 ? "Aset berhasil dihapus" : "Aset sudah dihapus sebelumnya.";
            }
        }
    }

    if (isset($_POST['tambah_aset']) || isset($_POST['update_aset'])) {
        $nama_aset = trim($_POST['nama_aset'] ?? '');
        $kategori = $_POST['kategori'] ?? '';
        $plat_nomor = trim($_POST['plat_nomor'] ?? '');
        $status_aset = $_POST['status_aset'] ?? '';

        if ($nama_aset === '' || !in_array($kategori, $allowed_categories, true) || !in_array($status_aset, $allowed_statuses, true)) {
            $error = "Input aset tidak valid.";
        } else {
            $plat_nomor = $plat_nomor !== '' ? $plat_nomor : null;
            $gambar_name = upload_asset_image('gambar', $error);

            if ($gambar_name !== false) {
                if (isset($_POST['tambah_aset'])) {
                    $stmt = $conn->prepare("INSERT INTO assets (nama_aset, kategori, plat_nomor, status_aset, gambar) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$nama_aset, $kategori, $plat_nomor, $status_aset, $gambar_name]);
                    $message = "Aset berhasil ditambahkan";
                } else {
                    $id_aset = filter_input(INPUT_POST, 'id_aset', FILTER_VALIDATE_INT);

                    if (!$id_aset) {
                        $error = "Data aset tidak valid.";
                        if ($gambar_name !== null) {
                            delete_unused_asset_image_file($gambar_name, $conn);
                        }
                    } else {
                        $old_stmt = $conn->prepare("SELECT gambar FROM assets WHERE id_aset = ? AND deleted_at IS NULL");
                        $old_stmt->execute([$id_aset]);
                        $old_gambar = $old_stmt->fetchColumn();
                    }

                    if (!$error && $old_gambar === false) {
                        $error = "Aset tidak ditemukan atau sudah dihapus.";
                        if ($gambar_name !== null) {
                            delete_unused_asset_image_file($gambar_name, $conn);
                        }
                    } elseif (!$error && $gambar_name !== null) {
                        $stmt = $conn->prepare("UPDATE assets SET nama_aset=?, kategori=?, plat_nomor=?, status_aset=?, gambar=? WHERE id_aset=? AND deleted_at IS NULL");
                        $stmt->execute([$nama_aset, $kategori, $plat_nomor, $status_aset, $gambar_name, $id_aset]);
                        if ($stmt->rowCount() > 0) {
                            delete_unused_asset_image_file($old_gambar, $conn);
                        }
                        $message = "Aset berhasil diperbarui";
                    } elseif (!$error) {
                        $stmt = $conn->prepare("UPDATE assets SET nama_aset=?, kategori=?, plat_nomor=?, status_aset=? WHERE id_aset=? AND deleted_at IS NULL");
                        $stmt->execute([$nama_aset, $kategori, $plat_nomor, $status_aset, $id_aset]);
                        $message = "Aset berhasil diperbarui";
                    }
                }
            }
        }
    }
}

$assets = $conn->query("SELECT * FROM assets WHERE deleted_at IS NULL ORDER BY id_aset DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Aset</title>
    <link rel="stylesheet" href="css/kelola_aset.css">
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

        <h1>Kelola Aset</h1>

        <?php if($message): ?>
            <p style="color:green; font-weight:bold;"><?= e($message) ?></p>
        <?php endif; ?>

        <?php if($error): ?>
            <p style="color:red; font-weight:bold;"><?= e($error) ?></p>
        <?php endif; ?>

        <h2>Tambah Aset Baru</h2>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <label>Nama Aset:</label>
            <input type="text" name="nama_aset" required>

            <label>Kategori:</label>
            <select name="kategori" required>
                <option value="mobil">Mobil</option>
                <option value="elektronik">Elektronik</option>
            </select>

            <label>Plat Nomor:</label>
            <input type="text" name="plat_nomor">

            <label>Status:</label>
            <select name="status_aset" required>
                <option value="tersedia">Tersedia</option>
                <option value="maintenance">Maintenance</option>
                <option value="rusak">Rusak</option>
            </select>

            <label>Gambar:</label>
            <input type="file" name="gambar" accept="image/jpeg,image/png,image/gif,image/webp">

            <button type="submit" name="tambah_aset">Tambah Aset</button>
        </form>

        <hr>

        <h2>Daftar Aset</h2>
        <table cellpadding="10">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Aset</th>
                    <th>Kategori</th>
                    <th>Plat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($assets as $index => $asset): ?>
                <tr>
                    <td><?= $index+1 ?></td>
                    <td>
                        <img src="<?= e(asset_image_src($asset['gambar'])) ?>" width="100" alt="<?= e($asset['nama_aset']) ?>">
                    </td>
                    <td><?= e($asset['nama_aset']) ?></td>
                    <td><?= e($asset['kategori']) ?></td>
                    <td><?= e($asset['plat_nomor'] ?: '-') ?></td>
                    <td><?= e($asset['status_aset']) ?></td>
                    <td>
                        <button type="button" onclick='editAset(<?= json_encode([
                            'id' => $asset['id_aset'],
                            'nama' => $asset['nama_aset'],
                            'kategori' => $asset['kategori'],
                            'plat' => $asset['plat_nomor'],
                            'status' => $asset['status_aset'],
                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin akan menghapus aset ini?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id_aset" value="<?= e($asset['id_aset']) ?>">
                            <button type="submit" name="delete_aset">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="editForm" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border:1px solid #000;">
            <h2 style="border:none; margin-bottom:10px;">Edit Aset</h2>
            <form method="POST" enctype="multipart/form-data" id="editFormContent" style="box-shadow:none; padding:0; margin:0;">
                <?= csrf_field() ?>
                <input type="hidden" name="id_aset" id="edit_id">

                <label>Nama Aset:</label>
                <input type="text" name="nama_aset" id="edit_nama" required>

                <label>Kategori:</label>
                <select name="kategori" id="edit_kategori" required>
                    <option value="mobil">Mobil</option>
                    <option value="elektronik">Elektronik</option>
                </select>

                <label>Plat Nomor:</label>
                <input type="text" name="plat_nomor" id="edit_plat">

                <label>Status:</label>
                <select name="status_aset" id="edit_status" required>
                    <option value="tersedia">Tersedia</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="rusak">Rusak</option>
                </select>

                <label>Gambar Baru:</label>
                <input type="file" name="gambar" accept="image/jpeg,image/png,image/gif,image/webp">

                <div style="margin-top:20px;">
                    <button type="submit" name="update_aset">Update</button>
                    <button type="button" onclick="document.getElementById('editForm').style.display='none'">Batal</button>
                </div>
            </form>
        </div>

    </div>
    <script>
    function editAset(asset) {
        document.getElementById('editForm').style.display = 'block';
        document.getElementById('edit_id').value = asset.id;
        document.getElementById('edit_nama').value = asset.nama || '';
        document.getElementById('edit_kategori').value = asset.kategori;
        document.getElementById('edit_plat').value = asset.plat || '';
        document.getElementById('edit_status').value = asset.status;
    }
    </script>

</body>
</html>
