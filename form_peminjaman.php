<?php
// Form Peminjaman - Booking dengan validasi tanggal
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['pegawai']);

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Ambil id_aset dari URL
$id_aset = $_GET['id'] ?? null;

if (!$id_aset) {
    header("Location: katalog_aset.php");
    exit();
}

// Ambil data aset
$stmt_aset = $conn->prepare("SELECT * FROM assets WHERE id_aset = :id");
$stmt_aset->bindParam(':id', $id_aset);
$stmt_aset->execute();
$aset = $stmt_aset->fetch();

if (!$aset) {
    header("Location: katalog_aset.php?error=aset_tidak_ditemukan");
    exit();
}

$error = '';
$success = '';

// Proses submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tgl_pinjam = $_POST['tgl_pinjam'] ?? '';
    $tgl_kembali = $_POST['tgl_kembali'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');
    $butuh_supir = isset($_POST['butuh_supir']) && $_POST['butuh_supir'] == '1';
    
    // Jika kategori mobil dan user menceklis butuh supir, tambahkan "(Request Supir)" ke keterangan
    if ($aset['kategori'] == 'mobil' && $butuh_supir) {
        $keterangan .= ' (Request Supir)';
    }

    // Validasi input
    if (empty($tgl_pinjam) || empty($tgl_kembali) || empty($keterangan)) {
        $error = "Semua field harus diisi!";
    } elseif ($tgl_pinjam > $tgl_kembali) {
        $error = "Tanggal kembali harus setelah tanggal pinjam!";
    } elseif ($tgl_pinjam < date('Y-m-d')) {
        $error = "Tanggal pinjam tidak boleh di masa lalu!";
    } else {
        // Validasi bentrok jadwal
        // Cek apakah ada peminjaman lain yang bentrok dengan tanggal yang diminta
        // Dua rentang tanggal bentrok jika: (start1 <= end2) AND (start2 <= end1)
        $check_conflict = $conn->prepare("
            SELECT COUNT(*) as jumlah 
            FROM loans 
            WHERE id_aset = :id_aset 
            AND status_loan IN ('pending', 'approved', 'on_loan')
            AND (
                (tgl_pinjam <= :tgl_kembali AND tgl_kembali >= :tgl_pinjam)
            )
        ");
        $check_conflict->bindParam(':id_aset', $id_aset);
        $check_conflict->bindParam(':tgl_pinjam', $tgl_pinjam);
        $check_conflict->bindParam(':tgl_kembali', $tgl_kembali);
        $check_conflict->execute();
        $conflict = $check_conflict->fetch();

        if ($conflict['jumlah'] > 0) {
            $error = "Aset ini sudah dipinjam pada tanggal yang diminta. Silakan pilih tanggal lain.";
        } else {
            // Cek status aset fisik
            if ($aset['status_aset'] != 'tersedia') {
                $error = "Aset sedang tidak tersedia (Status: " . ucfirst($aset['status_aset']) . ")";
            } else {
                // Simpan peminjaman
                try {
                    $insert = $conn->prepare("
                        INSERT INTO loans (id_user, id_aset, tgl_pinjam, tgl_kembali, keterangan, status_loan) 
                        VALUES (:id_user, :id_aset, :tgl_pinjam, :tgl_kembali, :keterangan, 'pending')
                    ");
                    $insert->bindParam(':id_user', $user_id);
                    $insert->bindParam(':id_aset', $id_aset);
                    $insert->bindParam(':tgl_pinjam', $tgl_pinjam);
                    $insert->bindParam(':tgl_kembali', $tgl_kembali);
                    $insert->bindParam(':keterangan', $keterangan);
                    $insert->execute();

                    header("Location: index.php?success=booking");
                    exit();
                } catch (PDOException $e) {
                    error_log("Booking error: " . $e->getMessage());
                    $error = "Terjadi kesalahan saat menyimpan data. Silakan coba lagi.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman - AssetFlow</title>
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
        <div class="header">
            <h1>Form Peminjaman Aset</h1>
            <p class="welcome">Selamat datang, <strong><?= htmlspecialchars($nama) ?></strong></p>
        </div>

        <div class="card">
            <a href="katalog_aset.php" class="btn-back">&laquo; Kembali ke Katalog</a>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Info Aset -->
            <div class="asset-preview">
                <h3>Detail Aset</h3>
                <div class="asset-detail-box">
                    <?php if ($aset['gambar']): ?>
                        <img src="assets/img/<?= htmlspecialchars($aset['gambar']) ?>" alt="<?= htmlspecialchars($aset['nama_aset']) ?>" class="preview-image">
                    <?php endif; ?>
                    <div>
                        <h4><?= htmlspecialchars($aset['nama_aset']) ?></h4>
                        <p><strong>Kategori:</strong> <?= ucfirst($aset['kategori']) ?></p>
                        <?php if ($aset['plat_nomor']): ?>
                            <p><strong>Plat Nomor:</strong> <?= htmlspecialchars($aset['plat_nomor']) ?></p>
                        <?php endif; ?>
                        <p><strong>Status:</strong> 
                            <span class="badge badge-<?= $aset['status_aset'] == 'tersedia' ? 'tersedia' : 'maintenance' ?>">
                                <?= ucfirst($aset['status_aset']) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Peminjaman -->
            <form method="POST" class="booking-form">
                <h3>Formulir Peminjaman</h3>
                
                <div class="form-group">
                    <label for="tgl_pinjam">Tanggal Pinjam *</label>
                    <input type="date" id="tgl_pinjam" name="tgl_pinjam" 
                           value="<?= htmlspecialchars($_POST['tgl_pinjam'] ?? '') ?>" 
                           min="<?= date('Y-m-d') ?>" required>
                    <small>Pilih tanggal mulai peminjaman</small>
                </div>

                <div class="form-group">
                    <label for="tgl_kembali">Tanggal Kembali *</label>
                    <input type="date" id="tgl_kembali" name="tgl_kembali" 
                           value="<?= htmlspecialchars($_POST['tgl_kembali'] ?? '') ?>" 
                           min="<?= date('Y-m-d') ?>" required>
                    <small>Pilih tanggal pengembalian aset</small>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan / Keperluan *</label>
                    <textarea id="keterangan" name="keterangan" rows="4" 
                              placeholder="Jelaskan keperluan peminjaman aset ini..." required><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
                    <small>Contoh: Meeting dengan klien, Survey lapangan, dll</small>
                </div>

                <?php if ($aset['kategori'] == 'mobil'): ?>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="butuh_supir" name="butuh_supir" value="1" <?= isset($_POST['butuh_supir']) ? 'checked' : '' ?>>
                        <label for="butuh_supir" style="margin-bottom: 0; font-weight: normal;">Apakah membutuhkan Supir?</label>
                    </div>
                    <small>Centang jika Anda membutuhkan supir untuk peminjaman mobil ini</small>
                </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                    <a href="katalog_aset.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validasi client-side: Tanggal kembali harus setelah tanggal pinjam
        document.getElementById('tgl_pinjam').addEventListener('change', function() {
            const tglKembali = document.getElementById('tgl_kembali');
            tglKembali.min = this.value;
            if (tglKembali.value && tglKembali.value < this.value) {
                tglKembali.value = this.value;
            }
        });
    </script>
</body>
</html>

