<?php
// Dashboard Pegawai
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pegawai</title>
    <link rel="stylesheet" href="css/dash_pegawai.css">
</head>
<body>

<div class="sidebar">
    <h2>AssetFlow</h2>
    <a href="dashboard_pegawai.php">Dashboard</a>
    <a href="katalog_aset_pegawai.php">Katalog Aset</a>
    <a href="#" class="logout">Logout</a>
</div>

<div class="main-content">
    <div class="page-header">
        <h1>Dashboard Pegawai</h1>
        <p>Daftar aset yang sedang atau pernah Anda pinjam.</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Aset</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Laptop Lenovo</td>
                <td>01-12-2025</td>
                <td>05-12-2025</td>
                <td>Dipinjam</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Proyektor Epson</td>
                <td>20-11-2025</td>
                <td>22-11-2025</td>
                <td>Selesai</td>
            </tr>
        </tbody>
    </table>
</div>

</body>
</html>
