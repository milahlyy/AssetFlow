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
    <link rel="stylesheet" href="css/admindash.css">
</head>
<body>
    
    <div class="sidebar">
        <h2>HRGA</h2>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="kelola_aset.php">Kelola Aset</a>
        <a href="persetujuan.php">Persetujuan Peminjaman</a>
        <a href="laporan.php">Laporan</a>
        <a href="kelola_user.php">Kelola User</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Dashboard HRGA</h1>
        <p class="welcome">Selamat datang, <b><?= htmlspecialchars($_SESSION['nama']) ?></b> (<?= $_SESSION['role'] ?>)</p>
        
        <h2>Statistik</h2>
        <div class="stats-grid">
            <div class="card">
                <span class="label">Total Aset</span>
                <span class="number"><?= $total_assets ?></span>
                <a href="kelola_aset.php" class="btn-small">Lihat</a>
            </div>
            <div class="card">
                <span class="label">Total Peminjaman</span>
                <span class="number"><?= $total_loans ?></span>
                <a href="laporan.php" class="btn-small">Lihat</a>
            </div>
            <div class="card">
                <span class="label">Pending Approval</span>
                <span class="number"><?= $pending_loans ?></span>
                <a href="persetujuan.php" class="btn-small">Review</a>
            </div>
            <div class="card">
                <span class="label">Sedang Dipinjam</span>
                <span class="number"><?= $active_loans ?></span>
                <a href="laporan.php?status=approved" class="btn-small">Lihat</a>
            </div>
        </div>
        
        <h2>Quick Actions</h2>
        <div class="quick-actions">
            <a href="kelola_aset.php" class="btn-blue">Kelola Aset</a>
            <a href="persetujuan.php" class="btn-yellow">Persetujuan Peminjaman</a>
            <a href="laporan.php" class="btn-green">Laporan</a>
            <a href="kelola_user.php" class="btn-cyan">Kelola User</a>
        </div>
        
        <div class="row-container">
            
            <div class="box-container">
                <h2>Permohonan Menunggu (<?= $pending_loans ?>)</h2>
                <?php if(empty($recent_pending)): ?>
                    <p class="empty">Tidak ada permohonan pending</p>
                <?php else: ?>
                    <table class="simple-table">
                        <tr>
                            <th>Tgl</th>
                            <th>Pemohon</th>
                            <th>Aset</th>
                            <th>Aksi</th>
                        </tr>
                        <?php foreach($recent_pending as $index => $p): ?>
                        <tr>
                            <td><?= date('d/m', strtotime($p['tgl_pinjam'])) ?></td>
                            <td><?= htmlspecialchars($p['pemohon']) ?></td>
                            <td><?= htmlspecialchars($p['nama_aset']) ?></td>
                            <td><a href="persetujuan.php" class="btn-action">Cek</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php if($pending_loans > 5): ?>
                        <div class="see-more"><a href="persetujuan.php">Lihat semua &rarr;</a></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="box-container">
                <h2>Aktivitas Terbaru</h2>
                <?php
                // Query asli kamu tidak saya ubah
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
                    <p class="empty">Tidak ada aktivitas</p>
                <?php else: ?>
                    <table class="simple-table">
                        <tr>
                            <th>Tgl</th>
                            <th>User</th>
                            <th>Aset</th>
                            <th>Status</th>
                        </tr>
                        <?php foreach($recent_activities as $a): ?>
                        <tr>
                            <td><?= date('d/m', strtotime($a['tgl_pinjam'])) ?></td>
                            <td><?= htmlspecialchars($a['pemohon']) ?></td>
                            <td><?= htmlspecialchars($a['nama_aset']) ?></td>
                            <td><span class="badge <?= $a['status_loan'] ?>"><?= $a['status_loan'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <div class="see-more"><a href="laporan.php">Lihat semua &rarr;</a></div>
                <?php endif; ?>
            </div>

        </div> <div class="box-container full-width">
            <h2>Status Aset</h2>
            <?php
            $asset_status = $conn->query("SELECT status_aset, COUNT(*) as jumlah FROM assets GROUP BY status_aset")->fetchAll();
            ?>
            <table class="simple-table">
                <tr><th>Status</th><th>Jumlah</th></tr>
                <?php foreach($asset_status as $status): ?>
                <tr>
                    <td><?= $status['status_aset'] ?></td>
                    <td><b><?= $status['jumlah'] ?></b></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</body>
</html>