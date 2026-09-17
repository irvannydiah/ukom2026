<?php
require_once __DIR__ . '/config/koneksi.php';
session_start();

if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/user/dashboard.php');
    }
} else {
    header('Location: ' . BASE_URL . '/auth/login.php');
}
exit;
