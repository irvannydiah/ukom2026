<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('user');
require_once __DIR__ . '/../../includes/fungsi.php';

$halaman_aktif = 'buku';
$id_user = (int)$_SESSION['id_user'];
$id_buku = (int)($_GET['id'] ?? 0);

if (!$id_buku) {
    header('Location: index.php?pesan=' . urlencode('ID buku tidak valid.') . '&tipe=danger');
    exit;
}

$error = '';

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

// ===== AJUKAN PEMINJAMAN =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'ajukan') {
    // Ambil kondisi stok/status buku terkini (hindari race condition sederhana)
    $cekBuku = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT stok, status FROM buku WHERE id_buku = $id_buku"));

    // Cek apakah user masih punya pengajuan/peminjaman aktif untuk buku ini
    $cekAktif = mysqli_query($koneksi,
        "SELECT id_peminjaman FROM peminjaman
         WHERE id_user = $id_user AND id_buku = $id_buku
           AND status IN ('diajukan','diproses','dipinjam')
         LIMIT 1");

    if (!$cekBuku || (int)$cekBuku['stok'] <= 0 || $cekBuku['status'] !== 'tersedia') {
        $error = 'Maaf, buku ini sedang tidak tersedia untuk dipinjam.';
    } elseif (mysqli_num_rows($cekAktif) > 0) {
        $error = 'Anda masih memiliki pengajuan/peminjaman aktif untuk buku ini.';
    } else {
        $tanggal_pinjam = date('Y-m-d');
        if (mysqli_query($koneksi,
            "INSERT INTO peminjaman (id_user, id_buku, tanggal_pinjam, status)
             VALUES ($id_user, $id_buku, '$tanggal_pinjam', 'diajukan')")) {

            $pesan = 'Pengajuan peminjaman buku "' . $buku['judul'] . '" berhasil dikirim. Silakan tunggu konfirmasi admin.';
            header('Location: ' . BASE_URL . '/user/peminjaman/index.php?pesan=' . urlencode($pesan) . '&tipe=success');
            exit;
        } else {
            $error = 'Gagal mengajukan peminjaman: ' . mysqli_error($koneksi);
        }
    }
}

$bisa_pinjam = ($buku['status'] === 'tersedia' && (int)$buku['stok'] > 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Buku - Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../../includes/sidebar_user.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Cari Buku</a></li>
                    <li class="breadcrumb-item active">Detail Buku</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Detail Buku</h4>
        </div>

        <?php if ($error): ?>
            <?= tampilkanAlert('danger', $error) ?>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
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
                                    <?php if ($bisa_pinjam): ?>
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
                    <?php if ($bisa_pinjam): ?>
                        <form method="POST" action=""
                              onsubmit="return confirm('Ajukan peminjaman buku &quot;<?= htmlspecialchars($buku['judul']) ?>&quot;?');">
                            <input type="hidden" name="aksi" value="ajukan">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-journal-plus me-1"></i> Ajukan Pinjam
                            </button>
                        </form>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="bi bi-journal-x me-1"></i> Stok Habis
                        </button>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
