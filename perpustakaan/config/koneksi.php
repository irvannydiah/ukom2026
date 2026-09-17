<?php
/**
 * File koneksi ke database MySQL
 * Sesuaikan $host, $user, $pass, $db dengan pengaturan XAMPP di komputer Anda.
 * Default XAMPP: user = root, password = "" (kosong)
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'perpustakaan';

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');

/**
 * BASE_URL dipakai untuk redirect antar folder (admin/user/auth).
 * PENTING: sesuaikan dengan nama folder proyek Anda di dalam htdocs.
 * Contoh: jika proyek disimpan di htdocs/perpustakaan, maka BASE_URL = '/perpustakaan'
 */
define('BASE_URL', '/perpustakaan');

