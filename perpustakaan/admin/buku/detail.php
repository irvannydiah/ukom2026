<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('admin');
require_once __DIR__ . '/../../includes/fungsi.php';

$halaman_aktif = 'buku';

$id_buku = (int)($_GET['id'] ?? 0);
if (!$id_buku) {
    header('Location: index.php?pesan=' . urlencode('ID tidak valid.') . '&tipe=danger');
    exit;
}

// Ambil data buku beserta nama kategori
$buku = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT b.*, k.nama_kategori
     FROM buku b
     JOIN kategori k ON b.id_kategori = k.id_kategori
     WHERE b.id_buku = $id_buku"));

if (!$buku) {
    header('Location: index.php?pesan=' . urlencode('Buku tidak ditemukan.') . '&tipe=danger');
    exit;
}

// Ambil riwayat peminjaman buku ini
$peminjaman = mysqli_query($koneksi,
    "SELECT p.*, u.nama, u.email
     FROM peminjaman p
     JOIN users u ON p.id_user = u.id_user
     WHERE p.id_buku = $id_buku
     ORDER BY p.created_at DESC
     LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Buku - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Data Buku</a></li>
                    <li class="breadcrumb-item active">Detail Buku</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Detail Buku</h4>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="fw-bold"><?= htmlspecialchars($buku['judul']) ?></h5>
                        <table class="table table-borderless table-sm mt-3">
                            <tr>
                                <td width="140" class="text-muted">Penulis</td>
                                <td>: <?= htmlspecialchars($buku['penulis']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Penerbit</td>
                                <td>: <?= htmlspecialchars($buku['penerbit'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tahun Terbit</td>
                                <td>: <?= $buku['tahun_terbit'] ?? '-' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">ISBN</td>
                                <td>: <?= htmlspecialchars($buku['isbn'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kategori</td>
                                <td>: <span class="badge bg-secondary"><?= htmlspecialchars($buku['nama_kategori']) ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Stok</td>
                                <td>: <?= $buku['stok'] ?> eksemplar</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td>:
                                    <?php if ($buku['status'] === 'tersedia'): ?>
                                        <span class="badge bg-success">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>

                        <?php if ($buku['deskripsi']): ?>
                            <div class="mt-2">
                                <strong>Deskripsi:</strong>
                                <p class="text-muted mt-1"><?= nl2br(htmlspecialchars($buku['deskripsi'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-center">
                        <!-- Placeholder cover buku -->
                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                             style="height:200px;">
                            <i class="bi bi-book text-secondary" style="font-size:4rem;"></i>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <a href="edit.php?id=<?= $buku['id_buku'] ?>" class="btn btn-warning text-white">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <a href="hapus.php?id=<?= $buku['id_buku'] ?>" class="btn btn-danger"
                       onclick="return confirm('Yakin ingin menghapus buku ini?')">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Riwayat peminjaman buku ini -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold py-3">
                <i class="bi bi-clock-history me-2"></i> Riwayat Peminjaman Buku Ini
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $ada = false;
                        while ($p = mysqli_fetch_assoc($peminjaman)):
                            $ada = true;
                        ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars($p['nama']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($p['email']) ?></small>
                                </td>
                                <td><?= formatTanggal($p['tanggal_pinjam']) ?></td>
                                <td><?= formatTanggal($p['tanggal_kembali']) ?></td>
                                <td>
                                    <?php
                                    $badge = [
                                        'diajukan'    => 'bg-secondary',
                                        'diproses'    => 'bg-info text-dark',
                                        'dipinjam'    => 'bg-primary',
                                        'dikembalikan'=> 'bg-success',
                                        'ditolak'     => 'bg-danger',
                                    ];
                                    $kelas = $badge[$p['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $kelas ?>">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$ada): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    Belum ada riwayat peminjaman untuk buku ini.
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
