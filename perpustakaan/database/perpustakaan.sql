-- =========================================================
-- DATABASE: perpustakaan
-- Sistem Perpustakaan Berbasis Web (UKOM)
-- =========================================================

CREATE DATABASE IF NOT EXISTS perpustakaan;
USE perpustakaan;

-- =========================================================
-- TABEL: users
-- Menyimpan data akun admin & anggota (user)
-- =========================================================
CREATE TABLE users (
    id_user       INT AUTO_INCREMENT PRIMARY KEY,
    nama          VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,       -- disimpan dengan password_hash()
    no_hp         VARCHAR(20)  DEFAULT NULL,
    alamat        VARCHAR(255) DEFAULT NULL,
    role          ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- TABEL: kategori
-- Menyimpan kategori/genre buku
-- =========================================================
CREATE TABLE kategori (
    id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- TABEL: buku
-- Menyimpan data buku. Berelasi ke kategori (many-to-one)
-- =========================================================
CREATE TABLE buku (
    id_buku       INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori   INT NOT NULL,
    judul         VARCHAR(150) NOT NULL,
    penulis       VARCHAR(100) NOT NULL,
    penerbit      VARCHAR(100) DEFAULT NULL,
    tahun_terbit  YEAR DEFAULT NULL,
    isbn          VARCHAR(30)  DEFAULT NULL,
    stok          INT NOT NULL DEFAULT 1,
    status        ENUM('tersedia','dipinjam') NOT NULL DEFAULT 'tersedia',
    sampul        VARCHAR(255) DEFAULT NULL,   -- nama file gambar cover buku
    deskripsi     TEXT DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_buku_kategori
        FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- TABEL: peminjaman
-- Transaksi peminjaman buku oleh user.
-- Berelasi ke users (siapa yang pinjam) dan buku (buku apa)
-- =========================================================
CREATE TABLE peminjaman (
    id_peminjaman   INT AUTO_INCREMENT PRIMARY KEY,
    id_user         INT NOT NULL,
    id_buku         INT NOT NULL,
    tanggal_pinjam  DATE NOT NULL,
    tanggal_kembali DATE DEFAULT NULL,   -- rencana/realisasi tanggal kembali
    status          ENUM('diajukan','diproses','dipinjam','dikembalikan','ditolak')
                    NOT NULL DEFAULT 'diajukan',
    catatan         VARCHAR(255) DEFAULT NULL,  -- catatan admin (misal alasan ditolak)
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_peminjaman_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_peminjaman_buku
        FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- DATA AWAL (SEED)
-- =========================================================

-- Catatan: Akun admin TIDAK di-seed manual di sini karena kolom password
-- wajib memakai hash dari fungsi PHP password_hash() agar valid untuk login.
-- Cara membuat akun admin pertama kali (lihat penjelasan Tahap 3):
--   1. Daftar akun biasa lewat halaman auth/register.php
--   2. Jalankan query berikut di phpMyAdmin untuk menjadikannya admin:
--      UPDATE users SET role = 'admin' WHERE email = 'email_anda@contoh.com';

-- Contoh kategori
INSERT INTO kategori (nama_kategori) VALUES
('Fiksi'),
('Non-Fiksi'),
('Teknologi'),
('Sejarah'),
('Kesehatan');

-- Contoh buku
INSERT INTO buku (id_kategori, judul, penulis, penerbit, tahun_terbit, isbn, stok, status) VALUES
(3, 'Belajar PHP Native', 'Budi Santoso', 'Penerbit Andi', 2022, '9786020123456', 5, 'tersedia'),
(1, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, '9789793062792', 3, 'tersedia'),
(5, 'Panduan Hidup Sehat', 'Dr. Siti Aminah', 'Gramedia', 2020, '9786020456789', 2, 'tersedia');
