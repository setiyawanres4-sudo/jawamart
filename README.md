# JAWA MART — Website Toko Online Sembako (Versi 2.0)

**JAWA MART** adalah aplikasi web toko online modern, cepat, dan responsif yang dirancang khusus untuk kebutuhan toko sembako dan bahan pokok harian. Sistem memungkinkan pelanggan memesan barang tanpa perlu registrasi akun, dan secara otomatis menghubungkan pesanan dengan **WhatsApp Resmi Toko** dengan rincian rapi sambil tetap mencatat seluruh transaksi ke dalam database MySQL.

---

## 🌾 Identitas & Kontak Brand

* **Nama Toko:** JAWA MART
* **Tagline:** *Belanja Sembako Mudah, Hemat, dan Praktis.*
* **Nomor WhatsApp Resmi Awal:** `+62 878-1212-2735` (Format Sistem: `6287812122735`)
* **Link WhatsApp:** [https://wa.me/6287812122735](https://wa.me/6287812122735)

> 💡 **Penting:** Nomor WhatsApp tersimpan secara dinamis di database toko dan dapat diubah kapan saja melalui **Dashboard Admin → Pengaturan Toko**. Seluruh tombol WhatsApp di website otomatis mengikuti nomor yang tersimpan di pengaturan.

---

## 🚀 Fitur Utama

### 🛒 Pelanggan (Frontend)
1. **Belanja Cepat Tanpa Akun:** Pelanggan dapat langsung memilih barang, mengatur kuantitas, dan checkout tanpa registrasi berbelit-belit.
2. **Katalog Sembako Dinamis:** Menampilkan foto produk (rasio 1:1), nama, satuan (Kg, Liter, Pcs, dll), harga normal, harga diskon, dan informasi stok.
3. **Pencarian & Filter Cepat:** Cari produk berdasarkan kata kunci nama atau SKU, filter menurut kategori, dan urutkan harga terendah/tertinggi.
4. **Detail Produk & Galeri:** Informasi lengkap produk, deskripsi mutu, stok tersedia, batas maksimum kuantitas, dan tombol tanya langsung via WhatsApp.
5. **Keranjang Belanja LocalStorage:** Tersimpan di browser pelanggan secara persisten dan divalidasi ulang di sisi server saat checkout.
6. **Checkout & Integrasi WhatsApp Otomatis:**
   - Memvalidasi harga dan ketersediaan stok secara atomik langsung dari database server.
   - Menghasilkan kode unik pesanan berformat `JM-XXXXXXXX`.
   - Menghitung ongkir default atau gratis ongkir jika belanja mencapai nominal tertentu.
   - Menyimpan pesanan dan snapshot barang ke database.
   - Membuka WhatsApp resmi JAWA MART dengan pesan siap kirim berformat rapi.

### 💼 Administrator (Dashboard)
1. **Keamanan Login:** Menggunakan enkripsi `password_hash()` (bcrypt) dan `password_verify()`, session regeneration, serta pembatasan percobaan login (rate limiting).
2. **Dashboard Overview:**
   - Kartu statistik: Total Produk, Total Kategori, Total Pesanan, Pesanan Baru, Pesanan Diproses, Pesanan Selesai, dan Total Omzet Penjualan.
   - Grafik penjualan visual (menggunakan HTML5 Canvas ringan tanpa dependensi luar).
   - Peringatan stok menipis (&le; batas minimum) dan stok habis.
   - Tabel pesanan pelanggan terbaru.
3. **Manajemen Produk (CRUD):** Tambah, edit, aktifkan/nonaktifkan, tandai unggulan, dan soft delete untuk menjaga keutuhan riwayat transaksi.
4. **Manajemen Kategori (CRUD):** Tambah kategori baru dengan upload ikon foto, edit deskripsi, dan proteksi penghapusan jika kategori masih menampung produk aktif.
5. **Manajemen Stok & Riwayat Mutasi (Audit Trail):**
   - Tambah stok masuk (restock), kurangi stok rusak, atau tetapkan stok opname fisik.
   - Log mutasi lengkap mencatat: Produk, jenis transaksi, jumlah perubahan, stok sebelum, stok sesudah, catatan, dan nama administrator.
6. **Manajemen Pesanan & Status:**
   - 6 Status Pesanan: `Menunggu Konfirmasi`, `Dikonfirmasi`, `Diproses`, `Dalam Pengiriman`, `Selesai`, `Dibatalkan`.
   - **Aturan Stok Atomik:** Mengonfirmasi pesanan otomatis mengurangi stok fisik gudang; membatalkan pesanan otomatis mengembalikan stok (diproteksi agar tidak berulang).
   - Riwayat status tersimpan abadi sebagai jejak audit.
   - Tombol instan chat WhatsApp ke nomor pelanggan.
   - Cetak nota / faktur pesanan siap print/PDF.
7. **Laporan & Analisis Penjualan:**
   - Filter periode: Hari ini, Kemarin, Minggu ini, Bulan ini, Bulan sebelumnya, Tahun ini, atau Rentang tanggal khusus.
   - Metrik Total Omzet, Total Transaksi, Produk Terjual, Rata-rata Nilai Transaksi (AOV), Produk Terlaris, dan Produk Paling Sedikit Terjual.
   - **Export Excel / CSV** dengan UTF-8 BOM untuk kompatibilitas penuh Microsoft Excel.
   - Cetak ringkasan laporan siap tanda tangan.
8. **Pengaturan Banner Promosi:** Unggah spanduk promo slider, atur teks tombol dan link tujuan.
9. **Pengaturan Toko & Ongkir:** Ubah nama toko, nomor WhatsApp, alamat, email, jam operasional, tarif ongkir default, batas gratis ongkir, dan ganti password admin.

---

## 🛠️ Arsitektur & Teknologi

* **Backend:** PHP Native 8.1+ (Menggunakan PDO, Strict Types, Prepared Statements)
* **Database:** MySQL 8.0+ / MariaDB (Engine InnoDB, Charset `utf8mb4`, Foreign Keys, Transaksi Atomik)
* **Frontend:** HTML5 Semantik, CSS3 Modern (Mobile-First Responsive Grid/Flexbox), Vanilla JavaScript (ES6+)
* **Penyimpanan Klien:** JavaScript `LocalStorage` untuk keranjang belanja
* **Kompatibilitas Hosting:** Shared Hosting cPanel, VPS, Localhost (XAMPP / Laragon) tanpa memerlukan Node.js atau framework berat.

---

## 📂 Struktur Direktori Proyek

```
JAWAMART/
├── .htaccess                       # Konfigurasi Apache (keamanan, kompresi, proteksi)
├── README.md                       # Dokumentasi umum proyek
├── INSTALLATION.md                 # Panduan instalasi shared hosting cPanel
├── ADMIN-GUIDE.md                  # Panduan pengoperasian dashboard administrator
├── robots.txt                      # Direktif crawler mesin pencari
├── sitemap.xml                     # Peta situs SEO
│
├── index.php                       # Beranda toko (banner slider, kategori, unggulan, terbaru)
├── products.php                    # Katalog lengkap (pencarian, filter kategori, sorting, pagination)
├── product-detail.php              # Halaman detail produk sembako & galeri
├── categories.php                  # Halaman daftar seluruh kategori sembako
├── cart.php                        # Halaman keranjang belanja
├── checkout.php                    # Form pemesanan & server validation
├── order-success.php               # Halaman sukses order & trigger WhatsApp
├── about.php                       # Profil & keunggulan JAWA MART
├── contact.php                     # Informasi kontak, alamat toko & form pesan
│
├── config/
│   └── database.php                # Koneksi database PDO aman & konfigurasi cPanel
│
├── database/
│   └── jawa_mart.sql               # Skrip database siap impor (bebas CREATE DATABASE/USE)
│
├── includes/
│   ├── functions.php               # Fungsi inti, helpers, format rupiah/tanggal, WA builder, upload
│   ├── auth.php                    # Autentikasi admin, proteksi rate-limiting, session regeneration
│   ├── header.php                  # Komponen header, navigasi, search, keranjang
│   └── footer.php                  # Komponen footer & floating WhatsApp button
│
├── api/
│   └── cart-validate.php           # API validasi keranjang belanja real-time
│
├── assets/
│   ├── css/
│   │   ├── style.css               # Desain frontend responsive sesuai palet warna PRD
│   │   └── admin.css               # Desain dashboard administrator modern
│   ├── js/
│   │   ├── main.js                 # Slider banner, mobile drawer menu, flash alert
│   │   ├── cart.js                 # Engine keranjang LocalStorage & event dispatcher
│   │   └── admin.js                # Canvas sales chart, preview upload, konfirmasi
│   └── images/
│       ├── logo.svg                # Logo resmi JAWA MART
│       ├── favicon.svg             # Favicon tab browser
│       └── placeholder.svg         # Placeholder default produk sembako
│
├── uploads/
│   ├── .htaccess                   # Proteksi ketat larangan eksekusi skrip PHP
│   ├── products/                   # Foto produk sembako (1:1 ratio)
│   ├── categories/                 # Ikon kategori sembako
│   └── banners/                    # Gambar banner spanduk promosi
│
└── admin/
    ├── index.php                   # Dashboard overview & statistik utama
    ├── login.php                   # Halaman login administrator
    ├── logout.php                  # Logout & pembersihan sesi
    ├── includes/
    │   ├── header.php              # Sidebar & topbar admin
    │   └── footer.php              # Footer admin
    ├── products/                   # CRUD Produk & Soft Delete
    │   ├── index.php
    │   ├── create.php
    │   ├── edit.php
    │   └── delete.php
    ├── categories/                 # CRUD Kategori & Proteksi Relasi
    │   ├── index.php
    │   ├── create.php
    │   ├── edit.php
    │   └── delete.php
    ├── stock/                      # Manajemen Stok & Riwayat Mutasi
    │   ├── index.php
    │   └── adjust.php
    ├── orders/                     # Manajemen Pesanan, Status Atomik & Cetak Nota
    │   ├── index.php
    │   ├── detail.php
    │   ├── update-status.php
    │   └── print.php
    ├── reports/                    # Laporan Penjualan, Filter Tanggal, Export Excel
    │   ├── index.php
    │   ├── export-excel.php
    │   └── print.php
    ├── banners/                    # CRUD Banner Promosi Beranda
    │   ├── index.php
    │   ├── create.php
    │   ├── edit.php
    │   └── delete.php
    └── settings/                   # Pengaturan Toko, WhatsApp, Ongkir & Ganti Password
        └── index.php
```

---

## 🔑 Akun Administrator Awal

* **URL Login Admin:** `http://domain-anda.com/admin/login.php`
* **Username:** `admin`
* **Password Sementara:** `admin123`

*(Harap segera mengganti password sementara melalui menu **Pengaturan Toko → Ganti Password Admin** setelah berhasil masuk).*
