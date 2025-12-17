<?php
// File: detail_mobil.php
require_once 'auth_check.php';
require_once 'database/db.php';

checkrole(['satpam', 'supir']);

$id_aset = $_GET['id'] ?? 0;
$my_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 1. Ambil Info Mobil
$stmt = $conn->prepare("SELECT * FROM assets WHERE id_aset = :id");
$stmt->bindParam(':id', $id_aset);
$stmt->execute();
$mobil = $stmt->fetch();

if (!$mobil) die("Data mobil tidak ditemukan.");

// 2. Ambil Peminjaman Aktif
$queryLoan = "SELECT l.*, u.nama as peminjam, d.nama as supir 
              FROM loans l 
              JOIN users u ON l.id_user = u.id_user
              LEFT JOIN users d ON l.driver_id = d.id_user
              WHERE l.id_aset = :id 
              AND l.status_loan IN ('approved', 'on_loan') 
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
</head>
<body>
    <h2>Detail Kendaraan</h2>
    <a href="dashboard_operasional.php">&laquo; Kembali ke Dashboard</a> | 
    <a href="galeri_mobil.php">Lihat Galeri Lain</a>
    <hr>

    <table border="0" cellpadding="10" width="100%">
        <tr>
            <td width="300" valign="top">
                <?php if($mobil['gambar']): ?>
                    <img src="assets/img/<?php echo $mobil['gambar']; ?>" width="300" style="border:1px solid #ccc;">
                <?php else: ?>
                    <div style="width:300px; height:200px; border:1px solid #ccc; text-align:center; line-height:200px;">
                        Tidak ada gambar
                    </div>
                <?php endif; ?>
            </td>
            <td valign="top">
                <h3><?php echo htmlspecialchars($mobil['nama_aset']); ?></h3>
                <p>
                    <strong>Plat Nomor:</strong> <?php echo htmlspecialchars($mobil['plat_nomor']); ?><br>
                    <strong>Kategori:</strong> <?php echo strtoupper($mobil['kategori']); ?><br>
                    <strong>Status Aset:</strong> <?php echo strtoupper($mobil['status_aset']); ?>
                </p>
            </td>
        </tr>
    </table>
    <hr>

    <h3>Status Peminjaman Saat Ini</h3>
    
    <?php if ($active_loan): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <td bgcolor="#f9f9f9"><strong>Status</strong></td>
                <td>
                    <?php 
                    if($active_loan['status_loan'] == 'approved') echo "AKAN DIPAKAI (Approved)";
                    elseif($active_loan['status_loan'] == 'on_loan') echo "SEDANG DILUAR (On Loan)";
                    ?>
                </td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>Peminjam</strong></td>
                <td><?php echo htmlspecialchars($active_loan['peminjam']); ?></td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>Keperluan</strong></td> <td><?php echo htmlspecialchars($active_loan['keterangan']); ?></td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>Supir Bertugas</strong></td>
                <td><?php echo htmlspecialchars($active_loan['supir'] ?? 'Tanpa Supir'); ?></td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>Jadwal</strong></td>
                <td><?php echo $active_loan['tgl_pinjam']; ?> s/d <?php echo $active_loan['tgl_kembali']; ?></td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>Log Waktu (Satpam)</strong></td>
                <td>
                    Keluar: <?php echo $active_loan['jam_keluar'] ? $active_loan['jam_keluar'] : '-'; ?><br>
                    Masuk: <?php echo $active_loan['jam_masuk'] ? $active_loan['jam_masuk'] : '-'; ?>
                </td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>Log Fisik (Supir)</strong></td>
                <td>
                    KM Awal: <?php echo $active_loan['km_awal'] ? $active_loan['km_awal'] : '-'; ?><br>
                    KM Akhir: <?php echo $active_loan['km_akhir'] ? $active_loan['km_akhir'] : '-'; ?><br>
                    Kondisi: <?php echo $active_loan['kondisi_mobil'] ? $active_loan['kondisi_mobil'] : '-'; ?>
                </td>
            </tr>
        </table>
        <br>

        <?php if ($role == 'satpam'): ?>
            <p><strong>Aksi Satpam:</strong></p>
            <a href="form_satpam.php?id_loan=<?php echo $active_loan['id_loan']; ?>">
                <button style="padding: 10px 20px;">UPDATE JAM KELUAR/MASUK</button>
            </a>
        <?php elseif ($role == 'supir' && $active_loan['driver_id'] == $my_id): ?>
            <p><strong>Aksi Supir:</strong></p>
            <a href="form_supir.php?id_loan=<?php echo $active_loan['id_loan']; ?>">
                <button style="padding: 10px 20px;">UPDATE KM & KONDISI</button>
            </a>
        <?php endif; ?>

    <?php else: ?>
        <p><em>Saat ini mobil sedang parkir di kantor (Tidak ada peminjaman aktif).</em></p>
    <?php endif; ?>

</body>
</html>