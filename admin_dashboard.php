<?php
session_start();
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['hrga']);

// Get statistics
$total_assets = $conn->query("SELECT COUNT(*) FROM assets")->fetchColumn();
$total_loans = $conn->query("SELECT COUNT(*) FROM loans")->fetchColumn();
$pending_loans = $conn->query("SELECT COUNT(*) FROM loans WHERE status_loan='pending'")->fetchColumn();
$active_loans = $conn->query("SELECT COUNT(*) FROM loans WHERE status_loan IN ('approved','on_loan')")->fetchColumn();

// Get recent pending
$recent_pending = $conn->query("
    SELECT l.*, u.nama as pemohon, a.nama_aset 
    FROM loans l 
    JOIN users u ON l.id_user = u.id_user 
    JOIN assets a ON l.id_aset = a.id_aset 
    WHERE l.status_loan='pending' 
    ORDER BY l.tgl_pinjam ASC 
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard HRGA</title>
</head>
<body>
    <h1>Dashboard HRGA</h1>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?> (<?= $_SESSION['role'] ?>)</p>
    
    <hr>
    
    <!-- Statistics -->
    <h2>Statistik</h2>
    <table border="1" cellpadding="10">
        <tr>
            <td><strong>Total Aset</strong></td>
            <td><?= $total_assets ?></td>
            <td><a href="kelola_aset.php">Lihat</a></td>
        </tr>
        <tr>
            <td><strong>Total Peminjaman</strong></td>
            <td><?= $total_loans ?></td>
            <td><a href="laporan.php">Lihat</a></td>
        </tr>
        <tr>
            <td><strong>Pending Approval</strong></td>
            <td><?= $pending_loans ?></td>
            <td><a href="persetujuan.php">Review</a></td>
        </tr>
        <tr>
            <td><strong>Sedang Dipinjam</strong></td>
            <td><?= $active_loans ?></td>
            <td><a href="laporan.php?status=approved">Lihat</a></td>
        </tr>
    </table>
    
    <hr>
    
    <!-- Quick Actions -->
    <h2>Quick Actions</h2>
    <p>
        <a href="kelola_aset.php">Kelola Aset</a> | 
        <a href="persetujuan.php">Persetujuan Peminjaman</a> | 
        <a href="laporan.php">Laporan</a> | 
        <a href="kelola_user.php">Kelola User</a>
    </p>
    
    <hr>
    
    <!-- Recent Pending -->
    <h2>Permohonan Menunggu (<?= $pending_loans ?>)</h2>
    <?php if(empty($recent_pending)): ?>
        <p>Tidak ada permohonan pending</p>
    <?php else: ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Pemohon</th>
                <th>Aset</th>
                <th>Aksi</th>
            </tr>
            <?php foreach($recent_pending as $index => $p): ?>
            <tr>
                <td><?= $index+1 ?></td>
                <td><?= date('d/m/Y', strtotime($p['tgl_pinjam'])) ?></td>
                <td><?= htmlspecialchars($p['pemohon']) ?></td>
                <td><?= htmlspecialchars($p['nama_aset']) ?></td>
                <td><a href="persetujuan.php">Review</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php if($pending_loans > 5): ?>
            <p><a href="persetujuan.php">Lihat semua (<?= $pending_loans ?> permohonan)</a></p>
        <?php endif; ?>
    <?php endif; ?>
    
    <hr>
    
    <!-- Recent Activities -->
    <h2>Aktivitas Terbaru</h2>
    <?php
    $recent_activities = $conn->query("
        SELECT l.*, u.nama as pemohon, a.nama_aset 
        FROM loans l 
        JOIN users u ON l.id_user = u.id_user 
        JOIN assets a ON l.id_aset = a.id_aset 
        WHERE l.status_loan != 'pending' 
        ORDER BY l.tgl_pinjam DESC 
        LIMIT 10
    ")->fetchAll();
    ?>
    
    <?php if(empty($recent_activities)): ?>
        <p>Tidak ada aktivitas</p>
    <?php else: ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Tanggal</th>
                <th>Pemohon</th>
                <th>Aset</th>
                <th>Status</th>
            </tr>
            <?php foreach($recent_activities as $a): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($a['tgl_pinjam'])) ?></td>
                <td><?= htmlspecialchars($a['pemohon']) ?></td>
                <td><?= htmlspecialchars($a['nama_aset']) ?></td>
                <td><?= $a['status_loan'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><a href="laporan.php">Lihat semua aktivitas</a></p>
    <?php endif; ?>
    
    <hr>
    
    <!-- Asset Status -->
    <h2>Status Aset</h2>
    <?php
    $asset_status = $conn->query("SELECT status_aset, COUNT(*) as jumlah FROM assets GROUP BY status_aset")->fetchAll();
    ?>
    <table border="1" cellpadding="10">
        <tr><th>Status</th><th>Jumlah</th></tr>
        <?php foreach($asset_status as $status): ?>
        <tr>
            <td><?= $status['status_aset'] ?></td>
            <td><?= $status['jumlah'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <hr>
    
    <p><a href="logout.php">Logout</a></p>
</body>
</html>