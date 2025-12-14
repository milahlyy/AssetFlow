<?php
session_start();
require_once 'database/db.php';
require_once 'auth_check.php';

// Cek role HRGA saja yang boleh akses
checkrole(['hrga']);

$message = '';
$message_type = '';

// Handle Approve/Reject
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_loan = $_POST['id_loan'];
    $action = $_POST['action'];
    
    try {
        if ($action == 'approve') {
            $driver_id = $_POST['driver_id'] ?: null;
            $status_loan = 'approved';
            
            $stmt = $conn->prepare("UPDATE loans SET status_loan = :status, driver_id = :driver WHERE id_loan = :id");
            $stmt->bindParam(':driver', $driver_id);
            $message = "Peminjaman disetujui!";
        } else {
            $alasan_penolakan = htmlspecialchars($_POST['alasan_penolakan']);
            $status_loan = 'rejected';
            
            $stmt = $conn->prepare("UPDATE loans SET status_loan = :status, alasan_penolakan = :alasan WHERE id_loan = :id");
            $stmt->bindParam(':alasan', $alasan_penolakan);
            $message = "Peminjaman ditolak!";
        }
        
        $stmt->bindParam(':status', $status_loan);
        $stmt->bindParam(':id', $id_loan);
        $stmt->execute();
        
        $message_type = 'success';
        
    } catch (PDOException $e) {
        $message = "Terjadi kesalahan: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Get pending requests
try {
    $query = "SELECT l.*, u.nama as nama_pemohon, u.divisi, a.nama_aset, a.kategori 
              FROM loans l 
              JOIN users u ON l.id_user = u.id_user 
              JOIN assets a ON l.id_aset = a.id_aset 
              WHERE l.status_loan = 'pending' 
              ORDER BY l.tgl_pinjam ASC";
    
    $stmt = $conn->query($query);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $requests = [];
    $message = "Gagal mengambil data: " . $e->getMessage();
    $message_type = 'error';
}

// Get available drivers
try {
    $drivers = $conn->query("SELECT * FROM users WHERE role = 'supir' ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $drivers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Peminjaman - HRGA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #0A192F;
            --secondary-cyan: #64FFDA;
            --accent-orange: #FF8C00;
            --neutral-light: #F8F9FA;
        }
        
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        
        .badge-approved {
            background-color: var(--secondary-cyan);
            color: var(--primary-navy);
        }
        
        .badge-rejected {
            background-color: #dc3545;
        }
        
        .modal-header {
            background-color: var(--primary-navy);
            color: white;
        }
        
        .btn-success {
            background-color: var(--secondary-cyan);
            border-color: var(--secondary-cyan);
            color: var(--primary-navy);
            font-weight: bold;
        }
        
        .btn-success:hover {
            background-color: #52d4b9;
            border-color: #52d4b9;
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .table th {
            background-color: var(--primary-navy);
            color: white;
            position: sticky;
            top: 0;
        }
        
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .loan-detail {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 5px 0;
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
                    <h1 class="h2"><i class="fas fa-check-circle"></i> Persetujuan Peminjaman</h1>
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-clock"></i> <?php echo count($requests); ?> Menunggu
                    </span>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($requests)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada permintaan persetujuan yang tertunda.
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Pemohon</th>
                                            <th>Divisi</th>
                                            <th>Aset</th>
                                            <th>Periode Pinjam</th>
                                            <th>Keterangan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $index => $request): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td>
                                                <?php 
                                                $date = new DateTime($request['tgl_pinjam']);
                                                echo $date->format('d/m/Y'); 
                                                ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($request['nama_pemohon']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['divisi']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-<?php echo $request['kategori'] == 'mobil' ? 'primary' : 'info'; ?> me-2">
                                                        <?php echo strtoupper(substr($request['kategori'], 0, 1)); ?>
                                                    </span>
                                                    <?php echo htmlspecialchars($request['nama_aset']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php 
                                                    $tgl_pinjam = new DateTime($request['tgl_pinjam']);
                                                    $tgl_kembali = new DateTime($request['tgl_kembali']);
                                                    echo $tgl_pinjam->format('d/m') . ' - ' . $tgl_kembali->format('d/m/Y');
                                                    ?>
                                                    <br>
                                                    <span class="text-muted">
                                                        (<?php 
                                                        $diff = $tgl_pinjam->diff($tgl_kembali);
                                                        echo $diff->days + 1; ?> hari)
                                                    </span>
                                                </small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($request['keterangan'], 0, 50)); ?>...</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-pending">
                                                    <i class="fas fa-clock"></i> Menunggu
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approveModal"
                                                            data-id="<?php echo $request['id_loan']; ?>"
                                                            data-asset="<?php echo htmlspecialchars($request['nama_aset']); ?>"
                                                            data-pemohon="<?php echo htmlspecialchars($request['nama_pemohon']); ?>"
                                                            data-kategori="<?php echo $request['kategori']; ?>">
                                                        <i class="fas fa-check"></i> Setujui
                                                    </button>
                                                    <button class="btn btn-danger"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal"
                                                            data-id="<?php echo $request['id_loan']; ?>"
                                                            data-asset="<?php echo htmlspecialchars($request['nama_aset']); ?>"
                                                            data-pemohon="<?php echo htmlspecialchars($request['nama_pemohon']); ?>">
                                                        <i class="fas fa-times"></i> Tolak
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Modal Approve -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-check-circle"></i> Setujui Peminjaman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_loan" id="approve_id">
                        <input type="hidden" name="action" value="approve">
                        
                        <div class="mb-3">
                            <p>Anda akan menyetujui peminjaman:</p>
                            <div class="loan-detail">
                                <p><strong>Aset:</strong> <span id="approve_asset"></span></p>
                                <p><strong>Pemohon:</strong> <span id="approve_pemohon"></span></p>
                                <p><strong>Kategori:</strong> <span id="approve_kategori"></span></p>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="driverSelectGroup">
                            <label class="form-label">Pilih Driver (Opsional)</label>
                            <select class="form-select" name="driver_id" id="driverSelect">
                                <option value="">Tanpa Driver</option>
                                <?php foreach ($drivers as $driver): ?>
                                    <option value="<?php echo $driver['id_user']; ?>">
                                        <?php echo htmlspecialchars($driver['nama']); ?> 
                                        (<?php echo htmlspecialchars($driver['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hanya untuk kategori mobil</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Catatan Persetujuan (Opsional)</label>
                            <textarea class="form-control" name="catatan" rows="3" placeholder="Catatan untuk pemohon..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Setujui Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Reject -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-times-circle"></i> Tolak Peminjaman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_loan" id="reject_id">
                        <input type="hidden" name="action" value="reject">
                        
                        <div class="mb-3">
                            <p>Anda akan menolak peminjaman:</p>
                            <div class="loan-detail">
                                <p><strong>Aset:</strong> <span id="reject_asset"></span></p>
                                <p><strong>Pemohon:</strong> <span id="reject_pemohon"></span></p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan *</label>
                            <textarea class="form-control" name="alasan_penolakan" rows="4" required 
                                      placeholder="Berikan alasan yang jelas untuk penolakan..."></textarea>
                            <small class="text-muted">Alasan akan dikirimkan kepada pemohon</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle approve modal
        var approveModal = document.getElementById('approveModal');
        approveModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var modal = this;
            var kategori = button.getAttribute('data-kategori');
            
            modal.querySelector('#approve_id').value = button.getAttribute('data-id');
            modal.querySelector('#approve_asset').textContent = button.getAttribute('data-asset');
            modal.querySelector('#approve_pemohon').textContent = button.getAttribute('data-pemohon');
            modal.querySelector('#approve_kategori').textContent = kategori === 'mobil' ? 'Mobil' : 'Elektronik';
            
            // Show/hide driver select based on kategori
            var driverGroup = modal.querySelector('#driverSelectGroup');
            if (kategori === 'mobil') {
                driverGroup.style.display = 'block';
            } else {
                driverGroup.style.display = 'none';
            }
        });
        
        // Handle reject modal
        var rejectModal = document.getElementById('rejectModal');
        rejectModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            this.querySelector('#reject_id').value = button.getAttribute('data-id');
            this.querySelector('#reject_asset').textContent = button.getAttribute('data-asset');
            this.querySelector('#reject_pemohon').textContent = button.getAttribute('data-pemohon');
        });
    </script>
</body>
</html>