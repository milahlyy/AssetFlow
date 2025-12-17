<?php
require_once 'auth_check.php';
require_once 'database/db.php';
checkrole(['supir']);

$id_loan = $_GET['id_loan'] ?? 0;
$my_id = $_SESSION['user_id'];
$pesan = '';

// 1. Ambil Data Tugas
$stmt = $conn->prepare("SELECT l.*, a.nama_aset, a.plat_nomor 
                        FROM loans l JOIN assets a ON l.id_aset = a.id_aset
                        WHERE l.id_loan = :id AND l.driver_id = :did");
$stmt->bindParam(':id', $id_loan);
$stmt->bindParam(':did', $my_id);
$stmt->execute();
$data = $stmt->fetch();

if(!$data) die("Tugas tidak ditemukan atau bukan tugas Anda.");

// 2. Proses Simpan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $km_awal   = $_POST['km_awal'];
    $km_akhir  = $_POST['km_akhir'];
    $kondisi   = $_POST['kondisi_mobil'];
    $id_target = $_POST['id_loan'];

    $query = "UPDATE loans SET km_awal = :ka, km_akhir = :kk, kondisi_mobil = :km 
              WHERE id_loan = :id";
    $update = $conn->prepare($query);
    $update->bindParam(':ka', $km_awal);
    $update->bindParam(':kk', $km_akhir);
    $update->bindParam(':km', $kondisi);
    $update->bindParam(':id', $id_target);

    if ($update->execute()) {
        $pesan = "Laporan perjalanan tersimpan!";
        $stmt->execute(); $data = $stmt->fetch(); // Refresh data
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Form Supir</title></head>
<body>
    <h2>Laporan Perjalanan Supir</h2>
    <a href="dashboard_operasional.php">&laquo; Kembali ke Tugas Saya</a>
    <hr>

    <?php if($pesan): ?>
        <p style="color: green; font-weight: bold;"><?php echo $pesan; ?></p>
    <?php endif; ?>

    <p>Mobil: <strong><?php echo $data['nama_aset']; ?> - <?php echo $data['plat_nomor']; ?></strong></p>

    <form method="POST">
        <input type="hidden" name="id_loan" value="<?php echo $data['id_loan']; ?>">
        
        <label>KM Awal (Sebelum Jalan):</label><br>
        <input type="number" name="km_awal" value="<?php echo $data['km_awal']; ?>" placeholder="0"><br><br>

        <label>KM Akhir (Setelah Pulang):</label><br>
        <input type="number" name="km_akhir" value="<?php echo $data['km_akhir']; ?>" placeholder="0"><br><br>

        <label>Kondisi Mobil:</label><br>
        <textarea name="kondisi_mobil" rows="5" cols="40" placeholder="Catat jika ada baret, penyok, atau masalah mesin..."><?php echo $data['kondisi_mobil']; ?></textarea>
        <br><br>

        <button type="submit">SIMPAN LAPORAN</button>
    </form>
</body>
</html>