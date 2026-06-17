# AssetFlow

AssetFlow adalah sistem informasi peminjaman aset berbasis web untuk mengelola aset kantor, pengajuan peminjaman, persetujuan HRGA, serta log operasional kendaraan oleh satpam dan supir.

Project ini dibuat dengan PHP native, MySQL, HTML, dan CSS tanpa framework tambahan.

## Fitur Utama

- Login berdasarkan role pengguna.
- Dashboard pegawai untuk melihat status peminjaman aktif dan pengajuan.
- Katalog aset dengan filter kategori.
- Form pengajuan peminjaman aset.
- Riwayat peminjaman pegawai.
- Dashboard HRGA untuk statistik dan aktivitas terbaru.
- Kelola aset dan kelola user.
- Persetujuan atau penolakan pengajuan peminjaman.
- Laporan peminjaman berdasarkan tanggal, status, dan kategori.
- Dashboard operasional untuk satpam dan supir.
- Log jam keluar/masuk kendaraan oleh satpam.
- Log KM dan kondisi kendaraan oleh supir.

## Role Pengguna

| Role | Akses |
| --- | --- |
| `pegawai` | Mengajukan peminjaman, melihat katalog aset, melihat riwayat, mengembalikan aset |
| `hrga` | Mengelola aset/user, menyetujui/menolak peminjaman, melihat laporan |
| `satpam` | Mencatat jam keluar dan jam masuk kendaraan |
| `supir` | Mencatat KM awal, KM akhir, dan kondisi mobil |

## Struktur Folder

```text
AssetFlow/
|-- assets/img/              # Gambar aset
|-- css/                     # Stylesheet halaman
|-- database/
|   |-- assetflow.sql        # Schema dan seed database
|   `-- db.php               # Koneksi database
|-- docs/                    # Dokumen rancangan project
|-- admin_dashboard.php      # Dashboard HRGA
|-- auth_check.php           # Cek login, timeout session, helper role
|-- dashboard_operasional.php
|-- form_peminjaman.php
|-- form_satpam.php
|-- form_supir.php
|-- index.php                # Dashboard pegawai
|-- katalog_aset.php
|-- kelola_aset.php
|-- kelola_user.php
|-- laporan.php
|-- login.php
|-- logout.php
|-- persetujuan.php
|-- riwayat.php
`-- riwayat_saya.php
```

## Kebutuhan

- PHP 8.x
- MySQL atau MariaDB
- Web server lokal seperti Apache dari XAMPP/MAMP/Laragon

## Cara Menjalankan

1. Clone atau salin repository ini ke folder web server lokal.

   Contoh XAMPP:

   ```text
   C:\xampp\htdocs\AssetFlow
   ```

2. Buat database MySQL dengan nama:

   ```sql
   assetflow
   ```

3. Import file SQL:

   ```text
   database/assetflow.sql
   ```

4. Sesuaikan konfigurasi database di `database/db.php`.

   Default saat ini:

   ```php
   $host = 'localhost';
   $dbname = 'assetflow';
   $username = 'root';
   $password = '';
   ```

5. Buka aplikasi melalui browser:

   ```text
   http://localhost/AssetFlow/login.php
   ```

## Akun Default

Password default untuk akun seed adalah:

```text
password
```

| Role | Email |
| --- | --- |
| HRGA | `hrga@kantor.com` |
| Pegawai | `pegawai@kantor.com` |
| Satpam | `satpam@kantor.com` |
| Supir | `supir@kantor.com` |

## Halaman Penting

| Halaman | Keterangan |
| --- | --- |
| `login.php` | Halaman login |
| `index.php` | Dashboard pegawai |
| `katalog_aset.php` | Katalog aset untuk pegawai |
| `admin_dashboard.php` | Dashboard HRGA |
| `kelola_aset.php` | Manajemen aset |
| `kelola_user.php` | Manajemen user |
| `persetujuan.php` | Persetujuan peminjaman |
| `laporan.php` | Laporan peminjaman |
| `dashboard_operasional.php` | Dashboard satpam/supir |
| `galeri_mobil.php` | Galeri kendaraan |
| `riwayat.php` | Riwayat operasional |

## Validasi Syntax PHP

Jalankan command berikut dari PowerShell untuk mengecek syntax seluruh file PHP di root project:

```powershell
Get-ChildItem -LiteralPath 'D:\[0]_lifeline\miaomiao\github\AssetFlow' -Filter '*.php' -File | ForEach-Object { php -l $_.FullName }
```

## Catatan Development

- Gunakan prepared statement PDO untuk query yang menerima input user.
- Selalu panggil `auth_check.php` dan `checkrole([...])` pada halaman yang butuh proteksi role.
- Escape output dari database/user dengan `htmlspecialchars`.
- Gunakan POST untuk aksi tambah, edit, hapus, approve, reject, dan return.
- Hindari mempercayai hidden input tanpa validasi ulang di backend.
- Validasi upload gambar sebelum menyimpan file ke `assets/img/`.

## Status Project

Project ini masih berbentuk aplikasi PHP native sederhana dan belum memiliki automated test suite, Composer setup, atau migration system. Schema utama masih dikelola melalui `database/assetflow.sql`.
