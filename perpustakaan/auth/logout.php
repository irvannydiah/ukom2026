<?php
session_start();
session_unset();
session_destroy();

require_once __DIR__ . '/../config/koneksi.php';
header('Location: ' . BASE_URL . '/auth/login.php');
exit;
