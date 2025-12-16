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
</head>
<body>
    <h1>Laporan Peminjaman</h1>
    
    <!-- Filter Form -->
    <form method="GET">
        Tanggal Mulai: <input type="date" name="start_date" value="<?= $start_date ?>"><br>
        Tanggal Akhir: <input type="date" name="end_date" value="<?= $end_date ?>"><br>
        Status: 
        <select name="status">
            <option value="">Semua</option>
            <option value="pending" <?= $status=='pending'?'selected':'' ?>>Pending</option>
            <option value="approved" <?= $status=='approved'?'selected':'' ?>>Approved</option>
            <option value="rejected" <?= $status=='rejected'?'selected':'' ?>>Rejected</option>
        </select><br>
        Kategori:
        <select name="kategori">
            <option value="">Semua</option>
            <option value="mobil" <?= $kategori=='mobil'?'selected':'' ?>>Mobil</option>
            <option value="elektronik" <?= $kategori=='elektronik'?'selected':'' ?>>Elektronik</option>
        </select><br>
        <button type="submit">Filter</button>
    </form>
    
    <hr>
    
    <!-- Statistics -->
    <h3>Statistik:</h3>
    <p>Total: <?= $total ?></p>
    <p>Approved: <?= $approved ?></p>
    <p>Rejected: <?= $rejected ?></p>
    <p>Pending: <?= $pending ?></p>
    
    <hr>
    
    <!-- Report Table -->
    <h3>Detail Laporan</h3>
    <?php if(empty($reports)): ?>
        <p>Tidak ada data</p>
    <?php else: ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Pemohon</th>
                <th>Divisi</th>
                <th>Aset</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Driver</th>
                <th>Alasan Penolakan</th>
            </tr>
            
            <?php foreach($reports as $index => $r): ?>
            <tr>
                <td><?= $index+1 ?></td>
                <td><?= date('d/m/Y', strtotime($r['tgl_pinjam'])) ?></td>
                <td><?= htmlspecialchars($r['pemohon']) ?></td>
                <td><?= $r['divisi'] ?></td>
                <td><?= htmlspecialchars($r['nama_aset']) ?></td>
                <td><?= $r['kategori'] ?></td>
                <td><?= $r['status_loan'] ?></td>
                <td><?= $r['driver'] ?: '-' ?></td>
                <td><?= $r['alasan_penolakan'] ?: '-' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <!-- Export Links -->
        <p>
            <a href="javascript:window.print()">Cetak</a> | 
            <a href="export_excel.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&status=<?= $status ?>&kategori=<?= $kategori ?>">Export Excel</a>
        </p>
    <?php endif; ?>
    
    <p><a href="admin_dashboard.php">Kembali ke Dashboard</a></p>
</body>
</html>