-- Migration for existing AssetFlow databases created from the older schema.
-- Run this once after backing up the database.

START TRANSACTION;

ALTER TABLE `users`
  ADD COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `divisi`,
  ADD COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  ADD COLUMN `deleted_at` datetime DEFAULT NULL AFTER `updated_at`,
  ADD KEY `idx_users_active_role` (`deleted_at`, `role`);

ALTER TABLE `assets`
  ADD COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `gambar`,
  ADD COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  ADD COLUMN `deleted_at` datetime DEFAULT NULL AFTER `updated_at`,
  ADD KEY `idx_assets_visible` (`deleted_at`, `status_aset`, `kategori`);

ALTER TABLE `loans`
  ADD COLUMN `returned_at` datetime DEFAULT NULL AFTER `status_loan`,
  ADD COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `returned_at`,
  ADD COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  ADD KEY `idx_loans_asset_schedule` (`id_aset`, `status_loan`, `tgl_pinjam`, `tgl_kembali`),
  ADD KEY `idx_loans_user_status` (`id_user`, `status_loan`),
  ADD KEY `idx_loans_driver_status` (`driver_id`, `status_loan`),
  ADD KEY `idx_loans_report_date` (`tgl_pinjam`, `status_loan`);

ALTER TABLE `loans`
  DROP FOREIGN KEY `loans_ibfk_1`,
  DROP FOREIGN KEY `loans_ibfk_2`;

ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`id_aset`) REFERENCES `assets` (`id_aset`) ON DELETE RESTRICT;

ALTER TABLE `loans`
  ADD CONSTRAINT `chk_loans_dates` CHECK (`tgl_kembali` >= `tgl_pinjam`),
  ADD CONSTRAINT `chk_loans_km_order` CHECK (`km_awal` IS NULL OR `km_akhir` IS NULL OR `km_akhir` >= `km_awal`);

UPDATE `assets` SET `gambar` = 'Avanza.jpg' WHERE `gambar` = 'avanza.jpg';
UPDATE `assets` SET `gambar` = 'Innova.jpg' WHERE `gambar` = 'innova.jpg';
UPDATE `assets` SET `gambar` = 'iphone.jpg' WHERE `gambar` = 'Iphone.jpg';

COMMIT;
