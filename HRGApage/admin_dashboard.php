<?php
session_start();
require_once 'database/db.php';
require_once 'auth_check.php';

// Cek role HRGA saja yang boleh akses
checkrole(['hrga']);

// Get statistics
try {
    // Total assets
    $total_assets = $conn->query("SELECT COUNT(*) FROM assets")->fetchColumn();
    
    // Total loans
    $total_loans = $conn->query("SELECT COUNT(*) FROM loans")->fetchColumn();
    
    // Pending loans
    $pending_loans = $conn->query("SELECT COUNT(*) FROM loans WHERE status_loan = 'pending'")->fetchColumn();
    
    // Active loans
    $active_loans = $conn->query("SELECT COUNT(*) FROM loans WHERE status_loan IN ('approved', 'on_loan')")->fetchColumn();
    
    // Get recent pending requests
    $recent_requests = $conn->query("SELECT l.*, u.nama as nama_pemohon, a.nama_aset 
                                     FROM loans l 
                                     JOIN users u ON l.id_user = u.id_user 
                                     JOIN assets a ON l.id_aset = a.id_aset 
                                     WHERE l.status_loan = 'pending' 
                                     ORDER BY l.tgl_pinjam ASC 
                                     LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activities
    $recent_activities = $conn->query("SELECT l.*, u.nama as nama_pemohon, a.nama_aset 
                                       FROM loans l 
                                       JOIN users u ON l.id_user = u.id_user 
                                       JOIN assets a ON l.id_aset = a.id_aset 
                                       WHERE l.status_loan != 'pending' 
                                       ORDER BY l.tgl_pinjam DESC 
                                       LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Gagal mengambil data dashboard: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HRGA - AssetFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #0A192F;
            --secondary-cyan: #64FFDA;
            --accent-orange: #FF8C00;
            --neutral-light: #F8F9FA;
        }
        
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .stat-card.assets { 
            background: linear-gradient(45deg, var(--primary-navy), #1a3a6b); 
        }
        .stat-card.loans { 
            background: linear-gradient(45deg, var(--secondary-cyan), #52d4b9); 
            color: var(--primary-navy);
        }
        .stat-card.pending { 
            background: linear-gradient(45deg, #ffc107, #e0a800); 
            color: #000;
        }
        .stat-card.active { 
            background: linear-gradient(45deg, var(--accent-orange), #e67e00); 
        }
        
        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        
        .dashboard-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 12px 15px;
        }
        
        .list-group-item:first-child {
            border-top: none;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .quick-actions .btn {
            margin: 5px;
            min-width: 150px;
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'components/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard HRGA
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="me-3">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong></span>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card assets text-center">
                            <i class="fas fa-boxes"></i>
                            <div class="h2 font-weight-bold mt-2"><?php echo $total_assets; ?></div>
                            <div class="h6">Total Aset</div>
                            <small>Tersedia & Dipinjam</small>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card loans text-center">
                            <i class="fas fa-clipboard-list"></i>
                            <div class="h2 font-weight-bold mt-2"><?php echo $total_loans; ?></div>
                            <div class="h6">Total Peminjaman</div>
                            <small>Semua Waktu</small>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card pending text-center">
                            <i class="fas fa-clock"></i>
                            <div class="h2 font-weight-bold mt-2"><?php echo $pending_loans; ?></div>
                            <div class="h6">Menunggu</div>
                            <small>Perlu Persetujuan</small>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card active text-center">
                            <i class="fas fa-car"></i>
                            <div class="h2 font-weight-bold mt-2"><?php echo $active_loans; ?></div>
                            <div class="h6">Aktif</div>
                            <small>Sedang Dipinjam</small>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="dashboard-section">
                    <h5 class="mb-3"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    <div class="quick-actions text-center">
                        <a href="persetujuan.php" class="btn btn-warning">
                            <i class="fas fa-check-circle"></i> Review Permohonan
                            <?php if ($pending_loans > 0): ?>
                                <span class="badge bg-danger ms-1"><?php echo $pending_loans; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="kelola_aset.php" class="btn btn-primary">
                            <i class="fas fa-boxes"></i> Kelola Aset
                        </a>
                        <a href="laporan.php" class="btn btn-success">
                            <i class="fas fa-chart-bar"></i> Lihat Laporan
                        </a>
                        <a href="kelola_user.php" class="btn btn-info">
                            <i class="fas fa-users"></i> Kelola User
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Recent Pending Requests -->
                    <div class="col-lg-6 mb-4">
                        <div class="dashboard-section h-100">
                            <h5 class="mb-3">
                                <i class="fas fa-clock me-2"></i>Permohonan Menunggu
                                <span class="badge bg-warning float-end"><?php echo $pending_loans; ?></span>
                            </h5>
                            
                                                        <?php if (empty($recent_requests)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Tidak ada permohonan yang menunggu persetujuan.
                                </div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($recent_requests as $request): 
                                        $tgl_pinjam = new DateTime($request['tgl_pinjam']);
                                        $tgl_kembali = new DateTime($request['tgl_kembali']);
                                        $diff = $tgl_pinjam->diff($tgl_kembali);
                                    ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($request['nama_aset']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo $tgl_pinjam->format('d/m'); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1">
                                            <small>
                                                <strong>Pemohon:</strong> <?php echo htmlspecialchars($request['nama_pemohon']); ?><br>
                                                <strong>Durasi:</strong> <?php echo ($diff->days + 1); ?> hari
                                            </small>
                                        </p>
                                        <small>
                                            <a href="persetujuan.php" class="text-primary">
                                                <i class="fas fa-external-link-alt"></i> Review
                                            </a>
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($pending_loans > 5): ?>
                                    <div class="text-center mt-3">
                                        <a href="persetujuan.php" class="btn btn-sm btn-warning">
                                            <i class="fas fa-eye"></i> Lihat Semua (<?php echo $pending_loans; ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
                    <div class="col-lg-6 mb-4">
                        <div class="dashboard-section h-100">
                            <h5 class="mb-3">
                                <i class="fas fa-history me-2"></i>Aktivitas Terbaru
                            </h5>
                            
                            <?php if (empty($recent_activities)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Belum ada aktivitas peminjaman.
                                </div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($recent_activities as $activity): 
                                        $tgl_pinjam = new DateTime($activity['tgl_pinjam']);
                                        
                                        // Determine status badge
                                        $status_class = '';
                                        $status_text = '';
                                        $status_icon = '';
                                        
                                        switch ($activity['status_loan']) {
                                            case 'approved':
                                                $status_class = 'bg-success';
                                                $status_text = 'Disetujui';
                                                $status_icon = 'check-circle';
                                                break;
                                            case 'rejected':
                                                $status_class = 'bg-danger';
                                                $status_text = 'Ditolak';
                                                $status_icon = 'times-circle';
                                                break;
                                            case 'on_loan':
                                                $status_class = 'bg-info';
                                                $status_text = 'Dipinjam';
                                                $status_icon = 'car';
                                                break;
                                            case 'returned':
                                                $status_class = 'bg-secondary';
                                                $status_text = 'Dikembalikan';
                                                $status_icon = 'undo';
                                                break;
                                            default:
                                                $status_class = 'bg-secondary';
                                                $status_text = $activity['status_loan'];
                                                $status_icon = 'question-circle';
                                        }
                                    ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($activity['nama_aset']); ?></h6>
                                                <p class="mb-1 small">
                                                    <?php echo htmlspecialchars($activity['nama_pemohon']); ?>
                                                </p>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge <?php echo $status_class; ?> badge-status">
                                                    <i class="fas fa-<?php echo $status_icon; ?>"></i> <?php echo $status_text; ?>
                                                </span><br>
                                                <small class="text-muted">
                                                    <?php echo $tgl_pinjam->format('d/m/Y'); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="laporan.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-list"></i> Lihat Semua Aktivitas
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Calendar Section -->
                <div class="dashboard-section">
                    <h5 class="mb-3">
                        <i class="fas fa-calendar me-2"></i>Jadwal Peminjaman Minggu Ini
                    </h5>
                    <?php
                    try {
                        // Get current week start and end
                        $monday = date('Y-m-d', strtotime('monday this week'));
                        $sunday = date('Y-m-d', strtotime('sunday this week'));
                        
                        $weekly_schedule = $conn->query("SELECT l.*, u.nama as nama_pemohon, a.nama_aset, a.kategori 
                                                         FROM loans l 
                                                         JOIN users u ON l.id_user = u.id_user 
                                                         JOIN assets a ON l.id_aset = a.id_aset 
                                                         WHERE l.tgl_pinjam BETWEEN '$monday' AND '$sunday'
                                                         AND l.status_loan IN ('approved', 'on_loan')
                                                         ORDER BY l.tgl_pinjam ASC")->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($weekly_schedule)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Tidak ada jadwal peminjaman untuk minggu ini.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Aset</th>
                                            <th>Pemohon</th>
                                            <th>Kategori</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($weekly_schedule as $schedule): 
                                            $tgl_pinjam = new DateTime($schedule['tgl_pinjam']);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo $tgl_pinjam->format('D, d/m'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($schedule['nama_aset']); ?></td>
                                            <td><?php echo htmlspecialchars($schedule['nama_pemohon']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $schedule['kategori'] == 'mobil' ? 'primary' : 'info'; ?>">
                                                    <?php echo ucfirst($schedule['kategori']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Approved
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif;
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger">Gagal mengambil jadwal: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>
                
                <!-- Asset Status Overview -->
                <div class="dashboard-section">
                    <h5 class="mb-3">
                        <i class="fas fa-chart-pie me-2"></i>Status Aset
                    </h5>
                    <?php
                    try {
                        $asset_status = $conn->query("SELECT status_aset, COUNT(*) as count 
                                                      FROM assets 
                                                      GROUP BY status_aset")->fetchAll(PDO::FETCH_ASSOC);
                        
                        $asset_categories = $conn->query("SELECT kategori, COUNT(*) as count 
                                                          FROM assets 
                                                          GROUP BY kategori")->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-center mb-3">Status Aset</h6>
                            <div class="list-group">
                                <?php foreach ($asset_status as $status): 
                                    $badge_class = '';
                                    switch ($status['status_aset']) {
                                        case 'tersedia': $badge_class = 'bg-success'; break;
                                        case 'maintenance': $badge_class = 'bg-warning text-dark'; break;
                                        case 'rusak': $badge_class = 'bg-danger'; break;
                                    }
                                ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo ucfirst($status['status_aset']); ?>
                                    <span class="badge <?php echo $badge_class; ?> rounded-pill">
                                        <?php echo $status['count']; ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-center mb-3">Kategori Aset</h6>
                            <div class="list-group">
                                <?php foreach ($asset_categories as $category): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo ucfirst($category['kategori']); ?>
                                    <span class="badge bg-primary rounded-pill">
                                        <?php echo $category['count']; ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php } catch (PDOException $e) {
                        echo '<div class="alert alert-danger">Gagal mengambil status aset: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    } ?>
                </div>
                
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh stats every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        
        // Make stat cards clickable
        document.querySelectorAll('.stat-card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function() {
                const text = this.querySelector('.h6').textContent.toLowerCase();
                if (text.includes('aset')) {
                    window.location.href = 'kelola_aset.php';
                } else if (text.includes('menunggu')) {
                    window.location.href = 'persetujuan.php';
                } else if (text.includes('peminjaman')) {
                    window.location.href = 'laporan.php';
                } else if (text.includes('aktif')) {
                    window.location.href = 'laporan.php?status=on_loan';
                }
            });
        });
    </script>
</body>
</html>