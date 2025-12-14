<?php
session_start();
require_once 'database/db.php';
require_once 'auth_check.php';

// Cek role HRGA saja yang boleh akses
checkrole(['hrga']);

// Set default date range (current month)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$divisi_filter = isset($_GET['divisi']) ? $_GET['divisi'] : '';

// Build query
$query = "SELECT l.*, u.nama as nama_pemohon, u.divisi, a.nama_aset, a.kategori,
                 d.nama as nama_driver
          FROM loans l 
          JOIN users u ON l.id_user = u.id_user 
          JOIN assets a ON l.id_aset = a.id_aset
          LEFT JOIN users d ON l.driver_id = d.id_user
          WHERE DATE(l.tgl_pinjam) BETWEEN :start_date AND :end_date";

$params = [
    ':start_date' => $start_date,
    ':end_date' => $end_date
];

if ($status_filter) {
    $query .= " AND l.status_loan = :status";
    $params[':status'] = $status_filter;
}

if ($kategori_filter) {
    $query .= " AND a.kategori = :kategori";
    $params[':kategori'] = $kategori_filter;
}

if ($divisi_filter) {
    $query .= " AND u.divisi = :divisi";
    $params[':divisi'] = $divisi_filter;
}

$query .= " ORDER BY l.tgl_pinjam DESC, l.status_loan";

// Get report data
try {
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reports = [];
    $error = "Gagal mengambil data laporan: " . $e->getMessage();
}

// Get unique divisions for filter
try {
    $divisions = $conn->query("SELECT DISTINCT divisi FROM users WHERE divisi IS NOT NULL ORDER BY divisi")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $divisions = [];
}

// Calculate statistics
$total_peminjaman = count($reports);
$approved = 0;
$rejected = 0;
$pending = 0;
$on_loan = 0;
$returned = 0;

