# **Project Blueprint: AssetFlow (Sistem Informasi Peminjaman Aset)**

Dokumen ini adalah rancangan pengembangan sistem final berdasarkan analisis dokumen "Laporan Akhir Kelompok 4", "Jurnal PT XYZ", dan diskusi penyempurnaan fitur (Booking & Dashboard Operasional).

## **1\. Identitas Project & Tech Stack**

* **Nama Project:** AssetFlow  
* **Tujuan:** Mengelola peminjaman aset (Mobil & Elektronik), persetujuan, pelacakan status, dan penjadwalan (booking).  
* **Frontend:** HTML5, CSS3 (Native \+ Custom Variables), Vanilla JS (ES6+).  
* **Backend:** PHP 8 (Native/Procedural atau OOP Sederhana).  
* **Database:** MySQL (Relational).  
* **Server:** XAMPP/MAMP.

### **Desain & Color Palette**

Menggunakan CSS Variables untuk konsistensi:

* **Primary (Navy):** \#0A192F (Background Sidebar, Header, Teks Utama)  
* **Secondary (Cyan):** \#64FFDA (Highlight, Hover States, Active Menu)  
* **Accent (Orange):** \#FF8C00 (Tombol Aksi Penting: Pinjam, Approve, Login)  
* **Neutral:** \#F8F9FA (Background Body), \#FFFFFF (Card Container).

## **2\. Aktor & Workflow (Alur Kerja)**

### **A. Pegawai (User Umum)**

1. **Login:** Masuk ke sistem.  
2. **Melihat Katalog:** Melihat daftar **seluruh** aset (Mobil/Elektronik). Aset yang "Sedang Dipinjam" tetap tampil agar bisa dibooking untuk tanggal lain.  
3. **Mengajukan Peminjaman (Booking):** Mengisi formulir tanggal & durasi.  
   * *Logic:* User input tanggal \-\> Klik Kirim \-\> Backend cek bentrok \-\> Jika aman, simpan sebagai "Pending". Jika bentrok, tolak & tampilkan pesan error.  
4. **Dashboard Aktif:** Melihat barang yang sedang dibawa dan status pengajuan (Menunggu/Disetujui/Ditolak).  
5. **Pengembalian:** Melakukan konfirmasi "Kembalikan Aset" dari dashboard.

### **B. HRGA (Admin/Approver)**

1. **Kelola Aset (CRUD):** Tambah, Edit, Hapus data aset perusahaan.  
2. **Validasi Peminjaman:** Menerima notifikasi \-\> Cek jadwal \-\> Approve (Assign Driver jika perlu) / Reject (Isi alasan).  
3. **Monitoring:** Melihat "Siapa pegang apa" secara real-time.  
4. **Laporan:** Rekap data peminjaman.

### **C. Satpam (Gatekeeper \- Khusus Mobil)**

1. **Dashboard Mobil:** Melihat daftar mobil yang dijadwalkan keluar/masuk hari ini.  
2. **Cek Detail:** Klik mobil untuk melihat detail ("Siapa yang pinjam hari ini?").  
3. **Log Operasional:** Mengupdate status jam\_keluar dan jam\_masuk langsung pada data peminjaman.

### **D. Supir (Driver \- Khusus Peminjaman dengan Supir)**

1. **Dashboard Tugas:** Melihat daftar peminjaman di mana dirinya ditugaskan (driver\_id).  
2. **Log Kondisi:** Mengupdate km\_awal, km\_akhir, dan kondisi\_mobil pada data peminjaman.

## **3\. Struktur Halaman & Fitur (Site Map)**

### **Auth (Umum)**

* login.php \- Halaman masuk.  
* logout.php \- Script logout.

### **Halaman Pegawai**

* index.php (Dashboard) \- **Fokus Status Aktif**.  
  * Tabel "Sedang Saya Pinjam" (Ada tombol "Kembalikan").  
  * Tabel "Menunggu Persetujuan".  
