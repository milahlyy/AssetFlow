<?php
require_once 'auth_check.php';
checkRole(['supir', 'satpam']);

require_once 'database/db.php';

// Cek parameter
if(!isset($_GET['id'])) {
    header("Location: dashboard_operasional.php");
    exit();
}

$asset_id = $_GET['id'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Inisialisasi pesan
$success = '';
$error = '';

// Ambil data asset
try {
    $stmt = $conn->prepare("SELECT * FROM assets WHERE id_aset = :id");
    $stmt->bindParam(':id', $asset_id);
    $stmt->execute();
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$asset || $asset['kategori'] != 'mobil') {
        header("Location: dashboard_operasional.php");
        exit();
    }
    
    // Ambil data peminjaman aktif
    $stmt = $conn->prepare("SELECT l.*, u.nama as peminjam_nama, d.nama as supir_nama 
                           FROM loans l 
                           JOIN users u ON l.id_user = u.id_user 
                           LEFT JOIN users d ON l.driver_id = d.id_user 
                           WHERE l.id_aset = :id 
                           AND l.status_loan IN ('approved', 'on_loan')
                           ORDER BY l.tgl_pinjam DESC 
                           LIMIT 1");
    $stmt->bindParam(':id', $asset_id);
    $stmt->execute();
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Proses form update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if($role == 'satpam' && isset($_POST['update_jam'])) {
        // Update jam keluar/masuk (Satpam)
        $jam_keluar = !empty($_POST['jam_keluar']) ? $_POST['jam_keluar'] : null;
        $jam_masuk = !empty($_POST['jam_masuk']) ? $_POST['jam_masuk'] : null;
        
        try {
            // Format datetime untuk database
            if($jam_keluar) {
                $jam_keluar = date('Y-m-d H:i:s', strtotime($jam_keluar));
            }
            if($jam_masuk) {
                $jam_masuk = date('Y-m-d H:i:s', strtotime($jam_masuk));
            }
            
            // Cek apakah ada loan aktif
            $checkStmt = $conn->prepare("SELECT id_loan FROM loans WHERE id_aset = :asset_id AND status_loan IN ('approved', 'on_loan')");
            $checkStmt->bindParam(':asset_id', $asset_id);
            $checkStmt->execute();
            
            if($checkStmt->rowCount() > 0) {
                $query = "UPDATE loans SET 
                         jam_keluar = :jam_keluar, 
                         jam_masuk = :jam_masuk";
                
                // Jika jam masuk diisi, ubah status jadi returned
                if($jam_masuk) {
                    $query .= ", status_loan = 'returned'";
                } 
                // Jika jam keluar diisi tapi jam masuk belum, ubah status jadi on_loan
                elseif($jam_keluar && !$loan['jam_keluar']) {
                    $query .= ", status_loan = 'on_loan'";
                }
                
                $query .= " WHERE id_aset = :asset_id 
                           AND (status_loan = 'approved' OR status_loan = 'on_loan')";
                
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':jam_keluar', $jam_keluar);
                $stmt->bindParam(':jam_masuk', $jam_masuk);
                $stmt->bindParam(':asset_id', $asset_id);
                
                if($stmt->execute()) {
                    $success = "Jam berhasil diperbarui!";
                    
                    // Update status asset jika sudah kembali
                    if($jam_masuk) {
                        $stmt = $conn->prepare("UPDATE assets SET status_aset = 'tersedia' WHERE id_aset = :id");
                        $stmt->bindParam(':id', $asset_id);
                        $stmt->execute();
                    }
                    
                    // Refresh data loan
                    $stmt = $conn->prepare("SELECT l.*, u.nama as peminjam_nama, d.nama as supir_nama 
                                           FROM loans l 
                                           JOIN users u ON l.id_user = u.id_user 
                                           LEFT JOIN users d ON l.driver_id = d.id_user 
                                           WHERE l.id_aset = :id 
                                           ORDER BY l.tgl_pinjam DESC 
                                           LIMIT 1");
                    $stmt->bindParam(':id', $asset_id);
                    $stmt->execute();
                    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            } else {
                $error = "Tidak ada peminjaman aktif untuk mobil ini";
            }
        } catch(PDOException $e) {
            $error = "Gagal memperbarui jam: " . $e->getMessage();
        }
    } 
    elseif($role == 'supir' && isset($_POST['update_perjalanan'])) {
        // Update KM dan kondisi (Supir)
        $km_awal = $_POST['km_awal'];
        $km_akhir = $_POST['km_akhir'];
        $kondisi_mobil = $_POST['kondisi_mobil'];
        
        // Validasi
        if($km_akhir <= $km_awal) {
            $error = "KM akhir harus lebih besar dari KM awal";
        } else {
            try {
                // Cek apakah supir bertanggung jawab atas loan ini
                $checkStmt = $conn->prepare("SELECT * FROM loans WHERE id_aset = :asset_id AND driver_id = :driver_id AND status_loan IN ('on_loan', 'approved')");
                $checkStmt->bindParam(':asset_id', $asset_id);
                $checkStmt->bindParam(':driver_id', $user_id);
                $checkStmt->execute();
                
                if($checkStmt->rowCount() > 0) {
                    $conn->beginTransaction();
                    
                    // Update loan
                    $stmt = $conn->prepare("UPDATE loans SET 
                                           km_awal = :km_awal,
                                           km_akhir = :km_akhir,
                                           kondisi_mobil = :kondisi_mobil,
                                           jam_masuk = NOW()
                                           WHERE id_aset = :asset_id AND driver_id = :driver_id");
                    $stmt->bindParam(':km_awal', $km_awal);
                    $stmt->bindParam(':km_akhir', $km_akhir);
                    $stmt->bindParam(':kondisi_mobil', $kondisi_mobil);
                    $stmt->bindParam(':asset_id', $asset_id);
                    $stmt->bindParam(':driver_id', $user_id);
                    $stmt->execute();
                    
                    // Update data mobil (KM terakhir dan status)
                    $stmt = $conn->prepare("UPDATE assets SET 
                                           status_aset = 'tersedia'
                                           WHERE id_aset = :id");
                    $stmt->bindParam(':id', $asset_id);
                    $stmt->execute();
                    
                    $conn->commit();
                    $success = "Data perjalanan berhasil diperbarui!";
                    
                    // Refresh data asset
                    $stmt = $conn->prepare("SELECT * FROM assets WHERE id_aset = :id");
                    $stmt->bindParam(':id', $asset_id);
                    $stmt->execute();
                    $asset = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Refresh data loan
                    $stmt = $conn->prepare("SELECT l.*, u.nama as peminjam_nama, d.nama as supir_nama 
                                           FROM loans l 
                                           JOIN users u ON l.id_user = u.id_user 
                                           LEFT JOIN users d ON l.driver_id = d.id_user 
                                           WHERE l.id_aset = :asset_id AND l.driver_id = :driver_id
                                           ORDER BY l.tgl_pinjam DESC 
                                           LIMIT 1");
                    $stmt->bindParam(':asset_id', $asset_id);
                    $stmt->bindParam(':driver_id', $user_id);
                    $stmt->execute();
                    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                } else {
                    $error = "Anda tidak memiliki akses untuk memperbarui data ini";
                }
            } catch(PDOException $e) {
                if($conn->inTransaction()) {
                    $conn->rollBack();
                }
                $error = "Gagal memperbarui data: " . $e->getMessage();
            }
        }
    }
}

// Ambil data terbaru setelah update
try {
    $stmt = $conn->prepare("SELECT l.*, u.nama as peminjam_nama, d.nama as supir_nama 
                           FROM loans l 
                           JOIN users u ON l.id_user = u.id_user 
                           LEFT JOIN users d ON l.driver_id = d.id_user 
                           WHERE l.id_aset = :id 
                           ORDER BY l.tgl_pinjam DESC 
                           LIMIT 1");
    $stmt->bindParam(':id', $asset_id);
    $stmt->execute();
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $loan = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mobil - AssetFlow</title>
</head>
<body style="margin: 0; font-family: Arial, sans-serif; background-color: #f0f2f5;">

    <!-- Top Navigation -->
    <div style="background: #0A192F; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-bottom: 3px solid #64FFDA;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="dashboard_operasional.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: white; color: #0A192F; text-decoration: none; border-radius: 8px; font-weight: bold;">
                ⬅ Kembali ke Dashboard
            </a>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="color: #64FFDA; font-size: 24px;">🚗</span>
                <h1 style="font-size: 22px; margin: 0;">Detail Mobil</h1>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: rgba(255,255,255,0.1); border-radius: 50px;">
            <div style="width: 36px; height: 36px; background: #64FFDA; color: #0A192F; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
            </div>
            <div style="background: #64FFDA; color: #0A192F; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                <?php echo ucfirst($role); ?>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
        
        <!-- Alert Messages -->
        <?php if($success): ?>
        <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; background: rgba(16, 185, 129, 0.1); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.2);">
            ✅ <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; background: rgba(239, 68, 68, 0.1); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.2);">
            ❌ <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- Car Detail -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
            
            <!-- Car Image -->
            <div style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;">
                <?php
                $image_path = 'assets/img/' . $asset['gambar'];
                if(!file_exists($image_path)) {
                    $image_path = 'assets/img/default-car.jpg';
                }
                ?>
                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($asset['nama_aset']); ?>" style="width: 100%; height: 280px; object-fit: cover; border-radius: 12px; margin-bottom: 25px; border: 3px solid #64FFDA;">
                
                <h2 style="font-size: 22px; font-weight: bold; color: #0A192F; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #64FFDA; display: flex; align-items: center; gap: 10px;">
                    🚗 <?php echo htmlspecialchars($asset['nama_aset']); ?>
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px;">
                    <div style="padding: 18px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e0e0e0;">
                        <div style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                            🔢 Plat Nomor
                        </div>
                        <div style="font-size: 16px; font-weight: bold; color: #0A192F;">
                            <?php echo htmlspecialchars($asset['plat_nomor']); ?>
                        </div>
                    </div>
                    
                    <div style="padding: 18px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e0e0e0;">
                        <div style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                            📊 Status Kendaraan
                        </div>
                        <?php
                        $status_style = '';
                        switch($asset['status_aset']) {
                            case 'tersedia': $status_style = 'background: rgba(16, 185, 129, 0.2); color: #10B981;'; break;
                            case 'maintenance': $status_style = 'background: rgba(245, 158, 11, 0.2); color: #F59E0B;'; break;
                            case 'rusak': $status_style = 'background: rgba(239, 68, 68, 0.2); color: #EF4444;'; break;
                            default: $status_style = 'background: #f0f0f0; color: #666;';
                        }
                        ?>
                        <div style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: bold; <?php echo $status_style; ?>">
                            ● <?php echo ucfirst($asset['status_aset']); ?>
                        </div>
                    </div>
                    
                    <div style="padding: 18px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e0e0e0;">
                        <div style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                            ⚙️ Kategori
                        </div>
                        <div style="font-size: 16px; font-weight: bold; color: #0A192F;">
                            <?php echo ucfirst($asset['kategori']); ?>
                        </div>
                    </div>
                    
                    <?php if(!empty($loan) && is_array($loan)): ?>
                    <div style="padding: 18px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e0e0e0;">
                        <div style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                            👤 Supir Bertugas
                        </div>
                        <div style="font-size: 16px; font-weight: bold; color: #0A192F;">
                            <?php echo !empty($loan['supir_nama']) ? htmlspecialchars($loan['supir_nama']) : 'Belum ditentukan'; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Car Info -->
            <div style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;">
                <h2 style="font-size: 22px; font-weight: bold; color: #0A192F; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #64FFDA; display: flex; align-items: center; gap: 10px;">
                    ℹ️ Informasi Detail
                </h2>
                
                <?php if(!empty($loan) && is_array($loan)): ?>
                <div style="background: linear-gradient(135deg, #0A192F 0%, #152642 100%); color: white; padding: 25px; border-radius: 12px; margin-bottom: 30px; border: 2px solid #64FFDA;">
                    <h3 style="font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        📋 Informasi Peminjaman
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div style="padding: 15px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                            <div style="font-size: 12px; opacity: 0.8; margin-bottom: 5px; text-transform: uppercase;">Peminjam</div>
                            <div style="font-size: 16px; font-weight: bold; color: #64FFDA;"><?php echo htmlspecialchars($loan['peminjam_nama']); ?></div>
                        </div>
                        
                        <div style="padding: 15px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                            <div style="font-size: 12px; opacity: 0.8; margin-bottom: 5px; text-transform: uppercase;">Tanggal Pinjam</div>
                            <div style="font-size: 16px; font-weight: bold; color: #64FFDA;"><?php echo date('d/m/Y', strtotime($loan['tgl_pinjam'])); ?></div>
                        </div>
                        
                        <div style="padding: 15px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                            <div style="font-size: 12px; opacity: 0.8; margin-bottom: 5px; text-transform: uppercase;">Keterangan</div>
                            <div style="font-size: 16px; font-weight: bold; color: #64FFDA;"><?php echo htmlspecialchars($loan['keterangan']); ?></div>
                        </div>
                        
                        <div style="padding: 15px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                            <div style="font-size: 12px; opacity: 0.8; margin-bottom: 5px; text-transform: uppercase;">Status Peminjaman</div>
                            <div style="font-size: 16px; font-weight: bold; color: #64FFDA;">
                                <?php 
                                $status_map = [
                                    'approved' => 'Disetujui',
                                    'on_loan' => 'Sedang Dipinjam',
                                    'returned' => 'Dikembalikan',
                                    'pending' => 'Menunggu',
                                    'rejected' => 'Ditolak'
                                ];
                                echo isset($loan['status_loan']) ? ($status_map[$loan['status_loan']] ?? $loan['status_loan']) : 'Tidak diketahui';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div style="background: white; border-radius: 12px; padding: 40px; text-align: center; border: 2px dashed #e0e0e0;">
                    <div style="font-size: 60px; margin-bottom: 20px; color: #ddd;">ℹ️</div>
                    <h3 style="color: #0A192F; margin-bottom: 10px; font-size: 20px;">Tidak ada peminjaman aktif</h3>
                    <p style="color: #666;">Mobil ini sedang tidak dipinjam atau tidak ada data peminjaman</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- FORM UPDATE BERDASARKAN ROLE -->
        <?php if($role == 'satpam'): ?>
        <!-- Form Update Jam (Satpam) -->
        <div style="background: white; border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;">
            <h2 style="font-size: 20px; font-weight: bold; color: #0A192F; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #FF8C00; display: flex; align-items: center; gap: 10px;">
                ⏰ Update Waktu Operasional
            </h2>
            
            <?php if(!empty($loan) && is_array($loan)): ?>
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 30px;">
                    <div>
                        <label style="display: block; margin-bottom: 10px; font-weight: bold; color: #0A192F; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            🕒 Jam Keluar
                        </label>
                        <input type="datetime-local" 
                               name="jam_keluar" 
                               style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #f8f9fa; color: #0A192F;"
                               value="<?php echo isset($loan['jam_keluar']) ? date('Y-m-d\TH:i', strtotime($loan['jam_keluar'])) : ''; ?>">
                        <div style="font-size: 12px; color: #666; margin-top: 6px; font-style: italic; padding-left: 24px;">
                            Isi waktu saat mobil keluar dari kantor
                        </div>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 10px; font-weight: bold; color: #0A192F; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            🕒 Jam Masuk
                        </label>
                        <input type="datetime-local" 
                               name="jam_masuk" 
                               style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #f8f9fa; color: #0A192F;"
                               value="<?php echo isset($loan['jam_masuk']) ? date('Y-m-d\TH:i', strtotime($loan['jam_masuk'])) : ''; ?>">
                        <div style="font-size: 12px; color: #666; margin-top: 6px; font-style: italic; padding-left: 24px;">
                            Isi waktu saat mobil kembali ke kantor
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="update_jam" style="background: #FF8C00; color: white; border: none; padding: 16px 32px; border-radius: 10px; font-size: 15px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 12px;">
                    💾 Simpan Perubahan
                </button>
            </form>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <div style="font-size: 60px; margin-bottom: 20px; color: #ddd;">⚠️</div>
                <h3 style="color: #0A192F; margin-bottom: 10px; font-size: 20px;">Tidak dapat mengupdate</h3>
                <p>Tidak ada data peminjaman aktif untuk mobil ini</p>
            </div>
            <?php endif; ?>
        </div>
        
        <?php elseif($role == 'supir'): ?>
            <?php if(!empty($loan) && is_array($loan) && isset($loan['driver_id']) && $loan['driver_id'] == $user_id): ?>
            <!-- Form Update Perjalanan (Supir) -->
            <div style="background: white; border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;">
                <h2 style="font-size: 20px; font-weight: bold; color: #0A192F; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #FF8C00; display: flex; align-items: center; gap: 10px;">
                    🛣️ Update Data Perjalanan
                </h2>
                
                <form method="POST" action="">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 30px;">
                        <div>
                            <label style="display: block; margin-bottom: 10px; font-weight: bold; color: #0A192F; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                📊 KM Awal
                            </label>
                            <input type="number" 
                                   name="km_awal" 
                                   style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #f8f9fa; color: #0A192F;"
                                   value="<?php echo isset($loan['km_awal']) ? $loan['km_awal'] : ''; ?>"
                                   required
                                   min="0">
                            <div style="font-size: 12px; color: #666; margin-top: 6px; font-style: italic; padding-left: 24px;">
                                Isi angka kilometer saat mulai perjalanan
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 10px; font-weight: bold; color: #0A192F; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                📊 KM Akhir
                            </label>
                            <input type="number" 
                                   name="km_akhir" 
                                   style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #f8f9fa; color: #0A192F;"
                                   value="<?php echo isset($loan['km_akhir']) ? $loan['km_akhir'] : ''; ?>"
                                   required
                                   min="0">
                            <div style="font-size: 12px; color: #666; margin-top: 6px; font-style: italic; padding-left: 24px;">
                                Isi angka kilometer saat selesai perjalanan
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 10px; font-weight: bold; color: #0A192F; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                🚗 Kondisi Mobil
                            </label>
                            <select name="kondisi_mobil" style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #f8f9fa; color: #0A192F;" required>
                                <option value="">Pilih Kondisi</option>
                                <option value="baik" <?php echo (isset($loan['kondisi_mobil']) && $loan['kondisi_mobil'] == 'baik') ? 'selected' : ''; ?>>Baik</option>
                                <option value="rusak_ringan" <?php echo (isset($loan['kondisi_mobil']) && $loan['kondisi_mobil'] == 'rusak_ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                                <option value="rusak_berat" <?php echo (isset($loan['kondisi_mobil']) && $loan['kondisi_mobil'] == 'rusak_berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                                <option value="perlu_service" <?php echo (isset($loan['kondisi_mobil']) && $loan['kondisi_mobil'] == 'perlu_service') ? 'selected' : ''; ?>>Perlu Service</option>
                            </select>
                            <div style="font-size: 12px; color: #666; margin-top: 6px; font-style: italic; padding-left: 24px;">
                                Pilih kondisi mobil setelah digunakan
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_perjalanan" style="background: #FF8C00; color: white; border: none; padding: 16px 32px; border-radius: 10px; font-size: 15px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 12px;">
                        💾 Simpan Data Perjalanan
                    </button>
                </form>
            </div>
            
            <?php else: ?>
            <!-- Message for driver not assigned -->
            <div style="background: white; border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid #e0e0e0; text-align: center;">
                <div style="font-size: 60px; margin-bottom: 20px; color: #ddd;">⛔</div>
                <h4 style="color: #0A192F; margin-bottom: 10px; font-size: 20px;">Tidak Ada Akses</h4>
                <p style="color: #666;">Anda tidak ditugaskan untuk mobil ini atau tidak ada data peminjaman</p>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Validasi form
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Validasi khusus untuk form supir
                    if(form.querySelector('[name="update_perjalanan"]')) {
                        const kmAwal = document.querySelector('[name="km_awal"]');
                        const kmAkhir = document.querySelector('[name="km_akhir"]');
                        
                        if(kmAwal && kmAkhir && parseInt(kmAkhir.value) <= parseInt(kmAwal.value)) {
                            e.preventDefault();
                            alert('KM akhir harus lebih besar dari KM awal');
                            kmAkhir.focus();
                            return false;
                        }
                    }
                    
                    // Validasi untuk form satpam
                    if(form.querySelector('[name="update_jam"]')) {
                        const jamKeluar = document.querySelector('[name="jam_keluar"]');
                        const jamMasuk = document.querySelector('[name="jam_masuk"]');
                        
                        if(jamKeluar && jamMasuk && jamKeluar.value && jamMasuk.value) {
                            const dateKeluar = new Date(jamKeluar.value);
                            const dateMasuk = new Date(jamMasuk.value);
                            
                            if(dateMasuk <= dateKeluar) {
                                e.preventDefault();
                                alert('Jam masuk harus lebih besar dari jam keluar');
                                jamMasuk.focus();
                                return false;
                            }
                        }
                    }
                });
            });
            
            // Set current time for datetime inputs
            const now = new Date();
            const currentDateTime = now.toISOString().slice(0, 16);
            
            // Set default value for empty datetime inputs
            document.querySelectorAll('input[type="datetime-local"]').forEach(input => {
                if(!input.value) {
                    input.value = currentDateTime;
                }
            });
        });
    </script>
</body>
</html>