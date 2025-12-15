<?php
require_once 'auth_check.php';
checkRole(['supir', 'satpam']);

require_once 'database/db.php';

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operasional - AssetFlow</title>
</head>
<body style="margin: 0; font-family: Arial, sans-serif; background-color: #f0f2f5;">

    <!-- Top Navigation -->
    <div style="background: #0A192F; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="color: #64FFDA; font-size: 24px;">🚗</span>
                <h1 style="font-size: 22px; margin: 0;">AssetFlow</h1>
            </div>
            <div style="font-size: 20px; font-weight: bold; display: flex; align-items: center; gap: 10px;">
                📊 Dashboard Operasional
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: rgba(255,255,255,0.1); border-radius: 50px;">
            <div style="width: 36px; height: 36px; background: #64FFDA; color: #0A192F; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
            </div>
            <div>
                <div style="font-weight: bold;"><?php echo htmlspecialchars($_SESSION['nama']); ?></div>
                <div style="background: #64FFDA; color: #0A192F; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: bold; display: inline-block;">
                    <?php echo ucfirst($role); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div style="display: flex; min-height: calc(100vh - 73px);">
        
        <!-- Sidebar -->
        <div style="width: 250px; background: #0A192F; padding: 30px 20px; color: white;">
            <div style="padding-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h3 style="color: #64FFDA; margin-bottom: 5px;">Operasional Panel</h3>
                <p style="color: rgba(255,255,255,0.7); font-size: 12px; margin: 0;">Monitoring Kendaraan</p>
            </div>
            
            <div style="margin: 30px 0;">
                <div style="color: rgba(255,255,255,0.5); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; padding: 0 10px;">Main Menu</div>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 5px;">
                        <a href="dashboard_operasional.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #64FFDA; text-decoration: none; border-radius: 8px; background: rgba(100, 255, 218, 0.2); font-weight: bold;">
                            📊 <span>Dashboard</span>
                        </a>
                    </li>
                    <li style="margin-bottom: 5px;">
                        <a href="galeri_mobil.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 8px;">
                            🚗 <span>Galeri Mobil</span>
                        </a>
                    </li>
                    <li style="margin-bottom: 5px;">
                        <a href="riwayat.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 8px;">
                            📋 <span>Riwayat</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div style="margin-top: auto; padding: 0 10px;">
                <a href="logout.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #FF6B6B; text-decoration: none; border-radius: 8px; background: rgba(255, 107, 107, 0.1);">
                    🚪 <span>Keluar</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div style="flex: 1; padding: 30px;">
            
            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <?php
                try {
                    // Hitung statistik
                    $total_cars = $conn->query("SELECT COUNT(*) FROM assets WHERE kategori = 'mobil'")->fetchColumn();
                    $available_cars = $conn->query("SELECT COUNT(*) FROM assets WHERE kategori = 'mobil' AND status_aset = 'tersedia'")->fetchColumn();
                    
                    $active_loans = $conn->prepare("SELECT COUNT(*) FROM loans l 
                                                   JOIN assets a ON l.id_aset = a.id_aset 
                                                   WHERE a.kategori = 'mobil' AND l.status_loan = 'on_loan'");
                    $active_loans->execute();
                    $active_loans_count = $active_loans->fetchColumn();
                    
                    if($role == 'supir') {
                        $my_tasks = $conn->prepare("SELECT COUNT(*) FROM loans WHERE driver_id = :user_id AND status_loan = 'on_loan'");
                        $my_tasks->bindParam(':user_id', $user_id);
                        $my_tasks->execute();
                        $my_tasks_count = $my_tasks->fetchColumn();
                    } else {
                        $today_logs = $conn->query("SELECT COUNT(*) FROM loans WHERE DATE(tgl_pinjam) = CURDATE()")->fetchColumn();
                    }
                ?>
                <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 20px; border: 1px solid #e0e0e0;">
                    <div style="width: 60px; height: 60px; background: rgba(100, 255, 218, 0.2); color: #64FFDA; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        🚗
                    </div>
                    <div>
                        <h3 style="font-size: 28px; font-weight: bold; color: #0A192F; margin: 0 0 5px 0;"><?php echo $total_cars; ?></h3>
                        <p style="color: #666; margin: 0;">Total Mobil</p>
                    </div>
                </div>
                
                <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 20px; border: 1px solid #e0e0e0;">
                    <div style="width: 60px; height: 60px; background: rgba(255, 140, 0, 0.2); color: #FF8C00; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        ✅
                    </div>
                    <div>
                        <h3 style="font-size: 28px; font-weight: bold; color: #0A192F; margin: 0 0 5px 0;"><?php echo $available_cars; ?></h3>
                        <p style="color: #666; margin: 0;">Mobil Tersedia</p>
                    </div>
                </div>
                
                <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 20px; border: 1px solid #e0e0e0;">
                    <div style="width: 60px; height: 60px; background: rgba(106, 90, 205, 0.2); color: #6a5acd; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        🛣️
                    </div>
                    <div>
                        <h3 style="font-size: 28px; font-weight: bold; color: #0A192F; margin: 0 0 5px 0;"><?php echo $active_loans_count; ?></h3>
                        <p style="color: #666; margin: 0;">Sedang Dipinjam</p>
                    </div>
                </div>
                
                <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 20px; border: 1px solid #e0e0e0;">
                    <div style="width: 60px; height: 60px; background: rgba(255, 140, 0, 0.2); color: #FF8C00; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <?php echo $role == 'supir' ? '📋' : '📅'; ?>
                    </div>
                    <div>
                        <h3 style="font-size: 28px; font-weight: bold; color: #0A192F; margin: 0 0 5px 0;"><?php echo $role == 'supir' ? $my_tasks_count : $today_logs; ?></h3>
                        <p style="color: #666; margin: 0;"><?php echo $role == 'supir' ? 'Tugas Saya' : 'Aktivitas Hari Ini'; ?></p>
                    </div>
                </div>
                
                <?php } catch(PDOException $e) { ?>
                <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 20px; border: 1px solid #e0e0e0;">
                    <div style="width: 60px; height: 60px; background: rgba(239, 68, 68, 0.2); color: #EF4444; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        ⚠️
                    </div>
                    <div>
                        <h3 style="font-size: 28px; font-weight: bold; color: #0A192F; margin: 0 0 5px 0;">-</h3>
                        <p style="color: #666; margin: 0;">Error Load Data</p>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- Galeri Mobil -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2 style="font-size: 20px; font-weight: bold; color: #0A192F; display: flex; align-items: center; gap: 10px; margin: 0;">
                        🖼️ Galeri Mobil
                    </h2>
                    <a href="galeri_mobil.php" style="padding: 8px 15px; background: #64FFDA; color: #0A192F; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;">
                        Lihat Semua →
                    </a>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <?php
                    try {
                        $query = "SELECT a.*, l.status_loan, u.nama as peminjam_nama
                                 FROM assets a 
                                 LEFT JOIN loans l ON a.id_aset = l.id_aset 
                                 AND l.status_loan = 'on_loan'
                                 LEFT JOIN users u ON l.id_user = u.id_user
                                 WHERE a.kategori = 'mobil'
                                 ORDER BY a.status_aset DESC, a.nama_aset";
                        
                        $stmt = $conn->prepare($query);
                        $stmt->execute();
                        $mobils = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if(count($mobils) > 0):
                            foreach($mobils as $car):
                                $status = $car['status_aset'];
                                $status_class = $status == 'tersedia' ? 'background: rgba(46, 204, 113, 0.2); color: #27ae60;' : 'background: rgba(231, 76, 60, 0.2); color: #c0392b;';
                                $status_text = $status == 'tersedia' ? 'Tersedia' : 'Digunakan';
                                
                                if($car['status_loan'] == 'on_loan') {
                                    $status_class = 'background: rgba(231, 76, 60, 0.2); color: #c0392b;';
                                    $status_text = 'Digunakan';
                                }
                                
                                // Data spesifikasi tambahan
                                $specs = [
                                    'Toyota Avanza' => ['type' => 'MPV', 'year' => '2023', 'fuel' => 'Bensin', 'cc' => '1300cc'],
                                    'Innova Reborn' => ['type' => 'MPV', 'year' => '2022', 'fuel' => 'Diesel', 'cc' => '2400cc']
                                ];
                                
                                $car_specs = $specs[$car['nama_aset']] ?? ['type' => 'MPV', 'year' => '2020', 'fuel' => 'Bensin', 'cc' => '1500cc'];
                                
                                // Path gambar
                                $gambar_path = 'assets/img/' . $car['gambar'];
                                $gambar_exists = file_exists($gambar_path);
                    ?>
                    <div style="background: #f8f9fa; border-radius: 10px; overflow: hidden; border: 1px solid #e0e0e0;">
                        <!-- Bagian Gambar Mobil -->
                        <div style="height: 180px; position: relative; overflow: hidden; border-bottom: 3px solid #64FFDA;">
                            <?php if($gambar_exists): ?>
                                <img src="<?php echo $gambar_path; ?>" 
                                     alt="<?php echo htmlspecialchars($car['nama_aset']); ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.4));"></div>
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #0A192F 0%, #1a2b3c 100%); display: flex; align-items: center; justify-content: center; color: #64FFDA; font-size: 60px;">
                                    <?php echo $car['nama_aset'] == 'Toyota Avanza' ? '🚗' : '🚙'; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Overlay Info di atas gambar -->
                            <div style="position: absolute; bottom: 10px; left: 0; right: 0; padding: 0 15px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: white; font-weight: bold; font-size: 14px; background: rgba(0,0,0,0.6); padding: 5px 10px; border-radius: 15px;">
                                        🔢 <?php echo htmlspecialchars($car['plat_nomor']); ?>
                                    </span>
                                    <span style="padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: bold; <?php echo $status_class; ?>">
                                        ● <?php echo $status_text; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div style="padding: 20px;">
                            <h4 style="font-size: 16px; font-weight: bold; color: #0A192F; margin: 0 0 10px 0;"><?php echo htmlspecialchars($car['nama_aset']); ?></h4>
                            <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #666;">
                                    ⛽ <span><?php echo $car_specs['fuel']; ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #666;">
                                    🏷️ <span><?php echo $car_specs['type']; ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #666;">
                                    📅 <span><?php echo $car_specs['year']; ?></span>
                                </div>
                                <?php if($car['peminjam_nama']): ?>
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #666;">
                                    👤 <span><?php echo htmlspecialchars($car['peminjam_nama']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- PERBAIKI LINK INI -->
                            <a href="detail_mobil.php?id=<?php echo $car['id_aset']; ?>" style="display: block; width: 100%; padding: 12px; background: #FF8C00; color: white; text-align: center; text-decoration: none; border-radius: 8px; margin-top: 15px; font-weight: bold;">
                                👁️ Lihat Detail & Form
                            </a>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div style="text-align: center; padding: 60px 20px; color: #666; grid-column: 1 / -1;">
                        <div style="font-size: 60px; margin-bottom: 20px; color: #ddd;">🚫</div>
                        <h4 style="color: #0A192F; margin-bottom: 10px; font-size: 18px;">Tidak ada data mobil</h4>
                        <p>Belum ada mobil yang terdaftar dalam sistem</p>
                    </div>
                    <?php endif; ?>
                    <?php } catch(PDOException $e) { ?>
                    <div style="text-align: center; padding: 60px 20px; color: #666; grid-column: 1 / -1;">
                        <div style="font-size: 60px; margin-bottom: 20px; color: #ddd;">⚠️</div>
                        <h4 style="color: #0A192F; margin-bottom: 10px; font-size: 18px;">Terjadi Kesalahan</h4>
                        <p>Gagal memuat data mobil</p>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Riwayat Terbaru -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2 style="font-size: 20px; font-weight: bold; color: #0A192F; display: flex; align-items: center; gap: 10px; margin: 0;">
                        📋 Riwayat Terbaru
                    </h2>
                    <a href="riwayat.php" style="padding: 8px 15px; background: #64FFDA; color: #0A192F; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;">
                        Lihat Semua →
                    </a>
                </div>
                
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden;">
                        <thead>
                            <tr style="background: #0A192F; color: white;">
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Tanggal</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Mobil</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Peminjam</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Status</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $query = "SELECT l.*, a.nama_aset, a.plat_nomor, u.nama as peminjam_nama
                                         FROM loans l 
                                         JOIN assets a ON l.id_aset = a.id_aset 
                                         JOIN users u ON l.id_user = u.id_user
                                         WHERE a.kategori = 'mobil'
                                         ORDER BY l.tgl_pinjam DESC
                                         LIMIT 5";
                                
                                $stmt = $conn->prepare($query);
                                $stmt->execute();
                                $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if(count($riwayat) > 0):
                                    foreach($riwayat as $row):
                                        $badge_style = '';
                                        switch($row['status_loan']) {
                                            case 'returned': 
                                                $badge_style = 'background: rgba(46, 204, 113, 0.2); color: #27ae60;'; 
                                                $status_text = 'Selesai'; 
                                                break;
                                            case 'on_loan': 
                                                $badge_style = 'background: rgba(52, 152, 219, 0.2); color: #2980b9;'; 
                                                $status_text = 'Berjalan'; 
                                                break;
                                            case 'approved': 
                                                $badge_style = 'background: rgba(241, 196, 15, 0.2); color: #f39c12;'; 
                                                $status_text = 'Disetujui'; 
                                                break;
                                            default: 
                                                $badge_style = ''; 
                                                $status_text = $row['status_loan'];
                                        }
                            ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 15px;"><?php echo date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                                <td style="padding: 15px;">
                                    <strong><?php echo htmlspecialchars($row['nama_aset']); ?></strong><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($row['plat_nomor']); ?></small>
                                </td>
                                <td style="padding: 15px;"><?php echo htmlspecialchars($row['peminjam_nama']); ?></td>
                                <td style="padding: 15px;">
                                    <span style="display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; <?php echo $badge_style; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="font-size: 13px; max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars(substr($row['keterangan'], 0, 30)); ?>
                                        <?php echo strlen($row['keterangan']) > 30 ? '...' : ''; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                                    Tidak ada riwayat peminjaman
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php } catch(PDOException $e) { ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #EF4444;">
                                    Error loading data
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>