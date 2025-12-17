<?php
session_start();
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['hrga']);

// Filter defaults
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$status = $_GET['status'] ?? '';
$kategori = $_GET['kategori'] ?? '';

// Build query
$query = "
    SELECT l.*, u.nama as pemohon, u.divisi, a.nama_aset, a.kategori, d.nama as driver
    FROM loans l 
    JOIN users u ON l.id_user = u.id_user 
    JOIN assets a ON l.id_aset = a.id_aset
    LEFT JOIN users d ON l.driver_id = d.id_user
    WHERE DATE(l.tgl_pinjam) BETWEEN ? AND ?
";

$params = [$start_date, $end_date];

if ($status) {
    $query .= " AND l.status_loan = ?";
    $params[] = $status;
}

if ($kategori) {
    $query .= " AND a.kategori = ?";
    $params[] = $kategori;
}

$query .= " ORDER BY l.tgl_pinjam DESC";

// Get data
$stmt = $conn->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Get statistics
$total = count($reports);
$approved = 0;
$rejected = 0;
$pending = 0;

foreach($reports as $r) {
    switch($r['status_loan']) {
        case 'approved': $approved++; break;
        case 'rejected': $rejected++; break;
        case 'pending': $pending++; break;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Peminjaman</title>
    <link rel="stylesheet" href="css/laporan_adm.css">
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
        <h1>Laporan Peminjaman</h1>
        
        <form method="GET" class="filter-card">
            <div class="filter-group">
                <label>Tanggal Mulai</label>
                <input type="date" name="start_date" value="<?= $start_date ?>">
            </div>
            
            <div class="filter-group">
                <label>Tanggal Akhir</label>
                <input type="date" name="end_date" value="<?= $end_date ?>">
            </div>
            
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $status=='pending'?'selected':'' ?>>Pending</option>
                    <option value="approved" <?= $status=='approved'?'selected':'' ?>>Approved</option>
                    <option value="rejected" <?= $status=='rejected'?'selected':'' ?>>Rejected</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Kategori</label>
                <select name="kategori">
                    <option value="">Semua Kategori</option>
                    <option value="mobil" <?= $kategori=='mobil'?'selected':'' ?>>Mobil</option>
                    <option value="elektronik" <?= $kategori=='elektronik'?'selected':'' ?>>Elektronik</option>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Terapkan Filter</button>
        </form>
        
        <div class="stats-container">
            <div class="stat-box stat-total">
                <h4>TOTAL DATA</h4>
                <div class="number"><?= $total ?></div>
            </div>
            <div class="stat-box stat-approved">
                <h4>APPROVED</h4>
                <div class="number"><?= $approved ?></div>
            </div>
            <div class="stat-box stat-rejected">
                <h4>REJECTED</h4>
                <div class="number"><?= $rejected ?></div>
            </div>
            <div class="stat-box stat-pending">
                <h4>PENDING</h4>
                <div class="number"><?= $pending ?></div>
            </div>
        </div>
        
        <h3>Detail Laporan</h3>
        <?php if(empty($reports)): ?>
            <p style="background:#fff; padding:20px; border-radius:8px;">Tidak ada data ditemukan untuk periode ini.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table cellpadding="10">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pemohon</th>
                            <th>Divisi</th>
                            <th>Aset</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Driver</th>
                            <th>Ket/Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reports as $index => $r): ?>
                        <tr>
                            <td><?= $index+1 ?></td>
                            <td><?= date('d/m/Y', strtotime($r['tgl_pinjam'])) ?></td>
                            <td><?= htmlspecialchars($r['pemohon']) ?></td>
                            <td><?= $r['divisi'] ?></td>
                            <td><?= htmlspecialchars($r['nama_aset']) ?></td>
                            <td><?= ucfirst($r['kategori']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $r['status_loan'] ?>">
                                    <?= ucfirst($r['status_loan']) ?>
                                </span>
                            </td>
                            <td><?= $r['driver'] ?: '-' ?></td>
                            <td><?= $r['alasan_penolakan'] ?: '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="export-buttons">
                <a href="javascript:window.print()" class="btn-print">Cetak PDF / Print</a>
            </div>
        <?php endif; ?>
        
    </div> </body>
</html>