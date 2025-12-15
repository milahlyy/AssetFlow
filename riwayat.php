<?php
require_once 'auth_check.php';
checkRole(['supir', 'satpam']);

require_once 'database/db.php';

// Tambahkan data riwayat peminjaman untuk 2 mobil
$data_riwayat = [
    // Data untuk Toyota Avanza (ID 1)
    [
        'id_aset' => 1,
        'mobil' => 'Toyota Avanza',
        'plat' => 'B 1234 CD',
        'peminjam' => 'Pegawai Satu',
        'driver' => 'Pak Supir',
        'tanggal_pinjam' => '2024-03-01',
        'tanggal_kembali' => '2024-03-01',
        'jam_keluar' => '08:30:00',
        'jam_masuk' => '17:00:00',
        'km_awal' => 5000,
        'km_akhir' => 5120,
        'keterangan' => 'Meeting dengan klien di Bandung',
        'status' => 'returned'
    ],
    [
        'id_aset' => 1,
        'mobil' => 'Toyota Avanza',
        'plat' => 'B 1234 CD',
        'peminjam' => 'Pegawai Satu',
        'driver' => NULL,
        'tanggal_pinjam' => '2024-03-03',
        'tanggal_kembali' => '2024-03-03',
        'jam_keluar' => '09:00:00',
        'jam_masuk' => '16:30:00',
        'km_awal' => 5120,
        'km_akhir' => 5200,
        'keterangan' => 'Survey lokasi project',
        'status' => 'returned'
    ],
    [
        'id_aset' => 1,
        'mobil' => 'Toyota Avanza',
        'plat' => 'B 1234 CD',
        'peminjam' => 'Pegawai Satu',
        'driver' => 'Pak Supir',
        'tanggal_pinjam' => '2024-03-05',
        'tanggal_kembali' => '2024-03-05',
        'jam_keluar' => '07:30:00',
        'jam_masuk' => '18:00:00',
        'km_awal' => 5200,
        'km_akhir' => 5350,
        'keterangan' => 'Kunjungan ke supplier',
        'status' => 'returned'
    ],
    [
        'id_aset' => 1,
        'mobil' => 'Toyota Avanza',
        'plat' => 'B 1234 CD',
        'peminjam' => 'Pegawai Satu',
        'driver' => NULL,
        'tanggal_pinjam' => '2024-03-08',
        'tanggal_kembali' => '2024-03-08',
        'jam_keluar' => '10:00:00',
        'jam_masuk' => '15:30:00',
        'km_awal' => 5350,
        'km_akhir' => 5400,
        'keterangan' => 'Ambil dokumen di notaris',
        'status' => 'returned'
    ],
    // Data untuk Innova Reborn (ID 2)
    [
        'id_aset' => 2,
        'mobil' => 'Innova Reborn',
        'plat' => 'B 5678 EF',
        'peminjam' => 'Pegawai Satu',
        'driver' => 'Pak Supir',
        'tanggal_pinjam' => '2024-03-02',
        'tanggal_kembali' => '2024-03-02',
        'jam_keluar' => '07:00:00',
        'jam_masuk' => '18:30:00',
        'km_awal' => 12000,
        'km_akhir' => 12150,
        'keterangan' => 'Perjalanan ke Cirebon',
        'status' => 'returned'
    ],
    [
        'id_aset' => 2,
        'mobil' => 'Innova Reborn',
        'plat' => 'B 5678 EF',
        'peminjam' => 'Pegawai Satu',
        'driver' => NULL,
        'tanggal_pinjam' => '2024-03-04',
        'tanggal_kembali' => '2024-03-04',
        'jam_keluar' => '08:00:00',
        'jam_masuk' => '17:00:00',
        'km_awal' => 12150,
        'km_akhir' => 12200,
        'keterangan' => 'Meeting di kantor pusat',
        'status' => 'returned'
    ],
    [
        'id_aset' => 2,
        'mobil' => 'Innova Reborn',
        'plat' => 'B 5678 EF',
        'peminjam' => 'Pegawai Satu',
        'driver' => 'Pak Supir',
        'tanggal_pinjam' => '2024-03-06',
        'tanggal_kembali' => '2024-03-07',
        'jam_keluar' => '08:00:00',
        'jam_masuk' => '17:30:00',
        'km_awal' => 12200,
        'km_akhir' => 12400,
        'keterangan' => 'Meeting dengan investor (menginap)',
        'status' => 'returned'
    ],
    [
        'id_aset' => 2,
        'mobil' => 'Innova Reborn',
        'plat' => 'B 5678 EF',
        'peminjam' => 'Pegawai Satu',
        'driver' => NULL,
        'tanggal_pinjam' => '2024-03-09',
        'tanggal_kembali' => '2024-03-09',
        'jam_keluar' => '09:30:00',
        'jam_masuk' => '16:00:00',
        'km_awal' => 12400,
        'km_akhir' => 12480,
        'keterangan' => 'Antar jemput tamu dari bandara',
        'status' => 'returned'
    ],
    // Data peminjaman aktif
    
];

