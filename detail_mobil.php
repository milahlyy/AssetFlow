<?php
// File: detail_mobil.php
require_once 'auth_check.php';
require_once 'database/db.php';

checkrole(['satpam', 'supir']);

$id_aset = $_GET['id'] ?? 0;
$my_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil info mobil
$stmt = $conn->prepare("SELECT * FROM assets WHERE id_aset = :id");
$stmt->bindParam(':id', $id_aset);
$stmt->execute();
$mobil = $stmt->fetch();

if (!$mobil) die("Data mobil tidak ditemukan.");

// Ambil peminjaman aktif
$queryLoan = "SELECT l.*, u.nama AS peminjam, d.nama AS supir
              FROM loans l
              JOIN users u ON l.id_user = u.id_user
              LEFT JOIN users d ON l.driver_id = d.id_user
              WHERE l.id_aset = :id
              AND l.status_loan IN ('approved','on_loan')
              ORDER BY l.id_loan DESC LIMIT 1";

$stmtLoan = $conn->prepare($queryLoan);
$stmtLoan->bindParam(':id', $id_aset);
$stmtLoan->execute();
$active_loan = $stmtLoan->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Mobil</title>
    <link rel="stylesheet" href="css/detail_mobil.css">
</head>
<body>

<h2>Detail Kendaraan</h2>

<a href="dashboard_operasional.php">&laquo; Kembali ke Dashboard</a> |
<a href="galeri_mobil.php">Lihat Galeri Lain</a>

<hr>

<div class="detail-wrapper">

    <!-- KIRI : INFO MOBIL -->
    <div class="mobil-card">
        <?php if ($mobil['gambar']): ?>
            <img src="assets/img/<?php echo $mobil['gambar']; ?>">
        <?php else: ?>
            <div class="no-image">Tidak ada gambar</div>
        <?php endif; ?>

        <h3><?php echo htmlspecialchars($mobil['nama_aset']); ?></h3>

        <p>
            <strong>Plat Nomor:</strong> <?php echo htmlspecialchars($mobil['plat_nomor']); ?><br>
            <strong>Kategori:</strong> <?php echo strtoupper($mobil['kategori']); ?><br>
            <strong>Status Aset:</strong> <?php echo strtoupper($mobil['status_aset']); ?>
        </p>
    </div>

    <!-- KANAN : STATUS PEMINJAMAN -->
    <div class="status-card">
        <h3>Status Peminjaman</h3>

        <?php if ($active_loan): ?>
            <table>
                <tr>
                    <td>Status</td>
                    <td>
                        <?php
                        if ($active_loan['status_loan'] == 'approved') echo "AKAN DIPAKAI";
                        else echo "SEDANG DIGUNAKAN";
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Peminjam</td>
                    <td><?php echo htmlspecialchars($active_loan['peminjam']); ?></td>
                </tr>
                <tr>
                    <td>Keperluan</td>
                    <td><?php echo htmlspecialchars($active_loan['keterangan']); ?></td>
                </tr>
                <tr>
                    <td>Supir</td>
                    <td><?php echo htmlspecialchars($active_loan['supir'] ?? 'Tanpa Supir'); ?></td>
                </tr>
                <tr>
                    <td>Jadwal</td>
                    <td><?php echo $active_loan['tgl_pinjam']; ?> s/d <?php echo $active_loan['tgl_kembali']; ?></td>
                </tr>
            </table>

            <?php if ($role == 'satpam'): ?>
                <a href="form_satpam.php?id_loan=<?php echo $active_loan['id_loan']; ?>">
                    <button>UPDATE JAM KELUAR / MASUK</button>
                </a>
            <?php elseif ($role == 'supir' && $active_loan['driver_id'] == $my_id): ?>
                <a href="form_supir.php?id_loan=<?php echo $active_loan['id_loan']; ?>">
                    <button>UPDATE KM & KONDISI</button>
                </a>
            <?php endif; ?>

        <?php else: ?>
            <em>Mobil sedang parkir di kantor.</em>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
