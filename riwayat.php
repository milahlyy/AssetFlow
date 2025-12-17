<?php
// File: riwayat.php
require_once 'auth_check.php';
require_once 'database/db.php';
//cek role
checkrole(['satpam', 'supir']);

$role = $_SESSION['role'];
$my_id = $_SESSION['user_id'];

if ($role == 'satpam') {
    $query = "SELECT l.*, a.nama_aset, a.plat_nomor, u.nama as peminjam 
              FROM loans l 
              JOIN assets a ON l.id_aset = a.id_aset 
              JOIN users u ON l.id_user = u.id_user 
              WHERE a.kategori='mobil' 
              ORDER BY l.tgl_pinjam DESC";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT l.*, a.nama_aset, a.plat_nomor, u.nama as peminjam 
              FROM loans l 
              JOIN assets a ON l.id_aset = a.id_aset 
              JOIN users u ON l.id_user = u.id_user 
              WHERE l.driver_id = :did 
              ORDER BY l.tgl_pinjam DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':did', $my_id);
}

$stmt->execute();
$result = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Operasional</title>
    <link rel="stylesheet" href="css/riwayat.css">
</head>
<body>
    <div class="sidebar">
    <h2>AssetFlow</h2>

    <a href="dashboard_operasional.php" >Dashboard</a>
    <a href="galeri_mobil.php">Galeri Mobil</a>
    <a href="riwayat.php">Riwayat Log</a>

    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main-content">
        <h1>Riwayat Operasional</h1>
    
    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr bgcolor="#f2f2f2">
                <th>No</th>
                <th>Tanggal Pinjam</th>
                <th>Mobil</th>
                <th>Plat Nomor</th>
                <th>Peminjam</th>
                <th>Keperluan</th> <th>Jam Keluar</th>
                <th>Jam Masuk</th>
                <th>KM Awal</th>
                <th>KM Akhir</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (count($result) > 0):
                foreach($result as $row): 
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo $row['tgl_pinjam']; ?></td>
                <td><?php echo htmlspecialchars($row['nama_aset']); ?></td>
                <td><?php echo htmlspecialchars($row['plat_nomor']); ?></td>
                <td><?php echo htmlspecialchars($row['peminjam']); ?></td>
                <td><?php echo htmlspecialchars($row['keterangan']); ?></td> <td><?php echo $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-'; ?></td>
                <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                
                <td><?php echo $row['km_awal'] ? number_format($row['km_awal']) : '-'; ?></td>
                <td><?php echo $row['km_akhir'] ? number_format($row['km_akhir']) : '-'; ?></td>
                
                <td><?php echo $row['status_loan']; ?></td>
            </tr>
            <?php 
                endforeach; 
            else:
            ?>
            <tr>
                <td colspan="11" align="center">Belum ada riwayat data.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