foreach ($reports as $report) {
    switch ($report['status_loan']) {
        case 'approved': $approved++; break;
        case 'rejected': $rejected++; break;
        case 'pending': $pending++; break;
        case 'on_loan': $on_loan++; break;
        case 'returned': $returned++; break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman - HRGA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
        }
        
        .stat-card.total { 
            background: linear-gradient(45deg, var(--primary-navy), #1a3a6b); 
        }
        .stat-card.approved { 
            background: linear-gradient(45deg, var(--secondary-cyan), #52d4b9); 
            color: var(--primary-navy);
        }
        .stat-card.rejected { 
            background: linear-gradient(45deg, #dc3545, #c82333); 
        }
        .stat-card.pending { 
            background: linear-gradient(45deg, #ffc107, #e0a800); 
            color: #000;
        }
        .stat-card.onloan { 
            background: linear-gradient(45deg, var(--accent-orange), #e67e00); 
        }
        
        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .btn-export {
            background-color: var(--secondary-cyan);
            border-color: var(--secondary-cyan);
            color: var(--primary-navy);
            font-weight: bold;
        }
        
        .btn-export:hover {
            background-color: #52d4b9;
            border-color: #52d4b9;
        }
        
        .table th {
            background-color: var(--primary-navy);
            color: white;
        }
        
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5px 10px;
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
                    <h1 class="h2"><i class="fas fa-chart-bar"></i> Laporan Peminjaman</h1>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="printReport()">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                        <button class="btn btn-export" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card total text-center">
                            <i class="fas fa-clipboard-list"></i>
                            <div class="h4 font-weight-bold mt-2"><?php echo $total_peminjaman; ?></div>
                            <div class="small">Total</div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card approved text-center">
                            <i class="fas fa-check-circle"></i>
                            <div class="h4 font-weight-bold mt-2"><?php echo $approved; ?></div>
                            <div class="small">Disetujui</div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card pending text-center">
                            <i class="fas fa-clock"></i>
                            <div class="h4 font-weight-bold mt-2"><?php echo $pending; ?></div>
                            <div class="small">Menunggu</div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card onloan text-center">
                            <i class="fas fa-car"></i>
                            <div class="h4 font-weight-bold mt-2"><?php echo $on_loan; ?></div>
                            <div class="small">Dipinjam</div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card total text-center">
                            <i class="fas fa-undo"></i>
                            <div class="h4 font-weight-bold mt-2"><?php echo $returned; ?></div>
                            <div class="small">Dikembalikan</div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card rejected text-center">
                            <i class="fas fa-times-circle"></i>
                            <div class="h4 font-weight-bold mt-2"><?php echo $rejected; ?></div>
                            <div class="small">Ditolak</div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                                <option value="on_loan" <?php echo $status_filter == 'on_loan' ? 'selected' : ''; ?>>Dipinjam</option>
                                <option value="returned" <?php echo $status_filter == 'returned' ? 'selected' : ''; ?>>Dikembalikan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="kategori">
                                <option value="">Semua Kategori</option>
                                <option value="mobil" <?php echo $kategori_filter == 'mobil' ? 'selected' : ''; ?>>Mobil</option>
                                <option value="elektronik" <?php echo $kategori_filter == 'elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Divisi</label>
                            <select class="form-select" name="divisi">
                                <option value="">Semua Divisi</option>
                                <?php foreach ($divisions as $division): ?>
                                    <option value="<?php echo htmlspecialchars($division); ?>" 
                                        <?php echo $divisi_filter == $division ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($division); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter Data
                            </button>
                            <a href="laporan.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset Filter
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Report Table -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-white">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-table"></i> Detail Laporan 
                            <span class="badge bg-primary"><?php echo count($reports); ?> Data</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="reportTable" class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>Pemohon</th>
                                        <th>Divisi</th>
                                        <th>Aset</th>
                                        <th>Kategori</th>
                                        <th>Periode</th>
                                        <th>Driver</th>
                                        <th>Status</th>
                                        <th>Alasan Penolakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $index => $report): 
                                        $tgl_pinjam = new DateTime($report['tgl_pinjam']);
                                        $tgl_kembali = new DateTime($report['tgl_kembali']);
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo $tgl_pinjam->format('d/m/Y'); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($report['nama_pemohon']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($report['divisi']); ?></td>
                                        <td><?php echo htmlspecialchars($report['nama_aset']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $report['kategori'] == 'mobil' ? 'primary' : 'info'; ?>">
                                                <?php echo ucfirst($report['kategori']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo $tgl_pinjam->format('d/m') . ' - ' . $tgl_kembali->format('d/m'); ?>
                                                <br>
                                                <span class="text-muted">
                                                    <?php 
                                                    $diff = $tgl_pinjam->diff($tgl_kembali);
                                                    echo $diff->days + 1; ?> hari
                                                </span>
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo $report['nama_driver'] ? htmlspecialchars($report['nama_driver']) : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            $status_text = '';
                                            $icon = '';
                                            
                                            switch ($report['status_loan']) {
                                                case 'approved': 
                                                    $badge_class = 'bg-success';
                                                    $status_text = 'Disetujui';
                                                    $icon = 'check-circle';
                                                    break;
                                                case 'rejected': 
                                                    $badge_class = 'bg-danger';
                                                    $status_text = 'Ditolak';
                                                    $icon = 'times-circle';
                                                    break;
                                                case 'pending': 
                                                    $badge_class = 'bg-warning text-dark';
                                                    $status_text = 'Menunggu';
                                                    $icon = 'clock';
                                                    break;
                                                case 'on_loan': 
                                                    $badge_class = 'bg-info';
                                                    $status_text = 'Dipinjam';
                                                    $icon = 'car';
                                                    break;
                                                case 'returned': 
                                                    $badge_class = 'bg-secondary';
                                                    $status_text = 'Dikembalikan';
                                                    $icon = 'undo';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?> badge-status">
                                                <i class="fas fa-<?php echo $icon; ?>"></i>
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo $report['alasan_penolakan'] ? 
                                                    '<span class="text-danger">' . htmlspecialchars($report['alasan_penolakan']) . '</span>' : 
                                                    '-'; ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="9" class="text-end">Total Data:</th>
                                        <th><?php echo count($reports); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#reportTable').DataTable({
                "order": [[0, "desc"]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
                },
                "pageLength": 25,
                "responsive": true
            });
        });
        
        // Print function
        function printReport() {
            window.print();
        }
        
        // Export to Excel
        function exportToExcel() {
            const table = document.getElementById('reportTable');
            const wb = XLSX.utils.table_to_book(table, {sheet: "Laporan Peminjaman"});
            
            // Get current date for filename
            const today = new Date();
            const dateStr = today.toISOString().split('T')[0];
            
            XLSX.writeFile(wb, `Laporan_Peminjaman_${dateStr}.xlsx`);
        }
    </script>
</body>
</html>