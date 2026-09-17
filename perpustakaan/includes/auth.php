<?php
/**
 * File ini mengatur:
 * - Memulai session
 * - Fungsi cekLogin()  -> memastikan user sudah login (role apa saja)
 * - Fungsi cekRole()   -> memastikan role user sesuai (admin/user)
 *
 * WAJIB di-include PALING ATAS di setiap halaman yang butuh login,
 * SEBELUM ada output HTML apapun (karena memakai header() untuk redirect).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/koneksi.php';

// Pastikan user sudah login, kalau belum -> tendang ke halaman login
function cekLogin()
{
    if (!isset($_SESSION['id_user'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

// Pastikan user sudah login DAN rolenya sesuai yang diminta
// Contoh pemakaian di halaman admin : cekRole('admin');
// Contoh pemakaian di halaman user  : cekRole('user');
function cekRole($roleDiizinkan)
{
    cekLogin();

    if ($_SESSION['role'] !== $roleDiizinkan) {
        // Kalau role tidak cocok, arahkan ke dashboard sesuai rolenya masing-masing
        if ($_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/user/dashboard.php');
        }
        exit;
    }
}
