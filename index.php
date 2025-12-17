<?php
// Dashboard Pegawai - Fokus Status Aktif
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['pegawai']);

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Query: Aset yang sedang dipinjam (status on_loan)
$query_sedang_pinjam = "SELECT l.*, a.nama_aset, a.kategori, a.plat_nomor, a.gambar
                        FROM loans l
                        JOIN assets a ON l.id_aset = a.id_aset
                        WHERE l.id_user = :user_id AND l.status_loan = 'on_loan'
                        ORDER BY l.tgl_pinjam DESC";
$stmt_sedang = $conn->prepare($query_sedang_pinjam);
$stmt_sedang->bindParam(':user_id', $user_id);
$stmt_sedang->execute();
$sedang_pinjam = $stmt_sedang->fetchAll();

// Query: Menunggu persetujuan (status pending atau approved)
$query_menunggu = "SELECT l.*, a.nama_aset, a.kategori, a.plat_nomor, a.gambar
                   FROM loans l
                   JOIN assets a ON l.id_aset = a.id_aset
                   WHERE l.id_user = :user_id AND l.status_loan IN ('pending', 'approved')
                   ORDER BY l.tgl_pinjam DESC";
$stmt_menunggu = $conn->prepare($query_menunggu);
$stmt_menunggu->bindParam(':user_id', $user_id);
$stmt_menunggu->execute();
$menunggu = $stmt_menunggu->fetchAll();

// Handle pengembalian aset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kembalikan'])) {
    $id_loan = $_POST['id_loan'];
    
    // Validasi: Pastikan loan milik user ini dan status on_loan
    $check = $conn->prepare("SELECT id_loan FROM loans WHERE id_loan = :id AND id_user = :user_id AND status_loan = 'on_loan'");
    $check->bindParam(':id', $id_loan);
    $check->bindParam(':user_id', $user_id);
    $check->execute();
    
    if ($check->fetch()) {
        // Update status menjadi returned
        $update = $conn->prepare("UPDATE loans SET status_loan = 'returned' WHERE id_loan = :id");
        $update->bindParam(':id', $id_loan);
        $update->execute();
        
        header("Location: index.php?success=kembali");
        exit();
    } else {
        $error = "Gagal mengembalikan aset. Data tidak valid.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pegawai - AssetFlow</title>
    <link rel="stylesheet" href="css/style_pegawai.css">
</head>
<body>
    <div class="sidebar">
        <h2>AssetFlow</h2>
        <a href="index.php" class="active">Dashboard</a>
        <a href="katalog_aset.php">Katalog Aset</a>
        <a href="riwayat_saya.php">Riwayat Saya</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Dashboard Pegawai</h1>
        <p class="welcome">Selamat datang, <strong><?= htmlspecialchars($nama) ?></strong></p>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'kembali'): ?>
            <div class="alert alert-success">Aset berhasil dikembalikan!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Tabel: Sedang Saya Pinjam -->
        <div class="box-container">
            <h2>Sedang Saya Pinjam</h2>
            <?php if (empty($sedang_pinjam)): ?>
                <p class="empty">Tidak ada aset yang sedang dipinjam.</p>
            <?php else: ?>
                <table class="simple-table">
                    <tr>
                        <th>No</th>
                        <th>Aset</th>
                        <th>Kategori</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                    <?php $no = 1; foreach ($sedang_pinjam as $item): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                            <td>
                                <div class="asset-info-table">
                                    <?php if ($item['gambar']): ?>
                                        <img src="assets/img/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_aset']) ?>" class="asset-thumb">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($item['nama_aset']) ?></span>
                                    <?php if ($item['plat_nomor']): ?>
                                        <small>(<?= htmlspecialchars($item['plat_nomor']) ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <td><span class="badge badge-<?= $item['kategori'] ?>"><?= ucfirst($item['kategori']) ?></span></td>
                        <td><?= date('d/m/Y', strtotime($item['tgl_pinjam'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($item['tgl_kembali'])) ?></td>
                        <td><?= htmlspecialchars($item['keterangan']) ?></td>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin mengembalikan aset ini?');">
                                <input type="hidden" name="id_loan" value="<?= $item['id_loan'] ?>">
                                <button type="submit" name="kembalikan" class="btn btn-return">Kembalikan</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-- Tabel: Menunggu Persetujuan -->
        <div class="box-container">
            <h2>Menunggu Persetujuan</h2>
            <?php if (empty($menunggu)): ?>
                <p class="empty">Tidak ada pengajuan yang menunggu persetujuan.</p>
            <?php else: ?>
                <table class="simple-table">
                    <tr>
                        <th>No</th>
                        <th>Aset</th>
                        <th>Kategori</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                    </tr>
                    <?php $no = 1; foreach ($menunggu as $item): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                            <td>
                                <div class="asset-info-table">
                                    <?php if ($item['gambar']): ?>
                                        <img src="assets/img/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_aset']) ?>" class="asset-thumb">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($item['nama_aset']) ?></span>
                                    <?php if ($item['plat_nomor']): ?>
                                        <small>(<?= htmlspecialchars($item['plat_nomor']) ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <td><span class="badge badge-<?= $item['kategori'] ?>"><?= ucfirst($item['kategori']) ?></span></td>
                        <td><?= date('d/m/Y', strtotime($item['tgl_pinjam'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($item['tgl_kembali'])) ?></td>
                        <td><?= htmlspecialchars($item['keterangan']) ?></td>
                        <td>
                            <?php if ($item['status_loan'] == 'pending'): ?>
                                <span class="badge badge-pending">Menunggu</span>
                            <?php elseif ($item['status_loan'] == 'approved'): ?>
                                <span class="badge badge-approved">Disetujui</span>
                            <?php endif; ?>
                            <?php if ($item['alasan_penolakan']): ?>
                                <br><small style="color: #dc3545; display: block; margin-top: 5px;">Ditolak: <?= htmlspecialchars($item['alasan_penolakan']) ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <div class="quick-actions">
            <a href="katalog_aset.php" class="btn-primary">Lihat Katalog Aset</a>
            <a href="riwayat_saya.php" class="btn-secondary">Riwayat Peminjaman</a>
        </div>
    </div>
</body>
</html>
