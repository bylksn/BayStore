# 🏪 BayStore - Sistem Manajemen Toko Buku Berbasis OOP

BayStore adalah aplikasi Sistem Manajemen Toko Buku berbasis web yang dibangun menggunakan PHP Native dengan menerapkan konsep **Pemrograman Berorientasi Objek (OOP)** secara mendalam. Proyek ini dibuat sebagai pemenuhan tugas Ujian Akhir Semester (UAS) mata kuliah Konsep Bahasa Pemrograman.

## 🚀 Fitur Utama

- **Dashboard Statistik (Admin):** Menampilkan metrik total buku, member, transaksi sukses, dan total pendapatan toko.
- **Manajemen Inventaris (Buku):** Menambahkan, melihat, dan menghapus data buku beserta pelacakan sisa stok.
- **Manajemen Keanggotaan (Member):** Pencatatan, melihat, dan menghapus data pelanggan dengan sistem otomatisasi level keanggotaan (Bronze, Silver, Gold) berdasarkan akumulasi total belanja.
- **Sistem Transaksi Kasir (Point of Sales):**
  - Mendukung pembelian multi-item (banyak buku dalam 1 transaksi).
  - Perhitungan subtotal dan diskon secara otomatis (Silver: 5%, Gold: 10%).
  - Alur status transaksi: `Pending` → `Completed` atau `Cancelled`.
  - Sinkronisasi otomatis: Mengurangi stok buku dan menambah *total spent* member ketika transaksi dikonfirmasi.
- **Sistem Autentikasi (Login & Register):**
  - Halaman `login.php` untuk autentikasi dengan email & password.
  - Halaman `register.php` untuk pendaftaran member baru; akun langsung aktif dan bisa login.
  - Halaman `logout.php` untuk mengakhiri sesi.
- **Role-Based Access Control (RBAC):** Dua peran berbeda dengan akses yang dipisahkan:
  - **Admin** — akses penuh ke seluruh fitur manajemen (Buku, Member, Transaksi).
  - **Member (User)** — akses ke Dashboard pribadi: kartu level membership, statistik total belanja, katalog harga buku, dan riwayat transaksi milik sendiri.

## 🛠️ Teknologi yang Digunakan

- **Backend:** PHP 8+ (Native)
- **Konsep Arsitektur:** Object-Oriented Programming (OOP)
- **Database:** MySQL / MariaDB (ekstensi `mysqli`)
- **Frontend:** HTML5, Vanilla CSS3 (Custom Design System), JavaScript (untuk interaktivitas UI & kalkulasi DOM)
- **Icons:** Font Awesome 6

## 📚 Implementasi OOP

Proyek ini sangat kaya akan penerapan OOP, meliputi:
1. **Class & Object:** Terdapat banyak pemisahan class entitas (`Book`, `Member`, `Transaction`) dan class infrastruktur (`Database`, `DataStore`).
2. **Encapsulation:** Penggunaan *Access Modifier* (`private`, `protected`) yang ketat untuk mengontrol state objek, diakses melalui *Getter* dan aksi mutator spesifik. Properti `$password` dan `$role` di class `Member` bersifat private dan hanya bisa diakses melalui getter.
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
   - Buat database baru bernama `baystore_uas`.
   - Lakukan **Import** menggunakan file `database.sql` yang telah disediakan di folder *root* proyek.

3. **Migrasi Kolom Autentikasi**
   Jalankan script migrasi sekali saja untuk menambah kolom `password` & `role` ke tabel `members` dan membuat akun Admin:
   ```
   http://localhost/baystore_uas/migrate.php
   ```
   Setelah berhasil, file `migrate.php` bisa dihapus untuk keamanan.

4. **Konfigurasi**
   Buka file `config.php` dan pastikan kredensial database sudah sesuai.
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Sesuaikan jika ada password
   define('DB_NAME', 'baystore_uas');
   ```

5. **Jalankan Aplikasi**
   Buka browser Anda dan akses:
   👉 `http://localhost/baystore_uas/`

## 📖 Alur Kerja Aplikasi (Workflow)

Aplikasi menggunakan sistem autentikasi. Setiap pengguna harus login terlebih dahulu.

### Sebagai Admin
1. Login dengan email `admin@baystore.com` dan password `admin`.
2. Daftarkan **Buku** di menu Buku.
3. Kelola **Member** di menu Member (admin juga dapat menghapus member).
4. Buat **Transaksi** di menu Transaksi — pilih member & buku, lalu proses.
5. **Konfirmasi** transaksi agar statusnya berubah jadi *Completed*; stok dan total belanja member diperbarui otomatis.

### Sebagai Member (User)
1. Daftar akun baru di halaman `register.php`.
2. Login dengan email & password yang sudah didaftarkan.
3. Setelah login, langsung masuk ke **Dashboard Member** yang menampilkan:
   - Kartu level keanggotaan (Bronze / Silver / Gold).
   - Total belanja terakumulasi.
   - Katalog harga buku yang tersedia.
   - Riwayat transaksi pribadi beserta statusnya.

## 🔐 Akun Default

| Peran | Email | Password |
|-------|-------|----------|
| Admin | `admin@baystore.com` | `admin` |
| Member | Daftar via `register.php` | Sesuai yang didaftarkan |

---
*Dibuat untuk keperluan UAS Konsep Bahasa Pemrograman.*
