# PANDUAN PENGOPERASIAN DASHBOARD ADMINISTRATOR
# JAWA MART — TOKO ONLINE SEMBAKO

Dokumen ini merupakan panduan lengkap bagi pemilik toko dan staf administrator dalam mengelola operasional harian website toko online **JAWA MART**.

---

## 📑 DAFTAR ISI
1. [Akses & Keamanan Login Admin](#1-akses--keamanan-login-admin)
2. [Dashboard Overview](#2-dashboard-overview)
3. [Manajemen Produk Sembako](#3-manajemen-produk-sembako)
4. [Manajemen Kategori](#4-manajemen-kategori)
5. [Manajemen Stok & Riwayat Mutasi](#5-manajemen-stok--riwayat-mutasi)
6. [Manajemen Pesanan & Alur Status](#6-manajemen-pesanan--alur-status)
7. [Pengaturan Toko & Nomor WhatsApp](#7-pengaturan-toko--nomor-whatsapp)
8. [Manajemen Banner Promosi](#8-manajemen-banner-promosi)
9. [Laporan Penjualan & Ekspor Excel](#9-laporan-penjualan--ekspor-excel)
10. [Panduan Backup dan Pemulihan Data](#10-panduan-backup-dan-pemulihan-data)

---

## 1. Akses & Keamanan Login Admin

### 1.1 Cara Masuk ke Dashboard
1. Buka browser dan ketik alamat URL: `https://domain-anda.com/admin/login.php`.
2. Masukkan username atau email administrator.
3. Masukkan password, lalu klik **Masuk ke Dashboard**.

> 💡 **Kredensial Awal:**
> - Username: `admin`
> - Password: `admin123`
> *(Segera ganti password Anda setelah pertama kali login).*

### 1.2 Keamanan Sesi & Rate Limiting
- Sistem dilengkapi pembatasan percobaan login (**Rate Limiting**). Jika terjadi 5 kali kegagalan login berturut-turut, sistem akan mengunci akses selama 15 menit untuk melindungi dari serangan brute-force.
- Halaman admin diproteksi secara ketat. Pengunjung publik yang mencoba membuka direktori admin tanpa login akan otomatis dialihkan ke halaman login.
- Klik tombol **Logout** pada menu sebelah kiri setiap kali selesai bekerja.

---

## 2. Dashboard Overview

Setelah login, Anda akan disambut oleh halaman ringkasan performa toko:
* **Omzet Selesai:** Total nilai rupiah transaksi yang sudah berstatus *Selesai*.
* **Pesanan Baru:** Jumlah pesanan yang baru masuk dan menunggu konfirmasi WhatsApp toko.
* **Sedang Diproses:** Pesanan yang sedang dikemas atau dalam perjalanan pengiriman kurir.
* **Katalog Produk:** Total produk sembako yang aktif di etalase.
* **Grafik Penjualan 7 Hari:** Grafik batang visual omzet harian.
* **Peringatan Stok Menipis & Habis:** Menampilkan barang yang stoknya di bawah batas minimum agar Anda dapat segera memesan ke distributor/supplier.
* **Pesanan Terbaru:** 6 transaksi paling akhir yang masuk ke sistem.

---

## 3. Manajemen Produk Sembako

Menu: **Katalog & Inventori → Kelola Produk**

### 3.1 Menambah Produk Baru
1. Klik tombol **+ Tambah Produk Baru** di kanan atas.
2. Isi formulir data produk:
   - **Kategori:** Pilih kategori sembako yang sesuai.
   - **SKU:** Kode unik barang (dapat dikosongkan untuk auto-generate dari sistem).
   - **Nama Produk:** Nama lengkap barang (contoh: *Beras Premium Ramos Wangi 5 Kg*).
   - **Deskripsi:** Keterangan mutu, rasa, butiran, atau saran penyimpanan.
   - **Harga Normal:** Harga pokok penjualan normal.
   - **Harga Diskon (Opsional):** Jika produk sedang promo, isi harga diskon (harus lebih kecil dari harga normal). Di website akan otomatis muncul badge diskon merah persen.
   - **Satuan:** Pilih satuan kemasan (*Kg, Liter, Pack, Dus, Pcs, Karung, dll*).
   - **Stok Awal:** Masukkan jumlah fisik barang yang tersedia saat ini. Sistem otomatis mencatat log mutasi *Stok Awal*.
   - **Batas Minimum Stok:** Batas ambang peringatan jika stok menipis (standar: 5).
   - **Foto Produk:** Unggah foto dengan rasio 1:1 persegi (Maksimal 2 MB, format JPG/PNG/WebP/SVG). Foto akan langsung tampil pada kotak preview.
   - **Tandai Unggulan:** Centang jika ingin produk tampil di bagian Rekomendasi Beranda.
   - **Status Aktif:** Centang agar produk langsung tayang di katalog pembeli.
3. Klik **Simpan Produk Baru**.

### 3.2 Mengedit Produk
1. Pada tabel daftar produk, klik tombol **Edit**.
2. Ubah data atau harga yang diinginkan.
3. Jika ingin mengganti foto, pilih file baru pada input foto. Foto lama akan tetap tersimpan aman sampai foto baru sukses diunggah.
4. Klik **Simpan Perubahan**.

### 3.3 Menghapus Produk (Soft Delete yang Aman)
- Klik tombol **Hapus** pada baris produk yang ingin dihapus.
- **Perlindungan Integritas:** Produk yang sudah memiliki riwayat transaksi pesanan akan diarsipkan (**Soft Delete**) dengan aman. Produk tidak akan tampil lagi di katalog pembeli, namun riwayat nota pesanan lama pelanggan tidak akan rusak atau hilang.

---

## 4. Manajemen Kategori

Menu: **Katalog & Inventori → Kategori Sembako**

- **Tambah Kategori:** Buat kategori baru dengan nama, deskripsi, dan upload gambar ikon kategori.
- **Edit Kategori:** Ubah nama atau ganti ikon kategori.
- **Proteksi Hapus Kategori:** Kategori yang masih memiliki produk sembako aktif tidak dapat dihapus sembarangan untuk mencegah produk kehilangan induk kategori. Pindahkan produk terlebih dahulu atau nonaktifkan kategori.

---

## 5. Manajemen Stok & Riwayat Mutasi

Menu: **Katalog & Inventori → Manajemen Stok**

### 5.1 Memantau Stok
Halaman ini menampilkan seluruh produk yang diurutkan mulai dari stok paling sedikit:
- **Badge Merah (Habis):** Stok bernilai 0. Tombol beli di frontend otomatis terkunci menjadi "Habis".
- **Badge Kuning (Menipis):** Stok &le; batas minimum stok.
- **Badge Hijau (Aman):** Stok masih melimpah.

### 5.2 Melakukan Penyesuaian / Restock Barang
1. Klik tombol **+ Penyesuaian / Tambah Stok Baru** (atau klik *Atur Stok* pada baris produk).
2. Pilih produk sembako.
3. Pilih jenis penyesuaian:
   - **+ Penambahan Stok:** Untuk barang masuk baru dari supplier / restock.
   - **- Pengurangan Stok:** Untuk barang rusak, bocor kemasan, kadaluarsa, atau hilang.
   - **= Tetapkan Stok Baru:** Untuk mencocokkan stok fisik berdasarkan hasil *Stock Opname* berkala.
4. Masukkan jumlah perubahan dan tuliskan alasan pada kolom catatan.
5. Klik **Simpan Penyesuaian Stok**.

### 5.3 Audit Trail (Riwayat Mutasi)
Setiap pergerakan stok (stok awal, restock, pengurangan manual, pengurangan otomatis karena pesanan dikonfirmasi, dan pengembalian akibat pembatalan) tercatat lengkap dan tidak dapat dimanipulasi, memuat:
- Tanggal dan jam kejadian.
- Nama produk & SKU.
- Jenis transaksi mutasi.
- Jumlah (+ / -).
- Stok sebelum dan stok sesudah.
- Nomor pesanan terkait & nama administrator yang bertugas.

---

## 6. Manajemen Pesanan & Alur Status

Menu: **Transaksi & Analisis → Pesanan Toko**

### 6.1 Alur Transaksi Pemesanan
```
Pelanggan Checkout di Web 
       ↓ (Tersimpan ke DB MySQL & Pesan WA Terbentuk)
Status: "Menunggu Konfirmasi" (Stok belum dipotong)
       ↓ (Admin cek chat WhatsApp pembeli)
Admin ubah status ke: "Dikonfirmasi" atau "Diproses"
       ↓ (Sistem otomatis memotong stok gudang secara atomik)
Admin serahkan paket ke kurir: "Dalam Pengiriman"
       ↓ (Barang diterima pembeli)
Admin selesaikan pesanan: "Selesai" (Omzet masuk ke laporan)
```

### 6.2 Aturan Pembatalan Pesanan
- Jika pelanggan membatalkan pesanan yang **sudah dikonfirmasi/diproses** (stok sudah terlanjur dipotong):
  - Ubah status pesanan menjadi **"Dibatalkan"**.
  - Sistem secara otomatis dan atomik akan **mengembalikan jumlah stok** ke gudang dan mencatat log mutasi *Pengembalian karena Pembatalan*.
  - Sistem memiliki flag proteksi (`stock_deducted = 0`) sehingga stok tidak akan pernah dikembalikan berulang kali meskipun status diubah kembali.

### 6.3 Menghubungi Pembeli via WhatsApp
Pada halaman daftar pesanan maupun detail pesanan, klik tombol **💬 Chat WhatsApp Pelanggan**. Browser Anda akan langsung membuka jendela chat WhatsApp ke nomor pelanggan dengan format sapaan konfirmasi otomatis.

### 6.4 Mencetak Nota Pesanan
Pada detail pesanan, klik tombol **🖨️ Cetak Nota Pesanan**. Tampilan nota berformat resmi JAWA MART lengkap dengan logo, rincian barang, ongkir, total pembayaran, dan kolom tanda tangan siap dicetak atau disimpan sebagai PDF.

---

## 7. Pengaturan Toko & Nomor WhatsApp

Menu: **Tampilan & Konfigurasi → Pengaturan Toko**

### 7.1 Mengubah Nomor WhatsApp Resmi Toko
1. Masukkan nomor WhatsApp baru pada kotak **Nomor WhatsApp Resmi Toko** (contoh: `087812122735` atau `6287812122735`).
2. Klik **Simpan Seluruh Pengaturan Toko**.
3. **Hasil Seketika:** Seluruh tombol WhatsApp di halaman beranda, tombol floating melayang di pojok kanan bawah, tombol konsultasi detail produk, dan alur checkout otomatis menggunakan nomor WhatsApp baru tersebut!

### 7.2 Mengatur Tarif Ongkos Kirim
- **Ongkos Kirim Default:** Tarif pengiriman standar toko (contoh: Rp 10.000).
- **Minimal Belanja Gratis Ongkir:** Batas belanja agar pelanggan mendapatkan promo gratis ongkir otomatis di keranjang (contoh: Rp 150.000). Ketik `0` jika promo gratis ongkir ingin dinonaktifkan.

### 7.3 Mengganti Password Administrator
1. Pada kartu sebelah kanan **Ganti Password Admin**, masukkan password lama Anda.
2. Masukkan password baru (minimal 6 karakter) dan ulangi konfirmasi.
3. Klik **Perbarui Password**.

---

## 8. Manajemen Banner Promosi

Menu: **Tampilan & Konfigurasi → Banner Promosi**

- **Tambah Banner:** Unggah spanduk promosi beresolusi 1200 &times; 450 pixel, atur judul promo, teks tombol (misal: *Belanja Sekarang*), dan link tujuan (*products.php*).
- **Urutan Tampil:** Tentukan nomor urut banner (1, 2, 3...) untuk mengatur slide spanduk di slider beranda.
- **Status:** Aktifkan atau nonaktifkan banner tanpa harus menghapusnya.

---

## 9. Laporan Penjualan & Ekspor Excel

Menu: **Transaksi & Analisis → Laporan Penjualan**

### 9.1 Memfilter Periode Penjualan
Pilih periode pada dropdown:
- **Hari Ini / Kemarin**
- **Minggu Ini**
- **Bulan Ini / Bulan Sebelumnya**
- **Tahun Ini**
- **Rentang Tanggal Khusus:** Tentukan tanggal awal dan tanggal akhir secara bebas.

### 9.2 Mengunduh Laporan ke Excel (CSV)
1. Atur rentang tanggal yang diinginkan.
2. Klik tombol hijau **📥 Unduh Excel / CSV**.
3. File laporan penjualan akan langsung terunduh ke komputer Anda dengan nama file berformat:
   `Laporan-Penjualan-JAWAMART_YYYY-MM-DD_sd_YYYY-MM-DD.csv`
4. File dilengkapi kode UTF-8 BOM sehingga dapat dibuka langsung dengan rapi di **Microsoft Excel**, **Google Sheets**, atau **LibreOffice Calc** tanpa kendala format angka atau karakter.

---

## 10. Panduan Backup dan Pemulihan Data

### 10.1 Backup Database Berkala
1. Buka **cPanel → phpMyAdmin**.
2. Pilih database `jawa_mart`.
3. Klik tab **Export**, pilih metode **Quick**, format **SQL**, lalu klik **Export**.
4. Simpan file cadangan `.sql` di tempat yang aman (Google Drive atau hard disk terpisah).

### 10.2 Backup File Gambar Produk
1. Buka **cPanel → File Manager**.
2. Masuk ke folder website Anda, lalu buka folder `uploads/`.
3. Kompres folder `uploads/` menjadi file ZIP (misal: `backup_uploads_tgl.zip`).
4. Unduh file ZIP tersebut ke komputer Anda.

### 10.3 Pemulihan Data (Restore)
Jika sewaktu-waktu server hosting mengalami gangguan:
1. Impor kembali file cadangan database SQL melalui phpMyAdmin.
2. Ekstrak kembali folder gambar `uploads/` ke direktori website Anda.
3. Website akan langsung pulih seperti kondisi saat backup terakhir dilakukan.
