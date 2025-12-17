<?php
require_once 'auth_check.php';
require_once 'database/db.php';
checkrole(['supir']);

$id_loan = $_GET['id_loan'] ?? 0;
$my_id = $_SESSION['user_id'];
$pesan = '';

// Ambil data tugas supir
// Supir bisa mengisi log selama status 'approved', 'on_loan', atau 'returned' (jika data belum lengkap)
$stmt = $conn->prepare("
    SELECT l.*, a.nama_aset, a.plat_nomor
    FROM loans l
    JOIN assets a ON l.id_aset = a.id_aset
    WHERE l.id_loan = :id AND l.driver_id = :did
      AND (
          l.status_loan IN ('approved', 'on_loan')
          OR (l.status_loan = 'returned' AND (l.km_awal IS NULL OR l.km_akhir IS NULL OR l.kondisi_mobil IS NULL))
      )
");
$stmt->bindParam(':id', $id_loan);
$stmt->bindParam(':did', $my_id);
$stmt->execute();
$data = $stmt->fetch();

if (!$data) {
    header("Location: dashboard_operasional.php?error=tugas_tidak_ditemukan");
    exit();
}

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $km_awal   = $_POST['km_awal'];
    $km_akhir  = $_POST['km_akhir'];
    $kondisi   = $_POST['kondisi_mobil'];
    $id_target = $_POST['id_loan'];

    $update = $conn->prepare("
        UPDATE loans 
        SET km_awal = :ka, km_akhir = :kk, kondisi_mobil = :km
        WHERE id_loan = :id
    ");
    $update->bindParam(':ka', $km_awal);
    $update->bindParam(':kk', $km_akhir);
    $update->bindParam(':km', $kondisi);
    $update->bindParam(':id', $id_target);

    if ($update->execute()) {
        $pesan = "Laporan perjalanan tersimpan!";
        $stmt->execute();
        $data = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perjalanan Supir</title>
    <link rel="stylesheet" href="css/form_supir.css">
</head>
<body>

<h2>Laporan Perjalanan Supir</h2>
<a href="dashboard_operasional.php" class="back-link">&laquo; Kembali ke Tugas Saya</a>

<div class="card">

    <?php if ($pesan): ?>
        <div class="success"><?= $pesan ?></div>
    <?php endif; ?>

    <div class="info">
        Mobil: <strong><?= htmlspecialchars($data['nama_aset']) ?> - <?= htmlspecialchars($data['plat_nomor']) ?></strong>
    </div>

    <form method="POST">
        <input type="hidden" name="id_loan" value="<?= $data['id_loan'] ?>">

        <label>KM Awal (Sebelum Jalan)</label>
        <input type="number" name="km_awal" value="<?= $data['km_awal'] ?>">

        <label>KM Akhir (Setelah Pulang)</label>
        <input type="number" name="km_akhir" value="<?= $data['km_akhir'] ?>">

        <label>Kondisi Mobil</label>
        <textarea name="kondisi_mobil" rows="5"><?= $data['kondisi_mobil'] ?></textarea>

        <button type="submit">SIMPAN LAPORAN</button>
    </form>

</div>

</body>
</html>
