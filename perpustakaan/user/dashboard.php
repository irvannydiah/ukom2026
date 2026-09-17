<?php
require_once __DIR__ . '/../includes/auth.php';
cekRole('user'); // halaman ini hanya boleh diakses user
require_once __DIR__ . '/../includes/fungsi.php';

$halaman_aktif = 'dashboard';
$id_user = (int)$_SESSION['id_user'];

// Ambil statistik untuk kartu dashboard
$total_buku_tersedia = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM buku WHERE status = 'tersedia' AND stok > 0"))[0];

$total_aktif = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peminjaman
     WHERE id_user = $id_user AND status IN ('diajukan','diproses','dipinjam')"))[0];

$total_dikembalikan = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peminjaman
     WHERE id_user = $id_user AND status = 'dikembalikan'"))[0];

$total_riwayat = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peminjaman WHERE id_user = $id_user"))[0];

// 5 peminjaman terbaru milik user ini
$riwayat_terbaru = mysqli_query($koneksi,
    "SELECT p.*, b.judul AS judul_buku
     FROM peminjaman p
     JOIN buku b ON p.id_buku = b.id_buku
     WHERE p.id_user = $id_user
     ORDER BY p.created_at DESC
     LIMIT 5");

$badge = [
    'diajukan'    => 'bg-secondary',
    'diproses'    => 'bg-info text-dark',
    'dipinjam'    => 'bg-primary',
    'dikembalikan'=> 'bg-success',
    'ditolak'     => 'bg-danger',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../includes/sidebar_user.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="mb-4">
            <h4 class="fw-bold mb-0">Dashboard Anggota</h4>
            <small class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</small>
        </div>

        <!-- Kartu statistik -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-stat bg-blue">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:.85rem; opacity:.85;">Buku Tersedia</div>
                                <h2><?= $total_buku_tersedia ?></h2>
                            </div>
                            <i class="bi bi-journal-bookmark" style="font-size:2rem; opacity:.6;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stat bg-orange">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:.85rem; opacity:.75;">Peminjaman Aktif</div>
                                <h2><?= $total_aktif ?></h2>
                            </div>
                            <i class="bi bi-arrow-left-right" style="font-size:2rem; opacity:.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stat bg-green">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:.85rem; opacity:.85;">Selesai Dikembalikan</div>
                                <h2><?= $total_dikembalikan ?></h2>
                            </div>
                            <i class="bi bi-check2-circle" style="font-size:2rem; opacity:.6;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stat bg-red">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:.85rem; opacity:.85;">Total Riwayat</div>
                                <h2><?= $total_riwayat ?></h2>
                            </div>
                            <i class="bi bi-clock-history" style="font-size:2rem; opacity:.6;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <a href="<?= BASE_URL ?>/user/buku/index.php" class="btn btn-primary">
                <i class="bi bi-search me-1"></i> Cari &amp; Pinjam Buku
            </a>
            <a href="<?= BASE_URL ?>/user/peminjaman/index.php" class="btn btn-outline-primary">
                <i class="bi bi-list-check me-1"></i> Lihat Semua Peminjaman
            </a>
        </div>

        <!-- Tabel peminjaman terbaru -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between">
                <span><i class="bi bi-clock-history me-2"></i>Peminjaman Terbaru Saya</span>
                <a href="<?= BASE_URL ?>/user/peminjaman/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $ada = false;
                        while ($p = mysqli_fetch_assoc($riwayat_terbaru)):
                            $ada = true;
                            $kelas = $badge[$p['status']] ?? 'bg-secondary';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($p['judul_buku']) ?></td>
                                <td><?= formatTanggal($p['tanggal_pinjam']) ?></td>
                                <td><span class="badge <?= $kelas ?>"><?= ucfirst($p['status']) ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$ada): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    Anda belum pernah mengajukan peminjaman buku.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
