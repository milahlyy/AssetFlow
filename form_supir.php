<?php
require_once 'auth_check.php';
require_once 'database/db.php';
checkrole(['supir']);

$id_loan = filter_input(INPUT_GET, 'id_loan', FILTER_VALIDATE_INT) ?: 0;
$my_id = $_SESSION['user_id'];
$pesan = '';
$error = '';

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
    verify_csrf();

    $km_awal_raw = $_POST['km_awal'] ?? '';
    $km_akhir_raw = $_POST['km_akhir'] ?? '';
    $km_awal   = $km_awal_raw !== '' ? filter_var($km_awal_raw, FILTER_VALIDATE_INT) : null;
    $km_akhir  = $km_akhir_raw !== '' ? filter_var($km_akhir_raw, FILTER_VALIDATE_INT) : null;
    $kondisi   = trim($_POST['kondisi_mobil'] ?? '');
    $id_target = $data['id_loan'];

    if ($km_awal_raw !== '' && $km_awal === false) {
        $error = "KM awal harus berupa angka.";
    } elseif ($km_akhir_raw !== '' && $km_akhir === false) {
        $error = "KM akhir harus berupa angka.";
    } elseif (($km_awal !== null && $km_awal < 0) || ($km_akhir !== null && $km_akhir < 0)) {
        $error = "KM tidak boleh bernilai negatif.";
    } elseif ($km_awal !== null && $km_akhir !== null && $km_akhir < $km_awal) {
        $error = "KM akhir tidak boleh lebih kecil dari KM awal.";
    } else {
        $update = $conn->prepare("
            UPDATE loans 
            SET km_awal = :ka, km_akhir = :kk, kondisi_mobil = :km
            WHERE id_loan = :id
              AND driver_id = :did
              AND (
                  status_loan IN ('approved', 'on_loan')
                  OR (status_loan = 'returned' AND (km_awal IS NULL OR km_akhir IS NULL OR kondisi_mobil IS NULL))
              )
        ");
        $update->bindParam(':ka', $km_awal);
        $update->bindParam(':kk', $km_akhir);
        $update->bindParam(':km', $kondisi);
        $update->bindParam(':id', $id_target);
        $update->bindParam(':did', $my_id);

        if ($update->execute()) {
            $pesan = "Laporan perjalanan tersimpan!";
            $stmt->execute();
            $data = $stmt->fetch();
        }
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
        <div class="success"><?= e($pesan) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="success" style="background:#fdecea; color:#b42318;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="info">
        Mobil: <strong><?= e($data['nama_aset']) ?> - <?= e($data['plat_nomor']) ?></strong>
    </div>

    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id_loan" value="<?= e($data['id_loan']) ?>">

        <label>KM Awal (Sebelum Jalan)</label>
        <input type="number" name="km_awal" value="<?= e($data['km_awal']) ?>">

        <label>KM Akhir (Setelah Pulang)</label>
        <input type="number" name="km_akhir" value="<?= e($data['km_akhir']) ?>">

        <label>Kondisi Mobil</label>
        <textarea name="kondisi_mobil" rows="5"><?= e($data['kondisi_mobil']) ?></textarea>

        <button type="submit">SIMPAN LAPORAN</button>
    </form>

</div>

</body>
</html>
