<?php
// Katalog Aset Pegawai
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Aset Pegawai</title>
    <link rel="stylesheet" href="css/katalog_pegawai.css">
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>AssetFlow</h2>
        <a href="dashboard_pegawai.php">Dashboard</a>
        <a href="katalog_aset_pegawai.php">Katalog Aset</a>
        <a href="#" class="logout">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="page-header">
            <h1>Katalog Aset</h1>
            <p>Daftar aset yang dapat dipinjam oleh pegawai</p>
        </div>

        <!-- TABEL ASET -->
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Aset</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Laptop Lenovo</td>
                    <td>Elektronik</td>
                    <td>Tersedia</td>
                    <td><button class="btn btn-pinjam">Pinjam</button></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Mobil Operasional</td>
                    <td>Kendaraan</td>
                    <td>Maintenance</td>
                    <td><button class="btn btn-disable">Tidak Tersedia</button></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Proyektor Epson</td>
                    <td>Elektronik</td>
                    <td>Tersedia</td>
                    <td><button class="btn btn-pinjam">Pinjam</button></td>
                </tr>
            </tbody>
        </table>

    </div>

</body>
</html>
