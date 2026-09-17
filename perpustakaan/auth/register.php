<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';

session_start();

// Kalau sudah login, jangan biarkan buka halaman register lagi
if (isset($_SESSION['id_user'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = bersihkan($_POST['nama']);
    $email    = bersihkan($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $no_hp    = bersihkan($_POST['no_hp']);
    $alamat   = bersihkan($_POST['alamat']);

    // ===== VALIDASI FORM =====
    if (empty($nama) || empty($email) || empty($password) || empty($konfirmasi_password)) {
        $error = 'Semua field wajib diisi, kecuali No. HP dan Alamat.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi_password) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Cek apakah email sudah terdaftar
        $cekEmail = mysqli_query($koneksi, "SELECT id_user FROM users WHERE email = '$email'");

        if (mysqli_num_rows($cekEmail) > 0) {
            $error = 'Email sudah terdaftar, silakan gunakan email lain.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (nama, email, password, no_hp, alamat, role)
                      VALUES ('$nama', '$email', '$passwordHash', '$no_hp', '$alamat', 'user')";

            if (mysqli_query($koneksi, $query)) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Registrasi gagal: ' . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-icon"><i class="bi bi-person-plus"></i></div>
        <h3 class="text-center">Daftar Akun</h3>
        <p class="subtitle text-center">Buat akun untuk mulai meminjam buku</p>

        <?php if ($error): ?>
            <?= tampilkanAlert('danger', $error) ?>
        <?php endif; ?>

        <?php if ($success): ?>
            <?= tampilkanAlert('success', $success . ' <a href="login.php">Klik di sini untuk login</a>') ?>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required
                       value="<?= isset($nama) ? $nama : '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required
                       value="<?= isset($email) ? $email : '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">No. HP</label>
                <input type="text" name="no_hp" class="form-control"
                       value="<?= isset($no_hp) ? $no_hp : '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2"><?= isset($alamat) ? $alamat : '' ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="konfirmasi_password" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary w-100">Daftar</button>
        </form>
        <?php endif; ?>

        <p class="text-center mt-3 mb-0">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </p>
    </div>
</div>
</body>
</html>
