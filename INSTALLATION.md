# PANDUAN INSTALASI WEBSITE JAWA MART
## Shared Hosting cPanel & Localhost Environment

Dokumen ini berisi panduan teknis langkah demi langkah untuk memasang dan menjalankan website **JAWA MART — Toko Online Sembako** pada layanan **Shared Hosting cPanel** maupun lingkungan **Localhost** (XAMPP / Laragon).

---

## 📋 Persyaratan Sistem Server

| Komponen | Spesifikasi Minimum | Rekomendasi |
|---|---|---|
| **Web Server** | Apache 2.4+ / Nginx | Apache dengan mod_rewrite aktif |
| **PHP Version** | PHP 8.1 | PHP 8.2 atau 8.3 |
| **Ekstensi PHP** | PDO, pdo_mysql, mbstring, fileinfo, session, json | Lengkap (standar cPanel) |
| **Database** | MySQL 8.0+ / MariaDB 10.4+ | MariaDB 10.6+ / MySQL 8.0 |
| **Penyimpanan** | 100 MB free space | 500 MB+ untuk foto produk |
| **SSL / HTTPS** | Mendukung Let's Encrypt / AutoSSL | HTTPS Wajib Aktif |
| **Node.js / Composer** | **TIDAK DIPERLUKAN** | Native PHP murni tanpa build step |

---

## 🚀 Panduan Instalasi pada Shared Hosting cPanel

### Langkah 1: Buat Database MySQL & User di cPanel
1. Login ke panel kontrol hosting (**cPanel**) Anda.
2. Cari dan klik menu **MySQL Databases** (atau *MySQL Database Wizard*).
3. Buat nama database baru, misalnya: `cpaneluser_jawamart`.
4. Buat akun pengguna database baru (User MySQL), misalnya: `cpaneluser_jawaadmin` dengan password yang kuat. Catat nama database, username, dan password tersebut.
5. Pada bagian **Add User to Database**, hubungkan pengguna ke database tersebut.
6. Berikan centang pada **ALL PRIVILEGES** dan klik **Make Changes**.

---

### Langkah 2: Impor File Database melalui phpMyAdmin
1. Kembali ke halaman utama cPanel, lalu klik menu **phpMyAdmin**.
2. Pada panel sebelah kiri phpMyAdmin, klik nama database yang baru saja Anda buat (`cpaneluser_jawamart`).
3. Klik tab **Import** pada menu navigasi atas.
4. Pada bagian *File to import*, klik tombol **Choose File** (Pilih File) dan arahkan ke file:
   ```
   database/jawa_mart.sql
   ```
5. Pastikan format terpilih adalah **SQL**.
6. Gulir ke bawah dan klik tombol **Import** (atau **Go**).
7. Tunggu beberapa detik hingga muncul pesan hijau sukses:
   > *"Import has been successfully finished, 10 queries executed."*
8. Seluruh 10 tabel utama JAWA MART beserta data awal (kategori, sembako contoh, admin, dan pengaturan nomor WhatsApp) sekarang telah siap.

---

### Langkah 3: Unggah Source Code Website
1. Pada cPanel, buka **File Manager**.
2. Masuk ke direktori root website Anda:
   - Jika domain utama: masuk ke folder `public_html/`.
   - Jika subdomain / addon domain: masuk ke folder direktori subdomain tersebut.
3. Unggah seluruh file dan folder proyek **JAWA MART**:
   - Anda dapat mengunggah file `JAWA-MART.zip` lalu klik tombol **Extract** di File Manager, atau mengunggah folder langsung.
4. Pastikan struktur file di `public_html/` tersusun rapi (misalnya `index.php`, `.htaccess`, folder `admin/`, `config/`, `uploads/`, dll langsung berada di dalam `public_html/`).

---

### Langkah 4: Sesuaikan File Konfigurasi Database
1. Di File Manager cPanel, masuk ke folder `config/`.
2. Klik kanan pada file `database.php`, lalu pilih **Edit**.
3. Sesuaikan baris konfigurasi `$db_config` dengan kredensial cPanel yang telah dibuat pada Langkah 1:

```php
$db_config = [
    'host'     => 'localhost',              // Biasanya tetap localhost pada cPanel
    'port'     => '3306',
    'dbname'   => 'cpaneluser_jawamart',    // Nama database cPanel Anda
    'username' => 'cpaneluser_jawaadmin',   // User database cPanel Anda
    'password' => 'PasswordDatabaseAnda',   // Password user database
    'charset'  => 'utf8mb4'
];
```

