<?php
// Dashboard Pegawai - Fokus Status Aktif
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['pegawai']);

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Query: Aset yang sedang dipinjam (status on_loan ATAU approved yang tanggal pinjam sudah tiba)
// Ambil semua field yang diperlukan untuk cek log (jam_keluar, jam_masuk, km_awal, km_akhir, kondisi_mobil, driver_id)
$query_sedang_pinjam = "SELECT l.*, a.nama_aset, a.kategori, a.plat_nomor, a.gambar
                        FROM loans l
                        JOIN assets a ON l.id_aset = a.id_aset
                        WHERE l.id_user = :user_id 
                        AND (
                            l.status_loan = 'on_loan' 
                            OR (l.status_loan = 'approved' AND l.tgl_pinjam <= CURDATE())
                        )
                        ORDER BY l.tgl_pinjam DESC";
$stmt_sedang = $conn->prepare($query_sedang_pinjam);
$stmt_sedang->bindParam(':user_id', $user_id);
$stmt_sedang->execute();
$sedang_pinjam = $stmt_sedang->fetchAll();

// Query: Menunggu persetujuan (status pending ATAU approved yang tanggal pinjam belum tiba)
$query_menunggu = "SELECT l.*, a.nama_aset, a.kategori, a.plat_nomor, a.gambar
                   FROM loans l
                   JOIN assets a ON l.id_aset = a.id_aset
                   WHERE l.id_user = :user_id 
                   AND (
                       l.status_loan = 'pending' 
                       OR (l.status_loan = 'approved' AND l.tgl_pinjam > CURDATE())
                   )
                   ORDER BY l.tgl_pinjam DESC";
$stmt_menunggu = $conn->prepare($query_menunggu);
$stmt_menunggu->bindParam(':user_id', $user_id);
$stmt_menunggu->execute();
$menunggu = $stmt_menunggu->fetchAll();

// Handle pengembalian aset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kembalikan'])) {
    $id_loan = $_POST['id_loan'];
    
    // Ambil data loan beserta kategori asset
    $check = $conn->prepare("
        SELECT l.*, a.kategori 
        FROM loans l 
        JOIN assets a ON l.id_aset = a.id_aset 
        WHERE l.id_loan = :id 
        AND l.id_user = :user_id 
        AND (l.status_loan = 'on_loan' OR (l.status_loan = 'approved' AND l.tgl_pinjam <= CURDATE()))
    ");
    $check->bindParam(':id', $id_loan);
    $check->bindParam(':user_id', $user_id);
    $check->execute();
    $loan_data = $check->fetch();
    
    if ($loan_data) {
        // Jika asset adalah mobil, cek kelengkapan log
        if ($loan_data['kategori'] == 'mobil') {
            $log_lengkap = true;
            $log_kurang = [];
            
            // Cek log satpam
            if (empty($loan_data['jam_keluar'])) {
                $log_lengkap = false;
                $log_kurang[] = "Jam Keluar (Satpam)";
            }
            if (empty($loan_data['jam_masuk'])) {
                $log_lengkap = false;
                $log_kurang[] = "Jam Masuk (Satpam)";
            }
            
            // Cek log supir (jika ada driver_id, berarti perlu supir)
            if (!empty($loan_data['driver_id'])) {
                if (empty($loan_data['km_awal'])) {
                    $log_lengkap = false;
                    $log_kurang[] = "KM Awal (Supir)";
                }
                if (empty($loan_data['km_akhir'])) {
                    $log_lengkap = false;
                    $log_kurang[] = "KM Akhir (Supir)";
                }
                if (empty($loan_data['kondisi_mobil'])) {
                    $log_lengkap = false;
                    $log_kurang[] = "Kondisi Mobil (Supir)";
                }
            }
            
            // Jika log belum lengkap, tampilkan error
            if (!$log_lengkap) {
                $error = "Tidak dapat mengembalikan aset. Log belum lengkap. Data yang masih kurang: " . implode(", ", $log_kurang);
            } else {
                // Semua log sudah lengkap, update status menjadi returned
                $update = $conn->prepare("UPDATE loans SET status_loan = 'returned' WHERE id_loan = :id");
                $update->bindParam(':id', $id_loan);
                $update->execute();
                
                header("Location: index.php?success=kembali");
                exit();
            }
        } else {
            // Bukan mobil, langsung bisa dikembalikan tanpa perlu log
            $update = $conn->prepare("UPDATE loans SET status_loan = 'returned' WHERE id_loan = :id");
            $update->bindParam(':id', $id_loan);
            $update->execute();
            
            header("Location: index.php?success=kembali");
            exit();
        }
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
        <a href="index.php">Dashboard</a>
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
                            <?php 
                            // Cek jika mobil dan log belum lengkap
                            $log_belum_lengkap = false;
                            $log_info = [];
                            if ($item['kategori'] == 'mobil') {
                                if (empty($item['jam_keluar'])) {
                                    $log_belum_lengkap = true;
                                    $log_info[] = "Jam Keluar";
                                }
                                if (empty($item['jam_masuk'])) {
                                    $log_belum_lengkap = true;
                                    $log_info[] = "Jam Masuk";
                                }
                                if (!empty($item['driver_id'])) {
                                    if (empty($item['km_awal'])) {
                                        $log_belum_lengkap = true;
                                        $log_info[] = "KM Awal";
                                    }
                                    if (empty($item['km_akhir'])) {
                                        $log_belum_lengkap = true;
                                        $log_info[] = "KM Akhir";
                                    }
                                    if (empty($item['kondisi_mobil'])) {
                                        $log_belum_lengkap = true;
                                        $log_info[] = "Kondisi";
                                    }
                                }
                            }
                            ?>
                            <?php if ($log_belum_lengkap): ?>
                                <small style="color: #FF8C00; display: block; margin-bottom: 5px;">
                                    <strong>Log belum lengkap:</strong> <?= implode(", ", $log_info) ?>
                                </small>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin mengembalikan aset ini?');">
                                <input type="hidden" name="id_loan" value="<?= $item['id_loan'] ?>">
                                <button type="submit" name="kembalikan" class="btn btn-return" <?= $log_belum_lengkap ? 'title="Log belum lengkap, pengembalian mungkin gagal"' : '' ?>>Kembalikan</button>
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
    </div>
</body>
</html>
