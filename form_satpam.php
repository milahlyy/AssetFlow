<?php
require_once 'auth_check.php';
require_once 'database/db.php';
checkrole(['satpam']);

$id_loan = $_GET['id_loan'] ?? 0;
$pesan = '';
$error = '';

// 1. AMBIL DATA LAMA DULU (PENTING: Ini harus dilakukan SEBELUM proses POST)
// Agar kita bisa membandingkan input baru dengan data yang sudah ada di DB.
$stmt = $conn->prepare("SELECT l.*, a.nama_aset, a.plat_nomor, u.nama as peminjam 
                        FROM loans l 
                        JOIN assets a ON l.id_aset = a.id_aset 
                        JOIN users u ON l.id_user = u.id_user
                        WHERE l.id_loan = :id");
$stmt->bindParam(':id', $id_loan);
$stmt->execute();
$data = $stmt->fetch();

if (!$data) die("Data peminjaman tidak ditemukan.");

// 2. PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_jam_keluar = $_POST['jam_keluar'];
    $input_jam_masuk  = $_POST['jam_masuk'];
    $id_target        = $_POST['id_loan'];

    // --- LOGIKA PERBAIKAN ---
    // Cek Jam Keluar: Jika input user kosong TAPI di database sudah ada datanya, 
    // maka PAKAI DATA LAMA (jangan ditimpa jadi 00:00).
    if (empty($input_jam_keluar) && !empty($data['jam_keluar'])) {
        $final_jam_keluar = $data['jam_keluar'];
    } else {
        // Jika input ada isinya, pakai input. Jika kosong & db kosong, set NULL.
        $final_jam_keluar = !empty($input_jam_keluar) ? $input_jam_keluar : null;
    }

    // Cek Jam Masuk: Sama seperti di atas
    if (empty($input_jam_masuk) && !empty($data['jam_masuk'])) {
        $final_jam_masuk = $data['jam_masuk'];
    } else {
        $final_jam_masuk = !empty($input_jam_masuk) ? $input_jam_masuk : null;
    }

    // Logic Status: Jika jam keluar terisi, status 'on_loan'. 
    // (Opsional: Jika jam masuk terisi, bisa ubah status ke 'returned' atau biarkan on_loan)
    $sql_status = "";
    if (!empty($final_jam_keluar) && $data['status_loan'] == 'approved') {
        $sql_status = ", status_loan = 'on_loan'";
    }

    // Query Update
    $query = "UPDATE loans SET jam_keluar = :jk, jam_masuk = :jm $sql_status WHERE id_loan = :id";
    $update = $conn->prepare($query);
    $update->bindParam(':jk', $final_jam_keluar);
    $update->bindParam(':jm', $final_jam_masuk);
    $update->bindParam(':id', $id_target);

    if ($update->execute()) {
        $pesan = "Data berhasil disimpan!";
        // Refresh data $data agar tampilan form langsung terupdate dengan nilai baru
        $stmt->execute(); 
        $data = $stmt->fetch();
    } else {
        $error = "Gagal menyimpan data.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Form Satpam</title></head>
<body>
    <h2>Form Keamanan</h2>
    <a href="dashboard_operasional.php">&laquo; Kembali ke Dashboard</a>
    <hr>

    <?php if($pesan): ?>
        <p style="color: green; font-weight: bold;"><?php echo $pesan; ?></p>
    <?php endif; ?>
    <?php if($error): ?>
        <p style="color: red; font-weight: bold;"><?php echo $error; ?></p>
    <?php endif; ?>

    <table border="0" cellpadding="5">
        <tr>
            <td><strong>Kendaraan</strong></td>
            <td>: <?php echo htmlspecialchars($data['nama_aset']); ?> (<?php echo htmlspecialchars($data['plat_nomor']); ?>)</td>
        </tr>
        <tr>
            <td><strong>Peminjam</strong></td>
            <td>: <?php echo htmlspecialchars($data['peminjam']); ?></td>
        </tr>
    </table>
    <br>

    <form method="POST">
        <input type="hidden" name="id_loan" value="<?php echo $data['id_loan']; ?>">
        
        <label><strong>Jam Keluar (Waktu Berangkat):</strong></label><br>
        <input type="datetime-local" name="jam_keluar" 
               value="<?php echo !empty($data['jam_keluar']) ? date('Y-m-d\TH:i', strtotime($data['jam_keluar'])) : ''; ?>">
        <br><small><i>Biarkan jika tidak ingin mengubah jam keluar.</i></small>
        <br><br>

        <label><strong>Jam Masuk (Waktu Kembali):</strong></label><br>
        <input type="datetime-local" name="jam_masuk"
               value="<?php echo !empty($data['jam_masuk']) ? date('Y-m-d\TH:i', strtotime($data['jam_masuk'])) : ''; ?>">
        <br><br>

        <button type="submit">SIMPAN DATA</button>
    </form>
</body>
</html>