4. Klik tombol **Save Changes** (Simpan Perubahan) di kanan atas.

---

### Langkah 5: Pastikan Izin Akses Folder (Permissions)
1. Pastikan folder `uploads/` dan seluruh subfoldernya (`uploads/products/`, `uploads/categories/`, `uploads/banners/`) memiliki izin akses **0755** (atau **0777** jika server menggunakan konfigurasi Apache DSO).
2. Periksa bahwa file `uploads/.htaccess` tetap ada di dalam folder `uploads/` untuk mencegah eksekusi skrip PHP yang tidak sah.

---

### Langkah 6: Aktifkan HTTPS / SSL
1. Di cPanel, buka menu **SSL/TLS Status**.
2. Pilih domain toko Anda dan klik **Run AutoSSL** jika sertifikat SSL belum terpasang.
3. Website sekarang sudah aman dan dapat diakses dengan protokol `https://domain-anda.com`.

---

### Langkah 7: Pengujian Awal & Login Administrator
1. Buka browser dan akses alamat website Anda:
   ```
   https://domain-anda.com/
   ```
2. Pastikan halaman Beranda JAWA MART tampil sempurna beserta banner promo, kategori sembako, dan daftar produk contoh.
3. Akses halaman login administrator toko:
   ```
   https://domain-anda.com/admin/login.php
   ```
4. Masukkan akun awal:
   - **Username:** `admin`
   - **Password:** `admin123`
5. Masuk ke menu **Pengaturan Toko**:
   - Sesuaikan **Nomor WhatsApp Resmi Toko** dengan nomor aktif pemilik toko.
   - Segera **Ganti Password Administrator** dengan kata sandi baru yang aman.

---

## 💻 Panduan Instalasi pada Localhost (XAMPP / Laragon)

1. **Letakkan Proyek:**
   - Salin folder proyek `JAWAMART` ke dalam direktori:
     - XAMPP: `C:\xampp\htdocs\JAWAMART\`
     - Laragon: `C:\laragon\www\JAWAMART\`
2. **Nyalakan Service:**
   - Buka XAMPP / Laragon Control Panel, klik **Start** pada modul **Apache** dan **MySQL**.
3. **Impor Database:**
   - Buka browser ke `http://localhost/phpmyadmin/`.
   - Buat database baru bernama: `jawa_mart`.
   - Klik database `jawa_mart`, buka tab **Import**, pilih file `database/jawa_mart.sql`, lalu klik **Import**.
4. **Periksa Konfigurasi:**
   - Buka file `config/database.php`.
   - Konfigurasi default sudah disesuaikan untuk localhost:
     ```php
     'host'     => 'localhost',
     'port'     => '3306',
     'dbname'   => 'jawa_mart',
     'username' => 'root',
     'password' => '', // kosong pada default XAMPP
     ```
5. **Buka Website di Browser:**
   - Frontend: `http://localhost/JAWAMART/`
   - Admin Dashboard: `http://localhost/JAWAMART/admin/login.php`

---

## ✅ Daftar Periksa (Checklist) Verifikasi Sistem

Sebelum menyerahkan toko online kepada pemilik bisnis, lakukan pengujian berikut:

- [x] Beranda dapat dibuka dan menampilkan produk dengan foto yang relevan.
- [x] Pencarian sembako dan filter kategori bekerja dinamis.
- [x] Halaman detail produk memuat data harga, stok, dan deskripsi dengan benar.
- [x] Keranjang belanja dapat menambah, mengurangi, dan menghapus barang.
- [x] Form checkout menyimpan pesanan ke database dan menghasilkan kode `JM-XXXXXXXX`.
- [x] Tombol WhatsApp membuka link `https://wa.me/{nomor}` dengan rincian pesanan yang benar.
- [x] Login admin berfungsi dan menolak kredensial yang salah.
- [x] CRUD produk dapat menambah, mengedit harga/stok, dan mengarsipkan produk.
- [x] Upload foto produk menghasilkan file fisik di folder `uploads/` dan path tersimpan di DB.
- [x] Perubahan status pesanan otomatis memotong atau mengembalikan stok secara atomik.
- [x] Ekspor laporan penjualan menghasilkan file CSV/Excel yang rapi saat dibuka di Microsoft Excel.
- [x] Nomor WhatsApp di pengaturan toko langsung mengubah seluruh tombol WA di website.
