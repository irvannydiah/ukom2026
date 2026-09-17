<?php
require_once __DIR__ . '/../includes/auth.php';
cekRole('admin');
require_once __DIR__ . '/../includes/fungsi.php';

$halaman_aktif = 'dashboard';

// Ambil statistik untuk kartu dashboard
$total_buku      = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM buku"))[0];
$total_kategori  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM kategori"))[0];
$total_anggota   = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM users WHERE role='user'"))[0];
$total_dipinjam  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE status IN ('dipinjam','diproses','diajukan')"))[0];

// 5 peminjaman terbaru
$peminjaman_baru = mysqli_query($koneksi,
    "SELECT p.*, u.nama AS nama_user, b.judul AS judul_buku
     FROM peminjaman p
     JOIN users u ON p.id_user = u.id_user
     JOIN buku b  ON p.id_buku = b.id_buku
     ORDER BY p.created_at DESC
     LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="mb-4">
            <h4 class="fw-bold mb-0">Dashboard Admin</h4>
            <small class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</small>
        </div>

        <!-- Kartu statistik -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-stat bg-blue">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:.85rem; opacity:.85;">Total Buku</div>
                                <h2><?= $total_buku ?></h2>
                            </div>
                            <i class="bi bi-journal-bookmark" style="font-size:2rem; opacity:.6;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stat bg-green">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:.85rem; opacity:.85;">Kategori</div>
                                <h2><?= $total_kategori ?></h2>
                            </div>
                            <i class="bi bi-tags" style="font-size:2rem; opacity:.6;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stat bg-orange">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:.85rem; opacity:.75;">Anggota</div>
                                <h2><?= $total_anggota ?></h2>
                            </div>
                            <i class="bi bi-people" style="font-size:2rem; opacity:.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stat bg-red">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:.85rem; opacity:.85;">Sedang Dipinjam</div>
                                <h2><?= $total_dipinjam ?></h2>
                            </div>
                            <i class="bi bi-arrow-left-right" style="font-size:2rem; opacity:.6;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel peminjaman terbaru -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between">
                <span><i class="bi bi-clock-history me-2"></i>Peminjaman Terbaru</span>
                <a href="peminjaman/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $ada = false;
                        $badge = [
                            'diajukan'    => 'bg-secondary',
                            'diproses'    => 'bg-info text-dark',
                            'dipinjam'    => 'bg-primary',
                            'dikembalikan'=> 'bg-success',
                            'ditolak'     => 'bg-danger',
                        ];
                        while ($p = mysqli_fetch_assoc($peminjaman_baru)):
                            $ada = true;
                            $kelas = $badge[$p['status']] ?? 'bg-secondary';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nama_user']) ?></td>
                                <td><?= htmlspecialchars($p['judul_buku']) ?></td>
                                <td><?= formatTanggal($p['tanggal_pinjam']) ?></td>
                                <td><span class="badge <?= $kelas ?>"><?= ucfirst($p['status']) ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$ada): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    Belum ada data peminjaman.
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
