<?php
session_start();
require_once 'database/db.php';
require_once 'auth_check.php';

// Cek role HRGA saja yang boleh akses
checkrole(['hrga']);

$message = '';
$message_type = '';

// Handle Create/Update Asset
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_aset']) || isset($_POST['update_aset'])) {
        $id_aset = isset($_POST['id_aset']) ? $_POST['id_aset'] : null;
        $nama_aset = htmlspecialchars($_POST['nama_aset']);
        $kategori = htmlspecialchars($_POST['kategori']);
        $plat_nomor = htmlspecialchars($_POST['plat_nomor']);
        $status_aset = htmlspecialchars($_POST['status_aset']);
        
        // Handle file upload
        $gambar_name = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (in_array($_FILES['gambar']['type'], $allowed_types) && 
                $_FILES['gambar']['size'] <= $max_size) {
                
                $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
                $gambar_name = uniqid('asset_', true) . '.' . $file_extension;
                $upload_path = 'assets/img/' . $gambar_name;
                
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                    // File uploaded successfully
                } else {
                    $message = "Gagal mengupload gambar.";
                    $message_type = 'error';
                }
            }
        }
        
        try {
            if (isset($_POST['tambah_aset'])) {
                // Create new asset
                $stmt = $conn->prepare("INSERT INTO assets (nama_aset, kategori, plat_nomor, status_aset, gambar) 
                                       VALUES (:nama_aset, :kategori, :plat_nomor, :status_aset, :gambar)");
                $stmt->bindParam(':gambar', $gambar_name);
                $message = "Aset berhasil ditambahkan!";
                $message_type = 'success';
            } else {
                // Update existing asset
                if ($gambar_name) {
                    // Delete old photo if exists
                    $old_photo = $conn->query("SELECT gambar FROM assets WHERE id_aset = $id_aset")->fetchColumn();
                    if ($old_photo && file_exists('assets/img/' . $old_photo)) {
                        unlink('assets/img/' . $old_photo);
                    }
                    $stmt = $conn->prepare("UPDATE assets SET nama_aset = :nama_aset, kategori = :kategori, 
                                           plat_nomor = :plat_nomor, status_aset = :status_aset, 
                                           gambar = :gambar WHERE id_aset = :id");
                    $stmt->bindParam(':gambar', $gambar_name);
                } else {
                    $stmt = $conn->prepare("UPDATE assets SET nama_aset = :nama_aset, kategori = :kategori, 
                                           plat_nomor = :plat_nomor, status_aset = :status_aset 
                                           WHERE id_aset = :id");
                }
                $stmt->bindParam(':id', $id_aset);
                $message = "Aset berhasil diperbarui!";
                $message_type = 'success';
            }
            
            $stmt->bindParam(':nama_aset', $nama_aset);
            $stmt->bindParam(':kategori', $kategori);
            $stmt->bindParam(':plat_nomor', $plat_nomor);
            $stmt->bindParam(':status_aset', $status_aset);
            $stmt->execute();
            
        } catch (PDOException $e) {
            $message = "Terjadi kesalahan: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Handle Delete Asset
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Delete photo file if exists
        $photo = $conn->query("SELECT gambar FROM assets WHERE id_aset = $id")->fetchColumn();
        if ($photo && file_exists('assets/img/' . $photo)) {
            unlink('assets/img/' . $photo);
        }
        
        $stmt = $conn->prepare("DELETE FROM assets WHERE id_aset = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $message = "Aset berhasil dihapus!";
        $message_type = 'success';
        header("Location: kelola_aset.php?message=" . urlencode($message) . "&type=" . $message_type);
        exit();
    } catch (PDOException $e) {
        $message = "Gagal menghapus aset: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Get all assets
try {
    $stmt = $conn->query("SELECT * FROM assets ORDER BY id_aset DESC");
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $assets = [];
    $message = "Gagal mengambil data aset: " . $e->getMessage();
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Aset - HRGA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #0A192F;
            --secondary-cyan: #64FFDA;
            --accent-orange: #FF8C00;
            --neutral-light: #F8F9FA;
            --neutral-white: #FFFFFF;
        }
        
        body {
            background-color: var(--neutral-light);
        }
        
        .asset-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #ddd;
        }
        
        .btn-action {
            margin: 0 2px;
        }
        
        .modal-img {
            max-width: 100%;
            max-height: 400px;
        }
        
        .navbar {
            background-color: var(--primary-navy) !important;
        }
        
        .sidebar {
            background-color: var(--primary-navy);
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: var(--secondary-cyan);
            background-color: rgba(100, 255, 218, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-navy);
            border-color: var(--primary-navy);
        }
        
        .btn-primary:hover {
            background-color: #0c2147;
            border-color: #0c2147;
        }
        
        .btn-success {
            background-color: var(--secondary-cyan);
            border-color: var(--secondary-cyan);
            color: var(--primary-navy);
        }
        
        .btn-success:hover {
            background-color: #52d4b9;
            border-color: #52d4b9;
        }
        
        .btn-warning {
            background-color: var(--accent-orange);
            border-color: var(--accent-orange);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e00;
            border-color: #e67e00;
        }
        
        .badge-tersedia {
            background-color: #28a745;
        }
        
        .badge-maintenance {
            background-color: #ffc107;
            color: #000;
        }
        
        .badge-rusak {
            background-color: #dc3545;
        }
        
        .table th {
            background-color: var(--primary-navy);
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'components/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-boxes"></i> Manajemen Aset</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahAsetModal">
                        <i class="fas fa-plus"></i> Tambah Aset
                    </button>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Gambar</th>
                                <th>Nama Aset</th>
                                <th>Kategori</th>
                                <th>Plat Nomor</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assets as $index => $asset): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <?php if ($asset['gambar']): ?>
                                        <img src="assets/img/<?php echo htmlspecialchars($asset['gambar']); ?>" 
                                             class="asset-photo" 
                                             data-bs-toggle="modal" 
                                             data-bs-target="#viewPhotoModal"
                                             data-photo="assets/img/<?php echo htmlspecialchars($asset['gambar']); ?>"
                                             style="cursor: pointer;"
                                             title="Klik untuk melihat">
                                    <?php else: ?>
                                        <div class="asset-photo bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($asset['nama_aset']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $asset['kategori'] == 'mobil' ? 'primary' : 'info'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($asset['kategori'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $asset['plat_nomor'] ? htmlspecialchars($asset['plat_nomor']) : '<span class="text-muted">-</span>'; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $asset['status_aset']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($asset['status_aset'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-action" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editAsetModal"
                                            data-id="<?php echo $asset['id_aset']; ?>"
                                            data-nama="<?php echo htmlspecialchars($asset['nama_aset']); ?>"
                                            data-kategori="<?php echo htmlspecialchars($asset['kategori']); ?>"
                                            data-plat="<?php echo htmlspecialchars($asset['plat_nomor']); ?>"
                                            data-status="<?php echo htmlspecialchars($asset['status_aset']); ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="?delete=<?php echo $asset['id_aset']; ?>" 
                                       class="btn btn-sm btn-danger btn-action"
                                       onclick="return confirm('Yakin ingin menghapus aset ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal Tambah Aset -->
    <div class="modal fade" id="tambahAsetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Aset Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Aset *</label>
                            <input type="text" class="form-control" name="nama_aset" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori *</label>
                            <select class="form-select" name="kategori" required onchange="togglePlatNomor(this)">
                                <option value="">Pilih Kategori</option>
                                <option value="mobil">Mobil</option>
                                <option value="elektronik">Elektronik</option>
                            </select>
                        </div>
                        <div class="mb-3" id="platNomorGroup" style="display: none;">
                            <label class="form-label">Plat Nomor *</label>
                            <input type="text" class="form-control" name="plat_nomor" placeholder="Contoh: B 1234 CD">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status Aset *</label>
                            <select class="form-select" name="status_aset" required>
                                <option value="tersedia">Tersedia</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="rusak">Rusak</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Aset</label>
                            <input type="file" class="form-control" name="gambar" accept="image/*">
                            <small class="text-muted">Max 2MB (JPG, PNG, GIF)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_aset" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit Aset -->
    <div class="modal fade" id="editAsetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Aset</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_aset" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nama Aset *</label>
                            <input type="text" class="form-control" name="nama_aset" id="edit_nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori *</label>
                            <select class="form-select" name="kategori" id="edit_kategori" required onchange="togglePlatNomorEdit(this)">
                                <option value="">Pilih Kategori</option>
                                <option value="mobil">Mobil</option>
                                <option value="elektronik">Elektronik</option>
                            </select>
                        </div>
                        <div class="mb-3" id="platNomorGroupEdit">
                            <label class="form-label">Plat Nomor</label>
                            <input type="text" class="form-control" name="plat_nomor" id="edit_plat" placeholder="Contoh: B 1234 CD">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status Aset *</label>
                            <select class="form-select" name="status_aset" id="edit_status" required>
                                <option value="tersedia">Tersedia</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="rusak">Rusak</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Aset Baru (Opsional)</label>
                            <input type="file" class="form-control" name="gambar" accept="image/*">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_aset" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal View Photo -->
    <div class="modal fade" id="viewPhotoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-image"></i> Gambar Aset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalPhoto" src="" class="modal-img img-fluid" alt="Asset Photo">
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit modal data
        var editModal = document.getElementById('editAsetModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var modal = this;
            
            modal.querySelector('#edit_id').value = button.getAttribute('data-id');
            modal.querySelector('#edit_nama').value = button.getAttribute('data-nama');
            modal.querySelector('#edit_kategori').value = button.getAttribute('data-kategori');
            modal.querySelector('#edit_plat').value = button.getAttribute('data-plat') || '';
            modal.querySelector('#edit_status').value = button.getAttribute('data-status');
            
            // Toggle plat nomor field based on kategori
            togglePlatNomorEdit(document.getElementById('edit_kategori'));
        });
        
        // Handle view photo modal
        var viewPhotoModal = document.getElementById('viewPhotoModal');
        viewPhotoModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var modal = this;
            modal.querySelector('#modalPhoto').src = button.getAttribute('data-photo');
        });
        
        // Toggle plat nomor field for new asset
        function togglePlatNomor(select) {
            var platNomorGroup = document.getElementById('platNomorGroup');
            if (select.value === 'mobil') {
                platNomorGroup.style.display = 'block';
                platNomorGroup.querySelector('input').required = true;
            } else {
                platNomorGroup.style.display = 'none';
                platNomorGroup.querySelector('input').required = false;
            }
        }
        
        // Toggle plat nomor field for edit asset
        function togglePlatNomorEdit(select) {
            var platNomorGroup = document.getElementById('platNomorGroupEdit');
            if (select.value === 'mobil') {
                platNomorGroup.style.display = 'block';
            } else {
                platNomorGroup.style.display = 'none';
            }
        }
    </script>
</body>
</html>