<?php
session_start();
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['hrga']);

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_loan = $_POST['id_loan'];
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        $driver_id = $_POST['driver_id'] ?: null;
        $stmt = $conn->prepare("UPDATE loans SET status_loan='approved', driver_id=? WHERE id_loan=?");
        $stmt->execute([$driver_id, $id_loan]);
        $message = "Peminjaman disetujui";
    } else {
        $alasan = $_POST['alasan_penolakan'];
        $stmt = $conn->prepare("UPDATE loans SET status_loan='rejected', alasan_penolakan=? WHERE id_loan=?");
        $stmt->execute([$alasan, $id_loan]);
        $message = "Peminjaman ditolak";
    }
}

// Get pending loans
$loans = $conn->query("
    SELECT l.*, u.nama as pemohon, a.nama_aset, a.kategori 
    FROM loans l 
    JOIN users u ON l.id_user = u.id_user 
    JOIN assets a ON l.id_aset = a.id_aset 
    WHERE l.status_loan = 'pending' 
    ORDER BY l.tgl_pinjam ASC
")->fetchAll();

// Get drivers
$drivers = $conn->query("SELECT * FROM users WHERE role='supir'")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Persetujuan Peminjaman</title>
</head>
<body>
    <h1>Persetujuan Peminjaman</h1>
    
    <?php if(isset($message)) echo "<p>$message</p>"; ?>
    
    <?php if(empty($loans)): ?>
        <p>Tidak ada permohonan pending</p>
    <?php else: ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Pemohon</th>
                <th>Aset</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
            
            <?php foreach($loans as $index => $loan): ?>
            <tr>
                <td><?= $index+1 ?></td>
                <td><?= date('d/m/Y', strtotime($loan['tgl_pinjam'])) ?></td>
                <td><?= htmlspecialchars($loan['pemohon']) ?></td>
                <td><?= htmlspecialchars($loan['nama_aset']) ?></td>
                <td><?= $loan['kategori'] ?></td>
                <td><?= substr($loan['keterangan'], 0, 50) ?>...</td>
                <td>
                    <!-- Approve Form -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id_loan" value="<?= $loan['id_loan'] ?>">
                        <input type="hidden" name="action" value="approve">
                        
                        <?php if($loan['kategori'] == 'mobil'): ?>
                            Driver: 
                            <select name="driver_id">
                                <option value="">Tanpa Driver</option>
                                <?php foreach($drivers as $driver): ?>
                                    <option value="<?= $driver['id_user'] ?>"><?= $driver['nama'] ?></option>
                                <?php endforeach; ?>
                            </select><br>
                        <?php endif; ?>
                        
                        <button type="submit">Approve</button>
                    </form>
                    
                    <!-- Reject Form -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id_loan" value="<?= $loan['id_loan'] ?>">
                        <input type="hidden" name="action" value="reject">
                        Alasan: <input type="text" name="alasan_penolakan" required>
                        <button type="submit">Reject</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    
    <p><a href="admin_dashboard.php">Kembali ke Dashboard</a></p>
</body>
</html>