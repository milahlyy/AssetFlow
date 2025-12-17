<?php
session_start();
require_once 'database/db.php';
require_once 'auth_check.php';
checkrole(['hrga']);

// Handle approve/reject (TIDAK DIUBAH SAMA SEKALI)
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

// Get pending loans (TIDAK DIUBAH SAMA SEKALI)
$loans = $conn->query("
    SELECT l.*, u.nama as pemohon, a.nama_aset, a.kategori 
    FROM loans l 
    JOIN users u ON l.id_user = u.id_user 
    JOIN assets a ON l.id_aset = a.id_aset 
    WHERE l.status_loan = 'pending' 
    ORDER BY l.tgl_pinjam ASC
")->fetchAll();

// Get drivers (TIDAK DIUBAH SAMA SEKALI)
$drivers = $conn->query("SELECT * FROM users WHERE role='supir'")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Persetujuan Peminjaman</title>
    <link rel="stylesheet" href="css/persetujuan_adm.css">
</head>
<body>
    
    <div class="sidebar">
        <h2>HRGA</h2>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="kelola_aset.php">Kelola Aset</a>
        <a href="persetujuan.php">Persetujuan Peminjaman</a>
        <a href="laporan.php">Laporan</a>
        <a href="kelola_user.php">Kelola User</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Persetujuan Peminjaman</h1>
        
        <?php if(isset($message)): ?>
            <div class="alert-box"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="loan-list">
            <?php if(empty($loans)): ?>
                <p style="text-align:center; color:#666;">Tidak ada permohonan pending</p>
            <?php else: ?>
                <?php foreach($loans as $loan): ?>
                <div class="loan-card">
                    <div class="loan-id">KM<?= str_pad($loan['id_loan'], 7, '0', STR_PAD_LEFT) ?></div>
                    
                    <div class="loan-date">
                        <?= date('m-d-Y', strtotime($loan['tgl_pinjam'])) ?>
                    </div>
                    
                    <div class="loan-date">
                        <?= date('m-d-Y', strtotime($loan['tgl_kembali'] ?? $loan['tgl_pinjam'])) ?>
                    </div>

                    <button class="btn-detail" onclick='openModal(<?= json_encode($loan) ?>)'>Detail...</button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            
            <div class="modal-header">
                <h3>Detail Peminjaman</h3>
            </div>

            <div class="modal-body">
                <div class="info-row">
                    <span class="info-label">ID Peminjaman :</span>
                    <span id="modal_id_view"></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama Peminjam :</span>
                    <span id="modal_peminjam"></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama Aset :</span>
                    <span id="modal_aset"></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Pinjam :</span>
                    <span id="modal_tgl_pinjam"></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Keterangan :</span>
                    <span id="modal_keterangan"></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status :</span>
                    <span style="color:#d97706; font-weight:bold;">Menunggu</span>
                </div>

                <div id="driver_section" style="margin-top: 15px;">
                    <span class="driver-label">Pilih Driver (Khusus Mobil):</span>
                    <form method="POST" id="formApprove">
                        <input type="hidden" name="id_loan" id="input_id_loan_approve">
                        <input type="hidden" name="action" value="approve">
                        
                        <select name="driver_id" id="driver_select" class="input-driver">
                            <option value="">-- Tanpa Driver --</option>
                            <?php foreach($drivers as $driver): ?>
                                <option value="<?= $driver['id_user'] ?>"><?= $driver['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <div id="reject_section" style="display:none; margin-top:10px;">
                    <form method="POST" id="formReject">
                        <input type="hidden" name="id_loan" id="input_id_loan_reject">
                        <input type="hidden" name="action" value="reject">
                        <input type="text" name="alasan_penolakan" class="input-reason" style="display:block;" required placeholder="Alasan penolakan...">
                        <button type="submit" class="btn-reject" style="margin-top:10px; width:100%;">Konfirmasi Tolak</button>
                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-approve" onclick="submitApprove()">Setujui</button>
                <button class="btn-reject" onclick="showReject()">Tolak</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById("detailModal");
        const driverSelect = document.getElementById("driver_select");
        const rejectSection = document.getElementById("reject_section");

        function openModal(data) {
            rejectSection.style.display = "none";
            document.querySelector('.modal-footer').style.display = "flex";

            let formattedId = "KM" + String(data.id_loan).padStart(7, '0');
            
            // Isi data ke Text
            document.getElementById("modal_id_view").innerText = formattedId;
            document.getElementById("modal_peminjam").innerText = data.pemohon;
            document.getElementById("modal_aset").innerText = data.nama_aset;
            document.getElementById("modal_keterangan").innerText = data.keterangan || '-';
            
            // Format Tanggal
            const d = new Date(data.tgl_pinjam);
            document.getElementById("modal_tgl_pinjam").innerText = 
                (d.getMonth()+1).toString().padStart(2,'0') + '-' + 
                d.getDate().toString().padStart(2,'0') + '-' + 
                d.getFullYear();

            // Isi ID ke Hidden Input Form
            document.getElementById("input_id_loan_approve").value = data.id_loan;
            document.getElementById("input_id_loan_reject").value = data.id_loan;

            // Logika Supir
            if(data.kategori && data.kategori.toLowerCase() === 'mobil') {
                driverSelect.style.display = "block";
                document.querySelector('.driver-label').style.display = "block";
            } else {
                driverSelect.style.display = "none";
                document.querySelector('.driver-label').style.display = "none";
            }

            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        function submitApprove() {
            document.getElementById("formApprove").submit();
        }

        function showReject() {
            rejectSection.style.display = "block";
            document.querySelector('.modal-footer').style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>