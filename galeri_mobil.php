<?php
require_once 'auth_check.php';
checkRole(['supir', 'satpam']);

require_once 'database/db.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Mobil - AssetFlow</title>
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
                🖼️ Galeri Mobil
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: rgba(255,255,255,0.1); border-radius: 50px;">
            <div style="width: 36px; height: 36px; background: #64FFDA; color: #0A192F; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
            </div>
            <div>
                <div style="font-weight: bold;"><?php echo htmlspecialchars($_SESSION['nama']); ?></div>
                <div style="background: #64FFDA; color: #0A192F; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: bold; display: inline-block;">
                    <?php echo ucfirst($_SESSION['role']); ?>
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
                        <a href="dashboard_operasional.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 8px;">
                            📊 <span>Dashboard</span>
                        </a>
                    </li>
                    <li style="margin-bottom: 5px;">
                        <a href="galeri_mobil.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #64FFDA; text-decoration: none; border-radius: 8px; background: rgba(100, 255, 218, 0.2); font-weight: bold;">
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
        <div style="flex: 1; padding: 30px; background-color: #f0f2f5;">
            
            <!-- Info Header -->
            <div style="background: linear-gradient(135deg, #0A192F 0%, #1a2b3c 100%); border-radius: 12px; padding: 25px; margin-bottom: 30px; color: white;">
                <h2 style="color: #64FFDA; margin: 0 0 10px 0; font-size: 22px;">🚗 Galeri Mobil Perusahaan</h2>
                <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 14px;">Total 2 unit mobil tersedia untuk operasional perusahaan</p>
            </div>

            <!-- Galeri Mobil (Hanya 2 mobil) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
                <?php
                try {
                    $query = "SELECT a.*, 
                             (SELECT status_loan FROM loans WHERE id_aset = a.id_aset AND status_loan = 'on_loan' LIMIT 1) as status_pinjam,
                             (SELECT u.nama FROM loans l JOIN users u ON l.id_user = u.id_user WHERE l.id_aset = a.id_aset AND l.status_loan = 'on_loan' LIMIT 1) as peminjam_sekarang
                             FROM assets a 
                             WHERE a.kategori = 'mobil'
                             ORDER BY a.nama_aset";
                    
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $mobils = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if(count($mobils) > 0):
                        foreach($mobils as $mobil):
                            // Tentukan status
                            $status = $mobil['status_aset'];
                            $sedang_dipinjam = ($mobil['status_pinjam'] == 'on_loan');
                            
                            if($sedang_dipinjam) {
                                $status_color = '#FF8C00';
                                $status_text = 'Sedang Dipinjam';
                                $badge_style = 'background: rgba(255, 140, 0, 0.2); color: #FF8C00;';
                            } else {
                                switch($status) {
                                    case 'tersedia':
                                        $status_color = '#27ae60';
                                        $status_text = 'Tersedia';
                                        $badge_style = 'background: rgba(46, 204, 113, 0.2); color: #27ae60;';
                                        break;
                                    case 'maintenance':
                                        $status_color = '#e67e22';
                                        $status_text = 'Maintenance';
                                        $badge_style = 'background: rgba(230, 126, 34, 0.2); color: #e67e22;';
                                        break;
                                    case 'rusak':
                                        $status_color = '#e74c3c';
                                        $status_text = 'Rusak';
                                        $badge_style = 'background: rgba(231, 76, 60, 0.2); color: #e74c3c;';
                                        break;
                                    default:
                                        $status_color = '#95a5a6';
                                        $status_text = 'Tidak Diketahui';
                                        $badge_style = 'background: rgba(149, 165, 166, 0.2); color: #7f8c8d;';
                                }
                            }
                            
                            // Data spesifikasi khusus untuk 2 mobil
                            $specs = [
                                'Toyota Avanza' => [
                                    'type' => 'MPV', 
                                    'year' => '2023', 
                                    'fuel' => 'Bensin', 
                                    'cc' => '1300cc',
                                    'transmission' => 'Manual',
                                    'seats' => '7',
                                    'color' => 'Hitam',
                                    'features' => ['AC', 'Power Steering', 'Audio Bluetooth', 'Airbag']
                                ],
                                'Innova Reborn' => [
                                    'type' => 'MPV', 
                                    'year' => '2022', 
                                    'fuel' => 'Diesel', 
                                    'cc' => '2400cc',
                                    'transmission' => 'Automatic',
                                    'seats' => '8',
                                    'color' => 'Hitam',
                                    'features' => ['AC Dual Zone', 'Power Steering', 'Touchscreen Audio', '7 Airbags', 'ABS', 'Parking Sensor']
                                ]
                            ];
                            
                            $car_specs = $specs[$mobil['nama_aset']] ?? ['type' => 'MPV', 'year' => '2020', 'fuel' => 'Bensin', 'cc' => '1500cc'];
                            
                            // PATH GAMBAR DARI assets/img/
                            $gambar_path = 'assets/img/' . $mobil['gambar'];
                            $gambar_exists = file_exists($gambar_path);
                ?>
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;">
                    <!-- Header Card dengan Gambar -->
                    <div style="height: 250px; position: relative; overflow: hidden;">
                        <?php if($gambar_exists): ?>
                            <!-- Gambar Mobil dari assets/img/ -->
                            <img src="<?php echo $gambar_path; ?>" 
                                 alt="<?php echo htmlspecialchars($mobil['nama_aset']); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                            <!-- Overlay gelap untuk teks lebih jelas -->
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(10, 25, 47, 0.3), rgba(10, 25, 47, 0.7));"></div>
                        <?php else: ?>
                            <!-- Fallback jika gambar tidak ada -->
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #0A192F 0%, #1a2b3c 100%); display: flex; align-items: center; justify-content: center; color: #64FFDA;">
                                <div style="text-align: center;">
                                    <div style="font-size: 60px; margin-bottom: 10px;">
                                        <?php echo $mobil['nama_aset'] == 'Toyota Avanza' ? '🚗' : '🚙'; ?>
                                    </div>
                                    <div style="color: white; font-size: 16px; font-weight: bold;">
                                        Gambar tidak tersedia
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Informasi mobil di atas gambar -->
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <h3 style="font-size: 24px; font-weight: bold; color: white; margin: 0; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">
                                    <?php echo htmlspecialchars($mobil['nama_aset']); ?>
                                </h3>
                                <span style="padding: 6px 15px; border-radius: 20px; font-size: 14px; font-weight: bold; <?php echo $badge_style; ?> backdrop-filter: blur(5px);">
                                    ● <?php echo $status_text; ?>
                                </span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="color: #64FFDA; font-size: 16px; background: rgba(100, 255, 218, 0.2); padding: 6px 15px; border-radius: 20px; font-weight: bold;">
                                    🔢 <?php echo htmlspecialchars($mobil['plat_nomor']); ?>
                                </span>
                                <span style="color: white; font-size: 14px; background: rgba(255,255,255,0.2); padding: 6px 15px; border-radius: 20px;">
                                    🏷️ <?php echo $car_specs['type']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detail Spesifikasi -->
                    <div style="padding: 25px;">
                        <h4 style="color: #0A192F; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; display: flex; align-items: center; gap: 10px;">
                            📊 Spesifikasi Detail
                        </h4>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                            <div>
                                <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;">Tahun</div>
                                <div style="font-weight: bold; font-size: 14px; color: #0A192F;"><?php echo $car_specs['year']; ?></div>
                            </div>
                            
                            <div>
                                <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;">Bahan Bakar</div>
                                <div style="font-weight: bold; font-size: 14px; color: #0A192F;"><?php echo $car_specs['fuel']; ?></div>
                            </div>
                            
                            <div>
                                <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;">Kapasitas Mesin</div>
                                <div style="font-weight: bold; font-size: 14px; color: #0A192F;"><?php echo $car_specs['cc']; ?></div>
                            </div>
                            
                            <div>
                                <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;">Transmisi</div>
                                <div style="font-weight: bold; font-size: 14px; color: #0A192F;"><?php echo $car_specs['transmission']; ?></div>
                            </div>
                            
                            <div>
                                <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;">Kapasitas Penumpang</div>
                                <div style="font-weight: bold; font-size: 14px; color: #0A192F;"><?php echo $car_specs['seats']; ?> Kursi</div>
                            </div>
                            
                            <div>
                                <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;">Warna</div>
                                <div style="font-weight: bold; font-size: 14px; color: #0A192F;"><?php echo $car_specs['color']; ?></div>
                            </div>
                            
                            <div>
                                <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;">Kondisi</div>
                                <div style="font-weight: bold; font-size: 14px; color: #27ae60;">Baik</div>
                            </div>
                            
                            <div>
                                <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;">Status</div>
                                <div style="font-weight: bold; font-size: 14px; color: <?php echo $status_color; ?>;">
                                    <?php echo $status_text; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fitur -->
                        <div style="margin-bottom: 25px;">
                            <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 10px;">Fitur Utama</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <?php foreach($car_specs['features'] as $feature): ?>
                                <span style="background: rgba(100, 255, 218, 0.1); color: #64FFDA; padding: 5px 12px; border-radius: 15px; font-size: 12px;">
                                    ✅ <?php echo $feature; ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <?php if($sedang_dipinjam && !empty($mobil['peminjam_sekarang'])): ?>
                        <div style="background: rgba(255, 140, 0, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #FF8C00;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="color: #FF8C00; font-size: 18px;">👤</span>
                                <div>
                                    <div style="font-size: 12px; color: #666;">Sedang dipinjam oleh:</div>
                                    <div style="font-weight: bold; color: #FF8C00; font-size: 16px;"><?php echo htmlspecialchars($mobil['peminjam_sekarang']); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 15px; margin-top: 20px;">
                            <!-- LINK DETAIL MOBIL -->
                            <a href="detail_mobil.php?id=<?php echo $mobil['id_aset']; ?>" style="flex: 1; padding: 15px; background: #64FFDA; color: #0A192F; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 16px; text-decoration: none;">
                                👁️ Detail & Form
                            </a>
                            
                            <?php if(!$sedang_dipinjam && $status == 'tersedia'): ?>
                            
                           
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="font-size: 60px; margin-bottom: 20px; color: #ddd;">🚫</div>
                    <h3 style="color: #0A192F; margin-bottom: 10px; font-size: 20px;">Tidak ada mobil tersedia</h3>
                    <p style="color: #666;">Belum ada data mobil yang terdaftar dalam sistem.</p>
                </div>
                <?php endif; ?>
                <?php } catch(PDOException $e) { ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="font-size: 60px; margin-bottom: 20px; color: #ddd;">⚠️</div>
                    <h3 style="color: #0A192F; margin-bottom: 10px; font-size: 20px;">Terjadi Kesalahan</h3>
                    <p style="color: #666;">Gagal memuat data mobil</p>
                </div>
                <?php } ?>
            </div>
            
            <!-- Info Footer -->
            <div style="margin-top: 30px; background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 10px;">
                    <span style="color: #64FFDA; font-size: 20px;">ℹ️</span>
                    <h4 style="color: #0A192F; margin: 0;">Informasi Penting</h4>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; text-align: left;">
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #64FFDA;">
                        <div style="font-weight: bold; color: #0A192F; margin-bottom: 5px;">Total Unit Mobil</div>
                        <div style="font-size: 24px; font-weight: bold; color: #0A192F;"><?php echo count($mobils); ?> Unit</div>
                    </div>
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #27ae60;">
                        <div style="font-weight: bold; color: #0A192F; margin-bottom: 5px;">Mobil Tersedia</div>
                        <div style="font-size: 24px; font-weight: bold; color: #27ae60;">
                            <?php echo count(array_filter($mobils, function($m) { 
                                return $m['status_aset'] == 'tersedia' && 
                                       !isset($m['status_pinjam']); 
                            })); ?> Unit
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>