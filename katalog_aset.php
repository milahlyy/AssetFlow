<?php
// Katalog Aset - Galeri semua aset dengan filter
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['pegawai']);

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Filter kategori
$filter_kategori = $_GET['kategori'] ?? 'all';

// Query aset dengan status ketersediaan
$query = "SELECT a.*, 
          (SELECT COUNT(*) FROM loans l 
           WHERE l.id_aset = a.id_aset 
           AND l.status_loan IN ('pending', 'approved', 'on_loan')
           AND CURDATE() BETWEEN l.tgl_pinjam AND l.tgl_kembali) as sedang_dipinjam
          FROM assets a
          WHERE a.status_aset = 'tersedia'";

if ($filter_kategori != 'all') {
    $query .= " AND a.kategori = :kategori";
}

$query .= " ORDER BY a.kategori, a.nama_aset";

$stmt = $conn->prepare($query);
if ($filter_kategori != 'all') {
    $stmt->bindParam(':kategori', $filter_kategori);
}
$stmt->execute();
$assets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Aset - AssetFlow</title>
    <link rel="stylesheet" href="css/style_pegawai.css">
</head>
<body>
    <div class="sidebar">
        <h2>AssetFlow</h2>
        <a href="index.php">Dashboard</a>
        <a href="katalog_aset.php" class="active">Katalog Aset</a>
        <a href="riwayat_saya.php">Riwayat Saya</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Katalog Aset</h1>
        </div>

        <!-- Filter Kategori -->
        <div class="filter-section">
            <a href="?kategori=all" class="filter-btn <?= $filter_kategori == 'all' ? 'active' : '' ?>">Semua</a>
            <a href="?kategori=mobil" class="filter-btn <?= $filter_kategori == 'mobil' ? 'active' : '' ?>">Mobil</a>
            <a href="?kategori=elektronik" class="filter-btn <?= $filter_kategori == 'elektronik' ? 'active' : '' ?>">Elektronik</a>
        </div>

        <!-- Grid Aset -->
        <div class="assets-grid">
            <?php if (empty($assets)): ?>
                <div class="empty-state">
                    <p>Tidak ada aset yang tersedia.</p>
                </div>
            <?php else: ?>
                <?php foreach ($assets as $asset): ?>
                    <div class="asset-card">
                        <div class="asset-image">
                            <?php if ($asset['gambar']): ?>
                                <img src="assets/img/<?= htmlspecialchars($asset['gambar']) ?>" alt="<?= htmlspecialchars($asset['nama_aset']) ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <span>No Image</span>
                                </div>
                            <?php endif; ?>
                            <div class="asset-status-badge">
                                <?php if ($asset['sedang_dipinjam'] > 0): ?>
                                    <span class="badge badge-dipinjam">Sedang Dipinjam</span>
                                <?php else: ?>
                                    <span class="badge badge-tersedia">Tersedia</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="asset-info">
                            <h3><?= htmlspecialchars($asset['nama_aset']) ?></h3>
                            <p class="asset-category">
                                <span class="badge badge-<?= $asset['kategori'] ?>"><?= ucfirst($asset['kategori']) ?></span>
                            </p>
                            <?php if ($asset['plat_nomor']): ?>
                                <p class="asset-detail">Plat: <strong><?= htmlspecialchars($asset['plat_nomor']) ?></strong></p>
                            <?php endif; ?>
                            <a href="form_peminjaman.php?id=<?= $asset['id_aset'] ?>" class="btn btn-primary btn-block">
                                Pinjam Sekarang
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

