-- Database: assetflow

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pegawai','hrga','satpam','supir') NOT NULL,
  `divisi` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users` (Default Accounts)
-- Password defaults: 'password' (Hashed with password_hash)
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`, `divisi`) VALUES
(1, 'Admin HRGA', 'hrga@kantor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hrga', 'HR & GA'),
(2, 'Pegawai Satu', 'pegawai@kantor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pegawai', 'Marketing'),
(3, 'Pak Satpam', 'satpam@kantor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'satpam', 'Security'),
(4, 'Pak Supir', 'supir@kantor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supir', 'Operasional');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id_aset` int(11) NOT NULL,
  `nama_aset` varchar(100) NOT NULL,
  `kategori` enum('mobil','elektronik') NOT NULL,
  `plat_nomor` varchar(20) DEFAULT NULL,
  `status_aset` enum('tersedia','maintenance','rusak') NOT NULL DEFAULT 'tersedia',
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id_aset`, `nama_aset`, `kategori`, `plat_nomor`, `status_aset`, `gambar`) VALUES
(1, 'Toyota Avanza', 'mobil', 'B 1234 CD', 'tersedia', 'avanza.jpg'),
(2, 'Innova Reborn', 'mobil', 'B 5678 EF', 'tersedia', 'innova.jpg'),
(3, 'Laptop Dell Latitude', 'elektronik', NULL, 'tersedia', 'laptop_dell.jpg'),
(4, 'Projector Epson', 'elektronik', NULL, 'tersedia', 'projector.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id_loan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_aset` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_kembali` date NOT NULL,
  `jam_keluar` datetime DEFAULT NULL,
  `jam_masuk` datetime DEFAULT NULL,
  `km_awal` int(11) DEFAULT NULL,
  `km_akhir` int(11) DEFAULT NULL,
  `kondisi_mobil` text DEFAULT NULL,
  `keterangan` text NOT NULL,
  `alasan_penolakan` text DEFAULT NULL,
  `status_loan` enum('pending','approved','rejected','on_loan','returned') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id_aset`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id_loan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_aset` (`id_aset`),
  ADD KEY `driver_id` (`driver_id`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `assets`
  MODIFY `id_aset` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `loans`
  MODIFY `id_loan` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`id_aset`) REFERENCES `assets` (`id_aset`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

COMMIT;

-- SKENARIO 1: BARU DISETUJUI (Siap Berangkat)
-- Satpam: Perlu isi Jam Keluar.
-- Supir: Perlu isi KM Awal (KM Keluar).
INSERT INTO loans (id_loan, id_user, id_aset, driver_id, tgl_pinjam, tgl_kembali, jam_keluar, jam_masuk, km_awal, km_akhir, kondisi_mobil, keterangan, status_loan) VALUES
(1, 2, 1, 4, CURDATE(), CURDATE() + INTERVAL 1 DAY, NULL, NULL, NULL, NULL, NULL, 'Dinas ke Bandung (Siap Berangkat)', 'approved');

-- SKENARIO 2: SEDANG BERJALAN (Sudah Keluar)
-- Sudah ada Jam Keluar (08:00) dan KM Awal (50000).
-- Satpam: Perlu isi Jam Masuk (Saat pulang).
-- Supir: Perlu isi KM Akhir (KM Masuk).
INSERT INTO loans (id_loan, id_user, id_aset, driver_id, tgl_pinjam, tgl_kembali, jam_keluar, jam_masuk, km_awal, km_akhir, kondisi_mobil, keterangan, status_loan) VALUES
(2, 2, 2, 4, CURDATE(), CURDATE(), CONCAT(CURDATE(), ' 08:00:00'), NULL, 50000, NULL, 'Bodi mulus, bensin full', 'Meeting di Jakarta (Sedang Jalan)', 'on_loan');

INSERT INTO assets (id_aset, nama_aset, kategori, plat_nomor, status_aset, gambar) VALUES
(5, 'Iphone 17', 'elektronik', NULL, 'tersedia', 'Iphone.jpg'),
(6, 'Kamera Canon', 'elektronik', NULL, 'maintenance', 'canon.jpg'),
(7, 'Kamera Olympus', 'elektronik', NULL, 'tersedia', 'olympus.jpg');