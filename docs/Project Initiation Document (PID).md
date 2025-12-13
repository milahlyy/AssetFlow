# **Project Initiation Document (PID)**

Nama Proyek: AssetFlow (Sistem Informasi Peminjaman Aset)

Versi: 1.0

Tanggal: 12 Desember 2025

## **1\. Ringkasan Eksekutif**

**AssetFlow** adalah inisiatif pengembangan sistem informasi berbasis web untuk mendigitalkan proses peminjaman aset (kendaraan dan elektronik) yang saat ini berjalan secara semi-manual. Sistem ini dirancang untuk mengatasi masalah ketidakefisienan pencatatan, kesulitan pelacakan aset secara *real-time*, dan kurangnya transparansi status pengajuan.

Sistem ini akan memfasilitasi empat aktor utama: Pegawai (Peminjam), HRGA (Penyetuju & Admin), Satpam (Pencatat Keluar/Masuk), dan Supir (Pencatat Kondisi Kendaraan), dalam satu platform terintegrasi.

## **2\. Latar Belakang Masalah**

Berdasarkan analisis studi kasus (Laporan Kelompok 4 & PT XYZ), ditemukan masalah utama:

1. **Inefisiensi Proses:** Penggunaan Google Form dan komunikasi manual (WhatsApp) memperlambat proses persetujuan.  
2. **Kesulitan Tracking:** Sulit mengetahui posisi aset (apakah sedang dipakai, di bengkel, atau tersedia) secara instan.  
3. **Data Tidak Terpusat:** Log satpam dan supir seringkali terpisah dari data peminjaman utama, menyulitkan rekapitulasi laporan.  
4. **Risiko Aset:** Kurangnya pencatatan kondisi fisik aset sebelum dan sesudah peminjaman meningkatkan risiko kerugian aset tanpa pertanggungjawaban yang jelas.

## **3\. Tujuan Proyek**

1. **Sentralisasi Data:** Menggabungkan seluruh data aset, transaksi peminjaman, dan log operasional dalam satu database terpusat.  
2. **Otomasi Alur Kerja:** Menggantikan proses manual dengan alur kerja digital (Request \-\> Approval \-\> Execution \-\> Return).  
3. **Transparansi:** Memberikan visibilitas status ketersediaan aset secara *real-time* kepada seluruh pegawai.  
4. **Kemudahan Pelaporan:** Memudahkan HRGA dalam menarik laporan penggunaan aset untuk analisis manajemen.

## **4\. Ruang Lingkup Proyek (Scope)**

### **4.1. Fitur Utama (In-Scope)**

* **Manajemen Aset (CRUD):** Pengelolaan data master aset (Mobil & Elektronik) beserta status fisiknya.  
* **Sistem Booking & Validasi:** Formulir peminjaman dengan validasi otomatis untuk mencegah bentrok jadwal.  
* **Workflow Persetujuan:** Fitur bagi HRGA untuk menyetujui, menolak (dengan alasan), dan menugaskan supir.  
* **Dashboard Operasional:**  
  * **Pegawai:** Monitoring status pengajuan dan aset yang sedang dibawa.  
  * **Satpam:** Pencatatan waktu keluar/masuk kendaraan secara *real-time*.  
  * **Supir:** Pencatatan kilometer (KM) dan kondisi fisik kendaraan.  
* **Laporan:** Rekapitulasi riwayat peminjaman berdasarkan periode dan kategori.

### **4.2. Di Luar Ruang Lingkup (Out-of-Scope)**

* Pengembangan aplikasi *mobile native* (Android/iOS). Sistem akan berbasis web (*responsive*).  
* Integrasi dengan sistem penggajian (Payroll) atau HRIS eksternal.  
* Fitur pelacakan lokasi GPS secara *live* pada kendaraan (hanya pencatatan log waktu & KM).

## **5\. Spesifikasi Teknis**

* **Frontend:** HTML5, CSS3 (Custom Variables: Navy, Cyan, Orange), Vanilla JavaScript (ES6+).  
* **Backend:** PHP 8 (Native/Procedural atau OOP Sederhana).  
* **Database:** MySQL (Relational Database).  
* **Server Environment:** XAMPP / MAMP (Apache Web Server).  
* **Browser Support:** Modern Browsers (Chrome, Edge, Firefox, Safari).

## **6\. Struktur Organisasi Proyek & Stakeholder**

| Peran | Tanggung Jawab |
| :---- | :---- |
| **Project Manager** | Mengawasi jalannya proyek, memastikan *timeline* tercapai. |
| **System Analyst** | Menerjemahkan kebutuhan bisnis PT XYZ menjadi spesifikasi teknis. |
| **Backend Developer** | Membangun logika PHP, koneksi database, dan keamanan sistem. |
| **Frontend Developer** | Mengimplementasikan desain antarmuka (UI/UX) yang responsif. |
| **User: HRGA** | *Key User* & Administrator sistem (Pemberi persetujuan). |
| **User: Pegawai** | Pengguna akhir yang melakukan peminjaman. |
| **User: Operasional** | Satpam dan Supir sebagai pelaksana lapangan. |

## **7\. Rencana Kerja (Timeline)**

| Fase | Kegiatan Utama | Output |
| :---- | :---- | :---- |
| **Fase 1** | **Setup Awal** \- Desain Database (assetflow.sql) \- Konfigurasi Koneksi (db.php) | Struktur Database Siap, Koneksi Berhasil |
| **Fase 2** | **Sistem Autentikasi** \- Login Page \- Session Management (Role-based) | Fitur Login & Logout Berfungsi |
| **Fase 3** | **Modul Pegawai** \- Katalog Aset \- Form Booking & Validasi \- Dashboard Pegawai | Pegawai bisa *request* & lihat status |
| **Fase 4** | **Modul HRGA** \- CRUD Aset \- Approval Workflow \- Assign Driver | HRGA bisa kelola aset & validasi request |
| **Fase 5** | **Modul Operasional** \- Dashboard Satpam (Log Keluar/Masuk) \- Dashboard Supir (Log KM/Kondisi) | Log tercatat di database |
| **Fase 6** | **Finalisasi & UI/UX** \- Styling CSS (Navy/Cyan/Orange) \- Testing & Bug Fixing | Aplikasi siap pakai (Production Ready) |

## **8\. Manajemen Risiko**

| Risiko | Dampak | Strategi Mitigasi |
| :---- | :---- | :---- |
| **Perubahan Kebutuhan** | Jadwal molor | Membekukan *scope* (freeze) setelah fase desain disetujui. |
| **Kesulitan Adaptasi User** | User enggan pakai sistem | Membuat antarmuka yang sangat sederhana & intuitif (Dashboard-first). |
| **Konflik Jadwal Aset** | Data tidak konsisten | Implementasi validasi ketat di Backend PHP sebelum data disimpan. |
| **Kehilangan Data** | Hilangnya riwayat log | Backup database berkala (SQL Dump). |

## **9\. Penutup**

Dokumen ini dibuat sebagai dasar kesepakatan dimulainya pengembangan **AssetFlow**. Dengan disetujuinya dokumen ini, tahap pengembangan (Fase 1\) dapat segera dilaksanakan.

