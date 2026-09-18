<div align="center">

# 📚 Sistem Perpustakaan Digital
### Aplikasi Manajemen Perpustakaan Berbasis Web (Admin & Anggota)

**Kelola data buku, kategori, anggota, dan alur peminjaman dalam satu platform.**

[![PHP](https://img.shields.io/badge/PHP-Native-777BB4?style=for-the-badge&logo=php)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)](https://www.mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap)](https://getbootstrap.com)
[![XAMPP](https://img.shields.io/badge/Server-XAMPP-FB7A24?style=for-the-badge&logo=xampp)](https://www.apachefriends.org)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)](LICENSE)

</div>

---

## 📌 Tentang Sistem Perpustakaan

**Sistem Perpustakaan Digital** adalah aplikasi berbasis web untuk mengelola operasional perpustakaan, mulai dari data buku & kategori, data anggota, hingga alur pengajuan dan persetujuan peminjaman buku. Sistem ini memisahkan akses **Admin (Pustakawan)** yang mengelola seluruh data, dan **Anggota (User)** yang bisa mencari buku serta mengajukan peminjaman secara mandiri.

> Dibangun dengan **PHP Native (mysqli)** dan **MySQL**, cocok dijalankan secara lokal menggunakan **XAMPP**.

---

## ✨ Fitur Utama

- 🔐 **Autentikasi Multi-Role** — Login & registrasi dengan pemisahan akses **Admin** dan **Anggota**, password disimpan ter-hash (`password_hash`).
- 📖 **Manajemen Buku (Admin)** — Tambah, edit, hapus, dan lihat detail buku beserta riwayat peminjamannya.
- 🏷️ **Manajemen Kategori (Admin)** — Kelola kategori/genre buku, lengkap dengan penghitung jumlah buku per kategori.
- 👥 **Manajemen Anggota (Admin)** — Tambah, edit, cari, dan hapus data anggota (dengan validasi tidak bisa hapus anggota yang masih punya peminjaman aktif).
- 🔄 **Manajemen Peminjaman (Admin)** — Ubah status peminjaman (*Diajukan → Diproses → Dipinjam → Dikembalikan/Ditolak*) dengan **stok buku yang otomatis tersinkron**.
- 🔎 **Cari & Ajukan Pinjam Buku (Anggota)** — Anggota bisa mencari/memfilter buku per kategori, melihat detail, lalu mengajukan peminjaman langsung dari halaman detail.
- 📋 **Riwayat Peminjaman Pribadi (Anggota)** — Anggota bisa memantau status pengajuannya sendiri dan membatalkan pengajuan yang belum diproses admin.
- 📊 **Dashboard Statistik** — Ringkasan data (total buku, kategori, anggota, peminjaman aktif) untuk Admin, serta ringkasan pribadi untuk Anggota.
- 🎨 **UI Responsif** — Tampilan modern berbasis Bootstrap 5 & Bootstrap Icons.

---

### 🧪 Alur Coba Pakai (Demo)

```text
1️⃣ Skenario 1: Registrasi & Login (Anggota)
   └─ Buka /auth/register.php, daftar akun baru.
   └─ Login menggunakan akun tersebut di /auth/login.php.
   └─ Akan diarahkan otomatis ke Dashboard Anggota.

2️⃣ Skenario 2: Cari & Ajukan Pinjam Buku (Anggota)
   └─ Buka menu 'Cari Buku', cari/filter buku yang diinginkan.
   └─ Klik 'Detail', lalu klik 'Ajukan Pinjam'.
   └─ Pantau statusnya di menu 'Peminjaman Saya'.

3️⃣ Skenario 3: Kelola Data & Proses Peminjaman (Admin)
   └─ Login menggunakan akun ber-role 'admin'.
   └─ Buka menu 'Data Buku' / 'Kategori' / 'Anggota' untuk kelola data master.
   └─ Buka menu 'Peminjaman', klik 'Ubah Status' untuk memproses pengajuan anggota.
```

> Belum ada akun admin bawaan. Cara membuat akun admin pertama: daftar akun biasa lewat halaman **Register**, lalu jalankan query berikut di phpMyAdmin:
> ```sql
> UPDATE users SET role = 'admin' WHERE email = 'email_anda@contoh.com';
> ```

---

## 🖥️ Screenshot

| Halaman Login | Dashboard Admin |
|---|---|
| ![Login](docs/login.png) | ![Dashboard Admin](docs/dashboard_admin.png) |

| Data Buku (Admin) | Data Kategori (Admin) |
|---|---|
| ![Data Buku](docs/admin_buku.png) | ![Data Kategori](docs/admin_kategori.png) |

| Data Anggota (Admin) | Kelola Peminjaman (Admin) |
|---|---|
| ![Data Anggota](docs/admin_anggota.png) | ![Kelola Peminjaman](docs/admin_peminjaman.png) |

| Dashboard Anggota | Cari Buku (Anggota) |
|---|---|
| ![Dashboard Anggota](docs/dashboard_user.png) | ![Cari Buku](docs/user_buku.png) |

| Detail Buku & Ajukan Pinjam | Peminjaman Saya (Anggota) |
|---|---|
| ![Detail Buku](docs/user_buku_detail.png) | ![Peminjaman Saya](docs/user_peminjaman.png) |

> 📸 Simpan tangkapan layar aplikasi kamu ke folder `docs/` dengan nama file seperti di atas, agar gambar otomatis tampil di README ini.

---

## 🛠️ Tech Stack

| Teknologi | Kegunaan |
|---|---|
| [PHP Native](https://www.php.net) | Logika backend (tanpa framework, native `mysqli`) |
| [MySQL / MariaDB](https://www.mysql.com) | Database relasional (`users`, `buku`, `kategori`, `peminjaman`) |
| [Bootstrap 5](https://getbootstrap.com) | Styling UI & layout responsif |
| [Bootstrap Icons](https://icons.getbootstrap.com) | Ikon antarmuka |
| [XAMPP](https://www.apachefriends.org) | Web server lokal (Apache + MySQL) |

---

## 🚀 Cara Menjalankan Lokal

### Prasyarat
- [XAMPP](https://www.apachefriends.org) (Apache + MySQL + PHP)
- Browser modern

### 1. Salin Project ke htdocs

Salin folder `perpustakaan/` ke dalam direktori `htdocs` instalasi XAMPP kamu, misalnya:

```
C:/xampp/htdocs/perpustakaan
```

### 2. Buat Database

1. Jalankan Apache & MySQL dari **XAMPP Control Panel**.
2. Buka `http://localhost/phpmyadmin`.
3. Import file `database/perpustakaan.sql` (database `perpustakaan` beserta tabel & data awal akan otomatis dibuat).

### 3. Sesuaikan Koneksi Database

Buka `config/koneksi.php`, sesuaikan jika perlu:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'perpustakaan';

define('BASE_URL', '/perpustakaan'); // sesuaikan dengan nama folder di htdocs
```

### 4. Akses Aplikasi

Buka browser dan kunjungi:

```
http://localhost/perpustakaan
```

---

## 📁 Struktur Project

```
perpustakaan/
├── admin/
│   ├── dashboard.php       # Statistik & ringkasan admin
│   ├── buku/                # CRUD data buku
│   ├── kategori/             # CRUD kategori buku
│   ├── anggota/               # Kelola data anggota
│   └── peminjaman/             # Kelola & ubah status peminjaman
├── user/
│   ├── dashboard.php       # Statistik & ringkasan anggota
│   ├── buku/                # Cari buku & ajukan peminjaman
│   └── peminjaman/            # Riwayat peminjaman & pembatalan
├── auth/
│   ├── login.php            # Halaman login
│   ├── register.php          # Halaman registrasi anggota
│   └── logout.php             # Proses logout
├── config/
│   └── koneksi.php          # Koneksi database & BASE_URL
├── includes/
│   ├── auth.php              # cekLogin() & cekRole()
│   ├── fungsi.php             # Fungsi bantu (bersihkan input, alert, format tanggal)
│   ├── sidebar_admin.php      # Sidebar navigasi admin
│   └── sidebar_user.php        # Sidebar navigasi anggota
├── assets/
│   ├── css/style.css        # Style kustom
│   ├── js/                   # Script tambahan
│   └── img/                   # Aset gambar
├── database/
│   └── perpustakaan.sql     # Skema & data awal database
└── index.php                 # Redirect otomatis sesuai status login & role
```

---

<div align="center">

📄 **Lisensi**

Project ini dapat digunakan bebas untuk keperluan pembelajaran.

Dibuat untuk mendukung digitalisasi perpustakaan 📚
</div>
