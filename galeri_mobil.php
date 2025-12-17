<?php
require_once 'auth_check.php';
require_once 'database/db.php';

// Pastikan hanya Satpam dan Supir yang bisa akses
checkrole(['satpam', 'supir']);
$role = $_SESSION['role'];


// Ambil semua data mobil
$list = $conn->query("SELECT * FROM assets WHERE kategori='mobil'")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Galeri Mobil</title>
    <link rel="stylesheet" href="css/galeri_mobil.css">

    <style>
        /* Style agar link nama mobil terlihat jelas */
        a.nama-mobil {
            color: #007bff;
            text-decoration: none;
        }
        a.nama-mobil:hover {
            text-decoration: underline;
            color: #0056b3;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2><?php echo strtoupper($role); ?></h2>

    <a href="dashboard_operasional.php">Dashboard</a>
    <a href="galeri_mobil.php">Galeri Mobil</a>
    <a href="riwayat.php">Riwayat Log</a>

    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main-content">

    <div class="page-header">
        <h1>Dashboard <?php echo ucfirst($role); ?></h1>
        <h2>Galeri Mobil Kantor</h2>
    </div>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr bgcolor="#f2f2f2">
                <th>No</th>
                <th>Gambar</th>
                <th>Nama Mobil & Plat</th>
                <th>Status Fisik</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; foreach($list as $m): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td align="center">
                    <?php if($m['gambar']): ?>
                        <img src="assets/img/<?php echo $m['gambar']; ?>" width="100" style="border:1px solid #ddd; padding:3px;">
                    <?php else: ?>
                        <span style="color:gray; font-size:12px;">(Tidak ada foto)</span>
                    <?php endif; ?>
                </td>
                <td>
                    <b>
                        <a href="detail_mobil.php?id=<?php echo $m['id_aset']; ?>" class="nama-mobil" title="Klik untuk lihat detail">
                            <?php echo $m['nama_aset']; ?>
                        </a>
                    </b>
                    <br>
                    <small><?php echo $m['plat_nomor']; ?></small>
                </td>
                <td>
                    <?php 
                    // Warna status biar gampang dilihat
                    $warna = 'black';
                    if($m['status_aset'] == 'tersedia') $warna = 'green';
                    if($m['status_aset'] == 'rusak') $warna = 'red';
                    if($m['status_aset'] == 'maintenance') $warna = 'orange';
                    ?>
                    <strong style="color: <?php echo $warna; ?>">
                        <?php echo strtoupper($m['status_aset']); ?>
                    </strong>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
