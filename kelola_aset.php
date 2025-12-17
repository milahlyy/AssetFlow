<?php
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
        
        <?php if(isset($message)) echo "<p style='color:green; font-weight:bold;'>$message</p>"; ?>
        
        <h2>Tambah Aset Baru</h2>
        <form method="POST" enctype="multipart/form-data">
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
            <input type="file" name="gambar">
            
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
                        <button onclick="editAset(
                            <?= $asset['id_aset'] ?>, 
                            '<?= htmlspecialchars($asset['nama_aset'], ENT_QUOTES) ?>', 
                            '<?= $asset['kategori'] ?>', 
                            '<?= $asset['plat_nomor'] ?>', 
                            '<?= $asset['status_aset'] ?>'
                        )">Edit</button>
                        <a href="?delete=<?= $asset['id_aset'] ?>" onclick="return confirm('Yakin akan menghapus aset ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div id="editForm" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border:1px solid #000;">
            <h2 style="border:none; margin-bottom:10px;">Edit Aset</h2>
            <form method="POST" enctype="multipart/form-data" id="editFormContent" style="box-shadow:none; padding:0; margin:0;">
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
                <input type="file" name="gambar">
                
                <div style="margin-top:20px;">
                    <button type="submit" name="update_aset">Update</button>
                    <button type="button" onclick="document.getElementById('editForm').style.display='none'">Batal</button>
                </div>
            </form>
        </div>

    </div> <script>
    // Saya update sedikit fungsi JS-nya agar form terisi otomatis saat klik Edit
    function editAset(id, nama, kategori, plat, status) {
        document.getElementById('editForm').style.display = 'block';
        
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_kategori').value = kategori;
        document.getElementById('edit_plat').value = plat;
        document.getElementById('edit_status').value = status;
    }
    </script>
    
</body>
</html>