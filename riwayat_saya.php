<?php
// Riwayat Peminjaman Pegawai - Arsip sejarah peminjaman
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['pegawai']);

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Filter status (opsional)
$filter_status = $_GET['status'] ?? 'all';

// Query riwayat peminjaman
$query = "SELECT l.*, a.nama_aset, a.kategori, a.plat_nomor, a.gambar
          FROM loans l
          JOIN assets a ON l.id_aset = a.id_aset
          WHERE l.id_user = :user_id";

// Filter berdasarkan status
if ($filter_status == 'selesai') {
    $query .= " AND l.status_loan IN ('returned', 'rejected')";
} elseif ($filter_status == 'ditolak') {
    $query .= " AND l.status_loan = 'rejected'";
} elseif ($filter_status == 'selesai_dipinjam') {
    $query .= " AND l.status_loan = 'returned'";
} elseif ($filter_status != 'all') {
    $query .= " AND l.status_loan = :status";
}

$query .= " ORDER BY l.tgl_pinjam DESC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
if ($filter_status != 'all' && $filter_status != 'selesai' && $filter_status != 'ditolak' && $filter_status != 'selesai_dipinjam') {
    $stmt->bindParam(':status', $filter_status);
}
$stmt->execute();
$riwayat = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - AssetFlow</title>
    <link rel="stylesheet" href="css/style_pegawai.css">
</head>
<body>
    <div class="sidebar">
        <h2>AssetFlow</h2>
        <a href="index.php">Dashboard</a>
        <a href="katalog_aset.php">Katalog Aset</a>
        <a href="riwayat_saya.php" class="active">Riwayat Saya</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Riwayat Peminjaman Saya</h1>
        </div>

        <!-- Filter Status -->
        <div class="filter-section">
            <a href="?status=all" class="filter-btn <?= $filter_status == 'all' ? 'active' : '' ?>">Semua</a>
            <a href="?status=selesai" class="filter-btn <?= $filter_status == 'selesai' ? 'active' : '' ?>">Selesai</a>
            <a href="?status=selesai_dipinjam" class="filter-btn <?= $filter_status == 'selesai_dipinjam' ? 'active' : '' ?>">Selesai Dipinjam</a>
            <a href="?status=ditolak" class="filter-btn <?= $filter_status == 'ditolak' ? 'active' : '' ?>">Ditolak</a>
        </div>

        <!-- Tabel Riwayat -->
        <div class="card">
            <?php if (empty($riwayat)): ?>
                <div class="empty-state">
                    <p>Tidak ada riwayat peminjaman.</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Aset</th>
                            <th>Kategori</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($riwayat as $item): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <div class="asset-info">
                                    <?php if ($item['gambar']): ?>
                                        <img src="assets/img/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_aset']) ?>" class="asset-thumb">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($item['nama_aset']) ?></span>
                                    <?php if ($item['plat_nomor']): ?>
                                        <br><small>(<?= htmlspecialchars($item['plat_nomor']) ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><span class="badge badge-<?= $item['kategori'] ?>"><?= ucfirst($item['kategori']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($item['tgl_pinjam'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($item['tgl_kembali'])) ?></td>
                            <td><?= htmlspecialchars($item['keterangan']) ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                $status_text = '';
                                switch($item['status_loan']) {
                                    case 'pending':
                                        $status_class = 'pending';
                                        $status_text = 'Menunggu';
                                        break;
                                    case 'approved':
                                        $status_class = 'approved';
                                        $status_text = 'Disetujui';
                                        break;
                                    case 'rejected':
                                        $status_class = 'rejected';
                                        $status_text = 'Ditolak';
                                        break;
                                    case 'on_loan':
                                        $status_class = 'on_loan';
                                        $status_text = 'Dipinjam';
                                        break;
                                    case 'returned':
                                        $status_class = 'returned';
                                        $status_text = 'Dikembalikan';
                                        break;
                                }
                                ?>
                                <span class="badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                                <?php if ($item['alasan_penolakan']): ?>
                                    <br><small style="color: #dc3545; display: block; margin-top: 5px;">
                                        <strong>Alasan:</strong> <?= htmlspecialchars($item['alasan_penolakan']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['kategori'] == 'mobil'): ?>
                                    <?php if ($item['jam_keluar']): ?>
                                        <small>Keluar: <?= date('d/m/Y H:i', strtotime($item['jam_keluar'])) ?></small><br>
                                    <?php endif; ?>
                                    <?php if ($item['jam_masuk']): ?>
                                        <small>Masuk: <?= date('d/m/Y H:i', strtotime($item['jam_masuk'])) ?></small><br>
                                    <?php endif; ?>
                                    <?php if ($item['km_awal']): ?>
                                        <small>KM Awal: <?= number_format($item['km_awal']) ?></small><br>
                                    <?php endif; ?>
                                    <?php if ($item['km_akhir']): ?>
                                        <small>KM Akhir: <?= number_format($item['km_akhir']) ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="quick-actions">
            <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
            <a href="katalog_aset.php" class="btn btn-primary">Pinjam Aset Baru</a>
        </div>
    </div>
</body>
</html>

