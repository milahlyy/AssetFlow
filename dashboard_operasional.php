<?php
require_once 'auth_check.php';
require_once 'database/db.php';

// Hanya Satpam dan Supir yang boleh masuk
checkrole(['satpam', 'supir']);

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];
$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Operasional</title>
    <link rel="stylesheet" href="css/dash_opera.css">
</head>
</head>
<body>

<div class="sidebar">
    <h2>AssetFlow</h2>
    <a href="dashboard_operasional.php" class="active">Dashboard</a>
    <a href="galeri_mobil.php">Galeri Mobil</a>
    <a href="riwayat.php">Riwayat Log</a>

    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main-content">

    <div class="page-header">
        <h1>Dashboard <?php echo ucfirst($role); ?></h1>
        <p>Halo, <strong><?php echo $nama; ?></strong></p>
        
    </div>

    <?php if ($role == 'satpam'): ?>
        
        <h3>Daftar Kendaraan Keluar/Masuk</h3>
        <p>Silakan update jam keluar jika mobil berangkat, atau jam masuk jika mobil kembali.</p>

        <?php
        // Logic Satpam: Ditambahkan l.keterangan di SELECT
        $query = "SELECT l.id_loan, l.status_loan, l.jam_keluar, l.jam_masuk, l.keterangan,
                         a.nama_aset, a.plat_nomor, u.nama as peminjam 
                  FROM loans l
                  JOIN assets a ON l.id_aset = a.id_aset
                  JOIN users u ON l.id_user = u.id_user
                  WHERE a.kategori = 'mobil' AND l.status_loan IN ('approved', 'on_loan')
                  ORDER BY l.status_loan ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $list = $stmt->fetchAll();
        ?>

        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <tr bgcolor="#f2f2f2">
                <th>Mobil</th>
                <th>Peminjam</th>
                <th>Keperluan</th> <th>Status</th>
                <th>Jam Keluar</th>
                <th>Jam Masuk</th>
                <th>Aksi</th>
            </tr>
            <?php foreach($list as $row): ?>
            <tr>
                <td>
                    <b><?php echo $row['nama_aset']; ?></b><br>
                    <?php echo $row['plat_nomor']; ?>
                </td>
                <td><?php echo $row['peminjam']; ?></td>
                <td><?php echo htmlspecialchars($row['keterangan']); ?></td> <td>
                    <?php 
                    if($row['status_loan'] == 'approved') echo "Belum Berangkat";
                    if($row['status_loan'] == 'on_loan') echo "Sedang Diluar";
                    ?>
                </td>
                <td><?php echo $row['jam_keluar'] ? $row['jam_keluar'] : '-'; ?></td>
                <td><?php echo $row['jam_masuk'] ? $row['jam_masuk'] : '-'; ?></td>
                <td>
                    <a href="form_satpam.php?id_loan=<?php echo $row['id_loan']; ?>">Update Log</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($role == 'supir'): ?>

        <h3>Tugas Perjalanan Saya</h3>
        <p>Silakan update KM Awal sebelum berangkat dan KM Akhir setelah pulang.</p>

        <?php
        // Logic Supir: Ditambahkan l.keterangan di SELECT
        $query = "SELECT l.id_loan, l.tgl_pinjam, l.km_awal, l.km_akhir, l.kondisi_mobil, l.keterangan,
                         a.nama_aset, a.plat_nomor, u.nama as peminjam
                  FROM loans l
                  JOIN assets a ON l.id_aset = a.id_aset
                  JOIN users u ON l.id_user = u.id_user
                  WHERE l.driver_id = :my_id AND l.status_loan IN ('approved', 'on_loan')
                  ORDER BY l.tgl_pinjam ASC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':my_id', $userId);
        $stmt->execute();
        $tasks = $stmt->fetchAll();
        ?>

        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <tr bgcolor="#f2f2f2">
                <th>Tanggal</th>
                <th>Mobil</th>
                <th>Penumpang</th>
                <th>Keperluan</th> <th>KM Awal</th>
                <th>KM Akhir</th>
                <th>Aksi</th>
            </tr>
            <?php foreach($tasks as $t): ?>
            <tr>
                <td><?php echo $t['tgl_pinjam']; ?></td>
                <td><?php echo $t['nama_aset']; ?> (<?php echo $t['plat_nomor']; ?>)</td>
                <td><?php echo $t['peminjam']; ?></td>
                <td><?php echo htmlspecialchars($t['keterangan']); ?></td> <td><?php echo $t['km_awal'] ? $t['km_awal'] : '-'; ?></td>
                <td><?php echo $t['km_akhir'] ? $t['km_akhir'] : '-'; ?></td>
                <td>
                    <a href="form_supir.php?id_loan=<?php echo $t['id_loan']; ?>">Update Laporan</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php endif; ?>

</body>
</html>