# TXeyoo POS

Aplikasi penjualan (POS/kasir) dan administrasi toko sederhana berbasis **Laravel + Livewire 3 + Tailwind CSS + MySQL**.
Seluruh antarmuka, validasi, dan format uang menggunakan Bahasa Indonesia dan Rupiah. Zona waktu aplikasi: **Asia/Jakarta**.

---

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Kebutuhan Sistem](#kebutuhan-sistem)
- [Instalasi](#instalasi)
- [Akun Demo](#akun-demo)
- [Perintah Penting](#perintah-penting)
- [Menjalankan Pengujian](#menjalankan-pengujian)
- [Aturan Bisnis Utama](#aturan-bisnis-utama)
- [Keputusan Desain & Catatan](#keputusan-desain--catatan)
- [Troubleshooting](#troubleshooting)

---

## Fitur

### Kasir (`kasir`)

- **POS / Kasir**
  - Cari produk berdasarkan **nama, SKU, atau barcode** (scan barcode didukung).
  - Keranjang dengan batas jumlah sesuai stok.
  - Diskon **nominal** atau **persen**.
  - Pajak opsional per transaksi (persen diambil dari pengaturan toko).
  - Pelanggan opsional + **tambah pelanggan cepat** dari layar kasir.
  - Pembayaran **tunai, transfer, atau QRIS**.
  - Kembalian dihitung otomatis untuk pembayaran tunai.
  - Struk dapat dicetak **58 mm / 80 mm**.
- **Katalog Produk** — daftar produk aktif, harga, dan stok.
- **Pelanggan** — lihat dan tambah pelanggan.
- **Riwayat Transaksi** — hanya transaksi milik kasir yang sedang login, lengkap dengan detail dan cetak ulang struk.

### Admin (`admin`)

- **Dashboard** — ringkasan penjualan hari ini, jumlah transaksi, produk stok menipis, dan produk terlaris.
- **Produk** — CRUD produk, unggah gambar, filter kategori, filter stok menipis, aktif/nonaktif.
- **Kategori** — CRUD kategori.
- **Manajemen Stok** — restock dan penyesuaian stok dengan alasan; seluruh perubahan tercatat di `stock_movements`.
- **Pelanggan** — CRUD pelanggan.
- **Pengguna** — CRUD pengguna; akun dinonaktifkan (bukan dihapus) agar riwayat transaksi tetap utuh.
- **Riwayat Transaksi** — semua transaksi, detail, **void** (stok dikembalikan), dan cetak ulang struk.
- **Laporan** — ringkasan penjualan, penjualan per hari, produk terlaris, dan performa kasir; dapat difilter tanggal dan **diekspor ke CSV**.
- **Pengaturan Toko** — nama, alamat, telepon, persen pajak, dan footer struk.

Hak akses **bukan hanya menyembunyikan menu**. Setiap route admin dilindungi middleware `role:admin`, dan setiap aksi juga diperiksa melalui **Policy/Gate** di sisi server.

---

## Teknologi

| Komponen | Versi |
| --- | --- |
| PHP | 8.3+ (diuji pada PHP 8.5) |
| Laravel | 13.x |
| Livewire | 3.8 |
| Tailwind CSS | 4.x (via Vite) |
| Database | MySQL / MariaDB (XAMPP), SQLite untuk pengujian |
| Build tool | Vite 8, Node.js 20+ |

---

## Kebutuhan Sistem

- PHP **8.3+** dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`.
- Composer 2.
- Node.js 20+ dan npm.
- MySQL/MariaDB (disarankan melalui **XAMPP**).

---

## Instalasi

### 1. Siapkan database MySQL (XAMPP)

1. Jalankan **XAMPP Control Panel** → start **Apache** dan **MySQL**.
2. Buka `http://localhost/phpmyadmin`.
3. Buat database baru bernama **`aplikasi_penjualan`** (collation `utf8mb4_unicode_ci`).

Atau lewat terminal MySQL:

```sql
CREATE DATABASE aplikasi_penjualan
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

> Konfigurasi default menggunakan user `root` tanpa password, sesuai bawaan XAMPP.
> Jika MySQL Anda memakai password, ubah `DB_PASSWORD` pada `.env`.

### 2. Siapkan aplikasi

```bash
composer install
copy .env.example .env        # Windows
# cp .env.example .env        # macOS / Linux

php artisan key:generate
```

Sesuaikan `.env` bila perlu:

```dotenv
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aplikasi_penjualan
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migrasi dan isi data demo

```bash
php artisan migrate --seed
```

Untuk mengulang dari nol:

```bash
php artisan migrate:fresh --seed
```

### 4. Tautkan penyimpanan gambar produk

```bash
php artisan storage:link
```

### 5. Build aset frontend

```bash
npm install
npm run build
```

Saat pengembangan, jalankan `npm run dev` di terminal terpisah.

### 6. Jalankan aplikasi

```bash
php artisan serve
```

Buka **http://localhost:8000**.

---

## Akun Demo

Dibuat otomatis oleh seeder:

| Peran | Email | Kata Sandi |
| --- | --- | --- |
| Admin | `admin@txeyoo.test` | `password` |
| Kasir | `kasir@txeyoo.test` | `password` |

Data demo lain: 5 kategori, 25 produk, 5 pelanggan, dan pengaturan toko “TXeyoo” dengan pajak 11%.

> Ganti kata sandi akun demo sebelum dipakai di lingkungan nyata.

---

## Perintah Penting

```bash
# Migrasi + data demo
php artisan migrate --seed

# Reset database + data demo
php artisan migrate:fresh --seed

# Menjalankan server lokal
php artisan serve

# Build aset produksi
npm run build

# Pengembangan aset (hot reload)
npm run dev

# Menjalankan seluruh test
php artisan test

# Merapikan format kode (Laravel Pint)
vendor/bin/pint
```

---

## Menjalankan Pengujian

```bash
php artisan test
```

Pengujian menggunakan **SQLite in-memory** (lihat `phpunit.xml`), sehingga tidak mengubah database MySQL Anda.

Cakupan pengujian antara lain:

- Perhitungan subtotal, diskon nominal/persen, pajak, total, dan kembalian.
- Penolakan transaksi bila jumlah melebihi stok.
- Penolakan pembayaran tunai yang kurang.
- Snapshot nama, SKU, dan harga produk pada `sale_items`.
- Nomor invoice unik dan berurutan per hari.
- Void transaksi mengembalikan stok dan mencatat pergerakan stok.
- Kasir tidak dapat mengakses halaman admin maupun transaksi kasir lain.
- Penyesuaian stok dan validasinya.
- Smoke test seluruh halaman admin, kasir, detail transaksi, dan struk.

---

## Aturan Bisnis Utama

### Transaksi

- Uang disimpan sebagai **integer rupiah** (tanpa desimal).
- Diskon persen dibulatkan ke bawah; total pajak dibulatkan ke rupiah terdekat.
- Pajak hanya dikenakan pada `(subtotal - diskon)`.
- Pembayaran non-tunai otomatis dianggap pas sebesar total; kembalian `0`.
- Checkout berjalan dalam **satu database transaction**.
- Produk yang terlibat dikunci dengan `lockForUpdate()` dan diurutkan berdasarkan `id` untuk mencegah race condition/deadlock.
- Nomor invoice berformat **`INV-YYYYMMDD-0001`**, unik per hari, dibuat aman untuk transaksi bersamaan melalui tabel `invoice_sequences`.
- Nama, SKU, dan harga produk **di-snapshot** ke `sale_items`, sehingga riwayat tidak berubah walau produk diubah/dihapus.
- Void hanya dapat dilakukan pada transaksi berstatus `completed`; stok dikembalikan dan dicatat sebagai pergerakan stok.

### Produk & Stok

- Stok **tidak dapat diubah langsung** dari form edit produk. Semua perubahan stok harus melalui Manajemen Stok agar tercatat.
- Stok awal saat membuat produk dicatat sebagai pergerakan **restock**.
- Produk menggunakan **soft delete** agar riwayat transaksi tetap valid.
- Produk nonaktif tidak muncul di POS dan tidak dapat dijual.

### Hak Akses

- Middleware `role:admin` melindungi seluruh route `/admin/*`.
- Policy/Gate memeriksa ulang setiap aksi (create/update/delete/view).
- Kasir hanya melihat transaksi miliknya sendiri, termasuk saat membuka detail dan struk.

---

## Keputusan Desain & Catatan

Beberapa keputusan diambil karena spesifikasi tidak menyebutkan detail tertentu:

1. **Livewire dipasang manual.** Starter kit resmi Livewire ditujukan untuk aplikasi baru, sedangkan proyek ini dikembangkan di atas skeleton Laravel yang sudah ada. Login dibuat sebagai komponen Livewire tersendiri dengan rate limiting (5 percobaan per menit).
2. **Tabel `invoice_sequences`.** Nomor invoice harian dibuat melalui tabel sequence terkunci, bukan dengan menghitung `MAX()` pada tabel `sales`, agar tetap aman saat ada transaksi bersamaan.
3. **Pengguna tidak dihapus permanen.** Admin hanya dapat menonaktifkan pengguna. Penghapusan permanen akan merusak relasi riwayat transaksi.
4. **Stok tidak diedit langsung dari form produk.** Perubahan stok wajib melalui Manajemen Stok/aksi penyesuaian agar audit trail lengkap.
5. **Pajak bersifat opsional per transaksi.** Nilai persen diambil dari pengaturan toko; kasir dapat menyalakan/mematikan pajak pada transaksi tertentu.
6. **Pembayaran non-tunai dianggap pas.** Untuk transfer dan QRIS, `paid_amount` diset sama dengan total dan kembalian `0`.
7. **Format struk 58 mm/80 mm** hanya mengatur lebar kertas pada CSS cetak; tidak ada integrasi langsung ke driver printer thermal.
8. **Ekspor CSV** menggunakan endpoint admin terpisah agar dapat diunduh langsung, dan tetap dilindungi middleware serta policy.
9. **Tidak ada fitur di luar permintaan** (misalnya program loyalitas, multi-cabang, atau pembukuan) agar ruang lingkup tetap sesuai.
10. **Pencarian POS dibatasi 24 hasil** untuk menjaga performa dan keterbacaan layar kasir. Gunakan barcode/SKU untuk hasil yang lebih spesifik.

---

## Troubleshooting

### `Vite manifest not found at .../public/build/manifest.json`

Aset belum dibangun. Jalankan:

```bash
npm install
npm run build
```

Saat pengembangan gunakan `npm run dev`.

### Gagal terhubung ke MySQL

- Pastikan **MySQL** di XAMPP sudah berjalan.
- Pastikan database `aplikasi_penjualan` sudah dibuat.
- Periksa `DB_HOST`, `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`.
- Jika port 3306 dipakai instalasi MySQL lain, sesuaikan `DB_PORT`.

### Halaman 500 setelah mengubah `.env`

Bersihkan cache konfigurasi:

```bash
php artisan optimize:clear
```

### Gambar produk tidak tampil

Jalankan:

```bash
php artisan storage:link
```

### Port 8000 sudah dipakai

```bash
php artisan serve --port=8080
```

Lalu sesuaikan `APP_URL` di `.env`.

---

## Lisensi

Aplikasi ini dibangun di atas Laravel dan dirilis dengan lisensi MIT.