* katalog\_aset.php \- Galeri semua aset. Filter kategori (Mobil/Elektronik). Status aset ditampilkan (Tersedia/Dipinjam), tapi tombol "Pinjam" selalu aktif untuk booking masa depan.  
* form\_peminjaman.php \- Form detail. Menangkap id\_aset dari URL. Validasi tanggal dilakukan saat submit (Backend).  
* riwayat\_saya.php \- Arsip sejarah peminjaman (Selesai/Ditolak).

### **Halaman HRGA (Admin)**

* admin\_dashboard.php \- Statistik & Quick Action (Approve/Reject terbaru).  
* kelola\_aset.php \- Tabel CRUD Aset.  
* persetujuan.php \- Daftar request "Pending". Form Approve memiliki dropdown untuk memilih Supir (opsional) dan field teks untuk alasan jika Reject.  
* laporan.php \- Laporan filter tanggal & kategori.

### **Halaman Operasional (Satpam & Supir)**

* dashboard\_operasional.php \- Galeri khusus aset **Mobil**. Menampilkan status visual (Hijau \= Di kantor, Merah \= Keluar).  
* detail\_mobil.php \- Halaman satu mobil.  
  * Info Mobil & Peminjam.  
  * **Satpam:** Tombol "Catat Keluar" / "Catat Masuk" (Update jam\_keluar/jam\_masuk).  
  * **Supir:** Form Input KM & Kondisi (Update km\_awal/km\_akhir).

## **4\. Skema Database (MySQL)**

Berikut adalah desain tabel final (Simplified) untuk mendukung fitur booking dan log operasional tanpa tabel log terpisah.

Nama Database: assetflow

**1\. users**

* id\_user (INT, PK, AI)  
* nama (VARCHAR)  
* email (VARCHAR, Unique)  
* password (VARCHAR)  
* role (ENUM: 'pegawai', 'hrga', 'satpam', 'supir')  
* divisi (VARCHAR)

**2\. assets**

* id\_aset (INT, PK, AI)  
* nama\_aset (VARCHAR)  
* kategori (ENUM: 'mobil', 'elektronik')  
* plat\_nomor (VARCHAR, Nullable)  
* status\_aset (ENUM: 'tersedia', 'maintenance', 'rusak') \-- *Status fisik*  
* gambar (VARCHAR)

**3\. loans** (Transaksi Booking & Peminjaman)

* id\_loan (INT, PK, AI)  
* id\_user (INT, FK)  
* id\_aset (INT, FK)  
* driver\_id (INT, FK, Nullable) \-- *Diisi HRGA jika butuh supir*  
* tgl\_pinjam (DATE) \-- *Rencana Pinjam*  
* tgl\_kembali (DATE) \-- *Rencana Kembali*  
* jam\_keluar (DATETIME, Nullable) \-- *Diisi Satpam*  
* jam\_masuk (DATETIME, Nullable) \-- *Diisi Satpam*  
* km\_awal (INT, Nullable) \-- *Diisi Supir*  
* km\_akhir (INT, Nullable) \-- *Diisi Supir*  
* kondisi\_mobil (TEXT, Nullable) \-- *Diisi Supir/Satpam*  
* keterangan (TEXT)  
* alasan\_penolakan (TEXT, Nullable) \-- *Diisi HRGA jika reject*  
* status\_loan (ENUM: 'pending', 'approved', 'rejected', 'on\_loan', 'returned')

## **5\. Rencana Pengembangan (Step-by-Step)**

1. **Fase 1: Database & Koneksi:** Membuat assetflow.sql dan db.php.  
2. **Fase 2: Auth System:** Login, Logout, Session Check berdasarkan Role.  
3. **Fase 3: Core Pegawai:** Katalog Aset, Form Booking (dengan validasi tanggal), Dashboard Pegawai.  
4. **Fase 4: Core HRGA:** Approval Workflow (Assign Driver), CRUD Aset.  
5. **Fase 5: Core Operasional:** Dashboard Mobil, Log Keluar/Masuk & KM (Update table Loans).  
6. **Fase 6: UI/UX:** Polishing dengan CSS (Navy/Cyan/Orange).

