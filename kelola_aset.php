<?php
session_start();
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['hrga']);

// Handle operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_aset'])) {
        $nama_aset = $_POST['nama_aset'];
        $kategori = $_POST['kategori'];
        $plat_nomor = $_POST['plat_nomor'] ?? null;
        $status_aset = $_POST['status_aset'];
        
        // Upload gambar
        $gambar_name = null;
        if ($_FILES['gambar']['error'] == 0) {
            $gambar_name = uniqid() . '_' . $_FILES['gambar']['name'];
            move_uploaded_file($_FILES['gambar']['tmp_name'], 'assets/img/' . $gambar_name);
        }
        
        $stmt = $conn->prepare("INSERT INTO assets (nama_aset, kategori, plat_nomor, status_aset, gambar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama_aset, $kategori, $plat_nomor, $status_aset, $gambar_name]);
        
        $message = "Aset berhasil ditambahkan";
    }
    
    if (isset($_POST['update_aset'])) {
        $id_aset = $_POST['id_aset'];
        $nama_aset = $_POST['nama_aset'];
        $kategori = $_POST['kategori'];
        $plat_nomor = $_POST['plat_nomor'] ?? null;
        $status_aset = $_POST['status_aset'];
        
        // Jika ada gambar baru
        if ($_FILES['gambar']['error'] == 0) {
            $gambar_name = uniqid() . '_' . $_FILES['gambar']['name'];
            move_uploaded_file($_FILES['gambar']['tmp_name'], 'assets/img/' . $gambar_name);
            $stmt = $conn->prepare("UPDATE assets SET nama_aset=?, kategori=?, plat_nomor=?, status_aset=?, gambar=? WHERE id_aset=?");
            $stmt->execute([$nama_aset, $kategori, $plat_nomor, $status_aset, $gambar_name, $id_aset]);
        } else {
            $stmt = $conn->prepare("UPDATE assets SET nama_aset=?, kategori=?, plat_nomor=?, status_aset=? WHERE id_aset=?");
            $stmt->execute([$nama_aset, $kategori, $plat_nomor, $status_aset, $id_aset]);
        }
        
        $message = "Aset berhasil diperbarui";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM assets WHERE id_aset = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "Aset berhasil dihapus";
}

// Get all assets
$assets = $conn->query("SELECT * FROM assets ORDER BY id_aset DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Aset</title>
</head>
<body>
    <h1>Kelola Aset</h1>
    
    <?php if(isset($message)) echo "<p>$message</p>"; ?>
    
    <!-- Form Tambah Aset -->
    <h2>Tambah Aset Baru</h2>
    <form method="POST" enctype="multipart/form-data">
        Nama Aset: <input type="text" name="nama_aset" required><br>
        Kategori: 
        <select name="kategori" required>
            <option value="mobil">Mobil</option>
            <option value="elektronik">Elektronik</option>
        </select><br>
        Plat Nomor: <input type="text" name="plat_nomor"><br>
        Status: 
        <select name="status_aset" required>
            <option value="tersedia">Tersedia</option>
            <option value="maintenance">Maintenance</option>
            <option value="rusak">Rusak</option>
        </select><br>
        Gambar: <input type="file" name="gambar"><br>
        <button type="submit" name="tambah_aset">Tambah Aset</button>
    </form>
    
    <hr>
    
    <!-- Daftar Aset -->
    <h2>Daftar Aset</h2>
    <table border="1" cellpadding="10">
        <tr>
            <th>No</th>
            <th>Gambar</th>
            <th>Nama Aset</th>
            <th>Kategori</th>
            <th>Plat</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        
        <?php foreach($assets as $index => $asset): ?>
        <tr>
            <td><?= $index+1 ?></td>
            <td>
                <?php if($asset['gambar']): ?>
                    <img src="assets/img/<?= $asset['gambar'] ?>" width="100">
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($asset['nama_aset']) ?></td>
            <td><?= $asset['kategori'] ?></td>
            <td><?= $asset['plat_nomor'] ?: '-' ?></td>
            <td><?= $asset['status_aset'] ?></td>
            <td>
                <button onclick="editAset(<?= $asset['id_aset'] ?>)">Edit</button>
                <a href="?delete=<?= $asset['id_aset'] ?>" onclick="return confirm('Yakin?')">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <!-- Form Edit (Modal) -->
    <div id="editForm" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border:1px solid #000;">
        <h2>Edit Aset</h2>
        <form method="POST" enctype="multipart/form-data" id="editFormContent">
            <input type="hidden" name="id_aset" id="edit_id">
            Nama Aset: <input type="text" name="nama_aset" id="edit_nama" required><br>
            Kategori: 
            <select name="kategori" id="edit_kategori" required>
                <option value="mobil">Mobil</option>
                <option value="elektronik">Elektronik</option>
            </select><br>
            Plat Nomor: <input type="text" name="plat_nomor" id="edit_plat"><br>
            Status: 
            <select name="status_aset" id="edit_status" required>
                <option value="tersedia">Tersedia</option>
                <option value="maintenance">Maintenance</option>
                <option value="rusak">Rusak</option>
            </select><br>
            Gambar Baru: <input type="file" name="gambar"><br>
            <button type="submit" name="update_aset">Update</button>
            <button type="button" onclick="document.getElementById('editForm').style.display='none'">Batal</button>
        </form>
    </div>
    
    <script>
    function editAset(id) {
        // Fetch data via AJAX atau ambil dari data yang sudah ada
        // Ini contoh sederhana, sebaiknya pakai AJAX
        document.getElementById('editForm').style.display = 'block';
        // Isi form dengan data (bisa dari AJAX)
    }
    </script>
    
</body>
</html>