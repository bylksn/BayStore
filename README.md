# 🏪 BayStore - Sistem Manajemen Toko Buku Berbasis OOP

BayStore adalah aplikasi Sistem Manajemen Toko Buku berbasis web yang dibangun menggunakan PHP Native dengan menerapkan konsep **Pemrograman Berorientasi Objek (OOP)** secara mendalam. Proyek ini dibuat sebagai pemenuhan tugas Ujian Akhir Semester (UAS) mata kuliah Konsep Bahasa Pemrograman.

## 🚀 Fitur Utama

- **Dashboard Statistik:** Menampilkan metrik total buku, member, transaksi sukses, dan total pendapatan toko.
- **Manajemen Inventaris (Buku):** Menambahkan, melihat, dan menghapus data buku beserta pelacakan sisa stok.
- **Manajemen Keanggotaan (Member):** Pencatatan, melihat, dan menghapus data pelanggan dengan sistem otomatisasi level keanggotaan (Bronze, Silver, Gold) berdasarkan akumulasi total belanja.
- **Sistem Transaksi Kasir (Point of Sales):** 
  - Mendukung pembelian multi-item (banyak buku dalam 1 transaksi).
  - Perhitungan subtotal dan diskon secara otomatis (Silver: 5%, Gold: 10%).
  - Alur status transaksi: `Pending` → `Completed` atau `Cancelled`.
  - Sinkronisasi otomatis: Mengurangi stok buku dan menambah *total spent* member ketika transaksi dikonfirmasi.

## 🛠️ Teknologi yang Digunakan

- **Backend:** PHP 8+ (Native)
- **Konsep Arsitektur:** Object-Oriented Programming (OOP)
- **Database:** MySQL / MariaDB (ekstensi `mysqli`)
- **Frontend:** HTML5, Vanilla CSS3 (Custom Design System), JavaScript (untuk interaktivitas UI & kalkulasi DOM)
- **Icons:** Font Awesome 6

## 📚 Implementasi OOP

Proyek ini sangat kaya akan penerapan OOP, meliputi:
1. **Class & Object:** Terdapat banyak pemisahan class entitas (`Book`, `Member`, `Transaction`) dan class infrastruktur (`Database`, `DataStore`).
2. **Encapsulation:** Penggunaan *Access Modifier* (`private`, `protected`) yang ketat untuk mengontrol state objek, diakses melalui *Getter* dan aksi mutator spesifik.
3. **Inheritance:** Class `Member` adalah turunan (*extends*) dari class `Person`.
4. **Singleton Pattern:** Class `Database` dan `DataStore` menggunakan pola rancangan Singleton untuk memastikan hanya ada satu instance koneksi database atau *cache state* yang berjalan selama *request lifecycle*.
5. **Object Composition & Aggregation:** Objek `Transaction` berisi banyak objek `Book` (dalam bentuk keranjang) dan terkait ke satu objek `Member`.

## ⚙️ Cara Instalasi & Menjalankan Aplikasi

1. **Clone Repository / Ekstrak File**
   Letakkan folder proyek ini (misal: `baystore_uas`) di dalam folder *document root* web server lokal Anda:
   - Jika XAMPP: `C:\xampp\htdocs\baystore_uas\`
   - Jika Laragon: `C:\laragon\www\baystore_uas\`

2. **Setup Database**
   - Buka `http://localhost/phpmyadmin`.
   - Buat database baru bernama `baystore`.
   - Lakukan **Import** menggunakan file `database.sql` yang telah disediakan di folder *root* proyek.

3. **Konfigurasi**
   Buka file `config.php` dan pastikan kredensial database sudah sesuai dengan komputer Anda.
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Sesuaikan jika ada password
   define('DB_NAME', 'baystore');
   ```

4. **Jalankan Aplikasi**
   Buka browser Anda dan akses:
   👉 `http://localhost/baystore_uas/`

## 📖 Alur Kerja Aplikasi (Workflow)

Aplikasi ini bersifat *standalone kasir* (tanpa login admin). Alur umumnya adalah:
1. Daftarkan **Buku** terlebih dahulu di menu Buku.
2. Daftarkan **Member** di menu Member.
3. Masuk ke menu **Transaksi**. Pilih Member dan Buku yang dibeli.
4. Klik *Proses Transaksi*. Transaksi akan tersimpan dengan status **Pending**.
5. Klik **✓ Konfirmasi** pada tabel Riwayat Transaksi. Stok akan berkurang otomatis dan level member akan dihitung ulang secara sistem.

---
*Dibuat untuk keperluan UAS Konsep Bahasa Pemrograman.*
