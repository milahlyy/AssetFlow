<?php
require_once 'auth_check.php';
require_once 'database/db.php';
checkrole(['satpam']);

$id_loan = filter_input(INPUT_GET, 'id_loan', FILTER_VALIDATE_INT) ?: 0;
$pesan = '';
$error = '';

function normalize_datetime_input($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    foreach (['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
        $date = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();
        $has_errors = is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

        if ($date && !$has_errors) {
            return $date->format('Y-m-d H:i:s');
        }
    }

    return false;
}

// ambil data lama
$stmt = $conn->prepare("SELECT l.*, a.nama_aset, a.plat_nomor, u.nama as peminjam 
                        FROM loans l 
                        JOIN assets a ON l.id_aset = a.id_aset 
                        JOIN users u ON l.id_user = u.id_user
                        WHERE l.id_loan = :id
                          AND a.kategori = 'mobil'
                          AND (
                              l.status_loan IN ('approved', 'on_loan')
                              OR (l.status_loan = 'returned' AND (l.jam_keluar IS NULL OR l.jam_masuk IS NULL))
                          )");
$stmt->bindParam(':id', $id_loan);
$stmt->execute();
$data = $stmt->fetch();

if (!$data) {
    header("Location: dashboard_operasional.php?error=data_tidak_ditemukan");
    exit();
}

// update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();

    $input_jam_keluar = $_POST['jam_keluar'] ?? '';
    $input_jam_masuk  = $_POST['jam_masuk'] ?? '';
    $id_target        = $data['id_loan'];

    $normalized_jam_keluar = normalize_datetime_input($input_jam_keluar);
    $normalized_jam_masuk = normalize_datetime_input($input_jam_masuk);

    if ($normalized_jam_keluar === false || $normalized_jam_masuk === false) {
        $error = "Format jam tidak valid.";
    } elseif (empty($input_jam_keluar) && !empty($data['jam_keluar'])) {
        $final_jam_keluar = $data['jam_keluar'];
    } else {
        $final_jam_keluar = $normalized_jam_keluar;
    }

    if (!$error && empty($input_jam_masuk) && !empty($data['jam_masuk'])) {
        $final_jam_masuk = $data['jam_masuk'];
    } elseif (!$error) {
        $final_jam_masuk = $normalized_jam_masuk;
    }

    if (!$error && !empty($final_jam_keluar) && !empty($final_jam_masuk) && strtotime($final_jam_masuk) < strtotime($final_jam_keluar)) {
        $error = "Jam masuk tidak boleh lebih awal dari jam keluar.";
    } elseif (!$error) {
        // Logic Status: 
        $sql_status = "";
        if (!empty($final_jam_keluar) && $data['status_loan'] == 'approved') {
            // Mobil baru keluar
            $sql_status = ", l.status_loan = 'on_loan'";
        }

        // Query Update
        $query = "UPDATE loans l
                  JOIN assets a ON l.id_aset = a.id_aset
                  SET l.jam_keluar = :jk, l.jam_masuk = :jm $sql_status
                  WHERE l.id_loan = :id
                    AND a.kategori = 'mobil'
                    AND (
                        l.status_loan IN ('approved', 'on_loan')
                        OR (l.status_loan = 'returned' AND (l.jam_keluar IS NULL OR l.jam_masuk IS NULL))
                    )";
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
}
?>

<!DOCTYPE html>
<html>
<head><title>Form Satpam</title></head>
<link rel="stylesheet" href="css/form_satpam.css">
<body>
    <h2>Form Keamanan</h2>
    <a href="dashboard_operasional.php">&laquo; Kembali ke Dashboard</a>

    <?php if($pesan): ?>
        <p style="color: green; font-weight: bold;"><?php echo e($pesan); ?></p>
    <?php endif; ?>

    <?php if($error): ?>
        <p style="color: red; font-weight: bold;"><?php echo e($error); ?></p>
    <?php endif; ?>

    <div class="card">
        <table>
            <tr>
                <td><strong>Kendaraan</strong></td>
                <td>: <?= e($data['nama_aset']) ?> (<?= e($data['plat_nomor']) ?>)</td>
            </tr>
            <tr>
                <td><strong>Peminjam</strong></td>
                <td>: <?= e($data['peminjam']) ?></td>
            </tr>
        </table>

        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id_loan" value="<?= e($data['id_loan']); ?>">

            <label>Jam Keluar (Waktu Berangkat):</label>
            <input type="datetime-local" name="jam_keluar"
                value="<?= !empty($data['jam_keluar']) ? e(date('Y-m-d\TH:i', strtotime($data['jam_keluar']))) : ''; ?>">
            <small>Biarkan jika tidak ingin mengubah jam keluar.</small>

            <label style="margin-top:15px; display:block;">Jam Masuk (Waktu Kembali):</label>
            <input type="datetime-local" name="jam_masuk"
                value="<?= !empty($data['jam_masuk']) ? e(date('Y-m-d\TH:i', strtotime($data['jam_masuk']))) : ''; ?>">

            <button type="submit">SIMPAN DATA</button>
        </form>
    </div>
</body>
</html>