// Filter data berdasarkan parameter GET
$filter_status = $_GET['status'] ?? '';
$filter_mobil = $_GET['mobil'] ?? '';
$filter_tanggal = $_GET['tanggal'] ?? '';

$riwayat_filtered = $data_riwayat;

if ($filter_status) {
    $riwayat_filtered = array_filter($riwayat_filtered, function($item) use ($filter_status) {
        return $item['status'] == $filter_status;
    });
}

if ($filter_mobil) {
    $riwayat_filtered = array_filter($riwayat_filtered, function($item) use ($filter_mobil) {
        return $item['id_aset'] == $filter_mobil;
    });
}

if ($filter_tanggal) {
    $riwayat_filtered = array_filter($riwayat_filtered, function($item) use ($filter_tanggal) {
        return $item['tanggal_pinjam'] == $filter_tanggal;
    });
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - AssetFlow</title>
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
                📋 Riwayat Peminjaman
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
                        <a href="galeri_mobil.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 8px;">
                            🚗 <span>Galeri Mobil</span>
                        </a>
                    </li>
                    <li style="margin-bottom: 5px;">
                        <a href="riwayat.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #64FFDA; text-decoration: none; border-radius: 8px; background: rgba(100, 255, 218, 0.2); font-weight: bold;">
                            📋 <span>Riwayat</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Sidebar Laporan DIHAPUS sesuai permintaan -->
            
            <div style="margin-top: auto; padding: 0 10px;">
                <a href="logout.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #FF6B6B; text-decoration: none; border-radius: 8px; background: rgba(255, 107, 107, 0.1);">
                    🚪 <span>Keluar</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div style="flex: 1; padding: 30px;">
            
            <!-- Filter Section -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="color: #0A192F; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    🔍 Filter Riwayat
                </h3>
                
                <form method="GET" action="riwayat.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #0A192F;">Status Peminjaman</label>
                        <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                            <option value="">Semua Status</option>
                            <option value="returned" <?php echo $filter_status == 'returned' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="on_loan" <?php echo $filter_status == 'on_loan' ? 'selected' : ''; ?>>Sedang Dipinjam</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #0A192F;">Mobil</label>
                        <select name="mobil" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                            <option value="">Semua Mobil</option>
                            <option value="1" <?php echo $filter_mobil == '1' ? 'selected' : ''; ?>>Toyota Avanza (B 1234 CD)</option>
                            <option value="2" <?php echo $filter_mobil == '2' ? 'selected' : ''; ?>>Innova Reborn (B 5678 EF)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #0A192F;">Tanggal</label>
                        <input type="date" name="tanggal" value="<?php echo htmlspecialchars($filter_tanggal); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                    </div>
                    
                    <div style="display: flex; gap: 10px; align-items: flex-end;">
                        <button type="submit" style="padding: 10px 25px; background: #64FFDA; color: #0A192F; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            🔍 Terapkan Filter
                        </button>
                        <a href="riwayat.php" style="padding: 10px 25px; background: #ddd; color: #333; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                            🔄 Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Statistik Ringkas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <?php
                $total_peminjaman = count($riwayat_filtered);
                $peminjaman_selesai = array_filter($riwayat_filtered, function($item) {
                    return $item['status'] == 'returned';
                });
                $peminjaman_aktif = array_filter($riwayat_filtered, function($item) {
                    return $item['status'] == 'on_loan';
                });
                
                // Hitung jarak tempuh total
                $jarak_tempuh_total = 0;
                foreach($riwayat_filtered as $item) {
                    if($item['km_awal'] && $item['km_akhir']) {
                        $jarak_tempuh_total += ($item['km_akhir'] - $item['km_awal']);
                    }
                }
                ?>
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #0A192F;">
                    <div style="font-size: 32px; font-weight: bold; color: #0A192F;"><?php echo $total_peminjaman; ?></div>
                    <div style="color: #666; font-size: 14px;">Total Peminjaman</div>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #27ae60;">
                    <div style="font-size: 32px; font-weight: bold; color: #27ae60;"><?php echo count($peminjaman_selesai); ?></div>
                    <div style="color: #666; font-size: 14px;">Selesai</div>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #FF8C00;">
                    <div style="font-size: 32px; font-weight: bold; color: #FF8C00;"><?php echo count($peminjaman_aktif); ?></div>
                    <div style="color: #666; font-size: 14px;">Aktif</div>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #64FFDA;">
                    <div style="font-size: 32px; font-weight: bold; color: #64FFDA;"><?php echo $jarak_tempuh_total; ?> km</div>
                    <div style="color: #666; font-size: 14px;">Total Jarak Tempuh</div>
                </div>
            </div>

            <!-- Tabel Riwayat -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="padding: 25px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="color: #0A192F; margin: 0; display: flex; align-items: center; gap: 10px;">
                        📋 Data Riwayat Peminjaman (<?php echo count($riwayat_filtered); ?> Data)
                    </h3>
                    <div style="color: #666; font-size: 14px;">
                        <?php echo date('d F Y'); ?>
                    </div>
                </div>
                
                <?php if(count($riwayat_filtered) > 0): ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
                        <thead>
                            <tr style="background: #0A192F; color: white;">
                                <th style="padding: 15px; text-align: left; font-weight: bold;">No</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Tanggal</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Mobil</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Peminjam</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Supir</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Jam</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">KM</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Status</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($riwayat_filtered as $index => $row): 
                                // Format badge status
                                $badge_style = '';
                                $status_text = '';
                                switch($row['status']) {
                                    case 'returned':
                                        $badge_style = 'background: rgba(46, 204, 113, 0.2); color: #27ae60;';
                                        $status_text = 'Selesai';
                                        break;
                                    case 'on_loan':
                                        $badge_style = 'background: rgba(52, 152, 219, 0.2); color: #2980b9;';
                                        $status_text = 'Berjalan';
                                        break;
                                }
                                
                                // Hitung jarak tempuh
                                $jarak_tempuh = '';
                                if($row['km_awal'] && $row['km_akhir']) {
                                    $jarak_tempuh = ($row['km_akhir'] - $row['km_awal']) . ' km';
                                } elseif($row['km_awal']) {
                                    $jarak_tempuh = $row['km_awal'] . ' km';
                                }
                            ?>
                            <tr style="border-bottom: 1px solid #f0f0f0; <?php echo $index % 2 == 0 ? 'background: #f8f9fa;' : ''; ?>">
                                <td style="padding: 15px; font-weight: bold; color: #0A192F;"><?php echo $no++; ?></td>
                                <td style="padding: 15px;">
                                    <div style="font-weight: bold; color: #0A192F;"><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></div>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="font-weight: bold;"><?php echo htmlspecialchars($row['mobil']); ?></div>
                                    <div style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($row['plat']); ?></div>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="font-weight: bold;"><?php echo htmlspecialchars($row['peminjam']); ?></div>
                                </td>
                                <td style="padding: 15px;">
                                    <?php if($row['driver']): ?>
                                    <div style="color: #FF8C00; font-weight: bold;"><?php echo htmlspecialchars($row['driver']); ?></div>
                                    <?php else: ?>
                                    <span style="color: #999; font-size: 14px;">Tidak ada supir</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px;">
                                    <?php if($row['jam_keluar']): ?>
                                    <div><strong>Keluar:</strong> <?php echo date('H:i', strtotime($row['jam_keluar'])); ?></div>
                                    <?php endif; ?>
                                    <?php if($row['jam_masuk']): ?>
                                    <div><strong>Masuk:</strong> <?php echo date('H:i', strtotime($row['jam_masuk'])); ?></div>
                                    <?php else: ?>
                                    <div><strong>Masuk:</strong> <span style="color: #FF8C00;">-</span></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="font-weight: bold; color: #0A192F;"><?php echo $jarak_tempuh ?: '-'; ?></div>
                                    <?php if($row['km_awal'] && $row['km_akhir']): ?>
                                    <div style="font-size: 11px; color: #666;"><?php echo $row['km_awal']; ?> → <?php echo $row['km_akhir']; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px;">
                                    <span style="display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; <?php echo $badge_style; ?>">
                                        ● <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="font-size: 13px;">
                                        <?php echo htmlspecialchars($row['keterangan']); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="padding: 20px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; color: #666; font-size: 14px;">
                    <div>
                        Menampilkan <?php echo count($riwayat_filtered); ?> data riwayat peminjaman
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <div style="width: 12px; height: 12px; background: rgba(46, 204, 113, 0.2); border: 2px solid #27ae60; border-radius: 50%;"></div>
                            <span>Selesai</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <div style="width: 12px; height: 12px; background: rgba(52, 152, 219, 0.2); border: 2px solid #2980b9; border-radius: 50%;"></div>
                            <span>Berjalan</span>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 60px; color: #666;">
                    <div style="font-size: 48px; margin-bottom: 20px; color: #ddd;">📭</div>
                    <h4 style="color: #0A192F; margin-bottom: 10px; font-size: 18px;">Tidak ada data riwayat</h4>
                    <p>Tidak ditemukan data riwayat peminjaman yang sesuai dengan filter.</p>
                    <a href="riwayat.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #64FFDA; color: #0A192F; text-decoration: none; border-radius: 6px; font-weight: bold;">
                        🔄 Tampilkan Semua Data
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Info Footer -->
            <div style="margin-top: 30px; background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h4 style="color: #0A192F; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    ℹ️ Informasi Riwayat Peminjaman
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #0A192F;">
                        <div style="font-weight: bold; color: #0A192F; margin-bottom: 5px;">Toyota Avanza</div>
                        <div style="font-size: 13px; color: #666;">
                            Total peminjaman: <?php echo count(array_filter($riwayat_filtered, function($item) { return $item['id_aset'] == 1; })); ?>
                        </div>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #64FFDA;">
                        <div style="font-weight: bold; color: #0A192F; margin-bottom: 5px;">Innova Reborn</div>
                        <div style="font-size: 13px; color: #666;">
                            Total peminjaman: <?php echo count(array_filter($riwayat_filtered, function($item) { return $item['id_aset'] == 2; })); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>